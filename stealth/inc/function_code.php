<?php

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
            $code .= $file->current();
        }

        // Check if the code contains 'extract' (for your other purpose)
        $extract = str_contains(strtolower($code), 'extract');

        // Return the function/closure code
        return [
            'code' => $code,
            'file' => $filename,
        ];

    } catch (ReflectionException $e) {
        return false;
    }
}