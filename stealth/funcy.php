<?php

function get_all_ajax_actions($prefix) {
    global $wp_filter;

    $ajax_actions = [];

    $default_funcs = [
        'wp_ajax_save', 'wp_ajax_widgets-order', 'wp_ajax_add-category', 'wp_ajax_add-post_tag',
        'wp_ajax_add-nav_menu', 'wp_ajax_add-link_category', 'wp_ajax_add-post_format',
        'wp_ajax_add-wp_theme', 'wp_ajax_add-wp_template_part_area', 'wp_ajax_add-wp_pattern_category', 'wp_ajax_save-widget'
    ];

    $hide_noprivs = true;
    if (strpos($prefix, '_nopriv') === 0) {
        $hide_noprivs=false;
    }

    // Loop through the $wp_filter to find AJAX actions
    foreach ($wp_filter as $key => $value) {
        if (strpos($key, $prefix) === 0) {

            if ($hide_noprivs && strpos($key, '_nopriv') === 0){
                continue;
            }

            foreach ($value->callbacks as $priority => $callbacks) {

                foreach ($callbacks as $action => $details) {
                    // Check if the function is an array and get the method name
                    if (is_array($details['function']) && isset($details['function'][1])) {
                        $method_name = $details['function'][1];
                    } else {
                        $method_name = 'unknown_function';
                    }
                    $full_action_name = $method_name;

                    // Skip default WordPress functions
                    if (!in_array($key, $default_funcs)) {
                        $ajax_actions[$key] = $full_action_name;
                    }
                }
            }
        }
    }

    return $ajax_actions;
}


function print_actions($prefix){
    $actions = get_all_ajax_actions($prefix);

    echo "<h1>$prefix</h1><br>";
    echo "<ul>";
    foreach ($actions as $action => $function) {
        $url = add_query_arg('action', $action);
        echo "<li><a href='$url'>{$action}</a> → $function";
        echo "</li>";
    }
    echo "</ul>";

}

print_actions('wp_ajax_nopriv_');
print_actions('wp_ajax_');
print_actions('admin_post_nopriv_');
print_actions('admin_post');