<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$args = normalize_args($params, [
    'list' => [],
    'url' => '',
    'title' => '',
    'subject' => '',
    'aria_label' => 'Partager',
    'copied_label' => 'Lien copié',
    'copy_error_label' => 'Impossible de copier le lien',
    'classes' => '',
    'attributes' => [],
]);
$list = is_array($args['list']) ? $args['list'] : [];
if ($list === []) return;

$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';
$normalize_http_url = static function (mixed $value) use ($to_string): string {
    $url = $to_string($value);
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) return '';

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : '';
};
$normalize_email_href = static function (mixed $value) use ($to_string): string {
    $value = $to_string($value);
    if ($value === '') return '';

    if (str_starts_with(strtolower($value), 'mailto:')) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false ? $value : '';
    }

    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? 'mailto:' . $value : '';
};
$current_url = static function () use ($normalize_http_url): string {
    if (function_exists('get_permalink') && function_exists('is_singular') && is_singular()) {
        $permalink = $normalize_http_url(get_permalink());
        if ($permalink !== '') return $permalink;
    }

    $uri = function_exists('wp_unslash')
        ? wp_unslash((string) ($_SERVER['REQUEST_URI'] ?? '/'))
        : (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($uri, PHP_URL_PATH);
    $query = parse_url($uri, PHP_URL_QUERY);
    $path = is_string($path) && $path !== '' ? $path : '/';
    $query = is_string($query) && $query !== '' ? '?' . $query : '';

    return function_exists('home_url') ? $normalize_http_url(home_url($path . $query)) : '';
};

$url = $normalize_http_url($args['url']) ?: $current_url();
if ($url === '') return;

$encoded_url = rawurlencode($url);
$subject = $to_string($args['subject']);
$email_query = [];
if ($subject !== '') $email_query[] = 'subject=' . rawurlencode($subject);
$email_query[] = 'body=' . $encoded_url;

$catalog = [
    'email' => [
        'icon' => 'email',
        'href' => 'mailto:?' . implode('&', $email_query),
        'label' => 'Partager par e-mail',
    ],
    'copy' => [
        'icon' => 'copy',
        'href' => $url,
        'label' => 'Copier le lien',
    ],
    'facebook' => [
        'icon' => 'facebook',
        'href' => 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_url,
        'label' => 'Partager sur Facebook',
    ],
    'linkedin' => [
        'icon' => 'linkedin',
        'href' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $encoded_url,
        'label' => 'Partager sur LinkedIn',
    ],
    'x' => [
        'icon' => 'x',
        'href' => 'https://twitter.com/intent/tweet?url=' . $encoded_url,
        'label' => 'Partager sur X',
    ],
    'whatsapp' => [
        'icon' => 'whatsapp',
        'href' => 'https://wa.me/?text=' . $encoded_url,
        'label' => 'Partager sur WhatsApp',
    ],
];

$items = [];
$seen = [];
$is_assoc = !array_is_list($list);

foreach ($list as $key => $value) {
    $definition = [];

    if ($is_assoc) {
        $name = is_string($key) ? strtolower(trim($key)) : '';
        if ($value === false || $value === null) continue;
        if (is_array($value)) {
            $definition = $value;
        } elseif (is_string($value)) {
            $definition = ['href' => $value];
        }
    } elseif (is_string($value)) {
        $name = strtolower(trim($value));
    } elseif (is_array($value)) {
        $definition = $value;
        $name = strtolower($to_string($value['name'] ?? ''));
    } else {
        continue;
    }

    if ($name === '' || isset($seen[$name]) || !isset($catalog[$name])) continue;

    $item = ['name' => $name] + $catalog[$name];
    $has_custom_href = array_key_exists('href', $definition);
    if ($has_custom_href) {
        $custom_href = $definition['href'];
        $item['href'] = $name === 'email'
            ? $normalize_email_href($custom_href)
            : $normalize_http_url($custom_href);
        if ($item['href'] === '') continue;
    }

    $custom_label = $to_string($definition['label'] ?? '');
    $custom_icon = $to_string($definition['icon'] ?? '');
    if ($custom_label !== '') $item['label'] = $custom_label;
    if ($custom_icon !== '') $item['icon'] = $custom_icon;

    $seen[$name] = true;
    $items[] = $item;
}

if ($items === []) return;

$title = $to_string($args['title']);
$title_id = function_exists('wp_unique_id') ? wp_unique_id('shares-title-') : uniqid('shares-title-', false);
$aria_label = $to_string($args['aria_label']) ?: 'Partager';
$classes = component_classes('shares', $args['classes']);
$attributes = component_attributes($args['attributes'], [
    'class',
    'aria-label',
    'aria-labelledby',
    'data-context',
    'data-module',
]);
$copied_label = $to_string($args['copied_label']) ?: 'Lien copié';
$copy_error_label = $to_string($args['copy_error_label']) ?: 'Impossible de copier le lien';
?>
<nav class="<?= $classes ?>"<?= $title !== '' ? ' aria-labelledby="' . esc_attr($title_id) . '"' : ' aria-label="' . esc_attr($aria_label) . '"' ?> data-context="@visible true" data-module="components/shares"<?= $attributes ?>>
    <?php if ($title !== '') : ?>
        <div class="shares-title" id="<?= esc_attr($title_id) ?>"><?= esc_html($title) ?></div>
    <?php endif; ?>

    <ul class="shares-list">
        <?php foreach ($items as $item) :
            $name = $item['name'];
            $href = $item['href'];
            $label = $item['label'];
            $icon = $item['icon'];
        ?>
            <li class="shares-item">
                <?php if ($name === 'copy') : ?>
                    <button
                        type="button"
                        class="shares-action"
                        data-share-copy
                        data-url="<?= esc_attr($href) ?>"
                        data-success="<?= esc_attr($copied_label) ?>"
                        data-error="<?= esc_attr($copy_error_label) ?>"
                    >
                        <?php component('icon', ['name' => $icon, 'width' => 40, 'height' => 40]); ?>
                        <span class="sr-only"><?= esc_html($label) ?></span>
                        <span class="shares-tip" aria-hidden="true" data-copy-tip></span>
                        <span class="sr-only" role="status" aria-live="polite" aria-atomic="true" data-copy-status></span>
                    </button>
                <?php else : ?>
                    <a
                        class="shares-action"
                        href="<?= esc_url($href) ?>"
                        <?php if ($name !== 'email') : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                    >
                        <?php component('icon', ['name' => $icon, 'width' => 40, 'height' => 40]); ?>
                        <span class="sr-only"><?= esc_html($label) ?></span>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
