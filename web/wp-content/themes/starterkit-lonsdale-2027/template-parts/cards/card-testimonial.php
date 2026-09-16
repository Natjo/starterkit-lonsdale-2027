<?php
$args = get_template_args();
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';
$quote = $to_string($args['quote'] ?? '');

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
