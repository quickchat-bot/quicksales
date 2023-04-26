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

namespace LiveChat\Staff;

use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use LiveChat\Models\Message\SWIFT_MessageSurvey;
use LiveChat\Models\Note\SWIFT_ChatNote;
use SWIFT;
use SWIFT_App;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Loader;
use SWIFT_View;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Chat History View
 *
 * @author Varun Shoor
 *
 * @property Controller_ChatHistory $Controller
 */
class View_ChatHistory extends SWIFT_View
{
    /**
     * Render the Chat History Section
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @param int $_filterDepartmentID The Filter Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RenderChatHistory(SWIFT_Chat $_SWIFT_ChatObject, $_filterDepartmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        $_SWIFT_UserObject = false;
        if ($_SWIFT_ChatObject->GetProperty('userid') != '0' && $_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_CLIENT) {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_ChatObject->GetProperty('userid')));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                return false;
            }
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_chatSkillCache = $this->Cache->Get('skillscache');
        $_chatTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_CHAT, $_SWIFT_ChatObject->GetChatObjectID());

        $this->UserInterface->Start(get_short_class($this), '/LiveChat/ChatHistory/ViewChatSubmit/' . $_SWIFT_ChatObject->GetChatObjectID() . '/' . $_filterDepartmentID, SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertticket') != '0' && SWIFT_App::IsInstalled(APP_TICKETS)) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('createticket'), 'fa-plus-circle', 'UICreateWindow(\'/Tickets/Ticket/NewTicket/' . $_SWIFT_ChatObject->GetChatObjectID() . '\', \'newticket\', \'' . $this->Language->Get('createticket') . '\', \'Loading..\', 500, 350, true);', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        if ($_SWIFT->Staff->GetPermission('admin_lscaninsertchatnote') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-sticky-note-o', "UICreateWindow('" . SWIFT::Get('basename') . '/LiveChat/ChatHistory/AddNote/' . $_SWIFT_ChatObject->GetChatObjectID() . "', 'addnote', '" . $_SWIFT->Language->Get('addnote') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 600, 360, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('email'), 'fa-envelope', "UICreateWindow('" . SWIFT::Get('basename') . "/LiveChat/ChatHistory/Email/" . ($_SWIFT_ChatObject->GetChatObjectID()) . "', 'emailchat', '" . $_SWIFT->Language->Get('emailchat') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 580, 330, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('print'), 'fa-print', 'PrintChatHistory(\'' . ($_SWIFT_ChatObject->GetChatObjectID()) . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

        $this->UserInterface->Toolbar->AddButton('');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/ChatHistory/Delete/' . $_SWIFT_ChatObject->GetChatObjectID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);

        // Begin Hook: staff_chat_toolbar
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_chat_toolbar')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chathistory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN CHAT TAB
         * ###############################################
         */
        $_ChatTabObject = $this->UserInterface->AddTab(sprintf($this->Language->Get('tabchathistory'), $_SWIFT_ChatObject->GetProcessedChatID()), 'icon_chatballoon.png', 'general', true);

        $_notesHTML = $this->RenderChatNotes($_SWIFT_ChatObject);

        if (!empty($_notesHTML)) {
            $_ChatTabObject->RowHTML('<tr class="gridrow3" id="chatnotescontainerdivholder"><td colspan="4" align="left" valign="top class="gridrow3"><div id="chatnotescontainerdiv">' . $_notesHTML . '</div></td></tr>');
        } else {
            $_ChatTabObject->RowHTML('<tr class="gridrow3" style="display: none;" id="chatnotescontainerdivholder"><td colspan="4" align="left" valign="top class="gridrow3"><div id="chatnotescontainerdiv"></div></td></tr>');
        }

