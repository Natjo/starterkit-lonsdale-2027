<?php

declare(strict_types=1);

define('WP_DEBUG', true);
define('WPSEO_VERSION', 'test');
define('THEME_ASSETS', 'https://example.test/assets');
define('THEME_DIR', dirname(__DIR__) . '/');

/** @var array<string, mixed> $query_vars */
$query_vars = [];
/** @var list<string> $queued_modules */
$queued_modules = [];
/** @var list<string> $loaded_templates */
$loaded_templates = [];
/** @var list<string> $doing_it_wrong */
$doing_it_wrong = [];
$rendering_component_fixture = false;
$get_posts_result = [];
$get_posts_calls = [];
$post_types_by_id = [];
$registered_post_types = ['news'];
$meta_cache_calls = [];
$post_cache_calls = [];
$get_fields_calls = [];
$attachment_url_calls = [];
$queried_object_id = 7002;

function esc_attr(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_html(mixed $value): string
{
    return esc_attr($value);
}

function __(string $text, ?string $domain = null): string
{
    return $text;
}

function home_url(string $path = ''): string
{
    return 'https://example.test' . $path;
}

function get_template_directory_uri(): string
{
    return 'https://example.test/theme';
}

function is_admin(): bool
{
    return false;
}

function is_front_page(): bool
{
    return false;
}

function is_404(): bool
{
    return false;
}

function get_queried_object_id(): int
{
    global $queried_object_id;
    return $queried_object_id;
}

function is_post_type_archive(): bool
{
    return false;
}

function get_queried_object(): mixed
{
    return null;
}

function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
{
    return $single ? '' : [];
}

function did_action(string $hook_name): int
{
    return 0;
}

function paginate_links(array $args = []): array|false
{
    $total = (int) ($args['total'] ?? 1);
    $current = (int) ($args['current'] ?? 1);
    if ($total <= 1) return false;

    $url = static fn(int $page): string => isset($args['base'])
        ? str_replace('%#%', (string) $page, (string) $args['base'])
        : '?paged=' . $page;

    return [
        '<a class="prev page-numbers" href="' . $url(max(1, $current - 1)) . '">' . ($args['prev_text'] ?? '') . '</a>',
        '<span aria-current="page" class="page-numbers current">' . $current . '</span>',
        '<a class="next page-numbers" href="' . $url(min($total, $current + 1)) . '">' . ($args['next_text'] ?? '') . '</a>',
    ];
}

function add_query_arg(string $key, mixed $value, string $url = 'https://example.test/current/'): string
{
    return rtrim($url, '?&') . (str_contains($url, '?') ? '&' : '?')
        . rawurlencode($key) . '=' . rawurlencode((string) $value);
}

function wp_unique_id(string $prefix = ''): string
{
    static $counter = 0;
    return $prefix . ++$counter;
}

function esc_url(mixed $value): string
{
    $value = trim((string) $value);
    if ($value === '' || preg_match('/^(?:javascript|data):/i', $value)) {
        return '';
    }

    return esc_attr($value);
}

function esc_url_raw(mixed $value): string
{
    return html_entity_decode(esc_url($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function wp_strip_all_tags(mixed $value): string
{
    return strip_tags((string) $value);
}

function sanitize_html_class(mixed $value): string
{
    return preg_replace('/[^A-Za-z0-9_-]/', '', (string) $value) ?? '';
}

function sanitize_title(mixed $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $value) ?: '';
    $value = strtolower(strip_tags($value));
    $value = preg_replace('/[^a-z0-9 _-]/', '', $value) ?? '';

    return trim((string) preg_replace('/[\s_-]+/', '-', $value), '-');
}

function sanitize_key(mixed $value): string
{
    return strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) $value) ?? '');
}

function sanitize_text_field(mixed $value): string
{
    return trim(strip_tags((string) $value));
}

function wp_unslash(mixed $value): mixed
{
    return is_string($value) ? stripslashes($value) : $value;
}

function get_post_type(int $post_id): string|false
{
    global $post_types_by_id;
    return $post_types_by_id[$post_id] ?? false;
}

function post_type_exists(string $post_type): bool
{
    global $registered_post_types;
    return in_array($post_type, $registered_post_types, true);
}

function get_post_type_object(string $post_type): mixed
{
    return null;
}

function get_posts(array $args = []): array
{
    global $get_posts_calls, $get_posts_result;
    $get_posts_calls[] = $args;
    return $get_posts_result;
}

function update_meta_cache(string $meta_type, array $object_ids): array|false
{
    global $meta_cache_calls;
    $meta_cache_calls[] = [$meta_type, $object_ids];
    return [];
}

function _prime_post_caches(array $ids, bool $update_term_cache = true, bool $update_meta_cache = true): void
{
    global $post_cache_calls;
    $post_cache_calls[] = [$ids, $update_term_cache, $update_meta_cache];
}

function wp_kses_post(mixed $value): string
{
    return strip_tags((string) $value, '<p><br><strong><em><a>');
}

function wp_kses(mixed $value, array $allowed_html): string
{
    $allowed_tags = '';
    foreach (array_keys($allowed_html) as $tag) {
        $allowed_tags .= '<' . $tag . '>';
    }

    return strip_tags((string) $value, $allowed_tags);
}

function lsd_get_thumb(mixed $input, mixed $size = 'full'): array
{
    $GLOBALS['lsd_thumb_calls'][] = [$input, $size];

    $key = (string) $input . '|' . (is_array($size) ? implode('x', $size) : (string) $size);
    if (isset($GLOBALS['lsd_thumb_responses'][$key])) {
        return $GLOBALS['lsd_thumb_responses'][$key];
    }

    return [(string) $input, 1200, 800, 'Texte alternatif'];
}

function attachment_url_to_postid(string $url): int
{
    $GLOBALS['attachment_url_calls'][] = $url;
    return (int) ($GLOBALS['attachment_ids_by_url'][$url] ?? 0);
}

function lsd_image_is_webp_convertible(string $src): bool
{
    return !empty($GLOBALS['lsd_webp_convertible']);
}

function lsd_image_webp_src(mixed $src, mixed $size = 'full'): string
{
    $key = (string) $src . '|' . (is_array($size) ? implode('x', $size) : (string) $size);
    return (string) ($GLOBALS['lsd_webp_sources'][$key] ?? '');
}

function lsd_image_file_dimensions(string $src): array
{
    return $GLOBALS['lsd_image_dimensions'][$src] ?? [];
}

function wp_check_filetype(string $path): array
{
    return ['type' => 'image/jpeg'];
}

function get_fields(mixed $post_id = null, bool $format_value = true): array|false
{
    global $get_fields_calls, $card_fields_by_id, $business_fields_by_id;
    $get_fields_calls[] = [$post_id, $format_value];

    if ($post_id === 'option') {
        return ['prefooter' => ['title' => 'Préfooter']];
    }

    if (!is_int($post_id) || $post_id <= 0) return false;

    $fields = [
        'title' => 'Actualité ' . $post_id,
        'text' => '<p>Contenu du post ' . $post_id . '</p>',
        'source' => 'Source ' . $post_id,
    ];
    if (isset($card_fields_by_id[$post_id])) $fields['card'] = $card_fields_by_id[$post_id];
    if (isset($business_fields_by_id[$post_id])) $fields = array_merge($fields, $business_fields_by_id[$post_id]);
    return $fields;
}

function get_field(string $selector, mixed $post_id = null): mixed
{
    if ($selector !== 'hero-news' || !is_int($post_id) || $post_id <= 0) {
        return false;
    }

    return [
        'date' => '20/07/2026',
        'title' => 'Actualité ' . $post_id,
        'images' => ['desktop' => 900 + $post_id],
    ];
}

function get_the_title(int $post_id = 0): string
{
    return 'Titre WordPress ' . $post_id;
}

function get_the_date(string $format = '', int $post_id = 0): string
{
    return $format === 'Y-m-d' ? '2026-07-20' : '20/07/2026';
}

function get_the_excerpt(int $post_id = 0): string
{
    return 'Extrait ' . $post_id;
}

function get_post_thumbnail_id(int $post_id = 0): int
{
    return 0;
}

function get_permalink(int $post_id = 0): string
{
    return 'https://example.test/news/' . $post_id . '/';
}

function theme_enqueue_image_preload(mixed $input, mixed $size = 'full'): void
{
}

function theme_queue_module_assets(string $module): void
{
    global $queued_modules;
    $queued_modules[] = $module;
}

function set_query_var(string $name, mixed $value): void
{
    global $query_vars;
    $query_vars[$name] = $value;
}

function get_query_var(string $name, mixed $default = ''): mixed
{
    global $query_vars;

    return array_key_exists($name, $query_vars) ? $query_vars[$name] : $default;
}

function locate_template(string|array $template_names, bool $load = false, bool $load_once = true, array $args = []): string
{
    foreach ((array) $template_names as $template_name) {
        $path = dirname(__DIR__) . '/' . ltrim($template_name, '/');
        if (is_file($path)) return $path;
    }

    return '';
}

function get_template_part(string $slug, ?string $name = null, array $args = []): ?bool
{
    global $loaded_templates, $query_vars, $rendering_component_fixture;
    $loaded_templates[] = $slug;

    $is_fixture = str_starts_with($slug, 'template-parts/components/')
        || str_starts_with($slug, 'template-parts/cards/');

    if ($rendering_component_fixture && $is_fixture && is_file(dirname(__DIR__) . "/{$slug}.php")) {
        $params = $query_vars['params'] ?? [];
        include dirname(__DIR__) . "/{$slug}.php";
    }

    return null;
}

function _doing_it_wrong(string $function, string $message, ?string $version): void
{
    global $doing_it_wrong;
    $doing_it_wrong[] = $message;
}

function add_action(mixed ...$args): void
{
}

function add_filter(mixed ...$args): void
{
}

function apply_filters(string $hook_name, mixed $value, mixed ...$args): mixed
{
    if ($hook_name === 'theme_card_field_map') {
        return $GLOBALS['card_field_map_by_type'][$args[0] ?? ''] ?? [];
    }
    return $value;
}

require_once dirname(__DIR__) . '/inc/front/methods.php';
require_once dirname(__DIR__) . '/inc/front/components.php';
require_once dirname(__DIR__) . '/inc/front/filters.php';
require_once dirname(__DIR__) . '/inc/front/contact-form.php';
require_once dirname(__DIR__) . '/inc/front/breadcrumb.php';
require_once dirname(__DIR__) . '/inc/config/seo.php';
require_once dirname(__DIR__) . '/inc/config/acf.php';

/** @var list<string> $failures */
$failures = [];

$assert_same = static function (string $label, mixed $expected, mixed $actual) use (&$failures): void {
    if ($expected !== $actual) {
        $failures[] = sprintf(
            "%s\nAttendu: %s\nObtenu: %s",
            $label,
            var_export($expected, true),
            var_export($actual, true)
        );
    }
};

$assert_contains = static function (string $label, string $needle, string $actual) use (&$failures): void {
    if (!str_contains($actual, $needle)) {
        $failures[] = sprintf('%s: « %s » est absent de « %s »', $label, $needle, $actual);
    }
};

$assert_not_contains = static function (string $label, string $needle, string $actual) use (&$failures): void {
    if (str_contains($actual, $needle)) {
        $failures[] = sprintf('%s: « %s » ne devrait pas être présent dans « %s »', $label, $needle, $actual);
    }
};

$assert_same('component_args fusionne un payload associatif', ['name' => 'Test'], component_args(['name' => 'Test'], ['name' => '']));
$assert_same('component_args refuse un payload positionnel', null, component_args(['Test']));
$assert_same('component_string normalise une valeur scalaire', 'Test', component_string(' Test '));
$assert_same('component_string refuse une structure', 'fallback', component_string(['Test'], 'fallback'));
$assert_same('component_bool comprend les chaînes booléennes', false, component_bool('false', true));
$assert_same('component_bool conserve le défaut pour une valeur invalide', true, component_bool('invalid', true));
$assert_same('component_list conserve une liste', ['first', 'second'], component_list(['first', 'second']));
$assert_same('component_list refuse un tableau associatif', [], component_list(['name' => 'Test']));
$assert_same('component_options normalise les options', [[
    'value' => 'news',
    'label' => 'News',
    'alt' => 'Latest news',
    'selected' => true,
    'disabled' => false,
]], component_options([[
    'value' => ' news ',
    'label' => ' News ',
    'alt' => ' Latest news ',
    'selected' => 'true',
]]));
$assert_same('component_options ignore les options incomplètes', [], component_options([['value' => 'news']]));
$assert_same('component_id conserve un identifiant valide', 'section-test', component_id('section-test', 'component-'));
$assert_contains('component_id génère un identifiant préfixé', 'component-', component_id('', 'component-'));
$assert_same('theme_http_url conserve HTTPS', 'https://example.test/path', theme_http_url(' https://example.test/path '));
$assert_same('theme_http_url refuse les protocoles dangereux', '', theme_http_url('javascript:alert(1)'));

$previous_contact_status = $_GET['contact'] ?? null;
$_GET['contact'] = 'sent';
$contact_form = new Theme_Contact_Form([
    'action_url' => 'https://example.test/contact/',
    'recipient' => 'contact@example.test',
]);
$contact_form->handle();
$assert_same('Theme_Contact_Form détecte le statut envoyé', true, $contact_form->is_sent());
$assert_same('Theme_Contact_Form expose son URL d’action', 'https://example.test/contact/', $contact_form->action_url());
$assert_same('Theme_Contact_Form initialise les champs à vide', '', $contact_form->value('name'));
$assert_same('Theme_Contact_Form ignore les champs inconnus', '', $contact_form->value('unknown'));
$assert_same('Theme_Contact_Form reste sans erreur hors soumission', '', $contact_form->error());
$assert_same('Theme_Contact_Form expose le message de succès', 'Votre message a bien été envoyé.', $contact_form->success_message());
$assert_same('Theme_Contact_Form désactive AJAX par défaut', false, $contact_form->is_ajax());
$ajax_contact_form = new Theme_Contact_Form([
    'action_url' => 'https://example.test/contact/',
    'recipient' => 'contact@example.test',
    'ajax' => true,
]);
$assert_same('Theme_Contact_Form accepte AJAX explicitement', true, $ajax_contact_form->is_ajax());
if ($previous_contact_status === null) {
    unset($_GET['contact']);
} else {
    $_GET['contact'] = $previous_contact_status;
}

$render = static function (string $component, array $values): string {
    global $rendering_component_fixture;

    $params = $values;
    $rendering_component_fixture = true;
    ob_start();
    include dirname(__DIR__) . "/template-parts/components/{$component}.php";
    $rendering_component_fixture = false;

    return trim((string) ob_get_clean());
};

$render_strate = static function (string $strate, array $values): string {
    global $rendering_component_fixture;

    $previous_args = get_query_var('template_args', null);
    set_template_args($values);
    $rendering_component_fixture = true;
    ob_start();

    try {
        include dirname(__DIR__) . "/template-parts/strates/strate-{$strate}.php";
    } finally {
        $rendering_component_fixture = false;
        set_query_var('template_args', $previous_args);
    }

    return trim((string) ob_get_clean());
};

$render_card = static function (string $card_name, mixed $values): string {
    global $rendering_component_fixture;

    $rendering_component_fixture = true;
    ob_start();

    try {
        card($card_name, $values);
    } finally {
        $rendering_component_fixture = false;
    }

    return trim((string) ob_get_clean());
};

$get_fields_calls = [];
$assert_same('Le cache ACF retourne les champs du post', 'Actualité 7001', theme_get_fields(7001)['title'] ?? '');
$assert_same('Le cache ACF réutilise les champs du post', 'Actualité 7001', theme_get_fields('7001')['title'] ?? '');
$assert_same('Le cache ACF ne charge un post qu’une fois', [[7001, true]], $get_fields_calls);
$assert_same('Le cache ACF utilise le contenu interrogé par défaut', 'Actualité 7002', theme_get_fields()['title'] ?? '');
$assert_same('Le cache ACF distingue les valeurs brutes', 'Actualité 7001', theme_get_fields(7001, false)['title'] ?? '');
$assert_same('Le cache ACF accepte les options', 'Préfooter', theme_get_fields('option')['prefooter']['title'] ?? '');
$assert_same(
    'Le cache ACF sépare les cibles et les modes de formatage',
    [[7001, true], [7002, true], [7001, false], ['option', true]],
    $get_fields_calls
);

$post_cache_calls = [];
$meta_cache_calls = [];
$assert_same(
    'Le préchargement de posts normalise et déduplique les IDs',
    [7101, 7102, 7103],
    theme_prime_posts([7101, '7102', ['post_id' => 7103], 7101, 0, ['title' => 'Sans post']])
);
$assert_same('Le préchargement groupe les objets posts', [[[7101, 7102, 7103], false, false]], $post_cache_calls);
$assert_same('Le préchargement groupe les métadonnées', [['post', [7101, 7102, 7103]]], $meta_cache_calls);
theme_prime_posts([7102, 7103]);
$assert_same('Le préchargement ne répète pas les objets déjà traités', 1, count($post_cache_calls));
$assert_same('Le préchargement ne répète pas les métadonnées déjà traitées', 1, count($meta_cache_calls));

$post_cache_calls = [];
$meta_cache_calls = [];
$assert_same(
    'Le préchargement d’images ignore les autres nombres du payload',
    [7201, 7202, 7203],
    theme_prime_attachments([
        ['image' => 7201, 'breakpoint' => 768],
        ['images' => ['desktop' => 7202, 'mobile' => 7203]],
        ['image' => 'https://example.test/externe.jpg'],
    ])
);
$assert_same('Le préchargement groupe les pièces jointes', [[[7201, 7202, 7203], false, false]], $post_cache_calls);
$assert_same('Le préchargement groupe les métadonnées des pièces jointes', [['post', [7201, 7202, 7203]]], $meta_cache_calls);

$GLOBALS['attachment_ids_by_url']['https://example.test/cached.jpg'] = 7301;
$attachment_url_calls = [];
$assert_same('La résolution média retourne son ID', 7301, theme_attachment_url_to_post_id('https://example.test/cached.jpg'));
$assert_same('La résolution média réutilise son résultat', 7301, theme_attachment_url_to_post_id('https://example.test/cached.jpg'));
$assert_same('Une URL média identique est recherchée une seule fois', ['https://example.test/cached.jpg'], $attachment_url_calls);

ob_start();
theme_render_breadcrumb_schema();
$breadcrumb_schema_with_yoast = trim((string) ob_get_clean());
$assert_same(
    'Le thème ne produit aucun JSON-LD breadcrumb lorsque Yoast SEO est actif',
    '',
    $breadcrumb_schema_with_yoast
);
$assert_same(
    'Le CPT News utilise le type Yoast NewsArticle',
    'NewsArticle',
    theme_filter_yoast_article_type('None', (object) ['object_sub_type' => 'news'])
);
$assert_same(
    'Le type Article Yoast des autres contenus est conservé',
    'Article',
    theme_filter_yoast_article_type('Article', (object) ['object_sub_type' => 'post'])
);

$setup_source = file_get_contents(dirname(__DIR__) . '/inc/config/setup.php');
$assert_not_contains(
    'Le thème conserve la balise title native filtrée par Yoast',
    "remove_action('wp_head', '_wp_render_title_tag'",
    is_string($setup_source) ? $setup_source : ''
);

$common_signature = new ReflectionFunction('common');
$assert_same(
    'Le renderer common accepte uniquement le nom du template',
    1,
    $common_signature->getNumberOfParameters()
);

$assert_same('Les attributs bruts sont refusés', '', html_attributes('onclick="alert(1)"'));
$assert_same(
    'Les événements, valeurs complexes et attributs réservés sont refusés',
    'id="safe" data-active',
    html_attributes([
        'id' => 'safe',
        'onclick' => 'alert(1)',
        'class' => 'duplicate',
        'title' => ['invalid'],
        'data-active' => true,
    ], ['class'])
);

