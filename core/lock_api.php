<?php 

function DbLock($lockname, $timeout = 5) {
	return DbGetValue("SELECT GET_LOCK('m$" . $lockname . "', $timeout); ") == 1;
}

function DbLockFree($lockname) {
	return DbGetValue("SELECT IS_FREE_LOCK('m$" . $lockname . "'); ") == 1;
}

function DbIsLocked($lockname) {
	return !DbLockFree($lockname);
}

function DbUnlock($lockname) {
	return DbGetValue("SELECT  RELEASE_LOCK('m$" . $lockname . "'); ") == 1;
}

function gsession_lock_updates($gsession_id) {
	return DbLock("gs$gsession_id");
}

function gsession_unlock_updates($gsession_id) {
	return DbUnlock("gs$gsession_id");
}

function auction_lock_updates($auct_id) {
	return DbLock("lock_auct_id_$auct_id");
}

function auction_isnotlocked($auct_id) {
	return DbLockFree("lock_auct_id_$auct_id");
}

function auction_unlock_updates($auct_id) {
	return DbUnlock("lock_auct_id_$auct_id");
}

function deal_lock_updates($deal_id) {
	return DbLock("lock_deal_id_$deal_id");
}

function deal_isnotlocked($deal_id) {
	return DbLockFree("lock_deal_id_$deal_id");
}

function deal_unlock_updates($deal_id) {
	return DbUnlock("lock_deal_id_$deal_id");
}

function gs_turn_unlock_updates($gsession_id) {
	return DbUnlock("lock_turn_gsession_id_$gsession_id");
}

function gs_turn_lock_updates($gsession_id) {
	return DbLock("lock_turn_gsession_id_$gsession_id");
}

function gs_turn_isnotlocked($gsession_id) {
	return DbLockFree("lock_turn_gsession_id_$gsession_id");
}
?>