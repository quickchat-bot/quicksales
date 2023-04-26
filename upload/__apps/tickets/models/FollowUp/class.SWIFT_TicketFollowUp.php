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

namespace Tickets\Models\FollowUp;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Follow-Up Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketFollowUp extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketfollowups';
    const PRIMARY_KEY        =    'ticketfollowupid';

    const TABLE_STRUCTURE    =    "ticketfollowupid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                executiondateline I DEFAULT '0' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dochangeproperties I2 DEFAULT '0' NOTNULL,
                                ownerstaffid F DEFAULT '0' NOTNULL,
                                departmentid F DEFAULT '0' NOTNULL,
                                ticketstatusid F DEFAULT '0' NOTNULL,
                                tickettypeid F DEFAULT '0' NOTNULL,
                                priorityid F DEFAULT '0' NOTNULL,
                                dochangeduedateline I2 DEFAULT '0' NOTNULL,
                                duedateline I DEFAULT '0' NOTNULL,
                                resolutionduedateline I DEFAULT '0' NOTNULL,
                                timeworked I DEFAULT '0' NOTNULL,
                                timebillable I DEFAULT '0' NOTNULL,
                                donote I2 DEFAULT '0' NOTNULL,
                                notetype C(50) DEFAULT '0' NOTNULL,
                                notecolor I2 DEFAULT '0' NOTNULL,
                                ticketnotes X2 NOTNULL,
                                doreply I2 DEFAULT '0' NOTNULL,
                                replycontents X2 NOTNULL,
                                doforward I2 DEFAULT '0' NOTNULL,
                                forwardemailto C(255) DEFAULT '' NOTNULL,
                                forwardcontents X2 NOTNULL";

    const INDEX_1            =    'ticketid';
    const INDEX_2            =    'executiondateline';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Ticket Follow-Up Object');
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketfollowups', $this->GetUpdatePool(), 'UPDATE', "ticketfollowupid = '" .
                (int) ($this->GetTicketFollowUpID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Follow-Up ID
     *
     * @author Varun Shoor
     * @return mixed "ticketfollowupid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketFollowUpID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketfollowupid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketfollowups WHERE ticketfollowupid = '" .
                    (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketfollowupid']) && !empty($_dataStore['ticketfollowupid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketfollowupid']) || empty($this->_dataStore['ticketfollowupid']))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
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
        if (!$this->GetIsClassLoaded())
        {
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Follow-Up ID
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Parent Ticket Object
     * @param int $_executionDateline The Dateline when this follow up should be executed
     * @param bool $_doChangeProperties Whether to change the ticket properties
     * @param int $_ownerStaffID The Owner Staff ID
     * @param int $_departmentID The New Department ID
     * @param int $_ticketStatusID The New Ticket Status ID
     * @param int $_ticketTypeID The New Ticket Type ID
     * @param int $_ticketPriorityID The New Ticket Priority ID
     * @param int $_doChangeDueDateline
     * @param int $_dueDateline
     * @param int $_resolutionDueDateline
     * @param int $_timeWorked Total Time Worked
     * @param int $_timeBillable Total Time Billable
     * @param bool $_doNote Create a ticket note?
     * @param string $_noteType The Note Type
     * @param int $_noteColor The Note Color
     * @param string $_ticketNotes The Ticket Notes
     * @param bool $_doReply Whether to create a reply
     * @param string $_replyContents The Reply Contents
     * @param bool $_doForward Whether to forward this ticket
     * @param string $_forwardEmailTo The Email Address to forward to
     * @param string $_forwardContents The Forward Contents
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The Creator Staff Object
     * @return int Ticket Follow-Up ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, $_executionDateline,
            $_doChangeProperties, $_ownerStaffID, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketPriorityID,
            $_doChangeDueDateline, $_dueDateline, $_resolutionDueDateline,
            $_timeWorked, $_timeBillable,
            $_doNote, $_noteType, $_noteColor, $_ticketNotes,
            $_doReply, $_replyContents,
            $_doForward, $_forwardEmailTo, $_forwardContents,
            SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || empty($_executionDateline))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = 0;
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded())
        {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketfollowups', array('dateline' => DATENOW, 'executiondateline' =>  ($_executionDateline),
            'ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()), 'staffid' => $_staffID, 'dochangeproperties' => (int) ($_doChangeProperties),
            'ownerstaffid' => $_ownerStaffID, 'departmentid' => $_departmentID, 'ticketstatusid' => $_ticketStatusID,
            'tickettypeid' => $_ticketTypeID, 'priorityid' => $_ticketPriorityID, 'dochangeduedateline' =>  ($_doChangeDueDateline),
            'duedateline' =>  ($_dueDateline), 'resolutionduedateline' =>  ($_resolutionDueDateline), 'timeworked' =>  ($_timeWorked),
            'timebillable' =>  ($_timeBillable), 'donote' => (int) ($_doNote), 'notetype' => $_noteType, 'notecolor' =>  ($_noteColor),
            'ticketnotes' => $_ticketNotes, 'doreply' => (int) ($_doReply), 'replycontents' => $_replyContents, 'doforward' => (int) ($_doForward),
            'forwardemailto' => $_forwardEmailTo, 'forwardcontents' => $_forwardContents), 'INSERT');
        $_ticketFollowUpID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketFollowUpID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketObject->MarkHasFollowUp();

        return $_ticketFollowUpID;
    }

    /**
     * Update the Ticket Follow-Up Record
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Parent Ticket Object
     * @param int $_executionDateline The Dateline when this follow up should be executed
     * @param bool $_doChangeProperties Whether to change the ticket properties
     * @param int $_ownerStaffID The Owner Staff ID
     * @param int $_departmentID The New Department ID
     * @param int $_ticketStatusID The New Ticket Status ID
     * @param int $_ticketTypeID The New Ticket Type ID
     * @param int $_ticketPriorityID The New Ticket Priority ID
     * @param int $_doChangeDueDateline
     * @param int $_dueDateline
     * @param int $_resolutionDueDateline
     * @param int $_timeWorked Total Time Worked
     * @param int $_timeBillable Total Time Billable
     * @param bool $_doNote Create a ticket note?
     * @param string $_noteType The Note Type
     * @param int $_noteColor The Note Color
     * @param string $_ticketNotes The Ticket Notes
     * @param bool $_doReply Whether to create a reply
     * @param string $_replyContents The Reply Contents
     * @param bool $_doForward Whether to forward this ticket
     * @param string $_forwardEmailTo The Email Address to forward to
     * @param string $_forwardContents The Forward Contents
     * @param SWIFT_Staff $_SWIFT_StaffObject (OPTIONAL) The Creator Staff Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update(SWIFT_Ticket $_SWIFT_TicketObject, $_executionDateline,
            $_doChangeProperties, $_ownerStaffID, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketPriorityID,
            $_doChangeDueDateline, $_dueDateline, $_resolutionDueDateline,
            $_timeWorked, $_timeBillable,
            $_doNote, $_noteType, $_noteColor, $_ticketNotes,
            $_doReply, $_replyContents,
            $_doForward, $_forwardEmailTo, $_forwardContents,
            SWIFT_Staff $_SWIFT_StaffObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || empty($_executionDateline)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffID = 0;
        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded())
        {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
        }

        $this->UpdatePool('executiondateline',  ($_executionDateline));
        $this->UpdatePool('ticketid', (int) ($_SWIFT_TicketObject->GetTicketID()));
        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('dochangeproperties', (int) ($_doChangeProperties));
        $this->UpdatePool('ownerstaffid', $_ownerStaffID);
        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('ticketstatusid', $_ticketStatusID);
        $this->UpdatePool('tickettypeid', $_ticketTypeID);
        $this->UpdatePool('priorityid', $_ticketPriorityID);
        $this->UpdatePool('dochangeduedateline',  ($_doChangeDueDateline));
        $this->UpdatePool('duedateline',  ($_dueDateline));
        $this->UpdatePool('resolutionduedateline',  ($_resolutionDueDateline));
        $this->UpdatePool('timeworked',  ($_timeWorked));
        $this->UpdatePool('timebillable',  ($_timeBillable));
        $this->UpdatePool('donote', (int) ($_doNote));
        $this->UpdatePool('notetype', $_noteType);
        $this->UpdatePool('notecolor',  ($_noteColor));
        $this->UpdatePool('ticketnotes', $_ticketNotes);
        $this->UpdatePool('doreply', (int) ($_doReply));
        $this->UpdatePool('replycontents', $_replyContents);
        $this->UpdatePool('doforward', (int) ($_doForward));
        $this->UpdatePool('forwardemailto', $_forwardEmailTo);
        $this->UpdatePool('forwardcontents', $_forwardContents);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Ticket Follow-Up record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketFollowUpID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Follow-Up's
     *
     * @author Varun Shoor
     * @param array $_ticketFollowUpIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketFollowUpIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketFollowUpIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketfollowups WHERE ticketfollowupid IN (" . BuildIN($_ticketFollowUpIDList) . ")");

        return true;
    }

    /**
     * Delete the Ticket Follow-Up's based on a list of Ticket IDs
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array(($_ticketIDList)))
        {
            return false;
        }

        $_ticketFollowUpIDList = array();
        $_SWIFT->Database->Query("SELECT ticketfollowupid FROM " . TABLE_PREFIX . "ticketfollowups WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketFollowUpIDList[] = (int) ($_SWIFT->Database->Record['ticketfollowupid']);
        }

        if (!count($_ticketFollowUpIDList))
        {
            return false;
        }

        self::DeleteList($_ticketFollowUpIDList);

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

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketfollowups', array('ticketid' => (int) ($_SWIFT_ParentTicketObject->GetTicketID())),
                'UPDATE', "ticketid IN (" . BuildIN($_ticketIDList) . ")");

        return true;
    }

    /**
     * Retrieve the follow up objects based on the given ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return array The Ticket Follow-Up Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketFollowUpContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfollowups WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketFollowUpContainer[$_SWIFT->Database->Record['ticketfollowupid']] = new SWIFT_TicketFollowUp(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        return $_ticketFollowUpContainer;
    }
}
?>
