<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

$action = '';
$color_class = '';
if (is_user_logged_in()) {
    $current_user = wp_get_current_user()->user_login;
    $uid = wp_get_current_user()->ID;
    $color_class = get_color_for_role(wp_get_current_user()->roles[0]);
} else {
    $current_user = 'Not Logged In';
    $uid = -1;
}

$cookie_strings = [];
foreach ($_COOKIE as $key => $value) {
    if ($key === 'stealth_filters') {
        continue;
    }
    $cookie_strings[] = $key . '=' . $value;
}

$cookie_str = implode(";\n", $cookie_strings);
$cookie_str_copyable64 = base64_encode(implode(";", $cookie_strings));

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

                <?php if ($uid > 0) { ?>

                <div class="code-wrapper position-relative">
                    <label>Cookies</label>

                    <?php
                        echo '<pre style="white-space: pre-wrap;"><code class="language-json">' . $cookie_str . '</code></pre>';
                    }
                    ?>
                </div>
                <script>
                    window.addEventListener('load', function () {
                        let copy_btn = document.querySelector('.copy-to-clipboard-button');
                        if (copy_btn) {
                            copy_btn.onclick = function () {
                                copyToClipboard(atob('<?php echo $cookie_str_copyable64;?>'), 'Cookies');
                            }
                        }
                    });
                </script>

                <?php
                $data[] = ['<b>id</b>', '<b>username</b>','<b>roles</b>', '<b>login as</b>'];

                foreach (get_users() as $user) {
                    $url = add_query_arg('login_as_uid', $user->ID);
                    $color_class = 'btn-'.get_color_for_role($user->roles[0]);
                    $data[] = [$user->ID, $user->user_login, implode(', ', $user->roles), "<a title='Login as $user->user_login' class='btn $color_class mt-2 col-4' href='$url'>$user->user_login</a>"];
                }

                // Add unauthenticated
                $color_class = get_color_for_role('Unauthenticated');
                $url = add_query_arg('login_as_uid', -1);
                $data[] = [-1, 'Unauthenticated', '', "<a title='Logout' class='btn $color_class mt-2 col-4' href='$url'>Logout</a>"];

                echo key_value_table($data, false, 'text-center');

                ?>

            </div>
    </div>
</div>

