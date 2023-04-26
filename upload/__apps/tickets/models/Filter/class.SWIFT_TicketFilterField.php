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

namespace Tickets\Models\Filter;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Ticket Filter Field Criteria Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketFilterField extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketfilterfields';
    const PRIMARY_KEY        =    'ticketfilterfieldid';

    const TABLE_STRUCTURE    =    "ticketfilterfieldid I PRIMARY AUTO NOTNULL,
                                ticketfilterid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                fieldtitle C(255) DEFAULT '' NOTNULL,
                                fieldoper I DEFAULT '0' NOTNULL,
                                fieldvalue C(255) DEFAULT '' NOTNULL";

    const INDEX_1            =    'ticketfilterid';


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
            throw new SWIFT_Exception('Failed to load Ticket Filter Field Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketfilterfields', $this->GetUpdatePool(), 'UPDATE', "ticketfilterfieldid = '" . (int) ($this->GetTicketFilterFieldID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Filter Field ID
     *
     * @author Varun Shoor
     * @return mixed "ticketfilterfieldid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketFilterFieldID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketfilterfieldid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketfilterfields WHERE ticketfilterfieldid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketfilterfieldid']) && !empty($_dataStore['ticketfilterfieldid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketfilterfieldid']) || empty($this->_dataStore['ticketfilterfieldid']))
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
     * Create a new Ticket Filter Field Record
     *
     * @author Varun Shoor
     * @param int $_ticketFilterID
     * @param string $_fieldTitle
     * @param string $_fieldOper
     * @param string $_fieldValue
     * @return int Ticket Filter Field ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_ticketFilterID, $_fieldTitle, $_fieldOper, $_fieldValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketFilterID) || empty($_fieldTitle) || empty($_fieldOper))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketfilterfields', array('dateline' => DATENOW, 'ticketfilterid' => $_ticketFilterID,
                'fieldtitle' => $_fieldTitle, 'fieldoper' => $_fieldOper, 'fieldvalue' => $_fieldValue), 'INSERT');
        $_ticketFilterFieldID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketFilterFieldID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketFilterFieldID;
    }

    /**
     * Delete the Ticket Filter Field record
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

        self::DeleteList(array($this->GetTicketFilterFieldID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Filter Fields
     *
     * @author Varun Shoor
     * @param array $_ticketFilterFieldIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketFilterFieldIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketFilterFieldIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketfilterfields WHERE ticketfilterfieldid IN (" . BuildIN($_ticketFilterFieldIDList) . ")");

        return true;
    }

    /**
     * Delete the filter fields based on a list of ticket filter ids
     *
     * @author Varun Shoor
     * @param array $_ticketFilterIDList The Ticket Filter ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicketFilter($_ticketFilterIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketFilterIDList))
        {
            return false;
        }

        $_ticketFilterFieldIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfilterfields WHERE ticketfilterid IN (" . BuildIN($_ticketFilterIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketFilterFieldIDList[] = $_SWIFT->Database->Record['ticketfilterfieldid'];
        }

        if (!_is_array($_ticketFilterFieldIDList))
        {
            return false;
        }

        self::DeleteList($_ticketFilterFieldIDList);

        return true;
    }
}
?>
