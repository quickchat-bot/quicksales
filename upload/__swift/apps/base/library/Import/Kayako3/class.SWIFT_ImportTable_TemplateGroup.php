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

use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_UserGroup;
use SWIFT_App;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: TemplateGroup
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TemplateGroup extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TemplateGroup');

        if (!$this->TableExists(TABLE_PREFIX . 'templategroups')) {
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
            $_existingTemplateGroupContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");
            while ($this->Database->NextRecord()) {
                $_existingTemplateGroupContainer[$this->Database->Record['tgroupid']] = $this->Database->Record;
            }

            foreach ($_existingTemplateGroupContainer as $_templateGroupContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('templategrouptitle', mb_strtolower(trim($_templateGroupContainer['title'])), '1');
            }
        }

        // Retrieve Master Template Group ID
        $_masterTemplateGroupID = false;
        $_masterTemplateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid FROM " . TABLE_PREFIX . "templategroups WHERE ismaster = '1'");
        if (isset($_masterTemplateGroupContainer['tgroupid']) && !empty($_masterTemplateGroupContainer['tgroupid'])) {
            $_masterTemplateGroupID = (int)($_masterTemplateGroupContainer['tgroupid']);
        } else {
            throw new SWIFT_Exception('Invalid Master Template Group');
        }

        // Retrieve Default Language ID
        $_masterLanguageID = false;
        $_languageContainer = $this->Database->QueryFetch("SELECT languageid FROM " . TABLE_PREFIX . "languages WHERE ismaster = '1'");
        if (isset($_languageContainer['languageid']) && !empty($_languageContainer['languageid'])) {
            $_masterLanguageID = (int)($_languageContainer['languageid']);
        } else {
            throw new SWIFT_Exception('Invalid Master Language');
        }

        // Retrieve Default User Group ID (GUEST)
        $_masterGuestUserGroupID = false;
        $_guestUserGroupContainer = $this->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups
            WHERE grouptype = '" . SWIFT_UserGroup::TYPE_GUEST . "' ORDER BY usergroupid ASC");
        if (isset($_guestUserGroupContainer['usergroupid']) && !empty($_guestUserGroupContainer['usergroupid'])) {
            $_masterGuestUserGroupID = (int)($_guestUserGroupContainer['usergroupid']);
        } else {
            throw new SWIFT_Exception('Invalid Master User Group');
        }

        // Retrieve Default User Group ID (REGISTERED)
        $_masterRegisteredUserGroupID = false;
        $_registeredUserGroupContainer = $this->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups
            WHERE grouptype = '" . SWIFT_UserGroup::TYPE_REGISTERED . "' ORDER BY usergroupid ASC");
        if (isset($_registeredUserGroupContainer['usergroupid']) && !empty($_registeredUserGroupContainer['usergroupid'])) {
            $_masterRegisteredUserGroupID = (int)($_registeredUserGroupContainer['usergroupid']);
        } else {
            throw new SWIFT_Exception('No Default Registered User Group');
        }

        // Retrieve Default Ticket Status ID
        $_masterTicketStatusID = false;
        if ($this->TableExists(TABLE_PREFIX . 'ticketstatus') && SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_masterTicketStatusContainer = $this->Database->QueryFetch("SELECT ticketstatusid FROM " . TABLE_PREFIX . "ticketstatus
                WHERE statustype = '" . SWIFT_PUBLIC . "' AND markasresolved = '0' ORDER BY displayorder ASC");
            if (isset($_masterTicketStatusContainer['ticketstatusid']) && !empty($_masterTicketStatusContainer['ticketstatusid'])) {
                $_masterTicketStatusID = (int)($_masterTicketStatusContainer['ticketstatusid']);
            }
        }

        // Retrieve Default Ticket Priority ID
        $_masterTicketPriorityID = false;
        if ($this->TableExists(TABLE_PREFIX . 'ticketpriorities') && SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_masterTicketPriorityContainer = $this->Database->QueryFetch("SELECT priorityid FROM " . TABLE_PREFIX . "ticketpriorities
                WHERE type = '" . SWIFT_PUBLIC . "' ORDER BY displayorder ASC");
            if (isset($_masterTicketPriorityContainer['priorityid']) && !empty($_masterTicketPriorityContainer['priorityid'])) {
                $_masterTicketPriorityID = (int)($_masterTicketPriorityContainer['priorityid']);
            }
        }

        // Retrieve Default Ticket Department ID
        $_masterTicketDepartmentID = false;
        $_masterTicketDepartmentContainer = $this->Database->QueryFetch("SELECT departmentid FROM " . TABLE_PREFIX . "departments
            WHERE departmentapp = '" . APP_TICKETS . "' ORDER BY departmentid ASC");
        if (isset($_masterTicketDepartmentContainer['departmentid']) && !empty($_masterTicketDepartmentContainer['departmentid'])) {
            $_masterTicketDepartmentID = (int)($_masterTicketDepartmentContainer['departmentid']);
        }

        // Retrieve Default Live Chat Department ID
        $_masterLiveChatDepartmentID = false;
        $_masterLiveChatDepartmentContainer = $this->Database->QueryFetch("SELECT departmentid FROM " . TABLE_PREFIX . "departments
            WHERE departmentapp = '" . APP_LIVECHAT . "' ORDER BY departmentid ASC");
        if (isset($_masterLiveChatDepartmentContainer['departmentid']) && !empty($_masterLiveChatDepartmentContainer['departmentid'])) {
            $_masterLiveChatDepartmentID = (int)($_masterLiveChatDepartmentContainer['departmentid']);
        }

        // Retrieve Default Ticket Type ID
        $_masterTicketTypeID = false;
        $_masterTicketTypeContainer = $this->Database->QueryFetch("SELECT tickettypeid FROM " . TABLE_PREFIX . "tickettypes
            WHERE ismaster = '1' ORDER BY tickettypeid ASC");
        if (isset($_masterTicketTypeContainer['tickettypeid']) && !empty($_masterTicketTypeContainer['tickettypeid'])) {
            $_masterTicketTypeID = (int)($_masterTicketTypeContainer['tickettypeid']);
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingUserGroupTitle = $this->ImportManager->GetImportRegistry()->GetKey('templategrouptitle', mb_strtolower(trim($this->DatabaseImport->Record['title'])));

            // A record with same title exists?
            if ($_existingUserGroupTitle != false) {
                $_titleSuffix .= '_import';
            }

            $_guestUserGroupID = $this->ImportManager->GetImportRegistry()->GetKey('usergroup', $this->DatabaseImport->Record['guestusergroupid']);
            if ($_guestUserGroupID == false) {
                $_guestUserGroupID = $_masterGuestUserGroupID;
            }

            $_registeredUserGroupID = $this->ImportManager->GetImportRegistry()->GetKey('usergroup', $this->DatabaseImport->Record['regusergroupid']);
            if ($_registeredUserGroupID == false) {
                $_registeredUserGroupID = $_masterRegisteredUserGroupID;
            }

            $_ticketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $this->DatabaseImport->Record['ticketstatusid']);
            if ($_ticketStatusID == false) {
                $_ticketStatusID = $_masterTicketStatusID;
            }

            $_ticketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $this->DatabaseImport->Record['priorityid']);
            if ($_ticketPriorityID == false) {
                $_ticketPriorityID = $_masterTicketPriorityID;
            }

            $_departmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['departmentid']);
            if ($_departmentID == false) {
                $_departmentID = $_masterTicketDepartmentID;
            }

            if ($this->DatabaseImport->Record['isdefault'] == '1') {
                $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('ticketstatusid' => $_ticketStatusID, 'priorityid' => $_ticketPriorityID,
                    'departmentid' => $_departmentID, 'regusergroupid' => $_registeredUserGroupID, 'guestusergroupid' => $_guestUserGroupID),
                    'UPDATE', "ismaster = '1'");
            }

            $this->GetImportManager()->AddToLog('Importing Template Group: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('title' => $this->DatabaseImport->Record['title'] . $_titleSuffix,
                'languageid' => $_masterLanguageID, 'isenabled' => '1', 'guestusergroupid' => $_guestUserGroupID, 'regusergroupid' => $_registeredUserGroupID,
                'description' => $this->DatabaseImport->Record['description'], 'companyname' => $this->DatabaseImport->Record['companyname'], 'ismaster' => '0',
                'enablepassword' => '0', 'groupusername' => '', 'grouppassword' => '', 'restrictgroups' => (int)($this->DatabaseImport->Record['restrictgroups']),
                'isdefault' => '0', 'useloginshare' => '0', 'ticketstatusid' => $_ticketStatusID, 'priorityid' => $_ticketPriorityID,
                'departmentid' => $_departmentID, 'tickettypeid' => $_masterTicketTypeID, 'departmentid_livechat' => $_masterLiveChatDepartmentID,
                'tickets_prompttype' => '0', 'tickets_promptpriority' => '1'), 'INSERT');
            $_templateGroupID = $this->Database->InsertID();

            $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);
            $_SWIFT_TemplateGroupObject->Copy($_masterTemplateGroupID);

            $this->ImportManager->GetImportRegistry()->UpdateKey('templategroup', $this->DatabaseImport->Record['tgroupid'], $_templateGroupID);
        }

        SWIFT_TemplateGroup::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "templategroups");
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
