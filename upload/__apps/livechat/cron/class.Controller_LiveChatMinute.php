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

namespace LiveChat\Cron;

use Controller_cron;
use SWIFT;
use LiveChat\Library\Chat\SWIFT_ChatManager;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use SWIFT_Cron;
use SWIFT_CronLog;
use SWIFT_Exception;
use LiveChat\Models\Visitor\SWIFT_Visitor;

/**
 * The Minute Controller
 *
 * @author Varun Shoor
 */
class Controller_LiveChatMinute extends Controller_cron
{
    /**
     * The Indexing Executor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Indexing()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Cleanup
        SWIFT_ChatQueue::Cleanup();

        $_SWIFT_ChatManagerObject = new SWIFT_ChatManager();
        $_SWIFT_ChatManagerObject->IndexPending();

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1661 [ERROR] /usr/sbin/mysqld: The table 'swvisitorfootprints' is full
         *
         * Comments: Added the flush call to periodically clean up the visitor footprints
         */

        // Cleanup visitor sessions
        SWIFT_Visitor::Flush();

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1392: Task log does not get updated after manual execution of cron task from web browser
         *
         * Comments: Add an entry in cron log table
         */
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('livechat');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }

        return true;
    }
}

