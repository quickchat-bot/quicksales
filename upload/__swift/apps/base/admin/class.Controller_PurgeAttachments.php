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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Purge Attachments Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \Base\Library\Attachment\SWIFT_AttachmentRule $AttachmentRule
 * @property View_PurgeAttachments $View
 * @property \SWIFT_MIMEList $MIMEList
 * @author Varun Shoor
 */
class Controller_PurgeAttachments extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Attachment:AttachmentRule', [], true, false, 'base');
        $this->Load->Library('MIME:MIMEList');

        $this->Language->Load('admin_maintenance');
        $this->Language->Load('admin_purgeattachments');
    }

    /**
     * Displays the Purge Attachments Rule Form
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

        $this->UserInterface->Header($this->Language->Get('maintenance') . ' > ' . $this->Language->Get('purgeattachments'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanpurgeattachments') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderRuleForm();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the Attachment Rules
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessRule()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $typeOptions = $this->Input->Post('typeoptions');
        if ($typeOptions != 0 && !(($typeOptions == SWIFT_Attachment::LINKTYPE_TICKETPOST || $typeOptions == SWIFT_Attachment::LINKTYPE_KBARTICLE))) {
            $this->UserInterface->Error($this->Language->Get('titlenotype'), $this->Language->Get('msgnotype'));

            $this->Load->Index();

            return false;
        } elseif (!isset($_POST['rulecriteria']) || !count($_POST['rulecriteria'])) {
            $this->UserInterface->Error($this->Language->Get('titlenocriteria'), $this->Language->Get('msgnocriteria'));

            $this->Load->Index();

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Index();

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_tcanpurgeattachments') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Index();

            return false;
        }

        $_finalText = $this->_GetRenderedConfirmation();

        $_attachmentIDList = $this->AttachmentRule->GetAttachmentIDList($this->Input->Post('typeoptions'), $_POST['rulecriteria'], $_POST['ruleoptions']);

        SWIFT::Info(sprintf($this->Language->Get('titlesearchresult'), count($_attachmentIDList)), sprintf($this->Language->Get('msgsearchresult'), count($_attachmentIDList)) . '<br />' . $_finalText);

        $_attachmentFileContainer = array();

        if (count($_attachmentIDList)) {
            $_ticketIDList = $_attachmentTicketMap = array();

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE attachmentid IN (" . BuildIN($_attachmentIDList) . ")");
            while ($this->Database->NextRecord()) {
                $_attachmentFileContainer[$this->Database->Record['attachmentid']] = $this->Database->Record;
                $_attachmentFileContainer[$this->Database->Record['attachmentid']]['ticketmaskid'] = '';
                $_attachmentFileContainer[$this->Database->Record['attachmentid']]['subject'] = '';
                $_attachmentTicketMap[$this->Database->Record['ticketid']][] = $this->Database->Record['attachmentid'];

                if (!empty($this->Database->Record['ticketid'])) {
                    if (!in_array($this->Database->Record['ticketid'], $_ticketIDList)) {
                        $_ticketIDList[] = $this->Database->Record['ticketid'];
                    }
                }
            }

            if (SWIFT_App::IsInstalled(APP_TICKETS)) {
                $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
                while ($this->Database->NextRecord()) {
                    if (isset($_attachmentTicketMap[$this->Database->Record['ticketid']]) && count($_attachmentTicketMap[$this->Database->Record['ticketid']])) {
                        foreach ($_attachmentTicketMap[$this->Database->Record['ticketid']] as $_key => $_val) {
                            $_attachmentFileContainer[$_val]['ticketmaskid'] = $this->Database->Record['ticketmaskid'];
                            $_attachmentFileContainer[$_val]['subject'] = $this->Database->Record['subject'];
                        }
                    }
                }
            }
        }


        $this->UserInterface->Header($this->Language->Get('maintenance') . ' > ' . $this->Language->Get('purgeattachments'), self::MENU_ID, self::NAVIGATION_ID);

        $this->View->RenderRuleConfirmationList($_attachmentFileContainer);

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Delete the specified attachment id's
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Index();

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcanpurgeattachments') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Index();

            return false;
        }

        if (isset($_POST['itemid']) && _is_array($_POST['itemid'])) {
            $_deletedAttachmentFilenameList = SWIFT_Attachment::DeleteList($_POST['itemid']);

            if (_is_array($_deletedAttachmentFilenameList)) {
                $_finalText = $this->_GetRenderedConfirmation();

                SWIFT::Info(sprintf($this->Language->Get('titledelconfirm'), count($_deletedAttachmentFilenameList)), $this->Language->Get('msgdelconfirm') . '<br>' . implode(', ', $_deletedAttachmentFilenameList) . '<br /><br /><b>' . $this->Language->Get('criteria') . '</b><br />' . $_finalText);

                foreach ($_deletedAttachmentFilenameList as $_key => $_val) {
                    SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitypurgeattachment'), htmlspecialchars($_val)), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_GENERAL, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }
            }
        }

        $this->Load->Index();

        return true;
    }

    /**
     * Retrieves the Rendered Confirmation
     *
     * @author Varun Shoor
     * @return mixed "_finalText" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetRenderedConfirmation()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_criteriaPointer = $this->AttachmentRule->GetCriteriaPointer();

        $_index = 1;
        $_finalText = '';

        foreach ($_POST['rulecriteria'] as $_key => $_val) {
            $_finalText .= '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif' . '" border="0" align="absmiddle" /> ' . IIF($_index != 1, '<b>' . IIF($_POST['ruleoptions'] == SWIFT_Rules::RULE_MATCHALL, $this->Language->Get('and'), $this->Language->Get('or')) . '</b> ') . $this->Language->Get('if') . ' <b>"' . $this->Language->Get('pa' . $_val[0]) . '"</b> ' . SWIFT_Rules::GetOperText($_val[1]) . ' <b>"';

            $_extendedName = '';

            if (isset($_criteriaPointer[$_val[0]]) && isset($_criteriaPointer[$_val[0]]['fieldcontents']) && _is_array($_criteriaPointer[$_val[0]]['fieldcontents']) && $_criteriaPointer[$_val[0]]['field'] == 'custom') {
                foreach ($_criteriaPointer[$_val[0]]['fieldcontents'] as $_subKey => $_subVal) {
                    if ($_val['contents'] == $_val[0]) {
                        $_extendedName = $_val['title'];
                        break;
                    }
                }
            }

            $_finalText .= htmlspecialchars(IIF(!empty($_extendedName), $_extendedName, $_val[2])) . '"</b><br>';
            $_index++;
        }

        return $_finalText;
    }
}

?>
