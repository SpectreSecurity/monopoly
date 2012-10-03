<?php
require_once ('auth.php');
require_once ('core/core.php');
ConnectDB();
$current_user_id = GetCurrentUserId();
$current_user_name = GetUserName($current_user_id);
$dblasttime = us_GetCurrentLastUpdated();
$dbstarttime = DbTimeStamp();
$gstate = us_GetGState();
$waction = us_GetWAction();
header('Content-Type: text/javascript; charset=utf8');
//header('Access-Control-Allow-Origin: http://www.example.com/');
//header('Access-Control-Max-Age: 3628800');
//header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
$arr = array();

//$arr["refreshtimeout"] = 3000;
$arr["debug"] = $current_user_id;


$gs_join_tpl = "<div id='gs_id%GSESSION_ID%".'el'.$gstate."' class='msgln'>%GSESSION_ID% %MAP_NAME% %STARTSTAMP% %GTURN% %LAST_UPDATED% <a href='/mon/index.php?action=gs%ACTION%&gs_id=%GSESSION_ID%'>%ACTION%</a></div>";
$gs_view_tpl = "<div id='gs_id%GSESSION_ID%".'el'.$gstate."' class='msgln'>%GSESSION_ID% %MAP_NAME% %STARTSTAMP% %LAST_UPDATED% <a href='/mon/index.php?action=gsview&gs_id=%GSESSION_ID%'>view</a></div>";

//$canjoin=CanGSesssionJoinUser(

$gs_tpl = $waction=='wact_play'? $gs_join_tpl:$gs_view_tpl;
$gstate = $gstate==NULL?G_GS_GSTATE_STARTED:$gstate;
$gs_arr = GetChangedGSessionListArray(100, $current_user_id, $gs_tpl, 'gs_id%GSESSION_ID%'.'el'.$gstate, $gstate, $dblasttime);
$arr = array_merge($arr, $gs_arr);

if ($dblasttime!=NULL) {
$gs_del_tpl ='';
$gs_arr = GetChangedGSessionListArray(100, $current_user_id, $gs_del_tpl, 'gs_id%GSESSION_ID%'.'el'.$gstate, $gstate, $dblasttime, true);
$arr = array_merge($arr, $gs_arr);
}

$arr["lastupdated"] = $dbstarttime;
$json = json_encode($arr);
//must wrap in parens and end with semicolon
print($json);
//callback is prepended for json-p
//print_r($arr);
?>

