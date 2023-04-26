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

use Base\Library\Language\SWIFT_Language_Exception;
use Base\Models\Language\SWIFT_Language;
use SWIFT;
use SWIFT_Library;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;

/**
 * A Linked Volatile Language Phrase Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_LanguagePhraseLinked extends SWIFT_Library
{
    // Core Constants
    const TYPE_DEPARTMENT = 'department';
    const TYPE_CUSTOMFIELD = 'customfield';
    const TYPE_CUSTOMFIELDGROUP = 'customfieldgroup';
    const TYPE_TICKETPRIORITY = 'ticketpriority';
    const TYPE_TICKETSTATUS = 'ticketstatus';
    const TYPE_TICKETTYPE = 'tickettype';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check to see whether it is a valid link type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_linkType)
    {
        if ($_linkType == self::TYPE_DEPARTMENT || $_linkType == self::TYPE_CUSTOMFIELD || $_linkType == self::TYPE_CUSTOMFIELDGROUP ||
            $_linkType == self::TYPE_TICKETPRIORITY || $_linkType == self::TYPE_TICKETSTATUS || $_linkType == self::TYPE_TICKETTYPE) {
            return true;
        }

        return false;
    }

    /**
     * Update the Linked Phrase Value
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_typeID The Type ID Associated with Link
     * @param int $_languageID The Language ID
     * @param string $_phraseValue The Phrase Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If Class is not Loaded or if Invalid Data is Provided
     */
    public function Update($_linkType, $_typeID, $_languageID, $_phraseValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || !self::IsValidType($_linkType)) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $_isoCode = $_SWIFT_LanguageObject->GetProperty('languagecode');

        $_cacheStoreName = $_isoCode . ':custom';
        $this->Cache->Load($_cacheStoreName);

        $_cacheStore = SWIFT::Get($_cacheStoreName);

        if (!_is_array($_cacheStore)) {
            SWIFT::Set($_cacheStoreName, array());
        }

        // Do we need to remove this key?
        if (isset($_cacheStore[$_linkType][$_typeID]) && empty($_phraseValue)) {
            unset($_cacheStore[$_linkType][$_typeID]);
        }

        if (!empty($_phraseValue)) {
            $_cacheStore[$_linkType][$_typeID] = $_phraseValue;
        }

        $this->Cache->Update($_cacheStoreName, $_cacheStore);

        return true;
    }

    /**
     * Update a list of phrases for all current languages
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_typeID The Type ID Associated with Link
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateList($_linkType, $_typeID, $_phraseContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $_SWIFT->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded() || !self::IsValidType($_linkType) || !$_languageCache) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        foreach ($_languageCache as $_key => $_val) {
            if (!isset($_phraseContainer[$_val['languageid']])) {
                $_phraseValue = '';
            } else {
                $_phraseValue = $_phraseContainer[$_val['languageid']];
            }

            $this->Update($_linkType, $_typeID, $_val['languageid'], $_phraseValue);
        }

        return true;
    }

    /**
     * Get the Phrase List associated with the Link Type and Type ID
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_typeID The Type ID Associated with Link
     * @return mixed "_store" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If Invalid Data is Provided or If Class is not Loaded
     */
    public function Get($_linkType, $_typeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $_SWIFT->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded() || !self::IsValidType($_linkType) || !$_languageCache) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $_store = array();

        foreach ($_languageCache as $_key => $_val) {
            $_isoCode = $_val['languagecode'];
            $_cacheStoreName = $_isoCode . ':custom';
            $this->Cache->Load($_cacheStoreName);

            $_cacheStore = $_SWIFT->Cache->Get($_cacheStoreName);

            if (!_is_array($_cacheStore)) {
                $_store[$_val['languageid']] = '';
            } else {
                if (isset($_cacheStore[$_linkType][$_typeID])) {
                    $_store[$_val['languageid']] = $_cacheStore[$_linkType][$_typeID];
                } else {
                    $_store[$_val['languageid']] = '';
                }
            }
        }

        return $_store;
    }

    /**
     * Render the Language Cache Store
     *
     * @author Varun Shoor
     * @param mixed $_cacheStoreType The Cache Store Type
     * @param int $_typeID The Type ID that is currently active
     * @param mixed $_mode The User Interface Mode
     * @param SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject The Tab Object under which the fields will be added
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Language_Exception If the Class is not Loaded
     */
    public function Render($_cacheStoreType, $_typeID, $_mode, SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidType($_cacheStoreType) || !$_SWIFT_UserInterfaceTabObject->GetIsClassLoaded()) {
            throw new SWIFT_Language_Exception(SWIFT_INVALIDDATA);
        }

        $_languageStore = array();
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_languageStore = $this->Get($_cacheStoreType, $_typeID);
        }

        $_languageCache = $this->Cache->Get('languagecache');
        if (!$_languageCache) {
            return false;
        }

        foreach ($_languageCache as $_key => $_val) {
            $_fieldValue = '';

            if (isset($_POST['languages'][$_key])) {
                $_fieldValue = $_POST['languages'][$_key];
            } elseif (isset($_languageStore[$_key])) {
                $_fieldValue = $_languageStore[$_key];
            }

            $_SWIFT_UserInterfaceTabObject->Text('languages[' . $_key . ']', '<img src="' . IIF(empty($_val['flagicon']), SWIFT::Get('themepath') . 'images/icon_language.gif', str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_val['flagicon'])) . '" align="absmiddle" border="0" /> ' . IIF($_val['ismaster'] == 1, '<i>') . htmlspecialchars($_val['title']) . IIF($_val['ismaster'] == 1, '</i>'), '', $_fieldValue);
        }

        return true;
    }
}

?>
