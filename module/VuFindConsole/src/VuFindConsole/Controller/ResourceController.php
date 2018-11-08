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
class ResourceController extends AbstractBase
{

    /**
     * Updates the current_superseded flag
     *
     * @return empty
     */

    public function resourceuploadedcurrentAction()
    {

        Console::writeLine("Proccess started " . date('l jS \of F Y h:i:s A'));

    	$tableLatResources= $this->getTable('LatResources');

        foreach($tableLatResources->getDistintBundles() as $tlr) {
        	$bundle_id = $tlr['bundle_id'];
                $status = "Current";
                if ((strpos(file_get_contents('https://lat1.lis.soas.ac.uk/ds/oaiprovider/oai2?verb=GetRecord&metadataPrefix=imdi&identifier=' . $bundle_id), "Found no records")) !== false) {
                    $status = "Superseded";
                }
        	//$token = $tableLatResources->updateCurrentBundle($bundle_id, $status);
        	Console::writeLine("Pone status ". $status . " al bundle_id " .$bundle_id);
        	
        }
        Console::writeLine("Proccess finished." . date('l jS \of F Y h:i:s A'));
        return "";
    }

    public function resourcedownloadedcurrentAction()
    {

        Console::writeLine("Proccess started " . date('l jS \of F Y h:i:s A'));

    	$tableLatResources= $this->getTable('DownloadStat');

        foreach($tableLatResources->getDistintBundles() as $tlr) {
        	$bundle_id = $tlr['bundle_id'];
                $status = "Current";
                if ((strpos(file_get_contents('https://lat1.lis.soas.ac.uk/ds/oaiprovider/oai2?verb=GetRecord&metadataPrefix=imdi&identifier=' . $bundle_id), "Found no records")) !== false) {
                    $status = "Superseded";
                }
        	$token = $tableLatResources->updateCurrentBundle($bundle_id, $status);
        	
        }
        Console::writeLine("Proccess finished." . date('l jS \of F Y h:i:s A'));
        return "";
    }



    /**
     * Load LAT resources into VuFind database
     *
     * @return empty
     */
    public function resourceloadAction()
    {

        Console::writeLine("Proccess started " . date('l jS \of F Y h:i:s A'));

    	$tableLatResources= $this->getTable('LatResources');
        
        $mainArray = parse_ini_file('/usr/local/vufind/local/config/vufind/Ams.ini', true);
        $host= $mainArray['CatalogStructure']['host'];
        $port= $mainArray['CatalogStructure']['port'];
        $username= $mainArray['CatalogStructure']['username'];
        $password= $mainArray['CatalogStructure']['password'];
        $database= $mainArray['CatalogStructure']['database'];
        $final = array();

        $db = new PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $database,
            $username,
            $password
        );
        $resource = [];
        $finalResource = [];
        $current_year = date("Y");
    	$current_month = date('m', strtotime('-1 month'))+1;

        $sql = "SELECT distinct(vpath) ".
        "FROM corpusstructure, archiveobjects ".
        "where corpusstructure.nodeid = archiveobjects.nodeid ".
	//"and EXTRACT(MONTH FROM filetime) = '". $current_month . "' ".
        //"and EXTRACT(YEAR FROM filetime) = '". $current_year . "' ".
	"and vpath like '%MPI%MPI%MPI%MPI%'";
	//echo $sql ."\n";

