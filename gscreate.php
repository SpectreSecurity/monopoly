<?php
require_once ('core/core.php');
ConnectDB();

$user_id = GetCurrentUserId();

$gsession = new GSession();
if (!$gsession -> Create(2, $user_id)) {
	echo 'Create fail' . '<br>';
	exit;
}
$gsession_id = $gsession -> gsession_id;
$gsession -> AssignUser($user_id);

header("Location: http://" . $_SERVER['HTTP_HOST'] . "/mon/?action=gsplay&gs_id=$gsession_id");
?>