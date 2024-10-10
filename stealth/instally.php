<?php

require_once 'inc/installer.php';

//$action = '';
if (isset($_REQUEST['plugin'])){
    $plugin = $_REQUEST['plugin'];
    $result = install_plugin_by_slug($plugin);
    if ($result && isset($_REQUEST['install_and_activate'])){
        echo "Activating...<br>";
        activate_plugin_by_slug($plugin);
    }
}
if (isset($_REQUEST['theme'])){
    $theme = $_REQUEST['theme'];
    if (isset($_REQUEST['install_and_activate'])){

    }
}

?>

<script>
    function openInNewTab(url){
        window.open(url, '_blank');
    }
    function openPluginUrl() {
        let plugin = document.getElementById("plugin").value
        if (plugin) {
            openInNewTab("https://wordpress.org/plugins/" + plugin);
        }
    }
    function openThemeUrl() {
        let theme = document.getElementById("theme").value
        if (theme){
            openInNewTab("https://wordpress.org/themes/" + theme);
        }
    }
</script>

<p>Install and grab information about plugins much fastly.</p>

<p>Install a plugin</p>
<form class="m-2" action="" method="post">
    <div class="form-group">
        <input id="plugin" type="text" value="" name="plugin" placeholder="Plugin Name">
        <input class="btn btn-success m-1" type="submit" value="Install" name="install">
        <input class="btn btn-success m-1" type="submit" value="Install + Activate" name="install_and_activate">
        <button class="btn btn-success m-1" onclick="openPluginUrl();event.preventDefault();">Open</button>
    </div>
</form>

<!--TODO: Themes not ready yet -->
<!--<p>Install a theme</p>-->
<!--<form class="m-2" action="" method="post">-->
<!--    <div class="form-group">-->
<!--        <input id="theme" type="text" value="" name="theme" placeholder="Theme Name">-->
<!--        <input class="btn btn-success m-1" type="submit" value="Install" name="install">-->
<!--        <input class="btn btn-success m-1" type="submit" value="Install + Activate" name="install_and_activate">-->
<!--        <button class="btn btn-success m-1" onclick="openThemeUrl();event.preventDefault();">Open</button>-->
<!--    </div>-->
<!--</form>-->
