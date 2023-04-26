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
 * @copyright      Copyright (c) 2001-2012, Kayako
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
 * The Parser Loop Block Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_LoopBlock extends SWIFT_Model
{
    const TABLE_NAME = 'parserloopblocks';
    const PRIMARY_KEY = 'parserloopblockid';

    const TABLE_STRUCTURE = "parserloopblockid I PRIMARY AUTO NOTNULL,
                                restoretime I DEFAULT '0' NOTNULL,
                                address C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'address, restoretime';
    const INDEX_2 = 'restoretime';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_parserLoopBlockID The Parser Loop Block ID
     *
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception If the Record could not be loaded
     */
    public function __construct($_parserLoopBlockID)
    {
        parent::__construct();

        if (!$this->LoadData($_parserLoopBlockID)) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Loop_Exception('Failed to load Parser Loop Block ID: ' . $_parserLoopBlockID);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     *
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception
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
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'parserloopblocks', $this->GetUpdatePool(), 'UPDATE', "parserloopblockid = '" .
            (int)($this->GetLoopBlockID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Loop Block ID
     *
     * @author Varun Shoor
     * @return mixed "parserloopblockid" on Success, "false" otherwise
     * @throws \Parser\Models\Loop\SWIFT_Loop_Exception If the Class is not Loaded
     */
    public function GetLoopBlockID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Loop_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['parserloopblockid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_parserLoopBlockID The Parser Loop Block ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_parserLoopBlockID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "parserloopblocks WHERE parserloopblockid = '" .
            $_parserLoopBlockID . "'");
        if (isset($_dataStore['parserloopblockid']) && !empty($_dataStore['parserloopblockid'])) {
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
     * @throws \Parser\Models\Loop\SWIFT_Loop_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Loop_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws \Parser\Models\Loop\SWIFT_Loop_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Loop_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Loop_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Loop Block. Blocks an address for a given timeframe.
     *
     * @author Varun Shoor
     *
     * @param string $_address The Email Address to Block
     * @param int $_blockLengthInSeconds The Length of Time until the Block is Removed
     *
     * @return void "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_address, $_blockLengthInSeconds)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_address)) {
            throw new SWIFT_Loop_Exception(SWIFT_INVALIDDATA);
        }

        $_blockLengthInSeconds = $_blockLengthInSeconds;

        $_restoreBy = $_blockLengthInSeconds + DATENOW;
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserloopblocks', array('address' => $_address, 'restoretime' => $_restoreBy),
            'INSERT');
        $_parserLoopBlockID = $_SWIFT->Database->Insert_ID();

        if (!$_parserLoopBlockID) {
            throw new SWIFT_Loop_Exception(SWIFT_CREATEFAILED);
        }
    }

    /**
     * Delete the Loop Block record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Loop_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetLoopBlockID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Check if the address is blocked by loop
     *
     * @author Varun Shoor
     *
     * @param string $_emailAddress The Email Address
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CheckIfAddressIsBlocked($_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailAddress)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_loopBlockContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "parserloopblocks WHERE
            address = '" . $_SWIFT->Database->Escape($_emailAddress) . "' AND restoretime > '" . DATENOW . "'");
        if (isset($_loopBlockContainer['totalitems']) && $_loopBlockContainer['totalitems'] > 0) {
            return true;
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Delete a list of Parser Loop Blocks
     *
     * @author Varun Shoor
     *
     * @param array $_parserLoopBlockIDList The Parser Loop Block ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_parserLoopBlockIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserLoopBlockIDList)) {
            return false;
        }

        $_finalText = '';

        $_finalParserLoopBlockIDList = array();
        $_index = 1;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserloopblocks WHERE parserloopblockid IN (" .
            BuildIN($_parserLoopBlockIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalParserLoopBlockIDList[] = $_SWIFT->Database->Record['parserloopblockid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['address']) . '<br />';

            $_index++;
        }

        if (!count($_finalParserLoopBlockIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelloopblock'), count($_finalParserLoopBlockIDList)),
            $_SWIFT->Language->Get('msgdelloopblock') . '<br />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserloopblocks WHERE parserloopblockid IN (" .
            BuildIN($_finalParserLoopBlockIDList) . ")");

        return true;
    }

    /**
     * Delete all Obsolete Loop Blocks
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteObsolete()
    {
        $_SWIFT = SWIFT::GetInstance();

        /*
         * @todo Add this function to a recurring cron
         */

        $_parserLoopBlockIDList = array();

        $_SWIFT->Database->Query("SELECT parserloopblockid FROM " . TABLE_PREFIX . "parserloopblocks WHERE restoretime < '" . DATENOW . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_parserLoopBlockIDList[] = $_SWIFT->Database->Record['parserloopblockid'];
        }

        if (!count($_parserLoopBlockIDList)) {
            return false;
        }

        self::DeleteList($_parserLoopBlockIDList);

        return true;
    }

    /**
     * Delete the Loop Blocks on a List of Addresses
     *
     * @author Varun Shoor
     *
     * @param array $_addressList The Email Address List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnAddressList($_addressList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_addressList)) {
            return false;
        }

        $_parserLoopBlockIDList = array();

        $_SWIFT->Database->Query("SELECT parserloopblockid FROM " . TABLE_PREFIX . "parserloopblocks WHERE address IN (" . BuildIN($_addressList) .
            ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_parserLoopBlockIDList[] = $_SWIFT->Database->Record['parserloopblockid'];
        }

        if (!count($_parserLoopBlockIDList)) {
            return false;
        }

        self::DeleteList($_parserLoopBlockIDList);

        return true;
    }
}
