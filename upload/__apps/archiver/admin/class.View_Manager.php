<?php

/**
 * ###############################################
 *
 * Archiver App for Kayako
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       archiver
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       https://github.com/trilogy-group/kayako-classic-archiver/blob/master/LICENSE
 * @link          https://github.com/trilogy-group/kayako-classic-archiver
 *
 * ###############################################
 */

namespace Archiver\Admin;

use Archiver\Library\Archiver\SWIFT_ArchiverHelp;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * Class View_Manager
 */
class View_Manager extends SWIFT_View
{
    public $UserInterface;
    public $UserInterfaceGrid;

    /**
     * view for search form action
     *
     * @author Werner Garcia
     * @return boolean true
     * @throws SWIFT_Exception
     */
    public function RenderSearchForm()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(
            'archiver_manager',
            '/archiver/Manager/Search',
            SWIFT_UserInterface::MODE_INSERT
        );

        /**
         * @var SWIFT_UserInterfaceToolbar $toolbar
         */
        $toolbar = $this->UserInterface->Toolbar;

        $toolbar->AddButton(
            $this->Language->Get('archiver_search_button'),
            'fa-search',
            "javascript:this.blur(); TabLoading('archiver_manager', 'archiver_tab'); $('#archiver_managerform').submit(); PreventDoubleClicking(this);",
            (string) SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT,
            'archiver_managerform_submit'
        );

        $toolbar->AddButton(
            $this->Language->Get('help'),
            'fa-question-circle',
            SWIFT_ArchiverHelp::RetrieveHelpLink('archive_manager'),
            (string)  SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW
        );

        $_GeneralTabObject = $this->UserInterface->AddTab(
            $this->Language->Get('archive_manager'),
            'icon_form.gif',
            'archiver_tab',
            true
        );

        $_GeneralTabObject->RowHTML($this->Template->Get('warning_block'));

        $_GeneralTabObject->TextAutoComplete('ar_email', $this->Language->Get('ar_email'),
            '/archiver/Manager/AjaxSearch', $this->Language->Get('ar_desc_email'), '', 0, 'icon_staffuser.gif', '30',
            0);

        $_SWIFT = SWIFT::GetInstance();
        $_dmy = $_SWIFT->Settings->Get('dt_caltype') === 'eu';
        $_format = $_dmy ? 'dd/mm/yyyy' : 'mm/dd/yyyy';

        $_startDate = gmdate(
            SWIFT_Date::GetCalendarDateFormat(),
            0
        );
        $_GeneralTabObject->Date(
            'ar_start_date',
            $this->Language->Get('ar_start_date'),
            sprintf($this->Language->Get('ar_desc_start_date'), $_format),
            $_startDate
        );

        $endTime = new \DateTime('now -3 months');
        $_endDate = gmdate(
            SWIFT_Date::GetCalendarDateFormat(),
            $endTime->getTimestamp()
        );
        $_GeneralTabObject->Date(
            'ar_end_date',
            $this->Language->Get('ar_end_date'),
            sprintf($this->Language->Get('ar_desc_end_date'), $_format),
            $_endDate
        );

        $_GeneralTabObject->Number(
            'ar_page_size',
            $this->Language->Get('ar_page_size'),
            $this->Language->Get('ar_desc_page_size'),
            20
        );

        $this->UserInterface->End();

