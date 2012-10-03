<?php 
require_once ('gpc_api.php');

function us_GetCurrentUserIdentity() {
  	return gpc_get_string("user_identity",NULL); 
}

function us_GetCurrentUserId() {
	/*$user_id=0;
	
	if (isset($_GET["user_id"])){
	  $user_id= $_GET["user_id"];
	} else if ($_COOKIE['last_user_id']!='') {
		$user_id=$_COOKIE['last_user_id'];
	}*/

	//$user_id=gpc_get_int("user_id",0);
	//if ($user_id==0) {
	//	$user_identity = gpc_get_string("user_identity",NULL);
    //    $user_id = GetUserId_by_Identity($user_identity);
	//}
  	return gpc_get_int("user_id",0); 
}


function us_GetCurrentGSessionId() {
  	$gsession_id = gpc_get_int("gs_id",1); 
	//temporary override it
	//$gsession_id = DbGetValue("select max(gsession_id) gsession_id from m_gsession ");

	return $gsession_id;
}

function us_GetGState() {
  	return gpc_get_string("gstate",NULL); 
}

function us_GetWAction() {
  	return gpc_get_string("waction",NULL); 
}


function us_GetCurrentGAuctionId() {
	/*$auct_id = NULL;
	if (isset($_GET["auct_id"])) {
		$auct_id = $_GET["auct_id"];
	}
	*/
	return gpc_get_int("auct_id", NULL);
}

function us_GetCurrentBid() {
	/*$bid = NULL;
	if (isset($_GET["bid"])) {
		$bid = $_GET["bid"];
	}
	*/
	return gpc_get_int("bid", NULL);
}

function us_GetCurrentFGroupId() {
	/*$val = NULL;
	if (isset($_GET["fgroup_id"])) {
		$val = $_GET["fgroup_id"];
	}
	*/
	return gpc_get_int("fgroup_id", NULL);
}

function us_GetCurrentAction() {
	/*$val = NULL;
	if (isset($_GET["action"])) {
		$val = $_GET["action"];
	}
	*/
	return gpc_get_string("action", NULL);
}

function us_GetCurrentLastUpdated() {
	/*$val = NULL;
	if (isset($_GET["lastupdated"])) {
		$val = $_GET["lastupdated"];
	}
	*/
	return gpc_get_string("lastupdated", NULL);
}


function us_GetCurrentLangCode() {
	/*if (isset($_GET["lang_code"])) {
		$lang_code = $_GET["land_code"];
	} else {
		$lang_code = 'rus';
	}*/
	return gpc_get_string("land_code",'rus');
}

function us_GetCurrentFieldId() {
	/*$field_id = NULL;
	if (isset($_GET["field_id"])) {
		$field_id = $_GET["field_id"];
	}
	*/
	return gpc_get_int("field_id", NULL);
}

function us_GetDealId() {
	return gpc_get_int("deal_id", NULL);
}

function us_GetDealOpponentUserId() {
	return gpc_get_int("deal_opponent_user_id", NULL);
}

function us_GetDealPayment() {
	return gpc_get_int("deal_payment", NULL);
}

function us_GetDealHolderPropertySet() {
	return gpc_get_int_array("hps", NULL);
}

function us_GetDealOpponentPropertySet() {
	return gpc_get_int_array("ops", NULL);
}

?>