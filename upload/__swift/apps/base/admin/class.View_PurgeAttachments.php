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
use Base\Library\Attachment\SWIFT_AttachmentRule;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_MIME_Exception;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Purge Attachments View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_PurgeAttachments $Controller
 * @author Varun Shoor
 */
class View_PurgeAttachments extends SWIFT_View
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
     * Processes the submitted rule into a list of attachments
     *
     * @author Varun Shoor
     * @param array $_attachmentFileContainer The Attachment File Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderRuleConfirmationList($_attachmentFileContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $this->UserInterface->Start(get_short_class($this), '/Base/PurgeAttachments/Delete', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '', SWIFT_UserInterfaceToolbar::LINK_SUBMITCONFIRM, '', '', false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('purgeattachments'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_columnContainer = array();
        $_columnContainer[0]['width'] = '20';
        $_columnContainer[0]['align'] = 'center';
        $_columnContainer[0]['valign'] = 'middle';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = '<input type="checkbox" name="allselect" class="swiftcheckbox" onClick="javascript:toggleAll(\'\', \'View_PurgeAttachments\');">';
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['nowrap'] = true;
        $_columnContainer[1]['value'] = $this->Language->Get('filename');
        $_columnContainer[2]['align'] = 'center';
        $_columnContainer[2]['nowrap'] = true;
        $_columnContainer[2]['width'] = '150';
        $_columnContainer[2]['value'] = $this->Language->Get('attachmentid');
        $_columnContainer[3]['align'] = 'center';
        $_columnContainer[3]['nowrap'] = true;
        $_columnContainer[3]['width'] = '150';
        $_columnContainer[3]['value'] = $this->Language->Get('filesize');

        $_GeneralTabObject->Row($_columnContainer, 'gridtabletitlerow');

        $_index = 1;
        foreach ($_attachmentFileContainer as $_key => $_val) {
            $_fileExtension = mb_substr($_val['filename'], mb_strrpos($_val['filename'], '.') + 1);

            $_mimeDataContainer = false;

            try {
                $_mimeDataContainer = $this->Controller->MIMEList->Get($_fileExtension);
            } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                // Reserved
            }

            if ($_mimeDataContainer && isset($_mimeDataContainer[1])) {
                $_icon = $_mimeDataContainer[1];
            } else {
                $_icon = 'mimeico_blank.gif';
            }

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'center';
            $_columnContainer[0]['valign'] = 'middle';
            $_columnContainer[0]['value'] = '<input type="checkbox" name="itemid[]" value="' . (int)($_key) . '" class="swiftcheckbox" >';
            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[1]['value'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_icon . '" align="absmiddle" border="0" /> ' . htmlspecialchars($_val['filename']);
            $_columnContainer[2]['align'] = 'center';
            $_columnContainer[2]['value'] = (int)($_key);
            $_columnContainer[3]['align'] = 'center';
            $_columnContainer[3]['value'] = FormattedSize($_val['filesize']);

            $_GeneralTabObject->Row($_columnContainer);

            $_index++;
        }

        $this->UserInterface->Hidden('ruleoptions', $_POST['ruleoptions']);
        if (isset($_POST['rulecriteria']) && _is_array($_POST['rulecriteria'])) {
            foreach ($_POST['rulecriteria'] as $_key => $_val) {
                $this->UserInterface->Hidden('rulecriteria[' . $_key . '][0]', $_val[0]);
                $this->UserInterface->Hidden('rulecriteria[' . $_key . '][1]', $_val[1]);
                $this->UserInterface->Hidden('rulecriteria[' . $_key . '][2]', $_val[2]);
            }
        }

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Rule Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderRuleForm()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_criteriaPointer = $this->Controller->AttachmentRule->GetCriteriaPointer();

        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        $this->UserInterface->Start(get_short_class($this), '/Base/PurgeAttachments/ProcessRule', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('lookup'), 'fa-search');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertcriteria'), 'fa-plus-circle', 'newGlobalRuleCriteria(\'' . SWIFT_AttachmentRule::ATTACHMENT_FILENAME . '\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('purgeattachments'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->get('short_all_tickets');
        $_optionsContainer[0]['value'] = 0;
        $_optionsContainer[0]["checked"] = (false == $this->Input->Post('typeoptions'));

        $_optionsContainer[1]['title'] = $this->Language->Get('app_tickets');
        $_optionsContainer[1]['value'] = SWIFT_Attachment::LINKTYPE_TICKETPOST;
        $_optionsContainer[1]['checked'] = ($this->Input->Post('typeoptions') == SWIFT_Attachment::LINKTYPE_TICKETPOST);

        $_optionsContainer[2]['title'] = $this->Language->Get('app_knowledgebase');
        $_optionsContainer[2]['value'] = SWIFT_Attachment::LINKTYPE_KBARTICLE;
        $_optionsContainer[2]['checked'] = ($this->Input->Post('typeoptions') == SWIFT_Attachment::LINKTYPE_KBARTICLE);

        $_GeneralTabObject->Select('typeoptions', $this->Language->Get('matchtype'), $this->Language->Get('desc_matchtype'), $_optionsContainer);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('smatchall');
        $_optionsContainer[0]['value'] = SWIFT_Rules::RULE_MATCHALL;

        if ((isset($_POST['ruleoptions']) && $_POST['ruleoptions'] == SWIFT_Rules::RULE_MATCHALL) || (!isset($_POST['ruleoptions']))) {
            $_optionsContainer[0]["checked"] = true;
        }
        $_optionsContainer[1]['title'] = $this->Language->Get('smatchany');
        $_optionsContainer[1]['value'] = SWIFT_Rules::RULE_MATCHANY;

        if (isset($_POST['ruleoptions']) && $_POST['ruleoptions'] == SWIFT_Rules::RULE_MATCHANY) {
            $_optionsContainer[1]['checked'] = true;
        }

        $_GeneralTabObject->Radio('ruleoptions', $this->Language->Get('criteriamatchtype'), $this->Language->Get('desc_criteriamatchtype'), $_optionsContainer);

        $_GeneralTabObject->AppendHTML('<tr class="' . $_GeneralTabObject->GetClass() . '"><td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>');

        $this->UserInterface->End();

        if (!isset($_POST['rulecriteria'])) {
            echo '<script language="Javascript" type="text/javascript">QueueFunction(function(){newGlobalRuleCriteria(\'' . SWIFT_AttachmentRule::ATTACHMENT_FILENAME . '\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\');});</script>';
        } else {
            SWIFT_Rules::CriteriaActionsPointerToJavaScript($_POST['rulecriteria']);
        }

        return true;
    }
}

?>
