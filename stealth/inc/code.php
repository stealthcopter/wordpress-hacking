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
            $current = $file->current();
            if ($line == $start_line && (empty($current) || trim($current) == '*/')){
                continue;
            }
            $code .= $file->current();
        }

        $function_str = $function_name;
        if ($function_name instanceof Closure) {
            ob_start();
            print_r($function_name);
            $function_str = ob_get_clean();
        }

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

function print_code($code_obj) {
    if (is_array($code_obj)) {
        $php_code = $code_obj['code'];

        $mapping = [
            'action' => 'Action',
            'function_name' => 'Function',
            'file' => 'Filename',
            'lines' => 'Lines',
        ];

        $data = [];

        foreach ($mapping as $key => $label) {
            if (isset($code_obj[$key])) {
                $data[$label] = $code_obj[$key];
            }
        }

        echo key_value_table($data, true);
    }
    else{
        $php_code = $code_obj;
    }
    echo "<pre><code class='language-php'>" . htmlspecialchars($php_code) . "</code></pre>";
}