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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\SLA\SWIFT_SLASchedule;

/**
 * The Schedule View Manager
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Schedule extends SWIFT_View
{
    /**
     * Render the SLA Schedule Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_SLASchedule|null $_SWIFT_SLAScheduleObject The SWIFT_SLASchedule Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_SLASchedule $_SWIFT_SLAScheduleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_SLAScheduleObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Schedule/EditSubmit/'. $_SWIFT_SLAScheduleObject->GetSLAScheduleID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Schedule/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_slaScheduleTitle = $_customJavaScript = '';

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_SLAScheduleObject !== null)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Schedule/Delete/' . $_SWIFT_SLAScheduleObject->GetSLAScheduleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('slaschedule'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_slaScheduleTitle = $_SWIFT_SLAScheduleObject->GetProperty('title');

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('slaschedule'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);


        $_GeneralTabObject->Text('title', $this->Language->Get('scheduletitle'), $this->Language->Get('desc_scheduletitle'), $_slaScheduleTitle);

        $_GeneralTabObject->Title($this->Language->Get('scheduledesc'), 'doublearrows.gif');

        $_appendHTML = '<table width="100%" border="0" cellspacing="1" cellpadding="4">';

        $_slaDaysSchedule = array();

        foreach (SWIFT_SLA::GetDays() as $_key => $_val)
        {
            $_slaDayOpen = 0;
            $_openTimeline = 0;
            $_closeTimeline = 0;
            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !isset($_POST['sladay']))
            {
                if (($_val == 'sunday' || $_val == 'saturday'))
                {
                    $_slaDayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;
                } else {
                    $_slaDayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN24;
                }

                $_openTimeline = '09:00';
                $_closeTimeline = '17:00';
            } else {
                if (isset($_POST['sladay'][$_val]) && trim($_POST['sladay'][$_val]) != '')
                {
                    $_slaDayOpen = $_POST['sladay'][$_val];

                    // Custom Hours?
                    if ($_POST['sladay'][$_val] == SWIFT_SLASchedule::SCHEDULE_DAYOPEN && _is_array($_POST['dayHourOpen'][$_val]))
                    {
                        foreach ($_POST['dayHourOpen'][$_val] as $_hourKey => $_hourVal)
                        {
                            $_customJavaScript .= 'newSLAScheduleRow(\'' . $_val . '\', \'' . $_hourVal . ': ' . $_POST['dayMinuteOpen'][$_val][$_hourKey] . '\', \'' . $_POST['dayHourClose'][$_val][$_hourKey] . ': ' . $_POST['dayMinuteClose'][$_val][$_hourKey] . '\', \'' . $_POST['rowId'][$_val][$_hourKey] . '\');';
                        }
                    }

                    $_openTimeline = '09:00';
                    $_closeTimeline = '17:00';
                }
            }

            $_slaDaysSchedule[$_val] = $_slaDayOpen;

            $_subRow = '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
            $_subRow .= '<tr class="settabletitlerowmain2">';
            $_subRow .= '<td width="33.33%" id="sladayrow1' . $_val . '" class="' . IIF($_slaDayOpen == SWIFT_SLASchedule::SCHEDULE_DAYOPEN, 'slascheduletitleopen', 'slascheduletitledefault') . '"><div style="overflow: visible;"><div style="float: left;"><label for="sladay' . SWIFT_SLASchedule::SCHEDULE_DAYOPEN . $_val . '"><input class="swiftradio" type="radio" id="sladay' . SWIFT_SLASchedule::SCHEDULE_DAYOPEN . $_val . '" onclick="changeSLAScheduleRowBG(\'slascheduleform\', this.value, \'' . $_val . '\', false);this.blur();" name="sladay[' . $_val . ']" value="' . SWIFT_SLASchedule::SCHEDULE_DAYOPEN . '"' . IIF($_slaDayOpen == SWIFT_SLASchedule::SCHEDULE_DAYOPEN, ' checked') . ' /> ' . $this->Language->Get('sladayopencustom') . '</label></div><div style="float:right;" id="slacustomadd' . $_val . '"><a href="javascript:void(0);" onclick="newSLAScheduleRow(\'' . $_val . '\', \'' . $_openTimeline . '\', \'' . $_closeTimeline . '\');this.blur();"><img src="' . SWIFT::Get('themepath') . 'images/icon_addplus2.gif" align="absmiddle" border="0" /></a></div></div></td>';
            $_subRow .= '<td width="33.33%" id="sladayrow2' . $_val . '" class="' . IIF($_slaDayOpen == SWIFT_SLASchedule::SCHEDULE_DAYOPEN24, 'slascheduletitleopen24', 'slascheduletitledefault') . '"><label for="sladay' . SWIFT_SLASchedule::SCHEDULE_DAYOPEN24 . $_val . '"><input class="swiftradio" type="radio" id="sladay' . SWIFT_SLASchedule::SCHEDULE_DAYOPEN24 . $_val . '" onclick="changeSLAScheduleRowBG(\'slascheduleform\', this.value, \'' . $_val . '\', false);this.blur();" name="sladay[' . $_val . ']" value="' . SWIFT_SLASchedule::SCHEDULE_DAYOPEN24 . '"' . IIF($_slaDayOpen == SWIFT_SLASchedule::SCHEDULE_DAYOPEN24, ' checked'). ' /> ' . $this->Language->Get('sladayopen24') . '</label></td>';
            $_subRow .= '<td id="sladayrow0' . $_val . '" class="' . IIF($_slaDayOpen == SWIFT_SLASchedule::SCHEDULE_DAYCLOSED, 'slascheduletitleclosed', 'slascheduletitledefault') . '"><label for="sladay' . SWIFT_SLASchedule::SCHEDULE_DAYCLOSED . $_val . '"><input class="swiftradio" type="radio" id="sladay' . SWIFT_SLASchedule::SCHEDULE_DAYCLOSED . $_val . '" onclick="changeSLAScheduleRowBG(\'slascheduleform\', this.value, \'' . $_val . '\', false);this.blur();" name="sladay[' . $_val . ']" value="' . SWIFT_SLASchedule::SCHEDULE_DAYCLOSED . '"'. iif($_slaDayOpen == SWIFT_SLASchedule::SCHEDULE_DAYCLOSED, ' checked') . ' /> ' . $this->Language->Get('sladayclosed') . '</label></td>';
            $_subRow .= '</tr>';
            $_subRow .= '</table><div id="slaschedulecontainer' . $_val . '" style="' . IIF($_slaDayOpen != SWIFT_SLASchedule::SCHEDULE_DAYOPEN, 'display: none;') . 'padding: 4px;">';
            $_subRow .= '</div>';

            $_rowClass = $_GeneralTabObject->GetClass();

            $_appendHTML .= '<tr><td class="' . $_rowClass . '" align="left" valign="top" width="12%"><img src="' . SWIFT::Get('themepath') . 'images/icon_calendar.svg" align="absmiddle" border="0" /> ' . $this->Language->Get($_val) . '</td><td class="' . $_rowClass . '" align="left" valign="top">' . $_subRow . '</td></tr>';
        }

        $_appendHTML .= '</table>';

        $_GeneralTabObject->AppendHTML($_appendHTML . '<script language="javascript">QueueFunction(function(){ ' . $_customJavaScript . '}); </script>');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Rating Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('slaschedulegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'slaschedules WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'slaschedules WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'slaschedules', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'slaschedules');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slascheduleid', 'slascheduleid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('title'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('creationdate'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Tickets\Admin\Controller_Schedule', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Schedule/Insert');
        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array "true" on Success, "false" otherwise
     */
    public static function GridRender($_fieldContainer)
    {
        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_calendar.svg' . '" align="absmiddle" border="0" />';
        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/Schedule/Edit/' . (int) ($_fieldContainer['slascheduleid']) . '" viewport="1">' . htmlspecialchars($_fieldContainer['title']) . '</a>';
        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        return $_fieldContainer;
    }
}
