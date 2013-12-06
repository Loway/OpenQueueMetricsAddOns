#! /bin/bash
#
# This script removes all agents from a given Asterisk
# queue.
#
#

if [[ $# -eq 0 ]] ; then
    echo "Usage: $0 queuename"
    exit 1
fi

qName=$1

echo "Removing any dynamic agent from queue $qName"

asterisk -rx "queue show $qName" | awk -v q=$qName '
	/dynamic/  {
		agent = $1;
		cmd =  "queue remove member " agent " from " q
		printf " - " agent ": " ;
		system( "asterisk -rx \"" cmd "\"" );
}'



