<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function extract_shortcode_attributes($php_code)
{
    $matches = [];
    $used_shortcode_atts = false;

    // Check if $atts is the first parameter
    if (preg_match('/function\s*\w*\s*\(\s*\$att/', $php_code)) {

        // Regex to find `$atts['something']`
        preg_match_all("/\\\$att\w*\s*\[\s*'(.*?)'\]/", $php_code, $atts_matches);
        if (!empty($atts_matches[1])) {
            $matches = array_unique($atts_matches[1]);  // Get unique attributes and flatten the array
        }
    }

    // Regex to find shortcode_atts array keys
    if (preg_match_all('/shortcode_atts[\s\n]*\([\s\n]*array([^)]*?)\)/', $php_code, $shortcode_atts_matches)) {
        $used_shortcode_atts = true;
        foreach ($shortcode_atts_matches[1] as $shortcode_atts_block) {
            // Extract keys from array inside shortcode_atts
            if (preg_match_all("/'(.*?)'\s*=>/", $shortcode_atts_block, $shortcode_keys)) {
                $matches = array_unique(array_merge($matches, $shortcode_keys[1]));
            }
        }
    }

    // Return results along with flags for `shortcode_atts` and `extract`
    return [
        'attributes' => array_values($matches),  // Ensure it's a proper indexed array
        'used_shortcode_atts' => $used_shortcode_atts,
    ];
}

function get_function_name($shortcode_name)
{
    global $shortcode_tags;
    if (array_key_exists($shortcode_name, $shortcode_tags)) {
        $function = $shortcode_tags[$shortcode_name];
        if (is_object($function) && ($function instanceof Closure)) {
            return $function;
        } else if (is_array($function)) {
            // Check if the first element is an object and handle it
            if (is_object($function[0])) {
                return get_class($function[0]) . '::' . $function[1];
            } else {
                return $function[0] . '::' . $function[1];
            }
        } else if (is_string($function)) {
            return $function;
        } else {
            return "Unknown type: " . gettype($function);
        }
    }
    return "";
}

function count_attributes($shortcode){
    $function_name = get_function_name($shortcode);
    $code_obj = get_function_code($function_name);
    $php_code = $code_obj['code'];
    $result = extract_shortcode_attributes($php_code);
    return count($result['attributes']);
}

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

function get_shortcodes($defaults_to_ignore, $show_defaults) {
    global $shortcode_tags;
    if ($show_defaults){
        return $shortcode_tags;
    }
    // Use array_diff_key to filter out keys from $shortcode_tags
    return array_diff_key($shortcode_tags, array_flip($defaults_to_ignore));
}

$shortcodes = get_shortcodes($DEFAULT_SHORTCODES, $show_defaults);

echo show_defaults_toggle();

echo "<h2>Registered Shortcodes (" . count($shortcodes) . ")</h2>";
if (empty($shortcodes)){
    echo "<p>No shortcodes found...</p>";
}
else{
    echo "<ul>";
    foreach ($shortcodes as $shortcode => $function) {
        $text_color = '';
        $title ='';
        if (in_array($shortcode, $DEFAULT_SHORTCODES)){
            $text_color = 'text-secondary';
            $title = 'Built-in WordPress Shortcode';
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


<!--// TODO: Find if accepts content-->
<!--// TODO: Check for reflection ' "-->
<!--// TODO: Check for encoding-->
<!--// TODO: Check for LFI-->
