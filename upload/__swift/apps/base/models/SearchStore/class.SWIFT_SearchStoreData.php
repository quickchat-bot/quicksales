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

/**
 * The Search Store Data ID Manager
 *
 * @author Varun Shoor
 */
class SWIFT_SearchStoreData extends SWIFT_Model
{
    const TABLE_NAME = 'searchstoredata';
    const PRIMARY_KEY = 'searchstoredataid';

    const TABLE_STRUCTURE = "searchstoredataid I PRIMARY AUTO NOTNULL,
                                searchstoreid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                dataid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'searchstoreid';
    const INDEX_2 = 'dateline';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_searchStoreDataID The Search Store Data ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_searchStoreDataID)
    {
        parent::__construct();

        if (!$this->LoadData($_searchStoreDataID)) {
            throw new SWIFT_Exception('Failed to load Search Store Data ID: ' . $_searchStoreDataID);
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'searchstoredata', $this->GetUpdatePool(), 'UPDATE', "searchstoredataid = '" . (int)($this->GetSearchStoreDataID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Search Store Data ID
     *
     * @author Varun Shoor
     * @return mixed "searchstoredataid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSearchStoreDataID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['searchstoredataid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_searchStoreDataID The Search Store Data ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_searchStoreDataID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "searchstoredata WHERE searchstoredataid = '" . $_searchStoreDataID . "'");
        if (isset($_dataStore['searchstoredataid']) && !empty($_dataStore['searchstoredataid'])) {
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
     * Create a new Search Store Data Record
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @param int $_dataID The Data ID Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_searchStoreID, $_dataID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_searchStoreID) || empty($_dataID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'searchstoredata', array('searchstoreid' => $_searchStoreID, 'dataid' => $_dataID, 'dateline' => DATENOW), 'INSERT');

        return true;
    }

    /**
     * Delete the Search Store Data record
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

        self::DeleteList(array($this->GetSearchStoreDataID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Search Store Data ID's
     *
     * @author Varun Shoor
     * @param array $_searchStoreDataIDList The Search Store Data ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_searchStoreDataIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_searchStoreDataIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "searchstoredata WHERE searchstoredataid IN (" . BuildIN($_searchStoreDataIDList) . ")");

        return true;
    }

    /**
     * Delete the Data ID's on Search Store
     *
     * @author Varun Shoor
     * @param array $_searchStoreIDList The Search Store ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnSearchStore($_searchStoreIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_searchStoreIDList)) {
            return false;
        }

        $_finalSearchStoreDataIDList = array();
        $_SWIFT->Database->Query("SELECT searchstoredataid FROM " . TABLE_PREFIX . "searchstoredata WHERE searchstoreid IN (" . BuildIN($_searchStoreIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalSearchStoreDataIDList[] = $_SWIFT->Database->Record['searchstoredataid'];
        }

        if (!count($_finalSearchStoreDataIDList)) {
            return false;
        }

        self::DeleteList($_finalSearchStoreDataIDList);

        return true;
    }

    /**
     * Retrieve the data on search store
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @return mixed "_finalSearchStoreDataIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveOnSearchStore($_searchStoreID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_searchStoreID)) {
            return false;
        }

        $_finalSearchStoreDataIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "searchstoredata WHERE searchstoreid = '" . $_searchStoreID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalSearchStoreDataIDList[] = $_SWIFT->Database->Record['dataid'];
        }

        return $_finalSearchStoreDataIDList;
    }

    /**
     * Cleans up the search store data older than 7 days
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Cleanup()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (7 * 86400);

        $_searchStoreDataIDList = array();
        $_SWIFT->Database->Query("SELECT searchstoredataid FROM " . TABLE_PREFIX . "searchstoredata WHERE dateline <= '" . $_dateThreshold . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_searchStoreDataIDList[] = $_SWIFT->Database->Record['searchstoredataid'];
        }

        if (!count($_searchStoreDataIDList)) {
            return false;
        }

        self::DeleteList($_searchStoreDataIDList);

        return true;
    }
}

?>
