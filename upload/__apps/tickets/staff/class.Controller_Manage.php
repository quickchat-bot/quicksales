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

namespace Tickets\Staff;

use Base\Library\Tag\SWIFT_TagCloud;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_StaffBase;
use SWIFT;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Link\SWIFT_TicketLinkChain;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Library\View\SWIFT_TicketViewPropertyManager;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;
use SWIFT_Perf_Log;

/**
 * The Ticket Grid Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property Controller_Manage $Load
 * @property View_Manage $View
 * @property SWIFT_TagCloud $TagCloud
 * @author Varun Shoor
 */
class Controller_Manage extends Controller_StaffBase
{
    public $UserInterfaceGrid = false;
    protected static $_sendEmail = true;

    // Core Constants
    const MENU_ID = 2;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');

        SWIFT_Ticket::LoadLanguageTable();

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketmanagegrid', false, true), true, false, 'base');
    }

    /**
     * Put Back the Tickets that were trashed from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function PutBackList($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" .
                    BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
                if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                    throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
                }

                if ($_SWIFT_TicketObject->GetProperty('departmentid') != '0')
                {
                    continue;
                }

                $_departmentTitle = $_ticketStatusTitle = '';
                if (isset($_departmentCache[$_SWIFT->Database->Record['departmentid']])) {
                    $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT->Database->Record['departmentid']]['title']);
                }

                if (isset($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']])) {
                    $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']]['title']);
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityundeleteticket'),
                        htmlspecialchars($_SWIFT->Database->Record['subject']), text_to_html_entities($_departmentTitle),
                        htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT->Database->Record['fullname'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Ticket::UnDeleteList($_ticketIDList);
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Put Back the Given Ticket ID
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PutBack($_ticketID, $_listType = false, $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::PutBackList(array($_ticketID), true);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1562 Issue while moving a ticket to Trash
         *
         */
        $this->Load->Index(0, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);

        return true;
    }

    /**
     * Delete the Tickets from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcandeleteticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" .
                    BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
                if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                    throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
                }

                $_departmentTitle = $_ticketStatusTitle = '';
                if (isset($_departmentCache[$_SWIFT->Database->Record['departmentid']])) {
                    $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT->Database->Record['departmentid']]['title']);
                }

                if (isset($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']])) {
                    $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']]['title']);
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteticket'),
                        htmlspecialchars($_SWIFT->Database->Record['subject']), text_to_html_entities($_departmentTitle),
                        htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT->Database->Record['fullname'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Ticket::DeleteList($_ticketIDList);
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Delete all the Tickets from Trash Folder
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function EmptyTrash()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcandeleteticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }
        $_ticketIDList = array();
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE departmentid = '0' AND trasholddepartmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")");

        while ($_SWIFT->Database->NextRecord()) {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
            if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
            }

            $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];

            $_departmentTitle = $_ticketStatusTitle = '';
            if (isset($_departmentCache[$_SWIFT->Database->Record['departmentid']])) {
                $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT->Database->Record['departmentid']]['title']);
            }

            if (isset($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']])) {
                $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']]['title']);
            }

            SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteticket'), htmlspecialchars($_SWIFT->Database->Record['subject']), text_to_html_entities($_departmentTitle), htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT->Database->Record['fullname'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
        }

        SWIFT_Ticket::DeleteList($_ticketIDList);

        SWIFT_TicketManager::RebuildCache();

        $this->Load->Index();

        return true;
    }

    /**
     * Trash the Tickets from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function TrashList($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcantrashticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" .
                    BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
                if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                    throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
                }

                $_departmentTitle = $_ticketStatusTitle = '';
                if (isset($_departmentCache[$_SWIFT->Database->Record['departmentid']])) {
                    $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT->Database->Record['departmentid']]['title']);
                }

                if (isset($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']])) {
                    $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']]['title']);
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitytrashticket'),
                        htmlspecialchars($_SWIFT->Database->Record['subject']), text_to_html_entities($_departmentTitle),
                        htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT->Database->Record['fullname'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Ticket::TrashList($_ticketIDList);
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Trash the Given Ticket ID
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param bool $_listType (OPTIONAL) The Department ID
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Trash($_ticketID, $_listType = false, $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::TrashList(array($_ticketID), true);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1562 Issue while moving a ticket to Trash
         *
         */
        $this->Load->Index(0, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);

        return true;
    }

    /**
     * Mark the Tickets as Spam from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function SpamList($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcanmarkasspam') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" .
                    BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
                if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                    throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
                }

                $_departmentTitle = $_ticketStatusTitle = '';
                if (isset($_departmentCache[$_SWIFT->Database->Record['departmentid']])) {
                    $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT->Database->Record['departmentid']]['title']);
                }

                if (isset($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']])) {
                    $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']]['title']);
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitymarkasspamticket'),
                        htmlspecialchars($_SWIFT->Database->Record['subject']), text_to_html_entities($_departmentTitle),
                        htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT->Database->Record['fullname'])),
                        SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Ticket::MarkAsSpamList($_ticketIDList);
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Mark as Spam the Given Ticket ID
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Spam($_ticketID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::SpamList(array($_ticketID), true);

        $this->Load->Index();

        return true;
    }

    /**
     * Merge Tickets from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function MergeList($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" .
                    BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
                if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                    throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
                }

                $_departmentTitle = $_ticketStatusTitle = '';
                if (isset($_departmentCache[$_SWIFT->Database->Record['departmentid']])) {
                    $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT->Database->Record['departmentid']]['title']);
                }

                if (isset($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']])) {
                    $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']]['title']);
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitymergeticket'),
                        htmlspecialchars($_SWIFT->Database->Record['subject']), text_to_html_entities($_departmentTitle),
                        htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT->Database->Record['fullname'])),
                        SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Ticket::Merge($_ticketIDList);
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Watch Tickets from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function WatchList($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0' || $_SWIFT->Staff->GetPermission('staff_tcanviewticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_ticketIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" .
                    BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
                if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                    throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
                }

                $_departmentTitle = $_ticketStatusTitle = '';
                if (isset($_departmentCache[$_SWIFT->Database->Record['departmentid']])) {
                    $_departmentTitle = text_to_html_entities($_departmentCache[$_SWIFT->Database->Record['departmentid']]['title']);
                }

                if (isset($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']])) {
                    $_ticketStatusTitle = htmlspecialchars($_ticketStatusCache[$_SWIFT->Database->Record['ticketstatusid']]['title']);
                }

                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitywatchticket'),
                        htmlspecialchars($_SWIFT->Database->Record['subject']), text_to_html_entities($_departmentTitle),
                        htmlspecialchars($_ticketStatusTitle), text_to_html_entities($_SWIFT->Database->Record['fullname'])),
                        SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_Ticket::Watch($_ticketIDList, $_SWIFT->Staff);
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Mass Reply to Tickets from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function MassReplyList($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0' || $_SWIFT->Staff->GetPermission('staff_tcanviewticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_SWIFT->Language->Load('staff_ticketsmain');
        $_SWIFT->Language->Load('staff_ticketsmanage');

        SWIFT_Ticket::LoadLanguageTable();

        if (_is_array($_ticketIDList) && $_POST['replycontents'] != '') {
            foreach ($_ticketIDList as $_ticketID) {
                try {
                    $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));

                    if ($_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                        SWIFT_TicketPost::CreateStaff($_SWIFT_TicketObject, $_SWIFT->Staff, SWIFT_Ticket::CREATIONMODE_STAFFCP, $_POST['replycontents'],
                            $_SWIFT_TicketObject->GetProperty('subject'), !static::$_sendEmail);
                    }
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                }
            }

            SWIFT::Set('massreplyticketidlist', $_ticketIDList);
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Render the Mass Reply Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function _MassReplyDialog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('massreply'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0' || $_SWIFT->Staff->GetPermission('staff_tcanviewticket') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderMassReply();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Mass Action Panel Action
     *
     * @author Varun Shoor
     * @param mixed $_ticketIDList The Ticket ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function MassActionPanel($_ticketIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK


        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        $_SWIFT_TicketViewPropertyManagerObject = new SWIFT_TicketViewPropertyManager();

        if (_is_array($_ticketIDList)) {
            $_finalTicketObjectContainer = array();

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
                if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
                    continue;
                }

                $_finalTicketObjectContainer[] = $_SWIFT_TicketObject;

                /**
                 * @todo Create the activity logs
                 */
