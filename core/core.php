<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once ('microcore.php');
//--api
require_once ('utils.php');
require_once ('db_api.php');
require_once ('lock_api.php');
//--classes
require_once ('gsession.php');
require_once ('gauction.php');
require_once ('gdeal.php');

require_once ('watchdog.php');

//------------------------------------------------------
// Cfg Mesasge API
//------------------------------------------------------

function GetCfgMessage($msg_code) {
	if ($msg_code == NULL) {
		return NULL;
	}
	$lang_code = us_GetCurrentLangCode();
	return DbGetValue("SELECT `msg` FROM `m_cfg_message_lang` WHERE `msg_code`='$msg_code' and  `lang_code`='$lang_code'");
}

//------------------------------------------------------
// User API
//------------------------------------------------------
function GetCurrentUserId() {
	$user_id = us_GetCurrentUserId();
	if (($user_id == NULL) || ($user_id == 0)) {
		$user_id = GetUserId_by_Identity(us_GetCurrentUserIdentity());
	}
	return $user_id;
}

function GetUserName($user_id) {
	if ($user_id == NULL) {
		return NULL;
	}
	return DbGetValue("select name from `m_user` where `user_id`='$user_id'");
}

function GetUserId_by_Identity($user_identity) {
	if ($user_identity == NULL) {
		return NULL;
	}
	$user_id = DbGetValue("select user_id from `m_user` where `identity`='$user_identity'");
	return $user_id;
}

//------------------------------------------------------
// Map API
//------------------------------------------------------

function GetFieldId_by_fcode($map_id, $fcode) {

	return DbGetValue("select field_id from `m_cfg_map_field` where `map_id`=$map_id and `fcode`=$fcode");
}

function GetFieldName($field_id) {

	return DbGetValue("select name from `m_cfg_map_field` where `field_id`=$field_id");
}

function GetFieldFGroup($field_id) {

	return DbGetValue("select fgroup_id from `m_cfg_map_field` where `field_id`=$field_id");
}

function GetFGroupName($fgroup_id) {

	return DbGetValue("select fgroup_name from `m_cfg_map_fgroup` where `fgroup_id`=$fgroup_id");
}

function GetChangedGSessionListArray($limit, $user_id, $tpl, $item_name_tpl, $gstate, $lastupdated = NULL, $notgstate = false, $encodechars = true, $rowdelimter = '') {
	$arr = array();
	$lastupdated_cond = '';
	if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
		$lastupdated_cond = " and g.last_updated >= '" . $lastupdated . "' ";
	}
	$gstate_cond = '';
	if (($gstate != NULL) && ($gstate != 'NULL')) {
		$gstate_cond = " and g.gstate " . ($notgstate ? '!' : '') . "= '" . $gstate . "' ";
	}
	$sql = "SELECT g.gsession_id
                   FROM `m_gsession` g 
                  WHERE  0=0 $gstate_cond $lastupdated_cond
		  order by gsession_id desc limit 0, $limit";
	$rs = DbGetValueSet($sql);
	foreach ($rs as $row) {
		$gsession_id = $row['gsession_id'];
		$info_sql = "SELECT g.gsession_id, m.name map_name, g.`map_id`, g.`startstamp`, g.`endstamp`, g.`gstate`, g.`gturn`, g.`last_updated`, '%ACTION%' action
                     FROM `m_gsession` g 
			            left join m_cfg_map m on g.map_id=m.map_id
                     WHERE gsession_id = $gsession_id";
		if (CanGSesssionJoinUser($gsession_id, $user_id)) {
			$info_sql = str_replace('%ACTION%', 'join', $info_sql);
		} else if (CanGSesssionPlayUser($gsession_id, $user_id)) {
			$info_sql = str_replace('%ACTION%', 'play', $info_sql);
		} else {
			$info_sql = str_replace('%ACTION%', 'view', $info_sql);
		}
		//$tpl= 'u='.$user_id.' gu='.DbGetValue("select 1 from `m_gsession_user` where gsession_id=$gsession_id `user_id`=$user_id");
		//$tpl= 'u='.$user_id;
		$item = DbQuery($info_sql, $tpl, $rowdelimter, $encodechars);
		$item_name = str_replace('%GSESSION_ID%', $gsession_id, $item_name_tpl);
		$arr[$item_name] = $item;
	}
	return $arr;
}

//------------------------------------------------------
// GSession API
//------------------------------------------------------

