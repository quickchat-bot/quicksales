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
 * Import Table: PredefinedReply
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_PredefinedReply extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'PredefinedReply');

        if (!$this->TableExists(TABLE_PREFIX . 'predefinedreplies')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "macroreplies");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "predefinedreplies ORDER BY predefinedreplyid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newMacroCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('macrocategory', $this->DatabaseImport->Record['predefinedcategoryid']);
            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $this->GetImportManager()->AddToLog('Importing Predefined Reply: ' . htmlspecialchars($this->DatabaseImport->Record['subject']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'macroreplies',
                array('macrocategoryid' => $_newMacroCategoryID, 'staffid' => $_newStaffID, 'subject' => $this->DatabaseImport->Record['subject'],
                    'dateline' => $this->DatabaseImport->Record['dateline'], 'totalhits' => '0', 'lastusage' => '0', 'departmentid' => '-1', 'ownerstaffid' => '-1',
                    'tickettypeid' => '-1', 'ticketstatusid' => '-1', 'priorityid' => '-1'), 'INSERT');
            $_macroReplyID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('macroreply', $this->DatabaseImport->Record['predefinedreplyid'], $_macroReplyID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "predefinedreplies");
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
