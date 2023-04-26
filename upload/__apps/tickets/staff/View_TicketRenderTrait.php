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

use Base\Library\HTML\SWIFT_HTML;
use SWIFT;
use SWIFT_App;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use Tickets\Models\Escalation\SWIFT_EscalationPath;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use SWIFT_Loader;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use Base\Models\Rating\SWIFT_Rating;
use Base\Library\Rating\SWIFT_RatingRenderer;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Lock\SWIFT_TicketLock;
use Tickets\Models\Merge\SWIFT_TicketMergeLog;
use Tickets\Models\Ticket\SWIFT_TicketForward;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recurrence\SWIFT_TicketRecurrence;
use Tickets\Models\Watcher\SWIFT_TicketWatcher;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserProfileImage;

trait View_TicketRenderTrait {
    /**
     * Render the Ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer (Only for EDIT Mode)
     * @param mixed $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @param array $_variableContainer
     * @param bool $_activeTab
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderTicket(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_UserObject, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID,
        $_ticketLimitOffset, $_variableContainer, $_activeTab = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // these will be overwritten by extract
        $_ticketPostLimitCount = $_ticketPostCount = $_ticketPostOffset = $_userImageUserIDList  = $_staffImageUserIDList = $_userImageUserIDList = false;
        $_ticketPostContainer = [];
        extract($_variableContainer, EXTR_OVERWRITE);

        $_isGeneralTabSelected = true;
        $_isReplyTabSelected = $_isForwardTabSelected = $_isFollowUpTabSelected = $_isBillingTabSelected = $_isReleaseTabSelected = false;

        if (empty($_activeTab)) {

        } else if ($_activeTab == 'reply') {
            $_isGeneralTabSelected = false;
            $_isReplyTabSelected = true;
        } else if ($_activeTab == 'forward') {
            $_isGeneralTabSelected = false;
            $_isForwardTabSelected = true;
        } else if ($_activeTab == 'followup') {
            $_isGeneralTabSelected = false;
            $_isFollowUpTabSelected = true;
        } else if ($_activeTab == 'billing') {
            $_isGeneralTabSelected = false;
            $_isBillingTabSelected = true;
        } else if ($_activeTab == 'release') {
            $_isGeneralTabSelected = false;
            $_isReleaseTabSelected = true;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_departmentMap = SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();

        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_ticketLinkTypeCache = $this->Cache->Get('ticketlinktypecache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_emailQueueCache = $this->Cache->Get('queuecache');

        /**
         * ---------------------------------------------
         * BEGIN PROPERTIES
         * ---------------------------------------------
         */

        $_ticketURLSuffix = SWIFT::Get('ticketurlsuffix');
        $_ticketURL = SWIFT::Get('basename') . '/Tickets/Ticket/View/' . (int) ($_SWIFT_TicketObject->GetTicketID()) . '/' . $_ticketURLSuffix;

