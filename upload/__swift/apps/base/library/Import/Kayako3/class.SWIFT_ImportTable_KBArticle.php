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

use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: KBArticle
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_KBArticle extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'KBArticle');

        if (!$this->TableExists(TABLE_PREFIX . 'kbarticles')) {
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

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbarticles");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "kbarticles ORDER BY kbarticleid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);
            $_newEditedStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['editedstaffid']);

            $_staffName = $this->Language->Get('na');
            $_staffEmail = '';
            if (isset($_staffCache[$_newStaffID])) {
                $_staffName = $_staffCache[$_newStaffID]['fullname'];
                $_staffEmail = $_staffCache[$_newStaffID]['email'];
            }

            $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_DRAFT;
            if ($this->DatabaseImport->Record['articlestatus'] == 'published') {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED;
            } elseif ($this->DatabaseImport->Record['articlestatus'] == 'private') {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_DRAFT;
            }

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Article: ' . htmlspecialchars($this->DatabaseImport->Record['subject']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticles',
                array('creator' => SWIFT_KnowledgebaseArticle::CREATOR_STAFF, 'creatorid' => $_newStaffID, 'author' => $_staffName, 'email' => $_staffEmail,
                    'subject' => $this->DatabaseImport->Record['subject'], 'isedited' => $this->DatabaseImport->Record['isedited'],
                    'editeddateline' => $this->DatabaseImport->Record['editeddateline'], 'editedstaffid' => $_newEditedStaffID,
                    'views' => $this->DatabaseImport->Record['views'], 'isfeatured' => '0', 'allowcomments' => '1', 'totalcomments' => '0',
                    'hasattachments' => '0', 'dateline' => $this->DatabaseImport->Record['dateline'], 'articlestatus' => $_articleStatus,
                    'articlerating' => $this->DatabaseImport->Record['articlerating'], 'ratinghits' => $this->DatabaseImport->Record['ratinghits'],
                    'ratingcount' => $this->DatabaseImport->Record['ratingcount']), 'INSERT');
            $_knowledgebaseArticleID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('kbarticle', $this->DatabaseImport->Record['kbarticleid'], $_knowledgebaseArticleID, true);
        }

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "kbarticles");
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
