<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

require_once __DIR__ . '/../installer.php';

$markdown = '';
$python = '';

if (isset($_REQUEST['type'])) {
    $type = $_REQUEST['type'];
}

if (isset($type)){
    $slug = $_REQUEST['slug'];
    $vuln = $_REQUEST['vulnerability_type'];

    if ($type == 'plugin'){
        $api = get_plugin_info($slug);
    }
    else{
        $api = get_theme_info($slug);
    }

    if ($_REQUEST['user_level'] == 'unauth'){
        $authed = 'Unauthenticated';
    }
    else{
        $user = $_REQUEST['user_level'];
        $authed = "Authenticated ($user)";
    }

    // TODO: User level

    $link = "https://wordpress.org/{$type}s/$slug";
    $version = $api->version;
    $name = $api->name;
    $installs = number_format($api->active_installs);
    $updated = $api->last_updated;

    $markdown .= "# Summary\n";
    $markdown .= "The $name ".ucfirst($type). " is vulnerable to a {$authed} {$vuln}...\n\n\n";

    $markdown .= "## Affected ".ucfirst($type). "\n\n";
    $markdown .= "Title: $name\n";
    $markdown .= "Active installations: $installs\n";
    $markdown .= "Version: $version\n";
    $markdown .= "Slug: $slug\n";
    $markdown .= "Link: $link\n\n";

    $markdown .= "## Root Cause\n\n\n";

    $markdown .= "## Proof of Concept\n\n";
    $markdown .= "1. Install and activate the {$type}\n"; // TODO: Deps
}

?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Proof of Concept Generator</h5> <!-- Not unlike the royal family... -->
        <form action="" method="post">
            <div class="form-group mb-3">
                <!-- Radio for type -->
                <label class="form-label">Type</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="type_plugin" value="plugin" <?php echo (isset($_POST['type']) && $_POST['type'] === 'plugin') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="type_plugin">Plugin</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="type_theme" value="theme" <?php echo (isset($_POST['type']) && $_POST['type'] === 'theme') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="type_theme">Theme</label>
                </div>
            </div>


            <div class="form-group mb-3">
                <!-- Dropdown for user level -->
                <label for="slug" class="form-label">Slug</label>
                <input id="slug" class="form-input" name="slug" value="<?php echo $slug ?? ''; ?>"/>
            </div>

            <div class="form-group mb-3">
                <!-- Dropdown for user level -->
                <label for="user_level" class="form-label">User Level</label>
                <select class="form-select" id="user_level" name="user_level">
                    <?php
                    $user_levels = [
                        "unauth" => "Unauthenticated",
                        "subscriber" => "Subscriber",
                        "contributor" => "Contributor",
                        "author" => "Author",
                        "editor" => "Editor",
                        "administrator" => "Administrator",
                        "shop_manager" => "WooCommerce Shop Manager",
                        "customer" => "WooCommerce Customer"
                    ];
                    foreach ($user_levels as $value => $label) {
                        $selected = (isset($_POST['user_level']) && $_POST['user_level'] === $value) ? 'selected' : '';
                        echo "<option value='$value' $selected>$label</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <!-- Text input for vulnerability type with autocomplete -->
                <label for="vulnerability_type" class="form-label">Vulnerability Type</label>
                <input type="text" class="form-control" id="vulnerability_type" name="vulnerability_type" placeholder="Enter vulnerability type" list="vulnerability_options" value="<?php echo isset($_POST['vulnerability_type']) ? htmlspecialchars($_POST['vulnerability_type']) : ''; ?>">
                <datalist id="vulnerability_options">
                    <option value="XSS">
                    <option value="SQL Injection">
                    <option value="CSRF">
                    <option value="Remote Code Execution">
                    <option value="Privilege Escalation">
                    <option value="Path Traversal">
                </datalist>
            </div>

            <div class="form-group">
                <input class="btn btn-success" type="submit" value="Create" name="submit">
            </div>
        </form>
    </div>
</div>



<hr class="bg-danger border-2 border-top">

<h5>Markdown PoC</h5>

<pre><code class='language-markdown' id="poc_markdown"><?php echo esc_html($markdown);?></code></pre>

<hr class="bg-danger border-2 border-top">

<h5>Python PoC</h5>

<pre><code class='language-python' id="poc_python"><?php echo esc_html($python);?></code></pre>


<script>
document.onreadystatechange = function () {
    if (document.readyState == "complete") {
        Prism.highlightAll();
    }
}
</script>