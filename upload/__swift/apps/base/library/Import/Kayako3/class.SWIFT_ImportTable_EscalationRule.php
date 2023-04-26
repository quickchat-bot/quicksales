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
use Tickets\Models\Escalation\SWIFT_EscalationRule;

/**
 * Import Table: EscalationRule
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_EscalationRule extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'EscalationRule');

        if (!$this->TableExists(TABLE_PREFIX . 'escalationrules')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Escalation:EscalationRule', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "escalationrules");
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "escalationnotifications");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "escalationrules ORDER BY escalationruleid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newSLAPlanID = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $this->DatabaseImport->Record['slaplanid']);
            if ($_newSLAPlanID == false) {
                $this->GetImportManager()->AddToLog('Escalation rule import failed for "' . htmlspecialchars($this->DatabaseImport->Record['title']) . '" due to non existant sla plan id (incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_ownerStaffID = $_ticketPriorityID = $_ticketStatusID = $_departmentID = -1;
            if ($this->DatabaseImport->Record['staffid'] != '0') {
                $_ownerStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);
            }

            if ($this->DatabaseImport->Record['priorityid'] != '0') {
                $_ticketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $this->DatabaseImport->Record['priorityid']);
            }

            if ($this->DatabaseImport->Record['ticketstatusid'] != '0') {
                $_ticketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $this->DatabaseImport->Record['ticketstatusid']);
            }

            if ($this->DatabaseImport->Record['departmentid'] != '0') {
                $_departmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);
            }

            $this->GetImportManager()->AddToLog('Importing Escalation Rule: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'escalationrules',
                array('title' => $this->DatabaseImport->Record['title'], 'dateline' => $this->DatabaseImport->Record['dateline'],
                    'slaplanid' => $_newSLAPlanID, 'tickettypeid' => '-1', 'addtags' => serialize(array()), 'removetags' => serialize(array()),
                    'staffid' => $_ownerStaffID, 'priorityid' => $_ticketPriorityID, 'ticketstatusid' => $_ticketStatusID,
                    'departmentid' => $_departmentID, 'ruletype' => SWIFT_EscalationRule::TYPE_BOTH, 'flagtype' => '0', 'newslaplanid' => '0'), 'INSERT');
            $_escalationRuleID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('escalationrule', $this->DatabaseImport->Record['escalationruleid'], $_escalationRuleID);
        }

        SWIFT_EscalationRule::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "escalationrules");
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
