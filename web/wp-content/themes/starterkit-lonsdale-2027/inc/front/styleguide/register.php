<?php

// Les modèles internes sont trop imbriqués pour la découverte automatique.
add_filter('theme_page_templates', static function (array $templates): array {
    foreach (['styleguide.php' => 'Composants', 'cards.php' => 'Cards', 'strates.php' => 'Strates', 'heros.php' => 'Heroes', 'styles.php' => 'Styles'] as $file => $label) {
        $templates['inc/front/styleguide/' . $file] = 'Styleguide — ' . $label;
    }
    return $templates;
});

add_filter('body_class', static function (array $classes): array {
    if (str_starts_with(get_page_template_slug(), 'inc/front/styleguide/')) {
        $classes[] = 'page-template-styleguide';
    }
    return array_values(array_unique($classes));
});
