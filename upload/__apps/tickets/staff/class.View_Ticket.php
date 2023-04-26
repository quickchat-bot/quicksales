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

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use SWIFT_DataID;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Base\Library\HTML\SWIFT_HTML;
use SWIFT_LanguageEngine;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\Watcher\SWIFT_TicketWatcher;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Ticket Display View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Ticket $Controller
 * @author Varun Shoor
 */
class View_Ticket extends SWIFT_View
{
    use View_TicketRenderTrait;
    use View_TicketDispatchTrait;
    use View_TicketBillingTrait;
    use View_TicketFollowUpTrait;
    use View_TicketNewTicketTrait;
    use View_TicketNoteTrait;
    use View_TicketRecurrenceTrait;
    use View_TicketBoxesTrait;
    use View_TicketReleaseTrait;

    const TYPE_DEPARTMENT = 1;
    const TYPE_OWNER = 2;
    const TYPE_STATUS = 3;
    const TYPE_TYPE = 4;
    const TYPE_PRIORITY = 5;
    const TYPE_FLAG = 6;

    const TAB_REPLY = 1;
    const TAB_FORWARD = 2;
    const TAB_NEWTICKET_EMAIL = 3;
    const TAB_NEWTICKET_USER = 4;

    /**
     * Render the Edit Post Form
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Ticket Object
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The Ticket Post Object
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderEditPost(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_TicketPost $_SWIFT_TicketPostObject, $_listType = 'inbox',
            $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1, $_ticketLimitOffset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this) . 'post', '/Tickets/Ticket/EditPostSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' .
                                                             $_SWIFT_TicketPostObject->GetTicketPostID() . '/' . $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' .
                                                             $_ticketTypeID . '/' . $_ticketLimitOffset, SWIFT_UserInterface::MODE_EDIT, true);

        if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
                                                 SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
        */
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4941 Check Custom Tweaks compatibility with SWIFT
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'generaledit', true);

        $_contents = $this->Emoji->decode($_SWIFT_TicketPostObject->GetProperty('contents'));

        $_isHTML = SWIFT_HTML::DetectHTMLContent($_contents);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
        */
        if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0') {

            if ($_isHTML && !empty($_contents)) {
                $_Ticket                    = new SWIFT_Ticket(new SWIFT_DataID($_SWIFT_TicketPostObject->Get('ticketid')));
                $_ticketAttachmentContainer = IIF($_Ticket->Get('hasattachments') == '1', $_Ticket->GetAttachmentContainer(), []);

                if (isset($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetID()]) && _is_array($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetID()])) {
                    foreach ($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetID()] as $_attachmentContainer) {
                        $_fileExtension = mb_strtolower(mb_substr($_attachmentContainer['filename'], (mb_strrpos($_attachmentContainer['filename'], '.') + 1)));

                        /**
                         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                         *
                         * SWIFT-4995 Unable to quote posts with inline attachments older than 3 days
                         */
                        if (in_array($_fileExtension, ['gif', 'jpg', 'png', 'jpeg'])) {

                            $_attachmentObject = new SWIFT_Attachment($_attachmentContainer['attachmentid']);
                            $_contents         = str_replace('cid:' . $_attachmentContainer['contentid'], 'data:image/' . $_fileExtension . ';base64,' . $_attachmentObject->GetBase64Encoded(), $_contents);
                        }
                    }
                }
            }

            if (!SWIFT_HTML::DetectHTMLContent($_contents)) {
                $_contents = nl2br($_contents);
            }

            /**
             * BUG FIX  - Verem Dugeri <verem.dugeri@crossover.com>
             * KAYAKO-3095 - XSS Security Vulnerability with HTML
             *
             * Comments - None
             */
            //Translate html entities to html
            $_contents = html_entity_decode($_contents);

            $_GeneralTabObject->HTMLEditor('postcontents', $_contents);
        } else {
            $_postContents = html_entity_decode($_SWIFT_TicketPostObject->Get('contents'), ENT_QUOTES);
            $_GeneralTabObject->TextArea('postcontents', '', '', $_postContents, '30', '12');
        }
        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
        */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Forward Tab
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Ticket Object
     * @param SWIFT_User $_SWIFT_UserObject The User Object
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function RenderForward(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_UserObject = null, $_listType = '', $_departmentID = 0,
            $_ticketStatusID = 0, $_ticketTypeID = 0, $_ticketLimitOffset = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_ticketWatchContainer = SWIFT_TicketWatcher::RetrieveOnTicket($_SWIFT_TicketObject);

        $_ticketURLSuffix = SWIFT::Get('ticketurlsuffix');
        $this->UserInterface->Start(get_short_class($this),'', SWIFT_UserInterface::MODE_INSERT, false);
        $_ForwardTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, $this->Language->Get('tabforward'), '', 1, 'forward', false, false, 4, '');
        $_ForwardTabObject->SetColumnWidth('15%');
        $_ForwardTabObject->LoadToolbar();
        $_ForwardTabObject->Toolbar->AddButton($this->Language->Get('dispatchsend'), 'fa-check-circle', '/Tickets/Ticket/ForwardSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertticketnote') != '0') {
            $_ForwardTabObject->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-file-o', "\$('#forwardnotes').toggle(); \$('#forwardticketnotes').focus();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'forwardnotes');
        }

        $_ForwardTabObject->Toolbar->AddButton($this->Language->Get('dispatchattachfile'), 'fa-paperclip', "\$('#forwardattachments').toggle();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'forwardattachments');

        if ($_SWIFT->Staff->GetPermission('staff_tcanfollowup') != '0') {
            $_ForwardTabObject->Toolbar->AddButton($this->Language->Get('followup'), 'fa-bookmark-o', "\$('#forwardfollowup').toggle(); LoadFollowUp('forwardfollowup', '" . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '/fr/' . substr(BuildHash(), 6) . "');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'forwardfollowup');
        }

        $_ForwardTabObject->Toolbar->AddButton($this->Language->Get('options'), 'fa-gears', "\$('#forwardoptions').toggle();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'forwardoptions');
        $_ForwardTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $this->RenderDispatchTab(self::TAB_FORWARD, $_ForwardTabObject, $_SWIFT_TicketObject, $_SWIFT_UserObject, $_ticketWatchContainer);

        // Begin Hook: staff_ticket_forwardtab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_forwardtab')) ? eval($_hookCode) : false;
        // End Hook

        $_renderHTML = $_ForwardTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Render the History
     *
     * @author Varun Shoor
     * @param mixed $_SWIFT_BaseObject The Ticket/User Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function RenderHistory($_SWIFT_BaseObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_departmentCache = (array) $_SWIFT->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $_SWIFT->Cache->Get('prioritycache');
        $_ticketTypeCache = (array) $_SWIFT->Cache->Get('tickettypecache');

        $_historyContainer = array();
        if ($_SWIFT_BaseObject instanceof SWIFT_Ticket && $_SWIFT_BaseObject->GetIsClassLoaded()) {
            $_historyContainer = $_SWIFT_BaseObject->RetrieveHistory();
        } else if ($_SWIFT_BaseObject instanceof SWIFT_User && $_SWIFT_BaseObject->GetIsClassLoaded()) {
            $_historyContainer = SWIFT_Ticket::RetrieveHistoryOnUser($_SWIFT_BaseObject);
        } else if (is_array($_SWIFT_BaseObject)) {
            $_historyContainer = SWIFT_Ticket::RetrieveHistoryOnUser(false, $_SWIFT_BaseObject);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_renderHTML = '<table width="100%" cellspacing="0" cellpadding="4" border="0">';
        $_renderHTML .= '<tbody><tr class=""><td valign="middle" align="left" class="gridtabletitlerow" width="16">&nbsp;</td><td valign="middle" align="left" class="gridtabletitlerow" width="120">' . $this->Language->Get('history_ticketid') . '</td><td valign="middle" align="left" class="gridtabletitlerow">' . $this->Language->Get('history_subject') . '</td><td valign="middle" align="left" class="gridtabletitlerow" width="160">' . $this->Language->Get('history_date') . '</td><td valign="middle" align="left" class="gridtabletitlerow" width="120">' . $this->Language->Get('history_department') . '</td><td valign="middle" align="left" class="gridtabletitlerow" width="90">' . $this->Language->Get('history_type') . '</td><td valign="middle" align="left" class="gridtabletitlerow" width="100">' . $this->Language->Get('history_status') . '</td><td valign="middle" align="left" class="gridtabletitlerow" width="90">' . $this->Language->Get('history_priority') . '</td></tr>';

        $_historyCount = 0;
        foreach ($_historyContainer as $_ticketID => $_TicketObject_History) {
            if ($_SWIFT_BaseObject instanceof SWIFT_Ticket && $_ticketID == $_SWIFT_BaseObject->GetTicketID()) {
                continue;
            }

            $_historyCount++;

            $_ticketURL = $_ticketURLPrefix = $_ticketURLSuffix = $_departmentHTML = $_typeHTML = $_statusHTML = $_priorityHTML = $_statusStyle = $_priorityStyle = '';

            $_ticketIcon = 'fa-ticket';
            if (!$_TicketObject_History->CanAccess($_SWIFT->Staff)) {
                $_ticketIcon = 'fa-lock';
            } else {
                $_ticketURL = SWIFT::Get('basename') . '/Tickets/Ticket/View/' . (int) ($_TicketObject_History->GetTicketID());
                $_ticketURLPrefix = '<a href="' . $_ticketURL . '" viewport="1">';
                $_ticketURLSuffix = '</a>';
            }
            $_ticketIconHTML = '<i class="fa ' . $_ticketIcon . '" aria-hidden="true"></i>';

            // Department
            if (isset($_departmentCache[$_TicketObject_History->GetProperty('departmentid')])) {
                $_departmentHTML = text_to_html_entities(StripName($_departmentCache[$_TicketObject_History->GetProperty('departmentid')]['title'], 15));
            } else {
                $_departmentHTML = $_SWIFT->Language->Get('na');
            }

            // Ticket Status
            if (isset($_ticketStatusCache[$_TicketObject_History->GetProperty('ticketstatusid')])) {
                $_ticketStatusContainer = $_ticketStatusCache[$_TicketObject_History->GetProperty('ticketstatusid')];
                // _displayIconImage Implementation removed.
                $_displayIconImage = '';

                $_statusHTML =  '<span class="ticketStatusIndicator" style="background-color: ' . $_ticketStatusContainer['statusbgcolor'] . ';color: #ffffff;">' . htmlspecialchars(StripName($_ticketStatusContainer['title'], 15)) . '</span>';
                $_statusStyle = '';
            } else {
                $_statusHTML = $_SWIFT->Language->Get('na');
            }

            // Ticket Priority
            if (isset($_ticketPriorityCache[$_TicketObject_History->GetProperty('priorityid')])) {
                $_ticketPriorityContainer = $_ticketPriorityCache[$_TicketObject_History->GetProperty('priorityid')];
                // _displayIconImage Implementation removed.
                $_displayIconImage = '';
                if($_ticketPriorityContainer['bgcolorcode']!=''){
                $_priorityHTML = '<span class="ticketPriorityIndicator" style="background-color: ' . $_ticketPriorityContainer['bgcolorcode'] . ';color: ' . $_ticketPriorityContainer['frcolorcode'] . ';">' . htmlspecialchars(StripName($_ticketPriorityContainer['title'], 15)) . '</span>';
                }
                else{
                    $_priorityHTML = '<span style="color: ' . $_ticketPriorityContainer['frcolorcode'] . ';">' . htmlspecialchars(StripName($_ticketPriorityContainer['title'], 15)) . '</span>';
                }

                $_priorityStyle = '';
            } else {
                $_priorityHTML = $_SWIFT->Language->Get('na');
            }

            // Ticket Type
            if (isset($_ticketTypeCache[$_TicketObject_History->GetProperty('tickettypeid')])) {
                $_ticketTypeContainer = $_ticketTypeCache[$_TicketObject_History->GetProperty('tickettypeid')];
                $_displayIconImage = '';
                if (!empty($_ticketTypeContainer['displayicon'])) {
                    $_displayIconImage = '<img src="' . ProcessDisplayIcon($_ticketTypeContainer['displayicon']) .
                            '" align="absmiddle" border="0" /> ';
                }

                $_typeHTML = $_displayIconImage . htmlspecialchars(StripName($_ticketTypeContainer['title'], 15));
            } else {
                $_typeHTML = $_SWIFT->Language->Get('na');
            }

            $_renderHTML .= '<tr class="tablerow1_tr"><td valign="middle" align="center" class="tablerow1">' . $_ticketIconHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_ticketURLPrefix . $_TicketObject_History->GetTicketDisplayID() . $_ticketURLSuffix . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_ticketURLPrefix . $this->Input->SanitizeForXSS($this->Emoji->decode($_TicketObject_History->GetProperty('subject')), false, true) . $_ticketURLSuffix . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1" width="220">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_TicketObject_History->GetProperty('dateline')) . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_departmentHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_typeHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1">' . $_statusHTML . '</td>';
            $_renderHTML .= '<td valign="middle" align="left" class="tablerow1" style="' . $_priorityStyle . '">' . $_priorityHTML . '</td>';
            $_renderHTML .= '</tr>';
        }

        if ($_historyCount == 0) {
            $_renderHTML .= '<tr class="tablerow1_tr"><td valign="middle" align="left" class="tablerow1" colspan="8">' . $this->Language->Get('noinfoinview') . '</td></tr>';
        }

        $_renderHTML .= '</tbody></table>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Render the Audit Log
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Ticket Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderAuditLog(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_auditLogContainer = SWIFT_TicketAuditLog::RetrieveOnTicket($_SWIFT_TicketObject);

        $_renderHTML = '<table width="100%" cellspacing="0" cellpadding="4" border="0">';
        $_renderHTML .= '<tbody><tr class=""><td valign="middle" align="left" class="gridtabletitlerow">' . $this->Language->Get('aldescription') . '</td><td valign="middle" align="center" class="gridtabletitlerow" width="220">' . $this->Language->Get('alentrytype') . '</td></tr>';

        foreach ($_auditLogContainer as $_actionHash => $_hashContainer) {
            $_diffSeconds = DATENOW - $_hashContainer['dateline'];

            $_renderHTML .= '<tr class="tabletitlerowtitle"><td valign="middle" align="left" class="tabletitlerowtitle" colspan="2"><b>' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_hashContainer['dateline']) . ' - </b>' . SWIFT_Date::ColorTime($_diffSeconds) . '</td></tr>';
            foreach ($_hashContainer['items'] as $_ticketAuditLogID => $_logContainer) {

                $_creatorText = $_logContainer['creatorfullname'];
                if ($_logContainer['creatortype'] == SWIFT_TicketAuditLog::CREATOR_STAFF) {
                    $_creatorText .= $this->Language->Get('alstaff');
                } else if ($_logContainer['creatortype'] == SWIFT_TicketAuditLog::CREATOR_USER) {
                    $_creatorText .= $this->Language->Get('aluser');
                } else if ($_logContainer['creatortype'] == SWIFT_TicketAuditLog::CREATOR_SYSTEM) {
                    $_creatorText .= $this->Language->Get('alsystem');
                } else if ($_logContainer['creatortype'] == SWIFT_TicketAuditLog::CREATOR_PARSER) {
                    $_creatorText .= $this->Language->Get('alparser');
                }

                $_renderHTML .= '<tr class="tablerow1_tr"><td valign="middle" align="left" class="tablerow1">' . htmlspecialchars($_SWIFT->Emoji->Decode($_logContainer['actionmsg'])) . '</td><td valign="middle" align="left" class="tablerow1">' . htmlspecialchars($_creatorText) . '</td></tr>';
            }
        }

        $_renderHTML .= '</tbody></table>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Render the Edit Tab
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Ticket Object
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function RenderEdit(SWIFT_Ticket $_SWIFT_TicketObject, $_listType, $_departmentID,
            $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_slaPlanCache = (array) $this->Cache->Get('slaplancache');

        $_ticketURLSuffix = $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID;

        $this->UserInterface->Start(get_short_class($this),'', SWIFT_UserInterface::MODE_INSERT, false);
        $_EditTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, $this->Language->Get('tabedit'), '', 1, 'edit', false, false, 4, '');
        $_EditTabObject->SetColumnWidth('30%');
        $_EditTabObject->LoadToolbar();
        $_EditTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/Tickets/Ticket/EditSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);
        $_EditTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_EDIT,
                array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), $_EditTabObject,
                $_SWIFT_TicketObject->GetTicketID(), $_SWIFT_TicketObject->GetProperty('departmentid'));

        // Merge Ticket
        $_EditTabObject->Title($this->Language->Get('mergeoptions'), 'doublearrows.gif');
        $_EditTabObject->Text('mergeticketid', $this->Language->Get('mergeparentticket'), $this->Language->Get('desc_mergeparentticket'), '');

        // Ticket Properties
        $_EditTabObject->Title($this->Language->Get('edittproperties'), 'doublearrows.gif');

        $_EditTabObject->Text('editsubject', $this->Language->Get('edit_subject'), '', $this->Emoji->decode($_SWIFT_TicketObject->GetProperty('subject')));
        $_EditTabObject->Text('editfullname', $this->Language->Get('edit_fullname'), '', $_SWIFT_TicketObject->GetProperty('fullname'));

        $_ticketEmailAddress = $_SWIFT_TicketObject->GetProperty('email');
        if ($_SWIFT_TicketObject->GetProperty('replyto') != '')
        {
            $_ticketEmailAddress = $_SWIFT_TicketObject->GetProperty('replyto');
        }
        $_EditTabObject->Text('editemail', $this->Language->Get('edit_email'), '', $_ticketEmailAddress);

        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('editslausedef');
        $_optionsContainer[0]['value'] = '0';

        if ($_SWIFT_TicketObject->GetProperty('ticketslaplanid') == '0') {
            $_optionsContainer[0]['selected'] = true;
        }

        foreach ($_slaPlanCache as $_slaPlanID => $_slaPlanContainer) {
            $_optionsContainer[$_index]['title'] = $_slaPlanContainer['title'];
            $_optionsContainer[$_index]['value'] = $_slaPlanID;

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-4430 Disabled SLA plan can be implemented over a ticket manually from 'Edit' tab.
             */
            if ($_slaPlanContainer['isenabled'] == '0') {
                $_optionsContainer[$_index]['disabled'] = true;
            }

            if ($_SWIFT_TicketObject->GetProperty('ticketslaplanid') == $_slaPlanID) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_EditTabObject->Select('editticketslaplanid', $this->Language->Get('edit_overridesla'), $this->Language->Get('desc_edit_overridesla'), $_optionsContainer);

        // Recipients
        $_EditTabObject->Title($this->Language->Get('edittrecipients'), 'doublearrows.gif');
        $_EditTabObject->Description($this->Language->Get('editrecipientsdesc'), '', 'tabledescriptionext', 2, true);

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($_SWIFT_TicketObject);
        $_ccEmailContainer = $_bccEmailContainer = $_thirdPartyEmailContainer = array();

        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY])) {
            $_thirdPartyEmailContainer = $_recipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY];
        }

        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC])) {
            $_ccEmailContainer = $_recipientContainer[SWIFT_TicketRecipient::TYPE_CC];
        }

        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC])) {
            $_bccEmailContainer = $_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC];
        }

        /** Bug Fix : Saloni Dhall
         *
         * SWIFT-1983 : Auto-complete feature for user's email address under Edit tab
         *
         * Comments : Added parameter to perform autocomplete feature
         **/
        $_EditTabObject->TextMultipleAutoComplete('editthirdparty', $this->Language->Get('editthirdparty'), '', '/Tickets/Ajax/SearchEmail', $_thirdPartyEmailContainer, 'fa-envelope-o', false, false, 2, false, false, false, '', array('containemail' => true));
        $_EditTabObject->TextMultipleAutoComplete('editcc', $this->Language->Get('editcc'), '', '/Tickets/Ajax/SearchEmail', $_ccEmailContainer, 'fa-envelope-o', false, false, 2, false, false, false, '', array('containemail' => true));
        $_EditTabObject->TextMultipleAutoComplete('editbcc', $this->Language->Get('editbcc'), '', '/Tickets/Ajax/SearchEmail', $_bccEmailContainer, 'fa-envelope-o', false, false, 2, false, false, false, '', array('containemail' => true));

        // Begin Hook: staff_ticket_edittab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_edittab')) ? eval($_hookCode) : false;
        // End Hook

        $_renderHTML = $_EditTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Retrieve the Options HTML for SELECT element
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param mixed $_selectType The Select Type
     * @param array $_extendedDataContainer
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function GetSelectOptions($_SWIFT_TicketObject, $_selectType, $_extendedDataContainer = array()) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_exDepartmentID = $_exOwnerStaffID = $_exTicketTypeID = $_exTicketStatusID = $_exTicketPriorityID = false;

        if (is_array($_extendedDataContainer)) {
            extract($_extendedDataContainer);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
        } else if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
            if (!isset($_extendedDataContainer['_exDepartmentID'])) {
                $_exDepartmentID = $_SWIFT_TicketObject->GetProperty('departmentid');
            }

            if (!isset($_extendedDataContainer['_exOwnerStaffID'])) {
                $_exOwnerStaffID = $_SWIFT_TicketObject->GetProperty('ownerstaffid');
            }

            if (!isset($_extendedDataContainer['_exTicketTypeID'])) {
                $_exTicketTypeID = $_SWIFT_TicketObject->GetProperty('tickettypeid');
            }

            if (!isset($_extendedDataContainer['_exTicketStatusID'])) {
                $_exTicketStatusID = $_SWIFT_TicketObject->GetProperty('ticketstatusid');
            }

            if (!isset($_extendedDataContainer['_exTicketPriorityID'])) {
                $_exTicketPriorityID = $_SWIFT_TicketObject->GetProperty('priorityid');
            }
        }

        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        $_staffCache = (array) $this->Cache->Get('staffcache');
        $_ticketTypeCache = (array) $this->Cache->Get('tickettypecache');
        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $this->Cache->Get('prioritycache');
        $_staffGroupTicketStatusIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_SWIFT->Staff->GetProperty('staffgroupid'));

        $_renderHTML = '';

        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_parentDepartmentID = false;
        if (isset($_departmentCache[$_exDepartmentID])) {
            $_parentDepartmentID = $_departmentCache[$_exDepartmentID]['parentdepartmentid'];
        }

        if ($_selectType == self::TYPE_DEPARTMENT) {

            $_assignedDepartmentList        = (array) SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_SWIFT->Staff->GetStaffID(), APP_TICKETS);
            $_canChangeUnAssignedDepartment = $_SWIFT->Staff->GetPermission('staff_tcanchangeunassigneddepartment');

            foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
                if (!$_canChangeUnAssignedDepartment && !in_array($_departmentID, $_assignedDepartmentList)) {
                    continue;
                }
                $_renderHTML .= '<option value="' .  ($_departmentID) . '"';
                if ($_exDepartmentID == $_departmentID) {
                    $_renderHTML .= ' selected';
                }
                $_renderHTML .= '>' . text_to_html_entities($_departmentContainer['title']) . '</option>';

                $subdepartments = (array)$_departmentContainer['subdepartments'];
                /**
                 * @var int $_subDepartmentID
                 * @var array $_subDepartmentContainer
                 */
                foreach ($subdepartments as $_subDepartmentID => $_subDepartmentContainer) {
                    if (!$_canChangeUnAssignedDepartment && !in_array($_subDepartmentID, $_assignedDepartmentList)) {
                        continue;
                    }
                    $_renderHTML .= '<option value="' . ($_subDepartmentID) . '"';
                    if ($_exDepartmentID == $_subDepartmentID) {
                        $_renderHTML .= ' selected';
                    }

                    $_renderHTML .= '> |-' . text_to_html_entities($_subDepartmentContainer['title']) . '</option>';
                }
            }

        } else if ($_selectType == self::TYPE_OWNER) {
            $_renderHTML .= '<option value="0"' . IIF($_exOwnerStaffID == '0', ' selected') . '>' . $this->Language->Get('unassigned') . '</option>';
            foreach ($_staffCache as $_staffID => $_staffContainer) {
                $_activeStaffAssignedDepartmentIDList = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID, APP_TICKETS);
                if (!in_array($_exDepartmentID, $_activeStaffAssignedDepartmentIDList) || $_staffContainer['isenabled'] == '0') {
                    continue;
                }

                $_renderHTML .= '<option value="' .  ($_staffID) . '"';
                if ($_exOwnerStaffID == $_staffID) {
                    $_renderHTML .= ' selected';
                }

                $_renderHTML .= '>' . text_to_html_entities($_staffContainer['fullname']) . '</option>';
            }

        } else if ($_selectType == self::TYPE_TYPE) {
            foreach ($_ticketTypeCache as $_ticketTypeID => $_ticketTypeContainer) {
                if ($_ticketTypeContainer['departmentid'] != '0' && $_ticketTypeContainer['departmentid'] != $_exDepartmentID && $_ticketTypeContainer['departmentid'] != $_parentDepartmentID) {
                    continue;
                }

                $_renderHTML .= '<option value="' .  ($_ticketTypeID) . '"';
                if ($_exTicketTypeID == $_ticketTypeID) {
                    $_renderHTML .= ' selected';
                }

                $_renderHTML .= '>' . htmlspecialchars($_ticketTypeContainer['title']) . '</option>';
            }

        } else if ($_selectType == self::TYPE_STATUS) {
            $_statuses = [];
            foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
                $isAnotherDepartment = $_ticketStatusContainer['departmentid'] != '0' && $_ticketStatusContainer['departmentid'] != $_exDepartmentID;
                $isNotVisibleByStaff = $_ticketStatusContainer['staffvisibilitycustom'] == '1' && !in_array($_ticketStatusID, $_staffGroupTicketStatusIDList);

                if ($isNotVisibleByStaff &&
                    isset($_extendedDataContainer['ticketstatusid']) &&
                    !in_array($_extendedDataContainer['ticketstatusid'], $_statuses)) {
                    $_ticketStatusID = $_extendedDataContainer['ticketstatusid'];
                    $_ticketStatusContainer = $_extendedDataContainer;
                } else if ($isAnotherDepartment || $isNotVisibleByStaff) {
                    continue;
                }

                if (in_array($_ticketStatusID, $_statuses)) {
                    continue;
                }

                $_renderHTML .= '<option value="' .  ($_ticketStatusID) . '"';
                if ($_exTicketStatusID == $_ticketStatusID) {
                    $_renderHTML .= ' selected';
                }

                $_renderHTML .= '>' . htmlspecialchars($_ticketStatusContainer['title']) . '</option>';
                $_statuses[] = $_ticketStatusID;
            }

        } else if ($_selectType == self::TYPE_PRIORITY) {
            foreach ($_ticketPriorityCache as $_ticketPriorityID => $_ticketPriorityContainer) {
                $_renderHTML .= '<option value="' .  ($_ticketPriorityID) . '"';
                if ($_exTicketPriorityID == $_ticketPriorityID) {
                    $_renderHTML .= ' selected';
                }

                $_renderHTML .= '>' . htmlspecialchars($_ticketPriorityContainer['title']) . '</option>';
            }
        }

        return $_renderHTML;
    }

    /**
     * Retrieve the relevant JSON associated with ticket data
     *
     * @author Varun Shoor
     * @return string The JSON Data
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketDataJSON() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $this->Cache->Get('prioritycache');

        $_ticketDataContainer = array();
        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
            $_ticketDataContainer['status'][$_ticketStatusID]['statusbgcolor'] = $_ticketStatusContainer['statusbgcolor'];
        }

        foreach ($_ticketPriorityCache as $_ticketPriorityID => $_ticketPriorityContainer) {
            $_ticketDataContainer['priority'][$_ticketPriorityID]['bgcolorcode'] = $_ticketPriorityContainer['bgcolorcode'];
        }

        return json_encode($_ticketDataContainer);
    }

    /**
     * Get the XML Flag Menu
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @return string
     * @throws SWIFT_Exception
     */
    public function GetFlagMenu(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketURLSuffix        = SWIFT::Get('ticketurlsuffix');
        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();
        $_ticketFlagContainer    = $_SWIFT_TicketFlagObject->GetFlagContainer();

        $_flagHTML = '<ul class="swiftdropdown" id="ticketflagmenu">';

        foreach ($_ticketFlagContainer as $_ticketFlagType => $_ticketFlag) {
            $_flagHTML .= '<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData(\'/Tickets/Ticket/Flag/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketFlagType . '/' . $_ticketURLSuffix . '\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);"> <i style="margin-right:6px;color:' . $_ticketFlag[1]. '" class="fa fa-flag" aria-hidden="true"></i>' . $_ticketFlag[0] . '</div></div></li>';
        }

        $_flagHTML .= '</ul>';

        return $_flagHTML;
    }

    /**
     * Render Split/Duplicate dialog box
     *
     * @author Simaranjit Singh
     *
     * @param int $_ticketID
     * @param int $_mode Split or duplicate
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded OR If Invalid Data is Provided
     */
    public function RenderSplitOrDuplicate($_ticketID, $_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Language->Load('tickets', SWIFT_LanguageEngine::TYPE_DB);

        $_phrase = IIF($_mode == SWIFT_TICKET::MODE_SPLIT, 'split', 'duplicate');

        $this->UserInterface->Start('ticketsplitform', '/Tickets/Ticket/SplitOrDuplicateSubmit', SWIFT_UserInterface::MODE_INSERT, true, false);

        $_Tab = $this->UserInterface->AddTab($this->Language->Get($_phrase . 'ticket'), 'icon_form.gif', 'general', true);

        // Display the ticket owner, department and title
        $_Ticket = new SWIFT_Ticket(new SWIFT_DataID($_ticketID));

        if (!$_Ticket instanceof SWIFT_Ticket || !$_Ticket->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_departmentTitle = $this->Language->Get('na');

        if ($_Ticket->Get('departmentid') == '0') {
            $_departmentTitle = $this->Language->Get('trash');
        } else if (isset($_departmentCache[$_Ticket->Get('departmentid')])) {
            $_departmentTitle = $_departmentCache[$_Ticket->Get('departmentid')]['title'];
        }

        $_User = $_Ticket->GetUserObject();

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4343: Security Issue
         * SWIFT-5060 Unicode characters like emojis not working in the subject
         */

        $_Tab->Title($this->Language->Get('oldticket'));
        $_Tab->DefaultDescriptionRow($this->Language->Get('st_subject'), '', $this->Input->SanitizeForXSS($this->Emoji->Decode($_Ticket->Get('subject')), false, true));
        $_Tab->DefaultDescriptionRow($this->Language->Get('st_fullname'), '', $this->Input->SanitizeForXSS($_User->GetFullName()));
        $_Tab->DefaultDescriptionRow($this->Language->Get('st_department'), '', $_departmentTitle);

        // Display the list of posts in date order
        $_ticketPostContainer = $_Ticket->GetTicketPosts(false, false, 'DESC');

        $_ticketPosts = array();
        foreach ($_ticketPostContainer as $_ticketPostID => $_ticketPostList) {
            $_ticketPosts[] = array(
                "value" => $_ticketPostID,
                "title" => SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_ticketPostList->Get('dateline')) . " (" . $_ticketPostList->Get('fullname') . ")",
            );
        }

        $_Tab->Title($this->Language->Get('newticket'));
        $_Tab->Text('newtitle', $this->Language->Get('st_subject'), '', $this->Emoji->Decode($_Ticket->Get('subject')));

        if ($_mode == SWIFT_TICKET::MODE_SPLIT && $_Ticket->Get('isresolved') == '0') {
            unset($_ticketPosts[count($_ticketPosts) - 1]);
            $_Tab->YesNo('closeold', $this->Language->Get('closeold'));
        } else {
            $_Tab->Hidden('closeold', 0);
        }

        $_Tab->Select('splitat', $this->Language->Get($_phrase . 'at'), $this->Language->Get($_phrase . 'at_d'), $_ticketPosts);

        $_Tab->Hidden('ticketid', $_ticketID);
        $_Tab->Hidden('operationmode', $_mode);

        return $this->UserInterface->End();
    }
}
