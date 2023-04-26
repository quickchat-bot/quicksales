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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use Base\Models\Language\SWIFT_Language;
use Base\Models\Language\SWIFT_LanguagePhrase;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Language Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Language $View
 * @author Varun Shoor
 */
class Controller_Language extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 1;

    const CONFIRMATION_UPDATE = 'update';
    const CONFIRMATION_INSERT = 'insert';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('languages');
    }

    /**
     * Resort the Languages
     *
     * @author Varun Shoor
     * @param mixed $_languageIDSortList The Language ID Sort List Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function SortList($_languageIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_canupdatelanguage') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        SWIFT_Language::UpdateDisplayOrderList($_languageIDSortList);

        return true;
    }

    /**
     * Delete the Languages from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_languageIDList The Language ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_languageIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_candeletelanguage') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_languageIDList)) {
            $_SWIFT->Database->Query("SELECT title FROM " . TABLE_PREFIX . "languages WHERE languageid IN (" . BuildIN($_languageIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletelanguage'), $_SWIFT->Database->Record['title']),
                    SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_Language::DeleteList($_languageIDList);
        }

        SWIFT_Language::RebuildCache();

        return true;
    }

    /**
     * Delete the Given Language ID
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete($_languageID)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        self::DeleteList(array($_languageID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Language Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('manage'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewlanguages') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     */
    protected function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['title']) == '' || trim($_POST['author']) == '' || trim($_POST['languagecode']) == '' || trim($_POST['charset']) == '') {
            $this->UserInterface->CheckFields('title', 'author', 'languagecode', 'charset');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertlanguage') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_canupdatelanguage') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Language
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('insert'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_caninsertlanguage') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertSubmit()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_POST['displayorder'] = (int)($_POST['displayorder']);
            if (empty($_POST['displayorder'])) {
                $_POST['displayorder'] = 1;
            }

            if (!isset($_POST['isenabled'])) {
                $_POST['isenabled'] = 1;
            }

            // check for existing language code
            $_list = SWIFT_Language::GetAvailableLanguageList();
            foreach ($_list as $_lang) {
                if ($_lang['languagecode'] === $_POST['languagecode']) {
                    $this->UserInterface->Error($this->Language->Get('invalidlanguagecode'), $this->Language->Get('invalidlanguagecodedesc'));

                    $this->Load->Insert();

                    return false;
                }
            }

            $_languageID = SWIFT_Language::Create($_POST['title'], $_POST['languagecode'], $_POST['charset'], $_POST['author'],
                $_POST['textdirection'], $_POST['displayorder'], $this->Input->SanitizeForXSS($_POST['flagicon']), $_POST['isenabled']);
            $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
            if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
                return false;
            }

            $_SWIFT_LanguageObject->ImportPhraseFromMaster();

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertlanguage'), $_POST['title']),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            $this->_RenderConfirmation($_SWIFT_LanguageObject, self::CONFIRMATION_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Language
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Edit($_languageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || empty($_languageID)) {
            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('edit'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatelanguage') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_LanguageObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function EditSubmit($_languageID)
    {
        if (!$this->GetIsClassLoaded() || empty($_languageID)) {
            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            if (!isset($_POST['isenabled'])) {
                $_POST['isenabled'] = 1;
            }

            // check for existing language code
            if ($_SWIFT_LanguageObject->Get('languagecode') !== $_POST['languagecode']) {
                $_list = SWIFT_Language::GetAvailableLanguageList();
                foreach ($_list as $_lang) {
                    if ($_lang['languagecode'] === $_POST['languagecode']) {
                        $this->UserInterface->Error($this->Language->Get('invalidlanguagecode'),
                            $this->Language->Get('invalidlanguagecodedesc'));

                        $this->Load->Edit($_languageID);

                        return false;
                    }
                }
            }

            $_updateResult = $_SWIFT_LanguageObject->Update($_POST['title'], $_POST['languagecode'], $_POST['charset'], $_POST['author'],
                $_POST['textdirection'], $_POST['displayorder'], $this->Input->SanitizeForXSS($_POST['flagicon']), $_POST['isenabled']);
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatelanguage'), $_POST['title']),
                SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult) {
                return false;
            }

            $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
            $this->_RenderConfirmation($_SWIFT_LanguageObject, self::CONFIRMATION_UPDATE);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_languageID);

        return false;
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
        if ($_confirmationType != self::CONFIRMATION_UPDATE && $_confirmationType != self::CONFIRMATION_INSERT) {
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

        SWIFT::Info(sprintf($this->Language->Get('title' . $_confirmationType . 'lang'), htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title'))), sprintf($this->Language->Get('msg' . $_confirmationType . 'lang'), htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title'))) . '<br />' . $_finalText);

        return true;
    }

    /**
     * Edit Restore Processor
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function Restore($_languageID)
    {
        $_SWIFT = SWIFT::GetInstance();
        $_languageCache = $_SWIFT->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded() || empty($_languageID) || !isset($_languageCache[$_languageID])) {
            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_canrestorephrases') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage();

            return false;
        }

        SWIFT_LanguagePhrase::Restore($_languageID);

        $this->UserInterface->Info($this->Language->Get('titlerestorephrase'), sprintf($this->Language->Get('msgrestorephrase'),
            htmlspecialchars($_languageCache[$_languageID]['title'])));

        SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityrestorelanguage'),
            htmlspecialchars($_languageCache[$_languageID]['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
            SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

        $this->Load->Manage();

        return false;
    }
}

?>
