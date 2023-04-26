<?php

namespace Base\Library\Import\Cerberus5;

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: KBArticle
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Kb_article extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Kb_article');

        if (!$this->TableExists('kb_article')) {
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

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbarticles");
//        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM kb_article ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = 1;

            $_staffName = $this->Language->Get('na');
            $_staffEmail = '';
            if (isset($_staffCache[$_newStaffID])) {
                $_staffName = $_staffCache[$_newStaffID]['fullname'];
                $_staffEmail = $_staffCache[$_newStaffID]['email'];
            }

            $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED;

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Article: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticles',
                array('creator' => SWIFT_KnowledgebaseArticle::CREATOR_STAFF, 'creatorid' => $_newStaffID, 'author' => $_staffName, 'email' => $_staffEmail,
                    'subject' => $this->DatabaseImport->Record['title'], 'views' => $this->DatabaseImport->Record['views'], 'isfeatured' => '0', 'allowcomments' => '1', 'totalcomments' => '0',
                    'hasattachments' => '0', 'dateline' => DATENOW, 'articlestatus' => $_articleStatus,), 'INSERT');
            $_knowledgebaseArticleID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('kbarticle', $this->DatabaseImport->Record['id'], $_knowledgebaseArticleID, true);

//            ARTICLE DATA
            $_kbArticleID = $_knowledgebaseArticleID;

            $_tempContents = $this->DatabaseImport->Record['content'];

            $_contentsText = strip_tags_attributes(str_replace('<br />', ' ', $_tempContents));

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Article Data for Message: ' . $_kbArticleID, SWIFT_ImportManager::LOG_SUCCESS);

            $_finalContents = $_tempContents;
            if (!stristr($_finalContents, '<br>') && !stristr($_finalContents, '<br />') && !stristr($_finalContents, '<p>')) {
                $_finalContents = nl2br($_finalContents);
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticledata',
                array('kbarticleid' => $_kbArticleID, 'contents' => $_finalContents, 'contentstext' => $_contentsText), 'INSERT');

            $_kbArticleDataID = $this->Database->InsertID();
        }

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM kb_article");
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
