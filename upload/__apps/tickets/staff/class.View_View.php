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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Tickets\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Models\View\SWIFT_TicketViewField;
use Tickets\Models\View\SWIFT_TicketViewLink;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Ticket View
 *
 * What a name. heh.
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_Staff $Staff
 * @author Varun Shoor
 */
class View_View extends SWIFT_View {
    /**
     * Render the Ticket View Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketView $_SWIFT_TicketViewObject The SWIFT_TicketView Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketView $_SWIFT_TicketViewObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');
        $_ticketTypeCache = (array) $this->Cache->Get('tickettypecache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/View/EditSubmit/' . $_SWIFT_TicketViewObject->GetTicketViewID(),
                    SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/View/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_viewTitle = '';

        $_viewTicketsPerPage = 20;
        $_viewAutoRefresh = 0;
        $_viewSetAsOwner = true;

        $_viewDefaultStatusOnReply = false;

        $_viewUnassigned = true;
        $_viewAssigned = true;
        $_viewAllTickets = false;

        $_viewAfterReplyAction = SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST;

        $_viewSortBy = SWIFT_TicketViewField::FIELD_LASTACTIVITY;
        $_viewSortOrder = SWIFT_TicketView::SORT_ASC;

        $_viewScope = SWIFT_TicketView::VIEWSCOPE_PRIVATE;

        $_ticketViewFields = array();
        if (isset($_POST['viewfields']) && _is_array($_POST['viewfields'])) {
            $_ticketViewFields = $_POST['viewfields'];
        } else if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_ticketViewFields = array(SWIFT_TicketViewField::FIELD_TICKETID, SWIFT_TicketViewField::FIELD_SUBJECT);
        }

        $_linkedDepartmentIDList = array();
        $_filterDepartmentIDList = $_filterTicketTypeIDList = $_filterTicketStatusIDList = $_filterTicketPriorityIDList = array();

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/View/Delete/' .
                    $_SWIFT_TicketViewObject->GetTicketViewID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketviews'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_viewTitle = $_SWIFT_TicketViewObject->GetProperty('title');
            $_viewUnassigned = (int) ($_SWIFT_TicketViewObject->GetProperty('viewunassigned'));
            $_viewAssigned = (int) ($_SWIFT_TicketViewObject->GetProperty('viewassigned'));
            $_viewAllTickets = (int) ($_SWIFT_TicketViewObject->GetProperty('viewalltickets'));

            $_viewScope = (int) ($_SWIFT_TicketViewObject->GetProperty('viewscope'));
            $_viewTicketsPerPage = (int) ($_SWIFT_TicketViewObject->GetProperty('ticketsperpage'));
            $_viewAutoRefresh = (int) ($_SWIFT_TicketViewObject->GetProperty('autorefresh'));
            $_viewSetAsOwner = (int) ($_SWIFT_TicketViewObject->GetProperty('setasowner'));
            $_viewDefaultStatusOnReply = (int) ($_SWIFT_TicketViewObject->GetProperty('defaultstatusonreply'));
            $_viewAfterReplyAction = (int) ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction'));

            $_viewSortBy = (int) ($_SWIFT_TicketViewObject->GetProperty('sortby'));
            $_viewSortOrder = (int) ($_SWIFT_TicketViewObject->GetProperty('sortorder'));

            $_ticketViewFieldsContainer = SWIFT_TicketViewField::RetrieveOnTicketView($_SWIFT_TicketViewObject->GetTicketViewID());
            foreach ($_ticketViewFieldsContainer as $_key => $_val) {
                $_fieldPrefix = '';
                if ($_val['fieldtype'] == SWIFT_TicketViewField::TYPE_CUSTOM) {
                    $_fieldPrefix = 'c_';
                }

                $_ticketViewFields[] = $_fieldPrefix . $_val['fieldtypeid'];
            }

            $_ticketViewLinksContainer = SWIFT_TicketViewLink::RetrieveOnTicketView($_SWIFT_TicketViewObject->GetTicketViewID());

            if (isset($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_DEPARTMENT])) {
                foreach ($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_DEPARTMENT] as $_key => $_val) {
                    $_linkedDepartmentIDList[] = $_val['linktypeid'];
                }
            }

            if (isset($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERDEPARTMENT])) {
                foreach ($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERDEPARTMENT] as $_key => $_val) {
                    $_filterDepartmentIDList[] = $_val['linktypeid'];
                }
            }

            if (isset($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERPRIORITY])) {
                foreach ($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERPRIORITY] as $_key => $_val) {
                    $_filterTicketPriorityIDList[] = $_val['linktypeid'];
                }
            }

            if (isset($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERSTATUS])) {
                foreach ($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERSTATUS] as $_key => $_val) {
                    $_filterTicketStatusIDList[] = $_val['linktypeid'];
                }
            }

            if (isset($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERTYPE])) {
                foreach ($_ticketViewLinksContainer[SWIFT_TicketViewLink::LINK_FILTERTYPE] as $_key => $_val) {
                    $_filterTicketTypeIDList[] = $_val['linktypeid'];
                }
            }
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketviews'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('viewtitle'), $this->Language->Get('desc_viewtitle'), $_viewTitle);

        $_checkboxContainer = array();
        $_checkboxContainer[0]['title'] = $this->Language->Get('viewunassigned');
        $_checkboxContainer[0]['value'] = SWIFT_TicketView::VIEW_UNASSIGNED;
        $_checkboxContainer[0]['checked'] = $_viewUnassigned;

        $_checkboxContainer[1]['title'] = $this->Language->Get('viewassigned');
        $_checkboxContainer[1]['value'] = SWIFT_TicketView::VIEW_ASSIGNED;
        $_checkboxContainer[1]['checked'] = $_viewAssigned;

        $_checkboxContainer[2]['title'] = $this->Language->Get('viewalltickets');
        $_checkboxContainer[2]['value'] = SWIFT_TicketView::VIEW_ALLTICKETS;
        $_checkboxContainer[2]['checked'] = $_viewAllTickets;

        $_GeneralTabObject->CheckBoxList('viewtype', $this->Language->Get('viewassignedfield'), $this->Language->Get('desc_viewassignedfield'),
                $_checkboxContainer);



        $_radioContainer = array();
        $_index = 0;
        foreach (array(SWIFT_TicketView::VIEWSCOPE_GLOBAL, SWIFT_TicketView::VIEWSCOPE_PRIVATE, SWIFT_TicketView::VIEWSCOPE_TEAM) as
                $_key => $_currentViewScope) {
            $_radioContainer[$_index]['title'] = SWIFT_TicketView::GetViewScopeLabel($_currentViewScope);
            $_radioContainer[$_index]['value'] = $_currentViewScope;

            if ($_viewScope == $_currentViewScope) {
                $_radioContainer[$_index]['checked'] = true;
            }

            $_index++;
        }
        $_GeneralTabObject->Radio('viewscope', $this->Language->Get('viewscope'), $this->Language->Get('desc_viewscope'), $_radioContainer,
                false);


        $_checkboxContainer = array();
        $_assignedDepartmentIDList = $this->Staff->GetAssignedDepartments(APP_TICKETS);
        $_departmentMap =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        /**
         * @var int $_departmentID
         * @var array $_departmentContainer
         */
        foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
            if ($_departmentContainer['departmentapp'] != APP_TICKETS || !in_array($_departmentID, $_assignedDepartmentIDList)) {
                continue;
            }

