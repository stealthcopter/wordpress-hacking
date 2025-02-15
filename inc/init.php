<?php
session_start(); // Start the session

global $DEFAULT_ACTIONS;
global $DEFAULT_SHORTCODES;
global $DEFAULT_ROUTES;

global $DEFINED_SHORTCODES;
global $DEFINED_ACTIONS;
global $DEFINED_ROUTES;

$show_defaults = get_show_defaults();

$DEFINED_ACTIONS = get_all_actions($show_defaults);
$ACTION_COUNT = count($DEFINED_ACTIONS);

$DEFINED_SHORTCODES = get_shortcodes($DEFAULT_SHORTCODES['default'], $show_defaults);
$SHORTCODE_COUNT = count($DEFINED_SHORTCODES);

$DEFINED_ROUTES = get_rest_routes($DEFAULT_ROUTES, $show_defaults);
$ROUTE_COUNT = count($DEFINED_ROUTES);
$DEFINED_NAMESPACES = get_namespaces();