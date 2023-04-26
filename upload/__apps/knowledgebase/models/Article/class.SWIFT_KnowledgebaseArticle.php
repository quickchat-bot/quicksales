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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Knowledgebase\Models\Article;

use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreFile;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use SWIFT_StringHTMLToText;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Base\Models\User\SWIFT_UserGroupAssign;

/**
 * The Knowledgebase Article Model
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseArticle extends SWIFT_Model
{
    const TABLE_NAME        =    'kbarticles';
    const PRIMARY_KEY        =    'kbarticleid';

    const TABLE_STRUCTURE    =    "kbarticleid I PRIMARY AUTO NOTNULL,
                                creator I2 DEFAULT '0' NOTNULL,
                                creatorid I DEFAULT '0' NOTNULL,
                                author C(255) DEFAULT '' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,

                                subject C(255) DEFAULT '' NOTNULL,

                                isedited I2 DEFAULT '0' NOTNULL,
                                editeddateline I DEFAULT '0' NOTNULL,
                                editedstaffid I DEFAULT '0' NOTNULL,

                                views I DEFAULT '0' NOTNULL,

                                isfeatured I2 DEFAULT '0' NOTNULL,
                                allowcomments I2 DEFAULT '0' NOTNULL,
                                totalcomments I DEFAULT '0' NOTNULL,
                                hasattachments I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,

                                articlestatus C(20) DEFAULT '' NOTNULL,
                                articlerating F DEFAULT '0.0' NOTNULL,
                                ratinghits I DEFAULT '0' NOTNULL,
                                ratingcount I DEFAULT '0' NOTNULL,
                                seosubject VARCHAR(255)";

    const INDEX_1            =    'creator, creatorid';
    const INDEX_2            =    'kbarticleid, isfeatured';
    const INDEX_3            =    'articlestatus';
    const INDEX_4            =    'subject, kbarticleid';
    const INDEX_5            =    'seosubject';


    protected $_dataStore = array();

    static protected $_filterCache = array();

    // Core Constants
    const CREATOR_USER = 1;
    const CREATOR_STAFF = 2;

    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 2;
    const STATUS_PENDINGAPPROVAL = 3;

    const FILTER_POPULAR = 1;
    const FILTER_RECENT = 2;

    const FEATURED_NOFILTER = 1;
    const FEATURED_ONLY = 2;
    const FEATURED_NO = 3;

    const SORT_ASC  = 'ASC';
    const SORT_DESC = 'DESC';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Article_Exception If the Record could not be loaded
     * @throws SWIFT_Exception
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Article_Exception('Failed to load the Knowledgebase Article Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Article_Exception
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * @author Utsav Handa <utsav.handa@opencart.com.vn>
     *
     * @param string $_sortField
     *
     * @return bool
     */
    public static function IsValidSortField($_sortField)
    {
        return in_array(strtolower($_sortField), array('author', 'subject', 'dateline', 'articlerating', 'articlestatus', 'views', 'kbarticleid'));
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Article_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Article_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticles', $this->GetUpdatePool(), 'UPDATE', "kbarticleid = '" . ($this->GetKnowledgebaseArticleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Knowledgebase Article ID
     *
     * @author Varun Shoor
     * @return mixed "kbarticleid" on Success, "false" otherwise
     * @throws SWIFT_Article_Exception If the Class is not Loaded
     */
    public function GetKnowledgebaseArticleID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Article_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['kbarticleid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Article_Exception
     * @throws SWIFT_Exception
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        $isClassLoaded = $_SWIFT_DataObject->GetIsClassLoaded();
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $isClassLoaded)
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT kbarticles.*, kbarticledata.contents, kbarticledata.contentstext FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
                WHERE kbarticles.kbarticleid = '" . ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['kbarticleid']) && !empty($_dataStore['kbarticleid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $isClassLoaded) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['kbarticleid']) || empty($this->_dataStore['kbarticleid']))
            {
                throw new SWIFT_Article_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Article_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Article_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Article_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Article_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Article_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Article_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid creator
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreator($_creatorType)
    {
        return $_creatorType == self::CREATOR_STAFF || $_creatorType == self::CREATOR_USER;
    }

    /**
     * @author Saloni Dhall <saloni.dhall@opencart.com.vn>
     *
     * @param mixed $_sortOrder
     *
     * @return bool
     */
    public static function IsValidSortOrder($_sortOrder)
    {
        return in_array(strtoupper($_sortOrder), array(self::SORT_ASC, self::SORT_DESC));
    }

    /**
     * Check to see if its a valid status
     *
     * @author Varun Shoor
     * @param mixed $_articleStatus The Article Status
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidStatus($_articleStatus)
    {
        return $_articleStatus == self::STATUS_DRAFT || $_articleStatus == self::STATUS_PENDINGAPPROVAL || $_articleStatus == self::STATUS_PUBLISHED;
    }

    /**
     * Cleans a string to be used as a SEO subject.
     * The returned string will be alphanumeric or underscore
     *
     * @param string   $seosubject
     * @param string   $prefix         Used when the seosubject is a reserved keyword by the database platform
     * @param int|bool $maxLength      Maximum number of characters used; 0 to disable
     * @param string   $spaceCharacter Character to replace spaces with
     *
     * @return string
     *
     */
    public static function cleanSeoSubject($seosubject, $prefix = '', $maxLength = false, $spaceCharacter = '_')
    {
        // Transliterate to latin characters
        $seosubject = transliterate(trim($seosubject));
        // Some labels are quite long if a question so cut this short
        $seosubject = strtolower(alphanum($seosubject, false, $spaceCharacter));
        // Ensure we have something
        if (empty($seosubject)) {
            $seosubject = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
        }
        // Trim if applicable
        if ($maxLength) {
            $seosubject = substr($seosubject, 0, $maxLength);
        }
        if (substr($seosubject, -1) == '_') {
            $seosubject = substr($seosubject, 0, -1);
        }

        return $seosubject;
    }

    /**
     * @param string $seosubject
     *
     * @return mixed
     */
    public static function checkPageUniqueSeoSubject($seosubject)
    {
        $_SWIFT = SWIFT::GetInstance();
        $_totalItemContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "kbarticles WHERE seosubject = '" . $_SWIFT->Database->Escape($seosubject) . "'");
        if (isset($_totalItemContainer['totalitems']) && !empty($_totalItemContainer['totalitems']))
        {
            return intval($_totalItemContainer['totalitems']);
        }

        return 0;
    }

    /**
     * Create a new Knowledgebase Article
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @param int $_creatorID The Creator ID
     * @param string $_author The Article Author Name
     * @param string $_email The Article Author Email Address
     * @param mixed $_articleStatus The Article Status
     * @param string $_subject The Article Subject
     * @param string $_seoSubject The Article SEO Subject
     * @param string $_articleContents The Article Contents
     * @param bool $_isFeatured (OPTIONAL) Whether the article is featured article
     * @param bool $_allowComments (OPTIONAL) Whether to allow for comments in the article
     * @param array $_knowledgebaseCategoryIDList (OPTIONAL) The Linked Knowledgebase Category ID List
     * @return int The Knowledgebase Article ID
     * @throws SWIFT_Article_Exception If Invalid Data is Provided or If the Object could not be created
     * @throws SWIFT_Exception
     */
    public static function Create($_creatorType, $_creatorID, $_author, $_email, $_articleStatus, $_subject, $_seoSubject, $_articleContents, $_isFeatured = false, $_allowComments = true,
        $_knowledgebaseCategoryIDList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_subject) || !self::IsValidCreator($_creatorType) || !self::IsValidStatus($_articleStatus))
        {
            throw new SWIFT_Article_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_seoSubject)) {
            $_seoSubject = $_subject;
        }
        $_seoSubject = self::cleanSeoSubject($_seoSubject, '', false, '-');
        //make sure seosubject is not already taken
        $testSeoSubject = $_seoSubject;
        $count     = static::checkPageUniqueSeoSubject($testSeoSubject);
        $_seoSubjectTag  = 1;
        while ($count) {
            $testSeoSubject = $_seoSubject.$_seoSubjectTag;
            $count     = static::checkPageUniqueSeoSubject($testSeoSubject);
            ++$_seoSubjectTag;
        }
        if ($testSeoSubject != $_seoSubject) {
            $_seoSubject = $testSeoSubject;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'kbarticles', array('creator' => ($_creatorType), 'creatorid' => ($_creatorID), 'articlestatus' => ($_articleStatus),
            'subject' => $_subject, 'seosubject' => $_seoSubject, 'isfeatured' => (int)($_isFeatured), 'allowcomments' => (int)($_allowComments), 'dateline' => DATENOW), 'INSERT');
        $_knowledgebaseArticleID = $_SWIFT->Database->Insert_ID();

        if (!$_knowledgebaseArticleID)
        {
            throw new SWIFT_Article_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();

        $_articleContentsText = $_SWIFT_StringHTMLToTextObject->Convert($_articleContents);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'kbarticledata', array('contents' => $_articleContents, 'contentstext' => $_articleContentsText,
            'kbarticleid' => (int)($_knowledgebaseArticleID)), 'INSERT');
        $_knowledgebaseArticleDataID = $_SWIFT->Database->Insert_ID();

        if (!$_knowledgebaseArticleDataID)
        {
            throw new SWIFT_Article_Exception(SWIFT_CREATEFAILED);
        }

        foreach ($_knowledgebaseCategoryIDList as $_knowledgebaseCategoryID)
        {
            SWIFT_KnowledgebaseArticleLink::Create($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY, $_knowledgebaseCategoryID);
        }

        SWIFT_KnowledgebaseCategory::RebuildCache(true);

        // Search engine indexing
        $eng = new SWIFT_SearchEngine();
        $eng->Insert($_knowledgebaseArticleID, 0, SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE, $_subject . " " . $_articleContentsText);

        return $_knowledgebaseArticleID;
    }

    /**
     * Update the Knowledgebase Article Record
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param string $_subject The Article Subject
     * @param string $_seoSubject The Article SEO Subject
     * @param string $_articleContents The Article Contents
     * @param bool $_isFeatured (OPTIONAL) Whether the article is featured article
     * @param bool $_allowComments (OPTIONAL) Whether to allow for comments in the article
     * @param array $_knowledgebaseCategoryIDList (OPTIONAL) The Linked Knowledgebase Category ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Article_Exception If the Class is not Loaded or If Invalid Data is Provided
     * @throws SWIFT_Exception
     */
    public function Update($_staffID, $_subject, $_seoSubject, $_articleContents, $_isFeatured = false, $_allowComments = true, $_knowledgebaseCategoryIDList = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Article_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Only update SEO subject if it was modified
        try {
            $_oseosubject = $this->GetProperty('seosubject');
        } catch (\Exception $ex) {
            $_oseosubject = '';
        }
        if (!empty($_seoSubject) && strcmp($_seoSubject, $_oseosubject) !== 0) {
            $_seoSubject = self::cleanSeoSubject($_seoSubject, '', false, '-');
            //make sure seosubject is not already taken
            $testSeoSubject = $_seoSubject;
            $count = static::checkPageUniqueSeoSubject($testSeoSubject);
            $_seoSubjectTag = 1;
            while ($count) {
                $testSeoSubject = $_seoSubject . $_seoSubjectTag;
                $count = static::checkPageUniqueSeoSubject($testSeoSubject);
                ++$_seoSubjectTag;
            }
            if ($testSeoSubject != $_seoSubject) {
                $_seoSubject = $testSeoSubject;
            }
            $this->UpdatePool('seosubject', $_seoSubject);
        }

        $this->UpdatePool('isedited', '1');
        $this->UpdatePool('editeddateline', DATENOW);
        $this->UpdatePool('editedstaffid', ($_staffID));
        $this->UpdatePool('subject', $_subject);
        $this->UpdatePool('isfeatured', ($_isFeatured));
        $this->UpdatePool('allowcomments', ($_allowComments));
        $this->ProcessUpdatePool();

        $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();

        $_articleContentsText = $_SWIFT_StringHTMLToTextObject->Convert($_articleContents);

        $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticledata', array('contents' => $_articleContents, 'contentstext' => $_articleContentsText),
                'UPDATE', "kbarticleid = '" . ($this->GetKnowledgebaseArticleID()) . "'");

        SWIFT_KnowledgebaseArticleLink::DeleteOnKnowledgebaseArticle(array($this->GetKnowledgebaseArticleID()));
        foreach ($_knowledgebaseCategoryIDList as $_knowledgebaseCategoryID)
        {
            SWIFT_KnowledgebaseArticleLink::Create($this->GetKnowledgebaseArticleID(), SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY, $_knowledgebaseCategoryID);
        }

        SWIFT_KnowledgebaseCategory::RebuildCache(true);

        // Search engine indexing
        $eng = new SWIFT_SearchEngine();
        $eng->Update($this->GetKnowledgebaseArticleID(), 0, SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE, $_subject . " " . $_articleContentsText);

        return true;
    }

    /**
     * Delete the Knowledgebase Article record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Article_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Article_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetKnowledgebaseArticleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Increment the Views for the Knowledgebase Article
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IncrementViews()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticles', array('views' => ($this->GetProperty('views')+1)), 'UPDATE', "kbarticleid = '" . ($this->GetKnowledgebaseArticleID()) . "'");

        return true;
    }

    /**
     * Delete a list of Knowledgebase Articles
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseArticleIDList The KB Article ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_knowledgebaseArticleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseArticleIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbarticles WHERE kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")");

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbarticledata WHERE kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")");

        SWIFT_Comment::DeleteOnType(SWIFT_Comment::TYPE_KNOWLEDGEBASE, $_knowledgebaseArticleIDList);

        SWIFT_KnowledgebaseArticleLink::DeleteOnKnowledgebaseArticle($_knowledgebaseArticleIDList);
        SWIFT_KnowledgebaseArticleLink::DeleteOnLinkType(SWIFT_KnowledgebaseArticleLink::LINKTYPE_ARTICLE, $_knowledgebaseArticleIDList);

        SWIFT_KnowledgebaseArticleSubscriber::DeleteOnKnowledgebaseArticle($_knowledgebaseArticleIDList);

        SWIFT_Attachment::DeleteOnLinkType(SWIFT_Attachment::LINKTYPE_KBARTICLE, $_knowledgebaseArticleIDList);

        SWIFT_KnowledgebaseCategory::RebuildCache(true);

        // Search engine index
        $eng = new SWIFT_SearchEngine();
        $eng->DeleteList($_knowledgebaseArticleIDList, SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE);

        return true;
    }

    /**
     * Update the Knowledgebase Article Status
     *
     * @author Varun Shoor
     * @param mixed $_articleStatus The New Article Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateStatus($_articleStatus)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('articlestatus', $_articleStatus);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve the Knowledgebase Articles based on the category id
     *
     * @author Varun Shoor
     * @author Saloni Dhall <saloni.dhall@opencart.com.vn>
     *
     * @param array $_knowledgebaseCategoryIDList
     * @param int $_recordLimit (OPTIONAL)
     * @param int $_rowOffset (OPTIONAL)
     * @param string $_sortField (OPTIONAL)
     * @param string $_sortOrder (OPTIONAL)
     *
     * @return array
     * @throws SWIFT_Exception
     */
    public static function Retrieve($_knowledgebaseCategoryIDList, $_recordLimit = -1, $_rowOffset = 0, $_sortField = null, $_sortOrder = self::SORT_ASC)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseCategoryIDList)) {
            return array();
        }

        $_knowledgebaseArticleIDList = array();

        /**
         * BUG FIX - Amarjeet Kaur
         *
         * SWIFT-3324: Pagination Support for API requests
         *
         * Comments: None
         */
        $_SWIFT->Database->Query("SELECT kbarticleid FROM " . TABLE_PREFIX . SWIFT_KnowledgebaseArticleLink::TABLE_NAME . "
                                 WHERE linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "'
                                       AND linktypeid IN (" . BuildIN($_knowledgebaseCategoryIDList, true) . ")");

        while ($_SWIFT->Database->NextRecord()) {
            $_knowledgebaseArticleIDList[] = $_SWIFT->Database->Record['kbarticleid'];
        }

        if (!count($_knowledgebaseArticleIDList)) {
            return array();
        }
        /**
         * Bug Fix : Saloni Dhall
         *
         * SWIFT-2712 : 'Override Article Display Order Field' option is not working under Knowledgebase setting
         */
        if ($_SWIFT->Settings->Get('kb_arorder') != '1')
        {
            $_sortOrder = self::SORT_DESC;
        }

        $_knowledgebaseArticleContainer = self::ParseRetrieveArticles($_knowledgebaseArticleIDList, self::FEATURED_NOFILTER, false, $_recordLimit, $_rowOffset, self::GetDefaultSortField(), $_sortOrder);

        return $_knowledgebaseArticleContainer;
    }

    /**
     * Return the default sort field
     *
     * @author Varun Shoor
     * @return string The Sort Field
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetDefaultSortField()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sortField = $_SWIFT->Settings->Get('kb_ardisplayorder');

        switch ($_sortField)
        {
            case '1':
                return 'subject';
            break;

            case '2':
                return 'subject';
            break;

            case '3':
                return 'kbarticleid';
            break;

            case '4':
                return 'editeddateline';
            break;

            case '5':
                return 'views';
            break;

            case '6':
                return 'articlerating';
            break;

            default:
            break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Retrieve the Popular Knowledgebase Articles
     *
     * @author Varun Shoor
     * @param mixed $_filterType The Filter Type
     * @param array $_knowledgebaseCategoryIDList The Category ID List
     * @param int $_staffGroupID (OPTIONAL) The Staff Group ID
     * @param int $_userGroupID (OPTIONAL) The User Group ID
     * @return array The Knowledgebase ARticle Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveFilter($_filterType = self::FILTER_POPULAR, $_knowledgebaseCategoryIDList, $_staffGroupID = 0, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseCategoryIDList))
        {
            return array();
        }

        $_knowledgebaseArticleIDList = array();
        $_knowledgebaseCategoryCache = $_SWIFT->Cache->Get('kbcategorycache');

        // Always include parent category id
        $_knowledgebaseCategoryIDList[] = 0;

        $_sortField = "views";
        if ($_filterType == self::FILTER_RECENT)
        {
            $_sortField = "dateline";
        }

        $_SWIFT->Database->Query("SELECT kbarticles.kbarticleid FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
            LEFT JOIN " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
            WHERE kbarticlelinks.linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "'
                AND kbarticlelinks.linktypeid IN (" . BuildIN($_knowledgebaseCategoryIDList) . ")
            ORDER BY kbarticles." . $_sortField . " DESC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseArticleIDList[] = $_SWIFT->Database->Record['kbarticleid'];
        }

        if (!count($_knowledgebaseArticleIDList))
        {
            return array();
        }

        $_articleLimit = ($_SWIFT->Settings->Get('kb_maxpopulararticles'));
        if ($_filterType == self::FILTER_RECENT)
        {
            $_articleLimit = ($_SWIFT->Settings->Get('kb_maxlatestarticles'));
        }

        return self::ParseRetrieveArticles($_knowledgebaseArticleIDList, self::FEATURED_NOFILTER, true, $_articleLimit, 0, $_sortField, self::SORT_DESC);
    }

    /**
     * Parse the retrieve articles and return the relevant data
     *
     * @author   Varun Shoor
     *
     * @param array $_knowledgebaseArticleIDList The Knowledgebase Article ID List
     * @param mixed $_featuredFilter (OPTIONAL) The Feature Filter
     * @param bool $_isSimple (OPTIONAL) Whether its a simple query
     * @param bool|int $_articleLimit (OPTIONAL) The Article Limit
     * @param int $_rowOffset (OPTIONAL)
     * @param string $_sortField (OPTIONAL)
     * @param string $_sortOrder (OPTIONAL)
     *
     * @return array The Parsed Knowledgebase Article Container
     * @throws SWIFT_Exception
     */
    protected static function ParseRetrieveArticles($_knowledgebaseArticleIDList, $_featuredFilter = self::FEATURED_NOFILTER, $_isSimple = false, $_articleLimit = false, $_rowOffset = 0,
                                                    $_sortField = null, $_sortOrder = self::SORT_ASC)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseArticleIDList))
        {
            return array();
        }

        $_knowledgebaseArticleContainer = array();

        $_sqlExtended = '';
        $_sortField   = Clean($_sortField);

        if ($_featuredFilter == self::FEATURED_NO)
        {
            $_sqlExtended = " AND kbarticles.isfeatured = '0'";
        } else if ($_featuredFilter == self::FEATURED_ONLY) {
            $_sqlExtended = " AND kbarticles.isfeatured = '1'";
        }

        /**
         * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-4279 Pinned knowledgebase articles are not listed at the top
         */
        if (!empty($_sortField) && $_sortField == 'editeddateline')
        {
            $_sqlExtended .= ' ORDER BY kbarticles.isfeatured DESC, kbarticles.isedited DESC, kbarticles.' . $_SWIFT->Database->Escape($_sortField) . ' ' . $_sortOrder;
        } else if (!empty($_sortField) && self::IsValidSortField($_sortField)) {
            $_sqlExtended .= ' ORDER BY kbarticles.isfeatured DESC, kbarticles.' . $_SWIFT->Database->Escape($_sortField) . ' ' . IIF(self::IsValidSortOrder($_sortOrder), $_sortOrder, self::SORT_ASC);
        } else if (empty($_sortField)) {
            $_sqlExtended .= ' ORDER BY kbarticles.isfeatured DESC, kbarticles.editeddateline DESC ';
        }

        $_finalSQLQuery = '';
        if ($_isSimple)
        {
            $_finalSQLQuery = "SELECT kbarticles.* FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                WHERE kbarticles.kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")" . $_sqlExtended;
        } else {
            $_finalSQLQuery = "SELECT kbarticles.*, kbarticledata.contents, kbarticledata.contentstext FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
                LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
                WHERE kbarticles.kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")" .
                $_sqlExtended;
        }

        if (empty($_articleLimit))
        {
            $_SWIFT->Database->Query($_finalSQLQuery);
        } else {
            $_SWIFT->Database->QueryLimit($_finalSQLQuery, $_articleLimit, $_rowOffset);
        }

        while ($_SWIFT->Database->NextRecord())
        {
            if ($_SWIFT->Database->Record['articlestatus'] != SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED)
            {
                continue;
            }

            $_knowledgebaseArticleContainer[$_SWIFT->Database->Record['kbarticleid']] = $_SWIFT->Database->Record;
            $_knowledgebaseArticleContainer[$_SWIFT->Database->Record['kbarticleid']]['subject'] = htmlspecialchars(StripName($_SWIFT->Database->Record['subject'], 65));

            if (isset($_SWIFT->Database->Record['contents'])) {
                $_knowledgebaseArticleContainer[$_SWIFT->Database->Record['kbarticleid']]['contents'] = StripScriptTags($_knowledgebaseArticleContainer[$_SWIFT->Database->Record['kbarticleid']]['contents']);
            }

            if (isset($_SWIFT->Database->Record['contentstext']))
            {
                $_knowledgebaseArticleContainer[$_SWIFT->Database->Record['kbarticleid']]['contentstext'] = StripName($_SWIFT->Database->Record['contentstext'], $_SWIFT->Settings->Get('kb_climit'));
            }
        }

        return $_knowledgebaseArticleContainer;
    }

    /**
     * Retrieve the processed data store for this knowledgebase article
     *
     * @author Varun Shoor
     * @return array The processed data store
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_articleContainer = $this->GetDataStore();

        $_staffCache = (array) $this->Cache->Get('staffcache');

        $_emailHash = md5($_articleContainer['email']);
        $_articleAuthor = $_articleContainer['author'];

        if ($_articleContainer['creator'] == self::CREATOR_STAFF && isset($_staffCache[$_articleContainer['creatorid']]))
        {
            $_emailHash = md5($_staffCache[$_articleContainer['creatorid']]['email']);

            $_articleAuthor = $_staffCache[$_articleContainer['creatorid']]['fullname'];
        } else if ($_articleContainer['creator'] == self::CREATOR_STAFF && empty($_articleContainer['email'])) {
            foreach ($_staffCache as $_staffContainer)
            {
                if (trim(mb_strtolower($_staffContainer['fullname'])) == trim(mb_strtolower($_articleContainer['author'])))
                {
                    $_emailHash = md5($_staffContainer['email']);
                    break;
                }
            }
        }

        $_articleContainer['emailhash'] = $_emailHash;
        $_articleContainer['subject'] = htmlspecialchars($_articleContainer['subject']);
        $_articleContainer['seosubject'] = htmlspecialchars($_articleContainer['seosubject']);
        $_articleContainer['contents'] = StripScriptTags($_articleContainer['contents']);
        $_articleContainer['author'] = htmlspecialchars($_articleAuthor);
        $_articleContainer['date'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_articleContainer['dateline']);

        return $_articleContainer;
    }

    /**
     * Mark the article as helpful
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsHelpful()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newHits = $this->GetProperty('ratinghits')+1;
        $_ratingCount = $this->GetProperty('ratingcount');
        if ($_ratingCount >= 0)
        {
            $_ratingCount += 5;
        }

        $this->UpdatePool('ratinghits', $_newHits);
        $this->UpdatePool('ratingcount', $_ratingCount);

        $this->CalculateArticleRating();
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Mark the article as not helpful
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsNotHelpful()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newHits = $this->GetProperty('ratinghits')+1;
        $_ratingCount = $this->GetProperty('ratingcount');
        if ($_ratingCount > 0)
        {
            $_ratingCount -= 5;
        }

        $this->UpdatePool('ratinghits', $_newHits);
        $this->UpdatePool('ratingcount', $_ratingCount);

        $this->CalculateArticleRating();
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Calculate the Article Rating
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CalculateArticleRating()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ratingHits = ($this->GetProperty('ratinghits'));
        $_ratingCount = ($this->GetProperty('ratingcount'));
        $_processedRating = ($_ratingCount/$_ratingHits);

        $_articleRating = '0';

        if ($_processedRating > 0 && $_processedRating <= 0.5)
        {
            $_articleRating = '0.5';
        } else if ($_processedRating > 0.5 && $_processedRating <= 1) {
            $_articleRating = '1';

        } else if ($_processedRating > 1 && $_processedRating <= 1.5) {
            $_articleRating = '1.5';

        } else if ($_processedRating > 1.5 && $_processedRating <= 2) {
            $_articleRating = '2';

        } else if ($_processedRating > 2 && $_processedRating <= 2.5) {
            $_articleRating = '2.5';

        } else if ($_processedRating > 2.5 && $_processedRating <= 3) {
            $_articleRating = '3';

        } else if ($_processedRating > 3 && $_processedRating <= 3.5) {
            $_articleRating = '3.5';

        } else if ($_processedRating > 3.5 && $_processedRating <= 4) {
            $_articleRating = '4';

        } else if ($_processedRating > 4 && $_processedRating <= 4.5) {
            $_articleRating = '4.5';

        } else if ($_processedRating > 4.5) {
            $_articleRating = '5';

        }

        $this->UpdatePool('articlerating', $_articleRating);

        return true;
    }

    /**
     * Processes the POST attachment field (kbattachments) and adds the attachments to the ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Article_Exception
     * @throws SWIFT_Exception
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     * @throws \Base\Library\Attachment\SWIFT_Attachment_Exception
     */
    public function ProcessPostAttachments() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * ---------------------------------------------
         * REPLICA EXISTS IN TROUBLESHOOTER STEP CLASS
         * ---------------------------------------------
         */

        $_finalFieldName = 'kbattachments';

        $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_KBARTICLE, $this->GetKnowledgebaseArticleID());
        $_existingAttachmentIDList = array();
        if (isset($_POST['_existingAttachmentIDList']) && _is_array($_POST['_existingAttachmentIDList']))
        {
            $_existingAttachmentIDList = $_POST['_existingAttachmentIDList'];
        }

        $_deleteAttachmentIDList = $_attachmentFileList = $_attachmentFileMap = array();
        if (_is_array($_attachmentContainer))
        {
            foreach ($_attachmentContainer as $_attachment)
            {
                $_attachmentFileList[] = $_attachment['filename'];
                $_attachmentFileMap[$_attachment['filename']] = $_attachment['attachmentid'];

                if (!in_array($_attachment['attachmentid'], $_existingAttachmentIDList))
                {
                    $_deleteAttachmentIDList[] = $_attachment['attachmentid'];
                }
            }

            SWIFT_Attachment::DeleteList($_deleteAttachmentIDList);
        }

        if (!isset($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]['name'])) {
            return false;
        }

        // Create the attachments
        $_attachmentCount = 0;
        foreach ($_FILES[$_finalFieldName]['name'] as $_fileIndex => $_fileName) {
            if (empty($_fileName) || empty($_FILES[$_finalFieldName]['type'][$_fileIndex]) || empty($_FILES[$_finalFieldName]['size'][$_fileIndex]) ||
                    !is_uploaded_file($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex]))
            {
                continue;
            }

            // If a file with same filename already exists then delete it
            if (in_array($_fileName, $_attachmentFileList) && isset($_attachmentFileMap[$_fileName]))
            {
                SWIFT_Attachment::DeleteList(array($_attachmentFileMap[$_fileName]));
            }

            $_SWIFT_AttachmentStoreObject = new SWIFT_AttachmentStoreFile($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex],
                    $_FILES[$_finalFieldName]['type'][$_fileIndex], $_fileName);

            $_SWIFT_AttachmentObject = SWIFT_Attachment::Create(SWIFT_Attachment::LINKTYPE_KBARTICLE, $this->GetKnowledgebaseArticleID(), $_SWIFT_AttachmentStoreObject);

            $_attachmentCount++;
        }

        if ($_attachmentCount > 0) {
            $this->UpdatePool('hasattachments', '1');
            $this->ProcessUpdatePool();
        }

        return true;
    }

    /**
     * Get the Article Status Label
     *
     * @author Varun Shoor
     * @param mixed $_articleStatus The Article Status
     * @return string The Article Status
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetStatusLabel($_articleStatus)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidStatus($_articleStatus))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_articleStatus)
        {
            case self::STATUS_DRAFT:
                return $_SWIFT->Language->Get('astatus_draft');
                break;

            case self::STATUS_PUBLISHED:
                return $_SWIFT->Language->Get('astatus_published');
                break;

            case self::STATUS_PENDINGAPPROVAL:
                return $_SWIFT->Language->Get('astatus_pendingapproval');
                break;
        // @codeCoverageIgnoreStart
        // This code will never be executer
            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Retrieve the Knowledgebase Articles based on IRS Search
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param int $_userGroupID
     * @return array The Knowledgebase ARticle Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveIRS($_searchQuery, $_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseArticleIDList = $_searchKnowledgebaseArticleIDList = array();

        $_wordContainer = self::Tokenize($_searchQuery);
        $_finalSearchQuery = implode(' ', $_wordContainer);

        // We first search all knowledgebase articles
        $_SWIFT->Database->QueryLimit("SELECT kbarticles.kbarticleid FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
            LEFT JOIN " . TABLE_PREFIX . "kbarticledata AS kbarticledata ON (kbarticles.kbarticleid = kbarticledata.kbarticleid)
            WHERE (" . BuildSQLSearch('kbarticles.subject', $_finalSearchQuery) . ") OR (" . BuildSQLSearch('kbarticledata.contentstext', $_finalSearchQuery) . ")", 100);
        while ($_SWIFT->Database->NextRecord())
        {
            $_searchKnowledgebaseArticleIDList[] = $_SWIFT->Database->Record['kbarticleid'];
        }

        $_sortField = 'views';

        $_filterKnowledgebaseCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup(SWIFT::Get('usergroupid'), SWIFT_UserGroupAssign::TYPE_KBCATEGORY);

        $_finalKnowledgebaseCategoryIDList = $_filterKnowledgebaseCategoryIDList;

        // Get all categories except for inherited ones
        $_SWIFT->Database->Query("SELECT kbcategoryid FROM " . TABLE_PREFIX . "kbcategories
            WHERE uservisibilitycustom = '0' AND (categorytype != '" . SWIFT_KnowledgebaseCategory::TYPE_INHERIT . "' AND categorytype != '" . SWIFT_KnowledgebaseCategory::TYPE_PRIVATE . "')");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['kbcategoryid'], $_finalKnowledgebaseCategoryIDList)) {
                $_finalKnowledgebaseCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
            }
        }

        $_filterFetchKnowledgebaseCategoryIDList = SWIFT_KnowledgebaseCategory::RetrieveSubCategoryIDListExtended(array(SWIFT_KnowledgebaseCategory::TYPE_INHERIT), $_finalKnowledgebaseCategoryIDList, 0, SWIFT::Get('usergroupid'));
        $_finalKnowledgebaseCategoryIDList = array_merge($_filterFetchKnowledgebaseCategoryIDList, $_finalKnowledgebaseCategoryIDList);

        if (!in_array('0', $_finalKnowledgebaseCategoryIDList)) {
            $_finalKnowledgebaseCategoryIDList[] = '0';
        }

        $_SWIFT->Database->Query("SELECT kbarticles.kbarticleid FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
            LEFT JOIN " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
            WHERE kbarticlelinks.linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "'
                AND kbarticlelinks.linktypeid IN (" . BuildIN($_finalKnowledgebaseCategoryIDList) . ")
                AND kbarticlelinks.kbarticleid IN (" . BuildIN($_searchKnowledgebaseArticleIDList) . ")
            ORDER BY kbarticles." . $_sortField . " DESC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseArticleIDList[] = $_SWIFT->Database->Record['kbarticleid'];
        }

        if (!count($_knowledgebaseArticleIDList))
        {
            return array();
        }

        $_finalKnowledgebaseArticleContainer = array();
        $_knowledgebaseArticleContainer = self::ParseRetrieveArticles($_knowledgebaseArticleIDList, self::FEATURED_NOFILTER, false, $_SWIFT->Settings->Get('g_maxsearchresults'), 0, $_sortField, self::SORT_DESC);

        $_index = 0;
        foreach ($_knowledgebaseArticleContainer as $_knowledgebaseArticleID => $_knowledgebaseArticle)
        {
            $_contentsText = $_knowledgebaseArticle['subject'] . ' ' . $_knowledgebaseArticle['contentstext'];

            $_rank = self::GetWordCount($_wordContainer, $_contentsText);
            $_arrayIndex = $_rank . '.' . $_index;

            $_finalKnowledgebaseArticleContainer[$_arrayIndex]  = $_knowledgebaseArticle;

            $_index++;
        }

        krsort($_finalKnowledgebaseArticleContainer);

        return $_finalKnowledgebaseArticleContainer;
    }

    /**
     * Retrieve the Knowledgebase Articles based on FullText Search
     *
     * @author Mahesh Salaria
     * @param string $_searchQuery The Search Query
     * @param int $_userGroupID
     * @return array The Knowledgebase ARticle Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
        public static function RetrieveFullText($_searchQuery, $_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseArticleIDList = $_searchKnowledgebaseArticleIDList = $_searchKnowledgebaseArticleIDListRel = array();

        // Now search with the search engine
        $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();

        $_searchEngineResult = $_SWIFT_SearchEngineObject->FindByRelevance($_searchQuery, SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE);

                if (_is_array($_searchEngineResult))
        {
            foreach ($_searchEngineResult as $_searchContainer)
            {
                if (!in_array($_searchContainer['objid'], $_searchKnowledgebaseArticleIDList))
                {
                    $_searchKnowledgebaseArticleIDList[] = $_searchContainer['objid'];
                    $_searchKnowledgebaseArticleIDListRel[$_searchContainer['objid']] = $_searchContainer['relevance'];
                }
            }
                }

        $_sortField = 'views';

        $_filterKnowledgebaseCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup(SWIFT::Get('usergroupid'), SWIFT_UserGroupAssign::TYPE_KBCATEGORY);

        $_finalKnowledgebaseCategoryIDList = $_filterKnowledgebaseCategoryIDList;

        // Get all categories except for inherited ones
        $_SWIFT->Database->Query("SELECT kbcategoryid FROM " . TABLE_PREFIX . "kbcategories
            WHERE uservisibilitycustom = '0' AND (categorytype != '" . SWIFT_KnowledgebaseCategory::TYPE_INHERIT . "' AND categorytype != '" . SWIFT_KnowledgebaseCategory::TYPE_PRIVATE . "')");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['kbcategoryid'], $_finalKnowledgebaseCategoryIDList)) {
                $_finalKnowledgebaseCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
            }
        }

        $_filterFetchKnowledgebaseCategoryIDList = SWIFT_KnowledgebaseCategory::RetrieveSubCategoryIDListExtended(array(SWIFT_KnowledgebaseCategory::TYPE_INHERIT), $_finalKnowledgebaseCategoryIDList, 0, SWIFT::Get('usergroupid'));
        $_finalKnowledgebaseCategoryIDList = array_merge($_filterFetchKnowledgebaseCategoryIDList, $_finalKnowledgebaseCategoryIDList);

        if (!in_array('0', $_finalKnowledgebaseCategoryIDList)) {
            $_finalKnowledgebaseCategoryIDList[] = '0';
        }

        $_SWIFT->Database->Query("SELECT kbarticles.kbarticleid FROM " . TABLE_PREFIX . "kbarticles AS kbarticles
            LEFT JOIN " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
            WHERE kbarticlelinks.linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "'
                AND kbarticlelinks.linktypeid IN (" . BuildIN($_finalKnowledgebaseCategoryIDList) . ")
                AND kbarticlelinks.kbarticleid IN (" . BuildIN($_searchKnowledgebaseArticleIDList) . ")
            ORDER BY kbarticles." . $_sortField . " DESC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseArticleIDList[] = $_SWIFT->Database->Record['kbarticleid'];
        }

                if (!count($_knowledgebaseArticleIDList))
        {
            return array();
        }

        $_finalKnowledgebaseArticleContainer = array();
        $_finalKnowledgebaseArticleContainerIntersect = array();
        $_knowledgebaseArticleContainer = self::ParseRetrieveArticles($_knowledgebaseArticleIDList, self::FEATURED_NOFILTER, false, $_SWIFT->Settings->Get('g_maxsearchresults'), 0, $_sortField, self::SORT_DESC);

        //To-Do checks.
        foreach ($_knowledgebaseArticleContainer as $_knowledgebaseArticleID => $_knowledgebaseArticle)
        {
            $_finalKnowledgebaseArticleContainer[$_knowledgebaseArticleID]  = $_knowledgebaseArticle;
            if(array_key_exists($_knowledgebaseArticleID, $_searchKnowledgebaseArticleIDListRel)){
                $_searchKnowledgebaseArticleIDListRel[$_knowledgebaseArticleID] = $_knowledgebaseArticle;
            }
        }

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-3249: Error at client support center when trying to search a knowledgebase article in PHP 5.4
         *
         * Comments: None
         */
        $_finalKnowledgebaseArticleContainerIntersect = array_intersect_key($_searchKnowledgebaseArticleIDListRel, $_finalKnowledgebaseArticleContainer);

        return $_finalKnowledgebaseArticleContainerIntersect;
    }

    /**
     * Return the count of words in a given array
     *
     * @author Varun Shoor
     * @param array $_wordContainer The Word Container
     * @param string $_contentsText
     * @return int The Word Count
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetWordCount($_wordContainer, $_contentsText)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_wordCount = 0;

        $_tokenContainer = self::Tokenize($_contentsText);

        foreach ($_wordContainer as $_word)
        {
            if (in_array($_word, $_tokenContainer))
            {
                $_wordCount++;
            }
        }

        return $_wordCount;
    }

    /**
     * Splits text into chunks and returns as array
     *
     * @author Varun Shoor
     * @param string $_text The Text to Process
     * @return array The Tokens
     */
    public static function Tokenize($_text) {
        $_tokensContainer = explode(" ", self::ReturnSanitizedText($_text));

        if (count($_tokensContainer) == 0) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            return array();
            // @codeCoverageIgnoreEnd
        }

        $_finalTokensContainer = array();

        foreach ($_tokensContainer as $_token) {
            if (self::ShouldIndexWord($_token)) {
                $_finalTokensContainer[] = strtolower($_token);
            }
        }

        return $_finalTokensContainer;
    }

    /**
     * Returns Boolean value depending upon the various settings configured on whether a word should be indexed or not
     *
     * @author Varun Shoor
     * @param string $_word The Word to Check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ShouldIndexWord($_word) {
        $_matches = array();
        $_wordLength = strlen($_word); // Cache length of word
        $_isNumber = preg_match("/^([0-9]+)$/", $_word); // Cache whether the word is just a number

        // Apply rules
        if (preg_match("/&#([0-9]+);/i", $_word)) {
            return true;
        }

        if ($_wordLength < 4 || $_wordLength > 10) {
            return false;
        } // if word is too short or too long to index

        if (in_array(strtolower($_word), self::GetStopWordContainer())) {
            return false;
        } // if the word is in the index-prohibited wordlist

        // number-only rules
        if ($_isNumber) {
            return false;
        }

        if ((!(preg_match('/^[a-z0-9]+$/i', $_word))) &&
                (!(preg_match('/&#(x[a-f0-9]+|[0-9]+);/i', trim($_word), $_matches)))) {
            return false;
        } // Word has symbol in it, return false

        return true;
    }

    /**
     * Returns a sanitized text for searches.. strips text of all symbols and returns a space delimited text
     *
     * @author Varun Shoor
     * @param string $_originalContents The Original Contents Container
     * @return string The Processed Text
     */
    public static function ReturnSanitizedText($_originalContents) {
        $_stopData = array("#\s+#s", "#(\r\n|\r|\n)#s", "/[^a-zA-Z0-9\-\_\s\x80-\xff\&\#;]/"); // POSIX Regexp clause to strip white spaces, words containing asterisks, new lines and all symbols
        $_replaceSpacePreg = array(" ", " ", ""); // replace above clauses with a space, a space or emptiness, respectively

        $_strippedContents = strip_tags($_originalContents); // Strip HTML Tags
        $_cleanedContents = preg_replace($_stopData, $_replaceSpacePreg, $_strippedContents); // Apply first pair of preg clauses
        $_finalContents = preg_replace('/&#(x[a-f0-9]+|[0-9]+);/i', ' &#$1; ', $_cleanedContents);

        return trim($_finalContents);
    }

    /**
     * Retrieve the Stop Words Container Array
     *
     * @author Varun Shoor
     * @return array The Stop Words Container Array
     */
    public static function GetStopWordContainer()
    {
        $_stopWordsContainer = array (
            '&amp', '&quot', 'a', 'a\'s', 'able', 'about', 'above', 'according', 'accordingly', 'across', 'actually', 'after', 'afterwards',
            'again', 'against', 'ain\'t', 'aint', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'already', 'also', 'although', 'always',
            'am', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere',
            'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'arent', 'around', 'as', 'aside', 'ask', 'asking', 'associated', 'at',
            'available', 'away', 'awfully', 'b', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'behind',
            'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'c\'mon',
            'c\'s', 'came', 'can', 'can\'t', 'cannot', 'cant', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'cmon', 'co', 'com',
            'come', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could',
            'couldn\'t', 'couldnt', 'course', 'cs', 'currently', 'd', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'didnt', 'different',
            'do', 'does', 'doesn\'t', 'doesnt', 'doing', 'don\'t', 'done', 'dont', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight',
            'either', 'else', 'elsewhere', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'every', 'everybody', 'everyone',
            'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'far', 'few', 'fifth', 'first', 'five', 'followed', 'following',
            'follows', 'for', 'former', 'formerly', 'forth', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given',
            'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'hadnt', 'happens', 'hardly', 'has',
            'hasn\'t', 'hasnt', 'have', 'haven\'t', 'havent', 'having', 'he', 'he\'s', 'hello', 'help', 'hence', 'her', 'here', 'here\'s',
            'hereafter', 'hereby', 'herein', 'heres', 'hereupon', 'hers', 'herself', 'hes', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully',
            'how', 'howbeit', 'however', 'i', 'i\'d', 'i\'ll', 'i\'m', 'i\'ve', 'id', 'ie', 'if', 'ignored', 'ill', 'im', 'immediate', 'in',
            'inasmuch', 'inc', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t',
            'isnt', 'ist', 'it', 'it\'d', 'it\'ll', 'it\'s', 'itd', 'itll', 'its', 'itself', 'ive', 'j', 'just', 'k', 'keep', 'keeps', 'kept',
            'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'lets',
            'like', 'liked', 'likely', 'little', 'look', 'looking', 'looks', 'ltd', 'm', 'mainly', 'many', 'may', 'maybe', 'me', 'mean', 'meanwhile',
            'merely', 'might', 'more', 'moreover', 'most', 'mostly', 'much', 'must', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly',
            'necessary', 'need', 'needs', 'neither', 'never', 'nevertheless', 'new', 'next', 'nine', 'no', 'nobody', 'non', 'none', 'noone', 'nor',
            'normally', 'not', 'nothing', 'novel', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once',
            'one', 'ones', 'only', 'onto', 'or', 'originally', 'other', 'others', 'otherwise', 'ought', 'our', 'ours', 'ourselves', 'out', 'outside',
            'over', 'overall', 'own', 'p', 'particular', 'particularly', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'posted',
            'presumably', 'probably', 'provides', 'q', 'que', 'quite', 'quote', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'regarding',
            'regardless', 'regards', 'relatively', 'respectively', 'right', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second',
            'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously',
            'seven', 'several', 'shall', 'she', 'should', 'shouldn\'t', 'shouldnt', 'since', 'six', 'so', 'some', 'somebody', 'somehow', 'someone',
            'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub',
            'such', 'sup', 'sure', 't', 't\'s', 'take', 'taken', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'s',
            'thats', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'there\'s', 'thereafter', 'thereby', 'therefore',
            'therein', 'theres', 'thereupon', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'theyd', 'theyll', 'theyre', 'theyve',
            'think', 'third', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'to',
            'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 'ts', 'twice', 'two', 'u', 'un', 'under',
            'unfortunately', 'unless', 'unlikely', 'until', 'unto', 'up', 'upon', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v',
            'value', 'various', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'wasnt', 'way', 'we', 'we\'d', 'we\'ll',
            'we\'re', 'we\'ve', 'wed', 'welcome', 'well', 'went', 'were', 'weren\'t', 'werent', 'weve', 'what', 'what\'s', 'whatever', 'whats',
            'when', 'whence', 'whenever', 'where', 'where\'s', 'whereafter', 'whereas', 'whereby', 'wherein', 'wheres', 'whereupon', 'wherever',
            'whether', 'which', 'while', 'whither', 'who', 'who\'s', 'whoever', 'whole', 'whom', 'whos', 'whose', 'why', 'will', 'willing', 'wish',
            'with', 'within', 'without', 'won\'t', 'wonder', 'wont', 'would', 'wouldn\'t', 'wouldnt', 'x', 'y', 'yes', 'yet', 'you', 'you\'d',
            'you\'ll', 'you\'re', 'you\'ve', 'youd', 'youll', 'your', 'youre', 'yours', 'yourself', 'yourselves', 'youve', 'z', 'zero',
        );

        return $_stopWordsContainer;
    }
}
