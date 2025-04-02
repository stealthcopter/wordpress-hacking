<?php

function get_plugin_slugs()
{
    $plugins = array();
    $active_plugins = get_option('active_plugins', array());
    foreach ($active_plugins as $plugin) {
        $plugin_parts = explode('/', $plugin);
        if ($plugin_parts[0] === 'stealth') {  // Skip our plugin
            continue;
        }
        $plugins[] = $plugin_parts[0];
    }
    return $plugins;
}

function get_filters()
{
    $result = array('default', 'theme');
    $result = array_merge($result, get_plugin_slugs());
    return $result;
}

function get_user_filters(){
    $filters = get_filters();
    if (isset($_COOKIE['stealth_filters'])) {
        $stealth_filters = explode(',', $_COOKIE['stealth_filters']);
        // TODO: Remove uninstalled shiz.
    }
    else{
        $stealth_filters = array_diff($filters, array('default'));
        $_COOKIE['stealth_filters'] = join(',', $stealth_filters);
    }
    return $stealth_filters;
}

function draw_filters()
{
    // TODO: Color filters
    $filters = get_filters();
    $user_filters = get_user_filters();

    echo '<form class="card p-3"><h5 class="card-title">Filters</h5><div class="form-group">';
    foreach ($filters as $filter) {
        $col = get_item_color('plugin', $filter);
        $checked = in_array($filter, $user_filters) ? 'checked' : '';
        echo "<div class='form-check form-check-inline form-switch'>";
        echo "<input class='form-check-input form-check-input-$col filter_checkbox' type='checkbox' role='switch' id='filter_check_$filter' value='$filter' $checked>";
        echo "<label class='form-check-label' for='filter_check_$filter'>$filter</label>";
        echo '</div>';
    }
    echo '</div></form>';
}

?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const checkboxes = document.querySelectorAll(".filter_checkbox");
        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                const checkedFilters = Array.from(checkboxes)
                    .filter((cb) => cb.checked)
                    .map((cb) => cb.value);
                console.log('Saving filters: ' + checkedFilters.join(','));
                // Save checked filters to a cookie
                document.cookie = `stealth_filters=${checkedFilters.join(',')}; path=/;`;
            });
        });
    });
</script>