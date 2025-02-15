<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

?>

<h5 class="card-title">REST API Functions</h5>
<p>Show the currently defined registered REST API routes created with `register_rest_route`. It's a bit barebones atm,
    but aiming to make this a bit more useful that browsing <a href="../wp-json/">/wp-json</a> or <a
            href="../?rest_route=/">/?rest_route=/</a></p><p>Click on the <span class='badge bg-success' style='cursor: pointer;' onclick="alert('yes, just like that. well done.')">badges</span> to get a RAW HTTP request for that endpoint.</p>
<?php echo show_defaults_toggle(); ?>
<div class="accordion accordion-flush mb-4" id="accordionExample">
    <?php
    $i = 0;
    foreach ($DEFINED_NAMESPACES as $key => $namespace) {
        print_rest_routes($i++, $DEFINED_ROUTES, $namespace);
    }
    ?>
</div>

<?php

if (isset($_REQUEST['action'])) {
    echo "<h3 class='mt-2'>Function Code</h3>";
    $action = $_REQUEST['action'];

    $rest_route = $DEFINED_ROUTES[$_REQUEST['action']];
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
