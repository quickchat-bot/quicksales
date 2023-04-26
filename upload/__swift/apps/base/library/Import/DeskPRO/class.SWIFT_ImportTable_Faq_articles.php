<?php

namespace Base\Library\Import\DeskPRO;

use Base\Models\Attachment\SWIFT_Attachment;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: KBArticle
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Faq_articles extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Faq_articles');

        if (!$this->TableExists('faq_articles')) {
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

        $this->DatabaseImport->QueryLimit("SELECT * FROM faq_articles ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['techid_made']);
            $_newEditedStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['techid_modified']);

            $_staffName = $this->Language->Get('na');
            $_staffEmail = '';
            if (isset($_staffCache[$_newStaffID])) {
                $_staffName = $_staffCache[$_newStaffID]['fullname'];
                $_staffEmail = $_staffCache[$_newStaffID]['email'];
            }

            $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_DRAFT;
            if ($this->DatabaseImport->Record['published'] == 1) {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED;
            } elseif ($this->DatabaseImport->Record['published'] == 0) {
                $_articleStatus = SWIFT_KnowledgebaseArticle::STATUS_DRAFT;
            }

            $_isEdited = 0;
            if (!empty($this->DatabaseImport->Record['timestamp_modified'])) {
                $_isEdited = 1;
            }

            $_articleRating = 0;
            if ($this->DatabaseImport->Record['rating'] == '20') {
                $_articleRating = 1;
            } elseif ($this->DatabaseImport->Record['rating'] == '40') {
                $_articleRating = 2;
            } elseif ($this->DatabaseImport->Record['rating'] == '60') {
                $_articleRating = 3;
            } elseif ($this->DatabaseImport->Record['rating'] == '80') {
                $_articleRating = 4;
            } elseif ($this->DatabaseImport->Record['rating'] == '10') {
                $_articleRating = 5;
            }

//            Check if the imported article has attachments
            $_articleAttachments = array();
            $_hasattachments = 0;
            $this->DatabaseImport->Query("SELECT * FROM faq_attachments WHERE articleid = " . (int)($this->DatabaseImport->Record['id']) . " ORDER BY id ASC;", 3);
            while ($this->DatabaseImport->NextRecord(3)) {
                $_hasattachments = 1;
                $_articleAttachments[] = $this->DatabaseImport->Record3;
            }

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Article: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticles',
                array('creator' => SWIFT_KnowledgebaseArticle::CREATOR_STAFF, 'creatorid' => $_newStaffID, 'author' => $_staffName, 'email' => $_staffEmail,
                    'subject' => $this->DatabaseImport->Record['title'], 'isedited' => $_isEdited,
                    'editeddateline' => $this->DatabaseImport->Record['timestamp_modified'], 'editedstaffid' => $_newEditedStaffID,
                    'views' => $this->DatabaseImport->Record['views'], 'isfeatured' => $this->DatabaseImport->Record['featured'], 'allowcomments' => $this->DatabaseImport->Record['allow_comments'], 'totalcomments' => '0',
                    'hasattachments' => $_hasattachments, 'dateline' => $this->DatabaseImport->Record['timestamp_made'], 'articlestatus' => $_articleStatus,
                    'articlerating' => $_articleRating, 'ratinghits' => $this->DatabaseImport->Record['votes'],
                    'ratingcount' => '0'), 'INSERT');
            $_knowledgebaseArticleID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('kbarticle', $this->DatabaseImport->Record['id'], $_knowledgebaseArticleID, true);

//            ARTICLE DATA
            $_kbArticleID = $_knowledgebaseArticleID;

            $_tempContents = $this->DatabaseImport->Record['question'] . '<br />' . $this->DatabaseImport->Record['answer'];

            $_contentsText = strip_tags_attributes(str_replace('<br />', ' ', $_tempContents));

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Article Data for Message: ' . $_kbArticleID, SWIFT_ImportManager::LOG_SUCCESS);

            $_finalContents = $_tempContents;
            if (!stristr($_finalContents, '<br>') && !stristr($_finalContents, '<br />') && !stristr($_finalContents, '<p>')) {
                $_finalContents = nl2br($_finalContents);
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticledata',
                array('kbarticleid' => $_kbArticleID, 'contents' => $_finalContents, 'contentstext' => $_contentsText), 'INSERT');

            $_kbArticleDataID = $this->Database->InsertID();

//            ARTICLE & CATEGORY LINK
            $_knowledgebaseCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('kbcategory', $this->DatabaseImport->Record['category']);

            $this->GetImportManager()->AddToLog('Importing Knowledgebase Article Link: ' . $_knowledgebaseArticleID . ' <=> ' . $_knowledgebaseCategoryID, SWIFT_ImportManager::LOG_SUCCESS);

            SWIFT_KnowledgebaseArticleLink::Create($_knowledgebaseArticleID, SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY, $_knowledgebaseCategoryID);

//            ATTACHMENTS
            foreach ($_articleAttachments as $indexID => $articleAttachmentData) {
                $this->GetImportManager()->AddToLog('Importing Knowledgebase Attachment ID: ' . htmlspecialchars($articleAttachmentData['id']) . ' for Knowledgebase Article: ' . htmlspecialchars($articleAttachmentData['articleid']), SWIFT_ImportManager::LOG_SUCCESS);

                $_attachmentType = SWIFT_Attachment::TYPE_FILE;
                $_storeFileName = SWIFT_Attachment::GenerateRandomFileName();

                $this->Database->AutoExecute(TABLE_PREFIX . 'attachments',
                    array('linktype' => SWIFT_Attachment::LINKTYPE_KBARTICLE, 'linktypeid' => $_knowledgebaseArticleID,
                        'downloaditemid' => '0', 'ticketid' => '0', 'filename' => $articleAttachmentData['filename'],
                        'filesize' => $articleAttachmentData['filesize'], 'filetype' => $articleAttachmentData['extension'], 'dateline' => $articleAttachmentData['timestamp'],
                        'attachmenttype' => $_attachmentType, 'storefilename' => $_storeFileName
                    ), 'INSERT');
                $_attachmentID = $this->Database->Insert_ID();

                $this->ImportManager->GetImportRegistry()->UpdateKey('attachment', $articleAttachmentData['id'], $_attachmentID, true);

//                Import the attachment data
                $this->DatabaseImport->Query("SELECT * FROM `blobs` AS b, `blob_parts` AS bp WHERE b.id = bp.blobid AND b.id = " . (int)($articleAttachmentData['blobid']) . " ORDER BY b.id ASC;", 3);
                while ($this->DatabaseImport->NextRecord(3)) {
                    $dwk_content = "";

//                    Checking if the attachment is on BD or file system
                    if (empty($this->DatabaseImport->Record3['filepath'])) {
//                        Is on BD
                        $dwk_content = $this->DatabaseImport->Record3['blobdata'];
                    } else {
//                        Is on file system
                        $_file = fopen($this->DatabaseImport->Record3['filepath'], "r");

//                        Read the attachment
                        if ($_file) {
                            while (!feof($_file)) {
                                $buffer = fgets($_file, 4096);
                                $dwk_content = $dwk_content . $buffer;
                            }
                            fclose($_file);
                        }
                    }
                    $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_storeFileName;
                    $Handle = fopen($_finalFilePath, 'w');
                    fwrite($Handle, $dwk_content);
                    fclose($Handle);

                    @chmod($_finalFilePath, SWIFT_Attachment::DEFAULT_FILEPERMISSION);

                    unset($dwk_content);
                    unset($_file);
                }
            }
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM faq_articles");
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
