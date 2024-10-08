<?php
/**
 * Plugin Name: Stealth
 * Plugin URI: https://github.com/stealthcopter/wordpress-hacking/stealth
 * Description: Some hacking tools for stuff and things.
 * Version: 0.0.1
 * Author: stealthcopter
 * Author URI: https://sec.stealthcopter.com/
 */

require_once 'inc/loader.php';

// Load the PHP object gadget
require_once 'payloads/php_obj.php';

function render_page(){
    include 'templates/header.php';

    if (isset($_REQUEST['stealth_page'])) {
        echo '<div class="content mt-4">';
        // You can path traverse here if you like, no stress
        include $_REQUEST['stealth_page'];
        echo '</div>';
    }

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
            else if( strstr( $_SERVER["REQUEST_URI"], "/stealth/" )){
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
