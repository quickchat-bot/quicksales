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
use SWIFT_Exception;
use SWIFT_Hook;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketLinkedTable;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserOrganization;

trait View_TicketBoxesTrait {

    /**
     * Render the Workflow Box
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @param array $_ticketLinkedTableContainer The Linked Table Values Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderWorkflowBox(SWIFT_Ticket $_SWIFT_TicketObject, $_ticketLinkedTableContainer) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanworkflow') == '0') {
            return false;
        }

        if (!isset($_ticketLinkedTableContainer[SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW])) {
            return false;
        }

        $_workflowHTML = '';

        $_ticketWorkflowCache = $this->Cache->Get('ticketworkflowcache');

        foreach ($_ticketLinkedTableContainer[SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW] as $_ticketLinkedTableValue) {
            $_ticketWorkflowID = $_ticketLinkedTableValue['linktypeid'];

            if (!isset($_ticketWorkflowCache[$_ticketWorkflowID])) {
                continue;
            }

            $_ticketWorkflowContainer = $_ticketWorkflowCache[$_ticketWorkflowID];

            $_staffGroupIDList = SWIFT_StaffGroupLink::RetrieveListFromCache(SWIFT_StaffGroupLink::TYPE_WORKFLOW, $_ticketWorkflowID);

            if ($_ticketWorkflowContainer['staffvisibilitycustom'] == '1' && !in_array($_SWIFT->Staff->GetProperty('staffgroupid'), $_staffGroupIDList)) {
                continue;
            }

            $_workflowHTML .= '<div class="ticketworkflowitem" onclick="javascript: loadViewportData(\'' . SWIFT::Get('basename') . '/Tickets/Ticket/ExecuteWorkflow/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketWorkflowID . '/' .  SWIFT::Get('ticketurlsuffix') . '\');">' . StripName(htmlspecialchars($_ticketWorkflowContainer['title']), 22) . '</div>';
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('workflowbox'), $_workflowHTML);

        return true;
    }

    /**
     * Render the Participants Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @param array $_participantHTMLContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderParticipantBox(SWIFT_Ticket $_SWIFT_TicketObject, $_participantHTMLContainer) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_informationHTML = '';

        if (count($_participantHTMLContainer) <= 2) {
            return false;
        }

        foreach ($_participantHTMLContainer as $_participant) {
            $_informationHTML .= '<div class="ticketinfoitemtext">' .
                '<div class="ticketinfoitemtitle">' . mb_strtoupper($_participant[0]) . '</div><div class="ticketinfoitemcontent">' . StripName(htmlspecialchars($_participant[1]), 20) . '</div></div>';
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('participantbox'), $_informationHTML);

        return true;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @param SWIFT_User $_SWIFT_UserObject (OPTIONAL) The SWIFT_User Object
     * @param SWIFT_UserOrganization $_SWIFT_UserOrganizationObject (OPTIONAL) The SWIFT_UserOrganization Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_UserObject, $_SWIFT_UserOrganizationObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_informationHTML = '';

        $_userGroupCache = $this->Cache->Get('usergroupcache');

        $_informationHTML .= '<div class="ticketinfoitem"><div class="ticketinfoitemtitle">' . $this->Language->Get('tinfobticketid') . '</div><div class="ticketinfoitemlink"><a viewport="1" href="' . SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $_SWIFT_TicketObject->GetTicketID() . '/' .  SWIFT::Get('ticketurlsuffix') . '" >' . $_SWIFT_TicketObject->GetTicketDisplayID() . '</a></div></div>';

        if ($_SWIFT->Staff->GetPermission('staff_canviewusers') != '0') {
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_ticketUserURL = SWIFT::Get('basename') . '/Base/User/Edit/' . $_SWIFT_UserObject->GetUserID();
                $_informationHTML .= '<div class="ticketinfoitem">' .
                    '<div class="ticketinfoitemtitle">' . $this->Language->Get('tinfobuser') . '</div><div class="ticketinfoitemcontainer"><span class="ticketinfoitemlink"><a href="' . $_ticketUserURL . '" viewport="1">' . StripName(text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname')), 20) . '</a></span></div></div>';
            }

            if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                $_ticketUserOrganizationURL = SWIFT::Get('basename') . '/Base/UserOrganization/Edit/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID();

                $_informationHTML .= '<div class="ticketinfoitem">' .
                    '<div class="ticketinfoitemtitle">' . $this->Language->Get('tinfobuserorganization') . '</div><div class="ticketinfoitemcontainer"><span class="ticketinfoitemlink"><a href="' . $_ticketUserOrganizationURL . '" viewport="1">' . StripName(htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('organizationname')), 20) . '</a></span></div></div>';
            }

            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded() && isset($_userGroupCache[$_SWIFT_UserObject->GetProperty('usergroupid')])) {
                $_informationHTML .= '<div class="ticketinfoitemtext">' .
                    '<div class="ticketinfoitemtitle">' . $this->Language->Get('tinfobusergroup') . '</div><div class="ticketinfoitemcontent">' . StripName(htmlspecialchars($_userGroupCache[$_SWIFT_UserObject->GetProperty('usergroupid')]['title']), 20) . '</div></div>';
            }
        }

        // Begin Hook: staff_ticket_infobox
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_infobox')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }
}
