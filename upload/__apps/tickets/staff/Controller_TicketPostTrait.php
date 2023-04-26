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
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\HTML\SWIFT_HTML;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The View Ticket Controller
 *
 * @author Varun Shoor
 */
trait Controller_TicketPostTrait
{
    /**
     * Delete a ticket post
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeletePost($_ticketID, $_ticketPostID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcandeleteticketpost') == '0') {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;

        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        // Did the object load up?
        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID() ||
                $_SWIFT->Staff->GetPermission('staff_tcandeleteticketpost') == '0') {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Create Audit Log
        $_logText = sprintf($_SWIFT->Language->Get('al_deleteticketpost'), $_SWIFT_TicketPostObject->GetProperty('fullname'),
                $_SWIFT_TicketPostObject->GetProperty('email'));
        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_DELETETICKETPOST,
                $_logText, SWIFT_TicketAuditLog::VALUE_NONE, 0, '',
                0, '',
                ['al_deleteticketpost', $_SWIFT_TicketPostObject->GetProperty('fullname'), $_SWIFT_TicketPostObject->GetProperty('email')]);

        // Activity Log
        SWIFT_StaffActivityLog::AddToLog($_logText,
                SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        $_SWIFT_TicketPostObject->Delete();

        $_SWIFT_TicketObject->RebuildProperties();

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Edit a Ticket Post
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditPost($_ticketID, $_ticketPostID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupaticketpost') == '0') {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;

        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        // Did the object load up?
        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID() ||
                $_SWIFT->Staff->GetPermission('staff_tcanupaticketpost') == '0') {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editpost'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderEditPost($_SWIFT_TicketObject, $_SWIFT_TicketPostObject, $_listType, $_departmentID,
                $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit a ticket post (Submission)
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditPostSubmit($_ticketID, $_ticketPostID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupaticketpost') == '0') {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;
        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        // Did the object load up?
        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID() ||
                $_SWIFT->Staff->GetPermission('staff_tcanupaticketpost') == '0') {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUG FIX  - Verem Dugeri <verem.dugeri@crossover.com>
         * KAYAKO-3095 - XSS Security Vulnerability with HTML
         *
         * Comments - Removed test for tinyMCE editor. Initial implementation used html_special_chars for tinyMCE
         */
        $_isHTML = SWIFT_HTML::DetectHTMLContent($_POST['postcontents']);
        $_htmlSetting = $_SWIFT->Settings->GetString('t_ochtml');
        $_postContents = SWIFT_TicketPost::GetParsedContents($_POST['postcontents'], $_htmlSetting, $_isHTML);
        if ($_SWIFT->Settings->GetBool('t_tinymceeditor') && $_SWIFT->Settings->Get('t_chtml') === 'entities')
        {
            $_postContents = htmlspecialchars_decode($_postContents);
        }

        $_SWIFT_TicketPostObject->Update($_postContents, $_SWIFT->Staff->GetStaffID());

        // Create Audit Log
        $_logText = sprintf($_SWIFT->Language->Get('al_updateticketpost'), $_SWIFT_TicketPostObject->GetProperty('fullname'),
                $_SWIFT_TicketPostObject->GetProperty('email'));
        SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKETPOST,
                $_logText, SWIFT_TicketAuditLog::VALUE_NONE, 0, '',
                0, '',
                ['al_updateticketpost', $_SWIFT_TicketPostObject->GetProperty('fullname'), $_SWIFT_TicketPostObject->GetProperty('email')]);

        // Activity Log
        SWIFT_StaffActivityLog::AddToLog($_logText,
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Check validity of emails in  the input box
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name to Check On
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _CheckPOSTEmailContainer($_fieldName)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailCount = 0;

        $_postEmailValues = SWIFT_UserInterface::GetMultipleInputValues($_fieldName);
        if (_is_array($_postEmailValues))
        {
            foreach ($_postEmailValues as $_key => $_val)
            {
                if (!IsEmailValid($_val))
                {
                    return false;
                }

                $_emailCount++;
            }
        }

        if (!$_emailCount)
        {
            return false;
        }

        return true;
    }

    /**
     * Ticket Post Rating Handler
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RatingPost($_ticketID, $_ticketPostID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset($_POST['ratingid']) || empty($_POST['ratingid']) || !isset($_POST['ratingvalue'])) {
            return false;
        }

        if ($_SWIFT->Staff->GetPermission('staff_canviewratings') == '0' || $_SWIFT->Staff->GetPermission('staff_canupdateratings') == '0') {
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

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        if ( $_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_RatingObject = new SWIFT_Rating((int) ($_POST['ratingid']));
        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        SWIFT_RatingResult::CreateOrUpdateIfExists($_SWIFT_RatingObject, $_SWIFT_TicketPostObject->GetTicketPostID(), $_POST['ratingvalue'], SWIFT_RatingResult::CREATOR_STAFF, $_SWIFT->Staff->GetStaffID());

        $_SWIFT_TicketObject->MarkHasRatings();

        return true;
    }
}
