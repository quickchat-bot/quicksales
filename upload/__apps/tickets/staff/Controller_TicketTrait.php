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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_App;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Library\HTML\SWIFT_HTML;
use SWIFT_Loader;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;

trait Controller_TicketTrait
{
    /**
     * Print a ticket
     *
     * @author Parminder Singh
     * @param int $_ticketID The Ticket ID
     * @param bool $_hasNotes The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     * @throws SWIFT_Exception if the ticket id is empty
     */
    public function PrintTicket($_ticketID, $_hasNotes = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
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
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanviewtickets') == '0') {
            throw new SWIFT_Exception(SWIFT_NOPERMISSION);
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1553 The Print function does not print the reply from top even if setting is correct
         *
         * Comments: None
         */

        $_ticketPostOrder = 'ASC';
        if ($this->Settings->Get('t_postorder') == 'desc') {
            $_ticketPostOrder = 'DESC';
        }

        $_ticketPostContainer = $_SWIFT_TicketObject->GetTicketPosts(false, false, $_ticketPostOrder);
        $_ticketPostArray = array();
        foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {
            $_ticketPostArray[$_ticketPostID]['fullname'] = StripName(text_to_html_entities($_SWIFT_TicketPostObject->GetProperty('fullname')), 19);
            $_designation = '';
            if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF && isset($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]) && !empty($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]['designation'])) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                $_designation = htmlspecialchars($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]['designation']);
                // @codeCoverageIgnoreEnd
            } elseif ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_USER && $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetProperty('userdesignation') != '') {
                $_designation = htmlspecialchars($_SWIFT_UserObject->GetProperty('userdesignation'));
            }

            $_ticketPostArray[$_ticketPostID]['designation'] = $_designation;
            $_creator = 0;
            if ($_SWIFT_TicketPostObject->GetProperty('isthirdparty') == '1' || $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_THIRDPARTY) {
                $_creator = $this->Language->Get('badgethirdparty');
            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_CC) {
                $_creator = $this->Language->Get('badgecc');
            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_BCC) {
                $_creator = $this->Language->Get('badgebcc');
            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_USER) {
                $_creator = $this->Language->Get('badgeuser');
            } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF) {
                $_creator = $this->Language->Get('badgestaff');
            }
            $_ticketPostArray[$_ticketPostID]['creator'] = $_creator;
            $_ticketPostArray[$_ticketPostID]['contents'] = $_SWIFT_TicketPostObject->GetDisplayContents();
            $_ticketPostArray[$_ticketPostID]['date'] = sprintf($this->Language->Get(IIF($_SWIFT_TicketPostObject->GetProperty('issurveycomment') == '1', 'tppostedonsurvey', 'tppostedon')), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketPostObject->GetProperty('dateline')));
        }

        $_departmentTitle = $this->Language->Get('na');
        if ($_SWIFT_TicketObject->GetProperty('departmentid') == '0') {
            $_departmentTitle = $this->Language->Get('trash');
        } else if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
            $_departmentTitle = $_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title'];
        }

        $_ticketStatusContainer = false;
        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            $_ticketStatusContainer = $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')];
            $_ticketStatusTitle = $_ticketStatusContainer['title'];
        } else {
            $_ticketStatusTitle = $this->Language->Get('na');
        }

        $_ticketPriorityContainer = false;
        if (isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')])) {
            $_ticketPriorityContainer = $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')];
            $_ticketPriorityTitle = $_ticketPriorityContainer['title'];
        } else {
            $_ticketPriorityTitle = $this->Language->Get('na');
        }

        $_ticketTypeContainer = false;
        if (isset($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')])) {
            $_ticketTypeContainer = $_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')];
            $_ticketTypeTitle = $_ticketTypeContainer['title'];
        } else {
            $_ticketTypeTitle = $this->Language->Get('na');
        }

        $_ticketOwnerContainer = false;
        if (isset($_staffCache[$_SWIFT_TicketObject->GetProperty('ownerstaffid')])) {
            $_ticketOwnerContainer = $_staffCache[$_SWIFT_TicketObject->GetProperty('ownerstaffid')];
            $_ticketOwnerTitle = $_ticketOwnerContainer['fullname'];
        } else if ($_SWIFT_TicketObject->GetProperty('ownerstaffid') == '0') {
            $_ticketOwnerTitle = $this->Language->Get('unassigned2');
        } else {
            $_ticketOwnerTitle = $this->Language->Get('na');
        }

        $_billingEntries = '';
        if ($_SWIFT_TicketObject->GetProperty('hasbilling') == '1' && $_SWIFT->Staff->GetPermission('staff_tcanviewbilling') != '0') {
            $_totalTimeSpent = $_SWIFT_TicketObject->GetProperty('timeworked');
            $_totalTimeBillable = $_SWIFT_TicketObject->GetProperty('timebilled');
            $_billingEntries = '<div class="ticketbillinginfocontainer">';
            $_billingEntries .= '<img src="' . SWIFT::Get('themepathimages') . 'icon_clock.png' . '" align="absmiddle" border="0" /> ' .
                '<b>' . $this->Language->Get('billtotalworked') . '</b> ' . SWIFT_Date::ColorTime($_totalTimeSpent, false, true) .
                '&nbsp;&nbsp;&nbsp;&nbsp;' .
                '<b>' . $this->Language->Get('billtotalbillable') . '</b> ' . SWIFT_Date::ColorTime($_totalTimeBillable, false, true);
            $_billingEntries .= '</div>';
        }

        $_customFields = $this->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), null, $_SWIFT_TicketObject->GetTicketID(), $_SWIFT_TicketObject->GetProperty('departmentid'), true, true);

        $_notesHTML = '';
        if ($_hasNotes) {
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_notesHTML = $this->View->RenderNotes($_SWIFT_TicketObject, $_SWIFT_UserObject);
            } else {
                $_notesHTML = $this->View->RenderNotes($_SWIFT_TicketObject);
            }
        }

        $this->Template->Assign('_companyName', SWIFT::Get('companyname'));
        $this->Template->Assign('_ticketID', $_SWIFT_TicketObject->GetTicketDisplayID());
        $this->Template->Assign('_ticketSubject', htmlspecialchars($this->Emoji->decode($_SWIFT_TicketObject->GetProperty('subject'))));
        $this->Template->Assign('_departmentTitle', $_departmentTitle);
        $this->Template->Assign('_ticketStatusTitle', $_ticketStatusTitle);
        $this->Template->Assign('_ticketPriorityTitle', $_ticketPriorityTitle);
        $this->Template->Assign('_ticketTypeTitle', $_ticketTypeTitle);
        $this->Template->Assign('_ticketOwnerTitle', $_ticketOwnerTitle);
        $this->Template->Assign('_ticketDate', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->GetProperty('dateline')));
        $this->Template->Assign('_ticketUpdated', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->GetProperty('lastactivity')));
        $this->Template->Assign('_ticketPost', $_ticketPostArray);
        $this->Template->Assign('_customFields', $_customFields);
        $this->Template->Assign('_billingEntries', $_billingEntries);
        $this->Template->Assign('_ticketNotes', $_notesHTML);

        $this->Template->Render('printticket');

        return true;
    }

    /**
     * Render the New Ticket Dialog
     *
     * @author Varun Shoor
     * @param int $_chatObjectID (OPTIONAL) The Chat Object ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function NewTicket($_chatObjectID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertticket') == '0') {
            $this->UserInterface->Header(
                $this->Language->Get('tickets') . ' > ' . $this->Language->Get('newticket'),
                self::MENU_ID,
                self::NAVIGATION_ID
            );
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;
        }

        if (!is_numeric($_chatObjectID)) {
            $_chatObjectID = false;
        }

        $this->UserInterface->Header(
            $this->Language->Get('tickets') . ' > ' . $this->Language->Get('newticket'),
            self::MENU_ID,
            self::NAVIGATION_ID
        );
        $this->View->RenderNewTicketDialog($_chatObjectID);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the New Ticket Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function NewTicketForm()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertticket') == '0') {
            $this->UserInterface->Header(
                $this->Language->Get('tickets') . ' > ' . $this->Language->Get('newticket'),
                self::MENU_ID,
                self::NAVIGATION_ID
            );
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;
        }

        if (!isset($_POST['departmentid']) || !isset($_POST['tickettype']) || empty($_POST['departmentid']) || empty($_POST['tickettype'])) {
            $this->Load->NewTicket();

            return false;
        }

        if (!in_array($_POST['departmentid'], $_assignedDepartmentIDList) && $_SWIFT->Settings->Get('t_restrictnewticket') == '1') {
            throw new SWIFT_Exception('No Permission to Department');
        }

        $_finalTicketType = View_Ticket::TAB_NEWTICKET_EMAIL;
        if ($_POST['tickettype'] === 'user') {
            $_finalTicketType = View_Ticket::TAB_NEWTICKET_USER;
        }

        /**
         * ---------------------------------------------
         * LIVE CHAT INTEGRATION
         * ---------------------------------------------
         */

        if (SWIFT_App::IsInstalled(APP_LIVECHAT) && isset($_POST['chatobjectid']) && !empty($_POST['chatobjectid'])) {
            SWIFT_Loader::LoadModel('Chat:Chat', APP_LIVECHAT);
            SWIFT_Loader::LoadModel('Chat:ChatQueue', APP_LIVECHAT);

            $_SWIFT_ChatObject = false;
            try {
                $_SWIFT_ChatObject = new SWIFT_Chat($_POST['chatobjectid']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if ($_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded() && $_SWIFT_ChatObject->CanAccess($_SWIFT->Staff)) {
                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1428 "Skip User Details" option when used while Tag Generation, throws an error while ticket creation from KD
                 *
                 * Comments: Falls back to staff email address if no user email is present
                 */
                $_userEmailAddress = $_SWIFT_ChatObject->GetProperty('useremail');
                if (empty($_userEmailAddress)) {
                    $_userEmailAddress = $_SWIFT->Staff->GetProperty('email');
                }

                // Now attempt to load the data and integrate it into _POST
                $_chatDataArray = $_SWIFT_ChatObject->GetConversationArray();

                $_conversation = $this->Language->Get('ntchatid') . $_SWIFT_ChatObject->GetProcessedChatID() . SWIFT_CRLF;
                $_conversation .= $this->Language->Get('ntchatuserfullname') . $_SWIFT_ChatObject->GetProperty('userfullname') . SWIFT_CRLF;
                $_conversation .= $this->Language->Get('ntchatuseremail') . $_userEmailAddress . SWIFT_CRLF;
                $_conversation .= $this->Language->Get('ntchatstafffullname') . $_SWIFT_ChatObject->GetProperty('staffname') . SWIFT_CRLF;
                $_conversation .= $this->Language->Get('ntchatdepartment') . $_SWIFT_ChatObject->GetProperty('departmenttitle') . SWIFT_CRLF . SWIFT_CRLF;

                foreach ($_chatDataArray as $_val) {
                    if ($_val['type'] != SWIFT_ChatQueue::MESSAGE_SYSTEM && $_val['type'] != SWIFT_ChatQueue::MESSAGE_STAFF && $_val['type'] != SWIFT_ChatQueue::MESSAGE_CLIENT) {
                        continue;
                    }

                    if ($this->Settings->Get('livechat_timestamps') == true) {
                        $_conversation .= '' . $_val['timestamp'] . ' ';
                    }

                    if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_SYSTEM) {
                        $_conversation .= '* ' . $_val['messagehtml'] . SWIFT_CRLF;
                    } else if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_STAFF || $_val['type'] == SWIFT_ChatQueue::MESSAGE_CLIENT) {
                        $_conversation .= $_val['name'] . ': ' . $_val['messagehtml'] . SWIFT_CRLF;
                    }
                }

                $_POST['newticketsubject']              = $_SWIFT_ChatObject->GetProperty('subject');
                $_POST['newticketcontents']             = nl2br($_conversation);
                $_POST['containertaginput_newticketto'] = array($_userEmailAddress);

                // Do we need to load up the user?
                if ($_POST['tickettype'] === 'user' && $_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_CLIENT) {
                    $_SWIFT_UserObject = false;
                    if ($_SWIFT_ChatObject->GetProperty('userid') != '0') {
                        // Attempt to load the user
                        try {
                            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_ChatObject->GetProperty('userid')));
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                            return false;
                        }
                    }

                    // Couldnt load user from ID? attempt to load it from email
                    if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                        // Do we have a valid template group?
                        $_userGroupID = false;

                        if (isset($_templateGroupCache[$_SWIFT_ChatObject->GetProperty('tgroupid')])) {
                            $_userGroupID = $_templateGroupCache[$_SWIFT_ChatObject->GetProperty('tgroupid')]['regusergroupid'];

                            // Go through the master group
                        } else {
                            $_masterTemplateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
                            $_userGroupID = $_templateGroupCache[$_masterTemplateGroupID]['regusergroupid'];
                        }

                        if (empty($_userGroupID)) {
                            throw new SWIFT_Exception('Invalid User Group ID');
                        }

                        $_userID = SWIFT_Ticket::GetOrCreateUserID($_SWIFT_ChatObject->GetProperty('userfullname'), $_userEmailAddress, $_userGroupID, false, false);

                        $_POST['userid'] = $_SWIFT_ChatObject->GetProperty('userfullname');
                        $_POST['autocomplete_userid'] = $_userID;
                    } else {
                        $_POST['userid'] = $_SWIFT_UserObject->GetProperty('fullname');
                        $_POST['autocomplete_userid'] = $_SWIFT_UserObject->GetUserID();
                    }
                }
            }
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_TicketViewRenderer::RenderTree('inbox', -1, -1, -1));
        $this->UserInterface->Header(
            $this->Language->Get('tickets') . ' > ' . $this->Language->Get('newticket'),
            self::MENU_ID,
            self::NAVIGATION_ID
        );
        $this->View->RenderNewTicket($_finalTicketType, (int) ($_POST['departmentid']));
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * New Ticket Submission Processor
     *
     * @author Varun Shoor
     * @param string $_ticketType The Ticket Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function NewTicketSubmit($_ticketType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_ticketType !== 'sendmail' && $_ticketType !== 'user') {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertticket') == '0') {
            return false;
        }

        /**
         * ---------------------------------------------
         * Begin Error Checking
         * ---------------------------------------------
         */
        $_POST['tickettype'] = $_ticketType;

        $_POST['departmentid'] = $_POST['newticketdepartmentid'];

        if (trim($_POST['newticketsubject']) == '' || trim($_POST['newticketcontents']) == '') {

            $this->UserInterface->CheckFields('newticketsubject', 'newticketcontents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->NewTicketForm();

            return false;

            // Only check TO field for the Send Mail Option
        }

        if ($_ticketType === 'sendmail' && (!SWIFT_UserInterface::GetMultipleInputValues('newticketto') ||
            !_is_array(SWIFT_UserInterface::GetMultipleInputValues('newticketto')) || !$this->_CheckPOSTEmailContainer('newticketto'))) {

            SWIFT::ErrorField('newticketto');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->NewTicketForm();

            return false;

            // Check sanity of all properties
        }

        if (
            !isset($_POST['newticketdepartmentid']) || empty($_POST['newticketdepartmentid']) ||
            !isset($_POST['newticketticketstatusid']) || empty($_POST['newticketticketstatusid']) ||
            !isset($_POST['newticketownerstaffid']) ||
            !isset($_POST['newtickettickettypeid']) || empty($_POST['newtickettickettypeid']) ||
            !isset($_POST['newticketticketpriorityid']) || empty($_POST['newticketticketpriorityid'])
        ) {
            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->NewTicketForm();

            return false;

            // Check User ID
        }

        if ($_ticketType === 'user' && (!isset($_POST['autocomplete_userid']) || empty($_POST['autocomplete_userid']))) {
            SWIFT::ErrorField('userid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->NewTicketForm();

            return false;
        }

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(
            SWIFT_CustomFieldManager::MODE_POST,
            SWIFT_UserInterface::MODE_INSERT,
            array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET),
            SWIFT_CustomFieldManager::CHECKMODE_STAFF,
            $_POST['newticketdepartmentid']
        );
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->NewTicketForm();

            return false;
        }

        // Load the user object for the user based tickets
        $_SWIFT_UserObject = false;
        if ($_ticketType === 'user') {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_POST['autocomplete_userid']));
            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-1121 - Macros do not assign ownership
         */
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if (!in_array($_POST['departmentid'], $_assignedDepartmentIDList) && $_SWIFT->Settings->Get('t_restrictnewticket') == '1') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $recurrenceStartDateline = 0;
        $recurrenceEndDateline   = 0;
        if (isset($_POST['recurrencetype']) && (int)$_POST['recurrencetype'] > 0) {
            $_tz = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $recurrenceStartDateline = GetCalendarDateline($_POST['recurrence_start']);
            $recurrenceEndDateline   = GetCalendarDateline($_POST['recurrence_enddateline']);
            $today = strtotime('today GMT');
            date_default_timezone_set($_tz);

            if (isset($_POST['recurrence_start']) && !empty($recurrenceStartDateline) && $recurrenceStartDateline < $today) {
                SWIFT::ErrorField('recurrence_start');
                $this->UserInterface->Error(
                    $this->Language->Get('titlefieldinvalid'),
                    $this->Language->Get('msgpastdate') . ': ' . $this->Language->Get('recur_starts')
                );
                $this->Load->NewTicketForm();

                return false;
            }

            if (isset($_POST['recurrence_enddateline']) && !empty($recurrenceEndDateline) && $recurrenceEndDateline < $today) {
                SWIFT::ErrorField('recurrence_enddateline');
                $this->UserInterface->Error(
                    $this->Language->Get('titlefieldinvalid'),
                    $this->Language->Get('msgpastdate') . ': ' . $this->Language->Get('recur_ends')
                );
                $this->Load->NewTicketForm();

                return false;
            }
        }

        // Check for valid attachments
        $_attachmentCheckResult = static::CheckForValidAttachments('newticketattachments');
        if ($_attachmentCheckResult[0] == false && _is_array($_attachmentCheckResult[1])) {
            $_SWIFT::Error($this->Language->Get('error'), sprintf($this->Language->Get('invalidattachments'), implode(', ', $_attachmentCheckResult[1])));

            $this->Load->NewTicketForm();

            return false;
        }

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
        $_emailQueueID = 0;
        if ($_POST['newticketfrom'] > 0 && isset($_emailQueueCache['list'][$_POST['newticketfrom']])) {
            $_emailQueueID = $_POST['newticketfrom'];
        }

        $_dontSendEmail = false;
        if (!isset($_POST['optnewticket_sendemail']) || (isset($_POST['optnewticket_sendemail']) && $_POST['optnewticket_sendemail'] != '1')) {
            $_dontSendEmail = true;
        }

        $_dispatchAutoResponder = false;
        if ($_ticketType !== 'sendmail' && isset($_POST['optnewticket_sendar']) && $_POST['optnewticket_sendar'] == '1') {
            $_dispatchAutoResponder = true;
        }

        $_isPrivate = false;
        if (isset($_POST['optnewticket_private']) && $_POST['optnewticket_private'] == '1') {
            $_dontSendEmail = true;
            $_isPrivate = true;
        }

        // Create the ticket
        $_creatorFullName = $_creatorEmail = $_phoneNumber = $_destinationEmail = '';
        $_userID = 0;
        $_ticketPhoneType = SWIFT_Ticket::TYPE_DEFAULT;
        $_ticketCreator = SWIFT_Ticket::CREATOR_STAFF;
        $_destinationEmailContainer = self::GetSanitizedEmailList('newticketto');
        $_recurTicketType = SWIFT_TicketRecurrence::TICKETTYPE_ASUSER;

        /**
         * BUG FIX - Werner Garcia
         * KAYAKOC-6832 - Raw HTML/XML in ticket content
         *
         * Comments - handle HTML correctly according to settings
         */
        $_isHTML = SWIFT_HTML::DetectHTMLContent($_POST['newticketcontents']);
        $_htmlSetting = $_SWIFT->Settings->GetString('t_ochtml');
        $_ticketContents = SWIFT_TicketPost::GetParsedContents($_POST['newticketcontents'], $_htmlSetting, $_isHTML);

        // only clear the subject from vulnerable tags at this point
        $_ticketSubject = removeTags($_POST['newticketsubject']);

        $_ownerStaffID = 0;
        if (isset($_POST['newticketownerstaffid']) && $_POST['newticketownerstaffid'] != 0) {
            $_ownerStaffID = (int) ($_POST['newticketownerstaffid']);
        }

        $_ticketContents .=  SWIFT_CRLF;

        if (
            $_SWIFT->Settings->GetBool('t_tinymceeditor') &&
            $_SWIFT->Settings->Get('t_chtml') === 'entities'
        ) {
            $_ticketContents = htmlspecialchars_decode($_ticketContents);
        }

        if ($_ticketType === 'sendmail') {
            $_recurTicketType = SWIFT_TicketRecurrence::TICKETTYPE_SENDEMAIL;
            $_creatorFullName = $_SWIFT->Staff->GetProperty('fullname');
            $_creatorEmail = $_SWIFT->Staff->GetProperty('email');

            $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID] ?? false;

            $_templateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();

            if (_is_array($_emailQueueContainer) && isset($_templateGroupCache[$_emailQueueContainer['tgroupid']])) {
                $_templateGroupID = $_emailQueueContainer['tgroupid'];
            }

            $_templateGroupContainer = $_templateGroupCache[$_templateGroupID];
            $_destinationEmail = $_destinationEmailContainer[0];

            $_userID = SWIFT_Ticket::GetOrCreateUserID($_destinationEmail, $_destinationEmail, $_templateGroupContainer['regusergroupid']);

            $_ticketCreator = SWIFT_Ticket::CREATOR_STAFF;
            $_phoneNumber = '';
        } else if ($_ticketType === 'user') {
            $_recurTicketType = SWIFT_TicketRecurrence::TICKETTYPE_ASUSER;
            $_creatorFullName = $_SWIFT_UserObject->GetProperty('fullname');
            $_creatorEmailList = $_SWIFT_UserObject->GetEmailList();
            $_creatorEmail = $_creatorEmailList[0];

            $_userID = $_SWIFT_UserObject->GetUserID();

            if (isset($_POST['optnewticket_isphone']) && $_POST['optnewticket_isphone'] == '1') {
                $_ticketPhoneType = SWIFT_Ticket::TYPE_PHONE;
            }

            $_ticketCreator = SWIFT_Ticket::CREATOR_USER;
            $_phoneNumber = $_SWIFT_UserObject->GetProperty('phone');
        }

        /**
         * Ticket signature using same logic implemented at SWIFT_TicketGettersTrait:813
         */
        $_ticketContentsFirstPost = $_ticketContents;

        if ($_ticketType === 'sendmail') {
            $_signatureContents = '';
            // Add Staff Signature to Signature content
            $_staffSignatureContents = $_SWIFT->Staff->GetProperty('signature');

            if ($_isHTML) {
                if (!SWIFT_HTML::DetectHTMLContent($_staffSignatureContents)) {
                    $_staffSignatureContents = nl2br($_staffSignatureContents);
                }
                $_signatureContents .= $_staffSignatureContents;
            } else {
                $_staffSignatureContents = strip_tags($_staffSignatureContents);
                $_signatureContents .= preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_staffSignatureContents);
            }

            // Add Queue Signature to Signature content
            if (_is_array($_emailQueueCache) && isset($_emailQueueCache['list'][$_emailQueueID])) {
                $_emailQueueContainer = $_emailQueueCache['list'][$_emailQueueID];
                if ($_signatureContents != '') {
                    $_signatureContents .= SWIFT_CRLF;
                }
                if ($_isHTML) {
                    if ($_signatureContents != '') {
                        $_signatureContents .= '<br />';
                    }
                    if (!SWIFT_HTML::DetectHTMLContent($_emailQueueContainer['contents'])) {
                        $_emailQueueContainer['contents'] = nl2br($_emailQueueContainer['contents']);
                    }
                    $_signatureContents .= $_emailQueueContainer['contents'];
                } else {
                    $_emailQueueContainer['contents'] = strip_tags($_emailQueueContainer['contents']);
                    $_signatureContents .= preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_emailQueueContainer['contents']);
                }
            }

            //Append Signature to Post
            if (!empty($_signatureContents)) {
                $_ticketContentsFirstPost = $_ticketContents . SWIFT_CRLF . $_signatureContents;
            }
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::Create(
            $_ticketSubject,
            $_creatorFullName,
            $_creatorEmail,
            $_ticketContentsFirstPost,
            $_ownerStaffID,
            $_POST['newticketdepartmentid'],
            $_POST['newticketticketstatusid'],
            $_POST['newticketticketpriorityid'],
            $_POST['newtickettickettypeid'],
            $_userID,
            $_SWIFT->Staff->GetStaffID(),
            $_ticketPhoneType,
            $_ticketCreator,
            SWIFT_Ticket::CREATIONMODE_STAFFCP,
            $_phoneNumber,
            $_emailQueueID,
            false,
            $_destinationEmail,
            $_isHTML,
            DATENOW,
            $_isPrivate
        );

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_TicketPostObject = $_SWIFT_TicketObject->GetFirstPostObject();
        $_SWIFT_TicketObject->ProcessPostAttachments($_SWIFT_TicketPostObject, 'newticketattachments');

        $_ticketDispatchContents = $_ticketContents = SWIFT_TicketPost::SmartReply($_SWIFT_TicketObject, $_ticketContents);

        $_signatureContentsDefault = $_signatureContentsHTML = '';
        if ($_ticketType === 'sendmail') {
            $_signatureContentsDefault = $_SWIFT_TicketObject->GetSignature(false, $_SWIFT->Staff);
            $isHTML = !SWIFT_HTML::DetectHTMLContent($_signatureContentsDefault);
            $_signatureContentsHTML = $_SWIFT_TicketObject->GetSignature($isHTML, $_SWIFT->Staff);
        }

        $_ticketID = $_SWIFT_TicketObject->GetTicketID();

        $this->_ProcessDispatchTab($_SWIFT_TicketObject, 'newticket');

        // Begin Hook: staff_newticket_create
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_newticket_create')) ? eval($_hookCode) : false;
        // End Hook

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-303 Staff Replies and Autoresponder are sending wrong template group name in the Support Center URL
         * SWIFT-1004 Survey e-mail is always sent in English, even if other language is set as default
         *
         * Comments: Reset the template group of the ticket
         */
        if ($_SWIFT_UserObject === false) {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        }

        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userTemplateGroupID = $_SWIFT_UserObject->GetTemplateGroupID();

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-4268 Issue with ticket recurrence, if ticket is created using 'As a user' option.
             *
             * Comments: If user group is not linked with any template then set the default template.
             */
            if (!empty($_userTemplateGroupID) && isset($_templateGroupCache[$_userTemplateGroupID])) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                $_SWIFT_TicketObject->SetTemplateGroup($_userTemplateGroupID);
            } else {
                // @codeCoverageIgnoreEnd
                $_userTemplateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
                $_SWIFT_TicketObject->SetTemplateGroup($_userTemplateGroupID);
            }
        }

        /**
         * FEATURE - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-3186 Custom field data in autoresponders, ticket notifications.
         *
         * Comments: Update the custom field values in the database before dispatching the auto responder.
         */

        // Update Custom Field Values
        $this->CustomFieldManager->Update(
            SWIFT_CustomFieldManager::MODE_POST,
            SWIFT_UserInterface::MODE_INSERT,
            array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET),
            SWIFT_CustomFieldManager::CHECKMODE_STAFF,
            $_SWIFT_TicketObject->GetTicketID(),
            $_POST['newticketdepartmentid']
        );

        // We dispatch the autoresponder after creation so that we end up adding all the CC users etc.
        if ($_dispatchAutoResponder) {
            $_SWIFT_TicketObject->DispatchAutoresponder();
        }

        $_isHTML = SWIFT_HTML::DetectHTMLContent($_ticketDispatchContents);

        $_fromEmailAddress = self::_GetDispatchFromEmail('newticket');

        if ($_ticketContents != '' && $_dontSendEmail == false) {
            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-4998 Missing " > " while using quote button in case tinymce is disabled
             */
            if ($_SWIFT->Settings->Get('t_chtml') == 'entities') {
                $_isHTML                = false;
                $_signatureContentsHTML = nl2br(htmlspecialchars($_signatureContentsDefault));
            }

            // Carry out the email dispatch logic
            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject);
            $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply($_SWIFT->Staff, $_ticketDispatchContents, $_isHTML, $_fromEmailAddress, array($_signatureContentsDefault, $_signatureContentsHTML));
        }

        if (isset($_POST['optnewticket_watch']) && $_POST['optnewticket_watch'] == '1') {
            SWIFT_Ticket::Watch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        } else {
            SWIFT_Ticket::UnWatch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        }

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        // Activity Log
        $description = sprintf(
            $_SWIFT->Language->Get('log_newticket') ?: '%s %s',
            $_SWIFT_TicketObject->GetTicketDisplayID(),
            $_ticketSubject
        );
        SWIFT_StaffActivityLog::AddToLog(
            $description,
            SWIFT_StaffActivityLog::ACTION_INSERT,
            SWIFT_StaffActivityLog::SECTION_TICKETS,
            SWIFT_StaffActivityLog::INTERFACE_STAFF
        );

        // Do we need to add a macro
        $_addMacro = false;
        if (isset($_POST['optnewticket_addmacro']) && $_POST['optnewticket_addmacro'] == '1' && $_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            $_addMacro = true;
        }

        // Do we need to add a KB article
        $_addKBArticle = false;
        if (isset($_POST['optnewticket_addkb']) && $_POST['optnewticket_addkb'] == '1' && $_SWIFT_TicketPostObject instanceof SWIFT_TicketPost && $_SWIFT_TicketPostObject->GetIsClassLoaded() && SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE)) {
            $_addKBArticle = true;
        }


        /**
         * ---------------------------------------------
         * RECURRENCE PROCESSING
         * ---------------------------------------------
         */
        // Check permission & recurrence type
        if (
            $_SWIFT->Staff->GetPermission('staff_tcaninsertrecurrence') != '0' &&
            isset($_POST['recurrencetype']) && (int)$_POST['recurrencetype'] > 0
        ) {
            $_endType = SWIFT_TicketRecurrence::END_NOEND;

            if (isset($_POST['recurrence_endtype']) && $_POST['recurrence_endtype'] == SWIFT_TicketRecurrence::END_DATE && $recurrenceEndDateline != false) {
                $_endType = SWIFT_TicketRecurrence::END_DATE;
            } else if (isset($_POST['recurrence_endtype']) && $_POST['recurrence_endtype'] == SWIFT_TicketRecurrence::END_OCCURENCES && (int) ($_POST['recurrence_endcount']) > 0) {
                $_endType = SWIFT_TicketRecurrence::END_OCCURENCES;
            }

            // Daily
            if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_DAILY) {
                $_intervalStep = (int) ($_POST['recurrence_daily_step']);
                $_dailyEveryWeekday = false;
                if ((empty($_intervalStep) || $_intervalStep < 0) && $_POST['recurrence_daily_type'] === 'default') {
                    $_intervalStep = 1;
                }

                if ($_POST['recurrence_daily_type'] === 'extended') {
                    $_intervalStep = 0;
                    $_dailyEveryWeekday = true;
                }

                SWIFT_TicketRecurrence::CreateDaily(
                    $_SWIFT_TicketObject,
                    $_SWIFT->Staff,
                    $_recurTicketType,
                    $_POST['newticketdepartmentid'],
                    $_POST['newticketownerstaffid'],
                    $_POST['newtickettickettypeid'],
                    $_POST['newticketticketstatusid'],
                    $_POST['newticketticketpriorityid'],
                    $_intervalStep,
                    $_dailyEveryWeekday,
                    $recurrenceStartDateline,
                    $_endType,
                    $recurrenceEndDateline,
                    (int) ($_POST['recurrence_endcount']),
                    $_dontSendEmail,
                    $_dispatchAutoResponder
                );

                // Weekly
            } else if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_WEEKLY) {
                $_intervalStep = (int) ($_POST['recurrence_weekly_step']);
                if ((empty($_intervalStep) || $_intervalStep < 0)) {
                    $_intervalStep = 1;
                }

                $_isMonday = $_isTuesday = $_isWednesday = $_isThursday = $_isFriday = $_isSaturday = $_isSunday = false;
                if (isset($_POST['recurrence_weekly_ismonday'])) {
                    $_isMonday = true;
                }

                if (isset($_POST['recurrence_weekly_istuesday'])) {
                    $_isTuesday = true;
                }

                if (isset($_POST['recurrence_weekly_iswednesday'])) {
                    $_isWednesday = true;
                }

                if (isset($_POST['recurrence_weekly_isthursday'])) {
                    $_isThursday = true;
                }

                if (isset($_POST['recurrence_weekly_isfriday'])) {
                    $_isFriday = true;
                }

                if (isset($_POST['recurrence_weekly_issaturday'])) {
                    $_isSaturday = true;
                }

                if (isset($_POST['recurrence_weekly_issunday'])) {
                    $_isSunday = true;
                }

                SWIFT_TicketRecurrence::CreateWeekly(
                    $_SWIFT_TicketObject,
                    $_SWIFT->Staff,
                    $_recurTicketType,
                    $_POST['newticketdepartmentid'],
                    $_POST['newticketownerstaffid'],
                    $_POST['newtickettickettypeid'],
                    $_POST['newticketticketstatusid'],
                    $_POST['newticketticketpriorityid'],
                    $_intervalStep,
                    $_isMonday,
                    $_isTuesday,
                    $_isWednesday,
                    $_isThursday,
                    $_isFriday,
                    $_isSaturday,
                    $_isSunday,
                    $recurrenceStartDateline,
                    $_endType,
                    $recurrenceEndDateline,
                    (int) ($_POST['recurrence_endcount']),
                    $_dontSendEmail,
                    $_dispatchAutoResponder
                );

                // Monthly
            } else if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_MONTHLY) {
                $_recurrenceMonthlyType  = SWIFT_TicketRecurrence::MONTHLY_DEFAULT;
                $_monthlyExtendedDay     = 'monday';
                $_monthlyExtendedDayStep = 'first';
                $_intervalStep           = 1;
                $_monthlyDay             = date('d');

                if ($_POST['recurrence_monthly_type'] === 'extended') {
                    $_recurrenceMonthlyType  = SWIFT_TicketRecurrence::MONTHLY_EXTENDED;
                    $_monthlyExtendedDay     = $_POST['recurrence_monthly_extday'];
                    $_monthlyExtendedDayStep = $_POST['recurrence_monthly_extdaystep'];

                    $_intervalStep = (int) ($_POST['recurrence_monthly_stepext']);
                } else {
                    $_intervalStep = (int) ($_POST['recurrence_monthly_step']);
                    if ((int) ($_POST['recurrence_monthly_day']) != '0' && (int) ($_POST['recurrence_monthly_day']) > 0) {
                        $_monthlyDay = (int) ($_POST['recurrence_monthly_day']);
                    }
                }

                if ((empty($_intervalStep) || $_intervalStep < 0)) {
                    $_intervalStep = 1;
                }

                SWIFT_TicketRecurrence::CreateMonthly(
                    $_SWIFT_TicketObject,
                    $_SWIFT->Staff,
                    $_recurTicketType,
                    $_POST['newticketdepartmentid'],
                    $_POST['newticketownerstaffid'],
                    $_POST['newtickettickettypeid'],
                    $_POST['newticketticketstatusid'],
                    $_POST['newticketticketpriorityid'],
                    $_intervalStep,
                    $_recurrenceMonthlyType,
                    $_monthlyDay,
                    $_monthlyExtendedDay,
                    $_monthlyExtendedDayStep,
                    $recurrenceStartDateline,
                    $_endType,
                    $recurrenceEndDateline,
                    (int) ($_POST['recurrence_endcount']),
                    $_dontSendEmail,
                    $_dispatchAutoResponder
                );

                // Yearly
            } else if ($_POST['recurrencetype'] == SWIFT_TicketRecurrence::INTERVAL_YEARLY) {
                $_yearlyType            = SWIFT_TicketRecurrence::YEARLY_DEFAULT;
                $_yearlyMonthDay        = gmdate('d', DATENOW);
                $_yearlyMonth           = gmdate('n', DATENOW);
                $_yearlyExtendedDay     = 'first';
                $_yearlyExtendedDayStep = 'monday';
                $_yearlyExtendedMonth   = $_yearlyMonth;

                if ($_POST['recurrence_yearly_type'] === 'extended') {
                    $_yearlyType            = SWIFT_TicketRecurrence::YEARLY_EXTENDED;
                    $_yearlyExtendedDay     = $_POST['recurrence_yearly_extday'];
                    $_yearlyExtendedDayStep = $_POST['recurrence_yearly_extdaystep'];
                    $_yearlyExtendedMonth   = $_POST['recurrence_yearly_extmonth'];
                } else {
                    $_yearlyMonthDay = (int) ($_POST['recurrence_yearly_monthday']);
                    if (empty($_yearlyMonthDay) || $_yearlyMonthDay <= 0) {
                        $_yearlyMonthDay = gmdate('d', DATENOW);
                    }

                    $_yearlyMonth = (int) ($_POST['recurrence_yearly_month']);
                }

                SWIFT_TicketRecurrence::CreateYearly(
                    $_SWIFT_TicketObject,
                    $_SWIFT->Staff,
                    $_recurTicketType,
                    $_POST['newticketdepartmentid'],
                    $_POST['newticketownerstaffid'],
                    $_POST['newtickettickettypeid'],
                    $_POST['newticketticketstatusid'],
                    $_POST['newticketticketpriorityid'],
                    $_yearlyType,
                    $_yearlyMonth,
                    $_yearlyMonthDay,
                    $_yearlyExtendedDay,
                    $_yearlyExtendedDayStep,
                    $_yearlyExtendedMonth,
                    $recurrenceStartDateline,
                    $_endType,
                    $recurrenceEndDateline,
                    (int) ($_POST['recurrence_endcount']),
                    $_dontSendEmail,
                    $_dispatchAutoResponder
                );
            }
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-5097 Infinite loop with several custom fields.
         *
         * Comments: Global array were conflicting with custom fields.
         */
        $GLOBALS['_POST']           = $GLOBALS['_REQUEST'] = $GLOBALS['_GET'] = array();
        $GLOBALS['_POST']['isajax'] = true;

        // Does the new department belong to this staff? if not, we need to jump him back to list!
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if (
            isset($_POST['newticketdepartmentid']) && !empty($_POST['newticketdepartmentid']) &&
            !in_array($_POST['newticketdepartmentid'], $_assignedDepartmentIDList)
        ) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            if ($_addMacro) {
                $this->Load->Controller('MacroReply')->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'inbox', -1, -1, -1, $_addKBArticle);
            } else if ($_addKBArticle) {
                $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'inbox', -1, -1, -1);
            } else {
                $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);
            }

            return true;
            // @codeCoverageIgnoreEnd
        }

        if ($_addMacro) {
            $this->Load->Controller('MacroReply')->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'viewticket', -1, -1, -1, $_addKBArticle);
        } else if ($_addKBArticle) {
            $this->Load->Controller('Article', APP_KNOWLEDGEBASE)->InsertTicket($_SWIFT_TicketPostObject, $_ticketID, 'viewticket', -1, -1, -1);
        } else {
            $this->Load->Method('View', $_SWIFT_TicketObject->GetTicketID(), 'inbox', -1, -1, -1, 0);
        }

        return true;
    }

    /**
     * Split a ticket
     *
     * @author Simaranjit Singh
     *
     * @param int $_ticketID
     *
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded OR If Access Denied
     */
    public function SplitTicket($_ticketID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_tcansplitticket') == '0') {
            throw new SWIFT_Exception(SWIFT_NOPERMISSION);
        }

        return $this->View->RenderSplitOrDuplicate($_ticketID, SWIFT_Ticket::MODE_SPLIT);
    }

    /**
     * Duplicate a ticket
     *
     * @author Simaranjit Singh
     *
     * @param int $_ticketID
     *
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded OR If Access Denied
     */
    public function DuplicateTicket($_ticketID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_tcanduplicateticket') == '0') {
            throw new SWIFT_Exception(SWIFT_NOPERMISSION);
        }

        return $this->View->RenderSplitOrDuplicate($_ticketID, SWIFT_Ticket::MODE_DUPLICATE);
    }
}
