<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_install() {
    require_once STEALTH_PLUGIN_PATH . '/inc/installer.php';

    $type     = isset($_POST['type'])     ? $_POST['type']     : '';
    $slug     = isset($_POST['slug'])     ? trim($_POST['slug']) : '';
    $activate = isset($_POST['activate']) && $_POST['activate'];

    if (empty($slug)) {
        stealth_json(['error' => 'slug parameter required'], 400);
    }
    if (!in_array($type, ['plugin', 'theme'], true)) {
        stealth_json(['error' => 'type must be "plugin" or "theme"'], 400);
    }

    $steps = [];

    if ($type === 'plugin') {
        $result = install_plugin_by_slug($slug);
        $steps[] = ['label' => 'Install', 'success' => $result['success'], 'message' => $result['output']];

        $info = null;
        if (isset($result['plugin'])) {
            $api = $result['plugin'];
            $info = [
                'name'    => $api->name            ?? '',
                'slug'    => $api->slug            ?? $slug,
                'version' => $api->version         ?? '',
                'installs'=> $api->active_installs ?? 0,
                'updated' => $api->last_updated    ?? '',
                'url'     => 'https://wordpress.org/plugins/' . ($api->slug ?? $slug),
                'icon'    => $api->icons['1x']     ?? ($api->icons['default'] ?? ''),
            ];
        }

        if ($result['success'] && $activate) {
            $act = activate_plugin_by_slug($slug);
            $steps[] = ['label' => 'Activate', 'success' => $act['success'], 'message' => $act['output']];
        }
    } else {
        $result = install_theme_by_slug($slug);
        $steps[] = ['label' => 'Install', 'success' => $result['success'], 'message' => $result['output']];

        $info = null;
        if (isset($result['theme'])) {
            $api = $result['theme'];
            $info = [
                'name'    => $api->name            ?? '',
                'slug'    => $api->slug            ?? $slug,
                'version' => $api->version         ?? '',
                'updated' => $api->last_updated    ?? '',
                'url'     => 'https://wordpress.org/themes/' . ($api->slug ?? $slug),
            ];
        }

        if ($result['success'] && $activate) {
            $act = activate_theme_by_slug($slug);
            $steps[] = ['label' => 'Activate', 'success' => $act['success'], 'message' => $act['output']];
        }
    }

    $overall_success = $result['success'] ?? false;

    stealth_json([
        'success' => $overall_success,
        'type'    => $type,
        'slug'    => $slug,
        'steps'   => $steps,
        'info'    => $info ?? null,
    ]);
}
