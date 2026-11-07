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
 * Retourne une URL HTTP(S) absolue valide ou une chaîne vide.
 */
function theme_http_url(mixed $value): string
{
    $url = is_scalar($value) || $value instanceof Stringable
        ? trim((string) $value)
        : '';

    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

    return in_array($scheme, ['http', 'https'], true) ? $url : '';
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

/**
 * Retourne les champs ACF d'une cible une seule fois par requête.
 *
 * @return array<string, mixed>
 */
function theme_get_fields(mixed $post_id = null, bool $format_value = true): array
{
    static $cache = [];

    if (!function_exists('get_fields')) {
        return [];
    }

    $target = $post_id;
    if ($target === null) {
        $queried_id = function_exists('get_queried_object_id') ? (int) get_queried_object_id() : 0;
        $target = $queried_id > 0 ? $queried_id : false;
    } elseif (is_string($target) && ctype_digit($target)) {
        $target = (int) $target;
    } elseif (is_object($target) && isset($target->ID)) {
        $target = (int) $target->ID;
    }

    if (!is_int($target) && !is_string($target) && !is_bool($target)) {
        return [];
    }

    $key = get_debug_type($target) . ':' . (string) $target . ':' . ($format_value ? 'formatted' : 'raw');
    if (!array_key_exists($key, $cache)) {
        $fields = get_fields($target, $format_value);
        $cache[$key] = is_array($fields) && !array_is_list($fields) ? $fields : [];
    }

    return $cache[$key];
}

/**
 * Construit le payload d'`anchor-nav` depuis une liste de strates ACF.
 *
 * Pour chaque strate, `options.name` devient le libellé visible (`label`) et
 * sa version normalisée par `sanitize_title()` devient la cible (`target`). La
 * cible est retournée sans caractère `#`, conformément au contrat du composant
 * `anchor-nav`.
 *
 * L'ordre initial des strates est conservé. Une entrée est ignorée lorsque la
 * liste, la strate, son tableau `options` ou son nom sont invalides, lorsque le
 * nom produit une cible vide, ou lorsqu'une strate précédente utilise déjà la
 * même cible. Cette normalisation correspond à celle du helper `options()`,
 * qui applique l'ID à la section rendue.
 *
 * La fonction est tolérante aux données ACF absentes ou mal formées et ne
 * produit aucun HTML.
 *
 * @param mixed $strates Liste ACF attendue sous la forme
 *   `list<array{options?: array{name?: scalar|Stringable}}>`. Toute autre valeur
 *   retourne une liste vide.
 * @return list<array{label: string, target: string}> Entrées prêtes à être
 *   transmises dans `component('anchor-nav', ['items' => $items])`.
 */
function theme_get_strate_anchor_items(mixed $strates): array
{
    if (!is_array($strates) || !array_is_list($strates)) {
        return [];
    }

    $items = [];
    $seen_targets = [];

    foreach ($strates as $strate) {
        if (!is_array($strate) || array_is_list($strate)) continue;

        $options = $strate['options'] ?? null;
        $options = is_array($options) && !array_is_list($options) ? $options : [];
        $name = $options['name'] ?? '';
        $name = is_scalar($name) || $name instanceof Stringable ? trim((string) $name) : '';
        $target = $name !== '' ? sanitize_title($name) : '';

        if ($target === '' || isset($seen_targets[$target])) continue;

        $seen_targets[$target] = true;
        $items[] = [
            'label' => $name,
            'target' => $target,
        ];
    }

    return $items;
}

/**
 * Extrait les IDs de posts d'une collection de cards.
 *
 * @param list<mixed> $items
 * @return list<int>
 */
function theme_collect_post_ids(array $items): array
{
    $ids = [];

    foreach ($items as $item) {
        $candidate = is_array($item) && !array_is_list($item)
            ? ($item['post_id'] ?? null)
            : $item;
        $id = filter_var($candidate, FILTER_VALIDATE_INT);

        if ($id !== false && $id > 0) {
            $ids[$id] = $id;
        }
    }

    return array_values($ids);
}

/**
 * Précharge les posts et leurs métadonnées en lots pour la requête courante.
 *
 * @param list<mixed> $items
 * @return list<int>
 */
function theme_prime_posts(array $items): array
{
    static $primed = [];

    $ids = theme_collect_post_ids($items);
    $pending = array_values(array_filter(
        $ids,
        static fn(int $id): bool => !isset($primed[$id])
    ));

    if ($pending === []) {
        return $ids;
    }

    if (function_exists('_prime_post_caches')) {
        _prime_post_caches($pending, false, false);
    }

    if (function_exists('update_meta_cache')) {
        update_meta_cache('post', $pending);
    }

    foreach ($pending as $id) {
        $primed[$id] = true;
    }

    return $ids;
}

/**
 * Prépare le payload commun aux cards à partir d'une publication.
 * Priorité par champ : groupe ACF `card`, champ métier déclaré pour le CPT,
 * puis donnée WordPress. Aucun groupe ni mapping n'est obligatoire.
 * Les posts absents, non publics ou protégés sont exclus, quels que soient les droits courants.
 *
 * @return array<string, mixed>|null
 */
function theme_post_card_item(int $post_id): ?array
{
    $post = $post_id > 0 ? get_post($post_id) : null;
    if (!$post || !is_post_publicly_viewable($post) || $post->post_password !== '') return null;

    $post_type = get_post_type($post_id);
    if (!is_string($post_type)) return null;

    $title = trim((string) get_the_title($post_id));

    $post_type_object = get_post_type_object($post_type);
    $type = trim((string) ($post_type_object?->labels->singular_name ?? ''));
    $item = [
        'post_id' => $post_id,
        'type' => $type !== '' ? $type : ucwords(str_replace(['-', '_'], ' ', $post_type)),
        'date' => get_the_date('', $post_id),
        'datetime' => get_the_date('Y-m-d', $post_id),
        'title' => $title,
        'text' => get_the_excerpt($post_id),
        'image' => get_post_thumbnail_id($post_id),
        'link' => ['url' => get_permalink($post_id), 'title' => $title],
    ];

    $fields = theme_get_fields($post_id);
    $card = $fields['card'] ?? null;
    $card = is_array($card) && !array_is_list($card) ? $card : [];
    $field_map = apply_filters('theme_card_field_map', [], $post_type, $post_id);
    $field_map = is_array($field_map) && !array_is_list($field_map) ? $field_map : [];
    $business_value = static function (string $name) use ($field_map, $fields): mixed {
        $field_name = $field_map[$name] ?? null;
        return is_string($field_name) && $field_name !== '' ? ($fields[$field_name] ?? null) : null;
    };

    foreach (['title', 'text'] as $name) {
        foreach ([$card[$name] ?? null, $business_value($name)] as $value) {
            if ((is_scalar($value) || $value instanceof Stringable) && trim((string) $value) !== '') {
                $item[$name] = trim((string) $value);
                break;
            }
        }
    }

    foreach ([$card['date'] ?? null, $business_value('date')] as $date) {
        if (!is_string($date)) continue;
        $date = trim($date);
        if (preg_match('/^\d{8}$/', $date) === 1) {
            $date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) continue;
        $timezone = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(date_default_timezone_get());
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        $errors = DateTimeImmutable::getLastErrors();
        if ($parsed !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $parsed->format('Y-m-d') === $date) {
            $item['datetime'] = $date;
            $item['date'] = function_exists('wp_date') && function_exists('get_option')
                ? wp_date((string) get_option('date_format'), $parsed->getTimestamp(), $timezone)
                : $parsed->format('d/m/Y');
            break;
        }
    }

    // Conserver les variantes responsive tout en fournissant l’image simple
    // attendue par les cards qui n’utilisent pas encore le groupe images.
    $item['images'] = [];
    foreach ([$card['images'] ?? null, ['desktop' => $card['image'] ?? null],
        $business_value('images'), ['desktop' => $business_value('image')],
        ['desktop' => $item['image']]] as $candidate) {
        if (!is_array($candidate)) continue;
        $images = [];
        foreach (['desktop', 'mobile'] as $variant) {
            $id = filter_var($candidate[$variant] ?? null, FILTER_VALIDATE_INT);
            if ($id !== false && $id > 0) $images[$variant] = $id;
        }
        if ($images === []) continue;
        $item['images'] = $images;
        $item['image'] = $images['desktop'] ?? $images['mobile'];
        break;
    }

    if ($item['title'] === '') return null;
    $item['link']['title'] = $item['title'];

    $filtered = apply_filters('theme_post_card_item', $item, $post_id, $post_type);
    return is_array($filtered) && !array_is_list($filtered) ? $filtered : $item;
}

/**
 * Prépare un lot de cards récentes pour les strates de remontée automatique.
 * Le dernier contenu supplémentaire sert uniquement à détecter « Voir plus ».
 *
 * @return array{items: list<array<string, mixed>>, has_more: bool}
 */
function theme_pushes_items(string $post_type, int $limit, int $page = 1, bool $lookahead = false): array
{
    if ($post_type === '' || !post_type_exists($post_type)) return ['items' => [], 'has_more' => false];

    $limit = max(1, min(12, $limit));
    $page = max(1, $page);
    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit + ($lookahead ? 1 : 0),
        'offset' => ($page - 1) * $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
        'suppress_filters' => false,
    ]);
    $has_more = $lookahead && count($posts) > $limit;
    $posts = array_slice($posts, 0, $limit);
    $ids = [];
    foreach ($posts as $post) {
        $id = is_object($post)
            ? (isset($post->ID) ? (int) $post->ID : 0)
            : filter_var($post, FILTER_VALIDATE_INT);
        if ($id > 0) $ids[$id] = $id;
    }
    theme_prime_posts(array_values($ids));

    return [
        'items' => array_values(array_filter(array_map('theme_post_card_item', array_values($ids)))),
        'has_more' => $has_more,
    ];
}

