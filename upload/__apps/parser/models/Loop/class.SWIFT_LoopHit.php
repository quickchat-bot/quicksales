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
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\Loop;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Loop Hit Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_LoopHit extends SWIFT_Model
{
    const TABLE_NAME = 'parserloophits';
    const PRIMARY_KEY = 'parserloophitid';

    const TABLE_STRUCTURE = "parserloophitid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                emailaddress C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'dateline';
    const INDEX_2 = 'emailaddress';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_parserLoopHitID The Parser Loop Hit ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_parserLoopHitID)
    {
        parent::__construct();

        if (!$this->LoadData($_parserLoopHitID)) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception('Failed to load Parser Loop Hit ID: ' . $_parserLoopHitID);
            // @codeCoverageIgnoreEnd
        }
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'parserloophits', $this->GetUpdatePool(), 'UPDATE', "parserloophitid = '" .
            (int)($this->GetLoopHitID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Parser Loop Hit ID
     *
     * @author Varun Shoor
     * @return mixed "parserloophitid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLoopHitID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['parserloophitid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_parserLoopHitID The Parser Loop ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_parserLoopHitID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "parserloophits WHERE parserloophitid = '" .
            $_parserLoopHitID . "'");
        if (isset($_dataStore['parserloophitid']) && !empty($_dataStore['parserloophitid'])) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Loop Hit record
     *
     * @author Varun Shoor
     *
     * @param string $_emailAddress The Email Address
     *
     * @return mixed "_loopHitID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailAddress)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserloophits', array('emailaddress' => $_emailAddress, 'dateline' => DATENOW), 'INSERT');
        $_loopHitID = $_SWIFT->Database->Insert_ID();

        if (!$_loopHitID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_loopHitID;
    }

    /**
     * Delete the Parser Loop Hit record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetLoopHitID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Parser Loop Hits
     *
     * @author Varun Shoor
     *
     * @param array $_parserLoopHitIDList The Parser Loop Hit ID List Array
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_parserLoopHitIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserLoopHitIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserloophits WHERE parserloophitid IN (" . BuildIN($_parserLoopHitIDList) . ")");

        return true;
    }

    /**
     * Delete the loop hits which are less than the given threshold
     *
     * @author Varun Shoor
     *
     * @param int $_timeThreshold The Threshold in Seconds
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTime($_timeThreshold)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_threshold = DATENOW - $_timeThreshold;

        $_parserLoopHitIDList = array();
        $_SWIFT->Database->Query("SELECT parserloophitid FROM " . TABLE_PREFIX . "parserloophits WHERE dateline < '" . $_threshold . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_parserLoopHitIDList[] = (int)($_SWIFT->Database->Record['parserloophitid']);
        }

        if (!count($_parserLoopHitIDList)) {
            return false;
        }

        self::DeleteList($_parserLoopHitIDList);

        return true;
    }

    /**
     * Retrieve all the hits for the given email address
     *
     * @author Varun Shoor
     *
     * @param string $_emailAddress The Email Address
     *
     * @return array The dateline list
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnAddress($_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailAddress)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_datelineList = array();
        $_SWIFT->Database->Query("SELECT dateline FROM " . TABLE_PREFIX . "parserloophits WHERE emailaddress = '" .
            $_SWIFT->Database->Escape($_emailAddress) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_datelineList[] = (int)($_SWIFT->Database->Record['dateline']);
        }

        return $_datelineList;
    }
}
