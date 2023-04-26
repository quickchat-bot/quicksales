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

namespace Tickets\Library\EmailParser;

use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationEmail;
use Base\Models\User\SWIFT_UserOrganizationLink;
use Parser\Models\Loop\SWIFT_LoopBlock;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreString;
use SWIFT_DataID;
use SWIFT_DataStore;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use SWIFT_Library;
use SWIFT_Loader;
use SWIFT_Log;
use Parser\Library\MailParser\SWIFT_MailParser;
use Parser\Library\MailParser\SWIFT_MailParserEmail;
use Parser\Models\Log\SWIFT_ParserLog;
use Parser\Library\Rule\SWIFT_ParserRuleManager;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_Mail;
use SWIFT_TemplateEngine;
use Base\Models\Template\SWIFT_TemplateGroup;
use Throwable;
use Tickets\Models\Status\SWIFT_Status_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Merge\SWIFT_TicketMergeLog;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;

/**
 * The Ticket Email Parser Lib
 *
 * @property SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_TicketEmailParser extends SWIFT_Library
{
    /**
     * @var \Parser\Library\MailParser\SWIFT_MailParserEmail
     */
    protected $MailParserEmail = null;

    /**
     * @var SWIFT_EmailQueue
     */
    protected $EmailQueue = null;

    /**
     * @var SWIFT_MailParser
     */
    protected $MailParser = null;

    /**
     * @var SWIFT_ParserRuleManager
     */
    protected $ParserRuleManager = null;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param SWIFT_MailParserEmail                      $_SWIFT_MailParserEmailObject
     * @param \Parser\Models\EmailQueue\SWIFT_EmailQueue $_SWIFT_EmailQueueObject
     * @param SWIFT_MailParser                           $_SWIFT_MailParserObject
     * @param SWIFT_ParserRuleManager                    $_SWIFT_ParserRuleManagerObject
     *
     * @throws SWIFT_Exception
     */
    public function __construct(
        SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject,
        SWIFT_EmailQueue $_SWIFT_EmailQueueObject,
        SWIFT_MailParser $_SWIFT_MailParserObject,
        SWIFT_ParserRuleManager $_SWIFT_ParserRuleManagerObject
    ) {
        parent::__construct();

        if (
            !$this->SetMailParserEmailObject($_SWIFT_MailParserEmailObject) || !$this->SetEmailQueue($_SWIFT_EmailQueueObject) || !$this->SetMailParser($_SWIFT_MailParserObject) ||
            !$this->SetParserRuleManager($_SWIFT_ParserRuleManagerObject)
        ) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $this->SetIsClassLoaded(false);

            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        SWIFT_Loader::LoadModel('Log:ParserLog', APP_PARSER);

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Set the MailParserEmail Object
     *
     * @author Varun Shoor
     *
     * @param \Parser\Library\MailParser\SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetMailParserEmailObject(SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_MailParserEmailObject instanceof SWIFT_MailParserEmail || !$_SWIFT_MailParserEmailObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->MailParserEmail = $_SWIFT_MailParserEmailObject;

        return true;
    }

    /**
     * Retrieve the MailParser Email Object
     *
     * @author Varun Shoor
     * @return \Parser\Library\MailParser\SWIFT_MailParserEmail The Mail Parser Email Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMailParserEmailObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->MailParserEmail;
    }

    /**
     * Set the Email Queue Object
     *
     * @author Varun Shoor
     *
     * @param \Parser\Models\EmailQueue\SWIFT_EmailQueue $_SWIFT_EmailQueueObject The Email Queue Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetEmailQueue(SWIFT_EmailQueue $_SWIFT_EmailQueueObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_EmailQueueObject instanceof SWIFT_EmailQueue || !$_SWIFT_EmailQueueObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->EmailQueue = $_SWIFT_EmailQueueObject;

        return true;
    }

    /**
     * Retrieve the Email Queue Object
     *
     * @author Varun Shoor
     * @return SWIFT_EmailQueue The Parser\Models\EmailQueue\SWIFT_EmailQueue Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetEmailQueue()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->EmailQueue;
    }

    /**
     * Set the Parser Rule Manager Object
     *
     * @author Varun Shoor
     * @param SWIFT_ParserRuleManager $_SWIFT_ParserRuleManagerObject The Parser Rule Manager Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetParserRuleManager(SWIFT_ParserRuleManager $_SWIFT_ParserRuleManagerObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_ParserRuleManagerObject instanceof SWIFT_ParserRuleManager || !$_SWIFT_ParserRuleManagerObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->ParserRuleManager = $_SWIFT_ParserRuleManagerObject;

        return true;
    }

    /**
     * Retrieve the Parser Rule Manager Object
     *
     * @author Varun Shoor
     * @return SWIFT_ParserRuleManager The Parser\Library\Rule\SWIFT_ParserRuleManager Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetParserRuleManager()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->ParserRuleManager;
    }

    /**
     * Set the Email Parser Object
     *
     * @author Varun Shoor
     * @param SWIFT_MailParser $_SWIFT_MailParserObject The Mail Parser Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetMailParser(SWIFT_MailParser $_SWIFT_MailParserObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_MailParserObject instanceof SWIFT_MailParser || !$_SWIFT_MailParserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->MailParser = $_SWIFT_MailParserObject;

        return true;
    }

    /**
     * Retrieve the Mail Parser Object
     *
     * @author Varun Shoor
     * @return SWIFT_MailParser The Parser\Library\MailParser\SWIFT_MailParser Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMailParser()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->MailParser;
    }

    /**
     * Process the Mail Parser Email and convert it into a ticket/reply/staff reply
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Process()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        // Check for ticket id or mask id in subject, if found check for existance of ticket
        $_ticketIDContainer = $this->getTicketIdContainer();

        $_ticketID = false;

        if (!empty($_ticketIDContainer['mask'])) {
            $_ticketID = SWIFT_Ticket::GetTicketIDFromMask($_ticketIDContainer['mask']);
        } else if (!empty($_ticketIDContainer['id'])) {
            $_ticketID = $_ticketIDContainer['id'];
        }

        $_SWIFT_TicketObject = false;
        if ($_ticketID != false) {
            try {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                $_SWIFT_TicketObject = false;
                // @codeCoverageIgnoreEnd
            }
        }

        // Check for merged status, this step will only be executed if there was a ticket id or mask in subject and we were unable to find it in the database
        list($_SWIFT_TicketObject, $_ticketIDContainer) = $this->checkMergedStatus($_SWIFT_TicketObject, $_ticketIDContainer);

        // Reply, staff reply and other logic
        $_isStaffReply = false;
        $_staffID = false;

        // We need to match first five characters of hash if its a suffix based process, if it fails.. we force a new ticket
        if (
            $_ticketIDContainer['issuffix'] == true && $_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()
            && substr($_SWIFT_TicketObject->GetProperty('tickethash'), 0, 5) != $_ticketIDContainer['suffixpassword']
        ) {
            $_SWIFT_TicketObject = false;
        }

        list($_staffID, $_isStaffReply) = $this->checkStaffReply($_SWIFT_TicketObject, $_ticketIDContainer, $_staffID, $_isStaffReply);

        // Load the template group
        $_templateGroupID = $this->EmailQueue->GetProperty('tgroupid');
        if (!SWIFT_TemplateGroup::IsValidTemplateGroupID($_templateGroupID)) {
            $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
        }
        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);
        $this->Template->SetTemplateGroupID($_templateGroupID);

        // Basic properties
        $_SWIFT_TicketPostObject = false;
        $_dispatchAutoResponder = true;
        $_isNewTicket = false;


        // Pre-Parse Rule Logic
        $_ruleIsReply = $_ruleIsStaffReply = $_ruleIsThirdPartyReply = false;
        $_isTicketLoaded = $_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded();
        if ($_isTicketLoaded && $_isStaffReply == false) {
            if ($_ticketIDContainer['isthirdparty'] == true) {
                $_ruleIsThirdPartyReply = true;
            }

            $_ruleIsReply = true;
        } else if ($_isTicketLoaded && $_isStaffReply == true) {
            $_ruleIsStaffReply = true;
        }

        $this->MailParserEmail->SetProperty('isreply', $_ruleIsReply);
        $this->MailParserEmail->SetProperty('isstaffreply', $_ruleIsStaffReply);
        $this->MailParserEmail->SetProperty('isthirdpartyreply', $_ruleIsThirdPartyReply);

        $this->ParserRuleManager->ExecutePreParse();

        if ($this->MailParserEmail->GetProperty('ignoreemail') == true) {
            throw new SWIFT_Exception('Ignoring Email (Parser rule: ' . $this->MailParserEmail->GetProperty('parserruletitle') . ')');
        }

        if ($this->MailParserEmail->GetProperty('noautoresponder') == true) {
            $_dispatchAutoResponder = false;
        }

        // Cleanup the subject
        $this->MailParserEmail->SetSubject($this->CleanupSubject($this->MailParserEmail->GetSubject()));

        $_noTicketReply = false;
        if ($this->MailParserEmail->GetProperty('noticketreply') == true) {
            $_noTicketReply = true;
        } else if ($_isTicketLoaded && $_SWIFT_TicketObject->GetProperty('isresolved') == '1' && $this->Settings->Get('pr_createnewticket') == '1') {
            $_noTicketReply = true;
        }

        $_dispatchStaffEmailContainer = false;
        $_isPrivate = false;

        /** BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4775 : Tickets should use the arrival time of email messages, rather than relying on the timestamp in the header
         */
        $_parserEmailCreationDate = DATENOW;

        /** BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
         *
         * SWIFT-4123 : Tickets created from outlook or thunderbird can have creation date greater than the present date
         *
         * Comments : $_parserEmailCreationDate returns GMT time of date field from email headers say (Mon, 16 Feb 2015 15:01:42 +0100), so comparing with the current GMT to avoid tickets which have date of creation greater than current date(GMT).
         */
        //Adding one hour leverage/margin to the current GMT time().
        if ($_parserEmailCreationDate > (DATENOW + 60 * 60)) {
            //We can't create a ticket whose time greater than the current GMT, so to notify admin adding corresponding entry to the parser logs as well to __swift/logs.
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $this->Log->Log('Failed to process this email : Creation date (' . gmdate("d M Y H:i:s", $_parserEmailCreationDate) . ') is greater than current date (' . gmdate("d M Y H:i:s", DATENOW) . ')', SWIFT_Log::TYPE_ERROR, 'SWIFT_TicketEmailParser');
            throw new SWIFT_Exception('Ignoring Email (Ticket can\'t be created: Date of creation must not be greater than current system time)');
            // @codeCoverageIgnoreEnd
        }

        list($_retStatus, $_SWIFT_TicketPostObject, $_SWIFT_StaffObject, $_dispatchStaffEmailContainer, $_isPrivate, $_dispatchAutoResponder, $_SWIFT_TicketObject, $_isNewTicket) = $this->processReplies(
            $_SWIFT_TicketObject,
            $_isStaffReply,
            $_noTicketReply,
            $_SWIFT_TemplateGroupObject,
            $_ticketIDContainer,
            $_parserEmailCreationDate,
            $_staffID,
            $_dispatchAutoResponder,
            $_isPrivate
        );

        // added return from above refactoring
        if ($_retStatus === false) {
            return false;
        }

        if (!($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded())) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception('Invalid Ticket Object');
            // @codeCoverageIgnoreEnd
        }

        if (!($_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded())) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception('Invalid Ticket Post Object');
            // @codeCoverageIgnoreEnd
        }

        /*
        * BUG FIX - Ruchi Kothari
        *
        * SWIFT-2240: Pre-Parser rule option "Do Not Process Ticket Alert Rules" does not work.
        *
        * Comments: Set noalerts for ticket
        */
        $_noAlerts = false;
        if ($this->MailParserEmail->GetProperty('noalerts') == true) {
            $_noAlerts = true;
        }
        $_SWIFT_TicketObject->SetNoAlerts($_noAlerts);

        // Process the attachments
        $_attachmentCount = $this->processAttachments(
            $_SWIFT,
            $_SWIFT_TicketObject,
            $_SWIFT_TicketPostObject,
            $_dispatchStaffEmailContainer
        );

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5180: Column 'hasattachments' is not being populated for tickets parsed via email parser.
         *
         * Comment: When the ticket has attachment in the headers mark the hasattachment as '1' in swtickets and swticketposts table
         *
         **/
        if ($_attachmentCount > 0) {
            $_SWIFT_TicketObject->MarkHasAttachments();
            $_SWIFT_TicketPostObject->UpdateHasAttachments(1);
        }

        /*
         * BUG FIX - Anjali Sharma
         *
         * SWIFT-3942: Private ticket reply using mail parser rule sends an email to the end user too
         */
        if (_is_array($_dispatchStaffEmailContainer) && !$_isPrivate) {

            /*
             * BUG FIX - Parminder Singh
             *
             * SWIFT-1475 Email queue signature and staff signature are not sent to the end user when a staff member replies to a notification email
             *
             */
            $_signatureContentsDefault = $_signatureContentsHTML = '';
            if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature($this->MailParserEmail->GetFinalContentIsHTML(), $_SWIFT_StaffObject);
                $_signatureContentsHTML = $_SWIFT_TicketObject->GetSignature(true, $_SWIFT_StaffObject);
            }

            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
            $_sender = isset($this->MailParserEmail) ? $this->MailParserEmail->GetFromEmail() : '';
            $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply($_dispatchStaffEmailContainer['staff'], $_dispatchStaffEmailContainer['contents'],
	            $_dispatchStaffEmailContainer['ishtml'], '', array($_signatureContentsDefault, $_signatureContentsHTML), $_sender);
        }

        // Dispatch the autoresponder if its a new ticket
        if ($_dispatchAutoResponder == true && $_isNewTicket == true) {
            $_SWIFT_TicketObject->DispatchAutoresponder();
        }

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();


        return true;
    }

    /**
     * Process the ticket recipients
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     *
     * @return array The Complete Recipient Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessRecipients(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_existingRecipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($_SWIFT_TicketObject);
        $_existingRecipientList = array();
        $_finalRecipientList = $_completeRecipientContainer = array();
        if (_is_array($_existingRecipientContainer)) {
            /**
             * @var int $_recipientType
             * @var array $_recipientEmailContainer
             */
            foreach ($_existingRecipientContainer as $_recipientType => $_recipientEmailContainer) {
                if (_is_array($_recipientEmailContainer)) {
                    foreach ($_recipientEmailContainer as $_emailAddress) {
                        if (!in_array($_emailAddress, $_existingRecipientList) && IsEmailValid($_emailAddress)) {
                            $_existingRecipientList[] = $_emailAddress;
                        }
                    }
                }
            }

            $_completeRecipientContainer = $_existingRecipientContainer;
        }

        $_recipientList = array_merge($this->MailParserEmail->GetRecipients(), (array)$this->MailParserEmail->GetFromEmail());
        $_existingRecipientList[] = $_SWIFT_TicketObject->GetProperty('replyto');
        $_existingRecipientList[] = $_SWIFT_TicketObject->GetProperty('email');

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2168 To recipient is stripped when the email is parsed and only CC'd user email addresses are retained.
         */
        if ($this->Settings->Get('t_autorecip') == '1') {
            $_queueCache = (array) $this->Cache->Get('queuecache');
            foreach ($_recipientList as $_emailAddress) {
                if (isset($_queueCache['pointer'][$_emailAddress])) {
                    continue;
                }

                // We add new recipients if needed
                if (!empty($_emailAddress) && IsEmailValid($_emailAddress) && !in_array($_emailAddress, $_existingRecipientList) && !in_array($_emailAddress, $_finalRecipientList)) {
                    $_finalRecipientList[] = $_emailAddress;
                    $_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_CC][] = $_emailAddress;
                }
            }
        }

        if (count($_finalRecipientList)) {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_CC, $_finalRecipientList);
        }

        return $_completeRecipientContainer;
    }

    /**
     * Parse the Ticket ID and other properties from a subject
     *
     * @author Varun Shoor
     *
     * @param string                                           $_subject The Subject
     * @param \Parser\Library\MailParser\SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject
     *
     * @return mixed "_ticketIDContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ParseTicketID($_subject, $_SWIFT_MailParserEmailObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $_ticketIDContainer = array();
        $_ticketIDContainer['isthirdparty'] = false;
        $_ticketIDContainer['isalert'] = false;
        $_ticketIDContainer['mask'] = false;
        $_ticketIDContainer['id'] = false;
        $_ticketIDContainer['issuffix'] = false;
        $_ticketIDContainer['suffixpassword'] = false;

        $_result = array();


        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3369 In case we change the existing ‘Ticket ID Type’ from Sequential to Random or vice versa, new Client’s reply creates a new ticket in the help desk, instead of appending to its original ticket.
         *
         * Comments: None
         */
        /**
         * ---------------------------------------------
         * NEW V4 SYNTAX
         * ---------------------------------------------
         */
        if (preg_match('@\[([A-Za-z0-9_ ]{1,100}) (\#|~|!)([A-Za-z]{3}-[0-9]{3}-[0-9]{5,6})\]@', $_subject, $_result)) {
            // First try to parse a ticket mask with a 1-100 digit company code prefixed. Example: [KAYAKO #ABC-123-12345]: Subject
            // 1 = Company Prefix
            // 2 = Ticket Mask ID

            $_ticketIDContainer['isthirdparty'] = IIF($_result[2] === '~', true, false);
            $_ticketIDContainer['isalert'] = IIF($_result[2] === '!', true, false);
            $_ticketIDContainer['mask'] = $_result[3];
        } else if (preg_match('@\[(\#|~|!)([A-Za-z]{3}-[0-9]{3}-[0-9]{5,6})\]@', $_subject, $_result)) {
            // No luck? Now try to get a simple ticket mask then. Example: [#ABC-123-12345]: Subject
            // 1 = Ticket Mask ID

            $_ticketIDContainer['isthirdparty'] = IIF($_result[1] === '~', true, false);
            $_ticketIDContainer['isalert'] = IIF($_result[1] === '!', true, false);
            $_ticketIDContainer['mask'] = $_result[2];

            /**
             * ---------------------------------------------
             * BACKWARD COMPATIBLE MASK CHECK
             * ---------------------------------------------
             */
        } else if (preg_match('@\[([A-Za-z0-9_ ]{1,100}) (\#|~|!)([A-Za-z]{3}-[0-9]{5,6})\]@', $_subject, $_result)) {
            // First try to parse a ticket mask with a 1-100 digit company code prefixed. Example: [KAYAKO #ABC-12345]: Subject
            // 1 = Company Prefix
            // 2 = Ticket Mask ID

            $_ticketIDContainer['isthirdparty'] = IIF($_result[2] === '~', true, false);
            $_ticketIDContainer['isalert'] = IIF($_result[2] === '!', true, false);
            $_ticketIDContainer['mask'] = $_result[3];
        } else if (preg_match('@\[(\#|~|!)([A-Za-z]{3}-[0-9]{5,6})\]@', $_subject, $_result)) {
            // No luck? Now try to get a simple ticket mask then. Example: [#ABC-12345]: Subject
            // 1 = Ticket Mask ID

            $_ticketIDContainer['isthirdparty'] = IIF($_result[1] === '~', true, false);
            $_ticketIDContainer['isalert'] = IIF($_result[1] === '!', true, false);
            $_ticketIDContainer['mask'] = $_result[2];
        } else if (preg_match('@\[([A-Za-z]{3}-[0-9]{5,6})\]@', $_subject, $_result)) {
            // Give another try at ticket mask, This is added for backwards compatibility. Example: [ABC-12345]: Subject
            // 1 = Ticket Mask ID

            $_ticketIDContainer['mask'] = $_result[1];

            /**
             * ---------------------------------------------
             * NUMERICAL ID CHECKS
             * ---------------------------------------------
             */
        } else if (preg_match('@\[([A-Za-z0-9_ ]{1,100}) (\#|~|!)([0-9]{5,6})\]@', $_subject, $_result)) {
            // Try to get a ticket id with prefix?. Example: [KAYAKO #12345]: Subject
            // 1 = Company Prefix
            // 2 = Ticket Mask ID

            $_ticketIDContainer['isthirdparty'] = IIF($_result[2] === '~', true, false);
            $_ticketIDContainer['isalert'] = IIF($_result[2] === '!', true, false);
            $_ticketIDContainer['id'] = $_result[3];
        } else if (preg_match('@\[(\#|~|!)([0-9]{1,22})\]@', $_subject, $_result)) {
            // Still nothing? Try to get the numeric ticket id then. Example: [#3412]: Subject
            // 1 key = 12345

            $_ticketIDContainer['isthirdparty'] = IIF($_result[1] === '~', true, false);
            $_ticketIDContainer['isalert'] = IIF($_result[1] === '!', true, false);
            $_ticketIDContainer['id'] = $_result[2];
        } else if (preg_match('@\[([A-Za-z0-9_ ]{1,100}) (\#|~|!)([0-9]{1,22})\]@', $_subject, $_result)) {
            // Still nothing? Try to get the numeric ticket id then. Example: [PREFIX #3412]: Subject
            // 1 key = 12345

            $_ticketIDContainer['isthirdparty'] = IIF($_result[2] === '~', true, false);
            $_ticketIDContainer['isalert'] = IIF($_result[2] === '!', true, false);
            $_ticketIDContainer['id'] = $_result[3];
        }

        if (!empty($_ticketIDContainer['mask']) || !empty($_ticketIDContainer['id'])) {
            return $_ticketIDContainer;
        }

        // No ticket id found, attempt lookups in destination suffix
        if ($_SWIFT_MailParserEmailObject instanceof SWIFT_MailParserEmail && $_SWIFT_MailParserEmailObject->GetIsClassLoaded() && $_SWIFT_MailParserEmailObject->GetToEmailSuffix() != '') {
            // Do we have mode and hash in there? Example: r.hash.id
            if (strpos($_SWIFT_MailParserEmailObject->GetToEmailSuffix(), '.')) {
                $_toEmailSuffixChunks = explode('.', $_SWIFT_MailParserEmailObject->GetToEmailSuffix());

                if (
                    count($_toEmailSuffixChunks) == 3
                    && ($_toEmailSuffixChunks[0] === 'r' || $_toEmailSuffixChunks[0] === 'a' || $_toEmailSuffixChunks[0] === 't')
                    && is_numeric($_toEmailSuffixChunks[2])
                ) {
                    $_ticketIDContainer['id'] = $_toEmailSuffixChunks[2];
                    $_ticketIDContainer['issuffix'] = true;
                    $_ticketIDContainer['suffixpassword'] = $_toEmailSuffixChunks[1];

                    if ($_toEmailSuffixChunks[0] === 'a') {
                        $_ticketIDContainer['isalert'] = true;
                    } else if ($_toEmailSuffixChunks[0] === 't') {
                        $_ticketIDContainer['isthirdparty'] = true;
                    }
                }
            }
        }

        return $_ticketIDContainer;
    }

    /**
     * This function cleans up the subject of its ticket id junk and returns a plain one, very useful in case a person
     * replies to a ticket that doesnt exist..
     *
     * @author Varun Shoor
     * @param string $_subject The Subject
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CleanupSubject($_subject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // First see if a subject has a ticket id in it?
        $_results = array();
        if ($this->ParseTicketID($_subject) && preg_match('@\[(.*)\]:(.*)@', $_subject, $_results)) {
            return $_results[2];
        }

        return $_subject;
    }

    /**
     * @return mixed
     * @throws SWIFT_Exception
     */
    protected function getTicketIdContainer()
    {
        $_ticketIDContainer = $this->ParseTicketID($this->MailParserEmail->GetSubject(), $this->MailParserEmail);

        // If the fetch against subject failed, we try some more exotic methods
        if (empty($_ticketIDContainer['mask']) && empty($_ticketIDContainer['id'])) {
            // We try to look it up in the text body
            if (empty($_ticketIDContainer['id']) && empty($_ticketIDContainer['mask']) && $this->Settings->Get('t_searchticketidinbody') == 1) {
                $_ticketIDContainer = $this->ParseTicketID($this->MailParserEmail->GetText()); // try to look it up in the mail body
            }

            // The above lookup failed? try against HTML body
            if (empty($_ticketIDContainer['id']) && empty($_ticketIDContainer['mask']) && $this->Settings->Get('t_searchticketidinbody') == 1) {
                // We try to look it up in the html body
                $_ticketIDContainer = $this->ParseTicketID($this->MailParserEmail->GetHTML()); // try to look it up in the mail body
            }

            // Its Still Empty?!?!?! How in this world can this be possible.. we only proceed if the subject has :, otherwise its most probably a new ticket..
            if (empty($_ticketIDContainer['id']) && empty($_ticketIDContainer['mask']) && stristr(
                $this->MailParserEmail->GetSubject(),
                ':'
            )) {
                $_parsedSubjectPrefix = mb_strtolower(trim(mb_substr(
                    $this->MailParserEmail->GetSubject(),
                    0,
                    strpos($this->MailParserEmail->GetSubject(), ':')
                )));
                $_parsedSubject = trim(mb_substr(mb_stristr($this->MailParserEmail->GetSubject(), ':'), 1));

                if ($_parsedSubjectPrefix != 'fw') {
                    // See if we can look it up using similar subject..
                    $_subjectReference = $this->Database->QueryFetch("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
                        WHERE email = '" . $this->Database->Escape($this->MailParserEmail->GetFromEmail()) . "' AND subject = '" . $this->Database->Escape(mb_substr(
                        $_parsedSubject,
                        0,
                        255
                    )) . "' ORDER BY ticketid DESC");
                    if (!empty($_subjectReference['ticketid'])) {
                        $_ticketIDContainer['id'] = $_subjectReference['ticketid'];
                    }
                }
            }

            // Ok.. I give up.. this has to be a new ticket..
        }
        return $_ticketIDContainer;
    }

    /**
     * @param SWIFT_Ticket|bool $_SWIFT_TicketObject
     * @param array $_ticketIDContainer
     * @return array
     * @throws SWIFT_Exception
     */
    protected function checkMergedStatus($_SWIFT_TicketObject, $_ticketIDContainer)
    {
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket && (!empty($_ticketIDContainer['mask']) || !empty($_ticketIDContainer['id']))) {
            $_mergedTicketID = false;
            if (!empty($_ticketIDContainer['mask'])) {
                // Is this ticket merged?
                $_mergedTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketMaskID($_ticketIDContainer['mask']);
            } else {
                if (!empty($_ticketIDContainer['id'])) {
                    // Is this ticket merged? (yeah again another check)
                    $_mergedTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketID($_ticketIDContainer['id']);
                }
            }

            // If we found a merge reference, then attempt to load the ticket object
            if (!empty($_mergedTicketID)) {
                $_ticketID = $_mergedTicketID;
                $_ticketIDContainer['id'] = $_ticketID;
                try {
                    $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    $_SWIFT_TicketObject = false;
                }
            }
        }

        return [$_SWIFT_TicketObject, $_ticketIDContainer];
    }

    /**
     * @param SWIFT_Ticket|bool $_SWIFT_TicketObject
     * @param array $_ticketIDContainer
     * @param int|bool $_staffID
     * @param bool $_isStaffReply
     * @return array
     * @throws SWIFT_Exception
     */
    protected function checkStaffReply($_SWIFT_TicketObject, $_ticketIDContainer, $_staffID, $_isStaffReply)
    {
        if (
            $_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() &&
            $_ticketIDContainer['isthirdparty'] == false
        ) {
            /**
             * Bug Fix    : Saloni Dhall <saloni.dhall@kayako.com>
             * SWIFT-3022 : Allow Staff to reply by Email' is not taking effect in some cases
             * Comments : Added clause to check settings and show appropriate message.
             */
            $_staffID = SWIFT_Staff::IsStaffEmail($this->MailParserEmail->GetFromEmail());
            $usermail = trim($_SWIFT_TicketObject->GetProperty('email'));
            if ($_staffID != false && strcasecmp($usermail, trim($this->MailParserEmail->GetFromEmail())) != 0) {
                $_isStaffReply = true;
            }
            if (!($this->Settings->Get('t_pstaffreply')) && $_isStaffReply) {
                //Load phrases...........
                $this->Load->Library('Mail:Mail');
                $this->Language->Load('staff_tickets', SWIFT_LanguageEngine::TYPE_FILE);
                $_staffEmailheader = sprintf(
                    $this->Language->Get('rejectionstaffemail_header'),
                    $this->MailParserEmail->GetSubject()
                );
                $_staffEmailContents = $this->Language->Get('rejectionstaffemail_contents');
                $this->Template->Assign('_staffEmailHeader', $_staffEmailheader);
                $this->Template->Assign('_staffEmailContents', $_staffEmailContents);
                $_textEmailContents = $this->Template->Get('staffreply_notallowed');
                $_htmlEmailContents = $_textEmailContents;
                $this->Mail->SetFromField($this->EmailQueue->Get('email'), SWIFT::Get('companyname'));
                $this->Mail->SetToField($this->MailParserEmail->GetFromEmail());
                $this->Mail->SetSubjectField(sprintf(
                    $this->Language->Get('rejectionstaffemail_subject'),
                    $this->MailParserEmail->GetSubject()
                ));
                $this->Mail->SetDataText($_textEmailContents);
                $this->Mail->SetDataHTML($_htmlEmailContents);
                $this->Mail->SendMail(false, $this->EmailQueue->GetEmailQueueID());
                throw new SWIFT_Exception('Ignoring Email (Setting : Staff can reply to tickets by email is disabled, ticket #' . $_SWIFT_TicketObject->GetTicketDisplayID() . ')');
            }
        }

        return [$_staffID, $_isStaffReply];
    }

    /**
     * @param SWIFT_Ticket|bool $_SWIFT_TicketObject
     * @param SWIFT_TemplateGroup|bool $_SWIFT_TemplateGroupObject
     * @param array $_ticketIDContainer
     * @param int $_parserEmailCreationDate
     * @return SWIFT_TicketPost
     * @throws SWIFT_Exception
     */
    protected function processUserReply(
        $_SWIFT_TicketObject,
        $_SWIFT_TemplateGroupObject,
        $_ticketIDContainer,
        $_parserEmailCreationDate
    ) {
        /**
         * BUG FIX - Saloni Dhall, Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3032 : Disabled users are still able to create a ticket via email
         */
        $_userIDFromEmail = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($this->MailParserEmail->GetFromEmail());
        $_SWIFT_UserObject = $_userID = false;

        if (!empty($_userIDFromEmail)) {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userIDFromEmail));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            // Check if user is disabled
            if ($_SWIFT_UserObject->GetProperty('isenabled') == '0') {
                $this->Load->Library('Mail:Mail');

                // Load the phrases from the database..
                $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->Queue('ticketsmain', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->Queue('ticketemails', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

                $_textEmailContents = $this->Template->Get('email_disableduser_text', SWIFT_TemplateEngine::TYPE_DB);
                $_htmlEmailContents = $this->Template->Get('email_disableduser_html', SWIFT_TemplateEngine::TYPE_DB);

                $this->Mail->SetFromField($this->EmailQueue->GetProperty('email'), SWIFT::Get('companyname'));
                $this->Mail->SetToField($this->MailParserEmail->GetFromEmail());
                $this->Mail->SetSubjectField($this->Language->Get('disableduserrejectsub'));
                $this->Mail->SetDataText($_textEmailContents);
                $this->Mail->SetDataHTML($_htmlEmailContents);
                $this->Mail->SendMail(false, $this->EmailQueue->GetEmailQueueID());

                throw new SWIFT_Exception($this->Language->Get('errdisableduser') . htmlspecialchars($this->MailParserEmail->GetFromEmail()));
            }
            unset($_SWIFT_UserObject);
        } else {
            if (empty($_userIDFromEmail) && $this->EmailQueue->GetProperty('registrationrequired') == '1') {
                /*
                 * BUG FIX - Saloni Dhall
                 *
                 * SWIFT-3215 Deleted user should not be allowed to update the ticket if ‘Registration Required?’ is enabled
                 *
                 * Comments: if the user does not registered and setting is enabled then do not parse the ticket post.
                 */
                throw new SWIFT_Exception('Ignoring Email (User not found : ' . $this->MailParserEmail->GetFromEmail() . ' )');
            }
        }

        // Process the ticket recipients
        $_completeRecipientContainer = $this->ProcessRecipients($_SWIFT_TicketObject);

        // Get a user id for this user
        $_userID = SWIFT_Ticket::GetOrCreateUserID(
            $this->MailParserEmail->GetFromName(),
            $this->MailParserEmail->GetFromEmail(),
            $_SWIFT_TemplateGroupObject->GetProperty('regusergroupid')
        );
        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));

        // We need to first see if the person who sent this email is actually the user or a CC/BCC/Third party
        $_creatorType = SWIFT_TicketPost::CREATOR_USER;
        if (isset($_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && is_array($_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && in_array(
            $this->MailParserEmail->GetFromEmail(),
            $_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_CC]
        )) {
            $_creatorType = SWIFT_TicketPost::CREATOR_CC;
        } else {
            if (isset($_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && is_array($_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && in_array(
                $this->MailParserEmail->GetFromEmail(),
                $_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_BCC]
            )) {
                $_creatorType = SWIFT_TicketPost::CREATOR_BCC;
            } else {
                if (($_ticketIDContainer['isthirdparty'] == true)
                    || (isset($_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY]) && is_array($_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY]) && in_array(
                        $this->MailParserEmail->GetFromEmail(),
                        $_completeRecipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY]
                    ))
                ) {
                    $_creatorType = SWIFT_TicketPost::CREATOR_THIRDPARTY;
                }
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1140 "Ticket Status (Post Parse)" criteria does not work
         *
         * Comments:
         */
        // Execute Post Parse Rules
        $this->ParserRuleManager->ExecutePostParse($_SWIFT_TicketObject);

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-3293 "Posted on" date is not valid when viewing a ticket.
         *
         * Comments: None
         */
        $_ticketPostID = SWIFT_TicketPost::CreateClient(
            $_SWIFT_TicketObject,
            $_SWIFT_UserObject,
            SWIFT_Ticket::CREATIONMODE_EMAIL,
            $this->MailParserEmail->GetProperty('finalcontent'),
            $this->MailParserEmail->GetSubject(),
            $_creatorType,
            $this->MailParserEmail->GetFinalContentIsHTML(),
            $this->MailParserEmail->GetFromEmail(),
            $this->MailParserEmail->GetAttachments(),
            $_parserEmailCreationDate
        );

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));

        /*
         * BUG FIX - Mahesh Salaria
         *
         * SWIFT-1484: Correct SLA plan is not applied if post-parse rules exists.
         *
         * Comments: Reset SLA and Execute to calculate SLA again.
         */
        $_SWIFT_TicketObject->ResetSLA();

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3197 Resolution time is reset when user/client reply is made by email parser
         */
        $_SWIFT_TicketObject->ExecuteSLA(false);

        // Create the parser log
        SWIFT_ParserLog::Create(
            SWIFT_ParserLog::TYPE_SUCCESS,
            $this->EmailQueue->GetEmailQueueID(),
            $_SWIFT_TicketObject->GetTicketID(),
            $this->MailParserEmail->GetSubject(),
            $this->MailParserEmail->GetFromEmail(),
            $this->MailParserEmail->GetToEmail(),
            $this->MailParserEmail->GetFinalContentSize(),
            sprintf(
                $this->Language->Get('scccreatedreply'),
                $_ticketPostID,
                $_SWIFT_TicketObject->GetTicketDisplayID()
            ),
            $this->MailParser->GetRawEmailData(),
            (GetMicroTime() - SWIFT::Get('parserstarttime')),
            [
                'responsetype' => 'ticketpost',
                'ticketmaskid' => $_SWIFT_TicketObject->GetProperty('ticketmaskid'),
                'ticketpostid' => $_ticketPostID,
            ],
            $this->MailParserEmail->GetMessageID()
        );

        return $_SWIFT_TicketPostObject;
    }

    /**
     * @param array $_staffCache
     * @param int|bool $_staffID
     * @param SWIFT_Ticket|bool $_SWIFT_TicketObject
     * @param int $_parserEmailCreationDate
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processStaffReply($_staffCache, $_staffID, $_SWIFT_TicketObject, $_parserEmailCreationDate)
    {
        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataStore($_staffCache[$_staffID]));

        $_dispatchStaffEmailContainer = [
            'staff' => $_SWIFT_StaffObject,
            'contents' => $this->MailParserEmail->GetProperty('finalcontent'),
            'ishtml' => $this->MailParserEmail->GetFinalContentIsHTML(),
        ];

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1140 "Ticket Status (Post Parse)" criteria does not work
         *
         * Comments:
         */
        // Execute Post Parse Rules
        $this->ParserRuleManager->ExecutePostParse($_SWIFT_TicketObject);

        $_isPrivate = $this->MailParserEmail->GetProperty('isprivate');

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-3293 "Posted on" date is not valid when viewing a ticket.
         *
         * Comments: None
         */

        $_SWIFT_TicketPostObject = SWIFT_TicketPost::CreateStaff(
            $_SWIFT_TicketObject,
            $_SWIFT_StaffObject,
            SWIFT_Ticket::CREATIONMODE_EMAIL,
            $this->MailParserEmail->GetProperty('finalcontent'),
            $this->MailParserEmail->GetSubject(),
            true,
            $this->MailParserEmail->GetFinalContentIsHTML(),
            '',
            $_isPrivate,
            $_parserEmailCreationDate
        );
        $_ticketPostID = $_SWIFT_TicketPostObject->GetTicketPostID();

        // Create the parser log
        SWIFT_ParserLog::Create(
            SWIFT_ParserLog::TYPE_SUCCESS,
            $this->EmailQueue->GetEmailQueueID(),
            $_SWIFT_TicketObject->GetTicketID(),
            $this->MailParserEmail->GetSubject(),
            $this->MailParserEmail->GetFromEmail(),
            $this->MailParserEmail->GetToEmail(),
            $this->MailParserEmail->GetFinalContentSize(),
            sprintf(
                $this->Language->Get('scccreatedstaffreply'),
                $_ticketPostID,
                $_SWIFT_TicketObject->GetTicketDisplayID()
            ),
            $this->MailParser->GetRawEmailData(),
            (GetMicroTime() - SWIFT::Get('parserstarttime')),
            [
                'responsetype' => 'ticketpost',
                'ticketmaskid' => $_SWIFT_TicketObject->GetProperty('ticketmaskid'),
                'ticketpostid' => $_ticketPostID,
            ],
            $this->MailParserEmail->GetMessageID()
        );

        return [$_SWIFT_StaffObject, $_dispatchStaffEmailContainer, $_isPrivate, $_SWIFT_TicketPostObject];
    }

    /**
     * @param SWIFT_User $_SWIFT_UserObject
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function doCreateUser($_SWIFT_UserObject)
    {
        /**
         * BUG FIX - Saloni Dhall, Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3032 : Disabled users are still able to create a ticket via email.
         *
         * Comments: System should check if user is disabled.
         */
        if ($_SWIFT_UserObject->GetProperty('isenabled') == '0') {
            $this->Load->Library('Mail:Mail');

            // We dont send ANY email if loop control is triggered
            if (SWIFT_LoopBlock::CheckIfAddressIsBlocked($this->MailParserEmail->GetFromEmail()) == true) {
                throw new SWIFT_Exception('Ignoring Email (User disabled: ' . $this->MailParserEmail->GetFromEmail() . ' )');
            }

            // Load the phrases from the database..
            $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
            $this->Language->Queue('ticketsmain', SWIFT_LanguageEngine::TYPE_DB);
            $this->Language->Queue('ticketemails', SWIFT_LanguageEngine::TYPE_DB);
            $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

            $_textEmailContents = $this->Template->Get('email_disableduser_text', SWIFT_TemplateEngine::TYPE_DB);
            $_htmlEmailContents = $this->Template->Get('email_disableduser_html', SWIFT_TemplateEngine::TYPE_DB);

            $this->Mail->SetFromField($this->EmailQueue->GetProperty('email'), SWIFT::Get('companyname'));
            $this->Mail->SetToField($this->MailParserEmail->GetFromEmail());
            $this->Mail->SetSubjectField($this->Language->Get('disableduserrejectsub'));
            $this->Mail->SetDataText($_textEmailContents);
            $this->Mail->SetDataHTML($_htmlEmailContents);
            $this->Mail->SendMail(false, $this->EmailQueue->GetEmailQueueID());

            throw new SWIFT_Exception(sprintf(
                $this->Language->Get('errdisableduser'),
                htmlspecialchars($this->MailParserEmail->GetFromEmail())
            ));
        }

        return true;
    }

    /**
     * @param SWIFT_User|bool $_SWIFT_UserObject
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function processUserNotRegistered($_SWIFT_UserObject)
    {
        if ((!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) && $this->EmailQueue->GetProperty('registrationrequired') == '1') {
            if (
                !stristr(
                    $this->MailParserEmail->GetFromEmail(),
                    'MAILER-DAEMON'
                ) && !stristr($this->MailParserEmail->GetSubject(), 'failure notice') &&
                !stristr($this->MailParserEmail->GetSubject(), 'Returned Mail')
            ) {
                $this->Load->Library('Mail:Mail');
                // Load the phrases from the database..
                $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->Queue('ticketsmain', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->Queue('ticketemails', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

                $_textEmailContents = $this->Template->Get('email_noregistration_text', SWIFT_TemplateEngine::TYPE_DB);
                $_htmlEmailContents = $this->Template->Get('email_noregistration_html', SWIFT_TemplateEngine::TYPE_DB);

                $this->Mail->SetFromField($this->EmailQueue->GetProperty('email'), SWIFT::Get('companyname'));
                $this->Mail->SetToField($this->MailParserEmail->GetFromEmail());
                $this->Mail->SetSubjectField($this->Language->Get('regemailrejectsub'));

                $this->Mail->SetDataText($_textEmailContents);
                $this->Mail->SetDataHTML($_htmlEmailContents);

                $this->Mail->SendMail(false, $this->EmailQueue->GetEmailQueueID());
            }

            throw new SWIFT_Exception(sprintf(
                $this->Language->Get('errusernotreg'),
                htmlspecialchars($this->MailParserEmail->GetFromEmail())
            ));
        }

        return true;
    }

    /**
     * @param SWIFT_User|bool $_SWIFT_UserObject
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function processUserNotValidated($_SWIFT_UserObject)
    {
        if (
            $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded() && $_SWIFT_UserObject->GetProperty('isvalidated') == '0' &&
            $this->EmailQueue->GetProperty('registrationrequired') == '1'
        ) {
            if (
                !stristr(
                    $this->MailParserEmail->GetFromEmail(),
                    'MAILER-DAEMON'
                ) && !stristr($this->MailParserEmail->GetSubject(), 'failure notice') &&
                !stristr($this->MailParserEmail->GetSubject(), 'Returned Mail')
            ) {
                $this->Load->Library('Mail:Mail');
                // Load the phrases from the database..
                $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->Queue('ticketsmain', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->Queue('ticketemails', SWIFT_LanguageEngine::TYPE_DB);
                $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

                // Do the users require staff validation?
                $_textEmailContents = $_htmlEmailContents = '';
                if ($this->Settings->Get('u_enablesveri') == '1') {
                    $_textEmailContents = $this->Template->Get(
                        'email_needsstaffvalidation_text',
                        SWIFT_TemplateEngine::TYPE_DB
                    );
                    $_htmlEmailContents = $this->Template->Get(
                        'email_needsstaffvalidation_html',
                        SWIFT_TemplateEngine::TYPE_DB
                    );
                } else {
                    $_textEmailContents = $this->Template->Get(
                        'email_notvalidatedgeneric_text',
                        SWIFT_TemplateEngine::TYPE_DB
                    );
                    $_htmlEmailContents = $this->Template->Get(
                        'email_notvalidatedgeneric_html',
                        SWIFT_TemplateEngine::TYPE_DB
                    );
                }


                $this->Mail->SetFromField($this->EmailQueue->GetProperty('email'), SWIFT::Get('companyname'));
                $this->Mail->SetToField($this->MailParserEmail->GetFromEmail());
                $this->Mail->SetSubjectField($this->Language->Get('validationemailrejectsub'));

                $this->Mail->SetDataText($_textEmailContents);
                $this->Mail->SetDataHTML($_htmlEmailContents);

                $this->Mail->SendMail(false, $this->EmailQueue->GetEmailQueueID());
            }

            throw new SWIFT_Exception(sprintf(
                $this->Language->Get('errusernotvalidated'),
                htmlspecialchars($this->MailParserEmail->GetFromEmail())
            ));
        }

        return true;
    }

    /**
     * @param int $_ownerStaffID
     * @param int $_departmentID
     * @param int $_ticketStatusID
     * @param int $_ticketPriorityID
     * @param int $_ticketTypeID
     * @param int $_userID
     * @param int $_parserEmailCreationDate
     * @param bool|int $_isPrivate
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processNewTicket(
        $_ownerStaffID,
        $_departmentID,
        $_ticketStatusID,
        $_ticketPriorityID,
        $_ticketTypeID,
        $_userID,
        $_parserEmailCreationDate,
        $_isPrivate
    ) {
        $_SWIFT_TicketObject = SWIFT_Ticket::Create(
            $this->MailParserEmail->GetSubject(),
            $this->MailParserEmail->GetFromName(),
            $this->MailParserEmail->GetFromEmail(),
            $this->MailParserEmail->GetProperty('originalfinalcontent'),
            $_ownerStaffID,
            $_departmentID,
            $_ticketStatusID,
            $_ticketPriorityID,
            $_ticketTypeID,
            $_userID,
            0,
            SWIFT_Ticket::TYPE_DEFAULT,
            SWIFT_Ticket::CREATOR_USER,
            SWIFT_Ticket::CREATIONMODE_EMAIL,
            '',
            $this->EmailQueue->GetEmailQueueID(),
            false,
            '',
            $this->MailParserEmail->GetFinalContentIsHTML(),
            $_parserEmailCreationDate
        );

        $_SWIFT_TicketPostObject = $_SWIFT_TicketObject->GetFirstPostObject();

        $_SWIFT_TicketObject->SetTemplateGroup($this->EmailQueue->GetProperty('tgroupid'));

        // Process the ticket recipients
        $this->ProcessRecipients($_SWIFT_TicketObject);

        // Create the parser log
        SWIFT_ParserLog::Create(
            SWIFT_ParserLog::TYPE_SUCCESS,
            $this->EmailQueue->GetEmailQueueID(),
            $_SWIFT_TicketObject->GetTicketID(),
            $this->MailParserEmail->GetSubject(),
            $this->MailParserEmail->GetFromEmail(),
            $this->MailParserEmail->GetToEmail(),
            $this->MailParserEmail->GetFinalContentSize(),
            sprintf($this->Language->Get('scccreatedticket'), $_SWIFT_TicketObject->GetTicketDisplayID()),
            $this->MailParser->GetRawEmailData(),
            (GetMicroTime() - SWIFT::Get('parserstarttime')),
            ['responsetype' => 'ticket', 'ticketmaskid' => $_SWIFT_TicketObject->GetProperty('ticketmaskid')],
            $this->MailParserEmail->GetMessageID()
        );

        $_isNewTicket = true;

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1140 "Ticket Status (Post Parse)" criteria does not work
         *
         * Comments:
         */
        // Execute Post Parse Rules
        $this->ParserRuleManager->ExecutePostParse($_SWIFT_TicketObject);

        /*
         * BUG FIX - Mahesh Salaria
         *
         * SWIFT-1484: Correct SLA plan is not applied if post-parse rules exists.
         *
         * Comments: Reset SLA and Execute to calculate SLA again.
         */
        /*
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-2940: Due and Resolution Due not cleared if ticket is marked as resolved via Mail Parser rule
         *
         * Comments: None
         */
        if ($_isPrivate == false && $_SWIFT_TicketObject->GetProperty('isresolved') != '1') {
            $_SWIFT_TicketObject->ResetSLA();
            $_SWIFT_TicketObject->ExecuteSLA();
        }

        return [$_SWIFT_TicketObject, $_SWIFT_TicketPostObject, $_isNewTicket];
    }

    /**
     * @param SWIFT $_SWIFT
     * @param SWIFT_Ticket|bool $_SWIFT_TicketObject
     * @param SWIFT_TicketPost|bool $_SWIFT_TicketPostObject
     * @param array|bool $_dispatchStaffEmailContainer
     * @return int
     * @throws SWIFT_Exception
     */
    protected function processAttachments(
        $_SWIFT,
        $_SWIFT_TicketObject,
        $_SWIFT_TicketPostObject,
        $_dispatchStaffEmailContainer
    ) {
        $_attachmentCount = 0;
        $_fileTypeCache = (array) $_SWIFT->Cache->Get('filetypecache');
        $_fileTypeCacheMap = [];
        foreach ($_fileTypeCache as $_ticketFileTypeID => $_ticketFileTypeContainer) {
            $_fileTypeCacheMap[mb_strtolower($_ticketFileTypeContainer['extension'])] = $_ticketFileTypeContainer;
        }

        foreach ($this->MailParserEmail->GetAttachments() as $_attachmentContainer) {
            /*
            * BUG FIX: Parminder Singh
            *
            * SWIFT-1779: Images attach in the body section does not parse by the parser
            *
            * Comments: None
            */
            if (empty($_attachmentContainer['data']) || empty($_attachmentContainer['size']) || empty($_attachmentContainer['contenttype'])) {
                continue;
            }

            if (empty($_attachmentContainer['filename'])) {
                $_attachmentContainer['filename'] = 'attachment_' . ($_attachmentCount + 1);
            }

            /*
            * BUG FIX: Verem Dugeri <verem.dugeru@crossover.com>
            *
            * KAYAKO-4062 - If the file extension is coming blank in mail parser, it should assume it from the content type
            *
            * Comments: None
            */
            // If extension is coming blank, try to get it from content type
            if (empty($_attachmentContainer['extension'])) {
                $_attachmentContainer['extension'] = GetExtensionFromContentType($_attachmentContainer['contenttype']);
                if (!empty($_attachmentContainer['extension'])) {
                    $_attachmentContainer['filename'] .= '.' . $_attachmentContainer['extension'];
                } else if ($_SWIFT->Settings->Get('tickets_resattachments') == '1') {
                    /*
                    * When 'tickets_resattachments' setting is set as YES
                    * And
                    * Attachment extension is empty and extension can't be determined by mine type
                    * Dont accept attachment
                    */
                    continue;
                }
            }

            // Skip file extension check for attachments with no extension
            if ("" != $_attachmentContainer['extension']) {
                $_fileExtension = mb_strtolower($_attachmentContainer['extension']);

                // File extension restrictions check
                if (
                    $_SWIFT->Settings->Get('tickets_resattachments') == '1' &&
                    (!isset($_fileTypeCacheMap[$_attachmentContainer['extension']]) ||
                        ($_fileTypeCacheMap[$_fileExtension]['acceptmailparser'] == '0') ||
                        ($_fileTypeCacheMap[$_fileExtension]['maxsize'] != '0' && ($_attachmentContainer['size'] / 1024) >= $_fileTypeCacheMap[$_fileExtension]['maxsize']))
                ) {
                    continue;
                }
            }
            /*
             * BUG FIX - Pankaj Garg
             *
             * SWIFT-2099 Image is not rendered in Staff CP for emails having an embedded image in its body
             */
            $_SWIFT_AttachmentStoreObject = new SWIFT_AttachmentStoreString(
                $_attachmentContainer['filename'],
                $_attachmentContainer['contenttype'],
                $_attachmentContainer['data'],
                $_attachmentContainer['contentid']
            );

            $_SWIFT_AttachmentObject = null;
            $sha1 = $_SWIFT_AttachmentStoreObject->GetSHA1(SWIFT_Attachment::TYPE_FILE);
            $_att = [];
            if (!empty($sha1)) {
                $_attachments = SWIFT_Attachment::RetrieveBySha1($sha1, $_SWIFT_TicketObject->GetID());
                if (!empty($_attachments)) {
                    // array index is not 0 based, so take the first one using shift
                    $_att = array_shift($_attachments);
                    $_SWIFT_AttachmentObject = SWIFT_Attachment::GetOnID($_att['attachmentid']);
                }
            }

            if ($_SWIFT_AttachmentObject === null) {
                $_SWIFT_AttachmentObject = SWIFT_Attachment::CreateOnTicket(
                    $_SWIFT_TicketObject,
                    $_SWIFT_TicketPostObject,
                    $_SWIFT_AttachmentStoreObject
                );
            } else {
                // We already have this attachment on the system. We need to refer to the correct ID of the object
                // that is already stored.
                $newContent = preg_replace(
                    sprintf("#[\"|']\s*cid\s*:\s*%s\s*[\"|']#", $_SWIFT_AttachmentStoreObject->GetContentID()),
                    sprintf('"cid:%s"', $_att['contentid']),
                    $_SWIFT_TicketPostObject->Get('contents')
                );
                $_SWIFT_TicketPostObject->UpdatePool('contents', $newContent);
            }

            $_SWIFT_TicketObject->AddToNotificationAttachments(
                $_attachmentContainer['filename'],
                $_attachmentContainer['contenttype'],
                $_attachmentContainer['data'],
                $_SWIFT_AttachmentObject->GetProperty('contentid')
            );

            if (_is_array($_dispatchStaffEmailContainer)) {
                $_SWIFT_TicketObject->AddToAttachments(
                    $_attachmentContainer['filename'],
                    $_attachmentContainer['contenttype'],
                    $_attachmentContainer['data']
                );
            }

            $_attachmentCount++;
        }

        return $_attachmentCount;
    }

    /**
     * @param SWIFT_Ticket|bool $_SWIFT_TicketObject
     * @param bool $_isStaffReply
     * @param int|bool $_noTicketReply
     * @param SWIFT_TemplateGroup|bool $_SWIFT_TemplateGroupObject
     * @param array $_ticketIDContainer
     * @param int $_parserEmailCreationDate
     * @param int|bool $_staffID
     * @param int|bool $_dispatchAutoResponder
     * @param int|bool $_isPrivate
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processReplies(
        $_SWIFT_TicketObject,
        $_isStaffReply,
        $_noTicketReply,
        $_SWIFT_TemplateGroupObject,
        $_ticketIDContainer,
        $_parserEmailCreationDate,
        $_staffID,
        $_dispatchAutoResponder,
        $_isPrivate
    ) {
        $_retStatus = true;

        list(
            $_SWIFT_TicketPostObject,
            $_SWIFT_StaffObject,
            $_dispatchStaffEmailContainer,
            $_isNewTicket
        ) = [null, null, [], false];

        // Load the core caches
        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $this->Cache->Get('prioritycache');
        $_ticketTypeCache = (array) $this->Cache->Get('tickettypecache');
        $_staffCache = (array) $this->Cache->Get('staffcache');

        $_SWIFT_TicketPostObject = null;
        $_SWIFT_StaffObject = null;
        $_dispatchStaffEmailContainer = [];
        $_isNewTicket = false;
        try {
            // Is a user reply?
            if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_isStaffReply == false && $_noTicketReply == false) {
                $_SWIFT_TicketPostObject = $this->processUserReply(
                    $_SWIFT_TicketObject,
                    $_SWIFT_TemplateGroupObject,
                    $_ticketIDContainer,
                    $_parserEmailCreationDate
                );

                // Is a staff reply?
            } else {
                if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_isStaffReply == true && isset($_staffCache[$_staffID]) && $_noTicketReply == false) {
                    list($_SWIFT_StaffObject, $_dispatchStaffEmailContainer, $_isPrivate, $_SWIFT_TicketPostObject) = $this->processStaffReply(
                        $_staffCache,
                        $_staffID,
                        $_SWIFT_TicketObject,
                        $_parserEmailCreationDate
                    );

                    // Create a new ticket
                } else {
                    $_userIDFromEmail = SWIFT_UserEmail::RetrieveUserIDOnUserEmail($this->MailParserEmail->GetFromEmail());
                    $_SWIFT_UserObject = $_userID = false;

                    if (!empty($_userIDFromEmail)) {
                        try {
                            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userIDFromEmail));
                            $this->doCreateUser($_SWIFT_UserObject);
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                            throw new ReturnStatusException('return false');
                        }
                    }

                    $this->processUserNotRegistered($_SWIFT_UserObject);

                    $this->processUserNotValidated($_SWIFT_UserObject);

                    if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                        // @codeCoverageIgnoreStart
                        // this code will never be executed
                        $_userFullName = $this->MailParserEmail->GetFromName();
                        if (empty($_userFullName)) {
                            $_userFullName = $this->MailParserEmail->GetFromEmail();
                        }

                        $_SWIFT_UserObject = SWIFT_User::Create(
                            $_SWIFT_TemplateGroupObject->GetProperty('regusergroupid'),
                            false,
                            SWIFT_User::SALUTATION_NONE,
                            $_userFullName,
                            '',
                            '',
                            true,
                            false,
                            [$this->MailParserEmail->GetFromEmail()],
                            false,
                            $_SWIFT_TemplateGroupObject->GetProperty('languageid'),
                            false,
                            false,
                            false,
                            false,
                            false,
                            true,
                            true
                        );
                        // @codeCoverageIgnoreEnd
                    } else {
                        // user already exists, check if user has organization
                        $_SWIFT_UserOrganizationObject = $_SWIFT_UserObject->GetOrganization();

                        // Get organization from email domain
                        $_userOrganizationID = SWIFT_UserOrganizationEmail::GetOrganizationFromEmailList([$this->MailParserEmail->GetFromEmail()]);

                        if ($_userOrganizationID) {
                            if (!$_SWIFT_UserOrganizationObject) {
                                // assign default organization
                                $_SWIFT_UserObject->UpdateOrganization($_userOrganizationID);
                            }

                            if (!SWIFT_UserOrganizationLink::LinkExists($_userOrganizationID, $_SWIFT_UserObject->GetUserID())) {
                                // link organization found from email domain
                                SWIFT_UserOrganizationLink::Create($_SWIFT_UserObject, $_userOrganizationID);
                            }
                        }
                    }

                    $_userID = $_SWIFT_UserObject->GetUserID();
                    $_ownerStaffID = 0;
                    $_ticketStatusID = $this->EmailQueue->GetProperty('ticketstatusid');
                    $_ticketPriorityID = $this->EmailQueue->GetProperty('priorityid');
                    $_ticketTypeID = $this->EmailQueue->GetProperty('tickettypeid');
                    $_departmentID = $this->EmailQueue->GetProperty('departmentid');

                    // Now that all basic data has been checked for, check the core variables.
                    if (!isset($_departmentCache[$_departmentID])) {
                        throw new SWIFT_Exception('Invalid Department ID linked to Email Queue. Unable to create ticket.');
                    }

                    if (!isset($_ticketPriorityCache[$_ticketPriorityID])) {
                        throw new SWIFT_Exception('Invalid Ticket Priority ID linked to Email Queue. Unable to create ticket.');
                    }

                    if (!isset($_ticketTypeCache[$_ticketTypeID])) {
                        throw new SWIFT_Exception('Invalid Ticket Type ID linked to Email Queue. Unable to create ticket.');
                    }

                    if (!isset($_ticketStatusCache[$_ticketStatusID])) {
                        throw new SWIFT_Exception('Invalid Ticket Status ID linked to Email Queue. Unable to create ticket.');
                    }

                    // If autoresponde has not been blocked by rules/loop cutter, check if the email queue has autoresponder property set
                    if ($_dispatchAutoResponder) {
                        $_dispatchAutoResponder = (int)($this->EmailQueue->GetProperty('ticketautoresponder'));
                    }

                    // By now we have checked for all incoming data.. time to create a new ticket
                    list($_SWIFT_TicketObject, $_SWIFT_TicketPostObject, $_isNewTicket) = $this->processNewTicket(
                        $_ownerStaffID,
                        $_departmentID,
                        $_ticketStatusID,
                        $_ticketPriorityID,
                        $_ticketTypeID,
                        $_userID,
                        $_parserEmailCreationDate,
                        $_isPrivate
                    );
                }
            }
        } catch (ReturnStatusException $ex) {
            $_retStatus = false;
        }

        return [
            $_retStatus,
            $_SWIFT_TicketPostObject,
            $_SWIFT_StaffObject,
            $_dispatchStaffEmailContainer,
            $_isPrivate,
            $_dispatchAutoResponder,
            $_SWIFT_TicketObject,
            $_isNewTicket,
        ];
    }
}

class ReturnStatusException extends \Exception
{
}
