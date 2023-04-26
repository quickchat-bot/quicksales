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

namespace Tickets\Staff;

use Controller_StaffBase;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Tickets\Library\Ajax\SWIFT_TicketAjaxManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Lock\SWIFT_TicketPostLock;
use Base\Models\User\SWIFT_UserEmailManager;

/**
 * The AJAX Controller
 *
 * @property SWIFT_TicketAjaxManager $TicketAjaxManager
 * @property View_Ajax $View
 * @author Varun Shoor
 */
class Controller_Ajax extends Controller_StaffBase
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('Ajax:TicketAjaxManager', [], true, false, 'tickets');

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Retrieve Ticket Status Combo Box on Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param string $_fieldName The Ticket Status ID Field Name
     * @param int $_ticketStatusID The Current Ticket Status ID
     * @param bool $_showNoChange (OPTIONAL) Show -- No Change -- Item in Select Box
     * @param bool $_onlyPublic (OPTIONAL) Show only public items
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketStatusOnDepartmentID($_departmentID, $_fieldName, $_ticketStatusID, $_showNoChange = false, $_onlyPublic = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->TicketAjaxManager->GetTicketStatusOnDepartmentID($_departmentID, $_fieldName, $_ticketStatusID, $_showNoChange, $_onlyPublic);

        return true;
    }

    /**
     * Retrieve Ticket Type Combo Box on Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param string $_fieldName The Ticket Type ID Field Name
     * @param int $_ticketTypeID The Current Ticket Type ID
     * @param bool $_showNoChange (OPTIONAL) Show -- No Change -- Item in Select Box
     * @param bool $_onlyPublic (OPTIONAL) Show only public items
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketTypeOnDepartmentID($_departmentID, $_fieldName, $_ticketTypeID, $_showNoChange = false, $_onlyPublic = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->TicketAjaxManager->GetTicketTypeOnDepartmentID($_departmentID, $_fieldName, $_ticketTypeID, $_showNoChange, $_onlyPublic);

        return true;
    }

    /**
     * Retrieve Ticket Owner on Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param string $_fieldName The Ticket Type ID Field Name
     * @param int $_staffID The Current Staff ID
     * @param bool $_showNoChange (OPTIONAL) Show -- No Change -- Item in Select Box
     * @param bool $_onlyPublic (OPTIONAL) Show only public items
     * @param bool $_showActiveStaff (OPTIONAL) Whether to Show Active Staff Item
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketOwnerOnDepartmentID($_departmentID, $_fieldName, $_staffID, $_showNoChange = false, $_onlyPublic = false, $_showActiveStaff = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->TicketAjaxManager->GetTicketOwnerOnDepartmentID($_departmentID, $_fieldName, $_staffID, $_showNoChange, $_onlyPublic, $_showActiveStaff);

        return true;
    }

    /**
     * Flag the given ticket id
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Flag($_ticketID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        $_SWIFT_TicketObject->SetFlag(SWIFT_TicketFlag::FLAG_RED);

        return true;
    }

    /**
     * Clear the flag on the given ticket id
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ClearFlag($_ticketID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        $_SWIFT_TicketObject->SetFlag('0');

        return true;
    }

    /**
     * Searches using Auto Complete
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SearchEmail()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['q']) || empty($_POST['q']))
        {
            return false;
        }

        $_emailContainer = $_emailMap = $_organizationMap = array();

        $this->Database->QueryLimit("SELECT useremails.*, users.fullname, userorganizations.organizationname
            FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid AND useremails.linktype = '" . SWIFT_UserEmailManager::LINKTYPE_USER . "')
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            WHERE ((" . BuildSQLSearch('useremails.email', $_POST['q']) . ") OR (" . BuildSQLSearch('users.fullname', $_POST['q']) . ") OR (" . BuildSQLSearch('userorganizations.organizationname', $_POST['q']) . ")) AND users.isenabled = 1", 10);
        while ($this->Database->NextRecord())
        {
            if (in_array($this->Database->Record['email'], $_emailContainer))
            {
                continue;
            }

            $_emailContainer[] = htmlspecialchars($this->Database->Record['email']);
            $_emailMap[$this->Database->Record['email']] = text_to_html_entities($this->Database->Record['fullname']);
            $_organizationMap[$this->Database->Record['email']] = text_to_html_entities($this->Database->Record['organizationname']);
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketemails
                                    WHERE issearchable = 1
                                      AND " . BuildSQLSearch('email', $this->Database->Escape($_POST['q'])), 10);
        while ($this->Database->NextRecord())
        {
            if (in_array($this->Database->Record['email'], $_emailContainer))
            {
                continue;
            }

            $_emailContainer[] = htmlspecialchars($this->Database->Record['email']);
            $_emailMap[$this->Database->Record['email']] = '';
            $_organizationMap[$this->Database->Record['email']] = '';
        }

        sort($_emailContainer);

        foreach ($_emailContainer as $_emailAddress)
        {
            if (!IsEmailValid($_emailAddress)) {
                continue;
            }

            $_emailMapLink = $_emailMap[$_emailAddress];
            $_organizationMapLink = $_organizationMap[$_emailAddress];

            if (!empty($_organizationMapLink))
            {
                $_emailMapLink .= ' (' . $_organizationMapLink . ')';
            }

            echo str_replace('|', '', IIF(!empty($_emailMapLink), $_emailMapLink . '<br />') . mb_strtolower($_emailAddress)) . '|' . str_replace('|', '', mb_strtolower($_emailAddress)) . SWIFT_CRLF;
        }

        return true;
    }

    /**
     * Check and Display Reply Lock Info
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReplyLock($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = false;

        try
        {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            return false;
        }

        $_replyContents = '';
        if ((isset($_POST['contents']) && $_POST['contents'] != '')) {
            $_replyContents = $_POST['contents'];
        }

        SWIFT_TicketPostLock::Replace($_SWIFT_TicketObject, $_SWIFT->Staff, $_replyContents);

        $_ticketPostLockContainer = SWIFT_TicketPostLock::RetrieveOnTicket($_SWIFT_TicketObject);

        $this->View->RenderPostLocks($_ticketPostLockContainer);

        return true;
    }
}
