<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, QuickSupport Singapore Pte. Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace News\Api;

use News\Models\Subscriber\SWIFT_NewsSubscriber;
use SWIFT;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use Controller_api;
use SWIFT_RESTServer;
use SWIFT_XML;

/**
 * The News Subscribers API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 * @author Simaranjit Singh
 */
class Controller_Subscriber extends Controller_api implements SWIFT_REST_Interface
{
    protected $sendEmails = true;

    /**
     * Constructor
     *
     * @author Simaranjit Singh
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Retrieve & Dispatch the Subscribers
     *
     * @author Simaranjit Singh
     * @param int $_newsSubscriberID (OPTIONAL) The News Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessNewsSubscribers($_newsSubscriberID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_subscriberContainer = array();

        if (!empty($_newsSubscriberID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newssubscribers WHERE newssubscriberid = '" .  ($_newsSubscriberID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newssubscribers ORDER BY newssubscriberid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_subscriberContainer[$this->Database->Record['newssubscriberid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('newssubscribers');

        foreach ($_subscriberContainer as $_newsSubID => $_subscriber) {
            $this->XML->AddParentTag('newssubscriber');
            $this->XML->AddTag('id', $_newsSubID);
            $this->XML->AddTag('tgroupid', $_subscriber['tgroupid']);
            $this->XML->AddTag('userid', $_subscriber['userid']);
            $this->XML->AddTag('email', $_subscriber['email']);
            $this->XML->AddTag('isvalidated', $_subscriber['isvalidated']);
            $this->XML->AddTag('usergroupid', $_subscriber['usergroupid']);
            $this->XML->EndParentTag('newssubscriber');
        }

        $this->XML->EndParentTag('newssubscribers');

        return true;
    }

    /**
     * Get a list of Subscribers
     *
     * Example Output:
     *
     * <newssubscribers>
     * <newssubscriber>
     *         <id>1</id>
     *         <tgroupid>1</tgroupid>
     *         <userid>0</userid>
     *         <email>john.doe@kayako.com</email>
     *         <isvalidated>0</isvalidated>
     *         <usergroupid>1</usergroupid>
     * </newssubscriber>
     * </newssubscribers>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessNewsSubscribers(0);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve a Subscriber
     *
     * Example Output:
     *
     * <newssubscribers>
     * <newssubscriber>
     *         <id>1</id>
     *         <tgroupid>1</tgroupid>
     *         <userid>0</userid>
     *         <email>john.doe@kayako.com</email>
     *         <isvalidated>0</isvalidated>
     *         <usergroupid>1</usergroupid>
     * </newssubscriber>
     * </newssubscribers>
     *
     * @author Simaranjit Singh
     * @param string $_newsSubscriberID The News Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_newsSubscriberID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessNewsSubscribers((int) ($_newsSubscriberID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a Subscriber
     *
     * Required Fields:
     * email
     *
     * Example Output:
     *
     * <newssubscribers>
     * <subscriber>
     *         <id>1</id>
     *         <tgroupid>1</tgroupid>
     *         <userid>0</userid>
     *         <email>john.doe@kayako.com</email>
     *         <isvalidated>0</isvalidated>
     *         <sendemails>1</sendemails>
     *         <usergroupid>1</usergroupid>
     * </subscriber>
     * </newssubscribers>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['email']) || trim($_POST['email']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Email field is empty');

            return false;
        }

        if (!IsEmailValid($_POST['email'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid email address');

            return false;
        }

        $_isValidated = '0';
        if (isset($_POST['isvalidated']) && !empty($_POST['isvalidated'])) {
            $_isValidated = '1';
        }

        $_sendEmails = $this->sendEmails;
        if (isset($_POST['sendemails'])) {
            $_sendEmails = (bool)$_POST['sendemails'];
        }

        $_SWIFT_NewsSubscriberObject = false;

        try {
            $_SWIFT_NewsSubscriberObject = SWIFT_NewsSubscriber::Create($_POST['email'], (bool)$_isValidated, 0, 0, $_sendEmails);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Subscription failed, please check if already subscribed');

            return false;
        }

        $this->ProcessNewsSubscribers($_SWIFT_NewsSubscriberObject);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update News Subscriber
     *
     * Required Fields:
     * email
     *
     * Example Output:
     *
     * <newssubscribers>
     * <subscriber>
     *         <id>1</id>
     *         <tgroupid>1</tgroupid>
     *         <userid>0</userid>
     *         <email>john.doe@kayako.com</email>
     *         <isvalidated>0</isvalidated>
     *         <usergroupid>1</usergroupid>
     * </subscriber>
     * </newssubscribers>
     *
     * @author Simaranjit Singh
     * @param int $_newsSubscriberID The news Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_newsSubscriberID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_NewsSubscriberObject = false;
        try {
            $_SWIFT_NewsSubscriberObject = new SWIFT_NewsSubscriber($_newsSubscriberID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid news subscriber ID');

            return false;
        }

        $_email = $_SWIFT_NewsSubscriberObject->GetProperty('email');

        if (isset($_POST['email']) && trim($_POST['email']) != '') {

            if (!IsEmailValid($_POST['email'])) {
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid email address');

                return false;
            } else {
                $_email = $_POST['email'];
            }
        }

        $_SWIFT_NewsSubscriberObject->Update($_email);

        $this->ProcessNewsSubscribers($_SWIFT_NewsSubscriberObject->GetNewsSubscriberID());

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Delete News Subscriber
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Simaranjit Singh
     * @param int $_newsSubscriberID The news Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_newsSubscriberID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_NewsSubscriberObject = false;
        try {
            $_SWIFT_NewsSubscriberObject = new SWIFT_NewsSubscriber($_newsSubscriberID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid news subscriber ID');
        }

        SWIFT_NewsSubscriber::DeleteList(array($_newsSubscriberID));

        return true;
    }

}
