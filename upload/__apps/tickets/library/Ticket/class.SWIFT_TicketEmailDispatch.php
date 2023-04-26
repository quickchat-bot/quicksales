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

namespace Tickets\Library\Ticket;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\HTML\SWIFT_HTML;
use Base\Models\Attachment\SWIFT_Attachment;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_App;
use SWIFT_Mail;
use SWIFT_StringHTMLToText;
use Tickets\Models\AutoClose\SWIFT_AutoCloseRule;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_Library;
use SWIFT_Loader;
use Parser\Models\Log\SWIFT_ParserLog;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_TemplateEngine;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserOrganization;

/**
 * Handles the Ticket Email Dispatching Routines
 * Also takes care of setting up template group, loading up the language variables, recipients etc.
 *
 * @property SWIFT_Mail $Mail
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @author Varun Shoor
 */
class SWIFT_TicketEmailDispatch extends SWIFT_Library
{
    protected $Ticket = false;
    protected $_emailQueueID = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    public function __construct(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        parent::__construct();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        $this->Ticket = $_SWIFT_TicketObject;

        $this->Load->Library('Mail:Mail');
        $this->Load->Library('Language:LanguagePhraseLinked', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->_emailQueueID = (int) ($_SWIFT_TicketObject->GetProperty('emailqueueid'));

        $this->Prepare();
    }

    /**
     *
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchNotification()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Dispatch the client reply. Send to all recipients except for third party
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object
     * @param string $_contents The Reply Contents
     * @param bool $_isHTML Whether the reply contents are HTML
     * @param array $_attachmentsContainer The Attachments Container
     * @param string $_fromEmail (OPTIONAL)
     * @param bool $_isThirdParty (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchUserReply(SWIFT_User $_SWIFT_UserObject, $_contents, $_isHTML, array $_attachmentsContainer, $_fromEmail = '', $_isThirdParty = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessPostList(true);

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($this->Ticket);

        $_ccEmailList = $_bccEmailList = array();

        // CC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC] as $_ticketRecipientID => $_emailAddress)
            {
                if ($_fromEmail != $_emailAddress && !in_array($_emailAddress, $_ccEmailList))
                {
                    $_ccEmailList[] = $_emailAddress;
                }
            }
        }

        // BCC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC] as $_ticketRecipientID => $_emailAddress)
            {
                if ($_fromEmail != $_emailAddress && !in_array($_emailAddress, $_bccEmailList))
                {
                    $_bccEmailList[] = $_emailAddress;
                }
            }
        }

        $_contentContainer = SWIFT_TicketPost::RetrieveProcessedContent($_contents, $_isHTML);

        $this->Template->Assign('_userReplyText', $_contentContainer[0]);
        $this->Template->Assign('_userReplyHTML', $_contentContainer[1]);

        $_textEmailContents = $this->Template->Get('email_ticketuserreplytext', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = '<div>' . $this->Template->Get('email_ticketuserreplyhtml', SWIFT_TemplateEngine::TYPE_DB) . '</div>';

        $_destinationEmailAddress = $this->Ticket->GetProperty('email');

        if ($this->Ticket->GetProperty('replyto') != '')
        {
            $_destinationEmailAddress = $this->Ticket->GetProperty('replyto');
        }

        // Adding original user into ccEmail List if CC user has replied
        if ($_isThirdParty == false && $_fromEmail != $_destinationEmailAddress && !in_array($_destinationEmailAddress, $_ccEmailList) && !in_array($_destinationEmailAddress, $_bccEmailList))
        {
            $_ccEmailList[] = $_destinationEmailAddress;
        }

        $_fromEmailAddress = $this->Ticket->GetMailFromEmail();
        $_fromEmailAddress = $this->Ticket->RetrieveFromEmailWithSuffix($_fromEmailAddress, SWIFT_Ticket::MAIL_CLIENT);

        $this->ProcessAttachments($_attachmentsContainer);
        $this->ProcessTicketAttachments();

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1680 End user reply to the ticket is sent on staff email address
         *
         * Comments: None
         */

        // In case of a user reply, we send emails only if we have a CC or BCC recipient list
        if (_is_array($_ccEmailList) || _is_array($_bccEmailList)) {
            $this->Dispatch(SWIFT_Ticket::MAIL_CLIENT, $_htmlEmailContents, $_textEmailContents, $_SWIFT_UserObject->GetProperty('fullname'), $_fromEmailAddress, $_destinationEmailAddress, '', $_ccEmailList, $_bccEmailList, '', '', true);
        }

        return true;
    }

    /**
     * Dispatch the staff reply
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object
     * @param string $_contents The Reply Contents
     * @param bool $_isHTML Whether the reply contents are HTML
     * @param string $_overrideFromEmail (OPTIONAL) The overriden from email address
     * @param array|bool $_customSignature (OPTIONAL) If you want to override signature for replies
     * @param string $_originalSender (OPTIONAL) original sender of the email
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchStaffReply($_SWIFT_StaffObject, $_contents, $_isHTML, $_overrideFromEmail = '', $_customSignature = false, $_originalSender = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessPostList(true);

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($this->Ticket);
        $_bccEmailList = array();

        // We CC all other associated emails of the user
        $_ccEmailList = $this->Ticket->GetCCUserEmails();

        if (!_is_array($_ccEmailList))
        {
            $_ccEmailList = array();
        } else {
            foreach ($_ccEmailList as $_index => $_emailAddress) {
                if ($_emailAddress == $this->Ticket->GetProperty('oldeditemailaddress')) {
                    unset($_ccEmailList[$_index]);
                }
            }
        }

        // CC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC] as $_ticketRecipientID => $_emailAddress)
            {
                if (!in_array($_emailAddress, $_ccEmailList))
                {
                    $_ccEmailList[] = $_emailAddress;
                }
            }
        }

        // BCC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC] as $_ticketRecipientID => $_emailAddress)
            {
                $_bccEmailList[] = $_emailAddress;
            }
        }

        // Third Party
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY] as $_emailAddress)
            {
                $_bccEmailList[] = $_emailAddress;
            }
        }

        $_htmlSetting = $_SWIFT->Settings->Get('t_ochtml');
        $_htmlSettingStaff = $_SWIFT->Settings->Get('t_ochtml');

        if (!($_htmlSetting === 'entities' && $_htmlSettingStaff === 'entities')) {
            ['contents' => $_contents, 'ishtml' => $_isHTML] = SWIFT_TicketPost::addLineBreaksIfText($_contents,
                $_isHTML, $_SWIFT, true);
        }
        if ($_htmlSetting === 'entities') {
            $_contents = html_entity_decode($_contents);
            $_contentContainer = SWIFT_TicketPost::RetrieveProcessedContent($_contents, false);
        } else {
            $_contentContainer = SWIFT_TicketPost::RetrieveProcessedContent($_contents, $_isHTML);
        }

        if (_is_array($_customSignature)) {
            if (SWIFT_HTML::DetectHTMLContent($_customSignature[0])) {
                ['contents' => $_customSignature[0], 'ishtml' => $_isHTML] = SWIFT_TicketPost::addLineBreaksIfText($_customSignature[0], $_isHTML, $_SWIFT, true);
                $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();
                $_customSignature[0] = $_SWIFT_StringHTMLToTextObject->Convert($_customSignature[0]);
            }
            $_contentContainer[0] .= SWIFT_CRLF .$_customSignature[0]; // Add signature in the next line of contents for plan text
            ['contents' => $_customSignature[1], 'ishtml' => $_isHTML] = SWIFT_TicketPost::addLineBreaksIfText($_customSignature[1], $_isHTML, $_SWIFT, true);

            $_contentContainer[1] .= $_customSignature[1];
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-3877 Language translation is not working for staff replies.
         */
        $_templateGroupID           = $this->Ticket->GetProperty('tgroupid');

