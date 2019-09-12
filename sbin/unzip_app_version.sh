#! /bin/bash

downloadUrl=`echo $1 | base64 -d`

downloadCacheFolder='/usr/share/app-download-cache'

if [ ! -d $downloadCacheFolder ]; then
    mkdir $downloadCacheFolder
fi

cd $downloadCacheFolder

zipDownloadedFile=$2'-cache.zip';


echo 'Download from url...'
wget $downloadUrl -O $zipDownloadedFile

# Unzip selected version
echo 'Unzip file...'
unzip $zipDownloadedFile -d latest > unziping.log

if [ ! -d '/usr/share/$3' ]; then
    mkdir '/usr/share/$3'
fi

echo 'Delete files from /usr/share/'$3'/latest'
rm -rf '/usr/share/'$3'/latest'

echo 'Move file to /usr/share/'$3
mv latest /usr/share/$3

echo "Done!"
