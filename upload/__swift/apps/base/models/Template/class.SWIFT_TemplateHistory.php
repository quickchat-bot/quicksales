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

namespace Base\Models\Template;

use SWIFT;
use SWIFT_Model;
use Base\Library\Template\SWIFT_Template_Exception;

/**
 * The Template History Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TemplateHistory extends SWIFT_Model
{
    const TABLE_NAME = 'templatehistory';
    const PRIMARY_KEY = 'templatehistoryid';

    const TABLE_STRUCTURE = "templatehistoryid I PRIMARY AUTO NOTNULL,
                                templateid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                changelognotes C(255) DEFAULT '' NOTNULL,
                                templatelength I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                templateversion C(20) DEFAULT '1.00.00' NOTNULL,
                                contents XL,
                                contentshash C(32) DEFAULT '' NOTNULL";

    const INDEX_1 = 'templateid';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_templateHistoryID The Template History ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Record could not be loaded
     */
    public function __construct($_templateHistoryID)
    {
        parent::__construct();

        if (!$this->LoadData($_templateHistoryID)) {
            throw new SWIFT_Template_Exception('Failed to load Template History ID: ' . $_templateHistoryID);
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
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'templatehistory', $this->GetUpdatePool(), 'UPDATE', "templatehistoryid = '" . (int)($this->GetTemplateHistoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Template History ID
     *
     * @author Varun Shoor
     * @return mixed "templatehistoryid" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetTemplateHistoryID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['templatehistoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_templateHistoryID The Template History ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_templateHistoryID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templatehistory WHERE templatehistoryid = '" . $_templateHistoryID . "'");
        if (isset($_dataStore['templatehistoryid']) && !empty($_dataStore['templatehistoryid'])) {
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
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Template History Record
     *
     * @author Varun Shoor
     * @param SWIFT_Template $_SWIFT_TemplateObject The SWIFT_Template Pointer
     * @param string $_contents The New Updated Contents
     * @param string $_changeLogNotes (OPTIONAL) The Changelog Notes
     * @param int $_staffID (OPTIONAL) The Staff ID
     * @return mixed "templateHistoryID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Template $_SWIFT_TemplateObject, $_contents, $_changeLogNotes = '', $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        $_templateContents = trim($_SWIFT_TemplateObject->GetContents());

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'templatehistory', array('templateid' => (int)($_SWIFT_TemplateObject->GetTemplateID()),
            'templatelength' => strlen($_templateContents), 'dateline' => DATENOW, 'templateversion' => $_SWIFT_TemplateObject->GetVersion(),
            'contents' => $_templateContents, 'contentshash' => md5($_templateContents), 'staffid' => $_staffID,
            'changelognotes' => ReturnNone($_changeLogNotes)), 'INSERT');
        $_templateHistoryID = $_SWIFT->Database->Insert_ID();

        if (!$_templateHistoryID) {
            throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
        }

        return $_templateHistoryID;
    }

    /**
     * Delete the Template History record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTemplateHistoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Template History ID's
     *
     * @author Varun Shoor
     * @param array $_templateHistoryIDList The Template History ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_templateHistoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateHistoryIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "templatehistory WHERE templatehistoryid IN (" . BuildIN($_templateHistoryIDList) . ")");

        return true;
    }

    /**
     * Delete the history records based on template ids
     *
     * @author Varun Shoor
     * @param array $_templateIDList The Template ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTemplate($_templateIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateIDList)) {
            return false;
        }

        $_templateHistoryIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templatehistory WHERE templateid IN (" . BuildIN($_templateIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateHistoryIDList[] = $_SWIFT->Database->Record['templatehistoryid'];
        }

        if (!count($_templateHistoryIDList)) {
            return false;
        }

        self::DeleteList($_templateHistoryIDList);

        return true;
    }

    /**
     * Retrieve the List of Histories based on Template
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @return mixed "_templateHistoryContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveListOnTemplate($_templateID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_templateHistoryContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templatehistory WHERE templateid = '" . $_templateID . "' ORDER BY templatehistoryid DESC");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateHistoryContainer[$_SWIFT->Database->Record['templatehistoryid']] = $_SWIFT->Database->Record;
        }

        return $_templateHistoryContainer;
    }
}

?>
