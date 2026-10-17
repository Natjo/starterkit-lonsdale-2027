<?php
/** Test router only: run in an isolated PHP server, never include in the site. */
define('WP_CONTENT_DIR', sys_get_temp_dir());
define('THEME_DIR', dirname(__DIR__) . '/');
$hooks = [];
function add_filter($name, $callback, $priority = 10, $args = 1) { global $hooks; $hooks[$name] = $callback; }
function apply_filters($name, $value) { return $value; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function wp_is_json_request() { return false; }
require dirname(__DIR__) . '/inc/config/csp.php';
require dirname(__DIR__, 3) . '/plugins/wp-static/csp-cache.php';
$mode = ($_GET['mode'] ?? '') === 'report' ? 'report' : 'enforce';
(new ReflectionProperty(Theme_CSP::class, 'config'))->setValue(null, [
    'enabled' => true, 'mode' => $mode, 'policy' => "default-src 'self'; object-src 'none'; base-uri 'self'",
]);
header('Content-Type: text/html; charset=UTF-8');
foreach (Theme_CSP::headers([]) as $name => $value) header($name . ': ' . $value);
$style = '#proof { color: rgb(12, 34, 56); }';
$script = "\nconsole.warn('trusted inline executed: ' + getComputedStyle(document.querySelector('#proof')).color);\n";
Theme_CSP::registerInline('style', $style);
$hooks['wp_inline_script_attributes'](['id' => 'app-js-js-extra'], $script);
$html = '<!doctype html><html><head><style>' . $style . '</style></head><body><p id="proof">CSP test</p><script>'
    . $script . '</script><script>console.warn("untrusted inline executed");</script></body></html>';
if (isset($_GET['static'])) {
    $name = $mode === 'report' ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
    $packed = wp_static_pack_csp($html, $html, [$name => Theme_CSP::policy()], '');
    $file = tempnam(sys_get_temp_dir(), 'csp-fixture-');
    file_put_contents($file, $packed);
    $cached = wp_static_read_csp_cache($file, sys_get_temp_dir());
    unlink($file);
    wp_static_send_csp_headers($cached['headers']);
    echo $cached['html'];
} else {
    ob_start([Theme_CSP::class, 'finalize'], 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE);
    echo $html;
}
