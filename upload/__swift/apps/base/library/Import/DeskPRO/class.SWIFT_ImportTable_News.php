<?php

namespace Base\Library\Import\DeskPRO;

use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: News
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_News extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'News');

        if (!$this->TableExists('news')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadLibrary('NewsItem:NewsItem', APP_NEWS);
        SWIFT_Loader::LoadLibrary('Category:NewsCategory', APP_NEWS);
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
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "newsitems");
//        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM news ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['techid']);

            $_author = $this->Language->Get('na');
            $_email = '';

            if (isset($_staffCache[$_newStaffID])) {
                $_author = $_staffCache[$_newStaffID]['fullname'];
                $_email = $_staffCache[$_newStaffID]['email'];
            }

            $_newsType = SWIFT_NewsItem::TYPE_PUBLIC;

            $_contents = $this->DatabaseImport->Record['details'];
            $_finalContents = $_contents;
            if (!stristr($_contents, '<br>') && !stristr($_contents, '<br />') && !stristr($_contents, '<p>')) {
                $_finalContents = nl2br($_contents);
            }

            $_contentsHash = md5($_finalContents);

            $this->GetImportManager()->AddToLog('Importing Public News: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'newsitems',
                array('staffid' => $_newStaffID, 'newstype' => $_newsType, 'newsstatus' => SWIFT_NewsItem::STATUS_PUBLISHED, 'author' => $_author, 'email' => $_email,
                    'subject' => $this->DatabaseImport->Record['title'], 'descriptionhash' => md5(''), 'subjecthash' => md5($this->DatabaseImport->Record['title']), 'contentshash' => $_contentsHash,
                    'dateline' => $this->DatabaseImport->Record['timestamp'], 'expiry' => '0', 'issynced' => '0', 'syncguidhash' => '', 'syncdateline' => '0',
                    'edited' => '0', 'editedstaffid' => '0', 'editeddateline' => '0',
                    'totalcomments' => '0', 'uservisibilitycustom' => '0', 'staffvisibilitycustom' => '0', 'allowcomments' => '1'), 'INSERT');
            $_newsID = $this->Database->InsertID();

            $this->Database->AutoExecute(TABLE_PREFIX . 'newsitemdata',
                array('newsitemid' => $_newsID, 'contents' => $_finalContents), 'INSERT');

            $_newsDataID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('news', $this->DatabaseImport->Record['id'], $_newsID);
        }

        SWIFT_NewsCategory::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM news");
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
