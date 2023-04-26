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

use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: Comment
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Comment extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Comment');

        if (!$this->TableExists(TABLE_PREFIX . 'comments')) {
            $this->SetByPass(true);
        }
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "comments");
        }

        $_count = 0;

        $_commentContainer = $_oldKnowledgebaseIDList = $_oldDownloadItemIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "comments ORDER BY commentid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            // define("COMMENT_KNOWLEDGEBASE", 4);
            // define("COMMENT_TROUBLESHOOTER", 6);
            // define("COMMENT_NEWS", 1);
            // define("COMMENT_DOWNLOADS", 5);

            $_commentType = $_typeID = false;
            if ($this->DatabaseImport->Record['commenttype'] == '1') {
                $_commentType = SWIFT_Comment::TYPE_NEWS;
                $_typeID = $this->ImportManager->GetImportRegistry()->GetKey('news', $this->DatabaseImport->Record['typeid']);
            } elseif ($this->DatabaseImport->Record['commenttype'] == '4') {
                $_commentType = SWIFT_Comment::TYPE_KNOWLEDGEBASE;

                $_oldKnowledgebaseIDList[] = $this->DatabaseImport->Record['typeid'];
            } elseif ($this->DatabaseImport->Record['commenttype'] == '5') {
                $_commentType = 2;
                $_oldDownloadItemIDList[] = $this->DatabaseImport->Record['typeid'];
            } else {
                $this->GetImportManager()->AddToLog('Ignoring Comment due to Invalid Type: ' . htmlspecialchars($this->DatabaseImport->Record['commenttype']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_commentContainer[$this->DatabaseImport->Record['commentid']] = $this->DatabaseImport->Record;
            $_commentContainer[$this->DatabaseImport->Record['commentid']]['commenttype'] = $_commentType;
            $_commentContainer[$this->DatabaseImport->Record['commentid']]['newtypeid'] = $_typeID;
        }

        $_newKnowledgebaseArticleIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('kbarticle', $_oldKnowledgebaseIDList);
        $_newDownloadItemIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('downloaditem', $_oldDownloadItemIDList);

        foreach ($_commentContainer as $_commentID => $_comment) {
            if ($_comment['commenttype'] == SWIFT_Comment::TYPE_KNOWLEDGEBASE && isset($_newKnowledgebaseArticleIDList[$_comment['typeid']])) {
                $_comment['newtypeid'] = $_newKnowledgebaseArticleIDList[$_comment['typeid']];

                // 2 == downloads
            } elseif ($_comment['commenttype'] == 2 && isset($_newDownloadItemIDList[$_comment['typeid']])) {
                $_comment['newtypeid'] = $_newDownloadItemIDList[$_comment['typeid']];

                $_comment['commenttype'] = SWIFT_Comment::TYPE_KNOWLEDGEBASE;
            } elseif (!empty($_comment['newtypeid'])) {
                // Do Nothing
            } else {
                $this->GetImportManager()->AddToLog('Ignoring Comment due to Invalid Type ID: ' . htmlspecialchars($_comment['commentid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            if ($_comment['isapproved'] == '0') {
                $this->GetImportManager()->AddToLog('Ignoring Unapproved Comment: ' . htmlspecialchars($_comment['commentid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_commentStatus = SWIFT_Comment::STATUS_APPROVED;

            $this->GetImportManager()->AddToLog('Importing Comment ID: ' . htmlspecialchars($_comment['commentid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'comments',
                array('typeid' => (int)($_comment['newtypeid']), 'creatortype' => SWIFT_Comment::CREATOR_USER, 'creatorid' => '0', 'commenttype' => $_comment['commenttype'],
                    'fullname' => $_comment['fullname'], 'email' => $_comment['email'], 'ipaddress' => '', 'dateline' => $_comment['dateline'], 'parentcommentid' => '0',
                    'commentstatus' => $_commentStatus, 'useragent' => '', 'referrer' => '', 'parenturl' => ''), 'INSERT');
            $_newCommentID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('comment', $_commentID, $_newCommentID, true);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "comments");
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

        return 3000;
    }
}

?>
