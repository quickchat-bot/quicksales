<?php

/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Core Model Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Model extends SWIFT_Base implements SWIFT_Model_Interface
{
    protected $_updatePool = array();
    protected $_dataStore = array();
    static protected $_updatePoolQueue = array();
    const PRIMARY_KEY = '';
    const TABLE_NAME = '';

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Data|string|array $_SWIFT_DataObject (OPTIONAL) This has been kept optional for backward compatibility
     *
     * @throws SWIFT_Exception If the Model could not be Initialized
     */
    public function __construct($_SWIFT_DataObject = null)
    {
        if (!$_SWIFT_DataObject instanceof SWIFT_Data && is_numeric($_SWIFT_DataObject)) {
            $_SWIFT_DataObject = new SWIFT_DataID($_SWIFT_DataObject);
        } else if (!$_SWIFT_DataObject instanceof SWIFT_Data && _is_array($_SWIFT_DataObject)) {
            $_SWIFT_DataObject = new SWIFT_DataStore($_SWIFT_DataObject);
        }

        if (!$this->Initialize()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT_DataObject instanceof SWIFT_Data &&
            $_SWIFT_DataObject->GetIsClassLoaded() && !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load ' . __CLASS__ . ' Object');
        }

        parent::__construct();
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
     * Retrieve the identifier for this model
     *
     * @author Varun Shoor
     *
     * @param int $_id
     *
     * @return SWIFT_Model
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public static function GetOnID($_id)
    {
        return new static(new SWIFT_DataID($_id));
    }

    /**
     * Retrieve the identifier for this model
     *
     * @author Varun Shoor
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (constant('static::PRIMARY_KEY')) {
            return $this->GetProperty($this->GetPrimaryKeyName());
        }

        return 0;
    }

    /**
     * Retrieve the Table Name
     *
     * @author Varun Shoor
     * @return string The Table Name
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTableName()
    {
        return constant('static::TABLE_NAME')?:false;
    }

    /**
     * Retrieve the Primary Key Name
     *
     * @author Varun Shoor
     * @return string The Primary Key Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public static function GetPrimaryKeyName()
    {
        return constant('static::PRIMARY_KEY')?:false;
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

        if (!_is_array($this->GetUpdatePool()) || static::GetTableName() == false || static::GetPrimaryKeyName() == false) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . static::GetTableName(), $this->GetUpdatePool(), 'UPDATE', static::GetPrimaryKeyName() . " = '" . (int) ($this->GetProperty(static::GetPrimaryKeyName())) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Call a custom function
     *
     * @author Varun Shoor
     *
     * @param string $_functionName
     * @param array  $_argumentContainer
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function __call($_functionName, $_argumentContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_baseFunctionName = mb_strtolower($_functionName);

        // Get{PROPERTY}
        if (strtolower(substr($_baseFunctionName, 0, strlen('get')) == 'get')) {
            $_propertyName = mb_strtolower(trim(substr($_baseFunctionName, 3)));
            if (!isset($this->_dataStore[$_propertyName])) {
                throw new SWIFT_Exception($_functionName . ' failed, property "' . $_propertyName . '" not found in datastore.');
            }

            return $this->GetProperty($_propertyName);
        }

        // Set{PROPERTY}
        if (strtolower(substr($_baseFunctionName, 0, strlen('set')) == 'set')) {
            $_propertyName = mb_strtolower(trim(substr($_baseFunctionName, 3)));
            if (!isset($this->_dataStore[$_propertyName])) {
                throw new SWIFT_Exception($_functionName . ' failed, property "' . $_propertyName . '" not found in datastore.');
            }

            if (!isset($_argumentContainer[0])) {
                throw new SWIFT_Exception($_functionName . ' failed, no value specified.');
            }

            $this->SetProperty($_propertyName, $_argumentContainer[0]);

            return true;
        }

        return true;
    }

    /**
     * Retrieve a datastore property
     *
     * @author Varun Shoor
     *
     * @param string $_propertyName
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function __get($_propertyName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_propertyName])) {
            throw new SWIFT_Exception('Property retrieval failed, "' . $_propertyName . '" not found in datastore.');
        }

        return $this->GetProperty($_propertyName);
    }

    /**
     * Check to see if the property is set
     *
     * @author Varun Shoor
     *
     * @param string $_propertyName
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function __isset($_propertyName)
    {
        return isset($this->_dataStore[$_propertyName]);
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
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     *
     * @param string $_propertyName
     *
     * @return mixed
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_propertyName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->GetProperty($_propertyName);
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     *
     * @param string $_propertyName The Property Name
     *
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_propertyName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_propertyName])) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_INVALIDDATA . ': ' . $_propertyName);
        }

        $_propertyName = mb_strtolower($_propertyName);

        return $this->_dataStore[$_propertyName];
    }

    /**
     * Update Property
     *
     * @author Varun Shoor
     *
     * @param string $_propertyName
     * @param string $_propertyValue
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetProperty($_propertyName, $_propertyValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_propertyName])) {
            throw new SWIFT_Exception('Property update failed, "' . $_propertyName . '" not found in datastore.');
        }

        $_propertyName = mb_strtolower($_propertyName);

        $this->_dataStore[$_propertyName] = $_propertyValue;

        $this->UpdatePool($_propertyName, $_propertyValue);

        return true;
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Data|int $_SWIFT_DataObject The SWIFT_Data Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();


        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_query     = "SELECT * FROM " . TABLE_PREFIX . static::GetTableName() . " WHERE " . static::GetPrimaryKeyName() . " = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'";
            $_dataStore = $_SWIFT->Database->QueryFetch($_query);
            if (isset($_dataStore[static::GetPrimaryKeyName()]) && !empty($_dataStore[static::GetPrimaryKeyName()])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            return false;
        }

        // Is it a Store?
        if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore[static::GetPrimaryKeyName()]) || empty($this->_dataStore[static::GetPrimaryKeyName()])) {
                throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_INVALIDDATA);
    }

    /**
     * Delete the record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (static::GetPrimaryKeyName() == false) {
            return false;
        }

        static::DeleteList(array($this->GetProperty(static::GetPrimaryKeyName())));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of records
     *
     * @author Varun Shoor
     *
     * @param array $_idList
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_idList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_idList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . static::GetTableName() . " WHERE " . static::GetPrimaryKeyName() . " IN (" . BuildIN($_idList) . ")");

        return true;
    }

    /**
     * Add an entry into the update pool
     *
     * @param string $_key   The key
     * @param mixed $_value The value
     *
     * @return bool True on success, false otherwise
     */
    public function UpdatePool($_key, $_value)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Do we have a data store?
        $this->_dataStore[$_key] = $_value;

        $this->_updatePool[$_key] = $_value;

        $this->QueueUpdatePool();

        return true;
    }

    /**
     * Retrieves the update pool
     *
     * @return array|bool The update pool
     */
    public function GetUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_updatePool;
    }

    /**
     * Clears the Update Pool
     *
     * @return bool True on success, false otherwise
     */
    public function ClearUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->_updatePool = array();

        $_keyName = get_short_class($this) . '_' . $this->GetInstanceID();

        // Clear shutdown queue if set
        if (isset(static::$_updatePoolQueue[$_keyName])) {
            unset(static::$_updatePoolQueue[$_keyName]);
        }

        return true;
    }

    /**
     * Queue the processing of the update pool for this object
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function QueueUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_keyName = get_short_class($this) . '_' . $this->GetInstanceID();

        if (!isset(static::$_updatePoolQueue[$_keyName])) {
            static::$_updatePoolQueue[$_keyName] = $this;
        }

        return true;
    }

    /**
     * Process the pending update pools before shutdown
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function ProcessShutdownUpdatePool()
    {
        if (!_is_array(static::$_updatePoolQueue)) {
            return false;
        }

        foreach (static::$_updatePoolQueue as $_ModelObject) {
            if ($_ModelObject instanceof SWIFT_Model && $_ModelObject->GetIsClassLoaded() && method_exists($_ModelObject, 'ProcessUpdatePool')) {
                $_ModelObject->ProcessUpdatePool();
            }
        }

        static::$_updatePoolQueue = array();

        return true;
    }
}
