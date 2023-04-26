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
use SWIFT_CronManager;
use SWIFT_Exception;

/**
 * Cron Controller
 *
 * @author Varun Shoor
 */
class Controller_CronManager extends Controller_cron
{
    /**
     * The Cron Executor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Execute()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        SWIFT_CronManager::RunPendingTasks();
        @header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQAEAIAAAP/a2gAAACH5BAAAAAAALAAAAAABAAQAAAIChFEAOw==');

        return true;
    }
}

?>
