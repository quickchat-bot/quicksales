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

namespace Base\Models\Language;

use SWIFT;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_Language_Exception;
use SWIFT_Model;
use SWIFT_StringConverter;

/**
 * The Language Phrase Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_LanguagePhrase extends SWIFT_Model
{
    const TABLE_NAME = 'languagephrases';
    const PRIMARY_KEY = 'phraseid';

    const TABLE_STRUCTURE = "phraseid I PRIMARY AUTO NOTNULL,
                                languageid I DEFAULT '0' NOTNULL,
                                section C(100) DEFAULT '' NOTNULL,
                                code C(100) DEFAULT '' NOTNULL,
                                sectioncode C(255) DEFAULT '' NOTNULL,
                                appname C(255) DEFAULT '' NOTNULL,
                                contents X,

                                contentsdefault X,
                                ismaster I2 DEFAULT '1' NOTNULL,
                                revertrequired I2 DEFAULT '0' NOTNULL,
                                modified C(50) DEFAULT 'notmodified' NOTNULL";

    const INDEX_1 = 'languageid, code';
    const INDEXTYPE_1 = 'UNIQUE';

    const INDEX_2 = 'modified, revertrequired';
    const INDEX_3 = 'languageid, modified';
    const INDEX_4 = 'appname';


    protected $_dataStore = array();

    // Core Constants
    const SECTION_DEFAULT = 'default';
    const PHRASE_CHARSET = 'charset';

    const PHRASE_NOTMODIFIED = 'notmodified';
    const PHRASE_MODIFIED = 'modified';
    const PHRASE_REVERTREQUIRED = 'revertrequired';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_languagePhraseID The Language Phrase ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class could not be Loaded
     */
    public function __construct($_languagePhraseID)
    {
        parent::__construct();

        if (!$this->LoadData($_languagePhraseID)) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
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
     * @return bool "true" on Success, "false" otherwise
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()) || !$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'languagephrases', $this->GetUpdatePool(), 'UPDATE', "phraseid = '" . (int)($this->GetPhraseID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Language Phrase ID
     *
     * @author Varun Shoor
     * @return mixed "languagephraseid" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not Loaded
     */
    public function GetLanguagePhraseID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['phraseid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_languagePhraseID The Language Phrase ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_languagePhraseID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE phraseid = '" . $_languagePhraseID . "'");
        if (isset($_dataStore['phraseid']) && !empty($_dataStore['phraseid'])) {
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
     * @throws SWIFT_Language_Exception If the Class is not Loaded
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
     * @throws SWIFT_Language_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Language Phrase
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @param string $_phraseSection The Phrase Section
     * @param string $_phraseCode The Unique Phrase Code
     * @param string $_phraseContents The Phrase Contents
     * @param string $_appName (OPTIONAL)
     * @param bool $_isMaster (OPTIONAL) Whether this Phrase is a master phrase which cannot be deleted
     * @return bool|int "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If Invalid Data is Provided
     */
    public static function Create($_languageID, $_phraseSection, $_phraseCode, $_phraseContents = '', $_appName = '', $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_languageID) || empty($_phraseSection) || empty($_phraseCode)) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $StringConverter = new SWIFT_StringConverter();

        $_phraseSection = Clean($_phraseSection);
        $_phraseCode = Clean($StringConverter->ConvertAccented($_phraseCode));

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'languagephrases', array('languageid' => $_languageID, 'section' => $_phraseSection, 'code' => $_phraseCode,
            'sectioncode' => $_phraseSection . ':' . $_phraseCode, 'contents' => $_phraseContents, 'contentsdefault' => $_phraseContents, 'revertrequired' => '0',
            'modified' => self::PHRASE_NOTMODIFIED, 'ismaster' => (int)($_isMaster), 'appname' => $_appName), 'INSERT');

        if (!$_queryResult) {
            return false;
        }

        $_languagePhraseID = $_SWIFT->Database->Insert_ID();

        self::RebuildCache($_languageID);

        return $_languagePhraseID;
    }

    /**
     * Create a new Language Phrase
     *
     * @author Varun Shoor
     *
     * @param mixed $_languagePhraseContainer
     *
     * @return bool
     */
    public static function CreateBatch($_languagePhraseContainer)
    {
        return SWIFT::GetInstance()->Database->AutoExecuteBatch(TABLE_PREFIX . 'languagephrases', $_languagePhraseContainer);
    }

    /**
     * Replace an Existing Phrase
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @param string $_phraseSection The Phrase Section
     * @param string $_phraseCode The Unique Phrase Code
     * @param string $_phraseContents The Phrase Contents
     * @param string $_appName (OPTIONAL)
     * @param bool $_isUpgrade (OPTIONAL) Whether this function is being executed through an upgrade script
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If Invalid Data is Provided
     */
    public static function Replace($_languageID, $_phraseSection, $_phraseCode, $_phraseContents, $_appName = '', $_isUpgrade = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_languageID) || empty($_phraseSection) || empty($_phraseCode)) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $_phraseSection = Clean($_phraseSection);
        $_phraseCode = Clean($_phraseCode);

        $_phraseContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "languagephrases
            WHERE languageid = '" . $_languageID . "' AND code = '" . $_SWIFT->Database->Escape($_phraseCode) . "'");

        $_phraseAppName = '';
        if (!empty($_phraseContainer['appname'])) {
            $_phraseAppName = $_phraseContainer['appname'];
        }
        $_queryFields = array('languageid' => $_languageID, 'code' => $_phraseCode, 'section' => $_phraseSection, 'sectioncode' => $_phraseSection . ':' . $_phraseCode, 'appname' => $_phraseAppName);

        if ($_phraseAppName != $_appName && !empty($_appName)) {
            $_queryFields['appname'] = $_appName;
        }

        // We only update the default phrases in upgrade
        if ($_isUpgrade) {
            // If phrase doesnt exist, then we add the contents
            if (!isset($_phraseContainer['phraseid'])) {
                $_queryFields['contentsdefault'] = $_phraseContents;
                $_queryFields['contents'] = $_phraseContents;
                $_queryFields['revertrequired'] = '0';
                $_queryFields['modified'] = self::PHRASE_NOTMODIFIED;
            } else {
                $_queryFields['contentsdefault'] = $_phraseContents;
                $_queryFields['contents'] = $_phraseContainer['contents'];
                $_queryFields['revertrequired'] = '1';
                $_queryFields['modified'] = self::PHRASE_REVERTREQUIRED;
            }

            // We always update the main contents field if user is importing the pack directly...
        } else {
            if (!isset($_phraseContainer['phraseid'])) {
                $_queryFields['contentsdefault'] = $_phraseContents;
                $_queryFields['contents'] = $_phraseContents;
                $_queryFields['revertrequired'] = '0';
                $_queryFields['modified'] = self::PHRASE_NOTMODIFIED;
            } else if ($_phraseContents != $_phraseContainer['contents']) {
                $_queryFields['contentsdefault'] = $_phraseContainer['contentsdefault'];
                $_queryFields['contents'] = $_phraseContents;
                $_queryFields['revertrequired'] = '0';
                $_queryFields['modified'] = self::PHRASE_MODIFIED;
            } else {
                return false;
            }
        }

        $_SWIFT->Database->Replace(TABLE_PREFIX . 'languagephrases', $_queryFields, array('languageid', 'code'));

        return true;
    }

    /**
     * Delete the Phrase record
     *
     * @author Varun Shoor
     * @return mixed "core" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetLanguagePhraseID()));

        $_phraseCode = $this->GetProperty('code');

        $this->SetIsClassLoaded(false);

        return $_phraseCode;
    }

    /**
     * Delete a list of language phrases
     *
     * @author Varun Shoor
     * @param array $_languagePhraseIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_languagePhraseIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_languagePhraseIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "languagephrases
            WHERE phraseid IN (" . BuildIN($_languagePhraseIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete phrases on a list of app names
     *
     * @author Varun Shoor
     * @param array $_appNameList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnApp($_appNameList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_appNameList)) {
            return false;
        }

        $_languagePhraseIDList = array();
        $_SWIFT->Database->Query("SELECT phraseid FROM " . TABLE_PREFIX . "languagephrases
            WHERE appname IN (" . BuildIN($_appNameList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_languagePhraseIDList[] = $_SWIFT->Database->Record['phraseid'];
        }

        self::DeleteList($_languagePhraseIDList);

        return true;
    }

    /**
     * Delete the phrases on the provided Language IDs
     *
     * @author Varun Shoor
     * @param array $_languageIDList The Language ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnLanguageList($_languageIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_languageIDList)) {
            return false;
        }

        // Registry cleanup
        $_languageSectionList = $_languageRegistryKeyList = array();
        $_SWIFT->Database->Query("SELECT DISTINCT section FROM " . TABLE_PREFIX . "languagephrases");
        while ($_SWIFT->Database->NextRecord()) {
            $_languageSectionList[] = $_SWIFT->Database->Record['section'];
        }

        // iterate through the languages and make sure we arent deleting the master language
        foreach ($_SWIFT->Cache->Get('languagecache') as $_key => $_val) {
            if (in_array($_val['languageid'], $_languageIDList) && $_val['ismaster'] == true) {
                // woops!.. no go.. nuke that value
                $_temporaryArray = array_flip($_languageIDList);
                unset($_temporaryArray[$_val['languageid']]);
                $_languageIDList = array_flip($_temporaryArray);

            } else if (in_array($_val['languageid'], $_languageIDList)) {
                // For all the sections we have.. make a key delete list
                foreach ($_languageSectionList as $_sectionKey => $_sectionVal) {
                    $_dumpKeyName = $_val['languagecode'] . ':' . $_sectionVal;
                    if (!in_array($_dumpKeyName, $_languageRegistryKeyList)) {
                        $_languageRegistryKeyList[] = $_dumpKeyName;
                    }
                }
            }
        }

        $_SWIFT->Registry->DeleteKeyList($_languageRegistryKeyList);

        $_languagePhraseIDList = array();
        $_SWIFT->Database->Query("SELECT phraseid FROM " . TABLE_PREFIX . "languagephrases
            WHERE languageid IN (" . BuildIN($_languageIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_languagePhraseIDList[] = $_SWIFT->Database->Record['phraseid'];
        }

        self::DeleteList($_languagePhraseIDList);

        return true;
    }

    /**
     * Rebuild the Phrase Cache
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID to rebuild phrases for, 0 = ALL
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache($_languageID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_queryResult = $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languages" . IIF(!empty($_languageID), " WHERE languageid = '" . $_languageID . "'"));
        $_languageContainer = $_languageIDList = array();
        while ($_SWIFT->Database->NextRecord()) {
            $_languageContainer[$_SWIFT->Database->Record['languageid']] = $_SWIFT->Database->Record;
            $_languageContainer[$_SWIFT->Database->Record['languageid']]['_phrases'] = array();
            $_languageIDList[] = $_SWIFT->Database->Record['languageid'];
        }

        $_phraseStore = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid IN (" . BuildIN($_languageIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_languageContainer[$_SWIFT->Database->Record['languageid']]['_phrases'][$_SWIFT->Database->Record['phraseid']] = $_SWIFT->Database->Record;
            $_languageISOCode = $_languageContainer[$_SWIFT->Database->Record['languageid']]['languagecode'];

            $_phraseStore[$_languageISOCode][] = $_SWIFT->Database->Record;
        }

        if (_is_array($_phraseStore)) {
            foreach ($_phraseStore as $_key => $_val) {
                $_phraseContainer = array();

                if (_is_array($_val)) {
                    foreach ($_val as $_phraseKey => $_phraseVal) {
                        $_phraseContainer[$_phraseVal["section"]][$_phraseVal["code"]] = $_phraseVal["contents"];
                    }
                }

                foreach ($_phraseContainer as $_containerKey => $_containerVal) {
                    $_SWIFT->Cache->Update($_key . ':' . $_containerKey, $_containerVal);
                }
            }
        }

        return true;
    }

    /**
     * Restire the Language Phrases
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @param array $_languagePhraseIDList The Language Phrase ID List
     * @return bool|array
     * @throws SWIFT_Language_Exception If Invalid Data is Provided
     */
    public static function Restore($_languageID, $_languagePhraseIDList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $_SWIFT->Cache->Get('languagecache');
        if (empty($_languageID) || !isset($_languageCache[$_languageID])) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $_languagePhraseContainer = array();
        if (_is_array($_languagePhraseIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . $_languageID . "' AND phraseid IN (" . BuildIN($_languagePhraseIDList) . ")");
        } else {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . $_languageID . "'");
        }
        while ($_SWIFT->Database->NextRecord()) {
            $_languagePhraseContainer[$_SWIFT->Database->Record['phraseid']] = $_SWIFT->Database->Record;
        }

        $_sql = "UPDATE " . TABLE_PREFIX . "languagephrases SET contents = " . $_SWIFT->Database->Param(0) . ", modified = 'notmodified', revertrequired = '0' WHERE phraseid = " . $_SWIFT->Database->Param(1);

        $_transactionData = array();
        foreach ($_languagePhraseContainer as $_key => $_val) {
            $_transactionData[] = array($_val['contentsdefault'], (int)($_val['phraseid']));
        }

        $_SWIFT->Database->StartTrans();
        $_SWIFT->Database->Execute($_sql, $_transactionData);
        $_SWIFT->Database->CompleteTrans();

        self::RebuildCache($_languageID);

        return $_languagePhraseContainer;

    }

    /**
     * Retrieve the list of language phrases requiring revert due to upgrade
     *
     * @author Varun Shoor
     * @return array The Language Phrase List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetUpgradeRevertList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $_SWIFT->Cache->Get('languagecache');

        $_phraseList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE modified = '" . self::PHRASE_REVERTREQUIRED . "' OR revertrequired = '1'");
        while ($_SWIFT->Database->NextRecord()) {
            if (!isset($_languageCache[$_SWIFT->Database->Record['languageid']])) {
                continue;
            }

            $_languageTitle = $_languageCache[$_SWIFT->Database->Record['languageid']]['title'];

            $_phraseList[$_languageTitle][] = $_SWIFT->Database->Record['code'];
        }

        return $_phraseList;
    }

    /**
     * KAYAKOC-5722
     * Added as a fill-in method to clear phpstan error since I'm not certain what function
     * the supposed method was intended to have performed
     *
     * @author Banjo Mofesola Paul <banjo.paul@aurea.com>
     */
    private function GetPhraseID()
    {
    }
}

?>
