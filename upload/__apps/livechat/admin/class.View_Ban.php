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
use LiveChat\Models\Ban\SWIFT_VisitorBan;

/**
 * The Visitor Ban View (Form, Grid etc.)
 *
 * @author Varun Shoor
 */
class View_Ban extends SWIFT_View
{
    /**
     * Render the Visitor Ban Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_VisitorBan $_SWIFT_VisitorBanObject The SWIFT_VisitorBan Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_VisitorBan $_SWIFT_VisitorBanObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Ban/EditSubmit/' . $_SWIFT_VisitorBanObject->GetVisitorBanID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/Ban/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_isRegularExpression = false;
        $_ipAddress = '';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/LiveChat/Ban/Edit/' . $_SWIFT_VisitorBanObject->GetVisitorBanID());
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/Ban/Delete/' . $_SWIFT_VisitorBanObject->GetVisitorBanID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatban'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_isRegularExpression = (int)($_SWIFT_VisitorBanObject->GetProperty('isregex'));
            $_ipAddress = $_SWIFT_VisitorBanObject->GetProperty('ipaddress');
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatban'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $_GeneralTabObject->Text('ipaddress', $this->Language->Get('ipaddress'), $this->Language->Get('desc_ipaddress'), $_ipAddress);

        $_GeneralTabObject->YesNo('isregex', $this->Language->Get('isregex'), $this->Language->Get('desc_isregex'), $_isRegularExpression);

        $this->UserInterface->End();
    }

    /**
     * Render the Visitor Ban Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('visitorbangrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'visitorbans WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'visitorbans WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'visitorbans', 'SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'visitorbans');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('visitorbanid', 'visitorbanid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ipaddress', $this->Language->Get('ipaddress'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('isregex', $this->Language->Get('gridisregex'), SWIFT_UserInterfaceGridField::TYPE_DB, 100, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffid', $this->Language->Get('addedby'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('bandate'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('LiveChat\Admin\Controller_Ban', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/LiveChat/Ban/Insert');

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

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        if (isset($_staffCache[$_fieldContainer['staffid']])) {
            $_fieldContainer['staffid'] = $_staffCache[$_fieldContainer['staffid']]['fullname'];
        } else {
            $_fieldContainer['staffid'] = $_SWIFT->Language->Get('na') . ' (' . $_fieldContainer['staffid'] . ')';
        }

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);
        $_fieldContainer['icon'] = '<i class="fa fa-minus-circle" aria-hidden="true"></i>';

        $_fieldContainer['ipaddress'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/Ban/Edit/' . (int)($_fieldContainer['visitorbanid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/LiveChat/Ban/Edit/' . (int)($_fieldContainer['visitorbanid']) . "/1', 'editban', '" . sprintf($_SWIFT->Language->Get('wineditban'), htmlspecialchars($_fieldContainer['ipaddress'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 580, 350, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['ipaddress']) . '</a>';

        $_fieldContainer['isregex'] = IIF($_fieldContainer['isregex'] == 1, $_SWIFT->Language->Get('yes'), $_SWIFT->Language->Get('no'));

        return $_fieldContainer;
    }
}