        if (!SWIFT_TemplateGroup::IsValidTemplateGroupID($_templateGroupID))
        {
            $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
        }

        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);

        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception('Unable to load template group object');
            // @codeCoverageIgnoreEnd
        }

        $this->Language = $this->getLanguage($_SWIFT_TemplateGroupObject);

        $_ticketDepartmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $this->Ticket->GetProperty('departmentid'));

        if (!empty($_ticketDepartmentTitleLanguage)) {
            $this->Template->Assign('_ticketDepartmentTitle', $_ticketDepartmentTitleLanguage);
        }

        $_ticketTypeTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE, $this->Ticket->GetProperty('tickettypeid'));
        if (!empty($_ticketTypeTitleLanguage)) {
            $this->Template->Assign('_ticketTypeTitle', $_ticketTypeTitleLanguage);
        }

        $_ticketStatusTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS, $this->Ticket->GetProperty('ticketstatusid'));
        if (!empty($_ticketStatusTitleLanguage)) {
            $this->Template->Assign('_ticketStatusTitle', $_ticketStatusTitleLanguage);
        }

        $_ticketPriorityTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY, $this->Ticket->GetProperty('priorityid'));
        if (!empty($_ticketPriorityTitleLanguage)) {
            $this->Template->Assign('_ticketPriorityTitle', $_ticketPriorityTitleLanguage);
        }

        $this->Template->Assign('_staffReplyText', $_contentContainer[0]);
        $this->Template->Assign('_staffReplyHTML', $_contentContainer[1]);

        $_textEmailContents = $this->Template->Get('email_ticketstaffreplytext', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = '<div>' . $this->Template->Get('email_ticketstaffreplyhtml', SWIFT_TemplateEngine::TYPE_DB) . '</div>';

        $_destinationEmailAddress = $this->Ticket->GetProperty('email');
        if ($this->Ticket->GetProperty('replyto') != '')
        {
            $_destinationEmailAddress = $this->Ticket->GetProperty('replyto');
        }
        if (!empty($_originalSender) && !empty($_destinationEmailAddress)
	        && trim(strtoupper($_originalSender)) === trim(strtoupper($_destinationEmailAddress))) {
        	$_destinationEmailAddress = '';
        }

        $_fromEmailAddress = $this->Ticket->GetMailFromEmail();
        if (!empty($_overrideFromEmail))
        {
            $_fromEmailAddress = $_overrideFromEmail;
        }

        $_fromEmailAddress = $this->Ticket->RetrieveFromEmailWithSuffix($_fromEmailAddress, SWIFT_Ticket::MAIL_CLIENT);

        $this->ProcessPostAttachments('replyattachments');
        $this->ProcessPostAttachments('newticketattachments');
        $this->ProcessTicketAttachments();

        $this->Dispatch(SWIFT_Ticket::MAIL_CLIENT, $_htmlEmailContents, $_textEmailContents, $this->Ticket->GetMailFromName($_SWIFT_StaffObject),
                $_fromEmailAddress, $_destinationEmailAddress, '', $_ccEmailList, $_bccEmailList, '', '');

        return true;
    }

    /**
     * Dispatch the forward reply
     *
     * @author Varun Shoor
     * @param string $_destinationEmailAddress The Destination Email Address
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object
     * @param string $_contents The Reply Contents
     * @param bool $_isHTML Whether the reply contents are HTML
     * @param string $_overrideFromEmail (OPTIONAL) The overriden from email address
     * @param array|bool $_customSignature (OPTIONAL) If you want to override signature for replies
     * @param string $_customSubject
     * @param bool $_isSendToCcBcc (OPTIONAL) Whether to send email to CC or BCC email addresses
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     * @throws SWIFT_Ticket_Exception
     */
    public function DispatchForwardReply($_destinationEmailAddress, $_SWIFT_StaffObject, $_contents, $_isHTML, $_overrideFromEmail = '', $_customSignature = false, $_customSubject = '', $_isSendToCcBcc = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessPostList();

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($this->Ticket);

        $_ccEmailList = $_bccEmailList = array();

        /**
         * Bug Fix : Saloni Dhall
         *
         * SWIFT-2440 : Help desk should not send follow up emails to the CC users of a ticket.
         *
         */
        // CC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && $_isSendToCcBcc == true)
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC] as $_ticketRecipientID => $_emailAddress)
            {
                $_ccEmailList[] = $_emailAddress;
            }
        }

        // BCC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && $_isSendToCcBcc == true)
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC] as $_ticketRecipientID => $_emailAddress)
            {
                $_bccEmailList[] = $_emailAddress;
            }
        }

        $_contentContainer = SWIFT_TicketPost::RetrieveProcessedContent($_contents, $_isHTML);

        if (_is_array($_customSignature)) {
            $_contentContainer[0] .= $_customSignature[0];
            $_contentContainer[1] .= $_customSignature[1];
        }

        $this->Template->Assign('_forwardReplyText', $_contentContainer[0]);
        $this->Template->Assign('_forwardReplyHTML', $_contentContainer[1]);

        $_textEmailContents = $this->Template->Get('email_ticketforwardtext', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = '<div>' . $this->Template->Get('email_ticketforwardhtml', SWIFT_TemplateEngine::TYPE_DB) . '</div>';

        $_fromEmailAddress = $this->Ticket->GetMailFromEmail();
        if (!empty($_overrideFromEmail))
        {
            $_fromEmailAddress = $_overrideFromEmail;
        }
        $_fromEmailAddress = $this->Ticket->RetrieveFromEmailWithSuffix($_fromEmailAddress, SWIFT_Ticket::MAIL_THIRDPARTY);

        $this->ProcessPostAttachments('forwardattachments');
        $this->ProcessTicketAttachments();

        /*
        * BUG FIX - Ravi Sharma
        *
        * SWIFT-3402 Edit Subject Field option should be available on ticket forwarding page.
        */
        $this->Dispatch(SWIFT_Ticket::MAIL_THIRDPARTY, $_htmlEmailContents, $_textEmailContents, $this->Ticket->GetMailFromName($_SWIFT_StaffObject), $_fromEmailAddress, $_destinationEmailAddress, '', $_ccEmailList, $_bccEmailList, '', $_customSubject);

        return true;
    }

    /**
     * Dispatch the Ticket Autoresponder
     *
     * @author Varun Shoor
     * @param string $_overrideEmailAddress (OPTIONAL) Override the Destination Email Address with the one specified
     * @param array $_ccEmailList (OPTIONAL) The CC Email List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchAutoresponder($_overrideEmailAddress = '', $_ccEmailList = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Ashish Kataria, Simaranjit Singh
         *
         * SWIFT-555 Latest news are not reflecting in autoresponders
         *
         * Comments - None
         */
        if (SWIFT_App::IsInstalled(APP_NEWS))
        {
            $_maxNewsItems = $this->Settings->Get('nw_maxnewslist');

            $_maxNewsItems = IIF(empty($_maxNewsItems), 5, (int) ($this->Settings->Get('nw_maxnewslist')));

            $this->Load->Model("NewsItem:NewsItem", [], false, false, APP_NEWS);

            $_templateGroupID = $this->Ticket->GetProperty('tgroupid');

            if (!SWIFT_TemplateGroup::IsValidTemplateGroupID($_templateGroupID))
            {
                $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
            }

            $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);

            if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception('Unable to load template group object');
                // @codeCoverageIgnoreEnd
            }

            $_userGroupID = $_SWIFT_TemplateGroupObject->GetProperty('guestusergroupid');

            $_newsContainer = SWIFT_NewsItem::Retrieve($_maxNewsItems, 0, array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC), $_userGroupID);

            $this->Template->Assign('_newsContainer', $_newsContainer);
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1315 Issue Autoresponder Email doesn't use translation for Types / Statuses / Priorities specified under the 'Languages: Translation' option
         *
         */
        if ($this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT || $this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CRON || $this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE) {
            // Re-assign the ticket department, type, status, priority according to the Languages:Translation
            $this->Language = $this->getLanguageEngine();

            $_ticketDepartmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $this->Ticket->GetProperty('departmentid'));
            if (!empty($_ticketDepartmentTitleLanguage)) {
                $this->Template->Assign('_ticketDepartmentTitle', $_ticketDepartmentTitleLanguage);
            }

            $_ticketTypeTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE, $this->Ticket->GetProperty('tickettypeid'));
            if (!empty($_ticketTypeTitleLanguage)) {
                $this->Template->Assign('_ticketTypeTitle', $_ticketTypeTitleLanguage);
            }

            $_ticketStatusTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS, $this->Ticket->GetProperty('ticketstatusid'));
            if (!empty($_ticketStatusTitleLanguage)) {
                $this->Template->Assign('_ticketStatusTitle', $_ticketStatusTitleLanguage);
            }

            $_ticketPriorityTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY, $this->Ticket->GetProperty('priorityid'));
            if (!empty($_ticketPriorityTitleLanguage)) {
                $this->Template->Assign('_ticketPriorityTitle', $_ticketPriorityTitleLanguage);
            }
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-1337 Include ticket contents (opening ticket reply) to autoresponder emails
         *
         * Comments: Getting the post content and passing it to template.
         */
        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($this->Ticket->GetProperty('lastpostid')));

        $_contentHTML = $_SWIFT_TicketPostObject->GetDisplayContents();
        $this->Template->Assign('_ticketContent', $_contentHTML);
        $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();
        $_contentText = $_SWIFT_StringHTMLToTextObject->Convert($_contentHTML);
        $this->Template->Assign('_ticketContentText', $_contentText);

        $_textEmailContents = $this->Template->Get('email_ticketautorespondertext', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_ticketautoresponderhtml', SWIFT_TemplateEngine::TYPE_DB);

        $_htmlEmailContents = $this->Emoji->decode($_htmlEmailContents);

        $_destinationEmailAddress = $this->Ticket->GetProperty('email');
        if (!empty($_overrideEmailAddress))
        {
            $_destinationEmailAddress = $_overrideEmailAddress;
        } else if ($this->Ticket->GetProperty('replyto') != '') {
            $_destinationEmailAddress = $this->Ticket->GetProperty('replyto');
        }

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($this->Ticket);

        if (!is_array($_ccEmailList))
        {
            $_ccEmailList = array();
        }

        $_bccEmailList = array();

        // CC
        if ($this->Settings->Get('t_autorespondercc') == '1' && isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC] as $_ticketRecipientID => $_emailAddress)
            {
                if (!in_array($_emailAddress, $_ccEmailList))
                {
                    $_ccEmailList[] = $_emailAddress;
                }
            }
        }

        // BCC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC] as $_ticketRecipientID => $_emailAddress)
            {
                $_bccEmailList[] = $_emailAddress;
            }
        }
        $this->ProcessTicketAttachments();

        $this->Dispatch(SWIFT_Ticket::MAIL_CLIENT, $_htmlEmailContents, $_textEmailContents, $this->Ticket->GetMailFromName(),
                $this->Ticket->RetrieveFromEmailWithSuffix($this->Ticket->GetMailFromEmail(), SWIFT_Ticket::MAIL_CLIENT), $_destinationEmailAddress, '', $_ccEmailList, $_bccEmailList, '', '');

        return true;
    }

    /**
     * Dispatch the Pending Auto Close Email
     *
     * @author Varun Shoor
     * @param SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject
     * @param string $_overrideEmailAddress (OPTIONAL) Override the Destination Email Address with the one specified
     * @param array $_ccEmailList (OPTIONAL) The CC Email List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchPendingAutoClose(SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject, $_overrideEmailAddress = '', $_ccEmailList = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupID = $this->Ticket->GetProperty('tgroupid');

        if (!SWIFT_TemplateGroup::IsValidTemplateGroupID($_templateGroupID)) {
            $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
        }

        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);

        $this->Language = $this->getLanguage($_SWIFT_TemplateGroupObject);

        $_languageCache = $this->Cache->Get('languagecache');
        $_User = $this->Ticket->GetUserObject();
        if ($_User instanceof SWIFT_User && isset($_languageCache[$_User->GetProperty('languageid')])) {
            $_userLanguageID = $_User->GetProperty('languageid');
            $_userLanguageCode = $_languageCache[$_userLanguageID]['languagecode'];
            $this->Language->SetLanguageID($_userLanguageID);
            $this->Language->SetLanguageCode($_userLanguageCode);
        }

        $this->Language->Load('ticketemails', SWIFT_LanguageEngine::TYPE_DB);

        $_pendingIntro = sprintf($this->Language->Get('acpendingintro'), $this->Ticket->GetTicketDisplayID(), $_SWIFT_AutoCloseRuleObject->GetProperty('inactivitythreshold'));
        $_pendingSubFooter = sprintf($this->Language->Get('acpendingsubfooter'), $_SWIFT_AutoCloseRuleObject->GetProperty('closurethreshold'));

        $this->Template->Assign('_autoClosePendingIntro', $_pendingIntro);
        $this->Template->Assign('_autoClosePendingSubFooter', $_pendingSubFooter);

        $_textEmailContents = $this->Template->Get('email_ticketautoclosependingtext', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_ticketautoclosependinghtml', SWIFT_TemplateEngine::TYPE_DB);

        $_destinationEmailAddress = $this->Ticket->GetProperty('email');
        if (!empty($_overrideEmailAddress))
        {
            $_destinationEmailAddress = $_overrideEmailAddress;
        } else if ($this->Ticket->GetProperty('replyto') != '') {
            $_destinationEmailAddress = $this->Ticket->GetProperty('replyto');
        }

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($this->Ticket);

        if (!is_array($_ccEmailList))
        {
            $_ccEmailList = array();
        }

        $_bccEmailList = array();

        // CC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC] as $_ticketRecipientID => $_emailAddress)
            {
                if (!in_array($_emailAddress, $_ccEmailList))
                {
                    $_ccEmailList[] = $_emailAddress;
                }
            }
        }

        // BCC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC] as $_ticketRecipientID => $_emailAddress)
            {
                $_bccEmailList[] = $_emailAddress;
            }
        }

        $this->Dispatch(SWIFT_Ticket::MAIL_CLIENT, $_htmlEmailContents, $_textEmailContents, $this->Ticket->GetMailFromName(),
                $this->Ticket->RetrieveFromEmailWithSuffix($this->Ticket->GetMailFromEmail(), SWIFT_Ticket::MAIL_CLIENT), $_destinationEmailAddress, '', $_ccEmailList, $_bccEmailList, '', '');

        return true;
    }

    /**
     * Dispatch the Final Auto Close Email
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>

     * @param SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject
     * @param string $_overrideEmailAddress (OPTIONAL) Override the Destination Email Address with the one specified
     * @param array $_ccEmailList (OPTIONAL) The CC Email List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchFinalAutoClose(SWIFT_AutoCloseRule $_SWIFT_AutoCloseRuleObject, $_overrideEmailAddress = '', $_ccEmailList = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupID = $this->Ticket->GetProperty('tgroupid');

        if (!SWIFT_TemplateGroup::IsValidTemplateGroupID($_templateGroupID)) {
            $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
        }

        $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup($_templateGroupID);

        $this->Language = $this->getLanguage($_SWIFT_TemplateGroupObject);

        $_ticketStatusTitle = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS, $_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid'));

        $_languageCache = $this->Cache->Get('languagecache');
        $_User = $this->Ticket->GetUserObject();
        if ($_User instanceof SWIFT_User && isset($_languageCache[$_User->GetProperty('languageid')])) {
            $_userLanguageID = $_User->GetProperty('languageid');
            $_userLanguageCode = $_languageCache[$_userLanguageID]['languagecode'];
            $this->Language->SetLanguageID($_userLanguageID);
            $this->Language->SetLanguageCode($_userLanguageCode);
            $_translatedStatusTitle = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS, $_SWIFT_AutoCloseRuleObject->GetProperty('targetticketstatusid'));
            $_ticketStatusTitle = !empty($_translatedStatusTitle) ? $_translatedStatusTitle : $_ticketStatusTitle;
        }

        $this->Language->Load('ticketemails', SWIFT_LanguageEngine::TYPE_DB);

        $_finalIntro = sprintf($this->Language->Get('acfinalintro'), $this->Ticket->GetTicketDisplayID(), $_ticketStatusTitle, $_SWIFT_AutoCloseRuleObject->GetProperty('closurethreshold'));
        $_finalSubFooter = sprintf($this->Language->Get('acfinalsubfooter'));

        $this->Template->Assign('_autoCloseFinalIntro', $_finalIntro);
        $this->Template->Assign('_autoCloseFinalSubFooter', $_finalSubFooter);

        $_textEmailContents = $this->Template->Get('email_ticketautoclosefinaltext', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_ticketautoclosefinalhtml', SWIFT_TemplateEngine::TYPE_DB);

        $_destinationEmailAddress = $this->Ticket->GetProperty('email');
        if (!empty($_overrideEmailAddress))
        {
            $_destinationEmailAddress = $_overrideEmailAddress;
        } else if ($this->Ticket->GetProperty('replyto') != '') {
            $_destinationEmailAddress = $this->Ticket->GetProperty('replyto');
        }

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($this->Ticket);

        if (!is_array($_ccEmailList))
        {
            $_ccEmailList = array();
        }

        $_bccEmailList = array();

        // CC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC] as $_ticketRecipientID => $_emailAddress)
            {
                if (!in_array($_emailAddress, $_ccEmailList))
                {
                    $_ccEmailList[] = $_emailAddress;
                }
            }
        }

        // BCC
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]) && _is_array($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC]))
        {
            foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC] as $_ticketRecipientID => $_emailAddress)
            {
                $_bccEmailList[] = $_emailAddress;
            }
        }

        $this->Dispatch(SWIFT_Ticket::MAIL_CLIENT, $_htmlEmailContents, $_textEmailContents, $this->Ticket->GetMailFromName(),
                $this->Ticket->RetrieveFromEmailWithSuffix($this->Ticket->GetMailFromEmail(), SWIFT_Ticket::MAIL_CLIENT), $_destinationEmailAddress, '', $_ccEmailList, $_bccEmailList, '', '');

        return true;
    }

    /**
     * Dispatch the Ticket Survey
     *
     * @author Varun Shoor
     * @param string $_overrideEmailAddress (OPTIONAL) Override the Destination Email Address with the one specified
     * @param array $_ccEmailList (OPTIONAL) The CC Email List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchSurvey($_overrideEmailAddress = '', $_ccEmailList = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_textEmailContents = $this->Template->Get('email_surveytext', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_surveyhtml', SWIFT_TemplateEngine::TYPE_DB);

        $_destinationEmailAddress = $this->Ticket->GetProperty('email');
        if (!empty($_overrideEmailAddress))
        {
            $_destinationEmailAddress = $_overrideEmailAddress;
        } else if ($this->Ticket->GetProperty('replyto') != '') {
            $_destinationEmailAddress = $this->Ticket->GetProperty('replyto');
        }

        /**
         * BUG FIX - Parminder Singh
         *
         * SWIFT-1986: There should be a setting to enable/disable for "should send survey emails to CC users"
         *
         * Comments: System should not send survey email to cc/ bcc users.
         */

        $this->ProcessTicketAttachments();

        $this->Dispatch(SWIFT_Ticket::MAIL_CLIENT, $_htmlEmailContents, $_textEmailContents, $this->Ticket->GetMailFromName(),
            $this->Ticket->RetrieveFromEmailWithSuffix($this->Ticket->GetMailFromEmail(), SWIFT_Ticket::MAIL_CLIENT), $_destinationEmailAddress, '', $_ccEmailList);

        return true;
    }

    /**
     * Processes the attachment array and adds the attachments to the mail
     *
     * @author Varun Shoor
     * @param array $_attachmentsContainer The Attachments Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function ProcessAttachments($_attachmentsContainer) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!_is_array($_attachmentsContainer))
        {
            return false;
        }

        foreach ($_attachmentsContainer as $_attachmentContainer) {
            if (empty($_attachmentContainer['data']) || empty($_attachmentContainer['size']) || empty($_attachmentContainer['filename']) ||
                    empty($_attachmentContainer['extension']) || empty($_attachmentContainer['contenttype']))
            {
                continue;
            }

            $this->Mail->Attach($_attachmentContainer['data'], $_attachmentContainer['contenttype'], $_attachmentContainer['filename']);
        }

        return true;
    }

    /**
     * Processes the Attachments linked with Ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function ProcessTicketAttachments() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_ticketAttachments = $this->Ticket->GetAttachments();
        if (!_is_array($_ticketAttachments))
        {
            return false;
        }

        foreach ($_ticketAttachments as $_attachment)
        {
            $this->Mail->Attach($_attachment['contents'], $_attachment['type'], $_attachment['name']);
        }

        return true;
    }

    /**
     * Processes the POST attachment field (ticketattachments) and adds the attachments to the mail
     *
     * @author Varun Shoor
     * @param string $_fieldName The Custom Field Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function ProcessPostAttachments($_fieldName) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_finalFieldName = 'ticketattachments';
        if (!empty($_fieldName))
        {
            $_finalFieldName = $_fieldName;
        }

        if (!isset($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]['name'])) {
            return false;
        }

        // Create the attachments
        $_attachmentCount = 0;
        foreach ($_FILES[$_finalFieldName]['name'] as $_fileIndex => $_fileName) {
            if (empty($_fileName) || empty($_FILES[$_finalFieldName]['type'][$_fileIndex]) || empty($_FILES[$_finalFieldName]['size'][$_fileIndex]) ||
                    !is_uploaded_file($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex]))
            {
                continue;
            }

            $this->Mail->Attach(file_get_contents($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex]), $_FILES[$_finalFieldName]['type'][$_fileIndex],
                    $_fileName);

            $_attachmentCount++;
        }

        return true;
    }

    /**
     * Processes the Post List Variable and Assigns it if needed
     *
     * @author Varun Shoor
     * @param bool $_isClient (OPTIONAL) Is client reply
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessPostList($_isClient = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('t_postlist') == 1 || $this->Settings->Get('t_enhistory') == 1)
        {
            $_ticketPostObjectContainer = $this->Ticket->GetTicketPosts(0, false,
                    IIF($this->Settings->Get('t_cpostorder') == 'asc', 'ASC', 'DESC'), false);

            $_ticketPostDataContainer = array();

            foreach ($_ticketPostObjectContainer as $_ticketPostID => $_SWIFT_TicketPostObject)
            {
                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1622 Third party replies should not be sent to the user / creator in ticket history
                 *
                 * Comments: None
                 */
                /*
                 * BUG FIX - Mahesh Salaria
                 *
                 * SWIFT-2919 Forwarded ticket contains Private messages on enabling 'Send Complete History in Staff Replies?' option
                 *
                 * Comments: No need to send private replies in any case.
                 */
                if ((($_isClient)
                        && ($_SWIFT_TicketPostObject->GetProperty('isthirdparty') == '1' || $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_THIRDPARTY))
                        || $_SWIFT_TicketPostObject->GetProperty('isprivate') == '1')
                {    // Do not display the third party and private replies to client
                    continue;
                }

                $_ticketPostContents = $this->Emoji->decode($_SWIFT_TicketPostObject->GetProperty('contents'));

                $_ticketPostDataContainer[$_ticketPostID]['ticketpostid'] = $_ticketPostID;
                $_ticketPostDataContainer[$_ticketPostID]['contents'] = SWIFT_TicketPost::RetrieveProcessedContent($_ticketPostContents,
                        SWIFT_HTML::DetectHTMLContent($_ticketPostContents));
                $_ticketPostDataContainer[$_ticketPostID]['fullname'] = $_SWIFT_TicketPostObject->GetProperty('fullname');
                $_ticketPostDataContainer[$_ticketPostID]['date'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketPostObject->GetProperty('dateline'));

                $_creatorLabel = $this->Language->Get('thclient');
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF)
                {
                    $_creatorLabel = $this->Language->Get('thstaff');
                } else if ($_SWIFT_TicketPostObject->GetProperty('isthirdparty') == '1' || $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_THIRDPARTY) {
                    $_creatorLabel = $this->Language->Get('ththirdparty');
                } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_CC) {
                    $_creatorLabel = $this->Language->Get('thcc');
                } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_BCC) {
                    $_creatorLabel = $this->Language->Get('thbcc');
                }

                $_ticketPostDataContainer[$_ticketPostID]['creator'] = $_creatorLabel;
            }

            $this->Template->Assign('_ticketPostList', $_ticketPostDataContainer);
            if(count($_ticketPostDataContainer) == 1) {
                $this->Template->Assign('_isNewTicket', true);
            }else {
                $this->Template->Assign('_isNewTicket', false);
            }
        }


        return true;
    }

    /**
     * Prepare the dispatch routines by loading the variables, templates etc.
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Prepare()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4548 Survey email is always dispatched in English, even if other language is linked to template group being used.
         */
        $_templateGroupCache = (array) $_SWIFT->Cache->Get('templategroupcache');

        /** @var array $_templateGroupContainer */
        foreach ($_templateGroupCache as $_templateGroupContainer) {
            if ($_templateGroupContainer['tgroupid'] == $this->Ticket->GetProperty('tgroupid')) {
                $this->Template->SetTemplateGroupID($_templateGroupContainer['tgroupid']);
                $_SWIFT->Template->SetTemplateGroupPrefix($_templateGroupContainer['tgroupid']);

                $_languageCache = $_SWIFT->Cache->Get('languagecache');

                if ($_templateGroupContainer['languageid'] != '0' && isset($_languageCache[$_templateGroupContainer['languageid']]) && $_SWIFT->Language->GetLanguageID() != $_templateGroupContainer['languageid']) {
                    $_SWIFT->Language->SetLanguageID($_templateGroupContainer['languageid']);
                    $_SWIFT->Language->SetLanguageCode($_languageCache[$_templateGroupContainer['languageid']]['languagecode']);
                }
                break;
            }
        }

        // Load the phrases from the database..
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('ticketsmain', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('ticketemails', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $_SWIFT = SWIFT::GetInstance();

        // user language preference overrides grouptemplate
        if ($this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
            $user = $this->Ticket->GetUserObject();
            $_languageCache = $this->Cache->Get('languagecache');
            if (isset($_languageCache[$user->GetProperty('languageid')]) && $_SWIFT->Language->GetLanguageID() != $user->GetProperty('languageid')) {
                $_languageCode = $_languageCache[$user->GetProperty('languageid')]['languagecode'];
                $_SWIFT = SWIFT::GetInstance();
                $_SWIFT->Language->SetLanguageID($user->GetProperty('languageid'));
                $_SWIFT->Language->SetLanguageCode($_languageCode);
                $_SWIFT->Language->Load('ticketemails', SWIFT_LanguageEngine::TYPE_DB);
            }
        } elseif ($this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT || $this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CRON || $this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE) {
            // Process autoresponder in user prefered language
            $this->Language = $this->getLanguageEngine();
            $_SWIFT->Language->SetLanguageID($this->Language->GetLanguageID());
            $_SWIFT->Language->SetLanguageCode($this->Language->GetLanguageCode());
            $_SWIFT->Language->Load('ticketemails', SWIFT_LanguageEngine::TYPE_DB);
        }

            // Load the template variables
        $this->LoadTemplateVariables();

        return true;
    }

    /**
     * Load the relevant template variables
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadTemplateVariables()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_emailQueueCache = $this->Cache->Get('queuecache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        $_staffCache = $this->Cache->Get('staffcache');

        $_ticketDataStore = $this->Ticket->GetDataStore();
        $this->Template->Assign('_ticket', $_ticketDataStore);

        if (isset($_templateGroupCache[$_ticketDataStore['tgroupid']]))
        {
            $this->Template->SetTemplateGroupPrefix($_ticketDataStore['tgroupid']);
        }

        $this->Template->Assign('_ticketPriorityTitle', '');
        $this->Template->Assign('_ticketStatusTitle', '');
        $this->Template->Assign('_ticketTypeTitle', '');
        $this->Template->Assign('_ticketDepartmentTitle', '');
        $this->Template->Assign('_ticketEmailQueueEmail', '');
        $this->Template->Assign('_ticketTemplateGroupTitle', '');


        // Priority
        if (isset($_ticketPriorityCache[$_ticketDataStore['priorityid']]))
        {
            $this->Template->Assign('_ticketPriority', $_ticketPriorityCache[$_ticketDataStore['priorityid']]);
            $this->Template->Assign('_ticketPriorityTitle', $_ticketPriorityCache[$_ticketDataStore['priorityid']]['title']);
        }

        // Status
        if (isset($_ticketStatusCache[$_ticketDataStore['ticketstatusid']]))
        {
            $this->Template->Assign('_ticketStatus', $_ticketStatusCache[$_ticketDataStore['ticketstatusid']]);
            $this->Template->Assign('_ticketStatusTitle', $_ticketStatusCache[$_ticketDataStore['ticketstatusid']]['title']);
        }

        /**
         * FEATURE - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-3186 Custom field data in autoresponders, ticket notifications.
         */
        // Custom Fields
        $_customFields = $this->CustomFieldManager->GetCustomFieldValue($this->Ticket->GetProperty('ticketid'));

        $this->Template->Assign('_customFields', $_customFields);

        // Type
        if (isset($_ticketTypeCache[$_ticketDataStore['tickettypeid']]))
        {
            $this->Template->Assign('_ticketType', $_ticketTypeCache[$_ticketDataStore['tickettypeid']]);
            $this->Template->Assign('_ticketTypeTitle', $_ticketTypeCache[$_ticketDataStore['tickettypeid']]['title']);
        }

        // Department
        if (isset($_departmentCache[$_ticketDataStore['departmentid']]))
        {
            $this->Template->Assign('_ticketDepartment', $_departmentCache[$_ticketDataStore['departmentid']]);
            $this->Template->Assign('_ticketDepartmentTitle', $_departmentCache[$_ticketDataStore['departmentid']]['title']);
        }

        // Owner
        if (isset($_staffCache[$_ticketDataStore['ownerstaffid']]))
        {
            $this->Template->Assign('_ticketOwner', $_staffCache[$_ticketDataStore['ownerstaffid']]);
            $this->Template->Assign('_ticketOwnerFullName', $_staffCache[$_ticketDataStore['ownerstaffid']]['fullname']);
        } else if ($_ticketDataStore['ownerstaffid'] == '0') {
            $this->Template->Assign('_ticketOwnerFullName', $this->Language->Get('unassigned'));
        }

        // Email Queue
        if (isset($_emailQueueCache[$_ticketDataStore['emailqueueid']]))
        {
            $this->Template->Assign('_ticketEmailQueue', $_emailQueueCache[$_ticketDataStore['emailqueueid']]);
            $this->Template->Assign('_ticketEmailQueueEmail', $_emailQueueCache[$_ticketDataStore['emailqueueid']]['email']);
        }

        // Template Group
        if (isset($_templateGroupCache[$_ticketDataStore['tgroupid']]))
        {
            $this->Template->Assign('_ticketTemplateGroup', $_templateGroupCache[$_ticketDataStore['tgroupid']]);
            $this->Template->Assign('_ticketTemplateGroupTitle', $_templateGroupCache[$_ticketDataStore['tgroupid']]['title']);
        }

        $this->Template->Assign('_ticketDueDate', '');
        $this->Template->Assign('_ticketResolutionDueDate', '');

        // Dates
        if ($this->Ticket->GetProperty('duetime') > DATENOW)
        {
            $this->Template->Assign('_ticketDueDate', $this->Language->Get('overdue'));
        } else if ($this->Ticket->GetProperty('duetime') != '0') {
            $this->Template->Assign('_ticketDueDate', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Ticket->GetProperty('duetime')));
        }

        if ($this->Ticket->GetProperty('resolutionduedateline') > DATENOW)
        {
            $this->Template->Assign('_ticketResolutionDueDate', $this->Language->Get('overdue'));
        } else if ($this->Ticket->GetProperty('resolutionduedateline') != '0') {
            $this->Template->Assign('_ticketResolutionDueDate', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Ticket->GetProperty('resolutionduedateline')));
        }

        $this->Template->Assign('_ticketCreationDate', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Ticket->GetProperty('dateline')));
        $this->Template->Assign('_ticketUpdateDate', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Ticket->GetProperty('lastactivity')));

        // User
        $this->Template->Assign('_ticketUser', '');
        $this->Template->Assign('_ticketUserFullName', '');
        $_SWIFT_UserObject = $this->Ticket->GetUserObject();
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded())
        {
            $this->Template->Assign('_ticketUser', $_SWIFT_UserObject->GetDataStore());
            $this->Template->Assign('_ticketUserFullName', $_SWIFT_UserObject->GetProperty('fullname'));
        }

        $this->Template->Assign('_ticketUserOrganization', '');
        $this->Template->Assign('_ticketUserOrganizationTitle', '');
        // User Organization
        $_SWIFT_UserOrganizationObject = $this->Ticket->GetUserOrganizationObject();
        if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded())
        {
            $this->Template->Assign('_ticketUserOrganization', $_SWIFT_UserOrganizationObject->GetDataStore());
            $this->Template->Assign('_ticketUserOrganizationTitle', $_SWIFT_UserOrganizationObject->GetProperty('organizationname'));
        }

        $this->Template->Assign('_ticketUserGroup', '');
        $this->Template->Assign('_ticketUserGroupTitle', '');
        // User Group
        $_SWIFT_UserGroupObject = $this->Ticket->GetUserGroupObject();
        if ($_SWIFT_UserGroupObject instanceof SWIFT_UserGroup && $_SWIFT_UserGroupObject->GetIsClassLoaded())
        {
            $this->Template->Assign('_ticketUserGroup', $_SWIFT_UserGroupObject->GetDataStore());
            $this->Template->Assign('_ticketUserGroupTitle', $_SWIFT_UserGroupObject->GetProperty('title'));
        }

        // Signature
        $this->Template->Assign('_ticketSignatureText', $this->Ticket->GetSignature(false));
        $this->Template->Assign('_ticketSignatureHTML', $this->Ticket->GetSignature(true));

        return true;
    }

    /**
     * Dispatch a ticket email
     *
     * @author Varun Shoor
     * @param mixed $_mailType The Mail Type. This decides the subject prefix
     * @param string $_contentsHTML The HTML Contents
     * @param string $_contentsText The Text Contents
     * @param string $_fromName The From Name
     * @param string $_fromEmail The From Email Address
     * @param string $_toEmail The Destination Email Address
     * @param string $_toName (OPTIONAL) The Destination Name
     * @param array $_ccEmailList (OPTIONAL) The CC User Email List
     * @param array $_bccEmailList (OPTIONAL) The BCC User Email List
     * @param string $_messageID (OPTIONAL) The Message ID
     * @param string $_customSubject (OPTIONAL) The Custom Email Subject
     * @param bool $_sendSeparateEmails (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Dispatch($_mailType, $_contentsHTML, $_contentsText, $_fromName, $_fromEmail, $_toEmail, $_toName = '',
            $_ccEmailList = array(), $_bccEmailList = array(), $_messageID = '', $_customSubject = '', $_sendSeparateEmails = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // We dont send ANY email if loop control is triggered
        if (SWIFT::Get('loopcontrol') == true)
        {
            return false;
        }

        $_emailQueueCache = $this->Cache->Get('queuecache');

        $_toEmail = mb_strtolower(trim($_toEmail));

        /**
         * Feature : Mansi Wason<mansi.wason@opencart.com.vn>
         *
         * SWIFT-3949 : Email conversation thread breaks in Gmail
         */
        SWIFT_Loader::LoadModel('Log:ParserLog', APP_PARSER);
        $_messageID = SWIFT_ParserLog::RetrieveMessageID($this->Ticket->GetTicketID());

        $this->Mail->SetReferences($_messageID);
        $this->Mail->SetFromField($_fromEmail, $_fromName);

        // Reject if destination email is set as email queue
        if (isset($_emailQueueCache['pointer'][$_toEmail]))
        {
            return false;
        }

