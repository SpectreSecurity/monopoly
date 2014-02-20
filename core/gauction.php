<?php 
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
		$this -> gsession -> OnAuctionCreate($this -> field_id, $this -> auct_id);

		$msg_code = $this -> auct_type == G_AU_AUCT_TYPE_ATTACHED ? 'MSG_INFO_AU_ATTACHED_OPENED' : 'MSG_INFO_AU_PUBLIC_OPENED';
		$this -> AddMesage(GetCfgMessage($msg_code), G_GS_MSGTYPE_AUMSG);
		$this -> CalcTimeLeft();
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
			LogCritical('Caught exception: ' . $ex -> getMessage(), 'mu');
			LogCritical($ex -> getTraceAsString(), 'mu');
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

	function CalcTimeLeft() {
		$left = DbGetValue("select " . G_AU_STEP_TIMEOUT . " - TIMESTAMPDIFF(MINUTE,auct_laststamp,CURRENT_TIMESTAMP) from m_gsession_auction where auct_id=" . $this -> auct_id . " ");
	        if ($left<0) {
			$left=0;
		}
		DbSQL("UPDATE `m_gsession_auction` 
		 SET `auct_time_left`=$left , last_changed=current_timestamp 
         WHERE  auct_id=" . $this -> auct_id);
		//$this -> MarkUpdated();

		return $left;
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

	function CanUserJoin($user_id) {
		$res = false;
		if (($this -> auct_type == G_AU_AUCT_TYPE_PUBLIC) && ($this -> holder_user_id != $user_id) && ($this -> IsOpened()) && ($this -> GetUserState($user_id) == NULL)) {
			$res = true;
		}
		return $res;
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
		$res = DbCompleteTrans();
		if ($res) {
			$this -> gsession -> OnAuctionClose($this -> field_id, $this -> auct_id);
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
		$res = DbCompleteTrans();
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
?>