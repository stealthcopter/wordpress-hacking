<?php

require_once 'inc/code.php';

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

?>
    <p>Show the currently registered shortcodes and the functions associated with them.</p>
<?php

global $shortcode_tags;

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

echo "<h2>Registered Shortcodes:</h2>";
echo "<ul>";
foreach ($shortcode_tags as $shortcode => $function) {
    $url = add_query_arg('shortcode', $shortcode);
    echo "<li>{$shortcode} → <a href='$url'>";
    $function_name = get_function_name($shortcode);
    if ($function_name instanceof Closure) {
        print_r($function_name);
    } else {
        echo get_function_name($shortcode);
    }
    echo "</a></li>";
}
echo "</ul>";

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
        echo "<button class='btn btn-outline-success m-2'>$attribute</button>";
        $test .= "$attribute='test' ";
    }
    $test = trim($test).']';

    echo "<textarea id='textarea_shortcode' class='form-control m-2' rows=3>$test</textarea>";
    echo "<button id='btn_do_shortcode' class='btn btn-success mb-2' title='Execute the shortcode and see the output'>Do Shortcode</button><br>";

    echo "<iframe id='shortcode_iframe' class='w-100 d-none' class='m-2'>hello</iframe>";

    // Step 5: Display the function code with syntax highlighting
    echo '<hr class="bg-danger border-2 border-top" />';
    echo "<h3>Function Code</h3>";
    print_code($code_obj);
} else {
    echo "<p>Select a shortcode from the list above to view its details.</p>";
}

?>

<script>
    // Add an event listener to the button
    document.getElementById('btn_do_shortcode').addEventListener('click', function() {
        // Get the shortcode from the textarea
        var shortcode = document.getElementById('textarea_shortcode').value;

        // Construct the new URL with query parameters
        var url = window.location.href.split('?')[0] + '?api=do_shortcode&shortcode=' + encodeURIComponent(shortcode);

        // Make the iframe visible and set its src attribute to the new URL
        var iframe = document.getElementById('shortcode_iframe');
        iframe.classList.remove('d-none');  // Make iframe visible by removing the 'd-none' class
        iframe.src = url;
    });
</script>



// Call the function
//echo freeworld_html5map_plugin_content(['abc'=>'133'], "contnets");

// TODO: Find attributes
// TODO: Find if accepts content
// TODO: Check for reflection ' "
// TODO: Check for encoding
// TODO: Check for LFI
// TODO: Run shortcodes on demand
