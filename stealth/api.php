<?php

$api = $_REQUEST['api'];

if ($api === 'do_shortcode'){

    include "inc/code.php";

    $output = do_shortcode(base64_decode($_REQUEST['shortcode']));

    if (isset($_REQUEST['code'])){
        if (empty($output)){
            echo "⚠️ No output!!!";
        }
        else{
            print_code($output, 'html');
        }
    }
    else{
        echo $output;
    }
}