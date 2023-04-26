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

namespace Knowledgebase\Library\UnifiedSearch;

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Loader;
use SWIFT_StringHighlighter;
use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase;

/**
 * The Unified Search Library for Knowledgebase App
 *
 * @author Varun Shoor
 *
 * @property SWIFT_StringHighlighter $StringHighlighter
 */
class SWIFT_UnifiedSearch_knowledgebase extends SWIFT_UnifiedSearchBase
{
    /**
     * Run the search and return results
     *
     * @author Varun Shoor
     * @return array Container of Result Objects
     * @throws SWIFT_Exception
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
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_STAFF ||
            $this->GetInterface() == SWIFT_Interface::INTERFACE_TESTS) {
            // Knowledgebase Categories & Articles
            $_finalSearchResults[$this->Language->Get('us_knowledgebase') . '::' . $this->Language->Get('us_created')] = array_merge($this->SearchCategories(), $this->SearchArticles());
        }

        return $_finalSearchResults;
    }

    /**
     * Search the Knowledgebase Categories
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchCategories()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_kbcanviewarticles') == '0') {
            return array();
        }

        // First get the list of probable categories
        SWIFT_Loader::LoadModel('Category:KnowledgebaseCategory', APP_KNOWLEDGEBASE);

        /*
         * Bug Fix : Saloni Dhall
         *
         * SWIFT-2573 : Unified search only searches base knowledgebase categories, not child categories
         *
         * Comments : Further in MySQL statement, categoryTypeList falls back in case of Inherit
         */
        $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseCategory::RetrieveSubCategoryIDListExtended(array(
                                                                                                            SWIFT_KnowledgebaseCategory::TYPE_GLOBAL,
                                                                                                            SWIFT_KnowledgebaseCategory::TYPE_PRIVATE,
                                                                                                            SWIFT_KnowledgebaseCategory::TYPE_INHERIT
                                                                                                       ), array(0), $this->GetStaff()->GetProperty('staffgroupid'));

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT kbcategoryid, title FROM " . TABLE_PREFIX . "kbcategories
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
                AND kbcategoryid IN (" . BuildIN($_knowledgebaseCategoryIDList) . ")
            ORDER BY dateline DESC, title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array(htmlspecialchars($this->Database->Record['title']), SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Index/' . $this->Database->Record['kbcategoryid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Knowledgebase Articles
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchArticles()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_kbcanviewarticles') == '0') {
            return array();
        }

        // First get the list of probable categories
        SWIFT_Loader::LoadModel('Category:KnowledgebaseCategory', APP_KNOWLEDGEBASE);
        
        /*
         * Bug Fix : Saloni Dhall
         *
         * SWIFT-2573 : Unified search only searches base knowledgebase categories, not child categories
         *
         * Comments : Further in MySQL statement, categoryTypeList falls back in case of Inherit category
         */
        $_knowledgebaseCategoryIDList = SWIFT_KnowledgebaseCategory::RetrieveSubCategoryIDListExtended(array(
                                                                                                            SWIFT_KnowledgebaseCategory::TYPE_GLOBAL,
                                                                                                            SWIFT_KnowledgebaseCategory::TYPE_PRIVATE,
                                                                                                            SWIFT_KnowledgebaseCategory::TYPE_INHERIT
                                                                                                       ), array(0), $this->GetStaff()->GetProperty('staffgroupid'));

        // fix to include the root category
        $_knowledgebaseCategoryIDList[] = 0;

        $_knowledgebaseArticleIDList = array();
        SWIFT_Loader::LoadModel('Article:KnowledgebaseArticleLink', APP_KNOWLEDGEBASE);
        $this->Database->Query("SELECT kbarticleid FROM " . TABLE_PREFIX . "kbarticlelinks
            WHERE linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "' AND linktypeid IN (" . BuildIN($_knowledgebaseCategoryIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_knowledgebaseArticleIDList[] = $this->Database->Record['kbarticleid'];
        }

        $_searchResults = $_finalKnowledgebaseArticleIDList = array();

        $this->Database->QueryLimit("SELECT kbarticles.kbarticleid AS kbarticleid FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
            LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticledata.kbarticleid = kbarticles.kbarticleid)
            WHERE ((" . BuildSQLSearch('kbarticles.subject', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('kbarticledata.contentstext', $this->GetQuery(), false, false) . "))
                AND kbarticles.kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")
            ORDER BY kbarticles.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_finalKnowledgebaseArticleIDList[] = $this->Database->Record['kbarticleid'];
        }

        $_knowledgebaseCategoryContainer = array();
        $this->Database->Query("SELECT kbarticlelinks.kbarticleid AS kbarticleid, kbcategories.title AS categorytitle FROM " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks
            LEFT JOIN " . TABLE_PREFIX . "kbcategories AS kbcategories ON (kbarticlelinks.linktypeid = kbcategories.kbcategoryid)
            WHERE kbarticlelinks.kbarticleid IN (" . BuildIN($_finalKnowledgebaseArticleIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (trim($this->Database->Record['categorytitle']) == '') {
                continue;
            }

            if (!isset($_knowledgebaseCategoryContainer[$this->Database->Record['kbarticleid']])) {
                $_knowledgebaseCategoryContainer[$this->Database->Record['kbarticleid']] = array();
            }

            $_knowledgebaseCategoryContainer[$this->Database->Record['kbarticleid']][] = $this->Database->Record['categorytitle'];
        }


        $this->Database->QueryLimit("SELECT kbarticles.kbarticleid AS kbarticleid, kbarticles.subject AS subject, kbarticles.dateline AS dateline, kbarticledata.contentstext AS kbarticlecontents FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
            LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticledata.kbarticleid = kbarticles.kbarticleid)
            WHERE ((" . BuildSQLSearch('kbarticles.subject', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('kbarticledata.contentstext', $this->GetQuery(), false, false) . "))
                AND kbarticles.kbarticleid IN (" . BuildIN($_finalKnowledgebaseArticleIDList) . ")
            ORDER BY kbarticles.dateline DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_extendedInfo = '';
            if (isset($_knowledgebaseCategoryContainer[$this->Database->Record['kbarticleid']]) && count($_knowledgebaseCategoryContainer[$this->Database->Record['kbarticleid']])) {
                $_extendedInfo .= implode(', ', $_knowledgebaseCategoryContainer[$this->Database->Record['kbarticleid']]) . '<br />';
            }

            if (self::HasQuery($this->Database->Record['kbarticlecontents'], $this->GetQuery())) {
                $_highlightResult = implode(' ... ', $this->StringHighlighter->GetHighlightedRange($this->Database->Record['kbarticlecontents'], $this->GetQuery(), 20));
                if (trim($_highlightResult) != '') {
                    $_extendedInfo .= '... ' . $_highlightResult . ' ...<br />';
                }
            } else {
                $_extendedInfo .= substr($this->Database->Record['kbarticlecontents'], 0, 255);
            }

            $_searchResults[] = array(htmlspecialchars($this->Database->Record['subject']), SWIFT::Get('basename') . '/Knowledgebase/ViewKnowledgebase/Article/' . $this->Database->Record['kbarticleid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']), $_extendedInfo);
        }

        return $_searchResults;
    }
}
