<?php
require_once ('core/core.php');
ConnectDB();

$user_id = GetCurrentUserId();
$field_id = us_GetCurrentFieldId();

$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
$bid=us_GetCurrentBid();

$gauction=& $gsession -> OpenFieldPublicAuction($user_id, $field_id, $bid);
if ($gauction!=NULL) {
		echo "GAuction opened id=" .$gauction -> GetAuctId(). '<br>';
} else {
	echo "GAuction open failed" . '<br>';
}
?>