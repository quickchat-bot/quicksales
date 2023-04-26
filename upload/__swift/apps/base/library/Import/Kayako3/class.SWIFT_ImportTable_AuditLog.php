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
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;

/**
 * Import Table: AuditLog
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_AuditLog extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'AuditLog');

        if (!$this->TableExists(TABLE_PREFIX . 'auditlogs')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        SWIFT_Loader::LoadModel('AuditLog:TicketAuditLog', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketauditlogs");
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_count = 0;

        $_ticketAuditLogContainer = $_oldUserIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "auditlogs ORDER BY auditlogid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_ticketAuditLogContainer[$this->DatabaseImport->Record['auditlogid']] = $this->DatabaseImport->Record;

            if ($this->DatabaseImport->Record['userid'] != '0') {
                $_oldUserIDList[] = $this->DatabaseImport->Record['userid'];
            }
        }

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);
        $_newUserContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_newUserIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_newUserContainer[$this->Database->Record['userid']] = $this->Database->Record;
        }

        foreach ($_ticketAuditLogContainer as $_ticketAuditLogID => $_ticketAuditLog) {
            $this->GetImportManager()->AddToLog('Importing Ticket Audit Log ID: ' . htmlspecialchars($_ticketAuditLog['auditlogid']), SWIFT_ImportManager::LOG_SUCCESS);

            $_departmentTitle = '';

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $_ticketAuditLog['departmentid']);
            if (isset($_departmentCache[$_newDepartmentID])) {
                $_departmentTitle = $_departmentCache[$_newDepartmentID]['title'];
            }

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticketAuditLog['staffid']);
            $_staffFullName = '';
            if (isset($_staffCache[$_newStaffID])) {
                $_staffFullName = $_staffCache[$_newStaffID]['fullname'];
            }

            $_newUserID = 0;
            if (isset($_newUserIDList[$_ticketAuditLog['userid']])) {
                $_newUserID = $_newUserIDList[$_ticketAuditLog['userid']];
            }

            // define("LOG_STAFF", 1);
            // define("LOG_PARSER", 2);
            // define("LOG_CLIENT", 3);
            // define("LOG_ESCALATION", 4);
            // define("LOG_SYSTEM", 5);
            $_creatorType = SWIFT_TicketAuditLog::CREATOR_SYSTEM;
            $_creatorFullName = '';
            $_creatorID = '0';

            if ($_ticketAuditLog['logtype'] == '1') {
                $_creatorType = SWIFT_TicketAuditLog::CREATOR_STAFF;

                $_creatorFullName = $_staffFullName;
                $_creatorID = $_newStaffID;
            } elseif ($_ticketAuditLog['logtype'] == '3') {
                $_creatorType = SWIFT_TicketAuditLog::CREATOR_USER;
                $_creatorID = $_newUserID;

                if (!empty($_newUserID) && isset($_newUserContainer[$_newUserID])) {
                    $_creatorFullName = $_newUserContainer[$_newUserID]['fullname'];
                }
            }

            /**
             * ---------------------------------------------
             * Old Action Types
             * ---------------------------------------------
             */
            // define("ACTION_TICKETNOTE", 1);
            // define("ACTION_USERNOTE", 2);
            // define("ACTION_FLAG", 3);
            // define("ACTION_MOVE", 4);
            // define("ACTION_MERGE", 5);
            // define("ACTION_EDIT", 6);
            // define("ACTION_DELETEPOST", 7);
            // define("ACTION_STATUS", 8);
            // define("ACTION_PRIORITY", 9);
            // define("ACTION_ASSIGN", 10);
            // define("ACTION_NEWTICKET", 11);
            // define("ACTION_NEWREPLY", 12);
            // define("ACTION_ESCALATION", 13);
            // define("ACTION_DUE", 14);
            // define("ACTION_SLA", 15);
            // define("ACTION_XMLEXPORT", 16);
            // define("ACTION_PDFEXPORT", 17);
            // define("ACTION_DELETENOTE", 18);
            // define("ACTION_PRINT", 19);
            // define("ACTION_DELETETICKETPOST", 20);
            // define("ACTION_CLEARDRAFT", 21);
            // define("ACTION_FORWARD", 22);
            // define("ACTION_RECIPIENTDEL", 23);
            // define("ACTION_DELETETIMETRACK", 24);
            // define("ACTION_TIMETRACK", 25);
            // define("ACTION_PARSERRULE", 26);
            // define("ACTION_MERGE", 27);
            // define("ACTION_DELETETICKET", 28);

            $_actionType = SWIFT_TicketAuditLog::ACTION_UPDATETICKET;
            if ($_ticketAuditLog['actiontype'] == '3') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_UPDATEFLAG;
            } elseif ($_ticketAuditLog['actiontype'] == '4') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_UPDATEDEPARTMENT;
            } elseif ($_ticketAuditLog['actiontype'] == '5') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_MERGETICKET;
            } elseif ($_ticketAuditLog['actiontype'] == '7') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_DELETETICKETPOST;
            } elseif ($_ticketAuditLog['actiontype'] == '8') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_UPDATESTATUS;
            } elseif ($_ticketAuditLog['actiontype'] == '9') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_UPDATEPRIORITY;
            } elseif ($_ticketAuditLog['actiontype'] == '10') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_UPDATEOWNER;
            } elseif ($_ticketAuditLog['actiontype'] == '11') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_NEWTICKET;
            } elseif ($_ticketAuditLog['actiontype'] == '12') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_NEWTICKETPOST;
            } elseif ($_ticketAuditLog['actiontype'] == '15') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_UPDATESLA;
            } elseif ($_ticketAuditLog['actiontype'] == '20') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_DELETETICKETPOST;
            } elseif ($_ticketAuditLog['actiontype'] == '27') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_MERGETICKET;
            } elseif ($_ticketAuditLog['actiontype'] == '28') {
                $_actionType = SWIFT_TicketAuditLog::ACTION_DELETETICKET;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketauditlogs',
                array('ticketid' => $_ticketAuditLog['ticketid'], 'ticketpostid' => '0', 'departmentid' => $_newDepartmentID, 'departmenttitle' => $_departmentTitle,
                    'dateline' => $_ticketAuditLog['dateline'], 'creatortype' => $_creatorType, 'creatorid' => $_creatorID, 'creatorfullname' => $_creatorFullName,
                    'actiontype' => $_actionType, 'actionmsg' => $_ticketAuditLog['actionmsg'], 'valuetype' => SWIFT_TicketAuditLog::VALUE_NONE,
                    'oldvalueid' => '0', 'oldvaluestring' => '', 'newvalueid' => '0', 'newvaluestring' => '0', 'actionhash' => md5($_ticketAuditLog['dateline'])), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "auditlogs");
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

        return 3000;
    }
}

?>
