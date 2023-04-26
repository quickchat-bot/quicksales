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

/**
 * The Registry Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Registry extends SWIFT_Model
{
    const TABLE_NAME        =    'registry';
    const PRIMARY_KEY        =    'vkey';

    const TABLE_STRUCTURE    =    "vkey C(50) DEFAULT '' PRIMARY NOTNULL,
                                data XL,
                                dateline I DEFAULT '0' NOTNULL,
                                isvolatile I2 DEFAULT '0' NOTNULL,
                                datasize I DEFAULT '0' NOTNULL";

    const CACHE_FILE = 'SWIFT_RegistryCache.cache';

    private $_registryCache = array();

    static protected $_RegistryCacheList = array();
    static protected $_isRegistryCacheLoaded = false;

    /**
     * Check to see if the key has a local cache
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @return bool "true" on Success, "false" otherwise
     */
    private function HasLocalCache($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        if (isset($this->_registryCache[$_keyName]))
        {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Local Cache Text
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @return string|bool "_registryCache[_keyName]" (STRING) on Success, "false" otherwise
     */
    private function GetLocalCache($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        if (isset($this->_registryCache[$_keyName]))
        {
            return $this->_registryCache[$_keyName];
        }

        return false;
    }

    /**
     * Update the local cache
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @param string $_keyData The Key Data
     * @return bool "true" on Success, "false" otherwise
     */
    private function UpdateLocalCache($_keyName, $_keyData = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        $this->_registryCache[$_keyName] = $_keyData;
//        SWIFT::Set($_keyName, @unserialize($_keyData));

        return true;
    }

    /**
     * Delete the Local Cache for a given key
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @return bool "true" on Success, "false" otherwise
     */
    private function DeleteLocalCache($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        unset($this->_registryCache[$_keyName]);

        return true;
    }

    /**
     * Insert a new Registry Key
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @param string $_keyData The Key Data
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertKey($_keyName, $_keyData = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX.'registry', array('vkey' => $_keyName, 'data' => $_keyData, 'dateline' => DATENOW, 'isvolatile' => '0', 'datasize' => mb_strlen($_keyData)), 'INSERT');

        //refresh the registry cache file.
        self::RefreshRegistryCacheFile();

        $this->UpdateLocalCache($_keyName, $_keyData);

        return true;
    }

    /**
     * Update the Key
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @param string $_keyData The Key Data
     * @return bool "true" on Success, "false" otherwise
     */
    public function UpdateKey($_keyName, $_keyData = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        if (!empty($_keyData))
        {
            $_returnData = $this->GetKey($_keyName);

            if (!empty($_returnData))
            {
                $this->Database->AutoExecute(TABLE_PREFIX . 'registry', array('data' => $_keyData, 'datasize' => mb_strlen($_keyData), 'dateline' => DATENOW), 'UPDATE', "vkey = '". $this->Database->Escape($_keyName) ."'");

                self::RefreshRegistryCacheFile();

                $this->UpdateLocalCache($_keyName, $_keyData);

                return true;
            } else {
                return $this->InsertKey($_keyName, $_keyData);
            }
        } else {
            return $this->DeleteKey($_keyName);
        }

        return false;
    }

    /**
     * Delete the key from the database
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteKey($_keyName = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        $this->DeleteKeyList(array($_keyName));

        return true;
    }

    /**
     * Delete a list of keys
     *
     * @author Varun Shoor
     * @param array $_keyNameList The Key Name List
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteKeyList($_keyNameList)
    {
        if (!$this->GetIsClassLoaded() || !_is_array($_keyNameList))
        {
            return false;
        }

        $this->Database->Query("DELETE FROM ". TABLE_PREFIX ."registry WHERE vkey IN (". BuildIN($_keyNameList) .")", 5);

        self::RefreshRegistryCacheFile();

        foreach ($_keyNameList as $_key => $_val)
        {
            $this->DeleteLocalCache($_val);
        }

        return true;
    }

    /**
     * Get a Key Data
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name
     * @return mixed "_keyData" (STRING) on Success, "false" otherwise
     */
    public function GetKey($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName))
        {
            return false;
        }

        if ($this->HasLocalCache($_keyName))
        {
            return $this->GetLocalCache($_keyName);
        }

        if(self::$_isRegistryCacheLoaded){
            if(isset(self::$_RegistryCacheList[$_keyName]['data'])){
                $this->UpdateLocalCache($_keyName, self::$_RegistryCacheList[$_keyName]['data']);
                return self::$_RegistryCacheList[$_keyName]['data'];
            }
        }
        
        $this->Database->Query("SELECT data FROM ". TABLE_PREFIX ."registry WHERE vkey = '". $this->Database->Escape($_keyName) ."'", 5);
        $this->Database->NextRecord(5);

        if ($this->Database->Record5["data"] != "")
        {
            $this->UpdateLocalCache($_keyName, $this->Database->Record5["data"]);

            return $this->Database->Record5["data"];
        }
        
        return false;
    }

    /**
     * Retrieve the registry entries based on a key list
     *
     * @author Varun Shoor
     * @param array $_keyNameList The Key Name List Container
     * @return mixed "array('key' => 'data')" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception When the Registry data could not be loaded
     */
    public function GetKeyList($_keyNameList)
    {
        if (!$this->GetIsClassLoaded() || !_is_array($_keyNameList))
        {
            return false;
        }

        $_resultContainer = $_finalKeyNameList = $_finalKeyNameList_rc =  array();

        foreach ($_keyNameList as $_key => $_val)
        {
            if ($this->HasLocalCache($_val))
            {
                $_resultContainer[$_val] = $this->GetLocalCache($_val);
            } else {
                $_finalKeyNameList_rc[] = $_val;
            }
        }

        // Return the container if every item was in the cache
        if (!count($_finalKeyNameList_rc))
        {
            return $_resultContainer;
        }

        if(self::$_isRegistryCacheLoaded){
            foreach ($_finalKeyNameList_rc as $_key => $_val) {
                if(isset(self::$_RegistryCacheList[$_val]['data'])){
                    $_resultContainer[$_val] = self::$_RegistryCacheList[$_val]['data'];
                    $this->UpdateLocalCache($_val, self::$_RegistryCacheList[$_val]['data']);
                }
                else{
                    $_finalKeyNameList[] = $_val;
                }
            }
        }

        // Return the container if every item was in the Registry file cache
        if (!count($_finalKeyNameList))
        {
            return $_resultContainer;
        }

        $_queryResult = $this->Database->Query("SELECT vkey, data FROM ". TABLE_PREFIX ."registry WHERE vkey IN(". BuildIN($_finalKeyNameList) .")", 5, true);
        if (!$_queryResult)
        {
            throw new SWIFT_Exception('Unable to connect to Database. ' . SWIFT_PRODUCT . ' might not be installed. Please run /setup to install '. SWIFT_PRODUCT . '.');

            log_error_and_exit();
        }
        while ($this->Database->NextRecord(5))
        {
            $_resultContainer[$this->Database->Record5["vkey"]] = $this->Database->Record5["data"];

            $this->UpdateLocalCache($this->Database->Record5["vkey"], $this->Database->Record5["data"]);
        }

        return $_resultContainer;
    }

    /**
     * Load Registry Cache from file.
     *
     * @author Ankit Saini
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadRegistryCache()
    {

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        //No need to store cache in file while in setup.
        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP) {
            return true;
        }

        $_cachePathFile = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . self::CACHE_FILE;
        $_cacheList = array();

        if (!file_exists($_cachePathFile)) {
            self::RefreshRegistryCacheFile();
        }
        else{
            $_fileContents = @file_get_contents($_cachePathFile);

            if (!empty($_fileContents)) {
                $_cacheList = @unserialize($_fileContents);

                //registry data must always hold some contents
                if (!is_array($_cacheList)) {
                    $_cacheList = array();
                    self::$_RegistryCacheList = $_cacheList;
                    self::$_isRegistryCacheLoaded = false;
                    return false;
                }
                else{
                    self::$_RegistryCacheList = $_cacheList;
                    self::$_isRegistryCacheLoaded = true;
                    return true;
                }    
            }
            else{
                self::RefreshRegistryCacheFile();
            }
        }

        return true;
    }


    /**
     * Load Registry Cache from DB into a file.
     *
     * @author Ankit Saini
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RefreshRegistryCacheFile(){

        $_SWIFT = SWIFT::GetInstance();

        //No need to store cache in file while in setup.
        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP) {
            return true;
        }

        chdir(SWIFT_BASEPATH);
        $_cachePathFile = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . self::CACHE_FILE;
        $_cacheList = array();

        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."registry");

        while($_SWIFT->Database->NextRecord()) {
            $_cacheList[$_SWIFT->Database->Record['vkey']] = $_SWIFT->Database->Record;
        }

        self::$_RegistryCacheList = $_cacheList;
        self::$_isRegistryCacheLoaded = true;

        file_put_contents($_cachePathFile, serialize(self::$_RegistryCacheList), LOCK_EX);
        @chmod($_cachePathFile, 0666);

        return true;
    }

}
