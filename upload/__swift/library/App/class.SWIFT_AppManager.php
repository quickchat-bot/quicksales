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

/**
 * The App Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_AppManager extends SWIFT_Library
{
    /**
     * Retrieve the available apps
     *
     * @author Varun Shoor
     * @return array The App Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveAvailableApps()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // First get the app list
        $_appList = SWIFT_App::ListApps();

        // Now retrieve the registered apps
        $_installedAppList = SWIFT_App::GetInstalledApps();

        // Now we need to itterate through each app and build a list of information
        $_appContainer = array();
        $_index = 0;
        foreach ($_appList as $_appName)
        {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            $_configFile = $_appDirectory . '/' . SWIFT_App::DIRECTORY_CONFIG . '/' . SWIFT_App::FILE_CONFIG;

            $_appContainer[$_appName] = array();
            $_appContainer[$_appName]['name'] = $_appName;
            $_appContainer[$_appName]['isregistered'] = IIF(in_array($_appName, $_installedAppList), true, false);

            if (!file_exists($_configFile))
            {
                $_index++;
                continue;
            }

            $_SimpleXMLObject = simplexml_load_file($_configFile);
            if (!$_SimpleXMLObject)
            {
                $_index++;
                continue;
            }

            $_appVersion = false;
            $_appTitle = $_appDescription = $_appAuthor = '';
            if (isset($_SimpleXMLObject->version))
            {
                $_appVersion = (string)$_SimpleXMLObject->version;
            }

            if (isset($_SimpleXMLObject->title))
            {
                $_appTitle = (string)$_SimpleXMLObject->title;
            }

            if (isset($_SimpleXMLObject->description))
            {
                $_appDescription = (string)$_SimpleXMLObject->description;
            }

            if (isset($_SimpleXMLObject->author))
            {
                $_appAuthor = (string)$_SimpleXMLObject->author;
            }

            $_appContainer[$_appName]['version'] = $_appVersion;
            $_appContainer[$_appName]['title'] = $_appTitle;
            $_appContainer[$_appName]['description'] = $_appDescription;
            $_appContainer[$_appName]['author'] = $_appAuthor;

            $_index++;
        }

        return $_appContainer;
    }

    /**
     * @author Atul Atri <atul.atri@kayako.com>
     *
     * @param string $_appName The App Name
     *
     * @return bool
     *
     * @throws SWIFT_Exception
     */
    public static function Install($_appName)
    {
        $_SWIFT        = SWIFT::GetInstance();
        $_appDirectory = SWIFT_App::GetAppDirectory($_appName);

        if (empty($_appDirectory))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (SWIFT_App::RetrieveSetupDatabaseObject($_appName)->InstallApp() !== false)
        {
            SWIFT::Info($_SWIFT->Language->Get('titleinstallsuccess'), sprintf($_SWIFT->Language->Get('msginstallsuccess'), $_appName));
        } else {
            SWIFT::Error($_SWIFT->Language->Get('titleinstallfailure'), sprintf($_SWIFT->Language->Get('msginstallfailure'), $_appName));

            return false;
        }

        return true;
    }

    /**
     * @author Atul Atri <atul.atri@kayako.com>
     *
     * @param string $_appName The App Name
     *
     * @return bool
     *
     * @throws SWIFT_Exception
     */
    public static function Uninstall($_appName)
    {
        $_SWIFT        = SWIFT::GetInstance();
        $_appDirectory = SWIFT_App::GetAppDirectory($_appName);

        if (empty($_appDirectory)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_appName == APP_CORE || $_appName == APP_BASE || $_appName == APP_BACKEND || $_appName == APP_CC || $_appName == APP_PRIVATE || $_appName == APP_CLUSTER) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (SWIFT_App::RetrieveSetupDatabaseObject($_appName)
                     ->Uninstall()
        ) {
            SWIFT::Info($_SWIFT->Language->Get('titleuninstallsuccess'), sprintf($_SWIFT->Language->Get('msguninstallsuccess'), $_appName));
        } else {
            SWIFT::Error($_SWIFT->Language->Get('titleuninstallfailure'), sprintf($_SWIFT->Language->Get('msguninstallfailure'), $_appName));

            return false;
        }

        return true;
    }

    /**
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @param string $_appName
     *
     * @throws SWIFT_Exception
     *
     * @returns bool
     */
    public static function Upgrade($_appName)
    {
        $_SWIFT        = SWIFT::GetInstance();
        $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
        if (empty($_appDirectory)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SetupDatabaseObject = SWIFT_App::RetrieveSetupDatabaseObject($_appName);

        $_upgradeResult = $_SWIFT_SetupDatabaseObject->UpgradeApp();

        // result is always true
        SWIFT::Info($_SWIFT->Language->Get('titleupgradesuccess'), sprintf($_SWIFT->Language->Get('msgupgradesuccess'), $_appName));

        return true;
    }

    /**
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @return bool
     *
     * @throws SWIFT_Exception
     */
    public static function UpgradeAll()
    {
        $_SWIFT   = SWIFT::GetInstance();
        $_appList = SWIFT_App::GetInstalledApps();

        $_finalSuccessText = $_finalFailureText = '';
        $_successIndex = $_failureIndex = 1;

        foreach ($_appList as $_appName)
        {
            $_SimpleXMLObject = SWIFT_App::RetrieveConfigXMLObject($_appName);

            $_appVersion = SWIFT_VERSION;
            if (isset($_SimpleXMLObject->version))
            {
                $_appVersion = (string)$_SimpleXMLObject->version;
            }

            $_appDBVersion = SWIFT_App::GetInstalledVersion($_appName);

            if ($_appDBVersion == false)
            {
                continue;

                // Upto date?
            } else if ($_appDBVersion == $_appVersion) {
                continue;
            }

            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_SetupDatabaseObject = SWIFT_App::RetrieveSetupDatabaseObject($_appName);

            $_upgradeResult = $_SWIFT_SetupDatabaseObject->UpgradeApp();

            if ($_upgradeResult != false)
            {
                $_finalSuccessText .= $_successIndex . '. ' . $_appName . '<br />';

                $_successIndex++;
            } else {
                $_finalFailureText .= $_failureIndex . '. ' . $_appName . '<br />';

                $_failureIndex++;
            }
        }

        if ($_successIndex > 1)
        {
            SWIFT::Info($_SWIFT->Language->Get('titleupgradeallsuccess'), sprintf($_SWIFT->Language->Get('msgupgradeallsuccess'), $_finalSuccessText));
        }

        if ($_failureIndex > 1)
        {
            SWIFT::Error($_SWIFT->Language->Get('titleupgradeallfailure'), sprintf($_SWIFT->Language->Get('msgupgradeallfailure'), $_finalFailureText));
        }

        return true;
    }


}

