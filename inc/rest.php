<?php

function get_namespaces()
{
    global $wp_rest_server;
    // If the REST server is not initialized, initialize it
    if (!isset($wp_rest_server)) {
        // Load the REST API infrastructure
        do_action('rest_api_init');
        $wp_rest_server = rest_get_server(); // Get the REST server instance
    }

    $namespaces = $wp_rest_server->get_namespaces();

    // Add 'none' to the front of the namespaces array
    array_unshift($namespaces, 'none');

    return $namespaces;
}

function get_full_callback_name($callback) {
    if (is_array($callback) && isset($callback[1])) {
        $class_name = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
        $method_name = $callback[1];
        return $class_name . '::' . $method_name;
    } elseif (is_string($callback)) {
        return $callback;
    }
    elseif ($callback instanceof Closure) {
        return $callback;
    } else {
        return 'unknown_function';
    }
}

function extract_parameters($php_code)
{
    $matches = [];

    if (empty($php_code)) {
        return $matches;
    }

    if (!is_string($php_code)) {
        return $matches;
    }

    $used_shortcode_atts = false;

    // Regex to find `$atts['something']`
    preg_match_all('/(\$request\s*\[|->get_param\()\s*["\']([\w-]+)["\']/', $php_code, $parameter_matches);
    if (!empty($parameter_matches[2])) {
        $matches = array_unique($parameter_matches[2]);  // Get unique attributes and flatten the array
    }

    return $matches;
}


function get_rest_routes()
{
    global $wp_rest_server;
    $rest_routes = [];

    $user_filters = get_user_filters();

    // If the REST server is not initialized, initialize it
    if (!isset($wp_rest_server)) {
        // Load the REST API infrastructure
        do_action('rest_api_init');
        $wp_rest_server = rest_get_server(); // Get the REST server instance
    }
    if (!isset($wp_rest_server)) {
        return $rest_routes;
    }

    // Get all registered namespaces
    $namespaces = $wp_rest_server->get_namespaces();

    // Get all registered REST routes
    $routes = $wp_rest_server->get_routes();
    $seen_routes = [];

    foreach ($routes as $route => $callbacks) {
        // Determine which namespace the route belongs to
        $namespace = 'none'; // Default to unknown
        foreach ($namespaces as $ns) {
            // Check if the route starts with the namespace (either exactly or followed by "/")
            if (strpos($route, "/$ns") === 0) {
                $namespace = $ns;
                break;
            }
        }

        foreach ($callbacks as $callback) {

            // Check if methods are defined and retrieve them as strings
            $method_string = implode(', ', array_keys($callback['methods']));

            $full_callback_name = get_full_callback_name($callback['callback']);

            if (isset($callback['permission_callback'])){
                $full_permission_callback_name = get_full_callback_name($callback['permission_callback']);
            }
            else{
                $full_permission_callback_name = 'none';
            }

            // For de-duplication
            if (!$full_callback_name instanceof Closure){
                $callback_id = $method_string . '_' . $full_callback_name;
                if (in_array($callback_id, $seen_routes)) {
                    // Skip this callback if we've already processed it
                    continue;
                }
                $seen_routes[] = $callback_id;
            }

            $code = get_function_code($full_callback_name);
            $permission_code = get_function_code($full_permission_callback_name);

            if (!is_filter_selected($user_filters, $code['slug'], $code['item_type'])){
                continue;
            }

            $code['parameters'] = extract_parameters($code['code']);

            // Store the route with the method and callback
            $rest_routes[] = [
                'namespace' => $namespace,
                'route' => $route,
                'method' => $method_string,
                'callback' => $full_callback_name,
                'callback_code' =>$code,
                'permission_callback' => $full_permission_callback_name,
                'permission_callback_code' => $permission_code
            ];
        }
    }

    return $rest_routes;
}

