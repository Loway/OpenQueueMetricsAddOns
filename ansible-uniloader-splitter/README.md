Setting up the Uniloader splitter with Ansible
==============================================

```
Project version: 1.0 
Runtime environment: CentOS 6 
Licence: Public domain
```
See the [changelog](CHANGELOG.md).

Project description
-------------------

If you have a number of PBXs on each of which there are multiple tenants, it is
not so easy to keep manually in sync when uploading data with a splitter.

This Ansible task does the following:

- connects to each Asterisk system
- installs Uniloader via TGZ (if not present)
- installs an init script that controls the service and starts it at boot
- creates a splitter file with your rules on each box
- restarts the services to pick up changes if needed.


Requirements
------------

* Ansible 2+
* This was developed to run on a set of pre-initd Debian systems; should work as well on CentOS 6.
* Works with QueueMetrics Live instances (no changes) and with local QueueMetrics system, as long as they are 
  set up for web data upload.

Usage istructions
-----------------

First define your set of Asteriks systems in `ansible-hosts`. You can go from one server to as many as you want.

Then edit `splitter-asterisk.yml` and edit the section `clients`:


	  vars:
	    uniloader_version: "0.7.1"
	    clients:
	      client1:
	        url:   "https://my.queuemetrics-live.com/client1/"
	        login: "webqloader"
	        pass:  "upload"  
	      client2:
	        url:   "http://my.local:8080/queuemetrics/"
	        login: "webqloader"
	        pass:  "upload"

Each client must be set up so that all queus for "client1" are named eg "1234-client1"  and all agents are like "SIP/103-client1", so that the splitter can split them  correctly. If you need different settings, see `files/splitter.json.j2`.

To run, just run:

		ansible-playbook -i ansible-hosts \
		       --private-key ~/zebraman_key -u zebraman --become-user root \
		       splitter-asterisk.yml


This logs in into each box as user "zebraman", becomes root, and then runs the script.

Authors
-------

Loway SA - ref `b4531_squashed_cluster`.


See also
--------

* [QueueMetrics home page](http://queuemetrics.com)
* [Uniloader manual](https://manuals.loway.ch/Uniloader-chunked/)
