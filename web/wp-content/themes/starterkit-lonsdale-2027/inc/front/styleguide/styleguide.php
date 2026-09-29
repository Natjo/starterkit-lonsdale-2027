<?php
$styleguide_sections = [
    'accordion' => 'Accordion',
    'autocomplete' => 'Autocomplete',
    'badge' => 'Badge',
    'btn' => 'Button',
    'date' => 'Date',
    'dialog' => 'Dialog',
    'eyebrow' => 'Eyebrow',
    'field' => 'Field',
    'header' => 'Header',
    'icon' => 'Icon',
    'image' => 'Image',
    'intro' => 'Intro',
    'link' => 'Link',
    'list' => 'List',
    'marquee' => 'Marquee',
    'newsletter' => 'Newsletter',
    'pagination' => 'Pagination',
    'picto' => 'Picto',
    'picture' => 'Picture',
    'quote' => 'Quote',
    'search' => 'Search',
    'select' => 'Select',
    'select-custom' => 'Select custom',
    'select-lang' => 'Select language',
    'shares' => 'Shares',
    'slider' => 'Slider',
    'socials' => 'Socials',
    'tag' => 'Tag',
    'tab' => 'Tabs',
    'table' => 'Table',
    'text' => 'Text',
    'title' => 'Title',
    'tooltip' => 'Tooltip',
    'video' => 'Video',
];

$image_desktop = rtrim(THEME_URL, '/') . '/inc/front/styleguide/429-1240x800.jpg';
$image_mobile = rtrim(THEME_ASSETS, '/') . '/inc/front/styleguide/429-1240x800.jpg';
$styleguide_options = [
    ['value' => 'paris', 'label' => 'Paris', 'alt' => 'Île-de-France'],
    ['value' => 'lyon', 'label' => 'Lyon', 'alt' => 'Rhône'],
    ['value' => 'nantes', 'label' => 'Nantes', 'alt' => 'Loire-Atlantique'],
];
$styleguide_news = [
    [
        'source' => 'Lonsdale',
        'date' => '20 juillet 2026',
        'datetime' => '2026-07-20',
        'title' => 'Une actualité présentée dans une carte',
        'text' => '<p>Un contenu factice pour vérifier le composant dans une liste.</p>',
        'link' => [
            'url' => '#component-list',
            'title' => 'Lire l’actualité',
        ],
    ],
    [
        'source' => 'Design system',
        'date' => '18 juillet 2026',
        'datetime' => '2026-07-18',
        'title' => 'Un deuxième exemple de contenu',
        'text' => '<p>La répétition permet de contrôler la grille et les espacements.</p>',
        'link' => [
            'url' => '#component-list',
            'title' => 'Voir le détail',
        ],
    ],
    [
        'source' => 'Design system',
        'date' => '18 juillet 2026',
        'datetime' => '2026-07-18',
        'title' => 'Un deuxième exemple de contenu',
        'text' => '<p>La répétition permet de contrôler la grille et les espacements.</p>',
        'link' => [
            'url' => '#component-list',
            'title' => 'Voir le détail',
        ],
    ],
];
?>

