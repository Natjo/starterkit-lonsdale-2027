<?php
/** Shared responsive indicator for every design-system family. */
$breakpoints_path = __DIR__ . '/docs/breakpoints.json';
$design_system_breakpoints = is_readable($breakpoints_path) ? json_decode(file_get_contents($breakpoints_path), true) : [];
$design_system_breakpoints = is_array($design_system_breakpoints) ? $design_system_breakpoints : [];
?>
    <?php if ($design_system_breakpoints !== []) : ?>
        <aside class="design-system-breakpoint-indicator" aria-label="Breakpoint actuel"
               data-design-system-breakpoint data-breakpoints="<?= esc_attr(wp_json_encode($design_system_breakpoints)) ?>" hidden>
            <strong>Breakpoint <span data-breakpoint-name></span></strong>
            <span data-design-system-viewport></span>
        </aside>
    <?php endif; ?>
