<?php

/**
 * Construit le contexte d'une page de filtres depuis une configuration explicite.
 *
 * @return array<string, mixed>
 */
function theme_build_filters_context(array $options = []): array
{
    $to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
        ? trim((string) $value)
        : '';
    $request = is_array($options['request'] ?? null) ? $options['request'] : $_GET;
    $requested_post_type = sanitize_key($to_string($options['post_type'] ?? ''));

    if ($requested_post_type === '') {
        $query_post_type = get_query_var('post_type');
        $query_post_type = is_array($query_post_type) ? reset($query_post_type) : $query_post_type;
        $requested_post_type = is_string($query_post_type) ? sanitize_key($query_post_type) : '';
    }

    $post_type_object = $requested_post_type !== '' ? get_post_type_object($requested_post_type) : null;
    $post_type = $post_type_object instanceof WP_Post_Type ? $requested_post_type : '';
    $archive_url = $post_type !== '' ? get_post_type_archive_link($post_type) : '';
    $archive_url = is_string($archive_url) ? $archive_url : '';
    $explicit_action = $to_string($options['action'] ?? '');
    $queried_object_id = max(0, (int) get_queried_object_id());
    $queried_permalink = $queried_object_id > 0 ? get_permalink($queried_object_id) : '';
    $queried_permalink = is_string($queried_permalink) ? $queried_permalink : '';
    $action_url = $explicit_action !== ''
        ? $explicit_action
        : ($archive_url !== '' ? $archive_url : $queried_permalink);
    $action_url = $action_url !== '' ? $action_url : home_url('/');
    $reset_url = $to_string($options['reset_url'] ?? '') ?: $action_url;
    $post_type_label = trim((string) ($post_type_object?->labels->name ?? 'contenus'));
    $singular_label = trim((string) ($post_type_object?->labels->singular_name ?? 'contenu'));

    $search_options = is_array($options['search'] ?? null) && !array_is_list($options['search'])
        ? $options['search']
        : [];
    $search_enabled = ($options['search'] ?? true) !== false;
    $search_name = sanitize_key($to_string($search_options['name'] ?? 'filter_search')) ?: 'filter_search';
    $search_acf_option = $search_options['acf_fields'] ?? [];
    $search_acf_option = is_array($search_acf_option) ? $search_acf_option : [$search_acf_option];
    $search_acf_fields = array_values(array_unique(array_filter(array_map(
        static fn($field): string => is_scalar($field) && preg_match('/^[A-Za-z0-9_.:-]+$/', trim((string) $field)) === 1
            ? trim((string) $field)
            : '',
        $search_acf_option
    ))));
    $search_value = $search_enabled && isset($request[$search_name]) && is_scalar($request[$search_name])
        ? sanitize_text_field(wp_unslash((string) $request[$search_name]))
        : '';
    $filter_items = [];
    $filter_groups = [
        'search' => [],
        'taxonomies' => [],
        'acf' => [],
        'sort' => [],
    ];

    if ($search_enabled) {
        $search_item = [
            'type' => 'search',
            'name' => $search_name,
            'label' => $to_string($search_options['label'] ?? '') ?: 'Rechercher',
            'value' => $search_value,
            'placeholder' => $to_string($search_options['placeholder'] ?? ''),
            'autocomplete' => $to_string($search_options['autocomplete'] ?? 'off'),
        ];
        $filter_items[] = $search_item;
        $filter_groups['search'][] = $search_item;
    }

    $taxonomy_option = $options['taxonomies'] ?? true;
    $allowed_taxonomies = is_array($taxonomy_option)
        ? array_values(array_filter(array_map(static fn($value): string => sanitize_key((string) $value), $taxonomy_option)))
        : [];
    $taxonomy_filters = [];
    $tax_query = [];

    if ($post_type !== '' && $taxonomy_option !== false) {
        foreach (get_object_taxonomies($post_type, 'objects') as $taxonomy) {
            if (!$taxonomy instanceof WP_Taxonomy || !$taxonomy->public) continue;
            if ($allowed_taxonomies !== [] && !in_array($taxonomy->name, $allowed_taxonomies, true)) continue;

            $terms = get_terms([
                'taxonomy' => $taxonomy->name,
                'hide_empty' => (bool) ($options['hide_empty_terms'] ?? true),
            ]);
            if (is_wp_error($terms) || $terms === []) continue;

            $requested_values = $request[$taxonomy->name] ?? [];
            $requested_values = is_array($requested_values) ? $requested_values : [$requested_values];
            $requested_values = array_values(array_unique(array_filter(array_map(
                static fn($value): string => is_scalar($value)
                    ? sanitize_title(wp_unslash((string) $value))
                    : '',
                $requested_values
            ))));
            $term_slugs = array_map(static fn(WP_Term $term): string => $term->slug, $terms);
            $selected_values = array_values(array_intersect($requested_values, $term_slugs));

            if ($selected_values !== []) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy->name,
                    'field' => 'slug',
                    'terms' => $selected_values,
                    'operator' => 'IN',
                ];
            }

            $taxonomy_filters[] = [
                'type' => 'taxonomy',
                'name' => $taxonomy->name,
                'label' => $taxonomy->labels->name,
                'value' => $selected_values,
                'options' => array_map(static fn(WP_Term $term): array => [
                    'value' => $term->slug,
                    'label' => $term->name,
                ], $terms),
            ];
        }
    }

    $filter_items = array_merge($filter_items, $taxonomy_filters);
    $filter_groups['taxonomies'] = $taxonomy_filters;
    $acf_filters = is_array($options['acf_filters'] ?? null) && array_is_list($options['acf_filters'])
        ? $options['acf_filters']
        : [];
    $meta_query = [];

    foreach ($acf_filters as $acf_filter) {
        if (!is_array($acf_filter) || array_is_list($acf_filter)) continue;

        $name = sanitize_key($to_string($acf_filter['name'] ?? ''));
        $field = $to_string($acf_filter['field'] ?? '');
        $control = strtolower($to_string($acf_filter['control'] ?? 'search'));
        $allowed_controls = [
            'text', 'search', 'select', 'checkbox', 'checkboxes', 'radios',
            'number', 'date', 'date_range', 'number_range',
        ];
        if ($name === '' || !preg_match('/^[A-Za-z0-9_.:-]+$/', $field)) continue;
        if (!in_array($control, $allowed_controls, true)) $control = 'search';

        $raw_value = $request[$name] ?? '';
        $value = '';
        $is_active = false;

        if ($control === 'checkboxes') {
            $raw_values = is_array($raw_value) ? $raw_value : [$raw_value];
            $value = array_values(array_unique(array_filter(array_map(
                static fn($item): string => is_scalar($item)
                    ? sanitize_text_field(wp_unslash((string) $item))
                    : '',
                $raw_values
            ))));
            $is_active = $value !== [];
        } elseif (in_array($control, ['date_range', 'number_range'], true)) {
            $raw_range = is_array($raw_value) && !array_is_list($raw_value) ? $raw_value : [];
            $from_key = $control === 'date_range' ? 'from' : 'min';
            $to_key = $control === 'date_range' ? 'to' : 'max';
            $value = [
                $from_key => isset($raw_range[$from_key]) && is_scalar($raw_range[$from_key])
                    ? sanitize_text_field(wp_unslash((string) $raw_range[$from_key]))
                    : '',
                $to_key => isset($raw_range[$to_key]) && is_scalar($raw_range[$to_key])
                    ? sanitize_text_field(wp_unslash((string) $raw_range[$to_key]))
                    : '',
            ];
            $is_active = $value[$from_key] !== '' || $value[$to_key] !== '';
        } elseif ($control === 'checkbox') {
            $input_value = $to_string($acf_filter['input_value'] ?? '1') ?: '1';
            $submitted_value = is_scalar($raw_value)
                ? sanitize_text_field(wp_unslash((string) $raw_value))
                : '';
            $is_active = hash_equals($input_value, $submitted_value);
            $value = $is_active ? $input_value : '';
        } else {
            $value = is_scalar($raw_value)
                ? sanitize_text_field(wp_unslash((string) $raw_value))
                : '';
            $is_active = $value !== '';
        }

        $acf_item = [
            'type' => 'acf',
            'control' => $control,
            'name' => $name,
            'label' => $to_string($acf_filter['label'] ?? ''),
            'value' => $value,
            'options' => is_array($acf_filter['options'] ?? null) ? $acf_filter['options'] : [],
            'placeholder' => $to_string($acf_filter['placeholder'] ?? ''),
            'autocomplete' => $to_string($acf_filter['autocomplete'] ?? 'off'),
            'from_label' => $to_string($acf_filter['from_label'] ?? ''),
            'to_label' => $to_string($acf_filter['to_label'] ?? ''),
            'min' => $acf_filter['min'] ?? null,
            'max' => $acf_filter['max'] ?? null,
        ];
        $filter_items[] = $acf_item;
        $filter_groups['acf'][] = $acf_item;

        if (!$is_active) continue;

        if (in_array($control, ['date_range', 'number_range'], true)) {
            $from_key = $control === 'date_range' ? 'from' : 'min';
            $to_key = $control === 'date_range' ? 'to' : 'max';
            $meta_type = $control === 'date_range' ? 'DATE' : 'NUMERIC';
            if ($value[$from_key] !== '') {
                $meta_query[] = [
                    'key' => $field,
                    'value' => $value[$from_key],
                    'compare' => '>=',
                    'type' => $meta_type,
                ];
            }
            if ($value[$to_key] !== '') {
                $meta_query[] = [
                    'key' => $field,
                    'value' => $value[$to_key],
                    'compare' => '<=',
                    'type' => $meta_type,
                ];
            }
            continue;
        }

        if ($control === 'checkboxes') {
            $value_relation = strtoupper($to_string($acf_filter['value_relation'] ?? 'OR'));
            $value_clauses = ['relation' => $value_relation === 'AND' ? 'AND' : 'OR'];
            foreach ($value as $selected_value) {
                $value_clauses[] = [
                    'key' => $field,
                    'value' => $selected_value,
                    'compare' => 'LIKE',
                ];
            }
            $meta_query[] = $value_clauses;
            continue;
        }

        $allowed_compares = ['=', '!=', 'LIKE', 'NOT LIKE', '>', '>=', '<', '<='];
        $default_compare = in_array($control, ['text', 'search'], true) ? 'LIKE' : '=';
        $compare = strtoupper($to_string($acf_filter['compare'] ?? $default_compare));
        $compare = in_array($compare, $allowed_compares, true) ? $compare : $default_compare;
        $stored_value = $control === 'checkbox'
            ? $to_string($acf_filter['stored_value'] ?? $value)
            : $value;
        $meta_query[] = [
            'key' => $field,
            'value' => $stored_value,
            'compare' => $compare,
            'type' => in_array($control, ['number'], true) ? 'NUMERIC' : 'CHAR',
        ];
    }

    $sort_config = is_array($options['sort'] ?? null) && !array_is_list($options['sort'])
        ? $options['sort']
        : [];
    $sort_name = sanitize_key($to_string($sort_config['name'] ?? 'filter_sort')) ?: 'filter_sort';
    $sort_default = sanitize_key($to_string($sort_config['default'] ?? 'date_desc')) ?: 'date_desc';
    $sort_options = is_array($sort_config['options'] ?? null) ? $sort_config['options'] : [];
    $normalized_sort_options = [];

    foreach ($sort_options as $value => $sort_option) {
        if (!is_array($sort_option) || array_is_list($sort_option)) continue;

        $value = sanitize_key((string) $value);
        $label = $to_string($sort_option['label'] ?? '');
        $orderby = $to_string($sort_option['orderby'] ?? '');
        $order = strtoupper($to_string($sort_option['order'] ?? 'DESC'));
        if ($value === '' || $label === '' || $orderby === '') continue;

        $normalized_sort_options[$value] = [
            'label' => $label,
            'orderby' => $orderby,
            'order' => $order === 'ASC' ? 'ASC' : 'DESC',
        ];
    }

    if ($normalized_sort_options === []) {
        $normalized_sort_options = [
            'date_desc' => ['label' => 'Plus récents', 'orderby' => 'date', 'order' => 'DESC'],
        ];
    }
    if (!isset($normalized_sort_options[$sort_default])) $sort_default = array_key_first($normalized_sort_options);

    $sort_value = isset($request[$sort_name]) && is_scalar($request[$sort_name])
        ? sanitize_key(wp_unslash((string) $request[$sort_name]))
        : $sort_default;
    $sort_value = isset($normalized_sort_options[$sort_value]) ? $sort_value : $sort_default;

    if (($options['sort'] ?? true) !== false) {
        $sort_item = [
            'type' => 'sort',
            'name' => $sort_name,
            'label' => $to_string($sort_config['label'] ?? '') ?: 'Trier par',
            'value' => $sort_value,
            'options' => array_map(static fn(string $value, array $sort_option): array => [
                'value' => $value,
                'label' => $sort_option['label'],
            ], array_keys($normalized_sort_options), $normalized_sort_options),
        ];
        $filter_items[] = $sort_item;
        $filter_groups['sort'][] = $sort_item;
    }

    $paged = max(1, (int) get_query_var('paged'));
    $query_args = [
        'post_type' => $post_type !== '' ? $post_type : 'post',
        'post_status' => 'publish',
        'paged' => $paged,
        'ignore_sticky_posts' => true,
        'suppress_filters' => false,
        'orderby' => $normalized_sort_options[$sort_value]['orderby'],
        'order' => $normalized_sort_options[$sort_value]['order'],
    ];
    if (is_numeric($options['posts_per_page'] ?? null)) {
        $query_args['posts_per_page'] = (int) $options['posts_per_page'];
    }
    if ($search_value !== '') {
        if ($search_acf_fields === []) {
            $query_args['s'] = $search_value;
        } else {
            $search_meta_query = ['relation' => 'OR'];
            foreach ($search_acf_fields as $field) {
                $search_meta_query[] = [
                    'key' => $field,
                    'value' => $search_value,
                    'compare' => 'LIKE',
                ];
            }
            $meta_query[] = $search_meta_query;
        }
    }
    if ($tax_query !== []) {
        $relation = strtoupper($to_string($options['taxonomy_relation'] ?? 'AND'));
        $query_args['tax_query'] = array_merge(
            ['relation' => $relation === 'OR' ? 'OR' : 'AND'],
            $tax_query
        );
    }
    if ($meta_query !== []) {
        $relation = strtoupper($to_string($options['meta_relation'] ?? 'AND'));
        $query_args['meta_query'] = array_merge(
            ['relation' => $relation === 'OR' ? 'OR' : 'AND'],
            $meta_query
        );
    }

    $query = $post_type !== '' ? new WP_Query($query_args) : null;
    $items = [];

    if ($query instanceof WP_Query) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $title = trim((string) get_the_title($post_id));
            if ($title === '') continue;

            $item = [
                'post_id' => $post_id,
                'type' => $singular_label,
                'date' => get_the_date('', $post_id),
                'datetime' => get_the_date('Y-m-d', $post_id),
                'title' => $title,
                'text' => get_the_excerpt($post_id),
                'image' => get_post_thumbnail_id($post_id),
                'link' => ['url' => get_permalink($post_id), 'title' => $title],
            ];
            $filtered_item = apply_filters('theme_filters_result_item', $item, $post_id, $post_type);
            $items[] = is_array($filtered_item) && !array_is_list($filtered_item) ? $filtered_item : $item;
        }

        wp_reset_postdata();
    }

    $configured_card = $to_string($options['card'] ?? '');
    if ($configured_card === '' && $post_type !== '') {
        $configured_card = apply_filters('theme_content_type_card', $post_type, $post_type);
    }
    $card_name = resolve_card_name($configured_card);
    $card_name = $card_name !== '' ? $card_name : 'page';
    $result_count = $query instanceof WP_Query ? (int) $query->found_posts : 0;
    $result_labels = is_array($options['result_labels'] ?? null) ? $options['result_labels'] : [];
    $result_count_label = $result_count === 1
        ? $to_string($result_labels['singular'] ?? $singular_label)
        : $to_string($result_labels['plural'] ?? $post_type_label);
    $has_active_filters = $search_value !== '' || $tax_query !== [] || $meta_query !== [] || $sort_value !== $sort_default;
    $ajax = filter_var($options['ajax'] ?? false, FILTER_VALIDATE_BOOL);
    $ajax_group = sanitize_html_class($to_string($options['ajax_group'] ?? '') ?: $post_type . '-filters');
    $ajax_target = sanitize_html_class($to_string($options['ajax_target'] ?? '') ?: $ajax_group . '-results');
    $form = [
        'action' => $action_url,
        'items' => $filter_items,
        'submit_label' => $to_string($options['submit_label'] ?? '') ?: 'Filtrer',
        'reset_label' => $to_string($options['reset_label'] ?? '') ?: 'Réinitialiser',
        'reset_url' => $reset_url,
        'show_reset' => $has_active_filters,
        'result_count' => $result_count,
        'result_count_label' => $result_count_label,
        'aria_label' => $to_string($options['aria_label'] ?? '') ?: 'Filtrer les résultats',
        'ajax' => $ajax,
        'ajax_group' => $ajax_group,
        'ajax_target' => $ajax_target,
    ];
    $forms = [];
    $groups = is_array($options['groups'] ?? null) && !array_is_list($options['groups'])
        ? $options['groups']
        : [];

    $hidden_items = static function (array $items): array {
        $hidden = [];

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $name = (string) ($item['name'] ?? '');
            $value = $item['value'] ?? '';
            if ($name === '') continue;

            if (is_array($value)) {
                foreach ($value as $key => $entry) {
                    if (!is_scalar($entry) || trim((string) $entry) === '') continue;

                    $hidden[] = [
                        'type' => 'hidden',
                        'name' => array_is_list($value) ? $name . '[]' : $name . '[' . $key . ']',
                        'value' => (string) $entry,
                    ];
                }
                continue;
            }

            if (!is_scalar($value) || trim((string) $value) === '') continue;
            $hidden[] = ['type' => 'hidden', 'name' => $name, 'value' => (string) $value];
        }

        return $hidden;
    };

    foreach ($groups as $group_name => $group_options) {
        $group_name = sanitize_key((string) $group_name);
        if ($group_name === '') continue;

        $group_options = is_array($group_options) ? $group_options : [];
        $controls = array_is_list($group_options)
            ? $group_options
            : ($group_options['controls'] ?? []);
        $controls = is_array($controls) ? array_values(array_filter(array_map('sanitize_key', $controls))) : [];
        $visible_items = [];
        $other_items = [];

        foreach ($filter_groups as $control_group => $control_items) {
            if (in_array($control_group, $controls, true)) {
                $visible_items = array_merge($visible_items, $control_items);
            } else {
                $other_items = array_merge($other_items, $control_items);
            }
        }
        if ($visible_items === []) continue;

        $group_form = $form;
        $group_form['items'] = array_merge($visible_items, $hidden_items($other_items));
        $group_form['classes'] = ['filters-' . $group_name, $group_options['classes'] ?? ''];
        $group_form['submit_label'] = $to_string($group_options['submit_label'] ?? '') ?: $form['submit_label'];
        $group_form['aria_label'] = $to_string($group_options['aria_label'] ?? '') ?: $form['aria_label'];
        $group_form['ajax_submit_on_change'] = filter_var(
            $group_options['ajax_submit_on_change'] ?? false,
            FILTER_VALIDATE_BOOL
        );
        if (array_key_exists('show_reset', $group_options)) {
            $group_form['show_reset'] = filter_var($group_options['show_reset'], FILTER_VALIDATE_BOOL)
                && $has_active_filters;
        }
        if (array_key_exists('show_result_count', $group_options)
            && !filter_var($group_options['show_result_count'], FILTER_VALIDATE_BOOL)) {
            $group_form['result_count'] = null;
        }

        $forms[$group_name] = $group_form;
    }

    return [
        'post_type' => $post_type,
        'post_type_label' => $post_type_label,
        'archive_title' => $post_type !== '' ? post_type_archive_title('', false) : '',
        'archive_url' => $archive_url,
        'action_url' => $action_url,
        'query' => $query,
        'items' => $items,
        'card' => $card_name,
        'result_count' => $result_count,
        'search_value' => $search_value,
        'has_active_filters' => $has_active_filters,
        'paged' => $paged,
        'ajax_target' => $ajax_target,
        'form' => $form,
        'forms' => $forms,
    ];
}