$assert_same(
    'Les options de strate conservent un tableau de classes initiales',
    'class="strate strate-text-image reverse ctr-sm mb-lg"',
    options(
        ['strate', 'strate-text-image', 'reverse', ''],
        [
            'options' => [
                'container' => 'sm',
                'margin' => ['bottom' => 'lg'],
            ],
        ]
    )
);

$loaded_templates_before_rename_tests = $loaded_templates;
$queued_modules_before_rename_tests = $queued_modules;
$text_image_strate = $render_strate('text-image', [
    'title' => 'Titre image',
    'text' => '<p>Texte image.</p>',
    'image' => 401,
]);
$assert_contains('La strate renommée utilise sa classe canonique', 'strate-text-image', $text_image_strate);
$assert_contains('La strate renommée conserve le titre', 'Titre image', $text_image_strate);
$text_animated_strate = $render_strate('text-animated', []);
$assert_contains('La strate animée hydrate le nouveau chemin', 'data-module="strates/strate-text-animated"', $text_animated_strate);
$columns_content_strate = $render_strate('columns-content', [
    'columns' => [['title' => 'Première colonne', 'text' => '<p>Contenu éditorial.</p>']],
]);
$assert_contains('La strate de colonnes utilise sa nouvelle classe', 'strate-columns-content', $columns_content_strate);
$assert_contains('La strate de colonnes conserve son contenu', 'Première colonne', $columns_content_strate);
$assert_same(
    'Les anciens layouts ACF sont normalisés avant chargement',
    ['text-image', 'text-animated', 'columns-content', 'intro'],
    theme_normalize_strate_layout_names(
        ['text_image', 'text_animated', 'content-columns', 'intro'],
        123,
        ['key' => 'field_669e66a6b1d81']
    )
);

$loaded_templates_before_alias = count($loaded_templates);
strate('text_image', []);
$assert_same('L’ancien nom text_image redirige vers le template renommé', 'template-parts/strates/strate-text-image', $loaded_templates[$loaded_templates_before_alias] ?? null);
$loaded_templates_before_columns_alias = count($loaded_templates);
strate('content-columns', []);
$assert_same('L’ancien nom content-columns redirige vers le template renommé', 'template-parts/strates/strate-columns-content', $loaded_templates[$loaded_templates_before_columns_alias] ?? null);
$loaded_templates = $loaded_templates_before_rename_tests;
$queued_modules = $queued_modules_before_rename_tests;
$assert_same(
    'Les options de strate exposent un identifiant normalisé',
    'class="strate strate-text mb-0" id="notre-equipe"',
    options('strate strate-text', [
        'options' => ['name' => 'Notre équipe'],
    ])
);
$assert_same(
    'Le nom des options de strate est normalisé et ne peut pas injecter d’attribut',
    'class="strate mb-0" id="equipe-onclickalert1"',
    options('strate', [
        'options' => ['name' => 'Équipe " onclick="alert(1)'],
    ])
);
$assert_same(
    'Les ancres de strates utilisent le nom des options et ignorent les doublons',
    [
        ['label' => 'Notre équipe', 'target' => 'notre-equipe'],
        ['label' => 'Contact', 'target' => 'contact'],
    ],
    theme_get_strate_anchor_items([
        ['acf_fc_layout' => 'text', 'options' => ['name' => 'Notre équipe']],
        ['acf_fc_layout' => 'image', 'options' => ['name' => 'Notre équipe']],
        ['acf_fc_layout' => 'text', 'options' => ['name' => '']],
        ['acf_fc_layout' => 'text', 'options' => ['name' => ['invalid']]],
        ['acf_fc_layout' => 'text', 'options' => ['name' => 'Contact']],
        'invalid',
    ])
);
$assert_same(
    'Les ancres de strates refusent un payload associatif',
    [],
    theme_get_strate_anchor_items(['options' => ['name' => 'Introduction']])
);

component('select-lang', [
    'languages' => [['code' => 'fr', 'url' => '/fr/', 'current' => true]],
]);
$assert_same(
    'Le dispatcher charge le template demandé',
    'template-parts/components/select-lang',
    $loaded_templates[0] ?? null
);
$assert_same(
    'Le dispatcher procédural restaure le payload précédent',
    null,
    $query_vars['params'] ?? null
);
$assert_same(
    'Le dispatcher charge les assets de la même cible',
    'components/select-lang/select-lang',
    $queued_modules[0] ?? null
);

component('text', ['text' => 'Procédural']);
$assert_same(
    'Le dispatcher procédural ne laisse pas fuir son payload',
    null,
    $query_vars['params'] ?? null
);

$loaded_templates_before_unsafe_strate = count($loaded_templates);
strate('../unsafe');
$assert_same(
    'Un nom de strate non sûr est refusé avant chargement',
    $loaded_templates_before_unsafe_strate,
    count($loaded_templates)
);

set_query_var('template_args', ['parent' => true]);
strate('text', ['title' => 'Enfant']);
$assert_same(
    'Le dispatcher de strate restaure le contexte parent',
    ['parent' => true],
    get_query_var('template_args')
);
set_query_var('template_args', null);

$loaded_templates_before_unsafe_name = count($loaded_templates);
component('../unsafe');
$assert_same(
    'Un nom de composant non sûr est refusé avant chargement',
    $loaded_templates_before_unsafe_name,
    count($loaded_templates)
);
$assert_same(
    'Un nom de composant non sûr est signalé en développement',
    1,
    count($doing_it_wrong)
);
$assert_same(
    'Les classes sont aplaties, nettoyées et dédupliquées',
    'badge alpha beta',
    component_classes('badge', ['alpha', ['beta', 'alpha']])
);
$assert_same(
    'La façade statique est supprimée',
    false,
    class_exists('component', false)
);

$badge = $render('badge', [
    'name' => '<strong>Pop</strong>',
    'classes' => ['featured', 'featured'],
    'attributes' => ['class' => 'bad', 'id' => 'badge-id'],
]);
$assert_contains('Le badge échappe son contenu', '&lt;strong&gt;Pop&lt;/strong&gt;', $badge);
$assert_contains('Le badge utilise son payload procédural strict', '<span class="badge featured"', $badge);
$assert_contains('Le badge conserve les attributs sûrs', 'id="badge-id"', $badge);
$assert_not_contains('Le badge refuse une seconde classe', 'class="bad"', $badge);
$assert_same('Le badge refuse une liste directe', '', $render('badge', ['Pop']));
$assert_same('Le badge retire ses anciens alias', '', $render('badge', ['label' => 'Ancien alias']));

$breadcrumb = $render('breadcrumb', [
    'items' => [
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => '<Actualités>', 'url' => 'https://example.test/news/'],
        ['name' => 'Article courant'],
    ],
    'classes' => ['in-hero', 'in-hero'],
    'attributes' => ['id' => 'breadcrumb-id', 'aria-label' => 'Refusé'],
]);
$assert_contains('Le breadcrumb rend une navigation accessible', 'aria-label="Fil d’Ariane"', $breadcrumb);
$assert_contains('Le breadcrumb ajoute ses classes sans doublon', 'class="breadcrumb in-hero"', $breadcrumb);
$assert_contains('Le breadcrumb conserve les attributs sûrs', 'id="breadcrumb-id"', $breadcrumb);
$assert_contains('Le breadcrumb échappe les libellés', '&lt;Actualités&gt;', $breadcrumb);
$assert_contains('Le breadcrumb marque la page courante', 'aria-current="page"', $breadcrumb);
$assert_not_contains('Le breadcrumb ne lie pas la page courante', 'href="">Article courant</a>', $breadcrumb);
$assert_same(
    'Le breadcrumb ne rend rien avec un seul élément',
    '',
    $render('breadcrumb', ['items' => [['name' => 'Accueil', 'url' => '/']]])
);
$assert_same('Le breadcrumb refuse une liste directe', '', $render('breadcrumb', ['Accueil']));

$anchor_nav = $render('anchor-nav', [
    'items' => [
        ['label' => 'Introduction', 'target' => 'introduction'],
        ['label' => 'Nos <expertises>', 'target' => '#expertises'],
        ['label' => 'Doublon', 'target' => 'expertises'],
        ['label' => 'Cible invalide', 'target' => '../invalide'],
    ],
    'aria_label' => 'Sommaire <principal>',
    'classes' => ['in-page', 'in-page'],
    'attributes' => ['id' => 'page-summary', 'data-module' => 'refuse'],
]);
$assert_contains('Anchor nav rend une navigation hydratée', 'data-module="components/anchor-nav"', $anchor_nav);
$assert_contains('Anchor nav ajoute ses classes sans doublon', 'class="anchor-nav in-page"', $anchor_nav);
$assert_not_contains('Anchor nav reste verticale par défaut', 'class="anchor-nav horizontal', $anchor_nav);
$assert_contains('Anchor nav conserve les attributs sûrs', 'id="page-summary"', $anchor_nav);
$assert_contains('Anchor nav échappe son nom accessible', 'aria-label="Sommaire &lt;principal&gt;"', $anchor_nav);
$assert_contains('Anchor nav échappe ses libellés', 'Nos &lt;expertises&gt;', $anchor_nav);
$assert_same('Anchor nav filtre les cibles invalides et dupliquées', 2, substr_count($anchor_nav, '<li>'));
$assert_not_contains('Anchor nav réserve son attribut d’hydratation', 'data-module="refuse"', $anchor_nav);
$assert_same('Anchor nav vide ne produit aucun HTML', '', $render('anchor-nav', ['items' => []]));
$assert_same('Anchor nav refuse une liste directe', '', $render('anchor-nav', ['Introduction']));

$horizontal_anchor_nav = $render('anchor-nav', [
    'items' => [['label' => 'Introduction', 'target' => 'introduction']],
    'horizontal' => true,
    'classes' => 'in-page',
]);
$assert_contains('Anchor nav ajoute sa variante horizontale', 'class="anchor-nav horizontal in-page"', $horizontal_anchor_nav);

$section_snap = $render('section-snap', [
    'slides' => [
        ['title' => 'Première'],
        ['title' => '<Deuxième>'],
        ['title' => ''],
    ],
    'root_id' => 'snap-demo-4',
    'aria_label' => 'Navigation <snap>',
    'classes' => ['in-demo', 'in-demo'],
    'attributes' => ['id' => 'snap-navigation', 'data-module' => 'refuse'],
]);
$assert_contains('Section snap rend la navigation attendue', 'class="snap-nav in-demo"', $section_snap);
$assert_contains('Section snap porte son hydratation', 'data-module="components/section-snap"', $section_snap);
$assert_contains('Section snap conserve les attributs sûrs', 'id="snap-navigation"', $section_snap);
$assert_contains('Section snap génère les cibles depuis la racine', 'href="#snap-demo-4-1"', $section_snap);
$assert_contains('Section snap échappe les titres', '&lt;Deuxième&gt;', $section_snap);
$assert_contains('Section snap échappe son nom accessible', 'aria-label="Navigation &lt;snap&gt;"', $section_snap);
$assert_same('Section snap ignore les titres vides', 2, substr_count($section_snap, '<li>'));
$assert_not_contains('Section snap réserve son attribut d’hydratation', 'data-module="refuse"', $section_snap);
$assert_same('Section snap refuse une racine invalide', '', $render('section-snap', [
    'slides' => [['title' => 'Première']],
    'root_id' => '../snap',
]));
$assert_same('Section snap vide ne produit aucun HTML', '', $render('section-snap', [
    'slides' => [],
    'root_id' => 'snap-demo',
]));

$lottie = $render('lottie', [
    'src' => 'https://example.test/desktop.json',
    'mobile_src' => '/mobile.json',
    'breakpoint' => 1024,
    'autoplay' => 'true',
    'loop' => '1',
    'width' => 1920,
    'height' => 1080,
    'label' => 'Animation <test>',
    'classes' => ['in-hero', 'in-hero'],
    'attributes' => ['id' => 'lottie-test', 'data-module' => 'refuse'],
]);
$assert_contains('Lottie rend sa source desktop', 'data-lottie-src="https://example.test/desktop.json"', $lottie);
$assert_contains('Lottie rend sa source mobile', 'data-lottie-src-mobile="/mobile.json"', $lottie);
$assert_contains('Lottie rend son breakpoint', 'data-lottie-breakpoint="1024"', $lottie);
$assert_contains('Lottie normalise la lecture automatique', 'data-lottie-autoplay="true"', $lottie);
$assert_contains('Lottie normalise la boucle', 'data-lottie-loop="true"', $lottie);
$assert_contains('Lottie réserve son ratio', 'style="--lottie-aspect-ratio:1920 / 1080"', $lottie);
$assert_contains('Lottie rend son nom accessible', 'role="img" aria-label="Animation &lt;test&gt;"', $lottie);
$assert_contains('Lottie porte son hydratation', 'data-module="components/lottie"', $lottie);
$assert_contains('Lottie conserve les attributs sûrs', 'id="lottie-test"', $lottie);
$assert_not_contains('Lottie réserve son attribut d’hydratation', 'data-module="refuse"', $lottie);
$assert_same('Lottie refuse une source dangereuse', '', $render('lottie', [
    'src' => 'javascript:alert(1)',
]));
$assert_not_contains('Lottie peut déléguer son hydratation', 'data-module=', $render('lottie', [
    'src' => '/animation.json',
    'autonomous' => false,
]));

$assert_same(
    'Le parcours SEO supprime le HTML et les éléments invalides',
    [
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => 'Page', 'url' => ''],
    ],
    theme_normalize_breadcrumb_items([
        ['name' => '<strong>Accueil</strong>', 'url' => 'https://example.test/'],
        ['name' => ''],
        ['name' => 'Page'],
        ['Ancien format'],
    ])
);
$assert_same(
    'Le schéma omet l’URL du dernier élément',
    [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Accueil',
            'item' => 'https://example.test/',
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Page',
        ],
    ],
    theme_get_breadcrumb_schema_elements([
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => 'Page', 'url' => ''],
    ])
);
$assert_same(
    'Le schéma refuse un niveau intermédiaire sans URL',
    [],
    theme_get_breadcrumb_schema_elements([
        ['name' => 'Accueil', 'url' => ''],
        ['name' => 'Page', 'url' => ''],
    ])
);

$select_lang = $render('select-lang', [
    'languages' => [
        ['code' => 'fr', 'url' => '/fr/', 'current' => true],
        ['code' => 'en', 'label' => 'English', 'url' => '/en/', 'disabled' => 'false'],
        ['code' => 'de', 'label' => 'Deutsch', 'url' => '/de/', 'disabled' => 'true'],
        ['code' => 'xx', 'url' => 'javascript:alert(1)'],
    ],
    'label' => 'Langue du site',
    'classes' => ['compact', 'compact'],
    'attributes' => ['class' => 'duplicate', 'data-module' => 'duplicate', 'id' => 'language-switcher'],
]);
$assert_contains('Le sélecteur utilise son payload procédural strict', '<div class="select-lang compact" data-module="components/select-lang" data-context="@visible true" id="language-switcher">', $select_lang);
$assert_contains('Le sélecteur reconnaît la langue courante', 'aria-current="page"', $select_lang);
$assert_not_contains('Le sélecteur retire les URLs interdites', 'javascript:', $select_lang);
$assert_same('Le sélecteur normalise la chaîne false', 2, substr_count($select_lang, '<a'));
$assert_same('Le sélecteur normalise la chaîne true', 1, substr_count($select_lang, 'aria-disabled="true"'));
$assert_not_contains('Le sélecteur protège sa classe', 'class="duplicate"', $select_lang);
$assert_not_contains('Le sélecteur protège son module', 'data-module="duplicate"', $select_lang);
$legacy_select_lang = $render('select-lang', [
    'languages' => [['code' => 'fr', 'url' => '/fr/', 'active' => true]],
]);
$assert_not_contains('Select-lang retire les anciens alias d’état', 'aria-current="page"', $legacy_select_lang);
$assert_same('Select-lang refuse une liste directe', '', $render('select-lang', [
    ['code' => 'fr', 'url' => '/fr/'],
]));

$accordion = $render('accordion', [
    'items' => [
        ['title' => 'Première <question>', 'text' => '<p>Réponse <strong>riche</strong>.</p>'],
        ['title' => 'Deuxième question', 'text' => '<p>Deuxième réponse.</p>'],
        ['title' => '', 'text' => '<p>Item invalide.</p>'],
    ],
    'multiple' => 'false',
    'classes' => ['faq', 'faq'],
    'attributes' => [
        'class' => 'duplicate',
        'data-module' => 'duplicate',
        'id' => 'faq',
    ],
]);
$assert_contains('Accordion utilise son payload procédural strict', '<div class="accordion faq" id="faq" data-module="components/accordion"', $accordion);
$assert_same('Accordion ignore les items invalides', 2, substr_count($accordion, 'class="details"'));
$assert_same('Le mode simple ouvre uniquement le premier panneau', 1, substr_count($accordion, 'aria-expanded="true"'));
$assert_contains('Accordion échappe le titre', 'Première &lt;question&gt;', $accordion);
$assert_contains('Accordion conserve le contenu éditorial', '<strong>riche</strong>', $accordion);
$assert_not_contains('Accordion protège ses attributs structurels', 'data-module="duplicate"', $accordion);

$multiple_accordion = $render('accordion', [
    'items' => [
        ['title' => 'Un', 'text' => 'Contenu un'],
        ['title' => 'Deux', 'text' => 'Contenu deux'],
    ],
    'multiple' => 'true',
]);
$assert_same('Le mode multiple ouvre tous les panneaux', 2, substr_count($multiple_accordion, 'aria-expanded="true"'));
$assert_same('Une liste directe accordion est refusée', '', $render('accordion', [
    ['title' => 'Ancienne forme', 'text' => 'Contenu'],
]));

$eyebrow = $render('eyebrow', [
    'eyebrow' => 'Notre <strong>expertise</strong>',
    'element' => 'span',
    'icon' => 'star',
    'icon_width' => 18,
    'icon_height' => 12,
    'classes' => ['featured', 'featured'],
    'attributes' => ['class' => 'duplicate', 'id' => 'hero-eyebrow'],
]);
$assert_contains('Eyebrow utilise son payload procédural strict', '<span class="eyebrow hasicon featured" id="hero-eyebrow">', $eyebrow);
$assert_contains('Eyebrow conserve le HTML éditorial autorisé', '<strong>expertise</strong>', $eyebrow);
$assert_contains('Eyebrow transmet les dimensions à l’icône', 'width="18" height="12"', $eyebrow);
$assert_not_contains('Eyebrow protège son attribut class', 'class="duplicate"', $eyebrow);
$assert_same('Eyebrow refuse une liste directe', '', $render('eyebrow', ['Ancienne forme']));
$assert_same('Eyebrow retire les anciens alias', '', $render('eyebrow', ['suptitle' => 'Ancien alias']));

$header = $render('header', [
    'title' => 'Nos <strong>expertises</strong>',
    'text' => '<p>Découvrez notre savoir-faire.</p>',
    'link' => [
        'url' => '/expertises/',
        'title' => 'Toutes les expertises',
        'target' => '_blank',
        'classes' => 'header-link',
        'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16],
        'attributes' => ['data-track' => 'expertises'],
    ],
    'classes' => ['section-header', 'section-header'],
    'attributes' => ['class' => 'duplicate', 'id' => 'expertises-header'],
    'title_classes' => 'title-2',
    'title_attributes' => ['id' => 'expertises-title'],
    'text_classes' => 'section-intro',
    'text_attributes' => ['data-content' => 'intro'],
    'hx' => 4,
]);
$assert_contains('Header utilise son payload procédural strict', '<header class="component-header section-header" id="expertises-header">', $header);
$assert_contains('Header conserve son titre h2 fixe', '<h2 class="title title-2" id="expertises-title">Nos <strong>expertises</strong></h2>', $header);
$assert_not_contains('Header ignore une clé hx externe', '<h4', $header);
$assert_contains('Header délègue le texte au composant text', 'class="text section-intro" data-content="intro"', $header);
$assert_contains('Header délègue le lien au composant link', 'href="/expertises/" class="link header-link" data-track="expertises" target="_blank"', $header);
$assert_contains('Header délègue l’icône au composant link', 'class="icon icon-blank" width="16" height="16"', $header);
$assert_not_contains('Header protège la classe du conteneur', 'class="duplicate"', $header);

