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
class UploadedFilesByFileTypeController extends AbstractBase
{

    public function homeAction()
    {


    	if (!$this->getAuthManager()->isLoggedIn()) {
            return $this->forceLogin();
        } else {
	
	$yearFrom = $_GET['yearFrom'];
	$monthFrom = $_GET['monthFrom'];
	$dayFrom = $_GET['dayFrom'];
	$yearTo = $_GET['yearTo'];
	$monthTo = $_GET['monthTo'];
	$dayTo = $_GET['dayTo'];
    	$source = $_GET['source'];
    	$deposit = $_GET['deposit'];
    	
    	$current_year = date('Y');
	$current_month = date('m');
	$current_day = date('d');
    	

        $fundingBody = $_GET['funding_body'];
        $currentSuperseded = $_GET['current_superseded'];

        $fundingBody = explode(",", $fundingBody);
        $currentSuperseded = explode(",", $currentSuperseded);

		$deposit = $_GET['deposit'];
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






    	
    	if (empty($yearFrom)) $yearFrom = $current_year;
	if (empty($monthFrom)&& $monthFrom<>"0") $monthFrom = $current_month;
	if (empty($dayFrom)&& $dayFrom<>"0") $dayFrom = $current_day;
	if (empty($yearTo)) $yearTo = $current_year;
	if (empty($monthTo)&& $monthTo<>"0") $monthTo = $current_month;
	if (empty($dayTo)&& $dayTo<>"0") $dayTo = $current_day;

        if (empty($fundingBody) || empty($fundingBody[0])) $fundingBody="All deposits";
        if (empty($currentSuperseded)|| empty($currentSuperseded[0])) $currentSuperseded="Current";


    	//Tablas a utilizar
    	$tablaSettings= $this->getTable('Settings');
    	
    	$PrivateStatsT = $tablaSettings->getPrivateStats();
    	
    	
    	$this->getRequest()->getPost()->set('PrivateStats', $PrivateStatsT);
    	if($PrivateStatsT==0){
    		$mens = $this->translate('Statistics not avaliable');
		$this->flashMessenger()->setNamespace('error')
		    		->addMessage($mens);
	}
        //SCB Store statistics
        $data=array('recordId'     => '0','recordSource' => 'No-Stat');
        $this->getServiceLocator()->get('VuFind\PublicstatsStats')
            ->log($data, $this->getRequest());
        //SCB Store statistics

    	$stats = $this->getTable('DownloadStat');
    	$user_stats = $this->getTable('UserStats');
    	$annexAndTrovaUserSession = $this->getTable('AnnexAndTrovaUserSession');
    	$tableLatResources= $this->getTable('LatResources');

    	
	$uploadByFileType = $tableLatResources->getByFileTypeDeposit($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo,$fundingBody, $currentSuperseded,$deposit);


    	$fundingBodies = $tableLatResources->getDistinctFundingBody();


    	
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
    	

        if (isset($_POST['btnExportarExcelUploadedFilesByFileType'])){
                $this->exportarexcelUploadedFilesByFileTypeDeposit($uploadByFileType,$yearFrom,$monthFrom,$dayFrom,$yearTo,$monthTo,$dayTo,$fundingBody, $currentSuperseded,$deposit, $source);
        }


        $view = $this->createViewModel(array('title' => $depositTitle, 'results' => $results, 'fundingbodies'=> $fundingBodies, uploadByFileType=>$uploadByFileType));
        $view->setTemplate('uploadedfilesbyfiletype/home');
        $view->request = $this->getRequest()->getPost();
    	return $view;
        
        //$view->comments = $this->getTable('comments')->getStatistics();
        //$view->favorites = $this->getTable('userresource')->getStatistics();
        //$view->tags = $this->getTable('resourcetags')->getStatistics();
        return $view;
        }
    }

