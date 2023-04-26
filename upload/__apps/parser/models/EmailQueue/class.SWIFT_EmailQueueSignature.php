<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Models\EmailQueue;

use SWIFT;
use SWIFT_Model;

/**
 * The Email Queue Signature Class
 *
 * @author Varun Shoor
 */
class SWIFT_EmailQueueSignature extends SWIFT_Model
{
    const TABLE_NAME = 'queuesignatures';
    const PRIMARY_KEY = 'queuesignatureid';

    const TABLE_STRUCTURE = "queuesignatureid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                emailqueueid I DEFAULT '0' NOTNULL,
                                contents X2";

    const INDEX_1 = 'emailqueueid';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_emailQueueID The Email Queue ID
     *
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Record could not be loaded
     * @throws \SWIFT_Exception
     */
    public function __construct($_emailQueueID)
    {
        parent::__construct();

        // @codeCoverageIgnoreStart
        if (!$this->LoadData($_emailQueueID)) {
            throw new SWIFT_EmailQueue_Exception('Failed to load Email Queue Signature based on Email Queue ID: ' . $_emailQueueID);
        }
        // @codeCoverageIgnoreEnd
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
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded
     * @throws \SWIFT_Exception
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'queuesignatures', $this->GetUpdatePool(), 'UPDATE', "queuesignatureid = '" .
            (int)($this->GetEmailQueueSignatureID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Email Queue Signature ID
     *
     * @author Varun Shoor
     * @return mixed "queuesignatureid" on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetEmailQueueSignatureID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['queuesignatureid'];
    }

    /**
     * Retrieves the Email Queue ID
     *
     * @author Varun Shoor
     * @return mixed "queuesignatureid" on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetEmailQueueID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['emailqueueid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_emailQueueID The Email Queue ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_emailQueueID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "queuesignatures WHERE emailqueueid = '" .
            $_emailQueueID . "'");
        if (isset($_dataStore['queuesignatureid']) && !empty($_dataStore['queuesignatureid']) && $_dataStore['emailqueueid'] == $_emailQueueID) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     *
     * @param string $_key The Key Identifier
     *
     * @return mixed Property Data on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded or If Invalid Data is PRovided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Email Queue Signature
     *
     * @author Varun Shoor
     *
     * @param int $_emailQueueID The Email Queue ID
     * @param string $_queueSignature
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception
     * @throws \SWIFT_Exception
     */
    public static function Create($_emailQueueID, $_queueSignature)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailQueueID)) {
            throw new SWIFT_EmailQueue_Exception('Invalid Email Queue ID Specified');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'queuesignatures', array('dateline' => DATENOW, 'emailqueueid' => $_emailQueueID,
            'contents' => $_queueSignature), 'INSERT');

        return true;
    }

    /**
     * Update the Queue Signature Record
     *
     * @author Varun Shoor
     *
     * @param string $_queueSignature The Email Queue Signature Text
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded or if Invalid Data is Specified
     * @throws \SWIFT_Exception
     */
    public function Update($_queueSignature)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'queuesignatures', array('dateline' => DATENOW, 'contents' => $_queueSignature), 'UPDATE',
            "emailqueueid = '" . (int)($this->GetEmailQueueID()) . "'");

        return true;
    }

    /**
     * Delete the Queue Signature record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     * @throws \SWIFT_Exception
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetEmailQueueSignatureID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Email Queue Signatures
     *
     * @author Varun Shoor
     *
     * @param array $_emailQueueSignatureIDList The Email Queue Signature ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public static function DeleteList($_emailQueueSignatureIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailQueueSignatureIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "queuesignatures WHERE queuesignatureid IN (" .
            BuildIN($_emailQueueSignatureIDList) . ")");

        return true;
    }

    /**
     * Delete on Email Queue ID
     *
     * @author Varun Shoor
     *
     * @param array $_emailQueueIDList The Email Queue ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws \SWIFT_Exception
     */
    public static function DeleteOnEmailQueue($_emailQueueIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailQueueIDList)) {
            return false;
        }

        $_emailQueueSignatureIDList = array();

        $_SWIFT->Database->Query("SELECT queuesignatureid FROM " . TABLE_PREFIX . "queuesignatures WHERE emailqueueid IN (" .
            BuildIN($_emailQueueIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_emailQueueSignatureIDList[] = $_SWIFT->Database->Record['queuesignatureid'];
        }

        // @codeCoverageIgnoreStart
        if (!count($_emailQueueSignatureIDList)) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        self::DeleteList($_emailQueueSignatureIDList);

        return true;
    }
}
