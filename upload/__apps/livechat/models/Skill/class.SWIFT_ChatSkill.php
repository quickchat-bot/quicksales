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

namespace LiveChat\Models\Skill;

use SWIFT;
use SWIFT_Model;
use LiveChat\Models\Skill\SWIFT_Skill_Exception;

/**
 * Live Chat Skills Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_ChatSkill extends SWIFT_Model
{
    const TABLE_NAME = 'chatskills';
    const PRIMARY_KEY = 'chatskillid';

    const TABLE_STRUCTURE = "chatskillid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                description C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'title';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Skill_Exception If the Class is not Loaded
     */
    public function __construct($_chatSkillID)
    {
        parent::__construct();

        if (!$this->LoadData($_chatSkillID)) {
            throw new SWIFT_Skill_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Skill_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'chatskills', $this->GetUpdatePool(), 'UPDATE', "chatskillid = '" . (int)($this->GetChatSkillID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Chat Skill ID
     *
     * @author Varun Shoor
     * @return mixed "chatskillid" on Success, "false" otherwise
     * @throws SWIFT_Skill_Exception If the Class is not Loaded
     */
    public function GetChatSkillID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Skill_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['chatskillid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_chatSkillID The Chat Skill ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_chatSkillID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chatskills WHERE chatskillid = '" . $_chatSkillID . "'");
        if (isset($_dataStore['chatskillid']) && !empty($_dataStore['chatskillid'])) {
            $_dataStore['links'] = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskilllinks WHERE chatskillid = '" . $_chatSkillID . "'");
            while ($this->Database->NextRecord()) {
                $_dataStore['links'][] = $this->Database->Record['staffid'];
            }

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
     * @throws SWIFT_Skill_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Skill_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_Skill_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Skill_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Insert a new chat skill
     *
     * @author Varun Shoor
     * @param string $_title The Chat Skill Title
     * @param string $_description The Chat Skill Description
     * @param array $_assignedStaffIDList The Assigned Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Skill_Exception If Invalid Data Specified or If Creation Fails
     */
    public static function Insert($_title, $_description, $_assignedStaffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title)) {
            throw new SWIFT_Skill_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatskills', array('title' => $_title, 'description' => $_description, 'dateline' => DATENOW), 'INSERT');
        $_chatSkillID = $_SWIFT->Database->Insert_ID();
        if (!$_chatSkillID) {
            throw new SWIFT_Skill_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        if (_is_array($_assignedStaffIDList)) {
            foreach ($_assignedStaffIDList as $key => $val) {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatskilllinks', array('chatskillid' => $_chatSkillID, 'staffid' => (int)($val)), 'INSERT');
            }
        }

        self::RebuildCache();

        return $_chatSkillID;
    }

    /**
     * Update The Chat Skill Record
     *
     * @author Varun Shoor
     * @param string $_title The Chat Skill Title
     * @param string $_description The Chat Skill Description
     * @param array $_assignedStaffIDList The Assigned Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Skill_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_title, $_description, $_assignedStaffIDList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Skill_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_title)) {
            throw new SWIFT_Skill_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'chatskills', array('title' => $_title, 'description' => $_description, 'dateline' => DATENOW), 'UPDATE', "chatskillid = '" . (int)($this->GetChatSkillID()) . "'");

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatskilllinks WHERE chatskillid = '" . (int)($this->GetChatSkillID()) . "'");
        if (_is_array($_assignedStaffIDList)) {
            foreach ($_assignedStaffIDList as $key => $val) {
                $this->Database->AutoExecute(TABLE_PREFIX . 'chatskilllinks', array('chatskillid' => (int)($this->GetChatSkillID()), 'staffid' => (int)($val)), 'INSERT');
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Loaded Skill
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Skill_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Skill_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($this->GetChatSkillID()));

        return false;
    }

    /**
     * Deletes a group of Chat Skills
     *
     * @author Varun Shoor
     * @param array $_chatSkillIDList The Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_chatSkillIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatSkillIDList)) {
            return false;
        }

        $_finalChatSkillIDList = array();
        $_index = 1;
        $_resultText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskills WHERE chatskillid IN (" . BuildIN($_chatSkillIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_resultText .= $_index . ". " . htmlspecialchars($_SWIFT->Database->Record["title"]) . " (" . IIF(!empty($_SWIFT->Database->Record['description']), htmlspecialchars($_SWIFT->Database->Record['description'])) . ")<BR />\n";
            $_finalChatSkillIDList[] = $_SWIFT->Database->Record["chatskillid"];

            $_index++;
        }

        $_chatSkillIDList = $_finalChatSkillIDList;

        if (!count($_chatSkillIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelchatskills'), count($_chatSkillIDList)), sprintf($_SWIFT->Language->Get('msgdelchatskills'), $_resultText));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatskills WHERE chatskillid IN (" . BuildIN($_chatSkillIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatskilllinks WHERE chatskillid IN (" . BuildIN($_chatSkillIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuilds the Chat Skill Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = $_chatSkillIDList = array();

        $_index = 0;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskills ORDER BY chatskillid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatSkillIDList[] = $_SWIFT->Database->Record["chatskillid"];
            $_cache[$_SWIFT->Database->Record["chatskillid"]] = $_SWIFT->Database->Record;
            $_cache[$_SWIFT->Database->Record["chatskillid"]]["links"] = array();
        }

        if (count($_chatSkillIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskilllinks WHERE chatskillid IN (" . BuildIN($_chatSkillIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_cache[$_SWIFT->Database->Record["chatskillid"]]["links"][] = (int)($_SWIFT->Database->Record['staffid']);
            }
        }

        $_SWIFT->Cache->Update('skillscache', $_cache);

        return true;
    }

    /**
     * Assign the chat skills to given staff
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID to assign to
     * @param array $_assignedChatSkillIDList The Assigned Chat Skill ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Skill_Exception If Invalid Data Specified or If Creation Fails
     */
    public static function Assign($_staffID, $_assignedChatSkillIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffID = $_staffID;

        if (empty($_staffID)) {
            throw new SWIFT_Skill_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatskilllinks WHERE staffid = '" . $_staffID . "'");
        if (_is_array($_assignedChatSkillIDList)) {
            foreach ($_assignedChatSkillIDList as $_key => $_val) {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatskilllinks', array('chatskillid' => (int)($_val), 'staffid' => $_staffID), 'INSERT');
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve a list of skill ids based on staff id's we received
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @return mixed "_chatSkillIDContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveSkillListOnStaffList($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return array();
        }

        $_chatSkillIDContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskilllinks WHERE staffid IN (" . BuildIN($_staffIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatSkillIDContainer[$_SWIFT->Database->Record['staffid']][] = $_SWIFT->Database->Record['chatskillid'];
        }

        return $_chatSkillIDContainer;
    }

    /**
     * Retrieve a list of skill ids based on staff id
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @return mixed "_chatSkillIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveSkillListOnStaff($_staffID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffID = $_staffID;

        if (empty($_staffID)) {
            return array();
        }

        $_chatSkillIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskilllinks WHERE staffid = '" . $_staffID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatSkillIDList[] = $_SWIFT->Database->Record['chatskillid'];
        }

        return $_chatSkillIDList;
    }

    /**
     * Retrieve the entire skills
     *
     * @author Varun Shoor
     * @return mixed "_chatSkillContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveSkills()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_chatSkillContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskills ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatSkillContainer[$_SWIFT->Database->Record['chatskillid']] = $_SWIFT->Database->Record;
        }

        return $_chatSkillContainer;
    }

    /**
     * Dispatches the Chat Skill Variable via Javascript
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DispatchJSVariable()
    {
        $_SWIFT = SWIFT::GetInstance();

        echo '<script type="text/javascript" language="Javascript">';
        $_chatSkills = array();
        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskills ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_chatSkills[] = '"' . $_index . '": {"0": "' . (int)($_SWIFT->Database->Record['chatskillid']) . '", "1": "' . addslashes($_SWIFT->Database->Record['title']) . '"}';
            $_index++;
        }

        if (!_is_array($_chatSkills)) {
            $_chatSkills[] = '"0": {"0": "0", "1": "' . addslashes($_SWIFT->Language->Get('notavailable')) . '"}';
        }

        echo 'var lschatskillsobj = {' . implode(',', $_chatSkills) . '}';
        echo '</script>';

        return true;
    }
}
