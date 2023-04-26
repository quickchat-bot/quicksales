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

namespace Base\Staff;

use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserNote;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationLink;
use Base\Models\User\SWIFT_UserProfileImage;
use Base\Models\User\SWIFT_UserSetting;
use SWIFT;
use SWIFT_App;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Loader;
use SWIFT_View;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;

/**
 * The User View
 *
 * @author Varun Shoor
 *
 * @property Controller_User $Controller
 */
class View_User extends SWIFT_View
{
    /**
     * Render the User Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object Pointer (Only for EDIT Mode)
     * @param bool $_isQuickInsert (OPTIONAL) Whether this is being executed using quick insert
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_User $_SWIFT_UserObject = null, $_isQuickInsert = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_userGroupCache = $this->Cache->Get('usergroupcache');
        $_languageCache = $this->Cache->Get('languagecache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');

        // Calculate the URL
        if ($_isQuickInsert == true) {
            $this->UserInterface->Start(get_short_class($this), '/Base/User/QuickInsertSubmit', SWIFT_UserInterface::MODE_INSERT, true, false, false, false, 'quickinsertuserdiv');
        } else if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/User/EditSubmit/' . $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterface::MODE_EDIT, false, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/User/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_userFullName = '';
        $_userSalutation = SWIFT_User::SALUTATION_NONE;
        $_userDesignation = '';
        $_userAvatarImage = SWIFT::Get('themepath') . 'images/icon_defaultavatar.gif';
        $_userIsEnabled = true;
        $_userExpiry = '';
        $_userPhone = '';
        $_userOrganization = '';
        $_userOrganizationAutoComplete = 0;
        $_userRole = SWIFT_User::ROLE_USER;
        $_userGroupID = SWIFT_UserGroup::RetrieveDefaultUserGroupID(SWIFT_UserGroup::TYPE_REGISTERED);
        $_userTimeZonePHP = '';
        $_userEnableDST = true;
        $_userLanguageID = false;
        $_userSLAPlanID = false;
        $_userSLAExpiry = '';
        $_userTagContainer = array();
        $_userEmailContainer = array();
        $_userOrganizationContainer = array();
        $_SWIFT_UserOrganizationObject = false;
        $_profileImageBottom = '';
        $_userID = 0;
        $_sendEmailToAll = 0;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') != '0') {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            if ($_SWIFT_UserObject->GetProperty('isvalidated') != '1') {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('markasverified'), 'fa-check-circle', '/Base/User/MarkAsVerified/' . $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
            }

            if ($_SWIFT->Staff->GetPermission('staff_caninsertusernote') != '0') {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-file', "UICreateWindow('" . SWIFT::Get('basename') . '/Base/User/AddNote/' . $_SWIFT_UserObject->GetUserID() . "', 'addnote', '" . $_SWIFT->Language->Get('addnote') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 600, 360, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            }

            if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') != '0') {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('changepassword'), 'fa-lock', "UICreateWindow('" . SWIFT::Get('basename') . '/Base/User/ChangePassword/' . $_SWIFT_UserObject->GetUserID() . "', 'changepassword', '" . $_SWIFT->Language->Get('changepassword') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 400, 280, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            }

            /**
             * BUG FIX - Saloni Dhall
             *
             * SWIFT-3018: "Login as User" permission bug
             *
             */
            if ($_SWIFT->Staff->GetPermission('staff_loginasuser') != '0') {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('loginasuser'), 'fa-user', SWIFT::Get('basename') . '/Base/User/LoginAsUser/' . $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            }

            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/User/Delete/' . $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('manageuser'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_userFullName = $_SWIFT_UserObject->GetProperty('fullname');
            $_userDesignation = $_SWIFT_UserObject->GetProperty('userdesignation');
            $_userSalutation = $_SWIFT_UserObject->GetProperty('salutation');
            $_userPhone = $_SWIFT_UserObject->GetProperty('phone');
            $_userTimeZonePHP = $_SWIFT_UserObject->GetProperty('timezonephp');

            $_userIsEnabled = (int)($_SWIFT_UserObject->GetProperty('isenabled'));
            $_userExpiry = (int)($_SWIFT_UserObject->GetProperty('userexpirytimeline'));
            $_userRole = (int)($_SWIFT_UserObject->GetProperty('userrole'));
            $_userGroupID = (int)($_SWIFT_UserObject->GetProperty('usergroupid'));
            $_userEnableDST = (int)($_SWIFT_UserObject->GetProperty('enabledst'));
            $_userLanguageID = (int)($_SWIFT_UserObject->GetProperty('languageid'));
            $_userSLAPlanID = (int)($_SWIFT_UserObject->GetProperty('slaplanid'));
            $_userSLAExpiry = (int)($_SWIFT_UserObject->GetProperty('slaexpirytimeline'));

            /*
             * BUG FIX - Pankaj Garg
             *
             * SWIFT-1683, In case of multiple email addresses for user account, there should be an option under User account to be enabled for sending ticket updates to all the email addresses
             */
            $_userSettingContainer = SWIFT_UserSetting::RetrieveOnUser($_SWIFT_UserObject);
            if (isset($_userSettingContainer['sendemailtoall'])) {
                $_sendEmailToAll = $_userSettingContainer['sendemailtoall'];
            }

