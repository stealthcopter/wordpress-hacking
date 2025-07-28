<?php
if (isset($_FILES['file'])) {
    echo "<div class='w-25'>";
    echo show_results('Pretend File Upload', $_FILES['file']);
    echo "</div>";
}
?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Upload a File</h5>
        <p>Sometimes it's useful to have an upload form so you don't have to create a request manually like a chump.</p>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fileToUpload">Choose file to upload:</label>
                <input type="file" name="file" id="file" required multiple>
                <br><br>
                <input class="btn btn-success" type="submit" value="Upload File" name="submit">
            </div>
        </form>
    </div>
</div>