RemoveAllAgents
===============

```
Project version: 1.0 
Runtime environment: bash shell 
Licence: LGPL
```
See  [changelog](CHANGELOG.md).

Project description
-------------------

This bash script removes all agents from a queue. It comes in handy to run at night
to make sure all agents are logged off at the end of their shift. You may also run it
manually to "reset" an Asterisk queue.


Requirements
------------

Asterisk needs to be installed.

Installing
----------

Copy the file to your Linux box, and make it executable.

In order to run on Linux machines, the executable flag must be set. To do this, from a shell prompt issue the command reported below: 


Usage istructions
-----------------

The script requires the name of the queue to log everybody off from.

For example:

```
[root@pbx ~]# ./removeAllAgents.sh 401
Removing any dynamic agent from queue 401
 - SIP/203: Removed interface 'SIP/203' from queue '401'
 - SIP/201: Removed interface 'SIP/201' from queue '401'
 - SIP/217: Removed interface 'SIP/217' from queue '401'
 
```

You may want to run this in a cron job to make sure everybody is logged off at night.


Authors
-------

Lenz Emilitri


See also
--------

None
