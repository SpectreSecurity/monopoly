<?php
require_once ('core/core.php');
ConnectDB();
	//echo '0</br>';
$current_user_id = GetCurrentUserId();
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
$current_user_name = GetUserName($current_user_id);


if ($gsession -> CanPlayUser($current_user_id)) {
	if ($gsession -> gstate == G_GS_GSTATE_CREATED) {
		include ('page_wait.php');
	} else if ($gsession -> gstate == G_GS_GSTATE_STARTED) {
		include ('page_main.php');
	} else {
		DIE;
	}

} ELSE {
//		include ('page_main.php');
}

?>