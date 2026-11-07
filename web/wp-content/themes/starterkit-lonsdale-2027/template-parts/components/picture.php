<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$normalize_size = static function (mixed $size): string|array {
    if (is_array($size)) {
        $width = (int) ($size[0] ?? 0);
        $height = (int) ($size[1] ?? 0);
        return $width > 0 && $height > 0 ? [$width, $height] : 'full';
    }

    return is_scalar($size) && trim((string) $size) !== '' ? trim((string) $size) : 'full';
};

$images = array_key_exists('images', $params)
    ? $params['images']
    : ($params['image'] ?? null);
if (is_array($images)) {
    $raw = [
        'desktop' => $images['desktop'] ?? null,
        'mobile' => $images['mobile'] ?? null,
    ];
} else {
    $raw = ['desktop' => $images, 'mobile' => null];
}

$classes = $params['classes'] ?? '';
$attributes = component_attributes($params['attributes'] ?? [], ['class']);
$fit = is_scalar($params['fit'] ?? null) ? strtolower(trim((string) $params['fit'])) : '';
$fit = in_array($fit, ['fluid', 'cover'], true) ? 'fit-' . $fit : '';
$placeholder = component_bool($params['placeholder'] ?? false);
$breakpoint = is_numeric($params['breakpoint'] ?? null) ? (int) $params['breakpoint'] : 768;
$breakpoint = $breakpoint > 0 ? $breakpoint : 768;
$lazy = component_bool($params['lazy'] ?? true, true);
$preload = component_bool($params['preload'] ?? false);
$critical = component_bool($params['critical'] ?? false) || $preload;
$size = $params['size'] ?? 'full';
$desktop_size = $normalize_size($params['desktop_size'] ?? $size);
$mobile_size = $normalize_size($params['mobile_size'] ?? $size);

$render_placeholder = static function () use ($classes, $fit, $attributes): void {
    $placeholder_classes = component_classes('picture', $fit, 'placeholder', $classes);
    echo '<picture class="' . $placeholder_classes . '"' . $attributes . '></picture>';
};

if (empty($raw['desktop']) && empty($raw['mobile'])) {
    if ($placeholder) $render_placeholder();
    return;
}

$mime_type = static function (string $src): string {
    $path = (string) (parse_url($src, PHP_URL_PATH) ?: $src);
    $filetype = wp_check_filetype($path);
    return is_string($filetype['type'] ?? null) ? $filetype['type'] : '';
};
$resolve = static function (mixed $input, string|array $size) use ($mime_type): array {
    $image = lsd_get_thumb($input, $size);
    $src = isset($image[0]) ? (string) $image[0] : '';
    if ($src === '' || esc_url($src) === '') return [];

    $webp = lsd_image_webp_src($src, $size);
    $webp_dimensions = $webp !== '' ? lsd_image_file_dimensions($webp) : [];

    return [
        'src' => $src,
        'width' => (int) ($image[1] ?? 0),
        'height' => (int) ($image[2] ?? 0),
        'alt' => (string) ($image[3] ?? ''),
        'webp' => $webp,
        'webp_width' => (int) ($webp_dimensions['width'] ?? 0),
        'webp_height' => (int) ($webp_dimensions['height'] ?? 0),
        'type' => $mime_type($src),
    ];
};

$desktop = !empty($raw['desktop']) ? $resolve($raw['desktop'], $desktop_size) : [];
$mobile = !empty($raw['mobile']) ? $resolve($raw['mobile'], $mobile_size) : [];
$fallback = $desktop !== [] ? $desktop : $mobile;
$has_responsive_variants = $desktop !== [] && $mobile !== [];

if ($fallback === []) {
    if ($placeholder) $render_placeholder();
    return;
}

$alt_value = array_key_exists('alt', $params) ? $params['alt'] : ($fallback['alt'] ?? '');
$alt = is_scalar($alt_value) || $alt_value instanceof Stringable ? trim((string) $alt_value) : '';
$picture_classes = component_classes('picture', $fit, $classes);
$class_attribute = ' class="' . $picture_classes . '"';
$loading_attribute = $lazy && !$critical ? ' loading="lazy"' : '';
$priority_attribute = $critical ? ' fetchpriority="high"' : '';
$desktop_media = $mobile !== [] ? ' media="(min-width:' . $breakpoint . 'px)"' : '';
$mobile_media = ' media="(max-width:' . ($breakpoint - 1) . 'px)"';

$dimensions = static function (array $image, bool $webp = false): string {
    $prefix = $webp ? 'webp_' : '';
    $width = (int) ($image[$prefix . 'width'] ?? 0);
    $height = (int) ($image[$prefix . 'height'] ?? 0);
    return $width > 0 && $height > 0 ? ' width="' . $width . '" height="' . $height . '"' : '';
};
$type_attribute = static fn(array $image): string => ($image['type'] ?? '') !== ''
    ? ' type="' . esc_attr($image['type']) . '"'
    : '';
$preload_variant = static function (array $image, string $media = '', bool $prefer_webp = true): void {
    if (!function_exists('starter_enqueue_preload_image')) return;

    $use_webp = $prefer_webp && $image['webp'] !== '';

    starter_enqueue_preload_image([
        'href' => $use_webp ? $image['webp'] : $image['src'],
        'media' => $media,
        'type' => $use_webp ? 'image/webp' : $image['type'],
    ]);
};

if ($preload) {
    if ($has_responsive_variants) {
        $preload_variant($mobile, '(max-width: ' . ($breakpoint - 1) . 'px)');
        $preload_variant($desktop, '(min-width: ' . $breakpoint . 'px)');
    } else {
        $preload_variant($fallback);
    }
}
?>
<picture<?= $class_attribute . $attributes ?>>
    <?php if ($has_responsive_variants) : ?>
        <?php if ($mobile['webp'] !== '') : ?>
            <source<?= $dimensions($mobile, true) ?> srcset="<?= esc_url($mobile['webp']) ?>"<?= $mobile_media ?> type="image/webp">
        <?php endif ?>
        <source<?= $dimensions($mobile) ?> srcset="<?= esc_url($mobile['src']) ?>"<?= $mobile_media ?><?= $type_attribute($mobile) ?>>

        <?php if ($desktop['webp'] !== '') : ?>
            <source<?= $dimensions($desktop, true) ?> srcset="<?= esc_url($desktop['webp']) ?>"<?= $desktop_media ?> type="image/webp">
        <?php endif ?>
        <source<?= $dimensions($desktop) ?> srcset="<?= esc_url($desktop['src']) ?>"<?= $desktop_media ?><?= $type_attribute($desktop) ?>>
    <?php elseif ($fallback['webp'] !== '') : ?>
        <source<?= $dimensions($fallback, true) ?> srcset="<?= esc_url($fallback['webp']) ?>" type="image/webp">
    <?php endif ?>

    <img src="<?= esc_url($fallback['src']) ?>" alt="<?= esc_attr($alt) ?>"<?= $dimensions($fallback) . $loading_attribute . $priority_attribute ?>>
</picture>
