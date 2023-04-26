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

use Base\Models\User\SWIFT_UserGroup;
use Controller_api;
use SWIFT;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;

/**
 * The UserGroup API Controller
 *
 * @author Varun Shoor
 */
class Controller_UserGroup extends Controller_api implements SWIFT_REST_Interface
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
     * Retrieve & Dispatch the User Groups
     *
     * @author Varun Shoor
     * @param int $_userGroupID (OPTIONAL) The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessUserGroups($_userGroupID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_userGroupContainer = array();

        if (!empty($_userGroupID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups WHERE usergroupid = '" . ($_userGroupID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY usergroupid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_userGroupContainer[$this->Database->Record['usergroupid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('usergroups');
        foreach ($_userGroupContainer as $_userGroupID => $_userGroup) {
            $_userGroupType = 'guest';
            if ($_userGroup['grouptype'] == SWIFT_UserGroup::TYPE_REGISTERED) {
                $_userGroupType = 'registered';
            }

            $this->XML->AddParentTag('usergroup');
            $this->XML->AddTag('id', $_userGroupID);
            $this->XML->AddTag('title', $_userGroup['title']);
            $this->XML->AddTag('grouptype', $_userGroupType);
            $this->XML->AddTag('ismaster', $_userGroup['ismaster']);
            $this->XML->EndParentTag('usergroup');
        }
        $this->XML->EndParentTag('usergroups');

        return true;
    }

    /**
     * Get a list of User Groups
     *
     * Example Output:
     *    <usergroups>
     *        <usergroup>
     *            <id>1</id>
     *            <title>Registered</title>
     *            <grouptype>registered</grouptype>
     *            <ismaster>1</ismaster>
     *        </usergroup>
     *        <usergroup>
     *            <id>2</id>
     *            <title>Guest</title>
     *            <grouptype>guest</grouptype>
     *            <ismaster>1</ismaster>
     *        </usergroup>
     *        <usergroup>
     *            <id>3</id>
     *            <title>Clients</title>
     *            <grouptype>registered</grouptype>
     *            <ismaster>0</ismaster>
     *        </usergroup>
     *    </usergroups>
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

        $this->ProcessUserGroups();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the User Group
     *
     * Example Output:
     *    <usergroups>
     *        <usergroup>
     *            <id>1</id>
     *            <title>Registered</title>
     *            <grouptype>registered</grouptype>
     *            <ismaster>1</ismaster>
     *        </usergroup>
     *    </usergroups>
     *
     * @author Varun Shoor
     * @param string $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_userGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessUserGroups((int)($_userGroupID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a User Group
     *
     * Required Fields:
     * title
     * grouptype
     *
     * Example Output:
     *    <usergroups>
     *        <usergroup>
     *            <id>1</id>
     *            <title>Registered</title>
     *            <grouptype>registered</grouptype>
     *            <ismaster>1</ismaster>
     *        </usergroup>
     *    </usergroups>
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

        if (isset($_POST['title']) && trim($_POST['title']) != '' && !empty($_POST['title']) && isset($_POST['grouptype']) && ($_POST['grouptype'] == 'guest' || $_POST['grouptype'] == 'registered')) {
            $_groupType = SWIFT_UserGroup::TYPE_GUEST;
            if ($_POST['grouptype'] == 'registered') {
                $_groupType = SWIFT_UserGroup::TYPE_REGISTERED;
            }

            $_SWIFT_UserGroupObject = SWIFT_UserGroup::Create($_POST['title'], $_groupType, false);
            if (!$_SWIFT_UserGroupObject instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Group Creation Failed');

                return false;
            }
            // @codeCoverageIgnoreEnd

            $this->ProcessUserGroups($_SWIFT_UserGroupObject->GetUserGroupID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return false;
    }

    /**
     * Update the User Group ID
     *
     * Required Fields:
     * title
     *
     * Example Output:
     *    <usergroups>
     *        <usergroup>
     *            <id>1</id>
     *            <title>Registered</title>
     *            <grouptype>registered</grouptype>
     *            <ismaster>1</ismaster>
     *        </usergroup>
     *    </usergroups>
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_userGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_UserGroupObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_UserGroupObject = new SWIFT_UserGroup($_userGroupID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_UserGroupObject instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Group Load Failed' . $_errorMessage);

            return false;
        }

        if (isset($_POST['title']) && trim($_POST['title']) != '' && !empty($_POST['title'])) {
            $_groupType = $_SWIFT_UserGroupObject->GetProperty('grouptype');
            $_SWIFT_UserGroupObject->Update($_POST['title'], $_groupType);

            $this->ProcessUserGroups($_SWIFT_UserGroupObject->GetUserGroupID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return true;
    }

    /**
     * Delete a User Group
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_userGroupID The User Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_UserGroupObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_UserGroupObject = new SWIFT_UserGroup($_userGroupID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_UserGroupObject instanceof SWIFT_UserGroup || !$_SWIFT_UserGroupObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Group Load Failed' . $_errorMessage);

            return false;
        } else if ($_SWIFT_UserGroupObject->GetProperty('ismaster') == '1') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Cannot delete master user group');

            return false;
        }

        SWIFT_UserGroup::DeleteList(array($_userGroupID));

        return true;
    }
}

?>
