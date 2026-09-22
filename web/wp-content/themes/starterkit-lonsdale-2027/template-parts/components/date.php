<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;


$value = component_string($params['date'] ?? '');
if ($value === '') {
    return;
}

$timezone = function_exists('wp_timezone')
    ? wp_timezone()
    : new DateTimeZone(date_default_timezone_get());

$normalize_datetime = static function (mixed $input) use ($timezone): string {
    if ($input instanceof DateTimeInterface) {
        return DateTimeImmutable::createFromInterface($input)
            ->setTimezone($timezone)
            ->format('Y-m-d');
    }

    $input = component_string($input);
    if ($input === '') return '';

    foreach ([
        '!Y-m-d' => 'Y-m-d',
        '!Ymd' => 'Ymd',
        '!d/m/Y' => 'd/m/Y',
    ] as $format => $comparison_format) {
        $date = DateTimeImmutable::createFromFormat($format, $input, $timezone);
        $errors = DateTimeImmutable::getLastErrors();
        $is_valid = $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format($comparison_format) === $input;

        if ($is_valid) return $date->format('Y-m-d');
    }

    return '';
};

$datetime = $normalize_datetime($params['datetime'] ?? '');
if ($datetime === '') {
    $datetime = $normalize_datetime(trim(strip_tags($value)));
}

$tag = $datetime !== '' ? 'time' : 'span';
$classes = component_classes('date', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], ['class', 'datetime']);
$content = wp_kses($value, [
    'span' => ['class' => true, 'aria-hidden' => true],
    'strong' => [],
    'em' => [],
    'abbr' => ['title' => true],
    'small' => [],
    'sup' => [],
    'sub' => [],
    'br' => [],
]);
?>
<<?= $tag ?> class="<?= $classes ?>"<?= $datetime !== '' ? ' datetime="' . esc_attr($datetime) . '"' : '' ?><?= $attributes ?>><?= $content ?></<?= $tag ?>>
