<?php
$args = function_exists('get_field') ? get_field('prefooter', 'option') : [];
$args = is_array($args) ? $args : [];

$title = theme_string($args['title'] ?? '');
$text = theme_string($args['text'] ?? '');
$socials = is_array($args['socials'] ?? null) ? $args['socials'] : [];
$newsletter_title = theme_string($args['newsletter_title'] ?? '');
$newsletter_text = theme_string($args['newsletter_text'] ?? '');
$newsletter_action = theme_http_url($args['newsletter_action'] ?? '');
$newsletter_button = theme_string($args['newsletter_button'] ?? '');

if (
    $title === ''
    && $text === ''
    && $socials === []
    && $newsletter_title === ''
    && $newsletter_text === ''
    && $newsletter_action === ''
) {
    return;
}
?>

<section class="prefooter-newsletter" aria-label="Pré-pied de page">
    <div class="prefooter-newsletter-content">
        <div class="prefooter-newsletter-intro">
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
                'classes' => 'prefooter-newsletter-socials',
            ]); ?>
        </div>

        <?php if ($newsletter_title !== '' || $newsletter_text !== '' || $newsletter_action !== '') : ?>
            <?php component('newsletter', [
                'title' => $newsletter_title,
                'text' => $newsletter_text,
                'action' => $newsletter_action,
                'email_id' => 'prefooter-newsletter-email',
                'email_label' => 'Adresse e-mail',
                'button_label' => $newsletter_button,
                'mandatory_msg' => 'Saisissez votre adresse e-mail.',
                'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
                'classes' => 'prefooter-newsletter-form',
            ]); ?>
        <?php endif; ?>
    </div>
</section>
