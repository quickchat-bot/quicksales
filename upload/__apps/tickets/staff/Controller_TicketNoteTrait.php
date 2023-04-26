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

use SWIFT;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Note\SWIFT_TicketNote;
use Base\Library\UserInterface\SWIFT_UserInterface;

trait Controller_TicketNoteTrait
{
    /**
     * Render the Add Note form for this Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNote(
        $_ticketID,
        $_listType = 'inbox',
        $_departmentID = -1,
        $_ticketStatusID = -1,
        $_ticketTypeID = -1,
        $_ticketLimitOffset = 0
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcaninsertticketnote') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        SWIFT::Set('ticketurlsuffix',
            $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID . '/' . $_ticketLimitOffset);

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('addnote'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_INSERT, $_SWIFT_TicketObject, null, $_SWIFT_UserObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Add Note form for this Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNoteSubmit(
        $_ticketID,
        $_listType = 'inbox',
        $_departmentID = -1,
        $_ticketStatusID = -1,
        $_ticketTypeID = -1,
        $_ticketLimitOffset = 0
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcaninsertticketnote') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        // Notification Event
        $_SWIFT_TicketObject->NotificationManager->SetEvent('newticketnotes');

        $_staffName = $_SWIFT->Staff->GetProperty('fullname');
        $_staffEmail = $_SWIFT->Staff->GetProperty('email');

        $_SWIFT_TicketObject->SetWatcherProperties($_staffName, sprintf($_SWIFT->Language->Get('watcherprefix'), $_staffName, $_staffEmail) . SWIFT_CRLF . $_POST['ticketnotes']);

        $_forStaffID = false;
        if (isset($_POST['forstaffid'])) {
            $_forStaffID = (int) ($_POST['forstaffid']);
        }

        if (isset($_POST['ticketnotes']) && trim($_POST['ticketnotes']) != '') {
            $_SWIFT_TicketObject->CreateNote($_SWIFT_UserObject, $_POST['ticketnotes'], $_POST['notecolor_ticketnotes'], $_POST['notetype'], $_forStaffID);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3465 Tickets should be refreshed while adding ticket notes.
         */
        $_SWIFT_TicketObject->ProcessUpdatePool();

        SWIFT_TicketManager::RebuildCache();

        if ($_SWIFT->Settings->Get('t_ticketnoteresetsupdatetime') && $_SWIFT_TicketObject->GetProperty('duetime') != '0') {
            $_SWIFT_TicketObject->ExecuteSLA(false, true);
        }

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Edit a note
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketNoteID The Ticket Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNote($_ticketID, $_ticketNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticketnote') == '0') {
            return false;
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $_SWIFT_TicketNoteObject = new SWIFT_TicketNote($_ticketNoteID);
        if (!$_SWIFT_TicketNoteObject instanceof SWIFT_TicketNote || !$_SWIFT_TicketNoteObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editnote'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderNoteForm(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketObject, $_SWIFT_TicketNoteObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit a note processor
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketNoteID The Ticket Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditNoteSubmit($_ticketID, $_ticketNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticketnote') == '0') {
            return false;
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_SWIFT_TicketNoteObject = new SWIFT_TicketNote($_ticketNoteID);
        if (!$_SWIFT_TicketNoteObject instanceof SWIFT_TicketNote || !$_SWIFT_TicketNoteObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        // Edit notes
        if (trim($_POST['ticketnotes']) != '')
        {
            $_SWIFT_TicketNoteObject->Update($_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'), $_POST['ticketnotes'], (int) ($_POST['notecolor_ticketnotes']));
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        echo $this->View->RenderNotes($_SWIFT_TicketObject, $_SWIFT_UserObject);

        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_NEWNOTE,
            $_SWIFT->Language->Get('al_updatenote'),
            SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '', ['al_updatenote']);

        return true;
    }

    /**
     * Delete Note Processer
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketNoteID The Ticket Note ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteNote($_ticketID, $_ticketNoteID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcandeleteticketnote') == '0') {
            return false;
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $_SWIFT_TicketNoteObject = new SWIFT_TicketNote($_ticketNoteID);
        if (!$_SWIFT_TicketNoteObject instanceof SWIFT_TicketNote || !$_SWIFT_TicketNoteObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }


        $_SWIFT_TicketNoteObject->Delete();

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        echo $this->View->RenderNotes($_SWIFT_TicketObject, $_SWIFT_UserObject);

        $_SWIFT_TicketObject->RebuildProperties();

        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_NEWNOTE,
            $_SWIFT->Language->Get('al_deletenote'),
            SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '', ['al_deletenote']);

        return true;
    }
}
