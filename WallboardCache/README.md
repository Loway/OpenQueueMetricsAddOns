# A QueueMetrics Wallboard cache

__In the end, if you have to serve hundreds of identical, non interactive wallboards that just display the current state, why not run just one of them, take pictures of it and redistribuite those instead?__


![Alt text](https://github.com/Loway/OpenQueueMetricsAddOns/raw/master/WallboardCache/qm_embedded_wb.jpg "How it looks like")


Advantages:

- Very resilient, very safe
- Works in really *any* client, with minimal energy consumption

s
Disadvantages:

- It's slow
- Consumes a bit of bandwidth
- Requires separate rendering server


## Prerequisites

On CentOS 7, `curl https://intoli.com/install-google-chrome.sh | bash`.

Node 10+

	yum install -y gcc-c++ make atk java-atk-wrapper at-spi2-atk gtk3 libXt
	yum update -y nss
	curl -sL https://rpm.nodesource.com/setup_10.x | sudo bash -



## Usage

	npm install
	rm -rf data/
	node index.js

Once clients are connected, you can restart the process - images will stall for a few seconds, but will keep on working. If the process crashes, client images will stall, and will restart when the process is restarted.

It might be a good idea to restart the process evey few hours, as to avoid memory leaks.

As each wallboard requires a separate Chrome instance, I'm seeing that it costs ~100M per wallboard on the server. 

In terms of data usage, if the average resulting image is ~60k and clients reload 720 times per hour, it's less than 50M/hour/client = ~15k/sec/client. If this is too much, you can have clients reload less often (every 10, 20 or even 60 seconds) to maintain situational awareness with a minimal data usage. In the end, how often would the look up at the wallboard in front of them? 


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





## Developers

After changing:

	npx prettier --write index.js