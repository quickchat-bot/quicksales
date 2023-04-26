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

/**
 * The SWIFT Setup Management Class
 *
 * @author Varun Shoor
 *
 *
 */
class SWIFT_Setup extends SWIFT_Model
{
    private $_setupStatus = false;
    private $_setupMode = 0;
    private $_logContainer = array();

    public $_databaseEmpty = true;

    /** @var SWIFT_Console */
    public $Console;

    // Core Constants
    const MODE_HTTP = 1;
    const MODE_CLI = 2;

    const VERSION_PHP = '7.1.0';
    const VERSION_MYSQL = '5.0';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_setupMode The Setup Mode
     */
    public function __construct($_setupMode = 0)
    {
        if (!self::IsValidMode($_setupMode))
        {
            $_setupMode = self::MODE_HTTP;
        }

        parent::__construct();

        $this->SetStatus(true);
        $this->SetMode($_setupMode);
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Checks to see if its a valid setup mode
     *
     * @author Varun Shoor
     * @param int $_setupMode The Setup Mode
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMode($_setupMode)
    {
        if ($_setupMode == self::MODE_HTTP || $_setupMode == self::MODE_CLI)
        {
            return true;
        }

        return false;
    }

    /**
     * Set the Setup Mode
     *
     * @author Varun Shoor
     * @param int $_setupMode The Setup Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function SetMode($_setupMode)
    {
        if (!self::IsValidMode($_setupMode))
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return true;
        }

        if ($_setupMode == self::MODE_CLI)
        {
            $this->Load->Library('Console:Console');
        }

        $this->_setupMode = $_setupMode;
    }

    /**
     * Retrieve the Setup Mode
     *
     * @author Varun Shoor
     * @return mixed "_setupMode" (INT) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetMode()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_setupMode;
    }

    /**
     * Set the Setup Status
     *
     * @author Varun Shoor
     * @param bool $_setupStatus The Setup Status
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetStatus($_setupStatus = true)
    {
        if (!is_bool($_setupStatus))
        {
            return false;
        }

        $this->_setupStatus = $_setupStatus;

        return true;
    }

    /**
     * Retrieve the current setup status
     *
     * @author Varun Shoor
     * @return bool "_setupStatus" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetStatus()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_setupStatus;
    }

    /**
     * Rebuilds All Core Caches
     *
     * @author Varun Shoor
     * @param array $_appList (OPTIONAL) The App List to Filter Results By
     * @param bool $_isUpgrade Whether this is being called from upgrade
     * @return bool "true" on Success, "false" otherwise
     */
    public function RebuildAllCaches($_appList = null, $_isUpgrade = false)
    {
        // Update Version Number
        $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('data' => SWIFT_VERSION), 'UPDATE', "section = 'core' AND vkey = 'version'");

        if (_is_array($_appList) && in_array(APP_BASE, $_appList)) {
            if (!$_isUpgrade) {
                $_languageContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "languages");
                $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('languageid' => $_languageContainer['languageid']),
                    'UPDATE', "1 = 1");
            }
        }

        SWIFT_CacheManager::RebuildEntireCache();

        return true;
    }

    /**
     * Installs ALL Apps
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function InstallAllApps()
    {
        $_appList = SWIFT_App::ListApps();
        foreach ($_appList as $_key => $_val)
        {
            $_SWIFT_SetupDatabaseObject = $this->LoadAppSetupObject($_val);
            if (!$_SWIFT_SetupDatabaseObject)
            {
                return false;
            }

            $this->InstallApp($_val, $_SWIFT_SetupDatabaseObject);
        }

        return true;
    }

    /**
     * Loads the App Setup Database Object
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return SWIFT_SetupDatabase|null
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function LoadAppSetupObject($_appName)
    {
        $_appName = Clean($_appName);

        $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
        if (!$_appDirectory)
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);
        }

        $_appSetupClassName = 'SWIFT_SetupDatabase_' . $_appName;
        $_appSetupFile = $_appDirectory . '/' . SWIFT_CONFIGDIRECTORY . '/class.'. $_appSetupClassName . '.php';
        $_appSetupClassName = prepend_app_namespace($_appName, $_appSetupClassName);

        if (!file_exists($_appSetupFile))
        {
            $this->AddToLog('[ERROR]: Failed to Locate Setup file for App: '. $_appName);

            throw new SWIFT_Setup_Exception('[ERROR]: Failed to Locate Setup file for App: '. $_appName);
        }

        require_once ($_appSetupFile);
        if (!class_exists($_appSetupClassName, false))
        {
            $this->AddToLog('[ERROR]: Failed to Locate Setup Class for App: '. $_appName);

            throw new SWIFT_Setup_Exception('[ERROR]: Failed to Locate Setup Class for App: '. $_appName);
        }

        $_SWIFT_SetupDatabaseObject = new $_appSetupClassName();
        if (!$_SWIFT_SetupDatabaseObject instanceof SWIFT_SetupDatabase || !$_SWIFT_SetupDatabaseObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);
        }

        return $_SWIFT_SetupDatabaseObject;
    }

    /**
     * Retrieve the APp Config XML
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @return SimpleXMLElement
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetAppConfig($_appName)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
        $_configFile = $_appDirectory . '/' . SWIFT_App::DIRECTORY_CONFIG . '/' . SWIFT_App::FILE_CONFIG;

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
     * Installs the given App
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @param SWIFT_SetupDatabase $_SWIFT_SetupDatabaseObject The App Setup Database Object
     * @param int $_pageIndex The Page Index (0 = Install All Pages)
     * @return int
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function InstallApp($_appName, SWIFT_SetupDatabase $_SWIFT_SetupDatabaseObject, $_pageIndex = 0)
    {
        if (!$_SWIFT_SetupDatabaseObject instanceof SWIFT_SetupDatabase || !$_SWIFT_SetupDatabaseObject->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);
        }

        $_SimpleXMLObject = $this->GetAppConfig($_appName);

        $_appVersion = SWIFT_VERSION;
        if (isset($_SimpleXMLObject->version))
        {
            $_appVersion = (string)$_SimpleXMLObject->version;
        }

        $_pageIndex = $_pageIndex;
        $_appPageNumbers = $_SWIFT_SetupDatabaseObject->GetPageCount();
        if (empty($_appPageNumbers))
        {
            $_appPageNumbers = 1;
        }

        SWIFT_Loader::AddApp(SWIFT_App::Get($_appName));

        $_SWIFT_SetupDatabaseObject->LoadTables();

        if (empty($_pageIndex))
        {
            for ($_ii=1; $_ii<=$_appPageNumbers; $_ii++)
            {
                $_SWIFT_SetupDatabaseObject->Install($_ii);
                $_SWIFT_SetupDatabaseObject->ExecuteQueue();

                $_pageIndex = $_ii;
            }
        } else {
            $_SWIFT_SetupDatabaseObject->Install($_pageIndex);
            $_SWIFT_SetupDatabaseObject->ExecuteQueue();
        }

        $this->StatusList($_SWIFT_SetupDatabaseObject->_setupStatusList);

        if ($_appPageNumbers == $_pageIndex)
        {
            // Install the App

            $this->Settings->UpdateKey('installedapps', $_appName, '1', true);
            $this->Settings->UpdateKey('appversions', $_appName, $_appVersion, true);
        }

        return $_pageIndex;
    }

    /**
     * Cook up the Product URL
     *
     * @author Varun Shoor
     * @return string The Possible Product URL
     */
    public static function GetProductURL()
    {
        $_isHTTPS = false;
        if(isset($_SERVER['HTTPS']) && ((int) ($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'on'))
        {
            $_isHTTPS = true;
        }

        $_selfURL = sprintf('http%s://%s%s',(isset($_SERVER['HTTPS']) && ((int) ($_SERVER['HTTPS']) != 0 || strtolower($_SERVER['HTTPS']) == 'on')? 's': ''),$_SERVER['HTTP_HOST'],$_SERVER['REQUEST_URI']);
        $_domainData = parse_url($_selfURL);
        $_finalPort = '';
        if (isset($_domainData['port'])) {
            $_finalPort = ':' . $_domainData['port'];
        }

        $_swiftPath = '';
        if (!empty($_domainData["user"])) {
            $_swiftPath = $_domainData["scheme"]."://".$_domainData["user"].":".$_domainData["pass"]."@".$_domainData["host"].$_finalPort.'/'.substr($_domainData["path"], 1, strrpos($_domainData["path"],"/"));
        } else if (isset($_domainData['scheme'], $_domainData['host'], $_domainData['path'])) {
            $_swiftPath = $_domainData["scheme"]."://".$_domainData["host"].$_finalPort.'/'.substr($_domainData["path"], 1, strrpos($_domainData["path"],"/"));
        } else {
            $_swiftPath = "http" . IIF($_isHTTPS, 's', ''). "://" . $_SERVER["SERVER_NAME"]."/" . substr($_SERVER["PHP_SELF"], 1, strrpos($_SERVER["PHP_SELF"],"/"));
        }

        // Cook up the product URL
        $_setupLocation = $_swiftPath;
        if (isset($_POST["producturl"]) && trim($_POST['producturl']) != "")
        {
            $_setupLocation = $_POST["producturl"];
        } else {
            $_setupLocation = substr($_setupLocation, 0, strlen($_setupLocation)-strlen("setup/"));
        }

        $_setupLocation = StripTrailingSlash($_setupLocation) . '/';

        return $_setupLocation;
    }

    /**
     * Checks to see if the database is empty
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Class is not Loaded
     */
    public function CheckDatabaseIsEmpty($_isCleared = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_tableResult = $this->Database->GetADODBObject()->MetaTables('TABLES');

        if (count($_tableResult) && !$_isCleared)
        {
            $this->_databaseEmpty = false;

            $this->Message($this->Language->Get('scdbnotempty'));

            return false;
        }

        return true;
    }

    /**
     * Checks to see if its a valid PHP version
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidPHPVersion()
    {
        $_versionResult = version_compare(phpversion(), self::VERSION_PHP);

        if ($_versionResult == -1)
        {
            return false;
        }

        return true;
    }

    /**
     * Check to see if file uploads are enabled
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsFileUploadEnabled()
    {
        if (ini_get("file_uploads") != "1")
        {
            return false;
        }

        return true;
    }

    /**
     * Check to see if magic quotes GPC is disabled
     *
     * @author Ravinder Singh
     * @return bool "true" if magic_quotes_gpc is disabled, "false" otherwise
     */
    public static function ISMagicQuotesGPCOff()
    {
        if (!strcasecmp(ini_get("magic_quotes_gpc"), "On") || ini_get("magic_quotes_gpc") == "1")
        {
            return false;
        }

        return true;
    }

    /**
     * Check to see if strict mode is enabled
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsStrictModeDisabled() {
        $_SWIFT = SWIFT::GetInstance();

        if (strtolower(DB_TYPE) != 'mysql' || strtolower(DB_TYPE) == 'mysqli') {
            return true;
        }

        foreach (array('@@GLOBAL.sql_mode', '@@SESSION.sql_mode') as $_sqlMode) {
            $_SWIFT->Database->Query("SELECT " . $_sqlMode . " AS sqlmode");
            while ($_SWIFT->Database->NextRecord()) {
                if ($_SWIFT->Database->Record['sqlmode'] == 'STRICT_TRANS_TABLES' || $_SWIFT->Database->Record['sqlmode'] == 'STRICT_ALL_TABLES') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check to see if the PHP Extension Exists
     *
     * @author Varun Shoor
     * @param string $_extensionName The Extension Name
     * @param string $_manualURL The URL to the PHP Manual Entry for this Extension
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function CheckForPHPExtension($_extensionName, $_manualURL)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_isLoaded = extension_loaded($_extensionName);
        $_status = '';

        if (!$_isLoaded)
        {
            $_status = sprintf($this->Language->Get('scextensionerror'), $_extensionName, $_manualURL);
        }

        if ($this->GetMode() == self::MODE_HTTP)
        {
            $this->Message(sprintf($this->Language->Get('scextensioncheck'), $_extensionName), IIF($_isLoaded, "<font color='green'>". $this->Language->Get('scinstalled') ."</font>", "<font color='red'>". $this->Language->Get('scmissing') ."</font>"), $_status);
        } else if ($this->GetMode() == self::MODE_CLI) {
            $this->Message(sprintf($this->Language->Get('scextensioncheck'), $_extensionName), IIF($_isLoaded, $this->Console->Green($this->Language->Get('scinstalled')), $this->Console->Red($this->Language->Get('scmissing'))), $_status);
        }

        if (!$_isLoaded)
        {
            $this->SetStatus(false);

            return false;
        }

        return true;
    }

    /**
     * Runs the system checks and logs/displays the results according to setup mode
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function RunSystemChecks()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!$this->Status($this->Language->Get('sccachedir'), is_writable('./' . SWIFT_BASEDIRECTORY . '/cache'), $this->Language->Get('sccachedirerror')))
        {
            $this->SetStatus(false);
        }

        if (!$this->Status($this->Language->Get('scfilesdir'), is_writable('./' . SWIFT_BASEDIRECTORY . '/files'), $this->Language->Get('scfilesdirerror')))
        {
            $this->SetStatus(false);
        }

        if (!$this->Status($this->Language->Get('sclogsdir'), is_writable('./' . SWIFT_BASEDIRECTORY . '/logs'), $this->Language->Get('sclogsdirerror')))
        {
            $this->SetStatus(false);
        }

        if (!$this->Status($this->Language->Get('scappsdir'), is_writable('./' . SWIFT_APPS_DIRECTORY . ''), $this->Language->Get('scappsdirerror')))
        {
            $this->SetStatus(false);
        }

        if (!$this->Status($this->Language->Get('scgeoipdir'), is_writable('./' . SWIFT_BASEDIRECTORY . '/geoip'), $this->Language->Get('scgeoipdirerror')))
        {
            $this->SetStatus(false);
        }

        if (!$this->Status(sprintf($this->Language->Get('scminphpversion'), self::VERSION_PHP), self::IsValidPHPVersion(), sprintf($this->Language->Get('scminphpversionerror'), self::VERSION_PHP)))
        {
            $this->SetStatus(false);
        }

        if (!$this->Status($this->Language->Get('scfileup'), self::IsFileUploadEnabled(), $this->Language->Get('scfileuperror')))
        {
            $this->SetStatus(false);
        }

        if (!$this->Status($this->Language->Get('scmagicquotesgpc'), self::ISMagicQuotesGPCOff(), $this->Language->Get('scmagicquotesgpcerror')))
        {
            $this->SetStatus(false);
        }

        $_uploadFileSize = ini_get('upload_max_filesize');
        $this->Message($this->Language->Get('scmaxupsize'), IIF(empty($_uploadFileSize), $this->Language->Get('scmaxupnotspec'), $_uploadFileSize), $this->Language->Get('scmaxupsizeerror'));

        $_executionTime = ini_get('max_execution_time');
        $this->Message($this->Language->Get('scmaxexectime'), IIF($_executionTime < 90, sprintf($this->Language->Get('scmaxexectimelessspec'), $_executionTime), $_executionTime), $this->Language->Get('scmaxexectimeerror'));

        $_safeMode = ini_get("safe_mode");
        $this->Message($this->Language->Get('scsafemodecheck'), IIF(empty($_safeMode), $this->Language->Get('scdisabled'), $this->Language->Get('scenabled')));

        // ======= CURL =======
        if (!$this->CheckForPHPExtension('curl', "http://us.php.net/manual/en/curl.installation.php"))
        {
            $this->SetStatus(false);
        }

        // ======= GD2 =======
        if (!$this->CheckForPHPExtension('gd', "http://us.php.net/manual/en/gd.installation.php"))
        {
            $this->SetStatus(false);
        }

        // ====== Freetype2 for GD and captcha =======
        if (!$this->Status($this->Language->Get('ftinstalled'), function_exists('imagecreate') && function_exists('imagettftext'), $this->Language->Get('ftinstallederror')))
        {
            $this->SetStatus(false);
        }

        // ======= MBString =======
        if (!$this->CheckForPHPExtension("mbstring", "http://us.php.net/manual/en/mbstring.installation.php"))
        {
            $this->SetStatus(false);
        }

        // ======= openssl =======
        if (!$this->CheckForPHPExtension("openssl", "http://php.net/manual/en/openssl.installation.php"))
        {
            $this->SetStatus(false);
        }

        // ======= MySQL/MySQLi =======
        if (!$this->CheckForPHPExtension(DB_TYPE, "http://us2.php.net/manual/en/mysql.installation.php"))
        {
            $this->SetStatus(false);
        }

        // ======= Filter =======
        if (!$this->CheckForPHPExtension("filter", "http://us2.php.net/filter"))
        {
            $this->SetStatus(false);
        }

        // ======= SimpleXML =======
        if (!$this->CheckForPHPExtension("simplexml", "http://us2.php.net/simplexml"))
        {
            $this->SetStatus(false);
        }

        // ======= PDO =======
        if (!$this->CheckForPHPExtension("pdo", "http://us2.php.net/pdo"))
        {
            $this->SetStatus(false);
        }

        // ======= PDO_MYSQL =======
        if (!$this->CheckForPHPExtension("pdo_mysql", "http://us2.php.net/pdo_mysql"))
        {
            $this->SetStatus(false);
        }

        // ======= JSON =======
        if (!$this->CheckForPHPExtension("json", "http://us2.php.net/json"))
        {
            $this->SetStatus(false);
        }

        // ======= STRICT MODE =======
        if (!$this->Status($this->Language->Get('scstrictmode'), self::IsStrictModeDisabled(), $this->Language->Get('scstrictmodeerror')))
        {
            $this->SetStatus(false);
        }

        return true;
    }

    /**
     * Adds a log message to the container
     *
     * @author Varun Shoor
     * @param string $_logMessage The Log Message
     * @param bool $_isError (OPTIONAL) Whether this log is an error
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function AddToLog($_logMessage, $_isError = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_logContainer[] = $_logMessage;

        if (isset($this->Console) && $this->Console instanceof SWIFT_Console)
        {
            if ($this->GetMode() === self::MODE_CLI) {
                $_c = $this->Console;
                $_logMessage = preg_replace_callback('/<strong>([^<]+)<\/strong>/', function ($matches) use ($_c) {
                    return $_c->Bold($matches[1]);
                }, $_logMessage);
            }
            $this->Console->Message($_logMessage, IIF($_isError == true, SWIFT_Console::CONSOLE_ERROR, false));
        }

        return true;
    }

    /**
     * Display the status message with confirmation
     *
     * @author Varun Shoor
     * @param string $_statusText The Status Text
     * @param bool $_result The Result of Check
     * @param string $_reasonFailure (OPTIONAL) The Reason for Failure
     * @param bool $_dontSetFailStatus (OPTIONAL) If set to true, the setup will continue to process other entries..
     * @return bool "true" on Success, "false" otherwise
     */
    public function Status($_statusText, $_result = true, $_reasonFailure = '', $_dontSetFailStatus = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_result || !$this->GetIsClassLoaded())
        {
            if (!$_dontSetFailStatus)
            {
                $this->SetStatus(false);
            }

            $this->AddToLog($_statusText . "\n" . 'Reason: ' . $_reasonFailure, true);

        } else {
            $this->AddToLog('[SUCCESS]: ' . $_statusText);
        }

        if ($this->GetMode() == self::MODE_HTTP)
        {
            /** @var View_Setup $view */
            $view = $_SWIFT->Controller->View;
            $view->DisplayStatus($_statusText, $_result, $_reasonFailure);
        }

        return true;
    }

    /**
     * Processes a list of container messages
     *
     * @author Varun Shoor
     * @param array $_statusListContainer The Status List Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function StatusListBasic($_statusListContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!_is_array($_statusListContainer)) {
//            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        foreach ($_statusListContainer as $_key => $_val)
        {
            if (!isset($_val[2]))
            {
                $_val[2] = '';
            }

            if (!isset($_val[1]))
            {
                $_val[1] = true;
            }

            $this->Status($_val[0], $_val[1], $_val[2]);
        }

        return true;
    }

    /**
     * Processes a list of container messages
     *
     * @author Varun Shoor
     * @param array $_statusListContainer The Status List Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function StatusList($_statusListContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!_is_array($_statusListContainer)) {
//            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        foreach ($_statusListContainer as $_key => $_val)
        {
            $this->Status($_val['statusText'], $_val['result'], $_val['reasonFailure']);
        }

        return true;
    }

    /**
     * Display the Message to the End User
     *
     * @author Varun Shoor
     * @param string $_messageText The Message Text
     * @param string $_messageValue (OPTIONAL) The Message Value
     * @param string $_reasonText (OPTIONAL) The Reason Text
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function Message($_messageText, $_messageValue = '', $_reasonText = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->AddToLog('[STATUS]: '. $_messageText . IIF(!empty($_messageValue), ': '. $_messageValue) . IIF(!empty($_reasonText), "\n". 'Reason: '. $_reasonText));

        if ($this->GetMode() == self::MODE_HTTP)
        {
            /** @var View_Setup $view */
            $view = $_SWIFT->Controller->View;
            $view->DisplayMessage($_messageText, $_messageValue, $_reasonText);
        }
    }

    /**
     * Run the final steps for installation
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RunFinalSteps()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_installedAppList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settings WHERE section = 'installedapps'");
        while ($this->Database->NextRecord()) {
            $_installedAppList[] = $this->Database->Record['vkey'];
        }

        if (in_array(APP_REPORTS, $_installedAppList)) {
            SWIFT_Loader::LoadLibrary('Setup:ReportSetup', APP_REPORTS, false);
            SWIFT_ReportSetup::Install();
        }

        return true;
    }
}
?>
