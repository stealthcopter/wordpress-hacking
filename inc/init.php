<?php
session_start(); // Start the session

global $DEFINED_SHORTCODES;
global $DEFINED_ACTIONS;
global $DEFINED_ROUTES;

$DEFINED_ACTIONS = get_all_actions();
$ACTION_COUNT = count($DEFINED_ACTIONS);

$DEFINED_SHORTCODES = get_shortcodes();
$SHORTCODE_COUNT = count($DEFINED_SHORTCODES);

$DEFINED_ROUTES = get_rest_routes();
$ROUTE_COUNT = count($DEFINED_ROUTES);
$DEFINED_NAMESPACES = get_namespaces();