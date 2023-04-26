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

namespace Tickets\Staff;

use SWIFT;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;

trait Controller_TicketBillingTrait
{
    /**
     * Render the Billing tab for this Ticket
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
    public function Billing($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        SWIFT::Set('ticketurlsuffix', $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID . '/' . $_ticketLimitOffset);

        $this->View->RenderBilling($_SWIFT_TicketObject, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Render the Billing tab for user
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function BillingUser($_userID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));

        // Did the object load up?
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->View->RenderBillingUser($_SWIFT_UserObject);

        return true;
    }

    /**
     * Check to see if its a valid billing time (hh:mm)
     *
     * @author Varun Shoor
     * @param string $_billingTime Check Billing Time
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function IsValidBillingTime($_billingTime)
    {
        $_matches = array();

        if (!preg_match('@([\d]{2}):([\d]{2})@', $_billingTime, $_matches))
        {
            return false;
        }

        if (!(int) ($_matches[1]) && !(int) ($_matches[2]))
        {
            return false;
        }

        return true;
    }

    /**
     * Parse hh:mm and retrieve time in seconds
     *
     * @author Varun Shoor
     * @param string $_billingTime Check Billing Time
     * @return int The Billing Seconds
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function GetBillingTime($_billingTime)
    {
        if (!self::IsValidBillingTime($_billingTime))
        {
            return 0;
        }

        if (!strpos($_billingTime, ':'))
        {
            return 0;
        }

        $_timeContainer = explode(':', $_billingTime);
        if (count($_timeContainer) != 2)
        {
            return 0;
        }

        $_timeHours = (int) ($_timeContainer[0]);
        $_timeMinutes = (int) ($_timeContainer[1]);

        $_finalSeconds = 0;
        $_finalSeconds += $_timeHours * 3600;
        $_finalSeconds += $_timeMinutes * 60;

        return $_finalSeconds;
    }

    /**
     * Render the Release tab for this Ticket
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
    public function BillingSubmit($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcaninsertbilling') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_workDateline = GetDateFieldTimestamp('billworkdate');
        $_billDateline = GetDateFieldTimestamp('billdate');

        if (empty($_POST['billingworkerstaffid']))
        {
            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
        } else if (empty($_workDateline) || empty($_billDateline)) {
            $this->UserInterface->Error($this->Language->Get('titleinvalidbilldate'), $this->Language->Get('msginvalidbilldate'));
        } else {
            // Try to load worker staff object
            $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataID($_POST['billingworkerstaffid']));
            if (!$_SWIFT_StaffObject_Worker instanceof SWIFT_Staff || !$_SWIFT_StaffObject_Worker->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            // Create the time worked entry
            $_SWIFT_TicketTimeTrackObject = SWIFT_TicketTimeTrack::Create($_SWIFT_TicketObject, $_SWIFT->Staff,
                    self::GetBillingTime($_POST['billingtimeworked']), self::GetBillingTime($_POST['billingtimebillable']),
                    $_POST['notecolor_billingnotes'], $_POST['billingnotes'], $_SWIFT_StaffObject_Worker, $_workDateline, $_billDateline);

            // Update Custom Field Values
            $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT,
                    array(SWIFT_CustomFieldGroup::GROUP_TIMETRACK), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_TicketTimeTrackObject->GetTicketTimeTrackID());
        }

        // Begin Hook: staff_ticket_billing
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_billing')) ? eval($_hookCode) : false;
        // End Hook

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
                $_SWIFT->Language->Get('al_ticketbilling'),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '', ['al_ticketbilling']);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1102 Due time is being set again, if ticket updated with billing time
         *
         * Comments: Not required
         */
//        $_SWIFT_TicketObject->ExecuteSLA();

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Edit a billing entry
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketTimeTrackID The Ticket Time Track ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditBilling($_ticketID, $_ticketTimeTrackID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdatebilling') == '0') {
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

        $_SWIFT_TicketTimeTrackObject = new SWIFT_TicketTimeTrack(new SWIFT_DataID($_ticketTimeTrackID));
        if (!$_SWIFT_TicketTimeTrackObject instanceof SWIFT_TicketTimeTrack || !$_SWIFT_TicketTimeTrackObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editbilling'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderBillingForm(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_TicketObject, $_SWIFT_TicketTimeTrackObject);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit a billing entry processor
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketTimeTrackID The Ticket Time Track ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditBillingSubmit($_ticketID, $_ticketTimeTrackID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdatebilling') == '0') {
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

        $_SWIFT_TicketTimeTrackObject = new SWIFT_TicketTimeTrack(new SWIFT_DataID($_ticketTimeTrackID));
        if (!$_SWIFT_TicketTimeTrackObject instanceof SWIFT_TicketTimeTrack || !$_SWIFT_TicketTimeTrackObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_workDateline = GetDateFieldTimestamp('ebillworkdate');
        $_billDateline = GetDateFieldTimestamp('ebilldate');

        if (trim($_POST['ebillingtimeworked']) == '' || trim($_POST['ebillingtimebillable']) == '' || empty($_POST['ebillingworkerstaffid']))
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
        } else if (empty($_workDateline) || empty($_billDateline)) {
            $this->UserInterface->DisplayError($this->Language->Get('titleinvalidbilldate'), $this->Language->Get('msginvalidbilldate'));
        } else {
            // Try to load worker staff object
            $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataID($_POST['ebillingworkerstaffid']));
            if (!$_SWIFT_StaffObject_Worker instanceof SWIFT_Staff || !$_SWIFT_StaffObject_Worker->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $_SWIFT_TicketTimeTrackObject->Update(self::GetBillingTime($_POST['ebillingtimeworked']), self::GetBillingTime($_POST['ebillingtimebillable']),
                    $_POST['notecolor_ebillingnotes'], $_POST['ebillingnotes'], $_SWIFT_StaffObject_Worker, $_workDateline, $_billDateline, $_SWIFT->Staff);

        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-722 Modified Billing entry does not get updated on the ticket
         *
         * Comments: Rebuild the properties
         */
        $_SWIFT_TicketObject->RebuildProperties();

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
                $_SWIFT->Language->Get('al_updticketbilling'),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '', ['al_updticketbilling']);

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
                array(SWIFT_CustomFieldGroup::GROUP_TIMETRACK), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_TicketTimeTrackObject->GetTicketTimeTrackID());

        $_SWIFT_TicketObject->RebuildProperties();

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3191: After editing a single custom field under ‘Ticket Time Tracking’ section, all other custom field entries display same updated value.
         *
         * Comments: Emptying the global requests in order to avoid reuse.
         */
        $GLOBALS['_POST']           = $GLOBALS['_REQUEST'] = $GLOBALS['_GET'] = array();
        $GLOBALS['_POST']['isajax'] = true;

        echo $this->View->RenderBillingEntries($_SWIFT_TicketObject);

        return true;
    }

    /**
     * Delete Billing Processer
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketTimeTrackID The Ticket Time Track ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteBilling($_ticketID, $_ticketTimeTrackID)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcandeletebilling') == '0') {
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

        $_SWIFT_TicketTimeTrackObject = new SWIFT_TicketTimeTrack(new SWIFT_DataID($_ticketTimeTrackID));
        if (!$_SWIFT_TicketTimeTrackObject instanceof SWIFT_TicketTimeTrack || !$_SWIFT_TicketTimeTrackObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_TicketTimeTrackObject->Delete();

        echo $this->View->RenderBillingEntries($_SWIFT_TicketObject);

        $_SWIFT_TicketObject->RebuildProperties();

        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            $_SWIFT->Language->Get('al_delbilling'),
            SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '', ['al_delbilling']);

        return true;
    }
}
