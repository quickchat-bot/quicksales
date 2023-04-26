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

namespace Base\Admin;

use SWIFT;
use SWIFT_App;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Models\Language\SWIFT_Language;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_UserGroup;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Template Group View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_TemplateGroup extends SWIFT_View
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render the Template Group Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TemplateGroup $_SWIFT_TemplateGroupObject The SWIFT_TemplateGroup Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_TemplateGroup $_SWIFT_TemplateGroupObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/TemplateGroup/EditSubmit/' . $_SWIFT_TemplateGroupObject->GetTemplateGroupID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/TemplateGroup/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_templateGroupTitle = '';
        $_templateGroupCompanyName = $this->Settings->Get('general_companyname');
        $_templateGroupIsDefault = false;
        $_templateGroupUseLoginShare = false;
        $_templateGroupIsEnabled = true;

        $_templateGroupEnablePassword = false;
        $_templateGroupUserName = '';
        $_templateGroupPassword = '';
        $_templateGroupPasswordConfirm = '';

        $_masterLanguageIDList = SWIFT_Language::GetMasterLanguageIDList();
        if (!count($_masterLanguageIDList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_templateGroupLanguageID = $_masterLanguageIDList[0];
        $_templateGroupDepartmentID = SWIFT_Department::RetrieveDefaultDepartmentID(APP_TICKETS, SWIFT_PUBLIC);
        $_templateGroupDepartmentID_LiveChat = SWIFT_Department::RetrieveDefaultDepartmentID(APP_LIVECHAT, SWIFT_PUBLIC);
        $_templateGroupTicketStatusID = false;
        $_templateGroupTicketPriorityID = false;
        $_templateGroupTicketTypeID = false;
        $_templateGroupRestrictGroups = false;
        $_templateGroupGuestUserGroupID = SWIFT_UserGroup::RetrieveDefaultUserGroupID(SWIFT_UserGroup::TYPE_GUEST);
        $_templateGroupRegisteredUserGroupID = SWIFT_UserGroup::RetrieveDefaultUserGroupID(SWIFT_UserGroup::TYPE_REGISTERED);

        $_templateGroupTicketPromptType = false;
        $_templateGroupTicketPromptPriority = true;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('templates'), 'fa-television', '/Base/Template/Manage/' . $_SWIFT_TemplateGroupObject->GetTemplateGroupID(), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/TemplateGroup/Delete/' . $_SWIFT_TemplateGroupObject->GetTemplateGroupID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('restore'), 'fa-rotate-left', '/Base/TemplateGroup/Restore/' . $_SWIFT_TemplateGroupObject->GetTemplateGroupID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templategroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_templateGroupTitle = $_SWIFT_TemplateGroupObject->GetProperty('title');
            $_templateGroupCompanyName = $_SWIFT_TemplateGroupObject->GetProperty('companyname');
            $_templateGroupIsDefault = (int)($_SWIFT_TemplateGroupObject->GetProperty('isdefault'));
            $_templateGroupUseLoginShare = (int)($_SWIFT_TemplateGroupObject->GetProperty('useloginshare'));
            $_templateGroupIsEnabled = (int)($_SWIFT_TemplateGroupObject->GetProperty('isenabled'));

            $_templateGroupEnablePassword = (int)($_SWIFT_TemplateGroupObject->GetProperty('enablepassword'));
            $_templateGroupUserName = $_SWIFT_TemplateGroupObject->GetProperty('groupusername');

            $_templateGroupLanguageID = (int)($_SWIFT_TemplateGroupObject->GetProperty('languageid'));
            $_templateGroupDepartmentID = (int)($_SWIFT_TemplateGroupObject->GetProperty('departmentid'));
            $_templateGroupDepartmentID_LiveChat = (int)($_SWIFT_TemplateGroupObject->GetProperty('departmentid_livechat'));
            $_templateGroupTicketStatusID = (int)($_SWIFT_TemplateGroupObject->GetProperty('ticketstatusid'));
            $_templateGroupTicketPriorityID = (int)($_SWIFT_TemplateGroupObject->GetProperty('priorityid'));
            $_templateGroupTicketTypeID = (int)($_SWIFT_TemplateGroupObject->GetProperty('tickettypeid'));
            $_templateGroupTicketPromptType = (int)($_SWIFT_TemplateGroupObject->GetProperty('tickets_prompttype'));
            $_templateGroupTicketPromptPriority = (int)($_SWIFT_TemplateGroupObject->GetProperty('tickets_promptpriority'));

            $_templateGroupGuestUserGroupID = (int)($_SWIFT_TemplateGroupObject->GetProperty('guestusergroupid'));
            $_templateGroupRegisteredUserGroupID = (int)($_SWIFT_TemplateGroupObject->GetProperty('regusergroupid'));
            $_templateGroupRestrictGroups = (int)($_SWIFT_TemplateGroupObject->GetProperty('restrictgroups'));

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templategroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_loginShareDisabled = false;
        if ($this->Settings->Get('loginshare_userenable') != '1') {
            $_templateGroupUseLoginShare = false;
            $_loginShareDisabled = true;
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('tgrouptitle'), $this->Language->Get('desc_tgrouptitle'), $_templateGroupTitle);

        if ($_templateGroupIsDefault == true) {
            // Adjusting the companyname for the 'Default' template group
            $_GeneralTabObject->Hidden('companyname', $this->Settings->Get('general_companyname'));
        } else {
            $_GeneralTabObject->Text('companyname', $this->Language->Get('companyname'), $this->Language->Get('desc_companyname'), $_templateGroupCompanyName);
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_optionsContainer = array();
            $_index = 0;
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY title ASC", 5);
            while ($this->Database->NextRecord(5)) {
                $_optionsContainer[$_index]['title'] = $this->Database->Record5['title'];
                $_optionsContainer[$_index]['value'] = $this->Database->Record5['tgroupid'];

                if ($this->Database->Record5['isdefault'] == 1) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_GeneralTabObject->Select('fromtgroupid', $this->Language->Get('copyfrom'), $this->Language->Get('desc_copyfrom'), $_optionsContainer);
        }

        if ($_templateGroupIsDefault == true) {
            $_GeneralTabObject->Hidden('isdefault', '1');
        } else {
            $_GeneralTabObject->YesNo('isdefault', $this->Language->Get('isdefault'), $this->Language->Get('desc_isdefault'), $_templateGroupIsDefault);
        }

        $_GeneralTabObject->YesNo('useloginshare', $this->Language->Get('useloginshare'), $this->Language->Get('desc_useloginshare'), $_templateGroupUseLoginShare, '', '', $_loginShareDisabled);

        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_templateGroupIsEnabled);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PERMISSIONS TAB
         * ###############################################
         */

        $_PermissionsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissions'), 'icon_lock.gif', 'permissions');

        $_PermissionsTabObject->Title($this->Language->Get('passwordprotection'), 'icon_doublearrows.gif');
        $_PermissionsTabObject->YesNo('enablepassword', $this->Language->Get('enablepassword'), $this->Language->Get('desc_enablepassword'), $_templateGroupEnablePassword);
        $_PermissionsTabObject->Text('groupusername', $this->Language->Get('groupusername'), $this->Language->Get('desc_groupusername'), $_templateGroupUserName, 'text', 20);
        $_PermissionsTabObject->Password('password', $this->Language->Get('password'), $this->Language->Get('desc_password'), $_templateGroupPassword, true);
        $_PermissionsTabObject->Password('passwordconfirm', $this->Language->Get('passwordconfirm'), $this->Language->Get('desc_passwordconfirm'), $_templateGroupPasswordConfirm, true);

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN SETTINGS TAB
         * ###############################################
         */

        $_SettingsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsettings'), 'icon_settings2.gif', 'settings');

        $_SettingsTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');
        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languages WHERE isenabled = 1 ORDER BY title ASC", 5);
        while ($this->Database->NextRecord(5)) {
            $_optionsContainer[$_index]['title'] = $this->Database->Record5['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record5['languageid'];

            if ($this->Database->Record5['languageid'] == $_templateGroupLanguageID) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_SettingsTabObject->Select('languageid', $this->Language->Get('defaultlanguage'), $this->Language->Get('desc_defaultlanguage'), $_optionsContainer);

        // ======= USER GROUPS =======
        $_SettingsTabObject->Title($this->Language->Get('usergroups'), 'icon_doublearrows.gif');
        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups WHERE grouptype = '" . SWIFT_UserGroup::TYPE_GUEST . "' ORDER BY title ASC", 5);
        while ($this->Database->NextRecord(5)) {
            $_optionsContainer[$_index]['title'] = $this->Database->Record5['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record5['usergroupid'];

            if ($this->Database->Record5['usergroupid'] == $_templateGroupGuestUserGroupID) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_SettingsTabObject->Select('guestusergroupid', $this->Language->Get('guestusergroup'), $this->Language->Get('desc_guestusergroup'), $_optionsContainer);

        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups WHERE grouptype = '" . SWIFT_UserGroup::TYPE_REGISTERED . "' ORDER BY title ASC", 5);
        while ($this->Database->NextRecord(5)) {
            $_optionsContainer[$_index]['title'] = $this->Database->Record5['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record5['usergroupid'];

            if ($this->Database->Record5['usergroupid'] == $_templateGroupRegisteredUserGroupID) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_SettingsTabObject->Select('regusergroupid', $this->Language->Get('regusergroup'), $this->Language->Get('desc_regusergroup'), $_optionsContainer);

        $_SettingsTabObject->YesNo('restrictgroups', $this->Language->Get('restrictgroups'), $this->Language->Get('desc_restrictgroups'), $_templateGroupRestrictGroups);

        /*
         * ###############################################
         * END SETTINGS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN SETTINGS (TICKETS) TAB
         * ###############################################
         */
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_TicketSettingsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsettings_tickets'), 'icon_settings2.gif',
                'settingstickets');
            $_TicketSettingsTabObject->Overflow(470);


            $_optionsContainer = SWIFT_Department::GetDepartmentMapOptions($_templateGroupDepartmentID, APP_TICKETS);
            $_TicketSettingsTabObject->Select('departmentid', $this->Language->Get('ticketdep'), $this->Language->Get('desc_ticketdep'),
                $_optionsContainer, 'javascript: UpdateTicketStatusDiv(this, \'ticketstatusid\', false, false); UpdateTicketTypeDiv(this, \'tickettypeid\', false, true);');

            $_optionsContainer = array();
            $_index = 0;
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes WHERE departmentid = '0'" .
                IIF(!empty($_templateGroupDepartmentID), " OR departmentid = '" . (int)($_templateGroupDepartmentID) . "'") .
                " ORDER BY displayorder ASC", 5);
            while ($this->Database->NextRecord(5)) {
                if ($this->Database->Record5['type'] != SWIFT_PUBLIC) {
                    continue;
                }

                $_optionsContainer[$_index]['title'] = $this->Database->Record5['title'];
                $_optionsContainer[$_index]['value'] = $this->Database->Record5['tickettypeid'];

                if ($this->Database->Record5['tickettypeid'] == $_templateGroupTicketTypeID) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_TicketSettingsTabObject->Select('tickettypeid', $this->Language->Get('tickettype'), $this->Language->Get('desc_tickettype'),
                $_optionsContainer, '', 'tickettypeid_container');

            $_optionsContainer = array();
            $_index = 0;
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus WHERE departmentid = '0'" .
                IIF(!empty($_templateGroupDepartmentID), " OR departmentid = '" . (int)($_templateGroupDepartmentID) . "'") .
                " ORDER BY displayorder ASC", 5);
            while ($this->Database->NextRecord(5)) {
                $_optionsContainer[$_index]['title'] = $this->Database->Record5['title'];
                $_optionsContainer[$_index]['value'] = $this->Database->Record5['ticketstatusid'];

                if ($this->Database->Record5['ticketstatusid'] == $_templateGroupTicketStatusID) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_TicketSettingsTabObject->Select('ticketstatusid', $this->Language->Get('ticketstatus'), $this->Language->Get('desc_ticketstatus'),
                $_optionsContainer, '', 'ticketstatusid_container');

            $_optionsContainer = array();
            $_index = 0;
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC", 5);
            while ($this->Database->NextRecord(5)) {
                $_optionsContainer[$_index]['title'] = $this->Database->Record5['title'];
                $_optionsContainer[$_index]['value'] = $this->Database->Record5['priorityid'];

                if ($this->Database->Record5['priorityid'] == $_templateGroupTicketPriorityID) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_TicketSettingsTabObject->Select('priorityid', $this->Language->Get('ticketpriority'), $this->Language->Get('desc_ticketpriority'),
                $_optionsContainer);

            $_TicketSettingsTabObject->YesNo('prompt_tickettype', $this->Language->Get('prompttickettype'),
                $this->Language->Get('desc_prompttickettype'), $_templateGroupTicketPromptType);

            $_TicketSettingsTabObject->YesNo('prompt_ticketpriority', $this->Language->Get('promptticketpriority'),
                $this->Language->Get('desc_promptticketpriority'), $_templateGroupTicketPromptPriority);
        } else {
            $this->UserInterface->Hidden('departmentid', 0);
            $this->UserInterface->Hidden('ticketstatusid', 0);
            $this->UserInterface->Hidden('tickettypeid', 0);
            $this->UserInterface->Hidden('priorityid', 0);
            $this->UserInterface->Hidden('prompt_tickettype', 0);
            $this->UserInterface->Hidden('prompt_ticketpriority', 0);
        }

        /*
         * ###############################################
         * END SETTINGS (TICKETS) TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN SETTINGS (LIVE CHAT) TAB
         * ###############################################
         */
        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $_LiveChatSettingsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsettings_livechat'), 'icon_settings2.gif',
                'settingslivechat');

            $_optionsContainer = SWIFT_Department::GetDepartmentMapOptions($_templateGroupDepartmentID_LiveChat, APP_LIVECHAT);
            $_LiveChatSettingsTabObject->Select('departmentid_livechat', $this->Language->Get('livechatdep'),
                $this->Language->Get('desc_livechatdep'), $_optionsContainer);
        } else {
            $this->UserInterface->Hidden('departmentid_livechat', 0);
        }

        /*
         * ###############################################
         * END SETTINGS (LIVE CHAT) TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Template Group Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('templategroupgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'templategroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'templategroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'templategroups', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'templategroups');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('tgroupid', 'tgroupid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('title'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('companyname', $this->Language->Get('companyname'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('languageid', $this->Language->Get('glanguage'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));

        if ($_SWIFT->Staff->GetPermission('admin_tmpcandeletegroup') != '0') {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_TemplateGroup', 'DeleteList'), $this->Language->Get('actionconfirm')));
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') != '0') {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('restore'), 'icon_restore.gif', array('Base\Admin\Controller_TemplateGroup', 'RestoreList'), $this->Language->Get('restoreconfirmask')));
        }

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/TemplateGroup/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $_SWIFT->Cache->Get('languagecache');

        if (isset($_languageCache[$_fieldContainer['languageid']])) {
            $_fieldContainer['languageid'] = $_languageCache[$_fieldContainer['languageid']]['title'];
        } else {
            $_fieldContainer['languageid'] = '<font color="red">' . $_SWIFT->Language->Get('na') . '</font>';
        }

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Base/TemplateGroup/Edit/' . (int)($_fieldContainer['tgroupid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Base/TemplateGroup/Edit/' . (int)($_fieldContainer['tgroupid']) . "', 'edittemplategroup', '" . sprintf($_SWIFT->Language->Get('winedittemplategroup'), htmlspecialchars($_fieldContainer['title'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 720, 680, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . IIF($_fieldContainer['isdefault'] == 1, '<i>') . htmlspecialchars($_fieldContainer['title']) . IIF($_fieldContainer['isdefault'] == 1, '</i>') . '</font></a>';

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_templategroup.gif" align="absmiddle" width="16" height="16" border="0" />';

        return $_fieldContainer;
    }
}

?>
