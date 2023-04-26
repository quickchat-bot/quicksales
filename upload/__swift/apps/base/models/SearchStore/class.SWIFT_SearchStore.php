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

namespace Base\Models\SearchStore;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\SearchStore\SWIFT_SearchStoreData;

/**
 * The Search Store Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_SearchStore extends SWIFT_Model
{
    const TABLE_NAME = 'searchstores';
    const PRIMARY_KEY = 'searchstoreid';

    const TABLE_STRUCTURE = "searchstoreid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                sessionid C(255) DEFAULT '' NOTNULL,
                                lastupdate I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                storetype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'sessionid';
    const INDEX_2 = 'storetype, staffid';
    const INDEX_3 = 'storetype, userid';


    protected $_dataStore = array();

    private $_searchStoreDataIDList = array();

    // Core Constants
    const TYPE_USERS = 1;
    const TYPE_CHATS = 2;
    const TYPE_CANNEDRESPONSE = 3;
    const TYPE_CHATMESSAGE = 4;
    const TYPE_TICKETS = 5;
    const TYPE_MACROREPLY = 6;
    const TYPE_USERORGANIZATIONS = 7;
    const TYPE_CALLS = 8;
    const TYPE_NEWS = 9;
    const TYPE_KBARTICLE = 10;
    const TYPE_TROUBLESHOOTER = 11;
    const TYPE_REPORTS = 12;
    const TYPE_NEWSCATEGORIES = 13;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_searchStoreID)
    {
        parent::__construct();

        if (!$this->LoadData($_searchStoreID)) {
            throw new SWIFT_Exception('Failed to load the Search Store ID: ' . $_searchStoreID);
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'searchstores', $this->GetUpdatePool(), 'UPDATE', "searchstoreid = '" . (int)($this->GetSearchStoreID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Search Store ID
     *
     * @author Varun Shoor
     * @return mixed "searchstoreid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSearchStoreID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['searchstoreid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_searchStoreID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "searchstores WHERE searchstoreid = '" . $_searchStoreID . "'");
        if (isset($_dataStore['searchstoreid']) && !empty($_dataStore['searchstoreid'])) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid store type
     *
     * @author Varun Shoor
     * @param mixed $_storeType The Store Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidStoreType($_storeType)
    {
        if ($_storeType == self::TYPE_USERS || $_storeType == self::TYPE_CHATS || $_storeType == self::TYPE_CHATMESSAGE ||
            $_storeType == self::TYPE_CANNEDRESPONSE || $_storeType == self::TYPE_TICKETS || $_storeType == self::TYPE_MACROREPLY ||
            $_storeType == self::TYPE_USERORGANIZATIONS || $_storeType == self::TYPE_CALLS || $_storeType == self::TYPE_NEWS || $_storeType == self::TYPE_NEWSCATEGORIES || $_storeType == self::TYPE_KBARTICLE ||
            $_storeType == self::TYPE_TROUBLESHOOTER || $_storeType == self::TYPE_REPORTS) {
            return true;
        } else if (is_numeric($_storeType)) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Search Store
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID
     * @param mixed $_storeType The Search Store Type
     * @param array $_dataIDList The Data ID List of this Store
     * @param int $_staffID The Staff ID
     * @param int $_userID The User ID
     * @return int
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_sessionID, $_storeType, $_dataIDList, $_staffID = 0, $_userID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_sessionID) || !self::IsValidStoreType($_storeType) || !is_array($_dataIDList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'searchstores', array('sessionid' => $_sessionID, 'storetype' => (int)($_storeType), 'dateline' => DATENOW, 'lastupdate' => DATENOW, 'staffid' => $_staffID, 'userid' => $_userID), 'INSERT');
        $_searchStoreID = $_SWIFT->Database->Insert_ID();

        if (!$_searchStoreID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        foreach ($_dataIDList as $_key => $_val) {
            SWIFT_SearchStoreData::Create($_searchStoreID, $_val);
        }

        return $_searchStoreID;
    }

    /**
     * Delete the Search Store record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetSearchStoreID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Search Stores
     *
     * @author Varun Shoor
     * @param array $_searchStoreIDList The Search Store ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_searchStoreIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_searchStoreIDList)) {
            return false;
        }

        $_finalSearchStoreIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "searchstores WHERE searchstoreid IN (" . BuildIN($_searchStoreIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalSearchStoreIDList[] = $_SWIFT->Database->Record['searchstoreid'];
        }

        if (!count($_finalSearchStoreIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "searchstores WHERE searchstoreid IN (" . BuildIN($_finalSearchStoreIDList) . ")");

        SWIFT_SearchStoreData::DeleteOnSearchStore($_finalSearchStoreIDList);

        return true;
    }

    /**
     * Verify the Data
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID
     * @param mixed $_storeType The Store Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Verify($_sessionID, $_storeType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidStoreType($_storeType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->GetProperty('sessionid') != $_sessionID || $this->GetProperty('storetype') != $_storeType) {
            return false;
        }

        return true;
    }

    /**
     * Load the Search Store Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function LoadSearchStoreData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchStoreDataIDList = SWIFT_SearchStoreData::RetrieveOnSearchStore($this->GetSearchStoreID());
        if (!_is_array($_searchStoreDataIDList)) {
            $_searchStoreDataIDList = array(0);
        }

        $this->_searchStoreDataIDList = $_searchStoreDataIDList;

        return true;
    }

    /**
     * Retrieve the Search Store Data ID's
     *
     * @author Varun Shoor
     * @return mixed "_searchStoreDataIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSearchStoreData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_searchStoreDataIDList;
    }

    /**
     * Delete on Type and/or staff/userid
     *
     * @author Varun Shoor
     * @param mixed $_storeType The Store Type
     * @param int $_staffID The Staff ID
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnType($_storeType, $_staffID = null, $_userID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidStoreType($_storeType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_extendedSQL = '';

        if (!empty($_staffID)) {
            $_extendedSQL = " AND staffid = '" . $_staffID . "'";
        } else if (!empty($_userID)) {
            $_extendedSQL = " AND userid = '" . $_userID . "'";
        }

        $_finalSearchStoreIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "searchstores WHERE storetype = '" . (int)($_storeType) . "'" . $_extendedSQL);
        while ($_SWIFT->Database->NextRecord()) {
            $_finalSearchStoreIDList[] = $_SWIFT->Database->Record['searchstoreid'];
        }

        if (!count($_finalSearchStoreIDList)) {
            return false;
        }

        self::DeleteList($_finalSearchStoreIDList);

        return false;
    }
}

?>
