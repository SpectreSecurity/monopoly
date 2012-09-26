<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once ('microcore.php');
include_once ('adodb5/adodb.inc.php');
include_once ('adodb5/adodb-exceptions.inc.php');
include_once ('watchdog.php');

function random($min, $max) {
	// md5() generates a hexadecimal number, so we must convert it into base 10
	$rand = base_convert(md5(microtime()), 16, 10);
	// the modulus operator doesn't work with great numbers, so we have to cut the number
	$rand = substr($rand, 10, 6);
	$diff = $max - $min + 1;
	return ($rand % $diff) + $min;
}

function ConnectDB() {
	//throw new Exception('error');
	global $db;
	try {
		$db = ADONewConnection("mysqlt");
		// create a connection
		$db -> PConnect('wenter', 'monopoly', 'xsw2zaq1', 'monopoly');
		//$sql=array();
		// or die('Fail');
		//$sql = "set character_set_client='cp1251';";
		$sql = "set character_set_client='utf8';";
		$rs = $db -> Execute($sql);
		//$rs -> Close();
		//$sql = "set character_set_results='cp1251';";
		$sql = "set character_set_results='utf8';";
		$rs = $db -> Execute($sql);
		//$rs -> Close();
		$sql = "set collation_connection='cp1251_general_cs';";
		$rs = $db -> Execute($sql);
		$rs -> Close();
		//$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$db -> SetFetchMode(ADODB_FETCH_BOTH);
	} catch (exception $e) {
		//var_dump($e);
		//adodb_backtrace($e->gettrace());
		echo $e -> getMessage();
	}
}

/*
 function DbQuery($sql) {
 global $db;
 $rs = $db -> Execute($sql);
 if ($rs)
 while ($arr = $rs -> FetchRow()) {
 # process $arr
 print_r($arr);
 }
 }
 */

function DbQuery($query, $mask, $rowdelimter = "", $encodechars = false) {
	global $db;

	$res = '';
	$rs = $db -> Execute($query);
	//print_r($rs);
	if ($db -> ErrorNo() > 0) {
		return $db -> ErrorMsg();
	} else {
		while ($array = $rs -> FetchRow()) {
			if ($res != null) {
				$line = $rowdelimter;
			} else {
				$line = '';
			}
			$line = $line . $mask;
			for ($i = 0; $i < $rs -> FieldCount(); $i++) {
				$fld = $rs -> FetchField($i);
				//echo '$fld->name='.$fld->name;
				//echo $array[$fld->name];
				if ($encodechars) {
					$val = preg_replace("%\n%", "", nl2br(htmlspecialchars($array[$fld -> name])));
				} else {
					$val = $array[$fld -> name];
				}
				//$act_tmpl=str_replace('%REP_ID%',urlencode($rep_id),$act_tmpl);

				$line = str_replace('%' . strtoupper($fld -> name) . '%', $val, $line);
			}
			$res = $res . $line;
		}
		$rs -> Close();
	}
	$rs = null;
	return $res;
}

function DbGetValue($sql) {
	global $db;
	$res = NULL;
	$rs = $db -> Execute($sql);
	if ($rs)
		if (!$rs -> EOF) {
			# process $arr
			$res = $rs -> fields[0];
		}
	$rs -> Close();
	return $res;
}

function DbSQL($sql) {
	global $db;
	$rs = $db -> Execute($sql);
	$rs -> Close();
	return true;
}

function DbGetValueSet($sql) {
	global $db;
	$rs = $db -> Execute($sql);
	return $rs;
}

function DbINSERT($sql) {
	global $db;
	$rs = $db -> Execute($sql);
	$rs -> Close();
	return $db -> Insert_ID();
}

function DbStartTrans() {
	global $db;
	//$db->SetTransactionMode("READ");
	//mysql_query("BEGIN");
	//$db->debug = true;
	return $db -> StartTrans();
}

function DbCompleteTrans() {
	global $db;
	return $db -> CompleteTrans();
}

function DbTimeStamp() {
	return DbGetValue("select CURRENT_TIMESTAMP");
}

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

function GetChangedGSessionListArray($limit, $user_id, $tpl, $item_name_tpl, $gstatus, $lastupdated = NULL, $notgstatus= false ,$encodechars = true, $rowdelimter = '') {
	$arr = array();
	$lastupdated_cond = '';
	if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
		$lastupdated_cond = " and g.last_updated >= '" . $lastupdated . "' ";
	}
	$gstatus_cond = '';
	if (($gstatus != NULL) && ($gstatus != 'NULL')) {
		$gstatus_cond = " and g.gstatus ".($notgstatus?'!':'')."= '" . $gstatus . "' ";
	}
	$sql = "SELECT g.gsession_id
                   FROM `m_gsession` g 
                  WHERE  0=0 $gstatus_cond $lastupdated_cond
		  order by gsession_id desc limit 0, $limit";
	$rs = DbGetValueSet($sql);
	foreach ($rs as $row) {
		$gsession_id = $row['gsession_id'];
		$info_sql = "SELECT g.gsession_id, m.name map_name, g.`map_id`, g.`startstamp`, g.`endstamp`, g.`gstatus`, g.`gturn`, g.`last_updated`, '%ACTION%' action
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
	$user_id = ($user_id == NULL ? us_GetCurrentUserId() : $user_id);
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
	$gstatus = DbGetValue("select gstatus from `m_gsession` where gsession_id=$gsession_id");
	if ($gstatus != G_GS_GSTATUS_CREATED) {
		return false;
	}
	return true;
}

function CanGSesssionPlayUser($gsession_id, $user_id) {
	if (($gsession_id == NULL) || ($user_id == NULL)) {
		return false;
	}
	$gstatus = DbGetValue("select gstatus from `m_gsession` where gsession_id=$gsession_id");
	if (($gstatus == G_GS_GSTATUS_STARTED) || ($gstatus == G_GS_GSTATUS_CREATED)) {
		if (DbGetValue("select 1 from `m_gsession_user` where gsession_id=$gsession_id and `user_id`=$user_id") == 1) {
			return true;
		}
	}
	return false;
}

/*
 *
 */
class GSession {
	public $gsession_id = 0;
	public $map_id = 0;
	public $start_field_id = 0;
	private $gauctions = array();
	private $gdeals = array();
	private $gturn = NULL;
    public $gstatus = NULL;

	function __construct() {
		//create existed
	}

	//-----------------------------------------------
	// GSession init methods
	//-----------------------------------------------
	function Load($gsession_id) {
		//create existed
		//get last gsession only for debug
		//$gsession_id = DbGetValue("select max(gsession_id) gsession_id from m_gsession ");
		$this -> gsession_id = $gsession_id;
		$map_id = DbGetValue("select map_id from m_gsession where gsession_id=$gsession_id");
		$this -> map_id = $map_id;
		$this -> gturn = DbGetValue("select gturn from m_gsession where gsession_id=$gsession_id");
		$this -> start_field_id = DbGetValue("Select field_id from `m_cfg_map_field` where map_id=$map_id and ftype_code=" . G_GS_FTYPE_START);
		$this -> gstatus = DbGetValue("select gstatus from m_gsession where gsession_id=$gsession_id");        
	}

