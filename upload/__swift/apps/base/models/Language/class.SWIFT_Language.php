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

namespace Base\Models\Language;

use SWIFT;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_Language_Exception;
use Base\Models\Language\SWIFT_LanguagePhrase;
use SWIFT_Model;

/**
 * The Language Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Language extends SWIFT_Model
{
    const TABLE_NAME = 'languages';

    const LANGUAGE_ID_COL_NAME = 'languageid';
    const IS_DEFAULT_COL_NAME = 'isdefault';
    const IS_ENABLED_COL_NAME = 'isenabled';

    const PRIMARY_KEY = 'languageid';

    const TABLE_STRUCTURE = "languageid I PRIMARY AUTO NOTNULL,
                                title C(100) DEFAULT '' NOTNULL,
                                languagecode C(20) DEFAULT '' NOTNULL,
                                charset C(200) DEFAULT '' NOTNULL,
                                author C(120) DEFAULT '' NOTNULL,
                                textdirection C(10) DEFAULT 'ltr' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL,
                                isdefault I2 DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                isenabled I2 DEFAULT '1' NOTNULL,
                                flagicon C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'languagecode';

    const GET_DEFAULT_LANGUAGE_ID_QUERY = 'SELECT ' . self::LANGUAGE_ID_COL_NAME
    . ' FROM ' . TABLE_PREFIX . self::TABLE_NAME
    . ' WHERE ' . self::IS_DEFAULT_COL_NAME . ' = 1 AND ' . self::IS_ENABLED_COL_NAME . ' = 1';


    protected $_dataStore = array();

    // Core Constants
    const DIRECTION_LTR = 'ltr';
    const DIRECTION_RTL = 'rtl';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class could not be loaded
     */
    public function __construct($_languageID)
    {
        parent::__construct();

        if (!$this->LoadData($_languageID)) {
            throw new SWIFT_Language_Exception('Failed to Load from Language ID: ' . $_languageID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @throws SWIFT_Language_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'languages', $this->GetUpdatePool(), 'UPDATE', "languageid = '" . (int)($this->GetLanguageID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Language ID
     *
     * @author Varun Shoor
     * @return mixed "languageid" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not Loaded
     */
    public function GetLanguageID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['languageid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_languageID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "languages WHERE languageid = '" . $_languageID . "'");
        if (isset($_dataStore['languageid']) && !empty($_dataStore['languageid'])) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve all phrases under this language
     *
     * @author Varun Shoor
     * @param bool $_isUpgrade (OPTIONAL) Whether this is being executed from upgrade script, in which case we send the contentsdefault
     * @return mixed "_phraseContanier" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not loaded
     */
    public function GetPhrases($_isUpgrade = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_phraseContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . (int)($this->GetLanguageID()) . "'");
        while ($this->Database->NextRecord()) {
            if ($_isUpgrade) {
                $_phraseContainer[$this->Database->Record['sectioncode']] = $this->Database->Record['contentsdefault'];
            } else {
                $_phraseContainer[$this->Database->Record['sectioncode']] = $this->Database->Record['contents'];
            }
        }

        return $_phraseContainer;
    }

    /**
     * Create a new Language
     *
     * @author Varun Shoor
     * @param string $_languageTitle The Language Title
     * @param string $_languageISOCode The Language Unique ISO Code
     * @param string $_languageCharset The Language Character Set
     * @param string $_languageAuthor The Language Author
     * @param string $_languageDirection The Language Direction
     * @param int $_displayOrder The Language Display Order
     * @param bool $_isEnabled Whether this language pack is enabled
     * @param bool $_isDefault Whether the Language is Default
     * @param bool $_isMaster Whether the Language is inserted during setup/upgrade
     * @return mixed "_languageID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If Invalid Data is Provided or If the Creation fails
     */
    public static function Create($_languageTitle, $_languageISOCode, $_languageCharset, $_languageAuthor, $_languageDirection, $_displayOrder, $_languageFlagIcon = '', $_isEnabled = true, $_isDefault = false, $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_languageTitle) || empty($_languageISOCode) || empty($_languageCharset) || empty($_languageDirection)) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'languages', array('title' => $_languageTitle, 'languagecode' => $_languageISOCode, 'charset' => $_languageCharset, 'author' => ReturnNone($_languageAuthor), 'textdirection' => $_languageDirection, 'ismaster' => (int)($_isMaster), 'isdefault' => (int)($_isDefault), 'displayorder' => $_displayOrder, 'flagicon' => ReturnNone($_languageFlagIcon), 'isenabled' => (int)($_isEnabled)), 'INSERT');
        if (!$_queryResult) {
            throw new SWIFT_Language_Exception(SWIFT_CREATEFAILED);
        }

        $_languageID = $_SWIFT->Database->Insert_ID();
        if (!$_languageID) {
            throw new SWIFT_Language_Exception(SWIFT_CREATEFAILED);
        }

        $_languagePhraseID = SWIFT_LanguagePhrase::Create($_languageID, SWIFT_LanguagePhrase::SECTION_DEFAULT, SWIFT_LanguagePhrase::PHRASE_CHARSET, $_languageCharset, APP_BASE, true);

        self::RebuildCache();

        return $_languageID;
    }

    /**
     * Imports Phrase from Master Language
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not loaded or if Invalid Data is Received
     */
    public function ImportPhraseFromMaster()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_masterLanguageIDList = self::GetMasterLanguageIDList();
        if (!_is_array($_masterLanguageIDList)) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        // Do a fetch for all phrases of master language
        $_languagePhraseList = $_languagePhraseContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid IN (" . BuildIN($_masterLanguageIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['code'], $_languagePhraseList)) {
                $_languagePhraseContainer[] = $this->Database->Record;
                $_languagePhraseList[] = $this->Database->Record['code'];
            }
        }

        foreach ($_languagePhraseContainer as $_key => $_val) {
            SWIFT_LanguagePhrase::Replace($this->GetLanguageID(), $_val['section'], $_val['code'], $_val['contents'], $_val['appname']);
        }

        self::RebuildCache();
        SWIFT_LanguagePhrase::RebuildCache($this->GetLanguageID());

        return true;
    }

    /**
     * Update the Language Record
     *
     * @author Varun Shoor
     * @param string $_languageTitle The Language Title
     * @param string $_languageISOCode The Language Unique ISO Code
     * @param string $_languageCharset The Language Character Set
     * @param string $_languageAuthor The Language Author
     * @param string $_languageDirection The Language Direction
     * @param int $_displayOrder The Language Display Order
     * @param bool $_isEnabled Whether this language pack is enabled
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not loaded, If Invalid Data is Provided OR If the Update Failed
     */
    public function Update($_languageTitle, $_languageISOCode, $_languageCharset, $_languageAuthor, $_languageDirection, $_displayOrder, $_languageFlagIcon = '', $_isEnabled = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_languageTitle) || empty($_languageISOCode) || empty($_languageCharset) || empty($_languageDirection)) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $_queryResult = $this->Database->AutoExecute(TABLE_PREFIX . 'languages', array('title' => $_languageTitle, 'languagecode' => $_languageISOCode, 'charset' => $_languageCharset, 'author' => ReturnNone($_languageAuthor), 'textdirection' => $_languageDirection, 'displayorder' => $_displayOrder, 'flagicon' => ReturnNone($_languageFlagIcon), 'isenabled' => (int)($_isEnabled)), 'UPDATE', "languageid = '" . (int)($this->GetLanguageID()) . "'");
        if (!$_queryResult) {
            throw new SWIFT_Language_Exception(SWIFT_UPDATEFAILED);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Language Record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception if the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetLanguageID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Update the given phrases
     *
     * @author Varun Shoor
     * @param array $_languagePhraseContainer The Language Phrase Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not Loaded
     */
    public function UpdatePhraseList($_languagePhraseContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_languagePhraseContainer)) {
            return false;
        }

        $_finalLanguagePhraseContainer = array_keys($_languagePhraseContainer);

        $_existingPhraseContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . (int)($this->GetLanguageID()) . "' AND code IN (" . BuildIN($_finalLanguagePhraseContainer) . ")");
        while ($this->Database->NextRecord()) {
            $_existingPhraseContainer[] = $this->Database->Record;
        }

        foreach ($_existingPhraseContainer as $_key => $_val) {
            if (!isset($_languagePhraseContainer[$_val['code']])) {
                continue;
            }

            SWIFT_LanguagePhrase::Replace($this->GetLanguageID(), $_val['section'], $_val['code'], $_languagePhraseContainer[$_val['code']], '');
        }

        SWIFT_LanguagePhrase::RebuildCache($this->GetLanguageID());

        return true;
    }

    /**
     * Delete the given Language ID's
     *
     * @author Varun Shoor
     * @param array $_languageIDList The Language ID Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_languageIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_languageIDList)) {
            return false;
        }

        $_finalLanguageIDList = array();
        $_index = 1;
        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languages WHERE languageid IN (" . BuildIN($_languageIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['ismaster'] != 1) {
                $_finalText .= '<b>' . $_index . '. </b>' . '<img src="' . IIF(empty($_SWIFT->Database->Record['flagicon']), SWIFT::Get('themepath') . 'images/icon_language.gif', str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_SWIFT->Database->Record['flagicon'])) . '" align="absmiddle" border="0" /> ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br>';
                $_finalLanguageIDList[] = $_SWIFT->Database->Record['languageid'];
                $_index++;
            } else {
                SWIFT::Error($_SWIFT->Language->Get('titleunabledelmasterlang'), $_SWIFT->Language->Get('msgunabledelmasterlang'));
            }
        }

        if (!count($_finalLanguageIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledellang'), count($_finalLanguageIDList)), $_SWIFT->Language->Get('msgdellang') . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "languages WHERE languageid IN (" . BuildIN($_finalLanguageIDList) . ")");

        SWIFT_LanguagePhrase::DeleteOnLanguageList($_finalLanguageIDList);

        return true;
    }

    /**
     * Retrieve the Language ID's of all languages which have ismaster = '1'
     *
     * @author Varun Shoor
     * @return array The Language ID Array
     */
    public static function GetMasterLanguageIDList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageIDList = array();

        // Get all the master languages
        $_SWIFT->Database->Query("SELECT languageid FROM " . TABLE_PREFIX . "languages WHERE ismaster = '1'");
        while ($_SWIFT->Database->NextRecord()) {
            $_languageIDList[] = $_SWIFT->Database->Record["languageid"];
        }

        return $_languageIDList;
    }

    /**
     * Retrieve the ID of the default language.
     *
     * @author Mario Catalan
     * @return int The ID of the default language.
     */
    public static function GetDefaultLanguageId(): int
    {
        $_defaultLanguageIdQueryResult = SWIFT::GetInstance()->Database->QueryFetch(
            SWIFT_Language::GET_DEFAULT_LANGUAGE_ID_QUERY);
        if (is_array($_defaultLanguageIdQueryResult)
            && isset($_defaultLanguageIdQueryResult[self::LANGUAGE_ID_COL_NAME])) {
            $_defaultLanguageId = (int) $_defaultLanguageIdQueryResult[self::LANGUAGE_ID_COL_NAME];
        } else {
            $_defaultLanguageId = 0;
        }

        return $_defaultLanguageId;
    }

    /**
     * Unset the isdefault flag for ALL languages
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UnsetDefault()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'languages', array('isdefault' => '0'), 'UPDATE', '1 = 1');

        return true;
    }

    /**
     * Get the Last Probable Display Order
     *
     * @author Varun Shoor
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_totalItemContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "languages");
        $_displayOrder = $_totalItemContainer["totalitems"] + 1;

        return $_displayOrder;
    }

    /**
     * Rebuild the Language Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languages ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record["languageid"]] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('languagecache', $_cache);

        return true;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_languageIDSortList The Language ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_languageIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_languageIDSortList)) {
            return false;
        }

        foreach ($_languageIDSortList as $_languageID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'languages', array('displayorder' => $_displayOrder), 'UPDATE',
                "languageid = '" . $_languageID . "'");
        }

        self::RebuildCache();

        return true;
    }

    /**
     * @author Arotimi Busayo
     *
     * @return array $_languageList
     */
    public static function GetAvailableLanguageList($_onlyIDs = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageList = array();

        // Get all available languages
        $_SWIFT->Database->Query("SELECT languageid, title, languagecode FROM " . TABLE_PREFIX . "languages");
        while ($_SWIFT->Database->NextRecord()) {
            $_languageList[] = ($_onlyIDs) ? $_SWIFT->Database->Record['languageid'] : $_SWIFT->Database->Record;
        }

        return $_languageList;
    }
}

?>
