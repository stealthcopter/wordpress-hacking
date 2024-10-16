<?php
/**
 * Plugin Name: Stealth
 * Plugin URI: https://github.com/stealthcopter/wordpress-hacking/tree/main/stealth
 * Description: Some hacking tools for stuff and things.
 * Version: 0.0.1
 * Author: stealthcopter
 * Author URI: https://sec.stealthcopter.com/
 */

if (!defined('STEALTH_PLUGIN_FILE')) {
    define('STEALTH_PLUGIN_FILE', __FILE__);
}

if (!defined('STEALTH_PLUGIN_PATH')) {
    define('STEALTH_PLUGIN_PATH', __DIR__);
}

require_once 'inc/loader.php';

// Load the PHP object gadget
require_once 'payloads/php_obj.php';

if (!function_exists('stealth_render_page')) {
    function stealth_render_page()
    {
        $page = 'index';
        if (isset($_REQUEST['stealth_page'])) {
            $page = basename($_REQUEST['stealth_page']);
        }

        include 'inc/templates/header.php';

        echo '<div class="content mt-4">';
        // You can path traverse here if you like, no stress
        include "inc/pages/$page.php";
        echo '</div>';

        include 'inc/templates/footer.php';
    }
}

function init_stealth()
{
    require_once 'config.php';
    require_once 'api.php';

    require_once 'inc/code.php';
    require_once 'inc/views.php';
}

if (is_installed_to_wordpress()) {
    add_action('init', 'init_stealth');

    add_action('parse_request', 'stealth_url_handler');
    function stealth_url_handler($query_args)
    {
        if (strstr($_SERVER["REQUEST_URI"], STEALTH_PERMALINK_PATH)) {
            // Intercept
            stealth_render_page();
            die();
        }
    }

} else {
    init_stealth();
    stealth_render_page();
}
