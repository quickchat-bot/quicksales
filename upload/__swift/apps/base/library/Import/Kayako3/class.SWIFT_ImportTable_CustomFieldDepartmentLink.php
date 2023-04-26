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

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: CustomFieldDepartmentLink
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CustomFieldDepartmentLink extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CustomFieldDepartmentLink');

        if (!$this->TableExists(TABLE_PREFIX . 'customfielddeplinks')) {
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

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfielddeplinks");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "customfielddeplinks", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_customFieldGroupID = $this->GetImportManager()->GetImportRegistry()->GetKey('customfieldgroup', $this->DatabaseImport->Record['customfieldgroupid']);
            if ($_customFieldGroupID == false) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Department Link due to non existant custom field group (' . (int)($this->DatabaseImport->Record['customfieldgroupid']) . ')', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_departmentID = $this->GetImportManager()->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);
            if ($_departmentID == false) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Department Link due to non existant department (' . (int)($this->DatabaseImport->Record['departmentid']) . ')', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Custom Field Department Link ' . htmlspecialchars($this->DatabaseImport->Record['customfieldgroupid']) . ' (Custom Field Group) <> ' . htmlspecialchars($this->DatabaseImport->Record['departmentid']) . ' (Department)', SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'customfielddeplinks',
                array('customfieldgroupid' => $_customFieldGroupID, 'departmentid' => $_departmentID), 'INSERT');
        }

        SWIFT_CustomFieldManager::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "customfielddeplinks");
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
