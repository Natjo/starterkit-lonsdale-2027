<?php
$footer_newsletter = get_option('theme_footer_newsletter', []);
$footer_newsletter = apply_filters('theme_footer_newsletter', is_array($footer_newsletter) ? $footer_newsletter : []);
$footer_newsletter = is_array($footer_newsletter) ? $footer_newsletter : [];
component('newsletter', [
    'title' => theme_string($footer_newsletter['title'] ?? 'Newsletter'),
    'text' => theme_string($footer_newsletter['text'] ?? '<p>Recevez nos dernières actualités et nos nouveaux projets.</p>'),
    'action' => theme_http_url($footer_newsletter['action'] ?? ''),
    'email_id' => 'footer-newsletter-email',
    'email_label' => 'Adresse e-mail',
    'email_placeholder' => 'Votre adresse e-mail',
    'button_label' => theme_string($footer_newsletter['button_label'] ?? 'S’inscrire'),
    'mandatory_msg' => 'Saisissez votre adresse e-mail.',
    'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
    'title_classes' => 'footer-widget-title',
    'classes' => 'footer-newsletter',
]);
?>
