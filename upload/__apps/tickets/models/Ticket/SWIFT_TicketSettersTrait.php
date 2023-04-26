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
use SWIFT_ParserBan;
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

trait SWIFT_TicketSettersTrait
{
    /**
     * Set a fixed SLA Plan for this Ticket
     *
     * @author Varun Shoor
     * @param SWIFT_SLA $_SWIFT_SLAObject The SWIFT_SLA Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSLA(SWIFT_SLA $_SWIFT_SLAObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_SLAObject instanceof SWIFT_SLA || !$_SWIFT_SLAObject->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // If the current sla plan and the one provided matches then bail out
        if ($this->GetProperty('ticketslaplanid') == $_SWIFT_SLAObject->GetSLAPlanID()) {
            return true;
        }

        $_slaPlanCache = $this->Cache->Get('slaplancache');
        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_SLAPLAN, $this->GetProperty('ticketslaplanid'), $_SWIFT_SLAObject->GetSLAPlanID());

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

        $_newSLATitle = $_SWIFT_SLAObject->GetProperty('title');

        $this->Notification->Update($this->Language->Get('notification_sla'), $_oldSLATitle, $_newSLATitle);

        $this->UpdatePool('ticketslaplanid', (int) ($_SWIFT_SLAObject->GetSLAPlanID()));
        $this->UpdatePool('slaplanid', (int) ($_SWIFT_SLAObject->GetSLAPlanID()));
        $this->UpdatePool('isescalatedvolatile', '0');

        // We set these to 0 and expect the sla processing engine to set the correct values
//        $this->UpdatePool('duetime', '0');
//        $this->UpdatePool('resolutionduedateline', '0');

        $this->_noSLACalculation = false;
        $this->ProcessSLAOverdue(true);
//        $this->QueueSLAOverdue(true);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Template Group
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetTemplateGroup($_templateGroupID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('tgroupid', $_templateGroupID);

        return true;
    }

    /**
     * Set a flag on this ticket
     *
     * @author Varun Shoor
     * @param mixed $_flagType The Flag Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetFlag($_flagType) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Flag:TicketFlag', [], true, false, 'tickets');
        $_flagContainer = $this->TicketFlag->GetFlagContainer();

        if ($_flagType == '0') {
            // Notification Update
            $_oldFlagTitle = '';
            if (isset($_flagContainer[$this->GetProperty('flagtype')])) {
                $_oldFlagTitle = $_flagContainer[$this->GetProperty('flagtype')][0];
            }
            $_newFlagTitle = $this->Language->Get('notificationcleared');

            $this->Notification->Update($this->Language->Get('notification_flag'), $_oldFlagTitle, $_newFlagTitle);

            $this->UpdatePool('flagtype', '0');

            $this->QueueSLAOverdue();

            return true;
        }

        if (!SWIFT_TicketFlag::IsValidFlagType($_flagType)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // If this ticket already has a flag set which matches the one specified.. then move on
        if ($this->GetProperty('flagtype') == $_flagType) {
            return true;
        }

        // Create Audit Log
        $_flagTitle = $_flagContainer[$_flagType][0];

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATEFLAG,
            sprintf($_SWIFT->Language->Get('al_flag'), $_flagTitle),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('flagtype'), '', $_flagType, '',
            ['al_flag', $_flagTitle]);

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_FLAGTYPE, $this->GetProperty('flagtype'), $_flagType);

        // Notification Update
        $_oldFlagTitle = '';
        if (isset($_flagContainer[$this->GetProperty('flagtype')])) {
            $_oldFlagTitle = $_flagContainer[$this->GetProperty('flagtype')][0];
        }
        $_newFlagTitle = $_flagTitle;

        $this->Notification->Update($this->Language->Get('notification_flag'), $_oldFlagTitle, $_newFlagTitle);

        $this->UpdatePool('flagtype', (int) ($_flagType));

        $this->QueueSLAOverdue(false,false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Change the department for the ticket
     *
     * @author Varun Shoor
     * @param int $_departmentID The department id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or if Invalid Data is Provided
     */
    public function SetDepartment($_departmentID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_departmentID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        if (!isset($_departmentCache[$_departmentID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // If the ticket is already in this department then bail out
        if ($this->GetProperty('departmentid') == $_departmentID) {
            return true;
        }

        // Add old and new department to recount queue
//        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
//        SWIFT_TicketManager::Recount($_departmentID);
        SWIFT_TicketManager::Recount(false);

        // Create Audit Log
        $_oldDepartmentTitle = $_newDepartmentTitle = '';
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $_oldDepartmentTitle = $_departmentCache[$this->GetProperty('departmentid')]['title'];
        }

        if ($this->GetProperty('departmentid') == '0') {
            $_oldDepartmentTitle = $this->Language->Get('trash');
        }

        $_newDepartmentTitle = $_departmentCache[$_departmentID]['title'];

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATEDEPARTMENT,
            sprintf($_SWIFT->Language->Get('al_department'), $_oldDepartmentTitle, $_newDepartmentTitle),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('departmentid'), '', $_departmentID, '',
            ['al_department', $_oldDepartmentTitle, $_newDepartmentTitle]);

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_DEPARTMENT, $this->GetProperty('departmentid'), $_departmentID);

        // Notification Update
        $_oldNotificationDepartmentTitle = $_newNotificationDepartmentTitle = '';
        if ($_oldDepartmentTitle == '') {
            $_oldNotificationDepartmentTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationDepartmentTitle = $_oldDepartmentTitle;
        }

        $_newNotificationDepartmentTitle = $_newDepartmentTitle;

        $_oldUserNotificationDepartmentTitle = $_oldNotificationDepartmentTitle;
        $_newUserNotificationDepartmentTitle = $_newNotificationDepartmentTitle;

        if (isset($_departmentCache[$this->GetProperty('departmentid')]) && $_departmentCache[$this->GetProperty('departmentid')]['departmenttype'] == SWIFT_PRIVATE) {
            $_oldUserNotificationDepartmentTitle = $this->Language->Get('private');
        }

        if (isset($_departmentCache[$_departmentID]) && $_departmentCache[$_departmentID]['departmenttype'] == SWIFT_PRIVATE) {
            $_newUserNotificationDepartmentTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_department'), $_oldNotificationDepartmentTitle, $_newNotificationDepartmentTitle, $_oldUserNotificationDepartmentTitle, $_newUserNotificationDepartmentTitle);

        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('departmenttitle', $_newDepartmentTitle);

        $this->QueueSLAOverdue(false,false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Ticket Type
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetType($_ticketTypeID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketTypeID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // Verify value of ticket type
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        if (!isset($_ticketTypeCache[$_ticketTypeID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketTypeContainer = $_ticketTypeCache[$_ticketTypeID];

        // If current ticket type is same as the new type then bail out
        if ($this->GetProperty('tickettypeid') == $_ticketTypeID) {
            return true;
        }

        // Verify the department id link of ticket type

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1298 Issue with ticket types and ticket statuses, if they are linked with Parent department
         *
         * Comments: None
         */
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_parentDepartmentID = false;
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $_parentDepartmentID = $_departmentCache[$this->GetProperty('departmentid')]['parentdepartmentid'];
        }

        if ($_ticketTypeContainer['departmentid'] != '0' && $_ticketTypeContainer['departmentid'] != $this->GetProperty('departmentid') && $_ticketTypeContainer['departmentid'] != $_parentDepartmentID) {
            throw new SWIFT_Ticket_Exception('Ticket Type (' . $_ticketTypeID . ') & Department ID (' . (int) ($this->GetProperty('departmentid')) . ') Mismatch');
        }

        // Create Audit Log
        $_oldTypeTitle = $_newTypeTitle = '';
        if (isset($_ticketTypeCache[$this->GetProperty('tickettypeid')])) {
            $_oldTypeTitle = $_ticketTypeCache[$this->GetProperty('tickettypeid')]['title'];
        }

        $_newTypeTitle = $_ticketTypeCache[$_ticketTypeID]['title'];

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATETYPE,
            sprintf($_SWIFT->Language->Get('al_type'), $_oldTypeTitle, $_newTypeTitle),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('tickettypeid'), '', $_ticketTypeID, '',
            ['al_type', $_oldTypeTitle, $_newTypeTitle]);

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_TICKETTYPE, $this->GetProperty('tickettypeid'), $_ticketTypeID);

        // Notification Update
        $_oldNotificationTypeTitle = $_newNotificationTypeTitle = '';
        if ($_oldTypeTitle == '') {
            $_oldNotificationTypeTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationTypeTitle = $_oldTypeTitle;
        }

        $_newNotificationTypeTitle = $_newTypeTitle;

        $_oldUserNotificationTypeTitle = $_oldNotificationTypeTitle;
        $_newUserNotificationTypeTitle = $_newNotificationTypeTitle;

        /**
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-2945:  Notice generated when Ticket Type is set from '-NA-' to an available Ticket Type
         *
         * Comments: None
         */
        if (isset($_ticketTypeCache[$this->GetProperty('tickettypeid')]) && $_ticketTypeCache[$this->GetProperty('tickettypeid')]['type'] == SWIFT_PRIVATE) {
            $_oldUserNotificationTypeTitle = $this->Language->Get('private');
        }

        if ($_ticketTypeCache[$_ticketTypeID]['type'] == SWIFT_PRIVATE) {
            $_newUserNotificationTypeTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_type'), $_oldNotificationTypeTitle, $_newNotificationTypeTitle, $_oldUserNotificationTypeTitle, $_newUserNotificationTypeTitle);


        $this->UpdatePool('tickettypeid', $_ticketTypeID);
        $this->UpdatePool('tickettypetitle', $_newTypeTitle);

//        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        $this->QueueSLAOverdue(false,false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Has Status Changed Property
     *
     * @author Varun Shoor
     * @param bool $_hasStatusChanged
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetHasStatusChanged($_hasStatusChanged)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_hasStatusChanged = $_hasStatusChanged;

        return true;
    }

    /**
     * Set the Ticket Status
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @param bool $_isViaAutoClose (OPTIONAL) If the status change is via auto close
     * @param bool $_suppressSurveyEmail (OPTIONAL) Whether to suppress survey email
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetStatus($_ticketStatusID, $_isViaAutoClose = false, $_suppressSurveyEmail = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);

        } else if (empty($_ticketStatusID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketStatusCache = $this->Cache->Get('statuscache');
        if (!isset($_ticketStatusCache[$_ticketStatusID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketStatusContainer = $_ticketStatusCache[$_ticketStatusID];

        $this->SetHasStatusChanged(true);

        // If the current ticket status id matches then bail out
        if ($this->GetProperty('ticketstatusid') == $_ticketStatusID) {
            return true;
        }

        // Verify the department id link of ticket status

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1298 Issue with ticket types and ticket statuses, if they are linked with Parent department
         *
         * Comments: None
         */
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_parentDepartmentID = false;
        if (isset($_departmentCache[$this->GetProperty('departmentid')])) {
            $_parentDepartmentID = $_departmentCache[$this->GetProperty('departmentid')]['parentdepartmentid'];
        }

        if ($_ticketStatusContainer['departmentid'] != '0' && $_ticketStatusContainer['departmentid'] != $this->GetProperty('departmentid') && $_ticketStatusContainer['departmentid'] != $_parentDepartmentID) {
            throw new SWIFT_Ticket_Exception('Ticket Status (' . $_ticketStatusID . ') & Department ID (' . (int) ($this->GetProperty('departmentid')) . ') Mismatch');
        }

        // Create Audit Log
        $_oldStatusTitle = $_newStatusTitle = '';
        if (isset($_ticketStatusCache[$this->GetProperty('ticketstatusid')])) {
            $_oldStatusTitle = $_ticketStatusCache[$this->GetProperty('ticketstatusid')]['title'];
        }

        $_newStatusTitle = $_ticketStatusCache[$_ticketStatusID]['title'];

        $_statusLanguageKey = 'al_status';
        if ($_isViaAutoClose) {
            $_statusLanguageKey = 'al_statusautoclose';
        }

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATESTATUS,
            sprintf($_SWIFT->Language->Get($_statusLanguageKey), $_oldStatusTitle, $_newStatusTitle),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('ticketstatusid'), '', $_ticketStatusID, '',
            [$_statusLanguageKey, $_oldStatusTitle, $_newStatusTitle]);

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_TICKETSTATUS, $this->GetProperty('ticketstatusid'), $_ticketStatusID);

        // Notification Update
        $_oldNotificationStatusTitle = $_newNotificationStatusTitle = '';
        if ($_oldStatusTitle == '') {
            $_oldNotificationStatusTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationStatusTitle = $_oldStatusTitle;
        }

        $_newNotificationStatusTitle = $_newStatusTitle;

        $_oldUserNotificationStatusTitle = $_oldNotificationStatusTitle;
        $_newUserNotificationStatusTitle = $_newNotificationStatusTitle;

        /**
         * BUG FIX - Ravi sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3202 Undefined offset: 0 (D:/xampp/htdocs/kayako/__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:2420)
         */
        if (isset($_ticketStatusCache[$this->Get('ticketstatusid')]) && isset($_ticketStatusCache[$this->Get('ticketstatusid')]['statustype']) && $_ticketStatusCache[$this->Get('ticketstatusid')]['statustype'] == SWIFT_PRIVATE) {
            $_oldUserNotificationStatusTitle = $this->Language->Get('private');
        }

        if ($_ticketStatusCache[$_ticketStatusID]['statustype'] == SWIFT_PRIVATE) {
            $_newUserNotificationStatusTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_status'), $_oldNotificationStatusTitle, $_newNotificationStatusTitle, $_oldUserNotificationStatusTitle, $_newUserNotificationStatusTitle);

        $this->UpdatePool('ticketstatusid', $_ticketStatusID);
        $this->UpdatePool('ticketstatustitle', $_newStatusTitle);

//        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        // Was reopened?
        if ($_ticketStatusContainer['markasresolved'] == '0' && $this->GetProperty('isresolved') == '1') {
            $this->UpdatePool('wasreopened', '1');
            $this->UpdatePool('reopendateline', DATENOW);
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4424 SLA plan is not applied if clear Reply Due time option is enabled for a status.
         * SWIFT-5128 SLA timers getting reset to previous values while replying the tickets
         */
        if ($_ticketStatusContainer['resetduetime'] == '1') {
            $this->ProcessSLAOverdue();
            $this->ClearOverdue();
        }

        if ($_ticketStatusContainer['markasresolved'] == '1')
        {
            $this->UpdatePool('isresolved', '1');
            $this->UpdatePool('resolutiondateline', DATENOW);
            $this->UpdatePool('repliestoresolution', $this->GetProperty('totalreplies'));

            // How much time did it take to resolve this ticket?
            $this->UpdatePool('resolutionseconds', DATENOW-$this->GetProperty('dateline'));
        } else {
            $this->UpdatePool('isresolved', '0');

            // Are we changing to an unresolved status and ticket is marked as auto closed or pending closure?
            if ($this->GetProperty('autoclosestatus') != self::AUTOCLOSESTATUS_NONE) {
                $this->UpdatePool('autoclosestatus', self::AUTOCLOSESTATUS_NONE);
                $this->UpdatePool('isautoclosed', '0');
                $this->UpdatePool('autoclosetimeline', '0');
            }
        }

        // If the status is set to resolved then we reset the resolution due time
        $_processResolutionDue = false;
        if ($_ticketStatusContainer['markasresolved'] == '1') {
            $this->UpdatePool('resolutionduedateline', '0');
            $this->UpdatePool('duetime', '0');
            $this->UpdatePool('slaplanid', '0');

            $this->_noSLACalculation = true;

        // Otherwise if its not, and resolutionduedateline is 0 then we force it to recalculate the resolution due time
        } else if ($_ticketStatusContainer['markasresolved'] == '0' && $this->GetProperty('resolutionduedateline') == '0') {
            $_processResolutionDue = true;
        }

        $this->QueueSLAOverdue($_processResolutionDue, false);

        // Is First Contact Resolved?
        if ($this->GetProperty('totalreplies') == '0' && $_ticketStatusContainer['markasresolved'] == '1' && $this->GetProperty('isfirstcontactresolved') == '0') {
            $this->UpdatePool('isfirstcontactresolved', '1');
        }

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-3020 Survey Email arrives first followed by ticket reply.
         *
         * Comments: Code placement changes.
         */
        // Do we have to trigger a survey?
        if ($_ticketStatusContainer['triggersurvey'] == '1' && !$_suppressSurveyEmail)
        {
            $this->Load->Library('Ticket:TicketEmailDispatch', array($this), true, false, 'tickets');
            $this->TicketEmailDispatch->DispatchSurvey();
        }

        return true;
    }

    /**
     * Set the Ticket Priority
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetPriority($_ticketPriorityID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketPriorityID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        if (!isset($_ticketPriorityCache[$_ticketPriorityID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketPriorityContainer = $_ticketPriorityCache[$_ticketPriorityID];

        // If the current ticket priority id matches then bail out
        if ($this->GetProperty('priorityid') == $_ticketPriorityID) {
            return true;
        }

        // Create Audit Log
        $_oldPriorityTitle = $_newPriorityTitle = '';
        if (isset($_ticketPriorityCache[$this->GetProperty('priorityid')])) {
            $_oldPriorityTitle = $_ticketPriorityCache[$this->GetProperty('priorityid')]['title'];
        }

        $_newPriorityTitle = $_ticketPriorityCache[$_ticketPriorityID]['title'];

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATEPRIORITY,
            sprintf($_SWIFT->Language->Get('al_priority'), $_oldPriorityTitle, $_newPriorityTitle),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('priorityid'), '', $_ticketPriorityID, '',
            ['al_priority', $_oldPriorityTitle, $_newPriorityTitle]);

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_TICKETPRIORITY, $this->GetProperty('priorityid'), $_ticketPriorityID);

        // Notification Update
        $_oldNotificationPriorityTitle = $_newNotificationPriorityTitle = '';
        if ($_oldPriorityTitle == '') {
            $_oldNotificationPriorityTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationPriorityTitle = $_oldPriorityTitle;
        }

        $_newNotificationPriorityTitle = $_newPriorityTitle;

        $_oldUserNotificationPriorityTitle = $_oldNotificationPriorityTitle;
        $_newUserNotificationPriorityTitle = $_newNotificationPriorityTitle;

        /*
         * BUG FIX - Gurpreet Singh
         *
         * SWIFT-3416 Undefined offset error when using mass action in ticket view
         */
        if (isset($_ticketPriorityCache[$this->GetProperty('priorityid')]['type']) && $_ticketPriorityCache[$this->GetProperty('priorityid')]['type'] == SWIFT_PRIVATE) {
            $_oldUserNotificationPriorityTitle = $this->Language->Get('private');
        }

        if ($_ticketPriorityCache[$_ticketPriorityID]['type'] == SWIFT_PRIVATE) {
            $_newUserNotificationPriorityTitle = $this->Language->Get('private');
        }

        $this->Notification->Update($this->Language->Get('notification_priority'), $_oldNotificationPriorityTitle, $_newNotificationPriorityTitle, $_oldUserNotificationPriorityTitle, $_newUserNotificationPriorityTitle);

        $this->UpdatePool('priorityid', $_ticketPriorityID);
        $this->UpdatePool('prioritytitle', $_newPriorityTitle);

//        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        $this->QueueSLAOverdue(false, false);

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Ticket Owner
     *
     * @author Varun Shoor
     * @param int $_staffID The Owner Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetOwner($_staffID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        // If the current owner matches the one provided then bail out
        if ($this->GetProperty('ownerstaffid') == $_staffID) {
            return true;
        }

        $_staffCache = $this->Cache->Get('staffcache');
        if ($_staffID != 0 && !isset($_staffCache[$_staffID])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        // Create Audit Log
        $_oldOwnerTitle = $_newOwnerTitle = '';
        if (isset($_staffCache[$this->GetProperty('ownerstaffid')])) {
            $_oldOwnerTitle = $_staffCache[$this->GetProperty('ownerstaffid')]['fullname'];
        }

        if ($this->GetProperty('ownerstaffid') == '0') {
            $_oldOwnerTitle = $this->Language->Get('unassigned');
        }

        if ($_staffID == '0') {
            $_newOwnerTitle = $this->Language->Get('unassigned');
        } else {
            $_newOwnerTitle = $_staffCache[$_staffID]['fullname'];
        }

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATEOWNER,
            sprintf($_SWIFT->Language->Get('al_owner'), $_oldOwnerTitle, $_newOwnerTitle),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('ownerstaffid'), '', $_staffID, '',
            ['al_owner', $_oldOwnerTitle, $_newOwnerTitle]);

        // Notification Event
        $this->NotificationManager->SetEvent('ticketassigned');

        // Notification Rule
        $this->NotificationManager->Changed(SWIFT_NotificationRule::CRITERIA_OWNER, $this->GetProperty('ownerstaffid'), $_staffID);

        // Notification Update
        $_oldNotificationOwnerTitle = $_newNotificationOwnerTitle = '';
        if ($_oldOwnerTitle == '') {
            $_oldNotificationOwnerTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationOwnerTitle = $_oldOwnerTitle;
        }

        $_newNotificationOwnerTitle = $_newOwnerTitle;

        $this->Notification->Update($this->Language->Get('notification_staff'), $_oldNotificationOwnerTitle, $_newNotificationOwnerTitle);


        $this->UpdatePool('ownerstaffid', $_staffID);

        $_staffName = '';
        if (isset($_staffCache[$_staffID])) {
            $_staffName = $_staffCache[$_staffID]['fullname'];
        }

        $this->UpdatePool('ownerstaffname', $_staffName);

        // How many owners did it take to resolve this ticket?
        $this->UpdatePool('resolutionlevel', (int) ($this->GetProperty('resolutionlevel'))+1);

//        SWIFT_TicketManager::Recount($this->GetProperty('departmentid'));
        SWIFT_TicketManager::Recount(false);

        $this->QueueSLAOverdue(false,false);

        // Load the last notification message for ticket assigned alert
        $this->LoadLastPostNotificationMessage();

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Set the Due Time
     *
     * @author Varun Shoor
     * @param string $_dueDateline The Due Dateline
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function SetDue($_dueDateline) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_dueDateline)) {
            return false;
        }

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            sprintf($_SWIFT->Language->Get('al_due'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline)),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('duetime'), '', $_dueDateline, '',
            ['al_due', SWIFT_TicketAuditLog::DATETIME_PARAM.$_dueDateline]);

        // Notification Update
        $_oldNotificationDueTitle = $_newNotificationDueTitle = '';
        if ($this->GetProperty('duetime') == '0') {
            $_oldNotificationDueTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->GetProperty('duetime'));
        }

