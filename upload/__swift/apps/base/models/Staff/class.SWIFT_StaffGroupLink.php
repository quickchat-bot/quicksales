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

namespace Base\Models\Staff;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\Staff\SWIFT_Staff_Exception;

/**
 * The Staff Group Link Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_StaffGroupLink extends SWIFT_Model
{
    const TABLE_NAME = 'staffgrouplinks';
    const PRIMARY_KEY = 'staffgrouplinkid';

    const TABLE_STRUCTURE = "staffgrouplinkid I PRIMARY AUTO NOTNULL,
                                toassignid I DEFAULT '0' NOTNULL,
                                type I2 DEFAULT '0' NOTNULL,
                                staffgroupid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'staffgroupid, type';
    const INDEX_2 = 'toassignid, type, staffgroupid';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_TICKETSTATUS = 1;
    const TYPE_RATING = 2;
    const TYPE_WORKFLOW = 3;
    const TYPE_NEWS = 4;
    const TYPE_KBCATEGORY = 5;
    const TYPE_TROUBLESHOOTERCATEGORY = 7;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_staffGroupLinkID The Staff Group Link ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class could not be Loaded
     */
    public function __construct($_staffGroupLinkID)
    {
        parent::__construct();

        if (!$this->LoadData($_staffGroupLinkID)) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'staffgrouplinks', $this->GetUpdatePool(), 'UPDATE', "staffgrouplinkid = '" . (int)($this->GetStaffGroupLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Staff Group Link ID
     *
     * @author Varun Shoor
     * @return mixed "staffgrouplinkid" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetStaffGroupLinkID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['staffgrouplinkid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_staffGroupLinkID The Staff Group Link ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_staffGroupLinkID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffgrouplinks WHERE staffgrouplinkid = '" . $_staffGroupLinkID . "'");
        if (isset($_dataStore['staffgrouplinkid']) && !empty($_dataStore['staffgrouplinkid'])) {
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
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid link type
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidLinkType($_linkType)
    {
        if ($_linkType == self::TYPE_TICKETSTATUS || $_linkType == self::TYPE_RATING || $_linkType == self::TYPE_WORKFLOW || $_linkType == self::TYPE_NEWS || $_linkType == self::TYPE_KBCATEGORY ||
            $_linkType == self::TYPE_TROUBLESHOOTERCATEGORY) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve a list of Staff Group ID's linked to a given type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_toAssignID The Link'ed Object's ID
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveList($_linkType, $_toAssignID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_toAssignID) || !self::IsValidLinkType($_linkType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_staffGroupIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgrouplinks WHERE toassignid = '" . $_toAssignID . "' AND type = '" . (int)($_linkType) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffGroupIDList[] = $_SWIFT->Database->Record['staffgroupid'];
        }

        return $_staffGroupIDList;
    }

    /**
     * Retrieve a list of Staff Group ID's linked to a given type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_toAssignID The Link'ed Object's ID
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveListFromCache($_linkType, $_toAssignID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffGroupLinkCache = $_SWIFT->Cache->Get('staffgrouplinkcache');

        if (empty($_toAssignID) || !self::IsValidLinkType($_linkType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_staffGroupIDList = array();

        if (isset($_staffGroupLinkCache[$_linkType][$_toAssignID])) {
            $_staffGroupIDList = $_staffGroupLinkCache[$_linkType][$_toAssignID];
        }

        return $_staffGroupIDList;
    }

    /**
     * Retrieve a list of To Assign IDs ID's linked to a given staff group
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_staffGroupID The Staff Group ID
     * @return mixed "_toAssignIDIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function RetrieveListFromCacheOnStaffGroup($_linkType, $_staffGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffGroupLinkCache = $_SWIFT->Cache->Get('staffgrouplinkcache');

        if (empty($_staffGroupID) || !self::IsValidLinkType($_linkType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_toAssignIDList = array();

        if (isset($_staffGroupLinkCache[$_linkType])) {
            foreach ($_staffGroupLinkCache[$_linkType] as $_toAssignID => $_staffGroupIDList) {
                if (in_array($_staffGroupID, $_staffGroupIDList)) {
                    $_toAssignIDList[] = $_toAssignID;
                }
            }
        }

        return $_toAssignIDList;
    }

    /**
     * Retrieve a map of Staff Group ID's linked to a given status and specified ids
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_toAssignIDList The Link'ed Object's IDs
     * @return mixed "_staffGroupIDMap" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveMap($_linkType, $_toAssignIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidLinkType($_linkType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_toAssignIDList)) {
            return false;
        }

        $_staffGroupIDMap = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgrouplinks WHERE toassignid IN (" . BuildIN($_toAssignIDList) .
            ") AND type = '" . (int)($_linkType) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffGroupIDMap[$_SWIFT->Database->Record['toassignid']][] = $_SWIFT->Database->Record['staffgroupid'];
        }

        return $_staffGroupIDMap;
    }

    /**
     * Create a new Staff Group Link
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @param mixed $_linkType The Link Type
     * @param int $_toAssignID The ID to Link With
     * @param bool $_rebuildCache Whether to Rebuild the Cache in the end
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_staffGroupID, $_linkType, $_toAssignID, $_rebuildCache = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_staffGroupID) || !self::IsValidLinkType($_linkType) || empty($_toAssignID) || !SWIFT_StaffGroup::IsValidStaffGroupID($_staffGroupID)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staffgrouplinks', array('toassignid' => $_toAssignID, 'type' => (int)($_linkType), 'staffgroupid' => $_staffGroupID), 'INSERT');
        if (!$_queryResult) {
            throw new SWIFT_Staff_Exception(SWIFT_CREATEFAILED);
        }

        if ($_rebuildCache) {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Rebuild the Staff Group Link Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgrouplinks", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_cache[$_SWIFT->Database->Record3['type']][$_SWIFT->Database->Record3['toassignid']][] = $_SWIFT->Database->Record3['staffgroupid'];
        }

        $_SWIFT->Cache->Update('staffgrouplinkcache', $_cache);

        return true;
    }

    /**
     * Delete the Records based on Link
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type
     * @param int $_toAssignID The Linked ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function DeleteOnLink($_linkType, $_toAssignID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_toAssignID) || !self::IsValidLinkType($_linkType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgrouplinks WHERE toassignid = '" . $_toAssignID . "' AND type = '" . $_linkType . "'");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Records based on Link
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type
     * @param array $_toAssignIDList The Linked ID Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function DeleteOnLinkList($_linkType, $_toAssignIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidLinkType($_linkType) || !_is_array($_toAssignIDList)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgrouplinks WHERE toassignid IN (" . BuildIN($_toAssignIDList) . ") AND type = '" . $_linkType . "'");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Records based on Staff Group ID
     *
     * @author Varun Shoor
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnStaffGroupList($_staffGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffGroupIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgrouplinks WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");

        self::RebuildCache();

        return true;
    }
}

?>
