<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

/**
 * Build a [{slug, item_type, count}] breakdown array from any items array.
 */
function stealth_count_breakdown($items) {
    $counts = [];
    foreach ($items as $item) {
        // Actions/shortcodes have slug/item_type at top level;
        // routes nest them inside callback_code.
        $slug = $item['slug']      ?? ($item['callback_code']['slug']      ?? 'default');
        $type = $item['item_type'] ?? ($item['callback_code']['item_type'] ?? 'default');
        $key  = $slug . '|' . $type;
        if (!isset($counts[$key])) {
            $counts[$key] = ['slug' => $slug, 'item_type' => $type, 'count' => 0];
        }
        $counts[$key]['count']++;
    }
    return array_values($counts);
}

/**
 * Lightweight navbar badge counts.
 * Returns action/shortcode/route counts with slug breakdown and current filter state.
 */
function stealth_api_counts() {
    $actions    = get_all_actions();
    $shortcodes = get_shortcodes();
    $routes     = get_rest_routes();
    $filters    = get_user_filters();
    $options    = count(wp_load_alloptions());

    stealth_json([
        'actions'              => count($actions),
        'actions_breakdown'    => stealth_count_breakdown($actions),
        'shortcodes'           => count($shortcodes),
        'shortcodes_breakdown' => stealth_count_breakdown($shortcodes),
        'routes'               => count($routes),
        'routes_breakdown'     => stealth_count_breakdown($routes),
        'options'              => $options,
        'filters'              => $filters,
    ]);
}