            $_isChecked = false;
            if (in_array($_departmentID, $_linkedDepartmentIDList)) {
                $_isChecked = true;
            }

            $_checkboxContainer[] = array('title' => text_to_html_entities($_departmentContainer['title']), 'value' =>  ($_departmentID),
                'icon' => SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif', 'checked' => $_isChecked);

            if (_is_array($_departmentContainer['subdepartments'])) {
                foreach ($_departmentContainer['subdepartments'] as $_subDepartmentID => $_subDepartmentContainer) {
                    if (!in_array($_subDepartmentID, $_assignedDepartmentIDList)) {
                        continue;
                    }

                    $_isChecked = false;
                    if (in_array($_subDepartmentID, $_linkedDepartmentIDList)) {
// @codeCoverageIgnoreStart
// this code will never be executed
                        $_isChecked = true;
                    }
// @codeCoverageIgnoreEnd

                    $_checkboxContainer[] = array('title' => '<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif' . '" border="0" /> ' . text_to_html_entities($_subDepartmentContainer['title']), 'value' => (int) ($_subDepartmentID),
                        'icon' => SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif', 'checked' => $_isChecked);
                }
            }
        }

        $_GeneralTabObject->CheckBoxContainerList('linkdepartmentid', $this->Language->Get('viewlinkdepartment'),
                $this->Language->Get('desc_viewlinkdepartment'), $_checkboxContainer, false, true);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN OPTIONS TAB
         * ###############################################
         */
        $_OptionsTabObject = $this->UserInterface->AddTab($this->Language->Get('taboptions'), 'icon_settings2.gif', 'options', false);

        $_OptionsTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');
        $_OptionsTabObject->Number('ticketsperpage', $this->Language->Get('viewticketsperpage'), $this->Language->Get('desc_viewticketsperpage'),
            $_viewTicketsPerPage);

        $_optionsContainer = array();
        $_index = 0;
        foreach (array('0' => 'autorefresh_disable', '30' => 'autorefresh_30s', '60' => 'autorefresh_1m',
            '300' => 'autorefresh_5m', '900' => 'autorefresh_15m', '1800' => 'autorefresh_30m', '3600' => 'autorefresh_1h') as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $this->Language->Get($_val);
            $_optionsContainer[$_index]['value'] =  ($_key);

            if ($_key == $_viewAutoRefresh) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }
        $_OptionsTabObject->Select('autorefresh', $this->Language->Get('viewautorefresh'), $this->Language->Get('desc_viewautorefresh'),
            $_optionsContainer);

        $_OptionsTabObject->YesNo('setasowner', $this->Language->Get('viewsetasowner'), $this->Language->Get('desc_viewsetasowner'),
                $_viewSetAsOwner);

        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('defaultstatus_unspecified');
        $_optionsContainer[0]['value'] = '0';
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_optionsContainer[0]['selected'] = true;
        }

