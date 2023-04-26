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
 * The Core Loader Class
 *
 * @method mixed Search(...$args)
 * @method mixed Restore(...$args)
 * @method mixed Diagnostics(...$args)
 * @method mixed RenderForm(...$args)
 * @method mixed ReportBug(...$args)
 * @method mixed ImpEx(...$args)
 * @method mixed Personalize(...$args)
 * @method mixed Index(...$args)
 * @method mixed Manage(...$args)
 * @method mixed Insert(...$args)
 * @method mixed Edit($id)
 * @author Varun Shoor
 */
class SWIFT_Loader extends SWIFT_Base
{
    private $_classPointer = false;
    private $_classFileName;
    private $_classReflection = false;

    static protected $_loadedApps = array();
    static protected $_cacheList = array();
    static protected $_isCacheUpdated = false;

    // Core Constants
    const EXCEPTION_SUFFIX = '_Exception';
    const INTERFACE_SUFFIX = '_Interface';

    const TYPE_LIBRARY = 1;
    const TYPE_INTERFACE = 2;
    const TYPE_EXCEPTION = 3;
    const TYPE_MODEL = 4;
    const TYPE_CONTROLLER = 5;

    const CACHE_FILE = 'SWIFT_Loader.cache';

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct(SWIFT_Base &$_classPointer)
    {
        $_ReflectionObject = new ReflectionClass($_classPointer);

        $this->_classReflection = $_ReflectionObject;
        $this->_classFileName = $_ReflectionObject->getFileName();
        $this->_classPointer = $_classPointer;

        parent::__construct();
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
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param int $_loadType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_loadType)
    {
        return ($_loadType == self::TYPE_LIBRARY || $_loadType == self::TYPE_INTERFACE || $_loadType == self::TYPE_EXCEPTION || $_loadType == self::TYPE_MODEL);
    }

    /**
     * Add App to the Loaded App List
     *
     * @author Varun Shoor
     * @param object $_AppObject The App Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public static function AddApp($_AppObject)
    {
        if (!$_AppObject instanceof SWIFT_App || !$_AppObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_appName = $_AppObject->GetName();

        if (!isset(self::$_loadedApps[$_appName])) {
            self::$_loadedApps[$_appName] = $_AppObject;
        }

        return true;
    }

    /**
     * Attempt to call a method in the class pointer if it doesnt exist in this local class
     *
     * @author Varun Shoor
     * @param string $_name The Function Name
     * @param array $_arguments The Function Arguments
     * @return mixed "Function Result" (MIXED) on Success, "false" otherwise
     */
    public function __call($_name, $_arguments)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (isset($this->_classReflection) && $this->_classReflection instanceof ReflectionClass && isset($this->_classPointer) && $this->_classPointer instanceof SWIFT_Controller) {
            if ($this->_classReflection->hasMethod($_name)) {
                // Before we call this, we need to update the router..
                SWIFT::ProcessAllShutdownFunctions();

                $_SWIFT->Router->SetAction($_name);
                $_SWIFT->Router->SetArguments($_arguments);

                SWIFT::Set('_incomingRequestHistoryChunk', $_SWIFT->Router->GetArgumentsAsString());

                return call_user_func_array(array($this->_classPointer, $_name), $_arguments);
            }
        }

