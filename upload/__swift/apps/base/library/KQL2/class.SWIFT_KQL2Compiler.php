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

use Base\Library\KQL\SWIFT_KQL;
use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\KQL2\SWIFT_KQL2;
use Base\Library\KQL2\SWIFT_KQL2_Exception;
use Base\Models\CustomField\SWIFT_CustomField;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\KQL2\SWIFT_KQL2Parser;
use SWIFT_Library;
use SWIFT_ReportBase;

/**
 * The KQL Compiler
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2Compiler extends SWIFT_Library
{
    /**
     * State variables
     */
    protected $_currentClause = false;
    protected $_nestingLevels = array();
    protected $_distinctSQL = false;
    protected $_sequenceIndex = false;

    /**
     * Saved KQL
     */
    protected $_kqlStructure = false;
    protected $_tableList = array();

    /**
     * Distinct expressions
     */
    protected $_distinctExpressions = array();

    /**
     * Sequent expressions
     */
    protected $_sequentExpressions = array();

    /**
     * Copy of GROUP BY expressions
     */
    protected $_groupByExpressions = array();

    /**
     * Additional conditions
     */
    protected $_additionalConditions = array();

    /**
     * Compiled SQL Chunks
     */
    protected $_sqlExpressions = array();

    /**
     * Fields used in ORDER BY clause
     */
    protected $_sortingFields = array();

    /**
     * The hidden fields
     */
    protected $_hiddenFields = array();

    /**
     * Arrays which get copied from KQL object
     */
    protected $_schemaContainer = array();
    protected $_customFieldContainer = array();
    protected $_clausesContainer = array();
    protected $_operatorsContainer = array();
    protected $_functionsContainer = array();
    protected $_preModifiersContainer = array();
    protected $_postModifiersContainer = array();
    protected $_identifiersContainer = array();
    protected $_variablesContainer = array();

    /**
     * Variables & Column Names
     */
    protected $_dynamicVariables = array();
    protected $_inlineVariables = array();
    protected $_columnNames = array();
    protected $_sequentColumnNames = array();

    /**
     * Compiled Expressions to Aliases Map
     */
    protected $_expressionsToAliasesMap = array();

    /**
     * Compatibility data
     */
    protected $_compat_ReturnGroupByFields = array();
    protected $_compat_returnMultiGroupByFields = array();

    /**
     * MySQL clauses
     */
    static protected $_mysqlClauses = array('SELECT', 'FROM', 'WHERE', 'GROUP BY', 'ORDER BY', 'LIMIT');
    static protected $_distinctClauses = array('SELECT', 'FROM', 'WHERE', 'ORDER BY');
    static protected $_unionClauses = array('SELECT', 'FROM', 'WHERE');

    /**
     * KQL1 Compatibility Array (Remove When Ready)
     */
    static $_kql1ExtendedFunctions = array(
        'MINUTE',
        'HOUR',
        'DAY',
        'HOUR',
        'DAY',
        'DAYNAME',
        'WEEK',
        'WEEKDAY',
        'MONTH',
        'MONTHNAME',
        'QUARTER',
        'YEAR'
    );

    /**
     * MySQL server time zone (used for adjusting dates)
     */
    static protected $_mysqlTimeZone = false;

    /**
     * @var SWIFT_KQL2
     */
    protected $KQL;

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Compiles KQL into SQL String
     *
     * @author Andriy Lesyuk
     * @param string|SWIFT_KQL2 $_kql
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compile($_kql)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (is_string($_kql)) {
            $_kqlParser = new SWIFT_KQL2Parser();
            $_kql = $_kqlParser->Parse($_kql);
        }

        $this->_distinctSQL = false;
        $this->_sequenceIndex = false;
        $this->_distinctExpressions = array();
        $this->_sequentExpressions = array();
        $this->_additionalConditions = array();
        $this->_sqlExpressions = array();
        $this->_sortingFields = array();

        $this->_schemaContainer = $_kql->GetSchema();
        $this->_customFieldContainer = $_kql->GetCustomFields();
        $this->_clausesContainer = $_kql->GetClauses();
        $this->_operatorsContainer = $_kql->GetOperators();
        $this->_functionsContainer = $_kql->GetFunctions();
        $this->_preModifiersContainer = $_kql->GetPreModifiers();
        $this->_postModifiersContainer = $_kql->GetPostModifiers();
        $this->_identifiersContainer = $_kql->GetIdentifiers();
        $this->_variablesContainer = $_kql->GetVariables();

        $this->_kqlStructure = $_kql->GetArray();
        $this->_tableList = $_kql->GetTableList();

        $this->KQL = $_kql;

        // Set Compiler for the KQL object
        $_kql->Compiler = $this;

        $_primaryTableName = false;

        // Include autojoins
        foreach ($this->_tableList as $_tableName => $_tableContainer) {
            if (!is_bool($_tableContainer) || ($_tableContainer === false)) {
                continue;
            }

            if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_AUTOJOIN])) {
                foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_AUTOJOIN] as $_autoJoinTableName) {
                    if (!isset($this->_tableList[$_autoJoinTableName]) || is_bool($this->_tableList[$_autoJoinTableName])) {
                        $this->_tableList[$_autoJoinTableName] = array(SWIFT_KQL2::TABLE_RELATED => $_tableName);
                    }
                }
            }

            if ($_primaryTableName &&
                isset($this->_schemaContainer[$_primaryTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName]) &&
                _is_array($this->_schemaContainer[$_primaryTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                $this->_additionalConditions[] = $this->_schemaContainer[$_primaryTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName][1];
            }

            // Just the first table
            if ($_primaryTableName === false) {
                $_primaryTableName = $_tableName;
            }
        }

        foreach ($this->_clausesContainer as $_clauseName => $_clauseContainer) {
            if (is_string($_clauseContainer)) { // Skip aliases
                continue;
            }

            $this->_currentClause = $_clauseName;

            if (isset($_clauseContainer[SWIFT_KQLSchema::CLAUSE_PRECOMPILER])) {
                $methodName = $_clauseContainer[SWIFT_KQLSchema::CLAUSE_PRECOMPILER];
                $this->$methodName($_clauseName);
            }

            $this->ClearNestingLevels();

            if (isset($_clauseContainer[SWIFT_KQLSchema::CLAUSE_COMPILER])) {
                $methodName = $_clauseContainer[SWIFT_KQLSchema::CLAUSE_COMPILER];
                $this->$methodName($_clauseName); // TODO: Support array('Class', 'Method')
            } else {
                $this->CompileClause($_clauseName);
            }
        }

        return true;
    }

    /**
     * Generates Distinct SQL Queries
     * Used to fetch values for subsequent queries.
     * Fetched values should be passed to GetSQL() in $_variables.
     *
     * @author Andriy Lesyuk
     * @return mixed The SQL Query
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDistinctSQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->_sqlExpressions)) {
            throw new SWIFT_Exception('Use Compile() first');
        }

        if (count($this->_distinctExpressions) == 0) {
            return false;
        }

        $this->_distinctSQL = true;

        if ($this->KQL->IsMatrix()) {
            $_distinctSQLs = array();

            foreach ($this->_distinctExpressions as $_variableName => $_sqlExpression) {
                $_distinctSQLs[$_variableName] = $this->BuildDistinctSQL(array($_variableName => $_sqlExpression));
            }

            return $_distinctSQLs;
        } else {
            return $this->BuildDistinctSQL();
        }
    }

    /**
     * Generates Distinct SQL Query
     * This function is used for multiple queries
     *
     * @author Andriy Lesyuk
     * @param array|bool $_distinctExpressions
     * @return string|bool The SQL Query
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function BuildDistinctSQL($_distinctExpressions = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Use $this->_distinctExpressions if argument is missing
        if ($_distinctExpressions === false) {
            $_distinctExpressions = $this->_distinctExpressions;
        }

        if (count($_distinctExpressions) == 0) {
            return false;
        }

        $_sqlChunks = array();

        $this->_expressionsToAliasesMap = array();

        foreach (self::$_distinctClauses as $_clauseName) {
            switch ($_clauseName) {
                case 'SELECT':
                    $_distinctChunks = array();

                    foreach ($_distinctExpressions as $_variableName => $_sqlExpression) {
                        $_distinctChunks[] = $_sqlExpression . " AS '" . $this->Database->Escape($_variableName) . "'";

                        $this->_expressionsToAliasesMap[$_sqlExpression] = $_variableName;

                        // For backwards compatibility (has no sense as decrypting and unserializing can't be done in MySQL)
                        $_auxiliaryChunks = $this->AddAuxiliaryFields($_variableName);
                        foreach ($_auxiliaryChunks as $_auxiliaryName => $_auxiliaryExpression) {
                            $_distinctChunks[] = $_auxiliaryExpression . " AS '" . $this->Database->Escape($_auxiliaryName) . "'";
                        }
                    }

                    $_sqlChunks[$_clauseName] = 'DISTINCT ' . implode(', ', $_distinctChunks);
                    break;

                case 'FROM':
                    $_sqlChunks[$_clauseName] = $this->CompileTableList();
                    break;

                case 'WHERE':
                    $_whereConditions = false;

                    if (isset($this->_sqlExpressions[$_clauseName])) {
                        $_whereConditions = $this->_sqlExpressions[$_clauseName];
                    }

                    foreach ($this->_additionalConditions as $_additionalCondition) {
                        if ($_whereConditions) {
                            $_whereConditions .= ' AND ' . $_additionalCondition;
                        } else {
                            $_whereConditions = $_additionalCondition;
                        }
                    }

                    if ($_whereConditions) {
                        $_sqlChunks[$_clauseName] = $_whereConditions;
                    }
                    break;

                case 'ORDER BY':
                    $_orderChunks = array();

                    if (isset($this->_sqlExpressions[$_clauseName])) {
                        foreach ($this->_sqlExpressions[$_clauseName] as $_orderExpression) {
                            list($_expression, $_mode) = $this->SplitOrderByMode($_orderExpression);
                            if (isset($this->_expressionsToAliasesMap[$_expression])) {
                                $_orderChunks[] = "`" . $this->Database->Escape($this->_expressionsToAliasesMap[$_expression]) . "`" . ($_mode ? ' ' . $_mode : '');
                            } else {
                                $_orderChunks[] = $_orderExpression;
                            }
                        }
                    }

                    foreach ($_distinctExpressions as $_variableName => $_sqlExpression) {
                        $_usedFields = $this->GetUsedFieldsFromExpression($this->_dynamicVariables[$_variableName]);

                        $_unsorted = true;
                        foreach ($_usedFields as $_usedField) {
                            if (in_array($_usedField, $this->_sortingFields)) {
                                $_unsorted = false;
                                break;
                            }
                        }

                        if ($_unsorted) {
                            $this->FixOrderByExpression($_sqlExpression, $this->_dynamicVariables[$_variableName]);

                            list($_expression, $_mode) = $this->SplitOrderByMode($_sqlExpression);
                            if (isset($this->_expressionsToAliasesMap[$_expression])) {
                                $_orderChunks[] = "`" . $this->Database->Escape($this->_expressionsToAliasesMap[$_expression]) . "`" . ($_mode ? ' ' . $_mode : '');
                            } else {
                                $_orderChunks[] = $_sqlExpression;
                            }
                        }
                    }

                    if (!empty($_orderChunks)) {
                        $_sqlChunks[$_clauseName] = implode(', ', $_orderChunks);
                    }
                    break;

                default:
                    if (isset($this->_sqlExpressions[$_clauseName])) {
                        if (is_array($this->_sqlExpressions[$_clauseName])) {
                            $_sqlChunks[$_clauseName] = implode(', ', $this->_sqlExpressions[$_clauseName]);
                        } else {
                            $_sqlChunks[$_clauseName] = $this->_sqlExpressions[$_clauseName];
                        }
                    }
                    break;
            }
        }

        $this->RunSchemaPostCompilers($_sqlChunks);

        $_sqlQuery = '';
        foreach (self::$_distinctClauses as $_clauseName) {
            if (isset($_sqlChunks[$_clauseName])) {
                if ($_sqlQuery != '') {
                    $_sqlQuery .= ' ';
                }
                $_sqlQuery .= $_clauseName . ' ' . $_sqlChunks[$_clauseName];
            }
        }

        return $_sqlQuery;
    }

    /**
     * Generates SQL Query
     *
     * @author Andriy Lesyuk
     * @param array $_variables
     * @return string The SQL Query
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSQL($_variables = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->_sqlExpressions)) {
            throw new SWIFT_Exception('Use Compile() first');
        }

        if (!empty($this->_distinctExpressions) && !$this->_distinctSQL) {
            throw new SWIFT_Exception('Use GetDistinctSQL() first');
        }

        $_sqlChunks = array();

        $this->_expressionsToAliasesMap = array();

        $this->_sequenceIndex = false;

        foreach (self::$_mysqlClauses as $_clauseName) {
            switch ($_clauseName) {
                case 'SELECT':
                    $_modifier = '';
                    $_selectChunks = array();

                    foreach ($this->_sqlExpressions[$_clauseName] as $_variableName => $_sqlExpression) {
                        if (is_string($_variableName)) {
                            $_selectChunks[] = $_sqlExpression . " AS '" . $this->Database->Escape($_variableName) . "'";

                            $this->_expressionsToAliasesMap[$_sqlExpression] = $_variableName;

                            $_auxiliaryChunks = $this->AddAuxiliaryFields($_variableName); // TODO: Same for SequentSQL
                            foreach ($_auxiliaryChunks as $_auxiliaryName => $_auxiliaryExpression) {
                                $_selectChunks[] = $_auxiliaryExpression . " AS '" . $this->Database->Escape($_auxiliaryName) . "'";
                            }
                        } else {
                            $_selectChunks[] = $_sqlExpression;
                        }
                    }

                    // TODO: Remove if no bugs
//                    if ($this->KQL->IsMatrix()) {
//                        foreach ($this->_groupByExpressions as $_variableName => $_sqlExpression) {
//                            $_selectChunks[] = $_sqlExpression . " AS '" .  $this->Database->Escape($_variableName) . "'";

//                            $this->_expressionsToAliasesMap[$_sqlExpression] = $_variableName;

//                            $_auxiliaryChunks = $this->AddAuxiliaryFields($_variableName);
//                            foreach ($_auxiliaryChunks as $_auxiliaryName => $_auxiliaryExpression) {
//                                $_selectChunks[] = $_auxiliaryExpression . " AS '" .  $this->Database->Escape($_auxiliaryName) . "'";
//                            }
//                        }
//                    }

                    foreach ($this->_distinctExpressions as $_variableName => $_sqlExpression) {
                        $_selectChunks[] = $_sqlExpression . " AS '" . $this->Database->Escape($_variableName) . "'";

                        $this->_expressionsToAliasesMap[$_sqlExpression] = $_variableName;

                        $_auxiliaryChunks = $this->AddAuxiliaryFields($_variableName);
                        foreach ($_auxiliaryChunks as $_auxiliaryName => $_auxiliaryExpression) {
                            $_selectChunks[] = $_auxiliaryExpression . " AS '" . $this->Database->Escape($_auxiliaryName) . "'";
                        }
                    }

                    foreach ($this->_kqlStructure[$_clauseName] as $_expression) {
                        if (isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
                            isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['DISTINCT'])) {
                            $_modifier = 'DISTINCT ';
                            break;
                        }
                    }

                    $_sqlChunks[$_clauseName] = $_modifier . implode(', ', $_selectChunks);
                    break;

                case 'FROM':
                    $_sqlChunks[$_clauseName] = $this->CompileTableList();
                    break;

                case 'WHERE':
                    $_whereConditions = $this->CompileConditionalExpression($_variables);
                    if ($_whereConditions) {
                        $_sqlChunks[$_clauseName] = $_whereConditions;
                    }
                    break;

                case 'GROUP BY':
                    if (isset($this->_sqlExpressions[$_clauseName])) {
                        $_groupByChunks = array();

                        foreach ($this->_sqlExpressions[$_clauseName] as $_groupByExpression) {
                            if (isset($this->_expressionsToAliasesMap[$_groupByExpression])) {
                                $_groupByChunks[] = "`" . $this->Database->Escape($this->_expressionsToAliasesMap[$_groupByExpression]) . "`";
                            } else {
                                $_groupByChunks[] = $_groupByExpression;
                            }
                        }

                        $_sqlChunks[$_clauseName] = implode(', ', $_groupByChunks);
                    }
                    break;

                case 'ORDER BY':
                    $_orderChunks = array();

                    // If unsorted results will break the layout of summary table
                    if ($this->KQL->IsSummary() && !empty($this->_groupByExpressions)) {
                        $_groupByVariables = array_keys($this->_groupByExpressions);

                        for ($_i = 0; $_i < (count($_groupByVariables) - 1); $_i++) {
                            $_orderExpression = $this->_groupByExpressions[$_groupByVariables[$_i]];

                            $this->FixOrderByExpression($_orderExpression, $this->_dynamicVariables[$_groupByVariables[$_i]]);

                            list($_expression, $_mode) = $this->SplitOrderByMode($_orderExpression);
                            if (isset($this->_expressionsToAliasesMap[$_expression])) {
                                $_orderChunks[] = "`" . $this->Database->Escape($this->_expressionsToAliasesMap[$_expression]) . "`" . ($_mode ? ' ' . $_mode : '');
                            } else {
                                $_orderChunks[] = $_orderExpression;
                            }
                        }
                    }

                    if (isset($this->_sqlExpressions[$_clauseName])) {
                        foreach ($this->_sqlExpressions[$_clauseName] as $_orderExpression) {
                            list($_expression, $_mode) = $this->SplitOrderByMode($_orderExpression);

                            if (isset($this->_expressionsToAliasesMap[$_expression])) {
                                $_orderChunks[] = "`" . $this->Database->Escape($this->_expressionsToAliasesMap[$_expression]) . "`" . ($_mode ? ' ' . $_mode : '');
                            } else {
                                $_orderChunks[] = $_orderExpression;
                            }
                        }
                    }

                    if (!empty($_orderChunks)) {
                        $_sqlChunks[$_clauseName] = implode(', ', $_orderChunks);
                    }
                    break;

                default:
                    if (isset($this->_sqlExpressions[$_clauseName])) {
                        if (is_array($this->_sqlExpressions[$_clauseName])) {
                            $_sqlChunks[$_clauseName] = implode(', ', $this->_sqlExpressions[$_clauseName]);
                        } else {
                            $_sqlChunks[$_clauseName] = $this->_sqlExpressions[$_clauseName];
                        }
                    }
                    break;
            }
        }

        $this->RunSchemaPostCompilers($_sqlChunks);

        $_sqlQuery = '';
        foreach (self::$_mysqlClauses as $_clauseName) {
            if (isset($_sqlChunks[$_clauseName])) {
                if ($_sqlQuery != '') {
                    $_sqlQuery .= ' ';
                }
                $_sqlQuery .= $_clauseName . ' ' . $_sqlChunks[$_clauseName];
            }
        }

        return $_sqlQuery;
    }

    /**
     * Generates Sequent SQL Queries
     *
     * @author Andriy Lesyuk
     * @param array $_variables
     * @return string|bool The SQL Query
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSequentSQL($_variables = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->_sqlExpressions)) {
            throw new SWIFT_Exception('Use Compile() first');
        }

        if ($this->_sequenceIndex === false) {
            $this->_sequenceIndex = 0;
        }

        if (!isset($this->_sequentExpressions[$this->_sequenceIndex])) {
            return false;
        }

        $_sqlChunks = array();

        // Sequent expressions were designed to be compatible with UNION
        foreach (self::$_unionClauses as $_clauseName) {
            switch ($_clauseName) {
                case 'SELECT':
                    $_selectChunks = $this->GetSequentSelectExpressions();

                    $_sqlChunks[$_clauseName] = implode(', ', $_selectChunks);
                    break;

                case 'FROM':
                    $_sqlChunks[$_clauseName] = $this->CompileTableList();
                    break;

                case 'WHERE':
                    $_whereConditions = $this->CompileConditionalExpression($_variables);
                    if ($_whereConditions) {
                        $_sqlChunks[$_clauseName] = $_whereConditions;
                    }
                    break;

                default:
                    if (isset($this->_sqlExpressions[$_clauseName])) {
                        if (is_array($this->_sqlExpressions[$_clauseName])) {
                            $_sqlChunks[$_clauseName] = implode(', ', $this->_sqlExpressions[$_clauseName]);
                        } else {
                            $_sqlChunks[$_clauseName] = $this->_sqlExpressions[$_clauseName];
                        }
                    }
                    break;
            }
        }

        $this->RunSchemaPostCompilers($_sqlChunks);

        $_sqlQuery = '';
        foreach (self::$_unionClauses as $_clauseName) {
            if (isset($_sqlChunks[$_clauseName])) {
                if ($_sqlQuery != '') {
                    $_sqlQuery .= ' ';
                }
                $_sqlQuery .= $_clauseName . ' ' . $_sqlChunks[$_clauseName];
            }
        }

        $this->_sequenceIndex++;

        return $_sqlQuery;
    }

    /**
     * Generates Array of SQL Expressions for SELECT
     *
     * @author Andriy Lesyuk
     * @return array|bool SQL Expressions
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetSequentSelectExpressions()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_sequentExpressions[$this->_sequenceIndex])) {
            return false;
        }

        $_sequentExpressionIndex = 0;
        $_sequentExpressions = $this->_sequentExpressions[$this->_sequenceIndex];

        $_selectChunks = array();

        // TODO: Check data types etc?

        foreach ($this->_sqlExpressions['SELECT'] as $_variableName => $_originalSQL) {
            if (!isset($this->_hiddenFields[$_variableName]) && isset($_sequentExpressions[$_sequentExpressionIndex])) {
                $_sqlExpression = $_sequentExpressions[$_sequentExpressionIndex];

                if (isset($this->_sequentColumnNames[$this->_sequenceIndex][$_sqlExpression])) {
                    if (is_string($_variableName)) {
                        $this->_sequentColumnNames[$this->_sequenceIndex][$_variableName] = $this->_sequentColumnNames[$this->_sequenceIndex][$_sqlExpression];
                    } else {
                        $this->_sequentColumnNames[$this->_sequenceIndex][$_originalSQL] = $this->_sequentColumnNames[$this->_sequenceIndex][$_sqlExpression];
                    }

                    unset($this->_sequentColumnNames[$this->_sequenceIndex][$_sqlExpression]);
                }
            } else {
                if (isset($this->_groupByExpressions[$_variableName])) {
                    $_sqlExpression = "'%grandtotalrowgroupbyexpression[" . $this->_sequenceIndex . "]%'";
                } else {
                    $_sqlExpression = 'NULL';
                }
            }

            if (is_string($_variableName)) {
                $_selectChunks[] = $_sqlExpression . " AS '" . $this->Database->Escape($_variableName) . "'";
            } else {
                $_selectChunks[] = $_sqlExpression . " AS '" . $this->Database->Escape($_originalSQL) . "'";
            }

            if (!isset($this->_hiddenFields[$_variableName])) {
                $_sequentExpressionIndex++;
            }
        }

        if ($this->KQL->IsMatrix()) {
            foreach ($this->_distinctExpressions as $_variableName => $_originalSQL) {
                $_selectChunks[] = "NULL AS '" . $this->Database->Escape($_variableName) . "'";

                // For backwards compatibility
                $_variableChunks = explode('_', $_variableName);
                if ((count($_variableChunks) >= 3) && ($_variableChunks[1] == 'cf')) { // FIXME
                    $_selectChunks[] = "NULL AS '" . $this->Database->Escape($_variableName) . "_isserialized'";
                    $_selectChunks[] = "NULL AS '" . $this->Database->Escape($_variableName) . "_isencrypted'";
                }
            }

            $_sequentExpressionIndex++;
        }

        return $_selectChunks;
    }

    /**
     * Compiles FROM Clause
     *
     * @author Andriy Lesyuk
     * @return string The FROM SQL Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CompileTableList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableChunks = array();

        $this->FixRelatedTables();

        $this->SortTableList();

        $_primaryTableName = false;

        foreach ($this->_tableList as $_tableName => $_tableContainer) {
            if ($_primaryTableName === false) {
                $_primaryTableName = $_tableName;
            }

            $_tableAlias = $_tableName;
            if ($_tableContainer && is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_NAME])) {
                $_tableName = $_tableContainer[SWIFT_KQL2::TABLE_NAME];
            }

            $_tableExpression = TABLE_PREFIX . $_tableName . ' AS ' . $_tableAlias;

            if (count($_tableChunks) > 0) {
                if ($_tableContainer && ((is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_JOIN])) || is_string($_tableContainer))) {

                    if (is_string($_tableContainer)) {
                        $_tableExpression .= ' ON ' . $_tableContainer;
                    } else {
                        $_tableExpression .= ' ON ' . $_tableContainer[SWIFT_KQL2::TABLE_JOIN];
                    }

                } else {
                    $_relatedTable = $_primaryTableName;
                    if ($_tableContainer && is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_RELATED])) {
                        $_relatedTable = $_tableContainer[SWIFT_KQL2::TABLE_RELATED];
                    }

                    if (!isset($this->_schemaContainer[$_relatedTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                        throw new SWIFT_Exception($_relatedTable . ' is not related to ' . $_tableName);
                    }

                    if (_is_array($this->_schemaContainer[$_relatedTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                        $_tableExpression .= ' ON ' . $this->_schemaContainer[$_relatedTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName][0];
                    } else {
                        $_tableExpression .= ' ON ' . $this->_schemaContainer[$_relatedTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName];
                    }
                }
            }

            $_tableChunks[] = $_tableExpression;
        }

        return implode(' LEFT JOIN ', $_tableChunks);
    }

    /**
     * Compiles Expression for WHERE Clause
     *
     * @author Andriy Lesyuk
     * @param array $_variables
     * @return string The WHERE SQL Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function CompileConditionalExpression($_variables = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_whereExpression = false;

        if (isset($this->_sqlExpressions['WHERE'])) {
            $_whereExpression = $this->_sqlExpressions['WHERE'];
        }

        foreach ($this->_distinctExpressions as $_variableName => $_sqlExpression) {
            if (array_key_exists($_variableName, $_variables)) {
                $_variableValue = $this->CompileValue($_variables[$_variableName]);
            } else {
                throw new SWIFT_Exception('Missing value for variable $' . $_variableName);
            }

            if ($_variableValue == 'NULL') {
                $_operator = 'IS';
            } else {
                $_operator = '=';
            }

            if ($_whereExpression) {
                $_whereExpression .= ' AND ' . $_sqlExpression . ' ' . $_operator . ' ' . $_variableValue;
            } else {
                $_whereExpression = $_sqlExpression . ' ' . $_operator . ' ' . $_variableValue;
            }
        }

        foreach ($this->_additionalConditions as $_additionalCondition) {
            if ($_whereExpression) {
                $_whereExpression .= ' AND ' . $_additionalCondition;
            } else {
                $_whereExpression = $_additionalCondition;
            }
        }

        return $_whereExpression;
    }

    /**
     * Executes Schema Post-Compilers
     *
     * @author Andriy Lesyuk
     * @param array $_sqlChunks
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunSchemaPostCompilers(&$_sqlChunks)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach ($this->_tableList as $_tableName => $_tableContainer) {
            $_tableAlias = $_tableName;
            if ($_tableContainer && is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_NAME])) {
                $_tableName = $_tableContainer[SWIFT_KQL2::TABLE_NAME];
            }

            if (isset($this->_schemaContainer[$_tableName])) {
                $_schemaContainer = $this->_schemaContainer[$_tableName];

                if (isset($_schemaContainer[SWIFT_KQLSchema::SCHEMA_POSTCOMPILER])) {
                    $methodName = $_schemaContainer[SWIFT_KQLSchema::SCHEMA_POSTCOMPILER];
                    $this->$methodName($_tableAlias, $_sqlChunks);
                }
            }
        }

        return true;
    }

    /**
     * Fix Position of Tables in the List
     *
     * @author Andriy Lesyuk
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function FixRelatedTables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (count($this->_tableList) == 0) {
            return false;
        }

        $_primaryTableName = false;
        $_tableListNames = array_keys($this->_tableList);

        foreach ($_tableListNames as $_tableIndex => $_tableName) {
            if ($_primaryTableName === false) {
                $_primaryTableName = $_tableName;
            }

            $_tableID = $_tableName;
            $_tableContainer = $this->_tableList[$_tableName];

            // Take table name from container
            if ($_tableContainer && is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_NAME])) {
                $_tableName = $_tableContainer[SWIFT_KQL2::TABLE_NAME];
            }

            // Container has join command
            if ($_tableContainer && ((is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_JOIN])) || is_string($_tableContainer))) {
                continue;
            }

            // Related table already set
            if ($_tableContainer && is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_RELATED])) {
                continue;
            }

            // First try finding the table among explicitly specified tables
            foreach (array_merge(array_reverse(array_slice($_tableListNames, 0, $_tableIndex)),
                array_slice($_tableListNames, $_tableIndex + 1)) as $_relatedTable) {
                if (($this->_tableList[$_relatedTable] === true) &&
                    (isset($this->_schemaContainer[$_relatedTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName]))) {
                    if (!is_array($this->_tableList[$_tableID])) {
                        $this->_tableList[$_tableID] = array();
                    }

                    $this->_tableList[$_tableID][SWIFT_KQL2::TABLE_RELATED] = $_relatedTable;
                    break;
                }
            }

            if (is_array($this->_tableList[$_tableID]) && isset($this->_tableList[$_tableID][SWIFT_KQL2::TABLE_RELATED])) {
                continue;
            }

            // Now check all other tables
            foreach ($_tableListNames as $_relatedTable) {
                if (($this->_tableList[$_relatedTable] === true) || ($_relatedTable == $_tableName)) {
                    continue;
                }

                if (isset($this->_schemaContainer[$_relatedTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                    if (!is_array($this->_tableList[$_tableID])) {
                        $this->_tableList[$_tableID] = array();
                    }

                    $this->_tableList[$_tableID][SWIFT_KQL2::TABLE_RELATED] = $_relatedTable;
                    break;
                }
            }
        }

        return true;
    }

    /**
     * Sort Table List to Avoid Errors
     * This function first splits hash into key and value arrays and then uses array_splice to move elements
     *
     * @author Andriy Lesyuk
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function SortTableList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (count($this->_tableList) == 0) {
            return false;
        }

        $_tableListNames = array_keys($this->_tableList);
        $_tableListContainers = array_values($this->_tableList);

        // Put custom fields at the end
        $_customFieldsAfter = false;
        for ($_i = count($_tableListNames) - 1; $_i >= 0; $_i--) {
            if (substr($_tableListNames[$_i], 0, strlen('customfield')) == 'customfield') {
                if ($_customFieldsAfter) {
                    array_splice($_tableListNames, $_customFieldsAfter, 0, $_tableListNames[$_i]); // Add name to the end
                    array_splice($_tableListNames, $_i, 1); // Remove name
                    array_splice($_tableListContainers, $_customFieldsAfter, 0, array($_tableListContainers[$_i])); // Add value to the end
                    array_splice($_tableListContainers, $_i, 1); // Remove value
                    $_customFieldsAfter--; // Decrease index
                }
            } elseif ($_customFieldsAfter === false) {
                $_customFieldsAfter = $_i + 1;
            }
        }

        $_tableListIndexes = array_flip($_tableListNames);

        // Put joined tables after related tables
        for ($_i = count($_tableListNames) - 1; $_i >= 0; $_i--) {
            if (isset($_tableListContainers[$_i][SWIFT_KQL2::TABLE_RELATED])) {
                $_relatedTable = $_tableListContainers[$_i][SWIFT_KQL2::TABLE_RELATED];

                // Avoid recursion
                if (isset($this->_tableList[$_relatedTable]) &&
                    isset($this->_tableList[$_relatedTable][SWIFT_KQL2::TABLE_RELATED]) &&
                    ($this->_tableList[$_relatedTable][SWIFT_KQL2::TABLE_RELATED] == $_tableListNames[$_i])
                ) {
                    continue;
                }

                if (isset($_tableListIndexes[$_relatedTable]) && ($_tableListIndexes[$_relatedTable] > $_i)) {
                    array_splice($_tableListNames, $_tableListIndexes[$_relatedTable] + 1, 0, $_tableListNames[$_i]); // Add name after the related table
                    array_splice($_tableListNames, $_i, 1); // Remove name
                    array_splice($_tableListContainers, $_tableListIndexes[$_relatedTable] + 1, 0, array($_tableListContainers[$_i])); // Add value to the end
                    array_splice($_tableListContainers, $_i, 1); // Remove value
                }
            }
        }

        $this->_tableList = array_combine($_tableListNames, $_tableListContainers);

        return true;
    }

    /**
     * Prepares SELECT statement for compilation
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_PreCompileSelect($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_expandExpressions = array();

        foreach ($this->_kqlStructure[$_clauseName] as $_expressionIndex => $_expressionData) {

            // Save user-supplied aliases to internal array
            if (isset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
                isset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'])) {
                $_aliasName = $_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'];

                if ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] !== SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                    unset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]['AS']);
                }

                $this->_inlineVariables[mb_strtolower($_aliasName)] = $_expressionData;

                // Generate aliases if missing
            } else {
                if (($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) ||
                    (($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
                        in_array($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0], self::$_kql1ExtendedFunctions) &&
                        (count($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1]) == 1) &&
                        ($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD))) {
                    if (!isset($this->_kqlStructure[$_clauseName][$_expressionIndex][SWIFT_KQL2::EXPRESSION_EXTRA])) {
                        $this->_kqlStructure[$_clauseName][$_expressionIndex][SWIFT_KQL2::EXPRESSION_EXTRA] = array();
                    }

                    $_variableName = $this->GenerateVariableNameForExpression($_expressionData, false, false);
                    if ($_variableName) {
                        $this->_kqlStructure[$_clauseName][$_expressionIndex][SWIFT_KQL2::EXPRESSION_EXTRA]['AS'] = $_variableName;
                    }
                }
            }

            // Save custom fields
            if (($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) &&
                ($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1] == '*')) {
                $_expandExpressions[] = $_expressionIndex;
            } elseif ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                $_expandExpressions[] = $_expressionIndex;
            }
        }

        foreach (array_reverse($_expandExpressions) as $_expressionIndex) {
            $_replaceExpressions = array();

            // Expand field wildcards
            if ($this->_kqlStructure[$_clauseName][$_expressionIndex][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
                $_tableName = $this->_kqlStructure[$_clauseName][$_expressionIndex][SWIFT_KQL2::EXPRESSION_DATA][0];

                // table.*
                if ($_tableName) {
                    foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_fieldName => $_fieldContainer) {
                        $_replaceExpressions[] = array(
                            SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FIELD,
                            SWIFT_KQL2::EXPRESSION_DATA => array($_tableName, $_fieldName)
                        );

                        $_fieldType = $this->GetFieldType($_tableName, $_fieldName);
                        if ($_fieldType) {
                            $_fieldExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_fieldType;
                        }
                    }

                    // *
                } else {
                    foreach ($this->_tableList as $_tableName => $_tableContainer) {
                        if ($_tableContainer && is_array($_tableContainer) && isset($_tableContainer[SWIFT_KQL2::TABLE_NAME])) {
                            $_tableName = $_tableContainer[SWIFT_KQL2::TABLE_NAME];
                        }

                        if (isset($this->_schemaContainer[$_tableName])) {
                            foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_fieldName => $_fieldContainer) {
                                $_replaceExpressions[] = array(
                                    SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_FIELD,
                                    SWIFT_KQL2::EXPRESSION_DATA => array($_tableName, $_fieldName)
                                );

                                $_fieldType = $this->GetFieldType($_tableName, $_fieldName);
                                if ($_fieldType) {
                                    $_fieldExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] = $_fieldType;
                                }
                            }
                        }
                    }
                }

                // Expand custom fields
            } else {
                $_customFieldTable = $this->_kqlStructure[$_clauseName][$_expressionIndex][SWIFT_KQL2::EXPRESSION_DATA][0];
                $_customFieldData = $this->_kqlStructure[$_clauseName][$_expressionIndex][SWIFT_KQL2::EXPRESSION_DATA][2];

                if (!is_array($_customFieldData)) {
                    $_customFieldData = array($_customFieldData);
                }

                foreach ($_customFieldData as $_customFieldID) {
                    $_expressionData = $this->_kqlStructure[$_clauseName][$_expressionIndex];
                    $_aliasName = $_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'] ?? '';
                    $_customFieldAliasBase = (isset($_aliasName) && !empty($_aliasName))? $_aliasName : '_cf_' . $_customFieldID;
                    $_customFieldProperties = $this->_customFieldContainer[$_customFieldID];

                    $_replaceExpressions[] = array(
                        SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_CUSTOMFIELD,
                        SWIFT_KQL2::EXPRESSION_DATA => array($_customFieldTable, $_customFieldProperties['group_id'], $_customFieldID),
                        SWIFT_KQL2::EXPRESSION_RETURNTYPE => SWIFT_KQL2::ConvertCustomFieldToKQLType($_customFieldProperties['type']),
                        SWIFT_KQL2::EXPRESSION_EXTRA => array('AS' => $_customFieldAliasBase)
                    );
                }
            }

            array_splice($this->_kqlStructure[$_clauseName], $_expressionIndex, 1, $_replaceExpressions);
        }

        return true;
    }

    /**
     * Compiles SELECT clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_CompileSelect($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        if (!isset($this->_sqlExpressions[$_clauseName])) {
            $this->_sqlExpressions[$_clauseName] = array();
        }

        foreach ($this->_kqlStructure[$_clauseName] as $_expression) {
            $this->ClearNestingLevels();

            $_sqlExpression = $this->CompileExpression($_expression);

            // $_sqlExpression = IF((tickets.ticketid = 75), customfield23.fieldvalue, 0)

            $_aliasName = false;
            if (isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
                isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'])) {
                $_aliasName = $_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'];

                unset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS']);
            }

            $this->CompilePostModifiers($_expression, $_sqlExpression, $_clauseName);

            if (!empty($_sqlExpression)) {
                // FIXME: Remove
                // Currently formatter supports only unix time
//                if (isset($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) &&
//                   (($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_DATE) ||
//                    ($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_DATETIME))) {
//                    $_sqlExpression = $this->Cast($_sqlExpression, $_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE], SWIFT_KQL2::DATA_UNIXDATE);
//                }

                if ($_aliasName) {
                    $this->_sqlExpressions[$_clauseName][$_aliasName] = $_sqlExpression;
                    $this->_columnNames[$_aliasName] = ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] === SWIFT_KQL2::ELEMENT_CUSTOMFIELD && isset($_aliasName) && !empty($_aliasName)) ? $_aliasName : $_expression;
                } else {
                    $this->_sqlExpressions[$_clauseName][] = $_sqlExpression;

                    $this->_columnNames[$_sqlExpression] = $_expression;
                }
            }
        }

        return true;
    }

    /**
     * Compiles WHERE Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_CompileWhere($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        $_sqlExpression = $this->CompileExpression($this->_kqlStructure[$_clauseName], false, false, SWIFT_KQL2::DATA_BOOLEAN);

        $this->CompilePostModifiers($this->_kqlStructure[$_clauseName], $_sqlExpression, $_clauseName);

        if (!empty($_sqlExpression)) {
            $this->_sqlExpressions[$_clauseName] = $_sqlExpression;
        }

        return true;
    }

    /**
     * Compiles GROUP BY Clause
     * NOTE: Used only to copy compiled expressions for later use
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_CompileGroupBy($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        if (!isset($this->_sqlExpressions[$_clauseName])) {
            $this->_sqlExpressions[$_clauseName] = array();
        }

        foreach ($this->_kqlStructure[$_clauseName] as $_expression) {
            $this->ClearNestingLevels();

            $_sqlExpression = $this->CompileExpression($_expression);

            $this->CompilePostModifiers($_expression, $_sqlExpression, $_clauseName);

            if (!empty($_sqlExpression)) {
                $_variableName = $this->GenerateVariableNameForExpression($_expression, true);

                $this->_groupByExpressions[$_variableName] = $_sqlExpression;

                $this->_sqlExpressions[$_clauseName][] = $_sqlExpression;

                if ($this->KQL->IsSummary() || $this->KQL->IsMatrix()) {
                    $this->_sqlExpressions['SELECT'][$_variableName] = $_sqlExpression;
                }
            }
        }

        return true;
    }

    /**
     * Compiles MULTIGROUP BY Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_CompileMultiGroupBy($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        foreach ($this->_kqlStructure[$_clauseName] as $_expression) {
            $this->ClearNestingLevels();

            $_sqlExpression = $this->CompileExpression($_expression);

            $this->CompilePostModifiers($_expression, $_sqlExpression, $_clauseName);

            if (!empty($_sqlExpression)) {
                $_variableName = $this->GenerateVariableNameForExpression($_expression, true);

                $this->_compat_returnMultiGroupByFields[$_variableName] = $_sqlExpression;

                $this->AddDistinctExpression($_variableName, $_sqlExpression, true);
            }
        }

        return true;
    }

    /**
     * Compiles LIMIT Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_CompileLimit($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        if (!is_array($this->_kqlStructure[$_clauseName]) || (count($this->_kqlStructure[$_clauseName]) != 2)) {
            return false;
        }

        $this->_sqlExpressions[$_clauseName] = $this->_kqlStructure[$_clauseName][0] . ', ' . $this->_kqlStructure[$_clauseName][1];

        return true;
    }

    /**
     * Compiles ORDER BY Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_CompileOrderBy($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        if (!isset($this->_sqlExpressions[$_clauseName])) {
            $this->_sqlExpressions[$_clauseName] = array();
        }

        $_sqlGroupByExpression = [];
        if (isset($this->_kqlStructure['GROUP BY']) && is_array($this->_kqlStructure['GROUP BY'])) {
            foreach ($this->_kqlStructure['GROUP BY'] as $groupByExpression) {
                $_sqlGroupByExpression[] = $this->CompileExpression($groupByExpression);
            }
        }

        foreach ($this->_kqlStructure[$_clauseName] as $_expression) {
            $this->ClearNestingLevels();
            $_sqlExpression = $this->CompileExpression($_expression);

            if (!in_array($_sqlExpression, $_sqlGroupByExpression)) {
                $this->FixOrderByExpression($_sqlExpression, $_expression);
            }

            $this->CompilePostModifiers($_expression, $_sqlExpression, $_clauseName);

            if (!empty($_sqlExpression)) {
                $this->_sqlExpressions[$_clauseName][] = $_sqlExpression;
            }
        }

        return true;
    }

    /**
     * Compiles TOTALIZE BY Clause
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Clause_CompileTotalizeBy($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        foreach ($this->_kqlStructure[$_clauseName] as $_sequentStructure) {
            $_sequentTitle = false;
            $_sequentExpressions = array();
            $_sequentColumnNames = array();

            foreach ($_sequentStructure as $_expression) {
                if (is_string($_expression)) {
                    $_sequentTitle = $this->CompileValue($_expression);
                    continue;
                }

                $this->ClearNestingLevels();

                $_sqlExpression = $this->CompileExpression($_expression);

                $this->CompilePostModifiers($_expression, $_sqlExpression, $_clauseName);

                if (!empty($_sqlExpression)) {
                    $_sequentExpressions[] = $_sqlExpression;
                    $_sequentColumnNames[$_sqlExpression] = $_expression;
                }
            }

            if (count($_sequentExpressions) > 0) {
                if ($_sequentTitle && !$this->KQL->IsSummary() && !$this->KQL->IsMatrix()) {
                    array_unshift($_sequentExpressions, $_sequentTitle);
                }

                $this->_sequentExpressions[] = $_sequentExpressions;
                $this->_sequentColumnNames[] = $_sequentColumnNames;
            }
        }

        return true;
    }

    /**
     * Corrects SQL Statement to Fix Sorting Issues
     *
     * @author Andriy Lesyuk
     * @param string $_sqlExpression
     * @param array $_expression
     * @return bool True if Fixed or False Otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function FixOrderByExpression(&$_sqlExpression, $_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) {
            switch ($_expression[SWIFT_KQL2::EXPRESSION_DATA][0]) {
                case 'DAYNAME':
                    $_dayNames = array();

                    foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $_dayName) {
                        $_dayNames[] = $this->CompileValue($this->Language->Get($_dayName));
                    }

                    $_sqlExpression = 'FIELD(' . $_sqlExpression . ', ' . implode(', ', $_dayNames) . ')';
                    return true;
                    break;

                case 'MONTHNAME':
                    $_monthNames = array();

                    foreach (array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december') as $_monthName) {
                        $_monthNames[] = $this->CompileValue($this->Language->Get('cal_' . $_monthName));
                    }

                    $_sqlExpression = 'FIELD(' . $_sqlExpression . ', ' . implode(', ', $_monthNames) . ')';
                    return true;
                    break;

                default:
                    break;
            }
        }

        // TODO: Fields with options

        return false;
    }

    /**
     * Compiles Clauses
     *
     * @author Andriy Lesyuk
     * @param string $_clauseName
     * @return bool True if Compilation was Successful
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CompileClause($_clauseName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure[$_clauseName])) {
            return false;
        }

        if (!isset($this->_sqlExpressions[$_clauseName])) {
            $this->_sqlExpressions[$_clauseName] = array();
        }

        foreach ($this->_kqlStructure[$_clauseName] as $_expression) {
            $this->ClearNestingLevels();

            $_sqlExpression = $this->CompileExpression($_expression);

            $this->CompilePostModifiers($_expression, $_sqlExpression, $_clauseName);

            if (!empty($_sqlExpression)) {
                $this->_sqlExpressions[$_clauseName][] = $_sqlExpression;
            }
        }

        return true;
    }

    /**
     * Applies Fixes for Tickets
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param array $_sqlChunks
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Schema_PostCompileTickets($_tableName, &$_sqlChunks)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_StaffObject = SWIFT_ReportBase::GetStaff();

        if (!empty($_SWIFT_StaffObject) && ($_SWIFT_StaffObject->GetPermission('staff_rrestrict') == '1')) {
            $_assignedDepartmentList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_TICKETS);

            $_secureCondition = $_tableName . '.departmentid IN (' . BuildIN($_assignedDepartmentList) . ')';

            if (isset($_sqlChunks['WHERE'])) {
                $_sqlChunks['WHERE'] = '(' . $_sqlChunks['WHERE'] . ') AND ' . $_secureCondition;
            } else {
                $_sqlChunks['WHERE'] = $_secureCondition;
            }
        }

        return true;
    }

    /**
     * Applies Fixes for Calls and Messages
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param array $_sqlChunks
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Schema_PostCompileLiveChat($_tableName, &$_sqlChunks)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_StaffObject = SWIFT_ReportBase::GetStaff();

        if (!empty($_SWIFT_StaffObject) && ($_SWIFT_StaffObject->GetPermission('staff_rrestrict') == '1')) {
            $_assignedDepartmentList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_LIVECHAT);

            $_secureCondition = $_tableName . '.departmentid IN (' . BuildIN($_assignedDepartmentList) . ')';

            if (isset($_sqlChunks['WHERE'])) {
                $_sqlChunks['WHERE'] = '(' . $_sqlChunks['WHERE'] . ') AND ' . $_secureCondition;
            } else {
                $_sqlChunks['WHERE'] = $_secureCondition;
            }
        }

        return true;
    }

    /**
     * Applies Fixes for Chat Objects
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @param array $_sqlChunks
     * @return bool True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Schema_PostCompileChatObjects($_tableName, &$_sqlChunks)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_StaffObject = SWIFT_ReportBase::GetStaff();

        if (!empty($_SWIFT_StaffObject) && ($_SWIFT_StaffObject->GetPermission('staff_rrestrict') == '1')) {
            $_assignedDepartmentList = $_SWIFT_StaffObject->GetAssignedDepartments(APP_LIVECHAT);

            // Offline messages do not have chat objects (and departments), so also checking for NULL
            $_secureCondition = '(' . $_tableName . '.departmentid IN (' . BuildIN($_assignedDepartmentList) . ') OR ' . $_tableName . '.departmentid IS NULL)';

            if (isset($_sqlChunks['WHERE'])) {
                $_sqlChunks['WHERE'] = '(' . $_sqlChunks['WHERE'] . ') AND ' . $_secureCondition;
            } else {
                $_sqlChunks['WHERE'] = $_secureCondition;
            }
        }

        return true;
    }

    /**
     * Processes Pre-Modifiers
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string $_modifierName
     * @param string $_modifierValue
     * @param bool $_parentheses
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CompilePreModifier($_expression, $_modifierName, $_modifierValue, $_parentheses = true, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_modifierProperties = $this->_preModifiersContainer[$_modifierName];

        if (isset($_modifierProperties[SWIFT_KQLSchema::PREMODIFIER_COMPILER])) {
            $var = $_modifierProperties[SWIFT_KQLSchema::PREMODIFIER_COMPILER];
            return $this->$var($_expression, $_modifierName, $_modifierValue, $_parentheses, $_operator, $_operand);
        } else {
            $_returnString = $_modifierName . ' ';

            $_sqlExpression = $this->CompileExpression($_expression, true);

            $_returnString .= $_sqlExpression;
            if ($_modifierValue) {
                $_returnString .= ' ' . $this->CompileValue($_modifierValue);
            }

            return $_returnString;
        }
    }

    /**
     * Processes Post-Modifiers
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string $_sqlExpression
     * @param string|false $_clause
     * @return bool Whether Post-Modifier Was Processed Successfully
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CompilePostModifiers($_expression, &$_sqlExpression, $_clause = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        static $_mysqlPostModifiers = array(
            'AS',
            'ASC',
            'DESC'
        );

        if (!$_clause) {
            $_clause = $this->CurrentClause();
        }

        if (isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
            foreach ($_expression[SWIFT_KQL2::EXPRESSION_EXTRA] as $_modifierName => $_modifierValue) {
                if (isset($this->_postModifiersContainer[$_modifierName])) {
                    $_modifierProperties = $this->_postModifiersContainer[$_modifierName];

                    if (isset($_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_CLAUSE]) && ($_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_CLAUSE] == $_clause)) {
                        if (isset($_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_COMPILER])) {
                            $var = $_modifierProperties[SWIFT_KQLSchema::POSTMODIFIER_COMPILER];
                            return $this->$var($_sqlExpression, $_modifierName, $_modifierValue, $_expression);
                        } elseif (in_array($_modifierName, $_mysqlPostModifiers)) {
                            $_sqlExpression .= ' ' . $_modifierName;
                            if ($_modifierValue) {
                                $_sqlExpression .= ' ' . $this->CompileValue($_modifierValue);
                            }

                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Just prevents DISTINCT to appear inside the expression list
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string $_modifierName
     * @param mixed $_modifierValue
     * @param bool $_parentheses
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_CompileDistinct($_expression, $_modifierName, $_modifierValue, $_parentheses = true, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sqlExpression = $this->CompileExpression($_expression, true);

        if ($this->GetNestingLevelsCount() > 0) {
            return $_modifierName . ' ' . $_sqlExpression;
        } else {
            return $_sqlExpression;
        }
    }

    /**
     * Processes Intervals
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string $_modifierName
     * @param mixed $_modifierValue
     * @param bool $_parentheses
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_CompileInterval($_expression, $_modifierName, $_modifierValue, $_parentheses = true, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sqlExpression = $this->CompileExpression($_expression, true);

        $_returnString = $_modifierName . ' ' . $_sqlExpression;

        if ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) {
            if (is_float($_expression[SWIFT_KQL2::EXPRESSION_DATA])) {
                $_returnString .= ' SECOND_MICROSECOND';
            } elseif (is_int($_expression[SWIFT_KQL2::EXPRESSION_DATA])) {
                $_returnString .= ' SECOND';
            } else {
                if ($_modifierValue && is_string($_modifierValue)) {
                    $_returnString .= ' ' . $_modifierValue;
                } else {
                    $_returnString .= ' SECOND';
                }
            }
        } else {
            if ($_modifierValue && is_string($_modifierValue)) {
                $_returnString .= ' ' . $_modifierValue;
            } else {
                $_returnString .= ' SECOND';
            }
        }

        return $_returnString;
    }

    /**
     * Processes X/Y Modifiers
     *
     * @author Andriy Lesyuk
     * @param string $_sqlExpression
     * @param string $_modifierName
     * @param mixed $_modifierValue
     * @param array $_expression
     * @return bool Whether Post-Modifier Was Processed Successfully
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Modifier_CompileXY(&$_sqlExpression, $_modifierName, $_modifierValue, $_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_modifierName == 'X') {
            $_variableName = $this->GenerateVariableNameForExpression($_expression, true);

            $this->_compat_ReturnGroupByFields[$_variableName] = $_sqlExpression;

            $this->AddDistinctExpression($_variableName, $_sqlExpression, true);

            $_sqlExpression = false;
        } // Ignore Y

        return true;
    }

    /**
     * Compiles Expression
     * Entry point for all expressions
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param bool $_parentheses
     * @param string|false $_operator
     * @param mixed $_operand
     * @return string|bool The SQL Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CompileExpression($_expression, $_parentheses = true, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA])) {
            foreach ($_expression[SWIFT_KQL2::EXPRESSION_EXTRA] as $_modifierName => $_modifierValue) {
                if (isset($this->_preModifiersContainer[$_modifierName])) {
                    unset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA][$_modifierName]);

                    return $this->CompilePreModifier($_expression, $_modifierName, $_modifierValue, $_parentheses, $_operator, $_operand);
                }
            }
        }

        $_prefix = '';
        if (isset($_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]) && $_expression[SWIFT_KQL2::EXPRESSION_NEGATIVE]) {
            $_prefix = 'NOT ';
        }

        switch ($_expression[SWIFT_KQL2::EXPRESSION_TYPE]) {
            case SWIFT_KQL2::ELEMENT_EXPRESSION:
                $_innerStrings = array();

                /**
                 * Expand
                 */

                $_precedenceGroups = SWIFT_KQL2::SortPrecedenceGroups(SWIFT_KQL2::GetPrecedenceGroups($_expression));

                foreach (array_reverse($_precedenceGroups) as $_precedenceGroup) {
                    $_firstOperand = $_precedenceGroup[0];
                    $_operatorIndex = $_precedenceGroup[1];
                    $_secondOperand = $_precedenceGroup[2];

                    $_replaceExpression = false;

                    // Expand arrays
                    if (is_int($_firstOperand) && is_array($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand]) &&
                        is_int($_secondOperand) && is_array($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand]) &&
                        (($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_ARRAY) ||
                            ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_ARRAY))) {
                        $_replaceExpression = $this->ExpandArray($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                    }

                    // Call function expander for first operand
                    if ($_replaceExpression &&
                        is_int($_firstOperand) && is_array($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand]) &&
                        ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION)) {
                        $_functionName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][0];
                        $_functionProperties = $this->_functionsContainer[$_functionName];

                        if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_EXPANDER])) {
                            $var = $_functionProperties[SWIFT_KQLSchema::FUNCTION_EXPANDER];
                            $_replaceExpression = $this->$var($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                        }
                    }

                    // Call function expander for second operand
                    if (is_bool($_replaceExpression) &&
                        is_int($_secondOperand) && is_array($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand]) &&
                        ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION)) {
                        $_functionName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][0];
                        $_functionProperties = $this->_functionsContainer[$_functionName];

                        if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_EXPANDER])) {
                            $var1 = $_functionProperties[SWIFT_KQLSchema::FUNCTION_EXPANDER];
                            $_replaceExpression = $this->$var1($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                        }
                    }

                    // Call operator expander
                    if (is_bool($_replaceExpression)) {
                        $_operatorIdentifier = SWIFT_KQL2::GetCleanOperator($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_operatorIndex]);

                        if (isset($this->_operatorsContainer[$_operatorIdentifier])) {
                            $_operatorProperties = $this->_operatorsContainer[$_operatorIdentifier];

                            if (isset($_operatorProperties[SWIFT_KQLSchema::OPERATOR_EXPANDER])) { // FIXME: arrat(Class, ...)
                                $var2 = $_operatorProperties[SWIFT_KQLSchema::OPERATOR_EXPANDER];
                                $_replaceExpression = $this->$var2($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                            }
                        }
                    }

                    if (is_array($_replaceExpression)) {
                        $_groupStart = is_array($_firstOperand) ? $_firstOperand[0] : $_firstOperand;
                        $_groupEnd = is_array($_secondOperand) ? $_secondOperand[1] : $_secondOperand;

                        // Create inner expression
                        if (!is_array($_replaceExpression)) {
                            $_replaceExpression = array($_replaceExpression);
                        }
                        if ((!isset($_replaceExpression[SWIFT_KQL2::EXPRESSION_TYPE]) ||
                                ($_replaceExpression[SWIFT_KQL2::EXPRESSION_TYPE] != SWIFT_KQL2::ELEMENT_EXPRESSION)) &&
                            // Not if the replacement is for the whole expression
                            (($_groupStart > 0) || ($_groupEnd < (count($_expression[SWIFT_KQL2::EXPRESSION_DATA]) - 1)))) {
                            $_replaceExpression = array(
                                SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_EXPRESSION,
                                SWIFT_KQL2::EXPRESSION_DATA => $_replaceExpression
                            );
                        }

                        array_splice($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_groupStart, ($_groupEnd - $_groupStart) + 1, $_replaceExpression);
                    }
                }

                /**
                 * Pre-Compile
                 */

                $_precedenceGroups = (array)SWIFT_KQL2::GetPrecedenceGroups($_expression);

                foreach ($_precedenceGroups as $_precedenceGroup) {
                    $_firstOperand = $_precedenceGroup[0];
                    $_operatorIndex = $_precedenceGroup[1];
                    $_secondOperand = $_precedenceGroup[2];

                    // Pre-compile the first operand if it's a function
                    if (is_int($_firstOperand) && is_array($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand])) {
                        if ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                            $this->PreCompileCustomField($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                        } elseif ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) {
                            $_functionName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][0];
                            $_functionProperties = $this->_functionsContainer[$_functionName];

                            if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_PRECOMPILER])) {
                                $var3 = $_functionProperties[SWIFT_KQLSchema::FUNCTION_PRECOMPILER];
                                $this->$var3($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                            }
                        }
                    }

                    // Pre-compile the second operand if it's a function
                    if (is_int($_secondOperand) && is_array($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand])) {
                        if ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                            $this->PreCompileCustomField($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                        } elseif ($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) {
                            $_functionName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][0];
                            $_functionProperties = $this->_functionsContainer[$_functionName];

                            if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_PRECOMPILER])) {
                                $var4 = $_functionProperties[SWIFT_KQLSchema::FUNCTION_PRECOMPILER];
                                $this->$var4($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                            }
                        }
                    }

                    // Execute operator compiler
                    $_operatorIdentifier = SWIFT_KQL2::GetCleanOperator($_expression[SWIFT_KQL2::EXPRESSION_DATA][$_operatorIndex]);

                    if (isset($this->_operatorsContainer[$_operatorIdentifier])) {
                        $_operatorProperties = $this->_operatorsContainer[$_operatorIdentifier];

                        if (isset($_operatorProperties[SWIFT_KQLSchema::OPERATOR_COMPILER])) {
                            $var5 = $_operatorProperties[SWIFT_KQLSchema::OPERATOR_COMPILER];
                            $this->$var5($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand, $_operatorIndex, $_secondOperand);
                        }
                    }
                }

                /**
                 * Compile
                 */

                $this->AddNestingLevel(SWIFT_KQL2::ELEMENT_EXPRESSION);

                $_expressionData = $_expression[SWIFT_KQL2::EXPRESSION_DATA];
                foreach ($_precedenceGroups as $_precedenceGroup) {
                    $_firstOperand = $_precedenceGroup[0];
                    $_operatorIndex = $_precedenceGroup[1];
                    $_secondOperand = $_precedenceGroup[2];

                    $_typeCasted = false;
                    $_operatorName = SWIFT_KQL2::GetCleanOperator($_expressionData[$_operatorIndex]);

                    // Compile the first operand
                    if (is_int($_firstOperand) && is_array($_expressionData[$_firstOperand])) {
                        $_operandExpression = false;
                        if (is_int($_secondOperand)) {
                            $_operandExpression = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_secondOperand];
                        } elseif (is_array($_secondOperand)) {
                            $_operandExpression = array(
                                SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_EXPRESSION,
                                SWIFT_KQL2::EXPRESSION_DATA => array_slice($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_secondOperand[0], $_secondOperand[1]),
                                # TODO: SWIFT_KQL2::EXPRESSION_RETURNTYPE =>
                            );
                        }
                        $_expressionString = $this->CompileExpression($_expressionData[$_firstOperand], true, $_expressionData[$_operatorIndex], $_operandExpression);

                        if (isset($this->_operatorsContainer[$_operatorName]) &&
                            isset($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                            $_operatorProperties = $this->_operatorsContainer[$_operatorName];
                            if (isset($_operatorProperties[SWIFT_KQLSchema::OPERATOR_LEFTTYPE]) &&
                                ($_operatorProperties[SWIFT_KQLSchema::OPERATOR_LEFTTYPE] != SWIFT_KQL2::DATA_ANY)) {
                                $_firstOperandType = $_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE];
                                if ($_operatorProperties[SWIFT_KQLSchema::OPERATOR_LEFTTYPE] == SWIFT_KQL2::DATA_SAME) {
                                    if ($_operandExpression && isset($_operandExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                                        $_expressionString = $this->Cast($_expressionString, $_firstOperandType, $_operandExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]);
                                        $_typeCasted = true;
                                    }
                                } else {
                                    $_expressionString = $this->Cast($_expressionString, $_firstOperandType, $_operatorProperties[SWIFT_KQLSchema::OPERATOR_LEFTTYPE]);
                                    $_typeCasted = true;
                                }
                            }
                        }

                        $_expressionData[$_firstOperand] = $_expressionString;
                    }

                    // Compile the second operand
                    if (is_int($_secondOperand) && is_array($_expressionData[$_secondOperand])) {
                        $_operandExpression = false;
                        if (is_int($_firstOperand)) {
                            $_operandExpression = $_expression[SWIFT_KQL2::EXPRESSION_DATA][$_firstOperand];
                        } elseif (is_array($_firstOperand)) {
                            $_operandExpression = array(
                                SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_EXPRESSION,
                                SWIFT_KQL2::EXPRESSION_DATA => array_slice($_expression[SWIFT_KQL2::EXPRESSION_DATA], $_firstOperand[0], $_firstOperand[1]),
                                # TODO: SWIFT_KQL2::EXPRESSION_RETURNTYPE =>
                            );
                        }
                        $_expressionString = $this->CompileExpression($_expressionData[$_secondOperand], true, $_expressionData[$_operatorIndex], $_operandExpression);

                        if (!$_typeCasted &&
                            isset($this->_operatorsContainer[$_operatorName]) &&
                            isset($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                            $_operatorProperties = $this->_operatorsContainer[SWIFT_KQL2::GetCleanOperator($_expressionData[$_operatorIndex])];
                            if (isset($_operatorProperties[SWIFT_KQLSchema::OPERATOR_RIGHTTYPE]) &&
                                ($_operatorProperties[SWIFT_KQLSchema::OPERATOR_RIGHTTYPE] != SWIFT_KQL2::DATA_ANY)) {
                                $_secondOperandType = $_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_RETURNTYPE];
                                if ($_operatorProperties[SWIFT_KQLSchema::OPERATOR_RIGHTTYPE] == SWIFT_KQL2::DATA_SAME) {
                                    if ($_operandExpression && isset($_operandExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                                        $_expressionString = $this->Cast($_expressionString, $_secondOperandType, $_operandExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]);
                                    }
                                } else {
                                    $_expressionString = $this->Cast($_expressionString, $_secondOperandType, $_operatorProperties[SWIFT_KQLSchema::OPERATOR_RIGHTTYPE]);
                                }
                            }
                        }

                        $_expressionData[$_secondOperand] = $_expressionString;
                    }
                }

                $this->RemoveNestingLevel();

                foreach ($_expressionData as $_innerExpression) {
                    if (is_string($_innerExpression)) {
                        $_innerStrings[] = $_innerExpression;
                    } else {
                        throw new SWIFT_KQL2_Exception('Compilation error: invalid expression');
                    }
                }

                if ($_parentheses) {
                    return $_prefix . '(' . implode(' ', $_innerStrings) . ')';
                } else {
                    return $_prefix . implode(' ', $_innerStrings);
                }
                break;

            case SWIFT_KQL2::ELEMENT_FIELD:
                return $_prefix . $this->CompileField($_expression);
                break;

            case SWIFT_KQL2::ELEMENT_CUSTOMFIELD:
                return $_prefix . $this->CompileCustomField($_expression);
                break;

            case SWIFT_KQL2::ELEMENT_FUNCTION:
                return $_prefix . $this->CompileFunction($_expression, $_operator, $_operand);
                break;

            // TODO
