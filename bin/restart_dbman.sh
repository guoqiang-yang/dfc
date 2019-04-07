#!/bin/sh
##

killall DbMan
sleep 0.5

nohup ./DbMan --kxi.conf=conf.dbman &> /dev/null&
sleep 0.2

ps -ef|grep DbMan