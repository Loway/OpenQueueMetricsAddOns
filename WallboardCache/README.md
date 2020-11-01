# A QueueMetrics Wallboard cache


![Alt text](./qm_embedded_wb?raw=true "How it looks like")

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

Once clients are connected, you can restart the process - images will stall for a few seconds, but will keep on working.


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