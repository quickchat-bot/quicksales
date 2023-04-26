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
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\Rules\SWIFT_Rules;
use Tickets\Models\Filter\SWIFT_TicketFilter;
use Tickets\Library\Search\SWIFT_TicketSearch;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Ticket Filter View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Filter extends SWIFT_View
{
    /**
     * Render the Ticket Filter Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_TicketFilter $_SWIFT_TicketFilterObject The SWIFT_TicketFilter Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_TicketFilter $_SWIFT_TicketFilterObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_criteriaPointer = SWIFT_TicketSearch::GetCriteriaPointer();
        SWIFT_TicketSearch::ExtendCustomCriteria($_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        if (isset($_POST['rulecriteria']))
        {
            SWIFT_TicketSearch::CriteriaActionsPointerToJavaScript($_POST['rulecriteria'], array());
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Filter/EditSubmit/' . $_SWIFT_TicketFilterObject->GetTicketFilterID(),
                    SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/Filter/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_filterTitle = '';
        $_restrictStaffGroupID = 0;
        $_filterType = SWIFT_TicketFilter::TYPE_PUBLIC;
        $_criteriaOptions = SWIFT_Rules::RULE_MATCHALL;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/Filter/Delete/' .
                    $_SWIFT_TicketFilterObject->GetTicketFilterID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketfilter'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_filterTitle = $_SWIFT_TicketFilterObject->GetProperty('title');

            $_restrictStaffGroupID = (int) ($_SWIFT_TicketFilterObject->GetProperty('restrictstaffgroupid'));
            $_filterType = (int) ($_SWIFT_TicketFilterObject->GetProperty('filtertype'));
            $_criteriaOptions = (int) ($_SWIFT_TicketFilterObject->GetProperty('criteriaoptions'));
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketfilter'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_funnel.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('filtertitle'), $this->Language->Get('desc_filtertitle'), $_filterTitle);

        $_GeneralTabObject->PublicPrivate('filtertype', $this->Language->Get('filtertype'), $this->Language->Get('desc_filtertype'), (int) ($_filterType), 'HandleFilterTypeToggle();');

        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('reststaffgroupall');
        $_optionsContainer[0]['value'] = 0;
        if ($_restrictStaffGroupID == 0)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['staffgroupid'];

            if ($this->Database->Record['staffgroupid'] == $_restrictStaffGroupID)
            {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('restrictstaffgroupid', $this->Language->Get('restrictstaffgroupfil'), $this->Language->Get('desc_restrictstaffgroupfil'), $_optionsContainer);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('smatchall');
        $_optionsContainer[0]['value'] = SWIFT_Rules::RULE_MATCHALL;

        if ($_criteriaOptions == SWIFT_Rules::RULE_MATCHALL)
        {
            $_optionsContainer[0]['checked'] = true;
        }

        $_optionsContainer[1]['title'] = $this->Language->Get('smatchany');
        $_optionsContainer[1]['value'] = SWIFT_Rules::RULE_MATCHANY;
        if ($_criteriaOptions == SWIFT_Rules::RULE_MATCHANY)
        {
            $_optionsContainer[1]['checked'] = true;
        }

        $_GeneralTabObject->Radio('criteriaoptions', $this->Language->Get('matchtype'), $this->Language->Get('desc_matchtype'), $_optionsContainer);

        $_appendHTML = '<tr id="tabtoolbar"><td align="left" valign="top" colspan="2" class="settabletitlerowmain2"><div class="tabtoolbarsub"><ul><li><a href="javascript:void(0);" onmouseup="javascript:this.blur(); newGlobalRuleCriteria(\'message\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\');"><i class="fa fa-plus-circle"></i>' . $this->Language->Get('insertcriteria') . '</a></li></ul></div></td>';

        $_appendHTML .= '<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>';

        $_javascriptAppendHTML = '<script type="text/javascript">QueueFunction(function(){ HandleFilterTypeToggle(); ';
        if (!isset($_POST['rulecriteria']) && $_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            $_javascriptAppendHTML .= 'newGlobalRuleCriteria(\'message\', \'' . SWIFT_Rules::OP_CONTAINS . '\', \'\');';
        }
        $_javascriptAppendHTML .= ' });</script>';

        $_GeneralTabObject->AppendHTML($_appendHTML . $_javascriptAppendHTML);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Filter Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketfiltergrid'), true, false, 'base');

        $_sqlExtended = "((filtertype = '" . SWIFT_TicketFilter::TYPE_PUBLIC . "' AND restrictstaffgroupid = '0')
            OR (filtertype = '" . SWIFT_TicketFilter::TYPE_PUBLIC . "' AND restrictstaffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
            OR (filtertype = '" . SWIFT_TicketFilter::TYPE_PRIVATE . "' AND staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
            OR (staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "'))";

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketfilters
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ') AND ' . $_sqlExtended,

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'ticketfilters
                        WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ') AND ' . $_sqlExtended);
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'ticketfilters WHERE ' . $_sqlExtended,

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'ticketfilters WHERE ' . $_sqlExtended);

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ticketfilterid', 'ticketfilterid',
                SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
                SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('title', $this->Language->Get('title'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('staffid', $this->Language->Get('filtercreator'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('filtertype', $this->Language->Get('filtertype'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('lastactivity', $this->Language->Get('lastused'),
                SWIFT_UserInterfaceGridField::TYPE_DB, 220, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Staff\Controller_Filter', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/Filter/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        $_javascriptAppendHTML = '<script type="text/javascript">QueueFunction(function(){';
        $_javascriptAppendHTML .= 'ReloadTicketFilterMenu();';
        $_javascriptAppendHTML .= '});</script>';

        echo $_javascriptAppendHTML;


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

        $_fieldContainer['icon'] = '<i class="fa fa-filter" aria-hidden="true"></i>';

        $_fieldContainer['title'] = '<a href="' . SWIFT::Get('basename') . "/Tickets/Filter/Edit/" . $_fieldContainer['ticketfilterid'] . '" viewport="1">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        if ($_fieldContainer['lastactivity'] == '0')
        {
            $_fieldContainer['lastactivity'] = $_SWIFT->Language->Get('filternotused');
        } else {
            $_fieldContainer['lastactivity'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['lastactivity']) . ' (' . SWIFT_Date::ColorTime(DATENOW-$_fieldContainer['lastactivity']) . ')';
        }

        $_fieldContainer['filtertype'] = IIF($_fieldContainer['filtertype'] == SWIFT_TicketFilter::TYPE_PUBLIC, $_SWIFT->Language->Get('public'), $_SWIFT->Language->Get('private'));

        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        if (isset($_staffCache[$_fieldContainer['staffid']]))
        {
            $_fieldContainer['staffid'] = text_to_html_entities($_staffCache[$_fieldContainer['staffid']]['fullname']);
        } else {
            $_fieldContainer['staffid'] = $_SWIFT->Language->Get('na');
        }

        return $_fieldContainer;
    }

    /**
     * Render the Ticket Filter Menu
     *
     * @author Varun Shoor
     * @param array $_ticketFilterContainer The Ticket Filter Container
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderMenu($_ticketFilterContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<ul class="swiftdropdown" id="ticketfiltermenu" style="display: none;">';
        $_renderHTML .= '<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData(\'/Tickets/Filter/Manage\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemimage"><img src="' . SWIFT::Get('themepath') . 'images/menu_ticketfilters.png" align="absmiddle" border="0" /></div><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('smanage') . '</div></div></li>';
        $_renderHTML .= '<li class="seperator"></li>';

        foreach ($_ticketFilterContainer as $_ticketFilter)
        {
            $_renderHTML .= '<li class="swiftdropdownitemparent" onclick="javascript: loadViewportData(\'/Tickets/Search/Filter/' . (int) ($_ticketFilter['ticketfilterid']) . '\');"><div class="swiftdropdownitem"><div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $_ticketFilter['title'] . '</div></div></li>';
        }

        $_renderHTML .= '</ul>';

        return $_renderHTML;
    }
}
