#!/bin/sh
PATH=$PATH:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
export PATH
export VUFIND_HOME=/usr/local/vufind/
export VUFIND_LOCAL_DIR=/usr/local/vufind/local
find  /usr/local/vufind/local/harvest/Authors/processed/ -name '*.xml' -exec rm {} \;
/usr/local/vufind/harvest/batch-import-xsl-auth.sh ./Authors/ ../local/import/authors.properties

