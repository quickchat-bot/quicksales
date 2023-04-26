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

namespace Base\Admin;

use SWIFT;
use Base\Library\Help\SWIFT_Help;
use Base\Models\Language\SWIFT_Language;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Language View Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Language extends SWIFT_View
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
     * Render the Language Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Language $_SWIFT_LanguageObject The SWIFT_Language Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_Language $_SWIFT_LanguageObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/Language/EditSubmit/' . $_SWIFT_LanguageObject->GetLanguageID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/Language/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_languageDirection = SWIFT_Language::DIRECTION_LTR;
        $_languageTitle = $_languageAuthor = $_languageFlagIcon = '';
        $_languageDisplayOrder = 0;
        $_languageIsEnabled = true;
        $_languageIsEnabledDisabled = false;

        $_languageCode = 'en-us';
        $_languageCharset = 'UTF-8';

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('phrases'), 'fa-align-left', '/Base/LanguagePhrase/Manage/_languageID=' . $_SWIFT_LanguageObject->GetLanguageID(), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('restorelanguage'), 'fa-rotate-left', '/Base/Language/Restore/' . $_SWIFT_LanguageObject->GetLanguageID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/Language/Delete/' . $_SWIFT_LanguageObject->GetLanguageID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('language'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_languageDirection = $_SWIFT_LanguageObject->GetProperty('textdirection');
            $_languageTitle = $_SWIFT_LanguageObject->GetProperty('title');
            $_languageAuthor = $_SWIFT_LanguageObject->GetProperty('author');
            $_languageCode = $_SWIFT_LanguageObject->GetProperty('languagecode');
            $_languageCharset = $_SWIFT_LanguageObject->GetProperty('charset');
            $_languageFlagIcon = $_SWIFT_LanguageObject->GetProperty('flagicon');
            $_languageDisplayOrder = (int)($_SWIFT_LanguageObject->GetProperty('displayorder'));
            $_languageIsEnabled = (int)($_SWIFT_LanguageObject->GetProperty('isenabled'));

            if ($_SWIFT_LanguageObject->GetProperty('ismaster')) {
                $_languageIsEnabled = true;
                $_languageIsEnabledDisabled = true;
            }
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('language'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_languageDisplayOrder = SWIFT_Language::GetLastDisplayOrder();
        }

        $_GeneralTabObject->Text('title', $this->Language->Get('languagetitle'), $this->Language->Get('desc_languagetitle'), $_languageTitle);

        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('languageisenabled'), $this->Language->Get('desc_languageisenabled'), $_languageIsEnabled, '', '', $_languageIsEnabledDisabled);

        $_GeneralTabObject->Text('author', $this->Language->Get('authorname'), $this->Language->Get('desc_authorname'), $_languageAuthor);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('ltr');
        $_optionsContainer[0]['value'] = SWIFT_Language::DIRECTION_LTR;
        if ($_languageDirection == SWIFT_Language::DIRECTION_LTR) {
            $_optionsContainer[0]['selected'] = true;
        }

        $_optionsContainer[1]['title'] = $this->Language->Get('rtl');
        $_optionsContainer[1]['value'] = SWIFT_Language::DIRECTION_RTL;
        if ($_languageDirection == SWIFT_Language::DIRECTION_RTL) {
            $_optionsContainer[1]['selected'] = true;
        }

        $_GeneralTabObject->Select('textdirection', $this->Language->Get('textdirection'), $this->Language->Get('desc_textdirection'), $_optionsContainer);

        // Master language pack?
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_LanguageObject->GetProperty('ismaster') == '1') {
            $this->UserInterface->Hidden('languagecode', $_languageCode);
        } else {
            $_GeneralTabObject->Text('languagecode', $this->Language->Get('isocode'), $this->Language->Get('desc_isocode'), $_languageCode);
        }

        $_GeneralTabObject->Text('charset', $this->Language->Get('languagecharset'), $this->Language->Get('desc_languagecharset'), $_languageCharset);

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_languageDisplayOrder);

        $_GeneralTabObject->Text('flagicon', $this->Language->Get('flagicon'), $this->Language->Get('desc_flagicon'), $_languageFlagIcon);

        $this->UserInterface->End();
    }

    /**
     * Render the Visitor Ban Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('languagegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'languages WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('languagecode') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('charset') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('author') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'languages WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('languagecode') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('charset') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('author') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'languages', 'SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'languages');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('languageid', 'languageid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('languagetitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('textdirection', $this->Language->Get('textdirection'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('languagecode', $this->Language->Get('isocode'), SWIFT_UserInterfaceGridField::TYPE_DB, 100, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('charset', $this->Language->Get('languagecharset'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('order'), SWIFT_UserInterfaceGridField::TYPE_DB, 70, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Admin\Controller_Language', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/Language/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_canupdatelanguage') != '0') {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Base\Admin\Controller_Language', 'SortList'));
        }

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return mixed "_fieldContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['isdefault'] = IIF($_fieldContainer['isdefault'] == 1, $_SWIFT->Language->Get('yes'), $_SWIFT->Language->Get('no'));
        $_fieldContainer['textdirection'] = IIF($_fieldContainer['textdirection'] == SWIFT_Language::DIRECTION_RTL, $_SWIFT->Language->Get('rtl'), $_SWIFT->Language->Get('ltr'));
        $_fieldContainer['icon'] = '<img src="' . IIF(empty($_fieldContainer['flagicon']), SWIFT::Get('themepath') . 'images/icon_language.gif', str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_fieldContainer['flagicon'])) . '" align="absmiddle" border="0" />';

        $_fieldContainer['displayorder'] = (int)($_fieldContainer['displayorder']);
        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . '/Base/Language/Edit/' . (int)($_fieldContainer['languageid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        return $_fieldContainer;
    }
}

?>
