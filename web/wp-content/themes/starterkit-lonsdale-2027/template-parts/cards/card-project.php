<?php
$args = get_template_args();

$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable ? trim((string) $value) : '';

$title = $to_string($args['title'] ?? '');
if ($title === '') return;

$link = is_array($args['link'] ?? null) && !array_is_list($args['link']) ? $args['link'] : [];
$url = $to_string($link['url'] ?? '');
$target = $to_string($link['target'] ?? '');
$tag = $url !== '' ? 'a' : 'div';
?>

<div class="card card-project">
    <<?= $tag ?> class="card-content" <?php if ($url !== '') : ?> href="<?= esc_url($url) ?>" <?php if ($target !== '') : ?> target="<?= esc_attr($target) ?>" <?= $target === '_blank' ? ' rel="noopener noreferrer"' : '' ?><?php endif; ?><?php endif; ?>>
        <?php component('picture', [
            'image' => $args['image'] ?? null,
            'size' => $args['image_size'] ?? 'large',
            'fit' => 'cover',
            'classes' => 'card-image',
        ]); ?>

        <div class="card-meta">
            <?php component('eyebrow', [
                'eyebrow' => $args['type'] ?? '',
                'element' => 'p',
                'classes' => 'card-type',
            ]); ?>

            <?php component('date', [
                'date' => $args['date'] ?? '',
                'datetime' => $args['datetime'] ?? '',
            ]); ?>
        </div>

        <?php component('title', [
            'title' => $title,
            'hx' => 3,
            'classes' => 'title-3',
        ]); ?>

        <?php component('text', [
            'text' => $args['text'] ?? '',
            'classes' => 'card-text',
        ]); ?>

        <?php component('btn', [
            'name' => "See",
            'element' => 'span',
            'classes' => 'btn-1',
        ]); ?>
    </<?= $tag ?>>
</div>