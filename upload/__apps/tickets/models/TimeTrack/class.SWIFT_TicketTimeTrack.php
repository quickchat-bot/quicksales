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

namespace Tickets\Models\TimeTrack;

use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Ticket Time Tracking Management Class
 *
 * @method int GetTicketTimeTrackID()
 * @author Varun Shoor
 */
class SWIFT_TicketTimeTrack extends SWIFT_Model
{
    const TABLE_NAME        =    'tickettimetracks';
    const PRIMARY_KEY        =    'tickettimetrackid';

    const TABLE_STRUCTURE    =    "tickettimetrackid I PRIMARY AUTO NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                workdateline I DEFAULT '0' NOTNULL,
                                creatorstaffid I DEFAULT '0' NOTNULL,
                                creatorstaffname C(255) DEFAULT '' NOTNULL,
                                timespent I DEFAULT '0' NOTNULL,
                                timebillable I DEFAULT '0' NOTNULL,
                                isedited I2 DEFAULT '0' NOTNULL,
                                editedstaffid I DEFAULT '0' NOTNULL,
                                editedstaffname C(255) DEFAULT '' NOTNULL,
                                editedtimeline I DEFAULT '0' NOTNULL,
                                notecolor I DEFAULT '0' NOTNULL,
                                workerstaffid I DEFAULT '0' NOTNULL,
                                workerstaffname C(255) DEFAULT '' NOTNULL";

    const INDEX_1            =    'ticketid';


