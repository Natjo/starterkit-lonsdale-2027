<?php
$args = get_template_args();
$card_fields = $args['card'] ?? null;
if (is_array($card_fields) && !array_is_list($card_fields)) {
    $args = array_replace($card_fields, $args);
}

$value = theme_string($args['value'] ?? '');
if ($value === '') return;

$prefix = theme_string($args['prefix'] ?? '');
$suffix = theme_string($args['suffix'] ?? '');
?>

<article class="card-keynumber">
    <p class="keynumber-number">
        <?php if ($prefix !== '') : ?>
            <span class="keynumber-prefix"><?= esc_html($prefix) ?></span>
        <?php endif; ?>
        <span class="keynumber-value"><?= esc_html($value) ?></span>
        <?php if ($suffix !== '') : ?>
            <span class="keynumber-suffix"><?= esc_html($suffix) ?></span>
        <?php endif; ?>
    </p>

    <?php component('title', [
        'title' => $args['title'] ?? '',
        'hx' => 3,
        'classes' => 'keynumber-title title-3',
    ]); ?>
    <?php component('text', [
        'text' => $args['text'] ?? '',
        'classes' => 'keynumber-text',
    ]); ?>
</article>