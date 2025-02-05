<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

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

$show_defaults = $_SESSION['show_defaults'];
$rest_routes = get_rest_routes($DEFAULT_ROUTES, $show_defaults);
$namespaces = get_namespaces();

?>

<h5 class="card-title">REST API Functions</h5>
<p>Show the currently defined registered REST API routes created with `register_rest_route`. It's a bit barebones atm,
    but aiming to make this a bit more useful that browsing <a href="../wp-json/">/wp-json</a> or <a
            href="../?rest_route=/">/?rest_route=/</a></p><p>Click on the <span class='badge bg-success' style='cursor: pointer;' onclick="alert('yes, just like that. well done.')">badges</span> to get a RAW HTTP request for that endpoint.</p>
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
    $permission_function = $rest_route['permission_callback'];

    if (!in_array($permission_function, ['unknown_function', 'none', '__return_true'])){
        echo "<h5>Permission Callback</h5>\n";

        if ($rest_route['permission_callback_code']) {
            print_code($rest_route['permission_callback_code']);
        }
        else{
            echo "???";
        }
    }

    $code_obj['route'] = $rest_route['route'];
    $code_obj['methods'] = print_method_badges($rest_route);;

    echo "<h5>Callback</h5>\n";

    if ($rest_route['callback_code']) {
        print_code($rest_route['callback_code']);
    } else {
        $msg = 'Could not find function through Reflection 🥲';
        echo $msg;
        echo "<script>showError('$msg')</script>";
    }
}

function get_current_auth_cookies(){
    $auth_cookies = '';
    if ( isset( $_COOKIE[LOGGED_IN_COOKIE] ) ) {
        $auth_cookies .= LOGGED_IN_COOKIE . '='.$_COOKIE[LOGGED_IN_COOKIE];
    }
    if ( isset( $_COOKIE[AUTH_COOKIE] ) ) {
        $auth_cookies .= ( $auth_cookies ? '; ' : '' ) . AUTH_COOKIE . '='. $_COOKIE[AUTH_COOKIE];
    }
    return $auth_cookies;
}

?>

<!-- Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Raw Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre><code class='language-http' id="raw_request_code"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
    const url = new URL('<?php echo esc_js(get_site_url()); ?>');
    const base_path = url.pathname === '/' ? '' : url.pathname;
    const cookies = '<?php echo esc_js(get_current_auth_cookies());?>';

    document.querySelectorAll('.method_badge').forEach(badge => {
        badge.addEventListener('click', function() {
            const jsonData = this.getAttribute('data-json');
            const data = JSON.parse(jsonData);

            // Run your function with the JSON data
            show_raw_http_request(data);
        });
    });


    function show_raw_http_request(data) {
        console.log(data)
        let method = data['method'];
        let parameters = data['parameters'];

        let body = ''

        let queryString = Object.values(parameters).map(key => `${key}=1`).join('&');

        let path = base_path + data['route'];
        let contentType = '';

        if (method === 'GET'){
            path = base_path + data['route'] + '?' + queryString;
        }
        else{
            contentType = 'Content-Type: application/x-www-form-urlencoded\n'
            body = `
${queryString}`
        }

        console.log(parameters)

        let output =
            `${method} ${path} HTTP/1.1
Host: ${url.host}
Cookie: ${cookies}
Accept: application/json
${contentType}${body}`;

        const codeElement = document.getElementById('raw_request_code')
        codeElement.textContent = output
        // var myModal = document.getElementById('requestModal')

        Prism.highlightElement(codeElement);

        const requestModal = new bootstrap.Modal(document.getElementById('requestModal'));
        requestModal.show();
    }

</script>
