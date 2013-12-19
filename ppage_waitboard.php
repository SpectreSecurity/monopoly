<?php
//require_once ('auth.php');
//require_once ('core/core.php');
//ConnectDB();
//exit;
//WatchDog();
//$gsession = GetCurrentGSession();
//$gsession_id = $gsession->gsession_id;
//$current_user_id = GetCurrentUserId();
//$current_user_name = GetUserName($current_user_id);
//$current_chat_room = "gs_$gsession_id";
//$bid_delta = 10;

?>

<script type="text/javascript">
	//refresh time
	var rtime=3000;
	//lastupdated
	var lastupdated='';
	var gturn=0;


	function AutoRefresh(){
		$.getJSON('gswaitjson.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&lastupdated='+lastupdated, function(data) {	
				$.each(data, function(key, val) {
					if (key=='go') {window.location.href="/mon/?action=gsplay&gs_id=<?php echo $gsession_id; ?>";}
					if (key=='back') {history.back();}
					if (key=='lastupdated') {lastupdated = val;}
			        var txt = new String(key);
			        if (key=="refreshtimeout") {
			        	rtime=val;
					} else if (txt.substr(0,6)=="msg_id") {
						if ($("#"+key).length) {
							$("#"+key).replaceWith(val);
						} else {
							$("#gmsgbox").prepend(val);
						}
					} else {
						$("#"+key).html(val);
					}
				});
		});
	
	}


	var t = setInterval(function(){
				AutoRefresh();
	},rtime);

	AutoRefresh();
</script>

<!--
<div id="dialog_loading" class="dialog" title="Loading">
	<p>Loading</p>
	<div id="progressbar"></div>
</div>
-->
	<div id="lastupdated"></div>
				<div id="userlist" style="width: 30%;height:100%" onclick="SwitchBot()">
					List of users:</br>
					<table>
						<?php
						$list_tpl = "
						<tr>
							<td><div id=uc%ACT_ORDER% class='pl us_c%ACT_ORDER%'></td><td class='us_c%IS_HOLDER%'><b>%NAME%</b></td><td>
				</div> %USER_CASH%</td></tr>";
						echo $gsession -> GetUserList($list_tpl);
				?>
				</table>
			    </div>
	<div id=gmsgbox class="gmsgbox" style="width: 100%; height:200px;" >
			<?php //echo "Welcome " . $current_user_name; ?>
	</div>
