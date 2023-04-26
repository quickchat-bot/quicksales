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

namespace Troubleshooter\Models\Link;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Troubleshooter Link Model
 *
 * @author Varun Shoor
 */
class SWIFT_TroubleshooterLink extends SWIFT_Model {
    const TABLE_NAME        =    'troubleshooterlinks';
    const PRIMARY_KEY        =    'troubleshooterlinkid';

    const TABLE_STRUCTURE    =    "troubleshooterlinkid I PRIMARY AUTO NOTNULL,
                                troubleshootercategoryid I DEFAULT '0' NOTNULL,
                                parenttroubleshooterstepid I DEFAULT '0' NOTNULL,
                                childtroubleshooterstepid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'troubleshootercategoryid, parenttroubleshooterstepid, childtroubleshooterstepid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject) {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Troubleshooter Link Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __destruct() {
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
    public function ProcessUpdatePool() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshooterlinks', $this->GetUpdatePool(), 'UPDATE', "troubleshooterlinkid = '" . (int) ($this->GetTroubleshooterLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Troubleshooter Link ID
     *
     * @author Varun Shoor
     * @return mixed "troubleshooterlinkid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTroubleshooterLinkID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['troubleshooterlinkid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "troubleshooterlinks WHERE troubleshooterlinkid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['troubleshooterlinkid']) && !empty($_dataStore['troubleshooterlinkid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['troubleshooterlinkid']) || empty($this->_dataStore['troubleshooterlinkid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore() {
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
    public function GetProperty($_key) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Troubleshooter Link
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID
     * @param int $_parentTroubleshooterStepID
     * @param int $_childTroubleshooterStepID
     * @return int The Troubleshooter Link ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_troubleshooterCategoryID, $_parentTroubleshooterStepID, $_childTroubleshooterStepID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_troubleshooterCategoryID) || empty($_childTroubleshooterStepID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'troubleshooterlinks', array('troubleshootercategoryid' => $_troubleshooterCategoryID,
            'parenttroubleshooterstepid' => $_parentTroubleshooterStepID, 'childtroubleshooterstepid' => $_childTroubleshooterStepID
        ), 'INSERT');
        $_troubleshooterLinkID = $_SWIFT->Database->Insert_ID();

        if (!$_troubleshooterLinkID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_troubleshooterLinkID;
    }

    /**
     * Delete the Troubleshooter Link record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTroubleshooterLinkID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Troubleshooter Link IDs
     *
     * @author Varun Shoor
     * @param array $_troubleshooterLinkIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_troubleshooterLinkIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_troubleshooterLinkIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshooterlinks WHERE troubleshooterlinkid IN (" . BuildIN($_troubleshooterLinkIDList) . ")");

        return true;
    }

    /**
     * Delete on Troubleshooter Category ID List
     *
     * @author Varun Shoor
     * @param array $_troubleshooterCategoryIDList The Troubleshooter Category ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnTroubleshooterCategory($_troubleshooterCategoryIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_troubleshooterCategoryIDList)) {
            return false;
        }

        $_troubleshooterLinkIDList = array();
        $_SWIFT->Database->Query("SELECT troubleshooterlinkid FROM " . TABLE_PREFIX . "troubleshooterlinks WHERE troubleshootercategoryid IN (" . BuildIN($_troubleshooterCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_troubleshooterLinkIDList[] = $_SWIFT->Database->Record['troubleshooterlinkid'];
        }

        if (!_is_array($_troubleshooterLinkIDList)) {
            return false;
        }

        self::DeleteList($_troubleshooterLinkIDList);

        return true;
    }

    /**
     * Delete on Troubleshooter Step ID List
     *
     * @author Varun Shoor
     * @param array $_troubleshooterStepIDList The Troubleshooter Step ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnTroubleshooterStep($_troubleshooterStepIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_troubleshooterStepIDList)) {
            return false;
        }

        $_troubleshooterLinkIDList = array();
        $_SWIFT->Database->Query("SELECT troubleshooterlinkid FROM " . TABLE_PREFIX . "troubleshooterlinks
            WHERE parenttroubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ") OR childtroubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_troubleshooterLinkIDList[] = $_SWIFT->Database->Record['troubleshooterlinkid'];
        }

        if (!_is_array($_troubleshooterLinkIDList)) {
            return false;
        }

        self::DeleteList($_troubleshooterLinkIDList);

        return true;
    }

    /**
     * Delete on Troubleshooter Step ID List
     *
     * @author Varun Shoor
     * @param array $_troubleshooterStepIDList The Troubleshooter Step ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnChildTroubleshooterStep($_troubleshooterStepIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_troubleshooterStepIDList)) {
            return false;
        }

        $_troubleshooterLinkIDList = array();
        $_SWIFT->Database->Query("SELECT troubleshooterlinkid FROM " . TABLE_PREFIX . "troubleshooterlinks
            WHERE childtroubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_troubleshooterLinkIDList[] = $_SWIFT->Database->Record['troubleshooterlinkid'];
        }

        if (!_is_array($_troubleshooterLinkIDList)) {
            return false;
        }

        self::DeleteList($_troubleshooterLinkIDList);

        return true;
    }

    /**
     * Retrieve on Child Troubleshooter Step ID List
     *
     * @author Varun Shoor
     * @param array $_troubleshooterStepIDList The Troubleshooter Step ID List
     * @return bool|array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function RetrieveOnChild($_troubleshooterStepIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_troubleshooterStepIDList)) {
            return false;
        }

        $_parentTroubleshooterStepIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshooterlinks
            WHERE childtroubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_parentTroubleshooterStepIDList[] = $_SWIFT->Database->Record['parenttroubleshooterstepid'];
        }

        return $_parentTroubleshooterStepIDList;
    }
}
