# Adding agents to Asterisk over AMI

A simple bash script that you can run like:


	nc 127.0.0.1 5038 -e ./events.sh

This does not handle errors or anything. Requires netcat (`yum install nc`)


## USAGE

Edit the script 'events.sh', by the bottom, where you have:


	login qm 1234

	addmember 999 SIP/123

	logoff

You can have as many addmember rows as you need. 


You can run it to see what it prints.


Then run:

	nc my.asterisk.server 5038 -e ./events.sh

And check that agents were added.

