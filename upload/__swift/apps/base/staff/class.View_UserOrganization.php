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

namespace Base\Staff;

use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserOrganizationEmail;
use Base\Models\User\SWIFT_UserOrganizationNote;
use SWIFT;
use SWIFT_App;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_View;

/**
 * The User Organization View
 *
 * @author Varun Shoor
 *
 * @property Controller_UserOrganization $Controller
 */
class View_UserOrganization extends SWIFT_View
{
    /**
     * Render the User Organization Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_UserOrganization $_SWIFT_UserOrganizationObject The SWIFT_UserOrganization Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_UserOrganization $_SWIFT_UserOrganizationObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_slaPlanCache = $this->Cache->Get('slaplancache');
        $_userGroupCache = $this->Cache->Get('usergroupcache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Base/UserOrganization/EditSubmit/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_UserInterface::MODE_EDIT, false, true);
        } else {
            $this->UserInterface->Start(get_short_class($this), '/Base/UserOrganization/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false, true);
        }

        $_userOrganizationName = '';
        $_userOrganizationType = SWIFT_UserOrganization::TYPE_RESTRICTED;
        $_userOrganizationTagContainer = array();
        $_userOrganizationEmailContainer = array();
        $_userOrganizationSLAPlanID = false;
        $_userOrganizationSLAExpiry = 0;
        $_userOrganizationAddress = '';
        $_userOrganizationCity = '';
        $_userOrganizationState = '';
        $_userOrganizationCountry = '';
        $_userOrganizationPostalCode = '';
        $_userOrganizationPhone = '';
        $_userOrganizationFax = '';
        $_userOrganizationWebsite = '';
        $_userOrganizationCountry = false;
        $_userOrganizationID = 0;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            if ($_SWIFT->Staff->GetPermission('staff_caninsertusernote') != '0') {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('addnote'), 'fa-sticky-note-o', "UICreateWindow('" . SWIFT::Get('basename') . '/Base/UserOrganization/AddNote/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID() . "', 'addnote', '" . $_SWIFT->Language->Get('addnote') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 600, 360, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            }

            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Base/UserOrganization/Delete/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('userorganization'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_userOrganizationID = $_SWIFT_UserOrganizationObject->GetUserOrganizationID();
            $_userOrganizationName = $_SWIFT_UserOrganizationObject->GetProperty('organizationname');
            $_userOrganizationType = (int)($_SWIFT_UserOrganizationObject->GetProperty('organizationtype'));
            $_userOrganizationSLAPlanID = (int)($_SWIFT_UserOrganizationObject->GetProperty('slaplanid'));
            $_userOrganizationSLAExpiry = (int)($_SWIFT_UserOrganizationObject->GetProperty('slaexpirytimeline'));

            $_userOrganizationAddress = $_SWIFT_UserOrganizationObject->GetProperty('address');
            $_userOrganizationCity = $_SWIFT_UserOrganizationObject->GetProperty('city');
            $_userOrganizationState = $_SWIFT_UserOrganizationObject->GetProperty('state');
            $_userOrganizationCountry = $_SWIFT_UserOrganizationObject->GetProperty('country');
            $_userOrganizationPostalCode = $_SWIFT_UserOrganizationObject->GetProperty('postalcode');
            $_userOrganizationPhone = $_SWIFT_UserOrganizationObject->GetProperty('phone');
            $_userOrganizationFax = $_SWIFT_UserOrganizationObject->GetProperty('fax');
            $_userOrganizationWebsite = $_SWIFT_UserOrganizationObject->GetProperty('website');
            $_userOrganizationCountry = $_SWIFT_UserOrganizationObject->GetProperty('country');

            $_userOrganizationTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_USERORGANIZATION, $_SWIFT_UserOrganizationObject->GetUserOrganizationID());
            $_userOrganizationEmailContainer = SWIFT_UserOrganizationEmail::RetrieveList($_SWIFT_UserOrganizationObject->GetUserOrganizationID());

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('insertorganization'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_notesHTML = $this->RenderUserNotes($_SWIFT_UserOrganizationObject);

            if (!empty($_notesHTML)) {
                $_GeneralTabObject->RowHTML('<tr class="gridrow3" id="usernotescontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3"><div id="usernotescontainerdiv">' . $_notesHTML . '</div></td></tr>');
            } else {
                $_GeneralTabObject->RowHTML('<tr class="gridrow3" style="display: none;" id="usernotescontainerdivholder"><td colspan="2" align="left" valign="top class="gridrow3"><div id="usernotescontainerdiv"></div></td></tr>');
            }
        }

        $_GeneralTabObject->Text('organizationname', $this->Language->Get('organizationname'), $this->Language->Get('desc_organizationname'), $_userOrganizationName);

        $_itemContainer = array();
        $_index = 0;
        foreach (array(SWIFT_UserOrganization::TYPE_RESTRICTED => $this->Language->Get('userorganizationrestricted'), SWIFT_UserOrganization::TYPE_SHARED => $this->Language->Get('userorganizationshared')) as $_key => $_val) {
            $_itemContainer[$_index]['title'] = $_val;
            $_itemContainer[$_index]['value'] = $_key;

            if ($_key == $_userOrganizationType) {
                $_itemContainer[$_index]['checked'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Radio('organizationtype', $this->Language->Get('organizationtype2'), $this->Language->Get('desc_organizationtype'), $_itemContainer, false);

        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            $_GeneralTabObject->TextMultipleAutoComplete('tags', false, false, '/Base/Tags/QuickSearch', $_userOrganizationTagContainer, 'fa-tags', 'gridrow1', true);
        }

        $_GeneralTabObject->Title($this->Language->Get('generaloptions'), 'icon_doublearrows.gif');

        $_GeneralTabObject->TextMultipleAutoComplete('emails', $this->Language->Get('userorganizationemails'), $this->Language->Get('desc_userorganizationemails'), false, $_userOrganizationEmailContainer, 'fa-tags', false, false, 2, false, false, false, '', array('containemail' => true));

        $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, $_mode, array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION), $_GeneralTabObject, $_userOrganizationID);

        // Only display the add note column in Insert
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_GeneralTabObject->Notes('userorganizationnotes', $this->Language->Get('addnotes'));
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

        $_ProfileTabObject->Title($this->Language->Get('contactdetails'), 'icon_doublearrows.gif');

        $_ProfileTabObject->TextArea('address', $this->Language->Get('organizationaddress'), $this->Language->Get('desc_organizationaddress'), $_userOrganizationAddress);
        $_ProfileTabObject->Text('city', $this->Language->Get('organizationcity'), $this->Language->Get('desc_organizationcity'), $_userOrganizationCity);
        $_ProfileTabObject->Text('state', $this->Language->Get('organizationstate'), $this->Language->Get('desc_organizationstate'), $_userOrganizationState);
        $_ProfileTabObject->Text('postalcode', $this->Language->Get('organizationpostalcode'), $this->Language->Get('desc_organizationpostalcode'), $_userOrganizationPostalCode, 'text', 10);

        $_countryList = $this->Controller->CountryContainer->GetList();
        $_optionsContainer = array();
        $_index = 0;
        foreach ($_countryList as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val;
            $_optionsContainer[$_index]['value'] = trim($_val);

            if ($_userOrganizationCountry == trim($_val) || (!$_userOrganizationCountry && $_index == 0)) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_ProfileTabObject->Select('country', $this->Language->Get('organizationcountry'), $this->Language->Get('desc_organizationcountry'), $_optionsContainer);
        $_ProfileTabObject->Text('phone', $this->Language->Get('organizationphone'), $this->Language->Get('desc_organizationphone'), $_userOrganizationPhone, 'text', 20);
        $_ProfileTabObject->Text('fax', $this->Language->Get('organizationfax'), $this->Language->Get('desc_organizationfax'), $_userOrganizationFax, 'text', 20);
        $_ProfileTabObject->Text('website', $this->Language->Get('organizationwebsite'), $this->Language->Get('desc_organizationwebsite'), $_userOrganizationWebsite);

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_ProfileTabObject->Title($this->Language->Get('usercustomsla'), 'icon_doublearrows.gif');

            $_optionsContainer = array();
            $_index = 1;

            $_optionsContainer[0]['title'] = $this->Language->Get('defaultslaplan');
            $_optionsContainer[0]['value'] = 0;
            if (!$_userOrganizationSLAPlanID) {
                $_optionsContainer[0]['selected'] = true;
            }

            foreach ($_slaPlanCache as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $_val['title'];
                $_optionsContainer[$_index]['value'] = $_val['slaplanid'];

                /**
                 * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                 *
                 * SWIFT-4430 Disabled SLA plan can be implemented over a ticket manually from 'Edit' tab.
                 */
                if ($_val['isenabled'] == '0') {
                    $_optionsContainer[$_index]['disabled'] = true;
                }

