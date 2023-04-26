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

namespace Parser\Models\CatchAll;

use Parser\Models\CatchAll\SWIFT_CatchAll_Exception;
use SWIFT;
use SWIFT_Model;

/**
 * The Parser Catch All Rule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_CatchAllRule extends SWIFT_Model
{
    const TABLE_NAME = 'catchallrules';
    const PRIMARY_KEY = 'catchallruleid';

    const TABLE_STRUCTURE = "catchallruleid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                ruleexpr C(255) DEFAULT '' NOTNULL,
                                emailqueueid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                sortorder I DEFAULT '0' NOTNULL";


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_catchAllRuleID The Catch All Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CatchAll_Exception If the Record could not be loaded
     */
    public function __construct($_catchAllRuleID)
    {
        parent::__construct();

        // @codeCoverageIgnoreStart
        if (!$this->LoadData($_catchAllRuleID)) {
            throw new SWIFT_CatchAll_Exception('Failed to load CatchAll ID: ' . $_catchAllRuleID);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CatchAll_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()) || !$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'catchallrules', $this->GetUpdatePool(), 'UPDATE', "catchallruleid = '" .
            (int)($this->GetCatchAllRuleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Catch All Rule ID
     *
     * @author Varun Shoor
     * @return mixed "catchallruleid" on Success, "false" otherwise
     * @throws SWIFT_CatchAll_Exception If the Class is not Loaded
     */
    public function GetCatchAllRuleID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CatchAll_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['catchallruleid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_catchAllRuleID The Catch All Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_catchAllRuleID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "catchallrules WHERE catchallruleid = '" .
            $_catchAllRuleID . "'");
        if (isset($_dataStore['catchallruleid']) && !empty($_dataStore['catchallruleid'])) {
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
     * @throws SWIFT_CatchAll_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CatchAll_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_CatchAll_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CatchAll_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_CatchAll_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Catch All Rule Record
     *
     * @author Varun Shoor
     *
     * @param string $_ruleTitle      The CatchAll Rule Title
     * @param string $_ruleExpression The CatchAll Rule Regular Expression
     * @param int    $_emailQueueID   The Email Queue ID this Rule is Linked to
     * @param int    $_sortOrder      The Execution Order for this Rule
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CatchAll_Exception If the Record could not be created
     */
    public static function Create($_ruleTitle, $_ruleExpression, $_emailQueueID, $_sortOrder)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ruleTitle) || empty($_ruleExpression) || empty($_emailQueueID) || empty($_sortOrder)) {
            throw new SWIFT_CatchAll_Exception('Invalid Data Specified');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'catchallrules', array('title' => $_ruleTitle, 'ruleexpr' => $_ruleExpression,
            'emailqueueid' => $_emailQueueID, 'dateline' => DATENOW, 'sortorder' => $_sortOrder), 'INSERT');
        $_catchAllRuleID = $_SWIFT->Database->Insert_ID();

        if (!$_catchAllRuleID) {
            throw new SWIFT_CatchAll_Exception('Unable to Create CatchAll Rule');
        }

        self::RebuildCache();

        return $_catchAllRuleID;
    }

    /**
     * Update TABLENAME Record
     *
     * @author Varun Shoor
     *
     * @param string $_ruleTitle      The CatchAll Rule Title
     * @param string $_ruleExpression The CatchAll Rule Regular Expression
     * @param int    $_emailQueueID   The Email Queue ID this Rule is Linked to
     * @param int    $_sortOrder      The Execution Order for this Rule
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CatchAll_Exception If the Class is not Loaded or if one of the data specified is invalid
     */
    public function Update($_ruleTitle, $_ruleExpression, $_emailQueueID, $_sortOrder)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CatchAll_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ruleTitle) || empty($_ruleExpression) || empty($_emailQueueID) || empty($_sortOrder)) {
            throw new SWIFT_CatchAll_Exception('Invalid Data Specified');
        }

        $this->UpdatePool('title', $_ruleTitle);
        $this->UpdatePool('ruleexpr', $_ruleExpression);
        $this->UpdatePool('emailqueueid', $_emailQueueID);
        $this->UpdatePool('dateline', DATENOW);
        $this->UpdatePool('sortorder', $_sortOrder);

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Catch All Rule record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_CatchAll_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_CatchAll_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetCatchAllRuleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Catch All Rules
     *
     * @author Varun Shoor
     *
     * @param array $_catchAllRuleIDList The Catch All Rule ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_catchAllRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_catchAllRuleIDList)) {
            return false;
        }

        $_finalCatchAllRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "catchallrules WHERE catchallruleid IN (" . BuildIN($_catchAllRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';
            $_index++;

            $_finalCatchAllRuleIDList[] = $_SWIFT->Database->Record['catchallruleid'];
        }

        if (!count($_finalCatchAllRuleIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelcatchall'), count($_finalCatchAllRuleIDList)), $_SWIFT->Language->Get('msgdelcatchall') .
            $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "catchallrules WHERE catchallruleid IN (" . BuildIN($_finalCatchAllRuleIDList) .
            ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete on Email Queue ID List
     *
     * @author Varun Shoor
     *
     * @param array $_emailQueueIDList The Email Queue ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnEmailQueue($_emailQueueIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_emailQueueIDList)) {
            return false;
        }

        $_catchAllRuleIDList = array();

        $_SWIFT->Database->Query("SELECT catchallruleid FROM " . TABLE_PREFIX . "catchallrules WHERE emailqueueid IN (" .
            BuildIN($_emailQueueIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_catchAllRuleIDList[] = $_SWIFT->Database->Record['catchallruleid'];
        }

        // @codeCoverageIgnoreStart
        if (!count($_catchAllRuleIDList)) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        self::DeleteList($_catchAllRuleIDList);

        return true;
    }

    /**
     * Rebuild the CatchAll Rule Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "catchallrules ORDER BY sortorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_index++;

            $_cache[$_SWIFT->Database->Record3['catchallruleid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['catchallruleid']]['index'] = $_index;
        }

        $_SWIFT->Cache->Update('parsercatchallcache', $_cache);

        return true;
    }
}

?>
