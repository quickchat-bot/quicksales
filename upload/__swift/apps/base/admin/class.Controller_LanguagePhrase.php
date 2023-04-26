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

use Controller_admin;
use SWIFT;
use Base\Models\Language\SWIFT_Language;
use Base\Models\Language\SWIFT_LanguagePhrase;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The Language Phrase Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_LanguagePhrase $View
 * @author Varun Shoor
 */
class Controller_LanguagePhrase extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 1;

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
     * Displays the Language Phrase List
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function Manage($_argumentContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageID = $_compare = $_compareLanguageID = $_search = $_offset = 0;
        $_searchType = 'codetext';
        $_searchQuery = '';

        if (_is_array($_argumentContainer)) {
            extract($_argumentContainer);
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('managephrases'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canviewphrases') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderPhrases($_languageID, $_compare, $_compareLanguageID, $_search, $_searchType, $_searchQuery, $_offset);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Phrase Updating Processing Function
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function EditSubmit($_argumentContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($_argumentContainer) || !isset($_argumentContainer['_languageID']) || empty($_argumentContainer['_languageID'])) {
            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_argumentContainer['_languageID']);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Manage($_argumentContainer);

            return false;
        }

        // END CSRF HASH CHECK

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Manage($_argumentContainer);

            return false;
        }

        // ======= Is an update required? =======
        if (isset($_POST['phrase']) && is_array($_POST['phrase']) && count($_POST['phrase'])) {
            if ($_SWIFT->Staff->GetPermission('admin_canupdatephrase') == '0') {
                $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

                $this->Load->Manage($_argumentContainer);

                return false;
            } else {
                $_SWIFT_LanguageObject->UpdatePhraseList($_POST['phrase']);

                SWIFT_StaffActivityLog::AddToLog($this->Language->Get('activityupdatephrases'), SWIFT_StaffActivityLog::ACTION_UPDATE,
                    SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

                $this->UserInterface->Info($this->Language->Get('titleupdatedlangphrases'), sprintf($this->Language->Get('msgupdatedlangphrases'),
                    htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title'))));

                $this->Load->Manage($_argumentContainer);

                return true;
            }
        }
    }

    /**
     * Delete a Phrase
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete($_argumentContainer = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($_argumentContainer) || !isset($_argumentContainer['_languageID']) || empty($_argumentContainer['_languageID']) ||
            !isset($_argumentContainer['_languagePhraseID']) || empty($_argumentContainer['_languagePhraseID'])) {
            return false;
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_argumentContainer['_languageID']);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Manage($_argumentContainer);

            return false;
        } elseif ($_SWIFT->Staff->GetPermission('admin_candeletephrase') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_argumentContainer);

            return false;
        }

        $_SWIFT_LanguagePhraseObject = new SWIFT_LanguagePhrase($_argumentContainer['_languagePhraseID']);
        if (!$_SWIFT_LanguagePhraseObject instanceof SWIFT_LanguagePhrase || !$_SWIFT_LanguagePhraseObject->GetIsClassLoaded()) {
            return false;
        }

        if ($_SWIFT_LanguagePhraseObject->GetProperty('ismaster') == '1') {
            return false;
        }

        $_phraseCode = $_SWIFT_LanguagePhraseObject->Delete();

        SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activitydeletephrase'), $_phraseCode),
            SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

        $this->UserInterface->Info($this->Language->Get('titlephrasedel'), sprintf($this->Language->Get('msgphrasedel'), htmlspecialchars($_phraseCode)));

        $this->Load->Manage($_argumentContainer);

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function RunChecks()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_languageCache = $this->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['phrasecode']) == '' || trim($_POST['phrasevalue']) == '' || trim($_POST['languageid']) == '' ||
            !isset($_languageCache[$_POST['languageid']])) {
            $this->UserInterface->CheckFields('phrasecode', 'phrasevalue', 'languageid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        } elseif ($_SWIFT->Staff->GetPermission('admin_caninsertphrase') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Language
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function Insert($_argumentContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageID = $_compareLanguageID = $_search = $_offset = 0;
        $_searchType = 'codetext';
        $_searchQuery = '';

        if (_is_array($_argumentContainer)) {
            extract($_argumentContainer);
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('insertphrase'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->Render($_languageID, $_compareLanguageID, $_search, $_searchType, $_searchQuery, $_offset);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertSubmit($_argumentContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageCache = $this->Cache->Get('languagecache');

        if ($this->RunChecks()) {
            $_languagePhraseID = SWIFT_LanguagePhrase::Create($_POST['languageid'], $_POST['section'], $_POST['phrasecode'], $_POST['phrasevalue'], APP_BASE);
            if (!$_languagePhraseID) {
                return false;
            }

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertphrase'), $_POST['phrasecode']),
                SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_LANGUAGE, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            $this->UserInterface->Info(sprintf($this->Language->Get('titlephraseinsert'), htmlspecialchars($_POST['phrasecode'])),
                sprintf($this->Language->Get('msgphraseinsert'), htmlspecialchars($_POST['phrasecode']),
                    htmlspecialchars($_languageCache[$_POST['languageid']]['title']), htmlspecialchars($_POST['phrasecode']),
                    htmlspecialchars($_POST['section']), htmlspecialchars($_POST['phrasevalue'])));

            $_argumentContainer['_languageID'] = $_POST['languageid'];

            $this->Load->Manage($_argumentContainer);

            return true;
        }

        $this->Load->Insert($_argumentContainer);

        return false;
    }

    /**
     * Search the Languages
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('languages') . ' > ' . $this->Language->Get('searchphrases'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderSearch();
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Process the Search Submission
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function SearchSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageCache = $this->Cache->Get('languagecache');

        if (!isset($_POST['comparelanguageid']) && isset($_POST['languageid']) && empty($_POST['languageid']) && !empty($_POST['comparelanguageid'])) {
            $_POST['languageid'] = (int)($_POST['comparelanguageid']);
        }

        if (trim($_POST['query']) == '' || trim($_POST['type']) == '' || trim($_POST['languageid']) == '' || !isset($_languageCache[$_POST['languageid']])) {
            $this->UserInterface->CheckFields('query', 'type', 'languageid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Search();

            return false;
        } elseif ($_SWIFT->Staff->GetPermission('admin_canviewphrases') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Search();

            return false;
        }

        $_compareLanguageID = 0;
        if (isset($_POST['comparelanguageid'])) {
            $_compareLanguageID = (int)($_POST['comparelanguageid']);
        }
        $this->Load->Manage(array('_languageID' => (int)($_POST['languageid']), '_search' => 1, '_searchType' => $_POST['type'],
            '_searchQuery' => CleanURL($_POST['query']), '_compareLanguageID' => $_compareLanguageID));

        return true;
    }
}

?>
