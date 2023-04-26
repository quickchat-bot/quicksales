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
use SWIFT_Date;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use SWIFT_View;

/**
 * The Activity Log View
 *
 * @author Varun Shoor
 */
class View_ActivityLog extends SWIFT_View
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('activityloggrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'staffactivitylog WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staffname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('forwardedipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('useragent') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staffactivitylog WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staffname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('forwardedipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('useragent') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'staffactivitylog', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'staffactivitylog');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffactivitylogid', 'staffactivitylogid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffname', $this->Language->Get('logdetails'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ipaddress', $this->Language->Get('ipaddress'), SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('interfacetype', $this->Language->Get('interfacetype'), SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('logdateline'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

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

        $_displayClass = $_displayText = '';
        if ($_fieldContainer['actiontype'] == SWIFT_StaffActivityLog::ACTION_INSERT) {
            $_displayText = $_SWIFT->Language->Get('accreated');
            $_displayClass = 'blocknotecountergreen';
        } elseif ($_fieldContainer['actiontype'] == SWIFT_StaffActivityLog::ACTION_UPDATE) {
            $_displayText = $_SWIFT->Language->Get('acupdated');
            $_displayClass = 'blocknotecounterorange';
        } elseif ($_fieldContainer['actiontype'] == SWIFT_StaffActivityLog::ACTION_DELETE) {
            $_displayText = $_SWIFT->Language->Get('acdeleted');
            $_displayClass = 'blocknotecounterred';
        } elseif ($_fieldContainer['actiontype'] == SWIFT_StaffActivityLog::ACTION_OTHER) {
            $_displayText = $_SWIFT->Language->Get('acother');
            $_displayClass = 'blocknotecounterred';
        }

        $_fieldContainer['staffname'] = '<table border="0" cellpadding="3" cellspacing="1" width="100%"><tr><td align="left" valign="top"><div style="display: inline;">' . htmlspecialchars($_fieldContainer['staffname']) . ' - </div>' . SWIFT_Date::ColorTime(DATENOW - $_fieldContainer['dateline']) . '</td></tr><tr><td align="left" valign="top">' . '<div class="' . $_displayClass . '">' . $_displayText . '</div> ' . htmlspecialchars($_fieldContainer['description']) . '</td></tr></table>';

        if (empty($_fieldContainer['forwardedipaddress'])) {
            $_fieldContainer['ipaddress'] = '<table border="0" cellpadding="3" cellspacing="1" width="100%"><tr><td align="left" valign="top">' . htmlspecialchars($_fieldContainer['ipaddress']) . '</td></tr></table>';
        } else {
            $_fieldContainer['ipaddress'] = '<table border="0" cellpadding="3" cellspacing="1" width="100%"><tr><td align="left" valign="top">' . htmlspecialchars($_fieldContainer['ipaddress']) . '</td></tr><tr><td align="left" valign="top"><b>' . $_SWIFT->Language->Get('forwardedipaddress') . '</b> ' . htmlspecialchars($_fieldContainer['forwardedipaddress']) . '</td></tr></table>';
        }

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_activitylog.gif" border="0" align="absmiddle" />';

        if ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_ADMIN) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_admin');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_STAFF) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_staff');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_API) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_api');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_WINAPP) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_winapp');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_SYNCWORKS) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_syncworks');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_INSTAALERT) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_instaalert');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_PDA) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_pda');
        } elseif ($_fieldContainer['interfacetype'] == SWIFT_StaffActivityLog::INTERFACE_RSS) {
            $_fieldContainer['interfacetype'] = $_SWIFT->Language->Get('interface_rss');
        }

        return $_fieldContainer;
    }
}

?>
