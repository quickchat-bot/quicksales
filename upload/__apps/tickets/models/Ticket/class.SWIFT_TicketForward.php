<?php /** @noinspection SqlResolve */
/** @noinspection SqlNoDataSourceInspection */

/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Werner Garcia
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Tickets\Models\Ticket;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;

/**
 * The Ticket Forward Management Class
 *
 * @author Werner Garcia
 */
class SWIFT_TicketForward extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketforwards';
    const PRIMARY_KEY        =    'ticketforwardid';

    const TABLE_STRUCTURE    =    "ticketforwardid I PRIMARY AUTO NOTNULL,
                                   ticketpostid I DEFAULT '0' NOTNULL,
                                   email C(255) DEFAULT '' NOTNULL";

    const INDEX_1            =    'ticketpostid';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Werner Garcia
     * @param int $_ticketForwardID The Ticket Forward ID
     * @throws SWIFT_Ticket_Exception If the Record could not be loaded
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_ticketForwardID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketForwardID)) {
            throw new SWIFT_Ticket_Exception('Failed to load Ticket Forward ID: ' . $_ticketForwardID);
        }
    }

    /**
     * Destructor
     *
     * @author Werner Garcia
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Werner Garcia
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketforwards', $this->GetUpdatePool(), 'UPDATE', "ticketforwardid = '" . (int) ($this->GetTicketForwardID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Forward ID
     *
     * @author Werner Garcia
     * @return mixed "ticketforwardid" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetTicketForwardID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketforwardid'];
    }

    /**
     * Load the Data
     *
     * @author Werner Garcia
     * @param int $_ticketForwardID The Ticket Forward ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketForwardID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketforwards WHERE ticketforwardid = '" . $_ticketForwardID . "'");
        if (isset($_dataStore['ticketforwardid']) && !empty($_dataStore['ticketforwardid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Werner Garcia
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Werner Garcia
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Forward record
     *
     * @param int $_ticketPostId
     * @param string $_emailAddress The Email Address
     * @return mixed "_ticketForwardID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception
     * @author   Werner Garcia
     */
    public static function Create($_ticketPostId, $_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailAddress))
        {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        try {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketforwards', array('email' => mb_strtolower($_emailAddress), 'ticketpostid' => (int) ($_ticketPostId)), 'INSERT');
            $_ticketForwardID = $_SWIFT->Database->Insert_ID();

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_ticketForwardID)
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketForwardID;
    }

    /**
     * Delete the Ticket Forward record
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     * @author Werner Garcia
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketForwardID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Forwards
     *
     * @param array $_ticketForwardIDList The Ticket Forward ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @author Werner Garcia
     */
    public static function DeleteList($_ticketForwardIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketForwardIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketforwards WHERE ticketforwardid IN (" . BuildIN($_ticketForwardIDList) . ")");

        return true;
    }

    /**
     * Create Ticket Forwards for the given email list 
     *
     * @param int $_ticketPostId
     * @param array $_emailList The Email List
     * @return mixed "_ticketForwardIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Ticket_Exception
     * @author Werner Garcia
     */
    public static function CreateFromEmailList($_ticketPostId, $_emailList)
    {
        if (!_is_array($_emailList))
        {
            return array();
        }

        $_ticketForwardIDList = [];

        foreach ($_emailList as $_key => $_email) {
            $_email = mb_strtolower($_email);
            $_ticketForwardID = self::Create($_ticketPostId, $_email);
            $_ticketForwardIDList[$_ticketForwardID] = $_ticketForwardID;
        }

        return $_ticketForwardIDList;
    }

    /**
     * Retrieve the list of forward records on a given ticket post
     *
     * @author Werner Garcia
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object Pointer
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicketPost(SWIFT_TicketPost $_SWIFT_TicketPostObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketForwardContainer = [];
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketforwards WHERE ticketpostid = '" . (int) ($_SWIFT_TicketPostObject->GetTicketPostID()) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketForwardContainer[$_SWIFT->Database->Record['ticketforwardid']] = $_SWIFT->Database->Record;
        }

        return $_ticketForwardContainer;
    }

    /**
     * Retrieve the list of recipients on a given ticket post
     *
     * @author Werner Garcia
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object Pointer
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveEmailListOnTicketPost(SWIFT_TicketPost $_SWIFT_TicketPostObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_emailContainer = [];
        $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "ticketforwards WHERE ticketpostid = '" . (int) ($_SWIFT_TicketPostObject->GetTicketPostID()) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_emailContainer[] = $_SWIFT->Database->Record['email'];
        }

        return $_emailContainer;
    }
}
