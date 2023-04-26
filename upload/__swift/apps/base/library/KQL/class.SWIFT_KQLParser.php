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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Library\KQL;

use Base\Library\KQL\SWIFT_KQL;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use DateTime;
use DateTimeZone;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\KQL2\SWIFT_KQL2;
use Base\Library\KQL2\SWIFT_KQL2Compiler;
use Base\Library\KQL2\SWIFT_KQL2Parser;
use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL\SWIFT_KQLSchema;

/**
 * The KQL Parser Class
 *
 * This class processes a raw KQL statement into one (or more) SQL statements.
 *
 * @author Varun Shoor
 */
class SWIFT_KQLParser extends SWIFT_KQL
{
    /**
     * This variable contains a list of all table names that are specified by user (does not include auto joins)
     */
    protected $_sqlTableList = array();

    /**
     * This variable contains a list of tables that are supposed to be automatically joined to the query
     */
    protected $_autoJoinTableList = array();
    protected $_autoJoinTableExtendedList = array();
    protected $_customFieldsAliasList = array();

    /**
     * This array contains the list of tables that have been joined
     */
    protected $_joinedTableList = array();

    /**
     * This variable contains a list of SQL Expressions to be automatically added to the query (LEFT JOIN Tablename ON (moo = cow))
     */
    protected $_autoJoinExpressionList = array();

    /**
     * This variable contains a list of SQL Expression fields to be automatically added to the query
     */
    protected $_autoSQLExpressionList = array();

    /**
     * This variable contains a list of Distinct SQL Expression fields to be automatically added to the query
     */
    protected $_autoDistinctSQLExpressionList = array();

    /**
     * This variable contains multigroup fields which need to use ordering specified in KQL
     */
    protected $_autoMultigroupSQLExpressionList = array(); // Same as _returnMultiGroupByFields but with AS <alias>

    /**
     * The Primary Table Name
     */
    protected $_primaryTableName = '';

    /**
     * The Custom Fields
     */
    protected $_customFields = array();

    /**
     * The Custom Fields Related Maps (used for search)
     */
    protected $_customFieldIDMap = array();
    protected $_customFieldNameMap = array();
    protected $_customFieldTitleMap = array();
    protected $_customFieldGroupTitleMap = array();

    /**
     * The Hidden Fields
     */
    protected $_hiddenFields = array();

    /**
     * The Return Fields
     */
    protected $_returnGroupByFields = array();

    /**
     * The Return Fields: X Axis
     */
    protected $_returnGroupByXFields = array();

    /**
     * The Return Fields: For Grouped Tabular Reports
     */
    protected $_returnMultiGroupByFields = array(); // Same as _autoMultigroupSQLExpressionList but without AS <alias>

    /**
     * The Return Fields: Multi Group SQL Statements
     */
    protected $_returnMultiGroupBySQLStatements = array();

    /**
     * The original SQL expressions specified by user
     */
    protected $_returnSQLExpressions = array();

    protected $_distinctValueContainer = array();

    /**
     * Stores Original Aliases
     */
    protected $_originalAliasesMap = array();

    /**
     * Stores Alias to Field Map
     */
    protected $_aliasesFieldsMap = array();

    /**
     * Stores Function Names
     */
    protected $_functionsFieldsMap = array();

    /**
     * @var SWIFT_KQL2Parser
     */
    protected $KQLParser;

    /**
     * @var SWIFT_KQL2 Object
     */
    protected $KQLObject;

