<?php
/** Three top-level headings, each with its direct child links. */
final class Theme_Footer_Columns_Walker extends Walker_Nav_Menu
{
    public function walk($elements, $max_depth, ...$args)
    {
        $roots = [];
        foreach ($elements as $item) {
            if ((int) $item->menu_item_parent === 0 && count($roots) < 3) $roots[] = (int) $item->ID;
        }
        $elements = array_values(array_filter($elements, static fn ($item) => in_array((int) $item->ID, $roots, true) || in_array((int) $item->menu_item_parent, $roots, true)));
        return parent::walk($elements, 2, ...$args);
    }

    public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0)
    {
        if ($depth === 0) {
            $output .= '<li class="footer-column"><h2 class="footer-column-title">' . esc_html($data_object->title) . '</h2>';
            return;
        }
        $classes = static function ($classes, $item) use ($data_object) {
            if ($item !== $data_object) return $classes;
            return array_values(array_filter($classes, static fn ($class) => !is_string($class) || !preg_match('/^menu-item(?:$|-type-|-object-|-\d+$|-has-children$)/', $class)));
        };
        $id = static fn ($value, $item) => $item === $data_object ? '' : $value;
        add_filter('nav_menu_css_class', $classes, PHP_INT_MAX, 2);
        add_filter('nav_menu_item_id', $id, PHP_INT_MAX, 2);
        try { parent::start_el($output, $data_object, $depth, $args, $current_object_id); }
        finally {
            remove_filter('nav_menu_css_class', $classes, PHP_INT_MAX);
            remove_filter('nav_menu_item_id', $id, PHP_INT_MAX);
        }
    }
}
