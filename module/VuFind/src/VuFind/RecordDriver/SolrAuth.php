<?php
/**
 * Model for Solr authority records.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2011.
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
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace VuFind\RecordDriver;

/**
 * Model for Solr authority records.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
/** SCB Now extends SolrDefault */
class SolrAuth extends SolrDefault
{
    /**
     * Used for identifying database records
     *
     * @var string
     */
    protected $resourceSource = 'SolrAuth';

    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     */
    public function getShortTitle()
    {
        // No difference between short and long titles for authority records:
        return $this->getTitle();
    }

    /**
     * Get the full title of the record.
     *
     * @return string
     */
    public function getTitle()
    {
        return isset($this->fields['heading']) ? $this->fields['heading'] : '';
    }

    /**
     * SCB Get the affiliation of the record.
     *
     * @return string
     */
    public function getAffiliation()
    {
        return isset($this->fields['affiliation']) ? $this->fields['affiliation'] : '';
    }

    /**
     * SCB Get the nationality of the record.
     *
     * @return string
     */
    public function getNationality()
    {
        return isset($this->fields['nationality']) ? $this->fields['nationality'] : '';
    }

    /**
     * SCB Get the urls of the record.
     *
     * @return string
     */
    public function getUrls()
    {
        return isset($this->fields['url']) ? $this->fields['url'] : '';
    }

    /**
     * SCB Get the full name of the record.
     *
     * @return string
     */
    public function getFullName()
    {
        return isset($this->fields['full_name']) ? $this->fields['full_name'] : '';
    }

    /**
     * SCB Get the image of the record.
     *
     * @return string
     */
    public function getImage()
    {
        return isset($this->fields['image']) ? $this->fields['image'] : '';
    }

    /**
     * Get the see also references for the record.
     *
     * @return array
     */
    public function getSeeAlso()
    {
        return isset($this->fields['see_also'])
            && is_array($this->fields['see_also'])
            ? $this->fields['see_also'] : [];
    }

    /**
     * Get the use for references for the record.
     *
     * @return array
     */
    public function getUseFor()
    {
        return isset($this->fields['use_for'])
            && is_array($this->fields['use_for'])
            ? $this->fields['use_for'] : [];
    }

    /**
     * Get a raw LCCN (not normalized).  Returns false if none available.
     *
     * @return string|bool
     */
    public function getRawLCCN()
    {
        $lccn = $this->getFirstFieldValue('010');
        if (!empty($lccn)) {
            return $lccn;
        }
        $lccns = $this->getFieldArray('700', ['0']);
        foreach ($lccns as $lccn) {
            if (substr($lccn, 0, '5') == '(DLC)') {
                return substr($lccn, 5);
            }
        }
        return false;
    }

    /**
     * SCB New method
     * Set raw data to initialize the object.
     *
     * @param mixed $data Raw data representing the record; Record Model
     * objects are normally constructed by Record Driver objects using data
     * passed in from a Search Results object.  In this case, $data is a Solr record
     * array containing MARC data in the 'fullrecord' field.
     *
     * @return void
     */
    public function setRawData($data)
    {   
        $this->fields = $data;
    }


    /**
     * Return an array of related record suggestion objects (implementing the
     * \VuFind\Related\RelatedInterface) based on the current record.
     *
     * @param \VuFind\Related\PluginManager $factory Related module plugin factory
     * @param array                         $types   Array of relationship types to
     * load; each entry should be a service name (i.e. 'Similar' or 'Editions')
     * optionally followed by a colon-separated list of parameters to pass to the
     * constructor.  If the parameter is set to null instead of an array, default
     * settings will be loaded from config.ini.
     *
     * @return array
     */
    public function getRelated(\VuFind\Related\PluginManager $factory, $types = null)
    {
        if (is_null($types)) {
            $types = isset($this->recordConfig->Record->relatedAuth) ?
                $this->recordConfig->Record->relatedAuth : array();
        }

        $retVal = array();
        foreach ($types as $current) {
            $parts = explode(':', $current);
            $type = $parts[0];
            $params = isset($parts[1]) ? $parts[1] : null;
            if ($factory->has($type)) {
                $plugin = $factory->get($type);
                $plugin->init($params, $this);
                $retVal[] = $plugin;
            } else {
                throw new \Exception("Related module {$type} does not exist.");
            }
        }
        return $retVal;
    }

}
