<?php
/*
 * GSession
 */
class GSession {
	public $gsession_id = 0;
	public $map_id = 0;
	public $start_field_id = 0;
	private $gauctions = array();
	private $gdeals = array();
	private $gturn = NULL;
	public $gstatus = NULL;
	public $gstate = NULL;

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
		$this -> gstate = DbGetValue("select gstate from m_gsession where gsession_id=$gsession_id");
	}

	function Create($map_id, $user_id) {
		//create new
		DbStartTrans();
		//create new gsession
		$this -> gsession_id = DbINSERT("INSERT INTO m_gsession (`map_id`, `gstatus`, `gstate`, createstamp, creator_user_id) VALUES ($map_id," . G_GS_GSTATUS_ACTIVE. "," . G_GS_GSTATE_CREATED . ", current_timestamp, $user_id)");
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
		$res=DbCompleteTrans();
		if ($res)
			$this -> AddMesage(GetCfgMessage('MSG_INFO_GS_CREATED'), G_GS_MSGTYPE_INFO, $user_id);
		return $res;
	}

	function CanStart() {
		$res = false;
		if (($this -> gstate == G_GS_GSTATE_CREATED) && ($this -> GetActivePlayersCount() >= G_GS_MIN_PLAYERS)) {
			$res = true;
		}
		return $res;
	}

	function Terminate() {
		DbStartTrans();
		if (gsession_lock_updates($this -> gsession_id)) {
			DbSQL("UPDATE `m_gsession` SET endstamp=current_timestamp, `gstate`=" . G_GS_GSTATE_TERMINATED . ", `gstatus`=" . G_GS_GSTATUS_INACTIVE. " 
		         WHERE `gsession_id`=" . $this -> gsession_id);
			gsession_unlock_updates($this -> gsession_id);
		}
		//todo add actions on terminate
		$res=DbCompleteTrans();
		if ($res)
			$this -> AddMesage(GetCfgMessage('MSG_INFO_GS_TERMINATED'), G_GS_MSGTYPE_INFO);
		return $res;
	}

	function CanFinish() {
		$res = false;
		if (($this -> gstate == G_GS_GSTATE_STARTED) && ($this -> GetActivePlayersCount() < G_GS_MIN_PLAYERS)) {
			$res = true;
		}
		return $res;
	}
	function Finish() {
		DbStartTrans();
		if (gsession_lock_updates($this -> gsession_id)) {
			DbSQL("UPDATE `m_gsession` SET endstamp=current_timestamp, `gstate`=" . G_GS_GSTATE_FINISHED. ", `gstatus`=" . G_GS_GSTATUS_INACTIVE. " 
		         WHERE `gsession_id`=" . $this -> gsession_id);
			gsession_unlock_updates($this -> gsession_id);
		}
		//todo add message to all gsession users
		$res=DbCompleteTrans();
		if ($res) {
			$this -> AddMesage(GetCfgMessage('MSG_INFO_GS_FINISHED'), G_GS_MSGTYPE_INFO);
			$this -> AddMesage(GetCfgMessage('MSG_INFO_GS_USER_WIN'), G_GS_MSGTYPE_INFO);
		}
		return $res;
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

		DbSQL("UPDATE `m_gsession` SET startstamp=current_timestamp, `gstate`=" . G_GS_GSTATE_STARTED . " 
         WHERE `gsession_id`=" . $this -> gsession_id);

		//todo add message to all gsession users
		//log
		//LogGSession($this -> gsession_id, ??? , G_LOG_LVL_DEBUG, "start gsession");
		$this -> Load($this -> gsession_id);
		$res=DbCompleteTrans();
		if ($res) {
			$this -> AddMesage(GetCfgMessage('MSG_INFO_GS_STARTED'), G_GS_MSGTYPE_INFO);
		}
		return $res;
	}

	function IsStarted() {
		return $this -> gstate == G_GS_GSTATE_STARTED;
	}

	function IsCreated() {
		return $this -> gstate == G_GS_GSTATE_CREATED;
	}

	function IsTerminated() {
		return $this -> gstate == G_GS_GSTATE_TERMINATED;
	}

	function IsFinished() {
		return $this -> gstate == G_GS_GSTATE_FINISHED;
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
			LogCritical('Caught exception: ' . $ex -> getMessage(), 'mu');
			LogCritical($ex -> getTraceAsString(), 'mu');
		}
	}

	private function MarkUpdatedByField($field_id) {
		DbSQL("update m_gsession_map_field set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and field_id='$field_id'");
		$this -> MarkUpdated();
	}

	private function MarkUpdatedByOwner($user_id) {
		DbSQL("update m_gsession_map_field set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and owner_user_id='$user_id'");
		$this -> MarkUpdated();
	}

	private function MarkUpdatedByFGroup($fgroup_id) {
		DbSQL("update m_gsession_map_field set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and fgroup_id='$fgroup_id'");
		$this -> MarkUpdated();
	}

	private function MarkUpdatedUser($user_id) {
		//$this->ManageDebtor($user_id);
		DbSQL("update m_gsession_user set last_changed=current_timestamp where gsession_id=" . $this -> gsession_id . " and user_id='$user_id'");
		$this -> MarkUpdated();
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
		$this -> ManageDebtor($user_id);
		$this -> MarkUpdatedUser($user_id);
	}

	function OnAuctionCreate($field_id, $auct_id) {
		DbSQL("update m_gsession_map_field set fauct_id='$auct_id' where gsession_id=" . $this -> gsession_id . " and field_id='$field_id'");
		$this -> MarkUpdatedByField($field_id);
	}

	function OnAuctionClose($field_id, $auct_id) {
		DbSQL("update m_gsession_map_field set fauct_id=NULL where gsession_id=" . $this -> gsession_id . " and field_id='$field_id'");
		$this -> MarkUpdatedByField($field_id);
	}

	function OnDealStart($field_id, $deal_id) {
		DbSQL("update m_gsession_map_field set fdeal_id='$deal_id' where gsession_id=" . $this -> gsession_id . " and field_id='$field_id'");
		$this -> MarkUpdatedByField($field_id);
	}

	function OnDealFinish($field_id, $deal_id) {
		DbSQL("update m_gsession_map_field set fdeal_id=NULL where gsession_id=" . $this -> gsession_id . " and field_id='$field_id'");
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
		$user_id = DbGetValue("select user_id from `m_gsession_user` where `gsession_id`=" . $this -> gsession_id . " and `is_holder`=true");
		if ($user_id == '') { raise_exception("GetHolderUserId is null",E_USER_ERROR); }
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

	function GetActivePlayersSet() {
		return DbGetValueSet("SELECT `gsu_id`, `gsession_id`, `user_id`, `act_order`, `is_active`, `is_holder`, `has_penalty`, `penalty_turn`, `user_cash`, `position_field_id`, `last_dice1`, `last_dice2`, `debitor_stamp`, `last_changed`  
				FROM `m_gsession_user` gu
                WHERE gu.gsession_id = " . $this -> gsession_id . " and gu.is_active=true");
	}

	function GetActivePlayersCount() {
		return DbGetValue("SELECT count(1) FROM `m_gsession_user` gu
                  WHERE gu.gsession_id = " . $this -> gsession_id . " and gu.is_active=true");
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
		$this -> MarkUpdated();
		LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "assign user");
		$res=DbCompleteTrans();
		if ($res) {
			$this -> AddMesage(GetCfgMessage('MSG_INFO_GS_USER_JOIN'), G_GS_MSGTYPE_INFO, $user_id);
		}
		return $res;
	}

	function DeactivateUser($user_id) {
		//check if it doesnt assigned
        $res=false;
		if (gs_turn_lock_updates($this -> gsession_id)) {
			DbStartTrans();
			//to do add msg
			DbSQL("Update `m_gsession_user` set is_active=false where gsession_id= " . $this -> gsession_id . " and user_id=$user_id");
			DbSQL("Update `m_gsession_map_fgroup` set fgowner_user_id=null, fgparam=NULL where gsession_id= " . $this -> gsession_id . " and fgowner_user_id=$user_id");
			DbSQL("Update `m_gsession_map_field` set owner_user_id=null where gsession_id= " . $this -> gsession_id . " and owner_user_id=$user_id");
			$next_user_id = $this -> GetNextTurnPlayer();
			DbSQL("UPDATE `m_gsession_user` 
			 SET `is_holder`=false
	         WHERE  gsession_id = " . $this -> gsession_id . " and user_id=$user_id");
			DbSQL("UPDATE `m_gsession_user` 
			 SET `is_holder`=true, has_penalty=false,  penalty_turn = NULL
	         WHERE  gsession_id = " . $this -> gsession_id . " and user_id=$next_user_id");
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "deactivate user");
			$res=DbCompleteTrans();
			$this -> MarkUpdated();
			gs_turn_unlock_updates($this -> gsession_id);
		}
		return $res;
	}

	function LoseUser($user_id) {
		//check if it doesnt assigned
		$res = $this -> DeactivateUser($user_id);
		if ($res) {
			$this -> AddMesage(GetCfgMessage('MSG_INFO_GS_USER_LOSE'), G_GS_MSGTYPE_INFO, $user_id);
		}
		return $res;
	}

	function ManageDebtor($user_id) {
		$dtimeout = $this -> GetDebitorTimeout($user_id);
		$dstamp = $this -> GetDebitorStamp($user_id);
		if ($dtimeout > G_GS_DEBITOR_TIMEOUT) {
			$this ->LoseUser($user_id);
		} else if (($user_id == $this -> GetHolderUserId()) && ($dstamp == NULL) && ($this -> GetUserCash($user_id) < 0)) {
			$this ->SetDebitorStamp($user_id);
		} else if (($dstamp != NULL) && ($this -> GetUserCash($user_id) > 0)) {
			$this ->CleanDebitorStamp($user_id);
		}
	}

	function ManageDebtors() {
		if (gs_turn_lock_updates($this -> gsession_id)) {
			$sql="SELECT `user_id`  
				FROM `m_gsession_user` gu
                WHERE gu.gsession_id = " . $this -> gsession_id . " and gu.is_active=true and (user_cash<0 or debitor_stamp is not NULL)";
			$rs = DbGetValueSet($sql);
			foreach ($rs as $row) {
				$user_id = $row['user_id'];
				if (gs_turn_lock_updates($this -> gsession_id)) {
                	$this->ManageDebtor($user_id);
                }
			}
			gs_turn_unlock_updates($this -> gsession_id);
		}
	}

	function GetDebitorStamp($user_id) {
		return DbGetValue("select debitor_stamp from m_gsession_user where gsession_id=" . $this -> gsession_id . " and user_id=$user_id");
	}

	function SetDebitorStamp($user_id) {
		return DbGetValue("update m_gsession_user set debitor_stamp=CURRENT_TIMESTAMP where gsession_id=" . $this -> gsession_id . " and user_id=$user_id");
	}

	function CleanDebitorStamp($user_id) {
		return DbGetValue("update m_gsession_user set debitor_stamp=NULL where gsession_id=" . $this -> gsession_id . " and user_id=$user_id");
	}

	function GetDebitorTimeout($user_id) {
		return DbGetValue("select TIMESTAMPDIFF(SECOND,IFNULL(debitor_stamp,CURRENT_TIMESTAMP),CURRENT_TIMESTAMP) from m_gsession_user where gsession_id=" . $this -> gsession_id . " and user_id=$user_id");
	}
	function GetDebitorTimeLeft($user_id) {
		$dtimeout = $this -> GetDebitorTimeout($user_id);
		if ((!isset($dtimeout)) || ($dtimeout == '')) {
			$dtimeout=0;
		} 
		return G_GS_DEBITOR_TIMEOUT-$dtimeout;
	}
	function IsDebitor($user_id) {
		$stamp=DbGetValue("select debitor_stamp from m_gsession_user where gsession_id=" . $this -> gsession_id . " and user_id=$user_id");
		if ((isset($stamp)) && ($stamp != '')&&($this -> GetUserCash($user_id) < 0)) {
			$res=true;
		} else {
			$res=false;
		}
		return $res;
	}

	//-----------------------------------------------
	// User methods
	//-----------------------------------------------

	function UserUpgradeMonopoly($user_id, $fgroup_id) {
		//Assign user
		//check is he session holder
		$res = FALSE;
		if ($this ->IsStarted()) {
			if (($this -> GetFGroupOwner($fgroup_id) == $user_id) && ($this -> GetUserCash($user_id) >= ($this -> GetFGroupCost($fgroup_id) * ($this -> GetFGroupFGParam($fgroup_id) + G_GS_FGROUP_FGPARAM_DELTA)))) {
				$res = $this -> ChangeFGroup($fgroup_id, $user_id, G_GS_FGROUP_FGPARAM_DELTA);
			}
		}
		return $res;
	}

	function UserDowngradeMonopoly($user_id, $fgroup_id) {
		//Assign user
		//check is he session holder
		$res = FALSE;
		if ($this ->IsStarted()) {
			if (($this -> GetFGroupOwner($fgroup_id) == $user_id) && ($this -> GetFGroupFGParam($fgroup_id) > 1)) {
				$res = $this -> ChangeFGroup($fgroup_id, $user_id, -G_GS_FGROUP_FGPARAM_DELTA);
			}
		}
		return $res;
	}

	function UserMakeTurn($user_id) {
		//Assign user
		//check is he session holder
		if ($this ->IsStarted()) {
			if ($user_id != $this -> GetHolderUserId()) {
				LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "turn access deny");
				return false;
			} else if ($this -> GetUserCash($user_id) < 0) {
				$this -> ManageDebtor($user_id);
				LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "turn deny UserCash < 0");
				//to do make user msg
				return false;
			}
			return $this -> HolderMakeTurn();
		}
		return false;
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
	function AddMesage($msg, $msg_type, $user_id = NULL, $addressee_user_id = NULL) {
		//check if it doesnt assigned
		//Assign user
		DbStartTrans();
		if ($addressee_user_id == NULL) {
			$addressee_user_id = 'NULL';
		}
		if ($user_id == NULL) {
			$user_id = 'NULL';
			$user_name = 'NULL';
		} else {
			$user_name = GetUserName($user_id);
		}
		//try simple replace
                if (strpos($msg, '%') != FALSE) {
			$holder_user_id=$this->GetHolderUserId();
			$holder_user_name=GetUserName($holder_user_id);
			if ($holder_user_id == NULL) {
				$holder_user_id = 'NULL';
				$holder_user_name = 'NULL';
			}
			//$target_user_name=GetUserName($target_user_id);  		
 			$sql = "SELECT $user_id user_id, '$user_name' user_name, g.gsession_id, g.map_id, g.createstamp gcreatestamp, g.startstamp gstartstamp, g.endstamp gendstamp, g.gstatus gstatus, g.gstate gstate, g.gturn gturn,
                                        '".G_GS_MAX_PLAYERS."' G_GS_MAX_PLAYERS,  '".G_GS_START_TIMEOUT."' G_GS_START_TIMEOUT, '".G_GS_MIN_PLAYERS."' G_GS_MIN_PLAYERS,
					c.name CREATOR_USER_NAME, '$holder_user_name' HOLDER_USER_NAME
					FROM m_gsession g 
					LEFT JOIN m_user c ON g.creator_user_id = c.user_id
					WHERE g.gsession_id = " . $this -> gsession_id;
			$msg = DbQuery( $sql, $msg);
		}
		//try replace for with user info
		if (strpos($msg, '%') != FALSE) {		
			$holder_user_id=$this->GetHolderUserId();
			$holder_user_name=GetUserName($holder_user_id);
			if ($holder_user_id == NULL) {
				$holder_user_id = 'NULL';
				$holder_user_name = 'NULL';
			}
			if ($user_id=='NULL') {
				$target_user_id=$holder_user_id;
				$target_user_name=$holder_user_name;
				 
			} else {
				$target_user_id=$user_id;
				$target_user_name=$user_name;
			}
			if ($target_user_id!='NULL') {
			//		(SELECT MAX( x.user_cash ) 
			//		 FROM m_gsession_user x
			//		 WHERE x.gsession_id =  55
			//		 )max_user_cash
			  $sql = "SELECT  $target_user_id user_id, '$target_user_name' user_name, g.gsession_id, g.map_id, g.createstamp gcreatestamp, g.startstamp gstartstamp, g.endstamp gendstamp, g.gstatus gstatus, g.gstate gstate, g.gturn gturn,
                                        '".G_GS_MAX_PLAYERS."' G_GS_MAX_PLAYERS,  '".G_GS_START_TIMEOUT."' G_GS_START_TIMEOUT, '".G_GS_MIN_PLAYERS."' G_GS_MIN_PLAYERS,
					" . $this -> GetActivePlayersCount() . " active_players,
					gu.`act_order`, gu.`user_cash`, gu.`last_dice1`, gu.`last_dice2`, gu.`debitor_stamp`,
					mf.field_id, mf.fcode, mf.name position_field_name,
					fg.fgroup_name,
					gfg.fgparam, 
					gmf.owner_user_id, gmf.fparam,
					own.name owner_user_name, 
					'" . $this -> GetUserProperty($user_id) . "'user_property 					
				FROM m_gsession g 
				LEFT JOIN m_gsession_user gu ON g.gsession_id = gu.gsession_id 
				LEFT JOIN m_cfg_map_field mf ON mf.field_id = gu.position_field_id
				LEFT JOIN m_gsession_map_fgroup gfg ON mf.fgroup_id = gfg.fgroup_id
					AND gfg.gsession_id =  g.gsession_id 
				LEFT JOIN m_cfg_map_fgroup fg ON fg.fgroup_id = mf.fgroup_id
				LEFT JOIN m_gsession_map_field gmf ON gmf.field_id = gu.position_field_id and gmf.gsession_id =  g.gsession_id
				LEFT JOIN m_user own ON gmf.owner_user_id = own.user_id
				WHERE g.gsession_id = " . $this -> gsession_id . "
					and gu.user_id= $user_id" ;
 			  $msg = DbQuery( $sql, $msg);
			}
		}
		if ($msg != '') {
			DbSQL("INSERT INTO `m_gsession_msg`(`user_id`, `gsession_id`, `msgtype`, `msg_text`) 
			VALUES ($addressee_user_id, " . $this -> gsession_id . ", $msg_type, ?)", array($msg));
		}
		return DbCompleteTrans();
	}

	//-----------------------------------------------
	// Holder methods
	//-----------------------------------------------
	function HolderDoAction($field_id, $act_event) {
		//check if it doesnt assigned
		//DbStartTrans();
		$user_id = $this -> GetHolderUserId();
		if ((isset($user_id))&&($user_id!='')) {
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
		$sql = "select $user_id user_id, '$user_name' user_name, " . $this -> map_id. " map_id, gu.user_cash, 
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
			//LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "fact_cond=$fact_cond");
			$fact_cond = DbQuery(str_replace('%fact_cond%', 't1.fact_cond', $sql), $fact_cond, "", false);
			//LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "fact_cond=$fact_cond");
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
				//LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "###gturn=" . $this -> gturn . " $act_event action on field=$field_id fparam=$fparam fparam_calc1=$fparam_calc1 fparam_calc2=$fparam_calc2 sql=$act_sql");
				//echo $sql;
				DbSQL($act_sql);
			}
			if (strpos($msg, '%') != FALSE) {
				//try to replace params
				$msg = DbQuery(str_replace('%fparam_calc1%', $fparam_calc1, str_replace('%fparam_calc2%', $fparam_calc2, str_replace('%pay_type%', $pay_type, str_replace('%exch_type%', $exch_type, $sql)))), $msg);
			}
			if (isset($msg) && ($msg != '')) {
				$this -> AddMesage($msg, G_GS_MSGTYPE_ACTMSG, $user_id);
			}
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "$act_event action on field=$field_id fparam=$fparam fparam_calc2=$fparam_calc2 fparam_calc1=$fparam_calc1 msg=$msg done");
		} else {
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "$act_event action on field=$field_id not started due condition: $fact_cond");
		}

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
				WHERE  map_id=" . $this -> map_id . " and  fcode IN (SELECT Ifnull(Min(fcode), (SELECT Min(fcode) 
                				                                    FROM   m_cfg_map_field  
                                				                    WHERE  map_id = " . $this -> map_id . ")) 
                     FROM   m_cfg_map_field 
                     WHERE  map_id=" . $this -> map_id . " and fcode > (SELECT fcode 
                                         FROM   m_cfg_map_field 
                                         WHERE  map_id=" . $this -> map_id . " and field_id = $fly_pos))");
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
				DbSQL("UPDATE m_gsession_user u1, m_gsession_user u2 
				SET u1.is_holder=false, u2.is_holder=true 
				WHERE u1.user_id=$user_id and u2.user_id=$next_user_id 
				and u1.gsession_id = " . $this -> gsession_id . "
				and u2.gsession_id = " . $this -> gsession_id );
/*				DbSQL("UPDATE `m_gsession_user` 
		 SET `is_holder`=false
         WHERE  gsession_id = " . $this -> gsession_id . " and user_id=$user_id");
				DbSQL("UPDATE `m_gsession_user` 
		 SET `is_holder`=true, has_penalty=false,  penalty_turn = NULL
         WHERE  gsession_id = " . $this -> gsession_id . " and user_id=$next_user_id");*/
				$this -> IncGTurn();
				$this -> MarkUpdatedUser($next_user_id);
				$this -> ManageDebtor($next_user_id);
			}
			LogGSession($this -> gsession_id, $user_id, G_LOG_LVL_DEBUG, "user turn end");
			$this -> MarkUpdatedByField($prev_pos);
			$this -> MarkUpdatedByField($next_pos);
			$this -> MarkUpdatedUser($user_id);
			$this -> ManageDebtor($user_id);
			gs_turn_unlock_updates($this -> gsession_id);
			//DbLockFree($lockname);
		}
		$res = DbCompleteTrans();
		//gs_turn_unlock_updates($this -> gsession_id);
		return $res;
	}

	/* *
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
	function GetChangedFieldListArray($user_id, $ceil_tpl, $ceil_user_tpl, $lastupdated = NULL, $ceil_user_tpl_marker = '%USERLIST%', $item_name_tpl='c%FCODE%',$encodechars = false, $rowdelimter = '') {
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
			$ceil= $tpl;
			if ($ceil_user_tpl !=NULL) { 
				$tpl_ulist = $this -> GetFieldUserInfo($field_id, $ceil_user_tpl);
				if ($this -> CanSellField($user_id, $field_id)) {
					$issellable = '';
				} else {
					$issellable = 'hidden';
				}
				$ceil = str_replace('%ISSELLABLE%', $issellable, str_replace($ceil_user_tpl_marker, $tpl_ulist, $tpl));
			}			
			$item_name = str_replace('%FCODE%', $fcode, $item_name_tpl);
			$arr[$item_name] = $ceil;
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
		           u.name owner_name, f.fauct_id auct_id, IF( IFNULL(f.fauct_id,0) >0 ,'onauction',NULL ) onauction,
			  gu.act_order owner_act_order
			FROM m_cfg_map_field cf 
			LEFT JOIN m_cfg_map_fgroup cfg ON cf.fgroup_id=cfg.fgroup_id   
			LEFT JOIN m_gsession_map_fgroup gfg ON cfg.fgroup_id = gfg.fgroup_id and gfg.gsession_id = " . $this -> gsession_id . ",
			m_gsession_map_field f
			LEFT OUTER JOIN m_user u ON f.owner_user_id = u.user_id
			LEFT OUTER JOIN m_gsession_user gu ON f.owner_user_id = gu.user_id and gu.gsession_id = " . $this -> gsession_id . " 
			". 
			//LEFT OUTER JOIN m_gsession_auction a ON a.gsession_id = " . $this -> gsession_id . " and f.field_id = a.field_id and a.auct_state='" . G_AU_AUCT_STATE_OPENED . "'
                  "WHERE f.gsession_id = " . $this -> gsession_id . " and f.field_id=$field_id 
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

	function & OpenFieldPublicAuction($holder_user_id, $field_id, $bid, $checkit = true) {
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