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

use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Models\Staff\SWIFT_Staff;

/**
 * The SWIFT Error Log Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_ErrorLog extends SWIFT_Model
{
    const TABLE_NAME        =    'errorlogs';
    const PRIMARY_KEY        =    'errorlogid';

    const TABLE_STRUCTURE    =    "errorlogid I PRIMARY AUTO NOTNULL,
                                type I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                errordetails X,
                                userdata X";

    const INDEX_1            =    'type';
    const INDEX_2            =    'dateline';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_DATABASE = 1;
    const TYPE_PHPERROR = 2;
    const TYPE_EXCEPTION = 3;
    const TYPE_MAILERROR = 4;
    const TYPE_LOGINSHARE = 5;
    const TYPE_GENERAL = 6;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_errorLogID The Error Log ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_errorLogID)
    {
        parent::__construct();

        if (!$this->LoadData($_errorLogID)) {
            throw new SWIFT_ErrorLog_Exception('Failed to load Error Log ID: ' . ($_errorLogID));

            $this->SetIsClassLoaded(false);
        }
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'errorlogs', $this->GetUpdatePool(), 'UPDATE', "errorlogid = '" . ($this->GetErrorLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Error Log ID
     *
     * @author Varun Shoor
     * @return mixed "errorlogid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetErrorLogID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_ErrorLog_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['errorlogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_errorLogID The Error Log ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_errorLogID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "errorlogs WHERE errorlogid = '" . ($_errorLogID) . "'");
        if (isset($_dataStore['errorlogid']) && !empty($_dataStore['errorlogid']))
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_ErrorLog_Exception(SWIFT_CLASSNOTLOADED);
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_ErrorLog_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_ErrorLog_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if it is a valid error type
     *
     * @author Varun Shoor
     * @param mixed $_errorType The Error Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_errorType)
    {
        if ($_errorType == self::TYPE_DATABASE || $_errorType == self::TYPE_PHPERROR || $_errorType == self::TYPE_EXCEPTION || $_errorType == self::TYPE_MAILERROR ||
                $_errorType == self::TYPE_LOGINSHARE || $_errorType == self::TYPE_GENERAL)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Error Log
     *
     * @author Varun Shoor
     * @param mixed $_errorType The Error Type
     * @param string $_errorDetails The Error Details
     * @param string $_userData The User Environment Data
     * @return mixed "_errorLogID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_errorType, $_errorDetails, $_userData = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT->Interface instanceof SWIFT_Interface || !$_SWIFT->Interface->GetIsClassLoaded() || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP) {
            return false;
        }

        if (empty($_errorType) || empty($_errorDetails) || !self::IsValidType($_errorType))
        {
            throw new SWIFT_ErrorLog_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'errorlogs', array('type' => ($_errorType), 'dateline' => DATENOW, 'errordetails' => $_errorDetails, 'userdata' => $_userData), 'INSERT', false, true, true);
        $_errorLogID = $_SWIFT->Database->Insert_ID();

        if (!$_errorLogID)
        {
            return false;
        }

        return $_errorLogID;
    }

    /**
     * Delete the Error record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_ErrorLog_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetErrorLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Error Log's
     *
     * @author Varun Shoor
     * @param array $_errorLogIDList The Error Log ID List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_errorLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_errorLogIDList))
        {
            return false;
        }

        $_finalErrorLogIDList = array();

        $_SWIFT->Database->Query("SELECT errorlogid FROM " . TABLE_PREFIX . "errorlogs WHERE errorlogid IN (" . BuildIN($_errorLogIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalErrorLogIDList[] = ($_SWIFT->Database->Record['errorlogid']);
        }

        if (!count($_finalErrorLogIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "errorlogs WHERE errorlogid IN (" . BuildIN($_finalErrorLogIDList) . ")");

        return true;
    }

    /**
     * Retrieve the Error Label
     *
     * @author Varun Shoor
     * @param mixed $_errorType The Error Type
     * @return mixed The Error Label (STRING) or false in case of failure
     */
    public static function GetErrorLabel($_errorType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_errorType == self::TYPE_DATABASE)
        {
            return $_SWIFT->Language->Get('error_database');
        } else if ($_errorType == self::TYPE_PHPERROR) {
            return $_SWIFT->Language->Get('error_php');
        } else if ($_errorType == self::TYPE_EXCEPTION) {
            return $_SWIFT->Language->Get('error_exception');
        } else if ($_errorType == self::TYPE_MAILERROR) {
            return $_SWIFT->Language->Get('error_mail');
        } else if ($_errorType == self::TYPE_GENERAL) {
            return $_SWIFT->Language->Get('error_general');
        } else if ($_errorType == self::TYPE_LOGINSHARE) {
            return $_SWIFT->Language->Get('error_loginshare');
        }

        return false;
    }

    /**
     * Displays Error Log information if available
     *
     * @author Varun Shoor
     * @return mixed "_errorContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Class is not Loaded
     */
    public static function GetDashboardContainer() {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_StaffObject = $_SWIFT->Staff;
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_timeLine = $_SWIFT_StaffObject->GetProperty('lastvisit');

        $_index = 1;

        $_errorContainer = array();
        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "errorlogs WHERE dateline > '" . ($_timeLine) . "'");
        $_totalRecordCount = 0;
        if (isset($_countContainer['totalitems']))
        {
            $_totalRecordCount = ($_countContainer['totalitems']);
        }

        $_finalText = '';
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "errorlogs WHERE dateline > '" . ($_timeLine) . "' ORDER BY dateline DESC", 7);
        while ($_SWIFT->Database->NextRecord()) {

            $_errorTitle = self::GetErrorLabel($_SWIFT->Database->Record['type']);

            $_errorContainer[] = array('title' => $_errorTitle, 'date' => SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT->Database->Record['dateline']) . ' (' . SWIFT_Date::ColorTime(DATENOW-$_SWIFT->Database->Record['dateline']) . ')', 'contents' => htmlspecialchars($_SWIFT->Database->Record['errordetails']) . IIF(!empty($_SWIFT->Database->Record['userdata']), '<BR /><BR />' . htmlspecialchars($_SWIFT->Database->Record['userdata'])));

            $_index++;
        }

        return array($_totalRecordCount, $_errorContainer);
    }

    /**
     * Cleanup all old logs according to settings
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (86400 * $_SWIFT->Settings->Get('cpu_logcleardays'));

        $_errorLogIDList = array();

        $_SWIFT->Database->Query("SELECT errorlogid FROM " . TABLE_PREFIX . "errorlogs WHERE dateline < '" . ($_dateThreshold) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_errorLogIDList[] = ($_SWIFT->Database->Record['errorlogid']);
        }

        if (!count($_errorLogIDList))
        {
            return false;
        }

        self::DeleteList($_errorLogIDList);

        return true;
    }
}
