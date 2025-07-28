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

if (isset($_REQUEST['action_name'])) {
    $action = $_REQUEST['action_name'];
    $nonce = wp_create_nonce($action);
    $data = [
        'User' => $current_user,
        'Action' => $action,
        'Nonce' => copyable(wp_create_nonce($action)),
    ];
    $card = show_results('Nonce Results', $data);
    echo "<div class='w-25'>$card</div>";
}

?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Nonce Generator</h5> <!-- Not unlike the royal family... -->
        <form action="" method="post">
            <div class="form-group">

                <p>For testing nonce-as-auth or for going nonce hunting. This will generate a nonce for a given action for the currently logged user, or unauthenticated if not logged in. You can change users <a href="?stealth_page=login">here</a>.</p>

                <div class="form-floating mb-3">
                    <input type="text" id="current_user" class="form-control" placeholder="User"
                           value="<?php echo $current_user; ?>" disabled>
                    <label for="current_user" class="text-<?php echo $color_class;?>">Current User</label>
                </div>

                <div class="form-floating mb-3">
                    <!-- Trolololol no xss for naughty bois -->
                    <input type="text" id="action_name" name="action_name" class="form-control"
                           value="<?= htmlspecialchars($action); ?>" placeholder="Action Name">
                    <label for="action_name">Action Name</label>
                </div>

                <input class="btn btn-success" type="submit" value="Create nonce" name="submit">
            </div>
        </form>
    </div>
</div>

