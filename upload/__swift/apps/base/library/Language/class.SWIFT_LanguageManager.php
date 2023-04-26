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

namespace Base\Library\Language;

use Base\Models\Language\SWIFT_Language;
use Base\Models\Language\SWIFT_LanguagePhrase;
use SWIFT_App;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Library;
use SWIFT_StringConverter;

/**
 * The Language Manager Class (IMPORT/EXPORT)
 *
 * @property \SWIFT_XML $XML
 * @author Varun Shoor
 */
class SWIFT_LanguageManager extends SWIFT_Library
{
    // Core Constants
    const LANGUAGE_SUFFIX = '.language.xml';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Import a Language Pack
     *
     * @author Varun Shoor
     * @param string $_fileName The Language Filename
     * @param bool $_isMaster Whether the language is installed during setup/upgrade
     * @param bool $_isDefault Whether this language is default
     * @param bool $_doVersionCheck Whether a version check should be carried out
     * @param bool $_isEnabled Whether the language need to be enabled or not
     * @return mixed array(array(statusText, result, reasonFailure), ...) on Success, "false" otherwise, "-1" in case of version check failure
     */
    public function Import($_fileName, $_isMaster = false, $_isDefault = false, $_doVersionCheck = false, $_isEnabled = true)
    {
        if (!file_exists($_fileName)) {
            return false;
        }

        // Parse the Language XML File
        $_languageXMLContainer = $this->XML->XMLToTree(file_get_contents($_fileName));

        if (!is_array($_languageXMLContainer)) {
            return false;
        }

        $_finalLanguageContainer = &$_languageXMLContainer["swiftlanguage"][0]["children"];

        $_languageTitle = $_languageISOCode = $_languageAuthor = $_languageCharset = $_languageVersion = $_languageFlagIcon = '';

        if (isset($_finalLanguageContainer["title"]) && isset($_finalLanguageContainer["title"][0]["values"][0])) {
            $_languageTitle = $_finalLanguageContainer["title"][0]["values"][0];
        }

        if (isset($_finalLanguageContainer["isocode"]) && isset($_finalLanguageContainer["isocode"][0]["values"][0])) {
            $_languageISOCode = $_finalLanguageContainer["isocode"][0]["values"][0];
        }

        if (isset($_finalLanguageContainer["author"]) && isset($_finalLanguageContainer["author"][0]["values"][0])) {
            $_languageAuthor = $_finalLanguageContainer["author"][0]["values"][0];
        }

        if (isset($_finalLanguageContainer["charset"]) && isset($_finalLanguageContainer["charset"][0]["values"][0])) {
            $_languageCharset = $_finalLanguageContainer["charset"][0]["values"][0];
        }

        if (isset($_finalLanguageContainer["version"]) && isset($_finalLanguageContainer["version"][0]["values"][0])) {
            $_languageVersion = $_finalLanguageContainer["version"][0]["values"][0];
        }

        if (isset($_finalLanguageContainer["flagicon"]) && isset($_finalLanguageContainer["flagicon"][0]["values"][0])) {
            $_languageFlagIcon = $_finalLanguageContainer["flagicon"][0]["values"][0];
        }

        $_appName = APP_BASE;
        if (isset($_finalLanguageContainer['app']) && isset($_finalLanguageContainer['app'][0]['values'][0])) {
            $_appName = $_finalLanguageContainer['app'][0]['values'][0];
        }

        if (!empty($_languageVersion) && $_doVersionCheck == true) {
            // check for version
            if (version_compare($_languageVersion, SWIFT_VERSION) == -1) {
                return -1;
            }
        }

        if (empty($_languageTitle)) {
            return false;
        }

        if (isset($_finalLanguageContainer["textdirection"]) && strtoupper($_finalLanguageContainer["textdirection"][0]["values"][0]) == "RTL") {
            $_languageDirection = SWIFT_Language::DIRECTION_RTL;
        } else {
            $_languageDirection = SWIFT_Language::DIRECTION_LTR;
        }

        // Delete the current master language file if we are inserting a new one
        $_createLanguage = false;
        if ($_isMaster) {
            $_languageIDList = SWIFT_Language::GetMasterLanguageIDList();

            if (_is_array($_languageIDList)) {
                $_languageID = $_languageIDList[0];
            } else {
                $_createLanguage = true;
            }
        } else {
            $_createLanguage = true;
        }

        $_statusListContainer = array();

        if ($_createLanguage) {
            // Are we supposed to set this language as default?
            if ($_isDefault) {
                SWIFT_Language::UnsetDefault();
            }

            $_displayOrder = SWIFT_Language::GetLastDisplayOrder();

            $_languageID = SWIFT_Language::Create($_languageTitle, $_languageISOCode, $_languageCharset, $_languageAuthor, $_languageDirection, $_displayOrder, $_languageFlagIcon, $_isEnabled, $_isDefault, $_isMaster);
            $_statusListContainer[] = array('statusText' => sprintf($this->Language->Get('sclanguage'), $_languageTitle), 'result' => $_languageID, 'reasonFailure' => $this->Database->FetchLastError());
        }

        if (!isset($_languageID)) {
            return false;
        }

        /**
         * Begin Insertion of Phrases
         */
        $_index = 0;
        $_sectionList = array();
        $_languagePhrases = array();

        for ($kk = 0; $kk < count($_finalLanguageContainer["phrase"]); $kk++) {
            $_languagePhraseContainer = $_finalLanguageContainer["phrase"][$kk]["attrs"];

            $_languagePhraseContents = '';
            if (isset($_finalLanguageContainer["phrase"][$kk]["values"][0])) {
                $_languagePhraseContents = $_finalLanguageContainer["phrase"][$kk]["values"][0];
            }

            if (!isset($_languagePhraseContainer['section']) || !isset($_languagePhraseContainer['code'])) {
                continue;
            }

            if (isset($_languagePhraseContainer['code']) && $_languagePhraseContainer['code'] == SWIFT_LanguagePhrase::PHRASE_CHARSET) {
                continue;
            }

            if (!in_array($_languagePhraseContainer['section'], $_sectionList)) {
                $_sectionList[] = $_languagePhraseContainer['section'];

                $_statusListContainer[] = array('statusText' => sprintf($this->Language->Get('sclanguagesection'), $_languagePhraseContainer['section']), 'result' => true, 'reasonFailure' => $this->Database->FetchLastError());
            }

            $StringConverter = new SWIFT_StringConverter();

            $_languagePhrases[] = array(
                'languageid' => $_languageID,
                'section' => Clean($_languagePhraseContainer['section']),
                'code' => Clean($StringConverter->ConvertAccented($_languagePhraseContainer['code'])),
                'sectioncode' => $_languagePhraseContainer['section'] . ':' . $_languagePhraseContainer['code'],
                'contents' => $_languagePhraseContents,
                'contentsdefault' => $_languagePhraseContents,
                'revertrequired' => '0',
                'modified' => SWIFT_LanguagePhrase::PHRASE_NOTMODIFIED,
                'ismaster' => (int)($_isMaster),
                'appname' => $_appName
            );

        }

        SWIFT_LanguagePhrase::CreateBatch($_languagePhrases);

        SWIFT_Language::RebuildCache();

        SWIFT_LanguagePhrase::RebuildCache($_languageID);

        return array($_languageID, $_statusListContainer);
    }

