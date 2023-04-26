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

namespace LiveChat\Staff;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\User\SWIFT_User;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Hook;
use SWIFT_View;

/**
 * The Call View
 *
 * @author Varun Shoor
 */
class View_Call extends SWIFT_View
{
    /**
     * Render the Call
     *
     * @author Varun Shoor
     * @param SWIFT_Call $_SWIFT_CallObject The SWIFT_Call Object Pointer (Only for EDIT Mode)
     * @param bool $_noDialog Whether to display dialog
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render(SWIFT_Call $_SWIFT_CallObject, $_noDialog = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        $_isDialog = true;
        if ($_noDialog == true) {
            $_isDialog = false;
        }

        $this->UserInterface->Start(get_short_class($this), '/LiveChat/Call/ViewSubmit/' . $_SWIFT_CallObject->GetCallID(),
            SWIFT_UserInterface::MODE_EDIT, $_isDialog);
        $this->UserInterface->SetDialogOptions(false);

        $_SWIFT_FileManagerObject = false;

        try {
            $_SWIFT_FileManagerObject = new SWIFT_FileManager($_SWIFT_CallObject->GetProperty('fileid'));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        if ($_SWIFT_FileManagerObject instanceof SWIFT_FileManager && $_SWIFT_FileManagerObject->GetIsClassLoaded()) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('download'), 'fa-download', SWIFT::Get('basename') . '/LiveChat/Call/Download/' .
                $_SWIFT_CallObject->GetCallID(), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/Call/Delete/' .
            $_SWIFT_CallObject->GetCallID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);

        // Begin Hook: staff_call_toolbar
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_call_toolbar')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('call'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN CALL TAB
         * ###############################################
         */
        $_CallTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcall'), 'icon_phone.gif', 'call', true);

        $_CallTabObject->SetColumnWidth('120');

        if ($_SWIFT_CallObject->GetProperty('fileid') != '0') {


            if ($_SWIFT_FileManagerObject instanceof SWIFT_FileManager && $_SWIFT_FileManagerObject->GetIsClassLoaded()) {

                /*
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-3634: Time duration implementation on circle player
                 *
                 * Comments: None
                 */
                $_CallTabObject->EventQueue('show', 'var myCirclePlayer = new CirclePlayer("#callrecording", { mp3: "' . SWIFT::Get("basename") . '/LiveChat/Call/PlayRecording/' . $_SWIFT_CallObject->GetCallID() . '" }, {timeupdate: function(event) { $("#cp-time").text($.jPlayer.convertTime(event.jPlayer.status.currentTime)); }});');

                $_renderCirclePlayer = '';
                $_renderCirclePlayer .= '<tr><td rowspan="10" width="200">';
                $_renderCirclePlayer .= '<div id="callrecording" class="cp-jplayer"></div>
                        <!-- The container for the interface can go where you want to display it. Show and hide it as you need. -->
                        <div id="cp_container_1" class="cp-container">
                            <div class="cp-buffer-holder"> <!-- .cp-gt50 only needed when buffer is > than 50% -->
                                <div class="cp-buffer-1"></div>
                                <div class="cp-buffer-2"></div>
                            </div>

                            <div class="cp-progress-holder"> <!-- .cp-gt50 only needed when progress is > than 50% -->
                                <div class="cp-progress-1"></div>
                                <div class="cp-progress-2"></div>
                            </div>
                            <div class="cp-circle-control"></div>
                            <ul class="cp-controls">
                                <li><a href="#" class="cp-play" tabindex="1">play</a></li>
                                <li><a href="#" class="cp-pause" style="display:none;" tabindex="1">pause</a></li> <!-- Needs the inline style here, or jQuery.show() uses display:inline instead of display:block -->
                            </ul>
                        </div>
                        <div id="cp-time" class="text-center"></div>';
                $_renderCirclePlayer .= '</td></tr>';
                $_CallTabObject->RowHTML($_renderCirclePlayer);
            }
        }

