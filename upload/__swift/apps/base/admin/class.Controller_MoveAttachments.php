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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_Exception;

/**
 * The Move Attachments Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_MoveAttachments $View
 * @author Varun Shoor
 */
class Controller_MoveAttachments extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    const ATTACHMENT_FILESTODB = 'filestodb';
    const ATTACHMENT_DBTOFILES = 'dbtofiles';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('MIME:MIMEList');

        $this->Language->Load('admin_maintenance');
        $this->Language->Load('admin_tmaintenance');
    }

    /**
     * Render the Maintenance Tabs
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('maintenance') . ' > ' . $this->Language->Get('moveattachments'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanrunmoveattachments') == '0' || (defined('ENFORCEATTACHMENTS_INFILES') && ENFORCEATTACHMENTS_INFILES == true)) {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Move the Attachments
     *
     * @author Varun Shoor
     * @param mixed $_moveType The Move Type
     * @param int $_attachmentsPerPass Number of Attachments to Process in a Single Pass
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Move($_moveType, $_attachmentsPerPass, $_totalAttachments = 0, $_startTime = 0, $_processCount = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_attachmentsPerPass) || $_attachmentsPerPass <= 0) {
            $_attachmentsPerPass = 20;
        }

        $_moveFrom = SWIFT_Attachment::TYPE_DATABASE;
        if ($_moveType == self::ATTACHMENT_FILESTODB) {
            $_moveFrom = SWIFT_Attachment::TYPE_FILE;
        }

        if (is_numeric($_totalAttachments)) {
            $_totalAttachments = (int)($_totalAttachments);
        } else {
            $_totalAttachments = 0;
        }

        if (empty($_totalAttachments)) {
            $_totalAttachments = SWIFT_Attachment::GetAttachmentCount($_moveFrom);
            $_startTime = DATENOW;
        } else {
            $_startTime = (int)($_startTime);
        }

        $_processCount = (int)($_processCount);
        if (empty($_processCount)) {
            $_processCount = 0;
        }

        $_attachmentContainer = array();
        // First get all the attachments under the given type limited to the number of attachments per pass
        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE attachmenttype = '" . $_moveFrom . "' ORDER BY attachmentid ASC", $_attachmentsPerPass, 0);
        while ($this->Database->NextRecord()) {
            $_attachmentContainer[$this->Database->Record['attachmentid']] = $this->Database->Record;
        }

        foreach ($_attachmentContainer as $_key => $_val) {
            $_SWIFT_AttachmentObject = new SWIFT_Attachment($_key);
            if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
                continue;
            }

            // Move from Files => Database
            if ($_moveFrom == SWIFT_Attachment::TYPE_FILE) {
                $_SWIFT_AttachmentObject->MoveToDatabase();

                // Move from Database => Files
            } elseif ($_moveFrom == SWIFT_Attachment::TYPE_DATABASE) {
                $_SWIFT_AttachmentObject->MoveToFile();
            }

            $_processCount++;
        }

        $_percent = 100;
        if ($_totalAttachments) {
            $_percent = floor($_processCount * (100 / $_totalAttachments));
        }

        $_attachmentRemaining = $_totalAttachments - $_processCount;
        $_averagePostTime = 0;
        if ($_processCount) {
            $_averagePostTime = (DATENOW - $_startTime) / $_processCount;
        }

        $_timeRemaining = $_attachmentRemaining * $_averagePostTime;

        $_redirectURL = false;
        if ($_percent < 100 && $_processCount < $_totalAttachments) {
            $_redirectURL = SWIFT::Get('basename') . '/Base/MoveAttachments/Move/' . $_moveType . '/' . $_attachmentsPerPass . '/' . $_totalAttachments . '/' . $_startTime . '/' . $_processCount;
        }

        $this->View->RenderMoveAttachmentData($_percent, $_redirectURL, $_processCount, $_totalAttachments, $_startTime, $_timeRemaining);

        return true;
    }
}

?>
