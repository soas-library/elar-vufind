<?php
/**
 * CLI Controller Module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Controller
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_a_controller Wiki
 */
namespace VuFindConsole\Controller;
use Zend\Console\Console,

PDO, PDOException, VuFind\Exception\ILS as ILSException;
/**
 * This controller handles various command-line tools
 *
 * @category VuFind2
 * @package  Controller
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_a_controller Wiki
 */
class LatLogsController extends AbstractBase
{
    
    /**
     * Load LAT logs(Annex and Trova log statistics) into VuFind database
     *
     * @return empty
     */
    public function annexloadAction()
    {
    	
    	$mainArray = parse_ini_file('/usr/local/vufind/local/config/vufind/Ams.ini', true);
        $host= $mainArray['CatalogStructure']['host'];
        $port= $mainArray['CatalogStructure']['port'];
        $username= $mainArray['CatalogStructure']['username'];
        $password= $mainArray['CatalogStructure']['password'];
        $database= $mainArray['CatalogStructure']['database'];
    	
    	$db = new PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $database,
            $username,
            $password
        );

	$host= $mainArray['Ams2']['host'];
        $port= $mainArray['Ams2']['port'];
        $username= $mainArray['Ams2']['username'];
        $password= $mainArray['Ams2']['password'];
        $database= $mainArray['Ams2']['database'];
	
