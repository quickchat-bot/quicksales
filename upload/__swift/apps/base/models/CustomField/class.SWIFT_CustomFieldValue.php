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
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Custom Field Value Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldValue extends SWIFT_Model
{
    const TABLE_NAME = 'customfieldvalues';
    const PRIMARY_KEY = 'customfieldvalueid';

    const TABLE_STRUCTURE = "customfieldvalueid I PRIMARY AUTO NOTNULL,
                                customfieldid I DEFAULT '0' NOTNULL,
                                typeid I DEFAULT '0' NOTNULL,
                                fieldvalue X NOTNULL,
                                isserialized I2 DEFAULT '0' NOTNULL,
                                isencrypted I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                uniquehash C(100) DEFAULT '' NOTNULL,
                                lastupdated I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'customfieldid, typeid';
    const INDEX_2 = 'uniquehash';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Custom Field Value Object');
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldvalues', $this->GetUpdatePool(), 'UPDATE', "customfieldvalueid = '" . (int)($this->GetCustomFieldValueID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Custom Field Value ID
     *
     * @author Varun Shoor
     * @return mixed "customfieldvalueid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCustomFieldValueID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['customfieldvalueid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldvalueid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['customfieldvalueid']) && !empty($_dataStore['customfieldvalueid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['customfieldvalueid']) || empty($this->_dataStore['customfieldvalueid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Custom Field Value Record
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @param int $_typeID The Type ID
     * @param string $_fieldValue The Field Value
     * @param bool $_isSerialized Whether the value is serialized
     * @param bool $_isEncrypted Whether the value is encrypted
     * @return int The Custom Field Value ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_customFieldID, $_typeID, $_fieldValue, $_isSerialized, $_isEncrypted)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customFieldID) || empty($_typeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldvalues', array('customfieldid' => $_customFieldID, 'typeid' => $_typeID, 'fieldvalue' => $_fieldValue,
            'dateline' => DATENOW, 'lastupdated' => '0', 'isserialized' => (int)($_isSerialized), 'isencrypted' => (int)($_isEncrypted), 'uniquehash' => BuildHash()), 'INSERT');
        $_customFieldValueID = $_SWIFT->Database->Insert_ID();

        if (!$_customFieldValueID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_customFieldValueID;
    }

    /**
     * Update the Custom Field Value Record
     *
     * @author Varun Shoor
     * @param string $_fieldValue The Field Value
     * @param bool $_isSerialized Whether the value is serialized
     * @param bool $_isEncrypted Whether the value is encrypted
     * @param bool $_isIncluded
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_fieldValue, $_isSerialized, $_isEncrypted, $_isIncluded = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        $this->UpdatePool('fieldvalue', $_fieldValue);
        $this->UpdatePool('isserialized', (int)($_isSerialized));
        $this->UpdatePool('isencrypted', (int)($_isEncrypted));
        $this->UpdatePool('lastupdated', time());

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Custom Field Value record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetCustomFieldValueID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Custom Field Values
     *
     * @author Varun Shoor
     * @param array $_customFieldValueIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldValueIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldValueIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldvalueid IN (" . BuildIN($_customFieldValueIDList) . ")");

        return true;
    }

    /**
     * Delete the List of Custom Field Values based on a list of Custom Field IDs
     *
     * @author Varun Shoor
     * @param array $_customFieldIDList The Custom Field ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnCustomField($_customFieldIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldIDList)) {
            return false;
        }

        $_customFieldValueIDList = array();
        $_SWIFT->Database->Query("SELECT customfieldvalueid FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldValueIDList[] = $_SWIFT->Database->Record['customfieldvalueid'];
        }

        if (!_is_array($_customFieldValueIDList)) {
            return false;
        }

        self::DeleteList($_customFieldValueIDList);

        return true;
    }

    /**
     * Sync the custom field values on type
     *
     * @author Varun Shoor
     * @param array $_groupTypeList
     * @param int $_newTypeID
     * @param array $_oldTypeIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SyncOnType($_groupTypeList, $_newTypeID, $_oldTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_groupTypeList) || empty($_newTypeID) || !_is_array($_oldTypeIDList)) {
            return false;
        }

        // First retrieve the custom field groups
        $_customFieldGroupIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks WHERE grouptype IN (" . BuildIN($_groupTypeList) . ") AND linktypeid = '" . $_newTypeID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];
        }

        // Now retrieve custom fields for the groups
        $_customFieldIDList = array();
        $_SWIFT->Database->Query("SELECT customfieldid FROM " . TABLE_PREFIX . "customfields WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldIDList[] = $_SWIFT->Database->Record['customfieldid'];
        }

        // Now update the values
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldvalues', array('typeid' => $_newTypeID), 'UPDATE', "customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND typeid IN (" . BuildIN($_oldTypeIDList) . ")");

        return true;
    }

    /**
     * Sync the custom field values on type
     *
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     *
     * @param SWIFT_Ticket $_SWIFT_TicketObject_New
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     *
     * @return bool
     */
    public static function CustomfieldvalueOnType($_SWIFT_TicketObject_New, $_SWIFT_TicketObject)
    {
        $_SWIFT = SWIFT::GetInstance();


        $_groupTypeList = array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET);
        $_newTypeID = $_SWIFT_TicketObject_New->GetTicketID();
        $_oldTypeID = $_SWIFT_TicketObject->GetTicketID();

        if (!_is_array($_groupTypeList) || empty($_newTypeID)) {

            return false;
        }

        // First retrieve the custom field groups
        $_customFieldGroupIDList = array();
        $_SWIFT->Database->Query("SELECT customfieldgroupid, grouptype FROM " . TABLE_PREFIX . "customfieldlinks WHERE grouptype IN (" . BuildIN($_groupTypeList) . ") AND linktypeid = '" . $_oldTypeID . "'");

        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldlinks', array(
                'grouptype' => (int)($_SWIFT->Database->Record['grouptype']), 'linktypeid' => $_newTypeID,
                'customfieldgroupid' => (int)($_SWIFT->Database->Record['customfieldgroupid'])

            ), 'INSERT');
        }

        // Now retrieve custom fields for the groups
        $_customFieldIDList = array();

        $_SWIFT->Database->Query("SELECT customfieldid FROM " . TABLE_PREFIX . "customfields WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");

        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldIDList [] = $_SWIFT->Database->Record['customfieldid'];
        }

        // Now retrieve custom field values
        $_SWIFT->Database->Query("SELECT isserialized, isencrypted, fieldvalue, customfieldid  FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND typeid = '" . $_oldTypeID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldvalues', array(
                'customfieldid' => (int)($_SWIFT->Database->Record['customfieldid']), 'typeid' => $_newTypeID, 'fieldvalue' => $_SWIFT->Database->Record['fieldvalue'],
                'isserialized' => (int)($_SWIFT->Database->Record['isserialized']), 'isencrypted' => (int)($_SWIFT->Database->Record['isencrypted']), 'dateline' => DATENOW,
                'uniquehash' => BuildHash(), 'lastupdated' => '0'
            ), 'INSERT');
        }

        return true;
    }

    /**
     * Duplicate Custom Fields
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     *
     * @param int $_fromTypeID The from type ID
     * @param int $_toTypeID The to type ID
     *
     * @return bool true
     */
    static function DuplicateCustomfields($_fromTypeID, $_toTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_customFieldValueContainer = $_SWIFT->Database->QueryFetchAll('SELECT t2.customfieldid, t2.fieldvalue, t2.isserialized, t2.isencrypted FROM ' . TABLE_PREFIX . SWIFT_CustomFieldValue::TABLE_NAME . ' as t2 LEFT JOIN ' . TABLE_PREFIX . SWIFT_CustomField::TABLE_NAME . ' as t1 USING(customfieldid) LEFT JOIN ' . TABLE_PREFIX . SWIFT_CustomFieldGroup::TABLE_NAME . ' as t3 USING(customfieldgroupid) WHERE t2.typeid = ' . $_fromTypeID . ' AND t3.grouptype in (' . implode(',', [SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET]). ')');

        if (is_array($_customFieldValueContainer)) {
            foreach ($_customFieldValueContainer as $_value) {
                SWIFT_CustomFieldValue::Create($_value['customfieldid'], $_toTypeID, $_value['fieldvalue'], $_value['isserialized'], $_value['isencrypted']);
            }
        }

        return true;
    }
}

?>