        // Subject & Chat ID
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_subject'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => htmlspecialchars($_SWIFT_ChatObject->GetProperty('subject')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_chatid'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[3] = array('value' => htmlspecialchars($_SWIFT_ChatObject->GetProcessedChatID()), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Full Name & Email
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_userfullname'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => text_to_html_entities($_SWIFT_ChatObject->GetProperty('userfullname')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_useremail'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[3] = array('value' => htmlspecialchars($_SWIFT_ChatObject->GetProperty('useremail')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Staff Name & Department
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_staff'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        if (isset($_staffCache[$_SWIFT_ChatObject->GetProperty('staffid')])) {
            $_staffName = $_staffCache[$_SWIFT_ChatObject->GetProperty('staffid')]['fullname'];
        } else {
            $_staffName = $_SWIFT_ChatObject->GetProperty('staffname');
        }
        $_columnContainer[1] = array('value' => htmlspecialchars($_staffName), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_department'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        if (isset($_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')])) {
            $_departmentTitle = $_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')]['title'];
        } else {
            $_departmentTitle = $_SWIFT_ChatObject->GetProperty('departmenttitle');
        }
        $_columnContainer[3] = array('value' => text_to_html_entities($_departmentTitle), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Date & Last Post Activity
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_date'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => htmlspecialchars(SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_ChatObject->GetProperty('dateline'))), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_lastpostactivity'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        $_chatObjectLastPostActivity = $_SWIFT_ChatObject->GetProperty('lastpostactivity');
        if (!empty($_chatObjectLastPostActivity)) {
            $_lastPostActivity = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_ChatObject->GetProperty('lastpostactivity'));
        } else {
            $_lastPostActivity = $this->Language->Get('na');
        }

        $_columnContainer[3] = array('value' => htmlspecialchars($_lastPostActivity), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Wait Time & Duration
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_waittime'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => SWIFT_Date::ColorTime($_SWIFT_ChatObject->GetProperty('waittime'), false, false), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_duration'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        if (!empty($_chatObjectLastPostActivity)) {
            $_chatDuration = $_chatObjectLastPostActivity - $_SWIFT_ChatObject->GetProperty('dateline');
        } else {
            $_chatDuration = 0;
        }
        $_columnContainer[3] = array('value' => SWIFT_Date::ColorTime($_chatDuration, false, true), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Was transferred & transfer date
        if ($_SWIFT_ChatObject->GetProperty('transferstatus') != '0') {
            $_columnContainer = array();
            $_columnContainer[0] = array('value' => $this->Language->Get('ch_wastransferred'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

            if ($_SWIFT_ChatObject->GetProperty('transferstatus') != '0') {
                switch ($_SWIFT_ChatObject->GetProperty('transferstatus')) {
                    case SWIFT_Chat::TRANSFER_PENDING:
                        $_wasTransferred = $this->Language->Get('chtransferpending');
                        break;

                    case SWIFT_Chat::TRANSFER_ACCEPTED:
                        $_wasTransferred = $this->Language->Get('chtransferaccepted');
                        break;

                    case SWIFT_Chat::TRANSFER_REJECTED:
                        $_wasTransferred = $this->Language->Get('chtransferrejected');
                        break;

                    default:
                        $_wasTransferred = $this->Language->Get('no');

                        break;
                }
            } else {
                $_wasTransferred = $this->Language->Get('no');
            }
            $_columnContainer[1] = array('value' => htmlspecialchars($_wasTransferred), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_columnContainer[2] = array('value' => $this->Language->Get('ch_transferdate'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

            $_chatObjectTransferDate = $_SWIFT_ChatObject->GetProperty('transfertimeline');
            if (!empty($_chatObjectTransferDate)) {
                $_transferDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_chatObjectTransferDate);
            } else {
                $_transferDate = $this->Language->Get('na');
            }
            $_columnContainer[3] = array('value' => htmlspecialchars($_transferDate), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_ChatTabObject->Row($_columnContainer);

            // Transferred From & Transferred To
            $_chatObjectTransferredFrom = $_SWIFT_ChatObject->GetProperty('transferfromid');
            $_chatObjectTransferredTo = $_SWIFT_ChatObject->GetProperty('transfertoid');

            if (isset($_staffCache[$_chatObjectTransferredFrom])) {
                $_transferredFrom = $_staffCache[$_chatObjectTransferredFrom]['fullname'];
            } else {
                $_transferredFrom = $this->Language->Get('na');
            }

            if (isset($_staffCache[$_chatObjectTransferredTo])) {
                $_transferredTo = $_staffCache[$_chatObjectTransferredTo]['fullname'];
            } else {
                $_transferredTo = $this->Language->Get('na');
            }

            $_columnContainer = array();
            $_columnContainer[0] = array('value' => $this->Language->Get('ch_transferredfrom'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
            $_columnContainer[1] = array('value' => htmlspecialchars($_transferredFrom), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_columnContainer[2] = array('value' => $this->Language->Get('ch_transferredto'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
            $_columnContainer[3] = array('value' => htmlspecialchars($_transferredTo), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_ChatTabObject->Row($_columnContainer);
        }

        // IP Address & Is Proactive
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_ipaddress'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => htmlspecialchars($_SWIFT_ChatObject->GetProperty('ipaddress')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_isproactive'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[3] = array('value' => htmlspecialchars(IIF($_SWIFT_ChatObject->GetProperty('isproactive') == '1', $this->Language->Get('yes'), $this->Language->Get('no'))), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Chat Type & Chat Status
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_chattype'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => htmlspecialchars(IIF($_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_STAFF, $this->Language->Get('chattypestaff'), $this->Language->Get('chattypeclient'))), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_chatstatus'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        $_chatStatus = $_SWIFT_ChatObject->GetChatStatusLabel();


        $_columnContainer[3] = array('value' => htmlspecialchars($_chatStatus), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Round Robin Hits & Primary Skill
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('ch_roundrobinhits'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => htmlspecialchars($_SWIFT_ChatObject->GetProperty('roundrobinhits')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('ch_primaryskill'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        if (isset($_chatSkillCache[$_SWIFT_ChatObject->GetProperty('chatskillid')])) {
            $_chatSkill = $_chatSkillCache[$_SWIFT_ChatObject->GetProperty('chatskillid')]['title'];
        } else {
            $_chatSkill = $this->Language->Get('na');
        }
        $_columnContainer[3] = array('value' => htmlspecialchars($_chatSkill), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_ChatTabObject->Row($_columnContainer);

        // Linked Call?
        if ($_SWIFT_ChatObject->GetProperty('isphone') == '1') {
            $_SWIFT_CallObject = SWIFT_Call::RetrieveOnChat($_SWIFT_ChatObject);
            if ($_SWIFT_CallObject instanceof SWIFT_Call && $_SWIFT_CallObject->GetIsClassLoaded()) {
                $_ChatTabObject->Title($this->Language->Get('chcallinfo'), 'icon_phone.gif', 4);

                $_callEnded = $this->Language->Get('na');
                if ($_SWIFT_CallObject->GetProperty('enddateline') != '0') {
                    $_callEnded = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_CallObject->GetProperty('enddateline'));
                }

                $_columnContainer = array();
                $_columnContainer[1] = array('value' => $this->Language->Get('chphonenumber'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
                $_columnContainer[2] = array('value' => htmlspecialchars($_SWIFT_CallObject->GetProperty('phonenumber')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
                $_columnContainer[3] = array('value' => $this->Language->Get('chcalltype'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
                $_columnContainer[4] = array('value' => SWIFT_Call::GetTypeLabel($_SWIFT_CallObject->GetProperty('calltype')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
                $_ChatTabObject->Row($_columnContainer);

                $_columnContainer = array();
                $_columnContainer[1] = array('value' => $this->Language->Get('chcallstatus'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
                $_columnContainer[2] = array('value' => SWIFT_Call::GetStatusLabel($_SWIFT_CallObject->GetProperty('callstatus')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
                $_columnContainer[3] = array('value' => $this->Language->Get('chduration'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
                $_columnContainer[4] = array('value' => SWIFT_Date::ColorTime($_SWIFT_CallObject->GetProperty('duration'), true, true), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
                $_ChatTabObject->Row($_columnContainer);

                $_columnContainer = array();
                $_columnContainer[1] = array('value' => $this->Language->Get('chcallstarted'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
                $_columnContainer[2] = array('value' => SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_CallObject->GetProperty('dateline')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
                $_columnContainer[3] = array('value' => $this->Language->Get('chcallended'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
                $_columnContainer[4] = array('value' => $_callEnded, 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
                $_ChatTabObject->Row($_columnContainer);
            }
        }

        // Linked Survey?
        $_SWIFT_MessageSurveyObject = SWIFT_MessageSurvey::RetrieveOnChat($_SWIFT_ChatObject->GetChatObjectID());
        if ($_SWIFT_MessageSurveyObject instanceof SWIFT_MessageSurvey) {
            $_linkSurveyURL = SWIFT::Get('basename') . '/LiveChat/Message/ViewMessage/' . $_SWIFT_MessageSurveyObject->GetMessageID();
            $_linkSurveyContents = '<a class="bluelink" href="' . $_linkSurveyURL . '" viewport="1">' . htmlspecialchars($_SWIFT_MessageSurveyObject->GetProperty('messagemaskid')) . '</a>';

            // Linked Survey Message ID & Rating
            $_columnContainer = array();
            $_columnContainer[1] = array('value' => $this->Language->Get('ch_surveyrating'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
            $_columnContainer[2] = array('value' => '<img src="' . SWIFT::Get('themepathimages') . SWIFT_MessageSurvey::RetrieveRatingImage($_SWIFT_MessageSurveyObject->GetProperty('messagerating')) . '" align="absmiddle" border="0" />', 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_columnContainer[3] = array('value' => $this->Language->Get('ch_linkedsurvey'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
            $_columnContainer[4] = array('value' => $_linkSurveyContents, 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_ChatTabObject->Row($_columnContainer);
        }

        // Template Group
        $_columnContainer = array();
        if ($_SWIFT_ChatObject->GetProperty('tgroupid') != '0') {
            $_templateGroupCache = $this->Cache->Get('templategroupcache');
            if (isset($_templateGroupCache[$_SWIFT_ChatObject->GetProperty('tgroupid')])) {
                $_columnContainer[0] = array('value' => $this->Language->Get('ch_templategroup'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
                $_columnContainer[1] = array('value' => htmlspecialchars($_templateGroupCache[$_SWIFT_ChatObject->GetProperty('tgroupid')]['title']), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
                $_columnContainer[2] = array('value' => '', 'class' => 'gridrow1');
                $_columnContainer[3] = array('value' => '', 'class' => 'gridrow2');
                $_ChatTabObject->Row($_columnContainer);
            }
        }

        // Begin Hook: staff_chat_generaltab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_chat_generaltab')) ? eval($_hookCode) : false;
        // End Hook

        // Tags?
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            $_ChatTabObject->TextMultipleAutoComplete('tags', false, false, '/Base/Tags/QuickSearch', $_chatTagContainer, 'fa-tags', 'gridrow2', true, 4, false, true);
        }

        /**
         * BUG Fix: Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-3290 'Live Chat - Pre Chat' type custom fields cannot be edited by staff
         *
         * Comments: Made custom fields editable
         */
        $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_EDIT,
            array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE, SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST), $_ChatTabObject,
            $_SWIFT_ChatObject->GetChatObjectID(), $_SWIFT_ChatObject->GetProperty('departmentid'), true, false, 4);


        // Has Survey Comments?
        if ($_SWIFT_MessageSurveyObject instanceof SWIFT_MessageSurvey) {
            // Survey Comments
            $_ChatTabObject->Title($this->Language->Get('chsurveycomments'), 'icon_doublearrows.gif', 4);

            $_messageHTML = '<div class="chathistorymessage">';
            $_messageHTML .= nl2br(htmlspecialchars($_SWIFT_MessageSurveyObject->GetContents()));
            $_messageHTML .= '</div>';

            $_columnContainer = array();
            $_columnContainer[] = array('value' => $_messageHTML, 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2', 'colspan' => '4');
            $_ChatTabObject->Row($_columnContainer);
        }

        $_ChatTabObject->Title($this->Language->Get('chconversation'), 'icon_doublearrows.gif', 4);
        $_chatDataArray = $_SWIFT_ChatObject->GetConversationArray();

        $_conversationHTML = '';
        foreach ($_chatDataArray as $_key => $_val) {
            if ($_val['type'] != SWIFT_ChatQueue::MESSAGE_SYSTEM && $_val['type'] != SWIFT_ChatQueue::MESSAGE_STAFF && $_val['type'] != SWIFT_ChatQueue::MESSAGE_CLIENT) {
                continue;
            }

            $_conversationHTML .= '<div class="chathistorymessage">';
            if ($this->Settings->Get('livechat_timestamps') == true) {
                $_conversationHTML .= '<span class="chathistorytimestamp">' . $_val['timestamp'] . ' </span>';
            }

            // Process the message
            if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_CLIENT) {
                $_cssClass = 'chathistoryblue';
            } else if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_STAFF) {
                $_cssClass = 'chathistoryred';
            } else {
                $_cssClass = 'chathistorygreen';
            }

            if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_SYSTEM) {
                $_conversationHTML .= '<span class="' . $_cssClass . '">' . strip_tags($_val['messagehtml']) . '</span>';
            } else if ($_val['type'] == SWIFT_ChatQueue::MESSAGE_STAFF || $_val['type'] == SWIFT_ChatQueue::MESSAGE_CLIENT) {
                $_conversationHTML .= '<span class="' . $_cssClass . '">' . $_val['name'] . ':</span> ' . $_val['messagehtml'];
            }

            $_conversationHTML .= '</div>';
        }

        $_columnContainer = array();
        $_columnContainer[] = array('value' => $_conversationHTML, 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2', 'colspan' => '4');
        $_ChatTabObject->Row($_columnContainer);

        /*
         * ###############################################
         * END CHAT TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN GEOIP TAB
         * ###############################################
         */

        if ($_SWIFT_ChatObject->GetProperty('hasgeoip') == '1') {
            $_GeoIPTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeoip'), 'icon_geoip.gif', 'geoip', false);
            $_GeoIPTabObject->SetColumnWidth('150px');
            $_GeoIPTabObject->LoadToolbar();
            $_GeoIPTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chathistory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_geoIPKeys = array('geoiporganization', 'geoipisp', 'geoipnetspeed', 'geoiptimezone', 'geoipcountry', 'geoipcountrydesc',
                'geoipregion', 'geoipcity', 'geoippostalcode', 'geoiplatitude', 'geoiplongitude', 'geoipmetrocode',
                'geoipareacode');

            foreach ($_geoIPKeys as $_keyName) {
                if ($_SWIFT_ChatObject->GetProperty($_keyName) != '') {
                    $_GeoIPTabObject->DefaultDescriptionRow($this->Language->Get($_keyName), '', htmlspecialchars($_SWIFT_ChatObject->GetProperty($_keyName)));
                }
            }

        }

        /*
         * ###############################################
         * END GEOIP TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN HISTORY TAB
         * ###############################################
         */

        $_chatHistoryContainer = $_SWIFT_ChatObject->RetrieveHistory();

        $_totalItemCount = number_format(count($_chatHistoryContainer), 0);

        $_HistoryTabObject = $this->UserInterface->AddTab($this->Language->Get('tabhistory'), 'icon_spacer.gif', 'chathistory', false);
        $_HistoryTabObject->SetTabCounter($_totalItemCount);
        $_HistoryTabObject->LoadToolbar();
        $_HistoryTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chathistory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_columnContainer = array();
        $_columnContainer[0]['value'] = '&nbsp;';
        $_columnContainer[0]['align'] = 'center';
        $_columnContainer[0]['width'] = '20';
        $_columnContainer[1]['value'] = $this->Language->Get('hchatid');
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['width'] = '100';
        $_columnContainer[2]['value'] = $this->Language->Get('huserfullname');
        $_columnContainer[2]['align'] = 'left';
        $_columnContainer[3]['value'] = $this->Language->Get('huseremail');
        $_columnContainer[3]['align'] = 'center';
        $_columnContainer[3]['width'] = '250';
        $_columnContainer[4]['value'] = $this->Language->Get('hstaff');
        $_columnContainer[4]['align'] = 'center';
        $_columnContainer[4]['width'] = '160';
        $_columnContainer[5]['value'] = $this->Language->Get('hdepartment');
        $_columnContainer[5]['align'] = 'center';
        $_columnContainer[5]['width'] = '160';
        $_columnContainer[6]['value'] = $this->Language->Get('hdate');
        $_columnContainer[6]['align'] = 'center';
        $_columnContainer[6]['width'] = '180';

        $_HistoryTabObject->Row($_columnContainer, 'gridtabletitlerow');

        foreach ($_chatHistoryContainer as $_key => $_val) {
            $_columnContainer = array();
            $_columnContainer[0]['value'] = '<i class="fa fa-comments-o" aria-hidden="true"></i>';
            $_columnContainer[0]['align'] = 'center';
            $_columnContainer[1]['value'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . (int)($_val['chatobjectid']) . '/' . $_filterDepartmentID . '" viewport="1">' . htmlspecialchars($_val['chatobjectmaskid']) . '</a>';
            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[2]['value'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . (int)($_val['chatobjectid']) . '/' . $_filterDepartmentID . '" viewport="1">' . text_to_html_entities($_val['userfullname']) . '</a>';
            $_columnContainer[2]['align'] = 'left';
            $_columnContainer[3]['value'] = htmlspecialchars($_val['useremail']);
            $_columnContainer[3]['align'] = 'left';
            $_columnContainer[4]['value'] = htmlspecialchars($_val['staffname']);
            $_columnContainer[4]['align'] = 'left';
            $_columnContainer[5]['value'] = text_to_html_entities($_val['departmenttitle']);
            $_columnContainer[5]['align'] = 'left';
            $_columnContainer[6]['value'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_val['dateline']);
            $_columnContainer[6]['align'] = 'left';

            $_HistoryTabObject->Row($_columnContainer);
        }

        if (!count($_chatHistoryContainer)) {
            $_columnContainer = array();
            $_columnContainer[0]['value'] = $this->Language->Get('noinfoinview');
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['colspan'] = '8';

            $_HistoryTabObject->Row($_columnContainer);
        }

        /*
         * ###############################################
         * END HISTORY TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN TICKETS TAB
         * ###############################################
         */

        if (SWIFT_App::IsInstalled(APP_TICKETS) && $_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_CLIENT) {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            $_ticketHistoryCounterHTML = '';
            $_ticketHistoryCount = SWIFT_Ticket::GetHistoryCountOnUser($_SWIFT_UserObject, array($_SWIFT_ChatObject->GetProperty('useremail')));
            if ($_ticketHistoryCount > 0) {
                $_ticketHistoryCount = number_format($_ticketHistoryCount, 0);
            }

            $_TicketsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabtickets'), 'icon_tickets.png', 'tickets', false, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/HistoryEmails/' . base64_encode('email[]=' . urlencode($_SWIFT_ChatObject->GetProperty('useremail'))));
            $_TicketsTabObject->SetTabCounter($_ticketHistoryCount);
        }

        /*
         * ###############################################
         * END TICKETS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN CALLS TAB
         * ###############################################
         */

        $_callHistoryCounterHTML = '';
        $_callHistoryCount = SWIFT_Call::GetHistoryCountOnUser($_SWIFT_UserObject, array($_SWIFT_ChatObject->GetProperty('useremail')));
        if ($_callHistoryCount > 0) {
            $_callHistoryCount = number_format($_callHistoryCount, 0);
        }

        $_userIDCall = -1;
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_userIDCall = $_SWIFT_UserObject->GetUserID();
        }

        $_CallsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcalls'), 'icon_phone.gif', 'calls', false, false, 0, SWIFT::Get('basename') . '/LiveChat/Call/History/' . base64_encode('userid=' . ($_userIDCall) . '&email[]=' . urlencode($_SWIFT_ChatObject->GetProperty('useremail'))));
        $_CallsTabObject->SetTabCounter($_callHistoryCount);

        /*
         * ###############################################
         * END CALLS TAB
         * ###############################################
         */

        // Begin Hook: staff_chat_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_chat_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the History Grid
     *
     * @author Varun Shoor
     * @param array $_historyContainer The History Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderChatHistoryGrid($_historyContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '', SWIFT_UserInterface::MODE_INSERT, false);
        $_HistoryTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, 'history', '', 1, 'history', false, false, 4, '');

        $_columnContainer = array();
        $_columnContainer[0]['value'] = '&nbsp;';
        $_columnContainer[0]['align'] = 'center';
        $_columnContainer[0]['width'] = '20';
        $_columnContainer[1]['value'] = $this->Language->Get('hchatid');
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['width'] = '100';
        $_columnContainer[2]['value'] = $this->Language->Get('huserfullname');
        $_columnContainer[2]['align'] = 'left';
        $_columnContainer[3]['value'] = $this->Language->Get('huseremail');
        $_columnContainer[3]['align'] = 'left';
        $_columnContainer[3]['width'] = '250';
        $_columnContainer[4]['value'] = $this->Language->Get('hstaff');
        $_columnContainer[4]['align'] = 'left';
        $_columnContainer[4]['width'] = '160';
        $_columnContainer[5]['value'] = $this->Language->Get('hdepartment');
        $_columnContainer[5]['align'] = 'left';
        $_columnContainer[5]['width'] = '160';
        $_columnContainer[6]['value'] = $this->Language->Get('hdate');
        $_columnContainer[6]['align'] = 'left';
        $_columnContainer[6]['width'] = '180';

        $_HistoryTabObject->Row($_columnContainer, 'gridtabletitlerow');

        foreach ($_historyContainer as $_key => $_val) {
            $_chatURL = SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . (int)($_val['chatobjectid']) . '/' . 0;

            $_columnContainer = array();
            $_columnContainer[0]['value'] = '<i class="fa fa-comments-o" aria-hidden="true"></i>';
            $_columnContainer[0]['align'] = 'center';
            $_columnContainer[1]['value'] = '<a href="' . $_chatURL . '" viewport="1">' . htmlspecialchars($_val['chatobjectmaskid']) . '</a>';
            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[2]['value'] = '<a href="' . $_chatURL . '" viewport="1">' . text_to_html_entities($_val['userfullname']) . '</a>';
            $_columnContainer[2]['align'] = 'left';
            $_columnContainer[3]['value'] = htmlspecialchars($_val['useremail']);
            $_columnContainer[3]['align'] = 'left';
            $_columnContainer[4]['value'] = htmlspecialchars($_val['staffname']);
            $_columnContainer[4]['align'] = 'left';
            $_columnContainer[5]['value'] = text_to_html_entities($_val['departmenttitle']);
            $_columnContainer[5]['align'] = 'left';
            $_columnContainer[6]['value'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_val['dateline']);
            $_columnContainer[6]['align'] = 'left';

            $_HistoryTabObject->Row($_columnContainer);
        }

        if (!count($_historyContainer)) {
            $_columnContainer = array();
            $_columnContainer[0]['value'] = $this->Language->Get('noinfoinview');
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['colspan'] = '8';

            $_HistoryTabObject->Row($_columnContainer);
        }

        $_renderHTML = $_HistoryTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= '</script>';


        echo $_renderHTML;

        return true;
    }


    /**
     * Render the Email Chat Form
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderEmail(SWIFT_Chat $_SWIFT_ChatObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this) . 'email', '/LiveChat/ChatHistory/EmailSubmit/' . $_SWIFT_ChatObject->GetChatObjectID(), SWIFT_UserInterface::MODE_EDIT, true);

        if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chathistory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'emailtab', true);

        $_GeneralTabObject->Text('email', $this->Language->Get('chatemailfield'), $this->Language->Get('desc_chatemailfield'), '');
        $_GeneralTabObject->TextArea('emailnotes', $this->Language->Get('chatnotesfield'), $this->Language->Get('desc_chatnotesfield'), '');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Chat History Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid($_searchStoreID = 0, $_departmentID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('chatgrid', false, true), true, false, 'base');

        $_extendedWhereClause = '';
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_LIVECHAT);

        if (!empty($_departmentID) && isset($_departmentCache[$_departmentID]) && $_departmentCache[$_departmentID]['departmentapp'] == APP_LIVECHAT &&
            in_array($_departmentID, $_assignedDepartmentIDList)) {
            $_extendedWhereClause = "chatobjects.departmentid = '" . ($_departmentID) . "'";

            $this->UserInterfaceGrid->SetExtendedArguments(($_departmentID));
        } else {
            $_assignedDepartmentIDList[] = 0;

            $_extendedWhereClause = "chatobjects.departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")";
        }

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $_searchQuerySuffix = "FROM " . TABLE_PREFIX . "chatobjects AS chatobjects WHERE ((" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.userfullname') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.useremail') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.staffname') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.subject') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.departmenttitle') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.ipaddress') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.chatobjectmaskid') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('chatobjects.chatobjectid') . "))" . IIF(!empty($_extendedWhereClause), ' AND ' . $_extendedWhereClause);

            $this->UserInterfaceGrid->SetSearchQuery("SELECT * " . $_searchQuerySuffix, "SELECT COUNT(*) AS totalitems " . $_searchQuerySuffix);
        }

        $_filterQuerySQLPrefix = '*';

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
            "SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
                    WHERE " . IIF(!empty($_extendedWhereClause), $_extendedWhereClause . ' AND ') . "chatobjects.chatobjectid IN (%s)",
            SWIFT_SearchStore::TYPE_CHATS, '/LiveChat/ChatHistory/Manage/-1');

        $this->UserInterfaceGrid->SetQuery(
            "SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "chatobjects AS chatobjects" . IIF(!empty($_extendedWhereClause), ' WHERE ' . $_extendedWhereClause)

            , "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatobjects AS chatobjects" . IIF(!empty($_extendedWhereClause), ' WHERE ' . $_extendedWhereClause));

        // Set Tag Lookup Queries..
        $this->UserInterfaceGrid->SetTagOptions(SWIFT_TagLink::TYPE_CHAT, "SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "chatobjects AS chatobjects WHERE chatobjects.chatobjectid IN (%s)" . IIF(!empty($_extendedWhereClause), ' AND ' . $_extendedWhereClause)

            , "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatobjects AS chatobjects WHERE chatobjects.chatobjectid IN (%s)" . IIF(!empty($_extendedWhereClause), ' AND ' . $_extendedWhereClause));

        $this->UserInterfaceGrid->SetRecordsPerPage(20);

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('chatobjectid', 'chatobjectid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('chatobjects.chatobjectid', $this->Language->Get('hchatid'), SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 100, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('chatobjects.userfullname', $this->Language->Get('userfullname'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('chatobjects.staffname', $this->Language->Get('staffname'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('chatobjects.departmentid', $this->Language->Get('chatdepartment'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('chatobjects.dateline', $this->Language->Get('chatdate'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('LiveChat\Staff\Controller_ChatHistory', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_fieldContainer['chatstatus'] == SWIFT_Chat::CHAT_TIMEOUT) {
            $_chatIcon = 'fa-comment-o';
            $_nameSuffix = $_SWIFT->Language->Get('chattimeout');
        } else if ($_fieldContainer['chatstatus'] == SWIFT_Chat::CHAT_NOANSWER) {
            $_chatIcon = 'fa-comment-o icon_chatballoon_red';
            $_nameSuffix = $_SWIFT->Language->Get('chatnoanswer');
        } else if ($_fieldContainer['chatstatus'] == SWIFT_Chat::CHAT_INCHAT) {
            $_chatIcon = 'fa-comments-o icon_chatballoon_green';
            $_nameSuffix = $_SWIFT->Language->Get('chatactive');
        } else if ($_fieldContainer['chattype'] == SWIFT_Chat::CHATTYPE_STAFF) {
            $_chatIcon = 'fa-comments-o icon_chatballoon_dotted';
            $_nameSuffix = $_SWIFT->Language->Get('chatprivate');
        } else {
            $_chatIcon = 'fa-comments-o icon_chatballoon';
            $_nameSuffix = '';
        }

        if (!empty($_nameSuffix)) {
            $_nameSuffix = ' ' . $_nameSuffix;
        }

        $_chatURL = SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . (int)($_fieldContainer['chatobjectid']) . '/' . SWIFT::Get('chfilterdepartmentid');

        // $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_chatIcon . '" align="absmiddle" border="0" />';
        $_fieldContainer['icon'] = '<i class="fa ' . $_chatIcon . '" data-title="' . $_nameSuffix . '" aria-hidden="true"></i>';

        $_fieldContainer['chatobjects.userfullname'] = '<a href="' . $_chatURL . '" viewport="1">' . text_to_html_entities($_fieldContainer['userfullname']) . $_nameSuffix . '</a>' . IIF(!empty($_fieldContainer['subject']), '<BR /><b>' . $_SWIFT->Language->Get('ch_subject') . '</b> ' . htmlspecialchars($_fieldContainer['subject']));

        $_fieldContainer['chatobjects.chatobjectid'] = '<a href="' . $_chatURL . '" viewport="1">' . htmlspecialchars($_fieldContainer['chatobjectmaskid']) . '</a>';

        $_fieldContainer['chatobjects.useremail'] = htmlspecialchars($_fieldContainer['useremail']);
        $_fieldContainer['chatobjects.staffname'] = htmlspecialchars($_fieldContainer['staffname']);

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        if (!isset($_departmentCache[$_fieldContainer['departmentid']])) {
            $_departmentTitle = $_SWIFT->Language->Get('na');
        } else {
            $_departmentTitle = $_departmentCache[$_fieldContainer['departmentid']]['title'];
        }

        $_fieldContainer['chatobjects.departmentid'] = text_to_html_entities($_departmentTitle);

        $_fieldContainer['chatobjects.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        return $_fieldContainer;
    }

    /**
     * Render the Notes for the given Chat
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object
     * @return mixed "_renderedHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RenderChatNotes(SWIFT_Chat $_SWIFT_ChatObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        } else if ($_SWIFT->Staff->GetPermission('staff_lscanviewchat') == '0') {
            return '';
        }

        // Retrieve notes
        $_visitorNoteContainer = array();
        $_renderedHTML = '';

        $this->Database->Query("SELECT visitornotes.*, visitornotedata.contents AS notecontents FROM " . TABLE_PREFIX . "visitornotes AS visitornotes LEFT JOIN " . TABLE_PREFIX . "visitornotedata AS visitornotedata ON (visitornotes.visitornoteid = visitornotedata.visitornoteid) WHERE (visitornotes.linktype = '" . SWIFT_ChatNote::LINKTYPE_CHAT . "' AND visitornotes.linktypevalue = '" . ($_SWIFT_ChatObject->GetChatObjectID()) . "') ORDER BY visitornotes.dateline DESC");
        while ($this->Database->NextRecord()) {
            $_visitorNoteContainer[] = $this->Database->Record;

            unset($_icon);

            $_icon = 'fa-commenting';

            $_renderedHTML .= '<div id="note' . (SWIFT_ChatNote::GetSanitizedNoteColor($this->Database->Record['notecolor'])) . '" class="bubble"><div class="notebubble"><cite class="tip"><strong><i class="fa ' . $_icon . '" aria-hidden="true"></i> ' . sprintf($this->Language->Get('notetitle'), htmlspecialchars($this->Database->Record['staffname']), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Database->Record['dateline']))

                . IIF(!empty($this->Database->Record['editedstaffid']) && !empty($this->Database->Record['editedstaffname']), sprintf($this->Language->Get('noteeditedtitle'), htmlspecialchars($this->Database->Record['editedstaffname']), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Database->Record['editedtimeline'])))

                . '</strong><div class="ticketnotesactions">';

            if ($_SWIFT->Staff->GetPermission('staff_lscanupdatechatnote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: UICreateWindow(\'' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/EditNote/' . $_SWIFT_ChatObject->GetChatObjectID() . '/' . (int)($this->Database->Record['visitornoteid']) . "', 'editnote', '" . $this->Language->Get('editnote') . "', '" . $this->Language->Get('loadingwindow') . '\', 600, 360, true, this);"><i class="fa fa-edit" aria-hidden="true"></i></a> ';
            }

            if ($_SWIFT->Staff->GetPermission('staff_lscandeletechatnote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: ChatDeleteNote(\'' . addslashes($this->Language->Get('chatnotedelconfirm')) . '\', \'' . ($_SWIFT_ChatObject->GetChatObjectID()) . '/' . (int)($this->Database->Record['visitornoteid']) . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            }

            $_renderedHTML .= '</div></cite><blockquote><p>' . nl2br(htmlspecialchars($this->Database->Record['notecontents'])) . '</p></blockquote></div></div>';
        }

        return $_renderedHTML;
    }

    /**
     * Render the Add Note Dialog
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @param SWIFT_ChatNote $_SWIFT_ChatNoteObject The SWIFT_ChatNote Object Poitner
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderNoteForm($_mode, SWIFT_Chat $_SWIFT_ChatObject, SWIFT_ChatNote $_SWIFT_ChatNoteObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $this->UserInterface->Start('chataddnotes', '/LiveChat/ChatHistory/AddNoteSubmit/' . $_SWIFT_ChatObject->GetChatObjectID(), SWIFT_UserInterface::MODE_EDIT, true, false, false, false, 'chatnotescontainerdiv');
        } else {
            $this->UserInterface->Start('chataddnotes', '/LiveChat/ChatHistory/EditNoteSubmit/' . $_SWIFT_ChatObject->GetChatObjectID() . '/' . $_SWIFT_ChatNoteObject->GetVisitorNoteID(), SWIFT_UserInterface::MODE_EDIT, true, false, false, false, 'chatnotescontainerdiv');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chathistory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_defaultNoteContents = '';
        $_defaultNoteColor = 1;
        if ($_SWIFT_ChatNoteObject instanceof SWIFT_ChatNote && $_SWIFT_ChatNoteObject->GetIsClassLoaded()) {
            $_defaultNoteContents = $_SWIFT_ChatNoteObject->GetProperty('contents');
            $_defaultNoteColor = (int)($_SWIFT_ChatNoteObject->GetProperty('notecolor'));
        }

        /*
         * ###############################################
         * BEGIN ADD NOTES TAB
         * ###############################################
         */

        $_AddNoteTabObject = $this->UserInterface->AddTab(IIF($_mode == SWIFT_UserInterface::MODE_INSERT, $this->Language->Get('tabaddnote'), $this->Language->Get('tabeditnote')), 'icon_note.png', 'addnote', true);

        $_AddNoteTabObject->Notes('chatnotes', $this->Language->Get('addnotes'), $_defaultNoteContents, $_defaultNoteColor);

        /*
         * ###############################################
         * END ADD NOTES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox(SWIFT_Chat $_SWIFT_ChatObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_informationHTML = '';

        $_chatURL = SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . $_SWIFT_ChatObject->GetChatObjectID();
        $_informationHTML .= '<div class="navinfoitem">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobchatid') . '</div><div class="navinfoitemcontainer"><span class="navinfoitemlink"><a href="' . $_chatURL . '" viewport="1">' . $_SWIFT_ChatObject->GetProcessedChatID() . '</a></span></div></div>';

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobuser') . '</div><div class="navinfoitemcontent">' . text_to_html_entities(StripName($_SWIFT_ChatObject->GetProperty('userfullname'), 20)) . '</div></div>';


        $_staffName = $_SWIFT_ChatObject->GetProperty('staffname');
        if (isset($_staffCache[$_SWIFT_ChatObject->GetProperty('staffid')])) {
            $_staffName = $_staffCache[$_SWIFT_ChatObject->GetProperty('staffid')]['fullname'];
        }

        if (!empty($_staffName)) {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobstaff') . '</div><div class="navinfoitemcontent">' . text_to_html_entities(StripName($_staffName, 20)) . '</div></div>';
        }

        $_departmentTitle = $_SWIFT_ChatObject->GetProperty('departmenttitle');
        if (isset($_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')])) {
            $_departmentTitle = $_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')]['title'];
        }

        if (!empty($_departmentTitle)) {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobdepartment') . '</div><div class="navinfoitemcontent">' . text_to_html_entities(StripName($_departmentTitle, 20)) . '</div></div>';
        }

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobstatus') . '</div><div class="navinfoitemcontent">' . $_SWIFT_ChatObject->GetChatStatusLabel() . '</div></div>';

        $_waitTime = SWIFT_Date::ColorTime($_SWIFT_ChatObject->GetProperty('waittime'), false, false);
        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobwaittime') . '</div><div class="navinfoitemcontent">' . $_waitTime . '</div></div>';

        $_chatObjectLastPostActivity = $_SWIFT_ChatObject->GetProperty('lastpostactivity');
        $_chatDuration = 0;
        $_chatDurationHTML = '';
        if (!empty($_chatObjectLastPostActivity)) {
            $_chatDuration = $_chatObjectLastPostActivity - $_SWIFT_ChatObject->GetProperty('dateline');
            $_chatDurationHTML = SWIFT_Date::ColorTime($_chatDuration, false, true);
        }

        if (!empty($_chatDurationHTML)) {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobduration') . '</div><div class="navinfoitemcontent">' . $_chatDurationHTML . '</div></div>';
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }
}
