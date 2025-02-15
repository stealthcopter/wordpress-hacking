<?php

function get_shortcodes($defaults_to_ignore, $show_defaults) {
    global $shortcode_tags;
    if ($show_defaults){
        return $shortcode_tags;
    }
    // Use array_diff_key to filter out keys from $shortcode_tags
    return array_diff_key($shortcode_tags, array_flip($defaults_to_ignore));
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