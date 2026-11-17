<?php

/**
 * Template Name: Contact
 * Template Post Type: page
 */

$contact_form = new Theme_Contact_Form([
    'action_url' => get_permalink(),
    'ajax' => true,
]);
$contact_form->handle();

get_header();
common('header-nav');
?>

<main class="template-contact">
    <?php hero('page'); ?>

    <div class="main-content">
        <?php if ($contact_form->is_sent()) : ?>
            <?php component('text', [
                'text' => '<p>' . esc_html($contact_form->success_message()) . '</p>',
                'classes' => 'contact-notice contact-success',
                'attributes' => ['role' => 'status'],
            ]); ?>
        <?php endif; ?>

        <?php if ($contact_form->error() !== '') : ?>
            <?php component('text', [
                'text' => '<p>' . esc_html($contact_form->error()) . '</p>',
                'classes' => 'contact-notice contact-error',
                'attributes' => ['role' => 'alert'],
            ]); ?>
        <?php endif; ?>

        <form
            class="contact-form"
            method="post"
            action="<?= esc_url($contact_form->action_url()) ?>"
            data-module="<?= esc_attr($contact_form->is_ajax() ? 'pages/template-contact' : 'components/field') ?>"
            <?php if ($contact_form->is_ajax()) : ?>data-contact-endpoint="<?= esc_url($contact_form->endpoint_url()) ?>"<?php endif; ?>
        >
            <?php $contact_form->nonce_field(); ?>
            <input type="hidden" name="contact_action" value="send">

            <?php component('field', [
                'type' => 'text',
                'name' => 'name',
                'label' => 'Nom',
                'value' => $contact_form->value('name'),
                'required' => true,
                'autocomplete' => 'name',
                'mandatory_msg' => 'Saisissez votre nom.',
            ]); ?>

            <?php component('field', [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Adresse e-mail',
                'value' => $contact_form->value('email'),
                'required' => true,
                'autocomplete' => 'email',
                'mandatory_msg' => 'Saisissez votre adresse e-mail.',
                'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
            ]); ?>

            <?php component('field', [
                'type' => 'tel',
                'name' => 'phone',
                'label' => 'Téléphone',
                'value' => $contact_form->value('phone'),
                'autocomplete' => 'tel',
            ]); ?>

            <?php component('field', [
                'type' => 'text',
                'name' => 'subject',
                'label' => 'Objet',
                'value' => $contact_form->value('subject'),
            ]); ?>

            <?php component('field', [
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Message',
                'value' => $contact_form->value('message'),
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
                'classes' => 'btn-solid contact-submit',
                'attributes' => ['type' => 'submit'],
            ]); ?>
        </form>
    </div>
</main>

<?php get_footer(); ?>
