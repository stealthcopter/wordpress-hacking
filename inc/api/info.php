<?php

if (!defined('ABSPATH')) {
    die('not like this...');
}

function stealth_api_highlighting() {
    $sinks_file   = STEALTH_PLUGIN_PATH . '/sinks.txt';
    $sources_file = STEALTH_PLUGIN_PATH . '/sources.txt';
    stealth_json([
        'sinks'   => file_exists($sinks_file)   ? array_values(file($sinks_file,   FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : [],
        'sources' => file_exists($sources_file) ? array_values(file($sources_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : [],
    ]);
}

function stealth_api_info() {
    global $wpdb;

    // Structured WordPress/environment summary
    $theme   = wp_get_theme();
    $summary = [
        'wp_version'       => get_bloginfo('version'),
        'theme_name'       => $theme->get('Name'),
        'theme_version'    => $theme->get('Version'),
        'theme_uri'        => $theme->get('ThemeURI'),
        'mysql_version'    => $wpdb->db_version(),
        'multisite'        => is_multisite(),
        'site_url'         => get_site_url(),
        'home_url'         => get_home_url(),
        'abspath'          => ABSPATH,
        'php_version'      => PHP_VERSION,
        'php_os'           => PHP_OS,
        'server_software'  => $_SERVER['SERVER_SOFTWARE'] ?? '',
    ];

    // Active plugins list
    $active_plugins = [];
    foreach (get_option('active_plugins', []) as $plugin) {
        $data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false, false);
        $active_plugins[] = [
            'file'       => $plugin,
            'name'       => $data['Name']       ?? $plugin,
            'version'    => $data['Version']    ?? '',
            'plugin_uri' => $data['PluginURI']  ?? '',
        ];
    }

    // phpinfo HTML (body only, to be displayed in an iframe srcdoc)
    ob_start();
    phpinfo();
    $phpinfo_raw = ob_get_clean();
    $phpinfo_html = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo_raw);

    // Syntax highlighting patterns for Prism (sinks / sources)
    $sinks_file   = STEALTH_PLUGIN_PATH . '/sinks.txt';
    $sources_file = STEALTH_PLUGIN_PATH . '/sources.txt';
    $sinks   = file_exists($sinks_file)   ? file($sinks_file,   FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $sources = file_exists($sources_file) ? file($sources_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    stealth_json([
        'summary'        => $summary,
        'active_plugins' => $active_plugins,
        'phpinfo_html'   => $phpinfo_html,
        'highlighting'   => [
            'sinks'   => array_values($sinks),
            'sources' => array_values($sources),
        ],
    ]);
}