        // Ratings
        $_ratingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_TICKETPOST), $_SWIFT->Staff, false, $_SWIFT_TicketObject->GetProperty('departmentid'));
        $_ratingIDList = array_keys($_ratingContainer);

        $_ticketReplyContents = '';
        $_userProfileImageObjectContainer = $_staffProfileImageObjectContainer = array();

        $_ticketStatusTitle = $_ticketPriorityTitle = $_ticketTypeTitle = $_ticketOwnerTitle = '';

        $_SWIFT_TicketLockObject = SWIFT_TicketLock::RetrieveOnTicket($_SWIFT_TicketObject);

        $_ticketDepartmentContainer = false;
        $_departmentTitle = $this->Language->Get('na');
        if ($_SWIFT_TicketObject->GetProperty('departmentid') == '0') {
            $_departmentTitle = $this->Language->Get('trash');
        } else if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
            $_departmentTitle = $_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title'];
            $_ticketDepartmentContainer = $_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')];
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
            /*
             * BUG FIX - Mansi Wason
             *
             * SWIFT-1139 Name of a deleted staff member still appears in assigned tickets in the Manage Ticket List.
             *
             */
            $_ticketOwnerTitle = $this->Language->Get('na');
        }


        $_titleBackgroundColor = '#626262';
        if (!empty($_ticketStatusContainer)) {
            $_titleBackgroundColor = $_ticketStatusContainer['statusbgcolor'];
        }

        $_priorityBackgroundColor = false;
        if (!empty($_ticketPriorityContainer) && isset($_ticketPriorityContainer['bgcolorcode']) && !empty($_ticketPriorityContainer['bgcolorcode'])) {
            $_priorityBackgroundColor = $_ticketPriorityContainer['bgcolorcode'];
        }

        $_ticketWatchContainer = SWIFT_TicketWatcher::RetrieveOnTicket($_SWIFT_TicketObject);

        $_ticketTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID());
        $this->UserInterface->Start(get_short_class($this),'/Tickets/Ticket/GeneralSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterface::MODE_EDIT, false, true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

        /**
         * ---------------------------------------------
         * CREATE TABS
         * ---------------------------------------------
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_ticket.png', 'general', $_isGeneralTabSelected, false, 0);

        /*
         * BUG FIX - Madhur Tandon
         *
         * SWIFT-3307: Dialog boxes need resizing with respect to new UI changes
         *
         * Comments: Increase the height for the UICreateWindow
         */
        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertticketnote') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-sticky-note-o', "UICreateWindow('" . SWIFT::Get('basename') . '/Tickets/Ticket/AddNote/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . "', 'addnote', '". $_SWIFT->Language->Get('addnote') ."', '". $_SWIFT->Language->Get('loadingwindow') ."', 650, 480, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') != '0' && $_SWIFT_TicketObject->GetProperty('departmentid') != '0') {
            if ($_SWIFT_TicketObject->GetProperty('ownerstaffid') == $_SWIFT->Staff->GetStaffID()) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('surrender'), 'fa-life-ring', '/Tickets/Ticket/Surrender/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            } else {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('take'), 'fa-hand-paper-o', '/Tickets/Ticket/Take/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('dispatch'), 'fa-hand-o-right', "UICreateWindow('" . SWIFT::Get('basename') . '/Tickets/Ticket/Dispatch/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . "', 'dispatch', '". $_SWIFT->Language->Get('dispatchticket') ."', '". $_SWIFT->Language->Get('loadingwindow') ."', 650, 550, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('print'), 'fa-print', 'PrintTicket(\'' . (int) ($_SWIFT_TicketObject->GetTicketID()) . '\', false);', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            if (count($_SWIFT_TicketObject->RetrieveNotes()) > 0) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('print_with_notes'), 'fa-print', 'PrintTicket(\'' . (int) ($_SWIFT_TicketObject->GetTicketID()) . '\', true);', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'btnPrintNotes', '', false);
            }

            if ($_SWIFT->Staff->GetPermission('staff_tcansplitticket') != '0') {
                // Split button only if more than 1 posts
                if ($_SWIFT_TicketObject->GetTicketPostCount() > 1) {
                    $this->UserInterface->Toolbar->AddButton($_SWIFT->Language->Get('split'), 'fa-chain-broken', "UICreateWindow('" . SWIFT::Get('basename') . "/Tickets/Ticket/SplitTicket/" . $_SWIFT_TicketObject->GetTicketID() . "', 'dosplit', '" . $_SWIFT->Language->Get('splitticket') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 650, 490, true, this);\"", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
                }
            }

            if ($_SWIFT->Staff->GetPermission('staff_tcanduplicateticket') != '0') {
                $this->UserInterface->Toolbar->AddButton($_SWIFT->Language->Get('duplicate'), 'fa-files-o', "UICreateWindow('" . SWIFT::Get('basename') . "/Tickets/Ticket/DuplicateTicket/" . $_SWIFT_TicketObject->GetTicketID() . "', 'dosplit', '" . $_SWIFT->Language->Get('duplicateticket') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 650, 455, true, this);\"", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            }

            // Already watching?
            if (isset($_ticketWatchContainer[$_SWIFT->Staff->GetStaffID()])) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('stopwatching'), 'fa-eye-slash', '/Tickets/Ticket/UnWatch/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            } else {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('watch'), 'fa-eye', '/Tickets/Ticket/Watch/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            }
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('flag') . ' <img src="'. SWIFT::Get('themepath') .'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-flag-o', 'UIDropDown(\'ticketflagmenu\', event, \'ticketFlag\', \'tabtoolbartable\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'ticketFlag', '', '', false);

        $this->UserInterface->Toolbar->AddButton('');
        if ($_SWIFT->Staff->GetPermission('staff_tcanmarkasspam') != '0' && $_SWIFT_TicketObject->GetProperty('departmentid') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('spam'), 'fa-warning', '/Tickets/Manage/Spam/' .
                $_SWIFT_TicketObject->GetTicketID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        }

        /**
         * BUG Fix: Parminder Singh
         *
         * SWIFT-1562: Issue while moving a ticket to Trash
         *
         * Comments: Sending ticket url suffix with link
         */
        if ($_SWIFT->Staff->GetPermission('staff_tcantrashticket') != '0' && $_SWIFT_TicketObject->GetProperty('departmentid') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('trash'), 'fa-trash', '/Tickets/Manage/Trash/' .
                $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        } else if ($_SWIFT->Staff->GetPermission('staff_tcantrashticket') != '0' && $_SWIFT_TicketObject->GetProperty('departmentid') == '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('putback'), 'fa-rotate-left', '/Tickets/Manage/PutBack/' .
                $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        }

        // Begin Hook: staff_ticket_generaltoolbar
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_generaltoolbar')) ? eval($_hookCode) : false;
        // End Hook

        //$this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
        */
        if (!empty($_ticketDepartmentContainer['parentdepartmentid']) && !empty($_departmentCache[$_ticketDepartmentContainer['parentdepartmentid']])) {
            $_parentDepartmentContainer = $_departmentCache[$_ticketDepartmentContainer['parentdepartmentid']];
            $_generalDepartmentTitle = '<a class="tickettitlelink" href="' . SWIFT::Get('basename') . '/Tickets/Manage/Filter/' . $_parentDepartmentContainer['departmentid'] . '/-1/-1/' . '" viewport="1">' . text_to_html_entities($_parentDepartmentContainer['title']) . '</a>' .
                ' &raquo; <a class="tickettitlelink" href="' . SWIFT::Get('basename') . '/Tickets/Manage/Filter/' . $_ticketDepartmentContainer['departmentid'] . '/-1/-1/' . '" viewport="1">' . text_to_html_entities($_departmentTitle) . '</a>';
        } else {
            $_generalDepartmentTitle = '<a class="tickettitlelink" href="' . SWIFT::Get('basename') . '/Tickets/Manage/Filter/' . $_ticketDepartmentContainer['departmentid'] . '/-1/-1/' . '" viewport="1">' . text_to_html_entities($_departmentTitle) . '</a>';
        }

        $_renderHTML = '<tr><td><div class="ticketgeneralcontainer">';

        // Begin Top Bar
        $_renderHTML .= '<div class="ticketgeneraltitlecontainer">';
        // $_renderHTML .= '<div class="ticketgeneraldepartment">' . $_generalDepartmentTitle . '</div>';

        //Any HTML in subject will be rendered as plain text.
        $_renderHTML .= '<div class="ticketgeneraltitle">' . $this->Input->SanitizeForXSS($this->Emoji->decode($_SWIFT_TicketObject->GetProperty('subject')), false, true) . '</div>';

        // Process the links
        $_linkContainer = $_SWIFT_TicketObject->GetLinks();
        foreach ($_linkContainer as $_ticketLinkTypeID => $_ticketObjectContainer) {
            if (!isset($_ticketLinkTypeCache[$_ticketLinkTypeID])) {
                continue;
            }

            $_ticketLinkTypeContainer = $_ticketLinkTypeCache[$_ticketLinkTypeID];
            $_renderHTML .= '<div class="ticketgenerallink">' . htmlspecialchars($_ticketLinkTypeContainer['linktypetitle']) . '</div>';

            foreach ($_ticketObjectContainer as $_linkTicketID => $_LinkedTicketObject) {
                if (!$_LinkedTicketObject instanceof SWIFT_Ticket || !$_LinkedTicketObject->GetIsClassLoaded()) {
                    continue;
                }

                $_linkTitlePrefix = '';
                $_linkDepartmentTitle = $_linkTicketStatusTitle = $this->Language->Get('na');

                if (!$_LinkedTicketObject->CanAccess($_SWIFT->Staff)) {
                    $_linkTitlePrefix = '<i class="fa fa-lock" aria-hidden="true"></i>';
                }

                if ($_LinkedTicketObject->GetProperty('departmentid') == '0') {
                    $_linkDepartmentTitle = $this->Language->Get('trash');
                } else if (isset($_departmentCache[$_LinkedTicketObject->GetProperty('departmentid')])) {
                    $_linkDepartmentTitle = text_to_html_entities($_departmentCache[$_LinkedTicketObject->GetProperty('departmentid')]['title']);
                }

                if (isset($_ticketStatusCache[$_LinkedTicketObject->GetProperty('ticketstatusid')])) {
                    $_linkTicketStatusTitle = htmlspecialchars($_ticketStatusCache[$_LinkedTicketObject->GetProperty('ticketstatusid')]['title']);
                }

                $_renderHTML .= '<div class="ticketgenerallinkticket">' . $_linkTitlePrefix

                    . '<b><a href="' . SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $_LinkedTicketObject->GetTicketDisplayID() . '" target="_blank">#' . $_LinkedTicketObject->GetTicketDisplayID() . '</a>: </b>'
                    . $_LinkedTicketObject->GetProperty('subject') . ' (' . sprintf($this->Language->Get('ticketlinkinfo'), $_linkDepartmentTitle, $_linkTicketStatusTitle) . ')'
                    . '<span style="margin-left:10px;"><a href="' . SWIFT::Get('basename') . '/Tickets/Ticket/Unlink/' . $_SWIFT_TicketObject->GetProperty('ticketid') . '/' . $_LinkedTicketObject->GetTicketDisplayID() . '/' . $_ticketLinkTypeID . '" onclick="return confirm(&#39;Are you sure you wish to continue?&#39;)">' . $this->Language->Get('ticketunlink') . '</a></span>'
                    . '</div>';
            }
        }
        $_renderHTML .= '</div>';

        $_propertySeperatorHTML = '&nbsp;&nbsp;&nbsp;&nbsp;';


        /**
         * ---------------------------------------------
         * BEGIN ESCALATION PATH RENDERING
         * ---------------------------------------------
         */
        $_escalationPathDividerHTML = '<div class="ticketescalationpathpropertiesdivider"><img src="' . SWIFT::Get('themepathimages') . 'ticketpropertiesdivider2.png" align="middle" border="0" /></div>';

        if ($_SWIFT_TicketObject->GetProperty('isescalated') == '1') {
            $_escalationPathContainer = SWIFT_EscalationPath::RetrieveOnTicket($_SWIFT_TicketObject->GetTicketID());

            $_escalationPathCount = count($_escalationPathContainer);
            $_index = 1;

            $_escalationHistoryHTML = '';
            if ($_escalationPathCount > 1) {
                $_escalationHistoryHTML = '<div>' . sprintf($this->Language->Get('tescalationhistory'), $_escalationPathCount-1) . '</div>';
            }

            foreach ($_escalationPathContainer as $_escalationPath) {
                if ($_index != $_escalationPathCount) {
                    $_renderHTML .= '<div style="display: none;" class="escalationpathhistory">';
                }

                $_renderHTML .= '<div class="ticketbillinginfocontainer">';
                if ($_index == $_escalationPathCount) {
                    $_renderHTML .= '<span style="float: right;"><a href="javascript: void(0);" onclick="javascript: ShowEscalationPathHistory();" class="menulink">' . $_escalationHistoryHTML . '</a></span>';
                }

                $_renderHTML .= '<i class="fa fa-arrow-circle-up" style="margin-right:2px;margin-left:-2px;" aria-hidden="true"></i>';
                $_renderHTML .= $this->Language->Get('tepdate') . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_escalationPath['dateline']) . $_propertySeperatorHTML;
                $_renderHTML .= $this->Language->Get('tepslaplan') . htmlspecialchars($_escalationPath['slaplantitle']) . $_propertySeperatorHTML;
                $_renderHTML .= $this->Language->Get('tepescalationrule') . htmlspecialchars($_escalationPath['escalationruletitle']) . $_propertySeperatorHTML;
                $_renderHTML .= '</div>';


                $_escalationPathTitleBackgroundColor = '#626262';
                if (isset($_ticketStatusCache[$_escalationPath['ticketstatusid']])) {
                    $_escalationPathTitleBackgroundColor = $_ticketStatusCache[$_escalationPath['ticketstatusid']]['statusbgcolor'];
                }

                $_renderHTML .= '<div class="ticketescalationpathproperties" style="background-color: ' . htmlspecialchars($_escalationPathTitleBackgroundColor) . ';">';

                // Departments
                $_renderHTML .= '<div class="ticketescalationpathpropertiesobject"><div class="ticketescalationpathpropertiestitle">' . $this->Language->Get('proptitledepartment') . '</div><div class="ticketescalationpathpropertiescontent">' . StripName(text_to_html_entities($_escalationPath['departmenttitle']), 16) . '</div></div>';
                $_renderHTML .= $_escalationPathDividerHTML;

                // Owner
                $_renderHTML .= '<div class="ticketescalationpathpropertiesobject"><div class="ticketescalationpathpropertiestitle">' . $this->Language->Get('proptitleowner') . '</div><div class="ticketescalationpathpropertiescontent">' . StripName(htmlspecialchars($_escalationPath['ownerstaffname']), 16) . '</div></div>';
                $_renderHTML .= $_escalationPathDividerHTML;

                // Type
                $_renderHTML .= '<div class="ticketescalationpathpropertiesobject"><div class="ticketescalationpathpropertiestitle">' . $this->Language->Get('proptitletype') . '</div><div class="ticketescalationpathpropertiescontent">' . StripName(htmlspecialchars($_escalationPath['tickettypetitle']), 16) . '</div></div>';
                $_renderHTML .= $_escalationPathDividerHTML;

                // Status
                $_renderHTML .= '<div class="ticketescalationpathpropertiesobject"><div class="ticketescalationpathpropertiestitle">' . $this->Language->Get('proptitlestatus') . '</div><div class="ticketescalationpathpropertiescontent">' . StripName(htmlspecialchars($_escalationPath['ticketstatustitle']), 16) . '</div></div>';
                $_renderHTML .= $_escalationPathDividerHTML;

                // Priority
                $_escalationPathPriorityBackgroundColor = false;
                if (isset($_ticketPriorityCache[$_escalationPath['priorityid']])) {
                    $_escalationPathPriorityBackgroundColor = $_ticketPriorityCache[$_escalationPath['priorityid']]['bgcolorcode'];
                }
                $_renderHTML .= '<div class="ticketescalationpathpropertiesobject"' . IIF(!empty($_escalationPathPriorityBackgroundColor), ' style="background-color: ' . htmlspecialchars($_escalationPathPriorityBackgroundColor) . ';"') . '><div class="ticketescalationpathpropertiestitle">' . $this->Language->Get('proptitlepriority') . '</div><div class="ticketescalationpathpropertiescontent">' . StripName(htmlspecialchars($_escalationPath['prioritytitle']), 16) . '</div></div>';
                $_renderHTML .= $_escalationPathDividerHTML;

                $_renderHTML .= '</div>';

                $_renderHTML .= '<div class="ticketescalationpatharrow"></div>';

                if ($_index != $_escalationPathCount) {
                    $_renderHTML .= '</div>';
                }

                $_index++;
            }
        }

        /**
         * ---------------------------------------------
         * END ESCALATION PATH RENDERING
         * ---------------------------------------------
         */

        // Calculate background color for the properties band
        $_infoCSSStyle = '';
        if ($_SWIFT_TicketObject->GetProperty('duetime') != '0') {
            if ($_SWIFT_TicketObject->GetProperty('duetime') < DATENOW) {
                $_infoCSSStyle = 'padding-left:5px;background-color: ' . htmlspecialchars($_SWIFT->Settings->Get('t_overduecolor')) . ' !important;';
            }
        }

        if ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0') {
            if ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') < DATENOW) {
                $_infoCSSStyle = 'padding-left:5px;background-color: ' . htmlspecialchars($_SWIFT->Settings->Get('t_overduecolor')) . ' !important;';
            }
        }

        // Render the properties band
        $_renderHTML .= '<div class="ticketgeneralinfocontainer" style="' . $_infoCSSStyle . '">';
