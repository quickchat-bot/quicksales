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

use Base\Library\KQL\SWIFT_KQL;
use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\KQL2\SWIFT_KQL2;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\Staff\SWIFT_Staff;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The Report Base class used by Report Renderer and Report Exporter
 *
 * @property CellStyleMock $_workSheet
 * @author Andriy Lesyuk
 */
abstract class SWIFT_ReportBase extends SWIFT_Library
{
    const EXPORT_EXCEL = 'Excel';
    const EXPORT_EXCEL5 = 'Excel5';
    //    const EXPORT_PDF = 'PDF';
    const EXPORT_CSV = 'CSV';
    const EXPORT_HTML = 'HTML';

    const SQL_WHERE = "WHERE";
    const REDUNDANT_TAGS_IN_FROM_RE = '/LEFT JOIN swtags AS tags\d ON taglinks\.tagid = tags\d\.tagid/m';
    const TAG_COND_RE = '/\((tags\d*\.tagname\s*=\s*\'(\w+)\'\s*(AND){0,1}\s*){1,}\)\s*/m';
    const TAG_PART_COND_RE = '/(tags\d*\.tagname\s*=\s*\'(\w+)\'\s*)/m';
    const WHERE_RE = '/((WHERE.*)GROUP BY)|((WHERE.*)ORDER BY)|(WHERE.*)/m';
    const TAG_SUB_JOIN = ' AND EXISTS (SELECT 1 FROM swtaglinks AS taglinks JOIN swtags AS tags ON taglinks.tagid = tags.tagid WHERE ';
    const LEFT_JOIN = "LEFT JOIN";
    const WHERE_COND = "WHERE";
    const JOIN_COND = '/ON(.*)/m';

    // Aliases-to-Fields Map
    protected $_aliasesToFieldsMap = array();
    protected $_fieldsToFunctionsMap = array();

    protected $_backupTimeZone = false;

    protected $KQLParserResult = false;
    protected $Report = false;
    protected $_sqlResult = array();
    protected $_schemaContainer = array();
    protected $_sqlParsedTitles = array();
    protected $_sqlGroupByFields = array();
    protected $_sqlGroupByXFields = array();
    protected $_sqlGroupByMultiFields = array();
    protected $_sqlDistinctValueContainer = array();

    // Matrix Report
    protected $_baseTitleContainer = false;
    protected $_replacementYKey = false;
    protected $_groupFieldList = array();
    protected $_parentTitleIgnoreList = array();
    protected $_dataContainer = array();
    protected $_baseUserFieldList = array();
    protected $_distinctYGroupValuesList = array();
    protected $_baseUserFieldCount = 0;
    protected $_resultsContainerY = array();
    protected $_groupByYCountMap = array();

    protected $_recordCount = 0;

    protected $_rowCount = 0;
    protected $_extraCount = 0;

    // Hidden Fields
    protected $_hiddenFields = array();

    // Custom Fields
    protected $_customFields = array();

    // Original Aliases
    protected $_originalAliasesMap = array();

