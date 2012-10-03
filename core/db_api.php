<?php
include_once ('adodb5/adodb.inc.php');
include_once ('adodb5/adodb-exceptions.inc.php');

function ConnectDB() {
	//throw new Exception('error');
	global $db;
	try {
		$db = ADONewConnection("mysqlt");
		// create a connection
		//$db -> PConnect('wenter', 'monopoly', 'xsw2zaq1', 'monopoly');
		$db -> PConnect(G_DB_HOST, G_DB_USER, G_DB_PASSWORD, G_DB_NAME);
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

