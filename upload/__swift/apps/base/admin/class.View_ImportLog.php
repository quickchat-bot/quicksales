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

namespace Base\Admin;

use SWIFT;
use SWIFT_Date;
use Base\Models\Import\SWIFT_ImportLog;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use SWIFT_View;

/**
 * The Import Log View Management Class
 *
 * @author Varun Shoor
 */
class View_ImportLog extends SWIFT_View
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('importloggrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'importlogs
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staffname') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ')',

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'importlogs
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('staffname') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'importlogs', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'importlogs');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('importlogid', 'importlogid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('description', $this->Language->Get('logmessage'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffname', $this->Language->Get('staffname'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ipaddress', $this->Language->Get('ipaddress'), SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('date'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);;

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

        $_fieldContainer['staffname'] = htmlspecialchars($_fieldContainer['staffname']);

        $_fieldContainer['ipaddress'] = htmlspecialchars($_fieldContainer['ipaddress']);

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_icon = 'icon_block';
        if ($_fieldContainer['logtype'] == SWIFT_ImportLog::TYPE_SUCCESS) {
            $_icon = 'icon_check';
        } elseif ($_fieldContainer['logtype'] == SWIFT_ImportLog::TYPE_WARNING) {
            $_icon = 'icon_exclamation';
        }

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_icon . '.gif" border="0" align="absmiddle" />';

        $_fieldContainer['description'] = htmlspecialchars($_fieldContainer['description']);

        return $_fieldContainer;
    }
}

?>
