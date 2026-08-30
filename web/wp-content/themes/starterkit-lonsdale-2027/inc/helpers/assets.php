<?php

define('AVIF_CONVERSION_ENABLED', false);
define('AVIF_QUALITY', 90);
define('AVIF_ALLOWED_MIMES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

function convertToAvif($source, $quality = AVIF_QUALITY)
{
    if (!AVIF_CONVERSION_ENABLED) {
        return false;
    }

    if (!function_exists('imageavif') || !file_exists($source)) {
        return false;
    }

    $mime = mime_content_type($source);

    $image = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($source),
        'image/png' => imagecreatefrompng($source),
        'image/webp' => imagecreatefromwebp($source),
        'image/gif' => imagecreatefromgif($source),
        default => false,
    };

    if (!$image) return false;

    if ($mime === 'image/png') {
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }

    $result = imageavif($image, $source . '.avif', $quality);
    imagedestroy($image);

    return $result;
}

add_filter('wp_generate_attachment_metadata', function ($metadata, $attachment_id) {
    if (!AVIF_CONVERSION_ENABLED) {
        return $metadata;
    }

    $mime = get_post_mime_type($attachment_id);

    if (!in_array($mime, AVIF_ALLOWED_MIMES)) return $metadata;

    $base_dir = wp_upload_dir()['basedir'];
    $file = $base_dir . '/' . $metadata['file'];

    convertToAvif($file);

    if (!empty($metadata['sizes'])) {
        $dir = dirname($file);
        foreach ($metadata['sizes'] as $size) {
            convertToAvif($dir . '/' . $size['file']);
        }
    }

    return $metadata;
}, 10, 2);

add_action('delete_attachment', function ($attachment_id) {
    if (!AVIF_CONVERSION_ENABLED) {
        return;
    }

    $metadata = wp_get_attachment_metadata($attachment_id);
    if (!$metadata || empty($metadata['file'])) return;

    $base_dir = wp_upload_dir()['basedir'];
    $file = $base_dir . '/' . $metadata['file'];

    @unlink($file . '.avif');

    if (!empty($metadata['sizes'])) {
        $dir = dirname($file);
        foreach ($metadata['sizes'] as $size) {
            @unlink($dir . '/' . $size['file'] . '.avif');
        }
    }
});

function svg($path = "", $folder = true)
{
    static $cache = [];

    $file = $folder ? THEME_DIR . 'assets/img/svg/' . $path . '.svg' : $path;

    if (!isset($cache[$file])) {
        $cache[$file] = file_exists($file) ? file_get_contents($file) : null;
    }

    return $cache[$file];
}

function asset($path)
{
    return THEME_URL . 'assets/' . $path;
}

/**
 * Résout une image (ID média WP ou URL) vers [src, width, height, alt].
 * Utilisé par les composants picture / image.
 */
function lsd_get_thumb(mixed $input, mixed $size = 'full'): array
{
    if ($input instanceof Stringable) {
        $input = (string) $input;
    }

    if ($input === '' || $input === null) {
        return [];
    }

    if (is_numeric($input)) {
        $id = (int) $input;
        if ($id <= 0) {
            return [];
        }

        if (is_array($size)) {
            $width = (int) ($size[0] ?? 0);
            $height = (int) ($size[1] ?? 0);
            $size = $width > 0 && $height > 0 ? [$width, $height] : 'full';
        } else {
            $size = is_string($size) && $size !== '' ? $size : 'full';
        }
        $data = wp_get_attachment_image_src($id, $size);
        if (empty($data[0])) {
            return [];
        }

        return [
            $data[0],
            (int) ($data[1] ?? 0),
            (int) ($data[2] ?? 0),
            (string) get_post_meta($id, '_wp_attachment_image_alt', true),
        ];
    }

    $src = is_scalar($input) ? trim((string) $input) : '';
    if ($src === '') {
        return [];
    }

    $path = lsd_get_thumb_local_path($src);
    $width = 0;
    $height = 0;

    if ($path !== '' && is_file($path)) {
        $dimensions = @getimagesize($path);
        if ($dimensions) {
            $width = (int) ($dimensions[0] ?? 0);
            $height = (int) ($dimensions[1] ?? 0);
        }
    }

    return [$src, $width, $height, ''];
}

function lsd_get_thumb_local_path(string $src): string
{
    if (defined('THEME_ASSETS') && str_starts_with($src, THEME_ASSETS)) {
        return THEME_DIR . 'assets/' . ltrim(substr($src, strlen(THEME_ASSETS)), '/');
    }

    if (defined('THEME_URL') && str_starts_with($src, THEME_URL)) {
        return THEME_DIR . ltrim(substr($src, strlen(THEME_URL)), '/');
    }

    if (defined('UPLOADS_DIR') && !empty(UPLOADS_DIR['baseurl']) && str_starts_with($src, UPLOADS_DIR['baseurl'])) {
        return UPLOADS_DIR['basedir'] . '/' . ltrim(substr($src, strlen(UPLOADS_DIR['baseurl'])), '/');
    }

    return '';
}

