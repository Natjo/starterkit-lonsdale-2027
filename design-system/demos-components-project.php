<?php
/** Components added or renamed in a client project, outside the starter's fixed demos. */
$known = require __DIR__ . '/components-index.php';
foreach (array_diff_key($design_system_sections, $known) as $name => $label) : ?>
    <section class="design-system-section design-system-section--component" id="component-<?= esc_attr($name) ?>">
        <div class="design-system-demo">
            <h2 class="design-system-section-title"><code><?= esc_html($name) ?></code></h2>
            <p class="design-system-note"><?= esc_html(theme_design_system_version($name, 'components')) ?></p>
            <div class="design-system-preview design-system-preview--block">
                <?php component($name, $catalogue['components'][$name]); ?>
            </div>
        </div>
        <aside class="design-system-readme"><?= theme_design_system_readme($name) ?></aside>
    </section>
<?php endforeach; ?>
