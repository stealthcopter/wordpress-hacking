<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_nonce() {
    if (empty($_REQUEST['action'])) {
        stealth_json(['error' => 'action parameter required'], 400);
    }

    $action = $_REQUEST['action'];

    if (is_user_logged_in()) {
        $user       = wp_get_current_user();
        $user_login = $user->user_login;
        $user_roles = $user->roles;
        $user_id    = $user->ID;
    } else {
        $user_login = 'Not Logged In';
        $user_roles = [];
        $user_id    = 0;
    }

    stealth_json([
        'action' => $action,
        'nonce'  => wp_create_nonce($action),
        'user'   => [
            'id'    => $user_id,
            'login' => $user_login,
            'roles' => $user_roles,
        ],
    ]);
}