//        $_renderHTML .= $this->Language->Get('tinfoticketid') . $_SWIFT_TicketObject->GetTicketDisplayID() . $_propertySeperatorHTML;

        if ($_SWIFT_TicketObject->GetProperty('duetime') != '0') {
            if ($_SWIFT_TicketObject->GetProperty('duetime') < DATENOW) {
                $_dueTimeline = '<font color="red"><b>' . $this->Language->Get('overdue') . '</b></font>';
                $_fieldContainer[':'] = 'background-color: ' . htmlspecialchars($_SWIFT->Settings->Get('t_overduecolor')) . ';';
            } else {
                $_dueTimeline = SWIFT_Date::ColorTime(($_SWIFT_TicketObject->GetProperty('duetime')-DATENOW), true);
            }
            $_renderHTML .= '<span id="labeldue">' . $this->Language->Get('tinfodue') . $_dueTimeline . '</span>' . $_propertySeperatorHTML;
        }

        if ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0') {
            if ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') < DATENOW) {
                $_dueTimeline = '<font color="red"><b>' . $this->Language->Get('overdue') . '</b></font>';
            } else {
                $_dueTimeline = SWIFT_Date::ColorTime(($_SWIFT_TicketObject->GetProperty('resolutionduedateline')-DATENOW), true);
            }
            $_renderHTML .= '<span id="labelresolutiondue">' . $this->Language->Get('tinforesolutiondue') . $_dueTimeline . '</span>' . $_propertySeperatorHTML;
        }

        /*
         * BUG FIX - Abhishek Mittal
         *
         * SWIFT-3913 SLA plan selected for a User Organization is overriding the SLA plan selected for a User account.
         * SWIFT-2696 Not able to change the SLA plan on the ticket in Staff CP under Edit tab, if there is a SLA already specified in respective user account.
         */
        if ($_SWIFT_TicketObject->Get('ticketslaplanid') != '0' && isset($_slaPlanCache[$_SWIFT_TicketObject->Get('ticketslaplanid')])) {
            $_renderHTML .= $this->Language->Get('tinfosla') . htmlspecialchars($_slaPlanCache[$_SWIFT_TicketObject->Get('ticketslaplanid')]['title']) . $_propertySeperatorHTML;
        } else if ($_SWIFT_TicketObject->Get('slaplanid') != '0' && isset($_slaPlanCache[$_SWIFT_TicketObject->Get('slaplanid')])) {
            $_renderHTML .= $this->Language->Get('tinfosla') . htmlspecialchars($_slaPlanCache[$_SWIFT_TicketObject->Get('slaplanid')]['title']) . $_propertySeperatorHTML;
        }

        $_renderHTML .= $this->Language->Get('tinfocreated') . '<span id="createdat">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->Get('dateline')) . '</span>' . $_propertySeperatorHTML;
        $_renderHTML .= $this->Language->Get('tinfoupdated') . '<span id="updatedat">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->Get('lastactivity')). '</span>' . $_propertySeperatorHTML;

        $_renderHTML .= '</div>';

        // Ticket Lock Processing
        $_lockThreshold = DATENOW -  $_SWIFT->Settings->Get('t_locktimeout');
        if ($_SWIFT_TicketLockObject instanceof SWIFT_TicketLock && $_SWIFT_TicketLockObject->GetIsClassLoaded() &&
            $_SWIFT_TicketLockObject->GetProperty('dateline') > $_lockThreshold &&
            $_SWIFT_TicketLockObject->GetProperty('staffid') != $_SWIFT->Staff->GetStaffID() &&
            isset($_staffCache[$_SWIFT_TicketLockObject->GetProperty('staffid')])) {
            $_renderHTML .= '<div class="ticketlockinfocontainer">';
            $_renderHTML .= '<i class="fa fa-lock" aria-hidden="true"></i> ' . sprintf($this->Language->Get('tlockinfo'), text_to_html_entities($_staffCache[$_SWIFT_TicketLockObject->GetProperty('staffid')]['fullname']), SWIFT_Date::ColorTime(DATENOW-$_SWIFT_TicketLockObject->GetProperty('dateline')));
            $_renderHTML .= '</div>';
        } else {
            // Create the ticket lock
            SWIFT_TicketLock::Create($_SWIFT_TicketObject, $_SWIFT->Staff);
        }

        // Billing Processing
        if ($_SWIFT_TicketObject->GetProperty('hasbilling') == '1' && $_SWIFT->Staff->GetPermission('staff_tcanviewbilling') != '0') {
            $_totalTimeSpent = $_SWIFT_TicketObject->GetProperty('timeworked');
            $_totalTimeBillable = $_SWIFT_TicketObject->GetProperty('timebilled');
            $_renderHTML .= '<div class="ticketbillinginfocontainer">';
            $_renderHTML .= '<i class="fa fa-lock" aria-hidden="true"></i> ' .
                '<b>' . $this->Language->Get('billtotalworked') . '</b> ' . SWIFT_Date::ColorTime($_totalTimeSpent, false, true) .
                '&nbsp;&nbsp;&nbsp;&nbsp;' .
                '<b>' . $this->Language->Get('billtotalbillable') . '</b> ' . SWIFT_Date::ColorTime($_totalTimeBillable, false, true);
            $_renderHTML .= '</div>';
        }

        $_renderHTML .= '</div></td></tr>';
        $_GeneralTabObject->RowHTML($_renderHTML);

        // Begin Properties
        $_dividerHTML = '<div class="ticketgeneralpropertiesdivider"><img src="' . SWIFT::Get('themepathimages') . 'ticketpropertiesdivider.png" align="middle" border="0" /></div>';
        $_renderHTML = '<tr><td><div class="ticketgeneralcontainer">';
        $_renderHTML .= '<div class="ticketgeneralproperties" id="generalticketproperties" style="background-color: ' . htmlspecialchars($_titleBackgroundColor) . ';">';

        // Departments
        $_departmentSelectHTML = '<div class="ticketgeneralpropertiesselect"><select id="general_departmentid" name="gendepartmentid" style="width: 160px;" class="swiftselect" onchange="javascript: UpdateTicketStatusDiv(this, \'genticketstatusid\', false, false, \'generalticketproperties\'); UpdateTicketTypeDiv(this, \'gentickettypeid\', false, false); UpdateTicketOwnerDiv(this, \'genownerstaffid\', false, false);">';
        $_departmentSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_DEPARTMENT);
        $_departmentSelectHTML .= '</select></div>';

        $_renderHTML .= '<div class="ticketgeneralpropertiesobject"><div class="ticketgeneralpropertiestitle">' . $this->Language->Get('proptitledepartment') . '</div><div class="ticketgeneralpropertiescontent">' . StripName(text_to_html_entities($_departmentTitle), 16) . '</div>' . $_departmentSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Owner
        $_ownerSelectHTML = '<div class="ticketgeneralpropertiesselect"><div id="genownerstaffid_container"><select id="selectgenownerstaffid" style="width: 160px;" name="genownerstaffid" class="swiftselect">';
        $_ownerSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_OWNER);
        $_ownerSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketgeneralpropertiesobject"><div class="ticketgeneralpropertiestitle">' . $this->Language->Get('proptitleowner') . '</div><div class="ticketgeneralpropertiescontent">' . StripName(htmlspecialchars($_ticketOwnerTitle), 16) . '</div>' . $_ownerSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Type
        $_ticketTypeSelectHTML = '<div class="ticketgeneralpropertiesselect"><div id="gentickettypeid_container"><select id="selectgentickettypeid" style="width: 160px;" name="gentickettypeid" class="swiftselect">';
        $_ticketTypeSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_TYPE);
        $_ticketTypeSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketgeneralpropertiesobject"><div class="ticketgeneralpropertiestitle">' . $this->Language->Get('proptitletype') . '</div><div class="ticketgeneralpropertiescontent">' . StripName(htmlspecialchars($_ticketTypeTitle), 16) . '</div>' . $_ticketTypeSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Status
        $_ticketStatusSelectHTML = '<div class="ticketgeneralpropertiesselect"><div id="genticketstatusid_container"><select id="selectgenticketstatusid" style="width: 160px;" name="genticketstatusid" onchange="javascript: ResetStatusParentColor(this, \'generalticketproperties\');" class="swiftselect">';
        $_ticketStatusSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_STATUS, $_ticketStatusContainer);
        $_ticketStatusSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketgeneralpropertiesobject"><div class="ticketgeneralpropertiestitle">' . $this->Language->Get('proptitlestatus') . '</div><div class="ticketgeneralpropertiescontent">' . StripName(htmlspecialchars($_ticketStatusTitle), 16) . '</div>' . $_ticketStatusSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Priority
        $_ticketPrioritySelectHTML = '<div class="ticketgeneralpropertiesselect"><select id="general_ticketpriorityid" name="genticketpriorityid" style="width: 160px;" class="swiftselect" onchange="javascript: ResetPriorityParentColor(this, \'generalpriorityproperties\');">';
        $_ticketPrioritySelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_PRIORITY);
        $_ticketPrioritySelectHTML .= '</select></div>';

        $_renderHTML .= '<div class="ticketgeneralpropertiesobject" id="generalpriorityproperties" ' . IIF(!empty($_priorityBackgroundColor), ' style="background-color: ' . htmlspecialchars($_priorityBackgroundColor) . ';"') . '><div class="ticketgeneralpropertiestitle">' . $this->Language->Get('proptitlepriority') . '</div><div class="ticketgeneralpropertiescontent">' . StripName(htmlspecialchars($_ticketPriorityTitle), 12) . '</div>' . $_ticketPrioritySelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Ticket Flag
        $_flagHTML = $_flagStyle = '';
        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();
        $_flagList = $_SWIFT_TicketFlagObject->GetFlagContainer();
        if (isset($_flagList[$_SWIFT_TicketObject->GetProperty('flagtype')])) {
            $_flagStyle = 'color: ' . $_flagList[$_SWIFT_TicketObject->GetProperty('flagtype')][1] . ' !important;';
            $_flagHTML = '<a href="javascript: void(0);" onclick="javascript: ToggleFlag(\'' .
                $_SWIFT_TicketObject->GetTicketID() . '\', \'' . $_flagList[SWIFT_TicketFlag::FLAG_RED][1] . '\', \'' .
                $_flagList[SWIFT_TicketFlag::FLAG_RED][2] . '\', \'icon_flagblankwhite.gif\');" style="' . $_flagStyle . '"><i class="fa fa-flag" id="ticketflagimg_' . $_SWIFT_TicketObject->GetTicketID() .
                '" src="' . SWIFT::Get('themepathimages') . $_flagList[$_SWIFT_TicketObject->GetProperty('flagtype')][2] .
                '" aria-hidden="true"></i></a>';
            $_renderHTML .= '<div class="ticketgeneralpropertiesflag" id="ticketgeneralpropertiesflag_' . $_SWIFT_TicketObject->GetTicketID() . '" style="border-right: 10px solid ' . $_flagList[$_SWIFT_TicketObject->GetProperty('flagtype')][1] .  ';' . $_flagStyle . '" >' . $_flagHTML . '</div>';
        } else {
            $_flagHTML = '<a href="javascript: void(0);" onclick="javascript: ToggleFlag(\'' .
                $_SWIFT_TicketObject->GetTicketID() . '\', \'' . $_flagList[SWIFT_TicketFlag::FLAG_RED][1] . '\', \'' .
                $_flagList[SWIFT_TicketFlag::FLAG_RED][2] . '\', \'icon_flagblankwhite.gif\');"><i class="fa fa-flag-o" style="color:white;"id="ticketflagimg_' . $_SWIFT_TicketObject->GetTicketID() .
                '" src="' . SWIFT::Get('themepathimages') . 'icon_flagblankwhite.gif' .
                '" aria-hidden="true"></i></a>';
            $_renderHTML .= '<div class="ticketgeneralpropertiesflag" id="ticketgeneralpropertiesflag_' . $_SWIFT_TicketObject->GetTicketID() . '" style="' . $_flagStyle . '" >' . $_flagHTML . '</div>';
        }


        $_renderHTML .= '</div>';

        $_renderHTML .= '</div></td></tr>';

        $_GeneralTabObject->RowHTML($_renderHTML);

        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            $_GeneralTabObject->TextMultipleAutoComplete('tags', false, false, '/Base/Tags/QuickSearch', $_ticketTagContainer, 'fa-tags', 'gridrow2', true, 2, false, true);
        }

        $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, SWIFT_UserInterface::MODE_INSERT,
            array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), $_GeneralTabObject,
            $_SWIFT_TicketObject->GetTicketID(), $_SWIFT_TicketObject->GetProperty('departmentid'), true);

        // Check the ticket merge log, if there is any old custom field records
        $_mergeTicketIDList = SWIFT_TicketMergeLog::GetMergedTicketIDFromTicketID($_SWIFT_TicketObject->GetTicketID());
        if (_is_array($_mergeTicketIDList)) {
            foreach ($_mergeTicketIDList as $_mergedTicketID) {
                $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), $_GeneralTabObject, $_mergedTicketID, $_SWIFT_TicketObject->GetProperty('departmentid'), true);
            }
        }

        if ($_SWIFT_TicketObject->GetProperty('userid') != '0') {
            $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USER), $_GeneralTabObject, $_SWIFT_TicketObject->GetProperty('userid'));

            $_SWIFT_UserOrganizationObject = $_SWIFT_TicketObject->GetUserOrganizationObject();
            if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), $_GeneralTabObject, $_SWIFT_UserOrganizationObject->GetUserOrganizationID());
            }
        }

        $_notesHTML = '';
        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_notesHTML = $this->RenderNotes($_SWIFT_TicketObject, $_SWIFT_UserObject);
        } else {
            $_notesHTML = $this->RenderNotes($_SWIFT_TicketObject);
        }

        if (!empty($_notesHTML)) {
            $_GeneralTabObject->RowHTML('<tr class="gridrow3" id="ticketnotescontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3"><div id="ticketnotescontainerdiv">' . $_notesHTML . '</div></td></tr>');
        } else {
            $_GeneralTabObject->RowHTML('<tr class="gridrow3" style="display: none;" id="ticketnotescontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3"><div id="ticketnotescontainerdiv"></div></td></tr>');
        }

        // Begin Hook: staff_ticket_generaltab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_generaltab')) ? eval($_hookCode) : false;
        // End Hook

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN REPLY TAB
         * ###############################################
        */

        if ($_SWIFT->Staff->GetDepartmentPermission($_SWIFT_TicketObject->GetProperty('departmentid'), 'd_t_canreply') != '0' && $_SWIFT->Staff->GetPermission('staff_tcanreply') != '0') {
            $_ReplyTabObject = $this->UserInterface->AddTab($this->Language->Get('tabreply'), 'icon_ticketreply.png', 'reply', $_isReplyTabSelected, false, 0);
            $_ReplyTabObject->EventQueue('show', '$("#replycontents").focus(); StartTicketReplyLockTimer(\'' . $_SWIFT_TicketObject->GetTicketID() . '\');');
            $_ReplyTabObject->LoadToolbar();
            $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('dispatchsend'), 'fa-paper-plane-o', '/Tickets/Ticket/ReplySubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);

            $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('cancel'), 'fa-times-circle', '/Tickets/Ticket/CancelReply/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);

            if ($_SWIFT->Staff->GetPermission('staff_tcansaveasdraft') != '0') {
                $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('dispatchsaveasdraft'), 'fa-floppy-o', '/Tickets/Ticket/SaveAsDraft/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix, SWIFT_UserInterfaceToolbar::LINK_FORM);
            }

            if ($_SWIFT->Staff->GetPermission('staff_tcaninsertticketnote') != '0') {
                $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-file-o', "\$('#replynotes').toggle(function(){ \$('#replyticketnotes').focus(); });", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'replynotes');
            }

            $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('dispatchattachfile'), 'fa-paperclip', "\$('#replyattachments').toggle();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'replyattachments');

            if ($_SWIFT->Staff->GetPermission('staff_tcanfollowup') != '0') {
                $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('followup'), 'fa-bookmark-o', "\$('#replyfollowup').toggle(); LoadFollowUp('replyfollowup', '" . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '/re/' . substr(BuildHash(), 6) . "');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'replyfollowup');
            }

            $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('options'), 'fa-gears', "\$('#replyoptions').toggle();", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', 'replyoptions');
            $_ReplyTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            // Begin Hook: staff_ticket_replytabtoolbar
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_ticket_replytabtoolbar')) ? eval($_hookCode) : false;
            // End Hook

            $this->RenderDispatchTab(self::TAB_REPLY, $_ReplyTabObject, $_SWIFT_TicketObject, $_SWIFT_UserObject, $_ticketWatchContainer);
        }

        /*
         * ###############################################
         * END REPLY TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN FORWARD TAB
         * ###############################################
        */

        if ($_SWIFT->Staff->GetPermission('staff_tcanforward') != '0' && $_SWIFT->Staff->GetDepartmentPermission($_SWIFT_TicketObject->GetProperty('departmentid'), 'd_t_canforward') != '0' && $_SWIFT->Staff->GetPermission('staff_tcanforward') != '0') {
            $_ForwardTabObject = $this->UserInterface->AddTab($this->Language->Get('tabforward'), 'icon_ticketforward.png', 'forward', $_isForwardTabSelected, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/Forward/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '/' . substr(BuildHash(), 6));
            $_ForwardTabObject->EventQueue('show', '$("#forwardcontents").focus();');
        }

        /*
         * ###############################################
         * END FORWARD TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN FOLLOW-UP TAB
         * ###############################################
        */

        if ($_SWIFT->Staff->GetPermission('staff_tcanfollowup') != '0' && $_SWIFT->Staff->GetDepartmentPermission($_SWIFT_TicketObject->GetProperty('departmentid'), 'd_t_canfollowup') != '0') {
            $_followUpCounterHTML = '';
            $_followUpCount = $_SWIFT_TicketObject->GetProperty('followupcount');
            if ($_followUpCount > 0) {
                $_followUpCount = number_format($_followUpCount, 0);
            }
            $_FollowUpTabObject = $this->UserInterface->AddTab($this->Language->Get('tabfollowup'), 'icon_followup.png', 'followup', $_isFollowUpTabSelected, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/FollowUp/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '/0/' . substr(BuildHash(), 6));
            $_FollowUpTabObject->SetTabCounter($_followUpCount);
            $_FollowUpTabObject->EventQueue('show', '$("#fufollowupvalue").focus();');
        }

        /*
         * ###############################################
         * END FOLLOW-UP TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN BILLING TAB
         * ###############################################
        */
        if ($_SWIFT->Staff->GetPermission('staff_tcanviewbilling') != '0' && $_SWIFT->Staff->GetDepartmentPermission($_SWIFT_TicketObject->GetProperty('departmentid'), 'd_t_canbilling') != '0') {
            $_timeTrackCounterHTML = '';
            $_timeTrackCount = 0;
            if ($_SWIFT_TicketObject->GetProperty('hasbilling') == '1') {
                $_timeTrackCount = $_SWIFT_TicketObject->GetTimeTrackCount();
            }
            if ($_timeTrackCount > 0) {
                $_timeTrackCount = number_format($_timeTrackCount, 0);
            }

            $_BillingTabObject = $this->UserInterface->AddTab($this->Language->Get('tabbilling') . $_timeTrackCounterHTML, 'icon_ticketbilling.png', 'billing', $_isBillingTabSelected, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/Billing/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '/' . substr(BuildHash(), 6));
            $_BillingTabObject->SetTabCounter($_timeTrackCount);
            $_BillingTabObject->EventQueue('show', '$("#billingtimeworked").focus();ClearFunctionQueue();');
        }

        /*
         * ###############################################
         * END BILLING TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN RELEASE TAB
         * ###############################################
        */

        if ($_SWIFT->Staff->GetPermission('staff_tcanrelease') != '0') {
            $_ReleaseTabObject = $this->UserInterface->AddTab($this->Language->Get('tabrelease'), 'icon_ticketrelease.png', 'release', $_isReleaseTabSelected, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/Release/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '/' . substr(BuildHash(), 6));
        }

        /*
         * ###############################################
         * END RELEASE TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN CHATS TAB
         * ###############################################
        */

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            SWIFT_Loader::LoadModel('Chat:Chat', APP_LIVECHAT);

            $_chatsCounterHTML = '';

            $_chatsCount = 0;
            if ($_SWIFT_TicketObject->GetProperty('userid') != '0') {
                $_chatsCount = SWIFT_Chat::GetHistoryCount($_SWIFT_TicketObject->GetProperty('userid'), IIF($_SWIFT_TicketObject->GetProperty('replyto') != '', $_SWIFT_TicketObject->GetProperty('replyto'), $_SWIFT_TicketObject->GetProperty('email')));
            }
            if ($_chatsCount > 0) {
                $_chatsCount = number_format($_chatsCount, 0);
            }

            $_historyArguments = 'userid=' . (int) ($_SWIFT_TicketObject->GetProperty('userid')) . '&email=' . urlencode(IIF($_SWIFT_TicketObject->GetProperty('replyto') != '', $_SWIFT_TicketObject->GetProperty('replyto'), $_SWIFT_TicketObject->GetProperty('email')));
            $_ChatsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabchats') . $_chatsCounterHTML, 'icon_chatballoon.png', 'chats', false, false, 0, SWIFT::Get('basename') . '/LiveChat/ChatHistory/History/' . base64_encode($_historyArguments));
            $_ChatsTabObject->SetTabCounter($_chatsCount);
        }

        /*
         * ###############################################
         * END CHATS TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN CALLS TAB
         * ###############################################
        */
        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {

            $_callHistoryCounterHTML = '';
            SWIFT_Loader::LoadModel('Call:Call', APP_LIVECHAT);
            $_callHistoryCount = SWIFT_Call::GetHistoryCountOnUser($_SWIFT_UserObject, $_SWIFT_TicketObject->GetProperty('email'));
            if ($_callHistoryCount > 0) {
                $_callHistoryCount = number_format($_callHistoryCount, 0);
            }

            $_userIDCall = -1;
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_userIDCall = $_SWIFT_UserObject->GetUserID();
            }

            $_callEmailList = '&email[]=' . urlencode($_SWIFT_TicketObject->GetProperty('email'));

            $_CallsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcalls') . $_callHistoryCounterHTML, 'icon_phone.gif', 'calls', false, false, 0, SWIFT::Get('basename') . '/LiveChat/Call/History/' . base64_encode('userid=' .  ($_userIDCall) . $_callEmailList));
            $_CallsTabObject->SetTabCounter($_callHistoryCount);
        }

        /*
         * ###############################################
         * END CALLS TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN HISTORY TAB
         * ###############################################
        */

        $_historyCounterHTML = '';
        $_historyCount = $_SWIFT_TicketObject->GetHistoryCount();
        if ($_historyCount > 0) {
            $_historyCount = number_format($_historyCount, 0);
        }
        $_HistoryTabObject = $this->UserInterface->AddTab($this->Language->Get('tabhistory') . $_historyCounterHTML, 'icon_spacer.gif', 'history', false, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/History/' . $_SWIFT_TicketObject->GetTicketID() . '/' . substr(BuildHash(), 6));
        $_HistoryTabObject->SetTabCounter($_historyCount);
        /*
         * ###############################################
         * END HISTORY TAB
         * ###############################################
        */


        /*
         * ###############################################
         * BEGIN RECURRENCE TAB
         * ###############################################
         */

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_tcanviewrecurrence') != '0') {
            $_TicketRecurrence = SWIFT_TicketRecurrence::RetrieveOnTicket($_SWIFT_TicketObject);

            // Search from the parent ticket ID, if the ticket is created using recurrence
            if (!$_TicketRecurrence instanceof SWIFT_TicketRecurrence && $_SWIFT_TicketObject->Get('recurrencefromticketid') != '0') {
                $_recurrenceParentTicketID = $_SWIFT_TicketObject->Get('recurrencefromticketid');
                $_RecurrenceParentTicket   = SWIFT_Ticket::GetObjectOnID($_recurrenceParentTicketID);

                if ($_RecurrenceParentTicket instanceof SWIFT_Ticket) {
                    $_TicketRecurrence = SWIFT_TicketRecurrence::RetrieveOnTicket($_RecurrenceParentTicket);
                }
            }

            if ($_TicketRecurrence instanceof SWIFT_TicketRecurrence) {
                $_RecurrenceTab = $this->UserInterface->AddTab($this->Language->Get('tabrecurrence'), 'icon_spacer.gif', 'recurrence', false, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/Recurrence/' . $_SWIFT_TicketObject->GetID() . '/' . $_TicketRecurrence->GetTicketRecurrenceID() . '/' . substr(BuildHash(), 6));
            }
        }

        /*
         * ###############################################
         * END RECURRENCE TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN AUDITLOG TAB
         * ###############################################
        */

        if ($_SWIFT->Staff->GetPermission('staff_tcanviewauditlog') != '0') {
            $_AuditLogTabObject = $this->UserInterface->AddTab($this->Language->Get('tabauditlog'), 'icon_spacer.gif', 'auditlog', false, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/AuditLog/' . $_SWIFT_TicketObject->GetTicketID() . '/' . substr(BuildHash(), 6));
        }

        /*
         * ###############################################
         * END AUDITLOG TAB
         * ###############################################
        */

        /*
         * ###############################################
         * BEGIN EDIT TAB
         * ###############################################
        */

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') != '0') {
            $_EditTabObject = $this->UserInterface->AddTab($this->Language->Get('tabedit'), 'icon_spacer.gif', 'edit', false, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/Edit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . substr(BuildHash(), 6));
        }

        /*
         * ###############################################
         * END EDIT TAB
         * ###############################################
        */

        // Begin Hook: staff_tickets_viewtickettab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_tickets_viewtickettab')) ? eval($_hookCode) : false;
        // End Hook

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4994 Notice Undefined variable: _ReplyTabObject (./__apps/tickets/staff/class.View_Ticket.php:1102)
         * SWIFT-4941 Check Custom Tweaks compatibility with SWIFT
         */
        if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0' && $_SWIFT->Staff->GetPermission('staff_tcanreply') != '0' && $_SWIFT->Staff->GetDepartmentPermission($_SWIFT_TicketObject->GetProperty('departmentid'), 'd_t_canreply') != '0' && $_SWIFT->Staff->GetPermission('staff_tcanreply') != '0')
        {
            /**
             * BUG FIX - Nidhi Gupta <nidhi.gupta@kayako.com>
             *
             * SWIFT-4982 WYSIWYG editor fails to load
             */
            echo '<script type="text/javascript" src="' . $_SWIFT->Settings->Get('general_producturl') . '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/tinymce.min.js"/><script>tinyMCE.baseURL = "' . $_SWIFT->Settings->Get('general_producturl') . '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/";</script>';

            if (isset($_ReplyTabObject)) {
                $_ReplyTabObject->EventQueue('show', GetTinyMceCode('textarea#replycontents', 'replycontents'));
            }
        }

        if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0' && $_SWIFT->Staff->GetPermission('staff_tcanforward') != '0' && $_SWIFT->Staff->GetDepartmentPermission($_SWIFT_TicketObject->GetProperty('departmentid'), 'd_t_canforward') != '0' && $_SWIFT->Staff->GetPermission('staff_tcanforward') != '0') {
            $_SWIFT = SWIFT::GetInstance();

            /**
             * BUG FIX - Nidhi Gupta <nidhi.gupta@kayako.com>
             *
             * SWIFT-4982 WYSIWYG editor fails to load
             */
            echo '<script type="text/javascript" src="' . $_SWIFT->Settings->Get('general_producturl') . '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/tinymce.min.js"/><script>tinyMCE.baseURL = "' . $_SWIFT->Settings->Get('general_producturl') . '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/";</script>';

            if (isset($_ForwardTabObject)) {
                $_ForwardTabObject->EventQueue('show', GetTinyMceCode('textarea#forwardcontents', 'forwardcontents'));
            }
        }

        /*
         * ###############################################
         * BEGIN TICKET POST RENDERING
         * ###############################################
        */

        $_ticketPostHTML = '<div class="ticketpostsholder">';

        // Create the pagination
        $_ticketPostPaginationLimit = $_ticketPostLimitCount;
        if ($_ticketPostLimitCount == false) {
            $_ticketPostPaginationLimit = $_ticketPostCount;
        }

        $_paginationHTML = '<div class="ticketpostpaginationtoolbar"><div style="float: right;"><table border="0" cellpadding="0" cellspacing="1"><tr><td class="gridnavpage"><a href="' . SWIFT::Get('basename') . '/Tickets/Ticket/Jump/previous/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '" viewport="1">' . $this->Language->Get('tnavprevticket') . '</a></td><td class="gridnavpage"><a href="' . SWIFT::Get('basename') . '/Tickets/Ticket/Jump/next/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_ticketURLSuffix . '" viewport="1">' . $this->Language->Get('tnavnextticket') . '</a></td></tr></table></div>'.SWIFT_CRLF;
        $_paginationHTML .= '<table border="0" cellpadding="0" cellspacing="1"><tr>' .
            SWIFT_UserInterfaceGrid::RenderPagination('javascript: loadViewportData("' . $_ticketURL . '/', $_ticketPostCount, $_ticketPostPaginationLimit, $_ticketPostOffset, '5', 'pageoftotal', true, false, true, true, true) .'</tr></table>';
        $_paginationHTML .= '</div>';

        $_ticketPostHTML .= $_paginationHTML;

        $_userProfileImageObjectContainer = SWIFT_UserProfileImage::RetrieveOnUserList($_userImageUserIDList);
        $_staffProfileImageObjectContainer = SWIFT_StaffProfileImage::RetrieveOnStaffList($_staffImageUserIDList);

        // Process attachments
        $_ticketAttachmentContainer = array();
        if ($_SWIFT_TicketObject->GetProperty('hasattachments') == '1') {
            $_ticketAttachmentContainer = $_SWIFT_TicketObject->GetAttachmentContainer();
        }

        $_ticketPostIDList = array_keys($_ticketPostContainer);
        $_ratingResultContainer = SWIFT_RatingResult::Retrieve($_ratingIDList, $_ticketPostIDList);

        $_ticketPostUserContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "users WHERE userid IN (" . BuildIN($_userImageUserIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_ticketPostUserContainer[$this->Database->Record['userid']] = $this->Database->Record;
        }

        if (is_array($_ticketPostContainer) && !empty($_ticketPostContainer)) {
            foreach ($_ticketPostContainer as $_ticketPostID => $_SWIFT_TicketPostObject) {
                $_postFooter = $_postTitle = '';

                $_userRecordContainer = array();
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_USER && $_SWIFT_TicketPostObject->GetProperty('userid') != '0' && isset($_ticketPostUserContainer[$_SWIFT_TicketPostObject->GetProperty('userid')])) {
                    $_userRecordContainer = $_ticketPostUserContainer[$_SWIFT_TicketPostObject->GetProperty('userid')];
                }

                $_postTitle = sprintf($this->Language->Get(IIF($_SWIFT_TicketPostObject->GetProperty('issurveycomment') == '1', 'tppostedonsurvey', 'tppostedon')), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketPostObject->GetProperty('dateline')));

                if ($_SWIFT_TicketObject->GetProperty('isphonecall') == '1' && $_SWIFT_TicketObject->GetProperty('firstpostid') == $_SWIFT_TicketPostObject->GetTicketPostID()) {
                    $_postFooter .= sprintf($this->Language->Get('phoneext'), htmlspecialchars($_SWIFT_TicketObject->GetProperty('phoneno'))) . '</br>';
                }

                if ($_SWIFT_TicketPostObject->GetProperty('emailto') != '') {
                    // Sent as email (New Ticket from Staff CP)
                    if ($_ticketPostID == $_SWIFT_TicketObject->GetProperty('firstpostid') && $_SWIFT_TicketObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF) {
                        $_postFooter .= sprintf($this->Language->Get('tpemailto'), htmlspecialchars($_SWIFT_TicketPostObject->GetProperty('emailto'))) . '</br>';

                        // Email Forwarded
                    } else {
                        $_recipients = SWIFT_TicketForward::RetrieveEmailListOnTicketPost($_SWIFT_TicketPostObject);
                        if (empty($_recipients)) {
                            $_recipients = [$_SWIFT_TicketPostObject->GetProperty('emailto')];
                        }
                        $_emails     = array_map('htmlspecialchars', $_recipients);
                        $_postFooter .= sprintf($this->Language->Get('tpemailforwardedto'),
                                implode(', ', $_emails)) . '</br>';
                    }
                }

                $_postFooter .= sprintf($this->Language->Get('tpemail'), htmlspecialchars($_SWIFT_TicketPostObject->GetProperty('email')));

                $_badgeText = $_badgeClass = '';

                if ($_SWIFT_TicketPostObject->GetProperty('dateline') >= $_SWIFT->Staff->GetProperty('lastvisit')) {
                    $_postTitle .= '&nbsp;<span class="postStatusIndicator">NEW</span>';
                }

                $_postFooterRight = '';


                if ($_SWIFT_TicketPostObject->GetProperty('ipaddress') != '') {
                    $_postFooterRight = sprintf($this->Language->Get('tpipaddress'), htmlspecialchars($_SWIFT_TicketPostObject->GetProperty('ipaddress')));
                }

                if ($_SWIFT_TicketPostObject->GetProperty('isthirdparty') == '1' || $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_THIRDPARTY) {
                    $_badgeClass = 'ticketpostbarbadgered';
                    $_badgeText = $this->Language->Get('badgethirdparty');
                } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_CC) {
                    $_badgeClass = 'ticketpostbarbadgered';
                    $_badgeText = $this->Language->Get('badgecc');
                } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_BCC) {
                    $_badgeClass = 'ticketpostbarbadgered';
                    $_badgeText = $this->Language->Get('badgebcc');
                } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_USER) {
                    $_badgeClass = 'ticketpostbarbadgeblue';
                    $_badgeText = $this->Language->Get('badgeuser');
                } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF) {
                    $_badgeClass = 'ticketpostbarbadgered';
                    $_badgeText = $this->Language->Get('badgestaff');
                }

                $_ticketPostHTML .= '<div class="ticketpostcontainer">';

                // DIV ENCLOSURE BEGIN
                $_ticketPostHTML .= '<div class="ticketpostbar">';

                // Begin Avatar Display
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_USER &&
                    isset($_userProfileImageObjectContainer[$_SWIFT_TicketPostObject->GetProperty('userid')])) {
                    $_ticketPostHTML .= '<div class="ticketpostavatar"><div class="tpavatar"><img src="' . SWIFT::Get('basename') .
                        '/Base/User/GetProfileImage/' . (int) ($_SWIFT_TicketPostObject->GetProperty('userid')) . '" align="absmiddle" border="0" width="42px;" /></div></div>';
                } else if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF &&
                    isset($_staffProfileImageObjectContainer[$_SWIFT_TicketPostObject->GetProperty('staffid')])) {
                    $_ticketPostHTML .= '<div class="ticketpostavatar"><div class="tpavatar"><img src="' . SWIFT::Get('basename') .
                        '/Base/StaffProfile/GetProfileImage/' . (int) ($_SWIFT_TicketPostObject->GetProperty('staffid')) . '" align="absmiddle" border="0" width="42px;" /></div></div>';
                } else {
                    $_ticketPostHTML .= '<div class="ticketpostavatar"><div class="tpavatar"><img src="' . SWIFT::Get('themepath') . 'images/icon_defaultavatar.gif' . '" align="absmiddle" border="0" width="42px;" /></div></div>';
                }

                $_ticketPostHTML .= '';
                $_designationText = '';
                if ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF && isset($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]) &&
                    !empty($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]['designation'])) {
                    $_designationText = '&nbsp;<span class="ticketpostbardesignation">(' . htmlspecialchars(StripName($_staffCache[$_SWIFT_TicketPostObject->GetProperty('staffid')]['designation'], 39)) . ')</span>';
                }elseif ($_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_USER && isset($_userRecordContainer['userdesignation']) && $_userRecordContainer['userdesignation'] != '') {
                    $_designationText = '&nbsp;<span class="ticketpostbardesignation">(' . htmlspecialchars(StripName($_userRecordContainer['userdesignation'], 39)) . ')</span>';
                }

                $_ratingHTML = '';
                if ($_SWIFT->Staff->GetPermission('staff_canviewratings') != '0' && $_SWIFT_TicketPostObject->GetProperty('creator') == SWIFT_TicketPost::CREATOR_STAFF) {
                    $_ratingHTML = SWIFT_RatingRenderer::Render(array(SWIFT_Rating::TYPE_TICKETPOST), $_SWIFT_TicketPostObject->GetTicketPostID(), '/Tickets/Ticket/RatingPost/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_SWIFT_TicketPostObject->GetTicketPostID(), $_ratingContainer, $_ratingResultContainer, 'ticketpostinfo');
                }

                // if (!empty($_ratingHTML)) {
                //     $_ticketPostHTML .= '<div class="ticketpostbox">' . $_ratingHTML . '</div>';
                // }

                $_ticketPostHTML .= '</div>';
                // DIV ENCLOSURE END

                // Prepare post content suffix
                $_postContentSuffix = '';
                if ($_SWIFT_TicketPostObject->GetProperty('edited') == 1) {
                    $_editStaffName = $this->Language->Get('na');
                    if (isset($_staffCache[$_SWIFT_TicketPostObject->GetProperty('editedbystaffid')])) {
                        $_editStaffName = text_to_html_entities($_staffCache[$_SWIFT_TicketPostObject->GetProperty('editedbystaffid')]['fullname']);
                    }
                    $_postContentSuffix .= '<div class="ticketpostedited">' . sprintf($this->Language->Get('lastedited'), $_editStaffName, SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketPostObject->GetProperty('editeddateline'))) . '</div>';
                }

                // Process the ticket attachments
                $_attachmentHTML = '';
                if (isset($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetTicketPostID()]) && _is_array($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetTicketPostID()])) {
                    $_attachmentHTML .= '<div class="ticketpostcontentsattachments">';
                    foreach ($_ticketAttachmentContainer[$_SWIFT_TicketPostObject->GetTicketPostID()] as $_attachmentID => $_attachmentContainer) {
                        $_mimeDataContainer = array();
                        try {
                            $_fileExtension = mb_strtolower(mb_substr($_attachmentContainer['filename'], (mb_strrpos($_attachmentContainer['filename'], '.')+1)));

                            $_MIMEListObject = new SWIFT_MIMEList();
                            $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
                        } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                            // Do nothing
                        }

                        if (isset($_mimeDataContainer[1])) {
                            $_attachmentIcon = $_mimeDataContainer[1];
                        } else {
                            $_attachmentIcon = 'icon_file.gif';
                        }

                        $_attachmentHTML .= '<a class="ticketpostcontentsattachmentitem" href="' . SWIFT::Get('basename') . '/Tickets/Ticket/GetAttachment/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_attachmentContainer['attachmentid'] . '" target="_blank"
                        style="background-image: URL(\'' . SWIFT::Get('themepathimages') . $_attachmentIcon . '\');">' . htmlspecialchars($_attachmentContainer['filename']) . ' <span>(' . FormattedSize($_attachmentContainer['filesize']) . ')</span>' . '</a>';
                    }

                    $_attachmentHTML .= '</div>';
                }

                $_ticketPostHTML .= '<div class="ticketpostcontents" >';
                $_ticketPostHTML .= '<div class="ticketpostcontentsbar' . IIF($_SWIFT_TicketPostObject->GetProperty('issurveycomment') == '1', 'green') . IIF($_SWIFT_TicketPostObject->GetProperty('isprivate') == '1', 'gray') . '"><div class="ticketpostbarname">' . StripName(text_to_html_entities($_SWIFT_TicketPostObject->GetProperty('fullname')), 19) . $_designationText .'<span class="' . $_badgeClass . '"><span class="tpbadgetext">' . mb_strtoupper(htmlspecialchars($_badgeText)) . '</span></span></div>';

                $_ticketPostHTML .= '<div class="ticketpostactions">';
                if ($_SWIFT->Staff->GetPermission('staff_tcanupaticketpost') != '0') {
                    $_ticketPostHTML .= '<a href="javascript: void(0);" onclick="javascript: UICreateWindow(\'' . SWIFT::Get('basename') .
                        '/Tickets/Ticket/EditPost/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_SWIFT_TicketPostObject->GetTicketPostID() . '/' . $_ticketURLSuffix .
                        '\', \'editticketpost\', \'' . $_SWIFT->Language->Get('wineditticketpost') . '\', \'' . $_SWIFT->Language->Get('loadingwindow') .
                        '\', 650, 430, true);"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
                }

                if ($_SWIFT->Staff->GetPermission('staff_tcandeleteticketpost') != '0' && $_SWIFT_TicketObject->GetProperty('firstpostid') != $_SWIFT_TicketPostObject->GetTicketPostID()) {
                    $_ticketPostHTML .= '<a href="javascript: void(0);" onclick="javascript: doConfirm(\'' . $this->Language->Get('actionconfirm') . '\', \'' . SWIFT::Get('basename') . '/Tickets/Ticket/DeletePost/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_SWIFT_TicketPostObject->GetTicketPostID() . '/' . $_ticketURLSuffix . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                }

                $_ticketPostHTML .= '<a href="javascript: void(0);" title="' . $_SWIFT->Language->Get('qticketpost') . '" alt="' . $_SWIFT->Language->Get('qticketpost') . '" onclick="javascript: QuoteTicketPost(\'' . $_SWIFT_TicketObject->GetTicketID() . '\', \'' . $_SWIFT_TicketPostObject->GetTicketPostID() . '\');"><i class="fa fa-quote-right" aria-hidden="true"></i></a>';

                $_ticketPostHTML .= '</div>';

                $_ticketPostHTML .= '<div class="ticketbarcontents">' . $_postTitle;
                $_ticketPostHTML .= '<a href="javascript: ToggleTicketDetailsDisplay(\''.$_SWIFT_TicketPostObject->GetTicketPostID().'\');"><div class="triangleDown" ><span><svg version="1.1" width="6" height="6" viewBox="0 0 6 6"><g><title>triangleDown</title><path fill="#BEC4C7" d="M5.7 0c.3 0 .4.4.2.5L3.2 3.8c-.2.2-.4.3-.6 0L0 .6C0 .3 0 0 .4 0h5.4z"></path></g></svg></span></div></a><span class="ticketbardatefold"></span></div></div><div class="ticketPostDetails" id="ticketPostDetails'.$_SWIFT_TicketPostObject->GetTicketPostID().'">' . $_postFooter . '</br>' . $_postFooterRight . '</div>';

                $_ticketDisplayContent = $_SWIFT_TicketPostObject->GetDisplayContents();

                $_ticketPostHTML .= '<div class="ticketpostcontentsdetails"><div class="ticketpostcontentsholder"><div class="ticketpostcontentsdetailscontainer">' . $_ticketDisplayContent . $_postContentSuffix . '</div></div>' . $_attachmentHTML . '</div>';

                $_ticketPostHTML .= '</div>';

                if (!empty($_ratingHTML)) {
                    $_ticketPostHTML .= '<div class="ticketpostbox">' . $_ratingHTML . '</div>';
                }

                $_ticketPostHTML .= '</div>';
            }
        }


        $_ticketPostHTML .= $_paginationHTML;

        $_ticketPostHTML .= '</div>';

        // Retrieve the Ticket Status & Priority JSON
        $_ticketDataJSON = $this->GetTicketDataJSON();


        $_ticketPostHTML .= '<script language="Javascript" type="text/javascript">';
        $_ticketPostHTML .= '_ticketData = ' . $_ticketDataJSON . ';';

        $_ticketPostHTML .= 'if (window.$UIObject) { window.$UIObject.Queue(function(){';
        $_ticketPostHTML .= '$(\'.ticketgeneralpropertiestitle, .ticketgeneralpropertiescontent\').unbind(\'click\').click(function(event) { return HandleTicketPropertiesClick(event); });';

        if ($_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0') {
            /* Bug Fix : Saloni Dhall
             *
             * SWIFT-3023 : Specifying a bad date format in Date & Time settings causes all hell to break loose
             *
             * Comments : The string to be escaped.
             */
            $_ticketPostHTML .= 'UITipBubble(\'labelresolutiondue\', \'' . addslashes(SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->GetProperty('resolutionduedateline'))) . '\');';
        }

        if ($_SWIFT_TicketObject->GetProperty('duetime') != '0') {
            /* Bug Fix : Saloni Dhall
             *
             * SWIFT-3023 : Specifying a bad date format in Date & Time settings causes all hell to break loose
             *
             * Comments : The string to be escaped.
             */
            $_ticketPostHTML .= 'UITipBubble(\'labeldue\', \'' . addslashes(SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->GetProperty('duetime'))) . '\');';
        }
        $_ticketPostHTML .= '}); }</script>';

        $_ticketPostHTML .= $this->GetFlagMenu($_SWIFT_TicketObject);
        $this->UserInterface->AppendHTML($_ticketPostHTML);

        $this->UserInterface->End();

        return true;
    }
}
