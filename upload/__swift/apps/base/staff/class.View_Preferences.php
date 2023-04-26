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

namespace Base\Staff;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\Department\SWIFT_Department;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_TimeZone;
use SWIFT_View;

/**
 * The Preferences View
 *
 * @author Varun Shoor
 * @property SWIFT_TimeZone $TimeZone
 * @property Controller_Preferences $Controller
 */
class View_Preferences extends SWIFT_View
{
    /**
     * Render the Preferences Form
     *
     * @author Varun Shoor
     * @param bool $_isChangePasswordTabSelected Whether the Change Password Tab is Selected
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_isChangePasswordTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_staffID = $_SWIFT->Staff->GetStaffID();
        $_staffCache = $this->Cache->Get('staffcache');
        $_staffGroupCache = $this->Cache->Get('staffgroupcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_SWIFT_StaffObject = $_SWIFT->Staff;

        $_isPreferencesTabSelected = true;
        if ($_isChangePasswordTabSelected) {
            $_isPreferencesTabSelected = false;
        }

        $_staffAvatarImage = SWIFT::Get('themepath') . 'images/icon_defaultavatar.gif';
        $_profileImageBottom = '';
        if (SWIFT_StaffProfileImage::StaffHasProfileImage($_staffID)) {
            $_staffAvatarImage = SWIFT::Get('basename') . '/Base/StaffProfile/GetProfileImage/' . $_staffID;
            $_profileImageBottom = '<a class="clearimagebutton" href="javascript: void(0);" onclick="javascript: loadViewportData(\'/Base/Preferences/ClearProfileImage/' . $_staffID . '\');">' . $this->Language->Get('clear') . '</a>';
        }

        $_staffFirstName = $_SWIFT_StaffObject->GetProperty('firstname');
        $_staffLastName = $_SWIFT_StaffObject->GetProperty('lastname');
        $_staffDesignation = $_SWIFT_StaffObject->GetProperty('designation');
        $_staffUserName = $_SWIFT_StaffObject->GetProperty('username');
        $_staffEmail = $_SWIFT_StaffObject->GetProperty('email');
        $_staffMobileNumber = $_SWIFT_StaffObject->GetProperty('mobilenumber');
        $_staffSignature = $_SWIFT_StaffObject->GetProperty('signature');
        $_staffGreeting = $_SWIFT_StaffObject->GetProperty('greeting');
        $_timeZonePHP = $_SWIFT_StaffObject->GetProperty('timezonephp');
        $_enableDST = ((int)($_SWIFT_StaffObject->GetProperty('enabledst')) ?? 0) !== 0;
        $_staffGroupID = (int)($_SWIFT_StaffObject->GetProperty('staffgroupid'));

        $this->UserInterface->Start(get_short_class($this), '/Base/Preferences/PreferencesSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

        // Begin Hook: staff_preferences_toolbar
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_preferences_toolbar')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffpreferences'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        /*
         * ###############################################
         * BEGIN PREFERENCES TAB
         * ###############################################
         */
        if ($_SWIFT_StaffObject->GetPermission('staff_profile') != '0') {
            $_PreferencesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabprofile'), 'icon_dashboardrecentactivity.gif', 'preferences', $_isPreferencesTabSelected);


            // $_PreferencesTabObject->RowHTML('<tr><td colspan="2" align="left" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="left" valign="top" width="105"><img style="margin: 10px;border-radius:5px;" src="' . $_staffAvatarImage . '" align="absmiddle" border="0" />' . $_profileImageBottom . '</td><td align="left" valign="top" width=""><table width="100%" border="0" cellspacing="1" cellpadding="4">');

            $_PreferencesTabObject->Text('firstname', $this->Language->Get('stafffirstname'), $this->Language->Get('desc_stafffirstname'), $_staffFirstName);
            $_PreferencesTabObject->Text('lastname', $this->Language->Get('stafflastname'), $this->Language->Get('desc_stafflastname'), $_staffLastName);
            $_PreferencesTabObject->Text('email', $this->Language->Get('staffemail'), $this->Language->Get('desc_staffemail'), $_staffEmail);

            // $_PreferencesTabObject->RowHTML('</table></td></tr></table></td></tr>');

            $_PreferencesTabObject->Title($this->Language->Get('personalizeoptions'), 'icon_doublearrows.gif');

            $_PreferencesTabObject->RowHTML('<tr><td align="left" valign="top" width=""></td><td align="left" valign="top" width="105"><img style="margin: 8px;border-radius:3px;box-shadow: 0px 0px 3px 2px rgba(0,0,0,0.06), 0 1px 3px 0 rgba(44,48,56,0.09);" src="' . $_staffAvatarImage . '" align="absmiddle" border="0" />' . $_profileImageBottom . '</td></tr>');

            $_PreferencesTabObject->File('profileimage', $this->Language->Get('staffprofileimage'), $this->Language->Get('desc_staffprofileimage'));

            $_PreferencesTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');

            /*
             * BUG FIX - Pankaj Garg
             *
             * SWIFT-3067: (Staff CP) Home->Preferences->Phone input=“20”, should be input=“text”
             *
             * Comments: Parameters passing was incorrect
             */
            $_PreferencesTabObject->Text('mobilenumber', $this->Language->Get('staffmobilenumber'), $this->Language->Get('desc_staffmobilenumber'), $_staffMobileNumber, 'text', 30);

            $_PreferencesTabObject->TextArea('signature', $this->Language->Get('staffsignature'), $this->Language->Get('desc_staffsignature'), $_staffSignature, 60, 4);

            $_optionsContainer = array();
            $_index = 1;
            $_optionsContainer[0]['title'] = $this->Language->Get('tzusedefault');
            $_optionsContainer[0]['value'] = '';
            if ($_timeZonePHP == '') {
                $_optionsContainer[0]['selected'] = true;
            }

            $this->Load->Library('Time:TimeZone');
            $_timeZoneContainer = $this->TimeZone->Get();
            foreach ($_timeZoneContainer as $_timeZone) {
                $_optionsContainer[$_index]['title'] = $_timeZone['title'];
                $_optionsContainer[$_index]['value'] = $_timeZone['value'];

                if ($_timeZonePHP == $_timeZone['value']) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_PreferencesTabObject->Select('timezonephp', $this->Language->Get('stafftimezone'), $this->Language->Get('desc_stafftimezone'), $_optionsContainer);

            $_PreferencesTabObject->YesNo('enabledst', $this->Language->Get('staffenabledst'), $this->Language->Get('desc_staffenabledst'), $_enableDST);

            if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
                $_PreferencesTabObject->Title($this->Language->Get('livechatoptions'), 'icon_doublearrows.gif');

                $_PreferencesTabObject->Text('greeting', $this->Language->Get('lcgreeting'), $this->Language->Get('desc_lcgreeting'), $_staffGreeting, 'text', 60);
            }

            // Begin Hook: staff_preferences_generaltab
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_preferences_generaltab')) ? eval($_hookCode) : false;
            // End Hook
        }

