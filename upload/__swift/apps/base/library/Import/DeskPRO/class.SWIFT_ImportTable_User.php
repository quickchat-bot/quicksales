<?php

namespace Base\Library\Import\DeskPRO;

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: User
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_User extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'User');

        if (!$this->TableExists('user')) {
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

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "users");
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "useremails");
//        }

        $_count = 0;

        $_userContainer = $_userIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM user ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_userContainer[$this->DatabaseImport->Record['id']] = $this->DatabaseImport->Record;
            $_userContainer[$this->DatabaseImport->Record['id']]['emails'] = array();
            $_userContainer[$this->DatabaseImport->Record['id']]['usergroup'] = 2;
            $_userContainer[$this->DatabaseImport->Record['id']]['userpasswordtxt'] = 'password';
            $_userIDList[] = $this->DatabaseImport->Record['id'];
        }

        $this->DatabaseImport->Query("SELECT * FROM user_email WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($this->DatabaseImport->NextRecord()) {
            $_userContainer[$this->DatabaseImport->Record['userid']]['emails'][] = $this->DatabaseImport->Record['email'];
        }

        $this->DatabaseImport->Query("SELECT * FROM user_member_groups WHERE user IN (" . BuildIN($_userIDList) . ")");
        while ($this->DatabaseImport->NextRecord()) {
            $_userContainer[$this->DatabaseImport->Record['user']]['usergroup'] = $this->DatabaseImport->Record['usergroup'];
        }

        foreach ($_userContainer as $_userID => $_user) {

            // Try to fetch the user group
            $_userGroupID = $this->ImportManager->GetImportRegistry()->GetKey('usergroup', $_user['usergroup']);
            if ($_userGroupID == false) {
                $_userGroupID = 2;
            }

            // Try to fetch the user organization
            $_useorganizationID = $this->ImportManager->GetImportRegistry()->GetKey('userorganization', $_user['default_company']);
            if ($_useorganizationID == false) {
                $_useorganizationID = 0;
            }

            $this->GetImportManager()->AddToLog('Importing User: ' . htmlspecialchars($_user['name']), SWIFT_ImportManager::LOG_SUCCESS);

            $_userRole = SWIFT_User::ROLE_USER;

            $_userPassword = '';
            $_isLegacyPassword = false;
            if (!empty($_user['userpasswordtxt'])) {
                $_userPassword = SWIFT_User::GetComputedPassword($_user['userpasswordtxt']);
                $_isLegacyPassword = false;
            }

            if (empty($_user['disabled'])) {
                $_isenabled = 1;
                $_isvalidated = 1;
            } else {
                $_isenabled = 0;
                $_isvalidated = 0;
            }

            $_slaPlanID = $_slaPlanExpiry = 0;

            $this->Database->AutoExecute(TABLE_PREFIX . 'users',
                array('usergroupid' => $_userGroupID, 'userrole' => $_userRole, 'userorganizationid' => $_useorganizationID, 'salutation' => SWIFT_User::SALUTATION_NONE,
                    'fullname' => $_user['name'], 'phone' => '0', 'userpassword' => $_userPassword, 'islegacypassword' => (int)($_isLegacyPassword),
                    'dateline' => $_user['date_registered'], 'lastvisit' => $_user['last_activity'], 'lastactivity' => $_user['last_activity'], 'isvalidated' => $_isenabled,
                    'slaplanid' => $_slaPlanID, 'slaexpirytimeline' => $_slaPlanExpiry, 'isenabled' => $_isvalidated), 'INSERT');
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
     * @author Nicolás Ibarra Sabogal
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM user");
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

        return 1000;
    }
}

?>
