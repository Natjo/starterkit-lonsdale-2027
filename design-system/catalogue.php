<?php
/** Données de démonstration explicites, sans modifier les contenus du site. */
$base = [
    'title' => 'Un exemple éditorial',
    'text' => '<p>Une présentation du savoir-faire et des engagements de la marque.</p>',
    'image' => $image_desktop,
    'images' => ['desktop' => $image_desktop, 'mobile' => $image_mobile],
    'link' => ['url' => '#main-content', 'title' => 'Découvrir'],
];
$cards = [];
foreach (['expertise', 'keynumber', 'news', 'page', 'project', 'slide', 'team', 'testimonial'] as $name) {
    $cards[$name] = array_replace($base, ['title' => 'Exemple ' . $name, 'type' => 'Design', 'date' => '10 novembre 2026', 'datetime' => '2026-11-10']);
}
$cards['keynumber'] = ['value' => '98', 'prefix' => '+', 'suffix' => '%', 'title' => 'de satisfaction', 'text' => '<p>Un indicateur pour illustrer la card.</p>'];
$cards['team']['title'] = 'Jane Doe';
$cards['team']['text'] = '<p>Creative director</p>';
$cards['testimonial'] = ['quote' => '<p>Une collaboration remarquable, du premier échange au lancement.</p>', 'author' => 'Jane Doe', 'source' => 'Design studio', 'image' => $image_desktop];
$slides = [array_replace($base, ['title' => 'Stratégie']), array_replace($base, ['title' => 'Création']), array_replace($base, ['title' => 'Expérience'])];
$news_ids = get_posts(['post_type' => 'news', 'post_status' => 'publish', 'posts_per_page' => 3, 'fields' => 'ids']);
$demo_logos = [];
foreach (['Airbnb', 'Apple', 'Discord', 'Dropbox', 'Figma', 'GitHub', 'Google', 'LinkedIn', 'Microsoft', 'Slack', 'Spotify', 'YouTube'] as $logo_name) {
    $demo_logos[] = [
        'name' => $logo_name,
        'image' => rtrim(THEME_ASSETS, '/') . '/img/placeholders/logos/' . strtolower($logo_name) . '.svg',
    ];
}
$strates = [
    'accordion' => array_replace($base, ['items' => [
        ['title' => 'Notre approche', 'text' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec ullamcorper nulla non metus auctor fringilla. Maecenas faucibus mollis interdum. Integer posuere erat a ante venenatis dapibus posuere velit aliquet.</p>'],
        ['title' => 'Notre équipe', 'text' => '<p>Curabitur blandit tempus porttitor. Aenean lacinia bibendum nulla sed consectetur. Nullam quis risus eget urna mollis ornare vel eu leo. Cras mattis consectetur purus sit amet fermentum.</p><p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Etiam porta sem malesuada magna mollis euismod. Vestibulum id ligula porta felis euismod semper.</p><p>Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Sed posuere consectetur est at lobortis. Nulla vitae elit libero, a pharetra augue.</p>'],
    ]]),
    'columns-content' => ['columns' => array_slice($slides, 0, 2)],
    'image' => ['image' => rtrim(THEME_ASSETS, '/') . '/img/placeholders/strate-image.jpg'],
    'intro' => ['text' => '<p>Nous concevons des expériences de marque claires, utiles et singulières.</p>'],
    'keynumbers' => array_replace($base, ['items' => [
        ['value' => '98', 'suffix' => '%', 'title' => 'de satisfaction'],
        ['value' => '25', 'suffix' => '+', 'title' => 'années d’expérience'],
    ]]),
    'logos' => array_replace($base, ['title' => 'Exemples de logos', 'text' => '<p>Une sélection de logos pour illustrer la grille.</p>', 'logos' => $demo_logos]),
    'marquee' => ['content_type' => 'images', 'images' => array_map(static fn(array $logo): array => ['image' => $logo['image']], $demo_logos), 'marquee_options' => ['speed' => 30]],
    'media' => ['media_type' => 'image', 'image' => $image_desktop],
    'push' => $base,
    'pushes' => array_replace($base, ['items' => $news_ids]),
    'pushes-auto' => array_replace($base, ['post_type' => 'news', 'limit' => 3]),
    'pushes-more' => array_replace($base, ['post_type' => 'news', 'limit' => 2, 'more_label' => 'Voir plus']),
    'pushes-slider' => array_replace($base, ['post_type' => 'news', 'limit' => 6]),
    'quote' => ['text' => '<p>Le design rend les usages complexes simples et désirables.</p>', 'author' => 'Jane Doe', 'source' => 'Design studio'],
    'slider-cards' => array_replace($base, ['items' => $slides]),
    'slider-images' => array_replace($base, ['images' => [['image' => $image_desktop], ['image' => $image_mobile]]]),
    'snap' => ['slides' => $slides],
    'steps' => array_replace($base, ['steps' => $slides]),
    'text' => $base,
    'text-animated' => [],
    'text-image' => $base,
    'video' => ['video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'video_title' => 'Exemple vidéo', 'poster' => $image_desktop],
];
$heros = [];
foreach (['flexible', 'homepage', 'page', 'post'] as $name) {
    $heros[$name] = array_replace($base, ['suptitle' => 'Design system', 'title' => 'Hero ' . $name, 'date' => '2026-11-10']);
}
return ['cards' => $cards, 'strates' => $strates, 'heros' => $heros];
