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

namespace Knowledgebase;

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT_Console;
use SWIFT_Exception;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use SWIFT_Setup_Exception;
use SWIFT_SetupDatabase;
use SWIFT_SetupDatabaseIndex;
use SWIFT_SetupDatabaseTable;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Widget\SWIFT_Widget;
use Base\Models\Widget\SWIFT_Widget_Exception;

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_knowledgebase extends SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    /**
     * SWIFT_SetupDatabase_knowledgebase constructor.
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(APP_KNOWLEDGEBASE);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception
     */
    public function LoadTables()
    {
        // ======= KBARTICLEDATA =======
        $this->AddTable('kbarticledata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "kbarticledata", "kbarticledataid I PRIMARY AUTO NOTNULL,
                                                                kbarticleid I DEFAULT '0' NOTNULL,
                                                                contents X2,
                                                                contentstext X2"));
        $this->AddIndex('kbarticledata', new SWIFT_SetupDatabaseIndex("kbarticledata1", TABLE_PREFIX . "kbarticledata", "kbarticleid"));

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
     * @throws SWIFT_Widget_Exception
     * @throws SWIFT_Exception
     */
    public function Install($_pageIndex = 1)
    {
        parent::Install($_pageIndex);

        // ======= WIDGET =======
        SWIFT_Widget::Create('PHRASE:widgetknowledgebase', 'knowledgebase', APP_KNOWLEDGEBASE, '/Knowledgebase/List', '{$themepath}icon_widget_knowledgebase.png', '{$themepath}icon_widget_knowledgebase_small.svg', 4, true, true, true, true, SWIFT_Widget::VISIBLE_ALL, false);

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

        // Creating a knowledgebase category
        $_knowledgebaseCategoryID = SWIFT_KnowledgebaseCategory::Create('0', $this->Language->Get('sample_kbcategoryname'), SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, 1, 1, 1, 1, 1);

        // Retrieving the Staff Details based on email address
        $_staffContainer = SWIFT_Staff::RetrieveOnEmail($_POST['email']);

        // Creating two knowledgebase articles
        $_subject = $this->Language->Get('sample_kbarticlesubject1');
        $_seosubject = htmlspecialchars(str_replace(' ', '-', CleanURL($_subject)));

        SWIFT_KnowledgebaseArticle::Create(SWIFT_KnowledgebaseArticle::CREATOR_STAFF, $_staffContainer['staffid'], $_staffContainer['fullname'], $_staffContainer['email'], SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED,
            $_subject, $_seosubject,
            sprintf($this->Language->Get('sample_kbarticlecontent1'), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl']), RemoveTrailingSlash($_POST['producturl'])),
            false, true, array($_knowledgebaseCategoryID));

        $_subject = $this->Language->Get('sample_kbarticlesubject2');
        $_seosubject = htmlspecialchars(str_replace(' ', '-', CleanURL($_subject)));

        SWIFT_KnowledgebaseArticle::Create(SWIFT_KnowledgebaseArticle::CREATOR_STAFF, $_staffContainer['staffid'], $_staffContainer['fullname'], $_staffContainer['email'], SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED,
            $_subject, $_seosubject,
            $this->Language->Get('sample_kbarticlecontent2'),
            false, true, array($_knowledgebaseCategoryID));

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

        SWIFT_Widget::DeleteOnApp(array(APP_KNOWLEDGEBASE));

        return true;
    }

    /**
     * Upgrade from 4.01.204
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
        public function Upgrade_4_01_204() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();

        $this->Database->Query("SELECT kbdata.kbarticledataid, kbdata.kbarticleid, kbdata.contentstext, kbarticles.subject FROM " . TABLE_PREFIX . "kbarticledata AS kbdata LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbdata.kbarticleid = kbarticles.kbarticleid) ORDER BY kbdata.kbarticleid ASC");
        while ($this->Database->NextRecord())
        {
            $_SWIFT_SearchEngineObject->Insert($this->Database->Record['kbarticleid'], $this->Database->Record['kbarticledataid'], SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE, $this->Database->Record['subject'] . " " . $this->Database->Record['contentstext']);
        }

        return true;
    }

    /**
     * Upgrade from 4.92.0
     *
     * @author Werner Garcia
     * @return bool "true" on Success,
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_92_3() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName =  TABLE_PREFIX . 'kbarticles';

        // Add new column
        $_console = new SWIFT_Console();
        $this->Database->Query("ALTER TABLE $_tableName ADD COLUMN seosubject VARCHAR(255), ADD UNIQUE INDEX (seosubject)");
        $_console->Message('Adding seosubject column to ' . $_tableName . '...' . $_console->Green('Done'));

        // Fetch articles
        $this->Database->Query("SELECT kbarticleid, subject FROM $_tableName");
        $_articles = array();
        while ($this->Database->NextRecord()) {
            $_articles[] = $this->Database->Record;
        }
        $_console->Message('Fetching existing articles...' . $_console->Green('Done'));

        // Update articles
        foreach ($_articles as $i => $article) {
            $subject = CleanURL($article['subject']);
            $_seosubject = htmlspecialchars(str_replace(' ', '-', $subject));
            $this->Database->Query(sprintf("UPDATE %s SET seosubject='%s' WHERE kbarticleid=%d", $_tableName, $this->Database->Escape($_seosubject), $article['kbarticleid']));
        }
        $_console->Message('Updating seosubject of existing articles...' . $_console->Green('Done'));

        $_console->Message($_console->Yellow('Updating articles complete.'));

        return true;
    }

    /**
     * Upgrade to 4.93.16
     *
     * @author Werner Garcia
     * @return bool "true" on Success,
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_93_16() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_console = new SWIFT_Console();

        // Fetch categories of type inherit
        $this->Database->Query('SELECT kbcategoryid, title, parentkbcategoryid FROM ' . TABLE_PREFIX . 'kbcategories where categorytype = ' . SWIFT_KnowledgebaseCategory::TYPE_INHERIT . ' order by kbcategoryid');
        $_categories = [];
        while ($this->Database->NextRecord()) {
            $_categories[] = $this->Database->Record;
        }

        // Update links in child categories
        foreach ($_categories as $i => $category) {
            // get parent
            $_parentCategory = new SWIFT_KnowledgebaseCategory(new \SWIFT_DataID($category['parentkbcategoryid']));
            if ($_parentCategory->Get('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT) {
                // if parent is also a child, ignore it
                continue;
            }
            $_console->WriteLine('- Updating inherited settings for subcategories of ' . $_console->Bold($category['title']));
            $_userVisibilityCustom = $_parentCategory->GetProperty('uservisibilitycustom');
            $_staffVisibilityCustom = $_parentCategory->GetProperty('staffvisibilitycustom');
            $_userGroupIdList = $_parentCategory->GetLinkedUserGroupIDList();
            $_staffGroupIDList = $_parentCategory->GetLinkedStaffGroupIDList();
            $_childCategory = new SWIFT_KnowledgebaseCategory(new \SWIFT_DataID($category['kbcategoryid']));

            SWIFT_KnowledgebaseCategory::UpdateChildrenInheritedLinks($_childCategory, $_userVisibilityCustom, $_staffVisibilityCustom, $_userGroupIdList, $_staffGroupIDList);
        }

        return true;
    }
}
