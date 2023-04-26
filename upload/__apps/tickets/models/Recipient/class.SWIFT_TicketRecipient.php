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

namespace Tickets\Models\Recipient;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketEmail;

/**
 * The Ticket Recipient Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketRecipient extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketrecipients';
    const PRIMARY_KEY        =    'ticketrecipientid';

    const TABLE_STRUCTURE    =    "ticketrecipientid I PRIMARY AUTO NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                ticketemailid I DEFAULT '0' NOTNULL,
                                recipienttype I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketid, ticketemailid';
    const INDEXTYPE_1        =    'UNIQUE';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_THIRDPARTY = 1;
    const TYPE_CC = 2;
    const TYPE_BCC = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketRecipientID The Ticket Recipient ID
     * @throws SWIFT_Recipient_Exception If the Record could not be loaded
     */
    public function __construct($_ticketRecipientID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketRecipientID)) {
            throw new SWIFT_Recipient_Exception('Failed to load Ticket Recipient ID: ' . $_ticketRecipientID);
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
     * @throws SWIFT_Recipient_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketrecipients', $this->GetUpdatePool(), 'UPDATE', "ticketrecipientid = '" . (int) ($this->GetTicketRecipientID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Recipient ID
     *
     * @author Varun Shoor
     * @return mixed "ticketrecipientid" on Success, "false" otherwise
     * @throws SWIFT_Recipient_Exception If the Class is not Loaded
     */
    public function GetTicketRecipientID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Recipient_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketrecipientid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketRecipientID The Ticket Recipient ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketRecipientID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketrecipients WHERE ticketrecipientid = '" . $_ticketRecipientID . "'");
        if (isset($_dataStore['ticketrecipientid']) && !empty($_dataStore['ticketrecipientid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Recipient_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Recipient_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Recipient_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Recipient_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Recipient_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Recipient Type
     *
     * @author Varun Shoor
     * @param mixed $_recipientType The Recipient Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_recipientType)
    {
        if ($_recipientType == self::TYPE_THIRDPARTY || $_recipientType == self::TYPE_CC || $_recipientType == self::TYPE_BCC)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Recipient
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket object pointer
     * @param mixed $_recipientType The Ticket Recipient Type
     * @param array $_emailList The Email List to add
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, $_recipientType, $_emailList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !self::IsValidType($_recipientType) || !_is_array($_emailList))
        {
            throw new SWIFT_Recipient_Exception(SWIFT_INVALIDDATA);
        }

        // Dont let it add a recipient which exists as a queue
        $_queueCache = $_SWIFT->Cache->Get('queuecache');

        $_finalEmailList = array();
        foreach ($_emailList as $_listEmailAddress) {
            $_listEmailAddress = trim(mb_strtolower($_listEmailAddress));

            if (isset($_queueCache['pointer'][$_listEmailAddress]))
            {
                continue;
            }

            $_finalEmailList[] = $_listEmailAddress;
        }

        // Retrieve the ticket email ids
        $_ticketEmailIDList = SWIFT_TicketEmail::RetrieveIDListOnEmailListAndCreateIfNotExists($_finalEmailList);

        // Now that we have the ID list, we need to check for records that dont exist
        if (count($_ticketEmailIDList))
        {
            $_SWIFT->Database->Query("SELECT ticketemailid FROM " . TABLE_PREFIX . "ticketrecipients WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "' AND ticketemailid IN (" . BuildIN($_ticketEmailIDList) . ")");
            while ($_SWIFT->Database->NextRecord())
            {
                // Clear all records that already exist
                unset($_ticketEmailIDList[$_SWIFT->Database->Record['ticketemailid']]);
            }
        }

        if (!count($_ticketEmailIDList))
        {
            return false;
        }

        // Attempt to create the recipients
        foreach ($_ticketEmailIDList as $_key => $_val)
        {
            try {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketrecipients', array('ticketid' => (int) ($_SWIFT_TicketObject->GetTicketID()), 'ticketemailid' => (int) ($_val), 'recipienttype' => (int) ($_recipientType)), 'INSERT');
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {


                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve the list of recipients on a given ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketRecipientContainer = $_ticketEmailIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketrecipients WHERE ticketid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketRecipientContainer[$_SWIFT->Database->Record['ticketrecipientid']] = $_SWIFT->Database->Record;

            if (!in_array($_SWIFT->Database->Record['ticketemailid'], $_ticketEmailIDList)) {
                $_ticketEmailIDList[] = $_SWIFT->Database->Record['ticketemailid'];
            }
        }

        if (!count($_ticketRecipientContainer)) {
            return [];
        }

        $_ticketEmailMap = SWIFT_TicketEmail::RetrieveEmailListOnIDList($_ticketEmailIDList);

        $_finalRecipientContainer = array();
        foreach ($_ticketRecipientContainer as $_ticketRecipientID => $_recipientContainer) {
            if (!isset($_ticketEmailMap[$_recipientContainer['ticketemailid']])) {
                continue;
            }

            $_finalRecipientContainer[$_recipientContainer['recipienttype']][$_ticketRecipientID] = $_ticketEmailMap[$_recipientContainer['ticketemailid']];
        }

        return $_finalRecipientContainer;
    }

    /**
     * Delete the ticket recipient record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Recipient_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Recipient_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketRecipientID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of ticket recipients
     *
     * @author Varun Shoor
     * @param array $_ticketRecipientIDList The Ticket Recipient ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketRecipientIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketRecipientIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketrecipients WHERE ticketrecipientid IN (" . BuildIN($_ticketRecipientIDList) . ")");

        return true;
    }

    /**
     * Delete the recipients based on ticket id
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @param bool $_ignoreThirdParty (OPTIONAL) Whether to ignore third party recipients
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList, $_ignoreThirdParty = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_ticketRecipientIDList = $_ticketEmailIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketrecipients
            WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if ($_SWIFT->Database->Record['recipienttype'] == self::TYPE_THIRDPARTY && $_ignoreThirdParty == true)
            {
                continue;
            }

            $_ticketRecipientIDList[] = (int) ($_SWIFT->Database->Record['ticketrecipientid']);
            $_ticketEmailIDList[] = $_SWIFT->Database->Record['ticketemailid'];
        }

        if (!_is_array($_ticketRecipientIDList))
        {
            return false;
        }

        self::DeleteList($_ticketRecipientIDList);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1186 While replying to a ticket in Staff CP, 'To' and 'CC' fields are also showing those emails addresses of the users which were already deleted
         *
         * Comments: None
         */
        SWIFT_TicketEmail::DeleteForRecipient($_ticketEmailIDList);

        return true;
    }

    /**
     * Replace the current ticket id all tickets with the new one
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Old Ticket ID List
     * @param SWIFT_Ticket $_SWIFT_ParentTicketObject The Parent Ticket Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ReplaceTicket($_ticketIDList, SWIFT_Ticket $_SWIFT_ParentTicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ParentTicketObject instanceof SWIFT_Ticket || !$_SWIFT_ParentTicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        try
        {
            $_ticketRecipientIDList = $_ticketEmailIDList = array();
            $_SWIFT->Database->Query("SELECT ticketrecipientid, ticketemailid FROM " . TABLE_PREFIX . "ticketrecipients
                WHERE ticketid IN (" . (int) ($_SWIFT_ParentTicketObject->GetTicketID()) . ', ' . BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord())
            {
                if (in_array($_SWIFT->Database->Record['ticketemailid'], $_ticketEmailIDList)) {
                    $_ticketRecipientIDList[] = $_SWIFT->Database->Record['ticketrecipientid'];
                } else {
                    $_ticketEmailIDList[] = $_SWIFT->Database->Record['ticketemailid'];
                }
            }

            if (_is_array($_ticketRecipientIDList))
            {
                self::DeleteList($_ticketRecipientIDList);
            }

            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketrecipients', array('ticketid' => (int) ($_SWIFT_ParentTicketObject->GetTicketID())),
                    'UPDATE', "ticketid IN (" . BuildIN($_ticketIDList) . ")");
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        return true;
    }
}
?>
