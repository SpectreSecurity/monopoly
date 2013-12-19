
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once ('../core/core.php');
echo "1" . '<br>';
ConnectDB();
//CleanALL();
$gsession = new GSession();
$gsession = new GSession();
if ($gsession -> Create(1, 1)) {
	echo 'Create done id= ' .$gsession -> gsession_id. '<br>';

} else {
	echo 'Create fail' . '<br>';
}
echo "2" . '<br>';
if ($gsession -> AssignUser(2)) {
	echo 'AssignUser done' . '<br>';

} else {
	echo 'AssignUser fail' . '<br>';
}
echo "3" . '<br>';
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