//            case SWIFT_KQL2::ELEMENT_SELECTOR:
//                break;

            case SWIFT_KQL2::ELEMENT_VALUE:
                return $_prefix . $this->CompileValue($_expression);
                break;

            case SWIFT_KQL2::ELEMENT_ARRAY:
                $_innerStrings = array();

                $this->AddNestingLevel(SWIFT_KQL2::ELEMENT_ARRAY);

                foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA] as $_innerExpression) {
                    $_innerStrings[] = $this->CompileExpression($_innerExpression, false);
                }

                $this->RemoveNestingLevel();

                return '(' . implode(', ', $_innerStrings) . ')';
                break;

            case SWIFT_KQL2::ELEMENT_VARIABLE:
                return $_prefix . $this->CompileVariable($_expression, $_operator, $_operand);
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Compiles Function
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CompileFunction($_expression, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_functionProperties = $this->_functionsContainer[$_expression[SWIFT_KQL2::EXPRESSION_DATA][0]];

        if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_COMPILER])) {
            $var = $_functionProperties[SWIFT_KQLSchema::FUNCTION_COMPILER];
            return $this->$var($_expression, $_operator, $_operand);
        } else {
            return $this->DefaultFunctionCompiler($_expression);
        }
    }

    /**
     * Default Function Compiler
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function DefaultFunctionCompiler($_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_functionProperties = $this->_functionsContainer[$_expression[SWIFT_KQL2::EXPRESSION_DATA][0]];

        $_argumentTypesContainer = false;
        if (isset($_functionProperties[SWIFT_KQLSchema::FUNCTION_ARGUMENTS])) {
            $_argumentTypesContainer = $_functionProperties[SWIFT_KQLSchema::FUNCTION_ARGUMENTS];
        }

        $this->AddNestingLevel(SWIFT_KQL2::ELEMENT_FUNCTION);

        $_returnString = $_expression[SWIFT_KQL2::EXPRESSION_DATA][0];
        $_returnString .= '(';

        $_argumentStrings = array();
        foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][1] as $_argumentIndex => $_argumentExpression) {
            $_argumentType = false;
            if ($_argumentTypesContainer && isset($_argumentTypesContainer[$_argumentIndex])) {
                $_argumentType = $_argumentTypesContainer[$_argumentIndex];
            }

            $_argumentString = $this->CompileExpression($_argumentExpression, true);
            // argumentstring = customfield5.fieldvalue. todo replace with realvalue if is serialized
            if ($_argumentType &&
                isset($_argumentExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) &&
                !SWIFT_KQLSchema::ArgumentTypeEqual($_argumentExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE], $_argumentType)) {
                if (is_array($_argumentType)) {
                    $_argumentType = SWIFT_KQL2::GetBestCastToType($_argumentExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE], $_argumentType);
                }

                $_argumentString = $this->Cast($_argumentString, $_argumentExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE], $_argumentType);
            }

            $_argumentStrings[] = $_argumentString;
        }
        $_returnString .= implode(', ', $_argumentStrings);

        $_returnString .= ')';

        $this->RemoveNestingLevel();

        return $_returnString;
    }

    /**
     * Compiles Field
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CompileField($_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_returnString = '';

        $_tableName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][0];
        $_columnName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][1];

        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
            $_linkedColumnContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO];

            list($_joinTable, $_joinColumn) = explode('.', $_linkedColumnContainer[0]);
            if (isset($this->_tableList[$_joinTable])) {
                $_tableAlias = $this->GenerateAliasNameForTable($_joinTable);

                $this->_tableList[$_tableAlias] = array(
                    SWIFT_KQL2::TABLE_NAME => $_joinTable,
                    SWIFT_KQL2::TABLE_JOIN => $_tableName . "." . $_columnName . " = " . $_tableAlias . "." . $_joinColumn,
                    SWIFT_KQL2::TABLE_RELATED => $_tableName
                );

                list($_tableName, $_columnName) = explode('.', $_linkedColumnContainer[1]);

                $_returnString .= $_tableAlias . '.' . $_columnName;
            } else {
                $this->_tableList[$_joinTable] = array(
                    SWIFT_KQL2::TABLE_JOIN => $_tableName . "." . $_columnName . " = " . $_joinTable . "." . $_joinColumn,
                    SWIFT_KQL2::TABLE_RELATED => $_tableName
                );

                list($_tableName, $_columnName) = explode('.', $_linkedColumnContainer[1]);

                $_returnString .= $_linkedColumnContainer[1];
            }
        } else {
            if ($_tableName) {
                $_returnString .= $_tableName . '.';
            }

            $_returnString .= $_columnName;
        }

        // Add auxiliary fields
        if (isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]) && isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS']) && // FIXME: Similar for custom fields
            isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_AUXILIARY]) &&
            ($this->CurrentClause() == 'SELECT') && ($this->GetNestingLevelsCount() == 0)) {
            $_auxiliaryFields = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_AUXILIARY];

            if (_is_array($_auxiliaryFields)) {
                $_aliasName = $_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'];

                foreach ($_auxiliaryFields as $_auxiliaryID => $_auxiliaryField) {
                    $this->_sqlExpressions['SELECT'][$_aliasName . '_' . $_auxiliaryID] = $_auxiliaryField;

                    $this->_hiddenFields[$_aliasName . '_' . $_auxiliaryID] = $_auxiliaryField; // TODO: $this->HideField($_aliasName. '_' . $_auxiliaryID);
                }
            }
        }

        // Saved to sorting fields
        if (($this->CurrentClause() == 'ORDER BY') && !in_array($_tableName . '.' . $_columnName, $this->_sortingFields)) {
            $this->_sortingFields[] = $_tableName . '.' . $_columnName;
        }

        return $_returnString;
    }

    /**
     * Compiles Custom Field
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CompileCustomField($_expression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_customFields = $_expression[SWIFT_KQL2::EXPRESSION_DATA];

        if (is_array($_customFields[2])) {
            // Should not ever get here actually
            $_customFieldExpressions = array();

            foreach ($_customFields[2] as $_customFieldID) {
                $_customFieldTable = 'customfield' . $_customFieldID;
                if (isset($this->_tableList[$_customFieldTable])) {
                    $_customFieldExpressions[] = $_customFieldTable . '.fieldvalue';
                }
            }

            return implode(', ', $_customFieldExpressions);
        } else {
            if (isset($_customFields[3])) { // FIXME: remove?
                return 'customfield' . $_customFields[2] . '.' . $_customFields[3];
            }

            // Saved to sorting fields
            if (($this->CurrentClause() == 'ORDER BY') && !in_array('customfield' . $_customFields[2] . '.fieldvalue', $this->_sortingFields)) {
                $this->_sortingFields[] = 'customfield' . $_customFields[2] . '.fieldvalue';
            }

            $_sqlExpression = 'customfield' . $_customFields[2] . '.fieldvalue';

            if (isset($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) && ($_expression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_DATE)) {
                if ($this->Settings->Get('dt_caltype') == 'us') {
                    $_sqlExpression = $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%m/%d/%Y')");
                } else {
                    $_sqlExpression = $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%d/%m/%Y')");
                }
            }

            return $_sqlExpression;
        }
    }

    /**
     * Adds Auxiliary or Related Fields
     *
     * @author Andriy Lesyuk
     * @param string $_variableName
     * @return array Auxiliary Fields
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddAuxiliaryFields($_variableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_variableName) || !is_string($_variableName)) {
            return array();
        }

        $_auxiliaryChunks = array();

        // Add auxiliary fields for others TODO

        // Add isserialized and isencrypted for custom fields
        $_variableChunks = explode('_', $_variableName);
        if (array_key_exists(strtolower($_variableName), $this->_inlineVariables) &&
            $this->_inlineVariables[strtolower($_variableName)][SWIFT_KQL2::EXPRESSION_TYPE] === SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
            $_cfid = $this->_inlineVariables[strtolower($_variableName)][SWIFT_KQL2::EXPRESSION_DATA ][2];
            $_auxiliaryChunks[$_variableName . '_isserialized'] = 'customfield' . $_cfid . ".isserialized";
            $_auxiliaryChunks[$_variableName . '_isencrypted'] = 'customfield' . $_cfid . ".isencrypted";
            $this->_hiddenFields[$_variableName . '_isserialized'] = $_cfid;
            $this->_hiddenFields[$_variableName . '_isencrypted'] = $_cfid;
        } elseif ((count($_variableChunks) >= 3) && ($_variableChunks[1] === 'cf')) { // FIXME: Use GetByColumnName in 4.60
            $_auxiliaryChunks[$_variableName . '_isserialized'] = 'customfield' . $_variableChunks[2] . ".isserialized";
            $_auxiliaryChunks[$_variableName . '_isencrypted'] = 'customfield' . $_variableChunks[2] . ".isencrypted";

            $this->_hiddenFields[$_variableName . '_isserialized'] = $_variableChunks[2];
            $this->_hiddenFields[$_variableName . '_isencrypted'] = $_variableChunks[2];
        }

        return $_auxiliaryChunks;
    }

    /**
     * Expands Arrays to Expressions for Some Custom Fields
     * NOTE: Used for MultiSelect Custom Fields, Converts Field IN (Value, Value) Into Field LIKE '%Value%' OR Field LIKE ...
     *
     * @author Andriy Lesyuk
     * @param array $_expressionData
     * @param mixed $_firstOperand
     * @param int $_operatorIndex
     * @param mixed $_secondOperand
     * @return bool|array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ExpandArray(&$_expressionData, $_firstOperand, $_operatorIndex, $_secondOperand)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Swap operands if the first is array
        if (is_int($_firstOperand) && is_int($_secondOperand) &&
            ($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_ARRAY)) {
            $_arrayExpression = $_expressionData[$_firstOperand];
            $_expressionData[$_firstOperand] = $_expressionData[$_secondOperand];
            $_expressionData[$_secondOperand] = $_arrayExpression;
        }

        // Expand array for multiple custom field types
        if (is_int($_firstOperand) &&
            (($_expressionData[$_operatorIndex] == 'IN') || ($_expressionData[$_operatorIndex] == 'NOT IN')) &&
            ($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD)) {
            $_customField = $this->_customFieldContainer[$_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][2]];

            if (($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED)) {
                $_expandExpression = array();

                if ($_expressionData[$_operatorIndex] == 'NOT IN') {
                    $_expandOperator = 'AND';
                    $_expressionOperator = '!=';
                } else { // IN
                    $_expandOperator = 'OR';
                    $_expressionOperator = '=';
                }

                foreach ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA] as $_itemExpression) {
                    if (count($_expandExpression) > 0) {
                        $_expandExpression[] = $_expandOperator;
                    }

                    $_expandExpression[] = $_expressionData[$_firstOperand];
                    $_expandExpression[] = $_expressionOperator;
                    $_expandExpression[] = $_itemExpression;
                }

                return $_expandExpression;
            }
        }

        return true;
    }

    /**
     * Pre-Compiles Custom Field Expressions Involving Multiple Values
     *
     * @author Andriy Lesyuk
     * @param array $_expressionData
     * @param mixed $_firstOperand
     * @param int $_operatorIndex
     * @param mixed $_secondOperand
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function PreCompileCustomField(&$_expressionData, $_firstOperand, $_operatorIndex, $_secondOperand)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Swap operands if the first is value
        if (is_int($_firstOperand) && is_int($_secondOperand) &&
            ($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
            ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) &&
            (($_expressionData[$_operatorIndex] == '=') || ($_expressionData[$_operatorIndex] == '!='))) {
            $_customField = $this->_customFieldContainer[$_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][2]];

            if (($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED)) {

                $_customFieldExpression = $_expressionData[$_firstOperand];
                $_expressionData[$_firstOperand] = $_expressionData[$_secondOperand];
                $_expressionData[$_secondOperand] = $_customFieldExpression;
            }
        }

        if (is_int($_secondOperand) &&
            ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
            !is_null($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA]) &&
            (($_expressionData[$_operatorIndex] == '=') || ($_expressionData[$_operatorIndex] == '!='))) {
            $_customField = $this->_customFieldContainer[$_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][2]];

            if (($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED)) {

                if ($_expressionData[$_operatorIndex] == '!=') {
                    $_expressionData[$_operatorIndex] = 'NOT LIKE';
                } else {
                    $_expressionData[$_operatorIndex] = 'LIKE';
                }

                $_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA] = '%:"' . $_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA] . '";%';
            }
        }

        return true;
    }

    /**
     * Compiles Operator =
     * NOTE: Just replaces = with IS for NULL
     *
     * @author Andriy Lesyuk
     * @param array $_expressionData
     * @param mixed $_firstOperand
     * @param int $_operatorIndex
     * @param mixed $_secondOperand
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Operator_CompileEqual(&$_expressionData, $_firstOperand, $_operatorIndex, $_secondOperand)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Swap operands if the first is NULL
        if (is_int($_firstOperand) && is_int($_secondOperand) &&
            ($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
            is_null($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA])) {
            $_valueExpression = $_expressionData[$_firstOperand];
            $_expressionData[$_firstOperand] = $_expressionData[$_secondOperand];
            $_expressionData[$_secondOperand] = $_valueExpression;
        }

        // Compile the operator
        if (is_int($_secondOperand) &&
            ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
            is_null($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA])) {

            // Replace operator
            switch ($_expressionData[$_operatorIndex]) {
                case '=':
                    $_expressionData[$_operatorIndex] = 'IS';
                    break;

                case '!=':
                    $_expressionData[$_operatorIndex] = 'IS NOT';
                    break;

                default:
                    break;
            }
        }

        return true;
    }

    /**
     * Compiles Operator IN
     * NOTE: Just replaces IN with = for non-arrays
     *
     * @author Andriy Lesyuk
     * @param array $_expressionData
     * @param int $_firstOperand
     * @param int $_operatorIndex
     * @param int $_secondOperand
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Operator_CompileIn(&$_expressionData, $_firstOperand, $_operatorIndex, $_secondOperand)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Compile the operator
        if (is_int($_secondOperand) &&
            ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] != SWIFT_KQL2::ELEMENT_ARRAY)) {

            // Replace operator
            switch ($_expressionData[$_operatorIndex]) {
                case 'IN':
                    $_expressionData[$_operatorIndex] = '=';
                    break;

                case 'NOT IN':
                    $_expressionData[$_operatorIndex] = '!=';
                    break;

                default:
                    break;
            }
        }

        return true;
    }

    /**
     * Pre-Compiles THISMONTH() etc Functions
     *
     * @author Andriy Lesyuk
     * @param array $_expressionData
     * @param mixed $_firstOperand
     * @param int $_operatorIndex
     * @param mixed $_secondOperand
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_PreCompileExtendedDateFunctions(&$_expressionData, $_firstOperand, $_operatorIndex, $_secondOperand)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        static $_extendedDateFunctions = array(
            'MONTHRANGE',
            'TODAY',
            'YESTERDAY',
            'TOMORROW',
            'LAST7DAYS',
            'LAST30DAYS',
            'LASTWEEK',
            'THISWEEK',
            'ENDOFWEEK',
            'NEXTWEEK',
            'LASTMONTH',
            'THISMONTH',
            'NEXTMONTH',
            'MONTH'
        );

        // Swap operands if the function is first
        if (is_int($_firstOperand) && is_int($_secondOperand) &&
            ($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
            in_array($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][0], $_extendedDateFunctions) &&

            // As we have conflicting MONTH(), check if it looks like a KQL function
            (($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][0] != 'MONTH') ||
                ((count($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][1]) > 0) &&
                    ($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                    ($_expressionData[$_firstOperand][SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING)))) {

            $_functionExpression = $_expressionData[$_firstOperand];
            $_expressionData[$_firstOperand] = $_expressionData[$_secondOperand];
            $_expressionData[$_secondOperand] = $_functionExpression;
        }

        // Compile the operator
        if (is_int($_secondOperand) &&
            ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
            in_array($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][0], $_extendedDateFunctions) &&

            // As we have conflicting MONTH(), check if it looks like a KQL function
            (($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][0] != 'MONTH') ||
                ((count($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][1]) > 0) &&
                    ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                    ($_expressionData[$_secondOperand][SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING)))) {

            // Replace operator
            switch ($_expressionData[$_operatorIndex]) {
                case '=':
                    $_expressionData[$_operatorIndex] = 'BETWEEN';
                    break;

                case '!=':
                    $_expressionData[$_operatorIndex] = 'NOT BETWEEN';
                    break;

                default:
                    break;
            }
        }

        return true;
    }

    /**
     * Compiles DATENOW() Function
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_CompileDateNow($_expression, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->CompileValue(DATENOW);
    }

    /**
     * Compiles MKTIME() Function
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_CompileMakeTime($_expression, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_arguments = array();

        foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][1] as $_argumentIndex => $_argumentExpression) {
            if (($_argumentExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                ($_argumentExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_INTEGER)) {
                $_arguments[] = $_argumentExpression[SWIFT_KQL2::EXPRESSION_DATA];
            } else {
                throw new SWIFT_KQL2_Exception('Argument ' . ($_argumentIndex + 1) . ' of MKTIME() must be integer');
            }
        }

        return $this->CompileValue(call_user_func_array('mktime', $_arguments));
    }

    /**
     * Compiles THISMONTH() etc Functions
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string|false $_operator
     * @param array|int|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Function_CompileExtendedDateFunctions($_expression, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_functionName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][0];
        $_arguments = $_expression[SWIFT_KQL2::EXPRESSION_DATA][1];

        if (is_int($_operand)) {
            $_expectedType = $_operand;
        } else {
            $_expectedType = $_operand[SWIFT_KQL2::EXPRESSION_RETURNTYPE];
        }

        if ($_operator && (($_operator == 'BETWEEN') || ($_operator == 'NOT BETWEEN'))) {
            $_fromDate = $_toDate = false;

            switch ($_functionName) {
                case 'MONTHRANGE':
                    $_monthFrom = $_arguments[0][SWIFT_KQL2::EXPRESSION_DATA];
                    $_monthTo = $_arguments[1][SWIFT_KQL2::EXPRESSION_DATA];
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('first day of ' . $_monthFrom));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('last day of ' . $_monthTo));
                    break;

                case 'TODAY':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('today'));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('today'));
                    break;

                case 'YESTERDAY':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('yesterday'));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('yesterday'));
                    break;

                case 'TOMORROW':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('tomorrow'));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('tomorrow'));
                    break;

                case 'LAST7DAYS':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('-7 days'));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('today'));
                    break;

                case 'LAST30DAYS':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('-30 days'));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('today'));
                    break;

                case 'LASTWEEK':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('last week'));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('-1 day', strtotime('this week')));
                    break;

                case 'THISWEEK':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('this week', DATENOW));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('today'));
                    break;

                case 'ENDOFWEEK':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('monday', strtotime('this week', DATENOW)));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('sunday', strtotime('this week', DATENOW)));
                    break;

                case 'NEXTWEEK':
                    $_fromDate = SWIFT_Date::FloorDate(strtotime('next week', DATENOW));
                    $_toDate = SWIFT_Date::CeilDate(strtotime('+6 days', strtotime('next week', DATENOW)));
                    break;

                case 'LASTMONTH':
                    $_fromDate = strtotime(date('Y-m-1', strtotime('last month')));
                    $_toDate = SWIFT_Date::CeilDate(strtotime(date('Y-m-t', strtotime('last month'))));
                    break;

                case 'THISMONTH':
                    $_fromDate = strtotime(date('Y-m-1'));
                    $_toDate = SWIFT_Date::CeilDate(strtotime(date('Y-m-t')));
                    break;

                case 'NEXTMONTH':
                    $_fromDate = strtotime(date('Y-m-1', strtotime('next month')));
                    $_toDate = SWIFT_Date::CeilDate(strtotime(date('Y-m-t', strtotime('next month'))));
                    break;

                case 'MONTH':
                    // Check if Month Name is Specified in Arguments, e.g. January 2010, August etc.
                    if (isset($_arguments[0])) {
                        $_monthName = $_arguments[0][SWIFT_KQL2::EXPRESSION_DATA];
                        $_fromDate = SWIFT_Date::FloorDate(strtotime('first day of ' . $_monthName));
                        $_toDate = SWIFT_Date::CeilDate(strtotime('last day of ' . $_monthName));
                    } else {
                        $_fromDate = SWIFT_Date::FloorDate(strtotime('first day of this month', DATENOW));
                        $_toDate = SWIFT_Date::CeilDate(strtotime('last day of this month', DATENOW));
                    }
                    break;
            }

            if ($_fromDate && $_toDate) { // FIXME: Does not seem to be the right place for casting...
                return $this->Cast($this->CompileValue($_fromDate), SWIFT_KQL2::DATA_UNIXDATE, $_expectedType) . ' AND ' .
                    $this->Cast($this->CompileValue($_toDate), SWIFT_KQL2::DATA_UNIXDATE, $_expectedType);
            }

        } else {
            $_date = false;

            /* TODO:
             * Should not if be: $_date = strtotime(date('Y-m-1')); for $_operator =~ /^<.*$/
             * Should not if be: $_date = SWIFT_Date::CeilDate(strtotime(date('Y-m-t'))); for $_operator =~ /^>.*$/
             */

            switch ($_functionName) {
                case 'MONTHRANGE':
                    $_monthFrom = $_arguments[0][SWIFT_KQL2::EXPRESSION_DATA];
                    $_date = SWIFT_Date::FloorDate(strtotime('first day of ' . $_monthFrom));
                    break;

                case 'TODAY':
                    $_date = strtotime('today');
                    break;

                case 'YESTERDAY':
                    $_date = strtotime('yesterday');
                    break;

                case 'TOMORROW':
                    $_date = strtotime('tomorrow');
                    break;

                case 'LAST7DAYS':
                    $_date = SWIFT_Date::FloorDate(strtotime('-7 days'));
                    break;

                case 'LAST30DAYS':
                    $_date = SWIFT_Date::FloorDate(strtotime('-30 days'));
                    break;

                case 'LASTWEEK':
                    $_date = SWIFT_Date::FloorDate(strtotime('-1 week'));
                    break;

                case 'THISWEEK':
                    $_date = SWIFT_Date::FloorDate(strtotime('this week', DATENOW));
                    break;

                case 'ENDOFWEEK':
                    $_date = SWIFT_Date::CeilDate(strtotime('sunday', strtotime('this week', DATENOW)));
                    break;

                case 'NEXTWEEK':
                    $_date = SWIFT_Date::FloorDate(strtotime('next week', DATENOW));
                    break;

                case 'LASTMONTH':
                    $_date = SWIFT_Date::FloorDate(strtotime('first day of last month', DATENOW));
                    break;

                case 'THISMONTH':
                    $_date = SWIFT_Date::FloorDate(strtotime('first day of this month', DATENOW));
                    break;

                case 'NEXTMONTH':
                    $_date = SWIFT_Date::FloorDate(strtotime('first day of next month', DATENOW));
                    break;

                case 'MONTH':
                    // Assuming this is the KQL function
                    if ($_operator && isset($_arguments[0]) &&
                        ($_arguments[0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VALUE) &&
                        ($_arguments[0][SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_STRING)) {
                        $_monthName = $_arguments[0][SWIFT_KQL2::EXPRESSION_DATA];
                        $_date = SWIFT_Date::FloorDate(strtotime('first day of ' . $_monthName));
                        // KQL's MONTH() without arguments
                    } elseif (count($_arguments) == 0) {
                        $_date = SWIFT_Date::FloorDate(strtotime('first day of this month', DATENOW));
                        // Otherwise use MySQL function
                    } else {
                        if (count($_arguments) == 1) {
                            $_argumentString = $this->CompileExpression($_arguments[0], true);

                            if (isset($_arguments[0][SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                                $_argumentString = $this->Cast($_argumentString, $_arguments[0][SWIFT_KQL2::EXPRESSION_RETURNTYPE], SWIFT_KQL2::DATA_DATE);
                            }

                            return $_functionName . '(' . $_argumentString . ')';
                        } else {
                            throw new SWIFT_KQL2_Exception('MONTH() requires one argument');
                        }
                    }
                    break;
            }

            if ($_date) { // FIXME: Does not seem to be the right place for casting...
                return $this->Cast($this->CompileValue($_date), SWIFT_KQL2::DATA_UNIXDATE, $_expectedType);
            }

        }

        throw new SWIFT_KQL2_Exception('Compilation error: invalid usage of ' . $_functionName . '()');
    }

    /**
     * Compiles Values
     *
     * @author Andriy Lesyuk
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CompileValue($_value)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (is_array($_value)) {
            if (isset($_value[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
                switch ($_value[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) {
                    case SWIFT_KQL2::DATA_DATETIME:
                    case SWIFT_KQL2::DATA_DATE:
                        $_time = localtime($_value[SWIFT_KQL2::EXPRESSION_DATA], true);
                        $_dateValue = sprintf("%04d-%02d-%02d", $_time['tm_year'] + 1900, $_time['tm_mon'] + 1, $_time['tm_mday']);
                        if ($_value[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_DATETIME) {
                            $_dateValue .= ' ' . sprintf("%02d:%02d:%02d", $_time['tm_hour'], $_time['tm_min'], $_time['tm_sec']);
                        }
                        return $this->CompileValue($_dateValue);
                        break;

                    case SWIFT_KQL2::DATA_TIME:
                        $_timeValue = sprintf("%d:%02d:%02d",
                            floor($_value[SWIFT_KQL2::EXPRESSION_DATA] / 3600),
                            floor(($_value[SWIFT_KQL2::EXPRESSION_DATA] % 3600) / 60),
                            $_value[SWIFT_KQL2::EXPRESSION_DATA] % 60);
                        return $this->CompileValue($_timeValue);
                        break;

                    default:
                        break;
                }
            }

            $_value = $_value[SWIFT_KQL2::EXPRESSION_DATA];
        }

        if (is_null($_value)) {
            return 'NULL';
        } elseif (is_bool($_value)) {
            return ($_value) ? '1' : '0';
        } elseif (is_int($_value) || is_float($_value)) {
            return strval($_value);
        } else {
            return "'" . $this->Database->Escape($_value) . "'";
        }
    }

    /**
     * Compiles Variables
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string|bool The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CompileVariable($_expression, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variableName = $_expression[SWIFT_KQL2::EXPRESSION_DATA];
        $_variable = $this->GetVariable($_variableName);

        // Schema variables
        if ($_variable[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_VARIABLE) {
            $_variableProperties = $this->_variablesContainer[$_variable[SWIFT_KQL2::EXPRESSION_DATA]];
            if ($_variableProperties && isset($_variableProperties[SWIFT_KQLSchema::VARIABLE_COMPILER])) {
                $var = $_variableProperties[SWIFT_KQLSchema::VARIABLE_COMPILER];
                return $this->$var($_variable, $_operator, $_operand);
            }

            // Dynamic or inline variable, i.e., alias
        } else {
            if (($this->GetNestingLevelsCount() == 0) &&
                (($this->CurrentClause() == 'GROUP BY') || ($this->CurrentClause() == 'ORDER BY'))) {
                return "'" . $this->Database->Escape($_variableName) . "'";
            } else {
                $_sqlExpression = $this->CompileExpression($_variable, true, $_operator, $_operand);

                $this->CompilePostModifiers($_variable, $_sqlExpression);

                if (!empty($_sqlExpression)) {
                    return $_sqlExpression;
                }
            }
        }

        return false;
    }

    /**
     * Compiles $_STAFF Variable
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Variable_Compile_Staff($_expression, $_operator = false, $_operand = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_StaffObject = SWIFT_ReportBase::GetStaff();
        if (!empty($_SWIFT_StaffObject)) {
            if ($_operand && isset($_operand[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) { // FIXME: Write RETURNTYPE
                switch ($_operand[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) {
                    case SWIFT_KQL2::DATA_STRING:
                        return $this->CompileValue($_SWIFT_StaffObject->GetProperty('fullname'));
                        break;

                    default:
                        return $this->CompileValue($_SWIFT_StaffObject->GetStaffID());
                        break;
                }
            } else {
                return $this->CompileValue($_SWIFT_StaffObject->GetProperty('fullname'));
            }
        } else {
            return $this->CompileValue(null);
        }
    }

    /**
     * Compiles $_NOW Variable
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param string|false $_operator
     * @param array|false $_operand
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Variable_Compile_Now($_expression, $_operator = false, $_operand = false) // FIXME: What if function argument?
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_operand && isset($_operand[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
            return $this->Cast($this->CompileValue(DATENOW), SWIFT_KQL2::DATA_UNIXDATE, $_operand[SWIFT_KQL2::EXPRESSION_RETURNTYPE]);
        }

        return $this->CompileValue(DATENOW);
    }

    /**
     * Cast Expressions Using SQL
     *
     * @author Andriy Lesyuk
     * @param mixed $_sqlExpression
     * @param int $_fromType
     * @param int $_toType
     * @return string The SQL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Cast($_sqlExpression, $_fromType, $_toType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_string($_sqlExpression)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // TODO: Save new type

        switch ($_toType) {
            case SWIFT_KQL2::DATA_BOOLEAN:
                if ($_fromType == SWIFT_KQL2::DATA_STRING) {
                    return '(LENGTH(' . $_sqlExpression . ') > 0)';
                } elseif (
                    ($_fromType == SWIFT_KQL2::DATA_INTEGER) ||
                    ($_fromType == SWIFT_KQL2::DATA_FLOAT) ||
                    ($_fromType == SWIFT_KQL2::DATA_NUMERIC) || // General type
                    ($_fromType == SWIFT_KQL2::DATA_SECONDS) || // KQL-only type
                    ($_fromType == SWIFT_KQL2::DATA_UNIXDATE)) { // KQL-only type
                    return '(' . $_sqlExpression . ' > 0)';
                } elseif ($_fromType == SWIFT_KQL2::DATA_TIME) {
                    return '(TIME_TO_SEC(' . $_sqlExpression . ') > 0)';
                } elseif (
                    ($_fromType == SWIFT_KQL2::DATA_DATE) ||
                    ($_fromType == SWIFT_KQL2::DATA_DATETIME)) {
                    return '(UNIX_TIMESTAMP(' . $_sqlExpression . ') > 0)';
                }
                break;

            case SWIFT_KQL2::DATA_INTEGER:
            case SWIFT_KQL2::DATA_FLOAT:
            case SWIFT_KQL2::DATA_SECONDS:
            case SWIFT_KQL2::DATA_NUMERIC:
                if ($_fromType == SWIFT_KQL2::DATA_TIME) {
                    return 'TIME_TO_SEC(' . $_sqlExpression . ')';
                } elseif (
                    ($_fromType == SWIFT_KQL2::DATA_DATE) ||
                    ($_fromType == SWIFT_KQL2::DATA_DATETIME)) {
                    return 'UNIX_TIMESTAMP(' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_STRING) {
                    return 'CONVERT(' . $_sqlExpression . ', DECIMAL(10,2))';
                }
                break;

            case SWIFT_KQL2::DATA_INTERVAL:
                if (($_fromType == SWIFT_KQL2::DATA_BOOLEAN) ||
                    ($_fromType == SWIFT_KQL2::DATA_INTEGER) ||
                    ($_fromType == SWIFT_KQL2::DATA_SECONDS) ||
                    ($_fromType == SWIFT_KQL2::DATA_NUMERIC)) {
                    return 'INTERVAL ' . $_sqlExpression . ' SECOND';
                } elseif ($_fromType == SWIFT_KQL2::DATA_FLOAT) {
                    return 'INTERVAL ' . $_sqlExpression . ' SECOND_MICROSECOND';
                } elseif ($_fromType == SWIFT_KQL2::DATA_TIME) {
                    return 'INTERVAL TIME_TO_SEC(' . $_sqlExpression . ') SECOND';
                }
                break;

            case SWIFT_KQL2::DATA_TIME:
                if (($_fromType == SWIFT_KQL2::DATA_BOOLEAN) ||
                    ($_fromType == SWIFT_KQL2::DATA_INTEGER) ||
                    ($_fromType == SWIFT_KQL2::DATA_FLOAT) ||
                    ($_fromType == SWIFT_KQL2::DATA_SECONDS) ||
                    ($_fromType == SWIFT_KQL2::DATA_NUMERIC)) {
                    return 'SEC_TO_TIME(' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_DATE) {
                    return "'00:00:00'";
                } elseif ($_fromType == SWIFT_KQL2::DATA_DATETIME) {
                    return 'TIME(' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_UNIXDATE) {
                    return 'TIME(FROM_UNIXTIME(' . $_sqlExpression . '))';
                } elseif ($_fromType == SWIFT_KQL2::DATA_STRING) {
                    return "TIME(" . $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%H:%i:%s')") . ")";
                }
                break;

            case SWIFT_KQL2::DATA_DATE:
                if (($_fromType == SWIFT_KQL2::DATA_INTEGER) ||
                    ($_fromType == SWIFT_KQL2::DATA_FLOAT) ||
                    ($_fromType == SWIFT_KQL2::DATA_SECONDS) ||
                    ($_fromType == SWIFT_KQL2::DATA_NUMERIC) ||
                    ($_fromType == SWIFT_KQL2::DATA_UNIXDATE)) {
                    return 'FROM_UNIXTIME(' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_STRING) {
                    if ($this->Settings->Get('dt_caltype') == 'us') {
                        return $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%m/%d/%Y')");
                    } else {
                        return $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%d/%m/%Y')");
                    }
                }
                break;

            case SWIFT_KQL2::DATA_UNIXDATE:
                if ($_fromType == SWIFT_KQL2::DATA_TIME) {
                    return 'UNIX_TIMESTAMP(TIMESTAMP(CURDATE(), ' . $_sqlExpression . '))';
                } elseif (
                    ($_fromType == SWIFT_KQL2::DATA_DATE) ||
                    ($_fromType == SWIFT_KQL2::DATA_DATETIME)) {
                    return 'UNIX_TIMESTAMP(' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_STRING) {
                    if ($this->Settings->Get('dt_caltype') == 'us') {
                        return "UNIX_TIMESTAMP(" . $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%m/%d/%Y %h:%i:%s')") . ")";
                    } else {
                        return "UNIX_TIMESTAMP(" . $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%d/%m/%Y %H:%i:%s')") . ")";
                    }
                }
                break;

            case SWIFT_KQL2::DATA_DATETIME:
                if (($_fromType == SWIFT_KQL2::DATA_INTEGER) ||
                    ($_fromType == SWIFT_KQL2::DATA_FLOAT) ||
                    ($_fromType == SWIFT_KQL2::DATA_SECONDS) ||
                    ($_fromType == SWIFT_KQL2::DATA_NUMERIC) ||
                    ($_fromType == SWIFT_KQL2::DATA_UNIXDATE)) {
                    return 'FROM_UNIXTIME(' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_TIME) {
                    return 'TIMESTAMP(CURDATE(), ' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_DATE) {
                    return 'TIMESTAMP(' . $_sqlExpression . ')';
                } elseif ($_fromType == SWIFT_KQL2::DATA_STRING) {
                    if ($this->Settings->Get('dt_caltype') == 'us') {
                        return $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%m/%d/%Y %h:%i:%s')");
                    } else {
                        return $this->FixFieldTimeZone("STR_TO_DATE(" . $_sqlExpression . ", '%d/%m/%Y %H:%i:%s')");
                    }
                }
                break;

            case SWIFT_KQL2::DATA_STRING:
                if (($_fromType == SWIFT_KQL2::DATA_BOOLEAN) || // '1' or '0'
                    ($_fromType == SWIFT_KQL2::DATA_INTEGER) || // '100'
                    ($_fromType == SWIFT_KQL2::DATA_FLOAT) ||   // '5.8'
                    ($_fromType == SWIFT_KQL2::DATA_NUMERIC)) {
                    return "'" . $_sqlExpression . "'";
                } elseif ($_fromType == SWIFT_KQL2::DATA_SECONDS) { // '5d 8h 4m'
                    return "CONCAT(IF(" . $_sqlExpression . " > 86400, CONCAT(FLOOR(" . $_sqlExpression . " / 86400), 'd '), ''), " .
                        "IF(" . $_sqlExpression . " > 3600, CONCAT(FLOOR((" . $_sqlExpression . " % 86400) / 3600), 'h '), ''), " .
                        "IF(" . $_sqlExpression . " > 60, CONCAT(FLOOR((" . $_sqlExpression . " % 3600) / 60), 'm '), ''), " .
                        $_sqlExpression . " % 60, 's')";
                } elseif ($_fromType == SWIFT_KQL2::DATA_TIME) { // '10:00:02'
                    return "TIME_FORMAT(" . $_sqlExpression . ", '%H:%i:%s')";
                } elseif ($_fromType == SWIFT_KQL2::DATA_DATE) { // '02/08/2012'
                    if ($this->Settings->Get('dt_caltype') == 'us') {
                        return "DATE_FORMAT(" . $_sqlExpression . ", '%m/%d/%Y')";
                    } else {
                        return "DATE_FORMAT(" . $_sqlExpression . ", '%d/%m/%Y')";
                    }
                } elseif ($_fromType == SWIFT_KQL2::DATA_DATETIME) { // '02/08/2012 10:00:02'
                    if ($this->Settings->Get('dt_caltype') == 'us') {
                        return "DATE_FORMAT(" . $_sqlExpression . ", '%m/%d/%Y %h:%i:%s')";
                    } else {
                        return "DATE_FORMAT(" . $_sqlExpression . ", '%d/%m/%Y %H:%i:%s')";
                    }
                } elseif ($_fromType == SWIFT_KQL2::DATA_UNIXDATE) { // '02/08/2012 10:00:02'
                    if ($this->Settings->Get('dt_caltype') == 'us') {
                        return "DATE_FORMAT(FROM_UNIXTIME(" . $_sqlExpression . "), '%m/%d/%Y %h:%i:%s')";
                    } else {
                        return "DATE_FORMAT(FROM_UNIXTIME(" . $_sqlExpression . "), '%d/%m/%Y %H:%i:%s')";
                    }
                }
                break;
        }

        return $_sqlExpression;
    }

    /**
     * Generates Variable Name for Expression
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @param bool $_force
     * @param bool $_useLinkedField
     * @return string|bool The Variable Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GenerateVariableNameForExpression($_expression, $_force = false, $_useLinkedField = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variableName = '';

        if (isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
            isset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS'])) {
            if ($_force == true) {
                $_variableName = preg_replace('/[^A-Za-z0-9_]/', '', $_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS']);

                unset($_expression[SWIFT_KQL2::EXPRESSION_EXTRA]['AS']);
            } else {
                return false;
            }

        } else {
            $_fieldExpression = $_expression;

            if ($_expression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) {
                $_variableName .= mb_strtolower($_expression[SWIFT_KQL2::EXPRESSION_DATA][0]);

                foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][1] as $_functionArgument) {
                    if (($_functionArgument[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) ||
                        ($_functionArgument[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD)) {
                        $_fieldExpression = $_functionArgument;
                        $_variableName .= '_';
                        break;
                    }
                }
            }

            if ($_fieldExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
                if ($_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA][1] != '*') {
                    $_tableName = $_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA][0];
                    $_columnName = $_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA][1];

                    if ($_useLinkedField &&
                        isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
                        $_linkedColumnContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO];

                        $_variableName .= implode('_', explode('.', $_linkedColumnContainer[1]));
                    } elseif (count($_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA]) == 2) {
                        $_variableName .= implode('_', $_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA]);
                    }
                } elseif ($_force == false) {
                    return false;
                }
            } elseif ($_fieldExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                if (count($_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA]) == 3) {
                    $_variableName .= '_cf_' . $_fieldExpression[SWIFT_KQL2::EXPRESSION_DATA][2];
                } elseif ($_force == false) {
                    return false;
                }
            } elseif ($_force == false) {
                return false;
            }
        }

        $_sequenceNumber = false;
        while ($this->GetVariable($_variableName . (($_sequenceNumber) ? '_' . $_sequenceNumber : ''))) {
            if ($_sequenceNumber) {
                $_sequenceNumber++;
            } else {
                $_sequenceNumber = 2;
            }
        }
        $_variableName = $_variableName . (($_sequenceNumber) ? '_' . $_sequenceNumber : '');

        $this->_dynamicVariables[$_variableName] = $_expression;

        return $_variableName;
    }

    /**
     * Generates Alias Name for Table
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @return string The Alias Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GenerateAliasNameForTable($_tableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_aliasName = $_tableName;

        $_sequenceNumber = false;
        while (isset($this->_tableList[$_aliasName . (($_sequenceNumber) ? $_sequenceNumber : '')])) {
            if ($_sequenceNumber) {
                $_sequenceNumber++;
            } else {
                $_sequenceNumber = 2;
            }
        }

        return $_aliasName . (($_sequenceNumber) ? $_sequenceNumber : '');
    }

    /**
     * Returns Array Similar to $_returnGroupByFields
     *
     * @author Andriy Lesyuk
     * @return array|bool (table.field, func_table_field)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compat_ReturnGroupByFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure['GROUP BY'])) {
            return false;
        }

        $_returnGroupByFields = array();

        foreach ($this->_kqlStructure['GROUP BY'] as $_expressionIndex => $_expressionData) {
            if (isset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
                isset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]['X'])) {
                continue;
            }

            $_fieldName = $_aliasName = '';

            if (($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
                in_array($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0], self::$_kql1ExtendedFunctions)) {
                if ((count($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1]) == 1) &&
                    (($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) ||
                        ($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD))) {

                    $_aliasName .= mb_strtolower($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0]) . '_';

                    $_expressionData = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0];
                }
            }

            if ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
                $_tableName = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0];
                $_columnName = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1];

                if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
                    $_linkedColumnContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO];

                    list($_tableName, $_columnName) = explode('.', $_linkedColumnContainer[1]);
                }

                $_fieldName = $_tableName . '.' . $_columnName;
                $_aliasName .= $_tableName . '_' . $_columnName;
            } elseif ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                if (!is_array($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2])) {
                    $_fieldName = 'cf.' . $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2];
                    $_aliasName .= '_cf_' . $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2];
                }
            }

            // Try taking alias from SELECT expressions
            if (empty($_aliasName)) {
                $_sqlExpression = $this->CompileExpression($_expressionData); // FIXME: Cache expressions above

                $this->CompilePostModifiers($_expressionData, $_sqlExpression, 'GROUP BY');

                if (isset($this->_expressionsToAliasesMap[$_sqlExpression])) {
                    $_aliasName = $this->_expressionsToAliasesMap[$_sqlExpression];
                } else {
                    continue;
                }
            }

            $_returnGroupByFields[] = array($_fieldName, $_aliasName);
        }

        return $_returnGroupByFields;
    }

    /**
     * Returns Array Similar to $_returnGroupByXFields
     *
     * @author Andriy Lesyuk
     * @return array|bool (table.field, func_table_field)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compat_ReturnGroupByXFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure['GROUP BY'])) {
            return false;
        }

        $_returnGroupByXFields = array();

        foreach ($this->_kqlStructure['GROUP BY'] as $_expressionIndex => $_expressionData) {
            if (!isset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]) ||
                !isset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]['X'])) {
                continue;
            }

            $_fieldName = $_aliasName = '';

            if (($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
                in_array($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0], self::$_kql1ExtendedFunctions)) {
                if ((count($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1]) == 1) &&
                    (($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) ||
                        ($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD))) {

                    $_aliasName .= mb_strtolower($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0]) . '_';

                    $_expressionData = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0];
                } else {
                    continue;
                }
            }

            if ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
                $_tableName = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0];
                $_columnName = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1];

                if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
                    $_linkedColumnContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO];

                    list($_tableName, $_columnName) = explode('.', $_linkedColumnContainer[1]);
                }

                $_fieldName = $_tableName . '.' . $_columnName;
                $_aliasName .= $_tableName . '_' . $_columnName;
            } elseif ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                if (!is_array($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2])) {
                    $_fieldName = 'cf.' . $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2];
                    $_aliasName .= '_cf_' . $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2];
                } else {
                    continue;
                }
            } else {
                continue;
            }

            if (isset($this->_compat_ReturnGroupByFields[$_aliasName])) {
                $_sqlExpression = $this->_compat_ReturnGroupByFields[$_aliasName];
            } else {
                unset($_expressionData[SWIFT_KQL2::EXPRESSION_EXTRA]['X']);

                $_sqlExpression = $this->CompileExpression($_expressionData); // FIXME: Cache expressions above

                $this->CompilePostModifiers($_expressionData, $_sqlExpression, 'GROUP BY');

                if (empty($_sqlExpression)) {
                    continue;
                }
            }

            $_returnGroupByXFields[] = array($_fieldName, $_aliasName, $_sqlExpression);
        }

        return $_returnGroupByXFields;
    }

    /**
     * Returns Array Similar to $_returnMultiGroupByFields
     *
     * @author Andriy Lesyuk
     * @return array|bool (table.field, func_table_field)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compat_ReturnMultiGroupByFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_kqlStructure['MULTIGROUP BY'])) {
            return false;
        }

        $_returnMultiGroupByFields = array();

        foreach ($this->_kqlStructure['MULTIGROUP BY'] as $_expressionIndex => $_expressionData) {
            $_fieldName = $_aliasName = '';

            if (($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FUNCTION) &&
                in_array($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0], self::$_kql1ExtendedFunctions)) {
                if ((count($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1]) == 1) &&
                    (($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) ||
                        ($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0][SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD))) {

                    $_aliasName .= mb_strtolower($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0]) . '_';

                    $_expressionData = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1][0];
                } else {
                    continue;
                }
            }

            if ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
                $_tableName = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][0];
                $_columnName = $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][1];

                if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
                    $_linkedColumnContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO];

                    list($_tableName, $_columnName) = explode('.', $_linkedColumnContainer[1]);
                }

                $_fieldName = $_tableName . '.' . $_columnName;
                $_aliasName .= $_tableName . '_' . $_columnName;
            } elseif ($_expressionData[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_CUSTOMFIELD) {
                if (!is_array($_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2])) {
                    $_fieldName = 'cf.' . $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2];
                    $_aliasName .= '_cf_' . $_expressionData[SWIFT_KQL2::EXPRESSION_DATA][2];
                } else {
                    continue;
                }
            } else {
                continue;
            }

            if (isset($this->_compat_returnMultiGroupByFields[$_aliasName])) {
                $_sqlExpression = $this->_compat_returnMultiGroupByFields[$_aliasName];
            } else {
                $_sqlExpression = $this->CompileExpression($_expressionData);

                $this->CompilePostModifiers($_expressionData, $_sqlExpression, 'MULTIGROUP BY');

                if (empty($_sqlExpression)) {
                    continue;
                }
            }

            $_returnMultiGroupByFields[] = array($_fieldName, $_aliasName, $_sqlExpression);
        }

        return $_returnMultiGroupByFields;
    }

    /**
     * Return Alias-to-Field Map
     *
     * @author Andriy Lesyuk
     * @return array The Aliases to Fields Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compat_GetAliasMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_aliasesFieldsMap = array();

        foreach ($this->_sqlExpressions['SELECT'] as $_variableName => $_sqlExpression) {
            if (is_string($_variableName)) {
                if (preg_match('/^([a-z0-9]+)\(([^\(\)]*)\)$/i', $_sqlExpression, $_functionMatches)) {
                    $_aliasesFieldsMap[$_variableName] = $_functionMatches[2];
                } else {
                    $_aliasesFieldsMap[$_variableName] = $_sqlExpression;
                }
            }
        }

        return $_aliasesFieldsMap;
    }

    /**
     * Return Field-to-Function Map
     *
     * @author Andriy Lesyuk
     * @return array The Fields to Functions Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compat_GetFunctionMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_functionsFieldsMap = array();

        foreach ($this->_sqlExpressions['SELECT'] as $_variableName => $_sqlExpression) {
            if (preg_match('/^([a-z0-9]+)\(([^\(\)]*)\)$/i', $_sqlExpression, $_functionMatches)) {
                $_functionName = $_functionMatches[1];
                $_innerExpression = $_functionMatches[2];
            } else {
                continue;
            }

            if (is_string($_variableName)) {
                $_functionsFieldsMap[$_variableName] = array($_functionName, $_innerExpression);
            } else {
                $_functionsFieldsMap[$_sqlExpression] = array($_functionName, $_innerExpression);
            }
        }

        return $_functionsFieldsMap;
    }

    /**
     * Return Original Aliases Map
     *
     * @author Andriy Lesyuk
     * @return array The Original Aliases Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compat_GetOriginalAliasMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_originalAliasesMap = array();

        foreach ($this->_sqlExpressions['SELECT'] as $_variableName => $_sqlExpression) {
            $_originalAliasesMap[$_variableName] = $_variableName;
        }

        return $_originalAliasesMap;
    }

    /**
     * Return Hidden Fields
     *
     * @author Andriy Lesyuk
     * @return array The Hidden Fields
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHiddenFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_hiddenFields;
    }

    /**
     * Get Custom Fields
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
     * Collects Fields Used in the Expression
     *
     * @author Andriy Lesyuk
     * @param array $_expression
     * @return array The Fields
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUsedFieldsFromExpression($_expression) // FIXME: _schemaContainer, _includeCustomFields
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_usedFields = array();

        switch ($_expression[SWIFT_KQL2::EXPRESSION_TYPE]) {
            case SWIFT_KQL2::ELEMENT_EXPRESSION:
                foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA] as $_innerExpression) {
                    if (is_array($_innerExpression)) {
                        $_usedFields = array_merge($_usedFields, $this->GetUsedFieldsFromExpression($_innerExpression));
                    }
                }
                break;

            case SWIFT_KQL2::ELEMENT_FIELD:
                $_tableName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][0];
                $_columnName = $_expression[SWIFT_KQL2::EXPRESSION_DATA][1];

                if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
                    $_linkedColumnContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO];

                    list($_tableName, $_columnName) = explode('.', $_linkedColumnContainer[1]);
                }

                $_usedFields[] = $_tableName . '.' . $_columnName;
                break;

            case SWIFT_KQL2::ELEMENT_CUSTOMFIELD:
                if (isset($_expression[SWIFT_KQL2::EXPRESSION_DATA][2])) {
                    if (is_array($_expression[SWIFT_KQL2::EXPRESSION_DATA][2])) {
                        foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][2] as $_customField) {
                            $_usedFields[] = 'customfield' . $_customField . '.fieldvalue';
                        }
                    } else {
                        $_usedFields[] = 'customfield' . $_expression[SWIFT_KQL2::EXPRESSION_DATA][2] . '.fieldvalue';
                    }
                }
                break;

            case SWIFT_KQL2::ELEMENT_FUNCTION:
                foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA][1] as $_argumentExpression) {
                    $_usedFields = array_merge($_usedFields, $this->GetUsedFieldsFromExpression($_argumentExpression));
                }
                break;

            case SWIFT_KQL2::ELEMENT_ARRAY:
                foreach ($_expression[SWIFT_KQL2::EXPRESSION_DATA] as $_innerExpression) {
                    $_usedFields = array_merge($_usedFields, $this->GetUsedFieldsFromExpression($_innerExpression));
                }
                break;

            default:
                break;
        }

        return $_usedFields;
    }

    /**
     * Get MySQL Server Time Zone in +HH:MM Format
     *
     * @author Andriy Lesyuk
     * @return string The Time Zone Used on MySQL Server
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetMySQLTimeZone()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (self::$_mysqlTimeZone === false) {
            self::$_mysqlTimeZone = '+00:00';

            $_mysqlTimeZone = '';

            $_sqlStatement = "SELECT TIMEDIFF(NOW(),CONVERT_TZ(NOW(), @@global.time_zone, '+00:00')) AS timezone";

            $this->Database->Query($_sqlStatement);
            if ($this->Database->NextRecord()) {
                $_mysqlTimeZone = $this->Database->Record['timezone'];
            }

            if (preg_match('/^([\-\+]?)([0-9]{2}:[0-9]{2}):[0-9]{2}$/', $_mysqlTimeZone, $_matches)) {
                $_sign = $_matches[1];
                if ($_sign != '-') {
                    $_sign = '+';
                }

                self::$_mysqlTimeZone = $_sign . $_matches[2];
            }
        }

        return self::$_mysqlTimeZone;
    }

    /**
     * Fix the Time Zone of the Date Converted By MySQL
     * FIXME: SWIFT sets session time zone for reports so this function may need to be removed
     *
     * @author Andriy Lesyuk
     * @param string $_sqlExpression
     * @return string The Fixed SQL Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function FixFieldTimeZone($_sqlExpression) # FIXME: Remove if no bugs
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // NOTE: maybe not the best fix (does not take daylight saving into account?)
        #$_timeZone = new DateTimeZone(SWIFT::Get('timezone'));
        #$_secondsOffset = $_timeZone->getOffset(new DateTime());
        #$_swiftTimeZone = sprintf("%s%02d:%02d", ($_secondsOffset < 0) ? '-' : '+', abs($_secondsOffset) / 3600, abs($_secondsOffset) % 3600);
        #$_mysqlTimeZone = $this->GetMySQLTimeZone();

        #if ($_swiftTimeZone != $_mysqlTimeZone) {
        #    return "CONVERT_TZ(" . $_sqlExpression . ", '" . $_swiftTimeZone . "', '" . $_mysqlTimeZone . "')";
        #} else {
        return $_sqlExpression;
        #}
    }

    /**
     * Separates ASC/DESC and Expression for ORDER BY
     *
     * @author Andriy Lesyuk
     * @param string $_sqlExpression
     * @return array The Expression and The Sort Mode
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SplitOrderByMode($_sqlExpression)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (preg_match('/^(.*?) +(ASC|DESC)$/i', $_sqlExpression, $_matches)) {
            return array($_matches[1], $_matches[2]);
        } else {
            return array($_sqlExpression, false);
        }
    }

    /**
     * Resets Nesting Levels
     *
     * @author Andriy Lesyuk
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ClearNestingLevels()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_nestingLevels = array();

        return true;
    }

    /**
     * Adds Nesting Level
     *
     * @author Andriy Lesyuk
     * @param int $_expressionType
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNestingLevel($_expressionType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        array_unshift($this->_nestingLevels, $_expressionType);

        return true;
    }

    /**
     * Removes Nesting Level
     *
     * @author Andriy Lesyuk
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RemoveNestingLevel()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        array_shift($this->_nestingLevels);

        return true;
    }

    /**
     * Returns Number of Nesting Levels
     *
     * @author Andriy Lesyuk
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNestingLevelsCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return count($this->_nestingLevels);
    }

    /**
     * Gets Parent Expression Type (Uses Nesting Levels)
     *
     * @author Andriy Lesyuk
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCurrentExpressionType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return isset($this->_nestingLevels[0]) && $this->_nestingLevels[0];
    }

    /**
     * Get the Field Type
     * NOTE: A Copy of SWIFT_KQL2Parser::GetFieldType
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
                    return SWIFT_KQL2Parser::ConvertKQLSchemaToKQLType($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE]);
                }

            } else {
                return SWIFT_KQL2Parser::ConvertKQLSchemaToKQLType($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE]);
            }
        }

        return false;
    }

    /**
     * Adds Expression to Distinct Expression List
     *
     * @author Andriy Lesyuk
     * @param string $_expressionID
     * @param string $_expressionString
     * @param bool $_overwrite
     * @return bool True if Added, False if Already There
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddDistinctExpression($_expressionID, $_expressionString, $_overwrite = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_distinctExpressions[$_expressionID]) || $_overwrite) {
            $this->_distinctExpressions[$_expressionID] = $_expressionString;

            return true;
        } else {
            return false;
        }
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
     * Find Dynamic Variable By Name and Return
     *
     * @param string $_variableName
     * @author Andriy Lesyuk
     * @return array|bool The Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDynamicVariable($_variableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_dynamicVariables[$_variableName])) {
            return $this->_dynamicVariables[$_variableName];
        }

        return false;
    }

    /**
     * Find Inline Variable By Name and Return
     *
     * @param string $_variableName
     * @author Andriy Lesyuk
     * @return array|bool The Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetInlineVariable($_variableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_inlineVariables[$_variableName])) {
            return $this->_inlineVariables[$_variableName];
        }

        return false;
    }

    # TODO + GetSchemaVariable? or GetVariableProperties

    /**
     * Find Variable (Any) By Name and Return
     *
     * @param string $_variableName
     * @author Andriy Lesyuk
     * @return array|bool The Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetVariable($_variableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variableName = mb_strtolower($_variableName);

        // For dynamic variables return expression
        if (isset($this->_dynamicVariables[$_variableName])) {
            return $this->_dynamicVariables[$_variableName];
            // For inline as well
        } elseif (isset($this->_inlineVariables[$_variableName])) {
            return $this->_inlineVariables[$_variableName];
            // For schema variables generate expression
        } elseif (isset($this->_variablesContainer[mb_strtoupper($_variableName)])) {
            return array(
                SWIFT_KQL2::EXPRESSION_TYPE => SWIFT_KQL2::ELEMENT_VARIABLE,
                SWIFT_KQL2::EXPRESSION_DATA => mb_strtoupper($_variableName)
            );
        }

        return false;
    }

    /**
     * Get KQL Expression By Column Name
     *
     * @author Andriy Lesyuk
     * @param string $_variableName
     * @param bool $_rowIndex
     * @return array|bool The KQL Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetExpressionByColumnName($_variableName, $_rowIndex = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (($_rowIndex !== false) && ($_rowIndex >= 0)) {
            if (isset($this->_sequentColumnNames[$_rowIndex]) &&
                isset($this->_sequentColumnNames[$_rowIndex][$_variableName])) {
                return $this->_sequentColumnNames[$_rowIndex][$_variableName];
            }

            return false;
        }

        // First check column names generated while compiling SELECT
        if (isset($this->_columnNames[$_variableName])) {
            return $this->_columnNames[$_variableName];
            // Now check distinct columns etc
        } elseif (isset($this->_dynamicVariables[$_variableName])) {
            return $this->_dynamicVariables[$_variableName];
        }

        return false;
    }

    /**
     * TODO: Should be in KQL2Base and KQL API, some classes may use ->KQL->*()
     */
//    public function GetFieldProperties($_tableName, $_fieldName) {}
//    public function GetCustomFieldProperties($_customFieldID) {}
//    public function GetClauseProperties($_clauseName) {}
//    public function GetOperatorProperties($_operator) {}
//    public function GetFunctionProperties($_functionName) {}
//    public function GetPreModifierProperties($_modifierName) {}
//    public function GetPostModifierProperties($_modifierName) {}
//    public function GetIdentifierProperties($_identifierName) {}
//    public function GetVariableProperties($_variableName) {} // TODO

}

?>
