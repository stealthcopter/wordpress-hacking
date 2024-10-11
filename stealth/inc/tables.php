<?php

function key_value_table($data, $mini){
    $style = '';
    if ($mini) {
        $style = 'style="width: auto;"';
    }
    $content = "<table class='table table-striped' $style><tbody>";
    foreach ($data as $key => $value) {
        $content .= "<tr><td><strong>" . htmlspecialchars($key) . "</strong></td><td>" . $value . "</td></tr>";
    }
    $content .= "</tbody></table>";
    return $content;
}
function show_results($title, $data)
{
    $content = key_value_table($data, true);

    echo "
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