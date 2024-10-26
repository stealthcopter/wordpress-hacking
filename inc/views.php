<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'not like this...' );
}

function key_value_table($data, $mini=false, $align='align-middle'){
    $style = '';
    if ($mini) {
        $style = 'style="width: auto;"';
    }
    $content = "<table class='table table-striped $align' $style><tbody>";

    if (is_string($data)) {
        $data = preg_split('\n', $data);
    }

    foreach ($data as $key => $value) {
        if (is_array($value)){
            if (is_numeric($key)) {
                $content .= "<tr>";
            }
            else{
                $content .= "<tr><td><strong>" . htmlspecialchars($key) . "</strong></td>";
            }
            foreach ($value as $v){
                $content .= "<td>" . $v . "</td>";
            }
            $content .= "</tr>";
        }
        else{
            if (is_numeric($key)) {
                $content .= "<tr><td>" . htmlspecialchars($value) . "</td></tr>";
            }
            else{
                $content .= "<tr><td><strong>" . htmlspecialchars($key) . "</strong></td><td>" . $value . "</td></tr>";
            }
        }
    }

    $content .= "</tbody></table>";
    return $content;
}

function show_results($title, $data, $mini=false, $type='success')
{
    $content = key_value_table($data, $mini, 'text-start');
    $data = esc_attr(strip_tags(json_encode($data)));

    $type = $type == 'success' ? 'success' : 'danger';

    return "
    <div class='card mt-4 mb-4'>
        <div class='card-header bg-$type'>
            $title <button class='copy-btn copy-btn-data' title='Copy to clipboard' data-json='$data'>📋</button>
        </div>
        <div class='card-body'>
            $content
        </div>
    </div>";
}

function copyable($text){
    if (is_array($text)){
        $text = implode(', ', $text);
    }
    return "<span class='copy-text'>$text</span>";
}

function display_in_columns(...$data)
{
    $content = '';
    foreach ($data as $row) {
        $content .=
            "<div class='col'>
               $row
            </div>";
    }

    return
        "<div class='container text-center'>
            <div class='row align-items-start'>
            $content
            </div>
         </div>";
}

function get_color_for_role($role) {
    switch ($role) {
        case 'administrator':
            return 'danger'; // Red
        case 'editor':
            return 'warning'; // Yellowish
        case 'author':
            return 'secondary'; // Greyish
        case 'contributor':
            return 'primary'; // Blue
        case 'customer':
            return 'info'; // Light Blue
        case 'subscriber':
            return 'success'; // Green
        default:
            return 'light'; // Default Grey
    }
}

function get_show_defaults() {
    session_start(); // Start the session

    // Check if the GET param is set, else use the session value
    if (isset($_GET['show_defaults'])) {
        // Set the session based on GET param
        $_SESSION['show_defaults'] = ($_GET['show_defaults'] === 'true');
    } elseif (!isset($_SESSION['show_defaults'])) {
        // Default session value if neither GET param nor session is set
        $_SESSION['show_defaults'] = true;
    }

    // Return the current session value
    return $_SESSION['show_defaults'];
}

function show_defaults_toggle() {
    $show_defaults = $_SESSION['show_defaults'];
    // Set the checkbox state based on the passed boolean value
    $checked = $show_defaults ? 'checked' : '';
    $url = add_query_arg('show_defaults', !$show_defaults ? 'true' : 'false');
    $url = remove_query_arg('action', $url);
    // Prepare and return the HTML output
    return '
    <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" id="toggleDefaults" ' . $checked . '>
        <label class="form-check-label" for="toggleDefaults" title="Hide the defaults defined by WordPress to help reduce signal to noise.">Show Defaults</label>
    </div>
    <script>
        document.getElementById("toggleDefaults").addEventListener("change", function() {
            // Get the new state of the checkbox
            var showDefaults = this.checked ? "true" : "false";
            // Reload the page with the show_defaults GET parameter
            window.location.href = "'. $url . '";
        });
    </script>';
}