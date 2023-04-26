<?php

namespace Base\Library\Import\DeskPRO;

use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffAssign;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: Staff
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Tech extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Tech');

        if (!$this->TableExists('tech')) {
            $this->SetByPass(true);
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

        $_staffUsernameList = $_staffContainer = $_staffIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY staffid ASC");
        while ($this->Database->NextRecord()) {
            $_staffUsernameList[] = $this->Database->Record['username'];

            $_staffContainer[$this->Database->Record['staffid']] = $this->Database->Record;
            $_staffIDList[] = $this->Database->Record['staffid'];
        }

        // Cache the existing items
        if ($this->GetOffset() == 0) {
            $_existingStaffContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY staffid ASC");
            while ($this->Database->NextRecord()) {
                $_existingStaffContainer[$this->Database->Record['staffid']] = $this->Database->Record;
            }

            foreach ($_existingStaffContainer as $_eStaffContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('stafffullname', mb_strtolower(trim($_eStaffContainer['fullname'])), '1');
            }
        }


        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM tech ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingStaffFullName = $this->ImportManager->GetImportRegistry()->GetKey('stafffullname', mb_strtolower(trim($this->DatabaseImport->Record['name'])));

            // A record with same title exists?
            if ($_existingStaffFullName != false) {
                $_titleSuffix .= ' (Import)';
            }

            // Try to fetch the staff group
            if ($this->DatabaseImport->Record['is_admin'] != 1) {
//                Staff
                $_staffGroupID = 2;
            } else {
//                Admin
                $_staffGroupID = 1;
            }

            $this->GetImportManager()->AddToLog('Importing Staff: ' . htmlspecialchars($this->DatabaseImport->Record['name']), SWIFT_ImportManager::LOG_SUCCESS);

            if (in_array($this->DatabaseImport->Record['username'], $_staffUsernameList)) {
                $this->DatabaseImport->Record['username'] .= '_imp' . substr(BuildHash(), 0, 4);

                $this->GetImportManager()->AddToLog('Imported "' . htmlspecialchars($this->DatabaseImport->Record['name']) . '" Username as "' . htmlspecialchars($this->DatabaseImport->Record['username']) . '" due to conflict with an existing staff username', SWIFT_ImportManager::LOG_WARNING);
            }

            $_staffFirstName = $_staffLastName = '';
            if (strpos($this->DatabaseImport->Record['name'], ' ')) {
                $_staffNameContainer = explode(' ', $this->DatabaseImport->Record['name']);
                $_staffFirstName = $_staffNameContainer[0];
                unset($_staffNameContainer[0]);

                $_staffLastName = implode(' ', $_staffNameContainer);
            } else {
                $_staffFirstName = $this->DatabaseImport->Record['name'];
            }

            $_staffPassword = SWIFT_Staff::GetComputedPassword('password');

            $this->Database->AutoExecute(TABLE_PREFIX . 'staff',
                array('firstname' => $_staffFirstName, 'lastname' => $_staffLastName . $_titleSuffix, 'fullname' => $this->DatabaseImport->Record['name'] . $_titleSuffix,
                    'username' => $this->DatabaseImport->Record['username'], 'staffpassword' => $_staffPassword, 'islegacypassword' => '1',
                    'staffgroupid' => $_staffGroupID, 'email' => $this->DatabaseImport->Record['email'], 'mobilenumber' => '0',
                    'groupassigns' => '0', 'timezonephp' => 'GMT', 'isenabled' => $this->DatabaseImport->Record['active'],
                    'lastvisit' => $this->DatabaseImport->Record['last_activity'], 'lastvisit2' => $this->DatabaseImport->Record['last_activity'],
                    'lastactivity' => $this->DatabaseImport->Record['last_activity']), 'INSERT');
            $_staffID = $this->Database->InsertID();

//            Add the staff signature
            $this->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('dateline' => DATENOW, 'staffid' => $_staffID, 'signature' => ReturnNone($this->DatabaseImport->Record['signature'])), 'INSERT');

            $this->ImportManager->GetImportRegistry()->UpdateKey('staff', $this->DatabaseImport->Record['id'], $_staffID);

//            Assign the departments
            $_dwk_departments = $this->ImportManager->GetImportRegistry()->GetSection('department');

            foreach ($_dwk_departments as $old_deptID => $new_deptID) {
                if (!strpos($this->DatabaseImport->Record['cats_admin'], $old_deptID)) {
//                    Assign the department
                    $this->GetImportManager()->AddToLog('Importing Staff Assign: ' . $new_deptID . ' (Department) <> ' . $_staffID . ' (Staff)', SWIFT_ImportManager::LOG_SUCCESS);

                    $this->Database->AutoExecute(TABLE_PREFIX . 'staffassigns', array('departmentid' => $new_deptID, 'staffid' => $_staffID), 'INSERT');
                } else {
//                    Don't assign the department
                }
            }

        }

        SWIFT_Staff::RebuildCache();
        SWIFT_StaffAssign::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM tech");
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
