<?php
require_once ('core/core.php');
ConnectDB();
	//echo '0</br>';
$user_id = GetCurrentUserId();
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
	//echo '0a</br>';
if ($gsession -> CanAssignUser($user_id)) {
	//echo '1</br>';
	if (!$gsession -> AssignUser($user_id)) {
		echo 'AssignUser fail' . '<br>';
	} else {
			//echo '2</br>';
	}
}

header("Location: http://" . $_SERVER['HTTP_HOST'] . "/mon/?action=gsplay&gs_id=$gsession_id");
?>