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
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Department\SWIFT_Department;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Message\SWIFT_Message;
use LiveChat\Models\Message\SWIFT_MessageManager;
use LiveChat\Models\Message\SWIFT_MessageSurvey;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_View;

/**
 * The Offline Message/Survey View
 *
 * @author Varun Shoor
 *
 * @property Controller_Message $Controller
 */
class View_Message extends SWIFT_View
{
    /**
     * Render the Message Section
     *
     * @author Varun Shoor
     * @param SWIFT_MessageManager $_SWIFT_MessageObject The SWIFT_Message Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RenderMessage(SWIFT_MessageManager $_SWIFT_MessageObject)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_MessageObject instanceof SWIFT_MessageManager || !$_SWIFT_MessageObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_messageTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_CHATMESSAGE, $_SWIFT_MessageObject->GetMessageID());

        $this->UserInterface->Start(get_short_class($this), '/LiveChat/Message/ViewMessageSubmit/' . $_SWIFT_MessageObject->GetMessageID(), SWIFT_UserInterface::MODE_INSERT, false);

        /*
         * ###############################################
         * BEGIN MESSAGE TAB
         * ###############################################
         */
        $_messageTabTitle = '';
        $_messageIcon = 'icon_email.gif';
        if ($_SWIFT_MessageObject instanceof SWIFT_Message) {
            $_messageTabTitle = $this->Language->Get('tabmessage');
        } else if ($_SWIFT_MessageObject instanceof SWIFT_MessageSurvey) {
            $_messageTabTitle = $this->Language->Get('tabsurvey');
            $_messageIcon = 'icon_survey.gif';
        }

        $_MessageTabObject = $this->UserInterface->AddTab(sprintf($_messageTabTitle, $_SWIFT_MessageObject->GetProperty('messagemaskid')), $_messageIcon, 'message', true);
        $_MessageTabObject->LoadToolbar();

