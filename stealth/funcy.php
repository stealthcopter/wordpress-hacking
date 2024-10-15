<?php

require_once 'inc/code.php';
function get_all_actions()
{
    global $wp_filter;

    $ajax_actions = [];

//    $default_funcs = [
//        'wp_ajax_save', 'wp_ajax_widgets-order', 'wp_ajax_add-category', 'wp_ajax_add-post_tag',
//        'wp_ajax_add-nav_menu', 'wp_ajax_add-link_category', 'wp_ajax_add-post_format',
//        'wp_ajax_add-wp_theme', 'wp_ajax_add-wp_template_part_area', 'wp_ajax_add-wp_pattern_category', 'wp_ajax_save-widget'
//    ];

    // Loop through the $wp_filter to find all actions
    foreach ($wp_filter as $key => $value) {
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
        $url = add_query_arg('action', $action);
        $content .= "<li>{$action} → <a href='$url'>$function</a></li>";
    }

    if (empty($content)) {
        $content = "No functions defined";
    }

    $show = '';
    if ($i == 0 && !isset($_REQUEST['action'])) {
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

$actions = get_all_actions();

?>

    <h5 class="card-title">Functions</h5>
    <p>Show the currently defined functions created with `add_action`.</p>
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
