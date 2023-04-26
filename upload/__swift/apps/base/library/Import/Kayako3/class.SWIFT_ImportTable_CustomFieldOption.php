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

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: CustomField
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CustomFieldOption extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CustomFieldOption');

        if (!$this->TableExists(TABLE_PREFIX . 'customfieldoptions')) {
            $this->SetByPass(true);
        }
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldoptions");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions ORDER BY customfieldoptionid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_customFieldID = $this->GetImportManager()->GetImportRegistry()->GetKey('customfield', $this->DatabaseImport->Record['customfieldid']);
            if ($_customFieldID == false) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Option due to non existant custom field (' . (int)($this->DatabaseImport->Record['customfieldid']) . ') option value: ' . htmlspecialchars($this->DatabaseImport->Record['optionvalue']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Custom Field Option: ' . htmlspecialchars($this->DatabaseImport->Record['optionvalue']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldoptions',
                array('customfieldid' => $_customFieldID, 'optionvalue' => $this->DatabaseImport->Record['optionvalue'],
                    'displayorder' => $this->DatabaseImport->Record['displayorder'], 'isselected' => $this->DatabaseImport->Record['isselected']), 'INSERT');
            $_customFieldOptionID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('customfieldoption', $this->DatabaseImport->Record['customfieldoptionid'], $_customFieldOptionID);
        }

        SWIFT_CustomFieldManager::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "customfieldoptions");
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
