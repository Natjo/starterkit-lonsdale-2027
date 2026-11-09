<?php

/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'quote' => '',
    'image' => null,
    'image_size' => 'full',
    'author' => '',
    'source' => '',
    'url' => '',
    'classes' => '',
    'attributes' => [],
]);
$quote = theme_string($args['quote']);
$author = theme_string($args['author']);
$source = theme_string($args['source']);
$url = esc_url(theme_string($args['url']));
$image = $args['image'];
$has_image = is_scalar($image) || $image instanceof Stringable
    ? trim((string) $image) !== ''
    : false;

if ($quote === '') return;

$classes = component_classes('quote', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class']);
?>

<figure class="<?= $classes ?>" <?= $attributes ?>>
    <?php component('icon', [
        'name' => 'quote',
        'width' => 38,
        'height' => 32,
        'classes' => 'quote-mark',
    ]); ?>

    <blockquote class="quote-content"><?= wp_kses_post($quote) ?></blockquote>
    <?php if ($author !== '' || $source !== '') : ?>
        <figcaption class="quote-caption">
            <?php if ($has_image) : ?>
                <?php component('image', [
                    'image' => $image,
                    'size' => $args['image_size'],
                    'classes' => 'quote-image',
                ]); ?>
            <?php endif; ?>
            <div class="caption-content">
                <?php if ($author !== '') : ?><span class="quote-author"><?= esc_html($author) ?></span><?php endif; ?>
                <?php if ($source !== '') : ?>
                    <cite class="quote-source">
                        <?php if ($url !== '') : ?><a href="<?= $url ?>"><?= esc_html($source) ?></a><?php else : ?><?= esc_html($source) ?><?php endif; ?>
                    </cite>
                <?php endif; ?>
            </div>
        </figcaption>
    <?php endif; ?>
</figure>