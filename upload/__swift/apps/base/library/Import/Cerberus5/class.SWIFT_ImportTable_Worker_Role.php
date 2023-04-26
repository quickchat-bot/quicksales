<?php

namespace Base\Library\Import\Cerberus5;

use Base\Models\Staff\SWIFT_StaffGroup;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: Worker_Role
 *
 * @author Carlos Orozco
 */
class SWIFT_ImportTable_Worker_Role extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Carlos Orozco
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Worker_Role');

        if (!$this->TableExists('worker_role')) {
            $this->SetIsClassLoaded(false);
        }
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Carlos Orozco
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Cache the existing items
        if ($this->GetOffset() == 0) {
            $_existingStaffGroupContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY staffgroupid ASC");
            while ($this->Database->NextRecord()) {
                $_existingStaffGroupContainer[$this->Database->Record['staffgroupid']] = $this->Database->Record;
            }

            foreach ($_existingStaffGroupContainer as $_staffGroupContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('staffgrouptitle', mb_strtolower(trim($_staffGroupContainer['title'])), '1');
            }
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM worker_role ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingStaffGroupTitle = $this->ImportManager->GetImportRegistry()->GetKey('staffgrouptitle', mb_strtolower(trim($this->DatabaseImport->Record['name'])));

            // A record with same title exists?
            if ($_existingStaffGroupTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            $_logType = SWIFT_ImportManager::LOG_SUCCESS;

            $this->GetImportManager()->AddToLog('Importing Working Role: ' . htmlspecialchars($this->DatabaseImport->Record['name']), $_logType);

            $this->Database->AutoExecute(TABLE_PREFIX . 'staffgroup', array('title' => $this->DatabaseImport->Record['name'] . $_titleSuffix,
                'isadmin' => 0), 'INSERT');
            $_staffGroupID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('staffgroup', $this->DatabaseImport->Record['id'], $_staffGroupID);
        }

        SWIFT_StaffGroup::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Carlos Orozco
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM worker_role");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Carlos Orozco
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 20;
    }
}

?>
