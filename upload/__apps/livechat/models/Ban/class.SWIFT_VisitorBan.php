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

namespace LiveChat\Models\Ban;

use LiveChat\Models\Ban\SWIFT_Ban_Exception;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Visitor Ban Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_VisitorBan extends SWIFT_Model
{
    const TABLE_NAME = 'visitorbans';
    const PRIMARY_KEY = 'visitorbanid';

    const TABLE_STRUCTURE = "visitorbanid I PRIMARY AUTO NOTNULL,
                                ipaddress C(255) DEFAULT '0.0.0.0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                isregex I2 DEFAULT '0' NOTNULL";

    const INDEX_2 = 'staffid';
    const INDEX_3 = 'ipaddress';
    const INDEXTYPE_3 = 'UNIQUE';
    const INDEX_4 = 'isregex';


    private $_visitorBan = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_visitorBanID The Visitor Ban ID
     * @throws SWIFT_Exception
     */
    public function __construct($_visitorBanID)
    {
        parent::__construct();

        if (!$this->LoadData($_visitorBanID)) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
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
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitorbans', $this->GetUpdatePool(), 'UPDATE', "visitorbanid = '" . (int)($this->GetVisitorBanID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Visitor Ban ID
     *
     * @author Varun Shoor
     * @return mixed "visitorbanid" on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function GetVisitorBanID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_visitorBan['visitorbanid'];
    }

    /**
     * Load the Visitor Ban Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_visitorBanID The Visitor Ban ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_visitorBanID)
    {
        $_visitorBan = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitorbans WHERE visitorbanid = '" . ($_visitorBanID) . "'");
        if (isset($_visitorBan['visitorbanid']) && !empty($_visitorBan['visitorbanid'])) {
            $this->_visitorBan = $_visitorBan;

            return true;
        }

        return false;
    }

    /**
     * Checks to see if the IP is valid
     *
     * @author Varun Shoor
     * @param string $_ip The IP Address
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidIP($_ip)
    {
        if (preg_match("/^((127)|(192)|(10).*)$/", $_ip)) {
            return false;
        }

        $_ipArray = explode(".", $_ip);
        if (count($_ipArray) != 4) {
            return false;
        }

        foreach ($_ipArray as $_block) {
            if (!is_numeric($_block) || $_block > 255 || $_block < 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves the Visitor Ban Record by IP Address
     *
     * @author Varun Shoor
     * @param string $_ipAddress The IP Address
     * @return mixed "_visitorBan" (ARRAY) on Success, "false" otherwise
     */
    public static function GetVisitorBanByIPAddress($_ipAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ipAddress)) {
            return false;
        }

        $_visitorBan = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitorbans WHERE ipaddress = '" . $_SWIFT->Database->Escape($_ipAddress) . "'");

        return $_visitorBan;
    }

    /**
     * Insert a new Visitor Ban
     *
     * @author Varun Shoor
     * @param string $_ipAddress The IP Address to ban
     * @param bool $_isRegex Whether or not the value is a regular expression
     * @param int $_staffID The Staff ID who is banning this visitor
     * @return mixed "visitorBanID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If Invalid Data is Provided or If Creation fails
     */
    public static function Insert($_ipAddress, $_isRegex, $_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ipAddress)) {
            throw new SWIFT_Ban_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_visitorBan = self::GetVisitorBanByIPAddress($_ipAddress);
        if (isset($_visitorBan['visitorbanid']) && !empty($_visitorBan['visitorbanid'])) {
            if ($_isRegex != $_visitorBan['isregex']) {
                $_SWIFT_VisitorBanObject = new SWIFT_VisitorBan($_visitorBan['visitorbanid']);
                if ($_SWIFT_VisitorBanObject instanceof SWIFT_VisitorBan && $_SWIFT_VisitorBanObject->GetIsClassLoaded()) {
                    $_SWIFT_VisitorBanObject->Update($_ipAddress, $_isRegex, $_staffID);
                }
            }

            return $_visitorBan['visitorbanid'];
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'visitorbans', array('ipaddress' => $_ipAddress, 'dateline' => DATENOW, 'staffid' => ($_staffID), 'isregex' => (int)($_isRegex)), 'INSERT');
        $_visitorBanID = $_SWIFT->Database->Insert_ID();
        if (!$_visitorBanID) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::RebuildCache();

        return $_visitorBanID;
    }

    /**
     * Rebuild the Visitor Ban Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();
        $_index = 0;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorbans WHERE isregex = '1' ORDER BY visitorbanid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record["visitorbanid"]] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('visitorbancache', $_cache);

        return true;
    }

    /**
     * Checks to see if a given IP Address is banned
     *
     * @author Varun Shoor
     * @param string $_ipAddress The IP Address to Check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function BanExists($_ipAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ipAddress)) {
            return false;
        }

        $_ipAddress = trim($_ipAddress);

        // First check directly against the IP
        $_visitorBan = self::GetVisitorBanByIPAddress($_ipAddress);
        if (isset($_visitorBan['visitorbanid']) && !empty($_visitorBan['visitorbanid'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if a given IP Address is banned
     *
     * @author Varun Shoor
     * @param string $_ipAddress The IP Address to Check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsBanned($_ipAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_visitorBanCache = $_SWIFT->Cache->Get('visitorbancache');

        // First check directly against the IP
        $_visitorBan = self::GetVisitorBanByIPAddress($_ipAddress);
        if (isset($_visitorBan['visitorbanid']) && !empty($_visitorBan['visitorbanid'])) {
            return true;
        }

        // If that failed, we itterate through our cache and check against the regular expressions
        if (!$_visitorBanCache || !_is_array($_visitorBanCache)) {
            return false;
        }

        foreach ($_visitorBanCache as $_key => $_val) {

            // Match found! Return with success
            if (@preg_match($_val['ipaddress'], $_ipAddress)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves a Visitor Ban Property
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_visitorBan[$_key])) {
            return false;
        }

        return $this->_visitorBan[$_key];
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_visitorBan" Array on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function GetDataArray()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_visitorBan;
    }

    /**
     * Deletes a set of visitor bans
     *
     * @author Varun Shoor
     * @param array $_visitorBanIDList The Visitor Ban ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_visitorBanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_visitorBanIDList)) {
            return false;
        }

        $_finalVisitorBanIDList = array();
        $_itemText = '';
        $_index = 1;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorbans WHERE visitorbanid IN (" . BuildIN($_visitorBanIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalVisitorBanIDList[] = $_SWIFT->Database->Record['visitorbanid'];
            $_itemText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['ipaddress']) . '<BR />';
            $_index++;
        }

        if (!count($_finalVisitorBanIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelbans'), count($_finalVisitorBanIDList)), $_SWIFT->Language->Get('msgdelbans') . '<br />' . $_itemText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorbans WHERE visitorbanid IN (" . BuildIN($_finalVisitorBanIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Update the visitor ban
     *
     * @author Varun Shoor
     * @param string $_ipAddress The IP Address to ban
     * @param bool $_isRegex Whether or not the value is a regular expression
     * @param int $_staffID The Staff ID who is banning this visitor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Update($_ipAddress, $_isRegex, $_staffID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('ipaddress', $_ipAddress);
        $this->UpdatePool('dateline', DATENOW);
        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('isregex', (int)($_isRegex));
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }
}


