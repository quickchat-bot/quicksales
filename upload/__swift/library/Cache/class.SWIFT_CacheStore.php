<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Abhishek Mittal
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Main Serialized Cache Storage Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CacheStore extends SWIFT_Library
{
    protected $CacheMemory = false;

    private $_cacheQueue = array();
    private $_cacheStore = array();
    private $_rawCacheStore = array();
    /** @var SWIFT_FirePHP  */
    public $FirePHP;

    /**
     * Constructor
     *
     * @author Abhishek Mittal
     */
    public function __construct()
    {
        parent::__construct();

        $this->GetCacheObject();

    }

    /**
     * Get Cache object
     *
     * @author Abhishek Mittal
     * @return SWIFT_Cache
     */
    public function GetCacheObject()
    {
        if (!$this->CacheMemory instanceof SWIFT_Cache && $this->CacheMemory === false) {
            $this->CacheMemory = SWIFT_Cache::GetObject();
        }

        return $this->CacheMemory;
    }

    /**
     * Check to see if the key has a local cache
     *
     * @author Varun Shoor
     *
     * @param string $_keyName The Key Name
     *
     * @return bool "true" on Success, "false" otherwise
     */
    private function HasLocalCache($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName)) {
            return false;
        }

        if (isset($this->_rawCacheStore[$_keyName]) || isset($this->_cacheStore[$_keyName])) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Local Cache Text
     *
     * @author Varun Shoor
     *
     * @param string $_keyName The Key Name
     *
     * @return string|bool "_cacheStore[_keyName]" (STRING) on Success, "false" otherwise
     */
    private function GetLocalCache($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName)) {
            return false;
        }

        if (isset($this->_cacheStore[$_keyName])) {
            return $this->_cacheStore[$_keyName];
        }

        if (isset($this->_rawCacheStore[$_keyName])) {

            if (is_array($this->_rawCacheStore[$_keyName])) {
                $_cacheStoreResult = $this->_rawCacheStore[$_keyName];
            } else {
                $_cacheStoreResult = json_decode($this->_rawCacheStore[$_keyName], true);
            }

            if ($_cacheStoreResult !== false) {
                $this->_cacheStore[$_keyName] = $_cacheStoreResult;
            } else if ($_cacheStoreResult === false) {
                echo 'Invalid Cache: ' . $_keyName;
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_PHPERROR, 'Invalid Cache: ' . $_keyName);
            }

            unset($this->_rawCacheStore[$_keyName]);
        }

        return false;
    }

    /**
     * Update the local cache
     *
     * @author Varun Shoor
     *
     * @param string       $_keyName The Key Name
     * @param string|array $_keyData The Key Data
     *
     * @return bool "true" on Success, "false" otherwise
     */
    private function UpdateLocalCache($_keyName, $_keyData = array())
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName)) {
            return false;
        }

        if ($this->CacheMemory instanceof SWIFT_Cache_Interface) {
            $this->CacheMemory->Set($_keyName, $_keyData, null);
        }

        $this->_cacheStore[$_keyName] = $_keyData;

        return true;
    }

    /**
     * Queue a given cache for loading in one go
     *
     * @author Varun Shoor
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Queue()
    {
        foreach (func_get_args() as $_keyName) {
            $_keyName = Clean($_keyName);

            if (in_array($_keyName, $this->_cacheQueue)) {
                continue;
            }

            $this->_cacheQueue[] = $_keyName;
        }

        return true;
    }

    /**
     * Get from the Local Cache
     *
     * @author Varun Shoor
     *
     * @param string $_keyName The Key Name
     *
     * @return mixed
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_keyName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_localCache = $this->GetLocalCache($_keyName);

        if (!$_localCache && $_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_SETUP) {
            $this->Load($_keyName);

            return $this->GetLocalCache($_keyName);
        }

        return $_localCache;
    }

    /**
     * Update the Cache Store for a given Key
     *
     * @author Varun Shoor
     *
     * @param string $_keyName       The Key Name
     * @param array  $_cacheContents The Key Cache Contents
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Update($_keyName, $_cacheContents)
    {
        $_keyName = Clean($_keyName);

        if (is_array($_cacheContents)) {
            $this->Registry->UpdateKey($_keyName, @json_encode($_cacheContents));

            SWIFT::Set($_keyName, $_cacheContents);

            $this->UpdateLocalCache($_keyName, $_cacheContents);

            return true;
        }

        return false;
    }

    /**
     * Load the given cache from the cache store
     *
     * @author Varun Shoor
     *
     * @param string $_keyName The Key Name
     *
     * @return string|bool
     */
    public function Load($_keyName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($this->HasLocalCache($_keyName)) {
            return $this->GetLocalCache($_keyName);
        }

        $_registryData = false;

        if ($this->CacheMemory instanceof SWIFT_Cache_Interface) {
            $_registryData = $this->CacheMemory->Get($_keyName);
        }

        $_cache = false;

        if ($_registryData === false) {
            $_registryData = $this->Registry->GetKey($_keyName);
        }

        if (!empty($_registryData)) {
            if (!is_array($_registryData)) {
                $_cache = json_decode($_registryData, true);
            } else {
                $_cache = $_registryData;
            }

            if ($_cache === false) {
                echo 'Invalid Cache (2): ' . $_keyName;
                SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_PHPERROR, 'Invalid Cache: ' . $_keyName);
            }
        }

        if (!is_array($_cache)) {
            $_cache = array();
        }

        $this->UpdateLocalCache($_keyName, $_cache);

        SWIFT::Set($_keyName, $_cache);

        return true;
    }

    /**
     * Load the Cache Items from the Queue
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadQueue()
    {
        $_SWIFT = SWIFT::GetInstance();

        // We dont load the cache store when in setup.. because theres no database to connect to stupid :P
        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP) {
            return true;
        }

        $_cacheQueue = $this->_cacheQueue;
        if (!_is_array($_cacheQueue)) {
            $this->_cacheQueue = array();

            return false;
        }

        $_finalCacheQueue   = array();
        $_memoryCacheResult = false;

        if ($this->CacheMemory instanceof SWIFT_Cache_Interface) {
            $_memoryCacheResult = $this->CacheMemory->GetMultiple($_cacheQueue);
        }

        if (_is_array($_memoryCacheResult)) {
            foreach ($_cacheQueue as $_cacheName) {

                if (array_key_exists($_cacheName, $_memoryCacheResult) && !isset($_memoryCacheResult[$_cacheName])) {
                    $_finalCacheQueue[] = $_cacheName;
                }
            }
        } else {
            $_finalCacheQueue   = $_cacheQueue;
            $_memoryCacheResult = array();
        }

        if (!count($_finalCacheQueue)) {
            return false;
        }

        $_registryKeys = $this->Registry->GetKeyList($_finalCacheQueue);
        reset($_registryKeys);

        $_cacheContainer = array_merge($_memoryCacheResult, $_registryKeys);

        if (!_is_array($_cacheContainer)) {
            $this->_cacheQueue = array();

            return false;
        }

        $_loadedCacheList = array();

        foreach ($_cacheContainer as $_key => $_val) {

            $_loadedCacheList[] = $_key;

            $this->_rawCacheStore[$_key] = $_val;

            unset($this->_cacheQueue[$_key]);
        }

        foreach ($this->_cacheQueue as $_val) {
            // Cache not Loaded?
            if (!in_array($_val, $_loadedCacheList)) {
                $_cacheContents = array();
                $this->UpdateLocalCache($_val, $_cacheContents);
            }
        }

        foreach ($_finalCacheQueue as $_key) {
            if (array_key_exists($_key, $this->_rawCacheStore)) {
                $this->UpdateLocalCache($_key, json_decode($this->_rawCacheStore[$_key], true));
            }
        }

        $this->_cacheQueue = array();

        return true;
    }

    /**
     * Remove from the local cache
     *
     * @author Werner Garcia
     * @param string       $_keyName The Key Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function Remove($_keyName)
    {
        $_keyName = Clean($_keyName);

        $this->Registry->DeleteKeyList([$_keyName]);

        SWIFT::Remove($_keyName);

        $this->RemoveLocalCache($_keyName);

        return true;
    }

    /**
     * Removes object from the local cache
     *
     * @author Werner Garcia
     * @param string       $_keyName The Key Name
     * @return bool "true" on Success, "false" otherwise
     */
    private function RemoveLocalCache($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName) || !isset($this->_cacheStore[$_keyName])) {
            return false;
        }

        if ($this->CacheMemory !== false) {
            $this->CacheMemory->Delete($_keyName);
        }

        $this->_cacheStore[$_keyName] = null;
        unset($this->_cacheStore[$_keyName]);

        return true;
    }
}
