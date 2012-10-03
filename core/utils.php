<?php

function StartTimer($timername) {
	global $G_TIMERS;
	if (isset($G_TIMERS)) {
		$G_TIMERS = array();
	}
	#---------
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = $mtime[1] + $mtime[0];
	$tstart = $mtime;
	#---------
	$G_TIMERS[$timername] = $tstart;
}

function GetTimer($timername) {
	global $G_TIMERS;
	if (isset($G_TIMERS[$timername])) {
		$tstart = $G_TIMERS[$timername];

		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$tend = $mtime;
		//Calculate the difference
		$totaltime = $tend - $tstart;
		return $totaltime;
	}
}

function random($min, $max) {
	// md5() generates a hexadecimal number, so we must convert it into base 10
	$rand = base_convert(md5(microtime()), 16, 10);
	// the modulus operator doesn't work with great numbers, so we have to cut the number
	$rand = substr($rand, 10, 6);
	$diff = $max - $min + 1;
	return ($rand % $diff) + $min;
}

?>