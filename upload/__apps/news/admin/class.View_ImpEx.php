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

namespace News\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The News Impex View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_ImpEx extends SWIFT_View
{
    /**
     * Renders the ImpEx Form
     *
     * @author Varun Shoor
     * @param bool $_isImportTabSelected (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function RenderImpEx($_isImportTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_isExportTabSelected = true;
        if ($_isImportTabSelected)
        {
            $_isExportTabSelected = false;
        }

        $_templateGroupCache = (array) $this->Cache->Get('templategroupcache');

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this),'/News/ImpEx/Import', SWIFT_UserInterface::MODE_EDIT, false, true);

        $_ExportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabexport'), 'icon_export.gif', 'export', $_isExportTabSelected);
        $_ImportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabimport'), 'icon_import.gif', 'import', $_isImportTabSelected);

        $_ExportTabObject->LoadToolbar();
        $_ExportTabObject->Toolbar->AddButton($this->Language->Get('export'), 'fa-check-circle', 'PopupSmallWindow(\''. SWIFT::Get('basename') .'/News/ImpEx/Export/\'+$(\'#exporttgroupid\').val());', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_ExportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newsimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        $_index = 1;
        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('tgroupany');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        foreach ($_templateGroupCache as $_templateGroup)
        {
            $_optionsContainer[$_index]['title'] = $_templateGroup['title'];
            $_optionsContainer[$_index]['value'] = $_templateGroup['tgroupid'];
            $_index++;
        }

        $_ExportTabObject->Select('exporttgroupid', $this->Language->Get('filtertgroup'), $this->Language->Get('desc_filtertgroup'), $_optionsContainer);


        $_ImportTabObject->LoadToolbar();
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('import'), 'fa-check-circle', 'TabLoading(\''. get_class($this) .'\', \''. 'import' .'\'); $(\'#'. get_short_class($this) . SWIFT_UserInterface::FORM_SUFFIX .'\').submit();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newsimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_ImportTabObject->Title($this->Language->Get('importemails'), 'icon_doublearrows.gif');
        $_ImportTabObject->TextArea('emails', '', '', '', 10, 10);

        $this->UserInterface->Hidden('isajax', '1');

        $this->UserInterface->End();

        return true;
    }
}
?>
