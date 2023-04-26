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
use SWIFT_App;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Tag Generator View Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_TagGenerator extends SWIFT_View
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
     * Render the Tag Generator Form
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

        $this->UserInterface->Start(get_short_class($this), '/Base/TagGenerator/Index', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('taggenerator'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $_liveChatTabSelected = true;
        } else {
            $_liveChatTabSelected = false;
        }

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            /*
             * ###############################################
             * BEGIN CHAT TAB
             * ###############################################
             */
            $_ChatTabObject = $this->UserInterface->AddTab($this->Language->Get('tabchats'), 'icon_taggenerator.gif', 'tagchats', $_liveChatTabSelected);
            $_ChatTabObject->LoadToolbar();
            $_ChatTabObject->Toolbar->AddButton($this->Language->Get('next'), 'fa-chevron-circle-right ', '/LiveChat/TagGenerator/Index', SWIFT_UserInterfaceToolbar::LINK_FORM);
            $_ChatTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('taggenerator'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_optionsContainer = array();
            $_optionsContainer[0]['title'] = $this->Language->Get('chat');
            $_optionsContainer[0]['value'] = 'chat';
            $_optionsContainer[0]['selected'] = true;

            $_optionsContainer[1]['title'] = $this->Language->Get('callrequest');
            $_optionsContainer[1]['value'] = 'call';

            $_ChatTabObject->Select('chatprompttype', $this->Language->Get('chatprompttype'), $this->Language->Get('desc_chatprompttype'), $_optionsContainer);
            $_ChatTabObject->Title($this->Language->Get('chattagtype'), 'icon_doublearrows.gif');

            $_tagTypeContainer = array();
            $_tagTypeContainer[0] = array('htmlbutton', $this->Language->Get('tag_htmlbutton'), $this->Language->Get('desc_tag_htmlbutton'), 'button.png');
            $_tagTypeContainer[1] = array('sitebadge', $this->Language->Get('tag_sitebadge'), $this->Language->Get('desc_tag_sitebadge'), 'site_badge.png');
            $_tagTypeContainer[2] = array('textlink', $this->Language->Get('tag_textlink'), $this->Language->Get('desc_tag_textlink'), 'text.png');
            $_tagTypeContainer[3] = array('monitoring', $this->Language->Get('tag_monitoring'), $this->Language->Get('desc_tag_monitoring'), 'monitoring.png');

            foreach ($_tagTypeContainer as $_key => $_val) {
                if ($_key == 0) {
                    $_isChecked = true;
                } else {
                    $_isChecked = false;
                }

                $_columnContainer = array();
                $_columnContainer[0]['align'] = 'left';
                $_columnContainer[0]['nowrap'] = true;
                $_columnContainer[0]['colspan'] = "2";
                $_columnContainer[0]['value'] = '<table width="100%"  border="0" cellspacing="0" cellpadding="2"><tr><td width="1%"><input name="tagtype" type="radio" id="' . $_val[0] . '" onchange="this.blur();" value="' . $_val[0] . '"' . IIF($_isChecked, ' checked') . '></td><td width="99%"><span class="tabletitle"><label for="' . $_val[0] . '">&nbsp;' . $_val[1] . '</label></span></td></tr><tr><td>&nbsp;</td><td><table width="100%"  border="0" cellspacing="6" cellpadding="0"><tr><td width="1"><img src="' . SWIFT::Get('themepath') . 'images/' . $_val[3] . '" border="0" /></td><td align="left" valign="top"><span class="tabledescription">' . $_val[2] . '</span></td></tr></table></td></tr></table>';

                $_ChatTabObject->Row($_columnContainer);
            }

            /*
             * ###############################################
             * END CHAT TAB
             * ###############################################
             */
        }

        $this->UserInterface->End();

        return true;
    }
}

?>
