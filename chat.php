<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

$folder="chats/";
function sendMsg($id, $msg) {
  echo "id: $id" . PHP_EOL;
  echo "data: $msg" . PHP_EOL;
//  echo "retry: 1000" . PHP_EOL;
  echo PHP_EOL;
  ob_flush();
  flush();
}


if(isset($_GET['user']) && isset($_GET['msg'])){
	$fp = fopen($folder.$_GET['room']."log.txt", 'a');  
    fwrite($fp, "<div class='msgln'><b>".strip_tags($_GET['user'])."</b>: ".strip_tags(($_GET['msg']))."<br></div>");  
    fclose($fp);  
}

if(file_exists($folder.$_GET['room']."log.txt") && filesize($folder.$_GET['room']."log.txt") > 0){  
    $handle = fopen($folder.$_GET['room']."log.txt", "r");  
    $contents = fread($handle, filesize($folder.$_GET['room']."log.txt"));  
    fclose($handle);

//deleting file when it get bigger
	if(filesize($folder.$_GET['room']."log.txt")>1100){
		@unlink($folder.$_GET['room']."log.txt");
	}
}  

sendMsg(time(),$contents);

?>