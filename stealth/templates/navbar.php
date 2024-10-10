<?php
$pages = [
    "info" => "?stealth_page=info.php",
    "gadgets" => "?stealth_page=gadgets.php",
    "instally" => "?stealth_page=instally.php",
    "shorty" => "?stealth_page=shorty.php",
    "noncy" => "?stealth_page=noncy.php",
    "funcy" => "?stealth_page=funcy.php",
    "upload" => "?stealth_page=upload.php",
    "settings" => "?stealth_page=settings.php",
];
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="stealth.php">StealthTools</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php
                foreach ($pages as $name => $file) {
                    echo "<li class='nav-item'>";
                    echo "<a class='nav-link' href='$file' >$name</a>";
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
            <a class='btn btn-outline-success' href='https://github.com/stealthcopter/wordpress-hacking/stealth' target='_blank'>i</a>
        </div>
    </div>
</nav>