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

namespace News\Cron;

use News\Library\Sync\SWIFT_NewsSyncManager;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_App;
use SWIFT_Cron;
use SWIFT_CronLog;
use SWIFT_Exception;
use Controller_cron;

/**
 * The News Minute Controller
 *
 * @property SWIFT_NewsSyncManager $NewsSyncManager
 * @author Varun Shoor
 */
class Controller_NewsMinute extends Controller_cron
{
    /**
     * Synchronize the News Items
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Sync()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Sync:NewsSyncManager');

        if (trim($this->Settings->Get('nw_globalsync')) != '')
        {
            $this->NewsSyncManager->Sync($this->Settings->Get('nw_globalsync'), SWIFT_NewsItem::TYPE_GLOBAL);
        }

        if (trim($this->Settings->Get('nw_publicsync')) != '')
        {
            $this->NewsSyncManager->Sync($this->Settings->Get('nw_publicsync'), SWIFT_NewsItem::TYPE_PUBLIC);
        }

        if (trim($this->Settings->Get('nw_privatesync')) != '')
        {
            $this->NewsSyncManager->Sync($this->Settings->Get('nw_privatesync'), SWIFT_NewsItem::TYPE_PRIVATE);
        }

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1392: Task log does not get updated after manual excution of cron task from web browser
         *
         * Comments: Add an entry in cron log table
         */
        if (!SWIFT::Get('iscron')) {
            $_SWIFT_CronObject = SWIFT_Cron::Retrieve('newssync');
            SWIFT_CronLog::Create($_SWIFT_CronObject, '');
        }
        return true;
    }
}
