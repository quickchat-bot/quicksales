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
 * Import Table: PredefinedReplyData
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_PredefinedReplyData extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'PredefinedReplyData');

        if (!$this->TableExists(TABLE_PREFIX . 'predefinedreplydata')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Macro:MacroCategory', APP_TICKETS);
        SWIFT_Loader::LoadModel('Macro:MacroReply', APP_TICKETS);
        SWIFT_Loader::LoadLibrary('Macro:MacroManager', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "macroreplydata");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT predefinedreplies.*, predefinedreplydata.* FROM " . TABLE_PREFIX . "predefinedreplydata AS predefinedreplydata
            LEFT JOIN " . TABLE_PREFIX . "predefinedreplies AS predefinedreplies ON (predefinedreplies.predefinedreplyid = predefinedreplydata.predefinedreplyid)
            ORDER BY predefinedreplydata.predefinedreplydataid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newMacroReplyID = $this->ImportManager->GetImportRegistry()->GetKey('macroreply', $this->DatabaseImport->Record['predefinedreplyid']);
            if (!$_newMacroReplyID) {
                $this->GetImportManager()->AddToLog('Failed to Import Predefined Reply Data due to non existent parent record: ' . htmlspecialchars($this->DatabaseImport->Record['predefinedreplyid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Predefined Reply Data: ' . htmlspecialchars($this->DatabaseImport->Record['subject']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'macroreplydata',
                array('macroreplyid' => $_newMacroReplyID, 'contents' => $this->DatabaseImport->Record['contents'], 'tagcontents' => serialize(array())), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "predefinedreplydata");
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
