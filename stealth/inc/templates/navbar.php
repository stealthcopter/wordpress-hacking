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
                    echo "<li class='nav-item'>";
                    echo "<a class='nav-link $active' href='?stealth_page=$name' title='$desc'>$name</a>";
                    echo "</li>";
                }
                ?>
                <!--                <li class="nav-item dropdown">-->
                <!--                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">-->
                <!--                        Dropdown-->
                <!--                    </a>-->
                <!--                    <ul class="dropdown-menu">-->
                <!--                        <li><a class="dropdown-item" href="#">Action</a></li>-->
                <!--                        <li><a class="dropdown-item" href="#">Another action</a></li>-->
                <!--                        <li><hr class="dropdown-divider"></li>-->
                <!--                        <li><a class="dropdown-item" href="#">Something else here</a></li>-->
                <!--                    </ul>-->
                <!--                </li>-->
            </ul>
            <a class='btn btn-outline-success' href='<?php echo STEALTH_URL;?>' target='_blank'>i</a>
        </div>
    </div>
</nav>