# Setting up and managing a FusionPBX/QueueMetrics link with Ansible


```
Project version: 1.0 
Runtime environment: Debian 12 (Bookworm) - CentOS/Rocky Linux 9
Licence: Public domain
```
See the [changelog](CHANGELOG.md).

## Project description

You have a FusionPBX system with multiple tenants, based on Debian 12, and you want to set up and manage
them on multiple QueueMetrics Live instances, one for each of the tenants you define (so you may have some tenants using QML, and some that don't use it, as you best see fit). 

It is easy to gather the benefits of offering your customers a complete call center suite, as described in https://www.queuemetrics-live.com/resellers.jsp - anst still have an easy management of the solution that links together your PBX and QueueMetrics Live.

This Ansible task does the following:

- connects to each FusionPBX system
- installs or updates Uniloader 
- checks that all supplied credentials are valid
- installs service `uniloader-freeswitch` to download events from FusionPBX and starts it at boot
- sets log rotation
- installs service `uniloader-splitter` to send the correct events to each tenant and starts it at boot
- installs `audiovault`
- creates a splitter file with your rules on each machine
- creates an AudioVault configuration file
- restarts the services to pick up changes if needed
- pulls configuration data (queues and agents) from each FusionPBX tenant and pushes them to the correct
  QueueMetrics Live instance, so that they remain in sync. This can be done on each run or just once
  when the system is created.


## Requirements

* Ansible 2+ (tested with 2.14)
* FusionPBX 5.3 or newer
* A Debian or CentOS target system; while it could be the FusionPBX system itself, we suggest using a separate virtual host to avoid any interference with the main PBX.
* Works with QueueMetrics Live instances (no changes) and with local QueueMetrics system, as long as they are 
  set up for web data upload.
* One or more QueueMetrics Live instances




### Downloading for Debian

    apt-get update
    apt-get install ansible git tar wget

    git clone https://github.com/Loway/OpenQueueMetricsAddOns.git
    cd OpenQueueMetricsAddOns/ansible-fusionpbx


### Downloading for CentOS/Rocky Linux

    yum install ansible git tar wget

    git clone https://github.com/Loway/OpenQueueMetricsAddOns.git
    cd OpenQueueMetricsAddOns/ansible-fusionpbx


### Instance configuration


On each QueueMetrics instance, the following settings must be made before starting:

- User `robot` is enabled to allow remote configuration and has security keys `USR_QUEUE USR_AGENT USRADMIN`.
- An outbound queue named `OUTBOUND` is added
- The settings below are present.


        platform.freeswitch.tenant=tenant1.popk.net
        callfile.dir=fsw:ClueCon@127.0.0.1:8021

        default.hotdesking=0
        platform.pbx=FREESWITCH_LIVE

        platform.freeswitch.use_external_ref=true
        platform.freeswitch.verbose=true
        platform.freeswitch.addmember=true
        platform.freeswitch.use_external_ref=true
        platform.freeswitch.agentChannel={qm_queue=${q},origination_caller_id_number=1973,origination_caller_id_name=QM-${q},enable_early_media=true}user/${num}
        platform.freeswitch.destinationNumber=${num}
        platform.freeswitch.spychannel={qm_ignore=1,origination_caller_id_name=Spy_q${q}_q${a}}user/${num}
        platform.freeswitch.spycmd=queue_dtmf:w${spymode}@500,eavesdrop:${callid}


(If you use a different IP address / connection token for your FusionPBX server, set it here).

For AudioVault, also add:

    audio.server=it.loway.app.queuemetrics.callListen.listeners.JsonListener
    audio.jsonlistener.url=https://tenant1.mysrv.my/audiovault/search/?tenant=tenant1.mysrv.my
    audio.jsonlistener.method=POST
    audio.jsonlistener.searchtoken=x1x1secret
    audio.jsonlistener.verbose=false
    audio.html5player=true




## Usage istructions

First define your set of FusionPBX systems in `ansible-hosts`. You can go from one server to as many as you want.

Then edit `fsw.yml` and edit the section `vars`:

    uniloader_version: "25.05.1"

THe version of Uniloader to use. Must be 25.05+

    fsw_host: "10.10.1.119"
    fsw_port: "8021"
    fsw_auth: "ClueCon"

These are the IP address, token and port for your Freeswitch server. If unsure, you can easily test them with Uniloader: `uniloader test fsw-esl --host 10.10.1.119`.


    fusion_db: "127.0.0.1/fusionpbx?sslmode=disable"
    fusion_login: "fusionpbx"
    fusion_pwd: "s0mepassw0rd"

These are credentials to FusionPBX's own database. If unsure, you can use `uniloader test postgres --ps-uri 127.0.0.1/fusionpbx?sslmode=disable --ps-login fusionpbx --ps-pwd s0mepassw0rd` to check them.

    outbound_include_caller: ""
    outbound_exclude_caller: ""
    outbound_include_callee: "^9.+"
    outbound_exclude_callee: ""

The rules (regexps) to include outbound calls (with automated tracking). In the example above, we include all calls where the destination number starts with a 9.

    autoconfiguration: True
    autoconfigure_always: False
    autoconfigure_agent_pwd: "v3rys3cret"
    default_domain: "company.my"    

Above, you set up autoconfiguration. If you set `autoconfiguration` to False, it won't be performed. If you set `autoconfigure_always` it will be repeated on each run, while usually it will be done just once, or when a clietnt's parameters change. You can set up a default password for your agents, so they can log in into QM; and you must define the domain name used by your FusionPBX instance. When a client is autoconfigured, a flag file to avoid further configurations is created, so you can see when it happened last.

Now edit the section `clients` - the key is the name of the customer's subdomain (e.g. in this example, key `client1` would be the subdomain `client1.company.my`) so that it contains all of your QM Live instances. You can add/remove clients as needed.

	    clients:
	      client1:
	        url:   "https://my.queuemetrics-live.com/client1/"
	        login: "webqloader"
	        pass:  "upload"  
            actions: True
            disabled: False
            av_secret: "x1x1secret"
            refresh: ""
	      client2:
	        url:   "https://us.queuemetrics-live.com/client2/"
	        login: "webqloader"
	        pass:  "upload"
            actions: False
            disabled: False
            av_secret: "x2x2secret"
            refresh: ""

For each client, apart from the usual credentials, we have the values:

- `actions` is whether your QM-Live instance should control agent presence through the Agent's page. 
  For this to work, the property `callfile.dir` in QueueMetrics must correctly point to the ESL port
  of your FusionPBX system.
- `disabled` is used so that you can keep the instance within your configuration file without deleting it; 
  still, a disabled instance will NOT upload data and won't be autoconfigured. 
- `av_secret` - the authorization code to be used to access AudioVault for this tenant
- `refresh` - when you add a new value, automatic configuration will be forced. As this is cached, it is important that you do not recycle the same refresh value - you could e.g. use a progressive date like `250516a` to avoid duplicates.

### AudioVault

You can run AudioVault in two ways:

- having it answer in HTTP, binding it so that it is accessible from `localhost` only. This presumes that you will put an HTTPS proxy in front of it. This option gives you the highest flexibility in terms of logging, rewriting, etc.
- having it serve answers in HTTPS directly. Your FusionPBX server will already have (and likely renew automatically) an HTTPS wildcard certificate. We can use it as well ourselves, to start a new server on port 4040!

For this latter option use:

    audiovault: True
    av_host: ""
    av_port: "4040"
    av_public_url: "https://tenant1.srv.my"
    av_path: "file:/var/lib/freeswitch/recordings/%%TE/archive/%%YY/%%ME/%%DD"
    av_token: "CHANGEME"
    av_cert: "/etc/dehydrated/certs/popk.net/fullchain.pem"
    av_cert_key: "/etc/dehydrated/certs/popk.net/privkey.pem"

Note that if the certificate is a wildcard, you can then use _any name_ that points to that server, and it will just work!


If you want to use a proxy instead, configure it like:

    audiovault: True
    av_host: "localhost"
    av_port: "4040"
    av_public_url: "https://tenant1.srv.my"
    av_path: "file:/var/lib/freeswitch/recordings/%%TE/archive/%%YY/%%ME/%%DD"
    av_token: "CHANGEME"
    av_cert: ""
    av_cert_key: ""

This way, the server will ignore any calls not coming from the same server.



## Running

To run the script:
- if you want to install on a remote server, first edit the file `ansible-hosts` to decide on which server(s) to install. 
- if you want to install on the same system, edit `fsw.yml` so that it says:

        - hosts: localhost
          connection: local    

To install on the same server, use "localhost".

To run the script, just run:

    ./run.sh


Every time you change the configuration (eg add a new tenant), you run this script again.



## Authors

Loway SA 

## See also

* [QueueMetrics home page](https://www.queuemetrics.com)
* [FusionPBX home page](https://www.fusionpbx.com/)
* [Uniloader manual](https://docs.loway.ch/Uniloader/index.html)
* [AudioVault](https://docs.loway.ch/Uniloader/087_AudioVault.html)
