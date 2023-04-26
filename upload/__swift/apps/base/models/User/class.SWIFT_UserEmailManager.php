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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Abstract User Email Manager Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_UserEmailManager extends SWIFT_Model
{
    const TABLE_NAME = 'useremails';
    const PRIMARY_KEY = 'useremailid';

    const TABLE_STRUCTURE = "useremailid I PRIMARY AUTO NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                isprimary I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'linktype, linktypeid, isprimary';
    const INDEX_2 = 'linktype, email';
    const INDEX_3 = 'email';
    const INDEX_4 = 'linktype, useremailid';


    protected $_dataStore = array();

    // Core Constants
    const LINKTYPE_USER = 1;
    const LINKTYPE_ORGANIZATION = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Unable to Load User Email Record
     */
    public function __construct($_userEmailID)
    {
        parent::__construct();

        if (!$this->LoadData($_userEmailID)) {
            throw new SWIFT_Exception('Unable to load User Email: ' .$_userEmailID);
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
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'useremails', $this->GetUpdatePool(), 'UPDATE', "useremailid = '" . (int)($this->GetUserEmailID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Email ID
     *
     * @author Varun Shoor
     * @return mixed "useremailid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserEmailID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['useremailid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_userEmailID The User Email ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_userEmailID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE useremailid = '" .$_userEmailID . "'");
        if (isset($_dataStore['useremailid']) && !empty($_dataStore['useremailid'])) {
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
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Checks to see if the given variable is a valid link type
     *
     * @author Varun Shoor
     * @param int $_linkType The User Email Link Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidLinkType($_linkType)
    {
        if ($_linkType == self::LINKTYPE_USER || $_linkType == self::LINKTYPE_ORGANIZATION) {
            return true;
        }

        return false;
    }

    /**
     * Create a new User Email Record
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type
     * @param int $_linkTypeID The Link Type ID
     * @param string $_email The User Email Record
     * @param bool $_isPrimary Whether its the primary email
     * @return int
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_linkType, $_linkTypeID, $_email, $_isPrimary = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidLinkType($_linkType) || empty($_linkTypeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'useremails', array('linktype' => $_linkType, 'linktypeid' => $_linkTypeID,
            'email' => mb_strtolower($_email), 'isprimary' => (int)($_isPrimary)), 'INSERT');
        $_userEmailID = $_SWIFT->Database->Insert_ID();
        if (!$_userEmailID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_userEmailID;
    }

    /**
     * Update User Email Record
     *
     * @author Varun Shoor
     * @param string $_email The User Email Record
     * @param bool $_isPrimary Whether its the primary email
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Update($_email, $_isPrimary)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('email', mb_strtolower($_email));
        $this->UpdatePool('isprimary', (int)($_isPrimary));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the user email record
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

        self::DeleteList(array($this->GetUserEmailID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a List of User Email IDs
     *
     * @author Varun Shoor
     * @param array $_userEmailIDList The User Email ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userEmailIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userEmailIDList)) {
            return false;
        }

        $_finalUserEmailIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE useremailid IN (" . BuildIN($_userEmailIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalUserEmailIDList[] = $_SWIFT->Database->Record['useremailid'];
        }

        if (!count($_finalUserEmailIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "useremails WHERE useremailid IN (" . BuildIN($_finalUserEmailIDList) . ")");

        return true;
    }
}

?>
