<?php

require_once 'inc/code.php';

function extract_shortcode_attributes($php_code)
{
    $matches = [];
    $used_shortcode_atts = false;
    $used_extract = false;

    // Check if $atts is the first parameter
    if (preg_match('/function\s*\w*\s*\(\s*\$atts/', $php_code)) {

        // Regex to find `$atts['something']`
        preg_match_all("/\\\$atts\['(.*?)'\]/", $php_code, $atts_matches);
        if (!empty($atts_matches[1])) {
            $matches = array_unique($atts_matches[1]);  // Get unique attributes and flatten the array
        }

        // Regex to find shortcode_atts array keys
        if (preg_match_all('/shortcode_atts\s*\(\s*array\s*\((.*?)\)\s*,/s', $php_code, $shortcode_atts_matches)) {
            $used_shortcode_atts = true;
            foreach ($shortcode_atts_matches[1] as $shortcode_atts_block) {
                // Extract keys from array inside shortcode_atts
                if (preg_match_all("/'(.*?)'\s*=>/", $shortcode_atts_block, $shortcode_keys)) {
                    $matches = array_unique(array_merge($matches, $shortcode_keys[1]));
                }
            }
        }

        // Check if extract() is used
        if (preg_match('/\bextract\s*\(/', $php_code)) {
            $used_extract = true;
        }

    } else {
        // If $atts is not the first param, return an empty array
        return [];
    }

    // Return results along with flags for `shortcode_atts` and `extract`
    return [
        'attributes' => array_values($matches),  // Ensure it's a proper indexed array
        'used_shortcode_atts' => $used_shortcode_atts,
        'used_extract' => $used_extract
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

    echo "<h2>Shortcode: [{$shortcode}]</h2>";

    echo "<p>Function Name: ";
    if ($function_name instanceof Closure) {
        print_r($function_name);
    } else {
        echo get_function_name($shortcode);
    }
    echo "</p>";

    // Step 3: Get the function code
    $code_obj = get_function_code($function_name);
    $php_code = $code_obj['code'];

    // Step 4: Extract shortcode attributes
    $result = extract_shortcode_attributes($php_code);

    echo "<h3>Extracted Shortcode Attributes:</h3>";
    echo "<pre>" . print_r($result, true) . "</pre>";

    // Step 5: Display the function code with syntax highlighting
    echo "<h3>Function Code</h3>";
    print_code($code_obj);
} else {
    echo "<p>Select a shortcode from the list above to view its details.</p>";
}



// Call the function
//echo freeworld_html5map_plugin_content(['abc'=>'133'], "contnets");

// TODO: Find attributes
// TODO: Find if accepts content
// TODO: Check for reflection ' "
// TODO: Check for encoding
// TODO: Check for LFI
// TODO: Run shortcodes on demand
