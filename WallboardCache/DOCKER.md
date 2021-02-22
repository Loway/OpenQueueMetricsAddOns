# Running with Docker


On the server

	yum install -y docker-io && service docker start

Then run:


	docker run -e DELAY=15 -e "URL=https://us.queuemetrics-live.com:443/unk/qm_wab2.do?user=robot&pass=123&queues=500&wallboardId=17" -p 8080:3000  -d loway/wbcache

That will expose the URL specified to port 8080 as wallboard "wb".

In QM:


	realtime.agent_webpanel1_url=http://server:8080/?wb=wb&agent=[a]
	realtime.agent_webpanel1_label=My wallboard



## Developing

Create a CentOS 7 VM, then:


	yum install -y curl docker-io git
	git clone ....


	docker build -t=wb .
	docker run -p 3000:3000  -d wb


Run in foreground, with Bash:

	docker run -p 3000:3000  -it wb /bin/bash

Tag and push

	docker tag wb loway/wbcache:210222a
	docker tag wb loway/wbcache:latest
	docker push loway/wbcache:210222a
	docker push loway/wbcache:latest








