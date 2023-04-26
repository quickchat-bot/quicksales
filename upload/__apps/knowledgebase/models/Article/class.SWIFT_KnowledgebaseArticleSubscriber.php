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

namespace Knowledgebase\Models\Article;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Knowledgebase Article Subscriber Management Model
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseArticleSubscriber extends SWIFT_Model
{
    const TABLE_NAME        =    'kbarticlesubscribers';
    const PRIMARY_KEY        =    'kbarticlesubscriberid';

    const TABLE_STRUCTURE    =    "kbarticlesubscriberid I PRIMARY AUTO NOTNULL,
                                kbarticleid I DEFAULT '0' NOTNULL,
                                creator I2 DEFAULT '0' NOTNULL,
                                creatorid I DEFAULT '0' NOTNULL,
                                email C(255) DEFAULT '0' NOTNULL";

    const INDEX_1            =    'kbarticleid';


    protected $_dataStore = array();

    // Core Constants
    const CREATOR_USER = 1;
    const CREATOR_STAFF = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Knowledgebase Article Subscriber Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
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
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'kbarticlesubscribers', $this->GetUpdatePool(), 'UPDATE', "kbarticlesubscriberid = '" . ($this->GetKnowledgebaseArticleSubscriberID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Knowledgebase Article Subscriber ID
     *
     * @author Varun Shoor
     * @return mixed "kbarticlesubscriberid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetKnowledgebaseArticleSubscriberID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['kbarticlesubscriberid'];
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
        $isClassLoaded = $_SWIFT_DataObject->GetIsClassLoaded();
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $isClassLoaded)
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "kbarticlesubscribers WHERE kbarticlesubscriberid = '" . ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['kbarticlesubscriberid']) && !empty($_dataStore['kbarticlesubscriberid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $isClassLoaded) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['kbarticlesubscriberid']) || empty($this->_dataStore['kbarticlesubscriberid']))
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
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid creator
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreator($_creatorType)
    {
        return $_creatorType == self::CREATOR_STAFF || $_creatorType == self::CREATOR_USER;
    }

    /**
     * Create a new Knowledgebase Article Subscriber
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseArticleID The Knowledgebase Article ID
     * @param mixed $_creatorType The Creator Type
     * @param int $_creatorID The Creator ID
     * @param string $_emailAddress The Email Address
     * @return int The Knowledgebase Article Subscriber ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_knowledgebaseArticleID, $_creatorType, $_creatorID, $_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_knowledgebaseArticleID) || !self::IsValidCreator($_creatorType) || empty($_emailAddress))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'kbarticlesubscribers', array('kbarticleid' => ($_knowledgebaseArticleID), 'creator' => ($_creatorType),
            'creatorid' => ($_creatorID), 'email' => $_emailAddress), 'INSERT');
        $_knowledgebaseArticleSubscriberID = $_SWIFT->Database->Insert_ID();

        if (!$_knowledgebaseArticleSubscriberID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_knowledgebaseArticleSubscriberID;
    }

    /**
     * Delete the Knowledgebase Article Subscriber record
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

        self::DeleteList(array($this->GetKnowledgebaseArticleSubscriberID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Knowledgebase Article Subscribers
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseArticleSubscriberIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_knowledgebaseArticleSubscriberIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseArticleSubscriberIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbarticlesubscribers WHERE kbarticlesubscriberid IN (" . BuildIN($_knowledgebaseArticleSubscriberIDList) . ")");

        return true;
    }

    /**
     * Delete a list of Knowledgebase Article Subscribers based on a list of Knowledgebase Article IDs
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseArticleIDList The Knowledgebase Article ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnKnowledgebaseArticle($_knowledgebaseArticleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseArticleIDList))
        {
            return false;
        }

        $_knowledgebaseArticleSubscriberIDList = array();
        $_SWIFT->Database->Query("SELECT kbarticlesubscriberid FROM " . TABLE_PREFIX . "kbarticlesubscribers WHERE kbarticleid IN (" . BuildIN($_knowledgebaseArticleIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseArticleSubscriberIDList[] = ($_SWIFT->Database->Record['kbarticlesubscriberid']);
        }

        if (!count($_knowledgebaseArticleSubscriberIDList))
        {
            return false;
        }

        self::DeleteList($_knowledgebaseArticleSubscriberIDList);

        return true;
    }
}
