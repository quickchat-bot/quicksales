<?php

namespace Base\Library\Import\Cerberus5;

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: User
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Address extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Address');

        if (!$this->TableExists('address')) {
            $this->SetIsClassLoaded(false);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "users");
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "useremails");
        }

        $_count = 0;

        $_userContainer = $_userIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM address ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_userContainer[$this->DatabaseImport->Record['id']] = $this->DatabaseImport->Record;
            $_userContainer[$this->DatabaseImport->Record['id']]['emails'][] = $this->DatabaseImport->Record['email'];
        }

        foreach ($_userContainer as $_userID => $_user) {

            // Try to fetch the user group
            $_userGroupID = 2;

            $this->GetImportManager()->AddToLog('Importing User: ' . htmlspecialchars($_user['first_name'] . " " . $_user['last_name']), SWIFT_ImportManager::LOG_SUCCESS);

            $_userRole = SWIFT_User::ROLE_USER;


            $_userPassword = SWIFT_User::GetComputedPassword('password');
            $_isLegacyPassword = false;

            $_slaPlanID = $_slaPlanExpiry = 0;

            $_userorganizationid = $this->ImportManager->GetImportRegistry()->GetKey('userorganization', $_user['contact_org_id']);

            if (empty($_user['is_registered'])) {
                $_isenabled = 0;
            } else {
                $_isenabled = 1;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'users',
                array('usergroupid' => $_userGroupID, 'userrole' => $_userRole, 'userorganizationid' => $_userorganizationid, 'salutation' => SWIFT_User::SALUTATION_NONE,
                    'fullname' => $_user['first_name'] . " " . $_user['last_name'], 'phone' => '0', 'userpassword' => $_userPassword, 'islegacypassword' => (int)($_isLegacyPassword),
                    'dateline' => DATENOW, 'lastvisit' => DATENOW, 'lastactivity' => DATENOW, 'isenabled' => $_isenabled, 'isvalidated' => '1',
                    'slaplanid' => $_slaPlanID, 'slaexpirytimeline' => $_slaPlanExpiry), 'INSERT');
            $dwk_userID = $this->Database->InsertID();

            foreach ($_user['emails'] as $_emailAddress) {
                $this->Database->AutoExecute(TABLE_PREFIX . 'useremails',
                    array('linktype' => SWIFT_UserEmail::LINKTYPE_USER, 'linktypeid' => $dwk_userID, 'email' => $_emailAddress), 'INSERT');
            }

            $this->ImportManager->GetImportRegistry()->UpdateKey('user', $_user['id'], $dwk_userID, true);
        }

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM address");
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

        return 100;
    }
}

?>
