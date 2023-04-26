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
 * The Core View Management Class
 *
 * @author Varun Shoor
 *
 * @property \Base\Library\Language\SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 */
class SWIFT_View extends SWIFT_Base
{
    static private $_viewObjectCache = array();

    public $UserInterfaceGrid = false;
    public $Controller = false;

    // Core Constants
    const FILE_PREFIX = 'class.';
    const VIEW_CLASS_PREFIX = 'View_';

    /**
     * Constructor
     *
     * @author Varun Shoore
     * @throws SWIFT_Exception If the View could not be Initialized
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
     * Load the View file into the controller name space
     *
     * @author Varun Shoor
     * @param SWIFT_Interface $_SWIFT_InterfaceObject The SWIFT_Interface Object Pointer
     * @param SWIFT_App $_SWIFT_AppObject The SWIFT_App Object Pointer
     * @param SWIFT_Router $_SWIFT_RouterObject The SWIFT_Router Object Pointer
     * @return bool|SWIFT_View "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the View cannot be loaded
     */
    public static function Load(SWIFT_Controller $_SWIFT_ControllerObject, SWIFT_Interface $_SWIFT_InterfaceObject, SWIFT_App $_SWIFT_AppObject, SWIFT_Router $_SWIFT_RouterObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Sanity Checks
        if ((!$_SWIFT_ControllerObject instanceof SWIFT_Controller || !$_SWIFT_ControllerObject->GetIsClassLoaded()) || (!$_SWIFT_InterfaceObject instanceof SWIFT_Interface || !$_SWIFT_InterfaceObject->GetIsClassLoaded()) || (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) || (!$_SWIFT_RouterObject instanceof SWIFT_Router || !$_SWIFT_RouterObject->GetIsClassLoaded()))
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_appDirectory = $_SWIFT_AppObject->GetDirectory();
        $_interfaceName = strtolower(Clean($_SWIFT_InterfaceObject->GetName()));

        if (substr(get_short_class($_SWIFT_ControllerObject), 0, strlen(SWIFT_Controller::CONTROLLER_CLASS_PREFIX)) == SWIFT_Controller::CONTROLLER_CLASS_PREFIX)
        {
            $_controllerName = substr(get_short_class($_SWIFT_ControllerObject), strlen(SWIFT_Controller::CONTROLLER_CLASS_PREFIX));
        } else {
            $_controllerName = Clean($_SWIFT_RouterObject->GetController());
        }

        $_viewFile = $_appDirectory . '/' . $_interfaceName . '/' . self::FILE_PREFIX . self::VIEW_CLASS_PREFIX . $_controllerName . '.php';
        $_viewClassName = self::VIEW_CLASS_PREFIX . $_controllerName;
        $_viewClassName = prepend_view_namespace($_SWIFT_AppObject->GetName(), $_interfaceName, $_viewClassName);
        if (isset(self::$_viewObjectCache[$_viewClassName]) && self::$_viewObjectCache[$_viewClassName] instanceof SWIFT_View && self::$_viewObjectCache[$_viewClassName]->GetIsClassLoaded())
        {
            return self::$_viewObjectCache[$_viewClassName];
        }

        if (!file_exists($_viewFile))
        {
            return false;
        }

        // Load the View Class
        require_once ($_viewFile);

        // Now comes the tricky part, we need to load the class for it..
        if (!class_exists($_viewClassName, false))
        {
            return false;
        }

        // Call the requested method. Any URI segments present (besides the class/function) will be passed to the method for convenience
        $_SWIFT_ViewObject = new $_viewClassName();
        if (!$_SWIFT_ViewObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception('"' . Clean($_viewClassName) . '" View in SWIFT App "'. Clean($_SWIFT_AppObject->GetName()) .'" could not be loaded');

            return false;
        }

        // Override the App, Router & Interface Objects
        $_SWIFT_ViewObject->OverrideObjects($_SWIFT_InterfaceObject, $_SWIFT_AppObject, $_SWIFT_RouterObject);

        self::$_viewObjectCache[$_viewClassName] = $_SWIFT_ViewObject;

        return self::$_viewObjectCache[$_viewClassName];
    }
}
?>
