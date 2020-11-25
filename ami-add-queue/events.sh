#! /bin/bash

function addmember {
Q=$1
A=$2 

sleep .3

cat <<EOF
Action: QueueAdd
Queue: $Q
Interface: $A
Penalty: 0
Paused: 0
MemberName: $A


EOF
 
}  

function login {
U=$1
P=$2

sleep .3

cat << EOF
Action: Login
Username: $U
Secret: $P


EOF
}


function logoff {
sleep .3

cat << EOF
Action: Logoff


EOF
}


login qm 1234

addmember 999 SIP/123

logoff



