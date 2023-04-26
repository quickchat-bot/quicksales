<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

/**
 * The Report Schedule View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Andriy Lesyuk
 */
class View_Schedule extends SWIFT_View
{
    /**
     * Render the Schedules tab
     *
     * @author Andriy Lesyuk
     * @param SWIFT_Report $_SWIFT_ReportObject The Report Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderReport($_SWIFT_ReportObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_schedulesContainer = SWIFT_ReportSchedule::RetrieveOnReportAndStaff($_SWIFT_ReportObject, $_SWIFT->Staff);

        $_renderHTML = '<table width="100%" cellspacing="0" cellpadding="4" border="0">';
        $_renderHTML .= '<tbody>';
        $_renderHTML .= '<tr>';
        $_renderHTML .= '<td valign="middle" align="center" class="gridtabletitlerow" width="16" nowrap>&nbsp;</td>';
        $_renderHTML .= '<td valign="middle" align="left" class="gridtabletitlerow" width="100">' . $this->Language->Get('exportformat') . '</td>';
        $_renderHTML .= '<td valign="middle" align="left" class="gridtabletitlerow">' . $this->Language->Get('ccemailaddresses') . '</td>';
        $_renderHTML .= '<td valign="middle" align="center" class="gridtabletitlerow" width="120">' . $this->Language->Get('recurrence') . '</td>';
        $_renderHTML .= '<td valign="middle" align="center" class="gridtabletitlerow" width="200">' . $this->Language->Get('nextexecutiondate') . '</td>';
        $_renderHTML .= '<td valign="middle" align="center" class="gridtabletitlerow" width="200">' . $this->Language->Get('lastexecutiondate') . '</td>';
        $_renderHTML .= '</tr>';

        foreach ($_schedulesContainer as $_scheduleID => $_ReportObject_Schedule) {

            // URL
            $_scheduleURL = SWIFT::Get('basename') . '/Reports/Schedule/Dialog/' . (int) ($_SWIFT_ReportObject->GetReportID()) . '/' . (int) ($_ReportObject_Schedule->GetScheduleID());
            $_scheduleURLPrefix = '<a href="javascript: void(0);" onclick="javascript: return UICreateWindow(\'' . $_scheduleURL . "', 'schedulereport', '" . $this->Language->Get('reportemailingproperties') . "', '" . $this->Language->Get('loadingwindow') . '\', 700, 360, true, this);">';
            $_scheduleURLSuffix = '</a>';

            if ($_SWIFT->Staff->GetPermission('staff_rcanupdateschedule') == '0') {
                $_scheduleURLPrefix = '';
                $_scheduleURLSuffix = '';
            }

            // Get icon
            switch ($_ReportObject_Schedule->GetProperty('format'))
            {
                case 'Excel':
                case 'Excel5':
                    $_exportIcon = 'mimeico_excel.gif';
                    break;

//                case 'PDF':
//                    $_exportIcon = 'mimeico_pdf.gif';
//                    break;

                case 'CSV':
                    $_exportIcon = 'mimeico_text.gif';
                    break;

                case 'HTML':
                    $_exportIcon = 'mimeico_html.gif';
                    break;

                default:
                    $_exportIcon = 'mimeico_blank.gif';
                    break;
            }

            $_exportIconHTML = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_exportIcon . '" align="absmiddle" border="0" />';

            // Get text for format
            switch ($_ReportObject_Schedule->GetProperty('format'))
            {
                case 'Excel':
                    $_exportFormat = $this->Language->Get('formatexcel2007');
                    break;

                default:
                    $_exportFormat = $this->Language->Get('format' . mb_strtolower($_ReportObject_Schedule->GetProperty('format')));
                    break;
            }

            // Get text for recurrence type
            $_recurrenceTypeText = '';
            $_recurrenceTypesContainer = SWIFT_ReportSchedule::GetSupportedRecurrenceTypes();

            if (isset($_recurrenceTypesContainer[$_ReportObject_Schedule->GetProperty('recurrencetype')])) {
                $_recurrenceTypeText = $this->Language->Get('recurrence_' . $_recurrenceTypesContainer[$_ReportObject_Schedule->GetProperty('recurrencetype')]);
            }

            // Get email addresses
            $_emailAddresses = $_ReportObject_Schedule->GetProperty('ccemails');
            if (!empty($_emailAddresses)) {
                $_emailAddressesHTML = htmlspecialchars($_emailAddresses);
            } else {
                $_emailAddressesHTML = $_SWIFT->Language->Get('na');
            }

            // Get last run date
            $_lastRunDate = $_ReportObject_Schedule->GetProperty('lastrun');
            if ($_lastRunDate) {
                $_lastRunDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_lastRunDate);
            } else {
                $_lastRunDate = $_SWIFT->Language->Get('na');
            }

            $_renderHTML .= '<tr class="tablerow1_tr">';
            $_renderHTML .= '<td valign="middle" align="center" class="tablerow1">' . $_exportIconHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_scheduleURLPrefix . $_exportFormat . $_scheduleURLSuffix . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_scheduleURLPrefix . $_emailAddressesHTML . $_scheduleURLSuffix . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_scheduleURLPrefix . $_recurrenceTypeText . $_scheduleURLSuffix . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_scheduleURLPrefix . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_ReportObject_Schedule->GetProperty('nextrun')) . $_scheduleURLSuffix . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_lastRunDate . '</td>';
            $_renderHTML .= '</tr>';
        }

        $_renderHTML .= '</tbody>';
        $_renderHTML .= '</table>';

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'reParseDoc();';
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Render the Schedule Report Dialog
     *
     * @author Andriy Lesyuk
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @param SWIFT_ReportSchedule $_SWIFT_ReportScheduleObject
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderScheduleDialog(SWIFT_Report $_SWIFT_ReportObject, SWIFT_ReportSchedule $_SWIFT_ReportScheduleObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($_SWIFT_ReportScheduleObject) {
            $_mode = SWIFT_UserInterface::MODE_EDIT;
        } else {
            $_mode = SWIFT_UserInterface::MODE_INSERT;
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_dialogAction = '/Reports/Schedule/Submit/' . $_SWIFT_ReportObject->GetReportID() . '/' . $_SWIFT_ReportScheduleObject->GetScheduleID();
        } else {
            $_dialogAction = '/Reports/Schedule/Submit/' . $_SWIFT_ReportObject->GetReportID();
        }

        $this->UserInterface->Start(get_short_class($this),$_dialogAction, $_mode, true);

        if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) {
            if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            } else {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }
        }

        if (($_mode == SWIFT_UserInterface::MODE_EDIT) && ($_SWIFT->Staff->GetPermission('staff_rcandeleteschedule') != '0')) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Reports/Schedule/Delete/' . $_SWIFT_ReportScheduleObject->GetScheduleID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_buttonLabel = $this->Language->Get('update');
        } else {
            $_buttonLabel = $this->Language->Get('schedule');
        }

        $_buttonText = '<input type="button" name="submitbutton" id="%formid%_submit" class="rebuttonblue" onclick="javascript: $(\'body\').css(\'overflow\',\'scroll\');$(\'#%formid%\').submit();" value="'. $_buttonLabel .'" onfocus="blur();" /> ';
        $_buttonText .= '<input type="button" name="cancel" class="rebuttonred" value="'. $this->Language->Get('cancel') .'" onclick="javascript: UIDestroyAllDialogs();" onfocus="blur();" />';

        $this->UserInterface->OverrideButtonText($_buttonText);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_ScheduleTabObject = $this->UserInterface->AddTab($this->Language->Get('emailingproperties'), 'icon_calendar.svg', 'schedulereport', true);

        $_optionsContainer = array();
        $_index = 0;
        $_exportFormatContainer = SWIFT_ReportExport::GetExportFormatContainer();
        foreach ($_exportFormatContainer as $_exportFormat => $_exportFormatInfo) {
            $_optionsContainer[$_index]['title'] = $this->Language->Get($_exportFormatInfo[0]);
            $_optionsContainer[$_index]['value'] = $_exportFormat;

            if ($_SWIFT_ReportScheduleObject && ($_exportFormat == $_SWIFT_ReportScheduleObject->GetProperty('format'))) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_ScheduleTabObject->Select('exportformat', $this->Language->Get('exportformat'), $this->Language->Get('desc_exportformat'), $_optionsContainer);

        $_optionsContainer = array();
        $_index = 0;
        $_recurrenceTypesContainer = SWIFT_ReportSchedule::GetSupportedRecurrenceTypes();
        foreach ($_recurrenceTypesContainer as $_recurrenceType => $_recurrenceTypeID) {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('recurrence_' . $_recurrenceTypeID);
            $_optionsContainer[$_index]['value'] = $_recurrenceTypeID;

            if ($_SWIFT_ReportScheduleObject && ($_recurrenceType == $_SWIFT_ReportScheduleObject->GetProperty('recurrencetype'))) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_ScheduleTabObject->Select('recurrence', $this->Language->Get('recurrence'), $this->Language->Get('desc_recurrence'), $_optionsContainer);

        if ($_SWIFT_ReportScheduleObject) {
            $_executionDate = date(SWIFT_Date::GetCalendarDateFormat(), $_SWIFT_ReportScheduleObject->GetProperty('nextrun'));
            $_executionTime = $_SWIFT_ReportScheduleObject->GetProperty('nextrun');
        } else {
            $_executionDate = date(SWIFT_Date::GetCalendarDateFormat(), DATENOW);
            $_executionTime = DATENOW;
        }

        $_ScheduleTabObject->Date('executiondate', $this->Language->Get('firstexecutiondate'), $this->Language->Get('desc_executiondate'), $_executionDate, $_executionTime, true, true);

        $_scheduleEmailContainer = array();

        if ($_SWIFT_ReportScheduleObject && $_SWIFT_ReportScheduleObject->GetProperty('ccemails')) {
            $_scheduleEmailContainer = explode(', ', $_SWIFT_ReportScheduleObject->GetProperty('ccemails'));
        }

        $_ScheduleTabObject->TextMultipleAutoComplete('schedulecc', $this->Language->Get('schedulecc'), $this->Language->Get('desc_schedulecc'), '/Tickets/Ajax/SearchEmail', $_scheduleEmailContainer, 'fa-envelope-o', false, false, 2, false, false, false, '', array('containemail' => true));

        $this->UserInterface->End();

        return true;
    }

}
?>
