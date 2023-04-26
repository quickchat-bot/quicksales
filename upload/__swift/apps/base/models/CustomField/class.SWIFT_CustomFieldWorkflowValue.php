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
class SWIFT_CustomFieldWorkflowValue extends SWIFT_Model
{
    const TABLE_NAME = 'customfieldworkflowvalues';
    const PRIMARY_KEY = 'customfieldworkflowvalueid';

    const TABLE_STRUCTURE = "customfieldworkflowvalueid I PRIMARY AUTO NOTNULL,
                                customfieldid I DEFAULT '0' NOTNULL,
                                ticketworkflowid I DEFAULT '0' NOTNULL,
                                fieldvalue X NOTNULL,
                                isserialized I2 DEFAULT '0' NOTNULL,
                                isencrypted I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                uniquehash C(100) DEFAULT '' NOTNULL,
                                lastupdated I DEFAULT '0' NOTNULL,
                                isincluded I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'customfieldid, ticketworkflowid';
    const INDEX_2 = 'uniquehash';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
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
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldworkflowvalues', $this->GetUpdatePool(), 'UPDATE', "customfieldworkflowvalueid = '" . (int)$this->GetCustomFieldWorkflowValueID() . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Custom Field Value ID
     *
     * @author Varun Shoor
     * @return mixed "customfieldworkflowvalueid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCustomFieldWorkflowValueID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['customfieldworkflowvalueid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfieldworkflowvalues WHERE customfieldworkflowvalueid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['customfieldworkflowvalueid']) && !empty($_dataStore['customfieldworkflowvalueid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['customfieldworkflowvalueid']) || empty($this->_dataStore['customfieldworkflowvalueid'])) {
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
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Custom Field Value Record
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @param int $_ticketWorkflowID The Type ID
     * @param string $_fieldValue The Field Value
     * @param bool $_isSerialized Whether the value is serialized
     * @param bool $_isEncrypted Whether the value is encrypted
     * @param bool $_isIncluded Whether the custom field is included
     * @return int The Custom Field Value ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_customFieldID, $_ticketWorkflowID, $_fieldValue, $_isSerialized, $_isEncrypted, $_isIncluded)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customFieldID) || empty($_ticketWorkflowID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldworkflowvalues', [
            'customfieldid' => $_customFieldID,
            'ticketworkflowid' => $_ticketWorkflowID,
            'fieldvalue' => $_fieldValue,
            'dateline' => DATENOW,
            'lastupdated' => '0',
            'isserialized' => (int)$_isSerialized,
            'isencrypted' => (int)$_isEncrypted,
            'isincluded' => (int)$_isIncluded,
            'uniquehash' => BuildHash(),
        ], 'INSERT');
        $_customfieldworkflowvalueid = $_SWIFT->Database->Insert_ID();

        if (!$_customfieldworkflowvalueid) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_customfieldworkflowvalueid;
    }

    /**
     * Update the Custom Field Value Record
     *
     * @author Varun Shoor
     * @param string $_fieldValue The Field Value
     * @param bool $_isSerialized Whether the value is serialized
     * @param bool $_isEncrypted Whether the value is encrypted
     * @param bool $_isIncluded Whether the customfield is included
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_fieldValue, $_isSerialized, $_isEncrypted, $_isIncluded)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        $this->UpdatePool('fieldvalue', $_fieldValue);
        $this->UpdatePool('isserialized', (int)$_isSerialized);
        $this->UpdatePool('isencrypted', (int)$_isEncrypted);
        $this->UpdatePool('isincluded', (int)$_isIncluded);
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

        self::DeleteList(array($this->GetCustomFieldWorkflowValueID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Custom Field Values
     *
     * @author Varun Shoor
     * @param array $_customfieldworkflowvalueidList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_customfieldworkflowvalueidList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customfieldworkflowvalueidList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldworkflowvalues WHERE customfieldworkflowvalueid IN (" . BuildIN($_customfieldworkflowvalueidList) . ")");

        return true;
    }
}