        return true;
    }

    /**
     * view for empty trash action
     *
     * @author Werner Garcia
     * @return boolean true
     * @throws SWIFT_Exception
     */
    public function RenderTrashForm()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(
            'archiver_manager',
            '/archiver/Manager/Search',
            SWIFT_UserInterface::MODE_INSERT
        );

        /**
         * @var SWIFT_UserInterfaceToolbar $toolbar
         */
        $toolbar = $this->UserInterface->Toolbar;

        $toolbar->AddButton(
            $this->Language->Get('archiver_search_trash'),
            'fa-search',
            "javascript:this.blur(); TabLoading('archiver_manager', 'archiver_tab'); $('#archiver_managerform').submit(); PreventDoubleClicking(this);",
            (string)   SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT,
            'archiver_managerform_submit'
        );

        $toolbar->AddButton(
            $this->Language->Get('help'),
            'fa-question-circle',
            SWIFT_ArchiverHelp::RetrieveHelpLink('archive_manager'),
            (string)   SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW
        );

        $_GeneralTabObject = $this->UserInterface->AddTab(
            $this->Language->Get('empty_trash'),
            'icon_form.gif',
            'archiver_tab',
            true
        );

        $_GeneralTabObject->RowHTML($this->Template->Get('empty_trash'));

        $_startDate = gmdate(
            SWIFT_Date::GetCalendarDateFormat(),
            0
        );
        $_GeneralTabObject->Hidden('ar_start_date', $_startDate);

        $endTime = new \DateTime('now');
        $_endDate = gmdate(
            SWIFT_Date::GetCalendarDateFormat(),
            $endTime->getTimestamp()
        );
        $_GeneralTabObject->Hidden('ar_end_date', $_endDate);

        $_GeneralTabObject->Hidden('ar_is_trash', 'true');

        $_GeneralTabObject->Number(
            'ar_page_size',
            $this->Language->Get('ar_page_size'),
            $this->Language->Get('ar_desc_page_size'),
            20
        );

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Search Grid
     *
     * @author Werner Garcia
     * @param string $_where
     * @param string $_start_date
     * @param string $_end_date
     * @param string $_email
     * @param int $_page_size
     * @param int $_row_count
     * @param bool $_is_trash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderSearchGrid(
        $_where,
        $_start_date,
        $_end_date,
        $_email,
        $_page_size,
        $_row_count,
        $_is_trash = false
    ) {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('searchgrid'), true, false, 'base');

        $gridSearchSelect = 'SELECT B.ticketid,
    B.departmenttitle,
    B.ticketstatustitle,
    B.ownerstaffname,
    B.fullname,
    B.subject,
    B.dateline ';

        if ($this->UserInterfaceGrid->GetMode() === SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $gridSearchQuery = ' FROM ' . TABLE_PREFIX . 'tickets B WHERE ((' . $this->UserInterfaceGrid->BuildSQLSearch('subject') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('ownerstaffname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('fullname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('departmenttitle') . ')) AND (' . $_where . ')';

            $this->UserInterfaceGrid->SetSearchQuery($gridSearchSelect . $gridSearchQuery,
                'SELECT COUNT(*) ' . $gridSearchQuery);
        }

        $gridQuery = ' FROM ' . TABLE_PREFIX . 'tickets B WHERE (' . $_where . ')';

        $this->UserInterfaceGrid->SetQuery($gridSearchSelect . $gridQuery, 'SELECT COUNT(*) ' . $gridQuery);

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketid', $this->Language->Get('lcid'),
            SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('lcdate'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_LEFT,
            SWIFT_UserInterfaceGridField::SORT_DESC), true);

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('fullname', $this->Language->Get('lcuser'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('subject',
            $this->Language->Get('lcsubject'), SWIFT_UserInterfaceGridField::TYPE_DB, 0,
            SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('departmenttitle',
            $this->Language->Get('lcdept'), SWIFT_UserInterfaceGridField::TYPE_DB, 0,
            SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketstatustitle',
            $this->Language->Get('lcstatus'), SWIFT_UserInterfaceGridField::TYPE_DB, 0,
            SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ownerstaffname',
            $this->Language->Get('lcstaff'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $params = [
            'ar_start_date' => $_start_date,
            'ar_end_date' => $_end_date,
            'ar_page_size' => $_page_size,
            'ar_is_trash' => $_is_trash,
            'ar_email' => $_email,
        ];
        $query_string = http_build_query($params);

        $this->UserInterfaceGrid->AddAction(array(
            $this->Language->Get('back'),
            'fa-chevron-circle-left sw-help-button',
            null,
            '',
            "LoadViewportPOST('" . ($_is_trash ? '/archiver/Manager/Trash' : '/archiver/Manager/Index') . "', '" . $query_string . "');",
        ));

        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('archiver_delete_button'),
            'sw-trash-button sw-disable',
            array('\Archiver\Admin\Controller_Manager', 'DeleteList'),
            $this->Language->Get('ar_confirmdelete')));

        if (!$_is_trash) {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction(
                $this->Language->Get('archiver_export_button'),
                'sw-export-button sw-disable',
                array('\Archiver\Admin\Controller_Manager','ExportList')));
        }

        $msg = sprintf($this->Language->Get('ar_confirmdelete_all'), $_row_count);
        $link = SWIFT::Get('basename') . '/archiver/Manager/DeleteAll';
        $this->UserInterfaceGrid->AddAction(array(
            $this->Language->Get($_is_trash ?
                'archiver_trash_button' : 'archiver_delete_button_all'),
            'fa-trash sw-disable',
            null,
            '',
            "this.blur(); SWIFT.Archiver.AdminObject.StartDeleteAll('$msg', '$link', '$query_string');",
        ));

        if (!$_is_trash) {
            $link = SWIFT::Get('basename') . '/archiver/Manager/ExportAll&' . $query_string;
            $this->UserInterfaceGrid->AddAction(array(
                $this->Language->Get('archiver_export_button_all'),
                'fa-download sw-disable',
                null,
                '',
                "this.blur(); window.open('$link');",
            ));
        }

        $this->UserInterfaceGrid->AddAction(array(
            $this->Language->Get('help'),
            'fa-question-circle',
            null,
            '',
            "window.open('" . SWIFT_ArchiverHelp::RetrieveHelpLink('archive_manager') . "')",
        ));

        $this->UserInterfaceGrid->SetRecordsPerPage($_page_size);

        $this->UserInterfaceGrid->SetExtendedArguments('&' . $query_string . '&');

        $this->Log($this->UserInterfaceGrid->GetSearchSelectQuery());

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'SearchGridRender'));
        $this->UserInterfaceGrid->Render();
        $this->UserInterfaceGrid->Display();

        $this->Template->Assign('_swiftpath', SWIFT::Get('swiftpath'));
        $this->Template->Assign('_row_count', (string) $_row_count);

        $this->Template->Render('grid_links');

        return true;
    }

    /**
     * The Task Log Grid Rendering Function
     *
     * @author Werner Garcia
     * @param array $fieldContainer The Field Record Value Container
     * @return array
     */
    public static function SearchGridRender($fieldContainer)
    {
        $fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $fieldContainer['dateline']);

        $fieldContainer['fullname'] = trim(removeTags($fieldContainer['fullname']));

        // Download export file if the URL is stored
        if ($url = SWIFT::Get('export_ready')) {
            echo "<script>location.href = '$url';</script>";
            SWIFT::Set('export_ready', null);
        }

        return $fieldContainer;
    }

}