        //$sql = "SELECT distinct(vpath) FROM corpusstructure, archiveobjects where corpusstructure.nodeid = archiveobjects.nodeid and vpath like '%MPI1035329%'";

	
        try {
            $itemSqlStmt = $db->prepare($sql);
            $itemSqlStmt->execute();

            $initial=$tableLatResources->setInitial();

            foreach ($itemSqlStmt->fetchAll() as $num_path => $rowItem1) {
		echo "Num vpaths: ".$num_path."\n";
            $setSpec= $rowItem1['vpath'];

            $current_year = date("Y");
    	    $current_month = date('m', strtotime('-1 month'))+1;
            $sql = "SELECT  corpusstructure.nodeid, filetime, replace(url,'https://lat1.lis.soas.ac.uk/','file:/lat/') as url " .
                "FROM corpusstructure, archiveobjects ".
                "where corpusstructure.nodeid = archiveobjects.nodeid ".
                "and vpath = '". $setSpec. "' "
		//.
                //"and EXTRACT(MONTH FROM filetime) = '". $current_month . "' ".
                //"and EXTRACT(YEAR FROM filetime) = '". $current_year . "' "
                ;
                //echo $sql."\n";
                $itemSqlStmt = $db->prepare($sql);
                $itemSqlStmt->execute();
                foreach ($itemSqlStmt->fetchAll() as $rowItem) {
                    $sql = "select aclstring from accessgroups where md5 like (select readrights from archiveobjects where nodeid=" . $rowItem['nodeid'] .")";
                    //echo $sql ."\n";
                    try {
                        $itemSqlStmt = $db->prepare($sql);
                        $itemSqlStmt->execute();
                        foreach ($itemSqlStmt->fetchAll() as $rowItem2) {
                            $autorised="0";
                            $anyuser="0";
                            $everybody="0";
                            //Check if the resource is free for everybody
                            $posEverybody = strpos($rowItem2['aclstring'], "everybody");
                            $posAnyAuthenticatedUser = strpos($rowItem2['aclstring'], "anyAuthenticatedUser");
                            if ($posEverybody !== false ) {
                                $everybody="1";
                            }
                            if ($posAnyAuthenticatedUser !== false ) {
                                $anyuser="1";
                            }

                        }
                    }
                    catch (PDOException $e) {
                        throw new ILSException($e->getMessage());
                    }
                    $resource[] = [
                        'nodeid' => $rowItem['nodeid'],
                         'url'  => $rowItem['url'],
                         'anyuser' => $anyuser,
                         'everybody' => $everybody
                     ];
                }

                $id = $this->getId($setSpec);
                //echo $id."\n";
                $sql = "select title from corpusnodes where nodeid = " . str_replace('MPI','',$id). ";";
                //echo $sql."\n";
                try {
                        $itemSqlStmt = $db->prepare($sql);
                        $itemSqlStmt->execute();
                        foreach ($itemSqlStmt->fetchAll() as $rowTitle) {
                            $bundle_title = $rowTitle['title'];
                        }
                    }
                    catch (PDOException $e) {
                        throw new ILSException($e->getMessage());
                    }

		$parentId = $this->getParentId($setSpec);
                $parentIdNumber = str_replace('MPI','',$parentId);
                
		$sql = "select title from corpusnodes where nodeid = " . str_replace('MPI','',$parentId). ";";
                //echo $sql."\n";
                try {
                        $itemSqlStmt = $db->prepare($sql);
                        $itemSqlStmt->execute();
                        foreach ($itemSqlStmt->fetchAll() as $rowTitle) {
                            $deposit_title = $rowTitle['title'];
                        }
                    }
                    catch (PDOException $e) {
                        throw new ILSException($e->getMessage());
                    }

                foreach($resource as $item){
                    $sql = "select 'mf' as prefix, id2, type, imdimd_mediafile.format, accessavailability, ".
                    "replace(replace(replace(imdimd_mediafile.resourcelink,'file:/lat/corpora/zzz-test-zzz/',''), ".
                    "'file:/lat/corpora/ELAR/',''),'https://lat1.lis.soas.ac.uk/corpora/ELAR/','') as path, ".
                    "media_info.size as size, duration, type, 'project_id, funding_body ".
                    "from imdimd_mediafile, media_info ".
                    "where  imdimd_mediafile.nodeid= media_info.nodeid ".
                    "and (imdimd_mediafile.resourcelink  = '". $item['url']."' ".
                    "or imdimd_mediafile.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ".
		    "and (media_info.resourcelink  = '". $item['url']."' ".
                    "or media_info.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ".
                    "UNION ".
                    "select 'wr' as prefix, id2, type, imdimd_writtenresource.format, accessavailability, ".
                    "replace(replace(replace(imdimd_writtenresource.resourcelink,'file:/lat/corpora/zzz-test-zzz/',''), ".
                    "'file:/lat/corpora/ELAR/',''),'https://lat1.lis.soas.ac.uk/corpora/ELAR/','') as path, ".
                    "media_info.size as size, duration, type, project_id, funding_body ".
                    "from imdimd_writtenresource, media_info ".
                    "where imdimd_writtenresource.nodeid= media_info.nodeid ".
                    "and (imdimd_writtenresource.resourcelink = '". $item['url']."' ".
                    "or imdimd_writtenresource.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ".
		    "and (media_info.resourcelink = '". $item['url']."' ".
                    "or media_info.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ";

                    $sql = "select 'mf' as prefix, id2, type, imdimd_mediafile.format, accessavailability, ".
                    "replace(replace(replace(imdimd_mediafile.resourcelink,'file:/lat/corpora/zzz-test-zzz/',''), ".
                    "'file:/lat/corpora/ELAR/',''),'https://lat1.lis.soas.ac.uk/corpora/ELAR/','') as path, ".
                    "media_info.size as size, duration, type, project_id, funding_body ".
                    "from imdimd_mediafile, media_info, elar_imdimd_extra ".
                    "where  imdimd_mediafile.nodeid= media_info.nodeid ".
                    "and elar_imdimd_extra.nodeid =  ". $parentIdNumber . " " .
                    "and (imdimd_mediafile.resourcelink  = '". $item['url']."' ".
                    "or imdimd_mediafile.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ".
                    "and (media_info.resourcelink  = '". $item['url']."' ".
                    "or media_info.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ".
                    "UNION ".
                    "select 'wr' as prefix, id2, type, imdimd_writtenresource.format, accessavailability, ".
                    "replace(replace(replace(imdimd_writtenresource.resourcelink,'file:/lat/corpora/zzz-test-zzz/',''), ".
                    "'file:/lat/corpora/ELAR/',''),'https://lat1.lis.soas.ac.uk/corpora/ELAR/','') as path, ".
                    "media_info.size as size, duration, type, project_id, funding_body ".
                    "from imdimd_writtenresource, media_info, elar_imdimd_extra ".
                    "where imdimd_writtenresource.nodeid= media_info.nodeid ".
                    "and elar_imdimd_extra.nodeid =  ". $parentIdNumber . " " .
                    "and (imdimd_writtenresource.resourcelink = '". $item['url']."' ".
                    "or imdimd_writtenresource.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ".
                    "and (media_info.resourcelink = '". $item['url']."' ".
                    "or media_info.resourcelink = '". str_replace('file:/lat/','https://lat1.lis.soas.ac.uk/',trim($item['url']))."' ) ";


                    //echo $sql ."\n";
                    try {
                        $path1="";
                        $path2="";
                        $itemSqlStmt = $db->prepare($sql);
                        $itemSqlStmt->execute();


                        foreach ($itemSqlStmt->fetchAll() as $rowItem3) {

                            $anyuser= $item['anyuser'];
                            $everybody = $item['everybody'];

                    	    if ($everybody!=false) $accessavailability='O';
                    	    else if ($anyuser!= false) $accessavailability='U';
                    	         else $accessavailability='S';

                            $id = $this->getId($setSpec);
                            if (strpos($rowItem3['path'],'corpora/Crossroads') === false)
                            	$token = $tableLatResources->saveResource($rowItem3['prefix']."-".$id."-". $rowItem3['id2'], $accessavailability, $rowItem['filetime'], $rowItem3['path'], $parentId, $rowItem3['size'], $rowItem3['duration'], $rowItem3['type'], $bundle_title, $deposit_title, $rowItem3['funding_body']);
                   	
                        }
                    }
                    catch (PDOException $e) {
                        throw new ILSException($e->getMessage());
                    }
                
                }
                $resource=array();

            }
	//SCB. Do not delete the deleted resources as it will be used for statistics
        $tableLatResources->clear();
        }
        catch (PDOException $e) {
            throw new ILSException($e->getMessage());
        }


