<?php 
 if (isset($_GET["user_id"])){
  $user_id= $_GET["user_id"];
  setcookie('last_user_id',$user_id,time() + (86400 * 7)); // 86400 = 1 day
  echo "Welcome user_id=$user_id";
 } else {
  echo 'Hello '.($_COOKIE['last_user_id']!='' ? $_COOKIE['last_user_id'] : 'Guest'); // Hello 
 }
?>