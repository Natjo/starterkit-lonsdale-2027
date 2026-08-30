<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$args = normalize_args($params, [
    'headers' => [],
    'rows' => [],
    'id' => '',
    'caption' => '',
    'aria_label' => 'Tableau de données',
    'row_headers' => false,
    'scrollable' => true,
    'classes' => '',
    'table_classes' => '',
    'attributes' => [],
    'scroll_attributes' => [],
    'table_attributes' => [],
    'caption_attributes' => [],
]);
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';
$first_string = static function (mixed ...$values) use ($to_string): string {
    foreach ($values as $value) {
        $value = $to_string($value);
        if ($value !== '') return $value;
    }
    return '';
};
$to_bool = static fn(mixed $value, bool $default = false): bool => filter_var(
    $value,
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
) ?? $default;
$to_span = static function (mixed $value): int {
    $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
    return $value === false ? 1 : $value;
};

$raw_headers = is_array($args['headers']) ? $args['headers'] : [];
$headers = [];
$source_position = 0;
foreach ($raw_headers as $source_key => $header) {
    $config = is_array($header) ? $header : [];
    $label = $first_string($config['label'] ?? null, $header);
    $key = $to_string($config['key'] ?? (is_string($source_key) ? $source_key : ''));
    $scope = strtolower($to_string($config['scope'] ?? 'col'));
    $scope = in_array($scope, ['col', 'colgroup'], true) ? $scope : 'col';

    if ($label !== '') {
        $headers[] = [
            'key' => $key,
            'source_position' => $source_position,
            'label' => $label,
            'scope' => $scope,
            'colspan' => $to_span($config['colspan'] ?? 1),
            'rowspan' => $to_span($config['rowspan'] ?? 1),
            'classes' => $config['classes'] ?? '',
            'attributes' => is_array($config['attributes'] ?? null) ? $config['attributes'] : [],
        ];
    }
    $source_position++;
}
if ($headers === []) return;

$row_headers = $to_bool($args['row_headers']);
$raw_rows = is_array($args['rows']) && array_is_list($args['rows']) ? $args['rows'] : [];
$rows = [];
foreach ($raw_rows as $raw_row) {
    if (!is_array($raw_row)) continue;

    $wrapped_row = is_array($raw_row['cells'] ?? null);
    $row_data = $wrapped_row ? $raw_row['cells'] : $raw_row;
    $row_attributes = $wrapped_row && is_array($raw_row['attributes'] ?? null)
        ? $raw_row['attributes']
        : [];
    $row_is_list = array_is_list($row_data);
    $cells = [];
    $has_content = false;

    foreach ($headers as $column_index => $header) {
        if ($row_is_list) {
            $cell = $row_data[$header['source_position']] ?? '';
        } elseif ($header['key'] !== '' && array_key_exists($header['key'], $row_data)) {
            $cell = $row_data[$header['key']];
        } else {
            $cell = $row_data[$header['source_position']] ?? '';
        }

        $config = is_array($cell) ? $cell : [];
        $content = $first_string($config['content'] ?? null, $cell);
        $is_header = $to_bool($config['header'] ?? ($row_headers && $column_index === 0));
        $scope = strtolower($to_string($config['scope'] ?? 'row'));
        $scope = in_array($scope, ['row', 'rowgroup'], true) ? $scope : 'row';
        if ($content !== '') $has_content = true;

        $cells[] = [
            'content' => $content,
            'header' => $is_header,
            'scope' => $scope,
            'colspan' => $to_span($config['colspan'] ?? 1),
            'rowspan' => $to_span($config['rowspan'] ?? 1),
            'classes' => $config['classes'] ?? '',
            'attributes' => is_array($config['attributes'] ?? null) ? $config['attributes'] : [],
        ];
    }

    if ($has_content) {
        $rows[] = ['cells' => $cells, 'attributes' => $row_attributes];
    }
}
if ($rows === []) return;

$requested_id = sanitize_html_class($to_string($args['id']));
$base_id = $requested_id !== ''
    ? $requested_id
    : (function_exists('wp_unique_id') ? wp_unique_id('table-') : uniqid('table-', false));
$caption_id = $base_id . '-caption';
$table_id = $base_id . '-table';
$caption = $to_string($args['caption']);
$accessible_caption = $caption !== '' ? $caption : ($to_string($args['aria_label']) ?: 'Tableau de données');
$scrollable = $to_bool($args['scrollable'], true);
$classes = component_classes('table', $args['classes']);
$table_classes = component_classes('table__table', $args['table_classes']);
$attributes = component_attributes($args['attributes'], ['id', 'class']);
$scroll_attributes = component_attributes($args['scroll_attributes'], ['class', 'role', 'tabindex', 'aria-label', 'aria-labelledby']);
$table_attributes = component_attributes($args['table_attributes'], ['id', 'class']);
$caption_attributes = component_attributes($args['caption_attributes'], ['id', 'class']);
?>

<div id="<?= esc_attr($base_id) ?>" class="<?= $classes ?>"<?= $attributes ?>>
    <div class="table__scroll"<?= $scrollable ? ' tabindex="0" role="region" aria-labelledby="' . esc_attr($caption_id) . '"' : '' ?><?= $scroll_attributes ?>>
        <table id="<?= esc_attr($table_id) ?>" class="<?= $table_classes ?>"<?= $table_attributes ?>>
            <caption id="<?= esc_attr($caption_id) ?>" class="table__caption<?= $caption === '' ? ' table__caption--visually-hidden' : '' ?>"<?= $caption_attributes ?>><?= esc_html($accessible_caption) ?></caption>
            <thead>
                <tr>
                    <?php foreach ($headers as $header) :
                        $header_classes = component_classes($header['classes']);
                        $header_attributes = component_attributes($header['attributes'], ['class', 'scope', 'colspan', 'rowspan']);
                    ?>
                        <th scope="<?= esc_attr($header['scope']) ?>"<?= $header['colspan'] > 1 ? ' colspan="' . esc_attr($header['colspan']) . '"' : '' ?><?= $header['rowspan'] > 1 ? ' rowspan="' . esc_attr($header['rowspan']) . '"' : '' ?><?= $header_classes !== '' ? ' class="' . $header_classes . '"' : '' ?><?= $header_attributes ?>><?= wp_kses_post($header['label']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) :
                    $row_attributes = component_attributes($row['attributes']);
                ?>
                    <tr<?= $row_attributes ?>>
                        <?php foreach ($row['cells'] as $cell) :
                            $tag = $cell['header'] ? 'th' : 'td';
                            $cell_classes = component_classes($cell['classes']);
                            $cell_attributes = component_attributes($cell['attributes'], ['class', 'scope', 'colspan', 'rowspan']);
                        ?>
                            <<?= $tag ?><?= $cell['header'] ? ' scope="' . esc_attr($cell['scope']) . '"' : '' ?><?= $cell['colspan'] > 1 ? ' colspan="' . esc_attr($cell['colspan']) . '"' : '' ?><?= $cell['rowspan'] > 1 ? ' rowspan="' . esc_attr($cell['rowspan']) . '"' : '' ?><?= $cell_classes !== '' ? ' class="' . $cell_classes . '"' : '' ?><?= $cell_attributes ?>><?= wp_kses_post($cell['content']) ?></<?= $tag ?>>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
