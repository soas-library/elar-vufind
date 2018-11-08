<?php
/**
 * Admin Social Statistics Controller
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
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace VuFindAdmin\Controller;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * Class controls VuFind social statistical data.
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class PrivateStatsDownloadbyuserController extends AbstractAdmin
{
    /**
     * Social statistics reporting
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {
	
	$yearFrom = $_GET['yearFrom'];
	$monthFrom = $_GET['monthFrom'];
	$dayFrom = $_GET['dayFrom'];
	$yearTo = $_GET['yearTo'];
	$monthTo = $_GET['monthTo'];
	$dayTo = $_GET['dayTo'];
    	$source = $_GET['source'];

    	$surname = $_GET['surname'];
    	

    	
    	$current_year = date('Y');
	$current_month = date('m');
	$current_day = date('d');
    	

    	
    	
    	if (empty($yearFrom)) $yearFrom = $current_year;
	if (empty($monthFrom)&& $monthFrom<>"0") $monthFrom = $current_month;
	if (empty($dayFrom)&& $dayFrom<>"0") $dayFrom = $current_day;
	if (empty($yearTo)) $yearTo = $current_year;
	if (empty($monthTo)&& $monthTo<>"0") $monthTo = $current_month;
	if (empty($dayTo)&& $dayTo<>"0") $dayTo = $current_day;
    	

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
    	$downloadbyuser = $stats->getDownloadByUserSort($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo,0,"id","asc");
    	$fullnames = $stats->getFullName();

   	
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
    	

	if (isset($_POST['btnExportarExcelDownloadbyuser'])){
		$this->exportarexcelDownloadbyuser($downloadbyuser,$yearFrom,$monthFrom,$dayFrom,$yearTo,$monthTo,$dayTo,$surname,$source);
	}

        $view = $this->createViewModel(array('results' => $results, 'downloadbyuser'=>$downloadbyuser, 'fullnames'=>$fullnames));
        $view->setTemplate('admin/privatestatsdownloadbyuser/home');
        $view->request = $this->getRequest()->getPost();
    	return $view;

    }
    public function getResourceNumbers($id) {

    	$tableLatResources= $this->getTable('LatResources');
    	$latResourcesArray = $tableLatResources->getNumberOfResourcesByDeposit($yearFrom,$monthFrom,$dayFrom,$yearTo,$monthTo,$dayTo,$id);
    	//echo "escribe".count($latResourcesArray);
    	//print_r($latResourcesArray);
    	return $latResourcesArray;

    }

    public function exportarexcelDownloadbyuser($results,$yearFrom,$monthFrom,$dayFrom,$yearTo,$monthTo,$dayTo,$surname,$source){

        if ($source=="ajax"){
    	    $stats = $this->getTable('DownloadStat');
	    $results = $stats->getDownloadByUserCsv($yearFrom,$monthFrom,$dayFrom,$yearTo,$monthTo,$dayTo, $surname);
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

	

	$objSheet ->getCell('A'.$contador)->setValue('"'.$this->translate('Deposit'). '","' .$this->translate('Filename'). '","' .$this->translate('Internal node id'). '","' .$this->translate('Datetime').'"');

     foreach( $results as  $item=>$index ){

	$datetime = $index['datetime'];
	$filename = $index['filename'];
	$deposit_node_id = "MPI".$index['deposit_node_id'];
	$internal_node_id = $index['internal_node_id'];


	$contador=$contador+1;

	$objSheet ->getCell('A'.$contador)->setValue('"'.$deposit_node_id. '","' .$filename. '","' .$internal_node_id. '","' .$datetime. '"' );
	

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

