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

use Base\Models\Staff\SWIFT_Staff;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: Staff
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Staff extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Staff');

        if (!$this->TableExists(TABLE_PREFIX . 'staff')) {
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

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY staffid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingStaffFullName = $this->ImportManager->GetImportRegistry()->GetKey('stafffullname', mb_strtolower(trim($this->DatabaseImport->Record['fullname'])));

            // A record with same title exists?
            if ($_existingStaffFullName != false) {
                $_titleSuffix .= ' (Import)';
            }

            // Try to fetch the staff group
            $_staffGroupID = $this->ImportManager->GetImportRegistry()->GetKey('staffgroup', $this->DatabaseImport->Record['staffgroupid']);
            if ($_staffGroupID == false) {
                $this->GetImportManager()->AddToLog('Importing Staff: ' . text_to_html_entities($this->DatabaseImport->Record['fullname']), SWIFT_ImportManager::LOG_FAILURE,
                    'Staff Group ID "' . $this->DatabaseImport->Record['staffgroupid'] . '" does not exist');

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Staff: ' . text_to_html_entities($this->DatabaseImport->Record['fullname']), SWIFT_ImportManager::LOG_SUCCESS);

            if (in_array($this->DatabaseImport->Record['username'], $_staffUsernameList)) {
                $this->DatabaseImport->Record['username'] .= '_imp' . substr(BuildHash(), 0, 4);

                $this->GetImportManager()->AddToLog('Imported "' . text_to_html_entities($this->DatabaseImport->Record['fullname']) . '" Username as "' . htmlspecialchars($this->DatabaseImport->Record['username']) . '" due to conflict with an existing staff username', SWIFT_ImportManager::LOG_WARNING);
            }

            $_staffFirstName = $_staffLastName = '';
            if (strpos($this->DatabaseImport->Record['fullname'], ' ')) {
                $_staffNameContainer = explode(' ', $this->DatabaseImport->Record['fullname']);
                $_staffFirstName = $_staffNameContainer[0];
                unset($_staffNameContainer[0]);

                $_staffLastName = implode(' ', $_staffNameContainer);
            } else {
                $_staffFirstName = $this->DatabaseImport->Record['fullname'];
            }

            if ($this->DatabaseImport->Record['timezonephp'] == '99' || $this->DatabaseImport->Record['timezonephp'] == '') {
                $this->DatabaseImport->Record['timezonephp'] = 'GMT';
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'staff',
                array('firstname' => $_staffFirstName, 'lastname' => $_staffLastName . $_titleSuffix, 'fullname' => $this->DatabaseImport->Record['fullname'] . $_titleSuffix,
                    'username' => $this->DatabaseImport->Record['username'], 'staffpassword' => $this->DatabaseImport->Record['password'], 'islegacypassword' => '1',
                    'staffgroupid' => $_staffGroupID, 'email' => $this->DatabaseImport->Record['email'], 'mobilenumber' => $this->DatabaseImport->Record['mobilenumber'],
                    'groupassigns' => $this->DatabaseImport->Record['groupassigns'], 'timezonephp' => $this->DatabaseImport->Record['timezonephp'],
                    'lastvisit' => $this->DatabaseImport->Record['lastvisit'], 'lastvisit2' => $this->DatabaseImport->Record['lastvisit2'],
                    'lastactivity' => $this->DatabaseImport->Record['lastactivity']), 'INSERT');
            $_staffID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('staff', $this->DatabaseImport->Record['staffid'], $_staffID);
        }

        SWIFT_Staff::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staff");
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
