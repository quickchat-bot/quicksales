<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Models\Ban;

use Parser\Models\Ban\SWIFT_Ban_Exception;
use SWIFT;
use SWIFT_Model;

/**
 * The Parser Ban Model
 *
 * @author Varun Shoor
 */
class SWIFT_ParserBan extends SWIFT_Model
{
    const TABLE_NAME = 'parserbans';
    const PRIMARY_KEY = 'parserbanid';

    const TABLE_STRUCTURE = "parserbanid I PRIMARY AUTO NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'email';
    const INDEXTYPE_1 = 'UNIQUE';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_parserBanID The Parser Ban ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Record could not be loaded
     */
    public function __construct($_parserBanID)
    {
        parent::__construct();

        // @codeCoverageIgnoreStart
        if (!$this->LoadData($_parserBanID)) {
            throw new SWIFT_Ban_Exception('Failed to load Parser Ban ID: ' . $_parserBanID);
        }
        // @codeCoverageIgnoreEnd
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
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'parserbans', $this->GetUpdatePool(), 'UPDATE', "parserbanid = '" .
            (int)($this->GetParserBanID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Parser Ban ID
     *
     * @author Varun Shoor
     * @return mixed "parserbanid" on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function GetParserBanID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['parserbanid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_parserBanID The Parser Ban ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_parserBanID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "parserbans WHERE parserbanid = '" .
            $_parserBanID . "'");
        if (isset($_dataStore['parserbanid']) && !empty($_dataStore['parserbanid'])) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     *
     * @param string $_key The Key Identifier
     *
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Ban_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Parser Ban
     *
     * @author Varun Shoor
     *
     * @param string $_banEmail The Email Address to Ban
     * @param int    $_staffID  The Staff ID (Creator), 0 if its being created directly..
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Email Address is Empty or If Creation Fails
     */
    public static function Create($_banEmail, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_banEmail)) {
            throw new SWIFT_Ban_Exception('Ban Email Address is Empty');
        }

        $_SWIFT->Database->Replace(TABLE_PREFIX . 'parserbans', array('email' => $_banEmail, 'dateline' => DATENOW, 'staffid' => $_staffID),
            array('email'));
        $_parserBanID = $_SWIFT->Database->Insert_ID();

        if (!$_parserBanID) {
            throw new SWIFT_Ban_Exception('Unable to Create Parser Ban');
        }

        self::RebuildCache();

        return $_parserBanID;
    }

    /**
     * Create Parser Bans from List
     *
     * @author Varun Shoor
     *
     * @param array $_banEmailList The Email Ban List
     * @param int   $_staffID      If its being created by staff provide the Staff ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CreateFromList($_banEmailList, $_staffID = 0)
    {
        if (!_is_array($_banEmailList)) {
            return false;
        }

        foreach ($_banEmailList as $_key => $_val) {
            if (empty($_val)) {
                continue;
            }

            try {
                self::Create($_val, $_staffID);
            } catch (SWIFT_Ban_Exception $_SWIFT_Ban_ExceptionObject) {

            }
        }

        return true;
    }

    /**
     * Update the Parser Ban Record
     *
     * @author Varun Shoor
     *
     * @param string $_banEmail The Email Address to Ban
     * @param int    $_staffID  The Staff ID (Creator), 0 if its being created directly..
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded or if the Email Address is empty or If Update Fails
     */
    public function Update($_banEmail, $_staffID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_banEmail)) {
            throw new SWIFT_Ban_Exception('Ban Email Address is Empty');
        }

        // As Email is a unique field, check for duplicate records
        $_proceed = true;

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserbans WHERE email = '" . $this->Database->Escape($_banEmail) . "'");
        while ($this->Database->NextRecord()) {
            // Different record?

            // should this check not rather be == ? Since we're checking for duplicate
            if ($this->Database->Record['parserbanid'] != $this->GetParserBanID()) {
                throw new SWIFT_Ban_Exception('Duplicate Email Record Exists');
            }
        }

        $this->UpdatePool('email', $_banEmail);
        $this->UpdatePool('staffid', $_staffID);

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Parser Ban record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ban_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ban_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetParserBanID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Parser Ban Records
     *
     * @author Varun Shoor
     *
     * @param array $_parserBanIDList The Parser Ban ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_parserBanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserBanIDList)) {
            return false;
        }

        $_finalParserBanIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserbans WHERE parserbanid IN (" . BuildIN($_parserBanIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['email']) . '<br />';

            $_index++;

            $_finalParserBanIDList[] = $_SWIFT->Database->Record['parserbanid'];
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelbans'), count($_finalParserBanIDList)), $_SWIFT->Language->Get('msgdelbans') .
            '<br />' . $_finalText);

        if (!count($_finalParserBanIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserbans WHERE parserbanid IN (" . BuildIN($_finalParserBanIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Parser Ban Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserbans ORDER BY parserbanid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_cache[] = $_SWIFT->Database->Record3['email'];
        }

        $_SWIFT->Cache->Update('parserbancache', $_cache);

        return true;
    }

    /**
     * Check to see if the email is banned
     *
     * @author Varun Shoor
     *
     * @param string $_emailAddress The Email Address
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsBanned($_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailAddress) || !IsEmailValid($_emailAddress) || !_is_array($_SWIFT->Cache->Get('parserbancache'))) {
            return false;
        }

        $_matches = $_emailAddressList = array();
        $_emailAddressList[] = "(email = '" . $_SWIFT->Database->Escape($_emailAddress) . "')";

        if (preg_match('/^(.*)\@(.*)$/i', $_emailAddress, $_matches)) {
            if (isset($_matches[2]) && !empty($_matches[2])) {
                $_emailAddressList[] = "(email = '@" . $_SWIFT->Database->Escape(trim(mb_strtolower($_matches[2]))) . "')";
                $_emailAddressList[] = "(email = '" . $_SWIFT->Database->Escape(trim(mb_strtolower($_matches[2]))) . "')";
            }
        }

        // @codeCoverageIgnoreStart
        if (count($_emailAddressList) == 0) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        $_parserBanContainer = $_SWIFT->Database->QueryFetch("SELECT parserbanid FROM " . TABLE_PREFIX . "parserbans WHERE " .
            implode(' OR ', $_emailAddressList));
        if (isset($_parserBanContainer['parserbanid']) && !empty($_parserBanContainer['parserbanid'])) {
            return true;
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }
}

?>
