<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_options() {
    $options = wp_load_alloptions();

    // Keys that deserve a visual warning on the frontend
    $warning_substrings = ['default_role', 'secret', 'password', 'users_can_register', 'private_key'];

    $items = [];
    foreach ($options as $key => $value) {
        $warn = false;
        foreach ($warning_substrings as $substr) {
            if (stripos($key, $substr) !== false) {
                $warn = true;
                break;
            }
        }
        $items[$key] = [
            'value'   => print_r($value, true),
            'warning' => $warn,
        ];
    }

    stealth_json([
        'count' => count($items),
        'items' => $items,
    ]);
}