$text_only_header = $render('header', ['text' => '<p>Introduction seule.</p>']);
$assert_contains('Header accepte un texte sans titre', '<header class="component-header">', $text_only_header);
$assert_same('Header invalide ne produit aucun HTML', '', $render('header', [
    'link' => ['url' => 'javascript:alert(1)', 'title' => 'Lien invalide'],
]));
$assert_same('Header retire les anciens alias', '', $render('header', [
    'heading' => 'Ancien titre',
    'intro' => 'Ancien texte',
    'cta' => ['url' => '/legacy/', 'title' => 'Ancien lien'],
]));
$assert_same('Header refuse les arguments positionnels', '', $render('header', [
    ['title' => 'Ancienne forme'],
    'legacy-header',
]));

$icon = $render('icon', [
    'name' => 'star',
    'width' => 18,
    'height' => 12,
    'label' => 'Étoile',
    'classes' => ['featured', 'featured'],
    'attributes' => [
        'class' => 'duplicate',
        'aria-hidden' => 'true',
        'data-animation' => 'rotate',
    ],
]);
$assert_contains('Icon utilise son payload procédural strict', '<svg class="icon icon-star featured" width="18" height="12" role="img" aria-label="Étoile"', $icon);
$assert_contains('Icon conserve les attributs sûrs', 'data-animation="rotate"', $icon);
$assert_not_contains('Icon protège ses attributs structurels', 'class="duplicate"', $icon);
$assert_not_contains('Une icône informative ne peut pas être masquée', 'aria-hidden="true"', $icon);
$assert_contains('Icon référence le symbole du sprite', 'icons.svg#star', $icon);

$decorative_icon = $render('icon', ['name' => 'chat']);
$assert_contains('Icon est décorative sans label', 'aria-hidden="true" focusable="false"', $decorative_icon);
$assert_same('Icon refuse une liste directe', '', $render('icon', ['star', 16, 16]));

$picto = $render('picto', [
    'name' => 'chat',
    'label' => 'Conversation',
    'classes' => ['featured', 'featured'],
    'attributes' => ['class' => 'duplicate', 'id' => 'contact-picto'],
]);
$assert_contains('Picto utilise son payload procédural strict', '<span class="picto featured" id="contact-picto">', $picto);
$assert_contains('Picto délègue le nom et le label à icon', 'class="icon icon-chat" width="24" height="24" role="img" aria-label="Conversation"', $picto);
$assert_not_contains('Picto protège son attribut class', 'class="duplicate"', $picto);
$assert_same('Picto retire l’ancien alias icon', '', $render('picto', ['icon' => 'star']));
$assert_same('Picto refuse une liste directe', '', $render('picto', ['star']));

$button = $render('btn', [
    'name' => 'Envoyer <maintenant>',
    'classes' => ['primary', 'primary'],
    'icon' => ['name' => 'blank', 'width' => 16, 'height' => 12],
    'attributes' => [
        'type' => 'submit',
        'class' => 'duplicate',
        'href' => '/duplicate/',
        'data-tracking' => 'submit',
    ],
]);
$assert_contains('Btn utilise son payload procédural strict', '<button type="submit" class="btn primary" data-tracking="submit">', $button);
$assert_contains('Btn échappe son libellé', 'Envoyer &lt;maintenant&gt;', $button);
$assert_contains('Btn délègue son icône au composant icon', 'class="icon icon-blank" width="16" height="12"', $button);
$assert_not_contains('Btn protège ses attributs structurels', 'class="duplicate"', $button);
$assert_not_contains('Un bouton natif refuse href', 'href="/duplicate/"', $button);

$button_link = $render('btn', [
    'link' => [
        'url' => 'https://example.test/project/',
        'title' => 'Découvrir',
        'target' => '_blank',
    ],
    'classes' => 'secondary',
    'attributes' => ['rel' => 'nofollow', 'data-tracking' => 'project'],
]);
$assert_contains('Btn rend un link explicite comme un lien', '<a href="https://example.test/project/" class="btn secondary" data-tracking="project" target="_blank"', $button_link);
$assert_contains('Btn sécurise et fusionne rel', 'rel="nofollow noopener noreferrer"', $button_link);

$span_button = $render('btn', [
    'link' => [
        'url' => 'https://example.test/card/',
        'title' => 'Voir la card',
        'target' => '_blank',
    ],
    'element' => 'span',
    'classes' => 'secondary',
    'attributes' => [
        'href' => '/duplicate/',
        'type' => 'submit',
        'disabled' => true,
        'data-tracking' => 'card',
    ],
]);
$assert_contains('Btn peut devenir un span non interactif', '<span class="btn secondary" data-tracking="card"><span>Voir la card</span></span>', $span_button);
$assert_not_contains('Btn span ne rend aucun href', ' href=', $span_button);
$assert_not_contains('Btn span ne rend aucun type', ' type=', $span_button);
$assert_not_contains('Btn span ne rend aucun état disabled natif', ' disabled', $span_button);

$button_ignores_element = $render('btn', [
    'name' => 'Découvrir',
    'element' => 'span',
]);
$assert_contains('Btn sans link reste un bouton et ignore element', '<button type="button" class="btn"><span>Découvrir</span></button>', $button_ignores_element);

$invalid_element_link = $render('btn', [
    'link' => ['url' => '/fallback/', 'title' => 'Fallback'],
    'element' => 'script',
]);
$assert_contains('Btn refuse une balise arbitraire et conserve le lien par défaut', '<a href="/fallback/" class="btn">', $invalid_element_link);
$assert_contains('Btn sans link ignore aussi element a', '<button type="button" class="btn"><span>Lien incomplet</span></button>', $render('btn', [
    'name' => 'Lien incomplet',
    'element' => 'a',
]));

$disabled_button_link = $render('btn', [
    'link' => ['url' => '/project/', 'title' => 'Indisponible', 'target' => '_blank'],
    'attributes' => ['aria-disabled' => true],
]);
$assert_not_contains('Un btn lien désactivé ne possède pas de href', ' href=', $disabled_button_link);
$assert_not_contains('Un btn lien désactivé ne possède pas de target', ' target=', $disabled_button_link);
$assert_same('Btn refuse une liste directe', '', $render('btn', [['name' => 'Ancienne forme']]));
$assert_same('Btn retire la forme plate des liens', '', $render('btn', ['url' => '/legacy/', 'title' => 'Ancien lien']));
$assert_same('Btn retire les enveloppes historiques', '', $render('btn', ['cta' => ['url' => '/legacy/', 'title' => 'CTA']]));

$link = $render('link', [
    'link' => [
        'url' => 'https://example.test/contact/',
        'title' => 'Nous <contacter>',
        'target' => '_blank',
    ],
    'classes' => ['secondary', 'secondary'],
    'icon' => ['name' => 'blank', 'width' => 14, 'height' => 10],
    'attributes' => [
        'class' => 'duplicate',
        'href' => '/duplicate/',
        'rel' => 'nofollow',
        'data-tracking' => 'contact',
    ],
]);
$assert_contains('Link utilise son payload procédural strict', '<a href="https://example.test/contact/" class="link secondary" data-tracking="contact" target="_blank"', $link);
$assert_contains('Link échappe son libellé', 'Nous &lt;contacter&gt;', $link);
$assert_contains('Link sécurise et fusionne rel', 'rel="nofollow noopener noreferrer"', $link);
$assert_contains('Link délègue son icône au composant icon', 'class="icon icon-blank" width="14" height="10"', $link);
$assert_not_contains('Link protège ses attributs structurels', 'class="duplicate"', $link);
$assert_not_contains('Link refuse un href générique', 'href="/duplicate/"', $link);

$disabled_link = $render('link', [
    'link' => ['url' => '/contact/', 'title' => 'Indisponible', 'target' => '_blank'],
    'attributes' => ['aria-disabled' => true],
]);
$assert_not_contains('Un link désactivé ne possède pas de href', ' href=', $disabled_link);
$assert_not_contains('Un link désactivé ne possède pas de target', ' target=', $disabled_link);
$assert_same('Link refuse une liste directe', '', $render('link', [['link' => ['url' => '/', 'title' => 'Ancien']]]));
$assert_same('Link retire la forme plate', '', $render('link', ['url' => '/legacy/', 'title' => 'Ancien lien']));

$custom_card = $render_card('news', [
    'title' => 'Contenu personnalisé',
    'text' => '<p>Texte personnalisé</p>',
    'image' => 321,
]);
$assert_contains('Card accepte un contenu associatif personnalisé', 'Contenu personnalisé', $custom_card);
$assert_contains('Card rend son image en cover', '<picture class="picture fit-cover card-image">', $custom_card);
$post_card = $render_card('news', 101);
$assert_contains('Card charge les champs ACF depuis un ID', 'Actualité 101', $post_card);
$assert_contains('Card transmet le post_id normalisé au template', 'Contenu du post 101', $post_card);
$overridden_post_card = $render_card('news', [
    'post_id' => 102,
    'title' => 'Titre surchargé',
]);
$assert_contains('Le contenu explicite surcharge les champs du post', 'Titre surchargé', $overridden_post_card);
$assert_contains('Les champs non surchargés restent issus du post', 'Contenu du post 102', $overridden_post_card);
$assert_same('Card refuse un ID nul', '', $render_card('news', 0));
$assert_same('Card refuse un ID négatif', '', $render_card('news', -1));
$assert_same('Card refuse un ID sous forme de chaîne', '', $render_card('news', '101'));
$assert_same('Card refuse un tableau positionnel', '', $render_card('news', ['Ancien contenu']));
$assert_same('Card refuse un tableau vide', '', $render_card('news', []));

$page_card = $render_card('page', [
    'type' => 'Page',
    'title' => 'À propos',
    'text' => '<p>Présentation de la page.</p>',
    'link' => [
        'url' => '/a-propos/',
        'target' => '_blank',
    ],
]);
$assert_contains('Card page rend son type', '<p class="eyebrow card-type">Page</p>', $page_card);
$assert_contains('Card page rend son titre', '<h3 class="title title-3">À propos</h3>', $page_card);
$assert_contains('Card page rend son texte', '<p>Présentation de la page.</p>', $page_card);
$assert_contains('Card page sécurise une cible externe', 'target="_blank" rel="noopener noreferrer"', $page_card);
$assert_same('Card page ne rend rien sans titre', '', $render_card('page', ['type' => 'Page']));

$expertise_card = $render_card('expertise', [
    'title' => 'Conseil <strong>stratégique</strong>',
    'text' => '<p>Nous accompagnons les transformations.</p>',
]);
$assert_contains('Card expertise rend son titre en h3', '<h3 class="title title-3">Conseil <strong>stratégique</strong></h3>', $expertise_card);
$assert_contains('Card expertise rend son texte éditorial', '<p>Nous accompagnons les transformations.</p>', $expertise_card);
$assert_same('Card expertise ne rend rien sans titre', '', $render_card('expertise', [
    'text' => '<p>Titre absent.</p>',
]));

$testimonial_card = $render_card('testimonial', [
    'quote' => '<p>Une collaboration remarquable.</p>',
    'author' => 'Jane Doe',
    'source' => 'Example Company',
    'url' => 'https://example.test/',
]);
$assert_contains('Card testimonial rend sa citation', '<p>Une collaboration remarquable.</p>', $testimonial_card);
$assert_contains('Card testimonial rend sa source', '<cite class="quote-source">', $testimonial_card);
$assert_not_contains('Card testimonial ignore une URL extérieure à son contrat', '<a href=', $testimonial_card);

$keynumber_card = $render_card('keynumber', [
    'value' => '98<script>',
    'prefix' => '+<',
    'suffix' => '%',
    'title' => 'de <strong>satisfaction</strong>',
    'text' => '<p>Mesurée auprès des clients.</p>',
]);
$assert_contains('Card keynumber échappe sa valeur', '98&lt;script&gt;', $keynumber_card);
$assert_contains('Card keynumber échappe son préfixe', '<span class="keynumber-prefix">+&lt;</span>', $keynumber_card);
$assert_contains('Card keynumber échappe son suffixe', '<span class="keynumber-suffix">%</span>', $keynumber_card);
$assert_contains('Card keynumber rend son titre en h3', '<h3 class="title keynumber-title title-3">de <strong>satisfaction</strong></h3>', $keynumber_card);
$assert_contains('Card keynumber rend son texte éditorial', '<p>Mesurée auprès des clients.</p>', $keynumber_card);
$assert_same('Card keynumber ne rend rien sans valeur', '', $render_card('keynumber', [
    'title' => 'Valeur absente',
]));

set_query_var('template_args', ['scope' => 'before-card']);
$render_card('news', 103);
$assert_same(
    'Card restaure les arguments du template après son rendu',
    ['scope' => 'before-card'],
    get_query_var('template_args')
);

$list = $render('list', [
    'items' => [
        ['title' => 'Première actualité'],
        [],
        'item invalide',
        ['title' => 'Deuxième actualité'],
    ],
    'card' => 'news',
    'classes' => ['news-list', 'news-list'],
    'attributes' => [
        'class' => 'duplicate',
        'role' => 'group',
        'id' => 'latest-news',
        'aria-label' => 'Dernières actualités',
        'data-list' => 'news',
    ],
]);
$assert_contains('List utilise son payload procédural strict', '<ul class="list news-list" role="list" id="latest-news" aria-label="Dernières actualités" data-list="news">', $list);
$assert_same('List ignore les items invalides', 2, substr_count($list, '<li class="item">'));
$assert_same('List rend réellement chaque card valide', 2, substr_count($list, '<article class="card-news">'));
$assert_not_contains('List protège ses attributs structurels', 'class="duplicate"', $list);
$assert_not_contains('List protège son rôle structurel', 'role="group"', $list);

$flex_list = $render('list', [
    'items' => [['title' => 'Première'], ['title' => 'Deuxième']],
    'card' => 'news',
    'type' => 'flex',
]);
$assert_contains('List utilise la classe flex demandée', '<ul class="list-flex" role="list">', $flex_list);
$assert_not_contains('List flex ne conserve pas la classe grid', '<ul class="list ', $flex_list);

$invalid_type_list = $render('list', [
    'items' => [['title' => 'Type invalide']],
    'card' => 'news',
    'type' => 'invalid',
]);
$assert_contains('List utilise grid par défaut pour un type invalide', '<ul class="list" role="list">', $invalid_type_list);

$post_list = $render('list', [
    'items' => [104, 0, -1, '105', 106],
    'card' => 'news',
]);
$assert_same('List accepte les IDs positifs et rejette les autres scalaires', 2, substr_count($post_list, '<article class="card-news">'));
$assert_contains('List transmet les IDs aux cards', 'Actualité 104', $post_list);

$single_post_list = $render('list', [
    'items' => 107,
    'card' => 'news',
]);
$assert_same('List accepte un ID unique', 1, substr_count($single_post_list, '<article class="card-news">'));

$single_list_item = $render('list', [
    'items' => ['title' => 'Item associatif unique'],
    'card' => 'card-news',
]);
$assert_same('List accepte un item associatif unique', 1, substr_count($single_list_item, '<li class="item">'));
$assert_same('List vide ne produit aucun HTML', '', $render('list', ['items' => []]));
$assert_same('List refuse une card inexistante', '', $render('list', [
    'items' => [['title' => 'Sans card']],
    'card' => 'missing',
]));
$assert_same('List refuse un nom de card invalide', '', $render('list', [
    'items' => [['title' => 'Card invalide']],
    'card' => '../news',
]));
$assert_same('List refuse les arguments positionnels', '', $render('list', [
    [['title' => 'Ancienne forme']],
    'news',
    'legacy-list',
]));

$pagination = $render('pagination', [
    'total' => 5,
    'current' => 3,
    'prev_text' => 'Précédent',
    'next_text' => 'Suivant',
    'aria_label' => 'Pagination des actualités',
    'classes' => ['news-pagination', 'news-pagination'],
    'attributes' => [
        'class' => 'duplicate',
        'aria-label' => 'Dupliquée',
        'data-pagination' => 'news',
    ],
]);
$assert_contains('Pagination rend une navigation accessible', '<nav class="pagination news-pagination" aria-label="Pagination des actualités" data-pagination="news">', $pagination);
$assert_contains('Pagination indique la page courante', '<span aria-current="page" class="page-numbers current">3</span>', $pagination);
$assert_contains('Pagination rend le lien précédent', '>Précédent</a>', $pagination);
$assert_contains('Pagination rend le lien suivant', '>Suivant</a>', $pagination);
$assert_not_contains('Pagination protège sa classe', 'class="duplicate"', $pagination);
$assert_not_contains('Pagination protège son aria-label', 'aria-label="Dupliquée"', $pagination);
$assert_same('Pagination ne rend rien avec une seule page', '', $render('pagination', ['total' => 1]));
$assert_same('Pagination refuse les arguments positionnels', '', $render('pagination', [5, 2]));
$pagination_with_base = $render('pagination', [
    'total' => 3,
    'current' => 1,
    'base' => 'https://example.test/news/?hub_page=%#%',
]);
$assert_contains(
    'Pagination accepte une base explicite avec placeholder',
    'href="https://example.test/news/?hub_page=2"',
    $pagination_with_base
);

set_query_var('template_args', ['scope' => 'before-list']);
$render('list', [
    'items' => [['title' => 'État isolé']],
    'card' => 'news',
]);
$assert_same(
    'List restaure les arguments du template après le rendu des cards',
    ['scope' => 'before-list'],
    get_query_var('template_args')
);

$image = $render('image', [
    'image' => 'https://example.test/image.jpg',
    'lazy' => false,
    'fetchpriority' => 'high',
]);
$assert_not_contains('Une image non critique refuse une priorité haute', 'fetchpriority="high"', $image);

$critical_image = $render('image', [
    'image' => 'https://example.test/hero.jpg',
    'critical' => true,
    'fetchpriority' => 'low',
]);
$assert_contains('Une image critique impose une priorité haute', 'fetchpriority="high"', $critical_image);
$assert_not_contains('Une image critique ne doit pas être lazy', 'loading="lazy"', $critical_image);

$procedural_image = $render('image', [
    'image' => 'https://example.test/decorative.jpg',
    'alt' => '',
    'width' => 640,
    'height' => 360,
    'lazy' => 'false',
    'fetchpriority' => 'LOW',
    'decoding' => 'SYNC',
    'classes' => ['cover', 'featured'],
    'attributes' => [
        'class' => 'duplicate',
        'width' => 1,
        'onclick' => 'alert(1)',
        'data-track' => 'image',
    ],
]);
$assert_contains('L’image supporte l’API procédurale', 'class="cover featured"', $procedural_image);
$assert_contains('Un alt décoratif explicite reste vide', 'alt=""', $procedural_image);
$assert_not_contains('Un alt décoratif ne reprend pas le texte WordPress', 'Texte alternatif', $procedural_image);
$assert_contains('Les dimensions explicites remplacent les dimensions détectées', 'width="640" height="360"', $procedural_image);
$assert_not_contains('La chaîne false désactive le lazy loading', 'loading="lazy"', $procedural_image);
$assert_contains('Fetchpriority est normalisé en minuscules', 'fetchpriority="low"', $procedural_image);
$assert_contains('Decoding est normalisé en minuscules', 'decoding="sync"', $procedural_image);
$assert_contains('Les attributs libres sont conservés', 'data-track="image"', $procedural_image);
$assert_not_contains('Les attributs structurels ne sont pas dupliqués', 'class="duplicate"', $procedural_image);
$assert_not_contains('Les handlers sont refusés', 'onclick', $procedural_image);

$attachment_alt_image = $render('image', ['image' => 42]);
$assert_contains('L’alt WordPress est utilisé lorsque la clé alt est absente', 'alt="Texte alternatif"', $attachment_alt_image);

