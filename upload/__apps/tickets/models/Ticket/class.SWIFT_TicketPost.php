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

namespace Tickets\Models\Ticket;

use DOMDocument;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreString;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\HTML\SWIFT_HTML;
use Base\Library\HTML\SWIFT_HTMLPurifier;
use SWIFT_Interface;
use SWIFT_Model;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_StringHTMLToText;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserOrganization;
use Tickets\Library\Bayesian\SWIFT_Bayesian;
use Tickets\Library\SLA\SWIFT_SLAManager;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\MessageID\SWIFT_TicketMessageID;
use Tickets\Models\Note\SWIFT_TicketPostNote;
use Tickets\Models\SLA\SWIFT_SLA;

/**
 * The Ticket Post Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketPost extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketposts';
    const PRIMARY_KEY        =    'ticketpostid';

    const TABLE_STRUCTURE    =    "ticketpostid I PRIMARY AUTO NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                fullname C(255) DEFAULT '' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                emailto C(255) DEFAULT '' NOTNULL,
                                subject C(255) DEFAULT '' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                hasattachments I2 DEFAULT '0' NOTNULL,
                                edited I2 DEFAULT '0' NOTNULL,
                                editedbystaffid I DEFAULT '0' NOTNULL,
                                editeddateline I DEFAULT '0' NOTNULL,
                                creator I2 DEFAULT '0' NOTNULL,
                                isthirdparty I2 DEFAULT '0' NOTNULL,
                                ishtml I2 DEFAULT '0' NOTNULL,
                                isemailed I2 DEFAULT '0' NOTNULL,
                                isprivate I2 DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                contents X2,
                                contenthash C(50) DEFAULT '' NOTNULL,
                                subjecthash C(50) DEFAULT '' NOTNULL,
                                issurveycomment I2 DEFAULT '0' NOTNULL,

                                creationmode I2 DEFAULT '0' NOTNULL,
                                responsetime I DEFAULT '0' NOTNULL,
                                firstresponsetime I DEFAULT '0' NOTNULL,
                                slaresponsetime I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketid, staffid';
    const INDEX_2            =    'email, subjecthash';
    const INDEX_3            =    'creator, staffid, dateline';
    const INDEX_4            =    'responsetime';
    const INDEX_5            =    'firstresponsetime';



    protected $_dataStore = array();

    const CREATOR_STAFF = 1;
    const CREATOR_USER = 2;
    const CREATOR_CLIENT = 2;
    const CREATOR_CC = 3;
    const CREATOR_BCC = 4;
    const CREATOR_THIRDPARTY = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Ticket_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Ticket_Exception('Failed to load Ticket Post Object');
        }
    }

    /**
     * @param string $_contents
     * @param bool $_isHTML
     * @param SWIFT $_SWIFT
     * @return array
     */
    public static function addLineBreaksIfText($_contents, $_isHTML, SWIFT $_SWIFT, $_isMail = false): array
    {
        $contentIsHtml = SWIFT_HTML::DetectHTMLContent($_contents);
        $isTinymceEnabled = $_SWIFT->Settings->GetBool('t_tinymceeditor');
        $editorFormat = $_SWIFT->Settings->Get('t_editor_format');
        $contentHasBreaks = preg_match('@<br\s*/?>@is', $_contents);
        if (!$isTinymceEnabled) {
            if ($editorFormat === 'text') {
                $_isHTML = true;
                if (!$contentIsHtml && $_isMail) {
                    $_contents = nl2br($_contents);
                }
            } else if (($editorFormat === 'html' && !$contentIsHtml) ||
                ($editorFormat === 'html' && $contentIsHtml && !$contentHasBreaks)
            ) {
                $_isHTML = true;
                $_contents = nl2br($_contents);
            }
        }
        return ['contents' => $_contents, 'ishtml' => $_isHTML];
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketposts', $this->GetUpdatePool(), 'UPDATE', "ticketpostid = '" .
            (int) ($this->GetTicketPostID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Post ID
     *
     * @author Varun Shoor
     * @return mixed "ticketpostid" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetTicketPostID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketpostid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketposts WHERE ticketpostid = '" .
                (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketpostid']) && !empty($_dataStore['ticketpostid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketpostid']) || empty($this->_dataStore['ticketpostid'])) {
                throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid creator type
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreatorType($_creatorType)
    {
        if (
            $_creatorType == self::CREATOR_STAFF || $_creatorType == self::CREATOR_USER || $_creatorType == self::CREATOR_THIRDPARTY ||
            $_creatorType == self::CREATOR_CC || $_creatorType == self::CREATOR_BCC
        ) {
            return true;
        }

        return false;
    }

    /**
     * Create a Staff Reply
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @param mixed $_creationMode The Creation Mode
     * @param string $_contents The Reply Contents
     * @param string $_subject The Reply Subject
     * @param bool $_dontSendEmail (OPTIONAL) Whether to send a email to client or not
     * @param null|bool $_isHTML (OPTIONAL) Whether the contents contain HTML data
     * @param string $_overrideFromEmail (OPTIONAL) Whether to override the from email address with the given address
     * @param bool $_isPrivate (OPTIONAL) Whether its private post
     * @param int $_dateline
     * @param array $_attachmentStoreStringContainer (OPTIONAL) Whether attachments provided
     * @return SWIFT_TicketPost|null Ticket Post ID
     * @throws SWIFT_Exception
     */
    public static function CreateStaff(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_creationMode, $_contents, $_subject, $_dontSendEmail = false, $_isHTML = null, $_overrideFromEmail = '', $_isPrivate = false, $_dateline = DATENOW, $_attachmentStoreStringContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (
            !$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
            !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()
        ) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_contents) || !SWIFT_Ticket::IsValidCreationMode($_creationMode)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        if ($_isHTML === null) {
            $_isHTML = SWIFT_HTML::DetectHTMLContent($_contents);
        }

        // Notification Event
        $_SWIFT_TicketObject->NotificationManager->SetPrivate($_isPrivate);
        $_SWIFT_TicketObject->NotificationManager->SetEvent('newstaffreply');

        // Retrieve the signature of the staff/queue
        // Use same logic as new ticket / send email
        $_signatureContentsHTML = $_SWIFT_TicketObject->GetSignature($_isHTML, $_SWIFT_StaffObject);

        // First create the ticket post
        $_finalPostContents     = $_contents . SWIFT_CRLF . $_signatureContentsHTML;
        $_finalDispatchContents = $_contents . SWIFT_CRLF;
        $_ticketPostID          = self::Create(
            $_SWIFT_TicketObject,
            $_SWIFT_StaffObject->GetProperty('fullname'),
            $_SWIFT_StaffObject->GetProperty('email'),
            $_finalPostContents,
            SWIFT_Ticket::CREATOR_STAFF,
            $_SWIFT_StaffObject->GetStaffID(),
            $_creationMode,
            $_subject,
            '',
            $_isHTML,
            false,
            false,
            $_dateline,
            $_isPrivate
        );

        // Clear overdue time?
        if ($_SWIFT->Settings->Get('t_slaresets') == '1') {
            /*
             * BUG FIX - Bishwanath Jha
             *
             * SWIFT-2078: SLA plan is not applied correctly when ticket status is changed with staff reply
             *
             * Comments: Added this method to execute on shutdown. which allow SLA calculation first then clear overdue time.
             */
            register_shutdown_function(array($_SWIFT_TicketObject, 'ClearOverdue'));
        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));

        $_SWIFT_TicketObject->ProcessPostAttachments($_SWIFT_TicketPostObject, 'replyattachments');

        if (count($_attachmentStoreStringContainer) > 0) {
            foreach ($_attachmentStoreStringContainer as $_SWIFT_AttachmentStoreStringObject) {
                if ($_SWIFT_AttachmentStoreStringObject instanceof SWIFT_AttachmentStoreString) {
                    $_SWIFT_AttachmentObject = SWIFT_Attachment::Create(SWIFT_Attachment::LINKTYPE_TICKETPOST, $_ticketPostID, $_SWIFT_AttachmentStoreStringObject, $_SWIFT_TicketObject->GetTicketID());

                    if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
                        return null;
                    }
                }
            }

            SWIFT_Ticket::RecalculateHasAttachmentProperty(array($_SWIFT_TicketObject->GetTicketID()));
        }

        if (!$_dontSendEmail && !$_isPrivate) {
            $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature(false, $_SWIFT_StaffObject);
            // Carry out the email dispatch logic
            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
            $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply($_SWIFT_StaffObject, $_finalDispatchContents, $_isHTML, $_overrideFromEmail, array($_signatureContentsDefault, $_signatureContentsHTML));
        }

        return $_SWIFT_TicketPostObject;
    }

    /**
     * Create a Forward
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_Staff $_SWIFT_StaffObject The SWIFT_Staff Object Pointer
     * @param mixed $_creationMode The Creation Mode
     * @param string $_contents The Reply Contents
     * @param string $_subject The Reply Subject
     * @param string $_emailTo The Destination Email Address
     * @param bool $_dontSendEmail (OPTIONAL) Whether to send a email to client or not
     * @param null|bool $_isHTML (OPTIONAL) Whether the contents contain HTML data
     * @param string $_overrideFromEmail (OPTIONAL) Whether to override the from email address with the given address
     * @param bool $_isPrivate (OPTIONAL) Whether its private post
     * @param bool $_isSendToCcBcc (OPTIONAL) Whether to send email to CC or BCC email addresses
     * @return int Ticket Post ID
     * @throws SWIFT_Exception
     */
    public static function CreateForward(
        SWIFT_Ticket $_SWIFT_TicketObject,
        SWIFT_Staff $_SWIFT_StaffObject,
        $_creationMode,
        $_contents,
        $_subject,
        $_emailTo,
        $_dontSendEmail = false,
        $_isHTML = null,
        $_overrideFromEmail = '',
        $_isPrivate = false,
        $_isSendToCcBcc = true
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (
            !$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
            !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()
        ) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_contents) || !SWIFT_Ticket::IsValidCreationMode($_creationMode)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        if ($_isHTML === null) {
            $_isHTML = SWIFT_HTML::DetectHTMLContent($_contents);
        }

        // Retrieve the signature of the staff/queue
        $_htmlSetting = $_SWIFT->Settings->GetString('t_ochtml');
        $_signatureContentsDefault = self::GetParsedContents(
            $_SWIFT_TicketObject->GetSignature($_isHTML, $_SWIFT_StaffObject),
            $_htmlSetting,
            $_isHTML
        );
        $_signatureContentsHTML = self::GetParsedContents(
            $_SWIFT_TicketObject->GetSignature(SWIFT_HTML::DetectHTMLContent($_signatureContentsDefault) || $_isHTML, $_SWIFT_StaffObject),
            $_htmlSetting,
            SWIFT_HTML::DetectHTMLContent($_signatureContentsDefault) || $_isHTML
        );

        // First create the ticket post
        $_finalDispatchContents = $_contents . SWIFT_CRLF;
        $_finalPostContents     = $_contents . SWIFT_CRLF . $_signatureContentsDefault;
        $_ticketPostID          = self::Create(
            $_SWIFT_TicketObject,
            $_SWIFT_StaffObject->GetProperty('fullname'),
            $_SWIFT_StaffObject->GetProperty('email'),
            $_finalPostContents,
            SWIFT_Ticket::CREATOR_STAFF,
            $_SWIFT_StaffObject->GetStaffID(),
            $_creationMode,
            $_subject,
            $_emailTo,
            $_isHTML,
            true,
            false,
            DATENOW,
            $_isPrivate
        );

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));

        $_SWIFT_TicketObject->ProcessPostAttachments($_SWIFT_TicketPostObject, 'forwardattachments');

        // Carry out the email dispatch logic
        if ($_dontSendEmail == false) {
            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);

            /*
            * BUG FIX - Ravi Sharma
            *
            * SWIFT-3402 Edit Subject Field option should be available on ticket forwarding page.
            */
            $_SWIFT_TicketEmailDispatchObject->DispatchForwardReply($_emailTo, $_SWIFT_StaffObject, $_finalDispatchContents, $_isHTML, $_overrideFromEmail, array(
                $_signatureContentsDefault,
                $_signatureContentsHTML
            ), $_subject, $_isSendToCcBcc);
        }

        return $_ticketPostID;
    }

    /**
     * Udate ticket status
     * @param SWIFT $_SWIFT
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @throws SWIFT_Exception
     * @throws SWIFT_Ticket_Exception
     */
    private static function updateTicketStatus(SWIFT $_SWIFT, SWIFT_Ticket $_SWIFT_TicketObject)
    {
        $_ticketStatusCache = (array)$_SWIFT->Cache->Get('statuscache');

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1140 "Ticket Status (Post Parse)" criteria does not work
         *
         * Comments: Added has status changed property to make sure that this does not override the status set by parser rule
         */
        /*
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3945 Linking status with Department throws error at Client Support center while replying.
         *
         * Comments: If ticket department is not linked with 'set status on user reply' status then it should pick any unresolved status.
         */
        if (
            isset($_ticketStatusCache[$_SWIFT->Settings->Get('t_cstatusupd')])
            && $_SWIFT_TicketObject->GetHasStatusChanged() == false
            && ($_ticketStatusCache[$_SWIFT->Settings->Get('t_cstatusupd')]['departmentid'] == '0'
                || $_ticketStatusCache[$_SWIFT->Settings->Get('t_cstatusupd')]['departmentid'] == $_SWIFT_TicketObject->Get('departmentid'))
        ) {
            $_SWIFT_TicketObject->SetStatus($_SWIFT->Settings->Get('t_cstatusupd'));
        } else if ($_SWIFT->Settings->Get('t_cstatusupd') != '0' && $_SWIFT_TicketObject->GetHasStatusChanged() == false) {
            foreach ($_ticketStatusCache as $_statusContainer) {
                if (($_statusContainer['departmentid'] == '0' || $_statusContainer['departmentid'] == $_SWIFT_TicketObject->Get('departmentid')) && $_statusContainer['markasresolved'] != '1') {
                    $_SWIFT_TicketObject->SetStatus($_statusContainer['ticketstatusid']);
                    break;
                }
            }
        }
    }

    /**
     * Create a Client Ticket Post
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @param mixed $_creationMode The Creation Mode
     * @param string $_contents The Reply Contents
     * @param string $_subject The Reply Subject
     * @param mixed $_creatorType The Creator Type
     * @param null|bool $_isHTML
     * @param string $_customEmail (OPTIONAL) The custom email from which the post was received
     * @param array $_attachmentsContainer (OPTIONAL) The Attachments Container
     * @param int $_dateline
     * @param array $_attachmentStoreStringContainer (OPTIONAL) The AttachmentStoreStringObject Container
     * @return int Ticket Post ID
     * @throws SWIFT_Exception
     */
    public static function CreateClient(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_User $_SWIFT_UserObject, $_creationMode, $_contents, $_subject, $_creatorType, $_isHTML = null, $_customEmail = '', $_attachmentsContainer = array(), $_dateline = DATENOW, $_attachmentStoreStringContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (
            !$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
            !$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()
        ) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA . '2');
        }

        if (empty($_contents) || !SWIFT_Ticket::IsValidCreationMode($_creationMode)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA . '3');
        }

        if ($_isHTML === null) {
            $_isHTML = SWIFT_HTML::DetectHTMLContent($_contents);
        }

        $_isThirdParty = false;
        if ($_creatorType == self::CREATOR_THIRDPARTY) {
            $_isThirdParty = true;
        }

        $_finalEmail = '';
        if (empty($_customEmail)) {
            $_userEmailList = $_SWIFT_UserObject->GetEmailList();
            $_finalEmail = $_userEmailList[0];
        } else {
            $_finalEmail = $_customEmail;
        }

        // Notification Event
        $_SWIFT_TicketObject->NotificationManager->SetEvent('newclientreply');

        self::updateTicketStatus($_SWIFT, $_SWIFT_TicketObject);

        // First create the ticket post
        $_finalPostContents = $_contents;
        $_ticketPostID = self::Create(
            $_SWIFT_TicketObject,
            $_SWIFT_UserObject->GetProperty('fullname'),
            $_finalEmail,
            $_finalPostContents,
            $_creatorType,
            $_SWIFT_UserObject->GetUserID(),
            $_creationMode,
            $_subject,
            '',
            $_isHTML,
            $_isThirdParty,
            false,
            $_dateline
        );

        // Execute SLA
        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-2563 Resolution time gets reset on ticket after every client reply
         *
         */
        $_SWIFT_TicketObject->ExecuteSLA(false);

        // Processing Attachments
        if (count($_attachmentStoreStringContainer) > 0) {
            foreach ($_attachmentStoreStringContainer as $_SWIFT_AttachmentStoreStringObject) {
                if ($_SWIFT_AttachmentStoreStringObject instanceof SWIFT_AttachmentStoreString) {
                    $_SWIFT_AttachmentObject = SWIFT_Attachment::Create(SWIFT_Attachment::LINKTYPE_TICKETPOST, $_ticketPostID, $_SWIFT_AttachmentStoreStringObject, $_SWIFT_TicketObject->GetTicketID());

                    if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
                        // @codeCoverageIgnoreStart
                        // this code will never be executed
                        return 0;
                        // @codeCoverageIgnoreEnd
                    }
                }
            }
            SWIFT_Ticket::RecalculateHasAttachmentProperty(array($_SWIFT_TicketObject->GetTicketID()));
        }

        // Carry out the email dispatch logic

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1736 Help desk should not send reply to recipient address if it is already added in the email as CC or TO
         *
         * Comments: Dont send the emails to other recipients if the creation mode is email. This prevents the system from sending duplicate replies to other recipients.
         */
        if ($_creationMode != SWIFT_Ticket::CREATIONMODE_EMAIL) {
            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
            $_SWIFT_TicketEmailDispatchObject->DispatchUserReply($_SWIFT_UserObject, $_contents, $_isHTML, $_attachmentsContainer, $_finalEmail, $_isThirdParty);
        }

        return $_ticketPostID;
    }

    /**
     * Create a Client Ticket Post
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @param mixed $_creationMode The Creation Mode
     * @param string $_contents The Reply Contents
     * @param string $_subject The Reply Subject
     * @param mixed $_creatorType The Creator Type
     * @param null|bool $_isHTML (OPTIONAL) Whether the contents contain HTML data
     * @param string $_customEmail (OPTIONAL) The custom email from which the post was received
     * @return int Ticket Post ID
     * @throws SWIFT_Exception
     */
    public static function CreateClientSurvey(
        SWIFT_Ticket $_SWIFT_TicketObject,
        SWIFT_User $_SWIFT_UserObject,
        $_creationMode,
        $_contents,
        $_subject,
        $_creatorType,
        $_isHTML = null,
        $_customEmail = ''
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (
            !$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
            !$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()
        ) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA . '2');
        }

        if (empty($_contents) || !SWIFT_Ticket::IsValidCreationMode($_creationMode)) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA . '3');
        }

        if ($_isHTML === null) {
            $_isHTML = SWIFT_HTML::DetectHTMLContent($_contents);
        }

        $_isThirdParty = false;
        if ($_creatorType == self::CREATOR_THIRDPARTY) {
            $_isThirdParty = true;
        }

        $_finalEmail = '';
        if (empty($_customEmail)) {
            $_userEmailList = $_SWIFT_UserObject->GetEmailList();
            $_finalEmail = $_userEmailList[0];
        } else {
            $_finalEmail = $_customEmail;
        }

        // Notification Event
        $_SWIFT_TicketObject->NotificationManager->SetEvent('newclientsurvey');

        // First create the ticket post
        $_finalPostContents = $_contents;
        $_ticketPostID = self::Create(
            $_SWIFT_TicketObject,
            $_SWIFT_UserObject->GetProperty('fullname'),
            $_finalEmail,
            $_finalPostContents,
            $_creatorType,
            $_SWIFT_UserObject->GetUserID(),
            $_creationMode,
            $_subject,
            '',
            $_isHTML,
            $_isThirdParty,
            true
        );

        return $_ticketPostID;
    }

    /**
     * Create a new Ticket Post
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param string $_fullName The Fullname
     * @param string $_email The Email Address
     * @param string $_contents The contents of the post
     * @param mixed $_creatorType The Creator Type
     * @param int $_creatorID The Creator ID
     * @param mixed $_creationMode The Creation Mode
     * @param string $_subject (OPTIONAL) The Subject
     * @param string $_emailTo (OPTIONAL) The Email to which this post was received for, generally the email address of the queue
     * @param null|bool $_isHTML
     * @param bool $_isThirdParty (OPTIONAL) Whether its a third party reply
     * @param bool $_isSurveyComment (OPTIONAL) Whether its a survey comment
     * @param int $_dateline
     * @param bool $_isPrivate (OPTIONAL) Whether its private post
     * @return int "_ticketPostID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Create(
        SWIFT_Ticket $_SWIFT_TicketObject,
        $_fullName,
        $_email,
        $_contents,
        $_creatorType,
        $_creatorID,
        $_creationMode,
        $_subject,
        $_emailTo = '',
        $_isHTML = null,
        $_isThirdParty = false,
        $_isSurveyComment = false,
        $_dateline = DATENOW,
        $_isPrivate = false
    ) {
        $_SWIFT     = SWIFT::GetInstance();

        if ($_isHTML === null) {
            $_isHTML = SWIFT_HTML::DetectHTMLContent($_contents);
        }

        if (
            !$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || empty($_fullName) || empty($_email) ||
            $_contents == '' || !self::IsValidCreatorType($_creatorType) ||
            !SWIFT_Ticket::IsValidCreationMode($_creationMode)
        ) {
            throw new SWIFT_Ticket_Exception(SWIFT_INVALIDDATA);
        }

        ['contents' => $_contents, 'ishtml' => $_isHTML] = self::addLineBreaksIfText($_contents, $_isHTML, $_SWIFT);

        $_staffID = $_userID = 0;
        if ($_creatorType == SWIFT_Ticket::CREATOR_STAFF) {
            $_staffID = $_creatorID;
        } else if ($_creatorType == SWIFT_Ticket::CREATOR_USER) {
            $_userID = $_creatorID;
        }

        $_isEmailed = false;
        if ($_creationMode == SWIFT_Ticket::CREATIONMODE_EMAIL) {
            $_isEmailed = true;
        }

        $_ipAddress = '';
        if ($_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_CRON && $_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_CONSOLE) {
            $_ipAddress = SWIFT::Get('IP');
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4941 Check Custom Tweaks compatibility with SWIFT
         */

        $_subject  = $_SWIFT->Emoji->encode($_subject);
        $_contents = $_SWIFT->Emoji->encode($_contents);

        $_contents = SWIFT_TicketPost::SmartReply($_SWIFT_TicketObject, $_contents);

        /*
         * BUG FIX - Rajat Garg
         *
         * SWIFT-2210 Response times in reports are not calculated in accordance with SLA-defined working hours
         *
         * Comments: GetWorkingSeconds call if SLA is applicable, otherwise simple time difference
         */

        /*
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4317 SLA plans that get deleted but are linked to user profiles triggers an error when updating tickets started by that user
         *
         */
        $SLAManager    = new SWIFT_SLAManager();
        $_SLA          = false;
        $_slaPlanCache = $_SWIFT->Cache->Get('slaplancache');

        if ($_SWIFT_TicketObject->GetProperty('ticketslaplanid') != '0' && isset($_slaPlanCache[$_SWIFT_TicketObject->GetProperty('ticketslaplanid')])) {
            $_SLA = new SWIFT_SLA(new SWIFT_DataID($_SWIFT_TicketObject->GetProperty('ticketslaplanid')));
        }

        if (!$_SLA && $_SWIFT_TicketObject->GetProperty('slaplanid') != '0' && isset($_slaPlanCache[$_SWIFT_TicketObject->GetProperty('slaplanid')])) {
            $_SLA = new SWIFT_SLA(new SWIFT_DataID($_SWIFT_TicketObject->GetProperty('slaplanid')));
        }

        /**
         * BUG FIX - Ashish Kataria <ashish.kataria@kayako.com>, Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-2210 Response times in reports are not calculated in accordance with SLA-defined working hours
         *
         * Comments: Calculated SLA response time
         */
        // Calculate average response time
        // Does this ticket have a last post?
        $_responseTime = $_SLAResponseTime = 0;

        if ($_SWIFT_TicketObject->GetProperty('lastpostid') != '0') {
            try {
                $_TicketPost   = new SWIFT_TicketPost(new SWIFT_DataID($_SWIFT_TicketObject->GetProperty('lastpostid')));
                $_responseTime = DATENOW - $_TicketPost->GetProperty('dateline');

                $_replyTimeline = self::GetReplyTimeline($_SWIFT_TicketObject->GetID());

                if ($_SLA && $_creatorType == self::CREATOR_STAFF && $_replyTimeline > 0) {
                    $_SLAResponseTime = $SLAManager->GetSLAResponseTime($_SLA, $_replyTimeline, DATENOW);
                }

                // Was the last post by client?
                if ($_creatorType == self::CREATOR_STAFF && $_TicketPost->GetProperty('creator') != self::CREATOR_STAFF) {
                    $_SWIFT_TicketObject->UpdateAverageResponseTime($_responseTime);
                    //$_SWIFT_TicketObject->UpdateAverageSLAResponseTime($_SLAResponseTime, $_TicketPost->Get('ticketid'));
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        // The creator of post is staff, first response time is empty AND we have a last post.. and the total replies is less than four
        $_firstResponseTime = 0;
        if ($_creatorType == self::CREATOR_STAFF && $_SWIFT_TicketObject->GetProperty('firstresponsetime') == '0' && $_SWIFT_TicketObject->GetProperty('lastpostid') != '0' && $_SWIFT_TicketObject->GetProperty('totalreplies') <= 4 && $_isPrivate == false) {
            $_firstResponseTime = DATENOW - $_SWIFT_TicketObject->GetProperty('dateline');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketposts', array(
            'ticketid'        => (int) ($_SWIFT_TicketObject->GetTicketID()),
            'fullname'        => $_fullName, 'email' => mb_strtolower($_email), 'emailto' => mb_strtolower($_emailTo),
            'subject'         => $_subject, 'ipaddress' => $_ipAddress,
            'creator'         => (int) ($_creatorType), 'isemailed' => (int) ($_isEmailed), 'staffid' => $_staffID,
            'userid'          => $_userID,
            'contents'        => $_contents, 'contenthash' => sha1($_contents), 'subjecthash' => sha1($_subject),
            'creationmode'    => (int) ($_creationMode),
            'dateline'        => $_dateline, 'ishtml' => (int) ($_isHTML), 'isthirdparty' => (int) ($_isThirdParty),
            'issurveycomment' => (int) ($_isSurveyComment),
            'responsetime'    => (int) ($_responseTime),
            'firstresponsetime' => (int) ($_firstResponseTime),
            'slaresponsetime' => ($_SLAResponseTime),
            'isprivate'       => (int) ($_isPrivate)
        ), 'INSERT');

        $_ticketPostID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketPostID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));

        // Was the last post by staff?
        $_SWIFT_TicketObject->UpdateAverageSLAResponseTime($_SLAResponseTime, $_SWIFT_TicketPostObject->Get('ticketid'));

        if (!empty($_firstResponseTime)) {
            $_SWIFT_TicketObject->UpdatePool('firstresponsetime', (int) ($_firstResponseTime));
        }

        $_SWIFT_TicketObject->UpdatePool('lastpostid', $_ticketPostID);
        $_SWIFT_TicketObject->UpdatePool('lastactivity', DATENOW);
        $_SWIFT_TicketObject->UpdatePool('lastreplier', $_fullName);
        $_SWIFT_TicketObject->UpdatePool('autoclosestatus', SWIFT_Ticket::AUTOCLOSESTATUS_NONE);
        $_SWIFT_TicketObject->UpdatePool('autoclosetimeline', '0');

        if ($_creatorType == SWIFT_Ticket::CREATOR_STAFF) {
            $_SWIFT_TicketObject->UpdatePool('laststaffreplytime', DATENOW);
        } else if ($_creatorType == SWIFT_Ticket::CREATOR_USER) {
            $_SWIFT_TicketObject->UpdatePool('lastuserreplytime', DATENOW);
        }

        if ($_isPrivate == false) {
            $_totalTicketReplies = (int) ($_SWIFT_TicketObject->GetProperty('totalreplies'));
            $_SWIFT_TicketObject->UpdatePool('totalreplies', $_totalTicketReplies + 1);
        }

        $_SWIFT_TicketObject->SetWatcherProperties($_fullName, sprintf($_SWIFT->Language->Get('watcherprefix'), $_fullName, $_email) . SWIFT_CRLF . $_contents);

        // Index the words in the post for searching
        $eng = new SWIFT_SearchEngine();
        $eng->Insert($_SWIFT_TicketObject->GetTicketID(), $_ticketPostID, SWIFT_SearchEngine::TYPE_TICKET, $_contents);

        // Create unique message id
        SWIFT_TicketMessageID::Create($_SWIFT_TicketObject, $_SWIFT_TicketPostObject);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1609 ticketpostid is always set 0 in swticketauditlogs table
         */
        // Create Audit Log
        SWIFT_TicketAuditLog::AddToLog(
            $_SWIFT_TicketObject,
            $_SWIFT_TicketPostObject,
            SWIFT_TicketAuditLog::ACTION_NEWTICKETPOST,
            sprintf($_SWIFT->Language->Get('al_newreply'), $_fullName, $_email),
            SWIFT_TicketAuditLog::VALUE_NONE,
            0,
            '',
            0,
            '',
	        ['al_newreply', $_fullName, $_email]
        );

        return $_ticketPostID;
    }

    /**
     * Get Timeline for consecutive posts by user
     *
     * @author Nidhi Gupta <nidhi.gupta@kayako.com>
     * @param int $_ticketID
     * @return int
     */
    public static function GetReplyTimeline($_ticketID)
    {
        $_SWIFT                  = SWIFT::GetInstance();
        $_staffLastReplyTimeline = $_SWIFT->Database->QueryFetch('SELECT dateline FROM  ' . TABLE_PREFIX . 'ticketposts
                                                                   WHERE ticketid = ' . $_SWIFT->Database->Escape($_ticketID) . '
                                                                       AND creator = ' . self::CREATOR_STAFF . '
                                                                  ORDER BY ticketpostid DESC');

        // Need to pick dateline from staff post.
        $_whereCondition = (is_array($_staffLastReplyTimeline) && array_key_exists('dateline', $_staffLastReplyTimeline)) ? ' AND dateline > ' . $_staffLastReplyTimeline['dateline'] : '';

        $_currentTimeline = $_SWIFT->Database->QueryFetch('SELECT dateline FROM ' . TABLE_PREFIX . 'ticketposts
                                                            WHERE ticketid = ' . $_SWIFT->Database->Escape($_ticketID) . '
                                                              AND creator = ' . self::CREATOR_CLIENT . $_whereCondition . '
                                                           ORDER BY ticketpostid ASC');

        return (is_array($_currentTimeline) && array_key_exists('dateline', $_currentTimeline)) ?  $_currentTimeline['dateline'] : 0;
    }

    /**
     * Update the Ticket Post Record
     *
     * @author Varun Shoor
     * @param string $_contents The Ticket Post Contents
     * @param int $_staffID (OPTIONAL) The Staff ID making the change
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_contents, $_staffID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4941 Check Custom Tweaks compatibility with SWIFT
         */
        $_contents = $this->Emoji->encode($_contents);

        // Search engine index
        $eng = new SWIFT_SearchEngine();
        $eng->Update($this->GetProperty('ticketid'), $this->GetProperty('ticketpostid'), SWIFT_SearchEngine::TYPE_TICKET, $_contents);

        $this->UpdatePool('contents', $_contents);
        $this->UpdatePool('contenthash', sha1($_contents));
        $this->UpdatePool('edited', '1');
        $this->UpdatePool('editedbystaffid', $_staffID);
        $this->UpdatePool('editeddateline', DATENOW);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve the display contents for this ticket post
     *
     * @author Varun Shoor
     * @return string The display contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDisplayContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-5036 Outlook Conditional CSS causes blank ticket posts
         * SWIFT-5069 Iphone emails getting parsed as blank
         *
         * Comments: Cleaning Outlook Conditional CSS from ticket content.
         */
        $_parsedContents = preg_replace("/<!--\[if[^\]]*]>.*?<!\[endif\]-->/i", '', $this->GetProperty('contents'));

        $_parsedContents  = $this->Emoji->decode($_parsedContents);

        $_isHTML = SWIFT_HTML::DetectHTMLContent($this->GetProperty('contents'));

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-4026 DOMDocument::loadHTML(): Empty string supplied as input (./__apps/tickets/models/Ticket/class.SWIFT_TicketPost.php:737
         *
         * Comment: Support for inline "cid" image sources, skipping for non html or empty content
         */
        if ($this->Get('ishtml') == '1' && !empty($_parsedContents)) {
            $_Ticket = new SWIFT_Ticket(new SWIFT_DataID($this->Get('ticketid')));
            $_ticketAttachmentContainer = IIF($_Ticket->Get('hasattachments') == '1', $_Ticket->GetAttachmentContainer(), array());

            if (count($_ticketAttachmentContainer) > 0) {
                $_DOMDocument = new DOMDocument();

                $_previousErrorState = libxml_use_internal_errors(true);
                /**
                 * BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
                 * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
                 * BUG FIX : Ravi Sharma <ravi.sharma@kayako.com>
                 *
                 * SWIFT-4988 Use of undefined constant LIBXML_HTML_NOIMPLIED - assumed 'LIBXML_HTML_NOIMPLIED'
                 * SWIFT-4333 : An extra space is added to the contents of ticket created from Outlook
                 * SWIFT-4927 : With strip html tags setting enabled partial HTML content is striped off at staff cp
                 * SWIFT-4725 : 'Strip HTML tags' option results in adding extra spaces when an HTML email is sent from MS Outlook
                 * SWIFT-4912 : At trial instance, along with inline image parsing trial product message is coming.
                 * SWIFT-4999 : Blank emails with specific HTML headers
                 *
                 * Comments : Microsoft(Outlook) uses namespaces in its documents <o:p>, which in turns converted to <p> tags causing additional spaces in staff CP.
                 * Comments : Removing outlook added tags <o:p> and <o:p>&nbsp;<o:p> which is creating extra space.
                 */
                $_DOMDocument->loadHTML(mb_convert_encoding(preg_replace('/(<meta[^\>]*)\>{1,}|(<o:p>\s*[\&nbsp;]*\s*<\/o:p>)/i', '', $_parsedContents), 'HTML-ENTITIES', $_SWIFT->Language->Get('charset')));

                $_imageContainer = $_DOMDocument->getElementsByTagName('img');
                if ($_imageContainer->length > 0) {
                    // Since the images are de-duplicated, the original image can belong to another post, so we need to check
                    // every attachment on the thread for replacements
                    foreach ($_ticketAttachmentContainer as $attachmentContainers) {
                        foreach ($attachmentContainers as $_attachmentContainer) {
                            $_attachmentSource = SWIFT::Get('basename') . '/Tickets/Ticket/GetAttachment/' . $_Ticket->GetID() . '/' . $_attachmentContainer['attachmentid'];

                            for ($index = 0; $index < $_imageContainer->length; $index++) {
                                if (
                                    isset($_imageContainer->item($index)->attributes->getNamedItem('src')->nodeValue)
                                    && $_imageContainer->item($index)->attributes->getNamedItem('src')->nodeValue == "cid:" . $_attachmentContainer['contentid']
                                ) {

                                    // Replace cid:contentid with the url
                                    $_imageContainer->item($index)->attributes->getNamedItem('src')->nodeValue = $_attachmentSource;
                                }
                            }
                        }
                    }
                }

                $_parsedContents = $_DOMDocument->saveHTML();
                libxml_use_internal_errors($_previousErrorState);
            }
        }

        $_htmlSetting = $this->Settings->Get('t_chtml');
        //        if ($this->GetProperty('creator') === SWIFT_Ticket::CREATOR_STAFF) {
        //            $_htmlSetting = $this->Settings->Get('t_ochtml');
        //        } else {
        //            // Client Creator?
        //        }


        $_parsedContents = str_ireplace('</body>', '', $_parsedContents);

        $_parsedContents = ConvertTextUrlsToLinks($_parsedContents);

        $_htmlSettingstaff = $this->Settings->Get('t_ochtml');

        if ($_htmlSetting == "entities" && $_htmlSettingstaff == "entities") {
            $_parsedContents = self::GetParsedContents($_parsedContents, "html", $_isHTML);
        } else {
            $_parsedContents = self::GetParsedContents($_parsedContents, $_htmlSetting, $_isHTML);
        }

        if (!($_htmlSetting == "entities" && $_htmlSettingstaff == "entities")) {
            if (strpos($_parsedContents, "\n") > -1 && $_isHTML && $_htmlSetting == "entities") {
                $_parsedContents = nl2br($_parsedContents);
            }
        }
        $_parsedContents = SWIFT_HTML::HTMLBreaklines($_parsedContents, $_isHTML);
        return $_parsedContents;
    }


    /**
     * Retrieve the parsed contents
     *
     * @author Varun Shoor
     * @param string $_contents The Contents to Parse
     * @param string $_settingValue The Setting Value
     * @param bool $_isContentHTML
     * @param bool $_overrideAllowableTags (OPTIONAL)
     * @return string
     * @throws SWIFT_Exception
     */
    public static function GetParsedContents($_contents, $_settingValue, $_isContentHTML = false, $_overrideAllowableTags = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        /*
         * BUG FIX - Varun Shoor
         * SWIFT-1826 Replies that contain the code <base href="x-msg://27/"> , does not allow ticket tabs to work properly
         * Comments: Cleanup base tags
         */
        $_searchBase = array('@<base[^>]*?>@si');
        $_contents = preg_replace($_searchBase, '', $_contents);

        // TinyMCE editor will always encode < > & " characters, so we need to revert that so we can handle it
        if ($_SWIFT->Settings->GetBool('t_tinymceeditor')) {
            $_contents = htmlspecialchars_decode($_contents);
        }

        switch ($_settingValue) {
            case 'html':
                $_contents =  strip_javascript($_contents);

                break;

            case 'entities':
                $_contents =  htmlentities($_contents, ENT_COMPAT);
                break;

            case 'strip':
                $_SWIFT_HTMLPurifierObject = $_SWIFT->HTMLPurifier;
                if (!$_SWIFT->HTMLPurifier instanceof SWIFT_HTMLPurifier) {
                    $_SWIFT_HTMLPurifierObject = new SWIFT_HTMLPurifier();
                    $_SWIFT->SetClass('HTMLPurifier', $_SWIFT_HTMLPurifierObject);
                }
                $_contents =  $_SWIFT_HTMLPurifierObject->Purify($_contents, 'br,p'); // First purify to avoid XSS attacks
                // Strip all the non-allowed tags except for br and p, otherwise the line breaks will be removed
                $allowedTags = '<br><p>' . ($_SWIFT->Settings->GetBool('t_allowhtml')
                    ? preg_replace(['/(\w+)/', '/\s*,\s*/'], ['<$1>', ''], $_SWIFT->Settings->GetString('t_allowableadvtags'))
                    : null);
                $_contents = strip_tags($_contents, $allowedTags);
                break;
        }

        return $_contents;
    }

    /**
     * Delete the Ticket Post record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Ticket_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketPostID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Posts
     *
     * @author Varun Shoor
     * @param array $_ticketPostIDList The Ticket Post ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketPostIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketPostIDList)) {
            return false;
        }

        $_finalTicketPostIDList = array();
        $_SWIFT->Database->Query("SELECT ticketpostid FROM " . TABLE_PREFIX . "ticketposts WHERE ticketpostid IN (" . BuildIN($_ticketPostIDList) .
            ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTicketPostIDList[] = (int) ($_SWIFT->Database->Record['ticketpostid']);
        }

        if (!count($_finalTicketPostIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketposts WHERE ticketpostid IN (" . BuildIN($_finalTicketPostIDList) . ")");

        // Clear Attachments
        SWIFT_Attachment::DeleteOnTicketPost($_finalTicketPostIDList);

        // Clear Notes
        SWIFT_TicketPostNote::DeleteOnTicketPost($_finalTicketPostIDList);

        return true;
    }

    /**
     * Delete list of ticket posts based on list of ticket ids
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_ticketPostIDList = array();
        $_SWIFT->Database->Query("SELECT ticketpostid FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketPostIDList[] = (int) ($_SWIFT->Database->Record['ticketpostid']);
        }

        if (!count($_ticketPostIDList)) {
            return false;
        }

        // Search engine index
        //
        // We're nuking all of these ticket posts, based on ticket IDs.
        // In the search index, tickets are objects and ticket posts are sub-objects.
        // So, what we do is delete all of the objects (tickets) and the posts go with them.
        // No other data about tickets is stored in the index
        $eng = new SWIFT_SearchEngine();
        $eng->DeleteList($_ticketIDList, SWIFT_SearchEngine::TYPE_TICKET);

        self::DeleteList($_ticketPostIDList);

        return true;
    }

    /**
     * Replace the current ticket id all tickets with the new one
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Old Ticket ID List
     * @param SWIFT_Ticket $_SWIFT_ParentTicketObject The Parent Ticket Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ReplaceTicket($_ticketIDList, SWIFT_Ticket $_SWIFT_ParentTicketObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ParentTicketObject instanceof SWIFT_Ticket || !$_SWIFT_ParentTicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketposts', array('ticketid' => (int) ($_SWIFT_ParentTicketObject->GetTicketID())), 'UPDATE', "ticketid IN (" . BuildIN($_ticketIDList) . ")");

        return true;
    }

    /**
     * Retrieve the processed HTML & Text Contents
     *
     * @author Varun Shoor
     * @param string $_contents The Real Contents
     * @param bool $_isHTML Whether the content is HTML
     * @return array array(text contents, html contents)
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveProcessedContent($_contents, $_isHTML)
    {
        $_SWIFT        = SWIFT::GetInstance();
        if ($_isHTML) {
            $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();
            $_textContents = $_SWIFT_StringHTMLToTextObject->Convert($_contents);
            $_htmlSetting = $_SWIFT->Settings->Get('t_chtml');
            $_htmlContents = self::GetParsedContents($_contents, $_htmlSetting, $_isHTML);
            if ($_htmlSetting === 'entities') {
                $_htmlContents = nl2br($_htmlContents);
            }
        } else {
            $_textContents = $_contents;
            $_htmlContents = nl2br(htmlspecialchars($_contents));
        }

        return array($_textContents, $_htmlContents);
    }

    /**
     * Retrieve contents for quote dispatch
     *
     * @author Varun Shoor
     * @return string The Quote Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetQuoteContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        $_SWIFT                        = SWIFT::GetInstance();
        $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1499 Quote button removes the line breakes while we quote the ticket post in staff CP
         *
         */
        $_ticketContents         = $this->Emoji->decode($this->GetProperty('contents'));

        $_isHTML = SWIFT_HTML::DetectHTMLContent($_ticketContents);

        if (!$_isHTML) {
            $_ticketContents = preg_replace("#(\r\n|\r|\n)#s", "<br>", $_ticketContents);
        }

        $_finalContents          = $_SWIFT_StringHTMLToTextObject->Convert($_ticketContents);
        $_finalContentsContainer = explode(SWIFT_CRLF, $_finalContents);

        $_dispatchContents = '';
        foreach ($_finalContentsContainer as $_line) {
            $_dispatchContents .= ' > ' . $_line . SWIFT_CRLF;
        }
        if ($_isHTML && !empty($_ticketContents)) {
            $_Ticket                    = new SWIFT_Ticket(new SWIFT_DataID($this->Get('ticketid')));
            $_ticketAttachmentContainer = IIF($_Ticket->Get('hasattachments') == '1', $_Ticket->GetAttachmentContainer(), array());

            if (isset($_ticketAttachmentContainer[$this->GetID()]) && _is_array($_ticketAttachmentContainer[$this->GetID()])) {

                foreach ($_ticketAttachmentContainer[$this->GetID()] as $_attachmentContainer) {

                    $_fileExtension = mb_strtolower(substr($_attachmentContainer['filename'], (strrpos($_attachmentContainer['filename'], '.') + 1)));

                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                     *
                     * SWIFT-4995 Unable to quote posts with inline attachments older than 3 days
                     */
                    if (in_array($_fileExtension, array('gif', 'jpg', 'png', 'jpeg'))) {

                        $_attachmentObject = new SWIFT_Attachment($_attachmentContainer['attachmentid']);
                        $_ticketContents   = str_replace('cid:' . $_attachmentContainer['contentid'], 'data:image/' . $_fileExtension . ';base64,' . $_attachmentObject->GetBase64Encoded(), $_ticketContents);
                    }
                }
            }
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4985 Extra <br/> tags are added in the ticketposts while WYSIWYG editor is disabled
         * SWIFT-4998 Missing " > " while using quote button in case tinymce is disabled.
         */
        if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0') {

            if (!SWIFT_HTML::DetectHTMLContent($_ticketContents)) {
                $_ticketContents = nl2br($_ticketContents);
            }

            $_dispatchContents = "Quote On: " . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME) . "<hr>" . $_ticketContents;
        }

        return $_dispatchContents;
    }

    /**
     * Retrieve the total ticket post count
     *
     * @author Varun Shoor
     * @return int The Total Ticket Post Count
     */
    public static function GetPostCount()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketPostCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketposts");
        if (isset($_ticketPostCount['totalitems'])) {
            return (int) ($_ticketPostCount['totalitems']);
        }

        return 0;
    }

    /**
     * Update the Full Name and Email on Ticket & User ID
     *
     * @author Varun Shoor
     * @param int $_ticketID
     * @param int $_existingUserID
     * @param int $_newUserID
     * @param int $_newFullName
     * @param int $_newEmail
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateFullnameAndEmailOnTicketUser($_ticketID, $_existingUserID, $_newUserID, $_newFullName, $_newEmail)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketposts', array('userid' => $_newUserID, 'fullname' => $_newFullName, 'email' => $_newEmail), 'UPDATE', "ticketid = '" . $_ticketID . "' AND userid = '" . $_existingUserID . "'");

        return true;
    }

    /**
     * Update hasattachments property of ticket post
     *
     * @author Ruchi Kothari
     * @param int $_hasAttachments Has Attachment Value(0 or 1)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateHasAttachments($_hasAttachments)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('hasattachments', ($_hasAttachments));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve ticket post attachments
     *
     * @author Simaranjit Singh
     *
     * @param SWIFT_Ticket $_Ticket
     *
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded OR If Invalid Data is Provided
     */
    public function RetrieveAttachments(SWIFT_Ticket $_Ticket)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_Ticket->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketAttachmentContainer = array();

        $this->Database->Query("SELECT attachmentid FROM " . TABLE_PREFIX . SWIFT_Attachment::TABLE_NAME . "
                                WHERE ticketid = " . (int) ($_Ticket->GetTicketID()) . "
                                  AND linktype = " . SWIFT_Attachment::LINKTYPE_TICKETPOST . "
                                  AND linktypeid = " . (int) ($this->GetTicketPostID()));

        while ($this->Database->NextRecord()) {
            $_ticketAttachmentContainer[$this->Database->Record["attachmentid"]] = $this->Database->Record["attachmentid"];
        }

        return $_ticketAttachmentContainer;
    }

    /**
     * Update secondary user IDs with merged primary user ID
     *
     * @author Pankaj Garg
     *
     * @param int   $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     */
    public static function UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }
        $_ticketPostContainer = array();
        $_SWIFT->Database->Query("SELECT ticketpostid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                  WHERE userid IN ( " . BuildIN($_secondaryUserIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketPostContainer[$_SWIFT->Database->Record['ticketpostid']] = $_SWIFT->Database->Record;
        }
        foreach ($_ticketPostContainer as $_ticketPost) {
            $_TicketPOST = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPost['ticketpostid']));
            $_TicketPOST->UpdateUser($_primaryUserID);
        }
        return true;
    }
    /**
     * Updates the User with which the ticket post is linked
     *
     * @author Abhishek Mittal
     *
     * @param int $_userID (OPTIONAL)
     *
     * @return SWIFT_TicketPost
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateUser($_userID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }
        $this->UpdatePool('userid', $_userID);
        $this->ProcessUpdatePool();
        return $this;
    }

    /**
     * Smart Reply
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param string $_ticketPostContents
     * @return string
     * @throws SWIFT_Exception
     */
    public static function SmartReply($_SWIFT_TicketObject, $_ticketPostContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Loading language phrases
        $_SWIFT->Language->Load('staff_users');

        $_salutationList = array(
            '', $_SWIFT->Language->Get('salutationmr'), $_SWIFT->Language->Get('salutationmiss'), $_SWIFT->Language->Get('salutationmrs'), $_SWIFT->Language->Get('salutationdr')
        );

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
        $_organizationName = '';
        $_userDesignation  = '';
        $_firstName        = 'N/A';
        $_lastName         = 'N/A';
        $_fullName         = 'N/A';
        $_salutation       = 0;

        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userDesignation              = $_SWIFT_UserObject->GetProperty('userdesignation');
            $_salutation                   = $_SWIFT_UserObject->GetProperty('salutation');
            $_SWIFT_UserOrganizationObject = '';

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-5058 Placeholder used for 'username' for 'Send Email' tickets shall pick user name, instead of staff name
             *
             * Comments: Extracting first part of email and assuming . as full name separator.
             */
            if (IsEmailValid($_SWIFT_UserObject->GetProperty('fullname'))) {
                $_fullName  = preg_replace('/@.*?$/', '', $_SWIFT_UserObject->GetProperty('fullname'));
                $_parts     = explode('.', $_fullName);
                $_lastName  = array_pop($_parts);
                $_firstName = implode(' ', $_parts);
            } else {
                $_parts     = explode(' ', $_SWIFT_UserObject->GetProperty('fullname'));
                $_lastName  = array_pop($_parts);
                $_firstName = implode(' ', $_parts);
                $_fullName  = $_firstName . ' ' . $_lastName;
            }

            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT_UserObject->GetProperty('userorganizationid'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                // Nothing to prompt
            }

            if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization) {
                $_organizationName = $_SWIFT_UserOrganizationObject->GetProperty('organizationname');
            } else {
                $_organizationName = 'N/A';
            }
        }

        $_ticketPostContents = str_replace(['{{fullname}}', '%7B%7Bfullname%7D%7D'], $_fullName, $_ticketPostContents);
        $_ticketPostContents = str_replace(['{{forename}}', '%7B%7Bforename%7D%7D'], $_firstName, $_ticketPostContents);
        $_ticketPostContents = str_replace(['{{surname}}', '%7B%7Bsurname%7D%7D'], $_lastName, $_ticketPostContents);
        $_ticketPostContents = str_replace(['{{title}}', '%7B%7Btitle%7D%7D'], $_userDesignation, $_ticketPostContents);
        $_ticketPostContents = str_replace(['{{salutation}}', '%7B%7Bsalutation%7D%7D'], $_salutationList[$_salutation], $_ticketPostContents);
        $_ticketPostContents = str_replace(['{{organization}}', '%7B%7Borganization%7D%7D'], $_organizationName, $_ticketPostContents);

        // start custom fields check
        $_offset    = 0;
        $_find      = '{{custom_field[';
        $_positions = [];

        // find all positions of the custom_field placeholder
        while (($_pos = strpos($_ticketPostContents, $_find, $_offset)) !== FALSE) {
            $_positions[] = $_pos;
            $_offset = $_pos + 2;
        }

        if (empty($_positions)) {
            return $_ticketPostContents;
        }

        $_finalName = [];
        foreach ($_positions as $_value) {
            $_pos2        = strpos($_ticketPostContents, ']}}', $_value);
            $_finalName[] = substr($_ticketPostContents, $_value + 15, $_pos2 - $_value - 15);
        }

        foreach ($_finalName as $field) {
            $_SWIFT->Database->Query(
                'SELECT customfieldid, fieldtype, customfieldgroupid from ' . TABLE_PREFIX . 'customfields where title = ?',
                1,
                false,
                [$field]
            );

            while ($_SWIFT->Database->NextRecord()) {
                $_fieldtype          = $_SWIFT->Database->Record['fieldtype'];
                $_customfieldID      = $_SWIFT->Database->Record['customfieldid'];
                $_customfieldGroupID = $_SWIFT->Database->Record['customfieldgroupid'];
                $_grouptype          = '';

                $_SWIFT->Database->Query(
                    'SELECT grouptype from ' . TABLE_PREFIX . 'customfieldgroups where customfieldgroupid = ?',
                    1,
                    false,
                    [$_customfieldGroupID]
                );

                while ($_SWIFT->Database->NextRecord()) {
                    $_grouptype = $_SWIFT->Database->Record['grouptype'];
                }

                if ($_grouptype == 1) {
                    if (in_array($_fieldtype, array('1', '10', '2', '3'))) {
                        $_SWIFT->Database->Query(
                            'SELECT fieldvalue from ' . TABLE_PREFIX . 'customfieldvalues where typeid = ? and customfieldid = ?',
                            1,
                            false,
                            [$_SWIFT_TicketObject->GetProperty('userid'), $_customfieldID]
                        );

                        while ($_SWIFT->Database->NextRecord()) {
                            $_ticketPostContents = str_replace('{{custom_field[' . $field . ']}}', $_SWIFT->Database->Record['fieldvalue'], $_ticketPostContents);
                        }
                    } //end fieldtype 1,10,2,3

                    if (in_array($_fieldtype, array('4', '5', '6', '7', '9'))) {
                        $_SWIFT->Database->Query(
                            'SELECT fieldvalue, isserialized from ' . TABLE_PREFIX . 'customfieldvalues where typeid = ? and customfieldid = ?',
                            1,
                            false,
                            [$_SWIFT_TicketObject->GetProperty('userid'), $_customfieldID]
                        );

                        while ($_SWIFT->Database->NextRecord()) {
                            $_fieldValue = $_SWIFT->Database->Record['fieldvalue'];

                            if ($_SWIFT->Database->Record['isserialized'] == '1') {
                                $_fieldValue = mb_unserialize($_fieldValue);
                                $_fieldValue = self::multi_implode($_fieldValue, ",");
                            }

                            $_allValues = '';

                            $_params = str_repeat('?, ', count($_fieldValue) - 1) . '?';
                            $_SWIFT->Database->Query(
                                'SELECT optionvalue from ' . TABLE_PREFIX . 'customfieldoptions where customfieldid = ? and customfieldoptionid In (' . $_params . ')',
                                1,
                                false,
                                array_merge([$_customfieldID], $_fieldValue)
                            );

                            while ($_SWIFT->Database->NextRecord()) {
                                if ($_allValues == '') {
                                    $_allValues = $_SWIFT->Database->Record['optionvalue'];
                                } else {
                                    $_allValues = $_allValues . ',' . $_SWIFT->Database->Record['optionvalue'];
                                }
                            }

                            $_ticketPostContents = str_replace('{{custom_field[' . $field . ']}}', $_allValues, $_ticketPostContents);
                        }
                    } //end fieldtype 4,5,6,7,9
                } //end grouptype check

                if ($_grouptype == 3 || $_grouptype == 4 || $_grouptype == 9) {
                    if (in_array($_fieldtype, array("1", "10", "2", "3"))) {
                        $_SWIFT->Database->Query(
                            'SELECT fieldvalue from ' . TABLE_PREFIX . 'customfieldvalues where typeid = ? and customfieldid = ?',
                            1,
                            false,
                            [$_SWIFT_TicketObject->GetProperty('ticketid'), $_customfieldID]
                        );

                        while ($_SWIFT->Database->NextRecord()) {
                            $_ticketPostContents = str_replace('{{custom_field[' . $field . ']}}', $_SWIFT->Database->Record['fieldvalue'], $_ticketPostContents);
                        }
                    } //end fieldtype 1,10,2,3

                    if (in_array($_fieldtype, array('4', '5', '6', '7', '9'))) {
                        $_SWIFT->Database->Query(
                            'SELECT fieldvalue,isserialized from ' . TABLE_PREFIX . 'customfieldvalues where typeid = ? and customfieldid = ?',
                            1,
                            false,
                            [$_SWIFT_TicketObject->GetProperty('ticketid'), $_customfieldID]
                        );

                        while ($_SWIFT->Database->NextRecord()) {
                            $_fieldValue = $_SWIFT->Database->Record['fieldvalue'];

                            if ($_SWIFT->Database->Record['isserialized'] == '1') {
                                $_fieldValue = mb_unserialize($_fieldValue);
                                $_fieldValue = self::multi_implode($_fieldValue, ",");
                            }

                            $_allValues = '';

                            if (!is_array($_fieldValue)) {
                                $_fieldValue = [$_fieldValue];
                            }
                            $_params = str_repeat('?, ', count($_fieldValue) - 1) . '?';
                            $_SWIFT->Database->Query(
                                'SELECT optionvalue from ' . TABLE_PREFIX . 'customfieldoptions where customfieldid = ? and customfieldoptionid IN (' . $_params . ')',
                                1,
                                false,
                                array_merge([$_customfieldID], $_fieldValue)
                            );

                            while ($_SWIFT->Database->NextRecord()) {
                                if ($_allValues == '') {
                                    $_allValues = $_SWIFT->Database->Record['optionvalue'];
                                } else {
                                    $_allValues = $_allValues . ',' . $_SWIFT->Database->Record['optionvalue'];
                                }
                            }

                            $_ticketPostContents = str_replace('{{custom_field[' . $field . ']}}', $_allValues, $_ticketPostContents);
                        }
                    } //end fieldtype 4,5,6,7,9
                } //end grouptype check

                if ($_grouptype == 2) {
                    if (in_array($_fieldtype, array("1", "10", "2", "3"))) {
                        $_SWIFT->Database->Query(
                            "SELECT fieldvalue from " . TABLE_PREFIX . "customfieldvalues where typeid = ? and customfieldid = ?",
                            1,
                            false,
                            [$_SWIFT_UserObject->GetProperty('userorganizationid'), $_customfieldID]
                        );

                        while ($_SWIFT->Database->NextRecord()) {
                            $_ticketPostContents = str_replace('{{custom_field[' . $field . ']}}', $_SWIFT->Database->Record['fieldvalue'], $_ticketPostContents);
                        }
                    } //end fieldtype 1,10,2,3

                    if (in_array($_fieldtype, array("4", "5", "6", "7", "9"))) {
                        $_SWIFT->Database->Query(
                            "SELECT fieldvalue,isserialized from " . TABLE_PREFIX . "customfieldvalues where typeid = ? and customfieldid = ?",
                            1,
                            false,
                            [$_SWIFT_UserObject->GetProperty('userorganizationid'), $_customfieldID]
                        );

                        while ($_SWIFT->Database->NextRecord()) {
                            $_fieldValue = $_SWIFT->Database->Record['fieldvalue'];

                            if ($_SWIFT->Database->Record['isserialized'] == '1') {
                                $_fieldValue = mb_unserialize($_fieldValue);
                                $_fieldValue = self::multi_implode($_fieldValue, ",");
                            }

                            $_allValues = '';

                            $_params = str_repeat('?, ', count($_fieldValue) - 1) . '?';
                            $_SWIFT->Database->Query(
                                "SELECT optionvalue from " . TABLE_PREFIX . "customfieldoptions where customfieldid = ? and customfieldoptionid In (" . $_params . ")",
                                1,
                                false,
                                array_merge([$_customfieldID], $_fieldValue)
                            );

                            while ($_SWIFT->Database->NextRecord()) {
                                if ($_allValues == "") {
                                    $_allValues = $_SWIFT->Database->Record['optionvalue'];
                                } else {
                                    $_allValues = $_allValues . "," . $_SWIFT->Database->Record['optionvalue'];
                                }
                            }

                            $_ticketPostContents = str_replace('{{custom_field[' . $field . ']}}', $_allValues, $_ticketPostContents);
                        }
                    } //end fieldtype 4,5,6,7,9
                } //end grouptype check
            }
        } //foreach

        return $_ticketPostContents;
    }

    /**
     * Multi implode
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     * @param array $array
     * @param String $glue
     * @return array
     */
    public static function multi_implode($array, $glue)
    {
        $ret = [];

        foreach ($array as $item) {
            if (is_array($item)) {
                $ret = array_merge($ret, self::multi_implode($item, $glue));
            } else {
                $ret[] = $item;
            }
        }

        return $ret;
    }
}
