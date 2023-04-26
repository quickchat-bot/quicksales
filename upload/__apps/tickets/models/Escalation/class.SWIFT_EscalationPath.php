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

namespace Tickets\Models\Escalation;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Escalation Path Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_EscalationPath extends SWIFT_Model
{
    const TABLE_NAME        =    'escalationpaths';
    const PRIMARY_KEY        =    'escalationpathid';

    const TABLE_STRUCTURE    =    "escalationpathid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                slaplanid I DEFAULT '0' NOTNULL,
                                slaplantitle C(255) DEFAULT '' NOTNULL,
                                escalationruleid I DEFAULT '0' NOTNULL,
                                escalationruletitle C(255) DEFAULT '' NOTNULL,
                                ownerstaffid I DEFAULT '0' NOTNULL,
                                ownerstaffname C(255) DEFAULT '' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                departmenttitle C(255) DEFAULT '' NOTNULL,
                                ticketstatusid I DEFAULT '0' NOTNULL,
                                ticketstatustitle C(255) DEFAULT '' NOTNULL,
                                priorityid I DEFAULT '0' NOTNULL,
                                prioritytitle C(255) DEFAULT '' NOTNULL,
                                tickettypeid I DEFAULT '0' NOTNULL,
                                tickettypetitle C(255) DEFAULT '' NOTNULL,
                                flagtype I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketid';


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
            throw new SWIFT_Exception('Failed to load Escalation Path Object');
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'escalationpaths', $this->GetUpdatePool(), 'UPDATE', "escalationpathid = '" . (int) ($this->GetEscalationPathID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Escalation Path ID
     *
     * @author Varun Shoor
     * @return mixed "escalationpathid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetEscalationPathID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['escalationpathid'];
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
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "escalationpaths WHERE escalationpathid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['escalationpathid']) && !empty($_dataStore['escalationpathid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['escalationpathid']) || empty($this->_dataStore['escalationpathid']))
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
     * Create a new Escalation Path
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param int $_slaPlanID
     * @param int $_escalationRuleID
     * @param int $_ownerStaffID
     * @param int $_departmentID
     * @param int $_ticketStatusID
     * @param int $_ticketPriorityID
     * @param int $_ticketTypeID
     * @param int $_flagType
     * @return int Escalation Path ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_Ticket $_SWIFT_TicketObject, $_slaPlanID, $_escalationRuleID, $_ownerStaffID, $_departmentID, $_ticketStatusID,
            $_ticketPriorityID, $_ticketTypeID, $_flagType = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_slaPlanCache = $_SWIFT->Cache->Get('slaplancache');
        $_escalationRuleCache = $_SWIFT->Cache->Get('escalationrulecache');
        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');
        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || empty($_slaPlanID) || empty($_escalationRuleID) ||
                !isset($_slaPlanCache[$_slaPlanID]) || !isset($_escalationRuleCache[$_escalationRuleID]))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ownerStaffName = $_departmentTitle = $_ticketStatusTitle = $_priorityTitle = $_ticketTypeTitle = '';

        if ($_ownerStaffID == '0')
        {
            $_ownerStaffName = $_SWIFT->Language->Get('unassigned');
        } else if (isset($_staffCache[$_ownerStaffID])) {
            $_ownerStaffName = $_staffCache[$_ownerStaffID]['fullname'];
        }

        if (empty($_ownerStaffName))
        {
            $_ownerStaffName = $_SWIFT->Language->Get('unassigned');
        }

        if (isset($_departmentCache[$_departmentID]))
        {
            $_departmentTitle = $_departmentCache[$_departmentID]['title'];
        }

        if (isset($_ticketStatusCache[$_ticketStatusID]))
        {
            $_ticketStatusTitle = $_ticketStatusCache[$_ticketStatusID]['title'];
        }

        if (isset($_ticketPriorityCache[$_ticketPriorityID]))
        {
            $_priorityTitle = $_ticketPriorityCache[$_ticketPriorityID]['title'];
        }

