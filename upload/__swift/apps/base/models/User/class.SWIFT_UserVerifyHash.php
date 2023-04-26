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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The User Verification Hash Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserVerifyHash extends SWIFT_Model
{
    const TABLE_NAME = 'userverifyhash';
    const PRIMARY_KEY = 'userverifyhashid';

    const TABLE_STRUCTURE = "userverifyhashid C(50) PRIMARY NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                hashtype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'hashtype, dateline';
    const INDEX_2 = 'userid, hashtype';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_USER = '1';
    const TYPE_FORGOTPASSWORD = '2';

    const HASH_EXPIRY = 86400; // 1 day

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_userVerifyHashID The User Verification Hash ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_userVerifyHashID)
    {
        parent::__construct();

        if (!$this->LoadData($_userVerifyHashID)) {
            throw new SWIFT_Exception('Failed to load User Verification Hash ID: ' . htmlspecialchars($_userVerifyHashID));
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
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'userverifyhash', $this->GetUpdatePool(), 'UPDATE', "userverifyhashid = '" . $this->Database->Escape($this->GetUserVerifyHashID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Verify Hash ID
     *
     * @author Varun Shoor
     * @return mixed "userverifyhashid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserVerifyHashID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['userverifyhashid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|string $_userVerifyHashID The User Verification Hash ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_userVerifyHashID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userverifyhash WHERE userverifyhashid = '" . $this->Database->Escape($_userVerifyHashID) . "'");
        if (isset($_dataStore['userverifyhashid']) && !empty($_dataStore['userverifyhashid'])) {
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
     * Check to see if its a valid verification hash type
     *
     * @author Varun Shoor
     * @param mixed $_hashType The Verification Hash
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidHashType($_hashType)
    {
        if ($_hashType == self::TYPE_USER || $_hashType == self::TYPE_FORGOTPASSWORD) {
            return true;
        }

        return false;
    }

    /**
     * Create a new user verification hash
     *
     * @author Varun Shoor
     * @param mixed $_hashType The Hash Type
     * @param int $_userID The User ID
     * @return mixed "_userVerifyHashID" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_hashType, $_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userID) || !self::IsValidHashType($_hashType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Delete any previous attempts
        self::DeleteOnUser(array($_userID));

        $_userVerifyHashID = BuildHash();
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userverifyhash', array('userid' => $_userID, 'hashtype' => (int)($_hashType), 'dateline' => DATENOW, 'userverifyhashid' => $_userVerifyHashID), 'INSERT');

        return $_userVerifyHashID;
    }

    /**
     * Delete the User Verify Hash record
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

        self::DeleteList(array($this->GetUserVerifyHashID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of User Verification Hashes
     *
     * @author Varun Shoor
     * @param array $_userVerifyHashIDList The User Verification Hash ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userVerifyHashIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userVerifyHashIDList)) {
            return false;
        }

        $_finalUserVerifyHashIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userverifyhash WHERE userverifyhashid IN (" . BuildIN($_userVerifyHashIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalUserVerifyHashIDList[] = $_SWIFT->Database->Record['userverifyhashid'];
        }

        if (!count($_finalUserVerifyHashIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "userverifyhash WHERE userverifyhashid IN (" . BuildIN($_finalUserVerifyHashIDList) . ")");

        return true;
    }

    /**
     * Delete hashes on user id list
     *
     * @author Varun Shoor
     * @param array $_userIDList The User ID List
     * @param mixed $_hashType (OPTIONAL) The Hash Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUser($_userIDList, $_hashType = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_extendedSQL = '';
        if (!empty($_hashType) && self::IsValidHashType($_hashType)) {
            $_extendedSQL = " AND hashtype = '" . (int)($_hashType) . "'";
        }

        $_userVerifyHashIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userverifyhash WHERE userid IN (" . BuildIN($_userIDList) . ")" . $_extendedSQL);
        while ($_SWIFT->Database->NextRecord()) {
            $_userVerifyHashIDList[] = $_SWIFT->Database->Record['userverifyhashid'];
        }

        if (!count($_userVerifyHashIDList)) {
            return false;
        }

        self::DeleteList($_userVerifyHashIDList);

        return true;
    }

    /**
     * Has the user verification hash expired?
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HasExpired()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_expiryThreshold = DATENOW - self::HASH_EXPIRY;

        if ($this->GetProperty('dateline') < $_expiryThreshold) {
            return true;
        }

        return false;
    }

    /**
     * Clean up stale verification hashes
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userVerifyHashIDList = array();

        // We clean up all forgot password hashes after 24 hrs by default.. and the normal user hashes based on setting
        $_userVerifyThresholdForgotPassword = DATENOW - 86400;
        $_userVerifyThreshold = DATENOW - ($_SWIFT->Settings->Get('user_delcleardays') * 86400);
        $_SWIFT->Database->Query("SELECT userverifyhashid FROM " . TABLE_PREFIX . "userverifyhash WHERE (hashtype = '" . self::TYPE_FORGOTPASSWORD . "' AND dateline < '" . $_userVerifyThresholdForgotPassword . "') OR (hashtype = '" . self::TYPE_USER . "' AND dateline < '" . $_userVerifyThreshold . "')");
        while ($_SWIFT->Database->NextRecord()) {
            $_userVerifyHashIDList[] = $_SWIFT->Database->Record['userverifyhashid'];
        }

        if (count($_userVerifyHashIDList)) {
            self::DeleteList($_userVerifyHashIDList);
        }

        return true;
    }
}

?>
