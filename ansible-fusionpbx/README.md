Setting up and managing a FusionPBX/QueueMetrics link with Ansible
==================================================================

```
Project version: 1.0 
Runtime environment: CentOS 7 
Licence: Public domain
```
See the [changelog](CHANGELOG.md).

Project description
-------------------

You have a FusionPBX system with multiple tenants, and you want to set up and manage
them on multiple QueueMetrics Live instances, one for each of the tenants you define (so you may have some tenants using QML, and some that don't use it, as you best see fit). 

It is easy to gather the benefits of offering your customers a complete call center suite, as described in https://www.queuemetrics-live.com/resellers.jsp - anst still have an easy management of the solution that links together your PBX and QueueMetrics Live.

This Ansible task does the following:

- connects to each FusionPBX system
- installs Uniloader via `yum`
- installs service `uniloader-freeswitch` to download events from FusionPBX and starts it at boot
- installs service `uniloader-splitter` to send the correct events to each tenant and starts it at boot
- creates a splitter file with your rules on each box
- restarts the services to pick up changes if needed
- pulls configuration data (queues and agents) from each FusionPBX tenant and pushes them to the correct
  QueueMetrics Live instance, so that they remain in sync. This can be done on each run or just once
  when the system is created.


Requirements
------------

* Ansible 2+
* A CentOS 7 target system; while it could be the FusionPBX system itself, we suggest using a separate virtual host to avoid any interference with the main PBX.
* Works with QueueMetrics Live instances (no changes) and with local QueueMetrics system, as long as they are 
  set up for web data upload.
* One or more QueueMetrics Live instances



On each QM Live instance, the following settings must be made before starting:

- User `robot` is enabled to allow remote configuration
- The settings below are present.


    platform.pbx=Freeswitch
    default.webloaderpbx=true
    default.hotdesking=0
    callfile.dir=fsw:ClueCon@127.0.0.1

(If you use a different IP address / connection token for your FusionPBX server, set it here).



Usage istructions
-----------------

First define your set of FusionPBX systems in `ansible-hosts`. You can go from one server to as many as you want.

Then edit `fsw.yml` and edit the section `vars`:
    
    fsw_host: "10.10.1.119"
    fsw_port: "8021"
    fsw_auth: "ClueCon"

These are the IP address, token and port for your Freeswitch server. If unsure, you can easily test them with Uniloader: `uniloader test fsw-esl --host 10.10.1.119`.


    fusion_db: "127.0.0.1/fusionpbx?sslmode=disable"
    fusion_login: "fusionpbx"
    fusion_pwd: "s0mepassw0rd"

These are credentials to FusionPBX's own database. If unsure, you can use `uniloader test postgres --ps-uri 127.0.0.1/fusionpbx?sslmode=disable --ps-login fusionpbx --ps-pwd s0mepassw0rd` to check them.

    autoconfiguration: True
    autoconfigure_always: False
    autoconfigure_agent_pwd: "v3rys3cret"
    default_domain: "company.my"    

Above, you set up autoconfiguration. If you set `autoconfiguration` to False, it won't be performed. If you set `autoconfigure_always` it will be repeated on each run, while usually it will be done just once. You can set up a default password for your agents, so they can log in into QM; and you must define the domain name used by your FusionPBX instance. When a client is autoconfigured, a flag file to avoid further configurations is created, so you can see when it happened last.

Now edit the section `clients` - the key is the name of the customer's subdomain (e.g. in this example, key `client1` would be the subdomain `client1.company.my`) so that it contains all of your QM Live instances. You can add/remove clients as needed.

	    clients:
	      client1:
	        url:   "https://my.queuemetrics-live.com/client1/"
	        login: "webqloader"
	        pass:  "upload"  
            actions: True
            disabled: False
	      client2:
	        url:   "https://us.queuemetrics-live.com/client2/"
	        login: "webqloader"
	        pass:  "upload"
            actions: False
            disabled: False

For each client, apart from the usual credentials, we have the values:

- `actions` is whether your QM-Live instance should control agent presence through the Agent's page. 
  For this to work, the property `callfile.dir` in QueueMetrics must corretly point to the ESL port
  of your FusionPBX system.
- `disabled` is used so that you can keep the instance within your configuration file without deleting it; 
  still, a disabled instance will NOT upload data and won't be autoconfigured. 


To run the script, just run:

		ansible-playbook -i ansible-hosts \
		       --private-key ~/zebraman_key -u zebraman --become-user root \
		       fsw.yml

This logs in into each box as user "zebraman", becomes root, and then performs all needed changes. As the script is idempotent, it will only perform changes when needed - usually when you make changes to the configuration.

Authors
-------

Loway SA 

See also
--------

* [QueueMetrics home page](https://www.queuemetrics.com)
* [FusionPBX home page](https://www.fusionpbx.com/)
* [Uniloader manual](https://manuals.loway.ch/Uniloader-chunked/)
