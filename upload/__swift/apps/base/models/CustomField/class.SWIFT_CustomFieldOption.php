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
use Base\Library\CustomField\SWIFT_CustomField_Exception;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Custom Field Options (SELECT/RADIO etc.) Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldOption extends SWIFT_Model
{
    const TABLE_NAME = 'customfieldoptions';
    const PRIMARY_KEY = 'customfieldoptionid';

    const TABLE_STRUCTURE = "customfieldoptionid I PRIMARY AUTO NOTNULL,
                                customfieldid I DEFAULT '0' NOTNULL,
                                optionvalue C(255) DEFAULT '' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                isselected I2 DEFAULT '0' NOTNULL,
                                parentcustomfieldoptionid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'customfieldid';
    const INDEX_2 = 'parentcustomfieldoptionid';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_customFieldOptionID The Custom Field Option ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Record could not be loaded
     */
    public function __construct($_customFieldOptionID)
    {
        parent::__construct();

        if (!$this->LoadData($_customFieldOptionID)) {
            throw new SWIFT_CustomField_Exception('Failed to load Custom Field ID: ' . $_customFieldOptionID);
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldoptions', $this->GetUpdatePool(), 'UPDATE', "customfieldoptionid = '" . (int)($this->GetCustomFieldOptionID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Custom Field Option ID
     *
     * @author Varun Shoor
     * @return mixed "customfieldoptionid" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If the Class is not Loaded
     */
    public function GetCustomFieldOptionID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['customfieldoptionid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_customFieldOptionID The Custom Field Option ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_customFieldOptionID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldoptionid = '" . $_customFieldOptionID . "'");
        if (isset($_dataStore['customfieldoptionid']) && !empty($_dataStore['customfieldoptionid'])) {
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
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_CustomField_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Custom Field Option
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @param string $_optionValue The Option Value
     * @param int $_displayOrder The Display Order
     * @param bool $_isSelected Whether this Option is selected by default
     * @param int $_parentCustomFieldOptionID (OPTIONAL) The Parent Custom Field Option ID
     * @param array $_customFieldOptionLinkContainer (OPTIONAL) The list of custom field id's this option is linked to (if any)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CustomField_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_customFieldID, $_optionValue, $_displayOrder, $_isSelected = false, $_parentCustomFieldOptionID = 0, $_customFieldOptionLinkContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customFieldID) || empty($_optionValue)) {
            throw new SWIFT_CustomField_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldoptions', array('parentcustomfieldoptionid' => $_parentCustomFieldOptionID, 'customfieldid' => $_customFieldID, 'optionvalue' => $_optionValue, 'displayorder' => $_displayOrder, 'isselected' => (int)($_isSelected)), 'INSERT');
        $_customFieldOptionID = $_SWIFT->Database->Insert_ID();

        if (!$_customFieldOptionID) {
            throw new SWIFT_CustomField_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_customFieldOptionLinkContainer)) {
            foreach ($_customFieldOptionLinkContainer as $_key => $_val) {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldoptionlinks', array('customfieldoptionid' => $_customFieldOptionID, 'customfieldid' => (int)($_val)), 'INSERT');
            }
        }

        return $_customFieldOptionID;
    }

    /**
     * Update a Custom Field Option Value
     *
     * @author Varun Shoor
     * @param string $_optionValue The Option Value
     * @param int $_displayOrder The Display Order
     * @param bool $_isSelected Whether this Option is selected by default
     * @param int $_parentCustomFieldOptionID (OPTIONAL) The Parent Custom Field Option ID
     * @param array $_customFieldOptionLinkContainer (OPTIONAL) The list of custom field id's this option is linked to (if any)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Update($_optionValue, $_displayOrder, $_isSelected = false, $_parentCustomFieldOptionID = 0, $_customFieldOptionLinkContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('optionvalue', $_optionValue);
        $this->UpdatePool('displayorder', $_displayOrder);
        $this->UpdatePool('isselected', (int)($_isSelected));
        $this->UpdatePool('parentcustomfieldoptionid', $_parentCustomFieldOptionID);
        $this->ProcessUpdatePool();

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldoptionlinks WHERE customfieldoptionid = '" . (int)($this->GetCustomFieldOptionID()) . "'");

        if (_is_array($_customFieldOptionLinkContainer)) {
            foreach ($_customFieldOptionLinkContainer as $_key => $_val) {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldoptionlinks', array('customfieldoptionid' => (int)($this->GetCustomFieldOptionID()), 'customfieldid' => (int)($_val)), 'INSERT');
            }
        }

        return true;
    }

    /**
     * Delete the Custom Field Option record
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

        self::DeleteList(array($this->GetCustomFieldOptionID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Custom Field options
     *
     * @author Varun Shoor
     * @param array $_customFieldOptionIDList The Custom Field Option ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldOptionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldOptionIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldoptionid IN (" . BuildIN($_customFieldOptionIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldoptionlinks WHERE customfieldoptionid IN (" . BuildIN($_customFieldOptionIDList) . ")");

        return true;
    }

    /**
     * Delete Custom Field Options based on Custom Field ID List
     *
     * @author Varun Shoor
     * @param array $_customFieldIDList The Custom Field ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnCustomField($_customFieldIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldIDList)) {
            return false;
        }

        $_customFieldOptionIDList = array();

        $_SWIFT->Database->Query("SELECT customfieldoptionid FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldOptionIDList[] = $_SWIFT->Database->Record['customfieldoptionid'];
        }

        if (!count($_customFieldOptionIDList)) {
            return false;
        }

        self::DeleteList($_customFieldOptionIDList);

        return true;
    }
}

?>
