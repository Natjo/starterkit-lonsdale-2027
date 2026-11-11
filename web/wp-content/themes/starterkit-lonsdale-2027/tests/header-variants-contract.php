<?php
/** PHP rendering contracts for the simple header variants, without a database. */
declare(strict_types=1);
class Walker_Nav_Menu {}
$menus = ['menu-header' => 1, 'menu-header-cta' => 2, 'menu-secondary' => 3, 'menu-header-utility' => 4, 'menu-header-tools' => 5];
$action_items = [];
$menu_calls = [];
$component_calls = [];
function __(string $text, string $domain): string { return $text; }
function esc_attr(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_url(mixed $value): string { return esc_attr($value); }
function esc_html(mixed $value): string { return esc_attr($value); }
function home_url(string $path): string { return 'https://example.test' . $path; }
function get_bloginfo(string $key): string { return 'Demo'; }
function has_nav_menu(string $location): bool { return isset($GLOBALS['menus'][$location]); }
function get_nav_menu_locations(): array { return $GLOBALS['menus']; }
function wp_get_nav_menu_items(int $id): array|false { return $GLOBALS['action_items']; }
function get_search_query(): string { return ''; }
function wp_nav_menu(array $args): void {
    $GLOBALS['menu_calls'][] = $args;
    echo '<ul class="' . esc_attr($args['menu_class']) . '"><li><a href="/page/">Page</a></li></ul>';
}
function component(string $name, array $params): void { $GLOBALS['component_calls'][] = [$name, $params]; }
function check(bool $value, string $message): void { if (!$value) throw new RuntimeException($message); }
function render_header(string $name): string {
    $GLOBALS['menu_calls'] = $GLOBALS['component_calls'] = [];
    ob_start();
    require dirname(__DIR__) . '/template-parts/common/' . $name . '.php';
    return ob_get_clean();
}
foreach (['header-nav', 'header-nav-cta', 'header-nav-centered', 'header-nav-minimal', 'header-nav-search', 'header-nav-topbar', 'header-nav-commerce'] as $name) {
    $html = render_header($name);
    check(substr_count($html, 'id="nav"') === 1, "$name must have one navigation panel");
    check(str_contains($html, 'aria-controls="nav"'), "$name burger controls the panel");
    foreach ($menu_calls as $call) check($call['depth'] === 1, "$name renders only the first level");
    if (in_array($name, ['header-nav-cta', 'header-nav-centered', 'header-nav-minimal'], true)) {
        check(!in_array('search', array_column($component_calls, 0), true), "$name must not render a search field");
    }
    if (in_array($name, ['header-nav-search', 'header-nav-topbar', 'header-nav-commerce'], true)) {
        check(count(array_filter($component_calls, static fn ($call) => $call[0] === 'search')) === 1, "$name renders one real search form");
    }
    if ($name === 'header-nav-topbar') {
        check(array_column($menu_calls, 'theme_location') === ['menu-header-utility', 'menu-header'], 'Utility menu precedes the main menu');
    }
    if ($name === 'header-nav-commerce') {
        check(array_column($menu_calls, 'theme_location') === ['menu-header', 'menu-header-tools'], 'Commerce declares categories and actual shop links');
    }
    if ($name === 'header-nav-centered') {
        check(array_column($menu_calls, 'theme_location') === ['menu-header', 'menu-secondary'], 'Centered menus keep their order');
    }
}
$action_items = [
    (object) ['menu_item_parent' => 1, 'url' => '/child/', 'title' => 'Child', 'target' => ''],
    (object) ['menu_item_parent' => 0, 'url' => '/contact/', 'title' => 'Contact', 'target' => '_blank'],
    (object) ['menu_item_parent' => 0, 'url' => '/other/', 'title' => 'Other', 'target' => ''],
];
render_header('header-nav-cta');
$buttons = array_values(array_filter($component_calls, static fn ($call) => $call[0] === 'btn'));
check(count($buttons) === 1, 'CTA renders only one action');
check($buttons[0][1]['link'] === ['url' => '/contact/', 'title' => 'Contact', 'target' => '_blank'], 'CTA uses the first top-level link and preserves its target');
$action_items = false;
render_header('header-nav-cta');
check(!in_array('btn', array_column($component_calls, 0), true), 'Missing action menu does not produce a button');
unset($menus['menu-secondary']);
render_header('header-nav-centered');
check(count($menu_calls) === 1, 'Centered header handles an unassigned secondary menu');
echo "Header variant contracts passed.\n";
