<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'src' => '',
    'mobile_src' => '',
    'breakpoint' => 768,
    'autoplay' => false,
    'loop' => false,
    'progressive_load' => true,
    'respect_reduced_motion' => true,
    'autonomous' => true,
    'width' => null,
    'height' => null,
    'label' => '',
    'classes' => '',
    'attributes' => [],
]);

$to_source = static function (mixed $value): string {
    $source = component_string($value);
    $path = parse_url($source, PHP_URL_PATH);
    if (
        $source === ''
        || str_starts_with($source, '//')
        || preg_match('/[\x00-\x1F\x7F\\\\]/', $source)
        || !is_string($path)
        || has_unsafe_path_segments($path)
    ) {
        return '';
    }

    $scheme = strtolower((string) parse_url($source, PHP_URL_SCHEME));
    return $scheme === '' || in_array($scheme, ['http', 'https'], true) ? $source : '';
};
$to_dimension = static function (mixed $value): ?float {
    if (!is_numeric($value)) return null;

    $dimension = (float) $value;
    return is_finite($dimension) && $dimension > 0 ? $dimension : null;
};

$src = $to_source($args['src']);
$mobile_src = $to_source($args['mobile_src']);
if ($src === '') {
    $src = $mobile_src;
    $mobile_src = '';
}
if ($src === '' || $mobile_src === $src) {
    $mobile_src = '';
}
if ($src === '') return;

$breakpoint = filter_var($args['breakpoint'], FILTER_VALIDATE_INT);
$breakpoint = $breakpoint !== false && $breakpoint > 0 ? $breakpoint : 768;
$autoplay = component_bool($args['autoplay'], false);
$loop = component_bool($args['loop'], false);
$progressive_load = component_bool($args['progressive_load'], true);
$respect_reduced_motion = component_bool($args['respect_reduced_motion'], true);
$autonomous = component_bool($args['autonomous'], true);
$width = $to_dimension($args['width']);
$height = $to_dimension($args['height']);
$label = component_string($args['label']);
$classes = component_classes('lottie', $args['classes']);
$attributes = component_attributes($args['attributes'], [
    'class',
    'style',
    'role',
    'aria-label',
    'aria-hidden',
    'data-module',
    'data-context',
    'data-lottie-src',
    'data-lottie-src-mobile',
    'data-lottie-breakpoint',
    'data-lottie-autoplay',
    'data-lottie-loop',
    'data-lottie-progressive-load',
    'data-lottie-reduced-motion',
    'data-lottie-error',
]);
$aspect_ratio = $width !== null && $height !== null
    ? rtrim(rtrim(sprintf('%.4F', $width), '0'), '.') . ' / ' . rtrim(rtrim(sprintf('%.4F', $height), '0'), '.')
    : '';
?>

<div
    class="<?= $classes ?>"
    data-lottie-src="<?= esc_url($src) ?>"
    data-lottie-autoplay="<?= $autoplay ? 'true' : 'false' ?>"
    data-lottie-loop="<?= $loop ? 'true' : 'false' ?>"
    data-lottie-progressive-load="<?= $progressive_load ? 'true' : 'false' ?>"
    data-lottie-reduced-motion="<?= $respect_reduced_motion ? 'true' : 'false' ?>"<?=
        $mobile_src !== '' ? ' data-lottie-src-mobile="' . esc_url($mobile_src) . '" data-lottie-breakpoint="' . esc_attr((string) $breakpoint) . '"' : ''
    ?><?= $autonomous ? ' data-module="components/lottie" data-context="@visible true"' : '' ?><?=
        $aspect_ratio !== '' ? ' style="--lottie-aspect-ratio:' . esc_attr($aspect_ratio) . '"' : ''
    ?><?= $label !== '' ? ' role="img" aria-label="' . esc_attr($label) . '"' : ' aria-hidden="true"' ?><?= $attributes ?>></div>
