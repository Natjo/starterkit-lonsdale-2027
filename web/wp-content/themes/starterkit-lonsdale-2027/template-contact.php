<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 */

$contact_values = [
    'name' => '',
    'email' => '',
    'phone' => '',,
    'subject' => '',
    'message' => '',
];
$contact_error = '';
$contact_sent = isset($_GET['contact']) && sanitize_key(wp_unslash($_GET['contact'])) === 'sent';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['contact_action'])) {
    $nonce = isset($_POST['contact_nonce']) ? sanitize_text_field(wp_unslash($_POST['contact_nonce'])) : '';
    foreach (array_keys($contact_values) as $field_name) {
        $contact_values[$field_name] = isset($_POST[$field_name])
            ? sanitize_text_field(wp_unslash($_POST[$field_name]))
            : '';
    }
    $contact_values['message'] = isset($_POST['message'])
        ? sanitize_textarea_field(wp_unslash($_POST['message']))
        : '';
    $consent = isset($_POST['consent']) && wp_unslash($_POST['consent']) === '1';

    if (!wp_verify_nonce($nonce, 'theme_contact')) {
        $contact_error = 'La session a expiré. Rechargez la page puis réessayez.';
    } elseif ($contact_values['name'] === '' || !is_email($contact_values['email']) || $contact_values['message'] === '' || !$consent) {
        $contact_error = 'Complétez tous les champs obligatoires avec des informations valides.';
    } else {
        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $mail_subject = $contact_values['subject'] !== ''
            ? $contact_values['subject']
            : sprintf('Nouveau message depuis %s', $site_name);
        $mail_body = implode("\n\n", array_filter([
            'Nom : ' . $contact_values['name'],
            'E-mail : ' . $contact_values['email'],
            $contact_values['phone'] !== '' ? 'Téléphone : ' . $contact_values['phone'] : '',
            'Message :' . "\n" . $contact_values['message'],
        ]));
        $sent = wp_mail(
            sanitize_email((string) get_option('admin_email')),
            $mail_subject,
            $mail_body,
            ['Reply-To: ' . $contact_values['name'] . ' <' . $contact_values['email'] . '>']
        );

        if ($sent) {
            wp_safe_redirect(add_query_arg('contact', 'sent', get_permalink()));
            exit;
        }

        $contact_error = 'Le message n’a pas pu être envoyé. Réessayez dans quelques instants.';
    }
}

get_header();
common('header-nav');
?>

<main class="contact-page">
    <div class="contact-content">
        <?php component('title', [
            'title' => get_the_title(),
            'hx' => 1,
            'classes' => 'title-1',
        ]); ?>

        <?php component('text', [
            'text' => '<p>Utilisez ce formulaire pour nous envoyer votre demande.</p>',
            'classes' => 'contact-intro',
        ]); ?>

        <?php if ($contact_sent) : ?>
            <?php component('text', [
                'text' => '<p>Votre message a bien été envoyé.</p>',
                'classes' => 'contact-notice contact-success',
                'attributes' => ['role' => 'status'],
            ]); ?>
        <?php endif; ?>

        <?php if ($contact_error !== '') : ?>
            <?php component('text', [
                'text' => '<p>' . esc_html($contact_error) . '</p>',
                'classes' => 'contact-notice contact-error',
                'attributes' => ['role' => 'alert'],
            ]); ?>
        <?php endif; ?>

        <form class="contact-form" method="post" action="<?= esc_url(get_permalink()) ?>" data-module="components/field">
            <?php wp_nonce_field('theme_contact', 'contact_nonce'); ?>
            <input type="hidden" name="contact_action" value="send">

            <?php component('field', [
                'type' => 'text',
                'name' => 'name',
                'label' => 'Nom',
                'value' => $contact_values['name'],
                'required' => true,
                'autocomplete' => 'name',
                'mandatory_msg' => 'Saisissez votre nom.',
            ]); ?>

            <?php component('field', [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Adresse e-mail',
                'value' => $contact_values['email'],
                'required' => true,
                'autocomplete' => 'email',
                'mandatory_msg' => 'Saisissez votre adresse e-mail.',
                'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
            ]); ?>

            <?php component('field', [
                'type' => 'tel',
                'name' => 'phone',
                'label' => 'Téléphone',
                'value' => $contact_values['phone'],
                'autocomplete' => 'tel',
            ]); ?>

            <?php component('field', [
                'type' => 'text',
                'name' => 'subject',
                'label' => 'Objet',
                'value' => $contact_values['subject'],
            ]); ?>

            <?php component('field', [
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Message',
                'value' => $contact_values['message'],
                'required' => true,
                'rows' => 8,
                'classes' => 'contact-message',
                'mandatory_msg' => 'Saisissez votre message.',
            ]); ?>

            <?php component('field', [
                'type' => 'checkbox',
                'name' => 'consent',
                'label' => 'J’accepte que mes informations soient utilisées pour répondre à ma demande.',
                'value' => '1',
                'required' => true,
                'mandatory_msg' => 'Votre accord est nécessaire pour envoyer le formulaire.',
                'classes' => 'contact-consent',
            ]); ?>

            <?php component('btn', [
                'name' => 'Envoyer',
                'classes' => 'btn-1 contact-submit',
                'attributes' => ['type' => 'submit'],
            ]); ?>
        </form>
    </div>
</main>

<?php get_footer(); ?>
