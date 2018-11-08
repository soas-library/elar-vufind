#!/bin/sh
PATH=$PATH:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
export PATH
export VUFIND_HOME=/usr/local/vufind/
export VUFIND_LOCAL_DIR=/usr/local/vufind/local
cd /usr/local/vufind/resource/
php resource-load.php > resource-load.log 2>&1
#php resource-uploadedcurrent.php > resource-uploadedcurrent.log 2>&1
php resource-downloadedcurrent.php > resource-downloadedcurrent.log 2>&1
