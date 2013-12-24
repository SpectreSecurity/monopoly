<?php

$g_log_tpl = "<div id='log_id%LOG_ID%' class='msgln'>%DATESTAMP%-%NAME%-%ACTION_DESC%</div>";
$g_msg_tpl = "<div id='msg_id%MSG_ID%' class='msgln'>%DATESTAMP%-%MSG_TEXT%</div>";
$g_usermonopoly_tpl = "Monopolies: <table>%userprop%</table>";
$g_userprop_tpl = '<tr><td>%FGROUP_NAME%</td><td>%FGCOST%</td><td>%FGMULT%</td><td><button class="button" id="btn_mon_up_%FGROUP_ID%" onclick="DoUpFGroup(%FGROUP_ID%)">+</button></td><td><button class="button" id="btn_mon_down_%FGROUP_ID%" onclick="DoDownFGroup(%FGROUP_ID%)">-</button></td></tr>';
$g_userinfo_tpl = "User:<b>$current_user_name</b> Propetry:%userprop%";
$g_userlist_tpl = "List of users:</br><table>%ROWS%</table></b>";
$g_userlist_row_tpl = "<tr><td><div id=uc%ACT_ORDER% class='pl us_c%ACT_ORDER%'></td><td class='us_c%IS_HOLDER%'><b>%NAME%</b></td><td></div> %USER_CASH%</td></tr>";
$g_diceinfo_tpl = "Dice:<b>%LAST_DICE1%:%LAST_DICE2%</b></br><div id='dicer'><div class='die d%LAST_DICE1%'> <span class='dot'></span></div><div class='die d%LAST_DICE2%'> <span class='dot'></span></div></div>";

$g_ceil_prop_ind_tpl = '<div class="us_c%OWNER_ACT_ORDER%" style="width:100%;height:100%"> </div>';

$g_ceil_tpl = '<div id="ceil%FCODE%" class="%ONAUCTION% ceil">
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

$g_ceil_tpl_readonly = '<div id="ceil%FCODE%" class="%ONAUCTION% ceil">
		<div id="pic%FCODE%" style="height: 70%;">
		%FGROUP_NAME%.%FIELD_NAME%</br>%FCOST% %FGMULT%</br>%OWNER_NAME%
		</div>
		<div id="pzc%FCODE%">
		%USERLIST%
		</div>
	</div>';

$g_ceil_user_tpl = '<div id=uc%ACT_ORDER% class="pl us_c%ACT_ORDER%"></div>';
$g_auct_tpl = "<div id=auctions >%ROWS%</div>";

$g_auct_lot_tpl = "<div class='auctionlot lots' id='auctlot_%AUCT_ID%'> <div class='right'>AU%AUCT_ID% Lot %FIELD_NAME% Bid:%AUCT_BID% %AUCT_BID_USER_NAME%</div> 
	<div class='left'>
	<button class='button' id='btn_auct%AUCT_ID%_bid' %JOIN_DISABLED% onclick='DoJoinAuction(%AUCT_ID%)'>J</button>
	<button class='button' id='btn_auct%AUCT_ID%_bid' %BID_DISABLED% onclick='DoBidAuction(%AUCT_ID%)'>Bid</button>
	<button class='button' id='btn_auct%AUCT_ID%_leave' %LEAVE_DISABLED% onclick='DoLeaveAuction(%AUCT_ID%)'>X</button>
	</div>
	<div id='pnl_auct%AUCT_ID%' style='clear: both;'>
	</div>
	</div>";
$g_auct_lot_subrow_tpl = "%USER_NAME% %LAST_BID%</br>";
$g_deal_tpl = "<div class='deallot lots' id='deallot_%DEAL_ID%'> <div class='right'>DL%DEAL_ID% From:%DEAL_HOLDER_USER_NAME% </div> 
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
$g_deal_lot_give_tpl = "%FIELD_NAME%";
$g_deal_lot_receive_tpl = "%FIELD_NAME%";

$g_proplist_tpl = '<input type="checkbox" id="chk_deal_field_id%FIELD_ID%" /><label for="chk_deal_field_id%FIELD_ID%">%FIELD_NAME%</label></br>';

?>

