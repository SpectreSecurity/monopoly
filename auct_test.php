<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once ('core/core.php');
echo "1" . '<br>';
ConnectDB();

$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
echo "gsession_id = $gsession_id" . '<br>';
$gauction = new GAuction($gsession_id);
if ($gauction -> Load(720)) {
	echo "auct_id = " . $gauction -> GetAuctId() . '<br>';	
	echo "GetUserState= ".$gauction -> GetUserState(1). '<br>';
	echo "IsOpened= ".($gauction-> IsOpened()?1:0). '<br>';
	//if ($gauction -> GetUserState(1) == NULL) {
	if (($gauction-> IsOpened()) && ($gauction -> GetUserState(1) == NULL)) {

		echo 'link='.$gauction -> LinkUser(1). '<br>';
	}

	/*if ($gauction -> MakeDeltaBid(1, 3)) {
		echo "MakeBid done! bidder= " . GetUserName($gauction -> GetBidderUserId()) . ' bid=' . $gauction -> GetBid() . '<br>';
	} else {
		echo "GAuction MakeBid  failed" . '<br>';
	}
	*/
} else {
	echo "GAuction load failed" . '<br>';
}
?>