    /**
     * Merge a Language Pack with Existing One
     *
     * @author Varun Shoor
     * @param int $_languageID The Existing Language ID to Merge With
     * @param string $_fileName The Language Filename
     * @param bool $_doVersionCheck Whether a version check should be carried out
     * @param bool $_isUpgrade Whether the function is being executed in upgrade script
     * @return bool|string
     */
    public function Merge($_languageID, $_fileName, $_doVersionCheck = false, $_isUpgrade = false)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        if (!file_exists($_fileName)) {
            return false;
        }

        // Parse the Language XML File
        $_languageXMLContainer = $this->XML->XMLToTree(file_get_contents($_fileName));

        if (!is_array($_languageXMLContainer)) {
            return false;
        }

        $_finalLanguageContainer = &$_languageXMLContainer['swiftlanguage'][0]['children'];

        $_languageTitle = $_languageISOCode = $_languageAuthor = $_languageCharset = $_languageVersion = $_languageFlagIcon = '';

        if (isset($_finalLanguageContainer['title']) && isset($_finalLanguageContainer['title'][0]['values'][0])) {
            $_languageTitle = $_finalLanguageContainer['title'][0]['values'][0];
        }

        if (isset($_finalLanguageContainer['isocode']) && isset($_finalLanguageContainer['isocode'][0]['values'][0])) {
            $_languageISOCode = $_finalLanguageContainer['isocode'][0]['values'][0];
        }

        if (isset($_finalLanguageContainer['author']) && isset($_finalLanguageContainer['author'][0]['values'][0])) {
            $_languageAuthor = $_finalLanguageContainer['author'][0]['values'][0];
        }

        if (isset($_finalLanguageContainer['charset']) && isset($_finalLanguageContainer['charset'][0]['values'][0])) {
            $_languageCharset = $_finalLanguageContainer['charset'][0]['values'][0];
        }

        if (isset($_finalLanguageContainer['version']) && isset($_finalLanguageContainer['version'][0]['values'][0])) {
            $_languageVersion = $_finalLanguageContainer['version'][0]['values'][0];
        }

