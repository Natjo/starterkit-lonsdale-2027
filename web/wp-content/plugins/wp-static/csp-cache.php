<?php
/** Standalone CSP/HTML envelope: one atomic cache file, usable before WordPress. */

function wp_static_pack_csp($html, $original, array $headers, $revision)
{
    if (!$headers && $revision === '') return $html;
    if (!is_string($revision) || ($revision !== '' && !preg_match('/^[a-f0-9]{64}$/', $revision))) return null;
    foreach ($headers as $name => $policy) {
        if (!in_array($name, ['Content-Security-Policy', 'Content-Security-Policy-Report-Only'], true)
            || !is_string($policy) || strlen($policy) > 16000 || preg_match('/[\x00-\x1f\x7f]/', $policy)) return null;
    }
    // Post-processing must not mutate inline bytes after hashes were computed.
    preg_match_all('#<(script|style)\b[^>]*>.*?</\1\s*>#is', $original, $before);
    preg_match_all('#<(script|style)\b[^>]*>.*?</\1\s*>#is', $html, $after);
    if ($before[0] !== $after[0]) return null;
    $data = ['version' => 1, 'revision' => $revision, 'digest' => hash('sha256', $html), 'headers' => $headers];
    return '<!--wp-static-csp:' . base64_encode(json_encode($data, JSON_UNESCAPED_SLASHES)) . "-->\n" . $html;
}

/** Null means stale/corrupt cache: let WordPress generate a fresh policy instead. */
function wp_static_read_csp_cache($file, $content_dir)
{
    $config_file = $content_dir . '/theme-csp.json';
    $prefix = '<!--wp-static-csp:';
    $start = @file_get_contents($file, false, null, 0, strlen($prefix));
    if (!is_string($start)) return null;
    // Preserve streaming and low memory use for historical sites without CSP.
    if ($start !== $prefix) return file_exists($config_file) ? null : ['html' => null, 'headers' => []];
    $html = @file_get_contents($file);
    if (!is_string($html)) return null;
    $config = is_file($config_file) && filesize($config_file) <= 16000 ? @file_get_contents($config_file) : false;
    $revision = is_string($config) ? hash('sha256', $config) : '';
    if (!preg_match('/\A<!--wp-static-csp:([A-Za-z0-9+\/=]{1,50000})-->\n/', $html, $match)) return null;
    $data = json_decode(base64_decode($match[1], true), true);
    $body = substr($html, strlen($match[0]));
    if (!is_array($data) || ($data['version'] ?? null) !== 1 || ($data['revision'] ?? null) !== $revision
        || !is_string($data['digest'] ?? null) || !hash_equals(hash('sha256', $body), $data['digest'])
        || !is_array($data['headers'] ?? null) || count($data['headers']) > 2) return null;
    foreach ($data['headers'] as $name => $policy) {
        if (!in_array($name, ['Content-Security-Policy', 'Content-Security-Policy-Report-Only'], true)
            || !is_string($policy) || strlen($policy) > 16000 || preg_match('/[\x00-\x1f\x7f]/', $policy)) return null;
    }
    if (file_exists($config_file)) {
        $value = is_string($config) ? json_decode($config, true) : null;
        if (!is_array($value) || ($value['version'] ?? null) !== 1 || !is_bool($value['enabled'] ?? null)) return null;
        $name = ($value['mode'] ?? '') === 'report' ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        if ($value['enabled'] && empty($data['headers'][$name])) return null;
    }
    return ['html' => $body, 'headers' => $data['headers']];
}

function wp_static_send_csp_headers(array $headers): void
{
    foreach ($headers as $name => $policy) header($name . ': ' . $policy);
}
