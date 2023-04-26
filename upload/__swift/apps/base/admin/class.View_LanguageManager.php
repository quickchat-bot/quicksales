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
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The View Language Manager Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_LanguageManager extends SWIFT_View
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
     * Renders the ImpEx Form
     *
     * @author Varun Shoor
     * @param bool $_isImportTabSelected (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderImpEx($_isImportTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_isExportTabSelected = true;
        if ($_isImportTabSelected) {
            $_isExportTabSelected = false;
        }

        $_languageCache = $this->Cache->Get('languagecache');

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this), '/Base/LanguageManager/Import', SWIFT_UserInterface::MODE_EDIT, false, true);

        $_ExportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabexport'), 'icon_export.gif', 'export', $_isExportTabSelected);
        $_ImportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabimport'), 'icon_import.gif', 'import', $_isImportTabSelected);

        $_ExportTabObject->LoadToolbar();
        if ($_SWIFT->Staff->GetPermission('admin_canexportphrases') != '0') {
            $_ExportTabObject->Toolbar->AddButton($this->Language->Get('export'), 'fa-check-circle', 'PopupSmallWindow(\'' . SWIFT::Get('basename') . '/Base/LanguageManager/Export/\'+$(\'#selectexportlanguageid\').val());', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $_ExportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languageimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_index = 0;
        $_optionsContainer = array();
        foreach ($_languageCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['languageid'];
            $_index++;
        }

        $_ExportTabObject->Select('exportlanguageid', $this->Language->Get('explanguage'), $this->Language->Get('desc_explanguage'), $_optionsContainer);


        $_ImportTabObject->LoadToolbar();
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('import'), 'fa-check-circle', 'TabLoading(\'' . get_short_class($this) . '\', \'' . 'import' . '\'); $(\'#' . get_short_class($this) . SWIFT_UserInterface::FORM_SUFFIX . '\').submit();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languageimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_ImportTabObject->File('languagefile', $this->Language->Get('languagefile'), $this->Language->Get('desc_languagefile'));

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('createnewlanguage');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;
        $_index = 1;

        foreach ($_languageCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['languageid'];
            $_index++;
        }

        $_ImportTabObject->Select('importlanguageid', $this->Language->Get('mergewith'), $this->Language->Get('desc_mergewith'), $_optionsContainer);

        $_ImportTabObject->YesNo('ignoreversion', $this->Language->Get('ignoreversion'), $this->Language->Get('desc_ignoreversion'), false);

        $this->UserInterface->Hidden('isajax', '1');

        $this->UserInterface->End();

        return true;
    }

    /**
     * Renders the Diagnostics Form
     *
     * @author Varun Shoor
     * @param int $_languageID The First Language ID
     * @param int $_comparisonLanguageID The Second Language ID (Comparison)
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderDiagnostics($_languageID = 0, $_comparisonLanguageID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_languageCache = $this->Cache->Get('languagecache');

        $this->UserInterface->Start(get_short_class($this), '/Base/LanguageManager/DiagnosticsSubmit/', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabmissingphrases'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('compare'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languagediagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_languageCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['languageid'];
            if ((empty($_languageID) && $_val['ismaster'] == 1) || (!empty($_languageID) && $_languageID == $_val['languageid'])) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('languageid', $this->Language->Get('diagnosticslang1'), $this->Language->Get('desc_diagnosticslang1'), $_optionsContainer);

        $_optionsContainer = array();
        $index = 0;
        foreach ($_languageCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['languageid'];

            if ((empty($_comparisonLanguageID) && $_index == 0) || (!empty($_comparisonLanguageID) && $_comparisonLanguageID == $_val['languageid'])) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('comparelanguageid', $this->Language->Get('diagnosticslang2'), $this->Language->Get('desc_diagnosticslang2'), $_optionsContainer);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Diagnostics Result
     *
     * @author Varun Shoor
     * @param SWIFT_Language $_SWIFT_LanguageObject The First Language Object
     * @param SWIFT_Language $_SWIFT_LanguageObject_Comparison The Second Language Object (to compare with)
     * @param array $_languageComparisonContainer The Comparison Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderDiagnosticsResult(SWIFT_Language $_SWIFT_LanguageObject, SWIFT_Language $_SWIFT_LanguageObject_Comparison, $_languageComparisonContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/LanguageManager/Diagnostics/', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabmissingphrases'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/LanguageManager/Diagnostics/' . (int)($_SWIFT_LanguageObject->GetLanguageID()) . '/' . (int)($_SWIFT_LanguageObject_Comparison->GetLanguageID()), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languagediagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_optionsContainer = array();
        $_optionsContainer[0]['align'] = 'center';
        $_optionsContainer[0]['nowrap'] = true;
        $_optionsContainer[0]['value'] = '&nbsp;';
        $_optionsContainer[0]['width'] = 16;

        $_optionsContainer[1]['width'] = '';
        $_optionsContainer[1]['value'] = '<b>' . htmlspecialchars($_SWIFT_LanguageObject->GetProperty('title')) . '</b>';
        $_optionsContainer[1]['align'] = 'left';

        $_optionsContainer[2]['width'] = '';
        $_optionsContainer[2]['value'] = '<b>' . htmlspecialchars($_SWIFT_LanguageObject_Comparison->GetProperty('title')) . '</b>';
        $_optionsContainer[2]['align'] = 'left';
        $_GeneralTabObject->Row($_optionsContainer, 'gridtabletitlerow');


        foreach ($_languageComparisonContainer as $_key => $_val) {
            $_optionsContainer = array();
            $_optionsContainer[0]['align'] = 'center';
            $_optionsContainer[0]['nowrap'] = true;
            $_optionsContainer[0]['value'] = $_val[0];
            $_optionsContainer[0]['width'] = 16;

            $_optionsContainer[1]['width'] = '';
            $_optionsContainer[1]['value'] = $_val[1];
            $_optionsContainer[1]['align'] = 'left';

            $_optionsContainer[2]['width'] = '';
            $_optionsContainer[2]['value'] = $_val[2];
            $_optionsContainer[2]['align'] = 'left';

            $_GeneralTabObject->Row($_optionsContainer, IIF($_val[3] == -1, 'errorrow'));
        }

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Restoration Form
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID to Restore
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderRestore($_languageID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/LanguageManager/RestoreList/', SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabrestorephrases'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('search'), 'fa-search');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languagerestore'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languages ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['languageid'];
            if ((empty($_languageID) && $_index == 0) || (!empty($_languageID) && $_languageID == $this->Database->Record['languageid'])) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('languageid', $this->Language->Get('diagnosticslang1'), $this->Language->Get('desc_diagnosticslang1'), $_optionsContainer);

        $_GeneralTabObject->Title($this->Language->Get('phrasestatus'));

        $_appendHTML = '<tr class="' . $_GeneralTabObject->GetClass() . '"><td><img src="' . SWIFT::Get('themepath') . 'images/icon_templatemodified.gif" border="0" align="absmiddle" />&nbsp;<strong>' . $this->Language->Get('modified') . '</strong></td><td><input type="checkbox" name="modified[]" value="' . SWIFT_LanguagePhrase::PHRASE_MODIFIED . '" checked></td></tr>';
        $_appendHTML .= '<tr class="' . $_GeneralTabObject->GetClass() . '"><td><img src="' . SWIFT::Get('themepath') . 'images/icon_templateupgrade.gif" border="0" align="absmiddle" />&nbsp;<strong>' . $this->Language->Get('upgraderevert') . '</strong></td><td><input type="checkbox" name="modified[]" value="' . SWIFT_LanguagePhrase::PHRASE_REVERTREQUIRED . '" checked></td></tr>';
        $_appendHTML .= '<tr class="' . $_GeneralTabObject->GetClass() . '"><td><img src="' . SWIFT::Get('themepath') . 'images/icon_templatenotmodified.gif" border="0" align="absmiddle" />&nbsp;<strong>' . $this->Language->Get('notmodified') . '</strong></td><td><input type="checkbox" name="modified[]" value="' . SWIFT_LanguagePhrase::PHRASE_NOTMODIFIED . '"></td></tr>';

        $_GeneralTabObject->AppendHTML($_appendHTML);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Restoration Form List
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @param array $_languagePhraseIDList The Language Phrase ID List to Restore
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderRestoreList($_languageID, $_languagePhraseIDList)
    {
        $_languageCache = $this->Cache->Get('languagecache');

        if (!$this->GetIsClassLoaded() || empty($_languageID) || !isset($_languageCache[$_languageID])) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/LanguageManager/RestoreProcess/' . $_languageID, SWIFT_UserInterface::MODE_INSERT, false);

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabrestorephrases'), 'icon_form.gif', 'general', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('restore'), 'fa-rotate-left');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Base/LanguageManager/Restore/' . (int)($_POST['languageid']), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('languagerestore'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_GeneralTabObject->Title(sprintf($this->Language->Get('restorelanguage2'), htmlspecialchars($_languageCache[$_languageID]['title'])), '', 3);

        $_columnContainer = array();
        $_columnContainer[0]['width'] = '20';
        $_columnContainer[0]['value'] = '<input type="checkbox" name="allselect" class="swiftcheckbox" onClick="javascript:toggleAll(\'\', \'View_LanguageManager\');" />';
        $_columnContainer[0]['align'] = 'center';

        $_columnContainer[1]['width'] = '';
        $_columnContainer[1]['value'] = '<b>' . $this->Language->Get('code') . '</b>';
        $_columnContainer[1]['align'] = 'left';

        $_columnContainer[2]['width'] = '200';
        $_columnContainer[2]['value'] = '<b>' . $this->Language->Get('status') . '</b>';
        $_columnContainer[2]['align'] = 'center';

        $_GeneralTabObject->Row($_columnContainer, 'gridtabletitlerow');

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "languagephrases WHERE phraseid IN(" . BuildIN($_languagePhraseIDList) . ") ORDER BY code ASC");
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['modified'] == SWIFT_LanguagePhrase::PHRASE_NOTMODIFIED) {
                $_modifiedStatusIcon = 'icon_templatenotmodified.gif';
                $_modifiedStatusText = $this->Language->Get('notmodified');
            } elseif ($this->Database->Record["modified"] == SWIFT_LanguagePhrase::PHRASE_REVERTREQUIRED) {
                $_modifiedStatusIcon = 'icon_templateupgrade.gif';
                $_modifiedStatusText = $this->Language->Get('upgrade');
            } else {
                $_modifiedStatusIcon = 'icon_templatemodified.gif';
                $_modifiedStatusText = $this->Language->Get('modified');
            }

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'center';
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['value'] = '<input type="checkbox" name="itemid[]" value=' . (int)($this->Database->Record['phraseid']) . ' class=\"swiftcheckbox\" >';
            $_columnContainer[0]['width'] = 20;

            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[1]['nowrap'] = true;
            $_columnContainer[1]['value'] = htmlspecialchars($this->Database->Record['code']);

            $_columnContainer[2]['align'] = 'center';
            $_columnContainer[2]['nowrap'] = true;
            $_columnContainer[2]['value'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_modifiedStatusIcon . '" border="0" align="absmiddle" />&nbsp;' . $_modifiedStatusText;
            $_columnContainer[2]['width'] = 250;

            $_GeneralTabObject->Row($_columnContainer);
        }

        $this->UserInterface->End();

        return true;
    }
}

?>
