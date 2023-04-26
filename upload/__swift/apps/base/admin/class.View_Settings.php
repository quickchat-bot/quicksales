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
use SWIFT_App;
use Base\Library\UserInterface\SWIFT_UserInterface;
use SWIFT_View;

/**
 * The Settings View Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Settings extends SWIFT_View
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
     * Render the Settings List
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderList()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this), '/LiveChat/View', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settingsgroups WHERE ishidden = '0' ORDER BY displayorder ASC");
        while ($this->Database->NextRecord()) {
            if (!SWIFT_App::IsInstalled($this->Database->Record['app'])) {
                continue;
            }

            $_settingGroupURL = '/Base/Settings/View/' . (int)($this->Database->Record['sgroupid']);

            $_rowClass = $_GeneralTabObject->GetClass();

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['class'] = $_rowClass . ' pointer';
            $_columnContainer[0]['onclick'] = 'loadViewportData(\'' . $_settingGroupURL . '\');';
            $_columnContainer[0]['value'] = '<a href="' . SWIFT::Get('basename') . $_settingGroupURL . '" viewport="1"><img src="' . SWIFT::Get('themepath') . 'images/icon_settings2.gif" align="absmiddle" border="0" /> ' . htmlspecialchars($this->Language->Get($this->Database->Record['name'])) . '</a>';

            $_GeneralTabObject->Row($_columnContainer, $_rowClass);
        }

        $this->UserInterface->End();

        return true;
    }
}

?>