    /**
     * @var SWIFT_KQL2Compiler
     */
    protected $KQLCompiler;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->KQLParser = new SWIFT_KQL2Parser();
        $this->KQLCompiler = new SWIFT_KQL2Compiler();
    }

    /**
     * Day Name to Index Map
     */
    static private $_dayNameMap = array(
        'Monday' => 0,
        'Tuesday' => 1,
        'Wednesday' => 2,
        'Thursday' => 3,
        'Friday' => 4,
        'Saturday' => 5,
        'Sunday' => 6
    );

    /**
     * Month Name to Index Map
     */
    static private $_monthNameMap = array(
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

    /**
     * MySQL server time zone (used for adjusting dates for custom fields)
     */
    static protected $_mysqlTimeZone = false;

    /**
     * Parse the KQL to Chunks
     *
     * @author Varun Shoor
     * @param string $_kqlStatement
     * @param string|false $_baseTableName
     * @return mixed The KQL Parser Result Object
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ParseStatement($_kqlStatement, $_baseTableName = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_kqlStatement)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_distinctStatements = false;
        $_sqlStatements = array();
        $_extraStatements = array();

        $this->KQLObject = $this->KQLParser->Parse($_kqlStatement, $_baseTableName);

        $this->KQLCompiler->Compile($this->KQLObject);

        $_compat_distinctValueContainer = array();

        // Distinct statements are returned for grouped tabular (multi-group) and matrix reports
        $_distinctStatements = $this->KQLCompiler->GetDistinctSQL();
        if ($_distinctStatements) {

            // Matrix KQL returns array of distinct statements
            if (is_array($_distinctStatements)) {
                $_sqlStatements = $this->GetMatrixStatements($_distinctStatements, $_compat_distinctValueContainer);

                // Grouped tabular report
            } else {
                $this->_hiddenFields = $this->KQLCompiler->GetHiddenFields();
                $this->_customFieldIDMap = $this->KQLCompiler->GetCustomFields();

                $_sqlStatements = $this->GetGroupedTabularStatements($_distinctStatements);
            }

            // Tabular or summary (grouped) reports
        } else {
            $_sqlStatements[] = $this->KQLCompiler->GetSQL();

            while ($_sequentSQL = $this->KQLCompiler->GetSequentSQL()) {
                $_extraStatements[] = $_sequentSQL;
            }
        }

        $this->_aliasesFieldsMap = $this->KQLCompiler->Compat_GetAliasMap();
        $this->_functionsFieldsMap = $this->KQLCompiler->Compat_GetFunctionMap();
        $this->_originalAliasesMap = $this->KQLCompiler->Compat_GetOriginalAliasMap();
        $this->_hiddenFields = $this->KQLCompiler->GetHiddenFields();
        $this->_customFieldIDMap = $this->KQLCompiler->GetCustomFields();

        // Summary report
        if ($this->KQLObject->IsSummary()) {
            return SWIFT_KQLParserResult::LoadSummary($_sqlStatements, $_extraStatements, $this->KQLCompiler->Compat_ReturnGroupByFields());

            // Matrix report
        } elseif ($this->KQLObject->IsMatrix()) {
            return SWIFT_KQLParserResult::LoadMatrix($_sqlStatements, $_extraStatements, $this->KQLCompiler->Compat_ReturnGroupByFields(), $this->KQLCompiler->Compat_ReturnGroupByXFields(), $_compat_distinctValueContainer);

            // Grouped tabular report
        } elseif ($this->KQLObject->IsGroupedTabular()) {
            return SWIFT_KQLParserResult::LoadGroupedTabular($_sqlStatements, $_extraStatements, $this->KQLCompiler->Compat_ReturnMultiGroupByFields());

            // Tabular report
        } else {
            return SWIFT_KQLParserResult::LoadTabular($_sqlStatements, $_extraStatements);
        }

    }

    /**
     * Executes Distinct Query and Returns Grouped Tabular SQL Queries
     *
     * @author Andriy Lesyuk
     * @param string $_distinctStatement
     * @return array The Array of SQL Statements
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetGroupedTabularStatements($_distinctStatement)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sqlStatements = array();

        $this->Database->Query($_distinctStatement);

        while ($this->Database->NextRecord()) {
            $_distinctValues = array();

            foreach ($this->Database->Record as $_distinctField => $_distinctValue) {
                $_fieldName = explode('_', $_distinctField);

                if ((count($_fieldName) >= 3) && ($_fieldName[1] == 'cf') && isset($this->_customFieldIDMap[$_fieldName[2]])) {
                    if (count($_fieldName) > 3) {
                        continue;
                    }

                    $_isSerialized = $this->Database->Record[$_distinctField . '_isserialized'];
                    $_isEncrypted = $this->Database->Record[$_distinctField . '_isencrypted'];

                    $_distinctValues[] = SWIFT_KQL::GetParsedCustomFieldValue($this->Database->Record[$_distinctField], $this->_customFieldIDMap[$_fieldName[2]], ($_isEncrypted == '1'), ($_isSerialized == '1'));
                } else {
                    $_parsedDistinctValue = $this->GetParsedDistinctValue($_distinctField, $this->Database->Record[$_distinctField]);

                    $_distinctValues[] = $_parsedDistinctValue;
                }
            }

            $_groupStatement = $this->KQLCompiler->GetSQL($this->Database->Record);

            $_extraStatements = array();
            while ($_sequentSQL = $this->KQLCompiler->GetSequentSQL($this->Database->Record)) {
                $_extraStatements[] = $_sequentSQL;
            }
            if (!empty($_extraStatements)) {
                array_unshift($_extraStatements, $_groupStatement);
                $_groupStatement = $_extraStatements;
            }

            $_sqlStatements[implode('_', $_distinctValues)] = $_groupStatement;
        }

        return $_sqlStatements;
    }

    /**
     * Executes Distinct Queries and Returns Matrix SQL Queries
     *
     * @author Andriy Lesyuk
     * @param array $_distinctStatements
     * @param array $_compat_distinctValueContainer
     * @return array The Array of SQL Statements
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetMatrixStatements($_distinctStatements, &$_compat_distinctValueContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_distinctValues = array();

        foreach ($_distinctStatements as $_distinctAlias => $_distinctStatement) {
            $_distinctValues[$_distinctAlias] = array();

            $this->Database->Query($_distinctStatement);

            while ($this->Database->NextRecord()) {
                $_distinctValues[$_distinctAlias][] = $this->Database->Record[$_distinctAlias];
            }
        }

        $_compat_distinctValueContainer = $_distinctValues;

        $_matrixValuesContainer = false;

        // Loop through distinct fields
        foreach ($_distinctValues as $_distinctField => $_distinctValuesContainer) {
            $_matrixValueContainer = array();

            if ($_matrixValuesContainer) {
                // Loop through previous values
                foreach ($_matrixValuesContainer as $_previousFields => $_previousValuesContainer) {
                    // Loop through values of the field
                    foreach ($_distinctValuesContainer as $_distinctValue) {
                        $_previousValuesContainer[$_distinctField] = $_distinctValue;
                        $_matrixValueContainer[$_previousFields . '_' . $_distinctField . ':' . $_distinctValue] = $_previousValuesContainer;
                    }
                }
            } else {
                // Loop through values of the field
                foreach ($_distinctValuesContainer as $_distinctValue) {
                    $_matrixValueContainer[$_distinctField . ':' . $_distinctValue] = array($_distinctField => $_distinctValue);
                }
            }

            $_matrixValuesContainer = $_matrixValueContainer;
        }

        $_sqlStatements = array();

        foreach ($_matrixValuesContainer as $_distinctFieldsID => $_distinctValuesContainer) {
            $_matrixStatement = $this->KQLCompiler->GetSQL($_distinctValuesContainer);

            $_extraStatements = array();
            while ($_sequentSQL = $this->KQLCompiler->GetSequentSQL($_distinctValuesContainer)) {
                $_extraStatements[] = $_sequentSQL;
            }
            if (!empty($_extraStatements)) {
                array_unshift($_extraStatements, $_matrixStatement);
                $_matrixStatement = $_extraStatements;
            }

            $_sqlStatements[$_distinctFieldsID] = $_matrixStatement;
        }

        return $_sqlStatements;
    }

    /**
     * Parse the SQL Chunks
     *
     * @author Varun Shoor
     * @param array $_chunksContainer
     * @return mixed
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ParseSQLChunks($_chunksContainer) // FIXME: Full review and compatibility check needed
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_chunksContainer['command']) || $_chunksContainer['command'] != 'select') {
            throw new SWIFT_Exception('Not a valid SELECT Statement');
        }

        if (!isset($_chunksContainer['from'])) {
            throw new SWIFT_Exception('No Source Table Specified');
        }

        $_sqlExpressions = $_sqlTableList = $_sqlJoinList = $_sqlWhereClauses = $_sqlWhereClausesExtended = array();
        $_sqlJoinTableNameList = array();
        $_sqlPrimaryTable = '';

        /**
         * ---------------------------------------------
         * Tables
         * ---------------------------------------------
         */
        foreach ($_chunksContainer['from']['table_references']['table_factors'] as $_tableList) {
            // We have three probable table names:
            // 1) Actual one supplied
            // 2) Language Key
            // 3) Alias
            $_tableName = mb_strtolower($_tableList['table']);
            $_tableAlias = mb_strtolower($_tableList['alias']);
            $_tableLabelResult = $this->GetTableNameOnLabel($_tableName);

            $_finalTableName = false;

            // Do we have a table for this?
            if (isset($this->_schemaContainer[$_tableName])) {
                $_finalTableName = $this->Database->Escape(Clean($_tableName));
            } elseif (!empty($_tableLabelResult)) {
                $_finalTableName = $this->Database->Escape(Clean($_tableLabelResult));
            } elseif (!empty($_tableAlias) && isset($this->_schemaContainer[$_tableAlias])) {
                $_finalTableName = $this->Database->Escape(Clean($_tableAlias));
            } else {
                throw new SWIFT_Exception('Table not found: ' . $_tableName);
            }

            $_sqlTableList[] = $_finalTableName;

            if (isset($this->_schemaContainer[$_finalTableName][SWIFT_KQLSchema::SCHEMA_AUTOJOIN])) {
                foreach ($this->_schemaContainer[$_finalTableName][SWIFT_KQLSchema::SCHEMA_AUTOJOIN] as $_autoJoinTableName) {
                    if (!in_array($_autoJoinTableName, $this->_autoJoinTableList)) {
                        $this->_autoJoinTableList[] = $_autoJoinTableName;
                        $this->_autoJoinTableExtendedList[$_autoJoinTableName] = $_finalTableName;
                    }
                }
            }
        }

        $this->_sqlTableList = $_sqlTableList;

        // Update Primary Table
        $_sqlPrimaryTable = $_sqlTableList[0];
        $this->_primaryTableName = $_sqlPrimaryTable;


        /**
         * ---------------------------------------------
         * Prepare LEFT JOIN's
         * ---------------------------------------------
         */
        foreach ($_sqlTableList as $_key => $_tableName) {
            // Move on if table is primary
            if ($_key == 0) {
                continue;
            }

            // Make sure the tables are related
            if (!isset($this->_schemaContainer[$_sqlPrimaryTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                throw new SWIFT_Exception($_sqlPrimaryTable . ' is not related to ' . $_tableName);
            }

            $this->_joinedTableList[] = $_tableName;

            $_joinStatement = '';

            // We have an extended where processing for related tables in place
            if (_is_array($this->_schemaContainer[$_sqlPrimaryTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                $_joinStatement = $this->_schemaContainer[$_sqlPrimaryTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName][0];
                $_sqlWhereClausesExtended[] = $this->_schemaContainer[$_sqlPrimaryTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName][1];
            } else {
                $_joinStatement = $this->_schemaContainer[$_sqlPrimaryTable][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName];
            }

            $_sqlJoinList[] = TABLE_PREFIX . $_tableName . ' AS ' . $_tableName . ' ON (' . $_joinStatement . ')';
        }


        /**
         * ---------------------------------------------
         * SQL Expressions
         * ---------------------------------------------
         */
        if (isset($_chunksContainer['select_expressions']) && _is_array($_chunksContainer['select_expressions'])) {
            foreach ($_chunksContainer['select_expressions'] as $_argsContainer) {
                if (!isset($_argsContainer['args']) || !_is_array($_argsContainer['args'])) {
                    continue;
                }

                $_argumentDepth = 0;
                $_sqlExpressions = array_merge($_sqlExpressions, $this->ProcessParentArguments($_argsContainer, $_sqlPrimaryTable, $_sqlTableList, $_argumentDepth));
            }
        }


        /**
         * ---------------------------------------------
         * Where Clause
         * ---------------------------------------------
         */
        if (isset($_chunksContainer['where_clause']) && _is_array($_chunksContainer['where_clause'])) {
            if (isset($_chunksContainer['where_clause']['args'])) {
                $_argumentDepth = 0;
                $_sqlWhereClauses = array_merge($_sqlWhereClauses, $this->ProcessWhereArguments($_chunksContainer['where_clause'], $_sqlPrimaryTable, $_sqlTableList, $_argumentDepth));
            }

            foreach ($_chunksContainer['where_clause'] as $_argsContainer) {
                if (!isset($_argsContainer['args']) || !_is_array($_argsContainer['args'])) {
                    continue;
                }

                $_argumentDepth = 0;
                $_sqlWhereClauses = array_merge($_sqlWhereClauses, $this->ProcessWhereArguments($_argsContainer, $_sqlPrimaryTable, $_sqlTableList, $_argumentDepth));
            }
        }


        /**
         * ---------------------------------------------
         * Group
         * ---------------------------------------------
         */
        $_sqlGroupByExpressions = array();

        if (isset($_chunksContainer['group_by']) && _is_array($_chunksContainer['group_by'])) {
            foreach ($_chunksContainer['group_by'] as $_groupByContainer) {
                $_baseGroupByExpression = $this->ProcessGroupBy($_sqlPrimaryTable, $_sqlTableList, $_groupByContainer[0], $_groupByContainer[1]);
                if (!empty($_baseGroupByExpression)) {
                    $_sqlGroupByExpressions[] = $_baseGroupByExpression;
                }
            }
        }


        /**
         * ---------------------------------------------
         * MultiGroup
         * ---------------------------------------------
         */

        if (isset($_chunksContainer['multigroup_by']) && _is_array($_chunksContainer['multigroup_by'])) {
            foreach ($_chunksContainer['multigroup_by'] as $_multiGroupByContainer) {
                $_baseMultiGroupByExpression = $this->ProcessMultiGroupBy($_sqlPrimaryTable, $_sqlTableList, $_multiGroupByContainer[0], $_multiGroupByContainer[1]);
                if (!empty($_baseMultiGroupByExpression)) {
                    $_sqlGroupByExpressions[] = $_baseMultiGroupByExpression;
                }
            }
        }

        /**
         * ---------------------------------------------
         * Order
         * ---------------------------------------------
         */
        $_sqlSortOrderExpressions = array();

        if (isset($_chunksContainer['sort_order']) && _is_array($_chunksContainer['sort_order'])) {
            foreach ($_chunksContainer['sort_order'] as $_tableCol) {
                $_sqlSortOrderExpressions[] = $this->ProcessSortOrder($_sqlPrimaryTable, $_sqlTableList, $_tableCol);
            }
        }


        /**
         * ---------------------------------------------
         * Prepare *automatic* LEFT JOIN's
         * ---------------------------------------------
         */
        foreach ($this->_autoJoinTableList as $_tableName) {
            // If the table is already joined up, then  move on
            if (in_array($_tableName, $_sqlTableList) || in_array($_tableName, $this->_joinedTableList)) {
                continue;
            }

            $_joinParentTableName = $_sqlPrimaryTable;

            // If we are supposed to join on a different table, then check
            if (isset($this->_autoJoinTableExtendedList[$_tableName])) {
                $_joinParentTableName = $this->_autoJoinTableExtendedList[$_tableName];
            }

            // Make sure the tables are related
            if (!isset($this->_schemaContainer[$_joinParentTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                throw new SWIFT_Exception($_joinParentTableName . ' is not related to ' . $_tableName);
            }

            $this->_joinedTableList[] = $_tableName;

            $_joinStatement = '';

            // We have an extended where processing for related tables in place
            if (_is_array($this->_schemaContainer[$_joinParentTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName])) {
                $_joinStatement = $this->_schemaContainer[$_joinParentTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName][0];
                $_sqlWhereClausesExtended[] = $this->_schemaContainer[$_joinParentTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName][1];
            } else {
                $_joinStatement = $this->_schemaContainer[$_joinParentTableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES][$_tableName];
            }

            $_sqlJoinList[] = TABLE_PREFIX . $_tableName . ' AS ' . $_tableName . ' ON (' . $_joinStatement . ')';
        }

        $_sqlJoinList = array_merge($_sqlJoinList, $this->_autoJoinExpressionList);


        /**
         * ---------------------------------------------
         * Do final processing on where clauses
         * ---------------------------------------------
         */
        if (count($_sqlWhereClausesExtended)) {
            foreach ($_sqlWhereClausesExtended as $_whereClauseText) {
                $_opsPrefix = '';
                if (count($_sqlWhereClauses)) {
                    $_opsPrefix = ' AND ';
                }

                $_sqlWhereClauses[] = $_opsPrefix . $_whereClauseText;
            }
        }


        /**
         * ---------------------------------------------
         * Construct Final SQL Statements
         * ---------------------------------------------
         */

        // Load up the distinct values?
        if (count($this->_autoDistinctSQLExpressionList)) {
            $_distinctValueMap = array();

            $_statementSuffixes = '';
            if (count($_sqlJoinList)) {
                $_statementSuffixes .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $_sqlJoinList);
            }

            if (count($_sqlWhereClauses)) {
                $_statementSuffixes .= ' WHERE ' . implode('', $_sqlWhereClauses);
            }

            foreach ($this->_autoDistinctSQLExpressionList as $_fieldNameReference => $_distinctField) {
                $_fieldNames = explode('_', $_fieldNameReference);

                $_sqlStatement = 'SELECT ' . 'DISTINCT ' . $_distinctField . ' FROM ' . TABLE_PREFIX . $_sqlPrimaryTable . ' AS ' . $_sqlPrimaryTable . $_statementSuffixes;

                $this->Database->Query($_sqlStatement);
                while ($this->Database->NextRecord()) {
                    if (!isset($_distinctValueMap[$_fieldNameReference])) {
                        $_distinctValueMap[$_fieldNameReference] = array();
                    }

                    $_distinctValue = $this->Database->Record[$_fieldNameReference];

                    if ((count($_fieldNames) >= 3) && ($_fieldNames[1] == 'cf') && isset($this->_customFieldIDMap[$_fieldNames[2]])) {
                        $_isSerialized = $this->Database->Record[$_fieldNameReference . '_isserialized'];
                        $_isEncrypted = $this->Database->Record[$_fieldNameReference . '_isencrypted'];

                        $_parsedValue = SWIFT_KQL::GetParsedCustomFieldValue($_distinctValue, $this->_customFieldIDMap[$_fieldNames[2]], ($_isEncrypted == '1'), ($_isSerialized == '1'));

                        $_distinctValue = array($_distinctValue, $_parsedValue);
                    }

                    $_distinctValueMap[$_fieldNameReference][] = $_distinctValue;
                }

                if (isset($_distinctValueMap[$_fieldNameReference]) && _is_array($_distinctValueMap[$_fieldNameReference])) {
                    $_sorted = false;

                    /**
                     * BUG FIX: Andriy Lesyuk
                     *
                     * SWIFT-1858: Column and label sorting issues
                     *
                     * Comments: Day names and month names distinct values are sorted in a special way
                     */
                    $_count = count($_fieldNames);
                    if (($_count >= 2) &&
                        isset($this->_schemaContainer[$_fieldNames[$_count - 2]]) &&
                        isset($this->_schemaContainer[$_fieldNames[$_count - 2]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldNames[$_count - 1]]) &&
                        ($this->_schemaContainer[$_fieldNames[$_count - 2]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldNames[$_count - 1]][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME)) {

                        switch ($_fieldNames[0]) {
                            case 'dayname':
                                usort($_distinctValueMap[$_fieldNameReference], array($this, '_SortDayNames'));
                                $_sorted = true;
                                break;

                            case 'monthname':
                                usort($_distinctValueMap[$_fieldNameReference], array($this, '_SortMonthNames'));
                                $_sorted = true;
                                break;

                            default:
                                break;
                        }
                    }

                    if ($_sorted == false) {
                        sort($_distinctValueMap[$_fieldNameReference]);
                    }
                }
            }

            $this->_distinctValueContainer = $_distinctValueMap;
        }


        /**
         * ---------------------------------------------
         * MULTIGROUP STATEMENT
         * ---------------------------------------------
         */

        /**
         * BUG FIX: Andriy Lesyuk
         *
         * SWIFT-1879: MULTIGROUP BY x ORDER BY y DESC does not affect multigroup ordering
         *
         * Comments: Completely refactored the way multi-group fields are handled
         */

        // Load multi-group values
        if (count($this->_autoMultigroupSQLExpressionList) && _is_array($this->_returnMultiGroupByFields)) {

            $_sqlStatement = 'SELECT ' . 'DISTINCT ' . implode(', ', $this->_autoMultigroupSQLExpressionList) . ' FROM ' . TABLE_PREFIX . $_sqlPrimaryTable . ' AS ' . $_sqlPrimaryTable;

            if (count($_sqlJoinList)) {
                $_sqlStatement .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $_sqlJoinList);
            }

            if (count($_sqlWhereClauses)) {
                $_sqlStatement .= ' WHERE ' . implode('', $_sqlWhereClauses);
            }

            if (count($_sqlSortOrderExpressions)) {
                $_sqlStatement .= ' ORDER BY ' . implode(', ', $_sqlSortOrderExpressions);
            }

            $_sqlExpressions = array_merge($_sqlExpressions, $this->_autoSQLExpressionList, $this->_autoMultigroupSQLExpressionList);

            $_sqlTemplate = 'SELECT ' . implode(', ', $_sqlExpressions) . ' FROM ' . TABLE_PREFIX . $_sqlPrimaryTable . ' AS ' . $_sqlPrimaryTable;

            if (count($_sqlJoinList)) {
                $_sqlTemplate .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $_sqlJoinList);
            }

            if (count($_sqlWhereClauses)) {
                $_sqlTemplate .= ' WHERE ' . implode('', $_sqlWhereClauses) . ' AND %extendedmultiwhereclause%';
            } else {
                $_sqlTemplate .= ' WHERE %extendedmultiwhereclause%';
            }

            if (count($_sqlSortOrderExpressions)) {
                $_sqlTemplate .= ' ORDER BY ' . implode(', ', $_sqlSortOrderExpressions);
            }

            if (isset($_chunksContainer['limit_clause']) && _is_array($_chunksContainer['limit_clause'])) {
                $_sqlTemplate .= ' LIMIT ' . (int)($_chunksContainer['limit_clause']['start']) . ', ' . (int)($_chunksContainer['limit_clause']['length']);
            }

            $_finalSQLStatementList = array();

            $this->Database->Query($_sqlStatement);
            while ($this->Database->NextRecord()) {
                $_multiWhereClause = array();
                $_distinctValues = array();

                foreach ($this->_returnMultiGroupByFields as $_multiGroupByField) {
                    if ($this->Database->Record[$_multiGroupByField[1]] !== NULL) {
                        $_multiWhereClause[] = $_multiGroupByField[2] . " = '" . $this->Database->Escape($this->Database->Record[$_multiGroupByField[1]]) . "'";
                    } else {
                        $_multiWhereClause[] = $_multiGroupByField[2] . " IS NULL";
                    }

                    $_fieldNames = explode('_', $_multiGroupByField[1]);

                    if ((count($_fieldNames) >= 3) && ($_fieldNames[1] == 'cf') && isset($this->_customFieldIDMap[$_fieldNames[2]])) {
                        $_isSerialized = $this->Database->Record[$_multiGroupByField[1] . '_isserialized'];
                        $_isEncrypted = $this->Database->Record[$_multiGroupByField[1] . '_isencrypted'];

                        $_distinctValues[] = SWIFT_KQL::GetParsedCustomFieldValue($this->Database->Record[$_multiGroupByField[1]], $this->_customFieldIDMap[$_fieldNames[2]], ($_isEncrypted == '1'), ($_isSerialized == '1'));
                    } else {
                        $_distinctValues[] = $this->GetParsedDistinctValue($_multiGroupByField[1], $this->Database->Record[$_multiGroupByField[1]]);
                    }
                }

                $_statementTitle = implode('_', $_distinctValues);
                $_finalSQLStatementList[$_statementTitle] = str_replace('%extendedmultiwhereclause%', implode(" AND ", $_multiWhereClause), $_sqlTemplate);
            }

            return $_finalSQLStatementList;


            /**
             * ---------------------------------------------
             * MATRIX STATEMENT
             * ---------------------------------------------
             */
        } elseif (_is_array($this->_returnGroupByXFields)) {
            $_combinedWhereClauseList = $_fieldWhereClauseList = $_finalMatrixWhereClauseList = $_activeMatrixWhereClauseList = array();

            $_isFirst = true;

            $_totalMatrixFields = count($this->_returnGroupByXFields);

            foreach ($this->_returnGroupByXFields as $_index => $_groupByXField) {
                if (!isset($this->_distinctValueContainer[$_groupByXField[1]])) {
                    continue;
                }
                foreach ($this->_distinctValueContainer[$_groupByXField[1]] as $_distinctValue) {
                    if (is_array($_distinctValue)) {
                        $_distinctValue = $_distinctValue[0];
                    }

                    $_matrixWhereComparison = $_groupByXField[2] . " = '" . $this->Database->Escape($_distinctValue) . "'";
                    if ($_distinctValue === NULL) {
                        $_matrixWhereComparison = $_groupByXField[2] . " IS NULL";
                    }

                    if ($_isFirst) {
                        $_fieldWhereClauseList[$_groupByXField[1] . ':' . $_distinctValue] = $_matrixWhereComparison;
                    } else {
                        foreach ($_activeMatrixWhereClauseList[$_index - 1] as $_key => $_val) {
                            $_fieldWhereClauseList[$_key . '_' . $_groupByXField[1] . ':' . $_distinctValue] = $_val . " AND " . $_matrixWhereComparison;
                        }
                    }
                }

                $_activeMatrixWhereClauseList[$_index] = $_fieldWhereClauseList;
                $_combinedWhereClauseList = array_merge($_combinedWhereClauseList, $_fieldWhereClauseList);
                $_fieldWhereClauseList = array();
                $_isFirst = false;
            }

            if (isset($_activeMatrixWhereClauseList[$_totalMatrixFields - 1])) {
                $_finalMatrixWhereClauseList = $_activeMatrixWhereClauseList[$_totalMatrixFields - 1];
            }

            ksort($_finalMatrixWhereClauseList, SORT_STRING);

            $_finalSQLStatementList = array();
            $_sqlStatement = '';

            $_sqlExpressions = array_merge($_sqlExpressions, $this->_autoSQLExpressionList, $this->_autoDistinctSQLExpressionList);

            $_sqlStatement = 'SELECT ' . implode(', ', $_sqlExpressions) . ' FROM ' . TABLE_PREFIX . $_sqlPrimaryTable . ' AS ' . $_sqlPrimaryTable;

            if (count($_sqlJoinList)) {
                $_sqlStatement .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $_sqlJoinList);
            }

            if (count($_sqlWhereClauses)) {
                $_sqlStatement .= ' WHERE ' . implode('', $_sqlWhereClauses);
                if (_is_array($_finalMatrixWhereClauseList)) {
                    $_sqlStatement .= ' AND %extendedmatrixwhereclause%';
                }

                // Always add WHERE clause
            } else {
                if (_is_array($_finalMatrixWhereClauseList)) {
                    $_sqlStatement .= ' WHERE %extendedmatrixwhereclause%';
                }
            }

            if (count($_sqlGroupByExpressions)) {
                $_sqlStatement .= ' GROUP BY ' . implode(', ', $_sqlGroupByExpressions);
            }

            if (count($_sqlSortOrderExpressions)) {
                $_sqlStatement .= ' ORDER BY ' . implode(', ', $_sqlSortOrderExpressions);
            }

            if (isset($_chunksContainer['limit_clause']) && _is_array($_chunksContainer['limit_clause'])) {
                $_sqlStatement .= ' LIMIT ' . (int)($_chunksContainer['limit_clause']['start']) . ', ' . (int)($_chunksContainer['limit_clause']['length']);
            }

            if (_is_array($_finalMatrixWhereClauseList)) {
                foreach ($_finalMatrixWhereClauseList as $_statementTitle => $_extendedMatrixWhereClause) {
                    $_finalSQLStatementList[$_statementTitle] = str_replace('%extendedmatrixwhereclause%', $_extendedMatrixWhereClause, $_sqlStatement);
                }

                return $_finalSQLStatementList;
            } else {
                return $_sqlStatement;
            }

        }


        /**
         * ---------------------------------------------
         * BASIC STATEMENT
         * ---------------------------------------------
         */
        $_sqlStatement = '';

        $_sqlExpressions = array_merge($_sqlExpressions, $this->_autoSQLExpressionList);

        $_sqlStatement = 'SELECT ' . implode(', ', $_sqlExpressions) . ' FROM ' . TABLE_PREFIX . $_sqlPrimaryTable . ' AS ' . $_sqlPrimaryTable;

        if (count($_sqlJoinList)) {
            $_sqlStatement .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $_sqlJoinList);
        }

        if (count($_sqlWhereClauses)) {
            $_sqlStatement .= ' WHERE ' . implode('', $_sqlWhereClauses);
        }

        if (count($_sqlGroupByExpressions)) {
            $_sqlStatement .= ' GROUP BY ' . implode(', ', $_sqlGroupByExpressions);
        }

        if (count($_sqlSortOrderExpressions)) {
            $_sqlStatement .= ' ORDER BY ' . implode(', ', $_sqlSortOrderExpressions);
        }

        if (isset($_chunksContainer['limit_clause']) && _is_array($_chunksContainer['limit_clause'])) {
            $_sqlStatement .= ' LIMIT ' . (int)($_chunksContainer['limit_clause']['start']) . ', ' . (int)($_chunksContainer['limit_clause']['length']);
        }

        return $_sqlStatement;
    }

    /**
     * Saves Fields Properties for Later Use
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function StoreFieldsProperties($_sqlExpressions)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_sqlExpressions)) {
            return false;
        }

        foreach ($_sqlExpressions as $_sqlExpression) {
            $_aliasName = false;
            $_fieldName = false;
            $_functionName = false;
            $_fieldExpression = false;

            $_fieldExpressions = explode(' AS ', $_sqlExpression);
            if (count($_fieldExpressions) <= 2) {
                if (isset($_fieldExpressions[1])) {
                    $_aliasName = $_fieldExpressions[1];
                }
                $_fieldExpression = $_fieldExpressions[0];
            } else {
                $_fieldExpression = $_sqlExpression;
            }

            if (preg_match('/^([a-z0-9]+)\(([^\(\)]*)\)$/i', $_fieldExpression, $_functionMatches)) {
                $_functionName = $_functionMatches[1];
                $_fieldName = $_functionMatches[2];
            } else {
                $_fieldName = $_fieldExpression;
            }

            // Save real field name
            if ($_aliasName) {
                $this->_aliasesFieldsMap[$_aliasName] = $_fieldName;
            }

            // Save function
            if ($_functionName) {
                if ($_aliasName) {
                    $_fieldExpression = $_aliasName;
                }
                $this->_functionsFieldsMap[$_fieldExpression] = array($_functionName, $_fieldName);
            }
        }

        return true;
    }

    /**
     * Process Parent Arguments
     *
     * @author Varun Shoor
     * @param array $_argsContainer
     * @param string $_sqlPrimaryTable
     * @param array $_sqlTableList
     * @return array SQL Expression Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessParentArguments($_argsContainer, $_sqlPrimaryTable, $_sqlTableList, $_argumentDepth)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sqlExpressions = array();

        foreach ($_argsContainer['args'] as $_key => $_argument) {
            $_aliasName = '';
            if (isset($_argsContainer['alias']) && $_argumentDepth == 0) {
                $_aliasName = $this->Database->Escape(Clean($_argsContainer['alias']));

                $this->_originalAliasesMap[$_aliasName] = $_argsContainer['alias'];
            }

            $_opsPrefix = '';
            if (isset($_argsContainer['ops'])) {
                $_opsHandle = $_key - 1;
                if (isset($_argsContainer['ops'][$_opsHandle])) {
                    $_opsPrefix = ' ' . $_argsContainer['ops'][$_opsHandle] . ' ';
                }
            }

            // Whenever the select expression is enclosed in quotes
            // 'Tickets.Ticket ID' OR 'tickets.ticketid'
            if (isset($_argument['value']) && isset($_argument['type']) && $_argument['type'] == 'text_val') {
                if (!strpos($_argument['value'], '.')) {
                    $_sqlExpression = $this->GetExpressionFromTableNameAndColumn($_sqlPrimaryTable, $_argument['value'], $_sqlTableList, $_aliasName, false);
                } else {
                    $_sqlExpression = $this->GetExpressionFromTableNameAndColumn(substr($_argument['value'], 0, strpos($_argument['value'], '.')), substr($_argument['value'], strpos($_argument['value'], '.') + 1), $_sqlTableList, $_aliasName, false);
                }
                $_sqlExpressions[] = $_opsPrefix . $_sqlExpression;

                /**
                 * BUG FIX - Andriy Lesyuk
                 *
                 * SWIFT-2426 While generating a report to display ticket post contents, it should not display HTML tags
                 *
                 */
                if (empty($_opsPrefix) && $_argumentDepth == 0) {
                    list($_columnName, $_columnAlias) = explode(' AS ', $_sqlExpression);

                    if ($_columnName == 'ticketposts.contents' && !empty($_columnAlias)) {
                        $_sqlExpressions[] = 'ticketposts.ishtml AS ' . $_columnAlias . '_ishtml';

                        $this->_hiddenFields[$_columnAlias . '_ishtml'] = 'ticketposts.ishtml';
                    }
                }

            } elseif (isset($_argument['value']) && isset($_argument['type']) && $_argument['type'] == 'int_val') {
                $_sqlExpressions[] = $_opsPrefix . floatval($_argument['value']);

                // Complete Fetch
                // SELECT *
            } elseif (isset($_argument['value']) && isset($_argument['type']) && $_argument['type'] == '*') {
                throw new SWIFT_Exception('Cannot retrieve via *, please specify an exact field name.');

                $_sqlExpressions[] = $_opsPrefix . $_sqlPrimaryTable . '.*';

                // Basic Column Expression
                // ticketid OR tickets.ticketid, tickets.*
                // Contains table, column & alias
            } elseif (isset($_argument['column']) && !in_array(mb_strtolower($_argument['column']), self::$_disallowedColumns)) {
                if (!empty($_argument['table'])) {
                    $_sqlExpressions[] = $_opsPrefix . $this->GetExpressionFromTableNameAndColumn($_argument['table'], $_argument['column'], $_sqlTableList, $_aliasName);
                } else {
                    $_sqlExpressions[] = $_opsPrefix . $_argument['column'];

                    // Commented to support work on aliases like Open+InProgress+Closed
//                    $_sqlExpressions[] = $_opsPrefix . $this->GetExpressionFromTableNameAndColumn($_sqlPrimaryTable, $_argument['column'], $_sqlTableList, $_aliasName);
                }

                // Select Expression with Function Call
                // COUNT(tickets.ticketid) or COUNT(ticketid)
                // Can contain alias for expressions like: COUNT(tickets.ticketid) AS ticketid
            } elseif (isset($_argument['name']) && isset($_argument['arg'])) {
                $_nestedArguments = array();
                if (isset($_argument['args'])) {
                    $_nestedArguments = $_argument['args'];
                }

                $_functionExpression = $this->GetExpressionFromFunction($_argument['name'], $_argument['arg'], $_nestedArguments, $_sqlTableList, $_sqlPrimaryTable, $_aliasName);
                if ($_functionExpression) {
                    $_sqlExpressions[] = $_opsPrefix . $_functionExpression;
                }
            } elseif (isset($_argument['args'])) {
                $_expressionSuffix = '';
                if (isset($_argument['alias']) && !empty($_argument['alias'])) {
                    $_expressionSuffix = ' AS ' . $this->Database->Escape(Clean($_argument['alias']));
                }

                $_ops = '';
                if (!isset($_argument['ops'])) {
                    $_ops = ', ';
                }

                $_sqlExpressions[] = '(' . implode($_ops, $this->ProcessParentArguments($_argument, $_sqlPrimaryTable, $_sqlTableList, ($_argumentDepth + 1))) . ')' . $_expressionSuffix;
            }
        }

        // Save fields properties for charts
        $this->StoreFieldsProperties($_sqlExpressions);

        return $_sqlExpressions;
    }

    /**
     * Process Arguments for Where Clause
     *
     * @author Varun Shoor
     * @param array $_argsContainer
     * @param string $_sqlPrimaryTable
     * @param array $_sqlTableList
     * @return array SQL Expression Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessWhereArguments($_argsContainer, $_sqlPrimaryTable, $_sqlTableList, $_argumentDepth)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_whereExpressions = array();

        foreach ($_argsContainer['args'] as $_key => $_argument) {
            $_opsPrefix = $_operator = '';

            $_activeTableName = $_activeColumnName = '';
            if (isset($_argsContainer['ops'])) {
                $_opsHandle = $_key - 1;
                if (isset($_argsContainer['ops'][$_opsHandle])) {
                    $_opsPrefix = ' ' . trim(mb_strtoupper($_argsContainer['ops'][$_opsHandle])) . ' ';
                    $_operator = trim(mb_strtolower($_argsContainer['ops'][$_opsHandle]));
                }
            }

            // Whenever the where expression is enclosed in quotes
            // 'Tickets.Ticket ID' OR 'tickets.ticketid'
            if (isset($_argument['value']) && isset($_argument['type']) && $_argument['type'] == 'text_val') {

                // Is it a function?
                $_functionMatches = array();
                if (preg_match('/^(.*)\((.*)?\)$/i', $_argument['value'], $_functionMatches)) {
                    $_whereExpressionResult = $this->GetWhereExpressionFromFunction(mb_strtolower($_functionMatches[1]), self::ParseArgumentIntoArray($_functionMatches[2]), $_sqlTableList, $_sqlPrimaryTable, $_opsPrefix);
                    if (_is_array($_whereExpressionResult)) {
                        $_whereExpressions[] = ' ' . $_whereExpressionResult[0] . ' ' . $_whereExpressionResult[1];
                    } else {
                        $_whereExpressions[] = $_opsPrefix . $_whereExpressionResult;
                    }

                } else {
                    // Is it a valid operator?
                    if (in_array($_operator, self::$_allowedBasicOperators) || in_array($_operator, self::$_allowedTextOperators)) {
                        // 'tickets.field' = 'Some value'
                        if (isset($_argsContainer['args'][$_key - 1]['value']) && $_argsContainer['args'][$_key - 1]['type'] == 'text_val') {
                            $_activeTableContainer = $this->GetTableAndFieldNameOnText($_argsContainer['args'][$_key - 1]['value']);
                            if (isset($_activeTableContainer[0]) && !empty($_activeTableContainer[0])) {
                                $_activeTableName = $_activeTableContainer[0];
                                $_activeColumnName = $_activeTableContainer[1];
                            }

                            $_whereExpressions[] = $_opsPrefix . $this->GetWhereValue($_argument['value'], $_activeTableName, $_activeColumnName, $_sqlTableList, $_operator);
                            // CUSTOMFIELD('Tickets', 'Some custom field') = 'Some value'
                        } elseif (isset($_argsContainer['args'][$_key - 1]['name']) && isset($_argsContainer['args'][$_key - 1]['arg'])) {
                            $_customFieldValue = false;

                            if (mb_strtolower($_argsContainer['args'][$_key - 1]['name']) == 'customfield') {
                                $_customField = $this->GetCustomField($_argsContainer['args'][$_key - 1]['arg'], $_sqlPrimaryTable);
                                if ($_customField && isset($_customField['id'])) {
                                    $_customFieldValue = $this->GetCustomFieldValue($_argument['value'], $_customField, trim($_opsPrefix));
                                }
                            }

                            if ($_customFieldValue) {
                                $_whereExpressions[] = $_customFieldValue; // contains $_opsPrefix
                            } else {
                                $_whereExpressions[] = $_opsPrefix . $this->GetWhereValue($_argument['value'], $_activeTableName, $_activeColumnName, $_sqlTableList, $_operator);
                            }
                        }

                        // This is a column then
                    } else {
                        if (!strpos($_argument['value'], '.')) {
                            $_whereExpressions[] = $_opsPrefix . $this->GetWhereExpressionFromTableNameAndColumn($_sqlPrimaryTable, $_argument['value'], $_sqlTableList, false, false);
                        } else {
                            $_whereExpressions[] = $_opsPrefix . $this->GetWhereExpressionFromTableNameAndColumn(substr($_argument['value'], 0, strpos($_argument['value'], '.')), substr($_argument['value'], strpos($_argument['value'], '.') + 1), $_sqlTableList, false);
                        }
                    }

                }


                // Integer Value
            } elseif (isset($_argument['value']) && isset($_argument['type']) && $_argument['type'] == 'int_val') {
                $_whereExpressions[] = $_opsPrefix . floatval($_argument['value']);

                // Multiple Values: IN, NOT IN
            } elseif (isset($_argument['values']) && isset($_argument['types'])) {
                if (isset($_argsContainer['args'][$_key - 1]['value']) && $_argsContainer['args'][$_key - 1]['type'] == 'text_val') {
                    $_activeTableContainer = $this->GetTableAndFieldNameOnText($_argsContainer['args'][$_key - 1]['value']);
                    if (isset($_activeTableContainer[0]) && !empty($_activeTableContainer[0])) {
                        $_activeTableName = $_activeTableContainer[0];
                        $_activeColumnName = $_activeTableContainer[1];
                    }
                }

                $_customFieldValues = false;
                if (isset($_argsContainer['args'][$_key - 1]['name']) && isset($_argsContainer['args'][$_key - 1]['arg'])) {
                    if (mb_strtolower($_argsContainer['args'][$_key - 1]['name']) == 'customfield') {
                        $_customField = $this->GetCustomField($_argsContainer['args'][$_key - 1]['arg'], $_sqlPrimaryTable);
                        if ($_customField && isset($_customField['id'])) {
                            $_customFieldValues = $this->GetCustomFieldMultipleValues($_argument, $_opsPrefix, $_customField);
                        }
                    }
                }

                if ($_customFieldValues) {
                    if (is_array($_customFieldValues)) {
                        $_customFieldExpressions = array();
                        $_previousExpression = array_pop($_whereExpressions);
                        $_joinOperator = array_pop($_customFieldValues);
                        foreach ($_customFieldValues as $_customFieldValue) {
                            $_customFieldExpressions[] = $_previousExpression . $_customFieldValue;
                        }
                        $_whereExpressions[] = '(' . implode(' ' . $_joinOperator . ' ', $_customFieldExpressions) . ')';
                    } else {
                        $_whereExpressions[] = $_customFieldValues;
                    }
                } else {
                    $_whereExpressions[] = $this->GetWhereExpressionForMultipleValues($_argument, $_opsPrefix, $_activeTableName, $_activeColumnName, $_operator);
                }

                // Basic Column Expression
                // ticketid OR tickets.ticketid, tickets.*
                // Contains table, column & alias
            } elseif (isset($_argument['column'])) {
                if (!empty($_argument['table'])) {
                    $_whereExpressions[] = $_opsPrefix . $this->GetWhereExpressionFromTableNameAndColumn($_argument['table'], $_argument['column'], $_sqlTableList, false);
                } else {
                    $_whereExpressions[] = $_opsPrefix . $this->GetWhereExpressionFromTableNameAndColumn($_sqlPrimaryTable, $_argument['column'], $_sqlTableList, false);
                }

                // Select Expression with Function Call
                // COUNT(tickets.ticketid) or COUNT(ticketid)
                // Can contain alias for expressions like: COUNT(tickets.ticketid) AS ticketid
            } elseif (isset($_argument['name']) && isset($_argument['arg'])) {
                $_whereExpressionResult = $this->GetWhereExpressionFromFunction($_argument['name'], $_argument['arg'], $_sqlTableList, $_sqlPrimaryTable, $_opsPrefix);
                if (_is_array($_whereExpressionResult)) {
                    $_whereExpressions[] = ' ' . $_whereExpressionResult[0] . ' ' . $_whereExpressionResult[1];
                } else {
                    $_whereExpressions[] = $_opsPrefix . $_whereExpressionResult;
                }

            } elseif (isset($_argument['args'])) {
                $_ops = '';
                if (!isset($_argument['ops'])) {
                    $_ops = ' AND ';
                }

                $_whereExpressions[] = $_opsPrefix . '(' . implode($_ops, $this->ProcessWhereArguments($_argument, $_sqlPrimaryTable, $_sqlTableList, ($_argumentDepth + 1))) . ')';
            }
        }

        return $_whereExpressions;
    }

    /**
     * Get the WHERE value
     *
     * @author Varun Shoor
     * @param string $_value
     * @param string $_tableName
     * @param string $_columnName
     * @param array $_tableList
     * @param string $_operator
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetWhereValue($_value, $_tableName, $_columnName, $_tableList, $_operator)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_tableName))));
        $_columnName = $this->Database->Escape(mb_strtolower($_columnName));

        $_returnValue = false;

        if (empty($_tableName)) {
            $_returnValue = true;
        } elseif (!empty($_tableName) && empty($_columnName)) {
            $_returnValue = true;
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            $_returnValue = true;
        } elseif (!in_array($_tableName, $_tableList) && !in_array($_tableName, $this->_autoJoinTableList)) {
            $_returnValue = true;
        }

        if ($_returnValue) {
            return "'" . $_value . "'";
        }

        // Check to see that the field exists under table
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName])) {
            if ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_LINKED) {
                return "'" . $this->GetLinkedFieldValue($_tableName, $_columnName, $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO], $_value, $_operator) . "'";
            } elseif ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_CUSTOM) {
                return "'" . $this->GetCustomValue($_tableName, $_columnName, $_value) . "'";
            }
        }

        // Now if the column doesnt exist, it either is being used as a label or its an invalid column, attempt to look it up using label
        foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_columnNameLoop => $_columnContainer) {
            $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_columnNameLoop));

            // Attempt on just column name
            if (empty($_columnLabel)) {
                $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_columnNameLoop));
            }

            if (!empty($_columnLabel) && $_columnLabel == mb_strtolower($_columnName)) {
                if ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnNameLoop][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_LINKED) {
                    return "'" . $this->GetLinkedFieldValue($_tableName, $_columnNameLoop, $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnNameLoop][SWIFT_KQLSchema::FIELD_LINKEDTO], $_value, $_operator) . "'";
                } else {
                    return "'" . $this->GetCustomValue($_tableName, $_columnNameLoop, $_value) . "'";
                }

                break;
            }
        }

        return "'" . $_value . "'";
    }

    /**
     * Get Custom Field Value
     *
     * @author Andriy Lesyuk
     * @param string $_fieldValue
     * @param array $_customField
     * @return string The Option ID
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetCustomFieldValue($_fieldValue, $_customField, $_operator)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_customValue = false;

        if (($_customField['type'] == SWIFT_CustomField::TYPE_RADIO) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_SELECT) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE)) {
            foreach ($_customField['options'] as $_optionID => $_optionValue) {
                if (mb_strtolower($_fieldValue) == mb_strtolower($_optionValue)) {
                    $_customValue = $this->Database->Escape($_optionID);
                    break;
                }
            }
        } elseif ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
            foreach ($_customField['options'] as $_optionID => $_option) {
                if (mb_strtolower($_fieldValue) == mb_strtolower($_option['value'])) {
                    $_customValue = $this->Database->Escape($_optionID);
                    break;
                }
                if (_is_array($_option['suboptions'])) {
                    foreach ($_option['suboptions'] as $_subOptionID => $_subOption) {
                        if (mb_strtolower($_fieldValue) == mb_strtolower($_subOption['value'])) {
                            $_customValue = $this->Database->Escape($_subOptionID);
                            break;
                        }
                    }
                    if ($_customValue) {
                        break;
                    }
                }
            }
        }

        if ($_customValue) {

            if (($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED)) {

                if ($_operator == '=') {
                    $_operator = 'LIKE';
                    $_customValue = "'%:\"" . $_customValue . "\";%'";
                } elseif ($_operator == '!=') {
                    $_operator = 'NOT LIKE';
                    $_customValue = "'%:\"" . $_customValue . "\";%'";
                }
            }

            return ' ' . $_operator . ' ' . $_customValue;
        } else {
            false;
        }
    }

    /**
     * Get the Custom Value
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_columnName
     * @param string $_fieldValue
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetCustomValue($_tableName, $_columnName, $_fieldValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldValue = trim($this->Database->Escape(stripslashes($_fieldValue))); // Sanitize the string

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1860: [Notice]: Undefined offset: 5 (KQL/class.SWIFT_KQLParser.php:995)
         *
         * Comments: Added isset check
         */
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_CUSTOMVALUES])) {
            foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_CUSTOMVALUES] as $_actualValue => $_localeString) {
                $_localeValue = SWIFT_KQLSchema::GetLabel($_localeString);

                // Do the final values match?
                if (mb_strtolower($_localeValue) == mb_strtolower($_fieldValue)) {
                    return $_actualValue;
                }
            }
        }

        return $_fieldValue;
    }

    /**
     * Get the Linked Field Value
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_columnName
     * @param array $_linkedFieldContainer The KQL Schema Linked Field Container: array(linked field, field value fetcher, extended where clause)
     * @param string $_fieldValue
     * @param string $_operator
     * @return mixed
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetLinkedFieldValue($_tableName, $_columnName, $_linkedFieldContainer, $_fieldValue, $_operator)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!isset($_linkedFieldContainer[0]) || !isset($_linkedFieldContainer[1])) {
            throw new SWIFT_Exception('Invalid Linked Field Container');
        }

        $_fieldValue = $this->Database->Escape(stripslashes($_fieldValue)); // Sanitize the string

        // If this is numeric, we return as is
        if (is_numeric($_fieldValue)) {
            return $_fieldValue;
        }

        $_columnValueContainer = $this->GetTableAndFieldNameOnText($_linkedFieldContainer[1]);
        if (!isset($_columnValueContainer[0]) || empty($_columnValueContainer[0])) {
            throw new SWIFT_Exception('Invalid Linked Value Column');
        }

        $_extendedWhere = '';
        if (isset($_linkedFieldContainer[2])) {
            $_extendedWhere = " AND " . $_linkedFieldContainer[2];
        }

        $_resultValue = $this->Database->QueryFetch("SELECT " . $_linkedFieldContainer[0] . " AS resultvalue FROM " . TABLE_PREFIX . $_columnValueContainer[0] . " AS " . $_columnValueContainer[0]
            . " WHERE " . $_linkedFieldContainer[1] . " " . 'LIKE' . " '" . $_fieldValue . "'" . $_extendedWhere);
        if (isset($_resultValue['resultvalue'])) {
            return $_resultValue['resultvalue'];
        }

        return $_fieldValue;
    }

    /**
     * Retrieve the Where Expression for Multiple Values
     *
     * @author Varun Shoor
     * @param array $_argumentContainer
     * @param string $_opsPrefix
     * @param string $_tableName
     * @param string $_columnName
     * @param string $_operator
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetWhereExpressionForMultipleValues($_argumentContainer, $_opsPrefix, $_tableName, $_columnName, $_operator)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_opsPrefix = trim($_opsPrefix);

        $_prefix = $_suffix = '';
        if ($_opsPrefix == 'IN' || $_opsPrefix == 'NOT IN') {
            $_prefix = $_opsPrefix . ' (';
            $_suffix = ')';
        }


        $_tableName = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_tableName))));
        $_columnName = $this->Database->Escape(mb_strtolower($_columnName));

        $_returnValue = false;

        if (empty($_tableName)) {
            $_returnValue = true;
        } elseif (!empty($_tableName) && empty($_columnName)) {
            $_returnValue = true;
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            $_returnValue = true;
        }

        $_linkedFieldContainer = $_customFieldContainer = false;

        // Check to see that the field exists under table
        if (!$_returnValue && isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName])) {
            if ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_LINKED) {
                $_linkedFieldContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO];
            } elseif ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_CUSTOM) {
                $_customFieldContainer = array($_tableName, $_columnName);
            }
        }

        // Now if the column doesnt exist, it either is being used as a label or its an invalid column, attempt to look it up using label
        if (!$_returnValue) {
            foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_columnNameLoop => $_columnContainer) {
                $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_columnNameLoop));

                // Attempt on just column name
                if (empty($_columnLabel)) {
                    $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_columnNameLoop));
                }

                if (!empty($_columnLabel) && $_columnLabel == mb_strtolower($_columnName)) {
                    if ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnNameLoop][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_LINKED) {
                        $_linkedFieldContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnNameLoop][SWIFT_KQLSchema::FIELD_LINKEDTO];

                    } elseif ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnNameLoop][SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_CUSTOM) {
                        $_customFieldContainer = array($_tableName, $_columnNameLoop);
                    }

                    break;
                }
            }
        }

        $_argumentList = array();
        foreach ($_argumentContainer['values'] as $_key => $_val) {
            if (_is_array($_customFieldContainer)) {
                $_argumentList[] = "'" . $this->GetCustomValue($_customFieldContainer[0], $_customFieldContainer[1], $_val) . "'";

                continue;
            }

            $_dispatchValue = '';
            if (isset($_argumentContainer['types'][$_key]) && $_argumentContainer['types'][$_key] == 'int_val') {
                $_argumentList[] = $this->Database->Escape($_val);
            } elseif (isset($_argumentContainer['types'][$_key]) && $_argumentContainer['types'][$_key] == 'text_val') {
                $_dispatchValue = "'" . $this->Database->Escape($_val) . "'";
            } else {
                $_dispatchValue = "'" . $this->Database->Escape($_val) . "'";
            }

            if (!empty($_dispatchValue) && !$_returnValue && _is_array($_linkedFieldContainer)) {
                $_argumentList[] = "'" . $this->GetLinkedFieldValue($_tableName, $_columnName, $_linkedFieldContainer, $_val, $_operator) . "'";
            } elseif (!empty($_dispatchValue)) {
                $_argumentList[] = $_dispatchValue;
            }
        }

        $_finalValue = ' ' . $_prefix . implode(', ', $_argumentList) . $_suffix;

        return $_finalValue;
    }

    /**
     * Get Custom Field Option IDs
     *
     * @author Andriy Lesyuk
     * @param array $_argumentContainer
     * @param string $_opsPrefix
     * @param array $_customField
     * @return string|array The Option IDs
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetCustomFieldMultipleValues($_argumentContainer, $_opsPrefix, $_customField)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_opsPrefix = trim($_opsPrefix);

        $_argumentList = array();

        // IN ('Value 1', 'Value 2') => IN (1, 2)
        if (($_customField['type'] == SWIFT_CustomField::TYPE_RADIO) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_SELECT) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE)) {
            foreach ($_argumentContainer['values'] as $_key => $_val) {
                foreach ($_customField['options'] as $_optionID => $_optionValue) {
                    if (mb_strtolower($_val) == mb_strtolower($_optionValue)) {
                        $_argumentList[] = $this->Database->Escape($_optionID);
                        continue;
                    }
                }
            }

        } else {
            foreach ($_argumentContainer['values'] as $_key => $_val) {
                $_argumentList[] = "'" . $this->Database->Escape($_val) . "'";
            }
        }

        // IN ('Value 1', 'Value 2') => (... LIKE '%:"1";%' AND ... LIKE '%:"1";%')
        if (($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE)) {
            $_operator = $_joinOperator = '';

            if ($_opsPrefix == 'IN') {
                $_operator = 'LIKE';
                $_joinOperator = 'OR';
            } elseif ($_opsPrefix == 'NOT IN') {
                $_operator = 'NOT LIKE';
                $_joinOperator = 'AND';
            }

            foreach ($_argumentList as &$_argument) {
                $_argument = ' ' . $_operator . " '%:\"" . $_argument . "\";%'";
            }
            unset($_argument);

            $_argumentList[] = $_joinOperator;

            return $_argumentList;

        } else {
            $_prefix = $_suffix = '';

            if (($_opsPrefix == 'IN') || ($_opsPrefix == 'NOT IN')) {
                $_prefix = $_opsPrefix . ' (';
                $_suffix = ')';
            }

            return ' ' . $_prefix . implode(', ', $_argumentList) . $_suffix;
        }
    }

    /**
     * Get Expression From Function Call
     *
     * @author Varun Shoor
     * @param string $_functionName
     * @param array $_arguments
     * @param array $_nestedArguments
     * @param array $_tableList
     * @param string $_sqlPrimaryTable
     * @param string $_alias (OPTIONAL)
     * @return mixed
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetExpressionFromFunction($_functionName, $_arguments, $_nestedArguments, $_tableList, $_sqlPrimaryTable, $_alias = '', $_nestedLevel = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (_is_array($_nestedArguments)) {
            foreach ($_nestedArguments as $_key => $_argument) {
                $_dispatchNestedArguments = array();
                if (isset($_argument['args'])) {
                    $_dispatchNestedArguments = $_argument['args'];
                }

                $_functionExpression = $this->GetExpressionFromFunction($_argument['name'], $_argument['arg'], $_dispatchNestedArguments, $_tableList, $_sqlPrimaryTable, '', $_nestedLevel + 1);
                if ($_functionExpression) {
                    return mb_strtoupper($_functionName) . '(' . $_functionExpression . ')' . IIF(!empty($_alias), ' AS ' . $_alias);
                } else {
                    return false;
                }
            }
        }

        $_functionName = $this->Database->Escape(Clean(mb_strtolower($_functionName)));

        // Commented because a check is needed for base functions like IF, COUNT, SUM too!
//        if (!in_array($_functionName, self::$_allowedFunctionList) && !in_array($_functionName, self::$_allowedExtendedFunctionList)) {
//            throw new SWIFT_Exception('Invalid Function: ' . $_functionName);
//        }

        if (in_array($_functionName, self::$_allowedExtendedFunctionList)) {
            $_functionExpression = $this->ParseExtendedFunction($_functionName, $_arguments, $_tableList, $_sqlPrimaryTable, ($_nestedLevel > 0) ? '' : false, $_alias);

            if (!empty($_alias) && $_functionExpression && !preg_match('/ AS /', $_functionExpression, $_matches)) {
                $_functionExpression .= ' AS ' . $_alias;
            }

            return $_functionExpression;
        }

        $_alias = $this->Database->Escape(Clean($_alias));

        $_finalArgumentList = array();
        foreach ($_arguments as $_argument) {
            if (!in_array(mb_strtolower($_argument), self::$_disallowedColumns)) {
                $_finalArgumentList[] = $_argument;
            }
        }

        return mb_strtoupper($_functionName) . '(' . implode(', ', $_finalArgumentList) . ')' . IIF(!empty($_alias), ' AS ' . $_alias);
    }

    /**
     * Get Expression From Function Call
     *
     * @author Varun Shoor
     * @param string $_functionName
     * @param array $_arguments
     * @param array $_tableList
     * @param string $_sqlPrimaryTable
     * @param string $_opsPrefix
     * @return mixed
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetWhereExpressionFromFunction($_functionName, $_arguments, $_tableList, $_sqlPrimaryTable, $_opsPrefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_operator = trim($_opsPrefix);

        $_functionName = $this->Database->Escape(Clean(mb_strtolower($_functionName)));
        if (!in_array($_functionName, self::$_allowedFunctionList) && !in_array($_functionName, self::$_allowedExtendedFunctionList)) {
            throw new SWIFT_Exception('Invalid Function: ' . $_functionName);
        }

        if (in_array($_functionName, self::$_allowedExtendedFunctionList)) {
            return $this->ParseExtendedFunction($_functionName, $_arguments, $_tableList, $_sqlPrimaryTable, $_operator);
        }

//        $_alias = $this->Database->Escape(Clean($_alias));

        $_finalArgumentList = array();
        foreach ($_arguments as $_argument) {
            if (!strpos($_argument, '.')) {
                $_finalArgumentList[] = $this->GetWhereExpressionFromTableNameAndColumn($_sqlPrimaryTable, $_argument, $_tableList, false);
            } else {
                $_finalArgumentList[] = $this->GetWhereExpressionFromTableNameAndColumn(substr($_argument, 0, strpos($_argument, '.')), substr($_argument, strpos($_argument, '.') + 1), $_tableList, false);
            }
        }

        return mb_strtoupper($_functionName) . '(' . implode(', ', $_finalArgumentList) . ')';
    }

    /**
     * Determine Group Types
     *
     * @author Andriy Lesyuk
     * @param string $_tableName
     * @return array Group Types
     */
    public static function GetCustomFieldGroupTypesByTable($_tableName)
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
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function BuildCustomFieldsCache($_tableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_customFields[$_tableName])) {
            return true;
        }

        // Determine group types
        $_groupTypeList = SWIFT_KQLParser::GetCustomFieldGroupTypesByTable($_tableName);
        if (empty($_groupTypeList)) {
            throw new SWIFT_Exception('Custom fields are not available for table: ' . $_tableName);
        }

        // Fetch custom fields for table
        $_customFields = array();

        $_sqlExpression = "SELECT customfields.customfieldid AS id, customfields.fieldname AS name, customfields.fieldtype AS type, customfields.title, customfields.encryptindb AS encrypt, customfieldgroups.customfieldgroupid AS group_id, customfieldgroups.title AS group_title
            FROM " . TABLE_PREFIX . "customfields AS customfields
            LEFT JOIN " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups ON (customfieldgroups.customfieldgroupid = customfields.customfieldgroupid)
            WHERE customfieldgroups.grouptype IN (" . BuildIN($_groupTypeList) . ")";

        $this->Database->Query($_sqlExpression);
        while ($this->Database->NextRecord()) {
            $_customFields[] = $this->Database->Record;
        }

        $this->_customFields[$_tableName] = array();

        foreach ($_customFields as $_customField) {
            $_customField['table'] = $_tableName;

            // Fetch options
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

            // Populate internal cache arrays
            if (!isset($this->_customFields[$_tableName][$_customField['group_id']])) {
                $this->_customFields[$_tableName][$_customField['group_id']] = array();
            }
            $this->_customFields[$_tableName][$_customField['group_id']][$_customField['id']] = $_customField;

            $this->_customFieldIDMap[$_customField['id']] = $_customField;
            $this->_customFieldNameMap[$_customField['name']] = $_customField;

            $_customFieldTitle = mb_strtolower($_customField['title']);
            if (isset($this->_customFieldTitleMap[$_customFieldTitle])) {
                if (!is_array($this->_customFieldTitleMap[$_customFieldTitle])) {
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
     * Get Custom Field from Arguments
     *
     * @author Andriy Lesyuk
     * @param mixed $_arguments
     * @param string $_sqlPrimaryTable
     * @return array|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCustomField($_arguments, $_sqlPrimaryTable)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Get table name
        if (count($_arguments) > 1) {
            $_tableLabelResult = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_arguments[0]))));
            if (!empty($_tableLabelResult) && isset($this->_schemaContainer[$_tableLabelResult])) {
                $_tableName = $_tableLabelResult;
                array_shift($_arguments);
            } else {
                $_tableName = $_sqlPrimaryTable;
            }
        } else {
            $_tableName = $_sqlPrimaryTable;
        }

        if (!isset($this->_schemaContainer[$_tableName])) {
            throw new SWIFT_Exception('Table is not available in schema: ' . $_tableName);
        }

        $this->BuildCustomFieldsCache($_tableName);

        // Get group ID
        if (count($_arguments) > 1) {
            $_groupTitle = CleanQuotes($_arguments[0]);
            if ($_groupTitle == '*') {
                $_groupID = false;
            } elseif ($this->_customFieldGroupTitleMap[mb_strtolower($_groupTitle)]) {
                $_groupID = $this->_customFieldGroupTitleMap[mb_strtolower($_groupTitle)];
            } else {
                throw new SWIFT_Exception('Custom field group does not exist: ' . $_groupTitle);
            }
            array_shift($_arguments);
        } else {
            $_groupID = false;
        }

        // Get custom field ID
        if (count($_arguments) > 0) {
            $_customFieldTitle = CleanQuotes($_arguments[0]);

            if ($_customFieldTitle == '*') {
                $_customFieldID = false;

            } elseif (isset($this->_customFieldTitleMap[mb_strtolower($_customFieldTitle)])) {
                if (isset($this->_customFieldTitleMap[mb_strtolower($_customFieldTitle)]['id'])) {
                    $_customFieldID = $this->_customFieldTitleMap[mb_strtolower($_customFieldTitle)]['id'];

                } else {
                    foreach ($this->_customFieldTitleMap[mb_strtolower($_customFieldTitle)] as $_customField) {
                        if (($_customField['table'] == $_tableName) &&
                            (!$_groupID || ($_customField['group_id'] == $_groupID))) {
                            return $_customField;
                        }
                    }

                    throw new SWIFT_Exception('Custom field does not exist: ' . $_customFieldTitle);
                }

            } elseif (isset($this->_customFieldNameMap[$_customFieldTitle])) {
                $_customFieldID = $this->_customFieldNameMap[$_customFieldTitle]['id'];

            } else {
                throw new SWIFT_Exception('Custom field does not exist: ' . $_customFieldTitle);
            }
        } else {
            throw new SWIFT_Exception('No custom field specified');
        }

        // CUSTOMFIELD('Group', '...')
        if ($_groupID) {

            // CUSTOMFIELD('Group', 'Field')
            if ($_customFieldID) {
                if (($this->_customFieldIDMap[$_customFieldID]['table'] == $_tableName) &&
                    ($this->_customFieldIDMap[$_customFieldID]['group_id'] == $_groupID)) {
                    return $this->_customFieldIDMap[$_customFieldID];
                } else {
                    throw new SWIFT_Exception('Custom field does not exist: ' . $_customFieldTitle);
                }

                // CUSTOMFIELD('Group', *)
            } else {
                return $this->_customFields[$_tableName][$_groupID];
            }

        } else {

            // CUSTOMFIELD('Field')
            if ($_customFieldID) {
                if ($this->_customFieldIDMap[$_customFieldID]['table'] == $_tableName) {
                    return $this->_customFieldIDMap[$_customFieldID];
                } else {
                    throw new SWIFT_Exception('Custom field does not exist: ' . $_customFieldTitle);
                }

                // CUSTOMFIELD(*)
            } else {
                $_customFields = array();
                foreach ($this->_customFields[$_tableName] as $_groupID => $_groupContainer) {
                    $_customFields = array_merge($_customFields, $_groupContainer);
                }
                return $_customFields;
            }
        }

        return true;
    }

    /**
     * Get Custom Field SQL Expression
     *
     * @author Andriy Lesyuk
     * @param array $_customField
     * @param string $_customFieldTable
     * @param bool $_hasExtendedArgument
     * @return string Custom Field SQL Expression
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetCustomFieldExpressionByType($_customField, $_customFieldTable, $_hasExtendedArgument = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        $_fieldValue = false;

        switch ($_customField['type']) {

            case SWIFT_CustomField::TYPE_DATE:
                // See also GetCalendarDateline
                if ($_SWIFT->Settings->Get('dt_caltype') == 'us') {
                    $_fieldFormat = '%m/%d/%Y';
                } else {
                    $_fieldFormat = '%d/%m/%Y';
                }

                // NOTE: maybe not the best fix (does not take daylight saving into account?)
                $_tz = new DateTimeZone(SWIFT::Get('timezone'));
                $_secondsOffset = $_tz->getOffset(new DateTime());
                $_swiftTimeZone = sprintf("%s%02d:%02d", ($_secondsOffset < 0) ? '-' : '+', abs($_secondsOffset) / 3600, abs($_secondsOffset) % 3600);
                $_mysqlTimeZone = $this->GetMySQLTimeZone();

                $_fieldValue = '';
                if (!$_hasExtendedArgument) {
                    $_fieldValue .= "UNIX_TIMESTAMP(";
                }
                if ($_swiftTimeZone != $_mysqlTimeZone) {
                    $_fieldValue .= "CONVERT_TZ(";
                }
                $_fieldValue .= "STR_TO_DATE(" . $_customFieldTable . ".fieldvalue, '" . $_fieldFormat . "')";
                if ($_swiftTimeZone != $_mysqlTimeZone) {
                    $_fieldValue .= ", '" . $_swiftTimeZone . "', '" . $_mysqlTimeZone . "')";
                }
                if (!$_hasExtendedArgument) {
                    $_fieldValue .= ")";
                }
                break;

            default:
                $_fieldValue = $_customFieldTable . ".fieldvalue";
                break;
        }

        return $_fieldValue;
    }

    /**
     * Get MySQL server time zone in +HH:MM format
     *
     * @author Andriy Lesyuk
     * @return string Time Zone Used on MySQL Server
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
            while ($this->Database->NextRecord()) {
                $_mysqlTimeZone = $this->Database->Record['timezone'];
                break;
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
     * Get Custom Field Expression(s)
     *
     * @author Andriy Lesyuk
     * @param array $_arguments
     * @param array $_tableList
     * @param string $_sqlPrimaryTable
     * @param string|false $_alias
     * @return string|bool SQL Expression For Getting Custom Field
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetExpressionForCustomField($_arguments, $_tableList, $_sqlPrimaryTable, $_alias = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Obtain custom fields
        $_customFields = $this->GetCustomField($_arguments, $_sqlPrimaryTable);

        if (empty($_customFields)) {
            return false;
        }

        $_inSelectClause = (!$_alias);

        if (!$_inSelectClause) {
            if (!isset($_customFields['id'])) {
                throw new SWIFT_Exception('CUSTOMFIELD(*) cannot be used outside SELECT clause');
            } elseif ($_customFields['type'] == SWIFT_CustomField::TYPE_FILE) {
                throw new SWIFT_Exception('File custom field cannot be used outside SELECT clause');
            } elseif (($_customFields['type'] == SWIFT_CustomField::TYPE_PASSWORD) || ($_customFields['encrypt'] == 1)) {
                throw new SWIFT_Exception('Encrypted custom field cannot be used outside SELECT clause');
            }
        }

        $_SWIFT = SWIFT::GetInstance();

        $_sqlExpressions = array();

        if (isset($_customFields['id'])) {
            // Save alias into custom fields array
            if (!empty($_alias) && isset($this->_customFieldIDMap[$_customFields['id']])) {
                $this->_customFieldIDMap[$_customFields['id']]['alias'] = $_alias;
            }

            $_customFields = array($_customFields);
        }

        foreach ($_customFields as $_customFieldID => $_customField) {
            $_customFieldTable = "customfield" . $_customField['id'];

            $_fieldValue = $this->GetCustomFieldExpressionByType($_customField, $_customFieldTable);

            if ($_inSelectClause == true) {
                $_customFieldAlias = '_cf_' . $_customField['id'];

                $_sqlExpressions[] = $_fieldValue . ' AS ' . $_customFieldAlias;
                $_sqlExpressions[] = $_customFieldTable . '.isserialized AS ' . $_customFieldAlias . '_isserialized';
                $_sqlExpressions[] = $_customFieldTable . '.isencrypted AS ' . $_customFieldAlias . '_isencrypted';

                $this->_hiddenFields[$_customFieldAlias . '_isserialized'] = $_customFieldID;
                $this->_hiddenFields[$_customFieldAlias . '_isencrypted'] = $_customFieldID;
            } else {
                $_sqlExpressions[] = $_fieldValue;
            }

            $this->AutoJoinCustomFieldValues($_customField, $_customFieldTable);
        }

        return implode(', ', $_sqlExpressions);
    }

    /**
     * Parse Extended Function
     *
     * @author Varun Shoor
     * @param string $_functionName
     * @param array $_arguments
     * @param array $_tableList
     * @param string $_sqlPrimaryTable
     * @param string|bool $_operator
     * @param string $_alias
     * @return mixed
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ParseExtendedFunction($_functionName, $_arguments, $_tableList, $_sqlPrimaryTable, $_operator = false, $_alias = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        /**
         * ---------------------------------------------
         * Process the primary functions first
         * ---------------------------------------------
         */
        switch ($_functionName) {
            case 'customfield':
                return $this->GetExpressionForCustomField($_arguments, $_tableList, $_sqlPrimaryTable, ($_operator !== false) ? false : $_alias);

            case 'mktime':
                return call_user_func_array('mktime', $_arguments);

            case 'datenow':
                return DATENOW;

            /**
             * BUG FIX - Andriy Lesyuk
             *
             * SWIFT-2203 DATEDIFF() function in reports is not working
             *
             * Comments: All arguments should be timestamps so enclosing into FROM_UNIXTIME
             */
            case 'datediff':
                $_finalArgumentList = array();
                foreach ($_arguments as $_argument) {
                    if (preg_match('/^(\'|")(.*)\1$/', $_argument, $_fieldMatches)) {
                        $_argument = strtotime($_fieldMatches[2]);
                    }
                    $_finalArgumentList[] = 'FROM_UNIXTIME(' . $_argument . ')';
                }

                return mb_strtoupper($_functionName) . '(' . implode(', ', $_finalArgumentList) . ')' . IIF(!empty($_alias), ' AS ' . $_alias);
        }


        /**
         * ---------------------------------------------
         * If we are using equal as an operator
         * ---------------------------------------------
         */
        if ($_operator == '=' || $_operator == '!=') {
            $_returnOperator = 'BETWEEN';
            if ($_operator == '!=') {
                $_returnOperator = 'NOT BETWEEN';
            }

            switch ($_functionName) {

                case 'monthrange' :
                    if (isset($_arguments[0]) && isset($_arguments[1])) {
                        $_monthFrom = $this->Database->Escape($_arguments[0]);
                        $_monthTo = $this->Database->Escape($_arguments[1]);

                        return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('first day of ' . $_monthFrom)) . "' AND '" . SWIFT_Date::CeilDate(strtotime('last day of ' . $_monthTo)) . "'");
                    }

                case 'today':
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('today')) . "' AND '" . SWIFT_Date::CeilDate(strtotime('today')) . "'");

                case 'yesterday':
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('yesterday')) . "' AND '" . SWIFT_Date::CeilDate(strtotime('yesterday')) . "'");

                case 'tomorrow':
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('tomorrow')) . "' AND '" . SWIFT_Date::CeilDate(strtotime('tomorrow')) . "'");

                case 'last7days':
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('-7 days')) . "' AND '" . SWIFT_Date::CeilDate(strtotime('today')) . "'");

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-2117 LastWeek() and Last7Days() functions in Reports are giving same results
                 *
                 */
                case 'lastweek':
                    /*
                     * BUG FIX - Andriy Lesyuk
                     *
                     * SWIFT-2233 Using LastWeek() or ThisWeek() criteria returns tickets that are resolved today
                     *
                     */
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('last week')) . "' AND '" . SWIFT_Date::CeilDate(strtotime('-1 day', strtotime('this week'))) . "'");

                case 'thisweek':
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('this week', DATENOW)) . "' AND '" . SWIFT_Date::CeilDate(strtotime('today')) . "'");

                case 'endofweek':
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('monday', strtotime('this week', DATENOW))) . "' AND '" . SWIFT_Date::CeilDate(strtotime('sunday', strtotime('this week', DATENOW))) . "'");

                case 'nextweek':
                    return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('next week', DATENOW)) . "' AND '" . SWIFT_Date::CeilDate(strtotime('+6 days', strtotime('next week', DATENOW))) . "'");

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1919 LastMonth(), ThisMonth(), NextMonth() returned empty result, if PHP version < 5.3
                 *
                 */
                /*
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-2281 KQL functions like "LastMonth()", "ThisMonth()" and "NextMonth()" does not return result for last day of the month
                 *
                 */
                case 'lastmonth':
                    return array($_returnOperator, "'" . strtotime(date('Y-m-1', strtotime('last month'))) . "' AND '" . SWIFT_Date::CeilDate(strtotime(date('Y-m-t', strtotime('last month')))) . "'");

                case 'thismonth':
                    return array($_returnOperator, "'" . strtotime(date('Y-m-1')) . "' AND '" . SWIFT_Date::CeilDate(strtotime(date('Y-m-t'))) . "'");

                case 'nextmonth':
                    return array($_returnOperator, "'" . strtotime(date('Y-m-1', strtotime('next month'))) . "' AND '" . SWIFT_Date::CeilDate(strtotime(date('Y-m-t', strtotime('next month')))) . "'");

                case 'month':
                    // Check Month Name is Specified in Arguments, e.g. January 2010, August etc.
                    if (isset($_arguments[0])) {
                        $_monthName = $this->Database->Escape($_arguments[0]);
                        return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('first day of ' . $_monthName)) . "' AND '" . SWIFT_Date::CeilDate(strtotime('last day of ' . $_monthName)) . "'");
                    } else {
                        return array($_returnOperator, "'" . SWIFT_Date::FloorDate(strtotime('first day of this month', DATENOW)) . "' AND '" . SWIFT_Date::CeilDate(strtotime('last day of this month', DATENOW)) . "'");
                    }
            }
        }


        /**
         * ---------------------------------------------
         * Fallback
         * ---------------------------------------------
         */
        switch ($_functionName) {

            case 'today':
                return "'" . strtotime('today') . "'";

            case 'yesterday':
                return "'" . strtotime('yesterday') . "'";

            case 'tomorrow':
                return "'" . strtotime('tomorrow') . "'";

            case 'last7days':
                return "'" . SWIFT_Date::FloorDate(strtotime('-7 days')) . "'";

            case 'lastweek':
                return "'" . SWIFT_Date::FloorDate(strtotime('-1 week')) . "'";

            case 'thisweek':
                return "'" . SWIFT_Date::FloorDate(strtotime('this week', DATENOW)) . "'";

            case 'endofweek':
                return "'" . SWIFT_Date::CeilDate(strtotime('sunday', strtotime('this week', DATENOW))) . "'";

            case 'nextweek':
                return "'" . SWIFT_Date::FloorDate(strtotime('next week', DATENOW)) . "'";

            case 'lastmonth':
                return "'" . SWIFT_Date::FloorDate(strtotime('first day of last month', DATENOW)) . "'";

            case 'thismonth':
                return "'" . SWIFT_Date::FloorDate(strtotime('first day of this month', DATENOW)) . "'";

            case 'nextmonth':
                return "'" . SWIFT_Date::FloorDate(strtotime('first day of next month', DATENOW)) . "'";

            default:
                break;
        }

        return false;
    }

    /**
     * Retrieve the SQL Expression from Table Name and Column
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_column
     * @param array $_tableList
     * @param string $_aliasName (OPTIONAL)
     * @param bool $_prefixTable (OPTIONAL)
     * @param bool $_isFunctionCall (OPTIONAL)
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetExpressionFromTableNameAndColumn($_tableName, $_column, $_tableList, $_aliasName = '', $_prefixTable = true, $_isFunctionCall = false)
    {
        $_originalColumn = $_column;

        $_tableName = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_tableName))));
        $_column = $this->Database->Escape(mb_strtolower($_column));
        $_aliasName = $this->Database->Escape(Clean($_aliasName));

        if ($_originalColumn == '*') {
            $_column = '*';
        }

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_tableName)) {
            throw new SWIFT_Exception('No Table Name Specified for SQL Expression');
        } elseif (!empty($_tableName) && empty($_column)) {
            throw new SWIFT_Exception('No Column specified for table: ' . $_tableName);
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            throw new SWIFT_Exception('Table is not available in Schema: ' . $_tableName);
        } elseif (!in_array($_tableName, $_tableList) && !in_array($_tableName, $this->_autoJoinTableList)) {
            throw new SWIFT_Exception('Table is not part of available table list: ' . $_tableName);
        }

        $_tablePrefix = '';
        if ($_prefixTable) {
            $_tablePrefix = TABLE_PREFIX;
        }

        if (!in_array($_tableName, $this->_autoJoinTableList)) {
            $this->_autoJoinTableList[] = $_tableName;
        }

        // By now we have the table, check to see if its a blanket fetch
        if ($_column == '*') {
            throw new SWIFT_Exception('Cannot retrieve via *, please specify an exact field name.');
        }

        // Check to see that the field exists under table
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_column])) {
            if (!$_isFunctionCall && empty($_aliasName)) {
                $_aliasName = $_tableName . '_' . $_column;
            }

            $_linkedResult = $this->GetLinkedToTableAndColumnInfo($_tableName, $_column, $_aliasName, $_tablePrefix);
            if (!empty($_linkedResult)) {
                return $_linkedResult;
            }

            return $_tablePrefix . $_tableName . '.' . $_column . IIF(!empty($_aliasName), ' AS ' . $_aliasName);
        }

        // Now if the column doesnt exist, it either is being used as a label or its an invalid column, attempt to look it up using label
        foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_columnName => $_columnContainer) {
            $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_columnName));

            // Attempt on just column name
            if (empty($_columnLabel)) {
                $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_columnName));
            }

            if (!empty($_columnLabel) && $_columnLabel == mb_strtolower($_column)) {
                if (!$_isFunctionCall && empty($_aliasName)) {
                    $_aliasName = $_tableName . '_' . $_columnName;
                }

                $_linkedResult = $this->GetLinkedToTableAndColumnInfo($_tableName, $_columnName, $_aliasName, $_tablePrefix);
                if (!empty($_linkedResult)) {
                    return $_linkedResult;
                }

                return $_tablePrefix . $_tableName . '.' . $_columnName . IIF(!empty($_aliasName), ' AS ' . $_aliasName);
            }
        }

        // No go?
        throw new SWIFT_Exception('Invalid Column, column not found: ' . $_column);
    }

    /**
     * Retrieve the Linked To Table Info and add to Auto Join
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_columnName
     * @param string $_aliasName
     * @param string $_tablePrefix
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedToTableAndColumnInfo($_tableName, $_columnName, $_aliasName, $_tablePrefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = $this->GetTableNameOnLabel($_tableName);

        if (!isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
            return false;
        }

        // We have a linked field!
        $_joinField = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO][0];
        $_replacementField = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO][1];

        $_tableAndFieldContainer = $this->GetTableAndFieldNameOnText($_joinField);
        if (!isset($_tableAndFieldContainer[0]) || empty($_tableAndFieldContainer[0])) {
            throw new SWIFT_Exception('Invalid Join Table Name');
        }

        $_joinTableName = $_tableAndFieldContainer[0];
        $_joinFieldName = $_tableAndFieldContainer[1];

        if (!in_array($_joinTableName, $this->_sqlTableList) && !in_array($_joinTableName, $this->_autoJoinTableList)) {
            $this->_autoJoinExpressionList[] = TABLE_PREFIX . $_joinTableName . ' AS ' . $_joinTableName . ' ON (' . $_tableName . '.' . $_columnName . ' = ' . $_joinField . ')';
            $this->_sqlTableList[] = $_joinTableName;
        }

        return $_tablePrefix . $_replacementField . IIF(!empty($_aliasName), ' AS ' . $_aliasName);
    }

    /**
     * Retrieve the Linked To Table Info and add to Auto Join (ONLY FOR SORTING!)
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_columnName
     * @param string $_aliasName
     * @param string $_tablePrefix
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSortLinkedToTableAndColumnInfo($_tableName, $_columnName, $_aliasName, $_tablePrefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = $this->GetTableNameOnLabel($_tableName);

        if (!isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
            return false;
        }

        // We have a linked field!
        $_joinField = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO][0];
        $_replacementField = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO][1];

        $_tableAndFieldContainer = $this->GetTableAndFieldNameOnText($_joinField);
        if (!isset($_tableAndFieldContainer[0]) || empty($_tableAndFieldContainer[0])) {
            throw new SWIFT_Exception('Invalid Join Table Name');
        }

        $_joinTableName = $_tableAndFieldContainer[0];
        $_joinFieldName = $_tableAndFieldContainer[1];

        if (!in_array($_joinTableName, $this->_sqlTableList) && !in_array($_joinTableName, $this->_autoJoinTableList)) {
            $this->_autoJoinExpressionList[] = TABLE_PREFIX . $_joinTableName . ' AS ' . $_joinTableName . ' ON (' . $_tableName . '.' . $_columnName . ' = ' . $_joinField . ')';
            $this->_sqlTableList[] = $_joinTableName;
        }

        return $_tablePrefix . $_replacementField . IIF(!empty($_aliasName), ' AS ' . $_aliasName);
    }

    /**
     * Retrieve the Linked To Table Info and add to Auto Join
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_columnName
     * @param string $_aliasName
     * @param string $_tablePrefix
     * @return array|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetWhereLinkedToTableAndColumnInfo($_tableName, $_columnName, $_aliasName, $_tablePrefix, $_extendedFunction)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = $this->GetTableNameOnLabel($_tableName);

        if (!isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO])) {
            return false;
        }

        // We have a linked field!
        $_joinField = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO][0];
        $_replacementField = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_LINKEDTO][1];

        $_tableAndFieldContainer = $this->GetTableAndFieldNameOnText($_joinField);
        if (!isset($_tableAndFieldContainer[0]) || empty($_tableAndFieldContainer[0])) {
            return false;
        }

        $_joinTableName = $_tableAndFieldContainer[0];
        $_joinFieldName = $_tableAndFieldContainer[1];

        $_replacementTableAndFieldContainer = $this->GetTableAndFieldNameOnText($_replacementField);
        if (!isset($_replacementTableAndFieldContainer[0]) || empty($_replacementTableAndFieldContainer[0])) {
            return false;
        }

        $_replacementTableName = $_replacementTableAndFieldContainer[0];
        $_replacementFieldName = $_replacementTableAndFieldContainer[1];

        if (!in_array($_joinTableName, $this->_sqlTableList) && !in_array($_joinTableName, $this->_autoJoinTableList)) {
            $this->_autoJoinExpressionList[] = TABLE_PREFIX . $_joinTableName . ' AS ' . $_joinTableName . ' ON (' . $_tableName . '.' . $_columnName . ' = ' . $_joinField . ')';
            $this->_sqlTableList[] = $_joinTableName;
        }

        return array($_tablePrefix . $_replacementField . IIF(!empty($_aliasName), ' AS ' . $_aliasName), $_replacementTableName, $_replacementFieldName);
    }

    /**
     * Retrieve the SQL WHERE Expression from Table Name and Column
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_column
     * @param array $_tableList
     * @param bool $_prefixTable (OPTIONAL)
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetWhereExpressionFromTableNameAndColumn($_tableName, $_column, $_tableList, $_prefixTable = true, $_hasTableByDefault = true)
    {
        $_originalColumn = $_column;

        $_tableName = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_tableName))));
        $_column = $this->Database->Escape(mb_strtolower($_column));

        if ($_originalColumn == '*') {
            $_column = '*';
        }

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_tableName)) {
            throw new SWIFT_Exception('No Table Name Specified for SQL Expression');
        } elseif (!empty($_tableName) && empty($_column)) {
            throw new SWIFT_Exception('No Column specified for table: ' . $_tableName);
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            throw new SWIFT_Exception('Table is not available in Schema: ' . $_tableName);
        } elseif (!in_array($_tableName, $_tableList) && !in_array($_tableName, $this->_autoJoinTableList)) {
            throw new SWIFT_Exception('Table is not part of available table list: ' . $_tableName);
        }

        $_tablePrefix = '';
        if ($_prefixTable) {
            $_tablePrefix = TABLE_PREFIX;
        }

        // Check to see that the field exists under table
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_column])) {
            return $_tablePrefix . $_tableName . '.' . $_column;
        }

        // Now if the column doesnt exist, it either is being used as a label or its an invalid column, attempt to look it up using label
        foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_columnName => $_columnContainer) {
            $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_columnName));

            // Attempt on just column name
            if (empty($_columnLabel)) {
                $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_columnName));
            }

            if (!empty($_columnLabel) && $_columnLabel == mb_strtolower($_column)) {
                return $_tablePrefix . $_tableName . '.' . $_columnName;
            }
        }

        // No column found? Did we have a provided table? if not, return value as is
        if (!$_hasTableByDefault) {
            return "'" . $_originalColumn . "'";
        }

        // No go?
        throw new SWIFT_Exception('Invalid Column, column not found: ' . $_column);
    }

    /**
     * Process the Sort Order
     *
     * @author Varun Shoor
     * @param string $_sqlPrimaryTable
     * @param array $_tableList
     * @param array $_tableCol
     * @param bool $_prefixTable (OPTIONAL)
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessSortOrder($_sqlPrimaryTable, $_tableList, $_tableCol, $_prefixTable = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_order = $this->Database->Escape(Clean(mb_strtoupper($_tableCol[1])));

        if ($_order != 'ASC' && $_order != 'DESC') {
            throw new SWIFT_Exception('Invalid Order Specified: ' . $_order);
        }

        if (is_array($_tableCol[0])) {
            $_fieldExpression = $_tableCol[0];

            if (isset($_fieldExpression['name']) && isset($_fieldExpression['arg'])) {
                if (mb_strtolower($_fieldExpression['name']) == 'customfield') {
                    $_customFieldExpression = $this->GetExpressionForCustomField($_fieldExpression['arg'], $_tableList, $_sqlPrimaryTable, false);
                    if ($_customFieldExpression) {
                        $_customFieldExpression .= ' ' . $_order;
                    }

                    return $_customFieldExpression;
                } else {
                    throw new SWIFT_Exception('Invalid function for ORDER BY: ' . $_fieldExpression['name']);
                }
            } else {
                throw new SWIFT_Exception('Invalid Column');
            }
        }

        $_tableName = $_sqlPrimaryTable;
        $_column = '';
        if (!strpos($_tableCol[0], '.')) {
            $_column = $_tableCol[0];
        } else {
            $_tableName = substr($_tableCol[0], 0, strpos($_tableCol[0], '.'));
            $_column = substr($_tableCol[0], strpos($_tableCol[0], '.') + 1);
        }

        $_tableName = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_tableName))));
        $_column = $this->Database->Escape(mb_strtolower($_column));

        if (empty($_tableName)) {
            throw new SWIFT_Exception('No Table Name Specified for SQL Expression');
        } elseif (!empty($_tableName) && empty($_column)) {
            throw new SWIFT_Exception('No Column specified for table: ' . $_tableName);
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            throw new SWIFT_Exception('Table is not available in Schema: ' . $_tableName);
        } elseif (!in_array($_tableName, $_tableList) && !in_array($_tableName, $this->_autoJoinTableList)) {
            throw new SWIFT_Exception('Table is not part of available table list: ' . $_tableName);
        }

        $_tablePrefix = '';
        if ($_prefixTable) {
            $_tablePrefix = TABLE_PREFIX;
        }

        // Check to see that the field exists under table
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_column])) {
            $_linkedResult = $this->GetSortLinkedToTableAndColumnInfo($_tableName, $_column, '', $_tablePrefix);
            if (!empty($_linkedResult)) {
                return $_linkedResult . ' ' . $_order;
            }

            return $_tablePrefix . $_tableName . '.' . $_column . ' ' . $_order;
        }

        // Now if the column doesnt exist, it either is being used as a label or its an invalid column, attempt to look it up using label
        foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_columnName => $_columnContainer) {
            $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_columnName));

            // Attempt on just column name
            if (empty($_columnLabel)) {
                $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_columnName));
            }

            if (!empty($_columnLabel) && $_columnLabel == mb_strtolower($_column)) {
                $_linkedResult = $this->GetLinkedToTableAndColumnInfo($_tableName, $_columnName, '', $_tablePrefix);
                if (!empty($_linkedResult)) {
                    return $_linkedResult . ' ' . $_order;
                }

                return $_tablePrefix . $_tableName . '.' . $_columnName . ' ' . $_order;
            }
        }

        // No go?
        throw new SWIFT_Exception('Invalid Column, column not found: ' . $_column);
    }

    /**
     * Process the Group By
     *
     * @author Varun Shoor
     * @param string $_sqlPrimaryTable
     * @param array $_tableList
     * @param string $_tableCol
     * @param array $_options
     * @param bool $_prefixTable (OPTIONAL)
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessGroupBy($_sqlPrimaryTable, $_tableList, $_tableCol, $_options, $_prefixTable = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = $_sqlPrimaryTable;
        $_column = '';
        if (!strpos($_tableCol, '.')) {
            $_column = $_tableCol;
        } else {
            $_tableName = substr($_tableCol, 0, strpos($_tableCol, '.'));
            $_column = substr($_tableCol, strpos($_tableCol, '.') + 1);
        }

        $_tableName = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_tableName))));
        $_column = $this->Database->Escape(mb_strtolower($_column));

        if (empty($_tableName)) {
            throw new SWIFT_Exception('No Table Name Specified for SQL Expression');
        } elseif (!empty($_tableName) && empty($_column)) {
            throw new SWIFT_Exception('No Column specified for table: ' . $_tableName);
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            throw new SWIFT_Exception('Table is not available in Schema: ' . $_tableName);
        } elseif (!in_array($_tableName, $_tableList) && !in_array($_tableName, $this->_autoJoinTableList)) {
            print_r($this->_autoJoinTableList);
            throw new SWIFT_Exception('Table is not part of available table list: ' . $_tableName);
        }

        $_tablePrefix = '';
        if ($_prefixTable) {
            $_tablePrefix = TABLE_PREFIX;
        }

        $_hasExtendedArgument = false;
        $_fieldPrefix = $_fieldSuffix = $_extendedFunction = '';
        if (isset($_options['extended'])) {
            $_hasExtendedArgument = true;

            $_extendedFunction = mb_strtolower($_options['extended']);
        }

        $_groupFunction = '';
        if (isset($_options['function'])) {
            $_groupFunction = mb_strtoupper($_options['function']);
        }

        // The field is actually a function
        if ($_column == '_function') {
            if (mb_strtolower($_options['name']) == 'customfield') {
                $_customField = $this->GetCustomField($_options['arg'], $_sqlPrimaryTable);
                if ($_customField && isset($_customField['id'])) {
                    $_customFieldTable = "customfield" . $_customField['id'];

                    $_fieldExpression = $this->GetCustomFieldExpressionByType($_customField, $_customFieldTable, $_hasExtendedArgument);
                    $_fieldName = '_cf_' . $_customField['id'];

                    if ($_hasExtendedArgument) {
                        $_fieldPrefixSuffixContainer = $this->GetCustomFieldPrefixSuffix($_extendedFunction, $_customField);
                        extract($_fieldPrefixSuffixContainer);

                        $_fieldName = $_extendedFunction . $_fieldName;
                        $_fieldExpression = $_fieldPrefix . $_fieldExpression . $_fieldSuffix;
                    }

                    $_customFieldExpressionList = array();

                    $_customFieldExpressionList[] = $_fieldExpression . ' AS ' . $_fieldName;
                    $_customFieldExpressionList[] = $_customFieldTable . '.isserialized AS ' . $_fieldName . '_isserialized';
                    $_customFieldExpressionList[] = $_customFieldTable . '.isencrypted AS ' . $_fieldName . '_isencrypted';

                    // Ensure that JOINSs are made
                    $this->AutoJoinCustomFieldValues($_customField, $_customFieldTable);

                    $this->_hiddenFields[$_fieldName . '_isserialized'] = $_customField['id'];
                    $this->_hiddenFields[$_fieldName . '_isencrypted'] = $_customField['id'];

                    if ($_groupFunction == 'X') {
                        $this->_autoDistinctSQLExpressionList[$_fieldName] = implode(', ', $_customFieldExpressionList);

                        $this->_returnGroupByXFields[] = array('cf.' . $_customField['id'], $_fieldName, $_fieldExpression);

                        return '';
                    } else {
                        $this->_autoSQLExpressionList = array_merge($this->_autoSQLExpressionList, $_customFieldExpressionList);

                        $this->_returnGroupByFields[] = array('cf.' . $_customField['id'], $_fieldName);

                        return $_fieldExpression;
                    }
                } else {
                    throw new SWIFT_Exception('CUSTOMFIELD(*) cannot be used in GROUP BY');
                }
            } else {
                throw new SWIFT_Exception('Function not supported in GROUP BY: ' . $_options['name']);
            }
        }

        // Check to see that the field exists under table
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_column])) {
            $_linkedResultContainer = $this->GetWhereLinkedToTableAndColumnInfo($_tableName, $_column, '', $_tablePrefix, $_extendedFunction);
            if (_is_array($_linkedResultContainer)) {
                $_baseFieldName = $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];
                $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;

                if ($_hasExtendedArgument) {
                    $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_linkedResultContainer[1], $_linkedResultContainer[2]);
                    extract($_fieldPrefixSuffixContainer);
                    $_baseFieldName = $_extendedFunction . '_' . $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];
                    $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;

                    // Skip the grouping for X axis
                    if ($_groupFunction == 'X') {
                        $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fetchFieldName . ' AS ' . $_baseFieldName;

                        $this->_returnGroupByXFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_fetchFieldName);

                        return '';
                    }

                    $this->_autoSQLExpressionList[] = $_fetchFieldName . ' AS ' . $_baseFieldName;

                    // Add to Group By array
                    $this->_returnGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName);

                    return $_baseFieldName;
                }


                // Skip the grouping for X axis
                if ($_groupFunction == 'X') {
                    $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fetchFieldName . ' AS ' . $_baseFieldName;

                    $this->_returnGroupByXFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_linkedResultContainer[0]);

                    return '';
                }

                $this->_autoSQLExpressionList[] = $_linkedResultContainer[0] . ' AS ' . $_baseFieldName;

                // Add to Group By array
                $this->_returnGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName);

                return $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;
            }

            $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_tableName, $_column);
            extract($_fieldPrefixSuffixContainer);
            $_returnField = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_column . $_fieldSuffix;
            $_baseFieldName = $_tableName . '_' . $_column;
            if ($_hasExtendedArgument) {
                $_baseFieldName = $_extendedFunction . '_' . $_tableName . '_' . $_column;

                // Skip the grouping for X axis
                if ($_groupFunction == 'X') {
                    $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_column . $_fieldSuffix . ' AS ' . $_baseFieldName;

                    $this->_returnGroupByXFields[] = array($_tableName . '.' . $_column, $_baseFieldName, $_returnField);

                    return '';
                }

                $this->_autoSQLExpressionList[] = $_returnField . ' AS ' . $_baseFieldName;

                $_returnField = $_baseFieldName;

                // Dont add to auto load if its supposed to be part of the matrix report
            } elseif ($_groupFunction != 'X') {
                $this->_autoSQLExpressionList[] = $_tableName . '.' . $_column . ' AS ' . $_baseFieldName;
            }

            // Skip the grouping for X axis
            if ($_groupFunction == 'X') {
                $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_column . $_fieldSuffix . ' AS ' . $_baseFieldName;

                $this->_returnGroupByXFields[] = array($_tableName . '.' . $_column, $_baseFieldName, $_tableName . '.' . $_column);

                return '';
            }

            // Add to Group By array
            $this->_returnGroupByFields[] = array($_tableName . '.' . $_column, $_baseFieldName);

            return $_returnField;
        }

        // Now if the column doesnt exist, it either is being used as a label or its an invalid column, attempt to look it up using label
        foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_columnName => $_columnContainer) {
            $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_columnName));

            // Attempt on just column name
            if (empty($_columnLabel)) {
                $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_columnName));
            }

            if (!empty($_columnLabel) && $_columnLabel == mb_strtolower($_column)) {
                $_linkedResultContainer = $this->GetWhereLinkedToTableAndColumnInfo($_tableName, $_columnName, '', $_tablePrefix, $_extendedFunction);
                if (_is_array($_linkedResultContainer)) {
                    $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;
                    $_baseFieldName = $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];

                    if ($_hasExtendedArgument) {
                        $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_linkedResultContainer[1], $_linkedResultContainer[2]);
                        extract($_fieldPrefixSuffixContainer);
                        $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;
                        $_baseFieldName = $_extendedFunction . '_' . $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];

                        // Skip the grouping for X axis
                        if ($_groupFunction == 'X') {
                            $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fieldPrefix . $_fetchFieldName . $_fieldSuffix . ' AS ' . $_baseFieldName;

                            $this->_returnGroupByXFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_fetchFieldName);

                            return '';
                        }

                        $this->_autoSQLExpressionList[] = $_fetchFieldName . ' AS ' . $_baseFieldName;

                        // Add to Group By array
                        $this->_returnGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName);

                        return $_baseFieldName;
                    }


                    // Skip the grouping for X axis
                    if ($_groupFunction == 'X') {
                        $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fieldPrefix . $_fetchFieldName . $_fieldSuffix . ' AS ' . $_baseFieldName;

                        $this->_returnGroupByXFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_linkedResultContainer[0]);

                        return '';
                    }

                    $this->_autoSQLExpressionList[] = $_linkedResultContainer[0] . ' AS ' . $_baseFieldName;

                    // Add to Group By array
                    $this->_returnGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName);

                    return $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;
                }

                $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_tableName, $_columnName);
                extract($_fieldPrefixSuffixContainer);
                $_returnField = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_columnName . $_fieldSuffix;
                $_baseFieldName = $_tableName . '_' . $_columnName;

                if ($_hasExtendedArgument) {
                    $_baseFieldName = $_extendedFunction . '_' . $_tableName . '_' . $_columnName;
                    // Skip the grouping for X axis
                    if ($_groupFunction == 'X') {
                        $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_columnName . $_fieldSuffix . ' AS ' . $_baseFieldName;

                        $this->_returnGroupByXFields[] = array($_tableName . '.' . $_columnName, $_baseFieldName, $_returnField);

                        return '';
                    }

                    $this->_autoSQLExpressionList[] = $_returnField . ' AS ' . $_baseFieldName;

                    $_returnField = $_baseFieldName;

                    // Dont add to auto load if its supposed to be part of the matrix report
                } elseif ($_groupFunction != 'X') {
                    $this->_autoSQLExpressionList[] = $_tableName . '.' . $_columnName . ' AS ' . $_baseFieldName;
                }

                if ($_groupFunction == 'X') {
                    $this->_autoDistinctSQLExpressionList[$_baseFieldName] = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_columnName . $_fieldSuffix . ' AS ' . $_baseFieldName;

                    $this->_returnGroupByXFields[] = array($_tableName . '.' . $_columnName, $_baseFieldName, $_tableName . '.' . $_columnName);

                    return '';
                }

                // Add to Group By array
                $this->_returnGroupByFields[] = array($_tableName . '.' . $_columnName, $_baseFieldName);

                return $_returnField;
            }
        }

        // No go?
        throw new SWIFT_Exception('Invalid Column, column not found: ' . $_column);
    }

    /**
     * Process the MultiGroup By Clauses
     *
     * @author Varun Shoor
     * @param string $_sqlPrimaryTable
     * @param array $_tableList
     * @param string $_tableCol
     * @param array $_options
     * @param bool $_prefixTable (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessMultiGroupBy($_sqlPrimaryTable, $_tableList, $_tableCol, $_options, $_prefixTable = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = $_sqlPrimaryTable;
        $_column = '';
        if (!strpos($_tableCol, '.')) {
            $_column = $_tableCol;
        } else {
            $_tableName = substr($_tableCol, 0, strpos($_tableCol, '.'));
            $_column = substr($_tableCol, strpos($_tableCol, '.') + 1);
        }

        $_tableName = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_tableName))));
        $_column = $this->Database->Escape(mb_strtolower($_column));

        if (empty($_tableName)) {
            throw new SWIFT_Exception('No Table Name Specified for SQL Expression');
        } elseif (!empty($_tableName) && empty($_column)) {
            throw new SWIFT_Exception('No Column specified for table: ' . $_tableName);
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            throw new SWIFT_Exception('Table is not available in Schema: ' . $_tableName);
        } elseif (!in_array($_tableName, $_tableList) && !in_array($_tableName, $this->_autoJoinTableList)) {
            print_r($this->_autoJoinTableList);
            throw new SWIFT_Exception('Table is not part of available table list: ' . $_tableName);
        }

        $_tablePrefix = '';
        if ($_prefixTable) {
            $_tablePrefix = TABLE_PREFIX;
        }

        $_hasExtendedArgument = false;
        $_fieldPrefix = $_fieldSuffix = $_extendedFunction = '';
        if (isset($_options['extended'])) {
            $_hasExtendedArgument = true;

            $_extendedFunction = mb_strtolower($_options['extended']);
        }

        $_groupFunction = '';
        if (isset($_options['function'])) {
            $_groupFunction = mb_strtoupper($_options['function']);
        }

        // The field is actually a function
        if ($_column == '_function') {
            if (mb_strtolower($_options['name']) == 'customfield') {
                $_customField = $this->GetCustomField($_options['arg'], $_sqlPrimaryTable);
                if ($_customField && isset($_customField['id'])) {
                    $_customFieldTable = "customfield" . $_customField['id'];

                    $_fieldExpression = $this->GetCustomFieldExpressionByType($_customField, $_customFieldTable, $_hasExtendedArgument);
                    $_fieldName = '_cf_' . $_customField['id'];

                    if ($_hasExtendedArgument) {
                        $_fieldPrefixSuffixContainer = $this->GetCustomFieldPrefixSuffix($_extendedFunction, $_customField);
                        extract($_fieldPrefixSuffixContainer);

                        $_fieldName = $_extendedFunction . $_fieldName;
                        $_fieldExpression = $_fieldPrefix . $_fieldExpression . $_fieldSuffix;
                    }

                    // Ensure that JOINSs are made
                    $this->AutoJoinCustomFieldValues($_customField, $_customFieldTable);

                    $this->_autoMultigroupSQLExpressionList[$_fieldName] = $_fieldExpression . ' AS ' . $_fieldName;
                    $this->_autoMultigroupSQLExpressionList[$_fieldName . '_isserialized'] = $_customFieldTable . '.isserialized AS ' . $_fieldName . '_isserialized';
                    $this->_autoMultigroupSQLExpressionList[$_fieldName . '_isencrypted'] = $_customFieldTable . '.isencrypted AS ' . $_fieldName . '_isencrypted';

                    $this->_hiddenFields[$_fieldName . '_isserialized'] = $_customField['id'];
                    $this->_hiddenFields[$_fieldName . '_isencrypted'] = $_customField['id'];

                    $this->_returnMultiGroupByFields[] = array('cf.' . $_customField['id'], $_fieldName, $_fieldExpression);

                    return false;
                } else {
                    throw new SWIFT_Exception('CUSTOMFIELD(*) cannot be used in GROUP BY');
                }
            } else {
                throw new SWIFT_Exception('Function not supported in GROUP BY: ' . $_options['name']);
            }
        }

        // Check to see that the field exists under table
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_column])) {
            $_linkedResultContainer = $this->GetWhereLinkedToTableAndColumnInfo($_tableName, $_column, '', $_tablePrefix, $_extendedFunction);
            if (_is_array($_linkedResultContainer)) {
                $_baseFieldName = $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];
                $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;

                if ($_hasExtendedArgument) {
                    $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_linkedResultContainer[1], $_linkedResultContainer[2]);
                    extract($_fieldPrefixSuffixContainer);
                    $_baseFieldName = $_extendedFunction . '_' . $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];
                    $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;

                    $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_fetchFieldName . ' AS ' . $_baseFieldName;

                    // Add to Multi Group By array
                    $this->_returnMultiGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_fetchFieldName);

                    return false;
                }

                $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_linkedResultContainer[0] . ' AS ' . $_baseFieldName;

                // Add to Multi Group By array
                $this->_returnMultiGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_linkedResultContainer[0]);

                return false;
            }

            $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_tableName, $_column);
            extract($_fieldPrefixSuffixContainer);
            $_returnField = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_column . $_fieldSuffix;
            $_baseFieldName = $_tableName . '_' . $_column;
            if ($_hasExtendedArgument) {
                $_baseFieldName = $_extendedFunction . '_' . $_tableName . '_' . $_column;

                $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_returnField . ' AS ' . $_baseFieldName;

                // Add to Multi Group By array
                $this->_returnMultiGroupByFields[] = array($_tableName . '.' . $_column, $_baseFieldName, $_returnField);

            } else {
                $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_tableName . '.' . $_column . ' AS ' . $_baseFieldName;

                // Add to Multi Group By array
                $this->_returnMultiGroupByFields[] = array($_tableName . '.' . $_column, $_baseFieldName, $_tableName . '.' . $_column);
            }

            return false;
        }

        // Now if the column doesnt exist, it either is being used as a label or its an invalid column, attempt to look it up using label
        foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_columnName => $_columnContainer) {
            $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_columnName));

            // Attempt on just column name
            if (empty($_columnLabel)) {
                $_columnLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_columnName));
            }

            if (!empty($_columnLabel) && $_columnLabel == mb_strtolower($_column)) {
                $_linkedResultContainer = $this->GetWhereLinkedToTableAndColumnInfo($_tableName, $_columnName, '', $_tablePrefix, $_extendedFunction);
                if (_is_array($_linkedResultContainer)) {
                    $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;
                    $_baseFieldName = $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];

                    if ($_hasExtendedArgument) {
                        $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_linkedResultContainer[1], $_linkedResultContainer[2]);
                        extract($_fieldPrefixSuffixContainer);
                        $_fetchFieldName = $_fieldPrefix . $_linkedResultContainer[0] . $_fieldSuffix;
                        $_baseFieldName = $_extendedFunction . '_' . $_linkedResultContainer[1] . '_' . $_linkedResultContainer[2];

                        $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_fetchFieldName . ' AS ' . $_baseFieldName;

                        // Add to Multi Group By array
                        $this->_returnMultiGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_fetchFieldName);

                        return false;
                    }

                    $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_linkedResultContainer[0] . ' AS ' . $_baseFieldName;

                    // Add to Multi Group By array
                    $this->_returnMultiGroupByFields[] = array($_linkedResultContainer[0], $_baseFieldName, $_linkedResultContainer[0]);

                    return false;
                }

                $_fieldPrefixSuffixContainer = $this->GetFieldPrefixSuffix($_extendedFunction, $_tableName, $_columnName);
                extract($_fieldPrefixSuffixContainer);
                $_returnField = $_fieldPrefix . $_tablePrefix . $_tableName . '.' . $_columnName . $_fieldSuffix;
                $_baseFieldName = $_tableName . '_' . $_columnName;

                if ($_hasExtendedArgument) {
                    $_baseFieldName = $_extendedFunction . '_' . $_tableName . '_' . $_columnName;

                    $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_returnField . ' AS ' . $_baseFieldName;

                    // Add to Multi Group By array
                    $this->_returnMultiGroupByFields[] = array($_tableName . '.' . $_columnName, $_baseFieldName, $_returnField);

                } else {
                    $this->_autoMultigroupSQLExpressionList[$_baseFieldName] = $_tableName . '.' . $_columnName . ' AS ' . $_baseFieldName;

                    // Add to Multi Group By array
                    $this->_returnMultiGroupByFields[] = array($_tableName . '.' . $_columnName, $_baseFieldName, $_tableName . '.' . $_columnName);
                }

                return false;
            }
        }

        // No go?
        throw new SWIFT_Exception('Invalid Column, column not found: ' . $_column);
    }

    /**
     * Join the Custom Field Values Table
     *
     * @author Andriy Lesyuk
     * @param array $_customField
     * @param string $_customFieldTable
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function AutoJoinCustomFieldValues($_customField, $_customFieldTable)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!in_array($_customFieldTable, $this->_customFieldsAliasList)) {
            $this->_autoJoinExpressionList[] = TABLE_PREFIX . "customfieldvalues AS " . $_customFieldTable . " ON
                (" . $_customFieldTable . ".customfieldid = '" . $_customField['id'] . "' AND
                 " . $_customFieldTable . ".fieldvalue != '' AND
                 " . $_customFieldTable . ".typeid = " . $_customField['table'] . "." . $this->_schemaContainer[$_customField['table']][SWIFT_KQLSchema::SCHEMA_PRIMARYKEY] . ")";
            $this->_customFieldsAliasList[] = $_customFieldTable;
        }

        return true;
    }

    /**
     * Retrieve the field prefix and suffix
     *
     * @author Andriy Lesyuk
     * @param string $_extendedFunction
     * @param array $_customField
     * @return array Field Prefix and Suffix
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetCustomFieldPrefixSuffix($_extendedFunction, $_customField)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Check functions are allowed for the custom field type
        if (empty($_extendedFunction) || !isset(self::$_extendedCustomFieldClauses[$_customField['type']])) {
            return array();
        }

        // Check if the function is allowed
        $_foundMatch = false;
        $_functionList = self::$_extendedCustomFieldClauses[$_customField['type']];
        foreach ($_functionList as $_function) {
            if ($_extendedFunction == mb_strtolower($_function)) {
                $_foundMatch = true;
                break;
            }
        }
        if (!$_foundMatch) {
            return array();
        }

        $_fieldPrefix = $_fieldSuffix = '';

        if ($_customField['type'] == SWIFT_CustomField::TYPE_DATE) {
            $_fieldPrefix = strtoupper($_extendedFunction) . '(';
            $_fieldSuffix = ')';
        }

        return array('_fieldPrefix' => $_fieldPrefix, '_fieldSuffix' => $_fieldSuffix);
    }

    /**
     * Retrieve the field prefix and suffix
     *
     * @author Varun Shoor
     * @param string $_extendedFunction
     * @param string $_tableName
     * @param string $_columnName
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetFieldPrefixSuffix($_extendedFunction, $_tableName, $_columnName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName = $this->GetTableNameOnLabel($_tableName);

        if (empty($_extendedFunction) || !isset(self::$_extendedClauses[$this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_TYPE]])) {
            return array();
        }

        // Check for validity of function
        $_fieldType = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_TYPE];
        $_functionList = self::$_extendedClauses[$this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnName][SWIFT_KQLSchema::FIELD_TYPE]];

        $_fieldPrefix = $_fieldSuffix = '';

        $_foundMatch = false;
        foreach ($_functionList as $_function) {
            if ($_extendedFunction == mb_strtolower($_function)) {
                // Now that we have the match, we decide on the prefix/suffix
                if ($_fieldType == SWIFT_KQLSchema::FIELDTYPE_SECONDS) {
                    $_foundMatch = true;

                    if ($_extendedFunction == 'minute') {
                        $_fieldPrefix = '(';
                        $_fieldSuffix = '/60)';
                    } elseif ($_extendedFunction == 'hour') {
                        $_fieldPrefix = '(';
                        $_fieldSuffix = '/3600)';
                    } elseif ($_extendedFunction == 'day') {
                        $_fieldPrefix = '(';
                        $_fieldSuffix = '/86400)';
                    }
                } elseif ($_fieldType == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME) {
                    $_foundMatch = true;
                    $_fieldPrefix = strtoupper($_extendedFunction) . '(FROM_UNIXTIME(';
                    $_fieldSuffix = '))';
                }
            }
        }

        if (!$_foundMatch) {
            return array();
        }

        return array('_fieldPrefix' => $_fieldPrefix, '_fieldSuffix' => $_fieldSuffix);
    }

    /**
     * Return the Primary Table Name
     *
     * @author Varun Shoor
     * @return string The Primary Table Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetPrimaryTableName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_primaryTableName;
    }

    /**
     * Return Original Aliases Map
     *
     * @author Andriy Lesyuk
     * @return array The Original Aliases Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOriginalAliasMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_originalAliasesMap;
    }

    /**
     * Return Alias-to-Field Map
     *
     * @author Andriy Lesyuk
     * @return array The Aliases to Fields Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAliasMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_aliasesFieldsMap;
    }

    /**
     * Return Field-to-Function Map
     *
     * @author Andriy Lesyuk
     * @return array The Fields to Functions Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFunctionMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_functionsFieldsMap;
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
     * Return Custom Fields
     *
     * @author Andriy Lesyuk
     * @return array The Custom Fields
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCustomFields($_tableName = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!empty($_tableName)) {
            $_groupTypeList = SWIFT_KQLParser::GetCustomFieldGroupTypesByTable($_tableName);
            if (!empty($_groupTypeList)) {
                $this->BuildCustomFieldsCache($_tableName);
            }

            if (isset($this->_customFields[$_tableName])) {
                $_customFields = array();
                foreach ($this->_customFields[$_tableName] as $_groupID => $_groupContainer) {
                    $_customFields = array_merge($_customFields, $_groupContainer);
                }

                return $_customFields;
            } else {
                return array();
            }
        } else {
            return $this->_customFieldIDMap;
        }
    }

    /**
     * Return KQL2 Object
     *
     * @author Andriy Lesyuk
     * @return mixed The KQL Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetKQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->KQLObject;
    }

    /**
     * Parse the argument into an array
     *
     * @author Varun Shoor
     * @param string $_argumentText
     * @return array The Result Array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ParseArgumentIntoArray($_argumentText)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_argumentText = trim($_argumentText);

        $_resultArray = array();

        if (empty($_argumentText)) {
            return $_resultArray;
        }

        $_chunks = explode(',', $_argumentText);
        if (!_is_array($_chunks)) {
            $_chunks = array($_argumentText);
        }

        foreach ($_chunks as $_chunkValue) {
            $_resultArray[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_chunkValue));
        }

        return $_resultArray;
    }

    /**
     * Function for Sorting Day Names
     *
     * @author Andriy Lesyuk
     * @param string $_a
     * @param string $_b
     * @return int The Comparison Result
     */
    private function _SortDayNames($_a, $_b)
    {
        return (self::$_dayNameMap[$_a] - self::$_dayNameMap[$_b]);
    }

    /**
     * Function for Sorting Month Names
     *
     * @author Andriy Lesyuk
     * @param string $_a
     * @param string $_b
     * @return int The Comparison Result
     */
    private function _SortMonthNames($_a, $_b)
    {
        return (self::$_monthNameMap[$_a] - self::$_monthNameMap[$_b]);
    }

}

?>
