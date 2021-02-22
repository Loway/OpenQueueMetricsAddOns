# A QueueMetrics Wallboard cache

_In the end, if you have to serve hundreds of identical, non-interactive wallboards that just display the current state, why not run just one of them, take **pictures** of it and redistribuite those pictures instead?_


![Alt text](https://github.com/Loway/OpenQueueMetricsAddOns/raw/master/WallboardCache/qm_embedded_wb.jpg "How it looks like")


__Advantages:__

- Very resilient: keeps on working even when server fails, will re-sync when it comes back up 
- Very safe: it's just a picture!
- Works in really *any* client, with minimal memory and energy consumption


__Disadvantages:__

- It's slow
- Uses more bandwidth (but: see below)
- Requires a separate rendering server


## Prerequisites

__Google Chrome__

On CentOS 7:

	curl https://intoli.com/install-google-chrome.sh | bash


This will install a Chrome that can be run from `/usr/bin/google-chrome`.

__Node 10+__

On CentOS 7:

	yum install -y gcc-c++ make atk java-atk-wrapper at-spi2-atk gtk3 libXt
	yum update -y nss
	curl -sL https://rpm.nodesource.com/setup_10.x | sudo bash -
	yum install -y nodejs


## Usage

First, you have to configure your wallboards in 

	const WALLBOARDS = {
	  plain:
	    my_qm +
	    "queues=500%7C501%7C502%7C770%7C771%7C772%7Cpark-default&wallboardId=17",
	  classic:
	    my_qm +
	    "queues=500%7C501%7C502%7C770%7C771%7C772%7Cpark-default&wallboardId=16",
	  hn: "https://news.ycombinator.com/"
	};

Each wallboard has a name and a URL (that is, the one you get from QM). You can have as many wallboards as you need.

	const delay = 5000;

Is the page refresh time, both for servers and for clients.

	const localChrome =
	  "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";

This is where the local instance of Chrome will be found.

	const extra_args = []; // ["--disable-setuid-sandbox", "--no-sandbox"]

If running as root (not recommended!) you should set `extra_args` to `["--disable-setuid-sandbox", "--no-sandbox"]`or Chrome won't start.

Then run:

	npm install
	rm -rf workspace/
	node index.js


Each wallboard will be availble at the URL:

	http://127.0.0.1:3000/?wb={name_of_wallboard}&agent={id_of_agent}

Once clients are connected, you can restart the process at any time - images will stall for a few seconds, but will keep on working. If the process crashes, client images will stall, and will restart automatically when the process is restarted.

It might be a good idea to restart the process evey few hours, as to avoid memory leaks.

As each wallboard requires a separate Chrome instance, I'm seeing that it costs __~100M per wallboard__ on the server. 

In terms of __data usage__, if the average resulting image is ~60k and clients reload 720 times per hour, it's less than 50M/hour/client = ~15k/sec/client. If this is too much, you can have clients reload less often (every 10, 20 or even 60 seconds) to maintain situational awareness with a minimal data usage. In the end, how often would the look up at the wallboard in front of them? 

### Run in Docker instead

You can avoid the setup and all the fun above by running:

	docker run -e "URL=https://us.queuemetrics-live.com:443/unk/qm_wab2.do?user=robot&pass=123&queues=500&wallboardId=17" -p 3000:3000  -d loway/wbcache

More information on the Docker image is available at https://hub.docker.com/repository/docker/loway/wbcache


### Set up in QueueMetrics

**As background**

As one of multiple selectable backgrounds:

	realtime.agent_web1_url=http://127.0.0.1:3000/?wb=classic&agent=[a]
	realtime.agent_web1_label=Wallboard

Where you can have up to three.

**As a web panel**

Most flexible, it is a panel so it can be hidden / moved / etc.

When resized, image resizes.

	realtime.agent_webpanel1_url=http://127.0.0.1:3000/?wb=classic&agent=[a]
	realtime.agent_webpanel1_label=My wallboard

Cons: can have only one.


**As general background**

Least flexible, can have only one:

	realtime.agent_background_url=http://127.0.0.1:3000/?wb=classic&agent=[a]


## For developers

After making changes:

	npx prettier --write index.js

Thanks!
