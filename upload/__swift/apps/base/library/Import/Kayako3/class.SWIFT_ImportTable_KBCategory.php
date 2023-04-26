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

namespace Base\Library\Import\QuickSupport3;

use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: KBCategory
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_KBCategory extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'KBCategory');

        if (!$this->TableExists(TABLE_PREFIX . 'kbcategories')) {
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

        $_staffCache = $this->Cache->Get('staffcache');

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbcategories");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "kbcategories ORDER BY kbcategoryid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $_categoryType = SWIFT_KnowledgebaseCategory::TYPE_GLOBAL;
            if ($this->DatabaseImport->Record['categorytype'] == 'private') {
                $_categoryType = SWIFT_KnowledgebaseCategory::TYPE_PRIVATE;
            }

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Category: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbcategories',
                array('parentkbcategoryid' => $this->DatabaseImport->Record['parentcategoryid'], 'staffid' => $_newStaffID, 'title' => $this->DatabaseImport->Record['title'],
                    'dateline' => $this->DatabaseImport->Record['dateline'], 'totalarticles' => (int)($this->DatabaseImport->Record['totalarticles']),
                    'categorytype' => $_categoryType, 'displayorder' => (int)($this->DatabaseImport->Record['displayorder']), 'articlesortorder' => SWIFT_KnowledgebaseCategory::SORT_TITLE,
                    'allowcomments' => '1', 'allowrating' => '1', 'ispublished' => '1', 'uservisibilitycustom' => '0', 'staffvisibilitycustom' => '0'), 'INSERT');
            $_knowledgebaseCategoryID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('kbcategory', $this->DatabaseImport->Record['kbcategoryid'], $_knowledgebaseCategoryID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "kbcategories");
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