                if ($_val['slaplanid'] == $_userOrganizationSLAPlanID) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_ProfileTabObject->Select('slaplanid', $this->Language->Get('userorganizationslaplan'), $this->Language->Get('desc_userorganizationslaplan'), $_optionsContainer);

            $_userOrganizationSLAExpiryDate = '';
            if (!empty($_userOrganizationSLAExpiry)) {
                $_userOrganizationSLAExpiryDate = gmdate(SWIFT_Date::GetCalendarDateFormat(), $_userOrganizationSLAExpiry);
            }

            $_ProfileTabObject->Date('slaexpirytimeline', $this->Language->Get('userorganizationslaexpiry'), $this->Language->Get('desc_userorganizationslaexpiry'), $_userOrganizationSLAExpiryDate);
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
         * BEGIN USERS TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_userCountContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "userorganizationlinks l JOIN " . TABLE_PREFIX . "users u USING (userid) WHERE l.userorganizationid = '" . (int)($_SWIFT_UserOrganizationObject->GetUserOrganizationID()) . "'");
            if (isset($_userCountContainer['totalitems']) && !empty($_userCountContainer['totalitems'])) {
                // We have users!!
                if ((int)($_userCountContainer['totalitems']) > 99) {
                    $_totalItemCount = '99+';
                } else {
                    $_totalItemCount = (int)($_userCountContainer['totalitems']);
                }

                $_UserTabObject = $this->UserInterface->AddTab($this->Language->Get('tabusers'), 'icon_contacts.png', 'users', false);
                $_UserTabObject->SetTabCounter($_totalItemCount);

                // Retrieve all users
                $_userContainer = array();
                $this->Database->Query("SELECT u.* FROM " . TABLE_PREFIX . "userorganizationlinks l JOIN " . TABLE_PREFIX . "users u USING (userid) WHERE l.userorganizationid = '" . (int)($_SWIFT_UserOrganizationObject->GetUserOrganizationID()) . "'");
                while ($this->Database->NextRecord()) {
                    $_userContainer[$this->Database->Record['userid']] = $this->Database->Record;
                }

                $_columnContainer = array();
                $_columnContainer[0]['value'] = '&nbsp;';
                $_columnContainer[0]['align'] = 'center';
                $_columnContainer[0]['width'] = '20';
                $_columnContainer[1]['value'] = $this->Language->Get('userfullname');
                $_columnContainer[1]['align'] = 'left';
                $_columnContainer[2]['value'] = $this->Language->Get('userdesignation');
                $_columnContainer[2]['align'] = 'center';
                $_columnContainer[2]['width'] = '200';
                $_columnContainer[3]['value'] = $this->Language->Get('userrole');
                $_columnContainer[3]['align'] = 'center';
                $_columnContainer[3]['width'] = '150';
                $_columnContainer[4]['value'] = $this->Language->Get('usergroup');
                $_columnContainer[4]['align'] = 'center';
                $_columnContainer[4]['width'] = '180';

                $_UserTabObject->Row($_columnContainer, 'gridtabletitlerow');

                foreach ($_userContainer as $_key => $_val) {
                    $_titleUserRole = $this->Language->Get('userroleuser');
                    if ($_val['userrole'] == SWIFT_User::ROLE_MANAGER) {
                        $_titleUserRole = $this->Language->Get('userrolemanager');
                    }

                    if (isset($_userGroupCache[$_val['usergroupid']])) {
                        $_titleUserGroup = htmlspecialchars($_userGroupCache[$_val['usergroupid']]['title']);
                    } else {
                        $_titleUserGroup = $this->Language->Get('na');
                    }

                    $_columnContainer = array();
                    $_columnContainer[0]['value'] = '<i class="fa fa-user" aria-hidden="true"></i>';
                    $_columnContainer[0]['align'] = 'center';
                    $_columnContainer[1]['value'] = '<a href="' . SWIFT::Get('basename') . '/Base/User/Edit/' . (int)($_val['userid']) . '" viewport="1">' . text_to_html_entities($_val['fullname']) . '</a>';
                    $_columnContainer[1]['align'] = 'left';
                    $_columnContainer[2]['value'] = htmlspecialchars($_val['userdesignation']);
                    $_columnContainer[2]['align'] = 'center';
                    $_columnContainer[3]['value'] = $_titleUserRole;
                    $_columnContainer[3]['align'] = 'center';
                    $_columnContainer[4]['value'] = $_titleUserGroup;
                    $_columnContainer[4]['align'] = 'center';

                    $_UserTabObject->Row($_columnContainer);
                }
            }
        }

        /*
         * ###############################################
         * END USERS TAB
         * ###############################################
         */

        // Begin Hook: staff_userorganization_tabs
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_userorganization_tabs')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the User Organization Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderGrid($_searchStoreID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('userorganizationgrid', false, true), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $_searchSuffix = '(' . $this->UserInterfaceGrid->BuildSQLSearch('organizationname') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('city') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('state') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('country') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('address') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('phone') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('website') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('postalcode') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('fax') . ')';

            $this->UserInterfaceGrid->SetSearchQuery('SELECT o.*, (SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'userorganizationlinks l JOIN '. TABLE_PREFIX. 'users u USING (userid) where l.userorganizationid = o.userorganizationid) AS totalusers FROM ' . TABLE_PREFIX . 'userorganizations o WHERE ' . $_searchSuffix, 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'userorganizations WHERE ' . $_searchSuffix);
        }

        $this->UserInterfaceGrid->SetQuery('SELECT *, (SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'userorganizationlinks l JOIN '. TABLE_PREFIX. 'users u USING (userid) where l.userorganizationid = o.userorganizationid) AS totalusers FROM ' . TABLE_PREFIX . 'userorganizations AS o', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'userorganizations');

        // Set Tag Lookup Queries..
        $this->UserInterfaceGrid->SetTagOptions(SWIFT_TagLink::TYPE_USERORGANIZATION, "SELECT *, (SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'userorganizationlinks l where l.userorganizationid = userorganizations.userorganizationid) AS totalusers FROM " . TABLE_PREFIX . "userorganizations AS userorganizations WHERE userorganizations.userorganizationid IN (%s)"

            , "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "userorganizations AS userorganizations WHERE userorganizations.userorganizationid IN (%s)");


        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
            "SELECT *, (SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'userorganizationlinks l where l.userorganizationid = userorganizations.userorganizationid) AS totalusers FROM " . TABLE_PREFIX . "userorganizations AS userorganizations
                    WHERE userorganizations.userorganizationid IN (%s)", SWIFT_SearchStore::TYPE_USERORGANIZATIONS, SWIFT::Get('basename') . '/Base/UserOrganization/Manage/-1');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('userorganizationid', 'userorganizationid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('organizationname', $this->Language->Get('organizationname'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('organizationtype', $this->Language->Get('organizationtype'), SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('city', $this->Language->Get('organizationcity'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('state', $this->Language->Get('organizationstate'), SWIFT_UserInterfaceGridField::TYPE_DB, 140, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('country', $this->Language->Get('organizationcountry'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('totalusers', $this->Language->Get('users'), SWIFT_UserInterfaceGridField::TYPE_DB, 80, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Base\Staff\Controller_UserOrganization', 'DeleteList'), $this->Language->Get('actionconfirm')));

        if ($_SWIFT->Staff->GetPermission('staff_canupdateuserorganization') != '0') {
            $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('merge'), 'fa-random',
                array('Base\Staff\Controller_UserOrganization', 'MergeOrganizationList'), '',
                array($this->Language->Get('mergeorganization'), '500', '180', array($this->Controller, '_MergeOrganizationDialog'))));
        }

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Base/UserOrganization/Insert');

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

        $_userOrganizationURL = SWIFT::Get('basename') . "/Base/UserOrganization/Edit/" . (int)($_fieldContainer['userorganizationid']);

        $_fieldContainer['icon'] = '<i class="fa fa-institution" aria-hidden="true"></i>';

        $_fieldContainer['organizationname'] = '<a href="' . $_userOrganizationURL . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['organizationname']) . '</a>';

        $_fieldContainer['organizationtype'] = SWIFT_UserOrganization::GetOrganizationTypeLabel($_fieldContainer['organizationtype']);

        $_fieldContainer['city'] = htmlspecialchars($_fieldContainer['city']);
        $_fieldContainer['state'] = htmlspecialchars($_fieldContainer['state']);
        $_fieldContainer['country'] = htmlspecialchars($_fieldContainer['country']);

        return $_fieldContainer;
    }

    /**
     * Render the Notes for the given User (including his organization notes)
     *
     * @author Varun Shoor
     * @param SWIFT_UserOrganization $_SWIFT_UserOrganizationObject The SWIFT_UserOrganization Object
     * @return mixed "_renderedHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RenderUserNotes(SWIFT_UserOrganization $_SWIFT_UserOrganizationObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        } else if ($_SWIFT->Staff->GetPermission('staff_canviewusernotes') == '0') {
            return '';
        }

        // Retrieve notes
        $_userNoteContainer = array();
        $_renderedHTML = '';

        $this->Database->Query("SELECT usernotes.*, usernotedata.* FROM " . TABLE_PREFIX . "usernotes AS usernotes LEFT JOIN " . TABLE_PREFIX . "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid) WHERE (usernotes.linktype = '" . SWIFT_UserOrganizationNote::LINKTYPE_ORGANIZATION . "' AND usernotes.linktypeid = '" . (int)($_SWIFT_UserOrganizationObject->GetUserOrganizationID()) . "') ORDER BY usernotes.dateline DESC");
        while ($this->Database->NextRecord()) {
            $_userNoteContainer[] = $this->Database->Record;

            unset($_icon);

            $_icon = 'fa-institution';

            $_renderedHTML .= '<div id="note' . (SWIFT_UserOrganizationNote::GetSanitizedNoteColor($this->Database->Record['notecolor'])) . '" class="bubble"><div class="notebubble"><cite class="tip"><strong><i class="fa ' . $_icon . '" aria-hidden="true"></i> ' . sprintf($this->Language->Get('notetitle'), '<b>' . htmlspecialchars($this->Database->Record['staffname']) . '</b>', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Database->Record['dateline']))

                . IIF(!empty($this->Database->Record['editedstaffid']) && !empty($this->Database->Record['editedstaffname']), sprintf($this->Language->Get('noteeditedtitle'), htmlspecialchars($this->Database->Record['editedstaffname']), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Database->Record['editedtimeline'])))

                . '</strong><div class="ticketnotesactions">';

            if ($_SWIFT->Staff->GetPermission('staff_canupdateusernote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: UICreateWindow(\'' . SWIFT::Get('basename') . '/Base/UserOrganization/EditNote/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID() . '/' . (int)($this->Database->Record['usernoteid']) . "', 'editnote', '" . $this->Language->Get('editnote') . "', '" . $this->Language->Get('loadingwindow') . '\', 600, 360, true, this);"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> ';
            }

            if ($_SWIFT->Staff->GetPermission('staff_candeleteusernote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: UserOrganizationDeleteNote(\'' . addslashes($this->Language->Get('usernotedelconfirm')) . '\', \'' . (int)($_SWIFT_UserOrganizationObject->GetUserOrganizationID()) . '/' . (int)($this->Database->Record['usernoteid']) . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            }

            $_renderedHTML .= '</div></cite><blockquote><p>' . nl2br(htmlspecialchars($this->Database->Record['notecontents'])) . '</p></blockquote></div></div>';
        }

        return $_renderedHTML;
    }

    /**
     * Render the Add Note Dialog
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_UserOrganization $_SWIFT_UserOrganizationObject The SWIFT_UserOrganization Object Pointer
     * @param SWIFT_UserOrganizationNote $_SWIFT_UserOrganizationNoteObject The SWIFT_UserOrganizationNote Object Poitner
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderNoteForm($_mode, SWIFT_UserOrganization $_SWIFT_UserOrganizationObject, SWIFT_UserOrganizationNote $_SWIFT_UserOrganizationNoteObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $this->UserInterface->Start('useraddnotes', '/Base/UserOrganization/AddNoteSubmit/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_UserInterface::MODE_EDIT, true, false, false, false, 'usernotescontainerdiv');
        } else {
            $this->UserInterface->Start('useraddnotes', '/Base/UserOrganization/EditNoteSubmit/' . $_SWIFT_UserOrganizationObject->GetUserOrganizationID() . '/' . $_SWIFT_UserOrganizationNoteObject->GetUserNoteID(), SWIFT_UserInterface::MODE_EDIT, true, false, false, false, 'usernotescontainerdiv');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('userorganization'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_defaultNoteContents = '';
        $_defaultNoteColor = 1;
        if ($_SWIFT_UserOrganizationNoteObject instanceof SWIFT_UserOrganizationNote && $_SWIFT_UserOrganizationNoteObject->GetIsClassLoaded()) {
            $_defaultNoteContents = $_SWIFT_UserOrganizationNoteObject->GetProperty('notecontents');
            $_defaultNoteColor = (int)($_SWIFT_UserOrganizationNoteObject->GetProperty('notecolor'));
        }

        /*
         * ###############################################
         * BEGIN ADD NOTES TAB
         * ###############################################
         */

        $_AddNoteTabObject = $this->UserInterface->AddTab(IIF($_mode == SWIFT_UserInterface::MODE_INSERT, $this->Language->Get('tabaddnote'), $this->Language->Get('tabeditnote')), 'icon_note.png', 'addnote', true);

        $_AddNoteTabObject->Notes('userorganizationnotes', $this->Language->Get('addnotes'), $_defaultNoteContents, $_defaultNoteColor);

        /*
         * ###############################################
         * END ADD NOTES TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the User Merge Organization Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderMergeOrganization()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start('MergeOrganizationDialog', $_POST['_gridURL'], SWIFT_UserInterface::MODE_INSERT, false, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('userorganization'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);
        $_GeneralTabObject->SetColumnWidth('150');

        $_optionsContainer = array();
        $_index = 0;

        if (isset($_POST['itemid']) && _is_array($_POST['itemid'])) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE userorganizationid IN (" . BuildIN($_POST['itemid']) . ")");
            while ($this->Database->NextRecord()) {
                $_optionsContainer[$_index]['title'] = $this->Database->Record['organizationname'];
                $_optionsContainer[$_index]['value'] = $this->Database->Record['userorganizationid'];

                if ($_index == 0) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

        } else {
            $_optionsContainer[0]['title'] = $this->Language->Get('na');
            $_optionsContainer[0]['value'] = '0';
            $_optionsContainer[0]['selected'] = true;
        }

        $_GeneralTabObject->Select('primaryorganizationid', $this->Language->Get('primaryorganization'), '', $_optionsContainer);

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
