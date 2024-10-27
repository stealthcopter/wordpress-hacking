<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function read_patterns($filename) {
    // Read the file into an array, one line per pattern
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $lines;
}

// TODO: Link to wpctf.org sources/sinks

$sinks = read_patterns(STEALTH_PLUGIN_PATH.'/sinks.txt');
$source_funcs = read_patterns(STEALTH_PLUGIN_PATH.'/sources.txt');

// Filter the items that start with a '$' and move them to $source_vars
$source_vars = array_values(array_filter($source_funcs, function($item) {
    return strpos($item, '\$') === 0;
}));

// Remove those items from $source_funcs that were moved to $source_vars
$source_funcs = array_values(array_filter($source_funcs, function($item) {
    return strpos($item, '\$') !== 0;
}));

?>

<style>
    .token.function-sink {
        background: #f12c3d; /* or any color you prefer */
        font-weight: bold;
        padding: 1px;
        border-radius: 2px;
    }
    .token.function-source {
        background: #f8ee28; /* or any color you prefer */
        color: #0c0c0c;
        padding: 1px;
        border-radius: 2px;
        font-weight: bold;
    }
</style>
<script>
    // Insert the patterns into Prism
    Prism.languages.insertBefore('php', 'function', {
        'function-sink': {
            pattern: /(<?php echo implode('|', $sinks);?>)/,
            alias: 'function-sink'
        },
        'function-source': {
            pattern: /(<?php echo implode('|', $source_funcs);?>)/,
            alias: 'function-source'
        },
    });
    Prism.languages.insertBefore('php', 'variable', {
        'function-source': {
            pattern: /(<?php echo implode('|', $source_vars);?>)/,
            alias: 'function-source'
        }
    });
</script>
