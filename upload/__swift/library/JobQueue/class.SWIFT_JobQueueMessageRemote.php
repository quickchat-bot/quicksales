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

/**
 * The Job Queue Remote Message Management Class
 *
 * @property SWIFT_JobQueue $JobQueue
 * @author Varun Shoor
 */
class SWIFT_JobQueueMessageRemote extends SWIFT_Library
{
    protected $_messageContainer = array();

    protected $_queueName = '';

    protected $_hasValidServer = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_queueName The Queue Name the message originated from
     * @param string $_messageBody The Packed Message Body
     * @param string $_verifyHash Whether to verify the hash of each message against the server pass key. IMPORTANT. Make sure this is done for all non master packetse
     * @throws SWIFT_JobQueue_Exception If Invalid Data is Provided
     */
    public function __construct($_queueName, $_messageBody, $_verifyHash)
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        if (!$this->SetQueue($_queueName) || !$this->DecodeMessage($_messageBody))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }


        if ($_SWIFT->Router instanceof SWIFT_Router && $_SWIFT->Router->GetApp() instanceof SWIFT_App && strtolower($_SWIFT->Router->GetApp()->GetName()) == 'cluster') {
            $this->_hasValidServer = true;
        } else {
            $class = '\SWIFT_Server';
            $_SWIFT_ServerObject = $class::GetObjectOnServerID($this->GetProperty('serverid'));
            if ($_SWIFT_ServerObject instanceof SWIFT_Library && $_SWIFT_ServerObject->GetIsClassLoaded()) {
                $this->_hasValidServer = true;
            } else {
                return;
            }
        }

        if ($_verifyHash && !$this->VerifyHash())
        {
            throw new SWIFT_JobQueue_Exception('HASH MISMATCH WITH MESSAGE PACKET!: ' . $this->GetProperty('serverid'));
        }

        $this->LoadJobQueue();
    }

    /**
     * Retrieve the server status
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHasValidServer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_hasValidServer;
    }

    /**
     * Set the Job Queue
     *
     * @author Varun Shoor
     * @param string $_queueName The Queue Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetQueue($_queueName)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_queueName)) {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_queueName = $_queueName;

        return true;
    }

    /**
     * Retrieve the currently set queue name
     *
     * @author Varun Shoor
     * @return mixed "_queueName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function GetQueue()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_queueName;
    }

    /**
     * Decode the Message
     *
     * @author Varun Shoor
     * @param string $_messageBody The Packed Message Body
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DecodeMessage($_messageBody)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_jsonData = gzuncompress(base64_decode($_messageBody));
        if (empty($_jsonData))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = json_decode($_jsonData);

        if (!isset($_JSONObject->execute, $_JSONObject->arguments, $_JSONObject->dateline))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        $_finalMessageContainer = array();

        if (NULL == $_JSONObject) {
            $_JSONObject = [];
        }
        foreach ($_JSONObject as $_key => $_val) {
            $_finalMessageContainer[$_key] = $_val;
        }

        $_finalMessageContainer['originalarguments'] = $_finalMessageContainer['arguments'];
        $_finalMessageContainer['arguments'] = mb_unserialize($_finalMessageContainer['arguments']);

//        print_r($_finalMessageContainer);
//        error_log('SWIFT: Hard exiting at ' . __METHOD__); exit;

        $this->_messageContainer = $_finalMessageContainer;

        return true;
    }

    /**
     * Retrieve a property from the message container
     *
     * @author Varun Shoor
     * @param string $_propertyName The Property Name
     * @return mixed "_messageContainer[_propertyName]" (MIXED) on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function GetProperty($_propertyName)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_messageContainer[$_propertyName])) {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_messageContainer[$_propertyName];
    }

    /**
     * Load the Job Queue for the server where message >ORIGINATED< from
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function LoadJobQueue()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_serverID = (int) ($this->GetProperty('serverid'));

        $_clusterName = 'Cluster';
        // The Job Queue is being loaded from cluster
        if ($_SWIFT->Router->GetApp()->GetName() == APP_CLUSTER)
        {
            $_SWIFT_AmazonSQSObject = new SWIFT_AmazonSQS('', '', $this->$_clusterName->GetAWSAccountNumber(), false);
            if (!$_SWIFT_AmazonSQSObject->GetIsClassLoaded())
            {
                throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
            }

            $cluster = '\SWIFT_Cluster';
            $this->Load->Library('JobQueue:JobQueue', array($_SWIFT_AmazonSQSObject, $cluster::MASTERQUEUE_SERVERPOOL, $this->$_clusterName->GetServerID(), $this->$_clusterName->GetPassKey()));

        } else {
            // Attempt to load the server object. This will always be called from master
            $amazonSQS = '\SWIFT_PrivateAmazonSQS';
            $_SWIFT_AmazonSQSObject = new $amazonSQS();
            if (!$_SWIFT_AmazonSQSObject->GetIsClassLoaded())
            {
                throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
            }

            $server = '\SWIFT_Server';
            $_SWIFT_ServerObject = $server::GetObjectOnServerID($_serverID);
            if (!$_SWIFT_ServerObject instanceof SWIFT_Library || !$_SWIFT_ServerObject->GetIsClassLoaded())
            {
                throw new SWIFT_JobQueue_Exception(SWIFT_CREATEFAILED . ": " . $_serverID);

                return false;
            }

            $_SWIFT_ServerObject_Master = $server::GetServerObjectFromType($server::TYPE_BACKENDMASTER);
            if (!$_SWIFT_ServerObject_Master instanceof SWIFT_Library || !$_SWIFT_ServerObject_Master->GetIsClassLoaded())
            {
                throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
            }

            $this->Load->Library('JobQueue:JobQueue', array($_SWIFT_AmazonSQSObject, $_SWIFT_ServerObject->GetProperty('queuename'), $_SWIFT_ServerObject_Master->GetServerID(), $_SWIFT_ServerObject_Master->GetProperty('passkey')));
        }

        if (!isset($this->JobQueue) || !$this->JobQueue instanceof SWIFT_JobQueue || !$this->JobQueue->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return true;
    }

    /**
     * Verify the hash of message
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function VerifyHash()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_serverID = (int) ($this->GetProperty('serverid'));

        // This will always fail if you attempt to verify hashes from master server
        if (empty($_serverID))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        // Attempt to get the pass key for the server.. will only work from master server!
        $server = '\SWIFT_Server';
        $_serverPassKey = $server::GetPassKey($_serverID);

        $_messageHash = $this->GetProperty('hash');
        if (empty($_messageHash))
        {
            return false;
        }

        // IMPORTANT: Hash to be calculated as: CONTROLLER PATH . SERIALIZED ARGUMENTS . DATELINE . SERVER ID
        $_hashCalculateString = $this->GetProperty('execute') . $this->GetProperty('originalarguments') . $this->GetProperty('dateline') . $_serverID;
        $_packetHash = hash_hmac('sha256', $_hashCalculateString, $_serverPassKey);

        if ($_packetHash != $_messageHash)
        {
            return false;
        }

        return true;
    }

    /**
     * Update The Job Queue Message Record
     *
     * @author Varun Shoor
     * @param int $_messageStatus The Message Status
     * @param int $_statusStage The Status Stage
     * @param string $_updateContents The Update Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_messageStatus, $_statusStage, $_updateContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_serverID = (int) ($this->GetProperty('serverid'));

        // If we are executing under cluster, we will always send back to the backend.
        if ($_SWIFT->Router->GetApp()->GetName() == APP_CLUSTER)
        {
            $_controllerPath = SWIFT_JobQueueMessage::JOBQUEUECONTROLLER_BACKEND;
        } else {
            // If this is backend, we need to find out where the request originated from.
            $server = '\SWIFT_Server';
            $_SWIFT_ServerObject = $server::GetObjectOnServerID($this->GetProperty('serverid'));
            if (!$_SWIFT_ServerObject instanceof SWIFT_Library || !$_SWIFT_ServerObject->GetIsClassLoaded())
            {
                throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
            }

            /**
             * Commented as the backend now assumes dual roles..
             */
            // If this request originated from backend, we reply back to the backend
//            if ($_SWIFT_ServerObject->GetProperty('type') == SWIFT_Server::TYPE_BACKENDMASTER)
//            {
//                $_controllerPath = SWIFT_JobQueueMessage::JOBQUEUECONTROLLER_BACKEND;
//            } else {
                $_controllerPath = SWIFT_JobQueueMessage::JOBQUEUECONTROLLER_CLUSTER;
//            }
        }

        $this->JobQueue->Dispatch($_controllerPath, $this->GetProperty('messageuuid'), $_messageStatus, $_statusStage, $_updateContents, time());

        return true;
    }

    /**
     * A wrapper to the JobQueue->Dispatch Call
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function Reply()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_functionArguments = func_get_args();
        call_user_func_array(array($this->JobQueue, 'Dispatch'), $_functionArguments);

        return true;
    }
}
