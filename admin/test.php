<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once ('core/core.php');
echo "1" . '<br>';
ConnectDB();
CleanALL();
//DoQuery("SELECT * FROM  `m_cfg_faction` LIMIT 0 , 30");
//CreateGSession (1,1);
$gsession = new GSession();
if ($gsession -> Create(1, 1)) {
	echo 'Create done' . '<br>';

} else {
	echo 'Create fail' . '<br>';
}
echo "2" . '<br>';
if ($gsession -> AssignUser(1)) {
	echo 'AssignUser done' . '<br>';

} else {
	echo 'AssignUser fail' . '<br>';
}
echo "3" . '<br>';
if ($gsession -> AssignUser(2)) {
	echo 'AssignUser done' . '<br>';

} else {
	echo 'AssignUser fail' . '<br>';
}
echo "4" . '<br>';
if ($gsession -> AssignUser(3)) {
	echo 'AssignUser done' . '<br>';

} else {
	echo 'AssignUser fail' . '<br>';
}
echo "5" . '<br>';
if ($gsession -> Start(1)) {
	echo 'start done' . '<br>';

} else {
	echo 'start fail' . '<br>';
}

echo "6" . '<br>';
for ($i = 1; $i <= 15; $i++) {
	if ($gsession -> MakeTurnUser($gsession -> GetHolderUserId())) {
		echo "turn $i done" . '<br>';

	} else {
		echo "turn $i fail" . '<br>';
	}

}
?>