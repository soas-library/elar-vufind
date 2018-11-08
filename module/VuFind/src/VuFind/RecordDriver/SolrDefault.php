<?php
/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
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
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace VuFind\RecordDriver;
use VuFindCode\ISBN, VuFind\View\Helper\Root\RecordLink;

/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
 *
 * This should be used as the base class for all Solr-based record models.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class SolrDefault extends AbstractBase
{
    /**
     * These Solr fields should be used for snippets if available (listed in order
     * of preference).
     *
     * @var array
     */
    protected $preferredSnippetFields = [
        'contents', 'topic'
    ];

    /**
     * These Solr fields should NEVER be used for snippets.  (We exclude author
     * and title because they are already covered by displayed fields; we exclude
     * spelling because it contains lots of fields jammed together and may cause
     * glitchy output; we exclude ID because random numbers are not helpful).
     *
     * @var array
     */
    protected $forbiddenSnippetFields = [
        'author', 'author-letter', 'title', 'title_short', 'title_full',
        'title_full_unstemmed', 'title_auth', 'title_sub', 'spelling', 'id',
        'ctrlnum'
    ];

    /**
     * These are captions corresponding with Solr fields for use when displaying
     * snippets.
     *
     * @var array
     */
    protected $snippetCaptions = [];

    /**
     * Should we highlight fields in search results?
     *
     * @var bool
     */
    protected $highlight = false;

    /**
     * Should we include snippets in search results?
     *
     * @var bool
     */
    protected $snippet = false;

    /**
     * Hierarchy driver plugin manager
     *
     * @var \VuFind\Hierarchy\Driver\PluginManager
     */
    protected $hierarchyDriverManager = null;

    /**
     * Hierarchy driver for current object
     *
     * @var \VuFind\Hierarchy\Driver\AbstractBase
     */
    protected $hierarchyDriver = null;

    /**
     * Highlighting details
     *
     * @var array
     */
    protected $highlightDetails = [];

    /**
     * Search results plugin manager
     *
     * @var \VuFindSearch\Service
     */
    protected $searchService = null;

    /**
     * Should we use hierarchy fields for simple container-child records linking?
     *
     * @var bool
     */
    protected $containerLinking = false;

    /**
     * Constructor
     *
     * @param \Zend\Config\Config $mainConfig     VuFind main configuration (omit for
     * built-in defaults)
     * @param \Zend\Config\Config $recordConfig   Record-specific configuration file
     * (omit to use $mainConfig as $recordConfig)
     * @param \Zend\Config\Config $searchSettings Search-specific configuration file
     */
    public function __construct($mainConfig = null, $recordConfig = null,
        $searchSettings = null
    ) {
        // Turn on highlighting/snippets as needed:
        $this->highlight = !isset($searchSettings->General->highlighting)
            ? false : $searchSettings->General->highlighting;
        $this->snippet = !isset($searchSettings->General->snippets)
            ? false : $searchSettings->General->snippets;

        // Load snippet caption settings:
        if (isset($searchSettings->Snippet_Captions)
            && count($searchSettings->Snippet_Captions) > 0
        ) {
            foreach ($searchSettings->Snippet_Captions as $key => $value) {
                $this->snippetCaptions[$key] = $value;
            }
        }

        // Container-contents linking
        $this->containerLinking
            = !isset($mainConfig->Hierarchy->simpleContainerLinks)
            ? false : $mainConfig->Hierarchy->simpleContainerLinks;

        parent::__construct($mainConfig, $recordConfig);
    }

    /**
     * Add highlighting details to the object.
     *
     * @param array $details Details to add
     *
     * @return void
     */
    public function setHighlightDetails($details)
    {
        $this->highlightDetails = $details;
    }

    /**
     * Get access restriction notes for the record.
     *
     * @return array
     */
    public function getAccessRestrictions()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @return array
     */
    public function getAllSubjectHeadings()
    {
        $headings = [];
        foreach (['topic', 'geographic', 'genre', 'era'] as $field) {
            if (isset($this->fields[$field])) {
                $headings = array_merge($headings, $this->fields[$field]);
            }
        }

        // The Solr index doesn't currently store subject headings in a broken-down
        // format, so we'll just send each value as a single chunk.  Other record
        // drivers (i.e. MARC) can offer this data in a more granular format.
        $callback = function ($i) {
            return [$i];
        };
        return array_map($callback, array_unique($headings));
    }

    /**
     * Get all record links related to the current record. Each link is returned as
     * array.
     * NB: to use this method you must override it.
     * Format:
     * <code>
     * array(
     *        array(
     *               'title' => label_for_title
     *               'value' => link_name
     *               'link'  => link_URI
     *        ),
     *        ...
     * )
     * </code>
     *
     * @return null|array
     */
    public function getAllRecordLinks()
    {
        return null;
    }

    /**
     * Get award notes for the record.
     *
     * @return array
     */
    public function getAwards()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get notes on bibliography content.
     *
     * @return array
     */
    public function getBibliographyNotes()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get text that can be displayed to represent this record in
     * breadcrumbs.
     *
     * @return string Breadcrumb text to represent this record.
     */
    public function getBreadcrumb()
    {
        return $this->getShortTitle();
    }

    /**
     * Get the first call number associated with the record (empty string if none).
     *
     * @return string
     */
    public function getCallNumber()
    {
        $all = $this->getCallNumbers();
        return isset($all[0]) ? $all[0] : '';
    }

    /**
     * Get all call numbers associated with the record (empty string if none).
     *
     * @return array
     */
    public function getCallNumbers()
    {
        return isset($this->fields['callnumber-raw'])
            ? $this->fields['callnumber-raw'] : [];
    }
    /**
     * Return the first valid ISBN found in the record (favoring ISBN-10 over
     * ISBN-13 when possible).
     *
     * @return mixed
     */
    public function getCleanISBN()
    {
        // Get all the ISBNs and initialize the return value:
        $isbns = $this->getISBNs();
        $isbn13 = false;

        // Loop through the ISBNs:
        foreach ($isbns as $isbn) {
            // Strip off any unwanted notes:
            if ($pos = strpos($isbn, ' ')) {
                $isbn = substr($isbn, 0, $pos);
            }

            // If we find an ISBN-10, return it immediately; otherwise, if we find
            // an ISBN-13, save it if it is the first one encountered.
            $isbnObj = new ISBN($isbn);
            if ($isbn10 = $isbnObj->get10()) {
                return $isbn10;
            }
            if (!$isbn13) {
                $isbn13 = $isbnObj->get13();
            }
        }
        return $isbn13;
    }

    /**
     * Get just the base portion of the first listed ISSN (or false if no ISSNs).
     *
     * @return mixed
     */
    public function getCleanISSN()
    {
        $issns = $this->getISSNs();
        if (empty($issns)) {
            return false;
        }
        $issn = $issns[0];
        if ($pos = strpos($issn, ' ')) {
            $issn = substr($issn, 0, $pos);
        }
        return $issn;
    }

    /**
     * Get just the first listed OCLC Number (or false if none available).
     *
     * @return mixed
     */
    public function getCleanOCLCNum()
    {
        $nums = $this->getOCLC();
        return empty($nums) ? false : $nums[0];
    }

    /**
     * Get just the first listed UPC Number (or false if none available).
     *
     * @return mixed
     */
    public function getCleanUPC()
    {
        $nums = $this->getUPC();
        return empty($nums) ? false : $nums[0];
    }

    /**
     * Get the main corporate author (if any) for the record.
     *
     * @return string
     */
    public function getCorporateAuthor()
    {
        // Not currently stored in the Solr index
        return null;
    }

    /**
     * Get the date coverage for a record which spans a period of time (i.e. a
     * journal).  Use getPublicationDates for publication dates of particular
     * monographic items.
     *
     * @return array
     */
    public function getDateSpan()
    {
        return isset($this->fields['dateSpan']) ?
            $this->fields['dateSpan'] : [];
    }

    /**
     * Deduplicate author information into associative array with main/corporate/
     * secondary keys.
     *
     * @return array
     */
    public function getDeduplicatedAuthors()
    {
        $authors = [
            'main' => $this->getPrimaryAuthor(),
            'corporate' => $this->getCorporateAuthor(),
            'secondary' => $this->getSecondaryAuthors()
        ];

        // The secondary author array may contain a corporate or primary author;
        // let's be sure we filter out duplicate values.
        $duplicates = [];
        if (!empty($authors['main'])) {
            $duplicates[] = $authors['main'];
        }
        if (!empty($authors['corporate'])) {
            $duplicates[] = $authors['corporate'];
        }
        if (!empty($duplicates)) {
            $authors['secondary'] = array_diff($authors['secondary'], $duplicates);
        }

        return $authors;
    }

    /**
     * Get the edition of the current record.
     *
     * @return string
     */
    public function getEdition()
    {
        return isset($this->fields['edition']) ?
            $this->fields['edition'] : '';
    }

    /**
     * Get notes on finding aids related to the record.
     *
     * @return array
     */
    public function getFindingAids()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get an array of all the formats associated with the record.
     *
     * @return array
     */
    public function getFormats()
    {
        return isset($this->fields['format']) ? $this->fields['format'] : [];
    }

    /**
     * Get general notes on the record.
     *
     * @return array
     */
    public function getGeneralNotes()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get a highlighted author string, if available.
     *
     * @return string
     */
    public function getHighlightedAuthor()
    {
        // Don't check for highlighted values if highlighting is disabled:
        if (!$this->highlight) {
            return '';
        }
        return (isset($this->highlightDetails['author'][0]))
            ? $this->highlightDetails['author'][0] : '';
    }

    /**
     * Get a string representing the last date that the record was indexed.
     *
     * @return string
     */
    public function getLastIndexed()
    {
        return isset($this->fields['last_indexed'])
            ? $this->fields['last_indexed'] : '';
    }

    /**
     * Given a Solr field name, return an appropriate caption.
     *
     * @param string $field Solr field name
     *
     * @return mixed        Caption if found, false if none available.
     */
    public function getSnippetCaption($field)
    {
        return isset($this->snippetCaptions[$field])
            ? $this->snippetCaptions[$field] : false;
    }

    /**
     * Pick one line from the highlighted text (if any) to use as a snippet.
     *
     * @return mixed False if no snippet found, otherwise associative array
     * with 'snippet' and 'caption' keys.
     */
    public function getHighlightedSnippet()
    {
        // Only process snippets if the setting is enabled:
        if ($this->snippet) {
            // First check for preferred fields:
            foreach ($this->preferredSnippetFields as $current) {
                if (isset($this->highlightDetails[$current][0])) {
                    return [
                        'snippet' => $this->highlightDetails[$current][0],
                        'caption' => $this->getSnippetCaption($current)
                    ];
                }
            }

            // No preferred field found, so try for a non-forbidden field:
            if (isset($this->highlightDetails)
                && is_array($this->highlightDetails)
            ) {
                foreach ($this->highlightDetails as $key => $value) {
                    if (!in_array($key, $this->forbiddenSnippetFields)) {
                        return [
                            'snippet' => $value[0],
                            'caption' => $this->getSnippetCaption($key)
                        ];
                    }
                }
            }
        }

        // If we got this far, no snippet was found:
        return false;
    }

    /**
     * Get a highlighted title string, if available.
     *
     * @return string
     */
    public function getHighlightedTitle()
    {
        // Don't check for highlighted values if highlighting is disabled:
        if (!$this->highlight) {
            return '';
        }
        return (isset($this->highlightDetails['title'][0]))
            ? $this->highlightDetails['title'][0] : '';
    }

    /**
     * Get the institutions holding the record.
     *
     * @return array
     */
    public function getInstitutions()
    {
        return isset($this->fields['institution'])
            ? $this->fields['institution'] : [];
    }

    /**
     * Get an array of all ISBNs associated with the record (may be empty).
     *
     * @return array
     */
    public function getISBNs()
    {
        // If ISBN is in the index, it should automatically be an array... but if
        // it's not set at all, we should normalize the value to an empty array.
        return isset($this->fields['isbn']) && is_array($this->fields['isbn']) ?
            $this->fields['isbn'] : [];
    }

    /**
     * Get an array of all ISSNs associated with the record (may be empty).
     *
     * @return array
     */
    public function getISSNs()
    {
        // If ISSN is in the index, it should automatically be an array... but if
        // it's not set at all, we should normalize the value to an empty array.
        return isset($this->fields['issn']) && is_array($this->fields['issn']) ?
            $this->fields['issn'] : [];
    }

    /**
     * Get an array of all the languages associated with the record.
     *
     * @return array
     */
    public function getLanguages()
    {
        return isset($this->fields['language']) ?
            $this->fields['language'] : [];
    }

    /**
     * Get a LCCN, normalised according to info:lccn
     *
     * @return string
     */
    public function getLCCN()
    {
        // Get LCCN from Index
        $raw = isset($this->fields['lccn']) ? $this->fields['lccn'] : '';

        // Remove all blanks.
        $raw = preg_replace('{[ \t]+}', '', $raw);

        // If there is a forward slash (/) in the string, remove it, and remove all
        // characters to the right of the forward slash.
        if (strpos($raw, '/') > 0) {
            $tmpArray = explode("/", $raw);
            $raw = $tmpArray[0];
        }
        /* If there is a hyphen in the string:
            a. Remove it.
            b. Inspect the substring following (to the right of) the (removed)
               hyphen. Then (and assuming that steps 1 and 2 have been carried out):
                    i.  All these characters should be digits, and there should be
                    six or less.
                    ii. If the length of the substring is less than 6, left-fill the
                    substring with zeros until  the length is six.
        */
        if (strpos($raw, '-') > 0) {
            // haven't checked for i. above. If they aren't all digits, there is
            // nothing that can be done, so might as well leave it.
            $tmpArray = explode("-", $raw);
            $raw = $tmpArray[0] . str_pad($tmpArray[1], 6, "0", STR_PAD_LEFT);
        }
        return $raw;
    }

    /**
     * Get an array of newer titles for the record.
     *
     * @return array
     */
    public function getNewerTitles()
    {
        return isset($this->fields['title_new']) ?
            $this->fields['title_new'] : [];
    }

    /**
     * Get the OCLC number(s) of the record.
     *
     * @return array
     */
    public function getOCLC()
    {
        return isset($this->fields['oclc_num']) ?
            $this->fields['oclc_num'] : [];
    }

    /**
     * Support method for getOpenURL() -- pick the OpenURL format.
     *
     * @return string
     */
    protected function getOpenURLFormat()
    {
        // If we have multiple formats, Book, Journal and Article are most
        // important...
        $formats = $this->getFormats();
        if (in_array('Book', $formats)) {
            return 'Book';
        } else if (in_array('Article', $formats)) {
            return 'Article';
        } else if (in_array('Journal', $formats)) {
            return 'Journal';
        } else if (isset($formats[0])) {
            return $formats[0];
        } else if (strlen($this->getCleanISSN()) > 0) {
            return 'Journal';
        }
        return 'Book';
    }

    /**
     * Get the COinS identifier.
     *
     * @return string
     */
    protected function getCoinsID()
    {
        // Get the COinS ID -- it should be in the OpenURL section of config.ini,
        // but we'll also check the COinS section for compatibility with legacy
        // configurations (this moved between the RC2 and 1.0 releases).
        if (isset($this->mainConfig->OpenURL->rfr_id)
            && !empty($this->mainConfig->OpenURL->rfr_id)
        ) {
            return $this->mainConfig->OpenURL->rfr_id;
        }
        if (isset($this->mainConfig->COinS->identifier)
            && !empty($this->mainConfig->COinS->identifier)
        ) {
            return $this->mainConfig->COinS->identifier;
        }
        return 'vufind.svn.sourceforge.net';
    }

    /**
     * Get default OpenURL parameters.
     *
     * @return array
     */
    protected function getDefaultOpenURLParams()
    {
        // Get a representative publication date:
        $pubDate = $this->getPublicationDates();
        $pubDate = empty($pubDate) ? '' : $pubDate[0];

        // Start an array of OpenURL parameters:
        return [
            'ctx_ver' => 'Z39.88-2004',
            'ctx_enc' => 'info:ofi/enc:UTF-8',
            'rfr_id' => 'info:sid/' . $this->getCoinsID() . ':generator',
            'rft.title' => $this->getTitle(),
            'rft.date' => $pubDate
        ];
    }

    /**
     * Get OpenURL parameters for a book.
     *
     * @return array
     */
    protected function getBookOpenURLParams()
    {
        $params = $this->getDefaultOpenURLParams();
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:book';
        $params['rft.genre'] = 'book';
        $params['rft.btitle'] = $params['rft.title'];
        $series = $this->getSeries();
        if (count($series) > 0) {
            // Handle both possible return formats of getSeries:
            $params['rft.series'] = is_array($series[0]) ?
                $series[0]['name'] : $series[0];
        }
        $params['rft.au'] = $this->getPrimaryAuthor();
        $publishers = $this->getPublishers();
        if (count($publishers) > 0) {
            $params['rft.pub'] = $publishers[0];
        }
        $params['rft.edition'] = $this->getEdition();
        $params['rft.isbn'] = (string)$this->getCleanISBN();
        return $params;
    }

    /**
     * Get OpenURL parameters for an article.
     *
     * @return array
     */
    protected function getArticleOpenURLParams()
    {
        $params = $this->getDefaultOpenURLParams();
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
        $params['rft.genre'] = 'article';
        $params['rft.issn'] = (string)$this->getCleanISSN();
        // an article may have also an ISBN:
        $params['rft.isbn'] = (string)$this->getCleanISBN();
        $params['rft.volume'] = $this->getContainerVolume();
        $params['rft.issue'] = $this->getContainerIssue();
        $params['rft.spage'] = $this->getContainerStartPage();
        // unset default title -- we only want jtitle/atitle here:
        unset($params['rft.title']);
        $params['rft.jtitle'] = $this->getContainerTitle();
        $params['rft.atitle'] = $this->getTitle();
        $params['rft.au'] = $this->getPrimaryAuthor();

        $params['rft.format'] = 'Article';
        $langs = $this->getLanguages();
        if (count($langs) > 0) {
            $params['rft.language'] = $langs[0];
        }
        return $params;
    }

    /**
     * Get OpenURL parameters for an unknown format.
     *
     * @param string $format Name of format
     *
     * @return array
     */
    protected function getUnknownFormatOpenURLParams($format)
    {
        $params = $this->getDefaultOpenURLParams();
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:dc';
        $params['rft.creator'] = $this->getPrimaryAuthor();
        $publishers = $this->getPublishers();
        if (count($publishers) > 0) {
            $params['rft.pub'] = $publishers[0];
        }
        $params['rft.format'] = $format;
        $langs = $this->getLanguages();
        if (count($langs) > 0) {
            $params['rft.language'] = $langs[0];
        }
        return $params;
    }

    /**
     * Get OpenURL parameters for a journal.
     *
     * @return array
     */
    protected function getJournalOpenURLParams()
    {
        $params = $this->getUnknownFormatOpenURLParams('Journal');
        /* This is probably the most technically correct way to represent
         * a journal run as an OpenURL; however, it doesn't work well with
         * Zotero, so it is currently commented out -- instead, we just add
         * some extra fields and to the "unknown format" case.
        $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
        $params['rft.genre'] = 'journal';
        $params['rft.jtitle'] = $params['rft.title'];
        $params['rft.issn'] = $this->getCleanISSN();
        $params['rft.au'] = $this->getPrimaryAuthor();
         */
        $params['rft.issn'] = (string)$this->getCleanISSN();

        // Including a date in a title-level Journal OpenURL may be too
        // limiting -- in some link resolvers, it may cause the exclusion
        // of databases if they do not cover the exact date provided!
        unset($params['rft.date']);

        // If we're working with the SFX resolver, we should add a
        // special parameter to ensure that electronic holdings links
        // are shown even though no specific date or issue is specified:
        if (isset($this->mainConfig->OpenURL->resolver)
            && strtolower($this->mainConfig->OpenURL->resolver) == 'sfx'
        ) {
            $params['sfx.ignore_date_threshold'] = 1;
        }
        return $params;
    }

    /**
     * Get the OpenURL parameters to represent this record (useful for the
     * title attribute of a COinS span tag).
     *
     * @return string OpenURL parameters.
     */
    public function getOpenURL()
    {
        // Set up parameters based on the format of the record:
        switch ($format = $this->getOpenURLFormat()) {
        case 'Book':
            $params = $this->getBookOpenURLParams();
            break;
        case 'Article':
            $params = $this->getArticleOpenURLParams();
            break;
        case 'Journal':
            $params = $this->getJournalOpenURLParams();
            break;
        default:
            $params = $this->getUnknownFormatOpenURLParams($format);
            break;
        }

        // Assemble the URL:
        return http_build_query($params);
    }

    /**
     * Get the OpenURL parameters to represent this record for COinS even if
     * supportsOpenUrl() is false for this RecordDriver.
     *
     * @return string OpenURL parameters.
     */
    public function getCoinsOpenUrl()
    {
        return $this->getOpenUrl($this->supportsCoinsOpenUrl());
    }


    /**
     * Get an array of physical descriptions of the item.
     *
     * @return array
     */
    public function getPhysicalDescriptions()
    {
        return isset($this->fields['physical']) ?
            $this->fields['physical'] : [];
    }

    /**
     * Get the item's place of publication.
     *
     * @return array
     */
    public function getPlacesOfPublication()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get an array of playing times for the record (if applicable).
     *
     * @return array
     */
    public function getPlayingTimes()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get an array of previous titles for the record.
     *
     * @return array
     */
    public function getPreviousTitles()
    {
        return isset($this->fields['title_old']) ?
            $this->fields['title_old'] : [];
    }

    /**
     * Get the main author of the record.
     *
     * @return string
     */
    public function getPrimaryAuthor()
    {

        return isset($this->fields['author']) ?
            $this->fields['author'] : '';
    }

    /**
     * Get credits of people involved in production of the item.
     *
     * @return array
     */
    public function getProductionCredits()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get the publication dates of the record.  See also getDateSpan().
     *
     * @return array
     */
    public function getPublicationDates()
    {
        return isset($this->fields['publishDate']) ?
            $this->fields['publishDate'] : [];
    }

    /**
     * Get human readable publication dates for display purposes (may not be suitable
     * for computer processing -- use getPublicationDates() for that).
     *
     * @return array
     */
    public function getHumanReadablePublicationDates()
    {
        return $this->getPublicationDates();
    }

    /**
     * Get an array of publication detail lines combining information from
     * getPublicationDates(), getPublishers() and getPlacesOfPublication().
     *
     * @return array
     */
    public function getPublicationDetails()
    {
        $places = $this->getPlacesOfPublication();
        $names = $this->getPublishers();
        $dates = $this->getHumanReadablePublicationDates();

        $i = 0;
        $retval = [];
        while (isset($places[$i]) || isset($names[$i]) || isset($dates[$i])) {
            // Build objects to represent each set of data; these will
            // transform seamlessly into strings in the view layer.
            $retval[] = new Response\PublicationDetails(
                isset($places[$i]) ? $places[$i] : '',
                isset($names[$i]) ? $names[$i] : '',
                isset($dates[$i]) ? $dates[$i] : ''
            );
            $i++;
        }

        return $retval;
    }

    /**
     * Get an array of publication frequency information.
     *
     * @return array
     */
    public function getPublicationFrequency()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get the publishers of the record.
     *
     * @return array
     */
    public function getPublishers()
    {
        return isset($this->fields['publisher']) ?
            $this->fields['publisher'] : [];
    }

    /**
     * Get an array of information about record history, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHistory()
    {
        // Not supported by the Solr index -- implement in child classes.
        return [];
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHoldings()
    {
        // Not supported by the Solr index -- implement in child classes.
        return [];
    }

    /**
     * Get an array of strings describing relationships to other items.
     *
     * @return array
     */
    public function getRelationshipNotes()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get an array of all secondary authors (complementing getPrimaryAuthor()).
     *
     * @return array
     */
    public function getSecondaryAuthors()
    {
        return isset($this->fields['author2']) ?
            $this->fields['author2'] : [];
    }

    /**
     * Get an array of all series names containing the record.  Array entries may
     * be either the name string, or an associative array with 'name' and 'number'
     * keys.
     *
     * @return array
     */
    public function getSeries()
    {
        // Only use the contents of the series2 field if the series field is empty
        if (isset($this->fields['series']) && !empty($this->fields['series'])) {
            return $this->fields['series'];
        }
        return isset($this->fields['series2']) ?
            $this->fields['series2'] : [];
    }

    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     */
    public function getShortTitle()
    {
        return isset($this->fields['title_short']) ?
            $this->fields['title_short'] : '';
    }

    /**
     * Get the item's source.
     *
     * @return string
     */
    public function getSource()
    {
        // Not supported in base class:
        return '';
    }

    /**
     * Get the subtitle of the record.
     *
     * @return string
     */
    public function getSubtitle()
    {
        return isset($this->fields['title_sub']) ?
            $this->fields['title_sub'] : '';
    }

    /**
     * Get an array of technical details on the item represented by the record.
     *
     * @return array
     */
    public function getSystemDetails()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get an array of summary strings for the record.
     *
     * @return array
     */
    public function getSummary()
    {
        // We need to return an array, so if we have a description, turn it into an
        // array as needed (it should be a flat string according to the default
        // schema, but we might as well support the array case just to be on the safe
        // side:
        if (isset($this->fields['description'])
            && !empty($this->fields['description'])
        ) {
            return is_array($this->fields['description'])
                ? $this->fields['description'] : [$this->fields['description']];
        }

        // If we got this far, no description was found:
        return [];
    }

    /**
     * Get an array of note about the record's target audience.
     *
     * @return array
     */
    public function getTargetAudienceNotes()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small')
    {
        if (isset($this->fields['thumbnail']) && $this->fields['thumbnail']) {
            return $this->fields['thumbnail'];
        }
        $arr = [
            'author'     => mb_substr($this->getPrimaryAuthor(), 0, 300, 'utf-8'),
            'callnumber' => $this->getCallNumber(),
            'size'       => $size,
            'title'      => mb_substr($this->getTitle(), 0, 300, 'utf-8')
        ];
        if ($isbn = $this->getCleanISBN()) {
            $arr['isbn'] = $isbn;
        }
        if ($issn = $this->getCleanISSN()) {
            $arr['issn'] = $issn;
        }
        if ($oclc = $this->getCleanOCLCNum()) {
            $arr['oclc'] = $oclc;
        }
        if ($upc = $this->getCleanUPC()) {
            $arr['upc'] = $upc;
        }
        // If an ILS driver has injected extra details, check for IDs in there
        // to fill gaps:
        if ($ilsDetails = $this->getExtraDetail('ils_details')) {
            foreach (['isbn', 'issn', 'oclc', 'upc'] as $key) {
                if (!isset($arr[$key]) && isset($ilsDetails[$key])) {
                    $arr[$key] = $ilsDetails[$key];
                }
            }
        }
        return $arr;
    }

    /**
     * Get the full title of the record.
     *
     * @return string
     */
    public function getTitle()
    {
        return isset($this->fields['title']) ?
            $this->fields['title'] : '';
    }

    /**
     * Get the text of the part/section portion of the title.
     *
     * @return string
     */
    public function getTitleSection()
    {
        // Not currently stored in the Solr index
        return null;
    }

    /**
     * Get the statement of responsibility that goes with the title (i.e. "by John
     * Smith").
     *
     * @return string
     */
    public function getTitleStatement()
    {
        // Not currently stored in the Solr index
        return null;
    }

    /**
     * Get an array of lines from the table of contents.
     *
     * @return array
     */
    public function getTOC()
    {
        return isset($this->fields['contents'])
            ? $this->fields['contents'] : [];
    }

    /**
     * Get hierarchical place names
     *
     * @return array
     */
    public function getHierarchicalPlaceNames()
    {
        // Not currently stored in the Solr index
        return [];
    }

    /**
     * Get the UPC number(s) of the record.
     *
     * @return array
     */
    public function getUPC()
    {
        return isset($this->fields['upc_str_mv']) ?
            $this->fields['upc_str_mv'] : [];
    }

    /**
     * Return an array of associative URL arrays with one or more of the following
     * keys:
     *
     * <li>
     *   <ul>desc: URL description text to display (optional)</ul>
     *   <ul>url: fully-formed URL (required if 'route' is absent)</ul>
     *   <ul>route: VuFind route to build URL with (required if 'url' is absent)</ul>
     *   <ul>routeParams: Parameters for route (optional)</ul>
     *   <ul>queryString: Query params to append after building route (optional)</ul>
     * </li>
     *
     * @return array
     */
    public function getURLs()
    {
        // If non-empty, map internal URL array to expected return format;
        // otherwise, return empty array:
        if (isset($this->fields['url']) && is_array($this->fields['url'])) {
            $filter = function ($url) {
                return ['url' => $url];
            };
            return array_map($filter, $this->fields['url']);
        }
        return [];
    }

    /**
     * Get a hierarchy driver appropriate to the current object.  (May be false if
     * disabled/unavailable).
     *
     * @return \VuFind\Hierarchy\Driver\AbstractBase|bool
     */
    public function getHierarchyDriver()
    {
        if (null === $this->hierarchyDriver
            && null !== $this->hierarchyDriverManager
        ) {
            $type = $this->getHierarchyType();
            $this->hierarchyDriver = $type
                ? $this->hierarchyDriverManager->get($type) : false;
        }
        return $this->hierarchyDriver;
    }

    /**
     * Inject a hierarchy driver plugin manager.
     *
     * @param \VuFind\Hierarchy\Driver\PluginManager $pm Hierarchy driver manager
     *
     * @return SolrDefault
     */
    public function setHierarchyDriverManager(
        \VuFind\Hierarchy\Driver\PluginManager $pm
    ) {
        $this->hierarchyDriverManager = $pm;
        return $this;
    }

    /**
     * Get the hierarchy_top_id(s) associated with this item (empty if none).
     *
     * @return array
     */
    public function getHierarchyTopID()
    {
        return isset($this->fields['hierarchy_top_id'])
            ? $this->fields['hierarchy_top_id'] : [];
    }

    /**
     * Get the absolute parent title(s) associated with this item (empty if none).
     *
     * @return array
     */
    public function getHierarchyTopTitle()
    {
        return isset($this->fields['hierarchy_top_title'])
            ? $this->fields['hierarchy_top_title'] : [];
    }

    /**
     * Get an associative array (id => title) of collections containing this record.
     *
     * @return array
     */
    public function getContainingCollections()
    {
        // If collections are disabled or this record is not part of a hierarchy, go
        // no further....
        if (!isset($this->mainConfig->Collections->collections)
            || !$this->mainConfig->Collections->collections
            || !($hierarchyDriver = $this->getHierarchyDriver())
        ) {
            return false;
        }

        // Initialize some variables needed within the switch below:
        $isCollection = $this->isCollection();
        $titles = $ids = [];

        // Check config setting for what constitutes a collection, act accordingly:
        switch ($hierarchyDriver->getCollectionLinkType()) {
        case 'All':
            if (isset($this->fields['hierarchy_parent_title'])
                && isset($this->fields['hierarchy_parent_id'])
            ) {
                $titles = $this->fields['hierarchy_parent_title'];
                $ids = $this->fields['hierarchy_parent_id'];
            }
            break;
        case 'Top':
            if (isset($this->fields['hierarchy_top_title'])
                && isset($this->fields['hierarchy_top_id'])
            ) {
                foreach ($this->fields['hierarchy_top_id'] as $i => $topId) {
                    // Don't mark an item as its own parent -- filter out parent
                    // collections whose IDs match that of the current collection.
                    if (!$isCollection
                        || $topId !== $this->fields['is_hierarchy_id']
                    ) {
                        $ids[] = $topId;
                        $titles[] = $this->fields['hierarchy_top_title'][$i];
                    }
                }
            }
            break;
        }

        // Map the titles and IDs to a useful format:
        $c = count($ids);
        $retVal = [];
        for ($i = 0; $i < $c; $i++) {
            $retVal[$ids[$i]] = $titles[$i];
        }
        return $retVal;
    }

    /**
     * Get the value of whether or not this is a collection level record
     *
     * @return bool
     */
    public function isCollection()
    {
        if (!($hierarchyDriver = $this->getHierarchyDriver())) {
            // Not a hierarchy type record
            return false;
        }

        // Check config setting for what constitutes a collection
        switch ($hierarchyDriver->getCollectionLinkType()) {
        case 'All':
            return (isset($this->fields['is_hierarchy_id']));
        case 'Top':
            return isset($this->fields['is_hierarchy_title'])
                && isset($this->fields['is_hierarchy_id'])
                && in_array(
                    $this->fields['is_hierarchy_id'],
                    $this->fields['hierarchy_top_id']
                );
        default:
            // Default to not be a collection level record
            return false;
        }
    }

    /**
     * Get the positions of this item within parent collections.  Returns an array
     * of parent ID => sequence number.
     *
     * @return array
     */
    public function getHierarchyPositionsInParents()
    {
        $retVal = [];
        if (isset($this->fields['hierarchy_parent_id'])
            && isset($this->fields['hierarchy_sequence'])
        ) {
            foreach ($this->fields['hierarchy_parent_id'] as $key => $val) {
                $retVal[$val] = $this->fields['hierarchy_sequence'][$key];
            }
        }
        return $retVal;
    }

     /**
     * Get the titles of this item within parent collections.  Returns an array
     * of parent ID => sequence number.
     *
     * @return Array
     */
    public function getTitlesInHierarchy()
    {
        $retVal = [];
        if (isset($this->fields['title_in_hierarchy'])
            && is_array($this->fields['title_in_hierarchy'])
        ) {
            $titles = $this->fields['title_in_hierarchy'];
            $parentIDs = $this->fields['hierarchy_parent_id'];
            if (count($titles) === count($parentIDs)) {
                foreach ($parentIDs as $key => $val) {
                    $retVal[$val] = $titles[$key];
                }
            }
        }
        return $retVal;
    }

    /**
     * Get a list of hierarchy trees containing this record.
     *
     * @param string $hierarchyID The hierarchy to get the tree for
     *
     * @return mixed An associative array of hierarchy trees on success
     * (id => title), false if no hierarchies found
     */
    public function getHierarchyTrees($hierarchyID = false)
    {
        $hierarchyDriver = $this->getHierarchyDriver();
        if ($hierarchyDriver && $hierarchyDriver->showTree()) {
            return $hierarchyDriver->getTreeRenderer($this)
                ->getTreeList($hierarchyID);
        }
        return false;
    }

    /**
     * Get the Hierarchy Type (false if none)
     *
     * @return string|bool
     */
    public function getHierarchyType()
    {
        if (isset($this->fields['hierarchy_top_id'])) {
            $hierarchyType = isset($this->fields['hierarchytype'])
                ? $this->fields['hierarchytype'] : false;
            if (!$hierarchyType) {
                $hierarchyType = isset($this->mainConfig->Hierarchy->driver)
                    ? $this->mainConfig->Hierarchy->driver : false;
            }
            return $hierarchyType;
        }
        return false;
    }

    /**
     * Return the unique identifier of this record within the Solr index;
     * useful for retrieving additional information (like tags and user
     * comments) from the external MySQL database.
     *
     * @return string Unique identifier.
     */
    public function getUniqueID()
    {
        if (!isset($this->fields['id'])) {
            throw new \Exception('ID not set!');
        }
        return $this->fields['id'];
    }

    /**
     * Return an XML representation of the record using the specified format.
     * Return false if the format is unsupported.
     *
     * @param string     $format     Name of format to use (corresponds with OAI-PMH
     * metadataPrefix parameter).
     * @param string     $baseUrl    Base URL of host containing VuFind (optional;
     * may be used to inject record URLs into XML when appropriate).
     * @param RecordLink $recordLink Record link helper (optional; may be used to
     * inject record URLs into XML when appropriate).
     *
     * @return mixed         XML, or false if format unsupported.
     */
    public function getXML($format, $baseUrl = null, $recordLink = null)
    {
        // For OAI-PMH Dublin Core, produce the necessary XML:
        if ($format == 'oai_dc') {
            $dc = 'http://purl.org/dc/elements/1.1/';
            $xml = new \SimpleXMLElement(
                '<oai_dc:dc '
                . 'xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" '
                . 'xmlns:dc="' . $dc . '" '
                . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
                . 'xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ '
                . 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd" />'
            );
            $xml->addChild('title', htmlspecialchars($this->getTitle()), $dc);
            $primary = $this->getPrimaryAuthor();
            if (!empty($primary)) {
                $xml->addChild('creator', htmlspecialchars($primary), $dc);
            }
            $corporate = $this->getCorporateAuthor();
            if (!empty($corporate)) {
                $xml->addChild('creator', htmlspecialchars($corporate), $dc);
            }
            foreach ($this->getSecondaryAuthors() as $current) {
                $xml->addChild('creator', htmlspecialchars($current), $dc);
            }
            foreach ($this->getLanguages() as $lang) {
                $xml->addChild('language', htmlspecialchars($lang), $dc);
            }
            foreach ($this->getPublishers() as $pub) {
                $xml->addChild('publisher', htmlspecialchars($pub), $dc);
            }
            foreach ($this->getPublicationDates() as $date) {
                $xml->addChild('date', htmlspecialchars($date), $dc);
            }
            foreach ($this->getAllSubjectHeadings() as $subj) {
                $xml->addChild(
                    'subject', htmlspecialchars(implode(' -- ', $subj)), $dc
                );
            }
            if (null !== $baseUrl && null !== $recordLink) {
                $url = $baseUrl . $recordLink->getUrl($this);
                $xml->addChild('identifier', $url, $dc);
            }

            return $xml->asXml();
        }

        // Unsupported format:
        return false;
    }

    /**
     * Does the OpenURL configuration indicate that we should display OpenURLs in
     * the specified context?
     *
     * @param string $area 'results', 'record' or 'holdings'
     *
     * @return bool
     */
    public function openURLActive($area)
    {
        // Only display OpenURL link if the option is turned on and we have
        // an ISSN.  We may eventually want to make this rule more flexible.
        if (!$this->getCleanISSN()) {
            return false;
        }
        return parent::openURLActive($area);
    }

    /**
     * Get an array of strings representing citation formats supported
     * by this record's data (empty if none).  For possible legal values,
     * see /application/themes/root/helpers/Citation.php, getCitation()
     * method.
     *
     * @return array Strings representing citation formats.
     */
    protected function getSupportedCitationFormats()
    {
        return ['APA', 'Chicago', 'MLA'];
    }

    /**
     * Get the title of the item that contains this record (i.e. MARC 773s of a
     * journal).
     *
     * @return string
     */
    public function getContainerTitle()
    {
        return isset($this->fields['container_title'])
            ? $this->fields['container_title'] : '';
    }

    /**
     * Get the volume of the item that contains this record (i.e. MARC 773v of a
     * journal).
     *
     * @return string
     */
    public function getContainerVolume()
    {
        return isset($this->fields['container_volume'])
            ? $this->fields['container_volume'] : '';
    }

    /**
     * Get the issue of the item that contains this record (i.e. MARC 773l of a
     * journal).
     *
     * @return string
     */
    public function getContainerIssue()
    {
        return isset($this->fields['container_issue'])
            ? $this->fields['container_issue'] : '';
    }

    /**
     * Get the start page of the item that contains this record (i.e. MARC 773q of a
     * journal).
     *
     * @return string
     */
    public function getContainerStartPage()
    {
        return isset($this->fields['container_start_page'])
            ? $this->fields['container_start_page'] : '';
    }

    /**
     * Get the end page of the item that contains this record.
     *
     * @return string
     */
    public function getContainerEndPage()
    {
        // not currently supported by Solr index:
        return '';
    }

    /**
     * Get a full, free-form reference to the context of the item that contains this
     * record (i.e. volume, year, issue, pages).
     *
     * @return string
     */
    public function getContainerReference()
    {
        return isset($this->fields['container_reference'])
            ? $this->fields['container_reference'] : '';
    }

    /**
     * Get a sortable title for the record (i.e. no leading articles).
     *
     * @return string
     */
    public function getSortTitle()
    {
        return isset($this->fields['title_sort'])
            ? $this->fields['title_sort'] : parent::getSortTitle();
    }

    /**
     * Get longitude/latitude text (or false if not available).
     *
     * @return string|bool
     */
    public function getLongLat()
    {
        return isset($this->fields['long_lat'])
            ? $this->fields['long_lat'] : false;
    }

    /**
     * Get schema.org type mapping, an array of sub-types of
     * http://schema.org/CreativeWork, defaulting to CreativeWork
     * itself if nothing else matches.
     *
     * @return array
     */
    public function getSchemaOrgFormatsArray()
    {
        $types = [];
        foreach ($this->getFormats() as $format) {
            switch ($format) {
            case 'Book':
            case 'eBook':
                $types['Book'] = 1;
                break;
            case 'Video':
            case 'VHS':
                $types['Movie'] = 1;
                break;
            case 'Photo':
                $types['Photograph'] = 1;
                break;
            case 'Map':
                $types['Map'] = 1;
                break;
            case 'Audio':
                $types['MusicAlbum'] = 1;
                break;
            default:
                $types['CreativeWork'] = 1;
            }
        }
        return array_keys($types);
    }

    /**
     * Get schema.org type mapping, expected to be a space-delimited string of
     * sub-types of http://schema.org/CreativeWork, defaulting to CreativeWork
     * itself if nothing else matches.
     *
     * @return string
     */
    public function getSchemaOrgFormats()
    {
        return implode(' ', $this->getSchemaOrgFormatsArray());
    }

    /**
     * Get information on records deduplicated with this one
     *
     * @return array Array keyed by source id containing record id
     */
    public function getDedupData()
    {
        return isset($this->fields['dedup_data'])
            ? $this->fields['dedup_data']
            : [];
    }

    /**
     * Attach a Search Results Plugin Manager connection and related logic to
     * the driver
     *
     * @param \VuFindSearch\Service $service Search Service Manager
     *
     * @return void
     */
    public function attachSearchService(\VuFindSearch\Service $service)
    {
        $this->searchService = $service;
    }

    /**
     * Get the number of child records belonging to this record
     *
     * @return int Number of records
     */
    public function getChildRecordCount()
    {
        // Shortcut: if this record is not the top record, let's not find out the
        // count. This assumes that contained records cannot contain more records.
        if (!$this->containerLinking
            || empty($this->fields['is_hierarchy_id'])
            || null === $this->searchService
        ) {
            return 0;
        }

        $safeId = addcslashes($this->fields['is_hierarchy_id'], '"');
        $query = new \VuFindSearch\Query\Query(
            'hierarchy_parent_id:"' . $safeId . '"'
        );
        return $this->searchService->search('Solr', $query, 0, 0)->getTotal();
    }

    /**
     * Get the container record id.
     *
     * @return string Container record id (empty string if none)
     */
    public function getContainerRecordID()
    {
        return $this->containerLinking
            && !empty($this->fields['hierarchy_parent_id'])
            ? $this->fields['hierarchy_parent_id'][0] : '';
    }

    /**
     * CUSTOM CODE FOR SOAS LIBRARY ELAR
     * @author Simon Barron sb174@soas.ac.uk
     *
     */
    public function getRealSummary()
    {
        return isset($this->fields['summary'])
            ? $this->fields['summary'] : array();
    }
    public function getSummaryofDeposit()
    {  
        return isset($this->fields['summary_of_deposit'])
            ? $this->fields['summary_of_deposit'] : array();
    }
    public function getGroupRepresented()
    {
        return isset($this->fields['group_represented'])
            ? $this->fields['group_represented'] : array();
    }
    public function getCharacteristics()
    {
        return isset($this->fields['characteristics'])
            ? $this->fields['characteristics'] : array();
    }
    public function getSpecialCharacteristics()
    {  
        return isset($this->fields['special_characteristics'])
            ? $this->fields['special_characteristics'] : array();
    }
    public function getHistory()
    {
        return isset($this->fields['history'])
            ? $this->fields['history'] : array();
    }
    public function getHistoryofDeposit()
    {  
        return isset($this->fields['deposit_history'])
            ? $this->fields['deposit_history'] : array();
    }
    public function getOther()
    {
        return isset($this->fields['other'])
            ? $this->fields['other'] : array();
    }
    public function getOtherInformation()
    {  
        return isset($this->fields['other_information'])
            ? $this->fields['other_information'] : array();
    }
    public function getAcknowledgement()
    {
        return isset($this->fields['acknowledgement'])
            ? $this->fields['acknowledgement'] : array();
    }
    public function getDepositName()
    {
        return isset($this->fields['deposit_name'])
            ? $this->fields['deposit_name'] : '';
    }
    public function getDepositTitle()
    {
        return isset($this->fields['deposit_title'])
            ? $this->fields['deposit_title'] : '';
    }
    public function getDepositCoverImage()
    {
        return isset($this->fields['deposit_cover_image'])
            ? $this->fields['deposit_cover_image'] : '';
    }
    public function getDepositPageImage()
    {
        return isset($this->fields['depositpage_image'])
            ? $this->fields['depositpage_image'] : array();
    }
    public function getDepositPagePodcast()
    {
        return isset($this->fields['depositpage_podcast'])
            ? $this->fields['depositpage_podcast'] : '';
    }
    public function getDepositPageShowreel()
    {
        return isset($this->fields['depositpage_showreel'])
            ? $this->fields['depositpage_showreel'] : '';
    }
    public function getDepositorPicture()
    {
        return isset($this->fields['depositor_picture'])
            ? $this->fields['depositor_picture'] : '';
    }
    public function getDepositNotes()
    {  
        return isset($this->fields['depsoit_notes'])
            ? $this->fields['depsoit_notes'] : '';
    }
    public function getDepositContents()
    {  
        return isset($this->fields['deposit_contents'])
            ? $this->fields['deposit_contents'] : '';
    }
    public function getDepositor()
    {
        return isset($this->fields['depositor'])
            ? $this->fields['depositor'] : array();
    }
    /** SCB CorpusId **/
    public function getCorpusId()
    {
        return isset($this->fields['corpusid'])
            ? $this->fields['corpusid'] : array();
    }
    /** SCB CorpusId **/
    /** SCB SetSpec **/
    public function getSetSpec()
    {
        return isset($this->fields['setSpec'])
            ? $this->fields['setSpec'] : array();
    }
    /** SCB SetSpec **/
    /** SCB Depositor id **/
    public function getDepositorId()
    {
        return isset($this->fields['depositor_id'])
            ? $this->fields['depositor_id'] : array();
    }
    /** SCB Depositor id **/
    /** SCB Nationality and affilitaion **/
    public function getDepositorNationality()
    {
        return isset($this->fields['depositor_nationality'])
            ? $this->fields['depositor_nationality'] : array();
    }

    /** SCB Nationality and affiliation **/
    public function getDepositorAffiliation()
    {
        return isset($this->fields['depositor_affiliation'])
            ? $this->fields['depositor_affiliation'] : array();
    }

    /** SCB Nationality and affiliation **/
    
    /** SCB Language information **/
    public function getLanguageInformation()
    {
        return isset($this->fields['language_information'])
            ? $this->fields['language_information'] : array();
    }
    /** SCB Language information **/
    
    /** SCB Deposit Status **/
    public function getDepositStatus()
    {
        return isset($this->fields['deposit_status'])
            ? $this->fields['deposit_status'] : array();
    }
    /** SCB Deposit Status **/

    public function getDepositorImage()
    {
        return isset($this->fields['depositor_image'])
            ? $this->fields['depositor_image'] : array();
    }
    public function getBundleDate()
    {
        return isset($this->fields['bundle_date_created'])
            ? $this->fields['bundle_date_created'] : '';
    }
    public function getBundleName()
    {
        return isset($this->fields['bundle_name'])
            ? $this->fields['bundle_name'] : '';
    }
    public function getBundleTitle()
    {
        return isset($this->fields['bundle_title'])
            ? $this->fields['bundle_title'] : '';
    }
    public function getBundleLocation()
    {
        return isset($this->fields['bundle_location'])
            ? array_filter(array_unique($this->fields['bundle_location'])) : array();
    }
    public function getBundleDescription()
    {
        return isset($this->fields['bundle_description'])
            ? array_filter(array_unique($this->fields['bundle_description'])) : array();
    }
    public function getBundleTag()
    {
        return isset($this->fields['bundle_tag_string'])
            ? $this->fields['bundle_tag_string'] : array();
    }
    public function getBundleGenre()
    {
        return isset($this->fields['bundle_genre_string'])
            ? array_filter(array_unique($this->fields['bundle_genre_string'])) : array();
    }
    public function getBundleKeywords()
    {
        return isset($this->fields['bundle_keywords'])
            ? array_filter(array_unique($this->fields['bundle_keywords'])) : array();
    }
    public function getResourceKeyword()
    {
        return isset($this->fields['resource_keyword_string'])
            ? array_filter(array_unique($this->fields['resource_keyword_string'])) : array();
    }

    public function getBundleLanguageName()
    {
        return isset($this->fields['bundle_language_name_string'])
            ? array_filter(array_unique($this->fields['bundle_language_name_string'])) : array();
    }
    
    public function getBundlePartic()
    {
        return isset($this->fields['bundle_participant_name'])
            ? array_filter(array_unique($this->fields['bundle_participant_name'])) : array();
    }
    public function getBundleParticipant()
    {
        $mixed = array();
        $participants = array();
        $roles = array();
        if (isset($this->fields['bundle_participant_role'])) 
        	$role=$this->fields['bundle_participant_role'];
        else $role="";
        if (isset($this->fields['bundle_participant_name'])) 
        	$c = count($this->fields['bundle_participant_name']);
        else $c=0;
        $prevName = '';
        for ($ind = 0; $ind < $c; $ind++) {
        	if ($ind != 0){
        		$roles[] = $r;
        	}
        	$prevName = $this->fields['bundle_participant_name'][$ind];
        	$i=0;
        	$r='';
        	$participants[] = $prevName;
        	foreach($this->fields['bundle_participant_name'] as $name) {
	            if (strcmp($prevName,$name) == 0 & strcmp($r,'') ==0) {
	            	$r = strtolower($role[$i]);
	            } else if (strcmp($prevName,$name) == 0){
	            	$r = $r . ', ' . strtolower($role[$i]);
	            }
	            $i =$i+1;
	        }
        	if ($c == 1 || $c == $ind+1){
        		$roles[] = $r;
        	}
        }
        
        $participants = array_unique($participants);
        
        foreach($participants as $posicion=>$rol)
	{
		$ro = explode(", ",$roles[$posicion]);
		$ro = array_unique($ro);
        	$roles_string = array_filter($ro);
        	$roles_string = array_values($ro);
        	if(empty($roles_string))
			$part_roles = $rol;
		else
			$part_roles = $rol . " (" . implode(", ", $roles_string) . ")";
		$mixed[] = $part_roles;
	}
        
        return isset($mixed)
            ? $mixed : array();
    }

    public function getResourceAccessProtocol()
    {
        return isset($this->fields['resource_access_protocol'])
            ? $this->fields['resource_access_protocol'] : array();
    }
    public function getResourceLink()
    {
        return isset($this->fields['resource_link'])
            ? $this->fields['resource_link'] : array();
    }
    public function getResourceType()
    {
        return isset($this->fields['resource_type'])
            ? $this->fields['resource_type'] : array();
    }
    public function getShortDescription()
    {
        return isset($this->fields['short_description'])
            ? $this->fields['short_description'] : array();
    }
    //SCB New functions for list display
    public function getDepositLanguage()
    {
	$i=0;
        $mixed = array();
        $iso=$this->fields['deposit_language_name_iso'];
        foreach($this->fields['deposit_language_name'] as $language) {
            if ($iso[$i]!=" " && $iso[$i]!="")
            $mixed[] = $language . " (". $iso[$i]. ")";
            else
            $mixed[] = $language ;
            $i =$i+1;
        }
        return isset($mixed)
            ? $mixed : array();
    }

    //SCB New functions for only language to display in the title page
    public function getDepositLanguageTitle()
    {
        $mixed = array();
        foreach($this->fields['deposit_language_name'] as $language) {
            $mixed[] = $language ;
        }
        return isset($mixed)
            ? $mixed : array();
    }

    public function getDepositCountry()
    {
        return isset($this->fields['deposit_country_name'])
            ? $this->fields['deposit_country_name'] : array();
    }

    /** SCB Latitude **/
    public function getLatitude()
    {
        return isset($this->fields['deposit_latitude'])
            ? $this->fields['deposit_latitude'] : array();
    }
    /** SCB Latitude **/

    /** SCB Longitude **/
    public function getLongitude()
    {
        return isset($this->fields['deposit_longitude'])
            ? $this->fields['deposit_longitude'] : array();
    }
    /** SCB Longitude **/

    /** SCB ELDP ID **/
    public function getEldpId()
    {
        return isset($this->fields['project_id_number'])
            ? $this->fields['project_id_number'] : array();
    }
    /** SCB ELDP ID **/

    /** SCB ELDP ID TEXT **/
    public function getEldpIdText()
    {
        return isset($this->fields['project_id'])
            ? $this->fields['project_id'] : array();
    }
    /** SCB ELDP ID TEXT **/
    /** SCB FUNDING BODY **/
    public function getFundingBody()
    {
        return isset($this->fields['funding_body'])
            ? $this->fields['funding_body'] : array();
    }
    /** SCB FUNDING BODY **/


    /** SCB Latitude **/
    public function getDeletionMessage()
    {
        return isset($this->fields['deletion_message'])
            ? $this->fields['deletion_message'] : array();
    }
    /** SCB Latitude **/

    /** SCB Condition of Access **/
    public function getConditionOfAccess()
    {
        return isset($this->fields['resource_condition_access'])
            ? array_filter(array_unique($this->fields['resource_condition_access'])) : array();
    }
    /** SCB Condition of Access **/

    /** SCB Restrictions **/
    public function getRestrictions()
    {
        return isset($this->fields['resource_restrictions'])
            ? array_filter(array_unique($this->fields['resource_restrictions'])) : array();
    }
    /** SCB Restrictions **/

    /** SCB Languages **/
    public function getResourceLanguages()
    {
    	$mixed = array();
    	$langs_solr = $this->fields['resource_language'];
    	$langs_solr = array_filter(array_unique($langs_solr));
    	$langs_solr = array_values($langs_solr);
    	
    	$lang_arr = array();
	$uses_arr = array();
    	
    	foreach($langs_solr as $l) {
    		$l = str_replace(")","", $l);
	    	$separar = explode("(", $l);
	    	foreach($separar as $posicion=>$sep) {
	    		$lang_arr[] = $separar[0];
	    	}
	    	
	    	$uses_arr[] = explode(", ",implode(", ",$separar));
    	}
    	
    	$lang_arr = array_unique($lang_arr);
    	$lang_arr =array_values($lang_arr);
    	
    	$la = array();
    	$us = array();
    	$c = count($uses_arr);
        $uses_string = '';
    	
    	for ($ind = 0; $ind < $c; $ind++) {
    		$countU = count($uses_arr[$ind]);
    		for ($in = 0; $in < $countU; $in++) {
    			if($in == 0) {
    				$posArray = array_search($uses_arr[$ind][$in], $lang_arr);
    			} else if(strcmp($us[$posArray],'') !=0)
    				$us[$posArray] = $us[$posArray] . ", " . strtolower($uses_arr[$ind][$in]);
    			else
    				$us[$posArray] = strtolower($uses_arr[$ind][$in]);
    		}
	}
	
	foreach($lang_arr as $posicion=>$lang)
	{
		$lu = explode(", ",$us[$posicion]);
		$lang_us = array_unique($lu);
        	$lang_us = array_filter($lang_us);
        	$lang_us = array_values($lang_us);
        	if(empty($lang_us))
			$lang_uses = $lang;
		else
			$lang_uses = $lang . " (" . implode(", ", $lang_us) . ")";
		$mixed[] = $lang_uses;
	}
	
        return isset($mixed)
            ? $mixed : array();

    }
    /** SCB Language **/

    /** SCB Recording Equipment **/
    public function getRecordingEquipment()
    {
        return isset($this->fields['resource_rec_equipment'])
            ? array_filter(array_unique($this->fields['resource_rec_equipment'])) : array();
    }
    /** SCB Recording Equipment **/

    /** SCB Recording Conditions **/
    public function getRecordingConditions()
    {
        return isset($this->fields['resource_rec_condition'])
            ? array_filter(array_unique($this->fields['resource_rec_condition'])) : array();
    }
    /** SCB Recording Conditions **/

    /** SCB Additional Info **/
    public function getRecordingAdditional()
    {
        return isset($this->fields['resource_additional_info'])
            ? array_filter(array_unique($this->fields['resource_additional_info'])) : array();
    }
    /** SCB Additional Info **/

    /** SCB Resource participant **/
    public function getResourceParticipant()
    {
        return isset($this->fields['resource_participant'])
            ? array_filter(array_unique($this->fields['resource_participant'])) : array();
    }
    /** SCB Resource participant **/
    
    /** SCB Deposit statistics **/
    public function getDepositStat($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
	$stats = $this->getDbTable('DownloadStat');
	$stat = $stats->getDepositStatisticsDisplay($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
	return $stat;
    }
    /** SCB Deposit statistics  **/
   
    /** SCB Deposit uploaded files statistics **/
    public function getDepositUploadedFilesStat($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
	$stats = $this->getDbTable('LatResources');
	$stat = $stats->getDepositUploadedFilesStatisticsDisplay($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
	return $stat;
    }
    /** SCB Deposit uploaded files statistics  **/
 
    /** SCB Deposit user id by email **/
    public function getDepositUserId($email, $firstname, $surname)
    {
        $users = $this->getDbTable('User');
        $user_id = $users->getUserByEmail($email);
	if(empty($user_id)){
		if(strcmp($firstname, 'Lat') === 0 && strcmp($surname, 'user') === 0)
			return 'LU';
		else if(strcmp($firstname, 'Open') === 0 && strcmp($surname, 'Download') === 0)
			return 'OD';
		else
			return '_' . $firstname . '_' . $surname;
	} else {
        	return $user_id;
	}
    }
    /** SCB Deposit statistics  **/

    /** SCB let Deposit statistics by user **/
    public function getRealTimeStadistics($u, $node_id)
    {
        return $this->hasILS() ? $this->holdLogic->getUserPermission($u, $node_id) : [];
    }
    /** SCB let Deposit statistics by user **/
    
    /** SCB let Deposit statistics - On/off **/
    public function getSettingsPublicByDepositStats()
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->getPublicByDepositStats();
	return $value;
    }
    /** SCB let Deposit statistics - On/off **/
    
    /** SCB let Public statistics - On/off **/
    public function getSettingsPublicStats()
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->getPublicStats();
	return $value;
    }
    /** SCB let Public statistics - On/off **/
    
    /** SCB let Private statistics - On/off **/
    public function getSettingsPrivateStats()
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->getPrivateStats();
	return $value;
    }
    /** SCB let Private statistics - On/off **/
    /** SCB let Deposit statistics - On/off **/
    public function getSettingsPrivateByDepositStats()
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->getPrivateByDepositStats();
	return $value;
    }
    /** SCB let Deposit statistics - On/off **/
    /** SCB set Deposit statistics - On/off **/
    public function setSettingsPublicByDepositStats($val)
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->updatePublicByDepositStats($val);
	return $value;
    }
    /** SCB set Deposit statistics - On/off **/
    
    /** SCB set Public statistics - On/off **/
    public function setSettingsPublicStats($val)
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->updatePublicStats($val);
	return $value;
    }
    /** SCB set Public statistics - On/off **/
    
    /** SCB set Private statistics - On/off **/
    public function setSettingsPrivateStats($val)
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->updatePrivateStats($val);
	return $value;
    }
    /** SCB set Private statistics - On/off **/
    /** SCB set Deposit statistics - On/off **/
    public function setSettingsPrivateByDepositStats($val)
    {
	$settings = $this->getDbTable('Settings');
	$value = $settings->updatePrivateByDepositStats($val);
	return $value;
    }
    /** SCB set Deposit statistics - On/off **/
    /** SCB O/U/S files statistics **/
    public function getFilesStat($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
	$stats = $this->getDbTable('DownloadStat');
	$stat = $stats->getFilesStatisticsDisplay($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
	return $stat;
    }
    /** SCB O/U/S files statistics **/
    
    /** SCB Deposit hits statistics **/
    public function getHitsStat($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
	$stats = $this->getDbTable('UserStats');
	$stat = $stats->getDepositHitsDisplay($deposit_node_id, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);
	return $stat;
    }
    /** SCB Deposit hits statistics **/

    /** SCB Years to use in the filters **/
    public function getYears()
    {
    	$stats = $this->getDbTable('DownloadStat');
    	$user_stats = $this->getDbTable('UserStats');
	
	// Years to use in the filter
	$yearsUserStats = $user_stats->getYears();
	//print_r($yearsUserStats);
	//echo '</br>';
	$yearsStats = $stats->getYears();
	//print_r($yearsStats);
	//echo '</br>';
	
	$years = array_unique (array_merge (array_filter($yearsUserStats), array_filter($yearsStats)));
	arsort($years);
	
	return $years;
    }
    /** SCB Years to use in the filters **/

    /** SCB Get the bundleID and one resource **/
    public function getAccess1($nodeid)
    {
        return $this->hasILS() ? $this->holdLogic->getBundlesIdAndOneResource($nodeid) : [];
        //return 'BundleID ' . $nodeid;
    }
    /** SCB Get the bundleID and all child resources **/

    /** SCB Get the bundleID and all child resources **/
    public function getAccess2($nodeid)
    {
        return $this->hasILS() ? $this->holdLogic->getBundlesIdAndResources($nodeid) : [];
        //return 'BundleID ' . $nodeid;
    }
    /** SCB Get the bundleID and all child resources **/

    /** SCB Get the collectionID with all bundles **/
    public function getAccess3($nodeid)
    {
        return $this->hasILS() ? $this->holdLogic->getCollectionIdAndBundles($nodeid) : [];
        //return 'CollectionID ' . $nodeid;
    }
    /** SCB Get the collectionID with all bundles **/

}