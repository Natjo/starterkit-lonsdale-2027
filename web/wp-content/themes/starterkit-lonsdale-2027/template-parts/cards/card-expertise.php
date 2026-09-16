<?php
$args = get_template_args();

$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';

$title = $to_string($args['title'] ?? '');
if ($title === '') return;
?>

<article class="card-expertise">
    <?php component('title', [
        'title' => $title,
        'hx' => 3,
        'classes' => 'title-3',
    ]); ?>

    <?php component('text', [
        'text' => $args['text'] ?? '',
        'classes' => 'card-text',
    ]); ?>
</article>
