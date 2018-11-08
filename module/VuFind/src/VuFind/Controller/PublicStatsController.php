<?php
/**
 * Home action for Help module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
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
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace VuFind\Controller;
use PDO, PDOException, VuFind\Exception\ILS as ILSException;

/**
 * Home action for Help module
 *
 * @category VuFind2
 * @package  Controller
 * @author   Chris Hallberg <challber@villanova.edu>
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class PublicStatsController extends AbstractBase
{

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
     * Get the parent id
     *
     * @return string
     */
     public function getParentId($setSpec) {
     	$output=$setSpec;
     	$son=$this->getId($setSpec);
     	$newString = str_replace($son,"",$setSpec);
     	$newString= str_replace("#/#","", $newString);
     	$output=$this->getId($newString);
     	return $output;
     	

     }

    /**
     * Uses the user language to determine which Help template to use
     * Uses the English template as a back-up
     *
     * @return mixed
     */
    public function homeAction()
    {

    	
    	//Tablas a utilizar
    	$tablaSettings= $this->getTable('Settings');
    	
    	$PublicStatsT = $tablaSettings->getPublicStats();

    	$this->getRequest()->getPost()->set('PublicStats', $PublicStatsT);
    	
    	if($PublicStatsT==0) {
    		$mens = $this->translate('Statistics not avaliable');
    		$this->flashMessenger()->setNamespace('error')->addMessage($mens);
    	}
        //SCB Store statistics
        $data=array('recordId'     => '0','recordSource' => 'No-Stat');
        $this->getServiceLocator()->get('VuFind\PublicstatsStats')
            ->log($data, $this->getRequest());
        //SCB Store statistics
	//$year = $_GET['year'];
	//$month = $_GET['month'];
	$yearFrom = $_GET['yearFrom'];
	$monthFrom = $_GET['monthFrom'];
	$dayFrom = $_GET['dayFrom'];
	$yearTo = $_GET['yearTo'];
	$monthTo = $_GET['monthTo'];
	$dayTo = $_GET['dayTo'];

	/*print_r($yearFrom);
	print_r($monthFrom);
	print_r($dayFrom);
	print_r($yearTo);
	print_r($monthTo);
	print_r($dayTo);*/

	$current_year = date('Y');
	$current_month = date('m');
	$current_day = date('d');

    	//if (empty($year)) $year = $current_year;
	//if (empty($month)&& $month<>"0") $month = $current_month;
	//if (empty($day)&& $day<>"0") $day = $current_day;
    	
	if (empty($yearFrom)) $yearFrom = $current_year;
	if (empty($monthFrom)&& $monthFrom<>"0") $monthFrom = $current_month;
	if (empty($dayFrom)&& $dayFrom<>"0") $dayFrom = $current_day;
	if (empty($yearTo)) $yearTo = $current_year;
	if (empty($monthTo)&& $monthTo<>"0") $monthTo = $current_month;
	if (empty($dayTo)&& $dayTo<>"0") $dayTo = $current_day;

	/*print_r($yearFrom);
	print_r($monthFrom);
	print_r($dayFrom);
	print_r('</br>');
	print_r($yearTo);
	print_r($monthTo);
	print_r($dayTo);*/

    	$stats = $this->getTable('DownloadStat');
    	$user_stats = $this->getTable('UserStats');
    	$annexAndTrovaUserSession = $this->getTable('AnnexAndTrovaUserSession');
    	
    	$tableLatResources= $this->getTable('LatResources');
    	
    	$results = array();
    	$results [] = $user_stats->getNumberOfHits($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$results [] = $user_stats->getNumberOfLogins($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) + $annexAndTrovaUserSession->getNumberOfLogins($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$results [] = $stats->getAccessFilesWithResType('O', $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$results [] = $stats->getAccessFilesWithResType('U', $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$results [] = $stats->getAccessFilesWithResType('S', $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$results [] = $user_stats->getClicsPerVisit($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$results [] = $user_stats->getDurationOfVisit($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$resultsTable = $user_stats->getCountryPercentage($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	$latResourcesArray = $tableLatResources->getNumberOfResources($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
    	//echo "escribe".count($latResourcesArray);
	$output ="";
	foreach ($resultsTable as $position => $item) {
		if($position==0)
			$output = $output .'"'.$item['country'].'": '.$item['percentage'];
		else
			$output = $output .',"'.$item['country'].'": '.$item['percentage'];
	}
	
    	$results [] = $output;
    	$results [] = $latResourcesArray;
	
	// Years to use in the filter
	$yearsUserStats = $user_stats->getYears();
	//print_r($yearsUserStats);
	//echo '</br>';
	$yearsStats = $stats->getYears();
	//print_r($yearsStats);
	//echo '</br>';
	$yearsLatResources = $tableLatResources->getYears();
	//print_r($yearsLatResources);
	//echo '</br>';
	
	$years = array_unique (array_merge (array_filter($yearsUserStats), array_filter($yearsStats)));
	$years = array_unique (array_merge ($years, array_filter($yearsLatResources)));
	arsort($years);
	//print_r($years);
	
	$results [] = $years;
  	$view = $this->createViewModel(array('results' => $results));
  	$view->request = $this->getRequest()->getPost();
    	return $view;
    }
}
