#! /bin/bash

#
# Log a single response to a QA Form.
#
# Call this script as:
#    pushQM.sh 123456.1234 q1 100
#
# This will log the value 100 to uniqueid 123456.1234 on queue 'q1'
# on a call started no earlier than one hour before the call.
# 
# - The form name has to be specified within the script
# - The form item has to be specified
# - When logging YES/NO elements, YES=100 and NO=0
#

QM=http://127.0.0.1:8080/queuemetrics
USER=robot:robot
FORM=SATID
ITEM=SAT

UNIQUEID=$1
QUEUE=$2
VALUE=$3
NOW=$(date -d "today - 60 minutes" +"%Y-%m-%d.%T")

OPTS="margin=7200&queues=$QUEUE&calldate=$NOW"
URL="$QM/QmQaGrading/jsonStatsApi.do?$OPTS&form=$FORM&id=$UNIQUEID&item_$ITEM=$VALUE"

eval curl --user $USER -i -H \"Content-type: application/json\" -X GET \"$URL\"


