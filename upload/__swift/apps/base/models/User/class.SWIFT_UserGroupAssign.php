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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
/**
 * User Group Assignment Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserGroupAssign extends SWIFT_Model {
    const TABLE_NAME        =    'usergroupassigns';
    const PRIMARY_KEY        =    'usergroupassignid';

    const TABLE_STRUCTURE    =    "usergroupassignid I PRIMARY AUTO NOTNULL,
                                toassignid I DEFAULT '0' NOTNULL,
                                type I2 DEFAULT '0' NOTNULL,
                                usergroupid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'usergroupid, type';
    const INDEX_2            =    'toassignid, type, usergroupid';


    // Core Constants
    const TYPE_DEPARTMENT = 1;
    const TYPE_TICKETPRIORITY = 2;
    const TYPE_WIDGET = 3;
    const TYPE_RATING = 4;
    const TYPE_TICKETTYPE = 5;
    const TYPE_NEWS = 6;
    const TYPE_KBCATEGORY = 7;
    const TYPE_TROUBLESHOOTERCATEGORY = 9;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Insert a new User Group Assignment Record
     *
     * @author Varun Shoor
     * @param int $_toAssignID The To Assignment ID
     * @param int $_type The User Group Assignment Type
     * @param int $_userGroupID The User Group ID
     * @param bool $_rebuildCache Whether the rebuild cache should be done automatically
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Insert($_toAssignID, $_type, $_userGroupID, $_rebuildCache = true) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_type) || empty($_toAssignID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX .'usergroupassigns', array('toassignid' => $_toAssignID, 'type' => $_type, 'usergroupid' => $_userGroupID), 'INSERT');

        if ($_rebuildCache)
        {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Checks to see if it is a valid user group assignment type
     *
     * @author Varun Shoor
     * @param int $_type The Assignment Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_type) {
        if ($_type == self::TYPE_DEPARTMENT || $_type == self::TYPE_TICKETPRIORITY || $_type == self::TYPE_WIDGET ||
                $_type == self::TYPE_RATING || $_type == self::TYPE_TICKETTYPE || $_type == self::TYPE_NEWS || $_type == self::TYPE_KBCATEGORY ||
                $_type == self::TYPE_TROUBLESHOOTERCATEGORY) {
            return true;
        }

        return false;
    }

    /**
     * Deletes the User Group Assignment List
     *
     * @author Varun Shoor
     * @param array $_toAssignIDList The ID Container List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteList($_toAssignIDList) {
        if (func_num_args() < 2) {
            throw new \Exception('Missing parameters');
        }

        $_type = func_get_arg(1);
        $_rebuildCache = func_num_args() > 2 ? func_get_arg(2) : true;

        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_type)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."usergroupassigns WHERE toassignid IN (". BuildIN($_toAssignIDList) .") AND type = '". (int) ($_type) ."'");

        if ($_rebuildCache)
        {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Deletes the User Group Assignment List
     *
     * @author Rajat Garg
     *
     * @param array $_toAssignIDList
     * @param bool  $_rebuildCache (OPTIONAL)
     *
     * @return bool
     */
    public static function DeleteOnUser($_toAssignIDList, $_rebuildCache = true)
    {
        $_SWIFT = SWIFT::GetInstance();
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usergroupassigns WHERE toassignid IN (" . BuildIN($_toAssignIDList) . ")");
        if ($_rebuildCache) {
            self::RebuildCache();
        }
        return true;
    }

    /**
     * Delete Based on User Group ID List
     *
     * @author Varun Shoor
     * @param array $_userGroupIDList The User Group ID List
     * @param bool $_rebuildCache Whether the rebuild cache should be done automatically
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteListUserGroupID($_userGroupIDList, $_rebuildCache = true) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userGroupIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."usergroupassigns WHERE usergroupid IN (". BuildIN($_userGroupIDList) .")");

        if ($_rebuildCache)
        {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Rebuilds the User Group Assignment Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache() {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."usergroupassigns", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_cache[$_SWIFT->Database->Record3['type']][$_SWIFT->Database->Record3['usergroupid']][] = $_SWIFT->Database->Record3['toassignid'];
        }

        $_SWIFT->Cache->Update('usergroupassigncache', $_cache);

        return true;
    }

    /**
     * Retrieve the User Group List
     *
     * @author Varun Shoor
     * @param int $_toAssignID The ID to which user group list is assigned to
     * @param int $_type The Assign Type
     * @return mixed "userGroupIDList" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_toAssignID, $_type) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_type) || empty($_toAssignID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userGroupIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."usergroupassigns WHERE toassignid = '". $_toAssignID ."' AND type = '". $_type ."'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userGroupIDList[$_SWIFT->Database->Record['usergroupid']] = '1';
        }

        return $_userGroupIDList;
    }

    /**
     * Retrieve a list of User Group ID's linked to a given type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_toAssignID The Link'ed Object's ID
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveList($_linkType, $_toAssignID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_toAssignID) || !self::IsValidType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userGroupIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroupassigns WHERE toassignid = '" . $_toAssignID . "' AND type = '" . (int) ($_linkType) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userGroupIDList[] = $_SWIFT->Database->Record['usergroupid'];
        }

        return $_userGroupIDList;
    }

    /**
     * Retrieve a map of User Group ID's linked to a given type and specified ids
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_toAssignIDList The Link'ed Object's IDs
     * @return mixed "_userGroupIDMap" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveMap($_linkType, $_toAssignIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_toAssignIDList)) {
            return false;
        }

        $_userGroupIDMap = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroupassigns WHERE toassignid IN (" . BuildIN($_toAssignIDList) .
                ") AND type = '" . (int) ($_linkType) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userGroupIDMap[$_SWIFT->Database->Record['toassignid']][] = $_SWIFT->Database->Record['usergroupid'];
        }

        return $_userGroupIDMap;
    }

    /**
     * Retrieve a list of assigned ids based on a user group id
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @param mixed $_linkType The Link Type
     * @return array The Assign List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveListOnUserGroup($_userGroupID, $_linkType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userGroupID) || !self::IsValidType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userGroupAssignCache = $_SWIFT->Cache->Get('usergroupassigncache');

        // No type container?
        if (!isset($_userGroupAssignCache[$_linkType]) ||
                !isset($_userGroupAssignCache[$_linkType][$_userGroupID]) ||
                !_is_array($_userGroupAssignCache[$_linkType][$_userGroupID]))
        {
            return array();
        }

        return $_userGroupAssignCache[$_linkType][$_userGroupID];
    }

    /**
     * Check to see if the item is linked to the given user group..
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_toAssignID The Link'ed Object's ID
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsItemLinkedToUserGroup($_linkType, $_toAssignID, $_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_toAssignID) || !self::IsValidType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userGroupAssignCache = $_SWIFT->Cache->Get('usergroupassigncache');

        // No type container?
        if (!isset($_userGroupAssignCache[$_linkType]) || !isset($_userGroupAssignCache[$_linkType][$_userGroupID]) || !_is_array($_userGroupAssignCache[$_linkType][$_userGroupID]))
        {
            return false;
        }

        // Is this item linked to the user group?
        if (in_array($_toAssignID, $_userGroupAssignCache[$_linkType][$_userGroupID]))
        {
            return true;
        }

        return false;
    }
}
