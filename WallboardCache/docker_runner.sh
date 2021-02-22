#! /bin/bash

export CHROME="/usr/bin/google-chrome"
export WORKSPACE="/opt/wbcache/work"
export ROOT=1
export URL=${URL:-https://www.queuemetrics.com}

echo "URL: $URL CHROME: $CHROME"

node /opt/wbcache/index.js



