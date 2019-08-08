#! /bin/bash

if [ ! -d "/usr/share/wesellin" ]; then
	mkdir /usr/share/wesellin
fi

chmod 775 -R "/usr/share/wesellin"

cd /usr/share/wesellin

unlink "latest.zip"
wget "http://download.wesellin.net/latest.zip"
unzip -o  "latest.zip"

