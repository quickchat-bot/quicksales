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

use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: News
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_News extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'News');

        if (!$this->TableExists(TABLE_PREFIX . 'news')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('NewsItem:NewsItem', APP_NEWS);
        SWIFT_Loader::LoadModel('Category:NewsCategory', APP_NEWS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "newsitems");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "news ORDER BY newsid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);
            $_editedStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['editedstaffid']);

            $_author = $this->Language->Get('na');
            $_email = '';

            if (isset($_staffCache[$_newStaffID])) {
                $_author = $_staffCache[$_newStaffID]['fullname'];
                $_email = $_staffCache[$_newStaffID]['email'];
            }

            $_newsType = SWIFT_NewsItem::TYPE_PUBLIC;
            if ($this->DatabaseImport->Record['newstype'] == 'private') {
                $_newsType = SWIFT_NewsItem::TYPE_PRIVATE;
            }

            $this->GetImportManager()->AddToLog('Importing News: ' . htmlspecialchars($this->DatabaseImport->Record['subject']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'newsitems',
                array('staffid' => $_newStaffID, 'newstype' => $_newsType, 'newsstatus' => SWIFT_NewsItem::STATUS_PUBLISHED, 'author' => $_author, 'email' => $_email,
                    'subject' => $this->DatabaseImport->Record['subject'], 'emailsubject' => $this->DatabaseImport->Record['emailsubject'], 'description' => $this->DatabaseImport->Record['description'],
                    'descriptionhash' => md5($this->DatabaseImport->Record['description']), 'subjecthash' => md5($this->DatabaseImport->Record['subject']), 'contentshash' => '',
                    'dateline' => $this->DatabaseImport->Record['dateline'], 'expiry' => $this->DatabaseImport->Record['expiry'], 'issynced' => '0', 'syncguidhash' => '', 'syncdateline' => '0',
                    'edited' => $this->DatabaseImport->Record['edited'], 'editedstaffid' => $_editedStaffID, 'editeddateline' => $this->DatabaseImport->Record['editeddateline'],
                    'totalcomments' => '0', 'uservisibilitycustom' => '0', 'staffvisibilitycustom' => '0', 'allowcomments' => '1'), 'INSERT');
            $_newsID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('news', $this->DatabaseImport->Record['newsid'], $_newsID);
        }

        SWIFT_NewsCategory::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "news");
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
