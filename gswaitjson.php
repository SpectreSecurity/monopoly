<?php
require_once ('core/core.php');
ConnectDB();
$current_user_id = GetCurrentUserId();
$current_user_name = GetUserName($current_user_id);
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
$dblasttime = us_GetCurrentLastUpdated();
$dbstarttime = DbTimeStamp();

header('Content-Type: text/javascript; charset=utf8');
$arr = array();
if ($gsession -> IsCreated()) {
	//$arr["refreshtimeout"] = 3000;

	$msg_tpl = "<div id='msg_id%MSG_ID%' class='msgln'>%DATESTAMP%-%MSG_TEXT%</div>";
	$msg_arr = $gsession -> GetLastMsgArray(30, $current_user_id, $msg_tpl, $dblasttime);
	$arr = array_merge($arr, $msg_arr);

	if ($gsession -> HasChanges($dblasttime)) {

		$userlist_tpl = "List of users:</br><table>%ROWS%</table></b>";
		$row_tpl = "<tr><td><div id=uc%ACT_ORDER% class='pl us_c%ACT_ORDER%'></td><td class='us_c%IS_HOLDER%'><b>%NAME%</b></td><td>
				</div> %USER_CASH%</td></tr>";
		$userlist_rows = $gsession -> GetUserList($row_tpl);
		$userlist = str_replace('%ROWS%', $userlist_rows, $userlist_tpl);
		$arr["userlist"] = $userlist;

	}
	$arr["lastupdated"] = $dbstarttime;
} else if ($gsession -> IsStarted()){
	$arr["go"] = 'go';
} else {
	$arr["back"] = 'back';
}
$json = json_encode($arr);
print($json);
?>

