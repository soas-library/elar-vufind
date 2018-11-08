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

/**
 * Class controls VuFind social statistical data.
 *
 * @category VuFind2
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class SettingsController extends AbstractAdmin
{
    /**
     * Settings
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {
    	
    	//Tablas a utilizar
    	$tablaSettings= $this->getTable('Settings');
    	
    	//PublicByDepositStats
    	if(isset($_GET['PublicByDepositStats'])){
    		$PublicByDepositStats = $_GET['PublicByDepositStats'];
    	}else{
    		if(isset($_POST['PublicByDepositStats'])){
    			$PublicByDepositStats = $_POST['PublicByDepositStats'];
    		}else{
    			if (isset($_POST['processSettings']))
    				$PublicByDepositStats = 0;
    		}
    	}
    	$PublicByDepositStatsT = $tablaSettings->getPublicByDepositStats();
    	
    	//PublicStats
    	if(isset($_GET['PublicStats'])){
    		$PublicStats = $_GET['PublicStats'];
    	}else{
    		if(isset($_POST['PublicStats'])){
    			$PublicStats = $_POST['PublicStats'];
    		}else{
    			if (isset($_POST['processSettings']))
    				$PublicStats = 0;
    		}
    	}
    	$PublicStatsT = $tablaSettings->getPublicStats();
    	
    	//PrivateStats
    	if(isset($_GET['PrivateStats'])){
    		$PrivateStats = $_GET['PrivateStats'];
    	}else{
    		if(isset($_POST['PrivateStats'])){
    			$PrivateStats = $_POST['PrivateStats'];
    		}else{
    			if (isset($_POST['processSettings']))
    				$PrivateStats = 0;
    		}
    	}
    	$PrivateStatsT = $tablaSettings->getPrivateStats();

    	//PrivateByDepositStats
    	if(isset($_GET['PrivateByDepositStats'])){
    		$PrivateByDepositStats = $_GET['PrivateByDepositStats'];
    	}else{
    		if(isset($_POST['PrivateByDepositStats'])){
    			$PrivateByDepositStats = $_POST['PrivateByDepositStats'];
    		}else{
    			if (isset($_POST['processSettings']))
    				$PrivateByDepositStats = 0;
    		}
    	}
    	$PrivateByDepositStatsT = $tablaSettings->getPrivateByDepositStats();


    	
    	$changed = 0;
    	if(isset($PublicByDepositStats) && $PublicByDepositStats != $PublicByDepositStatsT) {
    		$tablaSettings->updatePublicByDepositStats($PublicByDepositStats);
    		$PublicByDepositStatsT = $PublicByDepositStats;
    		$changed = 1;
    	}
    	if(isset($PublicStats) && $PublicStats != $PublicStatsT) {
    		$tablaSettings->updatePublicStats($PublicStats);
    		$PublicStatsT = $PublicStats;
    		$changed = 1;
    	}
    	if(isset($PrivateStats) && $PrivateStats != $PrivateStatsT) {
    		$tablaSettings->updatePrivateStats($PrivateStats);
    		$PrivateStatsT = $PrivateStats;
    		$changed = 1;
    	}
    	if(isset($PrivateByDepositStats) && $PrivateByDepositStats != $PrivateByDepositStatsT) {
    		$tablaSettings->updatePrivateByDepositStats($PrivateByDepositStats);
    		$PrivateByDepositStatsT = $PrivateByDepositStats;
    		$changed = 1;
    	}
    	$this->getRequest()->getPost()->set('PublicByDepositStats', $PublicByDepositStatsT);
    	$this->getRequest()->getPost()->set('PublicStats', $PublicStatsT);
    	$this->getRequest()->getPost()->set('PrivateStats', $PrivateStatsT);
    	$this->getRequest()->getPost()->set('PrivateByDepositStats', $PrivateByDepositStatsT);
    	
    	if($changed == 1) {
    		$mens = $this->translate('Settings_changed');
		$this->flashMessenger()->setNamespace('info')
		    			->addMessage($mens);
    	} 
    	
    	
        $view = $this->createViewModel();
        $view->setTemplate('admin/settings/home');
        $view->request = $this->getRequest()->getPost();
        
        //$view->comments = $this->getTable('comments')->getStatistics();
        //$view->favorites = $this->getTable('userresource')->getStatistics();
        //$view->tags = $this->getTable('resourcetags')->getStatistics();
        return $view;
    }
}

