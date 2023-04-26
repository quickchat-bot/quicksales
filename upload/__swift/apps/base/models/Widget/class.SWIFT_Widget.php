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

namespace Base\Models\Widget;

use Base\Models\User\SWIFT_UserGroupAssign;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Model;
use Base\Models\Widget\SWIFT_Widget_Exception;

/**
 * The Widget Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Widget extends SWIFT_Model
{
    const TABLE_NAME = 'widgets';
    const PRIMARY_KEY = 'widgetid';

    const TABLE_STRUCTURE = "widgetid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                defaulttitle C(255) DEFAULT '' NOTNULL,
                                appname C(200) DEFAULT '' NOTNULL,
                                widgetlink C(255) DEFAULT '' NOTNULL,
                                defaulticon C(255) DEFAULT '' NOTNULL,
                                defaultsmallicon C(255) DEFAULT '' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                displayinnavbar I2 DEFAULT '0' NOTNULL,
                                displayinindex I2 DEFAULT '0' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL,
                                isenabled I2 DEFAULT '0' NOTNULL,
                                widgetvisibility I2 DEFAULT '0' NOTNULL,
                                uservisibilitycustom I2 DEFAULT '0' NOTNULL,
                                widgetname C(255) DEFAULT '' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'appname';
    const INDEX_2 = 'isenabled';

    const COLUMN_RENAME_MODULENAME = 'appname';

    protected $_dataStore = array();

    // Core Constants
    const VISIBLE_ALL = 1;
    const VISIBLE_LOGGEDIN = 2;
    const VISIBLE_GUESTS = 3;

    const PHRASE_PREFIX = 'PHRASE:';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_widgetID The Widget ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If the Record could not be loaded
     */
    public function __construct($_widgetID)
    {
        parent::__construct();

        if (!$this->LoadData($_widgetID)) {
            throw new SWIFT_Widget_Exception('Failed to load Widget ID: ' . $_widgetID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'widgets', $this->GetUpdatePool(), 'UPDATE', "widgetid = '" . (int)($this->GetWidgetID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Widget ID
     *
     * @author Varun Shoor
     * @return mixed "widgetid" on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If the Class is not Loaded
     */
    public function GetWidgetID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Widget_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['widgetid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_widgetID The Widget ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_widgetID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "widgets WHERE widgetid = '" . $_widgetID . "'");
        if (isset($_dataStore['widgetid']) && !empty($_dataStore['widgetid'])) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Widget_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Widget_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Widget_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Widget visibility
     *
     * @author Varun Shoor
     * @param int $_widgetVisibility The Widget Visibility
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidVisibility($_widgetVisibility)
    {
        if ($_widgetVisibility == self::VISIBLE_ALL || $_widgetVisibility == self::VISIBLE_LOGGEDIN || $_widgetVisibility == self::VISIBLE_GUESTS) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Widget
     *
     * @author Varun Shoor
     * @param string $_defaultTitle The Default Title
     * @param string $_widgetName The Widget Name
     * @param string $_appName The App Name
     * @param string $_widgetLink The Widget Link
     * @param string $_defaultIconName The Default Icon File Name (in themes)
     * @param int $_displayOrder The Display Order
     * @param bool $_displayInNavBar Whether to display the icon in top nav bar
     * @param bool $_displayInIndex Whether to display the widget in Index
     * @param bool $_isMaster Whether it is a master widget (which cannot be deleted)
     * @param bool $_isEnabled Whether this widget is enabled
     * @param int|bool $_widgetVisibility The Widget Visibility
     * @param int|bool $_staffID The Staff ID Creating the widget
     * @param int|bool $_userVisibilityCustom Whether the widget is restricted to custom groups
     * @param array $_userGroupIDList The Custom User Group ID List
     * @return mixed "_widgetID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_defaultTitle, $_widgetName, $_appName, $_widgetLink, $_defaultIconName, $_defaultSmallIconName, $_displayOrder, $_displayInNavBar, $_displayInIndex, $_isMaster, $_isEnabled, $_widgetVisibility, $_staffID, $_userVisibilityCustom = false, $_userGroupIDList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_defaultTitle) || empty($_appName) || empty($_widgetLink) || !self::IsValidVisibility($_widgetVisibility)) {
            throw new SWIFT_Widget_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_displayOrder) || $_displayOrder == 0) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT MAX(displayorder)+1 AS next_order FROM " . TABLE_PREFIX . "widgets");
            $_displayOrder = $_dataStore['next_order'];
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'widgets', array('dateline' => DATENOW, 'defaulttitle' => $_defaultTitle, 'widgetname' => $_widgetName, 'appname' => $_appName, 'widgetlink' => $_widgetLink, 'defaulticon' => $_defaultIconName, 'defaultsmallicon' => $_defaultSmallIconName, 'displayorder' => $_displayOrder, 'displayinnavbar' => (int)($_displayInNavBar), 'displayinindex' => (int)($_displayInIndex), 'ismaster' => (int)($_isMaster), 'isenabled' => (int)($_isEnabled), 'widgetvisibility' => (int)($_widgetVisibility), 'staffid' => $_staffID, 'uservisibilitycustom' => (int)($_userVisibilityCustom)), 'INSERT');
        $_widgetID = $_SWIFT->Database->Insert_ID();

        if (!$_widgetID) {
            throw new SWIFT_Widget_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == true) {
            foreach ($_userGroupIDList as $_key => $_val) {
                SWIFT_UserGroupAssign::Insert($_widgetID, SWIFT_UserGroupAssign::TYPE_WIDGET, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();

        self::RebuildCache();

        return $_widgetID;
    }

    /**
     * Update the Widget Record
     *
     * @author Varun Shoor
     * @param string $_defaultTitle The Default Title
     * @param string $_widgetLink The Widget Link
     * @param string $_defaultIconName The Default Icon File Name (in themes)
     * @param int $_displayOrder The Display Order
     * @param bool $_displayInNavBar Whether to display the icon in top nav bar
     * @param bool $_displayInIndex Whether to display the widget in Index
     * @param bool $_isEnabled Whether this widget is enabled
     * @param int $_widgetVisibility The Widget Visibility
     * @param int $_staffID The Staff ID Creating the widget
     * @param bool $_userVisibilityCustom Whether the widget is restricted to custom groups
     * @param array $_userGroupIDList The Custom User Group ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If the Class is not Loaded or If Invalid Data is Provided
     * @throws SWIFT_Exception
     */
    public function Update($_defaultTitle, $_widgetLink, $_defaultIconName, $_defaultSmallIconName, $_displayOrder, $_displayInNavBar, $_displayInIndex, $_isEnabled, $_widgetVisibility, $_staffID, $_userVisibilityCustom = false, $_userGroupIDList = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Widget_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_defaultTitle) || empty($_widgetLink) || !self::IsValidVisibility($_widgetVisibility)) {
            throw new SWIFT_Widget_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('defaulttitle', $_defaultTitle);
        $this->UpdatePool('widgetlink', $_widgetLink);
        $this->UpdatePool('defaulticon', $_defaultIconName);
        $this->UpdatePool('defaultsmallicon', $_defaultSmallIconName);
        $this->UpdatePool('widgetvisibility', (int)($_widgetVisibility));
        $this->UpdatePool('displayorder', $_displayOrder);
        $this->UpdatePool('displayinnavbar', (int)($_displayInNavBar));
        $this->UpdatePool('displayinindex', (int)($_displayInIndex));
        $this->UpdatePool('isenabled', (int)($_isEnabled));
        $this->UpdatePool('staffid', $_staffID);
        $this->UpdatePool('uservisibilitycustom', (int)($_userVisibilityCustom));

        $this->ProcessUpdatePool();

        SWIFT_UserGroupAssign::DeleteList(array($this->GetWidgetID()), SWIFT_UserGroupAssign::TYPE_WIDGET, false);
        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == true) {
            foreach ($_userGroupIDList as $_key => $_val) {
                SWIFT_UserGroupAssign::Insert($this->GetWidgetID(), SWIFT_UserGroupAssign::TYPE_WIDGET, $_val, false);
            }
        } else {
            SWIFT_UserGroupAssign::DeleteList(array($this->GetWidgetID()), SWIFT_UserGroupAssign::TYPE_WIDGET, false);
        }

        SWIFT_UserGroupAssign::RebuildCache();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Widget record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Widget_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetWidgetID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Widgets
     *
     * @author Varun Shoor
     * @param array $_widgetIDList The Widget ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_widgetIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_widgetIDList)) {
            return false;
        }

        $_finalWidgetIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "widgets WHERE widgetid IN (" . BuildIN($_widgetIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalWidgetIDList[] = $_SWIFT->Database->Record['widgetid'];
        }

        if (!count($_finalWidgetIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "widgets WHERE widgetid IN (" . BuildIN($_finalWidgetIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete widgets based on list of titles
     *
     * @author Varun Shoor
     * @param array $_widgetTitleList The Widget Title List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTitle($_widgetTitleList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_widgetTitleList)) {
            return false;
        }

        $_widgetIDList = array();
        $_SWIFT->Database->Query("SELECT widgetid FROM " . TABLE_PREFIX . "widgets WHERE defaulttitle IN (" . BuildIN($_widgetTitleList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_widgetIDList[] = $_SWIFT->Database->Record['widgetid'];
        }

        self::DeleteList($_widgetIDList);

        return true;
    }

    /**
     * Delete widgets based on app name
     *
     * @author Varun Shoor
     * @param array $_appNameList The App Name List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnApp($_appNameList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_appNameList)) {
            return false;
        }

        $_widgetIDList = array();
        $_SWIFT->Database->Query("SELECT widgetid FROM " . TABLE_PREFIX . "widgets WHERE appname IN (" . BuildIN($_appNameList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_widgetIDList[] = $_SWIFT->Database->Record['widgetid'];
        }

        self::DeleteList($_widgetIDList);

        return true;
    }

    /**
     * Check to see whether a widget is enabled and visible for a given app
     *
     * @author Varun Shoor
     * @param string $_appName The App Name
     * @param string $_widgetName The Widget Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsWidgetVisible($_appName, $_widgetName = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_isVisible = false;

        $_widgetCache = $_SWIFT->Cache->Get('widgetcache');
        foreach ($_widgetCache as $_widgetID => $_widgetContainer) {
            if ($_widgetContainer['appname'] == $_appName &&
                (empty($_widgetName) || (!empty($_widgetName) && $_widgetName == $_widgetContainer['widgetname']))
            ) {
                if (($_widgetContainer['displayinnavbar'] == '1' || $_widgetContainer['displayinindex'] == '1') && $_widgetContainer['isenabled'] == '1') {
                    $_isVisible = true;
                } else if ($_widgetContainer['isenabled'] == '1') {
                    $_isVisible = true;
                }

                if ($_isVisible) {
                    if ($_widgetContainer['uservisibilitycustom'] == '1') {
                        $_userGroupIDList = SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_WIDGET, $_widgetID);

                        // Not visible to active user group?
                        if (!in_array(SWIFT::Get('usergroupid'), $_userGroupIDList)) {
                            $_isVisible = false;
                        } else {
                            break;
                        }

                        /*
                         * BUG FIX - Varun Shoor
                         *
                         * SWIFT-628 Restricting Knowledgebase widget from Admin CP does not restrict the access to any articles
                         *
                         * Comments:
                         */
                    } else if (($_widgetContainer['widgetvisibility'] == self::VISIBLE_LOGGEDIN && !$_SWIFT->Session->IsLoggedIn()) || ($_widgetContainer['widgetvisibility'] == self::VISIBLE_GUESTS && $_SWIFT->Session->IsLoggedIn())) {
                        $_isVisible = false;
                    } else {
                        break;
                    }


                }
            }
        }

        return $_isVisible;
    }

    /**
     * Rebuild the Widget Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "widgets ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['widgetid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('widgetcache', $_cache);

        return true;
    }

    /**
     * Retrieve the Widget Label
     *
     * @author Varun Shoor
     * @param string $_defaultTitle The Default Widget Title
     * @return string
     */
    public static function GetLabel($_defaultTitle)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (mb_strtoupper(substr($_defaultTitle, 0, strlen(self::PHRASE_PREFIX))) == self::PHRASE_PREFIX) {
            $_finalLanguagePhrase = substr($_defaultTitle, strlen(self::PHRASE_PREFIX));

            // Do we have a phrase in language system?
            if ($_SWIFT->Language->Get($_finalLanguagePhrase)) {
                return $_SWIFT->Language->Get($_finalLanguagePhrase);
            } else {
                return $_finalLanguagePhrase;
            }
        }

        return $_defaultTitle;
    }

    /**
     * Retrieve the relevant icon
     *
     * @author Varun Shoor
     * @param string $_iconString The Icon String
     * @return string
     */
    public static function GetIcon($_iconString)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_VISITOR ||
            $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_RSS) {
            $_clientThemePath = SWIFT::Get('themepathimages');
        } else {
            $_clientThemePath = SWIFT::Get('clientthemepathimages');
        }

        if (trim($_iconString) == '') {
            return $_clientThemePath . 'icon_widget_default.png';
        }

        // If it has http or https in front we return it as it is, its probably a direct path..
        if (mb_strtolower(mb_substr($_iconString, 0, strlen('http://'))) == 'http://' ||
            mb_strtolower(mb_substr($_iconString, 0, strlen('https://'))) == 'https://') {
            // We need to do some URL changes if icon is in http and URL is in https
            if (mb_strtolower(mb_substr($_iconString, 0, strlen('http://'))) == 'http://' && mb_strtolower(mb_substr(SWIFT::Get('swiftpath'), 0, strlen('https://'))) == 'https://') {
                return str_replace('http://', 'https://', $_iconString);
            }

            return $_iconString;
        }

        // If it has a themepath.. we replace it with the client theme path
        if (mb_strpos($_iconString, '{$themepath}') !== FALSE) {
            return str_replace('{$themepath}', $_clientThemePath, $_iconString);

            // No themepath? Prefix in front..
        } else {
            return $_clientThemePath . $_iconString;
        }
    }

    /**
     * Retrieve the relevant path
     *
     * @author Varun Shoor
     * @param string $_linkString The Link String
     * @return string
     */
    public static function GetPath($_linkString)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_clientBaseName = SWIFT::Get('basename');

        if (trim($_linkString) == '') {
            return $_clientBaseName;
        }

        // If it has http or https in front we return it as it is, its probably a direct path..
        if (mb_strtolower(mb_substr($_linkString, 0, strlen('http://'))) == 'http://' || mb_strtolower(mb_substr($_linkString, 0, strlen('https://'))) == 'https://') {
            return $_linkString;
        }

        return $_clientBaseName . $_SWIFT->Template->GetTemplateGroupPrefix() . $_linkString;
    }

    /**
     * Retrieve the Last Possible Display Order for a Widget
     *
     * @author Varun Shoor
     * @return int|string The Last Possible Display Order
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('widgetcache');

        $_widgetCache = $_SWIFT->Cache->Get('widgetcache');

        if (!$_widgetCache) {
            return '1';
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_widgetCache));

        $_displayOrder = (int)($_widgetCache[$_lastInsertID]['displayorder'] + 1);

        return $_displayOrder;
    }

    /**
     * Retrieve a list of valid widgets for the currently loaded user group...
     *
     * @author Varun Shoor
     * @return mixed "_finalWidgetContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Widget_Exception If Invalid Data is Provided
     */
    public static function GetWidgetListForUser()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_widgetCache = $_SWIFT->Cache->Get('widgetcache');
        if (!$_widgetCache) {
            throw new SWIFT_Widget_Exception(SWIFT_INVALIDDATA);
        }

        $_finalWidgetContainer = array();

        foreach ($_widgetCache as $_key => $_val) {
            if (!SWIFT_App::IsInstalled($_val['appname'])) {
                continue;
            }

            // Is widget disabled completely?
            if ($_val['isenabled'] == '0') {
                continue;

                // Have we restricted this widget to custom set of user groups and this item isnt linked to active user group?
            } else if ($_val['uservisibilitycustom'] == '1' && !SWIFT_UserGroupAssign::IsItemLinkedToUserGroup(SWIFT_UserGroupAssign::TYPE_WIDGET, $_val['widgetid'], SWIFT::Get('usergroupid'))) {
                continue;

                // Now check for widget visibility
            } else if (($_val['widgetvisibility'] == self::VISIBLE_LOGGEDIN && !$_SWIFT->Session->IsLoggedIn()) || ($_val['widgetvisibility'] == self::VISIBLE_GUESTS && $_SWIFT->Session->IsLoggedIn())) {
                continue;
            }

            // By now we have passed all checks.. we add this widget to the final list..
            $_val['defaulttitle'] = htmlspecialchars(self::GetLabel($_val['defaulttitle']));
            $_val['widgetlink'] = htmlspecialchars(self::GetPath($_val['widgetlink']));
            $_val['defaulticon'] = htmlspecialchars(self::GetIcon($_val['defaulticon']));
            $_val['defaultsmallicon'] = htmlspecialchars(self::GetIcon($_val['defaultsmallicon']));

            if ($_SWIFT->UserInterface->GetWidget() == $_val['widgetname'] && !empty($_val['widgetname'])) {
                $_val['isactive'] = true;
            } else {
                $_val['isactive'] = false;
            }

            $_finalWidgetContainer[] = $_val;
        }

        return $_finalWidgetContainer;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_widgetIDSortList The Widget ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_widgetIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_widgetIDSortList)) {
            return false;
        }

        foreach ($_widgetIDSortList as $_widgetID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'widgets', array('displayorder' => $_displayOrder), 'UPDATE',
                "widgetid = '" . $_widgetID . "'");
        }

        self::RebuildCache();

        return true;
    }
}

?>
