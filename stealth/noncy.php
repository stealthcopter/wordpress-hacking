<?php

$action = '';
if (isset($_REQUEST['n'])){
    $action = $_REQUEST['n'];
    echo "<p>User: ".wp_get_current_user()->user_login."<br>\n";
    echo "Action: ".htmlspecialchars($action)."<br>\n";
    echo "Nonce: ".wp_create_nonce($action)."</p>\n";
}

?>

<p>Generate a nonce for the current logged user</p>
<form action="" method="post">
    <div class="form-group">
        <!-- Trolololol no xss for naughty bois -->
        <input type="text" value="<?=htmlspecialchars($action);?>" name="n" placeholder="Action Name">
        <input class="btn btn-success" type="submit" value="Create nonce" name="submit">
    </div>
</form>

