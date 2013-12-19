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
$log_tpl = "<div id='log_id%LOG_ID%' class='msgln'>%DATESTAMP%-%NAME%-%ACTION_DESC%</div>";
//$actlog=$gsession -> GetLastActions(30, $current_user_id, $log_tpl, $dblasttime);
//if ($actlog!=NULL) {
//$arr["actlog"] = "Last actions</br>" . $actlog;
//}
$log_arr = $gsession -> GetLastLogArray(30, $current_user_id, $log_tpl, $dblasttime);
$arr = array_merge($arr, $log_arr);

$msg_tpl = "<div id='msg_id%MSG_ID%' class='msgln'>%DATESTAMP%-%MSG_TEXT%</div>";
//$gmsgbox=$gsession -> GetLastMessages(30, $current_user_id, $msg_tpl, $dblasttime);
//if ($gmsgbox!=NULL) {
//$arr["gmsgbox"] = $gmsgbox;
//}
$msg_arr = $gsession -> GetLastMsgArray(30, $current_user_id, $msg_tpl, $dblasttime);
$arr = array_merge($arr, $msg_arr);

if ($gsession -> HasChanges($dblasttime)) {
	$arr["gstate"] = $gsession -> gstate;
	$arr["gturn"] = $gsession -> GetGTurn();

	//$userinfo = "User:<b>" . GetUserName($current_user_id) . "</b>";
	//$userprop_tpl = "<tr><td>%FIELD_NAME%</td><td>%FIELD_PRICE%</td></tr>";
	/*$userinfo_tpl = "User:<b><?php echo $current_user_name; ?></b>
	 <table>%userprop%
	 </table>";
	 $arr["userinfo"] = str_replace('%userprop%', $gsession -> GetUserProp($current_user_id, $userprop_tpl), $userinfo_tpl);
	 * */
	if ($G_MODE == G_MODE_PLAY) {
		$usermonopoly_tpl = "Monopolies: <table>%userprop%</table>";
		$userprop_tpl = '<tr><td>%FGROUP_NAME%</td><td>%FGCOST%</td><td>%FGMULT%</td><td><button class="button" id="btn_mon_up_%FGROUP_ID%" onclick="DoUpFGroup(%FGROUP_ID%)">+</button></td><td><button class="button" id="btn_mon_down_%FGROUP_ID%" onclick="DoDownFGroup(%FGROUP_ID%)">-</button></td></tr>';
		$arr["usermonopoly"] = str_replace('%userprop%', $gsession -> GetUserMonopolyList($current_user_id, $userprop_tpl), $usermonopoly_tpl);

		$userinfo_tpl = "User:<b>$current_user_name</b> Propetry:%userprop%";
		$arr["userinfo"] = str_replace('%userprop%', $gsession -> GetUserProperty($current_user_id), $userinfo_tpl);
	}
	$userlist_tpl = "List of users:</br><table>%ROWS%</table></b>";
	$row_tpl = "<tr><td><div id=uc%ACT_ORDER% class='pl us_c%ACT_ORDER%'></td><td class='us_c%IS_HOLDER%'><b>%NAME%</b></td><td>
				</div> %USER_CASH%</td></tr>";
	$userlist_rows = $gsession -> GetUserList($row_tpl);
	$userlist = str_replace('%ROWS%', $userlist_rows, $userlist_tpl);
	$arr["userlist"] = $userlist;
	if ($G_MODE == G_MODE_PLAY) {
		$diceinfo_tpl = "Dice:<b>%LAST_DICE1%:%LAST_DICE2%</b>";
		$arr["dice"] = $gsession -> GetLastUserDiceInfo($current_user_id, $diceinfo_tpl);
	}

	/*$ceil_tpl = '
	 <section id="c%i%" class="%color% right">
	 <div id="pic%i%" style="height: 70%;">
	 %FGROUP_ID%.%NAME%</br>%FCOST%</br>%OWNER_NAME%
	 </div>
	 <div id="pzc%i%">
	 %USERLIST%
	 </div>
	 %SELLBOX%
	 </section>';*/
	if ($G_MODE == G_MODE_PLAY) {
		$ceil_tpl = '<div id="ceil%FCODE%" class="%ONAUCTION% ceil">
		<div id="pic%FCODE%" style="height: 70%;">
		%FGROUP_NAME%.%FIELD_NAME%</br>%FCOST% %FGMULT%</br>%OWNER_NAME%
		</div>
		<div id="pzc%FCODE%">
		%USERLIST%
		</div>
		<div class="%ISSELLABLE% sellbox">
		<button class="button" id="btn_auct_start_%FIELD_ID%" onclick="DoOpenAuctionForm(%FIELD_ID%)">$</button>
		</div>
	</div>';
	} else {
		$ceil_tpl = '<div id="ceil%FCODE%" class="%ONAUCTION% ceil">
		<div id="pic%FCODE%" style="height: 70%;">
		%FGROUP_NAME%.%FIELD_NAME%</br>%FCOST% %FGMULT%</br>%OWNER_NAME%
		</div>
		<div id="pzc%FCODE%">
		%USERLIST%
		</div>
	</div>';
	}
	$ceil_user_tpl = '<div id=uc%ACT_ORDER% class="pl us_c%ACT_ORDER%"></div>';
	/**
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
	$ceil_arr = $gsession -> GetChangedFieldListArray($current_user_id, $ceil_tpl, $ceil_user_tpl, $dblasttime);
	$arr = array_merge($arr, $ceil_arr);

	$auct_tpl = "<div id=auctions >%ROWS%</div>";
	//$auct_lot_tpl = "<h3><a href='#'>Lot %FIELD_NAME% Bid:%AUCT_BID% Bidder:%AUCT_BID_USER_NAME%</a></h3>
	/*$auct_lot_tpl = "<h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; AU%AUCT_ID% Lot %FIELD_NAME% Bid:%AUCT_BID% %AUCT_BID_USER_NAME%
	 <button class='button' id='btn_auct%AUCT_ID%_bid' %BID_DISABLED% onclick='DoBidAuction(%AUCT_ID%)'>Bid</button>
	 <button class='button' id='btn_auct%AUCT_ID%_leave' %LEAVE_DISABLED% onclick='DoLeaveAuction(%AUCT_ID%)'>Leave</button>
	 </h3>
	 <div id='pnl_auct%AUCT_ID%'>
	 </div>";*/
	$auct_lot_tpl = "<div class='auctionlot lots' id='auctlot_%AUCT_ID%'> <div class='right'>AU%AUCT_ID% Lot %FIELD_NAME% Bid:%AUCT_BID% %AUCT_BID_USER_NAME%</div> 
	<div class='left'>
	<button class='button' id='btn_auct%AUCT_ID%_bid' %JOIN_DISABLED% onclick='DoJoinAuction(%AUCT_ID%)'>J</button>
	<button class='button' id='btn_auct%AUCT_ID%_bid' %BID_DISABLED% onclick='DoBidAuction(%AUCT_ID%)'>Bid</button>
	<button class='button' id='btn_auct%AUCT_ID%_leave' %LEAVE_DISABLED% onclick='DoLeaveAuction(%AUCT_ID%)'>X</button>
	</div>
	<div id='pnl_auct%AUCT_ID%' style='clear: both;'>
	</div>
	</div>";
	//$arr["auctbox"] = str_replace('%ROWS%', $gsession -> GetOpenedAuctionsList($current_user_id, $auct_lot_tpl), $auct_tpl);
	$auct_arr = $gsession -> GetChangedAuctionListArray($current_user_id, $auct_lot_tpl, $dblasttime, 'auctlot_%AUCT_ID%', NULL, G_AU_AUCT_STATUS_ACTIVE);
	$arr = array_merge($arr, $auct_arr);
	if ($dblasttime != NULL) {
		$auct_arr = $gsession -> GetChangedAuctionListArray($current_user_id, NULL, $dblasttime, 'auctlot_%AUCT_ID%', NULL, G_AU_AUCT_STATUS_INACTIVE);
		$arr = array_merge($arr, $auct_arr);
	}

	$auct_lot_subrow_tpl = "%USER_NAME% %LAST_BID%</br>";
	/*$rs = $gsession -> GetOpenedAuctionsSet($current_user_id);
	 foreach ($rs as $row) {
	 $auction = new GAuction($gsession_id);
	 $auction -> Load($row['auct_id']);
	 $arr["pnl_auct" . $row['auct_id']] = $auction -> GetActiveUsersList($auct_lot_subrow_tpl);
	 }*/
	$auct_arr = $gsession -> GetChangedAuctionsLotListArray($current_user_id, $auct_lot_subrow_tpl, $dblasttime, 'pnl_auct%AUCT_ID%', NULL, G_AU_AUCT_STATUS_ACTIVE);
	$arr = array_merge($arr, $auct_arr);

	$deal_tpl = "<div class='deallot lots' id='deallot_%DEAL_ID%'> <div class='right'>DL%DEAL_ID% From:%DEAL_HOLDER_USER_NAME% </div> 
	<div class='left'>
	<button class='button' id='btn_deal%DEAL_ID%_accept' %ACCEPT_DISABLED% onclick='DoAcceptDeal(%DEAL_ID%)'>A</button>
	<button class='button' id='btn_deal%DEAL_ID%_cancel' %CANCEL_DISABLED% onclick='DoCancelDeal(%DEAL_ID%)'>C</button>
	<button class='button' id='btn_deal%DEAL_ID%_reject' %REJECT_DISABLED% onclick='DoRejectDeal(%DEAL_ID%)'>R</button>
	</div>
	<div id='pnl_deal%DEAL_ID%' style='clear: both;'>
	Player %DEAL_OPPONENT_USER_NAME% will receive %HOLDER_PAYMENT%</br>
	%DEALITEMS_GIVE%</br>
	Player %DEAL_HOLDER_USER_NAME% will receive %OPPONENT_PAYMENT%</br>
	%DEALITEMS_RECEIVE%
	</div>
	</div>";
	$deal_lot_give_tpl = "%FIELD_NAME%";
	$deal_lot_receive_tpl = "%FIELD_NAME%";
	$deal_arr = $gsession -> GetChangedDealListArray($current_user_id, $deal_tpl, $dblasttime, 'deallot_%DEAL_ID%', NULL, G_AU_AUCT_STATUS_ACTIVE, '%DEALITEMS_GIVE%', $deal_lot_give_tpl, '%DEALITEMS_RECEIVE%', $deal_lot_receive_tpl, true, ',');
	$arr = array_merge($arr, $deal_arr);

	if ($dblasttime != NULL) {
		$deal_arr = $gsession -> GetChangedDealListArray($current_user_id, NULL, $dblasttime, 'deallot_%DEAL_ID%', NULL, G_DL_DEAL_STATUS_INACTIVE);
		$arr = array_merge($arr, $deal_arr);
	}

	/*$rs = $gsession -> GetOpenedAuctionsSet($current_user_id);
	 foreach ($rs as $row) {
	 $auction = new GAuction($gsession_id);
	 $auction -> Load($row['auct_id']);
	 $arr["pnl_auct" . $row['auct_id']] = $auction -> GetActiveUsersList($auct_lot_subrow_tpl);
	 }*/
	//$deal_arr = $gsession -> GetChangedDealsLotListArray($current_user_id, $auct_lot_subrow_tpl, $dblasttime, 'pnl_deal%DEAL_ID%',  NULL, G_AU_AUCT_STATUS_ACTIVE);
	//$arr = array_merge ($arr, $deal_arr);
	if ($G_MODE == G_MODE_PLAY) {
		$proplist_tpl = '<input type="checkbox" id="chk_deal_field_id%FIELD_ID%" /><label for="chk_deal_field_id%FIELD_ID%">%FIELD_NAME%</label></br>';
		$arr["property_set_owner"] = $gsession -> GetUserPropertyList($current_user_id, $proplist_tpl);

		$proplist_tpl = '<input type="checkbox" id="chk_deal_field_id%FIELD_ID%" /><label for="chk_deal_field_id%FIELD_ID%">%FIELD_NAME%</label></br>';
		$items_arr = $gsession -> GetChangedOponentPropertyListArray($current_user_id, $proplist_tpl, $dblasttime, 'property_set_user_id%USER_ID%');
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

