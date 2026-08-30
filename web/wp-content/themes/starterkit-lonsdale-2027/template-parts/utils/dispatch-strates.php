<?php
$fields = get_fields() ?: [];
$strates = $fields['strates'] ?? [];

if (!is_array($strates) || $strates === []) {
    return;
}

foreach ($strates as $strate) {
    if (!is_array($strate)) {
        continue;
    }

    $layout = isset($strate['acf_fc_layout']) ? trim((string) $strate['acf_fc_layout']) : '';

    if ($layout === '') {
        continue;
    }

    strate($layout, $strate);
}
