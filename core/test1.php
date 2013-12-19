#!/usr/bin/env php
<?php
require_once ('core.php');
echo "Connecting\n";
ConnectDB();
$lock='a';
echo "Connected\n";
echo "begin\n";
if (DbIsLocked($lock)) {
echo "IsLocked!\n";
}
if (DbLock($lock)) {;
echo "Locked. Sleeping...\n";
sleep(10);
echo "Wakeup. Unlock\n";
DbUnLock($lock);
echo "Unlocked\n";
}
echo "end\n";
?>