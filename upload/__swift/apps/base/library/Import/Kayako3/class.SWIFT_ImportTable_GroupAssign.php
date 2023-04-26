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

use Base\Models\Staff\SWIFT_StaffGroupAssign;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: GroupAssigns
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_GroupAssign extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'GroupAssign');

        if (!$this->TableExists(TABLE_PREFIX . 'groupassigns')) {
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

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "groupassigns ORDER BY groupassignid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);
            $_newStaffGroupID = $this->ImportManager->GetImportRegistry()->GetKey('staffgroup', $this->DatabaseImport->Record['staffgroupid']);

            if ($_newDepartmentID == false || $_newStaffGroupID == false) {
                $this->GetImportManager()->AddToLog('Group Assign Import failed due to non existant key (incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Group Assign: ' . $_newDepartmentID . ' (Department) <> ' . $_newStaffGroupID . ' (Staff Group)', SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'groupassigns',
                array('departmentid' => $_newDepartmentID, 'staffgroupid' => $_newStaffGroupID), 'INSERT');
        }

        SWIFT_StaffGroupAssign::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "groupassigns");
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
