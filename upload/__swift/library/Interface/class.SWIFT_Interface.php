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

use Base\Models\Staff\SWIFT_StaffLoginLog;

/**
 * The Core Interface Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Interface extends SWIFT_Base
{
    private $_currentInterface = false;

    // Core Constants (for adding more, please update GetName() and IsValidInterfaceType() functions in this class)
    const INTERFACE_API = 10;
    const INTERFACE_STAFF = 20;
    const INTERFACE_ADMIN = 30;
    const INTERFACE_CLIENT = 40;
    const INTERFACE_WINAPP = 50;
    const INTERFACE_CONSOLE = 60;
    const INTERFACE_SETUP = 70;
    const INTERFACE_VISITOR = 80;
    const INTERFACE_CALLBACK = 90;
    const INTERFACE_CRON = 100;
    const INTERFACE_CHAT = 110;
    const INTERFACE_PDA = 120;
    const INTERFACE_RSS = 130;
    const INTERFACE_SYNCWORKS = 140;
    const INTERFACE_INSTAALERT = 150;
    const INTERFACE_MOBILE = 160;
    const INTERFACE_ARCHIVE = 170;
    const INTERFACE_INTRANET = 180;
    const INTERFACE_GEOIP = 200;
    const INTERFACE_STAFFAPI = 210;
    const INTERFACE_TESTS = 220;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_interfaceType The Interface Typee
     * @throws SWIFT_Exception If the Interface Object could not be Created
     */
    public function __construct($_interfaceType)
    {
        if (!$_interfaceType || !self::IsValidInterfaceType($_interfaceType))
        {    }

        $this->SetInterface($_interfaceType);

        if (!$this->Initialize())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        if (!$this->RunChecks())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        parent::__construct();

        if (!$this->LoadControllers())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }
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
     * Sets the default interface
     *
     * @author Varun Shoor
     * @param int $_interfaceType The Interface Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetInterface($_interfaceType)
    {
        if (empty($_interfaceType) || !self::IsValidInterfaceType($_interfaceType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_currentInterface = $_interfaceType;

        return true;
    }

    /**
     * Get the current interface type
     *
     * @author Varun Shoor
     * @return int "_currentInterface" (INT) on Success, "false" otherwise
     */
    public function GetInterface()
    {
        return $this->_currentInterface;
    }

    /**
     * Load the Interface Settings
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadSettings()
    {
        if (!$this->ProcessSWIFTPath())
        {
            return false;
        }

        return true;
    }

    /**
     * Gets the textual representation for the interface
     *
     * @author Varun Shoor
     * @return mixed Interface Name (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not loaded
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        switch ($this->GetInterface())
        {
            case self::INTERFACE_API:
                return 'api';
            break;

            case self::INTERFACE_TESTS:
                return 'tests';
            break;

            case self::INTERFACE_ADMIN:
                return 'admin';
            break;

            case self::INTERFACE_STAFF:
                return 'staff';
            break;

            case self::INTERFACE_CLIENT:
                return 'client';
            break;

            case self::INTERFACE_WINAPP:
                return 'winapp';
            break;

            case self::INTERFACE_CONSOLE:
                return 'console';
            break;

            case self::INTERFACE_SETUP:
                return 'setup';
            break;

            case self::INTERFACE_VISITOR:
                return 'visitor';
            break;

            case self::INTERFACE_CALLBACK:
                return 'callback';
            break;

            case self::INTERFACE_CRON:
                return 'cron';
            break;

            case self::INTERFACE_CHAT:
                return 'chat';
            break;

            case self::INTERFACE_PDA:
                return 'pda';
            break;

            case self::INTERFACE_RSS:
                return 'rss';
            break;

            case self::INTERFACE_SYNCWORKS:
                return 'syncworks';
            break;

            case self::INTERFACE_INSTAALERT:
                return 'instaalert';
            break;

            case self::INTERFACE_MOBILE:
                return 'mobile';
            break;

            case self::INTERFACE_STAFFAPI:
                return 'staffapi';
            break;

            case self::INTERFACE_ARCHIVE:
                return 'archive';
            break;

            case self::INTERFACE_INTRANET:
                return 'intranet';
            break;

            case self::INTERFACE_GEOIP:
                return 'geoip';
            break;

            default:
            break;
        }

        return false;
    }

    /**
     * Checks to see if the given interface is valid
     *
     * @author Varun Shoor
     * @param int $_interfaceType The Interface Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidInterfaceType($_interfaceType)
    {
        $_interfaceType = $_interfaceType;
        if ($_interfaceType == self::INTERFACE_API || $_interfaceType == self::INTERFACE_STAFF || $_interfaceType == self::INTERFACE_ADMIN || $_interfaceType == self::INTERFACE_CLIENT
                || $_interfaceType == self::INTERFACE_WINAPP || $_interfaceType == self::INTERFACE_CONSOLE || $_interfaceType == self::INTERFACE_SETUP || $_interfaceType == self::INTERFACE_VISITOR
                || $_interfaceType == self::INTERFACE_CALLBACK || $_interfaceType == self::INTERFACE_CRON || $_interfaceType == self::INTERFACE_CHAT || $_interfaceType == self::INTERFACE_PDA
                || $_interfaceType == self::INTERFACE_RSS || $_interfaceType == self::INTERFACE_SYNCWORKS || $_interfaceType == self::INTERFACE_INSTAALERT || $_interfaceType == self::INTERFACE_MOBILE
                || $_interfaceType == self::INTERFACE_ARCHIVE || $_interfaceType == self::INTERFACE_INTRANET || $_interfaceType == self::INTERFACE_GEOIP
                || $_interfaceType == self::INTERFACE_STAFFAPI || $_interfaceType == self::INTERFACE_TESTS)
        {
            return true;
        }

        return false;
    }

    /**
     * Loads the relevant controller file depending upon the interface
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Controller is Specified
     */
    private function LoadControllers()
    {
        $_controllerName = Clean($this->GetName());

        if (empty($_controllerName))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_controllerParentFileList = array();

        if ($this->GetInterface() == self::INTERFACE_STAFF || $this->GetInterface() == self::INTERFACE_ADMIN || $this->GetInterface() == self::INTERFACE_PDA || $this->GetInterface() == self::INTERFACE_INTRANET)
        {
            $_controllerParentFileList[] = './'. SWIFT_BASEDIRECTORY .'/'. SWIFT_LIBRARYDIRECTORY .'/Controllers/class.Controller_StaffBase.php';
        }

        $_controllerParentFileList[] = './'. SWIFT_BASEDIRECTORY .'/'. SWIFT_LIBRARYDIRECTORY .'/Controllers/class.Controller_' . $_controllerName . '.php';

        if ($this->GetInterface() == self::INTERFACE_CONSOLE) {
            $_controllerParentFileList[] = './'. SWIFT_BASEDIRECTORY .'/'. SWIFT_LIBRARYDIRECTORY .'/Controllers/class.Controller_console_JobQueue.php';
        }

        foreach ($_controllerParentFileList as $_filePath)
        {
            if (file_exists($_filePath))
            {
                require_once ($_filePath);
            }
        }

        return true;
    }


    /**
     * Processes the SWIFT Path Variable
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function ProcessSWIFTPath()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_domainData = array();
        $_domainData = parse_url($_SWIFT->Settings->Get('general_producturl'));
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']))
        {
            $_httpsChunk = '';
            if (isset($_SERVER['HTTPS']) && ((int) ($_SERVER['HTTPS']) != 0 || strtolower($_SERVER['HTTPS']) == 'on'))
            {
                $_httpsChunk = 's';
            } else if (strtolower(substr($_SWIFT->Settings->Get('general_producturl'), 0, strlen('https'))) == 'https') {
                $_httpsChunk = 's';
            }

            $_selfURL = sprintf('http%s://%s%s', $_httpsChunk, $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
            if ($this->GetInterface() == self::INTERFACE_CLIENT || $this->GetInterface() == self::INTERFACE_VISITOR
                    || $this->GetInterface() == self::INTERFACE_RSS || !$_SWIFT->Settings->Get('general_producturl')) {
                $_domainData = parse_url($_selfURL);
            }
        }

        $_finalPort = '';
        if (isset($_domainData['port'])) {
            $_finalPort = ':' . $_domainData['port'];
        }

        if (!empty($_domainData['user'])) {
            $_swiftPath = $_domainData['scheme'] . '://' . $_domainData['user'] . ':' . $_domainData['pass'] . '@' . $_domainData['host'] . $_finalPort . $_domainData['path'];
        } else if (isset($_domainData['scheme'], $_domainData['host'], $_domainData['path'])) {
            $_swiftPath = $_domainData['scheme'] . '://' . $_domainData['host'] . $_finalPort . $_domainData['path'];
        } else {
            $_swiftPath = $_SWIFT->Settings->Get('general_producturl');
            $_domainData = parse_url($_swiftPath);
        }

        // If base name is empty and we arent in setup, we override it with the path in settings
        if ($this->GetInterface() != self::INTERFACE_SETUP && SWIFT_BASENAME == '' && $_SWIFT->Settings->Get('general_producturl') == $_swiftPath) {
            $_swiftPath  = $_SWIFT->Settings->Get('general_producturl');
            $_domainData = parse_url($_swiftPath);
        } else if (SWIFT_BASENAME == '' && $_SWIFT->Settings->Get('general_producturl') != $_swiftPath && defined('SWIFT_TEMPLATE_GROUP') && SWIFT_TEMPLATE_GROUP) {
            // Check if secondary URL is in template group
            if (strpos(SWIFT_TEMPLATE_GROUP, "http") !== false) {
                $_swiftPath = SWIFT_TEMPLATE_GROUP;
            } else {
                $_domainData = parse_url($_swiftPath);
                $_swiftPath  = $_domainData['scheme'] . '://' . $_domainData['host'];
            }
        } else if ($this->GetInterface() != self::INTERFACE_SETUP && SWIFT_BASENAME == '') {
            $_swiftPath  = $_SWIFT->Settings->Get('general_producturl');
            $_domainData = parse_url($_swiftPath);
        }

        $_interfaceName = $this->GetName();
        if ($this->GetInterface() == self::INTERFACE_CLIENT)
        {
            $_interfaceName = 'index.php';
        }

        if (substr($_swiftPath, -1) == '/') {
            $_swiftPath = substr($_swiftPath, 0, strlen($_swiftPath)-1);
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1902 SWIFT Interface names can not be used in sub-domain names
         *
         */
        $_checkPath = '';
        if (isset($_domainData['path']) && !empty($_domainData['path'])) {
            $_checkPath = $_domainData['path'];
            if (substr($_checkPath, 0, 1) != '/') {
                $_checkPath = '/' . $_checkPath;
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1910 Product URL needs to be sanitized
         *
         * Comments:
         */
        $_hasTriggeredIndexPHP = false;
        while (strpos($_swiftPath, '/index.php')) {
            $_hasTriggeredIndexPHP = true;
            $_swiftPath = substr($_swiftPath, 0, strrpos($_swiftPath, '/index.php'));
        }

        if ($_hasTriggeredIndexPHP) {
            $_swiftPath .= '/index.php';
        }

        if (strstr($_checkPath, '/' . $_interfaceName)) {
            $_swiftPath = substr($_swiftPath, 0, strrpos($_swiftPath, '/' . $_interfaceName));

        } else if (strstr($_swiftPath, '/index.php')) {
            $_swiftPath = substr($_swiftPath, 0, strrpos($_swiftPath, '/index.php'));
        }

        if (substr($_swiftPath, -1) != "/") {
            $_swiftPath .= '/';
        }

        SWIFT::Set('themepathglobal', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/' . SWIFT_THEMEGLOBAL_DIRECTORY . '/');
        SWIFT::Set('swiftpath', $_swiftPath);
        if ($this->GetInterface() == self::INTERFACE_CLIENT || $this->GetInterface() == self::INTERFACE_RSS || $this->GetInterface() == self::INTERFACE_VISITOR) {
            SWIFT::SetThemePath('themepath', '_themePath', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/client/');
            SWIFT::SetThemePath('themepathinterface', '_themePathInterface', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/client/');
            SWIFT::SetThemePath('themepathimages', '_themePathImages', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/client/images/');
        } else {
            SWIFT::SetThemePath('themepath', '_themePath', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/__cp/');
            SWIFT::SetThemePath('themepathinterface', '_themePathInterface', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/' . $this->GetName() . '/');
            SWIFT::SetThemePath('themepathimages', '_themePathImages', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/__cp/images/');
            SWIFT::SetThemePath('clientthemepath', '_clientThemePath', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/client/');
            SWIFT::SetThemePath('clientthemepathimages', '_clientThemePathImages', $_swiftPath . SWIFT_BASEDIRECTORY . '/themes/client/images/');
        }

        if ($this->GetInterface() == self::INTERFACE_CLIENT) {
            SWIFT::Set('interfacepath', $_swiftPath);
        } else {
            SWIFT::Set('interfacepath', $_swiftPath . $_interfaceName . '/');
        }

        foreach (SWIFT_App::GetInstalledApps() as $_appName) {

            $_SWIFT_AppObject = false;
            try {
                $_SWIFT_AppObject = new SWIFT_App($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if ($_SWIFT_AppObject instanceof SWIFT_App && $_SWIFT_AppObject->GetIsClassLoaded()) {
                $_appDirectory = $_SWIFT_AppObject->GetDirectory();

                if (substr($_appDirectory, 0, strlen('./' . SWIFT_BASE_DIRECTORY)) == './' . SWIFT_BASE_DIRECTORY) {
                    SWIFT::SetThemePath('themepath' . strtolower($_appName), '_themePath_' . strtolower($_appName), $_swiftPath . SWIFT_BASE_DIRECTORY . '/' . SWIFT_COREAPPSDIRECTORY . '/' . $_appName . '/themes/' . $this->GetName() . '/');
                } else {
                    SWIFT::SetThemePath('themepath' . strtolower($_appName), '_themePath_' . strtolower($_appName), $_swiftPath . SWIFT_APPS_DIRECTORY . '/' . $_appName . '/themes/' . $this->GetName() . '/');
                }
            }
        }

        if ($this->GetInterface() == self::INTERFACE_CLIENT) {
            SWIFT::Set('interfacepath', $_swiftPath);
        } else {
            SWIFT::Set('interfacepath', $_swiftPath . $_interfaceName . '/');
        }

        return true;
    }


    /**
     * Runs interface specific checks
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function RunChecks()
    {
        $_interfaceType = $this->GetInterface();

        if ($_interfaceType == self::INTERFACE_STAFF || $_interfaceType == self::INTERFACE_ADMIN || $_interfaceType == self::INTERFACE_SETUP ||
                $_interfaceType == self::INTERFACE_PDA || $_interfaceType == self::INTERFACE_SYNCWORKS || $_interfaceType == self::INTERFACE_INSTAALERT ||
                $_interfaceType == self::INTERFACE_MOBILE || $_interfaceType == self::INTERFACE_SETUP || $_interfaceType == self::INTERFACE_INTRANET || $_interfaceType == self::INTERFACE_STAFFAPI)
        {
            $this->ErrorIfIPRestricted();
        }

        if ($_interfaceType == self::INTERFACE_ADMIN) {
            $this->ErrorIfAdminIPRestricted();
        } else if ($_interfaceType == self::INTERFACE_WINAPP) {
            $this->ErrorIfWinappIPRestricted();
        }

        // If we are in setup, we dont load ANY caches..
        if ($_interfaceType == self::INTERFACE_SETUP)
        {
            return true;
        }

        // Loading Core cache here.
        $this->Cache->Queue('settingscache');
        $this->Cache->Queue('languagecache');
        $this->Cache->Queue('templategroupcache');
        $this->Cache->Queue('usergroupcache');
        $this->Cache->Queue('usergroupsettingcache');

        return true;
    }

    /**
     * Checks to see if the current IP is restricted
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the IP is Restricted
     */
    private function ErrorIfIPRestricted()
    {
        if (_is_array(SWIFT::Get('iprestrict')))
        {
            if ((!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR'])) && defined('SETUP_CONSOLE') && constant('SETUP_CONSOLE') == '1') {
                return false;
            }

            // Seems like we have IPs specified.. by default deny all requests
            foreach (SWIFT::Get('iprestrict') as $_ipAddress)
            {
                if (NetMatch($_ipAddress, $_SERVER['REMOTE_ADDR']))
                {
                    return false;
                }
            }

            /*
             * BUG FIX Ravinder Singh
             *
             * SWIFT-2369 'IP not allowed' on the control panels should return HTTP 403 Forbidden
             *
             * Comments: None
             */
            header('HTTP/1.0 403 Forbidden');
            echo sprintf('Access Denied (%s): IP not allowed (%s), please add the IP in the allowed list under /config/config.php', SWIFT_INTERFACE, $_SERVER['REMOTE_ADDR']);

            log_error_and_exit();
        }

        return false;
    }

    /**
     * Checks to see if the current Admin IP is restricted
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the IP is Restricted
     */
    private function ErrorIfAdminIPRestricted()
    {
        if (_is_array(SWIFT::Get('adminiprestrict')))
        {
            // Seems like we have IPs specified.. by default deny all requests
            foreach (SWIFT::Get('adminiprestrict') as $_ipAddress)
            {
                if (NetMatch($_ipAddress, $_SERVER['REMOTE_ADDR']))
                {
                    return false;
                }
            }

            /*
             * BUG FIX Ravinder Singh
             *
             * SWIFT-2369 'IP not allowed' on the control panels should return HTTP 403 Forbidden
             *
             * Comments: None
             */
            header('HTTP/1.0 403 Forbidden');
            echo sprintf('Access Denied (%s): ADMIN IP not allowed (%s), please add the IP in the allowed list under /config/config.php', SWIFT_INTERFACE, $_SERVER['REMOTE_ADDR']);

            log_error_and_exit();
        }

        return false;
    }

    /**
     * Checks to see if current Winapp IP is restricted or is a QuickSupport IP
     *
     * @author Ravinder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function ErrorIfWinappIPRestricted()
    {
        if (_is_array(SWIFT::Get('iprestrict')))
        {
            // First check if IP is in allowed list
            foreach (SWIFT::Get('iprestrict') as $_ipAddress)
            {
                if (NetMatch($_ipAddress, $_SERVER['REMOTE_ADDR']))
                {
                    return false;
                }
            }

            // IP is not in allowed list. Verify if X-Originated-From is in allowed list
            $_forwardedIPIsAllowed = false;

            if (defined('ENABLECHATGATEWAYBYPASS') && ENABLECHATGATEWAYBYPASS === true && isset($_SERVER['HTTP_X_ORIGINATED_FROM']) && !empty($_SERVER['HTTP_X_ORIGINATED_FROM']))
            {
                foreach (SWIFT::Get('iprestrict') as $_ipAddress)
                {
                    if (NetMatch($_ipAddress, $_SERVER['HTTP_X_ORIGINATED_FROM']))
                    {
                        $_forwardedIPIsAllowed = true;

                        break;
                    }
                }

                $_gatewayIP = $_SERVER['REMOTE_ADDR'];

                if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !stristr($_SERVER['HTTP_X_FORWARDED_FOR'], ','))
                {
                    $_gatewayIP = GetClientIPFromXForwardedFor($_SERVER['HTTP_X_FORWARDED_FOR']);
                }

                if ($_forwardedIPIsAllowed === true  && IsQuickSupportIP($_gatewayIP))
                {
                    return false;
                }
            }
            echo sprintf('Access Denied (%s): WINAPP IP not allowed (%s), please add the IP in the allowed list under /config/config.php', SWIFT_INTERFACE, $_SERVER['REMOTE_ADDR']);

            log_error_and_exit();
        }

        return false;
    }

    /**
     * Loads the interface based on SWIFT_INTERFACE global constant
     *
     * @author Varun Shoor
     * @return mixed "SWIFT_Interface" Object on Success, "false" otherwise
     * @throws SWIFT_Exception If the Interface is not defined
     */
    public static function Load()
    {
        if (!defined('SWIFT_INTERFACE'))
        {
            throw new SWIFT_Exception('Interface not defined');

            return false;
        }

        $_SWIFT_InterfaceObject = self::GetInterfaceFromString(SWIFT_INTERFACE);
        if (!$_SWIFT_InterfaceObject || !$_SWIFT_InterfaceObject instanceof SWIFT_Interface || !$_SWIFT_InterfaceObject->GetIsClassLoaded())
        {
            return false;
        }

        return $_SWIFT_InterfaceObject;
    }

    /**
     * Retrieves the Interface object from the string value
     *
     * @author Varun Shoor
     * @param string $_interfaceString The String Representation of Interface. Example: ADMIN, CRON, SETUP
     * @return mixed "SWIFT_Interface" (object) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Interface is not defined or If Invalid Interface is Provided
     */
    public static function GetInterfaceFromString($_interfaceString)
    {
        $_interfaceString = strtoupper(Clean($_interfaceString));

        $_interfaceConstant = 'SWIFT_Interface::INTERFACE_' . $_interfaceString;
        if (!defined($_interfaceConstant))
        {
            throw new SWIFT_Exception('Interface not defined');

            return false;
        }

        $_interfaceType = constant($_interfaceConstant);
        if (!$_interfaceType || !self::IsValidInterfaceType($_interfaceType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return new SWIFT_Interface($_interfaceType);
    }

    /**
     * Get the Interface Label
     *
     * @author Varun Shoor
     * @param int $_interfaceType The Interface Type
     * @return string "_interfaceTitle" on Success, "false" otherwise
     */
    public static function GetInterfaceLabel($_interfaceType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_ADMIN) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_admin');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_STAFF) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_staff');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_INTRANET) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_intranet');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_API) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_api');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_TESTS) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_tests');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_WINAPP) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_winapp');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_SYNCWORKS) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_syncworks');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_INSTAALERT) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_instaalert');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_PDA) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_pda');
        } else if ($_interfaceType == SWIFT_StaffLoginLog::INTERFACE_RSS) {
            $_interfaceTitle = $_SWIFT->Language->Get('interface_rss');
        } else {
            return '';
        }

        return $_interfaceTitle;
    }
}
