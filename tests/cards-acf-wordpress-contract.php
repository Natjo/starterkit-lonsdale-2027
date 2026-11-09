<?php
/** Run through wp eval-file; fixtures use only the non-persistent object cache. */
if (wp_using_ext_object_cache()) throw new RuntimeException('Cache objet persistant : fixtures refusées.');
$render = static function (string $name, mixed $args, bool $strate = false): string {
    ob_start();
    try {
        if ($strate) strate($name, $args);
        else card($name, $args);
        return (string) ob_get_contents();
    } finally { ob_end_clean(); }
};
$assert = static function (bool $condition, string $label): void {
    if (!$condition) throw new RuntimeException($label);
};
foreach (['keynumber', 'testimonial', 'slide'] as $name) {
    $group = acf_get_field_group('group_card_' . $name);
    $fields = $group ? acf_get_fields($group) : [];
    $assert(count($fields) === 1 && $fields[0]['name'] === 'card', 'Groupe manquant : ' . $name);
}
$number = ['card' => ['value' => '98', 'prefix' => '+', 'suffix' => '%', 'title' => 'AUDIT_NUMBER']];
$assert(str_contains($render('keynumber', $number), 'AUDIT_NUMBER'), 'Card Keynumber imbriquée');
$assert(str_contains($render('keynumbers', ['items' => [$number]], true), 'AUDIT_NUMBER'), 'Répéteur Keynumbers imbriqué');
$slide = ['card' => ['title' => 'AUDIT_SLIDE', 'text' => '<p>AUDIT_SLIDE_TEXT</p>', 'link' => ['url' => '/audit-slide/', 'title' => 'Voir']]];
$slide_html = $render('slide', $slide);
$assert(str_contains($slide_html, 'AUDIT_SLIDE_TEXT') && str_contains($slide_html, 'href="/audit-slide/"'), 'Card Slide imbriquée');
$assert(str_contains($render('slider-cards', ['items' => [$slide]], true), 'AUDIT_SLIDE'), 'Répéteur Slider Cards imbriqué');
$assert($render('slider-cards', ['items' => [['card' => ['title' => '', 'text' => '', 'image' => null]]]], true) === '', 'Slide vide ignorée');
$first = 920000000 + random_int(1, 100000);
$ids = [];
try {
    $defaults_sequence = 100;
    foreach (['news' => 'text', 'project' => 'text', 'expertise' => 'text', 'team' => 'function', 'page' => 'title', 'keynumber' => 'value', 'testimonial' => 'quote', 'slide' => 'title'] as $type => $name) {
        $defaults_id = $first + $defaults_sequence++;
        $assert(get_post($defaults_id) === null, 'Collision de fixture defaults');
        $ids[] = $defaults_id;
        wp_cache_set($defaults_id, new WP_Post((object) ['ID' => $defaults_id, 'post_type' => $type, 'post_status' => 'publish', 'post_title' => 'Audit defaults']), 'posts');
        wp_cache_set($defaults_id, [], 'post_meta');
        $values = get_field('field_card_' . $type . '_group', $defaults_id);
        $assert(is_array($values) && trim((string) ($values[$name] ?? '')) !== '', 'Défaut ACF manquant : ' . $type);
    }
    $empty_id = $first + 200;
    $assert(get_post($empty_id) === null, 'Collision de fixture vide');
    $ids[] = $empty_id;
    wp_cache_set($empty_id, new WP_Post((object) ['ID' => $empty_id, 'post_type' => 'news', 'post_status' => 'publish', 'post_title' => 'Audit vide']), 'posts');
    wp_cache_set($empty_id, ['card_text' => [''], '_card_text' => ['field_6aa511e07b5b6']], 'post_meta');
    $empty_values = get_field('field_card_news_group', $empty_id);
    $assert(($empty_values['text'] ?? null) === '', 'Une valeur volontairement vide est remplacée');
    foreach (['publish', 'private', 'draft', 'future', 'trash', 'password'] as $index => $status) {
        $id = $first + $index;
        $assert(get_post($id) === null, 'Collision de fixture');
        $ids[] = $id;
        wp_cache_set($id, new WP_Post((object) [
            'ID' => $id, 'post_type' => 'testimonial',
            'post_status' => $status === 'password' ? 'publish' : $status,
            'post_password' => $status === 'password' ? 'audit' : '',
            'post_title' => 'Audit testimonial', 'post_excerpt' => '',
            'post_date' => '2026-01-01 12:00:00', 'post_date_gmt' => '2026-01-01 12:00:00',
        ]), 'posts');
        wp_cache_set($id, [
            'card' => [''], '_card' => ['field_card_testimonial_group'],
            'card_quote' => ['<p>AUDIT_QUOTE</p>'], '_card_quote' => ['field_card_testimonial_quote'],
            'card_author' => ['AUDIT_AUTHOR'], '_card_author' => ['field_card_testimonial_author'],
            'card_source' => ['AUDIT_SOURCE'], '_card_source' => ['field_card_testimonial_source'],
        ], 'post_meta');
        $html = $render('testimonial', $id);
        if ($status === 'publish') {
            $assert(str_contains($html, 'AUDIT_QUOTE') && str_contains($html, 'AUDIT_AUTHOR') && str_contains($html, 'AUDIT_SOURCE'), 'Témoignage interne publié non rendu');
        } else {
            $assert($html === '', 'Contenu non public/protégé rendu : ' . $status);
        }
    }
} finally {
    foreach ($ids as $id) {
        wp_cache_delete($id, 'posts');
        wp_cache_delete($id, 'post_meta');
    }
}
echo "Cards ACF WordPress contract: OK (groupes, Keynumbers, témoignages internes et confidentialité)\n";
