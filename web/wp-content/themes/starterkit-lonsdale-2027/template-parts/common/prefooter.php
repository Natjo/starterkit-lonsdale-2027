<?php
$args = function_exists('get_field') ? get_field('prefooter', 'option') : [];
$args = is_array($args) ? $args : [];

$to_string = static fn (mixed $value): string =>
    is_scalar($value) || $value instanceof Stringable ? trim((string) $value) : '';
$http_url = static function (mixed $value) use ($to_string): string {
    $url = $to_string($value);
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) return '';

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : '';
};

$title = $to_string($args['title'] ?? '');
$text = $to_string($args['text'] ?? '');
$socials = is_array($args['socials'] ?? null) ? $args['socials'] : [];
$newsletter_title = $to_string($args['newsletter_title'] ?? '');
$newsletter_text = $to_string($args['newsletter_text'] ?? '');
$newsletter_action = $http_url($args['newsletter_action'] ?? '');
$newsletter_button = $to_string($args['newsletter_button'] ?? '');

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
                'classes' => 'prefooter-newsletter',
            ]); ?>
        <?php endif; ?>
    </div>
</section>
