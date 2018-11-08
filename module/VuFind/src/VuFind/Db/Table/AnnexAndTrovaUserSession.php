<?php
/**
 * Table Definition for resource
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
use Zend\Db\Sql\Expression;

/**
 * Table Definition for resource
 *
 * @category VuFind2
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class AnnexAndTrovaUserSession extends Gateway
{
    /**
     * Date converter
     *
     * @var \VuFind\Date\Converter
     */
    protected $dateConverter;

    /**
     * Constructor
     *
     * @param \VuFind\Date\Converter $converter Date converter
     */
    public function __construct()
    {
        parent::__construct('annex_and_trova_user_session', 'VuFind\Db\Row\AnnexAndTrovaUserSession');
    }

    /**
     * Save the lat user session.
     *
     * @param string                            $id     Record ID to look up
     * @param string                            $source Source of record to look up
     * @param bool                              $create If true, create the row if it
     * does not
     * yet exist.
     * @param \VuFind\RecordDriver\AbstractBase $driver A record driver for the
     * resource being created (optional -- improves efficiency if provided, but will
     * be auto-loaded as needed if left null).
     *
     * @return \VuFind\Db\Row\Resource|null Matching row if found or created, null
     * otherwise.
     */
    public function saveAnnexAndTrovaUserSession($datetime, $user_id) {
            $result = $this->createRow();
            $result->datetime = $datetime;
            $result->user_id = $user_id;
            $result->save();
    }


    /**
     * Delete all the entries
     *
     * @return void
     */
    public function truncate()
    {
        $sql = "delete from annex_and_trova_user_session";
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	return $results;
    }

     /**
     * Returns the count of number of page hits
     *
     * @param date $current_year Year to use in the query
     * date $current_month Month to use in the query
     *
     * @return number
     */
    public function getNumberOfLogins($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        /*if($current_month == 0) {	
	        if($current_year == 0) {	
		        $sql="SELECT count(*)"
		        ." FROM vufind.annex_and_trova_user_session;";
		} else {
		        $sql="SELECT count(*)"
		        ." FROM vufind.annex_and_trova_user_session"
		        ." where YEAR(datetime) = ".$current_year
		        ." ;";
		}
	} else {
		if($current_year == 0) {	
		        $sql="SELECT count(*)"
		        ." FROM vufind.annex_and_trova_user_session"
		        ." where MONTH(datetime) = ".$current_month
		        ." ;";
		} else {
		        $sql="SELECT count(*)"
		        ." FROM vufind.annex_and_trova_user_session"
		        ." where YEAR(datetime) = ".$current_year
		        ." and MONTH(datetime) = ".$current_month
		        ." ;";
		}
	}*/
	
	$sql = 'SELECT count(*) FROM vufind.annex_and_trova_user_session '
	.'where DATE(datetime) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom 
	.'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo .'";';

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num = $row['count(*)'];
	}
	
        return $num;
    }

}