        return false;
    }

    /**
     * Call up a method
     *
     * @author Varun Shoor
     * @param string $_methodName The Method Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Method($_methodName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_argumentContainer = array();

        foreach (func_get_args() as $_key => $_val) {
            if ($_key > 0) {
                $_argumentContainer[] = $_val;
            }
        }

        if (isset($this->_classReflection) && $this->_classReflection instanceof ReflectionClass && isset($this->_classPointer) && $this->_classPointer instanceof SWIFT_Controller) {
            if ($this->_classReflection->hasMethod($_methodName)) {
                // Before we call this, we need to update the router..
                SWIFT::ProcessAllShutdownFunctions();

                $_SWIFT->Router->SetAction($_methodName);
                $_SWIFT->Router->SetArguments($_argumentContainer);

                SWIFT::Set('_incomingRequestHistoryChunk', $_SWIFT->Router->GetArgumentsAsString());

                return call_user_func_array(array($this->_classPointer, $_methodName), $_argumentContainer);
            }
        }

        return true;
    }

    /**
     * Returns the controller object
     *
     * @author Varun Shoor
     * @param string $_controllerName The Controller Name
     * @param string $_customApp (OPTIONAL) The Custom App
     * @return mixed "SWIFT_Controller" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Controller($_controllerName, $_customApp = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return SWIFT_Controller::LoadCustomController($_controllerName, $_customApp);
    }

    /**
     * Load the given Model into the local namespace
     *
     * @author Varun Shoor
     * @param string $_modelName The Model Name
     * @param array $_arguments The Function Arguments
     * @param bool $_initiateInstance Whether the Instance should be Initiated
     * @param mixed $_customAppName The Custom App to Load Model From
     * @param string $appName
     * @return bool "true" on Success, "false" otherwise
     * @throws ReflectionException
     * @throws SWIFT_Exception When the Model cannot be loaded or If the Class is not loaded
     */
    public function Model(string $_modelName, array $_arguments, bool $_initiateInstance, $_customAppName, string $appName = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_model = explode(':', $_modelName);

        // We first need to make sure that the model exists in the 'CORE' libraries folder
        $_modelClassName = self::CLASS_PREFIX . $_modelName;

        $_appDirectory = $_classFileName = false;

        if (!class_exists($_modelClassName, true)) {
            // Loading library with a custom classFileName leads to inconsistent exception and interface loading.
            // Adjusting to override classFileName only with matching Library & classFileName
            $_classFileName = IIF(stristr($this->_classFileName, substr($_modelName, 0, strpos($_modelName, ':')) . DIRECTORY_SEPARATOR), $this->_classFileName);

            // Is the app loaded for this object?
            if ($this->_classPointer && !$_customAppName) {
                if (isset($this->_classPointer->App) && $this->_classPointer->App instanceof SWIFT_App && $this->_classPointer->App->GetIsClassLoaded()) {
                    self::AddApp($this->_classPointer->App);
                }
            } else if ($this->_classPointer && $_customAppName) {
                $_SWIFT_AppObject = SWIFT_App::Get($_customAppName);

                if ($_SWIFT_AppObject instanceof SWIFT_App && $_SWIFT_AppObject->GetIsClassLoaded()) {
                    self::AddApp($_SWIFT_AppObject);
                }
            }

            self::LoadModelFile($_modelName, $_classFileName, true, $appName);
        }

        $_variableContainer = self::ParseModelInformation($_modelName);
        extract($_variableContainer);

        $_modelClassName = prepend_library_namespace($_model, $_model[0] , $_modelClassName,'Models', $appName);

        if ($_initiateInstance) {
            $_ReflectionObject = new ReflectionClass($_modelClassName);
            if (!$_ReflectionObject instanceof ReflectionClass) {
                throw new SWIFT_Exception('Unable to load Reflection Class: ' . $_modelClassName);

                return false;
            }

            if (!is_subclass_of($_modelClassName, 'SWIFT_Model')) {
                throw new SWIFT_Exception($_modelClassName . ' is not inherited from SWIFT_Model');
            }

            $_ModelObject = call_user_func_array(array(&$_ReflectionObject, 'newInstance'), $_arguments);

            if (!$_ModelObject instanceof SWIFT_Base || !$_ModelObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception('Unable to load class: ' . $_modelClassName);

                return false;
            }

            if ($this->_classPointer) {
                if (isset($this->_classPointer->$_modelName)) {
                    unset($this->_classPointer->$_modelName);
                }

                $this->_classPointer->$_modelName = $_ModelObject;
            }
        }

        return true;
    }

    /**
     * Load the library from a given app into the local namespace
     *
     * @author Varun Shoor
     * @param string $_appName The Custom App to Load Library From
     * @param string $_libraryName The Library Name
     * @param array $_arguments The Function Arguments
     * @param bool $_initiateInstance Whether the Instance should be Initiated
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AppLibrary($_appName, $_libraryName, $_arguments = array(), $_initiateInstance = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->Library($_libraryName, $_arguments, $_initiateInstance, $_appName);
    }

    /**
     * Load the given library into the local namespace
     *
     * @author Varun Shoor
     * @param string $_libraryName The Library Name
     * @param mixed $_arguments The Function Arguments
     * @param bool $_initiateInstance Whether the Instance should be Initiated
     * @param mixed $_customAppName The Custom App to Load Library From
     * @param string $_appName The App name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the Library cannot be loaded or If the Class is not loaded
     */
    public function Library($_libraryName, $_arguments = array(), $_initiateInstance = true, $_customAppName = false, $_appName = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_lib = explode(':', $_libraryName);

        if (!is_array($_arguments) && !empty($_arguments)) {
            $_arguments = array($_arguments);
        } else if (!is_array($_arguments) && empty($_arguments)) {
            $_arguments = array();
        }

        // We first need to make sure that the library exists in the 'CORE' libraries folder
        $_libraryClassName = self::CLASS_PREFIX . $_libraryName;

        $_AppDirectory = $_classFileName = false;

        if (!class_exists($_libraryClassName, true)) {
            // Loading library with a custom classFileName leads to inconsistent exception and interface loading.
            // For example:
            // $this->Load->Library('MIME:MIMEList'); //Within class.SWIFT_Attachment.php, results in:
            // $_classFileName: {AbsoluteFilePath}/SWIFT/trunk/__swift/apps/base/models/Attachment/class.SWIFT_Attachment.php
            // which would derive the Exception name as - SWIFT_Attachment_Exception
            // and would try loading exceptionFile as - ./__swift/library/MIME/class.SWIFT_MIME_Exception.php
            // Adjusting to override classFileName only with matching Library & classFileName
            $_classFileName = IIF(stristr($this->_classFileName, substr($_libraryName, 0, strpos($_libraryName, ':')) . DIRECTORY_SEPARATOR), $this->_classFileName);

            // Is the app loaded for this object?
            $_SWIFT_AppObject = null;
            if ($this->_classPointer && !$_customAppName) {
                if (isset($this->_classPointer->App) && $this->_classPointer->App instanceof SWIFT_App && $this->_classPointer->App->GetIsClassLoaded()) {
                    self::AddApp($this->_classPointer->App);
                }
            } else if ($this->_classPointer && $_customAppName) {
                $_SWIFT_AppObject = SWIFT_App::Get($_customAppName);

                if ($_SWIFT_AppObject instanceof SWIFT_App && $_SWIFT_AppObject->GetIsClassLoaded()) {
                    self::AddApp($_SWIFT_AppObject);
                }
            }

            $__appName = $_appName;

            if (empty($__appName)) {
                if (isset($_SWIFT_AppObject) && $_SWIFT_AppObject instanceof SWIFT_App) {
                    $__appName = $_SWIFT_AppObject->GetName();
                }
            }
            if (empty($__appName)) {
                $__appName = $_customAppName;
            }

            self::LoadLibraryFile($_libraryName, $_classFileName, $__appName);
        }

        $_variableContainer = self::ParseLibraryInformation($_libraryName);
        extract($_variableContainer);

        $_libraryClassName = prepend_library_namespace($_lib, $_libraryName, $_libraryClassName, 'Library', $_appName);

        if ($_initiateInstance) {
            $_ReflectionObject = new ReflectionClass($_libraryClassName);
            if (!$_ReflectionObject instanceof ReflectionClass) {
                throw new SWIFT_Exception('Unable to load Reflection Class: ' . $_libraryClassName);

                return false;
            }

            $_LibraryObject = call_user_func_array(array(&$_ReflectionObject, 'newInstance'), $_arguments);

            if (!$_LibraryObject instanceof SWIFT_Base || !$_LibraryObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception('Unable to load class: ' . $_libraryClassName);

                return false;
            }

            if ($this->_classPointer) {
                if (isset($this->_classPointer->$_libraryName)) {
                    unset($this->_classPointer->$_libraryName);
                }

                $this->_classPointer->$_libraryName = $_LibraryObject;
            }
        }

        return true;
    }

    /**
     * Parses the Library Variable into the Directory and Library Name
     *
     * @author Varun Shoor
     * @param string $_libraryName The Library Name
     * @return array "true" on Success, "false" otherwise
     */
    static private function ParseLibraryInformation($_libraryName)
    {
        $_parsedData = self::ParseInformation($_libraryName);

        return array('_libraryName' => $_parsedData['_name'], '_directoryName' => $_parsedData['_directoryName'], '_libraryClassName' => $_parsedData['_className'], '_addonClassName' => $_parsedData['_addonClassName']);
    }

    /**
     * Parses the Model Variable into the Directory and Model Name
     *
     * @author Varun Shoor
     * @param string $_modelName The Model Name
     * @return array "true" on Success, "false" otherwise
     */
    static private function ParseModelInformation($_modelName)
    {
        $_parsedData = self::ParseInformation($_modelName);

        return array('_modelName' => $_parsedData['_name'], '_directoryName' => $_parsedData['_directoryName'], '_modelClassName' => $_parsedData['_className'], '_addonClassName' => $_parsedData['_addonClassName']);
    }

    /**
     * Parses the Library/Model Variable into the Directory and Library/Model Name
     *
     * @author Varun Shoor
     * @param string $_name The Library/Model Name
     * @return array "true" on Success, "false" otherwise
     */
    static private function ParseInformation($_name)
    {
        $_name = Clean($_name);

        $_directoryName = false;
        $_addonClassNamePre = false;
        if (stristr($_name, ':')) {
            $_nameContainer = explode(':', $_name);

            // Multiple Sub Directory Specified Staff:Group:System
            if (count($_nameContainer) > 1) {
                foreach ($_nameContainer as $_index => $_chunkName) {
                    // This is the file
                    if ($_index == (count($_nameContainer) - 1)) {
                        $_name = $_chunkName;
                    } else {
                        if (!$_directoryName) {
                            $_directoryName = '';
                        }

                        if (!$_addonClassNamePre && $_index == 0) {
                            $_addonClassNamePre = $_chunkName;
                        }

                        $_directoryName .= $_chunkName . '/';
                    }
                }
            }
        }

        $_className = self::CLASS_PREFIX . $_name;

        if ($_addonClassNamePre) {
            $_addonClassName = self::CLASS_PREFIX . $_addonClassNamePre;
        } else {
            $_addonClassName = $_name;
        }

        return array('_name' => $_name, '_directoryName' => $_directoryName, '_className' => $_className, '_addonClassName' => $_addonClassName);
    }

    /**
     * Load an Interface
     *
     * @author Varun Shoor
     * @param string $_interfaceName The Interface Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _Interface($_interfaceName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_variableContainer = self::ParseLibraryInformation($_interfaceName);
        extract($_variableContainer);

        // Interface already exists?
        if (interface_exists($_interfaceName, false)) {
            return true;
        }

        $_classFileName = $this->_classFileName;

        return self::LoadInterfaceFile($_interfaceName, $_classFileName);
    }

    /**
     * Load the Model File
     *
     * @author Varun Shoor
     * @param string $_modelName The Model Name
     * @param string $_classFileName The Class File Name
     * @param bool $_isInstanceCreationCall (OPTIONAL) This is true when this is called from Loader->Model
     * @param string $_appName The App Name
     * @return bool|string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Model cannot be loaded
     */
    static private function LoadModelFile($_modelName, $_classFileName = '', $_isInstanceCreationCall = false, $_appName = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_currentWorkingDirectory = getcwd();
        chdir(SWIFT_BASEPATH);

        $_model = explode(':', $_modelName);

        $_variableContainer = self::ParseModelInformation($_modelName);
        extract($_variableContainer);

        $_addonClassName = $_variableContainer['_addonClassName'];
        $_directoryName = $_variableContainer['_directoryName'];

        // We first need to make sure that the model exists in the 'CORE' libraries folder
        $__modelClassName = $_modelClassName = self::CLASS_PREFIX . $_modelName;
        $_modelClassName = prepend_library_namespace($_model, $_modelName, $_modelClassName, 'Models', $_appName);
        $_exceptionClassName = $_addonClassName . self::EXCEPTION_SUFFIX;
        $_interfaceClassName = $_addonClassName . self::INTERFACE_SUFFIX;

        $_modelCurrentDirectoryFile = $_appModelFile = false;

        if (!class_exists($_modelClassName, true)) {
            // Check cache first.. cache always takes precedence
            $_modelCacheContainer = self::GetCache(self::TYPE_MODEL, $_modelClassName);
            if (is_array($_modelCacheContainer)) {
                $_pathContainer = pathinfo($_modelCacheContainer[0]);
                $_interfaceCacheDirectoryFile = '';
                $_exceptionCacheDirectoryFile = '';
                $_modelCacheDirectoryFile = '';
                if (!empty($_pathContainer) && isset($_pathContainer['dirname'])) {
                    $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                    $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                    $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;

                    $_interfaceCacheDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_interfaceClassName . '.php';
                    $_exceptionCacheDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_exceptionClassName . '.php';
                    $_modelCacheDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $__modelClassName . '.php';
                }

                self::LoadLibraryInterface($_interfaceClassName, $_interfaceCacheDirectoryFile);
                self::LoadLibraryAddon($_exceptionClassName, $_exceptionCacheDirectoryFile);

                require_once($_modelCacheDirectoryFile);
            } else {
                $_swiftModelFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $__modelClassName . '.php';

                $_pathContainer = pathinfo($_swiftModelFile);
                if (isset($_pathContainer['dirname'])) {
                    $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                    $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                    $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;
                }

                $_swiftInterfaceFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_interfaceClassName . '.php';
                $_swiftExceptionFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_exceptionClassName . '.php';

                $_pathContainer = pathinfo($_classFileName);
                $_interfaceCurrentDirectoryFile = '';
                $_exceptionCurrentDirectoryFile = '';
                if (!empty($_pathContainer) && isset($_pathContainer['dirname'])) {
                    $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                    $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                    $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;

                    $_interfaceCurrentDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_interfaceClassName . '.php';
                    $_exceptionCurrentDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_exceptionClassName . '.php';
                    $_modelCurrentDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $__modelClassName . '.php';
                }

                if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded()) {
                    $_AppObject = $_SWIFT->Router->GetApp();
                    if ($_AppObject instanceof SWIFT_App && $_AppObject->GetIsClassLoaded()) {
                        self::AddApp($_AppObject);
                    }
                }

                if (file_exists($_swiftModelFile)) {
                    self::LoadLibraryInterface($_interfaceClassName, $_swiftInterfaceFile);
                    self::LoadLibraryAddon($_exceptionClassName, $_swiftExceptionFile);

                    self::SetCache(self::TYPE_MODEL, $_modelClassName, $_swiftModelFile, false);

                    require_once($_swiftModelFile);
                } else if ($_modelCurrentDirectoryFile && file_exists($_modelCurrentDirectoryFile)) {
                    self::LoadLibraryInterface($_interfaceClassName, $_interfaceCurrentDirectoryFile);
                    self::LoadLibraryAddon($_exceptionClassName, $_exceptionCurrentDirectoryFile);

                    self::SetCache(self::TYPE_MODEL, $_modelClassName, $_modelCurrentDirectoryFile, false);

                    require_once($_modelCurrentDirectoryFile);
                } else {
                    $_appFileLoaded = false;
                    $_appFileContainer = array();

                    foreach (self::$_loadedApps as $_appName => $_AppObject) {
                        $_appModelFile = $_AppObject->GetDirectory() . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $__modelClassName . '.php';

                        $_pathContainer = pathinfo($_appModelFile);
                        if (isset($_pathContainer['dirname'])) {
                            $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                            $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                            $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;
                        }

                        $_appInterfaceFile = $_AppObject->GetDirectory() . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_interfaceClassName . '.php';
                        $_appExceptionFile = $_AppObject->GetDirectory() . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_exceptionClassName . '.php';
                        $_appFileContainer[] = $_appModelFile;

                        if (file_exists($_appModelFile)) {
                            self::LoadLibraryInterface($_interfaceClassName, $_appInterfaceFile);
                            self::LoadLibraryAddon($_exceptionClassName, $_appExceptionFile);

                            self::SetCache(self::TYPE_MODEL, $_modelClassName, $_appModelFile, $_appName);

                            require_once($_appModelFile);

                            $_appFileLoaded = true;

                            break;
                        }
                    }

                    if (!$_appFileLoaded) {
                        throw new SWIFT_Exception('Unable to locate file in ' . $_swiftModelFile . IIF(!empty($_modelCurrentDirectoryFile), ' OR ' . $_modelCurrentDirectoryFile) . ' OR ' . implode(' OR ', $_appFileContainer));

                        return false;
                    }
                }
            }

            if (!class_exists($_modelClassName, true)) {
                throw new SWIFT_Exception('Unable to locate class: ' . $_modelClassName);
            }

            if (!$_isInstanceCreationCall) {
                $_ReflectionObject = new ReflectionClass($_modelClassName);
                if (!$_ReflectionObject instanceof ReflectionClass) {
                    throw new SWIFT_Exception('Unable to load Reflection Class: ' . $_modelClassName);

                    return false;
                }

                if (!is_subclass_of($_modelClassName, 'SWIFT_Model')) {
                    throw new SWIFT_Exception($_modelClassName . ' is not inherited from SWIFT_Model');
                }
            }
        }

        @chdir($_currentWorkingDirectory);

        return $_modelClassName;
    }

    /**
     * Load the Library File
     *
     * @author Varun Shoor
     * @param string $_libraryName The Library Name
     * @param string $_classFileName The Class File Name
     * @param string $_appName The App Name
     * @return bool|string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Library cannot be loaded
     */
    static private function LoadLibraryFile($_libraryName, $_classFileName = '', $_appName = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_currentWorkingDirectory = getcwd();
        chdir(SWIFT_BASEPATH);

        $_lib = explode(':', $_libraryName);

        $_variableContainer = self::ParseLibraryInformation($_libraryName);
        extract($_variableContainer);

        $_addonClassName = $_variableContainer['_addonClassName'];
        $_directoryName = $_variableContainer['_directoryName'];

        $_libraryClassName = $_libraryName;
        // We first need to make sure that the library exists in the 'CORE' libraries folder
        //(FIX: Avoid prefixing the Controller/View loading : Utsav Handa)
        if (substr(strtolower($_libraryName), 0, strlen('controller_')) != 'controller_' && (substr(strtolower($_libraryName), 0, strlen('view_'))) != 'view_') {
            $_libraryClassName = self::CLASS_PREFIX . $_libraryName;
        }

        $__libraryClassName = $_libraryClassName;
        $_libraryClassName = prepend_library_namespace($_lib, $_libraryName, $_libraryClassName, 'Library', $_appName);
        $_exceptionClassName = $_addonClassName . self::EXCEPTION_SUFFIX;
        $_interfaceClassName = $_addonClassName . self::INTERFACE_SUFFIX;

        $_libraryCurrentDirectoryFile = $_appLibraryFile = false;

        if (!class_exists($_libraryClassName, true)) {
            // Check cache first.. cache always takes precedence
            $_libraryCacheContainer = self::GetCache(self::TYPE_LIBRARY, $_libraryClassName);
            if (is_array($_libraryCacheContainer)) {
                $_pathContainer = pathinfo($_libraryCacheContainer[0]);
                $_libraryCacheDirectoryFile = '';
                $_interfaceCacheDirectoryFile = '';
                $_exceptionCacheDirectoryFile = '';
                if (!empty($_pathContainer) && isset($_pathContainer['dirname'])) {
                    $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                    $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                    $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;

                    $_interfaceCacheDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_interfaceClassName . '.php';
                    $_exceptionCacheDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_exceptionClassName . '.php';
                    $_libraryCacheDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $__libraryClassName . '.php';
                }

                self::Debug('Loading via Cache: ' . $_libraryCacheDirectoryFile);

                self::LoadLibraryInterface($_interfaceClassName, $_interfaceCacheDirectoryFile);
                self::LoadLibraryAddon($_exceptionClassName, $_exceptionCacheDirectoryFile);

                require_once($_libraryCacheDirectoryFile);
            } else {
                $_swiftLibraryFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $__libraryClassName . '.php';

                $_pathContainer = pathinfo($_swiftLibraryFile);
                if (isset($_pathContainer['dirname'])) {
                    $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                    $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                    $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;
                }

                $_swiftInterfaceFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_interfaceClassName . '.php';
                $_swiftExceptionFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_exceptionClassName . '.php';

                $_pathContainer = pathinfo($_classFileName);
                $_interfaceCurrentDirectoryFile = '';
                $_exceptionCurrentDirectoryFile = '';
                if (!empty($_pathContainer) && isset($_pathContainer['dirname'])) {
                    $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                    $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                    $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;

                    $_interfaceCurrentDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_interfaceClassName . '.php';
                    $_exceptionCurrentDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $_exceptionClassName . '.php';
                    $_libraryCurrentDirectoryFile = $_pathContainer['dirname'] . '/' . 'class.' . $__libraryClassName . '.php';
                }

                if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded()) {
                    $_AppObject = $_SWIFT->Router->GetApp();
                    if ($_AppObject instanceof SWIFT_App && $_AppObject->GetIsClassLoaded()) {
                        self::AddApp($_AppObject);
                    }
                }

                $__found = false;
                $__swiftLibraryFile = $_swiftLibraryFile;
                if (isset($_pathContainer['dirname'])) {
                    $__swiftLibraryFile = str_replace('models', 'library',
                            $_pathContainer['dirname']) . '/' . 'class.' . $__libraryClassName . '.php';
                    $__found = file_exists($__swiftLibraryFile);
                }
                if ($__found) {
                    self::LoadLibraryInterface($_interfaceClassName, $_swiftInterfaceFile);
                    self::LoadLibraryAddon($_exceptionClassName, $_swiftExceptionFile);

                    self::SetCache(self::TYPE_LIBRARY, $_libraryClassName, $__swiftLibraryFile, false);

                    require_once($__swiftLibraryFile);
                } else if (file_exists($_swiftLibraryFile)) {
                    self::LoadLibraryInterface($_interfaceClassName, $_swiftInterfaceFile);
                    self::LoadLibraryAddon($_exceptionClassName, $_swiftExceptionFile);

                    self::SetCache(self::TYPE_LIBRARY, $_libraryClassName, $_swiftLibraryFile, false);

                    require_once($_swiftLibraryFile);
                } else if ($_libraryCurrentDirectoryFile && file_exists($_libraryCurrentDirectoryFile)) {
                    self::LoadLibraryInterface($_interfaceClassName, $_interfaceCurrentDirectoryFile);
                    self::LoadLibraryAddon($_exceptionClassName, $_exceptionCurrentDirectoryFile);

                    self::SetCache(self::TYPE_LIBRARY, $_libraryClassName, $_libraryCurrentDirectoryFile, false);

                    require_once($_libraryCurrentDirectoryFile);
                } else {
                    $_appFileLoaded = false;
                    $_appFileContainer = array();

                    foreach (self::$_loadedApps as $_appName => $_AppObject) {
                        $_appLibraryFile = $_AppObject->GetDirectory() . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $__libraryClassName . '.php';

                        $_pathContainer = pathinfo($_appLibraryFile);
                        if (isset($_pathContainer['dirname'])) {
                            $_parentDirectoryName = 'SWIFT_' . substr(StripTrailingSlash($_pathContainer['dirname']), strrpos($_pathContainer['dirname'], '/') + 1);

                            $_exceptionClassName = $_parentDirectoryName . self::EXCEPTION_SUFFIX;
                            $_interfaceClassName = $_parentDirectoryName . self::INTERFACE_SUFFIX;
                        }

                        $_appInterfaceFile = $_AppObject->GetDirectory() . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_interfaceClassName . '.php';
                        $_appExceptionFile = $_AppObject->GetDirectory() . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . 'class.' . $_exceptionClassName . '.php';
                        $_appFileContainer[] = $_appLibraryFile;

                        if (file_exists($_appLibraryFile)) {
                            self::LoadLibraryInterface($_interfaceClassName, $_appInterfaceFile);
                            self::LoadLibraryAddon($_exceptionClassName, $_appExceptionFile);

                            self::SetCache(self::TYPE_LIBRARY, $_libraryClassName, $_appLibraryFile, $_appName);

                            require_once($_appLibraryFile);

                            $_appFileLoaded = true;

                            break;
                        }
                    }

                    if (!$_appFileLoaded) {
                        throw new SWIFT_Exception('Unable to locate file in ' . $_swiftLibraryFile . IIF(!empty($_libraryCurrentDirectoryFile), ' OR ' . $_libraryCurrentDirectoryFile) . ' OR ' . implode(' OR ', $_appFileContainer));
                    }
                }
            }

            if (!class_exists($_libraryClassName, true)) {
                throw new SWIFT_Exception('Unable to locate class: ' . $_libraryClassName);
            }
        }

        @chdir($_currentWorkingDirectory);

        return $_libraryClassName;
    }

    /**
     * Load the Interface File
     *
     * @author Varun Shoor
     * @param string $_interfaceName The Interface Name
     * @param string $_classFileName The Class File Name
     * @return bool|string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Library cannot be loaded
     */
    static private function LoadInterfaceFile($_interfaceName, $_classFileName = '', $_appName = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_currentWorkingDirectory = getcwd();
        chdir(SWIFT_BASEPATH);

        $_lib = explode(':', $_interfaceName);
        $_variableContainer = self::ParseLibraryInformation($_interfaceName);
        extract($_variableContainer);

        $_libraryName = $_variableContainer['_libraryName'];
        $_directoryName = $_variableContainer['_directoryName'];

        // We first need to make sure that the library exists in the 'CORE' libraries folder
        $__libraryClassName = self::CLASS_PREFIX . $_libraryName . self::INTERFACE_SUFFIX;
        $_libraryClassName = $_interfaceClassName = prepend_library_namespace($_lib, $_libraryName, $__libraryClassName, 'Library', $_appName);
        $_interfaceFileName = 'class.' . self::CLASS_PREFIX . $_libraryName . self::INTERFACE_SUFFIX . '.php';

        $_libraryCurrentDirectoryFile = $_appLibraryFile = false;

        if (!interface_exists($_interfaceClassName, true)) {
            // Check cache first.. cache always takes precedence
            $_interfaceCacheContainer = self::GetCache(self::TYPE_INTERFACE, $_interfaceClassName);
            if (is_array($_interfaceCacheContainer)) {
                self::LoadLibraryInterface($_interfaceClassName, $_interfaceCacheContainer[0]);
            } else {
                $_swiftLibraryInterfaceFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . $_interfaceFileName;
                $_swiftModelInterfaceFile = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . $_interfaceFileName;

                $_pathContainer = pathinfo($_classFileName);
                if (!empty($_pathContainer) && isset($_pathContainer['dirname'])) {
                    $_interfaceCurrentDirectoryFile = $_pathContainer['dirname'] . '/' . $_interfaceFileName;
                }

                if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded()) {
                    $_AppObject = $_SWIFT->Router->GetApp();
                    if ($_AppObject instanceof SWIFT_App && $_AppObject->GetIsClassLoaded()) {
                        self::AddApp($_AppObject);
                    }
                }

                if (file_exists($_swiftLibraryInterfaceFile)) {
                    self::LoadLibraryInterface($_interfaceClassName, $_swiftLibraryInterfaceFile);

                } else if (file_exists($_swiftModelInterfaceFile)) {
                    self::LoadLibraryInterface($_interfaceClassName, $_swiftModelInterfaceFile);

                } else if (isset($_interfaceCurrentDirectoryFile) && file_exists($_interfaceCurrentDirectoryFile)) {
                    self::LoadLibraryInterface($_interfaceClassName, $_interfaceCurrentDirectoryFile);

                } else {
                    $_appFileLoaded = false;
                    $_appFileContainer = array();

                    foreach (self::$_loadedApps as $_appName => $_AppObject) {
                        $_appFileContainer[] = $_AppObject->GetDirectory() . '/' . SWIFT_LIBRARYDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . $_interfaceFileName;
                        $_appFileContainer[] = $_AppObject->GetDirectory() . '/' . SWIFT_MODELSDIRECTORY . '/' . IIF($_directoryName, $_directoryName) . $_interfaceFileName;

                        foreach ($_appFileContainer as $_appInterfaceFile) {
                            if (file_exists($_appInterfaceFile)) {
                                self::LoadLibraryInterface($_interfaceClassName, $_appInterfaceFile);

                                $_appFileLoaded = true;

                                break;
                            }
                        }

                        // Break from parent loop...
                        if ($_appFileLoaded) {
                            break;
                        }
                    }

                    if (!$_appFileLoaded) {
                        throw new SWIFT_Exception('Unable to locate file in ' . $_interfaceFileName . IIF(!empty($_libraryCurrentDirectoryFile), ' OR ' . $_libraryCurrentDirectoryFile) . ' OR ' . implode(' OR ', $_appFileContainer));

                        return false;
                    }
                }
            }

            if (!interface_exists($_interfaceClassName, false)) {
                throw new SWIFT_Exception('Unable to locate interface: ' . $_interfaceClassName);

                return false;
            }
        }

        chdir($_currentWorkingDirectory);

        return $_interfaceClassName;
    }

    /**
     * Load the Library Addon (Exception/Interface) File
     *
     * @author Varun Shoor
     * @param string $_addonClassName The Exception/Interface Class Name
     * @param string $_addonFile The Exception File
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function LoadLibraryAddon($_addonClassName, $_addonFile)
    {
        if (class_exists($_addonClassName, true)) {
            return true;
        }

        if (file_exists($_addonFile)) {
            chdir(SWIFT_BASEPATH);
            require_once($_addonFile);

            return true;
        }

        return false;
    }

    /**
     * Load the Library Interface File
     *
     * @author Varun Shoor
     * @param string $_interfaceName The Interface Class Name
     * @param string $_interfaceFile The Interface File
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function LoadLibraryInterface($_interfaceName, $_interfaceFile)
    {
        if (interface_exists($_interfaceName, false)) {
            return true;
        }

        if (file_exists($_interfaceFile)) {
            chdir(SWIFT_BASEPATH);
            require_once($_interfaceFile);

            return true;
        }

        return false;
    }

    /**
     * Load the given view name
     *
     * @author Varun Shoor
     * @param string $_viewName The View Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function View($_viewName, $interface = SWIFT_INTERFACE)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_viewName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT = SWIFT::getInstance();

        if (!self::LoadView($_viewName)) {
            return false;
        }

        // Try to guess the namespace of the View
        $_viewNamespace = "{$_SWIFT->App->GetName()}\\{$interface}\\";
        $_viewClassName = SWIFT_View::VIEW_CLASS_PREFIX . $_viewName;

        $_ReflectionObject = new ReflectionClass($_viewNamespace . $_viewClassName);
        if (!$_ReflectionObject instanceof ReflectionClass) {
            // The guess failed, let's use the legacy way to load it
            $_ReflectionObject = new ReflectionClass($_viewClassName);
        } elseif (!$_ReflectionObject instanceof ReflectionClass) {
            // I give up
            throw new SWIFT_Exception('Unable to load Reflection Class: ' . $_viewClassName);
        }

        $_ViewObject = call_user_func_array(array(&$_ReflectionObject, 'newInstance'), array());

        if (!$_ViewObject instanceof SWIFT_View || !$_ViewObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception('Unable to load class: ' . $_ViewObject);
        }

        if ($this->_classPointer) {
            if (isset($this->_classPointer->$_viewClassName)) {
                unset($this->_classPointer->$_viewClassName);
            }

            $this->_classPointer->$_viewClassName = $_ViewObject;
        }

        return true;
    }

    /**
     * Load the view for the currently active app and interface
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Received or If the Classes are Invalid
     */
    public static function LoadView($_viewName, $_basedir = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_viewName = Clean($_viewName);
        if (empty($_viewName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if (!$_SWIFT->App instanceof SWIFT_App || !$_SWIFT->Interface instanceof SWIFT_Interface || !$_SWIFT->App->GetIsClassLoaded() || !$_SWIFT->Interface->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        chdir(SWIFT_BASEPATH);
        if (empty($_basedir)) {
            $_basedir = $_SWIFT->App->GetDirectory() . '/' . $_SWIFT->Interface->GetName();
        }
        $_include_file = $_basedir . '/' . SWIFT_View::FILE_PREFIX . SWIFT_View::VIEW_CLASS_PREFIX . $_viewName . '.php';
        require_once $_include_file;

        return true;
    }

    /**
     * Load the Model File
     *
     * @author Varun Shoor
     * @param string $_modelName The Model Name
     * @param string $_appName (OPTIONAL) The App Name
     * @param bool $_checkAppRegistration (OPTIONAL) Whether to Check If App is Registered or not
     * @return bool "true" on Success, "false" otherwise
     */
    public static function LoadModel($_modelName, $_appName = '', $_checkAppRegistration = true)
    {
        if (!empty($_appName) && (($_checkAppRegistration == true && SWIFT_App::IsInstalled($_appName)) || !$_checkAppRegistration)) {
            $_SWIFT_AppObject = SWIFT_App::Get($_appName);

            if ($_SWIFT_AppObject instanceof SWIFT_App && $_SWIFT_AppObject->GetIsClassLoaded()) {
                self::AddApp($_SWIFT_AppObject);
            }
        }

        self::LoadModelFile($_modelName, '', false, $_appName);

        return true;
    }

    /**
     * Load the Library File
     *
     * @author Varun Shoor
     * @param string $_libraryName The Library Name
     * @param string $_appName (OPTIONAL) The App Name
     * @param bool $_checkAppRegistration (OPTIONAL) Whether to Check If App is Registered or not
     * @param string $_classFileName (OPTIONAL) The class filename
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function LoadLibrary($_libraryName, $_appName = '', $_checkAppRegistration = true, $_classFileName = '')
    {
        if (!empty($_appName) && (($_checkAppRegistration == true && SWIFT_App::IsInstalled($_appName)) || !$_checkAppRegistration)) {
            $_SWIFT_AppObject = SWIFT_App::Get($_appName);

            if ($_SWIFT_AppObject instanceof SWIFT_App && $_SWIFT_AppObject->GetIsClassLoaded()) {
                self::AddApp($_SWIFT_AppObject);
            }
        }

        self::LoadLibraryFile($_libraryName, $_classFileName, $_appName);

        return true;
    }

    /**
     * Load the Interface File
     *
     * @author Varun Shoor
     * @param string $_libraryName The Library Name
     * @param string $_appName (OPTIONAL) The App Name
     * @param bool $_checkAppRegistration (OPTIONAL) Whether to Check If App is Registered or not
     * @return bool "true" on Success, "false" otherwise
     */
    public static function LoadInterface($_libraryName, $_appName = '', $_checkAppRegistration = true)
    {
        if (!empty($_appName) && (($_checkAppRegistration == true && SWIFT_App::IsInstalled($_appName)) || !$_checkAppRegistration)) {
            $_SWIFT_AppObject = SWIFT_App::Get($_appName);

            if ($_SWIFT_AppObject instanceof SWIFT_App && $_SWIFT_AppObject->GetIsClassLoaded()) {
                self::AddApp($_SWIFT_AppObject);
            }
        }

        self::LoadInterfaceFile($_libraryName, '', $_appName);

        return true;
    }

    /**
     * Debug Message Processing Function
     *
     * @author Varun Shoor
     * @param string $_debugMessage
     * @return bool "true" on Success, "false" otherwise
     */
    public static function Debug($_debugMessage)
    {
        if (!defined('DEBUG_LOADER')) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Log->Log($_debugMessage, SWIFT_Log::TYPE_OK, 'SWIFT_Loader');

        return true;
    }

    /**
     * Registers the Auto Load for Loader
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RegisterAutoLoad()
    {
        spl_autoload_register(array('SWIFT_Loader', 'AutoLoad'), true, true);

        // By default add both base & core
        self::AddApp(SWIFT_App::Get(APP_CORE));

        try {
            self::AddApp(SWIFT_App::Get(APP_BASE));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        return true;
    }

    /**
     * Auto Load the Dwoo files
     *
     * @author Varun Shoor
     * @param string $_className The Class Name to Load
     * @return bool "true" on Success, "false" otherwise
     */
    public static function AutoLoad($_className)
    {
        chdir(SWIFT_BASEPATH);

        self::Debug('Auto Load Triggered: ' . $_className);

        if (substr($_className, 0, 6) === 'SWIFT_' && !class_exists($_className, true) && !strstr($_className, ':')
            && substr($_className, -strlen(self::EXCEPTION_SUFFIX)) != self::EXCEPTION_SUFFIX && substr($_className, -strlen(self::INTERFACE_SUFFIX)) != self::INTERFACE_SUFFIX) {
            $_finalClassName = substr($_className, 6);

            self::Debug('Attempting to Auto Load: ' . $_finalClassName);

            self::LoadAutoLoadFile($_finalClassName);

            return true;
        } else if (substr($_className, 0, 6) === 'SWIFT_' && substr($_className, -strlen('_Interface')) === '_Interface' && !interface_exists($_className)) {
            $_preClassName = substr($_className, 6);
            $_finalClassName = substr($_preClassName, 0, strlen($_preClassName) - strlen('_Interface'));

            // Once we have the directories to look into, we will now tokenize the class and make a probable list of sub directories
            $_subdirectoryChunks = preg_split('/([[:upper:]][[:lower:]]+)/', $_finalClassName, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            $_searchDirectoryList = array(); // (Path, App)

            $_searchDirectoryList[] = array('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/', false);
            $_searchDirectoryList[] = array('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_MODELSDIRECTORY . '/', false);

            foreach (self::$_loadedApps as $_appName => $_AppObject) {
                $_searchDirectoryList[] = array($_AppObject->GetDirectory() . '/' . SWIFT_LIBRARYDIRECTORY . '/', $_appName);
                $_searchDirectoryList[] = array($_AppObject->GetDirectory() . '/' . SWIFT_MODELSDIRECTORY . '/', $_appName);
            }

            $_finalAppName = false;
            $_finalLoadStatement = '';

            foreach ($_searchDirectoryList as $_directoryContainer) {
                $_directoryName = $_directoryContainer[0];
                $_appName = $_directoryContainer[1];

                self::Debug('Searching: ' . $_directoryName . 'class.SWIFT_' . $_className . '.php');

                // First search directory under file
                if (file_exists($_directoryName . 'class.' . $_className . '.php')) {
                    $_finalLoadStatement = $_className;
                    $_finalAppName = $_appName;
                    break;
                }

                // Now attempts searches using sub directory chunks.. so if the class is TicketPost, it will search for /Ticket/class.SWIFT_TicketPost.php and /TicketPost/class.SWIFT_TicketPost.php
                $_subDirectoryCombined = '';
                foreach ($_subdirectoryChunks as $_subdirectoryChunk) {
                    $_subDirectoryCombined .= $_subdirectoryChunk;

                    self::Debug('Searching: ' . $_directoryName . $_subDirectoryCombined . '/class.SWIFT_' . $_className . '.php');
                    self::Debug('Searching: ' . $_directoryName . $_subdirectoryChunk . '/class.SWIFT_' . $_className . '.php');

                    if (file_exists($_directoryName . $_subDirectoryCombined . '/class.' . $_className . '.php')) {
                        $_finalLoadStatement = $_subDirectoryCombined . ':' . $_finalClassName;
                        $_finalAppName = $_appName;
                        break;

                    } else if (file_exists($_directoryName . $_subdirectoryChunk . '/class.' . $_className . '.php')) {
                        $_finalLoadStatement = $_subdirectoryChunk . $_finalClassName;
                        $_finalAppName = $_appName;
                        break;

                    }
                }

                // If we have found a result, then break the parent loop too!
                if (!empty($_finalLoadStatement)) {
                    break;
                }
            }

            self::LoadInterface($_finalLoadStatement, $_finalAppName);

        } else if (substr($_className, 0, 6) === "SWIFT\\") {
            self::LoadAutoLoadNamespaceFile($_className);

            return true;
        } else if (strpos($_className, '\\') !== false
            && in_array(strtolower(substr($_className, 0, strpos($_className, '\\'))), get_swift_namespaces())) {
            $chunks = explode('\\', $_className);

            $appFolder = strtolower($chunks[0]);

            $moduleFolder = count($chunks) > 2 ? strtolower($chunks[1]) : 'config';

            $classFile = $chunks[count($chunks) - 1] . '.php';

            $swiftClassFile = 'class.' . $classFile;

            array_splice($chunks, 0, 2);

            array_pop($chunks);
            $subFolders = array_reduce($chunks, function ($itr, $val) {
                return $itr . '/' . $val . '/';
            }, '');

            $fileRelativePath = $appFolder . '/' . $moduleFolder . '/' . $subFolders . $swiftClassFile;

            $coreAppsPath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_COREAPPSDIRECTORY . '/' . $fileRelativePath;
            $appsPath = './' . SWIFT_APPSDIRECTORY . '/' . $fileRelativePath;

            //check in core apps first
            if (!class_exists($_className)) {
                if (file_exists($coreAppsPath)) {
                    require_once $coreAppsPath;
                } else if (file_exists($appsPath)) {
                    require_once $appsPath;
                }
            }

            return true;
        }

        self::Debug('Auto Load IF Block Pass');

        return false;
    }

    /**
     * Load the file from a namespaced call
     *
     * @author Varun Shoor
     * @param string $_className
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LoadAutoLoadNamespaceFile($_className)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_className)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $_classChunks = explode("\\", $_className);

        $_appName = $_appModeActive = false;

        foreach ($_classChunks as $_chunkName) {
            $_processChunkName = mb_strtolower($_chunkName);

            if ($_processChunkName == 'apps') {
                $_appModeActive = true;

                continue;
            } else if ($_appModeActive === true && !$_appName) {
                $_appName = $_chunkName;
                $_appModeActive = false;

                continue;
            }


        }


        return true;
    }

    /**
     * Load Auto Load File by searching on specific locations
     *
     * @author Varun Shoor
     * @param string $_className
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function LoadAutoLoadFile($_className)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_className = Clean($_className);

        // First check the cache
        $_libraryCache = self::GetCache(self::TYPE_LIBRARY, 'SWIFT_' . $_className);
        if (is_array($_libraryCache)) {
            self::Debug('Found Library In Cache: ' . $_className);

            self::LoadLibraryFile($_className);

            return true;
        }

        $_modelCache = self::GetCache(self::TYPE_MODEL, 'SWIFT_' . $_className);
        if (is_array($_modelCache)) {
            self::Debug('Found Model In Cache: ' . $_className);

            self::LoadModelFile($_className);

            return true;
        }

        // Add the currently loaded app if not added already
        if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded()) {
            $_AppObject = $_SWIFT->Router->GetApp();
            if ($_AppObject instanceof SWIFT_App && $_AppObject->GetIsClassLoaded() && !isset(self::$_loadedApps[$_AppObject->GetName()])) {
                self::AddApp($_AppObject);
            }
        }

        $_searchDirectoryList = array(); // (Path, App, Type)

        $_searchDirectoryList[] = array('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/', false, self::TYPE_LIBRARY);
        $_searchDirectoryList[] = array('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_MODELSDIRECTORY . '/', false, self::TYPE_MODEL);
        foreach (self::$_loadedApps as $_appName => $_AppObject) {
            $_searchDirectoryList[] = array($_AppObject->GetDirectory() . '/' . SWIFT_LIBRARYDIRECTORY . '/', $_appName, self::TYPE_LIBRARY);
            $_searchDirectoryList[] = array($_AppObject->GetDirectory() . '/' . SWIFT_MODELSDIRECTORY . '/', $_appName, self::TYPE_MODEL);
        }

        // Once we have the directories to look into, we will now tokenize the class and make a probable list of sub directories
        $_subdirectoryChunks = preg_split('/([[:upper:]][[:lower:]]+)/', $_className, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $_finalAppName = false;
        $_finalLoadStatement = '';
        $_finalLoadType = false;

        foreach ($_searchDirectoryList as $_directoryContainer) {
            $_directoryName = $_directoryContainer[0];
            $_appName = $_directoryContainer[1];
            $_loadType = $_directoryContainer[2];

            self::Debug('Searching: ' . $_directoryName . 'class.SWIFT_' . $_className . '.php');

            // First search directory under file
            if (file_exists($_directoryName . 'class.SWIFT_' . $_className . '.php')) {
                $_finalLoadStatement = $_className;
                $_finalAppName = $_appName;
                $_finalLoadType = $_loadType;

                break;
            }

            // Now attempts searches using sub directory chunks.. so if the class is TicketPost, it will search for /Ticket/class.SWIFT_TicketPost.php and /TicketPost/class.SWIFT_TicketPost.php
            $_subDirectoryCombined = '';
            foreach ($_subdirectoryChunks as $_subdirectoryChunk) {
                $_subDirectoryCombined .= $_subdirectoryChunk;

                self::Debug('Searching: ' . $_directoryName . $_subDirectoryCombined . '/class.SWIFT_' . $_className . '.php');
                self::Debug('Searching: ' . $_directoryName . $_subdirectoryChunk . '/class.SWIFT_' . $_className . '.php');

                if (file_exists($_directoryName . $_subDirectoryCombined . '/class.SWIFT_' . $_className . '.php')) {
                    $_finalLoadStatement = $_subDirectoryCombined . ':' . $_className;
                    $_finalAppName = $_appName;
                    $_finalLoadType = $_loadType;

                    break;
                } else if (file_exists($_directoryName . $_subdirectoryChunk . '/class.SWIFT_' . $_className . '.php')) {
                    $_finalLoadStatement = $_subdirectoryChunk . ':' . $_className;
                    $_finalAppName = $_appName;
                    $_finalLoadType = $_loadType;

                    break;
                }
            }

            // If we still didnt find anything.. try searching by removing first chunk from sub directory,
            if (!$_finalLoadType) {
                $_subDirectoryCombined = '';
                foreach ($_subdirectoryChunks as $_index => $_subdirectoryChunk) {
                    $_parsedSubDirectoryChunk = mb_strtolower($_subdirectoryChunk);
                    $_parsedAppName = mb_strtolower($_appName);
                    if ($_index == 0) { // Tickets = Ticket
                        continue;
                    }

                    $_subDirectoryCombined .= $_subdirectoryChunk;

                    self::Debug('Searching: ' . $_directoryName . $_subDirectoryCombined . '/class.SWIFT_' . $_className . '.php');
                    if (file_exists($_directoryName . $_subDirectoryCombined . '/class.SWIFT_' . $_className . '.php')) {
                        $_finalLoadStatement = $_subDirectoryCombined . ':' . $_className;
                        $_finalAppName = $_appName;
                        $_finalLoadType = $_loadType;

                        break;
                    }
                }
            }

            // If we have found a result, then break the parent loop too!
            if (!empty($_finalLoadStatement)) {
                break;
            }
        }

        self::Debug('Final Load Statement: ' . $_finalLoadStatement);

        if (!empty($_finalLoadStatement) && self::IsValidType($_finalLoadType)) {
            if (!empty($_finalAppName)) {
                if (!isset(self::$_loadedApps[$_finalAppName])) {
                    self::AddApp(SWIFT_App::Get($_finalAppName));
                }
            }

            if ($_finalLoadType == self::TYPE_LIBRARY) {
                self::LoadLibrary($_finalLoadStatement, $_finalAppName);

                return true;
            } else if ($_finalLoadType == self::TYPE_MODEL) {
                self::LoadModel($_finalLoadStatement, $_finalAppName);

                return true;
            }
        }

        return false;
    }

    /**
     * Loads the Loader Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LoadCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (SWIFT_App::IsInstalled(APP_BASE)) {
            self::AddApp(SWIFT_App::Get(APP_BASE));
        }

        $_cacheFile = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . self::CACHE_FILE;

        if (!file_exists($_cacheFile)) {
            self::WarmupCache();

            return true;
        }

        $_fileContents = @file_get_contents($_cacheFile);
        $_cacheList = array();
        if (!empty($_fileContents)) {
            $_cacheList = @unserialize($_fileContents);
            if (!is_array($_cacheList)) {
                $_cacheList = array();
            }
        }

        self::$_cacheList = $_cacheList;

        return true;
    }

    /**
     * Retrieve from Cache Entry
     *
     * @author Varun Shoor
     * @param int $_loadType
     * @param string $_className
     * @return mixed File Name on success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function GetCache($_loadType, $_className)
    {
        $_className = strtolower($_className);
        if (isset(self::$_cacheList[$_loadType][$_className])) {
            return self::$_cacheList[$_loadType][$_className];
        }

        return false;
    }

    /**
     * @author Utsav Handa
     *
     * @param SWIFT_App $_App
     * @param SWIFT_Interface $_Interface
     * @param int $_loadType
     * @param string $_className
     *
     * @return string File Name on success, "false" otherwise
     */
    public static function GetCacheByAppInterface(SWIFT_App $_App, SWIFT_Interface $_Interface, $_loadType, $_className)
    {
        // Var(s)
        $_appName = $_App->GetName();
        $_interfaceName = $_Interface->GetName();

        // Checking whether App-Interface directory cache has been loaded, else inflate
        if (!self::GetCache($_loadType, $_appName . $_interfaceName)) {
            $_searchDirectoryList[] = array($_App->GetDirectory() . DIRECTORY_SEPARATOR . $_interfaceName, $_appName, $_loadType, $_appName . $_interfaceName);
            self::InflateCache($_searchDirectoryList);
            self::SetCache($_loadType, $_appName . $_interfaceName, 'true', null);
        }

        return self::GetCache($_loadType, $_appName . $_interfaceName . $_className);
    }

    /**
     * Update Cache
     *
     * @author Varun Shoor
     * @param int $_loadType
     * @param string $_className
     * @param string $_fileName
     * @param string $_appName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function SetCache($_loadType, $_className, $_fileName, $_appName)
    {
        if (empty($_className) || empty($_fileName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (isset(self::$_cacheList[$_loadType][strtolower($_className)])) {
            return true;
        }

        if (!isset(self::$_cacheList[$_loadType])) {
            self::$_cacheList[$_loadType] = array();
        }

        self::$_cacheList[$_loadType][strtolower($_className)] = array($_fileName, $_appName);
        self::$_isCacheUpdated = true;

        return true;
    }

    /**
     * Rebuilds the Cache if there was an update
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        if (!self::$_isCacheUpdated || SWIFT::IsDebug()) {
            //return true;
        }

        chdir(SWIFT_BASEPATH);
        $_cachePathFile = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . self::CACHE_FILE;
        file_put_contents($_cachePathFile, serialize(self::$_cacheList));
        @chmod($_cachePathFile, 0666);

        self::$_isCacheUpdated = false;

        return true;
    }

    /**
     * Warms up the cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function WarmupCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_searchDirectoryList = array();

        $_searchDirectoryList[] = array('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/', false, self::TYPE_LIBRARY);
        $_searchDirectoryList[] = array('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_MODELSDIRECTORY . '/', false, self::TYPE_MODEL);

        $_appList = SWIFT_App::GetInstalledApps();

        foreach ($_appList as $_appName) {
            $_AppObject = false;

            try {
                $_AppObject = SWIFT_App::Get($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_AppObject instanceof SWIFT_App || !$_AppObject->GetIsClassLoaded()) {
                continue;
            }

            // Preparing directory list - Path, AppName, LoadType, ClassPrefix
            $_searchDirectoryList[] = array($_AppObject->GetDirectory() . '/' . SWIFT_LIBRARYDIRECTORY . '/', $_appName, self::TYPE_LIBRARY);
            $_searchDirectoryList[] = array($_AppObject->GetDirectory() . '/' . SWIFT_MODELSDIRECTORY . '/', $_appName, self::TYPE_MODEL);
        }

        return self::InflateCache($_searchDirectoryList);
    }


    /**
     * @author Utsav Handa
     *
     * @param array $_searchDirectoryList
     *
     * @return bool
     */
    public static function InflateCache(array $_searchDirectoryList)
    {
        // Inflate cache from specific search directories
        foreach ($_searchDirectoryList as $_directoryContainer) {
            list($_directoryPath, $_appName, $_loadType) = $_directoryContainer;
            $_classPrefix = isset($_directoryContainer[3]) ? $_directoryContainer[3] : '';

            $_cacheFileList = self::GetCacheFileList($_directoryPath);
            foreach ($_cacheFileList as $_className => $_filePath) {
                // Check for interface and exceptions...
                if (substr($_className, -strlen(self::EXCEPTION_SUFFIX)) == self::EXCEPTION_SUFFIX) {
                    $_loadType = self::TYPE_EXCEPTION;
                } else if (substr($_className, -strlen(self::INTERFACE_SUFFIX)) == self::INTERFACE_SUFFIX) {
                    $_loadType = self::TYPE_INTERFACE;
                }

                self::SetCache($_loadType, $_classPrefix . $_className, $_filePath, $_appName);
            }
        }

        return self::RebuildCache();
    }


    /**
     * Retrieves the Cache File List from the Directory
     *
     * @author Varun Shoor
     * @param string $_directoryPath
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function GetCacheFileList($_directoryPath)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_directoryPath = StripTrailingSlash($_directoryPath);

        if (!file_exists($_directoryPath) || !is_dir($_directoryPath)) {
            return array();
        }

        $_cacheFileList = array();

        if ($_directoryHandle = opendir($_directoryPath)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                if ($_fileName != '.' && $_fileName != '..') {
                    $_filePath = $_directoryPath . '/' . $_fileName;

                    if (is_dir($_filePath)) {
                        $_cacheFileList = array_merge($_cacheFileList, self::GetCacheFileList($_filePath));
                    } else {
                        $_matches = array();
                        if (preg_match('/^class\.(.*)\.php$/i', $_fileName, $_matches)) {
                            $_cacheFileList[$_matches[1]] = $_filePath;
                        }
                    }
                }
            }
            closedir($_directoryHandle);
        }

        return $_cacheFileList;
    }
}

?>
