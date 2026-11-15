<?php
/** Local-only virtual routes; no WordPress pages or production endpoints. */
if (!defined('ABSPATH') || !defined('ENV_LOCAL') || ENV_LOCAL !== true) return;
function forge_ds_asset(string $file): string { return home_url('/design-system/assets/' . $file); }
function forge_ds_page(string $slug): string { return home_url('/design-system/' . $slug . '/'); }
add_action('template_redirect', static function (): void {
    $path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    $base = (string) parse_url(home_url('/design-system/'), PHP_URL_PATH);
    if (!str_starts_with($path, $base) && rtrim($path, '/') !== rtrim($base, '/')) return;
    $deny = static function (): never { status_header(404); nocache_headers(); exit; };
    $project = is_file(__DIR__ . '/project.json');
    if ($project) {
        $ticket = $_GET['forge_ds_ticket'] ?? null;
        if (is_string($ticket) && preg_match('/^[a-f0-9]{64}$/D', $ticket)) {
            $key = 'forge_ds_ticket_' . hash('sha256', $ticket);
            if (get_transient($key) !== 'ready') $deny();
            delete_transient($key);
            $session = bin2hex(random_bytes(32));
            set_transient('forge_ds_session_' . hash('sha256', $session), 'ready', HOUR_IN_SECONDS);
            setcookie('forge_ds', $session, ['expires'=>time()+HOUR_IN_SECONDS, 'path'=>$base, 'secure'=>is_ssl(), 'httponly'=>true, 'samesite'=>'Strict']);
            $redirect = remove_query_arg('forge_ds_ticket');
            wp_safe_redirect($redirect, 303); exit;
        }
        $session = $_COOKIE['forge_ds'] ?? '';
        if (!is_string($session) || !preg_match('/^[a-f0-9]{64}$/D', $session) || get_transient('forge_ds_session_' . hash('sha256', $session)) !== 'ready') $deny();
    }
    nocache_headers();
    header('X-Robots-Tag: noindex, nofollow', true);
    header('Referrer-Policy: no-referrer', true);
    $relative = trim(substr($path, strlen(rtrim($base, '/'))), '/');
    if (str_starts_with($relative, 'assets/')) {
        $file = substr($relative, 7);
        if (!preg_match('~^[a-zA-Z0-9._/-]+$~D', $file) || str_contains($file, '..')) $deny();
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $types = ['css'=>'text/css', 'js'=>'text/javascript', 'jpg'=>'image/jpeg', 'png'=>'image/png', 'svg'=>'image/svg+xml', 'woff2'=>'font/woff2'];
        $target = realpath(__DIR__ . '/' . $file);
        if (!isset($types[$extension]) || !$target || !str_starts_with($target, realpath(__DIR__) . '/') || !is_file($target)) $deny();
        status_header(200);
        header('Content-Type: ' . $types[$extension]); readfile($target); exit;
    }
    $families = require __DIR__ . '/families.php';
    $relative = $relative ?: 'components';
    if (!isset($families[$relative])) $deny();
    status_header(200);
    global $wp_query;
    $wp_query->is_404 = false;
    add_filter('body_class', static fn(array $classes): array => array_merge($classes, [$relative === 'common' ? 'forge-common-page' : 'page-template-design-system']));
    wp_enqueue_script_module('forge-design-system', forge_ds_asset('boot.js'), [], (string) filemtime(__DIR__ . '/boot.js'));
    require __DIR__ . '/' . $families[$relative]['template'];
    exit;
}, -100);
