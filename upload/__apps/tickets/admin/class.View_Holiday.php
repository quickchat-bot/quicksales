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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\SLA\SWIFT_SLAHoliday;

/**
 * The Holiday View Manager Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Holiday extends SWIFT_View
{
    /**
     * Render the SLA Holiday
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_SLAHoliday $_SWIFT_SLAHolidayObject The SWIFT_SLAHoliday Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_SLAHoliday $_SWIFT_SLAHolidayObject = null)
    {
        $_slaMonthContainer = array('january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12);

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Holiday/EditSubmit/'. $_SWIFT_SLAHolidayObject->GetSLAHolidayID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Holiday/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_slaHolidayTitle = '';
        $_slaHolidayFlagIcon = '';
        $_holidayDay = 0;
        $_holidayMonth = 0;
        $_slaPlanIDList = array();
        $_isCustom = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Holiday/Delete/' . $_SWIFT_SLAHolidayObject->GetSLAHolidayID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('slaholiday'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_slaHolidayTitle = $_SWIFT_SLAHolidayObject->GetProperty('title');
            $_POST['holidayday'] = (int) ($_SWIFT_SLAHolidayObject->GetProperty('holidayday'));
            $_POST['holidaymonth'] = (int) ($_SWIFT_SLAHolidayObject->GetProperty('holidaymonth'));
            $_isCustom = (int) ($_SWIFT_SLAHolidayObject->GetProperty('iscustom'));
            $_slaHolidayFlagIcon = $_SWIFT_SLAHolidayObject->GetProperty('flagicon');

            $_slaPlanIDList = $_SWIFT_SLAHolidayObject->GetSLAPlanIDList();
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('slaholiday'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('holidaytitle'), $this->Language->Get('desc_holidaytitle'), $_slaHolidayTitle);

        $_holidayDateHTML = '<select name="holidayday" class="swiftselect">';
        for ($_ii=1; $_ii<=31; $_ii++)
        {
            $_isSelected = false;
            if ((isset($_POST['holidayday']) && $_POST['holidayday'] == $_ii) || (!isset($_POST['holidayday']) && $_ii == 1))
            {
                $_isSelected = true;
            }

            $_holidayDateHTML .= '<option value="' . $_ii . '"' . IIF($_isSelected, ' selected') . '>' . $_ii . '</option>';
        }

        $_holidayDateHTML .= '</select> <select name="holidaymonth" class="swiftselect">';
        foreach ($_slaMonthContainer as $_key => $_val)
        {
            $_isSelected = false;

            if ((isset($_POST['holidaymonth']) && $_POST['holidaymonth'] == $_val) || (!isset($_POST['holidaymonth']) && $_val == 1))
            {
                $_isSelected = true;
            }

            $_holidayDateHTML .= '<option value="' . ($_val) . '"' . iif($_isSelected, ' selected') . '>' . $this->Language->Get('sla' . $_key) . '</option>';
        }

        $_holidayDateHTML .= '</select>';

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('holidaydate'), $this->Language->Get('desc_holidaydate'), $_holidayDateHTML);

        $_GeneralTabObject->Text('flagicon', $this->Language->Get('flagicon'), $this->Language->Get('desc_flagicon'), $_slaHolidayFlagIcon);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN SLA PLAN TAB
         * ###############################################
         */
        $_SLAPlanTabObject = $this->UserInterface->AddTab($this->Language->Get('tabslaplans'), 'icon_sla.gif', 'slaplans');

        $_slaPlanContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaplans ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_slaPlanContainer[$this->Database->Record['slaplanid']] = $this->Database->Record;
        }

        $_SLAPlanTabObject->Overflow('230');

        $_SLAPlanTabObject->YesNo('iscustom', $this->Language->Get('slaiscustom'), $this->Language->Get('desc_slaiscustom'), $_isCustom);
        $_SLAPlanTabObject->Title($this->Language->Get('slaplans'), 'doublearrows.gif');

        foreach ($_slaPlanContainer as $_key => $_val)
        {
            $_isSelected = false;
            if (in_array($_val['slaplanid'], $_slaPlanIDList))
            {
                $_isSelected = true;
            }

            $_SLAPlanTabObject->YesNo('slaplans[' . $_val['slaplanid'] . ']', $_val['title'], '', $_isSelected);
        }

        /*
         * ###############################################
         * END PERMISSIONS TAB
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('slaholidaygrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'slaholidays WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'slaholidays WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'slaholidays', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'slaholidays');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slaholidayid', 'slaholidayid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('title'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('holidaydate', $this->Language->Get('holidaydate'), SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Tickets\Admin\Controller_Holiday', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Holiday/Insert');

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
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['icon'] = '<img src="' . IIF(empty($_fieldContainer['flagicon']), SWIFT::Get('themepath') . 'images/icon_calendar.svg', str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_fieldContainer['flagicon'])). '" align="absmiddle" border="0" />';

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/Holiday/Edit/' . (int) ($_fieldContainer['slaholidayid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Tickets/Holiday/Edit/' . (int) ($_fieldContainer['slaholidayid']) . "', 'editslaholiday', '" . sprintf($_SWIFT->Language->Get('wineditslaholiday'), addslashes(htmlspecialchars($_fieldContainer['title']))) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 680, 425, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['holidaydate'] = strftime('%d %B', mktime(0, 0, 0, $_fieldContainer['holidaymonth'], $_fieldContainer['holidayday']));

        return $_fieldContainer;
    }
}
