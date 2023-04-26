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

namespace Base\Models\Comment;

use SWIFT;
use SWIFT_Akismet;
use Base\Library\Comment\SWIFT_Comment_Exception;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Comment Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_Comment extends SWIFT_Model
{
    const TABLE_NAME = 'comments';
    const PRIMARY_KEY = 'commentid';

    const TABLE_STRUCTURE = "commentid I PRIMARY AUTO NOTNULL,
                                typeid I DEFAULT '0' NOTNULL,
                                creatortype I2 DEFAULT '0' NOTNULL,
                                creatorid I DEFAULT '0' NOTNULL,
                                commenttype I2 DEFAULT '0' NOTNULL,
                                fullname C(255) DEFAULT '' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                parentcommentid I DEFAULT '0' NOTNULL,
                                commentstatus I2 DEFAULT '0' NOTNULL,
                                useragent C(255) DEFAULT '' NOTNULL,
                                referrer C(255) DEFAULT '' NOTNULL,
                                parenturl C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'commenttype, commentstatus, typeid';
    const INDEX_2 = 'parentcommentid';
    const INDEX_3 = 'dateline';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_KNOWLEDGEBASE = 1;
    const TYPE_TROUBLESHOOTER = 3;
    const TYPE_NEWS = 4;

    const CREATOR_STAFF = 1;
    const CREATOR_USER = 2;

    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_SPAM = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_commentID The Comment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Comment_Exception If the Record could not be loaded
     */
    public function __construct($_commentID)
    {
        parent::__construct();

        if (!$this->LoadData($_commentID)) {
            throw new SWIFT_Comment_Exception('Failed to load Comment ID: ' . $_commentID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
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
     * @throws SWIFT_Comment_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'comments', $this->GetUpdatePool(), 'UPDATE', "commentid = '" . (int)($this->GetCommentID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Comment ID
     *
     * @author Varun Shoor
     * @return mixed "commentid" on Success, "false" otherwise
     * @throws SWIFT_Comment_Exception If the Class is not Loaded
     */
    public function GetCommentID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Comment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['commentid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_commentID The Comment ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_commentID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "comments WHERE commentid = '" . $_commentID . "'");
        if (isset($_dataStore['commentid']) && !empty($_dataStore['commentid'])) {
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
     * @throws SWIFT_Comment_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Comment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Comment_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Comment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Comment_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Comment Type
     *
     * @author Varun Shoor
     * @param mixed $_commentType The Comment Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_commentType)
    {
        if ($_commentType == self::TYPE_KNOWLEDGEBASE || $_commentType == self::TYPE_TROUBLESHOOTER || $_commentType == self::TYPE_NEWS) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a Valid Creator
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreator($_creatorType)
    {
        if ($_creatorType == self::CREATOR_USER || $_creatorType == self::CREATOR_STAFF) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid comment status
     *
     * @author Varun Shoor
     * @param mixed $_commentStatus The Comment Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidStatus($_commentStatus)
    {
        if ($_commentStatus == self::STATUS_PENDING || $_commentStatus == self::STATUS_APPROVED || $_commentStatus == self::STATUS_SPAM) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Comment
     *
     * @author Varun Shoor
     * @param mixed $_commentType The Comment Type
     * @param int $_typeID The Type ID
     * @param mixed $_commentStatus The Comment Status
     * @param string $_fullName The Full Name
     * @param string $_email The Email of Creator
     * @param string $_ipAddress The IP Address
     * @param string $_commentContents The Comment Contents
     * @param mixed $_creatorType The Creator Type
     * @param int $_creatorID (OPTIONAL) The Creator ID
     * @param int $_parentCommentID (OPTIONAL) The Parent Comment ID
     * @param string $_userAgent (OPTIONAL) The User Agent
     * @param string $_referrer (OPTIONAL) The Referrer
     * @param string $_parentURL (OPTIONAL) The Parent URL
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Comment_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_commentType, $_typeID, $_commentStatus, $_fullName, $_email, $_ipAddress, $_commentContents, $_creatorType, $_creatorID = 0, $_parentCommentID = 0,
                                  $_userAgent = '', $_referrer = '', $_parentURL = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_typeID) || empty($_fullName) || empty($_commentContents) || !self::IsValidCreator($_creatorType) || !self::IsValidType($_commentType) || !self::IsValidStatus($_commentStatus)) {
            throw new SWIFT_Comment_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'comments', array('commenttype' => (int)($_commentType), 'typeid' => $_typeID, 'fullname' => $_fullName,
            'email' => $_email, 'creatortype' => (int)($_creatorType), 'creatorid' => $_creatorID, 'ipaddress' => $_ipAddress,
            'parentcommentid' => $_parentCommentID, 'commentstatus' => (int)($_commentStatus), 'useragent' => $_userAgent,
            'referrer' => $_referrer, 'dateline' => DATENOW, 'parenturl' => $_parentURL), 'INSERT');
        $_commentID = $_SWIFT->Database->Insert_ID();

        if (!$_commentID) {
            throw new SWIFT_Comment_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'commentdata', array('commentid' => $_commentID, 'contents' => $_commentContents), 'INSERT');

        self::RebuildCache();

        return $_commentID;
    }

    /**
     * Update the Comment Record
     *
     * @author Varun Shoor
     * @param int $_typeID The Type ID
     * @param string $_fullName The Full Name
     * @param string $_email The Email of Creator
     * @param string $_commentContents The Comment Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Comment_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_typeID, $_fullName, $_email, $_commentContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Comment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_typeID) || empty($_fullName) || empty($_commentContents)) {
            throw new SWIFT_Comment_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('fullname', $_fullName);
        $this->UpdatePool('email', $_email);

        $this->ProcessUpdatePool();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'commentdata', array('contents' => $_commentContents), 'UPDATE', "commentid = '" . (int)($this->GetCommentID()) . "'");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Comment record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Comment_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Comment_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetCommentID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Comment's
     *
     * @author Varun Shoor
     * @param array $_commentIDList The Comment ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_commentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_commentIDList)) {
            return false;
        }

        $_finalCommentIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "comments WHERE commentid IN (" . BuildIN($_commentIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalCommentIDList[] = $_SWIFT->Database->Record['commentid'];
        }

        if (!_is_array($_finalCommentIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "comments WHERE commentid IN (" . BuildIN($_finalCommentIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "commentdata WHERE commentid IN (" . BuildIN($_finalCommentIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the comment on the given type
     *
     * @author Varun Shoor
     * @param mixed $_commentType The Comment Type
     * @param array $_typeIDList The Type ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnType($_commentType, $_typeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_commentType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_typeIDList)) {
            return false;
        }

        $_commentIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "comments WHERE commenttype = '" . (int)($_commentType) . "' AND typeid IN (" . BuildIN($_typeIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_commentIDList[] = (int)($_SWIFT->Database->Record['commentid']);
        }

        if (!count($_commentIDList)) {
            return false;
        }

        self::DeleteList($_commentIDList);

        return true;
    }

    /**
     * Approve a list of Comment's
     *
     * @author Varun Shoor
     * @param array $_commentIDList The Comment ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ApproveList($_commentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_commentIDList)) {
            return false;
        }

        $_finalCommentIDList = $_commentsContainer = array();
        $_SWIFT->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commentid IN (" . BuildIN($_commentIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalCommentIDList[] = $_SWIFT->Database->Record['commentid'];
            $_commentsContainer[$_SWIFT->Database->Record['commentid']] = $_SWIFT->Database->Record;
        }

        if (!_is_array($_finalCommentIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'comments', array('commentstatus' => self::STATUS_APPROVED), 'UPDATE', "commentid IN (" . BuildIN($_commentIDList) . ")");

        if ($_SWIFT->Settings->Get('security_enableakismet') == '1' && $_SWIFT->Settings->Get('security_akismetkey') != '') {
            $_SWIFT_AkismetObject = new SWIFT_Akismet();
            if ($_SWIFT_AkismetObject instanceof SWIFT_Akismet && $_SWIFT_AkismetObject->GetIsClassLoaded()) {
                foreach ($_commentsContainer as $_comment) {
                    $_SWIFT_AkismetObject->MarkAsHam($_comment['fullname'], $_comment['email'], $_comment['contents'], $_comment['useragent'], $_comment['referrer']);
                }
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Mark a list of Comment's as Spam
     *
     * @author Varun Shoor
     * @param array $_commentIDList The Comment ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SpamList($_commentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_commentIDList)) {
            return false;
        }

        $_finalCommentIDList = $_commentsContainer = array();
        $_SWIFT->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commentid IN (" . BuildIN($_commentIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalCommentIDList[] = $_SWIFT->Database->Record['commentid'];
            $_commentsContainer[$_SWIFT->Database->Record['commentid']] = $_SWIFT->Database->Record;
        }

        if (!_is_array($_finalCommentIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'comments', array('commentstatus' => self::STATUS_SPAM), 'UPDATE', "commentid IN (" . BuildIN($_commentIDList) . ")");

        if ($_SWIFT->Settings->Get('security_enableakismet') == '1' && $_SWIFT->Settings->Get('security_akismetkey') != '') {
            $_SWIFT_AkismetObject = new SWIFT_Akismet();
            if ($_SWIFT_AkismetObject instanceof SWIFT_Akismet && $_SWIFT_AkismetObject->GetIsClassLoaded()) {
                foreach ($_commentsContainer as $_comment) {
                    $_SWIFT_AkismetObject->MarkAsSpam($_comment['fullname'], $_comment['email'], $_comment['contents'], $_comment['useragent'], $_comment['referrer']);
                }
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the comments
     *
     * @author Varun Shoor
     * @param mixed $_commentType The Comment Type
     * @param int $_typeID The Type ID
     * @param mixed $_commentStatus (OPTIONAL) The Comment Status
     * @return array The Comment Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_commentType, $_typeID, $_commentStatus = SWIFT_Comment::STATUS_APPROVED)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_commentType) || !self::IsValidStatus($_commentStatus)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_commentContainer = $_finalCommentContainer = array();

        $_SWIFT->Database->Query("SELECT comments.*, commentdata.contents FROM " . TABLE_PREFIX . "comments AS comments
            LEFT JOIN " . TABLE_PREFIX . "commentdata AS commentdata ON (comments.commentid = commentdata.commentid)
            WHERE comments.commenttype = '" . (int)($_commentType) . "' AND comments.commentstatus = '" . (int)($_commentStatus) . "' AND comments.typeid = '" . $_typeID . "'
            ORDER BY comments.commentid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_commentContainer[$_SWIFT->Database->Record['commentid']] = $_SWIFT->Database->Record;
            $_commentContainer[$_SWIFT->Database->Record['commentid']]['fullname'] = text_to_html_entities($_SWIFT->Database->Record['fullname']);
            $_commentContainer[$_SWIFT->Database->Record['commentid']]['email'] = htmlspecialchars($_SWIFT->Database->Record['email']);
            $_commentContainer[$_SWIFT->Database->Record['commentid']]['date'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT->Database->Record['dateline']);
            $_commentContainer[$_SWIFT->Database->Record['commentid']]['emailhash'] = md5($_SWIFT->Database->Record['email']);

            $_profileImageURL = '';
            if ($_SWIFT->Database->Record['creatortype'] == SWIFT_Comment::CREATOR_STAFF) {
                $_profileImageURL = SWIFT::Get('basename') . '/Base/StaffProfile/DisplayAvatar/' . $_SWIFT->Database->Record['creatorid'] . '/' . md5($_SWIFT->Database->Record['email']) . '/40';
            } else {
                $_profileImageURL = SWIFT::Get('basename') . '/Base/User/DisplayAvatar/' . $_SWIFT->Database->Record['creatorid'] . '/' . md5($_SWIFT->Database->Record['email']) . '/40';
            }
            $_commentContainer[$_SWIFT->Database->Record['commentid']]['avatarurl'] = $_profileImageURL;

            $_commentContainer[$_SWIFT->Database->Record['commentid']]['contents'] = nl2br(htmlspecialchars($_SWIFT->Database->Record['contents']));
            $_commentContainer[$_SWIFT->Database->Record['commentid']]['isstaff'] = IIF($_SWIFT->Database->Record['creatortype'] == self::CREATOR_STAFF, true, false);
        }

        self::RetrieveSub(0, $_commentContainer, $_finalCommentContainer);

        return $_finalCommentContainer;
    }

    /**
     * Retrieve Sub Comments
     *
     * @author Varun Shoor
     * @param int $_incomingParentCommentID The Incoming Parent Comment ID
     * @param array $_commentContainer The Comment Container
     * @param array $_finalCommentContainer The Final Comment Container
     * @param int $_padding (OPTIONAL) The Padding Offset
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function RetrieveSub($_incomingParentCommentID, &$_commentContainer, &$_finalCommentContainer, $_padding = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        foreach ($_commentContainer as $_commentID => $_comment) {
            $_parentCommentID = $_comment['parentcommentid'];

            if ($_parentCommentID != $_incomingParentCommentID || (!isset($_commentContainer[$_parentCommentID]) && $_parentCommentID != 0)) {
                continue;
            }

            $_finalCommentContainer[$_commentID] = $_comment;
            $_finalCommentContainer[$_commentID]['padding'] = $_padding;

            self::RetrieveSub($_commentID, $_commentContainer, $_finalCommentContainer, ($_padding + 40));
        }

        return true;
    }

    /**
     * Rebuild the Comment Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cacheContainer = array();

        $_SWIFT->Database->Query("SELECT commentstatus, COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "comments GROUP BY commentstatus");
        while ($_SWIFT->Database->NextRecord()) {
            $_cacheContainer[$_SWIFT->Database->Record['commentstatus']] = (int)($_SWIFT->Database->Record['totalitems']);
        }

        $_SWIFT->Cache->Update('commentscache', $_cacheContainer);

        return true;
    }

    /**
     * Retrieve the Comment Counter
     *
     * @author Varun Shoor
     * @return array The Comment Counter
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetCommentCounter()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_commentsCache = $_SWIFT->Cache->Get('commentscache');

        $_counter = false;

        if (isset($_commentsCache[self::STATUS_PENDING]) && $_commentsCache[self::STATUS_PENDING] > 0) {
            $_counter = array($_SWIFT->Language->Get('dashcomments'), number_format($_commentsCache[self::STATUS_PENDING], 0), '/Base/Comment/Manage/' . self::STATUS_PENDING);
        }

        return $_counter;
    }

    /**
     * Update secondary user IDs with merged primary user ID
     *
     * @author Pankaj Garg
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     */
    public static function UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }
        $_commentContainer = array();
        $_SWIFT->Database->Query("SELECT commentid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                  WHERE creatortype = " . self::CREATOR_USER . "
                                    AND creatorid IN ( " . BuildIN($_secondaryUserIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_commentContainer[$_SWIFT->Database->Record['commentid']] = $_SWIFT->Database->Record;
        }
        foreach ($_commentContainer as $_comment) {
            $_Comment = new SWIFT_Comment($_comment['commentid']);
            $_Comment->UpdateCreator($_primaryUserID, self::CREATOR_USER);
        }
        return true;
    }

    /**
     * Updates the Creator with which the comment is linked
     *
     * @author Abhishek Mittal
     *
     * @param int $_creatorID
     * @param int $_creatorType
     *
     * @return SWIFT_Comment
     * @throws SWIFT_Exception If the Class is not Loaded OR If Invalid Data is Provided
     */
    public function UpdateCreator($_creatorID, $_creatorType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }
        if (!self::IsValidCreator($_creatorType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $this->UpdatePool('creatorid', $_creatorID);
        $this->UpdatePool('creatortype', $_creatorType);
        $this->ProcessUpdatePool();
        return $this;
    }
}

?>
