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

namespace Tickets\Models\Filter;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Filter\SWIFT_TicketFilterField;

/**
 * The Ticket Filter Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketFilter extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketfilters';
    const PRIMARY_KEY        =    'ticketfilterid';

    const TABLE_STRUCTURE    =    "ticketfilterid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastactivity I DEFAULT '0' NOTNULL,
                                filtertype I2 DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                restrictstaffgroupid I DEFAULT '0' NOTNULL,
                                criteriaoptions I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'filtertype, staffid';
    const INDEX_2            =    'filtertype, restrictstaffgroupid';
    const INDEX_3            =    'staffid';
    const INDEX_4            =    'title, ticketfilterid'; // Unified Search


    protected $_dataStore = array();

    // Core Constants
    const TYPE_PUBLIC = '1';
    const TYPE_PRIVATE = '0';

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
            throw new SWIFT_Exception('Failed to load Ticket Filter Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketfilters', $this->GetUpdatePool(), 'UPDATE', "ticketfilterid = '" . (int) ($this->GetTicketFilterID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Filter ID
     *
     * @author Varun Shoor
     * @return mixed "ticketfilterid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketFilterID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketfilterid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketfilters WHERE ticketfilterid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketfilterid']) && !empty($_dataStore['ticketfilterid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketfilterid']) || empty($this->_dataStore['ticketfilterid']))
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
     * Check to see if its a valid filter type
     *
     * @author Varun Shoor
     * @param mixed $_filterType The Filter Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_filterType)
    {
        if ($_filterType == self::TYPE_PRIVATE || $_filterType == self::TYPE_PUBLIC)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Ticket Filter
     *
     * @author Varun Shoor
     * @param string $_filterTitle The Filter Title
     * @param mixed $_filterType The Filter Type
     * @param int $_restrictStaffGroupID Restrict visibility to select staff groups
     * @param mixed $_criteriaOptions The Rule Criteria OPtions
     * @param int $_staffID The Creator Staff ID
     * @param array $_ticketFilterFieldContainer The Ticket Filter Field Container
     * @return int The Ticket Filter ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_filterTitle, $_filterType, $_restrictStaffGroupID, $_criteriaOptions, $_staffID, $_ticketFilterFieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_filterTitle) || !self::IsValidType($_filterType) || !_is_array($_ticketFilterFieldContainer))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketfilters', array('title' => $_filterTitle, 'filtertype' => (int) ($_filterType),
            'restrictstaffgroupid' => $_restrictStaffGroupID, 'criteriaoptions' => (int) ($_criteriaOptions), 'staffid' => $_staffID,
            'dateline' => DATENOW), 'INSERT');
        $_ticketFilterID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketFilterID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        foreach ($_ticketFilterFieldContainer as $_value)
        {
            SWIFT_TicketFilterField::Create($_ticketFilterID, $_value[0], $_value[1], $_value[2]);
        }

        self::RebuildCache();

        return $_ticketFilterID;
    }

    /**
     * Update Ticket Filter Record
     *
     * @author Varun Shoor
     * @param string $_filterTitle The Filter Title
     * @param mixed $_filterType The Filter Type
     * @param int $_restrictStaffGroupID Restrict visibility to select staff groups
     * @param mixed $_criteriaOptions The Rule Criteria OPtions
     * @param int $_staffID The Creator Staff ID
     * @param array $_ticketFilterFieldContainer The Ticket Filter Field Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_filterTitle, $_filterType, $_restrictStaffGroupID, $_criteriaOptions, $_staffID, $_ticketFilterFieldContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        if (empty($_filterTitle) || !self::IsValidType($_filterType) || !_is_array($_ticketFilterFieldContainer))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_filterTitle);
        $this->UpdatePool('filtertype', (int) ($_filterType));
        $this->UpdatePool('restrictstaffgroupid', $_restrictStaffGroupID);
        $this->UpdatePool('criteriaoptions', (int) ($_criteriaOptions));
        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('dateline', DATENOW);
        $this->ProcessUpdatePool();

        SWIFT_TicketFilterField::DeleteOnTicketFilter(array($this->GetTicketFilterID()));
        foreach ($_ticketFilterFieldContainer as $_value)
        {
            SWIFT_TicketFilterField::Create($this->GetTicketFilterID(), $_value[0], $_value[1], $_value[2]);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Update the last activity for the given filter
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateLastActivity()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('lastactivity', DATENOW);
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Ticket Filter record
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

        self::DeleteList(array($this->GetTicketFilterID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Filters
     *
     * @author Varun Shoor
     * @param array $_ticketFilterIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketFilterIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketFilterIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketfilters WHERE ticketfilterid IN (" . BuildIN($_ticketFilterIDList) . ")");

        // Clear the linked fields
        SWIFT_TicketFilterField::DeleteOnTicketFilter($_ticketFilterIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Ticket Filter Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfilters ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_cache[$_SWIFT->Database->Record['ticketfilterid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('ticketfiltercache', $_cache);

        return true;
    }

    /**
     * Retrieve the Menu container
     *
     * @author Varun Shoor
     * @return array The Menu Container
     */
    public static function RetrieveMenu()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_finalTicketFilterCache = array();

        $_ticketFilterCache = $_SWIFT->Cache->Get('ticketfiltercache');
        foreach ($_ticketFilterCache as $_ticketFilterID => $_ticketFilterContainer)
        {
            if (($_ticketFilterContainer['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC && $_ticketFilterContainer['restrictstaffgroupid'] == '0') ||
                    ($_ticketFilterContainer['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC && $_ticketFilterContainer['restrictstaffgroupid'] == $_SWIFT->Staff->GetProperty('staffgroupid')) ||
                    ($_ticketFilterContainer['filtertype'] == SWIFT_TicketFilter::TYPE_PRIVATE && $_ticketFilterContainer['staffid'] == $_SWIFT->Staff->GetStaffID()))
            {
                $_finalTicketFilterCache[$_ticketFilterID] = $_ticketFilterContainer;
                $_finalTicketFilterCache[$_ticketFilterID]['title'] = htmlspecialchars($_finalTicketFilterCache[$_ticketFilterID]['title']);
            }
        }

        return $_finalTicketFilterCache;
    }
}
?>
