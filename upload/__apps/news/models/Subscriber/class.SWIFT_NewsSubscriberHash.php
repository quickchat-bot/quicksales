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

namespace News\Models\Subscriber;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The News Subscriber Hash Model
 *
 * @author Varun Shoor
 */
class SWIFT_NewsSubscriberHash extends SWIFT_Model
{
    const TABLE_NAME        =    'newssubscriberhash';
    const PRIMARY_KEY        =    'newssubscriberhashid';

    const TABLE_STRUCTURE    =    "newssubscriberhashid I PRIMARY AUTO NOTNULL,
                                newssubscriberid I DEFAULT '0' NOTNULL,
                                hash C(50) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'newssubscriberid';
    const INDEX_2            =    'hash';


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
            throw new SWIFT_Exception('Failed to load News Subscriber Hash Object');

            $this->SetIsClassLoaded(false);
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
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'newssubscriberhash', $this->GetUpdatePool(), 'UPDATE', "newssubscriberhashid = '" . ($this->GetNewsSubscriberHashID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the News Subscriber Hash ID
     *
     * @author Varun Shoor
     * @return mixed "newssubscriberhashid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNewsSubscriberHashID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['newssubscriberhashid'];
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
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newssubscriberhash WHERE newssubscriberhashid = '" . ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['newssubscriberhashid']) && !empty($_dataStore['newssubscriberhashid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['newssubscriberhashid']) || empty($this->_dataStore['newssubscriberhashid']))
            {
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
        if (!$this->GetIsClassLoaded())
        {
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new News Subscriber Hash
     *
     * @author Varun Shoor
     * @param int $_newsSubscriberID
     * @return string The Unique Hash
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_newsSubscriberID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_newsSubscriberID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_hash = BuildHash();
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'newssubscriberhash', array('newssubscriberid' => ($_newsSubscriberID), 'dateline' => DATENOW, 'hash' => $_hash), 'INSERT');

        return $_hash;
    }

    /**
     * Delete the News Subscriber Hash record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetNewsSubscriberHashID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of News Subscriber Hashes
     *
     * @author Varun Shoor
     * @param array $_newsSubscriberHashIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_newsSubscriberHashIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsSubscriberHashIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "newssubscriberhash WHERE newssubscriberhashid IN (" . BuildIN($_newsSubscriberHashIDList) . ")");

        return true;
    }

    /**
     * Retrieve the News Subscriber Hash Object based on the Unique Hash
     *
     * @author Varun Shoor
     * @param string $_hash The Unique Hash
     * @return null|SWIFT_NewsSubscriberHash The News Subscriber Hash Object
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_hash)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_newsSubcriberHashContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newssubscriberhash WHERE hash = '" . $_SWIFT->Database->Escape($_hash) . "'");
        if (isset($_newsSubcriberHashContainer['newssubscriberhashid']))
        {
            return new SWIFT_NewsSubscriberHash(new SWIFT_DataStore($_newsSubcriberHashContainer));
        }

        return null;
    }
}
