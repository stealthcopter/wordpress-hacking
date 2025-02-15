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


function get_rest_routes($DEFAULT_ROUTES, $show_defaults)
{
    global $wp_rest_server;
    $rest_routes = [];

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

        if (!$show_defaults && in_array($route, $DEFAULT_ROUTES)) {
            continue;
        }

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

function print_permissions_badges($route){
    if ($route['permission_callback'] == 'none'){
        return "<span class='badge bg-danger ms-2' title='Missing permissions callback!'>No Auth</span>";
    }
    elseif ($route['permission_callback'] == '__return_true'){
        return "<span class='badge bg-danger ms-2' title='Missing permissions callback! (__return_true)'>No Auth</span>";
    }
    return '';
}
function print_method_badges($route)
{
    // Define color mapping for each HTTP method
    $methods = $route['method'];
    $method_colors = [
        'GET' => 'success',
        'POST' => 'primary',
        'PUT' => 'warning',
        'PATCH' => 'info',
        'DELETE' => 'danger',
    ];

    // Split the method string by comma and space
    $method_list = explode(', ', $methods);
    $output = '';
    // Loop through each method and print a span with the corresponding color
    foreach ($method_list as $method) {
        $data = [
            'method' => $method,
            'route' => $route['route'],
            'parameters' => $route['callback_code']['parameters'] ?? [],
        ];
        $data_json = esc_attr(json_encode($data));
        $color = isset($method_colors[$method]) ? $method_colors[$method] : 'secondary'; // Default to 'secondary' if method isn't mapped
        $output .= "<span class='method_badge badge bg-$color me-2' style='cursor: pointer;' data-json='$data_json'>$method</span>";
    }
    return $output;
}

function print_rest_routes($i, $rest_routes, $namespace)
{

    $is_requested_action = false;
    $route_count = 0;
    $content = "<ul class='ps-0 mb-0' style='list-style-type: none;'>";
    foreach ($rest_routes as $key => $route) {
        if ($route['namespace'] != $namespace) {
            continue;
        }
        if (isset($_REQUEST['action']) && $key == $_REQUEST['action']){
            $is_requested_action = true;
        }
        $route_count++;
        $url = add_query_arg('action', $key);
        //  {$route['method']}
        $method_badges = print_method_badges($route);
        $extra_badges = print_permissions_badges($route);

        $parameters = '';
        if (isset($route['callback_code']) && !empty($route['callback_code']['parameters'])){
            $parameters = '- <small>' . count($route['callback_code']['parameters']) . ' parameters</small>';
        }

        $function_str = get_printable_function_name($route['callback']);
        $content .= "<li>$method_badges ".htmlentities($route['route'])." → <a href='$url'>{$function_str}</a> $extra_badges $parameters</li>";
    }
    $content .= "</ul>";

    $title = "Namespace: $namespace (" . $route_count . ")";

    if ($route_count == 0) {
        $content = "No REST API routes defined";
    }

    $show = '';
    if ($is_requested_action || (!isset($_REQUEST['action']) && $route_count > 0)) {
        $show = 'show';
    }

    ?>
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed bg-secondary text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="true"
                    aria-controls="collapse<?php echo $i; ?>">
                <?php echo $title; ?>
            </button>
        </h2>
        <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $show; ?>"
             style="background:#424242;">
            <div class="accordion-body">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    <?php
}