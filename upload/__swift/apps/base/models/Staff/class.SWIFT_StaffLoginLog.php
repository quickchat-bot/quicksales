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

namespace Base\Models\Staff;

use SWIFT;
use SWIFT_Date;
use SWIFT_Interface;
use SWIFT_Model;
use SWIFT_Session;
use Base\Library\Staff\SWIFT_Staff_Exception;

/**
 * The Staff Login Log Class
 *
 * @author Varun Shoor
 */
class SWIFT_StaffLoginLog extends SWIFT_Model
{
    const TABLE_NAME = 'staffloginlog';
    const PRIMARY_KEY = 'staffloginlogid';

    const TABLE_STRUCTURE = "staffloginlogid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                logindateline I DEFAULT '0' NOTNULL,
                                activitydateline I DEFAULT '0' NOTNULL,
                                logoutdateline I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                staffusername C(255) DEFAULT '' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                forwardedipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                useragent C(255) DEFAULT '' NOTNULL,
                                sessionid C(255) DEFAULT '' NOTNULL,
                                logouttype I2 DEFAULT '0' NOTNULL,
                                loginresult I2 DEFAULT '0' NOTNULL,
                                interfacetype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'staffid, logindateline, interfacetype';
    const INDEX_2 = 'staffusername, logindateline, loginresult';
    const INDEX_3 = 'logindateline, loginresult';
    const INDEX_4 = 'sessionid';


    protected $_dataStore = array();

    // Default Properties
    const LOGIN_RETRIES = 3;
    const LOGIN_TIMELINE = 900; // In Seconds

    // Logout Types
    const LOGOUT_NORMAL = 1;
    const LOGOUT_FLUSH = 2;
    const LOGOUT_FORCED = 3;

    // Login Result Types
    const LOGIN_SUCCESS = 1;
    const LOGIN_FAILURE = 2;

    // Interface Types
    const INTERFACE_STAFF = 1;
    const INTERFACE_ADMIN = 2;
    const INTERFACE_PDA = 3;
    const INTERFACE_SYNCWORKS = 4;
    const INTERFACE_WINAPP = 5;
    const INTERFACE_INSTAALERT = 6;
    const INTERFACE_API = 7;
    const INTERFACE_RSS = 8;
    const INTERFACE_INTRANET = 9;
    const INTERFACE_MOBILE = 10;
    const INTERFACE_STAFFAPI = 11;
    const INTERFACE_TESTS = 12;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve the retries for login.. check settings, if it doesnt exist.. default to core value
     *
     * @author Varun Shoor
     * @return int "login retries" on Success, "false" otherwise
     */
    public static function GetLoginRetries()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_loginEntries = 0;
        if ($_SWIFT->Settings->Get('security_loginlocked') == '1') {
            $_loginEntries = (int)($_SWIFT->Settings->Get('security_loginattempts'));
        }

        if (!empty($_loginEntries)) {
            return $_loginEntries;
        }

