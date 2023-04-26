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

namespace Base\Intranet;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_View;

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
        } else if (empty($_title) || empty($_contents)) {
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
        } else if (empty($_title) || empty($_contents)) {
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
        } else if (empty($_title) || empty($_contents)) {
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
            $_lastVisit = $this->Language->Get('na');
        } else {
            $_lastVisit = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT->Staff->GetProperty('lastvisit'));
        }

        echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dashboardlayoutborder" style="margin-top: 0;">
        <tr>
        <td align="left" valign="bottom" id="dashboardcontainer">
        <div id="dashboardtitle">' . $this->Language->Get('dashdashboard') . '</div>
        </td>
        </tr>
        <tr><td align="left" valign="top">

        <div style="PADDING: 8px;">

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
        <td align="left" valign="top" width="300">

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
                echo '<li><a href="#dashboardtabs-' . $_val[Controller_Home::TAB_NAME] . '"><img src="' . SWIFT::Get('themepath') . 'images/' . $_val[Controller_Home::TAB_ICON] . '' . '" align="absmiddle" border="0" /> ' . $_val[Controller_Home::TAB_TITLE] . '</a></li>';

                $_tabHTML .= '<div id="dashboardtabs-' . $_val[Controller_Home::TAB_NAME] . '"><div class="ui-tabs-vertical-custom-prop"></div>';

                $_tabHTML .= $_val[Controller_Home::TAB_CONTENTS];

                $_tabHTML .= '<div class="ui-tabs-vertical-custom-clear"></div></div>';
            }
        }

        echo '</ul>';

        echo $_tabHTML;

        echo '</div>
        </td>



        <td align="left" valign="top" width="200">
<div class="tabparent">
    <ul class="tab">
        <li>
            <a href="javascript:void(0);" class="currenttab">
            <span>
            <div style="background: transparent url(' . SWIFT::Get('themepath') . 'images/icon_overview.png) no-repeat scroll 0%; padding-left: 20px; height: 16px; float: left;">' . $this->Language->Get('overview') . '</div>
            </span>
            </a>
        </li>
    </ul>
<div class="basictabcontent">
<table width="300" cellspacing="1" cellpadding="4" border="0">
<tbody>
<tr>
<td class="gridrow2" valign="top" align="left">&nbsp;</td>
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
            $("#dashboardtabs").tabs().addClass(\'ui-tabs-vertical ui-helper-clearfix\').removeClass(\'ui-corner-all ui-widget-content\');
            $("#dashboardtabs li").removeClass(\'ui-corner-top\').addClass(\'ui-corner-left\');
        });

        $(function() {
            ClearFunctionQueue();
        });
        </script>

        </div></td></tr></table>';
    }
}

?>
