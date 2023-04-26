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

namespace Tickets\Models\Lock;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Lock Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketLock extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketlocks';
    const PRIMARY_KEY        =    'ticketid';

    const TABLE_STRUCTURE    =    "ticketid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketid';
    const INDEXTYPE_1        =    'UNIQUE';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Lock_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Lock_Exception('Failed to load Ticket Object');
        }
    }

    /**
     * Retrieves the Ticket ID
     *
     * @author Varun Shoor
     * @return mixed "ticketid" on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function GetTicketID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketlocks WHERE ticketid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketid']) && !empty($_dataStore['ticketid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketid']) || empty($this->_dataStore['ticketid']))
            {
                throw new SWIFT_Lock_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Lock_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Lock_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Lock
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket object pointer
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff object pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !$_SWIFT_StaffObject instanceof SWIFT_Staff)
        {
            throw new SWIFT_Lock_Exception(SWIFT_INVALIDDATA);
        }

        $_queryResult = $_SWIFT->Database->Replace(TABLE_PREFIX . 'ticketlocks', array('ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()),
            'staffid' => (int) ($_SWIFT_StaffObject->GetStaffID()), 'dateline' => DATENOW), array('ticketid'));

        return true;
    }

    /**
     * Delete the Ticket Lock record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Locks
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketlocks WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");

        return true;
    }

    /**
     * Delete on Ticket ID
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        self::DeleteList($_ticketIDList);
    }

    /**
     * Retrieve the lock object on ticket id
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @return mixed "SWIFT_TicketLock" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Lock_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            throw new SWIFT_Lock_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketLockObject = false;

        try
        {
            $_SWIFT_TicketLockObject = new SWIFT_TicketLock(new SWIFT_DataID($_SWIFT_TicketObject->GetTicketID()));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        return $_SWIFT_TicketLockObject;
    }
}
?>
