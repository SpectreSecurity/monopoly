<!doctype html>
<html lang="en" dir="ltr" class="no-js">
	<head>
		<meta charset="utf-8" />
		<title>HTML5 &amp; CSS3 </title>

		<link type="text/css" href="css/custom-theme/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.23.custom.min.js"></script>
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>

		<link rel="stylesheet" href="css/mon2.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="css/dice.css" type="text/css" media="screen" />

	</head>
	<body>
	<div id="main">
		<div id="board" style="float: left;">
		<?php
			include ('ppage_board.php');
		?>
		</div>
		<div id="chat" style="float: left;">
		<?php
			include ('ppage_chat.php');
		?>
		</div>
	</div>
	<a href="#" id="colophon" />Menu</a>
        </body>
</html>