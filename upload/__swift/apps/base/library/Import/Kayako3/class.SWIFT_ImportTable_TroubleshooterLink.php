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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: TroubleshooterLink
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TroubleshooterLink extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TroubleshooterLink');

        if (!$this->TableExists(TABLE_PREFIX . 'troubleshooterlinks')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Category:TroubleshooterCategory', APP_TROUBLESHOOTER);
        SWIFT_Loader::LoadModel('Step:TroubleshooterStep', APP_TROUBLESHOOTER);
        SWIFT_Loader::LoadModel('Link:TroubleshooterLink', APP_TROUBLESHOOTER);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshooterlinks");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "troubleshooterlinks
            ORDER BY troubleshooterlinkid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
            $_newTroubleshooterCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('troubleshootercategory', $this->DatabaseImport->Record['troubleshootercatid']);
            if (empty($_newTroubleshooterCategoryID)) {
                $this->GetImportManager()->AddToLog('Failed to Import Troubleshooter Link: ' . (int)($this->DatabaseImport->Record['troubleshooterlinkid']) . ', Probably Reason: Incomplete Old Deletion', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_newParentTroubleshooterStepID = $this->ImportManager->GetImportRegistry()->GetKey('troubleshooterstep', $this->DatabaseImport->Record['parenttroubleshooterid']);
            $_newChildTroubleshooterStepID = $this->ImportManager->GetImportRegistry()->GetKey('troubleshooterstep', $this->DatabaseImport->Record['childtroubleshooterid']);
            if (empty($_newChildTroubleshooterStepID)) {
                $this->GetImportManager()->AddToLog('Failed to Import Troubleshooter Link (2): ' . (int)($this->DatabaseImport->Record['troubleshooterlinkid']) . ', Probably Reason: Incomplete Old Deletion', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Troubleshooter Link: ' . (int)($this->DatabaseImport->Record['troubleshooterlinkid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshooterlinks',
                array('troubleshootercategoryid' => $_newTroubleshooterCategoryID, 'parenttroubleshooterstepid' => $_newParentTroubleshooterStepID,
                    'childtroubleshooterstepid' => $_newChildTroubleshooterStepID), 'INSERT');
            $_troubleshooterLinkID = $this->Database->InsertID();
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "troubleshooterlinks");
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
