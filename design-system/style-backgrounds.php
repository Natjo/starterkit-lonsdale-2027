<?php
$backgrounds_path = __DIR__ . '/docs/backgrounds.json';
$backgrounds = is_readable($backgrounds_path) ? json_decode(file_get_contents($backgrounds_path), true) : [];
$backgrounds = is_array($backgrounds) ? array_values(array_unique(array_filter($backgrounds,
    static fn ($name): bool => is_string($name) && preg_match('/^bg-[a-z0-9_-]+$/i', $name) === 1
))) : [];
?>
<section class="design-system-section design-system-section--full" id="style-backgrounds">
    <div class="design-system-demo">
        <h2 class="design-system-section-title"><code>Backgrounds</code></h2>
        <?php if ($backgrounds !== []) : ?>
            <ul class="design-system-backgrounds" role="list">
                <?php foreach ($backgrounds as $background) : ?>
                    <li>
                        <div class="design-system-background-sample <?= esc_attr($background) ?>">
                            <span>Aa</span>
                            <span>Exemple de texte sur ce fond.</span>
                        </div>
                        <code>.<?= esc_html($background) ?></code>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="design-system-note">Aucune classe de background disponible dans <code>assets/styles/classes.scss</code>.</p>
        <?php endif; ?>
    </div>
</section>
