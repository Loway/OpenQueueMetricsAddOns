Sloggatron
==========

```
Project version: 1.0 
Runtime environment: bash shell 
Licence: LGPL
```
See  [changelog](CHANGELOG.md).

Project description
-------------------

This bash script removes all the dynamic agents from all the queues in an Asterisk. It comes in handy to run at night
to make sure all agents are logged off at the end of their shift. You may also run it manually to "reset" an Asterisk queue.


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

You may want to run this in a cron job to make sure everybody is logged off at night.
