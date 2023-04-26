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

namespace LiveChat\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\Department\SWIFT_Department;
use SWIFT;
use SWIFT_View;

/**
 * The Message Routing View Management Class
 *
 * @author Varun Shoor
 */
class View_MessageRouting extends SWIFT_View
{
    /**
     * Render the Message Routing User Interface
     *
     * @author Varun Shoor
     * @param array $_messageRoutingData The Message Routing Data
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_messageRoutingData)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/LiveChat/MessageRouting/IndexSubmit', SWIFT_UserInterface::MODE_INSERT, false);

        $_MessageRoutingTabObject = $this->UserInterface->AddTab($this->Language->Get('messagerouting'), 'icon_messagerouting.gif', 'messagerouting', true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('messagerouting'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_outputHTML = '<div><table cellpadding="0" cellspacing="0" border="0" width="100%" class="gridlayoutborder" style="margin-left: -1px; margin-top: -1px;">' . SWIFT_CRLF;
        $_outputHTML .= '<tbody><tr><td class="gridcontentborder">';
        $_outputHTML .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';

        $_columnContanier[0]['width'] = '200';
        $_columnContainer[0]['class'] = 'gridtabletitlerow';
        $_columnContainer[0]['value'] = $this->Language->Get('mrdepartment');
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['class'] = 'gridtabletitlerow';
        $_columnContainer[1]['value'] = $this->Language->Get('mraction');
        $_outputHTML .= $_MessageRoutingTabObject->Row($_columnContainer);

        $_ticketDepartmentList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentapp = '" . APP_TICKETS . "' ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_ticketDepartmentList[$this->Database->Record['departmentid']] = $this->Database->Record;
        }

        $_ticketDepartmentMap = SWIFT_Department::GetDepartmentMap(APP_TICKETS);

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentapp = '" . APP_LIVECHAT . "' ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_optionsList = '';
            $_index = 0;

            foreach ($_ticketDepartmentMap as $_ticketDepartmentID => $_ticketDepartmentContainer) {
                $_optionsList .= '<option value="' . (int)($_ticketDepartmentID) . '"';
                if ((!isset($_messageRoutingData[$this->Database->Record['departmentid']]) && $_index == 1)
                    || (isset($_messageRoutingData[$this->Database->Record['departmentid']]) && $_messageRoutingData[$this->Database->Record['departmentid']]['ticketdepartmentid'] == $_ticketDepartmentID)) {
                    $_optionsList .= ' selected';
                }

                $_optionsList .= '>' . text_to_html_entities($_ticketDepartmentContainer['title']) . '</option>';
                $_index++;

                foreach ($_ticketDepartmentContainer['subdepartments'] as $_ticketSubDepartmentID => $_ticketSubDepartmentContainer) {
                    $_optionsList .= '<option value="' . (int)($_ticketSubDepartmentID) . '"';
                    if ((!isset($_messageRoutingData[$this->Database->Record['departmentid']]) && $_index == 1)
                        || (isset($_messageRoutingData[$this->Database->Record['departmentid']]) && $_messageRoutingData[$this->Database->Record['departmentid']]['ticketdepartmentid'] == $_ticketSubDepartmentID)) {
                        $_optionsList .= ' selected';
                    }

                    $_optionsList .= '>' . text_to_html_entities($_ticketSubDepartmentContainer['title']) . '</option>';
                    $_index++;

                }
            }

            $_columnContainer[0]['width'] = '200';
            $_columnContainer[0]['class'] = 'gridrow1';
            $_columnContainer[0]['valign'] = 'top';
            $_columnContainer[0]['value'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_livesupport.gif" align="absmiddle" border="0" /> ' . htmlspecialchars($this->Database->Record['title']);
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[1]['class'] = 'gridrow2';

            $_rowHTML = '<div style="PADDING: 12px;"><input type="checkbox" id="preservemessage' . $this->Database->Record['departmentid'] . '" name="preservemessage[' . $this->Database->Record['departmentid'] . ']" value="1"';
            if (isset($_messageRoutingData[$this->Database->Record['departmentid']]) && $_messageRoutingData[$this->Database->Record['departmentid']]['preservemessage'] == 1) {
                $_rowHTML .= ' checked';
            }

            $_rowHTML .= ' /> <label for="preservemessage' . $this->Database->Record['departmentid'] . '">' . $this->Language->Get('preservemessage') . '</label></div><fieldset class="swiftfieldset"><legend><input type="checkbox" id="routingtickets' . $this->Database->Record['departmentid'] . '" onclick="toggleRoutingSelectBox(this.checked, \'' . $this->Database->Record['departmentid'] . '\');" name="routetotickets[' . $this->Database->Record['departmentid'] . ']" value="1"';

            if (isset($_messageRoutingData[$this->Database->Record['departmentid']]) && $_messageRoutingData[$this->Database->Record['departmentid']]['routetotickets'] == 1) {
                $_rowHTML .= ' checked';
            }

            $_rowHTML .= ' /> <label for="routingtickets' . $this->Database->Record['departmentid'] . '">' . $this->Language->Get('routetotickets') . '</label></legend>' . $this->Language->Get('mrdepartment') . ': <select class="swiftselect"';

            if (!isset($_messageRoutingData[$this->Database->Record['departmentid']]) || $_messageRoutingData[$this->Database->Record['departmentid']]['routetotickets'] != 1) {
                $_rowHTML .= ' disabled="disabled"';
            }

            $_rowHTML .= ' name="routedepartmentid[' . $this->Database->Record['departmentid'] . ']">' . $_optionsList . '</select></fieldset><br /><fieldset class="swiftfieldset"><legend><input type="checkbox" id="routingemail' . $this->Database->Record['departmentid'] . '" name="routetoemail[' . $this->Database->Record['departmentid'] . ']" onclick="toggleRoutingTextBox(this.checked, \'' . $this->Database->Record['departmentid'] . '\');" value="1"';

            if (isset($_messageRoutingData[$this->Database->Record['departmentid']]) && $_messageRoutingData[$this->Database->Record['departmentid']]['routetoemail'] == 1) {
                $_rowHTML .= ' checked';
            }

            $_rowHTML .= ' /> <label for="routingemail' . $this->Database->Record['departmentid'] . '">' . $this->Language->Get('routetoemail') . '</label></legend>' . $this->Language->Get('mremail') . ': <input type="text"';

            if (!isset($_messageRoutingData[$this->Database->Record['departmentid']]) || $_messageRoutingData[$this->Database->Record['departmentid']]['routetoemail'] != 1) {
                $_rowHTML .= ' disabled="disabled"';
            }

            $_rowHTML .= ' name="emailroute[' . $this->Database->Record['departmentid'] . ']" size="45" class="swifttext" value="';

            if (isset($_messageRoutingData[$this->Database->Record['departmentid']]) && !empty($_messageRoutingData[$this->Database->Record['departmentid']]['forwardemails'])) {
                $_rowHTML .= $_messageRoutingData[$this->Database->Record['departmentid']]['forwardemails'];
            }

            $_rowHTML .= '" /> (' . $this->Language->Get('separatevia') . ')</fieldset>';

            $_columnContainer[1]['value'] = $_rowHTML;

            $_outputHTML .= $_MessageRoutingTabObject->Row($_columnContainer);
        }

        $_outputHTML .= '</table></td></tr></tbody></table></div>';

        $_MessageRoutingTabObject->HTML($_outputHTML);

        $this->UserInterface->End();

    }
}