$GLOBALS['lsd_thumb_responses'] = [
    '42|718_478' => ['https://example.test/image-718x478.jpg', 718, 478, 'Image responsive'],
    '42|medium' => ['https://example.test/image-300x205.jpg', 300, 205, 'Image responsive'],
];
$GLOBALS['lsd_webp_convertible'] = true;
$GLOBALS['lsd_webp_sources'] = [
    'https://example.test/image-718x478.jpg|718_478' => 'https://example.test/image-718x478.webp',
    'https://example.test/image-300x205.jpg|medium' => 'https://example.test/image-300x205.webp',
];
$GLOBALS['lsd_image_dimensions'] = [
    'https://example.test/image-718x478.webp' => ['width' => 718, 'height' => 478],
    'https://example.test/image-300x205.webp' => ['width' => 300, 'height' => 205],
];

$responsive_image = $render('image', [
    'image' => 42,
    'sizes' => ['718_478', 'medium', 'medium'],
    'attributes' => [
        'srcset' => 'https://example.test/injected.jpg 1w',
        'sizes' => '1px',
    ],
]);
$assert_not_contains('Image responsive ne produit pas de picture', '<picture', $responsive_image);
$assert_contains('Image responsive utilise le premier format comme source', 'src="https://example.test/image-718x478.webp"', $responsive_image);
$assert_contains(
    'Image responsive trie ses formats par largeur réelle',
    'srcset="https://example.test/image-300x205.webp 300w, https://example.test/image-718x478.webp 718w"',
    $responsive_image
);
$assert_contains(
    'Image responsive calcule automatiquement sizes depuis le format principal',
    'sizes="(max-width: 718px) 100vw, 718px"',
    $responsive_image
);
$assert_contains('Image responsive conserve les dimensions du format principal', 'width="718" height="478"', $responsive_image);
$assert_not_contains('Image responsive protège son srcset', 'injected.jpg', $responsive_image);
$assert_not_contains('Image responsive protège son attribut sizes', 'sizes="1px"', $responsive_image);

$GLOBALS['attachment_ids_by_url'] = [
    'https://example.test/uploads/image.jpg' => 42,
];
$responsive_url_image = $render('image', [
    'image' => 'https://example.test/uploads/image.jpg',
    'sizes' => ['718_478', 'medium'],
]);
$assert_contains(
    'Une URL de la médiathèque utilise les formats de son attachement',
    'srcset="https://example.test/image-300x205.webp 300w, https://example.test/image-718x478.webp 718w"',
    $responsive_url_image
);
$assert_not_contains('Une URL de la médiathèque ne produit pas de picture', '<picture', $responsive_url_image);

$external_url_image = $render('image', [
    'image' => 'https://cdn.example.test/external.jpg',
    'sizes' => ['718_478', 'medium'],
]);
$assert_contains(
    'Une URL externe reste utilisable comme image simple',
    'src="https://cdn.example.test/external.jpg"',
    $external_url_image
);
$assert_not_contains('Une URL externe sans métadonnées ne génère pas de srcset artificiel', 'srcset=', $external_url_image);

unset(
    $GLOBALS['attachment_ids_by_url'],
    $GLOBALS['lsd_thumb_responses'],
    $GLOBALS['lsd_webp_convertible'],
    $GLOBALS['lsd_webp_sources'],
    $GLOBALS['lsd_image_dimensions']
);

$assert_same('Les anciens alias de l’image sont retirés', '', $render('image', ['src' => 'https://example.test/legacy.jpg']));

$unsafe_image = $render('image', ['image' => 'javascript:alert(1)']);
$assert_same('Une URL d’image dangereuse ne produit aucun HTML', '', $unsafe_image);

$picture = $render('picture', [
    'images' => [
        'desktop' => '/images/desktop.jpg',
        'mobile' => '/images/mobile.jpg',
    ],
    'desktop_size' => 'large',
    'mobile_size' => 'medium',
    'alt' => '',
    'classes' => ['cover', 'cover'],
    'fit' => 'cover',
    'attributes' => ['class' => 'duplicate', 'id' => 'responsive-picture'],
    'lazy' => 'false',
    'breakpoint' => 900,
]);
$assert_contains('Picture utilise son payload procédural strict', '<picture class="picture fit-cover cover" id="responsive-picture">', $picture);
$assert_contains('Picture rend la variante mobile sous le breakpoint', 'srcset="/images/mobile.jpg" media="(max-width:899px)"', $picture);
$assert_contains('Picture rend la variante desktop à partir du breakpoint', 'srcset="/images/desktop.jpg" media="(min-width:900px)"', $picture);
$assert_contains('Picture utilise la variante desktop comme fallback', '<img src="/images/desktop.jpg" alt=""', $picture);
$assert_not_contains('Picture respecte lazy false', 'loading="lazy"', $picture);
$assert_not_contains('Picture protège sa classe structurelle', 'class="duplicate"', $picture);

$GLOBALS['lsd_thumb_calls'] = [];
$GLOBALS['lsd_webp_sources'] = [
    '/images/simple.jpg|large' => '/images/simple.webp',
    '/images/mobile-only.jpg|medium' => '/images/mobile-only.webp',
];
$simple_picture = $render('picture', [
    'image' => '/images/simple.jpg',
    'size' => 'large',
]);
$assert_contains('Picture simple rend le WebP existant dans une source', '<source srcset="/images/simple.webp" type="image/webp">', $simple_picture);
$assert_contains('Picture simple conserve l’original comme fallback', '<img src="/images/simple.jpg"', $simple_picture);
$assert_contains('Picture utilise fluid par défaut', '<picture class="picture fit-fluid">', $simple_picture);
$assert_same(
    'Picture transmet la taille simple au resolver',
    ['/images/simple.jpg', 'large'],
    $GLOBALS['lsd_thumb_calls'][0] ?? null
);

$mobile_only_picture = $render('picture', [
    'images' => ['mobile' => '/images/mobile-only.jpg'],
    'mobile_size' => 'medium',
]);
$assert_contains('Picture mobile seul rend son WebP sans media query', '<source srcset="/images/mobile-only.webp" type="image/webp">', $mobile_only_picture);
$assert_contains('Picture mobile seul conserve l’original comme fallback', '<img src="/images/mobile-only.jpg"', $mobile_only_picture);

$picture_source_priority = $render('picture', [
    'image' => '/images/simple.jpg',
    'images' => '/images/priority.jpg',
]);
$assert_contains('Picture donne la priorité à images lorsqu’il est fourni', '<img src="/images/priority.jpg"', $picture_source_priority);

$critical_picture = $render('picture', [
    'images' => '/images/critical.jpg',
    'critical' => 'true',
    'lazy' => true,
]);
$assert_contains('Picture donne une priorité haute aux images critiques', 'fetchpriority="high"', $critical_picture);
$assert_not_contains('Picture ne charge jamais une image critique en lazy', 'loading="lazy"', $critical_picture);

$placeholder_picture = $render('picture', [
    'images' => null,
    'classes' => 'media-placeholder',
    'placeholder' => true,
]);
$assert_same('Picture rend son placeholder explicite', '<picture class="picture fit-fluid placeholder media-placeholder"></picture>', $placeholder_picture);
$assert_same('Les variantes picture à la racine sont retirées', '', $render('picture', ['desktop' => '/images/legacy.jpg']));
$assert_same('Une URL picture simple dangereuse ne produit aucun HTML', '', $render('picture', ['image' => 'javascript:alert(1)']));
$assert_same('Une URL picture dangereuse ne produit aucun HTML', '', $render('picture', ['images' => 'javascript:alert(1)']));

$video = $render('video', [
    'sources' => ['videos/demo.webm', '/videos/demo.mp4', '/videos/demo.unknown'],
    'title' => 'Démonstration',
    'autoplay' => 'false',
    'loop' => 'true',
    'classes' => 'video-featured',
    'attributes' => ['class' => 'duplicate', 'data-type' => 'duplicate', 'data-track' => 'video'],
    'tracks' => [[
        'src' => '/videos/demo-fr.vtt',
        'kind' => 'subtitles',
        'srclang' => 'fr<script>',
        'label' => 'Français',
        'default' => true,
    ]],
]);
$assert_contains('La vidéo accepte les chemins relatifs et les sources multiples', '<source src="videos/demo.webm" type="video/webm">', $video);
$assert_contains('La vidéo détecte le MP4', '<source src="/videos/demo.mp4" type="video/mp4">', $video);
$assert_contains('Une extension inconnue ne reçoit pas un faux type MIME', '<source src="/videos/demo.unknown">', $video);
$assert_contains('La chaîne false ne déclenche pas l’autoplay', 'data-autoplay="false"', $video);
$assert_not_contains('La chaîne false ne produit pas l’attribut autoplay', ' autoplay', $video);
$assert_contains('La chaîne true active la boucle', ' loop', $video);
$assert_contains('La vidéo conserve ses attributs libres', 'data-track="video"', $video);
$assert_contains('La vidéo protège ses attributs structurels', 'class="video video-featured" data-type="video"', $video);
$assert_not_contains('La vidéo refuse une classe injectée par les attributs', 'class="duplicate"', $video);
$assert_contains('La vidéo normalise une piste de sous-titres', '<track src="/videos/demo-fr.vtt" kind="subtitles" srclang="frscript" label="Français" default>', $video);

$facade_video = $render('video', [
    'url' => 'https://youtu.be/dQw4w9WgXcQ',
    'title' => 'YouTube',
    'poster' => '/images/poster.jpg',
]);
$assert_contains('L’API procédurale du composant vidéo est supportée', 'data-type="youtube"', $facade_video);
$assert_contains('La façade active le module vidéo', 'data-module="components/video"', $facade_video);
$assert_contains('YouTube utilise le domaine respectueux de la vie privée', 'data-src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?', $facade_video);
$assert_not_contains('La façade diffère la source de l’iframe', ' src="https://www.youtube-nocookie.com', $facade_video);
$assert_contains('Le bouton de façade possède un nom accessible', 'aria-label="Lire la vidéo : YouTube"', $facade_video);

$external_video_without_poster = $render('video', [
    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'title' => 'YouTube sans poster',
]);
$assert_not_contains('Un lecteur externe sans poster ne crée pas de façade', 'class="poster"', $external_video_without_poster);
$assert_contains('Un lecteur externe sans poster attend le viewport', 'data-context="@visible true"', $external_video_without_poster);
$assert_contains('Un lecteur externe sans poster conserve sa source différée', 'data-src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?', $external_video_without_poster);
$assert_not_contains('Un lecteur externe sans poster ne charge pas son iframe au rendu PHP', ' src="https://www.youtube-nocookie.com', $external_video_without_poster);

$autoplay_video = $render('video', [
    'url' => '/videos/background.mp4',
    'title' => 'Arrière-plan',
    'autoplay' => true,
    'loop' => false,
]);
$assert_contains('La vidéo utilise son payload procédural strict', 'title="Arrière-plan"', $autoplay_video);
$assert_contains('L’autoplay HTML5 est hydraté à la visibilité', 'data-context="@visible true"', $autoplay_video);
$assert_contains('L’autoplay HTML5 est muet et inline', 'muted playsinline data-lazy-autoplay', $autoplay_video);
$assert_not_contains('L’autoplay différé ne démarre pas avant hydratation', ' autoplay', $autoplay_video);

$unsafe_video = $render('video', ['url' => 'javascript:alert(1)']);
$assert_same('Une URL vidéo dangereuse ne produit aucun HTML', '', $unsafe_video);
$assert_same('Une source structurée retire l’ancien alias url', '', $render('video', [
    'sources' => [['url' => '/videos/legacy.mp4']],
]));
$assert_same('La vidéo refuse les arguments positionnels', '', $render('video', [
    '/videos/legacy.mp4',
    'Ancienne forme',
]));

$tag = $render('tag', ['name' => 'Design', 'classes' => 'tag-featured']);
$assert_contains('Le tag affiche un libellé informatif', '<span class="tag tag-featured">Design</span>', $tag);

$procedural_tag = $render('tag', [
    'name' => 'Procédural',
    'attributes' => ['class' => 'duplicate', 'data-track' => 'tag'],
]);
$assert_contains('Le tag supporte l’API procédurale', '<span class="tag" data-track="tag">Procédural</span>', $procedural_tag);
$assert_not_contains('Le tag protège sa classe structurelle', 'class="duplicate"', $procedural_tag);

$escaped_tag = $render('tag', [
    'name' => 'Design <script>alert(1)</script>',
    'classes' => 'tag-small',
    'attributes' => ['data-category' => 'design'],
]);
$assert_contains('Le tag échappe son contenu', 'Design &lt;script&gt;alert(1)&lt;/script&gt;', $escaped_tag);
$assert_contains('Le tag conserve les classes et attributs', 'class="tag tag-small" data-category="design"', $escaped_tag);

$assert_same('Un tag vide ne produit aucun HTML', '', $render('tag', ['name' => '   ']));
$assert_same('Un tag complexe ne produit aucun HTML', '', $render('tag', ['name' => ['invalide']]));
$assert_same('Le tag refuse une liste directe', '', $render('tag', ['Ancienne forme']));

$tag_button = $render('btn', ['name' => 'Filtrer', 'classes' => 'tag']);
$assert_contains('Un bouton peut adopter le style du tag', '<button type="button" class="btn tag"', $tag_button);

$tag_link = $render('link', [
    'link' => [
        'url' => '/design/',
        'title' => 'Design',
    ],
    'classes' => 'tag',
]);
$assert_contains('Un lien peut adopter le style du tag', '<a href="/design/" class="link tag"', $tag_link);

$dialog = $render('dialog', [
    'id' => 'dialog-test',
    'title' => 'Titre <dangereux>',
    'content' => '<p>Contenu <strong>riche</strong></p><script>alert(1)</script>',
    'trigger' => ['type' => 'btn', 'label' => 'Ouvrir', 'classes' => 'primary'],
    'close_label' => 'Fermer',
    'attributes' => [
        'id' => 'duplicate',
        'class' => 'duplicate',
        'open' => true,
        'data-module' => 'duplicate',
        'data-tracking' => 'dialog-test',
    ],
]);
$assert_contains('Le déclencheur du dialogue cible son ID', 'data-dialog-id="dialog-test"', $dialog);
$assert_contains('Le déclencheur expose le contrôle ARIA', 'aria-controls="dialog-test"', $dialog);
$assert_contains('Le dialogue est nommé par son titre', 'aria-labelledby="dialog-test-title"', $dialog);
$assert_contains('Le dialogue conserve les attributs libres', 'data-tracking="dialog-test"', $dialog);
$assert_contains('Le dialogue conserve le HTML éditorial autorisé', '<strong>riche</strong>', $dialog);
$assert_not_contains('Le dialogue échappe son titre', '<dangereux>', $dialog);
$assert_not_contains('Le dialogue filtre les balises interdites', '<script>', $dialog);
$assert_not_contains('Le dialogue protège son ID', 'id="duplicate"', $dialog);
$assert_not_contains('Le dialogue protège son état modal', ' open', $dialog);
$assert_not_contains('Le dialogue protège son module', 'data-module="duplicate"', $dialog);

$procedural_dialog = $render('dialog', [
    'id' => 'dialog-procedural',
    'content' => '<p>Mode procédural</p>',
    'trigger' => ['type' => 'btn', 'label' => 'Ouvrir'],
]);
$assert_contains('Le dialogue supporte le payload procédural', 'id="dialog-procedural"', $procedural_dialog);

$labelled_dialog = $render('dialog', [
    'content' => '<p>Confirmation</p>',
    'trigger' => ['type' => 'btn', 'label' => 'Supprimer'],
    'aria_label' => 'Confirmation de suppression',
]);
$assert_contains('Le dialogue donne priorité au libellé ARIA explicite', 'aria-label="Confirmation de suppression"', $labelled_dialog);

$fallback_label_dialog = $render('dialog', [
    'content' => '<p>Contenu sans déclencheur</p>',
    'trigger' => [],
    'aria_label' => '',
]);
$assert_contains('Le dialogue garantit un nom accessible', 'aria-label="Dialog"', $fallback_label_dialog);

$link_trigger_dialog = $render('dialog', [
    'id' => 'dialog-link-trigger',
    'content' => '<p>Contenu</p>',
    'trigger' => ['type' => 'link', 'label' => 'Ouvrir par lien', 'classes' => 'secondary'],
]);
$assert_contains('Le dialogue délègue un déclencheur link au composant link', '<a href="#dialog-link-trigger" class="link dialog-trigger secondary"', $link_trigger_dialog);

$legacy_trigger_dialog = $render('dialog', [
    'content' => '<p>Contenu</p>',
    'trigger_label' => 'Ancien libellé',
]);
$assert_not_contains('Le dialogue retire l’ancienne clé trigger_label', 'Ancien libellé', $legacy_trigger_dialog);
$assert_same('Le dialogue refuse les arguments positionnels', '', $render('dialog', [
    '<p>Ancienne forme</p>',
    ['btn', 'Ouvrir'],
]));

$shares = $render('shares', [
    'title' => 'Partager cette page',
    'url' => 'https://example.test/article/?source=test',
    'subject' => 'À découvrir',
    'list' => ['copy', 'linkedin', 'email', 'linkedin', 'unknown'],
    'classes' => ['shares-article', 'shares-article'],
    'attributes' => [
        'class' => 'duplicate',
        'data-module' => 'duplicate',
        'data-tracking' => 'share',
    ],
]);
$assert_contains('Les partages nomment leur navigation avec le titre', 'aria-labelledby="shares-title-', $shares);
$assert_contains('Les partages rendent les réseaux comme des liens', '<a', $shares);
$assert_contains('Les partages conservent un bouton pour la copie', 'data-share-copy', $shares);
$assert_contains('Les partages sécurisent les liens externes', 'target="_blank" rel="noopener noreferrer"', $shares);
$assert_contains('Les partages rendent l’e-mail comme un lien natif', 'href="mailto:?subject=', $shares);
$assert_contains('Les partages conservent les attributs libres', 'data-tracking="share"', $shares);
$assert_not_contains('Les partages protègent leur classe', 'class="duplicate"', $shares);
$assert_not_contains('Les partages protègent leur module', 'data-module="duplicate"', $shares);
$assert_same('Les partages suppriment les doublons', 1, substr_count($shares, 'Partager sur LinkedIn'));

$procedural_shares = $render('shares', [
    'list' => [
        'linkedin' => ['label' => 'Réseau professionnel'],
        'email' => ['href' => 'contact@example.test'],
        'facebook' => ['href' => 'javascript:alert(1)'],
        'copy' => [],
    ],
    'url' => 'https://example.test/page/',
    'aria_label' => 'Diffuser cette page',
    'copied_label' => 'Copie réussie',
    'copy_error_label' => 'Échec de la copie',
]);
$assert_contains('Les partages supportent le payload procédural', 'Réseau professionnel', $procedural_shares);
$assert_contains('Les partages limitent mailto au service e-mail', 'href="mailto:contact@example.test"', $procedural_shares);
$assert_not_contains('Les partages refusent les destinations dangereuses', 'javascript:', $procedural_shares);
$assert_not_contains('Un service dont la destination est invalide est ignoré', 'Partager sur Facebook', $procedural_shares);
$assert_contains('Les partages acceptent un nom accessible explicite', 'aria-label="Diffuser cette page"', $procedural_shares);
$assert_contains('Les partages exposent le message de copie traduit par l’appelant', 'data-success="Copie réussie"', $procedural_shares);
$assert_contains('Les partages exposent le message d’erreur traduit par l’appelant', 'data-error="Échec de la copie"', $procedural_shares);

$legacy_share_href = $render('shares', [
    'list' => ['linkedin' => ['url' => 'https://example.test/legacy-share/']],
    'url' => 'https://example.test/page/',
]);
$assert_not_contains('Les partages retirent l’alias url des services', 'legacy-share', $legacy_share_href);
$assert_same('Les partages refusent les arguments positionnels', '', $render('shares', [
    'Partager',
    ['copy'],
]));

