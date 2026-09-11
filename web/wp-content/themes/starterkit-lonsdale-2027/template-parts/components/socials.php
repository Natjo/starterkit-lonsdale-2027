<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'items' => [],
    'aria_label' => '',
    'classes' => '',
    'attributes' => [],
]);
$items = is_array($args['items']) ? $args['items'] : [];
if ($items === []) return;

$to_string = static fn (mixed $value): string =>
    is_scalar($value) || $value instanceof Stringable ? trim((string) $value) : '';
$http_url = static function (mixed $value) use ($to_string): string {
    $url = $to_string($value);
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) return '';

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : '';
};
$catalog = [
    'facebook' => 'Facebook',
    'instagram' => 'Instagram',
    'linkedin' => 'LinkedIn',
    'x' => 'X',
    'youtube' => 'YouTube',
    'tiktok' => 'TikTok',
    'pinterest' => 'Pinterest',
    'threads' => 'Threads',
];
$normalized_items = [];
$seen = [];

foreach ($items as $item) {
    if (!is_array($item) || array_is_list($item)) continue;

    $network = strtolower($to_string($item['network'] ?? ''));
    $url = $http_url($item['url'] ?? '');
    if (!isset($catalog[$network]) || $url === '' || isset($seen[$network])) continue;

    $normalized_items[] = [
        'network' => $network,
        'label' => $catalog[$network],
        'url' => $url,
    ];
    $seen[$network] = true;
}

if ($normalized_items === []) return;

$aria_label = $to_string($args['aria_label']) ?: 'Réseaux sociaux';
$classes = component_classes('socials', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'aria-label']);
?>

<nav class="<?= $classes ?>" aria-label="<?= esc_attr($aria_label) ?>"<?= $attributes ?>>
    <ul class="socials-list">
        <?php foreach ($normalized_items as $item) : ?>
            <li class="<?= component_classes('socials-item', 'socials-item-' . $item['network'], 'has-icon') ?>">
                <a
                    class="socials-link"
                    href="<?= esc_url($item['url']) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="<?= esc_attr($item['label']) ?>"
                >
                    <?php component('icon', [
                        'name' => $item['network'],
                        'width' => 24,
                        'height' => 24,
                    ]); ?>
                    <span class="socials-label"><?= esc_html($item['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
