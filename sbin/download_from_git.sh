#! /bin/bash


rm -rf /usr/share/wesellin
mkdir -p /usr/share/wesellin/latest

#if [ ! -d "/usr/share/wesellin/latest" ]; then
#    mkdir /usr/share/wesellin/latest
#fi

chmod 775 -R "/usr/share/wesellin/latest"

cd "/usr/share/wesellin"

#readlink -f "./"

# Remove old zip
unlink "latest.zip"

# Download latest version 
wget "http://download.wesellin.net/latest.zip" >> downloading.log

# Unzip latest version
unzip -o "latest.zip" -d latest >> unziping.log

chmod 777 -R latest

echo "Done!"
