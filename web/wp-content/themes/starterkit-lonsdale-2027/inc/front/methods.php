<?php

/**
 * Helpers communs aux templates et composants.
 *
 * Ce fichier regroupe les fonctions utilitaires génériques du starter:
 * normalisation d'arguments, sécurisation de slugs, génération d'attributs
 * HTML et raccourcis de rendu comme component(), common(), hero(),
 * strate() ou card().
 *
 * Ces helpers restent volontairement indépendants de WordPress afin de
 * pouvoir être repris dans un thème ou dans le starter sans changer les
 * composants qui les utilisent.
 */

function normalize_args(mixed $args, array $defaults = []): array
{
    return array_replace($defaults, is_array($args) ? $args : []);
}

/**
 * Passe des arguments à un template chargé via get_template_part().
 *
 * Ne pas utiliser le nom `args` : load_template() déclare déjà $args = []
 * et extract(..., EXTR_SKIP) ignore la query var du même nom.
 */
function set_template_args(array $args): void
{
    set_query_var('template_args', $args);
}

function get_template_args(array $defaults = []): array
{
    return normalize_args(get_query_var('template_args') ?: [], $defaults);
}

function normalize_template_slug(mixed $slug): string
{
    $slug = trim(str_replace('\\', '/', (string) $slug), '/');
    $slug = preg_replace('#\.php$#', '', $slug);
    $slug = preg_replace('#/+#', '/', $slug);

    if ($slug === '' || has_unsafe_path_segments($slug)) {
        return '';
    }

    return preg_match('#^[A-Za-z0-9_/-]+$#', $slug) ? $slug : '';
}

function has_unsafe_path_segments(mixed $path): bool
{
    if (str_contains((string) $path, "\0")) {
        return true;
    }

    foreach (explode('/', (string) $path) as $segment) {
        if ($segment === '..') {
            return true;
        }
    }

    return false;
}

