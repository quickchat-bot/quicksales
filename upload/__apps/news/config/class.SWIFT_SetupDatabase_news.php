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

namespace News;

use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT_Console;
use SWIFT_Cron;
use SWIFT_Exception;
use SWIFT_Loader;
use SWIFT_SetupDatabase;
use SWIFT_SetupDatabaseIndex;
use SWIFT_SetupDatabaseTable;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_news extends SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(APP_NEWS);
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
        // ======= NEWSITEMDATA =======
        $this->AddTable('newsitemdata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX ."newsitemdata", "newsitemdataid I PRIMARY AUTO NOTNULL,
                                                                                newsitemid I DEFAULT '0' NOTNULL,
                                                                                contents X2"));
        $this->AddIndex('newsitemdata', new SWIFT_SetupDatabaseIndex("newsitemdata1", TABLE_PREFIX ."newsitemdata", "newsitemid"));

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
     * @throws SWIFT_Exception
     */
    public function Install($_pageIndex)
    {
        parent::Install($_pageIndex);

        // ======= WIDGET =======
        SWIFT_Widget::Create('PHRASE:widgetnews', 'news', APP_NEWS, '/News/List', '{$themepath}icon_widget_news.png', '{$themepath}icon_widget_news_small.svg', 5, true, true, true, true, SWIFT_Widget::VISIBLE_ALL, false);

        // ======= CRON =======
        SWIFT_Cron::Create('newssync', 'News', 'NewsMinute', 'Sync', 0, 30, 0, true);

        $this->InstallSampleData();

        return true;
    }

    /**
     * @author Saloni Dhall <saloni.dhall@kayako.com>
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function InstallSampleData()
    {
        if (!defined('INSTALL_SAMPLE_DATA') || INSTALL_SAMPLE_DATA === false) {
            return false;
        }

        // Create a news category
        $_newsCategoryID = SWIFT_NewsCategory::Create($this->Language->Get('sample_newscatname'), SWIFT_PUBLIC);

        $_staffContainer = SWIFT_Staff::RetrieveOnEmail($_POST['email']);

        // Create a news article
        SWIFT_NewsItem::Create(SWIFT_NewsItem::TYPE_PRIVATE, SWIFT_NewsItem::STATUS_PUBLISHED, $_staffContainer['fullname'], $_staffContainer['email'], $this->Language->Get('sample_newsarticletitle1'),
                               $this->Language->Get('sample_newsarticletitle1'),
                               sprintf($this->Language->Get('sample_newsarticlecontent1'), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl'])),
                               $_staffContainer['staffid'], 0, true, false, array(), false, array(), false, '', '', 0, array($_newsCategoryID));

        SWIFT_NewsItem::Create(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::STATUS_PUBLISHED, $_staffContainer['fullname'], $_staffContainer['email'], $this->Language->Get('sample_newsarticletitle2'),
                               $this->Language->Get('sample_newsarticletitle2'),
                               sprintf($this->Language->Get('sample_newsarticlecontent2'), RemoveTrailingSlash($_POST['producturl'] . SWIFT_BASENAME), RemoveTrailingSlash($_POST['producturl'] . SWIFT_BASENAME), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl'])),
                               $_staffContainer['staffid'], 0, true, false, array(), false, array(), false, '', '', 0, array($_newsCategoryID));

        return true;
    }

    /**
     * Uninstalls the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Uninstall()
    {
        parent::Uninstall();

        SWIFT_Widget::DeleteOnApp(array(APP_NEWS));

        SWIFT_Cron::DeleteOnName(array('newssync'));

        return true;
    }

    /**
     * Upgrade from 4.92.5
     *
     * @author Werner Garcia
     * @return bool "true" on Success,
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_92_6() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName =  TABLE_PREFIX . 'newsitems';

        // Add new column
        $_console = new SWIFT_Console();
        $_console->WriteLine('Recreating indexes on ' . $_tableName . '...');
        $this->Database->Query("ALTER TABLE $_tableName ADD COLUMN start INT NOT NULL DEFAULT '0' AFTER dateline, 
        DROP INDEX newsitems1,
        ADD KEY newsitems1 (newstype, newsstatus, start, expiry, uservisibilitycustom, newsitemid),
        DROP INDEX newsitems4,
        ADD KEY newsitems4 (newsstatus, start, expiry, staffvisibilitycustom),
        DROP INDEX newsitems5,
        ADD KEY newsitems5 (start, expiry, staffvisibilitycustom)");
        $_console->WriteLine('Index recreation...' . $_console->Green('Done'));

        return true;
    }
}
