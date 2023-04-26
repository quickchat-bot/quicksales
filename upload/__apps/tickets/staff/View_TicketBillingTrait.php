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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_Base;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrackNote;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

trait View_TicketBillingTrait {
    /**
     * Render the Billing Tab for Tickets interface
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
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function RenderBilling(SWIFT_Ticket $_SWIFT_TicketObject, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID,
        $_ticketLimitOffset) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_ticketURLSuffix = SWIFT::Get('ticketurlsuffix');
        $this->UserInterface->Start(get_short_class($this),'', SWIFT_UserInterface::MODE_INSERT, false);
        $_BillingTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, $this->Language->Get('tabbilling'), '', 1, 'billing', false, false, 0, '');
        $_BillingTabObject->SetColumnWidth('15%');
        $_BillingTabObject->LoadToolbar();
        $_BillingTabObject->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle', '/Tickets/Ticket/BillingSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);
        $_BillingTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_billingHTML = $this->RenderBillingEntries($_SWIFT_TicketObject);

        if (!empty($_billingHTML)) {
            $_BillingTabObject->RowHTML('<tr class="gridrow3" id="ticketbillingcontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3" id="ticketbillingcontainertd">' . $_billingHTML . '</td></tr>');
        } else {
            $_BillingTabObject->RowHTML('<tr class="gridrow3" style="display: none;" id="ticketbillingcontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3" id="ticketbillingcontainertd"></td></tr>');
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertbilling') != '0') {
            $this->CreateBillingForm($_BillingTabObject, $_SWIFT->Staff->GetStaffID(), DATENOW, DATENOW, 0, 0);
        }

        $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_INSERT,
            array(SWIFT_CustomFieldGroup::GROUP_TIMETRACK), $_BillingTabObject);

        // Begin Hook: staff_ticket_billingtab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_billingtab')) ? eval($_hookCode) : false;
        // End Hook

        $_renderHTML = $_BillingTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= 'QueueFunction(function() {';
        $_renderHTML .= ' $("#billingtimeworked").mask("99:99");';
        $_renderHTML .= ' $("#billingtimebillable").mask("99:99");';
        $_renderHTML .= '}); reParseDoc();';
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Create a billing form
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceTab $_BillingTabObject The Billing Tab Object
     * @param int $_workerStaffID The Worker Staff ID
     * @param int $_defaultBillDate The UNIX Date Timestamp
     * @param int $_defaultWorkDate The Default work date in timestamp
     * @param int|string $_defaultTimeWorked (OPTIONAL)
     * @param int|string $_defaultTimeBillable (OPTIONAL)
     * @param string $_defaultNotes (OPTIONAL)
     * @param int $_defaultNoteColor (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CreateBillingForm(SWIFT_UserInterfaceTab $_BillingTabObject, $_workerStaffID, $_defaultBillDate, $_defaultWorkDate,
        $_defaultTimeWorked = '', $_defaultTimeBillable = '', $_defaultNotes = '', $_defaultNoteColor = 1, $_type = '') {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = (array)$this->Cache->Get('staffcache');

        foreach (array('_defaultTimeWorked', '_defaultTimeBillable') as $_variable) {
            $_value = $$_variable;

            $_timeMinutes = $_value/60;
            $_timeHours = $_timeMinutes/60;

            $_finalHours = floor($_timeHours);
            $_finalMinutes = $_timeMinutes - ($_finalHours*60);
            if (strlen($_finalHours) == 1) {
                $_finalHours = '0' . $_finalHours;
            }

            if (strlen($_finalMinutes) == 1) {
                $_finalMinutes = '0' . $_finalMinutes;
            }

            $$_variable = $_finalHours . ':' . $_finalMinutes;
        }

        $_billingEntriesHTML = $this->Language->Get('billworked') . ' <input type="text" class="swifttextnumeric2" name="' . $_type . 'billingtimeworked" id="' . $_type . 'billingtimeworked" value="' . $_defaultTimeWorked . '" size="5" />';
        $_billingEntriesHTML .= '&nbsp;&nbsp;&nbsp;' . $this->Language->Get('billbillable') . ' <input type="text" class="swifttextnumeric2" name="' . $_type . 'billingtimebillable" id="' . $_type . 'billingtimebillable" value="' . $_defaultTimeBillable . '" onfocus="javascript: HandleBillingBillableFocus(this, \'' . $_type . '\');" size="5" />';
        $_BillingTabObject->DefaultDescriptionRow($this->Language->Get('billtimespent'), '', $_billingEntriesHTML);

        $_parsedBillDate = $_parsedWorkDate = '';
        if (!empty($_defaultBillDate)) {
            $_parsedBillDate = date(SWIFT_Date::GetCalendarDateFormat(), $_defaultBillDate);
        }

        if (!empty($_defaultWorkDate)) {
            $_parsedWorkDate = date(SWIFT_Date::GetCalendarDateFormat(), $_defaultWorkDate);
        }

        $_BillingTabObject->Date($_type . 'billdate', $this->Language->Get('billdate'), '', $_parsedBillDate, $_defaultBillDate, true, true, 'SyncTicketBillDate(\'' . $_type . '\');');
        $_BillingTabObject->Date($_type . 'billworkdate', $this->Language->Get('billworkdate'), '', $_parsedWorkDate, $_defaultWorkDate, true, true);

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_staffCache as $_staffID => $_staffContainer) {
            $_optionsContainer[$_index]['title'] = $_staffContainer['fullname'];
            $_optionsContainer[$_index]['value'] = $_staffID;

            if ($_staffID == $_workerStaffID) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_BillingTabObject->Select($_type . 'billingworkerstaffid', $this->Language->Get('billworker'), '', $_optionsContainer);

        // Notes
        $_BillingTabObject->Notes($_type . 'billingnotes', $this->Language->Get('notes'), $_defaultNotes, $_defaultNoteColor);

        return true;
    }

    /**
     * Render the Billing Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_TicketTimeTrack $_SWIFT_TicketTimeTrackObject The SWIFT_TicketTimeTrack Object Poitner
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     * @throws \Tickets\Models\TimeTrack\SWIFT_TimeTrack_Exception
     */
    public function RenderBillingForm($_mode, SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_TicketTimeTrack $_SWIFT_TicketTimeTrackObject) {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $this->Cache->Get('staffcache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start('ticketbillingform', '/Tickets/Ticket/EditBillingSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_SWIFT_TicketTimeTrackObject->GetTicketTimeTrackID(), SWIFT_UserInterface::MODE_EDIT, true, true, false, false, 'ticketbillingcontainertd');

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN BILLING TAB
         * ###############################################
        */

        $_BillingTabObject = $this->UserInterface->AddTab($this->Language->Get('tabeditbilling'), 'icon_clock.png', 'editbilling', true);
        $_BillingTabObject->Overflow(400);

        /**
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2975: Editing a billing entry results in error being logged
         *
         */
        $_ticketTimeTrackNotes = '';
        $_SWIFT_TicketTimeTrackNoteObject = SWIFT_TicketTimeTrackNote::RetrieveObjectOnTicketTimeTrackID($_SWIFT_TicketTimeTrackObject);
        if ($_SWIFT_TicketTimeTrackNoteObject instanceof SWIFT_TicketTimeTrackNote && $_SWIFT_TicketTimeTrackNoteObject->GetIsClassLoaded()) {
            $_ticketTimeTrackNotes = $_SWIFT_TicketTimeTrackNoteObject->GetProperty('notes');
        }

        $this->CreateBillingForm($_BillingTabObject, $_SWIFT_TicketTimeTrackObject->GetProperty('workerstaffid'),
            $_SWIFT_TicketTimeTrackObject->GetProperty('dateline'), $_SWIFT_TicketTimeTrackObject->GetProperty('workdateline'),
            $_SWIFT_TicketTimeTrackObject->GetProperty('timespent'), $_SWIFT_TicketTimeTrackObject->GetProperty('timebillable'),
            $_ticketTimeTrackNotes, $_SWIFT_TicketTimeTrackObject->GetProperty('notecolor'), 'e');

        $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_EDIT,
            array(SWIFT_CustomFieldGroup::GROUP_TIMETRACK), $_BillingTabObject, $_SWIFT_TicketTimeTrackObject->GetTicketTimeTrackID());

