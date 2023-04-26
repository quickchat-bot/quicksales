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
use Tickets\Models\Macro\SWIFT_MacroCategory;

/**
 * Import Table: PredefinedCategory
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_PredefinedCategory extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'PredefinedCategory');

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

        $_staffCache = $this->Cache->Get('staffcache');

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "macrocategories");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "predefinedcategories ORDER BY predefinedcategoryid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $_categoryType = SWIFT_MacroCategory::TYPE_PUBLIC;
            if ($this->DatabaseImport->Record['categorytype'] == '0') {
                $_categoryType = SWIFT_MacroCategory::TYPE_PRIVATE;
            }

            $this->GetImportManager()->AddToLog('Importing Macro Category: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'macrocategories',
                array('parentcategoryid' => $this->DatabaseImport->Record['parentcategoryid'], 'categorytype' => $_categoryType, 'staffid' => $_newStaffID,
                    'restrictstaffgroupid' => '0', 'title' => $this->DatabaseImport->Record['title']), 'INSERT');
            $_macroCategoryID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('macrocategory', $this->DatabaseImport->Record['predefinedcategoryid'], $_macroCategoryID);
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
