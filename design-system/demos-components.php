<div class="st-components">
        <?php if (isset($design_system_sections['accordion'])) : ?><section class="design-system-section design-system-section--component" id="component-accordion">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>accordion</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('accordion', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('accordion', [
                        'items' => [
                            ['title' => 'À quoi sert ce composant ?', 'text' => '<p>Il présente plusieurs contenus repliables accessibles.</p>'],
                            ['title' => 'Peut-on ouvrir plusieurs panneaux ?', 'text' => '<p>Oui, en activant le mode multiple lors de l’appel.</p>'],
                            ['title' => 'Le contenu accepte-t-il du HTML ?', 'text' => '<p>Oui, le HTML éditorial est filtré par WordPress.</p>'],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-accordion">
                <h3 class="design-system-readme-title" id="readme-accordion">README</h3>
                <?= theme_design_system_readme('accordion') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['autocomplete'])) : ?><section class="design-system-section design-system-section--component" id="component-autocomplete">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>autocomplete</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('autocomplete', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('autocomplete', [
                        'name' => 'city',
                        'label' => 'Ville',
                        'placeholder' => 'Commencez à saisir une ville',
                        'options' => $design_system_options,
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-autocomplete">
                <h3 class="design-system-readme-title" id="readme-autocomplete">README</h3>
                <?= theme_design_system_readme('autocomplete') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['badge'])) : ?><section class="design-system-section design-system-section--component" id="component-badge">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>badge</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('badge', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('badge', ['name' => 'Nouveau']); ?>
                    <?php component('badge', ['name' => 'À la une']); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-badge">
                <h3 class="design-system-readme-title" id="readme-badge">README</h3>
                <?= theme_design_system_readme('badge') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['btn'])) : ?><section class="design-system-section design-system-section--component" id="component-btn">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>btn</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('btn', 'components')) ?></p>
                <?php
                $button_examples = [
                    ['name' => 'Bouton simple'],
                    ['name' => 'Bouton avec icône', 'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16]],
                    ['name' => 'Bouton avec SVG', 'svg' => true],
                    ['icon' => ['name' => 'blank', 'width' => 16, 'height' => 16], 'attributes' => ['aria-label' => 'Ouvrir les détails (icône seule)']],
                    ['svg' => true, 'attributes' => ['aria-label' => 'Ouvrir les détails (SVG seul)']],
                    ['name' => 'Bouton désactivé', 'attributes' => ['disabled' => true]],
                ];
                ?>
                <div class="design-system-variants">
                    <?php foreach (['btn-1', 'btn-2'] as $variant) : ?>
                        <div class="design-system-variant">
                            <h3><code><?= esc_html($variant) ?></code></h3>
                            <ul class="design-system-preview" role="list">
                                <?php foreach ($button_examples as $example) : ?>
                                    <li>
                                        <?php component('btn', array_replace($example, ['classes' => $variant])); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-btn">
                <h3 class="design-system-readme-title" id="readme-btn">README</h3>
                <?= theme_design_system_readme('btn') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['date'])) : ?><section class="design-system-section design-system-section--component" id="component-date">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>date</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('date', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('date', [
                        'date' => '20 juillet 2026',
                        'datetime' => '2026-07-20',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-date">
                <h3 class="design-system-readme-title" id="readme-date">README</h3>
                <?= theme_design_system_readme('date') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['quote'])) : ?><section class="design-system-section design-system-section--component" id="component-quote">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>quote</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('quote', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('quote', [
                        'quote' => '<p>Le design rend les usages complexes simples et désirables.</p>',
                        'author' => 'Équipe design',
                        'source' => 'Design system',
                        'url' => '#component-quote',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-quote">
                <h3 class="design-system-readme-title" id="readme-quote">README</h3>
                <?= theme_design_system_readme('quote') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['dialog'])) : ?><section class="design-system-section design-system-section--component" id="component-dialog">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>dialog</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('dialog', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('dialog', [
                        'title' => 'Exemple de fenêtre modale',
                        'content' => '<p>Ce contenu factice permet de tester l’ouverture, la fermeture et la restitution du focus.</p>',
                        'trigger' => ['type' => 'btn', 'label' => 'Ouvrir le dialogue'],
                        'close_label' => 'Fermer',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-dialog">
                <h3 class="design-system-readme-title" id="readme-dialog">README</h3>
                <?= theme_design_system_readme('dialog') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['eyebrow'])) : ?><section class="design-system-section design-system-section--component" id="component-eyebrow">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>eyebrow</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('eyebrow', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('eyebrow', ['eyebrow' => 'Sans icône']); ?>
                    <?php component('eyebrow', [
                        'eyebrow' => 'Avec une <strong>icône</strong>',
                        'icon' => 'logo',
                        'icon_width' => 54,
                        'icon_height' => 55,

                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-eyebrow">
                <h3 class="design-system-readme-title" id="readme-eyebrow">README</h3>
                <?= theme_design_system_readme('eyebrow') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['header'])) : ?><section class="design-system-section design-system-section--component" id="component-header">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>header</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('header', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('header', [
                        'title' => 'Introduction de section',
                        'title_classes' => 'title-2-bis',
                        'text' => '<p>Le composant assemble un titre h2, un texte et un lien facultatif.</p>',
                        'link' => [
                            'url' => '#component-header',
                            'title' => 'Lien d’exemple',
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-header">
                <h3 class="design-system-readme-title" id="readme-header">README</h3>
                <?= theme_design_system_readme('header') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['field'])) : ?><section class="design-system-section design-system-section--component" id="component-field">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>field</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('field', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <form class="form" data-module="components/field">
                        <fieldset>
                            <legend>Demande de contact</legend>
                            <?php component('field', [
                                'type' => 'email',
                                'name' => 'email',
                                'label' => 'Adresse e-mail',
                                'required' => true,
                                'mandatory_msg' => 'Saisissez une adresse e-mail.',
                                'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
                                'autocomplete' => 'email',
                            ]); ?>
                            <?php component('field', [
                                'type' => 'checkbox',
                                'name' => 'consent',
                                'label' => 'J’accepte les conditions',
                                'required' => true,
                            ]); ?>
                        </fieldset>
                        <div class="action"><button type="submit">Envoyer</button></div>
                    </form>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-field">
                <h3 class="design-system-readme-title" id="readme-field">README</h3>
                <?= theme_design_system_readme('field') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['icon'])) : ?><section class="design-system-section design-system-section--component" id="component-icon">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>icon</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('icon', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('icon', ['name' => 'email', 'width' => 44, 'height' => 44, 'label' => 'Étoile']); ?>
                    <?php component('icon', ['name' => 'linkedin', 'width' => 44, 'height' => 44, 'label' => 'Conversation']); ?>

                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-icon">
                <h3 class="design-system-readme-title" id="readme-icon">README</h3>
                <?= theme_design_system_readme('icon') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['image'])) : ?><section class="design-system-section design-system-section--component" id="component-image">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>image</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('image', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--media">
                    <?php component('image', [
                        'image' => $image_desktop,
                        'alt' => 'Exemple visuel du composant image',
                        'classes' => 'design-system-image',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-image">
                <h3 class="design-system-readme-title" id="readme-image">README</h3>
                <?= theme_design_system_readme('image') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['link'])) : ?><section class="design-system-section design-system-section--component" id="component-link">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>link</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('link', 'components')) ?></p>
                <?php
                $link_examples = [
                    ['link' => ['url' => '#component-link', 'title' => 'Lien simple']],
                    ['link' => ['url' => '#component-link', 'title' => 'Lien avec icône'], 'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16]],
                    ['link' => ['url' => '#component-link', 'title' => 'Lien avec SVG'], 'svg' => true],
                    ['link' => ['url' => '#component-link'], 'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16], 'attributes' => ['aria-label' => 'Découvrir le projet (icône seule)']],
                    ['link' => ['url' => '#component-link'], 'svg' => true, 'attributes' => ['aria-label' => 'Découvrir le projet (SVG seul)']],
                    ['link' => ['url' => '#component-link', 'title' => 'Lien désactivé'], 'attributes' => ['aria-disabled' => true]],
                ];
                ?>
                <div class="design-system-variants">
                    <?php foreach (['link-1', 'link-2'] as $variant) : ?>
                        <div class="design-system-variant">
                            <h3><code><?= esc_html($variant) ?></code></h3>
                            <ul class="design-system-preview" role="list">
                                <?php foreach ($link_examples as $example) : ?>
                                    <li>
                                        <?php component('link', array_replace($example, ['classes' => $variant])); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-link">
                <h3 class="design-system-readme-title" id="readme-link">README</h3>
                <?= theme_design_system_readme('link') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['list'])) : ?><section class="design-system-section design-system-section--component" id="component-list">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>list</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('list', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('list', [
                        'items' => $design_system_news,
                        'card' => 'news',
                        'classes' => 'design-system-list',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-list">
                <h3 class="design-system-readme-title" id="readme-list">README</h3>
                <?= theme_design_system_readme('list') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['pagination'])) : ?><section class="design-system-section design-system-section--component" id="component-pagination">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>pagination</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('pagination', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('pagination', [
                        'total' => 8,
                        'current' => 3,
                        'prev_text' => 'Précédent',
                        'next_text' => 'Suivant',
                        'aria_label' => 'Pagination de démonstration',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-pagination">
                <h3 class="design-system-readme-title" id="readme-pagination">README</h3>
                <?= theme_design_system_readme('pagination') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['breadcrumb'])) : ?><section class="design-system-section design-system-section--component" id="component-breadcrumb">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>breadcrumb</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('breadcrumb', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('breadcrumb', [
                        'items' => [
                            ['name' => 'Accueil', 'url' => home_url('/')],
                            ['name' => 'Actualités', 'url' => home_url('/news/')],
                            ['name' => 'Titre de l’actualité'],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-breadcrumb">
                <h3 class="design-system-readme-title" id="readme-breadcrumb">README</h3>
                <?= theme_design_system_readme('breadcrumb') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['picto'])) : ?><section class="design-system-section design-system-section--component" id="component-picto">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>picto</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('picto', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('picto', ['name' => 'email', 'label' => 'email']); ?>
                    <?php component('picto', ['name' => 'linkedin', 'label' => 'email']); ?>

                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-picto">
                <h3 class="design-system-readme-title" id="readme-picto">README</h3>
                <?= theme_design_system_readme('picto') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['picture'])) : ?><section class="design-system-section design-system-section--component" id="component-picture">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>picture</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('picture', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--media">
                    <?php component('picture', [
                        'images' => [
                            'desktop' => $image_desktop,
                            'mobile' => $image_mobile,
                        ],
                        'classes' => 'design-system-picture',
                        'breakpoint' => 768,
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-picture">
                <h3 class="design-system-readme-title" id="readme-picture">README</h3>
                <?= theme_design_system_readme('picture') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['select-lang'])) : ?><section class="design-system-section design-system-section--component" id="component-select-lang">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>select-lang</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('select-lang', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('select-lang', [
                        'languages' => [
                            ['code' => 'fr', 'label' => 'FR', 'url' => home_url('/'), 'current' => true],
                            ['code' => 'en', 'label' => 'EN', 'url' => home_url('/?lang=en')],
                            ['code' => 'de', 'label' => 'DE', 'url' => home_url('/?lang=de'), 'disabled' => true],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-select-lang">
                <h3 class="design-system-readme-title" id="readme-select-lang">README</h3>
                <?= theme_design_system_readme('select-lang') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['search'])) : ?><section class="design-system-section design-system-section--component" id="component-search">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>search</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('search', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('search', ['placeholder' => 'Rechercher dans le site']); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-search">
                <h3 class="design-system-readme-title" id="readme-search">README</h3>
                <?= theme_design_system_readme('search') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['select'])) : ?><section class="design-system-section design-system-section--component" id="component-select">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>select</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('select', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('select', [
                        'name' => 'native-city',
                        'label' => 'Ville — select natif',
                        'placeholder' => 'Choisir une ville',
                        'options' => $design_system_options,
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-select">
                <h3 class="design-system-readme-title" id="readme-select">README</h3>
                <?= theme_design_system_readme('select') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['marquee'])) : ?><section class="design-system-section design-system-section--component" id="component-marquee">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>marquee</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('marquee', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('marquee', [
                        'items' => [
                            '<strong>Brand strategy</strong>',
                            '<span>Design</span>',
                            '<a href="#component-marquee">Digital experience</a>',
                            '<span>Content</span>',
                            '<span>Innovation</span>',
                        ],
                        'speed' => 60,
                        'aria_label' => 'Expertises de l’agence',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-marquee">
                <h3 class="design-system-readme-title" id="readme-marquee">README</h3>
                <?= theme_design_system_readme('marquee') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['newsletter'])) : ?><section class="design-system-section design-system-section--component" id="component-newsletter">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>newsletter</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('newsletter', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('newsletter', [
                        'title' => 'Newsletter',
                        'text' => '<p>Recevez occasionnellement nos dernières actualités.</p>',
                        'action' => 'https://example.test/newsletter/',
                        'email_label' => 'Adresse e-mail',
                        'button_label' => 'S’inscrire',
                        'mandatory_msg' => 'Saisissez votre adresse e-mail.',
                        'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-newsletter">
                <h3 class="design-system-readme-title" id="readme-newsletter">README</h3>
                <?= theme_design_system_readme('newsletter') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['select-custom'])) : ?><section class="design-system-section design-system-section--component" id="component-select-custom">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>select-custom</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('select-custom', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('select-custom', [
                        'name' => 'custom-city',
                        'label' => 'Ville — select personnalisé',
                        'placeholder' => 'Choisir une ville',
                        'options' => $design_system_options,
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-select-custom">
                <h3 class="design-system-readme-title" id="readme-select-custom">README</h3>
                <?= theme_design_system_readme('select-custom') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['shares'])) : ?><section class="design-system-section design-system-section--component" id="component-shares">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>shares</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('shares', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('shares', [
                        'title' => 'Partager cette page',
                        'list' => ['email', 'linkedin'],
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-shares">
                <h3 class="design-system-readme-title" id="readme-shares">README</h3>
                <?= theme_design_system_readme('shares') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['socials'])) : ?><section class="design-system-section design-system-section--component" id="component-socials">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>socials</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('socials', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('socials', [
                        'items' => [
                            ['network' => 'linkedin', 'url' => 'https://www.linkedin.com/'],
                            ['network' => 'facebook', 'url' => 'https://www.facebook.com/'],
                            ['network' => 'instagram', 'url' => 'https://www.instagram.com/'],
                        ],
                        'aria_label' => 'Réseaux sociaux de démonstration',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-socials">
                <h3 class="design-system-readme-title" id="readme-socials">README</h3>
                <?= theme_design_system_readme('socials') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['slider'])) : ?><section class="design-system-section design-system-section--component" id="component-slider">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>slider</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('slider', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('slider', [
                        'items' => [
                            '<article class="design-system-slide"><strong>Slide 1</strong><p>Premier contenu factice.</p></article>',
                            '<article class="design-system-slide"><strong>Slide 2</strong><p>Deuxième contenu factice.</p></article>',
                            '<article class="design-system-slide"><strong>Slide 3</strong><p>Troisième contenu factice.</p></article>',
                        ],
                        'card' => null,
                        'navigation' => true,
                        'pagination' => true,
                        'aria_label' => 'Exemple de slider',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-slider">
                <h3 class="design-system-readme-title" id="readme-slider">README</h3>
                <?= theme_design_system_readme('slider') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['tag'])) : ?><section class="design-system-section design-system-section--component" id="component-tag">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>tag</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('tag', 'components')) ?></p>
                <div class="design-system-preview">
                    <?php component('tag', ['name' => 'Information']); ?>
                    <?php component('btn', [
                        'name' => 'Action',
                        'classes' => 'tag',
                    ]); ?>
                    <?php component('link', [
                        'link' => ['url' => '#component-tag', 'title' => 'Tag lien'],
                        'classes' => 'tag',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-tag">
                <h3 class="design-system-readme-title" id="readme-tag">README</h3>
                <?= theme_design_system_readme('tag') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['text'])) : ?><section class="design-system-section design-system-section--component" id="component-text">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>text</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('text', 'components')) ?></p>
                <h3 class="design-system-example-title"><code>.intro</code></h3>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('text', [
                        'classes' => 'intro',
                        'text' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>',
                    ]); ?>
                </div>
                <h3 class="design-system-example-title"><code>.rte</code></h3>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('text', [
                        'text' => require __DIR__ . '/text-example.php',
                        'classes' => 'rte',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-text">
                <h3 class="design-system-readme-title" id="readme-text">README</h3>
                <?= theme_design_system_readme('text') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['tab'])) : ?><section class="design-system-section design-system-section--component" id="component-tab">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>tab</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('tab', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('tab', [
                        'title' => 'Informations pratiques',
                        'items' => [
                            ['title' => 'Présentation', 'content' => '<p>Présentation générale du service.</p>'],
                            ['title' => 'Horaires', 'content' => '<p>Du lundi au vendredi, de 9 h à 18 h.</p>'],
                            ['title' => 'Contact', 'content' => '<p>Contactez notre équipe pour en savoir plus.</p>'],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-tab">
                <h3 class="design-system-readme-title" id="readme-tab">README</h3>
                <?= theme_design_system_readme('tab') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['table'])) : ?><section class="design-system-section design-system-section--component" id="component-table">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>table</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('table', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--block">
                    <?php component('table', [
                        'caption' => 'Disponibilités hebdomadaires',
                        'headers' => ['Jour', 'Matin', 'Après-midi'],
                        'rows' => [
                            ['Lundi', '9 h – 12 h', '14 h – 18 h'],
                            ['Mardi', '9 h – 12 h', '14 h – 18 h'],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-table">
                <h3 class="design-system-readme-title" id="readme-table">README</h3>
                <?= theme_design_system_readme('table') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['title'])) : ?><section class="design-system-section design-system-section--component" id="component-title">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>title</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('title', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--stack">
                    <?php component('title', ['title' => 'Titre de niveau 1', 'hx' => 1, 'classes' => 'title-1']); ?>
                    <?php component('title', ['title' => 'Titre de niveau 2', 'hx' => 2, 'classes' => 'title-2']); ?>
                    <?php component('title', ['title' => 'Titre de niveau 3', 'hx' => 3, 'classes' => 'title-3']); ?>
                    <?php component('title', ['title' => 'Titre avec un mot <strong>important</strong>', 'hx' => 4, 'classes' => 'title-4']); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-title">
                <h3 class="design-system-readme-title" id="readme-title">README</h3>
                <?= theme_design_system_readme('title') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['tooltip'])) : ?><section class="design-system-section design-system-section--component" id="component-tooltip">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>tooltip</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('tooltip', 'components')) ?></p>
                <div class="design-system-preview">
                    <span>Survolez ou focalisez&nbsp;:</span>
                    <?php component('tooltip', [
                        'label' => 'API',
                        'content' => 'Application Programming Interface',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-tooltip">
                <h3 class="design-system-readme-title" id="readme-tooltip">README</h3>
                <?= theme_design_system_readme('tooltip') ?>
            </aside>
        </section><?php endif; ?>

        <?php if (isset($design_system_sections['video'])) : ?><section class="design-system-section design-system-section--component" id="component-video">
            <div class="design-system-demo">
                <h2 class="design-system-section-title"><code>video</code></h2>
                <p class="design-system-note"><?= esc_html(theme_design_system_version('video', 'components')) ?></p>
                <div class="design-system-preview design-system-preview--media">
                    <?php component('video', [
                        'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'title' => 'Exemple du composant vidéo',
                    ]); ?>
                </div>
            </div>
            <aside class="design-system-readme" aria-labelledby="readme-video">
                <h3 class="design-system-readme-title" id="readme-video">README</h3>
                <?= theme_design_system_readme('video') ?>
            </aside>
        </section><?php endif; ?>
    </div>
