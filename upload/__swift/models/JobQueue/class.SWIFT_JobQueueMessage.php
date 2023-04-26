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
 * Job Queue Message Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_JobQueueMessage extends SWIFT_Model
{
    const TABLE_NAME        =    'jobqueuemessages';
    const PRIMARY_KEY        =    'jobqueuemessageid';

    const TABLE_STRUCTURE    =    "jobqueuemessageid I PRIMARY AUTO NOTNULL,
                                messageuuid C(100) DEFAULT '' NOTNULL,
                                serverid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastupdate I DEFAULT '0' NOTNULL,
                                messagestatus I2 DEFAULT '0' NOTNULL,
                                statusstage I2 DEFAULT '0' NOTNULL,
                                executionpath C(255) DEFAULT '' NOTNULL,
                                contents XL NOTNULL";

    const INDEX_1            =    'serverid';
    const INDEX_2            =    'messageuuid';
    const INDEX_3            =    'lastupdate';
    const INDEX_4            =    'dateline';


    protected $_dataStore = array();

    // Core Constants
    const STATUS_DISPATCHED = 1;
    const STATUS_RECEIVED = 2;
    const STATUS_INPROGRESS = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_FAILED = 5;
    const STATUS_TIMEDOUT = 6;
    const STATUS_SIZELIMIT = 7;

    // Controller Constants
    const JOBQUEUECONTROLLER_BACKEND = '/Backend/JobQueue/Update';
    const JOBQUEUECONTROLLER_CLUSTER = '/Cluster/JobQueue/Update';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_jobQueueMessageID The Job Queue Message ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_jobQueueMessageID)
    {
        parent::__construct();

        if (!$this->LoadData($_jobQueueMessageID)) {
            throw new SWIFT_JobQueue_Exception('Failed to load Job Queue Message ID: ' . ($_jobQueueMessageID));

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

        $this->Database->AutoExecute(TABLE_PREFIX . 'jobqueuemessages', $this->GetUpdatePool(), 'UPDATE', "jobqueuemessageid = '" . ($this->GetJobQueueMessageID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Job Queue Message ID
     *
     * @author Varun Shoor
     * @return mixed "jobqueuemessageid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJobQueueMessageID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['jobqueuemessageid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_jobQueueMessageID The Job Queue Message ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_jobQueueMessageID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "jobqueuemessages WHERE jobqueuemessageid = '" . ($_jobQueueMessageID) . "'");
        if (isset($_dataStore['jobqueuemessageid']) && !empty($_dataStore['jobqueuemessageid']))
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
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid message status
     *
     * @author Varun Shoor
     * @param mixed $_messageStatus The Message Status
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidStatus($_messageStatus)
    {
        if ($_messageStatus == self::STATUS_DISPATCHED || $_messageStatus == self::STATUS_RECEIVED || $_messageStatus == self::STATUS_INPROGRESS || $_messageStatus == self::STATUS_COMPLETED || $_messageStatus == self::STATUS_FAILED || $_messageStatus == self::STATUS_TIMEDOUT || $_messageStatus == self::STATUS_SIZELIMIT)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Job Queue Message
     *
     * @author Varun Shoor
     * @param int $_serverID The Server ID
     * @param string $_serverPassKey The Server Pass Key
     * @param string $_controllerPath The Controller Path to Execute
     * @return mixed "_packetContents" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_serverID, $_serverPassKey, $_controllerPath)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_controllerPath))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        $_functionArguments = array();
        $_index = 0;
        foreach (func_get_args() as $_key => $_val)
        {
            if ($_index > 2)
            {
                $_functionArguments[] = $_val;
            }

            $_index++;
        }

        // We always mark the job queue update messages as completed.
        $_messageStatus = self::STATUS_DISPATCHED;
        if (StripTrailingSlash(strtolower($_controllerPath)) == strtolower(self::JOBQUEUECONTROLLER_BACKEND) || StripTrailingSlash(strtolower($_controllerPath)) == strtolower(self::JOBQUEUECONTROLLER_CLUSTER))
        {
            $_messageStatus = self::STATUS_COMPLETED;
        }

        $_messageUUID = GenerateUUID();

        // IMPORTANT: Hash the data so that its integrity can be verified at the master server. Hash to be calculated as: CONTROLLER PATH . SERIALIZED ARGUMENTS . DATELINE . SERVER ID
        $_serializedArguments = serialize($_functionArguments);
        $_hashCalculateString = $_controllerPath . $_serializedArguments . DATENOW . ($_serverID);
        $_packetHash = hash_hmac('sha256', $_hashCalculateString, $_serverPassKey);

        // This is for the database only
        $_databaseContainer = array();
        $_databaseContainer['execute'] = $_controllerPath;
        $_databaseContainer['arguments'] = $_functionArguments;
        $_databaseContainer['dateline'] = DATENOW;
        $_databaseContainer['serverid'] = ($_serverID);
        $_databaseContainer['hash'] = $_packetHash;

        $_SWIFT_JobQueueMessageObject = false;

        try {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'jobqueuemessages', array('messageuuid' => $_messageUUID, 'serverid' => ($_serverID), 'dateline' => DATENOW,
                'messagestatus' => $_messageStatus, 'statusstage' => 0, 'contents' => serialize($_databaseContainer), 'executionpath' => $_controllerPath, 'lastupdate' => DATENOW), 'INSERT');
            $_jobQueueMessageID = $_SWIFT->Database->Insert_ID();

            if (!$_jobQueueMessageID)
            {
                throw new SWIFT_JobQueue_Exception(SWIFT_CREATEFAILED);
            }

            $_SWIFT_JobQueueMessageObject = new SWIFT_JobQueueMessage($_jobQueueMessageID);
            if (!$_SWIFT_JobQueueMessageObject->GetIsClassLoaded())
            {
                throw new SWIFT_JobQueue_Exception(SWIFT_CREATEFAILED);
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

        }

        // Prepare the Container Array for Dispatch
        $_dispatchContainer = array();
        $_dispatchContainer['messageuuid'] = $_messageUUID;
        $_dispatchContainer['execute'] = $_controllerPath;
        $_dispatchContainer['arguments'] = $_serializedArguments;
        $_dispatchContainer['dateline'] = DATENOW;
        $_dispatchContainer['serverid'] = ($_serverID);
        $_dispatchContainer['hash'] = $_packetHash;

        $_jsonData = json_encode($_dispatchContainer);

        $_packetContents = base64_encode(gzcompress($_jsonData, 9));

        $_packetLength = strlen($_packetContents)/1024; // In KB
        if ($_packetLength > 65 && $_SWIFT_JobQueueMessageObject instanceof SWIFT_JobQueueMessage && $_SWIFT_JobQueueMessageObject->GetIsClassLoaded())
        {
            $_SWIFT_JobQueueMessageObject->Update(self::STATUS_SIZELIMIT);

            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        return $_packetContents;
    }

    /**
     * Update The Job Queue Message Record
     *
     * @author Varun Shoor
     * @param int $_messageStatus The Message Status
     * @param int $_statusStage The Status Stage
     * @param string $_updateContents The Update Contents
     * @param int $_serverDate The Server Date
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_messageStatus, $_statusStage = 0, $_updateContents = '', $_serverDate = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidStatus($_messageStatus)) {
            throw new SWIFT_JobQueue_Exception('3' . SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('lastupdate', DATENOW);

        // We only update local state if message is not marked as completed. Due to nature of SQS, there will be instances wherein we might receive the completed status before other status'es
        if ($this->GetProperty('messagestatus') <= $_messageStatus)
        {
            $this->UpdatePool('messagestatus', ($_messageStatus));
            $this->UpdatePool('statusstage', ($_statusStage));
            $this->ProcessUpdatePool();
        }

        // But we always log this event..
        $_SWIFT_JobQueueMessageLogID = SWIFT_JobQueueMessageLog::Create($this->GetJobQueueMessageID(), $_messageStatus, $_statusStage, $_updateContents, $_serverDate);

        return true;
    }

    /**
     * Delete the Job Queue Message record
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
        }

        self::DeleteList(array($this->GetJobQueueMessageID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Job Queues
     *
     * @author Varun Shoor
     * @param array $_jobQueueMessageIDList The Job Queue Message ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_jobQueueMessageIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_jobQueueMessageIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "jobqueuemessages WHERE jobqueuemessageid IN (" . BuildIN($_jobQueueMessageIDList) . ")");

        return true;
    }

    /**
     * We cleanup all records older than 1 hour
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Cleanup() {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - 3600;

        $_jobQueueMessageIDList = array();

        $_SWIFT->Database->Query("SELECT jobqueuemessageid FROM " . TABLE_PREFIX . "jobqueuemessages WHERE dateline <= '" . ($_dateThreshold) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_jobQueueMessageIDList[] = $_SWIFT->Database->Record['jobqueuemessageid'];
        }

        if (!count($_jobQueueMessageIDList)) {
            return false;
        }

        self::DeleteList($_jobQueueMessageIDList);

        return true;
    }

    /**
     * Retrieve the Job Queue Message Object on the Message UUID
     *
     * @author Varun Shoor
     * @param string $_messageUUID The Message UUID
     * @return SWIFT_JobQueueMessage
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnUUID($_messageUUID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_messageUUID))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        $_jobQueueMessageContainer = $_SWIFT->Database->QueryFetch("SELECT jobqueuemessageid FROM " . TABLE_PREFIX . "jobqueuemessages WHERE messageuuid = '" . $_SWIFT->Database->Escape($_messageUUID) . "'");
        if (!$_jobQueueMessageContainer || !isset($_jobQueueMessageContainer['jobqueuemessageid']))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        return new SWIFT_JobQueueMessage($_jobQueueMessageContainer['jobqueuemessageid']);
    }

    /**
     * Retrieve the appropriate label for the message status
     *
     * @author Varun Shoor
     * @param mixed $_messageStatus The Message Status
     * @param int|bool $_statusStage (OPTIONAL) The Current Status Stage
     * @return mixed "Message Status Label" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetMessageStatusLabel($_messageStatus, $_statusStage = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidStatus($_messageStatus))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);
        }

        $_stageSuffix = IIF(!empty($_statusStage), ' (' . ($_statusStage) . ')', '');

        switch ($_messageStatus)
        {
            case self::STATUS_DISPATCHED:
                return $_SWIFT->Language->Get('messagestatus_dispatched') . $_stageSuffix;
                break;

            case self::STATUS_RECEIVED:
                return $_SWIFT->Language->Get('messagestatus_received') . $_stageSuffix;
                break;

            case self::STATUS_INPROGRESS:
                return $_SWIFT->Language->Get('messagestatus_inprogress') . $_stageSuffix;
                break;

            case self::STATUS_COMPLETED:
                return $_SWIFT->Language->Get('messagestatus_completed') . $_stageSuffix;
                break;

            case self::STATUS_FAILED:
                return $_SWIFT->Language->Get('messagestatus_failed') . $_stageSuffix;
                break;

            case self::STATUS_TIMEDOUT:
                return $_SWIFT->Language->Get('messagestatus_timedout') . $_stageSuffix;
                break;

            case self::STATUS_SIZELIMIT:
                return $_SWIFT->Language->Get('messagestatus_sizelimit') . $_stageSuffix;
                break;

            default:
                break;
        }

        return false;
    }
}
