#!/usr/bin/env php
<?php
require_once ('core.php');
echo "Connecting\n";
ConnectDB();
//raise_exception('test');
echo "Connected\n";
echo "WatchDog begin\n";
WatchDog();
echo "WatchDog end\n";
//echo "1";
//* * * * * /foo/bar/your_script
//* * * * * sleep 15; /foo/bar/your_script
//* * * * * sleep 30; /foo/bar/your_script
//* * * * * sleep 45; /foo/bar/your_script
?>