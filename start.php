
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once ('core/core.php');
echo "1" . '<br>';
ConnectDB();
//CleanALL();
$gsession = new GSession();
if ($gsession -> Create(1, 1)) {
	echo 'Create done id= ' .$gsession -> gsession_id. '<br>';

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
//$gsession->UserAssignField(2,2);
$gsession->UserAssignField(3,3);
//$gsession->UserAssignField(1,4);
//$gsession->UserAssignField(2,6);
$gsession->UserAssignField(1,7);
//$gsession->UserAssignField(3,8);
$gsession->UserAssignField(3,11);
$gsession->UserAssignField(1,12);
$gsession->UserAssignField(1,14);
$gsession->UserAssignField(3,15);
$gsession->UserAssignField(3,16);
$gsession->UserAssignField(2,18);
//$gsession->UserAssignField(2,19);
$gsession->UserAssignField(2,20);
$gsession->UserAssignField(1,22);
//$gsession->UserAssignField(1,23);
$gsession->UserAssignField(3,24);
//$gsession->UserAssignField(3,25);
$gsession->UserAssignField(1,27);
//$gsession->UserAssignField(2,28);
$gsession->UserAssignField(2,29);
$gsession->UserAssignField(1,31);
$gsession->UserAssignField(3,32);
?>