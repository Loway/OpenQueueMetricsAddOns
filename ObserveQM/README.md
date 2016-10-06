ObserveQM - Performance monitoring tool
=======================================

```
Project version: 1.0 
Licence: Public domain
```

Project description
-------------------

If you are experiencing performance issue with QueueMetrics, this script you will help identifyng the problem.
This simple script takes both a thread dump and the memory status of QueueMetrics and create two logs files in __/usr/local/queuemetrics/tomcat/logs__:

* thread.__current_date__
* mem.__current_date__

If ran multiple times during the same day it will happend the output to the existing log.


Installing
----------

Put your script somewhere in your machine (__/opt/scripts__ is a good place) and make it executable.

Then make sure to add a line to your crontab in order to execute it every 5 minutes until you collected enough information.

    */5 * * * * /path/to/observe_qm_mem.sh
