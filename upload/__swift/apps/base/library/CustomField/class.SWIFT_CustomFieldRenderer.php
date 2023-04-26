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

namespace Base\Library\CustomField;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Models\CustomField\SWIFT_CustomField;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;

/**
 * The Base Custom Field Renderer Lib
 *
 * @author Varun Shoor
 */
abstract class SWIFT_CustomFieldRenderer extends SWIFT_Library
{
    const TYPE_STATIC = 1;
    const TYPE_FIELDS = 2;

    static protected $_valueCache = array();

    /**
     * Check to see if its a valid render type
     *
     * @author Varun Shoor
     * @param mixed $_renderType The Render Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidRenderType($_renderType)
    {
        if ($_renderType == self::TYPE_FIELDS || $_renderType == self::TYPE_STATIC) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the values
     *
     * @author Varun Shoor
     * @param array $_customFieldIDList The Custom Field ID List
     * @param array $_customFieldContainer The Custom Field Container
     * @param int $_linkTypeID The Link Type ID
     * @return array The Values Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetValues($_customFieldIDList, $_customFieldContainer, $_linkTypeID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_customFieldIDList)) {
            return array();
        }

        $_customFieldINList = BuildIN($_customFieldIDList);
        $_customFieldHash = md5($_customFieldINList);

        if (!empty($_linkTypeID) && isset(self::$_valueCache[$_customFieldHash][$_linkTypeID])) {
            return self::$_valueCache[$_customFieldHash][$_linkTypeID];
        }

        $_customFieldValueContainer = $_rawCustomFieldValueContainer = array();
        foreach ($_customFieldContainer as $_customFieldID => $_customField) {
            if (!isset($_POST[$_customField['fieldname']])) {
                continue;
            }

            /**
             * BUG Fix: Nidhi Gupta <nidhi.gupta@kayako.com>
             *
             * SWIFT-3290 'Live Chat - Pre Chat' type custom fields cannot be edited by staff
             *
             * Comments: If post is empty then get values from database (ex. In case of keeping old file)
             */
            if (!empty($_POST[$_customField['fieldname']])) {
                $_customFieldValueContainer[$_customFieldID] = $_POST[$_customField['fieldname']];
            } else {
                $this->Database->Query("SELECT fieldvalue FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . $_customFieldINList . ")");
            }
        }

        if (!empty($_linkTypeID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . $_customFieldINList . ") AND typeid = '" . $_linkTypeID . "'");
            while ($this->Database->NextRecord()) {

                $_rawCustomFieldValueContainer[$this->Database->Record['customfieldid']] = $this->Database->Record;

                // If we already have data set from POST request then we continue as is
                if (isset($_customFieldValueContainer[$this->Database->Record['customfieldid']])) {
                    continue;
                }

                $_fieldValue = '';
                if ($this->Database->Record['isencrypted'] == '1') {
                    $_fieldValue = SWIFT_CustomFieldManager::Decrypt($this->Database->Record['fieldvalue']);
                } else {
                    $_fieldValue = $this->Database->Record['fieldvalue'];
                }

                if ($this->Database->Record['isserialized'] == '1') {
                    $_fieldValue = mb_unserialize($_fieldValue);
                }

                $_customFieldValueContainer[$this->Database->Record['customfieldid']] = $_fieldValue;
            }
        }

        $_returnContainer = array($_customFieldValueContainer, $_rawCustomFieldValueContainer);
        self::$_valueCache[$_customFieldHash][$_linkTypeID] = $_returnContainer;

        return $_returnContainer;
    }

    /**
     * Retrieve the values
     *
     * @author Werner Garcia
     * @param array $_customFieldIDList The Custom Field ID List
     * @param array $_customFieldContainer The Custom Field Container
     * @param SWIFT_TicketWorkflow|null $_SWIFT_TicketWorkflowObject
     * @return array The Values Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetWorkflowValues($_customFieldIDList, $_customFieldContainer, SWIFT_TicketWorkflow $_SWIFT_TicketWorkflowObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_customFieldIDList)) {
            return array();
        }
        $_ticketWorkflowID = ($_SWIFT_TicketWorkflowObject !== null)? $_SWIFT_TicketWorkflowObject->GetTicketWorkflowID() : 0;
        $_customFieldINList = BuildIN($_customFieldIDList);
        $_customFieldHash = md5($_customFieldINList);

        if (!empty($_ticketWorkflowID) && isset(self::$_valueCache[$_customFieldHash][$_ticketWorkflowID])) {
            return self::$_valueCache[$_customFieldHash][$_ticketWorkflowID];
        }

        $_customFieldValueContainer = $_rawCustomFieldValueContainer = array();
        foreach ($_customFieldContainer as $_customFieldID => $_customField) {
            if (!isset($_POST[$_customField['fieldname']])) {
                continue;
            }

            if (!empty($_POST[$_customField['fieldname']])) {
                $_customFieldValueContainer[$_customFieldID] = $_POST[$_customField['fieldname']];
            }
        }

        if (!empty($_ticketWorkflowID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldworkflowvalues WHERE customfieldid IN (" . $_customFieldINList . ") AND ticketworkflowid = '" . ($_ticketWorkflowID) . "'");
            while ($this->Database->NextRecord()) {

                $_rawCustomFieldValueContainer[$this->Database->Record['customfieldid']] = $this->Database->Record;

                // If we already have data set from POST request then we continue as is
                if (isset($_customFieldValueContainer[$this->Database->Record['customfieldid']])) {
                    continue;
                }

                $_fieldValue = '';
                if ($this->Database->Record['isencrypted'] == '1') {
                    $_fieldValue = SWIFT_CustomFieldManager::Decrypt($this->Database->Record['fieldvalue']);
                } else {
                    $_fieldValue = $this->Database->Record['fieldvalue'];
                }

                if ($this->Database->Record['isserialized'] == '1') {
                    $_fieldValue = mb_unserialize($_fieldValue);
                }

                $_customFieldValueContainer[$this->Database->Record['customfieldid']] = $_fieldValue;
            }
        }

        $_returnContainer = array($_customFieldValueContainer, $_rawCustomFieldValueContainer);

        if (!empty($_ticketWorkflowID)) {
            self::$_valueCache[$_customFieldHash][$_ticketWorkflowID] = $_returnContainer;
        }

        return $_returnContainer;
    }

    /**
     * Retrieve the options container array for a given custom field
     *
     * @author Varun Shoor
     * @param array $_customField The Custom Field Container
     * @param array $_customFieldValueContainer The Custom Field Value Container
     * @return array The Options Container Array
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function GetOptions($_customField, $_customFieldValueContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_customField['fieldtype'] != SWIFT_CustomField::TYPE_CHECKBOX && $_customField['fieldtype'] != SWIFT_CustomField::TYPE_RADIO &&
            $_customField['fieldtype'] != SWIFT_CustomField::TYPE_SELECT && $_customField['fieldtype'] != SWIFT_CustomField::TYPE_SELECTLINKED &&
            $_customField['fieldtype'] != SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_customFieldID = $_customField['customfieldid'];

        $_optionsContainer = array();
        if (isset($_customField['_options'])) {
            $_index = 0;

            foreach ($_customField['_options'] as $_customFieldOptionID => $_customFieldOption) {
                $_optionsContainer[$_index]['title'] = $_customFieldOption['optionvalue'];
                $_optionsContainer[$_index]['value'] = $_customFieldOptionID;

                if (isset($_customFieldOption['parentcustomfieldoptionid'])) {
                    $_optionsContainer[$_index]['parent'] = $_customFieldOption['parentcustomfieldoptionid'];
                }
                $_optionsContainer[$_index]['stored'] = false;

                // Do we have stored values?
                if (isset($_customFieldValueContainer[$_customFieldID])) {
                    // By default we will mark it as not selected
                    $_customFieldOption['isselected'] = false;
                    $_optionsContainer[$_index]['stored'] = true;

                    // Is it a linked select?
                    if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                        // Check for parent select comparison
                        if (isset($_customFieldValueContainer[$_customFieldID][0]) && isset($_customFieldOption['parentcustomfieldoptionid']) &&
                            $_customFieldOption['parentcustomfieldoptionid'] == 0 && $_customFieldOptionID == $_customFieldValueContainer[$_customFieldID][0]) {
                            $_customFieldOption['isselected'] = true;
                        } elseif (isset($_customFieldValueContainer[$_customFieldID][1][$_customFieldOption['parentcustomfieldoptionid']]) && isset($_customFieldOption['parentcustomfieldoptionid']) &&
                            $_customFieldOption['parentcustomfieldoptionid'] == $_customFieldValueContainer[$_customFieldID][0] &&
                            $_customFieldOptionID == $_customFieldValueContainer[$_customFieldID][1][$_customFieldOption['parentcustomfieldoptionid']]) {
                            $_customFieldOption['isselected'] = true;
                        }

                        // For multiple values
                    } elseif (($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) &&
                        _is_array($_customFieldValueContainer[$_customFieldID])) {
                        if (in_array($_customFieldOptionID, $_customFieldValueContainer[$_customFieldID])) {
                            $_customFieldOption['isselected'] = true;
                        }

                        // For all other fields
                    } else {
                        if ($_customFieldOptionID == $_customFieldValueContainer[$_customFieldID]) {
                            $_customFieldOption['isselected'] = true;
                        }
                    }
                }

                if ($_customFieldOption['isselected'] == true && ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_RADIO)) {
                    $_optionsContainer[$_index]['checked'] = true;
                } elseif ($_customFieldOption['isselected'] == true) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }
        }

        return $_optionsContainer;
    }

    /**
     * Render the Static Options
     *
     * @author Varun Shoor
     * @param array $_customField The Custom Field Container
     * @param array $_optionsContainer The Options Container
     * @param bool $_showControls
     * @return string The Static Options
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetStaticOptions($_customField, $_optionsContainer, $_showControls = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_optionsContainer)) {
            return '';
        }

        $_index = $_linkedParent = 0;
        $_staticOptionsList = $_fieldList = $_linkedList = array();
        $_pre = $_post = '';

        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT ||
            $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED ||
            $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
            $_pre = '<select readonly="readonly" disabled="disabled" size="'.(count($_optionsContainer)>5?'5':count($_optionsContainer)).'" style="height: 100%;" multiple="multiple" class="swiftselect">';
        }

        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT ||
            $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED ||
            $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
            $_post = '</select>';
        }

        foreach ($_optionsContainer as $_option) {
            if ($_option['stored'] != true) {
                continue;
            }

            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX) {
                $_fieldList[] = '<input type="checkbox" '.((isset($_option['checked']) && $_option['checked'] == true)?  'checked="checked"' : '').' readonly="readonly" disabled="disabled"/> ' . htmlspecialchars($_option['title']);
            }

            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_RADIO) {
                $_fieldList[] = '<input type="radio" '.((isset($_option['checked']) && $_option['checked'] == true)?  'checked="checked"' : '').' readonly="readonly" disabled="disabled"/> ' . htmlspecialchars($_option['title']);
            }

            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT ||
                $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) {
                $_fieldList[] = '<option '.((isset($_option['selected']) && $_option['selected'])?  'selected="selected"' : '').'> ' . htmlspecialchars($_option['title']) . '</option>';
            }

            if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                if ((int)$_option['parent'] === 0) {
                    if (isset($_option['selected']) && $_option['selected']) {
                        $_linkedParent = $_option['value'];
                    }
                    $_fieldList[] = '<option ' . ((isset($_option['selected']) && $_option['selected']) ? 'selected="selected"' : '') . '> ' . htmlspecialchars($_option['title']) . '</option>';
                } else {
                    $_linkedList[] = $_option;
                }
            }

            if ((isset($_option['checked']) && $_option['checked'] == true) || (isset($_option['selected']) && $_option['selected'] == true)) {
                $_optionPrefix = '';
                if ($_option['parent'] != '0') {
                    $_optionPrefix = '<img src="' . SWIFT::Get('themepathimages') . 'linkdownarrow_blue.gif" align="absmiddle" border="0" /> ';
                    $_staticOptionsList[$_option['parent']] = $_optionPrefix . htmlspecialchars($_option['title']);
                } else {
                    $_staticOptionsList[$_index] = $_optionPrefix . htmlspecialchars($_option['title']);
                    $_index++;
                }
            }
        }

        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED && count($_linkedList)) {
            $_pre = '<select readonly="readonly" disabled="disabled" size="'.(count($_fieldList)>5?'5':count($_fieldList)).'" style="height: 100%;" multiple="multiple" class="swiftselect">';
            $_linkedList = array_filter($_linkedList, function ($op) use ($_linkedParent) {
                return $op['parent'] == $_linkedParent;
            });
            ksort($_linkedList);
            $_staticOptions = implode(PHP_EOL,
                array_map(function ($_option) {
                    return '<option ' . ((isset($_option['selected']) && $_option['selected']) ? 'selected="selected"' : '') . '> ' . htmlspecialchars($_option['title']) . '</option>';
                }, $_linkedList));
            $_post .= '<br/><img src="' . SWIFT::Get('themepathimages') . 'linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' . '<select readonly="readonly" disabled="disabled" size="'.(count($_linkedList)>5?'5':count($_linkedList)).'" style="height: 100%;" multiple="multiple" class="swiftselect">' . $_staticOptions . '</select>';
        }

        if ($_showControls) {
            if (count($_fieldList) === 0) {
                return '';
            }

            ksort($_fieldList);
            $_staticOptions = implode('<br/>', $_fieldList);

            return $_pre . $_staticOptions . $_post;
        }

        if (count($_staticOptionsList) === 0) {
            return '';
        }

        ksort($_staticOptionsList);
        return implode('<br/>', $_staticOptionsList);
    }
}
