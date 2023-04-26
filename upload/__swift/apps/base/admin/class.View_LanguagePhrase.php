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

use SWIFT;
use Base\Library\Help\SWIFT_Help;
use Base\Models\Language\SWIFT_Language;
use Base\Models\Language\SWIFT_LanguagePhrase;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Language Phrase Management View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_LanguagePhrase $Controller
 * @author Varun Shoor
 */
class View_LanguagePhrase extends SWIFT_View
{
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
     * Render the Phrases
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderPhrases($_languageID = 0, $_compare = 0, $_compareLanguageID = 0, $_search = 0, $_searchType = 'codetext', $_searchQuery = '', $_offset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageID = (int)($_languageID);
        $_compareLanguageID = (int)($_compareLanguageID);

        $_languageFlagIcon = 'fa-flag-o';

        if (empty($_languageID)) {
            $_languageIDList = SWIFT_Language::GetMasterLanguageIDList();
            if (!_is_array($_languageIDList) || empty($_languageIDList[0]) || !is_numeric($_languageIDList[0])) {
                return false;
            }

            $_languageID = $_languageIDList[0];
        }

        $_SWIFT_LanguageObject = new SWIFT_Language($_languageID);
        if (!$_SWIFT_LanguageObject instanceof SWIFT_Language || !$_SWIFT_LanguageObject->GetIsClassLoaded()) {
            return false;
        }

        $_currentLanguageTitle = ': <b>' . htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title')) . '</b>';

        $_languageFlagIconFetch = $_SWIFT_LanguageObject->GetProperty('flagicon');
        if (!empty($_languageFlagIconFetch)) {
            $_languageFlagIcon = str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_languageFlagIconFetch);
        }

        unset($_languageFlagIconFetch);


        $_comparisonLanguageTitle = '';
        $_comparisonFlagIcon = 'fa-flag-o';

        $_SWIFT_LanguageObject_Comparison = null;
        if (!empty($_compareLanguageID)) {
            $_SWIFT_LanguageObject_Comparison = new SWIFT_Language($_compareLanguageID);

            if (!$_SWIFT_LanguageObject_Comparison instanceof SWIFT_Language || !$_SWIFT_LanguageObject_Comparison->GetIsClassLoaded()) {
                return false;
            }

            $_comparisonLanguageTitle = ': <b>' . htmlspecialchars($_SWIFT_LanguageObject_Comparison->GetProperty('title')) . '</b>';

            $_comparisonFlagIconFetch = $_SWIFT_LanguageObject_Comparison->GetProperty('flagicon');
            if (!empty($_comparisonFlagIconFetch)) {
                $_comparisonFlagIcon = str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_comparisonFlagIconFetch);
            }