        $_MessageTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        if ($_SWIFT->Staff->GetPermission('staff_lscanupdatemessages') != '0' && $_SWIFT_MessageObject->GetProperty('messagestatus') == SWIFT_Message::STATUS_NEW) {
            $_MessageTabObject->Toolbar->AddButton($this->Language->Get('markasread'), 'fa-check', "loadViewportData('" . SWIFT::Get('basename') . '/LiveChat/Message/MarkAsRead/' . $_SWIFT_MessageObject->GetMessageID() . "');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $_MessageTabObject->Toolbar->AddButton('');
        $_MessageTabObject->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/Message/Delete/' . $_SWIFT_MessageObject->GetMessageID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);

        // Begin Hook: staff_message_toolbar
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_message_toolbar')) ? eval($_hookCode) : false;
        // End Hook

        $_MessageTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatmessage'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        // Subject & Message ID
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('mh_subject'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => htmlspecialchars($_SWIFT_MessageObject->GetProperty('subject')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('mh_messageid'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[3] = array('value' => htmlspecialchars($_SWIFT_MessageObject->GetProperty('messagemaskid')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_MessageTabObject->Row($_columnContainer);

        // Full Name & Email
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('mh_userfullname'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => text_to_html_entities($_SWIFT_MessageObject->GetProperty('fullname')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('mh_useremail'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[3] = array('value' => htmlspecialchars($_SWIFT_MessageObject->GetProperty('email')), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_MessageTabObject->Row($_columnContainer);

        // Staff Name & Department
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('mh_repliedbystaff'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        $_messageStaffID = $_SWIFT_MessageObject->GetProperty('staffid');
        if (isset($_staffCache[$_SWIFT_MessageObject->GetProperty('staffid')])) {
            $_staffName = $_staffCache[$_SWIFT_MessageObject->GetProperty('staffid')]['fullname'];
        } else if (!empty($_messageStaffID)) {
            $_staffName = $_SWIFT_MessageObject->GetProperty('staffname');
        } else {
            $_staffName = $this->Language->Get('na');
        }
        $_columnContainer[1] = array('value' => htmlspecialchars($_staffName), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('mh_department'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        if (isset($_departmentCache[$_SWIFT_MessageObject->GetProperty('departmentid')])) {
            $_departmentTitle = $_departmentCache[$_SWIFT_MessageObject->GetProperty('departmentid')]['title'];
        } else {
            $_departmentTitle = $this->Language->Get('na');
        }
        $_columnContainer[3] = array('value' => text_to_html_entities($_departmentTitle), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_MessageTabObject->Row($_columnContainer);

        // Date & Reply Date
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('mh_date'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
        $_columnContainer[1] = array('value' => htmlspecialchars(SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_MessageObject->GetProperty('dateline'))), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('mh_replydate'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        $_messageReplyDateline = $_SWIFT_MessageObject->GetProperty('replydateline');
        if (!empty($_messageReplyDateline)) {
            $_messageReplyDateline = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_MessageObject->GetProperty('replydateline'));
        } else {
            $_messageReplyDateline = $this->Language->Get('na');
        }

        $_columnContainer[3] = array('value' => htmlspecialchars($_messageReplyDateline), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_MessageTabObject->Row($_columnContainer);

        // Message Status & Type
        $_columnContainer = array();
        $_columnContainer[0] = array('value' => $this->Language->Get('mh_messagestatus'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        $_messageStatus = $_SWIFT_MessageObject->GetMessageStatusLabel();

        $_columnContainer[1] = array('value' => htmlspecialchars($_messageStatus), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_columnContainer[2] = array('value' => $this->Language->Get('mh_messagetype'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');

        $_messageType = $_SWIFT_MessageObject->GetMessageTypeLabel();

        $_columnContainer[3] = array('value' => htmlspecialchars($_messageType), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
        $_MessageTabObject->Row($_columnContainer);

        // Is it survey?
        $_SWIFT_ChatObject = false;
        if ($_SWIFT_MessageObject instanceof SWIFT_MessageSurvey) {
            // Try loading the linked chat object
            $_linkChatContents = $this->Language->Get('na');
            try {
                $_SWIFT_ChatObject = new SWIFT_Chat($_SWIFT_MessageObject->GetProperty('chatobjectid'));
                if ($_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded()) {
                    $_linkChatContents = '<a class="bluelink" href="' . SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . $_SWIFT_ChatObject->GetChatObjectID() . '" viewport="1">' . htmlspecialchars($_SWIFT_ChatObject->GetProperty('chatobjectmaskid')) . '</a>';
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
            // Linked Chat ID & Rating
            $_columnContainer = array();
            $_columnContainer[0] = array('value' => $this->Language->Get('mh_linkedchat'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
            $_columnContainer[1] = array('value' => $_linkChatContents, 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_columnContainer[2] = array('value' => $this->Language->Get('mh_surveyrating'), 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow1');
            $_columnContainer[3] = array('value' => '<img src="' . SWIFT::Get('themepathimages') . SWIFT_MessageSurvey::RetrieveRatingImage($_SWIFT_MessageObject->GetProperty('messagerating')) . '" align="absmiddle" border="0" />', 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2');
            $_MessageTabObject->Row($_columnContainer);
        }

        // Begin Hook: staff_message_generaltab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_message_generaltab')) ? eval($_hookCode) : false;
        // End Hook

        // Tags?
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            $_MessageTabObject->TextMultipleAutoComplete('tags', false, false, '/Base/Tags/QuickSearch', $_messageTagContainer, 'fa-tags', 'gridrow2', true, 4, false, true);
        }

        if ($_SWIFT_MessageObject instanceof SWIFT_MessageSurvey && $_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded()) {
            $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, SWIFT_UserInterface::MODE_INSERT,
                array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST), $_MessageTabObject,
                $_SWIFT_MessageObject->GetProperty('chatobjectid'), $_SWIFT_ChatObject->GetProperty('departmentid'), true, false, 4);
        }

        $_MessageTabObject->Title($this->Language->Get('mhmessagecontents'), 'icon_doublearrows.gif', 4);

        $_messageHTML = '<div class="chathistorymessage">';
        $_messageHTML .= nl2br(htmlspecialchars($_SWIFT_MessageObject->GetContents()));
        $_messageHTML .= '</div>';

        $_columnContainer = array();
        $_columnContainer[] = array('value' => $_messageHTML, 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2', 'colspan' => '4');
        $_MessageTabObject->Row($_columnContainer);

        if ($_SWIFT_MessageObject->GetProperty('messagestatus') == SWIFT_Message::STATUS_REPLIED) {
            $_staffReplyContents = $_SWIFT_MessageObject->GetStaffReplyContents();
            if ($_staffReplyContents) {
                $_MessageTabObject->Title($this->Language->Get('mhstaffreply'), 'icon_doublearrows.gif', 4);

                $_messageHTML = '<div class="chathistorymessage">';
                $_messageHTML .= nl2br(htmlspecialchars($_staffReplyContents));
                $_messageHTML .= '</div>';

                $_columnContainer = array();
                $_columnContainer[] = array('value' => $_messageHTML, 'align' => 'left', 'valign' => 'top', 'class' => 'gridrow2', 'colspan' => '4');
                $_MessageTabObject->Row($_columnContainer);
            }
        }

        /*
         * ###############################################
         * END MESSAGE TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN REPLY TAB
         * ###############################################
         */

        if ($_SWIFT_MessageObject->GetProperty('messagestatus') != SWIFT_Message::STATUS_REPLIED) {
            $_ReplyTabObject = $this->UserInterface->AddTab($this->Language->Get('tabreply'), 'icon_reply.gif', 'reply', false);
            $_ReplyTabObject->LoadToolbar();
            $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('mrsend'), 'fa-check-circle', '/LiveChat/Message/Reply/' . $_SWIFT_MessageObject->GetMessageID(), SWIFT_UserInterfaceToolbar::LINK_FORM);
            $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatmessage'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_optionsContainer = array();
            $_optionsContainer[0]['title'] = $_SWIFT->Staff->GetProperty('fullname') . ' <' . $this->Settings->Get('general_returnemail') . '>';
            $_optionsContainer[0]['value'] = $this->Settings->Get('general_returnemail');
            $_optionsContainer[0]['selected'] = true;

            $_optionsContainer[1]['title'] = $_SWIFT->Staff->GetProperty('fullname') . ' <' . $_SWIFT->Staff->GetProperty('email') . '>';
            $_optionsContainer[1]['value'] = $_SWIFT->Staff->GetProperty('email');
            $_ReplyTabObject->Select('fromemail', $this->Language->Get('mrfromemail'), $this->Language->Get('desc_mrfromemail'), $_optionsContainer);

            $_ReplyTabObject->Text('subject', $this->Language->Get('mrsubject'), $this->Language->Get('desc_mrsubject'), sprintf($this->Language->Get('subjectreplyformat'), $_SWIFT_MessageObject->GetProperty('subject')), 'text', '50');

            $_ReplyTabObject->Title($this->Language->Get('mrreplycontents'), 'icon_doublearrows.gif');
            $_ReplyTabObject->TextArea('replycontents', '', '', '', '40', '20');
        }

        /*
         * ###############################################
         * END REPLY TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN GEOIP TAB
         * ###############################################
         */

        if ($_SWIFT_MessageObject->GetProperty('hasgeoip') == '1') {
            $_GeoIPTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeoip'), 'icon_geoip.gif', 'geoip', false);
            $_GeoIPTabObject->SetColumnWidth('150px');

            $_geoIPKeys = array('geoiporganization', 'geoipisp', 'geoipnetspeed', 'geoiptimezone', 'geoipcountry', 'geoipcountrydesc',
                'geoipregion', 'geoipcity', 'geoippostalcode', 'geoiplatitude', 'geoiplongitude', 'geoipmetrocode',
                'geoipareacode');

            foreach ($_geoIPKeys as $_keyName) {
                if ($_SWIFT_MessageObject->GetProperty($_keyName) != '') {
                    $_GeoIPTabObject->DefaultDescriptionRow($this->Language->Get($_keyName), '', htmlspecialchars($_SWIFT_MessageObject->GetProperty($_keyName)));
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
         * BEGIN CHATS TAB
         * ###############################################
         */

        $_SWIFT_UserObject = null;
        try
        {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_MessageObject->GetProperty('userid')));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        $_chatHistoryCounterHTML = '';
        $_chatHistoryCount = SWIFT_Chat::GetHistoryCountOnUser($_SWIFT_UserObject, array($_SWIFT_MessageObject->GetProperty('email')));
        if ($_chatHistoryCount > 0) {
            $_chatHistoryCount = number_format($_chatHistoryCount, 0);
        }

        $_messageUserID = '-1';
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_messageUserID = $_SWIFT_UserObject->GetUserID();
        }

        $_historyArguments = 'userid=' . $_messageUserID . '&email=' . urlencode($_SWIFT_MessageObject->GetProperty('email')) . '&random=' . substr(BuildHash(), 6);
        $_ChatsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabchats'), 'icon_livesupport.gif', 'livechathistory', false, false, 0, SWIFT::Get('basename') . '/LiveChat/ChatHistory/History/' . base64_encode($_historyArguments));
        $_ChatsTabObject->SetTabCounter($_chatHistoryCount);
        /*
         * ###############################################
         * END CHATS TAB
         * ###############################################
         */

        // Begin Hook: staff_message_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_message_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Messages/Survey Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param bool $_filterToSurvey (OPTIONAL) Whether to filter the results to just the surveys
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid($_searchStoreID = 0, $_departmentID = 0, $_filterToSurvey = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('chatmessagegrid', false, true), true, false, 'base');

        $_extendedWhereClause = '';
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_LIVECHAT);

        if (!empty($_departmentID) && isset($_departmentCache[$_departmentID]) && $_departmentCache[$_departmentID]['departmentapp'] == APP_LIVECHAT && in_array($_departmentID, $_assignedDepartmentIDList)) {
            $_extendedWhereClause = "messages.departmentid = '" . ($_departmentID) . "'";

            $this->UserInterfaceGrid->SetExtendedArguments(($_departmentID) . '/' . (int)($_filterToSurvey));
        } else {
            $_extendedWhereClause = "messages.departmentid IN (" . BuildIN($_assignedDepartmentIDList) . ")";
        }

        if ($_filterToSurvey) {
            $_extendedWhereClause .= IIF(!empty($_extendedWhereClause), ' AND ') . "messages.messagetype = '" . (SWIFT_Message::MESSAGE_CLIENTSURVEY) . "'";
        }

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $_searchQuerySuffix = "FROM " . TABLE_PREFIX . "messages AS messages
                LEFT JOIN " . TABLE_PREFIX . "messagedata AS messagedata ON (messages.messageid = messagedata.messageid)
                    WHERE ((" . $this->UserInterfaceGrid->BuildSQLSearch('messages.fullname') . ")
                        OR (" . $this->UserInterfaceGrid->BuildSQLSearch('messages.email') . ")
                        OR (" . $this->UserInterfaceGrid->BuildSQLSearch('messages.subject') . ")
                        OR (" . $this->UserInterfaceGrid->BuildSQLSearch('messagedata.contents') . "))" .
                IIF(!empty($_extendedWhereClause), ' AND ' . $_extendedWhereClause);

            $this->UserInterfaceGrid->SetSearchQuery("SELECT * " . $_searchQuerySuffix, "SELECT COUNT(*) AS totalitems " . $_searchQuerySuffix);
        }

        $_filterQuerySQLPrefix = 'messages.*';

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID, "SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "messages AS messages
            WHERE " . IIF(!empty($_extendedWhereClause), $_extendedWhereClause . ' AND ') . "messages.messageid IN (%s)", SWIFT_SearchStore::TYPE_CHATMESSAGE, '/LiveChat/Message/Manage/-1');

        $this->UserInterfaceGrid->SetQuery(
            "SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "messages AS messages" . IIF(!empty($_extendedWhereClause), ' WHERE ' . $_extendedWhereClause)

            , "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "messages AS messages" . IIF(!empty($_extendedWhereClause), ' WHERE ' . $_extendedWhereClause));

        // Set Tag Lookup Queries..
        $this->UserInterfaceGrid->SetTagOptions(SWIFT_TagLink::TYPE_CHATMESSAGE, "SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "messages AS messages
            WHERE messages.messageid IN (%s)" . IIF(!empty($_extendedWhereClause), ' AND ' . $_extendedWhereClause)

            , "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "messages AS messages WHERE messages.messageid IN (%s)" . IIF(!empty($_extendedWhereClause), ' AND ' . $_extendedWhereClause));

        $this->UserInterfaceGrid->SetRecordsPerPage(20);

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('messageid', 'messageid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('messages.messagemaskid', $this->Language->Get('mhmessageid'), SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 100, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('messages.subject', $this->Language->Get('mhsubject'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        if (empty($_departmentID)) {
            $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('messages.departmentid', $this->Language->Get('mhdepartment'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        }

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('messages.fullname', $this->Language->Get('mhfullname'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('messages.dateline', $this->Language->Get('mhcreatedon'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

        if ($_filterToSurvey == true) {
            $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('messages.messagerating', $this->Language->Get('mhrating'), SWIFT_UserInterfaceGridField::TYPE_DB, 100, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        }

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('LiveChat\Staff\Controller_Message', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('markasread'), 'fa-check-circle', array('LiveChat\Staff\Controller_Message', 'MarkAsReadList')));

        $this->UserInterfaceGrid->Render();

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

        $_nameSuffix = '';
        if (($_fieldContainer['messagetype'] == SWIFT_Message::MESSAGE_CLIENT || $_fieldContainer['messagetype'] == SWIFT_Message::MESSAGE_STAFF) && $_fieldContainer['messagestatus'] == SWIFT_Message::STATUS_NEW) {
            $_messageIcon = 'fa-envelope';
        } else if (($_fieldContainer['messagetype'] == SWIFT_Message::MESSAGE_CLIENT || $_fieldContainer['messagetype'] == SWIFT_Message::MESSAGE_STAFF) && $_fieldContainer['messagestatus'] != SWIFT_Message::STATUS_NEW) {
            $_messageIcon = 'fa-check';
        } else if ($_fieldContainer['messagetype'] == SWIFT_Message::MESSAGE_CLIENTSURVEY) {
            $_messageIcon = 'fa-star-half-o';
        } else {
            $_messageIcon = 'fa-envelope';
        }

        if (!empty($_nameSuffix)) {
            $_nameSuffix = ' ' . $_nameSuffix;
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_messageIcon . '" aria-hidden="true"></i>';

        $_fieldContainer['messages.subject'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/Message/ViewMessage/' . (int)($_fieldContainer['messageid']) . '/' . SWIFT::Get('chmfilterdepartmentid') . '" viewport="1">' . htmlspecialchars($_fieldContainer['subject']) . $_nameSuffix . '</a>';

        $_fieldContainer['messages.messagemaskid'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/Message/ViewMessage/' . (int)($_fieldContainer['messageid']) . '/' . SWIFT::Get('chmfilterdepartmentid') . '" viewport="1">' . htmlspecialchars($_fieldContainer['messagemaskid']) . $_nameSuffix . '</a>';

        $_fieldContainer['messages.fullname'] = text_to_html_entities($_fieldContainer['fullname']);

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        if (!isset($_departmentCache[$_fieldContainer['departmentid']])) {
            $_departmentTitle = $_SWIFT->Language->Get('na');
        } else {
            $_departmentTitle = $_departmentCache[$_fieldContainer['departmentid']]['title'];
        }

        $_fieldContainer['messages.departmentid'] = text_to_html_entities($_departmentTitle);

        $_fieldContainer['messages.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_fieldContainer['messages.messagerating'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . SWIFT_MessageSurvey::RetrieveRatingImage($_fieldContainer['messagerating']) . '" align="absmiddle" border="0" />';

        return $_fieldContainer;
    }

    /**
     * Render the Messages Tree
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTree()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chatMessageCountCache = (array)$this->Cache->Get('chatmessagecountcache');

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="funnel"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/department/0" viewport="1">' . $this->Language->Get('chtdepartment') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_departmentMap = SWIFT_Department::GetDepartmentMap(APP_LIVECHAT);
        $_assignedDepartmentIDList = (array)$_SWIFT->Staff->GetAssignedDepartments(APP_LIVECHAT);

        foreach ($_departmentMap as $_key => $_val) {
            $_extendedText = '';

            if (!in_array($_key, $_assignedDepartmentIDList)) {
                continue;
            }

            // Counters
            if (isset($_chatMessageCountCache[$_val['departmentid']][SWIFT_Message::STATUS_NEW]['totalitems']) && $_chatMessageCountCache[$_val['departmentid']][SWIFT_Message::STATUS_NEW]['totalitems'] > 0) {
                $_extendedText = ' <font color="red">(' . (int)($_chatMessageCountCache[$_val['departmentid']][SWIFT_Message::STATUS_NEW]['totalitems']) . ')</font>';
            }

            // Is it new?
            if (isset($_chatMessageCountCache[$_val['departmentid']]['dateline']) && $_chatMessageCountCache[$_val['departmentid']]['dateline'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
                $_departmentClass = 'folderred';
            } else {
                $_departmentClass = 'folder';
            }

            $_renderHTML .= '<li><span class="' . $_departmentClass . '"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/department/' . (int)($_val['departmentid']) . '" viewport="1">' . text_to_html_entities($_val['title']) . '</a>' . $_extendedText . '</span>';

            if (_is_array($_val['subdepartments'])) {
                $_renderHTML .= '<ul>';
                foreach ($_val['subdepartments'] as $_subKey => $_subVal) {
                    if (!in_array($_subKey, $_assignedDepartmentIDList)) {
                        continue;
                    }

                    $_extendedText = '';

                    // Sub Department Counters
                    if (isset($_chatMessageCountCache[$_subVal['departmentid']][SWIFT_Message::STATUS_NEW]['totalitems']) && $_chatMessageCountCache[$_subVal['departmentid']][SWIFT_Message::STATUS_NEW]['totalitems'] > 0) {
                        $_extendedText = ' <font color="red">(' . (int)($_chatMessageCountCache[$_subVal['departmentid']][SWIFT_Message::STATUS_NEW]['totalitems']) . ')</font>';
                    }

                    // Is it new?
                    if (isset($_chatMessageCountCache[$_subVal['departmentid']]['dateline']) && $_chatMessageCountCache[$_subVal['departmentid']]['dateline'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
                        $_departmentClass = 'folderred';
                    } else {
                        $_departmentClass = 'folder';
                    }

                    $_renderHTML .= '<li><span class="' . $_departmentClass . '"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/department/' . (int)($_subVal['departmentid']) . '" viewport="1">' . text_to_html_entities($_subVal['title']) . '</a>' . $_extendedText . '</span></li>';
                }
                $_renderHTML .= '</ul>';
            }

            $_renderHTML .= '</li>';
        }
        $_renderHTML .= '</ul></li>';

        /*
         * ###############################################
         * Calculate Message Ratings
         * ###############################################
         */
        $_messageRatingContainer = [0, 0, 0, 0, 0, 0];

        if (isset($_chatMessageCountCache['surveys']) && is_array($_chatMessageCountCache['surveys'])) {
            foreach ($_chatMessageCountCache['surveys'] as $_key => $_val) {
                switch ($_key) {
                    case '0':
                        $_messageRatingContainer[0] += $_val['totalitems'];
                        break;

                    case '0.5':
                        $_messageRatingContainer[0] += $_val['totalitems'];
                        break;

                    case '1':
                        $_messageRatingContainer[1] += $_val['totalitems'];
                        break;

                    case '1.5':
                        $_messageRatingContainer[1] += $_val['totalitems'];
                        break;

                    case '2':
                        $_messageRatingContainer[2] += $_val['totalitems'];
                        break;

                    case '2.5':
                        $_messageRatingContainer[2] += $_val['totalitems'];
                        break;

                    case '3':
                        $_messageRatingContainer[3] += $_val['totalitems'];
                        break;

                    case '3.5':
                        $_messageRatingContainer[3] += $_val['totalitems'];
                        break;

                    case '4':
                        $_messageRatingContainer[4] += $_val['totalitems'];
                        break;

                    case '4.5':
                        $_messageRatingContainer[4] += $_val['totalitems'];
                        break;

                    case '5':
                        $_messageRatingContainer[5] += $_val['totalitems'];
                        break;

                    default:
                        break;
                }
            }
        }

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('chtratings') . '</a></span>';
        $_renderHTML .= '<ul>';
        for ($_ii = 0; $_ii <= 5; $_ii++) {
            $_extendedText = '';
            if (isset($_messageRatingContainer[$_ii]) && $_messageRatingContainer[$_ii] > 0) {
                $_extendedText = ' <font color="red">(' . (int)($_messageRatingContainer[$_ii]) . ')</font>';
            }

            $_renderHTML .= '<li><span class="blank"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/rating/' . $_ii . '" viewport="1"><img src="' . SWIFT::Get('themepathimages') . 'icon_star_' . $_ii . '.gif" align="top" border="0" /></a>' . $_extendedText . '</span></li>';
        }
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('chtdate') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/date/today" viewport="1">' . htmlspecialchars($this->Language->Get('ctoday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($this->Language->Get('cyesterday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($this->Language->Get('cl7days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($this->Language->Get('cl30days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($this->Language->Get('cl180days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($this->Language->Get('cl365days')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('chttype') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="chat"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/type/new" viewport="1">' . htmlspecialchars($this->Language->Get('chtnewmess')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="chat"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/type/replied" viewport="1">' . htmlspecialchars($this->Language->Get('chtrepliedmess')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="chat"><a href="' . SWIFT::Get('basename') . '/LiveChat/Message/QuickFilter/type/survey" viewport="1">' . htmlspecialchars($this->Language->Get('chtsurveymess')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_MessageManager $_SWIFT_MessageObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox(SWIFT_MessageManager $_SWIFT_MessageObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_informationHTML = '';

        $_messageURL = SWIFT::Get('basename') . '/LiveChat/Message/ViewMessage/' . $_SWIFT_MessageObject->GetMessageID();
        $_informationHTML .= '<div class="navinfoitem">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobmessageid') . '</div><div class="navinfoitemcontainer"><span class="navinfoitemlink"><a href="' . $_messageURL . '" viewport="1">' . $_SWIFT_MessageObject->GetProperty('messagemaskid') . '</a></span></div></div>';

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobuser') . '</div><div class="navinfoitemcontent">' . text_to_html_entities(StripName($_SWIFT_MessageObject->GetProperty('fullname'), 20)) . '</div></div>';


        $_departmentTitle = '';
        if (isset($_departmentCache[$_SWIFT_MessageObject->GetProperty('departmentid')])) {
            $_departmentTitle = $_departmentCache[$_SWIFT_MessageObject->GetProperty('departmentid')]['title'];
        }

        if (!empty($_departmentTitle)) {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobdepartment') . '</div><div class="navinfoitemcontent">' . text_to_html_entities(StripName($_departmentTitle, 20)) . '</div></div>';
        }

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobstatus') . '</div><div class="navinfoitemcontent">' . $_SWIFT_MessageObject->GetMessageStatusLabel() . '</div></div>';

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobtype') . '</div><div class="navinfoitemcontent">' . $_SWIFT_MessageObject->GetMessageTypeLabel() . '</div></div>';

        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }
}
