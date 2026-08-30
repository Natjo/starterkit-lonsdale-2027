<?php
// add styles in wysiwyg
add_filter('mce_css', 'wysiwyg_css');
function wysiwyg_css($mce_css)
{
    if (!empty($mce_css)) {
        $mce_css .= ',';
    }

    $path = THEME_DIR . 'assets/css/admin-rte.css';
    $url  = THEME_ASSETS . 'css/admin-rte.css';

    if (is_readable($path)) {
        $url .= '?ver=' . filemtime($path);
    }

    $mce_css .= $url;

    return $mce_css;
}

// Toolbar ACF « Light » : gras uniquement.
add_filter('acf/fields/wysiwyg/toolbars', 'theme_acf_wysiwyg_toolbars');
function theme_acf_wysiwyg_toolbars(array $toolbars): array
{
    $toolbars['Light'] = [];
    $toolbars['Light'][1] = ['bold'];

    return $toolbars;
}

// Classe sur le wrapper pour cibler le style admin (hauteur réduite).
add_filter('acf/load_field/type=wysiwyg', 'theme_acf_wysiwyg_light_wrapper');
function theme_acf_wysiwyg_light_wrapper(array $field): array
{
    if (($field['toolbar'] ?? '') !== 'light') {
        return $field;
    }

    $field['wrapper']['class'] = trim(($field['wrapper']['class'] ?? '') . ' acf-field-wysiwyg--light');

    return $field;
}

// Classe sur le body TinyMCE + config éditeur pour la toolbar Light.
add_action('acf/input/admin_enqueue_scripts', 'theme_acf_wysiwyg_tinymce_light_settings');
function theme_acf_wysiwyg_tinymce_light_settings(): void
{
    wp_add_inline_script(
        'acf-input',
        <<<'JS'
        acf.addFilter('wysiwyg_tinymce_settings', function (init, id, field) {
            if (field.get('toolbar') !== 'light') {
                return init;
            }

            init.body_class = ((init.body_class || '') + ' mce-toolbar-light').trim();
            init.wpautop = false;
            init.tadv_noautop = true;
            init.forced_root_block = false;
            init.force_br_newlines = true;
            init.force_p_newlines = false;
            init.remove_linebreaks = false;
            init.valid_elements = 'strong,br';
            init.extended_valid_elements = '';

            return init;
        });
        JS
    );
}

function theme_acf_is_wysiwyg_light_field(array $field): bool
{
    return ($field['toolbar'] ?? '') === 'light';
}

function theme_acf_wysiwyg_light_sanitize(string $value): string
{
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = preg_replace('/<\/p>\s*<p[^>]*>/i', '<br>', $value);
    $value = preg_replace('/<p[^>]*>/i', '', $value);
    $value = preg_replace('/<\/p>/i', '<br>', $value);
    $value = preg_replace('/<b\b([^>]*)>/i', '<strong$1>', $value);
    $value = preg_replace('/<\/b>/i', '</strong>', $value);
    $value = preg_replace('/\n+/', '<br>', $value);
    $value = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br>', $value);
    $value = preg_replace('/^(<br\s*\/?>\s*)+/i', '', $value);
    $value = preg_replace('/(<br\s*\/?>\s*)+$/i', '', $value);

    return wp_kses(trim($value), [
        'strong' => [],
        'br'     => [],
    ]);
}

add_filter('acf/update_value/type=wysiwyg', 'theme_acf_wysiwyg_light_update_value', 10, 3);
function theme_acf_wysiwyg_light_update_value($value, $post_id, array $field)
{
    if (!theme_acf_is_wysiwyg_light_field($field) || !is_string($value) || $value === '') {
        return $value;
    }

    return theme_acf_wysiwyg_light_sanitize($value);
}

add_filter('acf/load_value/type=wysiwyg', 'theme_acf_wysiwyg_light_load_value', 10, 3);
function theme_acf_wysiwyg_light_load_value($value, $post_id, array $field)
{
    if (!theme_acf_is_wysiwyg_light_field($field) || !is_string($value) || $value === '') {
        return $value;
    }

    return theme_acf_wysiwyg_light_sanitize($value);
}

add_filter('acf/format_value/type=wysiwyg', 'theme_acf_wysiwyg_light_format_value', 20, 3);
function theme_acf_wysiwyg_light_format_value($value, $post_id, array $field)
{
    if (!theme_acf_is_wysiwyg_light_field($field) || !is_string($value) || $value === '') {
        return $value;
    }

    return theme_acf_wysiwyg_light_sanitize($value);
}