        if (isset($_finalLanguageContainer['flagicon']) && isset($_finalLanguageContainer['flagicon'][0]['values'][0])) {
            $_languageFlagIcon = $_finalLanguageContainer['flagicon'][0]['values'][0];
        }

        $_appName = APP_BASE;
        if (isset($_finalLanguageContainer['app']) && isset($_finalLanguageContainer['app'][0]['values'][0])) {
            $_appName = $_finalLanguageContainer['app'][0]['values'][0];
        }

        if (!empty($_languageVersion) && $_doVersionCheck == true) {
            // check for version
            if (version_compare($_languageVersion, SWIFT_VERSION) == -1) {
                return '-1';
            }
        }

        if (empty($_languageTitle)) {
            return false;
        }

        if (isset($_finalLanguageContainer['textdirection']) && strtoupper($_finalLanguageContainer['textdirection'][0]['values'][0]) == 'RTL') {
            $_languageDirection = SWIFT_Language::DIRECTION_RTL;
        } else {
            $_languageDirection = SWIFT_Language::DIRECTION_LTR;
        }

        $_phraseContainer = $_SWIFT_LanguageObject->GetPhrases($_isUpgrade);
        if (!$_phraseContainer) {
            return false;
        }

        /**
         * Begin Merging of Phrases
         */
        $_index = 0;
        $_sectionList = array();

        for ($kk = 0; $kk < count($_finalLanguageContainer["phrase"]); $kk++) {
            $_languagePhraseContainer = $_finalLanguageContainer["phrase"][$kk]["attrs"];

            $_languagePhraseContents = '';
            if (isset($_finalLanguageContainer["phrase"][$kk]["values"][0])) {
                $_languagePhraseContents = $_finalLanguageContainer["phrase"][$kk]["values"][0];
            }

            /**
             * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
             *
             * SWIFT-4762 While upgrading product restore notification should come only for modified template, phrases.
             *
             * Comments : Code to remove Carriage return character with Phrases
             */
            $_languagePhraseContents = str_replace("\r\n", SWIFT_CRLF, $_languagePhraseContents);

            if (isset($_phraseContainer[$_languagePhraseContainer['section'] . ':' . $_languagePhraseContainer['code']])) {
                $_databasePhraseContents = $_phraseContainer[$_languagePhraseContainer['section'] . ':' . $_languagePhraseContainer['code']];
            } else {
                $_databasePhraseContents = false;
            }

            if (!isset($_languagePhraseContainer['section']) || !isset($_languagePhraseContainer['code'])) {
                continue;
            }

            if (isset($_languagePhraseContainer['code']) && $_languagePhraseContainer['code'] == SWIFT_LanguagePhrase::PHRASE_CHARSET) {
                continue;
            }

            if (($_databasePhraseContents != $_languagePhraseContents) || empty($_databasePhraseContents)) {
                // Phrase doesnt match.. we need to replace/insert it
                SWIFT_LanguagePhrase::Replace($_languageID, $_languagePhraseContainer['section'], $_languagePhraseContainer['code'], $_languagePhraseContents, $_appName, $_isUpgrade);
            }
        }

        SWIFT_Language::RebuildCache();
        SWIFT_LanguagePhrase::RebuildCache($_languageID);

