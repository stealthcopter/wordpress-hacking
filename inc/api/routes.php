<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_routes() {
    $all_routes = get_rest_routes();
    $namespaces = get_namespaces();

    // Detail: ?stealth_api=routes&id={numeric_index}
    if (isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        if (!isset($all_routes[$id])) {
            stealth_json(['error' => 'Route not found'], 404);
        }

        $route = $all_routes[$id];

        $no_auth = in_array($route['permission_callback'], ['none', '__return_true'], true);

        $result = [
            'id'                  => $id,
            'namespace'           => $route['namespace'],
            'route'               => $route['route'],
            'method'              => $route['method'],
            'callback'            => stealth_printable($route['callback']),
            'permission_callback' => stealth_printable($route['permission_callback']),
            'no_auth'             => $no_auth,
            'parameters'          => $route['callback_code']['parameters'] ?? [],
            'slug'                => $route['callback_code']['slug']       ?? '',
            'item_type'           => $route['callback_code']['item_type']  ?? '',
            'callback_code'       => stealth_serialize_code($route['callback_code']),
            'permission_code'     => stealth_serialize_code($route['permission_callback_code']),
        ];

        stealth_json($result);
    }

    // List request – omit code blobs to keep response lean
    $items = [];
    foreach ($all_routes as $idx => $route) {
        $no_auth = in_array($route['permission_callback'], ['none', '__return_true'], true);

        $items[] = [
            'id'                  => $idx,
            'namespace'           => $route['namespace'],
            'route'               => $route['route'],
            'method'              => $route['method'],
            'callback'            => stealth_printable($route['callback']),
            'permission_callback' => stealth_printable($route['permission_callback']),
            'no_auth'             => $no_auth,
            'parameters'          => $route['callback_code']['parameters'] ?? [],
            'slug'                => $route['callback_code']['slug']       ?? '',
            'item_type'           => $route['callback_code']['item_type']  ?? '',
        ];
    }

    stealth_json([
        'namespaces' => $namespaces,
        'count'      => count($items),
        'items'      => $items,
    ]);
}
