<?php

$api = $_REQUEST['api'];

if ($api === 'do_shortcode'){
    echo do_shortcode($_REQUEST['shortcode']);
}