$socials = $render('socials', [
    'items' => [
        ['network' => 'linkedin', 'url' => 'https://www.linkedin.com/company/example/'],
        ['network' => 'facebook', 'url' => 'https://www.facebook.com/example/'],
        ['network' => 'linkedin', 'url' => 'https://www.linkedin.com/company/duplicate/'],
        ['network' => 'unknown', 'url' => 'https://example.test/unknown/'],
        ['network' => 'instagram', 'url' => 'javascript:alert(1)'],
    ],
    'aria_label' => 'Retrouvez-nous',
    'classes' => 'socials-footer',
    'attributes' => ['class' => 'duplicate', 'data-location' => 'footer'],
]);
$assert_contains('Socials rend une navigation accessible', '<nav class="socials socials-footer" aria-label="Retrouvez-nous" data-location="footer">', $socials);
$assert_contains('Socials ouvre les liens externes dans un nouvel onglet', 'target="_blank"', $socials);
$assert_contains('Socials protège les liens externes', 'rel="noopener noreferrer"', $socials);
$assert_contains('Socials utilise une icône disponible dans le sprite', 'icon-linkedin', $socials);
$assert_contains('Socials utilise l’icône correspondant au réseau', 'icon-facebook', $socials);
$assert_contains('Socials conserve le nom visible du réseau', '<span class="socials-label">Facebook</span>', $socials);
$assert_same('Socials déduplique les réseaux', 1, substr_count($socials, 'socials-item-linkedin'));
$assert_not_contains('Socials ignore les réseaux inconnus', 'unknown', $socials);
$assert_not_contains('Socials ignore les URL dangereuses', 'javascript:', $socials);
$assert_not_contains('Socials protège sa classe structurelle', 'class="duplicate"', $socials);
$assert_same('Socials refuse les arguments positionnels', '', $render('socials', [
    ['network' => 'linkedin', 'url' => 'https://www.linkedin.com/'],
]));

$picto = $render('picto', [
    'name' => 'star',
    'label' => 'Étoile',
    'type' => 'picto-3',
    'size' => 'lg',
    'classes' => ['picto-featured', 'picto-featured'],
    'attributes' => [
        'class' => 'duplicate',
        'data-tracking' => 'star',
    ],
]);
$assert_contains('Le picto utilise un conteneur inline', '<span class="picto picto-featured"', $picto);
$assert_contains('Le picto conserve les attributs libres', 'data-tracking="star"', $picto);
$assert_not_contains('Le picto protège sa classe', 'class="duplicate"', $picto);
$assert_not_contains('Le picto ignore l’ancien type', 'picto-3', $picto);
$assert_not_contains('Le picto ignore l’ancienne taille', ' lg', $picto);

$picto_name = new class implements Stringable {
    public function __toString(): string
    {
        return 'training';
    }
};
$procedural_picto = $render('picto', [
    'name' => $picto_name,
    'classes' => 'picto-training',
]);
$assert_contains('Le picto supporte le payload procédural et Stringable', '<span class="picto picto-training">', $procedural_picto);

$invalid_picto = $render('picto', ['name' => ['star']]);
$assert_same('Le picto refuse un name de type invalide', '', $invalid_picto);

$date = $render('date', [
    'date' => '20 juillet 2026',
    'datetime' => '20260720',
    'classes' => ['date-news', 'date-news'],
    'attributes' => [
        'class' => 'duplicate',
        'datetime' => 'duplicate',
        'data-tracking' => 'publication-date',
    ],
]);
$assert_contains('La date normalise le format ACF', '<time class="date date-news" datetime="2026-07-20"', $date);
$assert_contains('La date conserve les attributs libres', 'data-tracking="publication-date"', $date);
$assert_not_contains('La date protège sa classe', 'class="duplicate"', $date);
$assert_not_contains('La date protège son datetime', 'datetime="duplicate"', $date);

$procedural_date = $render('date', [
    'date' => '<strong>20 juillet 2026</strong><p>bloc</p><script>alert(1)</script>',
    'datetime' => '20/07/2026',
]);
$assert_contains('La date supporte le payload procédural', 'datetime="2026-07-20"', $procedural_date);
$assert_contains('La date conserve le HTML inline autorisé', '<strong>20 juillet 2026</strong>', $procedural_date);
$assert_not_contains('La date supprime les balises de bloc', '<p>', $procedural_date);
$assert_not_contains('La date filtre les scripts', '<script>', $procedural_date);

$fallback_date = $render('date', [
    'date' => '20 juillet 2026',
    'datetime' => '2026-02-30',
]);
$assert_contains('La date visible reste affichée sans datetime valide', '<span class="date">20 juillet 2026</span>', $fallback_date);
$assert_not_contains('La date invalide ne produit pas de time', '<time', $fallback_date);

$automatic_date = $render('date', ['date' => '2026-07-20']);
$assert_contains('La date ISO visible est auto-détectée', 'datetime="2026-07-20"', $automatic_date);

$empty_date = $render('date', ['date' => '']);
$assert_same('La date vide ne produit aucun HTML', '', $empty_date);
$assert_same('La date retire l’ancien alias text', '', $render('date', ['text' => '20 juillet 2026']));
$assert_same('La date refuse une liste directe', '', $render('date', ['20 juillet 2026']));

$tooltip = $render('tooltip', [
    'label' => 'Délai <rapide>',
    'content' => 'Entre <strong>3 et 5 jours</strong>. <a href="/offre">Offre</a>',
    'classes' => ['compact', 'compact'],
    'attributes' => [
        'class' => 'duplicate',
        'data-module' => 'duplicate',
        'data-tooltip-open' => 'true',
        'data-tracking' => 'delivery-delay',
    ],
]);
$assert_contains('Le tooltip charge son module', 'data-module="components/tooltip"', $tooltip);
$assert_contains('Le tooltip associe son contenu au bouton', 'aria-describedby="tooltip-', $tooltip);
$assert_contains('Le tooltip conserve les balises inline autorisées', '<strong>3 et 5 jours</strong>', $tooltip);
$assert_contains('Le tooltip conserve les attributs libres', 'data-tracking="delivery-delay"', $tooltip);
$assert_not_contains('Le tooltip échappe son libellé', '<rapide>', $tooltip);
$assert_not_contains('Le tooltip refuse les liens interactifs', '<a ', $tooltip);
$assert_not_contains('Le tooltip protège sa classe', 'class="duplicate"', $tooltip);
$assert_not_contains('Le tooltip protège son module', 'data-module="duplicate"', $tooltip);
$assert_not_contains('Le tooltip protège son état', 'data-tooltip-open="true"', $tooltip);

$zero_tooltip = $render('tooltip', ['label' => '0', 'content' => 'Valeur zéro']);
$assert_contains('Le libellé zéro reste valide', '>0</button>', preg_replace('/\s+/', '', $zero_tooltip) ?? '');

$procedural_tooltip = $render('tooltip', [
    'label' => 'API',
    'content' => 'Application Programming Interface',
]);
$assert_contains('Le tooltip supporte le payload procédural', 'Application Programming Interface', $procedural_tooltip);
$assert_same('Le tooltip refuse les arguments positionnels', '', $render('tooltip', ['API', 'Ancienne forme']));

$strict_text = $render('text', [
    'text' => '<p>Texte <strong>important</strong></p>',
    'classes' => ['intro', 'intro'],
    'attributes' => ['class' => 'duplicate', 'id' => 'introduction'],
]);
$assert_contains('Le texte utilise son payload procédural strict', '<div class="text intro" id="introduction">', $strict_text);
$assert_contains('Le texte conserve le HTML éditorial autorisé', '<strong>important</strong>', $strict_text);
$assert_not_contains('Le texte protège sa classe structurelle', 'class="duplicate"', $strict_text);
$assert_same('Les anciens alias du texte sont retirés', '', $render('text', ['intro' => 'Ancien alias']));

$title = $render('title', [
    'title' => 'Titre <strong>important</strong>',
    'hx' => 3,
    'classes' => ['title-3', 'title-3'],
    'attributes' => ['class' => 'duplicate', 'id' => 'section-title'],
]);
$assert_contains('Le titre utilise son payload associatif', '<h3 class="title title-3" id="section-title">', $title);
$assert_contains('Le titre conserve le HTML éditorial autorisé', '<strong>important</strong>', $title);
$assert_not_contains('Le titre protège sa classe structurelle', 'class="duplicate"', $title);

$procedural_title = $render('title', ['title' => 'Titre procédural', 'hx' => 2]);
$assert_contains('Le titre supporte directement l’API procédurale', '<h2 class="title">Titre procédural</h2>', $procedural_title);
$assert_same('Les anciens alias du titre sont retirés', '', $render('title', ['heading' => 'Ancien alias']));

$invalid_hx_title = $render('title', ['title' => 'Niveau invalide', 'hx' => 'h3']);
$assert_contains('Le format historique h3 revient au niveau par défaut', '<h2 class="title">Niveau invalide</h2>', $invalid_hx_title);

$rendering_component_fixture = true;
ob_start();
component('title', ['title' => 'Appel direct', 'classes' => 'title-2']);
$direct_title = trim((string) ob_get_clean());
$rendering_component_fixture = false;
$assert_contains('component() rend title sans passer par le dispatcher statique', '<h2 class="title title-2">Appel direct</h2>', $direct_title);

$assert_same('L’ancienne API scalaire du titre est retirée', '', $render('title', ['Titre historique']));

$header = $render('header', [
    'title' => 'Titre <strong>important</strong>',
    'text' => '<p>Introduction <em>éditoriale</em>.</p>',
    'link' => [
        'url' => '/destination/',
        'title' => 'Découvrir',
    ],
    'classes' => ['section-intro', 'section-intro'],
    'attributes' => [
        'class' => 'duplicate',
        'id' => 'section-header',
    ],
    'title_classes' => 'title-custom',
    'text_classes' => 'text-custom',
]);
$assert_contains('Le header impose un titre h2', '<h2 class="title title-custom">', $header);
$assert_contains('Le header délègue le contenu riche du titre', '<strong>important</strong>', $header);
$assert_contains('Le header délègue son texte', '<div class="text text-custom">', $header);
$assert_contains('Le header rend un lien valide', 'href="/destination/"', $header);
$assert_contains('Le header conserve ses attributs libres', 'id="section-header"', $header);
$assert_not_contains('Le header protège sa classe', 'class="duplicate"', $header);
$assert_not_contains('Le header ne produit jamais de h1', '<h1', $header);

$invalid_link_header = $render('header', [
    'link' => [
        'url' => 'javascript:alert(1)',
        'title' => 'Lien interdit',
    ],
]);
$assert_same('Un lien invalide ne produit pas de header vide', '', $invalid_link_header);

$procedural_header = $render('header', [
    'title' => 'Titre procédural',
]);
$assert_contains('Le header supporte le payload procédural', '<h2 class="title">Titre procédural</h2>', $procedural_header);

$slider = $render('slider', [
    'items' => [
        '<article>Premier</article>',
        '',
        ['contenu invalide'],
        '<article>Second</article>',
    ],
    'card' => null,
    'aria_label' => 'Sélection éditoriale',
    'attributes' => [
        'role' => 'presentation',
        'class' => 'duplicate',
        'data-module' => 'duplicate',
        'data-tracking' => 'editorial-slider',
    ],
]);
$assert_same('Le slider direct ignore les items incompatibles ou vides', 2, substr_count($slider, '<li'));
$assert_contains('Le slider charge son module', 'data-module="components/slider"', $slider);
$assert_contains('Le slider expose une région de carrousel', 'aria-roledescription="carousel"', $slider);
$assert_contains('Le slider expose une liste valide', 'role="list"', $slider);
$assert_same('Chaque slide conserve le rôle requis par la liste', 2, substr_count($slider, 'role="listitem"'));
$assert_contains('Le slider nomme chaque slide avec sa position réelle', 'aria-label="Diapositive 2 sur 2"', $slider);
$assert_contains('Le slider protège le scroll horizontal de Lenis', 'data-lenis-prevent-horizontal', $slider);
$assert_contains('Le slider conserve ses attributs libres', 'data-tracking="editorial-slider"', $slider);
$assert_not_contains('Le slider protège son rôle structurel', 'role="presentation"', $slider);
$assert_not_contains('Le slider protège sa classe', 'class="duplicate"', $slider);
$assert_not_contains('Le slider protège son module', 'data-module="duplicate"', $slider);

$parent_slider = $render('slider', [
    'items' => ['Premier', 'Second'],
    'card' => null,
    'hydrate' => false,
    'navigation' => true,
    'pagination' => true,
    'timeline' => true,
]);
$assert_contains('Le slider accepte son payload procédural strict', '>Premier</li>', preg_replace('/\s+/', '', $parent_slider) ?? '');
$assert_not_contains('Le slider non hydraté ne charge aucun module', 'data-module=', $parent_slider);
$assert_contains('La navigation reste indépendante de hydratation', 'slider-navigation', $parent_slider);
$assert_contains('La pagination reste indépendante de hydratation', 'slider-pagination', $parent_slider);
$assert_contains('La timeline reste indépendante de hydratation', 'slider-timeline', $parent_slider);
$assert_contains('Le statut reste disponible pour une hydratation parente', 'data-slider-status', $parent_slider);

$procedural_slider = $render('slider', [
    'items' => ['Mode procédural'],
    'card' => null,
    'hydrate' => false,
]);
$assert_contains('Le slider supporte le payload procédural', 'Mode procédural', $procedural_slider);

$invalid_card_slider = $render('slider', [
    'items' => [[], 'invalide'],
    'card' => 'news',
]);
$assert_same('Le slider avec card ignore les données incompatibles ou vides', '', $invalid_card_slider);
$post_slider = $render('slider', [
    'items' => [108, 0, '109', ['post_id' => 110, 'title' => 'Titre slider surchargé']],
    'card' => 'news',
    'hydrate' => false,
]);
$assert_same('Le slider avec card accepte les IDs et les payloads associatifs', 2, substr_count($post_slider, '<article class="card-news">'));
$assert_contains('Le slider transmet un ID à la card', 'Actualité 108', $post_slider);
$assert_contains('Le slider conserve les surcharges explicites', 'Titre slider surchargé', $post_slider);
$assert_same('Le slider exige une liste d’items', '', $render('slider', [
    'items' => ['first' => '<p>Premier</p>'],
    'card' => null,
]));
$assert_same('Le slider refuse les arguments positionnels', '', $render('slider', [
    ['Premier', 'Second'],
    null,
]));

$translated_slider = $render('slider', [
    'items' => ['Premier', 'Second'],
    'card' => null,
    'slide_label' => 'Slide',
    'slide_position_format' => '{label} {current} of {total}',
]);
$assert_contains('Le slider accepte un format de position fourni par l’appelant', 'aria-label="Slide 2 of 2"', $translated_slider);

$picture_slider = $render('slider', [
    'items' => [
        [
            'images' => ['desktop' => 201, 'mobile' => 202],
            'classes' => 'slider-image',
        ],
        [],
    ],
    'item_component' => 'picture',
    'card' => null,
    'hydrate' => false,
]);
$assert_same('Le slider picture ignore les payloads invalides', 1, substr_count($picture_slider, '<li'));
$assert_contains('Le slider conserve le picture responsive', '<picture class="picture fit-fluid slider-image">', $picture_slider);
$assert_contains('Le slider rend la source mobile', 'srcset="202"', $picture_slider);
$assert_contains('Le slider rend la source desktop', 'srcset="201"', $picture_slider);

$intro = $render('intro', [
    'text' => '<p>Introduction <strong>riche</strong></p><script>alert(1)</script>',
    'classes' => ['featured', 'featured'],
    'attributes' => ['class' => 'duplicate', 'id' => 'page-intro'],
]);
$assert_contains('L’intro conserve le HTML éditorial', '<strong>riche</strong>', $intro);
$assert_not_contains('L’intro filtre les scripts', '<script>', $intro);
$assert_contains('L’intro conserve ses classes et attributs', '<div class="intro featured" id="page-intro">', $intro);
$assert_not_contains('L’intro protège sa classe', 'class="duplicate"', $intro);
$assert_same('Une intro vide ne produit aucun HTML', '', $render('intro', ['text' => '   ']));
$assert_same('Intro refuse les arguments positionnels', '', $render('intro', ['Ancienne introduction']));

$quote_component = $render('quote', [
    'quote' => '<p>Une citation.</p>',
    'image' => 321,
    'author' => 'Auteur <dangereux>',
    'source' => 'Source',
    'url' => 'javascript:alert(1)',
    'classes' => ['featured', 'featured'],
    'attributes' => ['class' => 'duplicate', 'id' => 'quote-test'],
]);
$assert_contains('Quote rend son image facultative', '<img class="quote-image" src="321"', $quote_component);
$assert_contains('Quote rend un blockquote', '<blockquote class="quote-content">', $quote_component);
$assert_not_contains('Quote échappe son auteur', '<dangereux>', $quote_component);
$assert_not_contains('Quote refuse une URL dangereuse', '<a href=', $quote_component);
$assert_contains('Quote conserve ses classes et attributs', '<figure class="quote featured" id="quote-test">', $quote_component);
$assert_not_contains('Quote protège sa classe structurelle', 'class="duplicate"', $quote_component);

$linked_quote = $render('quote', [
    'quote' => '<p>Texte <strong>riche</strong>.</p><script>alert(1)</script>',
    'author' => 'Jane Doe',
    'source' => 'Publication',
    'url' => '/publication/',
]);
$assert_contains('Quote conserve le HTML éditorial autorisé', '<strong>riche</strong>', $linked_quote);
$assert_not_contains('Quote filtre les scripts', '<script>', $linked_quote);
$assert_contains('Quote lie une source valide', '<cite class="quote-source">', $linked_quote);
$assert_contains('Quote échappe et rend une URL sûre', '<a href="/publication/">Publication</a>', $linked_quote);
$assert_same('Une quote vide ne produit aucun HTML', '', $render('quote', ['quote' => '   ']));
$assert_same('Quote refuse les arguments positionnels', '', $render('quote', [
    'Ancienne citation',
    'Ancien auteur',
]));

$search = $render('search', [
    'value' => '<requête>',
    'label' => 'Chercher sur le site',
    'placeholder' => 'Votre recherche',
    'submit_label' => 'Lancer la recherche',
    'attributes' => ['role' => 'presentation', 'data-track' => 'search'],
]);
$assert_contains('La recherche utilise une méthode GET', 'method="get"', $search);
$assert_contains('La recherche conserve ses attributs libres', 'data-track="search"', $search);
$assert_not_contains('La recherche protège son rôle', 'role="presentation"', $search);
$assert_not_contains('La recherche échappe sa valeur', 'value="<requête>"', $search);
$assert_contains('La recherche accepte les libellés traduits par l’appelant', '>Lancer la recherche</button>', $search);
$assert_same('La recherche refuse les arguments positionnels', '', $render('search', [
    'requête',
    'placeholder',
    'submit',
]));

$native_select = $render('select', [
    'name' => 'city',
    'label' => 'Ville',
    'value' => 'lyon',
    'options' => [
        ['value' => 'paris', 'label' => 'Paris'],
        ['value' => 'lyon', 'label' => 'Lyon'],
    ],
]);
$assert_contains('Le select natif génère un identifiant unique', '<select id="select-field-', $native_select);
$assert_contains('Le select natif conserve son nom soumis', 'name="city"', $native_select);
$assert_contains('Le select natif sélectionne la valeur active', 'value="lyon" selected', $native_select);
$assert_not_contains('Le select natif ne charge aucun module JS', 'data-module=', $native_select);

