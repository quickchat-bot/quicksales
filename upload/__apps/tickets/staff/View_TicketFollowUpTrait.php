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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\FollowUp\SWIFT_TicketFollowUp;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

trait View_TicketFollowUpTrait {
    /**
     * Render the Follow-Up Tab
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The Ticket Object
     * @param SWIFT_User $_SWIFT_UserObject The User Object
     * @param string $_prefix The ID Prefix Value
     * @param string $_listType The List Type
     * @param int $_departmentID The Department ID
     * @param int $_ticketStatusID The Ticket Status ID
     * @param int $_ticketTypeID The Ticket Type ID
     * @param int $_ticketLimitOffset The offset to display ticket posts on
     * @param int $_isInline Whether its an inline display
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function RenderFollowUp(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_UserObject = null, $_prefix = '', $_listType = '', $_departmentID = 0,
        $_ticketStatusID = 0, $_ticketTypeID = 0, $_ticketLimitOffset = 0, $_isInline = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_ticketURLSuffix = $_listType . '/' . $_departmentID . '/' . $_ticketStatusID . '/' . $_ticketTypeID;

        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');

        $_ticketStatusContainer = false;
        if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            $_ticketStatusContainer = $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')];
        }

        $_ticketPriorityContainer = false;
        if (isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')])) {
            $_ticketPriorityContainer = $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')];
        }

        $_titleBackgroundColor = '#626262';
        if (!empty($_ticketStatusContainer)) {
            $_titleBackgroundColor = $_ticketStatusContainer['statusbgcolor'];
        }

        $_priorityBackgroundColor = false;
        if (!empty($_ticketPriorityContainer) && !empty($_ticketPriorityContainer['bgcolorcode'])) {
            $_priorityBackgroundColor = $_ticketPriorityContainer['bgcolorcode'];
        }

        $this->UserInterface->Start(get_short_class($this),'', SWIFT_UserInterface::MODE_INSERT, false);
        $_FollowUpTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, $this->Language->Get('tabfollowup'), '', 1, 'followup', false, false, 0, '');
        $_FollowUpTabObject->SetColumnWidth('8%');

        if ($_isInline == false) {
            $_FollowUpTabObject->LoadToolbar();
            $_FollowUpTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/Tickets/Ticket/FollowUpSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);
            $_FollowUpTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            if ($_SWIFT_TicketObject->GetProperty('hasfollowup') == '1') {
                $_FollowUpTabObject->RowHTML('<tr><td>' . $this->RenderFollowUpEntries($_SWIFT_TicketObject) . '</td></tr>');
            }
        }

        $_selectorHTML = '<select class="swiftselect" name="' . $_prefix . 'followuptype" onChange="javascript: FollowUpTrigger(this, \'' . $_prefix . '\');">';
        $_selectorHTML .= '<option value="minutes">' . $this->Language->Get('followupmins') . '</option><option value="hours" selected>' . $this->Language->Get('followuphours') . '</option><option value="days">' . $this->Language->Get('followupdays') . '</option><option value="weeks">' . $this->Language->Get('followupweeks') . '</option><option value="months">' . $this->Language->Get('followupmonths') . '</option><option value="custom">' . $this->Language->Get('followupcustom') . '</option></select><div id="' . $_prefix . 'followupblock" style="display: inline; padding-left: 6px;"><input type="text" name="' . $_prefix . 'followupvalue" class="swifttext" id="' . $_prefix . 'followupvalue" value="" size="4" /></div>';

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = $_selectorHTML;

        $_FollowUpTabObject->Row($_columnContainer, '', '');
        $_FollowUpTabObject->RowHTML('<tr><td><div id="' . $_prefix . 'followupcustomvaluecontainer' . '" style="DISPLAY: none;"><table width="100%" border="0" cellspacing="0" cellpadding="0">');
        $_FollowUpTabObject->Date($_prefix . 'followupcustomvalue', $this->Language->Get('date'), '', '', DATENOW, true, true);
        $_FollowUpTabObject->RowHTML('</table></div></td></tr>');

        // Begin Properties
        $_FollowUpTabObject->Title('<label for="' . $_prefix . 'dochangeproperties"><input type="checkbox" name="' . $_prefix . 'dochangeproperties" onclick="javascript: ToggleTicketFollowUpCheckbox(this);" value="1" id="' . $_prefix . 'dochangeproperties" /> ' . $this->Language->Get('fugeneral') . '</label>');
        $_FollowUpTabObject->RowHTML('<tr><td><div id="' . $_prefix . 'dochangeproperties_container' . '" style="DISPLAY: none;"><table width="100%" border="0" cellspacing="0" cellpadding="0">');

        $_dividerHTML = '<div class="ticketfupropertiesdivider"><img src="' . SWIFT::Get('themepathimages') . 'ticketpropertiesdivider.png" align="middle" border="0" /></div>';
        $_renderHTML = '<tr><td colspan="2"><div class="ticketfucontainer">';
        $_renderHTML .= '<div class="ticketfuproperties" id="' . $_prefix . 'ticketproperties" style="background-color: ' . htmlspecialchars($_titleBackgroundColor) . ';">';

        // Departments
        $_departmentSelectHTML = '<div class="ticketfupropertiesselect"><select id="' . $_prefix . '_departmentid" name="' . $_prefix . 'departmentid" class="swiftselect" onchange="javascript: UpdateTicketStatusDiv(this, \'' . $_prefix . 'ticketstatusid\', false, false, \'' . $_prefix . 'ticketproperties\'); UpdateTicketTypeDiv(this, \'' . $_prefix . 'tickettypeid\', false, false); UpdateTicketOwnerDiv(this, \'' . $_prefix . 'ownerstaffid\', false, false);" style="max-width:160px;">';
        $_departmentSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_DEPARTMENT);
        $_departmentSelectHTML .= '</select></div>';

        $_renderHTML .= '<div class="ticketfupropertiesobject"><div class="ticketfupropertiestitle">' . $this->Language->Get('proptitledepartment') . '</div>' . $_departmentSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Owner
        $_ownerSelectHTML = '<div class="ticketfupropertiesselect"><div id="' . $_prefix . 'ownerstaffid_container"><select id="select' . $_prefix . 'ownerstaffid" name="' . $_prefix . 'ownerstaffid" class="swiftselect" style="max-width:160px;">';
        $_ownerSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_OWNER);
        $_ownerSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketfupropertiesobject"><div class="ticketfupropertiestitle">' . $this->Language->Get('proptitleowner') . '</div>' . $_ownerSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Type
        $_ticketTypeSelectHTML = '<div class="ticketfupropertiesselect"><div id="' . $_prefix . 'tickettypeid_container"><select id="select' . $_prefix . 'tickettypeid" name="' . $_prefix . 'tickettypeid" class="swiftselect" style="max-width:160px;">';
        $_ticketTypeSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_TYPE);
        $_ticketTypeSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketfupropertiesobject"><div class="ticketfupropertiestitle">' . $this->Language->Get('proptitletype') . '</div>' . $_ticketTypeSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Status
        $_ticketStatusSelectHTML = '<div class="ticketfupropertiesselect"><div id="' . $_prefix . 'ticketstatusid_container"><select id="select' . $_prefix . 'ticketstatusid" onchange="javascript: ResetStatusParentColor(this, \'' . $_prefix . 'ticketproperties\');" name="' . $_prefix . 'ticketstatusid" class="swiftselect" style="max-width:160px;">';
        $_ticketStatusSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_STATUS);
        $_ticketStatusSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketfupropertiesobject"><div class="ticketfupropertiestitle">' . $this->Language->Get('proptitlestatus') . '</div>' . $_ticketStatusSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Priority
        $_ticketPrioritySelectHTML = '<div class="ticketfupropertiesselect"><select id="' . $_prefix . '_ticketpriorityid" name="' . $_prefix . 'ticketpriorityid" class="swiftselect" onchange="javascript: ResetPriorityParentColor(this, \'' . $_prefix . 'priorityproperties\');" style="max-width:160px;">';
        $_ticketPrioritySelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_PRIORITY);
        $_ticketPrioritySelectHTML .= '</select></div>';

        $_renderHTML .= '<div class="ticketfupropertiesobject" id="' . $_prefix . 'priorityproperties"' . IIF(!empty($_priorityBackgroundColor), ' style="background-color: ' . htmlspecialchars($_priorityBackgroundColor) . ';"') . '><div class="ticketfupropertiestitle">' . $this->Language->Get('proptitlepriority') . '</div>' . $_ticketPrioritySelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        $_renderHTML .= '</div>';

        $_renderHTML .= '</div></td></tr>';

        $_FollowUpTabObject->RowHTML($_renderHTML);
        $_FollowUpTabObject->RowHTML('</table></div></td></tr>');



        // Add Note
        $_FollowUpTabObject->Title('<label for="' . $_prefix . 'donote"><input type="checkbox" name="' . $_prefix . 'donote" onclick="javascript: ToggleTicketFollowUpCheckbox(this);" value="1" id="' . $_prefix . 'donote" /> ' . $this->Language->Get('fuaddnote') . '</label>');

        $_FollowUpTabObject->RowHTML('<tr><td><div id="' . $_prefix . 'donote_container' . '" style="DISPLAY: none;"><table width="100%" border="0" cellspacing="0" cellpadding="4">');

        $_FollowUpTabObject->Notes($_prefix . 'ticketnotes', $this->Language->Get('addnotes'), '', 1);

        $_radioContainer = array();
        $_radioContainer[0]['title'] = $this->Language->Get('notes_ticket');
        $_radioContainer[0]['value'] = 'ticket';
        $_radioContainer[0]['checked'] = true;

        if ($_SWIFT->Staff->GetPermission('staff_caninsertusernote') != '0' && $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_radioContainer[1]['title'] = $this->Language->Get('notes_user');
            $_radioContainer[1]['value'] = 'user';

            $_radioContainer[2]['title'] = $this->Language->Get('notes_userorganization');
            $_radioContainer[2]['value'] = 'userorganization';
        }

        $_FollowUpTabObject->Radio($_prefix . 'notetype', $this->Language->Get('notetype'), '', $_radioContainer, false);

        $_FollowUpTabObject->RowHTML('</table></div></td></tr>');

        // Post Reply
        $_FollowUpTabObject->Title('<label for="' . $_prefix . 'doreply"><input type="checkbox" name="' . $_prefix . 'doreply" onclick="javascript: ToggleTicketFollowUpCheckbox(this);" value="1" id="' . $_prefix . 'doreply" /> ' . $this->Language->Get('fupostreply') . '</label>');
        $_FollowUpTabObject->RowHTML('<tr><td><div id="' . $_prefix . 'doreply_container' . '" style="DISPLAY: none;"><table width="100%" border="0" cellspacing="0" cellpadding="4">');

        $_FollowUpTabObject->TextArea($_prefix . 'replycontents', '', '', '', '30', '10', false, '');

        $_FollowUpTabObject->RowHTML('</table></div></td></tr>');

        // Forward
        $_FollowUpTabObject->Title('<label for="' . $_prefix . 'doforward"><input type="checkbox" name="' . $_prefix . 'doforward" onclick="javascript: ToggleTicketFollowUpCheckbox(this);" value="1" id="' . $_prefix . 'doforward" /> ' . $this->Language->Get('fuforward') . '</label>');
        $_FollowUpTabObject->RowHTML('<tr><td><div id="' . $_prefix . 'doforward_container' . '" style="DISPLAY: none;"><table width="100%" border="0" cellspacing="0" cellpadding="4">');
        $_FollowUpTabObject->TextMultipleAutoComplete($_prefix . 'to', $this->Language->Get('dispatchto'), '(Email will be sent to first recipient only).', '/Tickets/Ajax/SearchEmail', array(), 'fa-envelope-o', false, false, 2, false, false, false, '', array('containemail' => true));

        $_FollowUpTabObject->TextArea($_prefix . 'forwardcontents', '', '', '', '30', '10', false, '');

        $_FollowUpTabObject->RowHTML('</table></div></td></tr>');

        // Begin Hook: staff_ticket_followuptab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_followuptab')) ? eval($_hookCode) : false;
        // End Hook

        $_renderHTML = $_FollowUpTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'function initFollowUpTab() {' .
            GetTinyMceCode('textarea#' . $_prefix .
                'replycontents, textarea#' . $_prefix . 'forwardcontents') .
            '};';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= 'QueueFunction(function() {';
        $_renderHTML .= ' $("#relbillingtimeworked").mask("99:99");';
        $_renderHTML .= ' $("#relbillingtimebillable").mask("99:99");';
        if ((int)$_SWIFT->Settings->Get('t_tinymceeditor') !== 0) {
            $_renderHTML .= ' initFollowUpTab();';
        }
        $_renderHTML .= '}); reParseDoc();';
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Render the Follow-Up Entries
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return string Rendered HTML
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderFollowUpEntries(SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');

        $_ticketFollowUpContainer = SWIFT_TicketFollowUp::RetrieveOnTicket($_SWIFT_TicketObject);
        if (!count($_ticketFollowUpContainer)) {
            return '';
        }

        $_finalHTML = '<ol id="followuplist" class="followuplist"><div>';

        foreach ($_ticketFollowUpContainer as $_ticketFollowUpID => $_SWIFT_TicketFollowUpObject) {
            $_staffName = $this->Language->Get('na');
            if (isset($_staffCache[$_SWIFT_TicketFollowUpObject->GetProperty('staffid')])) {
                $_staffName = text_to_html_entities($_staffCache[$_SWIFT_TicketFollowUpObject->GetProperty('staffid')]['fullname']);
            }

            $_deleteHTML = '';
            if ($_SWIFT->Staff->GetPermission('staff_tcandeletefollowup') != '0') {
                $_deleteHTML = '<a href="javascript: void(0);" onclick="javascript: doConfirmViewport(\''. addslashes($this->Language->Get('actionconfirm')) .'\', \''. SWIFT::Get('basename') . '/Tickets/Ticket/DeleteFollowUp/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_SWIFT_TicketFollowUpObject->GetTicketFollowUpID() . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            }

            $_finalHTML .= '<li>';
            $_finalHTML .= '<div><b>' . sprintf($this->Language->Get('followup_willrunattime'),
                    SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketFollowUpObject->GetProperty('executiondateline'), false, false, true),
                    $_staffName,
                    SWIFT_Date::ColorTime($_SWIFT_TicketFollowUpObject->GetProperty('executiondateline')-DATENOW, true)) . '</b>&nbsp;&nbsp;' . $_deleteHTML . '';
            $_finalHTML .= '<ul>';

            if ($_SWIFT_TicketFollowUpObject->GetProperty('dochangeproperties') == '1') {
                if (isset($_departmentCache[$_SWIFT_TicketFollowUpObject->GetProperty('departmentid')])) {
                    $_finalHTML .= '<li>' . sprintf($this->Language->Get('followup_willchangedepartmentto'), text_to_html_entities($_departmentCache[$_SWIFT_TicketFollowUpObject->GetProperty('departmentid')]['title'])) . '</li>';
                }

                if (isset($_staffCache[$_SWIFT_TicketFollowUpObject->GetProperty('ownerstaffid')])) {
                    $_finalHTML .= '<li>' . sprintf($this->Language->Get('followup_willchangeownerto'), text_to_html_entities($_staffCache[$_SWIFT_TicketFollowUpObject->GetProperty('ownerstaffid')]['fullname'])) . '</li>';
                } else if ($_SWIFT_TicketFollowUpObject->GetProperty('ownerstaffid') == '0') {
                    $_finalHTML .= '<li>' . $this->Language->Get('followup_removeowner') . '</li>';
                }

                if (isset($_ticketTypeCache[$_SWIFT_TicketFollowUpObject->GetProperty('tickettypeid')])) {
                    $_finalHTML .= '<li>' . sprintf($this->Language->Get('followup_willchangetypeto'), htmlspecialchars($_ticketTypeCache[$_SWIFT_TicketFollowUpObject->GetProperty('tickettypeid')]['title'])) . '</li>';
                }

                if (isset($_ticketStatusCache[$_SWIFT_TicketFollowUpObject->GetProperty('ticketstatusid')])) {
                    $_finalHTML .= '<li>' . sprintf($this->Language->Get('followup_willchangestatusto'), htmlspecialchars($_ticketStatusCache[$_SWIFT_TicketFollowUpObject->GetProperty('ticketstatusid')]['title'])) . '</li>';
                }

                if (isset($_ticketPriorityCache[$_SWIFT_TicketFollowUpObject->GetProperty('priorityid')])) {
                    $_finalHTML .= '<li>' . sprintf($this->Language->Get('followup_willchangepriorityto'), htmlspecialchars($_ticketPriorityCache[$_SWIFT_TicketFollowUpObject->GetProperty('priorityid')]['title'])) . '</li>';
                }
            }

            if ($_SWIFT_TicketFollowUpObject->GetProperty('donote') == '1') {
                if ($_SWIFT_TicketFollowUpObject->GetProperty('notetype') == 'ticket') {
                    $_finalHTML .= '<li>' . $this->Language->Get('followup_willaddstaffnotes') . '</li>';
                } else {
                    $_finalHTML .= '<li>' . $this->Language->Get('followup_willaddusernotes') . '</li>';
                }
            }

            if ($_SWIFT_TicketFollowUpObject->GetProperty('doreply') == '1') {
                $_finalHTML .= '<li>' . $this->Language->Get('followup_willaddareply') . '</li>';
            }

            if ($_SWIFT_TicketFollowUpObject->GetProperty('doforward') == '1') {
                $_finalHTML .= '<li>' . sprintf($this->Language->Get('followup_willforwardto'), htmlspecialchars($_SWIFT_TicketFollowUpObject->GetProperty('forwardemailto'))) . '</li>';
            }

            $_finalHTML .= '</ul></li>';
        }

        $_finalHTML .= '</div></ol>';

        return $_finalHTML;
    }
}
