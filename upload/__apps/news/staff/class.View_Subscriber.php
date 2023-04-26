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

namespace News\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Models\Subscriber\SWIFT_NewsSubscriber;
use SWIFT;
use SWIFT_Date;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The News Subscriber View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Subscriber extends SWIFT_View
{
    /**
     * Render the News Subscriber Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_NewsSubscriber $_SWIFT_NewsSubscriberObject The SWIFT_NewsSubscriber Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public function Render($_mode, SWIFT_NewsSubscriber $_SWIFT_NewsSubscriberObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_NewsSubscriberObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/News/Subscriber/EditSubmit/' . $_SWIFT_NewsSubscriberObject->GetNewsSubscriberID(),
                    SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/News/Subscriber/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_email = '';

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_NewsSubscriberObject !== null)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/News/Subscriber/Delete/' .
                    $_SWIFT_NewsSubscriberObject->GetNewsSubscriberID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newssubscriber'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_email = $_SWIFT_NewsSubscriberObject->GetProperty('email');
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newssubscriber'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('email', $this->Language->Get('emailaddress'), $this->Language->Get('desc_emailaddress'), $_email);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the News Subscriber Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('subscribergrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery(
            'SELECT * FROM ' . TABLE_PREFIX . 'newssubscribers
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('email') . ')',

            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'newssubscribers
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('email') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'newssubscribers',
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'newssubscribers');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('newssubscriberid', 'newssubscriberid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('email', $this->Language->Get('subscriberemail'),SWIFT_UserInterfaceGridField::TYPE_DB, 0,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('isvalidated', $this->Language->Get('isvalidated'),SWIFT_UserInterfaceGridField::TYPE_DB, 100,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('creationdate'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('\News\Staff\Controller_Subscriber', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLink("UICreateWindow('" . SWIFT::Get('basename') . "/News/Subscriber/Insert', 'insertsubscriber', '" . $this->Language->Get('wininsertsubscriber') . "', '" . $this->Language->Get('loadingwindow') . "', 550, 200, true, this);");

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
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_icon = 'fa-envelope';
        if ($_fieldContainer['isvalidated'] == '0')
        {
            $_icon = 'fa-envelope-o';
        }

        $_fieldContainer['icon'] = '<i class="fa '. $_icon .'" aria-hidden="true"></i>';

        $_fieldContainer['email'] = '<a href="' . SWIFT::Get('basename') . '/News/Subscriber/Edit/' . (int) ($_fieldContainer['newssubscriberid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/News/Subscriber/Edit/' . (int) ($_fieldContainer['newssubscriberid']) . "', 'editsubscriber', '" .
        sprintf($_SWIFT->Language->Get('wineditsubscriber'), htmlspecialchars($_fieldContainer['email'])) . "', '" .
                $_SWIFT->Language->Get('loadingwindow') . "', 550, 235, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') .
                '">' . htmlspecialchars($_fieldContainer['email']) . '</a>';

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        $_fieldContainer['isvalidated'] = IIF($_fieldContainer['isvalidated'] == '1', $_SWIFT->Language->Get('yes'), $_SWIFT->Language->Get('no'));

        return $_fieldContainer;
    }
}
?>
