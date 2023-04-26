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

namespace Base\Models\CustomField;

use SWIFT;
use SWIFT_App;
use Base\Library\CustomField\SWIFT_CustomField_Exception;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Models\CustomField\SWIFT_CustomFieldOption;
use Base\Models\CustomField\SWIFT_CustomFieldValue;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Loader;
use SWIFT_Model;
use Tickets\Models\View\SWIFT_TicketViewField;

/**
 * The Custom Field Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomField extends SWIFT_Model
{
    const TABLE_NAME = 'customfields';
    const PRIMARY_KEY = 'customfieldid';

    const TABLE_STRUCTURE = "customfieldid I PRIMARY AUTO NOTNULL,
                                customfieldgroupid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                fieldtype I2 DEFAULT '0' NOTNULL,
                                fieldname C(100) DEFAULT '' NOTNULL,
                                defaultvalue C(255) DEFAULT '' NOTNULL,
                                isrequired I2 DEFAULT '0' NOTNULL,
                                usereditable I2 DEFAULT '0' NOTNULL,
                                staffeditable I2 DEFAULT '0' NOTNULL,
                                regexpvalidate C(255) DEFAULT '' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                encryptindb I2 DEFAULT '0' NOTNULL,
                                description C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'customfieldgroupid';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_TEXT = 1;
    const TYPE_TEXTAREA = 2;
    const TYPE_PASSWORD = 3;
    const TYPE_CHECKBOX = 4;
    const TYPE_RADIO = 5;
    const TYPE_SELECT = 6;
    const TYPE_SELECTMULTIPLE = 7;
    const TYPE_CUSTOM = 8;
    const TYPE_SELECTLINKED = 9;
    const TYPE_DATE = 10;
    const TYPE_FILE = 11;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Record could not be loaded
     */
    public function __construct($_customFieldID)
    {
        parent::__construct();

        if (!$this->LoadData($_customFieldID)) {
            throw new SWIFT_CustomField_Exception('Failed to load Custom Field ID: ' . $_customFieldID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'customfields', $this->GetUpdatePool(), 'UPDATE', "customfieldid = '" . (int)($this->GetCustomFieldID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Custom Field ID
     *
     * @author Varun Shoor
     * @return mixed "customfieldid" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetCustomFieldID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['customfieldid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_customFieldID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfields WHERE customfieldid = '" . $_customFieldID . "'");
        if (isset($_dataStore['customfieldid']) && !empty($_dataStore['customfieldid'])) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Field Type
     *
     * @author Varun Shoor
     * @param mixed $_fieldType The Field Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_fieldType)
    {
        if ($_fieldType == self::TYPE_TEXT || $_fieldType == self::TYPE_TEXTAREA || $_fieldType == self::TYPE_PASSWORD || $_fieldType == self::TYPE_CHECKBOX || $_fieldType == self::TYPE_RADIO || $_fieldType == self::TYPE_SELECT || $_fieldType == self::TYPE_SELECTMULTIPLE || $_fieldType == self::TYPE_CUSTOM || $_fieldType == self::TYPE_SELECTLINKED || $_fieldType == self::TYPE_DATE || $_fieldType == self::TYPE_FILE) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Custom Field
     *
     * @author Varun Shoor
     * @param int $_customFieldGroupID The Custom Field Group ID
     * @param mixed $_fieldType The Field Type
     * @param string $_fieldTitle The Custom Field Title
     * @param string $_description The Field Description
     * @param string $_fieldName The Field Name (For HTML Forms)
     * @param string $_defaultValue The Field Default Value
     * @param int $_displayOrder The Field Display Order
     * @param bool $_isRequired Whether this field is required for form submission
     * @param bool $_userEditable Whether this field can be edited by users
     * @param bool $_staffEditable Whether this field can be edited by staff
     * @param string $_regularExpressionValidation The Regular Expression to Validate this field
     * @param array $_fieldOptionsContainer The Options Container. Example: array(optionValue, displayOrder, isSelected, Array(Linked Custom Field IDs), array(optionValue, displayOrder, isSelected, Array(Linked Custom Field IDs))
     * @param bool $_encryptInDB Whether to encrypt the value in database
     * @return mixed "SWIFT_CustomField" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_customFieldGroupID, $_fieldType, $_fieldTitle, $_description, $_fieldName, $_defaultValue, $_displayOrder, $_isRequired, $_userEditable,
                                  $_staffEditable, $_regularExpressionValidation, $_fieldOptionsContainer, $_encryptInDB = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customFieldGroupID) || !self::IsValidType($_fieldType) || empty($_fieldTitle) || empty($_fieldName) || !SWIFT_CustomFieldGroup::IsValidCustomFieldGroupID($_customFieldGroupID)) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA);
        }

        if ($_fieldType == self::TYPE_DATE && !empty($_defaultValue)) {
            $_defaultValue = GetCalendarDateline($_defaultValue);
        }

        $_fieldName = Clean($_fieldName);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfields', array('customfieldgroupid' => $_customFieldGroupID, 'title' => $_fieldTitle,
            'fieldtype' => (int)($_fieldType), 'fieldname' => $_fieldName, 'defaultvalue' => $_defaultValue, 'isrequired' => (int)($_isRequired),
            'usereditable' => (int)($_userEditable), 'staffeditable' => (int)($_staffEditable), 'regexpvalidate' => $_regularExpressionValidation,
            'displayorder' => $_displayOrder, 'description' => $_description, 'encryptindb' => (int)($_encryptInDB)), 'INSERT');
        $_customFieldID = $_SWIFT->Database->Insert_ID();
        if (!$_customFieldID) {
            throw new SWIFT_CustomField_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_CustomFieldObject = new SWIFT_CustomField($_customFieldID);
        if (!$_SWIFT_CustomFieldObject instanceof SWIFT_CustomField || !$_SWIFT_CustomFieldObject->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_CustomFieldObject->ProcessOptions($_fieldOptionsContainer);

        SWIFT_CustomFieldManager::RebuildCache();

        return $_SWIFT_CustomFieldObject;
    }

    /**
     * Update the Custom Field Record
     *
     * @author Varun Shoor
     * @param string $_fieldTitle The Custom Field Title
     * @param string $_description The Field Description
     * @param string $_fieldName The Field Name (For HTML Forms)
     * @param string $_defaultValue The Field Default Value
     * @param int $_displayOrder The Field Display Order
     * @param bool $_isRequired Whether this field is required for form submission
     * @param bool $_userEditable Whether this field can be edited by users
     * @param bool $_staffEditable Whether this field can be edited by staff
     * @param string $_regularExpressionValidation The Regular Expression to Validate this field
     * @param array $_fieldOptionsContainer The Options Container. Example: array(optionValue, displayOrder, isSelected, Array(Linked Custom Field IDs), array(optionValue, displayOrder, isSelected, Array(Linked Custom Field IDs))
     * @param bool $_encryptInDB Whether to encrypt the value in database
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_fieldTitle, $_description, $_fieldName, $_defaultValue, $_displayOrder, $_isRequired, $_userEditable, $_staffEditable, $_regularExpressionValidation,
                           $_fieldOptionsContainer, $_encryptInDB = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_fieldTitle) || empty($_fieldName)) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA);
        }

        if ($this->GetProperty('fieldtype') == self::TYPE_DATE && !empty($_defaultValue)) {
            $_defaultValue = GetCalendarDateline($_defaultValue);
        }

        $_fieldName = Clean($_fieldName);

        $this->UpdatePool('title', $_fieldTitle);
        $this->UpdatePool('fieldname', $_fieldName);
        $this->UpdatePool('defaultvalue', $_defaultValue);
        $this->UpdatePool('isrequired', (int)($_isRequired));
        $this->UpdatePool('usereditable', (int)($_userEditable));
        $this->UpdatePool('staffeditable', (int)($_staffEditable));
        $this->UPdatePool('regexpvalidate', $_regularExpressionValidation);
        $this->UpdatePool('displayorder', $_displayOrder);
        $this->UpdatePool('encryptindb', (int)($_encryptInDB));
        $this->UpdatePool('description', $_description);
        $this->ProcessUpdatePool();

        $this->ProcessOptions($_fieldOptionsContainer);

        SWIFT_CustomFieldManager::RebuildCache();

        return true;
    }

    /**
     * Process the Field Options
     *
     * @author Varun Shoor
     * @param array $_fieldOptionsContainer The Options Container. Example: array(optionValue, displayOrder, isSelected, Array(Linked Custom Field IDs), array(optionValue, displayOrder, isSelected, Array(Linked Custom Field IDs))
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessOptions($_fieldOptionsContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_customFieldOptionIDList = array();

        if (_is_array($_fieldOptionsContainer)) {
            // Iterate through field options
            foreach ($_fieldOptionsContainer as $_key => $_val) {
                if (isset($_val[0]) && $_val[0] != '') {

                    // Process the Display Order
                    $_displayOrder = 1;

                    if (isset($_val[1])) {
                        $_displayOrder = (int)($_val[1]);
                    }

                    // Process the Selected Flag
                    $_isSelected = false;
                    if (isset($_val[2]) && !empty($_val[2])) {
                        $_isSelected = true;
                    }

                    // Process the Linked Custom Field IDs
                    $_linkedCustomFieldIDList = array();
                    if (isset($_val[3]) && _is_array($_val[3])) {
                        $_linkedCustomFieldIDList = $_val[3];
                    }

                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                     *
                     * SWIFT-4359 Changing the ‘Display Order’ of child value of Linked Select unlink the values from tickets.
                     *
                     * Comments: In order to avoid overriding, track first parent and act accordingly.
                     */
                    $_SWIFT_CustomFieldOptionObject = false;
                    $_firstParent = false; // to avoid override first child

                    try {
                        $_SWIFT_CustomFieldOptionObject = new SWIFT_CustomFieldOption($_key);
                        /*
                         * BUG FIX - Ravi Sharma
                         *
                         * SWIFT-1541: Few values are missed while inserting/updating "Linked Select" custom field
                         *
                         * Comments: There was conflict in sub key.
                         */
                        if ($_SWIFT_CustomFieldOptionObject->GetProperty('parentcustomfieldoptionid') != 0 || $_SWIFT_CustomFieldOptionObject->GetProperty('customfieldid') != $this->GetCustomFieldID()) {
                            $_SWIFT_CustomFieldOptionObject = false;
                        }

                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                        $_firstParent = true;
                    }

                    $_customFieldOptionID = 0;
                    if ($_SWIFT_CustomFieldOptionObject instanceof SWIFT_CustomFieldOption && $_SWIFT_CustomFieldOptionObject->GetIsClassLoaded()) {
                        $_SWIFT_CustomFieldOptionObject->Update($_val[0], $_displayOrder, $_isSelected, 0, $_linkedCustomFieldIDList);
                        $_customFieldOptionID = $_SWIFT_CustomFieldOptionObject->GetCustomFieldOptionID();
                    } else {
                        $_customFieldOptionID = SWIFT_CustomFieldOption::Create($this->GetCustomFieldID(), $_val[0], $_displayOrder, $_isSelected, 0, $_linkedCustomFieldIDList);
                    }
                    $_customFieldOptionIDList[] = $_customFieldOptionID;

                    if (!$_customFieldOptionID) {
                        throw new SWIFT_CustomField_Exception(SWIFT_CREATEFAILED);
                    }

                    if ($this->GetProperty('fieldtype') == self::TYPE_SELECTLINKED && isset($_val[4]) && _is_array($_val[4])) {
                        foreach ($_val[4] as $_subKey => $_subVal) {
                            if (!isset($_subVal[0]) || $_subVal[0] == '') {
                                continue;
                            }

                            $_displayOrder = 1;
                            if (isset($_subVal[1])) {
                                $_displayOrder = (int)($_subVal[1]);
                            }

                            $_isSelected = false;
                            if (isset($_subVal[2]) && !empty($_subVal[2])) {
                                $_isSelected = true;
                            }

                            $_linkedCustomFieldIDList = array();
                            if (isset($_subVal[3]) && _is_array($_subVal[3])) {
                                $_linkedCustomFieldIDList = $_subVal[3];
                            }


                            $_SWIFT_CustomFieldOptionObject_Sub = false;
                            try {
                                $_SWIFT_CustomFieldOptionObject_Sub = new SWIFT_CustomFieldOption($_subKey);

                                /*
                                 * BUG FIX - Ravi Sharma
                                 *
                                 * SWIFT-1541: Few values are missed while inserting/updating "Linked Select" custom field
                                 *
                                 * Comments: There was conflict in sub key.
                                 */
                                if (($_SWIFT_CustomFieldOptionObject_Sub->GetProperty('parentcustomfieldoptionid') != $_key) || $_firstParent || $_SWIFT_CustomFieldOptionObject_Sub->GetProperty('customfieldid') != $this->GetCustomFieldID()) {
                                    $_SWIFT_CustomFieldOptionObject_Sub = false;
                                }

                            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                            }

                            $_subCustomFieldOptionID = 0;
                            if ($_SWIFT_CustomFieldOptionObject_Sub instanceof SWIFT_CustomFieldOption && $_SWIFT_CustomFieldOptionObject_Sub->GetIsClassLoaded()) {
                                $_SWIFT_CustomFieldOptionObject_Sub->Update($_subVal[0], $_displayOrder, $_isSelected, $_customFieldOptionID, $_linkedCustomFieldIDList);
                                $_subCustomFieldOptionID = $_SWIFT_CustomFieldOptionObject_Sub->GetCustomFieldOptionID();
                            } else {
                                $_subCustomFieldOptionID = SWIFT_CustomFieldOption::Create($this->GetCustomFieldID(), $_subVal[0], $_displayOrder, $_isSelected, $_customFieldOptionID, $_linkedCustomFieldIDList);
                            }
                            $_customFieldOptionIDList[] = $_subCustomFieldOptionID;

                            if (!$_subCustomFieldOptionID) {
                                throw new SWIFT_CustomField_Exception(SWIFT_CREATEFAILED);
                            }

                        }
                    }
                }
            }
        }

        // Cleanup
        $_finalCustomFieldOptionIDList = $_deleteCustomFieldOptionIDList = array();
        $this->Database->Query("SELECT customfieldoptionid FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldid = '" . (int)($this->GetCustomFieldID()) . "'");
        while ($this->Database->NextRecord()) {
            $_finalCustomFieldOptionIDList[] = $this->Database->Record['customfieldoptionid'];
        }

        foreach ($_finalCustomFieldOptionIDList as $_customFieldOptionID) {
            if (!in_array($_customFieldOptionID, $_customFieldOptionIDList)) {
                $_deleteCustomFieldOptionIDList[] = $_customFieldOptionID;
            }
        }

        if (count($_deleteCustomFieldOptionIDList)) {
            SWIFT_CustomFieldOption::DeleteList($_deleteCustomFieldOptionIDList);
        }

        return true;
    }

    /**
     * Return the Custom Field options Container
     *
     * @author Varun Shoor
     * @return mixed "_customFieldOptionsContainer" ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOptionsContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_parentCustomFieldOptionIDList = $_customFieldOptionsContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldid = '" . (int)($this->GetCustomFieldID()) . "' AND parentcustomfieldoptionid = '0' ORDER BY displayorder ASC");
        while ($this->Database->NextRecord()) {
            $_customFieldOptionsContainer[$this->Database->Record['customfieldoptionid']] = $this->Database->Record;
            $_customFieldOptionsContainer[$this->Database->Record['customfieldoptionid']]['suboptions'] = array();
            $_parentCustomFieldOptionIDList[] = $this->Database->Record['customfieldoptionid'];
        }

        if (count($_parentCustomFieldOptionIDList)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions WHERE parentcustomfieldoptionid IN (" . BuildIN($_parentCustomFieldOptionIDList) . ") ORDER BY displayorder ASC");
            while ($this->Database->NextRecord()) {
                $_customFieldOptionsContainer[$this->Database->Record['parentcustomfieldoptionid']]['suboptions'][$this->Database->Record['customfieldoptionid']] = $this->Database->Record;
            }
        }

        return $_customFieldOptionsContainer;
    }

    /**
     * Delete the Custom Field record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetCustomFieldID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Custom Fields
     *
     * @author Varun Shoor
     * @param array $_customFieldIDList The Custom Field ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldIDList)) {
            return false;
        }

        $_finalCustomFieldIDList = array();
        $_index = 1;
        $_fileIDList = $_fileCustomFieldValueIDList = array();

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT customfields.*, customfieldgroups.title AS grouptitle FROM " . TABLE_PREFIX . "customfields AS customfields
            LEFT JOIN " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid)
                WHERE customfields.customfieldid IN (" . BuildIN($_customFieldIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_fieldType = $_SWIFT->Database->Record['fieldtype'];

            if ($_fieldType == SWIFT_CustomField::TYPE_FILE) {
                $_fileCustomFieldValueIDList[] = $_SWIFT->Database->Record['customfieldid'];
            }

            $_fieldLabelContainer = self::GetFieldLabel($_fieldType);

            $_finalText .= $_index . ". <img src='" . SWIFT::Get('themepath') . 'images/' . $_fieldLabelContainer[1] . "' align='absmiddle' border='0' /> " . htmlspecialchars($_SWIFT->Database->Record['title']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['grouptitle']) . ')<br>';
            $_finalCustomFieldIDList[] = $_SWIFT->Database->Record['customfieldid'];

            $_index++;
        }

        if (!count($_finalCustomFieldIDList)) {
            return false;
        }

        // Do we need to clear the file records?
        if (count($_fileCustomFieldValueIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_fileCustomFieldValueIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_fileIDList[] = $_SWIFT->Database->Record['fieldvalue'];
            }

            // Clear the file records + files from the directory
            if (count($_fileIDList)) {
                SWIFT_FileManager::DeleteList($_fileIDList);
            }
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelcfields'), count($_finalCustomFieldIDList)), sprintf($_SWIFT->Language->Get('msgdelcfields'), $_finalText));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfields WHERE customfieldid IN (" . BuildIN($_finalCustomFieldIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_finalCustomFieldIDList) . ")");

        SWIFT_CustomFieldOption::DeleteOnCustomField($_finalCustomFieldIDList);

        // Clear the Custom Field Values
        SWIFT_CustomFieldValue::DeleteOnCustomField($_finalCustomFieldIDList);

        // Clean up linked values
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadModel('View:TicketViewField', APP_TICKETS);
            SWIFT_TicketViewField::DeleteOnCustomFieldTypeAndID(SWIFT_TicketViewField::TYPE_CUSTOM, $_finalCustomFieldIDList);
        }

        SWIFT_CustomFieldManager::RebuildCache();

        return true;
    }

    /**
     * Delete Custom Fields based on Custom Field Group ID list
     *
     * @author Varun Shoor
     * @param array $_customFieldGroupIDList The Custom Field Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnCustomFieldGroup($_customFieldGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldGroupIDList)) {
            return false;
        }

        $_customFieldIDList = array();
        $_SWIFT->Database->Query("SELECT customfieldid FROM " . TABLE_PREFIX . "customfields WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldIDList[] = $_SWIFT->Database->Record['customfieldid'];
        }

        if (!count($_customFieldIDList)) {
            return false;
        }

        self::DeleteList($_customFieldIDList);

        return true;
    }

    /**
     * Gets the Field Label
     *
     * @author Varun Shoor
     * @param mixed $_fieldType The Field Type
     * @return array Array(Title, Icon)
     */
    public static function GetFieldLabel($_fieldType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_fieldType)) {
            return array('', '', '');
        }

        $_typeTitle = $_typeDescription = $_typeIcon = '';

        if ($_fieldType == self::TYPE_TEXT) {
            $_typeTitle = $_SWIFT->Language->Get('field_text');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_text');
            $_typeIcon = 'icon_cfeditbox.gif';
        } else if ($_fieldType == self::TYPE_TEXTAREA) {
            $_typeTitle = $_SWIFT->Language->Get('field_textarea');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_textarea');
            $_typeIcon = 'icon_cfeditbox.gif';
        } else if ($_fieldType == self::TYPE_PASSWORD) {
            $_typeTitle = $_SWIFT->Language->Get('field_password');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_password');
            $_typeIcon = 'icon_cfpassword.gif';
        } else if ($_fieldType == self::TYPE_CHECKBOX) {
            $_typeTitle = $_SWIFT->Language->Get('field_checkbox');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_checkbox');
            $_typeIcon = 'icon_cfcheckbox.gif';
        } else if ($_fieldType == self::TYPE_RADIO) {
            $_typeTitle = $_SWIFT->Language->Get('field_radio');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_radio');
            $_typeIcon = 'icon_cfradio.gif';
        } else if ($_fieldType == self::TYPE_SELECT) {
            $_typeTitle = $_SWIFT->Language->Get('field_select');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_select');
            $_typeIcon = 'icon_cfselect.gif';
        } else if ($_fieldType == self::TYPE_SELECTMULTIPLE) {
            $_typeTitle = $_SWIFT->Language->Get('field_selectmultiple');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_selectmultiple');
            $_typeIcon = 'icon_cfselectmultiple.gif';
        } else if ($_fieldType == self::TYPE_SELECTLINKED) {
            $_typeIcon = 'icon_cflinkedselect.gif';
            $_typeTitle = $_SWIFT->Language->Get('field_linkedselect');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_linkedselect');
        } else if ($_fieldType == self::TYPE_DATE) {
            $_typeIcon = 'icon_cfdate.gif';
            $_typeTitle = $_SWIFT->Language->Get('field_date');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_date');
        } else if ($_fieldType == self::TYPE_FILE) {
            $_typeIcon = 'icon_cffile.gif';
            $_typeTitle = $_SWIFT->Language->Get('field_file');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_file');
        } else if ($_fieldType == self::TYPE_CUSTOM) {
            $_typeTitle = $_SWIFT->Language->Get('field_custom');
            $_typeDescription = $_SWIFT->Language->Get('desc_field_custom');
            $_typeIcon = 'icon_cfcustom.gif';
        }

        return array($_typeTitle, $_typeIcon, $_typeDescription);
    }

    /**
     * Returns the list of applicable Custom Fields
     *
     * @author Varun Shoor
     * @return mixed "_returnFieldList" (ARRAY) on Success, "false" otherwise
     */
    public static function GetFieldList()
    {
        $_fieldList = array(self::TYPE_TEXT, self::TYPE_TEXTAREA, self::TYPE_PASSWORD, self::TYPE_CHECKBOX, self::TYPE_RADIO, self::TYPE_SELECT, self::TYPE_SELECTMULTIPLE, self::TYPE_SELECTLINKED, self::TYPE_DATE, self::TYPE_FILE, self::TYPE_CUSTOM);

        $_returnFieldList = array();
        foreach ($_fieldList as $_key => $_val) {
            $_labelContainer = self::GetFieldLabel($_val);

            if (!isset($_labelContainer[0]) || !isset($_labelContainer[1]) || !isset($_labelContainer[2])) {
                continue;
            }

            $_returnFieldList[] = array($_val, $_labelContainer[0], $_labelContainer[2], $_labelContainer[1]);
        }

        return $_returnFieldList;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_customFieldIDSortList The Custom Field ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_customFieldIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldIDSortList)) {
            return false;
        }

        foreach ($_customFieldIDSortList as $_customFieldID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfields', array('displayorder' => $_displayOrder), 'UPDATE',
                "customfieldid = '" . $_customFieldID . "'");
        }

        SWIFT_CustomFieldManager::RebuildCache();

        return true;
    }
}

?>
