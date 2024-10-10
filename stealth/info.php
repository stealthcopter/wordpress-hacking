<?php

$path = 'info.php';

if (function_exists('is_installed_to_wordpress')){
    $path = '/wp-content/plugins/stealth/info.php';
}

if (basename($_SERVER['REQUEST_URI']) == 'info.php') {
    // The current request URI is info.php
    ob_start();
    phpinfo();
    $phpinfo_output = ob_get_clean();
    $phpinfo_output = str_replace('background-color: #fff', 'background-color: #212529', $phpinfo_output);
    echo $phpinfo_output;
} else {
    // The current request URI is not info.php
    echo "
    <style>
        iframe {
            width: 100%;
            height: 100vh; /* Full viewport height */
            border: none; /* Optional: Remove iframe border */
        }
        /* If the iframe is inside a parent container, make sure the parent container also takes the full height */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
    </style>
    <iframe src='$path'></iframe>";
}
?>


