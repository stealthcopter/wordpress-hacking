<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

/**
 * Sanitise a get_function_code() result for JSON output.
 * Strips non-serialisable Closure instances and appends code analysis.
 */
function stealth_serialize_code($code_obj) {
    if (!is_array($code_obj)) {
        return null;
    }
    $result = [
        'slug'          => $code_obj['slug']          ?? '',
        'item_type'     => $code_obj['item_type']     ?? '',
        'code'          => $code_obj['code']          ?? '',
        'file'          => $code_obj['file']          ?? '',
        'function_name' => $code_obj['function_name'] ?? '',
        'lines'         => $code_obj['lines']         ?? '',
        'parameters'    => $code_obj['parameters']    ?? [],
    ];
    if (!empty($result['code'])) {
        $result['analysis'] = code_analysis($result['code']);
    }
    return $result;
}

/**
 * Convert a value that may be a Closure to a printable string.
 */
function stealth_printable($value) {
    if ($value instanceof Closure) {
        return 'Closure';
    }
    return (string) $value;
}

/**
 * Output JSON and die.
 */
function stealth_json($data, $status = 200) {
    // Discard any stray output from admin includes (e.g. WP upgrader HTML)
    while (ob_get_level()) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    die();
}

/**
 * Main API dispatcher. Called from stealth.php when ?stealth_api= is present.
 */
function stealth_api_dispatch() {
    // Clean any buffered output (e.g. from wp-load.php notices)
    while (ob_get_level()) {
        ob_end_clean();
    }
    // Fresh buffer to capture stray HTML from admin includes
    ob_start();

    header('Content-Type: application/json');
    // Allow the frontend to send requests from the same origin
    header('X-Content-Type-Options: nosniff');

    $endpoint = isset($_REQUEST['stealth_api']) ? $_REQUEST['stealth_api'] : '';
    $method   = $_SERVER['REQUEST_METHOD'];

    switch ($endpoint) {
        case 'info':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/info.php';
            stealth_api_info();
            break;

        case 'counts':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/counts.php';
            stealth_api_counts();
            break;

        case 'actions':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/actions.php';
            stealth_api_actions();
            break;

        case 'shortcodes':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/shortcodes.php';
            stealth_api_shortcodes();
            break;

        case 'shortcodes_execute':
            if ($method !== 'POST') {
                stealth_json(['error' => 'POST required'], 405);
            }
            require_once STEALTH_PLUGIN_PATH . '/inc/api/shortcodes.php';
            stealth_api_shortcodes_execute();
            break;

        case 'routes':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/routes.php';
            stealth_api_routes();
            break;

        case 'nonce':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/nonce.php';
            stealth_api_nonce();
            break;

        case 'users':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/login.php';
            stealth_api_users();
            break;

        case 'login':
            if ($method !== 'POST') {
                stealth_json(['error' => 'POST required'], 405);
            }
            require_once STEALTH_PLUGIN_PATH . '/inc/api/login.php';
            stealth_api_login();
            break;

        case 'options':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/options.php';
            stealth_api_options();
            break;

        case 'gadgets_lfi':
            if ($method !== 'POST') {
                stealth_json(['error' => 'POST required'], 405);
            }
            require_once STEALTH_PLUGIN_PATH . '/inc/api/gadgets.php';
            stealth_api_gadgets_lfi();
            break;

        case 'install':
            if ($method !== 'POST') {
                stealth_json(['error' => 'POST required'], 405);
            }
            require_once STEALTH_PLUGIN_PATH . '/inc/api/install.php';
            stealth_api_install();
            break;

        case 'filters':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/filters.php';
            if ($method === 'POST') {
                stealth_api_filters_save();
            } else {
                stealth_api_filters_get();
            }
            break;

        case 'highlighting':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/info.php';
            stealth_api_highlighting();
            break;

        case 'gadgets_info':
            require_once STEALTH_PLUGIN_PATH . '/inc/api/gadgets.php';
            stealth_api_gadgets_info();
            break;

        case 'upload_echo':
            if ($method !== 'POST') {
                stealth_json(['error' => 'POST required'], 405);
            }
            require_once STEALTH_PLUGIN_PATH . '/inc/api/upload.php';
            stealth_api_upload_echo();
            break;

        default:
            stealth_json(['error' => 'Unknown endpoint: ' . htmlspecialchars($endpoint)], 404);
    }
}
