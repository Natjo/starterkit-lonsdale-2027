<?php
$args = get_template_args();
$card_fields = $args['card'] ?? null;
if (is_array($card_fields) && !array_is_list($card_fields)) {
    $args = array_replace($card_fields, $args);
}
$link = theme_link($args['link'] ?? []);
$url = $link['url'];
$target = $link['target'];
$tag = $url !== '' ? 'a' : 'div';
?>

<article class="card-slide">
    <<?= $tag ?> <?php if ($url !== '') : ?> href="<?= esc_url($url) ?>" <?php if ($target !== '') : ?> target="<?= esc_attr($target) ?>" <?= $target === '_blank' ? ' rel="noopener noreferrer"' : '' ?><?php endif; ?><?php endif; ?>>
        <?php component('picture', [
            'image' => $args['image'] ?? null,
            'size' => 'large',
            'fit' => 'cover',
            'classes' => 'card-image',
        ]); ?>
        <div class="card-content">
            <?php component('title', [
                'title' => $args['title'] ?? '',
                'hx' => 3,
                'classes' => 'title-3',
            ]); ?>
            <?php component('text', [
                'text' => $args['text'] ?? '',
                'classes' => 'card-text',
            ]); ?>
        </div>
    </<?= $tag ?>>
</article>