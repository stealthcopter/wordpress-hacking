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

require_once 'config.php';
require_once 'inc/loader.php';
require_once 'api.php';

require_once 'inc/views.php';

// Load the PHP object gadget
require_once 'payloads/php_obj.php';

function render_page(){
    $page = 'templates/index';
    if (isset($_REQUEST['stealth_page'])) {
        $page = $_REQUEST['stealth_page'];
    }

    include 'templates/header.php';

    echo '<div class="content mt-4">';
    // You can path traverse here if you like, no stress
    include "$page.php";
    echo '</div>';

    include 'templates/footer.php';
}

if (is_installed_to_wordpress()){
    // Intercept urls
    if ( function_exists( 'add_action' ) ) {
        add_action('parse_request', 'stealth_url_handler');
        function stealth_url_handler( $query_args ) {
            if( strstr( $_SERVER["REQUEST_URI"], "/wp-content/plugins/stealth/" )){
                // Don't intercept
            }
            else if( strstr( $_SERVER["REQUEST_URI"], STEALTH_PERMALINK_PATH )){
                // Intercept
                render_page();
                die();
            }
        }
    }
}
else{
    render_page();
}