        return self::LOGIN_RETRIES;
    }

    /**
     * Retrieve the timeline for login.. check settings, if it doesnt exist.. default to core value
     *
     * @author Varun Shoor
     * @return int "login timline" on Success, "false" otherwise
     */
    public static function GetLoginTimeline()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_loginTimeLine = 0;
        if ($_SWIFT->Settings->Get('security_loginlocked') == '1') {
            $_loginTimeLine = (int)($_SWIFT->Settings->Get('security_loginlockedtimeline'));
        }

        if (!empty($_loginTimeLine)) {
            return $_loginTimeLine;
        }

        return self::LOGIN_TIMELINE;
    }

    /**
     * Check to see if the staff can login (check for failed attempts in X timeline)
     *
     * @author Varun Shoor
     * @param string $_staffUserName The Staff User Name
     * @return array array(result, failedattempts) on Success, "false" otherwise
     */
    public static function CanStaffLogin($_staffUserName)
    {
        if (empty($_staffUserName)) {
            return array(false, 0);
        }

        $_SWIFT = SWIFT::GetInstance();
        if ($_SWIFT->Settings->Get('security_loginlocked') == '0') {
            return array(true, 0);
        }

        $_timeLine = DATENOW - self::GetLoginTimeline();
        $_loginRetries = self::GetLoginRetries();

        $_attemptCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staffloginlog WHERE staffusername = '" . $_SWIFT->Database->Escape($_staffUserName) . "' AND logindateline > '" . $_timeLine . "' AND loginresult = '" . self::LOGIN_FAILURE . "'");

        if ((int)($_attemptCount['totalitems']) > $_loginRetries) {
            return array(false, ((int)($_attemptCount['totalitems']) - 1));
        } else {
            return array(true, (int)($_attemptCount['totalitems']));
        }

        return array(true, 0);
    }

    /**
     * Retrieves the Login Log ID
     *
     * @author Varun Shoor
     * @return mixed "_staffLoginLogID" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetStaffLoginLogID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['staffloginlogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_staffLoginLogID The Staff Login Log ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_staffLoginLogID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffloginlog WHERE staffloginlogid = '" . $_staffLoginLogID . "'");
        if (isset($_dataStore['staffloginlogid']) && !empty($_dataStore['staffloginlogid'])) {
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
     * Checks to see if the interface type specified is valid
     *
     * @author Varun Shoor
     * @param int $_interfaceType The Login Interface Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidInterfaceType($_interfaceType)
    {
        if ($_interfaceType == self::INTERFACE_STAFF || $_interfaceType == self::INTERFACE_ADMIN || $_interfaceType == self::INTERFACE_PDA || $_interfaceType == self::INTERFACE_SYNCWORKS
            || $_interfaceType == self::INTERFACE_WINAPP || $_interfaceType == self::INTERFACE_INSTAALERT || $_interfaceType == self::INTERFACE_API
            || $_interfaceType == self::INTERFACE_RSS || $_interfaceType == self::INTERFACE_INTRANET || $_interfaceType == self::INTERFACE_MOBILE || $_interfaceType == self::INTERFACE_STAFFAPI
            || $_interfaceType == self::INTERFACE_TESTS) {
            return true;
        }

        return false;
    }

    /**
     * Insert a new staff login log id
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Full Name
     * @param string $_staffUserName The Staff User Name
     * @param int $_loginResult The Login Result
     * @param int $_interfaceType The Login Interface Type
     * @param string $_sessionID (OPTIONAL) The Relevant Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data Provided
     */
    public static function Insert($_staffID, $_staffName, $_staffUserName, $_loginResult, $_interfaceType, $_sessionID = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidInterfaceType($_interfaceType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_forwardedIPAddress = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $_forwardedIPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = '';
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staffloginlog', array('staffid' => $_staffID, 'logindateline' => DATENOW, 'activitydateline' => DATENOW, 'logoutdateline' => '0', 'staffname' => ReturnNone($_staffName), 'staffusername' => ReturnNone($_staffUserName), 'ipaddress' => ReturnNone($_SERVER['REMOTE_ADDR']), 'forwardedipaddress' => ReturnNone($_forwardedIPAddress), 'useragent' => ReturnNone($_SERVER['HTTP_USER_AGENT']), 'logouttype' => '0', 'loginresult' => $_loginResult, 'interfacetype' => $_interfaceType, 'sessionid' => $_sessionID), 'INSERT');
        $_staffLoginLogID = $_SWIFT->Database->Insert_ID();

        return $_staffLoginLogID;
    }

    /**
     * Logs a success full attempt at login
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT Staff Object
     * @param int $_interfaceType The Login Interface Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data Provided or If Class not Loaded
     */
    public static function Success($_SWIFT_StaffObject, $_interfaceType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidInterfaceType($_interfaceType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        } else if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sessionID = '';
        if ($_SWIFT->Session instanceof SWIFT_Session && $_SWIFT->Session->GetIsClassLoaded()) {
            $_sessionID = $_SWIFT->Session->GetSessionID();
        }

        return self::Insert($_SWIFT_StaffObject->GetStaffID(), $_SWIFT_StaffObject->GetProperty('fullname'), $_SWIFT_StaffObject->GetProperty('username'), self::LOGIN_SUCCESS, $_interfaceType, $_sessionID);
    }

    /**
     * Logs a failure at login
     *
     * @author Varun Shoor
     * @param string $_staffUserName The Staff User Name
     * @param int $_interfaceType The Login Interface Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data Provided
     */
    public static function Failure($_staffUserName, $_interfaceType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidInterfaceType($_interfaceType)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_staffContainer = SWIFT_Staff::RetrieveOnUsername($_staffUserName);

        $_staffID = 0;
        $_staffFullName = '';
        if (isset($_staffContainer['staffid'])) {
            $_staffID = (int)($_staffContainer['staffid']);
        }

        if (isset($_staffContainer['fullname'])) {
            $_staffFullName = $_staffContainer['fullname'];
        }

        return self::Insert($_staffID, $_staffFullName, $_staffUserName, self::LOGIN_FAILURE, $_interfaceType);
    }

    /**
     * Displays Last Login Failures information if available
     *
     * @author Varun Shoor
     * @return mixed "_failureContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Class is not Loaded
     */
    public static function GetDashboardContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_StaffObject = $_SWIFT->Staff;
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_timeLine = $_SWIFT_StaffObject->GetProperty('lastvisit');

        $_index = 1;

        $_failureContainer = array();
        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staffloginlog WHERE logindateline > '" . (int)($_timeLine) . "' AND loginresult = '" . self::LOGIN_FAILURE . "'");
        $_totalRecordCount = 0;
        if (isset($_countContainer['totalitems'])) {
            $_totalRecordCount = (int)($_countContainer['totalitems']);
        }

        $_finalText = '';
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "staffloginlog WHERE logindateline > '" . (int)($_timeLine) . "' AND loginresult = '" . self::LOGIN_FAILURE . "'", 7);
        while ($_SWIFT->Database->NextRecord()) {
            if (empty($_SWIFT->Database->Record['staffname'])) {
                $_titleText = htmlspecialchars($_SWIFT->Database->Record['staffusername']);
            } else {
                $_titleText = htmlspecialchars($_SWIFT->Database->Record['staffname']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['staffusername']) . ')';
            }

            if (empty($_SWIFT->Database->Record['forwardedipaddress'])) {
                $_ipText = '<b>' . $_SWIFT->Language->Get('dashipaddress') . ':</b> ' . htmlspecialchars($_SWIFT->Database->Record['ipaddress']);
            } else {
                $_ipText = '<b>' . $_SWIFT->Language->Get('dashipaddress') . ':</b> ' . htmlspecialchars($_SWIFT->Database->Record['ipaddress']) . '<BR /><b>' . $_SWIFT->Language->Get('dashforwardedipaddress') . '</b> ' . htmlspecialchars($_SWIFT->Database->Record['forwardedipaddress']);
            }

            $_interfaceTitle = SWIFT_Interface::GetInterfaceLabel($_SWIFT->Database->Record['interfacetype']);

            $_failureContainer[] = array('title' => $_titleText, 'date' => SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT->Database->Record['logindateline']) . ' (' . SWIFT_Date::ColorTime(DATENOW - $_SWIFT->Database->Record['logindateline']) . ')', 'contents' => '<b>' . $_SWIFT->Language->Get('dashinterface') . '</b> ' . $_interfaceTitle . '<BR /><b>' . $_SWIFT->Language->Get('dashuseragent') . '</b> ' . htmlspecialchars($_SWIFT->Database->Record['useragent']) . '<BR />' . $_ipText);

            $_index++;
        }

        return array($_totalRecordCount, $_failureContainer);
    }

    /**
     * Clear Staff Login logs after specified time.
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (86400 * $_SWIFT->Settings->Get('cpu_logcleardays'));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffloginlog WHERE logindateline < '" . (int)($_dateThreshold) . "'");

        return true;
    }
}

?>
