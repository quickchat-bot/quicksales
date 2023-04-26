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

/**
 * The File Compressor
 *
 * @author Varun Shoor
 */
class SWIFT_Compressor extends SWIFT_Library
{
    const TYPE_CSS = 'css';
    const TYPE_JS = 'js';
    const TYPE_TEMPLATE = 'tpl';

    const FILTER_LOGIN = 'login';
    const FILTER_CORE = 'core';
    const FILTER_THIRDPARTY = 'thirdparty';

    /**
     * ---------------------------------------------
     * Javascript
     * ---------------------------------------------
     */
    const JS_CORE = 'core';
    const JS_PLUGINS = 'plugins';
    const JS_MODELS = 'models';
    const JS_TEMPLATES = 'templates';
    const JS_CONTROLLERS = 'controllers';
    const JS_LIBRARIES = 'library';
    const JS_VIEWS = 'views';
    const JS_IMAGES = 'images';
    const JS_COLLECTIONS = 'collections';

    static protected $_jsCore = array(
        'jquery' => 'jquery/jquery-1.7.2.min.js',
        'jqueryui' => 'jqueryui/jquery-ui-1.8.20.custom.min.js',
        'underscore' => 'backbone/underscore-min.js',
        'backbone' => 'backbone/backbone-min.js',
        'modernizr' => 'modernizr/modernizr.min.js',
        'swiftbase' => 'swift/class.SWIFT_Base.js',
    );

    static protected $_jsThirdParty = array(
        'yepnope' => array('./__swift/javascript/__global/thirdparty/YepNope/yepnope-min.js', './__swift/javascript/__global/thirdparty/YepNope/yepnope.css-prefix.js', './__swift/javascript/__global/thirdparty/YepNope/yepnope.preload.js'),
        'xregexp' => array('./__swift/apps/base/javascript/__global/thirdparty/XRegExp/xregexp.js', './__swift/apps/base/javascript/__global/thirdparty/XRegExp/xregexp-unicode-base.js'),
        'tinymce' => './__swift/apps/base/javascript/__global/thirdparty/TinyMCE/jquery.tinymce.min.js',
        'nivo' => './__swift/apps/base/javascript/__global/thirdparty/NivoSlider/jquery.nivo.slider.pack.js',
        'unifiedsearch' => './__swift/apps/base/javascript/__global/thirdparty/UnifiedSearch/unifiedsearch.js',
        'circleplayer' => './__swift/apps/base/javascript/__global/thirdparty/Circleplayer/circle.player.js',
        'notification' => './__swift/apps/base/javascript/__global/thirdparty/Notification/notification.js',
        'popup' => './__swift/apps/base/javascript/__global/thirdparty/Popup/popup.js',
        'kql' => './__swift/apps/base/javascript/staff/thirdparty/KQL/kql.js',
        'corecp' => './__swift/apps/base/javascript/__cp/thirdparty/legacy/core.js',
        'coresc' => './__swift/apps/base/javascript/client/thirdparty/legacy/core.js',
        'staffcp' => './__swift/apps/base/javascript/staff/thirdparty/legacy/staff.js',
        'intranetcp' => './__apps/backend/javascript/intranet/thirdparty/legacy/intranet.js',
        'admincp' => './__swift/apps/base/javascript/admin/thirdparty/legacy/admin.js',
        'livesupport' => './__apps/livechat/javascript/visitor/thirdparty/legacy/livesupport.js',
        'd3'        => './__swift/javascript/__global/thirdparty/d3/d3.v3.min.js',
        'bootstrap' => './__swift/javascript/__global/thirdparty/bootstrap/js/bootstrap.min.js',
        'responsive' => './__swift/apps/base/javascript/client/responsive/responsive.js',
        'cookieconsent' => './__swift/apps/base/javascript/__global/thirdparty/Cookieconsent/cookieconsent.min.js',
        'codesample' => './__swift/apps/base/javascript/__global/thirdparty/TinyMCE/plugins/codesample/prism.js',
    );

    static protected $_jsInterfaceAutoLoad = array(
        'admin' => array('tinymce', 'popup', 'kql', 'notification', 'unifiedsearch', 'xregexp', 'corecp', 'admincp', 'line-awesome', 'codesample'),
        'staff' => array('tinymce', 'popup', 'kql', 'notification', 'circleplayer', 'unifiedsearch', 'xregexp', 'corecp', 'staffcp', 'line-awesome', 'codesample'),
        'intranet' => array('tinymce', 'popup', 'kql', 'notification', 'unifiedsearch', 'xregexp', 'corecp', 'intranetcp', 'line-awesome', 'codesample'),
        'client' => array('colorpicker', 'popup', 'coresc', 'responsive', 'line-awesome', 'cookieconsent', 'livesupport'),
        'visitor' => array('coresc', 'livesupport')
    );

    /**
     * ---------------------------------------------
     * CSS
     * ---------------------------------------------
     */
    const CSS_CORE = 'core';
    const CSS_PLUGINS = 'plugins';

    static protected $_cssCore = array(
        'jqueryui'  => './__swift/javascript/__global/core/jqueryui/custom-theme/jquery-ui-1.7.2.custom.css',
    );

    static protected $_cssThirdParty = array(
        'popup' => './__swift/apps/base/javascript/__global/thirdparty/Popup/popup.css',
        'notification' => './__swift/apps/base/javascript/__global/thirdparty/Notification/notification.css',
        'circleplayer' => './__swift/apps/base/javascript/__global/thirdparty/Circleplayer/circle.player.css',
        'unifiedsearch' => './__swift/apps/base/javascript/__global/thirdparty/UnifiedSearch/unifiedsearch.css',
        'source-sans-pro' => './__swift/themes/__global/css/source-sans-pro/source-sans-pro.css',
        'line-awesome' => './__swift/themes/__global/css/line-awesome/line-awesome-font-awesome.min.css',
        'nivo' => './__swift/apps/base/javascript/__global/thirdparty/NivoSlider/nivo-slider.css',
        'bootstrap' => './__swift/javascript/__global/thirdparty/bootstrap/css/bootstrap.min.css',
        'cookieconsent' => './__swift/apps/base/javascript/__global/thirdparty/Cookieconsent/cookieconsent.min.css',
        'codesample' => './__swift/apps/base/javascript/__global/thirdparty/TinyMCE/plugins/codesample/css/prism.css',
    );


