<?php
require_once ('core/core.php');

ConnectDB();
//WatchDog();
$current_user_id = GetCurrentUserId();
$current_user_name = GetUserName($current_user_id);
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
$G_MODE = us_GetCurrentGMode();
$dblasttime = us_GetCurrentLastUpdated();
$dbstarttime = DbTimeStamp();


if (!$current_user_id) {
	if ($G_MODE == G_MODE_VIEW) {
		$current_user_id = 0;
	} else {
		die ;
	}
};

require_once ('tpl/board_tpl.php');
//-----------------------------------------------------------------------------------------------
// Start
//-----------------------------------------------------------------------------------------------
header('Content-Type: text/javascript; charset=utf8');
//header('Access-Control-Allow-Origin: http://www.example.com/');
//header('Access-Control-Max-Age: 3628800');
//header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
$arr = array();
$arr["gs_id"] = $gsession_id;
$arr["gstatus"] = $gsession -> gstatus;

if ($gsession -> IsStarted()) {
	$arr["refreshtimeout"] = 3000;
} else {
	$arr["refreshtimeout"] = 30000;
}
if ($G_MODE == G_MODE_PLAY) {
	$dtimeout = $gsession -> GetDebitorTimeout($current_user_id);
	if ($dtimeout > 0) {
		$diceinfo_tpl = "Left $dtimeout sec";
		$arr["dice"] = $diceinfo_tpl;
	}
}
$log_arr = $gsession -> GetLastLogArray(30, $current_user_id, $g_log_tpl, $dblasttime);
$arr = array_merge($arr, $log_arr);

$msg_arr = $gsession -> GetLastMsgArray(30, $current_user_id, $g_msg_tpl, $dblasttime);
$arr = array_merge($arr, $msg_arr);

if ($gsession -> HasChanges($dblasttime)) {
	$arr["gstate"] = $gsession -> gstate;
	$arr["gturn"] = $gsession -> GetGTurn();

	if ($G_MODE == G_MODE_PLAY) {
		$arr["usermonopoly"] = str_replace('%userprop%', $gsession -> GetUserMonopolyList($current_user_id, $g_userprop_tpl), $g_usermonopoly_tpl);

		$arr["userinfo"] = str_replace('%userprop%', $gsession -> GetUserProperty($current_user_id), $g_userinfo_tpl);
	}
	$userlist_rows = $gsession -> GetUserList($g_userlist_row_tpl);
	$userlist = str_replace('%ROWS%', $userlist_rows, $g_userlist_tpl);
	$arr["userlist"] = $userlist;
	if ($G_MODE == G_MODE_PLAY) {
		$arr["dice"] = $gsession -> GetLastUserDiceInfo($current_user_id, $g_diceinfo_tpl);
	}

	if ($G_MODE == G_MODE_PLAY) {
		$ceil_tpl = $g_ceil_tpl;
	} else {
		$ceil_tpl = $g_ceil_tpl_readonly;
	}
	/* *
	 for ($i = 1; $i <= $gsession -> GetMapFieldCount(); $i++) {
	 $field_id = GetFieldId_by_fcode($gsession -> map_id, $i);
	 $tpl = str_replace('%i%', $i, $ceil_tpl);
	 $tpl = $gsession -> GetFieldInfo($field_id, $tpl);
	 $tpl_ulist = $gsession -> GetFieldUserInfo($field_id, $ceil_user_tpl);
	 if ($gsession -> CanSellField($current_user_id,$field_id)) {
	 $issellable='';
	 } else {
	 $issellable='hidden';
	 }
	 $ceil = str_replace('%ISSELLABLE%', $issellable, str_replace('%USERLIST%', $tpl_ulist, $tpl));
	 $arr["c$i"] = $ceil;
	 //	echo $ceil;
	 }/**/
	$ceil_arr = $gsession -> GetChangedFieldListArray($current_user_id, $ceil_tpl, $g_ceil_user_tpl, $dblasttime);
	$arr = array_merge($arr, $ceil_arr);

	$auct_arr = $gsession -> GetChangedAuctionListArray($current_user_id, $g_auct_lot_tpl, $dblasttime, 'auctlot_%AUCT_ID%', NULL, G_AU_AUCT_STATUS_ACTIVE);
	$arr = array_merge($arr, $auct_arr);
	if ($dblasttime != NULL) {
		$auct_arr = $gsession -> GetChangedAuctionListArray($current_user_id, NULL, $dblasttime, 'auctlot_%AUCT_ID%', NULL, G_AU_AUCT_STATUS_INACTIVE);
		$arr = array_merge($arr, $auct_arr);
	}

	$auct_arr = $gsession -> GetChangedAuctionsLotListArray($current_user_id, $g_auct_lot_subrow_tpl, $dblasttime, 'pnl_auct%AUCT_ID%', NULL, G_AU_AUCT_STATUS_ACTIVE);
	$arr = array_merge($arr, $auct_arr);

	$deal_arr = $gsession -> GetChangedDealListArray($current_user_id, $g_deal_tpl, $dblasttime, 'deallot_%DEAL_ID%', NULL, G_AU_AUCT_STATUS_ACTIVE, '%DEALITEMS_GIVE%', $g_deal_lot_give_tpl, '%DEALITEMS_RECEIVE%', $g_deal_lot_receive_tpl, true, ',');
	$arr = array_merge($arr, $deal_arr);

	if ($dblasttime != NULL) {
		$deal_arr = $gsession -> GetChangedDealListArray($current_user_id, NULL, $dblasttime, 'deallot_%DEAL_ID%', NULL, G_DL_DEAL_STATUS_INACTIVE);
		$arr = array_merge($arr, $deal_arr);
	}

	if ($G_MODE == G_MODE_PLAY) {
		$arr["property_set_owner"] = $gsession -> GetUserPropertyList($current_user_id, $g_proplist_tpl);

		$items_arr = $gsession -> GetChangedOponentPropertyListArray($current_user_id, $g_proplist_tpl, $dblasttime, 'property_set_user_id%USER_ID%');
		$arr = array_merge($arr, $items_arr);
	}
}
$arr["lastupdated"] = $dbstarttime;
//$json = '(' . json_encode($arr) . ');';
$json = json_encode($arr);
//must wrap in parens and end with semicolon
print($json);
//callback is prepended for json-p
//print_r($arr);
?>

