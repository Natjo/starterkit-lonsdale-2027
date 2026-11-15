<?php
/** Site demonstration only: all controls live in the design-system header. */
require __DIR__ . '/common-header.php';
?>
<?php if ($selected_common !== '' && !preg_match('/^(header|footer)(-|$)/', $selected_common)) : ?>
    <section id="common-<?= esc_attr($selected_common) ?>" aria-label="<?= esc_attr($items[$selected_common]['name'] ?? $selected_common) ?>">
        <?php common($selected_common); ?>
    </section>
<?php endif; ?>
<div class="forge-common-main" id="demonstration">
    <article class="forge-common-editorial" aria-label="Contenu de démonstration">
        <?php component('text', ['text' => require __DIR__ . '/text-example.php', 'classes' => 'rte']); ?>
    </article>
</div>
<?php require __DIR__ . '/common-footer.php'; ?>
