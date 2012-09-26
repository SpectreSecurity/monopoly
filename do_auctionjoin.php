<?php
require_once ('core/core.php');
ConnectDB();

$user_id = GetCurrentUserId();
$auct_id = us_GetCurrentGAuctionId();

$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;

$gauction = & $gsession -> getGAuction($auct_id);
if ($gauction !=NULL) {

	if ($gauction -> UserJoin($user_id)) {
		echo "GAuction $auct_id join user $user_id done! " . '<br>';
	} else {
		echo "GAuction $auct_id join user $user_id failed" . '<br>';
	}
} else {
	echo "GAuction $auct_id load failed" . '<br>';
}
?>