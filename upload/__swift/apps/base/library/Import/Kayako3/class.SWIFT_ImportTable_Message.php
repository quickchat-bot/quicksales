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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use LiveChat\Models\Message\SWIFT_MessageManager;

/**
 * Import Table: Message
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Message extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Message');

        if (!$this->TableExists(TABLE_PREFIX . 'messages')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Message:Message', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Message:MessageManager', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Message:MessageSurvey', APP_LIVECHAT);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "messages");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "messages ORDER BY messageid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);
            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);
            if (!$_newDepartmentID) {
                $this->GetImportManager()->AddToLog('Failed to Import Message due to non existent department: ' . htmlspecialchars($this->DatabaseImport->Record['messageid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_staffName = '';
            if (isset($_staffCache[$_newStaffID])) {
                $_staffName = $_staffCache[$_newStaffID]['fullname'];
            }

            $_messageStatus = SWIFT_MessageManager::STATUS_READ;

            // Mark as replied?
            if ($this->DatabaseImport->Record['messagestatus'] == '4') {
                $_messageStatus = SWIFT_MessageManager::STATUS_REPLIED;
            }

            $_messageType = SWIFT_MessageManager::MESSAGE_CLIENT;

            $this->GetImportManager()->AddToLog('Importing Message: ' . htmlspecialchars($this->DatabaseImport->Record['subject']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'messages',
                array('messagemaskid' => SWIFT_MessageManager::GetMessageMaskID(), 'dateline' => $this->DatabaseImport->Record['dateline'], 'replydateline' => $this->DatabaseImport->Record['replydateline'],
                    'fullname' => $this->DatabaseImport->Record['fullname'], 'email' => $this->DatabaseImport->Record['email'], 'subject' => $this->DatabaseImport->Record['subject'],
                    'departmentid' => $_newDepartmentID, 'parentmessageid' => '0', 'messagestatus' => $_messageStatus, 'messagetype' => $_messageType,
                    'messagerating' => '0', 'chatobjectid' => '0', 'staffid' => $_newStaffID, 'staffname' => $_staffName), 'INSERT');
            $_messageID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('message', $this->DatabaseImport->Record['messageid'], $_messageID, true);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "messages");
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
