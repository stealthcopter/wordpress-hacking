<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function get_all_actions()
{
    global $wp_filter;

    $user_filters = get_user_filters();

    $ajax_actions = [];

    // Loop through the $wp_filter to find all actions
    ksort($wp_filter);

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

        foreach ($value->callbacks as $_priority => $callbacks) {
            $i = 0;
            foreach ($callbacks as $_action => $details) {

                $slug = 'unknown';
                $item_type = 'unknown';

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

                if ($full_action instanceof Closure){
                    $key_name = $key.$i;
                }
                else{
                    $key_name = $key.$full_action;
                }

                $code = get_function_code($full_action);

                if (!is_filter_selected($user_filters, $code['slug'], $code['item_type'])){
                    continue;
                }

                $ajax_actions[md5($key_name)] = [
                    "hook"=>$key,
                    "action"=>$full_action,
                    "code" => $code,
                    "slug" => $code['slug'],
                    "item_type" => $code['item_type']
                ];

                $i++;
            }
        }
    }

    uasort($ajax_actions, function ($a, $b) {
        return strcmp((string)$a['slug'], (string)$b['slug']);
    });
    return $ajax_actions;
}

function get_function_code($function_name) {
    try {
        $slug = 'unknown';
        $item_type = 'unknown';
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

        if (strlen($code) > 0) {
            $relative_path = str_replace(ABSPATH . 'wp-content/', '', $filename);

            if (strlen($filename) === strlen($relative_path) || $function_name === '_wp_ajax_add_hierarchical_term'){
                $slug = 'default';
                $item_type = 'default';
            }
            else{
                $path_parts = explode('/', $relative_path);
                $item_type = isset($path_parts[0]) ? rtrim($path_parts[0], 's') : null;
                $slug = isset($path_parts[1]) ? $path_parts[1] : null;
            }
        }
        else{
            $slug = 'default';
            $item_type = 'default';
        }

        // Return the function/closure code
        return [
            'slug' => $slug,
            'item_type' => $item_type,
            'code' => $code,
            'file' => $filename,
            'function' => $function_name,
            'function_name' => $function_str,
            'lines' => "$start_line-$end_line",
        ];

    } catch (Throwable $e) {
        return [
            'slug' => 'default',
            'item_type' => 'default',
            'code' => ''
        ];
    }
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

function get_printable_function_name($function){
    $function_str = $function;
    if ($function instanceof Closure) {
        return "Closure";
    }
    return $function_str;
}