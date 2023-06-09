<?php

namespace Base\Library\Import\Cerberus5;

use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: Department
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Team extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Team');

        if (!$this->TableExists('team')) {
            $this->SetIsClassLoaded(false);
        }
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

        // Cache the existing items
        $dwk_actual_order = 0;
        if ($this->GetOffset() == 0) {
            $_existingDepartmentContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments ORDER BY departmentid ASC");
            while ($this->Database->NextRecord()) {
                $_existingDepartmentContainer[$this->Database->Record['departmentid']] = $this->Database->Record;
                $dwk_actual_order = $this->Database->Record['displayorder'];
            }

            foreach ($_existingDepartmentContainer as $_departmentContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('departmenttitle', mb_strtolower(trim($_departmentContainer['title'])), '1');
            }
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM team ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $dwk_actual_order = $dwk_actual_order + $_count;

            $_titleSuffix = '';
            $_existingDepartmentTitle = $this->ImportManager->GetImportRegistry()->GetKey('departmenttitle', mb_strtolower(trim($this->DatabaseImport->Record['name'])));

            // A record with same title exists?
            if ($_existingDepartmentTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            $this->GetImportManager()->AddToLog('Importing Department: ' . htmlspecialchars($this->DatabaseImport->Record['name']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'departments',
                array('title' => $this->DatabaseImport->Record['name'] . $_titleSuffix, 'departmenttype' => 'public',
                    'departmentapp' => 'tickets', 'displayorder' => (int)($dwk_actual_order)), 'INSERT');
            $_departmentID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('department', $this->DatabaseImport->Record['id'], $_departmentID);
        }

        SWIFT_Department::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM team");
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

        return 20;
    }
}

?>
