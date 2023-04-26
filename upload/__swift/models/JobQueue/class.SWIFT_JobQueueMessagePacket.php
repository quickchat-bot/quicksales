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

/**
 * The Job Queue Message Packet Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_JobQueueMessagePacket extends SWIFT_Model
{
    const TABLE_NAME        =    'jobqueuemessagepackets';
    const PRIMARY_KEY        =    'jobqueuemessagepacketid';

    const TABLE_STRUCTURE    =    "jobqueuemessagepacketid I PRIMARY AUTO NOTNULL,
                                queuename C(255) DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                receipthandle X NOTNULL,
                                messagebody XL NOTNULL,
                                verifyhash I2 DEFAULT '0' NOTNULL,
                                controllerparentclass C(255) DEFAULT '' NOTNULL";

    const INDEX_1            =    'queuename';
    const INDEX_2            =    'dateline';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_jobQueueMessagePacketID The Job Queue Message Packet ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_jobQueueMessagePacketID)
    {
        parent::__construct();

        if (!$this->LoadData($_jobQueueMessagePacketID)) {
            throw new SWIFT_JobQueue_Exception('Failed to load Job Queue Message Packet ID: ' . ($_jobQueueMessagePacketID));

            $this->SetIsClassLoaded(false);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'jobqueuemessagepackets', $this->GetUpdatePool(), 'UPDATE', "jobqueuemessagepacketid = '" . ($this->GetJobQueueMessagePacketID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Job Queue Message Packet ID
     *
     * @author Varun Shoor
     * @return mixed "jobqueuemessagepacketid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJobQueueMessagePacketID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['jobqueuemessagepacketid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_jobQueueMessagePacketID The Job Queue Message Packet ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_jobQueueMessagePacketID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "jobqueuemessagepackets WHERE jobqueuemessagepacketid = '" . ($_jobQueueMessagePacketID) . "'");
        if (isset($_dataStore['jobqueuemessagepacketid']) && !empty($_dataStore['jobqueuemessagepacketid']))
        {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Job Queue Message Packet
     *
     * @author Varun Shoor
     * @param string $_queueName The Queue Name
     * @param string $_receiptHandle The Receipt Handle
     * @param string $_messageBody The Message Body
     * @param bool $_verifyHash Whether to verify the hash
     * @param string $_controllerParentClass The Parent Class to restrict execution to
     * @return mixed "_jobQueueMessagePacketID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_queueName, $_receiptHandle, $_messageBody, $_verifyHash, $_controllerParentClass)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_queueName) || empty($_receiptHandle) || empty($_messageBody))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'jobqueuemessagepackets', array('queuename' => $_queueName, 'receipthandle' => $_receiptHandle, 'dateline' => DATENOW, 'messagebody' => $_messageBody, 'verifyhash' => ($_verifyHash), 'controllerparentclass' => $_controllerParentClass), 'INSERT');
        $_jobQueueMessagePacketID  = $_SWIFT->Database->Insert_ID();

        if (!$_jobQueueMessagePacketID)
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        return $_jobQueueMessagePacketID;
    }

    /**
     * Delete the Job Queue Message Packet record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($this->GetJobQueueMessagePacketID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Job Queue Message Packet IDs
     *
     * @author Varun Shoor
     * @param array $_jobQueueMessagePacketIDList The Job Queue Message Packet ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_jobQueueMessagePacketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_jobQueueMessagePacketIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "jobqueuemessagepackets WHERE jobqueuemessagepacketid IN (" . BuildIN($_jobQueueMessagePacketIDList) . ")");

        return true;
    }

    /**
     * We cleanup all records older than 1 day
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Cleanup() {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - 86400;

        $_jobQueueMessagePacketIDList = array();

        $_SWIFT->Database->Query("SELECT jobqueuemessagepacketid FROM " . TABLE_PREFIX . "jobqueuemessagepackets WHERE dateline <= '" . ($_dateThreshold) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_jobQueueMessagePacketIDList[] = $_SWIFT->Database->Record['jobqueuemessagepacketid'];
        }

        if (!count($_jobQueueMessagePacketIDList)) {
            return false;
        }

        self::DeleteList($_jobQueueMessagePacketIDList);

        return true;
    }
}
