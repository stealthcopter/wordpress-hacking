<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

$action = '';
$color_class = '';
if (is_user_logged_in()) {
    $current_user = wp_get_current_user()->user_login;
    $color_class = get_color_for_role(wp_get_current_user()->roles[0]);
} else {
    $current_user = 'Not Logged In';
}
?>


<div class="card">
    <div class="card-body">
        <h5 class="card-title">Auto Login</h5>

            <div class="form-group">

                <p></p>

                <div class="form-floating mb-3">
                    <input type="text" id="current_user" class="form-control" placeholder="User"
                           value="<?php echo $current_user; ?>" disabled>
                    <label for="current_user" class="text-<?php echo $color_class;?>">Current User</label>
                </div>

                <?php
                $data[] = ['<b>id</b>', '<b>username</b>','<b>roles</b>', '<b>login as</b>'];

                foreach (get_users() as $user) {
                    $url = add_query_arg('login_as_uid', $user->ID);
                    $color_class = 'btn-'.get_color_for_role($user->roles[0]);
                    $data[] = [$user->ID, $user->user_login, implode(', ', $user->roles), "<a class='btn $color_class mt-2 col-4' href='$url'>Login as $user->user_login</a>"];
                }

                echo key_value_table($data, false, 'text-center');

                ?>

            </div>
    </div>
</div>

