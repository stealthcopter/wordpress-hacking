<?php

// Where are you WordPress??? imma find ya!

if (defined( 'ABSPATH' ) ) {
    // We are probably loaded from WordPress
} else {
    // Array of directories to search for wp-load.php
    $paths = [
        __DIR__ . '/wp-load.php',
        __DIR__ . '/../wp-load.php',
        __DIR__ . '/../../wp-load.php',
        __DIR__ . '/../../../wp-load.php',
        __DIR__ . '/../../../../wp-load.php',
        '/var/www/html/wp-load.php',
        '/var/www/html/wordpress/wp-load.php',
        '/srv/www/wp-load.php',
        '/usr/share/nginx/html/wp-load.php',
        '/usr/share/nginx/html/wordpress/wp-load.php',
        '/var/www/wp-load.php'
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

function is_installed_to_wordpress(): bool
{
    return !defined('WP_LOADED_FROM_PATH');
}