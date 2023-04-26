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

namespace Tickets\Models\Status;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Escalation\SWIFT_EscalationPath;

/**
 * The Ticket Status Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketStatus extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketstatus';
    const PRIMARY_KEY        =    'ticketstatusid';

    const TABLE_STRUCTURE    =    "ticketstatusid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                iscustom I2 DEFAULT '1' NOTNULL,
                                displayinmainlist I2 DEFAULT '0' NOTNULL,
                                markasresolved I2 DEFAULT '0' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL,
                                statustype C(100) DEFAULT 'public' NOTNULL,
                                displaycount I2 DEFAULT '0' NOTNULL,
                                statuscolor C(50) DEFAULT '' NOTNULL,
                                statusbgcolor C(50) DEFAULT '' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                type C(50) DEFAULT 'public' NOTNULL,
                                resetduetime I2 DEFAULT '0' NOTNULL,

                                displayicon C(255) DEFAULT '' NOTNULL,
                                staffvisibilitycustom I2 DEFAULT '0' NOTNULL,
                                dispatchnotification I2 DEFAULT '0' NOTNULL,
                                triggersurvey I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'title';



    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @throws SWIFT_Status_Exception If the Class is not Loaded
     */
    public function __construct($_ticketStatusID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketStatusID))
        {
            $this->SetIsClassLoaded(false);

            throw new SWIFT_Status_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Status_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()))
        {
            return false;
        }

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Status_Exception(SWIFT_CLASSNOTLOADED);
        }


        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketstatus', $this->GetUpdatePool(), 'UPDATE', "ticketstatusid = '" .
                (int) ($this->GetTicketStatusID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Status ID
     *
     * @author Varun Shoor
     * @return mixed "ticketStatusID" on Success, "false" otherwise
     * @throws SWIFT_Status_Exception If the Class is not Loaded
     */
    public function GetTicketStatusID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Status_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketstatusid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketStatusID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketstatus WHERE ticketstatusid = '" .
                $_ticketStatusID . "'");
        if (isset($_dataStore['ticketstatusid']) && !empty($_dataStore['ticketstatusid']))
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
     * @throws SWIFT_Status_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Status_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Status_Exception If the Class is not Loaded or if the Property is not set
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Status_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Status_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve a new Ticket Status Object
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return object SWIFT_TicketStatus Object
     */
    public static function Retrieve($_ticketStatusID)
    {
        return new SWIFT_TicketStatus($_ticketStatusID);
    }

    /**
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param string $_statusType The Status Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_statusType)
    {
        if ($_statusType != SWIFT_PUBLIC && $_statusType != SWIFT_PRIVATE)
        {
            return false;
        }

        return true;
    }

    /**
     * Create a new Ticket Status
     *
     * @author Varun Shoor
     * @param string $_title The Status Title
     * @param int $_displayOrder The Status Display Order
     * @param bool $_markAsResolved Whether the Tickets under this Status should be marked as Resolved
     * @param bool $_displayCount Whether to Display Ticket Count beside the Status
     * @param string $_statusColor The Status Color Code
     * @param string $_statusBackgroundColor The Background Color for this Status
     * @param int $_departmentID Whether the Status is linked to a separate department
     * @param string $_statusType The Status Type (PUBLIC/PRIVATE)
     * @param bool $_resetDueTime Whether the Due Time should be reset whenever a ticket is switched to this status
     * @param string $_displayIcon A Custom Display Icon for this Status
     * @param bool $_dispatchNotification
     * @param bool $_triggerSurvey Whether to trigger a survey whenever a ticket is changed to this status
     * @param bool $_staffVisibilityCustom Whether the status should be visible to only select staff groups
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Groups the Status should be Linked With
     * @param bool $_isMaster (OPTIONAL) Whether this ticket status is a master status which cannot be deleted
     * @return mixed "_ticketStatusID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create($_title, $_displayOrder, $_markAsResolved, $_displayCount, $_statusColor, $_statusBackgroundColor, $_departmentID,
            $_statusType, $_resetDueTime, $_displayIcon, $_dispatchNotification, $_triggerSurvey, $_staffVisibilityCustom, $_staffGroupIDList = array(),
            $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_statusType))
        {
            throw new SWIFT_Status_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketstatus', array('title' => $_title, 'displayorder' =>  ($_displayOrder),
            'iscustom' => '1', 'markasresolved' => (int) ($_markAsResolved), 'displaycount' => (int) ($_displayCount),
            'statuscolor' => ReturnNone($_statusColor), 'statusbgcolor' => $_statusBackgroundColor,
            'departmentid' => $_departmentID, 'statustype' => $_statusType, 'triggersurvey' => (int) ($_triggerSurvey),
            'resetduetime' => (int) ($_resetDueTime), 'displayicon' => $_SWIFT->Input->SanitizeForXSS($_displayIcon), 'staffvisibilitycustom' => (int) ($_staffVisibilityCustom),
            'dispatchnotification' => (int) ($_dispatchNotification), 'ismaster' => (int) ($_isMaster)), 'INSERT');
        $_ticketStatusID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketStatusID)
        {
            throw new SWIFT_Status_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_staffGroupIDList) && $_staffVisibilityCustom == 1)
        {
            foreach ($_staffGroupIDList as $_key => $_val)
            {
                SWIFT_StaffGroupLink::Create($_val, SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_ticketStatusID, false);
            }
        }

        SWIFT_StaffGroupLink::RebuildCache();

        self::RebuildCache();

        return $_ticketStatusID;
    }

    /**
     * Create a new Ticket Status
     *
     * @author Varun Shoor
     * @param string $_title The Status Title
     * @param int $_displayOrder The Status Display Order
     * @param bool $_markAsResolved Whether the Tickets under this Status should be marked as Resolved
     * @param bool $_displayCount Whether to Display Ticket Count beside the Status
     * @param string $_statusColor The Status Color Code
     * @param string $_statusBackgroundColor The Background Color for this Status
     * @param int $_departmentID Whether the Status is linked to a separate department
     * @param string $_statusType The Status Type (PUBLIC/PRIVATE)
     * @param bool $_resetDueTime Whether the Due Time should be reset whenever a ticket is switched to this status
     * @param string $_displayIcon A Custom Display Icon for this Status
     * @param int $_dispatchNotification Whether to Dispatch Notification
     * @param bool $_triggerSurvey Whether to trigger a survey whenever a ticket is changed to this status
     * @param bool $_staffVisibilityCustom Whether the status should be visible to only select staff groups
     * @param array $_staffGroupIDList The Staff Groups the Status should be Linked With
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Update($_title, $_displayOrder, $_markAsResolved, $_displayCount, $_statusColor, $_statusBackgroundColor, $_departmentID,
            $_statusType, $_resetDueTime, $_displayIcon, $_dispatchNotification, $_triggerSurvey, $_staffVisibilityCustom, $_staffGroupIDList = array())
    {
        if (!$this->GetIsClassLoaded() || !self::IsValidType($_statusType))
        {
            throw new SWIFT_Status_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidType($_statusType)) {
            throw new SWIFT_Status_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('displayorder',  ($_displayOrder));
        $this->UpdatePool('markasresolved', (int) ($_markAsResolved));
        $this->UpdatePool('displaycount', (int) ($_displayCount));
        $this->UpdatePool('statuscolor', $_statusColor);
        $this->UpdatePool('statusbgcolor', $_statusBackgroundColor);
        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('statustype', $_statusType);
        $this->UpdatePool('resetduetime', (int) ($_resetDueTime));
        $this->UpdatePool('displayicon', $this->Input->SanitizeForXSS($_displayIcon));
        $this->UpdatePool('staffvisibilitycustom', (int) ($_staffVisibilityCustom));
        $this->UpdatePool('dispatchnotification',  ($_dispatchNotification));
        $this->UpdatePool('triggersurvey', (int) ($_triggerSurvey));

        $this->ProcessUpdatePool();

        SWIFT_StaffGroupLink::DeleteOnLink(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $this->GetTicketStatusID());
        if (_is_array($_staffGroupIDList) && $_staffVisibilityCustom == 1)
        {
            foreach ($_staffGroupIDList as $_key => $_val)
            {
                SWIFT_StaffGroupLink::Create($_val, SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $this->GetTicketStatusID(), false);
            }
        }

        SWIFT_StaffGroupLink::RebuildCache();

        $this->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('isresolved' => (int) ($_markAsResolved)), 'UPDATE', "ticketstatusid = '" . (int) ($this->GetTicketStatusID()) . "'");

        self::RebuildCache();

        // Rebuild other properties
        SWIFT_Ticket::UpdateGlobalProperty('ticketstatustitle', $_title, 'ticketstatusid', $this->GetTicketStatusID());

        SWIFT_EscalationPath::UpdateGlobalProperty('ticketstatustitle', $_title, 'ticketstatusid', $this->GetTicketStatusID());

        return true;
    }

    /**
     * Delete the Ticket Status record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Status_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Status_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketStatusID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Retrieve the Staff Group ID's linked with this Ticket Status
     *
     * @author Varun Shoor
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $this->GetTicketStatusID());
    }

    /**
     * Delete a List of Ticket Status
     *
     * @author Varun Shoor
     * @param array $_ticketStatusIDList The Ticket Status ID List Holder
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketStatusIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketStatusIDList))
        {
            return false;
        }

        $_finalTicketStatusIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus WHERE ticketstatusid IN (" . BuildIN($_ticketStatusIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalText .= $_index . '. ' . IIF(!empty($_SWIFT->Database->Record['displayicon']), '<img src="'. str_replace('{$themepath}',
                    SWIFT::Get('themepath').'images/', $_SWIFT->Database->Record['displayicon']).'" align="absmiddle" border="0" /> ') .
                    htmlspecialchars($_SWIFT->Database->Record['title']).'<br />';
            $_finalTicketStatusIDList[] = $_SWIFT->Database->Record['ticketstatusid'];

            $_index++;
        }

        if (!count($_finalTicketStatusIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelticketstatus'), count($_finalTicketStatusIDList)),
                $_SWIFT->Language->Get('msgdelticketstatus') . '<br />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketstatus WHERE ticketstatusid IN(" . BuildIN($_finalTicketStatusIDList) . ")");

        // Clear the Staff Group Links
        SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_finalTicketStatusIDList);

        SWIFT_Ticket::ChangeStatusToTrash($_finalTicketStatusIDList);

        SWIFT_TicketManager::RebuildCache();

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Ticket Status Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY displayorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;
            $_cache[$_SWIFT->Database->Record3['ticketstatusid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['ticketstatusid']]['index'] = $_index;
        }

        $_SWIFT->Cache->Update('statuscache', $_cache);

        return true;
    }

    /**
     * Retrieve the Last Possible Display Order for a Ticket Status
     *
     * @author Varun Shoor
     * @return int The Last Possible Display Order
     * @throws SWIFT_Exception
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('statuscache');

        $_statusCache = $_SWIFT->Cache->Get('statuscache');

        if (!$_statusCache)
        {
            return 1;
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_statusCache));

        return ($_statusCache[$_lastInsertID]['displayorder'] + 1);
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_ticketStatusIDSortList The Ticket Status ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_ticketStatusIDSortList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketStatusIDSortList)) {
            return false;
        }

        foreach ($_ticketStatusIDSortList as $_ticketStatusID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketstatus', array('displayorder' => (int) ($_displayOrder)), 'UPDATE',
                    "ticketstatusid = '" . $_ticketStatusID . "'");
        }

        self::RebuildCache();

        return true;
    }
}
?>
