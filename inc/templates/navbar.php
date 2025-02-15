<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

?>

<nav class="navbar navbar-expand-lg bg-body-tertiary pb-0">
    <div class="container-fluid">
        <a class="navbar-brand" href="stealth.php">StealthTools</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-tabs" style="border-bottom: none;">
                <?php
                foreach (PAGES as $name => $desc) {
                    $active = ($page == $name) ? "active" : "";
                    $badge = '';
                    if ($name == 'funcy') {
                        $badge = ($ACTION_COUNT > 0) ? "<span class='badge rounded-pill bg-primary ms-1'>$ACTION_COUNT</span>" : "";
                    }
                    else if ($name == 'shorty') {
                        $badge = ($SHORTCODE_COUNT > 0) ? "<span class='badge rounded-pill bg-primary ms-1'>$SHORTCODE_COUNT</span>" : "";
                    }
                    else if ($name == 'resty') {
                        $badge = ($ROUTE_COUNT > 0) ? "<span class='badge rounded-pill bg-primary ms-1'>$ROUTE_COUNT</span>" : "";
                    }
                    echo "<li class='nav-item'>";
                    echo "<a class='nav-link $active' href='?stealth_page=$name' title='$desc'>$name$badge</a>";
                    echo "</li>";
                }
                ?>
            </ul>

            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    &#8942;
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?stealth_page=instally&install_and_activate=1&plugin=<?php echo esc_url(STEALTH_PLUGIN_ZIP_URL);?>">Update</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="show_info()">About</a></li>
                </ul>
            </div>

        </div>
    </div>
</nav>


<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabel">Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Hello 🥳 this is a collection of WordPress hacking tools that I have found useful for performing dynamic analysis and to help during exploit creation for the purposes of bug bounty. For instructions and more information please refer to <a class='' href='<?php echo STEALTH_URL;?>' target='_blank'>GitHub</a></p>

                <p>If you have a bug to report or an idea for a new feature please create an issue in GitHub <a href="https://github.com/stealthcopter/wordpress-hacking/issues">here</a>.</p>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function show_info(){
        const requestModal = new bootstrap.Modal(document.getElementById('infoModal'));
        requestModal.show();
    }
</script>