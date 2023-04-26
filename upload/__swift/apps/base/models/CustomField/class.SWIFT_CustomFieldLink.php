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

/**
 * The Custom Field Link Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldLink extends SWIFT_Model
{
    const TABLE_NAME = 'customfieldlinks';
    const PRIMARY_KEY = 'customfieldlinkid';

    const TABLE_STRUCTURE = "customfieldlinkid I PRIMARY AUTO NOTNULL,
                                grouptype I2 DEFAULT '0' NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL,
                                customfieldgroupid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'grouptype, linktypeid, customfieldgroupid';
    const INDEX_2 = 'customfieldgroupid';


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
            throw new SWIFT_Exception('Failed to load Custom Field Link Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldlinks', $this->GetUpdatePool(), 'UPDATE', "customfieldlinkid = '" . (int)($this->GetCustomFieldLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Custom Field Link ID
     *
     * @author Varun Shoor
     * @return mixed "customfieldlinkid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCustomFieldLinkID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['customfieldlinkid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks WHERE customfieldlinkid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['customfieldlinkid']) && !empty($_dataStore['customfieldlinkid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['customfieldlinkid']) || empty($this->_dataStore['customfieldlinkid'])) {
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
     * Create a new Custom Field Link
     *
     * @author Varun Shoor
     * @param mixed $_groupType
     * @param int $_linkTypeID
     * @param int $_customFieldGroupID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_groupType, $_linkTypeID, $_customFieldGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_linkTypeID) || !SWIFT_CustomFieldGroup::IsValidGroupType($_groupType) || empty($_customFieldGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->Replace(TABLE_PREFIX . 'customfieldlinks', array('grouptype' => (int)($_groupType), 'linktypeid' => $_linkTypeID,
            'customfieldgroupid' => $_customFieldGroupID), array('grouptype', 'linktypeid', 'customfieldgroupid'));

        return true;
    }

    /**
     * Delete the Custom Field Link record
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

        self::DeleteList(array($this->GetCustomFieldLinkID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Custom Field Links
     *
     * @author Varun Shoor
     * @param array $_customFieldLinkIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_customFieldLinkIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldLinkIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldlinks WHERE customfieldlinkid IN (" . BuildIN($_customFieldLinkIDList) . ")");

        return true;
    }

    /**
     * Delete the links and corresponding values based on group type and linktype id list
     *
     * @author Varun Shoor
     * @param mixed $_groupType The Group Type
     * @param array $_linkTypeIDList The Link Type ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnLink($_groupType, $_linkTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_CustomFieldGroup::IsValidGroupType($_groupType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!_is_array($_linkTypeIDList)) {
            return false;
        }

        $_customFieldLinkIDList = array();
        $_SWIFT->Database->Query("SELECT customfieldlinkid FROM " . TABLE_PREFIX . "customfieldlinks
            WHERE grouptype = '" . (int)($_groupType) . "' AND linktypeid IN (" . BuildIN($_linkTypeIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldLinkIDList[] = (int)($_SWIFT->Database->Record['customfieldlinkid']);
        }

        if (!count($_customFieldLinkIDList)) {
            return false;
        }

        self::DeleteList($_customFieldLinkIDList);

        return true;
    }

    /**
     * Replace the links based on group type and link types
     *
     * @author Varun Shoor
     * @param array $_groupTypeList
     * @param array $_linkTypeIDList
     * @param int $_newLinkTypeID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ReplaceOnLink($_groupTypeList, $_linkTypeIDList, $_newLinkTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_groupTypeList) || !_is_array($_linkTypeIDList) || empty($_newLinkTypeID)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'customfieldlinks', array('linktypeid' => $_newLinkTypeID), 'UPDATE', "grouptype IN (" . BuildIN($_groupTypeList) . ") AND linktypeid IN (" . BuildIN($_linkTypeIDList) . ")");

        return true;
    }

    /**
     * Delete the Custom Field Links based on a list of Custom Field Group IDs
     *
     * @author Varun Shoor
     * @param array $_customFieldGroupIDList The Custom Field Group ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnCustomFieldGroup($_customFieldGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_customFieldGroupIDList)) {
            return false;
        }

        $_customFieldLinkIDList = array();
        $_SWIFT->Database->Query("SELECT customfieldlinkid FROM " . TABLE_PREFIX . "customfieldlinks WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldLinkIDList[] = (int)($_SWIFT->Database->Record['customfieldlinkid']);
        }

        if (!count($_customFieldLinkIDList)) {
            return false;
        }

        self::DeleteList($_customFieldLinkIDList);

        return true;
    }

    /**
     * Duplicate links
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     *
     * @param int $_fromLinkTypeID The from link type ID
     * @param int $_toLinkTypeID The to link type ID
     *
     * @return bool true
     */
    static function DuplicateLinks($_fromLinkTypeID, $_toLinkTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_customFieldLinkContainer = $_SWIFT->Database->QueryFetchAll('SELECT grouptype, customfieldgroupid FROM ' . TABLE_PREFIX . SWIFT_CustomFieldLink::TABLE_NAME . ' WHERE linktypeid = ' . $_fromLinkTypeID);

        if (is_array($_customFieldLinkContainer)) {
            foreach ($_customFieldLinkContainer as $_link) {
                SWIFT_CustomFieldLink::Create($_link['grouptype'], $_toLinkTypeID, $_link['customfieldgroupid']);
            }
        }

        return true;
    }
}

?>
