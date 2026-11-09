<?php

// WordPress ne découvre pas automatiquement les modèles imbriqués à ce niveau.
add_filter('theme_page_templates', static function (array $templates): array {
    $templates['inc/front/styleguide/styleguide.php'] = 'Styleguide';

    return $templates;
});
