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
class SWIFT_ImportTable_User extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'User');

        if (!$this->TableExists(TABLE_PREFIX . 'users')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "users");
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "useremails");
        }

        $_count = 0;

        $_userContainer = $_userIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "users ORDER BY userid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_userContainer[$this->DatabaseImport->Record['userid']] = $this->DatabaseImport->Record;
            $_userContainer[$this->DatabaseImport->Record['userid']]['emails'] = array();
            $_userIDList[] = $this->DatabaseImport->Record['userid'];
        }

        $this->DatabaseImport->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($this->DatabaseImport->NextRecord()) {
            $_userContainer[$this->DatabaseImport->Record['userid']]['emails'][] = $this->DatabaseImport->Record['email'];
        }

        foreach ($_userContainer as $_userID => $_user) {
            if ($_user['enabled'] != '1') {
                continue;
            }

            // Try to fetch the user group
            $_userGroupID = $this->ImportManager->GetImportRegistry()->GetKey('usergroup', $_user['usergroupid']);
            if ($_userGroupID == false) {
                $this->GetImportManager()->AddToLog('Importing User: ' . text_to_html_entities($_user['fullname']), SWIFT_ImportManager::LOG_FAILURE,
                    'User Group ID "' . $_user['usergroupid'] . '" does not exist');

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing User: ' . text_to_html_entities($_user['fullname']), SWIFT_ImportManager::LOG_SUCCESS);

            $_userRole = SWIFT_User::ROLE_USER;
            if ($_user['ismanager'] == '1') {
                $_userRole = SWIFT_User::ROLE_MANAGER;
            }

            $_userPassword = '';
            $_isLegacyPassword = false;
            if (!empty($_user['userpasswordtxt'])) {
                $_userPassword = SWIFT_User::GetComputedPassword($_user['userpasswordtxt']);
                $_isLegacyPassword = false;
            } else {
                $_userPassword = $_user['userpassword'];
                $_isLegacyPassword = true;
            }

            $_slaPlanID = $_slaPlanExpiry = 0;
            if ($_user['slaplanid'] != '0') {
                $_slaPlanID = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $_user['slaplanid']);

                $_slaPlanExpiry = (int)($_user['slaexpiry']);
            }

            $_userPhone = '';
            if (isset($_user['phone']) && !empty($_user['phone'])) {
                $_userPhone = $_user['phone'];
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'users',
                array('usergroupid' => $_userGroupID, 'userrole' => $_userRole, 'userorganizationid' => '0', 'salutation' => SWIFT_User::SALUTATION_NONE,
                    'fullname' => $_user['fullname'], 'phone' => $_userPhone, 'userpassword' => $_userPassword, 'islegacypassword' => (int)($_isLegacyPassword),
                    'dateline' => $_user['dateline'], 'lastvisit' => $_user['lastvisit'], 'lastactivity' => $_user['lastvisit'], 'isvalidated' => '1',
                    'slaplanid' => $_slaPlanID, 'slaexpirytimeline' => $_slaPlanExpiry, 'isenabled' => '1'), 'INSERT');
            $_userID = $this->Database->InsertID();

            foreach ($_user['emails'] as $_emailAddress) {
                $this->Database->AutoExecute(TABLE_PREFIX . 'useremails',
                    array('linktype' => SWIFT_UserEmail::LINKTYPE_USER, 'linktypeid' => $_userID, 'email' => $_emailAddress), 'INSERT');
            }

            $this->ImportManager->GetImportRegistry()->UpdateKey('user', $_user['userid'], $_userID, true);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "users");
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

        return 1000;
    }
}

?>
