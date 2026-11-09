<?php
$args = get_template_args();

$instance = theme_pushes_more_instance_id();
$page_key = 'pushes_more_' . $instance;
$requested_page = filter_var($_GET[$page_key] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$page = $requested_page !== false ? min(1000, $requested_page) : 1;
$post_type = sanitize_key(theme_string($args['post_type'] ?? ''));
$configured_card = theme_string($args['card'] ?? '') ?: apply_filters('theme_content_type_card', $post_type, $post_type);
$card = resolve_card_name(sanitize_key(theme_string($configured_card)));
$limit = is_numeric($args['limit'] ?? null) ? (int) $args['limit'] : 3;
$limit = max(1, min(12, $limit));
$batch = $card !== '' ? theme_pushes_items($post_type, $limit, $page, true) : ['items' => [], 'has_more' => false];

$title = $args['title'] ?? '';
$text = $args['text'] ?? '';
$link = is_array($args['link'] ?? null) ? $args['link'] : [];
$has_header = theme_string($title) !== ''
    || theme_string($text) !== ''
    || (theme_string($link['url'] ?? '') !== '' && theme_string($link['title'] ?? '') !== '');
if (!$has_header && $batch['items'] === []) return;

$more_label = theme_string($args['more_label'] ?? '') ?: 'Voir plus';
$next_url = $batch['has_more'] ? add_query_arg($page_key, $page + 1) : '';
?>

<section <?= options('strate strate-pushes-more', $args) ?>
    data-module="strates/strate-pushes-more"
    data-context="@visible true"
    data-pushes-more-instance="<?= esc_attr((string) $instance) ?>">
    <div class="strate-inner">
        <?php component('header', [
            'title' => $title,
            'title_classes' => 'title-2 strate-title',
            'text' => $text,
            'text_classes' => 'rte strate-text',
            'link' => $link,
            'classes' => 'strate-header',
        ]); ?>

        <?php if ($batch['items'] !== []) : ?>
            <?php component('list', [
                'items' => $batch['items'],
                'card' => $card,
                'classes' => 'strate-list',
            ]); ?>
        <?php endif; ?>

        <?php if ($next_url !== '') : ?>
            <a class="pushes-more-next" href="<?= esc_url($next_url) ?>"><?= esc_html($more_label) ?></a>
        <?php endif; ?>
    </div>
</section>