//                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeleteusergroup'), htmlspecialchars($_SWIFT->Database->Record['title'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_USERS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            if (count($_finalTicketObjectContainer))
            {
                $_finalTicketIDList = $_linkTicketIDList = array();
                /*
                 * BUG FIX - Nidhi Gupta
                 *
                 * SWIFT-839: Permission to restrict staff from moving tickets to departments they're not assigned to
                 *
                 * Comments: Added check for staff to move tickets in unassigned departments
                 */
                foreach ($_finalTicketObjectContainer as $_key => $_SWIFT_TicketObject) {
                    if (isset($_POST['departmentid']) && !empty($_POST['departmentid']) && $_POST['departmentid'] != '-1') {
                        if ($_SWIFT->Staff->GetPermission('staff_tcanchangeunassigneddepartment') == '0') {
                            if ($_SWIFT_TicketObject->Get('departmentid') != $_POST['departmentid'] && !in_array($_POST['departmentid'], $_SWIFT->Staff->GetAssignedDepartments())) {
                                $_SWIFT->UserInterface->Header($_SWIFT->Language->Get('tickets') . ' > ' . $_SWIFT->Language->Get('viewticket'), self::MENU_ID,
                                    self::NAVIGATION_ID);
                                $_SWIFT->UserInterface->DisplayError($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));
                                $_SWIFT->UserInterface->Footer();

                                return false;
                            // @codeCoverageIgnoreStart
                            // this code will never be executed
                            }
                        }
                            // @codeCoverageIgnoreEnd
                        $_SWIFT_TicketObject->SetDepartment($_POST['departmentid']);

                        $_SWIFT_TicketViewPropertyManagerObject->IncrementDepartment($_POST['departmentid']);
                    }

                    if (isset($_POST['staffid']) && $_POST['staffid'] != '-1') {
                        $_SWIFT_TicketObject->SetOwner($_POST['staffid']);

                        if ($_POST['staffid'] != '0') {
                            $_SWIFT_TicketViewPropertyManagerObject->IncrementStaff($_POST['staffid']);
                        }
                    }

                    if (isset($_POST['tickettypeid']) && !empty($_POST['tickettypeid']) && $_POST['tickettypeid'] != '-1') {
                        $_SWIFT_TicketObject->SetType($_POST['tickettypeid']);
                        $_SWIFT_TicketViewPropertyManagerObject->IncrementTicketType($_POST['tickettypeid']);
                    }

                    if (isset($_POST['ticketstatusid']) && !empty($_POST['ticketstatusid']) && $_POST['ticketstatusid'] != '-1') {
                        $_SWIFT_TicketObject->SetStatus($_POST['ticketstatusid']);
                        $_SWIFT_TicketViewPropertyManagerObject->IncrementTicketStatus($_POST['ticketstatusid']);
                    }

                    if (isset($_POST['ticketpriorityid']) && !empty($_POST['ticketpriorityid']) && $_POST['ticketpriorityid'] != '-1') {
                        $_SWIFT_TicketObject->SetPriority($_POST['ticketpriorityid']);
                        $_SWIFT_TicketViewPropertyManagerObject->IncrementTicketPriority($_POST['ticketpriorityid']);
                    }

                    if (isset($_POST['bayescategoryid']) && !empty($_POST['bayescategoryid']) && $_POST['bayescategoryid'] != '-1') {
                        $_SWIFT_TicketObject->TrainBayes($_POST['bayescategoryid']);
                        $_SWIFT_TicketViewPropertyManagerObject->IncrementBayesian($_POST['bayescategoryid']);
                    }

                    if (isset($_POST['ticketlinktypeid']) && !empty($_POST['ticketlinktypeid']) && $_POST['ticketlinktypeid'] != '-1') {
                        // Link the tickets

                        $_linkTicketIDList[] = $_SWIFT_TicketObject->GetTicketID();

                        $_SWIFT_TicketObject->MarkAsLinked();
                    }

                    // Set Flag
                    if (isset($_POST['ticketflagid']) && $_POST['ticketflagid'] != '-1') {
                        $_SWIFT_TicketObject->SetFlag($_POST['ticketflagid']);

                        if ($_POST['ticketflagid'] != '0') {
                            $_SWIFT_TicketViewPropertyManagerObject->IncrementTicketFlag($_POST['ticketflagid']);
                        }
                    }

                    // Add Tags
                    if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0')
                    {
                        SWIFT_Tag::AddTags(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID(), SWIFT_UserInterface::GetMultipleInputValues('addtags'), $_SWIFT->Staff->GetStaffID());
                    }

                    $_SWIFT_TicketObject->ProcessUpdatePool();

                    $_finalTicketIDList[] = $_SWIFT_TicketObject->GetTicketID();
                }

                // Process Links
                if (count($_linkTicketIDList)) {
                    SWIFT_TicketLinkChain::CreateChain($_POST['ticketlinktypeid'], $_linkTicketIDList);
                }

                // Remove Tags
                if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0')
                {
                    SWIFT_Tag::RemoveTags(SWIFT_TagLink::TYPE_TICKET, $_finalTicketIDList, SWIFT_UserInterface::GetMultipleInputValues('removetags'), $_SWIFT->Staff->GetStaffID());
                }
            }
        }

        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Displays the Ticket Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID
     * @param bool $_listType
     * @param int $_departmentID
     * @param int $_ticketStatusID
     * @param int $_ticketTypeID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_searchStoreID = 0, $_listType = false, $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1)
    {
        $perfLog = new SWIFT_Perf_Log();
        $startTime = time();
        $data = [
            '$_searchStoreID' => $_searchStoreID,
            '_listType' => $_listType,
            '_departmentID' => $_departmentID,
            '_ticketStatusID' => $_ticketStatusID,
            '_ticketTypeID' => $_ticketTypeID
        ];
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_searchStoreID)) {
            $_searchStoreID = 0;
        } else {
            $_searchStoreID = ($_searchStoreID);
        }

        if ($_listType !== false) {
            $this->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            $perfLog->addLog("Manage.Redirect", $startTime, time(), var_export($data, true));
            return true;
        }

        $this->_Render(-1, -1, -1, false, $_searchStoreID);
        $perfLog->addLog("Manage._Render", $startTime, time(), var_export($data, true));
        return true;
    }

    /**
     * Filter & Display Results
     *
     * @author Varun Shoor
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Filter($_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1, $_searchStoreID = 0) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        $perfLog = new SWIFT_Perf_Log();
        $_searchQuery = $this->UserInterfaceGrid->GetSearchQueryString();

        if (isset($_searchQuery) && !empty($_searchQuery))
        {
            $_POST['_searchQuery'] = base64_encode($_searchQuery);
        }

        if (!is_numeric($_departmentID)) {
            $_departmentID = -1;
        } else {
            $_departmentID = ($_departmentID);
        }
        SWIFT::Set('tickettreedepartmentid', $_departmentID);

        if (!is_numeric($_ticketStatusID)) {
            $_ticketStatusID = -1;
        } else {
            $_ticketStatusID = ($_ticketStatusID);
        }
        SWIFT::Set('tickettreestatusid', $_ticketStatusID);

        if (!is_numeric($_ticketTypeID)) {
            $_ticketTypeID = -1;
        } else {
            $_ticketTypeID = ($_ticketTypeID);
        }
        SWIFT::Set('tickettreetypeid', $_ticketTypeID);

//        $this->UserInterfaceGrid->SetURLArguments('/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID);

        if (!is_numeric($_searchStoreID)) {
            $_searchStoreID = 0;
        } else {
            $_searchStoreID = ($_searchStoreID);
        }
        $this->_Render($_departmentID, $_ticketStatusID, $_ticketTypeID, false, $_searchStoreID);

        return true;
    }

    /**
     * Display Assigned to Current Staff Tickets
     *
     * @author Varun Shoor
     * @param int $_searchStoreID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MyTickets($_searchStoreID = 0) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        SWIFT::Set('tickettreelisttype', 'mytickets');

        if (!is_numeric($_searchStoreID)) {
            $_searchStoreID = 0;
        } else {
            $_searchStoreID = ($_searchStoreID);
        }
        $this->_Render(-1, -1, -1, SWIFT_TicketViewRenderer::OWNER_MYTICKETS, $_searchStoreID);

        return true;
    }

    /**
     * Display Unassigned Tickets
     *
     * @author Varun Shoor
     * @param int $_searchStoreID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Unassigned($_searchStoreID = 0) {

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        if ($_SWIFT->Staff->GetPermission('staff_tcanviewunassign') == '0')
        {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();
            return false;
        }

        SWIFT::Set('tickettreelisttype', 'unassigned');

        if (!is_numeric($_searchStoreID)) {
            $_searchStoreID = 0;
        } else {
            $_searchStoreID = ($_searchStoreID);
        }
        $this->_Render(-1, -1, -1, SWIFT_TicketViewRenderer::OWNER_UNASSIGNED, $_searchStoreID);

        return true;
    }

    /**
     * Redirect call to appropriate function
     *
     * @author Varun Shoor
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Redirect($_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_departmentID == -1)
        {
            $_ticketStatusID = -1;
            $_ticketTypeID = -1;
        }

        if ($_listType === 'mytickets') {
            $this->MyTickets();
        } else if ($_listType === 'unassigned') {
            $this->Unassigned();
        } else {
            $this->Filter($_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        return true;
    }

    /**
     * Switch the View
     *
     * @author Varun Shoor
     * @param int $_ticketViewID The Ticket View ID
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function View($_ticketViewID, $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketViewCache = $_SWIFT->Cache->Get('ticketviewcache');
        if (!isset($_ticketViewCache[$_ticketViewID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketViewObject = new SWIFT_TicketView(new SWIFT_DataStore($_ticketViewCache[$_ticketViewID]));
        if (!$_SWIFT_TicketViewObject instanceof SWIFT_TicketView || !$_SWIFT_TicketViewObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketViewObject->CanStaffView()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1198: Staff CP -> Manage Tickets -> Trash -> View: All Tickets - All Tickets = doesn't work
         */
        if (!is_numeric($_departmentID)) {
            $_departmentID = -1;
        } else {
            $_departmentID = ($_departmentID);
        }

        SWIFT::Set('tickettreedepartmentid', $_departmentID);

        if (!is_numeric($_ticketStatusID)) {
            $_ticketStatusID = -1;
        } else {
            $_ticketStatusID = ($_ticketStatusID);
        }

        SWIFT::Set('tickettreestatusid', $_ticketStatusID);

        SWIFT_TicketViewRenderer::ChangeView($_SWIFT_TicketViewObject);

        $this->_Render($_departmentID, $_ticketStatusID, $_ticketTypeID);

        return true;
    }

    /**
     * Load the search results
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search($_searchStoreID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalSearchStoreID = -1;
        if (is_numeric($_searchStoreID))
        {
            $_finalSearchStoreID = ($_searchStoreID);
        }

        $this->_Render(-1, -1, -1, false, $_finalSearchStoreID);

        return true;
    }

    /**
     * Render the grid
     *
     * @author Varun Shoor
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param mixed $_ownerFilter (OPTIONAL) The Owner Filter
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _Render($_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1, $_ownerFilter = false, $_searchStoreID = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanviewtickets') == '0')
        {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('manage'), self::MENU_ID,
                    self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_departmentID, $_ticketStatusID, $_ticketTypeID, $_ownerFilter, $_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketListType = 'inbox';
        $_ticketTreeDepartmentID = $_ticketTreeStatusID = $_ticketTreeTypeID = -1;

        if (SWIFT::Get('tickettreedepartmentid') !== false) {
            $_ticketTreeDepartmentID = (SWIFT::Get('tickettreedepartmentid'));
        }

        if (SWIFT::Get('tickettreestatusid')) {
            $_ticketTreeStatusID = (SWIFT::Get('tickettreestatusid'));
        }

        if (SWIFT::Get('tickettreetypeid')) {
            $_ticketTreeTypeID = (SWIFT::Get('tickettreetypeid'));
        }

        if (SWIFT::Get('tickettreelisttype')) {
            $_ticketListType = SWIFT::Get('tickettreelisttype');
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_TicketViewRenderer::RenderTree($_ticketListType,
                $_ticketTreeDepartmentID, $_ticketTreeStatusID, $_ticketTreeTypeID));
        $this->Load->Library('Tag:TagCloud', array(SWIFT_TagLink::RetrieveCloudContainer(SWIFT_TagLink::TYPE_TICKET), false,
            'window.$gridirs.RunIRS(\'ticketmanagegrid\', \'tag:%s\');'), true, false, 'base');

        return true;
    }

    /**
     * Preview a Ticket Contents
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Preview($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();
        $perfLog = new SWIFT_Perf_Log();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        $startTime = time();
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        $endGetOnId = time();
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            throw new SWIFT_Exception('Access Denied to Ticket: ' . $_SWIFT_TicketObject->GetTicketDisplayID());
        }
        $endCanAccess = time();
        echo $_SWIFT_TicketObject->GetLastPostContents();

        $endTime = time();
        $perfLog->addLog("Preview.GetObjectOnID", $startTime, $endGetOnId, 'ticketID='.$_ticketID);
        $perfLog->addLog("Preview.CanAccess", $endGetOnId, $endCanAccess, 'ticketID='.$_ticketID);
        $perfLog->addLog("Preview.GetLastPostContents", $endCanAccess, $endTime, 'ticketID='.$_ticketID);
        $perfLog->addLog("Preview", $startTime, $endTime, 'ticketID='.$_ticketID);
        return true;
    }
}
