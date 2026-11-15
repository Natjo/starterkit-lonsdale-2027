<div class="st-components">
    <?php if ($catalogue['modules'] === []) : ?><p class="design-system-note">Aucun module disponible dans ce thème.</p><?php endif; ?>
    <?php foreach ($catalogue['modules'] as $slug => $definition) : ?>
        <section class="design-system-section design-system-section--component" id="module-<?= esc_attr($slug) ?>">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code><?= esc_html($slug) ?></code></h2>
                <p class="design-system-note"><?= !empty($definition['version']) ? 'Version ' . esc_html($definition['version']) : 'Version non renseignée' ?></p>
                <?php if ($definition['file'] !== '') : ?>
                    <p><code>assets/modules/<?= esc_html($slug . '/' . $definition['file']) ?></code></p>
                <?php else : ?>
                    <p class="design-system-note">Aucun point d’entrée JavaScript disponible.</p>
                <?php endif; ?>
                <p class="design-system-note">Assets : <?= $definition['file'] !== '' ? 'JavaScript' : '—' ?><?= $definition['hasCss'] ? ' · CSS' : '' ?></p>
                <?php if (array_filter($definition['dependencies'])) : ?>
                    <h3>Dépendances</h3>
                    <ul class="design-system-token-list" role="list">
                        <?php foreach ($definition['dependencies'] as $family => $slugs) : ?>
                            <?php foreach ($slugs as $dependency) : ?>
                                <li><code><?= esc_html($family . ':' . $dependency) ?></code></li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-module-<?= esc_attr($slug) ?>">
                <h3 class="design-system-readme-title" id="readme-module-<?= esc_attr($slug) ?>">README</h3>
                <?= theme_design_system_readme($slug, 'modules') ?>
            </aside>
        </section>
    <?php endforeach; ?>
</div>
