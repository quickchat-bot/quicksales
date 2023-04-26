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

namespace Tickets\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use SWIFT;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

trait View_TicketNewTicketTrait {
    /**
     * Render the New Ticket Dialog
     *
     * @author Varun Shoor
     * @param bool $_chatObjectID (OPTIONAL) The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public function RenderNewTicketDialog($_chatObjectID = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();

        $_optionsContainer = array();
        $_index = 0;

        foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
            if (!in_array($_departmentID, $_assignedDepartmentIDList) && $_SWIFT->Settings->Get('t_restrictnewticket') == '1') {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $_departmentContainer['title'];
            $_optionsContainer[$_index]['value'] = $_departmentID;

            if ($_index == 0) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;

            $subdepartments = (array)$_departmentContainer['subdepartments'];
            /**
             * @var int $_subDepartmentID
             * @var array $_subDepartmentContainer
             */
            foreach ($subdepartments as $_subDepartmentID => $_subDepartmentContainer) {
                if (!in_array($_subDepartmentID, $_assignedDepartmentIDList) && $_SWIFT->Settings->Get('t_restrictnewticket') == '1') {
                    continue;
                }

                $_optionsContainer[$_index]['title'] = ' |- ' . $_subDepartmentContainer['title'];
                $_optionsContainer[$_index]['value'] = $_subDepartmentID;

                $_index++;
            }
        }

        $this->UserInterface->Start('newticketdialog', '/Tickets/Ticket/NewTicketForm', SWIFT_UserInterface::MODE_EDIT, false);

        if (count($_optionsContainer)) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('nt_next'), 'fa-chevron-circle-right ');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
        */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'generalnt', true);

        $_radioContainer = array();
        $_radioContainer[0]['title'] = $this->Language->Get('nt_sendmail');
        $_radioContainer[0]['value'] = 'sendmail';
        $_radioContainer[0]['checked'] = true;

        $_radioContainer[1]['title'] = $this->Language->Get('nt_asuser');
        $_radioContainer[1]['value'] = 'user';

        $_GeneralTabObject->Radio('tickettype', $this->Language->Get('newticket_type'), $this->Language->Get('desc_newticket_type'), $_radioContainer, false);


        $_GeneralTabObject->Select('departmentid', $this->Language->Get('newticket_department'), $this->Language->Get('desc_newticket_department'), $_optionsContainer, '', '', '', false, 'width: 180px;');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
        */

        if (!empty($_chatObjectID)) {
            $this->UserInterface->Hidden('chatobjectid', $_chatObjectID);
        }

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the New TIcket Tab
     *
     * @author Varun Shoor
     * @param mixed $_ticketType
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderNewTicket($_ticketType, $_departmentID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_departmentContainer = $_departmentCache[$_departmentID];

        $this->UserInterface->Start('newticket', '/Tickets/Ticket/NewTicketForm', SWIFT_UserInterface::MODE_INSERT, false, true);

        if ($_ticketType == self::TAB_NEWTICKET_EMAIL) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('dispatchsend'), 'fa-check-circle', '/Tickets/Ticket/NewTicketSubmit/sendmail', SWIFT_UserInterfaceToolbar::LINK_FORM);
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('dispatchcreate'), 'fa-check-circle', '/Tickets/Ticket/NewTicketSubmit/user', SWIFT_UserInterfaceToolbar::LINK_FORM);
        }
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-sticky-note-o', "\$('#newticketnotes').toggle(); \$('#newticketticketnotes').focus();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'newticketnotes');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('dispatchattachfile'), 'fa-paperclip', "\$('#newticketattachments').toggle();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'newticketattachments');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('options'), 'fa-cogs', "\$('#newticketoptions').toggle();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'newticketoptions');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_NewTicketTabObject = $this->UserInterface->AddTab(sprintf($this->Language->Get('tabnewticket2'), text_to_html_entities($_departmentContainer['title'])), 'icon_ticket.png', 'general', true, false, 0);
        $_NewTicketTabObject->SetColumnWidth('15%');

        $this->RenderDispatchTab($_ticketType, $_NewTicketTabObject, null, null, array(), $_departmentID);

        /*
         * ###############################################
         * BEGIN RECURRENCE TAB
         * ###############################################
         */

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertrecurrence') != '0') {
            /** @var SWIFT_UserInterfaceTab $_RecurrenceTab */
            $_RecurrenceTab = $this->UserInterface->AddTab($this->Language->Get('tabrecurrence'), 'icon_spacer.gif', 'recurrence', false);
            $_RecurrenceTab->LoadToolbar();

            if ($_ticketType == self::TAB_NEWTICKET_EMAIL) {
                $_RecurrenceTab->Toolbar->AddButton($this->Language->Get('dispatchsend'), 'fa-check-circle', '/Tickets/Ticket/NewTicketSubmit/sendmail', SWIFT_UserInterfaceToolbar::LINK_FORM);
            } else {
                $_RecurrenceTab->Toolbar->AddButton($this->Language->Get('dispatchcreate'), 'fa-check-circle', '/Tickets/Ticket/NewTicketSubmit/user', SWIFT_UserInterfaceToolbar::LINK_FORM);
            }

            $_RecurrenceTab->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_RecurrenceTab->SetColumnWidth('200px');

            $_recurrenceRadioHTML = $_recurrenceDataHTML = '';

            $_recurrenceDataHTML .= '<div id="recurrencecontainer_none">' . $this->Language->Get('recurnotactivated') . '</div>';

            $_recurrenceDataHTML .= '<div id="recurrencecontainer_daily" style="display: none;"><label for="recurdaily_default"><input type="radio" id="recurdaily_default" name="recurrence_daily_type" value="default" checked /> ' . $this->Language->Get('rec_every') . '</label> <input type="text" class="swifttextnumeric" size="3" value="1" name="recurrence_daily_step" onfocus="javascript: $(\'#recurdaily_default\').attr(\'checked\', true);" /> <label for="recurdaily_default">' . $this->Language->Get('rec_days') . '</label><br /><br />';
            $_recurrenceDataHTML .= '<label for="recurdaily_ext"><input type="radio" id="recurdaily_ext" name="recurrence_daily_type" value="extended" /> ' . $this->Language->Get('rec_everyweekday') . '</label>';
            $_recurrenceDataHTML .= '</div>';

            $_recurrenceDataHTML .= '<div id="recurrencecontainer_weekly" style="display: none;"><label for="recurweekly_default"><input type="radio" id="recurweekly_default" name="recurrence_weekly_type" value="default" checked /> ' . $this->Language->Get('rec_every') . '</label> <input type="text" class="swifttextnumeric" size="3" value="1" name="recurrence_weekly_step" /> <label for="recurweekly_default">' . $this->Language->Get('rec_weeks') . '</label><br /><br />';
            $_recurrenceDataHTML .= '<label for="recurweekly_monday"><input type="checkbox" id="recurweekly_monday" name="recurrence_weekly_ismonday" value="1" checked /> ' . $this->Language->Get('rec_monday') . '</label>&nbsp;&nbsp;&nbsp;';
            $_recurrenceDataHTML .= '<label for="recurweekly_tuesday"><input type="checkbox" id="recurweekly_tuesday" name="recurrence_weekly_istuesday" value="1" checked /> ' . $this->Language->Get('rec_tuesday') . '</label>&nbsp;&nbsp;&nbsp;';
            $_recurrenceDataHTML .= '<label for="recurweekly_wednesday"><input type="checkbox" id="recurweekly_wednesday" name="recurrence_weekly_iswednesday" value="1" checked /> ' . $this->Language->Get('rec_wednesday') . '</label>&nbsp;&nbsp;&nbsp;';
            $_recurrenceDataHTML .= '<label for="recurweekly_thursday"><input type="checkbox" id="recurweekly_thursday" name="recurrence_weekly_isthursday" value="1" checked /> ' . $this->Language->Get('rec_thursday') . '</label>&nbsp;&nbsp;&nbsp;';
            $_recurrenceDataHTML .= '<label for="recurweekly_friday"><input type="checkbox" id="recurweekly_friday" name="recurrence_weekly_isfriday" value="1" checked /> ' . $this->Language->Get('rec_friday') . '</label>&nbsp;&nbsp;&nbsp;';
            $_recurrenceDataHTML .= '<label for="recurweekly_saturday"><input type="checkbox" id="recurweekly_saturday" name="recurrence_weekly_issaturday" value="1" /> ' . $this->Language->Get('rec_saturday') . '</label>&nbsp;&nbsp;&nbsp;';
            $_recurrenceDataHTML .= '<label for="recurweekly_sunday"><input type="checkbox" id="recurweekly_sunday" name="recurrence_weekly_issunday" value="1" /> ' . $this->Language->Get('rec_sunday') . '</label>&nbsp;&nbsp;&nbsp;';
            $_recurrenceDataHTML .= '</div>';

            $_recurrenceDataHTML .= '<div id="recurrencecontainer_monthly" style="display: none;"><label for="recurmonthly_default"><input type="radio" id="recurmonthly_default" name="recurrence_monthly_type" value="default" checked /> ' . $this->Language->Get('rec_day') . '</label> <input type="text" class="swifttextnumeric" size="3" value="' . gmdate('d', DATENOW) . '" name="recurrence_monthly_day" onfocus="javascript: $(\'#recurmonthly_default\').attr(\'checked\', true);" /> <label for="recurmonthly_default">' . $this->Language->Get('rec_ofevery') . '</label> <input type="text" class="swifttextnumeric" size="3" value="1" name="recurrence_monthly_step" onfocus="javascript: $(\'#recurmonthly_default\').attr(\'checked\', true);" />  <label for="recurmonthly_default">' . $this->Language->Get('rec_months') . '</label> <br /><br />';
            $_recurrenceDataHTML .= '<label for="recurmonthly_ext"><input type="radio" id="recurmonthly_ext" name="recurrence_monthly_type" value="extended" /> ' . $this->Language->Get('rec_the') . '</label> ';
            $_recurrenceDataHTML .= '<select name="recurrence_monthly_extdaystep" class="swiftselect" onfocus="javascript: $(\'#recurmonthly_ext\').attr(\'checked\', true);"><option value="first" selected>' . $this->Language->Get('rec_first') . '</option><option value="second">' . $this->Language->Get('rec_second') . '</option><option value="third">' . $this->Language->Get('rec_third') . '</option><option value="fourth">' . $this->Language->Get('rec_fourth') . '</option><option value="fifth">' . $this->Language->Get('rec_fifth') . '</option></select> ';
            $_recurrenceDataHTML .= '<select name="recurrence_monthly_extday" class="swiftselect" onfocus="javascript: $(\'#recurmonthly_ext\').attr(\'checked\', true);"><option value="monday" selected>' . $this->Language->Get('rec_monday') . '</option><option value="tuesday">' . $this->Language->Get('rec_tuesday') . '</option><option value="wednesday">' . $this->Language->Get('rec_wednesday') . '</option><option value="thursday">' . $this->Language->Get('rec_thursday') . '</option><option value="friday">' . $this->Language->Get('rec_friday') . '</option><option value="saturday">' . $this->Language->Get('rec_saturday') . '</option><option value="sunday">' . $this->Language->Get('rec_sunday') . '</option></select> ';
            $_recurrenceDataHTML .= '<label for="recurmonthly_ext">' . $this->Language->Get('rec_ofevery') . '</label> <input type="text" class="swifttextnumeric" size="3" value="1" name="recurrence_monthly_stepext" onfocus="javascript: $(\'#recurmonthly_ext\').attr(\'checked\', true);" />  <label for="recurmonthly_ext">' . $this->Language->Get('rec_months') . '</label> <br /><br />';
            $_recurrenceDataHTML .= '</div>';

            $_yearlyMonthlyOptions = '<option value="1"' . IIF(date('m') == '1', ' selected') . '>' . $this->Language->Get('cal_january') . '</option><option value="2"' . IIF(date('m') == '2', ' selected') . '>' . $this->Language->Get('cal_february') . '</option><option value="3"' . IIF(date('m') == '3', ' selected') . '>' . $this->Language->Get('cal_march') . '</option><option value="4"' . IIF(date('m') == '4', ' selected') . '>' . $this->Language->Get('cal_april') . '</option><option value="5"' . IIF(date('m') == '5', ' selected') . '>' . $this->Language->Get('cal_may') . '</option><option value="6"' . IIF(date('m') == '6', ' selected') . '>' . $this->Language->Get('cal_june') . '</option><option value="7"' . IIF(date('m') == '7', ' selected') . '>' . $this->Language->Get('cal_july') . '</option><option value="8"' . IIF(date('m') == '8', ' selected') . '>' . $this->Language->Get('cal_august') . '</option><option value="9"' . IIF(date('m') == '9', ' selected') . '>' . $this->Language->Get('cal_september') . '</option><option value="10"' . IIF(date('m') == '10', ' selected') . '>' . $this->Language->Get('cal_october') . '</option><option value="11"' . IIF(date('m') == '11', ' selected') . '>' . $this->Language->Get('cal_november') . '</option><option value="12"' . IIF(date('m') == '12', ' selected') . '>' . $this->Language->Get('cal_december') . '</option>';
            $_recurrenceDataHTML .= '<div id="recurrencecontainer_yearly" style="display: none;"><label for="recuryearly_default"><input type="radio" id="recuryearly_default" name="recurrence_yearly_type" value="default" checked /> ' . $this->Language->Get('rec_every') . '</label> <select class="swiftselect" name="recurrence_yearly_month" onfocus="javascript: $(\'#recuryearly_default\').attr(\'checked\', true);">' . $_yearlyMonthlyOptions . '</select> <input type="text" class="swifttextnumeric" size="3" value="' . gmdate('d', DATENOW) . '" name="recurrence_yearly_monthday" onfocus="javascript: $(\'#recuryearly_default\').attr(\'checked\', true);" /> <br /><br />';
            $_recurrenceDataHTML .= '<label for="recuryearly_ext"><input type="radio" id="recuryearly_ext" name="recurrence_yearly_type" value="extended" /> ' . $this->Language->Get('rec_the') . '</label> ';
            $_recurrenceDataHTML .= '<select name="recurrence_yearly_extdaystep" class="swiftselect" onfocus="javascript: $(\'#recuryearly_ext\').attr(\'checked\', true);"><option value="first" selected>' . $this->Language->Get('rec_first') . '</option><option value="second">' . $this->Language->Get('rec_second') . '</option><option value="third">' . $this->Language->Get('rec_third') . '</option><option value="fourth">' . $this->Language->Get('rec_fourth') . '</option><option value="fifth">' . $this->Language->Get('rec_fifth') . '</option></select> ';
            $_recurrenceDataHTML .= '<select name="recurrence_yearly_extday" class="swiftselect" onfocus="javascript: $(\'#recuryearly_ext\').attr(\'checked\', true);"><option value="monday" selected>' . $this->Language->Get('rec_monday') . '</option><option value="tuesday">' . $this->Language->Get('rec_tuesday') . '</option><option value="wednesday">' . $this->Language->Get('rec_wednesday') . '</option><option value="thursday">' . $this->Language->Get('rec_thursday') . '</option><option value="friday">' . $this->Language->Get('rec_friday') . '</option><option value="saturday">' . $this->Language->Get('rec_saturday') . '</option><option value="sunday">' . $this->Language->Get('rec_sunday') . '</option></select> ';
            $_recurrenceDataHTML .= '<label for="recuryearly_ext">' . $this->Language->Get('rec_of') . '</label> <select class="swiftselect" name="recurrence_yearly_extmonth" onfocus="javascript: $(\'#recuryearly_ext\').attr(\'checked\', true);">' . $_yearlyMonthlyOptions . '</select> <br /><br />';
            $_recurrenceDataHTML .= '</div>';

            $_recurrenceRadioHTML .= '<div class="tabletitle"><label for="recurrence_none"><input type="radio" onclick="javascript: ToggleRecurrence(\'none\');" id="recurrence_none" name="recurrencetype" value="0" checked /> ' . $this->Language->Get('recurrence_none') . '</label><br />';
            $_recurrenceRadioHTML .= '<label for="recurrence_daily"><input type="radio" onclick="javascript: ToggleRecurrence(\'daily\');" id="recurrence_daily" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_DAILY . '" /> ' . $this->Language->Get('recurrence_daily') . '</label><br />';
            $_recurrenceRadioHTML .= '<label for="recurrence_weekly"><input type="radio" onclick="javascript: ToggleRecurrence(\'weekly\');" id="recurrence_weekly" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_WEEKLY . '" /> ' . $this->Language->Get('recurrence_weekly') . '</label><br />';
            $_recurrenceRadioHTML .= '<label for="recurrence_monthly"><input type="radio" onclick="javascript: ToggleRecurrence(\'monthly\');" id="recurrence_monthly" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_MONTHLY . '" /> ' . $this->Language->Get('recurrence_monthly') . '</label><br />';
            $_recurrenceRadioHTML .= '<label for="recurrence_yearly"><input type="radio" onclick="javascript: ToggleRecurrence(\'yearly\');" id="recurrence_yearly" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_YEARLY . '" /> ' . $this->Language->Get('recurrence_yearly') . '</label><br /></div>';

            $_columnContainer              = array();
            $_columnContainer[0]['value']  = $_recurrenceRadioHTML;
            $_columnContainer[0]['align']  = 'left';
            $_columnContainer[0]['width']  = '150px';
            $_columnContainer[1]['value']  = $_recurrenceDataHTML;
            $_columnContainer[1]['align']  = 'left';
            $_columnContainer[1]['valign'] = 'top';
            $_RecurrenceTab->Row($_columnContainer);

            $_RecurrenceTab->StartContainer('recurrencerangecontainer', false);
            $_RecurrenceTab->Title($this->Language->Get('recurrencerange'), 'icon_doublearrows.gif');

            $_RecurrenceTab->Date('recurrence_start', $this->Language->Get('recur_starts'), '', gmdate(SWIFT_Date::GetCalendarDateFormat(), DATENOW), 0, false, true, '', $this->Language->Get('recur_utc'), true);

            $_recurrenceEndHTML = '';
            $_recurrenceEndHTML .= '<label for="recurrenceend_noeend"><input type="radio" id="recurrenceend_noeend" name="recurrence_endtype" value="' . SWIFT_TicketRecurrence::END_NOEND . '" checked /> ' . $this->Language->Get('rec_noeenddate') . '</label><br /><br />';
            $_recurrenceEndHTML .= '<label for="recurrenceend_endoccur"><input type="radio" id="recurrenceend_endoccur" name="recurrence_endtype" value="' . SWIFT_TicketRecurrence::END_OCCURENCES . '" /> ' . $this->Language->Get('rec_endafter') . '</label> <input type="text" class="swifttextnumeric" size="3" value="10" name="recurrence_endcount" onfocus="javascript: $(\'#recurrenceend_endoccur\').attr(\'checked\', true);" />  <label for="recurrenceend_endoccur">' . $this->Language->Get('rec_occurrences') . '</label><br /><br />';
            $_recurrenceEndHTML .= '<label for="recurrenceend_enddate"><input type="radio" id="recurrenceend_enddate" name="recurrence_endtype" value="' . SWIFT_TicketRecurrence::END_DATE . '" /> ' . $this->Language->Get('rec_endby') . '</label><br /><input type="text" name="recurrence_enddateline" id="recur_enddate" size="12" value="" onfocus="javascript: $(\'#recurrenceend_enddate\').attr(\'checked\', true);" class="swifttext" /><script language="Javascript">QueueFunction(function(){ datePickerDefaults.minDate=new Date("' . gmdate('r') . '");$("#recur_enddate").datepicker(datePickerDefaults);$("#recurrence_start").datepicker(datePickerDefaults); });</script><br />' . $this->Language->Get('recur_utc');

            $_columnClass = '';
            if (in_array('recurrence_enddateline', SWIFT::GetErrorFieldContainer())) {
                $_columnClass = 'errorrow';
            }

            $_columnContainer              = array();
            $_columnContainer[0]['value']  = '<span class="tabletitle">' . $this->Language->Get('recur_ends') . '</span>';
            $_columnContainer[0]['align']  = 'left';
            $_columnContainer[0]['width']  = '150px';
            $_columnContainer[0]['valign'] = 'top';
            $_columnContainer[1]['value']  = $_recurrenceEndHTML;
            $_columnContainer[1]['align']  = 'left';
            $_columnContainer[1]['valign'] = 'top';
            $_RecurrenceTab->Row($_columnContainer, $_columnClass);
            $_RecurrenceTab->EndContainer();
        }

        /*
         * ###############################################
         * END RECURRENCE TAB
         * ###############################################
         */

        // Begin Hook: staff_newticket_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_newticket_tabs')) ? eval($_hookCode) : false;
        // End Hook

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4941 Check Custom Tweaks compatibility with SWIFT
         */
        if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0')
        {
            echo '<script type="text/javascript" src="' . $_SWIFT->Settings->Get('general_producturl') . 
                '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/tinymce.min.js"></script><script>
                tinyMCE.baseURL = "' . $_SWIFT->Settings->Get('general_producturl') .
                '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/";' .
                GetTinyMceCode('#newticketcontents') . '</script>';
        }

        $this->UserInterface->End();

        return true;
    }
}
