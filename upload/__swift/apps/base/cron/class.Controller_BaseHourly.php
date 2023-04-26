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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Cron;

use Controller_cron;
use SWIFT;
use SWIFT_Cron;
use SWIFT_CronLog;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * The Hourly Controller
 *
 * @author Varun Shoor
 */
class Controller_BaseHourly extends Controller_cron
{
    /**
     * The Hourly Cleanup
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

        // Cleanup old sessions
        SWIFT_Session::FlushInactive();

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1392: Task log does not get updated after manual excution of cron task from web browser
         *
         * Comments: Add an entry in cron log table
         */
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('cronhourlycleanup');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }

        return true;
    }
}

?>
