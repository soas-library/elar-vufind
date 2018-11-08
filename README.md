Open-source VuFind software [https://vufind-org.github.io/vufind/](https://vufind-org.github.io/vufind/) (developed by Villanova University's Falvey Memorial Library) is used as the digital repository for SOAS' Endangered Languages Archive (ELAR). The repository is available at the URL: [https://elar.soas.ac.uk/](https://elar.soas.ac.uk/)

This site was largely designed by Scanbit [https://www.scanbit.net/](https://www.scanbit.net/) who provide software development and hosting for cultural heritage and research institutions.

This page collects notes and instructions on ELAR's VuFind repository.

For updates and support with VuFind, join the vufind-general@lists.sourceforge.net and the vufind-tech@lists.sourceforge.net mailing lists at [http://sourceforge.net/p/vufind/mailman/](http://sourceforge.net/p/vufind/mailman/). Thorough documentation and manuals on VuFind can be found at [https://vufind.org/wiki/](https://vufind.org/wiki/).

# 1.0: VuFind application

## 1.1: Servers

VuFind is installed on two servers for dev (sjon) and production (wurin). 

The intended change management process is to develop new features or customisations on sjon, send it to ELAR for testing and UAT, and then transfer the code to wurin. 

### 1.1.1: Version control

GitHub is used for version control and transferring files. To commit changes on the dev server:

`git commit -a --author="SimonXIX <sb174@soas.ac.uk>" -m "Changes to header"`

`git push origin [BRANCH_NAME]`

To retrieve changes in the production server:

`git fetch origin`

`git reset --hard origin/[BRANCH_NAME]`

To merge changes from a dev branch into the master branch, merge [BRANCH NAME] into soas via pull request: [https://help.github.com/articles/merging-a-pull-request/](https://help.github.com/articles/merging-a-pull-request/) 

### 1.1.2: Crontab 

The crontab on the VuFind server is under the root username. To see it:

`sudo -i`

Enter root password

`crontab -e`

## 1.2: Basic Linux

VuFind is installed on Linux servers and therefore requires at least a basic understanding of administration in a Linux environment and how to access Linux servers.

On Windows PCs, use PuTTY for access to VuFind servers: http://www.chiark.greenend.org.uk/~sgtatham/putty/

On Macs, use the built-in Terminal application.

For Linux server administration, the best text available is: Nemeth, E., et al, 2011. _Unix and Linux systems administration handbook_. Fourth Edition. Boston: Pearson Education, Inc. Buy or otherwise acquire a copy of that. See also: Shotts, W. E., 2013. _The Linux command line_. Second Internet Edition. This book is available under Creative Commons at [http://linuxcommand.org/tlcl.php](http://linuxcommand.org/tlcl.php) and is a gentler introduction to the more advanced command line functions in Linux.

In a pinch, there's a handy list of basic Unix commands here: [http://mally.stanford.edu/~sr/computing/basic-unix.html](http://mally.stanford.edu/~sr/computing/basic-unix.html) and here: [http://journal.code4lib.org/articles/9158](http://journal.code4lib.org/articles/9158)

Some good general commands to use to troubleshoot problems on the Linux servers:

* w - who is logged in at this moment. Load averages are fine up to about 5.

* top - Top is a load-checking program similar to Task Manager in Windows.

* htop - more advanced load-checking program. Shows virtual and real memory. Use F6 to sort the process list. Kill processes in a safe way by matching by PID in Innopac client. DON'T KILL ANYTHING USING TOP OR HTOP. IT ISN'T SAFE TO DO SO.

* ps -ef will give a quick view of processes running. Under a heavy load ps might work where htop doesn’t.

* df will show disk space.

* df -i will show available inodes.

* df -ih will show the memory load for every partition.

* ls -l | wc -l shows how many files are in the current directory.

* ls -lrt will show which user owns (and therefore probably created) those files.

* $ for i in /*; do echo $i; find $i |wc -l; done will list directories and number of files in them.

* du -smh * to check disk space

* free -m to show available RAM

## 1.3: VuFind application structure

VuFind is a PHP application with all files held in the /usr/local/vufind directory on the server. The application comprises a SolrMarc indexer to build a search index out of Marc records, an Apache website to display it, and a PHP Zend framework to manage catalogue functions. The website runs on port 443 as a https site.

Important sub-directories are:

* /usr/local/vufind/config (Original blank config files)

* /usr/local/vufind/harvest (Files to configure OAI-PMH harvest)

* /usr/local/vufind/import (Files to configure Marc import and indexing)

* /usr/local/vufind/languages (Language files for display languages)

* /usr/local/vufind/local (Local files)

* /usr/local/vufind/local/cache (Cache files for cover images, languages, objects, and searchspecs)

* /usr/local/vufind/local/config (Copies of the /usr/local/vufind2/config files which have been customised for ELAR)

* /usr/local/vufind/module (VuFind application code)

* /usr/local/vufind/public (Files for public display on the website: logos, favicons, sitemaps, etc.)

* /usr/local/vufind/solr	(Solr config and indexes)

* /usr/local/vufind/solr/vufind/biblio (Bibliographic records indexes and configuration)

* /usr/local/vufind/solr/vufind/biblio/conf (Configuration for Solr's bibliographic records search and indexing)

* /usr/local/vufind/solr/vufind/alphabetical_browse (Alphabrowse records indexes)

* /usr/local/vufind/themes (HTML and CSS files for website display)

* /usr/local/vufind/themes/templates/elar (Highly customised files for ELAR display)

* /usr/local/vufind/util (Utilities scripts: optimization, sitemap building, dedupe, etc.)

### 1.3.1: Configuration files

.ini configuration files are where the configuration for VuFind is kept. These are not versioned in git because they contain details that should not be made publicly accessible. Most are kept in the local directory in the config folder: /usr/local/vufind/local/config/vufind/... The important ones are:

* /usr/local/vufind/local/config/vufind/config.ini (Main configuration file for VuFind. Contains most general configuration: theme, ILS, debug mode, proxy settings, languages, authentication, links to external sites, SFX link, browsing, alphabrowsing, and more.)

* /usr/local/vufind/local/config/vufind/Ams.ini	(Configuration file for connection to LAT's access management database.)

* /usr/local/vufind/local/config/vufind/facets.ini (Controls the facets / filters.)

* /usr/local/vufind/local/config/vufind/searches.ini (Controls search settings.)

* /usr/local/vufind/local/config/vufind/searchspecs.yaml	(Controls distribution of 'points' for searches and hence relevancy ranking. More information here: https://vufind.org/wiki/searches_customizing_tuning_adding)

* /usr/local/vufind/local/config/vufind/sitemap.ini (Controls how the sitemap is built.)

* /usr/local/vufind/local/config/vufind/searchbox.ini (Controls the searchbox: largely used to turn on the 'combined search' module.)

* /usr/local/vufind/harvest/oai.ini (Defines OAI-PMH connections. Used for connecting to LAT.)

### 1.3.2: Basic Apache control

VuFind runs on an Apache http server. The Apache configuration file can be found at /etc/httpd/conf.d/vufind.conf (which is a symlink to /usr/local/vufind/local/httpd-vufind.conf). Other Apache config files are at /etc/httpd/conf/ and /etc/httpd/conf.d/. 

To start Apache:

`service httpd start`

To restart Apache:

`service httpd restart`

### 1.5.3: Basic VuFind control

The main files to control VuFind are kept in the base /usr/local/vufind folder. This basic control is scripted using scripts in the vufind_integration_scripts repository but can also be run manually.

solr.sh starts and stops the application. import-marc.sh imports Marc files and starts the indexing process. index-alphabetic-browse.sh starts the alphabrowse indexing for the 'Browse Alphabetically' feature.

To start the VuFind application (user must be root):

`/usr/local/vufind/solr.sh start`

To stop the VuFind application (user must be root):

`/usr/local/vufind/solr.sh stop`

To restart the VuFind application (user must be root):

`/usr/local/vufind/solr.sh restart`

### 1.5.4: Solr interface

VuFind's Apache Solr has a separate interface for debugging issues with the Solr search index. It's available on port 8080.

This interface allows you to run direct queries on the Solr index to check how fields have been indexed, to delete the Solr indexes, and to view JVM performance statistics. It also logs errors so you can see which errors get thrown up during the indexing process. 

If you want to see the Solr interface, port 8080 will need to be opened. The larger SOAS firewall should prevent port 8080 from being seen outside the network so even opening this port will only make the interface available within SOAS' network.

### 1.5.5: Cache

Clearing the cache may sometimes be necessary to make changes appear (in the case of languages or certain feature changes). To clear the caches, run:

`cd /usr/local/vufind`

`rm -rf ./local/cache/*`
