#!/bin/bash
LOGFILE=/var/www/html/mon/core/logs/callwd_`date +%F`.log
cd /var/www/html/mon/core/
echo `date +%H:%M:%S` : Starting WatchDog >> $LOGFILE
./rwatchdog.php >> $LOGFILE
echo `date +%H:%M:%S` : Done  >> $LOGFILE
