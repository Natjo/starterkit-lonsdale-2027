<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;


$input = $params['image'] ?? null;
if ($input === null || $input === '') {
    return;
}

$responsive_sizes = [];
if (is_array($params['sizes'] ?? null)) {
    foreach ($params['sizes'] as $responsive_size) {
        $responsive_size = component_string($responsive_size);
        if (
            $responsive_size !== ''
            && preg_match('/^[A-Za-z0-9_-]+$/', $responsive_size)
            && !in_array($responsive_size, $responsive_sizes, true)
        ) {
            $responsive_sizes[] = $responsive_size;
        }
    }
}

$default_size = $params['size'] ?? 'full';
$resolved_input = $input;
$input_url = !is_numeric($input) ? component_string($input) : '';
if ($input_url !== '') {
    $attachment_id = theme_attachment_url_to_post_id($input_url);
    if ($attachment_id > 0) {
        $resolved_input = $attachment_id;
    }
}

$resolve = static function (mixed $input, mixed $size): array {
    $image = lsd_get_thumb($input, $size);
    $src = isset($image[0]) ? trim((string) $image[0]) : '';
    $src = $src !== '' ? esc_url_raw($src) : '';
    if ($src === '') {
        return [];
    }

    $width = (int) ($image[1] ?? 0);
    $height = (int) ($image[2] ?? 0);

    if (lsd_image_is_webp_convertible($src)) {
        $webp = lsd_image_webp_src($src, $size);
        $webp = $webp !== '' ? esc_url_raw($webp) : '';

        if ($webp !== '') {
            $webp_dimensions = lsd_image_file_dimensions($webp);
            $src = $webp;
            $width = (int) ($webp_dimensions['width'] ?? $width);
            $height = (int) ($webp_dimensions['height'] ?? $height);
        }
    }

    return [
        'src' => $src,
        'width' => $width,
        'height' => $height,
        'alt' => (string) ($image[3] ?? ''),
        'size' => $size,
    ];
};

$primary = [];
$candidates = [];

if ($responsive_sizes !== []) {
    foreach ($responsive_sizes as $responsive_size) {
        $candidate = $resolve($resolved_input, $responsive_size);
        if ($candidate === []) {
            continue;
        }

        if ($primary === []) {
            $primary = $candidate;
        }

        $candidate_width = (int) $candidate['width'];
        if ($candidate_width > 0 && !isset($candidates[$candidate_width])) {
            $candidates[$candidate_width] = $candidate;
        }
    }
} else {
    $primary = $resolve($resolved_input, $default_size);
}

if ($primary === []) {
    return;
}

$src = (string) $primary['src'];
$width = (int) $primary['width'];
$height = (int) $primary['height'];
$primary_width = $width;
$primary_size = $primary['size'];
$alt = array_key_exists('alt', $params) ? component_string($params['alt']) : (string) $primary['alt'];
$srcset = '';
$html_sizes = '';

if (count($candidates) > 1) {
    ksort($candidates, SORT_NUMERIC);
    $srcset_parts = [];
    foreach ($candidates as $candidate_width => $candidate) {
        $srcset_parts[] = (string) $candidate['src'] . ' ' . (int) $candidate_width . 'w';
    }
    $srcset = implode(', ', $srcset_parts);

    if ($primary_width > 0) {
        $html_sizes = '(max-width: ' . $primary_width . 'px) 100vw, ' . $primary_width . 'px';
    }
}

if (is_numeric($params['width'] ?? null) && (int) $params['width'] > 0) {
    $width = (int) $params['width'];
}
if (is_numeric($params['height'] ?? null) && (int) $params['height'] > 0) {
    $height = (int) $params['height'];
}

$preload = component_bool($params['preload'] ?? false);
$critical = component_bool($params['critical'] ?? false) || $preload;
$is_lazy = component_bool($params['lazy'] ?? true, true) && !$critical;

if ($preload && function_exists('theme_enqueue_image_preload')) {
    theme_enqueue_image_preload($resolved_input, $primary_size, [
        'imagesrcset' => $srcset,
        'imagesizes' => $html_sizes,
    ]);
}

$fetchpriority = strtolower(component_string($params['fetchpriority'] ?? ''));
$fetchpriority = in_array($fetchpriority, ['low', 'auto', 'high'], true) ? $fetchpriority : '';
$fetchpriority = $critical ? 'high' : ($fetchpriority === 'high' ? '' : $fetchpriority);
$decoding = strtolower(component_string($params['decoding'] ?? 'async'));
$decoding = in_array($decoding, ['async', 'sync', 'auto'], true) ? $decoding : 'async';
$classes = component_classes($params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], [
    'class',
    'src',
    'srcset',
    'sizes',
    'alt',
    'width',
    'height',
    'loading',
    'decoding',
    'fetchpriority',
]);
?>
<img<?= $classes !== '' ? ' class="' . $classes . '"' : '' ?> src="<?= esc_url($src) ?>"<?= $srcset !== '' ? ' srcset="' . esc_attr($srcset) . '"' : '' ?><?= $html_sizes !== '' ? ' sizes="' . esc_attr($html_sizes) . '"' : '' ?> alt="<?= esc_attr($alt) ?>"<?= $width > 0 ? ' width="' . $width . '"' : '' ?><?= $height > 0 ? ' height="' . $height . '"' : '' ?><?= $is_lazy ? ' loading="lazy"' : '' ?> decoding="<?= esc_attr($decoding) ?>"<?= $fetchpriority !== '' ? ' fetchpriority="' . esc_attr($fetchpriority) . '"' : '' ?><?= $attributes ?>>