        foreach ($_ticketStatusCache as $_ticketStatusID => $_ticketStatusContainer) {
            if ($_ticketStatusContainer['departmentid'] != '0') {
                continue;
            }

            $_optionsContainer[$_index]['title'] = htmlspecialchars($_ticketStatusContainer['title']);
            $_optionsContainer[$_index]['value'] =  ($_ticketStatusID);

            if ($_ticketStatusID == $_viewDefaultStatusOnReply) {
// @codeCoverageIgnoreStart
// this code will never be executed
                $_optionsContainer[$_index]['selected'] = true;
            }
// @codeCoverageIgnoreEnd

            $_index++;
        }

        $_OptionsTabObject->Select('defaultstatusonreply', $this->Language->Get('viewdefaultstatusonreply'),
                $this->Language->Get('desc_viewdefaultstatusonreply'), $_optionsContainer);

        $_optionsContainer = array();
        $_index = 0;
        foreach (array(SWIFT_TicketView::AFTERREPLY_TOPTICKETLIST, SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST,
            SWIFT_TicketView::AFTERREPLY_TICKET, SWIFT_TicketView::AFTERREPLY_NEXTTICKET) as $_key => $_afterReplyAction) {
            $_optionsContainer[$_index]['title'] = SWIFT_TicketView::GetAfterReplyActionLabel($_afterReplyAction);
            $_optionsContainer[$_index]['value'] = $_afterReplyAction;

            if ($_viewAfterReplyAction == $_afterReplyAction) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_OptionsTabObject->Select('afterreplyaction', $this->Language->Get('viewafterreplyaction'),
                $this->Language->Get('desc_viewafterreplyaction'), $_optionsContainer);

        /*
         * ###############################################
         * END OPTIONS TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN COLUMNS TAB
         * ###############################################
         */
        $_fieldsContainer = SWIFT_TicketViewField::GetFieldContainer();

        $_ColumnsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcolumns'), 'icon_ticketviewcolumn.png', 'columns');

        $_ColumnsTabObject->Title($this->Language->Get('viewsortoptions'), 'icon_doublearrows.gif');

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_fieldsContainer as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = htmlspecialchars($_val['title']);
            $_optionsContainer[$_index]['value'] = $_key;

            if ($_key == $_viewSortBy) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_ColumnsTabObject->Select('sortby', $this->Language->Get('viewsortby'), $this->Language->Get('desc_viewsortby'), $_optionsContainer);

        $_optionsContainer = array();
        $_index = 0;
        foreach (array(SWIFT_TicketView::SORT_ASC, SWIFT_TicketView::SORT_DESC) as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = SWIFT_TicketView::GetSortOrderLabel($_val);
            $_optionsContainer[$_index]['value'] = $_val;

            if ($_val == $_viewSortOrder) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }
        $_ColumnsTabObject->Select('sortorder', $this->Language->Get('viewsortorder'), $this->Language->Get('desc_viewsortorder'),
                $_optionsContainer);

        $_viewFieldsHTML = '';
        foreach ($_ticketViewFields as $_key => $_val) {
            if (!isset($_fieldsContainer[$_val])) {
                continue;
            }

            $_fieldContainer = $_fieldsContainer[$_val];

            $_viewFieldsHTML .= '<li id="' . $_val . '">' . $_fieldContainer['title'] . '<input type="hidden" name="viewfields[]" value="' .
                $_val . '" /></li>';
        }


        $_ColumnsTabObject->Title($this->Language->Get('viewselectfields'), 'icon_doublearrows.gif');
        $_rowContainer = array();
        $_rowContainer[0]['value'] = '<div class="ticketviewcolumncontainer"><ul id="ticketviewfielddragtarget">' . $_viewFieldsHTML . '</ul></div>';
        $_rowContainer[0]['align'] = 'left';
        $_rowContainer[0]['valign'] = 'middle';
        $_rowContainer[0]['colspan'] = '2';
        $_ColumnsTabObject->Row($_rowContainer);

        $_defaultFieldContainerHTML = '<ul class="ticketviewfielddragcontainer">';

        foreach ($_fieldsContainer as $_key => $_fieldContainer) {
            if (in_array($_key, $_ticketViewFields)) {
                continue;
            }

            $_defaultFieldContainerHTML .= '<li id="' . $_key . '">' . $_fieldContainer['title'] . '</li>';
        }

        $_defaultFieldContainerHTML .= '</ul>';


        $_rowContainer = array();
        $_rowContainer[0]['value'] = $_defaultFieldContainerHTML;
        $_rowContainer[0]['align'] = 'left';
        $_rowContainer[0]['valign'] = 'middle';
        $_rowContainer[0]['colspan'] = '2';
        $_ColumnsTabObject->Row($_rowContainer);

        $_renderHTML = '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'QueueFunction(function(){';
        $_renderHTML .= 'EnableViewSorting();';
        $_renderHTML .= '});</script>';

        $_ColumnsTabObject->RowHTML($_renderHTML);

        /*
         * ###############################################
         * END FIELDS TAB
         * ###############################################
         */


        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket View Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketviewgrid'), true, false, 'base');

        $_viewScopeSQL = " (ticketviews.viewscope = '" . SWIFT_TicketView::VIEWSCOPE_GLOBAL . "' OR
            (ticketviews.viewscope = '" . SWIFT_TicketView::VIEWSCOPE_TEAM . "' AND staffgroup.staffgroupid = '" .
                (int) ($this->Staff->GetProperty('staffgroupid')) . "') OR (ticketviews.viewscope = '" . SWIFT_TicketView::VIEWSCOPE_PRIVATE .
                        "' AND ticketviews.staffid = '" . $this->Staff->GetStaffID() . "'))";

        $_extendedSQL = " LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (ticketviews.staffid = staff.staffid) LEFT JOIN " . TABLE_PREFIX .
            "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)";

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT ticketviews.* FROM ' . TABLE_PREFIX . 'ticketviews AS ticketviews' . $_extendedSQL .
                    ' WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('ticketviews.title') . ') AND ' . $_viewScopeSQL,
                    'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'ticketviews AS ticketviews' . $_extendedSQL . ' WHERE (' .
                    $this->UserInterfaceGrid->BuildSQLSearch('ticketviews.title') . ') AND ' . $_viewScopeSQL);
        }

        $this->UserInterfaceGrid->SetQuery("SELECT ticketviews.* FROM " . TABLE_PREFIX . "ticketviews AS ticketviews" . $_extendedSQL .
                ' WHERE ' . $_viewScopeSQL, "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketviews AS ticketviews" . $_extendedSQL .
                ' WHERE ' . $_viewScopeSQL);

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketviewid', 'ticketviewid',
                SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketviews.title', $this->Language->Get('viewtitle'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketviews.viewscope', $this->Language->Get('viewscope'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketviews.staffid', $this->Language->Get('createdby'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Staff\Controller_View', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/View/Insert');

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

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        $_fieldContainer['icon'] = '<i class="fa fa-th-list" aria-hidden="true"></i>';

        $_fieldContainer['ticketviews.title'] = IIF($_fieldContainer['ismaster'] == '1', '<em>'). '<a href="' . SWIFT::Get('basename') . '/Tickets/View/Edit/' . (int) ($_fieldContainer['ticketviewid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>' . IIF($_fieldContainer['ismaster'] == '1', '</em>');

        $_fieldContainer['ticketviews.viewscope'] = SWIFT_TicketView::GetViewScopeLabel($_fieldContainer['viewscope']);

        if (isset($_staffCache[$_fieldContainer['staffid']])) {
            $_fieldContainer['ticketviews.staffid'] = text_to_html_entities($_staffCache[$_fieldContainer['staffid']]['fullname']);
        } else {
            $_fieldContainer['ticketviews.staffid'] = $_SWIFT->Language->Get('na');
        }

        return $_fieldContainer;
    }
}
