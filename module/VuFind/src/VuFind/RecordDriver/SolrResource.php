<?php
/**
 * Model for MARC records in Solr.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 * Copyright (C) The National Library of Finland 2015.
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
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace VuFind\RecordDriver;
use VuFind\Exception\ILS as ILSException,
    VuFind\View\Helper\Root\RecordLink,
    VuFind\XSLT\Processor as XSLTProcessor;

/**
 * Model for MARC records in Solr.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
class SolrResource extends SolrDefault
{

    /**
     * MARC record
     *
     * @var \File_MARC_Record
     */
    protected $marcRecord;

    /**
     * ILS connection
     *
     * @var \VuFind\ILS\Connection
     */

    protected $ils = null;

    /**
     * Hold logic
     *
     * @var \VuFind\ILS\Logic\Holds
     */
    protected $holdLogic;

    /**
     * Title hold logic
     *
     * @var \VuFind\ILS\Logic\TitleHolds
     */
    protected $titleHoldLogic;



    /**
     * Attach an ILS connection and related logic to the driver
     *
     * @param \VuFind\ILS\Connection       $ils            ILS connection
     * @param \VuFind\ILS\Logic\Holds      $holdLogic      Hold logic handler
     * @param \VuFind\ILS\Logic\TitleHolds $titleHoldLogic Title hold logic handler
     *
     * @return void
     */
    public function attachILS(\VuFind\ILS\Connection $ils,
        \VuFind\ILS\Logic\Holds $holdLogic,
        \VuFind\ILS\Logic\TitleHolds $titleHoldLogic
    ) { 
        $this->ils = $ils;
        $this->holdLogic = $holdLogic;
        $this->titleHoldLogic = $titleHoldLogic;
    }

    /**
     * Do we have an attached ILS connection?
     *
     * @return bool
     */
    protected function hasILS()
    {
        return null !== $this->ils;
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHoldings()
    {
        return $this->hasILS() ? $this->holdLogic->getHoldings(
            $this->getUniqueID(), $this->getConsortialIDs()
        ) : [];
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeResources($email, $setSpec)
    {
        return $this->hasILS() ? $this->holdLogic->getResources(
            $this->getUniqueID(), $this->getConsortialIDs(), $email, $setSpec
        ) : [];
    }


    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getDepositorFromNodeId($nodeId)
    {
        return $this->hasILS() ? $this->holdLogic->getDepositor($nodeId) : [];
    }


    /**
     * Get an array of information about record history, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHistory()
    {
        // Get Acquisitions Data
        if (!$this->hasILS()) {
            return [];
        }
            return [];
    }

    /**
     * Get a link for placing a title level hold.
     *
     * @return mixed A url if a hold is possible, boolean false if not
     */
    public function getRealTimeTitleHold()
    {
        return false;
    }

    /**
     * Returns true if the record supports real-time AJAX status lookups.
     *
     * @return bool
     */
    public function supportsAjaxStatus()
    {
        return true;
    }


    /**
     * Return the list of "source records" for this consortial record.
     *
     * @return array
     */
    public function getConsortialIDs()
    {
        //return $this->getFieldArray('035', 'a', true);
        return 'patata';
    }
    
    /**
     * Return the list of "source records" for this consortial record.
     *
     * @return array
     */
    public function insertStat($firstname, $surname, $userID, $path, $filename, $internaNodeID, $date, $depositorName, $depositNodeID)
    {
	$stats = $this->getDbTable('DownloadStat');
	$stat = $stats->insertStat($firstname, $surname, $userID, $path, $filename, $internaNodeID, $date, $depositorName, $depositNodeID);
	return print_r($stat);
    }
    
    //SCB Get key
    /**
     * Get the Key
     *
     * @return string
     */
    public function getConfigKey()
    {
        if (isset($this->mainConfig->Authentication->ils_encryption_key)
            && !empty($this->mainConfig->Authentication->ils_encryption_key)
        ) {
            return $this->mainConfig->Authentication->ils_encryption_key;
        }
        return '18c2e18bb80581f76284f747d71de62aee193ece';
    }
    //SCB Get key

    //SCB Encrypt link
    /**
     * Get the Key
     *
     * @return string
     */
    public function encryptLink($key, $format, $link, $extension)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, utf8_encode($format.";".$link), MCRYPT_MODE_ECB, $iv);
	$encrypted_string_trim = trim($encrypted_string);
	//$base_encode = base64_encode($encrypted_string_trim);
	$base_encode = base64_encode(trim(utf8_encode($format.";".$link)));
	$url_encode = urlencode($base_encode);
	$url_entera = $url_encode . "." . $extension;
	$url_replace = str_replace("%","-p-",$url_entera);
	return $url_replace;

    }
    //SCB Encrypt link

}
