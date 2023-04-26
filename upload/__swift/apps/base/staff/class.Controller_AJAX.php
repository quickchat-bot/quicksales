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

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT_Exception;
use Tickets\Library\View\SWIFT_TicketViewRenderer;

/**
 * The AJAX Controller
 *
 * @author Varun Shoor
 */
class Controller_AJAX extends Controller_staff
{
    /**
     * Dispatch Online Staff JSON
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function OnlineStaff()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->ProcessOnlineStaff();
        $_onlineStaffContainer = $this->UserInterface->GetOnlineStaffContainer();

        $_onlineStaffHTML = SWIFT_UserInterfaceControlPanel::RenderOnlineStaff();

        header('Content-Type: text/plain');

        echo '{' . SWIFT_CRLF;
        echo '"onlineusershtml": "' . addslashes($_onlineStaffHTML) . '",' . SWIFT_CRLF;
        echo '"onlineusersarray": {' . SWIFT_CRLF;

        $_index = 1;
        $_jsonStaffContainer = [];
        foreach ($_onlineStaffContainer as $_key => $_val) {
            $_jsonStaffContainer[] = '"' . $_val['staffid'] . '"' . ': {"fullname": "' . $_val['fullname'] . '"}';
            $_index++;
        }

        echo implode(', ', $_jsonStaffContainer);

        echo '} }';

        return true;
    }

    /**
     * Fetches ticket overview stats
     *
     * @author Werner Garcia
     * @return bool
     * @throws SWIFT_Exception
     */
    public function OverviewTabContent()
    {
        $this->Language->Load('dashboard');

        $_departmentProgressContainer = SWIFT_TicketViewRenderer::GetDashboardDepartmentProgress();
        $_ownerProgressContainer = SWIFT_TicketViewRenderer::GetDashboardOwnerProgress();
        $_statusProgressContainer = SWIFT_TicketViewRenderer::GetDashboardStatusProgress();
        $_typeProgressContainer = SWIFT_TicketViewRenderer::GetDashboardTypeProgress();
        $_priorityProgressContainer = SWIFT_TicketViewRenderer::GetDashboardPriorityProgress();

        $_showTab = count($_departmentProgressContainer) || count($_ownerProgressContainer) ||
            count($_statusProgressContainer) || count($_typeProgressContainer) ||
            count($_priorityProgressContainer);

        if ($_showTab) {
            echo '<div class="ui-tabs ui-widget ui-tabs-hide">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
        <a href="javascript:void(0);" class="currenttab">
        <span>
        <div class="dashboardoverviewtab">' . $this->Language->Get('overview') . '</div>
        </span>
        </a>
    </li>
</ul>
<div class="basictabcontent stats">
<table width="100%" cellspacing="1" cellpadding="4" border="0">
<tbody>
<tr>
<td class="gridrow2 stats" valign="top" align="left">';
            if (count($_departmentProgressContainer)) {
                echo '<div class="dashboardprogresscontainer">';
                echo '<table class="hlineheader"><tr><th rowspan="2" nowrap>' . $this->Language->Get('dashdepartmentprogress') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>';
                echo $this->RenderProgress($_departmentProgressContainer);
                echo '</div>';
            }

            if (count($_statusProgressContainer)) {
                echo '<div class="dashboardprogresscontainer">';
                echo '<table class="hlineheader"><tr><th rowspan="2" nowrap>' . $this->Language->Get('dashstatusprogress') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>';
                echo $this->RenderProgress($_statusProgressContainer, true);
                echo '</div>';
            }

            if (count($_typeProgressContainer)) {
                echo '<div class="dashboardprogresscontainer">';
                echo '<table class="hlineheader"><tr><th rowspan="2" nowrap>' . $this->Language->Get('dashtypeprogress') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>';
                echo $this->RenderProgress($_typeProgressContainer, true);
                echo '</div>';
            }

            if (count($_priorityProgressContainer)) {
                echo '<div class="dashboardprogresscontainer">';
                echo '<table class="hlineheader"><tr><th rowspan="2" nowrap>' . $this->Language->Get('dashpriorityprogress') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>';
                echo $this->RenderProgress($_priorityProgressContainer, true);
                echo '</div>';
            }

            if (count($_ownerProgressContainer)) {
                echo '<div class="dashboardprogresscontainer">';
                echo '<table class="hlineheader"><tr><th rowspan="2" nowrap>' . $this->Language->Get('dashownerprogress') . '</th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>';
                echo $this->RenderProgress($_ownerProgressContainer);
                echo '</div>';
            }

            echo '</td></tr>
            </tbody>
            </table>
            </div></div>';
        }

        return true;
    }