// remove useless formats
add_filter('tiny_mce_before_init', 'wysiwyg_block_formats');
function wysiwyg_block_formats($init)
{
    $block_formats = array(
        'Niveau 1=h1',
        'Niveau 2=h2',
        'Niveau 3=h3',
        'Niveau 4=h4',
        'Paragraphe=p',
    );

    $init['block_formats'] = implode( ';', $block_formats );

    return $init;
}

// remove useless buttons
add_filter('mce_buttons', 'wysiwyg_override_buttons');
add_filter('mce_buttons_2', 'wysiwyg_override_buttons');
add_filter('mce_external_plugins', 'wysiwyg_override_buttons');
function wysiwyg_override_buttons($buttons) {
    foreach ($buttons as $idx => $id ) {
        if (
            stripos($id, 'numlist') !== false ||
            stripos($id, 'more') !== false ||
            stripos($id, 'wpadv') !== false ||
            stripos($id, 'strikethrough') !== false ||
            stripos($id, 'alignleft') !== false ||
            stripos($id, 'aligncenter') !== false ||
            stripos($id, 'alignright') !== false ||
            stripos($id, 'hr') !== false ||
            stripos($id, 'charmap') !== false ||
            stripos($id, 'wp_help') !== false ||
            stripos($id, 'forecolor') !== false ||
            stripos($id, 'indent') !== false ||
            stripos($id, 'outdent') !== false
        ) {
            unset($buttons[$idx]);
        }
    }
 
    return $buttons;
}

// styles
add_filter('tiny_mce_before_init', function($initArray) {
    $style_formats = array(  
        array(  
            'title' => 'Niveau 1',  
            'block' => 'p',  
            'classes' => 'h1',
            'remove_classes' => 'h2 h3 h4 h5 h6',
            'wrapper' => false,
            'exact' => true
            
        ),  
        array(  
            'title' => 'Niveau 2',  
            'block' => 'p',  
            'classes' => 'h2',
            'remove_classes' => 'h1 h3 h4 h5 h6',
            'wrapper' => false,
            'exact' => true
        ),
        array(  
            'title' => 'Niveau 3',  
            'block' => 'p',  
            'classes' => 'h3',
            'remove_classes' => 'h1 h2 h4 h5 h6',
            'wrapper' => false,
            'exact' => true
        ),
        array(  
            'title' => 'Niveau 4',  
            'block' => 'p',  
            'classes' => 'h4',
            'remove_classes' => 'h1 h2 h3 h5 h6',
            'wrapper' => false,
            'exact' => true
        ),

        array(  
            'title' => 'Texte (sm - 14px)',  
            'block' => 'p',  
            'classes' => 'text--sm',
            'remove_classes' => 'h1 h2 h3 h4 h5 h6',
            'wrapper' => false,
            'exact' => true
        ),

        array(  
            'title' => 'Texte (lg - 18px)',  
            'block' => 'p',  
            'classes' => 'text--lg',
            'remove_classes' => 'h1 h2 h3 h4 h5 h6',
            'wrapper' => false,
            'exact' => true
        ),

        array(  
            'title' => 'Texte (xl - 20px)',  
            'block' => 'p',  
            'classes' => 'text--xl',
            'remove_classes' => 'h1 h2 h3 h4 h5 h6',
            'wrapper' => false,
            'exact' => true
        ),

        array(  
            'title' => 'Texte (wd - 24px)',  
            'block' => 'p',  
            'classes' => 'text--wd',
            'remove_classes' => 'h1 h2 h3 h4 h5 h6',
            'wrapper' => false,
            'exact' => true
        ),
    );  

    $initArray['style_formats'] = wp_json_encode( $style_formats );
    
    $initArray['formats'] = wp_json_encode(array(
        'underline' => array('inline' => 'u', 'exact' => true)
    ));
    
    return $initArray;  
});  

add_filter('mce_buttons_2', function($buttons) {
    array_unshift($buttons, 'styleselect');
    return $buttons;
});

// add underline button
add_filter('mce_buttons', function($buttons) {
    $italic_pos = array_search('italic', $buttons);
    if ($italic_pos !== false) {
        array_splice($buttons, $italic_pos + 1, 0, 'underline');
    } else {
        $bold_pos = array_search('bold', $buttons);
        if ($bold_pos !== false) {
            array_splice($buttons, $bold_pos + 1, 0, 'underline');
        } else {
            array_unshift($buttons, 'underline');
        }
    }
    return $buttons;
});

// add fontselect
// add_filter('mce_buttons_2', function($buttons) {
//     array_unshift($buttons, 'fontselect');
//     return $buttons;
// });

// add_filter( 'tiny_mce_before_init', function($initArray) {
//     $initArray['font_formats'] = 'Font-1=font-1;Font-2=font-2';
//     return $initArray;
// });
