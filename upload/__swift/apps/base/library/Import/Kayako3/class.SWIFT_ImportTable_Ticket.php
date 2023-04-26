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
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Import Table: Ticket
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Ticket extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Ticket');

        if (!$this->TableExists(TABLE_PREFIX . 'tickets')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickets");
        }

        $_count = 0;

        $_ticketContainer = $_ticketIDList = $_oldUserIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets ORDER BY ticketid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_ticketContainer[$this->DatabaseImport->Record['ticketid']] = $this->DatabaseImport->Record;
            $_ticketIDList[] = $this->DatabaseImport->Record['ticketid'];

            if ($this->DatabaseImport->Record['userid'] != '0') {
                $_oldUserIDList[] = $this->DatabaseImport->Record['userid'];
            }
        }

        $_ticketTypeContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "tickettypes WHERE ismaster = '1'");
        $_ticketTypeID = $_ticketTypeContainer['tickettypeid'];

        $_ticketStatusContainer = $_ticketPriorityContainer = $_staffContainer = $_departmentContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus");
        while ($this->Database->NextRecord()) {
            $_ticketStatusContainer[$this->Database->Record['ticketstatusid']] = $this->Database->Record;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities");
        while ($this->Database->NextRecord()) {
            $_ticketPriorityContainer[$this->Database->Record['priorityid']] = $this->Database->Record;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff");
        while ($this->Database->NextRecord()) {
            $_staffContainer[$this->Database->Record['staffid']] = $this->Database->Record;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments");
        while ($this->Database->NextRecord()) {
            $_departmentContainer[$this->Database->Record['departmentid']] = $this->Database->Record;
        }

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);

        foreach ($_ticketContainer as $_ticketID => $_ticket) {
            $_newTicketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $_ticket['ticketstatusid']);
            if (!$_newTicketStatusID) {
                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int)($_ticket['ticketid']) . ' failed due to non existant ticket status id: ' . htmlspecialchars($_ticket['ticketstatusid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
                continue;
            }

            $_newTicketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $_ticket['priorityid']);
            if (!$_newTicketPriorityID) {
                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int)($_ticket['ticketid']) . ' failed due to non existant ticket priority id: ' . htmlspecialchars($_ticket['priorityid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
                continue;
            }

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $_ticket['departmentid']);
            if (!$_newDepartmentID) {
                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int)($_ticket['ticketid']) . ' failed due to non existant ticket department id: ' . htmlspecialchars($_ticket['departmentid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
                continue;
            }

            $_newEmailQueueID = $this->ImportManager->GetImportRegistry()->GetKey('emailqueue', $_ticket['emailqueueid']);

            $_newUserID = 0;
            if (isset($_newUserIDList[$_ticket['userid']])) {
                $_newUserID = $_newUserIDList[$_ticket['userid']];
            }

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticket['staffid']);
            $_newOwnerStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticket['ownerstaffid']);

            $_ownerStaffName = $_ticketStatusTitle = $_priorityTitle = $_departmentTitle = '';
            if (isset($_ticketPriorityContainer[$_newTicketPriorityID])) {
                $_priorityTitle = $_ticketPriorityContainer[$_newTicketPriorityID]['title'];
            }

            if (isset($_ticketStatusContainer[$_newTicketStatusID])) {
                $_ticketStatusTitle = $_ticketStatusContainer[$_newTicketStatusID]['title'];
            }

            if (isset($_staffContainer[$_newOwnerStaffID])) {
                $_ownerStaffName = $_staffContainer[$_newOwnerStaffID]['fullname'];
            }

            if (isset($_departmentContainer[$_newDepartmentID])) {
                $_departmentTitle = $_departmentContainer[$_newDepartmentID]['title'];
            }

            $_assignStatus = false;
            if (!empty($_newOwnerStaffID)) {
                $_assignStatus = true;
            }

            $_newSLAPlanID = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $_ticket['slaplanid']);
            $_newTicketSLAPlanID = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $_ticket['ticketslaplanid']);

            $_newEditedByStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticket['editedbystaffid']);

            $_newCreator = SWIFT_Ticket::CREATOR_CLIENT;
            if ($_ticket['creator'] == '1') {
                $_newCreator = SWIFT_Ticket::CREATOR_STAFF;
            }

            $_newTemplateGroupID = $this->ImportManager->GetImportRegistry()->GetKey('templategroup', $_ticket['tgroupid']);

            $_newEscalationRuleID = $this->ImportManager->GetImportRegistry()->GetKey('escalationrule', $_ticket['escalationruleid']);

            $_creationMode = SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER;
            if ($_ticket['isemailed'] == '0' || !empty($_newEmailQueueID)) {
                $_creationMode = SWIFT_Ticket::CREATIONMODE_EMAIL;
            }

            $_ticketType = SWIFT_Ticket::TYPE_DEFAULT;
            if ($_ticket['isphonecall'] == '1') {
                $_ticketType = SWIFT_Ticket::TYPE_PHONE;
            }

            $_messageId = '';
            if (isset($_ticket['messageid']) && !empty($_ticket['messageid'])) {
                $_messageId = $_ticket['messageid'];
            }

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1938 Column 'escalatedtime' cannot be null (library/class.SWIFT.php:768)
             *
             */
            $_escalatedTime = 0;
            if (isset($_ticket['escalatedtime']) && !empty($_ticket['escalatedtime'])) {
                $_escalatedTime = $_ticket['escalatedtime'];
            }

            $_followupCount = 0;
            if (isset($_ticket['followupcount']) && !empty($_ticket['followupcount'])) {
                $_followupCount = $_ticket['followupcount'];
            }

            $this->GetImportManager()->AddToLog('Importing Ticket ID: ' . htmlspecialchars($_ticket['ticketid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'tickets',
                array('ticketid' => $_ticket['ticketid'], 'ticketmaskid' => $_ticket['ticketmaskid'], 'departmentid' => $_newDepartmentID, 'ticketstatusid' => $_newTicketStatusID,
                    'priorityid' => $_newTicketPriorityID, 'emailqueueid' => $_newEmailQueueID, 'userid' => $_newUserID, 'staffid' => $_newStaffID,
                    'ownerstaffid' => $_newOwnerStaffID, 'assignstatus' => (int)($_assignStatus), 'fullname' => $_ticket['fullname'], 'email' => $_ticket['email'],
                    'lastreplier' => $_ticket['lastreplier'],
                    'replyto' => $_ticket['replyto'], 'subject' => $_ticket['subject'], 'dateline' => $_ticket['dateline'], 'lastactivity' => $_ticket['lastactivity'],
                    'laststaffreplytime' => $_ticket['laststaffreplytime'], 'lastuserreplytime' => $_ticket['lastuserreplytime'], 'slaplanid' => $_newSLAPlanID, 'ticketslaplanid' => $_newTicketSLAPlanID,
                    'duetime' => $_ticket['duetime'], 'totalreplies' => $_ticket['totalreplies'], 'ipaddress' => $_ticket['ipaddress'], 'flagtype' => (int)($_ticket['flagtype']),
                    'hasnotes' => $_ticket['hasnotes'], 'hasattachments' => $_ticket['hasattachments'], 'isemailed' => $_ticket['isemailed'], 'edited' => (int)($_ticket['edited']),
                    'editedbystaffid' => $_newEditedByStaffID, 'editeddateline' => $_ticket['editeddateline'], 'creator' => $_newCreator, 'charset' => $_ticket['charset'],
                    'transferencoding' => $_ticket['transferencoding'], 'timeworked' => $_ticket['timeworked'], 'timebilled' => '0', 'dateicon' => $_ticket['dateicon'],
                    'lastpostid' => $_ticket['lastpostid'], 'firstpostid' => $_ticket['firstpostid'], 'tgroupid' => $_newTemplateGroupID, 'messageid' => $_messageId,
                    'escalationruleid' => $_newEscalationRuleID, 'hasdraft' => (int)($_ticket['hasdraft']), 'hasbilling' => $_ticket['hasbilling'], 'isphonecall' => $_ticket['isphonecall'],
                    'isescalated' => $_ticket['isescalated'], 'phoneno' => $_ticket['phoneno'], 'autoclosetimeline' => $_ticket['autoclosetimeline'], 'escalatedtime' => $_escalatedTime,
                    'followupcount' => $_followupCount, 'hasfollowup' => IIF($_followupCount > 0, '1', '0'), 'hasratings' => '0', 'tickethash' => BuildHash(),
                    'islinked' => '0', 'tickettype' => $_ticketType, 'tickettypeid' => $_ticketTypeID, 'creationmode' => $_creationMode,
                    'tickettypetitle' => $_ticketTypeContainer['title'], 'ticketstatustitle' => $_ticketStatusTitle, 'prioritytitle' => $_priorityTitle, 'ownerstaffname' => $_ownerStaffName, 'departmenttitle' => $_departmentTitle
                ), 'INSERT');
            $_ticketID = $this->Database->InsertID();

            if ($_ticketID != $_ticket['ticketid']) {
                $this->GetImportManager()->AddToLog('Import for Ticket ID #: ' . (int)($_ticket['ticketid']) . ' failed due to invalid autoincrement result, make sure that this desk is not live and no tickets are being created during the import process', SWIFT_ImportManager::LOG_FAILURE);
                break;
            }
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets");
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

        return 1000;
    }
}

?>