        $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvphone'), '', htmlspecialchars($_SWIFT_CallObject->GetProperty('phonenumber')));

        $_userFullName = text_to_html_entities($_SWIFT_CallObject->GetProperty('userfullname'));
        $_userLinkExtended = '%s';
        if ($_SWIFT_CallObject->GetProperty('userid') != '0') {
            try {
                $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_SWIFT_CallObject->GetProperty('userid')));

                $_userFullName = text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname'));
                $_userLink = SWIFT::Get('basename') . '/Base/User/Edit/' . $_SWIFT_UserObject->GetUserID();
                $_userLinkExtended = '<a href="' . $_userLink . '" viewport="1">%s</a>';
                $_userLinkExtended .= '&nbsp;<a href="' . $_userLink . '" target="_blank"><img src="' . SWIFT::Get('themepathimages') . 'icon_newwindow_gray.png' . '" align="absmiddle" border="0" /></a>&nbsp;';
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        if (!empty($_userFullName)) {
            $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvuser'), '', sprintf($_userFullName, $_userLinkExtended));
        }

        $_staffID = $_SWIFT_CallObject->GetProperty('staffid');
        $_staffFullName = text_to_html_entities($_SWIFT_CallObject->GetProperty('stafffullname'));
        if (!empty($_staffID) && isset($_staffCache[$_staffID])) {
            $_staffFullName = text_to_html_entities($_staffCache[$_staffID]['fullname']);
        }

        if (!empty($_staffFullName)) {
            $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvstaff'), '', text_to_html_entities($_staffFullName));
        }

