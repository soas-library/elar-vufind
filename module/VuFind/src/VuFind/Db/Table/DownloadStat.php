<?php
/**
 * Table Definition for tags
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
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace VuFind\Db\Table;
use Zend\Db\Sql\Expression, Zend\Db\Sql\Select;

/**
 * Table Definition for donwload_stat
 *
 * @category VuFind2
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class DownloadStat extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        ini_set('memory_limit', '2048M');
        parent::__construct('download_stat', 'VuFind\Db\Row\DownloadStat');
    }

    /**
     * 
     */
    public function insertStat($firstname, $surname, $userID, $path, $filename, $internaNodeID, $date, $depositorName, $depositNodeID, $source, $resource_type, $file_type, $funding_body, $size, $duration)
    {
    	if ($userID=="") {$firstname= "Open"; $surname="Download";}


	$day=ltrim(substr($date,8,2),0);
	$month=ltrim(substr($date,5,2),0);
	$year=substr($date,0,4);
	$hour=ltrim(substr($date,11,2),0);

	$sql = "select count(*) as contador  from download_stat where surname='".$surname."' and firstname='".$firstname."' and filename='".$filename."' ";
	$sql = $sql . "and day(date_stat)='".$day."' ";
	$sql = $sql . "and month(date_stat)='".$month."' ";
	$sql = $sql . "and year(date_stat)='".$year."' ";
	$sql = $sql . "and hour(date_stat)='".$hour."' ";

	
	$statement = $this->adapter->query($sql); 
	$result = $statement->execute();

	
	$result = (array)$result->current();
	$same_hour = $result['contador'];
	if (trim($same_hour) == '0' || $source == 'V') {

	        $row = $this->createRow();
	        $row->user_id = $userID;
	        $row->firstname = $firstname;
	        $row->surname = $surname;
	        $row->path = $path;
	        $row->filename = $filename;
	        $row->internal_node_id = $internaNodeID;
	        $row->date_stat = $date;
	        $row->depositor_name = $depositorName;
	        $row->deposit_node_id = $depositNodeID;
	        $row->source = $source;
	        $row->resource_type = $resource_type;
	        $row->file_type = $file_type;
	        $row->funding_body = $funding_body;
	        $row->superseded_or_current = "Current";
	        $row->size = $size;
	        $row->duration = $duration;
	        $row->save();
        }
    }
    

    /**
     * 
     */

    public function getStatsByDate($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo, $page)
    {
        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($depNodeId,'?');
        if ($hasArguments >0) {$depNodeId=substr($depNodeId,0,$hasArguments-1);}

        $sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path '
        .'from download_stat '
        .'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
        .'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
        .'order by firstname, surname ASC, date_stat DESC limit 10 OFFSET '. $page*10;

        $statement = $this->adapter->query($sql);

        $results = $statement->execute();

        $exa = array();
        foreach ($results as $row) {
                $d = $row['DATE(date_stat)'];
                $t = $row['TIME(date_stat)'];
                $firstname = $row['firstname'];
                $surname = $row['surname'];
                $path = $row['path'];
                $exa[]=array('datetime'=>$d . ' - ' . $t,'name'=>$firstname . ' ' . $surname, 'path'=>$path);
        }
        return $exa;
    }




    public function getStatsByDateSort($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo, $page, $type,$sort)
    {
      
      $field = "id";
      if ($type=="user") $field = "CONCAT( firstname , surname)";
      if ($type=="time") $field = "date_stat";
      if ($type=="resource_path") $field = "path";

      
        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($depNodeId,'?');
        if ($hasArguments >0) {$depNodeId=substr($depNodeId,0,$hasArguments-1);}
        
        if ($sort=="both")
	$sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'limit 10 OFFSET '. $page*10;
        else
	$sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'order by '. $field. ' ' .$sort. ' limit 10 OFFSET '. $page*10;


	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	$exa = array();
	
	foreach ($results as $row) {
 
		$d = $row['DATE(date_stat)'];
		$t = $row['TIME(date_stat)'];
		$firstname = $row['firstname'];
		$surname = $row['surname'];
		$path = $row['path'];
		
		$exa[]=array('datetime'=>$d . ' - ' . $t,'name'=>$firstname . ' ' . $surname, 'path'=>$path);
	        
	}
        return $exa;
    }




    public function getCountDownloadByUser($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo, $surname)
    {

        if ($surname <>"")
	$sql = 'select count(*) as total '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'AND concat(firstname, " ", surname) = "'.$surname.'" '
	.'order by firstname, surname ASC, date_stat DESC limit 10 OFFSET '. $page*10;
	else
	$sql = 'select count(*) as total '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'order by firstname, surname ASC, date_stat DESC limit 10 OFFSET '. $page*10;

        $statement = $this->adapter->query($sql);
        $results = $statement->execute();

        foreach ($results as $row) {
                $total = $row['total'];
        }

        return $total;
    }



    public function getDownloadByUserSort($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo, $surname, $page,$type,$sort)
    {
      
      $field = "id";
      if ($type=="deposit") $field = "deposit_node_id";
      if ($type=="filename_id") $field = "filename";
      if ($type=="internal_node_id") $field = "internal_node_id";
      if ($type=="datetime") $field = "date_stat";
      if ($sort=="both") {$field = "id"; $sort="asc";}

        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($depNodeId,'?');
        if ($hasArguments >0) {$depNodeId=substr($depNodeId,0,$hasArguments-1);}
        

        if ($surname <>"")
	$sql = 'select deposit_node_id, filename, internal_node_id, DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'AND concat(firstname, " ", surname) = "'.$surname.'" '
	.'order by '.$field.' '.$sort.' limit 10 OFFSET '. $page*10;
	else
	$sql = 'select deposit_node_id, filename, internal_node_id, DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'order by '.$field . ' '. $sort . ' limit 10 OFFSET '. $page*10;




	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	$exa = array();
	
	foreach ($results as $row) {
 
		$d = $row['DATE(date_stat)'];
		$t = $row['TIME(date_stat)'];
		$firstname = $username.$row['firstname'];
		$surname = $row['surname'];
		$path = $row['path'];
		$filename = $row['filename'];
		$deposit_node_id = $row['deposit_node_id'];
		$internal_node_id = $row['internal_node_id'];
		
		//$exa[]=array('datetime'=>$d . ' - ' . $t,'name'=>$firstname . ' ' . $surname, 'path'=>$path);
		$exa[]=array('deposit_node_id'=>$deposit_node_id ,'filename'=>$filename , 'internal_node_id'=>$internal_node_id , 'datetime'=>$d.'-' .$t,'firstname'=>$firstname, 'surname' => $surname);
	        
	}
        return $exa;
    }


    public function getDownloadByUserCsv($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo, $surname)
    {
      
        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($depNodeId,'?');
        if ($hasArguments >0) {$depNodeId=substr($depNodeId,0,$hasArguments-1);}
        

	$sql = 'select deposit_node_id, filename, internal_node_id, DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'AND concat(firstname, " ", surname) = "'.$surname.'" '
	.'order by firstname, surname ASC, date_stat DESC';

        if ($surname <>"")
	$sql = 'select deposit_node_id, filename, internal_node_id, DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'AND concat(firstname, " ", surname) = "'.$surname.'" '
	.'order by firstname, surname ASC, date_stat DESC';
	else
	$sql = 'select deposit_node_id, filename, internal_node_id, DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'order by firstname, surname ASC, date_stat DESC';

	
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	$exa = array();
	
	foreach ($results as $row) {
 
		$d = $row['DATE(date_stat)'];
		$t = $row['TIME(date_stat)'];
		$firstname = $username.$row['firstname'];
		$surname = $row['surname'];
		$path = $row['path'];
		$filename = $row['filename'];
		$deposit_node_id = $row['deposit_node_id'];
		$internal_node_id = $row['internal_node_id'];
		
		$exa[]=array('deposit_node_id'=>$deposit_node_id ,'filename'=>$filename , 'internal_node_id'=>$internal_node_id , 'datetime'=>$d.'-' .$t,'firstname'=>$firstname, 'surname' => $surname);
	        
	}
        return $exa;
    }





    public function getStatsByDateCsv($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {

        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($depNodeId,'?');
        if ($hasArguments >0) {$depNodeId=substr($depNodeId,0,$hasArguments-1);}


        ini_set('memory_limit', '2048M');

        $sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path, internal_node_id '
        .'from download_stat '
        .'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
        .'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
        .'order by firstname, surname ASC, date_stat DESC ;';


        $statement = $this->adapter->query($sql);

        $results = $statement->execute();
		
        $exa = array();

        foreach ($results as $row) {

                $d = $row['DATE(date_stat)'];
                $t = $row['TIME(date_stat)'];
                $firstname = $row['firstname'];
                $surname = $row['surname'];
                $path = $row['path'];
				$internal_node_id = "MPI" . $row['internal_node_id'];
				# Added by sb174 for Supportworks call no. F0183052 2018-05-29
				$status = "Current";
				if ((strpos(file_get_contents('https://lat1.lis.soas.ac.uk/ds/oaiprovider/oai2?verb=GetRecord&metadataPrefix=imdi&identifier=' . $internal_node_id), "Found no records")) !== false) {
					$status = "Superseded";
				}
				# End

                $exa[]=array('datetime'=>$d . ' - ' . $t,'name'=>$firstname . ' ' . $surname, 'path'=>$path, 'internal_node_id'=>$internal_node_id, 'status'=>$status);

        }

        return $exa;
    }

    public function getCountStatsByDate($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        $sql = 'select count(*) as total '
        .'from download_stat '
        .'where DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
        .'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
        .'order by firstname, surname ASC, date_stat DESC;';

        $statement = $this->adapter->query($sql);
        $results = $statement->execute();

        foreach ($results as $row) {
                $total = $row['total'];
        }

        return $total;
    }


    /**
     * 
     */

    public function getStatsFromDeposit($depNodeId, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
      
        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($depNodeId,'?');
        if ($hasArguments >0) {$depNodeId=substr($depNodeId,0,$hasArguments-1);}

 	$sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path, internal_node_id '
 	.'from download_stat '
 	.'where deposit_node_id = "' . $depNodeId . '" '
 	.'AND DATE(date_stat) '
 	.'between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
 	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
 	.'order by date_stat DESC;';

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	$exa = array();
	
	foreach ($results as $row) {
 
		$d = $row['DATE(date_stat)'];
		$t = $row['TIME(date_stat)'];
		$firstname = $row['firstname'];
		$surname = $row['surname'];
		$path = $row['path'];
		$internal_node_id = "MPI" . $row['internal_node_id'];
		# Added by sb174 for Supportworks call no. F0183052 2018-05-29
		$status = "Current";
		if ((strpos(file_get_contents('https://lat1.lis.soas.ac.uk/ds/oaiprovider/oai2?verb=GetRecord&metadataPrefix=imdi&identifier=' . $internal_node_id), "Found no records")) !== false) {
			$status = "Superseded";
		}
		# End
		
	        
	        $ex = $d . ' - ' . $t . ' - ' . $firstname . ' - ' . $surname . ' - ' . $path . ' - ' . $internal_node_id . ' - ' . $status;
	        
	        $exa[] = $ex;
	        
	}
        return $exa;
    }

     /**
     * 
     */

    public function getStatsFromUser($deposit_node_id, $user_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
      
        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($deposit_node_id,'?');
        if ($hasArguments >0) {$deposit_node_id=substr($deposit_node_id,0,$hasArguments-1);}
        /*$sql="select user_id from download_stat where firstname='" . $firstname . "' and surname='" . $surname . "'";
        
        $statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
        
        foreach ($results as $row) {
		$user_id = $row['user_id'];
	}*/
        
        //$sql="select * from download_stat where deposit_node_id=" . $deposit_node_id . " and user_id='" . $user_id . "'";

	$sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where deposit_node_id = "' . $deposit_node_id . '" '
	.'AND DATE(date_stat) '
	.'between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'and user_id="' . $user_id . '" '
	.'order by date_stat DESC;';

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	$exa = array();
	
	foreach ($results as $row) {
 
		$d = $row['DATE(date_stat)'];
		$t = $row['TIME(date_stat)'];
		$firstname = $row['firstname'];
		$surname = $row['surname'];
		$path = $row['path'];
	        
	        $ex = $d . ' - ' . $t . ' - ' . $firstname . ' - ' . $surname . ' - ' . $path;
	        
	        $exa[] = $ex;
	        
	}
        return $exa;
    }
    
     /**
     *
     */

    public function getStatsFromLU($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {

        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($deposit_node_id,'?');
        if ($hasArguments >0) {$deposit_node_id=substr($deposit_node_id,0,$hasArguments-1);}

	$sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where deposit_node_id = "' . $deposit_node_id . '" '
	.'AND DATE(date_stat) '
	.'between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'and firstname="Lat" and surname="user" '
	.'order by date_stat DESC;';

        $statement = $this->adapter->query($sql);

        $results = $statement->execute();

        $exa = array();

        foreach ($results as $row) {

                $d = $row['DATE(date_stat)'];
                $t = $row['TIME(date_stat)'];
                $firstname = $row['firstname'];
                $surname = $row['surname'];
                $path = $row['path'];

                $ex = $d . ' - ' . $t . ' - ' . $firstname . ' - ' . $surname . ' - ' . $path;

                $exa[] = $ex;

        }
        return $exa;
    }

     /**
     *
     */

    public function getStatsFromOD($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {

        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($deposit_node_id,'?');
        if ($hasArguments >0) {$deposit_node_id=substr($deposit_node_id,0,$hasArguments-1);}

	$sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where deposit_node_id = "' . $deposit_node_id . '" '
	.'AND DATE(date_stat) '
	.'between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'and firstname="Open" and surname="Download" '
	.'order by date_stat DESC;';

        $statement = $this->adapter->query($sql);

        $results = $statement->execute();

        $exa = array();

        foreach ($results as $row) {

                $d = $row['DATE(date_stat)'];
                $t = $row['TIME(date_stat)'];
                $firstname = $row['firstname'];
                $surname = $row['surname'];
                $path = $row['path'];

                $ex = $d . ' - ' . $t . ' - ' . $firstname . ' - ' . $surname . ' - ' . $path;

                $exa[] = $ex;

        }
        return $exa;
    }

     /**
     *
     */

    public function getStatsFromSpecificUser($deposit_node_id, $firstname, $surname, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {

        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($deposit_node_id,'?');
        if ($hasArguments >0) {$deposit_node_id=substr($deposit_node_id,0,$hasArguments-1);}

	$sql = 'select DATE(date_stat), TIME(date_stat), firstname, surname, path '
	.'from download_stat '
	.'where deposit_node_id = "' . $deposit_node_id . '" '
	.'AND DATE(date_stat) '
	.'between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'and firstname="' . $firstname . '" and surname="' . $surname . '" '
	.'order by date_stat DESC;';

	$statement = $this->adapter->query($sql);

        $results = $statement->execute();

        $exa = array();

        foreach ($results as $row) {

                $d = $row['DATE(date_stat)'];
                $t = $row['TIME(date_stat)'];
                $firstname = $row['firstname'];
                $surname = $row['surname'];
                $path = $row['path'];

                $ex = $d . ' - ' . $t . ' - ' . $firstname . ' - ' . $surname . ' - ' . $path;

                $exa[] = $ex;

        }
        return $exa;
    }

    /**
     * 
     */

    public function getDepositStatisticsDisplay($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {

        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($deposit_node_id,'?');
        if ($hasArguments >0) {$deposit_node_id=substr($deposit_node_id,0,$hasArguments-1);}
        
        /*if($current_month == 0) {
	        if($current_year == 0) {
		        $sql="SELECT count(*),firstname,surname,user_id, date_stat"
		        ." FROM download_stat" 
		        ." where deposit_node_id = ".$deposit_node_id
		        ." group by user_id order by count(*) DESC;";
		} else {
		        $sql="SELECT count(*),firstname,surname,user_id, date_stat"
		        ." FROM download_stat" 
		        ." where deposit_node_id = ".$deposit_node_id
		        ." and YEAR(date_stat) = ".$current_year
		        ." group by user_id order by count(*) DESC;";
		}
	} else {
	        if($current_year == 0) {
			$sql="SELECT count(*),firstname,surname,user_id, date_stat"
		        ." FROM download_stat" 
		        ." where deposit_node_id = ".$deposit_node_id
		        ." and MONTH(date_stat) = ".$current_month
		        ." group by user_id order by count(*) DESC;";
		} else {
			$sql="SELECT count(*),firstname,surname,user_id, date_stat"
		        ." FROM download_stat" 
		        ." where deposit_node_id = ".$deposit_node_id
		        ." and YEAR(date_stat) = ".$current_year
		        ." and MONTH(date_stat) = ".$current_month
		        ." group by user_id order by count(*) DESC;";
		}
	}*/
		 
	$sql = 'SELECT count(*),firstname,surname,user_id, date_stat '
	.'FROM download_stat where deposit_node_id = "' . $deposit_node_id 
	.'" AND DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom 
	.'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo 
	.'" group by user_id order by count(*) DESC;';
	
	
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	$exa = array();
	
	foreach ($results as $row) {
 
		$num = $row['count(*)'];
		$firstname = $row['firstname'];
		$surname = $row['surname'];
		$email = $row['user_id'];
		
	        
	        $ex = $num . ' - ' . $firstname . ' - ' . $surname . ' - ' . $email;
	        
	        $exa[] = $ex;
	        
	}
        return $exa;
    }
    
    /**
     * 
     */

    public function getFilesStatisticsDisplay($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        //SCB Fix. Sanitize $depNodeId
        $hasArguments = strpos($deposit_node_id,'?');
        if ($hasArguments >0) {$deposit_node_id=substr($deposit_node_id,0,$hasArguments-1);}
        
        /*if($current_month == 0) {
	        if($current_year == 0) {
		        $sql="SELECT resource_type, file_type, COUNT(resource_type) AS Count"
			." FROM vufind.download_stat where resource_type in (SELECT resource_type"
			." FROM vufind.download_stat group by resource_type)"
			." and deposit_node_id = ".$deposit_node_id
			." group by resource_type, file_type;";
		} else {
		        $sql="SELECT resource_type, file_type, COUNT(resource_type) AS Count"
			." FROM vufind.download_stat where resource_type in (SELECT resource_type"
			." FROM vufind.download_stat group by resource_type)"
			." and deposit_node_id = ".$deposit_node_id
			." and YEAR(date_stat) = ".$current_year
			." group by resource_type, file_type;";
		}
	} else {
		if($current_year == 0) {
			$sql="SELECT resource_type, file_type, COUNT(resource_type) AS Count"
			." FROM vufind.download_stat where resource_type in (SELECT resource_type"
			." FROM vufind.download_stat group by resource_type)"
			." and deposit_node_id = ".$deposit_node_id
			." and MONTH(date_stat) = ".$current_month
			." group by resource_type, file_type;";
		} else {
			$sql="SELECT resource_type, file_type, COUNT(resource_type) AS Count"
			." FROM vufind.download_stat where resource_type in (SELECT resource_type"
			." FROM vufind.download_stat group by resource_type)"
			." and deposit_node_id = ".$deposit_node_id
			." and YEAR(date_stat) = ".$current_year
			." and MONTH(date_stat) = ".$current_month
			." group by resource_type, file_type;";
		}
	}*/
	
	
	$sql = 'SELECT resource_type, file_type, COUNT(resource_type) AS Count '
	.'FROM vufind.download_stat where resource_type in '
	.'(SELECT resource_type FROM vufind.download_stat group by resource_type) '
	.'and deposit_node_id = "' . $deposit_node_id 
	.'" AND DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom 
	.'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo 
	.'" group by resource_type, file_type;';
	
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	$totalFiles = array();
	
	foreach ($results as $row) {
 
		$res_type = $row['resource_type'];
		$f_type = $row['file_type'];
		$count = $row['Count'];
		
	        
	        $f = $res_type . ' - ' . $f_type . ' - ' . $count;
	        
	        $totalFiles[] = $f;
	        
	}
        return $totalFiles;
    }
    
    /**
     * 
     */


    
    public function getAccessFilesWithResType($res_type, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        /*if($current_month == 0) {
	        if($current_year == 0) {
		        $sql="SELECT count(*)"
		        ." FROM vufind.download_stat"
		        ." where resource_type = '".$res_type."'";
		} else {
		        $sql="SELECT count(*)"
		        ." FROM vufind.download_stat"
		        ." where resource_type = '".$res_type."'"
		        ." and YEAR(date_stat) = ".$current_year.";";
		}
	} else {
	        if($current_year == 0) {
			 $sql="SELECT count(*)"
		        ." FROM vufind.download_stat"
		        ." where resource_type = '".$res_type."'"
		        ." and MONTH(date_stat) = ".$current_month.";";
		} else {
			 $sql="SELECT count(*)"
		        ." FROM vufind.download_stat"
		        ." where resource_type = '".$res_type."'"
		        ." and YEAR(date_stat) = ".$current_year
		        ." and MONTH(date_stat) = ".$current_month.";";
		}
	}*/

	$sql = 'SELECT count(*) '
	.'FROM vufind.download_stat '
	.'where resource_type = "' . $res_type 
	.'" AND DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom 
	.'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo .'";';

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num = $row['count(*)'];
	}
        return $num;
    }

    /**
     * Returns the years to use in the filters
     *
     * @return number
     */
     
    public function getYears()
    {
        $filters = array();
        
        $sql="select distinct(year(date_stat)) as year from download_stat;";
	$statement = $this->adapter->query($sql);
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$filters[] = $row['year'];
	}
        return $filters;
    }


    public function getByFileType($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo,$fundingBody,$currentSupersed)
    {
    	

	if (!($fundingBody[0]=='All deposits')) {
		$ores = "";
		foreach($fundingBody as $k => $fb) {
		    if ($k==0)
		      $ores = "funding_body = '".$fb;
		    else
		      $ores = $ores. "' or funding_body = '".$fb;
		}
		if (strlen($ores)>0) $ores = $ores . "'";
        }	

	if ($currentSupersed=="Current") $currentSupersed = array("Current");
	if ($currentSupersed=="Both") $currentSupersed = array("Current","Supersed");
        if (strlen($ores)>0){
	    if (count($currentSupersed)==2 || count($currentSupersed)==0){

	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" AND (' . $ores . ') '
	        .' group by file_type order by date_stat DESC';
	    }
	    else{
	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" AND (' . $ores . ') '
	        .' and superseded_or_current =  "'.$currentSupersed[0]. '" '
	        .' group by file_type order by date_stat DESC';

	    }
	}
        else {
	    if (count($currentSupersed)==2||count($currentSupersed)==0){
	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" group by file_type order by date_stat DESC';
	    	}
	    else {
	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" and superseded_or_current =  "'.$currentSupersed[0]. '" '
	        .' group by file_type order by date_stat DESC';

	   }
	}


	$statement = $this->adapter->query($sql);
	$results = $statement->execute();
	$exa = array();
	$totalResults=0;
	$totalCount=0;
	$totalSize = 0;
	$totalDuration = 0;
	$totalCountO = 0;
	$totalCountS = 0;
	$totalCountU = 0;
	$totalPercentO = 0;
	$totalPercentS = 0;
	$totalPercentU = 0;
	


	foreach ($results as $row) {
        	$file_type = $row['file_type'];
		$count = $row['total'];
		$totalCount = $totalCount + $count;
		$size = $row['size'];
		$totalSize = $totalSize + $size;

		if ($size > 1024*1024*1024*1024) $size = round($size/(1024*1024*1024*1024),2)." TB";
		else if ($size > 1024*1024*1024) $size = round($size/(1024*1024*1024),2)." GB";
		     else if ($size > 1024*1024) $size = round($size/(1024*1024),2)." MB";
		          else if ($size > 1024) $size = round($size/(1024),2)." KB";
		               else $size = round($size,2)." B";
		
		$duration = $row['duration'];
		$totalDuration = $totalDuration + $duration;

		$hours = floor($duration / 3600);
		$minutes = floor(($duration - ($hours * 3600)) / 60);
		$seconds = $duration - ($hours * 3600) - ($minutes * 60);
		
		$duration = $hours . 'h:' . $minutes . "m:" . round($seconds,0)."s";
		$totalDuration = $totalDuration + $duration;

		$countO = $row['countO'];
		$countS = $row['countS'];
		$countU = $row['countU'];
		
		$totalCountO = $totalCountO + $countO;
		$totalCountS = $totalCountS + $countS;
		$totalCountU = $totalCountU + $countU;
		
		$percentO = ($countO * 100)/$count;
		$percentU = ($countU * 100)/$count;
		$percentS = ($countS * 100)/$count;

		$totalPercentO = ($totalCountO * 100)/$totalCount;
		$totalPercentU = ($totalCountU * 100)/$totalCount;
		$totalPercentS = ($totalCountS * 100)/$totalCount;


		$totalResults++;
	        $exa[] = array('file_type'=>$file_type, 'count'=>$count, 'size'=>$size, 'duration'=>$duration, 'countO'=>$countO, 'percentO'=>$percentO, 'countS'=>$countS, 'percentS'=>$percentS, 'countU'=>$countU, 'percentU'=>$percentU);
	        
	}
	if ($totalSize > 1024*1024*1024*1024) $totalSize = round($totalSize/(1024*1024*1024*1024),2)." TB";
	else if ($totalSize > 1024*1024*1024) $totalSize = round($totalSize/(1024*1024*1024),2)." GB";
	     else if ($totalSize > 1024*1024) $totalSize = round($totalSize/(1024*1024),2)." MB";
	          else if ($totalSize > 1024) $totalSize = round($totalSize/(1024),2)." KB";
	               else $totalSize = round($totalSize,2)." B";


	$totalHours = floor($totalDuration / 3600);
	$totalMinutes = floor(($totalDuration - ($totalHours * 3600)) / 60);
	$totalSeconds = $totalDuration - ($totalHours * 3600) - ($totalMinutes * 60);
		
	$totalDuration = $totalHours . 'h:' . $totalMinutes . "m:" . round($totalSeconds,0)."s";
	$exa[] = array('file_type'=>"TOTAL", 'count'=>$totalCount, 'size'=>$totalSize, 'duration'=>$totalDuration, 'countO'=>$totalCountO, 'percentO'=>$totalPercentO, 'countS'=>$totalCountS, 'percentS'=>$totalPercentS, 'countU'=>$totalCountU, 'percentU'=>$totalPercentU);


        return $exa;
    }

    public function getByFileTypeDeposit($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo,$fundingBody,$currentSupersed,$deposit)
    {
    	

	if (!($fundingBody[0]=='All deposits')) {
		$ores = "";
		foreach($fundingBody as $k => $fb) {
		    if ($k==0)
		      $ores = "funding_body = '".$fb;
		    else
		      $ores = $ores. "' or funding_body = '".$fb;
		}
		if (strlen($ores)>0) $ores = $ores . "'";
        }	

	if ($currentSupersed=="Both") $currentSupersed = array("Current","Superseded");
	if ($currentSupersed=="Current") $currentSupersed = array("Current");
        if (strlen($ores)>0){
	    if (count($currentSupersed)==2 || count($currentSupersed)==0){

	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" AND (' . $ores . ') '
	        .' and deposit_node_id = '.$deposit
	        .' group by file_type order by date_stat DESC';
	    }
	    else{
	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" AND (' . $ores . ') '
	        .' and superseded_or_current =  "'.$currentSupersed[0]. '" '
	        .' and deposit_node_id = '.$deposit
	        .' group by file_type order by date_stat DESC';

	    }
	}
        else {
	    if (count($currentSupersed)==2||count($currentSupersed)==0){
	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" and deposit_node_id = "'.$deposit
	        .'" group by file_type order by date_stat DESC';
	    	}
	    else {
	        $sql = 'SELECT file_type, count(*) as total, sum(size) as size, sum(duration) as duration , '
	        .'COUNT(case when resource_type ="O" then resource_type end) as countO, '
	        .'COUNT(case when resource_type ="U" then resource_type end) as countU, '
	        .'COUNT(case when resource_type ="S" then resource_type end) as countS '
	        .'FROM download_stat '
	        .'WHERE DATE(date_stat) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom
	        .'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo
	        .'" and superseded_or_current =  "'.$currentSupersed[0]. '" '
	        .' and deposit_node_id = '.$deposit
	        .' group by file_type order by date_stat DESC';

	   }
	}
	$statement = $this->adapter->query($sql);
	$results = $statement->execute();
	$exa = array();
	$totalResults=0;
	$totalCount = 0;
	$totalSize = 0;
	$totalDuration = 0;
	$totalCountO=0;
	$totalCountU=0;
	$totalCountS=0;
	$totalPercentO = 0;
	$totalPercentS = 0;
	$totalPercentU = 0;

	
	foreach ($results as $row) {
        	$file_type = $row['file_type'];
		$count = $row['total'];
		$totalCount = $totalCount + $count;
		$size = $row['size'];
		$totalSize = $totalSize + $size;
		
		if ($size > 1024*1024*1024*1024) $size = round($size/(1024*1024*1024*1024),2)." TB";
		else if ($size > 1024*1024*1024) $size = round($size/(1024*1024*1024),2)." GB";
		     else if ($size > 1024*1024) $size = round($size/(1024*1024),2)." MB";
		          else if ($size > 1024) $size = round($size/(1024),2)." KB";
		               else $size = round($size,2)." B";
		
		$duration = $row['duration'];
		$totalDuration = $totalDuration + $duration;

		$hours = floor($duration / 3600);
		$minutes = floor(($duration - ($hours * 3600)) / 60);
		$seconds = $duration - ($hours * 3600) - ($minutes * 60);
		
		$duration = $hours . 'h:' . $minutes . "m:" . round($seconds,0)."s";

		$countO = $row['countO'];
		$countS = $row['countS'];
		$countU = $row['countU'];
		$totalCountO = $totalCountO + $countO;
		$totalCountS = $totalCountS + $countO;
		$totalCountU = $totalCountU + $countO;
		
		$percentO = ($countO * 100)/$count;
		$percentU = ($countU * 100)/$count;
		$percentS = ($countS * 100)/$count;

		$totalPercentO = ($totalCountO * 100)/$totalCount;
		$totalPercentU = ($totalCountU * 100)/$totalCount;
		$totalPercentS = ($totalCountS * 100)/$totalCount;


		$totalResults++;
	        $exa[] = array('file_type'=>$file_type, 'count'=>$count, 'size'=>$size, 'duration'=>$duration, 'countO'=>$countO, 'percentO'=>$percentO, 'countS'=>$countS, 'percentS'=>$percentS, 'countU'=>$countU, 'percentU'=>$percentU);
	        
	}

	if ($totalSize > 1024*1024*1024*1024) $totalSize = round($totalSize/(1024*1024*1024*1024),2)." TB";
	else if ($totalSize > 1024*1024*1024) $totalSize = round($totalSize/(1024*1024*1024),2)." GB";
	     else if ($totalSize > 1024*1024) $totalSize = round($totalSize/(1024*1024),2)." MB";
	          else if ($totalSize > 1024) $totalSize = round($totalSize/(1024),2)." KB";
	               else $totalSize = round($totalSize,2)." B";


	$totalHours = floor($totalDuration / 3600);
	$totalMinutes = floor(($totalDuration - ($totalHours * 3600)) / 60);
	$totalSeconds = $totalDuration - ($totalHours * 3600) - ($totalMinutes * 60);
		
	$totalDuration = $totalHours . 'h:' . $totalMinutes . "m:" . round($totalSeconds,0)."s";
	
	$exa[] = array('file_type'=>"TOTAL", 'count'=>$totalCount, 'size'=>$totalSize, 'duration'=>$totalDuration, 'countO'=>$totalCountO, 'percentO'=>$totalPercentO, 'countS'=>$totalCountS, 'percentS'=>$totalPercentS, 'countU'=>$totalCountU, 'percentU'=>$totalPercentU);

        //print_r($exa);

        return $exa;
    }

    /**
     * Returns the distinct number of bundles
     *
     * @param date $current_year Year to use in the query
     * date $current_month Month to use in the query
     *
     * @return number
     */
    public function getDistintBundles()
    {
        $num=array();
	$sql = 'select distinct(internal_node_id) as bundle_id '.
        'from download_stat';

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num[] = array('bundle_id'=>"MPI".$row['bundle_id']);
	}
        return $num;
    }

    /**
     * Initialize supersed_or_current
     *
     * @return void
     */
    public function updateCurrentBundle($id, $status)
    {
    	$id = str_replace("MPI","",$id);
        $sql = "update download_stat set superseded_or_current='".$status. "' where internal_node_id = '". $id. "'";
        $statement = $this->adapter->query($sql);

        $results = $statement->execute();
        return $results;
    }

    public function getFullName()
    {

	$fullnames = array();
	
	$sql = 'select distinct (concat(firstname," ",surname)) as fullname '.
        'from download_stat '.
        'order by 1 asc';


        $statement = $this->adapter->query($sql);
        $results = $statement->execute();

        foreach ($results as $row) {
                $fullnames[] = $row['fullname'];
        }

        return $fullnames;
    }


}
