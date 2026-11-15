    <canvas class="design-system-particles" aria-hidden="true"></canvas>
    <header class="design-system-intro">
        <div class="design-system-title-row">
            <h1><span>Design system</span><br><span class="design-system-family-title"><?= esc_html($family['label']) ?></span></h1>
            <button type="button" class="design-system-refresh" data-design-system-refresh><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M6.1 6.1A8 8 0 0 1 20 12M4 12a8 8 0 0 0 13.9 5.9"/></svg>Refresh</button>
        </div>
        <nav class="design-system-families" aria-label="Rubriques du Design system">
            <?php foreach ($design_system_families as $key => $entry) :
            ?>
                <a href="<?= esc_url(forge_ds_page($entry['slug'])) ?>"<?= $key === $design_system_family ? ' aria-current="page"' : '' ?>><?= esc_html($entry['label']) ?></a>
            <?php endforeach; ?>
        </nav>
        <?= $design_system_header_controls ?? '' ?>
    </header>
