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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Import\Kayako3;

use Base\Models\Staff\SWIFT_StaffGroup;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: Staff Group/Teams
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_StaffGroup extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'StaffGroup');

        if (!$this->TableExists(TABLE_PREFIX . 'staffgroup')) {
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

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY staffgroupid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingStaffGroupTitle = $this->ImportManager->GetImportRegistry()->GetKey('staffgrouptitle', mb_strtolower(trim($this->DatabaseImport->Record['title'])));

            // A record with same title exists?
            if ($_existingStaffGroupTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            $_logType = SWIFT_ImportManager::LOG_SUCCESS;

            $this->GetImportManager()->AddToLog('Importing Staff Group/Team: ' . htmlspecialchars($this->DatabaseImport->Record['title']), $_logType);

            $this->Database->AutoExecute(TABLE_PREFIX . 'staffgroup', array('title' => $this->DatabaseImport->Record['title'] . $_titleSuffix,
                'isadmin' => $this->DatabaseImport->Record['isadmin']), 'INSERT');
            $_staffGroupID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('staffgroup', $this->DatabaseImport->Record['staffgroupid'], $_staffGroupID);
        }

        SWIFT_StaffGroup::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staffgroup");
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
