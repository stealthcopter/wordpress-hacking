<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function what_functions_are_defined_here($prefix){
    // Get all defined functions
    $all_functions = get_defined_functions();

    // Filter user-defined functions by the prefix
    if (isset($prefix)){
        $matching_functions = array_filter($all_functions['user'], function($function) use ($prefix) {
            return strpos($function, $prefix) === 0;
        });
    }
    else{
        $matching_functions = $all_functions;
    }

    // Output the matching functions
    print_r($matching_functions);
}
