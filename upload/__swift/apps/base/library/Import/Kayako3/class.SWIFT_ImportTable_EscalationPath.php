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

namespace Base\Library\Import\Kayako3;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: EscalationPath
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_EscalationPath extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'EscalationPath');

        if (!$this->TableExists(TABLE_PREFIX . 'escalationpaths')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "escalationpaths");
        }

        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_escalationRuleCache = $this->Cache->Get('escalationrulecache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "escalationpaths ORDER BY escalationpathid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $this->GetImportManager()->AddToLog('Importing Escalation Path ID: ' . htmlspecialchars($this->DatabaseImport->Record['escalationpathid']), SWIFT_ImportManager::LOG_SUCCESS);

            $_newSLAPlanTitle = $_escalationRuleTitle = $_ownerStaffName = $_departmentTitle = $_ticketStatusTitle = $_ticketPriorityTitle = '';

            $_newSLAPlanID = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $this->DatabaseImport->Record['slaplanid']);
            if (isset($_slaPlanCache[$_newSLAPlanID])) {
                $_newSLAPlanTitle = $_slaPlanCache[$_newSLAPlanID]['title'];
            }

            $_newEscalationRuleID = $this->ImportManager->GetImportRegistry()->GetKey('escalationrule', $this->DatabaseImport->Record['escalationruleid']);
            if (isset($_escalationRuleCache[$_newEscalationRuleID])) {
                $_escalationRuleTitle = $_escalationRuleCache[$_newEscalationRuleID]['title'];
            }

            $_newOwnerStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['ownerstaffid']);
            if (isset($_staffCache[$_newOwnerStaffID])) {
                $_ownerStaffName = $_staffCache[$_newOwnerStaffID]['fullname'];
            }

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);
            if (isset($_departmentCache[$_newDepartmentID])) {
                $_departmentTitle = $_departmentCache[$_newDepartmentID]['title'];
            }

            $_newTicketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $this->DatabaseImport->Record['ticketstatusid']);
            if (isset($_ticketStatusCache[$_newTicketStatusID])) {
                $_ticketStatusTitle = $_ticketStatusCache[$_newTicketStatusID]['title'];
            }

            $_newTicketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $this->DatabaseImport->Record['priorityid']);
            if (isset($_ticketPriorityCache[$_newTicketPriorityID])) {
                $_ticketPriorityTitle = $_ticketPriorityCache[$_newTicketPriorityID]['title'];
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'escalationpaths',
                array('dateline' => $this->DatabaseImport->Record['dateline'], 'ticketid' => (int)($this->DatabaseImport->Record['ticketid']),
                    'slaplanid' => $_newSLAPlanID, 'slaplantitle' => $_newSLAPlanTitle, 'escalationruleid' => $_newEscalationRuleID,
                    'escalationruletitle' => $_escalationRuleTitle, 'ownerstaffid' => $_newOwnerStaffID, 'ownerstaffname' => $_ownerStaffName,
                    'departmentid' => $_newDepartmentID, 'departmenttitle' => $_departmentTitle, 'ticketstatusid' => $_newTicketStatusID,
                    'ticketstatustitle' => $_ticketStatusTitle, 'priorityid' => $_newTicketPriorityID, 'prioritytitle' => $_ticketPriorityTitle,
                    'tickettypeid' => '0', 'tickettypetitle' => '', 'flagtype' => $this->DatabaseImport->Record['flagtype']), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "escalationpaths");
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
