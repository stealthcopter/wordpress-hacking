<?php

function load_table($options){
    // Print each option and its value
    echo "<br>\n";
    echo '<input class="form-control mb-3" id="tableSearchInput" type="text" placeholder="Filter...">';
    echo "<table class='table table-striped' id='optionsTable'>";
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

?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the search input and the table
        const searchInput = document.getElementById('tableSearchInput');
        const table = document.getElementById('optionsTable').getElementsByTagName('tbody')[0];

        // Add event listener to search input
        searchInput.addEventListener('keyup', function() {
            const searchValue = searchInput.value.toLowerCase();
            const rows = table.getElementsByTagName('tr');

            // Loop through all table rows and hide those that don't match the search query
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                const optionName = cells[0].innerText.toLowerCase();
                const optionValue = cells[1].innerText.toLowerCase();

                // Check if either option name or value contains the search query
                if (optionName.includes(searchValue) || optionValue.includes(searchValue)) {
                    rows[i].style.display = '';  // Show row
                } else {
                    rows[i].style.display = 'none';  // Hide row
                }
            }
        });
    });
</script>
