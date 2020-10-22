#!/bin/bash

basepath=$(cd `dirname $0`; pwd)
result=$(crontab -l|grep -i "* * * * * $basepath/queue.sh"|grep -v grep)
if [ ! -n "$result" ]
then
crontab -l > createcrontemp && echo "* * * * * $basepath/queue.sh" >> createcrontemp && crontab createcrontemp && rm -f createcrontemp
echo -e "\033[32mOk.\033[0m"
else
echo "The process has been add ."
fi
