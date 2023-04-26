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

namespace Parser\Models\Loop;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Parser Loop Rule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_LoopRule extends SWIFT_Model
{
    const TABLE_NAME = 'parserlooprules';
    const PRIMARY_KEY = 'parserloopruleid';

    const TABLE_STRUCTURE = "parserloopruleid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                length I DEFAULT '0' NOTNULL,
                                maxhits I DEFAULT '0' NOTNULL,
                                restoreafter I DEFAULT '0' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL";


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_parserLoopRuleID The Parser Loop Rule ID
     *
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception If the Record could not be loaded
     */
    public function __construct($_parserLoopRuleID)
    {
        parent::__construct();

        if (!$this->LoadData($_parserLoopRuleID)) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Loop_Exception('Failed to load Parser Loop Rule ID: ' . $_parserLoopRuleID);
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

        $this->Database->AutoExecute(TABLE_PREFIX . 'parserlooprules', $this->GetUpdatePool(), 'UPDATE', "parserloopruleid = '" .
            (int)($this->GetLoopRuleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Parser Loop Rule ID
     *
     * @author Varun Shoor
     * @return mixed "parserloopruleid" on Success, "false" otherwise
     * @throws \Parser\Models\Loop\SWIFT_Loop_Exception If the Class is not Loaded
     */
    public function GetLoopRuleID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Loop_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['parserloopruleid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_parserLoopRuleID The Parser Loop Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_parserLoopRuleID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "parserlooprules WHERE parserloopruleid = '" .
            $_parserLoopRuleID . "'");
        if (isset($_dataStore['parserloopruleid']) && !empty($_dataStore['parserloopruleid'])) {
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
     * Create a new Parser Loop Rule
     *
     * @author Varun Shoor
     *
     * @param string $_title The Loop Rule Title
     * @param int $_timeFrame the Time Frame to Check
     * @param int $_maxHits The Maximum Hits till this Rule is Triggered
     * @param int $_restoreAfter The Time after which this loop rule is to be restored
     * @param bool $_isMaster Whether this is a master rule (which cannot be deleted)
     *
     * @return bool "_parserLoopRuleID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_title, $_timeFrame, $_maxHits, $_restoreAfter, $_isMaster = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || empty($_timeFrame) || empty($_maxHits) || empty($_restoreAfter)) {
            throw new SWIFT_Loop_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserlooprules', array('title' => $_title, 'length' => $_timeFrame,
            'maxhits' => $_maxHits, 'restoreafter' => $_restoreAfter, 'ismaster' => (int)($_isMaster)), 'INSERT');
        $_parserLoopRuleID = $_SWIFT->Database->Insert_ID();
        if (!$_parserLoopRuleID) {
            throw new SWIFT_Loop_Exception(SWIFT_CREATEFAILED);
        }

        return $_parserLoopRuleID;
    }

    /**
     * Update the Parser Loop Rule Record
     *
     * @author Varun Shoor
     *
     * @param string $_title The Loop Rule Title
     * @param int $_timeFrame the Time Frame to Check
     * @param int $_maxHits The Maximum Hits till this Rule is Triggered
     * @param int $_restoreAfter The Time after which this loop rule is to be restored
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Loop_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_timeFrame, $_maxHits, $_restoreAfter)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Loop_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || empty($_timeFrame) || empty($_maxHits) || empty($_restoreAfter)) {
            throw new SWIFT_Loop_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('length', $_timeFrame);
        $this->UpdatePool('maxhits', $_maxHits);
        $this->UpdatePool('restoreafter', $_restoreAfter);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Loop Rule record
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

        self::DeleteList(array($this->GetLoopRuleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Loop Rule's
     *
     * @author Varun Shoor
     *
     * @param array $_parserLoopRuleIDList The Parser Loop Rule ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_parserLoopRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserLoopRuleIDList)) {
            return false;
        }

        $_finalParserLoopRuleIDList = array();

        $_finalText = '';
        $_index = 1;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserlooprules WHERE parserloopruleid IN (" .
            BuildIN($_parserLoopRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalParserLoopRuleIDList[] = $_SWIFT->Database->Record['parserloopruleid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';
            $_index++;
        }

        if (!count($_finalParserLoopRuleIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledellooprule'), count($_finalParserLoopRuleIDList)),
            $_SWIFT->Language->Get('msgdellooprule') . '<br />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserlooprules WHERE parserloopruleid IN (" .
            BuildIN($_finalParserLoopRuleIDList) . ")");

        return true;
    }

    /**
     * Digs through user preferences to find the user's maximum checked loop tolerance.  Directly SQL dependant.
     *
     * @author Varun Shoor
     * @return int The Max Age
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetMaxContactAge()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_loopRuleContainer = $_SWIFT->Database->QueryFetch("SELECT length FROM " . TABLE_PREFIX . "parserlooprules ORDER BY length DESC");
        if (!isset($_loopRuleContainer['length'])) {
            return 600;
        }

        // @codeCoverageIgnoreStart
        return (int)($_loopRuleContainer['length']);
        // @codeCoverageIgnoreEnd
    }

    /**
     *
     *  Gets the user-configurable threshholds for email loop tolerance as an array of array(length:seconds, maxhits:count, restoreafter:seconds).
     *  All returned parameters are unsigned integers.  Directly SQL dependant.
     *
     * @author    John Haugeland
     * @since     0.1.1
     * @version   4
     * @access    public
     *
     * @return    array( array('length'=>seconds:unsigned int, 'maxhits'=>count:unsigned int, 'restoreafter'=>seconds:unsigned int), ... ), false on error
     *
     * @throws SWIFT_Exception
     * @uses      SQL
     *
     * @todo      Set marker() profile
     * @todo      KACT 5/pages/317
     * @todo      Integration
     */
    static function GetFrequencyThreshholds()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Query("SELECT length, maxhits, restoreafter FROM " . TABLE_PREFIX . "parserlooprules");
        $_loopRuleContainer = array();
        while ($_SWIFT->Database->NextRecord()) {
            $_loopRuleContainer[] = array('length' => (int)($_SWIFT->Database->Record['length']),
                'maxhits' => (int)($_SWIFT->Database->Record['maxhits']), 'restoreafter' => (int)($_SWIFT->Database->Record['restoreafter']));
        }

        return $_loopRuleContainer;
    }
}
