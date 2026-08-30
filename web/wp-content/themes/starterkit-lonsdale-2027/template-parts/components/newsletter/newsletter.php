<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'title' => '',
    'text' => '',
    'action' => '',
    'email_id' => '',
    'email_name' => 'email',
    'email_label' => '',
    'email_placeholder' => '',
    'button_label' => '',
    'mandatory_msg' => '',
    'type_mismatch_msg' => '',
    'title_classes' => 'title-3',
    'button_classes' => '',
    'classes' => '',
    'attributes' => [],
]);

$to_string = static fn (mixed $value): string =>
    is_scalar($value) || $value instanceof Stringable ? trim((string) $value) : '';
$http_url = static function (mixed $value) use ($to_string): string {
    $url = $to_string($value);
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) return '';

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : '';
};

$email_label = $to_string($args['email_label']);
$button_label = $to_string($args['button_label']);
if ($email_label === '' || $button_label === '') return;

$email_name = $to_string($args['email_name']);
if (!preg_match('/^[A-Za-z][A-Za-z0-9_.\-\[\]]*$/', $email_name)) return;

$email_id = sanitize_html_class($to_string($args['email_id']));
if ($email_id === '') {
    $email_id = function_exists('wp_unique_id')
        ? wp_unique_id('newsletter-email-')
        : uniqid('newsletter-email-', false);
}

$title = $to_string($args['title']);
$text = $to_string($args['text']);
$action = $http_url($args['action']);
$classes = component_classes('newsletter', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class']);
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?php component('title', [
        'title' => $title,
        'hx' => 2,
        'classes' => $args['title_classes'],
    ]); ?>

    <?php component('text', [
        'text' => $text,
        'classes' => 'rte',
    ]); ?>

    <form
        class="newsletter-form"
        method="post"
        <?= $action !== '' ? 'action="' . esc_url($action) . '"' : '' ?>
        data-context="@visible true"
        data-module="components/field"
    >
        <?php component('field', [
            'type' => 'email',
            'id' => $email_id,
            'name' => $email_name,
            'label' => $email_label,
            'placeholder' => $to_string($args['email_placeholder']),
            'autocomplete' => 'email',
            'required' => true,
            'mandatory_msg' => $to_string($args['mandatory_msg']),
            'type_mismatch_msg' => $to_string($args['type_mismatch_msg']),
        ]); ?>

        <?php component('btn', [
            'name' => $button_label,
            'classes' => $args['button_classes'],
            'attributes' => ['type' => 'submit'],
        ]); ?>
    </form>
</div>
