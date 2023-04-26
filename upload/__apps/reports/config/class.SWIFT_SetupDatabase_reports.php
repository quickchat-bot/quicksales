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

use Base\Library\CustomField\SWIFT_CustomFieldManager;

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_reports extends SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    protected $_upgradeReset = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct(APP_REPORTS);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadTables()
    {

        return true;
    }

    /**
     * Get the Page Count for Execution
     *
     * @author Varun Shoor
     * @return int
     */
    public function GetPageCount()
    {
        return self::PAGE_COUNT;
    }

    /**
     * Function that does the heavy execution
     *
     * @author Varun Shoor
     * @param int $_pageIndex The Page Index
     * @return bool "true" on Success, "false" otherwise
     */
    public function Install($_pageIndex)
    {
        parent::Install($_pageIndex);

        // ======= CRON =======
        SWIFT_Cron::Create('reportemailing', 'Reports', 'ReportsMinute', 'EmailReports', '0', '10', '0', true);

        return true;
    }

    /**
     * Uninstalls the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Uninstall()
    {
        parent::Uninstall();

        return true;
    }

    /**
     * Upgrade from 4.01.586
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpgradePre_4_01_586() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_tableList = array();
        $this->Database->Query("SHOW TABLES LIKE '" . TABLE_PREFIX . "report%'");
        while ($this->Database->NextRecord()) {
            foreach ($this->Database->Record as $_tableName) {
                $_tableList[] = $_tableName;
            }
        }

        if (in_array(TABLE_PREFIX . 'reportgroupfields', $_tableList)) {
            foreach ($_tableList as $_tableName) {
                $this->Database->Query("DROP TABLE " . $_tableName);
            }

            $this->_upgradeReset = true;
        }

        return true;
    }

    /**
     * Upgrade from 4.01.586
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpgradePost_4_01_586() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($this->_upgradeReset == true) {
            SWIFT_Loader::LoadLibrary('Setup:ReportSetup', APP_REPORTS, false);
            SWIFT_ReportSetup::Install();
        }

        return true;
    }

    /**
     * Upgrade from 4.30.628
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_30_628()
    {
        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($this->_upgradeReset == false) {
            $this->Database->Query("TRUNCATE TABLE " . TABLE_PREFIX . "reportcategories");
            $this->Database->Query("TRUNCATE TABLE " . TABLE_PREFIX . "reports");
            $this->Database->Query("TRUNCATE TABLE " . TABLE_PREFIX . "reporthistory");
            $this->Database->Query("TRUNCATE TABLE " . TABLE_PREFIX . "reportusagelogs");
            SWIFT_Loader::LoadLibrary('Setup:ReportSetup', APP_REPORTS, false);
            SWIFT_ReportSetup::Install();
        }

        return true;
    }

    /**
     * Upgrade from 4.40.628
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_40_1148()
    {
        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'reports', array('basetablename' => 'ratingresults'), 'UPDATE', "basetablename = 'benchmarkresults'");
        $this->Database->AutoExecute(TABLE_PREFIX . 'reports', array('basetablename' => 'ratings'), 'UPDATE', "basetablename = 'benchmarks'");

        return true;
    }

    /**
     * Upgrade from 4.51.1891
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_52_2153()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // TODO: Replace ReportsHourly in 4.60

        // ======= CRON =======
        SWIFT_Cron::Create('reportemailing', 'Reports', 'ReportsMinute', 'EmailReports', '0', '10', '0', true);

        return true;
    }

    /**
     * BUG FIX - Andriy Lesyuk
     *
     * SWIFT-2506: Redundant data in database for linked select custom fields
     *
     * Comments: We need to fix values already stored in the database
     */
    /**
     * Upgrade from 4.65.0.5460
     *
     * @author Andriy Lesyuk <andriy.lesyuk@opencart.com.vn>
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_66_0_5800()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_CustomFieldManager::FixLinkedSelectValues();

        return true;
    }

    /**
     * BUG FIX - Mansi Wason
     *
     * SWIFT-4350: Uncaught exception thrown on runing Reports Email Schedule cron, if staff who set the schedule report is deleted.
     *
     * Comments: Delete the historic reportschedule whose staff is deleted previously.
     */
    /**
     * Upgrade from 4.70.2
     *
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_71_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffIDList = array();
        $this->Database->Query("SELECT staffid FROM " . TABLE_PREFIX . "reportschedules WHERE staffid NOT IN (SELECT staffid FROM " . TABLE_PREFIX . "staff)");

        while ($this->Database->NextRecord()) {
            $_staffIDList[] = $this->Database->Record['staffid'];
        }

        return SWIFT_ReportSchedule::DeleteOnStaff($_staffIDList);
    }
}
?>
