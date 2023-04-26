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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Library\SLA\SWIFT_SLAHolidayManager;

/**
 * The Holiday Manager View Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_HolidayManager extends SWIFT_View
{
    /**
     * Render the SLA Holiday ImpEx Form
     *
     * @author Varun Shoor
     * @param bool $_isImportTabActivated Whether Import Tab is Activated by Default
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Render($_isImportTabActivated = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_isImportTabActivated = (int) ($_isImportTabActivated);

        $this->UserInterface->Start(get_short_class($this),'/Tickets/HolidayManager/Import', SWIFT_UserInterface::MODE_EDIT, false, true);

        $_ExportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabexport'), 'icon_export.gif', 'export',
                IIF(!$_isImportTabActivated, true, false));
        $_ImportTabObject = $this->UserInterface->AddTab($this->Language->Get('tabimport'), 'icon_import.gif', 'import', $_isImportTabActivated);


        $_ExportTabObject->LoadToolbar();
        $_ExportTabObject->Toolbar->AddButton($this->Language->Get('export'), 'fa-check-circle', 'PopupSmallWindow(\''. SWIFT::Get('basename') .'/Tickets/HolidayManager/Export/_exportFileName=\'+escape($(\'#filename\').val()));', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_ExportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('slaholidayimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_holidayPackFileName = SWIFT_SLAHolidayManager::GenerateFileName('sla');
        $_ExportTabObject->Text('filename', $this->Language->Get('exportfilename'), $this->Language->Get('desc_exportfilename'), $_holidayPackFileName);

        $_ImportTabObject->LoadToolbar();
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('import'), 'fa-check-circle', 'TabLoading(\''. get_short_class($this) .'\', \''. 'import' .'\'); $(\'#'. get_short_class($this) . SWIFT_UserInterface::FORM_SUFFIX .'\').submit();', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_ImportTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('slaholidayimpex'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_ImportTabObject->File('slaholidayfile', $this->Language->Get('slaholidayfile'), $this->Language->Get('desc_slaholidayfile'));

        $this->UserInterface->Hidden('isajax', '1');

        $this->UserInterface->End();

        return true;
    }
}
