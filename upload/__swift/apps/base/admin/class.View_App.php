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
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The App management view
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_App $Controller
 * @author Varun Shoor
 */
class View_App extends SWIFT_View
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
     * Render the App List
     *
     * @author Varun Shoor
     * @param array $_appContainer The Available App Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderAppList($_appContainer)
    {
        if (!$this->GetIsClassLoaded() || !_is_array($_appContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_appVersionContainer = $this->Settings->GetSection('appversions');

        $this->UserInterface->Start(get_short_class($this), '/Base/App/UpgradeAll', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabapps'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('upgradeall'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('app'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_columnContainer = array();
        $_columnContainer[0]['width'] = '16';
        $_columnContainer[0]['value'] = '&nbsp;';
        $_columnContainer[0]['align'] = 'center';

        $_columnContainer[1]['width'] = '';
        $_columnContainer[1]['value'] = $this->Language->Get('appdetails');
        $_columnContainer[1]['align'] = 'left';

        $_columnContainer[2]['width'] = '120';
        $_columnContainer[2]['value'] = $this->Language->Get('appdbversion');
        $_columnContainer[2]['align'] = 'center';

        $_columnContainer[3]['width'] = '120';
        $_columnContainer[3]['value'] = $this->Language->Get('applatestversion');
        $_columnContainer[3]['align'] = 'center';

        $_GeneralTabObject->Row($_columnContainer, 'gridtabletitlerow');

        foreach ($_appContainer as $_app) {
            if (SWIFT_App::IsDefaultApp($_app['name'])) { // We don't list default apps...
                continue;
            }

            $_appIcon = 'icon_app.png';

            $_versionMismatch = false;

            $_appVersion = SWIFT_VERSION;
            if (isset($_app['version']) && !empty($_app['version'])) {
                $_appVersion = htmlspecialchars($_app['version']);
            }

            $_appDBVersion = $this->Language->Get('na');
            if ($_app['isregistered'] == true && isset($_appVersionContainer[$_app['name']])) {
                $_appDBVersion = htmlspecialchars($_appVersionContainer[$_app['name']]);
            }

            if ($_app['isregistered'] == true && isset($_app['version']) && !empty($_app['version']) && $_app['version'] != $_appVersionContainer[$_app['name']]) {
                $_appIcon = 'icon_appalert.png';

                $_versionMismatch = true;
            }

            $_appDetails = '';


            // Status
            $_appStatus = '<div>';
            if ($_app['isregistered'] == true && $_versionMismatch == false) {
                $_appStatus .= '<span class="blockgreen">' . $this->Language->Get('appinstalled') . '</span>';
            } elseif ($_app['isregistered'] == true && $_versionMismatch == true) {
                $_appStatus .= '<span class="blockred">' . $this->Language->Get('appupgraderequired') . '</span>';
            } else {
                $_appStatus .= '<span class="blockgray">' . $this->Language->Get('appnotinstalled') . '</span>';
            }

            $_appDetails .= $_appStatus . ' <a href="' . SWIFT::Get('basename') . '/Base/App/View/' . $_app['name'] . '" viewport="1">';

            // App Title/Name
            if (isset($_app['title']) && !empty($_app['title'])) {
                $_appDetails .= htmlspecialchars($_app['title']);
            } else {
                $_appDetails .= htmlspecialchars($_app['name']);
            }

            $_appDetails .= '</a></div>';

            if (isset($_app['description']) && !empty($_app['description'])) {
                $_appDetails .= '<br />' . '<div>' . nl2br(htmlspecialchars($_app['description'])) . '</div>';
            }

            if (isset($_app['author']) && !empty($_app['author'])) {
                $_appDetails .= '<br />' . '<div>' . sprintf($this->Language->Get('appauthor'), htmlspecialchars($_app['author'])) . '</div>';
            }

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['value'] = '<img src="' . SWIFT::Get('themepathimages') . '/' . $_appIcon . '" align="absmiddle" border="0" />';

            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[1]['value'] = $_appDetails;

            $_columnContainer[2]['align'] = 'left';
            $_columnContainer[2]['value'] = $_appDBVersion;
            if ($_versionMismatch == true) {
                $_columnContainer[2]['class'] = 'errorrow';
            }

            $_columnContainer[3]['align'] = 'left';
            $_columnContainer[3]['value'] = $_appVersion;
            if ($_versionMismatch == true) {
                $_columnContainer[3]['class'] = 'errorrow';
            }

            $_GeneralTabObject->Row($_columnContainer);
        }

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the App Dialog
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_appVersionContainer = $this->Settings->GetSection('appversions');

        $_appContainer = $this->Controller->AppManager->RetrieveAvailableApps();
        if (!isset($_appContainer[$_appName])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_app = $_appContainer[$_appName];

        $_appTitle = $_appName;
        if (isset($_appContainer[$_appName]['title']) && !empty($_appContainer[$_appName]['title'])) {
            $_appTitle = htmlspecialchars($_appContainer[$_appName]['title']);
        }

        $_appDescription = $_appAuthor = '';
        if (isset($_appContainer[$_appName]['description']) && !empty($_appContainer[$_appName]['description'])) {
            $_appDescription = nl2br(htmlspecialchars($_appContainer[$_appName]['description']));
        }

        if (isset($_appContainer[$_appName]['author']) && !empty($_appContainer[$_appName]['author'])) {
            $_appAuthor = htmlspecialchars($_appContainer[$_appName]['author']);
        }

        $_appVersion = SWIFT_VERSION;
        if (isset($_app['version']) && !empty($_app['version'])) {
            $_appVersion = htmlspecialchars($_app['version']);
        }

        $_appDBVersion = $this->Language->Get('na');
        if ($_app['isregistered'] == true && isset($_appVersionContainer[$_app['name']])) {
            $_appDBVersion = htmlspecialchars($_appVersionContainer[$_app['name']]);
        }


        $this->UserInterface->Start(get_short_class($this), '/Base/App/ViewSubmit', SWIFT_UserInterface::MODE_INSERT, false);

        if ($_appContainer[$_appName]['isregistered'] && $_appName != APP_CORE && $_appName != APP_BASE && $_appName != APP_BACKEND && $_appName != APP_CC && $_appName != APP_PRIVATE && $_appName != APP_CLUSTER) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('appuninstall'), 'fa-minus-circle', '/Base/App/Uninstall/' . $_appName, SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        } elseif ($_appContainer[$_appName]['isregistered'] == false) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('appinstall'), 'fa-check-circle', '/Base/App/Install/' . $_appName, SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        if ($_app['isregistered'] == true && $_appVersion != $_appDBVersion) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('upgrade'), 'fa-check-circle', '/Base/App/Upgrade/' . $_appName . '/0', SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/App/Manage', SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('app'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($_appTitle, 'icon_app.png', 'general', true);
        $_GeneralTabObject->SetColumnWidth('15%');

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('apprtitle'), '', $_appTitle);
        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('apprdescription'), '', $_appDescription);
        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('apprauthor'), '', $_appAuthor);
        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('appdbversion'), '', $_appDBVersion);
        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('applatestversion'), '', $_appVersion);

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
