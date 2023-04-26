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

namespace Base\Staff;

use LiveChat\Models\Message\SWIFT_MessageManager;
use SWIFT;
use SWIFT_App;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_View;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Dashboard View
 *
 * @author Varun Shoor
 *
 * @property Controller_Home $Controller
 */
class View_Home extends SWIFT_View
{
    private $_infoClass = '2';

    /**
     * Render the Error HTML
     *
     * @author Varun Shoor
     * @param string $_title The Box Title
     * @param string $_contents The Box Contents
     * @param string $_date The Date Row Data
     * @return string|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderError($_title, $_contents, $_date = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || empty($_contents)) {
            return false;
        }

        return '<div class="dashboardboxerror"><div class="dashboardboxtitlecontainer"><div class="dashboardboxtitle">' . $_title . '</div><div class="dashboardboxdate">' . $_date . '</div></div>' . $_contents . '</div>';
    }

    /**
     * Render the Alert HTML
     *
     * @author Varun Shoor
     * @param string $_title The Box Title
     * @param string $_contents The Box Contents
     * @param string $_date The Date Row Data
     * @return string|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderAlert($_title, $_contents, $_date = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || empty($_contents)) {
            return false;
        }

        return '<div class="dashboardboxalert"><div class="dashboardboxtitlecontainer"><div class="dashboardboxtitle">' . $_title . '</div><div class="dashboardboxdate">' . $_date . '</div></div>' . $_contents . '</div>';
    }

    /**
     * Renders the Info HTML
     *
     * @author Varun Shoor
     * @param string $_title The Box Title
     * @param string $_contents The Box Contents
     * @param string $_date The Date Row Data
     * @return string|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfo($_title, $_contents, $_date = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || empty($_contents)) {
            return false;
        }

        if ($this->_infoClass == '1') {
            $this->_infoClass = '2';
        } else {
            $this->_infoClass = '1';
        }

        return '<div class="dashboardboxinfo' . $this->_infoClass . '"><div class="dashboardboxtitlecontainer"><div class="dashboardboxtitle">' . $_title . '</div><div class="dashboardboxdate">' . $_date . '</div></div>' . $_contents . '</div>';
    }

    /**
     * Renders the Dashboard
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderDashboard($_counterContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffLastVisit = $_SWIFT->Staff->GetProperty('lastvisit');
        if (empty($_staffLastVisit)) {
            $_lastVisit = $this->Language->Get('never');
        } else {
            $_lastVisit = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT->Staff->GetProperty('lastvisit'));
        }

        $_staffDesignation = $_SWIFT->Staff->GetProperty('designation');
        $_designationExtended = '';

        if (!empty($_staffDesignation)) {
            $_designationExtended = ' (' . htmlspecialchars($_staffDesignation) . ')';
        }

        echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dashboardlayoutborder" style="margin-top: 0;">
        <tr>
        <td align="left" valign="bottom" id="dashboardcontainer">
        <div id="dashboardtitle">' . $this->Language->Get('dashdashboard') . '</div>
        </td>
        </tr>
        <tr><td align="left" valign="top">

        <div style="PADDING: 8px;padding-right: 15px;">

        <!-- BEGIN FIRST ROW (DATE, USERDETAILS) -->
        <table width="100%" border="0" cellspacing="1" cellpadding="0">
        <tr>
        <td align="left" valign="top" width="">

        <div style="display: inline-block; float: right;">';

        echo $this->RenderCounters($_counterContainer);

        echo '</div>

            <div style="float: left; padding-left: 8px;">
            <table width="100%" border="0" cellspacing="1" cellpadding="0">
            <tr>
            <td align="left" valign="middle" width="80">
            <div class="dashboardavatarimage">
            <img src="' . SWIFT::Get('basename') . '/Base/StaffProfile/DisplayAvatar/' . $_SWIFT->Staff->GetStaffID() . '/' . md5($_SWIFT->Staff->GetProperty('email')) . '/80/0" align="absmiddle" />
            </div>
            </td>
            <td align="left" valign="top" width=""><div class="dashboardrightcontents"><div class="dashboardusername">' . text_to_html_entities($_SWIFT->Staff->GetProperty('fullname')) . '</div>
            <div class="smalltext">' . $this->Language->Get('dashusername') . ' ' . htmlspecialchars($_SWIFT->Staff->GetProperty('username')) . '<br />
            ' . $this->Language->Get('dashemail') . ' ' . htmlspecialchars($_SWIFT->Staff->GetProperty('email')) . '<br />
            ' . $this->Language->Get('dashlastlogin') . ' ' . $_lastVisit . '<br /></div></div>
            </td>
            </tr>
            </table>
            </div>
        </td>
        <td align="left" valign="top" width="400">

        <div style="float: right;">
        <table width="100%" border="0" cellspacing="1" cellpadding="0">
        <tr>
        <td align="left" valign="top" width="80">
            <div class="dashboarddate">
                <div class="dashboarddatecontainer">
                <div class="dashboardmonthholder"><div class="dashboardmonthsub">' . SWIFT_Date::Get(SWIFT_Date::TYPE_CUSTOM, false, '%b') . '</div></div>
                <div class="dashboarddateholder"><div class="dashboarddatesub">' . SWIFT_Date::Get(SWIFT_Date::TYPE_CUSTOM, false, '%A') . '</div><div class="dashboarddatedcontainer">' . SWIFT_Date::Get(SWIFT_Date::TYPE_CUSTOM, false, '%d') . '</div></div>
                </div>
            </div>
        </td>
        </tr></table>
        </div>

        </td>
        </tr></table>
        <!-- END FIRST ROW -->

        <!-- BEGIN SECOND ROW -->
        <table width="100%" border="0">
        <tr>
        <td align="left" valign="top" width="">
        <div id="dashboardtabs"><ul>';

        $_tabContainer = $this->Controller->_GetTabContainer();

        $_tabHTML = '';
        if (_is_array($_tabContainer)) {
            foreach ($_tabContainer as $_key => $_val) {
                echo '<li><a href="#dashboardtabs-' . $_val[Controller_Home::TAB_NAME] . '">' . IIF(!empty($_val[Controller_Home::TAB_COUNTER]), '<div class="notecounterredver">' . $_val[Controller_Home::TAB_COUNTER] . '</div>') . '<img src="' . SWIFT::Get('themepath') . 'images/' . $_val[Controller_Home::TAB_ICON] . '' . '" align="absmiddle" border="0" /> ' . $_val[Controller_Home::TAB_TITLE] . '</a></li>';

                $_tabHTML .= '<div id="dashboardtabs-' . $_val[Controller_Home::TAB_NAME] . '"><div class="ui-tabs"></div>';

                $_tabHTML .= $_val[Controller_Home::TAB_CONTENTS];

                $_tabHTML .= '<div class="ui-tabs"></div></div>';
            }
        }

        echo '</ul>';

        echo $_tabHTML;

        echo '</div>
        </td>
        ';

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            echo '<td align="left" valign="top" width="350" class="overviewtab"><div id="loadingDiv">
<br/>loading stats...<br/><br/>
<img src="'.SWIFT::Get('themepathimages').'kayako-loader.gif" border="0" align="absmiddle" /></div></td>';
        }

        echo '</tr>
        </table>
        <!-- END SECOND ROW -->

        <script type="text/javascript">
        QueueFunction(function() {
            $("#dashboardtabs").tabs().addClass(\'ui-tabs-hide ui-tabs ui-widget ui-widget-content ui-corner-all\').removeClass(\'ui-corner-all ui-widget-content\');
        });

        $(function() {
            ClearFunctionQueue();
        });
        </script>

        </div></td></tr></table>
        <div id="klic"></div>';

        return true;
    }

    /**
     * Render the Chat Tab View
     *
     * @author Varun Shoor
     * @param array $_chatObjectContainer The Chat Object Container
     * @return string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderRecentChatTabView($_chatObjectContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_showingSuffix = '';
        if (count($_chatObjectContainer[1])) {
            $_showingSuffix = sprintf($this->Language->Get('showingmsg'), count($_chatObjectContainer[1]), $_chatObjectContainer[0]);
        }

        $_tabContents = '<div>';

        $_tabContents .= $this->RenderTabHeader($this->Language->Get('tabrecentchats') . $_showingSuffix, '/LiveChat/ChatHistory/Manage');

        $_tabContents .= '<div class="dashboardtabdatacontainer">';

        if (!_is_array($_chatObjectContainer[1])) {
            $_tabContents .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {

            $_rowClass = 'gridrow1';

            $_tabContents .= '<div class="gridcontentborder"><table cellspacing="1" cellpadding="4" border="0" width="100%">';
            $_tabContents .= '<tr class="gridtabletitlerow">
    <td class="gridtabletitlerow" width="20" valign="middle" align="center"> </td>
    <td class="gridtabletitlerow" width="100" valign="middle" align="left">' . $this->Language->Get('lcchatid') . '</td>
    <td class="gridtabletitlerow" valign="middle" align="left">' . $this->Language->Get('lcfullname') . '</td>
    <td class="gridtabletitlerow" width="160" valign="middle" align="left">' . $this->Language->Get('lcstaff') . '</td>
    <td class="gridtabletitlerow" width="160" valign="middle" align="left">' . $this->Language->Get('lcdepartment') . '</td>
    </tr>';
            if (_is_array($_chatObjectContainer[1])) {
                foreach ($_chatObjectContainer[1] as $_key => $_val) {
                    if ($_rowClass == 'gridrow1') {
                        $_rowClass = 'gridrow2';
                    } else {
                        $_rowClass = 'gridrow1';
                    }

                    $_chatLink = SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . (int)($_val['chatobjectid']);

                    $_tabContents .= '<tr class="' . $_rowClass . '"><td class="' . $_rowClass . '" valign="middle" align="center"><img src="' . SWIFT::Get('themepathimages') . 'icon_chatballoon.png' . '" align="absmiddle" border="0" /></td><td class="' . $_rowClass . '" valign="middle" align="left"><a href="' . $_chatLink . '" viewport="1">' . htmlspecialchars($_val['chatobjectmaskid']) . '</a></td><td class="' . $_rowClass . '" valign="middle" align="left"><a href="' . $_chatLink . '" viewport="1">' . text_to_html_entities($_val['userfullname']) . '</a></td><td class="' . $_rowClass . '" valign="middle" align="left">' . htmlspecialchars($_val['staffname']) . '</td><td class="' . $_rowClass . '" valign="middle" align="left">' . text_to_html_entities($_val['departmenttitle']) . '</td></tr>';

                }
            }
            $_tabContents .= '</table></div>';

        }

        $_tabContents .= '</div>';
        $_tabContents .= '</div>';


        return $_tabContents;
    }

    /**
     * Render the Message Tab View
     *
     * @author Varun Shoor
     * @param array $_messageContainer The Message Container
     * @return string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderRecentMessageTabView($_messageContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');


        $_showingSuffix = '';
        if (count($_messageContainer[1])) {
            $_showingSuffix = sprintf($this->Language->Get('showingmsg'), count($_messageContainer[1]), $_messageContainer[0]);
        }

        $_tabContents = '<div>';

        $_tabContents .= $this->RenderTabHeader($this->Language->Get('tabrecentmessages') . $_showingSuffix, '/LiveChat/Message/Manage');

        $_tabContents .= '<div class="dashboardtabdatacontainer">';

        if (!_is_array($_messageContainer[1])) {
            $_tabContents .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {

            $_rowClass = 'gridrow1';

            $_tabContents .= '<div class="gridcontentborder"><table cellspacing="1" cellpadding="4" border="0" width="100%">';
            $_tabContents .= '<tr class="gridtabletitlerow">
    <td class="gridtabletitlerow" width="20" valign="middle" align="center"> </td>
    <td class="gridtabletitlerow" width="100" valign="middle" align="left">' . $this->Language->Get('lcmessageid') . '</td>
    <td class="gridtabletitlerow" valign="middle" align="left">' . $this->Language->Get('lcsubject') . '</td>
    <td class="gridtabletitlerow" width="160" valign="middle" align="center">' . $this->Language->Get('lcfullname') . '</td>
    <td class="gridtabletitlerow" width="160" valign="middle" align="center">' . $this->Language->Get('lcdepartment') . '</td>
    </tr>';
            if (_is_array($_messageContainer[1])) {
                foreach ($_messageContainer[1] as $_key => $_val) {
                    if ($_rowClass == 'gridrow1') {
                        $_rowClass = 'gridrow2';
                    } else {
                        $_rowClass = 'gridrow1';
                    }

                    $_messageLink = SWIFT::Get('basename') . '/LiveChat/Message/ViewMessage/' . (int)($_val['messageid']);

                    if ($_val['messagetype'] == SWIFT_MessageManager::MESSAGE_CLIENT || $_val['messagetype'] == SWIFT_MessageManager::MESSAGE_STAFF) {
                        $_messageIcon = 'icon_email.gif';
                    } else if ($_val['messagetype'] == SWIFT_MessageManager::MESSAGE_CLIENTSURVEY) {
                        $_messageIcon = 'icon_survey.gif';
                    } else {
                        $_messageIcon = 'icon_email.gif';
                    }

                    $_departmentTitle = $this->Language->Get('na');
                    if (isset($_departmentCache[$_val['departmentid']])) {
                        $_departmentTitle = $_departmentCache[$_val['departmentid']]['title'];
                    }

                    $_tabContents .= '<tr class="' . $_rowClass . '"><td class="' . $_rowClass . '" valign="middle" align="center"><img src="' . SWIFT::Get('themepathimages') . $_messageIcon . '" align="absmiddle" border="0" /></td><td class="' . $_rowClass . '" valign="middle" align="left"><a href="' . $_messageLink . '" viewport="1">' . htmlspecialchars($_val['messagemaskid']) . '</a></td><td class="' . $_rowClass . '" valign="middle" align="left"><a href="' . $_messageLink . '" viewport="1">' . htmlspecialchars($_val['subject']) . '</a></td><td class="' . $_rowClass . '" valign="middle" align="center">' . text_to_html_entities($_val['fullname']) . '</td><td class="' . $_rowClass . '" valign="middle" align="center">' . text_to_html_entities($_departmentTitle) . '</td></tr>';

                }
            }
            $_tabContents .= '</table></div>';

        }

        $_tabContents .= '</div>';
        $_tabContents .= '</div>';


        return $_tabContents;
    }

    /**
     * Render the Overdue Tickets View
     *
     * @author Varun Shoor
     * @param array $_ticketsContainer The Tickets Container
     * @return string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderOverdueTicketsTabView($_ticketsContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_staffCache = $this->Cache->Get('staffcache');

        $_showingSuffix = '';
        if (count($_ticketsContainer[1])) {
            $_showingSuffix = sprintf($this->Language->Get('showingmsg'), count($_ticketsContainer[1]), $_ticketsContainer[0]);
        }

        $_tabContents = '<div>';

        $_tabContents .= $this->RenderTabHeader($this->Language->Get('taboverduetickets') . $_showingSuffix, '/Tickets/Search/Overdue');

        $_tabContents .= '<div class="dashboardtabdatacontainer">';

        if (!_is_array($_ticketsContainer[1])) {
            $_tabContents .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {

            $_rowClass = 'gridrow1';

            $_tabContents .= '<div class="gridcontentborder"><table cellspacing="1" cellpadding="4" border="0" width="100%">';
            $_tabContents .= '<tr class="gridtabletitlerow">
    <td class="gridtabletitlerow" width="20" valign="middle" align="center"> </td>
    <td class="gridtabletitlerow" width="100" valign="middle" align="left">' . $this->Language->Get('dashticketid') . '</td>
    <td class="gridtabletitlerow" valign="middle" align="left">' . $this->Language->Get('dashsubject') . '</td>
    <td class="gridtabletitlerow" width="140" valign="middle" align="left">' . $this->Language->Get('dashowner') . '</td>
    <td class="gridtabletitlerow" width="140" valign="middle" align="left">' . $this->Language->Get('dashdepartment') . '</td>
    <td class="gridtabletitlerow" width="100" valign="middle" align="left">' . $this->Language->Get('dashstatus') . '</td>
    </tr>';
            if (_is_array($_ticketsContainer[1])) {
                foreach ($_ticketsContainer[1] as $_ticketID => $_ticket) {
                    $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_ticket));
                    if ($_rowClass == 'gridrow1') {
                        $_rowClass = 'gridrow2';
                    } else {
                        $_rowClass = 'gridrow1';
                    }

                    $_departmentTitle = $_statusTitle = $_typeTitle = $_priorityTitle = $_ownerTitle = $this->Language->Get('na');

                    if ($_SWIFT_TicketObject->GetProperty('departmentid') == '0') {
                        $_departmentTitle = $this->Language->Get('trash');
                    } else if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')])) {
                        $_departmentTitle = $_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title'];
                    }

                    if ($_SWIFT_TicketObject->GetProperty('ownerstaffid') == '0') {
                        $_ownerTitle = $this->Language->Get('unassigned');
                    } else if (isset($_staffCache[$_SWIFT_TicketObject->GetProperty('ownerstaffid')])) {
                        $_ownerTitle = $_staffCache[$_SWIFT_TicketObject->GetProperty('ownerstaffid')]['fullname'];
                    }

                    if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
                        $_statusTitle = $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['title'];
                    }

                    if (isset($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')])) {
                        $_typeTitle = $_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]['title'];
                    }

                    if (isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')])) {
                        $_priorityTitle = $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]['title'];
                    }

                    $_ticketLink = SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $_ticketID;

                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                     *
                     * SWIFT-5060 Unicode characters like emojis not working in the subject
                     */

