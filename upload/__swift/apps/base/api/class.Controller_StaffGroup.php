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

namespace Base\API;

use Base\Models\Staff\SWIFT_StaffGroup;
use Controller_api;
use SWIFT;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;

/**
 * The StaffGroup API Controller
 *
 * @author Varun Shoor
 */
class Controller_StaffGroup extends Controller_api implements SWIFT_REST_Interface
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
     * Retrieve & Dispatch the Staff Groups
     *
     * @author Varun Shoor
     * @param int $_staffGroupID (OPTIONAL) The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessStaffGroups($_staffGroupID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_staffGroupContainer = array();

        if (!empty($_staffGroupID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup WHERE staffgroupid = '" . ($_staffGroupID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY staffgroupid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_staffGroupContainer[$this->Database->Record['staffgroupid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('staffgroups');
        foreach ($_staffGroupContainer as $_staffGroupID => $_staffGroup) {
            $this->XML->AddParentTag('staffgroup');
            $this->XML->AddTag('id', $_staffGroupID);
            $this->XML->AddTag('title', $_staffGroup['title']);
            $this->XML->AddTag('isadmin', $_staffGroup['isadmin']);
            $this->XML->EndParentTag('staffgroup');
        }
        $this->XML->EndParentTag('staffgroups');

        return true;
    }

    /**
     * Get a list of Staff Groups
     *
     * Example Output:
     *    <staffgroups>
     *        <staffgroup>
     *            <id>1</id>
     *            <title>Administrator</title>
     *            <isadmin>1</isadmin>
     *        </staffgroup>
     *    </staffgroups>
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

        $this->ProcessStaffGroups();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Staff Group
     *
     * Example Output:
     *    <staffgroups>
     *        <staffgroup>
     *            <id>1</id>
     *            <title>Administrator</title>
     *            <isadmin>1</isadmin>
     *        </staffgroup>
     *    </staffgroups>
     *
     * @author Varun Shoor
     * @param string $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_staffGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessStaffGroups((int)($_staffGroupID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a Staff Group
     *
     * Required Fields:
     * title
     * isadmin
     *
     * Example Output:
     *    <staffgroups>
     *        <staffgroup>
     *            <id>1</id>
     *            <title>Administrator</title>
     *            <isadmin>1</isadmin>
     *        </staffgroup>
     *    </staffgroups>
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

        if (isset($_POST['title']) && trim($_POST['title']) != '' && !empty($_POST['title']) && isset($_POST['isadmin'])) {
            $_isAdmin = false;
            if ($_POST['isadmin'] == '1') {
                $_isAdmin = true;
            }

            $_SWIFT_StaffGroupObject = SWIFT_StaffGroup::Insert($_POST['title'], $_isAdmin);
            if (!$_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup || !$_SWIFT_StaffGroupObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff Group Creation Failed');

                return false;
            }
            // @codeCoverageIgnoreEnd

            $this->ProcessStaffGroups($_SWIFT_StaffGroupObject->GetStaffGroupID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return false;
    }

    /**
     * Update the Staff Group ID
     *
     * Required Fields:
     * title
     *
     * Example Output:
     *    <staffgroups>
     *        <staffgroup>
     *            <id>1</id>
     *            <title>Administrator</title>
     *            <isadmin>1</isadmin>
     *        </staffgroup>
     *    </staffgroups>
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_staffGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_StaffGroupObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_StaffGroupObject = new SWIFT_StaffGroup($_staffGroupID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup || !$_SWIFT_StaffGroupObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff Group Load Failed' . $_errorMessage);

            return false;
        }

        if (isset($_POST['title']) && trim($_POST['title']) != '' && !empty($_POST['title'])) {
            $_isAdmin = $_SWIFT_StaffGroupObject->GetProperty('isadmin');
            if (isset($_POST['isadmin']) && $_POST['isadmin'] == '1') {
                $_isAdmin = true;
            } else if (isset($_POST['isadmin']) && $_POST['isadmin'] == '0') {
                $_isAdmin = false;
            }

            $_SWIFT_StaffGroupObject->Update($_POST['title'], $_isAdmin);

            $this->ProcessStaffGroups($_SWIFT_StaffGroupObject->GetStaffGroupID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return true;
    }

    /**
     * Delete a Staff Group
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_staffGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_StaffGroupObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_StaffGroupObject = new SWIFT_StaffGroup($_staffGroupID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_StaffGroupObject instanceof SWIFT_StaffGroup || !$_SWIFT_StaffGroupObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff Group Load Failed' . $_errorMessage);

            return false;
        }

        $_totalStaff = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staff WHERE staffgroupid = '" . ($_staffGroupID) . "'");
        if (isset($_totalStaff['totalitems']) && $_totalStaff['totalitems'] > 0) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff Group Deletion Failed. There are "' . $_totalStaff['totalitems'] . '" staff members assigned to this group. Please delete them or change their group to continue.');

            return false;
        }

        SWIFT_StaffGroup::DeleteList(array($_staffGroupID));

        return true;
    }
}

?>
