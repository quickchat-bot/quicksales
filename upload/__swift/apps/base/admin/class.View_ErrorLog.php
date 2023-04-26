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
use SWIFT_ErrorLog;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use SWIFT_View;

/**
 * The Error Log View Management Class
 *
 * @author Varun Shoor
 */
class View_ErrorLog extends SWIFT_View
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('errorloggrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'errorlogs WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('errordetails') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('userdata') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'errorlogs WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('errordetails') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('userdata') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'errorlogs', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'errorlogs');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('errorlogid', 'errorlogid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('type', $this->Language->Get('errortype'), SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('errordetails', $this->Language->Get('errordetails'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('errordate'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

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

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . 'icon_block' . '.gif" border="0" align="absmiddle" />';

        $_fieldContainer['errordetails'] = nl2br(htmlspecialchars($_fieldContainer['errordetails']));

        if ($_fieldContainer['type'] == SWIFT_ErrorLog::TYPE_DATABASE) {
            $_fieldContainer['type'] = $_SWIFT->Language->Get('errorlog_database');
        } elseif ($_fieldContainer['type'] == SWIFT_ErrorLog::TYPE_PHPERROR) {
            $_fieldContainer['type'] = $_SWIFT->Language->Get('errorlog_phperror');
        } elseif ($_fieldContainer['type'] == SWIFT_ErrorLog::TYPE_EXCEPTION) {
            $_fieldContainer['type'] = $_SWIFT->Language->Get('errorlog_exception');
        } elseif ($_fieldContainer['type'] == SWIFT_ErrorLog::TYPE_MAILERROR) {
            $_fieldContainer['type'] = $_SWIFT->Language->Get('errorlog_mailerror');
        } elseif ($_fieldContainer['type'] == SWIFT_ErrorLog::TYPE_GENERAL) {
            $_fieldContainer['type'] = $_SWIFT->Language->Get('errorlog_general');
        } elseif ($_fieldContainer['type'] == SWIFT_ErrorLog::TYPE_LOGINSHARE) {
            $_fieldContainer['type'] = $_SWIFT->Language->Get('errorlog_loginshare');
        }

        return $_fieldContainer;
    }
}

?>
