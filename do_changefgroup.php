<?php
require_once ('core/core.php');
ConnectDB();

$user_id = GetCurrentUserId();
$fgroup_id = us_GetCurrentFGroupId();
$auction = us_GetCurrentAction();
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;

if ($auction=='fgroup_up') {
	if ($gsession ->UserUpgradeMonopoly($user_id, $fgroup_id) ) {
		echo "UpgradeMonopoly! ".'<br>';
	} else {
		echo "UpgradeMonopoly  failed" . '<br>';
	}
}

if ($auction=='fgroup_down') {
	if ($gsession ->UserDowngradeMonopoly($user_id, $fgroup_id) ) {
		echo "DowngradeMonopoly! ".'<br>';
	} else {
		echo "DowngradeMonopoly  failed" . '<br>';
	}
}

?>