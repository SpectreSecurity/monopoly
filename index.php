<?php
require_once ('auth.php');
require_once ('core/microcore.php');
//require_once ('core/core.php');
//ConnectDB();

//$current_user_id = us_GetCurrentUserId();
$action = us_GetCurrentAction();
//echo 'us_GetCurrentAction='.us_GetCurrentAction().'</br>';

//echo 'us_GetCurrentUserIdentity='. us_GetCurrentUserIdentity().'</br>';
//echo 'us_GetCurrentUserId='.us_GetCurrentUserId().'</br>';
//echo '!GetCurrentUserId</br>';
//echo 'GetCurrentUserId='.GetCurrentUserId().'</br>';
//echo '!GetCurrentUserId</br>';

/**/
global $G_MODE;
switch ($action) {
	case 'gscreate' :
		include ('gscreate.php');
		break;
	case 'gsview' :
		$G_MODE = G_MODE_VIEW;
		include ('gsview.php');
		break;
	case 'gsjoin' :
		//$G_MODE = G_MODE_PLAY;
		//include ('main.php');
		//header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		//$gsession_id=us_GetCurrentGSessionId();
		include ('gsjoin.php');
		break;
	case 'gsplay' :
		$G_MODE = G_MODE_PLAY;
		include ('gsplay.php');
		break;
	default :
		include ('page_error.php');
}
/**/
?>
