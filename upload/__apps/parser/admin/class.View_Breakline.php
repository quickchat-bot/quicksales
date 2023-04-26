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
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
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
use Parser\Models\Breakline\SWIFT_Breakline;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Breakline View Management Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Breakline extends SWIFT_View
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
     * Render the Breakline Form
     *
     * @author Varun Shoor
     *
     * @param int    $_mode                  The Render Mode
     * @param SWIFT_Breakline $_SWIFT_BreaklineObject The Parser\Models\Breakline\SWIFT_Breakline Object Pointer (Only for EDIT Mode)
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_Breakline $_SWIFT_BreaklineObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Parser/Breakline/EditSubmit/' . $_SWIFT_BreaklineObject->GetBreaklineID(),
                SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Parser/Breakline/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_breaklineCache = $this->Cache->Get('breaklinecache');

        $_breaklineText = '';
        $_sortOrder = count($_breaklineCache) + 1;
        $_isRegularExpression = false;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/Breakline/Delete/' .
                $_SWIFT_BreaklineObject->GetBreaklineID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserbreakline'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_breaklineText = $_SWIFT_BreaklineObject->GetProperty('breakline');
            $_isRegularExpression = (int)($_SWIFT_BreaklineObject->GetProperty('isregexp'));
            $_sortOrder = (int)($_SWIFT_BreaklineObject->GetProperty('sortorder'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserbreakline'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('breakline', $this->Language->Get('breaklinetitle'), $this->Language->Get('desc_breaklinetitle'), $_breaklineText);
        $_GeneralTabObject->YesNo('isregexp', $this->Language->Get('isregexp'), $this->Language->Get('desc_isregexp'), $_isRegularExpression);
        $_GeneralTabObject->Number('sortorder', $this->Language->Get('sortorder'), $this->Language->Get('desc_sortorder'), $_sortOrder);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Parser Breakline Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('breaklinegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'breaklines WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('breakline') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                'breaklines WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('breakline') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'breaklines', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
            'breaklines');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('breaklineid', 'breaklineid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('breakline', $this->Language->Get('title'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('isregexp', $this->Language->Get('isregexp'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sortorder', $this->Language->Get('sortorder'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 100, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_Breakline', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Parser/Breakline/Insert');

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

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_breakline.gif" align="absmiddle" border="0" />';

        $_fieldContainer['breakline'] = '<a href="' . SWIFT::Get('basename') . '/Parser/Breakline/Edit/' . (int)($_fieldContainer['breaklineid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') .
            "/Parser/Breakline/Edit/" . (int)($_fieldContainer['breaklineid']) . "', 'editbreakline', '" . $_SWIFT->Language->Get('wineditbreakline') .
            "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 680, 420, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' .
            htmlspecialchars($_fieldContainer['breakline']) . '</a>';

        $_fieldContainer['sortorder'] = (int)($_fieldContainer['sortorder']);
        $_fieldContainer['isregexp'] = IIF($_fieldContainer['isregexp'] == '1', $_SWIFT->Language->Get('yes'), $_SWIFT->Language->Get('no'));

        return $_fieldContainer;
    }
}

?>
