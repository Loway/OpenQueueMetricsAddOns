FlanTel Wallboard
=================

```
Project version: 1.1
Runtime environment: PHP 
Licence: "Public domain"
```
See the [changelog](CHANGELOG.md).

Project description
-------------------

This is a QueueMetrics users' all-time favourite: the Flantel wallboard. 

For the moment, just see the [changelog](CHANGELOG.md).




Requirements
------------
PHP

Installing
----------
Copy the PHP file on your server and edit its config variables. 





Usage istructions
-----------------
You need to configure:

* The QM login and pass, plus where the QM server is located
* The queues you want to be used. Queues are set up in groups. Look for the code below
  and edit it to create your own groups.

```PHP
switch ($queuegroup) {
  case 'all':
    $queueids = "300|301";
    $queuename='Inbound';
  break;;
```

You can call the script qmon.php with a number of parameters:

* refresh=5 -  how often to refresh the page, in seconds
* queue=all - Thich queue group to display - default 'all'
* showstatus=1 - Whether to show the Agent Status for a queue group - default 1 (on)
* showcurrcalls=1 - Whether to show the Current Calls for a queue group - default 1 (on)
* showout=1 - Whether to show the Outbound queue as a separate column for a queue group - default 1 (on)




Authors
-------
Author: Barry Flanagan <barry AT flantel DOT com>
Date: 21 Nov 2007
  


See also
--------

* [QueueMetrics home page](http://queuemetrics.com)
* [qmon page at Flantel](http://www.flantel.com/qm/)