            Console::writeLine("Proccess finished." . date('l jS \of F Y h:i:s A'));


        return "";
    }


    /**
     * Get the identifier
     *
     * @return string
     */	
     public function getId($setSpec) {
     	$output=$setSpec;
     	$pos=strrpos($setSpec,"#/");
     	
     	if ($pos>0) $output=substr($setSpec,$pos);
     	$output=str_replace("/","",$output);
     	$output=str_replace("#","",$output);
     	return $output;
     }

    /**
     * Get the bundle title
     *
     * @return string
     */
     public function getTitle($id) {

     }

    /**
     * Get the parent id
     *
     * @return string
     */
     /*public function getParentId($setSpec) {
     	$output=$setSpec;
     	$son=$this->getId($setSpec);
     	$newString = str_replace($son,"",$setSpec);
     	$newString= str_replace("#/#","", $newString);
     	$output=$this->getId($newString);
     	return $output;
     }*/


    public static function getParentId($setSpec)
    {
        $parent="";
        $setSpec=str_replace("#/",":",$setSpec);
        $firstPos=strpos($setSpec,":");
        $aux=substr($setSpec,$firstPos+1,strlen($setSpec));
        $secondPos = strpos($aux, ":");
        $aux2=substr($aux,$secondPos+1,strlen($aux));
        $hasDot=strpos($aux2,":");
        if ($hasDot>0) $parent=substr($aux2,0,$hasDot);
        else $parent=$aux2;

        return $parent;
    }

}
