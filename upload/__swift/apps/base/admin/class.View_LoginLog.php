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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Admin;

use SWIFT;
use SWIFT_Date;
use Base\Models\Staff\SWIFT_StaffLoginLog;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use SWIFT_View;

/**
 * The Login Log View Management Class
 *
 * @author Varun Shoor
 */
class View_LoginLog extends SWIFT_View
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
     * Render the Ticket File Type Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('loginloggrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'staffloginlog WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staffusername') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staffname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('forwardedipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('useragent') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staffloginlog WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staffusername') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('staffname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('forwardedipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('useragent') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'staffloginlog', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staffloginlog');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffloginlogid', 'staffloginlogid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffusername', $this->Language->Get('stafftitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ipaddress', $this->Language->Get('ipaddress'), SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('interfacetype', $this->Language->Get('interfacetype'), SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('logindateline', $this->Language->Get('logindateline'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('activitydateline', $this->Language->Get('activitydateline'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('logoutdateline', $this->Language->Get('logoutdateline'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));

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

        if (empty($_fieldContainer['staffname'])) {
            $_titleText = htmlspecialchars($_fieldContainer['staffusername']);
        } else {
            $_titleText = htmlspecialchars($_fieldContainer['staffname']) . ' (' . htmlspecialchars($_fieldContainer['staffusername']) . ')';
        }

        $_fieldContainer['staffusername'] = '<table border="0" cellpadding="3" cellspacing="1" width="100%"><tr><td align="left" valign="top">' . $_titleText . '</td></tr><tr><td align="left" valign="top"><b>' . $_SWIFT->Language->Get('useragent') . '</b> ' . htmlspecialchars($_fieldContainer['useragent']) . '</td></tr></table>';

        if ($_fieldContainer['loginresult'] == SWIFT_StaffLoginLog::LOGIN_FAILURE) {
            $_fieldContainer['staffusername'] = '<div class="errorrow">' . $_fieldContainer['staffusername'] . '</div>';
        }

        if (empty($_fieldContainer['forwardedipaddress'])) {
            $_fieldContainer['ipaddress'] = '<table border="0" cellpadding="3" cellspacing="1" width="100%"><tr><td align="left" valign="top">' . htmlspecialchars($_fieldContainer['ipaddress']) . '</td></tr></table>';
        } else {
            $_fieldContainer['ipaddress'] = '<table border="0" cellpadding="3" cellspacing="1" width="100%"><tr><td align="left" valign="top">' . htmlspecialchars($_fieldContainer['ipaddress']) . '</td></tr><tr><td align="left" valign="top"><b>' . $_SWIFT->Language->Get('forwardedipaddress') . '</b> ' . htmlspecialchars($_fieldContainer['forwardedipaddress']) . '</td></tr></table>';
        }

        $_fieldContainer['logindateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['logindateline']);
        $_fieldContainer['logoutdateline'] = IIF(empty($_fieldContainer['logoutdateline']), $_SWIFT->Language->Get('notavailable'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['logoutdateline']));
        $_fieldContainer['activitydateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['activitydateline']);

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_fieldContainer['loginresult'] == SWIFT_StaffLoginLog::LOGIN_SUCCESS, 'icon_check', 'icon_block') . '.gif" border="0" align="absmiddle" />';

        if ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_ADMIN) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_admin');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_STAFF) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_staff');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_API) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_api');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_WINAPP) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_winapp');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_SYNCWORKS) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_syncworks');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_INSTAALERT) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_instaalert');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_PDA) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_pda');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffLoginLog::INTERFACE_RSS) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_rss');
        }

        return $_fieldContainer;
    }
}

?>
