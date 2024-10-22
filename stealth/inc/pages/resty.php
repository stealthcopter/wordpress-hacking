<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

// TODO: Hide default wordpress apis
// TODO: Show function code
function get_rest_routes()
{
    global $wp_rest_server;
    $rest_routes = [];

    // If the REST server is not initialized, initialize it
    if ( ! isset( $wp_rest_server ) ) {
        // Load the REST API infrastructure
        do_action( 'rest_api_init' );
        $wp_rest_server = rest_get_server(); // Get the REST server instance
    }

    // Get all registered REST routes
    if (isset($wp_rest_server)) {
        $routes = $wp_rest_server->get_routes();
        foreach ($routes as $route => $callbacks) {
            foreach ($callbacks as $callback) {

                // Check if methods are defined and retrieve them as strings
                $method_string = implode(', ', array_keys($callback['methods']));

                // Check if the callback is an array (method inside a class)
                if (is_array($callback['callback']) && isset($callback['callback'][1])) {
                    $class_name = is_object($callback['callback'][0])
                        ? get_class($callback['callback'][0])
                        : $callback['callback'][0];
                    $method_name = $callback['callback'][1];
                    $full_callback_name = $class_name . '::' . $method_name;
                } else if (is_string($callback['callback'])) {
                    // It's a regular function
                    $full_callback_name = $callback['callback'];
                } else {
                    $full_callback_name = 'unknown_function';
                }

                $namespace = isset($callback['namespace']) ? $callback['namespace'] : 'N/A';

                // Store the route with the method and callback
                $rest_routes[] = [
                    'namespace' => $namespace,
                    'route' => $route,
                    'method' => $method_string,
                    'callback' => $full_callback_name,
                    'permission_callback' => $full_callback_name,
                ];
            }
        }
    }

    return $rest_routes;
}

function print_actions($i, $all_actions, $prefix)
{
    $actions = [];

    // Filter only specific actions matching the prefix given
    foreach ($all_actions as $key => $value) {
        // Check if the action starts with the prefix
        if (strpos($key, $prefix) === 0) {
            // If the prefix does not contain '_nopriv', exclude actions that contain '_nopriv'
            if (strpos($prefix, '_nopriv') === false && strpos($key, '_nopriv') !== false) {
                continue; // Skip actions containing '_nopriv' if the prefix doesn't contain it
            }
            // Add the action to the filtered results while preserving the key
            $actions[$key] = $value;
        }
    }

    $title = "$prefix (" . count($actions) . ")";

    $content = "";
    foreach ($actions as $action => $function) {
        $url = add_query_arg('action', $action);
        $content .= "<li>{$action} → <a href='$url'>$function</a></li>";
    }

    if (empty($content)) {
        $content = "No functions defined";
    }

    $show = '';
    if (!isset($_REQUEST['action']) && count($actions) > 0) {
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
             style="background:#424242;" >
            <div class="accordion-body">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    <?php
}

function print_method_badges($methods) {
    // Define color mapping for each HTTP method
    $method_colors = [
        'GET'    => 'success',
        'POST'   => 'primary',
        'PUT'    => 'warning',
        'PATCH'  => 'info',
        'DELETE' => 'danger',
    ];

    // Split the method string by comma and space
    $method_list = explode(', ', $methods);
    $output = '';
    // Loop through each method and print a span with the corresponding color
    foreach ($method_list as $method) {
        $color = isset($method_colors[$method]) ? $method_colors[$method] : 'secondary'; // Default to 'secondary' if method isn't mapped
        $output .= "<span class='badge bg-$color me-2'>$method</span>";
    }
    return $output;
}

function print_rest_routes($i, $rest_routes)
{
    $title = "REST API Endpoints (" . count($rest_routes) . ")";

    $content = "";
    $i = 0;
    foreach ($rest_routes as $key => $route) {
        $url = add_query_arg('action', $key);
        //  {$route['method']}
        $method_badges = print_method_badges($route['method']);
        $content .= "<li>$method_badges {$route['route']} → <a href='$url'>{$route['callback']}</a></li>";
        $i++;
    }

    if (empty($content)) {
        $content = "No REST API routes defined";
    }

    $show = '';
    if (!isset($_REQUEST['action']) && count($rest_routes) > 0) {
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
             style="background:#424242;" >
            <div class="accordion-body">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    <?php
}

$rest_routes = get_rest_routes();

?>

    <h5 class="card-title">Functions</h5>
    <p>Show the currently defined registered REST API routes created with `register_rest_route`. It's a bit barebones atm, but aiming to make this a bit more useful that browsing <a href="../wp-json/">/wp-json</a> or <a href="../?rest_route=/">/?rest_route=/</a></p>
    <div class="accordion accordion-flush" id="accordionExample">
        <?php
        print_rest_routes(0, $rest_routes); // Add REST routes display here
        ?>
    </div>

<?php

if (isset($_REQUEST['action'])) {
    echo "<h3 class='mt-2'>Function Code</h3>";
    $action = $_REQUEST['action'];

    $rest_route = $rest_routes[$_REQUEST['action']];
    $function = $rest_route['callback'];

    $code_obj = get_function_code($function);
    $code_obj['route'] = $rest_route['route'];
    $code_obj['methods'] = print_method_badges($rest_route['method']);;

    if ($code_obj){
        print_code($code_obj);
    }
    else{
        $msg = 'Could not find function through Reflection 🥲';
        echo $msg;
        echo "<script>showError('$msg')</script>";
    }
}

