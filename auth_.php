<?php
require_once ('core/core.php');
echo "session_name=".session_name()."</br>";
echo "session </br>";
print_r($_SESSION);
echo "</br>request </br>";
print_r($_REQUEST);
echo "</br>cookie </br>";
print_r($_COOKIE);
echo "<hr>";

if (isset($_POST['auth_name'])) {
  ConnectDB();
  $name=mysql_real_escape_string($_POST['auth_name']);
  $pass=mysql_real_escape_string($_POST['auth_pass']);
  $query = "SELECT user_id FROM m_user WHERE name='$name' AND passwd='$pass'";
  $res = DbGetValue($query);
  echo "UserId=".$res;
  if ($res) {
    session_start();
    $_SESSION['user_id'] = $res;
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['gs_id'] = 1;
    print_r($_SESSION);
  }
  header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
  exit;
}
if (isset($_GET['action']) AND $_GET['action']=="logout") {
  session_start();
  session_destroy();
  header("Location: http://".$_SERVER['HTTP_HOST']."/");
  exit;
}
if (isset($_REQUEST[session_name()])) session_start();
if (isset($_COOKIE[session_name()])) session_start();
echo "</br>session </br>";
print_r($_SESSION);
echo "</br>request </br>";
print_r($_REQUEST);
echo "<hr>";
if (isset($_SESSION['user_id']) AND $_SESSION['ip'] == $_SERVER['REMOTE_ADDR']) {
echo "</br>gs_id=".$_SESSION['gs_id'];
return;
}
else {
?>
<form method="POST">
<input type="text" name="auth_name"><br>
<input type="password" name="auth_pass"><br>
<input type="submit"><br>
</form>
<?php
}
exit;
?>