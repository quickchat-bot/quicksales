<?php

//=======================================
//###################################
// Kayako Singapore Pte. Ltd. - SWIFT Framework
//
// Source Copyright 2001-2009 Kayako Singapore Pte. Ltd.
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//                          www.kayako.com
//###################################
//=======================================
namespace LiveChat\Models\Rule;

use Base\Library\Rules\SWIFT_Rules;
use Base\Models\Department\SWIFT_Department;
use SWIFT;
use LiveChat\Models\Visitor\SWIFT_Visitor;
use LiveChat\Models\Visitor\SWIFT_VisitorData;

/**
 * The Visitor Rule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_VisitorRule extends SWIFT_Rules
{
    const TABLE_NAME = 'visitorrules';
    const PRIMARY_KEY = 'visitorruleid';

    const TABLE_STRUCTURE = "visitorruleid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                ruletype I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                sortorder I DEFAULT '0' NOTNULL,
                                matchtype I2 DEFAULT '0' NOTNULL,
                                stopprocessing I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'ruletype';
    const INDEX_2 = 'title';


    protected $_dataStore = array();
    private $_visitorProperties = array();
    private $_loadStatusMap = false;

    // Core Constants
    // const RULETYPE_CHATESTABLISHED = 1;

    const RULETYPE_VISITORENTERSPAGE = 2;
    const RULETYPE_VISITORENTERSSITE = 3;
    // const RULETYPE_CHATQUEUED = 4;
    const ACTION_VARIABLE = 'variable';
    const ACTION_VISITOREXPERIENCE = 'visitorexperience';
    const ACTION_STAFFALERT = 'staffalert';
    const ACTION_SETSKILL = 'setskill';
    const ACTION_SETGROUP = 'setgroup';
    const ACTION_SETDEPARTMENT = 'setdepartment';
    const ACTION_SETCOLOR = 'setcolor';
    const ACTION_BANVISITOR = 'banvisitor';

    // Rule Constants
    const VISITOR_CURRENTPAGE = 'currentpage'; // The current page the visitor is on
    const VISITOR_NUMBEROFPAGES = 'numberofpages'; // The number of pages the visitors has viewied
    const VISITOR_PREVIOUSPAGETITLE = 'previouspagetitle'; // The previous page the visitor was viewing
    const VISITOR_PREVIOUSPAGEURL = 'previouspageurl'; // The previous page the visitor was viewing
    const VISITOR_REFERRINGURL = 'referringurl'; // The Referring URL
    const VISITOR_SEARCHENGINEFOUND = 'searchenginefound'; // Boolean on whether it was a search engine that referred the visitor
    const VISITOR_SEARCHENGINENAME = 'searchenginename'; // The search engine name
    const VISITOR_GEOCOUNTRY = 'geocountry';
    const VISITOR_GEOCITY = 'geocity';
    const VISITOR_GEOREGION = 'geoipregion';
    const VISITOR_GEOORGANIZATION = 'geoorganization';
    const VISITOR_GEOTIMEZONE = 'geotimezone';
    const VISITOR_GEOISP = 'geoisp';
    const VISITOR_GEOCONNECTIONTYPE = 'geoconnectiontype';
    const VISITOR_GEOPOSTALCODE = 'geoippostalcode';
    const VISITOR_GEOMETROCODE = 'geoipmetrocode';
    const VISITOR_GEOAREACODE = 'geoipareacode';
    const VISITOR_REPEATVISIT = 'repeatvisit'; // If this is a repeat visit from the visitor
    const VISITOR_ONLINESTAFF = 'onlinestaff'; // Boolean on whether there are any staff users online
    const VISITOR_ONLINESTAFFSKILLS = 'onlinestaffskills'; // Boolean on whether there are any staff users with specific skills online
    const VISITOR_ONLINESTAFFDEPARTMENT = 'onlinestaffdepartments'; // Boolean on whether there are any staff users assigned to specific departments
    const VISITOR_TIMEINSITE = 'timeinsite'; // Total time visitor has been on site in seconds

    /**
     * const VISITOR_TIMEINCHAT = 'timeinchat'; // Total time visitor has been in chat in seconds
     * const VISITOR_TIMEAGENTENTRY = 'timeagententry'; // Seconds since last agent entry
     * const VISITOR_TIMEVISITORENTRY = 'timevisitorentry'; // Seconds since last visitor entry
     * const VISITOR_TIMEAGENTVISITORENTRY = 'timeagentvisitorentry'; // Seconds since last agent or visitor entry
     * const VISITOR_CHATAGENTLASTMESSAGE = 'chatagentlastmessage'; // Last message of the agent
     * const VISITOR_CHATVISITORLASTMESSAGE = 'chatvisitorlastmessage'; // Last message of visitor
     * const VISITOR_CHATWITHAGENTMESSAGE = 'chatwithagentmessage'; // Boolean: Chat contains agent message
     * const VISITOR_CHATWITHVISITORMESSAGE = 'chatwithvisitormessage'; // Boolean: Chat contains visitor message
     * const VISITOR_CHATWITHAGENTVISITORMESSAGE = 'chatwithagentvisitormessage'; // Boolean: Chat contains agent OR visitor message
     * const VISITOR_CHATINCHAT = 'chatinchat'; // Boolean: Visitor has initiated a chat
     * const VISITOR_CHATREFUSEDCHAT = 'chatrefusedchat'; // Boolean: Visitor has been refused a chat
     */
    const VISITOR_CURRENTPAGEURL = 'currentpageurl'; // The Current Page URL
    const VISITOR_CURRENTPAGETITLE = 'currentpagetitle'; // The Current Page Title
    const VISITOR_SEARCHENGINEQUERY = 'searchenginequery'; // Search Engine Query
    const VISITOR_VISITEDPAGEURL = 'visitedpageurl'; // Visited Page URL
    const VISITOR_VISITEDPAGETITLE = 'visitedpagetitle'; // Visited Page Title
    const VISITOR_GEOLATITUDE = 'geoiplatitude'; // Geo Latitude
    const VISITOR_GEOLONGITUDE = 'geoiplongitude'; // Geo Longitude
    const VISITOR_BROWSER = 'browser'; // Browser
    const VISITOR_IPADDRESS = 'ipaddress'; // IP Address

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @param array|false $_dataStore The Predefined Data Store (saves on additional queries, OPTIONAL)
     */

    public function __construct($_visitorRuleID, $_dataStore = false)
    {
        if (!_is_array($_dataStore) && !$this->LoadData($_visitorRuleID)) {
            return;
        } else if (_is_array($_dataStore)) {
            if (_is_array($_dataStore['_criteria'])) {
                foreach ($_dataStore['_criteria'] as $key => $val) {
                    if ($val[0] == self::VISITOR_ONLINESTAFF || $val[0] == self::VISITOR_ONLINESTAFFSKILLS || $val[0] == self::VISITOR_ONLINESTAFFDEPARTMENT) {
                        $this->SetStatusMap(true);
                    }
                }
            }

            $this->_dataStore = $_dataStore;
        }

        parent::__construct($this->GetProperty('_criteria'), $this->GetProperty('matchtype'));
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
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()) || !$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitorrules', $this->GetUpdatePool(), 'UPDATE', "visitorruleid = '" . (int)($this->GetVisitorRuleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieve the Status Map Setting
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetStatusMap()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_loadStatusMap;
    }

    /**
     * Set the Status Map Setting
     *
     * @author Varun Shoor
     * @param bool $_loadStatusMap Load the status map?
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetStatusMap($_loadStatusMap)
    {
        $this->_loadStatusMap = $_loadStatusMap;

        return true;
    }

    /**
     * Retrieves the Visitor Rule ID
     *
     * @author Varun Shoor
     * @return mixed "visitorruleid" on Success, "false" otherwise
     */
    public function GetVisitorRuleID()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_dataStore['visitorruleid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_visitorRuleID The Visitor Rule ID ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_visitorRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitorrules WHERE visitorruleid = '" . $_visitorRuleID . "'");
        if (isset($_dataStore['visitorruleid']) && !empty($_dataStore['visitorruleid'])) {
            $_dataStore['_criteria'] = $_dataStore['_actions'] = array();

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorrulecriteria WHERE visitorruleid = '" . $_visitorRuleID . "'");
            while ($_SWIFT->Database->NextRecord()) {
                $_dataStore['_criteria'][$_SWIFT->Database->Record['visitorrulecriteriaid']] = array($_SWIFT->Database->Record['name'], $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch']);

                if ($_SWIFT->Database->Record['name'] == self::VISITOR_ONLINESTAFF || $_SWIFT->Database->Record['name'] == self::VISITOR_ONLINESTAFFSKILLS || $_SWIFT->Database->Record['name'] == self::VISITOR_ONLINESTAFFDEPARTMENT) {
                    $this->SetStatusMap(true);
                }
            }

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorruleactions WHERE visitorruleid = '" . $_visitorRuleID . "'");
            while ($_SWIFT->Database->NextRecord()) {
                $_dataStore['_actions'][$_SWIFT->Database->Record['visitorruleactionid']] = array($_SWIFT->Database->Record['actiontype'], $_SWIFT->Database->Record['actionname'], $_SWIFT->Database->Record['actionvalue']);
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
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
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
     */
    public function GetProperty($_key)
    {
        if (!isset($this->_dataStore[$_key])) {
            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Insert a new visitor rule record
     *
     * @author Varun Shoor
     * @param string $_title The Rule Title
     * @param bool $_stopProcessing Stop Processing Other Rules
     * @param int $_sortOrder The Rule Execution Order
     * @param int $_ruleOptions The Match Type (AND/OR)
     * @param array $_ruleCriteria The Criteria Container Array
     * @param array $_ruleActions The Rule Action Container Array
     * @param int $_ruleType The Rule Type
     * @return mixed "SWIFT_VisitorRule" object on Success, "false" otherwise
     */
    public static function Insert($_title, $_stopProcessing, $_sortOrder, $_ruleOptions, $_ruleCriteria, $_ruleActions, $_ruleType)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'visitorrules', array('title' => $_title, 'dateline' => DATENOW, 'sortorder' => ($_sortOrder), 'matchtype' => ($_ruleOptions), 'stopprocessing' => (int)($_stopProcessing), 'ruletype' => ($_ruleType)), 'INSERT');
        $_visitorRuleID = $_SWIFT->Database->Insert_ID();

        if (!$_visitorRuleID) {
            return false;
        }

        $_SWIFT_VisitorRuleObject = new SWIFT_VisitorRule($_visitorRuleID);
        if (!$_SWIFT_VisitorRuleObject instanceof SWIFT_VisitorRule || !$_SWIFT_VisitorRuleObject->GetIsClassLoaded()) {
            return false;
        }

        if (_is_array($_ruleCriteria)) {
            foreach ($_ruleCriteria as $key => $val) {
                $_SWIFT_VisitorRuleObject->InsertRuleCriteria($val[0], $val[1], $val[2]);
            }
        }

        if (_is_array($_ruleActions)) {
            foreach ($_ruleActions as $key => $val) {
                if (!isset($val[2])) {
                    $val[2] = '';
                }

                $_SWIFT_VisitorRuleObject->InsertRuleAction($val[0], $val[1], $val[2]);
            }
        }

        self::RebuildCache();

        return $_SWIFT_VisitorRuleObject;
    }

    /**
     * Update the visitor rule Record
     *
     * @author Varun Shoor
     * @param string $_title The Rule Title
     * @param bool $_stopProcessing Stop Processing Other Rules
     * @param int $_sortOrder The Rule Execution Order
     * @param int $_ruleOptions The Match Type (AND/OR)
     * @param array $_ruleCriteria The Criteria Container Array
     * @param array $_ruleActions The Rule Action Container Array
     * @param int $_ruleType The Rule Type
     * @return bool "true" on Success, "false" otherwise
     */
    public function Update($_title, $_stopProcessing, $_sortOrder, $_ruleOptions, $_ruleCriteria, $_ruleActions, $_ruleType)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitorrules', array('title' => $_title, 'dateline' => DATENOW, 'sortorder' => ($_sortOrder), 'matchtype' => ($_ruleOptions), 'stopprocessing' => (int)($_stopProcessing), 'ruletype' => ($_ruleType)), 'UPDATE', "visitorruleid = '" . (int)($this->GetVisitorRuleID()) . "'");

        $this->DeleteRuleCriteria();
        $this->DeleteRuleActions();

        if (_is_array($_ruleCriteria)) {
            foreach ($_ruleCriteria as $key => $val) {
                $this->InsertRuleCriteria($val[0], $val[1], $val[2]);
            }
        }

        if (_is_array($_ruleActions)) {
            foreach ($_ruleActions as $key => $val) {
                if (!isset($val[2])) {
                    $val[2] = '';
                }

                $this->InsertRuleAction($val[0], $val[1], $val[2]);
            }
        }

        self::RebuildCache();


        return true;
    }

    /**
     * Inserts a new Rule Criteria for the visitor rule
     *
     * @author Varun Shoor
     * @param string $_name The Criteria Name
     * @param int $_ruleOp The Rule Operator
     * @param string $_ruleMatch The Rule Match Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertRuleCriteria($_name, $_ruleOp, $_ruleMatch)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitorrulecriteria', array('visitorruleid' => $this->GetVisitorRuleID(), 'name' => $_name, 'ruleop' => ($_ruleOp), 'rulematch' => strval($_ruleMatch)), 'INSERT');

        return true;
    }

    /**
     * Inserts a new Rule Action for the given visitor rule
     *
     * @author Varun Shoor
     * @param string $_actionType The Action Type
     * @param string $_actionName The Action Field Name
     * @param string $_actionValue The Action Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function InsertRuleAction($_actionType, $_actionName, $_actionValue)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitorruleactions', array('visitorruleid' => $this->GetVisitorRuleID(), 'actiontype' => $_actionType, 'actionname' => $_actionName, 'actionvalue' => ReturnNone($_actionValue)), 'INSERT');

        return true;
    }

    /**
     * Empties the Rule Criteria for a Given Rule ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function DeleteRuleCriteria()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorrulecriteria WHERE visitorruleid = '" . (int)($this->GetVisitorRuleID()) . "'");

        return true;
    }

    /**
     * Deletes the Visitor Rule Actions based on Given Rule ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function DeleteRuleActions()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorruleactions WHERE visitorruleid = '" . (int)($this->GetVisitorRuleID()) . "'");

        return true;
    }

    /**
     * Delete the visitor rule record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        self::DeleteList(array($this->GetVisitorRuleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Deletes the Visitor Rule List
     *
     * @author Varun Shoor
     * @param array $_visitorRuleIDList The Visitor Rule ID List Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_visitorRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_visitorRuleIDList)) {
            return false;
        }

        $_finalVisitorRuleIDList = array();
        $_index = 1;

        $_resultText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorrules WHERE visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_resultText .= $_index . ". " . htmlspecialchars($_SWIFT->Database->Record["title"]) . " (" . IIF($_SWIFT->Database->Record["matchtype"] == self::RULE_MATCHALL, $_SWIFT->Language->Get('smatchall'), $_SWIFT->Language->Get('smatchany')) . ")<br>";
            $_finalVisitorRuleIDList[] = $_SWIFT->Database->Record["visitorruleid"];

            $_index++;
        }

        $_visitorRuleIDList = $_finalVisitorRuleIDList;

        if (!count($_visitorRuleIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelvrules'), count($_visitorRuleIDList)), sprintf($_SWIFT->Language->Get('msgdelvrules'), $_resultText));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorrules WHERE visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorrulecriteria WHERE visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorruleactions WHERE visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuilds the Visitor Rule Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = $_visitorRuleIDList = array();

        $_index = 0;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorrules ORDER BY sortorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_visitorRuleIDList[] = $_SWIFT->Database->Record["visitorruleid"];
            $_cache[$_SWIFT->Database->Record["visitorruleid"]] = $_SWIFT->Database->Record;
            $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_criteria"] = array();
            $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_criteriaPointer"] = array();
            $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_actions"] = array();
            $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_actionsPointer"] = array();
        }

        if (count($_visitorRuleIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorrulecriteria WHERE visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_criteria"][$_SWIFT->Database->Record['visitorrulecriteriaid']] = array($_SWIFT->Database->Record['name'], $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch']);
                $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_criteriaPointer"][$_SWIFT->Database->Record['visitorrulecriteriaid']] = $_SWIFT->Database->Record;
            }

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorruleactions WHERE visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_actions"][$_SWIFT->Database->Record['visitorruleactionid']] = array($_SWIFT->Database->Record['actiontype'], $_SWIFT->Database->Record['actionname'], $_SWIFT->Database->Record['actionvalue']);
                $_cache[$_SWIFT->Database->Record["visitorruleid"]]["_actionsPointer"][$_SWIFT->Database->Record['visitorruleactionid']] = $_SWIFT->Database->Record;
            }
        }

        $_SWIFT->Cache->Update('visitorrulecache', $_cache);

        return true;
    }

    /**
     * Extends the $_criteria array with custom field data (like skills, departments etc.)
     *
     * @author Varun Shoor
     * @param array $_criteriaPointer The Criteria Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ExtendCustomCriteria(&$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        // ======= STAFF WITH SKILLS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskills ORDER BY title");
        while ($_SWIFT->Database->NextRecord()) {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['chatskillid']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('na'), 'contents' => '0');
        }

        $_criteriaPointer[self::VISITOR_ONLINESTAFFSKILLS]["fieldcontents"] = $_field;

        // ======= STAFF WITH DEPARTMENTS =======
        $_field = array();
        $_departmentMap = SWIFT_Department::GetDepartmentMap(APP_LIVECHAT);
        foreach ($_departmentMap as $key => $val) {
            $_field[] = array('title' => $val['title'], 'contents' => $val['departmentid']);
            if (_is_array($val['subdepartments'])) {
                foreach ($val['subdepartments'] as $subkey => $subval) {
                    $_field[] = array('title' => '   |- ' . $subval['title'], 'contents' => $subval['departmentid']);
                }
            }
        }

        $_criteriaPointer[self::VISITOR_ONLINESTAFFDEPARTMENT]["fieldcontents"] = $_field;

        // ======= CONNECTION TYPE =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('high'), 'contents' => 'high');
        $_field[] = array('title' => $_SWIFT->Language->Get('corp'), 'contents' => 'corp');
        $_field[] = array('title' => $_SWIFT->Language->Get('dial'), 'contents' => 'dial');

        $_criteriaPointer[self::VISITOR_GEOCONNECTIONTYPE]["fieldcontents"] = $_field;

        return true;
    }

    /**
     * Loads the Rule Criteria and Rule Actions into $_POST
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadPOSTVariables()
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!isset($_POST['rulecriteria'])) {
            $_POST['rulecriteria'] = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorrulecriteria WHERE visitorruleid = '" . (int)($this->GetVisitorRuleID()) . "' ORDER BY visitorrulecriteriaid ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_POST['rulecriteria'][] = array($_SWIFT->Database->Record['name'], $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch']);
            }
        }

        if (!isset($_POST['ruleaction'])) {
            $_POST['ruleaction'] = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorruleactions WHERE visitorruleid = '" . (int)($this->GetVisitorRuleID()) . "' ORDER BY visitorruleactionid ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_POST['ruleaction'][] = array($_SWIFT->Database->Record['actiontype'], $_SWIFT->Database->Record['actionname'], $_SWIFT->Database->Record['actionvalue']);
            }
        }

        return true;
    }

    /**
     * Retrieves the Criteria Value
     *
     * @author Varun Shoor
     * @param string $_criteriaName The Criteria Name Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetCriteriaValue($_criteriaName)
    {
        if (isset($this->_visitorProperties[$_criteriaName])) {
            return $this->_visitorProperties[$_criteriaName];
        }

        return false;
    }

    /**
     * Processes the Visitor Properties for the Rule
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param string $_pageHash The Unique MD5 Hash of the page where the rule is being executed from
     * @param string $_proactiveResult (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function ProcessVisitorProperties($_visitorSessionID, $_pageHash, $_proactiveResult)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_visitorRuleCache = $this->Cache->Get('visitorrulecache');

        if (isset($_visitorRuleCache[$_visitorSessionID][$_pageHash])) {
            $this->_visitorProperties = $_visitorRuleCache[$_visitorSessionID][$_pageHash];

            return true;
        }

        $_visitorProperties = $_visitorFootprints = $_previousPage = array();
        $_leastDateline = false;

        $_visitorProperties['sessionid'] = $_visitorSessionID;
        $_visitorProperties['proactiveresult'] = $_proactiveResult;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorfootprints WHERE sessionid = '" . $_SWIFT->Database->Escape($_visitorSessionID) . "' ORDER BY dateline ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_visitorFootprints[] = $_SWIFT->Database->Record;

            if (!$_leastDateline || $_SWIFT->Database->Record['dateline'] < $_leastDateline) {
                $_leastDateline = $_SWIFT->Database->Record['dateline'];
            }

            if ($_SWIFT->Database->Record['pagehash'] == $_pageHash) {
                $_visitorProperties[self::VISITOR_CURRENTPAGEURL] = $_SWIFT->Database->Record['pageurl'];
                $_visitorProperties[self::VISITOR_CURRENTPAGETITLE] = $_SWIFT->Database->Record['pagetitle'];
                $_visitorProperties[self::VISITOR_REFERRINGURL] = $_SWIFT->Database->Record['referrer'];

                if (_is_array($_previousPage)) {
                    $_visitorProperties[self::VISITOR_PREVIOUSPAGETITLE] = $_previousPage['pagetitle'];
                    $_visitorProperties[self::VISITOR_PREVIOUSPAGEURL] = $_previousPage['pageurl'];
                }
            }

            if (!empty($_SWIFT->Database->Record['searchenginename'])) {
                $_visitorProperties[self::VISITOR_SEARCHENGINEFOUND] = true;
                $_visitorProperties[self::VISITOR_SEARCHENGINENAME] = $_SWIFT->Database->Record['searchenginename'];
                $_visitorProperties[self::VISITOR_SEARCHENGINEQUERY] = $_SWIFT->Database->Record['searchstring'];
            }

            if (!empty($_SWIFT->Database->Record['browsername'])) {
                $_visitorProperties[self::VISITOR_BROWSER] = $_SWIFT->Database->Record['browsername'];
            }

            $_visitorProperties[self::VISITOR_VISITEDPAGEURL][] = $_SWIFT->Database->Record['pageurl'];
            $_visitorProperties[self::VISITOR_VISITEDPAGETITLE][] = $_SWIFT->Database->Record['pagetitle'];

            if (!empty($_SWIFT->Database->Record['repeatvisit']) && !isset($_visitorProperties[self::VISITOR_REPEATVISIT])) {
                $_visitorProperties[self::VISITOR_REPEATVISIT] = $_SWIFT->Database->Record['repeatvisit'];
            }

            if (!empty($_SWIFT->Database->Record['geoipcountrydesc']) && !isset($_visitorProperties[self::VISITOR_GEOCOUNTRY])) {
                $_visitorProperties[self::VISITOR_GEOCOUNTRY] = $_SWIFT->Database->Record['geoipcountrydesc'];
            }

            if (!empty($_SWIFT->Database->Record['geoipcity']) && !isset($_visitorProperties[self::VISITOR_GEOCITY])) {
                $_visitorProperties[self::VISITOR_GEOCITY] = $_SWIFT->Database->Record['geoipcity'];
            }

            if (!empty($_SWIFT->Database->Record['geoipregion']) && !isset($_visitorProperties[self::VISITOR_GEOREGION])) {
                $_visitorProperties[self::VISITOR_GEOREGION] = $_SWIFT->Database->Record['geoipregion'];
            }

            if (!empty($_SWIFT->Database->Record['geoiporganization']) && !isset($_visitorProperties[self::VISITOR_GEOORGANIZATION])) {
                $_visitorProperties[self::VISITOR_GEOORGANIZATION] = $_SWIFT->Database->Record['geoiporganization'];
            }

            if (!empty($_SWIFT->Database->Record['geoiptimezone']) && !isset($_visitorProperties[self::VISITOR_GEOTIMEZONE])) {
                $_visitorProperties[self::VISITOR_GEOTIMEZONE] = $_SWIFT->Database->Record['geoiptimezone'];
            }

            if (!empty($_SWIFT->Database->Record['geoipisp']) && !isset($_visitorProperties[self::VISITOR_GEOISP])) {
                $_visitorProperties[self::VISITOR_GEOISP] = $_SWIFT->Database->Record['geoipisp'];
            }

            if (!empty($_SWIFT->Database->Record['geoipnetspeed']) && !isset($_visitorProperties[self::VISITOR_GEOCONNECTIONTYPE])) {
                $_visitorProperties[self::VISITOR_GEOCONNECTIONTYPE] = $_SWIFT->Database->Record['geoipnetspeed'];
            }

            if (!empty($_SWIFT->Database->Record['geoippostalcode']) && !isset($_visitorProperties[self::VISITOR_GEOPOSTALCODE])) {
                $_visitorProperties[self::VISITOR_GEOPOSTALCODE] = $_SWIFT->Database->Record['geoippostalcode'];
            }

            if (!empty($_SWIFT->Database->Record['geoipmetrocode']) && !isset($_visitorProperties[self::VISITOR_GEOMETROCODE])) {
                $_visitorProperties[self::VISITOR_GEOMETROCODE] = $_SWIFT->Database->Record['geoipmetrocode'];
            }

            if (!empty($_SWIFT->Database->Record['geoipareacode']) && !isset($_visitorProperties[self::VISITOR_GEOAREACODE])) {
                $_visitorProperties[self::VISITOR_GEOAREACODE] = $_SWIFT->Database->Record['geoipareacode'];
            }

            if (!empty($_SWIFT->Database->Record['geoiplatitude']) && !isset($_visitorProperties[self::VISITOR_GEOLATITUDE])) {
                $_visitorProperties[self::VISITOR_GEOLATITUDE] = $_SWIFT->Database->Record['geoiplatitude'];
            }

            if (!empty($_SWIFT->Database->Record['geoiplongitude']) && !isset($_visitorProperties[self::VISITOR_GEOLONGITUDE])) {
                $_visitorProperties[self::VISITOR_GEOLONGITUDE] = $_SWIFT->Database->Record['geoiplongitude'];
            }

            if (!empty($_SWIFT->Database->Record['ipaddress']) && !isset($_visitorProperties[self::VISITOR_IPADDRESS])) {
                $_visitorProperties[self::VISITOR_IPADDRESS] = $_SWIFT->Database->Record['ipaddress'];
            }

            $_previousPage = $_SWIFT->Database->Record;
        }

        $_visitorProperties[self::VISITOR_NUMBEROFPAGES] = count($_visitorFootprints);

        $_visitorProperties[self::VISITOR_TIMEINSITE] = DATENOW - $_leastDateline;

        // ======= STAFF STATUS MAP PROCESSING =======
        if ($this->GetStatusMap()) {
            $_staffStatusMap = SWIFT_Visitor::GetStaffOnlineStatusMap();
            $_visitorProperties[self::VISITOR_ONLINESTAFF] = IIF(count($_staffStatusMap[0]), true, false);
            $_visitorProperties[self::VISITOR_ONLINESTAFFDEPARTMENT] = $_staffStatusMap[1];
            $_visitorProperties[self::VISITOR_ONLINESTAFFSKILLS] = $_staffStatusMap[2];
        }

        $this->_visitorProperties = $_visitorProperties;

        $_visitorRuleCache[$_visitorSessionID][$_pageHash] = $_visitorProperties;

        return true;

        /*
          NEW: VISITOR_CURRENTPAGEURL, VISITOR_CURRENTPAGETITLE, VISITOR_SEARCHENGINEQUERY, VISITOR_VISITEDPAGEURL, VISITOR_VISITEDPAGETITLE, VISITOR_GEOLATITUDE, VISITOR_GEOLONGITUDE, VISITOR_BROWSER, VISITOR_IPADDRESS

          DONE (BUT CHANGE): define("VISITOR_CURRENTPAGE", 'currentpage'); // The current page the visitor is on
          DONE: define("VISITOR_NUMBEROFPAGES", 'numberofpages'); // The number of pages the visitors has viewied
          DONE (BUT CHANGE): define("VISITOR_PREVIOUSPAGE", 'previouspage'); // The previous page the visitor was viewing
          DONE: define("VISITOR_REFERRINGURL", 'referringurl'); // The Referring URL
          DONE: define("VISITOR_SEARCHENGINEFOUND", 'searchenginefound'); // Boolean on whether it was a search engine that referred the visitor
          DONE: define("VISITOR_SEARCHENGINENAME", 'searchenginename'); // The search engine name
          DONE: define("VISITOR_VISITEDPAGE", 'visitedpage'); // Any page the visitor might have visited
          DONE: define("VISITOR_GEOCOUNTRY", 'geocountry');
          DONE: define("VISITOR_GEOCITY", 'geocity');
          DONE: define("VISITOR_GEOREGION", 'geoipregion');
          DONE: define("VISITOR_GEOORGANIZATION", 'geoorganization');
          DONE: define("VISITOR_GEOTIMEZONE", 'geotimezone');
          DONE: define("VISITOR_GEOISP", 'geoisp');
          DONE: define("VISITOR_GEOCONNECTIONTYPE", 'geoconnectiontype');
          DONE: define("VISITOR_GEOPOSTALCODE", 'geoippostalcode');
          DONE: define("VISITOR_GEOMETROCODE", 'geoipmetrocode');
          DONE: define("VISITOR_GEOAREACODE", 'geoipareacode');
          DONE: define("VISITOR_REPEATVISIT", 'repeatvisit'); // If this is a repeat visit from the visitor
          DONE: define("VISITOR_ONLINESTAFF", 'onlinestaff'); // Boolean on whether there are any staff users online
          DONE: define("VISITOR_ONLINESTAFFSKILLS", 'onlinestaffskills'); // Boolean on whether there are any staff users with specific skills online
          DONE: define("VISITOR_ONLINESTAFFDEPARTMENT", 'onlinestaffdepartments'); // Boolean on whether there are any staff users assigned to specific departments
          DONE: define("VISITOR_TIMEINSITE", 'timeinsite'); // Total time visitor has been on site in seconds

          DEFERRED: define("VISITOR_TIMEINCHAT", 'timeinchat'); // Total time visitor has been in chat in seconds
          DEFERRED: define("VISITOR_TIMEAGENTENTRY", 'timeagententry'); // Seconds since last agent entry
          DEFERRED: define("VISITOR_TIMEVISITORENTRY", 'timevisitorentry'); // Seconds since last visitor entry
          DEFERRED: define("VISITOR_TIMEAGENTVISITORENTRY", 'timeagentvisitorentry'); // Seconds since last agent or visitor entry
          DEFERRED: define("VISITOR_CHATAGENTLASTMESSAGE", 'chatagentlastmessage'); // Last message of the agent
          DEFERRED: define("VISITOR_CHATVISITORLASTMESSAGE", 'chatvisitorlastmessage'); // Last message of visitor
          DEFERRED: define("VISITOR_CHATWITHAGENTMESSAGE", 'chatwithagentmessage'); // Boolean: Chat contains agent message
          DEFERRED: define("VISITOR_CHATWITHVISITORMESSAGE", 'chatwithvisitormessage'); // Boolean: Chat contains visitor message
          DEFERRED: define("VISITOR_CHATWITHAGENTVISITORMESSAGE", 'chatwithagentvisitormessage'); // Boolean: Chat contains agent OR visitor message
          DEFERRED: define("VISITOR_CHATINCHAT", 'chatinchat'); // Boolean: Visitor has initiated a chat
          DEFFERED: define("VISITOR_CHATREFUSEDCHAT", 'chatrefusedchat'); // Boolean: Visitor has been refused a chat
         */
    }

    /**
     * Executes all rules with a given type set
     *
     * @author Varun Shoor
     * @param int $_ruleType The Rules Type
     * @param string|false $_visitorSessionID The Visitor Session ID
     * @param string|false $_pageHash The Unique MD5 Hash of the page where the rule is being executed from
     * @param string|false $_proactiveResult (OPTIONAL)
     * @return mixed "_actions" on Success, "false" otherwise
     */
    public static function ExecuteAllRules($_ruleType, $_visitorSessionID = false, $_pageHash = false, $_proactiveResult = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_visitorRuleCache = $_SWIFT->Cache->Get('visitorrulecache');
        if (!self::IsValidRuleType($_ruleType) || !$_visitorRuleCache || !_is_array($_visitorRuleCache)) {
            return false;
        }

        $_ruleContainer = $_returnActions = array();
        foreach ($_visitorRuleCache as $_key => $_val) {
            if ($_val['ruletype'] == $_ruleType) {
                $_ruleContainer[$_key] = $_val;
            }
        }

        foreach ($_ruleContainer as $key => $val) {

            $_SWIFT_VisitorRuleObject = new SWIFT_VisitorRule($key, $val);
            if (!$_SWIFT_VisitorRuleObject instanceof SWIFT_VisitorRule || !$_SWIFT_VisitorRuleObject->GetIsClassLoaded()) {
                continue;
            }

            $_SWIFT_VisitorRuleObject->ProcessVisitorProperties($_visitorSessionID, $_pageHash, $_proactiveResult);

            /* if ($_ruleType == self::RULETYPE_CHATESTABLISHED || $_ruleType == self::RULETYPE_CHATQUEUED) {
              // Process Chat Properties Here
              } */

            $_ruleResult = $_SWIFT_VisitorRuleObject->Execute();
            if ($_ruleResult) {
                // Execute All Actions Here
                $_resultActions = $_SWIFT_VisitorRuleObject->ExecuteRuleActions();
                if (_is_array($_resultActions)) {
                    $_returnActions = array_merge($_returnActions, $_resultActions);
                }

                if ($_SWIFT_VisitorRuleObject->GetProperty('stopprocessing') == true) {
                    break;
                }
            }
        }

        return $_returnActions;
    }

    /**
     * Executes all actions associated with the given visitor rule
     *
     * @author Varun Shoor
     * @return mixed "_actions" on Success, "false" otherwise
     */
    public function ExecuteRuleActions()
    {
        if (!$this->GetIsClassLoaded() || !_is_array($this->_visitorProperties) && !empty($this->_visitorProperties['sessionid'])) {
            return false;
        }

        $_SWIFT_VisitorObject = new SWIFT_Visitor($this->_visitorProperties['sessionid']);
        if (!$_SWIFT_VisitorObject instanceof SWIFT_Visitor || !$_SWIFT_VisitorObject->GetIsClassLoaded()) {
            return false;
        }

        SWIFT_VisitorData::DeleteList($_SWIFT_VisitorObject->GetSessionID(), array($this->GetProperty('visitorruleid')));

        $_returnActions = array();

        if (_is_array($this->GetProperty('_actions'))) {
            foreach ($this->GetProperty('_actions') as $key => $val) {
                switch ($val[0]) {
                    case self::ACTION_VARIABLE:
                        $_SWIFT_VisitorObject->AddVariable($this->GetProperty('visitorruleid'), $val[1], $val[2]);
                        break;

                    case self::ACTION_VISITOREXPERIENCE:
                        /*
                         * BUG FIX - Mahesh Salaria
                         *
                         * SWIFT-1023: Automatic proactive chat (via visitor rules) should remember if a visitor declines chat
                         *
                         * Comments: Now check if proactive chat is denied by user.
                         */
                        if ($this->_visitorProperties['proactiveresult'] != SWIFT_Visitor::PROACTIVERESULT_DENIED) {
                            if ($val[1] == 'inline') {
                                $_returnActions[] = SWIFT_Visitor::PROACTIVE_INLINE;
                            } else if ($val[1] == 'engage') {
                                $_returnActions[] = SWIFT_Visitor::PROACTIVE_ENGAGE;
                            } else if ($val[1] == 'engagecustom') {
                                // TODO: Add Engage Custom Code Here
                            }
                        }
                        break;

                    case self::ACTION_STAFFALERT:
                        $_SWIFT_VisitorObject->AddAlert($this->GetProperty('visitorruleid'), $val[1], $val[2]);
                        break;

                    case self::ACTION_SETSKILL:
                        $_SWIFT_VisitorObject->AddSkill($this->GetProperty('visitorruleid'), (int)($val[1]));
                        break;

                    case self::ACTION_SETGROUP:
                        $_SWIFT_VisitorObject->SetGroup((int)($val[1]));
                        break;

                    case self::ACTION_SETCOLOR:
                        $_SWIFT_VisitorObject->SetColor($val[1]);
                        break;

                    case self::ACTION_SETDEPARTMENT:
                        $_SWIFT_VisitorObject->SetDepartment((int)($val[1]));
                        break;

                    case self::ACTION_BANVISITOR:
                        if ($val[1] == 'ip') {
                            $_SWIFT_VisitorObject->Ban(SWIFT_Visitor::BAN_IP, 0);
                        } else if ($val[1] == 'classa') {
                            $_SWIFT_VisitorObject->Ban(SWIFT_Visitor::BAN_CLASSA, 0);
                        } else if ($val[1] == 'classb') {
                            $_SWIFT_VisitorObject->Ban(SWIFT_Visitor::BAN_CLASSB, 0);
                        } else if ($val[1] == 'classc') {
                            $_SWIFT_VisitorObject->Ban(SWIFT_Visitor::BAN_CLASSC, 0);
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        return $_returnActions;
    }

    /**
     * Converts the Rule Criteria & Action Pointer to JavaScript representation
     *
     * @author Varun Shoor
     * @param array $_ruleCriteria The Criteria Pointer
     * @param array|false $_ruleActions The Action Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CriteriaActionsPointerToJavaScript($_ruleCriteria, $_ruleActions = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_returnValue = '';

        if (_is_array($_ruleActions)) {
            foreach ($_ruleActions as $key => $val) {
                if ($val[0] == self::ACTION_VARIABLE) {
                    $_returnValue .= 'globalActionVariables("' . $val[1] . '", "' . $val[2] . '");';
                } else if ($val[0] == self::ACTION_VISITOREXPERIENCE) {
                    $_returnValue .= 'globalActionVisitorExperience("' . $val[1] . '");';
                } else if ($val[0] == self::ACTION_STAFFALERT) {
                    $_returnValue .= 'globalActionStaffAlerts("' . $val[1] . '", "' . $val[2] . '");';
                } else if ($val[0] == self::ACTION_SETSKILL) {
                    $_returnValue .= 'globalActionSetSkill("' . $val[1] . '");';
                } else if ($val[0] == self::ACTION_SETGROUP) {
                    $_returnValue .= 'globalActionSetGroup("' . $val[1] . '");';
                } else if ($val[0] == self::ACTION_SETDEPARTMENT) {
                    $_returnValue .= 'globalActionSetDepartment("' . $val[1] . '");';
                } else if ($val[0] == self::ACTION_SETCOLOR) {
                    $_returnValue .= 'globalActionSetColor("' . $val[1] . '");';
                } else if ($val[0] == self::ACTION_BANVISITOR) {
                    $_returnValue .= 'globalActionBanVisitor("' . $val[1] . '");';
                }
            }
        }

        parent::CriteriaActionsPointerToJavaScript($_ruleCriteria, $_returnValue);
    }

    /**
     * Checks to see if it is a valid rule type
     *
     * @author Varun Shoor
     * @param int $_ruleType The Rules Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidRuleType($_ruleType)
    {
        if ($_ruleType == self::RULETYPE_VISITORENTERSPAGE || $_ruleType == self::RULETYPE_VISITORENTERSSITE) {
            return true;
        }

        return false;
    }

    /**
     * Return the Criteria for this Rule
     *
     * @author Varun Shoor
     * @return mixed "_criteriaPointer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetCriteriaPointer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_criteriaPointer = array();
        $_criteriaPointer[self::VISITOR_CURRENTPAGEURL]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_CURRENTPAGEURL);
        $_criteriaPointer[self::VISITOR_CURRENTPAGEURL]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_CURRENTPAGEURL);
        $_criteriaPointer[self::VISITOR_CURRENTPAGEURL]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_CURRENTPAGEURL]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_CURRENTPAGETITLE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_CURRENTPAGETITLE);
        $_criteriaPointer[self::VISITOR_CURRENTPAGETITLE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_CURRENTPAGETITLE);
        $_criteriaPointer[self::VISITOR_CURRENTPAGETITLE]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_CURRENTPAGETITLE]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_NUMBEROFPAGES]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_NUMBEROFPAGES);
        $_criteriaPointer[self::VISITOR_NUMBEROFPAGES]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_NUMBEROFPAGES);
        $_criteriaPointer[self::VISITOR_NUMBEROFPAGES]['op'] = 'int';
        $_criteriaPointer[self::VISITOR_NUMBEROFPAGES]['field'] = 'int';

        $_criteriaPointer[self::VISITOR_PREVIOUSPAGEURL]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_PREVIOUSPAGEURL);
        $_criteriaPointer[self::VISITOR_PREVIOUSPAGEURL]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_PREVIOUSPAGEURL);
        $_criteriaPointer[self::VISITOR_PREVIOUSPAGEURL]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_PREVIOUSPAGEURL]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_PREVIOUSPAGETITLE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_PREVIOUSPAGETITLE);
        $_criteriaPointer[self::VISITOR_PREVIOUSPAGETITLE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_PREVIOUSPAGETITLE);
        $_criteriaPointer[self::VISITOR_PREVIOUSPAGETITLE]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_PREVIOUSPAGETITLE]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_REFERRINGURL]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_REFERRINGURL);
        $_criteriaPointer[self::VISITOR_REFERRINGURL]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_REFERRINGURL);
        $_criteriaPointer[self::VISITOR_REFERRINGURL]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_REFERRINGURL]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_SEARCHENGINEFOUND]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_SEARCHENGINEFOUND);
        $_criteriaPointer[self::VISITOR_SEARCHENGINEFOUND]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_SEARCHENGINEFOUND);
        $_criteriaPointer[self::VISITOR_SEARCHENGINEFOUND]['op'] = 'bool';
        $_criteriaPointer[self::VISITOR_SEARCHENGINEFOUND]['field'] = 'bool';

        $_criteriaPointer[self::VISITOR_SEARCHENGINENAME]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_SEARCHENGINENAME);
        $_criteriaPointer[self::VISITOR_SEARCHENGINENAME]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_SEARCHENGINENAME);
        $_criteriaPointer[self::VISITOR_SEARCHENGINENAME]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_SEARCHENGINENAME]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_SEARCHENGINEQUERY]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_SEARCHENGINEQUERY);
        $_criteriaPointer[self::VISITOR_SEARCHENGINEQUERY]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_SEARCHENGINEQUERY);
        $_criteriaPointer[self::VISITOR_SEARCHENGINEQUERY]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_SEARCHENGINEQUERY]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_BROWSER]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_BROWSER);
        $_criteriaPointer[self::VISITOR_BROWSER]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_BROWSER);
        $_criteriaPointer[self::VISITOR_BROWSER]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_BROWSER]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_IPADDRESS]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_IPADDRESS);
        $_criteriaPointer[self::VISITOR_IPADDRESS]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_IPADDRESS);
        $_criteriaPointer[self::VISITOR_IPADDRESS]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_IPADDRESS]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_VISITEDPAGEURL]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_VISITEDPAGEURL);
        $_criteriaPointer[self::VISITOR_VISITEDPAGEURL]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_VISITEDPAGEURL);
        $_criteriaPointer[self::VISITOR_VISITEDPAGEURL]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_VISITEDPAGEURL]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_VISITEDPAGETITLE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_VISITEDPAGETITLE);
        $_criteriaPointer[self::VISITOR_VISITEDPAGETITLE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_VISITEDPAGETITLE);
        $_criteriaPointer[self::VISITOR_VISITEDPAGETITLE]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_VISITEDPAGETITLE]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_REPEATVISIT]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_REPEATVISIT);
        $_criteriaPointer[self::VISITOR_REPEATVISIT]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_REPEATVISIT);
        $_criteriaPointer[self::VISITOR_REPEATVISIT]['op'] = 'bool';
        $_criteriaPointer[self::VISITOR_REPEATVISIT]['field'] = 'bool';

        $_criteriaPointer[self::VISITOR_ONLINESTAFF]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_ONLINESTAFF);
        $_criteriaPointer[self::VISITOR_ONLINESTAFF]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_ONLINESTAFF);
        $_criteriaPointer[self::VISITOR_ONLINESTAFF]['op'] = 'bool';
        $_criteriaPointer[self::VISITOR_ONLINESTAFF]['field'] = 'bool';

        $_criteriaPointer[self::VISITOR_ONLINESTAFFSKILLS]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_ONLINESTAFFSKILLS);
        $_criteriaPointer[self::VISITOR_ONLINESTAFFSKILLS]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_ONLINESTAFFSKILLS);
        $_criteriaPointer[self::VISITOR_ONLINESTAFFSKILLS]['op'] = 'bool';
        $_criteriaPointer[self::VISITOR_ONLINESTAFFSKILLS]['field'] = 'custom';

        $_criteriaPointer[self::VISITOR_ONLINESTAFFDEPARTMENT]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_ONLINESTAFFDEPARTMENT);
        $_criteriaPointer[self::VISITOR_ONLINESTAFFDEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_ONLINESTAFFDEPARTMENT);
        $_criteriaPointer[self::VISITOR_ONLINESTAFFDEPARTMENT]['op'] = 'bool';
        $_criteriaPointer[self::VISITOR_ONLINESTAFFDEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::VISITOR_TIMEINSITE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_TIMEINSITE);
        $_criteriaPointer[self::VISITOR_TIMEINSITE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_TIMEINSITE);
        $_criteriaPointer[self::VISITOR_TIMEINSITE]['op'] = 'int';
        $_criteriaPointer[self::VISITOR_TIMEINSITE]['field'] = 'int';

        /**
         * $_criteriaPointer[self::VISITOR_TIMEINCHAT]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_TIMEINCHAT);
         * $_criteriaPointer[self::VISITOR_TIMEINCHAT]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_TIMEINCHAT);
         * $_criteriaPointer[self::VISITOR_TIMEINCHAT]['op'] = 'int';
         * $_criteriaPointer[self::VISITOR_TIMEINCHAT]['field'] = 'int';
         *
         * $_criteriaPointer[self::VISITOR_TIMEAGENTENTRY]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_TIMEAGENTENTRY);
         * $_criteriaPointer[self::VISITOR_TIMEAGENTENTRY]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_TIMEAGENTENTRY);
         * $_criteriaPointer[self::VISITOR_TIMEAGENTENTRY]['op'] = 'int';
         * $_criteriaPointer[self::VISITOR_TIMEAGENTENTRY]['field'] = 'int';
         *
         * $_criteriaPointer[self::VISITOR_TIMEVISITORENTRY]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_TIMEVISITORENTRY);
         * $_criteriaPointer[self::VISITOR_TIMEVISITORENTRY]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_TIMEVISITORENTRY);
         * $_criteriaPointer[self::VISITOR_TIMEVISITORENTRY]['op'] = 'int';
         * $_criteriaPointer[self::VISITOR_TIMEVISITORENTRY]['field'] = 'int';
         *
         * $_criteriaPointer[self::VISITOR_TIMEAGENTVISITORENTRY]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_TIMEAGENTVISITORENTRY);
         * $_criteriaPointer[self::VISITOR_TIMEAGENTVISITORENTRY]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_TIMEAGENTVISITORENTRY);
         * $_criteriaPointer[self::VISITOR_TIMEAGENTVISITORENTRY]['op'] = 'int';
         * $_criteriaPointer[self::VISITOR_TIMEAGENTVISITORENTRY]['field'] = 'int';
         *
         * $_criteriaPointer[self::VISITOR_CHATAGENTLASTMESSAGE]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_CHATAGENTLASTMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATAGENTLASTMESSAGE]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_CHATAGENTLASTMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATAGENTLASTMESSAGE]['op'] = 'string';
         * $_criteriaPointer[self::VISITOR_CHATAGENTLASTMESSAGE]['field'] = 'text';
         *
         * $_criteriaPointer[self::VISITOR_CHATVISITORLASTMESSAGE]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_CHATVISITORLASTMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATVISITORLASTMESSAGE]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_CHATVISITORLASTMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATVISITORLASTMESSAGE]['op'] = 'string';
         * $_criteriaPointer[self::VISITOR_CHATVISITORLASTMESSAGE]['field'] = 'text';
         *
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTMESSAGE]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_CHATWITHAGENTMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTMESSAGE]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_CHATWITHAGENTMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTMESSAGE]['op'] = 'bool';
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTMESSAGE]['field'] = 'bool';
         *
         * $_criteriaPointer[self::VISITOR_CHATWITHVISITORMESSAGE]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_CHATWITHVISITORMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATWITHVISITORMESSAGE]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_CHATWITHVISITORMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATWITHVISITORMESSAGE]['op'] = 'bool';
         * $_criteriaPointer[self::VISITOR_CHATWITHVISITORMESSAGE]['field'] = 'bool';
         *
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTVISITORMESSAGE]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_CHATWITHAGENTVISITORMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTVISITORMESSAGE]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_CHATWITHAGENTVISITORMESSAGE);
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTVISITORMESSAGE]['op'] = 'bool';
         * $_criteriaPointer[self::VISITOR_CHATWITHAGENTVISITORMESSAGE]['field'] = 'bool';
         *
         * $_criteriaPointer[self::VISITOR_CHATINCHAT]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_CHATINCHAT);
         * $_criteriaPointer[self::VISITOR_CHATINCHAT]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_CHATINCHAT);
         * $_criteriaPointer[self::VISITOR_CHATINCHAT]['op'] = 'bool';
         * $_criteriaPointer[self::VISITOR_CHATINCHAT]['field'] = 'bool';
         *
         * $_criteriaPointer[self::VISITOR_CHATREFUSEDCHAT]['title'] = $_SWIFT->Language->Get('rule_'.self::VISITOR_CHATREFUSEDCHAT);
         * $_criteriaPointer[self::VISITOR_CHATREFUSEDCHAT]['desc'] = $_SWIFT->Language->Get('desc_rule_'.self::VISITOR_CHATREFUSEDCHAT);
         * $_criteriaPointer[self::VISITOR_CHATREFUSEDCHAT]['op'] = 'bool';
         * $_criteriaPointer[self::VISITOR_CHATREFUSEDCHAT]['field'] = 'bool';
         */
        $_criteriaPointer[self::VISITOR_GEOLATITUDE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOLATITUDE);
        $_criteriaPointer[self::VISITOR_GEOLATITUDE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOLATITUDE);
        $_criteriaPointer[self::VISITOR_GEOLATITUDE]['op'] = 'int';
        $_criteriaPointer[self::VISITOR_GEOLATITUDE]['field'] = 'int';

        $_criteriaPointer[self::VISITOR_GEOLONGITUDE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOLONGITUDE);
        $_criteriaPointer[self::VISITOR_GEOLONGITUDE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOLONGITUDE);
        $_criteriaPointer[self::VISITOR_GEOLONGITUDE]['op'] = 'int';
        $_criteriaPointer[self::VISITOR_GEOLONGITUDE]['field'] = 'int';

        $_criteriaPointer[self::VISITOR_GEOCOUNTRY]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOCOUNTRY);
        $_criteriaPointer[self::VISITOR_GEOCOUNTRY]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOCOUNTRY);
        $_criteriaPointer[self::VISITOR_GEOCOUNTRY]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOCOUNTRY]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOCITY]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOCITY);
        $_criteriaPointer[self::VISITOR_GEOCITY]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOCITY);
        $_criteriaPointer[self::VISITOR_GEOCITY]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOCITY]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOREGION]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOREGION);
        $_criteriaPointer[self::VISITOR_GEOREGION]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOREGION);
        $_criteriaPointer[self::VISITOR_GEOREGION]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOREGION]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOORGANIZATION]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOORGANIZATION);
        $_criteriaPointer[self::VISITOR_GEOORGANIZATION]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOORGANIZATION);
        $_criteriaPointer[self::VISITOR_GEOORGANIZATION]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOORGANIZATION]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOTIMEZONE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOTIMEZONE);
        $_criteriaPointer[self::VISITOR_GEOTIMEZONE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOTIMEZONE);
        $_criteriaPointer[self::VISITOR_GEOTIMEZONE]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOTIMEZONE]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOISP]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOISP);
        $_criteriaPointer[self::VISITOR_GEOISP]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOISP);
        $_criteriaPointer[self::VISITOR_GEOISP]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOISP]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOCONNECTIONTYPE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOCONNECTIONTYPE);
        $_criteriaPointer[self::VISITOR_GEOCONNECTIONTYPE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOCONNECTIONTYPE);
        $_criteriaPointer[self::VISITOR_GEOCONNECTIONTYPE]['op'] = 'bool';
        $_criteriaPointer[self::VISITOR_GEOCONNECTIONTYPE]['field'] = 'custom';

        $_criteriaPointer[self::VISITOR_GEOPOSTALCODE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOPOSTALCODE);
        $_criteriaPointer[self::VISITOR_GEOPOSTALCODE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOPOSTALCODE);
        $_criteriaPointer[self::VISITOR_GEOPOSTALCODE]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOPOSTALCODE]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOMETROCODE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOMETROCODE);
        $_criteriaPointer[self::VISITOR_GEOMETROCODE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOMETROCODE);
        $_criteriaPointer[self::VISITOR_GEOMETROCODE]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOMETROCODE]['field'] = 'text';

        $_criteriaPointer[self::VISITOR_GEOAREACODE]['title'] = $_SWIFT->Language->Get('rule_' . self::VISITOR_GEOAREACODE);
        $_criteriaPointer[self::VISITOR_GEOAREACODE]['desc'] = $_SWIFT->Language->Get('desc_rule_' . self::VISITOR_GEOAREACODE);
        $_criteriaPointer[self::VISITOR_GEOAREACODE]['op'] = 'string';
        $_criteriaPointer[self::VISITOR_GEOAREACODE]['field'] = 'text';

        return $_criteriaPointer;
    }

}

