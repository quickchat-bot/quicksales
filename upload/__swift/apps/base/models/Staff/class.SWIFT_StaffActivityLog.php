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

namespace Base\Models\Staff;

use SWIFT;
use SWIFT_Date;
use SWIFT_Interface;
use SWIFT_Model;
use Base\Library\Staff\SWIFT_Staff_Exception;

/**
 * Staff Activity Log Manager Object
 *
 * @author Varun Shoor
 */
class SWIFT_StaffActivityLog extends SWIFT_Model
{
    const TABLE_NAME = 'staffactivitylog';
    const PRIMARY_KEY = 'staffactivitylogid';

    const TABLE_STRUCTURE = "staffactivitylogid I PRIMARY AUTO NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                ipaddress C(255) DEFAULT '' NOTNULL,
                                forwardedipaddress C(255) DEFAULT '' NOTNULL,
                                useragent C(255) DEFAULT '' NOTNULL,
                                description C(255) DEFAULT '' NOTNULL,
                                actiontype I2 DEFAULT '0' NOTNULL,
                                sectiontype I2 DEFAULT '0' NOTNULL,
                                interfacetype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'interfacetype, dateline';
    const INDEX_2 = 'dateline';


    protected $_dataStore = array();

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

    // Action Types
    const ACTION_INSERT = 1;
    const ACTION_UPDATE = 2;
    const ACTION_DELETE = 3;
    const ACTION_OTHER = 4;

