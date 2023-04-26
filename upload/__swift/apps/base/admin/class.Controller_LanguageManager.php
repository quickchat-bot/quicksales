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

namespace Base\Admin;

use Base\Library\Language\SWIFT_LanguageManager;
use Controller_admin;
use SWIFT;
use Base\Models\Language\SWIFT_Language;
use Base\Models\Language\SWIFT_LanguagePhrase;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Language Management Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \SWIFT_Loader $Load
 * @property SWIFT_LanguageManager $LanguageManager
 * @property View_LanguageManager $View
 * @author Varun Shoor
 */
class Controller_LanguageManager extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 1;

    const CONFIRMATION_MERGE = 'merge';
    const CONFIRMATION_IMPORT = 'import';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Language:LanguageManager', [], true, false, 'base');

        $this->Language->Load('languages');
    }

    /**
     * Render the Restore Form
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Restore($_languageID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('restorephrases'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderRestore($_languageID);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Phrase List
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RestoreList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $_SWIFT->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!isset($_POST['modified']) || !count($_POST['modified'])) {
            $this->UserInterface->Error($this->Language->Get('titlenooptsel'), $this->Language->Get('msgnooptsel'));

            $this->Load->Restore();

            return false;
        } elseif (!isset($_POST['languageid']) || !isset($_languageCache[$_POST['languageid']])) {
            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Restore();

            return false;
        } elseif ($_SWIFT->Staff->GetPermission('admin_canrestorephrases') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Restore($_POST['languageid']);

            return false;
        }

        // List all the phrases
        $_languagePhraseIDList = array();
        if (in_array(SWIFT_LanguagePhrase::PHRASE_MODIFIED, $_POST['modified'])) {
            $this->Database->Query("SELECT phraseid FROM " . TABLE_PREFIX . "languagephrases
                WHERE languageid = '" . (int)($_POST['languageid']) . "' AND modified = '" . SWIFT_LanguagePhrase::PHRASE_MODIFIED . "'");
            while ($this->Database->NextRecord()) {
                if (!in_array($this->Database->Record['phraseid'], $_languagePhraseIDList)) {
                    $_languagePhraseIDList[] = $this->Database->Record['phraseid'];
                }
            }
        }

        if (in_array(SWIFT_LanguagePhrase::PHRASE_REVERTREQUIRED, $_POST['modified'])) {
            $this->Database->Query("SELECT phraseid FROM " . TABLE_PREFIX . "languagephrases
                WHERE languageid = '" . (int)($_POST['languageid']) . "' AND modified = '" . SWIFT_LanguagePhrase::PHRASE_REVERTREQUIRED . "'");
            while ($this->Database->NextRecord()) {
                if (!in_array($this->Database->Record['phraseid'], $_languagePhraseIDList)) {
                    $_languagePhraseIDList[] = $this->Database->Record['phraseid'];
                }
            }
        }

        if (in_array(SWIFT_LanguagePhrase::PHRASE_NOTMODIFIED, $_POST['modified'])) {
            $this->Database->Query("SELECT phraseid FROM " . TABLE_PREFIX . "languagephrases
                WHERE languageid = '" . (int)($_POST['languageid']) . "' AND modified = '" . SWIFT_LanguagePhrase::PHRASE_NOTMODIFIED . "'");
            while ($this->Database->NextRecord()) {
                if (!in_array($this->Database->Record['phraseid'], $_languagePhraseIDList)) {
                    $_languagePhraseIDList[] = $this->Database->Record['phraseid'];
                }
            }
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('restorephrases'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderRestoreList($_POST['languageid'], $_languagePhraseIDList);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the Restore Variables
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function RestoreProcess($_languageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $this->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (empty($_languageID) || !isset($_languageCache[$_languageID])) {
            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Restore();

            return false;
        } elseif ($_SWIFT->Staff->GetPermission('admin_canrestorephrases') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Restore($_languageID);

            return false;
        }

        if (isset($_POST['itemid']) && _is_array($_POST['itemid'])) {
            $_restoreLanguagePhraseContainer = SWIFT_LanguagePhrase::Restore($_languageID, $_POST['itemid']);

            $_finalText = '';
            $_finaltext = sprintf($this->Language->Get('restorelanguage3'), htmlspecialchars($_languageCache[$_languageID]['title'])) . '<br />';

            if (_is_array($_restoreLanguagePhraseContainer)) {
                foreach ($_restoreLanguagePhraseContainer as $_key => $_val) {
                    if ($_val['modified'] == SWIFT_LanguagePhrase::PHRASE_NOTMODIFIED) {
                        $_modifiedStatusIcon = 'icon_templatenotmodified.gif';
                        $_modifiedStatusText = $this->Language->Get('notmodified');
                    } elseif ($_val["modified"] == SWIFT_LanguagePhrase::PHRASE_REVERTREQUIRED) {
                        $_modifiedStatusIcon = 'icon_templateupgrade.gif';
                        $_modifiedStatusText = $this->Language->Get('upgrade');
                    } else {
                        $_modifiedStatusIcon = 'icon_templatemodified.gif';
                        $_modifiedStatusText = $this->Language->Get('modified');
                    }

                    $_finalText .= '&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" align="absmiddle" border="0" /> ' . htmlspecialchars($_val['code']) . ' (' . '<img src="' . SWIFT::Get('themepath') . 'images/' . $_modifiedStatusIcon . '" border="0" align="absmiddle" />&nbsp;' . $_modifiedStatusText . ')<br />';
                }
            }

            $this->UserInterface->Info(sprintf($this->Language->Get('titlerestorephrases'), count($_restoreLanguagePhraseContainer)), $this->Language->Get('msgrestorephrases') . '<br />' . $_finalText);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityrestorelanguage'), htmlspecialchars($_languageCache[$_languageID]['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
        }

        $this->Load->Restore($_languageID);

        return true;
    }

    /**
     * The Language Diagnostics Renderer
     *
     * @author Varun Shoor
     * @param int $_languageID The First Language ID
     * @param int $_comparisonLanguageID The Second Language ID (Comparison)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Diagnostics($_languageID = 0, $_comparisonLanguageID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('diagnostics'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderDiagnostics($_languageID, $_comparisonLanguageID);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Language Diagnostics Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DiagnosticsSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $this->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Diagnostics();

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['languageid']) == '' || trim($_POST['comparelanguageid']) == '' || !isset($_languageCache[$_POST['languageid']]) ||
            !isset($_languageCache[$_POST['comparelanguageid']])) {
            $this->UserInterface->CheckFields('languageid', 'comparelanguageid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Diagnostics();

            return false;
        } elseif ($_SWIFT->Staff->GetPermission('admin_canrunlanguagediagnostics') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Diagnostics($_POST['languageid'], $_POST['comparelanguageid']);

            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_POST['languageid']);
        $_SWIFT_LanguageObject_Comparison = new SWIFT_Language($_POST['comparelanguageid']);

        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded() ||
            !$_SWIFT_LanguageObject_Comparison instanceof SWIFT_Language || !$_SWIFT_LanguageObject_Comparison->GetIsClassLoaded()) {
            return false;
        }

        $_languagePhraseContainer = $_compareLanguagePhraseContainer = array();
        $_phraseMap = $_compareMap = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases
            WHERE languageid = '" . (int)($_POST['languageid']) . "'
            ORDER BY code ASC");
        while ($this->Database->NextRecord()) {
            $_languagePhraseContainer[] = $this->Database->Record;
            $_phraseMap[$this->Database->Record['code']] = $this->Database->Record;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases
            WHERE languageid = '" . (int)($_POST['comparelanguageid']) . "'
            ORDER BY code ASC");
        while ($this->Database->NextRecord()) {
            $_compareLanguagePhraseContainer[] = $this->Database->Record;
            $_compareMap[$this->Database->Record['code']] = $this->Database->Record;
        }

        $_totalCount = count($_languagePhraseContainer) + count($_compareLanguagePhraseContainer);

        $_languagePhraseResult = $_compareLanguagePhraseResult = array();
        $_languageComparisonContainer = array();

        $_missingCount = 0;

        $_loadedPhraseContainer = array();
        for ($ii = 0; $ii < $_totalCount; $ii++) {
            if (!isset($_languagePhraseContainer[$ii])) {
                continue;
            }

            $_currentPhrase = $_languagePhraseContainer[$ii];

            $_loadedPhraseContainer[] = $_currentPhrase['code'];

            $_phraseStatus = 1;
            $_icon = 'fa-check-circle';
            if (isset($_compareMap[$_currentPhrase['code']])) {
                $_mappedPhrase = $_compareMap[$_currentPhrase['code']];
                $_phraseText = htmlspecialchars($_mappedPhrase['code']);
            } else {
                $_phraseStatus = -1;
                $_icon = 'fa-minus-circle';
                $_phraseText = '<B>' . $this->Language->Get('phrasemissing') . '</B>';

                $_missingCount++;
            }

            $_languageComparisonContainer[] = array('<i class="fa ' . $_icon . '" aria-hidden="true"></i>', htmlspecialchars($_currentPhrase['code']), $_phraseText, $_phraseStatus);

        }

        $_languageMissingContainer = array();
        for ($ii = 0; $ii < $_totalCount; $ii++) {
            if (!isset($_compareLanguagePhraseContainer[$ii])) {
                continue;
            }

            $_currentPhrase = $_compareLanguagePhraseContainer[$ii];

            if (in_array($_currentPhrase['code'], $_loadedPhraseContainer)) {
                continue;
            }

            $_phraseStatus = 1;
            $_icon = 'fa-check-circle';

            if (isset($_phraseMap[$_currentPhrase['code']])) {
                $_mappedPhrase = $_phraseMap[$_currentPhrase['code']];
                $_phraseText = htmlspecialchars($_mappedPhrase['code']);
            } else {
                $_phraseStatus = -1;
                $_icon = 'fa-minus-circle';
                $_phraseText = '<B>' . $this->Language->Get('phrasemissing') . '</B>';
            }

            $_languageMissingContainer[] = array('<i class="fa ' . $_icon . '" aria-hidden="true"></i>', $_phraseText, htmlspecialchars($_currentPhrase['code']), $_phraseStatus);
        }

        if ($_missingCount > 0) {
            $this->UserInterface->Alert(sprintf($this->Language->Get('titlemissingphrases'), $_missingCount), sprintf($this->Language->Get('msgmissingphrases'), $_missingCount));
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('diagnostics'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderDiagnosticsResult($_SWIFT_LanguageObject, $_SWIFT_LanguageObject_Comparison, array_merge($_languageMissingContainer, $_languageComparisonContainer));
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Language ImpEx Manager
     *
     * @author Varun Shoor
     * @param bool $_isImportTabSelected (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function ImpEx($_isImportTabSelected = false)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('importexport'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderImpEx($_isImportTabSelected);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Import the Language
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Import()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->ImpEx();

            return false;
        }

        // END CSRF HASH CHECK

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->ImpEx();

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_canimportphrases') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->ImpEx();

            return false;
        }

        if (isset($_FILES['languagefile']) && file_exists($_FILES['languagefile']['tmp_name'])) {
            // Success.. are we supposed to insert a new language or..?
            if (isset($_POST['importlanguageid']) && !empty($_POST['importlanguageid'])) {
                // Merge
                $_SWIFT_LanguageObject = new SWIFT_Language($_POST['importlanguageid']);
                if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
                    return false;
                }

                $_mergeResult = (string)$this->LanguageManager->Merge($_POST['importlanguageid'], $_FILES['languagefile']['tmp_name'], IIF($_POST['ignoreversion'] == '1', false, true));

                if ($_mergeResult == '-1') {
                    $this->UserInterface->Error($this->Language->Get('titlevcfailed'), $this->Language->Get('msgvcfailed'));
                } elseif ($_mergeResult == false) {
                    $this->UserInterface->Error($this->Language->Get('titlelangimpfailed'), $this->Language->Get('msglangimpfailed'));
                } else {
                    // Confirmation
                    $this->_RenderConfirmation($_SWIFT_LanguageObject, self::CONFIRMATION_MERGE);

                    SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activitymergelanguage'), $_SWIFT_LanguageObject->GetProperty('title')), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
                }
            } else {
                // New Language
                $_languageContainer = $this->LanguageManager->Import($_FILES['languagefile']['tmp_name'], false, false, IIF($_POST['ignoreversion'] == '1', false, true));
                if ($_languageContainer == -1) {
                    $this->UserInterface->Error($this->Language->Get('titlevcfailed'), $this->Language->Get('msgvcfailed'));
                } elseif ($_languageContainer == false) {
                    $this->UserInterface->Error($this->Language->Get('titlelangimpfailed'), $this->Language->Get('msglangimpfailed'));
                } else {
                    // Confirmation
                    $_SWIFT_LanguageObject = new SWIFT_Language($_languageContainer[0]);

                    SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityimportedlanguage'), $_SWIFT_LanguageObject->GetProperty('title')), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                    $this->_RenderConfirmation($_SWIFT_LanguageObject, self::CONFIRMATION_IMPORT);
                }
            }

            $this->Load->ImpEx(true);

            return true;

        } else {
            $this->UserInterface->CheckFields('languagefile');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->ImpEx(true);

            return false;
        }

        return true;
    }

    /**
     * Export the Language
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Export($_languageID)
    {
        $_languageCache = $this->Cache->Get('languagecache');

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || !isset($_languageCache[$_languageID])) {
            return false;
        }

        if (empty($_languageID)) {
            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_canexportphrases') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $this->LanguageManager->Export($_languageID);

        SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityexportedlanguage'), $_languageCache[$_languageID]['title']),
            SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

        return true;
    }

    /**
     * Render the Confirmation Dialogs
     *
     * @author Varun Shoor
     * @param SWIFT_Language $_SWIFT_LanguageObject The SWIFT_Language Object Pointer
     * @param mixed $_confirmationType The Confirmation Type
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _RenderConfirmation(SWIFT_Language $_SWIFT_LanguageObject, $_confirmationType)
    {
        if ($_confirmationType != self::CONFIRMATION_MERGE && $_confirmationType != self::CONFIRMATION_IMPORT) {
            return false;
        } elseif (!$this->GetIsClassLoaded() || !$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        $_finalText = '<b>' . $this->Language->Get('languagetitle') . ':</b> ' . '<img src="' . IIF(!$_SWIFT_LanguageObject->GetProperty('flagicon'), SWIFT::Get('themepath') . 'images/icon_language.gif', str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_SWIFT_LanguageObject->GetProperty('flagicon'))) . '" align="absmiddle" border="0" /> ' . htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title')) . '<br>';

        $_finalText .= '<b>' . $this->Language->Get('authorname') . ':</b> ' . htmlspecialchars($_SWIFT_LanguageObject->GetProperty('author')) . '<br>';
        $_finalText .= '<b>' . $this->Language->Get('textdirection') . ':</b> ' . htmlspecialchars(IIF($_SWIFT_LanguageObject->GetProperty('textdirection') == SWIFT_Language::DIRECTION_RTL, $this->Language->Get('rtl'), $this->Language->Get('ltr'))) . '<br>';
        $_finalText .= '<b>' . $this->Language->Get('isocode') . ':</b> ' . htmlspecialchars($_SWIFT_LanguageObject->GetProperty('languagecode')) . '<br>';
        $_finalText .= '<b>' . $this->Language->Get('languagecharset') . ':</b> ' . htmlspecialchars($_SWIFT_LanguageObject->GetProperty('charset')) . '<br>';
        $_finalText .= '<b>' . $this->Language->Get('displayorder') . ':</b> ' . (int)($_SWIFT_LanguageObject->GetProperty('displayorder')) . '<br>';


        SWIFT::Info(sprintf($this->Language->Get('title' . $_confirmationType . 'lang'), $_SWIFT_LanguageObject->GetProperty('title')), sprintf($this->Language->Get('msg' . $_confirmationType . 'lang'), htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title'))) . '<BR />' . $_finalText);

        return true;
    }
}

?>
