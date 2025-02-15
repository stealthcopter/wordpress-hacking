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
opcache_reset();

require_once 'inc/loader.php';

if (!defined('STEALTH_PLUGIN_FILE')) {
    // We do this nasty shit so we can support loading via symlinked directories without explosions.
    define('STEALTH_PLUGIN_FILE', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/' . basename(__FILE__));
}

if (!defined('STEALTH_PLUGIN_PATH')) {
    define('STEALTH_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
}

// Load the PHP object gadget
require_once STEALTH_PLUGIN_PATH . '/payloads/php_obj.php';

if (!function_exists('stealth_render_page')) {
    function stealth_render_page()
    {

        require_once STEALTH_PLUGIN_PATH . '/inc/defaults.php';
        require_once STEALTH_PLUGIN_PATH . '/inc/init.php';

        $page = 'index';
        $margin = '';
        if (isset($_REQUEST['stealth_page'])) {
            $page = basename($_REQUEST['stealth_page']);
            $margin = 'mt-4';
        }

        include STEALTH_PLUGIN_PATH . '/inc/templates/header.php';

        echo "<div class='content $margin'>";
        // You can path traverse here if you like, no stress
        include STEALTH_PLUGIN_PATH . "/inc/pages/$page.php";
        echo '</div>';

        include STEALTH_PLUGIN_PATH . '/inc/templates/footer.php';
    }
}

function init_stealth()
{
    require_once STEALTH_PLUGIN_PATH . '/config.php';
    require_once STEALTH_PLUGIN_PATH . '/api.php';

    require_once STEALTH_PLUGIN_PATH . '/inc/code.php';
    require_once STEALTH_PLUGIN_PATH . '/inc/shortcodes.php';
    require_once STEALTH_PLUGIN_PATH . '/inc/rest.php';

    require_once STEALTH_PLUGIN_PATH . '/inc/views.php';
}

if (is_installed_to_wordpress()) {
    add_action('init', 'init_stealth');

    add_action('parse_request', 'stealth_url_handler');
    function stealth_url_handler($query_args)
    {
        $requestPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $url = trailingslashit($requestPath);
        if (strstr($url, STEALTH_PERMALINK_PATH)) {
            // Intercept
            stealth_render_page();
            die();
        }
    }

} else {
    init_stealth();
    stealth_render_page();
}
