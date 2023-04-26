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

use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use SWIFT;
use SWIFT_Date;
use SWIFT_Interface;
use SWIFT_View;

/**
 * The Online Status View Management Class
 *
 * @author Varun Shoor
 */
class View_OnlineStatus extends SWIFT_View
{
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('staffonlinestatusgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT *, sessions.dateline AS sessiondateline FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'sessions AS sessions ON (staff.staffid = sessions.typeid) WHERE sessions.sessiontype IN (\'' . SWIFT_Interface::INTERFACE_WINAPP . '\') AND ((' . $this->UserInterfaceGrid->BuildSQLSearch('staff.fullname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.username') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.email') . '))', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'sessions AS sessions ON (staff.staffid = sessions.typeid) WHERE sessions.sessiontype IN (\'' . SWIFT_Interface::INTERFACE_WINAPP . '\') AND ((' . $this->UserInterfaceGrid->BuildSQLSearch('staff.fullname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.username') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staff.email') . '))');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT *, sessions.dateline AS sessiondateline FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'sessions AS sessions ON (staff.staffid = sessions.typeid) WHERE sessions.sessiontype IN (\'' . SWIFT_Interface::INTERFACE_WINAPP . '\')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staff AS staff LEFT JOIN ' . TABLE_PREFIX . 'sessions AS sessions ON (staff.staffid = sessions.typeid) WHERE sessions.sessiontype IN (\'' . SWIFT_Interface::INTERFACE_WINAPP . '\')');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sessionid', 'sessionid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staff.fullname', $this->Language->Get('fullname'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staff.username', $this->Language->Get('username'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sessions.dateline', $this->Language->Get('logintime'), SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sessions.lastactivity', $this->Language->Get('lastactivity'), SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disconnect'), 'icon_livechatdisconnect.gif', array('LiveChat\Admin\Controller_OnlineStatus', 'DisconnectList'), $this->Language->Get('actionconfirm')));

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

        $_fieldContainer['staff.fullname'] = text_to_html_entities($_fieldContainer['fullname']);

        $_threshold = DATENOW - 600;
        if ($_fieldContainer['lastactivity'] < $_threshold) {
            $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_bulboff.gif" border="0" align="absmiddle" />';
        } else {
            $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_bulbon.gif" border="0" align="absmiddle" />';
        }

        $_fieldContainer['sessions.lastactivity'] = SWIFT_Date::ColorTime(DATENOW - $_fieldContainer['lastactivity']);

        $_fieldContainer['sessions.dateline'] = SWIFT_Date::ColorTime(DATENOW - $_fieldContainer['sessiondateline'], false, true);

        $_fieldContainer['staff.username'] = htmlspecialchars($_fieldContainer['username']);
        $_fieldContainer['staff.staffid'] = $_fieldContainer['staffid'];

        return $_fieldContainer;
    }
}
