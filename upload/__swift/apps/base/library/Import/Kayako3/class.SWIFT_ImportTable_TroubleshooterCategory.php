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
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * Import Table: TroubleshooterCategory
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TroubleshooterCategory extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TroubleshooterCategory');

        if (!$this->TableExists(TABLE_PREFIX . 'troubleshootercat')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Category:TroubleshooterCategory', APP_TROUBLESHOOTER);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshootercategories");
        }

        $_staffContainer = array();
        $this->DatabaseImport->Query("SELECT * FROM " . TABLE_PREFIX . "staff");
        while ($this->DatabaseImport->NextRecord()) {
            $_staffContainer[$this->DatabaseImport->Record['staffid']] = $this->DatabaseImport->Record;
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "troubleshootercat ORDER BY troubleshootercatid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $_newStaffName = '';
            if (isset($_staffContainer[$this->DatabaseImport->Record['staffid']])) {
                $_newStaffName = $_staffContainer[$this->DatabaseImport->Record['staffid']]['fullname'];
            }

            $this->GetImportManager()->AddToLog('Importing Troubleshooter Category: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshootercategories',
                array('staffid' => $_newStaffID, 'staffname' => $_newStaffName, 'title' => $this->DatabaseImport->Record['title'],
                    'description' => $this->DatabaseImport->Record['description'], 'dateline' => $this->DatabaseImport->Record['dateline'],
                    'categorytype' => SWIFT_TroubleshooterCategory::TYPE_GLOBAL, 'displayorder' => $this->DatabaseImport->Record['displayorder'],
                    'views' => $this->DatabaseImport->Record['views'], 'uservisibilitycustom' => '0', 'staffvisibilitycustom' => '0'), 'INSERT');
            $_troubleshooterCategoryID = $this->Database->InsertID();
            $this->ImportManager->GetImportRegistry()->UpdateKey('troubleshootercategory', $this->DatabaseImport->Record['troubleshootercatid'], $_troubleshooterCategoryID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "troubleshootercat");
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
