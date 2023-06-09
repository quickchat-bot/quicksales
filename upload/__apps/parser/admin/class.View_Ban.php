<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Parser\Models\Ban\SWIFT_ParserBan;
use SWIFT_View;

/**
 * The Parser Ban View Management Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Ban extends SWIFT_View
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
     * Render the Parser Ban Form
     *
     * @author Varun Shoor
     *
     * @param int    $_mode                  The Render Mode
     * @param SWIFT_ParserBan $_SWIFT_ParserBanObject The Parser\Models\Ban\SWIFT_ParserBan Object Pointer (Only for EDIT Mode)
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_ParserBan $_SWIFT_ParserBanObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Parser/Ban/EditSubmit/' . $_SWIFT_ParserBanObject->GetParserBanID(),
                SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Parser/Ban/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_bannedEmail = '';

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/Ban/Delete/' .
                $_SWIFT_ParserBanObject->GetParserBanID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserban'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_bannedEmail = $_SWIFT_ParserBanObject->GetProperty('email');
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserban'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('email', $this->Language->Get('bannedemail'), $this->Language->Get('desc_bannedemail'), $_bannedEmail);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Parser Ban Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('parserbangrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserbans WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('email') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                'parserbans WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('email') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserbans', 'SELECT COUNT(*) AS totalitems FROM ' .
            TABLE_PREFIX . 'parserbans');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserbanid', 'parserbanid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('email', $this->Language->Get('bannedemail'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffid', $this->Language->Get('bannedby'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 250, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('date'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_Ban', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Parser/Ban/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     *
     * @param array $_fieldContainer The Field Record Value Container
     *
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        if (isset($_staffCache[$_fieldContainer['staffid']])) {
            $_fieldContainer['staffid'] = text_to_html_entities($_staffCache[$_fieldContainer['staffid']]['fullname']);
        } else {
            $_fieldContainer['staffid'] = $_SWIFT->Language->Get('na');
        }

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_block.gif" border="0" align="absmiddle" />';

        $_fieldContainer['email'] = '<a href="' . SWIFT::Get('basename') . '/Parser/Ban/Edit/' . (int)($_fieldContainer['parserbanid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') .
            "/Parser/Ban/Edit/" . (int)($_fieldContainer['parserbanid']) . "', 'editparserban', '" . $_SWIFT->Language->Get('wineditban') . "', '" .
            $_SWIFT->Language->Get('loadingwindow') . "', 680, 380, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' .
            htmlspecialchars($_fieldContainer['email']) . '</a>';

        return $_fieldContainer;
    }
}

?>