        /*
         * ###############################################
         * END PREFERENCES TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN CHANGE PASSWORD TAB
         * ###############################################
         */
        if ($_SWIFT_StaffObject->GetPermission('staff_changepassword') != '0') {
            $_ChangePasswordTabObject = $this->UserInterface->AddTab($this->Language->Get('tabchangepassword'), 'icon_lock.gif', 'changepassword', $_isChangePasswordTabSelected);

            $_ChangePasswordTabObject->LoadToolbar();
            $_ChangePasswordTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/Base/Preferences/ChangePasswordSubmit', SWIFT_UserInterfaceToolbar::LINK_FORM);
            $_ChangePasswordTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffpreferences'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_ChangePasswordTabObject->Password('existingpassword', $this->Language->Get('existingpassword'), $this->Language->Get('desc_existingpassword'));
            $_ChangePasswordTabObject->Password('newpassword', $this->Language->Get('newpassword'), $this->Language->Get('desc_newpassword') . '<br />' . $this->Controller->StaffPasswordPolicy->GetPasswordPolicyString());
            $_ChangePasswordTabObject->Password('newpasswordrepeat', $this->Language->Get('newpasswordrepeat'), $this->Language->Get('desc_newpassword'));
        }

        /*
         * ###############################################
         * END CHANGE PASSWORD TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN ASSIGNED DEPARTMENTS TAB
         * ###############################################
         */

