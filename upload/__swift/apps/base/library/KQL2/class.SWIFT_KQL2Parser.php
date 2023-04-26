<?php
/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-QuickSupport Singapore Pte. Ltd.h Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace Base\Library\KQL2;

use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\KQL2\SWIFT_KQL2;
use Base\Library\KQL2\SWIFT_KQL2_Exception;
use Base\Library\KQL2\SWIFT_KQL2Lexer;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The KQL Parser
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2Parser extends SWIFT_Library
{
    /**
     * Token Analysis State Variables
     */
    protected $_tokens = array();
    protected $_index = false;
    protected $_current = false;

    protected $_clausesContainer = array();
    protected $_clausesCache = array();

    protected $_operatorsContainer = array();
    protected $_operatorsCache = array();

    protected $_functionsContainer = array(); // FIXME: GetFunctionProperties($_functionName) + for all similar containers

    protected $_selectorsContainer = array();

    protected $_preModifiersContainer = array();
    protected $_postModifiersContainer = array();

    protected $_identifiersContainer = array();

    protected $_variablesContainer = array();

    /**
     * Month Name to Index Map
     */

    static private $_monthNamesMap = array(
        'January' => 1,
        'February' => 2,
        'March' => 3,
        'April' => 4,
        'May' => 5,
        'June' => 6,
        'July' => 7,
        'August' => 8,
        'September' => 9,
        'October' => 10,
        'November' => 11,
        'December' => 12
    );

    static private $_monthShortNamesMap = array(
        'Jan' => 1,
        'Feb' => 2,
        'Mar' => 3,
        'Apr' => 4,
        'May' => 5,
        'Jun' => 6,
        'Jul' => 7,
        'Aug' => 8,
        'Sep' => 9,
        'Oct' => 10,
        'Nov' => 11,
        'Dec' => 12
    );

    /**
     * KQL Schema Type to KQL Stype Map
     */

    static protected $_kqlSchemaFieldTypeMap = array(
        SWIFT_KQLSchema::FIELDTYPE_INT => SWIFT_KQL2::DATA_INTEGER,
        SWIFT_KQLSchema::FIELDTYPE_FLOAT => SWIFT_KQL2::DATA_FLOAT,
        SWIFT_KQLSchema::FIELDTYPE_STRING => SWIFT_KQL2::DATA_STRING,
        SWIFT_KQLSchema::FIELDTYPE_BOOL => SWIFT_KQL2::DATA_BOOLEAN,
        SWIFT_KQLSchema::FIELDTYPE_UNIXTIME => SWIFT_KQL2::DATA_UNIXDATE,
        SWIFT_KQLSchema::FIELDTYPE_SECONDS => SWIFT_KQL2::DATA_SECONDS,
        SWIFT_KQLSchema::FIELDTYPE_CUSTOM => SWIFT_KQL2::DATA_OPTION
    );

    /**
     * KQL Query State Variables
     */
    protected $_statement = array();
    protected $_tableList = array();
    protected $_primaryTableName = false;
    protected $_currentClause = false;

    /**
     * This array stores combined KQL Schema
     */
    protected $_schemaContainer = array();

    /**
     * This array contains the map of table label > table name
     */
    protected $_tableSchemaMap = array();

    /**
     * This array stored custom fields
     */
    protected $_customFieldTableMap = array();

    /**
     * Cache arrays for custom fields
     */
    protected $_customFieldIDMap = array();        /* by ID */
    protected $_customFieldNameMap = array();    /* by name */
    protected $_customFieldTitleMap = array();    /* by title */

    /**
     * Stores Alias Names Specified by User
     */
    protected $_inlineVariables = array();

    /**
     * Custom fields groups cache
     */
    protected $_customFieldGroupTitleMap = array();

    /**
     * @var SWIFT_KQL2Lexer
     */
    protected $Lexer;

    const TOKENS_HISTORY = 3;

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $_schemaContainer = array();
        $_clausesContainer = array();
        $_operatorsContainer = array();
        $_functionsContainer = array();
        $_selectorsContainer = array();
        $_preModifiersContainer = array();
        $_postModifiersContainer = array();
        $_identifiersContainer = array();
        $_variablesContainer = array();

        $_schemaObjects = SWIFT_KQLSchema::GetSchemaObjects(); // FIXME: GetCombinedSchema?
        foreach ($_schemaObjects as $_SWIFT_KQLSchemaObject) {

            // FIXME: Add GetCombined* functions and implement checks

            $_schemaContainer = array_merge($_schemaContainer, $_SWIFT_KQLSchemaObject->GetSchema());
            $_clausesContainer = array_merge($_clausesContainer, $_SWIFT_KQLSchemaObject->GetClauses());
            $_operatorsContainer = array_merge($_operatorsContainer, $_SWIFT_KQLSchemaObject->GetOperators());
            $_functionsContainer = array_merge($_functionsContainer, $_SWIFT_KQLSchemaObject->GetFunctions());
            $_selectorsContainer = array_merge($_selectorsContainer, $_SWIFT_KQLSchemaObject->GetSelectors());
            $_preModifiersContainer = array_merge($_preModifiersContainer, $_SWIFT_KQLSchemaObject->GetPreModifiers());
            $_postModifiersContainer = array_merge($_postModifiersContainer, $_SWIFT_KQLSchemaObject->GetPostModifiers());
            $_identifiersContainer = array_merge($_identifiersContainer, $_SWIFT_KQLSchemaObject->GetIdentifiers());
            $_variablesContainer = array_merge($_variablesContainer, $_SWIFT_KQLSchemaObject->GetVariables());

        }

        $this->_schemaContainer = $_schemaContainer;
        $this->_clausesContainer = $_clausesContainer;
        $this->_operatorsContainer = $_operatorsContainer;
        $this->_functionsContainer = $_functionsContainer;
        $this->_selectorsContainer = $_selectorsContainer;
        $this->_preModifiersContainer = $_preModifiersContainer;
        $this->_postModifiersContainer = $_postModifiersContainer;
        $this->_identifiersContainer = $_identifiersContainer;
        $this->_variablesContainer = $_variablesContainer;

        $this->CacheChunks();

        $this->ProcessTableSchemaMap();
    }

    /**
     * Cache Chunks of e.g. Clauses and Operators
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CacheChunks()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($this->_clausesCache)) {
            foreach ($this->_clausesContainer as $_clauseName => $_clauseContainer) {
                $_clauseChunks = explode(' ', $_clauseName);
                for ($_i = 0; $_i < count($_clauseChunks); $_i++) {
                    $this->_clausesCache[implode('', array_slice($_clauseChunks, 0, $_i + 1))] = $_clauseName;
                }
            }
        }

        if (empty($this->_operatorsCache)) {
            foreach ($this->_operatorsContainer as $_operator => $_operatorContainer) {
                $_operatorChunks = explode(' ', $_operator);
                for ($_i = 0; $_i < count($_operatorChunks); $_i++) {
                    $this->_operatorsCache[implode('', array_slice($_operatorChunks, 0, $_i + 1))] = $_operator;
                }
            }
        }

        return true;
    }

    /**
     * Process the Table Schema Map
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTableSchemaMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach ($this->_schemaContainer as $_tableName => $_tableContainer) {
            if (isset($_tableContainer[SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
                $_labelValue = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableContainer[SWIFT_KQLSchema::SCHEMA_TABLELABEL]));

                $this->_tableSchemaMap[$_labelValue] = $_tableName;
            }

            if (isset($_tableContainer[SWIFT_KQLSchema::SCHEMA_TABLEALIAS])) {
                $_labelValue = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableContainer[SWIFT_KQLSchema::SCHEMA_TABLEALIAS]));

                $this->_tableSchemaMap[$_labelValue] = $_tableName;
            }
        }

        return true;
    }

    /**
     * Gets Group Type IDs by the Table Name
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @return array The Group Type IDs
     */
    public static function GetCustomFieldGroupTypesByTableName($_tableName)
    {
        switch ($_tableName) {
            case 'users':
                return array(SWIFT_CustomFieldGroup::GROUP_USER);

            case 'userorganizations':
                return array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION);

            case 'chatobjects':
                return array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE,
                    SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST);

            case 'tickets':
                return array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                    SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                    SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET);

            case 'tickettimetracks':
                return array(SWIFT_CustomFieldGroup::GROUP_TIMETRACK);

            default:
                break;
        }

        return array();
    }

    /**
     * Fetch Custom Fields and Save into Cache
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param array $_groupTypes
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function BuildCustomFieldsCacheForTable($_tableName, $_groupTypes)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_customFieldTableMap[$_tableName])) {
            return true;
        }

        if (empty($_groupTypes)) {
            return false;
        }

        /**
         * Fetch all custom fields for the table
         */

        $_customFields = array();

        $_sqlExpression = "SELECT customfields.customfieldid AS id, customfields.fieldname AS name, customfields.fieldtype AS type, customfields.title, customfields.encryptindb AS encrypt, customfieldgroups.customfieldgroupid AS group_id, customfieldgroups.title AS group_title
            FROM " . TABLE_PREFIX . "customfields AS customfields
            LEFT JOIN " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups ON (customfieldgroups.customfieldgroupid = customfields.customfieldgroupid)
            WHERE customfieldgroups.grouptype IN (" . BuildIN($_groupTypes) . ")";

        $this->Database->Query($_sqlExpression);
        while ($this->Database->NextRecord()) {
            $_customFields[] = $this->Database->Record;
        }

        $this->_customFieldTableMap[$_tableName] = array();

        foreach ($_customFields as $_customField) {
            $_customField['table'] = $_tableName;

            /**
             * Fetch options
             */

            if (($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_RADIO) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECT) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED)) {

                $_customFieldOptions = array();

                $_sqlExpression = "SELECT customfieldoptionid, optionvalue, parentcustomfieldoptionid
                    FROM " . TABLE_PREFIX . "customfieldoptions
                    WHERE customfieldid = '" . (int)($_customField['id']) . "'
                    ORDER BY parentcustomfieldoptionid ASC, displayorder ASC";

                $this->Database->Query($_sqlExpression);
                while ($this->Database->NextRecord()) {
                    if ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                        if ($this->Database->Record['parentcustomfieldoptionid'] > 0) {
                            if (isset($_customFieldOptions[$this->Database->Record['parentcustomfieldoptionid']])) {
                                $_customFieldOptions[$this->Database->Record['parentcustomfieldoptionid']]['suboptions'][$this->Database->Record['customfieldoptionid']] = array(
                                    'value' => $this->Database->Record['optionvalue']
                                );
                            }
                        } else {
                            $_customFieldOptions[$this->Database->Record['customfieldoptionid']] = array(
                                'value' => $this->Database->Record['optionvalue'],
                                'suboptions' => array()
                            );
                        }
                    } else {
                        $_customFieldOptions[$this->Database->Record['customfieldoptionid']] = $this->Database->Record['optionvalue'];
                    }
                }

                $_customField['options'] = $_customFieldOptions;
            }

            /**
             * Populate internal cache arrays
             */

            if (!isset($this->_customFieldTableMap[$_tableName][$_customField['group_id']])) {
                $this->_customFieldTableMap[$_tableName][$_customField['group_id']] = array();
            }

            $this->_customFieldTableMap[$_tableName][$_customField['group_id']][$_customField['id']] = $_customField;

            $this->_customFieldIDMap[$_customField['id']] = $_customField;
            $this->_customFieldNameMap[$_customField['name']] = $_customField;

            $_customFieldTitle = mb_strtolower($_customField['title']);
            if (isset($this->_customFieldTitleMap[$_customFieldTitle])) {
                if (isset($this->_customFieldTitleMap[$_customFieldTitle]['id'])) {
                    $this->_customFieldTitleMap[$_customFieldTitle] = array($this->_customFieldTitleMap[$_customFieldTitle]);
                }
                $this->_customFieldTitleMap[$_customFieldTitle][] = $_customField;
            } else {
                $this->_customFieldTitleMap[$_customFieldTitle] = $_customField;
            }

            $_customFieldGroupTitle = mb_strtolower($_customField['group_title']);
            if (!isset($this->_customFieldGroupTitleMap[$_customFieldGroupTitle])) {
                $this->_customFieldGroupTitleMap[$_customFieldGroupTitle] = $_customField['group_id'];
            }
        }

        return true;
    }

    /**
     * Initialize Parser State Properties
     *
     * @author Andriy Lesyuk
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function InitializeState()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_tokens = array();
        $this->_index = false;
        $this->_current = false;

        $this->_statement = array();
        $this->_tableList = array();
        $this->_primaryTableName = false;

        return true;
    }

    /**
     * Initialize Lexer
     *
     * @author Andriy Lesyuk
     * @param string $_kql
     * @return object The Lexer
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function InitializeLexer($_kql)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Lexer = new SWIFT_KQL2Lexer($_kql);

        return $this->Lexer;
    }

    /**
     * Parses KQL String into Special Array
     *
     * @author Andriy Lesyuk
     * @param string $_kql
     * @param string|false $_primaryTableName
     * @param string $_startClause
     * @return SWIFT_KQL2 The KQL Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Parse($_kql, $_primaryTableName = false, $_startClause = 'SELECT')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->InitializeState();
        $this->InitializeLexer($_kql);

        // Check primary table
        if ($_primaryTableName) {
            $_tableName = $this->GetTableName($_primaryTableName);
            if ($_tableName) {
                $this->_primaryTableName = $_tableName;
            } else {
                throw new SWIFT_KQL2_Exception('Invalid primary table ' . $_primaryTableName);
            }
        }

        // Get first clause
        $this->_currentClause = $this->GetClause();
        if (!$this->_currentClause) {
            if ($_startClause && isset($this->_clausesContainer[$_startClause])) {
                if (is_string($this->_clausesContainer[$_startClause])) { // Alias
                    $this->_currentClause = $this->_clausesContainer[$_startClause];
                } else {
                    $this->_currentClause = $_startClause;
                }
            } else {
                throw new SWIFT_KQL2_Exception('SELECT expected' . $this->GetOffsetDetails(false));
            }
        }

        // Process clauses
        do {
            $_clauseProperties = $this->_clausesContainer[$this->_currentClause];

            if (!isset($this->_statement[$this->_currentClause])) {
                $this->_statement[$this->_currentClause] = array();
            } elseif (!isset($_clauseProperties[SWIFT_KQLSchema::CLAUSE_MULTIPLE]) || (!$_clauseProperties[SWIFT_KQLSchema::CLAUSE_MULTIPLE])) {
                throw new SWIFT_KQL2_Exception('Multiple ' . $this->_currentClause . ' clauses not allowed');
            }

            if (isset($_clauseProperties[SWIFT_KQLSchema::CLAUSE_PARSER])) {
                $methodName = $_clauseProperties[SWIFT_KQLSchema::CLAUSE_PARSER];
                $this->$methodName($this->_currentClause); // TODO: Support array('Class', 'Method')
            } else {
                $this->ParseClause($this->_currentClause);
            }
        } while ($this->_currentClause = $this->GetClause());

        // Check if there are tokens
        $_token = $this->GetToken();
        if ($_token) {
            throw new SWIFT_KQL2_Exception('Expected end of query or clause, got ' . $_token[0] . $this->GetOffsetDetails(false));
        }

        // Create KQL object
        $_SWIFT_KQLObject = new SWIFT_KQL2($this->_statement, $this->_tableList);

        // Copy internal arrays
        $_SWIFT_KQLObject->SetSchema($this->_schemaContainer);
        $_SWIFT_KQLObject->SetCustomFields($this->_customFieldIDMap);
        $_SWIFT_KQLObject->SetClauses($this->_clausesContainer);
        $_SWIFT_KQLObject->SetOperators($this->_operatorsContainer);
        $_SWIFT_KQLObject->SetFunctions($this->_functionsContainer);
        $_SWIFT_KQLObject->SetSelectors($this->_selectorsContainer);
        $_SWIFT_KQLObject->SetPreModifiers($this->_preModifiersContainer);
        $_SWIFT_KQLObject->SetPostModifiers($this->_postModifiersContainer);
        $_SWIFT_KQLObject->SetIdentifiers($this->_identifiersContainer);
        $_SWIFT_KQLObject->SetVariables($this->_variablesContainer);

        return $_SWIFT_KQLObject;
    }

    /**
     * Parses FROM Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_ParseFrom($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableList = array();

        $_previousTable = false;

        do {
            $_table = $this->GetTable();
            if ($_table) {
                $_tableList[$_table] = true;

                if ($this->IsTableRegistered($_table)) {
                    $this->RegisterTable($_table, false, true);
                }

                $_previousTable = $_table;
            } else {
                if ($_previousTable) {
                    $_prevToken = $this->MoveBack();
                    if ($_prevToken && $_prevToken[0] == ',') {
                        throw new SWIFT_KQL2_Exception('Possible redundant comma' . $this->GetOffsetDetails(false));
                    }
                }

                throw new SWIFT_KQL2_Exception('Expected table' . $this->GetOffsetDetails());
            }
        } while ($this->GetComma());

        if (count($_tableList) > 0) {
            $this->_tableList = array_merge($_tableList, $this->_tableList);
        }

        return true;
    }

    /**
     * Parses WHERE Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_ParseWhere($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_clauseName = 'WHERE';

        $_expression = $this->GetExpression();
        if ($_expression) {
            $this->_statement[$_clauseName] = $_expression;
        } else {
            throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
        }

        return true;
    }

    /**
     * Parses LIMIT Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_ParseLimit($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_clauseName = 'LIMIT';

        $_valueExpression = $this->GetNumber();
        if ($_valueExpression) {
            if ($this->GetComma()) {
                $_countExpression = $this->GetNumber();
                if ($_countExpression) {
                    $_limitOffset = $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA];
                    $_limitCount = $_countExpression[SWIFT_KQL2::EXPRESSION_DATA];
                } else {
                    throw new SWIFT_KQL2_Exception('Possible redundant comma' . $this->GetOffsetDetails(false));
                }
            } else {

                $_token = $this->GetToken();
                if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1]) && (mb_strtoupper($_token[0]) == 'OFFSET')) {
                    $this->MoveNext();

                    $_offsetExpression = $this->GetNumber();
                    if ($_offsetExpression) {
                        $_limitOffset = $_offsetExpression[SWIFT_KQL2::EXPRESSION_DATA];
                        $_limitCount = $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA];
                    } else {
                        throw new SWIFT_KQL2_Exception('Expected offset, got ' . $this->GetTokenName($_token[0]) . $this->GetOffsetDetails(false));
                    }

                } else {
                    $_limitOffset = 0;
                    $_limitCount = $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA];
                }
            }

            $this->_statement[$_clauseName] = array($_limitOffset, $_limitCount);
        } else {
            $_token = $this->GetToken();

            throw new SWIFT_KQL2_Exception('Expected rows count or offset, got ' . $this->GetTokenName($_token[0]) . $this->GetOffsetDetails(false));
        }

        return true;
    }

    /**
     * Parses TOTALIZE BY Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_ParseTotalizeBy($_clauseName) // TODO: PostParse for sync with SELECT?
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        static $_aggregateFunctions = array(
            'AVG',
            'COUNT',
            'MAX',
            'MIN',
            'SUM',
        );

        $_rowTitle = false;
        $_rowIndex = count($this->_statement[$_clauseName]);

        $this->_statement[$_clauseName][$_rowIndex] = array();

        // Get title of the grand total row
        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1]) && (mb_strtoupper($_token[0]) == 'TITLE')) {
            $this->MoveNext();

            $_token = $this->GetToken();
            if ($_token && SWIFT_KQL2Lexer::TypeIsString($_token[1])) {
                $this->MoveNext();

                $_rowTitle = $_token[0];

                $this->GetComma();
            } else {
                throw new SWIFT_KQL2_Exception('Expected string' . $this->GetOffsetDetails());
            }
        }

        do {
            $_token = $this->GetToken();

            $_expression = $this->GetExpression();
            if ($_expression) {
                while ($this->GetPostModifierAndModify($_expression, $_clauseName)) {
                }

                if ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] != SWIFT_KQL2::ELEMENT_VALUE) {
                    $_aggregateFunction = false;

                    foreach (SWIFT_KQL2::GetUsedFunctionsFromExpression($_expression) as $_functionName) {
                        if (in_array($_functionName, $_aggregateFunctions)) {
                            $_aggregateFunction = true;
                            break;
                        }
                    }

                    if ($_aggregateFunction == false) {
                        throw new SWIFT_KQL2_Exception('Expected value or expression with an aggregate function' . $this->GetOffsetDetails(false, $_token));
                    }
                }

                // TODO: UNDER '...' (post-modifier?)

                $this->_statement[$_clauseName][$_rowIndex][] = $_expression;

            } else {
                if (count($this->_statement[$_clauseName][$_rowIndex]) > 0) {
                    $_prevToken = $this->MoveBack();
                    if ($_prevToken && $_prevToken[0] == ',') {
                        throw new SWIFT_KQL2_Exception('Possible redundant comma' . $this->GetOffsetDetails(false));
                    }
                }

                throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
            }
        } while ($this->GetComma());

        if ($_rowTitle) {
            array_unshift($this->_statement[$_clauseName][$_rowIndex], $_rowTitle);
        }

        return true;
    }

    /**
     * Parses Clause (SELECT, GROUP BY etc)
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ParseClause($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        do {
            $_expression = $this->GetExpression();
            if ($_expression) {
                while ($this->GetPostModifierAndModify($_expression, $_clauseName)) {
                }
                $this->_statement[$_clauseName][] = $_expression;

            } else {
                if (count($this->_statement[$_clauseName]) > 0) {
                    $_prevToken = $this->MoveBack();
                    if ($_prevToken && $_prevToken[0] == ',') {
                        throw new SWIFT_KQL2_Exception('Possible redundant comma' . $this->GetOffsetDetails(false));
                    }
                }

                throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
            }
        } while ($this->GetComma());

        return true;
    }

    /**
     * Extracts Expression from KQL
     *
     * @author Andriy Lesyuk
     * @return array The Expression Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetExpression()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_negation = $this->GetNegation();

        // Check for pre-modifier
        $_modifierName = $this->GetPreModifier();
        if ($_modifierName) {
            $_modifierProperties = $this->_preModifiersContainer[$_modifierName];
            if (isset($_modifierProperties[SWIFT_KQLSchema::PREMODIFIER_PARSER])) {

                $methodName = $_modifierProperties[SWIFT_KQLSchema::PREMODIFIER_PARSER];
                return $this->$methodName($_modifierName); // TODO: Support also array(class, method)
            } else {
                $this->MoveBack();

                throw new SWIFT_KQL2_Exception('Missing parser for ' . $_modifierName . $this->GetOffsetDetails());
            }
        }

        $_openParenthesis = false;

        // Table.Field, Func() etc.
        $_expression = $this->ParseValue();
        if ($_expression) {
            if ($_negation) {
                $_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE] = true;
            }

            // (..., ...)
        } elseif ($_openParenthesis = $this->GetOpenParenthesis()) {
            $_arrayExpression = false;
            $_expression = array(
                SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_EXPRESSION,
                SWIFT_KQL2::EXPRESSION_DATA => array(),
                SWIFT_KQL2::EXPRESSION_EXTRA => array('_PARENTHESES' => true)
            );

            // TODO: To support nested queries
            /*
            if ($this->GetClause()) {
                $_kql2Parser = new SWIFT_KQL2Parser(...);
                $_kql2Parser->Parse();
            }
            */

            $_expressionFound = false;
            $_expressionExpected = false;
            $_closeParenthesis = false;
            while ($this->GetToken() && !($_closeParenthesis = $this->GetClosedParenthesis())) {
                $_innerExpression = $this->GetExpression();
                if ($_innerExpression) {
                    $_expression[SWIFT_KQL2::EXPRESSION_DATA][] = $_innerExpression;
                    $_expressionFound = true;

                    $_operator = $this->GetOperator();
                    if ($_operator) {
                        $_expression[SWIFT_KQL2::EXPRESSION_DATA][] = $_operator;

                        $_expressionExpected = true;
                        // Create array expression
                    } elseif ($this->GetComma()) {
                        if ($_arrayExpression === false) {
                            $_arrayExpression = array(
                                SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_ARRAY,
                                SWIFT_KQL2::EXPRESSION_DATA => array()
                            );
                        }

                        // Remove nesting level
                        if (count($_expression[SWIFT_KQL2::EXPRESSION_DATA]) == 1) {
                            $_expression = $_expression[SWIFT_KQL2::EXPRESSION_DATA][0];

                            if ($_negation &&
                                isset($_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]) && $_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]) {
                                unset($_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]);
                                $_negation = false;
                            }
                        }

                        $_arrayExpression[SWIFT_KQL2::EXPRESSION_DATA][] = $_expression;

                        // Guess type
                        if (isset($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                            if (isset($_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                                if (($_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) &&
                                    ($_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != SWIFT_KQL2::DATA_ANY)) {
                                    $_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_ANY;
                                }
                            } else {
                                $_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE];
                            }
                        }

                        // Create new empty expression
                        $_expression = array(
                            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_EXPRESSION,
                            SWIFT_KQL2::EXPRESSION_DATA => array()
                        );

                        $_expressionExpected = true;
                    } else {
                        $_expressionExpected = false;
                    }
                } else {
                    if ($_expressionFound) {
                        if ($_arrayExpression) {
                            throw new SWIFT_KQL2_Exception('Expected , or )' . $this->GetOffsetDetails());
                        } else {
                            throw new SWIFT_KQL2_Exception('Expected operator or )' . $this->GetOffsetDetails());
                        }
                    } else {
                        throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
                    }
                }
            }

            if (!$_closeParenthesis) {
                throw new SWIFT_KQL2_Exception('Missing closing parenthesis for (' . $this->GetOffsetDetails(false, $_openParenthesis));

            } elseif (empty($_expression[SWIFT_KQL2::EXPRESSION_DATA]) || $_expressionExpected) {
                $this->MoveBack();

                throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
            }

            // Remove nesting level
            if (count($_expression[SWIFT_KQL2::EXPRESSION_DATA]) == 1) {
                if (isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
                    isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['_PARENTHESES']) &&
                    ($_expression[SWIFT_KQL2::EXPRESSION_DATA][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_EXPRESSION)) {
                    $_expression[SWIFT_KQL2::EXPRESSION_DATA][0][SWIFT_KQL2::EXPRESSION_EXTRA] = array(
                        '_PARENTHESES' => $_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['_PARENTHESES']
                    );
                }

                $_expression = $_expression[SWIFT_KQL2::EXPRESSION_DATA][0];

                if ($_negation &&
                    isset($_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]) && $_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]) {
                    unset($_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]);
                    $_negation = false;
                }
            }

            if ($_arrayExpression) {
                $_arrayExpression[SWIFT_KQL2::EXPRESSION_DATA][] = $_expression;

                // Guess type
                if (isset($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                    if (!isset($_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                        if (($_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) &&
                            ($_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != SWIFT_KQL2::DATA_ANY)) {
                            $_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_ANY;
                        }
                    } else {
                        $_arrayExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE];
                    }
                }

                $_expression = $_arrayExpression;
            }

            if ($_negation) {
                if ($_arrayExpression) {
                    throw new SWIFT_KQL2_Exception('NOT is not supported for arrays' . $this->GetOffsetDetails(false));
                }

                $_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE] = true;
            }
        }

        // NOTE: It is assumed that in case of :Selector at the end of parentheses-less expression it will be parsed along with last element
        if (is_array($_expression) && ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] != SWIFT_KQL2::ELEMENT_ARRAY)) {
            $_selector = $this->GetSelector();
            if ($_selector) {
                $_selectorProperties = $this->_selectorsContainer[$_selector];

                $_functionProperties = false;
                if (isset($_selectorProperties[SWIFT_KQLSchema::SELECTOR_FUNCTION]) &&
                    isset($this->_functionsContainer[$_selectorProperties[SWIFT_KQLSchema::SELECTOR_FUNCTION]])) {
                    $_functionProperties = $this->_functionsContainer[$_selectorProperties[SWIFT_KQLSchema::SELECTOR_FUNCTION]];
                } elseif (!isset($_selectorProperties[SWIFT_KQLSchema::SELECTOR_COMPILER]) && isset($this->_functionsContainer[$_selector])) {
                    $_functionProperties = $this->_functionsContainer[$_selector];
                }

                if (is_array($_functionProperties)) {
                    $_expression = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FUNCTION,
                        SWIFT_KQL2::EXPRESSION_DATA => array($_selector, array($_expression))
                    );

                    if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_RETURNTYPE])) {
                        $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_functionProperties[SWIFT_KQLSchema::FUNCTION_RETURNTYPE];
                    }
                } elseif (is_string($_functionProperties) && isset($this->_functionsContainer[$_functionProperties])) {
                    $_expression = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FUNCTION,
                        SWIFT_KQL2::EXPRESSION_DATA => array($_functionProperties, array($_expression))
                    );

                    if (isset($this->_functionsContainer[$_functionProperties][SWIFT_KQLSchema::FUNCTION_RETURNTYPE])) {
                        $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $this->_functionsContainer[$_functionProperties][SWIFT_KQLSchema::FUNCTION_RETURNTYPE];
                    }
                } else {
                    $_expression = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_SELECTOR,
                        SWIFT_KQL2::EXPRESSION_DATA => array($_selector, array($_expression))
                    );
                }
            }
        }

        if (is_array($_expression)) {
            $_operator = $this->GetOperator();
            if ($_operator) {
                if ($_openParenthesis ||
                    ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] != SWIFT_KQL2::ELEMENT_EXPRESSION)) {
                    $_expression = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_EXPRESSION,
                        SWIFT_KQL2::EXPRESSION_DATA => array($_expression, $_operator)
                    );
                }

                $_nextValue = $this->GetExpression();
                if ($_nextValue) {
                    if (($_nextValue[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_EXPRESSION) &&
                        (!isset($_nextValue[SWIFT_KQL2::EXPRESSION_EXTRA]) ||
                            !isset($_nextValue[SWIFT_KQL2::EXPRESSION_EXTRA]['_PARENTHESES']) ||
                            ($_nextValue[SWIFT_KQL2::EXPRESSION_EXTRA]['_PARENTHESES'] !== true))) {
                        $_expression[SWIFT_KQL2::EXPRESSION_DATA] = array_merge($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_nextValue[SWIFT_KQL2::EXPRESSION_DATA]);
                    } else {
                        $_expression[SWIFT_KQL2::EXPRESSION_DATA][] = $_nextValue;
                    }
                } else {
                    throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
                }
            }
        }

        if (is_array($_expression) && ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_EXPRESSION)) {
            $this->ProcessExpressionGroups($_expression);
        }

        return $_expression;
    }

    /**
     * Processes Operands in Expression
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessExpressionGroups(&$_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] != SWIFT_KQL2::ELEMENT_EXPRESSION) {
            return false;
        }

        // TODO: Calculate SWIFT_KQL2::EXPRESSION_RETURNTYPE (DATA_SAME etc)

        $_savedTypes = array();
        $_precedenceGroups = (array)SWIFT_KQL2::GetPrecedenceGroups($_expression);

        foreach ($_precedenceGroups as $_precedenceGroup) {
            $_operator = $_precedenceGroup[1];
            $_firstOperand = $_precedenceGroup[0];
            $_secondOperand = $_precedenceGroup[2];

            $_firstOperandType = false;
            $_secondOperandType = false;
            $_firstOperandDataType = false;
            $_secondOperandDataType = false;

            // Determine first operand types
            if (is_array($_firstOperand)) {
                $_firstOperandType = SWIFT_KQL2::ELEMENT_EXPRESSION;
                if (isset($_savedTypes[$_firstOperand[1]])) {
                    $_firstOperandDataType = $_savedTypes[$_firstOperand[1]];
                }
            } else {
                $_firstOperandType = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE];
                if (isset($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                    $_firstOperandDataType = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE];
                }
            }

            // Determine second operand types
            if (is_array($_secondOperand)) {
                $_secondOperandType = SWIFT_KQL2::ELEMENT_EXPRESSION;
                if (isset($_savedTypes[$_secondOperand[0]])) {
                    $_secondOperandDataType = $_savedTypes[$_secondOperand[0]];
                }
            } else {
                $_secondOperandType = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE];
                if (isset($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                    $_secondOperandDataType = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE];
                }
            }

            // Convert option strings to IDs
            if ($_firstOperandDataType == SWIFT_KQL2::DATA_OPTION) {
                if (($_firstOperandType == SWIFT_KQL2::ELEMENT_FIELD) || ($_firstOperandType == SWIFT_KQL2::ELEMENT_CUSTOMFIELD)) {
                    if ($_secondOperandType == SWIFT_KQL2::ELEMENT_ARRAY) {
                        foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA] as $_itemIndex => $_itemExpression) {
                            if ($_itemExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) {
                                if (isset($_itemExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                                    if ($_itemExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING) {
                                        $this->ConvertStringToOption($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][$_itemIndex], $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand]);
                                    } elseif (($_itemExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != SWIFT_KQL2::DATA_INTEGER) && ($_itemExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != SWIFT_KQL2::DATA_OPTION)) {
                                        throw new SWIFT_KQL2_Exception('Second operand for field with option values must be string');
                                    }
                                }
                            } else {
                                throw new SWIFT_KQL2_Exception('Second operand for field with option values must be value');
                            }
                        }
                    } elseif ($_secondOperandType == SWIFT_KQL2::ELEMENT_VALUE) {
                        if ($_secondOperandDataType == SWIFT_KQL2::DATA_STRING) {
                            $this->ConvertStringToOption($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand], $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand]);
                        } elseif (($_secondOperandDataType != SWIFT_KQL2::DATA_INTEGER) && ($_secondOperandDataType != SWIFT_KQL2::DATA_OPTION) &&
                            !is_null($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA])) {
                            throw new SWIFT_KQL2_Exception('Second operand for field with option values must be string');
                        }
                    } else {
                        throw new SWIFT_KQL2_Exception('Second operand for field with option values must be value');
                    }
                } else {
                    throw new SWIFT_KQL2_Exception('Fields with option values must be used on their own');
                }
            } elseif ($_secondOperandDataType == SWIFT_KQL2::DATA_OPTION) {
                if (($_secondOperandType == SWIFT_KQL2::ELEMENT_FIELD) || ($_secondOperandType == SWIFT_KQL2::ELEMENT_CUSTOMFIELD)) {
                    if ($_firstOperandType == SWIFT_KQL2::ELEMENT_ARRAY) {
                        throw new SWIFT_KQL2_Exception('Array cannot be used as first operand');
                    } elseif ($_firstOperandType == SWIFT_KQL2::ELEMENT_VALUE) {
                        if ($_firstOperandDataType == SWIFT_KQL2::DATA_STRING) {
                            $this->ConvertStringToOption($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand], $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand]);
                        } elseif (($_firstOperandDataType != SWIFT_KQL2::DATA_INTEGER) && ($_firstOperandDataType != SWIFT_KQL2::DATA_OPTION) &&
                            !is_null($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA])) {
                            throw new SWIFT_KQL2_Exception('First operand for field with option values must be string');
                        }
                    } else {
                        throw new SWIFT_KQL2_Exception('First operand for field with option values must be value');
                    }
                } else {
                    throw new SWIFT_KQL2_Exception('Fields with option values must be used on their own');
                }
            }

            // Convert strings to other types
            if ($_firstOperandDataType != $_secondOperandDataType) {
                if ($_firstOperandType == SWIFT_KQL2::ELEMENT_ARRAY) {
                    foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA] as $_itemIndex => $_itemExpression) {
                        if (($_itemExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                            ($_itemExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING)) {
                            $this->ConvertStringToValue($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][$_itemIndex], $_secondOperandDataType);
                        }
                    }

                    $_firstOperandDataType = false;
                } elseif (($_firstOperandType == SWIFT_KQL2::ELEMENT_VALUE) && ($_firstOperandDataType == SWIFT_KQL2::DATA_STRING)) {
                    $this->ConvertStringToValue($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand], $_secondOperandDataType);

                    $_firstOperandDataType = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE];
                } elseif ($_secondOperandType == SWIFT_KQL2::ELEMENT_ARRAY) {
                    foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA] as $_itemIndex => $_itemExpression) {
                        if (($_itemExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                            ($_itemExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING)) {
                            $this->ConvertStringToValue($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][$_itemIndex], $_firstOperandDataType);
                        }
                    }

                    $_secondOperandDataType = false;
                } elseif (($_secondOperandType == SWIFT_KQL2::ELEMENT_VALUE) && ($_secondOperandDataType == SWIFT_KQL2::DATA_STRING)) {
                    $this->ConvertStringToValue($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand], $_firstOperandDataType);

                    $_secondOperandDataType = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE];
                }
            }

            $_operatorProperties = $this->_operatorsContainer[SWIFT_KQL2::GetCleanOperator($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_operator])];

            // Convert values to other types if needed
            if (($_firstOperandDataType != $_secondOperandDataType) &&
                isset($_operatorProperties[SWIFT_KQLSchema::OPERATOR_LEFTTYPE]) &&
                isset($_operatorProperties[SWIFT_KQLSchema::OPERATOR_RIGHTTYPE]) &&
                (($_operatorProperties[SWIFT_KQLSchema::OPERATOR_LEFTTYPE] == SWIFT_KQL2::DATA_SAME) ||
                    ($_operatorProperties[SWIFT_KQLSchema::OPERATOR_RIGHTTYPE] == SWIFT_KQL2::DATA_SAME))) {

                if ($_firstOperandType == SWIFT_KQL2::ELEMENT_ARRAY) {
                    foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA] as $_itemIndex => $_itemExpression) {
                        if ($_itemExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) {
                            $this->ConvertValueToType($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][$_itemIndex], $_secondOperandDataType);
                        }
                    }
                } elseif ($_firstOperandType == SWIFT_KQL2::ELEMENT_VALUE) {
                    $this->ConvertValueToType($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand], $_secondOperandDataType);
                } elseif ($_secondOperandType == SWIFT_KQL2::ELEMENT_ARRAY) {
                    foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA] as $_itemIndex => $_itemExpression) {
                        if ($_itemExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) {
                            $this->ConvertValueToType($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][$_itemIndex], $_firstOperandDataType);
                        }
                    }
                } elseif ($_secondOperandType == SWIFT_KQL2::ELEMENT_VALUE) {
                    $this->ConvertValueToType($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand], $_firstOperandDataType);
                }
            }

            $_expressionType = $this->GuessExpressionType($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_operator], $_firstOperandDataType, $_secondOperandDataType);

            // Save type for first operand
            if (is_array($_firstOperand)) {
                $_savedTypes[$_firstOperand[0]] = $_savedTypes[$_firstOperand[1]] = $_expressionType;
            } else {
                $_savedTypes[$_firstOperand] = $_expressionType;
            }

            // Save type for second operand
            if (is_array($_secondOperand)) {
                $_savedTypes[$_secondOperand[0]] = $_savedTypes[$_secondOperand[1]] = $_expressionType;
            } else {
                $_savedTypes[$_secondOperand] = $_expressionType;
            }

            // FIXME: Do we save $_expressionType?
        }

        return true;
    }

    /**
     * Converts String to Another Type (Determines)
     *
     * @author Andriy Lesyuk
     * @param array $_stringExpression
     * @param int $_expectedDataType
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ConvertStringToValue(&$_stringExpression, $_expectedDataType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (($_stringExpression[SWIFT_KQL2::EXPRESSION_TYPE] != SWIFT_KQL2::ELEMENT_VALUE) ||
            ($_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != SWIFT_KQL2::DATA_STRING)) {
            return false;
        }

        $_stringValue = $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA];

        // Float values (e.g. -3.14, .15, 1,500.50)
        if (preg_match('/^[+\-]?[0-9]*(?:[, ][0-9]+)*\.[0-9]+$/', $_stringValue)) {
            $_cleanValue = str_replace(array(' ', ','), '', $_stringValue);

            $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_FLOAT;
            $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = floatval($_cleanValue);

            // Integer values (e.g. +5, 100, 1,500)
        } elseif (preg_match('/^[+\-]?[0-9]+(?:[, ][0-9]+)*$/', $_stringValue)) {
            $_cleanValue = str_replace(array(' ', ','), '', $_stringValue);

            $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTEGER;
            $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_cleanValue);

            // Seconds values (e.g. 2d 5h, 6m 5s)
        } elseif (preg_match('/^(?:([0-9]+)d *)?(?:([0-9]+)h *)?(?:([0-9]+)m *)?(?:([0-9]+)s)?$/i', $_stringValue, $_matches) && !empty($_matches[0])) {
            $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_SECONDS;
            $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = (isset($_matches[1]) ? (int)($_matches[1]) * 86400 : 0) + (isset($_matches[2]) ? (int)($_matches[2]) * 3600 : 0) + (isset($_matches[3]) ? (int)($_matches[3]) * 60 : 0) + (isset($_matches[4]) ? (int)($_matches[4]) : 0);

            // Boolean values (e.g. true, off, yes)
        } elseif (SWIFT_KQLSchema::ArgumentTypeEqual(SWIFT_KQL2::DATA_BOOLEAN, $_expectedDataType) && preg_match('/^(?:(true|yes|on)|(false|no|off))$/i', $_stringValue, $_matches)) {
            $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_BOOLEAN;
            $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = !isset($_matches[2]);

            /**
             * Date and time values
             */

            // Date and time values (e.g. 2012-01-15 15:36:20, 12-1-5 15:40)
        } elseif (preg_match('/^(?:(?:([0-9]{2,4})[[:punct:]])?([0-9]{1,2})[[:punct:]])?([0-9]{1,2}) ([0-9]{1,2})[[:punct:]]([0-9]{2})(?:[[:punct:]]([0-9]{2}))?$/', $_stringValue, $_matches)) {
            if ((!isset($_matches[2]) || (($_matches[2] >= 1) && ($_matches[2] <= 12))) &&
                (!isset($_matches[3]) || (($_matches[3] >= 1) && ($_matches[3] <= 31))) &&
                ($_matches[4] < 24) && ($_matches[5] < 60) &&
                (!isset($_matches[6]) || ($_matches[6] < 60))) {
                $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime($_matches[4], $_matches[5], isset($_matches[6]) ? $_matches[6] : false, isset($_matches[2]) ? $_matches[2] : false, isset($_matches[3]) ? $_matches[3] : false, isset($_matches[1]) ? $_matches[1] : false);
            }

            // Date or time values (e.g. 2012-01-15, 12-1-5; 15:30:00)
        } elseif (preg_match('/^(?:([0-9]{1,4})[[:punct:]])?([0-9]{1,2})([[:punct:]])([0-9]{1,2})$/', $_stringValue, $_matches)) {
            // E.g. 05/12, 15:40
            if (!isset($_matches[1]) || ($_matches[1] == false)) {
                // E.g. 15:41
                if (($_matches[2] < 24) && ($_matches[4] < 60) && (($_matches[3] == ':') || ($_matches[3] == '-'))) {
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_TIME;
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_matches[2]) * 60 + (int)($_matches[4]);
                    // E.g. 12/21
                } elseif (($this->Settings->Get('dt_caltype') == 'us') &&
                    ($_matches[2] >= 1) && ($_matches[2] <= 12) && ($_matches[4] >= 1) && ($_matches[4] <= 31)) {
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(0, 0, 0, $_matches[2], $_matches[4]);
                    // E.g. 21/21
                } elseif (($this->Settings->Get('dt_caltype') != 'us') &&
                    ($_matches[2] >= 1) && ($_matches[2] <= 31) && ($_matches[4] >= 1) && ($_matches[4] <= 12)) {
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(0, 0, 0, $_matches[4], $_matches[2]);
                }
                // E.g. 2012-08-02, 15:30:10
            } else {
                // E.g. 15:30:45
                if (($_matches[1] < 24) && ($_matches[2] < 60) && ($_matches[4] < 60) && (($_matches[3] == ':') || ($_matches[3] == '-'))) {
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_TIME;
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_matches[1]) * 3600 + (int)($_matches[2]) * 60 + (int)($_matches[4]);
                    // E.g. 2012-07-26
                } elseif (($_matches[2] >= 1) && ($_matches[2] <= 12) && ($_matches[4] >= 1) && ($_matches[4] <= 31)) {
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(0, 0, 0, $_matches[2], $_matches[4], $_matches[1]);
                    // E.g. 12/21/12
                } elseif (($this->Settings->Get('dt_caltype') == 'us') &&
                    (($_matches[1] >= 1) && ($_matches[1] <= 12)) && (($_matches[2] >= 1) && ($_matches[2] <= 31))) {
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(0, 0, 0, $_matches[1], $_matches[2], $_matches[4]);
                    // E.g. 21/12/12
                } elseif (($this->Settings->Get('dt_caltype') != 'us') &&
                    (($_matches[1] >= 1) && ($_matches[1] <= 31)) && (($_matches[2] >= 1) && ($_matches[2] <= 12))) {
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                    $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(0, 0, 0, $_matches[2], $_matches[1], $_matches[4]);
                }
            }

            // Date values (e.g. 01-15-2012)
        } elseif (preg_match('/^([0-9]{1,2})[[:punct:]]([0-9]{1,2})[[:punct:]]([0-9]{4})$/', $_stringValue, $_matches)) {
            // E.g. 12/26/2012
            if (($this->Settings->Get('dt_caltype') == 'us') &&
                (($_matches[1] >= 1) && ($_matches[1] <= 12)) && (($_matches[2] >= 1) && ($_matches[2] <= 31))) {
                $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(0, 0, 0, $_matches[1], $_matches[2], $_matches[3]);
                // E.g. 26/12/2012
            } elseif (($this->Settings->Get('dt_caltype') != 'us') &&
                (($_matches[2] >= 1) && ($_matches[2] <= 12)) && (($_matches[1] >= 1) && ($_matches[1] <= 31))) {
                $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(0, 0, 0, $_matches[2], $_matches[1], $_matches[3]);
            }

        } else {
            $_monthsRegexp = implode('|', array_keys(array_merge(self::$_monthNamesMap, self::$_monthShortNamesMap)));

            // Date time values (e.g. 1st January 2012, Jan '12, 4 July 2012 11:30)
            if (preg_match('/^(?:([0-9]{1,2})(?:st|nd|rd|th)? +)?(' . $_monthsRegexp . ')(?: +\'?([0-9]{2}|[0-9]{4}))?(?: +([0-9]{1,2})[:\-]([0-9]{2})(?:[:\-]([0-9]{2}))?)?$/i', $_stringValue, $_matches)) {
                $_monthName = mb_convert_case($_matches[2], MB_CASE_TITLE);

                $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(isset($_matches[4]) ? $_matches[4] : false, isset($_matches[5]) ? $_matches[5] : false, isset($_matches[6]) ? $_matches[6] : false, (isset(self::$_monthNamesMap[$_monthName])) ? self::$_monthNamesMap[$_monthName] : self::$_monthShortNamesMap[$_monthName], isset($_matches[1]) ? $_matches[1] : false, isset($_matches[3]) ? $_matches[3] : false);

                // Date time values (e.g. January 1, 2012)
            } elseif (preg_match('/^(' . $_monthsRegexp . ') +([0-9]{1,2}),?(?: +\'?([0-9]{2}|[0-9]{4}))?(?: +([0-9]{1,2})[:\-]([0-9]{2})(?:[:\-]([0-9]{2}))?)?$/i', $_stringValue, $_matches)) {
                $_monthName = mb_convert_case($_matches[1], MB_CASE_TITLE);

                $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $this->_MakeTime(isset($_matches[4]) ? $_matches[4] : false, isset($_matches[5]) ? $_matches[5] : false, isset($_matches[6]) ? $_matches[6] : false, (isset(self::$_monthNamesMap[$_monthName])) ? self::$_monthNamesMap[$_monthName] : self::$_monthShortNamesMap[$_monthName], $_matches[2], isset($_matches[3]) ? $_matches[3] : false);
            }
        }

        return true;
    }

    /**
     * Converts String Option to Integer
     * @author Andriy Lesyuk
     * @param array $_stringExpression
     * @param array $_fieldExpression
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ConvertStringToOption(&$_stringExpression, $_fieldExpression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (($_fieldExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != SWIFT_KQL2::DATA_OPTION) ||
            ($_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] != SWIFT_KQL2::DATA_STRING)) {
            return false;
        }

        // Fields
        if ($_fieldExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
            $_tableName = $_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA][0];
            $_columnName = $_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA][1];
            $_columnValue = $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA];

            if (isset($this->_schemaContainer[$_tableName]) &&
                isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName])) {
                $_fieldContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName];

                if (isset($_fieldContainer[SWIFT_KQLSchema::FIELD_CUSTOMVALUES])) { // FIXME: Maybe cache?
                    foreach ($_fieldContainer[SWIFT_KQLSchema::FIELD_CUSTOMVALUES] as $_actualValue => $_localeString) {
                        $_localeValue = SWIFT_KQLSchema::GetLabel($_localeString);

                        if (mb_strtolower($_localeValue) == mb_strtolower($_columnValue)) {
                            $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $_actualValue;
                            $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_OPTION;
                            break;
                        }
                    }
                }
            }

            // Custom fields
        } elseif ($_fieldExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
            $_customField = $_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA][2];
            $_columnValue = $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA];

            if (!is_array($_customField) && isset($this->_customFieldIDMap[$_customField])) {
                $_customFieldContainer = $this->_customFieldIDMap[$_customField];

                if (isset($_customFieldContainer['options'])) {
                    foreach ($_customFieldContainer['options'] as $_actualValue => $_stringValue) {

                        // TYPE_CHECKBOX, TYPE_SELECTMULTIPLE, TYPE_RADIO, TYPE_SELECT
                        if (is_array($_stringValue)) {
                            if (mb_strtolower($_stringValue['value']) == mb_strtolower($_columnValue)) {
                                $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $_actualValue;
                                $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_OPTION;
                                break;
                            }

                            if (isset($_stringValue['suboptions'])) {
                                foreach ($_stringValue['suboptions'] as $_actualSubValue => $_stringSubValue) {
                                    if (mb_strtolower($_stringSubValue['value']) == mb_strtolower($_columnValue)) {
                                        $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $_actualSubValue;
                                        $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_OPTION;
                                        break;
                                    }
                                }
                            }

                            // TYPE_SELECTLINKED
                        } else {
                            if (mb_strtolower($_stringValue) == mb_strtolower($_columnValue)) {
                                $_stringExpression[SWIFT_KQL2::EXPRESSION_DATA] = $_actualValue;
                                $_stringExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_OPTION;
                                break;
                            }
                        }
                    }
                }
            } // Wildcard should not be here
        }

        return true;
    }

    /**
     * Converts Value to Another Type
     *
     * @author Andriy Lesyuk
     * @param array $_valueExpression
     * @param int $_toDataType
     * @param bool $_force
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ConvertValueToType(&$_valueExpression, $_toDataType, $_force = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
            return false;
        }

        if (is_null($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA])) {
            return false;
        }

        $_fromDataType = $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE];

        if ($_fromDataType == $_toDataType) {
            return true;
        }

        switch ($_toDataType) {
            case SWIFT_KQL2::DATA_BOOLEAN:
                if ($_force) {
                    // STRING => BOOLEAN
                    if ($_fromDataType == SWIFT_KQL2::DATA_STRING) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_BOOLEAN;
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = (strlen($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]) > 0);
                        // * => BOOLEAN
                    } elseif (($_fromDataType != SWIFT_KQL2::DATA_INTERVAL) || !is_string($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA])) { // INTEGER, FLOAT, SECONDS, TIME, DATE, UNIXDATE, DATETIME
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_BOOLEAN;
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] > 0);
                    }
                }
                break;

            case SWIFT_KQL2::DATA_INTEGER:
            case SWIFT_KQL2::DATA_FLOAT:
            case SWIFT_KQL2::DATA_SECONDS:
                // INTERVAL X UNIT => INTEGER
                if ($_fromDataType == SWIFT_KQL2::DATA_INTERVAL) {
                    if (is_float($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA])) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_FLOAT;
                    } elseif (is_int($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA])) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_SECONDS;
                    }
                    // TIME => INTEGER
                } elseif ($_fromDataType == SWIFT_KQL2::DATA_TIME) {
                    if ($_force && ($_toDataType != SWIFT_KQL2::DATA_FLOAT)) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                    } else {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTEGER;
                    }
                    // DATE => INTEGER
                } elseif (($_fromDataType == SWIFT_KQL2::DATA_DATE) ||
                    ($_fromDataType == SWIFT_KQL2::DATA_DATETIME)) {
                    if ($_force) {
                        if ($_toDataType != SWIFT_KQL2::DATA_FLOAT) {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                        } else {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTEGER;
                        }
                    } else {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_UNIXDATE;
                    }
                } elseif ($_force) {
                    // BOOLEAN => INTEGER
                    if ($_fromDataType == SWIFT_KQL2::DATA_BOOLEAN) {
                        if ($_toDataType != SWIFT_KQL2::DATA_FLOAT) {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                        } else {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTEGER;
                        }
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]) ? 1 : 0;
                        // STRING => INTEGER!
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_STRING) {
                        throw new SWIFT_KQL2_Exception('Cannot convert \'' . $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] . '\' to ' . SWIFT_KQL2::GetDataTypeString($_toDataType));
                        // SECONDS => INTEGER
                    } else { // UNIXDATE
                        if ($_toDataType != SWIFT_KQL2::DATA_FLOAT) {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                        } else {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTEGER;
                        }
                    }
                }
                break;

            case SWIFT_KQL2::DATA_INTERVAL:
                if ($_force) {
                    if (!isset($_valueExpression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
                    }
                    // BOOLEAN => INTERVAL X UNIT
                    if ($_fromDataType == SWIFT_KQL2::DATA_BOOLEAN) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTERVAL;
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]) ? 1 : 0;
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['INTERVAL'] = 'SECOND';
                        // INTEGER => INTERVAL X UNIT
                    } elseif (($_fromDataType == SWIFT_KQL2::DATA_INTEGER) ||
                        ($_fromDataType == SWIFT_KQL2::DATA_SECONDS) ||
                        ($_fromDataType == SWIFT_KQL2::DATA_TIME)) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTERVAL;
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['INTERVAL'] = 'SECOND';
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_FLOAT) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTERVAL;
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['INTERVAL'] = 'SECOND_MICROSECOND';
                    } else { // DATE, UNIXDATE, DATETIME, STRING
                        throw new SWIFT_KQL2_Exception('Cannot convert ' . SWIFT_KQL2::GetDataTypeString($_fromDataType) . ' to ' . SWIFT_KQL2::GetDataTypeString($_toDataType));
                    }
                }
                break;

            case SWIFT_KQL2::DATA_TIME:
                if (($_fromDataType == SWIFT_KQL2::DATA_INTEGER) ||
                    ($_fromDataType == SWIFT_KQL2::DATA_SECONDS)) {
                    $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_TIME;
                } elseif ($_fromDataType == SWIFT_KQL2::DATA_FLOAT) {
                    $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]);
                    $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_TIME;
                } elseif ($_fromDataType == SWIFT_KQL2::DATA_INTERVAL) {
                    if (is_float($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA])) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]);
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_TIME;
                    } elseif (is_int($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA])) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_TIME;
                    } else {
                        throw new SWIFT_KQL2_Exception('Cannot convert \'' . $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] . '\' to ' . SWIFT_KQL2::GetDataTypeString($_toDataType));
                    }
                } elseif ($_force) {
                    if ($_fromDataType == SWIFT_KQL2::DATA_BOOLEAN) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]) ? 1 : 0;
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_TIME;
                    } else { // DATE, UNIXDATE, DATETIME, STRING
                        throw new SWIFT_KQL2_Exception('Cannot convert ' . SWIFT_KQL2::GetDataTypeString($_fromDataType) . ' to ' . SWIFT_KQL2::GetDataTypeString($_toDataType));
                    }
                }
                break;

            case SWIFT_KQL2::DATA_DATE:
            case SWIFT_KQL2::DATA_UNIXDATE:
            case SWIFT_KQL2::DATA_DATETIME:
                if ($_fromDataType == SWIFT_KQL2::DATA_UNIXDATE) {
                    $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_DATETIME;
                } elseif ($_force) {
                    if ($_fromDataType == SWIFT_KQL2::DATA_FLOAT) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]);
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                    } elseif (($_fromDataType == SWIFT_KQL2::DATA_BOOLEAN) ||
                        ($_fromDataType == SWIFT_KQL2::DATA_INTERVAL)) {
                        throw new SWIFT_KQL2_Exception('Cannot convert ' . SWIFT_KQL2::GetDataTypeString($_fromDataType) . ' to ' . SWIFT_KQL2::GetDataTypeString($_toDataType));
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_TIME) {
                        if (($_toDataType == SWIFT_KQL2::DATA_UNIXDATE) ||
                            ($_toDataType == SWIFT_KQL2::DATA_DATETIME)) {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = mktime(0, 0, 0) + $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA];
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                        } else {
                            throw new SWIFT_KQL2_Exception('Cannot convert ' . SWIFT_KQL2::GetDataTypeString($_fromDataType) . ' to ' . SWIFT_KQL2::GetDataTypeString($_toDataType));
                        }
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_STRING) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = strtotime($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]);
                        if ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]) {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                        } else {
                            throw new SWIFT_KQL2_Exception('Cannot convert \'' . $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] . '\' to ' . SWIFT_KQL2::GetDataTypeString($_toDataType));
                        }
                    } else { // INTEGER, SECONDS, UNIXDATE, DATETIME, DATE
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_toDataType;
                    }
                }
                break;

            case SWIFT_KQL2::DATA_STRING:
                if ($_force) {
                    if ($_fromDataType == SWIFT_KQL2::DATA_BOOLEAN) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]) ? '1' : '0';
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_INTEGER) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = sprintf("%d", $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]);
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_FLOAT) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = sprintf("%f", $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA]);
                    } elseif (($_fromDataType == SWIFT_KQL2::DATA_SECONDS) ||
                        ($_fromDataType == SWIFT_KQL2::DATA_INTERVAL)) {
                        if (($_fromDataType != SWIFT_KQL2::DATA_INTERVAL) || !is_string($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA])) {
                            $_stringValues = array();
                            if ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] > 86400) {
                                $_stringValues[] = sprintf("%dd", floor($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] / 86400));
                            }
                            if ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] > 3600) {
                                $_stringValues[] = sprintf("%dh", floor(($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] % 86400) / 3600));
                            }
                            if ($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] > 60) {
                                $_stringValues[] = sprintf("%dm", floor(($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] % 3600) / 60));
                            }
                            $_stringValues[] = sprintf("%ds", $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] % 60);
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = implode(' ', $_stringValues);
                        }
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_TIME) {
                        $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = sprintf("%d:%02d:%02d",
                            floor($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] / 3600),
                            floor(($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] % 3600) / 60),
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] % 60);
                    } elseif ($_fromDataType == SWIFT_KQL2::DATA_DATE) {
                        $_time = localtime($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA], true);
                        if ($this->Settings->Get('dt_caltype') == 'us') {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = sprintf("%02d/%02d/%04d",
                                $_time['tm_mon'] + 1, $_time['tm_mday'], $_time['tm_year'] + 1900);
                        } else {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = sprintf("%02d/%02d/%04d",
                                $_time['tm_mday'], $_time['tm_mon'] + 1, $_time['tm_year'] + 1900);
                        }
                    } elseif (($_fromDataType == SWIFT_KQL2::DATA_UNIXDATE) ||
                        ($_fromDataType == SWIFT_KQL2::DATA_DATETIME)) {
                        $_time = localtime($_valueExpression[SWIFT_KQL2::EXPRESSION_DATA], true);
                        if ($this->Settings->Get('dt_caltype') == 'us') {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = sprintf("%02d/%02d/%04d %02d:%02d:%02d",
                                $_time['tm_mon'] + 1, $_time['tm_mday'], $_time['tm_year'] + 1900, $_time['tm_hour'], $_time['tm_min'], $_time['tm_sec']);
                        } else {
                            $_valueExpression[SWIFT_KQL2::EXPRESSION_DATA] = sprintf("%02d/%02d/%04d %02d:%02d:%02d",
                                $_time['tm_mday'], $_time['tm_mon'] + 1, $_time['tm_year'] + 1900, $_time['tm_hour'], $_time['tm_min'], $_time['tm_sec']);
                        }
                    }
                }
                break;
        }

        return true;
    }

    /**
     * Try Guessing Expression Return Type
     *
     * @author Andriy Lesyuk
     * @param string $_operator
     * @param int $_firstOperand
     * @param int $_secondOperand
     * @return int The Data Type
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GuessExpressionType($_operator, $_firstOperand, $_secondOperand)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // TODO

        $_operatorProperties = $this->_operatorsContainer[SWIFT_KQL2::GetCleanOperator($_operator)];

        if (isset($_operatorProperties[SWIFT_KQLSchema::OPERATOR_RETURNTYPE])) {
            $_expressionType = $_operatorProperties[SWIFT_KQLSchema::OPERATOR_RETURNTYPE];
            if (!SWIFT_KQL2::IsAbstractType($_expressionType)) {
                return $_expressionType;
            }
        }

        return ($_firstOperand > $_secondOperand) ? $_firstOperand : $_secondOperand;
    }

    /**
     * Extracts Table Names
     *
     * @author Andriy Lesyuk
     * @return string|bool The Table Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTable()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_table = $this->GetSequentialStringChunks();
        if ($_table) {
            $_tableName = $this->GetTableName($_table[0]);
            if ($_tableName) {
                for ($_i = 0; $_i < $_table[4]; $_i++) {
                    $this->MoveNext();
                }

                return $_tableName;
            }
        }

        $_token = $this->GetToken();
        if ($_token && (SWIFT_KQL2Lexer::TypeIsString($_token[1]) || SWIFT_KQL2Lexer::TypeIsFieldName($_token[1]))) {
            $_tableName = $this->GetTableName($_token[0]);
            if ($_tableName) {
                $this->MoveNext();

                return $_tableName;
            }
        }

        return false;
    }

    /**
     * Looks for Comma (Expressions Separator)
     *
     * @author Andriy Lesyuk
     * @return bool True if Comma was Met and False Otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetComma()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_GetSpecial(',');
    }

    /**
     * Looks for Open Parenthesis
     *
     * @author Andriy Lesyuk
     * @return bool True if Token is Open Parenthesis
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOpenParenthesis()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_GetSpecial('(');
    }

    /**
     * Looks for Closed Parenthesis
     *
     * @author Andriy Lesyuk
     * @return bool True if Token is Closed Parenthesis
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetClosedParenthesis()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_GetSpecial(')');
    }

    /**
     * Looks for Colon (Start of Selector)
     *
     * @author Andriy Lesyuk
     * @return bool True if Token is Colon
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetColon()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_GetSpecial(':');
    }

    /**
     * Looks for a Special Character
     *
     * @author Andriy Lesyuk
     * @param string $_special
     * @return mixed The Token or False
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetSpecial($_special)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsSpecial($_token[1]) && $_token[0] == $_special) {
            $this->MoveNext();

            return $_token;
        }

        return false;
    }

    /**
     * Looks for Pre-Modifier and Parses It If Fount
     * Executes Modifier's Parser
     *
     * @author Andriy Lesyuk
     * @return string|bool The Modifier
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetPreModifier()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
            $_modifierName = mb_strtoupper($_token[0]);
            if (isset($this->_preModifiersContainer[$_modifierName])) {
                $this->MoveNext();

                if (is_string($this->_preModifiersContainer[$_modifierName])) {
                    return $this->_preModifiersContainer[$_modifierName];
                } else {
                    return $_modifierName;
                }
            }
        }

        return false;
    }

    /**
     * Parses DISTINCT modifier
     *
     * @author Andriy Lesyuk
     * @param string $_modifierName
     * @return array|bool The Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_ParseDistinct($_modifierName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_expression = $this->GetExpression();
        if ($_expression) {
            if (!isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
                $_expression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
            }

            $_expression[SWIFT_KQL2::EXPRESSION_EXTRA][$_modifierName] = false;

            return $_expression;
        }

        return false;
    }

    /**
     * Parses INTERVAL value
     * NOTE: Interval value is always stored in seconds
     *
     * @author Andriy Lesyuk
     * @param string $_modifierName
     * @return array|bool The Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_ParseInterval($_modifierName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // NOTE: MONTH, QUARTER, YEAR and YEAR_MONTH can't be converted to seconds
        static $_unitIdentifiers = array(
            'MICROSECOND',
            'SECOND',
            'MINUTE',
            'HOUR',
            'DAY',
            'WEEK',
            'MONTH',
            'QUARTER',
            'YEAR',
            'SECOND_MICROSECOND',
            'MINUTE_MICROSECOND',
            'MINUTE_SECOND',
            'HOUR_MICROSECOND',
            'HOUR_SECOND',
            'HOUR_MINUTE',
            'DAY_MICROSECOND',
            'DAY_SECOND',
            'DAY_MINUTE',
            'DAY_HOUR',
            'YEAR_MONTH',
        );

        $_expression = $this->GetExpression();
        if ($_expression) {

            $_token = $this->GetToken();
            if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
                $_unitIdentifier = mb_strtoupper($_token[0]);
                if (in_array($_unitIdentifier, $_unitIdentifiers)) {
                    $this->MoveNext();

                    if (($_expression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                        (($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_INTEGER) ||
                            ($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_FLOAT) ||
                            ($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING))) {

                        // To make it possible to convert to other types
                        switch ($_unitIdentifier) {
                            case 'SECOND_MICROSECOND':    // 'SECONDS.MICROSECONDS'
                            case 'MINUTE_MICROSECOND':    // 'MINUTES:SECONDS.MICROSECONDS'
                            case 'HOUR_MICROSECOND':    // 'HOURS:MINUTES:SECONDS.MICROSECONDS'
                            case 'DAY_MICROSECOND':        // 'DAYS HOURS:MINUTES:SECONDS.MICROSECONDS'
                                if (preg_match('/^(-)?(?:([0-9]+) )?(?:([0-9]+):)?(?:([0-9]+):)?([0-9]+)\.([0-9]+)$/', trim($_expression[SWIFT_KQL2::EXPRESSION_DATA]), $_matches)) {
                                    $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_matches[2]) * 86400 + (int)($_matches[3]) * 3600 + (int)($_matches[4]) * 60 + (int)($_matches[5]) + (int)($_matches[6]) / pow(10, strlen($_matches[6]));

                                    if ($_matches[1] == '-') {
                                        $_expression[SWIFT_KQL2::EXPRESSION_DATA] = -1 * $_expression[SWIFT_KQL2::EXPRESSION_DATA];
                                    }
                                } else {
                                    throw new SWIFT_KQL2_Exception('Invalid interval expression: ' . $_expression[SWIFT_KQL2::EXPRESSION_DATA] . $this->GetOffsetDetails(true, $_token));
                                }
                                break;

                            case 'MINUTE_SECOND':    // 'MINUTES:SECONDS'
                            case 'HOUR_SECOND':        // 'HOURS:MINUTES:SECONDS'
                            case 'DAY_SECOND':        // 'DAYS HOURS:MINUTES:SECONDS'
                            case 'HOUR_MINUTE':        // 'HOURS:MINUTES'
                            case 'DAY_MINUTE':        // 'DAYS HOURS:MINUTES'
                                if (preg_match('/^(-)?(?:([0-9]+) )?(?:([0-9]+):)?([0-9]+)(?::([0-9]+))?$/', trim($_expression[SWIFT_KQL2::EXPRESSION_DATA]), $_matches)) {
                                    $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_matches[2]) * 86400 + (int)($_matches[3]) * 3600 + (int)($_matches[4]) * 60 + (int)($_matches[5]);

                                    if ($_matches[1] == '-') {
                                        $_expression[SWIFT_KQL2::EXPRESSION_DATA] = -1 * $_expression[SWIFT_KQL2::EXPRESSION_DATA];
                                    }
                                } else {
                                    throw new SWIFT_KQL2_Exception('Invalid interval expression: ' . $_expression[SWIFT_KQL2::EXPRESSION_DATA] . $this->GetOffsetDetails(true, $_token));
                                }
                                break;

                            case 'DAY_HOUR': // 'DAYS HOURS'
                                if (preg_match('/^(-)?([0-9]+) ([0-9]+)$/', trim($_expression[SWIFT_KQL2::EXPRESSION_DATA]), $_matches)) {
                                    $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)($_matches[2]) * 86400 + (int)($_matches[3]) * 3600;

                                    if ($_matches[1] == '-') {
                                        $_expression[SWIFT_KQL2::EXPRESSION_DATA] = -1 * $_expression[SWIFT_KQL2::EXPRESSION_DATA];
                                    }
                                } else {
                                    throw new SWIFT_KQL2_Exception('Invalid interval expression: ' . $_expression[SWIFT_KQL2::EXPRESSION_DATA] . $this->GetOffsetDetails(true, $_token));
                                }
                                break;

                            case 'MICROSECOND':
                                $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)(trim($_expression[SWIFT_KQL2::EXPRESSION_DATA])) / 1000000;
                                break;

                            case 'SECOND':
                                $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)(trim($_expression[SWIFT_KQL2::EXPRESSION_DATA]));
                                break;

                            case 'MINUTE':
                                $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)(trim($_expression[SWIFT_KQL2::EXPRESSION_DATA])) * 60;
                                break;

                            case 'HOUR':
                                $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)(trim($_expression[SWIFT_KQL2::EXPRESSION_DATA])) * 3600;
                                break;

                            case 'DAY':
                                $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)(trim($_expression[SWIFT_KQL2::EXPRESSION_DATA])) * 86400;
                                break;

                            case 'WEEK':
                                $_expression[SWIFT_KQL2::EXPRESSION_DATA] = (int)(trim($_expression[SWIFT_KQL2::EXPRESSION_DATA])) * 604800;
                                break;

                            default:
                                break;
                        }

                        if (is_float($_expression[SWIFT_KQL2::EXPRESSION_DATA])) {
                            $_unitIdentifier = 'SECOND_MICROSECOND';
                        } elseif (is_int($_expression[SWIFT_KQL2::EXPRESSION_DATA])) {
                            $_unitIdentifier = 'SECOND';
                        }
                    }

                    if (!isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
                        $_expression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
                    }

                    $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::DATA_INTERVAL;
                    $_expression[SWIFT_KQL2::EXPRESSION_EXTRA][$_modifierName] = $_unitIdentifier;

                    return $_expression;
                }
            }

            throw new SWIFT_KQL2_Exception('Expected unit, got ' . $this->GetTokenName($_token[0]) . $this->GetOffsetDetails(false));
        }

        return false;
    }

    /**
     * Looks for Post-Modifier and Processes it if Found
     * Executes Modifier's Parser
     *
     * @author Andriy Lesyuk
     * @return mixed The Modifier
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPostModifierAndModify(&$_expression, $_clause)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
            $_modifierName = mb_strtoupper($_token[0]);
            if (isset($this->_postModifiersContainer[$_modifierName])) {
                if (is_string($this->_postModifiersContainer[$_modifierName])) {
                    $_modifierProperties = $this->_postModifiersContainer[$this->_postModifiersContainer[$_modifierName]];
                } else {
                    $_modifierProperties = $this->_postModifiersContainer[$_modifierName];
                }

                if (isset($_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_CLAUSE]) && ($_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_CLAUSE] == $_clause)) {
                    $this->MoveNext();

                    if (isset($_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_PARSER])) {
                        $methodName = $_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_PARSER];
                        $this->$methodName($_modifierName, $_expression); // FIXME: Swap args
                    } else {
                        if (!isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
                            $_expression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
                        }

                        $_token = $this->GetToken();
                        if ($_token && SWIFT_KQL2Lexer::TypeIsString($_token[1])) {
                            $this->MoveNext();

                            $_expression[SWIFT_KQL2::EXPRESSION_EXTRA][$_modifierName] = $_token[0];
                        } else {
                            $_expression[SWIFT_KQL2::EXPRESSION_EXTRA][$_modifierName] = true;
                        }
                    }

                    return $_modifierName;
                } else {
                    throw new SWIFT_KQL2_Exception($_modifierName . ' modifier cannot be used in the ' . $_clause . ' clause' . $this->GetOffsetDetails(false));
                }
            }
        }

        return false;
    }

    /**
     * Parses SELECT Post-Modifiers
     *
     * @author Andriy Lesyuk
     * @param string $_modifier
     * @param array $_expression
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_ParseSelectModifiers($_modifier, &$_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
            $_expression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
        }

        switch ($_modifier) {
            case 'AS':
                $_token = $this->GetToken();
                if ($_token && SWIFT_KQL2Lexer::TypeIsString($_token[1])) {
                    $this->MoveNext();

                    $_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'] = $_token[0];

                    $this->_inlineVariables[mb_strtolower($_token[0])] = $_token[0];
                    return true;

                } elseif ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
                    if ($this->GetClause()) {
                        $this->MoveBack();

                        throw new SWIFT_KQL2_Exception('Missing alias' . $this->GetOffsetDetails());
                    } else {
                        $this->MoveNext();

                        $_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'] = $_token[0];

                        $this->_inlineVariables[mb_strtolower($_token[0])] = $_token[0];
                        return true;
                    }
                }

                throw new SWIFT_KQL2_Exception('Expected alias, got ' . $this->GetTokenName($_token[0]) . $this->GetOffsetDetails(false));
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Parses GROUP BY Post-Modifiers
     *
     * @author Andriy Lesyuk
     * @param string $_modifier
     * @param array $_expression
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_ParseGroupModifiers($_modifier, &$_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
            $_expression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
        }

        switch ($_modifier) {
            case 'X':
            case 'Y':
                $_expression[SWIFT_KQL2::EXPRESSION_EXTRA][$_modifier] = false;
                break;

            default:
                return false;
                break;
        }

        return true;
    }

    /**
     * Post-Parses X/Y() Functions
     *
     * @author Andriy Lesyuk
     * @return array The Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_PostParseXY($_functionExpression, $_startToken = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (($_functionExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
            (($_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][SWIFT_KQL2::EXPRESSION_TYPE] == 'X') ||
                ($_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][SWIFT_KQL2::EXPRESSION_TYPE] == 'Y'))) {
            $_functionName = $_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][SWIFT_KQL2::EXPRESSION_TYPE];
            $_arguments = $_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][SWIFT_KQL2::EXPRESSION_DATA];
            if (count($_arguments) == 1) {
                $_fieldExpression = $_arguments[0];

                if (!isset($_fieldExpression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
                    $_fieldExpression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
                }
                $_fieldExpression[SWIFT_KQL2::EXPRESSION_EXTRA][$_functionName] = false;

                return $_fieldExpression;
            } elseif (count($_arguments) == 0) {
                throw new SWIFT_KQL2_Exception('Missing field expression for ' . $_functionName . '()' . $this->GetOffsetDetails(false, $_startToken));
            } else {
                throw new SWIFT_KQL2_Exception('Too many arguments for ' . $_functionName . '()' . $this->GetOffsetDetails(false, $_startToken));
            }
        }

        return $_functionExpression;
    }

    /**
     * Parses ORDER BY Modifiers
     *
     * @author Andriy Lesyuk
     * @param string $_modifier
     * @param array $_expression
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_ParseOrderModifiers($_modifier, &$_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
            $_expression[SWIFT_KQL2::EXPRESSION_EXTRA] = array();
        }

        switch ($_modifier) {
            case 'ASC':
            case 'DESC':
                $_expression[SWIFT_KQL2::EXPRESSION_EXTRA][$_modifier] = false;
                break;

            default:
                return false;
                break;
        }

        return true;
    }

    /**
     * Parses Value Which Can be Field Name, String, Number etc.
     *
     * @author Andriy Lesyuk
     * @return array The Resulting Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ParseValue() # TODO: New parameter $_exprectedDataType? Same should be for GetExpression?
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_value = false;

        $_token = $this->GetToken();
        if ($_token) {

            // 'Some string', 'Table.Field Name' etc
            if (SWIFT_KQL2Lexer::TypeIsString($_token[1])) {
                $_value = $this->ParseString();

                // -3.14, 200 etc
            } elseif (SWIFT_KQL2Lexer::TypeIsRealNumber($_token[1]) && (substr_count($_token[0], '.') <= 1)) {
                $_value = $this->GetNumber();

                // Table.Field Name or NULL, FALSE etc
            } elseif (SWIFT_KQL2Lexer::TypeIsFieldName($_token[1])) {

                // Try subsequent chunks (field name without quotes)
                $_field = $this->GetSequentialStringChunks();
                if ($_field) {
                    $_value = $this->GetField($_field, true);
                    if ($_value) {
                        for ($_i = 0; $_i < $_field[4]; $_i++) {
                            $this->MoveNext();
                        }
                    }
                }

                // Try field name (without quotes)
                if (!$_value) {
                    $_value = $this->GetField($_token, true);
                    if ($_value) {
                        $this->MoveNext();
                    }
                }

                if (!$_value) {
                    if (SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {

                        // Try functions
                        $_value = $this->GetFunction();

                        // Try identifiers
                        if (!$_value) {
                            $_value = $this->GetIdentifier();
                        }

                        # TODO: Seconds e.g. 20d 5h 7m 0s
                        #} else {
                        # TODO: 2012.08.02
                    }
                }

            } elseif (SWIFT_KQL2Lexer::TypeIsSpecial($_token[1])) {

                // *
                if ($_token[0] == '*') {
                    $this->MoveNext();

                    $_value = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FIELD,
                        SWIFT_KQL2::EXPRESSION_DATA => array(false, '*')
                    );
                } elseif ($_token[0] == '$') {
                    $_value = $this->GetVariable();
                }

            }
        }

        return $_value;
    }

    /**
     * Tries Extracting the Field Name, Values etc from String
     *
     * @author Andriy Lesyuk
     * @return array|bool The Resulting Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ParseString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsString($_token[1])) {
            $_field = $this->GetField($_token);
            if ($_field) {
                $this->MoveNext();

                return $_field;
            }
        }

        $_token = $this->GetToken();
        if ($_token) {
            $this->MoveNext();

            return array(
                SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_VALUE,
                SWIFT_KQL2::EXPRESSION_DATA => $_token[0],
                SWIFT_KQL2::EXPRESSION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            );
        } else {
            return false;
        }
    }

    /**
     * Tries to Conver the Token into Number
     *
     * @author Andriy Lesyuk
     * @return array|bool The Resulting Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNumber()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsRealNumber($_token[1])) {
            $this->MoveNext();

            // should it be: $this->_type !==, === SWIFT_KQL2::TYPE_DOT ???
//            if ($_token[1] && SWIFT_KQL2::TYPE_DOT) {
            if (is_numeric($_token[0]) && strpos($_token[0],'.') !== false) {
                return array(
                    SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_VALUE,
                    SWIFT_KQL2::EXPRESSION_DATA => floatval($_token[0]),
                    SWIFT_KQL2::EXPRESSION_RETURNTYPE => SWIFT_KQL2::DATA_FLOAT
                );
            } else {
                return array(
                    SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_VALUE,
                    SWIFT_KQL2::EXPRESSION_DATA => (int)($_token[0]),
                    SWIFT_KQL2::EXPRESSION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
                );
            }
        }

        return false;
    }

    /**
     * Get Clause
     * Uses TokenEquals()
     *
     * @author Andriy Lesyuk
     * @return string|bool The Clause Operator or False
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetClause()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {

            foreach ($this->_clausesContainer as $_clauseName => $_clauseContainer) {
                if ($this->TokenEquals($_clauseName)) {
                    if (is_string($_clauseContainer)) {
                        return $_clauseContainer;
                    } else {
                        return $_clauseName;
                    }
                }
            }

        }

        return false;
    }

    /**
     * Check if Negation (NOT or !) is Present
     *
     * @author Andriy Lesyuk
     * @return bool Whether Negation was Specified
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetNegation()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && (
                (SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1]) && (mb_strtoupper($_token[0]) == 'NOT')) ||
                (SWIFT_KQL2Lexer::TypeIsSpecial($_token[1]) && ($_token[0] == '!')))) {
            $this->MoveNext();

            return true;
        }

        return false;
    }

    /**
     * Get Operator from KQL
     *
     * @author Andriy Lesyuk
     * @return array The Resulting Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetOperator()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_result = false;
        $_negation = $this->GetNegation();

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsOperator($_token[1]) && !in_array($_token[0], array(',', '(', ')', ':'))) {

            if (SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
                foreach ($this->_operatorsContainer as $_operator => $_operatorContainer) { // FIXME: Should be isset() else while?
                    if ($this->TokenEquals($_operator)) {

                        if (is_string($_operatorContainer)) {
                            $_result = $_operatorContainer;
                        } else {
                            $_result = $_operator;
                        }
                        break;
                    }
                }

            } else {
                $_operatorChunks = $this->GetSequentialOperatorChunks();
                if (_is_array($_operatorChunks)) {
                    for ($_i = count($_operatorChunks); $_i > 0; $_i--) {
                        $_operator = implode('', array_slice($_operatorChunks, 0, $_i));
                        if (isset($this->_operatorsContainer[$_operator])) {
                            for (; $_i > 0; $_i--) {
                                $this->MoveNext();
                            }

                            if (is_string($this->_operatorsContainer[$_operator])) {
                                $_result = $this->_operatorsContainer[$_operator];
                            } else {
                                $_result = $_operator;
                            }
                            break;
                        }
                    }
                }
            }

        }

        if ($_result && $_negation) {
            if (isset($this->_operatorsContainer[$_result][SWIFT_KQLSchema::OPERATOR_NEGATIVE])) {
                $_result = $this->_operatorsContainer[$_result][SWIFT_KQLSchema::OPERATOR_NEGATIVE];
            } else {
                throw new SWIFT_KQL2_Exception('Operator ' . $_result . ' cannot be used with NOT ' . $this->GetOffsetDetails(false));
            }
        }

        return $_result;
    }

    /**
     * Get Identifier
     *
     * @author Andriy Lesyuk
     * @return array|bool The Resulting Array
     */
    protected function GetIdentifier()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
            $_identifierName = mb_strtoupper($_token[0]);
            if (isset($this->_identifiersContainer[$_identifierName])) {
                $this->MoveNext();

                if (is_string($this->_identifiersContainer[$_identifierName])) {
                    $_identifierProperties = $this->_identifiersContainer[$this->_identifiersContainer[$_identifierName]];
                } else {
                    $_identifierProperties = $this->_identifiersContainer[$_identifierName];
                }

                if (isset($_identifierProperties[SWIFT_KQLSchema::IDENTIFIER_PARSER])) {
                    $methodName = $_identifierProperties[SWIFT_KQLSchema::IDENTIFIER_PARSER];
                    return $this->$methodName($_identifierName);
                } elseif (array_key_exists(SWIFT_KQLSchema::IDENTIFIER_VALUE, $_identifierProperties)) {
                    $_value = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_VALUE,
                        SWIFT_KQL2::EXPRESSION_DATA => $_identifierProperties[SWIFT_KQLSchema::IDENTIFIER_VALUE]
                    );

                    if (isset($_identifierProperties[SWIFT_KQLSchema::IDENTIFIER_TYPE])) {
                        $_value[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_identifierProperties[SWIFT_KQLSchema::IDENTIFIER_TYPE];
                    }

                    return $_value;
                } else {
                    throw new SWIFT_KQL2_Exception('Invalid or unsupported identifier ' . $_identifierName . $this->GetOffsetDetails(false));
                }
            }
        }

        return false;
    }

    /**
     * Get Variable
     *
     * @author Andriy Lesyuk
     * @return array|bool The Variable Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetVariable()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsSpecial($_token[1]) && ($_token[0] == '$')) {
            $this->MoveNext();

            $_token = $this->GetToken();
            if ($_token) {
                if (SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1]) ||
                    SWIFT_KQL2Lexer::TypeIsString($_token[1])) {
                    if (isset($this->_variablesContainer[mb_strtoupper($_token[0])]) ||
                        isset($this->_inlineVariables[mb_strtolower($_token[0])])) {
                        $this->MoveNext();

                        return array(
                            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_VARIABLE,
                            SWIFT_KQL2::EXPRESSION_DATA => $_token[0]
                        );
                    } else {
                        throw new SWIFT_KQL2_Exception('Undefined variable $\'' . $_token[0] . '\'' . $this->GetOffsetDetails(false));
                    }
                } else {
                    throw new SWIFT_KQL2_Exception('Expected variable identifier or string, got ' . $_token[0] . $this->GetOffsetDetails(false));
                }
            } else {
                throw new SWIFT_KQL2_Exception('Missing variable name' . $this->GetOffsetDetails());
            }
        }

        return false;
    }

    /**
     * Gets Function
     *
     * @author Andriy Lesyuk
     * @return array|bool The Resulting Array
     */
    protected function GetFunction()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
            $_nextToken = $this->GetNextToken();
            if ($_nextToken && SWIFT_KQL2Lexer::TypeIsSpecial($_nextToken[1]) && ($_nextToken[0] == '(')) {

                $_functionName = mb_strtoupper($_token[0]);
                if (isset($this->_functionsContainer[$_functionName])) {
                    $this->MoveNext();

                    if (is_string($this->_functionsContainer[$_functionName])) {
                        $_functionName = $this->_functionsContainer[$_functionName];
                    }

                    $_functionProperties = $this->_functionsContainer[$_functionName];

                    $_functionArgumentsProperties = false;
                    if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_ARGUMENTS])) {
                        $_functionArgumentsProperties = $_functionProperties[SWIFT_KQLSchema::FUNCTION_ARGUMENTS];
                    }

                    // Execute parser
                    if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_PARSER])) {
                        $methodName = $_functionProperties[SWIFT_KQLSchema::FUNCTION_PARSER];
                        $_functionExpression = $this->$methodName($_functionName, $_functionArgumentsProperties);
                    } else {
                        $_arguments = $this->GetFunctionArguments($_functionArgumentsProperties);

                        $_functionExpression = array(
                            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FUNCTION,
                            SWIFT_KQL2::EXPRESSION_DATA => array($_functionName, $_arguments)
                        );

                        // Set function return type
                        if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_RETURNTYPE])) {
                            if (is_array($_functionProperties[SWIFT_KQLSchema::FUNCTION_RETURNTYPE])) {
                                $_argumentIndex = reset($_functionProperties[SWIFT_KQLSchema::FUNCTION_RETURNTYPE]);

                                // Take type from the function argument
                                if (isset($_arguments[$_argumentIndex - 1]) && isset($_arguments[$_argumentIndex - 1][SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                                    if (isset($_functionArgumentsProperties[$_argumentIndex - 1])) {
                                        $_functionExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::GetBestCastToType($_arguments[$_argumentIndex - 1][SWIFT_KQL2::EXPRESSION_RETURNTYPE], $_functionArgumentsProperties[$_argumentIndex - 1]);
                                    } else {
                                        $_functionExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_arguments[$_argumentIndex - 1][SWIFT_KQL2::EXPRESSION_RETURNTYPE];
                                    }
                                }
                            } else {
                                $_functionExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_functionProperties[SWIFT_KQLSchema::FUNCTION_RETURNTYPE];
                            }
                        }
                    }

                    // Process arguments
                    foreach ($_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][1] as $_argumentIndex => $_argumentContainer) {
                        if (($_argumentContainer[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                            ($_argumentContainer[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING)) {
                            $_functionArgumentType = false;
                            if ($_functionArgumentsProperties && isset($_functionArgumentsProperties[$_argumentIndex])) {
                                $_functionArgumentType = $_functionArgumentsProperties[$_argumentIndex];
                            } // FIXME: Maybe should just skip?

                            if (!SWIFT_KQLSchema::ArgumentTypeEqual(SWIFT_KQL2::DATA_STRING, $_functionArgumentType) &&
                                !SWIFT_KQLSchema::ArgumentTypeEqual(SWIFT_KQL2::DATA_ANY, $_functionArgumentType)) {
                                $this->ConvertStringToValue($_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][1][$_argumentIndex], $_functionArgumentType);
                            }
                        }
                    }

                    // Execute post-parser
                    if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_POSTPARSER])) {
                        $methodName = $_functionProperties[SWIFT_KQLSchema::FUNCTION_POSTPARSER];
                        return $this->$methodName($_functionExpression, $_token);
                    } else {
                        return $_functionExpression;
                    }

                } else {
                    if (!isset($this->_clausesCache[$_functionName]) &&
                        !isset($this->_operatorsCache[$_functionName])) {
                        throw new SWIFT_KQL2_Exception('Unknown or unsupported function ' . $_functionName . $this->GetOffsetDetails(false));
                    }
                }
            }
        }

        return false;
    }

    /**
     * Default Parser for Function Arguments
     *
     * @author Andriy Lesyuk
     * @param array $_functionArguments
     * @return array The Arguments Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFunctionArguments($_functionArguments)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_arguments = array();

        $_closeParenthesis = false;
        $_expressionExpected = false; // For FUNCTION()
        if ($_openParenthesis = $this->GetOpenParenthesis()) {
            while ($this->GetToken() && !($_closeParenthesis = $this->GetClosedParenthesis())) {
                $_expression = $this->GetExpression();
                if ($_expression) {
                    $_arguments[] = $_expression;

                    if ($this->GetComma()) {
                        $_expressionExpected = true;
                    } else {
                        $_expressionExpected = false;
                    }
                } else {
                    throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
                }
            }
        }

        if (!$_closeParenthesis) {
            throw new SWIFT_KQL2_Exception('Missing closing parenthesis for (' . $this->GetOffsetDetails(false, $_openParenthesis));

        } elseif ($_expressionExpected) {
            $this->MoveBack();

            // FIXME: Or redundant comma? Take into account function properties?
            throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
        }

        return $_arguments;
    }

    /**
     * Get Full Operator by Chunks (e.g. >=)
     *
     * @author Andriy Lesyuk
     * @return array The Operator Chunks
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetSequentialOperatorChunks()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chunks = array();

        $_token = $this->GetToken();
        while ($_token && SWIFT_KQL2Lexer::TypeIsSpecial($_token[1])) {
            $_chunks[] = $_token[0];

            $_token = $this->GetNextToken();
        }

        return $_chunks;
    }

    /**
     * Recognizing Fields Without Quotes
     *
     * @author Andriy Lesyuk
     * @return array|bool The Field
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetSequentialStringChunks()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_type = 0;
        $_chunks = array();
        $_start = $_end = false;

        $_token = $this->GetToken();
        while ($_token && SWIFT_KQL2Lexer::TypeIsFieldName($_token[1])) {
            $_tokenName = mb_strtoupper($_token[0]);
            if (($_tokenName != 'NOT') &&
                !isset($this->_clausesCache[$_tokenName]) &&
                !isset($this->_operatorsCache[$_tokenName]) &&
                !isset($this->_preModifiersContainer[$_tokenName]) &&
                !isset($this->_postModifiersContainer[$_tokenName]) &&
                !isset($this->_identifiersContainer[$_tokenName])) {
                $_nextToken = $this->GetNextToken();

                // Check for functions
                if ($_nextToken && SWIFT_KQL2Lexer::TypeIsSpecial($_nextToken[1]) && ($_nextToken[0] == '(') &&
                    isset($this->_functionsContainer[$_tokenName])) {
                    break;
                }

                // Save token
                $_chunks[] = $_token[0];
                $_type |= $_token[1];
                if ($_start === false) {
                    $_start = $_token[2];
                }
                $_end = $_token[3];

                $_token = $_nextToken;
            } else {
                break;
            }
        }

        if (count($_chunks) > 1) {
            return array(implode(' ', $_chunks), $_type, $_start, $_end, count($_chunks));
        } else {
            return false;
        }
    }

    /**
     * Get Field Container
     *
     * @author Andriy Lesyuk
     * @param mixed $_token
     * @param bool $_force
     * @return array|bool The Field
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetField($_token, $_force = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldChunks = explode('.', $_token[0]);
        if (count($_fieldChunks) > 1) {

            $_customField = $this->GetCustomField($_token, $_force);
            if ($_customField) {
                return $_customField;

            } elseif (count($_fieldChunks) == 2) {
                $_tableName = $this->GetTableName($_fieldChunks[0]);
                if ($_tableName) {
                    $_fieldName = $this->GetFieldName($_tableName, $_fieldChunks[1]);
                    if ($_fieldName) {

                        // TODO: update _fieldNamesCache[$_token[0]]

                        $this->RegisterTable($_tableName);

                        $_fieldExpression = array(
                            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FIELD,
                            SWIFT_KQL2::EXPRESSION_DATA => array($_tableName, $_fieldName)
                        );

                        $_fieldType = $this->GetFieldType($_tableName, $_fieldName);
                        if ($_fieldType) {
                            $_fieldExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_fieldType;
                        }

                        return $_fieldExpression;
                    } else {
                        if ($_force) {
                            throw new SWIFT_KQL2_Exception('Field \'' . $_fieldChunks[1] . '\' does not exist in table \'' . $_fieldChunks[0] . '\'' . $this->GetOffsetDetails(false));
                        }
                    }
                }
            }

        } elseif ((count($_fieldChunks) == 1) && $this->_primaryTableName && $_force) {
            $_fieldName = $this->GetFieldName($this->_primaryTableName, $_fieldChunks[0]);
            if ($_fieldName) {

                $_fieldExpression = array(
                    SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FIELD,
                    SWIFT_KQL2::EXPRESSION_DATA => array($this->_primaryTableName, $_fieldName)
                );

                $_fieldType = $this->GetFieldType($this->_primaryTableName, $_fieldName);
                if ($_fieldType) { // FIXME: What about Custom and Linked? Are they guaranteed?
                    $_fieldExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_fieldType;
                }

                return $_fieldExpression;
            }
        }

        return false;
    }

    /**
     * Get Custom Field Container
     * NOTE: PostParseCustomField() is very similar
     *
     * @author Andriy Lesyuk
     * @param array $_token
     * @param bool $_force
     * @return array|bool The Custom Field
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetCustomField($_token, $_force = false) // TODO: Nesting level
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldChunks = explode('.', $_token[0]);
        if (count($_fieldChunks) > 1) {
            $_tableName = $this->_primaryTableName;
            $_tableOriginalName = false;

            $_customFieldMark = false;
            if (strcasecmp(trim($_fieldChunks[0]), 'custom fields') == 0) {
                $_customFieldMark = 0;
            } elseif (strcasecmp(trim($_fieldChunks[1]), 'custom fields') == 0) {
                $_customFieldMark = 1;
            }

            if ($_customFieldMark !== false) {

                $_customFieldArgs = count($_fieldChunks) - ($_customFieldMark + 1);
                if (($_customFieldArgs > 0) && ($_customFieldArgs <= 2)) {

                    if ($_customFieldMark > 0) {
                        $_tableOriginalName = $_fieldChunks[0];
                        $_tableName = $this->GetTableName($_tableOriginalName);
                        if (!$_tableName) {
                            if ($_force) {
                                throw new SWIFT_KQL2_Exception('Unknown or unsupported table ' . $_tableOriginalName . $this->GetOffsetDetails(false));
                            }
                            return false;
                        }
                    }

                    $_groupTypes = self::GetCustomFieldGroupTypesByTableName($_tableName);
                    if (!empty($_groupTypes)) {
                        $this->BuildCustomFieldsCacheForTable($_tableName, $_groupTypes);
                    } else {
                        if ($_force) {
                            if ($_tableOriginalName) {
                                throw new SWIFT_KQL2_Exception('Custom fields not supported for table ' . $_tableOriginalName . $this->GetOffsetDetails(false));
                            } else {
                                throw new SWIFT_KQL2_Exception('Custom fields not supported for the primary table ' . $this->GetOffsetDetails(false));
                            }
                        }
                        return false;
                    }

                    $_customFieldGroupID = false;
                    if ($_customFieldArgs > 1) {
                        $_customFieldGroupTitle = mb_strtolower($_fieldChunks[$_customFieldMark + 1]);
                        if (isset($this->_customFieldGroupTitleMap[$_customFieldGroupTitle])) {
                            $_customFieldGroupID = $this->_customFieldGroupTitleMap[$_customFieldGroupTitle];
                            if (!isset($this->_customFieldTableMap[$_tableName][$_customFieldGroupID])) {
                                if ($_force) {
                                    if ($_tableOriginalName) {
                                        throw new SWIFT_KQL2_Exception('Custom field group \'' . $_fieldChunks[$_customFieldMark + 1] . '\' is not related to table \'' . $_tableOriginalName . '\'' . $this->GetOffsetDetails(false));
                                    } else {
                                        throw new SWIFT_KQL2_Exception('Custom field group \'' . $_fieldChunks[$_customFieldMark + 1] . '\' is not related to the primary table' . $this->GetOffsetDetails(false));
                                    }
                                }
                                return false;
                            }
                        } else {
                            if ($_force) {
                                throw new SWIFT_KQL2_Exception('Expected custom field group, got \'' . $_fieldChunks[$_customFieldMark + 1] . '\'' .
                                    sprintf(" at offset %d", $_token[2] + strlen(implode('', array_slice($_fieldChunks, 0, $_customFieldMark + 1))) + $_customFieldMark + 1));
                            }
                            return false;
                        }
                    }

                    $_customFieldID = false;
                    $_customFieldTitle = mb_strtolower($_fieldChunks[$_customFieldMark + $_customFieldArgs]);

                    if ($_customFieldTitle == '*') {
                        $_customFieldID = array();

                        if ($this->CurrentClause() != 'SELECT') { // TODO: + check nesting level + raise error if encrypted
                            throw new SWIFT_KQL2_Exception('Custom fields wildcard can be used only in SELECT clause' . $this->GetOffsetDetails(false));
                        }

                        // Try custom field title
                    } elseif (isset($this->_customFieldTitleMap[$_customFieldTitle])) {
                        if (isset($this->_customFieldTitleMap[$_customFieldTitle]['id'])) {
                            if (($this->_customFieldTitleMap[$_customFieldTitle]['table'] == $_tableName) &&
                                (!$_customFieldGroupID || ($this->_customFieldTitleMap[$_customFieldTitle]['group_id'] == $_customFieldGroupID))) {
                                $_customFieldID = $this->_customFieldTitleMap[$_customFieldTitle]['id'];
                            } else {
                                if ($_force) {
                                    if ($this->_customFieldTitleMap[$_customFieldTitle]['table'] != $_tableName) {
                                        if ($_tableOriginalName) {
                                            throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' is not related to table \'' . $_tableOriginalName . '\'' . $this->GetOffsetDetails(false));
                                        } else {
                                            throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' is not related to the primary table' . $this->GetOffsetDetails(false));
                                        }
                                    } elseif ($_customFieldGroupID) {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' is not in group \'' . $_fieldChunks[$_customFieldMark + 1] . '\'' . $this->GetOffsetDetails(false));
                                    }
                                }
                                return false;
                            }
                        } else {
                            foreach ($this->_customFieldTitleMap[$_customFieldTitle] as $_customField) {
                                if (($_customField['table'] == $_tableName) &&
                                    (!$_customFieldGroupID || ($_customField['group_id'] == $_customFieldGroupID))) {
                                    $_customFieldID = $_customField['id'];
                                    break;
                                }
                            }
                            if ($_customFieldID === false) {
                                if ($_force) {
                                    if ($_customFieldGroupID) {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' does not match table and/or group' . $this->GetOffsetDetails(false));
                                    } else {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' does not match table' . $this->GetOffsetDetails(false));
                                    }
                                }
                                return false;
                            }
                        }

                        // Try custom field name
                    } elseif (isset($this->_customFieldNameMap[$_customFieldTitle])) {
                        if (($this->_customFieldNameMap[$_customFieldTitle]['table'] == $_tableName) &&
                            (!$_customFieldGroupID || ($this->_customFieldNameMap[$_customFieldTitle]['group_id'] == $_customFieldGroupID))) {
                            $_customFieldID = $this->_customFieldNameMap[$_customFieldTitle]['id'];
                        } else {
                            if ($_force) {
                                if ($this->_customFieldNameMap[$_customFieldTitle]['table'] != $_tableName) {
                                    if ($_tableOriginalName) {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' is not related to table \'' . $_tableOriginalName . '\'' . $this->GetOffsetDetails(false));
                                    } else {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' is not related to the primary table' . $this->GetOffsetDetails(false));
                                    }
                                } elseif ($_customFieldGroupID) {
                                    throw new SWIFT_KQL2_Exception('Custom field \'' . $_fieldChunks[$_customFieldMark + $_customFieldArgs] . '\' is not in group \'' . $_fieldChunks[$_customFieldMark + 1] . '\'' . $this->GetOffsetDetails(false));
                                }
                            }
                            return false;
                        }

                    } else {
                        if ($_force) {
                            throw new SWIFT_KQL2_Exception('Custom field \'' . $_token[0] . '\' does not exist' . $this->GetOffsetDetails(false));
                        }
                        return false;
                    }

                    return $this->ProcessCustomField($_tableName, $_customFieldGroupID, $_customFieldID);

                } elseif ($_force) {
                    if ($_customFieldArgs <= 0) {
                        throw new SWIFT_KQL2_Exception('Custom field group, title or name expected' .
                            sprintf(" at offset %d", $_token[2] + strlen(implode('', array_slice($_fieldChunks, 0, $_customFieldMark + 1))) + $_customFieldMark));
                    } else {
                        throw new SWIFT_KQL2_Exception('Too many dots in custom field expression ' . $_token[0] . $this->GetOffsetDetails(false));
                    }
                }

            }

        }

        return false;
    }

    /**
     * Parses *CONCAT*() Arguments
     *
     * @author Andriy Lesyuk
     * @param string $_functionName
     * @return array The Arguments
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_ParseConcat($_functionName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_functionProperties = $this->_functionsContainer[$_functionName];

        $_arguments = array();

        $_expressionExpected = true;
        $_closeParenthesis = null;
        if ($_openParenthesis = $this->GetOpenParenthesis()) {
            while ($this->GetToken() && !($_closeParenthesis = $this->GetClosedParenthesis())) {
                $_expression = $this->GetExpression();

                if ($_expression) {
                    $_arguments[] = $_expression;

                    if ($this->GetComma()) {
                        $_expressionExpected = true;
                    } else {
                        $_expressionExpected = false;
                    }
                } else {
                    throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
                }
            }
        }


        if ($_openParenthesis && is_null($_closeParenthesis)) {
            throw new SWIFT_KQL2_Exception('Missing closing parenthesis for (' . $this->GetOffsetDetails(false, $_openParenthesis));

        } elseif ($_expressionExpected) {
            $_token = $this->MoveBack();

            $_minimalArgumentsCount = 1;
            if ($_functionProperties && isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_OPTIONALARGUMENTS])) {
                $_minimalArgumentsCount = $_functionProperties[SWIFT_KQLSchema::FUNCTION_OPTIONALARGUMENTS] - 1;
            }

            if (count($_arguments) < $_minimalArgumentsCount) {
                throw new SWIFT_KQL2_Exception($_functionName . '() requires at least ' . $_minimalArgumentsCount . ' argument(s)' . $this->GetOffsetDetails());
            } else {
                $_prevToken = $this->MoveBack();

                if ($_prevToken && $_prevToken[0] == ',') {
                    throw new SWIFT_KQL2_Exception('Possible redundant comma' . $this->GetOffsetDetails(false));
                } else {
                    throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails(true, $_token));
                }
            }
        }

        return array(
            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FUNCTION,
            SWIFT_KQL2::EXPRESSION_DATA => array($_functionName, $_arguments)
        );
    }

    /**
     * Parses CUSTOMFIELD() Arguments
     *
     * @author Andriy Lesyuk
     * @param string $_functionName
     * @param array $_functionArguments
     * @return array The Arguments
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_ParseCustomField($_functionName, $_functionArguments)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_arguments = array();

        $_expressionExpected = false;
        $_closeParenthesis = null;
        if ($_openParenthesis = $this->GetOpenParenthesis()) {
            while ($this->GetToken() && !($_closeParenthesis = $this->GetClosedParenthesis())) {
                $_expression = false;

                // Check if the first argument is table
                if (empty($_arguments)) {
                    $_table = $this->GetTable();
                    if ($_table) {
                        $_expression = array(
                            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FIELD,
                            SWIFT_KQL2::EXPRESSION_DATA => array($_table)
                        );
                    }
                }

                if ($_expression === false) {
                    $_expression = $this->GetExpression();
                }

                if ($_expression) {
                    $_arguments[] = $_expression;

                    if ($this->GetComma()) {
                        $_expressionExpected = true;
                    } else {
                        $_expressionExpected = false;
                    }
                } else {
                    throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
                }
            }
        }

        if ($_openParenthesis && is_null($_closeParenthesis)) {
            throw new SWIFT_KQL2_Exception('Missing closing parenthesis for (' . $this->GetOffsetDetails(false, $_openParenthesis));

        } elseif ($_expressionExpected) {
            $this->MoveBack();

            // FIXME: Or redundant comma? Take into account function properties?
            throw new SWIFT_KQL2_Exception('Expected field, value or expression' . $this->GetOffsetDetails());
        }

        return array(
            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FUNCTION,
            SWIFT_KQL2::EXPRESSION_DATA => array($_functionName, $_arguments)
        );
    }

    /**
     * Parses MONTHRANGE() and MONTH() Arguments
     * NOTE: Accepts also arguments like MONTH(August 2012) (without quotes) - needed for backwards compatibility
     *
     * @author Andriy Lesyuk
     * @param string $_functionName
     * @param array $_functionArguments
     * @return array The Arguments
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_ParseMonthRange($_functionName, $_functionArguments)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_arguments = array();

        $_expressionExpected = false;
        $_closeParenthesis = null;
        if ($_openParenthesis = $this->GetOpenParenthesis()) {
            while (($_token = $this->GetToken()) && !($_closeParenthesis = $this->GetClosedParenthesis())) {
                if (SWIFT_KQL2Lexer::TypeIsString($_token[1])) {
                    $_arguments[] = $this->ParseString();

                } elseif (SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
                    $_argumentChunks = array();

                    do {
                        $this->MoveNext();

                        $_argumentChunks[] = $_token[0];
                    } while (($_token = $this->GetToken()) && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1]));

                    $_arguments[] = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_VALUE,
                        SWIFT_KQL2::EXPRESSION_DATA => implode(' ', $_argumentChunks),
                        SWIFT_KQL2::EXPRESSION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
                    );
                } else { // FIXME: Field name...
                    throw new SWIFT_KQL2_Exception('Expected month value' . $this->GetOffsetDetails());
                }

                if ($this->GetComma()) {
                    $_expressionExpected = true;
                } else {
                    $_expressionExpected = false;
                }
            }
        }

        if ($_openParenthesis && is_null($_closeParenthesis)) {
            throw new SWIFT_KQL2_Exception('Missing closing parenthesis for (' . $this->GetOffsetDetails(false, $_openParenthesis));

        } elseif ($_expressionExpected) {
            $this->MoveBack();

            // FIXME: Or redundant comma? Take into account function properties?
            throw new SWIFT_KQL2_Exception('Expected month value' . $this->GetOffsetDetails());
        }

        if (($_functionName == 'MONTHRANGE') && (count($_arguments) != 2)) {
            throw new SWIFT_KQL2_Exception('MONTHRANGE() requires exactly two arguments' . $this->GetOffsetDetails());
        }

        return array(
            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FUNCTION,
            SWIFT_KQL2::EXPRESSION_DATA => array($_functionName, $_arguments)
        );
    }

    /**
     * Post-Parses CUSTOMFIELD() Function
     * NOTE: GetCustomField() is very similar
     *
     * @author Andriy Lesyuk
     * @param array $_functionExpression
     * @param array|false $_startToken
     * @return array The Custom Field Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_PostParseCustomField($_functionExpression, $_startToken = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (($_functionExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
            ($_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][SWIFT_KQL2::EXPRESSION_TYPE] == 'CUSTOMFIELD')) {
            $_tableName = $this->_primaryTableName;
            $_arguments = $_functionExpression[SWIFT_KQL2::EXPRESSION_DATA][SWIFT_KQL2::EXPRESSION_DATA];

            if (count($_arguments) > 0) {
                $_argumentIndex = 0;

                if (($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) &&
                    (count($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA]) == 1)) { // Only table
                    $_tableName = $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA][$_argumentIndex];

                    $_argumentIndex++;
                }

                $_groupTypes = self::GetCustomFieldGroupTypesByTableName($_tableName);
                if (!empty($_groupTypes)) {
                    $this->BuildCustomFieldsCacheForTable($_tableName, $_groupTypes);
                } else {
                    throw new SWIFT_KQL2_Exception('Custom fields not supported for the table ' . $this->GetOffsetDetails(false, $_startToken));
                }

                $_customFieldGroupID = false;
                if ((count($_arguments) - $_argumentIndex) > 1) {
                    if (($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) &&
                        ($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA][0] === false) &&
                        ($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA][1] == '*')) {

                        $_argumentIndex++;
                    } elseif ($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) {

                        $_customFieldGroupTitle = mb_strtolower($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA]);
                        if (isset($this->_customFieldGroupTitleMap[$_customFieldGroupTitle])) {
                            $_customFieldGroupID = $this->_customFieldGroupTitleMap[$_customFieldGroupTitle];
                            if (!isset($this->_customFieldTableMap[$_tableName][$_customFieldGroupID])) {
                                throw new SWIFT_KQL2_Exception('Custom field group \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' is not related to the table' . $this->GetOffsetDetails(false, $_startToken));
                            }

                            $_argumentIndex++;
                        } else {
                            throw new SWIFT_KQL2_Exception('Custom field group \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' does not exist' . $this->GetOffsetDetails(false, $_startToken));
                        }

                    } else {
                        throw new SWIFT_KQL2_Exception('Expected custom field group as 2nd argument of CUSTOMFIELD(), got ' . SWIFT_KQL2::GetElementTypeString($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_TYPE]) . $this->GetOffsetDetails(false, $_startToken));
                    }
                }

                if ((count($_arguments) - $_argumentIndex) == 1) {
                    $_customFieldID = false;

                    if (($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) &&
                        ($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA][0] === false) &&
                        ($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA][1] == '*')) {
                        $_customFieldID = array();

                        if ($this->CurrentClause() != 'SELECT') { // TODO: + check nesting level
                            throw new SWIFT_KQL2_Exception('Custom fields wildcard can be used only in SELECT clause' . $this->GetOffsetDetails(false, $_startToken));
                        }

                    } elseif ($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) {
                        $_customFieldTitle = mb_strtolower($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA]);

                        // Try custom field title
                        if (isset($this->_customFieldTitleMap[$_customFieldTitle])) {
                            if (isset($this->_customFieldTitleMap[$_customFieldTitle]['id'])) {
                                if (($this->_customFieldTitleMap[$_customFieldTitle]['table'] == $_tableName) &&
                                    (!$_customFieldGroupID || ($this->_customFieldTitleMap[$_customFieldTitle]['group_id'] == $_customFieldGroupID))) {
                                    $_customFieldID = $this->_customFieldTitleMap[$_customFieldTitle]['id'];
                                } else {
                                    if ($this->_customFieldTitleMap[$_customFieldTitle]['table'] != $_tableName) {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' is not related to the table' . $this->GetOffsetDetails(false, $_startToken));
                                    } elseif ($_customFieldGroupID) {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' is not in group \'' . $_arguments[$_argumentIndex - 1][SWIFT_KQL2::EXPRESSION_DATA] . '\'' . $this->GetOffsetDetails(false, $_startToken));
                                    }
                                }
                            } else {
                                foreach ($this->_customFieldTitleMap[$_customFieldTitle] as $_customField) {
                                    if (($_customField['table'] == $_tableName) &&
                                        (!$_customFieldGroupID || ($_customField['group_id'] == $_customFieldGroupID))) {
                                        $_customFieldID = $_customField['id'];
                                        break;
                                    }
                                }
                                if ($_customFieldID === false) {
                                    if ($_customFieldGroupID) {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' does not match table and/or group' . $this->GetOffsetDetails(false, $_startToken));
                                    } else {
                                        throw new SWIFT_KQL2_Exception('Custom field \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' does not match table' . $this->GetOffsetDetails(false, $_startToken));
                                    }
                                }
                            }

                            // Try custom field name
                        } elseif (isset($this->_customFieldNameMap[$_customFieldTitle])) {
                            if (($this->_customFieldNameMap[$_customFieldTitle]['table'] == $_tableName) &&
                                (!$_customFieldGroupID || ($this->_customFieldNameMap[$_customFieldTitle]['group_id'] == $_customFieldGroupID))) {
                                $_customFieldID = $this->_customFieldNameMap[$_customFieldTitle]['id'];
                            } else {
                                if ($this->_customFieldNameMap[$_customFieldTitle]['table'] != $_tableName) {
                                    throw new SWIFT_KQL2_Exception('Custom field \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' is not related to the table' . $this->GetOffsetDetails(false, $_startToken));
                                } elseif ($_customFieldGroupID) {
                                    throw new SWIFT_KQL2_Exception('Custom field \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' is not in group \'' . $_arguments[$_argumentIndex - 1][SWIFT_KQL2::EXPRESSION_DATA] . '\'' . $this->GetOffsetDetails(false, $_startToken));
                                }
                            }

                        } else {
                            throw new SWIFT_KQL2_Exception('Custom field \'' . $_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_DATA] . '\' does not exist' . $this->GetOffsetDetails(false, $_startToken));
                        }
                    } else {
                        throw new SWIFT_KQL2_Exception('Expected custom field title or name as last argument of CUSTOMFIELD(), got ' . SWIFT_KQL2::GetElementTypeString($_arguments[$_argumentIndex][SWIFT_KQL2::EXPRESSION_TYPE]) . $this->GetOffsetDetails(false, $_startToken));
                    }

                    return $this->ProcessCustomField($_tableName, $_customFieldGroupID, $_customFieldID);

                } elseif ((count($_arguments) - $_argumentIndex) > 1) {
                    throw new SWIFT_KQL2_Exception('Too many arguments for CUSTOMFIELD()' . $this->GetOffsetDetails(false, $_startToken));
                } else {
                    throw new SWIFT_KQL2_Exception('Missing last argument (custom field title or name) for CUSTOMFIELD()' . $this->GetOffsetDetails(false, $_startToken));
                }

            } else {
                throw new SWIFT_KQL2_Exception('At least one argument required for CUSTOMFIELD()' . $this->GetOffsetDetails(false, $_startToken));
            }
        }

        return $_functionExpression;
    }

    /**
     * Processes Custom Fields
     * NOTE: Adds customfieldvalues to Table List and Populates with Array of Custom Fields if * is Used
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param mixed $_customFieldGroupID
     * @param mixed $_customFieldID
     * @return array The Custom Field
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessCustomField($_tableName, $_customFieldGroupID, $_customFieldID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (is_array($_customFieldID)) {
            if ($_customFieldGroupID) {
                foreach ($this->_customFieldTableMap[$_tableName][$_customFieldGroupID] as $_customFieldIndex => $_customFieldContainer) {
                    $_customFieldTable = 'customfield' . $_customFieldIndex;
                    $_joinCommand = $_customFieldTable . ".customfieldid = '" . $_customFieldIndex . "' AND " .
                        $_customFieldTable . ".fieldvalue != '' AND " .
                        $_customFieldTable . ".typeid = " . $_tableName . "." . $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_PRIMARYKEY];

                    $this->RegisterTable($_customFieldTable, $_joinCommand, true, 'customfieldvalues');

                    $_customFieldID[] = $_customFieldIndex;
                }
            } else {
                $_customFieldGroupID = array();
                foreach ($this->_customFieldTableMap[$_tableName] as $_customFieldGroupIndex => $_customFieldGroupContainer) {
                    $_customFieldGroupID[] = $_customFieldGroupIndex;
                    foreach ($_customFieldGroupContainer as $_customFieldIndex => $_customFieldContainer) {
                        $_customFieldTable = 'customfield' . $_customFieldIndex;
                        $_joinCommand = $_customFieldTable . ".customfieldid = '" . $_customFieldIndex . "' AND " .
                            $_customFieldTable . ".fieldvalue != '' AND " .
                            $_customFieldTable . ".typeid = " . $_tableName . "." . $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_PRIMARYKEY];

                        $this->RegisterTable($_customFieldTable, $_joinCommand, true, 'customfieldvalues');

                        $_customFieldID[] = $_customFieldIndex;
                    }
                }
            }
        } else {
            $_customFieldTable = 'customfield' . $_customFieldID;
            $_customFieldContainer = $this->_customFieldIDMap[$_customFieldID];
            $_joinCommand = $_customFieldTable . ".customfieldid = '" . $_customFieldID . "' AND " .
                $_customFieldTable . ".fieldvalue != '' AND " .
                $_customFieldTable . ".typeid = " . $_tableName . "." . $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_PRIMARYKEY];

            $this->RegisterTable($_customFieldTable, $_joinCommand, true, 'customfieldvalues');
        }

        $_customFieldExpression = array(
            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_CUSTOMFIELD,
            SWIFT_KQL2::EXPRESSION_DATA => array($_tableName, $_customFieldGroupID, $_customFieldID)
        );

        if (!is_array($_customFieldID)) {
            $_customField = $this->_customFieldIDMap[$_customFieldID];

            $_customFieldExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = SWIFT_KQL2::ConvertCustomFieldToKQLType($_customField['type']);
        }

        return $_customFieldExpression;
    }

    /**
     * Check if String is the Table Name
     *
     * @author Andriy Lesyuk
     * @param string $_string
     * @return string|bool The Table Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTableName($_string)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableLabel = mb_strtolower(trim($_string));

        if (isset($this->_schemaContainer[$_tableLabel])) {
            return $_tableLabel;
        }

        if (isset($this->_tableSchemaMap[$_tableLabel])) {
            return $this->_tableSchemaMap[$_tableLabel];
        }

        // For compatibility (try removing spaces etc)
        $_tableLabel = Clean($_tableLabel);

        if (isset($this->_schemaContainer[$_tableLabel])) {
            return $_tableLabel;
        }

        return false;
    }

    /**
     * Check if String is the Field Name
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param string $_string
     * @return string|bool The Field Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetFieldName($_tableName, $_string)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldLabel = mb_strtolower(trim($_string));

        if ($_fieldLabel == '*') {
            return '*';

        } elseif (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldLabel])) {
            return $_fieldLabel;

        } else {

            foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_fieldName => $_fieldContainer) {

                // Try on a combination of table name and field name
                $_activeFieldLabel = SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_fieldName);
                if (!empty($_activeFieldLabel) && (strcasecmp($_activeFieldLabel, $_fieldLabel) == 0)) {
                    return $_fieldName;
                } else {

                    // Try on just the field name
                    $_activeFieldLabel = SWIFT_KQLSchema::GetLabel($_fieldName);
                    if (!empty($_activeFieldLabel) && (strcasecmp($_activeFieldLabel, $_fieldLabel) == 0)) {
                        return $_fieldName;
                    }
                }
            }

            // For compatibility (try removing spaces etc)
            $_fieldLabel = Clean($_fieldLabel);

            if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldLabel])) {
                return $_fieldLabel;
            }
        }

        return false;
    }

    /**
     * Get the Field Type
     * NOTE: Copied to SWIFT_KQL2Compiler::GetFieldType
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param string $_fieldName
     * @return int|bool The KQL Field Type
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetFieldType($_tableName, $_fieldName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_schemaContainer[$_tableName]) &&
            isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName])) {

            if ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_LINKED) {
                $_linkedToContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_LINKEDTO];

                list($_tableName, $_fieldName) = explode('.', $_linkedToContainer[1]);
                if (isset($this->_schemaContainer[$_tableName]) &&
                    isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName])) {
                    return self::ConvertKQLSchemaToKQLType($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE]);
                }

            } else {
                return self::ConvertKQLSchemaToKQLType($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE]);
            }
        }

        return false;
    }

    /**
     * Converts KQLSchema Type to More Accurate KQL Type
     *
     * @author Andriy Lesyuk
     * @param int $_kqlSchemaType
     * @return int|bool The KQL Type
     */
    public static function ConvertKQLSchemaToKQLType($_kqlSchemaType)
    {
        if (isset(self::$_kqlSchemaFieldTypeMap[$_kqlSchemaType])) {
            return self::$_kqlSchemaFieldTypeMap[$_kqlSchemaType];
        } else {
            return false;
        }
    }

    /**
     * Fetches Selector
     *
     * @author Andriy Lesyuk
     * @return mixed The Selector
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetSelector()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetColon()) {
            $_token = $this->GetToken();
            if ($_token && SWIFT_KQL2Lexer::TypeIsStringOperator($_token[1])) {
                $_selectorName = mb_strtoupper($_token[0]);
                if (isset($this->_selectorsContainer[$_selectorName])) {
                    $this->MoveNext();

                    if (is_string($this->_selectorsContainer[$_selectorName])) {
                        return $this->_selectorsContainer[$_selectorName];
                    } else {
                        return $_selectorName;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if the Table is Already in List
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @return bool True if Is, False if Not
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsTableRegistered($_tableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return isset($this->_tableList[$_tableName]);
    }

    /**
     * Adds the Table to the List
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param string|false $_joinCommand
     * @param bool $_update
     * @param string|false $_actualTable
     * @return bool True if Added, False if Not
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RegisterTable($_tableName, $_joinCommand = false, $_update = false, $_actualTable = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_tableList[$_tableName]) || ($_update === true)) {
            if ($_joinCommand || $_actualTable) {
                $this->_tableList[$_tableName] = array();

                if ($_actualTable) {
                    $this->_tableList[$_tableName][SWIFT_KQL2::TABLE_NAME] = $_actualTable;
                }
                if ($_joinCommand) {
                    $this->_tableList[$_tableName][SWIFT_KQL2::TABLE_JOIN] = $_joinCommand;
                }
            } else {
                $this->_tableList[$_tableName] = $_update;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns Current Clause
     *
     * @author Andriy Lesyuk
     * @return string The Current Clause Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CurrentClause()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_currentClause;
    }

    /**
     * Returns the Primary Table Name
     *
     * @author Andriy Lesyuk
     * @return string The Primary Table Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function PrimaryTable()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_primaryTableName;
    }

    /**
     * Checks if Token equals to $_tokenString (Checks Several Tokens)
     *
     * @author Andriy Lesyuk
     * @param string $_tokenString
     * @param string|bool $_splitMode
     * @return bool True if Equals and False Otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function TokenEquals($_tokenString, $_splitMode = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_token = $this->GetToken();
        if ($_token && SWIFT_KQL2Lexer::TypeIsOperator($_token[1])) {

            if (($_splitMode == ' ') || (($_splitMode === false) && preg_match('/^[A-Z_0-9 ]+$/', $_tokenString))) {
                $_tokenChunks = explode(' ', $_tokenString);
                $_splitMode = ' ';
            } else {
                $_tokenChunks = str_split($_tokenString);
                $_splitMode = '';
            }

            if (is_array($_tokenChunks) && (count($_tokenChunks) > 1)) {
                $_firstChunk = array_shift($_tokenChunks);
                if (strncasecmp($_token[0], $_firstChunk, strlen($_firstChunk)) == 0) {

                    $_offset = strlen($_firstChunk);
                    while (strlen($_token[0]) > $_offset) {
                        if (strncasecmp(substr($_token[0], $_offset), $_tokenChunks[0], strlen($_tokenChunks[0])) != 0) {
                            return false;
                        }
                        $_nextChunk = array_shift($_tokenChunks);
                        $_offset += strlen($_nextChunk);
                    }

                    $this->MoveNext();

                    if (!empty($_tokenChunks)) {
                        $this->TokenEquals(implode($_splitMode, $_tokenChunks), $_splitMode);
                    }

                    return true;
                }
            } else {
                if (strcasecmp($_token[0], $_tokenString) == 0) {
                    $this->MoveNext();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Wrapper Around PHP's mktime()
     * NOTE: Used to fix exceptions
     *
     * @param int $_hour
     * @param int $_minute
     * @param int $_second
     * @param int $_month
     * @param int $_day
     * @param int $_year
     * @param int $_isDst
     * @return int The Unix Timestamp
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _MakeTime($_hour = 0, $_minute = 0, $_second = 0, $_month = 0, $_day = 0, $_year = 0, $_isDst = -1)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_day == '') {
            $_day = 1;
        }

        return mktime($_hour, $_minute, $_second, $_month, $_day, $_year);
    }

    /**
     * Get Offset Information for Error Message
     *
     * @author Andriy Lesyuk
     * @param bool $_near
     * @param mixed $_token
     * @return string The Offset Information
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOffsetDetails($_near = true, $_token = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_message = '';

        if (!$_token) {
            $_token = $this->GetToken();
        }

        if ($_token) {
            $_message .= sprintf(" at offset %d", $_token[2]);

            if ($_near) {
                $_message .= ' near ' . $_token[0];
            }
        }

        return $_message;
    }

    /**
     * Returns Readable Token Name
     *
     * @author Andriy Lesyuk
     * @param mixed $_token
     * @return string The Token Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTokenName($_token = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_token == false) {
            $_token = $this->GetToken();
        }

        if ($_token) {
            return $_token[0];
        } else {
            return 'end of query';
        }
    }

    /**
     * Gets Current Token
     * Resets Internal Pointer
     *
     * @author Andriy Lesyuk
     * @return string The Token
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetToken()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_index === false) {
            $_token = $this->_GetNextToken();
            if ($_token) {
                $this->_tokens[] = $_token;
                $this->_index = 0;
                $this->_current = $this->_index;
            }

        } else {
            $this->_current = $this->_index;

            $_token = $this->_tokens[$this->_index];
        }

        if ($_token && SWIFT_KQL2Lexer::TypeIsError($_token[1])) {
            throw new SWIFT_KQL2_Exception('Invalid character "' . $_token[0] . '"' . sprintf(" at offset %d", $_token[2]));
        }

        return $_token;
    }

    /**
     * Gets Next Token
     * Increases Internal Pointer
     *
     * @author Andriy Lesyuk
     * @return mixed The Next Token
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNextToken()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (is_null($this->_tokens[$this->_current])) {
            return null;
        }

        if (($this->_current + 1) >= count($this->_tokens)) {
            $_token = $this->_GetNextToken();
            if ($_token) {
                $this->_tokens[] = $_token;
                $this->_current++;
            }

            return $_token;
        } else {
            $this->_current++;

            return $this->_tokens[$this->_current];
        }
    }

    /**
     * Get Previous Token
     * Decreases Internal Pointer
     *
     * @author Andriy Lesyuk
     * @return string|null The Previous Token
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPreviousToken()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_current <= 0) {
            return null;
        }

        $this->_current--;

        return $this->_tokens[$this->_current];
    }

    /**
     * Fetches Token Using Lexer
     *
     * @author Andriy Lesyuk
     * @return array|null The Token
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetNextToken()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        do {
            $_nextToken = $this->Lexer->NextToken();
        } while ($_nextToken && ($_nextToken == ' ') && !SWIFT_KQL2Lexer::TypeIsString($this->Lexer->GetTokenType()));

        if (!is_null($_nextToken)) {
            return array(
                $_nextToken,
                $this->Lexer->GetTokenType(),
                $this->Lexer->GetTokenStart(),
                $this->Lexer->GetTokenEnd()
            );
        } else {
            return null;
        }
    }

    /**
     * Moves to the Next Token
     *
     * @author Andriy Lesyuk
     * @return string|null The Token
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MoveNext()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (is_null($this->_tokens[$this->_index])) {
            return null;
        }

        if (($this->_index + 1) >= count($this->_tokens)) {
            $_token = $this->_GetNextToken();
            $this->_tokens[] = $_token;
        }

        $this->_index++;
        $this->_current = $this->_index;

        if ($this->_index > self::TOKENS_HISTORY) {
            array_shift($this->_tokens);
            $this->_index--;
            $this->_current = $this->_index;
        }

        return $this->_tokens[$this->_index];
    }

    /**
     * Move to the Previous Token
     * Used for Error Reporting
     *
     * @author Andriy Lesyuk
     * @return string|null The Token
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MoveBack()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_index > 0) {
            $this->_index--;
            $this->_current = $this->_index;

            return $this->_tokens[$this->_index];
        } else {
            return null;
        }
    }

    /**
     * Sets Internal Lexer's Buffer to the Specified KQL
     * NOTE: The function is intended for testing purposes only
     *
     * @author Andriy Lesyuk
     * @param string $_kql
     * @param string|false $_primaryTableName
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _InjectKQL($_kql, $_primaryTableName = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->InitializeState();
        $this->InitializeLexer($_kql);

        // Check primary table
        if ($_primaryTableName) {
            $_tableName = $this->GetTableName($_primaryTableName);
            if ($_tableName) {
                $this->_primaryTableName = $_tableName;
            } else {
                throw new SWIFT_KQL2_Exception('Invalid primary table ' . $_primaryTableName);
            }
        }

        return true;
    }

}

?>