function LogGSession($gsession_id, $user_id, $log_level, $action_desc) {
	global $db;
	$microtime = time() . round(microtime() * 1000000);
	//*1000000+round(microtime()*1000000);
	//$user_id = ($user_id == NULL ? us_GetCurrentUserId() : $user_id);
	$user_id = ($user_id == NULL ? ' NULL ' : $user_id);
	DbSQL("INSERT INTO `m_gsession_log`(`gsession_id`, `loglevel`, `user_id`, `action_desc`, `microtime`) 
			VALUES ($gsession_id,$log_level, $user_id,'$action_desc',$microtime)");
	return true;
}

function GetCurrentGSession() {
	$gsession_id = us_GetCurrentGSessionId();

	$gsession = new GSession();
	$gsession -> Load($gsession_id);
	return $gsession;
}

function CanGSesssionJoinUser($gsession_id, $user_id) {
	if (($gsession_id == NULL) || ($user_id == NULL)) {
		return false;
	}
	if (DbGetValue("select 1 from `m_gsession_user` where gsession_id=$gsession_id and `user_id`=$user_id") == 1) {
		return false;
	}
	$gstate = DbGetValue("select gstate from `m_gsession` where gsession_id=$gsession_id");
	if ($gstate != G_GS_GSTATE_CREATED) {
		return false;
	}
	return true;
}

function CanGSesssionPlayUser($gsession_id, $user_id) {
	if (($gsession_id == NULL) || ($user_id == NULL)) {
		return false;
	}
	$gstate = DbGetValue("select gstate from `m_gsession` where gsession_id=$gsession_id");
	if (($gstate == G_GS_GSTATE_STARTED) || ($gstate == G_GS_GSTATE_CREATED)) {
		if (DbGetValue("select 1 from `m_gsession_user` where gsession_id=$gsession_id and `user_id`=$user_id") == 1) {
			return true;
		}
	}
	return false;
}




function CleanALL() {
	DbSQL("TRUNCATE TABLE `m_gsession_msg`");
	DbSQL("TRUNCATE TABLE `m_gsession_log`");
	DbSQL("TRUNCATE TABLE `m_gsession_auction_bid`");
	DbSQL("TRUNCATE TABLE `m_gsession_auction_user`");
	DbSQL("DELETE FROM `m_gsession_auction`");
	DbSQL("ALTER TABLE m_gsession_auction AUTO_INCREMENT = 1");
	DbSQL("TRUNCATE TABLE `m_gsession_deal_list`");
	DbSQL("DELETE FROM `m_gsession_deal`");
	DbSQL("ALTER TABLE m_gsession_deal AUTO_INCREMENT = 1");
	DbSQL("TRUNCATE TABLE `m_gsession_map_field`");
	DbSQL("TRUNCATE TABLE m_gsession_map_fgroup");
	DbSQL("TRUNCATE TABLE `m_gsession_user`");
	DbSQL("DELETE FROM `m_gsession`");
	DbSQL("ALTER TABLE m_gsession AUTO_INCREMENT = 1");
}

/*
 function CreateGSession($map_id, $user_id) {
 global $db;
 //$db->SetTransactionMode("READ");
 //mysql_query("BEGIN");
 //$db->debug = true;
 $db -> StartTrans();
 DbSQL("INSERT INTO m_gsession (`map_id`, `gstatus`) VALUES ($map_id," . G_GSTATUS_STARTED . ")");
 $v_gsession_id = $db -> Insert_ID();
 DbSQL("INSERT INTO m_gsession_map_field (`gsession_id`, `map_id`, `field_id`, `fparam`)
 SELECT $v_gsession_id,$map_id, field_id, fparam from `m_cfg_map_field` where map_id=$map_id");

 LogGSession($v_gsession_id, $user_id, G_LOG_LVL_DEBUG, "start gsession");
 if ($db -> CompleteTrans(true)) {
 echo 'done';

 } else {
 echo 'fail';
 }
 return $v_gsession_id;
 }
 */
/*
 function GSessionAssignUser( $user_id) {
 global $db;
 //$db->SetTransactionMode("READ");
 //mysql_query("BEGIN");
 //$db->debug = true;
 $db -> StartTrans();
 DoSQL("INSERT INTO m_gsession (`map_id`, `gstatus`) VALUES ($map_id," . G_GSTATUS_STARTED . ")");
 $v_gsession_id = $db -> Insert_ID();
 DoSQL("INSERT INTO m_gsession_map_field (`gsession_id`, `map_id`, `field_id`, `fparam`)
 SELECT $v_gsession_id,$map_id, field_id, fparam from `m_cfg_map_field` where map_id=$map_id");

 LogGSession($v_gsession_id, $user_id, G_LOG_LVL_DEBUG, "start gsession");
 if ($db -> CompleteTrans(true)) {
 echo 'done';

 } else {
 echo 'fail';
 }
 return $v_gsession_id;
 }
 */
/*
 $conn->debug =1;
 $query = 'select * from products';
 $conn->SetFetchMode(ADODB_FETCH_ASSOC);
 $rs = $conn->Execute($query);
 echo "<pre>";
 while( !$rs->EOF ) {
 $output[] = $rs->fields;
 var_dump($rs->fields);
 $rs->MoveNext();
 print "<p>";
 }
 die();
 */
?>