        return true;
    }

    /**
     * Upgrade for all languages
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name
     * @return array|bool
     * @throws SWIFT_Exception If Class is not Loaded
     */
    public function UpgradeLanguages($_fileName)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageCode = explode('-', basename($_fileName, '.xml'), 2);
        if (!isset($_languageCode[1])) {
            $_languageCode = "en-us";
        } else {
            $_languageCode = $_languageCode[1];
        }

        $_languageContainer = $_languageList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languages WHERE languagecode = " . "'" . $_languageCode . "'" . " ORDER BY languageid ASC");
        while ($this->Database->NextRecord()) {
            $_languageContainer[$this->Database->Record['languageid']] = $this->Database->Record;

            $_languageList[] = $this->Database->Record['title'];
        }

        foreach ($_languageContainer as $_languageID => $_language) {
            $this->Merge($_languageID, $_fileName, false, true);
        }

        return $_languageList;
    }

    /**
     * Generate the Language Filename for Export
     *
     * @author Varun Shoor
     * @param string $_languageTitle The Language Title
     * @return string The Language Filename
     */
    static private function GenerateFileName($_languageTitle)
    {
        return strtolower(SWIFT_PRODUCT) . "." . str_replace(".", "-", SWIFT_VERSION) . "." . strtolower(Clean($_languageTitle)) . self::LANGUAGE_SUFFIX;
    }

    /**
     * Export the Language Data
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID to Export
     * @param string $_customFileName (OPTIONAL) The Custom Filename to Export As
     * @return bool "true" on Success, "false" otherwise
     */
    public function Export($_languageID, $_customFileName = '')
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        if (empty($_customFileName)) {
            $_fileName = self::GenerateFileName($_SWIFT_LanguageObject->GetProperty('title'));
        } else {
            $_fileName = $_customFileName;
        }

        $this->XML->AddComment(sprintf($this->Language->Get('generationdate'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, DATENOW)));
        $this->XML->AddParentTag('swiftlanguage');
        $this->XML->AddTag('title', $_SWIFT_LanguageObject->GetProperty('title'));
        $this->XML->AddTag('isocode', $_SWIFT_LanguageObject->GetProperty('languagecode'));
        $this->XML->AddTag('author', $_SWIFT_LanguageObject->GetProperty('author'));
        $this->XML->AddTag('charset', $_SWIFT_LanguageObject->GetProperty('charset'));
        $this->XML->AddTag('textdirection', strtoupper($_SWIFT_LanguageObject->GetProperty('textdirection')));
        $this->XML->AddTag('version', SWIFT_VERSION);
        $this->XML->AddTag('flagicon', $_SWIFT_LanguageObject->GetProperty('flagicon'));

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . $_languageID . "' ORDER BY code ASC");
        while ($this->Database->NextRecord()) {
            $this->XML->AddTag('phrase', $this->Database->Record['contents'], array('section' => $this->Database->Record['section'], 'code' => $this->Database->Record['code']));
        }

        $this->XML->endTag('swiftlanguage');

        $_xmlData = $this->XML->ReturnXML();

        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            ini_set('zlib.output_compression', 'Off');
        }

        header("Content-Type: application/force-download");

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
            header("Content-Disposition: attachment; filename=\"" . $_fileName . "\"");
        } else {
            header("Content-Disposition: attachment; filename=\"" . $_fileName . "\"");
        }

        header("Content-Transfer-Encoding: binary\n");
//        header("Content-Length: ". strlen($_xmlData) ."\n");
        echo $_xmlData;

        return true;
    }

    /**
     * Import all files
     *
     * @author Varun Shoor
     * @param bool $_isUpgrade
     * @return array Result Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportAll($_isUpgrade)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_statusListContainer = array();

        $_installedAppList = SWIFT_App::GetInstalledApps();

        foreach ($_installedAppList as $_appName) {
            $_SWIFT_AppObject = false;
            try {
                $_SWIFT_AppObject = SWIFT_App::Get($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                continue;
            }

            if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) {
                continue;
            }

            // See if we have a language file in there..
            $_languageFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/language.xml';
            if (!file_exists($_languageFile)) {
                continue;
            }

            $_isMaster = $_isDefault = false;
            /**
             * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>, Mansi Wason <mansi.wason@kayako.com>
             *
             * SWIFT-4303 : Languages support within product
             *
             * Comments: Changes to support multiple language files placed at same location.
             */
            /**
             * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
             *
             * SWIFT-5063 : "Warning array_merge(): Argument #2 is not an array" error in case of "open_basedir" is enabled
             *
             * Comments: http://php.net/glob && http://stackoverflow.com/questions/9213249/behaviour-of-glob-function-in-php-is-different-with-open-basedir
             */
            $_glob = glob($_SWIFT_AppObject->GetDirectory() . DIRECTORY_SEPARATOR . SWIFT_CONFIG_DIRECTORY . DIRECTORY_SEPARATOR . 'language-*.xml');
            if (!is_array($_glob)) {
                $_glob = array();
            }
            $_languageList = array_merge(array($_languageFile), $_glob);

            foreach ($_languageList as $_languageFile) {
                // Enabling 'english' as master language
                $_isDefault = $_isMaster = (stristr($_languageFile, 'language.xml') === false) ? false : true;
                $_statusList = array();
                if ($_isUpgrade) {
                    $_statusList = $this->UpgradeLanguages($_languageFile);
                } else {
                    $_isEnabled = false;
                    if ($_isDefault) {
                        $_isEnabled = true;
                    }
                    $_statusList = $this->Import($_languageFile, $_isMaster, $_isDefault, false, $_isEnabled);
                    if (isset($_statusList[1]) && _is_array($_statusList[1])) {
                        $_statusList = $_statusList[1];
                    }
                }

                $_statusListContainer = array_merge($_statusList, $_statusListContainer);
            }
        }

        if ($_isUpgrade) {
            return array_unique($_statusListContainer);
        } else {
            return $_statusListContainer;
        }
    }

    /**
     * Clear the language phrases on the provided list of apps
     *
     * @author Varun Shoor
     * @param array $_appNameList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteOnApp($_appNameList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        } elseif (!_is_array($_appNameList)) {
            return false;
        }

        SWIFT_LanguagePhrase::DeleteOnApp($_appNameList);

        return true;
    }
}

?>
