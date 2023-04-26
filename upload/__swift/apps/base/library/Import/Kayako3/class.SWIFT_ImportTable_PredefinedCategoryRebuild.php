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
 * Import Table: PredefinedCategoryRebuild
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_PredefinedCategoryRebuild extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'PredefinedCategoryRebuild');

        if (!$this->TableExists(TABLE_PREFIX . 'predefinedcategories')) {
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

        $_count = 0;
        $_macroCategoryContainer = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "macrocategories ORDER BY macrocategoryid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->Database->NextRecord()) {
            $_count++;

            $_macroCategoryContainer[$this->Database->Record['macrocategoryid']] = $this->Database->Record;
        }

        foreach ($_macroCategoryContainer as $_macroCategoryID => $_macroCategory) {
            $_newParentMacroCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('macrocategory', $_macroCategory['parentcategoryid']);

            $this->GetImportManager()->AddToLog('Importing Macro Category Relationship: ' . htmlspecialchars($_macroCategory['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'macrocategories',
                array('parentcategoryid' => $_newParentMacroCategoryID), 'UPDATE', "macrocategoryid = '" . $_macroCategoryID . "'");
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "predefinedcategories");
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
