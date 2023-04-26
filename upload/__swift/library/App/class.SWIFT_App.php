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

use Base\Library\KQL\SWIFT_KQLSchema;

/**
 * The Core App Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_App extends SWIFT_Base
{
    private $_appName;
    private $_appDirectory;

    static private $_appObjectCache = array();

    static private $_availableAppsCache = array();
    static public $_installedApps = false;

    static private $_appDirectoryCache = array();

    static protected $_permissionsContainer = array();

    // Core Constants
    const SWIFT_APPCORE = 'core'; // Name of Core App

    const DIRECTORY_HOOKS = 'hooks';
    const FILE_HOOKSUFFIX = 'hook';
    const DIRECTORY_CONFIG = 'config';
    const FILE_CONFIG = 'config.xml';

    const FILETYPE_MODEL = 1;
    const FILETYPE_LIBRARY = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_appName The App Namee
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct($_appName)
    {
        if (!$this->SetName($_appName))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Sets the App Name
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    private function SetName($_appName)
    {
        $_appName = strtolower(Clean($_appName));

        if (empty($_appName) || !self::DirectoryExists($_appName))
        {
            throw new SWIFT_Exception($_appName . ' not found');
        }

        $this->_appName = $_appName;

        return true;
    }

    /**
     * Retrieve the Currently Set App Name
     *
     * @author Varun Shoor
     * @return mixed "_appName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_appName;
    }

    /**
     * Get the App with a given name (from a cache, if one exists)
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return SWIFT_App "SWIFT_App" Object on Success, "false" otherwise
     */
    public static function Get($_appName)
    {
        $_appName = strtolower($_appName);
        if (isset(self::$_appObjectCache[$_appName]) && self::$_appObjectCache[$_appName] instanceof SWIFT_App && self::$_appObjectCache[$_appName]->GetIsClassLoaded())
        {
            return self::$_appObjectCache[$_appName];
        }

        $_appDirectory = self::GetAppDirectory($_appName);
        $_appOverloadFile = $_appDirectory . '/' . SWIFT_CONFIG_DIRECTORY . '/class.SWIFT_App_' . Clean($_appName) . '.php';
        if (file_exists($_appOverloadFile)) {
            $_className = 'SWIFT_App_' . Clean($_appName);
            $_className = prepend_app_namespace($_appName, $_className);

            if (!class_exists($_className)) {
                require_once $_appOverloadFile;
            }

            self::$_appObjectCache[$_appName] = new $_className($_appName);
        } else {
            self::$_appObjectCache[$_appName] = new SWIFT_App($_appName);
        }

        return self::$_appObjectCache[$_appName];
    }

    /**
     * Execute the Controller
     *
     * @author Varun Shoor
     * @param SWIFT_Router $_SWIFT_RouterObject The SWIFT_Router Pointer
     * @param string $_controllerParentClass (OPTIONAL) Restrict the controller parent class to a specific type
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ExecuteController(SWIFT_Router $_SWIFT_RouterObject, $_controllerParentClass = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_RouterObject instanceof SWIFT_Router || !$_SWIFT_RouterObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_ControllerObject = SWIFT_Controller::Load($_SWIFT->Interface, $this, $_SWIFT_RouterObject, $_controllerParentClass);
        if (!$_SWIFT_ControllerObject instanceof SWIFT_Controller || !$_SWIFT_ControllerObject->GetIsClassLoaded())
        {
//            throw new SWIFT_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        return $_SWIFT_ControllerObject;
    }

    /**
     * Checks to see if the app directory exists
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function DirectoryExists($_appName)
    {
        $_appDirectory = self::GetAppDirectory($_appName);
        if (!$_appDirectory)
        {
            return false;
        }

        $this->SetDirectory($_appDirectory);

        return true;
    }

    /**
     * Retrieve the App Directory
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return mixed "_appDirectory" (STRING) on Success, "false" otherwise
     */
    public static function GetAppDirectory($_appName)
    {
        if (empty($_appName) || trim($_appName) == '')
        {
            return false;
        }

        if (isset(self::$_appDirectoryCache[$_appName]))
        {
            return self::$_appDirectoryCache[$_appName];
        }

        // We first check the list of core apps (./__swift/apps/APPNAME) and then the base apps directory (./__apps/APPNAME)
        $_appCoreDirectory = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_COREAPPSDIRECTORY . '/' . $_appName;
        $_appDirectory = './' . SWIFT_APPSDIRECTORY . '/' . $_appName;

        if (file_exists($_appCoreDirectory) && is_dir($_appCoreDirectory))
        {
            self::$_appDirectoryCache[$_appName] = $_appCoreDirectory;

            return $_appCoreDirectory;
        } else if (file_exists($_appDirectory) && is_dir($_appDirectory)) {
            self::$_appDirectoryCache[$_appName] = $_appDirectory;

            return $_appDirectory;
        }

        return false;
    }

    /**
     * Sets the App Directory
     *
     * @author Varun Shoor
     * @param string $_appDirectory The App Directory
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    private function SetDirectory($_appDirectory)
    {
        if (empty($_appDirectory))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_appDirectory = $_appDirectory;

        return false;
    }

    /**
     * Retrieve the Currently Set App Directory
     *
     * @author Varun Shoor
     * @return mixed "_app4Directory" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDirectory()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_appDirectory;
    }

    /**
     * Checks to see if the interface directory exists in the app
     *
     * @author Varun Shoor
     * @param string $_interfaceName The Interface Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function InterfaceDirectoryExists($_interfaceName)
    {
        $_appName = $this->GetName();
        $_appDirectory = $this->GetDirectory();
        $_interfaceName = strtolower(Clean($_interfaceName));

        $_interfaceDirectory = $_appDirectory . '/' . $_interfaceName;
        if (file_exists($_interfaceDirectory) && is_dir($_interfaceDirectory))
        {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if the controller file exists in the app
     *
     * @author Varun Shoor
     * @param string $_interfaceName The Interface Name
     * @param string $_controllerName The Controller Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function ControllerFileExists($_interfaceName, $_controllerName)
    {
        $_appName        = $this->GetName();
        $_appDirectory   = $this->GetDirectory();
        $_interfaceName  = strtolower(Clean($_interfaceName));
        $_controllerName = Clean($_controllerName);

        $_controllerClassName     = SWIFT_Controller::CONTROLLER_CLASS_PREFIX . $_controllerName;
        $_controllerFilename      = $_appDirectory . '/' . $_interfaceName . '/' . SWIFT_Controller::FILE_PREFIX . $_controllerClassName . '.php';
        $_controllerFileCacheMeta = SWIFT_Loader::GetCacheByAppInterface($this, SWIFT_Interface::GetInterfaceFromString($_interfaceName), SWIFT_Loader::TYPE_CONTROLLER, $_controllerClassName);
        $_controllerFile          = isset($_controllerFileCacheMeta[0]) ? $_controllerFileCacheMeta[0] : $_controllerFilename;

        return file_exists($_controllerFile);
    }

    /**
     * List the available apps
     *
     * @author Varun Shoor
     * @param bool $_onlyInstalled (OPTIONAL) If set to true, only installed apps are returned..
     * @return array(core, livechat, tickets..) on Success, "false" otherwise
     */
    public static function ListApps($_onlyInstalled = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (_is_array(self::$_availableAppsCache))
        {
            return self::$_availableAppsCache;
        }

        $_availableApps = array();
        $_swiftAppDirectory = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_COREAPPSDIRECTORY;
        $_appDirectory = './' . SWIFT_APPSDIRECTORY;

        $_availableApps[] = APP_CORE;

        self::ParseInstalledApps();

        $_installedAppList = array();
        if ($_onlyInstalled === true && $_SWIFT->Database instanceof SWIFT_Database && $_SWIFT->Database->GetIsClassLoaded()) {
            $_installedAppList = self::$_installedApps;
        }

        foreach (array($_swiftAppDirectory, $_appDirectory) as $_val)
        {
            if ($_directoryHandle = opendir($_val))
            {
                while (false !== ($_file = readdir($_directoryHandle)))
                {
                    if (substr($_file, 0, 1) != '.' && $_file != '.' && $_file != '..' && is_dir($_val . '/' . $_file) && !in_array($_file, $_availableApps))
                    {
                        if ($_onlyInstalled === true && !in_array($_file, $_installedAppList)) {
                            continue;
                        }

                        $_availableApps[] = $_file;
                    }
                }

                closedir($_directoryHandle);
            }
        }

        self::$_availableAppsCache = $_availableApps;

        return $_availableApps;
    }

    /**
     * Checks to see if the given app exists
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     */
    public static function AppExists($_appName)
    {
        $_appName = Clean($_appName);

        $_availableApps = self::ListApps();

        if (!in_array($_appName, $_availableApps))
        {
            return true;
        }

        return false;
    }

    /**
     * Parses the installed apps and loads it into the static name space of this class
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ParseInstalledApps()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Cache already loaded?
        if (self::$_installedApps !== false) {
            return true;
        }

        self::$_installedApps = array();

        $_loadFromDB = false;

        try {
            if ($_SWIFT->Settings instanceof SWIFT_Settings) {
                self::$_installedApps = $_SWIFT->Settings->GetSection('installedapps');
            }

            if (!is_array(self::$_installedApps)) {
                throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
            }

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_loadFromDB = true;
        }

        if ($_loadFromDB === true) {
            try {
                if ($_SWIFT->Database instanceof SWIFT_Database && $_SWIFT->Database->GetIsClassLoaded()) {
                    $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settings WHERE section = 'installedapps'");
                    while ($_SWIFT->Database->NextRecord()) {
                        if ($_SWIFT->Database->Record['data'] == '1') {
                            self::$_installedApps[$_SWIFT->Database->Record['vkey']] = '1';
                        }
                    }

                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        /**
         * Auto Load the Core App
         */
        self::$_installedApps[self::SWIFT_APPCORE] = '1';

        return true;
    }

    /**
     * Retrieve the Installed Apps
     *
     * @author Varun Shoor
     * @return array The Installed App List
     */
    public static function GetInstalledApps()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_appList = array();

        try {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settings WHERE section IN ('installedapps')");
            while ($_SWIFT->Database->NextRecord())
            {
                $_appList[] = $_SWIFT->Database->Record['vkey'];
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        if (!in_array(APP_CORE, $_appList))
        {
            $_appList[] = APP_CORE;
        }

        if (!in_array(APP_BASE, $_appList))
        {
            $_appList[] = APP_BASE;
        }

        return $_appList;
    }

    /**
     * Checks to see if a given app is installed
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsInstalled($_appName)
    {
        $_appName = Clean($_appName);

        if (self::$_installedApps === false) {
            self::ParseInstalledApps();
        }

        if (isset(self::$_installedApps[$_appName]) && self::$_installedApps[$_appName] == '1')
        {
            return true;
        }

        return false;
    }

    /**
     * Was this app ever installed?
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function WasInstalled()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_appLogContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "applogs
                                                        WHERE appname = '" . $_SWIFT->Database->Escape($this->GetName()) . "' AND logtype = '" . SWIFT_AppLog::TYPE_INSTALL . "'");
        if (isset($_appLogContainer['appname'])) {
            return true;
        }

        return false;
    }

    /**
     * Check to see whether this is a core application i.e. the one shipped with the product
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsCoreApp()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_coreAppList = SWIFT::Get('CoreApps');
        if (in_array($this->GetName(), $_coreAppList)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Hook file path
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @param string $_hookName The Controller Name
     * @return string The probable hook file path
     */
    public static function GetHookFilePath($_appName, $_hookName)
    {
        $_appName = Clean($_appName);
        $_hookName = Clean($_hookName);

        $_appDirectory = self::GetAppDirectory($_appName);

        $_hookFile = $_appDirectory . '/' . self::DIRECTORY_HOOKS . '/' . $_hookName . '.' . self::FILE_HOOKSUFFIX;

        return $_hookFile;
    }

    /**
     * Parses config files of all non-core apps
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ParseConfig()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_coreAppList = SWIFT::Get('CoreApps');
        $_nonCoreAppList = array();

        // Itterate through the installed apps and find non-core apps
        foreach (self::$_installedApps as $_key => $_val)
        {
            if (!in_array($_key, $_coreAppList) && !in_array($_key, $_nonCoreAppList))
            {
                $_nonCoreAppList[] = $_key;
            }
        }

        if (!count($_nonCoreAppList))
        {
            return false;
        }

        // Go through the non core installed apps and load its config.xml file
        $_globalAdminBar = $_globalAdminBarItems = $_globalStaffMenu = $_globalAdminMenu = $_globalStaffMenuLinks = $_globalAdminMenuLinks = $_menuContainer = $_menuLinksContainer = array();

        $_interfaceList = array('staff', 'admin', 'intranet');

        foreach ( $_interfaceList as $_interfaceKey => $_interfaceVal )
        {
            $_menuContainer[$_interfaceVal] = array();
            $_menuLinksContainer[$_interfaceVal] = array();
        }

        foreach ($_nonCoreAppList as $_key => $_val)
        {
            $_appDirectory = self::GetAppDirectory($_val);
            $_configFile = $_appDirectory . '/' . self::DIRECTORY_CONFIG . '/' . self::FILE_CONFIG;

            if (!file_exists($_configFile))
            {
                continue;
            }

            $_SimpleXMLObject = simplexml_load_file($_configFile);
            if (!$_SimpleXMLObject)
            {
                continue;
            }

            $_configResult = self::ParseConfigFile($_val, $_SimpleXMLObject);

            // Define constants
            $_constantName = 'APP_' . mb_strtoupper($_val);
            if (!defined($_constantName)) {
                define($_constantName, $_val);
            }

            /*
             * ###############################################
             * PROCESS INTERFACES
             * ###############################################
             */
            $_globalAdminBar = $_globalAdminBar + $_configResult['_adminBar'];
            $_globalAdminBarItems = $_globalAdminBarItems + $_configResult['_adminBarItems'];

            foreach ($_interfaceList as $_interfaceKey => $_interfaceVal)
            {
                if (!isset($_menuContainer[$_interfaceVal]))
                {
                    $_menuContainer[$_interfaceVal] = array();
                }

                if (!isset($_menuLinksContainer[$_interfaceVal]))
                {
                    $_menuLinksContainer[$_interfaceVal] = array();
                }

                if (isset($_configResult['_menuContainer'][$_interfaceVal]))
                {
                    $_linkedMenuContainer = $_configResult['_menuContainer'][$_interfaceVal];
                    $_menuContainer[$_interfaceVal] = $_menuContainer[$_interfaceVal] + $_linkedMenuContainer;
                }

                if (isset($_configResult['_menuLinks'][$_interfaceVal]))
                {
                    $_linkedMenuLinksContainer = $_configResult['_menuLinks'][$_interfaceVal];
                    $_menuLinksContainer[$_interfaceVal] = $_menuLinksContainer[$_interfaceVal] + $_linkedMenuLinksContainer;
                }
            }

            /*
             * ###############################################
             * ATTEMPT TO LOAD THE CORE LOCALE
             * ###############################################
             */
            $_currentLocaleLoadResult = $_SWIFT->Language->LoadApp($_SWIFT->Language->GetLanguageCode(), $_val);
            if (!$_currentLocaleLoadResult)
            {
                // Attempt to load default locale
                $_SWIFT->Language->LoadApp(SWIFT_LanguageEngine::DEFAULT_LOCALE, $_val);
            }
        }

        SWIFT::Set('globaladminbar', $_globalAdminBar);
        SWIFT::Set('globaladminbaritems', $_globalAdminBarItems);
        SWIFT::Set('globalmenu', $_menuContainer);
        SWIFT::Set('globalmenulinks', $_menuLinksContainer);

        return true;
    }

    /**
     * Parses the Simple XML Object and loads the required data
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @param SimpleXMLElement $_SimpleXMLObject The SimpleXMLElement Object Pointer
     * @return mixed
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function ParseConfigFile($_appName, SimpleXMLElement $_SimpleXMLObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SimpleXMLObject instanceof SimpleXMLElement || empty($_appName))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /*
         * ###############################################
         * PROCESS TABS
         * ###############################################
         */
        $_menuContainer = $_menuLinks = array();
        $_menuContainer['staff'] = $_menuContainer['admin'] = array();
        $_menuLinks['staff'] = $_menuLinks['admin'] = array();

        if (isset($_SimpleXMLObject->tabs) && isset($_SimpleXMLObject->tabs->tab))
        {
            foreach ($_SimpleXMLObject->tabs->tab as $_key => $_TabObject)
            {
                $_tabWidth = 100;
                $_tabPermission = false;

                $_tabType = (string)$_TabObject->attributes()->type;
                $_tabTitle = (string)$_TabObject->attributes()->title;
                $_tabID = (int) ($_TabObject->attributes()->id);

                if (isset($_TabObject->attributes()->width))
                {
                    $_tabWidth = (int)$_TabObject->attributes()->width;
                }

                if (isset($_TabObject->attributes()->permission))
                {
                    $_tabPermission = (string)$_TabObject->attributes()->permission;
                }

                if (empty($_tabTitle) || empty($_tabType) || ($_tabID < 100 && $_tabType != 'intranet'))
                {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                $_menuContainer[$_tabType][$_tabID] = array($_tabTitle, $_tabWidth, $_appName, $_tabPermission);

                if (isset($_TabObject->tabitem) && count($_TabObject->tabitem))
                {
                    foreach ($_TabObject->tabitem as $_tabItemKey => $_tabItemVal)
                    {
                        $_tabItemLink = (string) $_tabItemVal->attributes()->link;
                        $_tabItemTitle = (string) $_tabItemVal;

                        $_menuLinks[$_tabType][$_tabID][] = array($_tabItemTitle, $_tabItemLink);
                    }
                }

            }
        }

        /*
         * ###############################################
         * PROCESS ADMIN NAVIGATION
         * ###############################################
         */
        $_adminBar = $_adminBarItems = array();
        if (isset($_SimpleXMLObject->adminnavigation, $_SimpleXMLObject->adminnavigation->navigation) && count($_SimpleXMLObject->adminnavigation->navigation))
        {
            foreach ($_SimpleXMLObject->adminnavigation->navigation as $_key => $_NavigationObject)
            {
                $_navigationID = (int) $_NavigationObject->attributes()->id;
                $_navigationTitle = (string) $_NavigationObject->attributes()->title;
                $_navigationIcon = (string) $_NavigationObject->attributes()->icon;

                if ($_navigationID < 100 || empty($_navigationTitle) || empty($_navigationIcon))
                {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                if (isset($_NavigationObject->attributes()->link))
                {
                    $_navigationLink = (string) $_NavigationObject->attributes()->link;
                    $_adminBar[$_navigationID] = array($_navigationTitle, $_navigationIcon, $_appName, $_navigationLink);
                } else {
                    $_adminBar[$_navigationID] = array($_navigationTitle, $_navigationIcon, $_appName);

                    // Process the sub items
                    if (isset($_NavigationObject->navitem) && count($_NavigationObject->navitem))
                    {
                        foreach ($_NavigationObject->navitem as $_navigationItemKey => $_navigationItemVal)
                        {
                            $_navigationItemTitle = (string) $_navigationItemVal;
                            $_navigationItemLink = (string) $_navigationItemVal->attributes()->link;

                            $_adminBarItems[$_navigationID][] = array($_navigationItemTitle, $_navigationItemLink);
                        }
                    }
                }
            }
        }

        /*
         * ###############################################
         * PROCESS PERMISSIONS
         * ###############################################
         */
        $_permissionContainer = &self::$_permissionsContainer;

        if (isset($_SimpleXMLObject->permissions)) {
            foreach (array('staff', 'admin', 'user') as $_permissionSection) {
                if (!isset($_permissionContainer[$_permissionSection])) {
                    $_permissionContainer[$_permissionSection] = array();
                }

                $_permissionContainer[$_permissionSection][$_appName] = array();

                if (isset($_SimpleXMLObject->permissions->$_permissionSection)) {
                    // Process Permissions
                    if (isset($_SimpleXMLObject->permissions->$_permissionSection->permission)) {
                        foreach ($_SimpleXMLObject->permissions->$_permissionSection->permission as $_PermissionObject) {
                            $_permissionContainer[$_permissionSection][$_appName][] = (string)$_PermissionObject;
                        }
                    }

                    // Process Groups
                    if (isset($_SimpleXMLObject->permissions->$_permissionSection->group)) {
                        foreach ($_SimpleXMLObject->permissions->$_permissionSection->group as $_GroupObject) {
                            $_groupName = (string)$_GroupObject->attributes()->name;
                            $_groupPermissionContainer = array();

                            foreach (array('view', 'delete', 'insert', 'update', 'manage') as $_groupPermissionType) {
                                if (isset($_GroupObject->$_groupPermissionType)) {
                                    foreach ($_GroupObject->$_groupPermissionType as $_GroupPermissionObject) {
                                        switch ($_groupPermissionType) {
                                            case 'view':
                                                $_groupPermissionContainer[SWIFT_VIEW] = (string) $_GroupPermissionObject;
                                                break;

                                            case 'delete':
                                                $_groupPermissionContainer[SWIFT_DELETE] = (string) $_GroupPermissionObject;
                                                break;

                                            case 'insert':
                                                $_groupPermissionContainer[SWIFT_INSERT] = (string) $_GroupPermissionObject;
                                                break;

                                            case 'update':
                                                $_groupPermissionContainer[SWIFT_UPDATE] = (string) $_GroupPermissionObject;
                                                break;

                                            case 'manage':
                                                $_groupPermissionContainer[SWIFT_MANAGE] = (string) $_GroupPermissionObject;
                                                break;

                                            default:
                                                break;
                                        }
                                    }
                                }
                            }

                            $_permissionContainer[$_permissionSection][$_appName][] = array($_groupName, $_groupPermissionContainer);
                        }
                    }
                }
            }
        }

        /*
         * ###############################################
         * PROCESS HOOKS
         * ###############################################
         */
        $_hookList = array();
        if (isset($_SimpleXMLObject->hooks) && isset($_SimpleXMLObject->hooks->hook))
        {
            foreach ($_SimpleXMLObject->hooks->hook as $_key => $_HookObject)
            {
                $_hookName = (string) $_HookObject;

                $_hookPriority = false;

                if (empty($_hookName))
                {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                if (isset($_HookObject->attributes()->priority))
                {
                    $_hookPriority = (int) $_HookObject->attributes()->priority;
                }

                $_hookList[] = $_hookName;

                $_SWIFT->Hook->Register($_appName, $_hookName, $_hookPriority);
            }
        }

        /*
         * ###############################################
         * HOOKS DIRECTORY
         * ###############################################
         */
        $_appDirectory = self::GetAppDirectory($_appName);
        $_hookDirectory = $_appDirectory . '/' . self::DIRECTORY_HOOKS . '/';
        if (file_exists($_hookDirectory) && is_dir($_hookDirectory))
        {
            if ($_directoryHandle = opendir($_hookDirectory)) {
                while (false !== ($_file = readdir($_directoryHandle))) {
                    if ($_file != '.' && $_file != '..') {
                        $_fileInfoContainer = pathinfo($_file);

                        // Is it a hook?
                        if (isset($_fileInfoContainer['extension']) && mb_strtolower($_fileInfoContainer['extension']) == self::FILE_HOOKSUFFIX)
                        {
                            $_SWIFT->Hook->Register($_appName, substr($_fileInfoContainer['basename'], 0, strrpos($_fileInfoContainer['basename'], '.')));
                        }
                    }
                }

                closedir($_directoryHandle);
            }
        }

        return array('_menuContainer' => $_menuContainer, '_menuLinks' => $_menuLinks, '_adminBar' => $_adminBar, '_adminBarItems' => $_adminBarItems, '_hookList' => $_hookList);
    }

    /**
     * Retrieve the setup database object for the given app
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return SWIFT_SetupDatabase
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveSetupDatabaseObject($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_appDirectory = self::GetAppDirectory($_appName);
        if (empty($_appDirectory) || !file_exists($_appDirectory))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_setupDatabaseFile = $_appDirectory . '/' . self::DIRECTORY_CONFIG . '/class.SWIFT_SetupDatabase_' . $_appName . '.php';
        if (!file_exists($_setupDatabaseFile))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        require_once($_setupDatabaseFile);

        $_setupDatabaseClassName = 'SWIFT_SetupDatabase_' . $_appName;
        $_setupDatabaseClassName = prepend_app_namespace($_appName, $_setupDatabaseClassName);
        $_SWIFT_SetupDatabaseObject = new $_setupDatabaseClassName();
        if (!$_SWIFT_SetupDatabaseObject instanceof SWIFT_SetupDatabase || !$_SWIFT_SetupDatabaseObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_SWIFT_SetupDatabaseObject;
    }

    /**
     * Retrieve the KQL Schema object for the given app
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return SWIFT_KQLSchema|bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveKQLSchemaObject($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_appDirectory = self::GetAppDirectory($_appName);
        if (empty($_appDirectory) || !file_exists($_appDirectory))
        {
            return false;
        }

        $_kqlSchemaFile = $_appDirectory . '/' . self::DIRECTORY_CONFIG . '/class.SWIFT_KQLSchema_' . $_appName . '.php';
        if (!file_exists($_kqlSchemaFile))
        {
            return false;
        }

        SWIFT_Loader::AddApp(SWIFT_App::Get($_appName));

        require_once($_kqlSchemaFile);

        $_kqlSchemaClassName = 'SWIFT_KQLSchema_' . $_appName;
        $_kqlSchemaClassName = prepend_app_namespace($_appName, $_kqlSchemaClassName);
        $_SWIFT_KQLSchemaObject = new $_kqlSchemaClassName();
        if (!$_SWIFT_KQLSchemaObject instanceof SWIFT_KQLSchema || !$_SWIFT_KQLSchemaObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_SWIFT_KQLSchemaObject;
    }

    /**
     * Retrieve the setup database object for the given app
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return SimpleXMLElement
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveConfigXMLObject($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_appDirectory = self::GetAppDirectory($_appName);
        if (empty($_appDirectory) || !file_exists($_appDirectory))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_configFile = $_appDirectory . '/' . self::DIRECTORY_CONFIG . '/config.xml';
        if (!file_exists($_configFile))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SimpleXMLObject = simplexml_load_file($_configFile);

        if (!$_SimpleXMLObject)
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_SimpleXMLObject;
    }

    /**
     * Retrieve the Installed Version of the app
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return mixed "Installed Version" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetInstalledVersion($_appName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_appDirectory = self::GetAppDirectory($_appName);
        if (empty($_appDirectory) || !file_exists($_appDirectory))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_appVersionContainer = $_SWIFT->Settings->GetSection('appversions');

        $_appDBVersion = false;
        if (SWIFT_App::IsInstalled($_appName) && isset($_appVersionContainer[$_appName]))
        {
            $_appDBVersion = htmlspecialchars($_appVersionContainer[$_appName]);
        }

        return $_appDBVersion;
    }

    /**
     * Retrieve the Permission Container
     *
     * @author Varun Shoor
     * @param string $_interface The Interface
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetPermissionContainer($_interface) {
        $_SWIFT = SWIFT::GetInstance();

        if (!isset(self::$_permissionsContainer[$_interface])) {
            return array();
        }

        return self::$_permissionsContainer[$_interface];
    }

    /**
     * Initialize the app
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Initialize()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_interfaceType = $_SWIFT->Interface->GetInterface();

        if ($_interfaceType == SWIFT_Interface::INTERFACE_ADMIN || $_interfaceType == SWIFT_Interface::INTERFACE_STAFF || $_interfaceType == SWIFT_Interface::INTERFACE_WINAPP
                || $_interfaceType == SWIFT_Interface::INTERFACE_SYNCWORKS || $_interfaceType == SWIFT_Interface::INTERFACE_RSS || $_interfaceType == SWIFT_Interface::INTERFACE_PDA
                || $_interfaceType == SWIFT_Interface::INTERFACE_INSTAALERT || $_interfaceType == SWIFT_Interface::INTERFACE_MOBILE || $_interfaceType == SWIFT_Interface::INTERFACE_API
                || $_interfaceType == SWIFT_Interface::INTERFACE_INTRANET || $_interfaceType == SWIFT_Interface::INTERFACE_STAFFAPI) {

            $_SWIFT->Cache->Queue('gridcache');
            $_SWIFT->Cache->Queue('commentcache');
            $_SWIFT->Cache->Queue('loginsharecache');
            $_SWIFT->Cache->Queue('staffloginsharecache');
            $_SWIFT->Cache->Queue('slaplancache');
            $_SWIFT->Cache->Queue('slaschedulecache');
            $_SWIFT->Cache->Queue('cfgrouppermissioncache');
            $_SWIFT->Cache->Queue('chatcountcache');
            $_SWIFT->Cache->Queue('staffpermissioncache');
            $_SWIFT->Cache->Queue('ticketfiltercache');
            $_SWIFT->Cache->Queue('customfieldcache');
            $_SWIFT->Cache->Queue('customfieldidcache');
            $_SWIFT->Cache->Queue('customfieldoptioncache');
            $_SWIFT->Cache->Queue('customfieldmapcache');

        } else if ($_interfaceType == SWIFT_Interface::INTERFACE_CLIENT || $_interfaceType == SWIFT_Interface::INTERFACE_VISITOR
                || $_interfaceType == SWIFT_Interface::INTERFACE_RSS || $_interfaceType == SWIFT_Interface::INTERFACE_CHAT
                || $_interfaceType == SWIFT_Interface::INTERFACE_ADMIN || $_interfaceType == SWIFT_Interface::INTERFACE_STAFF || $_interfaceType == SWIFT_Interface::INTERFACE_CONSOLE
                || $_interfaceType == SWIFT_Interface::INTERFACE_CRON) {

            $_SWIFT->Cache->Queue('usergroupsettingcache');
        }

        if ($_interfaceType == SWIFT_Interface::INTERFACE_WINAPP || $_interfaceType == SWIFT_Interface::INTERFACE_MOBILE || $_interfaceType == SWIFT_Interface::INTERFACE_STAFFAPI)
        {
            $_SWIFT->Cache->Queue('skillscache');
        }

        $_SWIFT->Cache->Queue('settingscache');

        $_SWIFT->Cache->Queue('templategroupcache');
        $_SWIFT->Cache->Queue('languagecache');

        return true;
    }

    /**
     * Check to see whether its a valid file type
     *
     * @author Varun Shoor
     * @param mixed $_fileType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidFileType($_fileType)
    {
        return ($_fileType == self::FILETYPE_MODEL || $_fileType == self::FILETYPE_LIBRARY);
    }

    /**
     * Retrieve the file list for this app
     *
     * @author Varun Shoor
     * @param mixed $_fileType
     * @param string $_restrictStaticFunctionName (OPTIONAL) Filter down to files which have a given static function name
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RetrieveFileList($_fileType, $_restrictStaticFunctionName = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidFileType($_fileType)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $_returnContainer = $_fileList = array();

        $_appDirectory = $this->GetDirectory();
        if (!is_dir($_appDirectory)) {
            return $_returnContainer;
        }

        SWIFT_Loader::AddApp($this);

        if ($_fileType == self::FILETYPE_MODEL) {
            $_fileList = self::RetrieveModelFileList(StripTrailingSlash($_appDirectory) . '/' . SWIFT_MODELS_DIRECTORY);

            if ($this->GetName() == APP_CORE) {
                $_fileList = array_merge($_fileList, self::RetrieveModelFileList('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_MODELS_DIRECTORY));
            }
        } else if ($_fileType == self::FILETYPE_LIBRARY) {
            $_fileList = self::RetrieveModelFileList(StripTrailingSlash($_appDirectory) . '/' . SWIFT_LIBRARY_DIRECTORY);

            if ($this->GetName() == APP_CORE) {
                $_fileList = array_merge($_fileList, self::RetrieveModelFileList('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_LIBRARY_DIRECTORY));
            }
        }

        $_ignoredClasses = array('SWIFT_TestCase', 'SWIFT_GatewayTransportICICI');

        $_dir = $_fileType === self::FILETYPE_MODEL ? 'Models' : 'Library';

        foreach ($_fileList as $_fileContainer) {
            $_modelLoadString = $_fileContainer['name'];
            if (!empty($_fileContainer['prefix'])) {
                $_modelLoadString = $_fileContainer['prefix'] . ':' . $_fileContainer['name'];
            }

            $_className = 'SWIFT_' . $_fileContainer['name'];
            if (in_array($_className, $_ignoredClasses)) {
                continue;
            }
            $_className = prepend_library_namespace([$_fileContainer['prefix'], $_fileContainer['name']], $_fileContainer['name'], $_className, $_dir, $this->GetName());
            if (!class_exists($_className)) {
                require_once $_fileContainer['path'];
            }

            $_ReflectionClassObject = false;

            try {
                $_ReflectionClassObject = new ReflectionClass($_className);
            } catch (Exception $_ExceptionObject) {
                continue;
            }

            if ($_fileType == self::FILETYPE_MODEL && !$_ReflectionClassObject->isSubclassOf('SWIFT_Model')) {
                continue;
            } else if ($_fileType == self::FILETYPE_LIBRARY && !$_ReflectionClassObject->isSubclassOf('SWIFT_Library')) {
                continue;
            }

            $_ReflectionMethodObject = false;
            try {
                $_ReflectionMethodObject = new ReflectionMethod($_className, $_restrictStaticFunctionName);
            } catch (Exception $_ExceptionObject) {
                continue;
            }

            if (!$_ReflectionMethodObject->isStatic()) {
                continue;
            }

            $_returnContainer[] = array($_modelLoadString, $_fileContainer['name'], $_fileContainer['path'], $this->GetName());
        }

        return $_returnContainer;
    }

    /**
     * Retrieve a list of model files
     *
     * @author Varun Shoor
     * @param string $_directoryPath
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function RetrieveModelFileList($_directoryPath, $_directoryPrefix = '')
    {
        $_fileList = array();

        if (!file_exists($_directoryPath) || !is_dir($_directoryPath)) {
            return $_fileList;
        }

        if ($_directoryHandle = opendir($_directoryPath)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                $_filePath = $_directoryPath . '/' . $_fileName;

                if ($_fileName != '.' && $_fileName != '..' && !is_dir($_filePath)) {
                    $_pathInfoContainer = pathinfo($_filePath);
                    if (isset($_pathInfoContainer['extension']) && $_pathInfoContainer['extension'] == 'php') {
                        $_matches = array();
                        if (preg_match('/^class.SWIFT_([a-zA-Z0-9]+).php$/i', $_pathInfoContainer['basename'], $_matches)) {
                            $_fileList[] = array('prefix' => $_directoryPrefix, 'path' => $_filePath, 'name' => $_matches[1]);
                        }
                    }
                } else if ($_fileName != '.' && $_fileName != '..' && is_dir($_filePath)) {
                    $_newPrefix = $_fileName;
                    if (!empty($_directoryPrefix)) {
                        $_newPrefix = $_directoryPrefix . ':' . $_fileName;
                    }

                    $_fileList = array_merge($_fileList, self::RetrieveModelFileList($_filePath, $_newPrefix));
                }
            }

            closedir($_directoryHandle);
        }

        return $_fileList;
    }

    /**
     * Retrieve the Theme Path
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetThemePath()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return SWIFT::Get('themepath' . $this->GetName());
    }

    /**
     * Add an appName to installedApps Array
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     */
    public static function AddToInstalledApps($_appName) {

        self::$_installedApps[$_appName] = '1';

        return true;
    }

    /**
     * Default apps are not rendered in admin -> apps section to prevent install/uninstall options.
     *
     * @author Pankaj Garg <pankaj.garg@opencart.com.vn>
     *
     * @param string $appName
     *
     * @return bool
     */
    static function IsDefaultApp($appName)
    {
        return in_array($appName, SWIFT::Get('DefaultApps'));
    }
}
?>
