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
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\FileType\SWIFT_TicketFileType;

/**
 * The Ticket File Type View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_FileType extends SWIFT_View
{
    /**
     * Render the Ticket File Type Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketFileType $_SWIFT_TicketFileTypeObject The SWIFT_TicketFileType Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketFileType $_SWIFT_TicketFileTypeObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/FileType/EditSubmit/' . $_SWIFT_TicketFileTypeObject->GetTicketFileTypeID(),
                    SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/FileType/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_extension = '';
        $_maxSize = 0;
        $_acceptSupportCenter = true;
        $_acceptMailParser = true;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/FileType/Delete/' .
                    $_SWIFT_TicketFileTypeObject->GetTicketFileTypeID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketfiletype'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_extension = $_SWIFT_TicketFileTypeObject->GetProperty('extension');
            $_maxSize = (int) ($_SWIFT_TicketFileTypeObject->GetProperty('maxsize'));

            $_acceptSupportCenter = (int) ($_SWIFT_TicketFileTypeObject->GetProperty('acceptsupportcenter'));
            $_acceptMailParser = (int) ($_SWIFT_TicketFileTypeObject->GetProperty('acceptmailparser'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketfiletype'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('extension', $this->Language->Get('extension'), $this->Language->Get('desc_extension'), $_extension);

        $_GeneralTabObject->Number('maxsize', $this->Language->Get('maxsize'), $this->Language->Get('desc_maxsize'), ($_maxSize));

        $_GeneralTabObject->Title($this->Language->Get('filetypeoptions'), 'doublearrows.gif');

        $_GeneralTabObject->YesNo('acceptsupportcenter', $this->Language->Get('acceptsupportcenter'),
                $this->Language->Get('desc_acceptsupportcenter'), $_acceptSupportCenter);
        $_GeneralTabObject->YesNo('acceptmailparser', $this->Language->Get('acceptmailparser'), $this->Language->Get('desc_acceptmailparser'),
                $_acceptMailParser);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket File Type Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketfiletypegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketfiletypes WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('extension') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                    'ticketfiletypes WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('extension') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketfiletypes', 'SELECT COUNT(*) AS totalitems FROM ' .
                TABLE_PREFIX . 'ticketfiletypes');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketfiletypeid', 'ticketfiletypeid',
                SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('extension', $this->Language->Get('extension'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('maxsize', $this->Language->Get('maxsize'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_FileType', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/FileType/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_mimeDataContainer = array();
        try
        {
            $_MIMEListObject = new SWIFT_MIMEList();
            $_mimeDataContainer = $_MIMEListObject->Get($_fieldContainer['extension']);
        } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
            // Do nothing
        }

        $_icon = 'icon_file.gif';
        if (isset($_mimeDataContainer[1]))
        {
            $_icon = $_mimeDataContainer[1];
        }

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_icon .'" align="absmiddle" border="0" />';

        $_fieldContainer['extension'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/FileType/Edit/' . (int) ($_fieldContainer['ticketfiletypeid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Tickets/FileType/Edit/' . (int) ($_fieldContainer['ticketfiletypeid']) . "', 'editfiletype', '" . sprintf($_SWIFT->Language->Get('wineditfiletype'), htmlspecialchars($_fieldContainer['extension'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 800, 535, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['extension']) . '</a>';

        $_fieldContainer['maxsize'] = SWIFT_TicketFileType::GetSize($_fieldContainer['maxsize']);

        return $_fieldContainer;
    }
}
