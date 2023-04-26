<?php

namespace Base\Library\Import\Cerberus5;

use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: KBCategory
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Kb_category extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Kb_category');

        if (!$this->TableExists('kb_category')) {
            $this->SetIsClassLoaded(false);
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
     * @author Nicolás Ibarra Sabogal
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbcategories");
//        }

        $_count = 0;
        $_controlParent = false;

        $this->DatabaseImport->QueryLimit("SELECT * FROM kb_category ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = 1;

            $_categoryType = SWIFT_KnowledgebaseCategory::TYPE_PUBLIC;

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Category: ' . htmlspecialchars($this->DatabaseImport->Record['name']), SWIFT_ImportManager::LOG_SUCCESS);

            $_newKBCategoryparentID = 0;
            if ($_controlParent == true) {
                $_newKBCategoryparentID = $this->ImportManager->GetImportRegistry()->GetKey('kbcategory', $this->DatabaseImport->Record['parent_id']);
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbcategories',
                array('parentkbcategoryid' => $_newKBCategoryparentID, 'staffid' => $_newStaffID, 'title' => $this->DatabaseImport->Record['name'],
                    'totalarticles' => '2', 'categorytype' => $_categoryType, 'displayorder' => $_count,
                    'articlesortorder' => SWIFT_KnowledgebaseCategory::SORT_TITLE,
                    'allowcomments' => '1', 'allowrating' => '1', 'ispublished' => '1', 'uservisibilitycustom' => '0', 'staffvisibilitycustom' => '0'), 'INSERT');

            $_knowledgebaseCategoryID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('kbcategory', $this->DatabaseImport->Record['id'], $_knowledgebaseCategoryID);

            $_controlParent = true;
        }

        SWIFT_KnowledgebaseCategory::RebuildCache(true);

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM kb_category");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Nicolás Ibarra Sabogal
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
