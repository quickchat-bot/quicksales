<?php
/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    Kayako Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-Kayako Singapore Pte. Ltd.h Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace Base\Library\KQL2;

use Base\Models\CustomField\SWIFT_CustomField;
use SWIFT_Exception;
use Base\Library\KQL2\SWIFT_KQL2Compiler;
use Base\Library\KQL2\SWIFT_KQL2Parser;
use SWIFT_Library;

/**
 * The KQL Base Class
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2 extends SWIFT_Library
{
    const TYPE_ALPHA = 0x01; // A-Z, a-z, _
    const TYPE_NUMBER = 0x02; // 0-9
    const TYPE_SPACE = 0x04; // \n, \t, \r, ...
    const TYPE_ASTERISK = 0x08; // *
    const TYPE_SIGN = 0x10; // -, +
    const TYPE_DOT = 0x20; // .
    const TYPE_OTHER = 0x40; // anything else
    const TYPE_SPEC = 0x80; // ,, (, ), :, $, <, =, ...
    const TYPE_ERROR = 0xFF;

    const TYPE_OPERATOR = 0x03; // TYPE_ALPHA & TYPE_NUMBER
    const TYPE_FIELDNAME = 0x2B; // TYPE_ALPHA & TYPE_NUMBER & TYPE_DOT & TYPE_ASTERISK
    const TYPE_SIGNREALNUM = 0x32; // TYPE_SIGN & TYPE_DOT & TYPE_NUMBER

    const ELEMENT_EXPRESSION = 0;
    const ELEMENT_FIELD = 1;
    const ELEMENT_CUSTOMFIELD = 2;
    const ELEMENT_FUNCTION = 3;
    const ELEMENT_SELECTOR = 4;
    const ELEMENT_VALUE = 5;
    const ELEMENT_ARRAY = 6;
    const ELEMENT_VARIABLE = 7;

    const EXPRESSION_TYPE = 0;
    const EXPRESSION_DATA = 1;
    const EXPRESSION_RETURNTYPE = 2;
    const EXPRESSION_NEGATIVE = 3;
    const EXPRESSION_EXTRA = 4;

    const DATA_BOOLEAN = 1;
    const DATA_INTEGER = 2;
    const DATA_FLOAT = 3;
    const DATA_SECONDS = 4; // KQL-only type
    const DATA_INTERVAL = 5; // Expression + unit
    const DATA_TIME = 6;
    const DATA_DATE = 7;
    const DATA_UNIXDATE = 8; // KQL-only type
    const DATA_DATETIME = 9;
    const DATA_STRING = 10;

    const DATA_NUMERIC = 11; // General type => DATA_INTEGER, DATA_FLOAT

    const DATA_OPTION = 12;
    const DATA_SAME = 13;
    const DATA_ARRAY = 14;
    const DATA_ANY = 15;

    const TABLE_NAME = 0; // Real name if alias is used
    const TABLE_JOIN = 1; // LEFT JOIN ON expression
    const TABLE_RELATED = 2;

    /**
     * Stores Information About MySQL Operators Precedence
     */
    static protected $_operatorsPrecedence = array( // Used for precedence calculation only
        '!', '~', '^', '*', '/', 'DIV', '%', 'MOD', '-', '+', '<<', '>>', '&', '|', '=', '<=>', '>=', '>', '<=',
        '<', '<>', '!=', 'IS', 'LIKE', 'NOT LIKE', 'REGEXP', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', '&&', 'AND', 'XOR', '||', 'OR'
    );

    static protected $_kqlCustomFieldTypeMap = array(
        SWIFT_CustomField::TYPE_CHECKBOX => self::DATA_OPTION,
        SWIFT_CustomField::TYPE_SELECTMULTIPLE => self::DATA_OPTION,
        SWIFT_CustomField::TYPE_RADIO => self::DATA_OPTION,
        SWIFT_CustomField::TYPE_SELECT => self::DATA_OPTION,
        SWIFT_CustomField::TYPE_SELECTLINKED => self::DATA_OPTION,
        SWIFT_CustomField::TYPE_DATE => self::DATA_DATE
    );

    static protected $_kqlElementTypeStringMap = array(
        self::ELEMENT_FIELD => 'field expression',
        self::ELEMENT_CUSTOMFIELD => 'custom field expression',
        self::ELEMENT_FUNCTION => 'function expression',
        self::ELEMENT_VALUE => 'value expression',
        self::ELEMENT_ARRAY => 'array of expression'
    );

    static protected $_kqlDataTypeStringMap = array(
        self::DATA_ANY => 'any',
        self::DATA_NUMERIC => 'number',
        self::DATA_INTEGER => 'integer',
        self::DATA_FLOAT => 'float',
        self::DATA_INTERVAL => 'interval',
        self::DATA_SECONDS => 'seconds',
        self::DATA_BOOLEAN => 'boolean',
        self::DATA_STRING => 'string',
        self::DATA_DATE => 'date',
        self::DATA_TIME => 'time',
        self::DATA_UNIXDATE => 'unix timestamp',
        self::DATA_DATETIME => 'date and time'
    );

    /**
     * Arrays which get copied from the parser
     */
    protected $_schemaContainer = array();
    protected $_customFieldContainer = array();
    protected $_clausesContainer = array();
    protected $_operatorsContainer = array();
    protected $_functionsContainer = array();
    protected $_selectorsContainer = array();
    protected $_preModifiersContainer = array();
    protected $_postModifiersContainer = array();
    protected $_identifiersContainer = array();
    protected $_variablesContainer = array();

    protected $_kqlStructure = false;
    protected $_tableList = array();

    protected $_axisXCount = false;

    /**
     * @var SWIFT_KQL2Compiler
     */
    public $Compiler;

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     * @param mixed $_kql
     * @param array $_tableList
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_kql, $_tableList = array())
    {
        parent::__construct();

        if (is_string($_kql)) {
            $_kqlParser = new SWIFT_KQL2Parser();
            $_kqlObject = $_kqlParser->Parse($_kql);
            $_kql = $_kqlObject->GetArray();
        }

        $this->_kqlStructure = $_kql;
        $this->_tableList = $_tableList;
    }

    /**
     * Set KQL Schema Container
     *
     * @author Andriy Lesyuk
     * @param array $_schemaContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetSchema($_schemaContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_schemaContainer = $_schemaContainer;

        return true;
    }

    /**
     * Get KQL Schema Container
     *
     * @author Andriy Lesyuk
     * @return array The KQL Schema
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSchema()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_schemaContainer;
    }

    /**
     * Set Custom Fields Container
     *
     * @author Andriy Lesyuk
     * @param array $_customFieldContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetCustomFields($_customFieldContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_customFieldContainer = $_customFieldContainer;

        return true;
    }

    /**
     * Get Custom Fields Container
     *
     * @author Andriy Lesyuk
     * @return array The Custom Fields
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCustomFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_customFieldContainer;
    }

    /**
     * Set KQL Clauses Container
     *
     * @author Andriy Lesyuk
     * @param array $_clausesContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetClauses($_clausesContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_clausesContainer = $_clausesContainer;

        return true;
    }

    /**
     * Get KQL Clauses Container
     *
     * @author Andriy Lesyuk
     * @return array The Clauses Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetClauses()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_clausesContainer;
    }

    /**
     * Set KQL Operators Container
     *
     * @author Andriy Lesyuk
     * @param array $_operatorsContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetOperators($_operatorsContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_operatorsContainer = $_operatorsContainer;

        return true;
    }

    /**
     * Get KQL Operators Container
     *
     * @author Andriy Lesyuk
     * @return array The Operators Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOperators()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_operatorsContainer;
    }

    /**
     * Set KQL Functions Container
     *
     * @author Andriy Lesyuk
     * @param array $_functionsContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetFunctions($_functionsContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_functionsContainer = $_functionsContainer;

        return true;
    }

    /**
     * Get KQL Functions Container
     *
     * @author Andriy Lesyuk
     * @return array The Functions Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFunctions()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_functionsContainer;
    }

    /**
     * Set KQL Selectors Container
     *
     * @author Andriy Lesyuk
     * @param array $_selectorsContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetSelectors($_selectorsContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_selectorsContainer = $_selectorsContainer;

        return true;
    }

    /**
     * Get KQL Selectors Container
     *
     * @author Andriy Lesyuk
     * @return array The Selectors Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSelectors()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_selectorsContainer;
    }

    /**
     * Set KQL Pre-Modifiers Container
     *
     * @author Andriy Lesyuk
     * @param array $_preModifiersContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetPreModifiers($_preModifiersContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_preModifiersContainer = $_preModifiersContainer;

        return true;
    }

    /**
     * Get KQL Pre-Modifiers Container
     *
     * @author Andriy Lesyuk
     * @return array The Pre-Modifiers Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPreModifiers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_preModifiersContainer;
    }

    /**
     * Set KQL Post-Modifiers Container
     *
     * @author Andriy Lesyuk
     * @param array $_postModifiersContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetPostModifiers($_postModifiersContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_postModifiersContainer = $_postModifiersContainer;

        return true;
    }

    /**
     * Get KQL Post-Modifiers Container
     *
     * @author Andriy Lesyuk
     * @return array The Post-Modifiers Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPostModifiers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_postModifiersContainer;
    }

    /**
     * Set KQL Identifiers Container
     *
     * @author Andriy Lesyuk
     * @param array $_identifiersContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetIdentifiers($_identifiersContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_identifiersContainer = $_identifiersContainer;

        return true;
    }

    /**
     * Get KQL Identifiers Container
     *
     * @author Andriy Lesyuk
     * @return array The Identifiers Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetIdentifiers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_identifiersContainer;
    }

    /**
     * Set KQL Variables Container
     *
     * @author Andriy Lesyuk
     * @param array $_variablesContainer
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetVariables($_variablesContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_variablesContainer = $_variablesContainer;

        return true;
    }

    /**
     * Get KQL Variables Container
     *
     * @author Andriy Lesyuk
     * @return array The Variables Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetVariables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_variablesContainer;
    }

    /**
     * Get KQL as Array (Just Returns Internal Array)
     *
     * @author Andriy Lesyuk
     * @return array The KQL as Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetArray()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_kqlStructure;
    }

    /**
     * Get List of Used Tables
     *
     * @author Andriy Lesyuk
     * @return array The List of Tables
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTableList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_tableList;
    }

    /**
     * Get Expressions for the Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return array|bool The Expressions Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetClause($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_kqlStructure[$_clauseName])) {
            return $this->_kqlStructure[$_clauseName];
        }

        return false;
    }

    /**
     * Get Number of Total Rows
     *
     * @author Andriy Lesyuk
     * @return int The Number of Total Rows
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTotalRowCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_kqlStructure['TOTALIZE BY'])) {
            return count($this->_kqlStructure['TOTALIZE BY']);
        }

        return 0;
    }

    /**
     * Check if KQL is of Tabular Type
     *
     * @author Andriy Lesyuk
     * @return bool True or False
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsTabular()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure['GROUP BY']) &&
            !isset($this->_kqlStructure['MULTIGROUP BY'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if KQL is of Summary Type (uses GROUP BY)
     *
     * @author Andriy Lesyuk
     * @return bool True or False
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsSummary()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_kqlStructure['GROUP BY']) && !$this->IsMatrix()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if KQL is of Grouped Tabular Type (uses MULTIGROUP)
     *
     * @author Andriy Lesyuk
     * @return bool True or False
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsGroupedTabular()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_kqlStructure['MULTIGROUP BY'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if KQL is of Matrix Type (uses X/Y)
     *
     * @author Andriy Lesyuk
     * @return bool True or False
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsMatrix()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_axisXCount == false) {
            $this->_axisXCount = 0;

            if (isset($this->_kqlStructure['GROUP BY'])) {
                foreach ($this->_kqlStructure['GROUP BY'] as $_expression) {
                    if (isset($_expression[self::EXPRESSION_EXTRA]) &&
                        isset($_expression[self::EXPRESSION_EXTRA]['X'])) {
                        $this->_axisXCount = 1;

                        break;
                    }
                }
            }
        }

        return ($this->_axisXCount > 0);
    }

    /**
     * Returns String Representation of Element Type
     *
     * @author Andriy Lesyuk
     * @param int $_type
     * @return string The String Representation
     */
    public static function GetElementTypeString($_type)
    {
        if (isset(self::$_kqlElementTypeStringMap[$_type])) {
            return self::$_kqlElementTypeStringMap[$_type];
        } else {
            return 'expression';
        }
    }

    /**
     * Returns String Representation of Data Type
     *
     * @author Andriy Lesyuk
     * @param int $_type
     * @return string|bool
     */
    public static function GetDataTypeString($_type)
    {
        if (isset(self::$_kqlDataTypeStringMap[$_type])) {
            return self::$_kqlDataTypeStringMap[$_type];
        } elseif ($_type > 0) {
            return sprintf("unknown type 0x%04X", $_type);
        } else {
            return false;
        }
    }

    /**
     * Returns KQL Type for Custom Field Type
     *
     * @author Andriy Lesyuk
     * @param int $_type
     * @return int The KQL Type
     */
    public static function ConvertCustomFieldToKQLType($_type)
    {
        if (isset(self::$_kqlCustomFieldTypeMap[$_type])) {
            return self::$_kqlCustomFieldTypeMap[$_type];
        } else {
            return self::DATA_STRING;
        }
    }

    /**
     * Splits Expression into Groups by Precedence
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return array|bool
     */
    public static function GetPrecedenceGroups($_expression)
    {
        if ($_expression[self::EXPRESSION_TYPE] == self::ELEMENT_EXPRESSION) {

            $_operators = array();
            for ($_i = 0; $_i < count($_expression[self::EXPRESSION_DATA]); $_i++) {
                if (is_string($_expression[self::EXPRESSION_DATA][$_i])) {
                    if (!isset($_operators[$_expression[self::EXPRESSION_DATA][$_i]])) {
                        $_operators[$_expression[self::EXPRESSION_DATA][$_i]] = array();
                    }
                    $_operators[$_expression[self::EXPRESSION_DATA][$_i]][] = $_i;
                }
            }

            $_groups = array();
            $_groupsCache = array();
            foreach (self::$_operatorsPrecedence as $_operator) {
                if (isset($_operators[$_operator])) {
                    foreach ($_operators[$_operator] as $_position) {
                        $_leftOperand = false;
                        if (($_position > 0) && is_array($_expression[self::EXPRESSION_DATA][$_position - 1])) {
                            $_leftOperand = $_position - 1;
                        }
                        if (isset($_groupsCache[$_leftOperand])) {
                            $_leftOperand = array($_groupsCache[$_leftOperand], $_leftOperand);
                        }

                        $_rightOperand = false;
                        if ((($_position + 1) < count($_expression[self::EXPRESSION_DATA])) && is_array($_expression[self::EXPRESSION_DATA][$_position + 1])) {
                            $_rightOperand = $_position + 1;
                        }
                        if (isset($_groupsCache[$_rightOperand])) {
                            $_rightOperand = array($_rightOperand, $_groupsCache[$_rightOperand]);
                        }

                        $_groups[] = array($_leftOperand, $_position, $_rightOperand);

                        if (is_array($_leftOperand)) {
                            $_groupsCache[$_leftOperand[0]] = (is_array($_rightOperand)) ? $_rightOperand[1] : $_rightOperand;
                        } elseif (is_array($_rightOperand)) {
                            $_groupsCache[$_leftOperand] = (isset($_groupsCache[$_rightOperand[1]]) && ($_groupsCache[$_rightOperand[1]] > $_rightOperand[1])) ? $_groupsCache[$_rightOperand[1]] : $_rightOperand[1];
                        } else {
                            $_groupsCache[$_leftOperand] = (isset($_groupsCache[$_rightOperand]) && ($_groupsCache[$_rightOperand] > $_rightOperand)) ? $_groupsCache[$_rightOperand] : $_rightOperand;
                        }
                        if (is_array($_rightOperand)) {
                            $_groupsCache[$_rightOperand[1]] = (is_array($_leftOperand)) ? $_leftOperand[0] : $_leftOperand;
                        } elseif (is_array($_leftOperand)) {
                            $_groupsCache[$_rightOperand] = (isset($_groupsCache[$_leftOperand[0]]) && ($_groupsCache[$_leftOperand[0]] < $_leftOperand[0])) ? $_groupsCache[$_leftOperand[0]] : $_leftOperand[0];
                        } else {
                            $_groupsCache[$_rightOperand] = (isset($_groupsCache[$_leftOperand]) && ($_groupsCache[$_leftOperand] < $_leftOperand)) ? $_groupsCache[$_leftOperand] : $_leftOperand;
                        }
                    }
                }
            }

            return $_groups;
        }

        return false;
    }

    /**
     * Sort Precedence Groups
     * NOTE: Used for the cases when custom function can replace elements
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return array
     */
    public static function SortPrecedenceGroups($_expression)
    {
        usort($_expression, array('Base\Library\KQL2\SWIFT_KQL2', '_SortPrecedenceGroups'));

        return $_expression;
    }

    /**
     * Comparison Function for Sorting Precedence Groups
     *
     * @author Andriy Lesyuk
     * @return int
     */
    public static function _SortPrecedenceGroups($_aGroup, $_bGroup)
    {
        $_aStart = is_array($_aGroup[0]) ? $_aGroup[0][0] : $_aGroup[0];
        $_bStart = is_array($_bGroup[0]) ? $_bGroup[0][0] : $_bGroup[0];

        $_aEnd = is_array($_aGroup[2]) ? $_aGroup[2][1] : $_aGroup[2];
        $_bEnd = is_array($_bGroup[2]) ? $_bGroup[2][1] : $_bGroup[2];

        if (($_bStart >= $_aStart) && ($_bEnd <= $_aEnd)) {
            return 1;
        } elseif (($_aStart >= $_bStart) && ($_aEnd <= $_bEnd)) {
            return -1;
        } else {
            return $_aStart - $_bStart;
        }
    }

    /**
     * Returns True if This Is an Abstract Type
     *
     * @author Andriy Lesyuk
     * @param int $_kqlType
     * @return bool True if Abstract Type
     */
    public static function IsAbstractType($_kqlType)
    {
        switch ($_kqlType) {
            case self::DATA_NUMERIC:
                return true;
                break;
        }

        return false;
    }

    /**
     * Returns Non-Negative Operator
     *
     * @author Andriy Lesyuk
     * @param string $_operator
     * @return string The Operator
     */
    public static function GetCleanOperator($_operator)
    {
        return preg_replace(array('/^!/', '/^NOT /i'), '', $_operator);
    }

    /**
     * The Goal of the Function is to Choose the Type with the Minimal Data Loss and Closest Meaning
     *
     * @author Andriy Lesyuk
     * @param int $_fromType
     * @param array $_toTypes
     * @return mixed The Best Type
     */
    public static function GetBestCastToType($_fromType, $_toTypes)
    {
        if (!_is_array($_toTypes)) {
            // Expand abstract types
            switch ($_toTypes) {
                case self::DATA_NUMERIC:
                    $_toTypes = array(
                        self::DATA_FLOAT,
                        self::DATA_INTEGER,
                        self::DATA_SECONDS,
                    );
                    break;

                default:
                    return $_toTypes;
            }
        }

        if (in_array($_fromType, $_toTypes)) {
            return $_fromType;
        }

        $_toTypeMap = array();

        switch ($_fromType) {
            case self::DATA_BOOLEAN:
                $_toTypeMap = array(
                    self::DATA_INTEGER,
                    self::DATA_FLOAT,
                    self::DATA_STRING
                );
                break;

            case self::DATA_INTEGER:
                $_toTypeMap = array(
                    self::DATA_FLOAT,
                    self::DATA_SECONDS,
                    self::DATA_INTERVAL,
                    self::DATA_TIME,
                    self::DATA_UNIXDATE,
                    self::DATA_DATETIME,
                    self::DATA_DATE,
                    self::DATA_BOOLEAN,
                    self::DATA_STRING
                );
                break;

            case self::DATA_FLOAT:
                $_toTypeMap = array(
                    self::DATA_INTERVAL,
                    self::DATA_TIME,
                    self::DATA_DATETIME,
                    self::DATA_STRING,
                    self::DATA_INTEGER,
                    self::DATA_SECONDS,
                    self::DATA_UNIXDATE,
                    self::DATA_DATE
                );
                break;

            case self::DATA_SECONDS:
                $_toTypeMap = array(
                    self::DATA_INTERVAL,
                    self::DATA_TIME,
                    self::DATA_UNIXDATE,
                    self::DATA_DATETIME,
                    self::DATA_DATE,
                    self::DATA_INTEGER,
                    self::DATA_FLOAT,
                    self::DATA_STRING
                );
                break;

            case self::DATA_INTERVAL:
                $_toTypeMap = array(
                    self::DATA_INTEGER,
                    self::DATA_FLOAT,
                    self::DATA_STRING,
                    self::DATA_SECONDS,
                    self::DATA_TIME,
                    self::DATA_UNIXDATE,
                    self::DATA_DATETIME,
                    self::DATA_DATE
                );
                break;

            case self::DATA_TIME:
                $_toTypeMap = array(
                    self::DATA_FLOAT,
                    self::DATA_SECONDS,
                    self::DATA_INTERVAL,
                    self::DATA_INTEGER,
                    self::DATA_STRING,
                    self::DATA_UNIXDATE,
                    self::DATA_DATETIME,
                    self::DATA_DATE
                );
                break;

            case self::DATA_DATE:
                $_toTypeMap = array(
                    self::DATA_DATETIME,
                    self::DATA_UNIXDATE,
                    self::DATA_SECONDS,
                    self::DATA_INTEGER,
                    self::DATA_FLOAT,
                    self::DATA_STRING
                );
                break;

            case self::DATA_UNIXDATE:
                $_toTypeMap = array(
                    self::DATA_DATETIME,
                    self::DATA_SECONDS,
                    self::DATA_INTEGER,
                    self::DATA_FLOAT,
                    self::DATA_STRING,
                    self::DATA_DATE,
                    self::DATA_TIME
                );
                break;

            case self::DATA_DATETIME:
                $_toTypeMap = array(
                    self::DATA_UNIXDATE,
                    self::DATA_SECONDS,
                    self::DATA_INTEGER,
                    self::DATA_FLOAT,
                    self::DATA_STRING,
                    self::DATA_DATE,
                    self::DATA_TIME
                );
                break;

            case self::DATA_NUMERIC:
                $_toTypeMap = array(
                    self::DATA_FLOAT,
                    self::DATA_INTEGER,
                    self::DATA_SECONDS,
                    self::DATA_INTERVAL,
                    self::DATA_UNIXDATE,
                    self::DATA_DATETIME,
                    self::DATA_STRING,
                    self::DATA_DATE,
                    self::DATA_TIME
                );
                break;

            default: // DATA_STRING
                break;
        }

        $_bestTypeIndex = false;

        foreach ($_toTypes as $_toType) {
            $_typeIndex = array_search($_toType, $_toTypeMap);
            if ($_typeIndex !== false) {
                if (($_bestTypeIndex === false) || ($_typeIndex < $_bestTypeIndex)) {
                    $_bestTypeIndex = $_typeIndex;
                }
            }
        }

        if ($_bestTypeIndex !== false) {
            return isset($_toTypeMap[$_bestTypeIndex])?$_toTypeMap[$_bestTypeIndex]:0;
        } else {
            return reset($_toTypeMap);
        }
    }

    /**
     * Ensure that simple types pass abstract ones
     *
     * @author Andriy Lesyuk
     * @param int $_fromType
     * @param int $_toType
     * @return int The Returned Type
     */
    public static function SimplifyDataType($_fromType, $_toType) # FIXME: use
    {
        switch ($_toType) {
            case self::DATA_NUMERIC:
                if (($_fromType == self::DATA_INTEGER) ||
                    ($_fromType == self::DATA_FLOAT) ||
                    ($_fromType == self::DATA_SECONDS)) {
                    return $_fromType;
                }
                break;
        }

        return $_toType;
    }

    /**
     * Collects Functions Used in the Expression
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return array The Functions
     */
    public static function GetUsedFunctionsFromExpression($_expression)
    {
        $_usedFunctions = array();

        switch ($_expression[self::EXPRESSION_TYPE]) {
            case self::ELEMENT_EXPRESSION:
                foreach ($_expression[self::EXPRESSION_DATA] as $_innerExpression) {
                    if (is_array($_innerExpression)) {
                        $_usedFunctions = array_merge($_usedFunctions, self::GetUsedFunctionsFromExpression($_innerExpression));
                    }
                }
                break;

            case self::ELEMENT_FUNCTION:
                $_usedFunctions[] = $_expression[self::EXPRESSION_DATA][0];
                foreach ($_expression[self::EXPRESSION_DATA][1] as $_argumentExpression) {
                    $_usedFunctions = array_merge($_usedFunctions, self::GetUsedFunctionsFromExpression($_argumentExpression));
                }
                break;

            case self::ELEMENT_ARRAY:
                foreach ($_expression[self::EXPRESSION_DATA] as $_innerExpression) {
                    $_usedFunctions = array_merge($_usedFunctions, self::GetUsedFunctionsFromExpression($_innerExpression));
                }
                break;

            default:
                break;
        }

        return array_unique($_usedFunctions);
    }

}

?>
