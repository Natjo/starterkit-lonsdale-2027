<div class="st-components">
    <section class="styleguide-section styleguide-section--full" id="style-colors">
        <div class="styleguide-demo">
            <h2 class="styleguide-section-title"><code>Couleurs</code></h2>
            <ul class="styleguide-swatches" role="list">
                <?php foreach (['color-1', 'color-2', 'color-3', 'color-theme', 'color-bg', 'color-surface', 'color-overlay', 'color-text', 'color-text-invert', 'color-muted', 'color-border', 'color-shadow', 'color-error', 'color-gray-light', 'color-gray', 'color-gray-dark'] as $token) : ?>
                    <li>
                        <div class="styleguide-swatch" style="--swatch: var(--<?= esc_attr($token) ?>)" aria-hidden="true"></div>
                        <code>--<?= esc_html($token) ?></code>
                        <span data-styleguide-token="--<?= esc_attr($token) ?>"></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <section class="styleguide-section styleguide-section--full" id="style-typography">
        <div class="styleguide-demo">
            <h2 class="styleguide-section-title"><code>Typographie</code></h2>
            <?php foreach (['font-1', 'font-2'] as $token) : ?>
                <article class="styleguide-font-card" style="font-family: var(--<?= esc_attr($token) ?>)">
                    <header class="styleguide-font-header">
                        <div>
                            <code class="styleguide-font-token">--<?= esc_html($token) ?></code>
                            <h3 data-styleguide-font-name="--<?= esc_attr($token) ?>"><?= esc_html($token) ?></h3>
                            <p class="styleguide-font-stack" data-styleguide-token="--<?= esc_attr($token) ?>"></p>
                        </div>
                        <span class="styleguide-font-monogram" aria-hidden="true">Aa</span>
                    </header>
                    <?php foreach ([
                        ['name' => 'Light', 'weight' => 300, 'style' => 'normal'],
                        ['name' => 'Book', 'weight' => 500, 'style' => 'normal'],
                        ['name' => 'Heavy', 'weight' => 800, 'style' => 'normal'],
                        ['name' => 'Light Italic', 'weight' => 300, 'style' => 'italic'],
                    ] as $font) : ?>
                        <div class="styleguide-font-row">
                            <div class="styleguide-font-meta">
                                <strong><?= esc_html($font['name']) ?></strong>
                                <code><?= (int) $font['weight'] ?> · <?= esc_html($font['style']) ?></code>
                            </div>
                            <p class="styleguide-font-alphabet" style="font-weight: <?= (int) $font['weight'] ?>; font-style: <?= esc_attr($font['style']) ?>">
                                <span>ABCDEFGHIJKLMNOPQRSTUVWXYZ</span>
                                <span>abcdefghijklmnopqrstuvwxyz</span>
                                <span>0123456789 &amp; @ ! ? €</span>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="styleguide-section styleguide-section--full" id="style-spacing">
        <div class="styleguide-demo">
            <h2 class="styleguide-section-title"><code>Espacements</code></h2>
            <ul class="styleguide-token-list" role="list">
                <?php foreach (['space-xs', 'space-sm', 'space-md', 'space-lg', 'gap', 'ctr-offset'] as $token) : ?>
                    <li>
                        <code>--<?= esc_html($token) ?></code>
                        <span data-styleguide-token="--<?= esc_attr($token) ?>"></span>
                        <span class="styleguide-spacing-bar" style="width: var(--<?= esc_attr($token) ?>)" aria-hidden="true"></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <section class="styleguide-section styleguide-section--full" id="style-surfaces">
        <div class="styleguide-demo">
            <h2 class="styleguide-section-title"><code>Surfaces et bordures</code></h2>
            <div class="styleguide-surface-sample">
                <code>--radius : <span data-styleguide-token="--radius"></span></code>
                <p>Fond <code>--color-surface</code>, bordure <code>--color-border</code>, ombre <code>--color-shadow</code>.</p>
            </div>
        </div>
    </section>
    <section class="styleguide-section styleguide-section--full" id="style-layout">
        <div class="styleguide-demo">
            <h2 class="styleguide-section-title"><code>Layout</code></h2>
            <ul class="styleguide-token-list" role="list">
                <?php foreach (['ctr-width', 'ctr-offset', 'gap', 'header-height'] as $token) : ?>
                    <li><code>--<?= esc_html($token) ?></code><span data-styleguide-token="--<?= esc_attr($token) ?>"></span></li>
                <?php endforeach; ?>
            </ul>
            <p class="styleguide-note"><code>display: grid; grid-template-columns: var(--layout);</code></p>
            <div class="styleguide-layout-sample" data-styleguide-layout aria-label="Zones nommées du layout du thème">
                    <?php foreach (['full', 'fluid', 'ctr', 'ctr-sm', 'center'] as $rail) : ?>
                        <span class="styleguide-layout-guide styleguide-layout-rail--<?= esc_attr($rail) ?>"
                              data-layout-guide="<?= esc_attr($rail) ?>" aria-hidden="true"
                              style="grid-column: <?= esc_attr($rail) ?> / <?= esc_attr($rail) ?>-end"
                              <?= in_array($rail, ['ctr-sm', 'center'], true) ? 'hidden' : '' ?>></span>
                    <?php endforeach; ?>
                <?php foreach (['full', 'fluid', 'ctr', 'ctr-sm', 'center'] as $index => $rail) : ?>
                    <div class="styleguide-layout-rail styleguide-layout-rail--<?= esc_attr($rail) ?>"
                         data-layout-rail="<?= esc_attr($rail) ?>"
                         style="grid-column: <?= esc_attr(in_array($rail, ['full', 'fluid', 'ctr'], true) ? $rail : $rail . ' / ' . $rail . '-end') ?>; grid-row: <?= $index + 1 ?>"
                         <?= in_array($rail, ['ctr-sm', 'center'], true) ? 'hidden' : '' ?>>
                        <strong><?= esc_html($rail) ?></strong>
                        <code data-layout-placement>grid-column: <?= esc_html(in_array($rail, ['full', 'fluid', 'ctr'], true) ? $rail : $rail . ' / ' . $rail . '-end') ?></code>
                        <span data-layout-width></span>
                    </div>
                <?php endforeach; ?>
                <div class="styleguide-layout-rail styleguide-layout-rail--mixed"
                     data-layout-rail="ctr" data-layout-end="fluid"
                     style="grid-column: ctr / fluid; grid-row: 4">
                    <strong>ctr - fluid</strong>
                    <code data-layout-placement>grid-column: ctr / fluid</code>
                    <span data-layout-width></span>
                </div>
            </div>
            <div class="styleguide-layout-definition">
                <code>--layout : <span data-styleguide-token="--layout"></span></code>
            </div>
        </div>
    </section>
    <section class="styleguide-section styleguide-section--full" id="style-breakpoints">
        <div class="styleguide-demo">
            <h2 class="styleguide-section-title"><code>Breakpoints</code></h2>
            <p class="styleguide-note">Largeur actuelle : <strong data-styleguide-viewport></strong>. La plage active est surlignée.</p>
            <?php $breakpoints = $styleguide_breakpoints; ?>
            <?php if ($breakpoints !== []) : ?>
                <div class="styleguide-breakpoints-scroll" role="region" aria-label="Seuils responsive du thème" tabindex="0">
                    <table class="styleguide-breakpoints">
                        <thead><tr><th scope="col">Nom</th><th scope="col">Seuil</th><th scope="col">-up</th><th scope="col">-down</th><th scope="col">-only</th></tr></thead>
                        <tbody>
                            <?php foreach ($breakpoints as $index => $breakpoint) :
                                $width = (int) $breakpoint['width'];
                                $next = isset($breakpoints[$index + 1]) ? (int) $breakpoints[$index + 1]['width'] : null;
                            ?>
                                <tr data-breakpoint-min="<?= $width ?>"<?= $next !== null ? ' data-breakpoint-max="' . ($next - 1) . '"' : '' ?>>
                                    <th scope="row"><code>$breakpoint-<?= esc_html($breakpoint['name']) ?></code></th>
                                    <td><?= $width ?> px</td>
                                    <td><code>min-width: <?= $width ?>px</code></td>
                                    <td><code>max-width: <?= $width - 1 ?>px</code></td>
                                    <td><?php if (!empty($breakpoint['only']) && $next !== null) : ?><code><?= $width ?>–<?= $next - 1 ?>px</code><?php else : ?>—<?php endif; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="styleguide-note">En dessous de <?= (int) $breakpoints[0]['width'] ?> px, aucun seuil <code>-up</code> n’est atteint. Les seuils proviennent de <code>assets/styles/breakpoints.scss</code>.</p>
            <?php else : ?>
                <p class="styleguide-note">Relancer le build des assets pour afficher les breakpoints.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
