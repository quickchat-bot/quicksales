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
 * The Core Controller Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Controller extends SWIFT_Base
{
    static private $_controllerObjectCache = array();

    public $View = false;

    /** @var bool Stop the rendering */
    private $_stopRendering = false;

    // Core Constants
    const CONTROLLER_CLASS_PREFIX = 'Controller_';

    // Core Constants
    const DEFAULT_CONTROLLER = 'Default';
    const DEFAULT_ACTION = 'Index';
    const FILE_PREFIX = 'class.';

    /**
     * Constructor
     *
     * @author Varun Shoore
     * @throws SWIFT_Exception If the Controller fails to Load
     */
    public function __construct()
    {
        if (!$this->Initialize())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
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
     * @param bool $stop
     * @return SWIFT_Controller
     */
    public function stopRendering(bool $stop)
    {
        $this->_stopRendering = $stop;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStopRendering(): bool
    {
        return $this->_stopRendering;
    }

    /**
     * Load a custom controller object (only loads from active app)
     *
     * @author Varun Shoor
     * @param string $_controllerName The Controller Name
     * @param string $_customApp The Custom App
     * @return SWIFT_Controller|bool "true" on Success, "false" otherwise
     */
    public static function LoadCustomController($_controllerName, $_customApp = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_controllerName = Clean($_controllerName);

        $_SWIFT_InterfaceObject = $_SWIFT->Interface;

        $_SWIFT_AppObject = $_SWIFT->App;
        $_hasCustomApp = false;
        if (!empty($_customApp))
        {
            $_SWIFT_AppObject = SWIFT_App::Get($_customApp);

            SWIFT_Loader::AddApp($_SWIFT_AppObject);

            $_hasCustomApp = true;
        }

        $_SWIFT_RouterObject = $_SWIFT->Router;

        // Sanity Checks
        if ((!$_SWIFT_InterfaceObject instanceof SWIFT_Interface || !$_SWIFT_InterfaceObject->GetIsClassLoaded()) || (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) || (!$_SWIFT_RouterObject instanceof SWIFT_Router || !$_SWIFT_RouterObject->GetIsClassLoaded()))
        {
            throw new SWIFT_Exception('"Core Controller Loading Error');

            return false;
        }

        $_appDirectory = $_SWIFT_AppObject->GetDirectory();
        $_interfaceName = strtolower(Clean($_SWIFT_InterfaceObject->GetName()));
        $_controllerFile = $_appDirectory . '/' . $_interfaceName . '/' . self::FILE_PREFIX . self::CONTROLLER_CLASS_PREFIX . $_controllerName . '.php';
        $_controllerClassName = self::CONTROLLER_CLASS_PREFIX . $_controllerName;
        $_controllerClassName = prepend_controller_namespace($_SWIFT_AppObject->GetName(), $_interfaceName, $_controllerClassName);
        if (isset(self::$_controllerObjectCache[$_controllerClassName]) && self::$_controllerObjectCache[$_controllerClassName] instanceof SWIFT_Controller && self::$_controllerObjectCache[$_controllerClassName]->GetIsClassLoaded())
        {
            return self::$_controllerObjectCache[$_controllerClassName];
        }

        if (!file_exists($_controllerFile))
        {
            throw new SWIFT_Exception('"' . Clean($_controllerClassName) . '" Controller in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'" has no relevant file');

            return false;
        }

        // Load the Controller Class
        require_once ($_controllerFile);

        // Now comes the tricky part, we need to load the class for it..
        if (!class_exists($_controllerClassName, false))
        {
            throw new SWIFT_Exception('"' . Clean($_controllerClassName) . '" Controller class does not exist in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'"');

            return false;
        }

        // Call the requested method. Any URI segments present (besides the class/function) will be passed to the method for convenience
        $_SWIFT_ControllerObject = new $_controllerClassName();
        if (!$_SWIFT_ControllerObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception('"' . Clean($_controllerClassName) . '" Controller in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'" could not be loaded');

            return false;
        }
        $_SWIFT->Controller = $_SWIFT_ControllerObject;

        // We now attempt to see if there is a view associated with this action...
        $_SWIFT_ViewObject = SWIFT_View::Load($_SWIFT_ControllerObject, $_SWIFT_InterfaceObject, $_SWIFT_AppObject, $_SWIFT_RouterObject);
        if ($_SWIFT_ViewObject instanceof SWIFT_View && $_SWIFT_ViewObject->GetIsClassLoaded())
        {
            $_SWIFT_ControllerObject->UpdateObject('View', $_SWIFT_ViewObject);
            $_SWIFT_ViewObject->UpdateObject('Controller', $_SWIFT_ControllerObject);
        }

        // Override the App, Router & Interface Objects
        $_SWIFT_ControllerObject->OverrideObjects($_SWIFT_InterfaceObject, $_SWIFT_AppObject, $_SWIFT_RouterObject);

        self::$_controllerObjectCache[$_controllerClassName] = $_SWIFT_ControllerObject;

        if ($_hasCustomApp) {
            $_SWIFT_RouterObject->SetApp($_SWIFT_AppObject);
        }
        $_SWIFT_RouterObject->SetController($_controllerName);
        $_SWIFT_RouterObject->SetAction(self::DEFAULT_ACTION);

        SWIFT::Set('_incomingRequestHistoryChunk', $_SWIFT->Router->GetArgumentsAsString());

        return self::$_controllerObjectCache[$_controllerClassName];
    }

    /**
     * Load the Controller file and Execute the Router Action
     *
     * @author Varun Shoor
     * @param SWIFT_Interface $_SWIFT_InterfaceObject The SWIFT_Interface Object Pointer
     * @param SWIFT_App $_SWIFT_AppObject The SWIFT_App Object Pointer
     * @param SWIFT_Router $_SWIFT_RouterObject The SWIFT_Router Object Pointer
     * @param string $_controllerParentClass (OPTIONAL) Restrict the controller parent class to a specific type
     * @return SWIFT_Controller|bool
     * @throws SWIFT_Exception If the Controller cannot be loaded
     */
    public static function Load(SWIFT_Interface $_SWIFT_InterfaceObject, SWIFT_App $_SWIFT_AppObject, SWIFT_Router $_SWIFT_RouterObject, $_controllerParentClass = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Sanity Checks
        if ((!$_SWIFT_InterfaceObject instanceof SWIFT_Interface || !$_SWIFT_InterfaceObject->GetIsClassLoaded()) || (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) || (!$_SWIFT_RouterObject instanceof SWIFT_Router || !$_SWIFT_RouterObject->GetIsClassLoaded()))
        {
            throw new SWIFT_Exception('"Core Controller Loading Error');

            return false;
        }

        SWIFT_Loader::AddApp($_SWIFT_AppObject);

        $_appDirectory = $_SWIFT_AppObject->GetDirectory();
        $_interfaceName = strtolower(Clean($_SWIFT_InterfaceObject->GetName()));
        $_controllerName = Clean($_SWIFT_RouterObject->GetController());
        $_controllerClassName = self::CONTROLLER_CLASS_PREFIX . $_controllerName;
        $_controllerFilename = $_appDirectory . '/' . $_interfaceName . '/' . self::FILE_PREFIX . $_controllerClassName . '.php';
        $_controllerFileCacheMeta = SWIFT_Loader::GetCacheByAppInterface($_SWIFT_AppObject, $_SWIFT_InterfaceObject, SWIFT_Loader::TYPE_CONTROLLER, $_controllerClassName);
        $_controllerFile = isset($_controllerFileCacheMeta[0]) ? $_controllerFileCacheMeta[0] : $_controllerFilename;

        if (isset(self::$_controllerObjectCache[$_controllerClassName]) && self::$_controllerObjectCache[$_controllerClassName] instanceof SWIFT_Controller && self::$_controllerObjectCache[$_controllerClassName]->GetIsClassLoaded())
        {
//            return self::$_controllerObjectCache[$_controllerClassName];
        }

        if (!file_exists($_controllerFile))
        {
            FileNotFound('"' . Clean($_controllerClassName) . '" Controller in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'" has no relevant file');

            return false;
        }

        $_controllerActionName = $_SWIFT_RouterObject->GetAction();

        // Disable calls with prefix of _ OR ones that are in the parent class
        if (strncmp($_controllerActionName, '_', 1) == 0 || (in_array(strtolower($_controllerActionName), array_map('strtolower', get_class_methods('SWIFT_Controller'))))) {
            FileNotFound('"' . Clean($_controllerClassName) . '" Controller is attempting to call an Invalid Action "'. Clean($_controllerActionName) .'"  in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'"');

            return false;
        }

        // Load the Controller Class
        require_once ($_controllerFile);
        $_controllerClassName = prepend_controller_namespace($_SWIFT_AppObject->GetName(), $_interfaceName, $_controllerClassName);

        // Now comes the tricky part, we need to load the class for it..
        if (!class_exists($_controllerClassName, false))
        {
            FileNotFound('"' . Clean($_controllerClassName) . '" Controller class does not exist in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'"');

            return false;
        }

        // is_callable() returns TRUE on some versions of PHP 5 for private and protected methods, so we'll use this workaround for consistent behavior
        $_controllerMethodContainer = array_map('strtolower', get_class_methods($_controllerClassName));
        $_hasCallDeclaration = false;
        if (in_array('__call', $_controllerMethodContainer))
        {
            $_hasCallDeclaration = true;
        }

        $_controllerReflectionClassObject = new ReflectionClass($_controllerClassName);
        if (!$_controllerReflectionClassObject instanceof ReflectionClass)
        {
            throw new SWIFT_Exception('"' . Clean($_controllerClassName) . '" Reflection Class could not be initialized in SWIFT App "' . Clean($_SWIFT_AppObject->GetName()) . '"');

            return false;
        }

        // REST Interface check
        $_fetchRESTArguments = false;
        $_SWIFT_RESTServerObject = false;
        if ($_controllerReflectionClassObject->implementsInterface('SWIFT_REST_Interface')) {
            $_SWIFT_RESTServerObject = new SWIFT_RESTServer();
        }

        // We make sure that this controller does not have a __call method which will take over our tasks
        if (!in_array(strtolower($_controllerActionName), $_controllerMethodContainer) && !$_hasCallDeclaration && $_SWIFT_RESTServerObject === false)
        {
            $_argReturn = '';
            if (isset($GLOBALS['argv']))
            {
                $_argReturn = print_r($GLOBALS['argv'], true);
            }

            FileNotFound('"' . Clean($_controllerClassName) . '" Controller has no function declaration for "' . Clean($_controllerActionName) . '" Action in SWIFT App "' . Clean($_SWIFT_AppObject->GetName()) . '"' . $_argReturn . ' ' . SWIFT_INTERFACE);

            return false;
        }
        // Verify parent class of the controller
        if (!empty($_controllerParentClass))
        {
            $_parentClassName = false;
            $_parentClassArray = (array) $_controllerReflectionClassObject->getParentClass();
            if (isset($_parentClassArray['name']))
            {
                $_parentClassName = $_parentClassArray['name'];
            }

            if (!$_parentClassName || $_parentClassName != $_controllerParentClass)
            {
                throw new SWIFT_Exception('"' . Clean($_controllerClassName) . '" <> "' . $_parentClassName . '" parent class mismatch"');

                return false;
            }
        }

        $_controllerReflectionMethodObject = false;

        try
        {
            $_controllerReflectionMethodObject = $_controllerReflectionClassObject->getMethod($_controllerActionName);
        } catch (Exception $_ExceptionObject) {

        }

        if (!$_controllerReflectionMethodObject instanceof ReflectionMethod && !$_hasCallDeclaration && $_SWIFT_RESTServerObject === false)
        {
            FileNotFound('"' . Clean($_controllerClassName) . '" Reflection Class has no function declaration for "' . Clean($_controllerActionName) . '" Action in SWIFT App "' . Clean($_SWIFT_AppObject->GetName()) . '"');

            return false;

        // We didnt find a action with that name but this controller implements REST interface, so we need to override the method
        } else if (!$_controllerReflectionMethodObject instanceof ReflectionMethod && !$_hasCallDeclaration && $_SWIFT_RESTServerObject instanceof SWIFT_RESTServer) {
            $_controllerActionName = $_SWIFT_RESTServerObject->GetMethodFunction($_controllerActionName);

            try
            {
                $_controllerReflectionMethodObject = $_controllerReflectionClassObject->getMethod($_controllerActionName);
            } catch (Exception $_ExceptionObject) {

            }

            if (!$_controllerReflectionMethodObject) {
                FileNotFound('"' . Clean($_controllerClassName) . '" Reflection Class has no function declaration for "' . Clean($_controllerActionName) . '" Action in SWIFT App "' . Clean($_SWIFT_AppObject->GetName()) . '"');

                return false;
            }

            $_fetchRESTArguments = true;
        }

        // Prevent the user from calling static, protected, private or abstract methods
        if (!$_hasCallDeclaration && ($_controllerReflectionMethodObject->isStatic() || $_controllerReflectionMethodObject->isProtected() || $_controllerReflectionMethodObject->isPrivate() || $_controllerReflectionMethodObject->isAbstract()))
        {
            FileNotFound('"' . Clean($_controllerClassName) . '" Reflection Class has Invalid "'. Clean($_controllerActionName) .'" Action Declaration in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'"');

            return false;
        }

        // Call the requested method. Any URI segments present (besides the class/function) will be passed to the method for convenience
        $_SWIFT_ControllerObject = new $_controllerClassName();
        if (!$_SWIFT_ControllerObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception('"' . Clean($_controllerClassName) . '" Controller in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'" could not be loaded');

            return false;
        }

        // Check if we need to stop here, ie. no permission to avoid double rendering
        if ($_SWIFT_ControllerObject->isStopRendering()) {
            return false;
        }

        $_SWIFT_ControllerObject->REST = $_SWIFT_RESTServerObject;

        $_SWIFT->Controller = $_SWIFT_ControllerObject;

        // We now attempt to see if there is a view associated with this action...
        $_SWIFT_ViewObject = SWIFT_View::Load($_SWIFT_ControllerObject, $_SWIFT_InterfaceObject, $_SWIFT_AppObject, $_SWIFT_RouterObject);
        if ($_SWIFT_ViewObject instanceof SWIFT_View && $_SWIFT_ViewObject->GetIsClassLoaded())
        {
            $_SWIFT_ControllerObject->UpdateObject('View', $_SWIFT_ViewObject);
            $_SWIFT_ViewObject->UpdateObject('Controller', $_SWIFT_ControllerObject);
        }

        // Override the App, Router & Interface Objects
        $_SWIFT_ControllerObject->OverrideObjects($_SWIFT_InterfaceObject, $_SWIFT_AppObject, $_SWIFT_RouterObject);

        self::$_controllerObjectCache[$_controllerClassName] = $_SWIFT_ControllerObject;

        $_argumentsContainer = array();
        if ($_SWIFT_RouterObject->GetArgumentIsAssociative()) {
            $_argumentsContainer = array($_SWIFT_RouterObject->GetArguments($_fetchRESTArguments));
        } else {
            $_argumentsContainer = $_SWIFT_RouterObject->GetArguments($_fetchRESTArguments);
        }

        call_user_func_array(array(self::$_controllerObjectCache[$_controllerClassName], $_controllerActionName), $_argumentsContainer);

        return self::$_controllerObjectCache[$_controllerClassName];
    }

    /**
     * Retrieve the Menu ID
     *
     * @author Varun Shoor
     * @return int The Menu ID
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMenuID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_constantArg = get_short_class($this) . '::MENU_ID';
        if (defined($_constantArg))
        {
            return constant($_constantArg);
        }

        return 0;
    }

    /**
     * Retrieve the Navigation ID
     *
     * @author Varun Shoor
     * @return int The Navigation ID
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNavigationID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_constantArg = get_short_class($this) . '::NAVIGATION_ID';
        if (defined($_constantArg))
        {
            return constant($_constantArg);
        }

        return 0;
    }
}
?>