$procedural_select = $render('select', [
    'name' => 'status',
    'aria_label' => 'Statut de publication',
    'placeholder' => 'Choisir un statut',
    'required' => 'false',
    'disabled' => 'false',
    'value' => ['invalid'],
    'options' => [
        ['value' => 'draft', 'label' => 'Brouillon', 'selected' => 'false'],
        ['value' => 'published', 'label' => 'Publié', 'selected' => 'true'],
        ['value' => 'archived', 'label' => 'Archivé', 'selected' => true],
        ['value' => '', 'label' => 'Valeur vide'],
    ],
    'attributes' => ['class' => 'duplicate', 'data-field' => 'status'],
    'select_attributes' => [
        'id' => 'duplicate',
        'name' => 'duplicate',
        'multiple' => true,
        'aria-describedby' => 'status-help',
        'data-track' => 'status',
    ],
]);
$assert_contains('Le select natif supporte le payload procédural', 'name="status"', $procedural_select);
$assert_contains('Le select sans label utilise aria_label', 'aria-label="Statut de publication"', $procedural_select);
$assert_contains('Le select conserve ses attributs dédiés', 'aria-describedby="status-help" data-track="status"', $procedural_select);
$assert_contains('Le select conserve les attributs du conteneur', 'data-field="status"', $procedural_select);
$assert_contains('Le select normalise selected true', 'value="published" selected', $procedural_select);
$assert_same('Le select ne conserve qu’une option sélectionnée', 1, substr_count($procedural_select, ' selected'));
$assert_not_contains('Le select normalise required false', ' required', $procedural_select);
$assert_not_contains('Le select normalise disabled false', ' disabled', $procedural_select);
$assert_not_contains('Le select réserve multiple', ' multiple', $procedural_select);
$assert_not_contains('Le select ignore les options à valeur vide', 'Valeur vide', $procedural_select);
$assert_not_contains('Le select protège la classe du conteneur', 'class="duplicate"', $procedural_select);
$assert_not_contains('Le select protège son identifiant', 'id="duplicate"', $procedural_select);
$assert_not_contains('Le select protège son nom', 'name="duplicate"', $procedural_select);

$required_select = $render('select', [
    'name' => 'office',
    'label' => 'Bureau',
    'placeholder' => 'Choisir un bureau',
    'required' => 'true',
    'value' => 'closed',
    'options' => [
        ['value' => 'paris', 'label' => 'Paris'],
        ['value' => 'closed', 'label' => 'Fermé', 'selected' => true, 'disabled' => 'true'],
    ],
]);
$assert_contains('Le select applique required true', 'name="office" required', $required_select);
$assert_contains('Le placeholder requis reste la sélection de secours', '<option value="" selected disabled>', $required_select);
$assert_contains('Le select conserve une option désactivée', 'value="closed" disabled', $required_select);
$assert_not_contains('Une option désactivée ne devient pas selected', 'value="closed" selected', $required_select);

$priority_select = $render('select', [
    'name' => 'priority',
    'value' => 'second',
    'options' => [
        ['value' => 'first', 'label' => 'Première', 'selected' => true],
        ['value' => 'second', 'label' => 'Deuxième', 'selected' => true],
    ],
]);
$assert_contains('La valeur globale gagne sur selected', 'value="second" selected', $priority_select);
$assert_not_contains('La sélection déclarative perd face à la valeur globale', 'value="first" selected', $priority_select);
$assert_same('La priorité conserve une seule sélection', 1, substr_count($priority_select, ' selected'));

$duplicate_name_select = $render('select', [
    'name' => 'city',
    'options' => ['paris' => 'Paris'],
]);
preg_match('/<select id="([^"]+)"/', $native_select, $first_select_id);
preg_match('/<select id="([^"]+)"/', $duplicate_name_select, $second_select_id);
$assert_same(
    'Deux selects portant le même nom reçoivent des IDs différents',
    false,
    ($first_select_id[1] ?? '') === ($second_select_id[1] ?? '')
);
$assert_same('Le select retire l’ancien alias name des options', '', $render('select', [
    'name' => 'legacy',
    'options' => [['value' => 'legacy', 'name' => 'Ancien libellé']],
]));
$assert_same('Le select refuse les arguments positionnels', '', $render('select', [
    ['paris' => 'Paris'],
    'city',
    'Ville',
]));

$custom_select = $render('select-custom', [
    'name' => 'topics',
    'multiple' => true,
    'required' => 'true',
    'value' => ['design', 'research,design'],
    'options' => [
        ['value' => 'design', 'label' => 'Design'],
        ['value' => 'research,design', 'label' => 'Recherche'],
        ['value' => 'code', 'label' => 'Code'],
        ['value' => 'disabled', 'label' => 'Désactivé', 'selected' => true, 'disabled' => 'true'],
        ['value' => 'ignored', 'label' => 'Ignoré', 'selected' => 'false'],
    ],
    'select_attributes' => ['aria-describedby' => 'topics-help', 'multiple' => false],
]);
$assert_contains('Le select personnalisé charge son module', 'data-module="components/select-custom"', $custom_select);
$assert_contains('Le select personnalisé conserve un fallback natif', 'class="select-custom-native" name="topics[]"', $custom_select);
$assert_contains('Le select personnalisé expose son mode multiple', 'aria-multiselectable="true"', $custom_select);
$assert_contains('Le select personnalisé applique required', 'data-select-native multiple required', $custom_select);
$assert_contains('Le select personnalisé conserve ses attributs dédiés', 'aria-describedby="topics-help"', $custom_select);
$assert_contains('Le select multiple conserve une valeur avec virgule', 'value="research,design" selected', $custom_select);
$assert_not_contains('Le select personnalisé ne concatène plus les valeurs', 'type="hidden"', $custom_select);
$assert_not_contains('Le select personnalisé normalise selected false', 'value="ignored" selected', $custom_select);
$assert_not_contains('Une option désactivée ne devient pas selected', 'value="disabled" selected', $custom_select);
$assert_not_contains('Les attributs dédiés ne remplacent pas multiple', 'aria-describedby="topics-help" multiple', $custom_select);

$single_custom_select = $render('select-custom', [
    'name' => 'city',
    'aria_label' => 'Ville de résidence',
    'value' => ['invalid'],
    'options' => [
        ['value' => 'paris', 'label' => 'Paris', 'selected' => 'true'],
        ['value' => 'lyon', 'label' => 'Lyon', 'selected' => true],
    ],
]);
$assert_contains('Le select custom sans label reçoit un libellé accessible', 'select-custom-label--visually-hidden', $single_custom_select);
$assert_contains('Le select custom utilise le premier selected valide', 'value="paris" selected', $single_custom_select);
$assert_not_contains('Le select custom simple ne garde qu’une sélection', 'value="lyon" selected', $single_custom_select);
$assert_same('Le select custom simple rend une seule sélection', 1, substr_count($single_custom_select, ' selected'));
$assert_contains('L’interface personnalisée reste cachée avant hydratation', 'data-select-ui hidden', $single_custom_select);
$assert_not_contains('Le fallback natif reste accessible avant hydratation', 'data-select-native aria-hidden', $single_custom_select);
$assert_same('Le select custom retire l’ancien alias name des options', '', $render('select-custom', [
    'name' => 'legacy',
    'options' => [['value' => 'legacy', 'name' => 'Ancien libellé']],
]));
$assert_same('Le select custom refuse les arguments positionnels', '', $render('select-custom', [
    ['paris' => 'Paris'],
    'city',
    'Ville',
]));

$autocomplete = $render('autocomplete', [
    'name' => 'country',
    'label' => 'Pays',
    'value' => 'fr',
    'required' => 'false',
    'options' => [
        ['value' => 'fr', 'label' => 'France', 'alt' => 'République française'],
        ['value' => 'de', 'label' => 'Allemagne', 'disabled' => true],
    ],
]);
$assert_contains('L’autocomplete charge son module', 'data-module="components/autocomplete"', $autocomplete);
$assert_contains('L’autocomplete conserve un select soumis', 'name="country"', $autocomplete);
$assert_contains('L’autocomplete relie le champ à sa liste', 'aria-controls="autocomplete-', $autocomplete);
$assert_contains('L’autocomplete neutralise les suggestions enregistrées du navigateur', 'autocomplete="new-password"', $autocomplete);
$assert_contains('L’autocomplete conserve un fallback natif visible avant le JS', 'class="autocomplete-select"', $autocomplete);
$assert_contains('L’autocomplete sélectionne la valeur initiale', 'value="fr" data-alt="République française" selected', $autocomplete);
$assert_contains('L’autocomplete synchronise le libellé initial', 'value="France"', $autocomplete);
$assert_contains('L’autocomplete conserve les options désactivées dans le fallback', 'value="de" data-alt="" disabled', $autocomplete);
$assert_not_contains('L’autocomplete normalise la chaîne false', ' required', $autocomplete);
$assert_not_contains('Le fallback autocomplete n’est pas masqué avant le JS', 'aria-hidden="true"', $autocomplete);

$selected_autocomplete = $render('autocomplete', [
    'name' => 'city',
    'required' => true,
    'options' => [
        ['value' => 'paris', 'label' => 'Paris'],
        ['value' => 'lyon', 'label' => 'Lyon', 'selected' => true],
    ],
]);
$assert_contains('L’autocomplete honore selected sans value explicite', 'value="lyon" data-alt="" selected', $selected_autocomplete);
$assert_contains('L’autocomplete applique required au fallback', 'name="city" required', $selected_autocomplete);
$assert_same('L’autocomplete retire l’ancien alias name des options', '', $render('autocomplete', [
    'name' => 'legacy',
    'options' => [['value' => 'legacy', 'name' => 'Ancien libellé']],
]));
$assert_same('L’autocomplete refuse les arguments positionnels', '', $render('autocomplete', [
    [['value' => 'paris', 'label' => 'Paris']],
    'city',
    'Ville',
]));

$tabs = $render('tab', [
    'title' => 'Rubriques',
    'items' => [
        ['title' => 'Premier', 'content' => '<p>Contenu <strong>riche</strong></p>'],
        ['title' => 'Second', 'content' => '<script>alert(1)</script><p>Suite</p>'],
    ],
]);
$assert_contains('Les tabs chargent leur module', 'data-module="components/tab"', $tabs);
$assert_contains('Les tabs relient boutons et panneaux', 'role="tabpanel" aria-labelledby="tabs-', $tabs);
$assert_contains('La tablist utilise son titre visible', 'role="tablist" aria-orientation="horizontal" aria-labelledby="tabs-', $tabs);
$assert_contains('Le premier onglet est dans l’ordre de tabulation', 'aria-selected="true" tabindex="0"', $tabs);
$assert_contains('Les panneaux peuvent recevoir le focus', 'role="tabpanel" aria-labelledby="tabs-', $tabs);
$assert_not_contains('Les tabs filtrent les scripts', '<script>', $tabs);

$robust_tabs = $render('tab', [
    'id' => 'product-tabs',
    'aria_label' => 'Détails du produit',
    'orientation' => 'vertical',
    'attributes' => ['id' => 'duplicate', 'data-track' => 'tabs'],
    'items' => [
        ['title' => 'Indisponible', 'content' => 'Fermé', 'selected' => true, 'disabled' => 'true'],
        [
            'title' => 'Présentation',
            'content' => '<p>Présentation</p>',
            'disabled' => 'false',
            'tab_attributes' => ['tabindex' => 9, 'data-tab-track' => 'presentation'],
            'panel_attributes' => ['hidden' => false, 'data-panel-track' => 'presentation'],
        ],
        ['title' => 'Contact', 'content' => '<p>Contact</p>', 'selected' => 'true'],
        ['title' => 'Ignoré', 'content' => [], 'selected' => 'false'],
    ],
]);
$assert_contains('Les tabs acceptent un identifiant explicite', 'id="product-tabs"', $robust_tabs);
$assert_contains('La tablist sans titre utilise aria_label', 'aria-orientation="vertical" aria-label="Détails du produit"', $robust_tabs);
$assert_contains('Un onglet désactivé ne peut pas être sélectionné', 'aria-controls="product-tabs-panel-0" aria-selected="false" tabindex="-1" disabled', $robust_tabs);
$assert_contains('Selected choisit le premier onglet actif déclaré', 'aria-controls="product-tabs-panel-2" aria-selected="true" tabindex="0"', $robust_tabs);
$assert_not_contains('Un seul onglet reste sélectionné', 'aria-controls="product-tabs-panel-1" aria-selected="true"', $robust_tabs);
$assert_same('Les tabs ne rendent qu’une sélection active', 1, substr_count($robust_tabs, 'aria-selected="true"'));
$assert_contains('Les tabs conservent les attributs libres des boutons', 'data-tab-track="presentation"', $robust_tabs);
$assert_contains('Les tabs conservent les attributs libres des panneaux', 'data-panel-track="presentation"', $robust_tabs);
$assert_contains('Les tabs conservent les attributs libres du conteneur', 'data-track="tabs"', $robust_tabs);
$assert_not_contains('Les tabs protègent leur identifiant racine', 'id="duplicate"', $robust_tabs);
$assert_not_contains('Les tabs protègent tabindex', 'tabindex="9"', $robust_tabs);
$assert_contains('Le panneau désactivé reste masqué', 'id="product-tabs-panel-0" class="tabs-panel" role="tabpanel" aria-labelledby="product-tabs-tab-0" tabindex="0" hidden', $robust_tabs);
$assert_not_contains('Le panneau actif ne reçoit pas hidden', 'id="product-tabs-panel-2" class="tabs-panel" role="tabpanel" aria-labelledby="product-tabs-tab-2" tabindex="0" hidden', $robust_tabs);

$all_disabled_tabs = $render('tab', [
    'items' => [
        ['title' => 'Premier', 'disabled' => true],
        ['title' => 'Second', 'disabled' => 'true'],
    ],
]);
$assert_same('Un groupe sans aucun onglet actif ne produit aucun HTML', '', $all_disabled_tabs);
$assert_same('Les tabs retirent l’ancien alias label', '', $render('tab', [
    'items' => [['label' => 'Ancien titre', 'content' => 'Contenu']],
]));
$legacy_tab_content = $render('tab', [
    'items' => [['title' => 'Titre', 'text' => 'Ancien contenu']],
]);
$assert_not_contains('Les tabs retirent l’ancien alias text', 'Ancien contenu', $legacy_tab_content);
$assert_same('Les tabs refusent les arguments positionnels', '', $render('tab', [
    [['title' => 'Ancien', 'content' => 'Contenu']],
    'Titre',
]));

$table = $render('table', [
    'caption' => 'Résultats',
    'headers' => ['Nom', 'Valeur'],
    'rows' => [['Alpha', '<strong>10</strong><script>alert(1)</script>']],
]);
$assert_contains('Le tableau expose ses en-têtes', '<th scope="col">Nom</th>', $table);
$assert_contains('Le tableau conserve le HTML éditorial des cellules', '<strong>10</strong>', $table);
$assert_contains('Le tableau possède une caption reliée à sa région', 'role="region" aria-labelledby="table-', $table);
$assert_contains('La zone du tableau est accessible au clavier', 'class="table__scroll" tabindex="0"', $table);
$assert_not_contains('Le tableau filtre les scripts', '<script>', $table);

$structured_table = $render('table', [
    'id' => 'results-table',
    'aria_label' => 'Résultats accessibles',
    'row_headers' => 'true',
    'classes' => ['table-results'],
    'table_classes' => 'table-compact',
    'attributes' => ['id' => 'duplicate', 'data-component' => 'results'],
    'scroll_attributes' => ['tabindex' => 9, 'role' => 'presentation', 'data-scroll' => 'results'],
    'table_attributes' => ['id' => 'duplicate-table', 'data-table' => 'results'],
    'caption_attributes' => ['id' => 'duplicate-caption', 'data-caption' => 'results'],
    'headers' => [
        'name' => [
            'label' => 'Nom',
            'attributes' => ['scope' => 'row', 'data-column' => 'name'],
        ],
        'ignored' => '',
        'value' => [
            'label' => '<strong>Valeur</strong>',
            'colspan' => 2,
            'classes' => 'is-value',
        ],
    ],
    'rows' => [
        [
            'value' => ['content' => '10', 'attributes' => ['data-cell' => 'value']],
            'ignored' => 'Ne doit pas apparaître',
            'name' => '<em>Alpha</em>',
            'extra' => 'Cellule excédentaire',
        ],
        [
            'cells' => ['name' => 'Beta'],
            'attributes' => ['data-row' => 'beta'],
        ],
        ['name' => '', 'value' => ''],
        'invalid',
    ],
]);
$assert_contains('La table structurée utilise son identifiant', 'id="results-table"', $structured_table);
$assert_contains('Une caption accessible est toujours rendue', 'table__caption table__caption--visually-hidden', $structured_table);
$assert_contains('La caption accessible reprend aria_label', '>Résultats accessibles</caption>', $structured_table);
$assert_contains('Les lignes associatives suivent les clés des colonnes', '<th scope="row"><em>Alpha</em></th>', $structured_table);
$assert_contains('Les colonnes structurées acceptent colspan', 'scope="col" colspan="2" class="is-value"', $structured_table);
$assert_contains('Le HTML éditorial des en-têtes est conservé', '<strong>Valeur</strong>', $structured_table);
$assert_contains('Les attributs libres de colonne sont conservés', 'data-column="name"', $structured_table);
$assert_contains('Les attributs libres de cellule sont conservés', 'data-cell="value"', $structured_table);
$assert_contains('Les attributs libres de ligne sont conservés', '<tr data-row="beta">', $structured_table);
$assert_contains('Une cellule manquante conserve la grille', '<td></td>', $structured_table);
$assert_contains('Les attributs du conteneur sont conservés', 'data-component="results"', $structured_table);
$assert_contains('Les attributs de scroll sont conservés', 'data-scroll="results"', $structured_table);
$assert_contains('Les attributs de table sont conservés', 'data-table="results"', $structured_table);
$assert_contains('Les attributs de caption sont conservés', 'data-caption="results"', $structured_table);
$assert_contains('Les classes de table sont conservées', 'class="table__table table-compact"', $structured_table);
$assert_not_contains('Une colonne vide est ignorée avec sa cellule', 'Ne doit pas apparaître', $structured_table);
$assert_not_contains('Une cellule excédentaire est ignorée', 'Cellule excédentaire', $structured_table);
$assert_not_contains('Une ligne entièrement vide est ignorée', '<tr><th scope="row"></th><td></td></tr>', $structured_table);
$assert_not_contains('L’identifiant racine est protégé', 'id="duplicate"', $structured_table);
$assert_not_contains('L’identifiant de table est protégé', 'id="duplicate-table"', $structured_table);
$assert_not_contains('L’identifiant de caption est protégé', 'id="duplicate-caption"', $structured_table);
$assert_not_contains('Le tabindex de scroll est protégé', 'tabindex="9"', $structured_table);
$assert_not_contains('Le rôle de scroll est protégé', 'role="presentation"', $structured_table);

$non_scrollable_table = $render('table', [
    'headers' => ['Nom'],
    'rows' => [['Alpha']],
    'scrollable' => 'false',
]);
$assert_not_contains('Une table non scrollable ne crée pas de région', 'role="region"', $non_scrollable_table);
$assert_not_contains('Une table non scrollable ne crée pas de tab stop', 'tabindex="0"', $non_scrollable_table);

$empty_table = $render('table', [
    'headers' => ['', []],
    'rows' => [['Alpha']],
]);
$assert_same('Une table sans en-tête valide ne produit aucun HTML', '', $empty_table);

$empty_rows_table = $render('table', [
    'headers' => ['Nom'],
    'rows' => [[], [''], 'invalid'],
]);
$assert_same('Une table sans ligne utile ne produit aucun HTML', '', $empty_rows_table);
$assert_same('La table retire les alias de colonne', '', $render('table', [
    'headers' => [['title' => 'Ancienne colonne']],
    'rows' => [['Alpha']],
]));
$assert_same('La table retire les alias de cellule', '', $render('table', [
    'headers' => ['Nom'],
    'rows' => [[['value' => 'Ancienne cellule']]],
]));
$assert_same('La table refuse les arguments positionnels', '', $render('table', [
    ['Nom'],
    [['Alpha']],
]));