function is_safe_template_file(mixed $path, mixed $directory): bool
{
    if (!is_file($path)) {
        return false;
    }

    $realPath = realpath($path);
    $realDirectory = realpath($directory);

    return $realPath !== false
        && $realDirectory !== false
        && str_starts_with($realPath, rtrim($realDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

function sanitize_class_list(mixed $classes): string
{
    if (is_array($classes)) {
        $classes = implode(' ', array_filter($classes, static fn($class) => is_scalar($class)));
    }

    $classes = preg_split('/\s+/', (string) $classes, -1, PREG_SPLIT_NO_EMPTY);
    $classes = array_map('sanitize_html_class', $classes);
    $classes = array_filter($classes, static fn($class) => $class !== '');

    return implode(' ', array_unique($classes));
}

function options(mixed $classes, array $args = []): string
{


    $options = !empty($args['options']) && is_array($args['options']) ? $args['options'] : [];

    if (empty($options)) {
        return html_attributes(['class' => sanitize_class_list($classes)]);
    }

    $class_list = is_array($classes) ? $classes : [$classes];
    $option_value = static fn($value): string => is_scalar($value) ? trim((string) $value) : '';

    $container = $option_value($options['container'] ?? '');
    if ($container !== '') {
        $class_list[] = 'ctr-' . $container;
    }

    $margin = !empty($options['margin']) && is_array($options['margin']) ? $options['margin'] : [];
    $margin_bottom = $option_value($margin['bottom'] ?? '');
    $margin_top = $option_value($margin['top'] ?? '');

    if ($margin_bottom === '') {
        $class_list[] = 'mb-0';
    } elseif ($margin_bottom !== 'md') {
        $class_list[] = 'mb-' . $margin_bottom;
    }

    if ($margin_top !== '') {
        $class_list[] = 'mt-' . $margin_top;
    }

    $background = !empty($options['background']) && is_array($options['background']) ? $options['background'] : [];
    if (!empty($background['hasbackground'])) {
        $color = $option_value($background['color'] ?? '');
        if ($color !== '') {
            $class_list[] = 'bg-' . $color;
        }

        $padding = !empty($background['padding']) && is_array($background['padding']) ? $background['padding'] : [];
        foreach (['top' => 'pt', 'bottom' => 'pb'] as $key => $prefix) {
            $value = $option_value($padding[$key] ?? '');
            if ($value !== '' && $value !== 'md') {
                $class_list[] = $prefix . '-' . $value;
            }
        }
    }

    return html_attributes(['class' => sanitize_class_list($class_list)]);
}

function component(string $name, array $args = []): void
{
    $name = trim($name);
    $folder = str_replace('_', '-', $name);

    if ($folder === '' || !preg_match('/^[A-Za-z0-9-]+$/', $folder)) {
        if (defined('WP_DEBUG') && WP_DEBUG && function_exists('_doing_it_wrong')) {
            _doing_it_wrong(
                __FUNCTION__,
                sprintf('Le composant « %s » est introuvable ou son nom est invalide.', esc_html($name)),
                '1.0.0'
            );
        }
        return;
    }

    if (function_exists('theme_queue_module_assets')) {
        theme_queue_module_assets("components/{$folder}/{$folder}");
    }

    $previousParams = get_query_var('params', null);
    set_query_var('params', $args);

    try {
        $loaded = get_template_part("template-parts/components/{$folder}");
    } finally {
        set_query_var('params', $previousParams);
    }

    if ($loaded === false && defined('WP_DEBUG') && WP_DEBUG && function_exists('_doing_it_wrong')) {
        _doing_it_wrong(
            __FUNCTION__,
            sprintf('Le composant « %s » est introuvable ou son nom est invalide.', esc_html($name)),
            '1.0.0'
        );
    }
}

function common(string $name): void
{
    $name = trim($name);
    if ($name === '' || str_contains($name, '/') || str_contains($name, '..')) return;

    theme_queue_module_assets("common/{$name}/{$name}");
    get_template_part("template-parts/common/{$name}");
}

/**
 * Affiche un hero. Sans arguments, ses données viennent du champ ACF `hero-{$name}`.
 * Un tableau fourni, même vide, est transmis tel quel au template.
 *
 * @param array<string, mixed>|null $args
 */
function hero(string $name, ?array $args = null): void
{
    $name = trim($name);

    if ($name === '' || !preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
        return;
    }

    $template = "template-parts/heros/hero-{$name}";

    if (locate_template("{$template}.php") === '') {
        return;
    }

    if ($args === null) {
        $hero = get_field("hero-{$name}");
        $args = is_array($hero) ? $hero : [];
    }

    theme_queue_hero_critical($name);

    $previousArgs = get_query_var('template_args', null);
    set_template_args($args);

    try {
        get_template_part($template);
    } finally {
        set_query_var('template_args', $previousArgs);
    }
}

/**
 * @param array<string, mixed> $fields
 */
function theme_resolve_hero_name(array $fields, string $default = 'homepage'): string
{
    $hero = $fields['hero'] ?? null;

    if (is_string($hero)) {
        $name = trim($hero);

        if ($name !== '' && preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
            return $name;
        }
    }

    if (is_array($hero) && isset($hero['layout']) && is_string($hero['layout'])) {
        $name = trim($hero['layout']);

        if ($name !== '' && preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
            return $name;
        }
    }

    return $default;
}

/**
 * Queue hero CSS/fonts avant get_header() (template_redirect).
 *
 * @param array<string, mixed> $fields
 */
function theme_prescan_hero_assets(array $fields): void
{
    $names = [];

    foreach ($fields as $fieldName => $value) {
        if (!is_string($fieldName) || !str_starts_with($fieldName, 'hero-')) {
            continue;
        }

        $name = substr($fieldName, strlen('hero-'));

        if (
            $value !== null
            && $value !== false
            && $value !== ''
            && $value !== []
            && preg_match('#^[A-Za-z0-9_-]+$#', $name)
        ) {
            $names[$name] = true;
        }
    }

    $resolvedName = theme_resolve_hero_name($fields, '');

    if ($resolvedName !== '') {
        $names[$resolvedName] = true;
    }

    foreach (array_keys($names) as $name) {
        theme_queue_hero_critical($name);
    }
}


function strate(string $name, array $args = []): void
{
    $name = trim($name);

    if ($name === '' || !preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
        return;
    }

    theme_queue_module_assets("strates/strate-{$name}/strate-{$name}");
    $previousArgs = get_query_var('template_args', null);
    set_template_args($args);

    try {
        get_template_part("template-parts/strates/strate-{$name}");
    } finally {
        set_query_var('template_args', $previousArgs);
    }
}

function resolve_card_name(mixed $name): string
{
    static $cache = [];

    $name = (is_scalar($name) && !is_bool($name)) || $name instanceof Stringable
        ? trim((string) $name)
        : '';
    if ($name === '') return '';

    $name = str_starts_with($name, 'card-') ? $name : 'card-' . $name;
    if (!preg_match('/^card-[A-Za-z0-9_-]+$/', $name)) return '';

    if (array_key_exists($name, $cache)) {
        return $cache[$name];
    }

    $template = "template-parts/cards/{$name}.php";
    $cache[$name] = !function_exists('locate_template') || locate_template($template) !== ''
        ? $name
        : '';

    return $cache[$name];
}

function is_valid_card_data(mixed $data): bool
{
    if (is_int($data)) {
        return $data > 0;
    }

    if (!is_array($data) || $data === [] || array_is_list($data)) {
        return false;
    }

    return !array_key_exists('post_id', $data)
        || (is_int($data['post_id']) && $data['post_id'] > 0);
}

/**
 * @return array<string, mixed>|null
 */
function resolve_card_data(mixed $data): ?array
{
    if (!is_valid_card_data($data)) {
        return null;
    }

    $args = is_array($data) ? $data : [];
    $post_id = is_int($data) ? $data : ($args['post_id'] ?? 0);

    if ($post_id <= 0) {
        return $args;
    }

    $fields = function_exists('get_fields') ? get_fields($post_id) : [];
    $fields = is_array($fields) ? $fields : [];
    $args = array_replace($fields, $args);
    $args['post_id'] = $post_id;

    return $args;
}

function card(mixed $name, mixed $args = []): void
{
    $name = resolve_card_name($name);
    $args = resolve_card_data($args);

    if ($name === '' || $args === null) return;

    theme_queue_module_assets("cards/{$name}/{$name}");
    $previousArgs = function_exists('get_query_var') ? get_query_var('template_args', null) : null;
    set_template_args($args);

    try {
        get_template_part("template-parts/cards/{$name}");
    } finally {
        set_query_var('template_args', $previousArgs);
    }
}

function youtube_id_from_url(mixed $url): string
{
    $parts = parse_url((string) $url);
    $id = "";

    if (isset($parts['query'])) {
        parse_str($parts['query'], $qs);
        if (isset($qs['v'])) {
            $id = (string) $qs['v'];
        } else if (isset($qs['vi'])) {
            $id = (string) $qs['vi'];
        }
    }

    if ($id === '' && isset($parts['path'])) {
        $path = explode('/', trim($parts['path'], '/'));
        $id = (string) $path[count($path) - 1];
    }

    return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : "";
}

/**
 * Génère des attributs à partir d'un tableau uniquement.
 *
 * Les chaînes HTML brutes, gestionnaires d'événements et valeurs complexes
 * sont volontairement ignorés.
 *
 * @param list<string> $reserved
 */
function html_attributes(mixed $attributes, array $reserved = []): string
{
    if (!is_array($attributes)) {
        return '';
    }

    $html = [];
    $reserved = array_map(static fn(mixed $name): string => strtolower((string) $name), $reserved);

    foreach ($attributes as $name => $value) {
        $name = strtolower((string) $name);

        if (!preg_match('/^[a-z][a-z0-9_:-]*$/', $name)
            || str_starts_with($name, 'on')
            || $name === 'srcdoc'
            || in_array($name, $reserved, true)
            || $value === null
            || $value === false) {
            continue;
        }

        if ($value === true) {
            $html[] = esc_attr($name);
            continue;
        }

        if (!is_scalar($value) && !$value instanceof Stringable) {
            continue;
        }

        $html[] = esc_attr($name) . '="' . esc_attr((string) $value) . '"';
    }

    return implode(' ', $html);
}

/**
 * Normalise les données communes aux composants link et btn en mode lien.
 *
 * @return array{
 *     url: string,
 *     title: string,
 *     target: string,
 *     rel: string,
 *     disabled: bool,
 *     attributes: array<string, mixed>
 * }
 */
function normalize_action_link(mixed $link, mixed $attributes = []): array
{
    $link = is_array($link) ? $link : [];
    $attributes = is_array($attributes) ? $attributes : [];
    $to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
        ? trim((string) $value)
        : '';

    $url = esc_url($to_string($link['url'] ?? ''));
    $title = $to_string($link['title'] ?? '');
    $target = strtolower($to_string($link['target'] ?? ''));
    $target = in_array($target, ['_self', '_blank', '_parent', '_top'], true) ? $target : '';
    $rel = '';
    $disabled = false;

    foreach (array_keys($attributes) as $name) {
        $normalized_name = strtolower((string) $name);

        if ($normalized_name === 'rel') {
            $rel = $to_string($attributes[$name]);
            unset($attributes[$name]);
            continue;
        }

        if ($normalized_name === 'aria-disabled') {
            $disabled = filter_var($attributes[$name], FILTER_VALIDATE_BOOL);
        }

        if (in_array($normalized_name, ['class', 'href', 'target', 'type'], true)) {
            unset($attributes[$name]);
        }
    }

    $rel_tokens = preg_split('/\s+/', strtolower($rel), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $rel_tokens = array_filter($rel_tokens, static fn(string $token): bool => preg_match('/^[a-z0-9_-]+$/', $token) === 1);

    if ($target === '_blank') {
        $rel_tokens[] = 'noopener';
        $rel_tokens[] = 'noreferrer';
    }

    return [
        'url' => $url,
        'title' => $title,
        'target' => $target,
        'rel' => implode(' ', array_unique($rel_tokens)),
        'disabled' => $disabled,
        'attributes' => $attributes,
    ];
}

/*
 reset wordpress
*/

/**
 * Supprime les styles/scripts inline injectés par WordPress (front uniquement).
 */
function theme_strip_registered_inline_assets(): void
{
    if (is_admin()) {
        return;
    }

    global $wp_styles, $wp_scripts;

    if ($wp_styles instanceof WP_Styles) {
        foreach ($wp_styles->registered as $handle => $style) {
            if ($handle === 'app-css' || str_starts_with((string) $handle, 'split-')) {
                continue;
            }

            $wp_styles->registered[$handle]->extra['after'] = [];
            $wp_styles->registered[$handle]->extra['before'] = [];
        }
    }

    if ($wp_scripts instanceof WP_Scripts) {
        foreach ($wp_scripts->registered as $handle => $script) {
            if ($handle === 'app-js') {
                continue;
            }

            $wp_scripts->registered[$handle]->extra['after'] = [];
            $wp_scripts->registered[$handle]->extra['before'] = [];
        }
    }
}

add_action('init', function (): void {
    if (is_admin()) {
        return;
    }

    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
    remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

    remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles');
    remove_action('wp_enqueue_scripts', 'wp_enqueue_block_support_styles');
    remove_action('wp_enqueue_scripts', 'wp_enqueue_stored_styles');

    remove_action('wp_head', 'wp_custom_css_cb', 101);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

    if (function_exists('wp_print_speculation_rules')) {
        remove_action('wp_footer', 'wp_print_speculation_rules');
    }
}, 20);

add_filter('wp_get_global_stylesheet', static function ($stylesheet = '') {
    return is_admin() ? $stylesheet : '';
}, 10, 1);

add_filter('wp_get_global_styles_svg_filters', static function ($svg = '') {
    return is_admin() ? $svg : '';
}, 10, 1);

add_filter('wp_speculation_rules_configuration', static function ($config) {
    if (is_admin()) {
        return $config;
    }

    return null;
});

add_action('wp_enqueue_scripts', 'theme_strip_registered_inline_assets', PHP_INT_MAX);
