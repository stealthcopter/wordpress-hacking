<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function load_admin(){
    include ABSPATH . '/wp-admin/includes/admin.php';
    define('WP_ADMIN', true);
}

load_admin();

function get_all_actions($defaults, $show_defaults)
{
    global $wp_filter;

    $ajax_actions = [];

    // Loop through the $wp_filter to find all actions
    foreach ($wp_filter as $key => $value) {

        if (!$show_defaults && in_array($key, $defaults)) {
            continue;
        }

        foreach ($value->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $action => $details) {
                if (is_array($details['function']) && isset($details['function'][1])) {
                    // It's a method inside a class
                    $class_name = is_object($details['function'][0])
                        ? get_class($details['function'][0])
                        : $details['function'][0];
                    $method_name = $details['function'][1];
                    $full_action_name = $class_name . '::' . $method_name;
                } else if (is_string($details['function'])) {
                    // It's a regular function
                    $full_action_name = $details['function'];
                } else {
                    $full_action_name = 'unknown_function';
                }

                $ajax_actions[$key] = $full_action_name;
            }
        }
    }

    return $ajax_actions;
}

function print_actions($i, $all_actions, $prefix)
{
    global $DEFAULT_ACTIONS, $PLUGIN_COLOR_MAP;
    $actions = [];

    // Filter only specific actions matching the prefix given
    foreach ($all_actions as $key => $value) {
        // Check if the action starts with the prefix
        if (strpos($key, $prefix) === 0) {
            // If the prefix does not contain '_nopriv', exclude actions that contain '_nopriv'
            if (strpos($prefix, '_nopriv') === false && strpos($key, '_nopriv') !== false) {
                continue; // Skip actions containing '_nopriv' if the prefix doesn't contain it
            }
            // Add the action to the filtered results while preserving the key
            $actions[$key] = $value;
        }
    }

    $title = "$prefix (" . count($actions) . ")";

    $content = "";
    foreach ($actions as $action => $function) {

        $text_color = '';
        $li_title ='';

        foreach ($DEFAULT_ACTIONS as $name => $def_actions) {
            if (in_array($action, $def_actions)) {
                $text_color = $PLUGIN_COLOR_MAP[$name] ?? 'text-default';
                $li_title = ucfirst($name) . ' Action';
                break;
            }
        }

        $url = add_query_arg('action', $action);
        $content .= "<li class='${text_color}' title='${li_title}'>{$action} → <a href='$url'>$function</a></li>";
    }

    if (empty($content)) {
        $content = "No functions defined";
    }

    $show = '';
    if (!isset($_REQUEST['action']) && count($actions) > 0) {
        $show = 'show';
    }

    ?>
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed bg-secondary text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="true"
                    aria-controls="collapse<?php echo $i; ?>">
                <?php echo $title; ?>
            </button>
        </h2>
        <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $show; ?>"
             style="background:#424242;" >
            <div class="accordion-body">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    <?php
}

$show_defaults = $_SESSION['show_defaults'];
$actions = get_all_actions($DEFAULT_ACTIONS['default'], $show_defaults);
?>

    <h5 class="card-title">Functions</h5>
    <p>Show the currently defined functions created with `add_action`.</p>
<?php echo show_defaults_toggle(); ?>
    <div class="accordion accordion-flush" id="accordionExample">
        <?php

        print_actions(0, $actions, 'wp_ajax_nopriv_');
        print_actions(1, $actions, 'wp_ajax_');
        print_actions(2, $actions, 'admin_post_nopriv_');
        print_actions(3, $actions, 'admin_post');

        ?>
    </div>

<?php

if (isset($_REQUEST['action'])) {
    echo "<h3 class='mt-2'>Function Code</h3>";
    $action = $_REQUEST['action'];
    $function = $actions[$_REQUEST['action']];
    $code_obj = get_function_code($function);
    $code_obj['action'] = $action;

    if ($code_obj){
        print_code($code_obj);
    }
    else{
        $msg = 'Could not find function through Reflection 🥲';
        echo $msg;
        echo "<script>showError('$msg')</script>";
    }
}
