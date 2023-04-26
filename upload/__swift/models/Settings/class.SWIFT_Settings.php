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

/**
 * The Settings Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Settings extends SWIFT_Model
{
    const TABLE_NAME        =    'settings';
    const PRIMARY_KEY        =    'settingid';

    const TABLE_STRUCTURE    =    "settingid I PRIMARY AUTO NOTNULL,
                                section C(50) DEFAULT '' NOTNULL,
                                vkey C(50) DEFAULT '' NOTNULL,
                                data X2";

    const INDEX_1            =    'section, vkey';


    public $_settingsCache = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT::Get('settingscache'))
        {
            SWIFT::Set('settingscache', array());
        }

        if ($_SWIFT->Cache !== false)
            $this->ReplaceLocalCache($_SWIFT->Cache->Get('settingscache'));

        SWIFT::SetReference('settings', $this->_settingsCache['settings']);

        parent::__construct();
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
        if (!$this->GetIsClassLoaded() || empty($_keyName) || empty($_sectionName))
        {
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
        if (!$this->GetIsClassLoaded() || empty($_keyName) || empty($_sectionName))
        {
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
        if (!$this->GetIsClassLoaded() || empty($_sectionName))
        {
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
     * @param bool $_stopRebuildOfCache If Set to True, the cache wont be rebuilt after updating of value.
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertKey($_sectionName, $_keyName, $_keyValue, $_stopRebuildOfCache = false)
    {
        if (empty($_sectionName) || empty($_keyName))
        {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('section' => $_sectionName, 'vkey' => $_keyName, 'data' => $_keyValue), 'INSERT');

        $this->UpdateLocalCache($_sectionName, $_keyName, $_keyValue);

        if (!$_stopRebuildOfCache)
        {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Updates a given setting key, if empty.. deletes it, if it doesnt exist.. attempts to create one
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Setting Key Name
     * @param mixed $_keyValue The Setting Key Value
     * @param bool $_stopRebuildOfCache If Set to True, the cache wont be rebuilt after updating of value.
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function UpdateKey($_sectionName, $_keyName, $_keyValue, $_stopRebuildOfCache = false)
    {

        if (empty($_sectionName) || empty($_keyName))
        {
            return false;
        }

        $_settingContainer = $this->Database->QueryFetch("SELECT section, vkey FROM ". TABLE_PREFIX ."settings WHERE section = '". $this->Database->Escape($_sectionName) ."' AND vkey = '". $this->Database->Escape($_keyName) ."'", 5);

        if ($_settingContainer["section"] == $_sectionName && $_settingContainer["vkey"] == $_keyName)
        {
            $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('data' => $_keyValue), 'UPDATE', "section = '". $this->Database->Escape($_sectionName) ."' AND vkey = '". $this->Database->Escape($_keyName) ."'");

            $this->UpdateLocalCache($_sectionName, $_keyName, $_keyValue);

            if (!$_stopRebuildOfCache)
            {
                self::RebuildCache();
            }

            return true;
        } else {
            return $this->InsertKey($_sectionName, $_keyName, $_keyValue, $_stopRebuildOfCache);
        }
    }

    /**
     * Delete the given setting key
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param string $_keyName The Setting Key Name
     * @param bool $_stopRebuildOfCache If Set to True, the cache wont be rebuilt after updating of value.
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteKey($_sectionName, $_keyName, $_stopRebuildOfCache = false)
    {
        if (empty($_sectionName) || empty($_keyName))
        {
            return false;
        }

        $this->Database->Query("DELETE FROM ". TABLE_PREFIX ."settings WHERE section = '". $this->Database->Escape($_sectionName) ."' AND vkey = '". $this->Database->Escape($_keyName) ."'", 5);

        $this->DeleteLocalCache($_sectionName, $_keyName);

        if (!$_stopRebuildOfCache)
        {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Delete the given Setting section
     *
     * @author Varun Shoor
     * @param string $_sectionName The Setting Section Name
     * @param bool $_stopRebuildOfCache If Set to True, the cache wont be rebuilt after updating of value.
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteSection($_sectionName, $_stopRebuildOfCache = false)
    {
        if (empty($_sectionName))
        {
            return false;
        }

        $this->Database->Query("DELETE FROM ". TABLE_PREFIX ."settings WHERE section = '". $this->Database->Escape($_sectionName) ."'", 5);

        $this->DeleteLocalCacheSection($_sectionName);

        if (!$_stopRebuildOfCache)
        {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Replace the local cache with a new container
     *
     * @author Varun Shoor
     * @param array $_settingsContainer The New Settings Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function ReplaceLocalCache($_settingsContainer)
    {
        if (!_is_array($_settingsContainer))
        {
            return false;
        }

        $this->_settingsCache = $_settingsContainer;

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
        if (!$this->GetIsClassLoaded() || empty($_sectionName) || empty($_keyName))
        {
            return false;
        }

        if (isset($this->_settingsCache[$_sectionName]) && isset($this->_settingsCache[$_sectionName][$_keyName]))
        {
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
        if (!$this->GetIsClassLoaded() || empty($_sectionName))
        {
            return false;
        }

        if (isset($this->_settingsCache[$_sectionName]))
        {
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
        if (empty($_keyName) || !$this->GetIsClassLoaded())
        {
            return null;
        }

        return $this->_settingsCache['settings'][$_keyName] ?? null;
    }

    /**
     * @param string $_keyName
     * @return bool
     */
    public function GetBool($_keyName): bool
    {
        return (bool) $this->Get($_keyName);
    }

    /**
     * @param string $_keyName
     * @return int
     */
    public function GetInt($_keyName): int
    {
        return (int) $this->Get($_keyName);
    }

    /**
     * @param string $_keyName
     * @return string
     */
    public function GetString($_keyName): string
    {
        return (string) $this->Get($_keyName);
    }

    /**
     * Retrieve the Settings Container
     *
     * @author Varun Shoor
     * @return mixed "Settings Cache" (ARRAY) on Success, "false" otherwise
     */
    public function GetSettings()
    {
        if (!$this->GetIsClassLoaded())
        {
            return null;
        }

        return $this->_settingsCache['settings'] ?? false;
    }

    /**
     * Rebuilds the Settings Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT section, vkey, data FROM ". TABLE_PREFIX ."settings ORDER BY settingid ASC", 5);
        while ($_SWIFT->Database->NextRecord(5))
        {
            $_unserializedContainer = $_SWIFT->Database->Record5['data'];
            if (substr($_unserializedContainer, 0, strlen('SERIALIZED:')) == 'SERIALIZED:') {
                $_unserializedContainer = mb_unserialize(substr($_unserializedContainer, strlen('SERIALIZED:')));
            }

            if (_is_array($_unserializedContainer))
            {
                $_cache[$_SWIFT->Database->Record5["section"]][$_SWIFT->Database->Record5["vkey"]] = $_unserializedContainer;
            } else {
                $_cache[$_SWIFT->Database->Record5["section"]][$_SWIFT->Database->Record5["vkey"]] = $_SWIFT->Database->Record5["data"];
            }
        }

        if (!empty($_SWIFT->Settings) && $_SWIFT->Settings instanceof SWIFT_Settings)
        {
            $_SWIFT->Settings->ReplaceLocalCache($_cache);
        }

        $_SWIFT->Cache->Update('settingscache', $_cache);

        return true;
    }
}
