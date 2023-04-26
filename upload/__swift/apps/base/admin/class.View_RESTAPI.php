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

use SWIFT;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The REST API View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_RESTAPI extends SWIFT_View
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
     * Render the REST API Form
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

        $this->UserInterface->Start(get_short_class($this), '/Base/RESTAPI/ReGenerate', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('regenerate'), 'fa-repeat', $this->Language->Get('apiregenconfirm'), SWIFT_UserInterfaceToolbar::LINK_SUBMITCONFIRMCUSTOM, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('restapi'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Title($this->Language->Get('authenticationdetails'), 'icon_lock.gif');

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('apikey'), $this->Language->Get('desc_apikey'), $this->Settings->GetKey('restapi', 'apikey'));

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('secretkey'), $this->Language->Get('desc_secretkey'), $this->Settings->GetKey('restapi', 'secretkey'));

        $_GeneralTabObject->Title($this->Language->Get('usageinformation'), 'icon_doublearrows.gif');

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('apiurl'), $this->Language->Get('desc_apiurl'), SWIFT::Get('swiftpath') . 'api' . '/' . SWIFT_BASENAME);

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('apidocs'), $this->Language->Get('desc_apidocs'), '<a href="'.SWIFT_Help::RetrieveHelpLink('restapi').'" rel="noopener noreferrer" target="_blank">'.SWIFT_Help::RetrieveHelpLink('restapi').'</a>');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
