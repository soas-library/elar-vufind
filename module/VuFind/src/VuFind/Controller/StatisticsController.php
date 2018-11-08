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
use PHPExcel;
use PHPExcel_IOFactory;

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
class StatisticsController extends AbstractBase
{
    /**
     * Uses the user language to determine which Help template to use
     * Uses the English template as a back-up
     *
     * @return mixed
     */
    public function homeAction()
    {
    	
    	if (!$this->getAuthManager()->isLoggedIn()) {
            return $this->forceLogin();
        } else {
		$user = $this->getUser();
		$u = $user->cat_username;
		$stats = $this->getTable('DownloadStat');
		$users = $this->getTable('User');
		$deposit = $_GET['node_id'];
		$user = $_GET['user'];
		//$current_year = $_GET['current_year'];
		//$current_month = $_GET['current_month'];
		$yearFrom = $_GET['yearFrom'];
		$monthFrom = $_GET['monthFrom'];
		$dayFrom = $_GET['dayFrom'];
		$yearTo = $_GET['yearTo'];
		$monthTo = $_GET['monthTo'];
		$dayTo = $_GET['dayTo'];
		
	          $urlSolr = 'http://'.$_SERVER['SERVER_NAME'].':8080/solr/biblio/select?q=id%3AMPI'.$deposit.'&rows=1&fl=title&wt=xml&indent=true';
	          
	          $ch = curl_init();
	          curl_setopt($ch, CURLOPT_URL,$urlSolr);
	          //curl_setopt($ch, CURLOPT_PROXY, $proxy);
	          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	          curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	          $contentDepositTitle = curl_exec($ch);
	          curl_close($ch);
	          $xml = simplexml_load_string($contentDepositTitle, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	          foreach($xml->result->doc as $result2) {
	              foreach($result2->str as $result3  ) {
	                  $depositTitle = $result3;
	              }
	          }
		
		if (!empty($user)) {
			if(strcmp($user, 'LU') === 0){
				$results = $stats->getStatsFromLU($deposit, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
			} else if(strcmp($user, 'OD') === 0){
				$results = $stats->getStatsFromOD($deposit, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
			} else if (substr($user, 0, 1) === '_'){
				$fs = explode("_", $user);
				$results = $stats->getStatsFromSpecificUser($deposit, $fs[1], $fs[2], $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
			} else {
		    		$userResults = $users->getUserById($user);
		    		$results = $stats->getStatsFromUser($deposit, $userResults, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
			}
		} else {
		    $results = $stats->getStatsFromDeposit($deposit, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
		}
		
		if (isset($_POST['btnExportarExcel'])){
			$this->exportarexcel($results, $depositTitle);
		}
		
		return $this->createViewModel(array('title' => $depositTitle,'results' => $results));
	}
    }
    
    
    public function exportarexcel($results, $filename){

	$contador = 1;
	$dir = dirname(dirname(__FILE__));

	require_once($dir.'/Controller/PHPExcel/IOFactory.php');

	// Crea un nuevo objeto PHPExcel
	$objPHPExcel = new PHPExcel;

	//Propiedades
	$objPHPExcel->getProperties()
		->setCreator("SOAS")
		->setLastModifiedBy("SOAS")
		->setTitle($this->translate('Statistics'))
		->setSubject($this->translate('Statistics'))
		->setDescription($this->translate('Statistics'))
		->setKeywords($this->translate('Statistics'))
		->setCategory($this->translate('Statistics'));
	
	$objSheet  = $objPHPExcel->getActiveSheet();
	
	//  XLS file
	//$objSheet->getStyle('A'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('B'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('C'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('D'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('E'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');

	$objSheet ->getCell('A'.$contador)->setValue($this->translate('Date'). ',' .$this->translate('Time'). ',"' .$this->translate('Firstname'). '","' .$this->translate('Surname'). '","' .$this->translate('Path'). '","' .$this->translate('ID'). '","' .$this->translate('Status'). '"');
	
	$contador=$contador+1;
	
	foreach ($results as $valor) {

		$stat = explode(" - ", $valor);
		// Muestra los campos de la BD
		// date, time, firstname, surname, path
		$day = $stat[0];
		$timestamp = strtotime($day);

		$objSheet ->getCell('A'.$contador)->setValue(date("j F Y", $timestamp). ',' .$stat[1]. ',"' .$stat[2]. '","' .$stat[3]. '","' .$stat[4]. '","' .$stat[5] . '","' .$stat[6] . '"');

		$contador=$contador+1;

	}
	//FIN EXCEL
	
	// Renombrar Hoja
	$objPHPExcel->getActiveSheet()->setTitle('Statistics');
	 
	// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
	$objPHPExcel->setActiveSheetIndex(0);
	
	// XLS file
	/*header('Content-Type: application/vnd.ms-excel'); 
	header('Content-Disposition: attachment;filename="' . $filename.date('d/m/Y') . '.xls"');
	header('Cache-Control: max-age=0');
	ob_end_clean();
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  	//force user to download the Excel file without writing it to server's HD
	$objWriter->save('php://output');*/
	
	// CSV file
	header('Content-Type: application/csv'); 
	header('Content-Disposition: attachment;filename="' . $filename.date('d/m/Y') . '.csv"');
	header('Cache-Control: max-age=0');
	ob_end_clean();
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');  	//force user to download the Excel file without writing it to server's HD
	$objWriter->save('php://output');
	exit();
	
	}


}
