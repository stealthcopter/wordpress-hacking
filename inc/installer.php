<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
require_once(ABSPATH . 'wp-admin/includes/theme-install.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/theme.php');

// Define a custom skin class to suppress output issues
class Silent_Upgrader_Skin extends WP_Upgrader_Skin {
    public function feedback($string, ...$args) {
        // Override feedback to prevent any output or messages
        // This avoids calling the undefined show_message() function
    }
}

function get_plugin_info($slug){
    $api = plugins_api('plugin_information', array(
        'slug'   => strtolower($slug),
        'fields' => array(
            'sections'        => false,
            'active_installs' => true,
            'downloaded'      => true,
            'last_updated'    => true,
            'versions'         => true,
            'icons'           => true,
        ),
    ));
    return $api;
}
function get_theme_info($slug){
    $api = themes_api('theme_information', array(
        'slug'   => strtolower($slug),
        'fields' => array(
            'sections'        => false,
            'downloaded'      => true,
            'last_updated'    => true,
            'versions'         => true
        ),
    ));
    return $api;
}

function slug_from_url($url){
    if (preg_match('/\/([^\/]+?)(-\d+(\.\d+)*|)\.zip$/', $url, $matches)) {
        $name = $matches[1];
        return $name; // Outputs: stealth
    }
    return null;
}

function is_remote_file($slug){
    return (strpos($slug, 'http://') === 0 || strpos($slug, 'https://') === 0);
}

function install_plugin_by_slug($slug) {
    $output = '';

    if (is_remote_file($slug)) {
        // Installing from URL
        $download_link = $slug;
        $slug = slug_from_url($download_link);
        $api = null;
    }
    else{
        // Get plugin info from WordPress API
        $api = get_plugin_info($slug);

        if (is_wp_error($api)) {
            return ["success" => false, "output" => 'Failed to retrieve plugin information'];
        }

        $download_link = $api->download_link;
    }

    $args = array(
        'clear_destination' => true, // This will delete the existing plugin folder
        'overwrite_package' => true, // Optional: this flag ensures overwriting if necessary
    );

    // Set up the plugin upgrader and install the plugin
    $upgrader = new Plugin_Upgrader(new Silent_Upgrader_Skin());
    $result = $upgrader->install($download_link, $args);

    if ($result) {
        return ["success" => true, "output" => "Plugin {$slug} installed successfully!", "plugin" => $api];
    } else {
        return ["success" => false, "output" => "Plugin installation failed!"];
    }
}

function activate_plugin_by_slug($slug) {
    if (is_remote_file($slug)) {
        // Installing from URL
        $download_link = $slug;
        $slug = slug_from_url($download_link);
    }

    $results = '';
    // Get all plugins
    $all_plugins = get_plugins();

    // Look for the plugin with the given slug
    $plugin_file = '';
    foreach ($all_plugins as $file => $plugin_data) {
        if (strpos($file, $slug . '/') === 0) {
            // This plugin belongs to the given slug
            $plugin_file = $file;
            break;
        }
    }

    if ($plugin_file) {
        // Try to activate the plugin
        $activate_result = activate_plugin($plugin_file);
        if (is_wp_error($activate_result)) {
            return ["success" => false, "output" => "Plugin activation failed: " . $activate_result->get_error_message()];
        } else {
            return ["success" => true, "output" => "Plugin {$slug} activated successfully!"];
        }
    } else {
        return ["success" => false, "output" => "Plugin {$slug} not found!"];
    }
}


function install_theme_by_slug($slug) {

    if (is_remote_file($slug)) {
        // Installing from URL
        $download_link = $slug;
        $slug = slug_from_url($download_link);
        $api = null;
    }
    else{
        // Get theme info from WordPress API
        $api = get_theme_info($slug);

        if (is_wp_error($api)) {
            return ["success" => false, "output" => 'Failed to retrieve theme information'];
        }

        $download_link = $api->download_link;
    }

    // Set up the theme upgrader and install the theme
    $upgrader = new Theme_Upgrader(new Silent_Upgrader_Skin());
    $result = $upgrader->install($download_link);

    if ($result) {
        return ["success" => true, "output" => "Theme {$slug} installed successfully!", "theme" => $api];
    } else {
        return ["success" => false, "output" => "Theme installation failed!"];
    }
}

function activate_theme_by_slug($slug) {
    if (is_remote_file($slug)) {
        // Installing from URL
        $download_link = $slug;
        $slug = slug_from_url($download_link);
    }

    // Check if the theme is installed
    if (wp_get_theme($slug)->exists()) {
        // Activate the theme
        switch_theme($slug);
        return ["success" => true, "output" => "Theme {$slug} activated successfully!<br>"];
    } else {
        return ["success" => false, "output" => "Theme {$slug} not found!"];
    }
}

?>
