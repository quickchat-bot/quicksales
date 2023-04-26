<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_core extends SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct(APP_CORE);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadTables()
    {
        // ======= MAILQUEUEDATA =======
        $this->AddTable('mailqueuedata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "mailqueuedata", "mailqueuedataid I PRIMARY AUTO NOTNULL,
                                                                toemail C(255) DEFAULT '' NOTNULL,
                                                                fromemail C(255) DEFAULT '' NOTNULL,
                                                                fromname C(255) DEFAULT '' NOTNULL,
                                                                subject C(255) DEFAULT '' NOTNULL,
                                                                datatext XL,
                                                                datahtml XL,
                                                                dateline I DEFAULT '0' NOTNULL,
                                                                ishtml I2 DEFAULT '0' NOTNULL"));

        // ======= FILES =======
        $this->AddTable('files', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "files", "fileid I PRIMARY AUTO NOTNULL,
                                                                filename C(255) DEFAULT '' NOTNULL,
                                                                originalfilename C(255) DEFAULT '' NOTNULL,
                                                                filehash C(100) DEFAULT '' NOTNULL,
                                                                dateline I DEFAULT '0' NOTNULL,
                                                                expiry I DEFAULT '0' NOTNULL,
                                                                subdirectory C(255) DEFAULT '' NOTNULL"));
        $this->AddIndex('files', new SWIFT_SetupDatabaseIndex("files1", TABLE_PREFIX . "files", "dateline, expiry"));
        $this->AddIndex('files', new SWIFT_SetupDatabaseIndex("files2", TABLE_PREFIX . "files", "expiry"));


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
     *
     * @param int $_pageIndex The Page Index
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Install($_pageIndex)
    {
        if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli')
        {
            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER DATABASE " . DB_NAME . " CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"));
        }

        parent::Install($_pageIndex);

        if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli')
        {
            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE " . TABLE_PREFIX . "sessions TYPE = HEAP"));
        }

        $this->ExecuteQueue();

        if ($this->GetStatus() == true) {
            // ======= SETTINGS =======
            $this->Settings->InsertKey('core', 'version', SWIFT_VERSION);
            $this->Settings->InsertKey('core', 'product', SWIFT_PRODUCT);
            $this->Settings->InsertKey('core', 'installationhash', BuildHash());
            $this->Settings->InsertKey('cron', 'nextrun', (string)time());

            // ======= REST API =======
            $_SWIFT_RESTManagerObject = new SWIFT_RESTManager();
            $_SWIFT_RESTManagerObject->ReGenerateAuthenticationData(false);

            // Create default cron tasks
            $this->SyncCronTasks();
        }

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
     * Syncrhonize the cron tasks
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SyncCronTasks()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cronTaskList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cron WHERE app = 'core'");
        while ($this->Database->NextRecord()) {
            $_cronTaskList[] = strtolower('/' . $this->Database->Record['app'] . '/' . $this->Database->Record['controller'] . '/' . $this->Database->Record['action']);
        }

        // Cron Minute
        if (!in_array('/core/minute/index', $_cronTaskList)) {
            SWIFT_Cron::Create('defaultcoreminute', 'Core', 'Minute', 'Index', 0, 3, 0, true);
        }

        // Cron Hourly
        if (!in_array('/core/hourly/index', $_cronTaskList)) {
            SWIFT_Cron::Create('defaultcorehourly', 'Core', 'Hourly', 'Index', -1, 0, 0, true);
        }

        // Cron Daily
        if (!in_array('/core/daily/index', $_cronTaskList)) {
            SWIFT_Cron::Create('defaultcoredaily', 'Core', 'Daily', 'Index', 0, 0, -1, true);
        }

        // Cron Weekly
        if (!in_array('/core/weekly/index', $_cronTaskList)) {
            SWIFT_Cron::Create('defaultcoreweekly', 'Core', 'Weekly', 'Index', 0, 0, 7, true);
        }

        // Cron Monthly
        if (!in_array('/core/monthly/index', $_cronTaskList)) {
            SWIFT_Cron::Create('defaultcoremonthly', 'Core', 'Monthly', 'Index', 0, 0, 30, true);
        }

        return true;
    }

    /**
     * Performs upgrade steps for 4.01.161
     *
     * - Removes tables from old search engine app
     * - Removes old search engine cron task
     *
     * @author Ryan Lederman <rml@kayako.com>
     * @return bool
     */
    public function Upgrade_4_01_161()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Drop tables we don't use anymore
        $this->Database->Query("DROP TABLE IF EXISTS " . TABLE_PREFIX . "searchtextngrams, " . TABLE_PREFIX . "searchtextinstances, " . TABLE_PREFIX . "searchtextvariables;");

        // Remove deprecated cron entry
        return $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "cron WHERE name = 'cronsearchengine';");
    }

    /**
     * Performs upgrade steps for 4.01.180
     *
     * - Forces searchindex table to use the MyISAM engine
     *
     * @author Ryan Lederman <rml@kayako.com>
     * @return bool
     */
    public function Upgrade_4_01_180()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "searchindex ENGINE=MyISAM;");
    }

    /**
     * Performs upgrade steps for 4.50.1636
     *
     * @author Parminder Singh
     * @return bool
     */
    public function Upgrade_4_50_1637()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->SyncCronTasks();

        return true;
    }

    /**
     * Upgrade for 4.60
     * Remove section and setting
     *
     * @author Ashish Kataria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_60_0()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * BUG FIX - Amarjeet Kaur
         *
         * SWIFT-3318: Add an upgrade step to reset the customized logos to the new default logos
         *
         * Comments: none
         */
        $_headerImages = $this->Database->QueryFetch("SELECT COUNT(*) AS totalHeaderImages FROM " . TABLE_PREFIX . "settings WHERE section = 'headerimage'");
        if ($_headerImages['totalHeaderImages'] > 0) {
            $this->Settings->DeleteSection('headerimage');
        }

        /**
         * Improvement  - Ashish Kataria
         *
         * SWIFT-3328 Remove 'Display Top Logo Header' setting
         *
         * Comments: Delete Display top header settings
         */
        $this->Settings->DeleteKey('settings', 'g_displaytopheader');

        return true;
    }

    /**
     * Upgrade for 4.70
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     * @throws SWIFT_Exception
     * @return bool
     */
    public function Upgrade_4_70_2223()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Updating onsite port
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "settings SET data='2567' WHERE vkey='ons_xmppport_1'");

        return true;
    }

    /**
     * Upgrade from 4.70.2
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_71_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Remove deprecated onsite entry
        return $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "settingsgroups WHERE name = 'settings_onsite'");
    }

    /**
     * Upgrade from 4.71.0
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_72_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT- 4951 New fulltext indexes are not being added on a new database installation.
         *
         */
        $this->AddFTIndex();

        // Drop tables we don't use anymore
        $this->Database->Query("DROP TABLE IF EXISTS " . TABLE_PREFIX . "onsitesessions;");

        // Remove deprecated onsite entry
        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "settings WHERE vkey = 'onsite';");

        // Replace timezone from the setting
        $this->Database->Query('UPDATE ' . TABLE_PREFIX . "settings SET data = REPLACE(data, 'Brazil/Acre', 'America/Rio_Branco'), data = REPLACE(data, 'Brazil/DeNoronha', 'America/Noronha'), data = REPLACE(data, 'Brazil/East', 'America/Sao_Paulo'), data = REPLACE(data, 'Brazil/West', 'America/Manaus'), data = REPLACE(data, 'Canada/Atlantic', 'America/Halifax'), data = REPLACE(data, 'Canada/Central', 'America/Winnipeg'), data = REPLACE(data, 'Canada/East-Saskatchewan', 'America/Regina'), data = REPLACE(data, 'Canada/Eastern', 'America/Toronto'), data = REPLACE(data, 'Canada/Mountain', 'America/Edmonton'), data = REPLACE(data, 'Canada/Newfoundland', 'America/St_Johns'), data = REPLACE(data, 'Canada/Pacific', 'America/Vancouver'), data = REPLACE(data, 'Canada/Saskatchewan', 'America/Regina'), data = REPLACE(data, 'Canada/Yukon', 'America/Whitehorse'), data = REPLACE(data, 'CET', 'Europe/Berlin'), data = REPLACE(data, 'Chile/Continental', 'America/Santiago'), data = REPLACE(data, 'Chile/EasterIsland', 'Pacific/Easter'), data = REPLACE(data, 'CST6CDT', 'America/Chicago'), data = REPLACE(data, 'Cuba', 'America/Havana'), data = REPLACE(data, 'EET', 'Europe/Bucharest'), data = REPLACE(data, 'Egypt', 'Africa/Cairo'), data = REPLACE(data, 'Eire', 'UTC'), data = REPLACE(data, 'EST5EDT', 'America/New_York'), data = REPLACE(data, 'GB-Eire', 'UTC'), data = REPLACE(data, 'Etc/GMT+10', 'Australia/Brisbane'), data = REPLACE(data, 'Etc/GMT+11', 'Australia/Canberra'), data = REPLACE(data, 'Etc/GMT+12', 'Pacific/Majuro'), data = REPLACE(data, 'Etc/GMT+2', 'Europe/Sofia'), data = REPLACE(data, 'Etc/GMT+3', 'Asia/Baghdad'), data = REPLACE(data, 'Etc/GMT+4', 'Asia/Dubai'), data = REPLACE(data, 'Etc/GMT+5', 'Asia/Karachi'), data = REPLACE(data, 'Etc/GMT+6', 'Asia/Thimphu'), data = REPLACE(data, 'Etc/GMT+7', 'Asia/Bangkok'), data = REPLACE(data, 'Etc/GMT+8', 'Asia/Hong_Kong'), data = REPLACE(data, 'Etc/GMT+9', 'Asia/Tokyo'), data = REPLACE(data, 'Etc/GMT-10', 'Pacific/Honolulu'), data = REPLACE(data, 'Etc/GMT-11', 'Pacific/Niue'), data = REPLACE(data, 'Etc/GMT-12', 'Pacific/Fiji'), data = REPLACE(data, 'Etc/GMT-13', 'Pacific/Auckland'), data = REPLACE(data, 'Etc/GMT-14', 'Pacific/Samoa'), data = REPLACE(data, 'Etc/GMT-2', 'America/Noronha'), data = REPLACE(data, 'Etc/GMT-3', 'America/Belem'), data = REPLACE(data, 'Etc/GMT-4', 'America/Campo_Grande'), data = REPLACE(data, 'Etc/GMT-5', 'America/Eirunepe'), data = REPLACE(data, 'Etc/GMT-6', 'America/Regina'), data = REPLACE(data, 'Etc/GMT-7', 'America/Edmonton'), data = REPLACE(data, 'Etc/GMT-8', 'America/Vancouver'), data = REPLACE(data, 'Etc/GMT-9', 'America/Anchorage'), data = REPLACE(data, 'Etc/Greenwich', 'UTC'), data = REPLACE(data, 'Etc/UCT', 'UTC'), data = REPLACE(data, 'Etc/Universal', 'UTC'), data = REPLACE(data, 'Etc/UTC', 'UTC'), data = REPLACE(data, 'Etc/Zulu', 'UTC'), data = REPLACE(data, 'Factory', 'UTC'), data = REPLACE(data, 'Hongkong', 'Asia/Hong_Kong'), data = REPLACE(data, 'HST', 'Pacific/Honolulu'), data = REPLACE(data, 'Iceland', 'Atlantic/Reykjavik'), data = REPLACE(data, 'Iran', 'Asia/Tehran'), data = REPLACE(data, 'Israel', 'Asia/Jerusalem'), data = REPLACE(data, 'Asia/Ashkhabad', 'Asia/Ashgabat'), data = REPLACE(data, 'Asia/Chongqing', 'Asia/Shanghai'), data = REPLACE(data, 'Asia/Harbin', 'Asia/Shanghai'), data = REPLACE(data, 'Asia/Istanbul', 'Europe/Istanbul'), data = REPLACE(data, 'Asia/Tel_Aviv', 'Asia/Jerusalem'), data = REPLACE(data, 'Asia/Calcutta', 'Asia/Kolkata'), data = REPLACE(data, 'Asia/Katmandu', 'Asia/Kathmandu'), data = REPLACE(data, 'Asia/Macao', 'Asia/Macau'), data = REPLACE(data, 'Asia/Saigon', 'Asia/Ho_Chi_Minh'), data = REPLACE(data, 'Asia/Thimbu', 'Asia/Thimphu'), data = REPLACE(data, 'Europe/Nicosia', 'Asia/Nicosia'), data = REPLACE(data, 'Asia/Ujung_Pandang', 'Asia/Makassar'), data = REPLACE(data, 'Asia/Ulan_Bator', 'Asia/Ulaanbaatar'), data = REPLACE(data, 'Jamaica', 'America/Jamaica'), data = REPLACE(data, 'Japan', 'Asia/Tokyo'), data = REPLACE(data, 'Kwajalein', 'Pacific/Kwajalein'), data = REPLACE(data, 'Libya', 'Africa/Tunis'), data = REPLACE(data, 'MET', 'Europe/Budapest'), data = REPLACE(data, 'Mexico/BajaNorte', 'America/Tijuana'), data = REPLACE(data, 'Mexico/BajaSur', 'America/Mazatlan'), data = REPLACE(data, 'Mexico/General', 'America/Mexico_City'), data = REPLACE(data, 'MST7MDT', 'America/Boise'), data = REPLACE(data, 'MST', 'America/Boise'), data = REPLACE(data, 'Navajo', 'America/Phoenix'), data = REPLACE(data, 'America/Buenos_Aires', 'America/Argentina/Buenos_Aires'), data = REPLACE(data, 'America/Catamarca', 'America/Argentina/Catamarca'), data = REPLACE(data, 'America/Cordoba', 'America/Argentina/Cordoba'), data = REPLACE(data, 'America/Indianapolis', 'America/Indiana/Indianapolis'), data = REPLACE(data, 'America/Jujuy', 'America/Argentina/Jujuy'), data = REPLACE(data, 'America/Louisville', 'America/Kentucky/Louisville'), data = REPLACE(data, 'America/Mendoza', 'America/Argentina/Mendoza'), data = REPLACE(data, 'America/Atka', 'America/Adak'), data = REPLACE(data, 'America/Coral_Harbour', 'America/Atikokan'), data = REPLACE(data, 'America/Ensenada', 'America/Tijuana'), data = REPLACE(data, 'America/Fort_Wayne', 'America/Indiana/Indianapolis'), data = REPLACE(data, 'America/Knox_IN', 'America/Indiana/Knox'), data = REPLACE(data, 'America/Montreal', 'America/Toronto'), data = REPLACE(data, 'America/Porto_Acre', 'America/Rio_Branco'), data = REPLACE(data, 'America/Rosario', 'America/Argentina/Cordoba'), data = REPLACE(data, 'America/Shiprock', 'America/Denver'), data = REPLACE(data, 'America/Virgin', 'America/Port_of_Spain'), data = REPLACE(data, 'NZ-CHAT', 'Pacific/Chatham'), data = REPLACE(data, 'NZ', 'Pacific/Auckland'), data = REPLACE(data, 'Poland', 'Europe/Warsaw'), data = REPLACE(data, 'Portugal', 'Europe/Lisbon'), data = REPLACE(data, 'PRC', 'Asia/Shanghai'), data = REPLACE(data, 'PST8PDT', 'America/Los_Angeles'), data = REPLACE(data, 'ROC', 'Asia/Taipei'), data = REPLACE(data, 'ROK', 'Asia/Seoul'), data = REPLACE(data, 'Singapore', 'Asia/Singapore'), data = REPLACE(data, 'Turkey', 'Europe/Istanbul'), data = REPLACE(data, 'US/Alaska', 'America/Anchorage'), data = REPLACE(data, 'US/Aleutian', 'America/Adak'), data = REPLACE(data, 'US/Arizona', 'America/Phoenix'), data = REPLACE(data, 'US/Central', 'America/Chicago'), data = REPLACE(data, 'US/East-Indiana', 'America/Indiana/Indianapolis'), data = REPLACE(data, 'US/Eastern', 'America/New_York'), data = REPLACE(data, 'US/Hawaii', 'Pacific/Honolulu'), data = REPLACE(data, 'US/Indiana-Starke', 'America/Indiana/Knox'), data = REPLACE(data, 'US/Michigan', 'America/Detroit'), data = REPLACE(data, 'US/Mountain', 'America/Denver'), data = REPLACE(data, 'US/Pacific', 'America/Los_Angeles'), data = REPLACE(data, 'US/Pacific-New', 'America/Los_Angeles'), data = REPLACE(data, 'US/Samoa', 'Pacific/Pago_Pago'), data = REPLACE(data, 'W-SU', 'Europe/Moscow'), data = REPLACE(data, 'WET', 'Europe/Dublin'), data = REPLACE(data, 'GB', 'Europe/London'), data = REPLACE(data, 'EST', 'America/New_York'), data = REPLACE(data, 'Etc/GMT+0', 'UTC'), data = REPLACE(data, 'Etc/GMT-0', 'UTC'), data = REPLACE(data, 'Etc/GMT-1', 'Atlantic/Azores'), data = REPLACE(data, 'Etc/GMT0', 'UTC'), data = REPLACE(data, 'Etc/GMT+1', 'Europe/Berlin'), data = REPLACE(data, 'GMT+0', 'UTC'), data = REPLACE(data, 'GMT-0', 'UTC'), data = REPLACE(data, 'GMT0', 'UTC'), data = REPLACE(data, 'Etc/GMT', 'UTC'), data = REPLACE(data, 'GMT', 'UTC'), data = REPLACE(data, 'Africa/Asmera', 'Africa/Nairobi'), data = REPLACE(data, 'Pacific/Ponape', 'Pacific/Pohnpei'), data = REPLACE(data, 'Pacific/Samoa', 'Pacific/Pago_Pago'), data = REPLACE(data, 'Pacific/Yap', 'Pacific/Chuuk'), data = REPLACE(data, 'Europe/Belfast', 'Europe/London'), data = REPLACE(data, 'Europe/Tiraspol', 'Europe/Chisinau'), data = REPLACE(data, 'Australia/ACT', 'Australia/Sydney'), data = REPLACE(data, 'Australia/Canberra', 'Australia/Sydney'), data = REPLACE(data, 'Australia/LHI', 'Australia/Lord_Howe'), data = REPLACE(data, 'Australia/North', 'Australia/Darwin'), data = REPLACE(data, 'Australia/NSW', 'Australia/Sydney'), data = REPLACE(data, 'Australia/Queensland', 'Australia/Brisbane'), data = REPLACE(data, 'Australia/South', 'Australia/Adelaide'), data = REPLACE(data, 'Australia/Tasmania', 'Australia/Hobart'), data = REPLACE(data, 'Australia/Victoria', 'Australia/Melbourne'), data = REPLACE(data, 'Australia/West', 'Australia/Perth'), data = REPLACE(data, 'Australia/Yancowinna', 'Australia/Broken_Hill'), data = REPLACE(data, 'Africa/Asmara', 'Africa/Nairobi'), data = REPLACE(data, 'Atlantic/Jan_Mayen', 'Europe/Oslo'), data = REPLACE(data, 'Atlantic/Faeroe', 'Atlantic/Faroe'), data = REPLACE(data, 'Antarctica/South_Pole', 'Pacific/Auckland'), data = REPLACE(data, 'Asia/Asia', 'Asia'), data = REPLACE(data, 'America/America', 'America'), data = REPLACE(data, 'Pacific/Pacific', 'Pacific'), data = REPLACE(data, 'America/Los_Angeles-New', 'America/Los_Angeles') where vkey = 'dt_timezonephp'");

        // Prepare query
        $_prepareQuery = "REPLACE(timezonephp, 'Brazil/Acre', 'America/Rio_Branco'), timezonephp = REPLACE(timezonephp, 'Brazil/DeNoronha', 'America/Noronha'), timezonephp = REPLACE(timezonephp, 'Brazil/East', 'America/Sao_Paulo'), timezonephp = REPLACE(timezonephp, 'Brazil/West', 'America/Manaus'), timezonephp = REPLACE(timezonephp, 'Canada/Atlantic', 'America/Halifax'), timezonephp = REPLACE(timezonephp, 'Canada/Central', 'America/Winnipeg'), timezonephp = REPLACE(timezonephp, 'Canada/East-Saskatchewan', 'America/Regina'), timezonephp = REPLACE(timezonephp, 'Canada/Eastern', 'America/Toronto'), timezonephp = REPLACE(timezonephp, 'Canada/Mountain', 'America/Edmonton'), timezonephp = REPLACE(timezonephp, 'Canada/Newfoundland', 'America/St_Johns'), timezonephp = REPLACE(timezonephp, 'Canada/Pacific', 'America/Vancouver'), timezonephp = REPLACE(timezonephp, 'Canada/Saskatchewan', 'America/Regina'), timezonephp = REPLACE(timezonephp, 'Canada/Yukon', 'America/Whitehorse'), timezonephp = REPLACE(timezonephp, 'CET', 'Europe/Berlin'), timezonephp = REPLACE(timezonephp, 'Chile/Continental', 'America/Santiago'), timezonephp = REPLACE(timezonephp, 'Chile/EasterIsland', 'Pacific/Easter'), timezonephp = REPLACE(timezonephp, 'CST6CDT', 'America/Chicago'), timezonephp = REPLACE(timezonephp, 'Cuba', 'America/Havana'), timezonephp = REPLACE(timezonephp, 'EET', 'Europe/Bucharest'), timezonephp = REPLACE(timezonephp, 'Egypt', 'Africa/Cairo'), timezonephp = REPLACE(timezonephp, 'Eire', 'UTC'), timezonephp = REPLACE(timezonephp, 'EST5EDT', 'America/New_York'), timezonephp = REPLACE(timezonephp, 'GB-Eire', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+10', 'Australia/Brisbane'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+11', 'Australia/Canberra'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+12', 'Pacific/Majuro'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+2', 'Europe/Sofia'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+3', 'Asia/Baghdad'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+4', 'Asia/Dubai'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+5', 'Asia/Karachi'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+6', 'Asia/Thimphu'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+7', 'Asia/Bangkok'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+8', 'Asia/Hong_Kong'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+9', 'Asia/Tokyo'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-10', 'Pacific/Honolulu'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-11', 'Pacific/Niue'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-12', 'Pacific/Fiji'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-13', 'Pacific/Auckland'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-14', 'Pacific/Samoa'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-2', 'America/Noronha'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-3', 'America/Belem'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-4', 'America/Campo_Grande'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-5', 'America/Eirunepe'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-6', 'America/Regina'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-7', 'America/Edmonton'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-8', 'America/Vancouver'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-9', 'America/Anchorage'), timezonephp = REPLACE(timezonephp, 'Etc/Greenwich', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/UCT', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/Universal', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/UTC', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/Zulu', 'UTC'), timezonephp = REPLACE(timezonephp, 'Factory', 'UTC'), timezonephp = REPLACE(timezonephp, 'Hongkong', 'Asia/Hong_Kong'), timezonephp = REPLACE(timezonephp, 'HST', 'Pacific/Honolulu'), timezonephp = REPLACE(timezonephp, 'Iceland', 'Atlantic/Reykjavik'), timezonephp = REPLACE(timezonephp, 'Iran', 'Asia/Tehran'), timezonephp = REPLACE(timezonephp, 'Israel', 'Asia/Jerusalem'), timezonephp = REPLACE(timezonephp, 'Asia/Ashkhabad', 'Asia/Ashgabat'), timezonephp = REPLACE(timezonephp, 'Asia/Chongqing', 'Asia/Shanghai'), timezonephp = REPLACE(timezonephp, 'Asia/Harbin', 'Asia/Shanghai'), timezonephp = REPLACE(timezonephp, 'Asia/Istanbul', 'Europe/Istanbul'), timezonephp = REPLACE(timezonephp, 'Asia/Tel_Aviv', 'Asia/Jerusalem'), timezonephp = REPLACE(timezonephp, 'Asia/Calcutta', 'Asia/Kolkata'), timezonephp = REPLACE(timezonephp, 'Asia/Katmandu', 'Asia/Kathmandu'), timezonephp = REPLACE(timezonephp, 'Asia/Macao', 'Asia/Macau'), timezonephp = REPLACE(timezonephp, 'Asia/Saigon', 'Asia/Ho_Chi_Minh'), timezonephp = REPLACE(timezonephp, 'Asia/Thimbu', 'Asia/Thimphu'), timezonephp = REPLACE(timezonephp, 'Europe/Nicosia', 'Asia/Nicosia'), timezonephp = REPLACE(timezonephp, 'Asia/Ujung_Pandang', 'Asia/Makassar'), timezonephp = REPLACE(timezonephp, 'Asia/Ulan_Bator', 'Asia/Ulaanbaatar'), timezonephp = REPLACE(timezonephp, 'Jamaica', 'America/Jamaica'), timezonephp = REPLACE(timezonephp, 'Japan', 'Asia/Tokyo'), timezonephp = REPLACE(timezonephp, 'Kwajalein', 'Pacific/Kwajalein'), timezonephp = REPLACE(timezonephp, 'Libya', 'Africa/Tunis'), timezonephp = REPLACE(timezonephp, 'MET', 'Europe/Budapest'), timezonephp = REPLACE(timezonephp, 'Mexico/BajaNorte', 'America/Tijuana'), timezonephp = REPLACE(timezonephp, 'Mexico/BajaSur', 'America/Mazatlan'), timezonephp = REPLACE(timezonephp, 'Mexico/General', 'America/Mexico_City'), timezonephp = REPLACE(timezonephp, 'MST7MDT', 'America/Boise'), timezonephp = REPLACE(timezonephp, 'MST', 'America/Boise'), timezonephp = REPLACE(timezonephp, 'Navajo', 'America/Phoenix'), timezonephp = REPLACE(timezonephp, 'America/Buenos_Aires', 'America/Argentina/Buenos_Aires'), timezonephp = REPLACE(timezonephp, 'America/Catamarca', 'America/Argentina/Catamarca'), timezonephp = REPLACE(timezonephp, 'America/Cordoba', 'America/Argentina/Cordoba'), timezonephp = REPLACE(timezonephp, 'America/Indianapolis', 'America/Indiana/Indianapolis'), timezonephp = REPLACE(timezonephp, 'America/Jujuy', 'America/Argentina/Jujuy'), timezonephp = REPLACE(timezonephp, 'America/Louisville', 'America/Kentucky/Louisville'), timezonephp = REPLACE(timezonephp, 'America/Mendoza', 'America/Argentina/Mendoza'), timezonephp = REPLACE(timezonephp, 'America/Atka', 'America/Adak'), timezonephp = REPLACE(timezonephp, 'America/Coral_Harbour', 'America/Atikokan'), timezonephp = REPLACE(timezonephp, 'America/Ensenada', 'America/Tijuana'), timezonephp = REPLACE(timezonephp, 'America/Fort_Wayne', 'America/Indiana/Indianapolis'), timezonephp = REPLACE(timezonephp, 'America/Knox_IN', 'America/Indiana/Knox'), timezonephp = REPLACE(timezonephp, 'America/Montreal', 'America/Toronto'), timezonephp = REPLACE(timezonephp, 'America/Porto_Acre', 'America/Rio_Branco'), timezonephp = REPLACE(timezonephp, 'America/Rosario', 'America/Argentina/Cordoba'), timezonephp = REPLACE(timezonephp, 'America/Shiprock', 'America/Denver'), timezonephp = REPLACE(timezonephp, 'America/Virgin', 'America/Port_of_Spain'), timezonephp = REPLACE(timezonephp, 'NZ-CHAT', 'Pacific/Chatham'), timezonephp = REPLACE(timezonephp, 'NZ', 'Pacific/Auckland'), timezonephp = REPLACE(timezonephp, 'Poland', 'Europe/Warsaw'), timezonephp = REPLACE(timezonephp, 'Portugal', 'Europe/Lisbon'), timezonephp = REPLACE(timezonephp, 'PRC', 'Asia/Shanghai'), timezonephp = REPLACE(timezonephp, 'PST8PDT', 'America/Los_Angeles'), timezonephp = REPLACE(timezonephp, 'ROC', 'Asia/Taipei'), timezonephp = REPLACE(timezonephp, 'ROK', 'Asia/Seoul'), timezonephp = REPLACE(timezonephp, 'Singapore', 'Asia/Singapore'), timezonephp = REPLACE(timezonephp, 'Turkey', 'Europe/Istanbul'), timezonephp = REPLACE(timezonephp, 'US/Alaska', 'America/Anchorage'), timezonephp = REPLACE(timezonephp, 'US/Aleutian', 'America/Adak'), timezonephp = REPLACE(timezonephp, 'US/Arizona', 'America/Phoenix'), timezonephp = REPLACE(timezonephp, 'US/Central', 'America/Chicago'), timezonephp = REPLACE(timezonephp, 'US/East-Indiana', 'America/Indiana/Indianapolis'), timezonephp = REPLACE(timezonephp, 'US/Eastern', 'America/New_York'), timezonephp = REPLACE(timezonephp, 'US/Hawaii', 'Pacific/Honolulu'), timezonephp = REPLACE(timezonephp, 'US/Indiana-Starke', 'America/Indiana/Knox'), timezonephp = REPLACE(timezonephp, 'US/Michigan', 'America/Detroit'), timezonephp = REPLACE(timezonephp, 'US/Mountain', 'America/Denver'), timezonephp = REPLACE(timezonephp, 'US/Pacific', 'America/Los_Angeles'), timezonephp = REPLACE(timezonephp, 'US/Pacific-New', 'America/Los_Angeles'), timezonephp = REPLACE(timezonephp, 'US/Samoa', 'Pacific/Pago_Pago'), timezonephp = REPLACE(timezonephp, 'W-SU', 'Europe/Moscow'), timezonephp = REPLACE(timezonephp, 'WET', 'Europe/Dublin'), timezonephp = REPLACE(timezonephp, 'GB', 'Europe/London'), timezonephp = REPLACE(timezonephp, 'EST', 'America/New_York'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+0', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-0', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/GMT-1', 'Atlantic/Azores'), timezonephp = REPLACE(timezonephp, 'Etc/GMT0', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/GMT+1', 'Europe/Berlin'), timezonephp = REPLACE(timezonephp, 'GMT+0', 'UTC'), timezonephp = REPLACE(timezonephp, 'GMT-0', 'UTC'), timezonephp = REPLACE(timezonephp, 'GMT0', 'UTC'), timezonephp = REPLACE(timezonephp, 'Etc/GMT', 'UTC'), timezonephp = REPLACE(timezonephp, 'GMT', 'UTC'), timezonephp = REPLACE(timezonephp, 'Africa/Asmera', 'Africa/Nairobi'), timezonephp = REPLACE(timezonephp, 'Pacific/Ponape', 'Pacific/Pohnpei'), timezonephp = REPLACE(timezonephp, 'Pacific/Samoa', 'Pacific/Pago_Pago'), timezonephp = REPLACE(timezonephp, 'Pacific/Yap', 'Pacific/Chuuk'), timezonephp = REPLACE(timezonephp, 'Europe/Belfast', 'Europe/London'), timezonephp = REPLACE(timezonephp, 'Europe/Tiraspol', 'Europe/Chisinau'), timezonephp = REPLACE(timezonephp, 'Australia/ACT', 'Australia/Sydney'), timezonephp = REPLACE(timezonephp, 'Australia/Canberra', 'Australia/Sydney'), timezonephp = REPLACE(timezonephp, 'Australia/LHI', 'Australia/Lord_Howe'), timezonephp = REPLACE(timezonephp, 'Australia/North', 'Australia/Darwin'), timezonephp = REPLACE(timezonephp, 'Australia/NSW', 'Australia/Sydney'), timezonephp = REPLACE(timezonephp, 'Australia/Queensland', 'Australia/Brisbane'), timezonephp = REPLACE(timezonephp, 'Australia/South', 'Australia/Adelaide'), timezonephp = REPLACE(timezonephp, 'Australia/Tasmania', 'Australia/Hobart'), timezonephp = REPLACE(timezonephp, 'Australia/Victoria', 'Australia/Melbourne'), timezonephp = REPLACE(timezonephp, 'Australia/West', 'Australia/Perth'), timezonephp = REPLACE(timezonephp, 'Australia/Yancowinna', 'Australia/Broken_Hill'), timezonephp = REPLACE(timezonephp, 'Africa/Asmara', 'Africa/Nairobi'), timezonephp = REPLACE(timezonephp, 'Atlantic/Jan_Mayen', 'Europe/Oslo'), timezonephp = REPLACE(timezonephp, 'Atlantic/Faeroe', 'Atlantic/Faroe'), timezonephp = REPLACE(timezonephp, 'Antarctica/South_Pole', 'Pacific/Auckland'), timezonephp = REPLACE(timezonephp, 'Asia/Asia', 'Asia'), timezonephp = REPLACE(timezonephp, 'America/America', 'America'), timezonephp = REPLACE(timezonephp, 'Pacific/Pacific', 'Pacific'), timezonephp = REPLACE(timezonephp, 'America/Los_Angeles-New', 'America/Los_Angeles')";

        // Replace staff/admin timezones
        $this->Database->Query('UPDATE ' . TABLE_PREFIX . 'staff SET timezonephp = ' . $_prepareQuery);

        // Replace user timezones
        $this->Database->Query('UPDATE ' . TABLE_PREFIX . 'users SET SET timezonephp = ' . $_prepareQuery);

        return true;
    }

    /**
     * Upgrade from 4.72.0
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_72_0001()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        // Replace timezone from the setting
        $this->Database->Query('UPDATE ' . TABLE_PREFIX . "settings SET data = REPLACE(data, 'Asia/Asia', 'Asia'), data = REPLACE(data, 'America/America', 'America'), data = REPLACE(data, 'Pacific/Pacific', 'Pacific'), data = REPLACE(data, 'America/Los_Angeles-New', 'America/Los_Angeles') where vkey = 'dt_timezonephp'");

        // Prepare query
        $_prepareQuery = "REPLACE(timezonephp, 'Asia/Asia', 'Asia'), timezonephp = REPLACE(timezonephp, 'America/America', 'America'), timezonephp = REPLACE(timezonephp, 'Pacific/Pacific', 'Pacific'), timezonephp = REPLACE(timezonephp, 'America/Los_Angeles-New', 'America/Los_Angeles')";

        // Replace staff/admin timezones
        $this->Database->Query('UPDATE ' . TABLE_PREFIX . 'staff SET timezonephp = ' . $_prepareQuery);

        // Replace user timezones
        $this->Database->Query('UPDATE ' . TABLE_PREFIX . 'users SET timezonephp = ' . $_prepareQuery);
        return true;
    }
}
