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

namespace Tickets\Library\Ajax;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;

/**
 * The AJAX Library
 *
 * @author Varun Shoor
 */
class SWIFT_TicketAjaxManager extends SWIFT_Library
{
    /**
     * Retrieve Ticket Status Combo Box on Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param string $_fieldName The Ticket Status ID Field Name
     * @param int $_ticketStatusID The Current Ticket Status ID
     * @param bool $_showNoChange (OPTIONAL) Show -- No Change -- Item in Select Box
     * @param bool $_onlyPublic (OPTIONAL) Show only public items
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketStatusOnDepartmentID($_departmentID, $_fieldName, $_ticketStatusID, $_showNoChange = false, $_onlyPublic = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_fieldName))
        {
            return false;
        }

        $_staffGroupTicketStatusIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_SWIFT->Staff->GetProperty('staffgroupid'));

        $_departmentID = ($_departmentID);
        $_ticketStatusID = ($_ticketStatusID);

        if ($_showNoChange == 'true' || $_showNoChange == '1')
        {
            $_showNoChange = true;
        } else {
            $_showNoChange = false;
        }

        if ($_onlyPublic == 'true' || $_onlyPublic == '1')
        {
            $_onlyPublic = true;
        } else {
            $_onlyPublic = false;
        }

        $_optionsContainer = array();
        if ($_showNoChange)
        {
            $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
            $_optionsContainer[0]['value'] = '0';

            if (empty($_ticketStatusID))
            {
                $_optionsContainer[0]['selected'] = true;
            }
        }

        $_index = 1;
        $this->Database->Query("SELECT ticketstatusid, title, staffvisibilitycustom FROM " . TABLE_PREFIX . "ticketstatus WHERE
            (departmentid = '0' OR departmentid = '" . ($_departmentID) . "')" . IIF($_onlyPublic, " AND type = '" . SWIFT_PUBLIC . "'") .
                " ORDER BY displayorder ASC");
        while ($this->Database->NextRecord())
        {
            if ($this->Database->Record['staffvisibilitycustom'] == '1' && !in_array($this->Database->Record['ticketstatusid'], $_staffGroupTicketStatusIDList))
            {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['ticketstatusid'];

            if ($_ticketStatusID == $this->Database->Record['ticketstatusid'])
            {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        echo '<select id="select' . $_fieldName . '" name="' . $_fieldName . '" class="swiftselect" style="width:160px;">';
        foreach ($_optionsContainer as $_key => $_val)
        {
            $_selectedHTML = '';

            if (isset($_val['selected']) && $_val['selected'] == true)
            {
                $_selectedHTML = ' selected';
            }
            echo '<option value="' . htmlspecialchars($_val['value']) . '"'. $_selectedHTML .'>' . htmlspecialchars($_val['title']) . '</option>';
        }

        echo '</select>';

        return true;
    }

    /**
     * Retrieve Ticket Type Combo Box on Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param string $_fieldName The Ticket Type ID Field Name
     * @param int $_ticketTypeID The Current Ticket Type ID
     * @param bool $_showNoChange (OPTIONAL) Show -- No Change -- Item in Select Box
     * @param bool $_onlyPublic (OPTIONAL) Show only public items
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketTypeOnDepartmentID($_departmentID, $_fieldName, $_ticketTypeID, $_showNoChange = false, $_onlyPublic = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_fieldName))
        {
            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        $_departmentID = ($_departmentID);
        $_ticketTypeID = ($_ticketTypeID);

        $_parentDepartmentID = 0;

        if (isset($_departmentCache[$_departmentID]) && !empty($_departmentCache[$_departmentID]['parentdepartmentid'])) {
            $_parentDepartmentID = ($_departmentCache[$_departmentID]['parentdepartmentid']);
        }

        if ($_showNoChange == 'true' || $_showNoChange == '1')
        {
            $_showNoChange = true;
        } else {
            $_showNoChange = false;
        }

        if ($_onlyPublic == 'true' || $_onlyPublic == '1')
        {
            $_onlyPublic = true;
        } else {
            $_onlyPublic = false;
        }


        $_optionsContainer = array();
        if ($_showNoChange)
        {
            $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
            $_optionsContainer[0]['value'] = '0';

            if (empty($_ticketTypeID))
            {
                $_optionsContainer[0]['selected'] = true;
            }
        }

        $_index = 1;
        $this->Database->Query("SELECT tickettypeid, title FROM " . TABLE_PREFIX . "tickettypes WHERE
            (departmentid = '0' OR departmentid = '" . ($_departmentID) . "'" . IIF(!empty($_parentDepartmentID), " OR departmentid = '" . ($_parentDepartmentID) . "'") . ")" . IIF($_onlyPublic, " AND type = '" . SWIFT_PUBLIC . "'") .
                " ORDER BY displayorder ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['tickettypeid'];

            if ($_ticketTypeID == $this->Database->Record['tickettypeid'])
            {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        echo '<select id="select' . $_fieldName . '" name="' . $_fieldName . '" class="swiftselect" style="width:160px;">';
        foreach ($_optionsContainer as $_key => $_val)
        {
            $_selectedHTML = '';

            if (isset($_val['selected']) && $_val['selected'] == true)
            {
                $_selectedHTML = ' selected';
            }
            echo '<option value="' . htmlspecialchars($_val['value']) . '"'. $_selectedHTML .'>' . htmlspecialchars($_val['title']) . '</option>';
        }

        echo '</select>';

        return true;
    }

    /**
     * Retrieve Ticket Owner on Department ID
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param string $_fieldName The Ticket Type ID Field Name
     * @param int $_staffID The Current Staff ID
     * @param bool $_showNoChange (OPTIONAL) Show -- No Change -- Item in Select Box
     * @param bool $_onlyPublic (OPTIONAL) Show only public items
     * @param bool $_showActiveStaff (OPTIONAL) Whether to Show Active Staff Item
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketOwnerOnDepartmentID($_departmentID, $_fieldName, $_staffID, $_showNoChange = false, $_onlyPublic = false, $_showActiveStaff = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_fieldName))
        {
            return false;
        }

        $_departmentID = ($_departmentID);
        $_staffID = ($_staffID);

        if ($_showNoChange == 'true' || $_showNoChange == '1')
        {
            $_showNoChange = true;
        } else {
            $_showNoChange = false;
        }

        if ($_onlyPublic == 'true' || $_onlyPublic == '1')
        {
            $_onlyPublic = true;
        } else {
            $_onlyPublic = false;
        }

        $_noChangeValue = '-1';
        if ($_showActiveStaff)
        {
            $_noChangeValue = '-2';
        }

        $_index = 0;

        $_optionsContainer = array();
        if ($_showNoChange)
        {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('nochange');
            $_optionsContainer[$_index]['value'] = $_noChangeValue;

            if ($_staffID == $_noChangeValue)
            {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        if ($_showActiveStaff)
        {
            $_optionsContainer[$_index]['title'] = $this->Language->Get('activestaff');
            $_optionsContainer[$_index]['value'] = '-1';

            if ($_staffID == -1)
            {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_optionsContainer[$_index]['title'] = $this->Language->Get('unassigned');
        $_optionsContainer[$_index]['value'] = '0';
        if ($_staffID == '0') {
            $_optionsContainer[$_index]['selected'] = true;
        }
        $_index++;


        /**
         * BUG FIX - Madhur Tandon <madhur.tandon@opencart.com.vn>
         *
         * SWIFT-3039 Disabled staff is shown under 'Owner' field in tickets, if department is changed from drop-down
         *
         * Comment : Added the check to show the enabled staff from the database
         */
        $this->Database->Query("SELECT staffid, fullname FROM " . TABLE_PREFIX . "staff WHERE isenabled = 1 ORDER BY fullname ASC");
        while ($this->Database->NextRecord())
        {
            $_assignedDepartmentIDList = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($this->Database->Record['staffid']);
            if (!in_array($_departmentID, $_assignedDepartmentIDList)) {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $this->Database->Record['fullname'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['staffid'];

            if ($_staffID == $this->Database->Record['staffid'])
            {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        echo '<select id="select' . $_fieldName . '" name="' . $_fieldName . '" class="swiftselect" style="width:160px;">';
        foreach ($_optionsContainer as $_key => $_val)
        {
            $_selectedHTML = '';

            if (isset($_val['selected']) && $_val['selected'] == true)
            {
                $_selectedHTML = ' selected';
            }
            echo '<option value="' . htmlspecialchars($_val['value']) . '"'. $_selectedHTML .'>' . htmlspecialchars($_val['title']) . '</option>';
        }

        echo '</select>';

        return true;
    }
}