/** Identifiant stable selon l'ordre des strates rendues sur une page. */
function theme_pushes_more_instance_id(): int
{
    static $instance = 0;
    return ++$instance;
}

/**
 * Extrait uniquement les IDs placés dans un payload explicitement dédié aux images.
 *
 * @return list<int>
 */
function theme_collect_attachment_ids(mixed $images): array
{
    $ids = [];
    $collect = static function (mixed $value) use (&$ids, &$collect): void {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id !== false && $id > 0) {
            $ids[$id] = $id;
            return;
        }

        if (!is_array($value) || $value === []) {
            return;
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                $collect($item);
            }
            return;
        }

        foreach (['ID', 'id', 'image', 'images', 'desktop', 'mobile'] as $key) {
            if (array_key_exists($key, $value)) {
                $collect($value[$key]);
            }
        }
    };

    $collect($images);

    return array_values($ids);
}

/**
 * Précharge les pièces jointes connues et leurs métadonnées en lots.
 *
 * @return list<int>
 */
function theme_prime_attachments(mixed $images): array
{
    $ids = theme_collect_attachment_ids($images);

    if ($ids !== []) {
        theme_prime_posts($ids);
    }

    return $ids;
}

/**
 * Résout une URL de médiathèque une seule fois par requête.
 */
