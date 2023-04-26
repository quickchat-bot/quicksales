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

namespace LiveChat\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT;
use SWIFT_Date;
use SWIFT_View;
use LiveChat\Models\Group\SWIFT_VisitorGroup;

/**
 * The Visitor Group View Management Class
 *
 * @author Varun Shoor
 */
class View_Group extends SWIFT_View
{
    /**
     * Render the Visitor Group Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_VisitorGroup $_SWIFT_VisitorGroupObject The SWIFT_VisitorGroup Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_VisitorGroup $_SWIFT_VisitorGroupObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Group/EditSubmit/' . $_SWIFT_VisitorGroupObject->GetVisitorGroupID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Group/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_visitorGroupTitle = $_visitorGroupColor = '';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/LiveChat/Group/Edit/' . $_SWIFT_VisitorGroupObject->GetVisitorGroupID());
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/Group/Delete/' . $_SWIFT_VisitorGroupObject->GetVisitorGroupID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_visitorGroupTitle = $_SWIFT_VisitorGroupObject->GetProperty('title');
            $_visitorGroupColor = $_SWIFT_VisitorGroupObject->GetProperty('color');
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatgroup'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_GeneralTabObject->Text('title', $this->Language->Get('visitorgrouptitle'), $this->Language->Get('desc_visitorgrouptitle'), $_visitorGroupTitle);
        $_GeneralTabObject->Color('color', $this->Language->Get('groupcolor'), $this->Language->Get('desc_groupcolor'), $_visitorGroupColor);

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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('visitorgroupgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'visitorgroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'visitorgroups WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'visitorgroups', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'visitorgroups');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('visitorgroupid', 'visitorgroupid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('visitorgrouptitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('lastupdate'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('LiveChat\Admin\Controller_Group', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/LiveChat/Group/Insert');

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

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/Group/Edit/' . (int)($_fieldContainer['visitorgroupid']) . '" onclick="javascript: return UICreateWindowExtended(event, \'' . SWIFT::Get('basename') . '/LiveChat/Group/Edit/' . (int)($_fieldContainer['visitorgroupid']) . "', 'editgroup', '" . sprintf($_SWIFT->Language->Get('wineditgroup'), htmlspecialchars($_fieldContainer['title'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 580, 380, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_visitorgroup.gif" border="0" align="absmiddle" />';

        return $_fieldContainer;
    }
}