        /*
         * ###############################################
         * END BILLING TAB
         * ###############################################
        */

        $_renderHTML = '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'QueueFunction(function() {';
        $_renderHTML .= ' $("#ebillingtimeworked").mask("99:99");';
        $_renderHTML .= ' $("#ebillingtimebillable").mask("99:99");';
        $_renderHTML .= '});';
        $_renderHTML .= '</script>';

        $_BillingTabObject->AppendHTML($_renderHTML);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Billing Tab for User Interface
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The User Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderBillingUser(SWIFT_User $_SWIFT_UserObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $this->UserInterface->Start(get_short_class($this),'', SWIFT_UserInterface::MODE_INSERT, false);
        $_BillingTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, $this->Language->Get('tabbilling'), '', 1, 'billing', false, false, 0, '');
        $_BillingTabObject->LoadToolbar();
        $_BillingTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_billingHTML = $this->RenderBillingEntries($_SWIFT_UserObject);

        if (!empty($_billingHTML)) {
            $_BillingTabObject->RowHTML('<tr class="gridrow3" id="ticketbillingcontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3">' . $_billingHTML . '</td></tr>');
        } else {
            $_BillingTabObject->RowHTML('<tr class="tablerow1_tr"><td valign="middle" align="left" class="tablerow1" colspan="8">' . $this->Language->Get('noinfoinview') . '</td></tr>');
        }

        $_renderHTML = $_BillingTabObject->GetDisplayHTML(true);

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'ClearFunctionQueue();';
        $_renderHTML .= 'reParseDoc();';
        $_renderHTML .= '</script>';

        echo $_renderHTML;

        return true;
    }

    /**
     * Render the Billing Time Track entries for the given Ticket/User
     *
     * @author Varun Shoor
     * @param SWIFT_Base $_SWIFT_InputObject The SWIFT_Input Object
     * @return mixed "_renderedHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RenderBillingEntries($_SWIFT_InputObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ((!$_SWIFT_InputObject instanceof SWIFT_Ticket || !$_SWIFT_InputObject->GetIsClassLoaded()) &&
            (!$_SWIFT_InputObject instanceof SWIFT_User || !$_SWIFT_InputObject->GetIsClassLoaded()) ) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Retrieve entries
        $_timeTrackContainer = $_ticketObjectContainer = $_ticketIDList = array();

        $_renderedHTML = '';

        $_totalTimeSpent = $_totalTimeBillable = 0;

        $_ticketIDList = array();
        if ($_SWIFT_InputObject instanceof SWIFT_Ticket) {
            $_ticketIDList[] = $_SWIFT_InputObject->GetTicketID();
        } else if ($_SWIFT_InputObject instanceof SWIFT_User) {
            $_ticketIDList = SWIFT_Ticket::GetTicketIDListOnUser($_SWIFT_InputObject);
        }

        // Fetch ticket time track entries
        $this->Database->Query("SELECT tickettimetracks.*, tickettimetracknotes.notes AS notes
            FROM " . TABLE_PREFIX . "tickettimetracks AS tickettimetracks
            LEFT JOIN " . TABLE_PREFIX . "tickettimetracknotes AS tickettimetracknotes ON (tickettimetracks.tickettimetrackid = tickettimetracknotes.tickettimetrackid)
            WHERE tickettimetracks.ticketid IN (" . BuildIN($_ticketIDList) . ") ORDER BY tickettimetracks.dateline ASC");
        while ($this->Database->NextRecord()) {
            $_timeTrackContainer[$this->Database->Record['tickettimetrackid']] = $this->Database->Record;
            $_ticketIDList[] = $this->Database->Record['ticketid'];

            $_totalTimeSpent += $this->Database->Record['timespent'];
            $_totalTimeBillable += $this->Database->Record['timebillable'];
        }

        if ($_SWIFT_InputObject instanceof SWIFT_User && count($_ticketIDList)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
            while ($this->Database->NextRecord()) {
                $_ticketObjectContainer[$this->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($this->Database->Record));
            }
        }

        if (!count($_timeTrackContainer)) {
            return '';
        }

        $_renderedHTML .= '<div class="ticketbillinginfocontainer2">';
        $_renderedHTML .= '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-clock-o"></i> ' .
            '<b>' . $this->Language->Get('billtotalworked') . '</b> ' . SWIFT_Date::ColorTime($_totalTimeSpent, false, true) .
            '&nbsp;&nbsp;&nbsp;&nbsp;' .
            '<b>' . $this->Language->Get('billtotalbillable') . '</b> ' . SWIFT_Date::ColorTime($_totalTimeBillable, false, true);
        $_renderedHTML .= '</div>';

        $_renderedHTML .= '<div id="ticketbillingcontainerdiv">';

        foreach ($_timeTrackContainer as $_ticketTimeTrackID => $_ticketTimeTrack) {
            $_icon = 'fa-clock-o';
            $_timeTrackColor = GetSanitizedNoteColor($_ticketTimeTrack['notecolor']);
            $_timeTrackContents = $_ticketTimeTrack['notes'];

            $_timeTrackTitle = '';

            // If this is a user type then we will need to display ticket information along with the other details + a link to open the ticket
            if ($_SWIFT_InputObject instanceof SWIFT_User && isset($_ticketObjectContainer[$_ticketTimeTrack['ticketid']])) {
                $_LinkedTicketObject = $_ticketObjectContainer[$_ticketTimeTrack['ticketid']];
                $_timeTrackTitle = '<a href="' . SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $_LinkedTicketObject->GetTicketID() . '" viewport="1">' . $_LinkedTicketObject->GetTicketDisplayID() . '</a> - ';
            }

            $_timeTrackTitle .= sprintf($this->Language->Get('billingtitle'), htmlspecialchars($_ticketTimeTrack['workerstaffname']), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_ticketTimeTrack['dateline']));

            // Only display the worked on value if its different
            if ($_ticketTimeTrack['workdateline'] != $_ticketTimeTrack['dateline']) {
                $_timeTrackTitle .= sprintf($this->Language->Get('billingtitlework'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_ticketTimeTrack['workdateline']));
            }

            // Edited but not by staff?
            if (empty($_ticketTimeTrack['editedstaffid']) && $_ticketTimeTrack['isedited'] == '1') {
                $_timeTrackTitle .= sprintf($this->Language->Get('billingeditedtitle2'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_ticketTimeTrack['editedtimeline']));
                // Edited by staff
            } else if (!empty($_ticketTimeTrack['editedstaffid']) && $_ticketTimeTrack['isedited'] == '1') {
                $_timeTrackTitle .= sprintf($this->Language->Get('billingeditedtitle'), htmlspecialchars($_ticketTimeTrack['editedstaffname']), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_ticketTimeTrack['editedtimeline']));

            }

            // Render the additional time track details
            $_timeTrackDetails = '<p>' . '<b>' . $this->Language->Get('billworked') . '</b> ' . SWIFT_Date::ColorTime($_ticketTimeTrack['timespent'], false, true) . '&nbsp;&nbsp;&nbsp;&nbsp;<b>' . $this->Language->Get('billbillable') . '</b> ' . SWIFT_Date::ColorTime($_ticketTimeTrack['timebillable'], false, true) . '</p>';

            $_finalTimeTrackContents = '';
            if (trim($_timeTrackContents) != '') {
                $_finalTimeTrackContents = '<p>' . nl2br(htmlspecialchars($_timeTrackContents)) . '</p>';
            }

            $_customFieldHTML = $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, SWIFT_UserInterface::MODE_INSERT,
                array(SWIFT_CustomFieldGroup::GROUP_TIMETRACK), null, $_ticketTimeTrackID, 0, true, true);
            $_finalCustomFieldHTML = '';
            if (!empty($_customFieldHTML)) {
                $_finalCustomFieldHTML = $_customFieldHTML;
            }

            $_renderedHTML .= '<div id="note' . ($_timeTrackColor) . '" class="bubble"><div class="notebubble"><cite class="tip"><strong><i class="fa ' .  $_icon . '" aria-hidden="true"></i> ' . $_timeTrackTitle . '</strong><div class="ticketnotesactions">';

            if ($_SWIFT_InputObject instanceof SWIFT_Ticket) {
                if ($_SWIFT->Staff->GetPermission('staff_tcanupdatebilling') != '0') {
                    $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: UICreateWindow(\'' . SWIFT::Get('basename') . '/Tickets/Ticket/EditBilling/' . $_SWIFT_InputObject->GetTicketID() . '/' . (int) ($_ticketTimeTrackID) . "', 'editbilling', '". $this->Language->Get('editbilling') ."', '". $this->Language->Get('loadingwindow') . '\', 650, 560, true, this);"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> ';
                }

                if ($_SWIFT->Staff->GetPermission('staff_tcandeletebilling') != '0') {
                    $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: TicketDeleteBilling(\'' . addslashes($this->Language->Get('ticketbillingdelconfirm')) . '\', \'' . (int) ($_SWIFT_InputObject->GetTicketID()) . '/' . (int) ($_ticketTimeTrackID) . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                }
            }

            $_renderedHTML .= '</div></cite><blockquote>' . $_finalTimeTrackContents . $_finalCustomFieldHTML . '<hr class="ticketbillinghr" />'. $_timeTrackDetails . '</blockquote></div></div>';
        }

        $_renderedHTML .= '</div>';

        return $_renderedHTML;
    }
}