	function Create($map_id, $user_id) {
		//create new
		DbStartTrans();
		//create new gsession
		$this -> gsession_id = DbINSERT("INSERT INTO m_gsession (`map_id`, `gstatus`, createstamp) VALUES ($map_id," . G_GS_GSTATUS_CREATED . ", current_timestamp)");
		//init map
		DbSQL("INSERT INTO m_gsession_map_field (`gsession_id`, `map_id`, `field_id`, `fgroup_id`, `fparam`, fparam_calc1, fparam_calc2) 
	SELECT " . $this -> gsession_id . ",$map_id, field_id, `fgroup_id`, fparam, fparam_calc1, fparam_calc2 from `m_cfg_map_field` where map_id=$map_id");
		DbSQL("INSERT INTO m_gsession_map_fgroup (`gsession_id`, `map_id`, `fgroup_id`, `fgparam` ) 
	SELECT " . $this -> gsession_id . ",$map_id, fgroup_id, fgparam from `m_cfg_map_fgroup` where map_id=$map_id");

		//init properties
		//$this -> map_id = $map_id;
		//$this -> start_field_id = DbGetValue("Select field_id from `m_cfg_map_field` where map_id=$map_id and ftype_code=" . G_GS_FTYPE_START);
		$this -> Load($this -> gsession_id);
		//log
		LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "create gsession");
		return DbCompleteTrans();
	}

	function CanStart() {
		$res=false;
        if (($this->gstatus==G_GS_GSTATUS_CREATED) && ($this -> GetActivePlayersCount() >= G_GS_MIN_PLAYERS)) {
        	$res=true;
        }
		return $res;
	}

	function Terminate() {
		DbStartTrans();
        if (gsession_lock_updates($this -> gsession_id)) {
			DbSQL("UPDATE `m_gsession` SET endstamp=current_timestamp, `gstatus`=" . G_GS_GSTATUS_TERMINATED . " 
		         WHERE `gsession_id`=" . $this -> gsession_id);
			gsession_unlock_updates($this -> gsession_id);
         }
		//todo add message to all gsession users
		return DbCompleteTrans();
	}

	function Start() {
		//create new
		DbStartTrans();
		//init first user
		/*$holder_act_order = DbGetValue("select min(act_order)
		 from  m_gsession_user where gsession_id = " . $this -> gsession_id);
		 DbSQL("UPDATE m_gsession_user
		 SET `is_holder`=true
		 WHERE act_order =  $holder_act_order");*/
		DbSQL("UPDATE m_gsession_user 
		 SET `is_holder`=true
         WHERE gsu_id = ( select res from (select min(gsu_id) as res
             from  m_gsession_user 
             where gsession_id = " . $this -> gsession_id . ") as tmp)");
		DbSQL("UPDATE m_gsession_user u 
		         SET `act_order`= (select x.rank from (
		                            select @rownum:=@rownum+1 rank, gu.gsu_id 
		                            from m_gsession_user gu, (SELECT @rownum:=0) r 
		                            WHERE gu.gsession_id = " . $this -> gsession_id . ") x 
		                           where x.gsu_id=u.gsu_id)
                 where u.gsession_id = " . $this -> gsession_id);

		DbSQL("UPDATE `m_gsession` SET startstamp=current_timestamp, `gstatus`=" . G_GS_GSTATUS_STARTED . " 
         WHERE `gsession_id`=" . $this -> gsession_id);

		//todo add message to all gsession users
		//log
		//LogGSession($this -> gsession_id, ??? , G_LOG_LVL_DEBUG, "start gsession");
		$this -> Load($this -> gsession_id);
		return DbCompleteTrans();
	}
	
	function IsStarted(){
		return $this->gstatus==G_GS_GSTATUS_STARTED;
	} 
	
	function IsCreated(){
		return $this->gstatus==G_GS_GSTATUS_CREATED;
	} 
	
	function IsTerminated(){
		return $this->gstatus==G_GS_GSTATUS_TERMINATED;
	} 
	
	function IsFinished(){
		return $this->gstatus==G_GS_GSTATUS_FINISHED;
	} 
	
	function GetLastChanged() {
		return DbGetValue("select last_updated from m_gsession where gsession_id=" . $this -> gsession_id);
	}

	function HasChanges($last_changed) {
		return DbGetValue("select 1 from m_gsession where gsession_id=" . $this -> gsession_id . " and last_updated >='$last_changed'") == 1;
	}

	public function MarkUpdated() {
		try {
			DbSQL("update m_gsession set last_updated=current_timestamp where gsession_id=" . $this -> gsession_id);
		} catch(Exception $ex) {
					LogCritical('Caught exception: '.$ex->getMessage(), 'mu');
					LogCritical($ex -> getTraceAsString(),'mu');
		}
	}

	private function MarkUpdatedByField($field_id) {
		DbSQL("update m_gsession_map_field set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and field_id='$field_id'");
        $this->MarkUpdated();
	}

	private function MarkUpdatedByOwner($user_id) {
		DbSQL("update m_gsession_map_field set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and owner_user_id='$user_id'");
        $this->MarkUpdated();
	}

	private function MarkUpdatedByFGroup($fgroup_id) {
		DbSQL("update m_gsession_map_field set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and fgroup_id='$fgroup_id'");
        $this->MarkUpdated();
	}

	private function MarkUpdatedUser($user_id) {
		DbSQL("update m_gsession_user set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and user_id='$user_id'");
        $this->MarkUpdated();
	}

	//---------------------
	// events
	//---------------------
	function OnUserPropertyChange($user_id, $field_id = NULL) {
		$this -> RefreshUserMonopolies($user_id);
		if ($field_id != NULL) {
			$this -> MarkUpdatedByField($field_id);
		} else {
			//todo MarkUpdated all user fields
			$this -> MarkUpdatedByOwner($user_id);
		}
		return true;
	}

	function OnUserCashChange($user_id) {
		$this -> MarkUpdatedUser($user_id);
	}

	function OnAuctionClose($field_id) {
		$this -> MarkUpdatedByField($field_id);
	}

	//---------------------
	private function ChangeFGroup($fgroup_id, $user_id, $delta) {
		$lockname = "refreshfgroup_user_$user_id";
		DbStartTrans();
		if (DbLock($lockname)) {
			$fgchangecost = $this -> GetFGroupCost($fgroup_id) * ($this -> GetFGroupFGParam($fgroup_id) + $delta) * ($delta < 0 ? -0.5 : 1);
			$sql = "Update m_gsession_map_fgroup gfg, m_gsession_user gu set gfg.fgparam = gfg.fgparam + $delta, gu.user_cash = gu.user_cash - ($fgchangecost)
 			where gfg.gsession_id = " . $this -> gsession_id . " and gfg.fgroup_id=$fgroup_id and gfg.fgowner_user_id=$user_id 
 			   and gu.gsession_id = " . $this -> gsession_id . " and gu.user_id = $user_id";
			DbSQL($sql);
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "ChangeFGroup on fgroup_id=$fgroup_id delta: $delta fgchangecost: $fgchangecost");
			//todo add markupdated
			$this -> MarkUpdatedByFGroup($fgroup_id);
			$this -> MarkUpdatedUser($user_id);
			//$this->MarkUpdated($field_id)
			//
			DbUnlock($lockname);
		}
		return DbCompleteTrans();

	}

	private function RefreshUserMonopolies($user_id) {
		DbStartTrans();
		//LogGSession($this -> gsession_id, NULL, G_LOG_LVL_DEBUG, "RefreshUserMonopolies");
		$lockname = "refreshfgroup_user_$user_id";
		if (DbLock($lockname)) {
			/**/
			//LogGSession($this -> gsession_id, NULL, G_LOG_LVL_DEBUG, "RefreshUserMonopolies($user_id)");
			$sql = "update m_gsession_map_fgroup set  fgparam = NULL, fgowner_user_id =NULL
			 where gsession_id = " . $this -> gsession_id . " and fgowner_user_id=$user_id and
			 fgroup_id not in
			 (select a.fgroup_id from
			 (SELECT x.`fgroup_id`, count(1) cnt FROM `m_cfg_map_field` x
			 where x.map_id =" . $this -> map_id . " group by x.`fgroup_id`) a,
			 (SELECT m.`fgroup_id`, count(1) cnt FROM `m_gsession_map_field`
			 gm left join m_cfg_map_field m on gm.field_id=m.field_id
			 where gm.gsession_id=" . $this -> gsession_id . " and gm.owner_user_id=$user_id group by `fgroup_id`
			 ) b
			 where a.fgroup_id=b.fgroup_id and a.cnt=b.cnt)";
			DbSQL($sql);
			$sql = "update m_gsession_map_fgroup set  fgparam = IF(fgowner_user_id=$user_id,fgparam,1), fgowner_user_id =$user_id
			 where gsession_id = " . $this -> gsession_id . " and
			 fgroup_id in
			 (select a.fgroup_id from
			 (SELECT x.`fgroup_id`, count(1) cnt FROM `m_cfg_map_field` x
			 where x.map_id =" . $this -> map_id . " group by x.`fgroup_id`) a,
			 (SELECT m.`fgroup_id`, count(1) cnt FROM `m_gsession_map_field`
			 gm left join m_cfg_map_field m on gm.field_id=m.field_id
			 where gm.gsession_id=" . $this -> gsession_id . " and gm.owner_user_id=$user_id group by `fgroup_id`
			 ) b
			 where a.fgroup_id=b.fgroup_id and a.cnt=b.cnt)";
			DbSQL($sql);
			/**/
			/*
			 * $sql = "select fgroup_id, fgowner_user_id m_gsession_map_fgroup
			 *		where gsession_id = " . $this -> gsession_id . " and
			 *		fgroup_id in
			 *			(select a.fgroup_id from
			 *				(SELECT x.`fgroup_id`, count(1) cnt FROM `m_cfg_map_field` x
			 *					where x.map_id =" . $this -> map_id . " group by x.`fgroup_id`) a,
			 *				(SELECT m.`fgroup_id`, count(1) cnt FROM `m_gsession_map_field`
			 *					gm left join m_cfg_map_field m on gm.field_id=m.field_id
			 *					where gm.gsession_id=" . $this -> gsession_id . " and gm.owner_user_id=$user_id group by `fgroup_id`
			 *				) b
			 *			where a.fgroup_id=b.fgroup_id and a.cnt=b.cnt)";
			 *
			 *$rs = DbGetValueSet($sql);
			 *foreach ($rs as $row) {
			 *	$fgroup_id = $row['fgroup_id']; ;
			 *}
			 */
			DbUnlock($lockname);
		}

		return DbCompleteTrans();
	}

	//-----------------------------------------------
	// GSession info methods
	//-----------------------------------------------
	function GetGTurn() {
		return $this -> gturn;
	}

	function IncGTurn() {
		$this -> gturn = $this -> gturn + 1;
        if (gsession_lock_updates($this -> gsession_id)) {
			DbSQL("update m_gsession set gturn=" . $this -> gturn . " where gsession_id=" . $this -> gsession_id);
	        gsession_unlock_updates($this -> gsession_id);
		}

	}

	function GetHolderUserId() {
		$user_id=DbGetValue("select user_id from `m_gsession_user` where `gsession_id`=" . $this -> gsession_id . " and `is_holder`=true");
		//if ($user_id == NULL) { raise_exception("GetHolderUserId is null"); }
		return $user_id;
	}

	function GetPosition($user_id) {
		return DbGetValue("select position_field_id from `m_gsession_user` where `gsession_id`=" . $this -> gsession_id . " and `user_id`=$user_id");
	}

	function GetUserProperty($user_id) {
		return DbGetValue("SELECT IFNULL(sum(gm.fparam),0)
                   FROM `m_gsession_map_field` gm 
                  WHERE gm.gsession_id = " . $this -> gsession_id . " 
		  and gm.owner_user_id=$user_id");
	}

	function GetUserCash($user_id) {
		return DbGetValue("SELECT IFNULL(user_cash,0)
                   FROM `m_gsession_user` gu 
                  WHERE gu.gsession_id = " . $this -> gsession_id . " 
		  and gu.user_id=$user_id");
	}

	function GetUserFund($user_id) {
		return $this -> GetUserProperty($user_id) + $this -> GetUserCash($user_id);
	}

	function GetFieldFParam($field_id) {
		return DbGetValue("SELECT fparam
                   FROM `m_gsession_map_field` gm 
                  WHERE gm.gsession_id = " . $this -> gsession_id . " 
		  and gm.field_id=$field_id");
	}

	function GetFGroupFGParam($fgroup_id) {
		return DbGetValue("SELECT fgparam
                   FROM `m_gsession_map_fgroup` gm 
                  WHERE gm.gsession_id = " . $this -> gsession_id . " 
		  and gm.fgroup_id=$fgroup_id");
	}

	function GetFieldOpenedAuctionId($field_id) {
		return DbGetValue("SELECT auct_id
                   FROM `m_gsession_auction` ga 
                  WHERE ga.gsession_id = " . $this -> gsession_id . " 
                  	and ga.auct_state ='" . G_AU_AUCT_STATE_OPENED . "'
                  	and ga.field_id=$field_id");
	}

	function & GetFieldOpenedAuction($field_id) {
		$auction = NULL;
		$auct_id = $this -> GetFieldOpenedAuctionId($field_id);
		if ($auct_id != NULL) {
			//	$auction = new GAuction($this -> gsession_id);
			//	$auction -> Load($auct_id);
			$auction = &$this -> getGAuction($auct_id);
		}
		return $auction;
	}

	function GetFieldOwner($field_id) {
		return DbGetValue("SELECT owner_user_id
                   FROM `m_gsession_map_field` gm 
                  WHERE gm.gsession_id = " . $this -> gsession_id . " 
		  and gm.field_id=$field_id");
	}

	function GetFGroupCost($fgroup_id) {
		return DbGetValue("SELECT sum(gm.fparam)
                   FROM `m_gsession_map_field` gm,  `m_cfg_map_field` mf
                  WHERE gm.gsession_id = " . $this -> gsession_id . " and gm.field_id=mf.field_id
		  and mf.fgroup_id=$fgroup_id");
	}

	function GetFGroupOwner($fgroup_id) {
		return DbGetValue("SELECT fgowner_user_id
                   FROM `m_gsession_map_fgroup` gm 
                  WHERE gm.gsession_id = " . $this -> gsession_id . " 
		  and gm.fgroup_id=$fgroup_id");
	}

	function GetFieldFType($field_id) {
		return DbGetValue("SELECT ftype_code
                   FROM `m_cfg_map_field` gm 
                  WHERE gm.map_id = " . $this -> map_id . " 
		  and gm.field_id=$field_id");
	}

	function CanAssignUser($user_id) {
		//check if it doesnt assigned
		//$res=false;
		//if ($user_id!=NULL) {
		//	$res=!(DbGetValue("select 1 from m_gsession_user where gsession_id=" . $this -> gsession_id . " and user_id='$user_id'")==1);
		//}
		//return $res;
		return CanGSesssionJoinUser($this -> gsession_id, $user_id);
	}

	function CanPlayUser($user_id) {
		return CanGSesssionPlayUser($this -> gsession_id, $user_id);
	}

	//-----------------------------------------------
	// GSession user CRUD methods
	//-----------------------------------------------
	function AssignUser($user_id) {
		//check if it doesnt assigned
		//Assign user
		DbStartTrans();
		DbSQL("INSERT INTO `m_gsession_user`
		(`gsession_id`, `user_id`, `is_active`, `is_holder`, `has_penalty`, `user_cash`, `position_field_id`) 
		VALUES 
		(" . $this -> gsession_id . ",$user_id, TRUE,FALSE,FALSE," . G_GSINIT_USER_CASH . "," . $this -> start_field_id . ")");
		$this->MarkUpdated();
		LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "assign user");
		return DbCompleteTrans();
	}

	function DeactivateUser($user_id) {
		//check if it doesnt assigned
		//Assign user
		DbStartTrans();
		//to do
		LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "deactivate user");
		return DbCompleteTrans();
	}

	//-----------------------------------------------
	// User methods
	//-----------------------------------------------
	function UserUpgradeMonopoly($user_id, $fgroup_id) {
		//Assign user
		//check is he session holder
		$res = FALSE;
		if (($this -> GetFGroupOwner($fgroup_id) == $user_id) && ($this -> GetUserCash($user_id) >= ($this -> GetFGroupCost($fgroup_id) * ($this -> GetFGroupFGParam($fgroup_id) + G_GS_FGROUP_FGPARAM_DELTA)))) {
			$res = $this -> ChangeFGroup($fgroup_id, $user_id, G_GS_FGROUP_FGPARAM_DELTA);
		}
		return $res;
	}

	function UserDowngradeMonopoly($user_id, $fgroup_id) {
		//Assign user
		//check is he session holder
		$res = FALSE;
		if (($this -> GetFGroupOwner($fgroup_id) == $user_id) && ($this -> GetFGroupFGParam($fgroup_id) > 1)) {
			$res = $this -> ChangeFGroup($fgroup_id, $user_id, -G_GS_FGROUP_FGPARAM_DELTA);
		}
		return $res;
	}

	function UserMakeTurn($user_id) {
		//Assign user
		//check is he session holder
		if (($user_id != $this -> GetHolderUserId()) || ($this -> GetUserCash($user_id) < 0)) {
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "user  turn access deny");
			return false;
		}
		return $this -> HolderMakeTurn();
	}

	function UserAssignField($user_id, $field_id) {
		//Assign field user
		DbStartTrans();
		$this -> MarkUpdatedByField($this -> GetPosition($user_id));
		DbSQL("update `m_gsession_map_field` gm
		set gm.owner_user_id=$user_id
		where gm.gsession_id=" . $this -> gsession_id . " and gm.field_id=$field_id");
		$this -> MarkUpdatedByField($field_id);
		return DbCompleteTrans();
	}

	//-----------------------------------------------
	// Msg methods
	//-----------------------------------------------
	function AddMesage($msg, $msg_type, $user_id = NULL) {
		//check if it doesnt assigned
		//Assign user
		DbStartTrans();
		if ((!isset($user_id)) || ($user_id == '')) {
			$user_id = 'NULL';
		}
		DbSQL("INSERT INTO `m_gsession_msg`(`user_id`, `gsession_id`, `msgtype`, `msg_text`) 
		VALUES ($user_id, " . $this -> gsession_id . ", $msg_type, '$msg')");
		return DbCompleteTrans();
	}

	//-----------------------------------------------
	// Holder methods
	//-----------------------------------------------
	function HolderDoAction($field_id, $act_event) {
		//check if it doesnt assigned
		//DbStartTrans();
		$user_id = $this -> GetHolderUserId();
		$user_name = GetUserName($user_id);
		$pay_type = '';
		$exch_type = '';
		$sql_tpl = DbGetValue("select t2.fact_sql_tpl 
			from m_cfg_map_field t1, m_cfg_faction t2 
			where t1.fact_code=t2.fact_code  
			  and t1.field_id=$field_id and t1.map_id= " . $this -> map_id);
		$msg = DbGetValue("select t2.fact_msg_tpl 
			from m_cfg_map_field t1, m_cfg_faction t2 
			where t1.fact_code=t2.fact_code  
			  and t1.field_id=$field_id and t1.map_id= " . $this -> map_id);
		$fparam = DbGetValue("select t1.fparam 
			from m_gsession_map_field t1 
			where t1.gsession_id = " . $this -> gsession_id . " and t1.field_id=$field_id and t1.map_id= " . $this -> map_id);
		$fact_cond = DbGetValue("select t1.fact_cond 
			from m_cfg_map_field t1 
			where t1.field_id=$field_id and t1.map_id= " . $this -> map_id);
		$fparam_calc1 = DbGetValue("select t1.fparam_calc1 
			from m_gsession_map_field t1 
			where t1.gsession_id = " . $this -> gsession_id . " and t1.field_id=$field_id and t1.map_id= " . $this -> map_id);
		$fparam_calc2 = DbGetValue("select t1.fparam_calc2 
			from m_gsession_map_field t1 
			where t1.gsession_id = " . $this -> gsession_id . " and t1.field_id=$field_id and t1.map_id= " . $this -> map_id);
		$sql = "select $user_id user_id, '$user_name' user_name, gu.user_cash,
				" . $this -> gsession_id . " gsession_id, " . $this -> gturn . " gturn, " . $this -> GetActivePlayersCount() . " active_players,
				gfg.fgparam, IF(t1.ftype_code=2,(IFNULL(gfg.fgparam,1) * t3.fparam), NULL) ftax,  
				t3.field_id, '%fact_cond%' fact_cond, '%fparam_calc1%' fparam_calc1, '%fparam_calc2%' fparam_calc2,t3.fparam, '%pay_type%' pay_type, '%exch_type%' exch_type,
				t3.owner_user_id, own.name owner_user_name, t1.fcode, t1.name field_name, '" . $this -> GetUserProperty($user_id) . "' user_property,
				(select max(x.user_cash) from m_gsession_user x where x.gsession_id = " . $this -> gsession_id . ") max_user_cash
			from m_cfg_map_field t1 
			left join m_gsession_map_fgroup gfg on t1.fgroup_id=gfg.fgroup_id and gfg.gsession_id = " . $this -> gsession_id . ", 
			m_gsession_user gu, 
			m_gsession_map_field t3 
			left join m_user own on t3.owner_user_id=own.user_id
			where t1.field_id=t3.field_id and gu.user_id = $user_id 
			  and gu.gsession_id = " . $this -> gsession_id . "
			  and t3.field_id=$field_id and t3.map_id= " . $this -> map_id . "
			  and t3.gsession_id = " . $this -> gsession_id;
		if (strpos($fact_cond, '%') != FALSE) {
			//try to replace params
			$fact_cond = DbQuery(str_replace('%fact_cond%', 't1.fact_cond', $sql), $fact_cond, "", false);
		}
		if (isset($fact_cond) && ($fact_cond != '')) {
			//try to calc fparams
			$fact_cond = DbGetValue("select $fact_cond");
			$fact_cond_bool = $fact_cond > 0 ? TRUE : FALSE;
		} else {
			$fact_cond_bool = TRUE;
		}
		if ($fact_cond_bool) {
			if (strpos($fparam_calc1, '%') != FALSE) {
				//try to replace params
				$fparam_calc1 = DbQuery(str_replace('%fparam_calc1%', 't3.fparam_calc1', $sql), $fparam_calc1, "", false);
			}
			if (strpos($fparam_calc2, '%') != FALSE) {
				//try to replace params
				$fparam_calc2 = DbQuery(str_replace('%fparam_calc2%', 't3.fparam_calc2', $sql), $fparam_calc2, "", false);
			}
			//to do need to optimize = to do not call twice the same sql!!
			if (isset($fparam_calc1) && ($fparam_calc1 != '')) {
				//try to calc fparams
				$fparam_calc1 = DbGetValue("select $fparam_calc1");
				$pay_type = $fparam_calc1 > 0 ? G_TXT_INCOME : G_TXT_LOSS;
				$exch_type = $fparam_calc1 > 100 ? G_TXT_STOCK_UP : G_TXT_STOCK_DOWN;

			}
			if (isset($fparam_calc2) && ($fparam_calc2 != '')) {
				//try to calc fparams
				$fparam_calc2 = DbGetValue("select $fparam_calc2");
			}
			if ($sql_tpl != '') {
				$act_sql = DbQuery(str_replace('%fparam_calc1%', $fparam_calc1, str_replace('%fparam_calc2%', $fparam_calc2, $sql)), $sql_tpl, "", false);
				//echo $sql;
				DbSQL($act_sql);
			}
			if (strpos($msg, '%') != FALSE) {
				//try to replace params
				$msg = DbQuery(str_replace('%fparam_calc1%', $fparam_calc1, str_replace('%fparam_calc2%', $fparam_calc2, str_replace('%pay_type%', $pay_type, str_replace('%exch_type%', $exch_type, $sql)))), $msg);
			}
			if (isset($msg) && ($msg != '')) {
				$this -> AddMesage($msg, G_GS_MSGTYPE_ACTMSG);
			}
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "$act_event action on field=$field_id fparam=$fparam msg=$msg done");
		} else {
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "$act_event action on field=$field_id not started due condition: $fact_cond");
		}

		return true;
		//DbCompleteTrans();
	}

	function HolderDoOnflyAction($field_id) {
		//check if it doesnt assigned
		//DbStartTrans();
		/*$user_id = $this -> GetHolderUserId();
		 $sql_tpl = DbGetValue("select t2.fact_sql_tpl
		 from m_cfg_map_field t1, m_cfg_faction t2
		 where t1.fact_code=t2.fact_code
		 and t1.field_id=$field_id and t1.map_id= " . $this -> map_id);
		 if ($sql_tpl != '') {
		 $sql = "select $user_id user_id, " . $this -> gsession_id . " gsession_id,t3.field_id, t3.fparam, t3.owner_user_id, t1.fcode, t1.name
		 from m_cfg_map_field t1, m_gsession_map_field t3
		 where t1.field_id=t3.field_id
		 and t3.field_id=$field_id and t3.map_id= " . $this -> map_id;
		 $act_sql = DbQuery($sql, $sql_tpl, "", false);
		 DbSQL($act_sql);
		 LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "onfly action on field=$field_id done");
		 }
		 return true;
		 //DbCompleteTrans();
		 * */
		return $this -> HolderDoAction($field_id, 'onfly');
	}

	function HolderDoOnsiteAction($field_id) {
		return $this -> HolderDoAction($field_id, 'onsite');
	}

	private function GetNextUser($user_id, $nopenalty = true) {
		$nopenalty_cond = $nopenalty ? ' and (if(has_penalty=1, ifnull(penalty_turn,0),0))< ' . $this -> gturn : '';
		return DbGetValue("SELECT user_id 
				FROM   m_gsession_user 
				WHERE  act_order IN (SELECT Ifnull(Min(act_order), (SELECT Min(act_order) 
                				                                    FROM   m_gsession_user 
                                				                    WHERE  gsession_id = " . $this -> gsession_id . "
																	AND is_active = true
																	$nopenalty_cond)) 
                     FROM   m_gsession_user 
                     WHERE  act_order > (SELECT act_order 
                                         FROM   m_gsession_user 
                                         WHERE  gsession_id = " . $this -> gsession_id . " 
                                                AND user_id = $user_id)
					   AND is_active = true and gsession_id = " . $this -> gsession_id . " 
					   $nopenalty_cond) and gsession_id = " . $this -> gsession_id . " ");
	}

	function GetUserPenaltyTurn($user_id) {
		return DbGetValue("SELECT if(has_penalty=1, ifnull(penalty_turn,0),0) 
				FROM   m_gsession_user  where gsession_id = " . $this -> gsession_id . " 
                                                AND user_id = $user_id");
	}

	private function GetNextTurnPlayer() {
		$user_id = $this -> GetHolderUserId();
		$next_user_id = $this -> GetNextUser($user_id, true);
		if ($next_user_id == NULL) {
			$next_user_id = $this -> GetNextUser($user_id, false);
		}
		//$maxcnt = $this -> GetActivePlayersCount() - 1;
		//$i = 1;
		//while (($this -> GetUserPenaltyTurn($next_user_id) > $this -> gturn) && ($i < $maxcnt)) {
		//	$i++;
		//	$next_user_id = $this -> GetNextUser($next_user_id);
		//}
		return $next_user_id;

	}

	function HolderMakeTurn() {
		//Assign user
		//		gs_turn_lock_updates($this -> gsession_id);
		DbStartTrans();
		//$lockname = "GTURN_G" . $this -> gsession_id . "_T" . $this -> gturn;
		//if (DbLock($lockname)) {
		if (gs_turn_lock_updates($this -> gsession_id)) {
			//check is he session holder
			$user_id = $this -> GetHolderUserId();
			$user_name = GetUserName($user_id);
			$prev_pos = $this -> GetPosition($user_id);

			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "user turn start");
			//dice
			$dice1 = random(1, 6);
			$dice2 = random(1, 6);

			$cur_pos = $this -> GetPosition($user_id);
			//$next_pos = $cur_pos + $dice1 + $dice2;
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "user dice1=$dice1 dice2=$dice2");
			$msg = str_replace('%USER_NAME%', $user_name, G_MSG_INFO_DICE);
			$msg = str_replace('%LAST_DICE1%', $dice1, $msg);
			$msg = str_replace('%LAST_DICE2%', $dice2, $msg);
			$this -> AddMesage($msg, G_GS_MSGTYPE_TURNINFO);
			DbSQL("UPDATE `m_gsession_user` 
		 SET `last_dice1`=$dice1, `last_dice2`=$dice2 
         WHERE gsession_id = " . $this -> gsession_id . " and user_id = $user_id");
			//move and raise on fly events
			//not optimal to be optimized
			$fly_pos = $cur_pos;
			for ($i = 1; $i <= $dice1 + $dice2; $i++) {
				$fly_pos = DbGetValue("SELECT field_id 
				FROM   m_cfg_map_field 
				WHERE  fcode IN (SELECT Ifnull(Min(fcode), (SELECT Min(fcode) 
                				                                    FROM   m_cfg_map_field  
                                				                    WHERE  map_id = " . $this -> map_id . ")) 
                     FROM   m_cfg_map_field 
                     WHERE  fcode > (SELECT fcode 
                                         FROM   m_cfg_map_field 
                                         WHERE  map_id=1 and field_id = $fly_pos))");
				$event = DbGetValue("select event from m_cfg_map_field t1, m_cfg_faction t2 where t1.fact_code=t2.fact_code and t1.field_id=$fly_pos and t1.map_id= " . $this -> map_id);
				if ($event == 'onfly') {
					//raise on fly events
					LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "user raise onfly event for field_id=$fly_pos");
					$this -> HolderDoOnflyAction($fly_pos);
				}

			}

			$next_pos = $fly_pos;
			//Change user position
			DbSQL("UPDATE `m_gsession_user` 
		 SET `position_field_id`=$next_pos 
         WHERE gsession_id = " . $this -> gsession_id . " and user_id = $user_id");
			$msg = str_replace('%USER_NAME%', $user_name, G_MSG_INFO_ONSITE);
			$msg = str_replace('%FIELD_NAME%', GetFieldName($next_pos), $msg);
			$this -> AddMesage($msg, G_GS_MSGTYPE_TURNINFO);
			//EVENT ONSITE
			$event = DbGetValue("select event from m_cfg_map_field t1, m_cfg_faction t2 where t1.fact_code=t2.fact_code and t1.field_id=$next_pos and t1.map_id= " . $this -> map_id);
			if (($event == 'onsite') && ($user_id != $this -> GetFieldOwner($next_pos))) {
				//raise on site events
				LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "user raise onsite event for field_id=$next_pos");
				$this -> HolderDoOnsiteAction($next_pos);
			}
			//AUCTION
			if ($this -> CanOpenFieldAttachedAuction($next_pos)) {
				$auction = &$this -> OpenFieldAuction(G_AU_AUCT_TYPE_ATTACHED, $user_id, $next_pos);
			} else {
				$auction = &$this -> GetFieldOpenedAuction($next_pos);
			}

			if (($auction != NULL) && ($auction -> IsOpened()) && ($auction -> IsAttached())) {
				$auction -> LinkUser($user_id);
			}

			/*if ((($this -> GetFieldOwner($next_pos) == NULL)) && ($this -> GetFieldFType($next_pos) == G_GS_FTYPE_GENERAL)) {
			 $auction = $this -> GetFieldOpenedAuction($next_pos);

			 if ($auction == NULL) {
			 $auction = new GAuction($this -> gsession_id);
			 $bid = $this -> GetFieldFParam($next_pos);
			 $auction -> Start($next_pos, $bid);
			 }
			 $auction -> LinkUser($user_id);
			 }
			 */
			//change gsession holder
			if ($dice1 != $dice2) {
				//$next_user_id = DbGetValue("SELECT user_id
				//FROM   m_gsession_user
				//WHERE  act_order IN (SELECT Ifnull(Min(act_order), (SELECT Min(act_order)
				//				                                    FROM   m_gsession_user
				//                				                    WHERE  gsession_id = " . $this -> gsession_id . "
				//													AND is_active = true))
				//     FROM   m_gsession_user
				//     WHERE  act_order > (SELECT act_order
				//                         FROM   m_gsession_user
				//                         WHERE  gsession_id = " . $this -> gsession_id . "
				//                                AND user_id = $user_id)
				//	   AND is_active = true)");
				$next_user_id = $this -> GetNextTurnPlayer();
				DbSQL("UPDATE `m_gsession_user` 
		 SET `is_holder`=false
         WHERE  gsession_id = " . $this -> gsession_id . " and user_id=$user_id");
				DbSQL("UPDATE `m_gsession_user` 
		 SET `is_holder`=true, has_penalty=false,  penalty_turn = NULL
         WHERE  gsession_id = " . $this -> gsession_id . " and user_id=$next_user_id");
				$this -> IncGTurn();
			}
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "user turn end");
			$this -> MarkUpdatedByField($prev_pos);
			$this -> MarkUpdatedByField($next_pos);
			$this -> MarkUpdatedUser($user_id);
			gs_turn_unlock_updates($this -> gsession_id);
			//DbLockFree($lockname);
		}
		$res = DbCompleteTrans();
		//gs_turn_unlock_updates($this -> gsession_id);
		return $res;
	}

	/**
	 function GetChangedFieldListArray($user_id, $ceil_tpl, $ceil_user_tpl, $lastupdated = NULL,$encodechars = false, $rowdelimter = '') {
	 $arr= array();
	 for ($i = 1; $i <= $this-> GetMapFieldCount(); $i++) {
	 $field_id = GetFieldId_by_fcode($this-> map_id, $i);
	 $tpl = str_replace('%i%', $i, $ceil_tpl);
	 $tpl = $this -> GetFieldInfo($field_id, $tpl);
	 $tpl_ulist = $this -> GetFieldUserInfo($field_id, $ceil_user_tpl);
	 if ($this -> CanSellField($user_id,$field_id)) {
	 $issellable='';
	 } else {
	 $issellable='hidden';
	 }
	 $ceil = str_replace('%ISSELLABLE%', $issellable, str_replace('%USERLIST%', $tpl_ulist, $tpl));
	 $arr["c$i"] = $ceil;
	 }
	 return $arr;
	 }
	 /**/
	function GetChangedFieldListArray($user_id, $ceil_tpl, $ceil_user_tpl, $lastupdated = NULL, $ceil_user_tpl_marker = '%USERLIST%', $encodechars = false, $rowdelimter = '') {
		$arr = array();
		$lastupdated_cond = '';
		//LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "lastupdated $lastupdated");
		if (($lastupdated != NULL) || ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and last_changed >= '" . $lastupdated . "' ";
		}
		$rs = DbGetValueSet("SELECT gm.field_id,m.fcode FROM `m_gsession_map_field` gm, m_cfg_map_field m where gm.gsession_id=" . $this -> gsession_id . " and gm.map_id=m.map_id and gm.field_id=m.field_id $lastupdated_cond");
		//if ($lastupdated_cond == '')
		//LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "SELECT gm.field_id,m.fcode FROM `m_gsession_map_field` gm, m_cfg_map_field m where gm.gsession_id=".$this -> gsession_id." and gm.map_id=m.map_id and gm.field_id=m.field_id $lastupdated_cond");
		foreach ($rs as $row) {
			$field_id = $row['field_id'];
			$fcode = $row['fcode'];
			$tpl = $ceil_tpl;
			$tpl = $this -> GetFieldInfo($field_id, $tpl);
			$tpl_ulist = $this -> GetFieldUserInfo($field_id, $ceil_user_tpl);
			if ($this -> CanSellField($user_id, $field_id)) {
				$issellable = '';
			} else {
				$issellable = 'hidden';
			}
			$ceil = str_replace('%ISSELLABLE%', $issellable, str_replace($ceil_user_tpl_marker, $tpl_ulist, $tpl));
			$arr["c$fcode"] = $ceil;
		}
		return $arr;
	}

	function GetChangedOponentPropertyListArray($user_id, $tpl, $lastupdated = NULL, $item_name_tpl = 'user_id%USER_ID%_property_set', $encodechars = false, $rowdelimter = '') {
		$arr = array();
		$lastupdated_cond = '';
		if (($lastupdated != NULL) || ($lastupdated != 'NULL')) {
			//todo add last_changed
			//$lastupdated_cond = " and last_changed >= '" . $lastupdated . "' ";
			$lastupdated_cond = '';
		}
		$rs = DbGetValueSet("SELECT gu.user_id FROM `m_gsession_user` gu where gu.gsession_id=" . $this -> gsession_id . " and gu.is_active=true and gu.user_id != $user_id $lastupdated_cond");
		foreach ($rs as $row) {
			$op_user_id = $row['user_id'];
			$item = $this -> GetUserPropertyList($op_user_id, $tpl, $encodechars, $rowdelimter);
			$item_name = str_replace('%USER_ID%', $op_user_id, $item_name_tpl);
			$arr[$item_name] = $item;
		}
		return $arr;
	}

	function GetFieldInfo_by_fcode($fcode, $tpl, $encodechars = false, $rowdelimter = '') {
		$field_id = GetFieldId_by_fcode($this -> map_id, $fcode);
		return $this -> GetFieldInfo($field_id, $tpl, $encodechars, $rowdelimter);
	}

	function GetFieldInfo($field_id, $tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT cf.field_id, f.fparam, f.owner_user_id, cf.fcode, cf.name field_name, 
		          cf.fact_code, cf.ftype_code, 
		          cf.fgroup_id, cfg.fgroup_name fgroup_name, gfg.fgparam fgparam, CONCAT('x',gfg.fgparam) fgmult, 
		          IF(cf.ftype_code=2,f.fparam,NULL) fcost, 
		           u.name owner_name, a.auct_id, IF( IFNULL(a.auct_id,0) >0 ,'onauction',NULL ) onauction
			FROM m_cfg_map_field cf 
			LEFT JOIN m_cfg_map_fgroup cfg ON cf.fgroup_id=cfg.fgroup_id   
			LEFT JOIN m_gsession_map_fgroup gfg ON cfg.fgroup_id = gfg.fgroup_id and gfg.gsession_id = " . $this -> gsession_id . ",
			m_gsession_map_field f
			LEFT OUTER JOIN m_user u ON f.owner_user_id = u.user_id
			LEFT OUTER JOIN m_gsession_auction a ON a.gsession_id = " . $this -> gsession_id . " and f.field_id = a.field_id and a.auct_state='" . G_AU_AUCT_STATE_OPENED . "'
                  WHERE f.gsession_id = " . $this -> gsession_id . " and f.field_id=$field_id 
                  and cf.map_id=f.map_id and cf.field_id=f.field_id";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetFieldUserInfo_by_fcode($fcode, $tpl, $encodechars = false, $rowdelimter = '') {
		$field_id = GetFieldId_by_fcode($this -> map_id, $fcode);
		return $this -> GetFieldUserInfo($field_id, $tpl, $encodechars, $rowdelimter);
	}

	function GetFieldUserInfo($field_id, $tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT gu.act_order, u.user_id, u.name  
                   FROM `m_gsession_user` gu, `m_user` u
                  WHERE gu.gsession_id = " . $this -> gsession_id . " 
		  and gu.user_id=u.user_id and position_field_id=$field_id and gu.is_active=true";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetActivePlayersCount() {
		return DbGetValue("SELECT count(1) FROM `m_gsession_user` gu
                  WHERE gu.gsession_id = " . $this -> gsession_id . " and gu.is_active=true");
	}

	function GetUserList($tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT @rownum:=@rownum+1 rank, gu.act_order, u.user_id, u.name, gu.user_cash, gu.is_holder  
                   FROM `m_gsession_user` gu, `m_user` u, (SELECT @rownum:=0) r
                  WHERE gu.gsession_id = " . $this -> gsession_id . " 
		  and gu.user_id=u.user_id and gu.is_active=true 
		   order by gu.act_order";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetUserOponentList($user_id, $tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT gu.act_order, u.user_id, u.name, gu.user_cash, gu.is_holder  
                   FROM `m_gsession_user` gu, `m_user` u
                  WHERE gu.gsession_id = " . $this -> gsession_id . " 
		  and gu.user_id=u.user_id and gu.is_active=true and gu.user_id != $user_id";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetUserPropertyList($user_id, $tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT m.name field_name, gm.fparam field_price,  gm.field_id, $user_id user_id
                   FROM `m_gsession_map_field` gm left join m_cfg_map_field m on gm.field_id=m.field_id
                  WHERE gm.gsession_id = " . $this -> gsession_id . " 
		  and gm.owner_user_id=$user_id";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetUserMonopolyList($user_id, $tpl, $encodechars = false, $rowdelimter = '') {
		//$sql = "SELECT m.fgroup_id, sum(gm.fparam) monopoly_price
		//           FROM `m_gsession_map_field` gm left join m_cfg_map_field m on gm.field_id=m.field_id
		//          WHERE gm.gsession_id = " . $this -> gsession_id . "
		//          and m.fgroup_id in
		//          	(select a.fgroup_id from
		//          			(SELECT x.`fgroup_id`, count(1) cnt FROM `m_cfg_map_field` x
		//          				where x.map_id =" . $this -> map_id . " group by x.`fgroup_id`) a,
		//					(SELECT m.`fgroup_id`, count(1) cnt FROM `m_gsession_map_field`
		//						gm left join m_cfg_map_field m on gm.field_id=m.field_id
		//						where gm.gsession_id=" . $this -> gsession_id . " and gm.owner_user_id=$user_id group by `fgroup_id`
		//					) b
		//			where a.fgroup_id=b.fgroup_id and a.cnt=b.cnt)
		//  		and gm.owner_user_id=$user_id
		//  		group by m.fgroup_id";
		$sql = "SELECT gfg.fgroup_id, mg.fgroup_name, gfg.fgparam fgparam, CONCAT('x',gfg.fgparam) fgmult, 
		(select sum(gf.fparam) fgcost from m_gsession_map_field gf where gf.gsession_id=" . $this -> gsession_id . " and gf.fgroup_id=gfg.fgroup_id) fgcost  
		from m_gsession_map_fgroup gfg
		left join m_cfg_map_fgroup mg on gfg.fgroup_id=mg.fgroup_id
		where gfg.gsession_id=" . $this -> gsession_id . " and gfg.fgowner_user_id = $user_id";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetMapFieldCount() {
		return DbGetValue("SELECT count(1) FROM `m_gsession_map_field` f
                  WHERE f.gsession_id = " . $this -> gsession_id);
	}

	function GetLastUserDiceInfo($user_id, $tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT gu.last_dice1, gu.last_dice2
                   FROM `m_gsession_user` gu
                  WHERE gu.gsession_id = " . $this -> gsession_id . " 
		  and gu.user_id=$user_id";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetLogList($limit, $user_id, $tpl, $log_id = NULL, $lastupdated = NULL, $encodechars = false, $rowdelimter = '') {
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and l.datestamp >= '" . $lastupdated . "' ";
		}
		$logid_cond = '';
		if (($log_id != NULL) && ($log_id != 'NULL')) {
			$lastupdated_cond = " and l.log_id = " . $log_id . " ";
		}
		$sql = "SELECT l.log_id, l.datestamp, l.user_id, u.name, l.action_desc 
                   FROM `m_gsession_log` l LEFT JOIN m_user u ON l.user_id = u.user_id
                  WHERE l.gsession_id = " . $this -> gsession_id . " $lastupdated_cond $logid_cond 
		  order by log_id desc limit 0, $limit";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetMsgList($limit, $user_id, $tpl, $msg_id = NULL, $lastupdated = NULL, $encodechars = false, $rowdelimter = '') {
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and l.datestamp >= '" . $lastupdated . "' ";
		}
		$msgid_cond = '';
		if (($msg_id != NULL) && ($msg_id != 'NULL')) {
			$lastupdated_cond = " and l.msg_id = " . $msg_id . " ";
		}
		$sql = "SELECT l.msg_id, l.datestamp, l.msg_text 
                   FROM `m_gsession_msg` l
                  WHERE l.gsession_id = " . $this -> gsession_id . " 
  			and (l.user_id is null or l.user_id=$user_id)  $lastupdated_cond  $msgid_cond 
		  order by msg_id desc limit 0, $limit";

		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetLastActions($limit, $user_id, $tpl, $lastupdated = NULL, $encodechars = false, $rowdelimter = '') {
		if (($lastupdated == NULL) || ($lastupdated == 'NULL')) {
			$lastupdated = "'1970-01-01 00:00:00'";
		} else {
			$lastupdated = "'$lastupdated'";
		}
		$sql = "SELECT l.log_id, l.datestamp, l.user_id, u.name, l.action_desc 
                   FROM `m_gsession_log` l LEFT JOIN m_user u ON l.user_id = u.user_id
                  WHERE l.gsession_id = " . $this -> gsession_id . " and l.datestamp >= $lastupdated
		  order by log_id limit 0, $limit";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetLastLogArray($limit, $user_id, $tpl, $lastupdated = NULL, $encodechars = false, $rowdelimter = '') {
		$arr = array();
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and l.datestamp >= '" . $lastupdated . "' ";
		}
		$sql = "select x.* from (SELECT l.log_id 
                   FROM `m_gsession_log` l 
                  WHERE l.gsession_id = " . $this -> gsession_id . " $lastupdated_cond
		  order by log_id desc limit 0, $limit) x order by x.log_id asc";
		$rs = DbGetValueSet($sql);
		foreach ($rs as $row) {
			$log_id = $row['log_id'];
			$row = $this -> GetLogList($limit, $user_id, $tpl, $log_id, NULL, $encodechars, $rowdelimter);
			$arr["log_id$log_id"] = $row;
		}
		return $arr;
	}

	function GetLastMessages($limit, $user_id, $tpl, $lastupdated = NULL, $encodechars = false, $rowdelimter = '') {
		if (($lastupdated == NULL) || ($lastupdated == 'NULL')) {
			$lastupdated = "'1970-01-01 00:00:00'";
		} else {
			$lastupdated = "'$lastupdated'";
		}
		$sql = "SELECT l.msg_id, l.datestamp, l.msg_text 
                   FROM `m_gsession_msg` l
                  WHERE l.gsession_id = " . $this -> gsession_id . " 
  			and (l.user_id is null or l.user_id=$user_id)  and l.datestamp >= $lastupdated 
		  order by msg_id desc limit 0, $limit";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetLastMsgArray($limit, $user_id, $tpl, $lastupdated = NULL, $encodechars = false, $rowdelimter = '') {
		$arr = array();
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and l.datestamp >= '" . $lastupdated . "' ";
		}
		$sql = "select x.* from (SELECT l.msg_id msg_id 
                   FROM `m_gsession_msg` l 
                  WHERE l.gsession_id = " . $this -> gsession_id . " $lastupdated_cond
		  order by msg_id desc limit 0, $limit) x order by x.msg_id asc";
		$rs = DbGetValueSet($sql);
		foreach ($rs as $row) {
			$msg_id = $row['msg_id'];
			$row = $this -> GetMsgList($limit, $user_id, $tpl, $msg_id, NULL, $encodechars, $rowdelimter);
			$arr["msg_id$msg_id"] = $row;
		}
		return $arr;
	}

	//-----------------------------------------------
	// GSession auction methods
	//-----------------------------------------------
	function & getGAuction($auct_id) {
		if (!isset($this -> gauctions[$auct_id])) {
			$this -> gauctions[$auct_id] = new GAuction($this);
			//$this -> gauctions[$auct_id] -> setParent($this);
			$this -> gauctions[$auct_id] -> Load($auct_id);
		} else {
			$this -> gauctions[$auct_id] -> ReLoad();
		}
		return $this -> gauctions[$auct_id];
	}

	private function CanOpenFieldAuction($auct_type, $holder_user_id, $field_id) {
		$res = false;
		if (($this -> GetFieldFType($field_id) == G_GS_FTYPE_GENERAL) && ($this -> GetFieldOpenedAuctionId($field_id) == NULL)) {
			if ($auct_type == G_AU_AUCT_TYPE_ATTACHED) {
				if (($this -> GetFieldOwner($field_id) == NULL)) {
					$res = true;
				}
			} elseif ($auct_type == G_AU_AUCT_TYPE_PUBLIC) {
				if ($this -> GetFieldOwner($field_id) == $holder_user_id) {
					$res = true;
				}
			}
		}
		return $res;
	}

	private function & OpenFieldAuction($auct_type, $user_id, $field_id, $holder_user_id = NULL, $bid = NULL) {
		$auction = new GAuction($this, $auct_type);
		if ($auction != NULL) {
			if ($bid == NULL)
				$bid = $this -> GetFieldFParam($field_id);
			$auction -> Start($field_id, $bid, $holder_user_id);
			$this -> MarkUpdatedByField($field_id);
		}
		$this -> gauctions[$auction -> GetAuctId()] = &$auction;
		return $auction;
	}

	function CanOpenFieldPublicAuction($holder_user_id, $field_id) {
		return $this -> CanOpenFieldAuction(G_AU_AUCT_TYPE_PUBLIC, $holder_user_id, $field_id);
	}

	function CanOpenFieldAttachedAuction($field_id) {
		return $this -> CanOpenFieldAuction(G_AU_AUCT_TYPE_ATTACHED, NULL, $field_id);
	}

	function CanSellField($user_id, $field_id) {
		if ($this -> CanOpenFieldAuction(G_AU_AUCT_TYPE_PUBLIC, $user_id, $field_id)) {
			return true;
		}
		return false;
	}

	function OpenFieldPublicAuction($holder_user_id, $field_id, $bid, $checkit = true) {
		$checked = $checkit ? $this -> CanSellField($holder_user_id, $field_id) : true;
		$auction = NULL;
		if ($checked) {
			$auction = $this -> OpenFieldAuction(G_AU_AUCT_TYPE_PUBLIC, $holder_user_id, $field_id, $holder_user_id, $bid);
		}
		return $auction;
	}

	function AuctionUserMakeDeltaBid($auct_id, $user_id, $bid) {
		if ($this -> GetUserFund($user_id) >= $bid) {
			$gauction = &$this -> getGAuction($auct_id);
			return $gauction -> UserMakeDeltaBid($user_id, $bid);
		}
		return false;
	}

	function GetOpenedAuctionsSet($user_id) {
		$sql = "SELECT a.`auct_id`, a.`gsession_id`, a.`field_id`, a.`auct_state`, a.`auct_bid`, a.`auct_bid_user_id`, a.`auct_step`, 
		            u.name auct_bid_user_name,  m.name field_name,  au.auct_user_state, 
		            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "' and (a.`auct_bid_user_id`!=$user_id or a.`auct_bid_user_id` is NULL)),'enabled', 'disabled') is_leave_enabled,
		            IF(a.auct_type='" . G_AU_AUCT_TYPE_PUBLIC . "',IF(IFNULL(au.user_id,0)>0 or a.auct_holder_user_id=$user_id,'disabled','enabled'), 'disabled') is_join_enabled
     		FROM `m_gsession_auction` a  
     		left outer join m_user u on a.auct_bid_user_id = u.user_id 
     		LEFT JOIN m_cfg_map_field m ON a.field_id = m.field_id
     		LEFT outer JOIN m_gsession_auction_user au ON a.auct_id=au.auct_id and au.user_id=$user_id
     		WHERE a.gsession_id = " . $this -> gsession_id . " and a.auct_state='" . G_AU_AUCT_STATE_OPENED . "'
 		    order by a.auct_id asc";
		return DbGetValueSet($sql);
	}

	function GetAuctionsList($user_id, $tpl, $auct_id = NULL, $auct_state = NULL, $lastupdated = NULL, $encodechars = false, $rowdelimter = '') {
		if ($tpl == NULL)
			return NULL;
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and a.last_changed >= '" . $lastupdated . "' ";
		}
		$auct_state_cond = '';
		if (($auct_state != NULL) && ($auct_state != 'NULL')) {
			$auct_state_cond = " and a.auct_state = '" . $auct_state . "' ";
		}
		$auct_id_cond = '';
		if (($auct_id != NULL) && ($auct_id != 'NULL')) {
			$auct_id_cond = " and a.auct_id = $auct_id ";
		}
		$sql = "SELECT a.`auct_id`, a.`gsession_id`, a.`field_id`, a.`auct_state`, a.`auct_bid`, a.`auct_bid_user_id`, a.`auct_step`, 
		            u.name auct_bid_user_name,  m.name field_name,  au.auct_user_state, 
		            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "' and (a.`auct_bid_user_id`!=$user_id or a.`auct_bid_user_id` is NULL)),'enabled', 'disabled') leave_status, 
		            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "' and (a.`auct_bid_user_id`!=$user_id or a.`auct_bid_user_id` is NULL)),'','disabled=\"disabled\"') leave_disabled,
		            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "'),'enabled', 'disabled') bid_status, 
		            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "'),'','disabled=\"disabled\"') bid_disabled,
		            IF(a.auct_type='" . G_AU_AUCT_TYPE_PUBLIC . "',IF(IFNULL(au.user_id,0)>0 or a.auct_holder_user_id=$user_id,'disabled','enabled'), 'disabled') is_join_enabled,
		            IF(a.auct_type='" . G_AU_AUCT_TYPE_PUBLIC . "',IF(IFNULL(au.user_id,0)>0 or a.auct_holder_user_id=$user_id,'disabled=\"disabled\"',''), 'disabled=\"disabled\"') join_disabled
     		FROM `m_gsession_auction` a  
     		left outer join m_user u on a.auct_bid_user_id = u.user_id 
     		LEFT JOIN m_cfg_map_field m ON a.field_id = m.field_id
     		LEFT outer JOIN m_gsession_auction_user au ON a.auct_id=au.auct_id and au.user_id=$user_id
     		WHERE a.gsession_id = " . $this -> gsession_id . " $auct_id_cond $auct_state_cond $lastupdated_cond 
 		    order by a.auct_id asc";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function GetOpenedAuctionsList($user_id, $tpl, $encodechars = false, $rowdelimter = '') {

		//$sql = "SELECT a.`auct_id`, a.`gsession_id`, a.`field_id`, a.`auct_state`, a.`auct_bid`, a.`auct_bid_user_id`, a.`auct_step`,
		//            u.name auct_bid_user_name,  m.name field_name,  au.auct_user_state,
		//            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "' and (a.`auct_bid_user_id`!=$user_id or a.`auct_bid_user_id` is NULL)),'enabled', 'disabled') leave_status,
		//            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "' and (a.`auct_bid_user_id`!=$user_id or a.`auct_bid_user_id` is NULL)),'','disabled=\"disabled\"') leave_disabled,
		//            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "'),'enabled', 'disabled') bid_status,
		//            IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "'),'','disabled=\"disabled\"') bid_disabled,
		//            IF(a.auct_type='" . G_AU_AUCT_TYPE_PUBLIC . "',IF(IFNULL(au.user_id,0)>0 or a.auct_holder_user_id=$user_id,'disabled','enabled'), 'disabled') is_join_enabled,
		//            IF(a.auct_type='" . G_AU_AUCT_TYPE_PUBLIC . "',IF(IFNULL(au.user_id,0)>0 or a.auct_holder_user_id=$user_id,'disabled=\"disabled\"',''), 'disabled=\"disabled\"') join_disabled
		//	FROM `m_gsession_auction` a
		//	left outer join m_user u on a.auct_bid_user_id = u.user_id
		//	LEFT JOIN m_cfg_map_field m ON a.field_id = m.field_id
		//	LEFT outer JOIN m_gsession_auction_user au ON a.auct_id=au.auct_id and au.user_id=$user_id
		//	WHERE a.gsession_id = " . $this -> gsession_id . " and a.auct_state='" . G_AU_AUCT_STATE_OPENED . "'
		//    order by a.auct_id asc";
		//return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
		return $this -> GetAuctionsList($user_id, $tpl, NULL, G_AU_AUCT_STATE_OPENED);
	}

	function GetChangedAuctionListArray($user_id, $tpl, $lastupdated = NULL, $item_name_tpl = 'auct_%AUCT_ID%', $auct_state = NULL, $auct_status = NULL, $encodechars = false, $rowdelimter = '') {
		$arr = array();
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and last_changed >= '" . $lastupdated . "' ";
		}
		$auct_state_cond = '';
		if (($auct_state != NULL) && ($auct_state != 'NULL')) {
			$auct_state_cond = " and auct_state = '" . $auct_state . "' ";
		}
		$auct_status_cond = '';
		if (($auct_status != NULL) && ($auct_status != 'NULL')) {
			$auct_status_cond = " and auct_status = '" . $auct_status . "' ";
		}
		$rs = DbGetValueSet("SELECT gd.auct_id FROM `m_gsession_auction` gd where gd.gsession_id=" . $this -> gsession_id . "  $lastupdated_cond $auct_state_cond $auct_status_cond order by gd.auct_id asc ");

		foreach ($rs as $row) {
			$auct_id = $row['auct_id'];
			$item = NULL;
			if ($tpl != NULL)
				$item = $this -> GetAuctionsList($user_id, $tpl, $auct_id, $auct_state);
			$item_name = str_replace('%AUCT_ID%', $auct_id, $item_name_tpl);
			$arr[$item_name] = $item;
		}
		return $arr;
	}

	function GetChangedAuctionsLotListArray($user_id, $tpl, $lastupdated = NULL, $item_name_tpl = 'pnl_auct%AUCT_ID%', $auct_state = NULL, $auct_status = NULL, $encodechars = false, $rowdelimter = '') {
		$arr = array();
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and last_changed >= '" . $lastupdated . "' ";
		}
		$auct_state_cond = '';
		if (($auct_state != NULL) && ($auct_state != 'NULL')) {
			$auct_state_cond = " and auct_state = '" . $auct_state . "' ";
		}
		$auct_status_cond = '';
		if (($auct_status != NULL) && ($auct_status != 'NULL')) {
			$auct_status_cond = " and auct_status = '" . $auct_status . "' ";
		}
		$rs = DbGetValueSet("SELECT gd.auct_id FROM `m_gsession_auction` gd where gd.gsession_id=" . $this -> gsession_id . "  $lastupdated_cond $auct_state_cond $auct_status_cond order by gd.auct_id asc ");

		foreach ($rs as $row) {
			$auct_id = $row['auct_id'];
			$item = NULL;
			if ($tpl != NULL) {
				//$item = $this -> GetAuctionsList($user_id, $tpl, $auct_id, $auct_state);
				$auction = &$this -> getGAuction($auct_id);
				$item = $auction -> GetActiveUsersList($tpl, $encodechars, $rowdelimter);
			}
			$item_name = str_replace('%AUCT_ID%', $auct_id, $item_name_tpl);
			$arr[$item_name] = $item;
		}
		return $arr;
	}

	//$rs = $gsession -> GetOpenedAuctionsSet($current_user_id);
	//foreach ($rs as $row) {
	//	$auction = new GAuction($gsession_id);
	//	$auction -> Load($row['auct_id']);
	///	$arr["pnl_auct" . $row['auct_id']] = $auction -> GetActiveUsersList($auct_lot_subrow_tpl);
	//}
	/*function GetChangedAuctionsList($user_id, $tpl, $encodechars = false, $rowdelimter = '') {
	 $sql = "SELECT a.`auct_id`, a.`gsession_id`, a.`field_id`, a.`auct_state`, a.`auct_bid`, a.`auct_bid_user_id`, a.`auct_step`,
	 u.name auct_bid_user_name,  m.name field_name,  au.auct_user_state,
	 IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "' and (a.`auct_bid_user_id`!=$user_id or a.`auct_bid_user_id` is NULL)),'enabled', 'disabled') leave_status,
	 IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "' and (a.`auct_bid_user_id`!=$user_id or a.`auct_bid_user_id` is NULL)),'','disabled=\"disabled\"') leave_disabled,
	 IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "'),'enabled', 'disabled') bid_status,
	 IF((au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "'),'','disabled=\"disabled\"') bid_disabled
	 FROM `m_gsession_auction` a
	 left outer join m_user u on a.auct_bid_user_id = u.user_id
	 LEFT JOIN m_cfg_map_field m ON a.field_id = m.field_id
	 LEFT outer JOIN m_gsession_auction_user au ON a.auct_id=au.auct_id and au.user_id=$user_id
	 WHERE a.gsession_id = " . $this -> gsession_id . " and a.auct_state='" . G_AU_AUCT_STATE_OPENED . "'
	 order by a.auct_id asc";
	 return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	 }*/

	// DEAL API
	function & getGDeal($deal_id) {
		if (!isset($this -> gdeals[$deal_id])) {
			$this -> gdeals[$deal_id] = new GDeal($this);
			$this -> gdeals[$deal_id] -> Load($deal_id);
		} else {
			$this -> gdeals[$deal_id] -> ReLoad();
		}
		return $this -> gdeals[$deal_id];
	}

	private function OpenDeal($holder_user_id, $opponent_user_id, $deal_payment, $owner_property_set, $opponent_property_set) {
		$deal = new GDeal($this);
		if ($deal != NULL) {
			$deal -> Start($holder_user_id, $opponent_user_id, $deal_payment, $owner_property_set, $opponent_property_set);
		}
		return $deal;
	}

	function CanUserDealOpen($holder_user_id, $opponent_user_id, $deal_payment, $owner_property_set, $opponent_property_set) {
		//todo Create check can user open deal
		return true;
	}

	function UserDealOpen($holder_user_id, $opponent_user_id, $deal_payment, $owner_property_set, $opponent_property_set) {
		if ($this -> CanUserDealOpen($holder_user_id, $opponent_user_id, $deal_payment, $owner_property_set, $opponent_property_set)) {
			return $this -> OpenDeal($holder_user_id, $opponent_user_id, $deal_payment, $owner_property_set, $opponent_property_set);
		}
		return false;
	}

	function GetChangedDealListArray($user_id, $tpl, $lastupdated = NULL, $item_name_tpl = 'deal_%DEAL_ID%', $deal_state = NULL, $deal_status = NULL, $give_item_mask = NULL, $give_item_tpl = NULL, $receive_item_mask = NULL, $receive_item_tpl = NULL, $encodechars = false, $rowdelimter = '') {
		$arr = array();
		$lastupdated_cond = '';
		if (($lastupdated != NULL) && ($lastupdated != 'NULL')) {
			$lastupdated_cond = " and last_changed >= '" . $lastupdated . "' ";
		}
		$deal_state_cond = '';
		if (($deal_state != NULL) && ($deal_state != 'NULL')) {
			$deal_state_cond = " and deal_state = '" . $deal_state . "' ";
		}
		$deal_status_cond = '';
		if (($deal_status != NULL) && ($deal_status != 'NULL')) {
			$deal_status_cond = " and deal_status = '" . $deal_status . "' ";
		}

		$rs = DbGetValueSet("SELECT gd.deal_id FROM `m_gsession_deal` gd where gd.gsession_id=" . $this -> gsession_id . " and (gd.deal_holder_user_id = $user_id or gd.deal_opponent_user_id= $user_id) $lastupdated_cond $deal_state_cond  $deal_status_cond order by gd.deal_id asc ");

		foreach ($rs as $row) {
			$deal_id = $row['deal_id'];
			$item = NULL;
			if ($tpl != NULL) {
				$item = $this -> GetDealInfo($user_id, $deal_id, $tpl, $give_item_mask, $give_item_tpl, $receive_item_mask, $receive_item_tpl, $encodechars, $rowdelimter);
			}
			$item_name = str_replace('%DEAL_ID%', $deal_id, $item_name_tpl);
			$arr[$item_name] = $item;
		}
		return $arr;
	}

	function GetDealInfo($user_id, $deal_id, $tpl, $give_item_mask = NULL, $give_item_tpl = NULL, $receive_item_mask = NULL, $receive_item_tpl = NULL, $encodechars = false, $rowdelimter = '') {
		//$sql = "SELECT a.deal_id, a.`gsession_id`, a.`deal_holder_user_id`, u1.name deal_holder_user_name, a.`deal_opponent_user_id`, u2.name deal_holder_user_name, a.`deal_state`, a.`deal_startstamp`, a.`deal_payment`, a.`deal_endstamp`,
		//            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_holder_user_id=$user_id,'enabled','disabled'), 'disabled') is_cancel_enabled,
		//            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_holder_user_id=$user_id,'','disabled=\"disabled\"'), 'disabled=\"disabled\"') cancel_disabled,
		//            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'enabled','disabled'), 'disabled') is_reject_enabled,
		//            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'','disabled=\"disabled\"'), 'disabled=\"disabled\"') reject_disabled,
		//            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'enabled','disabled'), 'disabled') is_accept_enabled,
		//            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'','disabled=\"disabled\"'), 'disabled=\"disabled\"') accept_disabled
		//	FROM `m_gsession_deal` a
		//	left join m_user u1 on a.deal_holder_user_id = u1.user_id
		//	left join m_user u2 on a.deal_opponent_user_id = u2.user_id
		//	WHERE a.gsession_id = " . $this -> gsession_id . " and a.deal_id=$deal_id
		//    order by a.deal_id asc";
		//return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
		$res = NULL;
		$deal = &$this -> getGDeal($deal_id);
		if ($deal != NULL) {
			$res = $deal -> GetDealInfo($user_id, $tpl, $give_item_mask, $give_item_tpl, $receive_item_mask, $receive_item_tpl, $encodechars, $rowdelimter);
		}
		return $res;
	}

}

class GDeal {
	private $gsession = NULL;
	private $gsession_id = 0;
	private $deal_id = 0;
	//private $map_id = 0;
	private $opponent_user_id = NULL;
	private $holder_user_id = NULL;
	private $deal_payment = 0;
	private $deal_state = NULL;
	private $deal_status = NULL;
	function __construct(&$gsession) {
		$this -> gsession = &$gsession;
		$this -> gsession_id = $this -> gsession -> gsession_id;
		//create existed
	}

	//constructors
	function Start($holder_user_id, $opponent_user_id, $deal_payment, $owner_property_set, $opponent_property_set) {
		//create
		if (($deal_payment == NULL) || ($deal_payment == 0)) {
			$deal_payment = 'NULL';
		}
		$this -> deal_id = DbINSERT("INSERT INTO `m_gsession_deal`( `gsession_id`, `deal_status`, `deal_holder_user_id`, `deal_opponent_user_id`, `deal_state`, `deal_startstamp`, `deal_payment` ) VALUES
			(" . $this -> gsession_id . ", '" . G_DL_DEAL_STATUS_ACTIVE . "',$holder_user_id , $opponent_user_id , '" . G_DL_DEAL_STATE_OPENED . "', CURRENT_TIMESTAMP , $deal_payment )");

		if ($owner_property_set != NULL)
			foreach ($owner_property_set as $item) {
				DbINSERT("INSERT INTO `m_gsession_deal_list`(`deal_id`, `ddirection`, `field_id`)  VALUES
					(" . $this -> deal_id . ", '" . G_DL_DDIRECTION_GIVE . "', $item )");
			}
		if ($opponent_property_set != NULL)
			foreach ($opponent_property_set as $item) {
				DbINSERT("INSERT INTO `m_gsession_deal_list`(`deal_id`, `ddirection`, `field_id`)  VALUES
					(" . $this -> deal_id . ", '" . G_DL_DDIRECTION_RECEIVE . "', $item )");
			}
		$this -> AddMesage(GetCfgMessage('MSG_INFO_DL_OPENED'), G_GS_MSGTYPE_DLMSG);
		$this -> MarkUpdated();
		//load
		return $this -> Load($this -> deal_id);
	}

	function Load($deal_id) {
		$res = true;

		$this -> deal_id = $deal_id;
		$rs = DbGetValueSet("select deal_id, deal_status, deal_state, deal_payment, deal_holder_user_id, deal_opponent_user_id  from m_gsession_deal where deal_id=" . $this -> deal_id);
		if (isset($rs) && (!$rs -> EOF)) {
			$this -> opponent_user_id = $rs -> fields['deal_opponent_user_id'];
			$this -> holder_user_id = $rs -> fields['deal_holder_user_id'];
			$this -> deal_payment = $rs -> fields['deal_payment'];
			$this -> deal_state = $rs -> fields['deal_state'];
			$this -> deal_status = $rs -> fields['deal_status'];
		} else {
			$res = false;
		}
		return $res;
	}

	private function ReLoad() {
		return $this -> Load($this -> deal_id);
	}

	//
	function GetDealId() {
		return $this -> deal_id;
	}

	function IsActive() {
		return $this -> deal_status == G_DL_DEAL_STATUS_ACTIVE;
	}

	function IsInactive() {
		return $this -> deal_status == G_DL_DEAL_STATUS_INACTIVE;
	}

	function IsOpened() {
		return $this -> deal_state == G_DL_DEAL_STATE_OPENED;
	}

	function IsAccepted() {
		return $this -> deal_state == G_DL_DEAL_STATE_ACCEPTED;
	}

	function IsRejected() {
		return $this -> deal_state == G_DL_DEAL_STATE_REJECTED;
	}

	function IsTerminated() {
		return $this -> deal_state == G_DL_DEAL_STATE_TERMINATED;
	}

	function IsCanceled() {
		return $this -> deal_state == G_DL_DEAL_STATE_CANCELED;
	}

	private function MarkUpdated() {
		DbSQL("update m_gsession_deal set last_changed=current_timestamp where deal_id=" . $this -> deal_id);
		if ($this -> gsession != NULL) {
			$this -> gsession -> MarkUpdated();
		}

	}

	function CanUserAccept($user_id) {
		if ($this -> opponent_user_id == $user_id) {
			//todo check cash and field owners
			return true;
		}
		return false;
	}

	function UserAccept($user_id) {
		if ((deal_lock_updates($this -> deal_id)) && ($this -> IsOpened()) && ($this -> CanUserAccept($user_id))) {
			//todo lock
			DbStartTrans();
			if ($this -> deal_payment != NULL) {
				DbSQL("update m_gsession_user u1, m_gsession_user u2 
					set u1.user_cash=u1.user_cash-(" . $this -> deal_payment . "), u2.user_cash=u2.user_cash+(" . $this -> deal_payment . ") 
				where u1.user_id= " . $this -> holder_user_id . " and u1.gsession_id=" . $this -> gsession_id . " and u2.user_id=" . $this -> opponent_user_id . " 
					and u2.gsession_id=" . $this -> gsession_id);
			}
			DbSQL("update m_gsession_map_field gm 
					set gm.owner_user_id = " . $this -> holder_user_id . "
				where gm.owner_user_id= " . $this -> opponent_user_id . " and gm.gsession_id=" . $this -> gsession_id . " 
					and gm.field_id in (select dl.field_id from m_gsession_deal_list dl where dl.deal_id = " . $this -> deal_id . " and dl.ddirection ='" . G_DL_DDIRECTION_RECEIVE . "')");
			DbSQL("update m_gsession_map_field gm 
					set gm.owner_user_id = " . $this -> opponent_user_id . "
				where gm.owner_user_id= " . $this -> holder_user_id . " and gm.gsession_id=" . $this -> gsession_id . " 
					and gm.field_id in (select dl.field_id from m_gsession_deal_list dl where dl.deal_id = " . $this -> deal_id . " and dl.ddirection ='" . G_DL_DDIRECTION_GIVE . "')");

			DbSQL("update m_gsession_deal set deal_status='" . G_DL_DEAL_STATUS_INACTIVE . "',deal_endstamp = CURRENT_TIMESTAMP, deal_state='" . G_DL_DEAL_STATE_ACCEPTED . "' where deal_id=" . $this -> deal_id);
			$this -> AddMesage(GetCfgMessage('MSG_INFO_DL_ACCEPTED'), G_GS_MSGTYPE_DLMSG);
			DbCompleteTrans();
			deal_unlock_updates($this -> deal_id);
			$this -> MarkUpdated();
			$this -> ReLoad();
			if ($this -> gsession != NULL) {
				$this -> gsession -> OnUserPropertyChange($this -> opponent_user_id);
				$this -> gsession -> OnUserCashChange($this -> opponent_user_id);
				$this -> gsession -> OnUserPropertyChange($this -> holder_user_id);
				$this -> gsession -> OnUserCashChange($this -> holder_user_id);
			}
			return true;
		}
		return false;
	}

	function CanUserReject($user_id) {
		if ($this -> opponent_user_id == $user_id)
			return true;
		return false;
	}

	function UserReject($user_id) {
		if (($this -> IsOpened()) && ($this -> CanUserReject($user_id))) {
			DbSQL("update m_gsession_deal set deal_status='" . G_DL_DEAL_STATUS_INACTIVE . "',deal_endstamp = CURRENT_TIMESTAMP,deal_state='" . G_DL_DEAL_STATE_REJECTED . "' where deal_id=" . $this -> deal_id);
			$this -> AddMesage(GetCfgMessage('MSG_INFO_DL_REJECTED'), G_GS_MSGTYPE_DLMSG);
			$this -> MarkUpdated();
			$this -> ReLoad();
			return true;
		}
		return false;
	}

	function CanUserCancel($user_id) {
		if ($this -> holder_user_id == $user_id)
			return true;
		return false;
	}

	function UserCancel($user_id) {
		if (($this -> IsOpened()) && ($this -> CanUserCancel($user_id))) {
			DbSQL("update m_gsession_deal set deal_status='" . G_DL_DEAL_STATUS_INACTIVE . "',deal_endstamp = CURRENT_TIMESTAMP,deal_state='" . G_DL_DEAL_STATE_CANCELED . "' where deal_id=" . $this -> deal_id);
			$this -> AddMesage(GetCfgMessage('MSG_INFO_DL_CANCELED'), G_GS_MSGTYPE_DLMSG);
			$this -> MarkUpdated();
			$this -> ReLoad();
			return true;
		}
		return false;
	}

	function Terminate() {
		if ($this -> IsOpened()) {
			DbSQL("update m_gsession_deal set deal_status='" . G_DL_DEAL_STATUS_INACTIVE . "', deal_endstamp = CURRENT_TIMESTAMP,deal_state='" . G_DL_DEAL_STATE_TERMINATED . "' where deal_id=" . $this -> deal_id);
			$this -> AddMesage(GetCfgMessage('MSG_INFO_DL_TERMINATED'), G_GS_MSGTYPE_DLMSG);
			$this -> MarkUpdated();
			$this -> ReLoad();
			return true;
		}
		return false;
	}

	function AddMesage($msg, $msg_type, $user_id = NULL, $addressee_user_id = NULL) {

		if ($addressee_user_id == NULL) {
			$addressee_user_id = 'NULL';
		}
		if ($user_id == NULL) {
			$user_id = 'NULL';
			$user_name = 'NULL';
		} else {
			$user_name = GetUserName($user_id);
		}
		if (strpos($msg, '%') != FALSE) {
			//try to replace params
			$sql = "SELECT gd.`deal_id`, gd.`gsession_id`, gd.`deal_state`, gd.`deal_status`, gd.`deal_payment`,  
						gd.`deal_opponent_user_id`, u.name deal_opponent_user_name, 
						gd.`deal_holder_user_id`,  hu.name deal_holder_user_name,
						'$user_name' user_name,
						gd.`deal_startstamp`, gd.`last_changed`, gd.`deal_endstamp` 
				FROM `m_gsession_deal` gd
				Left outer join m_user u on gd.deal_opponent_user_id = u.user_id  
				Left outer join m_user hu on gd.deal_holder_user_id = hu.user_id  
				WHERE gd.deal_id=" . $this -> deal_id;
			$msg = DbQuery($sql, $msg);
		}
		if ($msg != '') {
			DbSQL("INSERT INTO `m_gsession_msg`(`user_id`, `gsession_id`, `msgtype`, `msg_text`) 
		VALUES ($addressee_user_id, " . $this -> gsession_id . ", $msg_type, '$msg')");
		}
		return true;
		//DbCompleteTrans();
	}

	function GetDealInfo($user_id, $tpl, $give_item_mask = NULL, $give_item_tpl = NULL, $receive_item_mask = NULL, $receive_item_tpl = NULL, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT a.deal_id, a.`gsession_id`, a.`deal_holder_user_id`, u1.name deal_holder_user_name, a.`deal_opponent_user_id`, u2.name deal_opponent_user_name, a.`deal_state`, a.`deal_startstamp`, a.`deal_payment`, a.`deal_endstamp`,
					IF(a.deal_payment>0,a.deal_payment,'') holder_payment,
					IF(a.deal_payment<0,-(a.deal_payment),'') opponent_payment,
		            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_holder_user_id=$user_id,'enabled','disabled'), 'disabled') is_cancel_enabled,
		            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_holder_user_id=$user_id,'','disabled=\"disabled\"'), 'disabled=\"disabled\"') cancel_disabled,
		            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'enabled','disabled'), 'disabled') is_reject_enabled,
		            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'','disabled=\"disabled\"'), 'disabled=\"disabled\"') reject_disabled,
		            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'enabled','disabled'), 'disabled') is_accept_enabled,
		            IF(a.deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "',IF(a.deal_opponent_user_id=$user_id,'','disabled=\"disabled\"'), 'disabled=\"disabled\"') accept_disabled
     		FROM `m_gsession_deal` a  
     		left join m_user u1 on a.deal_holder_user_id = u1.user_id 
     		left join m_user u2 on a.deal_opponent_user_id = u2.user_id 
     		WHERE a.gsession_id = " . $this -> gsession_id . " and a.deal_id=" . $this -> deal_id . "
 		    order by a.deal_id asc";
		$info = DbQuery($sql, $tpl, $rowdelimter, $encodechars);
		if (($give_item_mask != NULL) && ($give_item_tpl != NULL)) {
			$item = $this -> GetDealDetailsInfo($user_id, G_DL_DDIRECTION_GIVE, $give_item_tpl, $encodechars, $rowdelimter);
			$info = str_replace($give_item_mask, $item, $info);
		}
		if (($receive_item_mask != NULL) && ($receive_item_tpl != NULL)) {
			$item = $this -> GetDealDetailsInfo($user_id, G_DL_DDIRECTION_RECEIVE, $receive_item_tpl, $encodechars, $rowdelimter);
			$info = str_replace($receive_item_mask, $item, $info);
		}
		return $info;
	}

	function GetDealDetailsInfo($user_id, $ddirection, $tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT a.deal_id, a.field_id, gm.fparam, m.name field_name
     		FROM `m_gsession_deal_list` a  
     		left join m_gsession_map_field gm on a.field_id = gm.field_id and gm.gsession_id = " . $this -> gsession_id . "
     		left join m_cfg_map_field m on a.field_id = m.field_id 
     		WHERE a.deal_id=" . $this -> deal_id . " and a.ddirection ='$ddirection'
 		    order by a.field_id asc";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

}

class GAuction {
	private $gsession = NULL;
	private $gsession_id = 0;
	private $auct_id = 0;
	//private $map_id = 0;
	private $field_id = 0;
	private $bidder_user_id = NULL;
	private $holder_user_id = NULL;
	private $bid = 0;
	private $last_step = 0;
	private $auct_state = NULL;
	private $auct_status = NULL;
	private $auct_type = NULL;
	function __construct($gsession, $auct_type = G_AU_AUCT_TYPE_ATTACHED) {
		$this -> gsession = &$gsession;
		$this -> gsession_id = $this -> gsession -> gsession_id;
		$this -> auct_type = $auct_type;
		//create existed
	}

	//function setParent(&$gsession) {
	//	$this -> gsession = &$gsession;
	//}

	//constructors
	function Start($field_id, $bid, $holder_user_id = NULL) {
		$this -> field_id = $field_id;
		$this -> bid = $bid;
		$holder_user_id = $holder_user_id == NULL ? 'NULL' : $holder_user_id;
		$this -> auct_id = DbINSERT("INSERT INTO `m_gsession_auction`(`gsession_id`, `field_id`, `auct_state`, `auct_status`, `auct_bid`, `auct_step`, auct_type, auct_holder_user_id) VALUES
		(" . $this -> gsession_id . ", " . $this -> field_id . ",'" . G_AU_AUCT_STATE_OPENED . "','" . G_AU_AUCT_STATUS_ACTIVE . "'," . $this -> bid . " , 0 , " . $this -> auct_type . ", $holder_user_id)");
		$this -> MarkUpdated();

		$msg_code = $this -> auct_type == G_AU_AUCT_TYPE_ATTACHED ? 'MSG_INFO_AU_ATTACHED_OPENED' : 'MSG_INFO_AU_PUBLIC_OPENED';
		$this -> AddMesage(GetCfgMessage($msg_code), G_GS_MSGTYPE_AUMSG);
		return $this -> Load($this -> auct_id);
	}

	function Load($auct_id) {
		$res = true;

		$this -> auct_id = $auct_id;
		$rs = DbGetValueSet("select field_id, auct_state, auct_status,auct_type, auct_bid, auct_bid_user_id,auct_holder_user_id, auct_step from m_gsession_auction where auct_id=" . $this -> auct_id);
		if (isset($rs) && (!$rs -> EOF)) {
			$this -> field_id = $rs -> fields['field_id'];
			$this -> bid = $rs -> fields['auct_bid'];
			$this -> bidder_user_id = $rs -> fields['auct_bid_user_id'];
			$this -> last_step = $rs -> fields['auct_step'];
			$this -> auct_state = $rs -> fields['auct_state'];
			$this -> auct_status = $rs -> fields['auct_status'];
			$this -> auct_type = $rs -> fields['auct_type'];
			$this -> holder_user_id = $rs -> fields['auct_holder_user_id'];
		} else {
			$res = false;
		}
		return $res;
	}

	private function ReLoad() {
		return $this -> Load($this -> auct_id);
	}

	private function MarkUpdated() {
		try {
			DbSQL("update m_gsession_auction set last_changed=current_timestamp ,auct_laststamp=current_timestamp where auct_id=" . $this -> auct_id);
		} catch(Exception $ex) {
					LogCritical('Caught exception: '.$ex->getMessage(), 'mu');
					LogCritical($ex -> getTraceAsString(),'mu');
		}
		if ($this -> gsession != NULL) {
				$this -> gsession -> MarkUpdated();
		}
	}

	private function lock_updates() {
		return auction_lock_updates($this -> auct_id);
	}

	private function unlock_updates() {
		return auction_unlock_updates($this -> auct_id);
	}

	private function dostep($step, $type, $user_id, $bid) {
		DbStartTrans();
		DbSQL("INSERT INTO `m_gsession_auction_bid`(`gsession_id`, `auct_id`, `bidder_user_id`, `bid`, `auct_step`, `auct_step_type`) VALUES 
		(" . $this -> gsession_id . ", " . $this -> auct_id . "," . ($user_id == NULL ? 'NULL' : $user_id) . ", $bid	, $step, '$type')");
		return DbCompleteTrans();
	}

	private function checkbid($user_id, $bid) {
		$res = FALSE;
		DbStartTrans();
		$step = DbGetValue("select auct_step from m_gsession_auction where auct_id=" . $this -> auct_id . " LOCK IN SHARE MODE");
		if (($step == $this -> last_step) && ($this -> IsActiveUser($user_id)) && ($bid > $this -> bid) && ($this -> IsOpened())) {
			$res = TRUE;
		}
		if ($res)
			return DbCompleteTrans();
		else
			return FALSE;
	}

	function IsActive() {
		return $this -> auct_status == G_AU_AUCT_STATUS_ACTIVE;
	}

	function IsInactive() {
		return $this -> auct_status == G_AU_AUCT_STATUS_INACTIVE;
	}

	function IsOpened() {
		return $this -> auct_state == G_AU_AUCT_STATE_OPENED;
	}

	function IsPublic() {
		return $this -> auct_type == G_AU_AUCT_TYPE_PUBLIC;
	}

	function IsAttached() {
		return $this -> auct_type == G_AU_AUCT_TYPE_ATTACHED;
	}

	function GetAuctId() {
		return $this -> auct_id;
	}

	function GetAuctType() {
		return $this -> auct_type;
	}

	function GetBidderUserId() {
		return $this -> bidder_user_id;
	}

	function GetHolderUserId() {
		return $this -> holder_user_id;
	}

	function GetBid() {
		return $this -> bid;
	}

	function GetActiveUserCount() {
		return DbGetValue("SELECT  count(1) from m_gsession_auction_user where auct_id=" . $this -> auct_id . " and auct_user_state= '" . G_AU_AUCT_USER_STATE_ON . "'");
	}

	function GetUserState($user_id) {
		return DbGetValue("SELECT  auct_user_state from m_gsession_auction_user where auct_id=" . $this -> auct_id . " and user_id = $user_id");
	}

	private function CanAutoClose() {
		if ($this -> auct_type == G_AU_AUCT_TYPE_ATTACHED) {
			$active_user_cnt = $this -> GetActiveUserCount();
			if ($active_user_cnt == 0) {
				return TRUE;
			}
			if (($active_user_cnt == 1) && ($this -> GetBidderUserId() != NULL)) {
				return TRUE;
			}
		}
		return false;
	}

	private function DoEndAction() {
		DbStartTrans();
		if ($this -> bidder_user_id != NULL) {
			//hardcode - to be changed to some cfg select - it can be several actions also
			$auauct_code = ($this -> auct_type == G_AU_AUCT_TYPE_ATTACHED ? 1 : 2);
			//--
			$sql_tpl = DbGetValue("SELECT `auact_sql_tpl` FROM `m_cfg_auaction` WHERE auact_code=$auauct_code");
			$msg_code = DbGetValue("SELECT `auact_msg_tpl_code` FROM `m_cfg_auaction` WHERE auact_code=$auauct_code");
			$sql = "SELECT ga.`auct_id`, ga.`gsession_id`, ga.`field_id`, ga.`auct_state`, ga.`auct_type`, ga.`auct_bid`, ga.`auct_bid` bid, 
						ga.`auct_bid_user_id`, ga.`auct_bid_user_id` bidder_user_id, 
						u.name bidder_user_name, gm.owner_user_id, m.name field_name, 
						ga.`auct_step`, ga.`auct_startstamp`, ga.`auct_laststamp`, ga.`auct_endstamp` 
				FROM `m_gsession_auction` ga
				Left outer join m_user u on ga.auct_bid_user_id = u.user_id  
				Left join m_gsession_map_field gm on ga.field_id = gm.field_id and gm.gsession_id=" . $this -> gsession_id . "
				Left join m_cfg_map_field m on ga.field_id = m.field_id 
				WHERE ga.auct_id=" . $this -> auct_id;
			$msg = GetCfgMessage($msg_code);
			if ($sql_tpl != '') {
				$act_sql = DbQuery($sql, $sql_tpl, "", false);
				DbSQL($act_sql);
			}
			//if (strpos($msg, '%') != FALSE) {
			//try to replace params
			//	$msg = DbQuery($sql, $msg);
			//}
			//if (isset($msg) && ($msg != '')) {
			$this -> AddMesage($msg, G_GS_MSGTYPE_AUMSG);
			//}
			LogGSession($this -> gsession_id, NULL, G_LOG_LVL_DEBUG, "action for auction " . $this -> auct_id . " on field=" . $this -> field_id . " done");
			if ($this -> gsession != NULL) {
				$this -> gsession -> OnUserPropertyChange($this -> bidder_user_id, $this -> field_id);
				$this -> gsession -> OnUserCashChange($this -> bidder_user_id);
				if ($this -> holder_user_id != NULL) {
					$this -> gsession -> OnUserPropertyChange($this -> holder_user_id, $this -> field_id);
					$this -> gsession -> OnUserCashChange($this -> holder_user_id);
				}
			}
		}
		$res=DbCompleteTrans();
		if ($res) {
			$this -> gsession -> OnAuctionClose($this -> field_id);
		} 
		return $res;
	}

	//-----------------------------------------------
	// auction methods
	//-----------------------------------------------

	function Close() {

		$res = true;
		$step = $this -> last_step + 1;
		if ($this -> lock_updates()) {
		if (($this -> auct_state = G_AU_AUCT_STATE_OPENED) && ($this -> dostep($step, 'end', $this -> bidder_user_id, $this -> bid))) {
			DbStartTrans();
			DbSQL("UPDATE `m_gsession_auction` 
		 SET `auct_step`=$step , auct_state = '" . G_AU_AUCT_STATE_CLOSED . "', auct_status = '" . G_AU_AUCT_STATUS_INACTIVE . "', auct_endstamp = CURRENT_TIMESTAMP
         WHERE  auct_id=" . $this -> auct_id);
			$res = DbCompleteTrans();
			$this -> MarkUpdated();
			$this -> AddMesage(GetCfgMessage('MSG_INFO_AU_CLOSED'), G_GS_MSGTYPE_AUMSG);
			if ($res) {
				//$this -> last_step = $step;
				//$this -> auct_state = 'closed';
				$this -> ReLoad();
				$this -> DoEndAction();
			}
		} else {
			$res = false;
		}
			$this -> unlock_updates();
		} else {
			$res = false;
		}

		return $res;
	}

	//--System called
	function LinkUser($user_id) {
		DbStartTrans();
		if (($this -> IsOpened()) && ($this -> GetUserState($user_id) == NULL)) {
			DbSQL("INSERT INTO `m_gsession_auction_user`(`auct_id`, `user_id`, `auct_user_state`) VALUES 
				(" . $this -> auct_id . ", $user_id, 'on' )");
			$this -> MarkUpdated();
		}
		$res= DbCompleteTrans();
		if ($res) {
					$this -> AddMesage(GetCfgMessage('MSG_INFO_AU_USER_JOIN'), G_GS_MSGTYPE_AUMSG, $user_id);
		}
		return true;
	}

	//--UI called
	function UserJoin($user_id) {
		$res = false;
		if ($this -> lock_updates()) {
			if (($this -> auct_type == G_AU_AUCT_TYPE_PUBLIC) && ($this -> holder_user_id != $user_id)) {
				$res = $this -> LinkUser($user_id);
			}
			$this -> unlock_updates();
		}
		return $res;
	}

	//--System called
	function UnlinkUser($user_id, $force = false) {
		DbStartTrans();
		if ($this -> IsOpened() && (!$force || ($this -> bidder_user_id != $user_id))) {
			DbSQL("update `m_gsession_auction_user` 
			set  `auct_user_state` = 'off'  
			where `auct_id` = " . $this -> auct_id . " and user_id = $user_id");
			if ($this -> bidder_user_id == $user_id) {
				//set current bidder  to nobody (but it's better to last user)
				DbSQL("update `m_gsession_auction` 
					set  `auct_bid_user_id` = NULL  
					where `auct_id` = " . $this -> auct_id);
				//to do: return bid from last user
			}
			$this -> AddMesage(GetCfgMessage('MSG_INFO_AU_USER_LEAVE'), G_GS_MSGTYPE_AUMSG, $user_id);
			$this -> MarkUpdated();
			$res = DbCompleteTrans();
			if ($res)
				if ($this -> CanAutoClose()) {
					$this -> Close();
				}
		}
		return $res;
	}

	//--UI called
	function UserLeave($user_id) {
		$res = false;
		if ($this -> lock_updates()) {
			$res = $this -> UnlinkUser($user_id);
			$this -> unlock_updates();
		}
		return $res;
	}

	function IsActiveUser($user_id) {
		$res = FALSE;
		//if (DbGetValue("select 1 from m_gsession_auction_user
		//where auct_id=" . $this -> auct_id . " and user_id=$user_id and auct_user_state='on'") == 1) {
		if ($this -> GetUserState($user_id) == G_AU_AUCT_USER_STATE_ON) {
			$res = TRUE;
		}
		return $res;
	}

	function UserMakeDeltaBid($user_id, $delta) {
		return $this -> UserMakeBid($user_id, $this -> bid + $delta);
	}

	function UserMakeBid($user_id, $bid) {
		$res = true;
		$step = $this -> last_step + 1;
		if ($this -> lock_updates()) {
			if (($this -> checkbid($user_id, $bid)) && ($this -> dostep($step, 'bid', $user_id, $bid))) {
				DbStartTrans();
				DbSQL("UPDATE `m_gsession_auction_user` 
		 SET last_bid = $bid
         WHERE  auct_id=" . $this -> auct_id . " and user_id = $user_id");
				DbSQL("UPDATE `m_gsession_auction` 
		 SET `auct_step`=$step , auct_bid = $bid, auct_bid_user_id = $user_id
         WHERE  auct_id=" . $this -> auct_id);
				$this -> AddMesage(GetCfgMessage('MSG_INFO_AU_MAKEBID'), G_GS_MSGTYPE_AUMSG, $user_id);
				$this -> MarkUpdated();
				$res = DbCompleteTrans();
				$this -> ReLoad();
				if ($res)
					if ($this -> CanAutoClose()) {
						$this -> Close();
						$this -> ReLoad();
					}
				//$this -> last_step = $step;
			} else {
				$res = false;
			}
			$this -> unlock_updates();
		} else {
			$res = false;
		}
		return $res;
	}

	function GetCurrentBidder() {
		return $this -> bidder_user_id;
	}

	function GetCurrentBid() {
		return $this -> bid;
	}

	function GetActiveUsersList($tpl, $encodechars = false, $rowdelimter = '') {
		$sql = "SELECT au.`auct_id`, au.`auct_user_state`, au.`last_bid`, u.name user_name
     		FROM m_gsession_auction_user au   
     		left join m_user u on au.user_id = u.user_id  
     		WHERE au.auct_id = " . $this -> auct_id . "  and au.auct_user_state='" . G_AU_AUCT_USER_STATE_ON . "'
 		    order by au.auct_id asc";
		return DbQuery($sql, $tpl, $rowdelimter, $encodechars);
	}

	function AddMesage($msg, $msg_type, $user_id = NULL, $addressee_user_id = NULL) {
		//check if it doesnt assigned
		//Assign user
		//DbStartTrans();

		if ($addressee_user_id == NULL) {
			$addressee_user_id = 'NULL';
		}
		if ($user_id == NULL) {
			$user_id = 'NULL';
			$user_name = 'NULL';
		} else {
			$user_name = GetUserName($user_id);
		}
		if (strpos($msg, '%') != FALSE) {
			//try to replace params
			$sql = "SELECT ga.`auct_id`, ga.`gsession_id`, ga.`field_id`, ga.`auct_state`, ga.`auct_type`, ga.`auct_bid`, ga.`auct_bid` bid, 
						ga.`auct_bid_user_id`, ga.`auct_bid_user_id` bidder_user_id, u.name bidder_user_name, 
						gm.owner_user_id, 
						ga.auct_holder_user_id, ga.auct_holder_user_id holder_user_id, hu.name holder_user_name,
						m.name field_name, '$user_name' user_name,
						ga.`auct_step`, ga.`auct_startstamp`, ga.`auct_laststamp`, ga.`auct_endstamp` 
				FROM `m_gsession_auction` ga
				Left outer join m_user u on ga.auct_bid_user_id = u.user_id  
				Left outer join m_user hu on ga.auct_holder_user_id = hu.user_id  
				Left join m_gsession_map_field gm on ga.field_id = gm.field_id and gm.gsession_id=" . $this -> gsession_id . "
				Left join m_cfg_map_field m on ga.field_id = m.field_id 
				WHERE ga.auct_id=" . $this -> auct_id;
			$msg = DbQuery($sql, $msg);
		}
		if ($msg != '') {
			DbSQL("INSERT INTO `m_gsession_msg`(`user_id`, `gsession_id`, `msgtype`, `msg_text`) 
		VALUES ($addressee_user_id, " . $this -> gsession_id . ", $msg_type, '$msg')");
		}
		return true;
		//DbCompleteTrans();
	}

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