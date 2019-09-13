#!/bin/bash -e

username=`whoami`
if [ "$username" != "root" ]; then
    echo "Please run this script as root";
    exit 1
fi

if [ -f $1 ]; then
	cp -r $1 $2
fi