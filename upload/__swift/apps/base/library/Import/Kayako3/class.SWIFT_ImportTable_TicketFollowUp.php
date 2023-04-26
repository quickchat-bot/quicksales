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

namespace Base\Library\Import\QuickSupport3;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: TicketFollowUp
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketFollowUp extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketFollowUp');

        if (!$this->TableExists(TABLE_PREFIX . 'ticketfollowup')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        SWIFT_Loader::LoadModel('FollowUp:TicketFollowUp', APP_TICKETS);
        SWIFT_Loader::LoadModel('Note:TicketNoteManager', APP_TICKETS);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketfollowups");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketfollowup ORDER BY ticketfollowupid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $this->GetImportManager()->AddToLog('Importing Ticket Follow-Up ID: ' . htmlspecialchars($this->DatabaseImport->Record['ticketfollowupid']), SWIFT_ImportManager::LOG_SUCCESS);

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);
            $_staffFullName = $this->Language->Get('na');
            if (isset($_staffCache[$_newStaffID])) {
                $_staffFullName = $_staffCache[$_newStaffID]['fullname'];
            }

            $_newOwnerStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['ownerstaffid']);
            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);
            $_newTicketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $this->DatabaseImport->Record['ticketstatusid']);
            $_newTicketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $this->DatabaseImport->Record['priorityid']);

            $_doChangeProperties = false;

            if (!empty($_newOwnerStaffID) || !empty($_newDepartmentID) || !empty($_newTicketStatusID) || !empty($_newTicketPriorityID)) {
                $_doChangeProperties = true;
            }

            if (empty($_newOwnerStaffID)) {
                $_newOwnerStaffID = -1;
            }

            if (empty($_newDepartmentID)) {
                $_newDepartmentID = -1;
            }

            if (empty($_newTicketStatusID)) {
                $_newTicketStatusID = -1;
            }

            if (empty($_newTicketPriorityID)) {
                $_newTicketPriorityID = -1;
            }

            $_doChangeDueDateline = false;
            if ($this->DatabaseImport->Record['duedateline'] != '0') {
                $_doChangeDueDateline = true;
            }

            $_noteType = 'ticket';
            $_noteColor = 1;

            $_doNote = false;
            if ($this->DatabaseImport->Record['ticketnotes'] != '') {
                $_doNote = true;
            }

            $_doReply = false;
            if ($this->DatabaseImport->Record['replycontents'] != '') {
                $_doReply = true;
            }

            $_doForward = false;
            if ($this->DatabaseImport->Record['forwardcontents'] != '' && $this->DatabaseImport->Record['forwardemailto'] != '') {
                $_doForward = true;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketfollowups',
                array('dateline' => $this->DatabaseImport->Record['dateline'], 'executiondateline' => $this->DatabaseImport->Record['execdateline'],
                    'ticketid' => $this->DatabaseImport->Record['ticketid'], 'staffid' => $_newStaffID, 'dochangeproperties' => (int)($_doChangeProperties),
                    'ownerstaffid' => $_newOwnerStaffID, 'departmentid' => $_newDepartmentID, 'ticketstatusid' => $_newTicketStatusID,
                    'priorityid' => $_newTicketPriorityID, 'tickettypeid' => '-1', 'dochangeduedateline' => (int)($_doChangeDueDateline),
                    'duedateline' => $this->DatabaseImport->Record['duedateline'], 'resolutionduedateline' => '-1', 'timeworked' => $this->DatabaseImport->Record['timeworked'],
                    'timebillable' => $this->DatabaseImport->Record['timeworked'], 'donote' => (int)($_doNote), 'notetype' => $_noteType, 'notecolor' => $_noteColor,
                    'ticketnotes' => $this->DatabaseImport->Record['ticketnotes'], 'doreply' => (int)($_doReply), 'replycontents' => $this->DatabaseImport->Record['replycontents'],
                    'doforward' => (int)($_doForward), 'forwardemailto' => $this->DatabaseImport->Record['forwardemailto'], 'forwardcontents' => $this->DatabaseImport->Record['forwardcontents']
                ), 'INSERT');
        }

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketfollowup");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 500;
    }
}

?>
