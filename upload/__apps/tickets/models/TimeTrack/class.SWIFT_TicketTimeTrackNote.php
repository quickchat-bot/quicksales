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

namespace Tickets\Models\TimeTrack;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Ticket Time Track Note Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketTimeTrackNote extends SWIFT_Model
{
    const TABLE_NAME        =    'tickettimetracknotes';
    const PRIMARY_KEY        =    'tickettimetracknoteid';

    const TABLE_STRUCTURE    =    "tickettimetracknoteid I PRIMARY AUTO NOTNULL,
                                tickettimetrackid I DEFAULT '0' NOTNULL,
                                notes X2";

    const INDEX_1            =    'tickettimetrackid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketTimeTrackNoteID The Time Tracking Note ID
     * @throws SWIFT_TimeTrack_Exception If the Record could not be loaded
     */
    public function __construct($_ticketTimeTrackNoteID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketTimeTrackNoteID)) {
            throw new SWIFT_TimeTrack_Exception('Failed to load Ticket Time Track Note ID: ' . $_ticketTimeTrackNoteID);
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
     * @throws SWIFT_TimeTrack_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracknotes', $this->GetUpdatePool(), 'UPDATE', "tickettimetracknoteid = '" .
                (int) ($this->GetTicketTimeTrackNoteID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Time Track Note ID
     *
     * @author Varun Shoor
     * @return mixed "tickettimetracknoteid" on Success, "false" otherwise
     * @throws SWIFT_TimeTrack_Exception If the Class is not Loaded
     */
    public function GetTicketTimeTrackNoteID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['tickettimetracknoteid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketTimeTrackNoteID The Ticket Time Track Note ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketTimeTrackNoteID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tickettimetracknotes WHERE tickettimetracknoteid = '" .
                $_ticketTimeTrackNoteID . "'");
        if (isset($_dataStore['tickettimetracknoteid']) && !empty($_dataStore['tickettimetracknoteid']))
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
     * @throws SWIFT_TimeTrack_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_TimeTrack_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_TimeTrack_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Time Track Note
     *
     * @author Varun Shoor
     * @param SWIFT_TicketTimeTrack $_SWIFT_TicketTimeTrackObject The SWIFT_TicketTimeTrack Object Pointer
     * @param string $_noteContents The Note Contents
     * @return int "$_ticketTimeTrackNoteID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create(SWIFT_TicketTimeTrack $_SWIFT_TicketTimeTrackObject, $_noteContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketTimeTrackObject instanceof SWIFT_TicketTimeTrack || !$_SWIFT_TicketTimeTrackObject->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracknotes', array(
            'tickettimetrackid' =>  $_SWIFT_TicketTimeTrackObject->GetTicketTimeTrackID(), 'notes' => $_noteContents), 'INSERT');
        $_ticketTimeTrackNoteID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketTimeTrackNoteID)
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketTimeTrackNoteID;
    }

    /**
     * Update the Note Record
     *
     * @author Varun Shoor
     * @param string $_noteContents The Note Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_TimeTrack_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_noteContents)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('notes', $_noteContents);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Ticket Time Track Note record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_TimeTrack_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketTimeTrackNoteID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Retrieve the SWIFT_TicketTimeTrackNote Object based on Ticket Time Track ID
     *
     * @author Varun Shoor
     * @param SWIFT_TicketTimeTrack $_SWIFT_TicketTimeTrackObject The SWIFT_TicketTimeTrack Object Pointer
     * @return SWIFT_TicketTimeTrackNote|null "SWIFT_TicketTimeTrackNote" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_TimeTrack_Exception If Invalid Data is Provided
     */
    public static function RetrieveObjectOnTicketTimeTrackID(SWIFT_TicketTimeTrack $_SWIFT_TicketTimeTrackObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketTimeTrackObject instanceof SWIFT_TicketTimeTrack || !$_SWIFT_TicketTimeTrackObject->GetIsClassLoaded()) {
            throw new SWIFT_TimeTrack_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketTimeTrackNoteContainer = $_SWIFT->Database->QueryFetch('SELECT tickettimetracknoteid FROM ' . TABLE_PREFIX . "tickettimetracknotes
            WHERE tickettimetrackid = '" .  $_SWIFT_TicketTimeTrackObject->GetTicketTimeTrackID() . "'");
        if (isset($_ticketTimeTrackNoteContainer['tickettimetracknoteid']) && !empty($_ticketTimeTrackNoteContainer['tickettimetracknoteid'])) {
            return new SWIFT_TicketTimeTrackNote($_ticketTimeTrackNoteContainer['tickettimetracknoteid']);
        }

        return null;
    }

    /**
     * Delete a list of Ticket Time Track Note ID's
     *
     * @author Varun Shoor
     * @param array $_ticketTimeTrackNoteIDList The Note ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketTimeTrackNoteIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketTimeTrackNoteIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickettimetracknotes WHERE tickettimetracknoteid IN (" .
                BuildIN($_ticketTimeTrackNoteIDList) . ")");

        return true;
    }

    /**
     * Delete the notes on basis of time tracking ids
     *
     * @author Varun Shoor
     * @param array $_ticketTimeTrackIDList The Ticket Time Tracking ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicketTimeTrack($_ticketTimeTrackIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketTimeTrackIDList))
        {
            return false;
        }

        $_ticketTimeTrackNoteIDList = array();
        $_SWIFT->Database->Query("SELECT tickettimetracknoteid FROM " . TABLE_PREFIX . "tickettimetracknotes WHERE tickettimetrackid IN (" .
                BuildIN($_ticketTimeTrackIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketTimeTrackNoteIDList[] = (int) ($_SWIFT->Database->Record['tickettimetracknoteid']);
        }

        if (!count($_ticketTimeTrackNoteIDList))
        {
            return false;
        }

        self::DeleteList($_ticketTimeTrackNoteIDList);

        return true;
    }
}
?>
