<?php
/** Run with wp eval-file; synthetic posts live only in the request's memory cache. */
if (!function_exists('theme_post_card_item')) {
    throw new RuntimeException('Charger WordPress et le thème avant ce contrat.');
}
if (wp_using_ext_object_cache()) {
    throw new RuntimeException('Ce contrat nécessite un cache objet non persistant pour isoler ses fixtures.');
}
$failures = [];
$check = static function (bool $condition, string $label) use (&$failures): void {
    if (!$condition) $failures[] = $label;
};
$render = static function (string $name, array $args, bool $as_card = false): string {
    ob_start();
    try { $as_card ? card($name, $args) : strate($name, $args); }
    finally { $html = (string) ob_get_clean(); }
    return $html;
};
$previous_user = wp_get_current_user();
$fixtures = [];
$first_id = 910_100_000 + random_int(1, 100_000);
try {
    foreach (['publish', 'private', 'draft', 'future', 'trash', 'password'] as $index => $status) {
        $id = $first_id + $index;
        if (get_post($id)) throw new RuntimeException('Un ID de fixture existe déjà.');
        $post = new WP_Post((object) [
            'ID' => $id,
            'post_type' => 'page',
            'post_status' => $status === 'password' ? 'publish' : $status,
            'post_password' => $status === 'password' ? 'audit-secret' : '',
            'post_title' => 'AUDIT_CARD_TITLE',
            'post_excerpt' => 'AUDIT_CARD_EXCERPT',
            'post_name' => 'audit-card',
            'post_author' => 1,
            'post_date' => '2026-01-01 12:00:00',
            'post_date_gmt' => '2026-01-01 12:00:00',
        ]);
        wp_cache_set($id, $post, 'posts');
        $fixtures[$status] = $id;
    }
    foreach (['anonymous', 'administrator'] as $role) {
        wp_set_current_user(0);
        if ($role === 'administrator') {
            $admin = new WP_User();
            $admin->ID = $first_id + 99;
            $admin->allcaps = ['read_private_posts' => true, 'read_private_pages' => true];
            $GLOBALS['current_user'] = $admin;
            $check(current_user_can('read_private_pages'), 'La fixture admin possède ses privilèges');
        }
        foreach ($fixtures as $status => $id) {
            $public = $status === 'publish';
            $check((theme_post_card_item($id) !== null) === $public, "$role/$status : visibilité du payload");
            foreach (['pushes' => ['items' => [$id]], 'marquee' => ['content_type' => 'cards', 'post_type' => 'page', 'items' => [$id]]] as $name => $args) {
                $html = $render($name, $args);
                $check(str_contains($html, 'AUDIT_CARD_TITLE') === $public, "$role/$status/$name : titre");
                $check(str_contains($html, 'AUDIT_CARD_EXCERPT') === $public, "$role/$status/$name : extrait");
            }
            $html = $render('page', ['post_id' => $id], true);
            $check(str_contains($html, 'AUDIT_CARD_TITLE') === $public, "$role/$status/card : rendu direct par ID");
            if (!$public) {
                $check($render('page', ['post_id' => $id, 'title' => 'OVERRIDE'], true) === '', "$role/$status : une surcharge ne contourne pas la confidentialité");
            }
        }
    }
    $check(str_contains($render('page', ['post_id' => $fixtures['publish'], 'title' => 'OVERRIDE'], true), 'OVERRIDE'), 'Les surcharges publiques restent disponibles');
    $check($render('page', ['post_id' => $fixtures['publish'], 'title' => ''], true) === '', 'Un titre volontairement vidé reste vide');
    $check(str_contains($render('page', ['title' => 'MANUAL'], true), 'MANUAL'), 'Le payload éditorial sans post reste utilisable');
    $check(theme_post_card_item($first_id + 100) === null, 'Un ID inexistant est refusé');
} finally {
    $GLOBALS['current_user'] = $previous_user;
    foreach ($fixtures as $id) {
        wp_cache_delete($id, 'posts');
        wp_cache_delete($id, 'post_meta');
    }
}
if ($failures) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}
echo "Strates WordPress contract: OK (confidentialité, normalisation et surcharges)\n";
