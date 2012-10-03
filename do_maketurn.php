<?php
require_once ('core/core.php');
ConnectDB();

$user_id = GetCurrentUserId();
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
$action = us_GetCurrentAction();

//echo $gs_id;
//echo $user_id;
//echo $gsession -> map_id;
//if ($gsession -> UserMakeTurn($user_id)) {

//try {
/**/
$user_id = $gsession -> GetHolderUserId();
//if ($gsession -> HolderMakeTurn()) {
if ($gsession -> UserMakeTurn($user_id)) {
	echo '<br>' . "Turn done!";

} else {
	echo '<br>' . "Turn fail. May be it's not your turn!";
}

/**/
/*} catch (exception $e) {
 //var_dump($e);
 //adodb_backtrace($e->gettrace());
 echo $e -> getMessage();
 }*/
?>