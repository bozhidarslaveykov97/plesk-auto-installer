#! /bin/bash

downloadUrl=`echo $1 | base64 -d`

downloadCacheFolder='/usr/share/wesellin-download-cache'

if [ ! -d $downloadCacheFolder ]; then
    mkdir $downloadCacheFolder
fi

cd $downloadCacheFolder

zipCacheFolder=$2'-cache'
zipDownloadedFile=$2'-cache.zip';

// Remove old zip
echo 'Remove old zip cache folder...'
unlink $zipCacheFile

mkdir $zipCacheFolder
cd $zipCacheFolder

echo 'Download from url...'
wget $downloadUrl -O $zipDownloadedFile

# Unzip selected version

echo 'Unzip file...'
unzip $zipDownloadedFile > unziping.log

if [ ! -d '/usr/share/wesellin' ]; then
    mkdir '/usr/share/wesellin'
fi

echo 'Move file to /usr/share/wesellin'
mv $2 /usr/share/wesellin

echo "Done!"
