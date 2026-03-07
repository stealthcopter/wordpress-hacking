<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

/**
 * GET ?stealth_api=filters
 * Returns available filter slugs and their current enabled state.
 */
function stealth_api_filters_get() {
    $available = get_filters();
    $current   = get_user_filters();

    stealth_json([
        'available' => $available,
        'state'     => $current,
    ]);
}

/**
 * POST ?stealth_api=filters
 * Body: JSON object of { slug: bool, ... }
 *
 * Accepts filter state in the request body and updates the cookie so that
 * subsequent API calls (which read the cookie internally) honour the new state.
 */
function stealth_api_filters_save() {
    $body = file_get_contents('php://input');
    $new_state = json_decode($body, true);

    if (!is_array($new_state)) {
        stealth_json(['error' => 'Request body must be a JSON object'], 400);
    }

    $available = get_filters();

    // Sanitise: only allow known slugs, coerce values to bool
    $sanitised = [];
    foreach ($available as $slug) {
        $sanitised[$slug] = isset($new_state[$slug]) ? (bool) $new_state[$slug] : true;
    }

    // Write the cookie (mirrors what get_user_filters() does internally)
    setcookie('stealth_filters', json_encode($sanitised), [
        'path'     => '/',
        'httponly' => false,
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax',
    ]);

    stealth_json([
        'available' => $available,
        'state'     => $sanitised,
    ]);
}
