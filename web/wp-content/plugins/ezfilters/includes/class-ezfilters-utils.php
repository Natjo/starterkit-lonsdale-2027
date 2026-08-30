<?php

class EZFiltersUtils
{
    private static $slug_cache = [];

    public static function toSlug($string)
    {
        if (isset(self::$slug_cache[$string])) {
            return self::$slug_cache[$string];
        }

        $res = sanitize_title($string);
        self::$slug_cache[$string] = $res;
        return $res;
    }

    public static function getPostType()
    {
        $post_type = get_post_type();

        if (empty($post_type) && is_home()) {
            $post_type = 'post';
        } elseif (empty($post_type)) {
            global $wp_query;
            $post_type = $wp_query->query['post_type'];
        }

        return $post_type;
    }

    public static function sanitizePostData($key, $default = [])
    {
        if (!isset($_POST[$key])) return $default;
        $arr = wp_unslash($_POST[$key]);
        if (is_array($arr)) {
            $arr = array_filter($arr, fn($v) => $v !== '');
            return array_map('sanitize_text_field', $arr);
        }
        return sanitize_text_field($arr);
    }

    public static function sanitizeGetData($key, $default = '')
    {
        if (!isset($_GET[$key]) || $_GET[$key] === '') return $default;
        return sanitize_text_field(wp_unslash($_GET[$key]));
    }

    public static function validatePaged($paged)
    {
        $paged = absint($paged);
        return is_numeric($paged) ? $paged : 1;
    }

    public static function getFilterSlug($filter)
    {
        $slug = $filter['taxonomy'];
        if ($filter['type'] == "acf") $slug = $filter['field'];
        elseif ($filter['type'] == "keywords") $slug = "keywords";
        elseif ($filter['type'] == "relation") $slug = $filter['field'];

        return $slug;
    }
} 