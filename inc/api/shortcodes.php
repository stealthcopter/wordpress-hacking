<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_shortcodes() {
    global $shortcode_tags;

    $all_shortcodes = get_shortcodes();

    // Attribute extraction: ?stealth_api=shortcodes&tag={tag}&attrs=1
    if (isset($_REQUEST['tag']) && isset($_REQUEST['attrs'])) {
        $tag = $_REQUEST['tag'];
        if (!array_key_exists($tag, $shortcode_tags)) {
            stealth_json(['error' => 'Shortcode not found'], 404);
        }
        $function_name = get_function_name($tag);
        $code_obj      = get_function_code($function_name);
        $attrs         = extract_shortcode_attributes($code_obj['code'] ?? '');
        stealth_json([
            'tag'        => $tag,
            'attributes' => $attrs['attributes'],
            'uses_shortcode_atts' => $attrs['used_shortcode_atts'],
        ]);
    }

    // Detail: ?stealth_api=shortcodes&tag={tag}
    if (isset($_REQUEST['tag'])) {
        $tag = $_REQUEST['tag'];
        if (!array_key_exists($tag, $shortcode_tags)) {
            stealth_json(['error' => 'Shortcode not found'], 404);
        }
        $function_name = get_function_name($tag);
        $code_obj      = get_function_code($function_name);
        $attrs         = extract_shortcode_attributes($code_obj['code'] ?? '');

        stealth_json([
            'tag'           => $tag,
            'function_name' => stealth_printable($function_name),
            'attributes'    => $attrs['attributes'],
            'uses_shortcode_atts' => $attrs['used_shortcode_atts'],
            'code'          => stealth_serialize_code($code_obj),
        ]);
    }

    // List request
    $items = [];
    foreach ($all_shortcodes as $tag => $entry) {
        $function_name = get_function_name($tag);
        $code_obj      = get_function_code($function_name);
        $attr_count    = count(extract_shortcode_attributes($code_obj['code'] ?? '')['attributes']);

        $items[$tag] = [
            'tag'           => $tag,
            'function_name' => stealth_printable($function_name),
            'slug'          => $entry['slug'],
            'item_type'     => $entry['item_type'],
            'attr_count'    => $attr_count,
        ];
    }

    stealth_json([
        'count' => count($items),
        'items' => $items,
    ]);
}

function stealth_api_shortcodes_execute() {
    if (empty($_POST['shortcode'])) {
        stealth_json(['error' => 'shortcode parameter required'], 400);
    }

    $raw = base64_decode($_POST['shortcode']);
    if ($raw === false) {
        stealth_json(['error' => 'shortcode must be base64-encoded'], 400);
    }

    try {
        ob_start();
        $result = do_shortcode($raw);
        $output = ob_get_clean() . $result;
        stealth_json([
            'shortcode' => $raw,
            'output'    => $output,
            'empty'     => ($output === ''),
        ]);
    } catch (Exception $e) {
        stealth_json(['error' => 'Exception during shortcode execution: ' . $e->getMessage()], 500);
    }
}