    // Export formats
    protected $_exportFormatMap = array(
        self::EXPORT_EXCEL => array('Excel2007', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'),
        self::EXPORT_EXCEL5 => array('Excel5', 'application/vnd.ms-excel', 'xls'),
        //        self::EXPORT_PDF => array('PDF', 'application/pdf', 'pdf'),
        self::EXPORT_CSV => array('CSV', 'text/csv', 'csv'),
        self::EXPORT_HTML => array('HTML', 'text/html', 'html')
    );

    /**
     * @var SWIFT_KQL
     */
    public $KQL = null;

    /**
     * @var SWIFT_KQL2 Object
     */
    protected $KQLObject = null;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_KQL2 $_SWIFT_KQL2Object
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @param SWIFT_KQLParserResult $_SWIFT_KQLParserResultObject
     */
    public function __construct($_SWIFT_KQL2Object, SWIFT_Report $_SWIFT_ReportObject, SWIFT_KQLParserResult $_SWIFT_KQLParserResultObject)
    {
        parent::__construct();

        if (
            !$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()
            || !$_SWIFT_KQLParserResultObject instanceof SWIFT_KQLParserResult || !$_SWIFT_KQLParserResultObject->GetIsClassLoaded()
        ) {
            throw new SWIFT_Exception('Invalid Input Received for Report Rendering/Exporting');
        }

        $this->Load->Library('KQL:KQL', [], true, false, 'base');

        $this->_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        $this->KQLObject = $_SWIFT_KQL2Object;
        $this->Report = $_SWIFT_ReportObject;
        $this->KQLParserResult = $_SWIFT_KQLParserResultObject;

        $this->_sqlGroupByFields = $this->KQLParserResult->GetGroupByFields();
        $this->_sqlGroupByXFields = $this->KQLParserResult->GetGroupByXFields();
        $this->_sqlGroupByMultiFields = $this->KQLParserResult->GetMultiGroupByFields();
        $this->_sqlDistinctValueContainer = $this->KQLParserResult->GetDistinctValues();
    }

    /**
     * Execute the SQL statement and place in the result
     *
     * @author Varun Shoor
     * @param string|bool|array $_customSQL (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ExecuteSQL($_customSQL = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$this->KQLParserResult instanceof SWIFT_KQLParserResult || !$this->KQLParserResult->GetIsClassLoaded()) {
            throw new SWIFT_Exception('Invalid KQL Parser Result Object');
        }

        $this->_rowCount = 0;
        $this->_extraCount = 0;

        $this->_sqlResult = array();

        $_sqlStatementList = $this->KQLParserResult->GetSQL();
        foreach ($_sqlStatementList as $key => $value) {
            $_sqlStatementList[$key] = $this->processDateLineField($_sqlStatementList[$key]);
            $_sqlStatementList[0] = $_sqlStatementList[$key];
        }
         
        if (!empty($_customSQL)) {
            if (is_array($_customSQL)) {
                $_sqlStatement = $_customSQL[0];
            } else {
                $_sqlStatement = $_customSQL;
            }

            // echo $_sqlStatement . '<br />' . SWIFT_CRLF;
            $this->Database->Query($_sqlStatement);            
        } else if (isset($_sqlStatementList[0])) {
            // echo $_sqlStatementList[0] . '<br />' . SWIFT_CRLF;
            $correctedSql = $this->correctTagCondition($_sqlStatementList[0]);
            $this->Database->Query($correctedSql);
        } else {
            return false;
        }

        while ($this->Database->NextRecord()) {
            $this->_sqlResult[] = $this->Database->Record;
            $this->_rowCount++;
        }

        // Run extra SQLs
        if (($this->_rowCount > 0) &&
            (empty($_customSQL) || (is_array($_customSQL) && (count($_customSQL) > 1)))
        ) {
            if (is_array($_customSQL)) {
                $_extraStatementList = array_slice($_customSQL, 1);
            } else {
                $_extraStatementList = $this->KQLParserResult->GetExtraSQL();
            }

            foreach ($_extraStatementList as $_sql) {
                $this->Database->Query($_sql);

                while ($this->Database->NextRecord()) { // it should be a single row though
                    $this->_sqlResult[] = $this->Database->Record;
                    $this->_extraCount++;
                }
            }
        }

        return true;
    }


	/**
	 * This method is used incase user use "SELECT *" Ref: https://trilogy-eng.atlassian.net/browse/KAYAKOC-30280
	 * the dateline exists in both User and Tickets table that need to use alias "as" to differentiate
	 * The method should apply for SELECT part only. (https://docs.google.com/document/d/1MxrJN0OCNtJVooBwNrLe74iS3RBKrnDhdczPAZ21OUo/edit#)
	 *
	 * @author Thanh Dinh
	 * @param string $sql
	 * @return string the normalize sql after using alias
	 */
    protected function processDateLineField($sql)
    {
        if (!is_string($sql)) {
            return $sql;
        }
	    $wherePos = strpos(strtoupper($sql), self::SQL_WHERE);
	    if ($wherePos > 0) {
		    $selectPart = substr($sql, 0, $wherePos);
		    $wherePart = substr($sql, $wherePos);
	    } else {
		    $selectPart = $sql;
		    $wherePart = '';
	    }

	    $sqlArray = explode(",", $selectPart);
	    $result = [];
	    if (!empty($sqlArray)) {
		    foreach ($sqlArray as $sqlField) {
			    if (strpos($sqlField, ".dateline") > -1  && strpos($sqlField, "DATE_FORMAT") === false && strpos($sqlField, self::SQL_WHERE) === false && strpos($sqlField, "AS") === false) {
				    $alias = str_replace(".", "_", $sqlField);
				    $sqlField .= " as " . $alias;
			    }
			    $result[] = $sqlField;
		    }
	    }
	    return implode(",", $result).$wherePart;
    }

    /**
     * Process the Array Keys for the SQL Result
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessSQLResultTitle()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_sqlResult[0])) {
            return false;
        }

        $this->_sqlParsedTitles = array();

        $_functionList = SWIFT_KQL::GetCombinedFunctionList();

        $_arrayKeys = array_keys($this->_sqlResult[0]);

        $_finalSQLTitles = array(); // array(title, type, fieldpointer, function)

        foreach ($_arrayKeys as $_key => $_val) {
            if (!strstr($_val, '_')) {
                $_functionMatches = array();
                $_functionName = $_columnNameExtended = false;
                if (preg_match('/^(avg|min|max|sum)\((.*)\)$/i', $_val, $_functionMatches)) {
                    $_functionName = $_functionMatches[1];
                    $_columnNameExtended = $_functionMatches[2];

                    $_tableFieldNameContainer = $this->KQL->GetTableAndFieldNameOnText($_columnNameExtended);
                    $_tableNameParsed = $_tableFieldNameContainer[0];
                    $_columnNameParsed = $_tableFieldNameContainer[1];

                    if (isset($this->_schemaContainer[$_tableNameParsed][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnNameParsed])) {
                        $_fieldContainer = $this->_schemaContainer[$_tableNameParsed][SWIFT_KQLSchema::SCHEMA_FIELDS][$_columnNameParsed];
                        $_fieldTitle = $this->Language->Get($_tableNameParsed . '_' . $_columnNameParsed);
                        if (empty($_fieldTitle)) {
                            $_fieldTitle = $_val;
                        }

                        $_finalSQLTitles[$_val] = array($_fieldTitle, $_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE], $_fieldContainer, false);

                        continue;
                    }
                }

                if (isset($this->_originalAliasesMap[$_val])) {
                    $_finalSQLTitles[$_val] = array($this->_originalAliasesMap[$_val], false, false, false);
                } else {
                    $_finalSQLTitles[$_val] = array($_val, false, false, false);
                }

                continue;
            }

            $_keyChunks = explode('_', strtolower($_val));
            $_tableName = $_columnName = '';

            // No chunks? Probably custom
            if (!count($_keyChunks)) {
                $_finalSQLTitles[$_val] = array($_val, false, false, false);

                continue;
            }

            // Is the first chunk the name of a function
            if (in_array($_keyChunks[0], $_functionList) && count($_keyChunks) == 3 && isset($this->_schemaContainer[$_keyChunks[1]]) && isset($this->_schemaContainer[$_keyChunks[1]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[2]])) {
                $_tableName = $_keyChunks[1];
                $_columnName = $_keyChunks[2];

                $_fieldContainer = $this->_schemaContainer[$_keyChunks[1]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[2]];
                $_fieldTitle = $this->Language->Get($_tableName . '_' . $_columnName);
                if (empty($_fieldTitle)) {
                    $_fieldTitle = $_columnName;
                }

                $_finalSQLTitles[$_val] = array($_fieldTitle, $_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE], $_fieldContainer, $_keyChunks[0]);

                // Is this a custom field
            } else if (count($_keyChunks) >= 3 && $_keyChunks[1] == 'cf' && isset($this->_customFields[$_keyChunks[2]])) {
                if ($_keyChunks[0] == '') { // Function name
                    $_keyChunks[0] = false;
                }

                if (isset($_keyChunks[3])) { // IsSerialized & IsEncrypted field
                    $_finalSQLTitles[$_val] = array($_keyChunks[3], SWIFT_KQLSchema::FIELDTYPE_BOOL, array(SWIFT_KQLSchema::FIELD_TYPE => SWIFT_KQLSchema::FIELDTYPE_BOOL), false);
                } else {
                    $_fieldContainer = array();
                    if ($this->_customFields[$_keyChunks[2]]['type'] == SWIFT_CustomField::TYPE_DATE) {
                        $_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] = SWIFT_KQLSchema::FIELDTYPE_UNIXTIME;
                    } else {
                        $_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] = SWIFT_KQLSchema::FIELDTYPE_STRING;
                    }

                    if (isset($this->_customFields[$_keyChunks[2]]['alias'])) {
                        $_fieldTitle = $this->_customFields[$_keyChunks[2]]['alias'];
                    } else {
                        $_fieldTitle = $this->_customFields[$_keyChunks[2]]['title'];
                    }

                    $_finalSQLTitles[$_val] = array($_fieldTitle, $_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE], $_fieldContainer, $_keyChunks[0]);
                }
            } else if (count($_keyChunks) == 2 && isset($this->_schemaContainer[$_keyChunks[0]]) && isset($this->_schemaContainer[$_keyChunks[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[1]])) {
                $_tableName = $_keyChunks[0];
                $_columnName = $_keyChunks[1];

                $_fieldContainer = $this->_schemaContainer[$_keyChunks[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[1]];
                $_fieldTitle = $this->Language->Get($_tableName . '_' . $_columnName);
                if (empty($_fieldTitle)) {
                    $_fieldTitle = $_columnName;
                }

                $_finalSQLTitles[$_val] = array($_fieldTitle, $_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE], $_fieldContainer, false);
            } else {
                $_finalSQLTitles[$_val] = array($_val, false, false, false);
            }
        }

        $this->_sqlParsedTitles = $_finalSQLTitles;

        return true;
    }

    /**
     * Process the Field Values for the SQL Result
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessFieldValues()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_sqlResult[0])) {
            return false;
        }

        /**
         * Process Field Values (using FIELD_PROCESSOR)
         */

        $_processedFieldColumns = array();

        foreach ($this->_sqlResult[0] as $_columnName => $_columnValue) {
            $_columnExpression = $this->KQLObject->Compiler->GetExpressionByColumnName($_columnName);

            if ($_columnExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) { // TODO: FUNC(table.field) etc
                $_fieldName = $_columnExpression[SWIFT_KQL2::EXPRESSION_DATA];

                if (
                    isset($this->_schemaContainer[$_fieldName[0]]) &&
                    isset($this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]])
                ) {
                    $_fieldProperties = $this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]];

