#!/bin/bash -e

username=`whoami`
if [ "$username" != "root" ]; then
    echo "Please run this script as root";
    exit 1
fi

cp -r $1 $2