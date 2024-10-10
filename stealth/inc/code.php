<?php

function print_code($code_obj) {
    if (is_array($code_obj)) {
        $file = $code_obj['file'];
        $php_code = $code_obj['code'];
        echo "Filename: $file <br>";
    }
    else{
        $php_code = $code_obj;
    }
    echo "<pre><code class='language-php'>" . htmlspecialchars($php_code) . "</code></pre>";
}