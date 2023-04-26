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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT_Exception;
use Tickets\Library\Ajax\SWIFT_TicketAjaxManager;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The AJAX Controller
 *
 * @property SWIFT_TicketAjaxManager $TicketAjaxManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_Ajax extends Controller_admin
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Ajax:TicketAjaxManager', [], true, false, 'tickets');

        $this->Language->Load('staff_ticketsmain');
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
}
