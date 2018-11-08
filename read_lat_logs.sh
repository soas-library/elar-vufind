#!/bin/sh
PATH=$PATH:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
export PATH
export VUFIND_HOME=/usr/local/vufind/
export VUFIND_LOCAL_DIR=/usr/local/vufind/local
cd /usr/local/vufind/latlogs/
php asv-load.php > asv-load.log 2>&1
php annex-load.php > annex-load.log 2>&1
php trova-load.php > trova-load.log 2>&1
php annexlogins-load.php > annexlogins-load.log 2>&1
php trovalogins-load.php > trovalogins-load.log 2>&1
php asvlogins-load.php > asvlogins-load.log 2>&1
