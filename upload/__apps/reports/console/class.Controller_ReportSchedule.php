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
 * @copyright    Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Report Schedule Controller
 *
 * @author Andriy Lesyuk and Ravinder Singh
 */
class Controller_ReportSchedule extends Controller_console
{

    /**
     * Execute Scheduled Reports
     *
     * @author Andriy Lesyuk and Ravinder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EmailReports()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_ReportSchedule::ExecutePendingSchedules();

        return true;
    }

}
?>