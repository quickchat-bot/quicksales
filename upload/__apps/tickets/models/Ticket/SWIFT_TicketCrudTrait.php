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

namespace Tickets\Models\Ticket;

use Parser\Models\Ban\SWIFT_ParserBan;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreFile;
use SWIFT_Base;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use SWIFT_Loader;
use SWIFT_Model;
use Base\Library\Notification\SWIFT_NotificationManager;
use Base\Models\Notification\SWIFT_NotificationRule;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;
use Tickets\Models\Watcher\SWIFT_TicketWatcher;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserNote;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationNote;
use Base\Models\User\SWIFT_UserSetting;
use Tickets\Library\Bayesian\SWIFT_Bayesian;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Library\Notification\SWIFT_TicketNotification;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\AutoClose\SWIFT_AutoCloseRule;
use Tickets\Models\Bayesian\SWIFT_BayesianCategory;
use Tickets\Models\Draft\SWIFT_TicketDraft;
use Tickets\Models\Escalation\SWIFT_EscalationPath;
use Tickets\Models\Escalation\SWIFT_EscalationRule;
use Tickets\Models\FollowUp\SWIFT_TicketFollowUp;
use Tickets\Models\Link\SWIFT_TicketLinkChain;
use Tickets\Models\Lock\SWIFT_TicketLock;
use Tickets\Models\Lock\SWIFT_TicketPostLock;
use Tickets\Models\Merge\SWIFT_TicketMergeLog;
use Tickets\Models\MessageID\SWIFT_TicketMessageID;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Note\SWIFT_TicketNoteManager;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;
use Tickets\Models\SLA\SWIFT_SLA;

trait SWIFT_TicketCrudTrait
{
    /**
     * Create a new Ticket
     *
     * @author Varun Shoor
     * @param string $_subject The Ticket Subject
     * @param string $_fullName The Full Name of Creator
     * @param string $_email The Email of Creator
     * @param string $_contents The Ticket Contents
     * @param int $_ownerStaffID The Ticket Owner Staff ID
     * @param int $_departmentID The Department ID
     * @param int $_ticketStatusID The Ticket Status ID
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @param int $_ticketTypeID The Ticket Type ID
     * @param int $_userID The User ID (If creator is user)
     * @param int $_staffID The Staff ID (If creator is staff)
     * @param mixed $_ticketType The Ticket Type (Default/Phone)
     * @param mixed $_creatorType The Creator Type
     * @param mixed $_creationMode The Creation Mode
     * @param string $_phoneNumber (OPTIONAL) The Phone Number of User
     * @param int $_emailQueueID (OPTIONAL) The Email Queue ID
     * @param bool $_dispatchAutoResponder (OPTIONAL) Whether to dispatch the autoresponder msg
     * @param string $_emailTo (OPTIONAL) Only to be used when creating tickets from staff cp and using send mail option. Signifies the destination email address.
     * @param bool $_isPrivate (OPTIONAL) Whether private ticket post
     * @return mixed "_SWIFT_TicketObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided or If the Object could not be created
     */