            unset($_comparisonFlagIconFetch);
        }

        if (empty($_searchQuery)) {
            $_searchQuery = '';
        }

        // Get the current offset for this page
        if (!empty($_offset) && is_numeric($_offset)) {
            $_offset = $_offset;
        } else {
            $_offset = 0;
        }

        $_limit = 10;

        $_fetchQueryExtension = '';
        if ($_search == '1' && trim($_searchType) == 'codetext') {
            // Search both code and text?
            $_countQuery = "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . (int)($_SWIFT_LanguageObject->GetLanguageID()) . "' AND (code LIKE '%" . $this->Database->Escape($_searchQuery) . "%' OR contents LIKE '%" . $this->Database->Escape($_searchQuery) . "%')";
            $_fetchQueryExtension = " AND (code LIKE '%" . $this->Database->Escape($_searchQuery) . "%' OR contents LIKE '%" . $this->Database->Escape($_searchQuery) . "%')";
        } elseif ($_search == '1' && trim($_searchType) == 'code') {
            // Search just the code?
            $_countQuery = "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . (int)($_SWIFT_LanguageObject->GetLanguageID()) . "' AND (code LIKE '%" . $this->Database->Escape($_searchQuery) . "%')";
            $_fetchQueryExtension = " AND (code LIKE '%" . $this->Database->Escape($_searchQuery) . "%')";
        } else {
            $_countQuery = "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . (int)($_SWIFT_LanguageObject->GetLanguageID()) . "'";
        }

        // Get the total phrase count
        $_totalItemsContainer = $this->Database->QueryFetch($_countQuery);

        // is the offset wrong?
        $_lastOffset = SWIFT_UserInterfaceGrid::GetLastPageOffset($_totalItemsContainer['totalitems'], $_limit);
        if ($_offset > $_lastOffset && $_lastOffset) {
            $_offset = $_lastOffset;
        }

        if ($_offset < 0) {
            $_offset = 0;
        }

        if (empty($_searchQuery)) {
            $_searchQueryDispatch = 0;
        } else {
            $_searchQueryDispatch = urlencode($_searchQuery);
        }

        $_managePhraseURL = '_languageID=' . $_languageID . '/_compareLanguageID=' . $_compareLanguageID . '/_search=' . $_search . '/_searchType=' . $_searchType . '/_searchQuery=' . $_searchQueryDispatch . '/_offset=' . $_offset;
        $this->RenderMenu('languagedropdowndef', $_languageID, false, $_compareLanguageID, $_searchType, $_search, $_searchQuery, $_offset);
        $this->RenderMenu('languagedropdowncompare', $_languageID, true, $_compareLanguageID, $_searchType, $_search, $_searchQuery, $_offset);

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this), '/Base/LanguagePhrase/EditSubmit/' . $_managePhraseURL, SWIFT_UserInterface::MODE_EDIT, false);

        $_PhrasesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabphrases') . ': ' . htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title')), 'icon_form.gif', 'phrases', true);

        if ($_SWIFT->Staff->GetPermission('admin_canupdatephrase') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        }

        if ($_SWIFT->Staff->GetPermission('admin_caninsertphrase') != '0') {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertphrase'), 'fa-plus-circle', '/Base/LanguagePhrase/Insert/' . $_managePhraseURL, SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('search'), 'fa-search', '/Base/LanguagePhrase/Search/', SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('languagemen') . $_currentLanguageTitle . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', $_languageFlagIcon, 'UIDropDown(\'languagedropdowndef\', event, \'originalLanguageButton\', \'tabtoolbartable\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'originalLanguageButton', '', false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('compare') . $_comparisonLanguageTitle . ' <img src="' . SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', $_comparisonFlagIcon, 'UIDropDown(\'languagedropdowncompare\', event, \'compareLanguageButton\', \'tabtoolbartable\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'compareLanguageButton', '', false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languagephrase'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        /**
         * Begin Output
         */

        $_paginationData = SWIFT_UserInterfaceGrid::RenderPagination('javascript: loadViewportData("' . SWIFT::Get('basename') . '/Base/LanguagePhrase/Manage/_languageID=' . $_languageID . '/_compare=' . $_compare . '/_compareLanguageID=' . $_compareLanguageID . '/_search=' . $_search . '/_searchType=codetext/_searchQuery=' . $_searchQueryDispatch . '/_offset=', $_totalItemsContainer['totalitems'], $_limit, $_offset, '5', 'pageoftotal', TRUE, FALSE, TRUE);

        $_PhrasesTabObject->PrependHTML('<table width="100%" border="0" cellspacing="0" cellpadding="0">' . '<tr><td class="settabletitlerowmain2">' . IIF($_paginationData, '<table border="0" cellpadding="0" cellspacing="1" class="retborder"><tr>' . $_paginationData . '</tr></table>', '&nbsp;') . '</td></tr></table>' . SWIFT_CRLF);

        // Fetch all the phrases
        $_languagePhraseContainer = $_languagePhraseCodeList = $_finalLanguagePhraseCodeList = $_masterLanguagePhraseDefaultContainer = $_masterLanguagePhraseContainer = array();
        $this->Database->Query("SELECT code FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . (int)($_SWIFT_LanguageObject->GetLanguageID()) . "'" . $_fetchQueryExtension . " ORDER BY phraseid");
        while ($this->Database->NextRecord()) {
            $_languagePhraseCodeList[] = $this->Database->Record['code'];
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE languageid = '" . (int)($_SWIFT_LanguageObject->GetLanguageID()) . "' AND code IN (" . BuildIN($_languagePhraseCodeList) . ") ORDER BY code ASC", $_limit, $_offset);
        while ($this->Database->NextRecord()) {
            $_languagePhraseContainer[$this->Database->Record['code']] = $this->Database->Record;

            $_finalLanguagePhraseCodeList[] = $this->Database->Record['code'];

            if (!$_SWIFT_LanguageObject_Comparison instanceof SWIFT_Language || !$_SWIFT_LanguageObject_Comparison->GetIsClassLoaded()) {
                $_masterLanguagePhraseDefaultContainer[$this->Database->Record['code']] = $this->Database->Record['contentsdefault'];
            }
        }

        // Now fetch the same variables from master language
        if ($_SWIFT_LanguageObject_Comparison instanceof SWIFT_Language && $_SWIFT_LanguageObject_Comparison->GetIsClassLoaded()) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE code IN (" . BuildIN($_finalLanguagePhraseCodeList) . ") AND languageid = '" . $_compareLanguageID . "'" . $_fetchQueryExtension . " ORDER BY code ASC");
            while ($this->Database->NextRecord()) {
                $_masterLanguagePhraseDefaultContainer[$this->Database->Record['code']] = $this->Database->Record['contents'];
            }
        }

        foreach ($_languagePhraseContainer as $_key => $_val) {
            if ($_val['modified'] == SWIFT_LanguagePhrase::PHRASE_MODIFIED) {
                $_modifiedText = '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatemodified.gif" border="0" align="absmiddle" />&nbsp;' . $this->Language->Get('modified');
            } elseif ($_val['modified'] == SWIFT_LanguagePhrase::PHRASE_REVERTREQUIRED) {
                $_modifiedText = '<img src="' . SWIFT::Get('themepath') . 'images/icon_templateupgrade.gif" border="0" align="absmiddle" />&nbsp;' . $this->Language->Get('upgraderevert');
            } else {
                $_modifiedText = '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatenotmodified.gif" border="0" align="absmiddle" />&nbsp;' . $this->Language->Get('notmodified');
            }

            $_value = '<div><textarea name="phrasedisabled[' . $_key . ']" rows="2" cols="50" class="swifttextdisabled" disabled>';

            if (!isset($_masterLanguagePhraseDefaultContainer[$_key]) || empty($_masterLanguagePhraseDefaultContainer[$_key])) {
                $_value .= $this->Language->Get('novalue');
            } else {
                $_value .= htmlspecialchars($_masterLanguagePhraseDefaultContainer[$_key]);
            }

            $_value .= '</textarea></div><div><textarea name="phrase[' . $_key . ']" rows="2" cols="50" class="swifttextarea">' . htmlspecialchars($_val['contents']) . '</textarea></div>';

            $_PhrasesTabObject->DefaultDescriptionRow(htmlspecialchars($_key) . IIF($_SWIFT->Staff->GetPermission('admin_candeletephrase') != '0' && empty($_val['ismaster']), '&nbsp;<a href="javascript: void(0);" onClick="javascript:doConfirm(\'' . $this->Language->Get('phrasedeletepopup') . '\', \'' . SWIFT::Get('basename') . '/Base/LanguagePhrase/Delete/' . $_managePhraseURL . '/_languagePhraseID=' . (int)($_val['phraseid']) . '\');" title="' . $this->Language->Get('deletephrase') . '"><i class="fa fa-trash" aria-hidden="true"></i></a>') . '<BR /><BR /><BR /><BR /><BR />' . $_modifiedText, '', $_value);
        }

        $this->UserInterface->End();

    }

    /**
     * Render the Insert Phrase Form
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @param int $_compareLanguageID The Comparison Language ID
     * @param int $_search Whether to Run Search
     * @param string $_searchType The Search Type
     * @param string $_searchQuery The Search Query
     * @param int $_offset The Search Offset
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_languageID = 0, $_compareLanguageID = 0, $_search = 0, $_searchType = 'codetext', $_searchQuery = '', $_offset = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageCache = $this->Cache->Get('languagecache');

        $_extendedURL = '_languageID=' . $_languageID . '/_compareLanguageID=' . $_compareLanguageID . '/_search=' . $_search . '/_searchType=' . urlencode($_searchType) . '/_searchQuery=' . urlencode($_searchQuery) . '/_offset=' . $_offset;

        $this->UserInterface->Start(get_short_class($this), '/Base/LanguagePhrase/InsertSubmit/' . $_extendedURL, SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');

        if (!empty($_languageID)) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/LanguagePhrase/Manage/' . $_extendedURL, SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languagephrase'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        if (!empty($_languageID) && isset($_languageCache[$_languageID])) {
            $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('phraselanguage'), $this->Language->Get('desc_phraselanguage'), htmlspecialchars($_languageCache[$_languageID]['title']));
            $this->UserInterface->Hidden('languageid', $_languageID);
        } else {
            $_optionsContainer = array();
            $_index = 0;
            foreach ($_languageCache as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $_val['title'];
                $_optionsContainer[$_index]['value'] = $_val['languageid'];

                if ((empty($_languageID) && $_index == 0) || (!empty($_languageID) && $_languageID == $_val['languageid'])) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_GeneralTabObject->Select('languageid', $this->Language->Get('phraselanguage'), $this->Language->Get('desc_phraselanguage'), $_optionsContainer);
        }

        if (!isset($_POST['section'])) {
            $_phraseSection = 'default';
        } else {
            $_phraseSection = $_POST['section'];
        }

        $_phraseCode = '';
        if (isset($_POST['phrasecode'])) {
            $_phraseCode = $_POST['phrasecode'];
        }

        $_phraseValue = '';
        if (isset($_POST['phrasevalue'])) {
            $_phraseValue = $_POST['phrasevalue'];
        }

        $_GeneralTabObject->Text('section', $this->Language->Get('phrasesection'), $this->Language->Get('desc_phrasesection'), $_phraseSection);

        $_GeneralTabObject->Text('phrasecode', $this->Language->Get('code'), $this->Language->Get('desc_phrasecode'), $_phraseCode);

        $_GeneralTabObject->TextArea('phrasevalue', $this->Language->Get('value'), $this->Language->Get('desc_phrasevalue'), $_phraseValue, 50, 2);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Search Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderSearch()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageCache = $this->Cache->Get('languagecache');

        $this->UserInterface->Start(get_short_class($this), '/Base/LanguagePhrase/SearchSubmit/', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsearch'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('search'), 'fa-search');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languagesearchphrase'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_searchQuery = '';
        if (isset($_POST['query'])) {
            $_searchQuery = $_POST['query'];
        }

        $_GeneralTabObject->Text('query', $this->Language->Get('query'), $this->Language->Get('desc_query'), $_searchQuery);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('codetext');
        $_optionsContainer[0]['value'] = 'codetext';
        $_optionsContainer[1]['title'] = $this->Language->Get('code');
        $_optionsContainer[1]['value'] = 'code';

        $_GeneralTabObject->Select('type', $this->Language->Get('searchtype'), $this->Language->Get('desc_searchtype'), $_optionsContainer);

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_languageCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['languageid'];
            $_index++;
        }

        $_GeneralTabObject->Select('languageid', $this->Language->Get('searchlanguage'), $this->Language->Get('desc_searchlanguage'), $_optionsContainer);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Menu
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderMenu($_menuName, $_languageID, $_isCompare = false, $_compareLanguageID = false, $_searchType = '', $_search = '', $_searchQuery = '', $_offset = '')
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (empty($_compareLanguageID) && !$_isCompare) {
            $_compareLanguageID = $_languageID;
        }

        echo '<ul class="swiftdropdown" id="' . $_menuName . '">';

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languages ORDER BY title ASC;");
        while ($this->Database->NextRecord()) {
            if ($_isCompare) {
                echo '<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData(\'' . SWIFT::Get('basename') . '/Base/LanguagePhrase/Manage/_languageID=' . (int)($_languageID) . '/_compareLanguageID=' . (int)($this->Database->Record['languageid']) . '/_search=' . (int)($_search) . '/_searchType=' . $_searchType . '/_searchQuery=' . $_searchQuery . '/_offset=' . $_offset . '\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_language.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . htmlspecialchars($this->Database->Record['title']) . '</div></div></li>';
            } else {
                echo '<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData(\'' . SWIFT::Get('basename') . '/Base/LanguagePhrase/Manage/_languageID=' . (int)($this->Database->Record['languageid']) . '/_compareLanguageID=' . (int)($_compareLanguageID) . '/_search=' . (int)($_search) . '/_searchType=' . $_searchType . '/_searchQuery=' . $_searchQuery . '/_offset=' . $_offset . '\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_language.gif' . '" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . htmlspecialchars($this->Database->Record['title']) . '</div></div></li>';
            }
        }

        echo '<li class="seperator"></li>';

        echo '<li class="swiftdropdowninput"><div class="swiftdropdownitem"><div class="swiftdropdowniteminput"><img src="' . SWIFT::Get('themepath') . 'images/icon_doublearrows.gif' . '" align="absmiddle" border="0" /> ' . $this->Language->Get('squicksearch') . '<BR /><input style="margin-top: 4px; width: 180px;" type="text" onkeypress="javascript: return HandleLanguageQuickSearchKeyPress(' . (int)($_isCompare) . ', event);" name="languageqs_' . (int)($_isCompare) . '_query" class="swifttext" id="languageqs_' . (int)($_isCompare) . '_query" /><input type="hidden" id="languageqs_' . (int)($_isCompare) . '_comparelanguageid" name="languageqs_' . (int)($_isCompare) . '_comparelanguageid" value="' . IIF($_isCompare == 1, (int)($_languageID), (int)($_compareLanguageID)) . '" /><input type="hidden" id="languageqs_' . (int)($_isCompare) . '_type" name="languageqs_' . (int)($_isCompare) . '_type" value="codetext" /><input type="hidden" id="languageqs_' . (int)($_isCompare) . '_languageid" name="languageqs_' . (int)($_isCompare) . '_languageid" value="' . IIF($_isCompare == 1, (int)($_compareLanguageID), (int)($_languageID)) . '" /></div></div></li>';

        echo '</ul>';

        return true;
    }
}

?>