function theme_attachment_url_to_post_id(string $url): int
{
    static $cache = [];

    $url = trim($url);
    if ($url === '' || !function_exists('attachment_url_to_postid')) {
        return 0;
    }

    if (!array_key_exists($url, $cache)) {
        $cache[$url] = max(0, (int) attachment_url_to_postid($url));
    }

    return $cache[$url];
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
    $attributes = [];

    $name = $option_value($options['name'] ?? '');
    if ($name !== '') {
        $id = sanitize_title($name);
        if ($id !== '') {
            $attributes['id'] = $id;
        }
    }

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

    return html_attributes([
        'class' => sanitize_class_list($class_list),
        ...$attributes,
    ]);
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
        $fields = theme_get_fields();
        $hero = $fields["hero-{$name}"] ?? null;
        // Anciennes pages Flexible : les champs étaient contribués dans hero-page.
        if ($name === 'flexible'
            && !metadata_exists('post', get_queried_object_id(), 'hero-flexible')
            && is_array($fields['hero-page'] ?? null)) {
            $hero = $fields['hero-page'] ?? null;
        }
        // Une page jamais enregistrée peut afficher des valeurs par défaut dans
        // le BO sans posséder les références meta nécessaires à get_fields().
        if (!is_array($hero) && is_singular()
            && function_exists('acf_get_field_groups') && function_exists('acf_get_fields')) {
            $post_id = get_queried_object_id();
            foreach (acf_get_field_groups(['post_id' => $post_id]) as $group) {
                foreach (acf_get_fields($group) ?: [] as $field) {
                    if (($field['name'] ?? '') !== "hero-{$name}") continue;
                    $hero = get_field($field['key'], $post_id);
                    break 2;
                }
            }
        }
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

    // Compatibilité des contenus enregistrés avant l'harmonisation des slugs.
    $name = match ($name) {
        'text_image' => 'text-image',
        'text_animated' => 'text-animated',
        'content-columns' => 'columns-content',
        default => $name,
    };

    if ($name === '' || !preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
        return;
    }

    if ($args !== [] && array_is_list($args)) return;
    if (locate_template("template-parts/strates/strate-{$name}.php") === '') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            _doing_it_wrong(__FUNCTION__, "Unknown strate: {$name}", '2.0.0');
        }
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

    $item = theme_post_card_item($post_id);
    if ($item === null) return null;

    $fields = theme_get_fields($post_id);
    if (array_key_exists('image', $args) && !array_key_exists('images', $args)) {
        // Une surcharge explicite de l’image simple remplace aussi les variantes.
        $item['images'] = $args['image'];
    }
    $args = array_replace($fields, $item, $args);
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
