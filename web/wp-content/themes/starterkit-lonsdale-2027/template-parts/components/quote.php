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
$quote = component_string($args['quote']);
$author = component_string($args['author']);
$source = component_string($args['source']);
$url = esc_url(component_string($args['url']));
$image = $args['image'];
$has_image = is_scalar($image) || $image instanceof Stringable
    ? trim((string) $image) !== ''
    : false;

if ($quote === '') return;

$classes = component_classes('quote', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class']);
?>

<figure class="<?= $classes ?>"<?= $attributes ?>>
    <?php if ($has_image) : ?>
        <?php component('image', [
            'image' => $image,
            'size' => $args['image_size'],
            'classes' => 'quote-image',
        ]); ?>
    <?php endif; ?>

    <blockquote class="quote-content"><?= wp_kses_post($quote) ?></blockquote>
    <?php if ($author !== '' || $source !== '') : ?>
        <figcaption class="quote-caption">
            <?php if ($author !== '') : ?><span class="quote-author"><?= esc_html($author) ?></span><?php endif; ?>
            <?php if ($source !== '') : ?>
                <cite class="quote-source">
                    <?php if ($url !== '') : ?><a href="<?= $url ?>"><?= esc_html($source) ?></a><?php else : ?><?= esc_html($source) ?><?php endif; ?>
                </cite>
            <?php endif; ?>
        </figcaption>
    <?php endif; ?>
</figure>
