<?php
require_once(ABSPATH . 'wp-admin/includes/plugin-install.php'); // for plugins_api()
require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'); // for Plugin_Upgrader
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php'); // for get_plugin_data()

// Define a custom skin class to suppress output issues
class Silent_Upgrader_Skin extends WP_Upgrader_Skin {
    public function feedback($string, ...$args) {
        // Override feedback to prevent any output or messages
        // This avoids calling the undefined show_message() function
    }
}

function install_plugin_by_slug($slug) {
    // Initialize the WordPress filesystem
    if (false === function_exists('WP_Filesystem')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $creds = request_filesystem_credentials('', '', false, false, array());

    if (!WP_Filesystem($creds)) {
        echo 'Could not access filesystem.<br>';
        return false;
    }

    // Get plugin info from WordPress API
    $api = plugins_api('plugin_information', array(
        'slug'   => $slug,
        'fields' => array(
            'sections' => false,
        ),
    ));

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


?>
