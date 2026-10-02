<?php

/** Wrap child menu lists and omit technical WordPress item classes. */
final class Theme_Header_Nav_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0)
    {
        $filter = static function ($classes, $item) use ($data_object): array {
            if ($item !== $data_object) return $classes;

            return array_values(array_filter($classes, static fn ($class): bool =>
                !is_string($class) || !preg_match('/^menu-item(?:$|-type-|-object-|-\d+$|-has-children$)/', $class)
            ));
        };
        $id_filter = static fn ($id, $item) => $item === $data_object ? '' : $id;

        add_filter('nav_menu_css_class', $filter, PHP_INT_MAX, 2);
        add_filter('nav_menu_item_id', $id_filter, PHP_INT_MAX, 2);
        try {
            parent::start_el($output, $data_object, $depth, $args, $current_object_id);
        } finally {
            remove_filter('nav_menu_css_class', $filter, PHP_INT_MAX);
            remove_filter('nav_menu_item_id', $id_filter, PHP_INT_MAX);
        }
    }

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $output .= '<div class="wrapper-sub-menu">';
        parent::start_lvl($output, $depth, $args);
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        parent::end_lvl($output, $depth, $args);
        $output .= '</div>';
    }
}
