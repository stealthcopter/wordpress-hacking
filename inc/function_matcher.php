<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function what_functions_are_defined_here($prefix = null) {
    // Get all defined functions
    $all_functions = get_defined_functions();

    // Filter user-defined functions by the prefix
    if (isset($prefix)) {
        $matching_functions = array_filter($all_functions['user'], function($function) use ($prefix) {
            return strpos($function, $prefix) !== false;
        });
    } else {
        $matching_functions = $all_functions['user'];
    }

    echo "--- User-defined functions ---\n";
    print_r($matching_functions);

    echo "\n--- Public static methods (Class::method) ---\n";

    foreach (get_declared_classes() as $class) {
        foreach (get_class_methods($class) as $method) {
            try {
                $reflection = new ReflectionMethod($class, $method);
                if ($reflection->isStatic() && $reflection->isPublic()) {
                    if (!isset($prefix) || strpos($method, $prefix) !== false || strpos($class, $prefix) !== false) {
                        echo "$class::$method\n";
                    }
                }
            } catch (ReflectionException $e) {
                // ignore, in case of internal edge cases
            }
        }
    }
}
