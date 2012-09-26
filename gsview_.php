<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>GSView </title>
		<script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
	</head>
	<body>
		<!-- widget begin-->
		<script type="text/javascript">
			//refresh time
			var rtime = 3000;
			//lastupdated
			var lastupdated = '';

			function AutoRefresh() {
				$.getJSON('gsjson.php?user_id=1&lastupdated=' + lastupdated, function(data) {

					$.each(data, function(key, val) {
						if (key == 'lastupdated') {
							lastupdated = val;
						}
						var txt = new String(key);
						if (key == "refreshtimeout") {
							if (rtime != val) {
								rtime = val;
								clearInterval(myTimer);
								myTimer = setInterval(function() {
									AutoRefresh();
								}, rtime);
							}
						} else if (txt.substr(0, 5) == "gs_id") {
							if ($("#" + key).length) {
								$("#" + key).replaceWith(val);
							} else {
								$("#gsbox").prepend(val);
							}
						} else {
							$("#" + key).html(val);
						}
					});

				});

			}

			var myTimer = setInterval(function() {
				AutoRefresh();
			}, rtime);

		</script>
		<div id="gsbox"></div>
		<!-- widget end-->
	</body>
</html>
