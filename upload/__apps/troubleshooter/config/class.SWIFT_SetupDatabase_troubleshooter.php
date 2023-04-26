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

namespace Troubleshooter;

use SWIFT_Loader;
use SWIFT_SetupDatabaseIndex;
use SWIFT_SetupDatabaseTable;
use Base\Models\Widget\SWIFT_Widget;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;


/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_troubleshooter extends \SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws \SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(APP_TROUBLESHOOTER);

        SWIFT_Loader::LoadModel('Category:TroubleshooterCategory', APP_TROUBLESHOOTER, false);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Setup_Exception
     */
    public function LoadTables()
    {
        // ======= TROUBLESHOOTERDATA =======
        $this->AddTable('troubleshooterdata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "troubleshooterdata", "troubleshooterdataid I PRIMARY AUTO NOTNULL,
                                                                troubleshooterstepid I DEFAULT '0' NOTNULL,
                                                                contents X2"));
        $this->AddIndex('troubleshooterdata', new SWIFT_SetupDatabaseIndex("troubleshooterdata1", TABLE_PREFIX . "troubleshooterdata", "troubleshooterstepid"));

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
     * @throws \SWIFT_Exception
     */
    public function Install($_pageIndex = 1)
    {
        parent::Install($_pageIndex);

        // ======= WIDGET =======
        SWIFT_Widget::Create('PHRASE:widgettroubleshooter', 'troubleshooter', APP_TROUBLESHOOTER, '/Troubleshooter/List', '{$themepath}icon_widget_troubleshooter.svg', '{$themepath}icon_widget_troubleshooter_small.png', 6, true, true, true, true, SWIFT_Widget::VISIBLE_ALL, 0);

        SWIFT_TroubleshooterCategory::Create($this->Language->Get('coregeneral'), '', SWIFT_TroubleshooterCategory::TYPE_GLOBAL, 1);

        return true;
    }

    /**
     * Uninstalls the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function Uninstall()
    {
        parent::Uninstall();

        SWIFT_Widget::DeleteOnApp(array(APP_TROUBLESHOOTER));

        return true;
    }
}
