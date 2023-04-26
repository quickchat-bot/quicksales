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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

trait View_TicketRecurrenceTrait {
    /**
     * Render the Recurrence
     *
     * @author Parminder Singh
     *
     * @param SWIFT_Ticket $_Ticket
     * @param SWIFT_TicketRecurrence $_TicketRecurrence
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function RenderRecurrence($_Ticket, $_TicketRecurrence)
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->UserInterface->Start(get_short_class($this),'', SWIFT_UserInterface::MODE_INSERT, false);

        $_RecurrenceTab = new SWIFT_UserInterfaceTab($this->UserInterface, $this->Language->Get('tabrecurrence'), '', 1, 'recurrence', false, false, 4, '');

        $_RecurrenceTab->LoadToolbar();
        if ($_SWIFT->Staff->GetPermission('staff_tcanupdaterecurrence') != '0') {
            $_RecurrenceTab->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/Tickets/Ticket/UpdateRecurrence/' . $_Ticket->GetID() . '/' . $_TicketRecurrence->GetTicketRecurrenceID(), SWIFT_UserInterfaceToolbar::LINK_FORM);

            $_RecurrenceTab->Toolbar->AddButton(IIF($_TicketRecurrence->Get('nextrecurrence') == '0', $this->Language->Get('resume'), $this->Language->Get('pause')), IIF($_TicketRecurrence->Get('nextrecurrence') == '0', 'fa-play', 'fa-pause'), '/Tickets/Ticket/PauseOrResumeRecurrence/' . $_Ticket->GetID() . '/' . $_TicketRecurrence->GetTicketRecurrenceID(), SWIFT_UserInterfaceToolbar::LINK_FORM);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcandeleterecurrence') != '0') {
            $_RecurrenceTab->Toolbar->AddButton($this->Language->Get('stop'), 'fa-minus-circle', '/Tickets/Ticket/StopRecurrence/' . $_Ticket->GetID() . '/' . $_TicketRecurrence->GetTicketRecurrenceID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        }

        $_RecurrenceTab->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_RecurrenceTab->SetColumnWidth('150px');

        $_recurrenceRadioHTML = $_recurrenceDataHTML = '';

        $_recurrenceDailyChecked = $_recurDailyDefaultChecked = $_recurDailyExtDaily = $_recurrenceWeeklyChecked = $_recurWeeklyDefaultChecked = $_recurWeeklyMondayChecked = $_recurWeeklyTuesdayChecked = $_recurWeeklyWednesdayChecked = $_recurWeeklyThursdayChecked = $_recurWeeklyFridayChecked = $_recurWeeklySaturdayChecked = $_recurWeeklySundayChecked = $_recurrenceMonthlyChecked = $_recurMonthlyDefaultChecked = $_recurMonthlyExtChecked = $_recurrenceYearlyChecked = $_recurYearlyExtChecked = $_recurrenceendNoeend = $_recurrenceendEndoccurChecked = $_recurrenceendEnddateChecked = '';

        $_recurrenceDailyStepValue = $_recurrenceWeeklyStepValue = $_recurrenceMonthlyDayValue = $_recurrenceMonthlyStepValue = $_recurrenceMonthlyExtdaystepValue = $_recurrenceMonthlyExtdayValue = $_recurrenceMonthlyStepextValue = $_recurrenceYearlyMonthValue = $_recurrenceYearlyMonthdayValue = $_recurrenceYearlyExtdaystepValue = $_recurrenceYearlyExtdayValue = '1';

        $_recurrenceEndCountValue = $_recurEnddateValue = '';

        if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_DAILY) {
            $_recurrenceDailyChecked = ' checked ';

            if ($_TicketRecurrence->Get('intervalstep') != '0') {
                $_recurDailyDefaultChecked = ' checked ';
                $_recurrenceDailyStepValue = (int) ($_TicketRecurrence->Get('intervalstep'));
            } else if ($_TicketRecurrence->Get('daily_everyweekday') != '0') {
                $_recurDailyExtDaily = ' checked ';
            }
        } else if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_WEEKLY) {
            $_recurrenceWeeklyChecked = ' checked ';

            $_recurWeeklyDefaultChecked = ' checked ';

            if ($_TicketRecurrence->Get('intervalstep') != '0') {
                $_recurrenceWeeklyStepValue = (int) ($_TicketRecurrence->Get('intervalstep'));
            }

            if ($_TicketRecurrence->Get('weekly_monday') != '0') {
                $_recurWeeklyMondayChecked = ' checked ';
            }
            if ($_TicketRecurrence->Get('weekly_tuesday') != '0') {
                $_recurWeeklyTuesdayChecked = ' checked ';
            }
            if ($_TicketRecurrence->Get('weekly_wednesday') != '0') {
                $_recurWeeklyWednesdayChecked = ' checked ';
            }
            if ($_TicketRecurrence->Get('weekly_thursday') != '0') {
                $_recurWeeklyThursdayChecked = ' checked ';
            }
            if ($_TicketRecurrence->Get('weekly_friday') != '0') {
                $_recurWeeklyFridayChecked = ' checked ';
            }
            if ($_TicketRecurrence->Get('weekly_saturday') != '0') {
                $_recurWeeklySaturdayChecked = ' checked ';
            }
            if ($_TicketRecurrence->Get('weekly_sunday') != '0') {
                $_recurWeeklySundayChecked = ' checked ';
            }
        } else if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_MONTHLY) {
            $_recurrenceMonthlyChecked = ' checked ';
            if ($_TicketRecurrence->Get('monthly_type') == SWIFT_TicketRecurrence::MONTHLY_DEFAULT) {
                $_recurMonthlyDefaultChecked = ' checked ';
                $_recurrenceMonthlyDayValue  = (int) ($_TicketRecurrence->Get('monthly_day'));
                $_recurrenceMonthlyStepValue = (int) ($_TicketRecurrence->Get('intervalstep'));
            } else if ($_TicketRecurrence->Get('monthly_type') == SWIFT_TicketRecurrence::MONTHLY_EXTENDED) {
                $_recurMonthlyExtChecked           = ' checked ';
                $_recurrenceMonthlyExtdaystepValue = $_TicketRecurrence->Get('monthly_extdaystep');
                $_recurrenceMonthlyExtdayValue     = $_TicketRecurrence->Get('monthly_extday');
                $_recurrenceMonthlyStepextValue    = $_TicketRecurrence->Get('intervalstep');
            }
        } else if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_YEARLY) {
            $_recurrenceYearlyChecked = ' checked ';
            if ($_TicketRecurrence->Get('yearly_type') == SWIFT_TicketRecurrence::YEARLY_DEFAULT) {
                $_recurrenceYearlyMonthValue    = $_TicketRecurrence->Get('yearly_month');
                $_recurrenceYearlyMonthdayValue = $_TicketRecurrence->Get('yearly_monthday');
            } else if ($_TicketRecurrence->Get('yearly_type') == SWIFT_TicketRecurrence::YEARLY_EXTENDED) {
                $_recurYearlyExtChecked           = ' checked ';
                $_recurrenceYearlyExtdaystepValue = $_TicketRecurrence->Get('yearly_extdaystep');
                $_recurrenceYearlyExtdayValue     = $_TicketRecurrence->Get('yearly_extday');
                $_recurrenceYearlyMonthValue      = $_TicketRecurrence->Get('yearly_extmonth');
            }
        }

        // Range of Recurrence Script HTML
        $_recurrenceStartValue = DATENOW;
        if ($_TicketRecurrence->Get('startdateline') != '0') {
            $_recurrenceStartValue = $_TicketRecurrence->Get('startdateline');
        }

        if ($_TicketRecurrence->Get('endtype') == SWIFT_TicketRecurrence::END_NOEND) {
            $_recurrenceendNoeend = ' checked ';
        } else if ($_TicketRecurrence->Get('endtype') == SWIFT_TicketRecurrence::END_OCCURENCES) {
            $_recurrenceendEndoccurChecked = ' checked ';
            $_recurrenceEndCountValue      = $_TicketRecurrence->Get('endcount');
        } else if ($_TicketRecurrence->Get('endtype') == SWIFT_TicketRecurrence::END_DATE) {
            $_recurrenceendEnddateChecked = ' checked ';
            $_recurEnddateValue           = gmdate(SWIFT_Date::GetCalendarDateFormat(), $_TicketRecurrence->Get('enddateline'));
        }

        $_recurrenceDataHTML .= '<div id="recurrencecontainer_daily" style="display: none;"><label for="recurdaily_default"><input type="radio" id="recurdaily_default" name="recurrence_daily_type" value="default" ' . $_recurDailyDefaultChecked . ' /> ' . $this->Language->Get('rec_every') . '</label> <input type="text" class="swifttextnumeric" size="3" value="' . $_recurrenceDailyStepValue . '" name="recurrence_daily_step" id="recurrence_daily_step" onfocus="javascript: $(\'#recurdaily_default\').attr(\'checked\', true);" /> <label for="recurdaily_default">' . $this->Language->Get('rec_days') . '</label><br /><br />';
        $_recurrenceDataHTML .= '<label for="recurdaily_ext"><input type="radio" id="recurdaily_ext" name="recurrence_daily_type" value="extended" ' . $_recurDailyExtDaily . ' /> ' . $this->Language->Get('rec_everyweekday') . '</label>';
        $_recurrenceDataHTML .= '</div>';

        $_recurrenceDataHTML .= '<div id="recurrencecontainer_weekly" style="display: none;"><label for="recurweekly_default"><input type="radio" id="recurweekly_default" name="recurrence_weekly_type" value="default" ' . $_recurWeeklyDefaultChecked . ' /> ' . $this->Language->Get('rec_every') . '</label> <input type="text" class="swifttextnumeric" size="3" value="' . $_recurrenceWeeklyStepValue . '" name="recurrence_weekly_step" id="recurrence_weekly_step" /> <label for="recurweekly_default">' . $this->Language->Get('rec_weeks') . '</label><br /><br />';
        $_recurrenceDataHTML .= '<label for="recurweekly_monday"><input type="checkbox" id="recurweekly_monday" name="recurrence_weekly_ismonday" value="1" ' . $_recurWeeklyMondayChecked . ' /> ' . $this->Language->Get('rec_monday') . '</label>&nbsp;&nbsp;&nbsp;';
        $_recurrenceDataHTML .= '<label for="recurweekly_tuesday"><input type="checkbox" id="recurweekly_tuesday" name="recurrence_weekly_istuesday" value="1" ' . $_recurWeeklyTuesdayChecked . ' /> ' . $this->Language->Get('rec_tuesday') . '</label>&nbsp;&nbsp;&nbsp;';
        $_recurrenceDataHTML .= '<label for="recurweekly_wednesday"><input type="checkbox" id="recurweekly_wednesday" name="recurrence_weekly_iswednesday" value="1" ' . $_recurWeeklyWednesdayChecked . ' /> ' . $this->Language->Get('rec_wednesday') . '</label>&nbsp;&nbsp;&nbsp;';
        $_recurrenceDataHTML .= '<label for="recurweekly_thursday"><input type="checkbox" id="recurweekly_thursday" name="recurrence_weekly_isthursday" value="1" ' . $_recurWeeklyThursdayChecked . ' /> ' . $this->Language->Get('rec_thursday') . '</label>&nbsp;&nbsp;&nbsp;';
        $_recurrenceDataHTML .= '<label for="recurweekly_friday"><input type="checkbox" id="recurweekly_friday" name="recurrence_weekly_isfriday" value="1" ' . $_recurWeeklyFridayChecked . ' /> ' . $this->Language->Get('rec_friday') . '</label>&nbsp;&nbsp;&nbsp;';
        $_recurrenceDataHTML .= '<label for="recurweekly_saturday"><input type="checkbox" id="recurweekly_saturday" name="recurrence_weekly_issaturday" value="1" ' . $_recurWeeklySaturdayChecked . ' /> ' . $this->Language->Get('rec_saturday') . '</label>&nbsp;&nbsp;&nbsp;';
        $_recurrenceDataHTML .= '<label for="recurweekly_sunday"><input type="checkbox" id="recurweekly_sunday" name="recurrence_weekly_issunday" value="1" ' . $_recurWeeklySundayChecked . ' /> ' . $this->Language->Get('rec_sunday') . '</label>&nbsp;&nbsp;&nbsp;';
        $_recurrenceDataHTML .= '</div>';

        $_recurrenceDataHTML .= '<div id="recurrencecontainer_monthly" style="display: none;"><label for="recurmonthly_default"><input type="radio" id="recurmonthly_default" name="recurrence_monthly_type" value="default" ' . $_recurMonthlyDefaultChecked . ' /> ' . $this->Language->Get('rec_day') . '</label> <input type="text" class="swifttextnumeric" size="3" value="' . $_recurrenceMonthlyDayValue . '" name="recurrence_monthly_day" id="recurrence_monthly_day" onfocus="javascript: $(\'#recurmonthly_default\').attr(\'checked\', true);" /> <label for="recurmonthly_default">' . $this->Language->Get('rec_ofevery') . '</label> <input type="text" class="swifttextnumeric" size="3" value="' . $_recurrenceMonthlyStepValue . '" name="recurrence_monthly_step" id="recurrence_monthly_step" onfocus="javascript: $(\'#recurmonthly_default\').attr(\'checked\', true);" />  <label for="recurmonthly_default">' . $this->Language->Get('rec_months') . '</label> <br /><br />';
        $_recurrenceDataHTML .= '<label for="recurmonthly_ext"><input type="radio" id="recurmonthly_ext" name="recurrence_monthly_type" value="extended" ' . $_recurMonthlyExtChecked . ' /> ' . $this->Language->Get('rec_the') . '</label> ';
        $_recurrenceDataHTML .= '<select name="recurrence_monthly_extdaystep" id="recurrence_monthly_extdaystep" class="swiftselect" onfocus="javascript: $(\'#recurmonthly_ext\').attr(\'checked\', true);"><option value="first" ' . IIF($_recurrenceMonthlyExtdaystepValue == "first", "selected") . ' >' . $this->Language->Get('rec_first') . '</option><option value="second"  ' . IIF($_recurrenceMonthlyExtdaystepValue == "second", "selected") . '>' . $this->Language->Get('rec_second') . '</option><option value="third" ' . IIF($_recurrenceMonthlyExtdaystepValue == "third", "selected") . '>' . $this->Language->Get('rec_third') . '</option><option value="fourth"  ' . IIF($_recurrenceMonthlyExtdaystepValue == "fourth", "selected") . '>' . $this->Language->Get('rec_fourth') . '</option><option value="fifth" ' . IIF($_recurrenceMonthlyExtdaystepValue == "fifth", "selected") . '>' . $this->Language->Get('rec_fifth') . '</option></select> ';
        $_recurrenceDataHTML .= '<select name="recurrence_monthly_extday" id="recurrence_monthly_extday" class="swiftselect" onfocus="javascript: $(\'#recurmonthly_ext\').attr(\'checked\', true);"><option value="monday" ' . IIF($_recurrenceMonthlyExtdayValue == "monday", "selected") . ' >' . $this->Language->Get('rec_monday') . '</option><option value="tuesday" ' . IIF($_recurrenceMonthlyExtdayValue == "tuesday", "selected") . ' >' . $this->Language->Get('rec_tuesday') . '</option><option value="wednesday" ' . IIF($_recurrenceMonthlyExtdayValue == "wednesday", "selected") . ' >' . $this->Language->Get('rec_wednesday') . '</option><option value="thursday" ' . IIF($_recurrenceMonthlyExtdayValue == "thursday", "selected") . ' >' . $this->Language->Get('rec_thursday') . '</option><option value="friday" ' . IIF($_recurrenceMonthlyExtdayValue == "friday", "selected") . ' >' . $this->Language->Get('rec_friday') . '</option><option value="saturday" ' . IIF($_recurrenceMonthlyExtdayValue == "saturday", "selected") . ' >' . $this->Language->Get('rec_saturday') . '</option><option value="sunday" ' . IIF($_recurrenceMonthlyExtdayValue == "sunday", "selected") . ' >' . $this->Language->Get('rec_sunday') . '</option></select> ';
        $_recurrenceDataHTML .= '<label for="recurmonthly_ext">' . $this->Language->Get('rec_ofevery') . '</label> <input type="text" class="swifttextnumeric" size="3" value="' . $_recurrenceMonthlyStepextValue . '" name="recurrence_monthly_stepext" id="recurrence_monthly_stepext" onfocus="javascript: $(\'#recurmonthly_ext\').attr(\'checked\', true);" />  <label for="recurmonthly_ext">' . $this->Language->Get('rec_months') . '</label> <br /><br />';
        $_recurrenceDataHTML .= '</div>';

        $_yearlyMonthlyOptions = '<option value="1"' . IIF($_recurrenceYearlyMonthValue == '1', ' selected') . '>' . $this->Language->Get('cal_january') . '</option><option value="2"' . IIF($_recurrenceYearlyMonthValue == '2', ' selected') . '>' . $this->Language->Get('cal_february') . '</option><option value="3"' . IIF($_recurrenceYearlyMonthValue == '3', ' selected') . '>' . $this->Language->Get('cal_march') . '</option><option value="4"' . IIF($_recurrenceYearlyMonthValue == '4', ' selected') . '>' . $this->Language->Get('cal_april') . '</option><option value="5"' . IIF($_recurrenceYearlyMonthValue == '5', ' selected') . '>' . $this->Language->Get('cal_may') . '</option><option value="6"' . IIF($_recurrenceYearlyMonthValue == '6', ' selected') . '>' . $this->Language->Get('cal_june') . '</option><option value="7"' . IIF($_recurrenceYearlyMonthValue == '7', ' selected') . '>' . $this->Language->Get('cal_july') . '</option><option value="8"' . IIF($_recurrenceYearlyMonthValue == '8', ' selected') . '>' . $this->Language->Get('cal_august') . '</option><option value="9"' . IIF($_recurrenceYearlyMonthValue == '9', ' selected') . '>' . $this->Language->Get('cal_september') . '</option><option value="10"' . IIF($_recurrenceYearlyMonthValue == '10', ' selected') . '>' . $this->Language->Get('cal_october') . '</option><option value="11"' . IIF($_recurrenceYearlyMonthValue == '11', ' selected') . '>' . $this->Language->Get('cal_november') . '</option><option value="12"' . IIF($_recurrenceYearlyMonthValue == '12', ' selected') . '>' . $this->Language->Get('cal_december') . '</option>';

        $_recurrenceDataHTML .= '<div id="recurrencecontainer_yearly" style="display: none;"><label for="recuryearly_default"><input type="radio" id="recuryearly_default" name="recurrence_yearly_type" value="default" checked /> ' . $this->Language->Get('rec_every') . '</label> <select class="swiftselect" name="recurrence_yearly_month" id="recurrence_yearly_month" onfocus="javascript: $(\'#recuryearly_default\').attr(\'checked\', true);">' . $_yearlyMonthlyOptions . '</select> <input type="text" class="swifttextnumeric" size="3" value="' . $_recurrenceYearlyMonthdayValue . '" name="recurrence_yearly_monthday" id="recurrence_yearly_monthday" onfocus="javascript: $(\'#recuryearly_default\').attr(\'checked\', true);" /> <br /><br />';
        $_recurrenceDataHTML .= '<label for="recuryearly_ext"><input type="radio" id="recuryearly_ext" name="recurrence_yearly_type" value="extended" ' . $_recurYearlyExtChecked . ' /> ' . $this->Language->Get('rec_the') . '</label> ';
        $_recurrenceDataHTML .= '<select name="recurrence_yearly_extdaystep" id="recurrence_yearly_extdaystep" class="swiftselect" onfocus="javascript: $(\'#recuryearly_ext\').attr(\'checked\', true);"><option value="first" ' . IIF($_recurrenceYearlyExtdaystepValue == "first", "selected") . '>' . $this->Language->Get('rec_first') . '</option><option value="second" ' . IIF($_recurrenceYearlyExtdaystepValue == "second", "selected") . '>' . $this->Language->Get('rec_second') . '</option><option value="third" ' . IIF($_recurrenceYearlyExtdaystepValue == "third", "selected") . '>' . $this->Language->Get('rec_third') . '</option><option value="fourth" ' . IIF($_recurrenceYearlyExtdaystepValue == "first", "fourth") . '>' . $this->Language->Get('rec_fourth') . '</option><option value="fifth" ' . IIF($_recurrenceYearlyExtdaystepValue == "fifth", "selected") . '>' . $this->Language->Get('rec_fifth') . '</option></select> ';
        $_recurrenceDataHTML .= '<select name="recurrence_yearly_extday" id="recurrence_yearly_extday" class="swiftselect" onfocus="javascript: $(\'#recuryearly_ext\').attr(\'checked\', true);"><option value="monday" ' . IIF($_recurrenceYearlyExtdayValue == "monday", "selected") . '>' . $this->Language->Get('rec_monday') . '</option><option value="tuesday" ' . IIF($_recurrenceYearlyExtdayValue == "tuesday", "selected") . '>' . $this->Language->Get('rec_tuesday') . '</option><option value="wednesday" ' . IIF($_recurrenceYearlyExtdayValue == "wednesday", "selected") . '>' . $this->Language->Get('rec_wednesday') . '</option><option value="thursday" ' . IIF($_recurrenceYearlyExtdayValue == "thursday", "selected") . '>' . $this->Language->Get('rec_thursday') . '</option><option value="friday" ' . IIF($_recurrenceYearlyExtdayValue == "friday", "selected") . '>' . $this->Language->Get('rec_friday') . '</option><option value="saturday" ' . IIF($_recurrenceYearlyExtdayValue == "saturday", "selected") . '>' . $this->Language->Get('rec_saturday') . '</option><option value="sunday" ' . IIF($_recurrenceYearlyExtdayValue == "sunday", "selected") . '>' . $this->Language->Get('rec_sunday') . '</option></select> ';
        $_recurrenceDataHTML .= '<label for="recuryearly_ext">' . $this->Language->Get('rec_of') . '</label> <select class="swiftselect" name="recurrence_yearly_extmonth" id="recurrence_yearly_extmonth" onfocus="javascript: $(\'#recuryearly_ext\').attr(\'checked\', true);">' . $_yearlyMonthlyOptions . '</select> <br /><br />';
        $_recurrenceDataHTML .= '</div>';

        $_recurrenceRadioHTML .= '<div class="tabletitle"><label for="recurrence_daily"><input type="radio" onclick="javascript: ToggleRecurrence(\'daily\');" id="recurrence_daily" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_DAILY . '" ' . $_recurrenceDailyChecked . ' /> ' . $this->Language->Get('recurrence_daily') . '</label><br />';
        $_recurrenceRadioHTML .= '<label for="recurrence_weekly"><input type="radio" onclick="javascript: ToggleRecurrence(\'weekly\');" id="recurrence_weekly" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_WEEKLY . '" ' . $_recurrenceWeeklyChecked . ' /> ' . $this->Language->Get('recurrence_weekly') . '</label><br />';
        $_recurrenceRadioHTML .= '<label for="recurrence_monthly"><input type="radio" onclick="javascript: ToggleRecurrence(\'monthly\');" id="recurrence_monthly" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_MONTHLY . '" ' . $_recurrenceMonthlyChecked . ' /> ' . $this->Language->Get('recurrence_monthly') . '</label><br />';
        $_recurrenceRadioHTML .= '<label for="recurrence_yearly"><input type="radio" onclick="javascript: ToggleRecurrence(\'yearly\');" id="recurrence_yearly" name="recurrencetype" value="' . SWIFT_TicketRecurrence::INTERVAL_YEARLY . '" ' . $_recurrenceYearlyChecked . ' /> ' . $this->Language->Get('recurrence_yearly') . '</label><br /></div>';

        $_columnContainer              = array();
        $_columnContainer[0]['value']  = $_recurrenceRadioHTML;
        $_columnContainer[0]['align']  = 'left';
        $_columnContainer[0]['width']  = '150px';
        $_columnContainer[1]['value']  = $_recurrenceDataHTML;
        $_columnContainer[1]['align']  = 'left';
        $_columnContainer[1]['valign'] = 'top';

        $_RecurrenceTab->Row($_columnContainer);
        $_RecurrenceTab->EndContainer();

        // Range of recurrence
        $_RecurrenceTab->StartContainer('recurrencerangecontainer', false);
        $_RecurrenceTab->Title($this->Language->Get('recurrencerange'), 'icon_doublearrows.gif');

        if ($_SWIFT->Cache->Get('error_recurrence_start')) {
            SWIFT::ErrorField('recurrence_start');
            $_SWIFT->Cache->Remove('error_recurrence_start');
        }

        $_RecurrenceTab->Date('recurrence_start', $this->Language->Get('recur_starts'), '', gmdate(SWIFT_Date::GetCalendarDateFormat(), $_recurrenceStartValue), 0, false, true, '', $this->Language->Get('recur_utc'), true);

        $_recurrenceEndHTML = '';
        $_recurrenceEndHTML .= '<label for="recurrenceend_noeend"><input type="radio" id="recurrenceend_noeend" name="recurrence_endtype" value="' . SWIFT_TicketRecurrence::END_NOEND . '" ' . $_recurrenceendNoeend . ' /> ' . $this->Language->Get('rec_noeenddate') . '</label><br /><br />';
        $_recurrenceEndHTML .= '<label for="recurrenceend_endoccur"><input type="radio" id="recurrenceend_endoccur" name="recurrence_endtype" value="' . SWIFT_TicketRecurrence::END_OCCURENCES . '" ' . $_recurrenceendEndoccurChecked . '/> ' . $this->Language->Get('rec_endafter') . '</label> <input type="text" class="swifttextnumeric" size="3" name="recurrence_endcount" onfocus="javascript: $(\'#recurrenceend_endoccur\').attr(\'checked\', true);" value = "' . $_recurrenceEndCountValue . '" />  <label for="recurrenceend_endoccur">' . $this->Language->Get('rec_occurrences') . '</label><br /><br />';
        $_recurrenceEndHTML .= '<label for="recurrenceend_enddate"><input type="radio" id="recurrenceend_enddate" name="recurrence_endtype" value="' . SWIFT_TicketRecurrence::END_DATE . '" ' . $_recurrenceendEnddateChecked . ' /> ' . $this->Language->Get('rec_endby') . '</label><br /><input type="text" name="recurrence_enddateline" id="recur_enddate" size="12" onfocus="javascript: $(\'#recurrenceend_enddate\').attr(\'checked\', true);" class="swifttext" value = "' . $_recurEnddateValue . '" /><script language="Javascript">QueueFunction(function(){ datePickerDefaults.minDate=new Date("' . gmdate('r') . '");$("#recur_enddate").datepicker(datePickerDefaults); $("#recurrence_start").datepicker(datePickerDefaults); });</script><br />' . $this->Language->Get('recur_utc');

        if ($_SWIFT->Cache->Get('error_recurrence_enddateline')) {
            SWIFT::ErrorField('recurrence_enddateline');
            $_SWIFT->Cache->Remove('error_recurrence_enddateline');
        }

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

        // Render the tickets which are created from recurrence
        $_RecurrenceTab->StartContainer('recurrenceticketscontainer');
        $_RecurrenceTab->Title('Recurrence History', 'icon_doublearrows.gif');

        $_departmentCache     = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache   = $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');
        $_ticketTypeCache     = $_SWIFT->Cache->Get('tickettypecache');

        $_recurrenceHistoryContainer = SWIFT_Ticket::RetrieveRecurrenceHistory($_Ticket->GetID());
        // If no record found then try to search from its parent id
        if (!count($_recurrenceHistoryContainer) && $_Ticket->Get('recurrencefromticketid') != '0') {
            $_recurrenceParentTicketID   = $_Ticket->Get('recurrencefromticketid');
            $_recurrenceHistoryContainer = SWIFT_Ticket::RetrieveRecurrenceHistory($_recurrenceParentTicketID);

            $_recurrenceHistoryContainer[$_recurrenceParentTicketID] = SWIFT_Ticket::GetObjectOnID($_recurrenceParentTicketID);
        }

        $_renderHTML = '<table width="100%" cellspacing="0" cellpadding="2" border="0">';
        $_renderHTML .= '<tbody><tr class=""><td valign="middle" align="left" class="gridtabletitlerow" width="16">&nbsp;</td><td valign="middle" align="left" class="gridtabletitlerow" width="120">' . $this->Language->Get('history_ticketid') . '</td><td valign="middle" align="left" class="gridtabletitlerow">' . $this->Language->Get('history_subject') . '</td><td valign="middle" align="center" class="gridtabletitlerow" width="160">' . $this->Language->Get('history_date') . '</td><td valign="middle" align="center" class="gridtabletitlerow" width="120">' . $this->Language->Get('history_department') . '</td><td valign="middle" align="center" class="gridtabletitlerow" width="90">' . $this->Language->Get('history_type') . '</td><td valign="middle" align="center" class="gridtabletitlerow" width="100">' . $this->Language->Get('history_status') . '</td><td valign="middle" align="center" class="gridtabletitlerow" width="90">' . $this->Language->Get('history_priority') . '</td></tr>';

        $_historyCount = 0;
        foreach ($_recurrenceHistoryContainer as $_ticketID => $_Ticket_History) {

            if ($_Ticket->GetID() == $_ticketID) {
                continue;
            }

            $_historyCount++;

            $_subjectSuffix = $_ticketURLPrefix = $_ticketURLSuffix = $_departmentHTML = $_typeHTML = $_statusHTML = $_priorityHTML = $_statusStyle = $_priorityStyle = '';

            $_ticketIcon = 'fa-ticket';
            if (!$_Ticket_History->CanAccess($_SWIFT->Staff)) {
                $_ticketIcon = 'fa-lock';
            } else {
                $_ticketURL       = SWIFT::Get('basename') . '/Tickets/Ticket/View/' . (int) ($_Ticket_History->GetID());
                $_ticketURLPrefix = '<a href="javascript: void(0);" onclick="javascript: loadViewportData(\'' . $_ticketURL . '\');">';
                $_ticketURLSuffix = '</a>';
                $_subjectSuffix .= '&nbsp;<a href="' . $_ticketURL . '" target="_blank"><img src="' . SWIFT::Get('themepathimages') .
                    'icon_newwindow_gray.png' . '" align="absmiddle" border="0" /></a>&nbsp;';
            }
            $_ticketIconHTML = '<i class="fa ' . $_ticketIcon . '" aria-hidden="true"></i>';

            // Department
            if (isset($_departmentCache[$_Ticket_History->Get('departmentid')])) {
                $_departmentHTML = text_to_html_entities(StripName($_departmentCache[$_Ticket_History->Get('departmentid')]['title'], 15));
            } else {
                $_departmentHTML = $_SWIFT->Language->Get('na');
            }

            // Ticket Status
            if (isset($_ticketStatusCache[$_Ticket_History->Get('ticketstatusid')])) {
                $_ticketStatusContainer = $_ticketStatusCache[$_Ticket_History->Get('ticketstatusid')];
                // _displayIconImage implementation removed
                $_displayIconImage      = '';

                $_statusHTML = '<span class="ticketStatusIndicator" style="background-color: ' . $_ticketStatusContainer['statusbgcolor'] . ';color:#ffffff;">' . htmlspecialchars(StripName($_ticketStatusContainer['title'], 15)) . '</span>';
            } else {
                $_statusHTML = $_SWIFT->Language->Get('na');
            }

            // Ticket Priority
            if (isset($_ticketPriorityCache[$_Ticket_History->Get('priorityid')])) {
                $_ticketPriorityContainer = $_ticketPriorityCache[$_Ticket_History->Get('priorityid')];
                // _displayIconImage implementation removed
                $_displayIconImage        = '';
                if($_ticketPriorityContainer['bgcolorcode']!=''){
                    $_priorityHTML  = '<span class="ticketPriorityIndicator" style="background-color: ' . $_ticketPriorityContainer['bgcolorcode'] . ';color: ' . $_ticketPriorityContainer['frcolorcode'] . ';">' . htmlspecialchars(StripName($_ticketPriorityContainer['title'], 15)) . '</span>';
                }
                else{
                    $_priorityHTML  = '<span style="color: ' . $_ticketPriorityContainer['frcolorcode'] . ';">' . htmlspecialchars(StripName($_ticketPriorityContainer['title'], 15)) . '</span>';
                }
                $_priorityStyle = '';

            } else {
                $_priorityHTML = $_SWIFT->Language->Get('na');
            }

            // Ticket Type
            if (isset($_ticketTypeCache[$_Ticket_History->Get('tickettypeid')])) {
                $_ticketTypeContainer = $_ticketTypeCache[$_Ticket_History->Get('tickettypeid')];
                $_displayIconImage    = '';
                if (!empty($_ticketTypeContainer['displayicon'])) {
                    $_displayIconImage = '<img src="' . ProcessDisplayIcon($_ticketTypeContainer['displayicon']) .
                        '" align="absmiddle" border="0" /> ';
                }

                $_typeHTML = $_displayIconImage . htmlspecialchars(StripName($_ticketTypeContainer['title'], 15));
            } else {
                $_typeHTML = $_SWIFT->Language->Get('na');
            }

            $_renderHTML .= '<tr class="tablerow1_tr"><td valign="middle" align="center" class="tablerow1">' . $_ticketIconHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_ticketURLPrefix . $_Ticket_History->GetTicketDisplayID() . $_ticketURLSuffix . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_ticketURLPrefix . htmlspecialchars($this->Emoji->decode($_Ticket_History->Get('subject'))) . $_ticketURLSuffix . '<span style="float: right;">' . $_subjectSuffix . '</span></td>';
            $_renderHTML .= '<td valign="middle" align="center" class="tablerow1">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_Ticket_History->Get('dateline')) . '</td>';
            $_renderHTML .= '<td valign="middle" align="center" class="tablerow1">' . $_departmentHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="center" class="tablerow1">' . $_typeHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="center" class="tablerow1">' . $_statusHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="center" class="tablerow1" style="' . $_priorityStyle . '">' . $_priorityHTML . '</td>';
            $_renderHTML .= '</tr>';
        }

        if ($_historyCount == 0) {
            $_renderHTML .= '<tr class="tablerow1_tr"><td valign="middle" align="left" class="tablerow1" colspan="8">' . $this->Language->Get('noinfoinview') . '</td></tr>';
        }
        $_renderHTML .= '</tbody></table>';

        $_columnContainer              = array();
        $_columnContainer[0]['value']  = $_renderHTML;
        $_columnContainer[0]['align']  = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_RecurrenceTab->Row($_columnContainer);
        $_RecurrenceTab->EndContainer();

        $_renderHTML = $_RecurrenceTab->GetDisplayHTML(true);

        $_recurrenceScriptHTML = '';
        if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_DAILY) {
            $_recurrenceScriptHTML = 'ToggleRecurrence(\'daily\');';
        } else if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_WEEKLY) {
            $_recurrenceScriptHTML = 'ToggleRecurrence(\'weekly\');';
        } else if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_MONTHLY) {
            $_recurrenceScriptHTML = 'ToggleRecurrence(\'monthly\');';
        } else if ($_TicketRecurrence->Get('intervaltype') == SWIFT_TicketRecurrence::INTERVAL_YEARLY) {
            $_recurrenceScriptHTML = 'ToggleRecurrence(\'yearly\');';
        }

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= $_recurrenceScriptHTML;
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }
}
