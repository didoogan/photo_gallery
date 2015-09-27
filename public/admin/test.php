<?php
require_once('../../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }
include_layout_template('admin_header.php');
?>

<?php include_layout_template('admin_header.php'); ?>
<h2>Photo Upload</h2>

<form action="photo_upload.php" enctype="multipart/form-data"
    method="POST">

</form>

<?php include_layout_template('admin_footer.php'); ?>

		