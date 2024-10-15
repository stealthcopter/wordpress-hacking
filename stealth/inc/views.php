<?php

function key_value_table($data, $mini=false){
    $style = '';
    if ($mini) {
        $style = 'style="width: auto;"';
    }
    $content = "<table class='table table-striped text-start' $style><tbody>";

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

function as_markdown($data){

}
function show_results($title, $data, $mini=false)
{
    $content = key_value_table($data, $mini);
    $data = esc_attr(strip_tags(json_encode($data)));

    return "
    <div class='card mt-4 mb-4'>
        <div class='card-header bg-success'>
            $title <button class='copy-btn copy-btn-data' title='Copy to clipboard' data-json='$data'>📋</button>
        </div>
        <div class='card-body'>
            $content
        </div>
    </div>";
}

function copyable($text){
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