<?php

function key_value_table($data, $mini){
    $style = '';
    if ($mini) {
        $style = 'style="width: auto;"';
    }
    $content = "<table class='table table-striped text-start' $style><tbody>";

    if (is_string($data)) {
        $data = preg_split('\n', $data);
    }

    foreach ($data as $key => $value) {
        if (is_numeric($key)) {
            $content .= "<tr><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        else{
            $content .= "<tr><td><strong>" . htmlspecialchars($key) . "</strong></td><td>" . $value . "</td></tr>";
        }
    }

    $content .= "</tbody></table>";
    return $content;
}
function show_results($title, $data, $mini=false)
{
    $content = key_value_table($data, $mini);

    return "
    <div class='card mt-4 mb-4'>
        <div class='card-header bg-success'>
            $title
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
            <div class='row align-items-center'>
            $content
            </div>
         </div>";
}