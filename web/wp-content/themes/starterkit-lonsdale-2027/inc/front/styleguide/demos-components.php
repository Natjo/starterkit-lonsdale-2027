<div class="st-components">
        <section class="styleguide-section styleguide-section--component" id="component-accordion">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>accordion</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('accordion', [
                        'items' => [
                            ['title' => 'À quoi sert ce composant ?', 'text' => '<p>Il présente plusieurs contenus repliables accessibles.</p>'],
                            ['title' => 'Peut-on ouvrir plusieurs panneaux ?', 'text' => '<p>Oui, en activant le mode multiple lors de l’appel.</p>'],
                            ['title' => 'Le contenu accepte-t-il du HTML ?', 'text' => '<p>Oui, le HTML éditorial est filtré par WordPress.</p>'],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-accordion">
                <h3 class="styleguide-readme-title" id="readme-accordion">README</h3>
                <?= theme_styleguide_readme('accordion') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-autocomplete">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>autocomplete</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('autocomplete', [
                        'name' => 'city',
                        'label' => 'Ville',
                        'placeholder' => 'Commencez à saisir une ville',
                        'options' => $styleguide_options,
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-autocomplete">
                <h3 class="styleguide-readme-title" id="readme-autocomplete">README</h3>
                <?= theme_styleguide_readme('autocomplete') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-badge">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>badge</code></h2>
                <div class="styleguide-preview">
                    <?php component('badge', ['name' => 'Nouveau']); ?>
                    <?php component('badge', ['name' => 'À la une']); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-badge">
                <h3 class="styleguide-readme-title" id="readme-badge">README</h3>
                <?= theme_styleguide_readme('badge') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-btn">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>btn</code></h2>
                <?php
                $button_examples = [
                    ['name' => 'Bouton simple'],
                    ['name' => 'Bouton avec icône', 'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16]],
                    ['name' => 'Bouton avec SVG', 'svg' => true],
                    ['name' => 'Bouton désactivé', 'attributes' => ['disabled' => true]],
                ];
                ?>
                <div class="styleguide-variants">
                    <?php foreach (['btn-1', 'btn-2'] as $variant) : ?>
                        <div class="styleguide-variant">
                            <h3><code><?= esc_html($variant) ?></code></h3>
                            <ul class="styleguide-preview" role="list">
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
            <aside class="styleguide-readme" aria-labelledby="readme-btn">
                <h3 class="styleguide-readme-title" id="readme-btn">README</h3>
                <?= theme_styleguide_readme('btn') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-date">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>date</code></h2>
                <div class="styleguide-preview">
                    <?php component('date', [
                        'date' => '20 juillet 2026',
                        'datetime' => '2026-07-20',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-date">
                <h3 class="styleguide-readme-title" id="readme-date">README</h3>
                <?= theme_styleguide_readme('date') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-quote">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>quote</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('quote', [
                        'quote' => '<p>Le design rend les usages complexes simples et désirables.</p>',
                        'author' => 'Équipe design',
                        'source' => 'Design system',
                        'url' => '#component-quote',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-quote">
                <h3 class="styleguide-readme-title" id="readme-quote">README</h3>
                <?= theme_styleguide_readme('quote') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-dialog">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>dialog</code></h2>
                <div class="styleguide-preview">
                    <?php component('dialog', [
                        'title' => 'Exemple de fenêtre modale',
                        'content' => '<p>Ce contenu factice permet de tester l’ouverture, la fermeture et la restitution du focus.</p>',
                        'trigger' => ['type' => 'btn', 'label' => 'Ouvrir le dialogue'],
                        'close_label' => 'Fermer',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-dialog">
                <h3 class="styleguide-readme-title" id="readme-dialog">README</h3>
                <?= theme_styleguide_readme('dialog') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-eyebrow">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>eyebrow</code></h2>
                <div class="styleguide-preview">
                    <?php component('eyebrow', ['eyebrow' => 'Sans icône']); ?>
                    <?php component('eyebrow', [
                        'eyebrow' => 'Avec une <strong>icône</strong>',
                        'icon' => 'logo',
                        'icon_width' => 54,
                        'icon_height' => 55,

                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-eyebrow">
                <h3 class="styleguide-readme-title" id="readme-eyebrow">README</h3>
                <?= theme_styleguide_readme('eyebrow') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-header">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>header</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
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
            <aside class="styleguide-readme" aria-labelledby="readme-header">
                <h3 class="styleguide-readme-title" id="readme-header">README</h3>
                <?= theme_styleguide_readme('header') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-field">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>field</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
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
            <aside class="styleguide-readme" aria-labelledby="readme-field">
                <h3 class="styleguide-readme-title" id="readme-field">README</h3>
                <?= theme_styleguide_readme('field') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-icon">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>icon</code></h2>
                <div class="styleguide-preview">
                    <?php component('icon', ['name' => 'email', 'width' => 44, 'height' => 44, 'label' => 'Étoile']); ?>
                    <?php component('icon', ['name' => 'linkedin', 'width' => 44, 'height' => 44, 'label' => 'Conversation']); ?>

                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-icon">
                <h3 class="styleguide-readme-title" id="readme-icon">README</h3>
                <?= theme_styleguide_readme('icon') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-image">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>image</code></h2>
                <div class="styleguide-preview styleguide-preview--media">
                    <?php component('image', [
                        'image' => $image_desktop,
                        'alt' => 'Exemple visuel du composant image',
                        'classes' => 'styleguide-image',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-image">
                <h3 class="styleguide-readme-title" id="readme-image">README</h3>
                <?= theme_styleguide_readme('image') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-link">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>link</code></h2>
                <?php
                $link_examples = [
                    ['link' => ['url' => '#component-link', 'title' => 'Lien simple']],
                    ['link' => ['url' => '#component-link', 'title' => 'Lien avec icône'], 'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16]],
                    ['link' => ['url' => '#component-link', 'title' => 'Lien avec SVG'], 'svg' => true],
                    ['link' => ['url' => '#component-link', 'title' => 'Lien désactivé'], 'attributes' => ['aria-disabled' => true]],
                ];
                ?>
                <div class="styleguide-variants">
                    <?php foreach (['link-1', 'link-2'] as $variant) : ?>
                        <div class="styleguide-variant">
                            <h3><code><?= esc_html($variant) ?></code></h3>
                            <ul class="styleguide-preview" role="list">
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
            <aside class="styleguide-readme" aria-labelledby="readme-link">
                <h3 class="styleguide-readme-title" id="readme-link">README</h3>
                <?= theme_styleguide_readme('link') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-list">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>list</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('list', [
                        'items' => $styleguide_news,
                        'card' => 'news',
                        'classes' => 'styleguide-list',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-list">
                <h3 class="styleguide-readme-title" id="readme-list">README</h3>
                <?= theme_styleguide_readme('list') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-pagination">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>pagination</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('pagination', [
                        'total' => 8,
                        'current' => 3,
                        'prev_text' => 'Précédent',
                        'next_text' => 'Suivant',
                        'aria_label' => 'Pagination de démonstration',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-pagination">
                <h3 class="styleguide-readme-title" id="readme-pagination">README</h3>
                <?= theme_styleguide_readme('pagination') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-breadcrumb">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>breadcrumb</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('breadcrumb', [
                        'items' => [
                            ['name' => 'Accueil', 'url' => home_url('/')],
                            ['name' => 'Actualités', 'url' => home_url('/news/')],
                            ['name' => 'Titre de l’actualité'],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-breadcrumb">
                <h3 class="styleguide-readme-title" id="readme-breadcrumb">README</h3>
                <?= theme_styleguide_readme('breadcrumb') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-picto">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>picto</code></h2>
                <div class="styleguide-preview">
                    <?php component('picto', ['name' => 'email', 'label' => 'email']); ?>
                    <?php component('picto', ['name' => 'linkedin', 'label' => 'email']); ?>

                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-picto">
                <h3 class="styleguide-readme-title" id="readme-picto">README</h3>
                <?= theme_styleguide_readme('picto') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-picture">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>picture</code></h2>
                <div class="styleguide-preview styleguide-preview--media">
                    <?php component('picture', [
                        'images' => [
                            'desktop' => $image_desktop,
                            'mobile' => $image_mobile,
                        ],
                        'classes' => 'styleguide-picture',
                        'breakpoint' => 768,
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-picture">
                <h3 class="styleguide-readme-title" id="readme-picture">README</h3>
                <?= theme_styleguide_readme('picture') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-select-lang">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>select-lang</code></h2>
                <div class="styleguide-preview">
                    <?php component('select-lang', [
                        'languages' => [
                            ['code' => 'fr', 'label' => 'FR', 'url' => home_url('/'), 'current' => true],
                            ['code' => 'en', 'label' => 'EN', 'url' => home_url('/?lang=en')],
                            ['code' => 'de', 'label' => 'DE', 'url' => home_url('/?lang=de'), 'disabled' => true],
                        ],
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-select-lang">
                <h3 class="styleguide-readme-title" id="readme-select-lang">README</h3>
                <?= theme_styleguide_readme('select-lang') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-search">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>search</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('search', ['placeholder' => 'Rechercher dans le site']); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-search">
                <h3 class="styleguide-readme-title" id="readme-search">README</h3>
                <?= theme_styleguide_readme('search') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-select">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>select</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('select', [
                        'name' => 'native-city',
                        'label' => 'Ville — select natif',
                        'placeholder' => 'Choisir une ville',
                        'options' => $styleguide_options,
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-select">
                <h3 class="styleguide-readme-title" id="readme-select">README</h3>
                <?= theme_styleguide_readme('select') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-marquee">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>marquee</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
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
            <aside class="styleguide-readme" aria-labelledby="readme-marquee">
                <h3 class="styleguide-readme-title" id="readme-marquee">README</h3>
                <?= theme_styleguide_readme('marquee') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-newsletter">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>newsletter</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
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
            <aside class="styleguide-readme" aria-labelledby="readme-newsletter">
                <h3 class="styleguide-readme-title" id="readme-newsletter">README</h3>
                <?= theme_styleguide_readme('newsletter') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-select-custom">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>select-custom</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('select-custom', [
                        'name' => 'custom-city',
                        'label' => 'Ville — select personnalisé',
                        'placeholder' => 'Choisir une ville',
                        'options' => $styleguide_options,
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-select-custom">
                <h3 class="styleguide-readme-title" id="readme-select-custom">README</h3>
                <?= theme_styleguide_readme('select-custom') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-shares">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>shares</code></h2>
                <div class="styleguide-preview">
                    <?php component('shares', [
                        'title' => 'Partager cette page',
                        'list' => ['email', 'linkedin'],
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-shares">
                <h3 class="styleguide-readme-title" id="readme-shares">README</h3>
                <?= theme_styleguide_readme('shares') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-socials">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>socials</code></h2>
                <div class="styleguide-preview">
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
            <aside class="styleguide-readme" aria-labelledby="readme-socials">
                <h3 class="styleguide-readme-title" id="readme-socials">README</h3>
                <?= theme_styleguide_readme('socials') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-slider">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>slider</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('slider', [
                        'items' => [
                            '<article class="styleguide-slide"><strong>Slide 1</strong><p>Premier contenu factice.</p></article>',
                            '<article class="styleguide-slide"><strong>Slide 2</strong><p>Deuxième contenu factice.</p></article>',
                            '<article class="styleguide-slide"><strong>Slide 3</strong><p>Troisième contenu factice.</p></article>',
                        ],
                        'card' => null,
                        'navigation' => true,
                        'pagination' => true,
                        'aria_label' => 'Exemple de slider',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-slider">
                <h3 class="styleguide-readme-title" id="readme-slider">README</h3>
                <?= theme_styleguide_readme('slider') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-tag">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>tag</code></h2>
                <div class="styleguide-preview">
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
            <aside class="styleguide-readme" aria-labelledby="readme-tag">
                <h3 class="styleguide-readme-title" id="readme-tag">README</h3>
                <?= theme_styleguide_readme('tag') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-text">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>text</code></h2>
                <h3 class="styleguide-example-title"><code>.intro</code></h3>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('text', [
                        'classes' => 'intro',
                        'text' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>',
                    ]); ?>
                </div>
                <h3 class="styleguide-example-title"><code>.rte</code></h3>
                <div class="styleguide-preview styleguide-preview--block">
                    <?php component('text', [
                        'text' => <<<'HTML'
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <strong>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</strong> Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

<h2>Consectetur adipiscing elit</h2>
<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. <em>Excepteur sint occaecat cupidatat non proident</em>, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

<h3>Sed do eiusmod tempor</h3>
<p>Voici un texte contenant du <strong>gras</strong>, de l’<em>italique</em>, du <strong><em>gras italique</em></strong> ainsi qu’un texte <del>barré</del>. On peut également ajouter un <a href="https://example.com">lien vers un site</a> au milieu d’un paragraphe.</p>
<blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante venenatis dapibus posuere velit aliquet.</p></blockquote>

<h3>Liste à puces</h3>
<ul>
    <li>Lorem ipsum dolor sit amet</li>
    <li>Consectetur adipiscing elit</li>
    <li>Sed do eiusmod tempor incididunt
        <ul>
            <li>Ut labore et dolore magna aliqua</li>
            <li>Ut enim ad minim veniam</li>
        </ul>
    </li>
    <li>Quis nostrud exercitation ullamco</li>
</ul>

<h3>Liste numérotée</h3>
<ol>
    <li>Lorem ipsum dolor sit amet</li>
    <li>Consectetur adipiscing elit</li>
    <li>Sed do eiusmod tempor incididunt</li>
    <li>Ut labore et dolore magna aliqua</li>
</ol>

<h3>Texte supplémentaire</h3>
<p>Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec ullamcorper nulla non metus auctor fringilla. Vestibulum id ligula porta felis euismod semper.</p>
<hr>

<h2>Aliquam erat volutpat</h2>
<p>Curabitur blandit tempus porttitor. Maecenas faucibus mollis interdum. <strong>Nullam quis risus eget urna mollis ornare vel eu leo.</strong> <code>Lorem ipsum dolor sit amet</code> Vestibulum id ligula porta felis euismod semper. Cras mattis consectetur purus sit amet fermentum.</p>

<h3>Tableau</h3>
<table>
    <thead>
        <tr><th>Élément</th><th>Description</th><th>Valeur</th></tr>
    </thead>
    <tbody>
        <tr><td>Lorem</td><td>Ipsum dolor sit amet</td><td>01</td></tr>
        <tr><td>Dolor</td><td>Consectetur adipiscing</td><td>02</td></tr>
        <tr><td>Amet</td><td>Sed do eiusmod tempor</td><td>03</td></tr>
    </tbody>
</table>

<h3>Citation longue</h3>
<blockquote><p>“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed posuere consectetur est at lobortis.”</p></blockquote>

<h2>Dernier niveau de contenu</h2>
<p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Aenean lacinia bibendum nulla sed consectetur. <strong>Texte important :</strong> lorem ipsum dolor sit amet, consectetur adipiscing elit. <em>Texte secondaire :</em> sed do eiusmod tempor incididunt ut labore et dolore magna aliqua</p>
HTML,
                        'classes' => 'rte',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-text">
                <h3 class="styleguide-readme-title" id="readme-text">README</h3>
                <?= theme_styleguide_readme('text') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-tab">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>tab</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
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
            <aside class="styleguide-readme" aria-labelledby="readme-tab">
                <h3 class="styleguide-readme-title" id="readme-tab">README</h3>
                <?= theme_styleguide_readme('tab') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-table">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>table</code></h2>
                <div class="styleguide-preview styleguide-preview--block">
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
            <aside class="styleguide-readme" aria-labelledby="readme-table">
                <h3 class="styleguide-readme-title" id="readme-table">README</h3>
                <?= theme_styleguide_readme('table') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-title">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>title</code></h2>
                <div class="styleguide-preview styleguide-preview--stack">
                    <?php component('title', ['title' => 'Titre de niveau 1', 'hx' => 1, 'classes' => 'title-1']); ?>
                    <?php component('title', ['title' => 'Titre de niveau 2', 'hx' => 2, 'classes' => 'title-2']); ?>
                    <?php component('title', ['title' => 'Titre de niveau 3', 'hx' => 3, 'classes' => 'title-3']); ?>
                    <?php component('title', ['title' => 'Titre avec un mot <strong>important</strong>', 'hx' => 4, 'classes' => 'title-4']); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-title">
                <h3 class="styleguide-readme-title" id="readme-title">README</h3>
                <?= theme_styleguide_readme('title') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-tooltip">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>tooltip</code></h2>
                <div class="styleguide-preview">
                    <span>Survolez ou focalisez&nbsp;:</span>
                    <?php component('tooltip', [
                        'label' => 'API',
                        'content' => 'Application Programming Interface',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-tooltip">
                <h3 class="styleguide-readme-title" id="readme-tooltip">README</h3>
                <?= theme_styleguide_readme('tooltip') ?>
            </aside>
        </section>

        <section class="styleguide-section styleguide-section--component" id="component-video">
            <div class="styleguide-demo">
                <h2 class="styleguide-section-title"><code>video</code></h2>
                <div class="styleguide-preview styleguide-preview--media">
                    <?php component('video', [
                        'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'title' => 'Exemple du composant vidéo',
                    ]); ?>
                </div>
            </div>
            <aside class="styleguide-readme" aria-labelledby="readme-video">
                <h3 class="styleguide-readme-title" id="readme-video">README</h3>
                <?= theme_styleguide_readme('video') ?>
            </aside>
        </section>
    </div>
