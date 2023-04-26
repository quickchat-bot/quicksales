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

namespace Base\Library\UnifiedSearch;

use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_UserEmail;
use SWIFT;
use SWIFT_App;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchBase;

/**
 * The Unified Search Library for Base App
 *
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch_base extends SWIFT_UnifiedSearchBase
{

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_query The Search Query
     * @param mixed $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_maxResults
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_query, $_interfaceType, SWIFT_Staff $_SWIFT_StaffObject, $_maxResults)
    {
        parent::__construct($_query, $_interfaceType, $_SWIFT_StaffObject, $_maxResults);
    }

    /**
     * Run the search and return results
     *
     * @author Varun Shoor
     * @return array Container of Result Objects
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * ---------------------------------------------
         * SEARCH NAVIGATION
         * ---------------------------------------------
         */
        $_searchResults = array();
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
            $_searchResults = $this->SearchNavigationStaff();
        } elseif ($this->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
            $_searchResults = $this->SearchNavigationAdmin();
        }

        $_finalSearchResults = $_searchResults;


        /**
         * ---------------------------------------------
         * ADMIN SPECIFIC
         * ---------------------------------------------
         */
        if ($this->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
            // Settings
            $_finalSearchResults[$this->Language->Get('us_settings')] = $this->SearchSettings();

            // Staff
            $_finalSearchResults[$this->Language->Get('us_staff')] = $this->SearchStaff();

            // Teams
            $_finalSearchResults[$this->Language->Get('us_teams')] = $this->SearchTeams();

            // Departments
            $_finalSearchResults[$this->Language->Get('us_departments')] = $this->SearchDepartments();

            // User Groups
            $_finalSearchResults[$this->Language->Get('us_usergroups')] = $this->SearchUserGroups();

            // Template Groups
            $_finalSearchResults[$this->Language->Get('us_templategroups')] = $this->SearchTemplateGroups();

            // Templates
            $_finalSearchResults[$this->Language->Get('us_templates')] = $this->SearchTemplates();

            // Languages
            $_finalSearchResults[$this->Language->Get('us_languages')] = $this->SearchLanguages();

            // Custom Field Groups
            $_finalSearchResults[$this->Language->Get('us_cfgroup')] = $this->SearchCustomFieldGroups();

            // Custom Fields
            $_finalSearchResults[$this->Language->Get('us_cfields')] = $this->SearchCustomFields();

            // Ratings
            $_finalSearchResults[$this->Language->Get('us_ratings')] = $this->SearchRatings();

            // Widgets
            $_finalSearchResults[$this->Language->Get('us_widgets')] = $this->SearchWidgets();

            // Apps
            $_finalSearchResults[$this->Language->Get('us_apps')] = $this->SearchApps();


            /**
             * ---------------------------------------------
             * STAFF SPECIFIC
             * ---------------------------------------------
             */
        } elseif ($this->GetInterface() == SWIFT_INTERFACE::INTERFACE_STAFF) {
            // Users
            $_finalSearchResults[$this->Language->Get('us_users') . '::' . $this->Language->Get('us_updated')] = $this->SearchUsers();

            // User Organizations
            $_finalSearchResults[$this->Language->Get('us_userorganizations') . '::' . $this->Language->Get('us_created')] = $this->SearchUserOrganizations();

            // Tags
            $_finalSearchResults = array_merge($_finalSearchResults, $this->SearchTags());
        }

        return $_finalSearchResults;
    }

    /**
     * Run the search for staff interface
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchNavigationStaff()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchResults = array();

        $_staffMenuContainer = SWIFT::Get('staffmenu');
        $_staffLinkContainer = SWIFT::Get('stafflinks');

        foreach ($_staffMenuContainer as $_menuID => $_menuContainer) {
            if (!isset($_staffLinkContainer[$_menuID])) {
                continue;
            }

            foreach ($_staffLinkContainer[$_menuID] as $_linkID => $_linkContainer) {
                if (count($_searchResults) >= $this->GetMaxResults()) {
                    break;
                }

                if (stristr($_linkContainer[0], $this->GetQuery())) {
                    if (substr($_linkContainer[1], 0, strlen(':UIDropDown')) == ':UIDropDown') {
                        continue;
                    }

                    if (!isset($_searchResults[$_menuContainer[0]])) {
                        $_searchResults[$_menuContainer[0]] = array();
                    }

                    $_searchResults[$_menuContainer[0]][] = array($_linkContainer[0], $_linkContainer[1]);
                }
            }
        }

        return $_searchResults;
    }

    /**
     * Run the search for admin interface
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchNavigationAdmin()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchResults = array();

        $_adminMenuContainer = SWIFT::Get('adminmenu');
        $_adminLinkContainer = SWIFT::Get('adminlinks');

        foreach ($_adminMenuContainer as $_menuID => $_menuContainer) {
            if (!isset($_adminLinkContainer[$_menuID])) {
                continue;
            }

            foreach ($_adminLinkContainer[$_menuID] as $_linkID => $_linkContainer) {
                if (count($_searchResults) >= $this->GetMaxResults()) {
                    break;
                }

                if (stristr($_linkContainer[0], $this->GetQuery())) {
                    if (!isset($_searchResults[$_menuContainer[0]])) {
                        $_searchResults[$_menuContainer[0]] = array();
                    }

                    $_searchResults[$_menuContainer[0]][] = array($_linkContainer[0], $_linkContainer[1]);
                }
            }
        }

        $_adminBarContainer = SWIFT::Get('adminbar');
        $_adminBarItemContainer = SWIFT::Get('adminbaritems');
        foreach ($_adminBarContainer as $_menuID => $_menuContainer) {
            if (!isset($_adminBarItemContainer[$_menuID])) {
                continue;
            }

            $_addAll = false;

            if (stristr($_menuContainer[0], $this->GetQuery())) {
                $_addAll = true;
            }

            foreach ($_adminBarItemContainer[$_menuID] as $_barID => $_barContainer) {
                if (count($_searchResults) >= $this->GetMaxResults()) {
                    break;
                }

                if ($_addAll == true || stristr($_barContainer[0], $this->GetQuery())) {
                    if (!isset($_searchResults[$_menuContainer[0]])) {
                        $_searchResults[$_menuContainer[0]] = array();
                    }

                    $_searchResults[$_menuContainer[0]][] = array($_barContainer[0], $_barContainer[1]);
                }
            }
        }

        return $_searchResults;
    }

    /**
     * Search the Settings
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchSettings()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canupdatesettings') == '0') {
            return array();
        }

        $this->Language->Load('settings');

        $_searchResults = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settingsgroups WHERE ishidden = '0' ORDER BY name ASC");
        while ($this->Database->NextRecord()) {
            $_settingTitle = $this->Language->Get($this->Database->Record['name']);
            if (empty($_settingTitle)) {
                $_settingTitle = $this->Database->Record['name'];
            }

            if (stristr($_settingTitle, $this->GetQuery())) {
                $_searchResults[] = array($_settingTitle, SWIFT::Get('basename') . '/Base/Settings/View/' . $this->Database->Record['sgroupid']);
            }
        }

        return $_searchResults;
    }

    /**
     * Search the Staff
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchStaff()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewstaff') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "staff
            WHERE (" . BuildSQLSearch('fullname', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('username', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('email', $this->GetQuery(), false, false) . ")
                 OR (" . BuildSQLSearch('designation', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('mobilenumber', $this->GetQuery(), false, false) . ")
            ORDER BY fullname ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['fullname'], SWIFT::Get('basename') . '/Base/Staff/Edit/' . $this->Database->Record['staffid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Teams
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchTeams()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewstaffgroup') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "staffgroup
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['title'], SWIFT::Get('basename') . '/Base/StaffGroup/Edit/' . $this->Database->Record['staffgroupid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Departments
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchDepartments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewdepartments') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "departments
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_appTitle = $this->Language->Get('app_' . $this->Database->Record['departmentapp']);
            if (empty($_appTitle)) {
                $_appTitle = $this->Database->Record['departmentapp'];
            }

            $_searchResults[] = array($this->Database->Record['title'] . ' (' . $_appTitle . ')', SWIFT::Get('basename') . '/Base/Department/Edit/' . $this->Database->Record['departmentid']);
        }

        return $_searchResults;
    }

    /**
     * Search the User Groups
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchUserGroups()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewusergroups') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "usergroups
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['title'], SWIFT::Get('basename') . '/Base/UserGroup/Edit/' . $this->Database->Record['usergroupid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Template Groups
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchTemplateGroups()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tmpcanviewgroups') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "templategroups
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['title'], SWIFT::Get('basename') . '/Base/TemplateGroup/Edit/' . $this->Database->Record['tgroupid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Templates
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchTemplates()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_tmpcanviewtemplates') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "templates
            WHERE (" . BuildSQLSearch('name', $this->GetQuery(), false, false) . ")
            ORDER BY name ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['name'], SWIFT::Get('basename') . '/Base/Template/Edit/' . $this->Database->Record['templateid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Languages
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchLanguages()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewlanguages') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "languages
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('languagecode', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('charset', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_extendedInfo = sprintf($this->Language->Get('usi_langcode'), htmlspecialchars($this->Database->Record['languagecode'])) . '<br />';
            $_extendedInfo .= sprintf($this->Language->Get('usi_charset'), htmlspecialchars($this->Database->Record['charset'])) . '<br />';

            $_searchResults[] = array($this->Database->Record['title'], SWIFT::Get('basename') . '/Base/Language/Edit/' . $this->Database->Record['languageid'], '', $_extendedInfo);
        }

        return $_searchResults;
    }

    /**
     * Search the Custom Field Groups
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchCustomFieldGroups()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewcfgroups') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups
            WHERE (" . BuildSQLSearch('title', $this->GetQuery(), false, false) . ")
            ORDER BY title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['title'], SWIFT::Get('basename') . '/Base/CustomFieldGroup/Edit/' . $this->Database->Record['customfieldgroupid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Custom Fields
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchCustomFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewcfields') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT customfields.*, customfieldgroups.title AS customfieldgrouptitle FROM " . TABLE_PREFIX . "customfields AS customfields
            LEFT JOIN " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid)
            WHERE (" . BuildSQLSearch('customfields.title', $this->GetQuery(), false, false) . ")
            ORDER BY customfields.title ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_extendedInfo = sprintf($this->Language->Get('usi_group'), htmlspecialchars($this->Database->Record['customfieldgrouptitle'])) . '<br />';

            $_searchResults[] = array($this->Database->Record['title'], SWIFT::Get('basename') . '/Base/CustomField/Edit/' . $this->Database->Record['customfieldid'], '', $_extendedInfo);
        }

        return $_searchResults;
    }

    /**
     * Search the Ratings
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchRatings()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewratings') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ratings
            WHERE (" . BuildSQLSearch('ratingtitle', $this->GetQuery(), false, false) . ")
            ORDER BY dateline DESC, ratingtitle ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchResults[] = array($this->Database->Record['ratingtitle'], SWIFT::Get('basename') . '/Base/Rating/Edit/' . $this->Database->Record['ratingid']);
        }

        return $_searchResults;
    }

    /**
     * Search the Widgets
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchWidgets()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canviewwidgets') == '0') {
            return array();
        }

        $this->Language->Load('admin_widgets');

        $_searchResults = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "widgets");
        while ($this->Database->NextRecord()) {
            $_widgetTitle = $this->Database->Record['defaulttitle'];
            if (strtoupper(substr($_widgetTitle, 0, strlen('PHRASE'))) == 'PHRASE') {
                $_widgetTitle = $this->Language->Get(substr($_widgetTitle, strlen('PHRASE') + 1));
            }

            if (stristr($_widgetTitle, $this->GetQuery())) {
                $_searchResults[] = array($_widgetTitle, SWIFT::Get('basename') . '/Base/Widget/Edit/' . $this->Database->Record['widgetid']);
            }
        }

        return $_searchResults;
    }

    /**
     * Search the Apps
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchApps()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('admin_canmanageapps') == '0') {
            return array();
        }

        $_searchResults = array();

        $_installedAppList = SWIFT_App::GetInstalledApps();

        foreach ($_installedAppList as $_appName) {
            $_appTitle = $this->Language->Get('app_' . $_appName);
            if (empty($_appTitle)) {
                $_appTitle = $_appName;
            }

            if (stristr($_appTitle, $this->GetQuery())) {
                $_searchResults[] = array($_appTitle, SWIFT::Get('basename') . '/Base/App/View/' . $_appName);
            }
        }

        return $_searchResults;
    }

    /**
     * Search the Users
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchUsers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_canviewusers') == '0') {
            return array();
        }

        $_searchResults = $_userIDList = $_userEmailContainer = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "useremails
            WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND (" . BuildSQLSearch('email', $this->GetQuery(), false, false) . ")
            ORDER BY email ASC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_userIDList[] = $this->Database->Record['linktypeid'];

            if (!isset($_userEmailContainer[$this->Database->Record['linktypeid']])) {
                $_userEmailContainer[$this->Database->Record['linktypeid']] = array();
            }

            $_userEmailContainer[$this->Database->Record['linktypeid']][] = $this->Database->Record['email'];
        }

        $_searchUserIDList = $_searchUserEmailUserIDList = array();

        $this->Database->QueryLimit("SELECT users.userid FROM " . TABLE_PREFIX . "users AS users
            WHERE (" . BuildSQLSearch('users.fullname', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('users.userdesignation', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('users.phone', $this->GetQuery(), false, false) . ")
                OR (users.userid IN (" . BuildIN($_userIDList) . "))
            ORDER BY users.lastupdate DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_searchUserIDList[] = $this->Database->Record['userid'];
            if (!isset($_userEmailContainer[$this->Database->Record['userid']])) {
                $_searchUserEmailUserIDList[] = $this->Database->Record['userid'];
            }
        }
        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "useremails
            WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_searchUserEmailUserIDList) . ")
            ORDER BY email ASC");
        while ($this->Database->NextRecord()) {
            if (!isset($_userEmailContainer[$this->Database->Record['linktypeid']])) {
                $_userEmailContainer[$this->Database->Record['linktypeid']] = array();
            }

            $_userEmailContainer[$this->Database->Record['linktypeid']][] = $this->Database->Record['email'];
        }

        $this->Database->QueryLimit("SELECT users.*, userorganizations.organizationname FROM " . TABLE_PREFIX . "users AS users
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            WHERE users.userid IN (" . BuildIN($_searchUserIDList) . ")
            ORDER BY users.lastupdate DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_extendedInfo = '';

            if (isset($_userEmailContainer[$this->Database->Record['userid']])) {
                $_extendedInfo = htmlspecialchars(implode(', ', $_userEmailContainer[$this->Database->Record['userid']])) . '<br />';
            }

            $_finalTitle = $this->Database->Record['fullname'];
            if (!empty($this->Database->Record['userdesignation'])) {
                $_finalTitle .= ' (' . htmlspecialchars($this->Database->Record['userdesignation']) . ')';
            }

            if (!empty($this->Database->Record['organizationname'])) {
                $_extendedInfo .= htmlspecialchars($this->Database->Record['organizationname']) . '<br />';
            }

            if (!empty($this->Database->Record['phone'])) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_phone'), htmlspecialchars($this->Database->Record['phone'])) . '<br />';
            }

            $_easyDateValue = $this->Database->Record['lastupdate'];
            if (empty($_easyDateValue)) {
                $_easyDateValue = $this->Database->Record['dateline'];
            }

            $_searchResults[] = array($_finalTitle, SWIFT::Get('basename') . '/Base/User/Edit/' . $this->Database->Record['userid'], SWIFT_Date::EasyDate($_easyDateValue), $_extendedInfo);
        }

        return $_searchResults;
    }

    /**
     * Search the User Organizations
     *
     * @author Varun Shoor
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchUserOrganizations()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetStaff()->GetPermission('staff_canviewuserorganizations') == '0') {
            return array();
        }

        $_searchResults = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "userorganizations
            WHERE (" . BuildSQLSearch('organizationname', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('address', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('city', $this->GetQuery(), false, false) . ")
                OR (" . BuildSQLSearch('state', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('phone', $this->GetQuery(), false, false) . ") OR (" . BuildSQLSearch('website', $this->GetQuery(), false, false) . ")
            ORDER BY lastupdate DESC", $this->GetMaxResults());
        while ($this->Database->NextRecord()) {
            $_extendedInfo = '';
            if (!empty($this->Database->Record['address'])) {
                $_extendedInfo .= htmlspecialchars($this->Database->Record['address']) . '<br />';
            }

            if (!empty($this->Database->Record['city'])) {
                $_extendedInfo .= htmlspecialchars($this->Database->Record['city']) . IIF($this->Database->Record['state'] != '', ', ' . $this->Database->Record['state']) . '<br />';
            } elseif (!empty($this->Database->Record['state'])) {
                $_extendedInfo .= htmlspecialchars($this->Database->Record['state']) . '<br />';
            }

            if (!empty($this->Database->Record['country'])) {
                $_extendedInfo .= htmlspecialchars($this->Database->Record['country']) . '<br />';
            }

            if (!empty($this->Database->Record['phone'])) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_phone'), htmlspecialchars($this->Database->Record['phone'])) . '<br />';
            }

            if (!empty($this->Database->Record['website'])) {
                $_extendedInfo .= sprintf($this->Language->Get('usi_website'), htmlspecialchars($this->Database->Record['website'])) . '<br />';
            }

            $_searchResults[] = array($this->Database->Record['organizationname'], SWIFT::Get('basename') . '/Base/UserOrganization/Edit/' . $this->Database->Record['userorganizationid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']), $_extendedInfo);
        }

        return $_searchResults;
    }

    /**
     * Search the Tags for given query
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SearchTags()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_interimTagNameList = explode(' ', preg_replace('/\s+/', ' ', $this->GetQuery()));
        $_finalTagNameList = array();
        foreach ($_interimTagNameList as $_tagName) {
            if (trim($_tagName) == '') {
                continue;
            }

            $_finalTagNameList[] = trim(mb_strtolower($_tagName));
        }

        if (!count($_finalTagNameList)) {
            return array();
        }

        $_tagIDList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tags WHERE tagname IN (" . BuildIN($_finalTagNameList) . ")");
        while ($this->Database->NextRecord()) {
            $_tagIDList[] = $this->Database->Record['tagid'];
        }

        if (!count($_tagIDList)) {
            return array();
        }

        $_tagLinkContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks WHERE tagid IN (" . BuildIN($_tagIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (!isset($_tagLinkContainer[$this->Database->Record['linktype']])) {
                $_tagLinkContainer[$this->Database->Record['linktype']] = array();
            }

            $_tagLinkContainer[$this->Database->Record['linktype']][] = $this->Database->Record['linkid'];
        }

        $_finalSearchResults = array();

        foreach ($_tagLinkContainer as $_linkType => $_linkTypeIDList) {
            switch ($_linkType) {
                case SWIFT_TagLink::TYPE_REPORT:
                    if (SWIFT_App::IsInstalled(APP_REPORTS) && $this->GetStaff()->GetPermission('staff_rcanviewreports') != '0') {
                        if (!isset($_finalSearchResults[$this->Language->Get('us_reports')])) {
                            $_finalSearchResults[$this->Language->Get('us_reports')] = array();
                        }

                        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "reports
                            WHERE reportid IN (" . BuildIN($_linkTypeIDList) . ")
                            ORDER BY executedateline DESC", $this->GetMaxResults());
                        while ($this->Database->NextRecord()) {
                            $_finalSearchResults[$this->Language->Get('us_reports') . '::' . $this->Language->Get('us_created')][] = array($this->Database->Record['title'], SWIFT::Get('basename') . '/Reports/Report/Edit/' . $this->Database->Record['reportid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']));
                        }
                    }

                    break;

                /**
                 * ---------------------------------------------
                 * User
                 * ---------------------------------------------
                 */
                case SWIFT_TagLink::TYPE_USER:
                    if ($this->GetStaff()->GetPermission('staff_canviewusers') != '0') {
                        if (!isset($_finalSearchResults[$this->Language->Get('us_users')])) {
                            $_finalSearchResults[$this->Language->Get('us_users')] = array();
                        }

                        $_userEmailContainer = array();
                        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "useremails
                            WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_linkTypeIDList) . ")
                            ORDER BY email ASC");
                        while ($this->Database->NextRecord()) {
                            if (!isset($_userEmailContainer[$this->Database->Record['linktypeid']])) {
                                $_userEmailContainer[$this->Database->Record['linktypeid']] = array();
                            }

                            $_userEmailContainer[$this->Database->Record['linktypeid']][] = $this->Database->Record['email'];
                        }

                        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "users
                            WHERE userid IN (" . BuildIN($_linkTypeIDList) . ")
                            ORDER BY lastupdate DESC", $this->GetMaxResults());
                        while ($this->Database->NextRecord()) {
                            $_extendedInfo = '';

                            if (isset($_userEmailContainer[$this->Database->Record['userid']])) {
                                $_extendedInfo = htmlspecialchars(implode(', ', $_userEmailContainer[$this->Database->Record['userid']])) . '<br />';
                            }

                            $_finalTitle = $this->Database->Record['fullname'];
                            if (!empty($this->Database->Record['userdesignation'])) {
                                $_finalTitle .= ' (' . htmlspecialchars($this->Database->Record['userdesignation']) . ')';
                            }

                            if (!empty($this->Database->Record['organizationname'])) {
                                $_extendedInfo .= htmlspecialchars($this->Database->Record['organizationname']) . '<br />';
                            }

                            if (!empty($this->Database->Record['phone'])) {
                                $_extendedInfo .= sprintf($this->Language->Get('usi_phone'), htmlspecialchars($this->Database->Record['phone'])) . '<br />';
                            }

                            $_easyDateValue = $this->Database->Record['lastupdate'];
                            if (empty($_easyDateValue)) {
                                $_easyDateValue = $this->Database->Record['dateline'];
                            }

                            $_finalSearchResults[$this->Language->Get('us_users') . '::' . $this->Language->Get('us_updated')][] = array($_finalTitle, SWIFT::Get('basename') . '/Base/User/Edit/' . $this->Database->Record['userid'], SWIFT_Date::EasyDate($_easyDateValue), $_extendedInfo);
                        }
                    }

                    break;


                /**
                 * ---------------------------------------------
                 * User Organization
                 * ---------------------------------------------
                 */
                case SWIFT_TagLink::TYPE_USERORGANIZATION:
                    if ($this->GetStaff()->GetPermission('staff_canviewuserorganizations') != '0') {
                        if (!isset($_finalSearchResults[$this->Language->Get('us_userorganizations')])) {
                            $_finalSearchResults[$this->Language->Get('us_userorganizations')] = array();
                        }

                        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "userorganizations
                            WHERE userorganizationid IN (" . BuildIN($_linkTypeIDList) . ")
                            ORDER BY lastupdate DESC", $this->GetMaxResults());
                        while ($this->Database->NextRecord()) {
                            $_extendedInfo = '';
                            if (!empty($this->Database->Record['address'])) {
                                $_extendedInfo .= htmlspecialchars($this->Database->Record['address']) . '<br />';
                            }

                            if (!empty($this->Database->Record['city'])) {
                                $_extendedInfo .= htmlspecialchars($this->Database->Record['city']) . IIF($this->Database->Record['state'] != '', ', ' . $this->Database->Record['state']) . '<br />';
                            } elseif (!empty($this->Database->Record['state'])) {
                                $_extendedInfo .= htmlspecialchars($this->Database->Record['state']) . '<br />';
                            }

                            if (!empty($this->Database->Record['country'])) {
                                $_extendedInfo .= htmlspecialchars($this->Database->Record['country']) . '<br />';
                            }

                            if (!empty($this->Database->Record['phone'])) {
                                $_extendedInfo .= sprintf($this->Language->Get('usi_phone'), htmlspecialchars($this->Database->Record['phone'])) . '<br />';
                            }

                            if (!empty($this->Database->Record['website'])) {
                                $_extendedInfo .= sprintf($this->Language->Get('usi_website'), htmlspecialchars($this->Database->Record['website'])) . '<br />';
                            }

                            $_finalSearchResults[$this->Language->Get('us_userorganizations') . '::' . $this->Language->Get('us_created')][] = array($this->Database->Record['organizationname'], SWIFT::Get('basename') . '/Base/UserOrganization/Edit/' . $this->Database->Record['userorganizationid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']), $_extendedInfo);
                        }
                    }

                    break;

                case SWIFT_TagLink::TYPE_CHAT:
                    if (SWIFT_App::IsInstalled(APP_LIVECHAT) && $this->GetStaff()->GetPermission('staff_lscanviewchat') != '0') {
                        if (!isset($_finalSearchResults[$this->Language->Get('us_chathistory')])) {
                            $_finalSearchResults[$this->Language->Get('us_chathistory')] = array();
                        }

                        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chatobjects
                            WHERE chatobjectid IN (" . BuildIN($_linkTypeIDList) . ")
                            ORDER BY dateline DESC", $this->GetMaxResults());
                        while ($this->Database->NextRecord()) {
                            $_chatSubject = $this->Database->Record['subject'];
                            if (trim($_chatSubject) == '') {
                                $_chatSubject = $this->Language->Get('nosubject');
                            }

                            $_finalSearchResults[$this->Language->Get('us_chathistory') . '::' . $this->Language->Get('us_created')][] = array($this->Database->Record['userfullname'] . ': ' . $_chatSubject, SWIFT::Get('basename') . '/LiveChat/ChatHistory/ViewChat/' . $this->Database->Record['chatobjectid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']));
                        }

                    }

                    break;

                case SWIFT_TagLink::TYPE_CHATMESSAGE:
                    if (SWIFT_App::IsInstalled(APP_LIVECHAT) && $this->GetStaff()->GetPermission('staff_lscanviewmessages') != '0') {
                        if (!isset($_finalSearchResults[$this->Language->Get('us_messagessurv')])) {
                            $_finalSearchResults[$this->Language->Get('us_messagessurv')] = array();
                        }

                        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "messages
                            WHERE messageid IN (" . BuildIN($_linkTypeIDList) . ")
                            ORDER BY dateline DESC", $this->GetMaxResults());
                        while ($this->Database->NextRecord()) {
                            $_finalSearchResults[$this->Language->Get('us_messagessurv') . '::' . $this->Language->Get('us_created')][] = array($this->Database->Record['fullname'] . ': ' . $this->Database->Record['subject'], SWIFT::Get('basename') . '/LiveChat/Message/ViewMessage/' . $this->Database->Record['messageid'], SWIFT_Date::EasyDate($this->Database->Record['dateline']));
                        }
                    }

                    break;

                case SWIFT_TagLink::TYPE_TICKET:
                    if (SWIFT_App::IsInstalled(APP_TICKETS) && $this->GetStaff()->GetPermission('staff_tcanviewtickets') != '0') {
                        if (!isset($_finalSearchResults[$this->Language->Get('us_tickets')])) {
                            $_finalSearchResults[$this->Language->Get('us_tickets')] = array();
                        }

                        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets
                            WHERE ticketid IN (" . BuildIN($_linkTypeIDList) . ")
                            ORDER BY dateline DESC", $this->GetMaxResults());
                        while ($this->Database->NextRecord()) {
                            $_finalSearchResults[$this->Language->Get('us_tickets') . '::' . $this->Language->Get('us_updated')][] = array($this->Database->Record['fullname'] . ': ' . $this->Database->Record['subject'], SWIFT::Get('basename') . '/Tickets/Ticket/View/' . $this->Database->Record['ticketid'], SWIFT_Date::EasyDate($this->Database->Record['lastactivity']));
                        }
                    }

                    break;

                default:
                    break;
            }
        }

        return $_finalSearchResults;
    }

}

?>