                    if (isset($_fieldProperties[SWIFT_KQLSchema::FIELD_PROCESSOR])) {
                        $_processedFieldColumns[$_columnName] = $_fieldProperties[SWIFT_KQLSchema::FIELD_PROCESSOR];
                    }
                }
            }
        }

        foreach ($_processedFieldColumns as $_columnName => $_fieldProcessor) {
            foreach ($this->_sqlResult as $_index => $_resultContainer) {
                if ($_index >= $this->_rowCount) {
                    break;
                }

                $_columnValue = $this->_sqlResult[$_index][$_columnName];
                if ($_columnValue) {
                    $this->_sqlResult[$_index][$_columnName] = $this->$_fieldProcessor($_columnValue, $_columnName, $_resultContainer);
                }
            }
        }

        for ($_sequentIndex = 0; isset($this->_sqlResult[$this->_rowCount + $_sequentIndex]); $_sequentIndex++) {
            foreach ($this->_sqlResult[$_sequentIndex] as $_columnName => $_columnValue) {
                $_columnExpression = $this->KQLObject->Compiler->GetExpressionByColumnName($_columnName, $_sequentIndex);

                if ($_columnExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) { // TODO: FUNC(table.field) etc
                    $_fieldName = $_columnExpression[SWIFT_KQL2::EXPRESSION_DATA];

                    if (
                        isset($this->_schemaContainer[$_fieldName[0]]) &&
                        isset($this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]])
                    ) {
                        $_fieldProperties = $this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]];

                        if (isset($_fieldProperties[SWIFT_KQLSchema::FIELD_PROCESSOR])) {
                            $_method = $_fieldProperties[SWIFT_KQLSchema::FIELD_PROCESSOR];
                            $this->_sqlResult[$_sequentIndex][$_columnName] = $this->$_method($_columnValue, $_columnName, $this->_sqlResult[$_sequentIndex]);
                        }
                    }
                }
            }
        }

        /**
         * Process Custom Fields
         */

        $_customFieldColumns = array();

        // Collect custom fields column names
        foreach ($this->_sqlResult[0] as $_columnName => $_columnValue) {
            if (
                array_key_exists($_columnName, $this->_aliasesToFieldsMap) &&
                0 === strpos($this->_aliasesToFieldsMap[$_columnName], 'customfield')
            ) {
                $_colName = $this->_aliasesToFieldsMap[$_columnName];
                // extract customfieldid
                if (preg_match('/customfield(\d+)\.fieldvalue/', $_colName, $matches)) {
                    $_customFieldColumns[$_columnName] = $matches[1];
                }
            } else {
                $_keyChunks = explode('_', $_columnName);
                if ((count($_keyChunks) === 3) && ($_keyChunks[1] === 'cf')) {
                    $_customFieldColumns[$_columnName] = $_keyChunks[2];
                }
            }
        }

        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        foreach ($_customFieldColumns as $_columnName => $_fieldID) { // FIXME: Move to a special processor
            foreach ($this->_sqlResult as $_index => $_resultContainer) {
                if ($_index >= $this->_rowCount) {
                    break;
                }

                $_columnValue = $this->_sqlResult[$_index][$_columnName];
                if ($_columnValue) {
                    $_isSerialized = $this->_sqlResult[$_index][$_columnName . '_isserialized'];
                    $_isEncrypted = $this->_sqlResult[$_index][$_columnName . '_isencrypted'];

                    $this->_sqlResult[$_index][$_columnName] = SWIFT_KQL::GetParsedCustomFieldValue($_columnValue, $this->_customFields[$_fieldID], ($_isEncrypted == '1'), ($_isSerialized == '1'));
                }
            }
        }

        return true;
    }

    /**
     * Convert the Column Value Taking into Account Column Type
     *
     * @author Andriy Lesyuk
     * @param string $_columnName
     * @param string|null $_columnValue
     * @param int|bool $_rowIndex
     * @return mixed The Column Value
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ConvertColumnValue($_columnName, $_columnValue, $_rowIndex = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (null === $_columnValue) {
            return $_columnValue;
        }

        $_columnExpression = $this->KQLObject->Compiler->GetExpressionByColumnName($_columnName, $_rowIndex);

        if (!isset($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
            return $_columnValue;
        }

        switch ($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE]) {
            case SWIFT_KQL2::DATA_BOOLEAN:
                if ($_columnValue == '1') {
                    return true;
                } elseif ($_columnValue == '0') {
                    return false;
                }
                break;

            case SWIFT_KQL2::DATA_INTEGER:
            case SWIFT_KQL2::DATA_SECONDS:
            case SWIFT_KQL2::DATA_UNIXDATE:
                if (is_numeric($_columnValue)) {
                    return floatval($_columnValue);
                }
                break;

            case SWIFT_KQL2::DATA_FLOAT:
                if (is_numeric($_columnValue)) {
                    return floatval($_columnValue);
                }
                break;

            case SWIFT_KQL2::DATA_TIME:
                $_secondsValue = SWIFT_Date::StringToSeconds($_columnValue);
                if ($_secondsValue !== false) {
                    return $_secondsValue;
                }
                break;

            case SWIFT_KQL2::DATA_DATE:
                /**
                 * BUG FIX - Nidhi Gupta <nidhi.gupta@opencart.com.vn>
                 * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
                 *
                 * SWIFT-4504 Difference in date & time of 'Date' type Custom Field
                 * SWIFT-5050 Incorrect date calculations with all 'US' based time zones in reports
                 *
                 * Comments - mktime to gmmktime to get reports of same date.
                 * Comments - Set default time as GMT temporarily for the report calculations.
                 */
                $_time = date_default_timezone_get();
                date_default_timezone_set("GMT");
                if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $_columnValue, $_matches)) {
                    return gmmktime(0, 0, 0, (int) ($_matches[2]), (int) ($_matches[3]), (int) ($_matches[1]));
                }
                date_default_timezone_set($_time);
                break;

            case SWIFT_KQL2::DATA_DATETIME:
                if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $_columnValue, $_matches)) {
                    return mktime((int) ($_matches[4]), (int) ($_matches[5]), (int) ($_matches[6]), (int) ($_matches[2]), (int) ($_matches[3]), (int) ($_matches[1]));
                }
                break;

            case SWIFT_KQL2::DATA_STRING:
                return strval($_columnValue);
                break;
        }

        return $_columnValue;
    }

    /**
     * Sets Hidden Fields
     *
     * @author Andriy Lesyuk
     * @param array $_hiddenFields The Hidden Fields
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetHiddenFields($_hiddenFields)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_hiddenFields = $_hiddenFields;

        return true;
    }

    /**
     * Sets Custom Fields Properties
     *
     * @author Andriy Lesyuk
     * @param array $_customFields The Custom Fields
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetCustomFields($_customFields)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_customFields = $_customFields;

        return true;
    }

    /**
     * Sets Aliases-to-Fields Map
     *
     * @author Andriy Lesyuk
     * @param array $_aliasMap The Aliases Map
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetAliasMap($_aliasMap)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_aliasesToFieldsMap = $_aliasMap;

        return true;
    }

    /**
     * Sets Fields-to-Functions Map
     *
     * @author Andriy Lesyuk
     * @param array $_functionMap The Functions Map
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetFunctionMap($_functionMap)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_fieldsToFunctionsMap = $_functionMap;

        return true;
    }

    /**
     * Sets Original Aliases Map
     *
     * @author Andriy Lesyuk
     * @param array $_originalMap The Original Aliases Map
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetOriginalAliasMap($_originalMap)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_originalAliasesMap = $_originalMap;

        return true;
    }

    /**
     * Build a Count Map
     *
     * @author Varun Shoor, Andriy Lesyuk
     * @return array The Count Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function BuildSummaryCountMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_summaryCountMap = array();
        $_rowMapRefList = array();

        $_summaryCountReference = false;
        $_fieldDisplayReferenceName = false;
        $_referenceIsDone = false;

        foreach ($this->_sqlResult as $_index => $_resultContainer) {
            if ($_index == $this->_rowCount) {
                break; // skip extra rows
            }

            foreach ($this->_sqlGroupByFields as $_groupByField) {
                $_fieldName = $_groupByField[0];
                $_fieldReferenceName = $_groupByField[1];
                $_fieldValue = $_resultContainer[$_fieldReferenceName];

                if ($_summaryCountReference === false) {
                    $_summaryCountMap = array($_fieldReferenceName => array($_fieldValue => array('count' => 0, 'children' => array())));
                    $_summaryCountReference = &$_summaryCountMap;
                }

                if (!isset($_summaryCountReference[$_fieldReferenceName][$_fieldValue])) {
                    $_summaryCountReference[$_fieldReferenceName][$_fieldValue] = array('count' => 0, 'children' => array());
                }

                $_summaryCountReference[$_fieldReferenceName][$_fieldValue]['count']++;

                if ($_referenceIsDone === false && $_fieldDisplayReferenceName === false) {
                    $_fieldDisplayReferenceName = $_fieldReferenceName;
                } else if ($_referenceIsDone === false) {
                    $_fieldDisplayReferenceName .= '_' . $_fieldReferenceName;
                }

                if (!isset($_rowMapRefList[$_fieldDisplayReferenceName])) {
                    $_rowMapRefList[$_fieldDisplayReferenceName] = 0;
                }

                $_rowMapRefList[$_fieldDisplayReferenceName]++;

                $_summaryCountReference = &$_summaryCountReference[$_fieldReferenceName][$_fieldValue]['children'];
            }

            // Reset the summary count reference to the first field
            $_summaryCountReference = &$_summaryCountMap;

            // Mark the render reference table as done
            $_referenceIsDone = true;
        }

        return $_summaryCountMap;
    }

    /**
     * Sort SQL Result
     *
     * @author Varun Shoor, Andriy Lesyuk
     * @return array Sorted SQL Result
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SortSummarySQLResult()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalSQLResultContainer = array();

        $_resultIndex = 'a';
        foreach ($this->_sqlResult as $_index => $_resultContainer) {
            $_keyGroupText = false;

            foreach ($this->_sqlGroupByFields as $_groupByField) {
                $_fieldName = $_groupByField[0];
                $_fieldReferenceName = $_groupByField[1];
                $_fieldValue = $_resultContainer[$_fieldReferenceName];

                if ($_fieldValue === null) {
                    $_fieldValue = '%null%';
                }

                if ($_keyGroupText === false) {
                    $_keyGroupText = $_fieldValue;
                } else {
                    $_keyGroupText .= $_fieldValue;
                }
            }

            $_finalKey = $_keyGroupText . $_resultIndex;

            $_finalSQLResultContainer[$_finalKey] = $_resultContainer;

            $_resultIndex++;
        }

        /*
         * BUG FIX - Andriy Lesyuk
         *
         * SWIFT-2223 Reports: GROUP BY doesn't sort numeric columns with natsort
         *
         */
        //        ksort($_finalSQLResultContainer, SORT_STRING);

        return $_finalSQLResultContainer;
    }

    /**
     * Gets Report Filename
     *
     * @author Andriy Lesyuk
     * @return string The Report File Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFilename()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fileName = $this->Report->GetProperty('title');
        $_fileName = preg_replace('/[^a-z0-9]+/i', '_', $_fileName);
        $_fileName = trim($_fileName, '_');

        if ($_fileName != '') {
            $_fileName .= '_';
        }

        $_fileName .= strftime('%d-%b-%Y_%H-%M');

        return $_fileName;
    }

    /**
     * Load Up the Data
     *
     * @author Varun Shoor, Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadMatrixData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_originalSQLs = $this->KQLParserResult->GetSQL();

        $_finalSQLs = array();
        $_extraSQLs = array();

        // Split into two arrays
        foreach ($_originalSQLs as $_statementKey => $_sqlStatement) {
            if (is_array($_sqlStatement)) {
                $_finalSQLs[] = array($_statementKey, array_shift($_sqlStatement));
                $_extraSQLs[] = array($_statementKey, $_sqlStatement);
            } else {
                $_finalSQLs[] = array($_statementKey, $_sqlStatement);
            }
        }

        // Append extra SQLs to the end
        if (!empty($_extraSQLs)) {
            $_finalSQLs = array_merge($_finalSQLs, $_extraSQLs);
        }

        foreach ($_finalSQLs as $_statementArray) {
            $_statementKey = $_statementArray[0];
            $_sqlStatement = $_statementArray[1];

            if (isset($this->_parentTitleIgnoreList[$_statementKey])) {
                continue;
            }

            // Execute the SQL Statement
            if (!$this->ExecuteSQL($_sqlStatement)) {
                continue;
            }

            if (!count($this->_sqlResult)) {
                $this->_parentTitleIgnoreList[$_statementKey] = $_statementKey;
            }

            // Process Array Keys into Titles
            if (!$this->ProcessSQLResultTitle()) {
                continue;
            }

            // Process Custom Field Values
            if (!$this->ProcessFieldValues()) {
                continue;
            }

            if (isset($this->_dataContainer[$_statementKey])) {
                $this->_dataContainer[$_statementKey]['results'] = array_merge($this->_dataContainer[$_statementKey]['results'], $this->_sqlResult);
            } else {
                $this->_dataContainer[$_statementKey] = array('title' => $this->_sqlParsedTitles, 'results' => $this->_sqlResult);
            }

            if ($this->_baseTitleContainer === false && count($this->_sqlResult)) {
                $this->_baseTitleContainer = $this->_sqlParsedTitles;
            }

            foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {
                if (isset($this->_groupFieldList[$_titleName]) || in_array($_titleName, $this->_baseUserFieldList)) {
                    continue;
                }
                if (isset($this->_hiddenFields[$_titleName])) {
                    continue;
                }

                $this->_baseUserFieldList[] = $_titleName;
            }

            foreach ($this->_sqlResult as $_resultsContainer) {
                foreach ($this->_sqlGroupByFields as $_groupByField) {
                    $_fieldName = $_groupByField[0];
                    $_fieldNameReference = $_groupByField[1];

                    if (!isset($this->_distinctYGroupValuesList[$_fieldNameReference])) {
                        $this->_distinctYGroupValuesList[$_fieldNameReference] = array();
                    }

                    if ($_resultsContainer[$_fieldNameReference] === null) {
                        $_resultsContainer[$_fieldNameReference] = '';
                    }

                    if (isset($_resultsContainer[$_fieldNameReference]) && !in_array($_resultsContainer[$_fieldNameReference], $this->_distinctYGroupValuesList[$_fieldNameReference])) {
                        $this->_distinctYGroupValuesList[$_fieldNameReference][] = $_resultsContainer[$_fieldNameReference];
                    }
                }
            }
        }

        $this->_baseUserFieldCount = count($this->_baseUserFieldList);

        // Reset counts
        $this->_rowCount = 0;
        $this->_extraCount = $this->KQLObject->GetTotalRowCount();

        // Resort the distinct values
        foreach ($this->_sqlGroupByFields as $_groupByField) {
            $_fieldName = $_groupByField[0];
            $_fieldNameReference = $_groupByField[1];

            if (isset($this->_distinctYGroupValuesList[$_fieldNameReference])) {
                if ($this->_extraCount > 0) { // Sort only the part of the array
                    $_distinctYGroupValuesList = array_slice($this->_distinctYGroupValuesList[$_fieldNameReference], 0, -$this->_extraCount);

                    sort($_distinctYGroupValuesList);

                    $this->_distinctYGroupValuesList[$_fieldNameReference] = array_merge(
                        $_distinctYGroupValuesList,
                        array_slice($this->_distinctYGroupValuesList[$_fieldNameReference], $this->_extraCount)
                    );
                } else {
                    sort($this->_distinctYGroupValuesList[$_fieldNameReference]);
                }
            }
        }

        return true;
    }

    /**
     * Build the Y Map
     *
     * @author Varun Shoor, Andriy Lesyuk
     * @return array The Y Map
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function BuildMatrixYMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_processedGroupByYMap = array();
        $_processedGroupByYResultsMap = array();

        $this->_replacementYKey = array('fields' => array(), 'key' => '');

        $this->BuildMatrixYGrid($this->_sqlGroupByFields, $_processedGroupByYMap, $_processedGroupByYResultsMap, $this->_replacementYKey);

        // We need to load up the render result container
        foreach ($_processedGroupByYResultsMap as $_value) {
            $this->_resultsContainerY[$_value] = false;
            $this->_groupByYCountMap[$_value] = 0;
        }

        $this->_replacementYKey = $this->_replacementYKey['key'];

        return $_processedGroupByYMap;
    }

    /**
     * Build the Y Grid
     *
     * @author Varun Shoor
     * @param array $_groupByYFields
     * @param array $_processedGroupByMap
     * @param array $_processedGroupByResultsMap
     * @param string|array $_replacementYKey
     * @param array|bool|null $_nodeReference
     * @param string $_keyPrefix
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function BuildMatrixYGrid($_groupByYFields, &$_processedGroupByMap, &$_processedGroupByResultsMap, &$_replacementYKey, &$_nodeReference = false, $_keyPrefix = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldName = $_groupByYFields[0][0];
        $_fieldNameReference = $_groupByYFields[0][1];

        $_activeCount = $_childCount = 0;

        $_temporaryKeyList = array();

        if ($_nodeReference === false) {
            $_processedGroupByMap[$_fieldNameReference] = array();

            if (isset($this->_distinctYGroupValuesList[$_fieldNameReference])) {
                foreach ($this->_distinctYGroupValuesList[$_fieldNameReference] as $_distinctValue) {
                    $_activeKeyPrefix = $_fieldNameReference . ':' . $_distinctValue;

                    $_temporaryKeyList[] = $_activeKeyPrefix;

                    $_processedGroupByMap[$_fieldNameReference][$_distinctValue] = array('count' => 0, 'childcount' => 0, 'children' => array(), 'key' => $_activeKeyPrefix);
                }
            }

            $_replacementYKey['key'] = $_fieldNameReference . ':%' . $_fieldNameReference . '%';
            $_replacementYKey['fields'][] = $_fieldNameReference;

            $_nodeReference = &$_processedGroupByMap;
        } else {
            if (!in_array($_fieldNameReference, $_replacementYKey['fields'])) {
                $_replacementYKey['key'] .= '_' . $_fieldNameReference . ':%' . $_fieldNameReference . '%';
                $_replacementYKey['fields'][] = $_fieldNameReference;
            }

            $_nodeReference[$_fieldNameReference] = array();

            foreach ($this->_distinctYGroupValuesList[$_fieldNameReference] as $_distinctValue) {
                $_activeKeyPrefix = $_keyPrefix . '_' . $_fieldNameReference . ':' . $_distinctValue;

                $_temporaryKeyList[] = $_activeKeyPrefix;

                $_nodeReference[$_fieldNameReference][$_distinctValue] = array('count' => 0, 'childcount' => 0, 'children' => array(), 'key' => $_activeKeyPrefix);
            }
        }

        $_childCount = count($_nodeReference[$_fieldNameReference]);

        if ($_nodeReference == false) {
            throw new SWIFT_Exception('Invalid Node Reference');
        }

        $_nextGroupByYFields = array_slice($_groupByYFields, 1);
        $_totalCount = 0;

        if (count($_nextGroupByYFields)) {
            foreach ($_nodeReference[$_fieldNameReference] as $_distinctValue => $_distinctValueContainer) {
                $_ndv = &$_nodeReference[$_fieldNameReference][$_distinctValue];
                $_returnedCountContainer = $this->BuildMatrixYGrid($_nextGroupByYFields, $_processedGroupByMap, $_processedGroupByResultsMap, $_replacementYKey, $_ndv['children'], $_ndv['key']);

                $_returnedCount = $_returnedCountContainer[0];
                $_returnedChildCount = $_returnedCountContainer[1];

                $_ndv['count'] = $_returnedCount;
                $_totalCount += $_returnedCount;

                if (!isset($_ndv['childcount'])) {
                    $_ndv['childcount'] = 0;
                }
                $_ndv['childcount'] += $_returnedChildCount;
                $_childCount += $_returnedChildCount;
            }

            // We are at last node
        } else {
            $_processedGroupByResultsMap = array_merge($_processedGroupByResultsMap, $_temporaryKeyList);
        }

        $_finalCount = $_activeCount + $_totalCount;

        return array($_finalCount, $_childCount);
    }

    /**
     * Builds the X Grid
     *
     * @author Varun Shoor
     * @param array $_groupByXFields
     * @param array $_processedGroupByMap
     * @param array|bool|null $_nodeReference
     * @param string $_keyPrefix
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function BuildMatrixXGrid($_groupByXFields, &$_processedGroupByMap, &$_nodeReference = false, $_keyPrefix = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldName = $_groupByXFields[0][0];
        $_fieldNameReference = $_groupByXFields[0][1];

        $_activeCount = $_childCount = 0;

        if ($_nodeReference === false) {
            $_processedGroupByMap[$_fieldNameReference] = array();

            if (isset($this->_sqlDistinctValueContainer[$_fieldNameReference])) {
                foreach ($this->_sqlDistinctValueContainer[$_fieldNameReference] as $_distinctValue) {
                    if (is_array($_distinctValue)) {
                        $_activeKeyPrefix = $_fieldNameReference . ':' . $_distinctValue[0];
                        $_distinctValue = $_distinctValue[1];
                    } else {
                        $_activeKeyPrefix = $_fieldNameReference . ':' . $_distinctValue;
                    }

                    $_processedGroupByMap[$_fieldNameReference][$_distinctValue] = array('count' => 0, 'childcount' => 0, 'children' => array(), 'key' => $_activeKeyPrefix);

                    if (isset($this->_dataContainer[$_activeKeyPrefix])) {
                        $_processedGroupByMap[$_fieldNameReference][$_distinctValue]['values'] = &$this->_dataContainer[$_activeKeyPrefix];
                        $_processedGroupByMap[$_fieldNameReference][$_distinctValue]['count'] = count($this->_dataContainer[$_activeKeyPrefix]['results']);

                        $_activeCount += $_processedGroupByMap[$_fieldNameReference][$_distinctValue]['count'];
                    }
                }
            }

            $_nodeReference = &$_processedGroupByMap;
        } else {
            $_nodeReference[$_fieldNameReference] = array();

            foreach ($this->_sqlDistinctValueContainer[$_fieldNameReference] as $_distinctValue) {
                if (is_array($_distinctValue)) {
                    $_activeKeyPrefix = $_keyPrefix . '_' . $_fieldNameReference . ':' . $_distinctValue[0];
                    $_distinctValue = $_distinctValue[1];
                } else {
                    $_activeKeyPrefix = $_keyPrefix . '_' . $_fieldNameReference . ':' . $_distinctValue;
                }

                $_nodeReference[$_fieldNameReference][$_distinctValue] = array('count' => 0, 'childcount' => 0, 'children' => array(), 'key' => $_activeKeyPrefix);

                if (isset($this->_dataContainer[$_activeKeyPrefix])) {
                    $_nodeReference[$_fieldNameReference][$_distinctValue]['values'] = &$this->_dataContainer[$_activeKeyPrefix];
                    $_nodeReference[$_fieldNameReference][$_distinctValue]['count'] = count($this->_dataContainer[$_activeKeyPrefix]['results']);

                    $_activeCount += $_nodeReference[$_fieldNameReference][$_distinctValue]['count'];
                }
            }
        }

        $_childCount = count($_nodeReference[$_fieldNameReference]);

        if ($_nodeReference == false) {
            throw new SWIFT_Exception('Invalid Node Reference');
        }

        $_nextGroupByXFields = array_slice($_groupByXFields, 1);
        $_totalCount = 0;

        if (count($_nextGroupByXFields)) {
            foreach ($_nodeReference[$_fieldNameReference] as $_distinctValue => $_distinctValueContainer) {
                $_ndv = &$_nodeReference[$_fieldNameReference][$_distinctValue];
                $_returnedCountContainer = $this->BuildMatrixXGrid($_nextGroupByXFields, $_processedGroupByMap, $_ndv['children'], $_ndv['key']);

                $_returnedCount = $_returnedCountContainer[0];
                $_returnedChildCount = $_returnedCountContainer[1];

                $_ndv['count'] = $_returnedCount;
                $_totalCount += $_returnedCount;

                if (!isset($_ndv['childcount'])) {
                    $_ndv['childcount'] = 0;
                }
                $_ndv['childcount'] += $_returnedChildCount;
                $_childCount += $_returnedChildCount;
            }
        }

        $_finalCount = $_activeCount + $_totalCount;

        return array($_finalCount, $_childCount);
    }

    /**
     * Cleans up the grid map array
     *
     * @author Varun Shoor
     * @param array $_gridXMap
     * @param array $_groupByXFields
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CleanupMatrixXGrid($_groupByXFields, &$_gridXMap)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldName = $_groupByXFields[0][0];
        $_fieldNameReference = $_groupByXFields[0][1];

        if (!isset($_gridXMap[$_fieldNameReference])) {
            return false;
        }

        foreach ($_gridXMap[$_fieldNameReference] as $_fieldValue => $_valueMap) {
            if ($_valueMap['count'] == 0) {
                unset($_gridXMap[$_fieldNameReference][$_fieldValue]);

                continue;
            }

            if (isset($_gridXMap[$_fieldNameReference][$_fieldValue]['children']) && count($_gridXMap[$_fieldNameReference][$_fieldValue]['children'])) {
                $this->CleanupMatrixXGrid(array_slice($_groupByXFields, 1), $_gridXMap[$_fieldNameReference][$_fieldValue]['children']);
            }
        }

        if (!count($_gridXMap[$_fieldNameReference])) {
            unset($_gridXMap[$_fieldNameReference]);
        }

        return true;
    }

    /**
     * Recounts up the grid map array
     *
     * @author Varun Shoor
     * @param array $_groupByXFields
     * @param array $_gridXMap
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RecountMatrixXGrid($_groupByXFields, &$_gridXMap)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldName = $_groupByXFields[0][0];
        $_fieldNameReference = $_groupByXFields[0][1];

        if (!isset($_gridXMap[$_fieldNameReference])) {
            return 0;
        }

        $_finalCount = 0;

        /*
         * BUG FIX - Andriy Lesyuk
         *
         * SWIFT-2034 Table heading breakage (invalid colspan)
         *
         * Comments: Changed algo for colspan calculation
         */

        foreach ($_gridXMap[$_fieldNameReference] as $_fieldValue => $_valueMap) {
            if (isset($_gridXMap[$_fieldNameReference][$_fieldValue]['children']) && count($_gridXMap[$_fieldNameReference][$_fieldValue]['children'])) {
                $_returnCount = $this->RecountMatrixXGrid(array_slice($_groupByXFields, 1), $_gridXMap[$_fieldNameReference][$_fieldValue]['children']);

                $_gridXMap[$_fieldNameReference][$_fieldValue]['childcount'] = $_returnCount;

                $_finalCount += $_returnCount;
            } else {
                $_finalCount += 1;
            }
        }

        return $_finalCount;
    }

    /**
     * Recounts Up the Grid Map Array
     *
     * @author Varun Shoor
     * @param array $_gridYMap
     * @param array $_groupByYFields
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RebuildMatrixCountForYGrid(&$_gridYMap, $_groupByYFields)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldName = $_groupByYFields[0][0];
        $_fieldNameReference = $_groupByYFields[0][1];

        if (!isset($_gridYMap[$_fieldNameReference])) {
            return 0;
        }

        $_finalCount = 0;

        foreach ($_gridYMap[$_fieldNameReference] as $_fieldValue => $_valueMap) {
            $_activeValueCount = 0;

            if (isset($this->_groupByYCountMap[$_valueMap['key']])) {
                $_activeValueCount = $this->_groupByYCountMap[$_valueMap['key']];
            }

            $_finalCount += $_activeValueCount;

            if (isset($_gridYMap[$_fieldNameReference][$_fieldValue]['children']) && count($_gridYMap[$_fieldNameReference][$_fieldValue]['children'])) {
                $_returnCount = $this->RebuildMatrixCountForYGrid($_gridYMap[$_fieldNameReference][$_fieldValue]['children'], array_slice($_groupByYFields, 1));

                $_gridYMap[$_fieldNameReference][$_fieldValue]['count'] = $_activeValueCount + $_returnCount;

                $_finalCount += $_returnCount;
            } else {
                $_gridYMap[$_fieldNameReference][$_fieldValue]['count'] = $_activeValueCount;
            }
        }

        return $_finalCount;
    }

    /**
     * Cleans up the grid map array
     *
     * @author Varun Shoor
     * @param array $_gridYMap
     * @param array $_groupByYFields
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CleanupMatrixYGrid(&$_gridYMap, $_groupByYFields)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldName = $_groupByYFields[0][0];
        $_fieldNameReference = $_groupByYFields[0][1];

        if (!isset($_gridYMap[$_fieldNameReference])) {
            return false;
        }

        foreach ($_gridYMap[$_fieldNameReference] as $_fieldValue => $_valueMap) {
            if ($_valueMap['count'] == 0) {
                unset($_gridYMap[$_fieldNameReference][$_fieldValue]);

                continue;
            }

            if (isset($_gridYMap[$_fieldNameReference][$_fieldValue]['children']) && count($_gridYMap[$_fieldNameReference][$_fieldValue]['children'])) {
                $this->CleanupMatrixYGrid($_gridYMap[$_fieldNameReference][$_fieldValue]['children'], array_slice($_groupByYFields, 1));
            }
        }

        if (!count($_gridYMap[$_fieldNameReference])) {
            unset($_gridYMap[$_fieldNameReference]);
        }

        return true;
    }

    /**
     * Recounts up the grid map array
     *
     * @author Varun Shoor
     * @param array $_gridYMap
     * @param array $_groupByYFields
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RecountMatrixYGrid(&$_gridYMap, $_groupByYFields)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldName = $_groupByYFields[0][0];
        $_fieldNameReference = $_groupByYFields[0][1];

        if (!isset($_gridYMap[$_fieldNameReference])) {
            return 0;
        }

        $_finalCount = 0;

        /*
         * BUG FIX - Andriy Lesyuk
         *
         * SWIFT-1856 Summary table report breakage in example report
         *
         * Comments: Changed algo (copied from RecountMatrixXGrid)
         */

        foreach ($_gridYMap[$_fieldNameReference] as $_fieldValue => $_valueMap) {
            if (isset($_gridYMap[$_fieldNameReference][$_fieldValue]['children']) && count($_gridYMap[$_fieldNameReference][$_fieldValue]['children'])) {
                $_returnCount = $this->RecountMatrixYGrid($_gridYMap[$_fieldNameReference][$_fieldValue]['children'], array_slice($_groupByYFields, 1));

                $_gridYMap[$_fieldNameReference][$_fieldValue]['childcount'] = $_returnCount;

                $_finalCount += $_returnCount;
            } else {
                $_finalCount += 1;
            }
        }

        return $_finalCount;
    }

    /**
     * Check to see if its a valid export format
     *
     * @author Parminder Singh
     * @param mixed $_exportFormat
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidExportFormat($_exportFormat)
    {
        return ($_exportFormat == self::EXPORT_EXCEL || $_exportFormat == self::EXPORT_EXCEL5 || $_exportFormat == self::EXPORT_CSV || $_exportFormat == self::EXPORT_HTML);
    }

    /**
     * Increment the record count
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function IncrementRecordCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_recordCount++;

        return true;
    }

    /**
     * Retrieves the record count
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRecordCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_recordCount;
    }

    /**
     * Set PDO Session Time Zone to SWIFT Time Zone
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetSessionTimeZone()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_mysqlTimeZone = $this->Database->QueryFetch('SELECT @@SESSION.time_zone AS time_zone');
        if ($_mysqlTimeZone && isset($_mysqlTimeZone['time_zone'])) {
            $this->_backupTimeZone = $_mysqlTimeZone['time_zone'];
        }

        $_swiftTimeZone = SWIFT::Get('timezone');

        $_SWIFT_StaffObject = self::GetStaff();
        if ($_SWIFT_StaffObject->GetProperty('timezonephp') != '') {
            $_swiftTimeZone = $_SWIFT_StaffObject->GetProperty('timezonephp');
        }

        if ($_swiftTimeZone) {
            $_timeZoneObject = new DateTimeZone($_swiftTimeZone);
            $_secondsOffset = $_timeZoneObject->getOffset(new DateTime());
            $_newTimeZone = sprintf("%s%02d:%02d", ($_secondsOffset < 0) ? '-' : '+', floor(abs($_secondsOffset) / 3600), floor((abs($_secondsOffset) % 3600) / 60));

            $this->Database->Query("SET SESSION TIME_ZONE = '" . $this->Database->Escape($_newTimeZone) . "'");
        } else {
            $this->_backupTimeZone = false;

            return false;
        }

        return true;
    }

    /**
     * Restore PDO Session Time Zone to the Previous Value
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RestoreSessionTimeZone()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_backupTimeZone) {
            $this->Database->Query("SET SESSION TIME_ZONE = '" . $this->Database->Escape($this->_backupTimeZone) . "'");
        }

        return true;
    }

    /**
     * Retrieve Staff Running the Report
     *
     * @author Andriy Lesyuk
     * @return SWIFT_Staff The Staff Running Report
     * @throws SWIFT_Exception
     */
    public static function GetStaff()
    {
        if (SWIFT::Get('schedulestaffid')) {
            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID(SWIFT::Get('schedulestaffid')));

            if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
                return $_SWIFT_StaffObject;
            }
        }
        return SWIFT::GetInstance()->Staff;
    }

    /**
     * Determine the Format of Report
     *
     * @author Andriy Lesyuk
     * @return mixed The Report Type
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetFormat()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return self::EXPORT_CSV;
    }

    /**
     * Removes HTML tags from the string
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string|bool $_columnName
     * @param array $_results
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Field_ProcessHTMLContents($_fieldValue, $_columnName = false, $_results = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_results[$_columnName . '_ishtml']) && $_results[$_columnName . '_ishtml']) {
            $_fieldValue = SWIFT_TicketPost::GetParsedContents($_fieldValue, 'strip', true, '');
        }

        return $_fieldValue;
    }

    /**
     * Renders Link in the Report
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string $_fieldURL
     * @param array $_additionalInfo
     * @return mixed String or True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function WriteFieldURL($_fieldValue, $_fieldURL, $_additionalInfo = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        switch ($this->GetFormat()) {
            case self::EXPORT_HTML:
                return '<a href="' . htmlspecialchars($_fieldURL) . '">' . $_fieldValue . '</a>';
                break;

            case self::EXPORT_EXCEL:
            case self::EXPORT_EXCEL5:
                if (isset($_additionalInfo['columnIndex'])) {
                    $this->_workSheet->getCellByColumnAndRow($_additionalInfo['columnIndex'], $this->GetRecordCount() + 1)->getHyperlink()->setUrl($_fieldURL);

                    return true;
                } else {
                    return false;
                }
                break;
        }

        return $_fieldValue;
    }

    /**
     * Renders Link to the Ticket for the Ticket Mask ID
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string|bool $_columnName
     * @param array $_results
     * @param array $_additionalInfo
     * @return mixed String or True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Field_WriteTicketMaskID($_fieldValue, $_columnName = false, $_results = array(), $_additionalInfo = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4036: Incorrect ticket URL in scheduled report
         *
         * Comments: Replaced interfaces with staff.
         */
        $_baseURL = str_replace(['/cron', '/console', '/admin', '/api'], '/staff', SWIFT::Get('basename'));

        if (isset($_results[$_columnName . '_ticketid'])) {
            $_ticketURL = $_baseURL . '/Tickets/Ticket/View/' . $_results[$_columnName . '_ticketid'];
        } else {
            $_ticketURL = $_baseURL . '/Tickets/Ticket/View/' . $_fieldValue;
        }

        return $this->WriteFieldURL($_fieldValue, $_ticketURL, $_additionalInfo);
    }

    /**
     * Renders Link to the Ticket for the Ticket ID
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string|bool $_columnName
     * @param array $_results
     * @param array $_additionalInfo
     * @return mixed String or True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Field_WriteTicketID($_fieldValue, $_columnName = false, $_results = array(), $_additionalInfo = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4036: Incorrect ticket URL in scheduled report
         *
         * Comments: Replaced interfaces with staff.
         */
        $_baseURL = str_replace(['/cron', '/console', '/admin', '/api'], '/staff', SWIFT::Get('basename'));

        $_ticketURL = $_baseURL . '/Tickets/Ticket/View/' . $_fieldValue;

        return $this->WriteFieldURL($_fieldValue, $_ticketURL, $_additionalInfo);
    }

    /**
     * Renders Link to the Chat for the Chat Mask ID
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string|bool $_columnName
     * @param array $_results
     * @param array $_additionalInfo
     * @return mixed String or True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Field_WriteChatMaskID($_fieldValue, $_columnName = false, $_results = array(), $_additionalInfo = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4036: Incorrect ticket URL in scheduled report
         *
         * Comments: Replaced interfaces with staff.
         */
        $_baseURL = str_replace(['/cron', '/console', '/admin', '/api'], '/staff', SWIFT::Get('basename'));

        if (isset($_results[$_columnName . '_chatobjectid'])) {
            $_chatURL = $_baseURL . '/LiveChat/ChatHistory/ViewChat/' . $_results[$_columnName . '_chatobjectid'];

            return $this->WriteFieldURL($_fieldValue, $_chatURL, $_additionalInfo);
        }

        return $_fieldValue;
    }

    /**
     * Renders Link to the Chat for the Chat ID
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string|bool $_columnName
     * @param array $_results
     * @param array $_additionalInfo
     * @return mixed String or True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Field_WriteChatID($_fieldValue, $_columnName = false, $_results = array(), $_additionalInfo = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4036: Incorrect ticket URL in scheduled report
         *
         * Comments: Replaced interfaces with staff.
         */
        $_baseURL = str_replace(['/cron', '/console', '/admin', '/api'], '/staff', SWIFT::Get('basename'));

        $_chatURL = $_baseURL . '/LiveChat/ChatHistory/ViewChat/' . $_fieldValue;

        return $this->WriteFieldURL($_fieldValue, $_chatURL, $_additionalInfo);
    }

    /**
     * Renders Link to the Message for the Message Mask ID
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string|bool $_columnName
     * @param array $_results
     * @param array $_additionalInfo
     * @return mixed String or True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Field_WriteMessageMaskID($_fieldValue, $_columnName = false, $_results = array(), $_additionalInfo = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4036: Incorrect ticket URL in scheduled report
         *
         * Comments: Replaced interfaces with staff.
         */
        $_baseURL = str_replace(['/cron', '/console', '/admin', '/api'], '/staff', SWIFT::Get('basename'));


        if (isset($_results[$_columnName . '_messageid'])) {
            $_messageURL = $_baseURL . '/LiveChat/Message/ViewMessage/' . $_results[$_columnName . '_messageid'];

            return $this->WriteFieldURL($_fieldValue, $_messageURL, $_additionalInfo);
        }

        return $_fieldValue;
    }

    /**
     * Renders Link to the Message for the Message ID
     *
     * @author Andriy Lesyuk
     * @param mixed $_fieldValue
     * @param string|bool $_columnName
     * @param array $_results
     * @param array $_additionalInfo
     * @return mixed String or True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Field_WriteMessageID($_fieldValue, $_columnName = false, $_results = array(), $_additionalInfo = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4036: Incorrect ticket URL in scheduled report
         *
         * Comments: Replaced interfaces with staff.
         */
        $_baseURL     = str_replace(['/cron', '/console', '/admin', '/api'], '/staff', SWIFT::Get('basename'));

        $_messageURL = $_baseURL . '/LiveChat/Message/ViewMessage/' . $_fieldValue;

        return $this->WriteFieldURL($_fieldValue, $_messageURL, $_additionalInfo);
    }

    private function getJoinTale($sql, $table) {
        $joinPart = self::LEFT_JOIN.' '.$table;
        $startPos = strpos($sql, $joinPart);
        $endPos = strpos($sql, self::LEFT_JOIN, $startPos + strlen($joinPart));
        if (!$endPos) {
            $endPos = strpos($sql, self::WHERE_COND, $startPos + strlen($joinPart));
        }
        if ($startPos > 0 && $endPos > $startPos) {
            return substr($sql, $startPos, $endPos - $startPos);
        }
    }

    protected function correctTagCondition($sql)
    {
        $sql = preg_replace(self::REDUNDANT_TAGS_IN_FROM_RE, '', $sql);
        preg_match_all(self::WHERE_RE, $sql, $whereMatches, PREG_SET_ORDER, 0);
        if (empty($whereMatches)) {
            return $sql;
        }
        $where = end($whereMatches[0]);
        preg_match_all(self::TAG_COND_RE, $where, $tagMatches, PREG_SET_ORDER, 0);
        if (empty($tagMatches)) {
            return $sql;
        }

        $tagLinkJoin = $this->getJoinTale($sql, 'swtaglinks');
        preg_match_all(self::JOIN_COND, $tagLinkJoin, $joinMatches, PREG_SET_ORDER, 0);
        if (empty($joinMatches)) {
            return $sql;
        }
        $tagLinkCondition =  $joinMatches[0][1];
        $whereTag = '';
        $tagCondition = $tagMatches[0][0];

        preg_match_all(self::TAG_PART_COND_RE, $tagCondition, $tagPartMatches, PREG_SET_ORDER, 0);
        $index = 0;
        foreach ($tagPartMatches as $tagPartMatch) {
            if ($index == 0) {
                $whereTag = $tagPartMatch[0].' ';
            } else {
                $condition = preg_replace('/\d+\./m', '.', $tagPartMatch[0]);
                $whereTag = $whereTag.self::TAG_SUB_JOIN.$tagLinkCondition.' AND '.$condition.') ';
            }
            $index += 1;
        }
        return preg_replace(self::TAG_COND_RE, $whereTag, $sql);
    }
}
