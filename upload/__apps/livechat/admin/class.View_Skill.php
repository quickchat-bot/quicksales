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

namespace LiveChat\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT;
use LiveChat\Models\Skill\SWIFT_ChatSkill;
use SWIFT_Date;
use SWIFT_View;

/**
 * The Skill View Management Class
 *
 * @author Varun Shoor
 */
class View_Skill extends SWIFT_View
{
    /**
     * Render the Chat Skill Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_ChatSkill $_SWIFT_ChatSkillObject The SWIFT_ChatSkill Object Pointer (Only for EDIT Mode)
     * @param array $_permissionContainer The Permission Container (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_ChatSkill $_SWIFT_ChatSkillObject = null, $_permissionContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffGroupCache = $this->Cache->Get('staffgroupcache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Skill/EditSubmit/' . $_SWIFT_ChatSkillObject->GetChatSkillID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Skill/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);
        $_AssignmentTabObject = $this->UserInterface->AddTab($this->Language->Get('tabassignments'), 'icon_settings2.gif', 'assignments');

        $_skillTitle = $_skillDescription = '';

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/Skill/Delete/' . $_SWIFT_ChatSkillObject->GetChatSkillID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatskill'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_skillTitle = $_SWIFT_ChatSkillObject->GetProperty('title');
            $_skillDescription = $_SWIFT_ChatSkillObject->GetProperty('description');
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatskill'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_GeneralTabObject->Text('title', $this->Language->Get('skilltitle'), $this->Language->Get('desc_skilltitle'), $_skillTitle);
        $_GeneralTabObject->Text('description', $this->Language->Get('skilldesc'), $this->Language->Get('desc_skilldesc'), $_skillDescription);

        $_AssignmentTabObject->Overflow('280');
        foreach ($_staffGroupCache as $_groupKey => $_groupValue) {
            $_AssignmentTabObject->Title(htmlspecialchars($_groupValue['title']), 'icon_permissions.gif');

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffgroupid = '" . (int)($_groupValue['staffgroupid']) . "'");
            while ($this->Database->NextRecord()) {
                $_isAssignedToSkill = false;

                $_staff = $this->Database->Record;
                if (isset($_POST['permstaffid']) && isset($_POST['permstaffid'][$_staff['staffid']]) && $_POST['permstaffid'][$_staff['staffid']] == 1) {
                    $_isAssignedToSkill = true;
                } else if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                    $_isAssignedToSkill = true;
                } else if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                    if (_is_array($_permissionContainer) && isset($_permissionContainer[$_staff['staffid']]) && $_permissionContainer[$_staff['staffid']] == '1') {
                        $_isAssignedToSkill = true;
                    }
                }

                $_isAdmin = false;
                if (isset($_staffGroupCache[$_staff['staffgroupid']]) && $_staffGroupCache[$_staff['staffgroupid']]['isadmin'] == 1) {
                    $_isAdmin = true;
                }

                $_AssignmentTabObject->YesNo('permstaffid[' . $_staff['staffid'] . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_' . IIF($_isAdmin, 'admin', 'notadmin') . '.gif" border="0" align="absmiddle" /> ' . text_to_html_entities($_staff['fullname']), '', $_isAssignedToSkill);
            }
        }

        $this->UserInterface->End();
    }

    /**
     * Render the Chat Skill Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('chatskillgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'chatskills WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'chatskills WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'chatskills', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'chatskills');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('chatskillid', 'chatskillid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('skilltitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('lastupdate'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('LiveChat\Admin\Controller_Skill', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/LiveChat/Skill/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/Skill/Edit/' . (int)($_fieldContainer['chatskillid']) . '" onclick="javascript: return UICreateWindowExtended(event, \'' . SWIFT::Get('basename') . '/LiveChat/Skill/Edit/' . (int)($_fieldContainer['chatskillid']) . "', 'editskill', '" . sprintf($_SWIFT->Language->Get('wineditskill'), htmlspecialchars($_fieldContainer['title'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 680, 430, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_chatskill.gif" border="0" align="absmiddle" />';

        return $_fieldContainer;
    }
}
