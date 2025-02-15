<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

global $DEFINED_SHORTCODES;

?>
    <p>Show the currently registered shortcodes and the functions associated with them. <a href="<?php echo admin_url('post-new.php'); ?>" target="_blank">Create New Post</a></p>
<?php

global $shortcode_tags;
$show_defaults = $_SESSION['show_defaults'];

$old_error_reporting = error_reporting();
// Turn on all errors, warnings, and notices
error_reporting(E_ALL);

// API
if (isset($_REQUEST['do_shortcode'])) {
    $shortcode = wp_unslash($_REQUEST['do_shortcode']);
//    echo $shortcode."\n\n";
    $result = do_shortcode($shortcode);
    // TODO: Fail if not shortcoded
    die($result);
}
if (isset($_REQUEST['list'])) {
    wp_send_json($shortcode_tags);
}
if (isset($_REQUEST['attrs'])) {
    // Step 3: Get the function code
    if (array_key_exists($_REQUEST['attrs'], $shortcode_tags)) {
        $function_name = $shortcode_tags[$_REQUEST['attrs']];
        $result = get_function_code($function_name);
        $php_code = $result['code'];
        $result = extract_shortcode_attributes($php_code);
        wp_send_json($result);
    }
    wp_send_json_error();
}

echo show_defaults_toggle();

echo "<h2>Registered Shortcodes (" . count($DEFINED_SHORTCODES) . ")</h2>";
if (empty($DEFINED_SHORTCODES)){
    echo "<p>No shortcodes found...</p>";
}
else{
    echo "<ul>";
    foreach ($DEFINED_SHORTCODES as $shortcode => $function) {
        $text_color = '';
        $title ='';

        foreach ($DEFAULT_SHORTCODES as $name => $shortcodes) {
            if (in_array($shortcode, $shortcodes)) {
                $text_color = $PLUGIN_COLOR_MAP[$name] ?? 'text-default';
                $title = ucfirst($name) . ' Shortcode';
                break;
            }
        }

        $url = add_query_arg('shortcode', $shortcode);
        echo "<li class='$text_color' title='$title'>{$shortcode} → <a href='$url'>";
        $function_name = get_function_name($shortcode);
        if ($function_name instanceof Closure) {
            echo get_printable_function_name($function_name);
        } else {
            echo get_function_name($shortcode);
        }
        echo "</a> <small>(".count_attributes($shortcode)." attributes)</small></li>";
    }
    echo "</ul>";
    echo "<p>Select a shortcode from the list above to view its details.</p>";
}

// Step 2: Handle the clicked shortcode and display function details
if (isset($_GET['shortcode']) && array_key_exists($_GET['shortcode'], $shortcode_tags)) {
    $shortcode = $_GET['shortcode'];
    $function_name = get_function_name($shortcode);

    echo '<hr class="bg-danger border-2 border-top" />';
    echo "<h2>Shortcode: [{$shortcode}]</h2>";

    // Step 3: Get the function code
    $code_obj = get_function_code($function_name);
    $php_code = $code_obj['code'];

    // Step 4: Extract shortcode attributes
    $result = extract_shortcode_attributes($php_code);

    echo "<h3>Extracted Shortcode Attributes:</h3>";
    $test = "[$shortcode ";
    foreach ($result['attributes'] as $attribute) {
        echo "<button class='btn btn-outline-success mt-2 mb-2 me-2'>$attribute</button>";
        $test .= "$attribute='test' ";
    }
    $test = trim($test).']';

    echo "<div class='form-group'><div class='input-group'><textarea id='textarea_shortcode' class='form-control' rows=3>$test</textarea>";
    echo "<button id='btn_do_shortcode' class='btn btn-success' title='Execute the shortcode and see the output'>Do Shortcode</button><br></div></div>";

    $shortcode_iframe = "<div><h4>HTML Output</h4><iframe id='shortcode_iframe' class='w-100 d-none' class='m-2'>hello</iframe></div>";
    $shortcode_code = "<div><h4>Raw HTML Output</h4><div id='shortcode_code_output' class='m-2'><div class='spinner-border text-primary' role='status'></div></div></div>";

    echo "<div id='shortcode_execution_output' class='d-none mt-2 mb-2'>";
    echo display_in_columns($shortcode_iframe, $shortcode_code);
    echo "</div>";

    // Step 5: Display the function code with syntax highlighting
    echo '<hr class="bg-danger border-2 border-top" />';
    echo "<h3>Function Code</h3>";
    print_code($code_obj);
}

?>

<script>
    // Add an event listener to the button
    document.getElementById('btn_do_shortcode').addEventListener('click', function() {
        // Get the shortcode from the textarea
        document.getElementById('shortcode_execution_output').classList.remove('d-none');

        var shortcode = document.getElementById('textarea_shortcode').value;

        var url = window.location.href.split('?')[0] + '?api=do_shortcode&shortcode=' + btoa(shortcode);

        // Use fetch to perform the request
        fetch(url+'&code=1')
            .then(response => response.text())  // Parse the response as text
            .then(data => {
                // Set the response to the innerHTML of the shortcode_code_output element
                document.getElementById('shortcode_code_output').innerHTML = data;
                Prism.highlightAll();
            })
            .catch(error => {
                console.error('Error:', error);
            });

        // Make the iframe visible and set its src attribute to the new URL
        var iframe = document.getElementById('shortcode_iframe');
        iframe.classList.remove('d-none');  // Make iframe visible by removing the 'd-none' class
        iframe.src = url;
    });
</script>

<!--TODO: Find attributes based on array access from function param-->

<!--TODO: Find if accepts content-->
<!--TODO: Check for reflection ' "-->
<!--TODO: Check for encoding-->
<!--TODO: Check for LFI-->
