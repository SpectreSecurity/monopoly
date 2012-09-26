<?php
/**
 * Set additional info parameters to be used when displaying the next error
 * This function takes a variable number of parameters
 *
 * When writing internationalized error strings, note that you can change the
 *  order of parameters in the string.  See the PHP manual page for the
 *  sprintf() function for more details.
 * @access public
 * @return null
 */
function error_parameters() {
	global $g_error_parameters;

	$g_error_parameters = func_get_args();
}

function LogCritical($msg, $type) {
	$filename = str_replace('%date%', date('d-m-Y', time()), G_CRLOG_FILE_TPL);
	$fp = fopen(G_CRLOG_DIR . $type . $filename, 'a');
	//echo G_CRLOG_DIR . $type . $filename;
	fwrite($fp, date('d/m/Y H:i:s', time()) .' '. $msg."\n");
	fclose($fp);
}

function raise_exception($msg) {
	trigger_error($msg);
}

function exception_handler($exception) {

	ob_start();
	echo date('d/m/Y H:i:s', time()) . '\n';
	echo $exception -> getMessage();
	echo $exception -> getTraceAsString();
	//print_r($GLOBALS);
	//print_r($exception);
	file_put_contents(G_CRLOG_DIR . 'exceptions.txt', ob_get_clean() . "\n", FILE_APPEND);
}

set_exception_handler('exception_handler');
?>