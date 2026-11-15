<?php

/** Rend le README d’un module, converti en HTML par le renderer embarqué de Forge. */
function theme_design_system_readme(string $component, string $family = 'components'): string
{
    if (!in_array($family, ['components', 'cards', 'strates', 'heros', 'common', 'modules'], true)
        || ($component !== '' && !preg_match('/^[a-z0-9_-]+$/i', $component))) return '';

    $path = __DIR__ . '/docs/' . $family . ($component !== '' ? '/' . $component : '') . '/README.html';
    $html = is_readable($path) ? file_get_contents($path) : false;

    return is_string($html) && trim($html) !== ''
        ? wp_kses_post($html)
        : '<p>Documentation indisponible. Relancer le build des assets.</p>';
}

/** Métadonnées générées depuis les manifestes, lues une fois par requête. */
function theme_design_system_catalogue(): array
{
    static $catalogue = null;
    if ($catalogue === null) {
        $path = __DIR__ . '/docs/catalogue.json';
        $data = is_readable($path) ? json_decode(file_get_contents($path), true) : [];
        $catalogue = is_array($data) ? $data : [];
    }
    return $catalogue;
}

/** Version from the current preview's own catalogue, shared by all examples. */
function theme_design_system_version(string $slug, string $family): string
{
    $version = theme_design_system_catalogue()[$family][$slug]['version'] ?? '';
    return is_string($version) && trim($version) !== '' ? 'Version ' . trim($version) : 'Version non renseignée';
}

/** Nombre initial de colonnes de démonstration : entier de 1 à 3. */
function theme_design_system_card_columns(string $slug): int
{
    $columns = theme_design_system_catalogue()['cards'][$slug]['designSystem']['columns'] ?? 3;
    return is_int($columns) && $columns >= 1 && $columns <= 3 ? $columns : 3;
}
