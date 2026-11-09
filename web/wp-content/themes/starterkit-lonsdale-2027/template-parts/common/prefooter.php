<?php
$args = function_exists('get_field') ? get_field('prefooter', 'option') : [];
$args = is_array($args) ? $args : [];

$title = theme_string($args['title'] ?? '');
$text = theme_string($args['text'] ?? '');
$socials = is_array($args['socials'] ?? null) ? $args['socials'] : [];

if (
    $title === ''
    && $text === ''
    && $socials === []
) {
    return;
}
?>

<section class="prefooter" aria-label="Pré-pied de page">
    <div class="prefooter-content">
        <div class="prefooter-intro">
            <?php component('title', [
                'title' => $title,
                'hx' => 2,
                'classes' => 'title-2-bis',
            ]); ?>

            <?php component('text', [
                'text' => $text,
                'classes' => 'rte',
            ]); ?>

            <?php component('socials', [
                'items' => $socials,
                'aria_label' => 'Réseaux sociaux',
                'classes' => 'prefooter-socials',
            ]); ?>
        </div>
    </div>
</section>
