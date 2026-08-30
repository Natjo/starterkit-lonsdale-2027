<?php

function verifyAjaxRequest(string $action, int $maxPerWindow = 5, int $windowSeconds = 300): void
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ajax')) {
        wp_send_json(['success' => false, 'message' => 'Invalid nonce']);
    }

    if (!empty($_POST['website'])) {
        wp_send_json(['success' => true]);
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'rl_' . $action . '_' . md5($ip);
    $count = (int) get_transient($key);
    if ($count >= $maxPerWindow) {
        wp_send_json(['success' => false, 'message' => __('Trop de soumissions, veuillez réessayer plus tard.')]);
    }
    set_transient($key, $count + 1, $windowSeconds);
}

add_action('wp_ajax_ajaxExample', 'ajaxExample');
add_action('wp_ajax_nopriv_ajaxExample', 'ajaxExample');

function ajaxExample()
{
    verifyAjaxRequest('ajaxExample', 3, 300);
}
