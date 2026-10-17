<?php

/** Front CSP: trusted producers register hashes; arbitrary HTML is never authorized. */
final class Theme_CSP
{
    public const VERSION = 1;
    private static ?array $config = null;
    private static string $revision = '';
    private static string $preparedPolicy = '';
    private static array $hashes = ['script' => [], 'style' => []];

    public static function config(): array
    {
        if (self::$config !== null) return self::$config;
        $default = ['enabled' => false, 'mode' => 'report', 'policy' => "default-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'"];
        $file = WP_CONTENT_DIR . '/theme-csp.json';
        if (!is_file($file)) return self::$config = $default;
        $raw = filesize($file) <= 16000 ? file_get_contents($file) : false;
        $value = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($value) || ($value['version'] ?? null) !== 1 || !is_bool($value['enabled'] ?? null)
            || !in_array($value['mode'] ?? '', ['report', 'enforce'], true) || !is_string($value['policy'] ?? null)
            || strlen($value['policy']) > 8000 || trim($value['policy']) === '' || preg_match('/[\x00-\x1f\x7f]/', $value['policy'])) {
            error_log('Theme CSP: invalid configuration; restrictive enforcement fallback.');
            $default['enabled'] = true;
            $default['mode'] = 'enforce';
            return self::$config = $default;
        }
        self::$revision = hash('sha256', $raw);
        return self::$config = array_intersect_key($value, $default);
    }

    public static function enabled(): bool
    {
        return self::config()['enabled'] && !is_admin() && !wp_doing_ajax() && !wp_is_json_request()
            && !(defined('WP_CLI') && WP_CLI);
    }

    /** Call only from trusted PHP producers, never on editorial HTML. */
    public static function registerInline(string $kind, string $content): void
    {
        if (!self::enabled() || !isset(self::$hashes[$kind]) || $content === '') return;
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        self::$hashes[$kind]["'sha256-" . base64_encode(hash('sha256', $content, true)) . "'"] = true;
    }

    public static function isTrustedInline(string $kind, string $content): bool
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        return isset(self::$hashes[$kind]["'sha256-" . base64_encode(hash('sha256', $content, true)) . "'"]);
    }

    /** Hashes affect element rules only, not onclick/style attributes. */
    public static function policy(): string
    {
        $directives = [];
        foreach (explode(';', self::config()['policy']) as $part) {
            $tokens = preg_split('/\s+/', trim($part));
            $name = strtolower(array_shift($tokens) ?? '');
            if ($name !== '' && !isset($directives[$name])) $directives[$name] = $tokens;
        }
        foreach (self::$hashes as $kind => $hashes) {
            if (!$hashes) continue;
            $name = $kind . '-src-elem';
            $base = $directives[$name] ?? $directives[$kind . '-src'] ?? $directives['default-src'] ?? null;
            if ($base === null) continue; // An unrestricted kind needs no hash authorization.
            $base = array_values(array_filter($base, static fn ($token) => $token !== "'none'"));
            $directives[$name] = array_values(array_unique([...$base, ...array_keys($hashes)]));
        }
        return implode('; ', array_map(static fn ($name, $values) => trim($name . ' ' . implode(' ', $values)), array_keys($directives), $directives));
    }

    private static function headerName(): string
    {
        return self::config()['mode'] === 'report' ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
    }

    public static function headers(array $headers): array
    {
        if (!self::enabled()) return $headers;
        foreach (array_keys($headers) as $key) {
            if (in_array(strtolower($key), ['content-security-policy', 'content-security-policy-report-only'], true)) {
                error_log('Theme CSP: conflicting WordPress policy preserved.');
                return $headers;
            }
        }
        self::$preparedPolicy = self::policy();
        $headers[self::headerName()] = self::$preparedPolicy;
        return $headers;
    }

    /** Finalize after ThemeAssets, before the buffered response is sent. */
    public static function finalize(string $html): string
    {
        if (is_admin() || wp_doing_ajax() || wp_is_json_request() || (defined('WP_CLI') && WP_CLI)) return $html;
        self::config();
        if (headers_sent()) {
            if (self::enabled()) error_log('Theme CSP: headers already sent; hashes not finalized.');
            return $html;
        }
        if (self::$revision !== '') header('X-Theme-CSP-Revision: ' . self::$revision);
        if (!self::enabled()) return $html;
        $policy = self::policy();
        if (strlen($policy) > 16000) {
            error_log('Theme CSP: policy exceeds 16 KB; hashes not applied.');
            return $html;
        }
        foreach (headers_list() as $header) {
            [$key, $value] = array_pad(explode(':', $header, 2), 2, '');
            if (in_array(strtolower($key), ['content-security-policy', 'content-security-policy-report-only'], true)
                && (strcasecmp($key, self::headerName()) !== 0 || trim($value) !== self::$preparedPolicy)) {
                error_log('Theme CSP: conflicting policy; existing headers preserved.');
                return $html;
            }
        }
        header(self::headerName() . ': ' . $policy, true);
        return $html;
    }
}

add_filter('wp_headers', [Theme_CSP::class, 'headers'], PHP_INT_MAX);
add_filter('wp_inline_script_attributes', static function (array $attributes, string $data): array {
    $ids = apply_filters('theme_csp_inline_script_ids', ['app-js-js-extra', 'app-js-js-before', 'app-js-js-after']);
    if (in_array($attributes['id'] ?? '', $ids, true)) Theme_CSP::registerInline('script', $data);
    return $attributes;
}, PHP_INT_MAX, 2);
