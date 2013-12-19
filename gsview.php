<?php
require_once ('core/core.php');
ConnectDB();
//echo '0</br>';
$current_user_id = NULL;
$current_user_name = NULL;
//guest!
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
$current_chat_room = "gs_$gsession_id";

global $G_MODE;
$G_MODE = G_MODE_VIEW;

if ($gsession -> gstate == G_GS_GSTATE_CREATED) {
	//include ('page_wait.php'); not impemented
	DIE;
} else if ($gsession -> gstate == G_GS_GSTATE_STARTED) {
	include ('page_main.php');
} else {
	DIE ;
}
?>