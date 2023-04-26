<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Saloni Dhall
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Models\User;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use SWIFT_Session;

/**
 * The User Login Log Class
 *
 * @author Saloni Dhall
 */
class SWIFT_UserLoginLog extends SWIFT_Model
{
    const TABLE_NAME = 'userloginlog';
    const PRIMARY_KEY = 'userloginlogid';

    const TABLE_STRUCTURE = "userloginlogid I PRIMARY AUTO NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                logindateline I DEFAULT '0' NOTNULL,
                                activitydateline I DEFAULT '0' NOTNULL,
                                logoutdateline I DEFAULT '0' NOTNULL,
                                userfullname C(255) DEFAULT '' NOTNULL,
                                useremail C(255) DEFAULT '' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                forwardedipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                useragent C(255) DEFAULT '' NOTNULL,
                                sessionid C(255) DEFAULT '' NOTNULL,
                                logouttype I2 DEFAULT '0' NOTNULL,
                                loginresult I2 DEFAULT '0' NOTNULL,
                                interfacetype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'userid, logindateline, interfacetype';
    const INDEX_2 = 'userfullname, logindateline, loginresult';
    const INDEX_3 = 'logindateline, loginresult';
    const INDEX_4 = 'sessionid';


    protected $_dataStore = array();

    // Default Properties
    const LOGIN_RETRIES = 5;
    const LOGIN_TIMELINE = 600; // In Seconds

    // Logout Types
    const LOGOUT_NORMAL = 1;
    const LOGOUT_FLUSH = 2;
    const LOGOUT_FORCED = 3;

    // Login Result Types
    const LOGIN_SUCCESS = 1;
    const LOGIN_FAILURE = 2;

    // Interface Types
    const INTERFACE_CLIENT = 1;
    const INTERFACE_STAFF_AS_LOGIN = 2;
    const INTERFACE_LOGINSHARE = 3;

    /**
     * Constructor
     *
     * @author Saloni Dhall
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve the retries for login.. check settings, if it doesnt exist.. default to core value
     *
     * @author Saloni Dhall
     * @return int "login retries" on Success, "false" otherwise
     */
    public static function GetLoginRetries()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_loginEntries = 0;
        if ($_SWIFT->Settings->Get('security_scloginlocked') == '1') {
            $_loginEntries = (int)($_SWIFT->Settings->Get('security_scloginattempts'));
        }

        if (!empty($_loginEntries)) {
            return $_loginEntries;
        }

