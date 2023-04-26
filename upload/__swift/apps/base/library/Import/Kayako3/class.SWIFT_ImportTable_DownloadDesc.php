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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: DownloadDesc
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_DownloadDesc extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'DownloadDesc');

        if (!$this->TableExists(TABLE_PREFIX . 'downloaddesc')) {
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

        $_oldDownloadItemIDList = $_kbArticleDataContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT downloaditems.filelink, downloaddesc.* FROM " . TABLE_PREFIX . "downloaddesc AS downloaddesc
            LEFT JOIN " . TABLE_PREFIX . "downloaditems AS downloaditems ON (downloaddesc.downloaditemid = downloaditems.downloaditemid)
                ORDER BY downloaddesc.downloaddescid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_kbArticleDataContainer[$this->DatabaseImport->Record['downloaditemid']] = $this->DatabaseImport->Record;

            $_oldDownloadItemIDList[] = $this->DatabaseImport->Record['downloaditemid'];
        }

        $_newKBArticleIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('downloaditem', $_oldDownloadItemIDList);

        foreach ($_kbArticleDataContainer as $_kbArticleDataID => $_kbArticleData) {
            $_count++;

            if (!isset($_newKBArticleIDList[$_kbArticleData['downloaditemid']])) {
                $this->GetImportManager()->AddToLog('Failed to Import Download Item Data due to non existent KB Article: ' . htmlspecialchars($_kbArticleData['downloaditemid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_kbArticleID = (int)($_newKBArticleIDList[$_kbArticleData['downloaditemid']]);

            $_finalContents = $_kbArticleData['description'];

            if (!stristr($_finalContents, '<br>') && !stristr($_finalContents, '<br />') && !stristr($_finalContents, '<p>')) {
                $_finalContents = nl2br($_finalContents);
            }

            if ($_kbArticleData['filelink'] != '') {
                $_finalContents .= '<br /><br />Link: <a href="' . $_kbArticleData['filelink'] . '" target="_blank">' . $_kbArticleData['filelink'] . '</a>';
            }

            $_contentsText = strip_tags_attributes(str_replace('<br />', ' ', $_kbArticleData['description']));

            $this->GetImportManager()->AddToLog('Importing Download Item Data for Message: ' . $_kbArticleID, SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticledata',
                array('kbarticleid' => $_kbArticleID, 'contents' => $_finalContents, 'contentstext' => $_contentsText), 'INSERT');
            $_kbArticleDataID = $this->Database->InsertID();
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "downloaddesc");
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