    public static function Create($_subject, $_fullName, $_email, $_contents, $_ownerStaffID, $_departmentID, $_ticketStatusID, $_ticketPriorityID, $_ticketTypeID,
            $_userID, $_staffID, $_ticketType, $_creatorType, $_creationMode, $_phoneNumber = '', $_emailQueueID = 0, $_dispatchAutoResponder = true,
            $_emailTo = '', $_isHTML = false, $_date = DATENOW, $_isPrivate = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentID =  ($_departmentID);
        $_ticketStatusID =  ($_ticketStatusID);
        $_ticketPriorityID =  ($_ticketPriorityID);
        $_userID =  ($_userID);
        $_ticketTypeID =  ($_ticketTypeID);

        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');
        if(empty($_ticketTypeID)) {
            // Set default ticket type ID
            foreach ($_ticketTypeCache as $__ticketTypeID => $_ticketTypeContainer) {
                break;
            }

        }

        if ($_subject == '' || empty($_fullName) || empty($_email) || $_contents == '' || empty($_departmentID) || empty($_ticketStatusID) ||    !self::IsValidCreatorType($_creatorType)
                || !self::IsValidCreationMode($_creationMode) || !self::IsValidTicketType($_ticketType))
        {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }


        // Sanity check.. IMPORTANT
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');

        if (!isset($_departmentCache[$_departmentID]) || !isset($_ticketStatusCache[$_ticketStatusID]) ||
                !isset($_ticketPriorityCache[$_ticketPriorityID]) || !isset($_ticketTypeCache[$_ticketTypeID])) {
            throw new SWIFT_Ticket_Exception('Invalid Core Data: Department, Status, Priority, Type');
        }

        if ($_departmentCache[$_departmentID]['departmentapp'] != APP_TICKETS) {
            throw new SWIFT_Ticket_Exception('Invalid Department App');
        }

        $_ticketMaskID = GenerateUniqueMask();

        $_isPhoneCall = false;
        if ($_ticketType == self::TYPE_PHONE)
        {
            $_isPhoneCall = true;
        }

        $_replyToEmail = $_email;
        if (!empty($_emailTo))
        {
            $_replyToEmail = $_emailTo;
        }

        $_isEmailed = false;
        if ($_creationMode == self::CREATIONMODE_EMAIL)
        {
            $_isEmailed = true;
        }

        $_ipAddress = '';
        if ($_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_CRON && $_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_CONSOLE)
        {
            $_ipAddress = SWIFT::Get('IP');
        }

        $_SWIFT_BayesianObject = new SWIFT_Bayesian();
        $_finalBayesCategoryID = 0;
        $_probabilityContainer = $_SWIFT_BayesianObject->Get($_subject . ' ' . $_contents);
        if (_is_array($_probabilityContainer))
        {
            $_finalBayesBenchmark = 0;
            foreach ($_probabilityContainer[0] as $_bayesCategoryID => $_probability)
            {
                if ($_probability['combined'] >= 0.500 && $_probability['combined'] > $_finalBayesBenchmark)
                {
                    $_finalBayesCategoryID = $_bayesCategoryID;
                    $_finalBayesBenchmark = $_probability['combined'];
                }
            }
        }

        $_subject  = $_SWIFT->Emoji->encode($_subject);
        $_contents = $_SWIFT->Emoji->encode($_contents);

        $_fullName = text_to_html_entities($_fullName, 1, true, true);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('ticketmaskid' => $_ticketMaskID, 'departmentid' =>  ($_departmentID),
            'ticketstatusid' =>  ($_ticketStatusID), 'priorityid' =>  ($_ticketPriorityID), 'emailqueueid' =>  ($_emailQueueID),
            'userid' =>  ($_userID), 'staffid' =>  ($_staffID), 'fullname' => $_fullName, 'email' => mb_strtolower($_email), 'lastreplier' => $_fullName,
            'replyto' => $_replyToEmail, 'subject' => $_subject, 'dateline' => $_date, 'lastactivity' => DATENOW, 'ipaddress' => $_ipAddress,
            'isemailed' => (int) ($_isEmailed), 'isphonecall' => (int) ($_isPhoneCall), 'creator' => (int) ($_creatorType),
            'tickettype' => (int) ($_ticketType), 'phoneno' => $_phoneNumber, 'tickettypeid' =>  ($_ticketTypeID), 'tickethash' => substr(BuildHash(), 0, 12),
            'creationmode' => (int) ($_creationMode), 'bayescategoryid' => (int) ($_finalBayesCategoryID)), 'INSERT');
        $_ticketID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketID)
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CREATEFAILED);
        }

        $_departmentTitle = text_to_html_entities($_departmentCache[$_departmentID]['title']);
        $_statusTitle = $_ticketStatusCache[$_ticketStatusID]['title'];
        $_typeTitle = $_ticketTypeCache[$_ticketTypeID]['title'];
        $_priorityTitle = $_ticketPriorityCache[$_ticketPriorityID]['title'];

        $_SWIFT_TicketObject->UpdatePool('departmenttitle', $_departmentTitle);
        $_SWIFT_TicketObject->UpdatePool('ticketstatustitle', $_statusTitle);
        $_SWIFT_TicketObject->UpdatePool('tickettypetitle', $_typeTitle);
        $_SWIFT_TicketObject->UpdatePool('prioritytitle', $_priorityTitle);

        // Create the ticket post..
        $_creatorID = $_staffID;
        if ($_creatorType == self::CREATOR_CLIENT) {
            $_creatorID = $_userID;
        }

        // Get Email Queue Name
        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_emailQueueAddress = '';
        if (isset($_emailQueueCache['list'][$_emailQueueID])) {
            $_emailQueueAddress = $_emailQueueCache['list'][$_emailQueueID]['email'];
        }

        // Create Audit Log

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1657 Audit Log does not show email queue address via which ticket is created
         *
         * Comments: None
         */
        if (!empty($_emailQueueAddress)) {
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null,
	            SWIFT_TicketAuditLog::ACTION_NEWTICKET,
	            sprintf($_SWIFT->Language->Get('al_newticket_queue'), $_fullName, $_email, $_subject, $_emailQueueAddress),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
                ['al_newticket_queue', $_fullName, $_email, $_subject, $_emailQueueAddress]);
        } else {
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null,
	            SWIFT_TicketAuditLog::ACTION_NEWTICKET, sprintf($_SWIFT->Language->Get('al_newticket'), $_fullName, $_email, $_subject),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
                ['al_newticket', $_fullName, $_email, $_subject]);
        }

        $_ticketPostID = SWIFT_TicketPost::Create($_SWIFT_TicketObject, $_fullName, $_email, $_contents, $_creatorType, $_creatorID, $_creationMode,
            $_subject, $_emailTo, $_isHTML, false, false, $_date, $_isPrivate);

        $_SWIFT_TicketObject->UpdatePool('firstpostid', $_ticketPostID);
        $_SWIFT_TicketObject->UpdatePool('lastpostid', $_ticketPostID);
        $_SWIFT_TicketObject->UpdatePool('totalreplies', '0');


        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-965 When a new ticket is created with status "closed", 'Clear due time' setting does not work
         *
         * Comments: None
         */

        // SLA & Status Properties
        $_ticketStatusContainer = $_ticketStatusCache[$_ticketStatusID];
        if ($_ticketStatusContainer['resetduetime'] == '1') {
            $_SWIFT_TicketObject->UpdatePool('duetime', '0');
        }

        if ($_ticketStatusContainer['markasresolved'] == '1')
        {
            $_SWIFT_TicketObject->UpdatePool('isresolved', '1');
            $_SWIFT_TicketObject->UpdatePool('resolutiondateline', DATENOW);
            $_SWIFT_TicketObject->UpdatePool('repliestoresolution', $_SWIFT_TicketObject->GetProperty('totalreplies'));

            // How much time did it take to resolve this ticket?
            $_SWIFT_TicketObject->UpdatePool('resolutionseconds', DATENOW-$_SWIFT_TicketObject->GetProperty('dateline'));
        }

        // If the status is set to resolved then we reset the resolution due time
        $_processResolutionDue = false;
        if ($_ticketStatusContainer['markasresolved'] == '1') {
            $_SWIFT_TicketObject->UpdatePool('resolutionduedateline', '0');
            $_SWIFT_TicketObject->UpdatePool('duetime', '0');

            $_SWIFT_TicketObject->_noSLACalculation = true;

        // Otherwise if its not, and resolutionduedateline is 0 then we force it to recalculate the resolution due time
        } else if ($_ticketStatusContainer['markasresolved'] == '0' && $_SWIFT_TicketObject->GetProperty('resolutionduedateline') == '0') {
            $_processResolutionDue = true;
        }

        if ($_isEmailed) {
            $_SWIFT_TicketObject->_noSLACalculation = true;
        }

        // Set the ticket owner
        if (!empty($_ownerStaffID)) {
            try {
                $_SWIFT_TicketObject->SetOwner(($_ownerStaffID));
            } catch (\Exception $ex) {
                // staff id is invalid
            }
        }

        $_SWIFT_TicketObject->ProcessSLAOverdue($_processResolutionDue);

        // Notification Event
        $_SWIFT_TicketObject->NotificationManager->SetEvent('newticket');

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($_SWIFT_TicketObject);

        // Recount the cache
        SWIFT_TicketManager::Recount(false);

        if ($_dispatchAutoResponder == true)
        {
            register_shutdown_function(array($_SWIFT_TicketObject, 'DispatchAutoresponder'));
        }

        return $_SWIFT_TicketObject;
    }

    /**
     * Delete the Ticket record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Tickets
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_finalTicketIDList = $_departmentIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketIDList[] = (int) ($_SWIFT->Database->Record['ticketid']);

            $_departmentIDList[] = (int) ($_SWIFT->Database->Record['departmentid']);

            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataSTore($_SWIFT->Database->Record));

            // Create Audit Log
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_DELETETICKET,
                sprintf($_SWIFT->Language->Get('al_deleteticket'), $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
                ['al_deleteticket', $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']]);
        }

        if (!count($_finalTicketIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_finalTicketIDList) . ")");

        // Clear Ticket Posts
        SWIFT_TicketPost::DeleteOnTicket($_finalTicketIDList);

        // Clear Attachments
        SWIFT_Attachment::DeleteOnTicket($_finalTicketIDList);

        // Clear Notes
        SWIFT_TicketNote::DeleteOnTicket($_finalTicketIDList);

        // Clear Ticket Drafts
        SWIFT_TicketDraft::DeleteOnTicket($_finalTicketIDList);

        // Release the Locks
        SWIFT_TicketLock::DeleteOnTicket($_finalTicketIDList);
        SWIFT_TicketPostLock::DeleteOnTicket($_finalTicketIDList);

        // Clear the Recipients
        SWIFT_TicketRecipient::DeleteOnTicket($_finalTicketIDList);

        // Clear the Audit Logs
        SWIFT_TicketAuditLog::DeleteOnTicket($_finalTicketIDList);

        // Clear the Time Track entries
        SWIFT_TicketTimeTrack::DeleteOnTicket($_finalTicketIDList);

        // Clear the Message IDs
        SWIFT_TicketMessageID::DeleteOnTicket($_finalTicketIDList);

        // Clear the Linked Table IDs
        SWIFT_TicketLinkedTable::DeleteOnTicket($_finalTicketIDList);

        // Clear the ticket watchers
        SWIFT_TicketWatcher::DeleteOnTicket($_finalTicketIDList);

        // Clear the chains
        SWIFT_TicketLinkChain::DeleteOnTicket($_finalTicketIDList);

        // Delete the merge logs
        SWIFT_TicketMergeLog::DeleteOnTicket($_finalTicketIDList);

        // Delete the Ticket Follow-Up's
        SWIFT_TicketFollowUp::DeleteOnTicket($_finalTicketIDList);

        // Delete the Ticket Escalation Paths
        SWIFT_EscalationPath::DeleteOnTicket($_finalTicketIDList);

        // Delete the Ticket Recurrence
        SWIFT_TicketRecurrence::DeleteOnTicket($_finalTicketIDList);

        // Delete the tags
        SWIFT_TagLink::DeleteOnLinkList(SWIFT_TagLink::TYPE_TICKET, $_finalTicketIDList);

        // Rebuild count cache
        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Un-Delete a list of Tickets
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UnDeleteList($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_finalTicketIDList = $_departmentIDList = $_ticketContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketContainer[$_SWIFT->Database->Record['ticketid']] = $_SWIFT->Database->Record;

            $_finalTicketIDList[] = (int) ($_SWIFT->Database->Record['ticketid']);

            $_departmentIDList[] = (int) ($_SWIFT->Database->Record['departmentid']);

            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataSTore($_SWIFT->Database->Record));

            // Create Audit Log
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_DELETETICKET,
                sprintf($_SWIFT->Language->Get('al_untrashticket'), $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
                ['al_untrashticket', $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']]);
        }

        if (!count($_finalTicketIDList))
        {
            return false;
        }

        foreach ($_finalTicketIDList as $_ticketID)
        {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('departmentid' => (int) ($_ticketContainer[$_ticketID]['trasholddepartmentid'])), 'UPDATE', "ticketid = '" .  ($_ticketID) . "'");
        }

        // Rebuild count cache
        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Merge a list of tickets together
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @param int $_parentTicketID (OPTIONAL) The Parent Ticket ID
     * @param int $_staffID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     * @throws \Base\Library\Tag\SWIFT_Tag_Exception
     * @throws \Tickets\Models\MessageID\SWIFT_MessageID_Exception
     */
    public static function Merge($_ticketIDList, $_parentTicketID = 0, $_staffID = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList) || (count($_ticketIDList) < 2 && empty($_parentTicketID))) {
            return false;
        }

        $_departmentIDList = $_ticketMaskIDList = $_mergeTicketIDList = $_mergeEmailList = array();

        if (!$_parentTicketID) {
            $_parentTicketID = $_ticketIDList[0];
        }

        // Load the parent ticket
        $_SWIFT_ParentTicketObject = new SWIFT_Ticket(new SWIFT_DataID($_parentTicketID));
        if (!$_SWIFT_ParentTicketObject instanceof SWIFT_Ticket || !$_SWIFT_ParentTicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_departmentIDList[] = $_SWIFT_ParentTicketObject->GetProperty('departmentid');

        // Load the other objects
        $_ticketObjectContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['ticketid'] == $_parentTicketID) {
                continue;
            }

            $_ticketObjectContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));

            // Create Audit Log
            $_SWIFT_TicketObject_Merge = $_ticketObjectContainer[$_SWIFT->Database->Record['ticketid']];
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject_Merge, null, SWIFT_TicketAuditLog::ACTION_MERGETICKET,
                sprintf($_SWIFT->Language->Get('al_merge'), $_SWIFT_TicketObject_Merge->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
                ['al_merge', $_SWIFT_TicketObject_Merge->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']]);

            if (!in_array($_SWIFT->Database->Record['departmentid'], $_departmentIDList)) {
                $_departmentIDList[] = $_SWIFT->Database->Record['departmentid'];
            }

            $_ticketMaskIDList[] = $_SWIFT->Database->Record['ticketmaskid'];

            $_mergeTicketIDList[] = $_SWIFT->Database->Record['ticketid'];

            if (!in_array($_SWIFT->Database->Record['email'], $_mergeEmailList) &&
                    $_SWIFT->Database->Record['email'] != $_SWIFT_ParentTicketObject->GetProperty('email')) {
                $_mergeEmailList[] = $_SWIFT->Database->Record['email'];
            }
        }

        /**
         * @todo Raise Alert Here
         */

        // By now we have the parent ticket and the child tickets, we need to start the merge process now.

        // Update Notes. !! IMPORTANT: This needs to be called before ticket posts are updated. !!
        SWIFT_TicketNoteManager::ReplaceTicket($_mergeTicketIDList, $_SWIFT_ParentTicketObject);

        // Update Ticket Posts
        SWIFT_TicketPost::ReplaceTicket($_mergeTicketIDList, $_SWIFT_ParentTicketObject);

        // Update Attachments
        SWIFT_Attachment::ReplaceTicket($_mergeTicketIDList, $_SWIFT_ParentTicketObject);

        // Update the Recipients
        SWIFT_TicketRecipient::ReplaceTicket($_mergeTicketIDList, $_SWIFT_ParentTicketObject);

        // Update the Audit Logs
        SWIFT_TicketAuditLog::ReplaceTicket($_mergeTicketIDList, $_SWIFT_ParentTicketObject);

        // Update the Time Track entries
        SWIFT_TicketTimeTrack::ReplaceTicket($_mergeTicketIDList, $_SWIFT_ParentTicketObject);

        // Update the Ticket Follow-Up's
        SWIFT_TicketFollowUp::ReplaceTicket($_mergeTicketIDList, $_SWIFT_ParentTicketObject);

        // Add all the recipients if needed
        if ($_SWIFT->Settings->Get('t_mergrecip') == 1 && count($_mergeEmailList))
        {
            SWIFT_TicketRecipient::Create($_SWIFT_ParentTicketObject, SWIFT_TicketRecipient::TYPE_CC, $_mergeEmailList);
        }

        // Recaclulate all properties
        $_SWIFT_ParentTicketObject->RebuildProperties();

        /**
         * @todo Add Audit Log Entry
         */

        // First add the Merge Log List.. this is used to route replies to the right ticket.
        foreach ($_ticketObjectContainer as $_ticketID => $_SWIFT_TicketObject) {
            SWIFT_TicketMergeLog::Create($_SWIFT_ParentTicketObject, $_SWIFT_TicketObject->GetTicketID(),
                    $_SWIFT_TicketObject->GetProperty('ticketmaskid'), $_staffID);
        }

        // Delete JUST the ticket entries
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_mergeTicketIDList) . ")");

        // Clear Ticket Drafts
        SWIFT_TicketDraft::DeleteOnTicket($_mergeTicketIDList);

        // Release the Locks
        SWIFT_TicketLock::DeleteOnTicket($_mergeTicketIDList);
        SWIFT_TicketPostLock::DeleteOnTicket($_mergeTicketIDList);

        // Clear the Message IDs
        SWIFT_TicketMessageID::DeleteOnTicket($_mergeTicketIDList);

        // Clear the Linked Table IDs
        SWIFT_TicketLinkedTable::DeleteOnTicket($_mergeTicketIDList);

        // Clear the ticket watchers
        SWIFT_TicketWatcher::DeleteOnTicket($_mergeTicketIDList);

        // Clear the chains
        SWIFT_TicketLinkChain::DeleteOnTicket($_mergeTicketIDList);

        // Delete the Ticket Escalation Paths
        SWIFT_EscalationPath::DeleteOnTicket($_mergeTicketIDList);

        // Delete the Ticket Recurrence
        SWIFT_TicketRecurrence::DeleteOnTicket($_mergeTicketIDList);

        // Delete the tags
        SWIFT_TagLink::DeleteOnLinkList(SWIFT_TagLink::TYPE_TICKET, $_mergeTicketIDList);

        // Time for a recount
        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Move the Ticket record to trash
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function Trash()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::TrashList(array($this->GetTicketID()));

        return true;
    }

    /**
     * Trash a list of Tickets
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function TrashList($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_finalTicketIDList = $_departmentIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
            $_SWIFT_TicketObject->UpdateTrashDepartment();

            $_finalTicketIDList[] = (int) ($_SWIFT->Database->Record['ticketid']);

            $_departmentIDList[] = (int) ($_SWIFT->Database->Record['departmentid']);

            // Create Audit Log
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_TRASHTICKET,
                sprintf($_SWIFT->Language->Get('al_trashticket'), $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
                ['al_trashticket', $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['subject'],
                        $_SWIFT->Database->Record['fullname'], $_SWIFT->Database->Record['email']]);
        }

        if (!count($_finalTicketIDList))
        {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array('departmentid' => '0'), 'UPDATE', "ticketid IN (" .
                BuildIN($_finalTicketIDList) . ")");

        // Rebuild count cache
        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Ban the creator of this ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @param int $_staffID (OPTIONAL) The Staff ID initiating the Ban
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function Ban($_staffID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::BanList(array($this->GetTicketID()), $_staffID);

        return true;
    }

    /**
     * Ban a list of ticket ids
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List to ban
     * @param int $_staffID (OPTIONAL) The Staff ID initiating the Ban
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function BanList($_ticketIDList, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_rejectEmailList = array();

        foreach ($_staffCache as $_key => $_val)
        {
            $_rejectEmailList[] = mb_strtolower($_val['email']);
        }

        if (_is_array($_emailQueueCache) && isset($_emailQueueCache['list']) && _is_array($_emailQueueCache['list']))
        {
            foreach ($_emailQueueCache['list'] as $_key => $_val)
            {
                $_rejectEmailList[] = mb_strtolower($_val['email']);
            }
        }

        $_finalEmailBanList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));

            // Create Audit Log
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null, SWIFT_TicketAuditLog::ACTION_BAN,
                sprintf($_SWIFT->Language->Get('al_ban'), $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['email']),
                SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
                ['al_ban', $_SWIFT_TicketObject->GetTicketDisplayID(), $_SWIFT->Database->Record['email']]);

            if (!in_array(mb_strtolower($_SWIFT->Database->Record['email']), $_finalEmailBanList) &&
                    !in_array(mb_strtolower($_SWIFT->Database->Record['email']), $_rejectEmailList))
            {
                $_finalEmailBanList[] = mb_strtolower($_SWIFT->Database->Record['email']);
            }
        }

        if (!count($_finalEmailBanList))
        {
            return false;
        }

        SWIFT_Loader::LoadModel('Ban:ParserBan', APP_PARSER);

        if (class_exists('\Parser\Models\Ban\SWIFT_ParserBan', false))
        {
            foreach ($_finalEmailBanList as $_key => $_val)
            {
                SWIFT_ParserBan::Create($_val, $_staffID);
            }
        }

        return true;
    }

    /**
     * process the SLA Overdue time
     *
     * @author Varun Shoor
     * @param bool $_processResolutionDue (OPTIONAL) Whether to process the resolution due dateline
     * @param bool $_processReplyDue      (OPTIONAL)
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ProcessSLAOverdue($_processResolutionDue = false, $_processReplyDue = true)
    {
        $_SWIFT = SWIFT::GetInstance();
        chdir(SWIFT_BASEPATH);
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_noSLACalculation == true || $this->GetProperty('isresolved') == '1') {
            return false;
        }

        $this->Load->Library('SLA:SLAManager', [], true, false, 'tickets');

        $_slaManagerResult = $this->SLAManager->GetDueTime($this);
        if (count($_slaManagerResult) != 2) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SLAPlanObject = $_slaManagerResult[0];
        $_overDueSeconds = $_slaManagerResult[1];

        $_lapsedReplyDueSeconds = 0;
        $_lapsedResponseDueSeconds = 0;
        $_lapsedSeconds = 0;
        if (($this->GetProperty('slaplanid') > 0 && ($_SWIFT_SLAPlanObject instanceof SWIFT_SLA && $_SWIFT_SLAPlanObject->GetSLAPlanID() > 0)) && isset($this->_dataStore['slaplanid']) && $this->GetProperty('slaplanid') != $_SWIFT_SLAPlanObject->GetSLAPlanID() && (!$_processResolutionDue || !$_processReplyDue )) {
            $_SWIFT_SLAPlanObjectOld = new SWIFT_SLA(new SWIFT_DataID($this->GetProperty('slaplanid')));
            $_dueTime = $this->GetProperty('duetime');
            if (isset($_dueTime) && $_dueTime > 0) {
                $_timeLeftOnOldSlaPlan = $this->SLAManager->GetSLAResponseTime($_SWIFT_SLAPlanObjectOld, DATENOW, $_dueTime);
                $_slaReplyDueDurationInSeconds =  $this->SLAManager->GetSecondsFromHour($_SWIFT_SLAPlanObjectOld->GetProperty('overduehrs'));
                $_lapsedSeconds = $_slaReplyDueDurationInSeconds - $_timeLeftOnOldSlaPlan;
            }
        }

        if(!$_processResolutionDue) {
            $_lapsedResponseDueSeconds = $_lapsedSeconds;
        }

        if(!$_processResolutionDue) {
            $_lapsedReplyDueSeconds = $_lapsedSeconds;
        }

        if ($_SWIFT_SLAPlanObject instanceof SWIFT_SLA && $_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
            // Notification Rule
            $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_SLAPLAN, $this->GetProperty('slaplanid'), $_SWIFT_SLAPlanObject->GetSLAPlanID());

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-970 Resolution time is not updated according to SLA plan, while changing the ticket department using Mass Action
             *
             * Comments: We now force resolution due time recalculation if SLA plan changes
             */
            if ($this->GetProperty('slaplanid') != $_SWIFT_SLAPlanObject->GetSLAPlanID()) {
                $_processResolutionDue = true;
                $_processReplyDue      = true;
            }

            $this->UpdatePool('slaplanid', $_SWIFT_SLAPlanObject->GetSLAPlanID());
            $this->UpdatePool('isescalatedvolatile', '0');

            /**
             * Bug Fix : Saloni Dhall
             *
             * SWIFT-4355 : Exact SLA plan set while creation or occuring due to ticket properties change should be logged in Ticket Audit Logs
             *
             * Comments : ProcessSLAOverdue() and QueueSLAOverdue() methods called directly in helpdesk at many places instead of SetSLA() method.
             */
            // Create Audit Log
            SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATESLA,
                                           sprintf($_SWIFT->Language->Get('al_sla'), $_SWIFT_SLAPlanObject->GetProperty('title')),
                                           SWIFT_TicketAuditLog::VALUE_NONE,
	                                        $this->GetProperty('ticketslaplanid'), '',
	                                        $_SWIFT_SLAPlanObject->GetSLAPlanID(), '',
                                            ['al_sla', $_SWIFT_SLAPlanObject->GetProperty('title')]);
            /**
             * BUG FIX - Parminder Singh
             *
             * SWIFT-795: Issue with SLA plan retention
             *
             * Comments: If no SLA found then clear the exising one.
             */
        } else {
            // $this->UpdatePool('slaplanid', '0');
        }

        /**
         * BUG FIX - Parminder Singh
         *
         * SWIFT-1583: "Clear the Due Time" setting under Ticket Statuses settings, does not work properly
         *
         * Comments: If the setting 'Clear the Due Time' is disabled and overdue seconds ($_overDueSeconds) are coming blank then no need for SLA/ overdue hours.
         */
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        if (isset($_ticketStatusCache[$this->GetProperty('ticketstatusid')])) {
            $_ticketStatusContainer = $_ticketStatusCache[$this->GetProperty('ticketstatusid')];

            /**
             * BUG FIX - Ashish Kataria
             *
             * SWIFT-1946: "Clear the Due Time" setting under Ticket Statuses settings, does not work properly
             *
             * Comments: If the setting 'Clear the Due Time' is enabled and ticket has been marked as resolved then no need for SLA/ overdue hours when it is updated without changing the status.
             */
            if (($_ticketStatusContainer['resetduetime'] == '0' && empty($_overDueSeconds) && $this->GetProperty('duetime') != '0')
                || ($_ticketStatusContainer['resetduetime'] == '1' && $_ticketStatusContainer['markasresolved'] == '1')
            ) {
                $this->_noSLACalculation = true;

                return true;
            }
        }

        /*
         * BUG FIX - Abhishek Mittal
         *
         * SWIFT-3835: Incorrect SLA plan gets linked with the ticket when ticket is moved from a resolved status to unresolved status
         *
         * Comments: Update reply due time only in case if SLA plan changed
         */
        /* Bug Fix : Saloni Dhall
         * SWIFT-3867 : Due time get reset on performing an action on a ticket, which is in status where setting 'Clear ticket reply deadline when set to this status' is enabled.
         * Comments : if the ticketstatus setting 'Clear ticket reply deadline' already been set to yes, then no need to set the duetime while changing ticket properties in the same status.
         */
        if ($_processReplyDue === true && (isset($_ticketStatusCache[$this->GetProperty('ticketstatusid')]['resetduetime']) && $_ticketStatusCache[$this->GetProperty('ticketstatusid')]['resetduetime'] != '1')) {

            // Create Audit Log
            $_renderedDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_overDueSeconds);
	        $_renderedDateParam = SWIFT_TicketAuditLog::DATETIME_PARAM.$_overDueSeconds;
            $_overDueSeconds = ($_overDueSeconds - ($_lapsedReplyDueSeconds + time())) > 0 ? $_overDueSeconds - $_lapsedReplyDueSeconds : 0;
            if (empty($_overDueSeconds)) {
                $_renderedDate = $this->Language->Get('duetimecleared');
	            $_renderedDateParam = SWIFT_TicketAuditLog::PHRASE_PARAM.'duetimecleared';
            }

            SWIFT_TicketAuditLog::AddToLog($this, null,
	            SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
	            sprintf($_SWIFT->Language->Get('al_due'), $_renderedDate),
	            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('duetime'), '', $_overDueSeconds, '',
                ['al_due', $_renderedDateParam]);

            $this->UpdatePool('duetime', (int) ($_overDueSeconds));
        }

        if ($_processResolutionDue == true) {
            if (!$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded())
            {
                $_SWIFT_SLAPlanObject = null;
            }

            $_slaManagerResult_ResolutionDue = $this->SLAManager->GetResolutionTime($this, $_SWIFT_SLAPlanObject);
            $_resolutionOverDueSeconds = $_slaManagerResult_ResolutionDue[1];

            // Create Audit Log
            $_resolutionRenderedDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_resolutionOverDueSeconds);
	        $_resolutionRenderedDateParam = SWIFT_TicketAuditLog::DATETIME_PARAM.$_resolutionOverDueSeconds;
            $_resolutionOverDueSeconds = ($_resolutionOverDueSeconds - $_lapsedResponseDueSeconds) > 0 ? $_resolutionOverDueSeconds - $_lapsedResponseDueSeconds : 0;
            if (empty($_resolutionOverDueSeconds)) {
                $_resolutionRenderedDate = $this->Language->Get('duetimecleared');
	            $_resolutionRenderedDateParam = SWIFT_TicketAuditLog::PHRASE_PARAM.'duetimecleared';
            }
            SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
                sprintf($_SWIFT->Language->Get('al_resolutiondue'), $_resolutionRenderedDate),
                SWIFT_TicketAuditLog::VALUE_NONE,
	            $this->GetProperty('resolutionduedateline'), '', $_resolutionOverDueSeconds, '',
                ['al_resolutiondue', $_resolutionRenderedDateParam]);

            $this->UpdatePool('resolutionduedateline', (int) ($_resolutionOverDueSeconds));
        }

        $this->_noSLACalculation = true;

        return true;
    }

    /**
     * Queue the SLA Overdue calculation on shutdown
     *
     * @author Varun Shoor
     * @param int $_processResolutionDue (OPTIONAL) Whether to process the resolution due dateline
     * @param int $_processReplyDue      (OPTIONAL)
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QueueSLAOverdue($_processResolutionDue = 0, $_processReplyDue = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $intName = \SWIFT::GetInstance()->Interface->GetName()?: SWIFT_INTERFACE;
        if ($this->_slaOverdueQueued != -1 || $intName === 'tests') {
            return true;
        }

        $this->_slaOverdueQueued = $_processResolutionDue;

        register_shutdown_function(array($this, 'ProcessSLAOverdue'), $this->_slaOverdueQueued, $_processReplyDue);

        return true;
    }

    /**
     * Clear the fixed Ticket SLA Plan
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ClearSLA() {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('ticketslaplanid') == '0') {
            return true;
        }

        $_slaPlanCache = $this->Cache->Get('slaplancache');

        // Create Audit Log
        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATESLA,
            $_SWIFT->Language->Get('al_slaclear'),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('ticketslaplanid'),
	        '', 0, '', ['al_slaclear']);

        // Notification Update
        $_oldTicketSLAPlanID = $this->GetProperty('ticketslaplanid');
        $_oldSLATitle = $_newSLATitle = '';
        if ($_oldTicketSLAPlanID != '0') {
            if (isset($_slaPlanCache[$_oldTicketSLAPlanID])) {
                $_oldSLATitle = $_slaPlanCache[$_oldTicketSLAPlanID]['title'];
            } else {
                $_oldSLATitle = $this->Language->Get('na');
            }
        }

        $_newSLATitle = $this->Language->Get('notificationcleared');

        $this->Notification->Update($this->Language->Get('notification_sla'), $_oldSLATitle, $_newSLATitle);

        $this->UpdatePool('ticketslaplanid', 0);

        $this->QueueSLAOverdue();

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Escalate this Ticket
     *
     * @author Varun Shoor
     * @param SWIFT_EscalationRule $_SWIFT_EscalationRuleObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Escalate(SWIFT_EscalationRule $_SWIFT_EscalationRuleObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_EscalationRuleObject instanceof SWIFT_EscalationRule || !$_SWIFT_EscalationRuleObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        // We execute this before all arguments so that if a custom SLA plan is set, it can override this value.
        $this->UpdatePool('isescalatedvolatile', '1');

        SWIFT_EscalationPath::Create($this, $this->GetProperty('slaplanid'), $_SWIFT_EscalationRuleObject->GetEscalationRuleID(),
                $this->GetProperty('ownerstaffid'), $this->GetProperty('departmentid'), $this->GetProperty('ticketstatusid'),
                $this->GetProperty('priorityid'), $this->GetProperty('tickettypeid'), $this->GetProperty('flagtype'));

        if ($_SWIFT_EscalationRuleObject->GetProperty('staffid') != '-1' && $_SWIFT_EscalationRuleObject->GetProperty('staffid') != '0' && isset($_staffCache[$_SWIFT_EscalationRuleObject->GetProperty('staffid')]))
        {
            $this->SetOwner($_SWIFT_EscalationRuleObject->GetProperty('staffid'));
        }

        if ($_SWIFT_EscalationRuleObject->GetProperty('departmentid') != '0' && $_SWIFT_EscalationRuleObject->GetProperty('departmentid') != '-1')
        {
            $this->SetDepartment($_SWIFT_EscalationRuleObject->GetProperty('departmentid'));
        }

        if ($_SWIFT_EscalationRuleObject->GetProperty('ticketstatusid') != '0' && $_SWIFT_EscalationRuleObject->GetProperty('ticketstatusid') != '-1')
        {
            $this->SetStatus($_SWIFT_EscalationRuleObject->GetProperty('ticketstatusid'));
        }

        if ($_SWIFT_EscalationRuleObject->GetProperty('priorityid') != '0' && $_SWIFT_EscalationRuleObject->GetProperty('priorityid') != '-1')
        {
            $this->SetPriority($_SWIFT_EscalationRuleObject->GetProperty('priorityid'));
        }

        if ($_SWIFT_EscalationRuleObject->GetProperty('tickettypeid') != '0' && $_SWIFT_EscalationRuleObject->GetProperty('tickettypeid') != '-1')
        {
            $this->SetType($_SWIFT_EscalationRuleObject->GetProperty('tickettypeid'));
        }

        if ($_SWIFT_EscalationRuleObject->GetProperty('flagtype') != '0' && $_SWIFT_EscalationRuleObject->GetProperty('flagtype') != '-1')
        {
            $this->SetFlag($_SWIFT_EscalationRuleObject->GetProperty('flagtype'));
        }

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4444: "Disabled SLA plan gets applied on the ticket if set in the triggered Escalation rule."
         *
         * Comments: If the SLA plan is enabled only than it is applied.
         */
        if ($_SWIFT_EscalationRuleObject->GetProperty('newslaplanid') != '0' && $_SWIFT_EscalationRuleObject->GetProperty('newslaplanid') != '-1') {
            $_slaObject = new SWIFT_SLA(new SWIFT_DataID($_SWIFT_EscalationRuleObject->GetProperty('newslaplanid')));
            if ($_slaObject->GetProperty('isenabled') == '1') {
                $this->SetSLA($_slaObject);
            }
        }

        if ($_SWIFT_EscalationRuleObject->GetProperty('addtags') != '')
        {
            $_tagContainer = json_decode($_SWIFT_EscalationRuleObject->GetProperty('addtags'));
            if (is_array($_tagContainer)) {
                SWIFT_Tag::AddTags(SWIFT_TagLink::TYPE_TICKET, $this->GetTicketID(),
                        $_tagContainer, 0);
            }
        }

        if ($_SWIFT_EscalationRuleObject->GetProperty('removetags') != '')
        {
            $_tagContainer = json_decode($_SWIFT_EscalationRuleObject->GetProperty('removetags'));
            if (is_array($_tagContainer)) {
                SWIFT_Tag::RemoveTags(SWIFT_TagLink::TYPE_TICKET, array($this->GetTicketID()),
                    $_tagContainer, 0);
            }

        }

        $this->UpdatePool('escalationruleid', $_SWIFT_EscalationRuleObject->GetEscalationRuleID());
        $this->UpdatePool('isescalated', '1');
        $this->UpdatePool('escalatedtime', DATENOW);
        $this->UpdatePool('escalationlevelcount', ($this->GetProperty('escalationlevelcount')+1));

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Rebuilds the titles associated with the ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RebuildTitles()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_escalationRuleCache = $this->Cache->Get('escalationrulecache');

        // Build local properties
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $this->UpdatePool('departmenttitle', $_departmentCache[$this->GetProperty('departmentid')]['title']);
        }

        if (isset($_staffCache[$this->GetProperty('ownerstaffid')])) {
            $this->UpdatePool('ownerstaffname', $_staffCache[$this->GetProperty('ownerstaffid')]['fullname']);
        }

        if (isset($_ticketStatusCache[$this->GetProperty('ticketstatusid')])) {
            $this->UpdatePool('ticketstatustitle', $_ticketStatusCache[$this->GetProperty('ticketstatusid')]['title']);
        }

        if (isset($_ticketPriorityCache[$this->GetProperty('priorityid')])) {
            $this->UpdatePool('prioritytitle', $_ticketPriorityCache[$this->GetProperty('priorityid')]['title']);
        }

        if (isset($_ticketTypeCache[$this->GetProperty('tickettypeid')])) {
            $this->UpdatePool('tickettypetitle', $_ticketTypeCache[$this->GetProperty('tickettypeid')]['title']);
        }

        // Ticket Drafts
        $_ticketDraftUpdateContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketdrafts WHERE ticketid = '" . $this->GetTicketID() . "'");
        while ($this->Database->NextRecord()) {
            $_ticketDraftUpdateContainer[$this->Database->Record['ticketdraftid']] = $this->Database->Record;

            /*
             * BUG FIX - Abhishek Mittal
             *
             * SWIFT-3833: Illegal string offset 'staffname'
             */
            if (isset($_staffCache[$this->Database->Record['staffid']])) {
                $_ticketDraftUpdateContainer[$this->Database->Record['ticketdraftid']]['staffname'] = $_staffCache[$this->Database->Record['staffid']]['fullname'];
            }

            if (isset($_staffCache[$this->Database->Record['editedbystaffid']])) {
                $_ticketDraftUpdateContainer[$this->Database->Record['ticketdraftid']]['editedstaffname'] = $_staffCache[$this->Database->Record['editedbystaffid']]['fullname'];
            }
        }

        foreach ($_ticketDraftUpdateContainer as $_ticketDraftID => $_ticketDraft) {
            SWIFT_TicketDraft::UpdateStaffName($_ticketDraftID, $_ticketDraft['staffname'], $_ticketDraft['editedstaffname']);
        }
        unset($_ticketDraftUpdateContainer);

        // Ticket Audit Logs
        $_ticketAuditLogUpdateContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketauditlogs WHERE ticketid = '" . (int) ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            $_ticketAuditLogUpdateContainer[$this->Database->Record['ticketauditlogid']] = $this->Database->Record;

            if (isset($_departmentCache[$this->Database->Record['departmentid']])) {
                $_ticketAuditLogUpdateContainer[$this->Database->Record['ticketauditlogid']]['departmenttitle'] = $_departmentCache[$this->Database->Record['departmentid']]['title'];
            }

            if ($this->Database->Record['creatortype'] == SWIFT_TicketAuditLog::CREATOR_STAFF && isset($_staffCache[$this->Database->Record['creatorid']])) {
                $_ticketAuditLogUpdateContainer[$this->Database->Record['ticketauditlogid']]['creatorfullname'] = $_staffCache[$this->Database->Record['creatorid']]['fullname'];
            }
        }

        foreach ($_ticketAuditLogUpdateContainer as $_ticketAuditLogID => $_ticketAuditLog) {
            SWIFT_TicketAuditLog::UpdateProperties($_ticketAuditLogID, $_ticketAuditLog['departmenttitle'], $_ticketAuditLog['creatorfullname']);
        }
        unset($_ticketAuditLogUpdateContainer);

        // Ticket Notes
        $_ticketNoteUpdateContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketnotes
            WHERE linktype = '" . SWIFT_TicketNote::LINKTYPE_TICKET . "' AND linktypeid = '" . (int) ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            $_ticketNoteUpdateContainer[$this->Database->Record['ticketnoteid']] = $this->Database->Record;

            if (isset($_staffCache[$this->Database->Record['staffid']])) {
                $_ticketNoteUpdateContainer[$this->Database->Record['ticketnoteid']]['staffname'] = $_staffCache[$this->Database->Record['staffid']]['fullname'];
            }

            if (isset($_staffCache[$this->Database->Record['editedstaffid']])) {
                $_ticketNoteUpdateContainer[$this->Database->Record['ticketnoteid']]['editedstaffname'] = $_staffCache[$this->Database->Record['editedstaffid']]['fullname'];
            }
        }

        foreach ($_ticketNoteUpdateContainer as $_ticketNoteID => $_ticketNote) {
            SWIFT_TicketNoteManager::UpdateProperties($_ticketNoteID, $_ticketNote['staffname'], $_ticketNote['editedstaffname']);
        }

        unset($_ticketNoteUpdateContainer);

        // Ticket Time Tracks
        $_ticketTimeTrackUpdateContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettimetracks WHERE ticketid = '" . (int) ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            $_ticketTimeTrackUpdateContainer[$this->Database->Record['tickettimetrackid']] = $this->Database->Record;

            if (isset($_staffCache[$this->Database->Record['creatorstaffid']])) {
                $_ticketTimeTrackUpdateContainer[$this->Database->Record['tickettimetrackid']]['creatorstaffname'] = $_staffCache[$this->Database->Record['creatorstaffid']]['fullname'];
            }

            if (isset($_staffCache[$this->Database->Record['editedstaffid']])) {
                $_ticketTimeTrackUpdateContainer[$this->Database->Record['tickettimetrackid']]['editedstaffname'] = $_staffCache[$this->Database->Record['editedstaffid']]['fullname'];
            }

            if (isset($_staffCache[$this->Database->Record['workerstaffid']])) {
                $_ticketTimeTrackUpdateContainer[$this->Database->Record['tickettimetrackid']]['workerstaffname'] = $_staffCache[$this->Database->Record['workerstaffid']]['fullname'];
            }
        }

        foreach ($_ticketTimeTrackUpdateContainer as $_ticketTimeTrackID => $_ticketTimeTrack) {
            SWIFT_TicketTimeTrack::UpdateProperties($_ticketTimeTrackID, $_ticketTimeTrack['creatorstaffname'], $_ticketTimeTrack['editedstaffname'], $_ticketTimeTrack['workerstaffname']);
        }
        unset($_ticketTimeTrackUpdateContainer);

        // Escalation Paths
        $_escalationPathUpdateContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationpaths WHERE ticketid = '" . (int) ($this->GetTicketID()) . "'");
        while ($this->Database->NextRecord()) {
            $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']] = $this->Database->Record;

            if (isset($_slaPlanCache[$this->Database->Record['slaplanid']])) {
                $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']]['slaplantitle'] = $_slaPlanCache[$this->Database->Record['slaplanid']]['title'];
            }

            if (isset($_escalationRuleCache[$this->Database->Record['escalationruleid']])) {
                $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']]['escalationruletitle'] = $_escalationRuleCache[$this->Database->Record['escalationruleid']]['title'];
            }

            if (isset($_staffCache[$this->Database->Record['ownerstaffid']])) {
                $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']]['ownerstaffname'] = $_staffCache[$this->Database->Record['ownerstaffid']]['fullname'];
            }

            if (isset($_departmentCache[$this->Database->Record['departmentid']])) {
                $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']]['departmenttitle'] = $_departmentCache[$this->Database->Record['departmentid']]['title'];
            }

            if (isset($_ticketStatusCache[$this->Database->Record['ticketstatusid']])) {
                $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']]['ticketstatustitle'] = $_ticketStatusCache[$this->Database->Record['ticketstatusid']]['title'];
            }

            if (isset($_ticketPriorityCache[$this->Database->Record['priorityid']])) {
                $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']]['prioritytitle'] = $_ticketPriorityCache[$this->Database->Record['priorityid']]['title'];
            }

            if (isset($_ticketTypeCache[$this->Database->Record['tickettypeid']])) {
                $_escalationPathUpdateContainer[$this->Database->Record['escalationpathid']]['tickettypetitle'] = $_ticketTypeCache[$this->Database->Record['tickettypeid']]['title'];
            }
        }

        foreach ($_escalationPathUpdateContainer as $_escalationPathID => $_escalationPath) {
            SWIFT_EscalationPath::UpdateProperties($_escalationPathID, $_escalationPath['slaplantitle'], $_escalationPath['escalationruletitle'], $_escalationPath['ownerstaffname'],
                $_escalationPath['departmenttitle'], $_escalationPath['ticketstatustitle'], $_escalationPath['prioritytitle'], $_escalationPath['tickettypetitle']);
        }
        unset($_escalationPathUpdateContainer);

        return true;
    }

    /**
     * Calculate the Ticket Properties like is firstcontactresolved etc.
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CalculateProperties() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketPostObject_Last = false;
        try {
            $_SWIFT_TicketPostObject_Last = new SWIFT_TicketPost(new SWIFT_DataID($this->GetProperty('lastpostid')));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        // Is First Contact Resolved?
        if ($this->GetProperty('totalreplies') == '1' && $_SWIFT_TicketPostObject_Last instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject_Last->GetIsClassLoaded() && $_SWIFT_TicketPostObject_Last->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF
            && $this->GetProperty('isresolved') == '1' && $this->GetProperty('isfirstcontactresolved') == '0') {
            $this->UpdatePool('isfirstcontactresolved', '1');
        }

        $_lastActivity = $this->GetProperty('lastactivity');
        $_lastPostActivity = $_lastActivity;
        if ($_SWIFT_TicketPostObject_Last instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject_Last->GetIsClassLoaded()) {
            $_lastPostActivity = $_SWIFT_TicketPostObject_Last->GetProperty('dateline');
        }

        $_finalLastActivity = $_lastActivity;
        if ($_lastPostActivity > $_finalLastActivity) {
            $_finalLastActivity = $_lastPostActivity;
        }

        if ($this->GetProperty('isresolved') == '1') {
            $this->UpdatePool('resolutiondateline', $_finalLastActivity);

            // How much time did it take to resolve this ticket?
            $this->UpdatePool('resolutionseconds', $_finalLastActivity-$this->GetProperty('dateline'));
        } else {
            $this->UpdatePool('resolutiondateline', '0');
            $this->UpdatePool('resolutionseconds', '0');
        }

        // Is watched?
        $_ticketWatcherCountContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketwatchers WHERE ticketid = '" . (int) ($this->GetTicketID()) . "'");
        if (isset($_ticketWatcherCountContainer['totalitems']) && $_ticketWatcherCountContainer['totalitems'] > 0) {
            $this->UpdatePool('iswatched', '1');
        } else {
            $this->UpdatePool('iswatched', '0');
        }

        // Calculate the average response time
        $this->UpdatePool('averageresponsetime', '0');
        $this->UpdatePool('averageresponsetimehits', '0');

        $_ticketPostContainer = array();
        $_oldPostDateline = $_lastPostDateline = 0;
        $this->Database->Query("SELECT ticketpostid, dateline, creator FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid = '" . (int) ($this->GetTicketID()) . "' ORDER BY ticketpostid ASC");
        while ($this->Database->NextRecord()) {
            $_ticketPostContainer[$this->Database->Record['ticketpostid']] = $this->Database->Record;
        }

        $_firstResponseTime = 0;

        foreach ($_ticketPostContainer as $_ticketPostID => $_ticketPost) {
            if (!empty($_lastPostDateline)) {
                $_responseTime = $_ticketPost['dateline'] - $_lastPostDateline;

                $_postFirstResponseTime = 0;

                if (empty($_firstResponseTime) && $_ticketPost['creator'] == SWIFT_TicketPost::CREATOR_STAFF) {
                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                     *
                     * SWIFT-3929 First Response Time returns negative value for merged tickets
                     *
                     * Comments: Calculating response time from ticket posts, irrespective of ticket creation time.
                     */
                    $_firstResponseTime = (int) $_ticketPost['dateline'] - reset($_ticketPostContainer)['dateline'];
                    $_postFirstResponseTime = $_firstResponseTime;
                }

                $_updateContainer = array();
                $_updateContainer['responsetime'] = $_responseTime;

                if (!empty($_postFirstResponseTime)) {
                    $_updateContainer['firstresponsetime'] = $_postFirstResponseTime;
                }

                $this->Database->AutoExecute(TABLE_PREFIX . 'ticketposts', $_updateContainer, 'UPDATE', "ticketpostid = '" . (int) ($_ticketPostID) . "'");
            }

            if ($_ticketPost['creator'] != SWIFT_TicketPost::CREATOR_STAFF) {
                $_oldPostDateline = $_ticketPost['dateline'];
            } else if (!empty($_oldPostDateline) && $_ticketPost['creator'] == SWIFT_TicketPost::CREATOR_STAFF) {
                $_responseTime = $_ticketPost['dateline']-$_oldPostDateline;
                $this->UpdateAverageResponseTime($_responseTime);

                $_oldPostDateline = 0;
            }

            $_lastPostDateline = $_ticketPost['dateline'];
        }

        if (!empty($_firstResponseTime)) {
            $this->UpdatePool('firstresponsetime', (int) ($_firstResponseTime));
        }
        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5054 Resolved and Autoclosed date show different timestamps in report.
         *
         * Comments: Fixed the calculaton for the correct resolutiondateline.
         */
        $_ticketContainer = $this->Database->QueryFetch("SELECT isautoclosed, resolutiondateline, autoclosetimeline FROM " . TABLE_PREFIX . "tickets WHERE ticketid = " . (int) ($this->GetTicketID()));
        if ($_ticketContainer['isautoclosed'] == '1') {
            $this->UpdatePool('resolutiondateline', $_ticketContainer['autoclosetimeline']);
        }

        return true;
    }

    /**
     * Process the workflow queue for each active ticket
     *
     * @author Varun Shoor <varun.shoor@kayako.com>
     * @author Nidhi Gupta <nidhi.gupta@kayako.com>
     *
     * @return bool
     *
     * @throws SWIFT_Ticket_Exception
     */
    public static function ProcessWorkflowQueue()
    {
        //$_SWIFT = SWIFT::GetInstance();

        if (!count(self::$_workflowQueue)) {
            return true;
        }

        $_ticketWorkflowIDContainer = $_ticketIDList = array();

        foreach (self::$_workflowQueue as $_ticketID => $_SWIFT_TicketObject) {
            $_ticketWorkflowIDContainer[$_ticketID] = SWIFT_TicketWorkflow::ExecuteAll($_SWIFT_TicketObject);

            $_ticketIDList[] = $_ticketID;
        }

        if (!count($_ticketWorkflowIDContainer)) {
            self::$_workflowQueue         = array();
            self::$_isWorkflowQueueActive = false;

            return false;
        }

        SWIFT_TicketLinkedTable::DeleteOnTicket($_ticketIDList, SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW);

        foreach ($_ticketWorkflowIDContainer as $_ticketID => $_ticketWorkflowIDList) {
            if (!isset(self::$_workflowQueue[$_ticketID])) {
                throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
            }

            // Add the workflows to the ticket
            if (_is_array($_ticketWorkflowIDList)) {
                SWIFT_TicketLinkedTable::CreateIfNotExists(self::$_workflowQueue[$_ticketID], array(SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW => $_ticketWorkflowIDList));
            }

            unset(self::$_workflowQueue[$_ticketID]);
        }

        self::$_workflowQueue         = array();
        self::$_isWorkflowQueueActive = false;

        return true;
    }

    /**
     * Processes the POST attachment field (ticketattachments) and adds the attachments to the ticket
     *
     * @author Varun Shoor
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject (OPTIONAL) The Ticket Post Object
     * @param string $_fieldName (OPTIONAL) The Custom Field Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ProcessPostAttachments(SWIFT_TicketPost $_SWIFT_TicketPostObject = null, $_fieldName = '') {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalFieldName = 'ticketattachments';
        if (!empty($_fieldName))
        {
            $_finalFieldName = $_fieldName;
        }

        $_listFieldName = $_finalFieldName . 'list';

        // Link with first post if none specified
        if ($_SWIFT_TicketPostObject == null) {
            $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($this->GetProperty('firstpostid')));
        }

        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_attachmentCount = 0;

        if (isset($_POST[$_listFieldName]) && _is_array($_POST[$_listFieldName]))
        {
            foreach ($_POST[$_listFieldName] as $_attachmentID)
            {
                $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);

                SWIFT_Attachment::CloneOnTicket($this, $_SWIFT_TicketPostObject, $_SWIFT_AttachmentObject);

                $this->AddToNotificationAttachments($_SWIFT_AttachmentObject->GetProperty('filename'), $_SWIFT_AttachmentObject->GetProperty('filetype'),
                    $_SWIFT_AttachmentObject->Get(), $_SWIFT_AttachmentObject->GetProperty('contentid'));

                $_attachmentCount++;

            }

            if ($_attachmentCount > 0) {

                $this->UpdatePool('hasattachments', '1');

                /**
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-2576: 'hasattachments' field is not getting saved if ticket is having attachments
                 *
                 * Comments: Update hasattachments for ticket posts also
                 */
                $_SWIFT_TicketPostObject->UpdatePool('hasattachments', '1');
            }

            unset($_POST[$_listFieldName]);
        }

        if (!isset($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]['name'])) {
            return false;
        }

        // Create the attachments
        foreach ($_FILES[$_finalFieldName]['name'] as $_fileIndex => $_fileName) {
            if (empty($_fileName) || empty($_FILES[$_finalFieldName]['type'][$_fileIndex]) || empty($_FILES[$_finalFieldName]['size'][$_fileIndex]) ||
                !is_uploaded_file($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex]))
            {
                continue;
            }

            $_SWIFT_AttachmentStoreObject = new SWIFT_AttachmentStoreFile($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex],
                $_FILES[$_finalFieldName]['type'][$_fileIndex], $_fileName);

            $_SWIFT_AttachmentObject = SWIFT_Attachment::CreateOnTicket($this, $_SWIFT_TicketPostObject, $_SWIFT_AttachmentStoreObject);

            $this->AddToNotificationAttachments($_fileName, $_FILES[$_finalFieldName]['type'][$_fileIndex], file_get_contents($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex]), $_SWIFT_AttachmentObject->GetProperty('contentid'));

            $_attachmentCount++;
        }

        if ($_attachmentCount > 0) {

            $this->UpdatePool('hasattachments', '1');

            /**
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-2576: 'hasattachments' field is not getting saved if ticket is having attachments
             *
             * Comments: Update hasattachments for ticket posts also
             */
            $_SWIFT_TicketPostObject->UpdatePool('hasattachments', '1');
        }

        return true;
    }
}