    // Section Types
    const SECTION_STAFF = 1;
    const SECTION_DEPARTMENTS = 2;
    const SECTION_LIVESUPPORT = 3;
    const SECTION_LANGUAGE = 5;
    const SECTION_GEOIP = 6;
    const SECTION_TICKETS = 7;
    const SECTION_PARSER = 8;
    const SECTION_DIAGNOSTICS = 9;
    const SECTION_SCHEDULEDTASKS = 10;
    const SECTION_CUSTOMFIELDS = 11;
    const SECTION_TEMPLATES = 12;
    const SECTION_USERS = 13;
    const SECTION_WIDGETS = 14;
    const SECTION_RATINGS = 15;
    const SECTION_PRODUCTS = 16;
    const SECTION_CLOUD = 17;
    const SECTION_DEVELOPMENT = 18;
    const SECTION_GENERAL = 19;
    const SECTION_HOME = 20;
    const SECTION_NEWS = 21;
    const SECTION_KNOWLEDGEBASE = 22;
    const SECTION_TROUBLESHOOTER = 24;
    const SECTION_BACKEND = 25;
    const SECTION_REPORTS = 26;
    const SECTION_PHONE = 27;
    const SECTION_HRMS = 28;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staffactivitylog');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'staffactivitylog', $this->GetUpdatePool(), 'UPDATE', "staffactivitylogid = '" . (int)($this->GetStaffActivityLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Staff Activity Log ID
     *
     * @author Varun Shoor
     * @return mixed "staffactivitylogid" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetStaffActivityLogID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['staffactivitylogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_staffActivityLogID The Staff Activity Log ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_staffActivityLogID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staffactivitylog WHERE staffactivitylogid = '" . $_staffActivityLogID . "'");
        if (isset($_dataStore['staffactivitylogid']) && !empty($_dataStore['staffactivitylogid'])) {
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
        if ($_interfaceType == self::INTERFACE_STAFF || $_interfaceType == self::INTERFACE_INTRANET || $_interfaceType == self::INTERFACE_ADMIN || $_interfaceType == self::INTERFACE_PDA ||
            $_interfaceType == self::INTERFACE_SYNCWORKS || $_interfaceType == self::INTERFACE_WINAPP || $_interfaceType == self::INTERFACE_INSTAALERT || $_interfaceType == self::INTERFACE_API ||
            $_interfaceType == self::INTERFACE_RSS) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if action type is valid
     *
     * @author Varun Shoor
     * @param int $_actionType The Action Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidActionType($_actionType)
    {
        if ($_actionType == self::ACTION_INSERT || $_actionType == self::ACTION_UPDATE || $_actionType == self::ACTION_DELETE || $_actionType == self::ACTION_OTHER) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if the section type is valid
     *
     * @author Varun Shoor
     * @param int $_sectionType The Section Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidSectionType($_sectionType)
    {
        if ($_sectionType == self::SECTION_STAFF || $_sectionType == self::SECTION_DEPARTMENTS || $_sectionType == self::SECTION_LIVESUPPORT ||
            $_sectionType == self::SECTION_LANGUAGE || $_sectionType == self::SECTION_GEOIP || $_sectionType == self::SECTION_TICKETS || $_sectionType == self::SECTION_PARSER ||
            $_sectionType == self::SECTION_DIAGNOSTICS || $_sectionType == self::SECTION_SCHEDULEDTASKS || $_sectionType == self::SECTION_CUSTOMFIELDS ||
            $_sectionType == self::SECTION_TEMPLATES || $_sectionType == self::SECTION_USERS || $_sectionType == self::SECTION_WIDGETS || $_sectionType == self::SECTION_RATINGS ||
            $_sectionType == self::SECTION_PRODUCTS || $_sectionType == self::SECTION_CLOUD || $_sectionType == self::SECTION_DEVELOPMENT || $_sectionType == self::SECTION_GENERAL ||
            $_sectionType == self::SECTION_HOME || $_sectionType == self::SECTION_NEWS || $_sectionType == self::SECTION_KNOWLEDGEBASE ||
            $_sectionType == self::SECTION_TROUBLESHOOTER || $_sectionType == self::SECTION_BACKEND || $_sectionType == self::SECTION_REPORTS || $_sectionType == self::SECTION_PHONE ||
            $_sectionType == self::SECTION_HRMS) {
            return true;
        }

        return false;
    }

    /**
     * Insert a new Activity Log Record
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param string $_description The Activity Description
     * @param int $_actionType The Action Type (CRUD)
     * @param int $_sectionType The Section Type (Staff, Department etc.)
     * @param int $_interfaceType The Interface Type
     * @return bool "staffActivityLogID" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided or If Creation fails
     */
    static private function Insert($_staffID, $_staffName, $_description, $_actionType, $_sectionType, $_interfaceType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidInterfaceType($_interfaceType) || !self::IsValidActionType($_actionType) || !self::IsValidSectionType($_sectionType) || empty($_description)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_userAgent = $_ipAddress = $_forwardedIPAddress = '';
        if (SWIFT::Get('IP')) {
            $_ipAddress = SWIFT::Get('IP');
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-2189 1406:Data too long for column 'useragent' at row 1 (library/class.SWIFT.php:768)
         *
         */
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $_userAgent = trim(substr($_SERVER['HTTP_USER_AGENT'], 0, 255));
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $_forwardedIPAddress = GetClientIPFromXForwardedFor($_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staffactivitylog', array('staffid' => $_staffID, 'dateline' => DATENOW, 'staffname' => ReturnNone($_staffName), 'ipaddress' => ReturnNone($_ipAddress), 'forwardedipaddress' => ReturnNone($_forwardedIPAddress), 'useragent' => ReturnNone($_userAgent), 'description' => ReturnNone($_description), 'actiontype' => $_actionType, 'sectiontype' => $_sectionType, 'interfacetype' => $_interfaceType), 'INSERT');
        $_staffActivityLogID = $_SWIFT->Database->Insert_ID();
        if (!$_staffActivityLogID) {
            throw new SWIFT_Staff_Exception(SWIFT_CREATEFAILED);
        }

        return $_staffActivityLogID;
    }

    /**
     * Logs the Entry
     *
     * @author Varun Shoor
     * @param string $_description The Activity Description
     * @param int $_actionType The Action Type (CRUD)
     * @param int $_sectionType The Section Type (Staff, Department etc.)
     * @param int $_interfaceType The Interface Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided
     */
    public static function AddToLog($_description, $_actionType, $_sectionType, $_interfaceType)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_StaffObject = $_SWIFT->Staff;
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        return self::Insert($_SWIFT_StaffObject->GetStaffID(), $_SWIFT_StaffObject->GetProperty('fullname'), $_description, $_actionType, $_sectionType, $_interfaceType);
    }

    /**
     * Displays the Dashboard Widget
     *
     * @author Varun Shoor
     * @return mixed "_activityContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetDashboardContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_StaffObject = $_SWIFT->Staff;
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_timeLine = $_SWIFT_StaffObject->GetProperty('lastvisit');

        $_countContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staffactivitylog
            WHERE interfacetype = '" . self::INTERFACE_ADMIN . "' AND dateline > '" . (int)($_timeLine) . "' ORDER BY staffactivitylogid DESC");
        $_totalRecordCount = 0;
        if (isset($_countContainer['totalitems'])) {
            $_totalRecordCount = (int)($_countContainer['totalitems']);
        }

        $_staffActivityLogContainer = array();
        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "staffactivitylog WHERE interfacetype = '" . self::INTERFACE_ADMIN . "' AND dateline > '" . (int)($_timeLine) . "' ORDER BY staffactivitylogid DESC", 7);
        while ($_SWIFT->Database->NextRecord()) {
            $_staffActivityLogContainer[$_SWIFT->Database->Record['staffactivitylogid']] = $_SWIFT->Database->Record;
        }

        $_activityContainer = array();

        if (count($_staffActivityLogContainer)) {
            foreach ($_staffActivityLogContainer as $_key => $_val) {
                $_displayClass = $_displayText = '';
                if ($_val['actiontype'] == SWIFT_StaffActivityLog::ACTION_INSERT) {
                    $_displayText = $_SWIFT->Language->Get('accreated');
                    $_displayClass = 'blocknotecountergreen';
                } else if ($_val['actiontype'] == SWIFT_StaffActivityLog::ACTION_UPDATE) {
                    $_displayText = $_SWIFT->Language->Get('acupdated');
                    $_displayClass = 'blocknotecounterorange';
                } else if ($_val['actiontype'] == SWIFT_StaffActivityLog::ACTION_DELETE) {
                    $_displayText = $_SWIFT->Language->Get('acdeleted');
                    $_displayClass = 'blocknotecounterred';
                } else if ($_val['actiontype'] == SWIFT_StaffActivityLog::ACTION_OTHER) {
                    $_displayText = $_SWIFT->Language->Get('acother');
                    $_displayClass = 'blocknotecounterred';
                }

                $_interfaceTitle = SWIFT_Interface::GetInterfaceLabel($_val['interfacetype']);

                $_activityContainer[] = array('title' => htmlspecialchars($_val['staffname']) . ' (' . $_interfaceTitle . ')', 'date' => SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_val['dateline']) . ' (' . SWIFT_Date::ColorTime(DATENOW - $_val['dateline']) . ')', 'contents' => '<div class="' . $_displayClass . '">' . $_displayText . '</div> ' . htmlspecialchars($_val['description']));
            }
        }

        return array($_totalRecordCount, $_activityContainer);
    }

    /**
     * Clear Staff Activity logs after specified time.
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (86400 * $_SWIFT->Settings->Get('cpu_logcleardays'));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffactivitylog WHERE dateline < '" . (int)($_dateThreshold) . "'");

        return true;
    }
}

?>
