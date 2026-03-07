<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_actions() {
    $all_actions = get_all_actions();

    // Detail request: ?stealth_api=actions&id={hash}
    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        if (!array_key_exists($id, $all_actions)) {
            stealth_json(['error' => 'Action not found'], 404);
        }

        $entry  = $all_actions[$id];
        $action = $entry['action'];
        $hook   = $entry['hook'];
        $code   = $entry['code'];

        $result = [
            'id'        => $id,
            'hook'      => $hook,
            'action'    => stealth_printable($action),
            'slug'      => $entry['slug'],
            'item_type' => $entry['item_type'],
        ];

        $result = array_merge($result, stealth_serialize_code($code));

        // Build the callable URL for wp_ajax / admin_post hooks
        if (strpos($hook, 'wp_ajax') === 0) {
            $action_part = str_replace(['wp_ajax_nopriv_', 'wp_ajax_'], '', $hook);
            $result['link'] = '/wp-admin/admin-ajax.php?action=' . $action_part;
        } elseif (strpos($hook, 'admin_post') === 0) {
            $action_part = str_replace(['admin_post_nopriv_', 'admin_post_'], '', $hook);
            $result['link'] = '/wp-admin/admin-post.php?action=' . $action_part;
        }

        // Indicate if this hook is accessible without authentication
        $result['unauthenticated'] = (
            strpos($hook, 'wp_ajax_nopriv_') === 0 ||
            strpos($hook, 'admin_post_nopriv_') === 0 ||
            $hook === 'init'
        );

        stealth_json($result);
    }

    // List request
    $items = [];
    foreach ($all_actions as $hash => $entry) {
        $items[$hash] = [
            'hook'      => $entry['hook'],
            'action'    => stealth_printable($entry['action']),
            'slug'      => $entry['slug'],
            'item_type' => $entry['item_type'],
        ];
    }

    stealth_json([
        'count' => count($items),
        'items' => $items,
    ]);
}
