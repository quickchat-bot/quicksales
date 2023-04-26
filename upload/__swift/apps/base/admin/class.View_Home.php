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

namespace Base\Admin;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Dashboard View
 *
 * @author Varun Shoor
 * @property Controller_Home $Controller
 */
class View_Home extends SWIFT_View
{
    private $_infoClass = '2';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render the Error HTML
     *
     * @author Varun Shoor
     * @param string $_title The Box Title
     * @param string $_contents The Box Contents
     * @param string $_date The Date Row Data
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderError($_title, $_contents, $_date = '', $_escape = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_title) || empty($_contents)) {
            return false;
        }

        $_finalContents = $_contents;
        if ($_escape) {
            $_finalContents = nl2br(htmlspecialchars($_contents));
        }

        return '<div class="dashboardboxerror"><div class="dashboardboxtitlecontainer"><div class="dashboardboxtitle">' . $_title . '</div><div class="dashboardboxdate">' . $_date . '</div></div>' . $_finalContents . '</div>';
    }

    /**
     * Render the Alert HTML
     *
     * @author Varun Shoor
     * @param string $_title The Box Title
     * @param string $_contents The Box Contents
     * @param string $_date The Date Row Data
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderAlert($_title, $_contents, $_date = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_title) || empty($_contents)) {
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
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfo($_title, $_contents, $_date = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_title) || empty($_contents)) {
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
    public function RenderDashboard()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffLastVisit = $_SWIFT->Staff->GetProperty('lastvisit');
        if (empty($_staffLastVisit)) {
            $_lastVisit = $this->Language->Get('never');
        } else {
            $_lastVisit = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT->Staff->GetProperty('lastvisit')) . ' (' . SWIFT_Date::ColorTime(DATENOW - $_SWIFT->Staff->GetProperty('lastvisit'), false, true) . ')';
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

//        echo $this->RenderCounters($_counterContainer);

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
        <td align="left" valign="top" width="250">

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4">
        <tr>
        <td align="left" valign="top" width="">
        <div id="dashboardtabs"><ul>';

        $_tabContainer = $this->Controller->_GetTabContainer();

        $_tabHTML = '';
        if (_is_array($_tabContainer)) {
            foreach ($_tabContainer as $_key => $_val) {
                echo '<li>' . IIF(!empty($_val[Controller_Home::TAB_COUNTER]), '<div class="notecounterredver">' . $_val[Controller_Home::TAB_COUNTER] . '</div>') . '<a href="#dashboardtabs-' . $_val[Controller_Home::TAB_NAME] . '"><img src="' . SWIFT::Get('themepath') . 'images/' . $_val[Controller_Home::TAB_ICON] . '' . '" align="absmiddle" border="0" /> ' . $_val[Controller_Home::TAB_TITLE] . '</a></li>';

                $_tabHTML .= '<div id="dashboardtabs-' . $_val[Controller_Home::TAB_NAME] . '"><div class="ui-tabs"></div>';

                $_tabHTML .= $_val[Controller_Home::TAB_CONTENTS];

                $_tabHTML .= '<div class="ui-tabs"></div></div>';
            }
        }

        echo '</ul>';

        echo $_tabHTML;

        echo '</div>
        </td>';


        /**
         * ---------------------------------------------
         * LICENSE DETAILS
         * ---------------------------------------------
         */
        $_domainList = array();

        $_notAvailable = $this->Language->Get('na');

        $_licenseExpiry = $_licenseFullName = $_licenseUniqueID = $_licenseStaff = $_licenseOrganization = $_notAvailable;

        if (SWIFT::Get('licenseexpiry') !== false && SWIFT::Get('licenseexpiry') > 100) {
            $_licenseExpiry = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, SWIFT::Get('licenseexpiry'));
        } else {
            $_licenseExpiry = $this->Language->Get('licexpirenever');
        }

        if (SWIFT::Get('licensefullname') !== false) {
            $_licenseFullName = text_to_html_entities(SWIFT::Get('licensefullname'));
        }

        if (SWIFT::Get('licenseorganization') !== false) {
            $_licenseOrganization = text_to_html_entities(SWIFT::Get('licenseorganization'));
        }

        if (SWIFT::Get('licenseuniqueid') !== false) {
            $_licenseUniqueID = htmlspecialchars(SWIFT::Get('licenseuniqueid'));
        }

        if (SWIFT::Get('licensestaff') !== false) {
            if (SWIFT::Get('licensestaff') == '0') {
                $_licenseStaff = $this->Language->Get('licunlimited');
            } else {
                $_licenseStaff = htmlspecialchars(SWIFT::Get('licensestaff'));
            }
        }

        if (SWIFT::Get('licensedomains') !== false) {
            $_domainList = SWIFT::Get('licensedomains');
        }

        echo '<td align="left" valign="top" width="200">';

        echo '<div class="ui-tabs ui-widget ui-tabs-hide">
    <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
        <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
            <a href="javascript:void(0);" class="currenttab">
            <span>
            <div style="height: 16px; float: left;">' . $this->Language->Get('licensedetails') . '</div>
            </span>
            </a>
        </li>
    </ul>
