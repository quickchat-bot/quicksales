<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace News\Library\UnifiedSearch;

use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Library;
use SWIFT_Loader;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase;
use SWIFT_StringHighlighter;
use SWIFT_StringHTMLToText;

/**
 * The Unified Search Library for News App
 *
 * @property SWIFT_StringHTMLToText $StringHTMLToText
 * @property SWIFT_StringHighlighter $StringHighlighter
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch_news extends SWIFT_UnifiedSearchBase
{

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_query The Search Query
     * @param mixed $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_maxResults
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_query, $_interfaceType, SWIFT_Staff $_SWIFT_StaffObject, $_maxResults)
    {
        parent::__construct($_query, $_interfaceType, $_SWIFT_StaffObject, $_maxResults);
    }

    /**
     * Run the search and return results
     *
     * @author Varun Shoor
     * @return array Container of Result Objects
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalSearchResults = array();

        /**
         * ---------------------------------------------
         * STAFF SPECIFIC
         * ---------------------------------------------
         */
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_STAFF || $this->GetInterface() == SWIFT_Interface::INTERFACE_TESTS) {
            // News Items
            $_finalSearchResults[$this->Language->Get('us_news') . '::' . $this->Language->Get('us_created')] = $this->SearchNews();
        }

        return $_finalSearchResults;
    }

    /**
     * Search the News
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchNews()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_nwcanviewitems') == '0') {
            return array();
        }

        SWIFT_Loader::LoadModel('NewsItem:NewsItem', APP_NEWS);

        // First get the list of probable news items
        $_filterStaffNewsItemIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_NEWS, $this->GetStaff()->GetProperty('staffgroupid'));

        $_newsItemIDList = array();

        $this->Database->Query("SELECT newsitems.newsitemid AS newsitemid FROM " . TABLE_PREFIX . "newsitems AS newsitems
            LEFT JOIN " . TABLE_PREFIX . "newsitemdata AS newsitemdata ON (newsitems.newsitemid = newsitemdata.newsitemid)
            WHERE newsitems.newsstatus = '" . SWIFT_NewsItem::STATUS_PUBLISHED . "'
                AND (newsitems.start < '" . DATENOW . "' OR newsitems.start = '0')
                AND (newsitems.expiry > '" . DATENOW . "' OR newsitems.expiry = '0')
                AND (newsitems.staffvisibilitycustom = '0' OR (newsitems.staffvisibilitycustom = '1' AND newsitems.newsitemid IN (" . BuildIN($_filterStaffNewsItemIDList) . ")))
            ORDER BY newsitems.dateline DESC");
        while ($this->Database->NextRecord())
        {
            $_newsItemIDList[] = $this->Database->Record['newsitemid'];
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT newsitems.newsitemid AS newsitemid, newsitems.subject AS subject, newsitems.dateline AS dateline, newsitemdata.contents AS contents FROM " . TABLE_PREFIX . "newsitems AS newsitems
            LEFT JOIN " . TABLE_PREFIX . "newsitemdata AS newsitemdata ON (newsitems.newsitemid = newsitemdata.newsitemid)
            WHERE ((" . BuildSQLSearch('newsitems.subject', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('newsitemdata.contents', $this->GetQuery(), false, false) . "))
                AND newsitems.newsitemid IN (" . BuildIN($_newsItemIDList) . ")
            ORDER BY newsitems.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_extendedInfo = '';
            if (static::HasQuery($this->Database->Record['contents'], $this->GetQuery())) {
                $_highlightResult = implode(' ... ', $this->StringHighlighter->GetHighlightedRange($this->StringHTMLToText->Convert($this->Database->Record['contents']), $this->GetQuery(), 20));
                if (trim($_highlightResult) != '') {
                    $_extendedInfo .= '... ' . $_highlightResult . ' ...<br />';
                }
            }

            $_searchResults[] = array(htmlspecialchars($this->Database->Record['subject']), SWIFT::Get('basename') . '/News/NewsItem/ViewItem/' . $this->Database->Record['newsitemid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']), $_extendedInfo);
        }

        return $_searchResults;
    }
}
?>
