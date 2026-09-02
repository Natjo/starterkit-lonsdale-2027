<?php
$args = get_template_args();

$text_value = $args['text'] ?? null;
$text = is_scalar($text_value) || $text_value instanceof Stringable
    ? trim((string) $text_value)
    : '';

if ($text === '') {
    return;
}
?>

<section <?= options('strate strate-quote', $args) ?>>
    <?php component('quote', [
        'quote' => $text,
        'image' => $args['image'] ?? null,
        'image_size' => '40_40',
        'author' => $args['author'] ?? '',
        'source' => $args['source'] ?? '',
        'url' => $args['url'] ?? '',
    ]); ?>
</section>
