<?php
/** Actual PHP availability is the authority, including projects in Clean mode. */
$path = __DIR__ . '/docs/catalogue.json';
$data = is_readable($path) ? json_decode(file_get_contents($path), true) : [];
$items = [];
foreach (is_array($data['common'] ?? null) ? $data['common'] : [] as $slug => $definition) {
    if (!is_string($slug) || !preg_match('/^[a-z0-9_-]+$/i', $slug)) continue;
    if (!is_file(get_template_directory() . '/template-parts/common/' . $slug . '.php')) continue;
    $items[$slug] = is_array($definition) ? $definition : [];
}
return $items;
