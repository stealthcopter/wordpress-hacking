<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function get_all_actions($show_defaults)
{
    global $wp_filter;
    global $DEFAULT_ACTIONS;
    global $DEFAULT_FUNCTIONS;

    $defaults = $DEFAULT_ACTIONS['default'];

    $ajax_actions = [];

    // Loop through the $wp_filter to find all actions
    foreach ($wp_filter as $key => $value) {

        if (
            strpos($key, 'wp_ajax_') !== 0 &&
            strpos($key, 'admin_post') !== 0 &&
            $key != 'admin_init' &&
            $key != 'init'
        )
        {
            continue;
        }

        if (!$show_defaults && in_array($key, $defaults)) {
            continue;
        }

        foreach ($value->callbacks as $_priority => $callbacks) {
            $i = 0;
            foreach ($callbacks as $_action => $details) {
                if (is_array($details['function']) && isset($details['function'][1])) {
                    // It's a method inside a class
                    $class_name = is_object($details['function'][0])
                        ? get_class($details['function'][0])
                        : $details['function'][0];
                    $method_name = $details['function'][1];
                    $full_action = $class_name . '::' . $method_name;
                } else if (is_string($details['function'])) {
                    // It's a regular function
                    $full_action = $details['function'];
                }
                else if (is_object($details['function']) && ($details['function'] instanceof Closure)){
                    $full_action = $details['function'];
                } else {
                    $full_action = 'unknown_function';
                }

                if (!$show_defaults && in_array($full_action, $DEFAULT_FUNCTIONS['default'])) {
                    continue;
                }

                if ($full_action instanceof Closure){
                    $key_name = $key.$i;
                }
                else{
                    $key_name = $key.$full_action;
                }

                $ajax_actions[md5($key_name)] = ["hook"=>$key, "action"=>$full_action];

                $i++;
            }
        }
    }

    return $ajax_actions;
}

function get_function_code($function_name) {
    try {
        // Handle class methods
        if (is_object($function_name) && ($function_name instanceof Closure)) {
            // Handle closures using ReflectionFunction
            $function = new ReflectionFunction($function_name);
        }
        else if (strpos($function_name, '::') !== false) {
            // Split the function name into class and method
            list($class_name, $method_name) = explode('::', $function_name);
            // Create a ReflectionMethod object
            $function = new ReflectionMethod($class_name, $method_name);
        } else {
            // Handle standalone functions
            $function = new ReflectionFunction($function_name);
        }

        $filename = $function->getFileName();

        // Get the file where the function/method/closure is defined
        $file = new SplFileObject($filename);

        // Get the start and end lines of the function/method/closure
        $start_line = $function->getStartLine() - 2;  // Reflection lines are 1-based, so subtract 1
        $end_line = $function->getEndLine();

        // Read only the lines between the start and end of the function/method/closure
        $code = '';
        for ($line = $start_line; $line < $end_line; $line++) {
            $file->seek($line);
            $current = $file->current();
            if ($line == $start_line && (empty($current) || trim($current) == '*/')){
                continue;
            }
            $code .= $file->current();
        }

        $function_str = get_printable_function_name($function_name);

        // Return the function/closure code
        return [
            'code' => $code,
            'file' => $filename,
            'function' => $function_name,
            'function_name' => $function_str,
            'lines' => "$start_line-$end_line",
        ];

    } catch (ReflectionException $e) {
        return false;
    }
}

function print_code($code_obj, $language='php') {
    if (is_array($code_obj)) {
        $php_code = $code_obj['code'];

        $mapping = [
            'action' => 'Action',
            'link' => 'Link',
            'route' => 'Route',
            'methods' => 'Method(s)',
            'parameters' => 'Parameter(s)',
            'function_name' => 'Function',
            'file' => 'Filename',
            'lines' => 'Lines',
        ];

        $data = [];

        foreach ($mapping as $key => $label) {
            if (!empty($code_obj[$key])) {
                $data[$label] = copyable($code_obj[$key]);
            }
        }

        $analysis = code_analysis($php_code);
        print_analysis_results($analysis);

        echo key_value_table($data, true);
    }
    else{
        $php_code = $code_obj;
    }

    $php_code = trim($php_code); // Trim empty lines from start/end

    // TODO: Deindent code if it's all indented by a uniform amount

    echo "<pre><code class='language-$language'>" . htmlspecialchars($php_code) . "</code></pre>";
}

function code_analysis($code){
    // Define some basic tests with regex patterns and descriptions
    $tests = [
        [
            "name" => "extract",
            "type" => "bug",
            "pattern" => "/\bextract\s*\(/",
            "description" => "Detects usage of the extract function, which can lead to security issues like variable injection."
        ],
        [
            "name" => "shortcode_atts",
            "type" => "info",
            "pattern" => "/\bshortcode_atts\s*\(/",
            "description" => "shortcode_atts is used to parse shortcode attributes."
        ],
        [
            "name" => "current_user_can",
            "type" => "protection",
            "pattern" => "/\bcurrent_user_can\s*\(/",
            "description" => "current_user_can is used to check user permissions."
        ],
        [
            "name" => "Nonce Verification",
            "type" => "protection",
            "pattern" => "/\b(wp_verify_nonce|check_ajax_referer|check_admin_referer)\s*\(/",
            "description" => "wp_verify_nonce, check_ajax_referer or check_admin_referer is used to verify a nonce."
        ],
    ];

    // Initialize results array
    $results = [];

    // Loop through tests and perform regex matching
    foreach ($tests as $test) {
        // Perform regex match and store results
        if (preg_match($test['pattern'], $code)){
            $results[$test['name']] = [
                "name" => $test['name'],
                "type" => $test['type'],
                "match" => preg_match($test['pattern'], $code),
                "description" => $test['description']
            ];
        }
    }

    return $results;
}

function print_buttons($tests, $color) {
    foreach ($tests as $test) {
        echo '<button type="button" class="btn ' . $color . ' m-2" title="' . htmlspecialchars($test['description']) . '">' . htmlspecialchars($test['name']) . '</button> ';
    }
}
function print_analysis_results($results) {
    // Initialize arrays for categorizing results
    $categories = [
        "protection" => [],
        "info" => [],
        "bug" => []
    ];

    // Categorize the results based on their type
    foreach ($results as $name => $test) {
        if (array_key_exists($test['type'], $categories)) {
            $categories[$test['type']][] = $test;
        }
    }

    // Print sections
    print_buttons($categories['protection'], 'btn-success');
    print_buttons($categories['info'], 'btn-warning');
    print_buttons($categories['bug'], 'btn-danger');
}


function get_printable_function_name($function){
    $function_str = $function;
    if ($function instanceof Closure) {
        return "Closure";
    }
    return $function_str;
}