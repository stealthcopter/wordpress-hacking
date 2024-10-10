<?php

if (defined( 'ABSPATH' ) ) {
    // We are probably loaded from WordPress
} else {
    // Array of directories to search for wp-load.php
    $paths = [
        __DIR__ . '/wp-load.php',        // Current directory
        __DIR__ . '/../wp-load.php',     // One level up
        __DIR__ . '/../../wp-load.php',  // Two levels up
        '/var/www/html/wp-load.php',     // Common web root
        '/srv/www/wp-load.php',          // Another common web root
        '/usr/share/nginx/html/wp-load.php', // Common Nginx path
        '/var/www/wp-load.php'           // Another common web root
    ];

    // Loop through each path and check if wp-load.php exists
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            define('WP_LOADED_FROM_PATH', $path);
            break;
        }
    }

    // If none of the paths matched, you could handle the error:
    if (!defined('ABSPATH')) {
        die('Error: Could not locate WordPress wp-load.php file.');
    }
}

function is_installed_to_wordpress(){
    return !defined('WP_LOADED_FROM_PATH');
}