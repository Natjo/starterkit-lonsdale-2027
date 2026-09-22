<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

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

$email_label = component_string($args['email_label']);
$button_label = component_string($args['button_label']);
if ($email_label === '' || $button_label === '') return;

$email_name = component_string($args['email_name']);
if (!preg_match('/^[A-Za-z][A-Za-z0-9_.\-\[\]]*$/', $email_name)) return;

$email_id = component_id($args['email_id'], 'newsletter-email-');

$title = component_string($args['title']);
$text = component_string($args['text']);
$action = theme_http_url($args['action']);
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
            'placeholder' => component_string($args['email_placeholder']),
            'autocomplete' => 'email',
            'required' => true,
            'mandatory_msg' => component_string($args['mandatory_msg']),
            'type_mismatch_msg' => component_string($args['type_mismatch_msg']),
        ]); ?>

        <?php component('btn', [
            'name' => $button_label,
            'classes' => $args['button_classes'],
            'attributes' => ['type' => 'submit'],
        ]); ?>
    </form>
</div>
