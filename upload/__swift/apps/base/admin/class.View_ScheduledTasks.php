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
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use SWIFT_View;

/**
 * The Scheduled Tasks View Management Class
 *
 * @author Varun Shoor
 */
class View_ScheduledTasks extends SWIFT_View
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
     * Render the Scheduled Tasks Log Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderTaskLogGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('scheduledtaskloggrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'cronlogs WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('crontitle') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'cronlogs WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('crontitle') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('description') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'cronlogs', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'cronlogs');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('cronlogid', 'cronlogid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('crontitle', $this->Language->Get('title'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
//        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('description', $this->Language->Get('status'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('executiontime'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'TaskLogGridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_ScheduledTasks', 'DeleteLogList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Task Log Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array
     */
    public static function TaskLogGridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . 'icon_log.gif' . '" align="absmiddle" border="0" />';

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        return $_fieldContainer;
    }

    /**
     * Render the Scheduled Tasks Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('scheduledtaskgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'cron WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('name') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'cron WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('name') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT *, ((ABS(cday) * 24 * 60) + (ABS(chour) * 60) + ABS(cminute)) as runsevery FROM ' . TABLE_PREFIX . 'cron', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'cron');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('cronid', 'cronid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('name', $this->Language->Get('title'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('nextrun', $this->Language->Get('nextrun'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('lastrun', $this->Language->Get('lastrun'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('runsevery', $this->Language->Get('runsevery'), SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle', array('Base\Admin\Controller_ScheduledTasks', 'DisableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle', array('Base\Admin\Controller_ScheduledTasks', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('execute'), 'fa-caret-right', array('Base\Admin\Controller_ScheduledTasks', 'ExecuteList'), $this->Language->Get('actionconfirm')));

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

        $_runsEveryX = '';
        if ($_fieldContainer['cminute'] == -1 && $_fieldContainer['cday'] == 0 && $_fieldContainer['chour'] == 0) {
            // Append one minute to this and save
            $_runsEveryX = sprintf($_SWIFT->Language->Get('xminute'), '1');
        } elseif ($_fieldContainer['cminute'] > 0 && $_fieldContainer['cday'] == 0 && $_fieldContainer['chour'] == 0) {
            // Append $cminute to this and save, this means we run it every x minutes
            $_runsEveryX = sprintf($_SWIFT->Language->Get('xminute'), $_fieldContainer['cminute']);
        } elseif ($_fieldContainer['chour'] == -1 && $_fieldContainer['cday'] == 0 && $_fieldContainer['cminute'] == 0) {
            // We run this every 1 hour
            $_runsEveryX = sprintf($_SWIFT->Language->Get('xhour'), '1');
        } elseif ($_fieldContainer['chour'] > 0 && $_fieldContainer['cday'] == 0 && $_fieldContainer['cminute'] == 0) {
            // We run this every x hour
            $_runsEveryX = sprintf($_SWIFT->Language->Get('xhour'), $_fieldContainer['chour']);
        } elseif ($_fieldContainer['cday'] == -1 && $_fieldContainer['cminute'] == 0 && $_fieldContainer['chour'] == 0) {
            // We run this every day
            $_runsEveryX = sprintf($_SWIFT->Language->Get('xday'), '1');
        } elseif ($_fieldContainer['cday'] > 0 && $_fieldContainer['chour'] == 0 && $_fieldContainer['cminute'] == 0) {
            // We run this every x days
            $_runsEveryX = sprintf($_SWIFT->Language->Get('xday'), $_fieldContainer['cday']);
        }

        $_cronTitle = Controller_ScheduledTasks::_GetCronTitle($_fieldContainer['name']);

        $_fieldContainer['name'] = '<b>' . IIF($_fieldContainer['autorun'] == '0', '<span class="disabledtext">' . $_cronTitle . '</span>', $_cronTitle) . '</b><br />' . $_SWIFT->Language->Get('securl') . SWIFT::Get('swiftpath') . 'cron/index.php?/' . $_fieldContainer['app'] . '/' . $_fieldContainer['controller'] . '/' . $_fieldContainer['action'];

        $_fieldContainer['nextrun'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['nextrun']);
        $_fieldContainer['lastrun'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['lastrun']);

        $_fieldContainer['runsevery'] = $_runsEveryX;

        $_fieldContainer['icon'] = '<i class="fa fa-calendar" aria-hidden="true"></i>';

        return $_fieldContainer;
    }
}

?>
