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

use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Models\User\SWIFT_User;

/**
 * The Main SWIFT Language Management Engine
 *
 * @property SWIFT_LanguagePhraseLinked $LanguagePhraseLinked
 * @author Varun Shoor
 */
class SWIFT_LanguageEngine extends SWIFT_Library
{
    private $_languageCode = 'en-us';
    private $_engineType = false;

    private $_languageID = false;
    private $_languageCharset = 'UTF-8';

    private $_sectionQueue = array();

    protected $_isCustomLanguage = false;

    public $_phraseCache = array();

    public $LanguagePhraseLinked;

    // Core Constants
    const TYPE_DB = 1;
    const TYPE_FILE = 2;

    const DEFAULT_LOCALE = 'en-us';
    const DEFAULT_LOCALEDB = 'default';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param mixed $_engineType The Language Engine Type
     * @param string $_languageCode The Language Code
     * @param int $_languageID The Language ID
     * @param bool $_isCustomLanguage Whether this is a personalized language request for usere
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct($_engineType, $_languageCode = 'en-us', $_languageID = 0, $_isCustomLanguage)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidEngineType($_engineType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_isCustomLanguage = $_isCustomLanguage;

        $this->SetEngineType($_engineType);

        $this->SetLanguageCode($_languageCode);
        $this->SetLanguageID($_languageID);

        parent::__construct();

        $this->LoadLanguageTable();

        SWIFT::SetReference('language', $this->_phraseCache);
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
     * Add a section name to the queue
     *
     * @author Varun Shoor
     * @param string $_sectionName The Section Name
     * @param mixed $_engineType (OPTIONAL) Custom Engine Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function Queue($_sectionName, $_engineType = false)
    {
        if (empty($_sectionName))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_sectionName = Clean($_sectionName);

        if ($_engineType == self::TYPE_DB || $this->GetEngineType() == self::TYPE_DB)
        {
            $this->_sectionQueue[] = $this->GetLanguageCode() . ':' . $_sectionName;
        } else {
            $this->_sectionQueue[] = $_sectionName;
        }

        return true;
    }

    /**
     * Clear the Section Queue
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ClearQueue()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_sectionQueue = array();

        return true;
    }

    /**
     * Loads the Section Queue Items into the Language Table
     *
     * @author Varun Shoor
     * @param mixed $_engineType (OPTIONAL) Custom Engine Type
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadQueue($_engineType = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sectionQueue = $this->_sectionQueue;

        if (!_is_array($_sectionQueue))
        {
            return false;
        }

        $this->ClearQueue();

        if ($_engineType == self::TYPE_DB || $this->GetEngineType() == self::TYPE_DB)
        {
            foreach ($_sectionQueue as $_key => $_val)
            {
                $_SWIFT->Cache->Queue($_val);
            }

            $_SWIFT->Cache->LoadQueue();

            foreach ($_sectionQueue as $_key => $_val)
            {
                $_languageCodeCurrent = $_val;

                if (_is_array($_SWIFT->Cache->Get($_languageCodeCurrent)))
                {
                    // MERGE PHRASES AND REPLACE OLD PHRASES WITH NEW 
                    $this->_phraseCache = array_merge($this->_phraseCache, $_SWIFT->Cache->Get($_languageCodeCurrent));
                }
            }

        } else {
            foreach ($_sectionQueue as $_key => $_val)
            {
                $this->Load($_val);
            }
        }

        return true;
    }

    /**
     * Get the Linked Phrase Value
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_typeID The Type ID Associated with Link
     * @return mixed "Phrase String" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or if Invalid Data is Provided
     */
    public function GetLinked($_linkType, $_typeID) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->LanguagePhraseLinked) || $this->GetLanguageID() == 0) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_linkedPhraseContainer = $this->LanguagePhraseLinked->Get($_linkType, $_typeID);
        if (isset($_linkedPhraseContainer[$this->GetLanguageID()])) {
            return $_linkedPhraseContainer[$this->GetLanguageID()];
        }

        return '';
    }

    /**
     * Loads the Language Table
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the Language Table could not be loaded
     */
    public function LoadLanguageTable()
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($this->GetEngineType() == self::TYPE_DB)
        {
            // Add the default language section to data store cache loading statement, other sections can be loaded by apps in constructors
            $this->Queue(self::DEFAULT_LOCALEDB);
            $this->LoadQueue();

            $this->LanguagePhraseLinked = new SWIFT_LanguagePhraseLinked();

            $_languageCache = $_SWIFT->Cache->Get('languagecache');
            if (isset($_languageCache[$this->_languageID])) {
                $this->_languageCharset = $_languageCache[$this->_languageID]['charset'];
            }

            return true;
        } else {
            $_languageFilename = $this->GetLanguageFilenames(array($this->GetLanguageCode(), self::DEFAULT_LOCALE));
            if (!$_languageFilename)
            {
                throw new SWIFT_Exception('Unable to load default language table (ADMIN)');

                return false;
            }

            $__LANG = require_once ($_languageFilename);

            if(isset($__LANG) && is_array($__LANG))
            {
                $this->_phraseCache = $__LANG;
                unset($__LANG);

                return true;
            }
        }

        return false;
    }

    /**
     * Get the appropriate language file name
     *
     * @author Varun Shoor
     * @param array $_sectionNameList The Section Name List
     * @return mixed "_fileName" (STRING) on Success, "false" otherwise
     */
    private function GetLanguageFilenames($_sectionNameList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_sectionNameList))
        {
            return false;
        }

        $_fileNameContainer = array();

        $_AppObject = false;

        if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetIsClassLoaded() && $_SWIFT->Router->GetApp() instanceof SWIFT_App)
        {
            $_AppObject = $_SWIFT->Router->GetApp();
        }

        // Current Locale > Default Locale > App File?
        foreach ($_sectionNameList as $_key => $_val)
        {
            $_val = Clean($_val);

            if ($_AppObject && $_AppObject instanceof SWIFT_App && $_AppObject->GetIsClassLoaded())
            {
                $_fileNameContainer[] = './' . SWIFT_APPSDIRECTORY . '/' . $_AppObject->GetName() . '/' . SWIFT_LOCALEDIRECTORY . '/' . $this->GetLanguageCode() . '/' . $_val . '.php';
            }

            $_fileNameContainer[] = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LOCALEDIRECTORY . '/' . $this->GetLanguageCode() . '/' . $_val . '.php';

            if ($_AppObject && $_AppObject instanceof SWIFT_App && $_AppObject->GetIsClassLoaded())
            {
                $_fileNameContainer[] = './' . SWIFT_APPSDIRECTORY . '/' . $_AppObject->GetName() . '/' . SWIFT_LOCALEDIRECTORY . '/' . self::DEFAULT_LOCALE . '/' . $_val . '.php';
            }

            $_fileNameContainer[] = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LOCALEDIRECTORY . '/' . self::DEFAULT_LOCALE . '/' . $_val . '.php';
        }

        foreach ($_fileNameContainer as $_key => $_val)
        {
            if (file_exists($_val))
            {
                return $_val;
            }
        }

        print_r($_fileNameContainer);

        return false;
    }

    /**
     * Checks to see if its a valid engine type
     *
     * @author Varun Shoor
     * @param int $_engineType The Engine Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidEngineType($_engineType)
    {
        if ($_engineType == self::TYPE_DB || $_engineType == self::TYPE_FILE)
        {
            return true;
        }

        return false;
    }

    /**
     * Sets the Language Engine Type
     *
     * @author Varun Shoor
     * @param int $_engineType The Engine Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Received
     */
    private function SetEngineType($_engineType)
    {
        if (!self::IsValidEngineType($_engineType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_engineType = $_engineType;

        return true;
    }

    /**
     * Retrieve the Language Engine Type
     *
     * @author Varun Shoor
     * @return mixed "_engineType" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetEngineType()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_engineType;
    }

    /**
     * Set the language id
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetLanguageID($_languageID)
    {
        $_languageID = $_languageID;

        $this->_languageID = $_languageID;

        return true;
    }

    /**
     * Retrieve the Language ID
     *
     * @author Varun Shoor
     * @return mixed "_languageID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLanguageID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_languageID;
    }

    /**
     * Set the language code
     *
     * @author Varun Shoor
     * @param string $_languageCode The Language Code
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetLanguageCode($_languageCode)
    {
        if (empty($_languageCode))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_languageCode = Clean($_languageCode);

        $this->_languageCode = $_languageCode;

        return true;
    }

    /**
     * Retrieve the Language Code
     *
     * @author Varun Shoor
     * @return mixed "_languageCode" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLanguageCode()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_languageCode;
    }

    /**
     * Get a translated variable based on key
     *
     * @author Varun Shoor
     * @return mixed "Phrase Value" (STRING) on Success, "void" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_phraseCode)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return;
        }

        if (strtolower($_phraseCode) == 'charset') {
            return $this->_languageCharset;
        }

        if (!isset($this->_phraseCache[$_phraseCode]))
        {
            return;
        }

        return $this->_phraseCache[$_phraseCode];
    }

    /**
     * Attempts to load the given section for each non core app
     *
     * @author Varun Shoor
     * @param string $_sectionName The Language Section Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadNonCoreApps($_sectionName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_coreAppList = SWIFT::Get('CoreApps');
        $_nonCoreAppList = array();

        // Itterate through the registered apps and find non-core apps
        foreach (SWIFT_App::$_installedApps as $_key => $_val)
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

        foreach ($_nonCoreAppList as $_key => $_val)
        {
            $this->LoadApp($_sectionName, $_val);
        }

        return true;
    }

    /**
     * Load a given language section
     *
     * @author Varun Shoor
     * @param string $_sectionName The Language Section Name
     * @param string $_appName The App Name
     * @param mixed $_engineType (OPTIONAL) Custom Engine Type
     * @return array|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the Language Table cannot be loaded or If the Class is not Loaded
     */
    public function LoadApp($_sectionName, $_appName, $_engineType = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_sectionName = Clean($_sectionName);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-974 Language loading for apps is broken
         *
         * Comments: None
         */

        if ($_engineType == false) {
            $_engineType = $this->GetEngineType();
        }

        if ($_engineType == self::TYPE_DB)
        {
            $this->Queue($_sectionName, $_engineType);

            $this->LoadQueue($_engineType);
        } else {
            $_languageFilenameList = array();
            $_languageFilenameList[] = SWIFT_BASEPATH . '/' . SWIFT_APPSDIRECTORY . '/' . Clean($_appName) . '/' . SWIFT_LOCALEDIRECTORY . '/' . $this->GetLanguageCode() . '/' . Clean($_appName) . '.php';
            $_languageFilenameList[] = SWIFT_BASEPATH . '/' . SWIFT_APPSDIRECTORY . '/' . Clean($_appName) . '/' . SWIFT_LOCALEDIRECTORY . '/' . $this->GetLanguageCode() . '/' . $_sectionName . '.php';
            $_languageFilenameList[] = SWIFT_BASEPATH . '/' . SWIFT_APPSDIRECTORY . '/' . Clean($_appName) . '/' . SWIFT_LOCALEDIRECTORY . '/' . self::DEFAULT_LOCALE . '/' . $_sectionName . '.php';

            $_languageFilename = false;

            foreach ($_languageFilenameList as $_key => $_val)
            {
                if (file_exists($_val))
                {
                    $_languageFilename = $_val;
                }
            }

            if (!$_languageFilename)
            {
                return false;
            }

            $__LANG = null;
            $lang = require $_languageFilename;

            if (empty($__LANG)) {
                if (is_array($lang)) {
                    $__LANG = $lang;
                } else {
                    throw new SWIFT_Exception('$__LANG Array Could not be Located in: ' . $_sectionName);
                }
            }

            if (is_array($__LANG))
            {
                $this->_phraseCache = $this->_phraseCache + $__LANG;

                return $__LANG;
            }
        }

        return false;
    }

    /**
     * Load a given language section
     *
     * @author Varun Shoor
     * @param string $_sectionName The Language Section Name
     * @param mixed $_engineType (OPTIONAL) Custom Engine Type
     * @return array|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception When the Language Table cannot be loaded or If the Class is not Loaded
     */
    public function Load($_sectionName, $_engineType = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_sectionName = Clean($_sectionName);

        if ($_engineType == self::TYPE_DB || ($this->GetEngineType() == self::TYPE_DB && $_engineType == false))
        {
            $this->Queue($_sectionName, $_engineType);

            $this->LoadQueue($_engineType);
        } else {
            $_languageFilename = $this->GetLanguageFilenames(array($_sectionName));

            if (!$_languageFilename)
            {
                throw new SWIFT_Exception('Unable to load default language table: '. $_sectionName .' (ADMIN)');

                return false;
            }

            $__LANG = require ($_languageFilename);

            if (!isset($__LANG))
            {
                throw new SWIFT_Exception('$__LANG Array Could not be Located in: ' . $_sectionName);

                return false;
            }

            if(is_array($__LANG))
            {
                $this->_phraseCache = array_merge($this->_phraseCache, $__LANG);

                return $__LANG;
            }
        }

        return false;
    }

    /**
     * Load the Language Engine
     *
     * @author Varun Shoor
     * @return SWIFT_LanguageEngine|bool
     */
    public static function LoadEngine()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Process the Engine Type First.. the database engine is only for client & visitor interfaces..
        $_engineType = self::TYPE_FILE;
        $_languageCode = LANGUAGE_ADMIN;
        $_languageID = $_masterLanguageID = $_isCustomLanguage = false;
        /*
         * BUG FIX - Mansi Wason
         *
         * SWIFT-1315 Email templates don't pick up type, status and priority field translations
         *
         * Comments: Added the Console interface for ticket creation via mail parser.
         */
        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_VISITOR
            || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_RSS
            || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CRON || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_TESTS
        ) {
            $_engineType = self::TYPE_DB;
            $_SWIFT->Cookie->Parse('client');

            /**
             * Language Code Processing
             */
            $_cookieLanguageID = $_SWIFT->Cookie->GetVariable('client', 'languageid');

            $_languageCache = $_SWIFT->Cache->Get('languagecache');
            $_languageCode = false;

            // Did someone pass it in the _REQUEST?
            if (!empty($_REQUEST['languageid']) && isset($_languageCache[$_REQUEST['languageid']]) && !empty($_languageCache[$_REQUEST['languageid']]['languageid'])) {
                $_SWIFT->Cookie->AddVariable('client', 'languageid', $_REQUEST['languageid']);
                $_languageCode = $_languageCache[$_REQUEST['languageid']]['languagecode'];

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-4365 Automatic emails are sent in the language stored in cookies of the browser.
             *
             * Comments: In case of cron interface, system should not get the language data from client cookies.
             */
            // Is language id set in cookie?
            } else if (!empty($_cookieLanguageID) && isset($_languageCache[$_cookieLanguageID]) && !empty($_languageCache[$_cookieLanguageID]['languageid']) && $_SWIFT->Interface->GetInterface() != SWIFT_Interface::INTERFACE_CRON) {
                $_languageCode = $_languageCache[$_cookieLanguageID]['languagecode'];
                $_languageID = $_cookieLanguageID;
                $_isCustomLanguage = true;

            } else if (isset($_SWIFT->User) && $_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded() && isset($_languageCache[$_SWIFT->User->GetProperty('languageid')])) {
                $_languageCode = $_languageCache[$_SWIFT->User->GetProperty('languageid')]['languageid'];

            }

            /**
             * ---------------------------------------------
             * PROCESS LANGUAGE CODE
             * ---------------------------------------------
             */
            $_masterLanguageCode = '';
            if ($_languageCode && !$_languageID) {
                foreach ($_languageCache as $_languageID => $_languageContainer) {
                    if (mb_strtolower($_languageContainer['languagecode']) == mb_strtolower($_languageCode)) {
                        $_isCustomLanguage = true;
                    } else if ($_languageContainer['ismaster'] == '1') {
                        $_masterLanguageID = $_languageID;
                        $_masterLanguageCode = $_languageContainer['languagecode'];
                    }
                }
            }

            if (!$_languageID && isset($_SWIFT->Template) && $_SWIFT->Template instanceof SWIFT_TemplateEngine) {
                $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
                $_templateGroupID = $_SWIFT->Template->GetTemplateGroupID();

                if (isset($_templateGroupCache[$_templateGroupID]['languageid'])) {
                    $_languageID = $_templateGroupCache[$_templateGroupID]['languageid'];
                    $_languageCode = $_languageCache[$_templateGroupCache[$_templateGroupID]['languageid']]['languagecode'];
                }
            }
            /**
             * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
             *
             * SWIFT-4735 - The console interface is not setting language while running setup.
             */
            if (!SWIFT_App::IsInstalled(APP_BASE) && empty($_languageCode) && $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CONSOLE) {
                $_engineType   = self::TYPE_FILE;
                $_languageCode = LANGUAGE_ADMIN;
            }

            if (empty($_languageCode)) {
                $_languageCode = $_masterLanguageCode;
                $_languageID = $_masterLanguageID;
            }
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-4303 : Languages support within product
         *
         * Comments: Setup must not fetch language from cookie.
         */
        else if ( $_SWIFT->Cookie->Get('languagecode') != '' && SWIFT_INTERFACE != 'setup')
        {
            $_isCustomLanguage = true;
            $_languageCode = $_SWIFT->Cookie->Get('languagecode');
        }

        $_SWIFT_LanguageEngineObject = new SWIFT_LanguageEngine($_engineType, $_languageCode, $_languageID, $_isCustomLanguage);
        if (!$_SWIFT_LanguageEngineObject instanceof SWIFT_LanguageEngine || !$_SWIFT_LanguageEngineObject->GetIsClassLoaded())
        {
            return false;
        }

        $_SWIFT->Template->Assign('_activeLanguageID', $_languageID);

        return $_SWIFT_LanguageEngineObject;
    }

    /**
     * Update the template group linked language
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateTemplateGroup($_templateGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_languageCache = $_SWIFT->Cache->Get('languagecache');
        $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
        $_languageID = false;
        $_languageCode = '';

        if (isset($_templateGroupCache[$_templateGroupID]['languageid'])) {
            $_languageID = $_templateGroupCache[$_templateGroupID]['languageid'];

            if (!isset($_languageCache[$_templateGroupCache[$_templateGroupID]['languageid']]))
            {
                throw new SWIFT_Exception('Invalid Linked Language (to template group)');
            }

            $_languageCode = $_languageCache[$_templateGroupCache[$_templateGroupID]['languageid']]['languagecode'];
        } else {
            throw new SWIFT_Exception('Invalid Template Group');
        }

        if ($this->_isCustomLanguage == false) {
            $this->SetLanguageCode($_languageCode);
            $this->SetLanguageID($_languageID);
        }

        return true;
    }
}
?>
