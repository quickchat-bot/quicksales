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

/**
 * The Cron Weekly Controller
 *
 * @author Varun Shoor
 */
class Controller_Weekly extends Controller_cron
{
    /**
     * The Default Weekly Method
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        SWIFT_CronManager::RunModelCron(SWIFT_CronManager::TYPE_WEEKLY);

        return true;
    }
}
?>
