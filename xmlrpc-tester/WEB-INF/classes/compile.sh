#!/bin/bash

case "$1" in
  5) OPTS="-source 1.5 -target 1.5";;
  6) OPTS="-source 1.6 -target 1.6";;
esac

/usr/bin/javac -cp "../lib/*:." $OPTS -d ./ ./*.java