        $_AssignedDepartmentsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabassigneddepartments'), 'icon_folderyellow3.gif', 'assigneddep', false);
        $_AssignedDepartmentsTabObject->LoadToolbar();
        $_AssignedDepartmentsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('staffpreferences'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_departmentMap = SWIFT_Department::GetDepartmentMap();
        $_assignedDepartmentIDList = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID, -1);

        $_departmentListHTML = '';

        foreach ($_departmentMap as $_departmentID => $_departmentContainer) {
            if (!in_array($_departmentID, $_assignedDepartmentIDList) || !isset($_departmentCache[$_departmentID])) {
                continue;
            }

            $_departmentIcon = 'icon_folderyellow3.gif';
            if ($_departmentContainer['departmentapp'] == APP_LIVECHAT) {
                $_departmentIcon = 'icon_livesupport.gif';
            } else if ($_departmentContainer['departmentapp'] == APP_TICKETS) {
                $_departmentIcon = 'icon_tickets.png';
            }

            $_departmentListHTML .= '<img src="' . SWIFT::Get('themepathimages') . $_departmentIcon . '" align="absmiddle" border="0" /> ' . text_to_html_entities($_departmentContainer['title']) . '<br/>';

            foreach ($_departmentContainer['subdepartments'] as $_subDepartmentID => $_subDepartmentContainer) {
                if (!in_array($_subDepartmentID, $_assignedDepartmentIDList) || !isset($_departmentCache[$_subDepartmentID])) {
                    continue;
                }

                $_departmentListHTML .= '<img src="' . SWIFT::Get('themepathimages') . 'linkdownarrow_blue.gif" align="absmiddle" border="0" /> <img src="' . SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif" align="absmiddle" border="0" /> ' . text_to_html_entities($_subDepartmentContainer['title']) . '<br/>';
            }

            if (count($_departmentContainer['subdepartments'])) {
                $_departmentListHTML .= '<br />';
            }
        }

        $_rowContainer = array();
        $_rowContainer[0]['value'] = $_departmentListHTML;
        $_rowContainer[0]['align'] = 'left';
        $_AssignedDepartmentsTabObject->Row($_rowContainer);

        /*
         * ###############################################
         * END ASSIGNED DEPARTMENTS TAB
         * ###############################################
         */

        // Begin Hook: staff_preferences_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_preferences_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_staffGroupCache = $this->Cache->Get('staffgroupcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_informationHTML = '';

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infofullname') . '</div><div class="navinfoitemcontent">' . text_to_html_entities(StripName($_SWIFT->Staff->GetProperty('fullname'), 20)) . '</div></div>';

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infousername') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_SWIFT->Staff->GetProperty('username'), 20)) . '</div></div>';

        $_staffGroupTitle = $_SWIFT->Language->Get('na');
        $_staffID = $_SWIFT->Staff->GetStaffID();
        $_staffGroupID = (int)($_staffCache[$_staffID]['staffgroupid']);
        if (isset($_staffGroupCache[$_staffGroupID])) {
            $_staffGroupTitle = $_staffGroupCache[$_staffGroupID]['title'];
        }

        $_staffDesignation = $_SWIFT->Staff->GetProperty('designation');

        if (!empty($_staffDesignation)) {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infodesignation') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_staffDesignation, 20)) . '</div></div>';
        }

        $_informationHTML .= '<div class="navinfoitemtext">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infoteam') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_staffGroupTitle, 20)) . '</div></div>';


        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }
}

?>
