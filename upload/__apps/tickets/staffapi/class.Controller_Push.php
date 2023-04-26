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

namespace Tickets\Staffapi;

use Controller_staffapi;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreString;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Interface;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Library\StaffAPI\SWIFT_TicketStaffAPIManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserGroup;
use Tickets\Models\TimeTrack\SWIFT_TimeTrack_Exception;

/**
 * The Ticket List Retrieval Controller
 *
 * @author Varun Shoor
 */
class Controller_Push extends Controller_staffapi
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Initiate the Push
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_payLoadXML = '';
        if (isset($_POST['payload'])) {
            $_payLoadXML = $_POST['payload'];
        }

        $_PushDataObject = simplexml_load_string($_payLoadXML);

        $_statusMessage = '';

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_staffCache = $this->Cache->Get('staffcache');

        $_createStaffAPIIDMap = $_returnTicketIDList = $_extendedTicketMapper = array();

        /**
         * ---------------------------------------------
         * Process Create
         * ---------------------------------------------
         */
        list($_departmentCache, $_statusMessage, $_ticketStatusCache, $_ticketPriorityCache, $_ticketTypeCache, $_staffCache, $_createStaffAPIIDMap, $_returnTicketIDList, $_extendedTicketMapper) = $this->processCreate(
            $_PushDataObject,
            $_SWIFT,
            $_departmentCache,
            $_statusMessage,
            $_ticketStatusCache,
            $_ticketPriorityCache,
            $_ticketTypeCache,
            $_staffCache,
            $_createStaffAPIIDMap,
            $_returnTicketIDList,
            $_extendedTicketMapper
        );

        /**
         * ---------------------------------------------
         * Process Delete
         * ---------------------------------------------
         */
        list($_statusMessage, $_departmentCache, $_ticketStatusCache) = $this->processDelete(
            $_PushDataObject,
            $_SWIFT,
            $_statusMessage,
            $_departmentCache,
            $_ticketStatusCache
        );

        /**
         * ---------------------------------------------
         * Process Modify Request
         * ---------------------------------------------
         */
        list($_returnTicketIDList, $_statusMessage, $_extendedTicketMapper) = $this->processModify(
            $_PushDataObject,
            $_SWIFT,
            $_departmentCache,
            $_ticketStatusCache,
            $_ticketPriorityCache,
            $_ticketTypeCache,
            $_staffCache,
            $_returnTicketIDList,
            $_statusMessage,
            $_extendedTicketMapper
        );

        /**
         * ---------------------------------------------
         * Dispatch Final Result
         * ---------------------------------------------
         */

        SWIFT_TicketStaffAPIManager::DispatchOnTicketIDList($_returnTicketIDList, 0, '', '', true, true, false, 1000, $_createStaffAPIIDMap, $_statusMessage, false, 0, 1000, $_extendedTicketMapper);

        return true;
    }

    /**
     * Retrieve the From Email Address
     *
     * @author Varun Shoor
     * @param int $_departmentID
     * @return string The From Email Address
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function _GetDispatchFromEmail($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fromEmailAddress = '';

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');

        $_fromEmailAddress = $_SWIFT->Settings->Get('general_returnemail');

        if (isset($_emailQueueCache['list'])) {
            foreach ($_emailQueueCache['list'] as $_emailQueue) {
                if ($_emailQueue['departmentid'] != $_departmentID) {
                    continue;
                }

                if (!empty($_emailQueue['customfromemail'])) {
                    $_fromEmailAddress = $_emailQueue['customfromemail'];
                } else {
                    $_fromEmailAddress = $_emailQueue['email'];
                }
            }
        }

        return $_fromEmailAddress;
    }

    /**
     * @param \SimpleXMLElement $_PushDataObject
     * @param SWIFT $_SWIFT
     * @param array $_departmentCache
     * @param string $_statusMessage
     * @param array $_ticketStatusCache
     * @param array $_ticketPriorityCache
     * @param array $_ticketTypeCache
     * @param array $_staffCache
     * @param array $_createStaffAPIIDMap
     * @param array $_returnTicketIDList
     * @param array $_extendedTicketMapper
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processCreate(
        $_PushDataObject,
        $_SWIFT,
        $_departmentCache,
        $_statusMessage,
        $_ticketStatusCache,
        $_ticketPriorityCache,
        $_ticketTypeCache,
        $_staffCache,
        $_createStaffAPIIDMap,
        $_returnTicketIDList,
        $_extendedTicketMapper
    ) {
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-2599 Staff is able to delete the ticket from Android phone even if he does not have the permission
         */
        if (isset($_PushDataObject->create) && $_SWIFT->Staff->GetPermission('staff_tcaninsertticket') != '0') {
            foreach ($_PushDataObject->create as $_CreateObject) {
                $_staffAPIID = (string)$_CreateObject->attributes()->staffapiid;

                $_subject = (string)$_CreateObject->subject;
                $_fullName = (string)$_CreateObject->fullname;
                $_email = (string)$_CreateObject->email;

                $_departmentID = (int)$_CreateObject->departmentid;
                $_ticketStatusID = (int)$_CreateObject->ticketstatusid;
                $_ticketPriorityID = (int)$_CreateObject->ticketpriorityid;
                $_ticketTypeID = (int)$_CreateObject->tickettypeid;
                $_ownerStaffID = (int)$_CreateObject->ownerstaffid;
                $_emailQueueID = (int)$_CreateObject->emailqueueid;

                if (!isset($_departmentCache[$_departmentID])) {
                    $_statusCode = '0';
                    $_statusMessage .= 'Unable to create ticket: ' . $_staffAPIID . ', department id is invalid.' . SWIFT_CRLF;

                    continue;
                }

                if (!isset($_ticketStatusCache[$_ticketStatusID])) {
                    $_statusCode = '0';
                    $_statusMessage .= 'Unable to create ticket: ' . $_staffAPIID . ', ticket status id is invalid.' . SWIFT_CRLF;

                    continue;
                }

                if (!isset($_ticketPriorityCache[$_ticketPriorityID])) {
                    $_statusCode = '0';
                    $_statusMessage .= 'Unable to create ticket: ' . $_staffAPIID . ', ticket priority id is invalid.' . SWIFT_CRLF;

                    continue;
                }

                if (!isset($_ticketTypeCache[$_ticketTypeID])) {
                    $_statusCode = '0';
                    $_statusMessage .= 'Unable to create ticket: ' . $_staffAPIID . ', ticket type id is invalid.' . SWIFT_CRLF;

                    continue;
                }

                $_assignedDepartmentIDList = [];
                if ($_ownerStaffID != '0' && !isset($_staffCache[$_ownerStaffID])) {
                    $_statusCode = '0';
                    $_statusMessage .= 'Unable to create ticket: ' . $_staffAPIID . ', owner staff id is invalid.' . SWIFT_CRLF;

                    continue;
                }

                if ($_ownerStaffID != '0') {
                    $_assignedDepartmentIDList = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_ownerStaffID);
                }

                $_creator = (string)$_CreateObject->creator;
                $_userID = $_staffID = 0;

                /*
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-4151 : Phone number in user profile does not get updated if a ticket is created using Staff API.
                 */
                $_phoneNumber = '';
                if (isset($_CreateObject->phonenumber)) {
                    $_phoneNumber = (string)$_CreateObject->phonenumber;
                }

                // Get the User Object
                list($_SWIFT_UserObject, $_userID) = $this->getUserObjectForCreate(
                    $_CreateObject,
                    $_fullName,
                    $_email,
                    $_phoneNumber,
                    $_creator
                );

                if (isset($_CreateObject->staffid)) {
                    $_staffID = (int)$_CreateObject->staffid;
                }

                $_ticketType = (string)$_CreateObject->type;

                $_sendAutoresponder = true;
                if (isset($_CreateObject->sendautoresponder)) {
                    $_sendAutoresponder = (int)$_CreateObject->sendautoresponder;
                }

                $_flagType = 0;
                if (isset($_CreateObject->flagtype)) {
                    $_flagType = (int)$_CreateObject->flagtype;
                }

                $_ccToList = [];
                if (isset($_CreateObject->ccto)) {
                    foreach ($_CreateObject->ccto as $_CCToObject) {
                        $_ccToEmail = (string)$_CCToObject;

                        if (!IsEmailValid($_ccToEmail)) {
                            continue;
                        }

                        $_ccToList[] = $_ccToEmail;
                    }
                }

                $_bccToList = [];
                if (isset($_CreateObject->bccto)) {
                    foreach ($_CreateObject->bccto as $_BCCToObject) {
                        $_bccToEmail = (string)$_BCCToObject;

                        if (!IsEmailValid($_bccToEmail)) {
                            continue;
                        }

                        $_bccToList[] = $_bccToEmail;
                    }
                }

                $_resolutionDue = $_replyDue = -1;
                if (isset($_CreateObject->resolutiondue)) {
                    $_resolutionDue = (int)$_CreateObject->resolutiondue;
                }

                if (isset($_CreateObject->replydue)) {
                    $_replyDue = (int)$_CreateObject->replydue;
                }

                $_replyContents = (string)$_CreateObject->reply->contents;

                $_finalTicketType = SWIFT_Ticket::TYPE_DEFAULT;
                if ($_ticketType === 'phone') {
                    $_finalTicketType = SWIFT_Ticket::TYPE_PHONE;
                }

                $_finalCreatorType = SWIFT_Ticket::CREATOR_USER;
                if ($_creator === 'staff') {
                    $_finalCreatorType = SWIFT_Ticket::CREATOR_STAFF;
                }

                $_destinationEmail = '';
                $_fromEmail = $_email;
                if ($_creator === 'staff') {
                    $_destinationEmail = $_email;
                    $_fromEmail = $_SWIFT->Staff->GetProperty('email');
                }

                $_fromEmailAddress = self::_GetDispatchFromEmail($_departmentID);

                if (!in_array($_departmentID, $_assignedDepartmentIDList)) {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    $_ownerStaffID = 0;
                }
                // @codeCoverageIgnoreEnd

                $_SWIFT_TicketObject = SWIFT_Ticket::Create(
                    $_subject,
                    $_fullName,
                    $_fromEmail,
                    $_replyContents,
                    $_ownerStaffID,
                    $_departmentID,
                    $_ticketStatusID,
                    $_ticketPriorityID,
                    $_ticketTypeID,
                    $_userID,
                    $_staffID,
                    $_finalTicketType,
                    $_finalCreatorType,
                    SWIFT_Ticket::CREATIONMODE_STAFFAPI,
                    $_phoneNumber,
                    $_emailQueueID,
                    $_sendAutoresponder,
                    $_destinationEmail,
                    false
                );
                if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    $_statusCode = '0';
                    $_statusMessage .= 'Ticket Creation Failed, StaffAPI ID: ' . $_staffAPIID . SWIFT_CRLF;
                }
                // @codeCoverageIgnoreEnd

                $_signatureContentsDefault = $_signatureContentsHTML = '';
                if ($_ticketType === 'sendmail') {
                    $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature(false, $_SWIFT->Staff);
                    $_signatureContentsHTML = $_SWIFT_TicketObject->GetSignature(true, $_SWIFT->Staff);
                }

                if (count($_ccToList)) {
                    SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_CC, $_ccToList);
                }

                if (count($_bccToList)) {
                    SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_BCC, $_bccToList);
                }

                if ($_creator === 'staff' && $_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_STAFFAPI) {                    // Carry out the email dispatch logic
                    $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
                    $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply(
                        $_SWIFT->Staff,
                        $_replyContents,
                        false,
                        $_fromEmailAddress,
                        [$_signatureContentsDefault, $_signatureContentsHTML]
                    );
                }

                $_createStaffAPIIDMap[$_SWIFT_TicketObject->GetTicketID()] = $_staffAPIID;

                $_returnTicketIDList[] = $_SWIFT_TicketObject->GetTicketID();
                $_extendedTicketMapper[$_SWIFT_TicketObject->GetTicketID()] = [
                    'basicinfo' => true,
                    'extendedinfo' => true,
                    'ticketposts' => true
                ];

                if (isset($_staffCache[$_ownerStaffID])) {
                    $_SWIFT_TicketObject->SetOwner($_ownerStaffID);
                }

                if ($_flagType != '0') {
                    $_SWIFT_TicketObject->SetFlag($_flagType);
                }

                if (isset($_CreateObject->tags)) {
                    $_tagListCombined = (string)$_CreateObject->tags;

                    $_tagList = explode(' ', $_tagListCombined);
                    if (!count($_tagList)) {
                        // @codeCoverageIgnoreStart
                        // this code will never be executed
                        $_tagList = [$_tagListCombined];
                    }
                    // @codeCoverageIgnoreEnd

                    $_finalTagList = [];
                    foreach ($_tagList as $_key => $_val) {
                        $_finalTag = trim(CleanTag($_val));

                        if (empty($_finalTag)) {
                            continue;
                        }

                        $_finalTagList[] = CleanTag(trim($_val));
                    }

                    if (count($_finalTagList)) {
                        SWIFT_Tag::Process(
                            SWIFT_TagLink::TYPE_TICKET,
                            $_SWIFT_TicketObject->GetTicketID(),
                            $_finalTagList,
                            $_SWIFT->Staff->GetStaffID()
                        );
                    }
                }

                if ($_resolutionDue != '-1' && $_resolutionDue != '0') {
                    $_SWIFT_TicketObject->SetResolutionDue($_resolutionDue);
                }

                if ($_replyDue != '-1' && $_replyDue != '0') {
                    $_SWIFT_TicketObject->SetDue($_replyDue);
                }

                // Process Watcher
                $_statusMessage = $this->processCreateWatcher(
                    $_SWIFT,
                    $_statusMessage,
                    $_CreateObject,
                    $_SWIFT_TicketObject
                );

                $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_SWIFT_TicketObject->GetProperty('firstpostid')));

                // Process Attachments
                $_SWIFT_TicketEmailDispatchObject = $this->processCreateAttachments(
                    $_SWIFT,
                    $_CreateObject,
                    $_SWIFT_TicketObject,
                    $_SWIFT_TicketPostObject,
                    $_replyContents,
                    $_fromEmailAddress,
                    $_signatureContentsDefault,
                    $_signatureContentsHTML
                );

                // Process Notes
                list($_noteContents, $_noteColor, $_statusMessage) = $this->processCreateNotes(
                    $_SWIFT,
                    $_statusMessage,
                    $_CreateObject,
                    $_SWIFT_TicketObject,
                    $_SWIFT_UserObject
                );

                // Process Billing
                list($_staffCache, $_statusMessage) = $this->processCreateBilling(
                    $_SWIFT,
                    $_statusMessage,
                    $_staffCache,
                    $_CreateObject,
                    $_SWIFT_TicketObject
                );

                $_SWIFT_TicketObject->ProcessUpdatePool();

                /*
                 * BUG FIX - Rahul Bhattacharya
                 *
                 * SWIFT-3050 : Activities performed from Mobile apps are not recorded in activity logs.
                 */
                // Activity Log
                SWIFT_StaffActivityLog::AddToLog(
                    sprintf(
                        $_SWIFT->Language->Get('log_newticket'),
                        $_SWIFT_TicketObject->GetTicketDisplayID(),
                        $_subject
                    ),
                    SWIFT_StaffActivityLog::ACTION_INSERT,
                    SWIFT_StaffActivityLog::SECTION_TICKETS,
                    SWIFT_StaffActivityLog::INTERFACE_API
                );
            }
        }
        return [
            $_departmentCache,
            $_statusMessage,
            $_ticketStatusCache,
            $_ticketPriorityCache,
            $_ticketTypeCache,
            $_staffCache,
            $_createStaffAPIIDMap,
            $_returnTicketIDList,
            $_extendedTicketMapper
        ];
    }

    /**
     * @param \SimpleXMLElement $_PushDataObject
     * @param SWIFT $_SWIFT
     * @param string $_statusMessage
     * @param array $_departmentCache
     * @param array $_ticketStatusCache
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processDelete($_PushDataObject, $_SWIFT, $_statusMessage, $_departmentCache, $_ticketStatusCache)
    {
        $_SWIFT_TicketObject = null;

        if (isset($_PushDataObject->delete) && $_SWIFT->Staff->GetPermission('staff_tcantrashticket') != '0') {
            $_deleteTicketIDList = [];

            foreach ($_PushDataObject->delete as $_DeleteObject) {
                $_ticketID = (string)$_DeleteObject->attributes()->ticketid;

                try {
                    $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
                    if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
                        $_deleteTicketIDList[] = $_SWIFT_TicketObject->GetTicketID();
                    }
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    $_statusCode = '0';
                    $_statusMessage .= 'Invalid Ticket (Delete): ' . $_ticketID . SWIFT_CRLF;
                }
            }

            if (count($_deleteTicketIDList)) {
                SWIFT_Ticket::TrashList($_deleteTicketIDList);

                /*
                 * BUG FIX - Rahul Bhattacharya
                 *
                 * SWIFT-3050 : Activities performed from Mobile apps are not recorded in activity logs.
                 */
                // Activity Log
                $_departmentTitle = $this->Language->Get('na');
                if ($_SWIFT_TicketObject->GetProperty('departmentid') == '0') {
                    $_departmentTitle = $this->Language->Get('trash');
                } else {
                    if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
                        $_departmentTitle = $_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title'];
                    }
                }

                $_ticketStatusContainer = [];
                $_ticketStatusTitle = $this->Language->Get('na');
                if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
                    $_ticketStatusContainer = $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')];
                    $_ticketStatusTitle = $_ticketStatusContainer['title'];
                }

                SWIFT_StaffActivityLog::AddToLog(
                    sprintf(
                        $_SWIFT->Language->Get('activitytrashticket'),
                        htmlspecialchars($_SWIFT_TicketObject->GetProperty('subject')),
                        text_to_html_entities($_departmentTitle),
                        htmlspecialchars($_ticketStatusTitle),
                        text_to_html_entities($_SWIFT_TicketObject->GetProperty('fullname'))
                    ),
                    SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_TICKETS,
                    SWIFT_StaffActivityLog::INTERFACE_API
                );
            }
        } else {
            if (isset($_PushDataObject->delete)) {
                $_statusCode = '0';
                $_statusMessage .= 'Unable to Delete ticket';
            }
        }
        return [
            $_statusMessage,
            $_departmentCache,
            $_ticketStatusCache
        ];
    }

    /**
     * @param \SimpleXMLElement $_PushDataObject
     * @param SWIFT $_SWIFT
     * @param array $_departmentCache
     * @param array $_ticketStatusCache
     * @param array $_ticketPriorityCache
     * @param array $_ticketTypeCache
     * @param array $_staffCache
     * @param array $_returnTicketIDList
     * @param string $_statusMessage
     * @param array $_extendedTicketMapper
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processModify(
        $_PushDataObject,
        $_SWIFT,
        $_departmentCache,
        $_ticketStatusCache,
        $_ticketPriorityCache,
        $_ticketTypeCache,
        $_staffCache,
        $_returnTicketIDList,
        $_statusMessage,
        $_extendedTicketMapper
    ) {
        if (isset($_PushDataObject->modify) && $_SWIFT->Staff->GetPermission('staff_tcanupateticket') != '0') {
            foreach ($_PushDataObject->modify as $_ModifyObject) {
                $_ticketID = (string)$_ModifyObject->attributes()->ticketid;
                $_ignoreEmail = false;

                $_mapOptionBasic = $_mapOptionExtended = $_mapOptionPost = false;

                try {
                    $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
                    if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {

                        $_SWIFT_TicketPostObject = false;
                        $_departmentID = $_ticketStatusID = $_ticketPriorityID = $_ticketTypeID = $_ownerStaffID = -1;

                        if (isset($_ModifyObject->departmentid)) {
                            $_departmentID = (int)$_ModifyObject->departmentid;

                            if (!isset($_departmentCache[$_departmentID])) {
                                $_departmentID = -1;
                            }
                        }

                        if (isset($_ModifyObject->ticketstatusid)) {
                            $_ticketStatusID = (int)$_ModifyObject->ticketstatusid;

                            if (!isset($_ticketStatusCache[$_ticketStatusID])) {
                                $_ticketStatusID = -1;
                            }
                        }

                        if (isset($_ModifyObject->ticketpriorityid)) {
                            $_ticketPriorityID = (int)$_ModifyObject->ticketpriorityid;

                            if (!isset($_ticketPriorityCache[$_ticketPriorityID])) {
                                $_ticketPriorityID = -1;
                            }
                        }

                        if (isset($_ModifyObject->tickettypeid)) {
                            $_ticketTypeID = (int)$_ModifyObject->tickettypeid;

                            if (!isset($_ticketTypeCache[$_ticketTypeID])) {
                                $_ticketTypeID = -1;
                            }
                        }

                        if (isset($_ModifyObject->ownerstaffid)) {
                            $_ownerStaffID = (int)$_ModifyObject->ownerstaffid;

                            if ($_ownerStaffID != '0' && !isset($_staffCache[$_ownerStaffID])) {
                                $_ownerStaffID = -1;
                            }
                        }

                        $_flagType = -1;
                        if (isset($_ModifyObject->flagtype)) {
                            $_flagType = (int)$_ModifyObject->flagtype;
                        }

                        $_ccToList = [];
                        if (isset($_ModifyObject->ccto)) {
                            foreach ($_ModifyObject->ccto as $_CCToObject) {
                                $_ccToEmail = (string)$_CCToObject;

                                if (!IsEmailValid($_ccToEmail)) {
                                    continue;
                                }

                                $_ccToList[] = $_ccToEmail;
                            }
                        }

                        $_bccToList = [];
                        if (isset($_ModifyObject->bccto)) {
                            foreach ($_ModifyObject->bccto as $_BCCToObject) {
                                $_bccToEmail = (string)$_BCCToObject;

                                if (!IsEmailValid($_bccToEmail)) {
                                    continue;
                                }

                                $_bccToList[] = $_bccToEmail;
                            }
                        }

                        $_resolutionDue = $_replyDue = -1;
                        if (isset($_ModifyObject->resolutiondue)) {
                            $_resolutionDue = (int)$_ModifyObject->resolutiondue;
                        }

                        if (isset($_ModifyObject->replydue)) {
                            $_replyDue = (int)$_ModifyObject->replydue;
                        }

                        $_replyContents = '';

                        if (count($_ccToList)) {
                            SWIFT_TicketRecipient::Create(
                                $_SWIFT_TicketObject,
                                SWIFT_TicketRecipient::TYPE_CC,
                                $_ccToList
                            );
                        }

                        if (count($_bccToList)) {
                            SWIFT_TicketRecipient::Create(
                                $_SWIFT_TicketObject,
                                SWIFT_TicketRecipient::TYPE_BCC,
                                $_bccToList
                            );
                        }

                        if (isset($_ModifyObject->reply->contents)) {
                            $_replyContents = (string)$_ModifyObject->reply->contents;
                            $_ignoreEmail = false;
                            if (isset($_ModifyObject->reply->ignoreemail)) {
                                $_ignoreEmail = $_ModifyObject->reply->ignoreemail == 1;
                            }
                            /**
                             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                             *
                             * SWIFT-4806 Add option to the staff API to not send automatic emails when creating a ticket reply.
                             */
                            $_SWIFT_TicketPostObject = SWIFT_TicketPost::CreateStaff(
                                $_SWIFT_TicketObject,
                                $_SWIFT->Staff,
                                SWIFT_Ticket::CREATIONMODE_STAFFAPI,
                                $_replyContents,
                                $_SWIFT_TicketObject->GetProperty('subject'),
                                true,
                                false
                            );

                            /*
                             * BUG FIX - Rahul Bhattacharya
                             *
                             * SWIFT-3050 : Activities performed from Mobile apps are not recorded in activity logs.
                             */
                            // Activity Log
                            SWIFT_StaffActivityLog::AddToLog(
                                sprintf(
                                    $_SWIFT->Language->Get('log_newreply'),
                                    $_SWIFT_TicketObject->GetTicketDisplayID()
                                ),
                                SWIFT_StaffActivityLog::ACTION_UPDATE,
                                SWIFT_StaffActivityLog::SECTION_TICKETS,
                                SWIFT_StaffActivityLog::INTERFACE_API
                            );

                            $_mapOptionPost = true;
                        }

                        $_returnTicketIDList[] = $_SWIFT_TicketObject->GetTicketID();

                        if ($_flagType != '-1') {
                            $_SWIFT_TicketObject->SetFlag($_flagType);
                            $_mapOptionBasic = true;
                        }

                        if ($_departmentID != '-1' && $_departmentID != '0') {
                            $_SWIFT_TicketObject->SetDepartment($_departmentID);
                            $_mapOptionBasic = true;
                        }

                        if ($_ticketStatusID != '-1' && $_ticketStatusID != '0') {
                            $_SWIFT_TicketObject->SetStatus($_ticketStatusID);
                            $_mapOptionBasic = true;
                        }

                        if ($_ticketPriorityID != '-1' && $_ticketPriorityID != '0') {
                            $_SWIFT_TicketObject->SetPriority($_ticketPriorityID);
                            $_mapOptionBasic = true;
                        }

                        if ($_ticketTypeID != '-1' && $_ticketTypeID != '0') {
                            $_SWIFT_TicketObject->SetType($_ticketTypeID);
                            $_mapOptionBasic = true;
                        }

                        if ($_ownerStaffID != '-1') {
                            $_SWIFT_TicketObject->SetOwner($_ownerStaffID);
                            $_mapOptionBasic = true;
                        }

                        if (isset($_ModifyObject->tags)) {
                            $_tagListCombined = (string)$_ModifyObject->tags;

                            $_tagList = explode(' ', $_tagListCombined);
                            if (!count($_tagList)) {
                                // @codeCoverageIgnoreStart
                                // this code will never be executed
                                $_tagList = [$_tagListCombined];
                            }
                            // @codeCoverageIgnoreEnd

                            $_finalTagList = [];
                            foreach ($_tagList as $_key => $_val) {
                                $_finalTag = trim(CleanTag($_val));

                                if (empty($_finalTag)) {
                                    continue;
                                }

                                $_finalTagList[] = CleanTag(trim($_val));
                            }

                            if (count($_finalTagList)) {
                                SWIFT_Tag::Process(
                                    SWIFT_TagLink::TYPE_TICKET,
                                    $_SWIFT_TicketObject->GetTicketID(),
                                    $_finalTagList,
                                    $_SWIFT->Staff->GetStaffID()
                                );
                            }
                            $_mapOptionBasic = true;
                        }

                        if ($_resolutionDue != '-1' && $_resolutionDue != '0') {
                            $_SWIFT_TicketObject->SetResolutionDue($_resolutionDue);
                            $_mapOptionBasic = true;
                        } else {
                            if ($_resolutionDue == '0') {
                                $_SWIFT_TicketObject->ClearResolutionDue();
                                $_mapOptionBasic = true;
                            }
                        }

                        if ($_replyDue != '-1' && $_replyDue != '0') {
                            $_SWIFT_TicketObject->SetDue($_replyDue);
                            $_mapOptionBasic = true;
                        } else {
                            if ($_replyDue == '0') {
                                $_SWIFT_TicketObject->ClearOverdue();
                                $_mapOptionBasic = true;
                            }
                        }

                        // Process Watcher
                        list($_mapOptionExtended, $_statusMessage) = $this->processWatcher(
                            $_SWIFT,
                            $_statusMessage,
                            $_ModifyObject,
                            $_SWIFT_TicketObject
                        );

                        // Get the User Object
                        list($_SWIFT_UserObject, $_SWIFT_ExceptionObject) = $this->getUserObject($_SWIFT_TicketObject);

                        // Process Attachments
                        $this->processAttachments($_ModifyObject, $_SWIFT_TicketPostObject, $_SWIFT_TicketObject);

                        // Process Notes
                        list($_statusMessage) = $this->processNotes(
                            $_SWIFT,
                            $_statusMessage,
                            $_ModifyObject,
                            $_SWIFT_TicketObject,
                            $_SWIFT_UserObject
                        );

                        // Process Billing
                        list($_staffCache, $_mapOptionExtended, $_statusMessage) = $this->processBilling(
                            $_SWIFT,
                            $_staffCache,
                            $_statusMessage,
                            $_ModifyObject,
                            $_SWIFT_TicketObject
                        );

                        $_extendedTicketMapper[$_SWIFT_TicketObject->GetTicketID()] = [
                            'basicinfo' => $_mapOptionBasic,
                            'extendedinfo' => $_mapOptionExtended,
                            'ticketposts' => $_mapOptionPost
                        ];
                        $_SWIFT_TicketObject->ProcessUpdatePool();

                        /**
                         * Bug Fix: Saloni Dhall, Ravi Sharma <ravi.sharma@kayako.com>
                         *
                         * SWIFT-4133: Attachment is not sent in staff reply emails, for the tickets updated using Staff API.
                         *
                         * Comments: Dispatch the staff reply to end user after processing attachments, so that it can be included in the staff reply.
                         */
                        if (!empty($_replyContents) && !$_ignoreEmail) {
                            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
                            $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature(false, $_SWIFT->Staff);
                            $_signatureContentsHTML = $_SWIFT_TicketObject->GetSignature(true, $_SWIFT->Staff);

                            $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply(
                                $_SWIFT->Staff,
                                $_replyContents,
                                false,
                                false,
                                [
                                    $_signatureContentsDefault,
                                    $_signatureContentsHTML
                                ]
                            );
                        }
                    }
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    $_statusCode = '0';
                    $_statusMessage .= 'Invalid Ticket (Modify): ' . $_ticketID . SWIFT_CRLF . $_SWIFT_ExceptionObject->getMessage() . SWIFT_CRLF . $_SWIFT_ExceptionObject->getTraceAsString() . SWIFT_CRLF;
                }
            }
        }
        return [$_returnTicketIDList, $_statusMessage, $_extendedTicketMapper];
    }

    /**
     * @param SWIFT $_SWIFT
     * @param string $_statusMessage
     * @param \SimpleXMLElement $_ModifyObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processWatcher($_SWIFT, $_statusMessage, $_ModifyObject, $_SWIFT_TicketObject)
    {
        $_mapOptionExtended = false;
        if (isset($_ModifyObject->watch) && $_SWIFT->Staff->GetPermission('staff_tcanupateticket') != '0') {
            $_watchTicket = (int)$_ModifyObject->watch;

            if ($_watchTicket == '1') {
                SWIFT_Ticket::Watch([$_SWIFT_TicketObject->GetTicketID()], $_SWIFT->Staff);
            } else {
                SWIFT_Ticket::UnWatch([$_SWIFT_TicketObject->GetTicketID()], $_SWIFT->Staff);
            }
            $_mapOptionExtended = true;
        } else {
            if (isset($_ModifyObject->watch)) {
                $_statusCode = '0';
                $_statusMessage .= 'Unable to update watch';
            }
        }
        return [$_mapOptionExtended, $_statusMessage];
    }

    /**
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return array
     * @throws SWIFT_Exception
     */
    protected function getUserObject($_SWIFT_TicketObject)
    {
        $_SWIFT_UserObject = false;
        $_SWIFT_ExceptionObject = false;
        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_TicketObject->GetProperty('userid')));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup(SWIFT_TemplateGroup::GetDefaultGroupID());
            if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception('Unable to load master template group object');
                // @codeCoverageIgnoreEnd
            }

            $_userID = SWIFT_Ticket::GetOrCreateUserID(
                $_SWIFT_TicketObject->GetProperty('fullname'),
                $_SWIFT_TicketObject->GetProperty('email'),
                $_SWIFT_TemplateGroupObject->GetRegisteredUserGroupID()
            );
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        }
        return [$_SWIFT_UserObject, $_SWIFT_ExceptionObject];
    }

    /**
     * @param \SimpleXMLElement $_ModifyObject
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @throws SWIFT_Exception
     */
    protected function processAttachments($_ModifyObject, $_SWIFT_TicketPostObject, $_SWIFT_TicketObject)
    {
        if (isset($_ModifyObject->reply->attachment) && $_SWIFT_TicketPostObject instanceof SWIFT_TicketPost) {
            foreach ($_ModifyObject->reply->attachment as $_AttachmentObject) {
                $_fileName = (string)$_AttachmentObject->attributes()->filename;
                $_fileMD5 = (string)$_AttachmentObject->attributes()->md5;

                $_fileContents = (string)$_AttachmentObject;

                $_AttachmentStoreObject = new SWIFT_AttachmentStoreString(
                    $_fileName,
                    'application/download',
                    base64_decode($_fileContents)
                );

                SWIFT_Attachment::CreateOnTicket(
                    $_SWIFT_TicketObject,
                    $_SWIFT_TicketPostObject,
                    $_AttachmentStoreObject
                );
                $_SWIFT_TicketObject->AddToAttachments(
                    $_fileName,
                    'application/download',
                    base64_decode($_fileContents)
                );
                $_SWIFT_TicketObject->MarkHasAttachments();
            }
        }
    }

    /**
     * @param SWIFT $_SWIFT
     * @param string $_statusMessage
     * @param \SimpleXMLElement $_ModifyObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_User $_SWIFT_UserObject
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processNotes($_SWIFT, $_statusMessage, $_ModifyObject, $_SWIFT_TicketObject, $_SWIFT_UserObject)
    {
        if (isset($_ModifyObject->note) && $_SWIFT->Staff->GetPermission('staff_tcanupateticketnote') != '0') {
            foreach ($_ModifyObject->note as $_NoteObject) {
                $_noteContents = (string)$_NoteObject;

                $_noteColor = 1;
                if (isset($_NoteObject->attributes()->notecolor)) {
                    $_noteColor = (int)$_NoteObject->attributes()->notecolor;
                }

                $_noteType = (string)$_NoteObject->attributes()->type;

                $_SWIFT_TicketObject->CreateNote(
                    $_SWIFT_UserObject,
                    trim($_noteContents),
                    $_noteColor,
                    $_noteType
                );

                /*
                * BUG FIX - Saloni Dhall
                *
                * SWIFT-3235 Notifications are not working with Staff APIs
                *
                * Comments: While modifying ticket properties, it should send new ticket note notification
                */
                $_SWIFT_TicketObject->NotificationManager->SetEvent('newticketnotes');

                $_SWIFT_TicketObject->SetWatcherProperties(
                    $_SWIFT->Staff->GetProperty('fullname'),
                    sprintf(
                        $_SWIFT->Language->Get('watcherprefix'),
                        $_SWIFT->Staff->GetProperty('fullname'),
                        $_SWIFT->Staff->GetProperty('email')
                    ) . SWIFT_CRLF . trim($_noteContents)
                );
            }
            $_mapOptionExtended = true;
        } else {
            if (isset($_ModifyObject->note)) {
                $_statusCode = '0';
                $_statusMessage .= 'Unable to update notes';
            }
        }
        return [$_statusMessage];
    }

    /**
     * @param SWIFT $_SWIFT
     * @param array $_staffCache
     * @param string $_statusMessage
     * @param \SimpleXMLElement $_ModifyObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processBilling($_SWIFT, $_staffCache, $_statusMessage, $_ModifyObject, $_SWIFT_TicketObject)
    {
        $_mapOptionExtended = false;
        if (isset($_ModifyObject->billing) && $_SWIFT->Staff->GetPermission('staff_tcanupatebilling') != '0') {
            foreach ($_ModifyObject->billing as $_BillingObject) {
                $_workDateline = DATENOW;
                if (isset($_BillingObject->attributes()->workdate)) {
                    $_workDateline = (int)$_BillingObject->attributes()->workdate;
                }

                $_billDateline = $_workDateline;

                if (isset($_BillingObject->attributes()->billdate)) {
                    $_billDateline = (int)$_BillingObject->attributes()->billdate;
                }

                $_noteColor = (int)$_BillingObject->attributes()->notecolor;

                $_SWIFT_StaffObject_Worker = $_SWIFT->Staff;
                if (isset($_BillingObject->attributes()->worker)) {
                    $_workerStaffID = (int)$_BillingObject->attributes()->worker;
                    if (isset($_staffCache[$_workerStaffID])) {
                        $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataStore($_staffCache[$_workerStaffID]));
                    }
                }

                $_noteContents = (string)$_BillingObject;

                $_timeSpent = (int)$_BillingObject->attributes()->timeworked;
                $_timeBillable = (int)$_BillingObject->attributes()->timebillable;

                SWIFT_TicketTimeTrack::Create(
                    $_SWIFT_TicketObject,
                    $_SWIFT->Staff,
                    $_timeSpent,
                    $_timeBillable,
                    $_noteColor,
                    trim($_noteContents),
                    $_SWIFT_StaffObject_Worker,
                    $_workDateline,
                    $_billDateline
                );
            }
            $_mapOptionExtended = true;
        } else {
            if (isset($_ModifyObject->billing)) {
                $_statusCode = '0';
                $_statusMessage .= 'Unable to update billing';
            }
        }
        return [$_staffCache, $_mapOptionExtended, $_statusMessage];
    }

    /**
     * @param SWIFT $_SWIFT
     * @param \SimpleXMLElement $_CreateObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject
     * @param string $_replyContents
     * @param string $_fromEmailAddress
     * @param string $_signatureContentsDefault
     * @param string $_signatureContentsHTML
     * @return SWIFT_TicketEmailDispatch|null
     * @throws SWIFT_Exception
     */
    protected function processCreateAttachments(
        $_SWIFT,
        $_CreateObject,
        $_SWIFT_TicketObject,
        $_SWIFT_TicketPostObject,
        $_replyContents,
        $_fromEmailAddress,
        $_signatureContentsDefault,
        $_signatureContentsHTML
    ) {
        $_SWIFT_TicketEmailDispatchObject = null;
        if (isset($_CreateObject->reply->attachment)) {
            foreach ($_CreateObject->reply->attachment as $_AttachmentObject) {
                $_fileName = (string)$_AttachmentObject->attributes()->filename;
                $_fileMD5 = (string)$_AttachmentObject->attributes()->md5;

                $_fileContents = (string)$_AttachmentObject;

                $_AttachmentStoreObject = new SWIFT_AttachmentStoreString(
                    $_fileName, 'application/download', base64_decode($_fileContents)
                );

                SWIFT_Attachment::CreateOnTicket(
                    $_SWIFT_TicketObject,
                    $_SWIFT_TicketPostObject,
                    $_AttachmentStoreObject
                );
                $_SWIFT_TicketObject->AddToAttachments(
                    $_fileName,
                    'application/download',
                    base64_decode($_fileContents)
                );
                $_SWIFT_TicketObject->MarkHasAttachments();
            }
        }
        /*
        * BUG FIX - Mansi wason <mansi.wason@kayako.com>
        *
        * SWIFT-5108 Attachment is not sent in the reply email sent to customer for the tickets created using Staff API
        *
        * Comments: It sends the attachment with the staff reply via staff api.
        */

        // Email is sent, Attachments are included if present
        $intName = $_SWIFT->Interface->GetName() ?: SWIFT_INTERFACE;
        if ($intName === 'tests' || $intName === 'staffapi') {
            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
            $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply(
                $_SWIFT->Staff,
                $_replyContents,
                false,
                $_fromEmailAddress,
                [$_signatureContentsDefault, $_signatureContentsHTML]
            );
        }

        return $_SWIFT_TicketEmailDispatchObject;
    }

    /**
     * @param SWIFT $_SWIFT
     * @param string $_statusMessage
     * @param \SimpleXMLElement $_CreateObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_User $_SWIFT_UserObject
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processCreateNotes(
        $_SWIFT,
        $_statusMessage,
        $_CreateObject,
        $_SWIFT_TicketObject,
        $_SWIFT_UserObject
    ) {
        $_noteContents = '';
        $_noteColor = 0;
        if (isset($_CreateObject->note) && $_SWIFT->Staff->GetPermission('staff_tcaninsertticketnote') != '0') {
            foreach ($_CreateObject->note as $_NoteObject) {
                $_noteContents = (string)$_NoteObject;

                $_noteColor = 1;
                if (isset($_NoteObject->attributes()->notecolor)) {
                    $_noteColor = (int)$_NoteObject->attributes()->notecolor;
                }

                $_noteType = (string)$_NoteObject->attributes()->type;

                $_SWIFT_TicketObject->CreateNote(
                    $_SWIFT_UserObject,
                    trim($_noteContents),
                    $_noteColor,
                    $_noteType
                );

                /*
                * BUG FIX - Saloni Dhall
                *
                * SWIFT-3235 Notifications are not working with Staff APIs
                *
                * Comments: It should send new ticket note notification at the time of new note creation
                */
                $_SWIFT_TicketObject->NotificationManager->SetEvent('newticketnotes');

                $_SWIFT_TicketObject->SetWatcherProperties(
                    $_SWIFT->Staff->GetProperty('fullname'),
                    sprintf(
                        $_SWIFT->Language->Get('watcherprefix'),
                        $_SWIFT->Staff->GetProperty('fullname'),
                        $_SWIFT->Staff->GetProperty('email')
                    ) . SWIFT_CRLF . trim($_noteContents)
                );
            }
        } else {
            if (isset($_CreateObject->note)) {
                $_statusCode = '0';
                $_statusMessage .= 'Unable to insert note';
            }
        }
        return [$_noteContents, $_noteColor, $_statusMessage];
    }

    /**
     * @param SWIFT $_SWIFT
     * @param string $_statusMessage
     * @param array $_staffCache
     * @param \SimpleXMLElement $_CreateObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processCreateBilling(
        $_SWIFT,
        $_statusMessage,
        $_staffCache,
        $_CreateObject,
        $_SWIFT_TicketObject
    ) {
        if (isset($_CreateObject->billing) && $_SWIFT->Staff->GetPermission('staff_tcaninsertbilling') != '0') {
            foreach ($_CreateObject->billing as $_BillingObject) {
                $_workDateline = DATENOW;
                if (isset($_BillingObject->attributes()->workdate)) {
                    $_workDateline = (int)$_BillingObject->attributes()->workdate;
                }

                $_billDateline = $_workDateline;

                if (isset($_BillingObject->attributes()->billdate)) {
                    $_billDateline = (int)$_BillingObject->attributes()->billdate;
                }

                $_noteColor = (int)$_BillingObject->attributes()->notecolor;

                $_SWIFT_StaffObject_Worker = $_SWIFT->Staff;
                if (isset($_BillingObject->attributes()->worker)) {
                    $_workerStaffID = (int)$_BillingObject->attributes()->worker;
                    if (isset($_staffCache[$_workerStaffID])) {
                        $_SWIFT_StaffObject_Worker = new SWIFT_Staff(new SWIFT_DataStore($_staffCache[$_workerStaffID]));
                    }
                }

                $_noteContents = (string)$_BillingObject;

                $_timeSpent = (int)$_BillingObject->attributes()->timeworked;
                $_timeBillable = (int)$_BillingObject->attributes()->timebillable;

                SWIFT_TicketTimeTrack::Create(
                    $_SWIFT_TicketObject,
                    $_SWIFT->Staff,
                    $_timeSpent,
                    $_timeBillable,
                    $_noteColor,
                    trim($_noteContents),
                    $_SWIFT_StaffObject_Worker,
                    $_workDateline,
                    $_billDateline
                );
            }
        } else {
            if (isset($_CreateObject->billing)) {
                $_statusCode = '0';
                $_statusMessage .= 'Unable to insert billing';
            }
        }
        return [$_staffCache, $_statusMessage];
    }

    /**
     * @param \SimpleXMLElement $_CreateObject
     * @param string $_fullName
     * @param string $_email
     * @param string $_phoneNumber
     * @param string $_creator
     * @return array
     * @throws SWIFT_Exception
     */
    protected function getUserObjectForCreate($_CreateObject, $_fullName, $_email, $_phoneNumber, $_creator)
    {
        $_SWIFT_UserObject = false;
        $_userID = 0;
        if (isset($_CreateObject->userid)) {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_CreateObject->userid));
                $_userID = $_CreateObject->userid;
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                $_SWIFT_TemplateGroupObject = new SWIFT_TemplateGroup(SWIFT_TemplateGroup::GetDefaultGroupID());
                if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
                    // @codeCoverageIgnoreStart
                    // this code will never be executed
                    throw new SWIFT_Exception('Unable to load master template group object');
                    // @codeCoverageIgnoreEnd
                }

                $_userID = SWIFT_Ticket::GetOrCreateUserID(
                    $_fullName,
                    $_email,
                    $_SWIFT_TemplateGroupObject->GetRegisteredUserGroupID(),
                    false,
                    false,
                    $_phoneNumber
                );
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
            }
        } else {
            if ($_creator !== 'staff') {
                $_userID = SWIFT_Ticket::GetOrCreateUserID(
                    $_fullName,
                    $_email,
                    SWIFT_UserGroup::RetrieveDefaultUserGroupID(SWIFT_UserGroup::TYPE_REGISTERED),
                    false,
                    false,
                    $_phoneNumber
                );
            }
        }
        return [$_SWIFT_UserObject, $_userID];
    }

    /**
     * @param SWIFT $_SWIFT
     * @param string $_statusMessage
     * @param \SimpleXMLElement $_CreateObject
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return string
     * @throws SWIFT_Exception
     */
    protected function processCreateWatcher($_SWIFT, $_statusMessage, $_CreateObject, $_SWIFT_TicketObject)
    {
        if (isset($_CreateObject->watch) && $_SWIFT->Staff->GetPermission('staff_tcanupateticket') != '0') {
            $_watchTicket = (int)$_CreateObject->watch;

            if ($_watchTicket == '1') {
                SWIFT_Ticket::Watch([$_SWIFT_TicketObject->GetTicketID()], $_SWIFT->Staff);
            }
        } else {
            if (isset($_CreateObject->watch)) {
                $_statusCode = '0';
                $_statusMessage .= 'Unable to watch';
            }
        }
        return $_statusMessage;
    }
}
