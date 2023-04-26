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

use SWIFT;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Models\Template\SWIFT_Template;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Template Restore View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_TemplateRestore extends SWIFT_View
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
     * Render the Template Restore Form
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

        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateRestore/ListTemplates', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('search'), 'fa-search');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templaterestore'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN RESTORE TAB
         * ###############################################
         */
        $_RestoreTabObject = $this->UserInterface->AddTab($this->Language->Get('tabrestore'), 'icon_restore.gif', 'general', true);

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

        $_RestoreTabObject->Select('tgroupid', $this->Language->Get('moditgroup'), $this->Language->Get('desc_moditgroup'), $_optionsContainer);
        $_RestoreTabObject->Title($this->Language->Get('findtemplates'), 'icon_doublearrows.gif');

        $_templateModified = $_templateUpgrade = true;
        $_templateNotModified = false;
        if (isset($_POST['modified'][SWIFT_Template::TYPE_UPGRADE])) {
            $_templateUpgrade = true;
        }

        if (isset($_POST['modified'][SWIFT_Template::TYPE_NOTMODIFIED])) {
            $_templateNotModified = true;
        }

        $_RestoreTabObject->YesNo('modified[' . SWIFT_Template::TYPE_MODIFIED . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatemodified.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('modified') . '</b>', '', $_templateModified);
        $_RestoreTabObject->YesNo('modified[' . SWIFT_Template::TYPE_UPGRADE . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templateupgrade.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('upgrade') . '</b>', '', $_templateUpgrade);
        $_RestoreTabObject->YesNo('modified[' . SWIFT_Template::TYPE_NOTMODIFIED . ']', '<img src="' . SWIFT::Get('themepath') . 'images/icon_templatenotmodified.gif" align="absmiddle" border="0" />&nbsp;<b>' . $this->Language->Get('notmodified') . '</b>', '', $_templateNotModified);

        /*
         * ###############################################
         * END RESTORE TAB
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

        $this->UserInterface->Start(get_short_class($this), '/Base/TemplateRestore/Restore', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('restore'), 'fa-rotate-right', '', SWIFT_UserInterfaceToolbar::LINK_SUBMITCONFIRM, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('templaterestore'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

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
         * BEGIN RESTORE TAB
         * ###############################################
         */

        $_RestoreTabObject = $this->UserInterface->AddTab($this->Language->Get('tabrestore'), 'icon_restore.gif', 'general', true);

        $_RestoreTabObject->Title(sprintf($this->Language->Get('restoretgroup'), $_templateGroupCache[$_POST['tgroupid']]['title']), 'icon_doublearrows.gif', 4);

        $_columnContainer = array();
        $_columnContainer[0]['width'] = '20';
        $_columnContainer[0]['value'] = '<input type="checkbox" name="allselect" class="swiftcheckbox" onClick="javascript:toggleAll(\'\', \'View_TemplateRestore\');" />';
        $_columnContainer[0]['align'] = 'center';
        $_columnContainer[0]['class'] = 'gridtabletitlerow gridtabletitlerowcenter';

        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['nowrap'] = true;
        $_columnContainer[1]['value'] = $this->Language->Get('templatename');

        $_columnContainer[2]['align'] = 'left';
        $_columnContainer[2]['nowrap'] = true;
        $_columnContainer[2]['value'] = $this->Language->Get('status');
        $_RestoreTabObject->Row($_columnContainer, 'gridtabletitlerow');

        foreach ($_templateContainer as $_key => $_val) {
            $_modifiedContainer = SWIFT_Template::GetModifiedHTML($_val['modified']);
            if (!$_modifiedContainer) {
                continue;
            }

            $_modifiedStatus = $_modifiedContainer[0];
            $_modifiedText = $_modifiedContainer[1];

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'center';
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['value'] = '<input type="checkbox" name="itemid[]" value=' . (int)($_val['templateid']) . ' class=\"swiftcheckbox\" >';
            $_columnContainer[0]['width'] = 20;

            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[1]['nowrap'] = true;
            $_columnContainer[1]['value'] = '<a href="' . SWIFT::Get('basename') . '/Base/Template/Edit/' . (int)($_val['templateid']) . '" viewport="1">' . htmlspecialchars($_val['name']) . '</a>';

            $_columnContainer[2]['align'] = 'left';
            $_columnContainer[2]['nowrap'] = true;
            $_columnContainer[2]['value'] = '<img src="' . SWIFT::Get('themepath') . $_modifiedStatus . '" border="0" align="absmiddle" />&nbsp;' . $_modifiedText;
            $_columnContainer[2]['width'] = 200;

            $_RestoreTabObject->Row($_columnContainer);
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
