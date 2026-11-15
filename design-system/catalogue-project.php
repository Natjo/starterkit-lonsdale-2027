<?php
/** Project-only examples derived from present templates, manifests and local ACF JSON. */
$project_catalogue = json_decode((string) file_get_contents(__DIR__ . '/docs/catalogue.json'), true);
$project_catalogue = is_array($project_catalogue) ? $project_catalogue : [];
function theme_design_system_project_defaults(array $fields, string $image, int $depth = 0): array
{
    if ($depth > 8) return [];
    $values = [];
    foreach ($fields as $field) {
        $name = $field['name'] ?? '';
        if (!is_string($name) || $name === '' || in_array($field['type'] ?? '', ['tab', 'message', 'accordion'], true)) continue;
        $type = $field['type'] ?? '';
        $default = $field['default_value'] ?? null;
        $values[$name] = match ($type) {
            'group' => theme_design_system_project_defaults($field['sub_fields'] ?? [], $image, $depth + 1),
            'repeater' => array_fill(0, min(3, max(1, (int) ($field['min'] ?? 1))), theme_design_system_project_defaults($field['sub_fields'] ?? [], $image, $depth + 1)),
            'image' => $image,
            'gallery' => [$image, $image],
            'wysiwyg' => is_string($default) && $default !== '' ? $default : '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
            'textarea', 'text' => is_scalar($default) && (string) $default !== '' ? $default : ($name === 'title' ? 'Un exemple éditorial' : 'Lorem ipsum dolor sit amet'),
            'link' => ['url' => '#main-content', 'title' => 'Découvrir'],
            'number', 'range' => is_numeric($default) ? $default : 42,
            'select', 'radio', 'button_group' => $default ?? array_key_first($field['choices'] ?? []) ?? '',
            'true_false' => (bool) $default,
            'date_picker' => '20261110',
            default => $default ?? '',
        };
    }
    return $values;
}
function theme_design_system_project_acf(string $family, string $slug, string $image): array
{
    static $groups = null;
    if ($groups === null) {
        $groups = [];
        foreach (glob(get_template_directory() . '/acf-json/*.json') ?: [] as $path) {
            $group = json_decode((string) file_get_contents($path), true);
            if (is_array($group)) $groups[] = $group;
        }
    }
    foreach ($groups as $group) {
        $key = str_replace('_', '-', (string) ($group['key'] ?? ''));
        if (($family === 'cards' && $key === 'group-card-' . $slug) || ($family === 'heros' && $key === 'group-hero-' . $slug)) {
            $fields = $group['fields'][0]['sub_fields'] ?? $group['fields'] ?? [];
            return theme_design_system_project_defaults($fields, $image);
        }
        if ($family === 'strates') {
            foreach ($group['fields'] ?? [] as $field) {
                foreach ($field['layouts'] ?? [] as $layout) {
                    if (($layout['name'] ?? '') === $slug) return theme_design_system_project_defaults($layout['sub_fields'] ?? [], $image);
                }
            }
        }
    }
    return [];
}
function theme_design_system_project_urls(mixed $value): mixed
{
    if (is_array($value)) return array_map('theme_design_system_project_urls', $value);
    if (is_string($value) && str_starts_with($value, 'img/')) return THEME_ASSETS . $value;
    return $value;
}
$project_examples = [];
foreach (['components', 'cards', 'strates', 'heros'] as $family_name) {
    $project_examples[$family_name] = [];
    foreach ($project_catalogue[$family_name] ?? [] as $slug => $definition) {
        if (!preg_match('/^[a-z0-9_-]+$/i', $slug)) continue;
        $prefix = match ($family_name) { 'cards' => 'card-', 'strates' => 'strate-', 'heros' => 'hero-', default => '' };
        if (!is_file(get_template_directory() . '/template-parts/' . $family_name . '/' . $prefix . $slug . '.php')) continue;
        $defaults = is_array($definition['defaults'] ?? null) ? $definition['defaults'] : [];
        $project_examples[$family_name][$slug] = array_replace_recursive(
            $catalogue[$family_name][$slug] ?? $base,
            theme_design_system_project_acf($family_name, $slug, $image_desktop),
            theme_design_system_project_urls($defaults)
        );
    }
}
$catalogue = array_replace($catalogue, $project_examples);

// Use an enabled project content type for automatic listing demonstrations.
$types = array_values(array_diff(get_post_types(['public' => true], 'names'), ['attachment', 'page', 'post']));
$sample_type = in_array('news', $types, true) ? 'news' : ($types[0] ?? (post_type_exists('post') ? 'post' : 'page'));
$sample_ids = get_posts(['post_type' => $sample_type, 'post_status' => 'publish', 'posts_per_page' => 6, 'fields' => 'ids']);
foreach (['pushes', 'pushes-auto', 'pushes-more', 'pushes-slider'] as $listing) {
    if (!isset($catalogue['strates'][$listing])) continue;
    $catalogue['strates'][$listing]['post_type'] = $sample_type;
    if ($listing === 'pushes') $catalogue['strates'][$listing]['items'] = $sample_ids;
}
