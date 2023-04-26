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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\Link\SWIFT_TicketLinkType;

/**
 * The Ticket Link Type View Class
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Link extends SWIFT_View
{
    /**
     * Render the Ticket Link Type Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketLinkType $_SWIFT_TicketLinkTypeObject The SWIFT_TicketLinkType Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketLinkType $_SWIFT_TicketLinkTypeObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Link/EditSubmit/'. $_SWIFT_TicketLinkTypeObject->GetTicketLinkTypeID(),
                    SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Link/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_linkTypeTitle = '';
        $_displayOrder = SWIFT_TicketLinkType::GetLastDisplayOrder();

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Link/Delete/' .
                    $_SWIFT_TicketLinkTypeObject->GetTicketLinkTypeID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketlink'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_linkTypeTitle = $_SWIFT_TicketLinkTypeObject->GetProperty('linktypetitle');
            $_displayOrder = (int) ($_SWIFT_TicketLinkTypeObject->GetProperty('displayorder'));

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketlink'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('linktypetitle', $this->Language->Get('linktypetitle'), $this->Language->Get('desc_linktypetitle'),
                $_linkTypeTitle);

        $_GeneralTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), $_displayOrder);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Link Type Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketlinktypegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketlinktypes WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('linktypetitle') . ')', 'SELECT COUNT(*) AS totalitems FROM ' .
                    TABLE_PREFIX . 'ticketlinktypes WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('linktypetitle') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketlinktypes', 'SELECT COUNT(*) AS totalitems FROM ' .
                TABLE_PREFIX . 'ticketlinktypes');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketlinktypeid', 'ticketlinktypeid',
                SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('linktypetitle', $this->Language->Get('title'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('displayorder', $this->Language->Get('order'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_CENTER, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_Link', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Link/Insert');

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatelink') != '0')
        {
            $this->UserInterfaceGrid->SetSortableCallback('displayorder', array('Tickets\Admin\Controller_Link', 'SortList'));
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
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') .'images/icon_link.png" align="absmiddle" border="0" />';

        $_fieldContainer['linktypetitle'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/Link/Edit/' . (int) ($_fieldContainer['ticketlinktypeid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Tickets/Link/Edit/' . (int) ($_fieldContainer['ticketlinktypeid']) . "', 'editticketlinktype', '" . sprintf($_SWIFT->Language->Get('wineditticketlinktype'), addslashes(htmlspecialchars($_fieldContainer['linktypetitle']))) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 650, 430, true);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['linktypetitle']) . '</a>';

        return $_fieldContainer;
    }
}
