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
 * The User Note Manager Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_UserNoteManager extends SWIFT_Model
{
    const TABLE_NAME = 'usernotes';
    const PRIMARY_KEY = 'usernoteid';

    const TABLE_STRUCTURE = "usernoteid I PRIMARY AUTO NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastupdated I DEFAULT '0' NOTNULL,
                                isedited I2 DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                editedstaffid I DEFAULT '0' NOTNULL,
                                editedstaffname C(255) DEFAULT '' NOTNULL,
                                editedtimeline I DEFAULT '0' NOTNULL,
                                notecolor I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'linktype, linktypeid';


    protected $_dataStore = array();

    // Core Constants
    const LINKTYPE_USER = 1;
    const LINKTYPE_ORGANIZATION = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Unable to Load User Note
     */
    public function __construct($_userNoteID)
    {
        parent::__construct();

        if (!$this->LoadData($_userNoteID)) {
            throw new SWIFT_Exception('Unable to load User Note: ' . $_userNoteID);
        }
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'usernotes', $this->GetUpdatePool(), 'UPDATE', "usernoteid = '" . (int)($this->GetUserNoteID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Note ID
     *
     * @author Varun Shoor
     * @return mixed "usernoteid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserNoteID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['usernoteid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_userNoteID The User Note ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_userNoteID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT usernotes.*, usernotedata.* FROM " . TABLE_PREFIX . "usernotes AS usernotes LEFT JOIN " . TABLE_PREFIX . "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid) WHERE usernotes.usernoteid = '" . $_userNoteID . "'");
        if (isset($_dataStore['usernoteid']) && !empty($_dataStore['usernoteid'])) {
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
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Return a sanitized note color
     *
     * @author Varun Shoor
     * @param int $_noteColor The Note Color
     * @return int
     */
    public static function GetSanitizedNoteColor($_noteColor)
    {
        if ($_noteColor > 5 || $_noteColor < 1) {
            $_noteColor = 1;
        }

        return $_noteColor;
    }

    /**
     * Create a new User/Organization Note
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type for the User Note
     * @param int $_linkTypeID The Link Type ID
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return mixed "userNoteID" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_linkType, $_linkTypeID, $_staffID, $_staffName, $_noteContents, $_noteColor = 1)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        if (!self::IsValidLinkType($_linkType) || empty($_linkTypeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'usernotes', array('linktype' => $_linkType, 'linktypeid' => $_linkTypeID, 'dateline' => DATENOW, 'lastupdated' => DATENOW, 'isedited' => '0', 'staffid' => $_staffID, 'staffname' => $_staffName, 'editedstaffid' => '0', 'editedstaffname' => '', 'notecolor' => $_noteColor), 'INSERT');
        $_userNoteID = $_SWIFT->Database->Insert_ID();
        if (!$_userNoteID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'usernotedata', array('usernoteid' => $_userNoteID, 'notecontents' => $_noteContents));

        return $_userNoteID;
    }

    /**
     * Is Valid Link Type
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type
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
     * Update the user note Record
     *
     * @author Varun Shoor
     * @param int $_editedStaffID The Staff ID editing the Note
     * @param string $_editedStaffName The Staff Name editing the Note
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Update($_editedStaffID, $_editedStaffName, $_noteContents, $_noteColor = 1)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        $this->UpdatePool('lastupdated', DATENOW);
        $this->UpdatePool('isedited', '1');
        $this->UpdatePool('editedstaffid', $_editedStaffID);
        $this->UpdatePool('notecolor', $_noteColor);
        $this->UpdatePool('editedstaffname', ReturnNone($_editedStaffName));
        $this->UpdatePool('editedtimeline', DATENOW);
        $this->ProcessUpdatePool();

        $this->Database->AutoExecute(TABLE_PREFIX . 'usernotedata', array('notecontents' => ReturnNone($_noteContents)), 'UPDATE', "usernoteid = '" . (int)($this->GetUserNoteID()) . "'");

        return true;
    }

    /**
     * Delete the user note record
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

        self::DeleteList(array($this->GetUserNoteID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of user note ids
     *
     * @author Varun Shoor
     * @param array $_userNoteIDList The User Note ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userNoteIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userNoteIDList)) {
            return false;
        }

        $_finalUserNoteIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usernotes WHERE usernoteid IN (" . BuildIN($_userNoteIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalUserNoteIDList[] = $_SWIFT->Database->Record['usernoteid'];
        }

        if (!count($_finalUserNoteIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usernotes WHERE usernoteid IN (" . BuildIN($_finalUserNoteIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "usernotedata WHERE usernoteid IN (" . BuildIN($_finalUserNoteIDList) . ")");

        return true;
    }

    /**
     * Update secondary user IDs with merged primary user ID
     *
     * @author Pankaj Garg
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     */
    public static function UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }
        $_userNoteContainer = array();
        $_SWIFT->Database->Query("SELECT usernoteid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                  WHERE linktype = " . self::LINKTYPE_USER . "
                                    AND linktypeid IN ( " . BuildIN($_secondaryUserIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userNoteContainer[$_SWIFT->Database->Record['usernoteid']] = $_SWIFT->Database->Record;
        }
        foreach ($_userNoteContainer as $_userNote) {
            $_UserNote = new SWIFT_UserNote($_userNote['usernoteid']);
            $_UserNote->UpdateUser($_primaryUserID, self::LINKTYPE_USER);
        }
        return true;
    }

    /**
     * Updates the user with which the note is linked
     *
     * @author Abhishek Mittal
     *
     * @param int $_userID
     * @param int $_userType
     *
     * @return SWIFT_UserNoteManager
     * @throws SWIFT_Exception If the Class is not Loaded OR If Invalid Data is Provided
     */
    public function UpdateUser($_userID, $_userType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }
        if (!self::IsValidLinkType($_userType) || empty($_userID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $this->UpdatePool('linktypeid', $_userID);
        $this->UpdatePool('linktype', $_userType);
        $this->ProcessUpdatePool();
        return $this;
    }
}

?>
