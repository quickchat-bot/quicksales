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

namespace Base\API;

use Base\Models\Department\SWIFT_Department;
use Base\Models\User\SWIFT_UserGroupAssign;
use Controller_api;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;

/**
 * The Department API Controller
 *
 * @author Varun Shoor
 */
class Controller_Department extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Retrieve & Dispatch the Departments
     *
     * @author Varun Shoor
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessDepartments($_departmentID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentContainer = $_departmentIDList = array();

        if (!empty($_departmentID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentid = '" . ($_departmentID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments ORDER BY departmentid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_departmentContainer[$this->Database->Record['departmentid']] = $this->Database->Record;

            $_departmentIDList[] = $this->Database->Record['departmentid'];
        }

        $_departmentUserGroupMapList = SWIFT_UserGroupAssign::RetrieveMap(SWIFT_UserGroupAssign::TYPE_DEPARTMENT, $_departmentIDList);

        $this->XML->AddParentTag('departments');
        foreach ($_departmentContainer as $_departmentID => $_department) {
            $_departmentType = 'private';
            if ($_department['departmenttype'] == SWIFT_Department::DEPARTMENT_PUBLIC) {
                $_departmentType = 'public';
            }

            $this->XML->AddParentTag('department');
            $this->XML->AddTag('id', $_departmentID);
            $this->XML->AddTag('title', $_department['title']);
            $this->XML->AddTag('type', $_departmentType);
            $this->XML->AddTag('module', $_department['departmentapp']);
            $this->XML->AddTag('app', $_department['departmentapp']);
            $this->XML->AddTag('displayorder', $_department['displayorder']);
            $this->XML->AddTag('parentdepartmentid', $_department['parentdepartmentid']);
            $this->XML->AddTag('uservisibilitycustom', $_department['uservisibilitycustom']);

            $this->XML->AddParentTag('usergroups');
            if (isset($_departmentUserGroupMapList[$_departmentID]) && _is_array($_departmentUserGroupMapList[$_departmentID])) {
                foreach ($_departmentUserGroupMapList[$_departmentID] as $_userGroupID) {
                    $this->XML->AddTag('id', $_userGroupID);
                }
            }
            $this->XML->EndParentTag('usergroups');
            $this->XML->EndParentTag('department');
        }
        $this->XML->EndParentTag('departments');

        return true;
    }

    /**
     * Get a list of Departments
     *
     * Example Output:
     *    <departments>
     *        <department>
     *            <id>1</id>
     *            <title>Support</title>
     *            <type>private</type>
     *            <app>tickets</app>
     *            <displayorder>1</displayorder>
     *            <parentdepartmentid>0</parentdepartmentid>
     *            <uservisibilitycustom>1</uservisibilitycustom>
     *            <usergroups>
     *                <id>1</id>
     *                <id>2</id>
     *                <id>3</id>
     *            </usergroups>
     *        </department>
     *        <department>
     *            <id>2</id>
     *            <title>Sales</title>
     *            <type>public</type>
     *            <app>livechat</app>
     *            <displayorder>2</displayorder>
     *            <parentdepartmentid>0</parentdepartmentid>
     *            <uservisibilitycustom>0</uservisibilitycustom>
     *        </department>
     *        <department>
     *            <id>3</id>
     *            <title>QuickSupport Fusion</title>
     *            <type>public</type>
     *            <app>livechat</app>
     *            <displayorder>3</displayorder>
     *            <parentdepartmentid>2</parentdepartmentid>
     *            <uservisibilitycustom>0</uservisibilitycustom>
     *        </department>
     *    </departments>
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessDepartments();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Department
     *
     * Example Output:
     *    <departments>
     *        <department>
     *            <id>1</id>
     *            <title>Support</title>
     *            <type>private</type>
     *            <app>tickets</app>
     *            <displayorder>1</displayorder>
     *            <parentdepartmentid>0</parentdepartmentid>
     *            <uservisibilitycustom>1</uservisibilitycustom>
     *            <usergroups>
     *                <id>1</id>
     *                <id>2</id>
     *                <id>3</id>
     *            </usergroups>
     *        </department>
     *    </departments>
     *
     * @author Varun Shoor
     * @param string $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessDepartments((int)($_departmentID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a Department
     *
     * Required Fields:
     * title
     * app: tickets/livechat
     * type: public/private
     *
     * Optional Fields:
     * displayorder
     * parentdepartmentid
     * uservisibilitycustom
     * usergroupid[] (ARRAY)
     *
     * Example Output:
     *    <departments>
     *        <department>
     *            <id>1</id>
     *            <title>Support</title>
     *            <type>private</type>
     *            <app>tickets</app>
     *            <displayorder>1</displayorder>
     *            <parentdepartmentid>0</parentdepartmentid>
     *            <uservisibilitycustom>1</uservisibilitycustom>
     *            <usergroups>
     *                <id>1</id>
     *                <id>2</id>
     *                <id>3</id>
     *            </usergroups>
     *        </department>
     *    </departments>
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Check for App
        $_departmentApp = false;
        if (isset($_POST['module'])) {
            $_departmentApp = $_POST['module'];
        } else if (isset($_POST['app'])) {
            $_departmentApp = $_POST['app'];
        }

        if (empty($_departmentApp) || ($_departmentApp != 'tickets' && $_departmentApp != 'livechat')) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid App Specified. You can only create departments for tickets and livechat app.');

            return false;
        }

        // Check for Type
        if (!isset($_POST['type']) || ($_POST['type'] != 'public' && $_POST['type'] != 'private')) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Department Type Specified. Valid values are "public" and "private".');

            return false;
        }

        // Check for Parent Department ID
        $_parentDepartmentID = 0;
        if (isset($_POST['parentdepartmentid']) && !empty($_POST['parentdepartmentid'])) {
            $_SWIFT_DepartmentObject_Parent = false;

            try {
                $_SWIFT_DepartmentObject_Parent = new SWIFT_Department($_POST['parentdepartmentid']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Parent Department');

                return false;
            }

            if (!$_SWIFT_DepartmentObject_Parent instanceof SWIFT_Department || !$_SWIFT_DepartmentObject_Parent->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Parent Department: 2');

                return false;
            }
            // @codeCoverageIgnoreEnd

            if ($_SWIFT_DepartmentObject_Parent->GetProperty('departmentapp') != $_departmentApp) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Parent Department App. The app type of both parent and child department should match.');

                return false;
            }

            $_parentDepartmentID = $_SWIFT_DepartmentObject_Parent->GetDepartmentID();
        }

        if (isset($_POST['title']) && trim($_POST['title']) != '' && !empty($_POST['title'])) {
            $_departmentType = SWIFT_Department::DEPARTMENT_PRIVATE;
            if ($_POST['type'] == 'public') {
                $_departmentType = SWIFT_Department::DEPARTMENT_PUBLIC;
            }

            $_displayOrder = 1;
            if (isset($_POST['displayorder'])) {
                $_displayOrder = (int)($_POST['displayorder']);
            }

            $_userVisibilityCustom = false;
            $_userGroupIDList = array();
            if (isset($_POST['uservisibilitycustom']) && $_POST['uservisibilitycustom'] == '1') {
                $_userVisibilityCustom = 1;

                if (isset($_POST['usergroupid']) && _is_array($_POST['usergroupid'])) {
                    $_userGroupIDList = $_POST['usergroupid'];
                }
            }

            $_SWIFT_DepartmentObject = SWIFT_Department::Insert($_POST['title'], $_departmentApp, $_departmentType, $_displayOrder, $_parentDepartmentID, $_userVisibilityCustom, $_userGroupIDList);
            if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Department Creation Failed');

                return false;
            }
            // @codeCoverageIgnoreEnd

            $this->ProcessDepartments($_SWIFT_DepartmentObject->GetDepartmentID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return false;
    }

    /**
     * Update a Department
     *
     * Required Fields:
     * title
     *
     * Optional Fields:
     * type: public/private
     * displayorder
     * parentdepartmentid
     * uservisibilitycustom
     * usergroupid[] (ARRAY)
     *
     * Example Output:
     *    <departments>
     *        <department>
     *            <id>3</id>
     *            <title>QuickSupport Fusion</title>
     *            <type>public</type>
     *            <app>livechat</ap>
     *            <displayorder>3</displayorder>
     *            <parentdepartmentid>2</parentdepartmentid>
     *            <uservisibilitycustom>0</uservisibilitycustom>
     *        </department>
     *    </departments>
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_DepartmentObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_DepartmentObject = new SWIFT_Department($_departmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Department Load Failed' . $_errorMessage);

            return false;
        }

        // Check for Type
        if (isset($_POST['type']) && $_POST['type'] != 'public' && $_POST['type'] != 'private') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Department Type Specified. Valid values are "public" and "private".');

            return false;
        }
        // Check for Parent Department ID
        $_parentDepartmentID = 0;
        if (isset($_POST['parentdepartmentid']) && !empty($_POST['parentdepartmentid'])) {
            $_SWIFT_DepartmentObject_Parent = false;

            try {
                $_SWIFT_DepartmentObject_Parent = new SWIFT_Department($_POST['parentdepartmentid']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Parent Department');

                return false;
            }

            if (!$_SWIFT_DepartmentObject_Parent instanceof SWIFT_Department || !$_SWIFT_DepartmentObject_Parent->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Parent Department: 2');

                return false;
            }
            // @codeCoverageIgnoreEnd

            if ($_SWIFT_DepartmentObject_Parent->GetProperty('departmentapp') != $_SWIFT_DepartmentObject->GetProperty('departmentapp')) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Parent Department App. The app type of both parent and child department should match.');

                return false;
            }

            $_parentDepartmentID = $_SWIFT_DepartmentObject_Parent->GetDepartmentID();
        }

        if (isset($_POST['title']) && trim($_POST['title']) != '' && !empty($_POST['title'])) {
            $_departmentApp = $_SWIFT_DepartmentObject->GetProperty('departmentapp');
            $_departmentType = $_SWIFT_DepartmentObject->GetProperty('departmenttype');
            if (isset($_POST['type']) && $_POST['type'] == 'public') {
                $_departmentType = SWIFT_Department::DEPARTMENT_PUBLIC;
            } else if (isset($_POST['type']) && $_POST['type'] == 'private') {
                $_departmentType = SWIFT_Department::DEPARTMENT_PRIVATE;
            }

            $_displayOrder = $_SWIFT_DepartmentObject->GetProperty('displayorder');
            if (isset($_POST['displayorder'])) {
                $_displayOrder = (int)($_POST['displayorder']);
            }

            $_userVisibilityCustom = false;
            $_userGroupIDList = array();
            if (isset($_POST['uservisibilitycustom']) && $_POST['uservisibilitycustom'] == '1') {
                $_userVisibilityCustom = 1;

                if (isset($_POST['usergroupid']) && _is_array($_POST['usergroupid'])) {
                    $_userGroupIDList = $_POST['usergroupid'];
                }
            }

            $_SWIFT_DepartmentObject->Update($_POST['title'], $_departmentApp, $_departmentType, $_displayOrder, $_parentDepartmentID, $_userVisibilityCustom, $_userGroupIDList);

            $this->ProcessDepartments($_SWIFT_DepartmentObject->GetDepartmentID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return true;
    }

    /**
     * Delete a Department
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_DepartmentObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_DepartmentObject = new SWIFT_Department($_departmentID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_DepartmentObject instanceof SWIFT_Department || !$_SWIFT_DepartmentObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Department Load Failed' . $_errorMessage);

            return false;
        }

        SWIFT_Department::DeleteList(array($_departmentID));

        return true;
    }
}

?>
