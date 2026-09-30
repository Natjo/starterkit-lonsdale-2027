<?php
$args = get_template_args();
$rows = is_array($args['logos'] ?? null) ? $args['logos'] : [];
$logos = [];

foreach ($rows as $row) {
    if (!is_array($row) || array_is_list($row) || empty($row['image'])) continue;
    $logos[] = [
        'image' => $row['image'],
        'name' => component_string($row['name'] ?? ''),
        'link' => is_array($row['link'] ?? null) && !array_is_list($row['link']) ? $row['link'] : null,
    ];
}

if ($logos === []) return;
?>
<section <?= options('strate strate-logos', $args) ?>>
    <div class="strate-content">
        <?php component('header', [
            'title' => $args['title'] ?? '',
            'text' => $args['text'] ?? '',
            'classes' => 'strate-header',
        ]); ?>
        <ul class="logos-list">
            <?php foreach ($logos as $logo) : ?>
                <li class="logos-item">
                    <?php
                    $link = $logo['link'] !== null ? normalize_action_link($logo['link']) : null;
                    $has_link = $link !== null && !$link['disabled'] && $link['url'] !== '';
                    ?>
                    <?php if ($has_link) : ?>
                        <a class="logos-link" href="<?= $link['url'] ?>"<?= $link['target'] !== '' ? ' target="' . esc_attr($link['target']) . '"' : '' ?><?= $link['rel'] !== '' ? ' rel="' . esc_attr($link['rel']) . '"' : '' ?>>
                    <?php endif; ?>
                    <?php
                    $picture = [
                        'image' => $logo['image'],
                        'size' => 'medium',
                        'classes' => 'logos-image',
                    ];
                    $alt = $logo['name'] !== '' ? $logo['name'] : ($has_link ? $link['title'] : '');
                    if ($alt !== '') $picture['alt'] = $alt;
                    component('picture', $picture);
                    ?>
                    <?php if ($has_link) : ?></a><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
