<?php

namespace Base\Library\Import\Cerberus5;

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
class SWIFT_ImportTable_Worker extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Worker');

        if (!$this->TableExists('worker')) {
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

        $this->DatabaseImport->QueryLimit("SELECT * FROM worker ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingStaffFullName = $this->ImportManager->GetImportRegistry()->GetKey('stafffullname', mb_strtolower(trim($this->DatabaseImport->Record['first_name']) . " " . trim($this->DatabaseImport->Record['last_name'])));

            // A record with same title exists?
            if ($_existingStaffFullName != false) {
                $_titleSuffix .= ' (Import)';
            }

            // Try to fetch the staff group
            $_staffGroupID = 2;
            $this->DatabaseImport->QueryLimit("SELECT * FROM worker_to_role WHERE worker_id = " . (int)($this->DatabaseImport->Record['id']) . " ORDER BY role_id ASC", 1, 0, 2);
            while ($this->DatabaseImport->NextRecord(2)) {
                $_staffGroupID = $this->ImportManager->GetImportRegistry()->GetKey('staffgroup', $this->DatabaseImport->Record2['role_id']);
            }

//            Cerberus 5 is_disabled
            if ($this->DatabaseImport->Record['is_disabled'] == 0) {
                $_isenabled = 1;
            } else {
                $_isenabled = 0;
            }

//            Cerberus 5 is_disabled
            if (!empty($this->DatabaseImport->Record['last_activity_date'])) {
                $_dateline = $this->DatabaseImport->Record['last_activity_date'];
            } else {
                $_dateline = DATENOW;
            }

            $this->GetImportManager()->AddToLog('Importing Staff: ' . htmlspecialchars($this->DatabaseImport->Record['first_name'] . " " . $this->DatabaseImport->Record['last_name']), SWIFT_ImportManager::LOG_SUCCESS);

            if (in_array(mb_strtolower(trim($this->DatabaseImport->Record['first_name'])), $_staffUsernameList)) {
                $_username = mb_strtolower(trim($this->DatabaseImport->Record['first_name'])) . '_imp' . substr(BuildHash(), 0, 4);

                $this->GetImportManager()->AddToLog('Imported "' . htmlspecialchars($this->DatabaseImport->Record['first_name'] . " " . $this->DatabaseImport->Record['last_name']) . '" Username as "' . htmlspecialchars($_username) . '" due to conflict with an existing staff username', SWIFT_ImportManager::LOG_WARNING);
            } else {
                $_username = mb_strtolower(trim($this->DatabaseImport->Record['first_name']));
            }

            $_staffLastName = $this->DatabaseImport->Record['last_name'];
            $_staffFirstName = $this->DatabaseImport->Record['first_name'];

            $_staffPassword = SWIFT_Staff::GetComputedPassword('password');

            $this->Database->AutoExecute(TABLE_PREFIX . 'staff',
                array('firstname' => $_staffFirstName, 'lastname' => $_staffLastName . $_titleSuffix, 'fullname' => $this->DatabaseImport->Record['first_name'] . " " . $this->DatabaseImport->Record['last_name'] . $_titleSuffix,
                    'username' => $_username, 'staffpassword' => $_staffPassword, 'islegacypassword' => '1',
                    'staffgroupid' => $_staffGroupID, 'email' => $this->DatabaseImport->Record['email'], 'mobilenumber' => '0',
                    'groupassigns' => '0', 'timezonephp' => 'GMT', 'isenabled' => $_isenabled,
                    'lastvisit' => $_dateline, 'lastvisit2' => $_dateline,
                    'lastactivity' => $_dateline), 'INSERT');
            $_staffID = $this->Database->InsertID();

//            Add the staff signature
            $this->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('dateline' => DATENOW, 'staffid' => $_staffID, 'signature' => ''), 'INSERT');

            $this->ImportManager->GetImportRegistry()->UpdateKey('staff', $this->DatabaseImport->Record['id'], $_staffID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM worker");
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
