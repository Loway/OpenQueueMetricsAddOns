#!/bin/bash
 
# script for reporting errors per mail
# dsatta, 11/07/2008
# 
 
# variables
DATE=`date`
HOSTNAME=`hostname`
FROM="root@$HOSTNAME"
NOTIFY="admin@-----.com"
FILE="/var/log/qloader.log"
LASTFILE="$FILE.last"
BODY="$FILE.body"
 
# determine length of file
CURLENGTH=`cat $FILE | wc -l`
 
# create tempfile/set timestamp
touch $LASTFILE
 
# determine amount of lines of the last check; if none, reset the last length-counter
LASTLENGTH=`cat $LASTFILE`
if [ "$LASTLENGTH" == "" ]; then
  LASTLENGTH=0
fi
 
# if current length is smaller than the last length, we assume a logrotate and reset the last length-counter
if [ $CURLENGTH -lt $LASTLENGTH ]; then
  echo "The last file was smaller ! We're assuming a logrotate and reset the counter back to 0"
  LASTLENGTH = 0
fi
 
# create the body of the mail
 
# output current and last length of the files
echo "Current length of $FILE is $CURLENGTH" > $BODY
echo "Last length of $FILE is $LASTLENGTH" >> $BODY
echo "" >> $BODY
 
# calculate the difference
TAIL=$(($CURLENGTH-$LASTLENGTH))
 
# output the number of lines to tail/check for errors
echo "Number of lines to check for errors are $TAIL" >> $BODY
echo "" >> $BODY
 
# output general information
echo "From: $FROM" >> $BODY
echo "To: $NOTIFY" >> $BODY
echo "" >> $BODY
 
echo "$TAIL new entries found in $FILE on $HOSTNAME at $DATE"  >> $BODY
echo "" >> $BODY
 
# output the tail/actual log data
tail -n $TAIL $FILE >> $BODY
echo "" >> $BODY
 
# Filter for the string ERROR; only send a mail in case of a positive; take content from $BODY
ERR=`cat $BODY|grep ERROR`
if [ "$ERR" != "" ]; then
    mail -s "ALERT: $TAIL new entries found in $FILE on $HOSTNAME at $DATE" $NOTIFY < $BODY
    echo "An email got sent to $NOTIFY"
 
    # output new length into $LASTFILE (needed to let the next check determine the right length)
    echo $CURLENGTH > $LASTFILE
else
    echo "No Errors in $FILE"
fi
