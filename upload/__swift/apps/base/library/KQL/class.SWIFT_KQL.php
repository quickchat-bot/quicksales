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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\KQL;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Models\CustomField\SWIFT_CustomField;
use SQL_Parser;
use SQL_Parser_Lexer;
use SWIFT;
use SWIFT_Exception;
use SWIFT_FileManager;
use Base\Library\KQL\SWIFT_KQLSchema;
use SWIFT_Library;

/**
 * The KQL Base Class
 *
 * @author Varun Shoor
 */
class SWIFT_KQL extends SWIFT_Library # FIXME
{
    static protected $_allowedFunctionList = array('count', 'sum', 'avg', 'min', 'max');

    /**
     * The Extended Functions. If you add anything, update the SWIFT_KQLParser::ParseExtendedFunction and Parser::$_extendedFunctions
     */
    static protected $_allowedExtendedFunctionList = array('customfield', 'yesterday', 'today', 'tomorrow', 'last7days', 'lastweek', 'thisweek', 'nextweek', 'lastmonth', 'thismonth', 'nextmonth', 'endofweek', 'mktime', 'datenow', 'month', 'datediff', 'monthrange');
    static protected $_extendedFunctionList = array('Today()' => 'Today()', 'Yesterday()' => 'Yesterday()', 'Tomorrow()' => 'Tomorrow()', 'Last7Days()' => 'Last7Days()', 'LastWeek()' => 'LastWeek()',
        'ThisWeek()' => 'ThisWeek()', 'NextWeek()' => 'NextWeek()', 'LastMonth()' => 'LastMonth()', 'ThisMonth()' => 'ThisMonth()', 'NextMonth()' => 'NextMonth()', 'EndOfWeek()' => 'EndOfWeek()', 'DateNow()' => 'DateNow()');

    static protected $_extendedClauses = array(SWIFT_KQLSchema::FIELDTYPE_SECONDS => array('Minute', 'Hour', 'Day'),
        SWIFT_KQLSchema::FIELDTYPE_UNIXTIME => array('Hour', 'Day', 'DayName', 'Week', 'WeekDay', 'Month', 'MonthName', 'Quarter', 'Year'));

    /**
     * Separate variable for extended functions supported by custom fields (and their types)
     */
    static protected $_extendedCustomFieldClauses = array(SWIFT_CustomField::TYPE_DATE => array('Hour', 'Day', 'DayName', 'Week', 'WeekDay', 'Month', 'MonthName', 'Quarter', 'Year'));

    /**
     * Operators
     */
    static protected $_allowedTextOperators = array('in', 'not in', 'like', 'not like');
    static protected $_allowedBasicOperators = array('=', '!=', '>', '<', '>=', '<=');

    static protected $_disallowedColumns = array('staffpassword', 'userpassword');

    protected $_schemaContainer = array();

    /**
     * @var SQL_Parser
     */
    protected $SQLParser;

    /**
     * @var    SQL_Parser_Lexer
     * @access public
     */
    protected $Lexer;

    /**
     * @var    string
     * @access public
     */
    protected $_token;

    public $_symbols = array();
    public $_comments = array();
    public $_quotes = array();

    protected $_activeCaretPosition = 0;

    /**
     * This array contains the map of table label > table name
     */
    protected $_tableSchemaMap = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        require_once('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THIRDPARTY_DIRECTORY . '/SQLParser/Parser.php');
        require_once('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THIRDPARTY_DIRECTORY . '/SQLParser/Parser/Lexer.php');

        $this->SQLParser = new SQL_Parser(null, SQL_Parser::DIALECT_MYSQL, $this);

        // Load up the combined schema
        $this->_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        // Process the table schema map
        $this->ProcessTableSchemaMap();
    }

    /**
     * Retrieve the combined function list with each function name in lower case
     *
     * @author Varun Shoor
     * @return array The Function List
     */
    public static function GetCombinedFunctionList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalFunctionList = array();

        $_finalFunctionList = array_merge($_finalFunctionList, self::$_allowedFunctionList, self::$_allowedExtendedFunctionList);

        foreach (self::$_extendedClauses as $_extendedClauseType) {
            $_finalFunctionList = array_merge($_finalFunctionList, $_extendedClauseType);
        }

        foreach ($_finalFunctionList as $_key => $_val) {
            $_finalFunctionList[$_key] = strtolower($_val);
        }

        return $_finalFunctionList;
    }

