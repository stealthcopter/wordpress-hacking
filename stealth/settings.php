<?php

// Now you can use WordPress functions
// For example:
// echo get_bloginfo('name');


function load_table($options){
    // Print each option and its value
    echo "<br>\n";
    echo "<table class='table'>";
    echo "<thead><tr><th>Name</th><th>Value</th></tr></thead>";
    echo "<tbody>";
    foreach ($options as $option_name => $option_value) {
        echo "<tr><td>".$option_name . '</td><td>' . htmlentities(print_r($option_value, true)) . "</td></tr>\n";
    }
    echo "</tbody>";
    echo "</table>";
}

// Get all option names
$options = wp_load_alloptions();
load_table($options);


// update_option("admin_email","test222@test.com");
//update_option("siteurl","http://localhost:8080");
