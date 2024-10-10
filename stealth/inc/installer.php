<?php
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
        'slug'   => $slug,
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
        'slug'   => $slug,
        'fields' => array(
            'sections'        => false,
            'downloaded'      => true,
            'last_updated'    => true,
            'versions'         => true
        ),
    ));
    return $api;
}

function install_plugin_by_slug($slug) {
    // Get plugin info from WordPress API
    $api = get_plugin_info($slug);

    if (is_wp_error($api)) {
        echo 'Failed to retrieve plugin information';
        return false;
    }

    // Set up the plugin upgrader and install the plugin
    $upgrader = new Plugin_Upgrader(new Silent_Upgrader_Skin());
    $result = $upgrader->install($api->download_link);

    if ($result) {
        echo "Plugin {$slug} installed successfully!<br>";
        return true;
    } else {
        echo "Plugin installation failed!<br>";
        return false;
    }
}

function activate_plugin_by_slug($slug) {
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
            echo "Plugin activation failed: " . $activate_result->get_error_message() . "<br>";
        } else {
            echo "Plugin {$slug} activated successfully!<br>";
        }
    } else {
        echo "Plugin {$slug} not found!<br>";
    }
}


function install_theme_by_slug($slug) {
    // Get theme info from WordPress API
    $api = get_theme_info($slug);

    if (is_wp_error($api)) {
        echo 'Failed to retrieve theme information';
        return false;
    }

    // Set up the theme upgrader and install the theme
    $upgrader = new Theme_Upgrader(new Silent_Upgrader_Skin());
    $result = $upgrader->install($api->download_link);

    if ($result) {
        echo "Theme {$slug} installed successfully!<br>";
        return true;
    } else {
        echo "Theme installation failed!<br>";
        return false;
    }
}

function activate_theme_by_slug($slug) {
    // Check if the theme is installed
    if (wp_get_theme($slug)->exists()) {
        // Activate the theme
        switch_theme($slug);
        echo "Theme {$slug} activated successfully!<br>";
    } else {
        echo "Theme {$slug} not found!<br>";
    }
}

?>
