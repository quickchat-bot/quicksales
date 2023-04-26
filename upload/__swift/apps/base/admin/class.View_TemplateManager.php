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
use Base\Library\Template\SWIFT_TemplateManager;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Template Manager View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_TemplateManager extends SWIFT_View
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
     * @param bool $_isImpexTabSelected
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderImpEx($_isImpexTabSelected)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        $_isExportTabSelected = true;
        if ($_isImpexTabSelected) {
            $_isExportTabSelected = false;
        }

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateManager/Import', SWIFT_UserInterface::MODE_EDIT, false, true);

        $_ExportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabexport'), 'icon_export.gif', 'export', $_isExportTabSelected);
        $_ImportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabimport'), 'icon_import.gif', 'import', $_isImpexTabSelected);

        /*
         * ###############################################
         * BEGIN EXPORT TAB
         * ###############################################
         */

        $_ExportTabObject->LoadToolbar();
        $_ExportTabObject->Toolbar->AddButton($this->Language->Get('export'), 'fa-check-circle', 'PopupSmallWindow(\'' . SWIFT::Get('basename') . '/Base/TemplateManager/Export/\' + $(\'#selecttgroupidexport\').val() + \'/\' + $(\'#selectexportoptions\').val() + \'/\' + GetYesNoValue(\'exporthistory\'));', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_ExportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templateimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

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

        $_ExportTabObject->Select('tgroupidexport', $this->Language->Get('exporttgroup'), $this->Language->Get('desc_exporttgroup'), $_optionsContainer);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('exportalltemplates');
        $_optionsContainer[0]['value'] = SWIFT_TemplateManager::EXPORT_ALL;
        $_optionsContainer[0]['selected'] = true;
        $_optionsContainer[1]['title'] = $this->Language->Get('exportmodifications');
        $_optionsContainer[1]['value'] = SWIFT_TemplateManager::EXPORT_MODIFICATIONS;

        $_ExportTabObject->Select('exportoptions', $this->Language->Get('exportoptions'), $this->Language->Get('desc_exportoptions'), $_optionsContainer);
        $_ExportTabObject->YesNo('exporthistory', $this->Language->Get('exporthistory'), $this->Language->Get('desc_exporthistory'), false);

        /*
         * ###############################################
         * END EXPORT TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN IMPORT TAB
         * ###############################################
         */

        $_ImportTabObject->LoadToolbar();
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('import'), 'fa-check-circle', 'TabLoading(\'' . get_short_class($this) . '\', \'' . 'import' . '\'); $(\'#' . get_short_class($this) . SWIFT_UserInterface::FORM_SUFFIX . '\').submit();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templateimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_ImportTabObject->File('templatefile', $this->Language->Get('templatefile'), $this->Language->Get('desc_templatefile'));

        $_ImportTabObject->YesNo('ignoreversion', $this->Language->Get('ignoreversion'), $this->Language->Get('desc_ignoreversion'), false);

        $_ImportTabObject->title($this->Language->Get('mergeoptions'), 'icon_doublearrows.gif');

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('createnewgroup');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;
        foreach ($_templateGroupCache as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['tgroupid'];
            $_index++;
        }

        $_ImportTabObject->Select('tgroupidimport', $this->Language->Get('mergewith'), $this->Language->Get('desc_mergewith'), $_optionsContainer);
        $_ImportTabObject->YesNo('addtohistory', $this->Language->Get('addtohistory'), $this->Language->Get('desc_addtohistory'), true);

        /*
         * ###############################################
         * END IMPORT TAB
         * ###############################################
         */

        $this->UserInterface->Hidden('isajax', '1');

        $this->UserInterface->End();

        return true;
    }

    /**
     * Renders the Personalize Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderPersonalize()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateManager/PersonalizeSubmit', SWIFT_UserInterface::MODE_EDIT, false, true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templateheaderlogos'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN PERSONALIZE TAB
         * ###############################################
         */

        $_PersonalizeTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpersonalize'), 'icon_form.gif', 'personalize', true);

        $_PersonalizeTabObject->Title($this->Language->Get('logoimages'), 'icon_doublearrows.gif');

        $_PersonalizeTabObject->File('supportcenterlogo', $this->Language->Get('supportcenterlogo'), $this->Language->Get('desc_supportcenterlogo'));

        $_PersonalizeTabObject->File('stafflogo', $this->Language->Get('stafflogo'), $this->Language->Get('desc_stafflogo'));

        /*
         * ###############################################
         * END PERSONALIZE TAB
         * ###############################################
         */


        $this->UserInterface->Hidden('isajax', '1');

        $this->UserInterface->End();

        return true;
    }
}

?>
