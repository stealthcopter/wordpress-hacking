<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function load_admin(){
    include ABSPATH . '/wp-admin/includes/admin.php';
    define('WP_ADMIN', true);
}

load_admin();

function print_actions($i, $all_actions, $prefix)
{
    global $DEFAULT_ACTIONS, $PLUGIN_COLOR_MAP;
    $actions = [];

    $contains_selected = false;

    // Filter only specific actions matching the prefix given
    foreach ($all_actions as $hash => $entry) {
        $key = $entry['hook'];
        #foreach ($all_actions as $key => $value) {
        // Check if the action starts with the prefix
        if (strpos($key, $prefix) === 0) {
            // If the prefix does not contain '_nopriv', exclude actions that contain '_nopriv'
            if (strpos($prefix, '_nopriv') === false && strpos($key, '_nopriv') !== false) {
                continue; // Skip actions containing '_nopriv' if the prefix doesn't contain it
            }

            $actions[$hash] = $entry;

            if ($_REQUEST['action'] === $hash){
                $contains_selected = true;
            }
        }
    }

    $title = "$prefix (" . count($actions) . ")";

    $content = "";
    foreach ($actions as $hash => $entry) {
        $hook = $entry['hook'];
        $action = $entry['action'];

        $text_color = '';
        $li_title = '';

        foreach ($DEFAULT_ACTIONS as $name => $def_actions) {
            if (in_array($hook, $def_actions)) {
                $text_color = $PLUGIN_COLOR_MAP[$name] ?? 'text-default';
                $li_title = ucfirst($name) . ' Action';
                break;
            }
        }

        $url = add_query_arg('action', $hash);

        $content .= "<li class='${text_color}' title='${li_title}'>{$hook} → <a href='$url'>$action</a></li>";

    }

    if (empty($content)) {
        $content = "No functions defined";
    }

    $show = '';
    if ($contains_selected || (!isset($_REQUEST['action']) && count($actions) > 0)) {
        $show = 'show';
    }

    $auth_badge = "";
    if (in_array($prefix, ['init', 'admin_init']) ||
        strpos($prefix, 'wp_ajax_nopriv') !== false ||
        strpos($prefix, 'admin_post_nopriv') !== false) {
        $auth_badge = '<span class="badge bg-danger ms-2" title="Unauthenticated Access">Unauthenticated</span>';
    } else {
        $auth_badge = '<span class="badge bg-info ms-2" title="Authenticated Likely Subscriber/Customer+">Authenticated</span>';
    }


    ?>
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed bg-secondary text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="true"
                    aria-controls="collapse<?php echo $i; ?>">
                <?php echo $title . $auth_badge; ?>
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

?>

    <h5 class="card-title">Functions</h5>
    <p>Show the currently defined functions created with `add_action`.</p>
<?php echo show_defaults_toggle(); ?>
    <div class="accordion accordion-flush" id="accordionExample">
        <?php

        print_actions(0, $DEFINED_ACTIONS, 'init');
        print_actions(1, $DEFINED_ACTIONS, 'admin_init');
        print_actions(2, $DEFINED_ACTIONS, 'wp_ajax_nopriv_');
        print_actions(3, $DEFINED_ACTIONS, 'wp_ajax_');
        print_actions(4, $DEFINED_ACTIONS, 'admin_post_nopriv_');
        print_actions(5, $DEFINED_ACTIONS, 'admin_post');

        ?>
    </div>

<?php

if (isset($_REQUEST['action'])) {
    echo "<h3 class='mt-2'>Function Code</h3>";
    $entry = $DEFINED_ACTIONS[$_REQUEST['action']];

    $action = $entry['action'];
    $hook = $entry['hook'];

    $code_obj = get_function_code($action);
    $code_obj['action'] = $hook;

    if (strpos($hook, "wp_ajax") === 0){
        $action_part = str_replace('wp_ajax_nopriv_','', $hook);
        $action_part = str_replace('wp_ajax_','', $action_part);
        $url = "/wp-admin/admin-ajax.php?action=".$action_part;
        $code_obj['link'] = "<a href='$url' target='_blank'>$url</a>";
    }
    else if (strpos($hook, "admin_post") === 0){
        $action_part = str_replace('admin_post_nopriv_','', $hook);
        $action_part = str_replace('admin_post_','', $action_part);
        $url = "/wp-admin/admin-post.php?action=".$action_part;
        $code_obj['link'] = "<a href='$url' target='_blank'>$url</a>";
    }

    if ($code_obj){
        print_code($code_obj);
    }
    else{
        $msg = 'Could not find function through Reflection 🥲';
        echo $msg;
        echo "<script>showError('$msg')</script>";
    }
}
