<?php

namespace Base\Library\Import\DeskPRO;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: PredefinedReply
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Quickreply extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Quickreply');

        if (!$this->TableExists('quickreply')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Macro:MacroCategory', APP_TICKETS);
        SWIFT_Loader::LoadModel('Macro:MacroReply', APP_TICKETS);
        SWIFT_Loader::LoadLibrary('Macro:MacroManager', APP_TICKETS);
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
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "macroreplies");
//        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM quickreply ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newMacroCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('macrocategory', $this->DatabaseImport->Record['category']);
            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['techid']);

            $this->GetImportManager()->AddToLog('Importing Predefined Reply: ' . htmlspecialchars($this->DatabaseImport->Record['name']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'macroreplies',
                array('macrocategoryid' => $_newMacroCategoryID, 'staffid' => $_newStaffID, 'subject' => $this->DatabaseImport->Record['name'],
                    'dateline' => DATENOW, 'totalhits' => '0', 'lastusage' => '0', 'departmentid' => '-1', 'ownerstaffid' => '-1',
                    'tickettypeid' => '-1', 'ticketstatusid' => '-1', 'priorityid' => '-1'), 'INSERT');
            $_macroReplyID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('macroreply', $this->DatabaseImport->Record['id'], $_macroReplyID);

            $this->GetImportManager()->AddToLog('Importing Predefined Reply Data: ' . htmlspecialchars($this->DatabaseImport->Record['name']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'macroreplydata',
                array('macroreplyid' => $_macroReplyID, 'contents' => $this->DatabaseImport->Record['response'], 'tagcontents' => serialize(array())), 'INSERT');
        }

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM quickreply");
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
