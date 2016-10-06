#!/bin/bash

# Remember to make the script executable and to add
# */5 * * * * /path/to/observe_qm_mem.sh
# to your crontab

QMURL=http://127.0.0.1:8080/queuemetrics

LOGDIR=/usr/local/queuemetrics/tomcat/logs
TODAY=`date '+%Y.%m.%d'`
WGET=`which wget`

/etc/init.d/qm-tomcat6 threaddump >> $LOGDIR/thread.$TODAY
$WGET -O- $QMURL/sysup.jsp >> $LOGDIR/mem.$TODAY
