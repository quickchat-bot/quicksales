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

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Ticket Manage View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Manage $Controller
 * @author Varun Shoor
 */
class View_Manage extends SWIFT_View
{
    /**
     * Render the Ticket Manage Grid
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param int $_ticketStatusID The Ticket Status ID
     * @param int $_ticketTypeID The Ticket Type ID
     * @param mixed $_ownerFilter The Owner Filter
     * @param int $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid($_departmentID, $_ticketStatusID, $_ticketTypeID, $_ownerFilter, $_searchStoreID)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterfaceGrid = $this->Controller->UserInterfaceGrid;

        SWIFT_TicketViewRenderer::Render($this->UserInterface, $this->UserInterfaceGrid, $_departmentID, $_ticketStatusID, $_ticketTypeID,
                $_ownerFilter, $_searchStoreID);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        if ($_departmentID == '0' && $_SWIFT->Staff->GetPermission('staff_tcandeleteticket') != '0') {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                    array('Tickets\Staff\Controller_Manage', 'DeleteList'), $this->Language->Get('actionconfirm')));
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('putback'), 'fa-undo',
                    array('Tickets\Staff\Controller_Manage', 'PutBackList'), $this->Language->Get('actionconfirm')));
            $this->UserInterfaceGrid->AddAction(array($this->Language->Get('emptytrash'), 'fa-trash-o', '/Tickets/Manage/EmptyTrash', $this->Language->Get('emptytrashconfirm')));
        }


        if ($_departmentID != '0') {

            if ($_SWIFT->Staff->GetPermission('staff_tcanupdateticket') != '0' && $_SWIFT->Staff->GetPermission('staff_tcanviewticket') != '0')
            {
                $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('spam'), 'fa-exclamation-triangle',
                        array('Tickets\Staff\Controller_Manage', 'SpamList'), $this->Language->Get('actionconfirm')));
                $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('watch'), 'fa-eye',
                        array('Tickets\Staff\Controller_Manage', 'WatchList'), $this->Language->Get('actionconfirm')));

                $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('reply'), 'fa-reply',
                        array('Tickets\Staff\Controller_Manage', 'MassReplyList'), '',
                        array($this->Language->Get('massreply'), '600', '480', array($this->Controller, '_MassReplyDialog'))));
                $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('merge'), 'fa-random',
                        array('Tickets\Staff\Controller_Manage', 'MergeList'), $this->Language->Get('actionconfirm')));
            }

            if ($_SWIFT->Staff->GetPermission('staff_tcantrashticket') != '0') {
                $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('trash'), 'fa-trash',
                        array('Tickets\Staff\Controller_Manage', 'TrashList'), $this->Language->Get('actionconfirm')));
            }
        }

        $this->UserInterfaceGrid->Render();
        $this->Controller->_LoadDisplayData();
        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('manage'), Controller_Manage::MENU_ID,
                Controller_Manage::NAVIGATION_ID, $this->Controller->TagCloud->Render());
            SWIFT_TicketViewRenderer::DispatchMenu($_departmentID, $_ticketStatusID, $_ticketTypeID);

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');
        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');
        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');
        $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
        $_slaPlanCache = $_SWIFT->Cache->Get('slaplancache');
        $_escalationRuleCache = $_SWIFT->Cache->Get('escalationrulecache');
        $_bayesianCategoryCache = $_SWIFT->Cache->Get('bayesiancategorycache');

        $_userOrganizationTicketListCustomFieldMap = SWIFT::Get('_userOrganizationTicketListCustomFieldMap');
        $_userTicketListCustomFieldMap = SWIFT::Get('_userTicketListCustomFieldMap');

        if ($_fieldContainer['userid'] != '0' && isset($_userTicketListCustomFieldMap[$_fieldContainer['userid']])) {
            $_fieldContainer = array_merge($_fieldContainer, $_userTicketListCustomFieldMap[$_fieldContainer['userid']]);
        }

        if (isset($_fieldContainer['userorganizationid'])
            && $_fieldContainer['userorganizationid'] != '0'
            && isset($_userOrganizationTicketListCustomFieldMap[$_fieldContainer['userorganizationid']]))
        {
            $_fieldContainer = array_merge($_fieldContainer, $_userOrganizationTicketListCustomFieldMap[$_fieldContainer['userorganizationid']]);
        }

        // Make the ticket URL
        $_ticketListType = 'inbox';
        $_ticketTreeDepartmentID = $_ticketTreeStatusID = $_ticketTreeTypeID = -1;

        if (SWIFT::Get('tickettreedepartmentid') !== false) {
            $_ticketTreeDepartmentID = (int) (SWIFT::Get('tickettreedepartmentid'));
        }

        if (SWIFT::Get('tickettreestatusid')) {
            $_ticketTreeStatusID = (int) (SWIFT::Get('tickettreestatusid'));
        }

        if (SWIFT::Get('tickettreetypeid')) {
            $_ticketTreeTypeID = (int) (SWIFT::Get('tickettreetypeid'));
        }

        if (SWIFT::Get('tickettreelisttype')) {
            $_ticketListType = SWIFT::Get('tickettreelisttype');
        }

        $_ticketURL = SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $_fieldContainer['ticketid'] . '/' . $_ticketListType . '/' .
                $_ticketTreeDepartmentID . '/' . $_ticketTreeStatusID . '/' . $_ticketTreeTypeID;

        // Properties Icon
        $_propertiesIcon = '';
        $_propertiesIconAlt = '';
        if ($_fieldContainer['hasnotes'] == '1' && $_fieldContainer['hasbilling'] == '1') {
            $_propertiesIcon = 'fa-credit-card';
            $_propertiesIconAlt =  $_SWIFT->Language->Get('alt_tickethasbilling');
        } else if ($_fieldContainer['hasnotes'] == '1') {
            $_propertiesIcon = 'fa-commenting-o';
            $_propertiesIconAlt = $_SWIFT->Language->Get('alt_tickethasnote');
        } else if ($_fieldContainer['hasbilling'] == '1') {
            $_propertiesIcon = 'fa-clock-o';
            $_propertiesIconAlt = $_SWIFT->Language->Get('alt_tickethastimetracking');
        }

        if (!empty($_propertiesIcon)) {
            $_fieldContainer['propertyicon'] = '<i class="fa '. $_propertiesIcon .'" data-title="' . $_propertiesIconAlt . '" aria-hidden="true"></i>';
        }

        $_ticketIcon = 'fa-ticket';
        $_ticketIconColor = 'icon_ticket';
        $_ticketIconAlt = '';
        // New Ticket
        if ($_fieldContainer['lastactivity'] > $_SWIFT->Staff->GetProperty('lastvisit')) {
            $_ticketIcon = 'fa-ticket';
            $_ticketIconColor = 'icon_ticketred';
            $_ticketIconAlt = $_SWIFT->Language->Get('alt_ticketunread');
        }

        if ($_fieldContainer['isphonecall'] == '1') {
            $_ticketIcon = 'fa-mobile';
            $_ticketIconColor = '';
            $_ticketIconAlt = $_SWIFT->Language->Get('alt_ticketphonetype');
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_ticketIcon . ' ' . $_ticketIconColor . '" data-title="' . $_ticketIconAlt . '" aria-hidden="true"></i>';

        $_toolTip = '';
        if ($_SWIFT->Settings->Get('t_tpreview') == 1)
        {
            $_toolTip = ' onmouseover="javascript: TicketTipBubble(this, ' . $_fieldContainer['ticketid'] . ');"';
        }

        // Subject Prefixes
        $_subjectPrefix = '';

        // Subject Suffix
        //Attachement prefix icon moved to subject suffix as a part of ui upgrade.
        $_subjectSuffix = '';
        if ($_fieldContainer['hasattachments'] == '1') {
            $_subjectSuffix .= '&nbsp;<i class="fa fa-green-icon fa-paperclip" data-title="' . $_SWIFT->Language->Get('alt_hasattachments') . '" aria-hidden="true"></i>&nbsp;';
        }

        if ($_fieldContainer['isescalated'] == '1') {
            $_subjectSuffix .= '&nbsp;<i class="fa fa-arrow-circle-up" data-title="' . $_SWIFT->Language->Get('alt_isescalated') . '" aria-hidden="true"></i>&nbsp;';
        }

        if ($_fieldContainer['islinked'] == '1') {
            $_subjectSuffix .= '&nbsp;<i class="fa fa-link" data-title="' . $_SWIFT->Language->Get('alt_linkedticket') . '" aria-hidden="true"></i>&nbsp;';
        }

        if ($_fieldContainer['hasfollowup'] == '1') {
            $_subjectSuffix .= '&nbsp;<i class="fa fa-bookmark" data-title="' . $_SWIFT->Language->Get('alt_followupset') . '" aria-hidden="true"></i>&nbsp;';
        }

        if ($_fieldContainer['ownerstaffid'] == $_SWIFT->Staff->GetStaffID()) {
            $_subjectSuffix .= '&nbsp;<i class="fa fa-dot-circle-o" data-title="' . $_SWIFT->Language->Get('alt_assignedotyou') . '" aria-hidden="true"></i>&nbsp;';
        }

        if (isset($_fieldContainer['ticketwatcherstaffid']) && !empty($_fieldContainer['ticketwatcherstaffid'])
                && $_fieldContainer['ticketwatcherstaffid'] == $_SWIFT->Staff->GetStaffID()) {
            $_subjectSuffix .= '&nbsp;<i class="fa fa-eye" data-title="' . $_SWIFT->Language->Get('alt_watchingticket') . '" aria-hidden="true"></i>&nbsp;';
        }

        $_lockThreshold = DATENOW -  $_SWIFT->Settings->Get('t_locktimeout');
        if (!empty($_fieldContainer['lockstaffid']) && $_fieldContainer['lockstaffid'] != $_SWIFT->Staff->GetStaffID() && $_fieldContainer['lockdateline'] > $_lockThreshold) {
            $_subjectSuffix .= '&nbsp;<i class="fa fa-lock" data-title="' . $_SWIFT->Language->Get('alt_ticketlocked') . '" aria-hidden="true"></i>&nbsp;';
        }

        $_smallPagination = '';
        $_finalTotalReplies = (int) ($_fieldContainer['totalreplies'])+1;
        if ($_SWIFT->Settings->Get('t_enpagin') == '1' && $_finalTotalReplies > $_SWIFT->Settings->Get('t_postlimit')) {
            $_smallPagination = SWIFT_UserInterfaceGrid::RenderSmallPagination($_ticketURL . '/%d', $_finalTotalReplies, $_SWIFT->Settings->Get('t_postlimit'), -1, true, true);
        }

        //Any Subject with HTML content will be rendered as plain text.
        $_fieldContainer['tickets.subject'] = '<div class="subjectspancontainer" style="min-width:333px;"><span class="subjectspan" style="float: left;">' . $_subjectPrefix . '<a ' . $_toolTip . ' href="' . $_ticketURL . '" viewport="1">' . $_SWIFT->Input->SanitizeForXSS(StripName($_SWIFT->Emoji->decode($_fieldContainer['subject']), 255), false, true) . '</a>' . $_smallPagination . '</span><span style="float: right;">' . $_subjectSuffix . '</span></div>';
        $_fieldContainer['tickets.ticketid'] = '<a ' . $_toolTip . ' href="' . $_ticketURL . '" viewport="1">' . htmlspecialchars(IIF($_SWIFT->Settings->Get('t_eticketid') == 'seq', (int) ($_fieldContainer['ticketid']), $_fieldContainer['ticketmaskid'])) . '</a>';


        // Email Queue
        if (isset($_emailQueueCache['list'][$_fieldContainer['emailqueueid']])) {
            $_fieldContainer['tickets.emailqueueid'] = htmlspecialchars(StripName($_emailQueueCache['list'][$_fieldContainer['emailqueueid']]['email'], 15));
        } else {
            $_fieldContainer['tickets.emailqueueid'] = '';
        }

        // Department
        if (isset($_departmentCache[$_fieldContainer['departmentid']])) {
            $_fieldContainer['tickets.departmentid'] = text_to_html_entities(StripName($_departmentCache[$_fieldContainer['departmentid']]['title'], 15));
            $_fieldContainer['tickets.departmenttitle'] = $_fieldContainer['tickets.departmentid'];
        } else if ($_fieldContainer['departmentid'] == '0') {
            $_fieldContainer['tickets.departmentid'] = $_SWIFT->Language->Get('trash');
            $_fieldContainer['tickets.departmenttitle'] = $_fieldContainer['tickets.departmentid'];
        } else {
            $_fieldContainer['tickets.departmentid'] = $_SWIFT->Language->Get('na');
            $_fieldContainer['tickets.departmenttitle'] = text_to_html_entities($_fieldContainer['departmenttitle']);
        }

        // Ticket Status
        if (isset($_ticketStatusCache[$_fieldContainer['ticketstatusid']])) {
            $_ticketStatusContainer = $_ticketStatusCache[$_fieldContainer['ticketstatusid']];
            $_displayIconImage = '';
            if (!empty($_ticketStatusContainer['displayicon'])) {
                // $_displayIconImage = '<img src="' . ProcessDisplayIcon($_ticketStatusContainer['displayicon']) . '" align="absmiddle" border="0" />';
                $_displayIconImage = '';
            }

            $_fieldContainer['ticketstatus.displayorder'] = '<span class="ticketStatusIndicator" style="background-color: ' . $_ticketStatusContainer['statusbgcolor'] . ';color: #ffffff;">' .$_displayIconImage . htmlspecialchars($_ticketStatusCache[$_fieldContainer['ticketstatusid']]['title']) . '</span>';

            $_fieldContainer['ticketstatus.displayorder:'] = '';
        } else {

            $_fieldContainer['ticketstatus.displayorder'] = '<span class="ticketStatusIndicator" style="background-color:#cecece;color: #ffffff;">' . htmlspecialchars($_fieldContainer['ticketstatustitle']) . '</span>';
            $_fieldContainer['ticketstatus.displayorder:'] = '';
        }

        // Ticket Priority
        if (isset($_ticketPriorityCache[$_fieldContainer['priorityid']])) {
            $_ticketPriorityContainer = $_ticketPriorityCache[$_fieldContainer['priorityid']];

            /*
            $_displayIconImage = '';
            if (!empty($_ticketPriorityContainer['displayicon'])) {
                $_displayIconImage = '<img src="' . ProcessDisplayIcon($_ticketPriorityContainer['displayicon']) .
                    '" align="absmiddle" border="0" /> ';
            }
            */
            if($_ticketPriorityContainer['bgcolorcode']!=''){
                $_fieldContainer['ticketpriorities.displayorder'] = '<span class="ticketPriorityIndicator" style="background-color: ' . $_ticketPriorityContainer['bgcolorcode'] . ';color: ' . $_ticketPriorityContainer['frcolorcode'] . ';">' . htmlspecialchars($_ticketPriorityCache[$_fieldContainer['priorityid']]['title']) . '</span>';
            }
            else{
                $_fieldContainer['ticketpriorities.displayorder'] = '<span style="color: ' . $_ticketPriorityContainer['frcolorcode'] . ';">' . htmlspecialchars($_ticketPriorityCache[$_fieldContainer['priorityid']]['title']) . '</span>';
            }
            $_fieldContainer['ticketpriorities.displayorder:'] = '';
        } else {
            $_ptitle = $_fieldContainer['prioritytitle'];
            $_ptitle = empty($_ptitle)? $_SWIFT->Language->Get('na') : $_ptitle;
            $_fieldContainer['ticketpriorities.displayorder'] = htmlspecialchars($_ptitle);
        }

        // Ticket Type
        if (isset($_ticketTypeCache[$_fieldContainer['tickettypeid']])) {
            $_ticketTypeContainer = $_ticketTypeCache[$_fieldContainer['tickettypeid']];
            $_displayIconImage = '';
            if (!empty($_ticketTypeContainer['displayicon'])) {
                $_displayIconImage = '<img class="tickettypeicon" src="' . ProcessDisplayIcon($_ticketTypeContainer['displayicon']) .
                    '" align="absmiddle" border="0" /> ';
            }

            $_fieldContainer['tickets.tickettypetitle'] = $_displayIconImage . htmlspecialchars($_ticketTypeCache[$_fieldContainer['tickettypeid']]['title']);
            $_fieldContainer['tickets.tickettypetitleicon'] = $_displayIconImage;
        } else {
            $_fieldContainer['tickets.tickettypetitle'] = htmlspecialchars($_fieldContainer['tickettypetitle']);
        }

        // Template Group
        if (isset($_templateGroupCache[$_fieldContainer['tgroupid']])) {
            $_fieldContainer['tickets.tgroupid'] = htmlspecialchars($_templateGroupCache[$_fieldContainer['tgroupid']]['title']);
        } else {
            $_fieldContainer['tickets.tgroupid'] = $_SWIFT->Language->Get('na');
        }

        // Owner
        if (empty($_fieldContainer['ownerstaffid']))
        {
            $_fieldContainer['tickets.ownerstaffname'] = $_SWIFT->Language->Get('unassigned');
        } else {
            if (isset($_staffCache[$_fieldContainer['ownerstaffid']]))
            {
                $_fieldContainer['tickets.ownerstaffname'] = text_to_html_entities($_staffCache[$_fieldContainer['ownerstaffid']]['fullname']);
            } else {
                $_fieldContainer['tickets.ownerstaffname'] = $_SWIFT->Language->Get('na');
            }
        }

        // Ticket Flag
        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();
        $_flagList = $_SWIFT_TicketFlagObject->GetFlagContainer();
        if (isset($_flagList[$_fieldContainer['flagtype']])) {
            // $_fieldContainer['tickets.flagtype'] = '<a href="javascript: void(0);" onclick="javascript: ToggleFlag(\'' .
            //     $_fieldContainer['ticketid'] . '\', \'' . $_flagList[SWIFT_TicketFlag::FLAG_RED][1] . '\', \'' .
            //     $_flagList[SWIFT_TicketFlag::FLAG_RED][2] . '\');"><img id="ticketflagimg_' . $_fieldContainer['ticketid'] .
            //     '" src="' . SWIFT::Get('themepathimages') . $_flagList[$_fieldContainer['flagtype']][2] .
            //     '" align="absmiddle" border="0" /></a>';

            $_fieldContainer['tickets.flagtype'] = '<a href="javascript: void(0);" onclick="javascript: ToggleFlag(\'' .
                $_fieldContainer['ticketid'] . '\', \'' . $_flagList[SWIFT_TicketFlag::FLAG_RED][1] . '\', \'' .
                $_flagList[SWIFT_TicketFlag::FLAG_RED][2] . '\');"><i class="fa fa-flag" id="ticketflagimg_' . $_fieldContainer['ticketid'] .
                '" src="' . SWIFT::Get('themepathimages') . $_flagList[$_fieldContainer['flagtype']][2] .
                '" style="color:' . $_flagList[$_fieldContainer['flagtype']][1] . ';"></i></a>';

            // $_fieldContainer['tickets.flagtype:'] = 'background-color: ' . $_flagList[$_fieldContainer['flagtype']][1] . ';';
            $_fieldContainer['tickets.flagtype:'] = '';

        } else {
            // $_fieldContainer['tickets.flagtype'] = '<a href="javascript: void(0);" onclick="javascript: ToggleFlag(\'' .
            //     $_fieldContainer['ticketid'] . '\', \'' . $_flagList[SWIFT_TicketFlag::FLAG_RED][1] . '\', \'' .
            //     $_flagList[SWIFT_TicketFlag::FLAG_RED][2] . '\');"><img id="ticketflagimg_' . $_fieldContainer['ticketid'] .
            //     '" src="' . SWIFT::Get('themepathimages') . 'icon_flagblank.gif' .
            //     '" align="absmiddle" border="0" /></a>';

            $_fieldContainer['tickets.flagtype'] = '<a href="javascript: void(0);" onclick="javascript: ToggleFlag(\'' .
                $_fieldContainer['ticketid'] . '\', \'' . $_flagList[SWIFT_TicketFlag::FLAG_RED][1] . '\', \'' .
                $_flagList[SWIFT_TicketFlag::FLAG_RED][2] . '\');"><i class="fa fa-flag-o" id="ticketflagimg_' . $_fieldContainer['ticketid'] .
                '" src="' . SWIFT::Get('themepathimages') . 'icon_flagblank.gif' .
                '" ></i></a>';
        }

        // SLA Plan
        if (isset($_slaPlanCache[$_fieldContainer['slaplanid']])) {
            $_fieldContainer['tickets.slaplanid'] = htmlspecialchars(StripName($_slaPlanCache[$_fieldContainer['slaplanid']]['title'], 18));
        }

        // Due Time
        $_ticketIsOverdue = false;
        if (!empty($_fieldContainer['duetime'])) {
            if ($_fieldContainer['duetime'] < DATENOW) {
                $_ticketIsOverdue = true;
                $_fieldContainer['tickets.duetime'] = '<span class="ticketOverdueIndicator" style="background-color: #e05720;color:#fff;">' . $_SWIFT->Language->Get('overdue') . '</span>';
                $_fieldContainer['tickets.duetime:'] = '';
            } else {
                $_dueTimeSeconds = $_fieldContainer['duetime'] - DATENOW;

                $_fieldContainer['tickets.duetime'] = SWIFT_Date::ColorTime($_dueTimeSeconds, true);
            }
        }

        if (!empty($_fieldContainer['resolutionduedateline'])) {
            if ($_fieldContainer['resolutionduedateline'] < DATENOW) {
                $_ticketIsOverdue = true;
                $_fieldContainer['tickets.resolutionduedateline'] = '<span class="ticketOverdueIndicator" style="background-color: #e05720;color:#fff;">' . $_SWIFT->Language->Get('overdue') . '</span>';
                $_fieldContainer['tickets.resolutionduedateline:'] = '';
            } else {
                $_resolutionDueTimeSeconds = $_fieldContainer['resolutionduedateline'] - DATENOW;

                $_fieldContainer['tickets.resolutionduedateline'] = SWIFT_Date::ColorTime($_resolutionDueTimeSeconds, true);
            }
        }

        if ($_ticketIsOverdue) {
            $overdueBackgroundColor = $_SWIFT->Settings->Get('t_overduecolor') ?? '#FFFFFF';
            $_fieldContainer[':'] = 'background-color: ' . $overdueBackgroundColor . ' !important;';
        }

        // General Properties
        $_fieldContainer['tickets.fullname'] = text_to_html_entities(StripName($_fieldContainer['fullname'], 15));
        $_fieldContainer['tickets.lastreplier'] = htmlspecialchars(StripName($_fieldContainer['lastreplier'], 15));
        $_fieldContainer['tickets.email'] = htmlspecialchars(StripName($_fieldContainer['email'], 15));

        $_replyCount = 0;
        $_massReplyTicketIDList = SWIFT::Get('massreplyticketidlist');
        if (_is_array($_massReplyTicketIDList) && in_array($_fieldContainer['ticketid'], $_massReplyTicketIDList)) {
            $_replyCount++;
        }

        $_fieldContainer['tickets.totalreplies'] = (int) ($_fieldContainer['totalreplies']) + $_replyCount;
        $_fieldContainer['users.usergroupid'] = htmlspecialchars(StripName($_fieldContainer['usergrouptitle'], 18));
        $_fieldContainer['userorganizations.organizationname'] = htmlspecialchars(StripName($_fieldContainer['userorganizationname'], 18));
        $_fieldContainer['tickets.timeworked'] = SWIFT_Date::ColorTime($_fieldContainer['timeworked'], false, true);

        // Escalations
        if (isset($_escalationRuleCache[$_fieldContainer['escalationruleid']])) {
            $_fieldContainer['tickets.escalationruleid'] =
                htmlspecialchars(StripName($_escalationRuleCache[$_fieldContainer['escalationruleid']]['title'], 18));
        }

        if (!empty($_fieldContainer['escalatedtime'])) {
            $_escalationSeconds = DATENOW - $_fieldContainer['escalatedtime'];

            $_fieldContainer['tickets.escalatedtime'] = SWIFT_Date::ColorTime($_escalationSeconds);
        }

        // Bayesian
        if (isset($_bayesianCategoryCache[$_fieldContainer['bayescategoryid']])) {
            $_fieldContainer['tickets.bayescategoryid'] =
                htmlspecialchars(StripName($_bayesianCategoryCache[$_fieldContainer['bayescategoryid']]['category'], 15));
        }

        // Date Related Properties
        $_lastActivitySeconds = DATENOW - $_fieldContainer['lastactivity'];
        $_fieldContainer['tickets.lastactivity'] = SWIFT_Date::ColorTime($_lastActivitySeconds);

        $_fieldContainer['tickets.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_fieldContainer['tickets.laststaffreplytime'] = '';
        if (!empty($_fieldContainer['laststaffreplytime'])) {
            $_lastStaffReplySeconds = DATENOW - $_fieldContainer['laststaffreplytime'];
            $_fieldContainer['tickets.laststaffreplytime'] = SWIFT_Date::ColorTime($_lastStaffReplySeconds);
        }

        $_fieldContainer['tickets.lastuserreplytime'] = '';
        if (!empty($_fieldContainer['lastuserreplytime'])) {
            $_lastUserReplySeconds = DATENOW - $_fieldContainer['lastuserreplytime'];
            $_fieldContainer['tickets.lastuserreplytime'] = SWIFT_Date::ColorTime($_lastUserReplySeconds);
        }

        return $_fieldContainer;
    }

    /**
     * Render the MassReply Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderMassReply()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start('MassReplyDialog', $_POST['_gridURL'], SWIFT_UserInterface::MODE_INSERT, false, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('dispatchsend'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('manageorganization'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN REPLY TAB
         * ###############################################
         */

        $_ReplyTabObject = $this->UserInterface->AddTab($this->Language->Get('tabreply'), 'icon_ticketreply.png', 'reply', true);
        $_ReplyTabObject->SetColumnWidth('150');
        $_ReplyTabObject->TextArea('replycontents', '', '', '', '30', '17', false, '');

        if (isset($_POST['itemid']) && _is_array($_POST['itemid']))
        {
            foreach ($_POST['itemid'] as $_ticketID) {
                $_ReplyTabObject->Hidden('ticketid[]', $_ticketID);
            }
        }

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        $this->UserInterface->End();

        return true;
    }
}
