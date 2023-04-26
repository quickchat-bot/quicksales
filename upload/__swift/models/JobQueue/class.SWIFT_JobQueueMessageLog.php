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
 * The Job Queue Message Log Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_JobQueueMessageLog extends SWIFT_Model
{
    const TABLE_NAME        =    'jobqueuemessagelogs';
    const PRIMARY_KEY        =    'jobqueuemessagelogid';

    const TABLE_STRUCTURE    =    "jobqueuemessagelogid I PRIMARY AUTO NOTNULL,
                                jobqueuemessageid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                messagestatus I2 DEFAULT '0' NOTNULL,
                                statusstage I2 DEFAULT '0' NOTNULL,
                                updatecontents X NOTNULL";

    const INDEX_1            =    'jobqueuemessageid';
    const INDEX_2            =    'dateline';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_jobQueueMessageLogID The Job Queue Message Log ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_jobQueueMessageLogID)
    {
        parent::__construct();

        if (!$this->LoadData($_jobQueueMessageLogID)) {
            throw new SWIFT_JobQueue_Exception('Failed to load Job Queue Message Log ID: ' . ($_jobQueueMessageLogID));
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'jobqueuemessagelogs', $this->GetUpdatePool(), 'UPDATE', "jobqueuemessagelogid = '" . ($this->GetJobQueueMessageLogID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Job Queue Message Log ID
     *
     * @author Varun Shoor
     * @return mixed "jobqueuemessagelogid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetJobQueueMessageLogID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['jobqueuemessagelogid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_jobQueueMessageLogID The Job Queue Message Log ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_jobQueueMessageLogID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "jobqueuemessagelogs WHERE jobqueuemessagelogid = '" . ($_jobQueueMessageLogID) . "'");
        if (isset($_dataStore['jobqueuemessagelogid']) && !empty($_dataStore['jobqueuemessagelogid']))
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
     * Create a new Job Queue Message Log Entry
     *
     * @author Varun Shoor
     * @param int $_jobQueueMessageID The Job Queue Message ID
     * @param mixed $_messageStatus The Message Status
     * @param int $_statusStage The stage at which it is in
     * @param string $_updateContents The Update Contents Text (Some debug information)
     * @param int $_serverDate The Server Date
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_jobQueueMessageID, $_messageStatus, $_statusStage, $_updateContents, $_serverDate = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_jobQueueMessageID) || empty($_messageStatus))
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if (empty($_serverDate))
        {
            $_serverDate = DATENOW;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'jobqueuemessagelogs', array('jobqueuemessageid' => ($_jobQueueMessageID), 'messagestatus' => ($_messageStatus),
            'statusstage' => ($_statusStage), 'updatecontents' => $_updateContents, 'dateline' => ($_serverDate)), 'INSERT');
        $_jobQueueMessageLogID = $_SWIFT->Database->Insert_ID();

        if (!$_jobQueueMessageLogID)
        {
            throw new SWIFT_JobQueue_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        return $_jobQueueMessageLogID;
    }

    /**
     * Delete the Job Queue Message log record
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

        self::DeleteList(array($this->GetJobQueueMessageLogID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Job Queue Message Logs
     *
     * @author Varun Shoor
     * @param array $_jobQueueMessageLogIDList The Job Queue Message Log ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_jobQueueMessageLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_jobQueueMessageLogIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "jobqueuemessagelogs WHERE jobqueuemessagelogid IN (" . BuildIN($_jobQueueMessageLogIDList) . ")");

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

        $_jobQueueMessageLogIDList = array();

        $_SWIFT->Database->Query("SELECT jobqueuemessagelogid FROM " . TABLE_PREFIX . "jobqueuemessagelogs WHERE dateline <= '" . ($_dateThreshold) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_jobQueueMessageLogIDList[] = $_SWIFT->Database->Record['jobqueuemessagelogid'];
        }

        if (!count($_jobQueueMessageLogIDList)) {
            return false;
        }

        self::DeleteList($_jobQueueMessageLogIDList);

        return true;
    }
}
?>
