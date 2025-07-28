<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

?>

</div>

<!-- Prism.js JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-http.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markdown.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js"></script>

<?php
include STEALTH_PLUGIN_PATH .'/inc/custom_highlighting.php';
?>

<style>
    .hidden-php {
        display: none;
        line-height: 0;
    }
</style>
<script>
Prism.hooks.add('before-highlight', function (env) {
    if (env.language === 'php') {
        env.code = env.code.trim();
        if (!env.code.startsWith('<'+'?php')) {
            env.code = '<'+'?php\n' + env.code;
        }
    }
});
Prism.hooks.add('after-highlight', function (env) {
    if (env.language === 'php') {
        console.log('trying to hide it...')
        // Hide the first "< ?php" in the rendered output
        env.element.innerHTML = env.element.innerHTML.replace(
            /(&lt;\?php)/,
            '<span class="hidden-php">$1</span>'
        );
    }
});
</script>

</body>
</html>
