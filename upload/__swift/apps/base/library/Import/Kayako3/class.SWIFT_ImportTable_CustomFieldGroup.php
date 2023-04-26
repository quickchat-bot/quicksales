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
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: CustomFieldGroup
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CustomFieldGroup extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CustomFieldGroup');

        if (!$this->TableExists(TABLE_PREFIX . 'customfieldgroups')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldgroups");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups ORDER BY title ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_groupType = false;

            // User Registration
            if ($this->DatabaseImport->Record['grouptype'] == '1') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_USER;

                // User Groups
            } elseif ($this->DatabaseImport->Record['grouptype'] == '2') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION;

                // Staff Ticket Creation
            } elseif ($this->DatabaseImport->Record['grouptype'] == '3') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_STAFFTICKET;

                // User Ticket Creation
            } elseif ($this->DatabaseImport->Record['grouptype'] == '4') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_USERTICKET;

                // Staff & User Ticket Creation
            } elseif ($this->DatabaseImport->Record['grouptype'] == '9') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET;

                // Ticket Time Tracking
            } elseif ($this->DatabaseImport->Record['grouptype'] == '5') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_TIMETRACK;

            } else {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Group due to unsupported group type: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Custom Field Group: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldgroups',
                array('title' => $this->DatabaseImport->Record['title'], 'grouptype' => $_groupType, 'displayorder' => $this->DatabaseImport->Record['displayorder']), 'INSERT');
            $_customFieldGroupID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('customfieldgroup', $this->DatabaseImport->Record['customfieldgroupid'], $_customFieldGroupID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "customfieldgroups");
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