        return self::LOGIN_RETRIES;
    }

    /**
     * Retrieve the timeline for login.. check settings, if it doesnt exist.. default to core value
     *
     * @author Saloni Dhall
     * @return int "login timline"
     */
    public static function GetLoginTimeline()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_loginTimeLine = 0;
        if ($_SWIFT->Settings->Get('security_scloginlocked') == '1') {
            $_loginTimeLine = (int)($_SWIFT->Settings->Get('security_scloginlockedtimeline'));
        }

        if (!empty($_loginTimeLine)) {
            return $_loginTimeLine;
        }

        return self::LOGIN_TIMELINE;
    }

    /**
     * Check to see if the staff can login (check for failed attempts in X timeline)
     *
     * @author Saloni Dhall
     * @param string $_userEmail The User Email
     * @return array array(result, failedattempts) on Success, "false" otherwise
     */
    public static function CanUserLogin($_userEmail)
    {
        if (empty($_userEmail)) {
            return array(false, 0);
        }

        $_SWIFT = SWIFT::GetInstance();
        if ($_SWIFT->Settings->Get('security_scloginlocked') == '0') {
            return array(true, 0);
        }

        $_timeLine = DATENOW - self::GetLoginTimeline();
        $_loginRetries = self::GetLoginRetries();

        $_attemptCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "userloginlog WHERE useremail = '" . $_SWIFT->Database->Escape($_userEmail) . "' AND logindateline > '" . $_timeLine . "' AND loginresult = '" . self::LOGIN_FAILURE . "'");

        if ((int)($_attemptCount['totalitems']) > $_loginRetries) {
            return array(false, ((int)($_attemptCount['totalitems']) - 1));
        } else {
            return array(true, (int)($_attemptCount['totalitems']));
        }

        return array(true, 0);
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Saloni Dhall
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Checks to see if the interface type specified is valid
     *
     * @author Saloni Dhall
     * @param int $_interfaceType The Login Interface Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidInterfaceType($_interfaceType)
    {
        if ($_interfaceType == self::INTERFACE_CLIENT || $_interfaceType == self::INTERFACE_STAFF_AS_LOGIN || $_interfaceType == self::INTERFACE_LOGINSHARE) {
            return true;
        }

        return false;
    }

    /**
     * Insert a new user login log id
     *
     * @author Saloni Dhall
     * @param int $_userID The User ID
     * @param string $_userFullName The User Full Name
     * @param string $_userEmail The User Email Address
     * @param int $_loginResult The Login Result
     * @param int $_interfaceType The Login Interface Type
     * @param string $_sessionID (OPTIONAL) The Relevant Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data Provided
     */
    public static function Insert($_userID, $_userFullName, $_userEmail, $_loginResult, $_interfaceType, $_sessionID = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidInterfaceType($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_forwardedIPAddress = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $_forwardedIPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = '';
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userloginlog', array('userid' => $_userID, 'logindateline' => DATENOW, 'activitydateline' => DATENOW, 'logoutdateline' => '0', 'userfullname' => ReturnNone($_userFullName), 'useremail' => $_userEmail, 'ipaddress' => ReturnNone($_SERVER['REMOTE_ADDR']), 'forwardedipaddress' => ReturnNone($_forwardedIPAddress), 'useragent' => ReturnNone($_SERVER['HTTP_USER_AGENT']), 'logouttype' => '0', 'loginresult' => $_loginResult, 'interfacetype' => $_interfaceType, 'sessionid' => $_sessionID), 'INSERT');
        $_userLoginLogID = $_SWIFT->Database->Insert_ID();

        return $_userLoginLogID;
    }

    /**
     * Logs a success full attempt at login
     *
     * @author Saloni Dhall
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT User Object
     * @param int $_interfaceType The Login Interface Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data Provided or If Class not Loaded
     */
    public static function Success($_SWIFT_UserObject, $_interfaceType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidInterfaceType($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sessionID = '';
        if ($_SWIFT->Session instanceof SWIFT_Session && $_SWIFT->Session->GetIsClassLoaded()) {
            $_sessionID = $_SWIFT->Session->GetSessionID();
        }

        return self::Insert($_SWIFT_UserObject->GetID(), $_SWIFT_UserObject->GetProperty('fullname'), SWIFT_UserEmail::GetPrimaryEmail($_SWIFT_UserObject->GetID()), self::LOGIN_SUCCESS, $_interfaceType, $_sessionID);
    }

    /**
     * Logs a failure at login
     *
     * @author Saloni Dhall
     * @param array $_userEmail The User Email Address
     * @param int $_interfaceType The Login Interface Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data Provided
     */
    public static function Failure($_userEmail, $_interfaceType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidInterfaceType($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_UserObject = SWIFT_User::RetrieveOnEmailList($_userEmail);

        $_userID = 0;
        $_userFullname = '';
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userID = $_SWIFT_UserObject->GetUserID();
            $_userFullname = $_SWIFT_UserObject->GetFullName();
        }

        return self::Insert($_userID, $_userFullname, $_userEmail[0], self::LOGIN_FAILURE, $_interfaceType);
    }

    /**
     * Clear User Login logs after specified time.
     *
     * @author Saloni Dhall
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (86400 * $_SWIFT->Settings->Get('cpu_logcleardays'));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "userloginlog WHERE logindateline < '" . (int)($_dateThreshold) . "'");

        return true;
    }

}

?>
