<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once ('core/core.php');
ConnectDB();
$gsession = GetCurrentGSession();
$gsession_id = $gsession->gsession_id;
$current_user_id = GetCurrentUserId();
$current_user_name = GetUserName($current_user_id);
?>

<div id="dialog_deal_start" class="dialog" title="Create offer">
	<p>Create offer</p>
    <p class="validateTips">All form fields are required.</p>
    <form>
	<fieldset>
<div class="ui-widget">
	<label>Select user: </label>
	<select id="combobox">
		<option value="">Select one...</option>
		<?php
			$list_tpl ='<option value="%USER_ID%">%NAME%</option>';
			echo $gsession -> GetUserList($list_tpl);
		?>
	</select>
</div>
<div id="owner_property_set">
<?php 
	$proplist_tpl='<input type="checkbox" id="chk_user_id%USER_ID%_field_id%FIELD_ID%" /><label for="chk_user_id%USER_ID%_field_id%FIELD_ID%">%FIELD_NAME%</label></br>';
	echo $gsession -> GetUserPropertyList($current_user_id, $proplist_tpl);
?>
</div>
		<!--<button id="toggle">Show underlying select</button>-->
		<label for="name">Name</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" />
		<label for="email">Email</label>
		<input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all" />
		<label for="password">Password</label>
		<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	</form>
</div>
