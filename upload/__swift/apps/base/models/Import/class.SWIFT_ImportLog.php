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

namespace Base\Models\Import;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Import Log Class
 *
 * @author Varun Shoor
 */
class SWIFT_ImportLog extends SWIFT_Model
{
    const TABLE_NAME = 'importlogs';
    const PRIMARY_KEY = 'importlogid';

    const TABLE_STRUCTURE = "importlogid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                description C(255) DEFAULT '' NOTNULL,
                                logtype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'logtype, dateline';
    const INDEX_2 = 'dateline';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_SUCCESS = 1;
    const TYPE_FAILURE = 2;
    const TYPE_WARNING = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Import Log Object');
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
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'importlogs', $this->GetUpdatePool(), 'UPDATE', "importlogid = '" . (int)($this->GetImportLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Import Log ID
     *
     * @author Varun Shoor
     * @return mixed "importlogid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetImportLogID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['importlogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "importlogs WHERE importlogid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['importlogid']) && !empty($_dataStore['importlogid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['importlogid']) || empty($this->_dataStore['importlogid'])) {
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
     * Check to see if its a valid log type
     *
     * @author Varun Shoor
     * @param mixed $_logType The Log Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidLogType($_logType)
    {
        if ($_logType == self::TYPE_SUCCESS || $_logType == self::TYPE_FAILURE || $_logType == self::TYPE_WARNING) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Import Log Entry
     *
     * @author Varun Shoor
     * @param string $_logMessage
     * @param mixed $_logType
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return int The Import Log ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_logMessage, $_logType, $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffID = 0;
        $_staffFullName = '';

        if (empty($_logMessage) || !self::IsValidLogType($_logType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
            $_staffFullName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'importlogs', array('staffid' => $_staffID, 'dateline' => DATENOW, 'staffname' => $_staffFullName,
            'ipaddress' => SWIFT::Get('IP'), 'description' => $_logMessage, 'logtype' => (int)($_logType)), 'INSERT');
        $_importLogID = $_SWIFT->Database->Insert_ID();
        if (!$_importLogID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_importLogID;
    }

    /**
     * Delete the Import Log record
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

        self::DeleteList(array($this->GetImportLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Import Logs
     *
     * @author Varun Shoor
     * @param array $_importLogIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_importLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_importLogIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "importlogs WHERE importlogid IN (" . BuildIN($_importLogIDList) . ")");

        return true;
    }
}

?>
