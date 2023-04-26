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

namespace Parser\Models\Rule;

use Base\Library\Rules\SWIFT_Rules;
use Base\Models\Department\SWIFT_Department;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Loader;
use Tickets\Library\Flag\SWIFT_TicketFlag;

/**
 * The Parser Rule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_ParserRule extends SWIFT_Rules
{
    const TABLE_NAME = 'parserrules';
    const PRIMARY_KEY = 'parserruleid';

    const TABLE_STRUCTURE = "parserruleid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                sortorder I DEFAULT '0' NOTNULL,
                                ruletype I2 DEFAULT '0' NOTNULL,
                                matchtype I2 DEFAULT '0' NOTNULL,
                                stopprocessing I2 DEFAULT '0' NOTNULL,

                                isenabled I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'title';


    protected $_dataStore = array();
    protected $_parserRuleProperties = array();

    // Criteria
    const PARSER_SENDERNAME = 'sendername';
    const PARSER_SENDEREMAIL = 'senderemail';
    const PARSER_DESTINATIONNAME = 'destinationname';
    const PARSER_DESTINATIONEMAIL = 'destinationemail';
    const PARSER_REPLYTONAME = 'replytoname';
    const PARSER_REPLYTOEMAIL = 'replytoemail';
    const PARSER_SUBJECT = 'subject';
    const PARSER_RECIPIENTS = 'recipients';
    const PARSER_BODY = 'body';
    const PARSER_BODYSIZE = 'bodysize';
    const PARSER_TEXTBODY = 'textbody';
    const PARSER_HTMLBODY = 'htmlbody';
    const PARSER_TEXTBODYSIZE = 'textbodysize';
    const PARSER_HTMLBODYSIZE = 'htmlbodysize';
    const PARSER_ATTACHMENTNAME = 'attachmentname';
    const PARSER_ATTACHMENTSIZE = 'attachmentsize';
    const PARSER_TOTALATTACHMENTSIZE = 'totalattachmentsize';
    const PARSER_ISREPLY = 'isreply';
    const PARSER_ISTHIRDPARTY = 'isthirdparty';
    const PARSER_ISSTAFFREPLY = 'isstaffreply';
    const PARSER_TICKETSTATUS = 'ticketstatus';
    const PARSER_TICKETTYPE = 'tickettype';
    const PARSER_TICKETPRIORITY = 'ticketpriority';
    const PARSER_TICKETUSERGROUP = 'ticketusergroup';
    const PARSER_TICKETDEPARTMENT = 'ticketdepartment';
    const PARSER_TICKETOWNER = 'ticketowner';
    const PARSER_TICKETEMAILQUEUE = 'ticketemailqueue';
    const PARSER_TICKETFLAGTYPE = 'ticketflagtype';

    const PARSER_BAYESCATEGORY = 'bayescategory';

    // Pre Parse Actions
    const PARSERACTION_FORWARD = 'forward';
    const PARSERACTION_REPLY = 'reply';
    const PARSERACTION_IGNORE = 'ignore';
    const PARSERACTION_NOAUTORESPONDER = 'noautoresponder';
    const PARSERACTION_NOALERTRULES = 'noalertrules';
    const PARSERACTION_NOTICKET = 'noticket';

    // Post Parse Actions
    const PARSERACTION_DEPARTMENT = 'department';
    const PARSERACTION_OWNER = 'owner';
    const PARSERACTION_STATUS = 'status';
    const PARSERACTION_PRIORITY = 'priority';
    const PARSERACTION_ADDNOTE = 'addnote';
    const PARSERACTION_FLAGTICKET = 'flagticket';
    const PARSERACTION_SLAPLAN = 'slaplan';

    const PARSERACTION_TICKETTYPE = 'tickettype';
    const PARSERACTION_ADDTAGS = 'addtags';
    const PARSERACTION_REMOVETAGS = 'removetags';
    const PARSERACTION_MOVETOTRASH = 'movetotrash';
    const PARSERACTION_PRIVATE = 'private';

    // Rule Type
    const TYPE_PREPARSE = 1;
    const TYPE_POSTPARSE = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param int $_parserRuleID The Parser Rule ID
     * @param array|int $_dataStore The Data Store
     *
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Record could not be loaded
     */
    public function __construct($_parserRuleID, $_dataStore = null)
    {
        // @codeCoverageIgnoreStart
        if (!_is_array($_dataStore) && !$this->LoadData($_parserRuleID)) {
            throw new SWIFT_Rule_Exception('Failed to load Parser Rule ID: ' . $_parserRuleID);
        } else if (_is_array($_dataStore)) {
            $this->_dataStore = $_dataStore;
        }
        // @codeCoverageIgnoreEnd

        $this->SetIsClassLoaded(true);

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
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'parserrules', $this->GetUpdatePool(), 'UPDATE', "parserruleid = '" . (int)($this->GetParserRuleID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Parser Rule ID
     *
     * @author Varun Shoor
     * @return mixed "parserruleid" on Success, "false" otherwise
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function GetParserRuleID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['parserruleid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     *
     * @param \SWIFT_Data|int $_parserRuleID The Parser Rule ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function LoadData($_parserRuleID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid = '" . $_parserRuleID . "'");
        if (isset($_dataStore['parserruleid']) && !empty($_dataStore['parserruleid'])) {
            $_dataStore['_criteria'] = array();

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrulecriteria WHERE parserruleid = '" . $_parserRuleID . "'");
            while ($_SWIFT->Database->NextRecord()) {
                $_ruleMatchType = $_SWIFT->Database->Record['rulematchtype'];
                // An old entry?
                if (empty($_ruleMatchType) && $_dataStore['matchtype'] != SWIFT_Rules::RULE_MATCHEXTENDED) {
                    $_ruleMatchType = $_dataStore['matchtype'];
                }

                $_dataStore['_criteria'][$_SWIFT->Database->Record['parserrulecriteriaid']] = array($_SWIFT->Database->Record['name'], $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch'], $_ruleMatchType);
            }

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
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Rule_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Rule_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Insert the given Criterias in the currently loaded rule
     *
     * @author Varun Shoor
     *
     * @param array $_criteriaContainer The Criteria Container
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function InsertCriteria($_criteriaContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_criteriaContainer)) {
            return false;
        }

        foreach ($_criteriaContainer as $_key => $_val) {
            if (!isset($_val['name']) || !isset($_val['ruleop'])) {
                continue;
            }

            if (!isset($_val['rulematch'])) {
                $_val['rulematch'] = '';
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'parserrulecriteria', array('parserruleid' => (int)($this->GetParserRuleID()),
                'name' => $_val['name'], 'ruleop' => (int)($_val['ruleop']), 'rulematch' => strval($_val['rulematch']), 'rulematchtype' => (int)($_val['rulematchtype'])), 'INSERT');
        }

        return true;
    }

    /**
     * Insert the given Actions in the currently loaded rule
     *
     * @author Varun Shoor
     *
     * @param array $_actionContainer The Action Container
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function InsertActions($_actionContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_actionContainer)) {
            return false;
        }

        foreach ($_actionContainer as $_key => $_val) {
            if (!isset($_val['name']) || (!isset($_val['typeid']) && !isset($_val['typedata']) && !isset($_val['typechar']))) {
                continue;
            }

            if (!isset($_val['typeid'])) {
                $_val['typeid'] = '0';
            }

            if (!isset($_val['typedata'])) {
                $_val['typedata'] = '';
            }

            if (!isset($_val['typechar'])) {
                $_val['typechar'] = '';
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'parserruleactions', array('parserruleid' => (int)($this->GetParserRuleID()),
                'name' => $_val['name'], 'typeid' => (int)($_val['typeid']), 'typedata' => strval($_val['typedata']),
                'typechar' => strval($_val['typechar'])), 'INSERT');
        }

        return true;
    }

    /**
     * Clear all Criteria associated with this Rule
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function ClearCriteria()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::ClearCriteriaList(array($this->GetParserRuleID()));

        return true;
    }

    /**
     * Clear all Actions associated with this Rule
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function ClearActions()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::ClearActionsList(array($this->GetParserRuleID()));

        return true;
    }

    /**
     * Retrieve the Actions in a Container Array
     *
     * @author Varun Shoor
     * @return mixed "_actionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     */
    public function GetActionContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_actionContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserruleactions WHERE parserruleid = '" . (int)($this->GetParserRuleID()) .
            "'");
        while ($this->Database->NextRecord()) {
            $_actionContainer[$this->Database->Record['parserruleactionid']] = $this->Database->Record;
        }

        return $_actionContainer;
    }

    /**
     * Clears all Criterias associated with given parser rules
     *
     * @author Varun Shoor
     *
     * @param array $_parserRuleIDList The Parser Rule ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected static function ClearCriteriaList($_parserRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserRuleIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserrulecriteria WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) .
            ")");

        return true;
    }

    /**
     * Clears all actions associated with given parser rules
     *
     * @author Varun Shoor
     *
     * @param array $_parserRuleIDList The Parser Rule ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected static function ClearActionsList($_parserRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserRuleIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserruleactions WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) .
            ")");

        return true;
    }

    /**
     * Create a new Parser Rule
     *
     * @author Varun Shoor
     *
     * @param string $_ruleTitle The Rule Title
     * @param bool $_isEnabled Whether this Rule is Enabled by Default
     * @param int $_sortOrder The Rule Execution Order
     * @param int $_ruleType The Rule Type
     * @param int $_matchType The Rule Match Type (AND/OR)
     * @param bool $_stopProcessing Whether the system should stop processing rules if this one matches successfully
     * @param array $_criteriaContainer The Rule Criteria Container
     * @param array $_actionsContainer The Rule Actions Container
     *
     * @return mixed "Parser\Models\Rule\SWIFT_ParserRule" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Data is Empty or Invalid
     */
    public static function Create($_ruleTitle, $_isEnabled, $_sortOrder, $_ruleType, $_matchType, $_stopProcessing, $_criteriaContainer, $_actionsContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ruleTitle) || empty($_ruleType) || empty($_matchType) || !_is_array($_criteriaContainer) || !_is_array($_actionsContainer)) {
            throw new SWIFT_Rule_Exception('Invalid Data Specified');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserrules', array('title' => $_ruleTitle, 'isenabled' => (int)($_isEnabled),
            'dateline' => DATENOW, 'sortorder' => $_sortOrder, 'ruletype' => $_ruleType, 'matchtype' => $_matchType,
            'stopprocessing' => (int)($_stopProcessing)), 'INSERT');

        $_parserRuleID = $_SWIFT->Database->Insert_ID();
        if (!$_parserRuleID) {
            throw new SWIFT_Rule_Exception('Unable to Create Parser Rule');
        }

        $_SWIFT_ParserRuleObject = new SWIFT_ParserRule($_parserRuleID);
        if (!$_SWIFT_ParserRuleObject instanceof SWIFT_ParserRule || !$_SWIFT_ParserRuleObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Rule_Exception('Unable to Load Parser Rule Object');
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_ParserRuleObject->InsertCriteria($_criteriaContainer);
        $_SWIFT_ParserRuleObject->InsertActions($_actionsContainer);

        self::RebuildCache();

        return $_SWIFT_ParserRuleObject;
    }

    /**
     * Update the Parser Rule Record
     *
     * @author Varun Shoor
     *
     * @param string $_ruleTitle The Rule Title
     * @param bool $_isEnabled Whether this Rule is Enabled by Default
     * @param int $_sortOrder The Rule Execution Order
     * @param int $_ruleType The Rule Type
     * @param int $_matchType The Rule Match Type (AND/OR)
     * @param bool $_stopProcessing Whether the system should stop processing rules if this one matches successfully
     * @param array $_criteriaContainer The Rule Criteria Container
     * @param array $_actionsContainer The Rule Actions Container
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Class is not Loaded or if the data is empty/invalod
     */
    public function Update($_ruleTitle, $_isEnabled, $_sortOrder, $_ruleType, $_matchType, $_stopProcessing, $_criteriaContainer, $_actionsContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ruleTitle) || empty($_ruleType) || empty($_matchType) || !_is_array($_criteriaContainer) || !_is_array($_actionsContainer)) {
            throw new SWIFT_Rule_Exception('Invalid Data Specified');
        }

        $this->UpdatePool('title', $_ruleTitle);
        $this->UpdatePool('isenabled', (int)($_isEnabled));
        $this->UpdatePool('dateline', DATENOW);
        $this->UpdatePool('sortorder', $_sortOrder);
        $this->UpdatePool('ruletype', $_ruleType);
        $this->UpdatePool('matchtype', $_matchType);
        $this->UpdatePool('stopprocessing', (int)($_stopProcessing));

        $this->ProcessUpdatePool();

        $this->ClearCriteria();
        $this->ClearActions();

        $this->InsertCriteria($_criteriaContainer);
        $this->InsertActions($_actionsContainer);

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Parser Rule record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rule_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetParserRuleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Parser Rules
     *
     * @author Varun Shoor
     *
     * @param array $_parserRuleIDList The Parser Rule ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_parserRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserRuleIDList)) {
            return false;
        }

        $_finalParserRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_finalParserRuleIDList[] = $_SWIFT->Database->Record['parserruleid'];

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelprules'), count($_finalParserRuleIDList)), $_SWIFT->Language->Get('msgdelprules') .
            '<br />' . $_finalText);

        if (!count($_finalParserRuleIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid IN (" . BuildIN($_finalParserRuleIDList) . ")");

        self::ClearCriteriaList($_finalParserRuleIDList);
        self::ClearActionsList($_finalParserRuleIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Enable a list of Parser Rules
     *
     * @author Varun Shoor
     *
     * @param array $_parserRuleIDList The Parser Rule ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function EnableList($_parserRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserRuleIDList)) {
            return false;
        }

        $_finalParserRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['isenabled'] == '1') {
                continue;
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_finalParserRuleIDList[] = $_SWIFT->Database->Record['parserruleid'];

            $_index++;
        }

        if (!count($_finalParserRuleIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titleenableprules'), count($_finalParserRuleIDList)), $_SWIFT->Language->Get('msgenableprules') .
            '<br />' . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserrules', array('isenabled' => '1'), 'UPDATE', "parserruleid IN (" .
            BuildIN($_finalParserRuleIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Disable a list of Parser Rules
     *
     * @author Varun Shoor
     *
     * @param array $_parserRuleIDList The Parser Rule ID List
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DisableList($_parserRuleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parserRuleIDList)) {
            return false;
        }

        $_finalParserRuleIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrules WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['isenabled'] == '0') {
                continue;
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';

            $_finalParserRuleIDList[] = $_SWIFT->Database->Record['parserruleid'];

            $_index++;
        }

        if (!count($_finalParserRuleIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledisableprules'), count($_finalParserRuleIDList)), $_SWIFT->Language->Get('msgdisableprules') . '<br />' . $_finalText);

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'parserrules', array('isenabled' => '0'), 'UPDATE', "parserruleid IN (" .
            BuildIN($_finalParserRuleIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Return the Criteria for this Rule
     *
     * @author Varun Shoor
     * @return mixed "_criteriaPointer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetCriteriaPointer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_criteriaPointer = array();

        $_criteriaPointer[self::PARSER_SENDERNAME]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_SENDERNAME);
        $_criteriaPointer[self::PARSER_SENDERNAME]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_SENDERNAME);
        $_criteriaPointer[self::PARSER_SENDERNAME]['op'] = 'string';
        $_criteriaPointer[self::PARSER_SENDERNAME]['field'] = 'text';

        $_criteriaPointer[self::PARSER_SENDEREMAIL]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_SENDEREMAIL);
        $_criteriaPointer[self::PARSER_SENDEREMAIL]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_SENDEREMAIL);
        $_criteriaPointer[self::PARSER_SENDEREMAIL]['op'] = 'string';
        $_criteriaPointer[self::PARSER_SENDEREMAIL]['field'] = 'text';

        $_criteriaPointer[self::PARSER_DESTINATIONNAME]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_DESTINATIONNAME);
        $_criteriaPointer[self::PARSER_DESTINATIONNAME]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_DESTINATIONNAME);
        $_criteriaPointer[self::PARSER_DESTINATIONNAME]['op'] = 'string';
        $_criteriaPointer[self::PARSER_DESTINATIONNAME]['field'] = 'text';

        $_criteriaPointer[self::PARSER_DESTINATIONEMAIL]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_DESTINATIONEMAIL);
        $_criteriaPointer[self::PARSER_DESTINATIONEMAIL]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_DESTINATIONEMAIL);
        $_criteriaPointer[self::PARSER_DESTINATIONEMAIL]['op'] = 'string';
        $_criteriaPointer[self::PARSER_DESTINATIONEMAIL]['field'] = 'text';

        $_criteriaPointer[self::PARSER_REPLYTONAME]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_REPLYTONAME);
        $_criteriaPointer[self::PARSER_REPLYTONAME]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_REPLYTONAME);
        $_criteriaPointer[self::PARSER_REPLYTONAME]['op'] = 'string';
        $_criteriaPointer[self::PARSER_REPLYTONAME]['field'] = 'text';

        $_criteriaPointer[self::PARSER_REPLYTOEMAIL]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_REPLYTOEMAIL);
        $_criteriaPointer[self::PARSER_REPLYTOEMAIL]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_REPLYTOEMAIL);
        $_criteriaPointer[self::PARSER_REPLYTOEMAIL]['op'] = 'string';
        $_criteriaPointer[self::PARSER_REPLYTOEMAIL]['field'] = 'text';

        $_criteriaPointer[self::PARSER_SUBJECT]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_SUBJECT);
        $_criteriaPointer[self::PARSER_SUBJECT]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_SUBJECT);
        $_criteriaPointer[self::PARSER_SUBJECT]['op'] = 'string';
        $_criteriaPointer[self::PARSER_SUBJECT]['field'] = 'text';

        $_criteriaPointer[self::PARSER_RECIPIENTS]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_RECIPIENTS);
        $_criteriaPointer[self::PARSER_RECIPIENTS]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_RECIPIENTS);
        $_criteriaPointer[self::PARSER_RECIPIENTS]['op'] = 'string';
        $_criteriaPointer[self::PARSER_RECIPIENTS]['field'] = 'text';

        $_criteriaPointer[self::PARSER_BODY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_BODY);
        $_criteriaPointer[self::PARSER_BODY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_BODY);
        $_criteriaPointer[self::PARSER_BODY]['op'] = 'string';
        $_criteriaPointer[self::PARSER_BODY]['field'] = 'text';

        $_criteriaPointer[self::PARSER_BODYSIZE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_BODYSIZE);
        $_criteriaPointer[self::PARSER_BODYSIZE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_BODYSIZE);
        $_criteriaPointer[self::PARSER_BODYSIZE]['op'] = 'int';
        $_criteriaPointer[self::PARSER_BODYSIZE]['field'] = 'int';

        $_criteriaPointer[self::PARSER_TEXTBODY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TEXTBODY);
        $_criteriaPointer[self::PARSER_TEXTBODY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TEXTBODY);
        $_criteriaPointer[self::PARSER_TEXTBODY]['op'] = 'string';
        $_criteriaPointer[self::PARSER_TEXTBODY]['field'] = 'text';

        $_criteriaPointer[self::PARSER_HTMLBODY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_HTMLBODY);
        $_criteriaPointer[self::PARSER_HTMLBODY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_HTMLBODY);
        $_criteriaPointer[self::PARSER_HTMLBODY]['op'] = 'string';
        $_criteriaPointer[self::PARSER_HTMLBODY]['field'] = 'text';

        $_criteriaPointer[self::PARSER_TEXTBODYSIZE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TEXTBODYSIZE);
        $_criteriaPointer[self::PARSER_TEXTBODYSIZE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TEXTBODYSIZE);
        $_criteriaPointer[self::PARSER_TEXTBODYSIZE]['op'] = 'int';
        $_criteriaPointer[self::PARSER_TEXTBODYSIZE]['field'] = 'int';

        $_criteriaPointer[self::PARSER_HTMLBODYSIZE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_HTMLBODYSIZE);
        $_criteriaPointer[self::PARSER_HTMLBODYSIZE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_HTMLBODYSIZE);
        $_criteriaPointer[self::PARSER_HTMLBODYSIZE]['op'] = 'int';
        $_criteriaPointer[self::PARSER_HTMLBODYSIZE]['field'] = 'int';

        $_criteriaPointer[self::PARSER_ATTACHMENTNAME]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_ATTACHMENTNAME);
        $_criteriaPointer[self::PARSER_ATTACHMENTNAME]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_ATTACHMENTNAME);
        $_criteriaPointer[self::PARSER_ATTACHMENTNAME]['op'] = 'string';
        $_criteriaPointer[self::PARSER_ATTACHMENTNAME]['field'] = 'text';

        $_criteriaPointer[self::PARSER_ATTACHMENTSIZE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_ATTACHMENTSIZE);
        $_criteriaPointer[self::PARSER_ATTACHMENTSIZE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_ATTACHMENTSIZE);
        $_criteriaPointer[self::PARSER_ATTACHMENTSIZE]['op'] = 'int';
        $_criteriaPointer[self::PARSER_ATTACHMENTSIZE]['field'] = 'int';

        $_criteriaPointer[self::PARSER_TOTALATTACHMENTSIZE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TOTALATTACHMENTSIZE);
        $_criteriaPointer[self::PARSER_TOTALATTACHMENTSIZE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TOTALATTACHMENTSIZE);
        $_criteriaPointer[self::PARSER_TOTALATTACHMENTSIZE]['op'] = 'int';
        $_criteriaPointer[self::PARSER_TOTALATTACHMENTSIZE]['field'] = 'int';

        $_criteriaPointer[self::PARSER_ISREPLY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_ISREPLY);
        $_criteriaPointer[self::PARSER_ISREPLY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_ISREPLY);
        $_criteriaPointer[self::PARSER_ISREPLY]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_ISREPLY]['field'] = 'bool';

        $_criteriaPointer[self::PARSER_ISSTAFFREPLY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_ISSTAFFREPLY);
        $_criteriaPointer[self::PARSER_ISSTAFFREPLY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_ISSTAFFREPLY);
        $_criteriaPointer[self::PARSER_ISSTAFFREPLY]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_ISSTAFFREPLY]['field'] = 'bool';

        $_criteriaPointer[self::PARSER_ISTHIRDPARTY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_ISTHIRDPARTY);
        $_criteriaPointer[self::PARSER_ISTHIRDPARTY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_ISTHIRDPARTY);
        $_criteriaPointer[self::PARSER_ISTHIRDPARTY]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_ISTHIRDPARTY]['field'] = 'bool';

        $_criteriaPointer[self::PARSER_TICKETEMAILQUEUE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETEMAILQUEUE);
        $_criteriaPointer[self::PARSER_TICKETEMAILQUEUE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETEMAILQUEUE);
        $_criteriaPointer[self::PARSER_TICKETEMAILQUEUE]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETEMAILQUEUE]['field'] = 'custom';

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_criteriaPointer[self::PARSER_BAYESCATEGORY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_BAYESCATEGORY);
            $_criteriaPointer[self::PARSER_BAYESCATEGORY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_BAYESCATEGORY);
            $_criteriaPointer[self::PARSER_BAYESCATEGORY]['op'] = 'bool';
            $_criteriaPointer[self::PARSER_BAYESCATEGORY]['field'] = 'custom';
        }

        $_criteriaPointer[self::PARSER_TICKETSTATUS]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETSTATUS);
        $_criteriaPointer[self::PARSER_TICKETSTATUS]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETSTATUS);
        $_criteriaPointer[self::PARSER_TICKETSTATUS]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETSTATUS]['field'] = 'custom';

        $_criteriaPointer[self::PARSER_TICKETTYPE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETTYPE);
        $_criteriaPointer[self::PARSER_TICKETTYPE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETTYPE);
        $_criteriaPointer[self::PARSER_TICKETTYPE]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETTYPE]['field'] = 'custom';

        $_criteriaPointer[self::PARSER_TICKETPRIORITY]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETPRIORITY);
        $_criteriaPointer[self::PARSER_TICKETPRIORITY]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETPRIORITY);
        $_criteriaPointer[self::PARSER_TICKETPRIORITY]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETPRIORITY]['field'] = 'custom';

        $_criteriaPointer[self::PARSER_TICKETUSERGROUP]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETUSERGROUP);
        $_criteriaPointer[self::PARSER_TICKETUSERGROUP]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETUSERGROUP);
        $_criteriaPointer[self::PARSER_TICKETUSERGROUP]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETUSERGROUP]['field'] = 'custom';

        $_criteriaPointer[self::PARSER_TICKETDEPARTMENT]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETDEPARTMENT);
        $_criteriaPointer[self::PARSER_TICKETDEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETDEPARTMENT);
        $_criteriaPointer[self::PARSER_TICKETDEPARTMENT]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETDEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::PARSER_TICKETOWNER]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETOWNER);
        $_criteriaPointer[self::PARSER_TICKETOWNER]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETOWNER);
        $_criteriaPointer[self::PARSER_TICKETOWNER]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETOWNER]['field'] = 'custom';

        $_criteriaPointer[self::PARSER_TICKETFLAGTYPE]['title'] = $_SWIFT->Language->Get('p' . self::PARSER_TICKETFLAGTYPE);
        $_criteriaPointer[self::PARSER_TICKETFLAGTYPE]['desc'] = $_SWIFT->Language->Get('desc_p' . self::PARSER_TICKETFLAGTYPE);
        $_criteriaPointer[self::PARSER_TICKETFLAGTYPE]['op'] = 'bool';
        $_criteriaPointer[self::PARSER_TICKETFLAGTYPE]['field'] = 'custom';

        return $_criteriaPointer;
    }

    /**
     * Extends the $_criteria array with custom field data (like departments etc.)
     *
     * @author Varun Shoor
     *
     * @param array $_criteriaPointer The Criteria Pointer
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function ExtendCustomCriteria(&$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            // ======= TICKET STATUS =======
            $_field = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY category ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_field[] = array('title' => $_SWIFT->Database->Record['category'], 'contents' => $_SWIFT->Database->Record['bayescategoryid']);
            }

            if (!count($_field)) {
                $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
            }

            $_criteriaPointer[self::PARSER_BAYESCATEGORY]['fieldcontents'] = $_field;

            // ======= TICKET STATUS =======
            $_field = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY displayorder ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['ticketstatusid']);
            }

            if (!count($_field)) {
                $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
            }

            $_criteriaPointer[self::PARSER_TICKETSTATUS]['fieldcontents'] = $_field;

            // ======= TICKET TYPE =======
            $_field = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY displayorder ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tickettypeid']);
            }

            if (!count($_field)) {
                $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
            }

            $_criteriaPointer[self::PARSER_TICKETTYPE]['fieldcontents'] = $_field;

            // ======= TICKET PRIORITY =======
            $_field = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['priorityid']);
            }

            if (!count($_field)) {
                $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
            }

            $_criteriaPointer[self::PARSER_TICKETPRIORITY]['fieldcontents'] = $_field;
        }

        // ======= USER GROUPS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['usergroupid']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::PARSER_TICKETUSERGROUP]['fieldcontents'] = $_field;

        // ======= DEPARTMENT =======
        $_field = array();

        $_departmentMapOptions = SWIFT_Department::GetDepartmentMapOptions(false, APP_TICKETS);

        foreach ($_departmentMapOptions as $_key => $_val) {
            $_field[] = array('title' => $_val['title'], 'contents' => $_val['value']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('language'), 'contents' => '0');
        }

        $_criteriaPointer[self::PARSER_TICKETDEPARTMENT]['fieldcontents'] = $_field;

        // ======= TICKET OWNER =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('prunassigned'), 'contents' => '0');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_field[] = array('title' => $_SWIFT->Database->Record['fullname'], 'contents' => $_SWIFT->Database->Record['staffid']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::PARSER_TICKETOWNER]['fieldcontents'] = $_field;

        // ======= EMAIL QUEUE ID =======
        if (SWIFT_App::IsInstalled(APP_PARSER)) {
            $_field = array();
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues ORDER BY email ASC");
            while ($_SWIFT->Database->NextRecord()) {
                $_field[] = array('title' => $_SWIFT->Database->Record['email'], 'contents' => $_SWIFT->Database->Record['emailqueueid']);
            }

            if (!count($_field)) {
                $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
            }

            $_criteriaPointer[self::PARSER_TICKETEMAILQUEUE]['fieldcontents'] = $_field;
        }

        SWIFT_Loader::LoadLibrary('Flag:TicketFlag', APP_TICKETS);

        $_TicketFlagObject = new SWIFT_TicketFlag();

        // ======= FLAG TYPE =======
        $_field = array();

        $_flagContainer = $_TicketFlagObject->GetFlagList();
        foreach ($_flagContainer as $_key => $_val) {
            $_field[] = array('title' => $_val, 'contents' => $_key);
        }

        $_criteriaPointer[self::PARSER_TICKETFLAGTYPE]['fieldcontents'] = $_field;

        return true;
    }

    /**
     * Check to see if its a valid rule type
     *
     * @author Varun Shoor
     *
     * @param mixed $_ruleType The Rule Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidRuleType($_ruleType)
    {
        if ($_ruleType == self::TYPE_PREPARSE || $_ruleType == self::TYPE_POSTPARSE) {
            return true;
        }

        return false;
    }

    /**
     * Get the label for the rule type
     *
     * @author Varun Shoor
     *
     * @param mixed $_ruleType The Rule Type
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function GetRuleTypeLabel($_ruleType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidRuleType($_ruleType)) {
            return false;
        }

        if ($_ruleType == self::TYPE_PREPARSE) {
            return $_SWIFT->Language->Get('ipreparse');
        } else if ($_ruleType == self::TYPE_POSTPARSE) {
            return $_SWIFT->Language->Get('ipostparse');
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Rebuilds the Parser Rule Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = $_parserRuleIDList = array();

        $_index = 0;

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrules ORDER BY sortorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_parserRuleIDList[] = $_SWIFT->Database->Record["parserruleid"];
            $_cache[$_SWIFT->Database->Record["parserruleid"]] = $_SWIFT->Database->Record;
            $_cache[$_SWIFT->Database->Record["parserruleid"]]["_criteria"] = array();
            $_cache[$_SWIFT->Database->Record["parserruleid"]]["_criteriaPointer"] = array();
            $_cache[$_SWIFT->Database->Record["parserruleid"]]["_actions"] = array();
            $_cache[$_SWIFT->Database->Record["parserruleid"]]["_actionsPointer"] = array();
        }

        if (count($_parserRuleIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrulecriteria WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_ruleMatchType = $_SWIFT->Database->Record['rulematchtype'];
                // An old entry?
                if (empty($_ruleMatchType) && $_cache[$_SWIFT->Database->Record['parserruleid']]['matchtype'] != SWIFT_Rules::RULE_MATCHEXTENDED) {
                    $_ruleMatchType = $_cache[$_SWIFT->Database->Record['parserruleid']]['matchtype'];
                }

                $_cache[$_SWIFT->Database->Record["parserruleid"]]["_criteria"][$_SWIFT->Database->Record['parserrulecriteriaid']] = array($_SWIFT->Database->Record['name'], $_SWIFT->Database->Record['ruleop'], $_SWIFT->Database->Record['rulematch'], $_ruleMatchType);

                $_cache[$_SWIFT->Database->Record["parserruleid"]]["_criteriaPointer"][$_SWIFT->Database->Record['parserrulecriteriaid']] = $_SWIFT->Database->Record;
            }

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserruleactions WHERE parserruleid IN (" . BuildIN($_parserRuleIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                $_cache[$_SWIFT->Database->Record["parserruleid"]]["_actions"][$_SWIFT->Database->Record['parserruleactionid']] = array($_SWIFT->Database->Record['name'], $_SWIFT->Database->Record['typeid'], $_SWIFT->Database->Record['typedata'], $_SWIFT->Database->Record['typechar']);
                $_cache[$_SWIFT->Database->Record["parserruleid"]]["_actionsPointer"][$_SWIFT->Database->Record['parserruleactionid']] = $_SWIFT->Database->Record;
            }
        }

        $_SWIFT->Cache->Update('parserrulecache', $_cache);

        return true;
    }

    /**
     * Retrieves the Criteria Value
     *
     * @author Varun Shoor
     *
     * @param string $_criteriaName The Criteria Name Pointer
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetCriteriaValue($_criteriaName)
    {
        if (isset($this->_parserRuleProperties[$_criteriaName])) {
            return $this->_parserRuleProperties[$_criteriaName];
        }

        return false;
    }

    /**
     * Set the Rule Properties
     *
     * @author Varun Shoor
     *
     * @param array $_parserRuleProperties The Parser Rule Properties
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetRuleProperties($_parserRuleProperties)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_parserRuleProperties = $_parserRuleProperties;

        return true;
    }

    /**
     * Executes all rules with a given type set
     *
     * @author Varun Shoor
     *
     * @param int $_ruleType The Rules Type
     * @param array $_parserRuleProperties The Parser Rule Properties
     *
     * @return mixed "_actions" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rule_Exception
     */
    public static function ExecuteAllRules($_ruleType, $_parserRuleProperties)
    {
        $_SWIFT = SWIFT::GetInstance();

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4484 'Stop processing other rules' setting gets violated in Email Parser Rules
         *
         * Comment - We need to obey the Pre rules further execution to post parsing
         */
        $_stopExecution = false;

        $_parserRuleCache = $_SWIFT->Cache->Get('parserrulecache');
        if (!self::IsValidRuleType($_ruleType) || !$_parserRuleCache || !_is_array($_parserRuleCache)) {
            return false;
        }

        $_ruleContainer = $_returnActions = array();
        foreach ($_parserRuleCache as $_parserRuleID => $_parserRuleContainer) {
            if ($_parserRuleContainer['ruletype'] == $_ruleType && $_parserRuleContainer['isenabled'] == '1') {
                $_ruleContainer[$_parserRuleID] = $_parserRuleContainer;
            }
        }

        foreach ($_ruleContainer as $_parserRuleID => $_parserRuleContainer) {

            $_SWIFT_ParserRuleObject = new SWIFT_ParserRule($_parserRuleID, $_parserRuleContainer);
            if (!$_SWIFT_ParserRuleObject instanceof SWIFT_ParserRule || !$_SWIFT_ParserRuleObject->GetIsClassLoaded()) {
                continue;
            }

            $_SWIFT_ParserRuleObject->SetRuleProperties($_parserRuleProperties);

            $_ruleResult = $_SWIFT_ParserRuleObject->Execute();
            if ($_ruleResult) {
                $_returnActions = array_merge($_returnActions, $_SWIFT_ParserRuleObject->GetProperty('_actionsPointer'));

                if ($_SWIFT_ParserRuleObject->GetProperty('stopprocessing') == true) {
                    $_stopExecution = true;
                    break;
                }
            }
        }

        return array($_returnActions, $_stopExecution);
    }

}
