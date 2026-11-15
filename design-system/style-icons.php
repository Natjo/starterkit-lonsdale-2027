<?php
// Inventory the actual compiled sprite, including projects without the icon component.
$icon_names = [];
$sprite_path = get_theme_file_path('/assets/img/icons.svg');
if (is_readable($sprite_path)) {
    $previous_errors = libxml_use_internal_errors(true);
    try {
        $sprite = new DOMDocument();
        if ($sprite->load($sprite_path, LIBXML_NONET)) {
            foreach ($sprite->getElementsByTagName('symbol') as $symbol) {
                $name = $symbol->getAttribute('id');
                if ($name !== '' && sanitize_html_class($name) === $name) $icon_names[$name] = $name;
            }
        }
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous_errors);
    }
}
natcasesort($icon_names);
$sprite_url = rtrim(THEME_ASSETS, '/') . '/img/icons.svg';
?>
<section class="design-system-section design-system-section--full" id="style-icons">
    <div class="design-system-demo">
        <h2 class="design-system-section-title"><code>Icônes</code></h2>
        <?php if ($icon_names !== []) : ?>
            <p class="design-system-note"><?= count($icon_names) ?> icônes · <code>assets/img/icons.svg</code></p>
            <ul class="design-system-icons" role="list">
                <?php foreach ($icon_names as $name) : ?>
                    <li>
                        <svg width="32" height="32" aria-hidden="true" focusable="false"><use href="<?= esc_url($sprite_url . '#' . $name) ?>"></use></svg>
                        <code><?= esc_html($name) ?></code>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="design-system-note">Aucune icône disponible dans le sprite du thème.</p>
        <?php endif; ?>
    </div>
</section>
