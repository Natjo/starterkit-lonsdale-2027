<?php
$footer_text = trim((string) get_bloginfo('description'));
if ($footer_text !== '') component('text', ['text' => '<p>' . esc_html($footer_text) . '</p>', 'classes' => 'footer-text']);
?>
