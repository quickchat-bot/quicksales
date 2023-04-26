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

use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: CustomFieldValue
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CustomFieldValue extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CustomFieldValue');

        if (!$this->TableExists(TABLE_PREFIX . 'customfieldvalues')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldvalues");
        }

        $_count = 0;

        $_customFieldValueContainer = $_oldUserIDList = $_oldTicketIDList = $_oldTicketTimeTrackIDList = $_customFieldIDList = $_customFieldContainer = $_customFieldOptionContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT customfieldgroups.grouptype, customfieldvalues.* FROM " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues
            LEFT JOIN " . TABLE_PREFIX . "customfields AS customfields ON (customfields.customfieldid = customfieldvalues.customfieldid)
            LEFT JOIN " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups ON (customfieldgroups.customfieldgroupid = customfields.customfieldgroupid)
            ORDER BY customfieldvalues.customfieldvalueid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_customFieldID = $this->GetImportManager()->GetImportRegistry()->GetKey('customfield', $this->DatabaseImport->Record['customfieldid']);
            if ($_customFieldID == false) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Value due to non existant custom field (' . (int)($this->DatabaseImport->Record['customfieldid']) . ')', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            if (empty($this->DatabaseImport->Record['grouptype'])) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Value due to non existant group type (' . (int)($this->DatabaseImport->Record['customfieldid']) . ')', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }


            $_groupType = false;

            // User Registration
            if ($this->DatabaseImport->Record['grouptype'] == '1') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_USER;

                $_oldUserIDList[] = $this->DatabaseImport->Record['typeid'];

                // User Groups
            } elseif ($this->DatabaseImport->Record['grouptype'] == '2') {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Link as user group type is no longer supported: ' . htmlspecialchars($this->DatabaseImport->Record['typeid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
//                $_groupType = SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION;

                // Staff Ticket Creation
            } elseif ($this->DatabaseImport->Record['grouptype'] == '3') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_STAFFTICKET;

                $_oldTicketIDList[] = $this->DatabaseImport->Record['typeid'];

                // User Ticket Creation
            } elseif ($this->DatabaseImport->Record['grouptype'] == '4') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_USERTICKET;

                $_oldTicketIDList[] = $this->DatabaseImport->Record['typeid'];

                // Staff & User Ticket Creation
            } elseif ($this->DatabaseImport->Record['grouptype'] == '9') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET;

                $_oldTicketIDList[] = $this->DatabaseImport->Record['typeid'];

                // Ticket Time Tracking
            } elseif ($this->DatabaseImport->Record['grouptype'] == '5') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_TIMETRACK;

                $_oldTicketTimeTrackIDList[] = $this->DatabaseImport->Record['typeid'];

            } else {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Value due to unsupported group type: ' . htmlspecialchars($this->DatabaseImport->Record['typeid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_customFieldValueContainer[$this->DatabaseImport->Record['customfieldvalueid']] = $this->DatabaseImport->Record;
            $_customFieldValueContainer[$this->DatabaseImport->Record['customfieldvalueid']]['grouptype'] = $_groupType;
            $_customFieldValueContainer[$this->DatabaseImport->Record['customfieldvalueid']]['customfieldid'] = $_customFieldID;

            if (!in_array($_customFieldID, $_customFieldIDList)) {
                $_customFieldIDList[] = $_customFieldID;
            }
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_customFieldOptionContainer[$this->Database->Record['customfieldid']][$this->Database->Record['optionvalue']] = $this->Database->Record['customfieldoptionid'];
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfields WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_customFieldContainer[$this->Database->Record['customfieldid']] = $this->Database->Record;
        }

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);
        $_newTicketTimeTrackIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('timetrack', $_oldTicketTimeTrackIDList);

        $_customFieldValueUpdate = array();

        foreach ($_customFieldValueContainer as $_customFieldValueID => $_customFieldValue) {
            $_linkTypeID = false;

            if ($_customFieldValue['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USER && isset($_newUserIDList[$_customFieldValue['typeid']])) {
                $_linkTypeID = $_newUserIDList[$_customFieldValue['typeid']];
            } elseif ($_customFieldValue['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFTICKET || $_customFieldValue['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERTICKET || $_customFieldValue['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET) {
                $_linkTypeID = $_customFieldValue['typeid'];
            } elseif ($_customFieldValue['grouptype'] == SWIFT_CustomFieldGroup::GROUP_TIMETRACK && isset($_newTicketTimeTrackIDList[$_customFieldValue['typeid']])) {
                $_linkTypeID = $_newTicketTimeTrackIDList[$_customFieldValue['typeid']];
            }

            if (empty($_linkTypeID)) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Value due to Invalid Link Type ID: ' . htmlspecialchars($_customFieldValue['typeid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_customField = $_customFieldContainer[$_customFieldValue['customfieldid']];
            $_customFieldID = $_customFieldValue['customfieldid'];
            $_extendedID = $_customFieldID . '-' . $_linkTypeID;

            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_RADIO || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT ||
                $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
                if (isset($_customFieldOptionContainer[$_customFieldValue['customfieldid']][$_customFieldValue['fieldvalue']])) {
                    if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
                        if (!isset($_customFieldValueUpdate[$_extendedID])) {
                            $_customFieldValueUpdate[$_extendedID] = $_customFieldValue;
                            $_customFieldValueUpdate[$_extendedID]['options'] = array();
                            $_customFieldValueUpdate[$_extendedID]['linktypeid'] = $_linkTypeID;
                        }

                        $_customFieldValueUpdate[$_extendedID]['options'][] = $_customFieldOptionContainer[$_customFieldValue['customfieldid']][$_customFieldValue['fieldvalue']];

                        continue;
                    } else {
                        $_customFieldValue['fieldvalue'] = $_customFieldOptionContainer[$_customFieldValue['customfieldid']][$_customFieldValue['fieldvalue']];
                    }
                } else {
                    $this->GetImportManager()->AddToLog('Ignoring Custom Field Value due to Invalid Option Value: ' . htmlspecialchars($_customFieldValue['typeid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                    continue;
                }
            }

            $this->GetImportManager()->AddToLog('Importing Custom Field Value ' . htmlspecialchars($_customFieldValue['customfieldid']) . ' (Custom Field) <> ' . htmlspecialchars($_customFieldValue['typeid']) . ' (Link Type ID)', SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldvalues',
                array('customfieldid' => (int)($_customFieldValue['customfieldid']), 'typeid' => $_linkTypeID,
                    'fieldvalue' => $_customFieldValue['fieldvalue'], 'isserialized' => 0,
                    'dateline' => DATENOW, 'uniquehash' => BuildHash()), 'INSERT');
        }

        foreach ($_customFieldValueUpdate as $_customFieldValueID => $_customFieldValue) {
            $this->GetImportManager()->AddToLog('Importing Custom Field Value ' . htmlspecialchars($_customFieldValue['customfieldid']) . ' (Custom Field) <> ' . htmlspecialchars($_customFieldValue['typeid']) . ' (Link Type ID)', SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldvalues',
                array('customfieldid' => (int)($_customFieldValue['customfieldid']), 'typeid' => (int)($_customFieldValue['linktypeid']),
                    'fieldvalue' => serialize($_customFieldValue['options']), 'isserialized' => '1',
                    'dateline' => DATENOW, 'uniquehash' => BuildHash()), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "customfieldvalues");
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

        return 1000;
    }
}

?>
