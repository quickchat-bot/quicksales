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
use SWIFT_DataID;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;

trait Controller_TicketRecurrenceTrait
{
    /**
     * Render the Recurrence for this Ticket
     *
     * @author Parminder Singh
     *
     * @param int $_ticketID
     * @param int $_ticketRecurrenceID
     *
     * @return bool
     * @throws \SWIFT_Exception
     */
    public function Recurrence($_ticketID, $_ticketRecurrenceID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_Ticket = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));

        // Check permission
        if (!$_Ticket->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanviewrecurrence') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_TicketRecurrence = new SWIFT_TicketRecurrence(new SWIFT_DataID($_ticketRecurrenceID));

        $this->View->RenderRecurrence($_Ticket, $_TicketRecurrence);

        return true;
    }

    /**
     * Pause/Resume ticket recurrence
     *
     * @author Parminder Singh
     *
     * @param int $_ticketID
     * @param int $_ticketRecurrenceID
     *
     * @return bool
     * @throws \SWIFT_Exception
     */
    public function PauseOrResumeRecurrence($_ticketID, $_ticketRecurrenceID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_Ticket = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));

        // Check permission
        if (!$_Ticket->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupdaterecurrence') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_TicketRecurrence = new SWIFT_TicketRecurrence(new SWIFT_DataID($_ticketRecurrenceID));

        if ($_TicketRecurrence->Get('nextrecurrence') == '0') { //    If it is paused then resume it
            $_TicketRecurrence->UpdatePool('nextrecurrence', ($_TicketRecurrence->GetNextRecurrence()));
        } else { //    Otherwise pause it
            $_TicketRecurrence->UpdatePool('nextrecurrence', '0');
        }

        $this->Load->Method('View', $_ticketID);

        return true;
    }

    /**
     * Stop the ticket recurrence
     *
     * @author Parminder Singh
     *
     * @param int $_ticketID
     * @param int $_ticketRecurrenceID
     *
     * @return bool
     * @throws \SWIFT_Exception
     */
    public function StopRecurrence($_ticketID, $_ticketRecurrenceID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_Ticket = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));

        // Check permission
        if (!$_Ticket->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcandeleterecurrence') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_TicketRecurrence = new SWIFT_TicketRecurrence(new SWIFT_DataID($_ticketRecurrenceID));

        $_TicketRecurrence->Delete();

        $this->Load->Method('View', $_ticketID);

        return true;
    }

    /**
     * Delete the ticket recurrence
     *
     * @author Parminder Singh
     *
     * @param int $_ticketID
     * @param int $_ticketRecurrenceID
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function UpdateRecurrence($_ticketID, $_ticketRecurrenceID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_Ticket = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));

        // Check permission
        if (!$_Ticket->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupdaterecurrence') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_TicketRecurrence = new SWIFT_TicketRecurrence(new SWIFT_DataID($_ticketRecurrenceID));

        if (isset($_POST['recurrencetype']) && (int)$_POST['recurrencetype'] > 0) {
            $_tz = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $recurrenceStartDateline = GetCalendarDateline($_POST['recurrence_start']);
            $recurrenceEndDateline = GetCalendarDateline($_POST['recurrence_enddateline']);
            $today = strtotime('today GMT');
            date_default_timezone_set($_tz);

            if (isset($_POST['recurrence_start']) &&
                !empty($recurrenceStartDateline) &&
                $recurrenceStartDateline < $today &&
                (int)$_TicketRecurrence->GetProperty('startdateline') !== (int)$recurrenceStartDateline) {

                $_SWIFT->Cache->Update('error_recurrence_start', [true]);
                $this->UserInterface->Error($this->Language->Get('titlefieldinvalid'), $this->Language->Get('msgpastdate') . ': ' . $this->Language->Get('recur_starts'));
                $this->Load->Method('View', $_ticketID);

                return false;
            }

            if (isset($_POST['recurrence_enddateline']) &&
                !empty($recurrenceEndDateline) &&
                $recurrenceEndDateline < $today &&
                    (int)$_TicketRecurrence->GetProperty('enddateline') !== (int)$recurrenceEndDateline) {
                $_SWIFT->Cache->Update('error_recurrence_enddateline', [true]);
                $this->UserInterface->Error($this->Language->Get('titlefieldinvalid'), $this->Language->Get('msgpastdate') . ': ' . $this->Language->Get('recur_ends'));
                $this->Load->Method('View', $_ticketID);

                return false;
            }

            // Reset recurrence fields before updating
            $_TicketRecurrence->Reset();

            // Daily
            if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_DAILY) {
                $_intervalStep      = (int) ($_POST['recurrence_daily_step']);
                $_dailyEveryWeekday = false;
                if ((empty($_intervalStep) || $_intervalStep < 0) && $_POST['recurrence_daily_type'] === 'default') {
                    $_intervalStep = 1;
                }

                if ($_POST['recurrence_daily_type'] === 'extended') {
                    $_intervalStep      = 0;
                    $_dailyEveryWeekday = true;
                }

                $_TicketRecurrence->UpdateDaily($_intervalStep, $_dailyEveryWeekday);
                // Weekly
            } else if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_WEEKLY) {
                $_intervalStep = (int) ($_POST['recurrence_weekly_step']);
                if ((empty($_intervalStep) || $_intervalStep < 0)) {
                    $_intervalStep = 1;
                }

                $_isMonday = $_isTuesday = $_isWednesday = $_isThursday = $_isFriday = $_isSaturday = $_isSunday = false;
                if (isset($_POST['recurrence_weekly_ismonday'])) {
                    $_isMonday = true;
                }

                if (isset($_POST['recurrence_weekly_istuesday'])) {
                    $_isTuesday = true;
                }

                if (isset($_POST['recurrence_weekly_iswednesday'])) {
                    $_isWednesday = true;
                }

                if (isset($_POST['recurrence_weekly_isthursday'])) {
                    $_isThursday = true;
                }

                if (isset($_POST['recurrence_weekly_isfriday'])) {
                    $_isFriday = true;
                }

                if (isset($_POST['recurrence_weekly_issaturday'])) {
                    $_isSaturday = true;
                }

                if (isset($_POST['recurrence_weekly_issunday'])) {
                    $_isSunday = true;
                }

                $_TicketRecurrence->UpdateWeekly($_intervalStep, $_isMonday, $_isTuesday, $_isWednesday, $_isThursday, $_isFriday, $_isSaturday, $_isSunday);
                // Monthly
            } else if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_MONTHLY) {
                $_recurrenceMonthlyType  = SWIFT_TicketRecurrence::MONTHLY_DEFAULT;
                $_monthlyExtendedDay     = 'monday';
                $_monthlyExtendedDayStep = 'first';
                $_monthlyDay             = date('d');

                if ($_POST['recurrence_monthly_type'] === 'extended') {
                    $_recurrenceMonthlyType  = SWIFT_TicketRecurrence::MONTHLY_EXTENDED;
                    $_monthlyExtendedDay     = $_POST['recurrence_monthly_extday'];
                    $_monthlyExtendedDayStep = $_POST['recurrence_monthly_extdaystep'];

                    $_intervalStep = (int) ($_POST['recurrence_monthly_stepext']);
                } else {
                    $_intervalStep = (int) ($_POST['recurrence_monthly_step']);
                    if ((int) ($_POST['recurrence_monthly_day']) != '0' && (int) ($_POST['recurrence_monthly_day']) > 0) {
                        $_monthlyDay = (int) ($_POST['recurrence_monthly_day']);
                    }
                }

                if ((empty($_intervalStep) || $_intervalStep < 0)) {
                    $_intervalStep = 1;
                }

                $_TicketRecurrence->UpdateMonthly($_intervalStep, $_recurrenceMonthlyType, $_monthlyDay, $_monthlyExtendedDay, $_monthlyExtendedDayStep);
                // Yearly
            } else if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_YEARLY) {
                $_yearlyType            = SWIFT_TicketRecurrence::YEARLY_DEFAULT;
                $_yearlyMonthDay        = gmdate('d', DATENOW);
                $_yearlyMonth           = gmdate('n', DATENOW);
                $_yearlyExtendedDay     = 'first';
                $_yearlyExtendedDayStep = 'monday';
                $_yearlyExtendedMonth   = $_yearlyMonth;

                if ($_POST['recurrence_yearly_type'] === 'extended') {
                    $_yearlyType            = SWIFT_TicketRecurrence::YEARLY_EXTENDED;
                    $_yearlyExtendedDay     = $_POST['recurrence_yearly_extday'];
                    $_yearlyExtendedDayStep = $_POST['recurrence_yearly_extdaystep'];
                    $_yearlyExtendedMonth   = $_POST['recurrence_yearly_extmonth'];
                } else {
                    $_yearlyMonthDay = (int) ($_POST['recurrence_yearly_monthday']);
                    if (empty($_yearlyMonthDay) || $_yearlyMonthDay <= 0) {
                        $_yearlyMonthDay = gmdate('d', DATENOW);
                    }

                    $_yearlyMonth = (int) ($_POST['recurrence_yearly_month']);
                }

                $_TicketRecurrence->UpdateYearly($_yearlyType, $_yearlyMonth, $_yearlyMonthDay, $_yearlyExtendedDay, $_yearlyExtendedDayStep, $_yearlyExtendedMonth);
            }

            $_endType = SWIFT_TicketRecurrence::END_NOEND;
            if ($_POST['recurrence_endtype'] == SWIFT_TicketRecurrence::END_DATE && $recurrenceEndDateline != false) {
                $_endType = SWIFT_TicketRecurrence::END_DATE;
            } else if (($_POST['recurrence_endtype'] == SWIFT_TicketRecurrence::END_OCCURENCES && (int) ($_POST['recurrence_endcount']) > 0)) {
                $_endType = SWIFT_TicketRecurrence::END_OCCURENCES;
            }

            $_TicketRecurrence->UpdateRecurrenceRange($recurrenceStartDateline, $_endType, $recurrenceEndDateline, (int) ($_POST['recurrence_endcount']));

            // Update next recurrence
            $_TicketRecurrence->UpdatePool('nextrecurrence', ($_TicketRecurrence->GetNextRecurrence()));
        }

        $this->Load->Method('View', $_ticketID);

        return true;
    }
}
