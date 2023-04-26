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

namespace Parser\Models\Breakline;
use SWIFT;
use Parser\Models\Breakline\SWIFT_Breakline_Exception;
use SWIFT_Model;

/**
 * The Parser Breakline Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Breakline extends SWIFT_Model
{
    const TABLE_NAME = 'breaklines';
    const PRIMARY_KEY = 'breaklineid';

    const TABLE_STRUCTURE = "breaklineid I PRIMARY AUTO NOTNULL,
                                breakline C(255) DEFAULT '0' NOTNULL,

                                isregexp I2 DEFAULT '0' NOTNULL,
                                sortorder I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'breakline';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_breaklineID The Parser Breakline ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Breakline_Exception If it is unable to load the breakline record
     */
    public function __construct($_breaklineID)
    {
        parent::__construct();

        // @codeCoverageIgnoreStart
        if (!$this->LoadData($_breaklineID)) {
            throw new SWIFT_Breakline_Exception('Unable to load Breakline ID: ' . $_breaklineID);
        }
        // @codeCoverageIgnoreEnd
    }
     /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
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
     * @throws SWIFT_Breakline_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'breaklines', $this->GetUpdatePool(), 'UPDATE', "breaklineid = '" .
            (int)($this->GetBreaklineID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Breakline ID
     *
     * @author Varun Shoor
     * @return mixed "breaklineid" on Success, "false" otherwise
     * @throws SWIFT_Breakline_Exception If the Class is not Loaded
     */
    public function GetBreaklineID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Breakline_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['breaklineid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_breaklineID The Parser Breakline ID
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_breaklineID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "breaklines WHERE breaklineid = '" . $_breaklineID . "'");
        if (isset($_dataStore['breaklineid']) && !empty($_dataStore['breaklineid'])) {
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
     * @throws SWIFT_Breakline_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Breakline_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Breakline_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Breakline_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Breakline_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Breakline
     *
     * @author Varun Shoor
     *
     * @param string $_breaklineText       The Breakline Text
     * @param bool   $_isRegularExpression Whether it is a regular expression
     * @param int    $_sortOrder           The Breakline Sort Order
     *
     * @return mixed "_breaklineID" on Success, "false" otherwise
     * @throws SWIFT_Breakline_Exception If the Creation Fails
     */
    public static function Create($_breaklineText, $_isRegularExpression, $_sortOrder)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'breaklines', array('breakline' => $_breaklineText,
            'isregexp' => (int)($_isRegularExpression), 'sortorder' => $_sortOrder), 'INSERT');
        $_breaklineID = $_SWIFT->Database->Insert_ID();

        if (!$_breaklineID) {
            throw new SWIFT_Breakline_Exception('Failed to create Breakline');
        }

        self::RebuildCache();

        return $_breaklineID;
    }

    /**
     * Update the Breakline Record
     *
     * @author Varun Shoor
     *
     * @param string $_breaklineText       The Breakline Text
     * @param bool   $_isRegularExpression Whether it is a regular expression
     * @param int    $_sortOrder           The Breakline Sort Order
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Breakline_Exception If the Class is not Loaded
     */
    public function Update($_breaklineText, $_isRegularExpression, $_sortOrder)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Breakline_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('breakline', $_breaklineText);
        $this->UpdatePool('isregexp', $_isRegularExpression);
        $this->UpdatePool('sortorder', $_sortOrder);

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Breakline record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Breakline_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Breakline_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetBreaklineID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Breaklines
     *
     * @author Varun Shoor
     *
     * @param array $_breaklineIDList The Breakline ID List
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_breaklineIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_breaklineIDList)) {
            return false;
        }

        $_finalBreaklineIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "breaklines WHERE breaklineid IN (" . BuildIN($_breaklineIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalBreaklineIDList[] = $_SWIFT->Database->Record['breaklineid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['breakline']) . '<br />';
            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelbreakline'), count($_finalBreaklineIDList)), $_SWIFT->Language->Get('msgdelbreakline') .
            '<br />' . $_finalText);

        if (!count($_finalBreaklineIDList)) {
            return false;
        }

        $_SWIFT->Database->query("DELETE FROM " . TABLE_PREFIX . "breaklines WHERE breaklineid IN (" . buildIN($_finalBreaklineIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Parser Breakline Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "breaklines ORDER BY breaklineid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_index++;
            $_cache[$_SWIFT->Database->Record3['breaklineid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['breaklineid']]['index'] = $_index;
        }

        $_SWIFT->Cache->Update('breaklinecache', $_cache);

        return true;
    }
}

?>
