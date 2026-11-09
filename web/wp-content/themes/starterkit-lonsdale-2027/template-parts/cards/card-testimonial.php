<?php
$args = get_template_args();
$card_fields = $args['card'] ?? null;
if (is_array($card_fields) && !array_is_list($card_fields)) {
    $args = array_replace($card_fields, $args);
}
$quote = theme_string($args['quote'] ?? '');

if ($quote === '') return;
?>

<article class="card-testimonial">
    <?php component('quote', [
        'quote' => $quote,
        'image' => $args['image'] ?? null,
        'image_size' => $args['image_size'] ?? 'full',
        'author' => $args['author'] ?? '',
        'source' => $args['source'] ?? '',
    ]); ?>
</article>