    public function exportarexcelUploadedFilesByFileTypeDeposit($results,$yearFrom,$monthFrom,$dayFrom,$yearTo,$monthTo,$dayTo,$fundingBody, $currentSuperseded,$deposit,$source){

        if ($source=="ajax"){
	    $tableLatResources= $this->getTable('LatResources');
            $results = $tableLatResources->getByFileTypeDeposit($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo,$fundingBody, $currentSuperseded,$deposit);
	    //$results = $tableLatResources->getDepositUploadedFilesPrivateStatistics('2017', '01', '11', '2017', '08', '11');
        }

	$contador = 1;
	$dir = dirname(dirname(__FILE__));


	//require_once($dir.'/Controller/PHPExcel/IOFactory.php');
	require_once('/usr/local/vufind/module/VuFind/src/VuFind/Controller/PHPExcel/IOFactory.php');

	// Crea un nuevo objeto PHPExcel
	$objPHPExcel = new PHPExcel;

	//Propiedades
	$objPHPExcel->getProperties()
		->setCreator("SOAS")
		->setLastModifiedBy("SOAS")
		->setTitle($this->translate('Private statistics'))
		->setSubject($this->translate('Private statistics'))
		->setDescription($this->translate('Private statistics'))
		->setKeywords($this->translate('Private statistics'))
		->setCategory($this->translate('Private statistics'));
		
	$objSheet  = $objPHPExcel->getActiveSheet();

	
	//  XLS file
	//$objSheet->getStyle('A'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('B'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('C'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('D'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');
	//$objSheet->getStyle('E'.$contador)->getFont()->setBold(true)->setName('Helvetica')->setSize(10)->getColor()->setRGB('da2c45');

	$objSheet ->getCell('A'.$contador)->setValue('"'.$this->translate('File Type'). '","' .$this->translate('Number'). '","' .$this->translate('Size'). '","' .$this->translate('Duration'). '","' .$this->translate('O'). '","' .$this->translate('O%'). '","' .$this->translate('U'). '","' .$this->translate('U%'). '","' .$this->translate('S'). '","' .$this->translate('%S').'"');



     foreach( $results as  $item=>$index ){

     	$file_type=$index['file_type'];
     	$count=$index['count'];
     	$size=$index['size'];
	$duration=$index['duration'];
	$countO=$index['countO'];
        $countU=$index['countU'];
	$countS=$index['countS'];
	$percentO = round(($countO * 100)/$count).'%';
	$percentU = round(($countU * 100)/$count).'%';
	$percentS = round(($countS * 100)/$count).'%';
        
	$contador=$contador+1;

	$objSheet ->getCell('A'.$contador)->setValue('"'.$file_type. '","' .$count. '","' .$size. '","' .$duration. '","' .$countO. '","' .$percentO. '","' .$countU. '","' .$percentU. '","' .$countS. '","' .$percentS. '"' );
	

     	}

	

	

	//FIN EXCEL
	
	// Renombrar Hoja
	$objPHPExcel->getActiveSheet()->setTitle('Private statistics');
	 
	// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
	$objPHPExcel->setActiveSheetIndex(0);
	
	// XLS file
	/*header('Content-Type: application/vnd.ms-excel'); 
	header('Content-Disposition: attachment;filename="' . $filename.date('d/m/Y') . '.xls"');
	header('Cache-Control: max-age=0');
	ob_end_clean();
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
	//force user to download the Excel file without writing it to server's HD
	$objWriter->save('php://output');*/
	
	// CSV file
	header('Content-Type: application/csv'); 
	header('Content-Disposition: attachment;filename="' . $filename.date('d/m/Y') . '.csv"');
	header('Cache-Control: max-age=0');
	ob_end_clean();
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');  
	//force user to download the Excel file without writing it to server's HD
	$objWriter->save('php://output');
	exit();
	
	}



    /**
     * Uses the user language to determine which Help template to use
     * Uses the English template as a back-up
     *
     * @return mixed
     */
    public function home2Action()
    {
    	
    	if (!$this->getAuthManager()->isLoggedIn()) {
            return $this->forceLogin();
        } else {
		$stats = $this->getTable('LatResources');
		$deposit = $_GET['deposit'];
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
		
		$results = $stats->getDepositUploadedFilesStatistics($deposit, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
		
		if (isset($_POST['btnExportarExcel'])){
			$this->exportarexcel($results, $depositTitle);
		}
		
		return $this->createViewModel(array('title' => $depositTitle,'results' => $results));
                //return $this->createViewModel();
	}
    }
    
    

}
