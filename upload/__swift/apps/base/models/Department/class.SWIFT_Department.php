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

namespace Base\Models\Department;

use Base\Models\CustomField\SWIFT_CustomFieldGroupDepartmentLink;
use SWIFT;
use SWIFT_App;
use Base\Models\Department\SWIFT_Department_Exception;
use SWIFT_Exception;
use SWIFT_Loader;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_StaffAssign;
use Base\Models\Staff\SWIFT_StaffGroupAssign;
use Base\Models\Staff\SWIFT_StaffSettings;
use Base\Models\User\SWIFT_UserGroupAssign;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\Escalation\SWIFT_EscalationPath;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Department Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Department extends SWIFT_Model
{
    const TABLE_NAME = 'departments';
    const PRIMARY_KEY = 'departmentid';

    const TABLE_STRUCTURE = "departmentid I PRIMARY AUTO NOTNULL,
                                title C(100) DEFAULT '' NOTNULL,
                                departmenttype C(50) DEFAULT 'public' NOTNULL,
                                departmentapp C(50) DEFAULT 'tickets' NOTNULL,
                                isdefault I2 DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                parentdepartmentid I DEFAULT '0' NOTNULL,
                                uservisibilitycustom I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'departmentapp';
    const INDEX_2 = 'departmenttype';
    const INDEX_3 = 'parentdepartmentid, departmentapp, departmentid, departmenttype';

    const COLUMN_RENAME_DEPARTMENTMODULE = 'departmentapp';

    protected $_dataStore = array();

    // Core Constants
    const DEPARTMENT_PUBLIC = 'public';
    const DEPARTMENT_PRIVATE = 'private';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Department_Exception If the Class is not Loaded
     */
    public function __construct($_departmentID)
    {
        parent::__construct();

        if (!$this->LoadData($_departmentID)) {
            throw new SWIFT_Department_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Department_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'departments', $this->GetUpdatePool(), 'UPDATE', "departmentid = '" .
            (int)($this->GetDepartmentID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Department ID
     *
     * @author Varun Shoor
     * @return mixed "departmentid" on Success, "false" otherwise
     * @throws SWIFT_Department_Exception If the Class is not Loaded
     */
    public function GetDepartmentID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Department_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['departmentid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_departmentID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentid = '" .
            $_departmentID . "'");
        if (isset($_dataStore['departmentid']) && !empty($_dataStore['departmentid'])) {
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
     * @throws SWIFT_Department_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Department_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Department_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Department_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Department_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Checks to see if the Department App is Valid
     *
     * @author Varun Shoor
     * @param string $_departmentApp The Department App
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidDepartmentApp($_departmentApp)
    {
        if ($_departmentApp == APP_TICKETS || $_departmentApp == APP_LIVECHAT) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if it is a valid department type
     *
     * @author Varun Shoor
     * @param string $_departmentType The Department Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidDepartmentType($_departmentType)
    {
        if ($_departmentType == self::DEPARTMENT_PUBLIC || $_departmentType == self::DEPARTMENT_PRIVATE) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if the department is a valid parent department
     *
     * @author Varun Shoor
     * @param int $_departmentID The Parent Department ID
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidParentDepartment($_departmentID)
    {
        if (empty($_departmentID)) {
            return true;
        }

        $_SWIFT_DepartmentObject = new SWIFT_Department($_departmentID);
        if ($_SWIFT_DepartmentObject->GetProperty('parentdepartmentid') == '0') {
            return true;
        }

        return false;
    }

    /**
     * Insert a new department
     *
     * @author Varun Shoor
     * @param string $_title The Department Title
     * @param string $_departmentApp The Department App Type
     * @param string $_departmentType The Department Type (public/private)
     * @param int $_displayOrder The Display Order
     * @param int $_parentDepartmentID The Parent Department ID
     * @param bool $_userVisibilityCustom Whether User Visibility is set to custom list
     * @param array $_userGroupIDList The User Group ID List (if $_userVisibilityCustom is set to true)
     * @return mixed SWIFT_Department Object on Success, "false" otherwise
     * @throws SWIFT_Department_Exception If the Creation fails or if Invalid Data is Provided
     */
    public static function Insert($_title, $_departmentApp, $_departmentType, $_displayOrder, $_parentDepartmentID, $_userVisibilityCustom,
                                  $_userGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || !self::IsValidDepartmentApp($_departmentApp) || !self::IsValidDepartmentType($_departmentType) ||
            !self::IsValidParentDepartment($_parentDepartmentID)) {
            throw new SWIFT_Department_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUNBTX/KAYAKOC-3361 - Navigating to 'Insert Team' page in Admin Panel throws error
         *
         * Remove html tags from title to prevent XSS attacks
         *
         * @author Werner Garcia <werner.garcia@crossover.com>
         */
        $_title = trim(html_entity_decode(removeTags($_title)));

        // Insert the department record
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'departments', array('title' => $_title, 'departmenttype' => $_departmentType,
            'departmentapp' => $_departmentApp, 'isdefault' => 0, 'displayorder' => $_displayOrder,
            'parentdepartmentid' => $_parentDepartmentID, 'uservisibilitycustom' => (int)($_userVisibilityCustom)), 'INSERT');
        $_departmentID = $_SWIFT->Database->Insert_ID();
        if (!$_departmentID) {
            throw new SWIFT_Department_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == true) {
            foreach ($_userGroupIDList as $_key => $_val) {
                SWIFT_UserGroupAssign::Insert($_departmentID, SWIFT_UserGroupAssign::TYPE_DEPARTMENT, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();
        self::RebuildCache();

        return new SWIFT_Department($_departmentID);
    }

    /**
     * Update the Department Record
     *
     * @author Varun Shoor
     * @param string $_title The Department Title
     * @param string $_departmentApp The Department App Type
     * @param string $_departmentType The Department Type (public/private)
     * @param int $_displayOrder The Display Order
     * @param int $_parentDepartmentID The Parent Department ID
     * @param bool $_userVisibilityCustom Whether User Visibility is set to custom list
     * @param array $_userGroupIDList The User Group ID List (if $_userVisibilityCustom is set to true)
     * @return bool "true" on Success, "false" otherwise
     */
    public function Update($_title, $_departmentApp, $_departmentType, $_displayOrder, $_parentDepartmentID, $_userVisibilityCustom,
                           $_userGroupIDList)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!self::IsValidDepartmentApp($_departmentApp) || !self::IsValidDepartmentType($_departmentType) ||
            !self::IsValidParentDepartment($_parentDepartmentID)) {
            return false;
        }

        if ($_parentDepartmentID) {
            self::ResetParentDepartment(array($this->GetDepartmentID()), false);
        }

        /**
         * BUNBTX/KAYAKOC-3361 - Navigating to 'Insert Team' page in Admin Panel throws error
         *
         * Remove html tags from title to prevent XSS attacks
         *
         * @author Werner Garcia <werner.garcia@crossover.com>
         */
        $_title = trim(removeTags($_title));

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('departmenttype', $_departmentType);
        $this->UpdatePool('departmentapp', $_departmentApp);
        $this->UpdatePool('isdefault', '0');
        $this->UpdatePool('displayorder', $_displayOrder);
        $this->UpdatePool('parentdepartmentid', $_parentDepartmentID);
        $this->UpdatePool('uservisibilitycustom', (int)($_userVisibilityCustom));
        $this->ProcessUpdatePool();

        SWIFT_UserGroupAssign::DeleteList(array($this->GetDepartmentID()), SWIFT_UserGroupAssign::TYPE_DEPARTMENT, false);
        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == true) {
            foreach ($_userGroupIDList as $_key => $_val) {
                SWIFT_UserGroupAssign::Insert($this->GetDepartmentID(), SWIFT_UserGroupAssign::TYPE_DEPARTMENT, $_val, false);
            }
        } else {
            SWIFT_UserGroupAssign::DeleteList(array($this->GetDepartmentID()), SWIFT_UserGroupAssign::TYPE_DEPARTMENT, false);
        }

        SWIFT_UserGroupAssign::RebuildCache();
        self::RebuildCache();

        // Update other properties
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            SWIFT_Ticket::UpdateGlobalProperty('departmenttitle', $_title, 'departmentid', $this->GetDepartmentID());

            SWIFT_Loader::LoadModel('AuditLog:TicketAuditLog', APP_TICKETS);
            SWIFT_TicketAuditLog::UpdateGlobalProperty('departmenttitle', $_title, 'departmentid', $this->GetDepartmentID());

            SWIFT_Loader::LoadModel('Escalation:EscalationPath', APP_TICKETS);
            SWIFT_EscalationPath::UpdateGlobalProperty('departmenttitle', $_title, 'departmentid', $this->GetDepartmentID());
        }

        return true;
    }

    /**
     * Retrieves the Parent Department Object
     *
     * @author Varun Shoor
     * @return mixed "SWIFT_Department" object on Success, "false" otherwise
     * @throws SWIFT_Department_Exception If the Class is not Loaded
     */
    public function GetParentDepartmentObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Department_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$this->GetProperty('parentdepartmentid')) {
            return false;
        }

        return new SWIFT_Department($this->GetProperty('parentdepartmentid'));
    }

    /**
     * Deletes the list of departments
     *
     * @author Varun Shoor
     * @param array $_departmentIDList The Department ID List Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_departmentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_departmentIDList)) {
            return false;
        }

        $_finalDepartmentIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentid IN (" . BuildIN($_departmentIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_appTitle = 'app_' . $_SWIFT->Database->Record['departmentapp'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . ' (' .
                IIF($_SWIFT->Database->Record["departmentapp"] == APP_TICKETS, '<img src="' . SWIFT::Get('themepath') . 'images/icon_tickets.png" align="absmiddle" border="0" />',
                    '<img src="' . SWIFT::Get('themepath') . 'images/icon_livesupport.gif" align="absmiddle" border="0" />') .
                '&nbsp;' . $_SWIFT->Language->Get($_appTitle) . ')<BR />';

            $_finalDepartmentIDList[] = $_SWIFT->Database->Record['departmentid'];
            $_index++;
        }

        if (!count($_finalDepartmentIDList)) {
            return false;
        }

        // Delete the departments
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "departments WHERE departmentid IN (" . BuildIN($_finalDepartmentIDList) . ")");

        // Delete all assignments for the current departments

        SWIFT_CustomFieldGroupDepartmentLink::DeleteOnDepartment($_departmentIDList);

        SWIFT_StaffAssign::DeleteOnDepartmentList($_finalDepartmentIDList);
        SWIFT_StaffGroupAssign::DeleteOnDepartmentList($_finalDepartmentIDList);
        SWIFT_StaffSettings::DeleteOnDepartmentList($_finalDepartmentIDList);

        SWIFT_UserGroupAssign::DeleteList($_finalDepartmentIDList, SWIFT_UserGroupAssign::TYPE_DEPARTMENT);

        if (!self::ResetParentDepartment($_finalDepartmentIDList, false)) {
            return false;
        }

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);

            SWIFT_Ticket::MoveToTrashBulk($_finalDepartmentIDList);

            SWIFT_Loader::LoadLibrary('Ticket:TicketManager', APP_TICKETS);
            SWIFT_TicketManager::RebuildCache();
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('deldepartmentstitle'), count($_finalDepartmentIDList)),
            $_SWIFT->Language->Get('deldepartmentsmsg') . $_finalText);

        self::RebuildCache();

        return true;
    }

    /**
     * Resets the Parent Departments for the given department id list
     *
     * @author Varun Shoor
     * @param array $_departmentIDList The Department ID List Container
     * @param bool $_rebuildCache Whether the rebuild cache should be done automatically
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ResetParentDepartment($_departmentIDList, $_rebuildCache = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_departmentIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'departments', array('parentdepartmentid' => '0'), 'UPDATE', "parentdepartmentid IN (" .
            BuildIN($_departmentIDList) . ")");

        if ($_rebuildCache) {
            self::RebuildCache();
        }

        return true;
    }

    /**
     * Delete the department
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Department_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Department_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::DeleteList(array($this->GetDepartmentID()))) {
            return false;
        }

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Retrieves the Department Map. Basically an array filtered on the basis of apps, type, department id and with sub department relationship
     * intact.
     *
     * @author Varun Shoor
     * @param string $_departmentApp The Filter Department App (Optional)
     * @param string $_departmentType The Filter Department Type SWIFT_PUBLIC/SWIFT_PRIVATE (Optional)
     * @param int $_filterDepartmentID The Filter Department ID (Optional)
     * @param int $_filterUserGroupID Filter the department results according to the specified user group id (OPTIONAL)
     * @return array
     */
    public static function GetDepartmentMap($_departmentApp = null, $_departmentType = null, $_filterDepartmentID = null,
                                            $_filterUserGroupID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        $_departments = $_parentDepartmentIDList = array();

        $_sqlExtend = '';

        if (!empty($_departmentType)) {
            if ($_departmentType == SWIFT_PUBLIC) {
                $_sqlExtend = " AND departmenttype = '" . SWIFT_PUBLIC . "'";
            } else if ($_departmentType == SWIFT_PRIVATE) {
                $_sqlExtend = " AND departmenttype = '" . SWIFT_PRIVATE . "'";
            }
        }

        $_departmentIDList = array();
        foreach ($_departmentCache as $_departmentID => $_departmentContainer) {
            if (!empty($_departmentApp) && $_departmentContainer['departmentapp'] != $_departmentApp) {
                continue;
            } else if (!empty($_filterDepartmentID) && $_filterDepartmentID != $_departmentID) {
                continue;
            } else if (!empty($_departmentType) && $_departmentContainer['departmenttype'] != $_departmentType) {
                continue;
            } else if ($_departmentContainer['parentdepartmentid'] != '0') {
                continue;
            }

            $_departments[$_departmentID] = $_departmentContainer;
            $_parentDepartmentIDList[] = $_departmentID;
            $_departments[$_departmentID]['subdepartments'] = array();
            $_departments[$_departmentID]['subdepartmentids'] = array();

            $_departmentIDList[] = $_departmentID;
        }

        // We need to loop again for sub departments
        foreach ($_departmentCache as $_departmentID => $_departmentContainer) {
            if (!empty($_departmentApp) && $_departmentContainer['departmentapp'] != $_departmentApp) {
                continue;
            } else if (!empty($_departmentType) && $_departmentContainer['departmenttype'] != $_departmentType) {
                continue;
            } else if ($_departmentContainer['parentdepartmentid'] == '0' || !isset($_departments[$_departmentContainer['parentdepartmentid']])) {
                continue;
            }

            $_departments[$_departmentContainer['parentdepartmentid']]['subdepartments'][$_departmentID] = $_departmentContainer;
            $_departments[$_departmentContainer['parentdepartmentid']]['subdepartmentids'][] = $_departmentID;

            $_departmentIDList[] = $_departmentID;
        }

        // Do we need to filter our results according to user group ids?
        $_finalDepartmentContainer = array();
        if (!empty($_filterUserGroupID)) {

            $_userGroupIDMap = SWIFT_UserGroupAssign::RetrieveMap(SWIFT_UserGroupAssign::TYPE_DEPARTMENT, $_departmentIDList);

            foreach ($_departments as $_departmentID => $_departmentContainer) {
                $_addDepartmentContainer = false;

                // Global department, add to final department container or if department is linked to some user groups.. only add if the user group
                // id matches
                if (($_departmentContainer['uservisibilitycustom'] == '0') || ($_departmentContainer['uservisibilitycustom'] == '1' &&
                        isset($_userGroupIDMap[$_departmentID]) && in_array($_filterUserGroupID, $_userGroupIDMap[$_departmentID]))) {

                    $_addDepartmentContainer = $_departmentContainer;

                    // Unset the sub department data till we verify it
                    $_addDepartmentContainer['subdepartments'] = array();
                    $_addDepartmentContainer['subdepartmentids'] = array();

                }

                // Proceed to parse out sub departments only if parent department passed the test
                if (_is_array($_addDepartmentContainer)) {
                    foreach ($_departmentContainer['subdepartments'] as $_subDepartmentID => $_subDepartmentContainer) {
                        if (($_subDepartmentContainer['uservisibilitycustom'] == '0') ||
                            ($_subDepartmentContainer['uservisibilitycustom'] == '1' && isset($_userGroupIDMap[$_subDepartmentID]) &&
                                in_array($_filterUserGroupID, $_userGroupIDMap[$_subDepartmentID]))) {

                            $_addDepartmentContainer['subdepartments'][$_subDepartmentID] = $_subDepartmentContainer;
                            $_addDepartmentContainer['subdepartmentids'][] = $_subDepartmentID;
                        }
                    }

                    // Add to final department if everything went ok
                    $_finalDepartmentContainer[$_departmentID] = $_addDepartmentContainer;
                }
            }
        } else {
            $_finalDepartmentContainer = $_departments;
        }

        return $_finalDepartmentContainer;
    }

    /**
     * Retrieve the valid department's that are for the specified user group
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @param string $_departmentType (OPTIONAL) The department type
     * @param string $_departmentApp (OPTIONAL) The Department App
     * @param int $_filterDepartmentID (OPTIONAL) The Department ID to limit the results to
     * @return array The Department ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetDepartmentIDListOnUserGroup($_userGroupID, $_departmentType = null, $_departmentApp = null,
                                                          $_filterDepartmentID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sqlExtend = '';
        if (!empty($_departmentType)) {
            if ($_departmentType == SWIFT_PUBLIC) {
                $_sqlExtend = " AND departmenttype = '" . SWIFT_PUBLIC . "'";
            } else if ($_departmentType == SWIFT_PRIVATE) {
                $_sqlExtend = " AND departmenttype = '" . SWIFT_PRIVATE . "'";
            }
        }

        $_departmentIDList = $_processParentDepartmentIDList = $_processDepartmentContainer = $_parentDepartmentIDList = array();
        $_processDepartmentIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE parentdepartmentid = '0' " .
            IIF(!empty($_departmentApp), " AND departmentapp = '" . $_SWIFT->Database->Escape(Clean($_departmentApp)) . "'") .
            IIF(!empty($_filterDepartmentID), " AND departmentid = '" . $_filterDepartmentID . "'") . $_sqlExtend . ' ORDER BY title ASC');
        while ($_SWIFT->Database->NextRecord()) {
            $_processDepartmentContainer[$_SWIFT->Database->Record['departmentid']] = $_SWIFT->Database->Record;
            $_processParentDepartmentIDList[] = (int)($_SWIFT->Database->Record['departmentid']);

            $_processDepartmentIDList[] = (int)($_SWIFT->Database->Record['departmentid']);
        }

        // Now get the sub departments with same filter properties
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE parentdepartmentid IN (" .
            BuildIN($_processParentDepartmentIDList) . ") " . IIF(!empty($_departmentApp), " AND departmentapp = '" .
                $_SWIFT->Database->Escape(Clean($_departmentApp)) . "'") . $_sqlExtend . ' ORDER BY title ASC');
        while ($_SWIFT->Database->NextRecord()) {
            $_processDepartmentContainer[$_SWIFT->Database->Record['departmentid']] = $_SWIFT->Database->Record;
            $_processParentDepartmentIDList[] = (int)($_SWIFT->Database->Record['departmentid']);
            $_processDepartmentIDList[] = (int)($_SWIFT->Database->Record['departmentid']);
        }

        // Get the user group map
        $_userGroupIDMap = SWIFT_UserGroupAssign::RetrieveMap(SWIFT_UserGroupAssign::TYPE_DEPARTMENT, $_processDepartmentIDList);

        // Now itterate through the results and build the final department id list
        foreach ($_processDepartmentContainer as $_departmentID => $_departmentContainer) {
            if ($_departmentContainer['uservisibilitycustom'] == '0' || ($_departmentContainer['uservisibilitycustom'] == '1' &&
                    isset($_userGroupIDMap[$_departmentID]) && in_array($_userGroupID, $_userGroupIDMap[$_departmentID]))) {
                $_departmentIDList[] = $_departmentID;
            }
        }

        return $_departmentIDList;
    }

    /**
     * Retrieves the Department Map. Basically an array of Options filtered on the basis of apps, type, department id and with sub department
     * relationship intact.
     *
     * @author Varun Shoor
     * @param int $_selectedDepartmentID The Selected Department ID
     * @param string $_departmentApp The Filter Department App (Optional)
     * @param string $_departmentType The Filter Department Type SWIFT_PUBLIC/SWIFT_PRIVATE (Optional)
     * @param int $_departmentID The Filter Department ID (Optional)
     * @return array
     */
    public static function GetDepartmentMapOptions($_selectedDepartmentID = 0, $_departmentApp = null, $_departmentType = null,
                                                   $_departmentID = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentMap = self::GetDepartmentMap($_departmentApp, $_departmentType, $_departmentID);

        $_optionsContainer = array();

        if (!_is_array($_departmentMap)) {
            return $_optionsContainer;
        }

        $_index = 0;
        foreach ($_departmentMap as $_key => $_val) {
            $_optionsContainer[$_index]['title'] = $_val['title'];
            $_optionsContainer[$_index]['value'] = (int)($_val['departmentid']);

            if ($_val['departmentid'] == $_selectedDepartmentID) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;

            if (_is_array($_val['subdepartments'])) {
                foreach ($_val['subdepartments'] as $_subKey => $_subVal) {
                    $_optionsContainer[$_index]['title'] = '|- ' . $_subVal['title'];
                    $_optionsContainer[$_index]['value'] = (int)($_subVal['departmentid']);

                    if ($_subVal['departmentid'] == $_selectedDepartmentID) {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }
        }

        return $_optionsContainer;
    }

    /**
     * Retrieve the Default Department ID based on a given app
     *
     * @author Varun Shoor
     * @param mixed $_departmentApp the Department App
     * @param string $_departmentType The Filter Department Type SWIFT_PUBLIC/SWIFT_PRIVATE (Optional)
     * @return mixed "departmentid" (INT) on Success, "false" otherwise
     */
    public static function RetrieveDefaultDepartmentID($_departmentApp = null, $_departmentType = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentMap = self::GetDepartmentMap($_departmentApp, $_departmentType);

        if (!_is_array($_departmentMap)) {
            return false;
        }

        foreach ($_departmentMap as $_key => $_val) {
            return $_val['departmentid'];
        }

        return false;
    }


    /**
     * Rebuilds the Department Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments ORDER BY displayorder ASC");
        $_index = 0;
        while ($_SWIFT->Database->NextRecord()) {
            /**
             * BUNBTX/KAYAKOC-3361 - Navigating to 'Insert Team' page in Admin Panel throws error
             *
             * Remove html tags from title to prevent XSS attacks
             *
             * @author Werner Garcia <werner.garcia@crossover.com>
             */
            $_SWIFT->Database->Record['title'] = trim(removeTags($_SWIFT->Database->Record['title']));

            $_cache[$_SWIFT->Database->Record['departmentid']] = $_SWIFT->Database->Record;

            if ($_SWIFT->Database->Record['departmentapp'] == APP_TICKETS) {
                $_index++;
                $_cache[$_SWIFT->Database->Record['departmentid']]['index'] = $_index;
            }
        }

        $_SWIFT->Cache->Update('departmentcache', $_cache);

        return true;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_departmentIDSortList The Department ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_departmentIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_departmentIDSortList)) {
            return false;
        }

        foreach ($_departmentIDSortList as $_departmentID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'departments', array('displayorder' => $_displayOrder), 'UPDATE',
                "departmentid = '" . $_departmentID . "'");
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve all the staff id's assigned to a given department
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return array The Staff ID List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAssignedStaffIDList($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_departmentID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_groupAssignCache = $_SWIFT->Cache->Get('groupassigncache');
        $_staffAssignCache = $_SWIFT->Cache->Get('staffassigncache');
        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_staffGroupCache = $_SWIFT->Cache->Get('staffgroupcache');

        $_staffGroupAssignMap = $_staffIDList = array();

        foreach ($_groupAssignCache as $_staffGroupID => $_departmentIDList) {
            if (in_array($_departmentID, $_departmentIDList)) {
                $_staffGroupAssignMap[] = $_staffGroupID;
            }
        }

        foreach ($_staffCache as $_staffID => $_staffContainer) {
            // If this staff uses the staff group departments and that staff group is assigned to our department.. then add to list
            if ($_staffContainer['groupassigns'] == '1' && in_array($_staffContainer['staffgroupid'], $_staffGroupAssignMap)) {
                $_staffIDList[] = $_staffID;

                // Otherwise if this staff uses self assigned departments and is assigned to given department then add to list!
            } else if ($_staffContainer['groupassigns'] == '0' && isset($_staffAssignCache[$_staffID]) && in_array($_departmentID, $_staffAssignCache[$_staffID])) {
                $_staffIDList[] = $_staffID;
            }
        }

        return $_staffIDList;
    }

    /**
     * Delete the departments based on a list of apps
     *
     * @author Varun Shoor
     * @param array $_appList The App List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnApp($_appList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_appList)) {
            return false;
        }

        $_departmentIDList = array();
        $_SWIFT->Database->Query("SELECT departmentid FROM " . TABLE_PREFIX . "departments WHERE departmentapp IN (" . BuildIN($_appList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_departmentIDList[] = $_SWIFT->Database->Record['departmentid'];
        }

        if (!_is_array($_departmentIDList)) {
            return false;
        }

        self::DeleteList($_departmentIDList);

        return true;
    }
}

?>
