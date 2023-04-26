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

namespace Tickets\Models\Note;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Note Management Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_TicketNoteManager extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketnotes';
    const PRIMARY_KEY        =    'ticketnoteid';

    const TABLE_STRUCTURE    =    "ticketnoteid I PRIMARY AUTO NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                forstaffid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                isedited I2 DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,
                                editedstaffid I DEFAULT '0' NOTNULL,
                                editedstaffname C(255) DEFAULT '' NOTNULL,
                                editedtimeline I DEFAULT '0' NOTNULL,
                                notecolor I DEFAULT '0' NOTNULL,
                                note X2";

    const INDEX_1            =    'linktypeid, linktype, forstaffid';
    const INDEX_2            =    'linktype, linktypeid';


    protected $_dataStore = array();

    // Core Constants
    const LINKTYPE_TICKET = 1;
    const LINKTYPE_TICKETPOST = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketNoteID The Ticket Note ID
     * @throws SWIFT_Note_Exception If the Record could not be loaded
     */
    public function __construct($_ticketNoteID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketNoteID)) {
            throw new SWIFT_Note_Exception('Failed to load Ticket Note ID: ' . $_ticketNoteID);
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
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketnotes', $this->GetUpdatePool(), 'UPDATE', "ticketnoteid = '" . (int) ($this->GetTicketNoteID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Note ID
     *
     * @author Varun Shoor
     * @return mixed "ticketnoteid" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function GetTicketNoteID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketnoteid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketNoteID The Ticket Note ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketNoteID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketnotes WHERE ticketnoteid = '" . $_ticketNoteID . "'");
        if (isset($_dataStore['ticketnoteid']) && !empty($_dataStore['ticketnoteid']))
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
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
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
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
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
        if ($_noteColor > 5 || $_noteColor < 1)
        {
            $_noteColor = 1;
        }

        return $_noteColor;
    }

    /**
     * Create a new Ticket/Ticket Post Note
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type for the User Note
     * @param int $_linkTypeID The Link Type ID
     * @param int $_forStaffID The Staff ID for which this note is for (0 = Visible to all)
     * @param int $_staffID The Staff ID
     * @param string $_staffName The Staff Name
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return mixed "_ticketNoteID" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_linkType, $_linkTypeID, $_forStaffID, $_staffID, $_staffName, $_noteContents, $_noteColor = 1) {
        $_SWIFT = SWIFT::GetInstance();

        $_linkTypeID = $_linkTypeID;
        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        if (!self::IsValidLinkType($_linkType) || empty($_linkTypeID)) {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketnotes', array('linktype' =>  ($_linkType), 'linktypeid' => $_linkTypeID,
            'dateline' => DATENOW, 'isedited' => '0', 'forstaffid' => $_forStaffID, 'staffid' => $_staffID,
            'staffname' => $_staffName, 'editedstaffid' => '0', 'editedstaffname' => '', 'editedtimeline' => '0',
            'notecolor' =>  ($_noteColor), 'note' => $_noteContents), 'INSERT');
        $_ticketNoteID = $_SWIFT->Database->Insert_ID();
        if (!$_ticketNoteID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketNoteID;
    }

    /**
     * Is Valid Link Type
     *
     * @author Varun Shoor
     * @param int $_linkType The Link Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidLinkType($_linkType) {
        if ($_linkType == self::LINKTYPE_TICKET|| $_linkType == self::LINKTYPE_TICKETPOST) {
            return true;
        }

        return false;
    }

    /**
     * Update the ticket note Record
     *
     * @author Varun Shoor
     * @param int $_editedStaffID The Staff ID editing the Note
     * @param string $_editedStaffName The Staff Name editing the Note
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function Update($_editedStaffID, $_editedStaffName, $_noteContents, $_noteColor = 1) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        $this->UpdatePool('isedited', '1');
        $this->UpdatePool('editedstaffid', $_editedStaffID);
        $this->UpdatePool('notecolor',  ($_noteColor));
        $this->UpdatePool('note', $_noteContents);
        $this->UpdatePool('editedstaffname', ReturnNone($_editedStaffName));
        $this->UpdatePool('editedtimeline', DATENOW);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Ticket Note record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Note_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketNoteID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Notes
     *
     * @author Varun Shoor
     * @param array $_ticketNoteIDList The Ticket Note ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketNoteIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketNoteIDList))
        {
            return false;
        }

        $_finalTicketNoteIDList = array();
        $_SWIFT->Database->Query("SELECT ticketnoteid FROM " . TABLE_PREFIX . "ticketnotes WHERE ticketnoteid IN (" . BuildIN($_ticketNoteIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketNoteIDList[] = (int) ($_SWIFT->Database->Record['ticketnoteid']);
        }

        if (!count($_finalTicketNoteIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketnotes WHERE ticketnoteid IN (" . BuildIN($_finalTicketNoteIDList) . ")");

        return true;
    }

    /**
     * Replace the current ticket id all tickets with the new one
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Old Ticket ID List
     * @param SWIFT_Ticket $_SWIFT_ParentTicketObject The Parent Ticket Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ReplaceTicket($_ticketIDList, SWIFT_Ticket $_SWIFT_ParentTicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ParentTicketObject instanceof SWIFT_Ticket || !$_SWIFT_ParentTicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_ticketIDList)) {
            return false;
        }


        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketnotes', array('linktypeid' => (int) ($_SWIFT_ParentTicketObject->GetTicketID())),
                'UPDATE', "linktype = '" . self::LINKTYPE_TICKET . "' AND linktypeid IN (" . BuildIN($_ticketIDList) . ")");

        return true;
    }

    /**
     * Update the note properties, this is used from rebuild properties option in tickets
     *
     * @author Varun Shoor
     * @param int $_ticketNoteID
     * @param string $_staffName
     * @param string $_editedStaffName
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateProperties($_ticketNoteID, $_staffName, $_editedStaffName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketnotes', array('staffname' => $_staffName, 'editedstaffname' => $_editedStaffName), 'UPDATE', "ticketnoteid = '" . $_ticketNoteID . "'");

        return true;
    }

    /**
     * Update the global property on all ticket notes, used to update stuff like departmentname etc.
     *
     * @author Varun Shoor
     * @param string $_updateFieldName
     * @param string $_updateFieldValue
     * @param string $_whereFieldName
     * @param string $_whereFieldValue
     * @param string $_extendedUpdateStatement (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateGlobalProperty($_updateFieldName, $_updateFieldValue, $_whereFieldName, $_whereFieldValue, $_extendedUpdateStatement = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_updateFieldName = $_SWIFT->Database->Escape($_updateFieldName);
        $_whereFieldName = $_SWIFT->Database->Escape($_whereFieldName);
        $_whereFieldValue = (int) ($_whereFieldValue); // Expected to be always int

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketnotes', array($_updateFieldName => $_updateFieldValue), 'UPDATE', $_whereFieldName . " = '" . $_SWIFT->Database->Escape($_whereFieldValue) . "'" . $_extendedUpdateStatement);

        return true;
    }

    /**
     * Retrieves the Ticket Note forstaffid
     *
     * @author Saloni Dhall
     * @param int $_ticketID
     * @return SWIFT_TicketNote|bool
     * @throws SWIFT_Note_Exception
     */
    public static function GetLastNote($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketNoteDetails  = $_SWIFT->Database->QueryFetch("SELECT ticketnoteid FROM " . TABLE_PREFIX . "ticketnotes
        WHERE linktype = '" . SWIFT_TicketNote::LINKTYPE_TICKET . "' AND linktypeid = '" . $_ticketID . "' ORDER BY ticketnoteid DESC");

        if (isset($_ticketNoteDetails['ticketnoteid']) && !empty($_ticketNoteDetails['ticketnoteid'])) {
                return new SWIFT_TicketNote($_ticketNoteDetails['ticketnoteid']);
        }

        return false;
    }
}
