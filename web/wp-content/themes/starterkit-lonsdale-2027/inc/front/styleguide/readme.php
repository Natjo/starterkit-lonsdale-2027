<?php

/** Rend le README d’un module, converti en HTML par Rspack. */
function theme_styleguide_readme(string $component, string $family = 'components'): string
{
    if (!in_array($family, ['components', 'cards', 'strates', 'heros'], true)
        || ($component !== '' && !preg_match('/^[a-z0-9-]+$/', $component))) return '';

    $path = get_template_directory() . '/assets/styleguide/' . $family . ($component !== '' ? '/' . $component : '') . '/README.html';
    $html = is_readable($path) ? file_get_contents($path) : false;

    return is_string($html) && trim($html) !== ''
        ? wp_kses_post($html)
        : '<p>Documentation indisponible. Relancer le build des assets.</p>';
}
