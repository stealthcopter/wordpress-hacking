<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

// Base URL for frontend assets — works in both plugin-installed and standalone modes.
$frontend_url = plugins_url('frontend/', STEALTH_PLUGIN_FILE);
$site_url     = get_site_url();

?><!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stealth Tools</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- Prism CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.css" rel="stylesheet">

    <!-- Our CSS -->
    <link rel="stylesheet" href="<?php echo esc_url($frontend_url); ?>style.css">
</head>
<body class="bg-dark text-light">

<!-- Toast container -->
<div id="toast-container" class="toast-container"></div>

<!-- Navbar placeholder — filled by app.js -->
<nav id="navbar"></nav>

<!-- Page content — filled by app.js -->
<div class="container-fluid px-4" id="app">
    <div class="d-flex justify-content-center align-items-center" style="height:60vh">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
</div>

<!-- Pass PHP config to JS -->
<script>
    window.STEALTH_CONFIG = {
        siteUrl:     <?php echo json_encode($site_url); ?>,
        frontendUrl: <?php echo json_encode($frontend_url); ?>,
        pluginUrl:   <?php echo json_encode(plugins_url('', STEALTH_PLUGIN_FILE)); ?>,
        pages: <?php echo json_encode(PAGES); ?>,
        stealthUrl:  <?php echo json_encode(STEALTH_URL); ?>,
    };
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<!-- Prism JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-http.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markdown.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js"></script>

<!-- App entry point -->
<script type="module" src="<?php echo esc_url($frontend_url); ?>app.js"></script>

</body>
</html>
