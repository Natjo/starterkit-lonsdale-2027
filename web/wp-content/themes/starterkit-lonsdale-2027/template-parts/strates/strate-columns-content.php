<?php
$args = get_template_args();
$columns = is_array($args['columns'] ?? null) ? $args['columns'] : [];
$columns = array_values(array_filter(array_slice($columns, 0, 3), static function (mixed $column): bool {
    if (!is_array($column) || array_is_list($column)) return false;

    $title = is_scalar($column['title'] ?? null) || ($column['title'] ?? null) instanceof Stringable
        ? trim((string) $column['title'])
        : '';
    $text = is_scalar($column['text'] ?? null) || ($column['text'] ?? null) instanceof Stringable
        ? trim((string) $column['text'])
        : '';

    return $title !== '' || $text !== '' || !empty($column['image']);
}));

if ($columns === []) return;
?>

<section <?= options(['strate', 'strate-columns-content', 'columns-' . count($columns)], $args) ?>>
    <div class="strate-content">
        <?php foreach ($columns as $column) : ?>
            <article class="content-column">
                <?php component('picture', [
                    'image' => $column['image'] ?? null,
                    'size' => '730_auto',
                    'classes' => 'content-column-media',
                ]); ?>

                <?php component('title', [
                    'title' => $column['title'] ?? '',
                    'classes' => 'title-3 content-column-title',
                ]); ?>

                <?php component('text', [
                    'text' => $column['text'] ?? '',
                    'classes' => 'rte content-column-text',
                ]); ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
