<?php

namespace Base\Library\Import\Cerberus5;

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: KBArticleLink
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Kb_article_to_category extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Kb_article_to_category');

        if (!$this->TableExists('kb_article_to_category')) {
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
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbarticlelinks");
//        }

        $_count = 0;

        $_oldKBArticleIDList = $_kbArticleLinkContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM kb_article_to_category ORDER BY kb_top_category_id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_kbArticleLinkContainer[] = $this->DatabaseImport->Record;

            $_oldKBArticleIDList[] = $this->DatabaseImport->Record['kb_article_id'];
        }

        $_newKBArticleIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('kbarticle', $_oldKBArticleIDList);

        foreach ($_kbArticleLinkContainer as $_kbArticleLinkID => $_kbArticleLink) {
            $_count++;

            if (!isset($_newKBArticleIDList[$_kbArticleLink['kb_article_id']])) {
                $this->GetImportManager()->AddToLog('Failed to Import Knowledgebase Article Link due to non existent KB Article: ' . htmlspecialchars($_kbArticleLink['kb_article_id']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_newKBCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('kbcategory', $_kbArticleLink['kb_category_id']);
            if (!$_newKBCategoryID) {
                $this->GetImportManager()->AddToLog('Failed to Import Knowledgebase Article Link due to non existent KB Category: ' . htmlspecialchars($_kbArticleLink['kb_category_id']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_kbArticleID = (int)($_newKBArticleIDList[$_kbArticleLink['kb_article_id']]);

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Article Link: ' . $_kbArticleID . ' <=> ' . $_newKBCategoryID, SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticlelinks',
                array('kbarticleid' => $_kbArticleID, 'linktype' => SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY, 'linktypeid' => $_newKBCategoryID), 'INSERT');
            $_kbArticleLinkID = $this->Database->InsertID();
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM kb_article_to_category");
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
