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
use Base\Models\CustomField\SWIFT_CustomField;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: CustomField
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CustomField extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CustomField');

        if (!$this->TableExists(TABLE_PREFIX . 'customfields')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfields");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "customfields ORDER BY customfieldid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_fieldType = false;

            // Text
            if ($this->DatabaseImport->Record['fieldtype'] == '1') {
                $_fieldType = SWIFT_CustomField::TYPE_TEXT;

                // Text Area
            } elseif ($this->DatabaseImport->Record['fieldtype'] == '2') {
                $_fieldType = SWIFT_CustomField::TYPE_TEXTAREA;

                // Password
            } elseif ($this->DatabaseImport->Record['fieldtype'] == '3') {
                $_fieldType = SWIFT_CustomField::TYPE_PASSWORD;

                // Checkbox
            } elseif ($this->DatabaseImport->Record['fieldtype'] == '4') {
                $_fieldType = SWIFT_CustomField::TYPE_CHECKBOX;

                // Radio
            } elseif ($this->DatabaseImport->Record['fieldtype'] == '5') {
                $_fieldType = SWIFT_CustomField::TYPE_RADIO;

                // Select
            } elseif ($this->DatabaseImport->Record['fieldtype'] == '6') {
                $_fieldType = SWIFT_CustomField::TYPE_SELECT;

                // Select Multiple
            } elseif ($this->DatabaseImport->Record['fieldtype'] == '7') {
                $_fieldType = SWIFT_CustomField::TYPE_SELECTMULTIPLE;

                // Custom
            } elseif ($this->DatabaseImport->Record['fieldtype'] == '8') {
                $_fieldType = SWIFT_CustomField::TYPE_CUSTOM;

            } else {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field due to unsupported field type: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_customFieldGroupID = $this->GetImportManager()->GetImportRegistry()->GetKey('customfieldgroup', $this->DatabaseImport->Record['customfieldgroupid']);
            if ($_customFieldGroupID == false) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field due to non existant custom field group (' . (int)($this->DatabaseImport->Record['customfieldgroupid']) . ') field title: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Custom Field: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            //V3 can have null description fields
            $_description = $this->DatabaseImport->Record['description'];
            if ($_description == null) {
                $_description = '';
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'customfields',
                array('customfieldgroupid' => $_customFieldGroupID, 'title' => $this->DatabaseImport->Record['title'], 'fieldtype' => $_fieldType,
                    'fieldname' => $this->DatabaseImport->Record['fieldname'], 'defaultvalue' => $this->DatabaseImport->Record['defaultvalue'],
                    'isrequired' => (int)($this->DatabaseImport->Record['isrequired']), 'usereditable' => $this->DatabaseImport->Record['usereditable'],
                    'staffeditable' => $this->DatabaseImport->Record['staffeditable'], 'regexpvalidate' => $this->DatabaseImport->Record['regexpvalidate'],
                    'displayorder' => $this->DatabaseImport->Record['displayorder'], 'description' => $_description), 'INSERT');
            $_customFieldID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('customfield', $this->DatabaseImport->Record['customfieldid'], $_customFieldID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "customfields");
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
