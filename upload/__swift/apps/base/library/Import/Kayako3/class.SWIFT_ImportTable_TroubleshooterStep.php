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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Import\QuickSupport3;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;

/**
 * Import Table: TroubleshooterStep
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TroubleshooterStep extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TroubleshooterStep');

        if (!$this->TableExists(TABLE_PREFIX . 'troubleshootersteps')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Category:TroubleshooterCategory', APP_TROUBLESHOOTER);
        SWIFT_Loader::LoadModel('Step:TroubleshooterStep', APP_TROUBLESHOOTER);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshootersteps");
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshooterdata");
        }

        $_staffContainer = array();
        $this->DatabaseImport->Query("SELECT * FROM " . TABLE_PREFIX . "staff");
        while ($this->DatabaseImport->NextRecord()) {
            $_staffContainer[$this->DatabaseImport->Record['staffid']] = $this->DatabaseImport->Record;
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT troubleshootersteps.*, troubleshooterdata.contents AS contents FROM " . TABLE_PREFIX . "troubleshootersteps AS troubleshootersteps
            LEFT JOIN " . TABLE_PREFIX . "troubleshooterdata AS troubleshooterdata ON (troubleshootersteps.troubleshooterid = troubleshooterdata.troubleshooterid)
            ORDER BY troubleshootersteps.troubleshooterid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
            $_newTroubleshooterCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('troubleshootercategory', $this->DatabaseImport->Record['troubleshootercatid']);
            if (empty($_newTroubleshooterCategoryID)) {
                $this->GetImportManager()->AddToLog('Failed to Import Troubleshooter Step: ' . htmlspecialchars($this->DatabaseImport->Record['subject']) . ', Probably Reason: Incomplete Old Deletion', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);
            $_newEditedStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['editedstaffid']);

            $_newStaffName = '';
            if (isset($_staffContainer[$this->DatabaseImport->Record['staffid']])) {
                $_newStaffName = $_staffContainer[$this->DatabaseImport->Record['staffid']]['fullname'];
            }

            $_newEditedStaffName = '';
            if (isset($_staffContainer[$this->DatabaseImport->Record['editedstaffid']])) {
                $_newEditedStaffName = $_staffContainer[$this->DatabaseImport->Record['editedstaffid']]['fullname'];
            }

            $this->GetImportManager()->AddToLog('Importing Troubleshooter Step: ' . htmlspecialchars($this->DatabaseImport->Record['subject']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshootersteps',
                array('troubleshootercategoryid' => $_newTroubleshooterCategoryID, 'stepstatus' => SWIFT_TroubleshooterStep::STATUS_PUBLISHED,
                    'staffid' => $_newStaffID, 'staffname' => $_newStaffName, 'subject' => $this->DatabaseImport->Record['subject'],
                    'edited' => $this->DatabaseImport->Record['edited'], 'editedstaffid' => $_newEditedStaffID,
                    'editedstaffname' => $_newEditedStaffName, 'editeddateline' => $this->DatabaseImport->Record['dateline'],
                    'dateline' => $this->DatabaseImport->Record['dateline'], 'displayorder' => $this->DatabaseImport->Record['displayorder'],
                    'views' => $this->DatabaseImport->Record['views'], 'allowcomments' => '1', 'hasattachments' => '0', 'redirecttickets' => '0',
                    'ticketsubject' => '', 'redirectdepartmentid' => '0'), 'INSERT');
            $_troubleshooterStepID = $this->Database->InsertID();

            $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshooterdata',
                array('troubleshooterstepid' => $_troubleshooterStepID, 'contents' => $this->DatabaseImport->Record['contents']), 'INSERT');

            $this->ImportManager->GetImportRegistry()->UpdateKey('troubleshooterstep', $this->DatabaseImport->Record['troubleshooterid'], $_troubleshooterStepID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "troubleshootersteps");
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