    /**
     * Retrieve Token from Lex
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function NextToken()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_token = $this->Lexer->lex();

        return true;
    }

    /**
     * Retrieve Token from Lex
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function NextAjaxToken()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_token = $this->Lexer->lex();
        $_tokenLength = strlen($this->Lexer->tokText);

        $this->_activeCaretPosition += $_tokenLength;

        return true;
    }

    /**
     * Push back the token
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function PushBack()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Push back the lexical parser and reset the caret position to original
        $this->Lexer->pushBack();
        $this->_activeCaretPosition -= strlen($this->Lexer->tokText);

        return true;
    }

    /**
     * Initialize the Lexer
     *
     * @author Varun Shoor
     * @param string $_lexContents
     * @param bool $_skipSpaces
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function InitializeLexer($_lexContents, $_skipSpaces = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Initialize the Lexer with a 3-level look-back buffer
        $this->Lexer = new SQL_Parser_Lexer($_lexContents, 3, array('allowIdentFirstDigit' => true), $_skipSpaces);
        $this->Lexer->symbols =& $this->_symbols;
        $this->Lexer->comments =& $this->_comments;
        $this->Lexer->quotes =& $this->_quotes;

        return true;
    }

    /**
     * Process the table schema map
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTableSchemaMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach ($this->_schemaContainer as $_tableName => $_tableContainer) {
            if (!isset($_tableContainer[SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
                continue;
            }

            $_labelValue = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableContainer[SWIFT_KQLSchema::SCHEMA_TABLELABEL]));

            $this->_tableSchemaMap[$_labelValue] = $_tableName;
        }

        return true;
    }

    /**
     * Attempt to retrieve the table name on table label
     *
     * @author Varun Shoor
     * @param string $_tableLabel
     * @return bool|string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTableNameOnLabel($_tableLabel)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_tableLabel)) {
            return false;
        }

        $_tableLabel = mb_strtolower(trim($_tableLabel));

        // Is it in schema?
        if (isset($this->_schemaContainer[$_tableLabel])) {
            return $_tableLabel;
        }

        // Check the label map
        if (isset($this->_tableSchemaMap[$_tableLabel])) {
            return $this->_tableSchemaMap[$_tableLabel];
        }

        // Return as is
        return $_tableLabel;
    }

    /**
     * Retrieve the field name on label
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_fieldLabel
     * @return string|bool "Field Name" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetFieldNameOnLabel($_tableName, $_fieldLabel)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldLabel = mb_strtolower(trim($_fieldLabel));
        $_tableName = mb_strtolower(trim($_tableName));

        // Confirm that the table is set
        if (!isset($this->_schemaContainer[$_tableName]) || empty($_fieldLabel)) {
            return false;
        }

        // Is the field set for the given table?
        if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldLabel])) {
            return $_fieldLabel;

            // Otherwise we have to look under each field
        } else {
            foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_fieldName => $_fieldContainer) {
                // Try on a combination of table name and field name
                $_activeFieldLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_fieldName));
                if (!empty($_activeFieldLabel) && $_activeFieldLabel == $_fieldLabel) {
                    return $_fieldName;
                } else {
                    // Try on just the field name
                    $_activeFieldLabel = mb_strtolower(SWIFT_KQLSchema::GetLabel($_fieldName));
                    if (!empty($_activeFieldLabel) && $_activeFieldLabel == $_fieldLabel) {
                        return $_fieldName;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Retrieve the Table and Field Name on Text Value
     *
     * @author Varun Shoor
     * @param string $_activeFieldText
     * @return array array(tablename, fieldname)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTableAndFieldNameOnText($_activeFieldText)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_activeFieldText = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\()/i', '/(\))$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_activeFieldText)));

        // The field text has a table name in it?
        $_tableLabel = $this->GetPrimaryTableName();
        $_fieldLabel = '';
        if (strpos($_activeFieldText, '.')) {
            $_tableLabel = mb_strtolower(substr($_activeFieldText, 0, strpos($_activeFieldText, '.')));
            $_fieldLabel = mb_strtolower(substr($_activeFieldText, strpos($_activeFieldText, '.') + 1));
        } else {
            $_fieldLabel = mb_strtolower($_activeFieldText);
        }

        $_tableName = $this->GetTableNameOnLabel($_tableLabel);
        $_fieldName = $this->GetFieldNameOnLabel($_tableName, $_fieldLabel);

        if (empty($_fieldName)) {
            $_fieldName = $_fieldLabel;
        }

        return array($_tableName, $_fieldName);
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

        return '';
    }

    /**
     * Get the parsed distinct value
     *
     * @author Varun Shoor
     * @param string $_fieldNameReference
     * @param string $_distinctValue
     * @return bool|string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetParsedDistinctValue($_fieldNameReference, $_distinctValue) # FIXME: obsolete / replace with GetParsedColumnValue()/RenderColumnValue()/ExportColumnValue()/SetCellValue()/SetColumnValue()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_functionList = SWIFT_KQL::GetCombinedFunctionList();

        if (!strstr($_fieldNameReference, '_')) {
            return $_distinctValue;
        }

        $_keyChunks = explode('_', strtolower($_fieldNameReference));
        $_tableName = $_columnName = '';

        // No chunks? Probably custom
        if (!count($_keyChunks)) {
            return $_distinctValue;
        }

        // Is the first chunk the name of a function
        if (in_array($_keyChunks[0], $_functionList) && count($_keyChunks) == 3 && isset($this->_schemaContainer[$_keyChunks[1]]) && isset($this->_schemaContainer[$_keyChunks[1]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[2]])) {
            $_tableName = $_keyChunks[1];
            $_columnName = $_keyChunks[2];

            $_fieldContainer = $this->_schemaContainer[$_keyChunks[1]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[2]];
            if ($_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_CUSTOM && isset($_fieldContainer[SWIFT_KQLSchema::FIELD_CUSTOMVALUES])) {
                foreach ($_fieldContainer[SWIFT_KQLSchema::FIELD_CUSTOMVALUES] as $_actualValue => $_languageString) {
                    if (mb_strtolower($_distinctValue) == $_actualValue) {
                        $_labelResult = SWIFT_KQLSchema::GetLabel($_languageString);
                        if (!empty($_labelResult)) {
                            return $_labelResult;
                        }
                    }
                }
            }

        } elseif (count($_keyChunks) == 2 && isset($this->_schemaContainer[$_keyChunks[0]]) && isset($this->_schemaContainer[$_keyChunks[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[1]])) {
            $_tableName = $_keyChunks[0];
            $_columnName = $_keyChunks[1];

            $_fieldContainer = $this->_schemaContainer[$_keyChunks[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[1]];
            if ($_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_CUSTOM && isset($_fieldContainer[SWIFT_KQLSchema::FIELD_CUSTOMVALUES])) {
                foreach ($_fieldContainer[SWIFT_KQLSchema::FIELD_CUSTOMVALUES] as $_actualValue => $_languageString) {
                    if (mb_strtolower($_distinctValue) == $_actualValue) {
                        $_labelResult = SWIFT_KQLSchema::GetLabel($_languageString);
                        if (!empty($_labelResult)) {
                            return $_labelResult;
                        }
                    }
                }
            }

        }

        return $_distinctValue;
    }

    /**
     * Gets Parsed Custom Field Value
     *
     * @author Andriy Lesyuk
     * @param string $_customFieldValue
     * @param array $_customField
     * @param bool $_isEncrypted
     * @param bool $_isSerialized
     * @return string New Value
     */
    public static function GetParsedCustomFieldValue($_customFieldValue, $_customField, $_isEncrypted = false, $_isSerialized = false)
    {
        if (!$_customFieldValue) {
            return $_customFieldValue;
        }

        if ($_isEncrypted) {
            $_customFieldValue = SWIFT_CustomFieldManager::Decrypt($_customFieldValue);
        }
        if ($_isSerialized) {
            $_customFieldValue = mb_unserialize($_customFieldValue);
        }

        if (_is_array($_customFieldValue) &&
            (($_customField['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE))) {

            foreach ($_customFieldValue as $_fieldIndex => $_optionIndex) {
                if (isset($_customField['options'][$_optionIndex])) {
                    $_customFieldValue[$_fieldIndex] = $_customField['options'][$_optionIndex];
                }
            }
        } elseif
        (($_customField['type'] == SWIFT_CustomField::TYPE_RADIO) ||
            ($_customField['type'] == SWIFT_CustomField::TYPE_SELECT)) {

            if (isset($_customField['options'][$_customFieldValue])) {
                $_customFieldValue = $_customField['options'][$_customFieldValue];
            }
        } elseif ($_customField['type'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
            /*
             * BUG FIX - Andriy Lesyuk
             *
             * SWIFT-4096: A warning is displayed on running a matrix report with linked select custom field in Group By clause
             *
             * Comment: We just return non-arrays as they are
             */
            if (!is_array($_customFieldValue)) {
                return $_customFieldValue;
            }
            $_parentIndex = false;
            $_linkedSelectValue = array();
            foreach ($_customFieldValue as $_fieldIndex => $_optionIndex) {
                if (is_array($_optionIndex)) {
                    foreach ($_optionIndex as $_subIndex => $_suboptionIndex) {
                        if (($_subIndex == $_parentIndex) && // FIX
                            isset($_customField['options'][$_subIndex]) &&
                            isset($_customField['options'][$_subIndex]['suboptions'][$_suboptionIndex])) {

                            $_linkedSelectValue[] = $_customField['options'][$_subIndex]['suboptions'][$_suboptionIndex]['value'];
                        }
                    }
                    $_parentIndex = false;
                } else {
                    if (isset($_customField['options'][$_optionIndex])) {
                        $_linkedSelectValue[] = $_customField['options'][$_optionIndex]['value'];
                    }
                    $_parentIndex = $_optionIndex;
                }
            }
            $_customFieldValue = implode(' » ', $_linkedSelectValue);
        } elseif ($_customField['type'] == SWIFT_CustomField::TYPE_FILE) {
            try {
                $_SWIFT_FileManagerObject = new SWIFT_FileManager($_customFieldValue);

                $_customFieldValue = $_SWIFT_FileManagerObject->GetProperty('originalfilename'); // TODO: test
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        if (is_array($_customFieldValue)) {
            $_customFieldValue = implode(', ', $_customFieldValue);
        }

        return $_customFieldValue;
    }

}

?>
