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
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Models\Template\SWIFT_Template;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Template Diagnostics View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_TemplateDiagnostics extends SWIFT_View
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
     * Render the Template Diagnostics Form
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

        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateDiagnostics/RunDiagnostics', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('diagnose'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templatediagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        /*
         * ###############################################
         * BEGIN DIAGNOSTICS TAB
         * ###############################################
         */
        $_DiagnosticsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabdiagnostics'), 'icon_templatediagnostics.gif', 'general', true);

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

        $_DiagnosticsTabObject->Select('tgroupid', $this->Language->Get('diagtgroup'), $this->Language->Get('desc_diagtgroup'), $_optionsContainer);
        $_DiagnosticsTabObject->Title($this->Language->Get('filter'), 'icon_doublearrows.gif');

        $_templateModified = true;
        $_templateUpgrade = $_templateNotModified = false;
        if (isset($_POST['modified'][SWIFT_Template::TYPE_UPGRADE])) {
            $_templateUpgrade = true;
        }

        if (isset($_POST['modified'][SWIFT_Template::TYPE_NOTMODIFIED])) {
            $_templateNotModified = true;
        }

        $_DiagnosticsTabObject->YesNo('modified[' . SWIFT_Template::TYPE_MODIFIED . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatemodified.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('modified') . '</b>', '', $_templateModified);
        $_DiagnosticsTabObject->YesNo('modified[' . SWIFT_Template::TYPE_UPGRADE . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templateupgrade.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('upgrade') . '</b>', '', $_templateUpgrade);
        $_DiagnosticsTabObject->YesNo('modified[' . SWIFT_Template::TYPE_NOTMODIFIED . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatenotmodified.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('notmodified') . '</b>', '', $_templateNotModified);

        /*
         * ###############################################
         * END DIAGNOSTICS TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Template Diagnostics Result
     *
     * @author Varun Shoor
     * @param array $_templateContainer The Template Container Array with Processed Compile Result and Compile Time
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderResult($_templateContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        if (!isset($_templateGroupCache[$_POST['tgroupid']])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateDiagnostics/Index', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templatediagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        // Add hidden fields if we have the POST data (needed for back button to work)
        if (isset($_POST['tgroupid'])) {
            $this->UserInterface->Hidden('tgroupid', $_POST['tgroupid']);
        }

        if (isset($_POST['modified']) && _is_array($_POST['modified'])) {
            foreach ($_POST['modified'] as $_key => $_val) {
                if ($_val == 1) {
                    $this->UserInterface->Hidden('modified[' . $_key . ']', $_val);
                }
            }
        }

        /*
         * ###############################################
         * BEGIN DIAGNOSTICS TAB
         * ###############################################
         */
        $_DiagnosticsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabdiagnostics'), 'icon_templatediagnostics.gif', 'general', true);

        $_DiagnosticsTabObject->Title(sprintf($this->Language->Get('diagnosetemplategroup'), $_templateGroupCache[$_POST['tgroupid']]['title']), 'icon_doublearrows.gif', 4);

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = $this->Language->Get('templatename');
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['nowrap'] = true;
        $_columnContainer[1]['value'] = $this->Language->Get('compiletime');
        $_columnContainer[2]['align'] = 'left';
        $_columnContainer[2]['nowrap'] = true;
        $_columnContainer[2]['value'] = $this->Language->Get('result');
        $_columnContainer[3]['align'] = 'left';
        $_columnContainer[3]['nowrap'] = true;
        $_columnContainer[3]['value'] = $this->Language->Get('status');
        $_DiagnosticsTabObject->Row($_columnContainer, 'gridtabletitlerow');


        foreach ($_templateContainer as $_key => $_val) {
            if (!$_val['_compileResult']) {
                $_statusImage = 'fa-minus-circle';
                $_statusText = $this->Language->Get('failure');
            } else {
                $_statusImage = 'fa-check-circle';
                $_statusText = $this->Language->Get('success');
            }

            $_modifiedContainer = SWIFT_Template::GetModifiedHTML($_val['modified']);
            if (!$_modifiedContainer) {
                continue;
            }

            $_modifiedStatus = $_modifiedContainer[0];
            $_modifiedText = $_modifiedContainer[1];

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['value'] = '<a href="' . SWIFT::Get('basename') . '/Base/Template/Edit/' . (int)($_val['templateid']) . '" viewport="1">' . htmlspecialchars($_val['name']) . '</a>';

            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[1]['nowrap'] = true;
            $_columnContainer[1]['value'] = number_format($_val['_compileTime'], 2) . '&nbsp;' . $this->Language->Get('seconds');

            $_columnContainer[2]['align'] = 'left';
            $_columnContainer[2]['nowrap'] = true;
            $_columnContainer[2]['value'] = '<i class="fa ' . $_statusImage . '" aria-hidden="true"></i>&nbsp;' . $_statusText;

            $_columnContainer[3]['align'] = 'left';
            $_columnContainer[3]['nowrap'] = true;
            $_columnContainer[3]['value'] = '<img src="' . SWIFT::Get('themepath') . $_modifiedStatus . '" border="0" align="absmiddle" />&nbsp;' . $_modifiedText;
            $_columnContainer[3]['width'] = 200;

            $_DiagnosticsTabObject->Row($_columnContainer, IIF(!$_val['_compileResult'], 'errorrow', false));
        }


        /*
         * ###############################################
         * END DIAGNOSTICS TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
