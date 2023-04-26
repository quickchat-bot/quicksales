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

/**
 * Import Table: TicketTimeTrack
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketTimeTrack extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketTimeTrack');

        if (!$this->TableExists(TABLE_PREFIX . 'tickettimetrack')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        SWIFT_Loader::LoadModel('TimeTrack:TicketTimeTrack', APP_TICKETS);
        SWIFT_Loader::LoadModel('TimeTrack:TicketTimeTrackNote', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickettimetracks");
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickettimetracknotes");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickettimetrack ORDER BY timetrackid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $this->GetImportManager()->AddToLog('Importing Ticket Time Track ID: ' . htmlspecialchars($this->DatabaseImport->Record['timetrackid']), SWIFT_ImportManager::LOG_SUCCESS);

            $_newCreatorStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['creatorstaffid']);
            $_staffFullName = $this->Language->Get('na');
            if (isset($_staffCache[$_newCreatorStaffID])) {
                $_staffFullName = $_staffCache[$_newCreatorStaffID]['fullname'];
            }

            $_timeBillable = round((int)($this->DatabaseImport->Record['timebillable']) * 60);
            $_timeSpent = round((int)($this->DatabaseImport->Record['timespent']) * 60);

            $this->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracks',
                array('ticketid' => $this->DatabaseImport->Record['ticketid'], 'dateline' => $this->DatabaseImport->Record['dateline'], 'workdateline' => $this->DatabaseImport->Record['dateline'],
                    'creatorstaffid' => $_newCreatorStaffID, 'creatorstaffname' => $_staffFullName, 'timespent' => $_timeSpent,
                    'timebillable' => $_timeBillable, 'isedited' => '0', 'editedstaffid' => '0', 'editedstaffname' => '', 'editedtimeline' => '0',
                    'notecolor' => '1', 'workerstaffid' => $_newCreatorStaffID, 'workerstaffname' => $_staffFullName), 'INSERT');
            $_ticketTimeTrackID = $this->Database->Insert_ID();

            $this->Database->AutoExecute(TABLE_PREFIX . 'tickettimetracknotes',
                array('tickettimetrackid' => $_ticketTimeTrackID, 'notes' => $this->DatabaseImport->Record['notes']), 'INSERT');

            $this->ImportManager->GetImportRegistry()->UpdateKey('timetrack', $this->DatabaseImport->Record['timetrackid'], $_ticketTimeTrackID, true);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickettimetrack");
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
