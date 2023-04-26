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

namespace Tickets\Client;

use Base\Library\CustomField\SWIFT_CustomFieldRendererClient;
use Controller_client;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use SWIFT_DataID;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use Tickets\Library\Notification\SWIFT_TicketNotification;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Merge\SWIFT_TicketMergeLog;
use Tickets\Models\Ticket\SWIFT_TicketLinkedTable;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Priority\SWIFT_TicketPriority;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\User\SWIFT_UserProfileImage;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Tickets\Models\Workflow\SWIFT_TicketWorkflowNotification;

/**
 * The Ticket Controller
 *
 * @author Varun Shoor
 * @property SWIFT_CustomFieldRendererClient $CustomFieldRendererClient
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 */
class Controller_Ticket extends Controller_client
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

        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('tickets');

        SWIFT_Ticket::LoadLanguageTable();

        $_SWIFT = SWIFT::GetInstance();
        if (!$_SWIFT->Session->IsLoggedIn() || !$_SWIFT->User instanceof SWIFT_User || !$_SWIFT->User->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('logintocontinue'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            // Exit and stop processing so other error messages are not displayed
            log_error_and_exit($this->Language->Get('logintocontinue'));
        }

        $_canChangeTicketProperties = true;
        if (SWIFT_User::GetPermission('perm_canchangepriorities') == '0') {
            $_canChangeTicketProperties = false;
        }

        $this->Template->Assign('_canChangeTicketProperties', $_canChangeTicketProperties);
    }

    /**
     * Retrieve the Ticket Object
     *
     * @author Varun Shoor
     * @param int|string $_ticketID The Ticket ID
     * @return SWIFT_Ticket|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetTicketObject($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID))
        {
            return false;
        }

        $_SWIFT_TicketObject = false;

        $_finalTicketID = false;
        if (is_numeric($_ticketID))
        {
            $_finalTicketID = $_ticketID;
        } else {
            $_finalTicketID = SWIFT_Ticket::GetTicketIDFromMask($_ticketID);
        }

        if (!empty($_finalTicketID))
        {
            try
            {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_finalTicketID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_SWIFT_TicketObject->CanAccess($_SWIFT->User))
        {
            return $_SWIFT_TicketObject;
        }

        // By now we couldnt get the ticket object, we have to lookup the merge logs
        $_mergeTicketID = false;
        if (is_numeric($_ticketID)) {
            $_mergeTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketID($_ticketID);
        } else {
            $_mergeTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketMaskID($_ticketID);
        }

        if (!empty($_mergeTicketID)) {
            try
            {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_mergeTicketID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_SWIFT_TicketObject->CanAccess($_SWIFT->User))
            {
                return $_SWIFT_TicketObject;
            }
        }

        return false;
    }

    /**
     * View a Ticket
     *
     * @author Varun Shoor
     * @auhtor Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param int  $_ticketID
     * @param bool $_expandPostReply (OPTIONAL) Whether post reply box is to be expanded
     * @param int  $_errorCode       (OPTIONAL) if a reply has been posted but there was some problem
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function View($_ticketID, $_expandPostReply = false, $_errorCode = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Template->Assign('_expandPostReply', $_expandPostReply);

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('ticketpermdenied'));

            $this->Load->Controller('ViewList')->Load->Index();

            return false;
        }

        if (!$_SWIFT_TicketObject->GetProperty('departmentid'))
        {
            $this->UserInterface->Error(true, $this->Language->Get('ticketpermdenied'));

            $this->Load->Controller('ViewList')->Load->Index();

            return false;
        }

        if ($_errorCode == 1) {
            $this->UserInterface->Error(true, $this->Language->Get('st_attachmentwarning'));
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        $_ticketPostCount = $_SWIFT_TicketObject->GetTicketPostCount();

        $_ticketPostOffset = 0;
        $_ticketPostLimitCount = false;

        $_ticketPostOrder = 'ASC';
        if ($this->Settings->Get('t_cpostorder') === 'desc') {
            $_ticketPostOrder = 'DESC';
        }

        if ($_ticketPostOffset < 0) {
           // @codeCoverageIgnoreStart
           // this code will never be executed
            $_ticketPostOffset = 0;

            $_ticketPostLimitCount = $_ticketPostCount;
        }
        // @codeCoverageIgnoreEnd

        $_ticketPostContainer = $_SWIFT_TicketObject->GetTicketPosts($_ticketPostOffset, $_ticketPostLimitCount, $_ticketPostOrder);
        $_ticketPostIDList = array_keys($_ticketPostContainer);
        $_ticketPostCount = count($_ticketPostContainer);

        // Process Ratings
        $this->processRatings($_SWIFT, $_SWIFT_TicketObject, $_ticketPostIDList);

        // Process Properties
        $_ticketContainer = $this->processProperties($_SWIFT, $_SWIFT_TicketObject);

        // Build Ticket Status Select Options
        $this->buildOptions($_SWIFT_TicketObject);

        $this->Template->Assign('_ticketContainer', $_ticketContainer);

        /**
         * Ticket Workflows
         */

        $_ticketLinkedTableContainer = SWIFT_TicketLinkedTable::RetrieveOnTicket($_SWIFT_TicketObject);
        $_workFlows = [];

        if (isset($_ticketLinkedTableContainer[SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW])) {
            $_ticketWorkflowCache = $this->Cache->Get('ticketworkflowcache');
            foreach ($_ticketLinkedTableContainer[SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW] as $_ticketLinkedTableValue) {
                $_ticketWorkflowID = $_ticketLinkedTableValue['linktypeid'];
                if (isset($_ticketWorkflowCache[$_ticketWorkflowID]) && $_ticketWorkflowCache[$_ticketWorkflowID]['uservisibility'] == '1') {
                    $_workFlows[$_ticketWorkflowID] = $_ticketWorkflowCache[$_ticketWorkflowID];
                    $_workFlows[$_ticketWorkflowID]['link'] = SWIFT::Get('basename') . '/Tickets/Ticket/ExecuteWorkflow/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketWorkflowID;
                }
            }
        }

        $this->Template->Assign('_workFlows', $_workFlows);

        /**
         * ---------------------------------------------
         * TICKET POST PROCESSING
         * ---------------------------------------------
         */

        $this->doPostProcessing($_SWIFT, $_ticketPostContainer, $_SWIFT_TicketObject);

        // Custom Fields
        $this->CustomFieldRendererClient->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_EDIT,
                array(SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), $_SWIFT_TicketObject->GetTicketID(), $_SWIFT_TicketObject->GetProperty('departmentid'));

        $this->Template->Assign('_pageTitle', htmlspecialchars($this->Language->Get('ticketviewticketidtitle') . $_SWIFT_TicketObject->GetTicketDisplayID()));
        $this->UserInterface->Header('viewtickets');

        $this->Template->Render('viewticket');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * @auhtor Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param int|string $_ticketID
     * @param int|string $_ticketWorkflowID
     *
     * @return bool
     *
     * @throws SWIFT_Exception
     */
    public function ExecuteWorkflow($_ticketID, $_ticketWorkflowID){
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->User)) {
            $this->UserInterface->Error(true, $this->Language->Get('ticketpermdenied'));
            $this->Load->Controller('ViewList')->Load->Index();
            return false;
        }

        $_wfresult = SWIFT_TicketWorkflow::ProcessWorkflow($_SWIFT_TicketObject, $_ticketWorkflowID, false, true);
        if ($_wfresult) {
            // Update Custom Field Values
            $this->CustomFieldManager->UpdateForTicket($_ticketWorkflowID, [SWIFT_CustomFieldGroup::GROUP_USER], (int)$_SWIFT_TicketObject->GetProperty('userid'));
            $this->CustomFieldManager->UpdateForTicket($_ticketWorkflowID, [SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
            ], $_SWIFT_TicketObject->GetTicketID());
        }

        SWIFT_Ticket::ProcessWorkflowQueue();

        $this->View($_ticketID);

        return true;
    }

    /**
     * Reply to a Ticket
     *
     * @author Varun Shoor
     *
     * @param int $_ticketID
     * @param int $_hasAttachments (OPTIONAL)
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Reply($_ticketID, $_hasAttachments = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('ticketpermdenied'));

            $this->Load->Controller('ViewList')->Load->Index();

            return false;
        }

        if (!isset($_POST['replycontents']) || $_POST['replycontents'] == '')
        {
            SWIFT::ErrorField('replycontents');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Method('View', $_ticketID, true);

            return false;
        }

        /**
         * Check for valid attachments
         *
         * @todo Change this to a parent view/controller in K5
         */
        $_attachmentCheckResult = $this->Load->Controller('Submit')->Load->CheckForValidAttachments($_hasAttachments);
        if ($_attachmentCheckResult[0] == false && _is_array($_attachmentCheckResult[1])) {
            $this->UserInterface->Error(true, sprintf($this->Language->Get('invalidattachments'), implode(', ', $_attachmentCheckResult[1])));

            $this->Load->Method('View', $_ticketID, true);

            return false;
        }

        $_errorCode = IIF($_attachmentCheckResult === false, 1, 0);

        $_ticketPostID = SWIFT_TicketPost::CreateClient($_SWIFT_TicketObject, $_SWIFT->User, SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER, $_POST['replycontents'],
                $_SWIFT_TicketObject->GetProperty('subject'), SWIFT_TicketPost::CREATOR_CLIENT);
        if (empty($_ticketPostID))
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        // Process the attachments?
        if ($this->Settings->Get('t_cenattach') == '1') {
            $_SWIFT_TicketObject->ProcessPostAttachments($_SWIFT_TicketPostObject);
        }

        if (SWIFT_INTERFACE !== 'tests')
            @header('location: ' . SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $_ticketID . '/false/' . $_errorCode);

        return true;
    }

    /**
     * Update a Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Update($_ticketID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('ticketpermdenied'));

            $this->Load->Controller('ViewList')->Load->Index();

            return false;
        }

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
                array(SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_TicketObject->GetProperty('departmentid'));
        if ($_customFieldCheckResult[0] == false)
        {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty') . '.');

            $this->Load->Method('View', $_ticketID);

            return false;
        }

        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');

        // Update Status
        if (isset($_POST['ticketstatusid']) && isset($_ticketStatusCache[$_POST['ticketstatusid']]) && $_ticketStatusCache[$_POST['ticketstatusid']]['statustype'] == SWIFT_PUBLIC &&
                $_SWIFT_TicketObject->GetProperty('ticketstatusid') != $_POST['ticketstatusid'])
        {
            $_SWIFT_TicketObject->SetStatus($_POST['ticketstatusid']);
        }

        // Update Priority
        if (isset($_POST['ticketpriorityid']) && isset($_ticketPriorityCache[$_POST['ticketpriorityid']]) && $_ticketPriorityCache[$_POST['ticketpriorityid']]['type'] == SWIFT_PUBLIC &&
                $_SWIFT_TicketObject->GetProperty('priorityid') != $_POST['ticketpriorityid'])
        {
            $_SWIFT_TicketObject->SetPriority($_POST['ticketpriorityid']);
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1387 Incorrect custom field group attached to new tickets, when client updates the ticket from client support center
         *
         * Comments: None
         */

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
                array(SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_TicketObject->GetTicketID(),
                $_SWIFT_TicketObject->GetProperty('departmentid'));

        $this->UserInterface->Info(true, $this->Language->Get('updatedticketproperties'));

        $this->Load->Method('View', $_ticketID);

        return true;
    }

    /**
     * Dispatch the Attachment
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketAttachmentID The Ticket Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAttachment($_ticketID, $_ticketAttachmentID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('ticketpermdenied'));

            $this->Load->Controller('ViewList')->Load->Index();

            return false;
        }

        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_ticketAttachmentID);
        // Did the object load up?
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_AttachmentObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AttachmentObject->Dispatch();

        return true;
    }

    /**
     * Dispatch data for quoting a reply
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetQuote($_ticketID, $_ticketPostID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('ticketpermdenied'));

            $this->Load->Controller('ViewList')->Load->Index();

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

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        echo $_SWIFT_TicketPostObject->GetQuoteContents();

        return true;
    }

    /**
     * Set a Ticket Rating
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ratingID The Rating ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Rating($_ticketID, $_ratingID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            return false;
        }

        if (!isset($_POST['ratingvalue'])) {
            return false;
        }

        $_ratingValue = (int) ($_POST['ratingvalue']);

        $_SWIFT_RatingObject = new SWIFT_Rating($_ratingID);
        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        // Notification Event
        $_SWIFT_TicketObject->NotificationManager->SetEvent('newclientsurvey');

        SWIFT_RatingResult::CreateOrUpdateIfExists($_SWIFT_RatingObject, $_SWIFT_TicketObject->GetTicketID(), $_ratingValue, SWIFT_RatingResult::CREATOR_USER, $_SWIFT->User->GetUserID());

        $_SWIFT_TicketObject->MarkHasRatings();

        return true;
    }

    /**
     * Set a rating for a given ticket post
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @param int $_ratingID The Rating ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RatingPost($_ticketID, $_ticketPostID, $_ratingID) {

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
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

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset($_POST['ratingvalue'])) {
            return false;
        }

        $_ratingValue = (int) ($_POST['ratingvalue']);

        $_SWIFT_RatingObject = new SWIFT_Rating($_ratingID);
        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        SWIFT_RatingResult::CreateOrUpdateIfExists($_SWIFT_RatingObject, $_SWIFT_TicketPostObject->GetTicketPostID(), $_ratingValue, SWIFT_RatingResult::CREATOR_USER, $_SWIFT->User->GetUserID());

        $_SWIFT_TicketObject->MarkHasRatings();

        return true;
    }

    /**
     * Upload the image
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @return bool
     */
    public function UploadImage(): bool
    {
        return \Tickets\Staff\Controller_Ticket::processUploadImage();
    }

    /**
     * @param SWIFT $_SWIFT
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param array $_ticketPostIDList
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function processRatings($_SWIFT, $_SWIFT_TicketObject, $_ticketPostIDList)
    {
        $_ticketRatingContainer = SWIFT_Rating::Retrieve([SWIFT_Rating::TYPE_TICKET], $_SWIFT->User, SWIFT_PUBLIC,
            $_SWIFT_TicketObject->GetProperty('departmentid'));
        $_ticketPostRatingContainer = SWIFT_Rating::Retrieve([SWIFT_Rating::TYPE_TICKETPOST], $_SWIFT->User,
            SWIFT_PUBLIC, $_SWIFT_TicketObject->GetProperty('departmentid'));

        $_ticketRatingIDList = $_ticketPostRatingIDList = [];
        $_customJSCode = '';
        $_ticketPostID = 0;
        foreach ($_ticketRatingContainer as $_ticketRating) {
            $_ticketRatingIDList[] = $_ticketRating['ratingid'];
            $_customJSCode .= '$("input[name=rating_' . $_ticketRating['ratingid'] . '_' . $_SWIFT_TicketObject->GetTicketID() . ']").rating({callback: function(value, link) { TriggerRating(\'/Tickets/Ticket/Rating/' . $_SWIFT_TicketObject->GetTicketID() . '/' . (int)($_ticketRating['ratingid']) . '\', \'' . (int)($_ticketRating['ratingid']) . '\', \'' . $_SWIFT_TicketObject->GetTicketID() . '\', value, ' . IIF($_ticketRating['iseditable'] == '1',
                    'false', 'true') . '); }});';
        }

        foreach ($_ticketPostRatingContainer as $_ticketPostRating) {
            $_ticketPostRatingIDList[] = $_ticketPostRating['ratingid'];

            foreach ($_ticketPostIDList as $__ticketPostID) {
                $_customJSCode .= '$("input[name=rating_' . $_ticketPostRating['ratingid'] . '_' . $__ticketPostID . ']").rating({callback: function(value, link) { TriggerRating(\'/Tickets/Ticket/RatingPost/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $__ticketPostID . '/' . (int)($_ticketPostRating['ratingid']) . '\', \'' . (int)($_ticketPostRating['ratingid']) . '\', \'' . $__ticketPostID . '\', value, ' . IIF($_ticketPostRating['iseditable'] == '1',
                        'false', 'true') . '); }});';
            }
        }

        $this->Template->Assign('_ratingJSCode', $_customJSCode);

        $_ticketRatingResultContainer = SWIFT_RatingResult::Retrieve($_ticketRatingIDList,
            [$_SWIFT_TicketObject->GetTicketID()]);
        $_ticketPostRatingResultContainer = SWIFT_RatingResult::Retrieve($_ticketPostRatingIDList, $_ticketPostIDList);

        foreach ($_ticketRatingResultContainer as $_ratingID => $_ticketRatingResultContainer_Sub) {
            foreach ($_ticketRatingResultContainer_Sub as $_ticketRatingResult) {
                $_ticketRatingContainer[$_ratingID]['result'] = $_ticketRatingResult['ratingresult'];

                if ($_ticketRatingContainer[$_ratingID]['iseditable'] == '0') {
                    $_ticketRatingContainer[$_ratingID]['isdisabled'] = true;
                }
            }
        }

        foreach ($_ticketPostRatingResultContainer as $_ratingID => $_ticketPostRatingResultContainer_Sub) {
            foreach ($_ticketPostRatingResultContainer_Sub as $__ticketPostID => $_ticketPostRatingResult) {
                if ($_ticketPostRatingContainer[$_ratingID]['iseditable'] == '0') {
                    $_ticketPostRatingResultContainer[$_ratingID][$__ticketPostID]['isdisabled'] = true;
                }
            }
        }

        $this->Template->Assign('_ticketRatingCount', count($_ticketRatingContainer));
        $this->Template->Assign('_ticketPostRatingCount', count($_ticketPostRatingContainer));
        $this->Template->Assign('_ticketRatingContainer', $_ticketRatingContainer);
        $this->Template->Assign('_ticketPostRatingContainer', $_ticketPostRatingContainer);
        $this->Template->Assign('_ticketPostRatingResults', $_ticketPostRatingResultContainer);

        return true;
    }

    /**
     * @param SWIFT $_SWIFT
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return array
     * @throws SWIFT_Exception
     */
    protected function processProperties($_SWIFT, $_SWIFT_TicketObject) {
        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        $_ticketContainer = $_SWIFT_TicketObject->GetDataStore();

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-5060 Unicode characters like emojis not working in the subject
         */
        $_ticketContainer['displayticketid'] = $_SWIFT_TicketObject->GetTicketDisplayID();
        //Any subject containinh HTML will be rendered as plain text.
        $_ticketContainer['subject'] = $this->Input->SanitizeForXSS($this->Emoji->decode($_SWIFT_TicketObject->GetProperty('subject')));
        $_ticketContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME,
            $_SWIFT_TicketObject->GetProperty('dateline'));
        $_ticketContainer['lastactivity'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME,
            $_SWIFT_TicketObject->GetProperty('lastactivity'));
        $_ticketContainer['department'] = $this->Language->Get('na');
        $_ticketContainer['owner'] = $this->Language->Get('na');
        $_ticketContainer['type'] = $this->Language->Get('na');
        $_ticketContainer['status'] = $this->Language->Get('na');
        $_ticketContainer['priority'] = $this->Language->Get('na');

        $_ticketDepartmentFullTitle = $this->Language->Get('na');

        $_ticketContainer['statusbgcolor'] = '';
        $_ticketContainer['prioritybgcolor'] = '';

        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]) && $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['markasresolved'] == '1') {
            $_ticketContainer['isresolved'] = true;
        }

        // Department
        if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
            if ($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['departmenttype'] == SWIFT_Department::DEPARTMENT_PUBLIC) {
                $_ticketDepartmentParentDepartmentID = $_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['parentdepartmentid'];
                if ($_ticketDepartmentParentDepartmentID != '0' && isset($_departmentCache[$_ticketDepartmentParentDepartmentID])) {
                    $_ticketDepartmentFullTitle = text_to_html_entities(StripName($_departmentCache[$_ticketDepartmentParentDepartmentID]['title'],
                            16)) . ' &raquo; ' . text_to_html_entities(StripName($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title'],
                            16));
                } else {
                    $_ticketDepartmentFullTitle = text_to_html_entities(StripName($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title'],
                        16));
                }
                $_ticketContainer['department'] = text_to_html_entities(StripName($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title'],
                    16));
                $_ticketDepartmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT,
                    $_SWIFT_TicketObject->GetProperty('departmentid'));
                if (!empty($_ticketDepartmentTitleLanguage)) {
                    $_ticketContainer['department'] = StripName(text_to_html_entities($_ticketDepartmentTitleLanguage), 16);
                }
            } else {
                $_ticketContainer['department'] = $this->Language->Get('private');
            }
        }

        $this->Template->Assign('_ticketDepartmentFullTitle', $_ticketDepartmentFullTitle);

        // Owner
        if ($_SWIFT_TicketObject->GetProperty('ownerstaffid') == '0') {
            $_ticketContainer['owner'] = $this->Language->Get('unassigned');
        } else {
            if ($_SWIFT->Settings->Get('t_cstaffname') == '1') {
                $_ticketContainer['owner'] = StripName(htmlspecialchars($_SWIFT->Settings->Get('t_cdisplayname')), 16);
            } else {
                if (isset($_staffCache[$_SWIFT_TicketObject->GetProperty('ownerstaffid')])) {
                    $_ticketContainer['owner'] = StripName(text_to_html_entities($_staffCache[$_SWIFT_TicketObject->GetProperty('ownerstaffid')]['fullname']),
                        16);
                }
            }
        }

        // Ticket Type
        if (isset($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')])) {
            if (isset($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]) && $_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]['type'] == SWIFT_PUBLIC) {
                $_ticketContainer['type'] = StripName(htmlspecialchars($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]['title']),
                    16);
                $_ticketTypeTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE,
                    $_SWIFT_TicketObject->GetProperty('tickettypeid'));
                if (!empty($_ticketTypeTitleLanguage)) {
                    $_ticketContainer['type'] = StripName(htmlspecialchars($_ticketTypeTitleLanguage), 16);
                }
            } else {
                $_ticketContainer['type'] = $this->Language->Get('private');
            }
        }

        // Ticket Status
        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]) && $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['statustype'] == SWIFT_PUBLIC) {
                $_ticketContainer['status'] = StripName(htmlspecialchars($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['title']),
                    16);
                $_ticketStatusTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS,
                    $_SWIFT_TicketObject->GetProperty('ticketstatusid'));
                if (!empty($_ticketStatusTitleLanguage)) {
                    $_ticketContainer['status'] = StripName(htmlspecialchars($_ticketStatusTitleLanguage), 16);
                }
            } else {
                $_ticketContainer['status'] = $this->Language->Get('private');
            }

            $_ticketContainer['statusbgcolor'] = $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['statusbgcolor'];
        }

        // Ticket Priorities
        if (isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')])) {
            if (isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]) && $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]['type'] == SWIFT_PUBLIC) {
                $_ticketContainer['priority'] = StripName(htmlspecialchars($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]['title']),
                    16);
                $_ticketPriorityTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY,
                    $_SWIFT_TicketObject->GetProperty('priorityid'));
                if (!empty($_ticketPriorityTitleLanguage)) {
                    $_ticketContainer['priority'] = StripName(htmlspecialchars($_ticketPriorityTitleLanguage), 16);
                }
            } else {
                $_ticketContainer['priority'] = $this->Language->Get('private');
            }

            $_ticketContainer['prioritybgcolor'] = $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]['bgcolorcode'];
        }
        return $_ticketContainer;
    }

    /**
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function buildOptions($_SWIFT_TicketObject)
    {
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');

        $_optionContainer = [];
        $_index = 0;
        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatus) {
            if ($_ticketStatus['statustype'] == SWIFT_PRIVATE && $_SWIFT_TicketObject->GetProperty('ticketstatusid') != $_ticketStatusID) {
                continue;
            }

            if ($_ticketStatus['departmentid'] != '0' && $_SWIFT_TicketObject->GetProperty('departmentid') != $_ticketStatus['departmentid']) {
                continue;
            }

            if ($_ticketStatus['statustype'] == SWIFT_PRIVATE) {
                $_optionContainer[$_index]['title'] = $this->Language->Get('private');

            } else {
                $_optionContainer[$_index]['title'] = htmlspecialchars($_ticketStatus['title']);
                $_ticketStatusTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS,
                    $_ticketStatusID);
                if (!empty($_ticketStatusTitleLanguage)) {
                    $_optionContainer[$_index]['title'] = htmlspecialchars($_ticketStatusTitleLanguage);
                }
            }

            $_optionContainer[$_index]['value'] = $_ticketStatusID;
            $_optionContainer[$_index]['selected'] = false;

            if ($_SWIFT_TicketObject->GetProperty('ticketstatusid') == $_ticketStatusID) {
                $_optionContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $this->Template->Assign('_ticketStatusContainer', $_optionContainer);

        $_ticketPriorityUserGroupContainer = SWIFT_TicketPriority::RetrieveOnUserGroup(SWIFT::Get('usergroupid'));

        // Build Ticket Priority Select Options
        $_optionContainer = [];
        $_index = 0;
        foreach ($_ticketPriorityCache as $_ticketPriorityID => $_ticketPriority) {
            if ($_ticketPriority['type'] == SWIFT_PRIVATE && $_SWIFT_TicketObject->GetProperty('priorityid') != $_ticketPriorityID) {
                continue;

                // Not part of user group?
            }

            if (!isset($_ticketPriorityUserGroupContainer[$_ticketPriorityID])) {
                continue;
            }

            if ($_ticketPriority['type'] == SWIFT_PRIVATE) {
                $_optionContainer[$_index]['title'] = $this->Language->Get('private');

            } else {
                $_optionContainer[$_index]['title'] = htmlspecialchars($_ticketPriority['title']);
                $_ticketPriorityTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY,
                    $_ticketPriorityID);
                if (!empty($_ticketPriorityTitleLanguage)) {
                    $_optionContainer[$_index]['title'] = htmlspecialchars($_ticketPriorityTitleLanguage);
                }
            }

            $_optionContainer[$_index]['value'] = $_ticketPriorityID;
            $_optionContainer[$_index]['selected'] = false;

            if ($_SWIFT_TicketObject->GetProperty('priorityid') == $_ticketPriorityID) {
                $_optionContainer[$_index]['selected'] = true;
            }

            $_index++;
        }
        if ((int)$_SWIFT_TicketObject->GetProperty('priorityid') === 0) {
            array_unshift($_optionContainer, [
                'title' => $this->Language->Get('na'),
                'selected' => true,
                'value' => 0,
            ]);
        }

        $this->Template->Assign('_ticketPriorityContainer', $_optionContainer);

        return true;
    }

    /**
     * @param SWIFT $_SWIFT
     * @param array $_ticketPostContainer
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function doPostProcessing($_SWIFT, $_ticketPostContainer, $_SWIFT_TicketObject)
    {
        $_staffCache = $this->Cache->Get('staffcache');

        $_userImageUserIDList = $_staffImageUserIDList = [];
        foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {
            if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_USER &&
                $_SWIFT_TicketPostObject->GetProperty('userid') != '0' && !in_array($_SWIFT_TicketPostObject->GetProperty('userid'),
                    $_userImageUserIDList)) {
                $_userImageUserIDList[] = $_SWIFT_TicketPostObject->GetProperty('userid');
            } else {
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF &&
                    $_SWIFT_TicketPostObject->GetProperty('staffid') != '0' && !in_array($_SWIFT_TicketPostObject->GetProperty('staffid'),
                        $_staffImageUserIDList)) {
                    $_staffImageUserIDList[] = $_SWIFT_TicketPostObject->GetProperty('staffid');
                }
            }
        }

        $_userProfileImageObjectContainer = SWIFT_UserProfileImage::RetrieveOnUserList($_userImageUserIDList);
        $_staffProfileImageObjectContainer = SWIFT_StaffProfileImage::RetrieveOnStaffList($_staffImageUserIDList);

        // Process attachments
        $_ticketAttachmentContainer = [];
        if ($_SWIFT_TicketObject->GetProperty('hasattachments') == '1') {
            $_ticketAttachmentContainer = $_SWIFT_TicketObject->GetAttachmentContainer();
        }


        $_finalTicketPostContainer = $_ratingContainer = [];

        foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {

            if (($this->Settings->Get('t_cthirdparty') == '0' &&
                    ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_THIRDPARTY || $_SWIFT_TicketPostObject->GetProperty('isthirdparty') == '1')) || $_SWIFT_TicketPostObject->GetProperty('isprivate') == '1') {
                continue;
            }


            $_postFooter = $_postTitle = '';

            $_finalTicketPostContainer[$_ticketPostID] = $_SWIFT_TicketPostObject->GetDataStore();

            $_ticketPostMinimumHeight = 238;

            $_postTitle = sprintf($this->Language->Get('tppostedon'),
                SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketPostObject->GetProperty('dateline')));


            if ($_SWIFT_TicketPostObject->GetProperty('emailto') != '') {
                // Sent as email (New Ticket from Staff CP)
                if ($_ticketPostID == $_SWIFT_TicketObject->GetProperty('firstpostid') && $_SWIFT_TicketObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF) {
                    $_postFooter .= sprintf($this->Language->Get('tpemailto'),
                            htmlspecialchars($_SWIFT_TicketPostObject->GetProperty('emailto'))) . '&nbsp;&nbsp;&nbsp;';

                    // Email Forwarded
                } else {
                    $_postFooter .= sprintf($this->Language->Get('tpemailforwardedto'),
                            htmlspecialchars($_SWIFT_TicketPostObject->GetProperty('emailto'))) . '&nbsp;&nbsp;&nbsp;';

                }
            }

            $_badgeText = $_badgeClass = '';

            if ($_SWIFT_TicketPostObject->GetProperty('dateline') >= $_SWIFT->User->GetProperty('lastvisit')) {
                $_postTitle .= '&nbsp;<span class="postStatusIndicator">NEW</span>';
            }

            $_creatorLabel = 'user';
            if ($_SWIFT_TicketPostObject->GetProperty('isthirdparty') == '1' || $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_THIRDPARTY) {
                $_badgeClass = 'ticketpostbarbadgered';
                $_badgeText = $this->Language->Get('badgethirdparty');
                $_creatorLabel = 'thirdparty';
            } else {
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_CC) {
                    $_badgeClass = 'ticketpostbarbadgered';
                    $_badgeText = $this->Language->Get('badgecc');
                    $_creatorLabel = 'cc';
                } else {
                    if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_BCC) {
                        $_badgeClass = 'ticketpostbarbadgered';
                        $_badgeText = $this->Language->Get('badgebcc');
                        $_creatorLabel = 'bcc';
                    } else {
                        if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_USER) {
                            $_badgeClass = 'ticketpostbarbadgeblue';
                            $_badgeText = $this->Language->Get('badgeuser');
                            $_creatorLabel = 'user';
                        } else {
                            if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF) {
                                $_badgeClass = 'ticketpostbarbadgered';
                                $_badgeText = $this->Language->Get('badgestaff');
                                $_creatorLabel = 'staff';
                            }
                        }
                    }
                }
            }

            $_finalTicketPostContainer[$_ticketPostID]['creatorlabel'] = $_creatorLabel;
            $_finalTicketPostContainer[$_ticketPostID]['posttitle'] = $_postTitle;
            $_finalTicketPostContainer[$_ticketPostID]['postfooter'] = $_postFooter;

            if ($_SWIFT->Settings->Get('t_cstaffname') == '1' && $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF) {
                $_finalTicketPostContainer[$_ticketPostID]['fullname'] = StripName(htmlspecialchars($_SWIFT->Settings->Get('t_cdisplayname')),
                    19);
            } else {
                $_finalTicketPostContainer[$_ticketPostID]['fullname'] = StripName(text_to_html_entities($_SWIFT_TicketPostObject->GetProperty('fullname')),
                    19);
            }

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1224 'Title/Position' under tickets does not show according to the user logged into Client Support Center
             *
             */
            $_finalTicketPostContainer[$_ticketPostID]['designation'] = '';
            $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

            if ($_SWIFT->Settings->Get('t_cstaffname') != '1' && $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF && isset($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]) &&
                !empty($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]['designation'])) {
                $_finalTicketPostContainer[$_ticketPostID]['designation'] = htmlspecialchars($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]['designation']);
            } else {
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_USER && $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetProperty('userdesignation') != '') {
                    $_finalTicketPostContainer[$_ticketPostID]['designation'] = htmlspecialchars($_SWIFT_UserObject->GetProperty('userdesignation'));
                }
            }

            $_finalTicketPostContainer[$_ticketPostID]['badgeclass'] = $_badgeClass;
            $_finalTicketPostContainer[$_ticketPostID]['badgetext'] = $_badgeText;
            $_finalTicketPostContainer[$_ticketPostID]['avatar'] = '';
            $_finalTicketPostContainer[$_ticketPostID]['contents'] = $_SWIFT_TicketPostObject->GetDisplayContents();

            // Begin Avatar Display
            if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_USER &&
                isset($_userProfileImageObjectContainer[$_SWIFT_TicketPostObject->GetProperty('userid')])) {
                $_finalTicketPostContainer[$_ticketPostID]['avatar'] = SWIFT::Get('basename') . '/Base/User/GetProfileImage/' . (int)($_SWIFT_TicketPostObject->GetProperty('userid'));
            } else {
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF &&
                    isset($_staffProfileImageObjectContainer[$_SWIFT_TicketPostObject->GetProperty('staffid')])) {
                    $_finalTicketPostContainer[$_ticketPostID]['avatar'] = SWIFT::Get('basename') . '/Base/Staff/GetProfileImage/' . (int)($_SWIFT_TicketPostObject->GetProperty('staffid'));
                }
            }

            // Process the ticket attachments
            $_finalTicketPostContainer[$_ticketPostID]['attachments'] = [];
            $_finalTicketPostContainer[$_ticketPostID]['hasattachments'] = '0';

            if (isset($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetTicketPostID()]) && _is_array($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetTicketPostID()])) {
                $_finalTicketPostContainer[$_ticketPostID]['hasattachments'] = '1';

                foreach ($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetTicketPostID()] as $_attachmentID => $_attachmentContainer) {
                    $_mimeDataContainer = [];
                    try {
                        $_fileExtension = mb_strtolower(substr($_attachmentContainer['filename'],
                            (strrpos($_attachmentContainer['filename'], '.') + 1)));

                        $_MIMEListObject = new SWIFT_MIMEList();
                        $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
                    } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                        // Do nothing
                    }

                    $_attachmentIcon = 'icon_file.gif';
                    if (isset($_mimeDataContainer[1])) {
                        $_attachmentIcon = $_mimeDataContainer[1];
                    }

                    $_finalTicketPostContainer[$_ticketPostID]['attachments'][$_attachmentID] = [];
                    $_finalTicketPostContainer[$_ticketPostID]['attachments'][$_attachmentID]['icon'] = $_attachmentIcon;
                    $_finalTicketPostContainer[$_ticketPostID]['attachments'][$_attachmentID]['link'] = SWIFT::Get('basename') . '/Tickets/Ticket/GetAttachment/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_attachmentContainer['attachmentid'];
                    $_finalTicketPostContainer[$_ticketPostID]['attachments'][$_attachmentID]['name'] = htmlspecialchars($_attachmentContainer['filename']);
                    $_finalTicketPostContainer[$_ticketPostID]['attachments'][$_attachmentID]['size'] = FormattedSize($_attachmentContainer['filesize']);
                }
            }

            $_ticketPostMinimumHeight += (count($_finalTicketPostContainer[$_ticketPostID]['attachments']) - 1) * 25;
            $_finalTicketPostContainer[$_ticketPostID]['minimumheight'] = $_ticketPostMinimumHeight;
        }

        $this->Template->Assign('_ticketPostContainer', $_finalTicketPostContainer);

        return true;
    }
}
