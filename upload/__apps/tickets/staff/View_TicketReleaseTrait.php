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
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

trait View_TicketReleaseTrait {

    /**
     * Render the Release Tab
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
     * @throws \Base\Library\Tag\SWIFT_Tag_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function RenderRelease(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_UserObject = null, $_listType = '', $_departmentID = 0,
        $_ticketStatusID = 0, $_ticketTypeID = 0, $_ticketLimitOffset = 0) {
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
        $_ReleaseTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, $this->Language->Get('tabrelease'), '', 1, 'release', false, false, 4, '');
        $_ReleaseTabObject->SetColumnWidth('15%');
        $_ReleaseTabObject->LoadToolbar();
        $_ReleaseTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/Tickets/Ticket/ReleaseSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);
        $_ReleaseTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        // Begin Properties
        $_dividerHTML = '<div class="ticketreleasepropertiesdivider"><img src="' . SWIFT::Get('themepathimages') . 'ticketpropertiesdivider.png" align="middle" border="0" /></div>';
        $_renderHTML = '<tr><td colspan="2"><div class="ticketreleasecontainer">';
        $_renderHTML .= '<div class="ticketreleaseproperties" id="releaseticketproperties" style="background-color: ' . htmlspecialchars($_titleBackgroundColor) . ';">';

        // Departments
        $_departmentSelectHTML = '<div class="ticketreleasepropertiesselect"><select id="release_departmentid" name="reldepartmentid" class="swiftselect" onchange="javascript: UpdateTicketStatusDiv(this, \'relticketstatusid\', false, false, \'releaseticketproperties\'); UpdateTicketTypeDiv(this, \'reltickettypeid\', false, false); UpdateTicketOwnerDiv(this, \'relownerstaffid\', false, false);" style="max-width:160px">';
        $_departmentSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_DEPARTMENT);
        $_departmentSelectHTML .= '</select></div>';

        $_renderHTML .= '<div class="ticketreleasepropertiesobject"><div class="ticketreleasepropertiestitle">' . $this->Language->Get('proptitledepartment') . '</div>' . $_departmentSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Owner
        $_ownerSelectHTML = '<div class="ticketreleasepropertiesselect"><div id="relownerstaffid_container"><select id="selectrelownerstaffid" name="relownerstaffid" class="swiftselect" style="max-width:160px">';
        $_ownerSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_OWNER);
        $_ownerSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketreleasepropertiesobject"><div class="ticketreleasepropertiestitle">' . $this->Language->Get('proptitleowner') . '</div>' . $_ownerSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Type
        $_ticketTypeSelectHTML = '<div class="ticketreleasepropertiesselect"><div id="reltickettypeid_container"><select id="selectreltickettypeid" name="reltickettypeid" class="swiftselect" style="max-width:160px">';
        $_ticketTypeSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_TYPE);
        $_ticketTypeSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketreleasepropertiesobject"><div class="ticketreleasepropertiestitle">' . $this->Language->Get('proptitletype') . '</div>' . $_ticketTypeSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Status
        $_ticketStatusSelectHTML = '<div class="ticketreleasepropertiesselect"><div id="relticketstatusid_container"><select id="selectrelticketstatusid" onchange="javascript: ResetStatusParentColor(this, \'releaseticketproperties\');" name="relticketstatusid" class="swiftselect" style="max-width:160px">';
        $_ticketStatusSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_STATUS);
        $_ticketStatusSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketreleasepropertiesobject"><div class="ticketreleasepropertiestitle">' . $this->Language->Get('proptitlestatus') . '</div>' . $_ticketStatusSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Priority
        $_ticketPrioritySelectHTML = '<div class="ticketreleasepropertiesselect"><select id="rel_ticketpriorityid" name="relticketpriorityid" class="swiftselect" onchange="javascript: ResetPriorityParentColor(this, \'releasepriorityproperties\');" style="max-width:160px">';
        $_ticketPrioritySelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_PRIORITY);
        $_ticketPrioritySelectHTML .= '</select></div>';

        $_renderHTML .= '<div class="ticketreleasepropertiesobject" id="releasepriorityproperties"' . IIF(!empty($_priorityBackgroundColor), ' style="background-color: ' . htmlspecialchars($_priorityBackgroundColor) . ';"') . '><div class="ticketreleasepropertiestitle">' . $this->Language->Get('proptitlepriority') . '</div>' . $_ticketPrioritySelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        $_renderHTML .= '</div>';

        $_renderHTML .= '</div></td></tr>';

        $_ReleaseTabObject->RowHTML($_renderHTML);

        // Tags
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            $_ticketTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID());
            $_ReleaseTabObject->TextMultipleAutoComplete('reltags', false, false, '/Base/Tags/QuickSearch', $_ticketTagContainer, 'fa-tags', 'gridrow2', true, 2, false, true);
        }

        // Notes
        $_ReleaseTabObject->Notes('releaseticketnotes', $this->Language->Get('addnotes'), '', 1);

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

        $_ReleaseTabObject->Radio('releasenotetype', $this->Language->Get('notetype'), '', $_radioContainer, false);


        // Options
        $_ReleaseTabObject->Title($this->Language->Get('options'), 'doublearrows.gif');

        $_dueDateline           = $_SWIFT_TicketObject->GetProperty('duetime');
        $_resolutionDueDateline = $_SWIFT_TicketObject->GetProperty('resolutionduedateline');

        $_dueDate           = IIF(!empty($_dueDateline), date(SWIFT_Date::GetCalendarDateFormat(), $_dueDateline), '');
        $_resolutionDueDate = IIF(!empty($_resolutionDueDateline), date(SWIFT_Date::GetCalendarDateFormat(), $_resolutionDueDateline), '');

        $_ReleaseTabObject->Date('releasedue', $this->Language->Get('dialogduetimeline'), '', $_dueDate, $_dueDateline, true);
        $_ReleaseTabObject->Date('releaseresolutiondue', $this->Language->Get('dialogresolutionduetimeline'), '', $_resolutionDueDate, $_resolutionDueDateline, true);

        $_billingEntriesHTML = $this->Language->Get('billworked') . ' <input type="text" class="swifttextnumeric2" name="relbillingtimeworked" id="relbillingtimeworked" value="" size="5" />';
        $_billingEntriesHTML .= '&nbsp;&nbsp;&nbsp;' . $this->Language->Get('billbillable') . ' <input type="text" class="swifttextnumeric2" name="relbillingtimebillable" id="relbillingtimebillable" value="" onfocus="javascript: HandleBillingBillableFocus(this, \'rel\');" size="5" />';
        $_ReleaseTabObject->DefaultDescriptionRow($this->Language->Get('billtimespent'), '', $_billingEntriesHTML);

        // Begin Hook: staff_ticket_releasetab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_releasetab')) ? eval($_hookCode) : false;
        // End Hook

        $_renderHTML = $_ReleaseTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= 'QueueFunction(function() {';
        $_renderHTML .= ' $("#relbillingtimeworked").mask("99:99");';
        $_renderHTML .= ' $("#relbillingtimebillable").mask("99:99");';
        $_renderHTML .= '}); reParseDoc();';
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }
}
