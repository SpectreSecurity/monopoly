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
		<div id="chats"></div>
			<form onsubmit="javascript:sendMsg();return false;">
				<input type="text" name="text" id="msg" autocomplete="off" />
			</form>
		</div>
<script>
var username = '<?php echo $current_user_name; ?>';

function sendMsg(){

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
