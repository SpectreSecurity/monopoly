<!doctype html>
<html lang="en" dir="ltr" class="no-js">
	<head>
		<meta charset="utf-8" />
		<title>HTML5 &amp; CSS3 </title>

		<link type="text/css" href="css/custom-theme/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.23.custom.min.js"></script>
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>

		<link rel="stylesheet" href="mon2.css" type="text/css" media="screen" />

	</head>
	<body>
		<div id="main">
		<div id="board" style="float: left;">
			<?php
			include ('board.php');
		?>
		</div>
		<div id="chat" style="float: left;">

	<script>
	$(function() {
		$( "#draggablebox" ).draggable();
		$( "#draggablebox" ).resizable({
		   stop: function(event, ui) { 
		   	$("#chats").height(ui.size.height-53);
		   }
   		});
	});
	</script>

	<div id="draggablebox" class="ui-widget-content">
	<h3 class="ui-widget-header">Gsession chat</h3>
<div id="chats_box" style="height:100%;">
<!--<div id="chats_innerbox" style="height:90%;background-color: #00855c;">-->
<div id="chats"></div>
<!--</div>-->
<!--<div id="chat_edit" style="height:10%;">-->
<form onsubmit="javascript:sendMsg();return false;">
	<input type="text" name="text" id="msg" autocomplete="off" />
</form>
<!--</div>-->
</div>
<script>
var username = '<?php echo $current_user_name; ?>';

function sendMsg(){
	if(!username)
	{
		username = prompt("Hey there, good looking stranger!  What's your name?", "");
		if(!username)
		{
			return;
		}
	}

	var msg = document.getElementById("msg").value;
	if(!msg)
	{
		return;
	}
	
	document.getElementById("chats").innerHTML+=strip('<div class="msgln"><b>'+username+'</b>: '+msg+'<br/></div>');
	$("#chats").animate({ scrollTop: 2000 }, 'normal');

	$.get('chat.php?msg='+msg+'&user=<?php echo $current_user_name; ?>&room=<?php echo $current_chat_room; ?>', function(data)
	{
		document.getElementById("msg").value = '';
	});
}

var old = '';
var source = new EventSource('chat.php?room=<?php echo $current_chat_room; ?>');

source.onmessage = function(e)
{
	if(old!=e.data){
		document.getElementById("chats").innerHTML='<span>'+e.data+'</span>';
		old = e.data;
	}
};

function strip(html)
{
	var tmp = document.createElement("DIV");
	tmp.innerHTML = html;
	return tmp.textContent||tmp.innerText;
}
</script>
	</div>
		</div>
		</div>
		<a href="#" id="colophon" />Menu</a>

	</body>
</html>