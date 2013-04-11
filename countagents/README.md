Sample QueueMetrics add-on project
==================================

```
Project version: 1.0 
Runtime environment: PHP+PEAR 
Licence: LGPL
```
See  [changelog](CHANGELOG.md).

Project description
-------------------

The PHP script counts how many unique agents are running a specified queue in the given period.

Requirements
------------

The script requires to have installed:

* the PHP version 5 language interpreter
* the PEAR library containing the modules XML and Console

Installing
----------

To be able to run the script in a proper way, a simple customization has to be made before the first run. Download the file and place it in a folder, then open it with a text editor. Look at the code section marked with "==== Here are customizable parameters ====" and change the $agentLabel variable and $agentNameExceptions array if required. 

The $agentLabel is the caption that can be found on the column identifying the agents in the “Answered call” table reported by Queuemetrics. You should change if you run Queuemetrics in a language that is not English.
The $agentNameExceptions is an array containing a list of rules the script will follow when counting the agents. The default behavior is to skip all agents containing SIP/ at the beginning of their name definition. Feel free to add any other rule if needed. 

In order to run on Linux machines, the executable flag must be set. To do this, from a shell prompt issue the command reported below: 

```
#> chmod 755 ./CountAgents.php 
```
then run the script by issuing the command 

```
#> ./CountAgents.php
```

Usage istructions
-----------------

The script requires some parameters to be specified at command line; some are optional. The only mandatory parameter is the queue name and the analysis period. Below is the detailed explanation detail of each parameter. 

```
CountAgents.php [-h host] [-p port] [-w application] [-d days] -u username -x password queuename [yyyymmdd] [YYYYMMDD] 
```

* host: hostname or IP address (default: localhost);
* port: port where the application runs (default: 8080);
* application: the web application name (default: queuemetrics);
* days: the number of last days where the query will be run if no date is specified;
* username: the username to be used to log in. It has to have the robot key property;
* password: the password to be used to log in;
* queuename: the pivot queue name to look at;
* yyyymmdd: is the start date of period where the query will be run if no days are specified;
* YYYYMMDD: is the end date of period where the query will be run if no days are specified; 

Some additional notes are related to the following parameters: 

* queuename: this is the technical name of the queue to be analized. It's possible to analyze more than one queue at time concatenating their names through the pipe character. In this case, the composite name should be enclosed between quotation marks. For example, valid names are qs-100 or "qi-100|qo-100".
* days: when this parameter is present, the specified start and end time are optional. The reporting period will be included between the run date and the specified days before.
yyyymmdd and YYYYMMDD: are respectively the start date and the end date of the reporting period. They could be omitted if the -d option was specified. 

Examples: 

```
#> ./CountAgents.php -h10.10.0.1 -d30 -uuser -xrobot “200|201” 
```

This first example counts the number of unique agents found in the queue 200 and 201, in the last 30 days, for a Queuemetrics instance running on the host 10.10.0.1:8080. 

```
#> ./CountAgents.php -uuser -xrobot “200|201” 20070101 20071231 
```

This second example counts the number of unique agents found in the queue 200 and 201, in the year 2007, for a Queuemetrics instance running on local host.

Authors
-------

Marco Signorini


See also
--------

None
