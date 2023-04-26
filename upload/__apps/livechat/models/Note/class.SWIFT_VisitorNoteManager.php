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

namespace LiveChat\Models\Note;

use LiveChat\Models\Note\SWIFT_Note_Exception;
use SWIFT;
use SWIFT_Model;

/**
 * The Visitor Note Manager Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_VisitorNoteManager extends SWIFT_Model
{
    const TABLE_NAME = 'visitornotes';
    const PRIMARY_KEY = 'visitornoteid';

    const TABLE_STRUCTURE = "visitornoteid I PRIMARY AUTO NOTNULL,
                                linktypevalue C(50) DEFAULT '' NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                notetype I2 DEFAULT '0' NOTNULL,
                                lastupdated I DEFAULT '0' NOTNULL,
                                isedited I2 DEFAULT '0' NOTNULL,
                                editedstaffid I DEFAULT '0' NOTNULL,
                                editedstaffname C(255) DEFAULT '' NOTNULL,
                                editedtimeline I DEFAULT '0' NOTNULL,
                                notecolor I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'linktype, linktypevalue';
    const INDEX_2 = 'staffid';


    protected $_dataStore = array();

    // Core Constants
    const LINKTYPE_VISITOR = 1;
    const LINKTYPE_CHAT = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_visitorNoteID The Visitor Note ID
     * @throws SWIFT_Note_Exception If Unable to Load Visitor Note
     */
    public function __construct($_visitorNoteID)
    {
        parent::__construct();

        if (!$this->LoadData($_visitorNoteID)) {
            throw new SWIFT_Note_Exception('Unable to load Visitor Note: ' . $_visitorNoteID);
        }
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitornotes', $this->GetUpdatePool(), 'UPDATE', "visitornoteid = '" . (int)($this->GetVisitorNoteID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Visitor Note ID
     *
     * @author Varun Shoor
     * @return mixed "visitornoteid" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function GetVisitorNoteID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['visitornoteid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_visitorNoteID The Visitor Note ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_visitorNoteID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT visitornotes.*, visitornotedata.* FROM " . TABLE_PREFIX . "visitornotes AS visitornotes LEFT JOIN " . TABLE_PREFIX . "visitornotedata AS visitornotedata ON (visitornotes.visitornoteid = visitornotedata.visitornoteid) WHERE visitornotes.visitornoteid = '" . $_visitorNoteID . "'");
        if (isset($_dataStore['visitornoteid']) && !empty($_dataStore['visitornoteid'])) {
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
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);
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
     * Create a new Visitor/Chat Note
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type for the Visitor Note
     * @param int $_linkTypeValue The Link Type Value
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return mixed "_visitorNoteID" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_linkType, $_linkTypeValue, $_staffID, $_staffName, $_noteContents, $_noteColor = 1)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        if (!self::IsValidLinkType($_linkType) || empty($_linkTypeValue)) {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'visitornotes', array('linktype' => ($_linkType), 'linktypevalue' => $_linkTypeValue, 'dateline' => DATENOW, 'lastupdated' => DATENOW, 'isedited' => '0', 'staffid' => $_staffID, 'staffname' => $_staffName, 'editedstaffid' => '0', 'editedstaffname' => '', 'notecolor' => ($_noteColor)), 'INSERT');
        $_visitorNoteID = $_SWIFT->Database->Insert_ID();
        if (!$_visitorNoteID) {
            throw new SWIFT_Note_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'visitornotedata', array('visitornoteid' => $_visitorNoteID, 'contents' => $_noteContents));

        return $_visitorNoteID;
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
        if ($_linkType == self::LINKTYPE_VISITOR || $_linkType == self::LINKTYPE_CHAT) {
            return true;
        }

        return false;
    }

    /**
     * Update the visitor note Record
     *
     * @author Varun Shoor
     * @param int $_editedStaffID The Staff ID editing the Note
     * @param string $_editedStaffName The Staff Name editing the Note
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function Update($_editedStaffID, $_editedStaffName, $_noteContents, $_noteColor = 1)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        $this->UpdatePool('lastupdated', DATENOW);
        $this->UpdatePool('isedited', '1');
        $this->UpdatePool('editedstaffid', $_editedStaffID);
        $this->UpdatePool('notecolor', ($_noteColor));
        $this->UpdatePool('editedstaffname', ReturnNone($_editedStaffName));
        $this->UpdatePool('editedtimeline', DATENOW);
        $this->ProcessUpdatePool();

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitornotedata', array('contents' => ReturnNone($_noteContents)), 'UPDATE', "visitornoteid = '" . (int)($this->GetVisitorNoteID()) . "'");

        return true;
    }

    /**
     * Delete the visitor note record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetVisitorNoteID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of visitor note ids
     *
     * @author Varun Shoor
     * @param array $_visitorNoteIDList The Visitor Note ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_visitorNoteIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_visitorNoteIDList)) {
            return false;
        }

        $_finalVisitorNoteIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitornotes WHERE visitornoteid IN (" . BuildIN($_visitorNoteIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalVisitorNoteIDList[] = $_SWIFT->Database->Record['visitornoteid'];
        }

        if (!count($_finalVisitorNoteIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitornotes WHERE visitornoteid IN (" . BuildIN($_finalVisitorNoteIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitornotedata WHERE visitornoteid IN (" . BuildIN($_finalVisitorNoteIDList) . ")");

        return true;
    }
}
