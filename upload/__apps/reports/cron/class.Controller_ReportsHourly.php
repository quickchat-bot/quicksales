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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

// TODO: Remove in 4.60

/**
 * The Hourly Controller
 *
 * @author Andriy Lesyuk
 */
class Controller_ReportsHourly extends Controller_cron
{

    /**
     * Execute Scheduled Reports
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EmailReports()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_ReportSchedule::ExecutePendingSchedules();

        // Add an entry in cron log table
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('reportemailing');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }

        return true;
    }

}
?>