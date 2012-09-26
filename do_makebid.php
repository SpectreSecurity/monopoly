<?php
require_once ('core/core.php');
ConnectDB();

$user_id = GetCurrentUserId();
$auct_id = us_GetCurrentGAuctionId();
$bid = us_GetCurrentBid();

$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
/*
if ($gsession ->GetUserFund($user_id)>=$bid ) {

$gauction = new GAuction($gsession_id);
if ($gauction -> Load($auct_id)) {
	//echo "auct_id = " . $gauction -> GetAuctId() . '<br>';
	if ($gauction -> UserMakeDeltaBid($user_id, $bid)) {
		echo "MakeBid done! bidder= " . GetUserName($gauction -> GetBidderUserId()) . ' bid=' . $gauction -> GetBid() . '<br>';
	} else {
		echo "GAuction MakeBid  failed" . '<br>';
	}
} else {
	echo "GAuction load failed" . '<br>';
}
}
*/

	if ($gsession -> AuctionUserMakeDeltaBid($auct_id, $user_id, $bid)) {
		echo "MakeBid done! ".'<br>';
	} else {
		echo "GAuction MakeBid  failed" . '<br>';
	}

?>