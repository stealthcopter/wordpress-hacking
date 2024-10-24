<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}


// TODO: Hide default wordpress apis
// TODO: Split up by namespaces
// TODO: Show permissions callback
// TODO: Highlight permissions __return_true
// TODO: Annotate for nonce check, current_user_can
function get_namespaces()
{
    global $wp_rest_server;
    // If the REST server is not initialized, initialize it
    if ( ! isset( $wp_rest_server ) ) {
        // Load the REST API infrastructure
        do_action( 'rest_api_init' );
        $wp_rest_server = rest_get_server(); // Get the REST server instance
    }

    $namespaces = $wp_rest_server->get_namespaces();

    // Add 'none' to the front of the namespaces array
    array_unshift($namespaces, 'none');

    return $namespaces;
}
function get_rest_routes($DEFAULT_ROUTES, $show_defaults)
{
    global $wp_rest_server;
    $rest_routes = [];

    // If the REST server is not initialized, initialize it
    if ( ! isset( $wp_rest_server ) ) {
        // Load the REST API infrastructure
        do_action( 'rest_api_init' );
        $wp_rest_server = rest_get_server(); // Get the REST server instance
    }
    if (!isset($wp_rest_server)) {
        return $rest_routes;
    }

    // Get all registered namespaces
    $namespaces = $wp_rest_server->get_namespaces();

    // Get all registered REST routes
    $routes = $wp_rest_server->get_routes();
    foreach ($routes as $route => $callbacks) {

        if (!$show_defaults && in_array($route, $DEFAULT_ROUTES)){
            continue;
        }

        foreach ($callbacks as $callback) {

            // Determine which namespace the route belongs to
            $namespace = 'none'; // Default to unknown
            foreach ($namespaces as $ns) {
                // Check if the route starts with the namespace (either exactly or followed by "/")
                if (strpos($route, "/$ns") === 0) {
                    $namespace = $ns;
                    break;
                }
            }

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

    return $rest_routes;
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

function print_rest_routes($i, $rest_routes, $namespace)
{

    $route_count = 0;
    $content = "<ul class='ps-0 mb-0' style='list-style-type: none;'>";
    foreach ($rest_routes as $key => $route) {
        if ($route['namespace'] != $namespace){
            continue;
        }
        $route_count++;
        $url = add_query_arg('action', $key);
        //  {$route['method']}
        $method_badges = print_method_badges($route['method']);
        $content .= "<li>$method_badges {$route['route']} → <a href='$url'>{$route['callback']}</a></li>";
    }
    $content .= "</ul>";

    $title = "$namespace (" . $route_count . ")";

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

$show_defaults = $_SESSION['show_defaults'];
$rest_routes = get_rest_routes($DEFAULT_ROUTES, $show_defaults);
$namespaces = get_namespaces();

?>

    <h5 class="card-title">Functions</h5>
    <p>Show the currently defined registered REST API routes created with `register_rest_route`. It's a bit barebones atm, but aiming to make this a bit more useful that browsing <a href="../wp-json/">/wp-json</a> or <a href="../?rest_route=/">/?rest_route=/</a></p>
    <?php echo show_defaults_toggle(); ?>
    <div class="accordion accordion-flush mb-4" id="accordionExample">
        <?php
        $i = 0;
        foreach ($namespaces as $key => $namespace) {
            print_rest_routes($i++, $rest_routes, $namespace);
        }
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

