<?php 
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
		$this -> AfterStart();
		$this -> MarkUpdated();
		$this -> AddMesage(GetCfgMessage('MSG_INFO_DL_OPENED'), G_GS_MSGTYPE_DLMSG);
		//load
		return $this -> Load($this -> deal_id);
	}

	function AfterStart() {
		$sql="SELECT `field_id`  
			FROM `m_gsession_deal_list` dl
		        WHERE dl.deal_id = " . $this -> deal_id ;
		$rs = DbGetValueSet($sql);
		foreach ($rs as $row) {
			$field_id = $row['field_id'];
			$this -> gsession -> OnDealStart($field_id, $this -> deal_id);
		}
		return true;
	}
	function AfterFinish() {
		$sql="SELECT `field_id`  
			FROM `m_gsession_deal_list` dl
		        WHERE dl.deal_id = " . $this -> deal_id ;
		$rs = DbGetValueSet($sql);
		foreach ($rs as $row) {
			$field_id = $row['field_id'];
			$this -> gsession -> OnDealFinish($field_id, $this -> deal_id);
		}
		return true;
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
			$this -> AfterFinish();
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
			$this -> AfterFinish();
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
			$this -> AfterFinish();
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
?>