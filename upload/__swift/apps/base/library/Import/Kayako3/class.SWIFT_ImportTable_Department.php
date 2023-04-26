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

use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: Department
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Department extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Department');

        if (!$this->TableExists(TABLE_PREFIX . 'departments')) {
            $this->SetByPass(true);
        }
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

        // Cache the existing items
        if ($this->GetOffset() == 0) {
            $_existingDepartmentContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments ORDER BY departmentid ASC");
            while ($this->Database->NextRecord()) {
                $_existingDepartmentContainer[$this->Database->Record['departmentid']] = $this->Database->Record;
            }

            foreach ($_existingDepartmentContainer as $_departmentContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('departmenttitle', mb_strtolower(trim($_departmentContainer['title'])), '1');
            }
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "departments ORDER BY title ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingDepartmentTitle = $this->ImportManager->GetImportRegistry()->GetKey('departmenttitle', mb_strtolower(trim($this->DatabaseImport->Record['title'])));

            // A record with same title exists?
            if ($_existingDepartmentTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            if ($this->DatabaseImport->Record['departmentmodule'] == 'livesupport') {
                $this->DatabaseImport->Record['departmentmodule'] = 'livechat';
            }

            $this->GetImportManager()->AddToLog('Importing Department: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'departments',
                array('title' => $this->DatabaseImport->Record['title'] . $_titleSuffix, 'departmenttype' => $this->DatabaseImport->Record['departmenttype'],
                    'departmentapp' => $this->DatabaseImport->Record['departmentmodule'], 'displayorder' => $this->DatabaseImport->Record['displayorder']), 'INSERT');
            $_departmentID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('department', $this->DatabaseImport->Record['departmentid'], $_departmentID);
        }

        SWIFT_Department::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "departments");
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

        return 20;
    }
}

?>
