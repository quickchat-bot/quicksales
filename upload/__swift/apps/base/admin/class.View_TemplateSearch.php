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
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_StringHighlighter;
use Base\Models\Template\SWIFT_Template;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Template Search View Handler
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_TemplateSearch $Controller
 * @author Varun Shoor
 */
class View_TemplateSearch extends SWIFT_View
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
     * Render the Template Search Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateSearch/RunSearch', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('search'), 'fa-search');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templatesearch'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN SEARCH TAB
         * ###############################################
         */
        $_SearchTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsearch'), 'icon_form.gif', 'general', true);

        $_SearchTabObject->Text('searchquery', $this->Language->Get('query'), $this->Language->Get('desc_query'), '');

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_templateGroupCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['tgroupid'];

            if ($_index == 0) {
                $_optionsContainer[$_index]['selected'] = true;
            }
            $_index++;
        }

        $_SearchTabObject->Select('tgroupid', $this->Language->Get('searchtgroup'), $this->Language->Get('desc_searchtgroup'), $_optionsContainer);
        $_SearchTabObject->Title($this->Language->Get('filter'), 'icon_doublearrows.gif');

        $_templateModified = true;
        $_templateUpgrade = $_templateNotModified = true;
        if (isset($_POST['modified'][SWIFT_Template::TYPE_UPGRADE])) {
            $_templateUpgrade = true;
        }

        if (isset($_POST['modified'][SWIFT_Template::TYPE_NOTMODIFIED])) {
            $_templateNotModified = true;
        }

        $_SearchTabObject->YesNo('modified[' . SWIFT_Template::TYPE_MODIFIED . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatemodified.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('modified') . '</b>', '', $_templateModified);
        $_SearchTabObject->YesNo('modified[' . SWIFT_Template::TYPE_UPGRADE . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templateupgrade.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('upgrade') . '</b>', '', $_templateUpgrade);
        $_SearchTabObject->YesNo('modified[' . SWIFT_Template::TYPE_NOTMODIFIED . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatenotmodified.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('notmodified') . '</b>', '', $_templateNotModified);

        /*
         * ###############################################
         * END SEARCH TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Template Search Result
     *
     * @author Varun Shoor
     * @param array $_templateContainer The Template Container Array with Processed Search Results
     * @param array $_propertiesContainer The Properties Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderResult($_templateContainer, $_propertiesContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        if (!isset($_templateGroupCache[$_propertiesContainer['tgroupid']])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateSearch/Index', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templatesearch'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        // Add hidden fields if we have the POST data (needed for back button to work)
        if (isset($_propertiesContainer['tgroupid'])) {
            $this->UserInterface->Hidden('tgroupid', $_propertiesContainer['tgroupid']);
        }

        if (isset($_propertiesContainer['searchquery'])) {
            $this->UserInterface->Hidden('searchquery', $_propertiesContainer['searchquery']);
        }

        if (isset($_propertiesContainer['modified']) && _is_array($_propertiesContainer['modified'])) {
            foreach ($_propertiesContainer['modified'] as $_key => $_val) {
                if ($_val == 1) {
                    $this->UserInterface->Hidden('modified[' . $_key . ']', $_val);
                }
            }
        }

        /*
         * ###############################################
         * BEGIN SEARCH TAB
         * ###############################################
         */

        $_SearchTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsearch'), 'icon_form.gif', 'general', true);

        $_SearchTabObject->Title(sprintf($this->Language->Get('searchtemplategroup'), $_templateGroupCache[$_propertiesContainer['tgroupid']]['title']), 'icon_doublearrows.gif', 4);

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = $this->Language->Get('templatename');
        $_columnContainer[1]['align'] = 'center';
        $_columnContainer[1]['nowrap'] = true;
        $_columnContainer[1]['value'] = $this->Language->Get('status');
        $_SearchTabObject->Row($_columnContainer, 'gridtabletitlerow');


        foreach ($_templateContainer as $_key => $_val) {
            $_modifiedContainer = SWIFT_Template::GetModifiedHTML($_val['modified']);
            if (!$_modifiedContainer) {
                continue;
            }

            $_modifiedStatus = $_modifiedContainer[0];
            $_modifiedText = $_modifiedContainer[1];

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['value'] = '<a href="' . SWIFT::Get('basename') . '/Base/Template/Edit/' . (int)($_val['templateid']) . '/0/1" viewport="1">' . $this->Controller->StringHighlighter->Highlight(htmlspecialchars($_val['name']), $_propertiesContainer['searchquery'], SWIFT_StringHighlighter::HIGHLIGHT_SIMPLE, '<span class="searchighlightsmall">\1</span>') . '</a>';

            $_columnContainer[1]['align'] = 'center';
            $_columnContainer[1]['nowrap'] = true;
            $_columnContainer[1]['value'] = '<img src="' . SWIFT::Get('themepath') . $_modifiedStatus . '" border="0" align="absmiddle" />&nbsp;' . $_modifiedText;
            $_columnContainer[1]['width'] = 200;

            $_SearchTabObject->Row($_columnContainer);

            $_searchResult = $this->Controller->StringHighlighter->GetHighlightedRange($_val['contents'], $_propertiesContainer['searchquery'], 50);

            if (_is_array($_searchResult)) {
                $_columnContainer = array();
                $_columnContainer[0]['align'] = 'left';
                $_columnContainer[0]['nowrap'] = true;
                $_columnContainer[0]["value"] = '<div class="searchrule0"><table border="0" cellpadding="6" cellspacing="3" width="100%"><tr><td align="left" class="searchcode">' . implode('<hr class="search0hr" />', $_searchResult) . '</td></tr></table></div>';
                $_columnContainer[0]['colspan'] = '2';

                $_SearchTabObject->Row($_columnContainer, 'gridrow1');
            }
        }

        /*
         * ###############################################
         * END SEARCH TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