    /**
     * Render the Progress Bars
     *
     * @author Varun Shoor
     * @param array $_progressContainer The Progress Container
     * @param bool $_fixedColor (OPTIONAL) Whether to use a fixed color
     * @return string The Rendered HTML
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderProgress($_progressContainer, $_fixedColor = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_widthCeil = 150;

        $_color90 = '#cc1111';
        $_color80 = '#ad1010';
        $_color70 = '#950d0d';
        $_color60 = '#085386';
        $_color50 = '#085386';
        $_color40 = '#085d97';
        $_color30 = '#0b6eb3';
        $_color20 = '#457506';
        $_color10 = '#508806';
        $_color0 = '#5da105';


        $_totalItems = 0;
        foreach ($_progressContainer as $_progressItem) {
            $_totalItems += $_progressItem['count'];
        }

        $_renderHTML = '';
        foreach ($_progressContainer as $_progressItem) {
            if (empty($_progressItem['count'])) {
                continue;
            }

            $_progressItem['percentage'] = ($_progressItem['count'] / $_totalItems) * 100;

            $_progressItem['width'] = ($_progressItem['percentage'] * $_widthCeil) / 100;

            if ($_fixedColor && !isset($_progressItem['color'])) {
                $_progressItem['color'] = $_color40;
            } else {
                if (!isset($_progressItem['color'])) {
                    if ($_progressItem['percentage'] >= 90) {
                        $_progressItem['color'] = $_color90;
                    } else {
                        if ($_progressItem['percentage'] >= 80 && $_progressItem['percentage'] < 90) {
                            $_progressItem['color'] = $_color80;
                        } else {
                            if ($_progressItem['percentage'] >= 70 && $_progressItem['percentage'] < 80) {
                                $_progressItem['color'] = $_color70;
                            } else {
                                if ($_progressItem['percentage'] >= 60 && $_progressItem['percentage'] < 70) {
                                    $_progressItem['color'] = $_color60;
                                } else {
                                    if ($_progressItem['percentage'] >= 50 && $_progressItem['percentage'] < 60) {
                                        $_progressItem['color'] = $_color50;
                                    } else {
                                        if ($_progressItem['percentage'] >= 40 && $_progressItem['percentage'] < 50) {
                                            $_progressItem['color'] = $_color40;
                                        } else {
                                            if ($_progressItem['percentage'] >= 30 && $_progressItem['percentage'] < 40) {
                                                $_progressItem['color'] = $_color30;
                                            } else {
                                                if ($_progressItem['percentage'] >= 20 && $_progressItem['percentage'] < 30) {
                                                    $_progressItem['color'] = $_color20;
                                                } else {
                                                    if ($_progressItem['percentage'] >= 10 && $_progressItem['percentage'] < 20) {
                                                        $_progressItem['color'] = $_color10;
                                                    } else {
                                                        $_progressItem['color'] = $_color0;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $_finalWidth = round($_progressItem['width']);

            $_progressPercentage = round($_progressItem['percentage'], 0);
            if ($_progressItem['percentage'] < 0) {
                $_progressPercentage = round($_progressItem['percentage'], 2);
            }

            $_renderHTML .= '<div class="dashboardprogress"' . IIF(!empty($_progressItem['link']),
                    ' onclick="javascript: loadViewportData(\'' . $_progressItem['link'] . '\');"') . '>
            <div class="dashboardprogresstitle">' . StripName(text_to_html_entities($_progressItem['title']), 30) . '</div>
            <div style="float: right;">
            <div class="dashboardprogressperc">' . $_progressPercentage . '%</div>
            <div class="dashboardprogressbarparent"><div class="dashboardprogressbar" style="margin-right: ' . round($_widthCeil - $_finalWidth) . 'px; width: ' . $_finalWidth . 'px; background: ' . $_progressItem['color'] . ';"></div></div>
            </div>
            <div class="dashboardprogresscount">' . number_format((int)($_progressItem['count']), 0) . '</div>
            </div>';
        }

        return $_renderHTML;
    }
}
