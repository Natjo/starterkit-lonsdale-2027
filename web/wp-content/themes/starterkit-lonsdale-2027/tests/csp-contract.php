<?php
declare(strict_types=1);

define('WP_CONTENT_DIR', sys_get_temp_dir() . '/theme-csp-contract-' . bin2hex(random_bytes(6)));
define('THEME_DIR', dirname(__DIR__) . '/');
mkdir(WP_CONTENT_DIR);
$hooks = [];
function add_filter($name, $callback, $priority = 10, $args = 1) { global $hooks; $hooks[$name] = $callback; }
function apply_filters($name, $value) { return $value; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function wp_is_json_request() { return false; }
require dirname(__DIR__) . '/inc/config/csp.php';
require dirname(__DIR__) . '/inc/helpers/theme-assets.php';
require dirname(__DIR__, 3) . '/plugins/wp-static/csp-cache.php';
function check(bool $value, string $message): void { if (!$value) throw new RuntimeException($message); }
function reset_csp(): void {
    foreach (['config' => null, 'revision' => '', 'preparedPolicy' => '', 'hashes' => ['script' => [], 'style' => []]] as $name => $value) {
        (new ReflectionProperty(Theme_CSP::class, $name))->setValue(null, $value);
    }
}
try {
    check(!Theme_CSP::enabled(), 'CSP must default to disabled.');
    $config = ['version' => 1, 'enabled' => true, 'mode' => 'report', 'policy' => "default-src 'self'; script-src-elem 'none'; style-src-attr 'none'"];
    file_put_contents(WP_CONTENT_DIR . '/theme-csp.json', json_encode($config));
    reset_csp();
    $headers = Theme_CSP::headers([]);
    check(isset($headers['Content-Security-Policy-Report-Only']), 'Expected Report-Only header.');
    $script = "\nwindow.trusted = true;\n";
    $hooks['wp_inline_script_attributes'](['id' => 'app-js-js-extra'], $script);
    $hooks['wp_inline_script_attributes'](['id' => 'unknown-js-extra'], 'window.untrusted = true;');
    Theme_CSP::registerInline('style', "body { color: red; }\r\n");
    $policy = Theme_CSP::policy();
    $hash = "'sha256-" . base64_encode(hash('sha256', $script, true)) . "'";
    check(str_contains($policy, 'script-src-elem ' . $hash), 'Specific none must become trusted hashes only.');
    check(!str_contains($policy, base64_encode(hash('sha256', 'window.untrusted = true;', true))), 'Unknown scripts must never be authorized.');
    check(str_contains($policy, "style-src-attr 'none'"), 'Attribute restrictions must remain unchanged.');
    check(Theme_CSP::isTrustedInline('style', "body { color: red; }\n"), 'Line ending normalization must match browsers.');
    $css = 'body{color:blue}';
    Theme_CSP::registerInline('style', $css);
    ThemeAssets::queueHeroCritical('page');
    $processed = ThemeAssets::processBufferedHtml('<head><style id="critical-css">' . $css . '</style></head>');
    preg_match('#<style id="critical-css">(.*?)</style>#s', $processed, $match);
    check(Theme_CSP::isTrustedInline('style', $match[1]), 'Final concatenated critical CSS must be registered.');
    Theme_CSP::finalize('<script>alert(1)</script>');
    check(!Theme_CSP::isTrustedInline('script', 'alert(1)'), 'No HTML scan may authorize injected code.');
    $html = '<style>' . $css . '</style><script>' . $script . '</script>';
    $revision = hash_file('sha256', WP_CONTENT_DIR . '/theme-csp.json');
    $packed = wp_static_pack_csp($html, $html, ['Content-Security-Policy-Report-Only' => $policy], $revision);
    $file = WP_CONTENT_DIR . '/index.html';
    file_put_contents($file, $packed);
    $cached = wp_static_read_csp_cache($file, WP_CONTENT_DIR);
    check($cached !== null && $cached['html'] === $html && $cached['headers']['Content-Security-Policy-Report-Only'] === $policy, 'Static headers and body must be paired.');
    check(wp_static_pack_csp($html . '<script>alert(1)</script>', $html, ['Content-Security-Policy' => $policy], $revision) === null, 'Post-processing scripts must invalidate the cache.');
    check(wp_static_pack_csp($html, $html, ['Content-Security-Policy' => "bad\r\nInjected: header"], $revision) === null, 'Reject header injection.');
    file_put_contents($file, $packed . 'changed');
    check(wp_static_read_csp_cache($file, WP_CONTENT_DIR) === null, 'Reject altered HTML.');
    file_put_contents($file, $packed);
    $config['mode'] = 'enforce'; file_put_contents(WP_CONTENT_DIR . '/theme-csp.json', json_encode($config));
    check(wp_static_read_csp_cache($file, WP_CONTENT_DIR) === null, 'Changed CSP must bypass stale cache.');
    file_put_contents($file, $html);
    check(wp_static_read_csp_cache($file, WP_CONTENT_DIR) === null, 'Legacy cache cannot bypass newly enabled CSP.');
    unlink(WP_CONTENT_DIR . '/theme-csp.json');
    check(wp_static_read_csp_cache($file, WP_CONTENT_DIR)['html'] === null, 'Legacy cache stays streamed without CSP configuration.');
    echo "CSP contracts passed: trusted hashes, specific rules, critical concatenation, static pairing, invalidation and injection rejection.\n";
} finally {
    foreach (glob(WP_CONTENT_DIR . '/*') as $file) unlink($file);
    rmdir(WP_CONTENT_DIR);
}
