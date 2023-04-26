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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace News\Models\NewsItem;

use News\Library\Subscriber\SWIFT_NewsSubscriberDispatch;
use News\Models\Category\SWIFT_NewsCategory;
use News\Models\Category\SWIFT_NewsCategoryLink;
use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Library\HTML\SWIFT_HTMLPurifier;

/**
 * The News Item Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_NewsItem extends SWIFT_Model
{
    const TABLE_NAME        =    'newsitems';
    const PRIMARY_KEY        =    'newsitemid';

    const TABLE_STRUCTURE    =    "newsitemid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                newstype I2 DEFAULT '0' NOTNULL,
                                newsstatus I2 DEFAULT '0' NOTNULL,
                                author C(255) DEFAULT '' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                emailsubject C(255) DEFAULT '' NOTNULL,
                                description X2,
                                descriptionhash C(50) DEFAULT '' NOTNULL,
                                subjecthash C(50) DEFAULT '' NOTNULL,
                                contentshash C(50) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                expiry I DEFAULT '0' NOTNULL,
                                issynced I2 DEFAULT '0' NOTNULL,
                                syncguidhash C(50) DEFAULT '' NOTNULL,
                                syncdateline I DEFAULT '0' NOTNULL,
                                edited I2 DEFAULT '0' NOTNULL,
                                editedstaffid I DEFAULT '0' NOTNULL,
                                editeddateline I DEFAULT '0' NOTNULL,
                                totalcomments I DEFAULT '0' NOTNULL,
                                uservisibilitycustom I2 DEFAULT '0' NOTNULL,
                                staffvisibilitycustom I2 DEFAULT '0' NOTNULL,
                                allowcomments I2 DEFAULT '0' NOTNULL,
                                start I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'newstype, newsstatus, start, expiry, uservisibilitycustom, newsitemid';
    const INDEX_2            =    'issynced, syncguidhash, syncdateline';
    const INDEX_3            =    'dateline';
    const INDEX_4            =    'newsstatus, start, expiry, staffvisibilitycustom';
    const INDEX_5            =    'start, expiry, staffvisibilitycustom';
    const INDEX_6            =    'subject';

    protected $_dataStore = array();

    public $NewsSubscriberDispatch = false;

    // Core Constants
    const TYPE_GLOBAL = 1;
    const TYPE_PUBLIC = 2;
    const TYPE_PRIVATE = 3;

    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_newsItemID The News Item ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_newsItemID)
    {
        parent::__construct();

        if (!$this->LoadData($_newsItemID)) {
            throw new SWIFT_NewsItem_Exception('Failed to load News Item ID: ' .  ($_newsItemID));
        }

        $this->NewsSubscriberDispatch = new SWIFT_NewsSubscriberDispatch($this);
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_NewsItem_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'newsitems', $this->GetUpdatePool(), 'UPDATE', "newsitemid = '" . ($this->GetNewsItemID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the News Item ID
     *
     * @author Varun Shoor
     * @return mixed "newsitemid" on Success, "false" otherwise
     * @throws SWIFT_NewsItem_Exception If the Class is not Loaded
     */
    public function GetNewsItemID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['newsitemid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_newsItemID The News Item ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_newsItemID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT newsitems.*, newsitemdata.contents FROM " . TABLE_PREFIX . "newsitems AS newsitems
            LEFT JOIN " . TABLE_PREFIX . "newsitemdata AS newsitemdata ON (newsitems.newsitemid = newsitemdata.newsitemid)
            WHERE newsitems.newsitemid = '" .  ($_newsItemID) . "'");
        if (isset($_dataStore['newsitemid']) && !empty($_dataStore['newsitemid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_NewsItem_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_NewsItem_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_NewsItem_Exception(SWIFT_INVALIDDATA . ': ' . $_key);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid news type
     *
     * @author Varun Shoor
     * @param int $_newsType The News Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidNewsType($_newsType)
    {
        if ($_newsType == self::TYPE_GLOBAL || $_newsType == self::TYPE_PUBLIC || $_newsType == self::TYPE_PRIVATE)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid news status
     *
     * @author Varun Shoor
     * @param mixed $_newsStatus The News Status
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidNewsStatus($_newsStatus)
    {
        if ($_newsStatus == self::STATUS_DRAFT || $_newsStatus == self::STATUS_PUBLISHED)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new News Item Record
     *
     * @author Varun Shoor
     * @param mixed $_newsType The News Type
     * @param int $_newsStatus The News Status
     * @param string $_author The News Author
     * @param string $_email The Email Address
     * @param string $_subject The News Subject
     * @param string $_description The News Item Description
     * @param string $_newsContents The News Contents
     * @param int|bool $_staffID (OPTIONAL) The Staff ID of Creator
     * @param int|bool $_expiry (OPTIONAL) The News Expiry
     * @param bool $_allowComments (OPTIONAL) Whether to allow comments
     * @param bool $_userVisibilityCustom (OPTIONAL) Whether to restrict to custom user groups
     * @param array $_userGroupIDList (OPTIONAL) The Custom User Group ID List
     * @param bool $_staffVisibilityCustom (OPTIONAL) Whether to restrict to custom staff groups
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Group ID List
     * @param bool $_isSynced (OPTIONAL) Whether the news item is synced
     * @param string $_syncGUID (OPTIONAL) The Sync GUID
     * @param string $_emailSubject (OPTIONAL) The Email Subject
     * @param int|bool $_customDateline (OPTIONAL) The Custom Dateline
     * @param array $_newsCategoryIDList (OPTIONAL) The Linked News Category ID List
     * @param bool $_sendEmail (OPTIONAL) Whether to Send Email
     * @param string $_fromName (OPTIONAL) The From Name for Email
     * @param string $_fromEmail (OPTIONAL) The From Email Address
     * @param int|bool $_start (OPTIONAL) The News Expiry
     * @return mixed "_newsItemID" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create($_newsType, $_newsStatus, $_author, $_email, $_subject, $_description, $_newsContents, $_staffID = false, $_expiry = false, $_allowComments = true,
            $_userVisibilityCustom = false, $_userGroupIDList = array(), $_staffVisibilityCustom = false, $_staffGroupIDList = array(), $_isSynced = false, $_syncGUID = '',
            $_emailSubject = '', $_customDateline = false, $_newsCategoryIDList = array(), $_sendEmail = true, $_fromName = '', $_fromEmail = '', $_start = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ((empty($_subject) && empty($_description) && empty($_newsContents)) || !self::IsValidNewsType($_newsType))
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_subject)) {
            $_subject = substr(strip_tags(empty($_description) ? $_newsContents : $_description), 0, 40) . '...';
        }

        $_syncDateline = 0;
        $_syncGUIDHash = '';
        if ($_isSynced == true)
        {
            $_syncDateline = DATENOW;
            $_syncGUIDHash = md5($_syncGUID);
        }

        $_subjectHash = md5($_subject);
        $_descriptionHash = md5($_description);
        $_contentsHash = md5($_newsContents);

        $_finalDateline = DATENOW;
        if (!empty($_customDateline))
        {
            $_finalDateline = $_customDateline;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'newsitems', array('staffid' => (int) ($_staffID), 'newstype' => (int) ($_newsType), 'author' => $_author, 'subject' => $_subject,
            'emailsubject' => $_emailSubject, 'description' => $_description, 'dateline' => (int) ($_finalDateline), 'start' => (int) ($_start), 'expiry' => (int) ($_expiry), 'edited' => '0', 'editedstaffid' => '0',
            'editeddateline' => '0', 'totalcomments' => '0', 'issynced' => (int) ($_isSynced), 'syncdateline' => $_syncDateline, 'syncguidhash' => $_syncGUIDHash,
            'subjecthash' => $_subjectHash, 'descriptionhash' => $_descriptionHash, 'contentshash' => $_contentsHash, 'email' => $_email, 'newsstatus' => $_newsStatus,
            'allowcomments' => (int) ($_allowComments), 'uservisibilitycustom' => (int) ($_userVisibilityCustom), 'staffvisibilitycustom' => (int) ($_staffVisibilityCustom)), 'INSERT');
        $_newsItemID = $_SWIFT->Database->Insert_ID();

        if (!$_newsItemID)
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'newsitemdata', array('newsitemid' => ($_newsItemID), 'contents' => $_newsContents), 'INSERT');

        if ($_userVisibilityCustom == true)
        {
            if (_is_array($_userGroupIDList))
            {
                foreach ($_userGroupIDList as $_userGroupID)
                {
                    SWIFT_UserGroupAssign::Insert($_newsItemID, SWIFT_UserGroupAssign::TYPE_NEWS, $_userGroupID);
                }
            }
        }

        if ($_staffVisibilityCustom == true)
        {
            if (_is_array($_staffGroupIDList))
            {
                foreach ($_staffGroupIDList as $_staffGroupID)
                {
                    SWIFT_StaffGroupLink::Create($_staffGroupID, SWIFT_StaffGroupLink::TYPE_NEWS, $_newsItemID);
                }
            }
        }

        if (_is_array($_newsCategoryIDList))
        {
            foreach ($_newsCategoryIDList as $_newsCategoryID)
            {
                SWIFT_NewsCategoryLink::Create($_newsCategoryID, $_newsItemID);
            }
        }

        SWIFT_NewsCategory::RebuildCache();

        $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        if ($_sendEmail == true && $_newsStatus == self::STATUS_PUBLISHED)
        {
            $_finalEmailSubject = $_subject;
            if (!empty($_emailSubject))
            {
                $_finalEmailSubject = $_emailSubject;
            }

            if (empty($_fromName))
            {
                $_fromName = SWIFT::Get('companyname');
            }

            if (empty($_fromEmail) || !IsEmailValid($_fromEmail))
            {
                $_fromEmail = $_SWIFT->Settings->Get('general_returnemail');
            }

            $_SWIFT_NewsItemObject->NewsSubscriberDispatch->Send($_finalEmailSubject, $_fromName, $_fromEmail, $_userVisibilityCustom, $_userGroupIDList,
                    $_staffVisibilityCustom, $_staffGroupIDList);
        }

        // Search engine indexing
        $eng = new SWIFT_SearchEngine();
        $eng->Insert($_newsItemID, 0, SWIFT_SearchEngine::TYPE_NEWS, $_newsContents);

        return $_newsItemID;
    }

    /**
     * Update the News Record
     *
     * @author Varun Shoor
     * @param string $_subject The News Subject
     * @param string $_description The News Item Description
     * @param string $_newsContents The News Contents
     * @param int|bool $_staffID (OPTIONAL) The Staff ID of Updater
     * @param int|bool $_expiry (OPTIONAL) The News Expiry
     * @param bool $_allowComments (OPTIONAL) Whether to allow comments
     * @param bool $_userVisibilityCustom (OPTIONAL) Whether to restrict to custom user groups
     * @param array $_userGroupIDList (OPTIONAL) The Custom User Group ID List
     * @param bool $_staffVisibilityCustom (OPTIONAL) Whether to restrict to custom staff groups
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Group ID List
     * @param string $_emailSubject (OPTIONAL) The Email Subject
     * @param array $_newsCategoryIDList (OPTIONAL) The Linked News Category ID List
     * @param bool $_sendEmail (OPTIONAL) Whether to Send Email
     * @param string $_fromName (OPTIONAL) The From Name for Email
     * @param string $_fromEmail (OPTIONAL) The From Email Address
     * @param int|bool $_start (OPTIONAL) The News Start date
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_subject, $_description, $_newsContents, $_staffID = false, $_expiry = false, $_allowComments = true,
            $_userVisibilityCustom = false, $_userGroupIDList = array(), $_staffVisibilityCustom = false, $_staffGroupIDList = array(), $_emailSubject = '', $_newsCategoryIDList = array(),
            $_sendEmail = true, $_fromName = '', $_fromEmail = '', $_start = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_subject) || empty($_newsContents)) {
            throw new SWIFT_NewsItem_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('subject', $_subject);
        $this->UpdatePool('emailsubject', $_emailSubject);
        $this->UpdatePool('description', $_description);
        $this->UpdatePool('subjecthash', md5($_subject));
        $this->UpdatePool('descriptionhash', md5($_description));
        $this->UpdatePool('contentshash', md5($_newsContents));
        $this->UpdatePool('start', (int) ($_start));
        $this->UpdatePool('expiry', (int) ($_expiry));
        $this->UpdatePool('edited', '1');
        $this->UpdatePool('editedstaffid', ($_staffID));
        $this->UpdatePool('editeddateline', DATENOW);
        $this->UpdatePool('allowcomments', ($_allowComments));
        $this->UpdatePool('uservisibilitycustom', ($_userVisibilityCustom));
        $this->UpdatePool('staffvisibilitycustom', ($_staffVisibilityCustom));
        $this->ProcessUpdatePool();

        $this->Database->AutoExecute(TABLE_PREFIX . 'newsitemdata', array('contents' => $_newsContents), 'UPDATE', "newsitemid = '" . ($this->GetNewsItemID()) . "'");

        SWIFT_UserGroupAssign::DeleteList(array($this->GetNewsItemID()), SWIFT_UserGroupAssign::TYPE_NEWS);

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3180 Updating News with "Send Email" sends previous version
         *
         * Comments: Object was not updating in the case of contents
         */
        $this->_dataStore['contents'] = $_newsContents;

        if ($_userVisibilityCustom == true)
        {
            if (_is_array($_userGroupIDList))
            {
                foreach ($_userGroupIDList as $_userGroupID)
                {
                    SWIFT_UserGroupAssign::Insert($this->GetNewsItemID(), SWIFT_UserGroupAssign::TYPE_NEWS, $_userGroupID);
                }
            }
        }

        SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_NEWS, array($this->GetNewsItemID()));
        if ($_staffVisibilityCustom == true)
        {
            if (_is_array($_staffGroupIDList))
            {
                foreach ($_staffGroupIDList as $_staffGroupID)
                {
                    SWIFT_StaffGroupLink::Create($_staffGroupID, SWIFT_StaffGroupLink::TYPE_NEWS, $this->GetNewsItemID());
                }
            }
        }

        SWIFT_NewsCategoryLink::DeleteOnNewsItem(array($this->GetNewsItemID()));
        if (_is_array($_newsCategoryIDList))
        {
            foreach ($_newsCategoryIDList as $_newsCategoryID)
            {
                SWIFT_NewsCategoryLink::Create($_newsCategoryID, $this->GetNewsItemID());
            }
        }

        SWIFT_NewsCategory::RebuildCache();

        if ($_sendEmail == true && $this->GetProperty('newsstatus') == self::STATUS_PUBLISHED)
        {
            $_finalEmailSubject = $_subject;
            if (!empty($_emailSubject))
            {
                $_finalEmailSubject = $_emailSubject;
            }

            if (empty($_fromName))
            {
                $_fromName = SWIFT::Get('companyname');
            }

            if (empty($_fromEmail) || !IsEmailValid($_fromEmail))
            {
                $_fromEmail = $this->Settings->Get('general_returnemail');
            }

            $this->NewsSubscriberDispatch->Send($_finalEmailSubject, $_fromName, $_fromEmail, $_userVisibilityCustom, $_userGroupIDList,
                    $_staffVisibilityCustom, $_staffGroupIDList);
        }

        // Search engine indexing
        $eng = new SWIFT_SearchEngine();
        $eng->Update($this->GetNewsItemID(), 0, SWIFT_SearchEngine::TYPE_NEWS, $_newsContents);

        return true;
    }

    /**
     * Update the News Status
     *
     * @author Varun Shoor
     * @param string $_newsStatus The News Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateStatus($_newsStatus)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('newsstatus', $_newsStatus);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the News Record
     *
     * @author Varun Shoor
     * @param string $_subject The News Subject
     * @param string $_description The News Item Description
     * @param string $_newsContents The News Contents
     * @param int|bool $_staffID (OPTIONAL) The Staff ID
     * @param bool $_sendEmail (OPTIONAL) Whether to send email
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateContents($_subject, $_description, $_newsContents, $_staffID = false, $_sendEmail = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_subject) || empty($_newsContents)) {
            throw new SWIFT_NewsItem_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('subject', $_subject);
        $this->UpdatePool('description', $_description);
        $this->UpdatePool('subjecthash', md5($_subject));
        $this->UpdatePool('descriptionhash', md5($_description));
        $this->UpdatePool('contentshash', md5($_newsContents));
        $this->UpdatePool('edited', '1');
        $this->UpdatePool('editedstaffid', ($_staffID));
        $this->UpdatePool('editeddateline', DATENOW);
        $this->ProcessUpdatePool();

        $this->Database->AutoExecute(TABLE_PREFIX . 'newsitemdata', array('contents' => $_newsContents), 'UPDATE', "newsitemid = '" . ($this->GetNewsItemID()) . "'");

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3180 Updating News with "Send Email" sends previous version
         *
         * Comments: Object was not updating in the case of contents
         */
        $this->_dataStore['contents'] = $_newsContents;

        if ($_sendEmail == true)
        {
            $_finalEmailSubject = $_subject;

            $this->NewsSubscriberDispatch->Send($_finalEmailSubject, SWIFT::Get('companyname'), $this->Settings->Get('general_returnemail'), false, array(), false, array());
        }

        return true;
    }

    /**
     * Delete the News Item record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_NewsItem_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_NewsItem_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetNewsItemID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of News Items
     *
     * @author Varun Shoor
     * @param array $_newsItemIDList The News Item ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_newsItemIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsItemIDList))
        {
            return false;
        }

        $_finalNewsItemIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newsitems WHERE newsitemid IN (" . BuildIN($_newsItemIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalNewsItemIDList[] = $_SWIFT->Database->Record['newsitemid'];
        }

        if (!count($_finalNewsItemIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "newsitems WHERE newsitemid IN (" . BuildIN($_finalNewsItemIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "newsitemdata WHERE newsitemid IN (" . BuildIN($_finalNewsItemIDList) . ")");

        // Search engine index
        $eng = new SWIFT_SearchEngine();
        $eng->DeleteList($_finalNewsItemIDList, SWIFT_SearchEngine::TYPE_NEWS);

        SWIFT_NewsCategoryLink::DeleteOnNewsItem($_finalNewsItemIDList);

        SWIFT_UserGroupAssign::DeleteList($_finalNewsItemIDList, SWIFT_UserGroupAssign::TYPE_NEWS);

        SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_NEWS, $_finalNewsItemIDList);

        SWIFT_Comment::DeleteOnType(SWIFT_Comment::TYPE_NEWS, $_finalNewsItemIDList);

        SWIFT_NewsCategory::RebuildCache();

        return true;
    }

    /**
     * Retrieve the parsed news items
     *
     * @author Varun Shoor
     * @param int $_itemCount The Item Count
     * @param int $_offset The Offset
     * @param array|bool $_newsTypeList The News Type List
     * @param int|bool $_userGroupID (OPTIONAL) Filter by User Group ID
     * @param int|bool $_staffGroupID (OPTIONAL) Filter by Staff Group ID
     * @param int|bool $_newsCategoryID (OPTIONAL) Filter by News Category ID
     * @return array The News Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_itemCount, $_offset = 0, $_newsTypeList = false, $_userGroupID = false, $_staffGroupID = false, $_newsCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsTypeList))
        {
            $_newsTypeList = array(self::TYPE_PUBLIC);
        }

        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');

        $_newsContainer = $_staffNameCache = array();

        $_filterUserNewsItemIDList = $_filterStaffNewsItemIDList = $_filterNewsItemIDList = array();
        if (!empty($_userGroupID))
        {
            $_filterUserNewsItemIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_NEWS);
        }

        if (!empty($_newsCategoryID))
        {
            $_filterNewsItemIDList = SWIFT_NewsCategoryLink::RetrieveOnNewsCategory(array($_newsCategoryID));
        }

        if (!empty($_staffGroupID))
        {
            $_filterStaffNewsItemIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_NEWS, $_staffGroupID);
        }

        $_SWIFT->Database->QueryLimit("SELECT newsitems.*, newsitemdata.contents FROM " . TABLE_PREFIX . "newsitems AS newsitems
            LEFT JOIN " . TABLE_PREFIX . "newsitemdata AS newsitemdata ON (newsitems.newsitemid = newsitemdata.newsitemid)
            WHERE newsitems.newstype IN (" . BuildIN($_newsTypeList) . ")
                AND newsitems.newsstatus = '" . self::STATUS_PUBLISHED . "'
                AND (newsitems.start < '" . DATENOW . "' OR newsitems.start = '0')
                AND (newsitems.expiry > '" . DATENOW . "' OR newsitems.expiry = '0')
                " . IIF(!empty($_userGroupID), "AND (newsitems.uservisibilitycustom = '0' OR (newsitems.uservisibilitycustom = '1' AND newsitems.newsitemid IN (" . BuildIN($_filterUserNewsItemIDList) . ")))") . "
                " . IIF(!empty($_staffGroupID), "AND (newsitems.staffvisibilitycustom = '0' OR (newsitems.staffvisibilitycustom = '1' AND newsitems.newsitemid IN (" . BuildIN($_filterStaffNewsItemIDList) . ")))") . "
                " . IIF(!empty($_filterNewsItemIDList), " AND newsitems.newsitemid IN (" . BuildIN($_filterNewsItemIDList) . ")") . "
            ORDER BY newsitems.dateline DESC", $_itemCount, $_offset);
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsContainer[$_SWIFT->Database->Record['newsitemid']] = $_SWIFT->Database->Record;

            $_emailHash = md5($_SWIFT->Database->Record['email']);
            if (isset($_staffCache[$_SWIFT->Database->Record['staffid']]['email']))
            {
                $_emailHash = md5($_staffCache[$_SWIFT->Database->Record['staffid']]['email']);
            } else if (isset($_staffNameCache[trim(mb_strtolower($_SWIFT->Database->Record['author']))])) {
                $_emailHash = $_staffNameCache[trim(mb_strtolower($_SWIFT->Database->Record['author']))];

            } else if (empty($_SWIFT->Database->Record['email'])) {
                $_foundStaff = false;

                foreach ($_staffCache as $_staffContainer)
                {
                    if (isset($_staffContainer['fullname'], $_staffContainer['email']) && trim(mb_strtolower($_staffContainer['fullname'])) == trim(mb_strtolower($_SWIFT->Database->Record['author'])))
                    {
                        $_emailHash = md5($_staffContainer['email']);

                        $_staffNameCache[trim(mb_strtolower($_SWIFT->Database->Record['author']))] = $_emailHash;
                        $_foundStaff = true;

                        break;
                    }
                }

                if (!$_foundStaff)
                {
                    $_staffNameCache[trim(mb_strtolower($_SWIFT->Database->Record['author']))] = $_emailHash;
                }
            }
            $subject = CleanURL($_SWIFT->Database->Record['subject']);

            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['emailhash'] = $_emailHash;

            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['parsedmonth'] = strftime('%b', $_SWIFT->Database->Record['dateline']);
            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['parseddate'] = self::GetStrippedDay(strftime('%d', $_SWIFT->Database->Record['dateline']));
            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['urlextension'] = str_replace(' ', '-', $subject);
            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['subject'] = htmlspecialchars($_SWIFT->Database->Record['subject']);
            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['author'] = htmlspecialchars($_SWIFT->Database->Record['author']);
            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['date'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT->Database->Record['dateline']);

            /*
             * BUG FIX - Busayo Arotimi
             *
             * KAYAKOC-9177 | News with tables that have italics will break the dashboard
             */
            $contents = static::GetLeadParagraph($_SWIFT->Database->Record['contents'], 500);
            $_SWIFT_HTMLPurifierObject = $_SWIFT->HTMLPurifier;
            if (!$_SWIFT->HTMLPurifier instanceof SWIFT_HTMLPurifier) {
                $_SWIFT_HTMLPurifierObject = new SWIFT_HTMLPurifier();
                $_SWIFT->SetClass('HTMLPurifier', $_SWIFT_HTMLPurifierObject);
            }
            $contents =  $_SWIFT_HTMLPurifierObject->Purify($contents);

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-2054 News contents should convert script data to entities
             *
             */
            $_newsContainer[$_SWIFT->Database->Record['newsitemid']]['contents'] = StripTags(StripScriptTags($contents), '<img><a>');
        }

        return $_newsContainer;
    }

    /**
     * Returns the opening text of $value, preserving HTML tags like inline images
     *
     * @param string $value
     * @param int $limit
     * @return string $result
     *
     * @author Werner Garcia <werner.garcia@crossover.com>
     *
     */
    public static function GetLeadParagraph($value, $limit = 500)
    {
        // preserve UTF-8 characters
        $value = html_entity_decode($value);

        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        // Strip text with HTML tags and sum html len tags
        do {
            $len          = mb_strwidth($value, 'UTF-8');
            $len_stripped = mb_strwidth(strip_tags($value), 'UTF-8');
            $len_tags     = $len - $len_stripped;

            $value = mb_strimwidth($value, 0, $limit + $len_tags, '', 'UTF-8');

            // break at a space, not in the middle of a word
            if (substr($value, -1, 1) != ' ') {
                $value = substr($value, 0, strrpos($value, ' '));
            }
        } while ($len_stripped > $limit);

        // Load as HTML ignoring errors
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $value, LIBXML_HTML_NODEFDTD);

        // Fix the html errors
        $value = $dom->saveHtml($dom->getElementsByTagName('body')->item(0));

        // Remove body tag: <body> and </body>
        $value = mb_strimwidth($value, 6, mb_strwidth($value, 'UTF-8') - 13, '', 'UTF-8');

        // Remove empty tags
        $result = preg_replace('/<(\w+)\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\1\s*>/',
            '', $value);

        return $result;
    }

    /**
     * Get the day without 0 in front
     *
     * @author Varun Shoor
     * @param string $_day The Day
     * @return string
     */
    public static function GetStrippedDay($_day) {
        if (substr($_day, 0, 1) == '0') {
            return substr($_day, 1);
        }

        return $_day;
    }

    /**
     * Retrieve the parsed category count
     *
     * @author Varun Shoor
     * @param array $_newsTypeList The News Type List
     * @param int $_userGroupID (OPTIONAL) Filter by User Group ID
     * @param int $_staffGroupID (OPTIONAL) Filter by Staff Group ID
     * @return array The News Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveCategoryCount($_newsTypeList = null, $_userGroupID = null, $_staffGroupID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsTypeList))
        {
            $_newsTypeList = array(self::TYPE_PUBLIC);
        }

        $_newsCategoryCache = (array) $_SWIFT->Cache->Get('newscategorycache');

        $_newsCategoryCountContainer = array();

        $_filterNewsItemIDList = array();
        if (!empty($_userGroupID))
        {
            $_filterNewsItemIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_NEWS);
        }

        $_SWIFT->Database->Query("SELECT COUNT(*) AS totalitems, newscategorylinks.newscategoryid FROM " . TABLE_PREFIX . "newscategorylinks AS newscategorylinks
            LEFT JOIN " . TABLE_PREFIX . "newsitems AS newsitems ON (newscategorylinks.newsitemid = newsitems.newsitemid)
            WHERE newsitems.newstype IN (" . BuildIN($_newsTypeList) . ")
                AND newsitems.newsstatus = '" . self::STATUS_PUBLISHED . "'
                AND (newsitems.start < '" . DATENOW . "' OR newsitems.start = '0')
                AND (newsitems.expiry > '" . DATENOW . "' OR newsitems.expiry = '0')
                AND (newsitems.uservisibilitycustom = '0' OR (newsitems.uservisibilitycustom = '1' AND newsitems.newsitemid IN (" . BuildIN($_filterNewsItemIDList) . ")))
            GROUP BY newscategorylinks.newscategoryid");
        while ($_SWIFT->Database->NextRecord())
        {
            $_newsCategoryCountContainer[$_SWIFT->Database->Record['newscategoryid']] = ($_SWIFT->Database->Record['totalitems']);
        }

        $_finalNewsCategoryCountContainer = array();
        if (count($_newsCategoryCountContainer) && _is_array($_newsCategoryCache))
        {
            foreach ($_newsCategoryCache as $_newsCategoryID => $_newsCategoryContainer)
            {
                if ($_newsCategoryContainer['visibilitytype'] == SWIFT_PUBLIC && !in_array(SWIFT_NewsItem::TYPE_PUBLIC, $_newsTypeList))
                {
                    continue;
                } else if ($_newsCategoryContainer['visibilitytype'] == SWIFT_PRIVATE && !in_array(SWIFT_NewsItem::TYPE_PRIVATE, $_newsTypeList)) {
                    continue;
                }

                if (isset($_newsCategoryContainer['categorytitle'])) {
                    $_newsCategoryContainer['categorytitle'] = htmlspecialchars($_newsCategoryContainer['categorytitle']);
                }

                $_finalNewsCategoryCountContainer[$_newsCategoryID] = $_newsCategoryContainer;
                $_finalNewsCategoryCountContainer[$_newsCategoryID]['totalitems'] = 0;

                if (isset($_newsCategoryCountContainer[$_newsCategoryID]))
                {
                    $_finalNewsCategoryCountContainer[$_newsCategoryID]['totalitems'] = $_newsCategoryCountContainer[$_newsCategoryID];
                }
            }
        }

        return $_finalNewsCategoryCountContainer;
    }

    /**
     * Retrieve the processed data store
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

        $_newsContainer = $this->GetDataStore();
        $_staffCache = (array) $this->Cache->Get('staffcache');

        $_emailHash = md5($_newsContainer['email']);
        if (isset($_staffCache[$_newsContainer['staffid']]['email']))
        {
            $_emailHash = md5($_staffCache[$_newsContainer['staffid']]['email']);
        } else if (empty($_newsContainer['email'])) {
            foreach ($_staffCache as $_staffContainer)
            {
                if (isset($_staffContainer['fullname']) && trim(mb_strtolower($_staffContainer['fullname'])) == trim(mb_strtolower($_newsContainer['author'])) && isset($_staffContainer['email']))
                {
                    $_emailHash = md5($_staffContainer['email']);
                    break;
                }
            }
        }

        $subject = CleanURL($_newsContainer['subject']);
        $_newsContainer['emailhash'] = $_emailHash;
        $_newsContainer['parsedmonth'] = strftime('%b', $_newsContainer['dateline']);
        $_newsContainer['parseddate'] = self::GetStrippedDay(strftime('%d', $_newsContainer['dateline']));
        $_newsContainer['urlextension'] = str_replace(' ', '-', $subject);
        $_newsContainer['subject'] = htmlspecialchars($_newsContainer['subject']);
        $_newsContainer['author'] = htmlspecialchars($_newsContainer['author']);
        $_newsContainer['date'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_newsContainer['dateline']);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-2054 News contents should convert script data to entities
         *
         */
        $_newsContainer['contents'] = StripScriptTags($_newsContainer['contents']);

        return $_newsContainer;
    }

    /**
     * Retrieve the parsed news items count
     *
     * @author Varun Shoor
     * @param array $_newsTypeList The News Type List
     * @param int $_userGroupID (OPTIONAL) Filter by User Group ID
     * @param int $_staffGroupID (OPTIONAL) Filter by Staff Group ID
     * @param int $_newsCategoryID (OPTIONAL) Filter by News Category ID
     * @return int The Total News COunt
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveCount($_newsTypeList = null, $_userGroupID = null, $_staffGroupID = null, $_newsCategoryID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsTypeList))
        {
            $_newsTypeList = array(self::TYPE_PUBLIC);
        }

        $_filterUserNewsItemIDList = $_filterNewsItemIDList = array();
        if (!empty($_userGroupID))
        {
            $_filterUserNewsItemIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_NEWS);
        }

        if (!empty($_newsCategoryID))
        {
            $_filterNewsItemIDList = SWIFT_NewsCategoryLink::RetrieveOnNewsCategory(array($_newsCategoryID));
        }

        $_totalItemCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "newsitems AS newsitems
            WHERE newsitems.newstype IN (" . BuildIN($_newsTypeList) . ")
                AND newsitems.newsstatus = '" . self::STATUS_PUBLISHED . "'
                AND (newsitems.start < '" . DATENOW . "' OR newsitems.start = '0')
                AND (newsitems.expiry > '" . DATENOW . "' OR newsitems.expiry = '0')
                AND (newsitems.uservisibilitycustom = '0' OR (newsitems.uservisibilitycustom = '1' AND newsitems.newsitemid IN (" . BuildIN($_filterUserNewsItemIDList) . ")))
                " . IIF(!empty($_filterNewsItemIDList), " AND newsitems.newsitemid IN (" . BuildIN($_filterNewsItemIDList) . ")"));
        if (isset($_totalItemCount['totalitems']) && !empty($_totalItemCount['totalitems']))
        {
            return $_totalItemCount['totalitems'];
        }

        return 0;
    }

    /**
     * Get the News Type Label
     *
     * @author Varun Shoor
     * @param mixed $_newsType The News Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetNewsTypeLabel($_newsType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidNewsType($_newsType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_newsType)
        {
            case self::TYPE_GLOBAL:
                return $_SWIFT->Language->Get('global');

                break;

            case self::TYPE_PRIVATE:
                return $_SWIFT->Language->Get('private');

                break;

            case self::TYPE_PUBLIC:
                return $_SWIFT->Language->Get('public');

                break;

            // @codeCoverageIgnoreStart
            // This code will never be executed
            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Retrieve the User Group ID's linked with this News Item
     *
     * @author Varun Shoor
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_NEWS, $this->GetNewsItemID());
    }

    /**
     * Retrieve the Staff Group ID's linked with this News Item
     *
     * @author Varun Shoor
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_NEWS, $this->GetNewsItemID());
    }
}