$marquee_item = new class implements Stringable {
    public function __toString(): string
    {
        return '<em>Innovation</em>';
    }
};
$marquee = $render('marquee', [
    'items' => [
        '<strong>Stratégie</strong>',
        ['content' => '<a href="/design/">Design</a>'],
        $marquee_item,
        '<script>alert(1)</script>',
    ],
    'speed' => 900,
    'direction' => 'right',
    'scroll_acceleration' => false,
    'follow_scroll_direction' => false,
    'pause_on_hover' => false,
    'pause_on_focus' => '0',
    'attributes' => [
        'class' => 'duplicate',
        'data-module' => 'duplicate',
        'data-marquee-scroll-acceleration' => 'true',
        'data-marquee-follow-scroll-direction' => 'true',
        'data-track' => 'expertises',
    ],
]);
$assert_contains('Le marquee charge son module', 'data-module="components/marquee"', $marquee);
$assert_contains('Le marquee attend sa visibilité', 'data-context="@visible true"', $marquee);
$assert_contains('Le marquee limite sa vitesse maximale', 'data-marquee-speed="400"', $marquee);
$assert_contains('Le marquee accepte la direction droite', 'data-marquee-direction="right"', $marquee);
$assert_contains('Le marquee peut désactiver l’accélération au scroll', 'data-marquee-scroll-acceleration="false"', $marquee);
$assert_contains('Le marquee peut conserver son sens pendant le scroll', 'data-marquee-follow-scroll-direction="false"', $marquee);
$assert_contains('Le marquee normalise la pause au survol', 'data-marquee-pause-hover="false"', $marquee);
$assert_contains('Le marquee normalise la pause au focus', 'data-marquee-pause-focus="false"', $marquee);
$assert_contains('Le marquee conserve le HTML éditorial', '<strong>Stratégie</strong>', $marquee);
$assert_contains('Le marquee accepte les items Stringable', '<em>Innovation</em>', $marquee);
$assert_not_contains('Le marquee retire les anciens items content/text', 'Design', $marquee);
$assert_contains('Le marquee conserve les attributs libres', 'data-track="expertises"', $marquee);
$assert_not_contains('Le marquee filtre les scripts', '<script>', $marquee);
$assert_not_contains('Le marquee protège sa classe', 'class="duplicate"', $marquee);
$assert_not_contains('Le marquee protège son module', 'data-module="duplicate"', $marquee);

$empty_marquee = $render('marquee', ['items' => ['', []]]);
$assert_same('Le marquee vide ne produit aucun HTML', '', $empty_marquee);

$manual_marquee = $render('marquee', [
    'items' => ['Piloté par une strate'],
    'hydrate' => false,
]);
$assert_not_contains('Le marquee piloté par une strate ne s’hydrate pas seul', 'data-module=', $manual_marquee);
$assert_not_contains('Le marquee piloté par une strate n’attend pas sa visibilité', 'data-context=', $manual_marquee);

$card_marquee = $render('marquee', [
    'items' => [108],
    'card' => 'news',
    'hydrate' => false,
]);
$assert_contains('Le marquee rend directement ses cards', '<article class="card-news">', $card_marquee);
$assert_contains('Le marquee transmet les IDs aux cards', 'Actualité 108', $card_marquee);

$picture_marquee = $render('marquee', [
    'items' => [[
        'image' => 501,
        'size' => 'large',
        'fit' => 'cover',
        'classes' => 'marquee-image',
    ]],
    'item_component' => 'picture',
    'hydrate' => false,
]);
$assert_contains('Le marquee rend directement ses pictures', '<picture class="picture fit-cover marquee-image">', $picture_marquee);
$assert_contains('Le marquee transmet les images', '<img src="501"', $picture_marquee);

$assert_same('Le marquee refuse une liste directe', '', $render('marquee', [['Ancienne forme']]));

$field = $render('field', [
    'type' => 'email',
    'name' => 'email',
    'label' => 'Adresse e-mail',
    'required' => true,
    'attributes' => ['class' => 'duplicate', 'data-field' => 'email'],
]);
$assert_contains('Le champ de formulaire rend le bon type', 'type="email"', $field);
$assert_contains('Le champ de formulaire conserve ses attributs libres', 'data-field="email"', $field);
$assert_not_contains('Le champ de formulaire protège sa classe', 'class="duplicate"', $field);
$field_with_messages = $render('field', [
    'type' => 'password',
    'name' => 'password',
    'label' => 'Mot de passe',
    'hint' => 'Huit caractères minimum.',
    'pattern' => '.{8,}',
    'pattern_mismatch_msg' => '<strong>Trop court</strong>',
]);
$assert_contains('Le hint possède un identifiant stable', 'id="password-hint"', $field_with_messages);
$assert_contains('Le hint et l’erreur sont décrits par le contrôle', 'aria-describedby="password-error password-hint"', $field_with_messages);
$assert_contains('Le message de pattern est échappé dans un attribut', 'data-patternmismatch="&lt;strong&gt;Trop court&lt;/strong&gt;"', $field_with_messages);
$assert_contains('La zone d’erreur annonce ses changements', 'class="invalid-msg" aria-live="polite" hidden', $field_with_messages);
$field_group = $render('field', [
    'type' => 'radios',
    'name' => 'contact',
    'label' => 'Contact',
    'required' => true,
    'options' => [
        ['value' => 'email', 'label' => 'E-mail', 'checked' => 'false'],
        ['value' => 'phone', 'label' => 'Téléphone', 'disabled' => 'false'],
    ],
]);
$assert_contains('Le groupe requis expose son état accessible', 'data-required aria-required="true"', $field_group);
$assert_same('Un seul radio porte la contrainte HTML native', 1, substr_count($field_group, ' required'));
$assert_not_contains('La chaîne false ne coche pas une option', ' checked', $field_group);
$assert_not_contains('La chaîne false ne désactive pas une option', ' disabled', $field_group);
$assert_same('Field refuse les arguments positionnels', '', $render('field', [
    'email',
    'Adresse e-mail',
    'email',
]));

$filters = $render('filters', [
    'action' => '/projects/',
    'items' => [
        [
            'type' => 'search',
            'name' => 'filter_search',
            'label' => 'Rechercher',
            'value' => '<identité>',
        ],
        [
            'type' => 'taxonomy',
            'name' => 'project_type',
            'label' => 'Types de projets',
            'value' => ['website'],
            'options' => [
                ['value' => 'website', 'label' => 'Website'],
                ['value' => 'branding', 'label' => 'Branding'],
            ],
        ],
        [
            'type' => 'sort',
            'name' => 'filter_sort',
            'label' => 'Trier par',
            'value' => 'title_asc',
            'options' => [
                ['value' => 'date_desc', 'label' => 'Plus récents'],
                ['value' => 'title_asc', 'label' => 'Titre A–Z'],
            ],
        ],
        [
            'type' => 'acf',
            'control' => 'search',
            'name' => 'hero_title',
            'label' => 'Titre du hero',
            'value' => 'Website',
        ],
        [
            'type' => 'featured',
            'name' => 'featured',
            'label' => 'Mis en avant',
            'value' => true,
        ],
        [
            'type' => 'date_range',
            'name' => 'period',
            'label' => 'Période',
            'value' => ['from' => '2026-01-01', 'to' => '2026-12-31'],
        ],
        [
            'type' => 'number_range',
            'name' => 'budget',
            'label' => 'Budget',
            'value' => ['min' => 100, 'max' => 500],
            'min' => 0,
        ],
        [
            'type' => 'hidden',
            'name' => 'context',
            'value' => '<project>',
        ],
        ['type' => 'text', 'name' => 'invalid name', 'label' => 'Invalide'],
    ],
    'submit_label' => 'Appliquer',
    'reset_label' => 'Effacer',
    'reset_url' => '/projects/',
    'show_reset' => true,
    'result_count' => 12,
    'result_count_label' => 'projets',
    'aria_label' => 'Filtrer les projets',
    'attributes' => ['method' => 'post', 'class' => 'duplicate', 'data-track' => 'filters'],
]);
$assert_contains('Filters rend un formulaire GET accessible', '<form class="filters" action="/projects/" method="get" aria-label="Filtrer les projets" data-track="filters">', $filters);
$assert_contains('Filters rend une recherche native', 'type="search"', $filters);
$assert_contains('Filters conserve le nom de la recherche', 'name="filter_search"', $filters);
$assert_contains('Filters échappe la recherche active', 'value="&lt;identité&gt;"', $filters);
$assert_contains('Filters coche les valeurs de taxonomie actives', 'name="project_type[]" value="website" checked', $filters);
$assert_contains('Filters rend le tri sélectionné', 'value="title_asc" selected', $filters);
$assert_contains('Filters rend un champ ACF avec le contrôle demandé', 'name="hero_title"', $filters);
$assert_contains('Filters conserve la valeur du champ ACF', 'value="Website"', $filters);
$assert_contains('Filters rend les booléens sous forme de checkbox', 'type="checkbox" name="featured" value="1" checked', $filters);
$assert_contains('Filters rend le début de période', 'name="period[from]"', $filters);
$assert_contains('Filters rend la fin de période', 'name="period[to]"', $filters);
$assert_contains('Filters rend la borne numérique minimale', 'name="budget[min]"', $filters);
$assert_contains('Filters rend la borne numérique maximale', 'name="budget[max]"', $filters);
$assert_contains('Filters échappe les champs cachés', 'type="hidden" name="context" value="&lt;project&gt;"', $filters);
$assert_contains('Filters affiche le nombre de résultats', '<strong>12</strong>', $filters);
$assert_contains('Filters rend son bouton de validation', '<span>Appliquer</span>', $filters);
$assert_contains('Filters rend son lien de réinitialisation', '<span>Effacer</span>', $filters);
$assert_not_contains('Filters ignore les noms de champs invalides', 'Invalide', $filters);
$assert_not_contains('Filters protège sa méthode', 'method="post"', $filters);
$assert_not_contains('Filters protège sa classe', 'class="duplicate"', $filters);
$ajax_filters = $render('filters', [
    'items' => [['type' => 'sort', 'name' => 'filter_sort']],
    'ajax' => true,
    'ajax_group' => 'project-filters',
    'ajax_target' => 'project-results',
    'ajax_submit_on_change' => true,
    'attributes' => ['data-module' => 'refuse', 'data-filters-target' => 'refuse'],
]);
$assert_contains(
    'Filters expose son groupe et sa cible AJAX',
    'data-module="components/filters" data-filters-group="project-filters" data-filters-target="project-results" data-filters-submit-on-change="true"',
    $ajax_filters
);
$assert_not_contains('Filters protège ses attributs AJAX', 'data-module="refuse"', $ajax_filters);
$assert_not_contains('Filters ignore AJAX sans cible valide', 'data-module="components/filters"', $render('filters', [
    'items' => [['type' => 'search', 'name' => 'filter_search']],
    'ajax' => true,
    'ajax_group' => 'project-filters',
]));
$pretty_filters = $render('filters', [
    'items' => [['type' => 'sort', 'name' => 'filter_sort']],
    'ajax' => true,
    'ajax_group' => 'project-filters',
    'ajax_target' => 'project-results',
    'pretty_urls' => true,
    'pretty_base_url' => 'https://example.test/projects/filters/',
    'pretty_params' => ['project_type', 'filter_sort'],
]);
$assert_contains('Filters expose la base des URL lisibles', 'data-filters-pretty="true" data-filters-pretty-base="https://example.test/projects/filters/"', $pretty_filters);
$assert_contains('Filters expose les paramètres placés dans le chemin', 'data-filters-pretty-params="project_type,filter_sort"', $pretty_filters);
$assert_same(
    'Le chemin lisible des filtres est décodé en paramètres',
    [
        'project_type' => ['application', 'website'],
        'filter_sort' => 'date_desc',
        'paged' => '2',
    ],
    theme_parse_pretty_filters_path(
        'project_type/application,website/filter_sort/date_desc/page/2',
        'paged'
    )
);
$assert_same('Theme_Listing expose la recherche', true, method_exists(Theme_Listing::class, 'search'));
$assert_same('Theme_Listing expose les filtres', true, method_exists(Theme_Listing::class, 'filters'));
$assert_same('Theme_Listing expose les facettes', true, method_exists(Theme_Listing::class, 'facets'));
$assert_same('Theme_Listing expose le tri', true, method_exists(Theme_Listing::class, 'sort'));
$assert_same('Theme_Listing expose le message des résultats', true, method_exists(Theme_Listing::class, 'results_message'));
$assert_same('Theme_Listing expose le contexte archive', true, method_exists(Theme_Listing::class, 'is_archive'));
$assert_same('Theme_Listing expose le type de contenu', true, method_exists(Theme_Listing::class, 'post_type'));
$assert_same('Theme_Listing expose les résultats sans les rendre', true, method_exists(Theme_Listing::class, 'items'));
$assert_same('Theme_Listing expose les résultats groupés', true, method_exists(Theme_Listing::class, 'grouped_items'));
$assert_same('Theme_Listing expose le nombre total de résultats', true, method_exists(Theme_Listing::class, 'result_count'));
$filters_facade = new Theme_Listing([
    'post_type' => 'missing',
    'taxonomies' => false,
    'request' => [],
    'ajax' => true,
    'ajax_group' => 'contract-filters',
    'ajax_target' => 'contract-results',
]);
$rendering_component_fixture = true;
ob_start();
$filters_facade->search(['classes' => ['contract-search', 'contract-search-wide']]);
$filters_search = trim((string) ob_get_clean());
ob_start();
$filters_facade->sort(['classes' => 'contract-sort']);
$filters_sort = trim((string) ob_get_clean());
$filters_facets_facade = new Theme_Listing([
    'post_type' => 'missing',
    'taxonomies' => false,
    'request' => [],
    'groups' => [
        'facets' => ['controls' => ['search']],
    ],
]);
ob_start();
$filters_facets_facade->filters(['classes' => 'contract-facets']);
$filters_facets = trim((string) ob_get_clean());
$rendering_component_fixture = false;
$assert_contains('Theme_Listing rend directement la recherche', 'class="filters filters-search ', $filters_search);
$assert_contains('Theme_Listing ajoute les classes de la recherche', 'class="filters filters-search contract-search contract-search-wide"', $filters_search);
$assert_contains('Theme_Listing transmet le groupe AJAX', 'data-filters-group="contract-filters"', $filters_search);
$assert_contains('Theme_Listing ajoute les classes des facettes', 'class="filters filters-facets contract-facets"', $filters_facets);
$assert_contains('Theme_Listing rend directement le tri', 'class="filters filters-sort ', $filters_sort);
$assert_contains('Theme_Listing ajoute les classes du tri', 'class="filters filters-sort contract-sort"', $filters_sort);
$assert_contains('Theme_Listing active le tri au changement', 'data-filters-submit-on-change="true"', $filters_sort);
$assert_same('Theme_Listing expose la cible', 'contract-results', $filters_facade->target());
$results_target_facade = new Theme_Listing([
    'post_type' => 'missing',
    'search' => false,
    'taxonomies' => false,
    'sort' => false,
    'ajax' => false,
    'results_target' => 'hub-results',
]);
$assert_same(
    'Theme_Listing expose une cible de résultats sans activer AJAX',
    'hub-results',
    $results_target_facade->target()
);
$assert_same('Theme_Listing normalise une liste vide', [], $filters_facade->items());
$assert_same('Theme_Listing ne rend pas les résultats', false, $filters_facade->has_results());
ob_start();
$filters_facade->results_message();
$filters_message_without_search = trim((string) ob_get_clean());
$assert_same('Theme_Listing masque le message sans recherche', '', $filters_message_without_search);
$filters_message_facade = new Theme_Listing([
    'post_type' => 'missing',
    'taxonomies' => false,
    'request' => ['filter_search' => 'alpha'],
]);
ob_start();
$filters_message_facade->results_message(['classes' => 'contract-results-message']);
$filters_empty_message = trim((string) ob_get_clean());
$assert_contains('Theme_Listing affiche le message sans résultat', 'Aucun résultat pour votre recherche.', $filters_empty_message);
$assert_contains('Theme_Listing ajoute les classes du message', 'class="filters-results-message contract-results-message"', $filters_empty_message);
$filters_message_context = $filters_message_facade->context();
$filters_message_context['result_count'] = 2;
$filters_context_property = new ReflectionProperty(Theme_Listing::class, 'context');
$filters_context_property->setValue($filters_message_facade, $filters_message_context);
ob_start();
$filters_message_facade->results_message([
    'messages' => [
        'plural' => '{count} matching <projects>.',
    ],
]);
$filters_custom_message = trim((string) ob_get_clean());
$assert_contains('Theme_Listing personnalise le message pluriel', '<strong>2</strong> matching &lt;projects&gt;.', $filters_custom_message);
$assert_same('Filters ne rend rien sans contrôle valide', '', $render('filters', [
    'items' => [['type' => 'search', 'name' => 'invalid name']],
]));
$assert_same('Filters refuse les arguments positionnels', '', $render('filters', [
    [['type' => 'search', 'name' => 'filter_search']],
]));

$newsletter = $render('newsletter', [
    'title' => 'Newsletter',
    'text' => '<p>Recevez nos actualités.</p>',
    'action' => 'https://newsletter.example.test/subscribe',
    'email_id' => 'newsletter-test-email',
    'email_name' => 'subscriber_email',
    'email_label' => 'Adresse e-mail',
    'button_label' => 'S’inscrire',
    'mandatory_msg' => 'Adresse requise.',
    'type_mismatch_msg' => 'Adresse invalide.',
    'classes' => 'newsletter-footer',
    'attributes' => ['class' => 'duplicate', 'data-list' => 'main'],
]);
$assert_contains('Newsletter rend son conteneur', '<div class="newsletter newsletter-footer" data-list="main">', $newsletter);
$assert_contains('Newsletter rend son titre', '<h2 class="title title-3">Newsletter</h2>', $newsletter);
$assert_contains('Newsletter conserve son texte éditorial', '<p>Recevez nos actualités.</p>', $newsletter);
$assert_contains('Newsletter transmet son action sûre', 'action="https://newsletter.example.test/subscribe"', $newsletter);
$assert_contains('Newsletter hydrate la validation du champ', 'data-module="components/field"', $newsletter);
$assert_contains('Newsletter transmet le nom du champ', 'name="subscriber_email"', $newsletter);
$assert_contains('Newsletter rend son bouton submit', '<button type="submit" class="btn"><span>S’inscrire</span></button>', $newsletter);
$assert_not_contains('Newsletter protège sa classe structurelle', 'class="duplicate"', $newsletter);

$unsafe_newsletter = $render('newsletter', [
    'action' => 'javascript:alert(1)',
    'email_label' => 'Adresse e-mail',
    'button_label' => 'S’inscrire',
]);
$assert_not_contains('Newsletter ignore une action dangereuse', 'action=', $unsafe_newsletter);
$assert_same('Newsletter exige le libellé du champ', '', $render('newsletter', [
    'button_label' => 'S’inscrire',
]));
$assert_same('Newsletter refuse les arguments positionnels', '', $render('newsletter', [
    'Adresse e-mail',
    'S’inscrire',
]));

$assert_same('La fonction globale form est retirée', false, function_exists('form'));

$rendering_component_fixture = true;
ob_start();
component('form', ['type' => 'email', 'name' => 'legacy-email']);
$legacy_form = trim((string) ob_get_clean());
$rendering_component_fixture = false;
$assert_same('L’ancien composant form est retiré', '', $legacy_form);

$assert_same(
    'La strate media ne rend rien sans source',
    '',
    $render_strate('media', ['media_type' => 'image', 'image' => null])
);

$media_image_strate = $render_strate('media', [
    'media_type' => 'image',
    'image' => 123,
]);
$assert_contains('La strate media rend sa section avec une image', '<section class="strate strate-media">', $media_image_strate);
$assert_contains('La strate media délègue l’image au composant picture', '<picture', $media_image_strate);

$media_video_strate = $render_strate('media', [
    'media_type' => 'video',
    'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'video_title' => 'Présentation vidéo',
]);
$assert_contains('La strate media délègue la vidéo au composant video', 'data-type="youtube"', $media_video_strate);
$assert_contains('La strate media transmet le titre accessible', 'title="Présentation vidéo"', $media_video_strate);

