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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Data;
use SWIFT_Exception;
use SWIFT_Model;
use SWIFT_Session;

/**
 * User > Email Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserEmail extends SWIFT_UserEmailManager {
    static protected $_emailCache = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_userEmailID The User Email ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct($_userEmailID) {
        parent::__construct($_userEmailID);

        if ($this->GetProperty('linktype') != self::LINKTYPE_USER)
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Create a new User Email Record
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The User Object
     * @param string $_email The User Email
     * @param bool $_isPrimary Whether the Email is Primary one for this user
     * @return mixed "SWIFT_UserNote" Object on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_SWIFT_UserObject, $_email, $_isPrimary = false, $_ = null) {
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userEmailID = parent::Create(self::LINKTYPE_USER, $_SWIFT_UserObject->GetUserID(), $_email, $_isPrimary);
        if (!$_userEmailID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        self::$_emailCache = array();

        return new SWIFT_UserEmail($_userEmailID);
    }

    /**
     * Delete the User Emails on the User ID List
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUser($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList))
        {
            return false;
        }

        $_userEmailIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userEmailIDList[] = $_SWIFT->Database->Record['useremailid'];
        }

        if (!count($_userEmailIDList))
        {
            return false;
        }

        self::$_emailCache = array();

        self::DeleteList($_userEmailIDList);

        return true;
    }

    /**
     * Retrieve User ID based on User Email
     *
     * @author Varun Shoor
     * @param string $_userEmail The User Email
     * @return int
     */
    public static function RetrieveUserIDOnUserEmail($_userEmail)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userEmail))
        {
            return 0;
        }

        $_userEmailContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND email = '" . $_SWIFT->Database->Escape($_userEmail) . "'");
        if (isset($_userEmailContainer['linktypeid']) && !empty($_userEmailContainer['linktypeid']))
        {
            return $_userEmailContainer['linktypeid'];
        }

        return 0;
    }

    /**
     * Retrieve User ID List based on User Email ID List
     *
     * @author Varun Shoor
     * @param array $_userEmailIDList The User Email ID List
     * @return array|bool "true" on Success, "false" otherwise
     */
    public static function RetrieveUserIDListOnUserEmail($_userEmailIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userEmailIDList))
        {
            return false;
        }

        $_userIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND useremailid IN (" . BuildIN($_userEmailIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userIDList[] = $_SWIFT->Database->Record['linktypeid'];
        }

        if (!count($_userIDList))
        {
            return false;
        }

        return $_userIDList;
    }

    /**
     * Check to see if a given email already exists.. if it does, return the relevant user id
     *
     * @author Varun Shoor
     * @param array $_emailList The Email List
     * @param int $_currentUserID (OPTIONAL) The Current User ID to ignore
     * @return mixed array(email, linktypeid) on Success, "false" otherwise
     */
    public static function CheckEmailRecordExists($_emailList, $_currentUserID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailList))
        {
            return false;
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND email IN (" . BuildIN($_emailList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!empty($_currentUserID) && $_SWIFT->Database->Record['linktypeid'] == $_currentUserID)
            {
                // Belongs to the current user ignore...
            } else {
                return array($_SWIFT->Database->Record['email'], $_SWIFT->Database->Record['linktypeid']);
            }
        }

        return false;
    }

    /**
     * Retrieve all emails for the given user id
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param bool $_tagID (OPTIONAL) Whether to tag the useremailid
     * @return array "_emailList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveList($_userID, $_tagID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userID))
        {
            return array();
        }

        $_cacheKey = $_userID . '-' . $_tagID;
        // Check for Cache only when tagid is provided.
        if ($_tagID && isset(self::$_emailCache[$_cacheKey]))
        {
            return self::$_emailCache[$_cacheKey];
        }

        $_emailList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND linktypeid = '" . $_userID . "' ORDER BY useremailid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            if ($_tagID)
            {
                $_emailList[$_SWIFT->Database->Record['useremailid']] = $_SWIFT->Database->Record['email'];
            } else {
                $_emailList[] = $_SWIFT->Database->Record['email'];
            }
        }

        self::$_emailCache[$_cacheKey] = $_emailList;

        return $_emailList;
    }

    /**
     * Retrieve emails address for all the customers
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @return mixed $_emailList
     */
    public static function RetrieveEmailofAllUsers()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailList = [];

        $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "useremails");

        while ($_SWIFT->Database->NextRecord())
        {
            $_emailList[] = $_SWIFT->Database->Record['email'];
        }

        return $_emailList;
    }

    /**
     * Retrieve a list of emails based on a list of users
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return array The User Email List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveListOnUserIDList($_userIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return array();
        }

        $_emailList = array();
        $_SWIFT->Database->Query("SELECT DISTINCT(*) FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ") ORDER BY useremailid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['email'], $_emailList)) {
                $_emailList[] = $_SWIFT->Database->Record['email'];
            }
        }

        return $_emailList;
    }

    /**
     * Retrieve a list of emails based on a list of users
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @return array The User Email List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveListOnUserIDListOnSharedOrg($_userIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return array();
        }

        $_emailList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ") ORDER BY useremailid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['email'], $_emailList)) {
                $_emailList[] = $_SWIFT->Database->Record['email'];
            }
        }

        return $_emailList;
    }

    /**
     * Retrieve User Email ID from a User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return mixed "useremailid" (INT) on Success, "false" otherwise
     */
    public static function RetrieveUserEmailID($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userEmailContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . self::LINKTYPE_USER . "' AND linktypeid = '" . $_userID . "'");
        if (isset($_userEmailContainer['useremailid']) && !empty($_userEmailContainer['useremailid']))
        {
            return $_userEmailContainer['useremailid'];
        }

        return false;
    }

    /**
     * Retrieve primary email for the given user id
     *
     * @author Bishwanath Jha
     *
     * @param int  $_userID The User ID
     * @param bool $_tagID  (OPTIONAL) Whether to tag the useremailid
     *
     * @throws SWIFT_Exception If no userid is provided
     * @return mixed "_email" (STRING) on Success, "false" otherwise
     */
    public static function GetPrimaryEmail($_userID, $_tagID = false)
    {

        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_emailList = SWIFT_UserEmail::RetrieveList($_userID, $_tagID);

        if (empty($_emailList)) {
            return '';
        }

        return $_emailList[0];
    }

    /**
     * Update user id with new user id
     *
     * @author   Rajat Garg
     *
     * @param int $_userID
     * @param int $_userIDReplaceWith
     *
     * @return bool
     */
    public static function UpdateUserID($_userID, $_userIDReplaceWith)
    {
        $_SWIFT = SWIFT::GetInstance();
        $_userEmailContainer = array();
        $_SWIFT->Database->Query("SELECT useremailid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                  WHERE linktype = '" . self::LINKTYPE_USER . "'
                                    AND linktypeid = " . $_userID);
        while ($_SWIFT->Database->NextRecord()) {
            $_userEmailContainer[$_SWIFT->Database->Record['useremailid']] = $_SWIFT->Database->Record;
        }
        foreach ($_userEmailContainer as $_userEmail) {
            $_UserEmail = new SWIFT_UserEmail($_userEmail['useremailid']);
            $_UserEmail->UpdateUser($_userIDReplaceWith, self::LINKTYPE_USER);
        }
        self::$_emailCache = array();
        return true;
    }

    /**
     * @author Abhishek Mittal
     *
     * @param int $_userID
     * @param int $_userType
     *
     * @return SWIFT_UserEmail
     * @throws SWIFT_Exception If the Class is not Loaded OR If Invalid Data is Provided
     */
    public function UpdateUser($_userID, $_userType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }
        if (!self::IsValidLinkType($_userType) || empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $this->UpdatePool('linktypeid', $_userID);
        $this->UpdatePool('linktype', $_userType);
        $this->ProcessUpdatePool();
        return $this;
    }

    /**
     * Set user email address as primary
     *
     * @author Abhishek Mittal
     *
     * @param int  $_userID
     * @param bool $_isPrimary
     *
     * @return bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateIsPrimary($_userID, $_isPrimary = false)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $_primaryEmailCount = 0;
        if ($_isPrimary) {
            $_primaryEmailList  = $_SWIFT->Database->QueryFetch("SELECT count(useremailid) AS total FROM " . TABLE_PREFIX . "useremails
                                                                 WHERE linktype = '" . self::LINKTYPE_USER . "'
                                                                   AND linktypeid=" . $_userID . "
                                                                   AND isprimary = 1");
            $_primaryEmailCount = $_primaryEmailList['total'];
        }
        if ($_primaryEmailCount == 0 || $_isPrimary === false) {
            $_limit = '';
            if ($_isPrimary) {
                $_limit = " LIMIT 1";
            }
            $_SWIFT->Database->Query("UPDATE " . TABLE_PREFIX . "useremails SET isprimary = " . (int) ($_isPrimary) . " WHERE linktype = '" . self::LINKTYPE_USER . "' AND linktypeid=" . $_userID . $_limit);
        }
        return true;
    }

    /**
     * Retrieve only primary email addresses based on user email list
     *
     * @author Abhishek Mittal
     *
     * @param array $_userEmailList
     *
     * @return bool|array
     */
    public static function RetrievePrimaryEmailOnUserEmail($_userEmailList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_userEmailList)) {
            return false;
        }
        $_primaryEmailList = array();
        $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "useremails
                                  WHERE linktype = '" . self::LINKTYPE_USER . "'
                                    AND email IN (" . BuildIN($_userEmailList) . ")
                                    AND isprimary = 1");
        while ($_SWIFT->Database->NextRecord()) {
            $_primaryEmailList[] = $_SWIFT->Database->Record['email'];
        }
        if (!count($_primaryEmailList)) {
            return false;
        }
        return $_primaryEmailList;
    }
}
?>
