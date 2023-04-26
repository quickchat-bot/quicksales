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

namespace Base\Models\Import;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Import Registry Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_ImportRegistry extends SWIFT_Model
{
    const TABLE_NAME = 'importregistry';
    const PRIMARY_KEY = 'importregistryid';

    const TABLE_STRUCTURE = "importregistryid I PRIMARY AUTO NOTNULL,
                                section C(50) DEFAULT '' NOTNULL,
                                vkey C(50) DEFAULT '' NOTNULL,
                                data C(255) DEFAULT '' NOTNULL,
                                nocache I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'section, vkey';
    const INDEX_2 = 'nocache';


    public $_settingsCache = array();
    static protected $_importRegistryCache = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        $this->LoadLocalCache();
    }

    /**
     * Load the Local Import Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadLocalCache()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (self::$_importRegistryCache != false) {
            $this->_settingsCache = self::$_importRegistryCache;

            return true;
        }

        $_localCache = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "importregistry WHERE nocache = '0'");
        while ($this->Database->NextRecord()) {
            $_localCache[$this->Database->Record['section']][$this->Database->Record['vkey']] = $this->Database->Record['data'];
        }

        $this->_settingsCache = $_localCache;

        self::$_importRegistryCache = $_localCache;

        return true;
    }

    /**
     * Load the Import Cache from Non Cached Results
     *
     * @author Varun Shoor
     * @param string $_sectionName The Section Name
     * @param array $_keyList (OPTIONAL) The Key List
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNonCached($_sectionName, $_keyList = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_extendedSQL = '';
        if (_is_array($_keyList)) {
            $_extendedSQL = " AND vkey IN (" . BuildIN($_keyList) . ")";
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "importregistry WHERE section = '" . $this->Database->Escape($_sectionName) . "'" . $_extendedSQL);
        while ($this->Database->NextRecord()) {
            if (!isset($this->_settingsCache[$this->Database->Record['section']])) {
                $this->_settingsCache[$this->Database->Record['section']] = array();
            }

            $this->_settingsCache[$this->Database->Record['section']][$this->Database->Record['vkey']] = $this->Database->Record['data'];
        }

        self::$_importRegistryCache = $this->_settingsCache;

        if (isset($this->_settingsCache[$_sectionName])) {
            return $this->_settingsCache[$_sectionName];
        }

        return array();
    }

    /**
     * Update the local cache
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Key Name
     * @param string $_keyData The Key Data
     * @return bool "true" on Success, "false" otherwise
     */
    public function UpdateLocalCache($_sectionName, $_keyName, $_keyData = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName) || empty($_sectionName)) {
            return false;
        }

        $this->_settingsCache[$_sectionName][$_keyName] = $_keyData;

        return true;
    }

    /**
     * Delete the Local Cache for a given key
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Key Name
     * @return bool "true" on Success, "false" otherwise
     */
    private function DeleteLocalCache($_sectionName, $_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName) || empty($_sectionName)) {
            return false;
        }

        unset($this->_settingsCache[$_sectionName][$_keyName]);

        return true;
    }

    /**
     * Delete the Local Cache for a given key
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @return bool "true" on Success, "false" otherwise
     */
    private function DeleteLocalCacheSection($_sectionName)
    {
        if (!$this->GetIsClassLoaded() || empty($_sectionName)) {
            return false;
        }

        unset($this->_settingsCache[$_sectionName]);

        return true;
    }

    /**
     * Insert a new Setting Key
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Setting Key Name
     * @param string $_keyValue The Setting Key Value
     * @param bool $_noCache (OPTIONAL) Whether to not cache the records
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertKey($_sectionName, $_keyName, $_keyValue, $_noCache = false)
    {
        if (empty($_sectionName) || empty($_keyName)) {
            return false;
        }

//        if (SWIFT_INTERFACE != 'console')
//        {
        $this->Database->AutoExecute(TABLE_PREFIX . 'importregistry', array('section' => $_sectionName, 'vkey' => $_keyName, 'data' => $_keyValue, 'nocache' => (int)($_noCache)), 'INSERT');
//        }

        $this->UpdateLocalCache($_sectionName, $_keyName, $_keyValue);

        return true;
    }

    /**
     * Updates a given setting key, if empty.. deletes it, if it doesnt exist.. attempts to create one
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Setting Key Name
     * @param string|int $_keyValue The Setting Key Value
     * @param bool $_noCache (OPTIONAL) Whether to not cache the records
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function UpdateKey($_sectionName, $_keyName, $_keyValue, $_noCache = false)
    {

        if (empty($_sectionName) || empty($_keyName)) {
            return false;
        }

//        if (SWIFT_INTERFACE == 'console')
//        {
//            $this->UpdateLocalCache($_sectionName, $_keyName, $_keyValue);

//            return true;
//        }

        $_settingContainer = $this->Database->QueryFetch("SELECT section, vkey FROM " . TABLE_PREFIX . "importregistry
            WHERE section = '" . $this->Database->Escape($_sectionName) . "' AND vkey = '" . $this->Database->Escape($_keyName) . "'", 5);

        if ($_settingContainer['section'] == $_sectionName && $_settingContainer['vkey'] == $_keyName) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'importregistry', array('data' => $_keyValue, 'nocache' => (int)($_noCache)),
                'UPDATE', "section = '" . $this->Database->Escape($_sectionName) . "' AND vkey = '" . $this->Database->Escape($_keyName) . "'");

            $this->UpdateLocalCache($_sectionName, $_keyName, $_keyValue);

            return true;
        } else {
            return $this->InsertKey($_sectionName, $_keyName, $_keyValue, $_noCache);
        }
    }

    /**
     * Delete the given setting key
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Setting Key Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteKey($_sectionName, $_keyName)
    {
        if (empty($_sectionName) || empty($_keyName)) {
            return false;
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "importregistry
            WHERE section = '" . $this->Database->Escape($_sectionName) . "' AND vkey = '" . $this->Database->Escape($_keyName) . "'", 5);

        $this->DeleteLocalCache($_sectionName, $_keyName);

        return true;
    }

    /**
     * Delete all records
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteAll()
    {
        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "importregistry", 5);

        $this->_settingsCache = array();
        self::$_importRegistryCache = false;

        $this->LoadLocalCache();

        return true;
    }

    /**
     * Delete the given Setting section
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteSection($_sectionName)
    {
        if (empty($_sectionName)) {
            return false;
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "importregistry WHERE section = '" . $this->Database->Escape($_sectionName) . "'", 5);

        $this->DeleteLocalCacheSection($_sectionName);

        return true;
    }

    /**
     * Get a setting key
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Setting Key Name
     * @return mixed Settings Key Value (STRING) on Success, "false" otherwise
     */
    public function GetKey($_sectionName, $_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_sectionName) || empty($_keyName)) {
            return false;
        }

        if (isset($this->_settingsCache[$_sectionName]) && isset($this->_settingsCache[$_sectionName][$_keyName])) {
            return $this->_settingsCache[$_sectionName][$_keyName];
        }

        return false;
    }

    /**
     * Get a complete section
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @return mixed Section Container (ARRAY) on Success, "false" otherwise
     */
    public function GetSection($_sectionName)
    {
        if (!$this->GetIsClassLoaded() || empty($_sectionName)) {
            return false;
        }

        if (isset($this->_settingsCache[$_sectionName])) {
            return $this->_settingsCache[$_sectionName];
        }

        return false;
    }

    /**
     * Gets a generic setting
     *
     * @author Varun Shoor
     * @param string $_keyName The Setting Key Name
     * @return mixed Settings Key Value (STRING) on Success, "void" otherwise
     */
    public function Get($_keyName)
    {
        if (!$this->GetIsClassLoaded() || empty($_keyName)) {
            return;
        }

        if (isset($this->_settingsCache['settings'][$_keyName])) {
            return $this->_settingsCache['settings'][$_keyName];
        }

        return;
    }
}

?>
