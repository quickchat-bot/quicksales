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

/**
 * Import Table: CannedCategoryRebuild
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CannedCategoryRebuild extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CannedCategoryRebuild');

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

        $_count = 0;
        $_cannedCategoryContainer = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "cannedcategories ORDER BY cannedcategoryid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->Database->NextRecord()) {
            $_count++;

            $_cannedCategoryContainer[$this->Database->Record['cannedcategoryid']] = $this->Database->Record;
        }

        foreach ($_cannedCategoryContainer as $_cannedCategoryID => $_cannedCategory) {
            $_newParentCannedCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('cannedcategory', $_cannedCategory['parentcategoryid']);

            $this->GetImportManager()->AddToLog('Importing Canned Category Relationship: ' . htmlspecialchars($_cannedCategory['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'cannedcategories',
                array('parentcategoryid' => $_newParentCannedCategoryID), 'UPDATE', "cannedcategoryid = '" . $_cannedCategoryID . "'");
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
