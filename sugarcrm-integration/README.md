Sample QueueMetrics add-on project
==================================

```
Project version: 1.0 
Runtime environment: LAMP with nusoap libraries installed
Licence: "LGPL"
```
See the [changelog](CHANGELOG.md).

Project description
-------------------

This script integrates the QueueMetrics agent page with SugarCRM.
When properly configured, the QueueMetrics agent call history page opens an external URL for each taken call. 
This feature could be used to call the provided PHP script. The script searches between contacts in the SugarCRM, starting
from the calling number, and opens the call detail record, if present on SugarCRM database, or preloads a new contact page with
the calling party number.


Requirements
------------
Apache + PHP (could be placed in the SugarCRM webroot folder) + nusoap libraries.

Installing
----------
- Copy the nusoap libraries to the webroot folder
- Copy the QueueMetrics_SugarCRM.php script to the webroot folder
- Create a queuemetrics user on SugarCRM, with permissions to look at contacts
- Customize the server_url, username, password variables in the PHP script
- Configure QueueMetrics to open an external URL for each received call. The URL should contain the caller number as callid parameter


Usage istructions
-----------------
Each time a new call is shown in QueueMetrics agent page, the browser opens the SugarCRM caller contact page. The first time
SugarCRM requires authorization with valid agent credentials. 
It's possible to customize the script in order to embed authentication to SugarCRM. In this case you should implement the logic required to
retrieve the SugarCRM agent password and pass the agent code in the URL by mean of the agentcode parameter.
The logic needed to retrieve the SugarCRM agent password should be called by the script as shown by comments present on it.

Authors
-------
Marco Signorini


See also
--------

* [QueueMetrics home page](http://queuemetrics.com)
* [QueueMetrics support page](http://www.queuemetrics.com/manual_list.jsp) on Advanced Configuration Manual
