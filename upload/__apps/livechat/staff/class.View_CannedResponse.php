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

namespace LiveChat\Staff;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use LiveChat\Library\Canned\SWIFT_CannedManager;
use LiveChat\Models\Canned\SWIFT_CannedResponse;
use SWIFT;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Canned Response View
 *
 * @author Varun Shoor
 */
class View_CannedResponse extends SWIFT_View
{
    /**
     * Render the Canned Response Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_CannedResponse|null $_SWIFT_CannedResponseObject The SWIFT_CannedResponse Object Pointer (Only for EDIT Mode)
     * @param bool $_selectedCannedCategoryIDArg
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_CannedResponse $_SWIFT_CannedResponseObject = null, $_selectedCannedCategoryIDArg = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/CannedResponse/EditSubmit/' . $_SWIFT_CannedResponseObject->GetCannedResponseID(), SWIFT_UserInterface::MODE_EDIT, true, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/LiveChat/CannedResponse/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true, true);
        }

        $_cannedResponseTitle = '';
        $_selectedCannedCategoryID = 0;

        if (!empty($_selectedCannedCategoryIDArg)) {
            $_selectedCannedCategoryID = (int)($_selectedCannedCategoryIDArg);
        }

        $_cannedResponseURLData = '';
        $_cannedResponseImageData = '';
        $_cannedResponseContents = '';
        $_cannedResponseType = SWIFT_CannedResponse::TYPE_MESSAGE;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_CannedResponseObject !== null) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/LiveChat/CannedCategory/DeleteResponse/' . $_SWIFT_CannedResponseObject->GetCannedResponseID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatcanned'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_cannedResponseTitle = $_SWIFT_CannedResponseObject->GetProperty('title');
            $_cannedResponseURLData = $_SWIFT_CannedResponseObject->GetProperty('urldata');
            $_cannedResponseImageData = $_SWIFT_CannedResponseObject->GetProperty('imagedata');
            $_cannedResponseContents = $_SWIFT_CannedResponseObject->GetProperty('contents');
            $_cannedResponseType = (int)($_SWIFT_CannedResponseObject->GetProperty('responsetype'));

            $_selectedCannedCategoryID = (int)($_SWIFT_CannedResponseObject->GetProperty('cannedcategoryid'));
        } else {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('chatcanned'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('cannedresponsetitle'), $this->Language->Get('desc_cannedresponsetitle'), $_cannedResponseTitle);

        $_GeneralTabObject->Select('cannedcategoryid', $this->Language->Get('parentcategoryresp'), $this->Language->Get('desc_parentcategoryresp'), SWIFT_CannedManager::GetCannedCategoryOptions($_selectedCannedCategoryID));

        $_GeneralTabObject->Title($this->Language->Get('responseactions'), 'icon_doublearrows.gif');

        $_GeneralTabObject->Text('urldata', '<input type="checkbox" name="urldataenabled" id="urldataenabled" value="1"' . IIF(!empty($_cannedResponseURLData), ' checked') . ' /> <label for="urldataenabled">' . $this->Language->Get('crpushurl') . '</label>', $this->Language->Get('desc_crpushurl'), $_cannedResponseURLData);

        $_GeneralTabObject->URLAndUpload('imagedata', '<input type="checkbox" name="imagedataenabled" id="imagedataenabled" value="1"' . IIF(!empty($_cannedResponseImageData), ' checked') . ' /> <label for="imagedataenabled">' . $this->Language->Get('crpushimage') . '</label>', $this->Language->Get('desc_crpushimage'), $_cannedResponseImageData, IIF(empty($_cannedResponseImageData), true, false));

        $_radioContainer = array();
        $_radioContainer[0]['title'] = $this->Language->Get('crtext');
        $_radioContainer[0]['value'] = SWIFT_CannedResponse::TYPE_MESSAGE;
        $_radioContainer[0]['checked'] = IIF($_cannedResponseType == SWIFT_CannedResponse::TYPE_MESSAGE || $_cannedResponseType == SWIFT_CannedResponse::TYPE_NONE, true, false);

        $_radioContainer[1]['title'] = $this->Language->Get('crcode');
        $_radioContainer[1]['value'] = SWIFT_CannedResponse::TYPE_CODE;
        $_radioContainer[1]['checked'] = IIF($_cannedResponseType == SWIFT_CannedResponse::TYPE_CODE, true, false);
        $_GeneralTabObject->Radio('responsetype', '<input type="checkbox" name="responsetypeenabled" id="responsetypeenabled" value="1"' . IIF(!empty($_cannedResponseContents) || $_mode == SWIFT_UserInterface::MODE_INSERT, ' checked') . ' /> <label for="responsetypeenabled">' . $this->Language->Get('crsendmessage') . '</label>', $this->Language->Get('desc_crsendmessage'), $_radioContainer, false);

        $_GeneralTabObject->TextArea('responsecontents', '', '', $_cannedResponseContents, 30, 6, IIF(empty($_cannedResponseContents) && $_mode != SWIFT_UserInterface::MODE_INSERT, true, false));

        $_GeneralTabObject->AppendHTML('<script language="Javascript" type="text/javascript">QueueFunction(function(){ SyncResponseWindow(); });</script>');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}
