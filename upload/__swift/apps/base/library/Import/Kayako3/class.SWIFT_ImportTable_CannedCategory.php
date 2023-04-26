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

use LiveChat\Models\Canned\SWIFT_CannedCategory;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: CannedCategory
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CannedCategory extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CannedCategory');

        if (!$this->TableExists(TABLE_PREFIX . 'cannedcategories')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Canned:CannedCategory', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Canned:CannedResponse', APP_LIVECHAT);
        SWIFT_Loader::LoadLibrary('Canned:CannedManager', APP_LIVECHAT);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "cannedcategories");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "cannedcategories ORDER BY cannedcategoryid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $_categoryType = SWIFT_CannedCategory::TYPE_PUBLIC;
            if ($this->DatabaseImport->Record['categorytype'] == '0') {
                $_categoryType = SWIFT_CannedCategory::TYPE_PRIVATE;
            }

            $this->GetImportManager()->AddToLog('Importing Canned Category: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'cannedcategories',
                array('parentcategoryid' => $this->DatabaseImport->Record['parentcategoryid'], 'categorytype' => $_categoryType, 'staffid' => $_newStaffID,
                    'title' => $this->DatabaseImport->Record['title']), 'INSERT');
            $_cannedCategoryID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('cannedcategory', $this->DatabaseImport->Record['cannedcategoryid'], $_cannedCategoryID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "cannedcategories");
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
