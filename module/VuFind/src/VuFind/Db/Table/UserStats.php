<?php
/**
 * Table Definition for statistics (user data)
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
 * Table Definition for user statistics
 *
 * @category VuFind2
 * @package  Db_Table
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class UserStats extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('user_stats');
    }

    /**
     * Returns the list of most popular browsers with use counts
     *
     * @param boolean $withVersions Include browser version numbers?
     * True = versions (Firefox 12.0) False = names only (Firefox).
     * @param integer $limit        How many to return
     *
     * @return array
     */
    public function getBrowserStats($withVersions = false, $limit = 5)
    {
        $callback = function ($select) use ($withVersions, $limit) {
            if ($withVersions) {
                $select->columns(
                    [
                        'browserName' => new Expression(
                            'CONCAT_WS(" ",?,?)',
                            ['browser', 'browserVersion'],
                            [
                                Expression::TYPE_IDENTIFIER,
                                Expression::TYPE_IDENTIFIER
                            ]
                        ),
                        'count' => new Expression(
                            'COUNT(DISTINCT (?))',
                            ['session'],
                            [Expression::TYPE_IDENTIFIER]
                        )
                    ]
                );
                $select->group('browserName');
            } else {
                $select->columns(
                    [
                        'browserName' => 'browser',
                        'count' => new Expression(
                            'COUNT(DISTINCT (?))',
                            ['session'],
                            [Expression::TYPE_IDENTIFIER]
                        )
                    ]
                );
                $select->group('browser');
            }
            $select->limit($limit);
            $select->order('count DESC');
        };
        
        return $this->select($callback)->toArray();
    }
    /**
     * Returns the count of number of page hits
     *
     * @param date $current_year Year to use in the query
     * date $current_month Month to use in the query
     *
     * @return number
     */
    public function getNumberOfHits($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        /*if($current_month == 0) {
	        if($current_year == 0) {
		        $sql="SELECT count(*)"
		        ." FROM vufind.user_stats"
		        ." where url <> 'Authenticate';";
		} else {
		        $sql="SELECT count(*)"
		        ." FROM vufind.user_stats"
		        ." where YEAR(datestamp) = ".$current_year
		        ." and url <> 'Authenticate';";
		}
        } else {
                if($current_year == 0) {
	        	$sql="SELECT count(*)"
		        ." FROM vufind.user_stats"
		        ." where MONTH(datestamp) = ".$current_month
		        ." and url <> 'Authenticate';";
		} else {
	        	$sql="SELECT count(*)"
		        ." FROM vufind.user_stats"
		        ." where YEAR(datestamp) = ".$current_year
		        ." and MONTH(datestamp) = ".$current_month
		        ." and url <> 'Authenticate';";
			}
        }*/

	$sql = 'SELECT count(*) FROM vufind.user_stats '
	.'where DATE(datestamp) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom 
	.'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo 
	.'" and url <> "Authenticate";';

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num = $row['count(*)'];
	}
        return $num;
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
		        ." FROM vufind.user_stats"
		        ." where url = 'Authenticate';";
		} else {
		        $sql="SELECT count(*)"
		        ." FROM vufind.user_stats"
		        ." where YEAR(datestamp) = ".$current_year
		        ." and url = 'Authenticate';";
		}
	} else {
		if($current_year == 0) {	
		        $sql="SELECT count(*)"
		        ." FROM vufind.user_stats"
		        ." where MONTH(datestamp) = ".$current_month
		        ." and url = 'Authenticate';";
		} else {
		        $sql="SELECT count(*)"
		        ." FROM vufind.user_stats"
		        ." where YEAR(datestamp) = ".$current_year
		        ." and MONTH(datestamp) = ".$current_month
		        ." and url = 'Authenticate';";
		}
	}*/

	$sql = 'SELECT count(*) FROM vufind.user_stats '
	.'where DATE(datestamp) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom 
	.'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo 
	.'" and url = "Authenticate";';

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num = $row['count(*)'];
	}
	
        return $num;
    }

    /**
     * Returns the count of number of page hits for this deposit
     *
     * @param date $current_year Year to use in the query
     * date $current_month Month to use in the query
     *
     * @return number
     */
     
    public function getDepositHitsDisplay($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        /*if($current_month == 0) {
	        if($current_year == 0) {
		        $sql='SELECT count(*)'
		        .' FROM user_stats' 
		        .' where url like "%'.$deposit_node_id . '%"';
		} else {
		        $sql='SELECT count(*)'
		        .' FROM user_stats' 
		        .' where url like "%'.$deposit_node_id . '%" '
		        .' and YEAR(datestamp) = '.$current_year;
		}
	} else {
	        if($current_year == 0) {
			$sql='SELECT count(*)'
		        .' FROM user_stats' 
		        .' where url like "%'.$deposit_node_id . '%" '
		        .' and MONTH(datestamp) = '.$current_month;
	        } else {
			$sql='SELECT count(*)'
		        .' FROM user_stats' 
		        .' where url like "%'.$deposit_node_id . '%" '
		        .' and YEAR(datestamp) = '.$current_year
		        .' and MONTH(datestamp) = '.$current_month;
		}
	}*/

	$sql = 'SELECT count(*) FROM user_stats WHERE url like "%' . $deposit_node_id . 
	'%" AND DATE(datestamp) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . 
	'" AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '";';
	
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num = $row['count(*)'];
	}
        return $num;
    }


    /**
     * Returns the count of clics per visit
     *
     * @param date $current_year Year to use in the query
     * date $current_month Month to use in the query
     *
     * @return number
     */
    public function getClicsPerVisit($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        /*if($current_month == 0) {
                if($current_year == 0) {
		        $sql="select avg(numero) as num from ".
		        "(select count(*) as numero, concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as uno";
	        } else {
		        $sql="select avg(numero) as num from ".
		        "(select count(*) as numero, concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "and YEAR(datestamp) = ".$current_year ." ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as uno";
	        }
	} else {
		if($current_year == 0) {
			$sql="select avg(numero) as num from ".
		        "(select count(*) as numero, concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "and MONTH(datestamp) = ".$current_month ." ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as uno";
	        } else {
			$sql="select avg(numero) as num from ".
		        "(select count(*) as numero, concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "and YEAR(datestamp) = ".$current_year ." ".
		        "and MONTH(datestamp) = ".$current_month ." ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as uno";
	        }
	}*/
	
	$sql = 'select avg(numero) as num '
	.'from (select count(*) as numero, '
	.'concat(day(datestamp),"-",month(datestamp),"-",year(datestamp),"-",hour(datestamp),session) '
	.'from user_stats where url <> "Authenticate" '
	.'AND DATE(datestamp) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'group by concat(day(datestamp),"-",month(datestamp),"-",year(datestamp),"-",hour(datestamp),session) '
	.'order by 1 desc) as uno;';

        $num = 0;
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num = $row['num'];
	}
        if (is_null($num)) $num=0;
        return $num;
    }

    /**
     * Returns the count of clics per visit
     *
     * @param date $current_year Year to use in the query
     * date $current_month Month to use in the query
     *
     * @return number
     */
    public function getDurationOfVisit($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        /*if($current_month == 0) {
	        if($current_year == 0) {
		        $sql="select avg(duration) as duration from ".
		        "(select (max(datestamp) -  min(datestamp)) as duration ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as durationtime";
		} else {
		        $sql="select avg(duration) as duration from ".
		        "(select (max(datestamp) -  min(datestamp)) as duration ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "and YEAR(datestamp) = ".$current_year ." ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as durationtime";
		}
	} else {
	        if($current_year == 0) {
			$sql="select avg(duration) as duration from ".
		        "(select (max(datestamp) -  min(datestamp)) as duration ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "and MONTH(datestamp) = ".$current_month ." ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as durationtime";
		} else {
			$sql="select avg(duration) as duration from ".
		        "(select (max(datestamp) -  min(datestamp)) as duration ".
		        "from user_stats ".
		        "where url <> 'Authenticate' ".
		        "and YEAR(datestamp) = ".$current_year ." ".
		        "and MONTH(datestamp) = ".$current_month ." ".
		        "group by concat(day(datestamp),'-',month(datestamp),'-',year(datestamp),'-',hour(datestamp),session) ".
		        "order by 1 desc) as durationtime";
		}
	}*/

	$sql = 'select avg(duration) as duration '
	.'from (select (max(datestamp) -  min(datestamp)) as duration '
	.'from user_stats '
	.'where url <> "Authenticate" '
	.'AND DATE(datestamp) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'group by concat(day(datestamp),"-",month(datestamp),"-",year(datestamp),"-",hour(datestamp),session) '
	.'order by 1 desc) as durationtime;';

        $num=0;

	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$num = $row['duration'];
	}
        if (is_null($num)) $num=0;
        return $num;
    }

    /**
     * Returns the percentage of visits for each country
     *
     * @param date $current_year Year to use in the query
     * date $current_month Month to use in the query
     *
     * @return number
     */
     
    public function getCountryPercentage($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
      
        /*if($current_month == 0) {
	        if($current_year == 0) {
		        $sql="select country, count(*)  AS percentage ".
		        " from user_stats ".
		        " where url<> 'Authenticate' ".
		        " group by country ".
		        " order by percentage desc";
		} else {
		        $sql="select country, count(*)  AS percentage ".
		        " from user_stats ".
		        " where url<> 'Authenticate' ".
		        " and YEAR(datestamp) = ".$current_year ." ".
		        " group by country ".
		        " order by percentage desc";
		}
	} else {
		if($current_year == 0) {
		        $sql="select country, count(*)  AS percentage ".
		        " from user_stats ".
		        " where url<> 'Authenticate' ".
		        " and MONTH(datestamp) = ".$current_month  ." ".
		        " group by country ".
		        " order by percentage desc";
		} else {
		        $sql="select country, count(*)  AS percentage ".
		        " from user_stats ".
		        " where url<> 'Authenticate' ".
		        " and YEAR(datestamp) = ".$current_year ." ".
		        " and MONTH(datestamp) = ".$current_month  ." ".
		        " group by country ".
		        " order by percentage desc";
		}
	}*/
	
	$sql = 'select country, count(*) AS percentage '
	.'from user_stats '
	.'where url<> "Authenticate" '
	.'AND DATE(datestamp) between "' . $yearFrom . '-' . $monthFrom . '-' . $dayFrom . '" '
	.'AND "' . $yearTo . '-' . $monthTo . '-' . $dayTo . '" '
	.'group by country '
	.'order by percentage desc;';
	
	$statement = $this->adapter->query($sql);
	
	$results = $statement->execute();
	foreach ($results as $row) {
		$num[] = array('country'=> $row['country'], 'percentage' => $row['percentage']);
	}
	if (count($num)==0) $num[]=array('country'=> 'No entries', 'percentage' => '0');
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
        
        $sql="select distinct(year(datestamp)) as year from user_stats;";
	$statement = $this->adapter->query($sql);
	$results = $statement->execute();
	
	foreach ($results as $row) {
		$filters[] = $row['year'];
	}
        return $filters;
    }

}