$legacy_media_strate = $render_strate('media', [
    'is_image' => false,
    'video' => 'https://vimeo.com/123456',
    'video-title' => 'Ancienne vidéo',
]);
$assert_contains('La strate media conserve le sélecteur historique', 'data-type="vimeo"', $legacy_media_strate);
$assert_contains('La strate media conserve l’ancien titre', 'title="Ancienne vidéo"', $legacy_media_strate);

$image_strate = $render_strate('image', ['image' => 123]);
$assert_contains('La strate image utilise sa classe dédiée', '<section class="strate strate-image">', $image_strate);
$assert_contains('La strate image utilise picture', '<picture', $image_strate);
$assert_same('La strate image vide ne produit aucun HTML', '', $render_strate('image', ['image' => null]));

$video_strate = $render_strate('video', [
    'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'video_title' => 'Présentation vidéo',
]);
$assert_contains('La strate video utilise sa classe dédiée', '<section class="strate strate-video">', $video_strate);
$assert_contains('La strate video utilise le composant', 'data-type="youtube"', $video_strate);
$assert_contains('La strate video transmet le titre', 'title="Présentation vidéo"', $video_strate);
$assert_same('La strate video vide ne produit aucun HTML', '', $render_strate('video', ['video' => '']));

$quote_strate = $render_strate('quote', [
    'text' => '<p>Citation minimale.</p>',
]);
$assert_contains('La strate quote accepte son payload minimal', '<section class="strate strate-quote">', $quote_strate);
$assert_contains('La strate quote transmet son texte au composant', '<blockquote class="quote-content"><p>Citation minimale.</p></blockquote>', $quote_strate);
$assert_same('La strate quote ne rend rien sans citation', '', $render_strate('quote', ['text' => '   ']));

$GLOBALS['lsd_thumb_calls'] = [];
$quote_strate_complete = $render_strate('quote', [
    'text' => '<p>Citation complète.</p>',
    'image' => 456,
    'author' => 'Jane Doe',
    'source' => 'Publication',
    'url' => '/publication/',
    'options' => [
        'container' => 'sm',
        'margin' => ['bottom' => 'lg'],
    ],
]);
$assert_contains('La strate quote transmet son image', '<img class="quote-image" src="456"', $quote_strate_complete);
$assert_same('La strate quote utilise sa taille d’image', [456, '40_40'], $GLOBALS['lsd_thumb_calls'][0] ?? null);
$assert_contains('La strate quote transmet son auteur', '<span class="quote-author">Jane Doe</span>', $quote_strate_complete);
$assert_contains('La strate quote transmet sa source', '<a href="/publication/">Publication</a>', $quote_strate_complete);
$assert_contains('La strate quote applique les options communes', 'class="strate strate-quote ctr-sm mb-lg"', $quote_strate_complete);

$keynumbers_strate = $render_strate('keynumbers', [
    'title' => 'Nos chiffres clés',
    'items' => [
        ['value' => '25', 'prefix' => '+', 'suffix' => 'ans', 'title' => 'd’expérience'],
        ['value' => '', 'title' => 'Item invalide'],
        ['value' => '98', 'suffix' => '%', 'title' => 'de satisfaction'],
    ],
    'options' => [
        'container' => 'sm',
    ],
]);
$assert_contains('La strate keynumbers rend son introduction', '<header class="component-header strate-header">', $keynumbers_strate);
$assert_same('La strate keynumbers ignore les valeurs vides', 2, substr_count($keynumbers_strate, 'class="card-keynumber"'));
$assert_contains('La strate keynumbers utilise sa card dédiée', '<span class="keynumber-value">25</span>', $keynumbers_strate);
$assert_contains('La strate keynumbers transmet le préfixe', '<span class="keynumber-prefix">+</span>', $keynumbers_strate);
$assert_contains('La strate keynumbers applique les options communes', 'class="strate strate-keynumbers ctr-sm mb-0"', $keynumbers_strate);
$assert_same('La strate keynumbers vide ne produit aucun HTML', '', $render_strate('keynumbers', [
    'items' => [['value' => '']],
]));

$manual_cards_slider = $render_strate('slider-cards', [
    'title' => 'Cards éditoriales',
    'items' => [
        ['title' => 'Card libre 1', 'text' => '<p>Description libre.</p>'],
        ['title' => 'Card libre 2', 'link' => ['url' => '/libre/', 'title' => 'Lire']],
        ['title' => '', 'text' => '', 'image' => null],
    ],
]);
$assert_same('Le slider manuel rend deux cards sans post', 2, substr_count($manual_cards_slider, 'class="card-slide"'));
$assert_contains('Le slider manuel transmet le texte', 'Description libre.', $manual_cards_slider);
$assert_contains('Le slider manuel conserve le lien éditorial', 'href="/libre/"', $manual_cards_slider);
$assert_contains('Le slider manuel hydrate le composant', 'data-module="components/slider"', $manual_cards_slider);
$assert_same('Le slider manuel vide ne produit aucun HTML', '', $render_strate('slider-cards', ['items' => []]));

$image_only_slider = $render_strate('slider-images', [
    'images' => [['image' => 401], ['image' => null]],
]);
$assert_same('Le slider images ignore les lignes vides', 1, substr_count($image_only_slider, '<li'));
$assert_contains('Le slider images utilise picture', '<picture class="picture fit-cover slider-image">', $image_only_slider);
$assert_same('Le slider images vide ne produit aucun HTML', '', $render_strate('slider-images', ['images' => []]));

$post_types_by_id = [501 => 'news', 502 => 'page', 503 => 'news'];
$marquee_cards_strate = $render_strate('marquee', [
    'title' => 'News en continu',
    'content_type' => 'cards',
    'post_type' => 'news',
    'items' => [501, 502, 503],
    'marquee_options' => [
        'speed' => 80,
        'direction' => 'right',
        'scroll_acceleration' => false,
    ],
]);
$assert_contains('La strate marquee rend son introduction', '<header class="component-header strate-header">', $marquee_cards_strate);
$assert_same('La strate marquee filtre les cards d’un autre CPT', 2, substr_count($marquee_cards_strate, 'class="card-news"'));
$assert_contains('La strate marquee transmet sa vitesse', 'data-marquee-speed="80"', $marquee_cards_strate);
$assert_contains('La strate marquee transmet sa direction', 'data-marquee-direction="right"', $marquee_cards_strate);
$assert_contains('La strate marquee possède son hydratation', 'data-module="strates/strate-marquee"', $marquee_cards_strate);
$assert_not_contains('Le composant marquee n’est pas hydraté deux fois', 'data-module="components/marquee"', $marquee_cards_strate);

$GLOBALS['lsd_thumb_calls'] = [];
$marquee_images_strate = $render_strate('marquee', [
    'content_type' => 'images',
    'images' => [
        ['image' => 601],
        ['image' => null],
    ],
]);
$assert_same('La strate marquee ignore les lignes image vides', 1, substr_count($marquee_images_strate, 'class="marquee-item"'));
$assert_contains('La strate marquee rend ses images en cover', '<picture class="picture fit-cover marquee-image">', $marquee_images_strate);
$assert_same('La strate marquee transmet sa taille d’image', [601, 'full'], $GLOBALS['lsd_thumb_calls'][0] ?? null);

$marquee_html_strate = $render_strate('marquee', [
    'content_type' => 'html',
    'items' => ['<strong>Contenu libre</strong>'],
]);
$assert_contains('La strate marquee conserve son mode HTML direct', '<strong>Contenu libre</strong>', $marquee_html_strate);
$assert_same('La strate marquee vide ne produit aucun HTML', '', $render_strate('marquee', [
    'content_type' => 'images',
    'images' => [],
]));

$post_types_by_id = [101 => 'news', 102 => 'page'];
$manual_pushes = $render_strate('pushes', [
    'title' => 'Sélection',
    'text' => '<p>Introduction.</p>',
    'link' => [
        'url' => '/actualites/',
        'title' => 'Toutes les actualités',
        'target' => '',
    ],
    'items' => [101, 102, '101', -1],
]);
$assert_contains('La strate pushes rend son introduction', '<header class="component-header strate-header">', $manual_pushes);
$assert_contains('La strate pushes rend son lien interne', 'href="/actualites/"', $manual_pushes);
$assert_contains('La strate pushes rend les News sélectionnées', 'Titre WordPress 101', $manual_pushes);
$assert_contains('La strate pushes accepte plusieurs types de contenus', 'class="card-page"', $manual_pushes);
$assert_same('La strate pushes déduplique sa sélection', 1, substr_count($manual_pushes, 'class="card-news"'));

$registered_post_types[] = 'project';
$post_types_by_id[201] = 'project';
$project_pushes = $render_strate('pushes', [
    'items' => [201],
]);
$assert_contains('La strate pushes accepte un autre CPT', 'class="card-project"', $project_pushes);
$assert_contains('La card Project reçoit le titre normalisé', 'Titre WordPress 201', $project_pushes);

$post_types_by_id[202] = 'project';
$card_fields_by_id[202] = ['title' => 'Titre Card', 'text' => '', 'date' => '', 'images' => ['desktop' => 0, 'mobile' => 702]];
$partial_push = theme_post_card_item(202);
$assert_same('Le titre Card remplace le titre WordPress', 'Titre Card', $partial_push['title'] ?? null);
$assert_same('La description vide garde l’extrait WordPress', 'Extrait 202', $partial_push['text'] ?? null);
$assert_same('La date vide garde la date WordPress', '2026-07-20', $partial_push['datetime'] ?? null);
$assert_same('L’image mobile sert de repli à la desktop vide', 702, $partial_push['image'] ?? null);
$assert_same('Le lien reprend le titre Card', 'Titre Card', $partial_push['link']['title'] ?? null);

$post_types_by_id[203] = 'page';
$card_fields_by_id[203] = ['text' => '<p>Description Card</p>', 'date' => '2026-08-03'];
$page_push = theme_post_card_item(203);
$assert_same('Une page accepte le groupe Card', '<p>Description Card</p>', $page_push['text'] ?? null);
$assert_same('Le groupe Card renseigne la date normalisée', '2026-08-03', $page_push['datetime'] ?? null);
$assert_same('Le titre WordPress reste disponible sans titre Card', 'Titre WordPress 203', $page_push['title'] ?? null);

$card_field_map_by_type['project'] = ['date' => 'project_date', 'text' => 'project_summary'];
$post_types_by_id[204] = 'project';
$business_fields_by_id[204] = ['project_date' => '20270915', 'project_summary' => 'Résumé métier'];
$mapped_card = theme_post_card_item(204);
$assert_same('Le champ métier alimente la date de la card', '2027-09-15', $mapped_card['datetime'] ?? null);
$assert_same('Le champ métier alimente la description de la card', 'Résumé métier', $mapped_card['text'] ?? null);
$post_types_by_id[205] = 'project';
$business_fields_by_id[205] = ['project_date' => '2027-09-15'];
$card_fields_by_id[205] = ['date' => '2027-10-01'];
$override_card = theme_post_card_item(205);
$assert_same('La variante Card prime sur le champ métier', '2027-10-01', $override_card['datetime'] ?? null);

$get_posts_calls = [];
$get_posts_result = [(object) ['ID' => 1201], (object) ['ID' => 1202]];
$post_types_by_id[1201] = 'news';
$post_types_by_id[1202] = 'news';
$meta_cache_calls = [];
$post_cache_calls = [];
$automatic_pushes = $render_strate('pushes-auto', [
    'post_type' => 'news',
    'limit' => 99,
]);
$assert_same('La remontée automatique utilise le CPT demandé', 'news', $get_posts_calls[0]['post_type'] ?? null);
$assert_same('La remontée automatique limite la quantité', 12, $get_posts_calls[0]['posts_per_page'] ?? null);
$assert_same('La remontée automatique conserve les filtres multilingues', false, $get_posts_calls[0]['suppress_filters'] ?? null);
$assert_same('La remontée automatique charge les objets posts', false, array_key_exists('fields', $get_posts_calls[0] ?? []));
$assert_same('La remontée automatique groupe le cache des posts', [[1201, 1202], false, false], $post_cache_calls[0] ?? null);
$assert_same('La remontée automatique précharge les métadonnées', ['post', [1201, 1202]], $meta_cache_calls[0] ?? null);
$assert_same('La remontée automatique rend les résultats', 2, substr_count($automatic_pushes, 'class="card-news"'));
$assert_contains('La remontée automatique utilise le titre WordPress par défaut', 'Titre WordPress 1201', $automatic_pushes);
$assert_contains('La remontée automatique ajoute le permalien de la News', 'href="https://example.test/news/1201/"', $automatic_pushes);

$get_posts_calls = [];
$slider_pushes = $render_strate('pushes-slider', [
    'title' => 'À découvrir',
    'post_type' => 'news',
    'limit' => 6,
    'slider_options' => ['navigation' => true, 'pagination' => true, 'timeline' => false],
]);
$assert_same('Le slider automatique interroge le CPT demandé', 'news', $get_posts_calls[0]['post_type'] ?? null);
$assert_same('Le slider automatique utilise sa limite', 6, $get_posts_calls[0]['posts_per_page'] ?? null);
$assert_contains('Le slider de pushes hydrate le composant', 'data-module="components/slider"', $slider_pushes);
$assert_contains('Le slider de pushes affiche les cards', 'class="card-news"', $slider_pushes);
$assert_contains('Le slider de pushes affiche sa navigation', 'class="slider-navigation"', $slider_pushes);

$get_posts_calls = [];
$get_posts_result = [(object) ['ID' => 1301], (object) ['ID' => 1302], (object) ['ID' => 1303]];
$post_types_by_id[1301] = 'news';
$post_types_by_id[1302] = 'news';
$post_types_by_id[1303] = 'news';
$more_pushes = $render_strate('pushes-more', ['post_type' => 'news', 'limit' => 2]);
$assert_same('Voir plus récupère un contenu supplémentaire', 3, $get_posts_calls[0]['posts_per_page'] ?? null);
$assert_same('Voir plus commence au premier lot', 0, $get_posts_calls[0]['offset'] ?? null);
$assert_same('Voir plus affiche seulement la taille du lot', 2, substr_count($more_pushes, 'class="card-news"'));
$assert_contains('Voir plus expose un lien de secours', 'pushes_more_1=2', $more_pushes);
$assert_contains('Voir plus hydrate son module', 'data-module="strates/strate-pushes-more/strate-pushes-more"', $more_pushes);

$_GET['pushes_more_2'] = '2';
$get_posts_calls = [];
$get_posts_result = [(object) ['ID' => 1303]];
$last_pushes = $render_strate('pushes-more', ['post_type' => 'news', 'limit' => 2]);
$assert_same('Voir plus décale la requête du second lot', 2, $get_posts_calls[0]['offset'] ?? null);
$assert_not_contains('Voir plus masque le lien après le dernier lot', 'class="pushes-more-next"', $last_pushes);
unset($_GET['pushes_more_2']);

$get_posts_calls = [];
$assert_same(
    'La remontée automatique refuse un CPT non enregistré',
    '',
    $render_strate('pushes-auto', ['post_type' => 'invalid'])
);
$assert_same('Un CPT invalide ne lance aucune requête', [], $get_posts_calls);

$get_posts_result = [];
$assert_same(
    'La strate pushes vide ne produit aucun HTML',
    '',
    $render_strate('pushes', ['items' => []])
);

$snap_strate = $render_strate('snap', [
    'slides' => [
        ['title' => 'Introduction', 'text' => '<p>Texte snap.</p>', 'background_color' => 'color-1'],
        ['title' => 'Expertises', 'image' => 401, 'background_color' => 'color-2', 'link' => ['url' => '/expertises/', 'title' => 'Découvrir']],
        ['title' => 'Projets', 'background_color' => 'color-3'],
        ['title' => 'Sans fond', 'background_color' => 'none'],
        ['text' => '<p>Sans titre.</p>'],
    ],
]);
$assert_contains('La strate snap hydrate le composant section snap', 'data-module="components/section-snap"', $snap_strate);
$assert_not_contains('La strate snap ne conserve pas son ancien module', 'data-module="strates/strate-snap"', $snap_strate);
$assert_contains('La strate snap rend sa navigation', 'class="snap-nav"', $snap_strate);
$assert_same('La strate snap rend les panneaux avec titre', 4, substr_count($snap_strate, 'class="strate strate-snap-panel'));
$assert_contains('La strate snap rend un panneau noir', 'strate-snap-panel bg-color-1', $snap_strate);
$assert_contains('La strate snap rend un panneau bleu', 'strate-snap-panel bg-color-2', $snap_strate);
$assert_contains('La strate snap rend un panneau gris', 'strate-snap-panel bg-color-3', $snap_strate);
$assert_contains('La strate snap rend le texte', 'Texte snap.', $snap_strate);
$assert_contains('La strate snap rend le lien', 'href="/expertises/"', $snap_strate);
$assert_contains('La strate snap rend l’image', 'snap-panel-image', $snap_strate);
$assert_same('La strate snap vide ne produit aucun HTML', '', $render_strate('snap', ['slides' => []]));
preg_match_all('/href="#([^"]+)"/', $snap_strate, $snap_ids);
$assert_same('La strate snap produit quatre ancres uniques', 4, count(array_unique($snap_ids[1] ?? [])));

$steps_strate = $render_strate('steps', [
    'title' => 'Notre méthode',
    'steps' => [
        ['title' => 'Écouter', 'text' => '<p>Votre besoin.</p>'],
        ['title' => 'Créer', 'link' => ['url' => '/contact/', 'title' => 'Nous contacter']],
        ['text' => 'Sans titre'],
    ],
]);
$assert_contains('La strate steps rend une liste ordonnée', '<ol class="steps-list">', $steps_strate);
$assert_same('La strate steps ignore les lignes sans titre', 2, substr_count($steps_strate, 'class="steps-item"'));
$assert_contains('La strate steps rend le texte', 'Votre besoin.', $steps_strate);
$assert_contains('La strate steps rend le lien', 'href="/contact/"', $steps_strate);
$assert_same('La strate steps vide ne produit aucun HTML', '', $render_strate('steps', ['steps' => []]));

$logos_strate = $render_strate('logos', [
    'logos' => [
        ['name' => 'Partenaire', 'image' => 401, 'link' => ['url' => 'https://example.test', 'title' => 'Voir le site', 'target' => '_blank']],
        ['image' => 402],
        ['name' => 'Sans image'],
    ],
]);
$assert_same('La strate logos ignore les lignes sans image', 2, substr_count($logos_strate, 'class="logos-item"'));
$assert_contains('La strate logos rend le nom accessible', 'alt="Partenaire"', $logos_strate);
$assert_contains('La strate logos protège les liens externes', 'rel="noopener noreferrer"', $logos_strate);
$assert_same('La strate logos vide ne produit aucun HTML', '', $render_strate('logos', ['logos' => []]));

$homepage_manifest = json_decode((string) file_get_contents(dirname(__DIR__, 5) . '/assets/heros/hero-homepage/hero-homepage.json'), true, 512, JSON_THROW_ON_ERROR);
$homepage_defaults = $homepage_manifest['defaults'] ?? [];
foreach (['suptitle', 'title', 'text'] as $homepage_name) {
    $assert_same('Le catalogue Homepage préremplit ' . $homepage_name, true, is_string($homepage_defaults[$homepage_name] ?? null) && trim($homepage_defaults[$homepage_name]) !== '');
}
$assert_contains('Le texte Homepage est éditorial', '<p>', $homepage_defaults['text'] ?? '');
$assert_same('Le catalogue Homepage conserve le placeholder desktop', 'assets/img/placeholders/hero-homepage-desktop.jpg', $homepage_defaults['images']['desktop']['__forgeStarterMediaPath'] ?? '');
$assert_same('Le catalogue Homepage conserve le placeholder mobile', 'assets/img/placeholders/hero-homepage-mobile.jpg', $homepage_defaults['images']['mobile']['__forgeStarterMediaPath'] ?? '');

if ($failures !== []) {
    fwrite(STDERR, implode("\n\n", $failures) . "\n");
    exit(1);
}

echo "Components contract: OK\n";
