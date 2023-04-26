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
 * The Route Management Class
 *
 * The HTTP Routes will be in format of admin/Core/Departments/Insert, where: admin = Interface, Core = App, Departments = Controller, Insert = Action
 * For dispatching additional parameters admin/Core/Departments/Insert/1/General, where: admin = Interface, Core = App, Departments = Controller, Insert = Action and Action Execution will be: Insert(1, General)
 * (DEPRECATED) Other HTTP Route possibilities are: admin/index.php?_m=core&_a=insert&argument=value&argument2=value AND admin/index.php?_app=core&_action=insert&argument=value&argument2=value
 *
 * @author Varun Shoor
 */
class SWIFT_Router extends SWIFT_Base
{
    private $_AppObject;

    static private $_activeTemplateGroup = false;

    private $_appController;
    private $_appName;
    private $_controllerAction;
    private $_fullArguments = array();
    private $_controllerActionArguments = array();
    private $_uriString;
    private $_rawQueryString;

    static public $_activeURL = false;

    private $_isAssociative = false;

    private $_rawAppName = '';

    // Core Constants
    const DEFAULT_BASENAME = 'index.php';
    const PERMITTED_URICHARACTERS = ',~%.:=_\-';
    const PERMITTED_URICHARACTERSSTRICT = ',~%.:_\-';
    const URL_SUFFIX = '';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_appName The SWIFT App Name
     * @param string $_appController The App Controller Name
     * @param string $_controllerAction The Controller Action
     * @param string $_rawQueryString The Raw Query String
     * @param array $_fullArguments The Full Arguments Arraye
     * @throws SWIFT_Exception If the Router fails to Load
     */
    public function __construct($_appName, $_appController, $_controllerAction, $_controllerActionArguments = array(), $_isAssociative = false, $_rawQueryString = '', $_fullArguments = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_AppObject = SWIFT_App::Get($_appName);

        if (!$this->SetApp($_SWIFT_AppObject) || !$this->SetController($_appController) || !$this->SetAction($_controllerAction) || !$this->SetArguments($_controllerActionArguments)
                || !$this->SetArgumentIsAssociative($_isAssociative) || !$this->SetRawQueryString($_rawQueryString) || !$this->SetFullArguments($_fullArguments))
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_REQUEST['e'])) {
            unset($_GET['e']);
            unset($_REQUEST['e']);
            unset($_POST['e']);
        }

        $this->_rawAppName = $_appName;

        $_SWIFT->SetClass('App', $_SWIFT_AppObject);

        parent::__construct();

        // Core JS Variables
        SWIFT::Set('JSRouterPath', $this->GetJSRouterPath());
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
     * Return the Current URL
     *
     * @author Varun Shoor
     * @return mixed "Current URL" (STRING) on Success, "false" otherwise
     */
    public function GetCurrentURL()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_routerArguments = '';
        if (count($this->GetArguments()))
        {
            $_argumentContainer = $this->GetArguments();
            foreach ($_argumentContainer as $_index => $_argument) {
                if (is_object($_argument) || is_array($_argument))
                {
                    $_argumentContainer[$_index] = '0';
                }
            }

            $_routerArguments = '/' . implode('/', $_argumentContainer);
        }

        return SWIFT::Get('basename') .'/'. $this->_rawAppName . '/' . $this->GetController() . '/' . $this->GetAction() . $_routerArguments;
    }

    /**
     * Retrieve the Arguments as String
     *
     * @author Varun Shoor
     * @return string The Arguments
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetArgumentsAsString() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_routerArguments = $_baseArguments = '';
        if (count($this->GetArguments()))
        {
            $_argumentContainer = $this->GetArguments();
            foreach ($_argumentContainer as $_index => $_argument) {
                if (is_object($_argument) || is_array($_argument))
                {
                    $_argumentContainer[$_index] = '0';
                }
            }

            $_baseArguments = '/' . implode('/', $_argumentContainer);

        }

        $_routerArguments = '/'. $this->_rawAppName . '/' . $this->GetController() . '/' . $this->GetAction() . $_baseArguments;
        if (strtolower(substr($_routerArguments, 0, strlen('/core/default'))) == '/core/default') {
            $_routerArguments = '';
        }

        return $_routerArguments;
    }

    /**
     * Set the Raw Query String
     *
     * @author Varun Shoor
     * @param string $_rawQueryString The Raw Query String
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function SetRawQueryString($_rawQueryString)
    {
        $this->_rawQueryString = $_rawQueryString;

        return true;
    }

    /**
     * Retrieve the Raw Query String
     *
     * @author Varun Shoor
     * @return mixed "$_rawQueryString" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRawQueryString()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_rawQueryString;
    }

    /**
     * Sets the Property of whether the argument is associative
     *
     * @author Varun Shoor
     * @param bool $_isAssociative Whether the argument is associative
     * @return bool "true" on Success, "false" otherwise
     */
    private function SetArgumentIsAssociative($_isAssociative)
    {
        $_isAssociative = (int) ($_isAssociative);

        $this->_isAssociative = $_isAssociative;

        return true;
    }

    /**
     * Retrieve the Is Associative Property of Route Container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetArgumentIsAssociative()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_isAssociative;
    }

    /**
     * Set the App
     *
     * @author Varun Shoor
     * @param SWIFT_App $_SWIFT_AppObject The SWIFT App Object
     * @return bool "true" on Success, "false" otherwise
     * @throw SWIFT_Exception If Invalid Data is Provided
     */
    public function SetApp(SWIFT_App $_SWIFT_AppObject)
    {
        if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_AppObject  = $_SWIFT_AppObject;
        $this->_rawAppName = ucfirst($this->_AppObject->GetName());
        $this->_appName = $this->_AppObject->GetName();

        return true;
    }

    /**
     * Gets the App Object
     *
     * @author Varun Shoor
     * @return SWIFT_App "SWIFT_App" Object on Success, "false" otherwise
     * @throws SWIFT_Exception If Class is not Loaded
     */
    public function GetApp()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_AppObject;
    }

    /**
     * Sets the controller name
     *
     * @author Varun Shoor
     * @param string $_appController The App Controller Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetController($_appController)
    {
        if (empty($_appController))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_appController = $_appController;

        return true;
    }

    /**
     * Get the App Controller
     *
     * @author Varun Shoor
     * @return mixed "_appController" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetController()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_appController;
    }

    /**
     * Set the Controller Action
     *
     * @author Varun Shoor
     * @param string $_controllerAction The Controller Action
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetAction($_controllerAction)
    {
        if (empty($_controllerAction))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_controllerAction = $_controllerAction;

        return true;
    }

    /**
     * Get the Controller Action
     *
     * @author Varun Shoor
     * @return mixed "_controllerAction" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAction()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_controllerAction;
    }

    /**
     * Set the full arguments
     *
     * @author Varun Shoor
     * @param array $_fullArguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetFullArguments($_fullArguments)
    {
        $this->_fullArguments = $_fullArguments;

        return true;
    }

    /**
     * Retrieve Full Arguments
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFullArguments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->_fullArguments;
    }

    /**
     * Set the Controller > Action > Arguments
     *
     * @author Varun Shoor
     * @param array $_controllerActionArguments The Controller Action Arguments
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetArguments($_controllerActionArguments)
    {
        if (!_is_array($_controllerActionArguments))
        {
            return true;
        }

        // Commenting because of conflict with Job Queue

        /*
        foreach ($_controllerActionArguments as $_index => $_argument) {
            if (is_object($_argument) || is_array($_argument))
            {
                $_controllerActionArguments[$_index] = '0';
            }
        }
        */

        $this->_controllerActionArguments = $_controllerActionArguments;

        return true;
    }

    /**
     * Retrieve the Controller > Action > Arguments
     *
     * @author Varun Shoor
     * @param bool $_isRESTCall (OPTIONAL)
     * @return mixed "_controllerActionArguments" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetArguments($_isRESTCall = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_isRESTCall) {
            return array_splice($this->_fullArguments, 2);
        }

        return $this->_controllerActionArguments;
    }

    /**
     * Loads the Router Object with App, Controller, Action and Arguments Parameters
     *
     * @author Varun Shoor
     * @return mixed "$_SWIFT_RouterObject" SWIFT_Router on Success, "false" otherwise
     * @throws SWIFT_Exception When the Router is Unable to Process the Action
     */
    public static function Load()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_queryString = str_replace('%252', '/', self::GetURI());
        $_queryStringFinal = $_queryString;

        // If the query string is empty, then load the default controller
        if (empty($_queryString))
        {
            $_routeContainer = self::GetDefaultRouteContainer();
        } else {
            // Seems like it was not empty, we need to parse the query string so that it starts to make more sense..
            $_queryString = self::RemoveURLSuffix($_queryString);
            $_queryStringSegmentsContainer = self::ExplodeSegments($_queryString);

            $_queryStringSegments = $_queryStringSegmentsContainer['segments'];
            if (!_is_array($_queryStringSegments))
            {
                $_routeContainer = self::GetDefaultRouteContainer();
            } else {
                $_queryStringFinal = $_queryStringSegmentsContainer['querystring'];

                // Parse the URI into an array of app, action and arguments
                $_routeContainer = self::ParseURIToRouteContainer($_queryStringSegments, $_queryStringFinal);
            }
        }

        self::$_activeURL = SWIFT::Get('basename') . $_queryStringFinal;

        $_rawQueryString = '';
        if (isset($_routeContainer['argumentsraw']))
        {
            $_rawQueryString = $_routeContainer['argumentsraw'];
        }

        if (!_is_array($_routeContainer) || empty($_routeContainer['app']) || empty($_routeContainer['controller']) || empty($_routeContainer['action']) ||
                !isset($_routeContainer['isassociative']))
        {
            throw new SWIFT_Exception('No Route Available for Execution');
        }

        $_SWIFT_RouterObject = new SWIFT_Router($_routeContainer['app'], $_routeContainer['controller'], $_routeContainer['action'], $_routeContainer['arguments'], $_routeContainer['isassociative'], $_rawQueryString, $_routeContainer['fullarguments']);

        // Begin Hook: route
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('router')) ? eval($_hookCode) : false;
        // End Hook

        return $_SWIFT_RouterObject;
    }

    /**
     * Execute a Controller Action
     *
     * @author Varun Shoor
     * @param string $_controllerPath The Controller Path
     * @param array $_argumentsContainer The Arguments Container
     * @param string $_controllerParentClass (OPTIONAL) Restrict the controller parent class to a specific type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function Execute($_controllerPath, $_argumentsContainer, $_controllerParentClass = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_queryString = self::RemoveURLSuffix($_controllerPath);
        $_queryStringSegmentsContainer = self::ExplodeSegments($_controllerPath);
        $_queryStringSegments = $_queryStringSegmentsContainer['segments'];

        $_queryStringFinal = $_queryStringSegmentsContainer['querystring'];

        $_routeContainer = self::ParseURIToRouteContainer($_queryStringSegments, $_queryStringFinal);

        $_rawQueryString = '';
        if (isset($_routeContainer['argumentsraw']))
        {
            $_rawQueryString = $_routeContainer['argumentsraw'];
        }

        $_finalArgumentsContainer = array_merge($_routeContainer['arguments'], $_argumentsContainer);

        $_SWIFT_RouterObject = new SWIFT_Router($_routeContainer['app'], $_routeContainer['controller'], $_routeContainer['action'], $_finalArgumentsContainer, $_routeContainer['isassociative'], $_rawQueryString, $_routeContainer['fullarguments']);

        // Load the App
        $_AppObject = $_SWIFT_RouterObject->GetApp();

        // Execute the relevant controller and load the related objects
        $_AppObject->ExecuteController($_SWIFT_RouterObject, $_controllerParentClass);

        return true;
    }

    /**
     * Parses the URI into an array of app, action & arguments
     *
     * @author Varun Shoor
     * @param array $_queryStringSegments The Query String Segments to Process
     * @param string $_queryString The Query String
     * @return mixed "_routeContainer" (Array) on Success, "false" otherwise
     * @throws SWIFT_Exception When the Router Data cannot be Parsed
     */
    static private function ParseURIToRouteContainer($_queryStringSegments, $_queryString)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_queryStringSegments)
        {
            return false;
        }

        $_routeContainer = array();

        $_routeContainer = array();
        $_routeContainer['app'] = $_routeContainer['controller'] = $_routeContainer['action'] = $_routeContainer['arguments'] = false;

        $_SWIFT_AppObject = false;
        try {
            $_SWIFT_AppObject = SWIFT_App::Get($_queryStringSegments[0]);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        // Does the requested app exist?
        if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded())
        {
            FileNotFound('"' . Clean($_queryStringSegments[0]) . '" SWIFT App does not exist');

            log_error_and_exit();
        }

        $_routeContainer['app'] = $_queryStringSegments[0];

        // Does it have a valid interface set?
        $_interfaceName = $_SWIFT->Interface->GetName();
        if (empty($_interfaceName) || !$_SWIFT_AppObject->InterfaceDirectoryExists($_interfaceName))
        {
            FileNotFound('"' . Clean($_interfaceName) . '" Interface directory in SWIFT App "'. Clean($_queryStringSegments[0]) .'" does not exist');

            log_error_and_exit();
        }

        $_controllerName = $_actionName = '';

        // Does it have a valid controller?
        if (isset($_queryStringSegments[1]))
        {
            $_controllerName = $_queryStringSegments[1];
        } else {
            $_controllerName = '';
        }

        if (empty($_controllerName) || !$_SWIFT_AppObject->ControllerFileExists($_interfaceName, $_controllerName))
        {
            // Check for default controller
            $_controllerName = SWIFT_Controller::DEFAULT_CONTROLLER;
            if (empty($_controllerName) || !$_SWIFT_AppObject->ControllerFileExists($_interfaceName, $_controllerName))
            {
                FileNotFound('"' . Clean($_controllerName) . '" Controller OR a DEFAULT Controller in SWIFT App "'. Clean($_queryStringSegments[0]) .'" for Interface "'. Clean($_interfaceName) .'" does not exist (DEFAULT-MAIN)');

                log_error_and_exit();
            }
        }

        $_routeContainer['controller'] = $_controllerName;

        if (isset($_queryStringSegments[2]))
        {
            $_actionName = $_queryStringSegments[2];
        }

        if (empty($_actionName))
        {
            $_actionName = SWIFT_Controller::DEFAULT_ACTION;
        }

        $_routeContainer['action'] = $_actionName;
        $_routeContainer['isassociative'] = false;

        $_fullArgumentsParseResult = self::ParseURIArguments($_queryStringSegments, $_queryString);
        $_routeContainer['fullarguments'] = $_fullArgumentsParseResult[0];

        // Any Arguments?
        if (count($_queryStringSegments) > 3)
        {
            $_rawQueryArray = array_slice($_queryStringSegments, 3);
            $_rawQueryString = implode('/', $_rawQueryArray);

            $_argumentsParseResult = self::ParseURIArguments($_rawQueryArray, $_queryString);
            $_routeContainer['arguments'] = $_argumentsParseResult[0];
            $_routeContainer['argumentsraw'] = '';

            if (count($_rawQueryArray) == 1)
            {
                $_routeContainer['argumentsraw'] = $_rawQueryString;
            }

            if (isset($_argumentsParseResult[1]) && $_argumentsParseResult[1] == true)
            {
                $_routeContainer['isassociative'] = true;
            }
        } else {
            $_routeContainer['arguments'] = array();
        }

        return $_routeContainer;
    }

    /**
     * Parses the arguments
     *
     * @author Varun Shoor
     * @param array $_argumentStringContainer The Arguments Container
     * @param string $_queryString The Query String
     * @return array
     */
    public static function ParseURIArguments($_argumentStringContainer, $_queryString)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_argumentStringContainer))
        {
            return array(array(), false);
        }

        $_argumentContainer = array();
        $_isAssociative = false;
        $_finalChunkArray = array();

        foreach ($_argumentStringContainer as $_key => $_val)
        {
            if (substr($_val, 0, 2) == 'R:')
            {
                continue;
            } else if (substr($_val, 0, 1) == '&') {
                $_finalChunkProcess = substr($_val, 1);
                parse_str($_finalChunkProcess, $_finalChunkArray);

                foreach ($_finalChunkArray as $_chunkKey => $_chunkVal) {
                    if (!isset($_REQUEST[$_chunkKey]) || !isset($_GET[$_chunkKey])) {
                        $_REQUEST[$_chunkKey] = $_GET[$_chunkKey] = $_chunkVal;
                    }
                }
                continue;
            }

            $_matches = $_matches2 = array();
            $_matchResult = preg_match("/([a-zA-Z0-9_]*)=([a-zA-Z\s0-9" . preg_quote(self::PERMITTED_URICHARACTERSSTRICT) . "]+)/", $_val, $_matches);

            // Force base64 hash, if you hit on this part again.. figure out what really is wrong, I changed it so that base64 fetch can work without stripping the '=' at end. Specifically for hash checksum during trial creation [VARUN]
            if (!$_matchResult && count($_argumentStringContainer) >= 1 && substr($_val, -1) == '=') {
                $_matchResult = true;
                $_matches = array('', $_val, '');
            }

            // If we have = in the string, then we parse it differently... but we make sure that its not at end (generally base64 encoded data)
            if (!$_matchResult)
            {
                if (($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP)
                        && isset($GLOBALS['argv'][1])) {
                    $_argumentContainer[] = urldecode($_val);
                } else {
                    $_argumentContainer[] = Clean(self::FilterURI($_val));
                }
            } else if (((strpos($_val, '=') && substr($_queryString, -1) == '=') || (substr($_val, 0, 2) == 'B:')) && preg_match("|^[a-zA-Z\s0-9" . preg_quote(self::PERMITTED_URICHARACTERS) . "]+$|i", substr($_val, 0, strlen($_val)-1), $_matches2) && count($_argumentStringContainer) == 1) {
                if (substr($_val, 0, 2) == 'B:')
                {
                    $_val = substr($_val, 2);
                }

                $_argumentContainer[] = self::FilterURI($_val);
            } else if (count($_matches) == 3) {
                $_isAssociative = true;

                $_argumentContainer[Clean($_matches[1])] = htmlspecialchars(self::FilterURI($_matches[2]));
            }
        }

        return array($_argumentContainer, $_isAssociative);
    }

    /**
     * Gets the default route container
     *
     * @author Varun Shoor
     * @return mixed "_routeContainer" (Array) on Success, "false" otherwise
     * @throws SWIFT_Exception When there is no default route available
     */
    static private function GetDefaultRouteContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_routeContainer = array();

        $_routeContainer = array();
        $_routeContainer['app'] = $_routeContainer['controller'] = $_routeContainer['action'] = $_routeContainer['arguments'] = false;

        $_SWIFT_AppObject = SWIFT_App::Get(SWIFT_App::SWIFT_APPCORE);

        // Does the requested app exist?
        if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception('"' . ucfirst(SWIFT_App::SWIFT_APPCORE) . '" SWIFT App does not exist (DEFAULT)');

            return false;
        }

        $_routeContainer['app'] = $_SWIFT_AppObject->GetName();

        // Does it have a valid interface set?
        $_interfaceName = $_SWIFT->Interface->GetName();
        if (empty($_interfaceName) || !$_SWIFT_AppObject->InterfaceDirectoryExists($_interfaceName))
        {
            FileNotFound('"' . ucfirst($_interfaceName) . '" Interface directory does not exist');

            return false;
        }

        // Does it have a valid default controller?
        $_controllerName = SWIFT_Controller::DEFAULT_CONTROLLER;
        if (empty($_controllerName) || !$_SWIFT_AppObject->ControllerFileExists($_interfaceName, $_controllerName))
        {
            FileNotFound('"' . ucfirst($_controllerName) . '" Default Controller in SWIFT App "'. ucfirst(SWIFT_App::SWIFT_APPCORE) .'" for Interface "'. Clean($_interfaceName) .'" does not exist (DEFAULT)');

            return false;
        }

        $_routeContainer['controller'] = $_controllerName;

        $_routeContainer['action'] = SWIFT_Controller::DEFAULT_ACTION;
        $_routeContainer['isassociative'] = false;

        $_routeContainer['fullarguments'] = array();

        return $_routeContainer;
    }

    /**
     * Filter segments for malicious characters
     *
     * @author CodeIgniter
     * @param string $_string The String to Filter
     * @return string|false The Processed String
     * @throws SWIFT_Exception When the Filter String Contains Invalid Data
     */
    public static function FilterURI($_string)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP)
                && isset($GLOBALS['argv'][1])) {
            return $_string;
        }

        $_string = trim($_string);

        if ($_string != '' && self::PERMITTED_URICHARACTERS != '')
        {
            if (!preg_match("|^[a-zA-Z\s0-9" . preg_quote(self::PERMITTED_URICHARACTERS) . "]+$|i", $_string, $_matches))
            {
                if ($_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_CONSOLE)
                {
                    header('HTTP/1.1 400 Bad Request');
                }

                FileNotFound('The URI you submitted has disallowed characters.', false);

                return false;
            }
        }

        return $_SWIFT->Input->SanitizeForXSS($_string);
    }

    /**
     * Remove the URL Suffix (.html, .htm etc.)
     *
     * @author CodeIgniter
     * @param string $_string The String to Filter
     * @return string The Processed String
     */
    public static function RemoveURLSuffix($_string)
    {
        if (!defined('URL_SUFFIX'))
        {
            $_string = preg_replace("|".preg_quote(self::URL_SUFFIX)."$|", "", $_string);
        }

        return $_string;
    }

    /**
     * Explodes the Segments in the string and returns them as array
     *
     * @author Varun Shoor
     * @param string $_string The String to Process
     * @return array Exploded Segments which are individually filtered for malicious content
     */
    public static function ExplodeSegments($_string)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
        $_templateGroupList = array();

        if (_is_array($_templateGroupCache))
        {
            foreach ($_templateGroupCache as $_templateGroup) {
                $_templateGroupList[] = mb_strtolower($_templateGroup['title']);
            }
        }

        $_templateGroupTitle = $_originalTemplateGroupTitle = false;
        $_queryStringParsed = '';

        if (strstr($_string, '&')) {
            $_getVariableList = array();
            parse_str(substr($_string, strpos($_string, '&')+1), $_getVariableList);
            if (is_array($_getVariableList)) {
                foreach ($_getVariableList as $_key => $_val) {
                    if (!isset($_GET[$_key])) {
                        $_GET[$_key] = $_val;
                    }
                }
            }

            $_string = substr($_string, 0, strpos($_string, '&'));
        }

        $_segmentsContainer = array();

        $_index = 0;

        foreach(explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $_string)) as $_val)
        {
            // Filter segments for security
            $_val = trim($_val);

            // Incoming template group processing.
            // Make sure its the client interface, first argument, not a app and in the template group list
            if (($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_VISITOR || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_RSS)
                    && $_index == 0 && !SWIFT_App::IsInstalled(mb_strtolower($_val)) &&
                    in_array(mb_strtolower($_val), $_templateGroupList))
            {
                $_templateGroupTitle = $_val;

                continue;
            }

            if ($_val != '')
            {
                $_segmentsContainer[] = $_val;

                $_queryStringParsed .= '/' . $_val;

                $_index++;
            }
        }

        return array('querystring' => $_queryStringParsed, 'templategroup' => $_templateGroupTitle, 'segments' => $_segmentsContainer);
    }

    /**
     * Parse URI Function (Code has been taken from CodeIgniter framework)
     *
     * @author CodeIgniter
     * @return string Parsed Query String /Core/Departments/Insert
     */
    static private function GetURI()
    {
        $_SWIFT = SWIFT::GetInstance();

        // We follow a different path when in console mode..
        if (($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP) && isset($GLOBALS['argv'][1]))
        {
            $_queryString = $GLOBALS['argv'][1];
            $_baseArgCount = 2;
            $_baseArgOffset = 1;
            if (isset($_SERVER['PATH_INFO'])) {
                $_queryString = $_SERVER['PATH_INFO'];
                $_baseArgCount = 1;
                $_baseArgOffset = 0;
            }

            $_queryStringArgumentContainer = array();

            // Check for starting /
            if (substr($_queryString, 0, 1) != '/')
            {
                $_queryString = '/' . $_queryString;
            }

            $_queryString = self::RemoveURLSuffix($_queryString);
            $_queryStringSegmentsContainer = self::ExplodeSegments($_queryString);

            $_queryStringSegments = array();

            if (isset($_queryStringSegmentsContainer['segments'])) {
                $_queryStringSegments = $_queryStringSegmentsContainer['segments'];
            }

            if (count($_queryStringSegments) == 1)
            {
                $_queryString = StripTrailingSlash($_queryString) . '/' . SWIFT_Controller::DEFAULT_CONTROLLER . '/' . SWIFT_Controller::DEFAULT_ACTION;
            } else if (count($_queryStringSegments) == 2) {
                $_queryString = StripTrailingSlash($_queryString) . '/' . SWIFT_Controller::DEFAULT_ACTION;
            } else if (!_is_array($_queryStringSegments)) {
                return '';
            }

            // Do we have arguments?
            if (count($GLOBALS['argv']) > $_baseArgCount)
            {
                foreach ($GLOBALS['argv'] as $_key => $_val)
                {
                    if ($_key > $_baseArgOffset)
                    {
                        $_queryStringArgumentContainer[] = $_val;
                    }
                }
            }

            if (count($_queryStringArgumentContainer))
            {
                $_queryString = StripTrailingSlash($_queryString) . '/' . implode('/', $_queryStringArgumentContainer);
            }

            return $_queryString;
        } else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE && !isset($GLOBALS['argv'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_CONSOLE)
        {
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PUT' && isset($_SERVER['CONTENT_TYPE']) && strstr($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded')) {
                $_variableContainer = array();
                $_inputStream = file_get_contents('php://input');
                parse_str($_inputStream, $_variableContainer);
                if (_is_array($_variableContainer)) {
                    $_REQUEST = array_merge($_REQUEST, $_variableContainer);
                    $_POST = array_merge($_POST, $_variableContainer);
                }
            }

            // If we execute directly via get
            if (isset($_REQUEST['e']) && !empty($_REQUEST['e']) && substr($_REQUEST['e'], 0, 1) == '/')
            {
                $_queryString = urldecode($_REQUEST['e']);

                return $_queryString;
            }

            // Is there a PATH_INFO variable?
            // Note: some servers seem to have trouble with getenv() so we'll test it two ways
            $_pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
            if (trim($_pathInfo, '/') != '' && $_pathInfo != '/' . self::DEFAULT_BASENAME)
            {
                $_compareBaseName = str_replace('?', '', SWIFT_BASENAME);
                if (!empty($_compareBaseName) && strpos($_pathInfo, $_compareBaseName) !== false) {
                    $_processedString = substr($_pathInfo, strpos($_pathInfo, $_compareBaseName)+strlen($_compareBaseName));
                    if (substr($_processedString, 0, 1) == '?') {
                        $_processedString = substr($_processedString, 1);
                    }
                    if (!empty($_processedString) && substr($_processedString, 0, 1) == '/') {
                        return $_processedString;
                    }
                } else if (substr($_pathInfo, 0, 1) == '/' && !strpos($_pathInfo, 'index.php')) {
                    return $_pathInfo;
                } else if (strpos($_pathInfo, '?') !== false) {
                    $_processedPathInfo = substr($_pathInfo, strpos($_pathInfo, '?')+1);
                    if (substr($_processedPathInfo, 0, 1) == '/') {
                        return $_processedPathInfo;
                    }
                }
            }

            // No PATH_INFO?... What about QUERY_STRING?
            $_pathQueryString = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
            if (trim($_pathQueryString, '/') != '')
            {
                return $_pathQueryString;
            }

            // No QUERY_STRING?... Maybe the ORIG_PATH_INFO variable exists?
            $_pathOrigPathInfo = (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO');
            if (trim($_pathOrigPathInfo, '/') != '' && $_pathOrigPathInfo != "/" . self::DEFAULT_BASENAME)
            {
                // remove path and script information so we have good URI data
                return str_replace($_SERVER['SCRIPT_NAME'], '', $_pathOrigPathInfo);
            }

            if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '')
            {
                return key($_GET);
            }
        }

        // We've exhausted all our options...
        return '';
    }

    /**
     * Parse the incoming request based on REQUEST_URI
     *
     * @author CodeIgniter
     * @return string Parsed Query String /Core/Departments/Insert
     */
    public static function GetRequestURI()
    {
        if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '')
        {
            return '';
        }

        $_requestURI = preg_replace("|/(.*)|", "\\1", str_replace("\\", "/", $_SERVER['REQUEST_URI']));

        if ($_requestURI == '' || $_requestURI == self::DEFAULT_BASENAME)
        {
            return '';
        }

        $_interfaceFilePath = SWIFT_INTERFACEFILE;
        if (strpos($_requestURI, '?') !== FALSE)
        {
            $_interfaceFilePath .= '?';
        }

        $_parsedURIContainer = explode("/", $_requestURI);

        $_index = 0;
        foreach(explode("/", $_interfaceFilePath) as $i => $_segment)
        {
            if (isset($_parsedURIContainer[$i]) && $_segment == $_parsedURIContainer[$i])
            {
                $_index++;
            }
        }

        $_parsedURI = implode("/", array_slice($_parsedURIContainer, $_index));

        if ($_parsedURI != '')
        {
            $_parsedURI = '/' . $_parsedURI;
        }

        return $_parsedURI;
    }

    /**
     * Parse the Template Group from Query String
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ParseTemplateGroup()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_queryString = str_replace('%252', '/', self::GetURI());

        if (!empty($_queryString))
        {
            $_queryString = self::RemoveURLSuffix($_queryString);
            $_queryStringSegmentsContainer = self::ExplodeSegments($_queryString);

            if (!empty($_queryStringSegmentsContainer['templategroup']))
            {
                self::$_activeTemplateGroup = $_queryStringSegmentsContainer['templategroup'];

                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve a Template Group that we might have received
     *
     * @author Varun Shoor
     * @return string The Active Template Group
     */
    public static function GetTemplateGroup()
    {
        return self::$_activeTemplateGroup;
    }

    /**
     * Retrieve's the JS Router Path
     *
     * @author Varun Shoor
     * @return string The JS Router Path
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSRouterPath()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return strtolower('/' . $this->_appName . '/' . $this->_appController . '/') . $this->_controllerAction . $this->GetJSRouterArguments();
    }

    /**
     * Retrieve's the JS Router Arguments
     *
     * @author Varun Shoor
     * @return string The JS Router Arguments
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSRouterArguments()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_routerArguments = '';
        if (count($this->GetArguments()))
        {
            $_argumentContainer = $this->GetArguments();
            foreach ($_argumentContainer as $_index => $_argument) {
                if (is_object($_argument) || is_array($_argument))
                {
                    $_argumentContainer[$_index] = '0';
                } else {
                    $_argumentContainer[$_index] = addslashes(Clean($_argument));
                }
            }

            $_routerArguments = implode('/', $_argumentContainer);

        }

        return '/' . $_routerArguments;
    }
}
