<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

// Define sets of default WordPress things that we may want to ignore so we can focus on the non-built-in functionality.

global $PLUGIN_COLOR_MAP;
$PLUGIN_COLOR_MAP = [
    'default' => 'text-secondary',
    'woocommerce' => 'text-purple',
    'elementor' => 'text-yellow',
];

global $badge_colors;
$badge_colors = [
    'theme' => 'info',
    'plugin' => 'primary',
    'default' => 'secondary',
    'woocommerce' => 'purple',
    'elementor' => 'yellow',
];


function get_item_badge($item_type, $slug, $title){
    $color = get_item_color($item_type, $slug);
    if ($item_type == 'default'){
        $badge_title = 'Built-in WordPress Function';
    }
    else{
        $badge_title = $title;
    }
    return "<span class='badge badge-item bg-$color me-2' style='cursor: pointer;' title='$badge_title'>$slug</span>";
}
function get_item_color($item_type, $slug){
    global $badge_colors;

    $random_colors = ['blue', 'red', 'teal', 'orange', 'green', 'cyan', 'pink', 'lime', 'indigo', 'rose', 'amber', 'emerald', 'sky'];

    if ($item_type == 'plugin'){
        $color = $badge_colors[$slug];
        if (!isset($color)){
            // Determine a randomish color based on the slug
            $hash = crc32($slug); // Generate a hash based on the slug
            $index = $hash % count($random_colors); // Ensure the index is within the bounds of $random_colors
            $color = $random_colors[$index]; // Select a color from the $random_colors array
            $badge_colors[$slug] = $color;
        }
    }
    else{
        $color = $badge_colors[$item_type];
    }

    return $color;
}