//        echo 'TO: ' . $_toEmail . SWIFT_CRLF;

	    $_receivedAddress = array();
	    if (!$_sendSeparateEmails && !empty($_toEmail)) {
            $_receivedAddress[] = $_toEmail;
            $this->Mail->SetToField($_toEmail, $_toName);
        }

        $_ignoreCCEmailList = SWIFT::Get('_ignoreCCEmail');
        $_ignoreBCCEmailList = SWIFT::Get('_ignoreBCCEmail');

        if (!is_array($_ignoreCCEmailList))
        {
            $_ignoreCCEmailList = array();
        }

        if (!is_array($_ignoreBCCEmailList))
        {
            $_ignoreBCCEmailList = array();
        }

        $_combinedEmailList = array();

//        print_r($_ccEmailList);

        if (_is_array($_ccEmailList))
        {
            foreach ($_ccEmailList as $_emailAddress)
            {
                $_emailAddress = mb_strtolower(trim($_emailAddress));

                // We dont CC the person we are sending the email to
                if ($_emailAddress == $_toEmail && !$_sendSeparateEmails)
                {
                    continue;
                } else if (in_array($_emailAddress, $_ignoreCCEmailList)) {
                    continue;

                // Reject if recipient added as email queue
                } else if (isset($_emailQueueCache['pointer'][$_emailAddress])) {
                    continue;
                }

//                echo 'CC: ' . $_emailAddress . SWIFT_CRLF;

                if (!$_sendSeparateEmails) {
                    $this->Mail->AddCC($_emailAddress);
                } else {
                    $_combinedEmailList[] = $_emailAddress;
                }
                $_receivedAddress[] = $_emailAddress;
            }
        }

        if (_is_array($_bccEmailList))
        {
            foreach ($_bccEmailList as $_emailAddress)
            {
                $_emailAddress = mb_strtolower(trim($_emailAddress));

                // We dont BCC the person we are sending the email to
                if ($_emailAddress == $_toEmail)
                {
                    continue;
                } else if (in_array($_emailAddress, $_ignoreBCCEmailList)) {
                    continue;

                // Reject if recipient added as email queue
                } else if (isset($_emailQueueCache['pointer'][$_emailAddress])) {
                    continue;
                }

//                echo 'BCC: ' . $_emailAddress . SWIFT_CRLF;

                if (!$_sendSeparateEmails) {
                    $this->Mail->AddBCC($_emailAddress);
                } else {
                    $_combinedEmailList[] = $_emailAddress;
                }
                $_receivedAddress[] = $_emailAddress;
            }
        }

        if (empty($_receivedAddress)) {
            return false;
        }
        $this->Mail->SetSubjectField($this->Ticket->GetMailSubject($_mailType, $_customSubject));

        // force add part so clients like gmail give preference to HTML
        $this->Mail->SetDataText($_contentsText, true);
        $_contentsHTML = $this->EmbedImageAttachments($_contentsHTML);
        $this->Mail->SetDataHTML($_contentsHTML);

        if (!$_sendSeparateEmails) {
            $this->Mail->SendMail(false, $this->_emailQueueID);
        } else {
            foreach ($_combinedEmailList as $_emailAddress) {
                $this->Mail->OverrideToField($_emailAddress);
                $this->Mail->SendMail(false, $this->_emailQueueID);
            }
        }

        return true;
    }

    /**
     * @param string $_contentsHTML
     * @return string
     * @throws SWIFT_Exception
     */
    public function EmbedImageAttachments($_contentsHTML): string {
        $_attachmentContainer = $this->Ticket->GetAttachmentContainer();
        if (_is_array($_attachmentContainer)) {
            foreach ($_attachmentContainer as $_postId => $_postAttachmentContainer) {
                foreach ($_postAttachmentContainer as $_attId => $_attachment) {
                    $cid      = $_attachment['contentid'];
                    $filename = $_attachment['filename'];
                    if (!empty($cid)) {
                        $_fileExtension = mb_strtolower(mb_substr($filename,
                            mb_strrpos($filename, '.') + 1));
                        if (in_array($_fileExtension, ['gif', 'jpg', 'png', 'jpeg'])) {
                            $attachment = new SWIFT_Attachment($_attachment['attachmentid']);
                            $_contentsHTML  = str_replace('cid:' . $cid,
                                'data:image/' . $_fileExtension . ';base64,' . $attachment->GetBase64Encoded(),
                                $_contentsHTML);
                        }
                    }
                }
            }
        }

        return $_contentsHTML;
    }

    /**
     * @param SWIFT_TemplateGroup $_SWIFT_TemplateGroupObject
     * @return SWIFT_LanguageEngine
     * @throws SWIFT_Exception
     */
    protected function getLanguage($_SWIFT_TemplateGroupObject) {
        $_userLanguageID = $_SWIFT_TemplateGroupObject->GetProperty('languageid');
        $_languageCache  = $this->Cache->Get('languagecache');
        $_languageCode   = $_languageCache[$_userLanguageID]['languagecode'];

        $lang = new SWIFT_LanguageEngine(SWIFT_LanguageEngine::TYPE_DB, $_languageCode, $_userLanguageID, false);

        $lang->LanguagePhraseLinked = new SWIFT_LanguagePhraseLinked();

        return $lang;
    }

    protected function getLanguageEngine() {
        return SWIFT_LanguageEngine::LoadEngine();
    }
}
