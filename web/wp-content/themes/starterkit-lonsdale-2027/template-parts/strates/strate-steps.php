<?php
$args = get_template_args();
$rows = is_array($args['steps'] ?? null) ? $args['steps'] : [];
$steps = [];

foreach ($rows as $row) {
    if (!is_array($row) || array_is_list($row)) continue;
    $title = component_string($row['title'] ?? '');
    if ($title === '') continue;

    $steps[] = [
        'title' => $title,
        'text' => $row['text'] ?? '',
        'link' => is_array($row['link'] ?? null) && !array_is_list($row['link']) ? $row['link'] : null,
    ];
}

if ($steps === []) return;
?>
<section <?= options('strate strate-steps', $args) ?>>
    <div class="strate-content">
        <?php component('header', [
            'title' => $args['title'] ?? '',
            'text' => $args['text'] ?? '',
            'classes' => 'strate-header',
        ]); ?>
        <ol class="steps-list">
            <?php foreach ($steps as $step) : ?>
                <li class="steps-item">
                    <?php component('title', ['title' => $step['title'], 'classes' => 'title-3 steps-title']); ?>
                    <?php component('text', ['text' => $step['text'], 'classes' => 'rte steps-text']); ?>
                    <?php if ($step['link'] !== null) component('link', ['link' => $step['link'], 'classes' => 'steps-link']); ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
