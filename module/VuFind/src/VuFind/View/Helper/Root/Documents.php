<?php
/**
 * Documents view helper
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
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace VuFind\View\Helper\Root;
use Zend\View\Helper\AbstractHelper;

/**
 * Documents view helper
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Documents extends AbstractHelper implements \Zend\Log\LoggerAwareInterface, \VuFindHttp\HttpServiceAwareInterface
{
    /**
     * VuFind configuration
     *
     * @var \Zend\Config\Config
     */
    protected $config;

    /**
     * Object representing Id
     *
     */
    protected $id;

    /**
     * HTTP service
     *
     * @var \VuFindHttp\HttpServiceInterface
     */
    protected $httpService = null;


    /**
     * Logger
     *
     * @var \Zend\Log\LoggerInterface|bool
     */
    protected $logger = false;

    /**
     * Constructor
     *
     * @param \Zend\Config\Config $config VuFind configuration
     */
    public function __construct(\Zend\Config\Config $config)
    {
        $this->config = $config;
    }


    /**
     * Set logger
     *
     * @param \Zend\Log\LoggerInterface $logger Logger
     *
     * @return void
     */
    public function setLogger(\Zend\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Retrieve results for the providers specified.
     *
     * @param string $id      id to use for lookup
     * @param string $providers Provider configuration
     *
     * @return array
     */
    protected function getResults($id)
    {
        $results = array();
        if (!$this->setId($id)) {
            return $results;
        }
        try {
            $results[$provider] = $this->loadAuthors($id);
        } catch (\Exception $e) {
            unset($results[$provider]);
        }

        return $results;
    }

    /**
     * Do the actual work of loading the reviews.
     *
     * @param string $id id of book to find reviews for
     *
     * @return array
     */
    public function __invoke($id)
    {
        return $this->getResults($id);
    }


    /**
     * Guardian Reviews
     *
     * This method is responsible for connecting to the Guardian and abstracting
     * reviews for the specific ISBN.
     *
     * @param string $id Guardian API key
     *
     * @throws \Exception
     * @return array     Returns array with review data
     * @author Eoghan ÓCarragá <eoghan.ocarragain@gmail.com>
     */
    protected function loadAuthors($id)
    {

        $urlBase= $this->config['Index']['url'];

        //Varios
        $url =  $urlBase . "/solr/biblio/select?fl=%2A%2Cscore&hl=true&hl.fl=%2A&hl.simple.pre=%7B%7B%7B%7BSTART_HILITE%7D%7D%7D%7D&hl.simple.post=%7B%7B%7B%7BEND_HILITE%7D%7D%7D%7D&wt=json&json.nl=arrarr&q=%22". urlencode($id) . "%22&qf=author%5E100+author_fuller%5E50+author2+author_additional&qt=dismax";

        $url = $urlBase . "/biblio/select?fl=%2A%2Cscore&spellcheck=true&fq=author_browse%3A%22Fern%C3%A1ndez%2C+Alberto%22&sort=score+desc&hl=true&hl.fl=%2A&hl.simple.pre=%7B%7B%7B%7BSTART_HILITE%7D%7D%7D%7D&hl.simple.post=%7B%7B%7B%7BEND_HILITE%7D%7D%7D%7D&spellcheck.dictionary=default&wt=json&json.nl=arrarr&spellcheck.q=alberto+fernandez&q=%28%28%28_query_%3A%22%7B%21dismax+qf%3D%5C%22author%5E100+author_fuller%5E50+author2+author_additional%5C%22+%7Dalberto+fernandez%22%29%29%29";


       $url = $urlBase . "/biblio/select?fl=%2A%2Cscore&spellcheck=true&fq=author_browse%3A%22" .
urlencode($id)
       . "%22&sort=score+desc&hl=true&hl.fl=%2A&hl.simple.pre=%7B%7B%7B%7BSTART_HILITE%7D%7D%7D%7D&hl.simple.post=%7B%7B%7B%7BEND_HILITE%7D%7D%7D%7D&spellcheck.dictionary=default&wt=json&json.nl=arrarr&spellcheck.q=" .  urlencode($id) . "&q=%28%28%28_query_%3A%22%7B%21dismax+qf%3D%5C%22author%5E100+author_fuller%5E50+author2+author_additional%5C%22+%7D" . urlencode($id) . "%22%29%29%29";



        //echo $url;

        //find out if there are any reviews
        $result = $this->getHttpClient($url)->send();

        // Was the request successful?
        if ($result->isSuccess()) {
            // parse json from response
            $data = json_decode($result->getBody(), true);
            if ($data) {
                $result = array();
                $i = 0;
                foreach ($data['response']['docs'] as $review) {
                    //print_r($review); 
                    $result[$i]['id'] = $review['id'];
                    $result[$i]['title'] = $review['title'];
                    $result[$i]['author'] = $review['author'];
                    $result[$i]['publishDate'] = $review['publishDate'][0];
                    $i++;
                }
                return $result;
            } else {
                throw new \Exception('Author without articles published.');
            }
        } else {
            return array();
        }
    }
    /**
     * Get an HTTP client
     *
     * @param string $url URL for client to use
     *
     * @return \Zend\Http\Client
     */
    protected function getHttpClient($url)
    {
        if (null === $this->httpService) {
            throw new \Exception('HTTP service missing.');
        }
        return $this->httpService->createClient($url);
    }

    /**
     * Set the HTTP service to be used for HTTP requests.
     *
     * @param HttpServiceInterface $service HTTP service
     *
     * @return void
     */
    public function setHttpService(\VuFindHttp\HttpServiceInterface $service)
    {
        $this->httpService = $service;
    }

    protected function setId($id)
    {
        // We can't proceed without an ISBN:
        if (empty($id)) {
            return false;
        }

        $this->id = $id;
        return true;
    }

    /**
     * Turn an XML response into a DOMDocument object.
     *
     * @param string $xml XML to load.
     *
     * @return DOMDocument|bool Document on success, false on failure.
     */
    protected function xmlToDOMDocument($xml)
    {
        $dom = new DOMDocument();
        return $dom->loadXML($xml) ? $dom : false;
    }


}