                    $_tabContents .= '<tr class="' . $_rowClass . '"><td class="' . $_rowClass . '" valign="middle" align="center"><i class="fa fa-ticket" aria-hidden="true"></i></td><td class="' . $_rowClass . '" valign="middle" align="left"><a href="' . $_ticketLink . '" viewport="1">' . htmlspecialchars($_SWIFT_TicketObject->GetTicketDisplayID()) . '</a></td><td class="' . $_rowClass . '" valign="middle" align="left"><a href="' . $_ticketLink . '" viewport="1">' . htmlspecialchars($this->Input->SanitizeForXSS($this->Emoji->Decode($_SWIFT_TicketObject->GetProperty('subject')))) . '</a></td><td class="' . $_rowClass . '" valign="middle" align="left">' . htmlspecialchars($_ownerTitle) . '</td><td class="' . $_rowClass . '" valign="middle" align="left">' . text_to_html_entities($_departmentTitle) . '</td><td class="' . $_rowClass . '" valign="middle" align="left">' . htmlspecialchars($_statusTitle) . '</td></tr>';

                }
            }
            $_tabContents .= '</table></div>';

        }

        $_tabContents .= '</div>';
        $_tabContents .= '</div>';


        return $_tabContents;
    }

    /**
     * Render the Tab Header
     *
     * @author Varun Shoor
     * @param string $_headerTitle The Header Title
     * @param string|bool $_extendedLink (OPTIONAL) The Extended Link to View More Info
     * @return string|bool rendered html on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTabHeader($_headerTitle, $_extendedLink = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<div>';

        if (!empty($_extendedLink)) {
            $_renderHTML .= '<div style="display: block;min-height: 30px;"><div class="viewmore" onclick="javascript: loadViewportData(\'' . $_extendedLink . '\');">View More</div></div>';
        }
        $_renderHTML .= '<table class="hlineheaderext"><tr><th rowspan="2" nowrap>' . $_headerTitle . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>';
        $_renderHTML .= '</div>';

        return $_renderHTML;
    }

    /**
     * Render Counters
     *
     * @author Varun Shoor
     * @param array $_counterContainer The Counter Container
     * @return string The Rendered HTML
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderCounters($_counterContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '';

        foreach ($_counterContainer as $_counter) {
            if (empty($_counter[1])) {
                continue;
            }

            $_renderHTML .= '
            <div class="dashboardcounter" onclick="javascript: loadViewportData(\'' . $_counter[2] . '\');">
                <div class="dashboardcounterparent">
                    <div class="dashboardcounterheader">' . $_counter[0] . '</div>
                    <div class="dashboardcounternumber">' . $_counter[1] . '</div>
                </div>
            </div>
            ';
        }

        return $_renderHTML;
    }
}