/**
 * Façade de rendu pour placer séparément les contrôles d'une page filtrée.
 */
final class Theme_Filters
{
    /** @var array<string, mixed> */
    private array $context;

    public function __construct(array $options = [])
    {
        if (!isset($options['groups'])) {
            $options['groups'] = [
                'search' => [
                    'controls' => ['search'],
                    'submit_label' => 'Rechercher',
                    'aria_label' => 'Rechercher dans les résultats',
                    'show_reset' => false,
                    'show_result_count' => false,
                ],
                'facets' => [
                    'controls' => ['taxonomies', 'acf'],
                    'submit_label' => 'Filtrer',
                    'aria_label' => 'Filtrer les résultats',
                ],
                'sort' => [
                    'controls' => ['sort'],
                    'submit_label' => 'Trier',
                    'aria_label' => 'Trier les résultats',
                    'ajax_submit_on_change' => true,
                    'show_reset' => false,
                    'show_result_count' => false,
                ],
            ];
        }

        $this->context = theme_build_filters_context($options);
    }

    /** @param array{classes?: mixed} $options */
    public function search(array $options = []): void
    {
        $this->render_form('search', $options);
    }

    /** @param array{classes?: mixed} $options */
    public function filters(array $options = []): void
    {
        $this->render_form('facets', $options);
    }

