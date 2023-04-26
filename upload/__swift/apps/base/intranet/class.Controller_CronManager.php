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

namespace Base\Intranet;

use Controller_intranet;
use SWIFT_CronManager;
use SWIFT_Exception;

/**
 * Cron Controller
 *
 * @author Varun Shoor
 */
class Controller_CronManager extends Controller_intranet
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
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQAEAIAAAP/a2gAAACH5BAAAAAAALAAAAAABAAQAAAIChFEAOw==');

        return true;
    }
}

?>
