<?php

function get_plugin_slugs()
{
    $plugins = array();
    $active_plugins = get_option('active_plugins', array());
    foreach ($active_plugins as $plugin) {
        $plugin_parts = explode('/', $plugin);
        if ($plugin_parts[0] === 'stealth') {  // Skip our plugin
            continue;
        }
        $plugins[] = $plugin_parts[0];
    }
    return $plugins;
}

function get_filters()
{
    $result = array('default', 'theme');
    $result = array_merge($result, get_plugin_slugs());
    return $result;
}

function get_user_filters(){
    $filters = get_filters();

    try {
        $decoded = json_decode(wp_unslash($_COOKIE['stealth_filters']) ?? '', true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new JsonException('Invalid JSON structure');
        }
        $stealth_filters = array_intersect_key(array_map('boolval', $decoded), array_flip(get_filters()));
    } catch (JsonException $e) {
        $stealth_filters = array_fill_keys(get_filters(), true);
    }

    foreach ($filters as $filter) {
        if (!array_key_exists($filter, $stealth_filters)) {
            $stealth_filters[$filter] = true;
        }
    }

    foreach (array_keys($stealth_filters) as $key) {
        if (!in_array($key, $filters, true)) {
            unset($stealth_filters[$key]);
        }
    }

    setcookie('stealth_filters', json_encode($stealth_filters, JSON_THROW_ON_ERROR), [
        'path' => '/',
        'httponly' => false,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax',
    ]);

    return $stealth_filters;
}

function is_filter_selected($user_filters, $slug, $type){
    return ($user_filters[$slug] || ($type == 'theme' and $user_filters['theme']));
}

