<?php
$fields = theme_get_fields();
$strates = $fields['strates'] ?? [];

if (!is_array($strates) || $strates === []) {
    return;
}

foreach ($strates as $strate) {
    if (!is_array($strate) || array_is_list($strate)) {
        continue;
    }

    $layout = theme_string($strate['acf_fc_layout'] ?? '');

    if ($layout === '') {
        continue;
    }

    strate($layout, $strate);
}
