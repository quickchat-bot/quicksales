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

namespace Base\Admin;

use Controller_admin;
use SWIFT_CronManager;
use SWIFT_Exception;

/**
 * Cron Controller
 *
 * @author Varun Shoor
 */
class Controller_CronManager extends Controller_admin
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

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
        }

        SWIFT_CronManager::RunPendingTasks();
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQAEAIAAAP/a2gAAACH5BAAAAAAALAAAAAABAAQAAAIChFEAOw==');

        return true;
    }
}

?>