	$db_ams2 = new PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $database,
            $username,
            $password
        );

    	$yesterday = date('Y-m-d', strtotime(' -1 day'));
    	
    	$method1 = "/mnt/tomcat_logs/annex.log." . $yesterday;
    	//$method1 = "/usr/local/vufind2/latlogs/annexResourceAccess.log";
    	
    	Console::writeLine($method1);
    	
    	if(is_file($method1)){
    	
    	$depositor_name = 'Vacio';
    	$source = 'L';
    	$tableDownloadStat = $this->getTable('DownloadStat');
    	
    	//Console::writeLine($method1);
    	
	$file = fopen($method1, "r");
	
	while(! feof($file))
	  {
	  	
	  	$firstname = '';
	  	$surname = '';
	  	$email = '';
	  	$path = '';
	  	$filename = '';
	  	$internal_node_id = '';
	  	$date = '';
	  	$deposit_node_id = '';
	  	$res_type = '';
	  	$file_type = '';
                $funding_body = '';
                $project_id = '';

	  	
	  	$line = trim(fgets($file));
		if ($line != "") {
			$parts = explode(" ", $line);
			if(strcmp(strtolower($parts[4]), "accessing") == 0) {
				//Console::writeLine($parts[0]);
				//Console::writeLine($parts[1]);
				//Console::writeLine($parts[6]);
				//Console::writeLine($parts[7]);
				//Console::writeLine($parts[8]);
				//Console::writeLine($parts[9]);
				//Console::writeLine($parts[11]);
				
				$date = date("Y-m-d H:i:s", strtotime($parts[0] . " " . $parts[1]));
				//Console::writeLine($date);
				
				$string = ' ' . $parts[9];
				$ini = strpos($string, 'MPI');
				$ini += strlen('MPI');
				$len = strpos($string, '#', $ini) - $ini;
				$access_node_id = substr($string, $ini, $len);
				
				$string = ' ' . $parts[11];
				$ini = strpos($string, "userId='");
				$ini += strlen("userId='");
				$len = strpos($string, "'", $ini) - $ini;
				$email = substr($string, $ini, $len);
				
				//Console::writeLine($access_node_id);
				//Console::writeLine($email);
				
				$end_soas_email = '@soas.ac.uk';
				if (strcmp($email, '-') !== 0 && strpos($email, '@') == false)
					$email = $email . $end_soas_email;
				
				$tableUser = $this->getTable('User');
				$user = $tableUser->getByEmail($email);
				if (empty($user)) {
					$sql = "select uid, name, firstname from principal,public.user where principal.id=public.user.id and uid='" . $email . "';";

					try
                                	{
                                    		$itemSqlStmt = $db_ams2->prepare($sql);
                                    		$itemSqlStmt->execute();
                                    		foreach ($itemSqlStmt->fetchAll() as $rowUser) {
							$firstname = $rowUser['firstname'];
                                        		$surname = $rowUser['name'];
                                    		}
						if (empty($firstname) && empty($surname)) {
							// Anonymous download
							$firstname = 'Lat';
		                                	$surname = 'user';
						}
					}
                                	catch (PDOException $e) {
                                    		throw new ILSException($e->getMessage());
                                	}
				} else {
					$firstname = $user->firstname;
					$surname = $user->lastname;
				}
				
				//Console::writeLine($firstname);
				//Console::writeLine($surname);
				
				$id_sql = "SELECT vpath2, url FROM corpusstructure, archiveobjects"
			    	." where corpusstructure.nodeid = archiveobjects.nodeid"
			    	." and corpusstructure.nodeid =" . $access_node_id . ";";

                                $id_sql = "SELECT vpath2, url FROM corpusstructure, archiveobjects, elar_imdimd_extra"
                                ." where corpusstructure.nodeid = archiveobjects.nodeid"
                                ." elar_imdimd_extra.nodeid=vpath2"
                                ." and corpusstructure.nodeid =" . $access_node_id . ";";

			    	try
			    	{
			            $itemSqlStmt = $db->prepare($id_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	
			            	$url = $rowItem1['url'];
			            	$deposit_node_id = $rowItem1['vpath2'];
                                        $funding_body = $rowItem1['funding_body'];
                                        $project_id = $rowItem1['project_id'];
			            }
			            
			            if($url == null)
			            	$url = '-';
			            if($deposit_node_id == null)
			            	$deposit_node_id = '0';
			            
			            if (strcmp($url, '-') !== 0){
			            	$path = str_replace("https://lat1.lis.soas.ac.uk/corpora/ELAR/", "", $url);
			            	$path = str_replace("https://lat.lis.soas.ac.uk/corpora/ELAR/", "", $path);
			            	$path = str_replace("/corpora/ELAR/", "", $path);
			            	$filename = substr(strrchr($path, "/"), 1);
			            	
			            	if (strcmp(strtolower(substr(strrchr($filename, "."), 1)), "imdi") !== 0 && strcmp(strtolower(substr(strrchr($filename, "."), 1)), "cmdi") !== 0){
					            $type_sql = "select nodeid, type from imdimd_writtenresource"
					            ." where resourcelink like '%" . $path . 
					            "' UNION select nodeid, type from imdimd_mediafile where resourcelink like '%" . $path . 
					            "';";
					            
					            $itemSqlStmt = $db->prepare($type_sql);
					            $itemSqlStmt->execute();
					            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
					            	$file_type = $rowItem1['type'];
					            	$internal_node_id = $rowItem1['nodeid'];
					            }
					            // By default file_type is Document 
					            if($file_type == null)
					            	$file_type = 'Document';
					            if($internal_node_id == null)
					            	$internal_node_id = '0';
					            
					            $access_sql = "select aclstring from accessgroups"
					            ." where md5 like (select readrights"
					            ." from archiveobjects where nodeid=" . $access_node_id . ");";
					            
					            $itemSqlStmt = $db->prepare($access_sql);
					            $itemSqlStmt->execute();
					            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
					            	$aclstring= $rowItem1['aclstring'];
				                        $anyuser="0";
				                        $everybody="0";
				                        //Check if email is authorised
				                        //$posEmail = strpos($aclstring, $email);
				                        //Check if the resource is free for everybody
				                        $posEverybody = strpos($aclstring, "everybody");
				                        //Check if the resource is free for anyAuthenticatedUser
				                        $posAnyAuthenticatedUser = strpos($aclstring, "anyAuthenticatedUser");
				                        if ($posEverybody !== false ) {
				                            $everybody="1";
				                        }
				                        if ($posAnyAuthenticatedUser !== false /* ||$posEmail !== false */ ) {
				                            $anyuser="1";
				                        }
				                            
				                            if ($everybody!=false) $res_type='O';
			                    	    	    else if ($anyuser!= false) $res_type='U';
			                    	            else $res_type='S';
					            }
			            	
			            	/*Console::writeLine("--------------");
			            	Console::writeLine($firstname);
					Console::writeLine($surname);
					Console::writeLine($email);
					Console::writeLine($path);
					Console::writeLine($filename);
					Console::writeLine($internal_node_id);
					Console::writeLine($date);
					Console::writeLine($depositor_name);
					Console::writeLine($deposit_node_id);
					Console::writeLine($source);
					Console::writeLine($res_type);
					Console::writeLine($file_type);
					Console::writeLine("--------------");*/
					
					$tableDownloadStat->insertStat($firstname, $surname, $email, $path, $filename, $internal_node_id, $date, $depositor_name, $deposit_node_id, $source, $res_type, $file_type, $funding_body,0,0);
					Console::writeLine("Row inserted: " . $filename . " - " . $date);
					
			            	
			            	}
			            	
			            }
			            
			        }
			        catch (PDOException $e) {
			            throw new ILSException($e->getMessage());
			        }
				
			} else {
				//Console::writeLine('It is not a download.');
			}
		}
	  }
	
	fclose($file);
	
	}
    }
    
     /**
     * Load LAT logs(Trova log statistics) into VuFind database
     *
     * @return empty
     */
    public function trovaloadAction()
    {
    	
    	$mainArray = parse_ini_file('/usr/local/vufind/local/config/vufind/Ams.ini', true);
        $host= $mainArray['CatalogStructure']['host'];
        $port= $mainArray['CatalogStructure']['port'];
        $username= $mainArray['CatalogStructure']['username'];
        $password= $mainArray['CatalogStructure']['password'];
        $database= $mainArray['CatalogStructure']['database'];
    	
    	$db = new PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $database,
            $username,
            $password
        );
    	
	$host= $mainArray['Ams2']['host'];
        $port= $mainArray['Ams2']['port'];
        $username= $mainArray['Ams2']['username'];
        $password= $mainArray['Ams2']['password'];
        $database= $mainArray['Ams2']['database'];

        $db_ams2 = new PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $database,
            $username,
            $password
        );

    	$yesterday = date('Y-m-d', strtotime(' -1 day'));
    	
    	$method1 = "/mnt/tomcat_logs/trova.log." . $yesterday;

    	//$method1 = "/usr/local/vufind2/latlogs/annexResourceAccess.log";
    	
    	Console::writeLine($method1);
    	
    	if(is_file($method1)){
    	
    	$depositor_name = 'Vacio';
    	$source = 'L';
    	$tableDownloadStat = $this->getTable('DownloadStat');
    	
    	//Console::writeLine($method1);
    	
	$file = fopen($method1, "r");
	
	while(! feof($file))
	  {

	  	$firstname = '';
	  	$surname = '';
	  	$email = '';
	  	$path = '';
	  	$filename = '';
	  	$internal_node_id = '';
	  	$date = '';
	  	$deposit_node_id = '';
	  	$res_type = '';
	  	$file_type = '';
                $funding_body = '';
                $project_id = '';

	  	
	  	$line = trim(fgets($file));
		if ($line != "") {
			$parts = explode(" ", $line);
			if(strcmp(strtolower($parts[4]), "accessing") == 0) {

				//Console::writeLine($parts[0]);
				//Console::writeLine($parts[1]);
				//Console::writeLine($parts[6]);
				//Console::writeLine($parts[7]);
				//Console::writeLine($parts[8]);
				//Console::writeLine($parts[9]);
				//Console::writeLine($parts[11]);
				
				$date = date("Y-m-d H:i:s", strtotime($parts[0] . " " . $parts[1]));
				//Console::writeLine($date);
				
				$string = ' ' . $parts[9];
				$ini = strpos($string, 'MPI');
				$ini += strlen('MPI');
				$len = strpos($string, '#', $ini) - $ini;
				$access_node_id = substr($string, $ini, $len);
				
				$string = ' ' . $parts[11];
				$ini = strpos($string, "userId='");
				$ini += strlen("userId='");
				$len = strpos($string, "'", $ini) - $ini;
				$email = substr($string, $ini, $len);
				
				//Console::writeLine($access_node_id);
				//Console::writeLine($email);
				
				$end_soas_email = '@soas.ac.uk';
				if (strcmp($email, '-') !== 0 && strpos($email, '@') == false)
					$email = $email . $end_soas_email;
				
				$tableUser = $this->getTable('User');
				$user = $tableUser->getByEmail($email);
				if (empty($user)) {
					$sql = "select uid, name, firstname from principal,public.user where principal.id=public.user.id and uid='" . $email . "';";

                                        try
                                        {
                                                $itemSqlStmt = $db_ams2->prepare($sql);
                                                $itemSqlStmt->execute();
                                                foreach ($itemSqlStmt->fetchAll() as $rowUser) {
                                                        $firstname = $rowUser['firstname'];
                                                        $surname = $rowUser['name'];
                                                }
                                                if (empty($firstname) && empty($surname)) {
                                                        // Anonymous download
                                                        $firstname = 'Lat';
                                                        $surname = 'user';
                                                }
                                        }
                                        catch (PDOException $e) {
                                                throw new ILSException($e->getMessage());
                                        }
				} else {
					$firstname = $user->firstname;
					$surname = $user->lastname;
				}
				
				//Console::writeLine($firstname);
				//Console::writeLine($surname);
				
				$id_sql = "SELECT vpath2, url FROM corpusstructure, archiveobjects"
			    	." where corpusstructure.nodeid = archiveobjects.nodeid"
			    	." and corpusstructure.nodeid =" . $access_node_id . ";";


                                $id_sql = "SELECT vpath2, url FROM corpusstructure, archiveobjects, elar_imdimd_extra"
                                ." where corpusstructure.nodeid = archiveobjects.nodeid"
                                ." elar_imdimd_extra.nodeid=vpath2"
                                ." and corpusstructure.nodeid =" . $access_node_id . ";";



			    	try
			    	{
			            $itemSqlStmt = $db->prepare($id_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	
			            	$url = $rowItem1['url'];
			            	$deposit_node_id = $rowItem1['vpath2'];
                                        $funding_body = $rowItem1['funding_body'];
                                        $project_id = $rowItem1['project_id'];
			            }
			            
			            if($url == null)
			            	$url = '-';
			            if($deposit_node_id == null)
			            	$deposit_node_id = '0';
			            
			            if (strcmp($url, '-') !== 0){
			            	$path = str_replace("https://lat1.lis.soas.ac.uk/corpora/ELAR/", "", $url);
			            	$path = str_replace("https://lat.lis.soas.ac.uk/corpora/ELAR/", "", $path);
			            	$path = str_replace("/corpora/ELAR/", "", $path);
			            	$filename = substr(strrchr($path, "/"), 1);
			            	
			            	if (strcmp(strtolower(substr(strrchr($filename, "."), 1)), "imdi") !== 0 && strcmp(strtolower(substr(strrchr($filename, "."), 1)), "cmdi") !== 0){
					            $type_sql = "select nodeid, type from imdimd_writtenresource"
					            ." where resourcelink like '%" . $path . 
					            "' UNION select nodeid, type from imdimd_mediafile where resourcelink like '%" . $path . 
					            "';";
					            
					            $itemSqlStmt = $db->prepare($type_sql);
					            $itemSqlStmt->execute();
					            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
					            	$file_type = $rowItem1['type'];
					            	$internal_node_id = $rowItem1['nodeid'];
					            }
					            //By default file_type is document
					            if($file_type == null)
					            	$file_type = 'Document';
					            if($internal_node_id == null)
					            	$internal_node_id = '0';
					            
					            $access_sql = "select aclstring from accessgroups"
					            ." where md5 like (select readrights"
					            ." from archiveobjects where nodeid=" . $access_node_id . ");";
					            
					            $itemSqlStmt = $db->prepare($access_sql);
					            $itemSqlStmt->execute();
					            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
					            	$aclstring= $rowItem1['aclstring'];
				                        $anyuser="0";
				                        $everybody="0";
				                        //Check if email is authorised
				                        //$posEmail = strpos($aclstring, $email);
				                        //Check if the resource is free for everybody
				                        $posEverybody = strpos($aclstring, "everybody");
				                        //Check if the resource is free for anyAuthenticatedUser
				                        $posAnyAuthenticatedUser = strpos($aclstring, "anyAuthenticatedUser");
				                        if ($posEverybody !== false ) {
				                            $everybody="1";
				                        }
				                        if ($posAnyAuthenticatedUser !== false /* ||$posEmail !== false */ ) {
				                            $anyuser="1";
				                        }
				                            
				                            if ($everybody!=false) $res_type='O';
			                    	    	    else if ($anyuser!= false) $res_type='U';
			                    	            else $res_type='S';
					            }
			            	
			            	/*Console::writeLine("--------------");
			            	Console::writeLine($firstname);
					Console::writeLine($surname);
					Console::writeLine($email);
					Console::writeLine($path);
					Console::writeLine($filename);
					Console::writeLine($internal_node_id);
					Console::writeLine($date);
					Console::writeLine($depositor_name);
					Console::writeLine($deposit_node_id);
					Console::writeLine($source);
					Console::writeLine($res_type);
					Console::writeLine($file_type);
					Console::writeLine("--------------");*/
					
					$tableDownloadStat->insertStat($firstname, $surname, $email, $path, $filename, $internal_node_id, $date, $depositor_name, $deposit_node_id, $source, $res_type, $file_type, $funding_body, 0, 0);
					Console::writeLine("Row inserted: " . $filename . " - " . $date);
					
			            	
			            	}
			            	
			            }
			            
			        }
			        catch (PDOException $e) {
			            throw new ILSException($e->getMessage());
			        }
				
			} else {
				//Console::writeLine('It is not a download.');
			}
		}
	  }
	
	fclose($file);
	
	}
    }
    
    /**
     * Load LAT logs(ASV log statistics) into VuFind database
     *
     * @return empty
     */
    public function asvloadAction()
    {
    	
    	$mainArray = parse_ini_file('/usr/local/vufind/local/config/vufind/Ams.ini', true);
        $host= $mainArray['CatalogStructure']['host'];
        $port= $mainArray['CatalogStructure']['port'];
        $username= $mainArray['CatalogStructure']['username'];
        $password= $mainArray['CatalogStructure']['password'];
        $database= $mainArray['CatalogStructure']['database'];
    	
    	$db = new PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $database,
            $username,
            $password
        );
    	
	$host= $mainArray['Ams2']['host'];
        $port= $mainArray['Ams2']['port'];
        $username= $mainArray['Ams2']['username'];
        $password= $mainArray['Ams2']['password'];
        $database= $mainArray['Ams2']['database'];

        $db_ams2 = new PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $database,
            $username,
            $password
        );

    	$yesterday = date('Y.m.d', strtotime(' -1 day'));
    	
    	$method1 = "/mnt/apache_logs/ssl_request.log-" . $yesterday;
    	//Method 1, 2 y 3
    	//$method1 = "/mnt/apache_logs/ssl_request.log-2018.10.31";
    	$method1 = "/usr/local/vufind/latlogs/ssl_request.log-2018.09.03";
    	//$method1 = "/usr/local/vufind/latlogs/ssl_request.log-2018.09.03";
    	
    	Console::writeLine($method1);
	
	if(is_file($method1)){
	
    	$depositor_name = 'Vacio';
    	$source = 'L';
    	$tableDownloadStat = $this->getTable('DownloadStat');
    	
	$file = fopen($method1, "r");
	
	while(! feof($file))
	  {
	  	
	  	$firstname = '';
	  	$surname = '';
	  	$email = '';
	  	$path = '';
	  	$filename = '';
	  	$internal_node_id = '';
	  	$date = '';
	  	$deposit_node_id = '';
	  	$res_type = '';
	  	$file_type = '';
                $funding_body = '';
                $project_id = '';
                $size=0;
                $duration = 0;

		$url = '';
	  	
	  	$line = trim(fgets($file));
		if ($line != "") {
			
			$parts = explode(" ", $line);
			//Console::writeLine(print_r($line));
			$paths = explode(".", $parts[9]);
			$extension = $paths[count($paths)-1];
			//Console::writeLine($extension);
			//Console::writeLine($paths[count($paths)-1]);
			if(strcmp($parts[2], "127.0.0.1") !== 0 && strpos($parts[8], "GET") !== false && strpos($parts[9], "/corpora/ELAR/") !== false && strcmp(strtolower($extension), "imdi") !== 0 && strcmp(strtolower($extension), "cmdi") !== 0 && strcmp($extension, "cmdi&outFormat=imdi") !== 0 && strpos($parts[9], "/jkc/lamus/lamscontroller;jsessionid") === false) {


				//Console::writeLine($extension);
				//Console::writeLine("It is a download.");
                                $downloadIndicator = $parts[13];
                                $attachment = $parts[14];
                                $posAttachment = strpos($attachment, "attachment");
                                $httpStatus = $parts[12];
				$parts[0] = str_replace("[", "", $parts[0]);
				$fecha = str_replace("/", " ", strstr($parts[0], ':', true));
				$hora = substr(strstr($parts[0], ':'), 1);
							
				$date = date("Y-m-d H:i:s", strtotime($fecha . " " . $hora));
				
				$email = $parts[4];
				$end_soas_email = '@soas.ac.uk';
				if (strcmp($email, '-') !== 0 && strpos($email, '@') == false)
					$email = $email . $end_soas_email;
				$path = str_replace("/corpora/ELAR/", "", $parts[9]);
				$filename = substr(strrchr($parts[9], "/"), 1);
				
				$tableUser = $this->getTable('User');
				$user = $tableUser->getByEmail($email);
				if (empty($user)) {
					$sql = "select uid, name, firstname from principal,public.user where principal.id=public.user.id and uid='" . $email . "';";
					try
                                        {
                                                $itemSqlStmt = $db_ams2->prepare($sql);
                                                $itemSqlStmt->execute();
                                                foreach ($itemSqlStmt->fetchAll() as $rowUser) {
                                                        $firstname = $rowUser['firstname'];
                                                        $surname = $rowUser['name'];
                                                }
                                                if (empty($firstname) && empty($surname)) {
                                                        // Anonymous download
                                                        $firstname = 'Lat';
                                                        $surname = 'user';
                                                }
                                        }
                                        catch (PDOException $e) {
                                                throw new ILSException($e->getMessage());
                                        }
				} else {
					$firstname = $user->firstname;
					$surname = $user->lastname;
				}
					
				$type_sql = "select nodeid, type from imdimd_writtenresource where resourcelink like '%" . $path
				 . "' UNION select nodeid, type from imdimd_mediafile where resourcelink like '%" . $path 
				 . "';";
			    	$id_sql = "SELECT corpusstructure.nodeid, vpath2 FROM corpusstructure, archiveobjects "
			    	."where corpusstructure.nodeid = archiveobjects.nodeid and url like '%" . $parts[9] 
			    	. "';";


                                $id_sql = "SELECT corpusstructure.nodeid, vpath2, funding_body, project_id FROM corpusstructure, archiveobjects, elar_imdimd_extra "
                                ."where corpusstructure.nodeid = archiveobjects.nodeid and url like '%" . $parts[9]
                                . "' and elar_imdimd_extra.nodeid=vpath2;";

                                //echo $type_sql;
                                //echo "------------";
                                //echo $id_sql;
			    	
			    	try
			    	{
			            $itemSqlStmt = $db->prepare($type_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	$file_type = $rowItem1['type'];
			            	$internal_node_id = $rowItem1['nodeid'];
			            }
			            
			            if($file_type == null)
			            	$file_type = '-';
			            if($internal_node_id == null)
			            	$internal_node_id = '0';
			            
			            $itemSqlStmt = $db->prepare($id_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	$access_node_id = $rowItem1['nodeid'];
			            	$deposit_node_id = $rowItem1['vpath2'];
                                        $funding_body = $rowItem1['funding_body'];
                                        $project_id = $rowItem1['project_id'];
			            }
			            
			            if($access_node_id == null)
			            	$access_node_id = '-';
			            if($deposit_node_id == null)
			            	$deposit_node_id = '0';
			            
			            $access_sql = "select aclstring from accessgroups where md5 like (select readrights from archiveobjects where nodeid=" . $access_node_id . ");";
			            
			            $itemSqlStmt = $db->prepare($access_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	$aclstring= $rowItem1['aclstring'];
		                        $anyuser="0";
		                        $everybody="0";
		                        //Check if email is authorised
		                        //$posEmail = strpos($aclstring, $email);
		                        //Check if the resource is free for everybody
		                        $posEverybody = strpos($aclstring, "everybody");
		                        //Check if the resource is free for anyAuthenticatedUser
		                        $posAnyAuthenticatedUser = strpos($aclstring, "anyAuthenticatedUser");
		                        if ($posEverybody !== false ) {
		                            $everybody="1";
		                        }
		                        if ($posAnyAuthenticatedUser !== false /* ||$posEmail !== false */ ) {
		                            $anyuser="1";
		                        }
		                            
		                            if ($everybody!=false) $res_type='O';
	                    	    	    else if ($anyuser!= false) $res_type='U';
	                    	            else $res_type='S';
			            }
			            
			        }
			        catch (PDOException $e) {
			            throw new ILSException($e->getMessage());
			        }
			
			/*Console::writeLine("--------------");
			Console::writeLine($firstname);
			Console::writeLine($surname);
			Console::writeLine($email);
			Console::writeLine($path);
			Console::writeLine($filename);
			Console::writeLine($internal_node_id);
			Console::writeLine($date);
			Console::writeLine($depositor_name);
			Console::writeLine($deposit_node_id);
			Console::writeLine($source);
			Console::writeLine($res_type);
			Console::writeLine($file_type);
			Console::writeLine("--------------");*/

                        if($res_type === 'U' && $email !== '-' || $res_type === 'S' && $email !== '-' || $res_type === 'O'){
Console::writeLine("PREVIO ATTACHMENT" . $parts[14]);
                            if (($downloadIndicator=='+' || $downloadIndicator=='-') && $httpStatus[0]=='2' && $posAttachment !== false){
			    	try
			    	{
                                    $size=0;
                                    $duration=0;
			            $sqlSizeDuration="select CASE WHEN size IS NULL THEN 0 ELSE size END as size, CASE WHEN duration IS NULL THEN 0 ELSE duration END as duration  from media_info ".
			            "where nodeid = ". $internal_node_id ." ".
			            "and replace(resourcelink,'file:/lat/corpora/ELAR/','') = '". $path."'";
			            $itemSqlStmt = $db->prepare($sqlSizeDuration);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowSizeDuration) {
			            	$size = $rowSizeDuration['size'];
			            	$duration = $rowSizeDuration['duration'];
			            }
			        }
			        catch (PDOException $e) {
			            throw new ILSException($e->getMessage());
			        }
                                $tableDownloadStat->insertStat($firstname, $surname, $email, $path, $filename, $internal_node_id, $date, $depositor_name, $deposit_node_id, $source, $res_type, $file_type, $funding_body, $size, $duration);
                                Console::writeLine("Row inserted Normal: " . $filename . " - " . $date . " and status ". $httpStatus . " and code ". $downloadIndicator);
Console::writeLine("ATTACHMENT" . $parts[14]);
                            } else {
                                Console::writeLine("Aborted download: " . $filename . " - " . $date . " and status ". $httpStatus . " and code ". $downloadIndicator);
                            }
                        }

			
			
			} else {
				//Console::writeLine("It is not a download.");
			}
			
		}
	  }
	
	fclose($file);
    	}
    	$method1 = "/mnt/apache_logs/ssl_request.log-" . $yesterday;
    	//Method 1, 2 y 3

    	//$method1 = "/usr/local/vufind/latlogs/metadatabrowser-nodeactions.log.2018-09-20";
        $yesterdayChange = str_replace(".","-",$yesterday);
        $method1 = "/mnt/tomcat_logs/metadatabrowser-nodeactions/metadatabrowser-nodeactions.log." . $yesterdayChange;
        $method1 = "/mnt/tomcat_logs/metadatabrowser-nodeactions/metadatabrowser-nodeactions.log";



    	Console::writeLine($method1);
	
	if(is_file($method1)){
	
    	$depositor_name = 'Vacio';
    	$source = 'N';
    	$tableDownloadStat = $this->getTable('DownloadStat');
    	
	$file = fopen($method1, "r");
	
	while(! feof($file))
	  {
	  	$firstname = '';
	  	$surname = '';
	  	$email = '';
	  	$path = '';
	  	$filename = '';
	  	$internal_node_id = '';
	  	$date = '';
	  	$deposit_node_id = '';
	  	$res_type = '';
	  	$file_type = '';
                $funding_body = '';
                $project_id = '';
                $size=0;
                $duration = 0;

		$url = '';
	  	
	  	$line = trim(fgets($file));
           
		if ($line != "") {
			
			$parts = explode(" - ", $line);
			//Console::writeLine($extension);
			//Console::writeLine($parts[0]);
			$fecha = substr($parts[0],0,19);
			$parts2 = explode(" ", $parts[1]);
			$comienzo = $parts2[0];
                        $pos = strpos($line, ", denied");


			if(($comienzo =="Download" || $comienzo =="DownloadAll") && $pos === false ) {
			        //Console::writeLine($fecha);
				$node=$comienzo = $parts2[1];
				$node=str_replace("node:","",$node);
				$node=str_replace(",","",$node);
				$email=$comienzo = $parts2[2];
				$email=str_replace(",","",$email);
				//Console::writeLine($node);
				//Console::writeLine($email);
				//Console::writeLine($extension);
				//Console::writeLine("It is a download.");
							
				//$date = date("Y-m-d H:i:s", strtotime($fecha . " " . $hora));
				$date = date("Y-m-d H:i:s", strtotime($fecha));
				

				$end_soas_email = '@soas.ac.uk';
				if (strcmp($email, '-') !== 0 && strpos($email, '@') == false)
					$email = $email . $end_soas_email;

				
				$tableUser = $this->getTable('User');
				$user = $tableUser->getByEmail($email);
				if (empty($user)) {
					$sql = "select uid, name, firstname from principal,public.user where principal.id=public.user.id and uid='" . $email . "';";
					try
                                        {
                                                $itemSqlStmt = $db_ams2->prepare($sql);
                                                $itemSqlStmt->execute();
                                                foreach ($itemSqlStmt->fetchAll() as $rowUser) {
                                                        $firstname = $rowUser['firstname'];
                                                        $surname = $rowUser['name'];
                                                }
                                                if (empty($firstname) && empty($surname)) {
                                                        // Anonymous download
                                                        $firstname = 'Lat';
                                                        $surname = 'user';
                                                }
                                        }
                                        catch (PDOException $e) {
                                                throw new ILSException($e->getMessage());
                                        }
				} else {
					$firstname = $user->firstname;
					$surname = $user->lastname;
				}
				//Console::writeLine($firstname);
				
				







                                $id_sql = "SELECT corpusstructure.nodeid, vpath2, funding_body, project_id, url FROM corpusstructure, archiveobjects, elar_imdimd_extra "
                                ."where corpusstructure.nodeid = archiveobjects.nodeid and archiveobjects.nodeid = '" . $node
                                . "' and elar_imdimd_extra.nodeid=vpath2;";
                                 //Console::writeLine($id_sql);
                                //echo $type_sql;
                                //echo "------------";
                                //echo $id_sql;
			    	
			    	try
			    	{
			            $itemSqlStmt = $db->prepare($id_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	$access_node_id = $rowItem1['nodeid'];
			            	$deposit_node_id = $rowItem1['vpath2'];
                                        $funding_body = $rowItem1['funding_body'];
                                        $project_id = $rowItem1['project_id'];
                                        $path = $rowItem1['url'];
			            }
			            //Console::writeLine($access_node_id."--".$deposit_node_id."--".$funding_body."--".$project_id."--".$path);
			            if($access_node_id == null)
			            	$access_node_id = '-';
			            if($deposit_node_id == null)
			            	$deposit_node_id = '0';
                                    $originalPath = $path;
			            $path = str_replace("https://lat1.lis.soas.ac.uk/corpora/ELAR/", "", $path);
			            $path = str_replace("https://lat.lis.soas.ac.uk/corpora/ELAR/", "", $path);
			            $path = str_replace("/corpora/ELAR/", "", $path);
                                    $filename = substr(strrchr($path, "/"), 1);


				    $type_sql = "select nodeid, type from imdimd_writtenresource where resourcelink like '%" . $path
				     . "' UNION select nodeid, type from imdimd_mediafile where resourcelink like '%" . $path 
				     . "';";
				    //Console::writeLine($type_sql);
			            $itemSqlStmt = $db->prepare($type_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	$file_type = $rowItem1['type'];
			            	$internal_node_id = $rowItem1['nodeid'];
			            }
			            
			            if($file_type == null)
			            	$file_type = '-';
			            if($internal_node_id == null)
			            	$internal_node_id = '0';
			            

			            
			            $access_sql = "select aclstring from accessgroups where md5 like (select readrights from archiveobjects where nodeid=" . $access_node_id . ");";
			            
			            $itemSqlStmt = $db->prepare($access_sql);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowItem1) {
			            	$aclstring= $rowItem1['aclstring'];
		                        $anyuser="0";
		                        $everybody="0";
		                        //Check if email is authorised
		                        //$posEmail = strpos($aclstring, $email);
		                        //Check if the resource is free for everybody
		                        $posEverybody = strpos($aclstring, "everybody");
		                        //Check if the resource is free for anyAuthenticatedUser
		                        $posAnyAuthenticatedUser = strpos($aclstring, "anyAuthenticatedUser");
		                        if ($posEverybody !== false ) {
		                            $everybody="1";
		                        }
		                        if ($posAnyAuthenticatedUser !== false /* ||$posEmail !== false */ ) {
		                            $anyuser="1";
		                        }
		                            
		                            if ($everybody!=false) $res_type='O';
	                    	    	    else if ($anyuser!= false) $res_type='U';
	                    	            else $res_type='S';
			            }
			            
			        }
			        catch (PDOException $e) {
			            throw new ILSException($e->getMessage());
			        }
			
			/*Console::writeLine("--------------");
			Console::writeLine($firstname);
			Console::writeLine($surname);
			Console::writeLine($email);
			Console::writeLine($path);
			Console::writeLine($filename);
			Console::writeLine($internal_node_id);
			Console::writeLine($date);
			Console::writeLine($depositor_name);
			Console::writeLine($deposit_node_id);
			Console::writeLine($source);
			Console::writeLine($res_type);
			Console::writeLine($file_type);
			Console::writeLine("--------------");*/

                        if($res_type === 'U' && $email !== '-' || $res_type === 'S' && $email !== '-' || $res_type === 'O'){
			    	try
			    	{
                                    $size=0;
                                    $duration=0;
			            $sqlSizeDuration="select CASE WHEN size IS NULL THEN 0 ELSE size END as size, CASE WHEN duration IS NULL THEN 0 ELSE duration END as duration  from media_info ".
			            "where nodeid = ". $internal_node_id ." ".
			            "and replace(resourcelink,'file:/lat/corpora/ELAR/','') = '". $path."'";
			            $itemSqlStmt = $db->prepare($sqlSizeDuration);
			            $itemSqlStmt->execute();
			            foreach ($itemSqlStmt->fetchAll() as $rowSizeDuration) {
			            	$size = $rowSizeDuration['size'];
			            	$duration = $rowSizeDuration['duration'];
			            }
			        }
			        catch (PDOException $e) {
			            throw new ILSException($e->getMessage());
			        }
                                $tableDownloadStat->insertStat($firstname, $surname, $email, $originalPath , $filename, $internal_node_id, $date, $depositor_name, $deposit_node_id, $source, $res_type, $file_type, $funding_body, $size, $duration);
                                Console::writeLine("Row inserted Normal: " . $filename . " - " . $date );
                            } else {
                                Console::writeLine("Aborted download: " . $filename . " - " . $date );
                        }

			
			
			} else {
				//Console::writeLine("It is not a download.");
			}
			
		}
	  }
	
	fclose($file);
    	}



    }

     /**
     * Load LAT logs(Annex user logins statistics) into VuFind database
     *
     * @return empty
     */
    public function annexloginsloadAction()
    {
    	
    	$yesterday = date('Y-m-d', strtotime(' -1 day'));
    	
    	$method1 = "/mnt/tomcat_logs/annex.log." . $yesterday;
    	//$method1 = "/usr/local/vufind2/latlogs/annexLogins.log";
    	
    	Console::writeLine($method1);
    	
    	if(is_file($method1)){
    	
    	$annexAndTrovaUserSession = $this->getTable('AnnexAndTrovaUserSession');
    	
	$file = fopen($method1, "r");
	
	while(! feof($file))
	  {

	  	$line = trim(fgets($file));
		if ($line != "") {
			
			//Console::writeLine($line);
			
			$parts = explode(" ", $line);
			//Console::writeLine(count($parts));
			if (count($parts) > 6) {
				if(strcmp(strtolower($parts[6]), "addsession:") == 0) {
					//Console::writeLine($parts[0]);
					//Console::writeLine($parts[1]);
					//Console::writeLine($parts[8]);
					$user = str_replace("user=","", $parts[8]);
					
					$time_parts = explode(",", $parts[1]);
					$parts[1] = $time_parts[0];
					
					$date = date("Y-m-d H:i:s", strtotime($parts[0] . " " . $parts[1]));
					//Console::writeLine($date);
					
					$annexAndTrovaUserSession->saveAnnexAndTrovaUserSession($date, $user);
					Console::writeLine("Row inserted: " . $user . " - " . $date);
					
				} else {
					//Console::writeLine('It is not a user session.');
				}
			}
		}
	  }
	
	fclose($file);
	}




    }

     /**
     * Load LAT logs(Trova user logins statistics) into VuFind database
     *
     * @return empty
     */
    public function trovaloginsloadAction()
    {
    	
    	$yesterday = date('Y-m-d', strtotime(' -1 day'));
    	
    	$method1 = "/mnt/tomcat_logs/trova.log." . $yesterday;
    	//$method1 = "/usr/local/vufind2/latlogs/trovaLogins.log";
    	
    	Console::writeLine($method1);
    	
    	if(is_file($method1)){
    	
    	$annexAndTrovaUserSession = $this->getTable('AnnexAndTrovaUserSession');
    	
	$file = fopen($method1, "r");
	
	while(! feof($file))
	  {

	  	$line = trim(fgets($file));
		if ($line != "") {
			
			//Console::writeLine($line);
			
			$parts = explode(" ", $line);
			//Console::writeLine(count($parts));
			if (count($parts) > 6) {
				if(strcmp(strtolower($parts[6]), "addsession:") == 0) {
					//Console::writeLine($parts[0]);
					//Console::writeLine($parts[1]);
					//Console::writeLine($parts[8]);
					$user = str_replace("user=","", $parts[8]);
					
					$time_parts = explode(",", $parts[1]);
					$parts[1] = $time_parts[0];
					
					$date = date("Y-m-d H:i:s", strtotime($parts[0] . " " . $parts[1]));
					//Console::writeLine($date);
					
					$annexAndTrovaUserSession->saveAnnexAndTrovaUserSession($date, $user);
					Console::writeLine("Row inserted: " . $user . " - " . $date);
					
				} else {
					//Console::writeLine('It is not a user session.');
				}
			}
		}
	  }
	
	fclose($file);
	
	}
    }

     /**
     * Load LAT logs(ASV user logins statistics) into VuFind database
     *
     * @return empty
     */
    public function asvloginsloadAction()
    {

        $yesterday = date('Y-m-d', strtotime(' -1 day'));

	$method1 = "/mnt/apache_logs/ssl_request.log-" . $yesterday;
        //Method 1, 2 y 3
        //$method1 = "/usr/local/vufind2/latlogs/asvLogins.log";

        Console::writeLine($method1);

        if(is_file($method1)){

        $annexAndTrovaUserSession = $this->getTable('AnnexAndTrovaUserSession');

        $file = fopen($method1, "r");
        
	$arrDates = array();
	$arrUserSessions = array();
	$arrEmails = array();	

        while(! feof($file))
          {

                $line = trim(fgets($file));
                if ($line != "") {

                        //Console::writeLine($line);
                        
                        $parts = explode(" ", $line);
                        //Console::writeLine(count($parts));
	
			if (count($parts) > 9) {
                                if(strcmp(strtolower($parts[9]), "/ds/asv/login.jsp?login=1") == 0) {
                                        
					//Console::writeLine($parts[0]);
					//Console::writeLine($parts[5]);
					
					if (!in_array($parts[5], $arrUserSessions)) {
						$arrUserSessions[] = $parts[5];
						$parts[0] = str_replace("[","", $parts[0]);
 	                                        $parts[0] = str_replace("/","-",$parts[0]);
        	                                $time_parts = explode(":", $parts[0]);

                   	                        $date = date("Y-m-d H:i:s", strtotime($time_parts[0] . " " . $time_parts[1].':'.$time_parts[2].':'.$time_parts[3]));
                           	                //Console::writeLine($date);

                                        	$arrDates[] = $date;
					}

                                } else if(strcmp(strtolower($parts[9]), "/ds/asv") == 0){
                                        //Console::writeLine($parts[3]);
                                        //Console::writeLine($parts[5]);
					
					if (in_array($parts[5], $arrUserSessions)) {
                                                $clave = array_search($parts[5], $arrUserSessions);
						//Console::writeLine($clave);
						if(empty($arrEmails[$clave])){
							$arrEmails[$clave] = $parts[3];
						}
                                        }

				} else {
					//Console::writeLine('It is not a user session.');
				}
                        }
                }
          }
        
        fclose($file);
	
	Console::writeLine(count($arrDates));
        Console::writeLine(count($arrUserSessions));
        Console::writeLine(count($arrEmails));
	Console::writeLine('---------------------------');
	
	foreach ($arrEmails as $pos => $email) {
		Console::writeLine($arrUserSessions[$pos]);
		Console::writeLine($arrDates[$pos]);
		Console::writeLine($email);
		$annexAndTrovaUserSession->saveAnnexAndTrovaUserSession($arrDates[$pos], $email);
                Console::writeLine("Row inserted: " . $arrDates[$pos] . " - " . $email);
		Console::writeLine('*********************************');
	}	

        }
    }

}