        $_newNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline);

        $this->Notification->Update($this->Language->Get('notification_due'), $_oldNotificationDueTitle, $_newNotificationDueTitle);

        // Prevent calculation of SLA due time on this ticket
        $this->_noSLACalculation = true;

        $this->UpdatePool('duetime', (int) ($_dueDateline));

        return true;
    }

    /**
     * Set the Resolution Due Time
     *
     * @author Varun Shoor
     * @param string $_dueDateline The Due Dateline
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function SetResolutionDue($_dueDateline) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_dueDateline)) {
            return false;
        }

        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            sprintf($_SWIFT->Language->Get('al_resolutiondue'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline)),
            SWIFT_TicketAuditLog::VALUE_NONE, $this->GetProperty('duetime'), '', $_dueDateline, '',
            ['al_resolutiondue', SWIFT_TicketAuditLog::DATETIME_PARAM.$_dueDateline]);

        // Notification Update
        $_oldNotificationDueTitle = $_newNotificationDueTitle = '';
        if ($this->GetProperty('resolutionduedateline') == '0') {
            $_oldNotificationDueTitle = $this->Language->Get('na');
        } else {
            $_oldNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->GetProperty('resolutionduedateline'));
        }

        $_newNotificationDueTitle = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_dueDateline);

        $this->Notification->Update($this->Language->Get('notification_resolutiondue'), $_oldNotificationDueTitle, $_newNotificationDueTitle);

        // Prevent calculation of SLA due time on this ticket
        $this->_noSLACalculation = true;

        $this->UpdatePool('resolutionduedateline', (int) ($_dueDateline));

        return true;
    }

    /**
     * Set the Message ID for this Ticket
     *
     * @author Varun Shoor
     * @param string $_messageID The Message ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetMessageID($_messageID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_messageID)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('messageid', $_messageID);

        return true;
    }

    /**
     * Set the Watcher Notification Message
     *
     * @author Varun Shoor
     * @param string $_watcherName The Watcher Name
     * @param string $_watcherMessage The Watcher Message
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetWatcherProperties($_watcherName, $_watcherMessage)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_watcherCustomName = $_watcherName;
        $this->_watchNotificationMessage[] = $_watcherMessage;

        return true;
    }

    /**
    * Set alert rules for ticket
    *
    * @author Ruchi Kothari
    * @param bool $_noAlerts Alert rules
    * @return bool "true" on Success, "false" otherwise
    * @throws SWIFT_Exception If the Class is not Loaded
    */
    public function SetNoAlerts($_noAlerts)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_noAlerts = $_noAlerts;
    }

    /**
     * Set Old Ticket properties. This array is used to Keep previous ticket properties after execution.
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function SetOldTicketProperties()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETSTATUS] = $this->GetProperty('ticketstatusid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETPRIORITY] = $this->GetProperty('priorityid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETDEPARTMENT] = $this->GetProperty('departmentid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETOWNER] = (int) ($this->GetProperty('ownerstaffid'));
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETEMAILQUEUE] = $this->GetProperty('emailqueueid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETFLAGTYPE] = $this->GetProperty('flagtype');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETCREATOR] = $this->GetProperty('creator');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETUSERGROUP] = $this->GetUserGroupID();

        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETFULLNAME] = $this->GetProperty('fullname');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETEMAIL] = $this->GetProperty('email');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETLASTREPLIER] = $this->GetProperty('lastreplier');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETSUBJECT] = $this->GetProperty('subject');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETCHARSET] = $this->GetProperty('charset');

        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETTEMPLATEGROUP] = $this->GetProperty('tgroupid');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETISRESOLVED] = $this->GetProperty('isresolved');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETTYPE] = (int) ($this->GetProperty('tickettypeid'));
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETWASREOPENED] = $this->GetProperty('wasreopened');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETTOTALREPLIES] = $this->GetProperty('totalreplies');
        $this->_oldTicketProperties[SWIFT_SLA::SLA_TICKETBAYESCATEGORY] = $this->GetProperty('bayescategoryid');

        return true;
    }

    /**
     * Update the Ticket Record
     *
     * @author Varun Shoor
     * @param string $_subject The Ticket Subject
     * @param string $_fullName The Ticket Fullname
     * @param string $_email The Ticket Email
     * @param bool $_update_replyto Update replyto field
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_subject, $_fullName, $_email, $_update_replyto = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_subject = $this->Emoji->encode($_subject);

        // Create Audit Log
        SWIFT_TicketAuditLog::AddToLog($this, null, SWIFT_TicketAuditLog::ACTION_UPDATETICKET,
            sprintf($_SWIFT->Language->Get('al_updateproperties'), StripName($this->GetProperty('subject'), 60), StripName($_subject, 60), StripName($this->GetProperty('fullname'), 30),
                StripName($_fullName, 30), StripName($this->GetProperty('email'), 40), StripName($_email, 40)),
	        SWIFT_TicketAuditLog::VALUE_NONE, 0, '', 0, '',
            ['al_updateproperties', StripName($this->GetProperty('subject'), 60), StripName($_subject, 60), StripName($this->GetProperty('fullname'), 30),
	            StripName($_fullName, 30), StripName($this->GetProperty('email'), 40), StripName($_email, 40)]);

        $this->UpdatePool('subject', $_subject);
        $this->UpdatePool('fullname', $_fullName);

        $_ticketEmailAddress = $this->GetProperty('email');
        if ($this->GetProperty('replyto') != '')
        {
            $_ticketEmailAddress = $this->GetProperty('replyto');
        }

        $this->UpdatePool('oldeditemailaddress', $_ticketEmailAddress);
        $this->UpdatePool('email', $_email);

        if ($_update_replyto) {
            $this->UpdatePool('replyto', $_email);
        }

        // Load and Process Workflow Rules
        self::AddToWorkflowQueue($this);

        return true;
    }

    /**
     * Update the Average Response Time
     *
     * @author Varun Shoor
     *
     * @param int $_responseTime
     *
     * @throws SWIFT_Exception
     *
     * @return bool
     */
    public function UpdateAverageResponseTime($_responseTime)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_oldHitCount            = (int) ($this->GetProperty('averageresponsetimehits'));
        $_oldAverageResponseTime = (int) ($this->GetProperty('averageresponsetime'));

        $_newHitCount = $_oldHitCount + 1;

        $_newAverageResponseTime = (($_oldAverageResponseTime * $_oldHitCount) + $_responseTime) / $_newHitCount;

        $this->UpdatePool('averageresponsetime', (int) ($_newAverageResponseTime));
        $this->UpdatePool('averageresponsetimehits',  ($_newHitCount));
        return true;
    }

    /**
     * @author Nidhi Gupta <nidhi.gupta@kayako.com>
     *
     * @param int $_slaResponseTime
     *
     * @param int $_ticketID
     *
     * @return SWIFT_Ticket
     *
     * @throws SWIFT_Exception
     */
    public function UpdateAverageSLAResponseTime($_slaResponseTime, $_ticketID)
    {
        if (!is_numeric($_slaResponseTime)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $_averageSLAResponseTime = 0;

        if ((int) ($this->Get('averageresponsetimehits')) > 0 && (int) ($this->Get('averageslaresponsetime')) == 0) {
            // slaresponsetime / averageresponsetimehits (as hit counts are already updated with UpdateAverageResponseTime method.
            $_averageSLAResponseTime = ( ($_slaResponseTime)) / (int) ($this->Get('averageresponsetimehits'));
        } else {
            $_avgSLAResponseTime = $this->Database->QueryFetch('SELECT AVG(slaresponsetime) AS avgslaresponsetime FROM ' . TABLE_PREFIX . 'ticketposts WHERE  ticketid = ' . $_ticketID . ' AND slaresponsetime > 0 GROUP BY ticketid');
            if (_is_array($_avgSLAResponseTime) && array_key_exists('avgslaresponsetime', $_avgSLAResponseTime)) {
                $_averageSLAResponseTime = (int) ($_avgSLAResponseTime['avgslaresponsetime']);
            }
        }

        $this->UpdatePool('averageslaresponsetime', $_averageSLAResponseTime);

        return $this;
    }

    /**
     * Update the Email Queue
     *
     * @author Varun Shoor
     * @param int $_emailQueueIDIncoming The Email Queue ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateQueue($_emailQueueIDIncoming) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('emailqueueid') == $_emailQueueIDIncoming) {
            return true;
        }

        $_emailQueueCache = $this->Cache->Get('queuecache');
        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        $_emailQueueID = 0;
        if (isset($_emailQueueCache['list'][$_emailQueueIDIncoming]))
        {
            $_emailQueueID = $_emailQueueIDIncoming;
        }

        $_emailQueueContainer = false;
        if (isset($_emailQueueCache['list'][$_emailQueueID]))
        {
            $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID];
        }

        $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();

        if (_is_array($_emailQueueContainer) && isset($_templateGroupCache[$_emailQueueContainer['tgroupid']]))
        {
            $_templateGroupID = $_emailQueueContainer['tgroupid'];
        }

        $this->UpdatePool('emailqueueid', $_emailQueueID);
        $this->UpdatePool('tgroupid', $_templateGroupID);

        return true;
    }

    /**
     * Update the global property on all tickets, used to update stuff like departmentname etc.
     *
     * @author Varun Shoor
     * @param string $_updateFieldName
     * @param string $_updateFieldValue
     * @param string $_whereFieldName
     * @param mixed $_whereFieldValue
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateGlobalProperty($_updateFieldName, $_updateFieldValue, $_whereFieldName, $_whereFieldValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_updateFieldName = $_SWIFT->Database->Escape($_updateFieldName);
        $_whereFieldName = $_SWIFT->Database->Escape($_whereFieldName);
        $_whereFieldValue = (int) ($_whereFieldValue); // Expected to be always int

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'tickets', array($_updateFieldName => $_updateFieldValue), 'UPDATE', $_whereFieldName . " = '" . $_SWIFT->Database->Escape($_whereFieldValue) . "'");

        return true;
    }

    /**
     * Update the time tracking information
     *
     * @author Varun Shoor
     * @param int $_timeSpent The Time Spent
     * @param int $_timeBilled The Time Billed
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateTimeTrack($_timeSpent, $_timeBilled)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('timeworked',  ($_timeSpent));
        $this->UpdatePool('timebilled',  ($_timeBilled));

        return true;
    }

    /**
     * Update the trash department
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function UpdateTrashDepartment() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1781 Help desk is not removing the SLA while moving the tickets in Trash folder
         *
         */
        $this->ClearOverdue();
        $this->ClearResolutiondue();
        $this->UpdatePool('slaplanid', '0');
        $this->UpdatePool('trasholddepartmentid', $this->GetProperty('departmentid'));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the User record
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateUser($_userID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('userid', $_userID);
        $this->ProcessUpdatePool();

        return true;
    }
}
