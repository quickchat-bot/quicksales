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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Import\Kayako3;

use Base\Models\Widget\SWIFT_Widget;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: DownloadCategoryRebuild
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_DownloadCategoryRebuild extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'DownloadCategoryRebuild');

        if (!$this->TableExists(TABLE_PREFIX . 'downloadcategories')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadLibrary('Category:KnowledgebaseCategoryManager', APP_KNOWLEDGEBASE);
        SWIFT_Loader::LoadModel('Category:KnowledgebaseCategory', APP_KNOWLEDGEBASE);

        SWIFT_Loader::LoadLibrary('Article:KnowledgebaseArticleManager', APP_KNOWLEDGEBASE);
        SWIFT_Loader::LoadModel('Article:KnowledgebaseArticle', APP_KNOWLEDGEBASE);
        SWIFT_Loader::LoadModel('Article:KnowledgebaseArticleLink', APP_KNOWLEDGEBASE);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newDownloadsParentCategoryID = 0;

        if ($this->GetOffset() == 0) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'kbcategories',
                array('parentkbcategoryid' => '0', 'staffid' => '0', 'title' => 'Downloads',
                    'dateline' => DATENOW, 'totalarticles' => '0',
                    'categorytype' => SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, 'displayorder' => '1', 'articlesortorder' => SWIFT_KnowledgebaseCategory::SORT_TITLE,
                    'allowcomments' => '1', 'allowrating' => '1', 'ispublished' => '1', 'uservisibilitycustom' => '0', 'staffvisibilitycustom' => '0'), 'INSERT');
            $_newDownloadsParentCategoryID = $this->Database->InsertID();
            $this->ImportManager->GetImportRegistry()->UpdateKey('downloads', 'downloadparentcategoryid', $_newDownloadsParentCategoryID);

            SWIFT_Widget::DeleteOnTitle(array('PHRASE:widgetdownloads'));
            SWIFT_Widget::Create('PHRASE:widgetdownloads', 'downloads', APP_KNOWLEDGEBASE, '/Knowledgebase/List/Index/' . $_newDownloadsParentCategoryID, '{$themepath}icon_widget_downloads.png', '{$themepath}icon_widget_downloads_small.png', 5, true, true, true, true, SWIFT_Widget::VISIBLE_ALL, false);
        } else {
            $_newDownloadsParentCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('downloads', 'downloadparentcategoryid');
        }

        $_count = 0;
        $_kbCategoryContainer = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "kbcategories WHERE isimporteddownloadcategory = '1' ORDER BY kbcategoryid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->Database->NextRecord()) {
            $_count++;

            $_kbCategoryContainer[$this->Database->Record['kbcategoryid']] = $this->Database->Record;
        }

        foreach ($_kbCategoryContainer as $_kbCategoryID => $_kbCategory) {
            $_newParentKBCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('downloadcategory', $_kbCategory['parentkbcategoryid']);
            if ($_kbCategory['parentkbcategoryid'] == '0') {
                $_newParentKBCategoryID = $_newDownloadsParentCategoryID;
            }

            $this->GetImportManager()->AddToLog('Importing Downloads Category Relationship: ' . htmlspecialchars($_kbCategory['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbcategories',
                array('parentkbcategoryid' => $_newParentKBCategoryID), 'UPDATE', "kbcategoryid = '" . $_kbCategoryID . "'");
        }

        SWIFT_KnowledgebaseCategory::RebuildCache(true);

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "downloadcategories");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 500;
    }
}

?>
