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

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Controller_api;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;

/**
 * User Search API Controller
 *
 * @author Mahesh Salaria
 */
class Controller_UserSearch extends Controller_api implements SWIFT_REST_Interface
{

    /**
     * Constructor
     *
     * @author Mahesh Salaria
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');

        $this->Language->Load('staff_users');
    }

    /**
     * Initiate the User Search
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+UserSearch
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_query = '';
        if (isset($_POST['query']) && !empty($_POST['query'])) {
            $_query = $_POST['query'];
        }

        if (empty($_query)) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Query should not be empty');
            return false;
        }

        $_phrase = isset($_POST['phrase']) && (int)$_POST['phrase'] === 1;

        $_userIDList = array();
        $_userContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            LEFT JOIN " . TABLE_PREFIX . "usergroups AS usergroups ON (users.usergroupid = usergroups.usergroupid)
            WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND
                ((" . BuildSQLSearch('useremails.email', $_query, true, !$_phrase) . ")
                    OR (" . BuildSQLSearch('users.fullname', $_query, false, !$_phrase) . ")
                    OR (" . BuildSQLSearch('users.phone', $_query, false, !$_phrase) . ")
                    OR (" . BuildSQLSearch('userorganizations.organizationname', $_query, false, !$_phrase) . ")
                    OR (" . BuildSQLSearch('usergroups.title', $_query, false, !$_phrase) . "))");
        while ($this->Database->NextRecord()) {
            $_userContainer[$this->Database->Record['userid']] = $this->Database->Record;
            $_userContainer[$this->Database->Record['userid']]['emails'] = array();

            $_userIDList[] = $this->Database->Record['userid'];
        }

        $this->Database->Query("SELECT useremails.* FROM " . TABLE_PREFIX . "useremails AS useremails
                WHERE useremails.linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "'
                AND useremails.linktypeid IN (" . BuildIN($_userIDList) . ")");
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

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+UserSearch
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Base/UserSearch instead.');

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+UserSearch
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Base/UserSearch instead.');

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+UserSearch
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Base/UserSearch instead.');

        return true;
    }

    /**
     * Not Implemented
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+UserSearch
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call POST /Base/UserSearch instead.');

        return true;
    }

}