        if (isset($_ticketTypeCache[$_ticketTypeID]))
        {
            $_ticketTypeTitle = $_ticketTypeCache[$_ticketTypeID]['title'];
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'escalationpaths', array('dateline' => DATENOW, 'ticketid' => $_SWIFT_TicketObject->GetTicketID(),
                'slaplanid' => $_slaPlanID, 'slaplantitle' => $_slaPlanCache[$_slaPlanID]['title'], 'escalationruleid' => $_escalationRuleID,
                'escalationruletitle' => $_escalationRuleCache[$_escalationRuleID]['title'], 'ownerstaffid' => $_ownerStaffID, 'departmentid' => $_departmentID,
                'ticketstatusid' => $_ticketStatusID, 'priorityid' => $_ticketPriorityID, 'tickettypeid' => $_ticketTypeID, 'flagtype' => ($_flagType),
                'ownerstaffname' => ReturnNone($_ownerStaffName), 'departmenttitle' => ReturnNone($_departmentTitle), 'ticketstatustitle' => ReturnNone($_ticketStatusTitle), 'prioritytitle' => ReturnNone($_priorityTitle),
                'tickettypetitle' => $_ticketTypeTitle), 'INSERT');
        $_escalationPathID = $_SWIFT->Database->Insert_ID();

        if (!$_escalationPathID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_escalationPathID;
    }

    /**
     * Delete the Escalation Path record
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

        self::DeleteList(array($this->GetEscalationPathID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Escalation Paths
     *
     * @author Varun Shoor
     * @param array $_escalationPathIDList The Escalation Path ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_escalationPathIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_escalationPathIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "escalationpaths WHERE escalationpathid IN (" . BuildIN($_escalationPathIDList) . ")");

        return true;
    }

    /**
     * Delete the Escalation Paths on a ticket id list
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_escalationPathIDList = array();
        $_SWIFT->Database->Query("SELECT escalationpathid FROM " . TABLE_PREFIX . "escalationpaths WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_escalationPathIDList[] = $_SWIFT->Database->Record['escalationpathid'];
        }

        if (!count($_escalationPathIDList))
        {
            return false;
        }

        self::DeleteList($_escalationPathIDList);

        return true;
    }

    /**
     * Retrieve a list of escalation paths on the given ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return array Escalation Path Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketID))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_escalationPathContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationpaths WHERE ticketid = '" . $_ticketID . "' ORDER BY escalationpathid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_escalationPathContainer[$_SWIFT->Database->Record['escalationpathid']] = $_SWIFT->Database->Record;
        }

        return $_escalationPathContainer;
    }

    /**
     * Update the escalation path properties, this is used by the rebuildproperties action in ticket model
     *
     * @author Varun Shoor
     * @param int $_escalationPathID
     * @param string $_slaPlanTitle
     * @param string $_escalationRuleTitle
     * @param string $_ownerStaffName
     * @param string $_departmentTitle
     * @param string $_ticketStatusTitle
     * @param string $_ticketPriorityTitle
     * @param string $_ticketTypeTitle
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateProperties($_escalationPathID, $_slaPlanTitle, $_escalationRuleTitle, $_ownerStaffName, $_departmentTitle, $_ticketStatusTitle, $_ticketPriorityTitle, $_ticketTypeTitle)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'escalationpaths', array('slaplantitle' => $_slaPlanTitle, 'escalationruletitle' => $_escalationRuleTitle, 'ownerstaffname' => $_ownerStaffName,
            'departmenttitle' => $_departmentTitle, 'ticketstatustitle' => $_ticketStatusTitle, 'prioritytitle' => $_ticketPriorityTitle, 'tickettypetitle' => $_ticketTypeTitle), 'UPDATE',
            "escalationpathid = '" . $_escalationPathID . "'");

        return true;
    }

    /**
     * Update the global property on all escalation paths, used to update stuff like departmentname etc.
     *
     * @author Varun Shoor
     * @param string $_updateFieldName
     * @param string $_updateFieldValue
     * @param string $_whereFieldName
     * @param string $_whereFieldValue
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateGlobalProperty($_updateFieldName, $_updateFieldValue, $_whereFieldName, $_whereFieldValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_updateFieldName = $_SWIFT->Database->Escape($_updateFieldName);
        $_whereFieldName = $_SWIFT->Database->Escape($_whereFieldName);
        $_whereFieldValue = (int) ($_whereFieldValue); // Expected to be always int

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'escalationpaths', array($_updateFieldName => $_updateFieldValue), 'UPDATE', $_whereFieldName . " = '" . $_SWIFT->Database->Escape($_whereFieldValue) . "'");

        return true;
    }
}
?>
