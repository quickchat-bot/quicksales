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
use Tickets\Models\Priority\SWIFT_TicketPriority;

/**
 * Import Table: TicketPriority
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketPriority extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketPriority');

        if (!$this->TableExists(TABLE_PREFIX . 'ticketpriorities')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Priority:TicketPriority', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketpriorities");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY priorityid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $this->GetImportManager()->AddToLog('Importing Ticket Priority: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $_priorityType = SWIFT_PUBLIC;
            if ($this->DatabaseImport->Record['type'] == 'private') {
                $_priorityType = SWIFT_PRIVATE;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketpriorities',
                array('title' => $this->DatabaseImport->Record['title'], 'displayorder' => (int)($this->DatabaseImport->Record['displayorder']),
                    'type' => $_priorityType, 'frcolorcode' => $this->DatabaseImport->Record['frcolorcode'], 'iscustom' => '1',
                    'bgcolorcode' => '', 'ismaster' => '0', 'uservisibilitycustom' => '0'), 'INSERT');
            $_ticketPriorityID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('ticketpriority', $this->DatabaseImport->Record['priorityid'], $_ticketPriorityID);
        }

        SWIFT_TicketPriority::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketpriorities");
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
