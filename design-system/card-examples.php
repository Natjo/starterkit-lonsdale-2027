<?php
/** Trois longueurs de contenu pour comparer les cards, sans créer de posts. */
$examples = [
    ['title' => 'Imaginer demain', 'text' => '<p>Une idée, un nouvel horizon.</p>', 'type' => 'Création', 'date' => '12 novembre 2026', 'datetime' => '2026-11-12', 'value' => '25', 'prefix' => '', 'suffix' => '+', 'quote' => '<p>Une équipe à l’écoute et des idées qui font la différence.</p>', 'author' => 'Camille Martin', 'source' => 'Atelier Horizon'],
    ['title' => 'Des expériences qui rapprochent les marques et leurs publics', 'text' => '<p>De la stratégie à la réalisation, nous imaginons des expériences utiles et accessibles, pensées pour durer et accompagner chaque usage.</p>', 'type' => 'Stratégie', 'date' => '5 octobre 2026', 'datetime' => '2026-10-05', 'value' => '98', 'prefix' => '', 'suffix' => '%', 'quote' => '<p>Une collaboration enrichissante, de la première rencontre au lancement. Le résultat traduit parfaitement notre ambition et les attentes de nos publics.</p>', 'author' => 'Alex Bernard', 'source' => 'Maison Commune'],
    ['title' => 'Une nouvelle perspective', 'text' => '<p>Explorer de nouvelles pistes et donner forme à une vision singulière, avec simplicité et précision.</p>', 'type' => 'Design', 'date' => '18 septembre 2026', 'datetime' => '2026-09-18', 'value' => '120', 'prefix' => '+', 'suffix' => '', 'quote' => '<p>Un regard neuf, une méthode claire et un accompagnement précieux à chaque étape.</p>', 'author' => 'Lou Dubois', 'source' => 'Studio Traverses'],
];
if ($name === 'team') {
    foreach (['Camille Martin' => 'Direction artistique', 'Alex Bernard' => 'Design et stratégie de marque', 'Lou Dubois' => 'Développement créatif'] as $person => $role) {
        $index = array_search($person, array_column($examples, 'author'), true);
        $examples[$index]['title'] = $person;
        $examples[$index]['text'] = '<p>' . $role . '</p>';
        $examples[$index]['image'] = rtrim(THEME_ASSETS, '/') . '/img/placeholders/card-team-' . ($index + 1) . '.png';
    }
}
return array_map(static function (array $example) use ($payload): array {
    $result = array_replace($payload, $example);
    if (is_array($payload['link'] ?? null)) $result['link'] = array_replace($payload['link'], ['title' => $example['title']]);
    return $result;
}, $examples);
