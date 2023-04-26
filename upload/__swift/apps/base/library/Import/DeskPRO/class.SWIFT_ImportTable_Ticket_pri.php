<?php

namespace Base\Library\Import\DeskPRO;

use Base\Models\User\SWIFT_UserGroupAssign;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Tickets\Models\Priority\SWIFT_TicketPriority;

/**
 * Import Table: TicketPriority
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Ticket_pri extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Ticket_pri');

        if (!$this->TableExists('ticket_pri')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Priority:TicketPriority', APP_TICKETS);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketpriorities");
//        }

//         Cache the existing items
        if ($this->GetOffset() == 0) {
            $_existingPrioritiesContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY priorityid ASC");
            while ($this->Database->NextRecord()) {
                $_existingPrioritiesContainer[$this->Database->Record['priorityid']] = $this->Database->Record;
            }

            foreach ($_existingPrioritiesContainer as $_ePriorityContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('prioritytitle', mb_strtolower(trim($_ePriorityContainer['title'])), '1');
            }
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM ticket_pri ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingPriorityTitle = $this->ImportManager->GetImportRegistry()->GetKey('prioritytitle', mb_strtolower(trim($this->DatabaseImport->Record['name'])));

            // A record with same title exists?
            if ($_existingPriorityTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            $this->GetImportManager()->AddToLog('Importing Ticket Priority: ' . htmlspecialchars($this->DatabaseImport->Record['name']), SWIFT_ImportManager::LOG_SUCCESS);

            $_priorityType = SWIFT_PUBLIC;
//            if ($this->DatabaseImport->Record['type'] == 'private')
//            {
//                $_priorityType = SWIFT_PRIVATE;
//            }

//            Checking if the ticket priority has special permission on the usergroup
            if ($this->DatabaseImport->Record['perm_all'] == 0) {
//                Has special permissions
                $_uservisibilitycustom = 1;
            } else {
                $_uservisibilitycustom = 0;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketpriorities',
                array('title' => $this->DatabaseImport->Record['name'] . $_titleSuffix, 'displayorder' => (int)($this->DatabaseImport->Record['displayorder']),
                    'type' => $_priorityType, 'frcolorcode' => '#' . $this->DatabaseImport->Record['color'], 'iscustom' => '1',
                    'bgcolorcode' => '#' . $this->DatabaseImport->Record['color'], 'ismaster' => '0', 'uservisibilitycustom' => $_uservisibilitycustom), 'INSERT');
            $_ticketPriorityID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('ticketpriority', $this->DatabaseImport->Record['id'], $_ticketPriorityID);

//            Bring the ticket priorities permissions
            if ($_uservisibilitycustom == 1) {
                $this->DatabaseImport->Query("SELECT * FROM ticket_pri_permissions WHERE priority = " . (int)($this->DatabaseImport->Record['id']) . " ORDER BY priority ASC", 2);
                while ($this->DatabaseImport->NextRecord(2)) {
//                    Try to fetch the user group
                    $_userGroupID = $this->ImportManager->GetImportRegistry()->GetKey('usergroup', $this->DatabaseImport->Record2['usergroup']);
                    if ($_userGroupID == false) {
                        $_userGroupID = 2;
                    }
                    SWIFT_UserGroupAssign::Insert($_ticketPriorityID, SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY, $_userGroupID, false);
                }
            }
        }

        SWIFT_TicketPriority::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM ticket_pri");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Nicolás Ibarra Sabogal
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
