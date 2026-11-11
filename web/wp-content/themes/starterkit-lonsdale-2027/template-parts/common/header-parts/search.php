        <?php if (!defined('THEME_ENABLE_SEARCH') || THEME_ENABLE_SEARCH) component('search', [
            'value' => get_search_query(), 'action' => home_url('/'),
            'label' => __('Rechercher sur le site', 'theme'),
            'placeholder' => __('Rechercher', 'theme'), 'submit_label' => __('Rechercher', 'theme'),
            'classes' => 'header-search',
        ]); ?>
