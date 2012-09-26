<?php
//require_once ('auth.php');
require_once ('core/core.php');
ConnectDB();
echo 'us_GetCurrentUserIdentity='. us_GetCurrentUserIdentity().'</br>';
echo 'us_GetCurrentUserId='.us_GetCurrentUserId().'</br>';
echo 'GetCurrentUserId='.GetCurrentUserId().'</br>';
//  header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
if (isset($_REQUEST[session_name()])) session_start();
if (isset($_COOKIE[session_name()])) session_start();

if (isset($_SESSION['user_identity']) AND $_SESSION['ip'] == $_SERVER['REMOTE_ADDR']) {
	$_GET['user_identity'] = $_SESSION['user_identity'];
	echo 'Auth user_identity='.$_SESSION['user_identity'].'</br>';
}

echo 'us_GetCurrentUserIdentity='. us_GetCurrentUserIdentity().'</br>';
echo 'us_GetCurrentUserId='.us_GetCurrentUserId().'</br>';
echo 'GetCurrentUserId='.GetCurrentUserId().'</br>';

//else {
//  session_destroy();
//  header("Location: http://".$_SERVER['HTTP_HOST'].'/gw');
//  die;
//}
?>