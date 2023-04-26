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
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Move Attachments View Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_MoveAttachments extends SWIFT_View
{
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
     * Render the Maintenance Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/MoveAttachments/Index', SWIFT_UserInterface::MODE_INSERT, false, false, true);

        /*
         * ###############################################
         * BEGIN MOVE ATTACHMENTS TAB
         * ###############################################
         */

        $_AttachmentsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabmoveattachments'), 'icon_file.gif', 'moveattachments', true);

        $_AttachmentsTabObject->LoadToolbar();
        $_AttachmentsTabObject->Toolbar->AddButton($this->Language->Get('move'), 'fa-check-circle', 'startMoveAttachments(); ', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_AttachmentsTabObject->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('moveattachments'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = sprintf($this->Language->Get('filestodb'), SWIFT_Attachment::GetAttachmentCount(SWIFT_Attachment::TYPE_FILE));
        $_optionsContainer[0]['value'] = Controller_MoveAttachments::ATTACHMENT_FILESTODB;
        $_optionsContainer[0]['selected'] = true;
        $_optionsContainer[1]['title'] = sprintf($this->Language->Get('dbtofiles'), SWIFT_Attachment::GetAttachmentCount(SWIFT_Attachment::TYPE_DATABASE));
        $_optionsContainer[1]['value'] = Controller_MoveAttachments::ATTACHMENT_DBTOFILES;
        $_optionsContainer[1]['selected'] = false;

        $_AttachmentsTabObject->Select('movetype', $this->Language->Get('movetype'), $this->Language->Get('desc_movetype'), $_optionsContainer);

        $_AttachmentsTabObject->Number('attachmentsperpass', $this->Language->Get('attachmentsperpass'), $this->Language->Get('desc_attachmentsperpass'), '20');

        $_AttachmentsTabObject->AppendHTML('<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="moveattachmentsparent"></div></td></tr>');

        /*
         * ###############################################
         * END MOVE ATTACHMENTS TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Move Attachment Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderMoveAttachmentData($_percent, $_redirectURL, $_processCount, $_totalAttachments, $_startTime, $_timeRemaining)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_percent > 100) {
            $_percent = 100;
        }

        if ($_processCount > $_totalAttachments) {
            $_processCount = $_totalAttachments;
        }

        echo '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="gridlayoutborder">';
        echo '<tbody><tr><td class="gridcontentborder">';
        echo '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
        echo '<tr><td align="center" valign="top" class="rowhighlight" colspan="4" nowrap>';
        echo '<div class="bigtext">' . number_format($_percent, 2) . '%</div>' . IIF(!empty($_redirectURL), '<br /><img src="' . SWIFT::Get('themepath') . 'images/barloadingdark.gif" align="absmiddle" border="0" />');
        echo '</td></tr>';

        echo '<tr><td colspan="4" align="left" valign="top" class="settabletitlerowmain2">' . $this->Language->Get('generalinformation') . '</td></tr>';

        echo '<tr><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('attachmentsprocessed') . '</td><td align="left" valign="top" class="gridrow2">' . number_format((int)($_processCount), 0) . '</td><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('totalattachments') . '</td><td align="left" valign="top" class="gridrow2">' . number_format((int)($_totalAttachments), 0) . '</td></tr>';

        echo '<tr><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('timeelapsed') . '</td><td align="left" valign="top" class="gridrow2">' . SWIFT_Date::ColorTime(DATENOW - $_startTime) . '</td><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('timeremaining') . '</td><td align="left" valign="top" class="gridrow2">' . SWIFT_Date::ColorTime($_timeRemaining, true) . '</td></tr>';

        echo '</table></td></tr></tbody></table>';

        if (!empty($_redirectURL)) {
            echo '<script type="text/javascript">function nextIndexStepAttachments() { $("#moveattachmentsparent").load("' . $_redirectURL . '");} setTimeout("nextIndexStepAttachments();", 1000);</script>';
        } else {
            echo '<script type="text/javascript">RemoveActiveSWIFTAction("moveattachments"); ChangeTabLoading(\'View_Maintenanceform\', \'moveattachments\', \'icon_file.gif\')</script>';
        }
    }
}

?>
