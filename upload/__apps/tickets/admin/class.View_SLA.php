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
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Models\SLA\SWIFT_SLA;

/**
 * The SLA View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_SLA extends SWIFT_View
{
    /**
     * Render the SLA Plan
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_SLA $_SWIFT_SLAObject The SWIFT_SLA Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_SLA $_SWIFT_SLAObject = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_criteriaPointer = SWIFT_SLA::GetCriteriaPointer();
        SWIFT_SLA::ExtendCustomCriteria($_criteriaPointer);
        SWIFT_Rules::CriteriaPointerToJavaScriptArray($_criteriaPointer);

        if (isset($_POST['rulecriteria']))
        {
            SWIFT_SLA::CriteriaActionsPointerToJavaScript($_POST['rulecriteria'], array());
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/SLA/EditSubmit/'. $_SWIFT_SLAObject->GetSLAPlanID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/SLA/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
        }

        $_slaPlanTitle = '';
        $_slaPlanOverdueHours = 12;
        $_slaPlanResolutionDueHours = 48;
        $_slaRuleType = SWIFT_Rules::RULE_MATCHALL;
        $_isEnabled = true;

        $_sortOrderContainer = $this->Database->QueryFetch("SELECT sortorder FROM " . TABLE_PREFIX . "slaplans ORDER BY sortorder DESC");
        $_sortOrder = 1;

        if (!isset($_sortOrderContainer['sortorder']) || empty($_sortOrderContainer['sortorder']))
        {
            $_sortOrder = 1;
        } else {
            $_sortOrder = (int) ($_sortOrderContainer['sortorder']) + 1;
        }

        $_slaHolidayIDList = array();

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/SLA/Delete/' . $_SWIFT_SLAObject->GetSLAPlanID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('sla'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_slaPlanTitle = $_SWIFT_SLAObject->GetProperty('title');
            $_slaPlanOverdueHours = floatval($_SWIFT_SLAObject->GetProperty('overduehrs'));
            $_slaPlanResolutionDueHours = floatval($_SWIFT_SLAObject->GetProperty('resolutionduehrs'));
            $_slaRuleType = (int) ($_SWIFT_SLAObject->GetProperty('ruletype'));
            $_sortOrder = (int) ($_SWIFT_SLAObject->GetProperty('sortorder'));
            $_isEnabled = (int) ($_SWIFT_SLAObject->GetProperty('isenabled'));
            $_POST['slascheduleid'] = (int) ($_SWIFT_SLAObject->GetProperty('slascheduleid'));

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaholidaylinks WHERE slaplanid = '" . (int) ($_SWIFT_SLAObject->GetSLAPlanID()) . "'");
            while ($this->Database->NextRecord())
            {
                $_slaHolidayIDList[] = $this->Database->Record['slaholidayid'];
            }
        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('sla'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }



        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);


        $_GeneralTabObject->Text('title', $this->Language->Get('plantitle'), $this->Language->Get('desc_plantitle'), $_slaPlanTitle);

        $_GeneralTabObject->Number('overduehrs', $this->Language->Get('overduehrs'), $this->Language->Get('desc_overduehrs'), $_slaPlanOverdueHours);

        $_GeneralTabObject->Number('resolutionduehrs', $this->Language->Get('resolutionduehrs'), $this->Language->Get('desc_resolutionduehrs'),
                $_slaPlanResolutionDueHours);

        // ======= Render SLA Schedules =======
        $_index = 0;
        $_optionContainer = array();
        $this->Database->Query("SELECT slascheduleid, title FROM " . TABLE_PREFIX . "slaschedules ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionContainer[$_index]['value'] = $this->Database->Record['slascheduleid'];
            $_index++;
        }

        if (!count($_optionContainer))
        {
            $_optionContainer[$_index]['title'] = $this->Language->Get('noscheduleavailable');
            $_optionContainer[$_index]['value'] = '';
        }

        $_GeneralTabObject->Select('slascheduleid', $this->Language->Get('planschedule'), $this->Language->Get('desc_planschedule'), $_optionContainer);
        $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_isEnabled);

        // Sort Order
        $_GeneralTabObject->Number('sortorder', $this->Language->Get('sortorder'), $this->Language->Get('desc_sortorder'), $_sortOrder);

        $_GeneralTabObject->Hidden('ruleoptions', SWIFT_Rules::RULE_MATCHEXTENDED);

        $_departmentCache = (array) $this->Cache->Get('departmentcache');
        $_departmentID = false;
        foreach ($_departmentCache as $_key => $_val)
        {
            if ($_val['departmentapp'] == APP_TICKETS)
            {
                $_departmentID = ($_key);
                break;
            }
        }

        $_appendHTML = '<tr id="tabtoolbar"><td align="left" valign="top" colspan="2" class="settabletitlerowmain2"><div class="tabtoolbarsub"><ul><li><a href="javascript:void(0);" onmouseup="javascript:this.blur(); newGlobalRuleCriteria(\'departmentid\', \'' . SWIFT_Rules::OP_EQUAL . '\', \'' . (int) ($_departmentID) . ', \', \'1\', \'1\');"><img border="0" align="absmiddle" src="' . SWIFT::Get('themepath') . 'images/icon_insertcriteria.gif' . '" /> ' . $this->Language->Get('insertcriteria') . '</a></li></ul></div></td>';

        $_appendHTML .= '<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="ruleparent"></div></td></tr>';
        $_appendHTML .= '<script>function splitValue(s){var e=parseFloat(s.val()).toFixed(2).split(".");e[0]=e[1]>=60?parseInt(e[0])+1:parseInt(e[0]);e[1]=e[1]>=60?parseInt(e[1])-60:parseInt(e[1]);if(isNaN(e[0])||isNaN(e[1]))return "";s.val(e.join("."));return e[0]+"h "+e[1]+"m"}$("#overduehrs").focusout(function(){$("#overduehrs_span")&&$("#overduehrs_span").remove(),$(this).after("<span id=\'overduehrs_span\' style=\'padding-left:5px\'>"+splitValue($(this))+"</span>")}),$("#resolutionduehrs").focusout(function(){$("#resolutionduehrs_span")&&$("#resolutionduehrs_span").remove(),$(this).after("<span id=\'resolutionduehrs_span\' style=\'padding-left:5px\'>"+splitValue($(this))+"</span>")});</script>';

        $_GeneralTabObject->AppendHTML($_appendHTML);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN SLA HOLIDAYS TAB
         * ###############################################
         */
        $_SLAHolidayTabObject = $this->UserInterface->AddTab($this->Language->Get('tabholidays'), 'icon_calendar.svg', 'slaholidays');

        $_slaHolidayContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaholidays WHERE iscustom = '1'");
        while ($this->Database->NextRecord())
        {
            $_slaHolidayContainer[$this->Database->Record['slaholidayid']] = $this->Database->Record;
        }

        if (!_is_array($_slaHolidayContainer))
        {
            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['class'] = 'settabletitlerowmain2';
            $_columnContainer[0]['colspan'] = '2';
            $_columnContainer[0]['value'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_block.gif" align="absmiddle" border="0" /> ' . $this->Language->Get('nocustomholidays');
            $_SLAHolidayTabObject->Row($_columnContainer);
        } else {
            foreach ($_slaHolidayContainer as $_key => $_val)
            {
                $_isChecked = false;
                if ($_mode == SWIFT_UserInterface::MODE_INSERT && !isset($_POST['slaholidays'][$_key]))
                {
                    $_isChecked = true;
                } else if (isset($_POST['slaholidays'][$_key]) && $_POST['slaholidays'][$_key] == 1) {
                    $_isChecked = true;
                } else if (in_array($_key, $_slaHolidayIDList)) {
                    $_isChecked = true;
                }

                $_SLAHolidayTabObject->YesNo('slaholidays[' . (int) ($_val['slaholidayid']) . ']', IIF(!empty($_val['flagicon']), '<img src="' . str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_val['flagicon']) . '" align="absmiddle" border="0" /> ') . htmlspecialchars($_val['title']), '', $_isChecked);
            }
        }

        if (!isset($_POST['rulecriteria']))
        {
            $_SLAHolidayTabObject->AppendHTML('<script type="text/javascript">QueueFunction(function(){ newGlobalRuleCriteria(\'departmentid\', \'' . SWIFT_Rules::OP_EQUAL . '\', \'' . $_departmentID . '\', \'1\', \'1\'); });</script>');
        }

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Ticket Rating Grid
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

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('slaplangrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT slaplans.*, slaschedules.title AS scheduletitle FROM ' . TABLE_PREFIX . 'slaplans AS slaplans LEFT JOIN ' . TABLE_PREFIX . 'slaschedules AS slaschedules ON (slaplans.slascheduleid = slaschedules.slascheduleid) WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('slaplans.title') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'slaplans WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('title') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT slaplans.*, slaschedules.title AS scheduletitle FROM ' . TABLE_PREFIX . 'slaplans AS slaplans LEFT JOIN ' . TABLE_PREFIX . 'slaschedules AS slaschedules ON (slaplans.slascheduleid = slaschedules.slascheduleid)', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'slaplans');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slaplanid', 'slaplanid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slaplans.title', $this->Language->Get('plantitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slaschedules.title', $this->Language->Get('scheduletitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 160, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slaplans.overduehrs', $this->Language->Get('overduehrs'), SWIFT_UserInterfaceGridField::TYPE_DB, 110, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('slaplans.resolutionduehrs', $this->Language->Get('resolutionduehrs2'), SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_CENTER));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Tickets\Admin\Controller_SLA', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle',
                array('Tickets\Admin\Controller_SLA', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle',
                array('Tickets\Admin\Controller_SLA', 'DisableList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Tickets/SLA/Insert');

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
     */
    public static function GridRender($_fieldContainer)
    {
        $_fieldContainer['slaplans.title'] = '<a href="' . SWIFT::Get('basename') . '/Tickets/SLA/Edit/' . (int) ($_fieldContainer['slaplanid']) . '" viewport="1" title="' . addslashes(htmlspecialchars($_fieldContainer['title'])) . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';
        $_fieldContainer['slaschedules.title'] = htmlspecialchars($_fieldContainer['scheduletitle']);
        $_fieldContainer['slaplans.overduehrs'] = floatval($_fieldContainer['overduehrs']);
        $_fieldContainer['slaplans.resolutionduehrs'] = floatval($_fieldContainer['resolutionduehrs']);

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' .
        IIF($_fieldContainer['isenabled'] == '0', 'icon_block.gif', 'icon_sla.gif') . '" align="absmiddle" border="0" />';

        return $_fieldContainer;
    }
}
