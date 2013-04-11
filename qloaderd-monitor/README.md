Qloaderd monitor
================

```
Runtime environment: Unix Shell 
Licence: Public domain 
```
See the [changelog](CHANGELOG.md).

Project description
-------------------

This script will monitor the log produced by Qloaderd for disconnection errors and will send an email to the sysadmin in case any are found. This is useful because if you operate on a WAN, it is possible that you have long-term downtimes that risk losing data (for example if you do a logrotate while the network is down).

Requirements
------------

Qloaderd and a Unix environment.

Installing
----------

Copy the file to the machine where qlaoderd is running. Edit the file to enter your email address for notification in the NOTIFY variable.


Usage istructions
-----------------

Run daily through a cron job.

Authors
-------

Daniel Satta


See also
--------

* [QueueMetrics home page](http://queuemetrics.com)

