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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

use Base\Library\CallHomeData\SWIFT_CallHomeData;

/**
 * The Cron Daily Controller
 *
 * @author Varun Shoor
 */
class Controller_Daily extends Controller_cron
{
    /**
     * The Daily Cleanup
     *
     * @author Varun Shoor
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Cleanup()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Cleanup File Logs
        SWIFT_Log::CleanUp();
        /**
         * Improvement - Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4899 - Call Home report version 2 - SWIFT to send ping to backend
         */
        //Call home Data
        SWIFT_Loader::LoadLibrary('CallHomeData:CallHomeData', APP_BASE);

        $_CallHome = new SWIFT_CallHomeData();
        $_CallHome->CallHomeData();

        return true;
    }

    /**
     * The Default Daily Method
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

        SWIFT_CronManager::RunModelCron(SWIFT_CronManager::TYPE_DAILY);

        $this->Cleanup();

        return true;
    }
}
?>
