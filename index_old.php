<?php
 error_reporting(E_ALL);
 ini_set('display_errors', '1');
?>
<!doctype html>
<html lang="en" dir="ltr" class="no-js">
<head>
	<meta charset="utf-8" />
	<title>HTML5 &amp; CSS3 </title>

	<link rel="stylesheet" href="mon2.css" type="text/css" media="screen" />
	<script type="text/javascript" src="jquery-1.7.2.min.js" ></script>

</head>
<body>
<script type="text/javascript">

var g_field=1;

function DoAction(ActionId) {
       $.ajaxSetup({cache: false});
	$action_html=$('#'+ActionId).html();
	$('#pzc'+g_field).html('<div class="pl red"></div>');
	$('#console').html(g_field);
	g_field++;
if (g_field>32) {
	g_field=1;
}	
};
</script>
<?php
  $ceil_color_ar=array('purple','teal');
  $ceil_color_ind=1;
  $ceil_tmpl='
        <section id="c%i%" class="%color% right">
		<div style="height: 70%;">
		C%i%
		</div>
		<div id="pzc%i%">
		<div class="pl red"></div>
		<div class="pl yellow"></div>
		<div class="pl green"></div>
		<div class="pl blue"></div>
		<div class="pl white"></div>
		</div>
	</section>';
  function GetNextColor() {
    global $ceil_color_ar;
		$color=current($ceil_color_ar);
		if (next($ceil_color_ar)==FALSE) {
			reset($ceil_color_ar);
			//$color=next($ceil_color_ar);
		}
	return $color; 
  }
?>

<div id="board">
	<div style="clear: both;">
<?php
  	for ($i = 1; $i <= 10; $i++) {
    		echo str_replace('%color%',GetNextColor(),str_replace('%i%',$i,$ceil_tmpl));
	}
 
?>
	</div>
	<div style="width:5em;float: left;">
<?php
        GetNextColor();
  	for ($i = 32; $i >= 27; $i--) {
    		echo str_replace('%color%',GetNextColor(),str_replace('%i%',$i,$ceil_tmpl));
	}
 
?>
	</div>
	<div style="float: left;">
	<section onclick="DoAction('pzc1')" id="con" class="player1 teal right">
		<h1>CON</h1>
		<div id=console>Console</div>
	</section>
	</div>
	<div style="width:5em;float: left;">
<?php
        GetNextColor();
  	for ($i = 11; $i <= 16; $i++) {
    		echo str_replace('%color%',GetNextColor(),str_replace('%i%',$i,$ceil_tmpl));
	}
 
?>
	</div>
	<div style="clear: both;">
<?php
		
        GetNextColor();
	for ($i = 26; $i >= 17; $i--) {
    		echo str_replace('%color%',GetNextColor(),str_replace('%i%',$i,$ceil_tmpl));
	}
 
?>
	</div>
</div>
<a href="#" id="colophon" />Menu</a>
</body>
</html>