        $_chatObjectID = $_SWIFT_CallObject->GetProperty('chatobjectid');
        if (!empty($_chatObjectID)) {
            $_SWIFT_ChatObject = false;
            try {
                $_SWIFT_ChatObject = new SWIFT_Chat($_SWIFT_CallObject->GetProperty('chatobjectid'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                return false;
            }

            if ($_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded()) {
                $_chatID = $_SWIFT_ChatObject->GetProcessedChatID();
                $_chatLink = SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewCHat/' . $_SWIFT_ChatObject->GetChatObjectID();
                $_chatLinkExtended = '<a href="' . $_chatLink . '" viewport="1">' . $_chatID . '</a>';
                $_chatLinkExtended .= '&nbsp;<a href="' . $_chatLink . '" target="_blank"><img src="' . SWIFT::Get('themepathimages') . 'icon_newwindow_gray.png' . '" align="absmiddle" border="0" /></a>&nbsp;';
                $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvchat'), '', $_chatLinkExtended);
            }
        }

        $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvcallstarted'), '', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_CallObject->GetProperty('dateline')));

        $_callEnded = $this->Language->Get('na');
        if ($_SWIFT_CallObject->GetProperty('enddateline') != '0') {
            $_callEnded = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_CallObject->GetProperty('enddateline'));
        }
        $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvcallended'), '', $_callEnded);
        $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvduration'), '', SWIFT_Date::ColorTime($_SWIFT_CallObject->GetProperty('duration'), true, true));
        $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvcallstatus'), '', SWIFT_Call::GetStatusLabel($_SWIFT_CallObject->GetProperty('callstatus')));
        $_CallTabObject->DefaultDescriptionRow($this->Language->Get('cvcalltype'), '', SWIFT_Call::GetTypeLabel($_SWIFT_CallObject->GetProperty('calltype')));

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        // Begin Hook: staff_call_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_call_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Call Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid($_searchStoreID = null)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('callgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'calls
                    WHERE ((' . $this->UserInterfaceGrid->BuildSQLSearch('phonenumber') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('userfullname') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('stafffullname') . '))',

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'calls
                    WHERE ((' . $this->UserInterfaceGrid->BuildSQLSearch('phonenumber') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('userfullname') . ')
                    OR (' . $this->UserInterfaceGrid->BuildSQLSearch('stafffullname') . '))');
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
            "SELECT * FROM " . TABLE_PREFIX . "calls
                    WHERE callid IN (%s)",
            SWIFT_SearchStore::TYPE_CALLS, '/LiveChat/Call/Manage/-1');

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'calls', 'SELECT COUNT(*) AS totalitems FROM ' .
            TABLE_PREFIX . 'calls');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('callid', 'callid',
            SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('phonenumber', $this->Language->Get('cphonenumber'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('userfullname', $this->Language->Get('cuserfullname'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('stafffullname', $this->Language->Get('cstafffullname'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('callstatus', $this->Language->Get('ccallstatus'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('ccalldateline'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('LiveChat\Staff\Controller_Call', 'DeleteList'), $this->Language->Get('actionconfirm')));

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

        $_callIcon = 'fa-phone';
        if ($_fieldContainer['callstatus'] == SWIFT_Call::STATUS_PENDING && $_fieldContainer['calltype'] == SWIFT_Call::TYPE_INBOUND) {
            $_callIcon = 'fa-exclamation-triangle';
        } else if ($_fieldContainer['calltype'] == SWIFT_Call::TYPE_INBOUND) {
            $_callIcon = 'fa-sign-in';
        } else if ($_fieldContainer['calltype'] == SWIFT_Call::TYPE_OUTBOUND) {
            $_callIcon = 'fa-sign-out';
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_callIcon . '" aria-hidden="true"></i>';

        $_fieldContainer['phonenumber'] = '<a href="' . SWIFT::Get('basename') . '/LiveChat/Call/ViewCall/' . (int)($_fieldContainer['callid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . "/LiveChat/Call/ViewCall/" . (int)($_fieldContainer['callid']) . "', 'viewcall', '" .
            sprintf($_SWIFT->Language->Get('winviewcall'), htmlspecialchars($_fieldContainer['phonenumber'])) . "', '" .
            $_SWIFT->Language->Get('loadingwindow') . "', 650, 470, true, this);" . '" title="' . $_SWIFT->Language->Get('winviewcall') .
            '">' . htmlspecialchars($_fieldContainer['phonenumber']) . '</a>';

        $_fieldContainer['userfullname'] = text_to_html_entities($_fieldContainer['userfullname']);
        $_fieldContainer['stafffullname'] = text_to_html_entities($_fieldContainer['stafffullname']);

        $_fieldContainer['callstatus'] = SWIFT_Call::GetStatusLabel($_fieldContainer['callstatus']);
        $_fieldContainer['calltype'] = SWIFT_Call::GetTypeLabel($_fieldContainer['calltype']);

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        return $_fieldContainer;
    }

    /**
     * Render the Call Tree
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

            return false;
        }

        $_renderHTML = '<ul class="swifttree">';

        // Begin Hook: staff_call_tree
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_call_tree')) ? eval($_hookCode) : false;
        // End Hook

        // Begin Hook: staff_call_tree
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_call_tree')) ? eval($_hookCode) : false;
        // End Hook

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('callfdate') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/date/today" viewport="1">' . htmlspecialchars($this->Language->Get('ctoday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/date/yesterday" viewport="1">' . htmlspecialchars($this->Language->Get('cyesterday')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/date/l7" viewport="1">' . htmlspecialchars($this->Language->Get('cl7days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/date/l30" viewport="1">' . htmlspecialchars($this->Language->Get('cl30days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/date/l180" viewport="1">' . htmlspecialchars($this->Language->Get('cl180days')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="date"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/date/l365" viewport="1">' . htmlspecialchars($this->Language->Get('cl365days')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('callftype') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="callinbound"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/type/inbound" viewport="1">' . htmlspecialchars($this->Language->Get('type_inbound')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="calloutbound"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/type/outbound" viewport="1">' . htmlspecialchars($this->Language->Get('type_outbound')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="callmissed"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/type/missed" viewport="1">' . htmlspecialchars($this->Language->Get('type_missed')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '<li><span class="funnel"><a href="javascript: void(0);">' . $this->Language->Get('callfstatus') . '</a></span>';
        $_renderHTML .= '<ul>';
        $_renderHTML .= '<li><span class="call"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/status/pending" viewport="1">' . htmlspecialchars($this->Language->Get('status_pending')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="call"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/status/accepted" viewport="1">' . htmlspecialchars($this->Language->Get('status_accepted')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="call"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/status/ended" viewport="1">' . htmlspecialchars($this->Language->Get('status_ended')) . '</a></span></li>';
        $_renderHTML .= '<li><span class="call"><a href="' . SWIFT::Get('basename') . '/LiveChat/Call/QuickFilter/status/rejected" viewport="1">' . htmlspecialchars($this->Language->Get('status_rejected')) . '</a></span></li>';
        $_renderHTML .= '</ul></li>';

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }

    /**
     * Render the History Grid
     *
     * @author Varun Shoor
     * @param array $_historyContainer The History Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderCallHistoryGrid($_historyContainer)
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
        $_columnContainer[1]['value'] = $this->Language->Get('cphonenumber');
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[2]['value'] = $this->Language->Get('ccalltype');
        $_columnContainer[2]['align'] = 'left';
        $_columnContainer[2]['width'] = '120';
        $_columnContainer[3]['value'] = $this->Language->Get('ccallstatus');
        $_columnContainer[3]['align'] = 'left';
        $_columnContainer[3]['width'] = '120';
        $_columnContainer[4]['value'] = $this->Language->Get('cvcallstarted');
        $_columnContainer[4]['align'] = 'left';
        $_columnContainer[4]['width'] = '180';
        $_columnContainer[5]['value'] = $this->Language->Get('cvduration');
        $_columnContainer[5]['align'] = 'left';
        $_columnContainer[5]['width'] = '120';

        $_HistoryTabObject->Row($_columnContainer, 'gridtabletitlerow');

        foreach ($_historyContainer as $_key => $_val) {
            $_callIcon = 'icon_phone.gif';
            if ($_val['callstatus'] == SWIFT_Call::STATUS_PENDING && $_val['calltype'] == SWIFT_Call::TYPE_INBOUND) {
                $_callIcon = 'icon_phone_missed.gif';
            } else if ($_val['calltype'] == SWIFT_Call::TYPE_INBOUND) {
                $_callIcon = 'icon_phone_incoming.gif';
            } else if ($_val['calltype'] == SWIFT_Call::TYPE_OUTBOUND) {
                $_callIcon = 'icon_phone_outgoing.gif';
            }

            $_callURL = SWIFT::Get('basename') . '/LiveChat/Call/ViewCall/' . (int)($_val['callid']) . '/1' . 0;

            $_columnContainer = array();
            $_columnContainer[0]['value'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_callIcon . '" align="absmidle" border="0" />';
            $_columnContainer[0]['align'] = 'center';
            $_columnContainer[1]['value'] = '<a href="' . $_callURL . '" viewport="1">' . htmlspecialchars($_val['phonenumber']) . '</a>';
            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[2]['value'] = SWIFT_Call::GetTypeLabel($_val['calltype']);
            $_columnContainer[2]['align'] = 'center';
            $_columnContainer[3]['value'] = SWIFT_Call::GetStatusLabel($_val['callstatus']);
            $_columnContainer[3]['align'] = 'center';
            $_columnContainer[4]['value'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_val['dateline']);
            $_columnContainer[4]['align'] = 'center';
            $_columnContainer[5]['value'] = SWIFT_Date::ColorTime($_val['duration'], true, true);
            $_columnContainer[5]['align'] = 'center';

            $_HistoryTabObject->Row($_columnContainer);
        }

        if (!count($_historyContainer)) {
            $_columnContainer = array();
            $_columnContainer[0]['value'] = $this->Language->Get('noinfoinview');
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['colspan'] = '6';

            $_HistoryTabObject->Row($_columnContainer);
        }

        $_renderHTML = $_HistoryTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= '</script>';


        echo $_renderHTML;

        return true;
    }
}
