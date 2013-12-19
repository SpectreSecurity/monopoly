<?php

/*function Close_timeouted_auctions_by_gs_id($gsession_id) {
 if ($gsession_id != NULL) {
 $rs = DbGetValueSet("select auct_id from m_gsession_auction a where a.gsession_id=$gsession_id and auct_state='" . G_AU_AUCT_STATE_OPENED . "' and TIMESTAMPDIFF(MINUTE,auct_laststamp,CURRENT_TIMESTAMP)>" . G_AU_STEP_TIMEOUT);
 foreach ($rs as $row) {
 $auct_id = $row['auct_id'];
 if (auction_isnotlocked($auct_id)) {
 auction_lock_updates($auct_id);
 $auction = new GAuction($gsession_id);
 $auction -> Load($auct_id);
 $auction -> Close();
 //RefreshUserMonopolies($user_id)
 auction_unlock_updates($auct_id);
 }
 }
 }

 }
 */
function Close_timeouted_auctions(&$gsession) {
	LogCritical('Close_timeouted_auctions begin', 'wd');
	if ($gsession != NULL) {
		$gsession_id = $gsession -> gsession_id;
		$rs = DbGetValueSet("select auct_id from m_gsession_auction a 
	               	where a.gsession_id=$gsession_id and auct_state='" . G_AU_AUCT_STATE_OPENED . "' 
	               	  and TIMESTAMPDIFF(MINUTE,auct_laststamp,CURRENT_TIMESTAMP)>" . G_AU_STEP_TIMEOUT . "
	               	  order by auct_id");
		foreach ($rs as $row) {
			$auct_id = $row['auct_id'];
			//DbStartTrans();
			if ((auction_isnotlocked($auct_id)) && (auction_lock_updates($auct_id))) {
				LogCritical($auct_id, 'wd');
				try {
					$auction = &$gsession -> getGAuction($auct_id);
					$auction -> Close();
				} catch(Exception $ex) {
			        echo 'Caught exception: '.  $ex->getMessage(). "\n";
					LogCritical('Caught exception: '.$ex->getMessage(), 'wd');
					LogCritical($ex -> getTraceAsString(),'wd');
				}
				auction_unlock_updates($auct_id);
			}
			//DbCompleteTrans();
		}
	}
	LogCritical('Close_timeouted_auctions end', 'wd');
}

function Close_timeouted_deals(&$gsession) {
	if ($gsession != NULL) {
		$gsession_id = $gsession -> gsession_id;
		$rs = DbGetValueSet("select deal_id from m_gsession_deal a where a.gsession_id=$gsession_id and deal_status='" . G_DL_DEAL_STATUS_ACTIVE . "' and TIMESTAMPDIFF(MINUTE,last_changed,CURRENT_TIMESTAMP)>" . G_DL_TIMEOUT);
		foreach ($rs as $row) {
			$deal_id = $row['deal_id'];
			if ((deal_isnotlocked($deal_id)) && (deal_lock_updates($deal_id))) {
				$deal = &$gsession -> getGDeal($deal_id);
				$deal -> Terminate();
				deal_unlock_updates($deal_id);
			}
		}
	}

}
function GsessionManageStarted($gsession_id) {
	LogCritical('GsessionManageStarted '.$gsession_id, 'wd');
	$gsession = new GSession();
	$gsession -> Load($gsession_id);
	Close_timeouted_auctions($gsession);
	Close_timeouted_deals($gsession);
	$gsession -> ManageDebtors();
	if ($gsession -> CanFinish()) {
		$gsession -> Finish();
		LogCritical('Finished '.$gsession_id, 'wd');
	}
}

function GsessionManageCreated($gsession_id) {
	LogCritical('GsessionManageCreated '.$gsession_id, 'wd');
	$gsession = new GSession();
	$gsession -> Load($gsession_id);
	if ($gsession != NULL) {
		$gsession_id = $gsession -> gsession_id;
		$timeout = DbGetValue("select TIMESTAMPDIFF(MINUTE,createstamp,CURRENT_TIMESTAMP) start_timeout from m_gsession gs where gsession_id=$gsession_id");
		//LogCritical('timeout'.$timeout, 'wd');
		$usercnt= $gsession-> GetActivePlayersCount();
		if ((($timeout>=G_GS_START_TIMEOUT)||($usercnt==G_GS_MAX_PLAYERS))&&($gsession->CanStart())) {
			$gsession->Start();
		} else if ($timeout>=G_GS_START_TIMEOUT) {
			$gsession->Terminate();
		}
	}

}

function WatchDogJobs() {
	try {
		$rs = DbGetValueSet("select gsession_id from m_gsession gs where gstate=" . G_GS_GSTATE_STARTED);
		foreach ($rs as $row) {
			$gsession_id = $row['gsession_id'];
            GsessionManageStarted($gsession_id);
		}
	} catch(Exception $ex) {
        echo 'Caught exception: ',  $ex->getMessage(), "\n";
	}
	try {
		$rs = DbGetValueSet("select gsession_id from m_gsession gs where gstate=" . G_GS_GSTATE_CREATED);
		foreach ($rs as $row) {
			$gsession_id = $row['gsession_id'];
	        GsessionManageCreated($gsession_id);
		}
	} catch(Exception $ex) {
        echo 'Caught exception: ',  $ex->getMessage(), "\n";
	}
}

function WatchDog() {
	if (DbLockFree("watchdog")) {
		if (DbLock("watchdog", 1)) {
			$watchdog_timeago = DbGetValue("select TIMESTAMPDIFF(SECOND,watch_stamp,CURRENT_TIMESTAMP) from `m_watchdog` order by w_id desc limit 0,1 ");
			if (($watchdog_timeago==NULL)||($watchdog_timeago > G_WATCHDOG_PERIOD)) {
				DbStartTrans();
				$w_id = DbINSERT("INSERT INTO  `m_watchdog` (`wstatus`, wstart_stamp ) VALUES ( 'inprogress', CURRENT_TIMESTAMP)");
				DbCompleteTrans();
				DbUnlock("watchdog");
				StartTimer("watchdog");
				WatchDogJobs();
				$timer = GetTimer("watchdog");
				DbStartTrans();
				DbSQL("Update `m_watchdog` set `wstatus`= 'finished', watch_stamp = CURRENT_TIMESTAMP, wend_stamp = CURRENT_TIMESTAMP, wspend=$timer  where w_id=$w_id");
				DbCompleteTrans();
			}

		}
	}
}
?>