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

namespace Base\Cron;

use Base\Models\SearchStore\SWIFT_SearchStoreData;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffLoginLog;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserLoginLog;
use Controller_cron;
use SWIFT;
use SWIFT_Cron;
use SWIFT_CronLog;
use SWIFT_ErrorLog;
use SWIFT_Exception;

/**
 * The Cron Daily Controller
 *
 * @author Varun Shoor
 */
class Controller_BaseDaily extends Controller_cron
{
    /**
     * The Daily Cleanup
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Cleanup()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Cleanup unverified users
        SWIFT_User::CleanUp();

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1615 "Log Clearing Time (In Days)" setting does not clear logs other than parser logs
         *
         * Comments: None
         */

        SWIFT_CronLog::CleanUp();

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1392: Task log does not get updated after manual excution of cron task from web browser
         *
         * Comments: Add an entry in cron log table
         */
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('crondailycleanup');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }

        // Cleanup Staff Activity Logs
        SWIFT_StaffActivityLog::CleanUp();

        // Cleanup Staff Login Logs
        SWIFT_StaffLoginLog::CleanUp();

        // Cleanup User Login Logs
        SWIFT_UserLoginLog::CleanUp();

        // Cleanup Error Logs
        SWIFT_ErrorLog::CleanUp();

        // Cleanup Search Store Data
        SWIFT_SearchStoreData::Cleanup();

        return true;
    }
}

?>
