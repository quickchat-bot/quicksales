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

use Base\Models\Attachment\SWIFT_Attachment;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: DownloadItem
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_DownloadItem extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'DownloadItem');

        if (!$this->TableExists(TABLE_PREFIX . 'downloaditems')) {
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

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "downloaditems ORDER BY downloaditemid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newKBCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('downloadcategory', $this->DatabaseImport->Record['downloadcategoryid']);

            if (!$_newKBCategoryID) {
                $_newKBCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('downloads', 'downloadparentcategoryid');
            }

            if (!$_newKBCategoryID) {
                $this->GetImportManager()->AddToLog('Failed to Import Download Item due to non existent KB Category: ' . htmlspecialchars($this->DatabaseImport->Record['downloadcategoryid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            if ($this->DatabaseImport->Record['filename'] != '' && !file_exists('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->DatabaseImport->Record['storedfilename'])) {
                $this->GetImportManager()->AddToLog('Failed to Import Download File ' . htmlspecialchars($this->DatabaseImport->Record['filename']) . ' due to non existent store file: ' . htmlspecialchars($this->DatabaseImport->Record['storedfilename']) . '. Make sure you have moved all the files from files/ directory to __swift/files/ directory.', SWIFT_ImportManager::LOG_WARNING);
            }

            $_staffName = $this->Language->Get('na');
            $_staffEmail = '';

            $_hasAttachments = false;
            if ($this->DatabaseImport->Record['filename'] != '' && file_exists('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->DatabaseImport->Record['storedfilename'])) {
                $_hasAttachments = true;
            }

            $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED;

            $this->GetImportManager()->AddToLog('Importing Download Item: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticles',
                array('creator' => SWIFT_KnowledgebaseArticle::CREATOR_STAFF, 'creatorid' => 0, 'author' => $_staffName, 'email' => $_staffEmail,
                    'subject' => $this->DatabaseImport->Record['title'], 'isedited' => '0', 'editeddateline' => '0', 'editedstaffid' => '0',
                    'views' => '0', 'isfeatured' => '0', 'allowcomments' => '1', 'totalcomments' => '0',
                    'hasattachments' => (int)($_hasAttachments), 'dateline' => $this->DatabaseImport->Record['dateline'], 'articlestatus' => $_articleStatus,
                    'articlerating' => '0', 'ratinghits' => '0', 'ratingcount' => '0'), 'INSERT');
            $_knowledgebaseArticleID = $this->Database->InsertID();

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticlelinks',
                array('kbarticleid' => $_knowledgebaseArticleID, 'linktype' => SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY, 'linktypeid' => $_newKBCategoryID), 'INSERT');

            // Create the attachment
            if ($this->DatabaseImport->Record['filename'] != '' && file_exists('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->DatabaseImport->Record['storedfilename'])) {
                $this->Database->AutoExecute(TABLE_PREFIX . 'attachments',
                    array('linktype' => SWIFT_Attachment::LINKTYPE_KBARTICLE, 'linktypeid' => $_knowledgebaseArticleID, 'filename' => $this->DatabaseImport->Record['filename'],
                        'filesize' => filesize('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->DatabaseImport->Record['storedfilename']),
                        'filetype' => $this->DatabaseImport->Record['filetype'], 'dateline' => $this->DatabaseImport->Record['dateline'], 'attachmenttype' => SWIFT_Attachment::TYPE_FILE,
                        'storefilename' => $this->DatabaseImport->Record['storedfilename']), 'INSERT');
            }

            $this->ImportManager->GetImportRegistry()->UpdateKey('downloaditem', $this->DatabaseImport->Record['downloaditemid'], $_knowledgebaseArticleID, true);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "downloaditems");
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
