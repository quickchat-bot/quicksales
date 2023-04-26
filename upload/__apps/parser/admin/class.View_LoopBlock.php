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

use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Loop Blockages View Class
 *
 * @author Varun Shoor
 */
class View_LoopBlock extends SWIFT_View
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
     * Render the Parser Loop Blocks Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('parserloopblockgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserloopblocks WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('address') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                'parserloopblocks WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('address') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserloopblocks', 'SELECT COUNT(*) AS totalitems FROM ' .
            TABLE_PREFIX . 'parserloopblocks');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserloopblockid', 'parserloopblockid',
            SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('address', $this->Language->Get('pr_threshhold_grid_address_title'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('restoretime',
            $this->Language->Get('pr_threshhold_grid_restoreafter_title'), SWIFT_UserInterfaceGridField::TYPE_DB, 200,
            SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_LoopBlock', 'DeleteList'), $this->Language->Get('actionconfirm')));

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

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_loopcutterblocks.gif" align="absmiddle" border="0" />';
        $_fieldContainer['address'] = htmlspecialchars($_fieldContainer['address']);
        $_fieldContainer['restoretime'] = SWIFT_Date::ColorTime(-(DATENOW - $_fieldContainer['restoretime']));

        return $_fieldContainer;
    }
}

?>