    /**
     * List names that would be skipped for the 'processJavascriptContents'
     *
     */
    static protected $_jsProcessorSkipList = array(
        'd3'
    );


    // Options
    const OPTION_PACKJS = 1;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Less:Less');
        $this->Load->Library('JavaScript:JavaScriptPacker');
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
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param mixed $_dataType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_dataType)
    {
        return ($_dataType == self::TYPE_CSS || $_dataType == self::TYPE_JS || $_dataType == self::TYPE_TEMPLATE);
    }

    /**
     * Check to see if its a valid JS
     *
     * @author Varun Shoor
     * @param mixed $_jsType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidJS($_jsType)
    {
        return ($_jsType == self::JS_CORE || $_jsType == self::JS_PLUGINS || $_jsType == self::JS_MODELS || $_jsType == self::JS_TEMPLATES || $_jsType == self::JS_CONTROLLERS || $_jsType == self::JS_LIBRARIES || $_jsType == self::JS_COLLECTIONS);
    }

    /**
     * Check to see if its a valid CSS
     *
     * @author Varun Shoor
     * @param mixed $_cssType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidCSS($_cssType)
    {
        return ($_cssType == self::CSS_CORE || $_cssType == self::CSS_PLUGINS);
    }

    /**
     * Retrieve the JS Core Files
     *
     * @author Varun Shoor
     * @return string Retrieve the Core JS Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSCore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }


        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_core.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsCoreContents = '/* JS - CORE (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        // Global core is loaded manually to preserve hierarchy
        foreach (self::$_jsCore as $_jsFileName => $_jsFilePath) {
            $_finalJSFilePath = './__swift/javascript/__global/core/' . $_jsFilePath;

            if (!file_exists($_finalJSFilePath)) {
                continue;
            }

            $_jsCoreContents .= SWIFT_CRLF . '/* ' . $_finalJSFilePath . ' */' . SWIFT_CRLF;

            $_jsCoreContents .= self::ProcessJavascriptContents(file_get_contents($_finalJSFilePath), $_jsFileName, $_finalJSFilePath) . SWIFT_CRLF;
        }

        $_coreDirectories = array();

        // CP Directory
        if ($this->IsCP()) {
            $_coreDirectories[] = './__swift/javascript/__cp/core';
        }

        // Interface directory in SWIFT
        $_coreDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/core';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_coreDirectories[] = $_appDirectory . '/javascript/__global/core';

            // CP Directory
            if ($this->IsCP()) {
                $_coreDirectories[] = $_appDirectory . '/javascript/__cp/core';
            }

            $_coreDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/core';
        }

        foreach ($_coreDirectories as $_directoryPath) {
            $_jsCoreContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, false, 'js', true, array(self::OPTION_PACKJS));
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsCoreContents);
        }

        return $_jsCoreContents;
    }

    /**
     * Retrieve the JS Plugin Files
     *
     * @author Varun Shoor
     * @return string The JS Plugin Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSPlugins()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_plugins.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsPluginContents = '/* JS - PLUGINS (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_pluginDirectories = array();

        // Global plugins directory
        $_pluginDirectories[] = './__swift/javascript/__global/plugins';

        // CP Directory
        if ($this->IsCP()) {
            $_pluginDirectories[] = './__swift/javascript/__cp/plugins';
        }

        // Interface directory in SWIFT
        $_pluginDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/plugins';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_pluginDirectories[] = $_appDirectory . '/javascript/__global/plugins';

            // CP Directory
            if ($this->IsCP()) {
                $_pluginDirectories[] = $_appDirectory . '/javascript/__cp/plugins';
            }

            $_pluginDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/plugins';
        }

        foreach ($_pluginDirectories as $_directoryPath) {
            $_jsPluginContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, 'plugin.', 'js', true);
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsPluginContents);
        }

        return $_jsPluginContents;
    }

    /**
     * Retrieve the JS Library Files
     *
     * @author Varun Shoor
     * @param array $_filterList (OPTIONAL)
     * @return string The JS Library Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSLibrary($_filterList = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_library.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsLibraryContents = '/* JS - LIBRARY (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_libraryDirectories = array();

        // If filter list includes login or is empty.. then only search the core directories..
        if (!_is_array($_filterList) || (_is_array($_filterList) && in_array(self::FILTER_LOGIN, $_filterList))) {
            // Global lib directory
            $_libraryDirectories[] = './__swift/javascript/__global/library';

            // CP Directory
            if ($this->IsCP()) {
                $_libraryDirectories[] = './__swift/javascript/__cp/library';
            }

            // Interface directory in SWIFT
            $_libraryDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/library';
        }

        // Only load libraries from apps if filter list is empty or if it isnt
        if (!_is_array($_filterList) || (_is_array($_filterList) && !in_array(self::FILTER_LOGIN, $_filterList))) {
            // Parse Apps
            $_installedApps = SWIFT_App::GetInstalledApps();

            foreach ($_installedApps as $_appName) {
                $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
                if (empty($_appDirectory)) {
                    continue;
                }

                $_libraryDirectories[] = $_appDirectory . '/javascript/__global/library';

                // CP Directory
                if ($this->IsCP()) {
                    $_libraryDirectories[] = $_appDirectory . '/javascript/__cp/library';
                }

                $_libraryDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/library';
            }
        }

        foreach ($_libraryDirectories as $_directoryPath) {
            $_jsLibraryContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, 'class.', 'js', true, array(self::OPTION_PACKJS));
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsLibraryContents);
        }

        return $_jsLibraryContents;
    }

    /**
     * Retrieve the JS Model Files
     *
     * @author Varun Shoor
     * @param array $_filterList (OPTIONAL)
     * @return string The JS Model Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSModels($_filterList = [])
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_models.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsModelContents = '/* JS - MODELS (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_modelDirectories = array();

        // Global model directory
        $_modelDirectories[] = './__swift/javascript/__global/models';

        // CP Directory
        if ($this->IsCP()) {
            $_modelDirectories[] = './__swift/javascript/__cp/models';
        }

        // Interface directory in SWIFT
        $_modelDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/models';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_modelDirectories[] = $_appDirectory . '/javascript/__global/models';

            // CP Directory
            if ($this->IsCP()) {
                $_modelDirectories[] = $_appDirectory . '/javascript/__cp/models';
            }

            $_modelDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/models';
        }

        foreach ($_modelDirectories as $_directoryPath) {
            $_jsModelContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, 'model.', 'js', true, array(self::OPTION_PACKJS), $_filterList);
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsModelContents);
        }

        return $_jsModelContents;
    }

    /**
     * Retrieve the JS Collection Files
     *
     * @author Varun Shoor
     * @param array $_filterList (OPTIONAL)
     * @return string The JS Collection Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSCollections($_filterList = [])
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_collections.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsCollectionContents = '/* JS - COLLECTIONS (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_collectionDirectories = array();

        // Global collection directory
        $_collectionDirectories[] = './__swift/javascript/__global/collections';

        // CP Directory
        if ($this->IsCP()) {
            $_collectionDirectories[] = './__swift/javascript/__cp/collections';
        }

        // Interface directory in SWIFT
        $_collectionDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/collections';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_collectionDirectories[] = $_appDirectory . '/javascript/__global/collections';

            // CP Directory
            if ($this->IsCP()) {
                $_collectionDirectories[] = $_appDirectory . '/javascript/__cp/collections';
            }

            $_collectionDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/collections';
        }

        foreach ($_collectionDirectories as $_directoryPath) {
            $_jsCollectionContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, 'collection.', 'js', true, array(self::OPTION_PACKJS), $_filterList);
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsCollectionContents);
        }

        return $_jsCollectionContents;
    }

    /**
     * Retrieve the JS Controller Files
     *
     * @author Varun Shoor
     * @param array $_filterList (OPTIONAL)
     * @return string The JS Controller Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSControllers($_filterList = [])
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_controllers.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsControllerContents = '/* JS - CONTROLLERS (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_controllerDirectories = array();

        // Global controller directory
        $_controllerDirectories[] = './__swift/javascript/__global/controllers';

        // CP Directory
        if ($this->IsCP()) {
            $_controllerDirectories[] = './__swift/javascript/__cp/controllers';
        }

        // Interface directory in SWIFT
        $_controllerDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/controllers';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_controllerDirectories[] = $_appDirectory . '/javascript/__global/controllers';

            // CP Directory
            if ($this->IsCP()) {
                $_controllerDirectories[] = $_appDirectory . '/javascript/__cp/controllers';
            }

            $_controllerDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/controllers';
        }

        foreach ($_controllerDirectories as $_directoryPath) {
            $_jsControllerContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, 'controller.', 'js', true, array(self::OPTION_PACKJS), $_filterList);
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsControllerContents);
        }

        return $_jsControllerContents;
    }

    /**
     * Retrieve the JS View Files
     *
     * @author Varun Shoor
     * @param array $_filterList (OPTIONAL)
     * @return string The JS Voew Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSViews($_filterList = [])
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_views.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsViewsContents = '/* JS - VIEWS (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_viewsDirectories = array();

        // Global views directory
        $_viewsDirectories[] = './__swift/javascript/__global/views';

        // CP Directory
        if ($this->IsCP()) {
            $_viewsDirectories[] = './__swift/javascript/__cp/views';
        }

        // Interface directory in SWIFT
        $_viewsDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/views';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_viewsDirectories[] = $_appDirectory . '/javascript/__global/views';

            // CP Directory
            if ($this->IsCP()) {
                $_viewsDirectories[] = $_appDirectory . '/javascript/__cp/views';
            }

            $_viewsDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/views';
        }

        foreach ($_viewsDirectories as $_directoryPath) {
            $_jsViewsContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, 'view.', 'js', true, array(self::OPTION_PACKJS), $_filterList);
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsViewsContents);
        }

        return $_jsViewsContents;
    }

    /**
     * Retrieve the JS Template Files
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSTemplates()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_templates.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsTemplateContents = '/* JS - TEMPLATES (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_templateDirectories = array();

        // Global template directory
        $_templateDirectories[] = './__swift/javascript/__global/templates';

        // CP Directory
        if ($this->IsCP()) {
            $_templateDirectories[] = './__swift/javascript/__cp/templates';
        }

        // Interface directory in SWIFT
        $_templateDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/templates';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_templateDirectories[] = $_appDirectory . '/javascript/__global/templates';

            // CP Directory
            if ($this->IsCP()) {
                $_templateDirectories[] = $_appDirectory . '/javascript/__cp/templates';
            }

            $_templateDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/templates';
        }

        $_jsTemplateContainer = array();
        foreach ($_templateDirectories as $_directoryPath) {
            $_jsTemplateContainer = array_merge($_jsTemplateContainer, self::GetFileContentsFromDirectory(self::TYPE_TEMPLATE, $_directoryPath, false, 'tpl', true));
        }

        $_jsTemplateContents .= 'SWIFT.LoadCoreLibraries(); SWIFT.Template.AddToCache(' . json_encode($_jsTemplateContainer) . ');' . SWIFT_CRLF . SWIFT_CRLF;

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsTemplateContents);
        }

        return $_jsTemplateContents;
    }

    /**
     * Retrieve the JS SWIFT Files
     *
     * @author Varun Shoor
     * @param array $_filterList (OPTIONAL)
     * @return string Retrieve the SWIFT JS Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJSSWIFT($_filterList = [])
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_swift.js';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_jsSWIFTContents = '/* JS - SWIFT (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_swiftDirectories = array();

        // SWIFT
        $_swiftDirectories[] = './__swift/javascript/__global/swift';

        // CP Directory
        if ($this->IsCP()) {
            $_swiftDirectories[] = './__swift/javascript/__cp/swift';
        }

        // Interface directory in SWIFT
        $_swiftDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/swift';

        foreach ($_swiftDirectories as $_directoryPath) {
            $_jsSWIFTContents .= self::GetFileContentsFromDirectory(self::TYPE_JS, $_directoryPath, false, 'js', true, array(self::OPTION_PACKJS));
        }

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_libAppName = ucfirst($_appName);

            $_configFile = $_appDirectory . '/' . SWIFT_App::DIRECTORY_CONFIG . '/' . SWIFT_App::FILE_CONFIG;

            if (file_exists($_configFile))
            {
                $_SimpleXMLObject = simplexml_load_file($_configFile);
                if (isset($_SimpleXMLObject->name))
                {
                    $_libAppName = Clean($_SimpleXMLObject->name);
                }
            }

            $_jsSWIFTContents .= '/* JS - ' . strtoupper($_appName) . ' CONSTANTS */' . SWIFT_CRLF;
            if ($_libAppName != 'Base' && $_libAppName != 'Core') {
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . ' = {};' . SWIFT_CRLF;
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . '.Models = {};' . SWIFT_CRLF;
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . '.Library = {};' . SWIFT_CRLF;
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . '.Views = {};' . SWIFT_CRLF;
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . '.Collections = {};' . SWIFT_CRLF;
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . '.Controllers = {};' . SWIFT_CRLF;
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . '.Layouts = {};' . SWIFT_CRLF;
                $_jsSWIFTContents .= 'SWIFT.' . $_libAppName . '.Regions = {};' . SWIFT_CRLF;
            }

            $_jsSWIFTContents .= 'SWIFT.Library.' . $_libAppName . ' = {};' . SWIFT_CRLF;
            $_jsSWIFTContents .= 'SWIFT.Models.' . $_libAppName . ' = {};' . SWIFT_CRLF;
            $_jsSWIFTContents .= 'SWIFT.Layouts.' . $_libAppName . ' = {};' . SWIFT_CRLF;
            $_jsSWIFTContents .= 'SWIFT.Regions.' . $_libAppName . ' = {};' . SWIFT_CRLF;
            $_jsSWIFTContents .= 'SWIFT.Collections.' . $_libAppName . ' = {};' . SWIFT_CRLF;

            // Lowercase
            $_jsSWIFTContents .= 'SWIFT.Views.' . strtolower($_libAppName) . ' = {};' . SWIFT_CRLF;
            $_jsSWIFTContents .= 'SWIFT.Controllers.' . strtolower($_libAppName) . ' = {};' . SWIFT_CRLF;
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_jsSWIFTContents);
        }

        return $_jsSWIFTContents;
    }


    /**
     * Retrieve the CSS Core Files
     *
     * @author Varun Shoor
     * @param array $_fileChunks
     * @return string Retrieve the Core CSS Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCSSCore($_fileChunks)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Nidhi Gupta
         *
         * SWIFT-749: clientcss template of default template group is rendered at Support Center, irrespective of template group is being accessed
         */
        $_templateGroup = IIF(in_array($this->Interface->GetName(), array('client', 'visitor', 'rss', 'chat')), $this->Template->GetTemplateGroupName());
        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . IIF(!empty($_templateGroup), '_' . $_templateGroup) . '_core.css';
        if (!SWIFT::IsDebug() && file_exists($_cacheFile)) {
            return file_get_contents($_cacheFile);
        }

        $_cssCoreContents = '/* CSS - CORE (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        // Global core is loaded manually
        foreach (self::$_cssCore as $_cssFileName => $_cssFilePath) {
            if (!file_exists($_cssFilePath)) {
                continue;
            }

            $_cssCoreContents .= SWIFT_CRLF . '/* ' . $_cssFilePath . ' */' . SWIFT_CRLF;

            $_cssCoreContents .= self::ProcessCSSContents(file_get_contents($_cssFilePath)) . SWIFT_CRLF;
        }

        $_coreDirectories = array();

        // Interface directory in SWIFT
        $_coreDirectories[] = './__swift/themes/__global/css';

        // CP Directory
        if ($this->IsCP()) {
            $_coreDirectories[] = './__swift/themes/__cp/css';
        }

        $_coreDirectories[] = './__swift/themes/' . $this->Interface->GetName() . '/css';

        // If this is a client interface, we do a manual load from templates

        if ($this->Interface->GetName() == 'client' || $this->Interface->GetName() == 'visitor' || $this->Interface->GetName() == 'rss' || $this->Interface->GetName() == 'chat') {
            $_cssCoreContents .= SWIFT_CRLF . '/* clientcss template from database */' . SWIFT_CRLF;
            $_cssCoreContents .= self::ProcessCSSContents($this->Template->Get('clientcss', SWIFT_TemplateEngine::TYPE_DB)) . SWIFT_CRLF;

            $_cssCoreContents .= SWIFT_CRLF . '/* css_* Templates */' . SWIFT_CRLF;
            $_cssCoreContents .= self::ProcessCSSContents($this->Template->GetDBTemplatesWithPrefix('css_')) . SWIFT_CRLF;
        }

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_coreDirectories[] = $_appDirectory . '/themes/__global/css';

            // CP Directory
            if ($this->IsCP()) {
                $_coreDirectories[] = $_appDirectory . '/themes/__cp/css';
            }

            $_coreDirectories[] = $_appDirectory . '/themes/' . $this->Interface->GetName() . '/css';
        }

        foreach ($_coreDirectories as $_directoryPath) {
            $_cssCoreContents .= self::GetFileContentsFromDirectory(self::TYPE_CSS, $_directoryPath, false, 'css', true, array(), $_fileChunks);
        }

        // Add customcss
        /*
         * BUG FIX : Saloni Dhall
         *
         * SWIFT-3862 : Customcss does not render on Live Chat related windows
         *
         * Comments : None
         */
        if (SWIFT_INTERFACE == 'client' || SWIFT_INTERFACE == 'visitor') {
            $_cssCoreContents .= SWIFT_CRLF . '/* customcss template from database */' . SWIFT_CRLF;
            $_cssCoreContents .= self::ProcessCSSContents($this->Template->GetDBTemplatesWithName('customcss')) . SWIFT_CRLF;
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_cssCoreContents);
        }

        return $_cssCoreContents;
    }

    /**
     * Retrieve the CSS Plugin Files
     *
     * @author Varun Shoor
     * @return string The CSS Plugin Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCSSPlugins()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_cacheFile = './__swift/cache/compressor.' . $this->Interface->GetName() . '_plugins.css';
        if (file_exists($_cacheFile) && !SWIFT::IsDebug()) {
            return file_get_contents($_cacheFile);
        }

        $_cssPluginContents = '/* CSS - PLUGINS (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;

        $_pluginDirectories = array();

        // Global plugins directory
        $_pluginDirectories[] = './__swift/javascript/__global/plugins';

        // CP Directory
        if ($this->IsCP()) {
            $_pluginDirectories[] = './__swift/javascript/__cp/plugins';
        }

        // Interface directory in SWIFT
        $_pluginDirectories[] = './__swift/javascript/' . $this->Interface->GetName() . '/plugins';

        // Parse Apps
        $_installedApps = SWIFT_App::GetInstalledApps();
        foreach ($_installedApps as $_appName) {
            $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
            if (empty($_appDirectory)) {
                continue;
            }

            $_pluginDirectories[] = $_appDirectory . '/javascript/__global/plugins';

            // CP Directory
            if ($this->IsCP()) {
                $_pluginDirectories[] = $_appDirectory . '/javascript/__cp/plugins';
            }

            $_pluginDirectories[] = $_appDirectory . '/javascript/' . $this->Interface->GetName() . '/plugins';
        }

        foreach ($_pluginDirectories as $_directoryPath) {
            $_cssPluginContents .= self::GetFileContentsFromDirectory(self::TYPE_CSS, $_directoryPath, 'plugin.', 'css', true);
        }

        // Write to cache file
        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFile, $_cssPluginContents);
        }

        return $_cssPluginContents;
    }

    /**
     * Retrieve the files from directory
     *
     * @author Varun Shoor
     * @param mixed $_dataType
     * @param string $_directoryPath
     * @param string $_filePrefix (OPTIONAL) Filter by file prefix
     * @param string $_fileExtension (OPTIONAL) Filter by file extension
     * @param bool $_searchSubDirectories (OPTIONAL) Search the sub directories
     * @param array $_optionList (OPTIONAL)
     * @param array $_customFileChunks (OPTIONAL)
     * @param array $_customIgnoreFileChunks (OPTIONAL)
     * @return string|array The File Contents
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetFileContentsFromDirectory($_dataType, $_directoryPath, $_filePrefix = '', $_fileExtension = '', $_searchSubDirectories = false, $_optionList = array(), $_customFileChunks = array(), $_customIgnoreFileChunks = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_dataType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!file_exists($_directoryPath) || !is_dir($_directoryPath)) {
            if ($_dataType == self::TYPE_TEMPLATE) {
                return array();
            } else {
                return '';
            }
        }

        $_fileContents = '';

        $_filePrefix = mb_strtolower($_filePrefix);

        $_directoryPath = StripTrailingSlash($_directoryPath);

        $_directoryFileList = scandir($_directoryPath);

        if (_is_array($_directoryFileList)) {
            $_directoryList = array();

            foreach($_directoryFileList as $_fileName) {
                $_filePath = $_directoryPath . '/' . $_fileName;
                $_pathInfoContainer = pathinfo($_filePath);


                if ($_fileName != '.' && $_fileName != '..') {
                    // Is a directory
                    if (is_dir($_filePath) && $_searchSubDirectories == true) {
                        $_directoryList[] = $_filePath;

                    // Is a file
                    } else {
                        // Prefix doesnt match?
                        if (!empty($_filePrefix) && mb_strtolower(substr($_fileName, 0, strlen($_filePrefix))) != $_filePrefix) {
                            continue;

                        // Extension doesnt match?
                        } else if (!empty($_fileExtension) && (!isset($_pathInfoContainer['extension']) || ($_pathInfoContainer['extension'] != $_fileExtension))) {
                            continue;

                        }

                        if ($_dataType == self::TYPE_JS) {
                            $_parsedChunk = strtolower($_pathInfoContainer['filename']);
                            if (strstr($_parsedChunk, '.')) {
                                $_parsedChunk = substr($_parsedChunk, strpos($_parsedChunk, '.')+1);
                            }

                            if (_is_array($_customFileChunks) && !in_array($_parsedChunk, $_customFileChunks)) {
                                continue;
                            } else if (_is_array($_customIgnoreFileChunks) && in_array($_parsedChunk, $_customIgnoreFileChunks)) {
                                continue;
                            }

                            $_fileContents .= SWIFT_CRLF . '/* ' . $_filePath . ' */' . SWIFT_CRLF;

                            if (in_array(self::OPTION_PACKJS, $_optionList)) {
                                $_fileContents .= self::ProcessJavascriptContents(file_get_contents($_filePath), $_fileName, $_filePath) . SWIFT_CRLF;
                            } else {
                                $_fileContents .= self::ProcessJavascriptContents(file_get_contents($_filePath), $_fileName) . SWIFT_CRLF;
                            }
                        } else if ($_dataType == self::TYPE_CSS) {
                            if (_is_array($_customFileChunks) && !in_array($_pathInfoContainer['filename'], $_customFileChunks)) {
                                continue;
                            } else if (_is_array($_customIgnoreFileChunks) && in_array($_pathInfoContainer['filename'], $_customIgnoreFileChunks)) {
                                continue;
                            }

                            $_fileContents .= SWIFT_CRLF . '/* ' . $_filePath . ' */' . SWIFT_CRLF;

                            $_fileContents .= self::ProcessCSSContents(file_get_contents($_filePath)) . SWIFT_CRLF;
                        } else if ($_dataType == self::TYPE_TEMPLATE) {
                            $_fileContents[$_pathInfoContainer['filename']] = self::ProcessTemplateContents(file_get_contents($_filePath), $_pathInfoContainer['filename']);
                        }
                    }
                }
            }

            foreach ($_directoryList as $_filePath) {
                $_returnData = self::GetFileContentsFromDirectory($_dataType, $_filePath, $_filePrefix, $_fileExtension, $_searchSubDirectories, $_optionList, $_customFileChunks, $_customIgnoreFileChunks);

                if ($_dataType == self::TYPE_TEMPLATE && is_array($_returnData)) {
                    $_fileContents = array_merge($_fileContents, $_returnData);
                } else if ($_dataType != self::TYPE_TEMPLATE) {
                    $_fileContents .= $_returnData;
                }
            }
        }

        return $_fileContents;
    }

    /**
     * Check whether the currently loaded interface is a control panel
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function IsCP()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);

            return false;
        }

        return SWIFT::IsCP();
    }

    /**
     * Process the Variables
     *
     * @author Varun Shoor
     * @param string $_contents
     * @return string Processed Contents
     */
    public static function ProcessVariables($_contents)
    {
        $_contents = str_replace(array('%themepathglobal%', '%themepath%', '%themepathinterface%', '%swiftpath%', '%basename%'), array(SWIFT::Get('themepathglobal'), SWIFT::Get('themepath'), SWIFT::Get('themepathinterface'), SWIFT::Get('swiftpath'), SWIFT::Get('basename')), $_contents);

        return $_contents;
    }

    /**
     * Parse Language
     *
     * @author Varun Shoor
     * @param string $_languageKey
     * @return string
     */
    public static function ParseLanguage($_languageKey)
    {
        $_SWIFT = SWIFT::GetInstance();

        return $_SWIFT->Language->Get($_languageKey);
    }

    /**
     * Processes the Javascript contents and replaces variables etc.
     *
     * @author Varun Shoor
     * @param string $_contents
     * @param string $_requestName
     * @param string $_filePath
     * @param string|bool $_force Force processing irrespective of 'Skip' list inclusion
     * @return string The processed contents
     */
    protected static function ProcessJavascriptContents($_contents, $_requestName, $_filePath = '', $_force = false)
    {

        //
        // It was found that some thirdParty code includes variables with name such as - _[0]
        // which would be stripped in the processing. To avoid execution breakage, name could
        // be added in the list.
        //
        // Checking whether the processing should be skipped
        if ( (! $_force) &&  ((in_array($_requestName, self::$_jsProcessorSkipList))) ) {
            return $_contents;
        }


        $_SWIFT = SWIFT::GetInstance();

        // Replace variables
        $_contents = self::ProcessVariables($_contents);

        // Do we need to run the JS Packer on this?
        if (!empty($_filePath)) {
            $_pathInfoContainer = pathinfo($_filePath);
            if (isset($_pathInfoContainer['filename']) && !strstr($_pathInfoContainer['filename'], '.min') && !strstr($_pathInfoContainer['filename'], '-min') && !strstr($_pathInfoContainer['filename'], '.pack')
                    && !strstr($_pathInfoContainer['filename'], '-pack')) {
                $_SWIFT_JavaScriptPackerObject = new SWIFT_JavaScriptPacker();
    //                $_contents = $_SWIFT_JavaScriptPackerObject->Pack($_contents) . ';' . SWIFT_CRLF;
            }
        }

        if (false === strpos($_requestName, 'codesample')) {
            // do not replace if it's prism plugin
            $_contents = preg_replace_callback('/_\[([a-zA-Z0-9_]+)\]/U', function ($_matches) {
                return SWIFT_Compressor::ParseLanguage($_matches[1]);
            }, $_contents);
        }

        // Strip New Lines
    //        $_contents = preg_replace("#(\r\n|\r|\n)#s", '', $_contents);

        // Strip Comments
    //        $_contents = preg_replace('/(\s+)\/\*([^\/]*)\*\/(\s+)/s', "\n", $_contents);

        return $_contents;
    }

    /**
     * Processes the Template contents and replaces variables etc.
     *
     * @author Varun Shoor
     * @param string $_contents
     * @param string $_requestName
     * @return string The processed contents
     */
    protected static function ProcessTemplateContents($_contents, $_requestName)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Replace variables
        $_contents = self::ProcessVariables($_contents);

        $_contents = preg_replace_callback('/_\[([a-zA-Z0-9_]+)\]/U', function($_matches) { return SWIFT_Compressor::ParseLanguage($_matches[1]); } , $_contents);

        // Strip New Lines
    //        $_contents = preg_replace("#(\r\n|\r|\n)#s", '', $_contents);

        // Strip Comments
    //        $_contents = preg_replace('/(\s+)\/\*([^\/]*)\*\/(\s+)/s', "\n", $_contents);

        return $_contents;
    }

    /**
     * Processes the CSS contents and replaces variables etc.
     *
     * @author Varun Shoor
     * @param string $_contents
     * @return string The processed contents
     */
    protected static function ProcessCSSContents($_contents)
    {
        // Replace variables
        $_contents = self::ProcessVariables($_contents);

        // Parse LESS syntax
        $_SWIFT_LessObject = new SWIFT_Less();
        $_contents = $_SWIFT_LessObject->Parse($_contents);

        // Strip New Lines
    //        $_contents = preg_replace("#(\r\n|\r|\n)#s", '', $_contents);

        // Strip Comments
    //        $_contents = preg_replace('/(\s+)\/\*([^\/]*)\*\/(\s+)/s', "\n", $_contents);

        return $_contents;
    }

    /**
     * Dispatch the Data
     *
     * @author Varun Shoor
     * @param mixed $_dispatchType The Dispatch Type
     * @param string $_fileList (OPTIONAL) The File List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Dispatch($_dispatchType, $_fileList = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_fileList = trim(preg_replace("/[^a-zA-Z0-9\-\_:,]/", "" , $_fileList));
        $_requestHash = md5($_dispatchType . $this->Interface->GetName() . $this->Language->GetLanguageCode() . $_fileList);

        $_finalFileChunks = array();
        if (strpos($_fileList, ':'))
        {
            $_finalFileChunks = explode(':', $_fileList);
        } else if (strpos($_fileList, ';')) {
            $_finalFileChunks = explode(';', $_fileList);
        } else if (trim($_fileList) == '') {
            $_finalFileChunks = array();
        } else {
            $_finalFileChunks = array($_fileList);
        }

        if (!is_array($_finalFileChunks))
        {
            return false;
        }

        // Build up the filter list
        $_filterList = array();
        $_enableCoreFiltering = $_enableFiltering = false;
        foreach ($_finalFileChunks as $_chunkName) {
            if ($_chunkName == self::FILTER_LOGIN || $_chunkName == self::FILTER_CORE || $_chunkName == self::FILTER_THIRDPARTY) {
                $_filterList[] = $_chunkName;

                $_enableCoreFiltering = $_enableFiltering = true;
            }
        }


        $_finalContents = '';

        if (in_array(self::JS_CORE, $_finalFileChunks) || in_array(self::JS_PLUGINS, $_finalFileChunks) || in_array(self::JS_MODELS, $_finalFileChunks) || in_array(self::JS_TEMPLATES, $_finalFileChunks) || in_array(self::JS_VIEWS, $_finalFileChunks)
                || in_array(self::JS_CONTROLLERS, $_finalFileChunks) || in_array(self::JS_LIBRARIES, $_finalFileChunks)  || in_array(self::JS_IMAGES, $_finalFileChunks) || in_array(self::FILTER_LOGIN, $_finalFileChunks) || in_array(self::JS_COLLECTIONS, $_finalFileChunks)) {
            $_enableCoreFiltering = true;
        }

        HeaderCache();

        if ($_dispatchType == self::TYPE_CSS)
        {
            header('Content-Type: text/css');
        } else if ($_dispatchType == self::TYPE_JS) {
            header('Content-Type: text/javascript');
        }

        // Retrieve the template group
        $_templateGroup = $this->Template->GetTemplateGroupName();

        // Check if it supports gzip
        $_encodingList = array();
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $_encodingList = explode(',', strtolower(preg_replace('/\s+/', '', $_SERVER['HTTP_ACCEPT_ENCODING'])));
        }

        if (SWIFT::IsDebug()) {
            $_encodingList = array();
        }

        $_encodingType = false;
        $_gzipSupport = false;

        if ((in_array('gzip', $_encodingList) || in_array('x-gzip', $_encodingList) || isset($_SERVER['---------------'])) && function_exists('ob_gzhandler') && !ini_get('zlib.output_compression')) {
            $_encodingType = in_array('x-gzip', $_encodingList) ? 'x-gzip' : 'gzip';
            $_gzipSupport = true;
        }

        $_cacheFileName = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/compressor.cache' . IIF(!empty($_templateGroup), '_' . $_templateGroup) . '.' . $_requestHash . IIF(!empty($_encodingType), '.' . $_encodingType) . '.js';

        if ($_gzipSupport)
        {
            header('Content-Encoding: ' . $_encodingType);
        }

        if (file_exists($_cacheFileName) && !SWIFT::IsDebug())
        {
            echo file_get_contents($_cacheFileName);

            return true;
        }

        // Following stuff is rendered if the cache isnt hit
        if ($_dispatchType == self::TYPE_CSS)
        {
            // Login Override
            if (in_array(self::FILTER_LOGIN, $_finalFileChunks)) {
                $_finalFileChunks[] = self::FILTER_CORE;
            }

            $_finalContents .= $this->GetCSSCore($_finalFileChunks);
            if (!in_array(self::FILTER_LOGIN, $_finalFileChunks)) {
                $_finalContents .= $this->GetCSSPlugins();
            }

            // AUTOLOAD: Process Linked JS Logic
            $_finalContents .= '/* CSS - LINKEDJS (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;
            $_interfaceName = $this->Interface->GetName();
            if (isset(self::$_jsInterfaceAutoLoad[$_interfaceName])) {
                foreach (self::$_jsInterfaceAutoLoad[$_interfaceName] as $_thirdPartyLibName) {
                    if (!isset(self::$_cssThirdParty[$_thirdPartyLibName])) {
                        continue;
                    } else if (_is_array($_finalFileChunks) && !in_array($_thirdPartyLibName, $_finalFileChunks) && !in_array(self::FILTER_THIRDPARTY, $_finalFileChunks)) {
                        continue;
                    }

                    if (_is_array(self::$_cssThirdParty[$_thirdPartyLibName])) {
                        foreach (self::$_cssThirdParty[$_thirdPartyLibName] as $_filePath) {
                            if (!file_exists($_filePath)) {
                                continue;
                            }

                            $_finalContents .= SWIFT_CRLF . '/* ' . $_filePath . ' */' . SWIFT_CRLF;
                            $_finalContents .= self::ProcessCSSContents(file_get_contents($_filePath));
                        }
                    } else if (file_exists(self::$_cssThirdParty[$_thirdPartyLibName])) {
                        $_finalContents .= SWIFT_CRLF . '/* ' . self::$_cssThirdParty[$_thirdPartyLibName] . ' */' . SWIFT_CRLF;
                        $_finalContents .= self::ProcessCSSContents(file_get_contents(self::$_cssThirdParty[$_thirdPartyLibName]));
                    }
                }
            }

            /**
             * ---------------------------------------------
             * Custom Loading
             * ---------------------------------------------
             */
            foreach ($_finalFileChunks as $_chunkName) {
                //if (isset(self::$_cssThirdParty[$_chunkName]) && file_exists(self::$_cssThirdParty[$_chunkName])) {

                if (isset(self::$_cssThirdParty[$_chunkName])) {

                    // Retrieve chunk file information
                    $_chunkFiles = (array) self::$_cssThirdParty[$_chunkName];

                    foreach($_chunkFiles as $_chunkFile) {
                        $_finalContents .= SWIFT_CRLF . '/* ' . $_chunkFile . ' */' . SWIFT_CRLF;
                        $_finalContents .= self::ProcessCSSContents(file_get_contents($_chunkFile));
                    }
                }
            }


        } else if ($_dispatchType == self::TYPE_JS) {
            // Always load dispatch the base JS packages for this interface if the filtering isnt requested
            if (!$_enableCoreFiltering) {
                $_finalContents .= $this->GetJSCore() . $this->GetJSSWIFT() . $this->GetJSPlugins() . $this->GetJSLibrary() . $this->GetJSModels() . $this->GetJSControllers() . $this->GetJSViews() . $this->GetJSTemplates() . $this->GetJSCollections();
            } else if ($_enableFiltering && in_array(self::FILTER_LOGIN, $_filterList)) {
                $_finalContents .= $this->GetJSCore() . $this->GetJSSWIFT($_filterList) . $this->GetJSLibrary($_filterList) . $this->GetJSControllers($_filterList) . $this->GetJSViews($_filterList);
            } else {
                foreach ($_finalFileChunks as $_chunkName) {
                    if ($_chunkName == 'core') {
                        $_finalContents .= $this->GetJSCore();
                    } else if ($_chunkName == 'plugins') {
                        $_finalContents .= $this->GetJSPlugins();
                    } else if ($_chunkName == 'models') {
                        $_finalContents .= $this->GetJSModels();
                    } else if ($_chunkName == 'collections') {
                        $_finalContents .= $this->GetJSCollections();
                    } else if ($_chunkName == 'templates') {
                        $_finalContents .= $this->GetJSTemplates();
                    } else if ($_chunkName == 'controllers') {
                        $_finalContents .= $this->GetJSControllers();
                    } else if ($_chunkName == 'views') {
                        $_finalContents .= $this->GetJSViews();
                    } else if ($_chunkName == 'library') {
                        $_finalContents .= $this->GetJSLibrary(false); // Dont include the core
                    } else if ($_chunkName == 'swift') {
                        $_finalContents .= $this->GetJSSWIFT();
                    }
                }
            }


            /**
            * ---------------------------------------------
            * Custom Loading
            * ---------------------------------------------
            */
            foreach ($_finalFileChunks as $_chunkName) {
                //if (isset(self::$_jsThirdParty[$_chunkName]) && file_exists(self::$_jsThirdParty[$_chunkName])) {
                if (isset(self::$_jsThirdParty[$_chunkName])) {

                    // Retrieve chunk file information
                    $_chunkFiles = (array) self::$_jsThirdParty[$_chunkName];

                    foreach($_chunkFiles as $_chunkFile) {
                        $_finalContents .= SWIFT_CRLF . '/* ' . $_chunkFile . ' */' . SWIFT_CRLF;
                        $_finalContents .= self::ProcessJavascriptContents(file_get_contents($_chunkFile), $_chunkName);
                    }
                }
            }


            // AUTOLOAD: Process Interface Logic
            $_finalContents .= '/* JS - THIRDPARTY (' . date('d M Y h:i:s A', DATENOW) . ') */' . SWIFT_CRLF . SWIFT_CRLF;
            $_interfaceName = $this->Interface->GetName();
            if (isset(self::$_jsInterfaceAutoLoad[$_interfaceName])) {
                foreach (self::$_jsInterfaceAutoLoad[$_interfaceName] as $_thirdPartyLibName) {
                    if (!isset(self::$_jsThirdParty[$_thirdPartyLibName])) {
                        continue;
                    } else if (_is_array($_finalFileChunks) && !in_array($_thirdPartyLibName, $_finalFileChunks) && !in_array(self::FILTER_THIRDPARTY, $_finalFileChunks)) {
                        continue;
                    }

                    if (_is_array(self::$_jsThirdParty[$_thirdPartyLibName])) {
                        foreach (self::$_jsThirdParty[$_thirdPartyLibName] as $_filePath) {
                            if (!file_exists($_filePath)) {
                                continue;
                            }

                            $_finalContents .= SWIFT_CRLF . '/* ' . $_filePath . ' */' . SWIFT_CRLF;
                            $_finalContents .= self::ProcessJavascriptContents(file_get_contents($_filePath), $_thirdPartyLibName);
                        }
                    } else if (file_exists(self::$_jsThirdParty[$_thirdPartyLibName])) {
                        $_finalContents .= SWIFT_CRLF . '/* ' . self::$_jsThirdParty[$_thirdPartyLibName] . ' */' . SWIFT_CRLF;
                        $_finalContents .= self::ProcessJavascriptContents(file_get_contents(self::$_jsThirdParty[$_thirdPartyLibName]), $_thirdPartyLibName);
                    }
                }
            }
        }

        $_cacheData = $_finalContents;
        if ($_gzipSupport)
        {
            $_cacheData = gzencode($_finalContents, 9, FORCE_GZIP);
        }

        echo $_cacheData;

        if (!SWIFT::IsDebug())
        {
            file_put_contents($_cacheFileName, $_cacheData);
        }

        return true;
    }

    /**
     * Parse the Chunk Contents
     *
     * @author Varun Shoor
     * @param string $_chunkContents
     * @param string $_chunkName
     * @return string Chunk Contents
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseChunkContents($_chunkContents, $_chunkName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_chunkName == 'jqueryui')
        {
            $_chunkContents = str_replace('url(', 'url(' . SWIFT::Get('swiftpath') . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THIRDPARTY_DIRECTORY . '/jQueryUI/css/' . SWIFT::Get('jquerytheme') . '-theme/', $_chunkContents);
        } else if ($_chunkName == 'colorpicker') {
            $_chunkContents = str_replace('url(', 'url(' . SWIFT::Get('swiftpath') . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THIRDPARTY_DIRECTORY . '/jQuery/colorpicker/css/', $_chunkContents);
        }

        $_replaceWhat = array('%themepathglobal%', '%swiftpath%');
        $_replaceWith = array(SWIFT::Get('themepathglobal'), SWIFT::Get('swiftpath'));

        foreach (SWIFT::GetThemePath() as $_themePathName => $_themePathContainer) {
            $_replaceWhat[] = '%' . $_themePathName . '%';
            $_replaceWith[] = $_themePathContainer[1];
        }

        $_chunkContents = str_replace($_replaceWhat, $_replaceWith, $_chunkContents);

        return $_chunkContents;
    }
}
?>
