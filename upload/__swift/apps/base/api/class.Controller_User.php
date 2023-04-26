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

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserOrganization;
use Controller_api;
use SWIFT;
use SWIFT_DataID;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;

/**
 * The User API Controller
 *
 * @author Varun Shoor
 */
class Controller_User extends Controller_api implements SWIFT_REST_Interface
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

        $this->Language->Load('staff_users');
    }

    /**
     * Retrieve & Dispatch the Users
     *
     * @author Varun Shoor
     * @param int $_userID (OPTIONAL) The User ID
     * @param int $_marker (OPTIONAL) The User ID Marker
     * @param int $_maxRecords (OPTIONAL) The Max Records
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessUsers($_userID = 0, $_marker = 0, $_maxRecords = 1000)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_maxRecords > 1000) {
            $_maxRecords = 1000;
        }

        $_userContainer = array();

        $_whereContainer = array();
        if (!empty($_userID)) {
            $_whereContainer[] = "userid = '" . ($_userID) . "'";
        }

        if (!empty($_marker)) {
            $_whereContainer[] = "userid >= '" . $_marker . "'";
        }

        $_userIDList = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "users" . IIF(count($_whereContainer) > 0, " WHERE " . implode(' AND ', $_whereContainer)) . " ORDER BY userid ASC", $_maxRecords);

        while ($this->Database->NextRecord()) {
            $_userContainer[$this->Database->Record['userid']] = $this->Database->Record;
            $_userContainer[$this->Database->Record['userid']]['emails'] = array();

            $_userIDList[] = $this->Database->Record['userid'];
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_userContainer[$this->Database->Record['linktypeid']]['emails'][] = $this->Database->Record['email'];
        }

        $_salutationList = SWIFT_User::RetrieveSalutationList();

        $this->XML->AddParentTag('users');
        foreach ($_userContainer as $_userID => $_user) {
            $_userRole = 'user';
            if ($_user['userrole'] == SWIFT_User::ROLE_MANAGER) {
                $_userRole = 'manager';
            }

            $_userSalutation = '';
            if (isset($_salutationList[$_user['salutation']])) {
                $_userSalutation = $_salutationList[$_user['salutation']];
            }

            $this->XML->AddParentTag('user');
            $this->XML->AddTag('id', $_userID);
            $this->XML->AddTag('usergroupid', (int)($_user['usergroupid']));
            $this->XML->AddTag('userrole', $_userRole);
            $this->XML->AddTag('userorganizationid', (int)($_user['userorganizationid']));
            $this->XML->AddTag('salutation', $_userSalutation);

            $this->XML->AddTag('userexpiry', $_user['userexpirytimeline']);

            $this->XML->AddTag('fullname', $_user['fullname']);
            foreach ($_user['emails'] as $_emailAddress) {
                $this->XML->AddTag('email', $_emailAddress);
            }

            $this->XML->AddTag('designation', $_user['userdesignation']);
            $this->XML->AddTag('phone', $_user['phone']);

            $this->XML->AddTag('dateline', $_user['dateline']);
            $this->XML->AddTag('lastvisit', $_user['lastvisit']);
            $this->XML->AddTag('isenabled', $_user['isenabled']);
            $this->XML->AddTag('timezone', $_user['timezonephp']);
            $this->XML->AddTag('enabledst', $_user['enabledst']);

            $this->XML->AddTag('slaplanid', $_user['slaplanid']);
            $this->XML->AddTag('slaplanexpiry', $_user['slaexpirytimeline']);
            $this->XML->EndParentTag('user');
        }
        $this->XML->EndParentTag('users');

        return true;
    }

    /**
     * Get a list of Users
     *
     * Example Output:
     *
     * <users>
     *    <user>
     *        <id>1</id>
     *        <usergroupid>2</usergroupid>
     *        <userrole>manager</userrole>
     *        <userorganizationid>1</userorganizationid>
     *        <salutation>Mr.</salutation>
     *        <userexpiry>0</userexpiry>
     *        <fullname>John Doe</fullname>
     *        <email>john.doe@kayako.com</email>
     *        <email>john@kayako.com</email>
     *        <designation>CEO</designation>
     *        <phone>123456789</phone>
     *        <dateline><![CDATA[1296540309]]></dateline>
     *        <lastvisit><![CDATA[0]]></lastvisit>
     *        <isenabled><![CDATA[1]]></isenabled>
     *        <timezone>GMT</timezone>
     *        <enabledst><![CDATA[0]]></enabledst>
     *        <slaplanid><![CDATA[0]]></slaplanid>
     *        <slaplanexpiry><![CDATA[0]]></slaplanexpiry>
     *    </user>
     * </users>
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

        $this->ProcessUsers();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Get a list of Users
     *
     * Example Output:
     *
     * <users>
     *    <user>
     *        <id>1</id>
     *        <usergroupid>2</usergroupid>
     *        <userrole>manager</userrole>
     *        <userorganizationid>1</userorganizationid>
     *        <salutation>Mr.</salutation>
     *        <userexpiry>0</userexpiry>
     *        <fullname>John Doe</fullname>
     *        <email>john.doe@kayako.com</email>
     *        <email>john@kayako.com</email>
     *        <designation>CEO</designation>
     *        <phone>123456789</phone>
     *        <dateline>1296540309</dateline>
     *        <lastvisit>0</lastvisit>
     *        <isenabled>1</isenabled>
     *        <timezone>GMT</timezone>
     *        <enabledst>0</enabledst>
     *        <slaplanid>0</slaplanid>
     *        <slaplanexpiry>0</slaplanexpiry>
     *    </user>
     * </users>
     *
     * @author Varun Shoor
     * @param int $_marker (OPTIONAL)
     * @param int $_maxRecords (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Filter($_marker = 0, $_maxRecords = 1000)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessUsers(0, $_marker, $_maxRecords);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the User
     *
     * Example Output:
     *
     * <users>
     *    <user>
     *        <id>1</id>
     *        <usergroupid>2</usergroupid>
     *        <userrole>manager</userrole>
     *        <userorganizationid>1</userorganizationid>
     *        <salutation>Mr.</salutation>
     *        <userexpiry>0</userexpiry>
     *        <fullname>John Doe</fullname>
     *        <email>john.doe@kayako.com</email>
     *        <email>john@kayako.com</email>
     *        <designation>CEO</designation>
     *        <phone>123456789</phone>
     *        <dateline><![CDATA[1296540309]]></dateline>
     *        <lastvisit><![CDATA[0]]></lastvisit>
     *        <isenabled><![CDATA[1]]></isenabled>
     *        <timezone>GMT</timezone>
     *        <enabledst><![CDATA[0]]></enabledst>
     *        <slaplanid><![CDATA[0]]></slaplanid>
     *        <slaplanexpiry><![CDATA[0]]></slaplanexpiry>
     *    </user>
     * </users>
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessUsers($_userID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a User
     *
     * Required Fields:
     * title
     * grouptype
     *
     * Example Output:
     *
     * <users>
     *    <user>
     *        <id>1</id>
     *        <usergroupid>2</usergroupid>
     *        <userrole>manager</userrole>
     *        <userorganizationid>1</userorganizationid>
     *        <salutation>Mr.</salutation>
     *        <userexpiry>0</userexpiry>
     *        <fullname>John Doe</fullname>
     *        <email>john.doe@kayako.com</email>
     *        <email>john@kayako.com</email>
     *        <designation>CEO</designation>
     *        <phone>123456789</phone>
     *        <dateline><![CDATA[1296540309]]></dateline>
     *        <lastvisit><![CDATA[0]]></lastvisit>
     *        <isenabled><![CDATA[1]]></isenabled>
     *        <timezone>GMT</timezone>
     *        <enabledst><![CDATA[0]]></enabledst>
     *        <slaplanid><![CDATA[0]]></slaplanid>
     *        <slaplanexpiry><![CDATA[0]]></slaplanexpiry>
     *    </user>
     * </users>
     *
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userGroupCache = $this->Cache->Get('usergroupcache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');
        if (!isset($_POST['usergroupid']) || empty($_POST['usergroupid']) || !isset($_userGroupCache[$_POST['usergroupid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid User Group');

            return false;
        } else if ($_userGroupCache[$_POST['usergroupid']]['grouptype'] != SWIFT_UserGroup::TYPE_REGISTERED) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Group Specified is not of "Registered" type');

            return false;
        }

        $_salutationList = SWIFT_User::RetrieveSalutationList();
        $_salutation = '';
        if (isset($_POST['salutation'])) {
            foreach ($_salutationList as $_salutationID => $_salutationTitle) {
                if (mb_strtolower($_salutationTitle) == mb_strtolower($_POST['salutation'])) {
                    $_salutation = $_salutationID;
                }
            }
        }

        $_userOrganizationID = 0;
        if (isset($_POST['userorganizationid']) && !empty($_POST['userorganizationid'])) {
            $_SWIFT_UserOrganizationObject = false;

            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_POST['userorganizationid']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid User Organization ID');

                return false;
            }

            $_userOrganizationID = (int)($_POST['userorganizationid']);
        }

        $_userDesignation = '';
        if (isset($_POST['designation']) && !empty($_POST['designation'])) {
            $_userDesignation = $_POST['designation'];
        }

        $_phone = '';
        if (isset($_POST['phone']) && !empty($_POST['phone'])) {
            $_phone = $_POST['phone'];
        }

        $_isEnabled = true;
        if (isset($_POST['isenabled'])) {
            $_isEnabled = (int)($_POST['isenabled']);
        }

        $_userRole = SWIFT_User::ROLE_USER;
        if (isset($_POST['userrole']) && $_POST['userrole'] == 'manager') {
            $_userRole = SWIFT_User::ROLE_MANAGER;
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5195 : Better handling of email address for a user account
         *
         * Comments : Prevent customers to register the email address same as the email queue.
         **/
        $this->Load->Model('EmailQueue:EmailQueue', [], false, false, APP_PARSER);

        $_EmailQueueList = SWIFT_EmailQueue::RetrieveEmailofAllEmailQueues();

        $_emailContainer = array();
        if (isset($_POST['email']) && _is_array($_POST['email'])) {
            foreach ($_POST['email'] as $_emailAddress) {
                if (!IsEmailValid($_emailAddress)) {
                    continue;
                }

                $_emailContainer[] = $_emailAddress;
            }
        } else if (isset($_POST['email']) && is_string($_POST['email']) && IsEmailValid($_POST['email'])) {
            if (in_array($_POST['email'], $_EmailQueueList)) {

                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'The email address entered is already used in the desk; Please enter the valid email address.');

                return false;
            }

            $_emailContainer[] = $_POST['email'];
        }

        $_userEmailCheckResult = SWIFT_UserEmail::CheckEmailRecordExists($_emailContainer);
        if (_is_array($_userEmailCheckResult)) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Unable to insert user. Email address belongs to an existing user.');

            return false;
        }

        if (!count($_emailContainer)) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Email Specified');

            return false;
        }

        $_timeZone = '';
        if (isset($_POST['timezone']) && !empty($_POST['timezone'])) {
            $_timeZone = $_POST['timezone'];
        }

        $_enableDST = false;
        if (isset($_POST['enabledst'])) {
            $_enableDST = (int)($_POST['enabledst']);
        }

        $_slaPlanID = 0;
        if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid']) && !isset($_slaPlanCache[$_POST['slaplanid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid SLA Plan');

            return false;
        } else if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid']) && isset($_slaPlanCache[$_POST['slaplanid']])) {
            $_slaPlanID = (int)($_slaPlanCache[$_POST['slaplanid']]);
        }

        $_slaPlanExpiry = 0;
        if (isset($_POST['slaplanexpiry']) && !empty($_POST['slaplanexpiry'])) {
            $_slaPlanExpiry = (int)($_POST['slaplanexpiry']);
        }

        $_userExpiry = 0;
        if (isset($_POST['userexpiry']) && !empty($_POST['userexpiry'])) {
            $_userExpiry = (int)($_POST['userexpiry']);
        }

        $_sendWelcomeEmail = false;
        if (isset($_POST['sendwelcomeemail'])) {
            $_sendWelcomeEmail = (int)($_POST['sendwelcomeemail']);
        }


        if (isset($_POST['fullname']) && trim($_POST['fullname']) != '' && !empty($_POST['fullname']) &&
            isset($_POST['password']) && trim($_POST['password']) != '' && !empty($_POST['password'])) {

            $_SWIFT_UserObject = SWIFT_User::Create($_POST['usergroupid'], $_userOrganizationID, $_salutation, $_POST['fullname'], $_userDesignation, $_phone, $_isEnabled, $_userRole, $_emailContainer, $_POST['password'],
                0, $_timeZone, $_enableDST, $_slaPlanID, $_slaPlanExpiry, $_userExpiry, $_sendWelcomeEmail, true, false);
            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Creation Failed');

                return false;
            }
            // @codeCoverageIgnoreEnd

            $this->ProcessUsers($_SWIFT_UserObject->GetUserID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return false;
    }

    /**
     * Update the User ID
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
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_userID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_UserObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Load Failed' . $_errorMessage);

            return false;
        }


        $_userGroupCache = $this->Cache->Get('usergroupcache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');

        $_salutationList = SWIFT_User::RetrieveSalutationList();
        $_salutation = $_SWIFT_UserObject->GetProperty('salutation');
        if (isset($_POST['salutation'])) {
            foreach ($_salutationList as $_salutationID => $_salutationTitle) {
                if (mb_strtolower($_salutationTitle) == mb_strtolower($_POST['salutation'])) {
                    $_salutation = $_salutationID;
                }
            }
        }

        $_userOrganizationID = $_SWIFT_UserObject->GetProperty('userorganizationid');
        if (isset($_POST['userorganizationid']) && !empty($_POST['userorganizationid'])) {
            $_SWIFT_UserOrganizationObject = false;

            try {
                $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_POST['userorganizationid']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid User Organization ID');

                return false;
            }

            $_userOrganizationID = (int)($_POST['userorganizationid']);
        }

        $_userDesignation = $_SWIFT_UserObject->GetProperty('userdesignation');
        if (isset($_POST['designation']) && !empty($_POST['designation'])) {
            $_userDesignation = $_POST['designation'];
        }

        $_phone = $_SWIFT_UserObject->GetProperty('phone');
        if (isset($_POST['phone']) && !empty($_POST['phone'])) {
            $_phone = $_POST['phone'];
        }

        $_isEnabled = $_SWIFT_UserObject->GetProperty('isenabled');
        if (isset($_POST['isenabled'])) {
            $_isEnabled = (int)($_POST['isenabled']);
        }

        $_userRole = $_SWIFT_UserObject->GetProperty('userrole');
        if (isset($_POST['userrole']) && $_POST['userrole'] == 'manager') {
            $_userRole = SWIFT_User::ROLE_MANAGER;
        } else if (isset($_POST['userrole']) && $_POST['userrole'] == 'user') {
            $_userRole = SWIFT_User::ROLE_USER;
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5195 : Better handling of email address for a user account
         *
         * Comments : Prevent customers to register the email address same as the email queue.
         **/
        $this->Load->Model('EmailQueue:EmailQueue', [], false, false, APP_PARSER);

        $_EmailQueueList = SWIFT_EmailQueue::RetrieveEmailofAllEmailQueues();

        $_emailContainer = array();
        if (isset($_POST['email']) && _is_array($_POST['email'])) {
            foreach ($_POST['email'] as $_emailAddress) {
                if (!IsEmailValid($_emailAddress)) {
                    continue;
                }

                $_emailContainer[] = $_emailAddress;
            }
        } else if (isset($_POST['email']) && is_string($_POST['email']) && IsEmailValid($_POST['email'])) {
            if (in_array($_POST['email'], $_EmailQueueList)) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'The email address entered is already used in the desk; Please enter the valid email address.');

                return false;
            }
            $_emailContainer[] = $_POST['email'];
        }

        $_userEmailCheckResult = SWIFT_UserEmail::CheckEmailRecordExists($_emailContainer, $_SWIFT_UserObject->GetUserID());
        if (_is_array($_userEmailCheckResult)) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Unable to update user. Email address belongs to an existing user.');

            return false;
        }

        if (!count($_emailContainer)) {
            $_emailContainer = $_SWIFT_UserObject->GetEmailList();
        }

        $_timeZone = $_SWIFT_UserObject->GetProperty('timezonephp');
        if (isset($_POST['timezone']) && !empty($_POST['timezone'])) {
            $_timeZone = $_POST['timezone'];
        }

        $_enableDST = $_SWIFT_UserObject->GetProperty('enabledst');
        if (isset($_POST['enabledst'])) {
            $_enableDST = (int)($_POST['enabledst']);
        }

        $_slaPlanID = $_SWIFT_UserObject->GetProperty('slaplanid');
        if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid']) && !isset($_slaPlanCache[$_POST['slaplanid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid SLA Plan');

            return false;
        } else if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid']) && isset($_slaPlanCache[$_POST['slaplanid']])) {
            $_slaPlanID = (int)($_slaPlanCache[$_POST['slaplanid']]);
        }

        $_slaPlanExpiry = $_SWIFT_UserObject->GetProperty('slaexpirytimeline');
        if (isset($_POST['slaplanexpiry']) && !empty($_POST['slaplanexpiry'])) {
            $_slaPlanExpiry = (int)($_POST['slaplanexpiry']);
        }

        $_userExpiry = $_SWIFT_UserObject->GetProperty('userexpirytimeline');
        if (isset($_POST['userexpiry']) && !empty($_POST['userexpiry'])) {
            $_userExpiry = (int)($_POST['userexpiry']);
        }


        if (isset($_POST['fullname']) && trim($_POST['fullname']) != '' && !empty($_POST['fullname'])) {
            $_userGroupID = $_SWIFT_UserObject->GetProperty('usergroupid');
            if (isset($_POST['usergroupid'])) {
                if (empty($_POST['usergroupid']) || !isset($_userGroupCache[$_POST['usergroupid']])) {
                    $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid User Group');

                    return false;
                } else if ($_userGroupCache[$_POST['usergroupid']]['grouptype'] != SWIFT_UserGroup::TYPE_REGISTERED) {
                    $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Group Specified is not of "Registered" type');

                    return false;
                }

                $_userGroupID = $_POST['usergroupid'];
            }

            $_SWIFT_UserObject->Update($_userGroupID, $_userOrganizationID, $_salutation, $_POST['fullname'], $_userDesignation, $_phone, $_isEnabled, $_userRole, $_emailContainer, 0,
                $_timeZone, $_enableDST, $_slaPlanID, $_slaPlanExpiry, $_userExpiry);

            $this->ProcessUsers($_SWIFT_UserObject->GetUserID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return true;
    }

    /**
     * Delete a User
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_UserObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Load Failed' . $_errorMessage);

            return false;
        }

        SWIFT_User::DeleteList(array($_userID));

        return true;
    }
}

?>