    /**
     * Alias rétrocompatible de filters().
     *
     * @param array{classes?: mixed} $options
     */
    public function facets(array $options = []): void
    {
        $this->filters($options);
    }

    /** @param array{classes?: mixed} $options */
    public function sort(array $options = []): void
    {
        $this->render_form('sort', $options);
    }

    public function form(string $name): void
    {
        $this->render_form(sanitize_key($name));
    }

    public function archive_title(): string
    {
        return is_string($this->context['archive_title'] ?? null)
            ? $this->context['archive_title']
            : '';
    }

    public function target(): string
    {
        return is_string($this->context['ajax_target'] ?? null)
            ? $this->context['ajax_target']
            : '';
    }

    /** @return list<array<string, mixed>> */
    public function items(): array
    {
        $items = $this->context['items'] ?? [];

        return is_array($items) && array_is_list($items) ? $items : [];
    }

    public function card(): string
    {
        return is_string($this->context['card'] ?? null) && $this->context['card'] !== ''
            ? $this->context['card']
            : 'page';
    }

    public function has_results(): bool
    {
        return $this->items() !== [];
    }

    /**
     * @param array{
     *     classes?: mixed,
     *     messages?: array{zero?: mixed, singular?: mixed, plural?: mixed}
     * } $options
     */
    public function results_message(array $options = []): void
    {
        if (!filter_var($this->context['has_active_filters'] ?? false, FILTER_VALIDATE_BOOL)) return;

        $count = max(0, (int) ($this->context['result_count'] ?? 0));
        $classes = component_classes('filters-results-message', $options['classes'] ?? '');
        $messages = is_array($options['messages'] ?? null) && !array_is_list($options['messages'])
            ? $options['messages']
            : [];
        $to_message = static fn(mixed $value, string $fallback): string =>
            (is_scalar($value) || $value instanceof Stringable) && trim((string) $value) !== ''
                ? trim((string) $value)
                : $fallback;
        $message = $count === 0
            ? $to_message($messages['zero'] ?? null, 'Aucun résultat pour votre recherche.')
            : ($count === 1
                ? $to_message($messages['singular'] ?? null, '{count} résultat correspond à votre recherche.')
                : $to_message($messages['plural'] ?? null, '{count} résultats correspondent à votre recherche.'));
        $message_parts = explode('{count}', $message);
        ?>
        <p class="<?= $classes ?>">
            <?php foreach ($message_parts as $index => $part) : ?>
                <?php if ($index > 0) : ?><strong><?= esc_html((string) $count) ?></strong><?php endif; ?><?= esc_html($part) ?>
            <?php endforeach; ?>
        </p>
        <?php
    }

    public function current_page(): int
    {
        return max(1, (int) ($this->context['paged'] ?? 1));
    }

    public function total_pages(): int
    {
        $query = $this->context['query'] ?? null;

        return $query instanceof WP_Query ? max(0, (int) $query->max_num_pages) : 0;
    }

    public function post_type_label(): string
    {
        return is_string($this->context['post_type_label'] ?? null)
            ? $this->context['post_type_label']
            : '';
    }

    /** @return array<string, mixed> */
    public function context(): array
    {
        return $this->context;
    }

    /** @param array{classes?: mixed} $options */
    private function render_form(string $name, array $options = []): void
    {
        $form = $this->context['forms'][$name] ?? null;
        if (!is_array($form) || array_is_list($form)) return;

        $form['classes'] = [
            $form['classes'] ?? '',
            $options['classes'] ?? '',
        ];

        component('filters', $form);
    }
}