            $_userID = $_SWIFT_UserObject->GetUserID();

            if (SWIFT_UserProfileImage::UserHasProfileImage($_SWIFT_UserObject->GetUserID())) {
                $_userAvatarImage = SWIFT::Get('basename') . '/Base/User/GetProfileImage/' . $_SWIFT_UserObject->GetUserID();
                $_profileImageBottom = '<a class="clearimagebutton" href="' . SWIFT::Get('basename') . '/Base/User/ClearProfileImage/' . $_SWIFT_UserObject->GetUserID() . '" viewport="1"> ' . $this->Language->Get('clear') . '</a>';
            }

            if ($_SWIFT_UserObject->GetProperty('userorganizationid')) {
                try {
                    $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT_UserObject->GetProperty('userorganizationid'));
                    if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                        $_userOrganization = $_SWIFT_UserOrganizationObject->GetProperty('organizationname');
                        $_userOrganizationAutoComplete = (int)($_SWIFT_UserOrganizationObject->GetUserOrganizationID());
                    }
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                }
            }

            $_userTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_USER, $_SWIFT_UserObject->GetUserID());

            $_userEmailContainer = SWIFT_UserEmail::RetrieveList($_SWIFT_UserObject->GetUserID());

            $_userOrganizationContainer = array_values(SWIFT_UserOrganizationLink::RetrieveListOnUser($_SWIFT_UserObject->GetUserID()));
            if ($_SWIFT_UserOrganizationObject) {
                // always show the primary organization first
                $_userOrganizationContainer = array_diff($_userOrganizationContainer, [$_userOrganization]);
                array_unshift($_userOrganizationContainer, $_userOrganization);
            }
        } else {
            if (!$_isQuickInsert) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-check-circle');
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('insertuser'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            }
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        if ($_isQuickInsert) {
            $_GeneralTabObject->Overflow(480);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_notesHTML = $this->RenderUserNotes($_SWIFT_UserObject);

            if (!empty($_notesHTML)) {
                $_GeneralTabObject->RowHTML('<tr class="gridrow3" id="usernotescontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3"><div id="usernotescontainerdiv">' . $_notesHTML . '</div></td></tr>');
            } else {
                $_GeneralTabObject->RowHTML('<tr class="gridrow3" style="display: none;" id="usernotescontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3"><div id="usernotescontainerdiv"></div></td></tr>');
            }
        }

        if (!$_isQuickInsert) {
            $_GeneralTabObject->RowHTML('<tr><td colspan="2" align="left" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">');
        }

        $_salutationList = SWIFT_User::RetrieveSalutationList();
        $_salutationHTML = '<select name="salutation" class="swiftselect">';
        foreach ($_salutationList as $_key => $_val) {
            $_isSelected = false;
            if (($_userSalutation == $_key && !isset($_POST['salutation'])) || (isset($_POST['salutation']) && $_POST['salutation'] == $_key)) {
                $_isSelected = true;
            }

            $_salutationHTML .= '<option value="' . $_key . '"' . IIF($_isSelected, ' selected') . '>' . $_val . '</option>';
        }
        $_salutationHTML .= '</select>';

        if (isset($_POST['fullname'])) {
            $_userFullName = $_POST['fullname'];
        }
        $_fullNameHTML = '&nbsp;<input type="text" class="swifttext" name="fullname" value="' . text_to_html_entities($_userFullName) . '" size="20" />';

        $_columnClass = '';
        if (in_array('fullname', SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('userfullname'), $this->Language->Get('desc_userfullname'), $_salutationHTML . $_fullNameHTML, '', $_columnClass);

        $_GeneralTabObject->TextMultipleAutoComplete('organization', $this->Language->Get('userorganization'), $this->Language->Get('desc_userorganization'), '/Base/UserOrganization/QuickSearchNames', $_userOrganizationContainer, 'fa-institution', false, false, 2, false, false, false, '',
            ['data-allowtagchars' => true]);

        $_GeneralTabObject->Text('userdesignation', $this->Language->Get('userdesignation'), $this->Language->Get('desc_userdesignation'), $_userDesignation);

        if (!$_isQuickInsert) {
            $_GeneralTabObject->RowHTML('</table></td></tr>');
        }

        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            $_GeneralTabObject->TextMultipleAutoComplete('tags', false, false, '/Base/Tags/QuickSearch', $_userTagContainer, 'fa-tags', 'gridrow1', true);
        }
        /*
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-1683, In case of multiple email addresses for user account, there should be an option under User account to be enabled for sending ticket updates to all the email addresses
         */
        $_GeneralTabObject->YesNo('sendemailtoall', $this->Language->Get('sendticketupdate'), $this->Language->Get('desc_sendticketupdate'), $_sendEmailToAll);

        $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_STATIC, $_mode, array(SWIFT_CustomFieldGroup::GROUP_USER), $_GeneralTabObject, $_userID);

        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, $_mode, array(SWIFT_CustomFieldGroup::GROUP_USER), $_GeneralTabObject, $_userID);
        }

        $_GeneralTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');

        $_GeneralTabObject->TextMultipleAutoComplete('emails', $this->Language->Get('useremails'), $this->Language->Get('desc_useremails'), false, $_userEmailContainer, 'fa-envelope-o', false, false, 2, false, false, false, '', array('containemail' => '1'));

        $_GeneralTabObject->Text('phone', $this->Language->Get('userphone'), $this->Language->Get('desc_userphone'), $_userPhone, 'text', 30, 25);

        $_itemContainer = array();
        $_index = 0;
        foreach (array(SWIFT_User::ROLE_USER => $this->Language->Get('userroleuser'), SWIFT_User::ROLE_MANAGER => $this->Language->Get('userrolemanager')) as $_key => $_val) {
            $_itemContainer[$_index]['title'] = $_val;
            $_itemContainer[$_index]['value'] = $_key;

            if ($_key == $_userRole) {
                $_itemContainer[$_index]['checked'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Radio('userrole', $this->Language->Get('userrole'), $this->Language->Get('desc_userrole'), $_itemContainer, false);

        $_optionsContainer = array();
        $_index = 0;
        foreach ($_userGroupCache as $_key => $_val) {
            if ($_val['grouptype'] != SWIFT_UserGroup::TYPE_REGISTERED) {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['usergroupid'];

            if ($_val['usergroupid'] == $_userGroupID) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('usergroupid', $this->Language->Get('usergroup'), $this->Language->Get('desc_usergroup'), $_optionsContainer);

        if (!$_isQuickInsert) {
            $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('isenabled'), $this->Language->Get('desc_isenabled'), $_userIsEnabled);

            $_userExpiryDate = '';
            if (!empty($_userExpiry)) {
                $_userExpiryDate = gmdate(SWIFT_Date::GetCalendarDateFormat(), $_userExpiry);
            }
            $_GeneralTabObject->Date('userexpirytimeline', $this->Language->Get('userexpiry'), $this->Language->Get('desc_userexpiry'), $_userExpiryDate);

            // Only display the add note column in Insert
            if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                $_GeneralTabObject->Notes('usernotes', $this->Language->Get('addnotes'));
            }
        }


        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PROFILE TAB
         * ###############################################
         */

        $_ProfileTabObject = $this->UserInterface->AddTab($this->Language->Get('tabprofile'), 'icon_settings2.gif', 'profile', false);

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, $_mode, array(SWIFT_CustomFieldGroup::GROUP_USER), $_ProfileTabObject, $_userID);
        }

        if (!$_isQuickInsert) {
            $_ProfileTabObject->Title($this->Language->Get('personalizeoptions'), 'icon_doublearrows.gif');


            if (!$_isQuickInsert) {
                $_ProfileTabObject->RowHTML('<tr><td align="left" valign="top" width=""></td><td align="left" valign="top" width="105"><img style="margin: 8px;border-radius:3px;box-shadow: 0px 0px 3px 2px rgba(0,0,0,0.06), 0 1px 3px 0 rgba(44,48,56,0.09);" src="' . $_userAvatarImage . '" align="absmiddle" border="0" />' . $_profileImageBottom . '</td></tr>');
            }

            $_ProfileTabObject->File('profileimage', $this->Language->Get('userprofileimage'), $this->Language->Get('desc_userprofileimage'));

            $_ProfileTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');

            $_optionsContainer = array();
            $_index = 0;

            foreach ($this->Controller->TimeZoneContainer->GetTimeZoneList() as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $_val['title'];
                $_optionsContainer[$_index]['value'] = $_val['value'];

                if ($_val['value'] == $_userTimeZonePHP) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_ProfileTabObject->Select('timezonephp', $this->Language->Get('usertimezone'), $this->Language->Get('desc_usertimezone'), $_optionsContainer);

            $_ProfileTabObject->YesNo('enabledst', $this->Language->Get('userenabledst'), $this->Language->Get('desc_userenabledst'), $_userEnableDST);

            $_optionsContainer = array();
            $_index = 1;

            $_optionsContainer[0]['title'] = $this->Language->Get('defaultlanguage');
            $_optionsContainer[0]['value'] = 0;
            if (!$_userLanguageID) {
                $_optionsContainer[0]['selected'] = true;
            }

            foreach ($_languageCache as $_key => $_val) {
                if ($_val['isenabled'] == 1) {
                    $_optionsContainer[$_index]['title'] = $_val['title'];
                    $_optionsContainer[$_index]['value'] = $_val['languageid'];

                    if ($_val['languageid'] == $_userLanguageID) {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }

            $_ProfileTabObject->Select('languageid', $this->Language->Get('userlanguage'), $this->Language->Get('desc_userlanguage'), $_optionsContainer);
        }

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_ProfileTabObject->Title($this->Language->Get('usercustomsla'), 'icon_doublearrows.gif');

            $_optionsContainer = array();
            $_index = 1;

            $_optionsContainer[0]['title'] = $this->Language->Get('defaultslaplan');
            $_optionsContainer[0]['value'] = 0;
            if (!$_userSLAPlanID) {
                $_optionsContainer[0]['selected'] = true;
            }

            foreach ($_slaPlanCache as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $_val['title'];
                $_optionsContainer[$_index]['value'] = $_val['slaplanid'];

                /**
                 * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                 *
                 * SWIFT-4430 Disabled SLA plan can be implemented over a ticket manually from 'Edit' tab.
                 */
                if ($_val['isenabled'] == '0') {
                    $_optionsContainer[$_index]['disabled'] = true;
                }

                if ($_val['slaplanid'] == $_userSLAPlanID) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_ProfileTabObject->Select('slaplanid', $this->Language->Get('userslaplan'), $this->Language->Get('desc_userslaplan'), $_optionsContainer);

            $_userSLAExpiryDate = '';
            if (!empty($_userSLAExpiry)) {
                $_userSLAExpiryDate = gmdate(SWIFT_Date::GetCalendarDateFormat(), $_userSLAExpiry);
            }

            $_ProfileTabObject->Date('slaexpirytimeline', $this->Language->Get('userslaexpiry'), $this->Language->Get('desc_userslaexpiry'), $_userSLAExpiryDate);
        } else {
            $this->UserInterface->Hidden('slaplanid', '0');
            $this->UserInterface->Hidden('slaexpirytimeline', '0');
        }

        /*
         * ###############################################
         * END PROFILE TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN ORGANIZATION TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $_userOrganizationTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_USERORGANIZATION, $_SWIFT_UserOrganizationObject->GetUserOrganizationID());

            $_OrganizationTabObject = $this->UserInterface->AddTab($this->Language->Get('taborganization'), 'icon_userorganization.png', 'organization', false);
            $_OrganizationTabObject->LoadToolbar();

            $_OrganizationTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            $_OrganizationTabObject->Toolbar->AddButton($this->Language->Get('edit'), 'fa-pencil', '/Base/UserOrganization/Edit/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);

            if ($_SWIFT->Staff->GetPermission('staff_caninsertusernote') != '0') {
                $_OrganizationTabObject->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-file', "UICreateWindow('" . SWIFT::Get('basename') . '/Base/User/AddNote/' . $_SWIFT_UserObject->GetUserID() . "/1', 'addnote', '" . $_SWIFT->Language->Get('addnote') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 600, 360, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            }

            $_OrganizationTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('user'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationname'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('organizationname')));

            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationtype2'), '', htmlspecialchars(SWIFT_UserOrganization::GetOrganizationTypeLabel($_SWIFT_UserOrganizationObject->GetProperty('organizationtype'))));

            if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
                $_OrganizationTabObject->TextMultipleAutoComplete('organizationtags', false, false, '/Base/Tags/QuickSearch', $_userOrganizationTagContainer, 'fa-tags', 'gridrow1', true);
            }

            $_OrganizationTabObject->Title($this->Language->Get('contactdetails'), 'icon_doublearrows.gif');
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationaddress'), '', nl2br(htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('address'))));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationcity'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('city')));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationstate'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('state')));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationpostalcode'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('postalcode')));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationcountry'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('country')));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationphone'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('phone')));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationfax'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('fax')));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('organizationwebsite'), '', htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('website')));
        }

        /*
         * ###############################################
         * END ORGANIZATION TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN GEOIP TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_UserObject->GetProperty('hasgeoip') == '1') {
            $_GeoIPTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeoip'), 'icon_geoip.gif', 'geoip', false);
            $_GeoIPTabObject->SetColumnWidth('150px');

            $_geoIPKeys = array('geoiporganization', 'geoipisp', 'geoipnetspeed', 'geoiptimezone', 'geoipcountry', 'geoipcountrydesc',
                'geoipregion', 'geoipcity', 'geoippostalcode', 'geoiplatitude', 'geoiplongitude', 'geoipmetrocode',
                'geoipareacode');

            foreach ($_geoIPKeys as $_keyName) {
                if ($_SWIFT_UserObject->GetProperty($_keyName) != '') {
                    $_GeoIPTabObject->DefaultDescriptionRow($this->Language->Get($_keyName), '', htmlspecialchars($_SWIFT_UserObject->GetProperty($_keyName)));
                }
            }

        }

        /*
         * ###############################################
         * END GEOIP TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN TICKETS TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            $_ticketHistoryCounterHTML = '';
            $_ticketHistoryCount = SWIFT_Ticket::GetHistoryCountOnUser($_SWIFT_UserObject, $_userEmailContainer);
            if ($_ticketHistoryCount > 0) {
                $_ticketHistoryCount = number_format($_ticketHistoryCount, 0);
            }

            $_TicketsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabtickets'), 'icon_tickets.png', 'tickets', false, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/HistoryUser/' . $_SWIFT_UserObject->GetUserID() . '/' . substr(BuildHash(), 6));
            $_TicketsTabObject->SetTabCounter($_ticketHistoryCount);
        }

        /*
         * ###############################################
         * END TICKETS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN CHATS TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            SWIFT_Loader::LoadModel('Chat:Chat', APP_LIVECHAT);
            $_chatHistoryCounterHTML = '';
            $_chatHistoryCount = SWIFT_Chat::GetHistoryCountOnUser($_SWIFT_UserObject, $_userEmailContainer);
            if ($_chatHistoryCount > 0) {
                $_chatHistoryCount = number_format($_chatHistoryCount, 0);
            }

            $_historyArguments = 'userid=' . $_SWIFT_UserObject->GetUserID() . '&random=' . substr(BuildHash(), 6);
            $_ChatsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabchats'), 'icon_livesupport.gif', 'livechat', false, false, 0, SWIFT::Get('basename') . '/LiveChat/ChatHistory/History/' . base64_encode($_historyArguments));
            $_ChatsTabObject->SetTabCounter($_chatHistoryCount);
        }

        /*
         * ###############################################
         * END CHATS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN CALLS TAB
         * ###############################################
         */
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && SWIFT_App::IsInstalled(APP_LIVECHAT)) {

            $_callHistoryCounterHTML = '';
            SWIFT_Loader::LoadModel('Call:Call', APP_LIVECHAT);
            $_callHistoryCount = SWIFT_Call::GetHistoryCountOnUser($_SWIFT_UserObject, $_userEmailContainer);
            if ($_callHistoryCount > 0) {
                $_callHistoryCount = number_format($_callHistoryCount, 0);
            }

            $_userIDCall = -1;
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_userIDCall = $_SWIFT_UserObject->GetUserID();
            }

            $_callEmailList = '';
            foreach ($_userEmailContainer as $_callEmailAddress) {
                $_callEmailList .= '&email[]=' . urlencode($_callEmailAddress);
            }

            $_CallsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcalls'), 'icon_phone.gif', 'calls', false, false, 0, SWIFT::Get('basename') . '/LiveChat/Call/History/' . base64_encode('userid=' . ($_userIDCall) . $_callEmailList));

            $_CallsTabObject->SetTabCounter($_callHistoryCount);
        }

        /*
         * ###############################################
         * END CALLS TAB
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN BILLING TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadModel('TimeTrack:TicketTimeTrack', APP_TICKETS);
            $_timeTrackCounterHTML = '';
            $_timeTrackCount = SWIFT_TicketTimeTrack::GetTimeTrackCountOnUser($_SWIFT_UserObject);
            if ($_timeTrackCount > 0) {
                $_timeTrackCount = number_format($_timeTrackCount, 0);
            }

            $_BillingTabObject = $this->UserInterface->AddTab($this->Language->Get('tabbilling') . $_timeTrackCounterHTML, 'icon_spacer.gif', 'billing', false, false, 0, SWIFT::Get('basename') . '/Tickets/Ticket/BillingUser/' . $_SWIFT_UserObject->GetUserID() . '/' . substr(BuildHash(), 6));

            $_BillingTabObject->SetTabCounter($_timeTrackCount);
        }

        /*
         * ###############################################
         * END BILLING TAB
         * ###############################################
         */

        // Begin Hook: staff_user_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_user_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the User Grid
     *
     * @author Varun Shoor
     * @param int|false $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid($_searchStoreID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('usergrid', false, true), true, false, 'base');

        $_filterQuerySQLPrefix = 'useremails.*, users.fullname AS userfullname, users.isenabled AS isenabled, users.isvalidated AS isvalidated, usergroups.title AS usergrouptitle, userorganizations.organizationname AS userorganizationname, userorganizations.userorganizationid AS userorganizationid';

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $_searchQuerySuffix = "FROM " . TABLE_PREFIX . "useremails AS useremails
                LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
                LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND
                    ((" . $this->UserInterfaceGrid->BuildSQLSearch('useremails.email') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('users.fullname') . "))";

            $this->UserInterfaceGrid->SetSearchQuery("SELECT * FROM (SELECT " . $_filterQuerySQLPrefix . ' ' . $_searchQuerySuffix . ' ORDER BY useremails.isprimary DESC) AS data
                                                        GROUP BY linktypeid', "SELECT COUNT(*) AS totalitems FROM (SELECT COUNT(*) AS totalitems " . $_searchQuerySuffix . " GROUP BY linktypeid) AS data");
        }

        /**
         * BUG FIX  Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKOC-294 - Merged user account tickets are lost when related emails are removed
         *
         * Comments - Updated the query to prefer the primary email when fetching user data
         */

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
            "SELECT * FROM (SELECT * FROM (SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "useremails AS useremails
                    LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
                    LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
                    LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
                    WHERE useremails.useremailid IN (%s) ORDER BY useremails.isprimary DESC) AS data
                    GROUP BY linktypeid, isprimary ORDER BY isprimary DESC) AS GROUPED GROUP BY linktypeid", SWIFT_SearchStore::TYPE_USERS, SWIFT::Get('basename') . '/Base/User/Manage/-1');

        /**
         * BUG FIX  Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKOC-294 - Merged user account tickets are lost when related emails are removed
         *
         * Comments - Updated the query to prefer the primary email when fetching user data
         */

        $this->UserInterfaceGrid->SetQuery(
            "SELECT * FROM (SELECT * FROM (SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid) WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
            ) AS data
            GROUP BY linktypeid, isprimary ORDER BY isprimary DESC) AS GROUPED GROUP BY linktypeid"

            , "SELECT COUNT(*) AS totalitems FROM (SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
            GROUP BY linktypeid) AS data");

        // Set Tag Lookup Queries..
        /**
         * BUG FIX  Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKOC-294 - Merged user account tickets are lost when related emails are removed
         *
         * Comments - Updated the query to prefer the primary email when fetching user data
         */

        $this->UserInterfaceGrid->SetTagOptions(SWIFT_TagLink::TYPE_USER, "SELECT * FROM (SELECT * FROM (SELECT " . $_filterQuerySQLPrefix . " FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND users.userid IN (%s)
            ) AS data
            GROUP BY linktypeid, isprimary ORDER BY isprimary DESC) as GROUPED GROUP BY linktypeid"
            , "SELECT COUNT(*) AS totalitems FROM (SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND users.userid IN (%s) GROUP BY linktypeid) AS data");

        $this->UserInterfaceGrid->SetRecordsPerPage(20);

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('useremailid', 'useremailid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('email', $this->Language->Get('useremail'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('userfullname', $this->Language->Get('userfullname'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('userorganizationname', $this->Language->Get('userorganization'), SWIFT_UserInterfaceGridField::TYPE_DB, 250, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('usergrouptitle', $this->Language->Get('usergroup'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Staff\Controller_User', 'DeleteList'), $this->Language->Get('actionconfirm')));

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuser') != '0') {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle', array('Base\Staff\Controller_User', 'EnableList'), $this->Language->Get('actionconfirm')));
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle', array('Base\Staff\Controller_User', 'DisableList'), $this->Language->Get('actionconfirm')));

            $this->UserInterfaceGrid->SetMassActionPanel($this->RenderMassActionPanel(),
                array('Base\Staff\Controller_User', 'MassActionPanel'));
        }

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . "/Base/User/Insert");

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * Renders the Mass Action Panel and returns the HTML
     *
     * @author Varun Shoor
     * @return string "Mass Action Panel HTML" on Success, "false" otherwise
     */
    protected function RenderMassActionPanel()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_userGroupCache = $_SWIFT->Cache->Get('usergroupcache');

        $_GeneralTabObject = new SWIFT_UserInterfaceTab($this->UserInterface, $_SWIFT->Language->Get('tabmassaction'), 'icon_form.gif', 0,
            'general', true, false, 4);
        $_GeneralTabObject->SetColumnWidth('150');

        $_GeneralTabObject->LoadToolbar('general');
        $_GeneralTabObject->Toolbar->AddButton($_SWIFT->Language->Get('update'), 'fa-check-circle', 'GridMassActionPanel(\'' . 'usergrid' .
            '\', \'\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT);
        $_GeneralTabObject->Toolbar->AddButton($_SWIFT->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('user'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /**
         * ---------------------------------------------
         * Field Rendering Logic
         * ---------------------------------------------
         */

        $_GeneralTabObject->TextMultipleAutoComplete('organization', $this->Language->Get('userorganization'), '', '/Base/UserOrganization/QuickSearchNames', [], 'fa-institution', false, false, 2, false, false, false, '', ['data-allowtagchars' => true]);

        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('nochange');
        $_optionsContainer[0]['value'] = '0';
        $_optionsContainer[0]['selected'] = true;

        $_index = 1;

        foreach ($_userGroupCache as $_key => $_val) {
            if ($_val['grouptype'] != SWIFT_UserGroup::TYPE_REGISTERED) {
                continue;
            }

            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = $_val['usergroupid'];

            $_index++;
        }

        $_GeneralTabObject->Select('usergroupid', $this->Language->Get('usergroup'), '', $_optionsContainer);

        /**
         * ---------------------------------------------
         * Merge User Tab
         * ---------------------------------------------
         */
        $_primaryUserOptionContainer[0]['title'] = $_SWIFT->Language->Get('mergeuserpleaseselect');
        $_primaryUserOptionContainer[0]['value'] = '';
        $_primaryUserOptionContainer[0]['selected'] = true;
        $_MergeUserTab = new SWIFT_UserInterfaceTab($this->UserInterface, $_SWIFT->Language->Get('merge'), 'icon_ticketmerge.png', 2, 'merge', false, false, 4);
        $_MergeUserTab->SetColumnWidth('150');
        $_MergeUserTab->Select('primaryuser', $this->Language->Get('primaryuser'), '', $_primaryUserOptionContainer);
        $_MergeUserTab->LoadToolbar('usergrid');
        $_MergeUserTab->Toolbar->AddButton($_SWIFT->Language->Get('merge'), 'fa-check-circle', 'GridMassActionPanel(\'' . 'usergrid' . '\', \'' . $_SWIFT->Language->Get('undoneconfirm') . '\')', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $_MergeUserTab->Toolbar->AddButton($_SWIFT->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('user'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        $_html = "<script type='text/javascript'>$('#form_usergrid .swiftgridcheckbox').on('click', function(){ HandleMassActionGridCheckboxClick('usergrid', this.value, 'primaryuser'); }); </script>";
        $_MergeUserTab->RowHTML($_html);

        /**
         * ---------------------------------------------
         * Tab Rendering Logic
         * ---------------------------------------------
         */
        $_tabContainer = array();
        $_tabContainer[] = $_GeneralTabObject;
        $_tabContainer[] = $_MergeUserTab;

        $_formName = '';

        return SWIFT_UserInterfaceControlPanel::RenderMassActionPanelTabs($_formName, $_tabContainer);
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

        $_userURL = SWIFT::Get('basename') . '/Base/User/Edit/' . (int)($_fieldContainer['linktypeid']);

        $_fieldContainer['icon'] = '<i class="fa ' . IIF($_fieldContainer['isenabled'] == '0', 'fa-user-times', 'fa-user') . '"></i>';

        $_fieldContainer['email'] = IIF($_fieldContainer['isvalidated'] != '1', '<s>') . '<a href="' . $_userURL . '" viewport="1">' . htmlspecialchars($_fieldContainer['email']) . '</a>' . IIF($_fieldContainer['isvalidated'] != '1', '</s>');

        $_fieldContainer['userfullname'] = text_to_html_entities($_fieldContainer['userfullname']);

        if (!empty($_fieldContainer['userorganizationname'])) {
            $_fieldContainer['userorganizationname'] = '<a href="' . SWIFT::Get('basename') . '/Base/UserOrganization/Edit/' . (int)($_fieldContainer['userorganizationid']) . '" viewport="1"><i class="fa fa-institution" aria-hidden="true"></i> ' . text_to_html_entities($_fieldContainer['userorganizationname']) . '</a>';
        } else {
            $_fieldContainer['userorganizationname'] = $_SWIFT->Language->Get('na');
        }

        if (!empty($_fieldContainer['usergrouptitle'])) {
            $_fieldContainer['usergrouptitle'] = htmlspecialchars($_fieldContainer['usergrouptitle']);
        } else {
            $_fieldContainer['usergrouptitle'] = $_SWIFT->Language->Get('na');
        }

        return $_fieldContainer;
    }

    /**
     * Render the Notes for the given User (including his organization notes)
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object
     * @return mixed "_renderedHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RenderUserNotes(SWIFT_User $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_canviewusernotes') == '0') {
            return '';
        }

        // Retrieve notes
        $_userNoteContainer = array();
        $_renderedHTML = '';

        $this->Database->Query("SELECT usernotes.*, usernotedata.* FROM " . TABLE_PREFIX . "usernotes AS usernotes LEFT JOIN " . TABLE_PREFIX . "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid) WHERE (usernotes.linktype = '" . SWIFT_UserNote::LINKTYPE_USER . "' AND usernotes.linktypeid = '" . $_SWIFT_UserObject->GetUserID() . "') OR (usernotes.linktype = '" . SWIFT_UserNote::LINKTYPE_ORGANIZATION . "' AND usernotes.linktypeid IN (SELECT userorganizationid FROM " . TABLE_PREFIX . "userorganizationlinks WHERE userid = " . $_SWIFT_UserObject->GetUserID() . ")) ORDER BY usernotes.linktype ASC, usernotes.dateline DESC");
        while ($this->Database->NextRecord()) {
            $_userNoteContainer[] = $this->Database->Record;

            $_icon = '';

            if ($this->Database->Record['linktype'] == SWIFT_UserNote::LINKTYPE_USER) {
                $_icon = 'fa-user';
            } else if ($this->Database->Record['linktype'] == SWIFT_UserNote::LINKTYPE_ORGANIZATION) {
                $_icon = 'fa-institution';
            }

            $_renderedHTML .= '<div id="note' . (SWIFT_UserNote::GetSanitizedNoteColor($this->Database->Record['notecolor'])) . '" class="bubble"><div class="notebubble"><cite class="tip"><strong><i class="fa ' . $_icon . '" aria-hidden="true"></i> ' . sprintf($this->Language->Get('notetitle'), '<b>' . htmlspecialchars($this->Database->Record['staffname']) . '</b>', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Database->Record['dateline']))

                . IIF(!empty($this->Database->Record['editedstaffid']) && !empty($this->Database->Record['editedstaffname']), sprintf($this->Language->Get('noteeditedtitle'), htmlspecialchars($this->Database->Record['editedstaffname']), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Database->Record['editedtimeline'])))

                . '</strong><div class="ticketnotesactions">';

            if ($_SWIFT->Staff->GetPermission('staff_canupdateusernote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: UICreateWindow(\'' . SWIFT::Get('basename') . '/Base/User/EditNote/' . $_SWIFT_UserObject->GetUserID() . '/' . (int)($this->Database->Record['usernoteid']) . "', 'editnote', '" . $this->Language->Get('editnote') . "', '" . $this->Language->Get('loadingwindow') . '\', 600, 360, true, this);"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> ';
            }

            if ($_SWIFT->Staff->GetPermission('staff_candeleteusernote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: UserDeleteNote(\'' . addslashes($this->Language->Get('usernotedelconfirm')) . '\', \'' . ($_SWIFT_UserObject->GetUserID()) . '/' . (int)($this->Database->Record['usernoteid']) . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            }

            $_renderedHTML .= '</div></cite><blockquote><p>' . nl2br(htmlspecialchars($this->Database->Record['notecontents'])) . '</p></blockquote></div></div>';
        }

        return $_renderedHTML;
    }

    /**
     * Render the Change Password Dialog
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderChangePasswordForm(SWIFT_User $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start('userchangepassword', '/Base/User/ChangePasswordSubmit/' . $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterface::MODE_EDIT, false);
        $this->UserInterface->SetDialogOptions(false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('generateandemail'), 'fa-envelope', '/Base/User/GeneratePassword/' . $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('user'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN PASSWORD TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_lock.gif', 'changepwtab', true);

        $_GeneralTabObject->Password('password', $this->Language->Get('userpassword'), '');
        $_GeneralTabObject->Password('passwordagain', $this->Language->Get('userpasswordagain'), '');

        /*
         * ###############################################
         * END PASSWORD TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Add Note Dialog
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @param SWIFT_UserNote $_SWIFT_UserNoteObject The SWIFT_UserNote Object Poitner
     * @param bool $_isOrganizationNote Whether this is an Organization Note
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderNoteForm($_mode, SWIFT_User $_SWIFT_UserObject, SWIFT_UserNote $_SWIFT_UserNoteObject = null, $_isOrganizationNote = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $this->UserInterface->Start('useraddnotes', '/Base/User/AddNoteSubmit/' . $_SWIFT_UserObject->GetUserID(), SWIFT_UserInterface::MODE_EDIT, true, false, false, false, 'usernotescontainerdiv');
        } else {
            $this->UserInterface->Start('useraddnotes', '/Base/User/EditNoteSubmit/' . $_SWIFT_UserObject->GetUserID() . '/' . $_SWIFT_UserNoteObject->GetUserNoteID(), SWIFT_UserInterface::MODE_EDIT, true, false, false, false, 'usernotescontainerdiv');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('user'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_defaultNoteContents = '';
        $_defaultNoteColor = 1;
        if ($_SWIFT_UserNoteObject instanceof SWIFT_UserNote && $_SWIFT_UserNoteObject->GetIsClassLoaded()) {
            $_defaultNoteContents = $_SWIFT_UserNoteObject->GetProperty('notecontents');
            $_defaultNoteColor = (int)($_SWIFT_UserNoteObject->GetProperty('notecolor'));
        }

        $this->UserInterface->Hidden('isorganizationnote', (int)($_isOrganizationNote));

        /*
         * ###############################################
         * BEGIN ADD NOTES TAB
         * ###############################################
         */

        $_AddNoteTabObject = $this->UserInterface->AddTab(IIF($_mode == SWIFT_UserInterface::MODE_INSERT, $this->Language->Get('tabaddnote'), $this->Language->Get('tabeditnote')), 'icon_note.png', 'addnote', true);

        $_AddNoteTabObject->Notes('usernotes', $this->Language->Get('addnotes'), $_defaultNoteContents, $_defaultNoteColor);

        /*
         * ###############################################
         * END ADD NOTES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox(SWIFT_User $_SWIFT_UserObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_informationHTML = '';

        $_userGroupCache = $this->Cache->Get('usergroupcache');

        $_userURL = SWIFT::Get('basename') . '/Base/User/Edit/' . $_SWIFT_UserObject->GetUserID();
        $_informationHTML .= '<div class="navinfoitem">' .
            '<div class="navinfoitemtitle">' . $this->Language->Get('infobuser') . '</div><div class="navinfoitemcontainer"><span class="navinfoitemlink"><a href="' . $_userURL . '" viewport="1">' . StripName(text_to_html_entities($_SWIFT_UserObject->GetProperty('fullname')), 20) . '</a></span>&nbsp;</div></div>';

        $_SWIFT_UserOrganizationObject = false;
        if ($_SWIFT_UserObject->GetProperty('userorganizationid')) {
            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_SWIFT_UserObject->GetProperty('userorganizationid'));
                if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
        }

        if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $_userOrganizationURL = SWIFT::Get('basename') . '/Base/UserOrganization/Edit/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID();

            $_informationHTML .= '<div class="navinfoitem">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobuserorganization') . '</div><div class="navinfoitemcontainer"><span class="navinfoitemlink"><a href="' . $_userOrganizationURL . '" viewport="1">' . StripName(htmlspecialchars($_SWIFT_UserOrganizationObject->GetProperty('organizationname')), 20) . '</a></span>&nbsp;</div></div>';
        }

        if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded() && isset($_userGroupCache[$_SWIFT_UserObject->GetProperty('usergroupid')])) {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobusergroup') . '</div><div class="navinfoitemcontent">' . StripName(htmlspecialchars($_userGroupCache[$_SWIFT_UserObject->GetProperty('usergroupid')]['title']), 20) . '</div></div>';
        }

        if ($_SWIFT_UserObject->GetProperty('lastvisit') != '0') {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infolastlogin') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_UserObject->GetProperty('lastvisit')) . '</div></div>';
        }

        // Begin Hook: staff_user_infobox
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_user_infobox')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }

    /**
     * Render the CSV User Form
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     *
     * @param int $_mode The Render Mode
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderCSV($_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/User/ImportCSVSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('import'), 'icon_addplus.gif');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'icon_help.gif', SWIFT_Help::RetrieveHelpLink('user'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->File('csvfile', 'Import CSV', 'Import the users from CSV file, format must be comma separated e,g Email, First Name, Last Name, Organization, Phone Number, Salutation, Designation');
        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
