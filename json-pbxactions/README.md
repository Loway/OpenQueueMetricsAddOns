QueueMetrics JSON PBX action library project
============================================

```
Project version: 1.0 
Runtime environment: PHP json and lib CURL installed
Licence: "LGPL"
```
See the [changelog](CHANGELOG.md).

Project description
-------------------

This script allows to interact with QueueMetrics in order to interact with the PBX. This enable an external robot to 
perform login/logout, pause/unpause, hangup, monitor calls and other PBX oriented actions by simply calling a set of
JSON RPC.

Requirements
------------
PHP and lib CURL installed


Usage istructions
-----------------
The script contains a set of functions and a set of examples. Before using the script you need to configure the
local variables placed on top of the file. 
At the end of the file are reported a set of call examples you can use as a starting point for your own implementation.
Please comment-out the examples you don't need.

Authors
-------
Marco Signorini


See also
--------

* [QueueMetrics home page](http://queuemetrics.com)
* [QueueMetrics JSON manual](http://manuals.loway.ch/QM_JSON_manual-chunked/)