<div class="basictabcontent adminlicenseinfo">
<table cellspacing="0" cellpadding="4" border="0">
<tbody>
<tr>
<td class="gridrow1" width="40%" valign="top" align="left">' . $this->Language->Get('dproduct') . '</td>
<td class="gridrow2" width="60%" valign="top" align="left">' . SWIFT_PRODUCT . '</td>
</tr>
<tr>
<td class="gridrow1" width="40%" valign="top" align="left">' . $this->Language->Get('licversion') . '</td>
<td class="gridrow2" width="60%" valign="top" align="left">' . SWIFT_VERSION . '</td>
</tr>
<tr>
<td class="gridrow1" width="40%" valign="top" align="left">' . $this->Language->Get('licowner') . ':</td>
<td class="gridrow2" width="60%" valign="top" align="left">' . $_licenseFullName . '</td>
</tr>
<tr>
<td class="gridrow1" width="40%" valign="top" align="left">' . $this->Language->Get('licorganization') . ':</td>
<td class="gridrow2" width="60%" valign="top" align="left">' . $_licenseOrganization . '</td>
</tr>
<tr>
<td class="gridrow1" width="40%" valign="top" align="left">' . $this->Language->Get('licexpires') . ':</td>
<td class="gridrow2" width="60%" valign="top" align="left">' . $_licenseExpiry . '</td>
</tr>
<tr>
<td class="gridrow1" width="40%" valign="top" align="left">' . $this->Language->Get('licstaff') . ':</td>
<td class="gridrow2" width="60%" valign="top" align="left">' . $_licenseStaff . '</td>
</tr>
<tr>
<td class="gridrow1" width="40%" valign="top" align="left">' . $this->Language->Get('licdomains') . ':</td>
<td class="gridrow2" width="60%" valign="top" align="left">' . implode('<br />', $_domainList) . '</td>
</tr>
</tbody>
</table>
</div></div>';



        echo '
        </td>
        </tr>
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
        <div id="klic"></div>
        ';
    }

    /**
     * Render the Recent Activity Tab
     *
     * @author Varun Shoor
     * @param array $_activityContainer The Activity Container
     * @return string The Tab Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderRecentActivityTab($_activityContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_showingSuffix = '';
        if (count($_activityContainer[1])) {
            $_showingSuffix = sprintf($this->Language->Get('showingmsg'), count($_activityContainer[1]), $_activityContainer[0]);
        }

        $_tabContents = '<div>';

        $_tabContents .= $this->RenderTabHeader($this->Language->Get('tabrecentactivity') . $_showingSuffix, '/Base/ActivityLog/Manage');

        $_tabContents .= '<div class="dashboardtabdatacontainer">';

        if (!_is_array($_activityContainer[1])) {
            $_tabContents .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            foreach ($_activityContainer[1] as $_val) {
                $_tabContents .= $this->RenderInfo($_val['title'], $_val['contents'], $_val['date']);
            }
        }

        $_tabContents .= '</div>';
        $_tabContents .= '</div>';

        return $_tabContents;
    }

    /**
     * Render the Login Failure Tab
     *
     * @author Varun Shoor
     * @param array $_failureContainer The Login Failure Container
     * @return string The Tab Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderLoginFailureTab($_failureContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_showingSuffix = '';
        if (count($_failureContainer[1])) {
            $_showingSuffix = sprintf($this->Language->Get('showingmsg'), count($_failureContainer[1]), $_failureContainer[0]);
        }

        $_tabContents = '<div>';

        $_tabContents .= $this->RenderTabHeader($this->Language->Get('tabloginfailures') . $_showingSuffix, '/Base/LoginLog/Manage');

        $_tabContents .= '<div class="dashboardtabdatacontainer">';

        if (!_is_array($_failureContainer[1])) {
            $_tabContents .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            foreach ($_failureContainer[1] as $_key => $_val) {
                $_tabContents .= $this->RenderError($_val['title'], $_val['contents'], $_val['date'], false);
            }
        }

        $_tabContents .= '</div>';
        $_tabContents .= '</div>';

        return $_tabContents;
    }

    /**
     * Render the Error Log Tab
     *
     * @author Varun Shoor
     * @param array $_errorContainer The Error Container
     * @return string The Tab Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderErrorLogTab($_errorContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_showingSuffix = '';
        if (count($_errorContainer[1])) {
            $_showingSuffix = sprintf($this->Language->Get('showingmsg'), count($_errorContainer[1]), $_errorContainer[0]);
        }

        $_tabContents = '<div>';

        $_tabContents .= $this->RenderTabHeader($this->Language->Get('taberrorlog') . $_showingSuffix, '/Base/ErrorLog/Manage');

        $_tabContents .= '<div class="dashboardtabdatacontainer">';

        if (!_is_array($_errorContainer[1])) {
            $_tabContents .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            foreach ($_errorContainer[1] as $_key => $_val) {
                $_tabContents .= $this->RenderError($_val['title'], $_val['contents'], $_val['date']);
            }
        }

        $_tabContents .= '</div>';
        $_tabContents .= '</div>';

        return $_tabContents;
    }

    /**
     * Render the Parser Log Tab
     *
     * @author Varun Shoor
     * @param array $_parserLogContainer The Parser Log Container
     * @return string The Tab Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderParserLogTab($_parserLogContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_showingSuffix = '';
        if (count($_parserLogContainer[1])) {
            $_showingSuffix = sprintf($this->Language->Get('showingmsg'), count($_parserLogContainer[1]), $_parserLogContainer[0]);
        }

        $_tabContents = '<div>';

        $_tabContents .= $this->RenderTabHeader($this->Language->Get('tabparserlog') . $_showingSuffix, '/Parser/ParserLog/Manage');

        $_tabContents .= '<div class="dashboardtabdatacontainer">';

        if (!_is_array($_parserLogContainer[1])) {
            $_tabContents .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            foreach ($_parserLogContainer[1] as $_key => $_val) {
                $_tabContents .= $this->RenderError($_val['title'], $_val['contents'], $_val['date'], false);
            }
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
     * @param string $_extendedLink (OPTIONAL) The Extended Link to View More Info
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTabHeader($_headerTitle, $_extendedLink = null)
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

?>
