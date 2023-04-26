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

namespace Knowledgebase\Admin;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The KB Articles Maintenance View Management Class
 *
 * @author Mahesh Salaria
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 */
class View_Maintenance extends SWIFT_View
{
    /**
     * Render the Maintenance Form
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this),'/Knowledgebase/Maintenance/Index', SWIFT_UserInterface::MODE_INSERT, false, false, true);

        /*
         * ###############################################
         * BEGIN REBUILD INDEX TAB
         * ###############################################
         */

        $_IndexTabObject = $this->UserInterface->AddTab($this->Language->Get('tabkbindex'), 'icon_form.gif', 'general', true);

        $_IndexTabObject->LoadToolbar();
        $_IndexTabObject->Toolbar->AddButton($this->Language->Get('rebuild'), 'fa-check-circle', 'startKnowledgebaseMaintenance(); ', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_IndexTabObject->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketsmaintenance'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_IndexTabObject->Number('articlesperpass', $this->Language->Get('articlesperpass'), $this->Language->Get('desc_articlesperpass'), '100');

        $_IndexTabObject->AppendHTML('<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="searchindexparent"></div></td></tr>');

        /*
         * ###############################################
         * END REBUILD INDEX TAB
         * ###############################################
         */


        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the ReIndex Data
     *
     * @author Mahesh Salaria
     * @param int $_percent
     * @param string $_redirectURL
     * @param int $_processCount
     * @param int $_totalArticles
     * @param int $_startTime
     * @param int $_timeRemaining
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderReIndexData($_percent, $_redirectURL, $_processCount, $_totalArticles, $_startTime, $_timeRemaining)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="gridlayoutborder">';
        echo '<tbody><tr><td class="gridcontentborder">';
        echo '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
        echo '<tr><td align="center" valign="top" class="rowhighlight" colspan="4" nowrap>';

        $_style = '';

        if (100 == $_percent)
        {
            $_style = 'color: #00c34e; font-size: 13pt;';
        }

        echo '<div class="bigtext" style="' . $_style . '">' . number_format($_percent, 2) . '%</div>';

        echo IIF(!empty($_redirectURL), '<br /><img src="' . SWIFT::Get('themepath') . 'images/barloadingdark.gif" align="absmiddle" border="0" />');

        echo '</td></tr>';

        echo '<tr><td colspan="4" align="left" valign="top" class="settabletitlerowmain2">'. $this->Language->Get('reindexheader') . '</td></tr>';

        echo '<tr><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('articlesprocesed') . '</td><td align="left" valign="top" class="gridrow2">' . number_format(($_processCount), 0) . '</td><td align="left" valign="top" class="gridrow1">'. $this->Language->Get('totalreplies') . '</td><td align="left" valign="top" class="gridrow2">' . number_format(($_totalArticles), 0) . '</td></tr>';

        echo '<tr><td align="left" valign="top" class="gridrow1">'. $this->Language->Get('timeelapsed') . '</td><td id="time_elapsed" align="left" valign="top" class="gridrow2">' . SWIFT_Date::ColorTime(DATENOW-$_startTime) . '</td><td align="left" valign="top" class="gridrow1">'. $this->Language->Get('timeremaining') . '</td><td align="left" valign="top" class="gridrow2">'. SWIFT_Date::ColorTime($_timeRemaining, true) . '</td></tr>';

        echo '</table></td></tr></tbody></table>';

        if (!empty($_redirectURL))
        {
            echo '<script type="text/javascript">function nextIndexStepTicket() { $("#searchindexparent").load("' . $_redirectURL . '");} setTimeout("nextIndexStepTicket();", 1000);</script>';
        } else {
            echo '<script type="text/javascript">RemoveActiveSWIFTAction("searchreindex"); ChangeTabLoading(\'View_Maintenanceform\', \'general\', \'icon_form.gif\')</script>';
        }
    }
}
