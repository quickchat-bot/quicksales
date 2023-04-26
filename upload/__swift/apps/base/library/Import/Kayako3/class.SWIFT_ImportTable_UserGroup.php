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

use Base\Models\User\SWIFT_UserGroup;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: UserGroup
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_UserGroup extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'UserGroup');

        if (!$this->TableExists(TABLE_PREFIX . 'usergroups')) {
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
            $_existingUserGroupContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY usergroupid ASC");
            while ($this->Database->NextRecord()) {
                $_existingUserGroupContainer[$this->Database->Record['usergroupid']] = $this->Database->Record;
            }

            foreach ($_existingUserGroupContainer as $_userGroupContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('usergrouptitle', mb_strtolower(trim($_userGroupContainer['title'])), '1');
            }
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY usergroupid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingUserGroupTitle = $this->ImportManager->GetImportRegistry()->GetKey('usergrouptitle', mb_strtolower(trim($this->DatabaseImport->Record['title'])));

            // A record with same title exists?
            if ($_existingUserGroupTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            $this->GetImportManager()->AddToLog('Importing User Group: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $_groupType = SWIFT_UserGroup::TYPE_REGISTERED;
            if ($this->DatabaseImport->Record['grouptype'] == 'guest') {
                $_groupType = SWIFT_UserGroup::TYPE_GUEST;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'usergroups', array('title' => $this->DatabaseImport->Record['title'] . $_titleSuffix, 'grouptype' => $_groupType), 'INSERT');
            $_userGroupID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('usergroup', $this->DatabaseImport->Record['usergroupid'], $_userGroupID);
        }

        SWIFT_UserGroup::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "usergroups");
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
