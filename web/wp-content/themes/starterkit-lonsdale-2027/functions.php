<?php


$cdn = defined('LSD_CDN_ORIGIN') ? rtrim(LSD_CDN_ORIGIN, '/') : '';
if ($cdn && !is_admin()) {
    define('THEME_ASSETS', $cdn . '/wp-content/themes/starterkit-lonsdale-2027/assets/');
} else {
    define('THEME_ASSETS', get_template_directory_uri() . '/assets/');
}
// Filtre upload_dir (front uniquement)
add_filter('upload_dir', function ($dirs) use ($cdn) {
    if ($cdn && !is_admin()) {
        $dirs['baseurl'] = $cdn . '/wp-content/uploads';
        $dirs['url']     = $dirs['baseurl'] . $dirs['subdir'];
    }
    return $dirs;
});

defined('ENV_LOCAL') || define('ENV_LOCAL', false);
defined('ENV_PROD') || define('ENV_PROD', false);
if (!ENV_LOCAL) define('ACF_LITE', true);

define('THEME_DIR', get_template_directory() . '/');
define('THEME_URL', get_template_directory_uri() . '/');
define('UPLOADS_DIR', wp_upload_dir());
//define('THEME_UPLOADS', UPLOADS_DIR['baseurl']);
define('THEME_UPLOADS', $cdn && !is_admin()
    ? $cdn . '/wp-content/uploads'
    : UPLOADS_DIR['baseurl']
);
define('HOME_URL', get_home_url());
define('AJAX_URL', admin_url('admin-ajax.php'));

require_once __DIR__ . '/inc/config/setup.php';
require_once __DIR__ . '/inc/config/image-sizes.php';
require_once __DIR__ . '/inc/config/cleanup.php';
require_once __DIR__ . '/inc/config/disable-comments.php';
require_once __DIR__ . '/inc/config/security.php';
require_once __DIR__ . '/inc/config/acf.php';
require_once __DIR__ . '/inc/config/search.php';
require_once __DIR__ . '/inc/config/seo.php';

require_once __DIR__ . '/inc/datatypes.php';
require_once __DIR__ . '/inc/methods.php';
require_once __DIR__ . '/inc/ajax-methods.php';

require_once __DIR__ . '/inc/admin/wysiwyg.php';
require_once __DIR__ . '/inc/admin/utilities.php';

require_once __DIR__ . '/inc/api/maintenance.php';

require_once __DIR__ . '/inc/front/components.php';
require_once __DIR__ . '/inc/front/methods.php';
require_once __DIR__ . '/inc/front/breadcrumb.php';

require_once __DIR__ . '/vendor/autoload.php';

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || wp_is_json_request()) {
        return;
    }

    ob_start([ThemeAssets::class, 'processBufferedHtml']);
}, 0);

add_filter('template_include', function (string $template): string {
    theme_queue_template_assets($template);

    return $template;
});

add_action('wp_enqueue_scripts', function () {
    $app_css = THEME_DIR . 'assets/css/app.css';
    $app_js = THEME_DIR . 'assets/js/app.js';

    wp_enqueue_style(
        'app-css',
        THEME_ASSETS . 'css/app.css',
        [],
        file_exists($app_css) ? filemtime($app_css) : null
    );

    wp_enqueue_script(
        'app-js',
        THEME_ASSETS . 'js/app.js',
        [],
        file_exists($app_js) ? filemtime($app_js) : null,
        true
    );

    wp_localize_script('app-js', 'ParamsData', [
        'ajaxUrl'      => AJAX_URL,
        'nonce'        => wp_create_nonce('ajax'),
        /*'translations' => [
            'form' => [
                'default'   => __('Ce champ est obligatoire'),
                'passwords' => __('Les mots de passe doivent être identiques'),
                'format'    => __('Le format renseigné n\'est pas valide'),
                'emails'    => __('Les adresses e-mails doivent être identiques'),
                'error'     => __('Une erreur est survenue.'),
            ],
        ],*/
    ]);
});

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }

    $critical_css = THEME_DIR . 'assets/css/critical.css';

    if (!is_readable($critical_css) || filesize($critical_css) === 0) {
        return;
    }

    $css = file_get_contents($critical_css);

    if ($css === false || trim($css) === '') {
        return;
    }

    $heroCss = ThemeAssets::getHeroCriticalCssForHead();

    if ($heroCss !== '') {
        $css .= $heroCss;
    }

    printf(
        '<style id="critical-css">%s</style>' . "\n",
        $css
    );
}, 1);

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || wp_is_json_request() || !is_singular()) {
        return;
    }

    $fields = get_fields() ?: [];
    theme_prescan_page_assets($fields);
}, 5);

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }

    ThemeAssets::renderOnceStyles();
    ThemeAssets::renderSplitLinks();
}, 5);

add_action('admin_print_styles', function () {
    $admin_path = THEME_DIR . 'assets/css/admin.css';
    $admin_url  = THEME_ASSETS . 'css/admin.css';
    $version    = is_readable($admin_path) ? filemtime($admin_path) : null;

    wp_enqueue_style('admin_css', $admin_url, [], $version);
});

add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle === 'app-js') {
        return str_replace(' src=', ' defer src=', $tag);
    }
    return $tag;
}, 10, 2);

if (ENV_LOCAL) {
    add_action('phpmailer_init', function ($phpmailer) {
        $phpmailer->Host = 'mailpit';
        $phpmailer->Port = 1025;
        $phpmailer->IsSMTP();
    });
}
