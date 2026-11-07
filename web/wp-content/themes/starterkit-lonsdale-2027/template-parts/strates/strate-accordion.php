<?php
$args = get_template_args(['title' => '', 'text' => '', 'items' => []]);
$args['title'] = component_string($args['title']);
$args['text'] = component_string($args['text']);
$args['items'] = array_values(array_filter(is_array($args['items']) ? $args['items'] : [],
    static fn ($item): bool => is_array($item)
        && component_string($item['title'] ?? '') !== ''
        && component_string($item['text'] ?? '') !== ''));

if ($args['title'] === '' && $args['text'] === '' && $args['items'] === []) return;
?>

<section <?= options("strate strate-accordion", $args) ?>>
    <div class="strate-inner">
        <div class="strate-header">
            <?php
            component('title', [
                'title' => $args['title'] ?? '',
                'classes' => 'title-2 strate-title',
            ])
            ?>

            <?php component('text', [
                'text' => $args['text'],
                'classes' => 'rte strate-text',
            ]) ?>
        </div>

        <?php component('accordion', [
            'items' => $args['items'],
            'multiple' => false
        ]); ?>
    </div>
</section>