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

/**
 * The Job Queue Management Class - Handles the retrieval and processing of the queues
 *
 * @author Varun Shoor
 */
class SWIFT_JobQueue extends SWIFT_Library
{

    protected $_queueName = '';
    protected $_serverPassKey = '';
    protected $_serverID = false;
    protected $AmazonSQS = false;

    // Core Constants
    const VISIBILITY_TIMEOUT = 7200; // 2 Hours

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonSQS $_SWIFT_AmazonSQSObject The SWIFT_AmazonSQS Object Pointer
     * @param string $_queueName The Queue Name
     * @param int $_serverID The Server ID
     * @param string $_serverPassKey (OPTIONAL) The Server Pass Keye
     * @throws SWIFT_JobQueue_Exception If the Class could not be loaded
     */
    public function __construct(SWIFT_AmazonSQS $_SWIFT_AmazonSQSObject, $_queueName, $_serverID, $_serverPassKey)
    {
        parent::__construct();

        if (!$this->SetQueue($_queueName) || !$this->SetServerID($_serverID) || !$this->SetServerPassKey($_serverPassKey) || !$_SWIFT_AmazonSQSObject instanceof SWIFT_AmazonSQS || !$_SWIFT_AmazonSQSObject->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->AmazonSQS = $_SWIFT_AmazonSQSObject;

        $this->Load->_Interface('JobQueue:JobQueueMessage');
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
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
        if (!$this->GetIsClassLoaded()) {
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
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_queueName;
    }

    /**
     * Set the Server ID
     *
     * @author Varun Shoor
     * @param int $_serverID The Server ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetServerID($_serverID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_serverID = $_serverID;

        return true;
    }

    /**
     * Retrieve the currently set server id
     *
     * @author Varun Shoor
     * @return mixed "_serverID" (STRING) on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function GetServerID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_serverID;
    }

    /**
     * Set the Server Pass Key
     *
     * @author Varun Shoor
     * @param string $_serverPassKey The Server Pass Key
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetServerPassKey($_serverPassKey)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_serverPassKey = $_serverPassKey;

        return true;
    }

    /**
     * Retrieve the currently set server pass key
     *
     * @author Varun Shoor
     * @return mixed "_serverPassKey" (STRING) on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function GetServerPassKey()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_serverPassKey;
    }

    /**
     * Dispatch a message into the job queue
     *
     * @author Varun Shoor
     * @param string $_controllerPath The path to controller action
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function Dispatch($_controllerPath)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_functionArguments = array($this->GetServerID(), $this->GetServerPassKey(), $_controllerPath);

        $_index = 0;
        foreach (func_get_args() as $_key => $_val) {
            if ($_index > 0) {
                $_functionArguments[] = $_val;
            }

            $_index++;
        }

        $_packetContents = call_user_func_array(array('SWIFT_JobQueueMessage', 'Create'), $_functionArguments);

        $this->AmazonSQS->SendMessage($this->GetQueue(), $_packetContents);

        return true;
    }

    /**
     * Dispatch a message into the job queue
     *
     * @author Varun Shoor
     * @param string $_controllerPath The path to controller action
     * @param array $_argumentsContainer The Arguments Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function DispatchWithArguments($_controllerPath, $_argumentsContainer = [])
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_functionArguments = array($this->GetServerID(), $this->GetServerPassKey(), $_controllerPath);
        if (_is_array($_argumentsContainer)) {
            foreach ($_argumentsContainer as $_val) {
                $_functionArguments[] = $_val;
            }
        }

        $_packetContents = call_user_func_array(array('SWIFT_JobQueueMessage', 'Create'), $_functionArguments);

        $this->AmazonSQS->SendMessage($this->GetQueue(), $_packetContents);

        return true;
    }

    /**
     * Process the Queue and call the required function with arguments
     *
     * @author Varun Shoor
     * @param string $_verifyHash Whether to verify the hash of each message against the server pass key. IMPORTANT. Make sure this is done for all non master packets
     * @param string $_controllerParentClass (OPTIONAL) Restrict the controller parent class to a specific type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded
     */
    public function Process($_verifyHash, $_controllerParentClass = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_messageContainer = $this->AmazonSQS->ReceiveMessage($this->GetQueue(), 1, 300);

        if (!_is_array($_messageContainer)) {
            return false;
        }

        foreach ($_messageContainer as $_key => $_messageVal) {
            // First delete this message from queue..
            $this->AmazonSQS->DeleteMessage($this->GetQueue(), $_messageVal['ReceiptHandle']);

            $_SWIFT_JobQueueMessageRemoteObject = new SWIFT_JobQueueMessageRemote($this->GetQueue(), $_messageVal['Body'], $_verifyHash);
            if (!$_SWIFT_JobQueueMessageRemoteObject instanceof SWIFT_JobQueueMessageRemote || !$_SWIFT_JobQueueMessageRemoteObject->GetIsClassLoaded()) {
                throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT->SetClass('JobQueueMessage', $_SWIFT_JobQueueMessageRemoteObject);

            if (!$_SWIFT_JobQueueMessageRemoteObject->GetHasValidServer()) {
                return true;
            }

            SWIFT_Router::Execute($_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute'), $_SWIFT_JobQueueMessageRemoteObject->GetProperty('arguments'), $_controllerParentClass);
        }

        return true;
    }

    /**
     * Process the Queue Messages and call the required function with arguments
     *
     * @author Varun Shoor
     * @param string $_verifyHash Whether to verify the hash of each message against the server pass key. IMPORTANT. Make sure this is done for all non master packets
     * @param string $_controllerParentClass (OPTIONAL) Restrict the controller parent class to a specific type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_JobQueue_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ProcessBulk($_verifyHash, $_controllerParentClass = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_messageContainer = $this->AmazonSQS->ReceiveMessage($this->GetQueue(), 10, 300);

        if (!_is_array($_messageContainer)) {
            return false;
        }

        $_consolePath = dirname(SWIFT_INTERFACEFILE) . '/index.php';

        foreach ($_messageContainer as $_key => $_messageVal) {
            $_jobQueueMessagePacketID = SWIFT_JobQueueMessagePacket::Create($this->GetQueue(), $_messageVal['ReceiptHandle'], $_messageVal['Body'], $_verifyHash, $_controllerParentClass);
            if (empty($_jobQueueMessagePacketID)) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_JobQueueMessagePacketObject = new SWIFT_JobQueueMessagePacket($_jobQueueMessagePacketID);
            if (!$_SWIFT_JobQueueMessagePacketObject instanceof SWIFT_JobQueueMessagePacket || !$_SWIFT_JobQueueMessagePacketObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_JobQueueMessageRemoteObject = false;
            try {
                $_SWIFT_JobQueueMessageRemoteObject = new SWIFT_JobQueueMessageRemote($_SWIFT_JobQueueMessagePacketObject->GetProperty('queuename'), $_SWIFT_JobQueueMessagePacketObject->GetProperty('messagebody'), $_SWIFT_JobQueueMessagePacketObject->GetProperty('verifyhash'));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }

            if ($_SWIFT_JobQueueMessageRemoteObject instanceof SWIFT_JobQueueMessageRemote && $_SWIFT_JobQueueMessageRemoteObject->GetIsClassLoaded()) {
                $this->Console->Message('JOB QUEUE Executing: ' . $_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute') . print_r($_SWIFT_JobQueueMessageRemoteObject->GetProperty('arguments'), true));
            }

            $_app = ucfirst($_SWIFT->Router->GetApp()->GetName());

            $_executeCommand = '/usr/local/phpcluster/bin/php -q -c /etc/php.cli.ini ' . $_consolePath . ' "/' . $_app . '/JobQueue/Execute" ' . (int) ($_jobQueueMessagePacketID);

            $_output = array();
            $_returnStatus = false;

            $this->System->Execute($_executeCommand, $_output, $_returnStatus);
            echo implode(SWIFT_CRLF, $_output);

            $_cronFileName = SWIFT::Get('cronfilename');
            if (!empty($_cronFileName) && file_exists($_cronFileName)) {
                file_put_contents($_cronFileName, implode(SWIFT_CRLF, $_output) . SWIFT_CRLF, FILE_APPEND);
            }
        }

        return true;
    }

    /**
     * Execute a Job Queue Message Packet
     *
     * @author Varun Shoor
     * @param SWIFT_JobQueueMessagePacket $_SWIFT_JobQueueMessagePacketObject The Job Queue Message Packet Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Execute(SWIFT_JobQueueMessagePacket $_SWIFT_JobQueueMessagePacketObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_JobQueueMessagePacketObject instanceof SWIFT_JobQueueMessagePacket || !$_SWIFT_JobQueueMessagePacketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->AmazonSQS->DeleteMessage($_SWIFT_JobQueueMessagePacketObject->GetProperty('queuename'), $_SWIFT_JobQueueMessagePacketObject->GetProperty('receipthandle'));

        $_SWIFT_JobQueueMessageRemoteObject = new SWIFT_JobQueueMessageRemote($_SWIFT_JobQueueMessagePacketObject->GetProperty('queuename'), $_SWIFT_JobQueueMessagePacketObject->GetProperty('messagebody'), $_SWIFT_JobQueueMessagePacketObject->GetProperty('verifyhash'));
        if (!$_SWIFT_JobQueueMessageRemoteObject instanceof SWIFT_JobQueueMessageRemote || !$_SWIFT_JobQueueMessageRemoteObject->GetIsClassLoaded()) {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        if (!strstr($_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute'), 'HealthCheck') && !strstr($_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute'), 'UpdateLog')
                && !strstr($_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute'), 'UpdateProgress') && !strstr($_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute'), 'UpdateStatus')) {
            $this->Log('Executing: ' . $_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute') . SWIFT_CRLF . print_r($_SWIFT_JobQueueMessageRemoteObject->GetProperty('arguments'), true), SWIFT_Log::TYPE_OK, 'JOBQUEUE');
        } else {
            $this->Log('Executing: ' . $_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute'), SWIFT_Log::TYPE_OK, 'JOBQUEUE');
        }

        // We do not acknowledge the job queue status updates.. and only the cluster is supposed to acknowledge message receipts (Saves up costs and resources).
        if ($_SWIFT_JobQueueMessageRemoteObject->GetHasValidServer() && $_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute') != '/Cluster/JobQueue/Update' && $_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute') != '/Backend/JobQueue/Update') {
//            $_SWIFT_JobQueueMessageRemoteObject->Update(SWIFT_JobQueueMessage::STATUS_RECEIVED, 0, 'Received Message. Executing Controller..');
        }

        $_SWIFT->SetClass('JobQueueMessage', $_SWIFT_JobQueueMessageRemoteObject);

//        print_r($_SWIFT_JobQueueMessageRemoteObject->GetProperty('arguments'));

        if ($_SWIFT_JobQueueMessageRemoteObject->GetHasValidServer()) {
            SWIFT_Router::Execute($_SWIFT_JobQueueMessageRemoteObject->GetProperty('execute'), $_SWIFT_JobQueueMessageRemoteObject->GetProperty('arguments'), $_SWIFT_JobQueueMessagePacketObject->GetProperty('controllerparentclass'));
        }

        // Delete the packet
        $_SWIFT_JobQueueMessagePacketObject->Delete();

        return true;
    }

}

?>