function lsd_image_size_suffix(mixed $size): string
{
    if (is_array($size)) {
        $width = (int) ($size[0] ?? 0);
        $height = (int) ($size[1] ?? 0);

        return $width > 0 && $height > 0 ? $width . 'x' . $height : '';
    }

    $size = is_scalar($size) ? trim((string) $size) : '';

    return $size !== '' && $size !== 'full' ? $size : '';
}

function lsd_upload_relative_path(string $src): string
{
    if (!defined('THEME_UPLOADS')) {
        return '';
    }

    $src = str_replace('\\', '/', $src);
    $uploads = rtrim(str_replace('\\', '/', THEME_UPLOADS), '/') . '/';

    if (!str_starts_with($src, $uploads)) {
        return '';
    }

    $relative = ltrim(substr($src, strlen($uploads)), '/');

    return preg_match('#(^|/)\.\.(/|$)#', $relative) ? '' : $relative;
}

function lsd_uploads_basedir(): string
{
    if (!defined('UPLOADS_DIR') || empty(UPLOADS_DIR['basedir'])) {
        return '';
    }

    return rtrim(str_replace('\\', '/', (string) UPLOADS_DIR['basedir']), '/');
}

function lsd_image_is_webp_convertible(string $src): bool
{
    $path = parse_url($src, PHP_URL_PATH) ?: $src;

    return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
}

/**
 * Chemin WebP associé à une déclinaison uploads.
 * - Déclinaison déjà dans l'URL : image-880x475.jpg → image-880x475.webp
 * - Image full + taille : image.jpg + 880x0 → image-880x0.webp
 */
function lsd_image_webp_src(mixed $src, mixed $size = 'full'): string
{
    static $cache = [];

    if (!defined('THEME_UPLOADS') || lsd_uploads_basedir() === '') {
        return '';
    }

    $key = (string) $src . '|' . (is_array($size) ? implode('x', $size) : (string) $size);

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $relative = lsd_upload_relative_path((string) $src);

    if ($relative === '') {
        return $cache[$key] = '';
    }

    $basedir = lsd_uploads_basedir();
    $uploadsUrl = rtrim(THEME_UPLOADS, '/') . '/';

    $direct = preg_replace('/\.(jpe?g|png)$/i', '.webp', $relative);

    if ($direct !== $relative && is_file($basedir . '/' . $direct)) {
        return $cache[$key] = $uploadsUrl . $direct;
    }

    $path = pathinfo($relative);
    $dirname = !empty($path['dirname']) && $path['dirname'] !== '.' ? $path['dirname'] . '/' : '';
    $suffix = lsd_image_size_suffix($size);
    $suffix = $suffix !== '' ? '-' . $suffix : '';
    $webp = $dirname . $path['filename'] . $suffix . '.webp';

    if (!is_file($basedir . '/' . $webp)) {
        return $cache[$key] = '';
    }

    return $cache[$key] = $uploadsUrl . $webp;
}

function lsd_image_file_dimensions(string $src): array
{
    static $cache = [];

    $path = lsd_get_thumb_local_path($src);

    if ($path === '' && defined('UPLOADS_DIR') && !empty(UPLOADS_DIR['baseurl']) && str_starts_with($src, UPLOADS_DIR['baseurl'])) {
        $path = UPLOADS_DIR['basedir'] . '/' . ltrim(substr($src, strlen(UPLOADS_DIR['baseurl'])), '/');
    }

    if ($path === '') {
        return [];
    }

    if (array_key_exists($path, $cache)) {
        return $cache[$path];
    }

    if (!is_file($path)) {
        return $cache[$path] = [];
    }

    $dimensions = @getimagesize($path);

    return $cache[$path] = [
        'width' => (int) ($dimensions[0] ?? 0),
        'height' => (int) ($dimensions[1] ?? 0),
    ];
}

function theme_image_mime_type(string $src): string
{
    return match (strtolower(pathinfo($src, PATHINFO_EXTENSION))) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'avif' => 'image/avif',
        'svg' => 'image/svg+xml',
        default => 'image/jpeg',
    };
}

/**
 * Résout une image et l’enregistre pour preload dans le <head>.
 *
 * @param array{media?: string, type?: string, imagesrcset?: string, imagesizes?: string} $options
 */
function theme_enqueue_image_preload(mixed $input, mixed $size = 'full', array $options = []): void
{
    if ($input === '' || $input === null || !function_exists('starter_enqueue_preload_image')) {
        return;
    }

    $image = lsd_get_thumb($input, $size);

    if (empty($image[0])) {
        return;
    }

    $href = (string) $image[0];

    if (lsd_image_is_webp_convertible($href)) {
        $webp = lsd_image_webp_src($href, $size);

        if ($webp !== '') {
            $href = $webp;
        }
    }

    $type = isset($options['type']) && is_string($options['type']) && $options['type'] !== ''
        ? $options['type']
        : theme_image_mime_type($href);

    starter_enqueue_preload_image([
        'href' => $href,
        'media' => isset($options['media']) ? (string) $options['media'] : '',
        'type' => $type,
        'imagesrcset' => isset($options['imagesrcset']) ? (string) $options['imagesrcset'] : '',
        'imagesizes' => isset($options['imagesizes']) ? (string) $options['imagesizes'] : '',
    ]);
}
