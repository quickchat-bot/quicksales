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

use News\Models\Category\SWIFT_NewsCategory;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: NewsData
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_NewsData extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'NewsData');

        if (!$this->TableExists(TABLE_PREFIX . 'newsdata')) {
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

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "newsitemdata");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "newsdata ORDER BY newsdataid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newNewsID = $this->ImportManager->GetImportRegistry()->GetKey('news', $this->DatabaseImport->Record['newsid']);

            if ($_newNewsID == false) {
                $this->GetImportManager()->AddToLog('News data import failed due to non existant news id (incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_contents = $this->DatabaseImport->Record['contents'];
            $_finalContents = $_contents;
            if (!stristr($_contents, '<br>') && !stristr($_contents, '<br />') && !stristr($_contents, '<p>')) {
                $_finalContents = nl2br($_contents);
            }

            $this->GetImportManager()->AddToLog('Importing News Data for News ID: ' . $_newNewsID, SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'newsitemdata',
                array('newsitemid' => $_newNewsID, 'contents' => $_finalContents), 'INSERT');
            $_newsDataID = $this->Database->InsertID();

            $this->Database->AutoExecute(TABLE_PREFIX . 'newsitems',
                array('contentshash' => md5($_finalContents)), 'UPDATE', "newsitemid = '" . $_newNewsID . "'");
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "newsdata");
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
