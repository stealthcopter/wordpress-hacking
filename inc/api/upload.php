<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

/**
 * POST ?stealth_api=upload_echo
 * Echoes back $_FILES so the client can inspect what the server received.
 * The uploaded files are NOT saved anywhere – this is purely for inspection.
 */
function stealth_api_upload_echo() {
    if (empty($_FILES)) {
        stealth_json(['error' => 'No files uploaded'], 400);
    }

    // Normalise the $_FILES superglobal into a more readable structure
    $files = [];
    foreach ($_FILES as $field => $file_data) {
        // Handle single and multiple file inputs
        if (is_array($file_data['name'])) {
            for ($i = 0; $i < count($file_data['name']); $i++) {
                $files[] = [
                    'field'    => $field,
                    'name'     => $file_data['name'][$i],
                    'type'     => $file_data['type'][$i],
                    'tmp_name' => $file_data['tmp_name'][$i],
                    'error'    => $file_data['error'][$i],
                    'size'     => $file_data['size'][$i],
                ];
            }
        } else {
            $files[] = [
                'field'    => $field,
                'name'     => $file_data['name'],
                'type'     => $file_data['type'],
                'tmp_name' => $file_data['tmp_name'],
                'error'    => $file_data['error'],
                'size'     => $file_data['size'],
            ];
        }
    }

    stealth_json([
        'count'   => count($files),
        'files'   => $files,
        'post'    => $_POST,
    ]);
}