    protected $_dataStore = array();

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
     * Create a new ticket time tracking entry
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket object
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object
     * @param int $_timeSpent The Time Spent in SECONDS
     * @param int $_timeBillable The Time Billable in SECONDS
     * @param int $_noteColor The Note Color
     * @param string $_noteContents The Note Contents
     * @param SWIFT_Staff $_SWIFT_StaffObject_Worker The Worker Staff Object
     * @param int $_workDateline The Work Execution Date
     * @param int $_billDateline The Billing Date
     * @return SWIFT_TicketTimeTrack|null "$_SWIFT_TicketTimeTrackObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_timeSpent, $_timeBillable, $_noteColor,
            $_noteContents, SWIFT_Staff $_SWIFT_StaffObject_Worker, $_workDateline, $_billDateline)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_timeSpent = ($_timeSpent);
        $_timeBillable = ($_timeBillable);
        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded() ||
                !$_SWIFT_StaffObject_Worker instanceof SWIFT_Staff || !$_SWIFT_StaffObject_Worker->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_INVALIDDATA);
        }

        $_workerStaffID = false;
        $_workerStaffName = '';
        if ($_SWIFT_StaffObject_Worker instanceof SWIFT_Staff && $_SWIFT_StaffObject_Worker->GetIsClassLoaded()) {
            $_workerStaffID = ($_SWIFT_StaffObject_Worker->GetStaffID());
            $_workerStaffName = $_SWIFT_StaffObject_Worker->GetProperty('fullname');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracks', array('ticketid' => ($_SWIFT_TicketObject->GetTicketID()),
                'dateline' => ($_billDateline), 'creatorstaffid' => ($_SWIFT_StaffObject->GetStaffID()),
                'creatorstaffname' => $_SWIFT_StaffObject->GetProperty('fullname'), 'timespent' => ($_timeSpent),
                'timebillable' => ($_timeBillable), 'notecolor' => ($_noteColor), 'workerstaffid' => $_workerStaffID,
                'workerstaffname' => $_workerStaffName, 'workdateline' => ($_workDateline)), 'INSERT');
        $_ticketTimeTrackID = ($_SWIFT->Database->Insert_ID());

        if (!$_ticketTimeTrackID)
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CREATEFAILED);
        }

        // Load the object
        $_SWIFT_TicketTimeTrackObject = new SWIFT_TicketTimeTrack(new SWIFT_DataID($_ticketTimeTrackID));
        if (!$_SWIFT_TicketTimeTrackObject instanceof SWIFT_TicketTimeTrack || !$_SWIFT_TicketTimeTrackObject->GetIsClassLoaded()) {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CREATEFAILED);
        }

        SWIFT_TicketTimeTrackNote::Create($_SWIFT_TicketTimeTrackObject, $_noteContents);

        // Mark the ticket with hasbilling flag
        $_SWIFT_TicketObject->MarkHasBilling();

        $_ticketTimeWorked = $_SWIFT_TicketObject->GetProperty('timeworked');
        $_ticketTimeBilled = $_SWIFT_TicketObject->GetProperty('timebilled');

        $_SWIFT_TicketObject->UpdateTimeTrack(($_ticketTimeWorked + $_timeSpent), ($_ticketTimeBilled + $_timeBillable));

        SWIFT_Ticket::AddToWorkflowQueue($_SWIFT_TicketObject);

        return $_SWIFT_TicketTimeTrackObject;
    }

    /**
     * Update the Ticket Time Track Record
     *
     * @author Varun Shoor
     * @param int $_timeSpent The Time Spent in SECONDS
     * @param int $_timeBillable The Time Billable in SECONDS
     * @param int $_noteColor The Note Color
     * @param string $_noteContents The Note Contents
     * @param SWIFT_Staff $_SWIFT_StaffObject_Worker The Worker Staff Object
     * @param int $_workDateline The Work Execution Date
     * @param int $_billDateline The Billing Date
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object of the staff who edited this time track entry
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Update($_timeSpent, $_timeBillable, $_noteColor, $_noteContents, SWIFT_Staff $_SWIFT_StaffObject_Worker, $_workDateline,
            $_billDateline, SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        $_timeSpent = ($_timeSpent);
        $_timeBillable = ($_timeBillable);
        $_noteColor = self::GetSanitizedNoteColor($_noteColor);

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_TimeTrack_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_workerStaffID = false;
        $_workerStaffName = '';
        if ($_SWIFT_StaffObject_Worker instanceof SWIFT_Staff && $_SWIFT_StaffObject_Worker->GetIsClassLoaded()) {
            $_workerStaffID = ($_SWIFT_StaffObject_Worker->GetStaffID());
            $_workerStaffName = $_SWIFT_StaffObject_Worker->GetProperty('fullname');
        }

        $this->UpdatePool('timespent', ($_timeSpent));
        $this->UpdatePool('timebillable', ($_timeBillable));
        $this->UpdatePool('notecolor', ($_noteColor));
        $this->UpdatePool('isedited', '1');
        $this->UpdatePool('workerstaffid', $_workerStaffID);
        $this->UpdatePool('workerstaffname', $_workerStaffName);
        $this->UpdatePool('dateline', ($_billDateline));
        $this->UpdatePool('workdateline', ($_workDateline));
        $this->UpdatePool('editedtimeline', DATENOW);

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded())
        {
            $this->UpdatePool('editedstaffname', $_SWIFT_StaffObject->GetProperty('fullname'));
            $this->UpdatePool('editedstaffid', ($_SWIFT_StaffObject->GetStaffID()));
        }
        $this->ProcessUpdatePool();

        $_SWIFT_TicketTimeTrackNoteObject = SWIFT_TicketTimeTrackNote::RetrieveObjectOnTicketTimeTrackID($this);
        if (!$_SWIFT_TicketTimeTrackNoteObject instanceof SWIFT_TicketTimeTrackNote || !$_SWIFT_TicketTimeTrackNoteObject->GetIsClassLoaded()) {
            throw new SWIFT_TimeTrack_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketTimeTrackNoteObject->Update($_noteContents);

        return true;
    }

    /**
     * Delete a list of Ticket time tracking entries
     *
     * @author Varun Shoor
     * @param array $_ticketTimeTrackIDList The Ticket Time Tracking ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketTimeTrackIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketTimeTrackIDList))
        {
            return false;
        }

        parent::DeleteList($_ticketTimeTrackIDList);

        // Clear the related notes
        SWIFT_TicketTimeTrackNote::DeleteOnTicketTimeTrack($_ticketTimeTrackIDList);

        return true;
    }

    /**
     * Delete the time tracking entries for a ticket
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_ticketTimeTrackIDList = array();
        $_SWIFT->Database->Query("SELECT tickettimetrackid FROM " . TABLE_PREFIX . "tickettimetracks WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketTimeTrackIDList[] = ($_SWIFT->Database->Record['tickettimetrackid']);
        }

        if (!count($_ticketTimeTrackIDList))
        {
            return false;
        }

        self::DeleteList($_ticketTimeTrackIDList);

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

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracks', array('ticketid' => ($_SWIFT_ParentTicketObject->GetTicketID())),
                'UPDATE', "ticketid IN (" . BuildIN($_ticketIDList) . ")");

        return true;
    }

    /**
     * Retrieve the time tracking count for the user
     *
     * @author Varun Shoor
     * @return int The Count
     * @throws SWIFT_TimeTrack_Exception If Invalid Data is Provided
     */
    public static function GetTimeTrackCountOnUser(SWIFT_User $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_TimeTrack_Exception(SWIFT_INVALIDDATA);
        }

        $_timeTrackCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickettimetracks WHERE ticketid IN (" . BuildIN(SWIFT_Ticket::GetTicketIDListOnUser($_SWIFT_UserObject)) . ")");
        if (isset($_timeTrackCount['totalitems']))
        {
            return ($_timeTrackCount['totalitems']);
        }

        return 0;
    }

    /**
     * Update the time track properties, this is used from the rebuildproperties action in tickets model.
     *
     * @author Varun Shoor
     * @param int $_ticketTimeTrackID
     * @param string $_creatorStaffName
     * @param string $_editedStaffName
     * @param string $_workerStaffName
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateProperties($_ticketTimeTrackID, $_creatorStaffName, $_editedStaffName, $_workerStaffName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracks', array('creatorstaffname' => $_creatorStaffName, 'editedstaffname' => $_editedStaffName, 'workerstaffname' => $_workerStaffName), 'UPDATE', "tickettimetrackid = '" . $_ticketTimeTrackID . "'");

        return true;
    }

    /**
     * Update the global property on all ticket time tracks, used to update stuff like departmentname etc.
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
        $_whereFieldValue = ($_whereFieldValue); // Expected to be always int

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracks', array($_updateFieldName => $_updateFieldValue), 'UPDATE', $_whereFieldName . " = '" . $_SWIFT->Database->Escape($_whereFieldValue) . "'" . $_extendedUpdateStatement);

        return true;
    }
}
?>
