<?php
require_once('../../includes/initialize.php');
if (!$session->is_logged_in()) { redirect_to("login.php"); }
?>

<?php
$user = new User();
$user->id = 5;
$user->last_name = "Ura";
$user->save();

/*$user = User::find_by_id(3);
$user->first_name = "Jony D";
$user->save();*/

/*$user = User::find_by_id(3);
$user->delete();*/



?>
<?php include_layout_template('admin_footer.php'); ?>
		