<main class="styleguide" id="main-content">
    <header class="styleguide-intro">
        <p class="styleguide-kicker">
            <span>Design system</span>
            <span><?= esc_html((string) count($styleguide_sections)) ?> composants</span>
        </p>
        <h1>Styleguide</h1>
        <p class="styleguide-lead">
            Inventaire fonctionnel des composants PHP du thème, présenté avec
            des données factices pour contrôler leur rendu et leurs états.
        </p>
    </header>

    <nav class="styleguide-nav" aria-label="Sommaire des composants">
        <span class="styleguide-nav-label" aria-hidden="true">Index</span>
        <ul role="list">
            <?php foreach ($styleguide_sections as $slug => $label) : ?>
                <li><a href="#component-<?= esc_attr($slug) ?>"><?= esc_html($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- cards -->
    <div class="st-components">
        <section class="styleguide-section" id="card-news">
            <h2 class="styleguide-section-title"><code>card-news</code></h2>

            <?php
            card('news', [
                'date' => '22 juillet 2026',
                'datetime' => '2026-07-22',
                'title' => 'Une actualité affichée directement dans le styleguide',
                'text' => '<p>Ce contenu factice permet de contrôler le rendu autonome de la card.</p>',
                'link' => [
                    'url' => '#card-news',
                    'title' => 'Lire l’actualité',
                ],
            ]);
            ?>
        </section>

        <section class="styleguide-section" id="card-page">
            <h2 class="styleguide-section-title"><code>card-page</code></h2>

            <?php card('page', [
                'type' => 'Page',
                'title' => 'Une page affichée dans les résultats',
                'text' => '<p>Cette card sert aussi de fallback aux nouveaux types de contenus.</p>',
                'link' => [
                    'url' => '#card-page',
                    'title' => 'Voir la page',
                ],
            ]); ?>
        </section>

        <section class="styleguide-section" id="card-keynumber">
            <h2 class="styleguide-section-title"><code>card-keynumber</code></h2>

            <?php card('keynumber', [
                'value' => '98',
                'prefix' => '+',
                'suffix' => '%',
                'title' => 'de satisfaction',
                'text' => '<p>Une card autonome alimentée par un payload personnalisé.</p>',
            ]); ?>
        </section>
    </div>


    <!-- strates -->

    <?php strate('formulaire'); ?>

    <?php strate('text_animated'); ?>

    <?php strate('paralax'); ?>

    <?php strate('keynumbers', [
        'title' => 'Nos chiffres clés',
        'text' => '<p>Une strate générique sans animation de compteur.</p>',
        'items' => [
            ['value' => '25', 'prefix' => '+', 'suffix' => 'ans', 'title' => 'd’expérience'],
            ['value' => '98', 'suffix' => '%', 'title' => 'de satisfaction'],
            ['value' => '12', 'suffix' => 'pays', 'title' => 'accompagnés'],
        ],
    ]); ?>

    <?php strate('marquee', [
        'title' => 'Strate marquee',
        'text' => '<p>La strate importe le composant et prend seule en charge son hydratation et son nettoyage.</p>',
        'items' => [
            '<strong>Brand strategy</strong>',
            '<span>Design</span>',
            '<a href="#component-marquee">Digital experience</a>',
            '<span>Content</span>',
            '<span>Innovation</span>',
        ],
        'marquee_options' => [
            'speed' => 60,
            'scroll_acceleration' => true,
            'follow_scroll_direction' => true,
            'aria_label' => 'Expertises de la strate marquee',
        ],
    ]); ?>

    <?php strate('slider', [
        'title' => 'Strate slider',
        'text' => '<p>Un exemple complet de la strate avec son introduction et des cartes actualité.</p>',
        'items' => $styleguide_news,
        'card' => 'news',
        'slider_options' => [
            'navigation' => true,
            'pagination' => true,
            'timeline' => false,
            'aria_label' => 'Actualités à découvrir',
        ],
    ]); ?>

    <div class="st-components">
        <section class="styleguide-section" id="component-accordion">
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
        </section>

        <section class="styleguide-section" id="component-autocomplete">
            <h2 class="styleguide-section-title"><code>autocomplete</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('autocomplete', [
                    'name' => 'city',
                    'label' => 'Ville',
                    'placeholder' => 'Commencez à saisir une ville',
                    'options' => $styleguide_options,
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-badge">
            <h2 class="styleguide-section-title"><code>badge</code></h2>
            <div class="styleguide-preview">
                <?php component('badge', ['name' => 'Nouveau']); ?>
                <?php component('badge', ['name' => 'À la une']); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-btn">
            <h2 class="styleguide-section-title"><code>btn</code></h2>
            <div class="styleguide-preview">
                <?php component('btn', ['name' => 'Bouton simple']); ?>
                <?php component('btn', [
                    'name' => 'Bouton avec icône',
                    'classes' => 'btn-1',
                    'icon' => ['name' => 'blank', 'width' => 18, 'height' => 18],
                ]); ?>
                <?php component('btn', [
                    'link' => ['url' => '#component-link', 'title' => 'Bouton lien'],
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-date">
            <h2 class="styleguide-section-title"><code>date</code></h2>
            <div class="styleguide-preview">
                <?php component('date', [
                    'date' => '20 juillet 2026',
                    'datetime' => '2026-07-20',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-quote">
            <h2 class="styleguide-section-title"><code>quote</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('quote', [
                    'quote' => '<p>Le design rend les usages complexes simples et désirables.</p>',
                    'author' => 'Équipe design',
                    'source' => 'Design system',
                    'url' => '#component-quote',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-dialog">
            <h2 class="styleguide-section-title"><code>dialog</code></h2>
            <div class="styleguide-preview">
                <?php component('dialog', [
                    'title' => 'Exemple de fenêtre modale',
                    'content' => '<p>Ce contenu factice permet de tester l’ouverture, la fermeture et la restitution du focus.</p>',
                    'trigger' => ['type' => 'btn', 'label' => 'Ouvrir le dialogue'],
                    'close_label' => 'Fermer',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-eyebrow">
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
        </section>

        <section class="styleguide-section" id="component-header">
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
        </section>

        <section class="styleguide-section" id="component-field">
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
        </section>

        <section class="styleguide-section" id="component-icon">
            <h2 class="styleguide-section-title"><code>icon</code></h2>
            <div class="styleguide-preview">
                <?php component('icon', ['name' => 'email', 'width' => 44, 'height' => 44, 'label' => 'Étoile']); ?>
                <?php component('icon', ['name' => 'linkedin', 'width' => 44, 'height' => 44, 'label' => 'Conversation']); ?>
              
            </div>
        </section>

        <section class="styleguide-section" id="component-image">
            <h2 class="styleguide-section-title"><code>image</code></h2>
            <div class="styleguide-preview styleguide-preview--media">
                <?php component('image', [
                    'image' => $image_desktop,
                    'alt' => 'Exemple visuel du composant image',
                    'classes' => 'styleguide-image',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-link">
            <h2 class="styleguide-section-title"><code>link</code></h2>
            <div class="styleguide-preview">
                <?php component('link', [
                    'link' => ['url' => '#component-link', 'title' => 'Lien simple'],
                ]); ?>
                <?php component('link', [
                    'link' => ['url' => '#component-link', 'title' => 'Lien avec icône'],
                    'classes' => 'link-2',
                    'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16],
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-intro">
            <h2 class="styleguide-section-title"><code>intro</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('intro', [
                    'text' => '<p>Une introduction éditoriale mise en avant avant le contenu principal.</p>',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-list">
            <h2 class="styleguide-section-title"><code>list</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('list', [
                    'items' => $styleguide_news,
                    'card' => 'news',
                    'classes' => 'styleguide-list',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-pagination">
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
        </section>

        <section class="styleguide-section" id="component-breadcrumb">
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
        </section>

        <section class="styleguide-section" id="component-picto">
            <h2 class="styleguide-section-title"><code>picto</code></h2>
            <div class="styleguide-preview">
                <?php component('picto', ['name' => 'email', 'label' => 'email']); ?>
                <?php component('picto', ['name' => 'linkedin', 'label' => 'email']); ?>

            </div>
        </section>

        <section class="styleguide-section" id="component-picture">
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
        </section>

        <section class="styleguide-section" id="component-select-lang">
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
        </section>

        <section class="styleguide-section" id="component-search">
            <h2 class="styleguide-section-title"><code>search</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('search', ['placeholder' => 'Rechercher dans le site']); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-select">
            <h2 class="styleguide-section-title"><code>select</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('select', [
                    'name' => 'native-city',
                    'label' => 'Ville — select natif',
                    'placeholder' => 'Choisir une ville',
                    'options' => $styleguide_options,
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-marquee">
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
        </section>

        <section class="styleguide-section" id="component-newsletter">
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
        </section>

        <section class="styleguide-section" id="component-select-custom">
            <h2 class="styleguide-section-title"><code>select-custom</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('select-custom', [
                    'name' => 'custom-city',
                    'label' => 'Ville — select personnalisé',
                    'placeholder' => 'Choisir une ville',
                    'options' => $styleguide_options,
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-shares">
            <h2 class="styleguide-section-title"><code>shares</code></h2>
            <div class="styleguide-preview">
                <?php component('shares', [
                    'title' => 'Partager cette page',
                    'list' => ['email', 'linkedin'],
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-socials">
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
        </section>

        <section class="styleguide-section" id="component-slider">
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
        </section>

        <section class="styleguide-section" id="component-tag">
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
        </section>

        <section class="styleguide-section" id="component-text">
            <h2 class="styleguide-section-title"><code>text</code></h2>
            <div class="styleguide-preview styleguide-preview--block">
                <?php component('text', [
                    'text' => '<p>Un texte éditorial avec une mise en <strong>valeur</strong>, un <em>emphase</em> et un <a href="#component-text">lien</a>.</p>',
                    'classes' => 'rte',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-tab">
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
        </section>

        <section class="styleguide-section" id="component-table">
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
        </section>

        <section class="styleguide-section" id="component-title">
            <h2 class="styleguide-section-title"><code>title</code></h2>
            <div class="styleguide-preview styleguide-preview--stack">
                <?php component('title', ['title' => 'Titre de niveau 1', 'hx' => 1, 'classes' => 'title-1']); ?>
                <?php component('title', ['title' => 'Titre de niveau 2', 'hx' => 2, 'classes' => 'title-2']); ?>
                <?php component('title', ['title' => 'Titre de niveau 3', 'hx' => 3, 'classes' => 'title-3']); ?>
                <?php component('title', ['title' => 'Titre avec un mot <strong>important</strong>', 'hx' => 4, 'classes' => 'title-4']); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-tooltip">
            <h2 class="styleguide-section-title"><code>tooltip</code></h2>
            <div class="styleguide-preview">
                <span>Survolez ou focalisez&nbsp;:</span>
                <?php component('tooltip', [
                    'label' => 'API',
                    'content' => 'Application Programming Interface',
                ]); ?>
            </div>
        </section>

        <section class="styleguide-section" id="component-video">
            <h2 class="styleguide-section-title"><code>video</code></h2>
            <div class="styleguide-preview styleguide-preview--media">
                <?php component('video', [
                    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'title' => 'Exemple du composant vidéo',
                ]); ?>
            </div>
        </section>
    </div>
</main>
