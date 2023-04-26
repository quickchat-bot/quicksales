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

namespace Base\Library\Import\QuickSupport3;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Base\Library\Rules\SWIFT_Rules;
use Tickets\Models\SLA\SWIFT_SLA;

/**
 * Import Table: SLAPlan
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_SLAPlan extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'SLAPlan');

        if (!$this->TableExists(TABLE_PREFIX . 'slaplans')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('SLA:SLA', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "slaplans");
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "slarulecriteria");
        }

        $_count = 0;

        $_slaPlanContainer = $_slaPlanIDList = array();
        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "slaplans ORDER BY slaplanid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_slaPlanContainer[$this->DatabaseImport->Record['slaplanid']] = $this->DatabaseImport->Record;
            $_slaPlanIDList[] = $this->DatabaseImport->Record['slaplanid'];
            $_slaPlanContainer[$this->DatabaseImport->Record['slaplanid']]['ticketstatusidlist'] = array();
            $_slaPlanContainer[$this->DatabaseImport->Record['slaplanid']]['ticketpriorityidlist'] = array();
        }

        $this->DatabaseImport->Query("SELECT * FROM " . TABLE_PREFIX . "slaplanprioritylink WHERE slaplanid IN (" . BuildIN($_slaPlanIDList) . ")");
        while ($this->DatabaseImport->NextRecord()) {
            $_slaPlanContainer[$this->DatabaseImport->Record['slaplanid']]['ticketpriorityidlist'][] = $this->DatabaseImport->Record['priorityid'];
        }

        $this->DatabaseImport->Query("SELECT * FROM " . TABLE_PREFIX . "slaplanstatuslink WHERE slaplanid IN (" . BuildIN($_slaPlanIDList) . ")");
        while ($this->DatabaseImport->NextRecord()) {
            $_slaPlanContainer[$this->DatabaseImport->Record['slaplanid']]['ticketstatusidlist'][] = $this->DatabaseImport->Record['ticketstatusid'];
        }

        foreach ($_slaPlanContainer as $_slaPlan) {
            $_count++;

            $_newSLAScheduleID = $this->ImportManager->GetImportRegistry()->GetKey('slaschedule', $_slaPlan['slascheduleid']);
            if ($_newSLAScheduleID == false) {
                $this->GetImportManager()->AddToLog('SLA Plan import failed for "' . htmlspecialchars($_slaPlan['title']) . '" due to non existant SLA Schedule (incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing SLA Plan: ' . htmlspecialchars($_slaPlan['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'slaplans',
                array('title' => $_slaPlan['title'], 'slascheduleid' => $_newSLAScheduleID, 'overduehrs' => (int)($_slaPlan['overduehrs']),
                    'resolutionduehrs' => ((int)($_slaPlan['overduehrs']) * 3), 'isenabled' => '1', 'sortorder' => $_count,
                    'dateline' => (int)($_slaPlan['dateline']), 'ruletype' => SWIFT_Rules::RULE_MATCHEXTENDED), 'INSERT');
            $_slaPlanID = $this->Database->InsertID();

            // Insert Linked Departments
            if ($_slaPlan['departmentid'] != '0') {
                $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $_slaPlan['departmentid']);
                if ($_newDepartmentID != false) {
                    $this->Database->AutoExecute(TABLE_PREFIX . 'slarulecriteria',
                        array('slaplanid' => $_slaPlanID, 'name' => SWIFT_SLA::SLA_TICKETDEPARTMENT, 'ruleop' => SWIFT_SLA::OP_EQUAL,
                            'rulematch' => $_newDepartmentID, 'rulematchtype' => SWIFT_Rules::RULE_MATCHALL), 'INSERT');
                }
            }

            // Insert Linked Ticket Status
            foreach ($_slaPlan['ticketstatusidlist'] as $_ticketStatusID) {
                $_newTicketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $_ticketStatusID);
                if ($_newTicketStatusID == false) {
                    continue;
                }

                $this->Database->AutoExecute(TABLE_PREFIX . 'slarulecriteria',
                    array('slaplanid' => $_slaPlanID, 'name' => SWIFT_SLA::SLA_TICKETSTATUS, 'ruleop' => SWIFT_SLA::OP_EQUAL,
                        'rulematch' => $_newTicketStatusID, 'rulematchtype' => SWIFT_Rules::RULE_MATCHANY), 'INSERT');
            }

            // Insert Linked Ticket Priorities
            foreach ($_slaPlan['ticketpriorityidlist'] as $_ticketPriorityID) {
                $_newTicketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $_ticketPriorityID);
                if ($_newTicketPriorityID == false) {
                    continue;
                }

                $this->Database->AutoExecute(TABLE_PREFIX . 'slarulecriteria',
                    array('slaplanid' => $_slaPlanID, 'name' => SWIFT_SLA::SLA_TICKETPRIORITY, 'ruleop' => SWIFT_SLA::OP_EQUAL,
                        'rulematch' => $_newTicketPriorityID, 'rulematchtype' => SWIFT_Rules::RULE_MATCHANY), 'INSERT');
            }

            $this->ImportManager->GetImportRegistry()->UpdateKey('slaplan', $_slaPlan['slaplanid'], $_slaPlanID);
        }

        SWIFT_SLA::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "slaplans");
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
