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
use Tickets\Models\Status\SWIFT_TicketStatus;

/**
 * Import Table: TicketStatus
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketStatus extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketStatus');

        if (!$this->TableExists(TABLE_PREFIX . 'ticketstatus')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Status:TicketStatus', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketstatus");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY ticketstatusid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);

            $this->GetImportManager()->AddToLog('Importing Ticket Status: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $_statusType = SWIFT_PUBLIC;
            if ($this->DatabaseImport->Record['type'] == 'private') {
                $_statusType = SWIFT_PRIVATE;
            }

            $_markAsResolved = false;
            if ($this->DatabaseImport->Record['displayinmainlist'] == '0' && mb_strtolower(trim($this->DatabaseImport->Record['title'])) != 'on hold') {
                $_markAsResolved = true;
            }

            $_displayIcon = '{$themepath}icon_ticketstatusopen.png';
            $_statusBackgroundColor = '#4eafcb';
            if ($_markAsResolved == true) {
                $_statusBackgroundColor = '#36a148';
                $_displayIcon = '{$themepath}icon_ticketstatusclosed.png';
            }

            $_triggerSurvey = false;
            if ($_markAsResolved == true) {
                $_triggerSurvey = true;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketstatus',
                array('title' => $this->DatabaseImport->Record['title'], 'displayorder' => (int)($this->DatabaseImport->Record['displayorder']),
                    'iscustom' => '1', 'displayinmainlist' => (int)($this->DatabaseImport->Record['displayinmainlist']), 'ismaster' => '0',
                    'displaycount' => (int)($this->DatabaseImport->Record['displaycount']), 'statuscolor' => '#333333',
                    'departmentid' => $_newDepartmentID, 'statustype' => $_statusType, 'resetduetime' => (int)($this->DatabaseImport->Record['resetduetime']),
                    'markasresolved' => (int)($_markAsResolved), 'statusbgcolor' => $_statusBackgroundColor, 'displayicon' => $_displayIcon,
                    'triggersurvey' => (int)($_triggerSurvey), 'staffvisibilitycustom' => '0', 'type' => $_statusType), 'INSERT');
            $_ticketStatusID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('ticketstatus', $this->DatabaseImport->Record['ticketstatusid'], $_ticketStatusID);
        }

        SWIFT_TicketStatus::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketstatus");
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
