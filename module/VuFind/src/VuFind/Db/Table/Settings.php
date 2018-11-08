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
 * Table Definition for settings
 *
 * @category VuFind2
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class Settings extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('settings', 'VuFind\Db\Row\Settings');
    }

    /**
     * Set settings public_by_deposit_stats
     */
    public function updatePublicByDepositStats($value)
    {
        $sql = "UPDATE settings SET value=" . $value . " WHERE name = 'public_by_deposit_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	return true;
    }
    
    /**
     * Set settings public_stats
     */
    public function updatePublicStats($value)
    {
        $sql = "UPDATE settings SET value=" . $value . " WHERE name = 'public_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	return true;
    }
    
    /**
     * Set settings private_stats
     */
    public function updatePrivateStats($value)
    {
        $sql = "UPDATE settings SET value=" . $value . " WHERE name = 'private_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	return true;
    }

    /**
     * Set settings private_by_deposit_stats
     */
    public function updatePrivateByDepositStats($value)
    {
        $sql = "UPDATE settings SET value=" . $value . " WHERE name = 'private_by_deposit_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	return true;
    }

    /**
     * Get settings public_by_deposit_stats
     */

    public function getPublicByDepositStats()
    {
        $sql ="SELECT value FROM settings WHERE name = 'public_by_deposit_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$value = $row['value'];
	}
	return $value;
    }
    
     /**
     * Get settings public_stats
     */

    public function getPublicStats()
    {
        $sql ="SELECT value FROM settings WHERE name = 'public_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$value = $row['value'];
	}
	return $value;
    }
    
     /**
     * Get settings private_stats
     */

    public function getPrivateStats()
    {
        $sql ="SELECT value FROM settings WHERE name = 'private_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$value = $row['value'];
	}
	return $value;
    }
    
    /**
     * Get settings private_by_deposit_stats
     */

    public function getPrivateByDepositStats()
    {
        $sql ="SELECT value FROM settings WHERE name = 'private_by_deposit_stats';";
		 
		
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$value = $row['value'];
	}
	return $value;
    }
}
