#! /bin/bash


rm -rf /usr/share/wesellin/latest
mkdir -p /usr/share/wesellin/latest

#if [ ! -d "/usr/share/wesellin/latest" ]; then
#    mkdir /usr/share/wesellin/latest
#fi

chmod 775 -R "/usr/share/wesellin/latest"

cd "/usr/share/wesellin/latest"

#readlink -f "./"

# Download latest version
wget "http://download.wesellin.net/latest.zip" >> downloading.log

# Unzip latest version
unzip -o  "latest.zip" >> unziping.log

# Remove zip
unlink "latest.zip"

echo "Done!" 
