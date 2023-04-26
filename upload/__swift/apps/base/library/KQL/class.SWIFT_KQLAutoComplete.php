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
use Exception;
use SWIFT;
use SWIFT_Exception;
use Base\Library\KQL\SWIFT_KQLParser;
use Base\Library\KQL\SWIFT_KQLSchema;

/**
 * The KQL Auto Completer Class
 *
 * @author Varun Shoor
 * @property SWIFT_KQLParser $KQLParser
 */
class SWIFT_KQLAutoComplete extends SWIFT_KQL
{
    const MODE_NONE = 0;
    const MODE_FIELDNAME = 1;
    const MODE_FIELDOP = 2;
    const MODE_FIELDVALUE = 3;
    const MODE_FIELDGLUE = 4;
    const MODE_ORDERBY = 5;
    const MODE_GROUPBY = 6;
    const MODE_SELECT = 7;
    const MODE_FROM = 8;
    const MODE_CUSTOMFIELD = 9;

    const CAP_FIELDNAMES = 10;

    const VALUETYPE_INT = 1; // 1234
    const VALUETYPE_FLOAT = 2; // 1234.567
    const VALUETYPE_STRING = 3; // 'test yay!'
    const VALUETYPE_FUNCTION = 4; // CurrentYearToDate()
    const VALUETYPE_GROUP = 5; // (1, 2, 3)
    const VALUETYPE_STRINGNONQUOTED = 6; // testyay

    protected $_activeMode = self::MODE_NONE;
    protected $_activeField = false;
    protected $_activeOperator = false;
    protected $_activeValue = '';
    protected $_activeValueType = false;

    protected $_previousMode = self::MODE_NONE;

    protected $_usedExtendedGlues = array();

    const GLUE_AND = 'and';
    const GLUE_OR = 'or';
    const GLUE_ORDERBY = 'order by';
    const GLUE_GROUPBY = 'group by';

    protected $_kqlStatement = '';
    protected $_kqlStatementPrefix = '';
    protected $_primaryTableName = '';
    protected $_baseTableList = array();
    protected $_start = 0;
    protected $_end = 0;
    protected $_textSelection = '';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_kqlStatement
     * @param string $_kqlStatementPrefix
     * @param string $_primaryTableName
     * @param array $_baseTableList
     * @param int $_start
     * @param int $_end
     * @param string $_textSelection
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_kqlStatement, $_kqlStatementPrefix, $_primaryTableName, $_baseTableList, $_start, $_end, $_textSelection)
    {
        parent::__construct();


        $this->Load->Library('KQL:KQLParser', [], true, false, 'base');

        $this->_kqlStatement = $_kqlStatement;
        $this->_kqlStatementPrefix = $_kqlStatementPrefix;
        $this->_primaryTableName = $_primaryTableName;
        $this->_baseTableList = $_baseTableList;
        $this->_start = $_start;
        $this->_end = $_end;
        $this->_textSelection = $_textSelection;
    }

    /**
     * Retrieve JSON Options
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveOptionsJSON()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_parseResult = true;
        $_resultMessage = $_debugContents = '';

        $_kqlResults = array();
        try {
            $_kqlResults = $this->RetrieveOptions();
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_parseResult = false;
            $_resultMessage = $_SWIFT_ExceptionObject->getMessage();
        }


        $_kqlStatementList = array();
        if ($_parseResult) {
            try {
                $_kqlStatementList = $this->KQLParser->ParseStatement($this->_kqlStatementPrefix . $this->_kqlStatement);
            } catch (Exception $_SWIFT_ExceptionObject) {
                $_parseResult = false;
                $_resultMessage = $_SWIFT_ExceptionObject->getMessage();
            }
        }

        $_start = $_end = -1;
        if (isset($_kqlResults[0]) && isset($_kqlResults[1])) {
            $_start = $_kqlResults[0];
            $_end = $_kqlResults[1];
        }

        $_jsonData = array();
        if (isset($_kqlResults[2]) && _is_array($_kqlResults[2])) {
            foreach ($_kqlResults[2] as $_dataContainer) {
                $_jsonData[] = array(
                    'label' => $_dataContainer[0],
                    'value' => $_dataContainer[1]
                );
            }
        }

        $_jsonArray = array(
            'result' => $_parseResult,
            'message' => $_resultMessage,
            'start' => $_start,
            'end' => $_end,
            'data' => $_jsonData,
            'debug' => $_debugContents
        );

        return json_encode($_jsonArray);
    }

    /**
     * Retrieve the Probable Ajax Options
     *
     * @author Varun Shoor
     * @return array The AJAX Options
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveOptions()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_kqlStatement = $this->_kqlStatement;
        $_kqlStatementPrefix = $this->_kqlStatementPrefix;
        $_primaryTableName = $this->_primaryTableName;
        $_baseTableList = $this->_baseTableList;
        $_start = $this->_start;
        $_end = $this->_end;
        $_textSelection = $this->_textSelection;

        $_ajaxOptions = array();

        $_finalKQLStatement = $_kqlStatementPrefix . $_kqlStatement;

        $_kqlStatementList = array();

        $this->InitializeLexer($_finalKQLStatement, true);

        $_statementLength = strlen($_finalKQLStatement);
        $_prefixLength = strlen($_kqlStatementPrefix);

        echo 'StatementLength: ' . $_statementLength . SWIFT_CRLF;
        echo 'Start: ' . $_start . SWIFT_CRLF;
        echo 'End: ' . $_end . SWIFT_CRLF;

        echo '--' . print_r($_baseTableList, true) . '--';
        $_finalTableList = $this->GetTableListWithLinkedTables($_baseTableList);
        print_r($_finalTableList);

        $_enforceTableRestriction = false;
        if (count($_baseTableList)) {
            $_enforceTableRestriction = true;
        }

        if (count($_baseTableList) && !empty($_kqlStatementPrefix)) {
            $this->SetActiveMode(self::MODE_FIELDNAME);
        } else {
            $this->SetActiveMode(self::MODE_SELECT);
        }

        $this->_activeCaretPosition = 0;

        $_startOfSelectCaret = -1;
        $_startOfOperatorCaret = $_startOfFieldValueCaret = $_startOfGlueCaret = $_startOfGroupByCaret = $_startOfOrderByCaret = $_startOfFromCaret = 0;
        $_operatorPrefix = $_fieldValuePrefix = $_gluePrefix = $_groupByPrefix = $_orderByPrefix = $_selectPrefix = $_fromPrefix = '';

        $_resetOfFieldName = $_hasParentParenthesisStarted = false;

        $_tokenContainer = array();

        $_itterationCount = 0;
        while (true) {
            if ($_itterationCount >= 100) {
                break;
            }

            $this->NextAjaxToken();

            $_tokenLength = strlen($this->Lexer->tokText);

            // The caret position excluding prefix length
            $_finalActiveCaretPosition = $this->_activeCaretPosition - $_prefixLength;

            $_isAtToken = false;
            if ($_start >= (($_finalActiveCaretPosition - $_tokenLength) + 1) && $_start <= $_finalActiveCaretPosition) {
                $_isAtToken = true;
            }

            // Reset the operator caret position counter
            if ($this->GetActiveMode() != self::MODE_FIELDOP) {
                $_startOfOperatorCaret = 0;
                $_operatorPrefix = '';
            }

            // Reset the field value caret position counter
            if ($this->GetActiveMode() != self::MODE_FIELDVALUE) {
                $_startOfFieldValueCaret = 0;
                $_fieldValuePrefix = '';
            }

            // Reset the glue value caret position counter
            if ($this->GetActiveMode() != self::MODE_FIELDGLUE) {
                $_startOfGlueCaret = 0;
                $_gluePrefix = '';
            }

            // Reset the group by caret position counter
            if ($this->GetActiveMode() != self::MODE_GROUPBY) {
                $_startOfGroupByCaret = 0;
                $_groupByPrefix = '';
            }

            // Reset the order by caret position counter
            if ($this->GetActiveMode() != self::MODE_ORDERBY && $this->GetActiveMode() != self::MODE_CUSTOMFIELD) {
                $_startOfOrderByCaret = 0;
                $_orderByPrefix = '';
            }

            // Reset the SELECT caret position counter
            if ($this->GetActiveMode() != self::MODE_SELECT && $this->GetActiveMode() != self::MODE_CUSTOMFIELD) {
                $_startOfSelectCaret = -1;
                $_selectPrefix = '';
            }

            // Reset the FROM caret position counter
            if ($this->GetActiveMode() != self::MODE_FROM) {
                $_startOfFromCaret = 0;
                $_fromPrefix = '';
            }


            echo 'IsAtToken: ' . (int)($_isAtToken) . SWIFT_CRLF;
            echo 'ActiveMode: ' . $this->GetActiveMode() . SWIFT_CRLF;

            // If we are over the prefix length, then we start looking for options
            if ($_finalActiveCaretPosition >= 0) {

                /**
                 * ---------------------------------------------
                 * Field SELECT
                 * ---------------------------------------------
                 */
                if ($this->GetActiveMode() == self::MODE_SELECT) {
                    if ($_startOfSelectCaret < 0) {
                        $_startOfSelectCaret = $_finalActiveCaretPosition - $_tokenLength;
                    }

                    $_selectPrefix .= $this->Lexer->tokText;

                    echo 'MODE: SELECT' . SWIFT_CRLF;

                    $_startOfSelectPrefix = $_selectPrefix;

                    $_lastChunk = '';
                    $_selectChunks = array();
                    $_hasMoreStatement = false;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token10: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText10: ' . $this->Lexer->tokText . SWIFT_CRLF;
                        echo 'SelectPrefix: ' . $_selectPrefix . SWIFT_CRLF;

                        if (count($_selectChunks)) {
                            $_lastChunk = trim(mb_strtolower($_selectChunks[count($_selectChunks) - 1]));
                        }

                        if ($this->Lexer->tokText == '*end of input*') {
                            break;

                        } elseif ($this->_token == 'ident' && trim(mb_strtolower($this->Lexer->tokText)) == 'customfield' &&
                            ($_start > strlen($_selectPrefix) + strlen($this->Lexer->tokText))) {
                            $this->SetActiveMode(self::MODE_CUSTOMFIELD);

                            $_selectPrefix .= $this->Lexer->tokText;

                            break;
                            // We break on space when user types in from
                        } elseif ($this->_token == 'space' && $_lastChunk == 'from') {
                            $this->SetActiveMode(self::MODE_FROM);

                            $_hasMoreStatement = true;

                            $_finalSelectPrefix = mb_strtolower(trim($_selectPrefix));

                            $this->PushBack();

                            break;
                        } else {
                            $_selectPrefix .= $this->Lexer->tokText;
                            $_selectChunks = explode(' ', preg_replace("#\s+#s", ' ', $_selectPrefix));
                        }
                    }

                    $_selectChunks = explode(' ', preg_replace("#\s+#s", ' ', $_selectPrefix));
                    $_firstChunk = mb_strtolower(trim($_selectChunks[0]));
                    if ($_firstChunk != substr('select', 0, strlen($_firstChunk))) {
                        throw new SWIFT_Exception('Invalid Start of Statement, only SELECT is supported');
                    }

                    $_fullSelectPrefixLength = strlen($_selectPrefix);

                    echo 'Start: ' . $_start . SWIFT_CRLF;
                    echo 'StartOfSelectCaret: ' . $_startOfSelectCaret . SWIFT_CRLF;
                    echo 'FinalActiveCaretPos: ' . $_finalActiveCaretPosition . SWIFT_CRLF;
                    echo 'ComparisonFinalActiveCaretPosition: ' . (($_finalActiveCaretPosition - $_tokenLength) + $_fullSelectPrefixLength) . SWIFT_CRLF;
                    echo 'SEND: Select Prefix 2: ' . $_startOfSelectPrefix . SWIFT_CRLF;

                    $_tokenCaretPosition = ($_start - $_startOfSelectCaret);

                    // Confirm if we are inside this
                    if ($_start >= $_startOfSelectCaret && $_start <= $_startOfSelectCaret + $_fullSelectPrefixLength) {
                        echo 'SEND: Select 1: ' . $_selectPrefix . SWIFT_CRLF;
                        $_finalSelectPrefix = $_selectPrefix;

                        $_resultsContainer = $this->GetProbableSelectList($_finalTableList, $_enforceTableRestriction, $_finalSelectPrefix, $_tokenCaretPosition, $_hasMoreStatement);
                        return array($_resultsContainer[0], $_resultsContainer[1], $_resultsContainer[2]);

                    } elseif ($_start >= $_startOfSelectCaret && $_start <= $_startOfSelectCaret + $_fullSelectPrefixLength && mb_strtolower(trim($_startOfSelectPrefix)) == 'select') {
                        echo 'SEND: Select 2: ' . $_selectPrefix . SWIFT_CRLF;
                        $_finalSelectPrefix = $_selectPrefix;

                        $_resultsContainer = $this->GetProbableSelectList('', $_tokenCaretPosition);
                        return array($_resultsContainer[0], $_resultsContainer[1], $_resultsContainer[2]);

                    }


                    /**
                     * ---------------------------------------------
                     * Field FROM
                     * ---------------------------------------------
                     */
                } elseif ($this->GetActiveMode() == self::MODE_FROM) {
                    if ($_startOfFromCaret == 0) {

                        if ($this->_token != 'space') {
                            $_startOfFromCaret = $_finalActiveCaretPosition - $_tokenLength;
                        } else {
                            $_startOfFromCaret = $_finalActiveCaretPosition;
                        }

                        $_fromPrefix = $this->Lexer->tokText;
                    } else {
                        $_fromPrefix .= $this->Lexer->tokText;
                    }

                    echo 'MODE: FROM' . SWIFT_CRLF;

                    $_startOfFromPrefix = $_fromPrefix;

                    $_lastChunk = '';
                    $_fromChunks = array();

                    $_hasOpenToken = $_hasOpenQuote = -1;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token11: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText11: ' . $this->Lexer->tokText . SWIFT_CRLF;
                        echo 'FromPrefix: ' . $_fromPrefix . '-' . substr(mb_strtolower($_fromPrefix), strlen($_fromPrefix) - strlen('group by')) . '-' . SWIFT_CRLF;

                        if (count($_fromChunks)) {
                            $_lastChunk = trim(mb_strtolower($_fromChunks[count($_fromChunks) - 1]));
                        }


                        // We break on space when user types in where
                        if ($this->_token == 'space' && $_lastChunk == 'where') {
                            echo 'BREAKINGONWHERE' . SWIFT_CRLF;
                            $this->SetActiveMode(self::MODE_FIELDNAME);

                            $_finalFromPrefix = mb_strtolower(trim($_fromPrefix));
                            $_resetOfFieldName = true;

                            $this->PushBack();

                            break;


                            // We break on space when user types in group by
                        } elseif ($this->_token == 'space' && substr(mb_strtolower($_fromPrefix), strlen($_fromPrefix) - strlen('group by')) == 'group by') {
                            echo 'BREAKINGONGROUPBY' . SWIFT_CRLF;
                            $this->SetActiveMode(self::MODE_GROUPBY);

                            // Add to used glues
                            $this->AddToUsedGlues(self::GLUE_GROUPBY);

                            $_finalFromPrefix = mb_strtolower(trim($_fromPrefix));
                            $_resetOfFieldName = true;

                            $this->PushBack();

                            break;


                            // We break on space when user types in order by
                        } elseif ($this->_token == 'space' && substr(mb_strtolower($_fromPrefix), strlen($_fromPrefix) - strlen('order by')) == 'order by') {
                            echo 'BREAKINGONORDERBY' . SWIFT_CRLF;
                            $this->SetActiveMode(self::MODE_ORDERBY);

                            // Add to used glues
                            $this->AddToUsedGlues(self::GLUE_ORDERBY);

                            $_finalFromPrefix = mb_strtolower(trim($_fromPrefix));
                            $_resetOfFieldName = true;

                            $this->PushBack();

                            break;

                            // We also break if last character is detected as ';'
                        } elseif (substr($_fromPrefix, -1) == ';') {
                            $this->SetActiveMode(self::MODE_NONE);

                            $_finalFromPrefix = mb_strtolower(trim($_fromPrefix));

                            $this->PushBack();

                            break;
                        } elseif ($this->Lexer->tokText == '*end of input*') {
                            break;
                        } else {
                            if (($_hasOpenQuote === false || $_hasOpenQuote === -1) && ($this->Lexer->tokText == '\'' || $this->Lexer->tokText == '"')) {
                                $_hasOpenQuote = true;
                            } elseif ($_hasOpenQuote === true && ($this->Lexer->tokText == '\'' || $this->Lexer->tokText == '"')) {
                                $_hasOpenQuote = false;
                                $_hasOpenToken = false;
                            }

                            if ($this->Lexer->tokText == ',') {
                                $_hasOpenToken = true;
                            }

                            $_fromPrefix .= $this->Lexer->tokText;
                            $_fromChunks = explode(' ', preg_replace("#\s+#s", ' ', $_fromPrefix));
                        }
                    }

                    $_fullFromPrefixLength = strlen($_fromPrefix);

                    echo 'Start: ' . $_start . SWIFT_CRLF;
                    echo 'StartOfFromCaret: ' . $_startOfFromCaret . SWIFT_CRLF;
                    echo 'FinalActiveCaretPos: ' . $_finalActiveCaretPosition . SWIFT_CRLF;
                    echo 'ComparisonFinalActiveCaretPosition: ' . (($_finalActiveCaretPosition - $_tokenLength) + $_fullFromPrefixLength) . SWIFT_CRLF;
                    echo 'SEND: From Prefix 2: ' . $_startOfFromPrefix . SWIFT_CRLF;
                    $_tokenCaretPosition = ($_start - $_startOfFromCaret);

                    // Confirm if we are inside this
                    if ($_start >= $_startOfFromPrefix && $_start <= ($_finalActiveCaretPosition - $_tokenLength) + $_fullFromPrefixLength) {
                        echo 'SEND: From 1: ' . $_fromPrefix . SWIFT_CRLF;
                        $_finalFromPrefix = $_fromPrefix;

                        $_resultsList = $this->GetProbableFromList($_primaryTableName, $_finalTableList, $_enforceTableRestriction, $_finalFromPrefix, $_tokenCaretPosition);
                        return array($_startOfFromCaret, $_startOfFromCaret + $_fullFromPrefixLength, $_resultsList);

                    } elseif ($_start >= $_startOfFromCaret && $_start <= ($_finalActiveCaretPosition - $_tokenLength) + $_fullFromPrefixLength && mb_strtolower(trim($_startOfFromPrefix)) == '') {
                        echo 'SEND: From 2: ' . $_fromPrefix . SWIFT_CRLF;
                        $_finalFromPrefix = $_fromPrefix;

                        $_resultsList = $this->GetProbableFromList($_primaryTableName, $_finalTableList, $_enforceTableRestriction, '', $_tokenCaretPosition);
                        return array($_startOfFromCaret, $_startOfFromCaret, $_resultsList);

                    }


                    /**
                     * ---------------------------------------------
                     * Field Options (WHERE)
                     * ---------------------------------------------
                     */
                    // Send Field Options
                } elseif ($_finalActiveCaretPosition == 0 && $_start == 0 && $this->GetActiveMode() == self::MODE_FIELDNAME) {
                    echo 'SEND: Field Options' . SWIFT_CRLF;

                    $_resultsList = $this->GetProbableFieldList($_finalTableList, '', $_enforceTableRestriction, false, false);

                    return array(0, 0, $_resultsList);


                    /**
                     * ---------------------------------------------
                     * End of Statement
                     * ---------------------------------------------
                     */
                } elseif (($this->GetActiveMode() == self::MODE_FIELDNAME || $this->GetActiveMode() == self::MODE_FIELDGLUE || $this->GetActiveMode() == self::MODE_NONE) && $this->_token == ';') {
                    $this->SetActiveMode(self::MODE_NONE);

                    return array(-1, -1, array());


                    /**
                     * ---------------------------------------------
                     * CUSTOMFIELD()
                     * ---------------------------------------------
                     */
                } elseif (($this->GetActiveMode() == self::MODE_CUSTOMFIELD) ||
                    (($this->GetActiveMode() == self::MODE_FIELDNAME) && ($this->_token == 'ident') && (trim(mb_strtolower($this->Lexer->tokText)) == 'customfield'))) {

                    $_customFieldPrefix = '';
                    $_startOfCustomfieldCaret = $_finalActiveCaretPosition;

                    $_prefix = false;
                    if ($this->GetPreviousMode() == self::MODE_SELECT) {
                        $_prefix = &$_selectPrefix;
//                    } elseif ($this->GetPreviousMode() == self::MODE_GROUPBY) {
//                        $_prefix = &$_groupByPrefix;
                    } elseif ($this->GetPreviousMode() == self::MODE_ORDERBY) {
                        $_prefix = &$_orderByPrefix;
                    }

                    if ($_prefix) {
                        $_prefix .= $this->Lexer->tokText;
                    }

                    if ($this->GetActiveMode() == self::MODE_FIELDNAME) {
                        $this->NextAjaxToken(); // skip (
                        $_startOfCustomfieldCaret += strlen($this->Lexer->tokText);
                    }

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token12: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText12: ' . $this->Lexer->tokText . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            $_customFieldPrefix = '';

                            break;
                        } elseif ($this->Lexer->tokText == ")") {
                            if ($this->GetActiveMode() == self::MODE_CUSTOMFIELD) {
                                if ($this->GetPreviousMode() == self::MODE_GROUPBY) {
                                    $this->PushBack();
                                }

                                $this->RestorePreviousMode();
                            } else {
                                $_customFieldArguments = explode(',', $_customFieldPrefix);
                                foreach ($_customFieldArguments as &$_customFieldArgument) {
                                    $_customFieldArgument = trim($_customFieldArgument);
                                }
                                unset($_customFieldArgument);

                                $this->SetActiveMode(self::MODE_FIELDOP, $_customFieldArguments);
                            }

                            if ($_prefix) {
                                $_prefix .= $this->Lexer->tokText;
                            }

                            break;
                        } else {
                            if ($_prefix) {
                                $_prefix .= $this->Lexer->tokText;
                            }

                            $_customFieldPrefix .= $this->Lexer->tokText;
                        }
                    }

                    unset($_prefix);

                    $_tokenCaretPosition = $_start - $_startOfCustomfieldCaret;

                    // Confirm if we are inside this
                    if (($_start >= $_startOfCustomfieldCaret) && ($_start <= ($_startOfCustomfieldCaret + strlen($_customFieldPrefix)))) {
                        echo 'SEND: CUSTOMFIELD() Arguments' . SWIFT_CRLF;

                        $_resultsContainer = $this->GetProbableCustomFieldArgumentList($_primaryTableName, $_finalTableList, $_customFieldPrefix, $_tokenCaretPosition);

                        return array($_startOfCustomfieldCaret + $_resultsContainer[0], $_startOfCustomfieldCaret + $_resultsContainer[1], $_resultsContainer[2]);
                    }


                    // We are inside a field name call but the user has started a pr
                } elseif ($this->GetActiveMode() == self::MODE_FIELDNAME && $this->_token == '(') {
                    echo 'FIELDNAME: Start of Parenthesis' . SWIFT_CRLF;
                    $this->NextAjaxToken();
                    $_hasParentParenthesisStarted = true;

                    // We are just starting on paranthesis, so we need to give the suggestions
                    if ($_isAtToken) {
//                    if ($_isAtToken && $this->Lexer->tokText == '*end of input*') {
                        echo 'SEND: Field Options - Parenthesis End of Input' . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableFieldList($_finalTableList, '', $_enforceTableRestriction, false, false);

                        return array($_finalActiveCaretPosition, $_finalActiveCaretPosition, $_resultsList);
                    } else {
                        $this->PushBack();
                    }

                    // We are inside a field name but one with quotes
                } elseif ($this->GetActiveMode() == self::MODE_FIELDNAME && ($this->_token == "'" || $this->_token == '"')) {
                    $_fieldPrefix = '';

                    $_hasStartingQuote = true;

                    $_hasEndingQuote = false;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token2: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText2: ' . $this->Lexer->tokText . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            break;
                        } elseif ($this->Lexer->tokText == "'") {
                            $this->SetActiveMode(self::MODE_FIELDOP, $_fieldPrefix);
                            $_hasEndingQuote = true;

                            break;
                        } else {
                            $_fieldPrefix .= $this->Lexer->tokText;
                        }
                    }

                    $_fullFieldPrefixLength = strlen($_fieldPrefix);

                    // Confirm if we are inside this
                    if ($_start >= (($_finalActiveCaretPosition - $_tokenLength) + 1) && $_start <= ($_finalActiveCaretPosition + strlen($_fieldPrefix))) {
                        // Is caret in middle of fieldprefix or end?
                        if ($_start < ($_finalActiveCaretPosition + strlen($_fieldPrefix))) {
                            $_prefixEnd = ($_finalActiveCaretPosition + strlen($_fieldPrefix)) - $_start;
                            $_fieldPrefix = substr($_fieldPrefix, 0, strlen($_fieldPrefix) - $_prefixEnd);

                            echo 'CUSTOMFIELDPREFIX: ' . $_fieldPrefix . SWIFT_CRLF;
                        }

                        echo 'SEND: Field Options 2 - ' . $_fieldPrefix . SWIFT_CRLF;
                        echo 'SEND: Field Options 2 FinalCaretPos - ' . $_finalActiveCaretPosition . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableFieldList($_finalTableList, $_fieldPrefix, $_enforceTableRestriction, $_hasStartingQuote, $_hasEndingQuote);

                        return array($_finalActiveCaretPosition, $_finalActiveCaretPosition + $_fullFieldPrefixLength, $_resultsList);
                    }

                    // We are inside a field name but WITHOUT quotes, so we will end up at space of EOL (WHERE)
                } elseif (($this->GetActiveMode() == self::MODE_FIELDNAME && $this->_token == 'ident')
                    || ($this->GetActiveMode() == self::MODE_FIELDNAME && $_resetOfFieldName == true && $this->_token == 'space' && $_isAtToken == true)) {

                    $_fieldPrefix = $this->Lexer->tokText;
                    $_originalFieldPrefix = $_fieldPrefix;

                    $_fieldNameAtSpaceAndReset = false;
                    if ($_resetOfFieldName == true && $this->_token == 'space' && $_isAtToken == true) {
                        $_fieldNameAtSpaceAndReset = true;
                    }

                    $_resetOfFieldName = false;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token3: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText3: ' . $this->Lexer->tokText . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            break;
                        } elseif ($this->_token == 'space') {
                            $this->SetActiveMode(self::MODE_FIELDOP, $_fieldPrefix);
                            break;
                        } else {
                            $_fieldPrefix .= $this->Lexer->tokText;
                        }
                    }

                    $_fullFieldPrefixLength = strlen($_fieldPrefix);

                    // Confirm if we are inside this
                    if ($_start >= (($_finalActiveCaretPosition - $_tokenLength) + 1) && $_start <= ($_finalActiveCaretPosition + (strlen($_fieldPrefix) - strlen($_originalFieldPrefix)))) {
                        // Is caret in middle of fieldprefix or end?

                        if ($_start < (($_finalActiveCaretPosition + (strlen($_fieldPrefix)) - strlen($_originalFieldPrefix)))) {
                            $_prefixEnd = ($_finalActiveCaretPosition - strlen($_originalFieldPrefix) + strlen($_fieldPrefix)) - $_start;
                            $_fieldPrefix = substr($_fieldPrefix, 0, strlen($_fieldPrefix) - $_prefixEnd);

                            echo 'CUSTOMFIELDPREFIX: ' . $_fieldPrefix . SWIFT_CRLF;
                        }

                        echo 'SEND: Field Options 3 - ' . $_fieldPrefix . SWIFT_CRLF;

                        // Reset on space
                        if (trim($_fieldPrefix) == '') {
                            $_fieldPrefix = '';
                        }

                        $_resultsList = $this->GetProbableFieldList($_finalTableList, $_fieldPrefix, $_enforceTableRestriction, false, false);

                        $_workerTokenLength = $_tokenLength;
                        if ($_fieldNameAtSpaceAndReset) {
                            $_workerTokenLength = 0;
                        }

                        return array($_finalActiveCaretPosition - $_workerTokenLength, ($_finalActiveCaretPosition - $_workerTokenLength) + $_fullFieldPrefixLength, $_resultsList);
                    }

                    /**
                     * ---------------------------------------------
                     * Field Operator
                     * ---------------------------------------------
                     */
                } elseif ($this->GetActiveMode() == self::MODE_FIELDOP && $this->GetActiveField() != false && self::IsOperatorToken($this->_token, $this->Lexer->tokText, $_tokenContainer)) {
                    if ($_startOfOperatorCaret == 0) {
                        $_startOfOperatorCaret = $_finalActiveCaretPosition - $_tokenLength;

                        $_operatorPrefix = $this->Lexer->tokText;
                    } else {
                        $_operatorPrefix .= $this->Lexer->tokText;
                    }

                    echo 'MODE: FieldOp' . SWIFT_CRLF;

                    $_startOfOperatorPrefix = $_operatorPrefix;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token4: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText4: ' . $this->Lexer->tokText . SWIFT_CRLF;
                        echo 'OperatorPrefix: ' . $_operatorPrefix . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            break;

                            // Break on paranthesis?
                        } elseif ($this->_token == '(') {
                            $_finalOperatorPrefix = mb_strtolower(trim($_operatorPrefix));

                            echo 'FIELDOP: Paranthesis Check: ' . $_finalOperatorPrefix . SWIFT_CRLF;

                            if (in_array($_finalOperatorPrefix, self::$_allowedTextOperators)) {
                                $this->SetActiveOperator($_finalOperatorPrefix);
                                $this->SetActiveMode(self::MODE_FIELDVALUE);
                                $this->PushBack();

                                break;
                            }


                            // We break on space unless its an incomplete operator
                        } elseif ($this->_token == 'space' && trim(mb_strtolower($_operatorPrefix)) != 'not' && trim($_operatorPrefix) != '') {
                            $this->SetActiveMode(self::MODE_FIELDVALUE);

                            $_finalOperatorPrefix = mb_strtolower(trim($_operatorPrefix));

                            if (in_array($_finalOperatorPrefix, self::$_allowedBasicOperators) || in_array($_finalOperatorPrefix, self::$_allowedTextOperators)) {
                                $this->SetActiveOperator($_finalOperatorPrefix);
                            }

                            $this->PushBack();

                            break;
                        } else {
                            $_operatorPrefix .= $this->Lexer->tokText;
                        }
                    }

                    $_fullOperatorPrefixLength = strlen($_operatorPrefix);


                    echo 'Start: ' . $_start . SWIFT_CRLF;
                    echo 'StartOfOperatorCaret: ' . $_startOfOperatorCaret . SWIFT_CRLF;
                    echo 'FinalActiveCaretPos: ' . $_finalActiveCaretPosition . SWIFT_CRLF;
                    echo 'ComparisonFinalActiveCaretPosition: ' . (($_finalActiveCaretPosition - $_tokenLength) + $_fullOperatorPrefixLength) . SWIFT_CRLF;
                    echo 'SEND: Field Operator 2: ' . $_startOfOperatorPrefix . SWIFT_CRLF;

                    // Confirm if we are inside this
                    if ($_start >= $_startOfOperatorCaret && $_start <= ($_finalActiveCaretPosition - $_tokenLength) + $_fullOperatorPrefixLength && self::IsValidOperatorPrefix($_operatorPrefix)) {
                        echo 'SEND: Field Operator 1: ' . $_operatorPrefix . SWIFT_CRLF;
                        $_finalOperatorPrefix = mb_strtolower(trim($_operatorPrefix));

                        $_resultsList = $this->GetProbableOperatorList($_finalOperatorPrefix, $this->GetActiveField());
                        return array($_startOfOperatorCaret, $_finalActiveCaretPosition + strlen($_finalOperatorPrefix), $_resultsList);

                        // If we are inside this and its not a valid operator prefix and we were at space originally, then we list all
                    } elseif ($_start >= $_startOfOperatorCaret && $_start <= ($_finalActiveCaretPosition - $_tokenLength) + $_fullOperatorPrefixLength && !self::IsValidOperatorPrefix($_operatorPrefix) && trim($_startOfOperatorPrefix) == '') {
                        echo 'SEND: Field Operator 3: ' . $_operatorPrefix . SWIFT_CRLF;
                        $_finalOperatorPrefix = mb_strtolower(trim($_operatorPrefix));

                        $_resultsList = $this->GetProbableOperatorList('', $this->GetActiveField());
                        return array($_startOfOperatorCaret, $_startOfOperatorCaret, $_resultsList);

                    }

                    /**
                     * ---------------------------------------------
                     * Field Values
                     * ---------------------------------------------
                     */
                } elseif ($this->GetActiveMode() == self::MODE_FIELDVALUE && $this->GetActiveField() != false && $this->GetActiveOperator() != false) {
                    echo SWIFT_CRLF . 'MODE: FieldValues' . $this->_token . SWIFT_CRLF;

                    if ($_startOfFieldValueCaret == 0) {
                        $_startOfFieldValueCaret = $_finalActiveCaretPosition;

                        $_fieldValuePrefix = $this->Lexer->tokText;
                    } else {
                        $_fieldValuePrefix .= $this->Lexer->tokText;
                    }

                    // Added this to support IN() without space in between IN and parenthesis
                    if ($this->_token == '(') {
                        $_fieldValuePrefix = '';
                        $this->PushBack();
                    }

                    $_hasStringStarted = $_hasParanthesisStarted = false;

                    $_lengthSuffix = 0;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token5: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText5: ' . $this->Lexer->tokText . SWIFT_CRLF;
                        echo 'FieldValuePrefix: ' . $_fieldValuePrefix . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            echo 'FIELDVALUE: Breaking on end of INPUT - ' . $_fieldValuePrefix . SWIFT_CRLF;

                            $_finalFieldValuePrefix = trim($_fieldValuePrefix);

                            $this->SetActiveMode(self::MODE_FIELDGLUE);
                            $this->SetActiveValue(self::VALUETYPE_STRING, $_finalFieldValuePrefix);

                            break;

                            // Is it start of a string value?
                        } elseif (($this->_token == "'" || $this->_token == '"') && !$_hasStringStarted && !$_hasParanthesisStarted) {
                            $_hasStringStarted = true;

                            $_lengthSuffix++;

                            // Is it the end of a string value? We break!
                        } elseif ($this->_token == "'" && $_hasStringStarted && !$_hasParanthesisStarted) {
                            echo 'FIELDVALUE: Breaking on end of string - ' . $_fieldValuePrefix . SWIFT_CRLF;
                            $_lengthSuffix++;

                            $_finalFieldValuePrefix = trim($_fieldValuePrefix);

                            $this->SetActiveMode(self::MODE_FIELDGLUE);
                            $this->SetActiveValue(self::VALUETYPE_STRING, $_finalFieldValuePrefix);

                            break;

                            // Is it start of a paranthesis?
                        } elseif ($this->_token == '(' && trim($_fieldValuePrefix) == '' && !$_hasStringStarted && !$_hasParanthesisStarted) {
                            $_fieldValuePrefix .= $this->Lexer->tokText;
                            $_hasParanthesisStarted = true;

                            echo 'FIELDVALUE: Start of parenthesis' . SWIFT_CRLF;

                            // Is it the end of a paranthesis? We break!
                        } elseif ($this->_token == ')' && $_hasParanthesisStarted) {
                            $_fieldValuePrefix .= $this->Lexer->tokText;

                            $_finalFieldValuePrefix = trim($_fieldValuePrefix);

                            echo 'FIELDVALUE: Breaking on end of paranthesis - ' . $_fieldValuePrefix . SWIFT_CRLF;

                            $this->SetActiveMode(self::MODE_FIELDGLUE);
                            $this->SetActiveValue(self::VALUETYPE_GROUP, $_finalFieldValuePrefix);

                            break;

                            // We break on space unless its a string value
                        } elseif ($this->_token == 'space' && trim($_fieldValuePrefix) != '' && !$_hasStringStarted
                            && !$_hasParanthesisStarted && mb_strtolower(trim($_fieldValuePrefix)) != 'group' && mb_strtolower(trim($_fieldValuePrefix)) != 'order') {
                            echo 'FIELDVALUE: Breaking on space - ' . $_fieldValuePrefix . SWIFT_CRLF;
                            $this->SetActiveMode(self::MODE_FIELDGLUE);

                            $_finalFieldValuePrefix = trim($_fieldValuePrefix);
                            if (is_float($_fieldValuePrefix)) {
                                echo 'FIELDVALUE: Value Type is FLOAT ' . SWIFT_CRLF;
                                $this->SetActiveValue(self::VALUETYPE_FLOAT, $_finalFieldValuePrefix);
                            } elseif (is_numeric($_finalFieldValuePrefix)) {
                                echo 'FIELDVALUE: Value Type is INT ' . SWIFT_CRLF;
                                $this->SetActiveValue(self::VALUETYPE_INT, $_finalFieldValuePrefix);
                            } elseif (substr($_finalFieldValuePrefix, -2) == '()') {
                                echo 'FIELDVALUE: Value Type is FUNCTION ' . SWIFT_CRLF;
                                $this->SetActiveValue(self::VALUETYPE_FUNCTION, $_finalFieldValuePrefix);
                            } else {
                                $_tableAndFieldNameContainer = $this->GetTableAndFieldNameOnText($_finalFieldValuePrefix);

                                $_comparisonString = preg_replace("#\s+#s", " ", mb_strtolower(trim($_finalFieldValuePrefix)));

                                // Is it GROUP BY or ORDER BY? If yes, then we break on this and dont set the value
                                if ($_comparisonString == 'group by' || $_comparisonString == 'order by') {
                                    echo 'FIELDVALUE: Value is ORDER BY/GROUP BY so moving on..' . SWIFT_CRLF;
                                    $this->ResetActiveValue();

                                    // Is it a table and field name combination? break and dont set the value
                                } elseif (_is_array($_tableAndFieldNameContainer) && !empty($_tableAndFieldNameContainer[0]) && !empty($_tableAndFieldNameContainer[1])) {
                                    echo 'FIELDVALUE: Value is Table & Field Name so moving on..' . SWIFT_CRLF;
                                    $this->ResetActiveValue();

                                    // Is it AND/OR ? Break and dont set the value
                                } elseif ($_comparisonString == 'and' || $_comparisonString == 'or') {
                                    echo 'FIELDVALUE: Value is AND/OR so moving on..' . SWIFT_CRLF;
                                    $this->ResetActiveValue();

                                } else {
                                    echo 'FIELDVALUE: Value Type is STRING ' . SWIFT_CRLF;
                                    $this->SetActiveValue(self::VALUETYPE_STRINGNONQUOTED, $_finalFieldValuePrefix);
                                }
                            }

                            $this->PushBack();

                            break;
                        } else {
                            $_fieldValuePrefix .= $this->Lexer->tokText;
                        }
                    }

                    if (mb_strtolower(trim($_fieldValuePrefix)) == 'group by' || mb_strtolower(trim($_fieldValuePrefix)) == 'order by') {
                        $_fieldValuePrefix = ' ';
                    }

                    $_fullFieldValuePrefixLength = strlen($_fieldValuePrefix);

                    $_caretFieldValuePrefix = trim(substr($_fieldValuePrefix, 0 + $_tokenLength, ($_start - $_startOfFieldValueCaret)));
                    echo 'FIELDVALUE: CaretFieldValuePrefix -' . $_caretFieldValuePrefix . '-' . SWIFT_CRLF;
                    echo 'FIELDVALUE: FieldValuePrefix -' . $_fieldValuePrefix . '-' . SWIFT_CRLF;
                    echo 'Start: ' . $_start . SWIFT_CRLF;
                    echo 'StartOfFieldValueCaret: ' . $_startOfFieldValueCaret . SWIFT_CRLF;
                    echo 'FinalActiveCaretPos: ' . $_finalActiveCaretPosition . SWIFT_CRLF;
                    echo 'TokenLength: ' . $_tokenLength . SWIFT_CRLF;


                    // Is it start of the field value and we have caret at this position?
                    if ($_startOfFieldValueCaret == ($_finalActiveCaretPosition - $_tokenLength) && $_start == $_finalActiveCaretPosition) {
                        // We now have the field value, probable entry types are:
                        // Function()
                        // 343.43 - INT/FLOAT
                        // 'This is a string value'
                        // (1, 2, 3, 'test') for IN/NOT IN
                        echo 'FIELDVALUE: Caret is at beginning, value is: ' . $_fieldValuePrefix . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableFieldValueList($_fieldValuePrefix);
                        return array($_startOfFieldValueCaret, $_finalActiveCaretPosition, $_resultsList);

                        // Or if we are in the current token?
                    } elseif ($_startOfFieldValueCaret != 0 && $_start >= $_startOfFieldValueCaret && $_start <= ($_finalActiveCaretPosition + strlen($_fieldValuePrefix) - $_tokenLength)) {
                        echo 'FIELDVALUE: Caret is at field value token, value is: ' . $_fieldValuePrefix . SWIFT_CRLF;

                        $_tokenCaretPosition = ($_start - $_startOfFieldValueCaret - $_tokenLength);

                        $_resultsList = $this->GetProbableFieldValueList(trim($_fieldValuePrefix), $_caretFieldValuePrefix, $_tokenCaretPosition);
                        return array($_startOfFieldValueCaret, (($_finalActiveCaretPosition + $_lengthSuffix) + strlen($_fieldValuePrefix)), $_resultsList);

                    }


                    /**
                     * ---------------------------------------------
                     * Field Glues
                     * ---------------------------------------------
                     */
                } elseif ($this->GetActiveMode() == self::MODE_FIELDGLUE && $this->GetActiveField() != false && $this->GetActiveOperator() != false && $this->_token == ')' && $_hasParentParenthesisStarted) {
                    $_hasParentParenthesisStarted = false;
                    echo 'FIELDGLUE: Parenthesis END' . SWIFT_CRLF;

                } elseif ($this->GetActiveMode() == self::MODE_FIELDGLUE) {
                    echo 'FIELDGLUE: At Field Glue' . SWIFT_CRLF;

                    if ($_startOfGlueCaret == 0) {
                        $_startOfGlueCaret = $_finalActiveCaretPosition;
                        if ($this->_token != 'space') {
                            $_startOfGlueCaret -= $_tokenLength;
                        }

                        $_gluePrefix = $this->Lexer->tokText;
                    } else {
                        $_gluePrefix .= $this->Lexer->tokText;
                    }


                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token6: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText6: ' . $this->Lexer->tokText . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            echo 'FIELDGLUE: Breaking on end of INPUT - ' . $_gluePrefix . SWIFT_CRLF;

                            $_finalGluePrefix = trim($_gluePrefix);

                            $this->SetActiveMode(self::MODE_FIELDGLUE);

                            break;

                            // We break on space provided its not a group/order statement.. then we expect 'by'
                        } elseif ($this->_token == 'space' && trim($_gluePrefix) != ''
                            && mb_strtolower(trim($_gluePrefix)) != 'group' && mb_strtolower(trim($_gluePrefix)) != 'order'
                            && (mb_strtolower(trim($_gluePrefix)) == 'and' || mb_strtolower(trim($_gluePrefix)) == 'or' || mb_strtolower(trim($_gluePrefix)) == 'order by' || mb_strtolower(trim($_gluePrefix)) == 'group by')) {
                            echo 'FIELDGLUE: Breaking on space - ' . $_gluePrefix . SWIFT_CRLF;

                            $_finalGluePrefix = mb_strtolower(trim($_gluePrefix));

                            if (!self::IsValidGlue($_finalGluePrefix)) {
                                throw new SWIFT_Exception('Invalid Glue Statement: ' . $_finalGluePrefix . ', allowed values are AND/OR/ORDER BY/GROUP BY');
                            }

                            if ($_finalGluePrefix == self::GLUE_AND || $_finalGluePrefix == self::GLUE_OR) {
                                $this->SetActiveMode(self::MODE_FIELDNAME);
                            } elseif ($_finalGluePrefix == self::GLUE_GROUPBY) {
                                $this->SetActiveMode(self::MODE_GROUPBY);
                            } elseif ($_finalGluePrefix == self::GLUE_ORDERBY) {
                                $this->SetActiveMode(self::MODE_ORDERBY);
                            }

                            $this->AddToUsedGlues($_finalGluePrefix);

                            $_resetOfFieldName = true;

                            $this->PushBack();

                            break;
                        } else {
                            $_gluePrefix .= $this->Lexer->tokText;
                        }
                    }

                    $_fullGluePrefixLength = strlen($_gluePrefix);

                    $_caretGluePrefix = trim(substr($_gluePrefix, 0 + $_tokenLength, ($_start - $_startOfGlueCaret)));
                    echo 'FIELDGLUE: CaretGluePrefix -' . $_caretGluePrefix . '-' . $_start . ':' . $_startOfGlueCaret . SWIFT_CRLF;

                    // Is it start of the glue value and we have caret at this position?
                    if ($_startOfGlueCaret == ($_finalActiveCaretPosition - $_tokenLength) && $_start == $_finalActiveCaretPosition) {
                        echo 'FIELDGLUE: Caret is at beginning, value is: ' . $_gluePrefix . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableGlueList($_gluePrefix);
                        return array($_startOfGlueCaret, $_finalActiveCaretPosition, $_resultsList);

                        // Or if we are in the current token?
                    } elseif ($_startOfGlueCaret != 0 && $_start >= $_startOfGlueCaret && $_start <= ($_finalActiveCaretPosition + strlen($_gluePrefix) - $_tokenLength)) {
                        echo 'FIELDGLUE: Caret is at glue token, value is: ' . $_gluePrefix . SWIFT_CRLF;

                        $_tokenCaretPosition = ($_start - $_startOfGlueCaret);

                        echo 'FIELDGLUE: Token Caret Position: ' . $_tokenCaretPosition . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableGlueList(trim($_gluePrefix), $_caretGluePrefix, $_tokenCaretPosition);
                        return array($_startOfGlueCaret, (($_finalActiveCaretPosition - $_tokenLength) + strlen($_gluePrefix)), $_resultsList);

                    }


                    /**
                     * ---------------------------------------------
                     * GROUP BY
                     * ---------------------------------------------
                     */
                } elseif ($this->GetActiveMode() == self::MODE_GROUPBY) {
                    echo 'GROUPBY: At Group By' . SWIFT_CRLF;

                    if ($_startOfGroupByCaret == 0) {
                        $_startOfGroupByCaret = $_finalActiveCaretPosition;
                        if ($this->_token != 'space') {
                            $_startOfGroupByCaret -= $_tokenLength;
                        }

                        $_groupByPrefix = $this->Lexer->tokText;
                    } else {
                        $_groupByPrefix .= $this->Lexer->tokText;
                    }

                    $_groupByOpenToken = $_groupByOpenQuote = false;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token7: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText7: ' . $this->Lexer->tokText . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            echo 'GROUPBY: Breaking on end of INPUT - ' . $_groupByPrefix . SWIFT_CRLF;

                            $_finalGroupByPrefix = trim($_groupByPrefix);

                            $this->SetActiveMode(self::MODE_FIELDGLUE);

                            break;

                            // Custom field found
                        } elseif ($this->_token == 'ident' && trim(mb_strtolower($this->Lexer->tokText)) == 'customfield') {
                            echo 'GROUPBY: Breaking on CUSTOMFIELD - ' . $_groupByPrefix . SWIFT_CRLF;

                            $this->SetActiveMode(self::MODE_CUSTOMFIELD);

                            $_groupByPrefix .= $this->Lexer->tokText;

                            break;

                            // We break on space provided its not a group/order statement.. then we expect 'by'
                        } elseif ($this->_token == 'space' && trim($_groupByPrefix) != ''
                            && $_groupByOpenToken == false && $_groupByOpenQuote == false) {
                            echo 'GROUPBY: Breaking on space - ' . $_groupByPrefix . SWIFT_CRLF;

                            $_finalGroupByPrefix = mb_strtolower(trim($_groupByPrefix));

                            $this->SetActiveMode(self::MODE_FIELDGLUE);

                            $this->PushBack();

                            break;


                            // We break on ORDER BY
                        } elseif ($this->_token == 'ident' && mb_strtoupper($this->Lexer->tokText) == 'ORDER' && trim($_groupByPrefix) != '') {
                            echo 'GROUPBY: Breaking on ORDER - ' . $_groupByPrefix . SWIFT_CRLF;

                            $_groupByPrefix = substr($_groupByPrefix, 0, -1);

                            $_finalGroupByPrefix = mb_strtolower(trim($_groupByPrefix));

                            $this->SetActiveMode(self::MODE_FIELDGLUE);

                            $this->PushBack();

                            break;

                        } else {
                            if ($_groupByOpenQuote == false && $this->_token == ',') {
                                $_groupByOpenToken = true;
                            }

                            if ($_groupByOpenQuote == true && $this->_token == "'") {
                                $_groupByOpenQuote = false;
                                $_groupByOpenToken = false;
                            } elseif ($_groupByOpenQuote == false && $this->_token == "'") {
                                $_groupByOpenQuote = true;
                            }

                            $_groupByPrefix .= $this->Lexer->tokText;
                        }
                    }

                    $_fullGroupByPrefixLength = strlen($_groupByPrefix);

                    $_caretGroupByPrefix = trim(substr($_groupByPrefix, 0 + $_tokenLength, ($_start - $_startOfGroupByCaret)));
                    echo 'GROUPBY: CaretGroupByPrefix -' . $_caretGroupByPrefix . '-' . $_start . ':' . $_startOfGroupByCaret . SWIFT_CRLF;

                    // Is it start of the group by value and we have caret at this position?
                    if ($_startOfGroupByCaret == ($_finalActiveCaretPosition - $_tokenLength) && $_start == $_finalActiveCaretPosition) {
                        echo 'GROUPBY: Caret is at beginning, value is: ' . $_groupByPrefix . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableGroupByList($_finalTableList, $_enforceTableRestriction, $_groupByPrefix);
                        return array($_startOfGroupByCaret, $_finalActiveCaretPosition, $_resultsList);

                        // Or if we are in the current token?
                    } elseif ($_startOfGroupByCaret != 0 && $_start >= $_startOfGroupByCaret && $_start <= ($_finalActiveCaretPosition + strlen($_groupByPrefix) - $_tokenLength)) {
                        echo 'GROUPBY: Caret is at group by token, value is: ' . $_groupByPrefix . SWIFT_CRLF;

                        $_tokenCaretPosition = ($_start - $_startOfGroupByCaret);

                        echo 'FIELDGLUE: Token Caret Position: ' . $_tokenCaretPosition . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableGroupByList($_finalTableList, $_enforceTableRestriction, $_groupByPrefix, $_caretGroupByPrefix, $_tokenCaretPosition);
                        return array($_startOfGroupByCaret, (($_finalActiveCaretPosition - $_tokenLength) + strlen($_groupByPrefix)), $_resultsList);

                    }


                    /**
                     * ---------------------------------------------
                     * ORDER BY
                     * ---------------------------------------------
                     */
                } elseif ($this->GetActiveMode() == self::MODE_ORDERBY) {
                    echo 'ORDERBY: At Order By' . SWIFT_CRLF;

                    if ($_startOfOrderByCaret == 0) {
                        $_startOfOrderByCaret = $_finalActiveCaretPosition;
                        if ($this->_token != 'space') {
                            $_startOfOrderByCaret -= $_tokenLength;
                        }

                        $_orderByPrefix = $this->Lexer->tokText;
                    } else {
                        $_orderByPrefix .= $this->Lexer->tokText;
                    }

                    $_orderByOpenToken = $_orderByOpenQuote = $_orderByOpenOptions = $_showOrderOptions = false;

                    while (true) {
                        $this->NextAjaxToken();
                        echo 'Token8: ' . $this->_token . SWIFT_CRLF;
                        echo 'TokText8: ' . $this->Lexer->tokText . SWIFT_CRLF;

                        if ($this->Lexer->tokText == '*end of input*') {
                            echo 'ORDERBY: Breaking on end of INPUT - ' . $_groupByPrefix . SWIFT_CRLF;

                            $_finalOrderByPrefix = trim($_orderByPrefix);

                            $this->SetActiveMode(self::MODE_NONE);

                            break;

                            // We break on space provided its not a group/order statement.. then we expect 'by'
                        } elseif ($this->_token == 'space' && trim($_orderByPrefix) != ''
                            && $_orderByOpenToken == false && $_orderByOpenQuote == false && $_orderByOpenOptions == false) {
                            echo 'ORDERBY: Breaking on space - ' . $_orderByPrefix . SWIFT_CRLF;

                            $_finalOrderByPrefix = mb_strtolower(trim($_orderByPrefix));

                            $this->SetActiveMode(self::MODE_NONE);

                            $this->PushBack();

                            break;

                            // Custom field
                        } elseif ($this->_token == 'ident' && trim(mb_strtolower($this->Lexer->tokText)) == 'customfield') {
                            echo 'ORDERBY: Breaking on CUSTOMFIELD - ' . $_orderByPrefix . SWIFT_CRLF;

                            $this->SetActiveMode(self::MODE_CUSTOMFIELD);

                            $_orderByPrefix .= $this->Lexer->tokText;

                            break;

                        } else {
                            if ($_orderByOpenQuote == false && $this->_token == ',') {
                                $_orderByOpenToken = true;
                                $_orderByOpenOptions = false;
                            }

                            if ($_orderByOpenQuote == true && $this->_token == "'") {
                                $_orderByOpenOptions = true;
                                $_orderByOpenQuote = false;
                                $_orderByOpenToken = false;
                            } elseif ($_orderByOpenQuote == false && $this->_token == "'") {
                                $_orderByOpenQuote = true;
                            }

                            if ($_orderByOpenOptions == true && $_orderByOpenQuote == false && $_orderByOpenToken == false && $this->_token == 'ident'
                                && (trim(mb_strtolower($this->Lexer->tokText)) == 'asc' || trim(mb_strtolower($this->Lexer->tokText)) == 'desc')) {
                                $_orderByOpenOptions = false;
                            }

                            $_orderByPrefix .= $this->Lexer->tokText;
                        }
                    }

                    $_fullOrderByPrefixLength = strlen($_orderByPrefix);

                    $_caretOrderByPrefix = trim(substr($_orderByPrefix, 0 + $_tokenLength, ($_start - $_startOfOrderByCaret)));
                    echo 'ORDERBY: CaretOrderByPrefix -' . $_caretOrderByPrefix . '-' . $_start . ':' . $_startOfOrderByCaret . SWIFT_CRLF;

                    $_resultsList = array();

                    // Is it start of the group by value and we have caret at this position?
                    if ($_startOfOrderByCaret == ($_finalActiveCaretPosition - $_tokenLength) && $_start == $_finalActiveCaretPosition) {
                        echo 'ORDERBY: Caret is at beginning, value is: ' . $_orderByPrefix . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableOrderByList($_finalTableList, $_enforceTableRestriction, $_orderByPrefix);
                        return array($_startOfOrderByCaret, $_finalActiveCaretPosition, $_resultsList);

                        // Or if we are in the current token?
                    } elseif ($_startOfOrderByCaret != 0 && $_start >= $_startOfOrderByCaret && $_start <= ($_finalActiveCaretPosition + strlen($_orderByPrefix) - $_tokenLength)) {
                        echo 'ORDERBY: Caret is at group by token, value is: ' . $_orderByPrefix . SWIFT_CRLF;

                        $_tokenCaretPosition = ($_start - $_startOfOrderByCaret);

                        echo 'FIELDGLUE: Token Caret Position: ' . $_tokenCaretPosition . SWIFT_CRLF;

                        $_resultsList = $this->GetProbableOrderByList($_finalTableList, $_enforceTableRestriction, $_orderByPrefix, $_caretOrderByPrefix, $_tokenCaretPosition);
                        return array($_startOfOrderByCaret, (($_startOfOrderByCaret - $_tokenLength) + strlen($_orderByPrefix)), $_resultsList);

                    }
                }
            }

            echo 'Token: ' . $this->_token . SWIFT_CRLF;
            echo 'TokText: ' . $this->Lexer->tokText . SWIFT_CRLF;
            echo 'ActiveCaretPos: ' . $this->_activeCaretPosition . SWIFT_CRLF;
            echo 'FinalActiveCaretPos: ' . $_finalActiveCaretPosition . SWIFT_CRLF . SWIFT_CRLF;

            if ($this->Lexer->tokText == '*end of input*' || $this->_activeCaretPosition >= $_statementLength) {
                break;
            }

            $_tokenContainer[] = array($this->_token, $this->Lexer->tokText);

            $_itterationCount++;
        }
        return $_ajaxOptions;
    }

    /**
     * Retrieve the Probable Select List
     *
     * @author Varun Shoor
     * @param mixed $_tableList
     * @param bool|int $_enforceTableRestriction
     * @param string $_selectPrefix
     * @param int $_tokenActiveCaretPosition
     * @param bool $_hasMoreStatement (OPTIONAL)
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableSelectList($_tableList, $_enforceTableRestriction, $_selectPrefix = '', $_tokenActiveCaretPosition = 0, $_hasMoreStatement = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'SELECTPREFIX: ' . $_selectPrefix . SWIFT_CRLF;

        $_startOfReplacement = $_endOfReplacement = 0;

        $_processedSelectPrefixInterim = $_selectPrefix;
        $_matches = array();
        $_hasSpaceMatch = 0;
        $_processedSelectPrefix = $_prefix = $_suffix = '';
        if (preg_match("/^(SELECT\s+)(.*?)(\s+)?(from|fro|fr|f)?$/i", $_processedSelectPrefixInterim, $_matches)) {
            echo 'FOUNDMATCHES' . SWIFT_CRLF;
            $_processedSelectPrefix = $_matches[2];
            $_prefix = $_matches[1];

            if (isset($_matches[3])) {
                $_suffix = $_matches[3];
                $_hasSpaceMatch = strlen($_suffix);
            }

            if (isset($_matches[4])) {
                $_suffix .= $_matches[4];
            }

            $_startOfReplacement = strlen($_prefix);

            $_tokenActiveCaretPosition -= strlen($_prefix); // Reset the caret pointer to adjust for the central token
        } else {
            return array(-1, -1, '');
        }

        $_endOfReplacement = strlen($_prefix . $_processedSelectPrefix);

        if ($_hasSpaceMatch > 0) {
            $_endOfReplacement += $_hasSpaceMatch;
        }

        echo 'SELECT PREFIX: ' . $_selectPrefix . SWIFT_CRLF;
        echo 'SELECT PREFIXFINAL: ' . $_processedSelectPrefix . SWIFT_CRLF;
        echo 'TOKENACTIVECARETPOS: ' . $_tokenActiveCaretPosition . SWIFT_CRLF;
        print_r($_tableList);

        $_activeTokenContainer = $this->GetActiveCaretTokenForSelect($_processedSelectPrefix, $_tokenActiveCaretPosition);

        $_finalToken = $_activeTokenContainer[0];
        $_finalTokenPrefix = $_activeTokenContainer[1];

        $_fieldTokens = self::ExplodeFields($_processedSelectPrefix);
//        $_fieldTokens = explode(',', $_processedSelectPrefix);
        if (!_is_array($_fieldTokens)) {
            $_fieldTokens = array($_processedSelectPrefix);
        }

        $_ignoreTokens = $_probableFieldList = $_processingFieldValueList = $_fieldValueList = array();
        foreach ($_fieldTokens as $_token) {
            $_activeToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_token)));
            echo 'ACTIVETOKEN: ' . $_activeToken . SWIFT_CRLF;
            echo 'FINALTOKEN: ' . $_finalToken . SWIFT_CRLF;

            if ($_activeToken == $_finalToken) {
                echo 'GOT ACTIVE TOKEN: ' . $_activeToken . SWIFT_CRLF;
                $_probableFieldList = $this->GetProbableFieldList($_tableList, $_finalTokenPrefix, false, false, false);

                $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_finalTokenPrefix)));
                $_ignoreTokens[] = $_activeToken;
            }

            $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_token));
        }

        $_resultsList = array();

        $_suffixSpace = '';
        if ($_hasMoreStatement == true) {
            $_suffixSpace = ' ';
        }

        echo 'PROBABLE FIELD LIST';
        print_r($_probableFieldList);

        foreach ($_probableFieldList as $_fieldContainer) {
            $_displayText = $_fieldContainer[0];
            $_replacementText = $_fieldContainer[1];

            $_finalTokens = array();
            if (_is_array($_fieldTokens)) {
                foreach ($_fieldTokens as $_tokenText) {
                    if (substr($_tokenText, -1) == ';') {
                        $_tokenText = substr($_tokenText, 0, -1);
                    }

                    // Does this token have an extended clause?
                    $_baseProcessedTokenInterim = $_tokenText;
                    $_baseProcessedToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_baseProcessedTokenInterim)));

                    if (in_array($_baseProcessedToken, $_ignoreTokens)) {
                        $_finalTokens[] = rtrim($_replacementText);
                    } else {
                        $_finalTokens[] = trim($_tokenText);
                    }
                }
            }

            $_resultsList[] = array($_displayText, implode(', ', $_finalTokens) . $_suffixSpace);
        }

        return array($_startOfReplacement, $_endOfReplacement, $_resultsList);
    }

    /**
     * Get the Active Caret Token for SELECT
     *
     * @author Varun Shoor
     * @param string $_selectPrefix
     * @param int $_tokenCaretPosition
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetActiveCaretTokenForSelect($_selectPrefix, $_tokenCaretPosition)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalToken = $_finalTokenPrefix = '';

        $_fieldTokens = self::ExplodeFields($_selectPrefix);
//        $_fieldTokens = explode(',', $_selectPrefix);
        if (!_is_array($_fieldTokens)) {
            $_fieldTokens = array($_selectPrefix);
        }

        $_processedData = '';
        foreach ($_fieldTokens as $_index => $_token) {
            $_startOfToken = strlen($_processedData);

            // Take into account the ','
            if ($_index > 0) {
                $_startOfToken++;
                $_processedData .= ',';
            }

            $_processedData .= $_token;

            $_endOfToken = strlen($_processedData);

            $_fieldToken = $_token;

            $_endOfFieldToken = $_startOfToken + strlen($_fieldToken);

            // Are we at token?
            if ($_tokenCaretPosition >= $_startOfToken && $_tokenCaretPosition <= $_endOfToken) {

                $_tokenPrefixLength = $_tokenCaretPosition - $_startOfToken;
                $_finalToken = $_fieldToken;
                $_finalTokenPrefix = substr($_processedData, $_startOfToken, $_tokenPrefixLength);

                echo 'PROCESSEDDATA: ' . $_processedData . SWIFT_CRLF;
                echo 'FINALTOKENPREFIX: ' . $_finalTokenPrefix . SWIFT_CRLF;

                break;
            }

        }

        $_finalToken = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalToken)));
        $_finalTokenPrefix = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalTokenPrefix)));
        echo 'FINALTOKEN-FINALTOKENPREFIX: ' . $_finalToken . '-' . $_finalTokenPrefix . SWIFT_CRLF;

        return array($_finalToken, $_finalTokenPrefix);
    }

    /**
     * Retrieve the Probable From List
     *
     * @author Varun Shoor
     * @param string $_baseTableName
     * @param array $_tableList
     * @param bool $_enforceTableRestriction
     * @param string $_fromPrefix
     * @param int $_tokenActiveCaretPosition
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableFromList($_baseTableName, $_tableList, $_enforceTableRestriction, $_fromPrefix, $_tokenActiveCaretPosition)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        if (substr($_fromPrefix, 0, 1) == ' ') {
            $_fromPrefix = substr($_fromPrefix, 1, strlen($_fromPrefix));
        }

        echo 'FROMPREFIX: -' . $_fromPrefix . '-' . SWIFT_CRLF;

        $_startOfReplacement = $_endOfReplacement = 0;
        echo 'TOKENACTIVECARETPOS-PRE: ' . $_tokenActiveCaretPosition . SWIFT_CRLF;

        $_processedFromPrefixInterim = $_fromPrefix;
        $_matches = array();
        $_hasSpaceMatch = 0;
        $_processedFromPrefix = $_prefix = $_suffix = '';
        if (preg_match("/^(\s+)?(.*?)(\s+)?(where|wher|whe|wh|w)?$/i", $_processedFromPrefixInterim, $_matches)) {
            echo 'FOUNDMATCHES' . SWIFT_CRLF;
            $_processedFromPrefix = $_matches[2];

            if (isset($_matches[1])) {
                $_prefix = $_matches[1];
            }

            if (isset($_matches[3])) {
                $_suffix = $_matches[3];
                $_hasSpaceMatch = strlen($_suffix);
            }

            if (isset($_matches[4])) {
                $_suffix .= $_matches[4];
            }

            $_startOfReplacement = strlen($_prefix);

            $_tokenActiveCaretPosition -= strlen($_prefix); // Reset the caret pointer to adjust for the central token
        } else {
            return array(-1, -1, '');
        }

        if (trim($_suffix) != '') {
            $_suffix .= ' ';
            $_suffix = preg_replace("#\s+#s", ' ', $_suffix);
        }

        $_endOfReplacement = strlen($_prefix . $_processedFromPrefix);

        if ($_hasSpaceMatch > 0) {
            $_endOfReplacement += $_hasSpaceMatch;
        }

        echo 'FROM PREFIX: ' . $_fromPrefix . SWIFT_CRLF;
        echo 'FROM PREFIXFINAL: ' . $_processedFromPrefix . SWIFT_CRLF;
        echo 'TOKENACTIVECARETPOS: ' . $_tokenActiveCaretPosition . SWIFT_CRLF;
        print_r($_tableList);

        $_activeTokenContainer = $this->GetActiveCaretTokenForSelect($_processedFromPrefix, $_tokenActiveCaretPosition);

        $_finalToken = $_activeTokenContainer[0];
        $_finalTokenPrefix = $_activeTokenContainer[1];

        $_fieldTokens = array();

        if (trim($_processedFromPrefix) != '') {
            $_fieldTokens = explode(',', $_processedFromPrefix);
            if (!_is_array($_fieldTokens)) {
                if (trim($_processedFromPrefix) != '') {
                    $_fieldTokens = array($_processedFromPrefix);
                } else {
                    $_fieldTokens = array();
                }
            }
        }

        echo 'TOKENS: ' . print_r($_fieldTokens, true) . SWIFT_CRLF;

        $_restrictToPrimary = false;
        $_ignoreTokens = $_probableTableList = $_processingFieldValueList = $_fieldValueList = $_incomingTableList = array();

        foreach ($_fieldTokens as $_token) {
            $_activeToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_token)));
            $_incomingTableList[] = trim(mb_strtolower($_activeToken));
        }

        if (!count($_fieldTokens)) {
            $_restrictToPrimary = true;
            echo 'RESTRICTOPRIMARY' . SWIFT_CRLF;
            $_probableTableList = $this->GetProbableTableList($_baseTableName, $_tableList, $_finalTokenPrefix, $_restrictToPrimary);
        } else {
            foreach ($_fieldTokens as $_token) {
                $_activeToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_token)));
                echo 'ACTIVETOKEN: ' . $_activeToken . SWIFT_CRLF;
                echo 'FINALTOKEN: ' . $_finalToken . SWIFT_CRLF;

                if ($_activeToken == $_finalToken) {
                    echo 'GOT ACTIVE TOKEN: ' . $_activeToken . SWIFT_CRLF;
                    $_probableTableList = $this->GetProbableTableList($_baseTableName, $_tableList, $_finalTokenPrefix, $_restrictToPrimary, $_incomingTableList);

                    $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_finalTokenPrefix)));
                    $_ignoreTokens[] = $_activeToken;
                }

                $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_token));
            }
        }

        $_resultsList = array();

        echo 'PROBABLE TABLE LIST';
        print_r($_probableTableList);

        foreach ($_probableTableList as $_fieldContainer) {
            $_displayText = $_fieldContainer[0];
            $_replacementText = $_fieldContainer[1];

            $_finalTokens = array();
            if (_is_array($_fieldTokens)) {
                foreach ($_fieldTokens as $_tokenText) {
                    if (substr($_tokenText, -1) == ';') {
                        $_tokenText = substr($_tokenText, 0, -1);
                    }

                    $_baseProcessedTokenInterim = $_tokenText;
                    $_baseProcessedToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_baseProcessedTokenInterim)));

                    if (in_array($_baseProcessedToken, $_ignoreTokens)) {
                        $_finalTokens[] = rtrim($_replacementText);
                    } else {
                        $_finalTokens[] = trim($_tokenText);
                    }
                }
            } else {
                $_finalTokens[] = rtrim($_replacementText);
            }

            $_resultsList[] = array($_displayText, implode(', ', $_finalTokens) . $_suffix);
        }

        return $_resultsList;
    }

    /**
     * Get Probable Table List
     *
     * @author Varun Shoor
     * @param string $_primaryTableName
     * @param array $_tableList
     * @param string $_tokenPrefix
     * @param bool $_restrictToPrimary (OPTIONAL)
     * @param array $_incomingTableList (OPTIONAL)
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableTableList($_primaryTableName, $_tableList, $_tokenPrefix, $_restrictToPrimary = false, $_incomingTableList = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'GETTING PROBABLE TABLE LIST: ' . $_primaryTableName . ', ' . $_tokenPrefix . SWIFT_CRLF;
        print_r($_tableList);

        if ($_restrictToPrimary == true) {
            print_r($_tableList);
            $_tableList = array($_primaryTableName);
        }

        $_finalTableList = $_resultsList = array();
        foreach ($_tableList as $_tableName) {
            $_finalTableName = $_tableName;
            $_label = SWIFT_KQLSchema::GetLabel($_tableName);
            if (!empty($_label)) {
                $_finalTableList[$_tableName] = $_label;
                $_finalTableName = $_label;
            } else {
                $_finalTableList[$_tableName] = $_tableName;
            }

            if (in_array(mb_strtolower($_finalTableName), $_incomingTableList)) {
                continue;
            }

            if (mb_strtolower(substr($_finalTableName, 0, strlen($_tokenPrefix))) == mb_strtolower(trim($_tokenPrefix))) {
                $_resultsList[] = array($_finalTableName, "'" . $_finalTableName . "'");
            }
        }

        echo 'PROBABLERESULTS' . SWIFT_CRLF;
        print_r($_resultsList);

        return $_resultsList;
    }

    /**
     * Get Table List with Linked Tables
     *
     * @author Varun Shoor
     * @param array $_tableList
     * @return array The Final Table List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTableListWithLinkedTables($_tableList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalTableList = array();

        foreach ($_tableList as $_tableName) {
            $_tableName = mb_strtolower($_tableName);

            if (!isset($this->_schemaContainer[$_tableName])) {
                continue;
            }

            if (!in_array($_tableName, $_finalTableList)) {
                $_finalTableList[] = $_tableName;
            }

            if (isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES]) &&
                _is_array($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES])) {

                foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_RELATEDTABLES] as $_relatedTableName => $_joiner) {
                    if (!in_array($_relatedTableName, $_finalTableList)) {
                        $_finalTableList[] = mb_strtolower($_relatedTableName);
                    }
                }
            }
        }

        return $_finalTableList;
    }

    /**
     * Get a probable list of field names based on a prefix and base table list
     *
     * This function is only used for KQL Ajax Option Parser
     *
     * @author Varun Shoor
     * @param mixed $_tableList
     * @param string $_fieldPrefix (OPTIONAL)
     * @param bool $_enforceTableRestriction (OPTIONAL)
     * @param bool $_hasPreQuote
     * @param bool $_hasPostQuote
     * @return array|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableFieldList($_tableList, $_fieldPrefix = '', $_enforceTableRestriction = true, $_hasPreQuote = true, $_hasPostQuote = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Restrict the field options to this table
        $_restrictedTableName = $_finalFieldPrefix = '';

        // The field prefix has a table name in it?
        if (strpos($_fieldPrefix, '.')) {
            $_restrictedTableName = mb_strtolower(substr($_fieldPrefix, 0, strpos($_fieldPrefix, '.')));
            $_finalFieldPrefix = mb_strtolower(substr($_fieldPrefix, strpos($_fieldPrefix, '.') + 1));
        } else {
            $_finalFieldPrefix = mb_strtolower($_fieldPrefix);
        }

        // Check against label?
        if (!empty($_restrictedTableName) && !isset($this->_schemaContainer[$_restrictedTableName])) {
            foreach ($this->_schemaContainer as $_tableName => $_tableContainerExt) {
                $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
                if (empty($_tableLabel)) {
                    $_tableLabel = $_tableName;
                }

                if (mb_strtolower($_tableLabel) == $_restrictedTableName) {
                    $_restrictedTableName = $_tableName;

                    break;
                }
            }
        }

        $_prefix = $_suffix = '';
        if (!$_hasPreQuote) {
            $_prefix = "'";
        }
        if (!$_hasPostQuote) {
            $_suffix = "' ";
        }

        // We have a restricted table and need to enforce restriction
        if ($_enforceTableRestriction && !empty($_restrictedTableName) && !in_array($_restrictedTableName, $_tableList)) {
            return false;

            // We dont have enforced tables and this table isnt in the table list but is in KQL schema, then we add it
        } elseif ($_enforceTableRestriction && !empty($_restrictedTableName) && !in_array($_restrictedTableName, $_tableList)
            && isset($this->_schemaContainer[$_restrictedTableName])) {
            $_tableList[] = $_restrictedTableName;
        }

        echo 'FINAL FIELD PREFIX: ' . $_finalFieldPrefix . SWIFT_CRLF;

        $_resultList = $_forceTableList = array();

        // If the field prefix is empty then we give priority to table names
        if (empty($_restrictedTableName)) {
            foreach ($_tableList as $_tableName) {
                if (!isset($this->_schemaContainer[$_tableName])) {
                    continue;
                }
                $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
                if (empty($_tableLabel)) {
                    $_tableLabel = $_tableName;
                }

                if (!empty($_finalFieldPrefix) && mb_strtolower(substr($_tableName, 0, strlen($_finalFieldPrefix))) == $_finalFieldPrefix) {
                    $_forceTableList[] = $_tableName;
                } elseif (!empty($_finalFieldPrefix) && mb_strtolower(substr($_tableLabel, 0, strlen($_finalFieldPrefix))) == $_finalFieldPrefix) {
                    $_forceTableList[] = $_tableName;
                } elseif (empty($_finalFieldPrefix)) {
                    $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
                    if (!empty($_tableLabel)) {
                        $_resultList[] = array($_tableLabel . '.', $_prefix . $_tableLabel . '.');
                    } else {
                        $_resultList[] = array($_tableName . '.', $_prefix . $_tableName . '.');
                    }
                }

                if (count($_resultList) >= self::CAP_FIELDNAMES) {
                    return $_resultList;
                }
            }
        } elseif (!empty($_restrictedTableName) && empty($_finalFieldPrefix)) {
            $_forceTableList[] = $_restrictedTableName;
        }

        echo 'TABLE LIST' . SWIFT_CRLF;
        print_r($_forceTableList);
        print_r($_tableList);

        // List of fields
        foreach ($_tableList as $_tableName) {
            $_tableName = mb_strtolower($_tableName);
            $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
            if (empty($_tableLabel)) {
                $_tableLabel = $_tableName;
            }

            // User has specified a table name and table restriction is active, so if it doesnt match with the active table, we move on
            if ($_enforceTableRestriction && !empty($_restrictedTableName) && $_restrictedTableName != $_tableName) {
                continue;
            }

            if (!isset($this->_schemaContainer[$_tableName]) || !isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS])) {
                continue;
            }

            foreach ($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS] as $_fieldName => $_fieldContainer) {
                $_fieldLabel = SWIFT_KQLSchema::GetLabel($_tableName . '_' . $_fieldName);
                if (empty($_fieldLabel)) {
                    $_fieldLabel = SWIFT_KQLSchema::GetLabel($_fieldName);
                    if (empty($_fieldLabel)) {
                        $_fieldLabel = $_fieldName;
                    }
                }

                // Is the field prefix empty?
                if (empty($_fieldPrefix) || in_array($_tableName, $_forceTableList)) {
                    $_resultList[] = array($_tableLabel . '.' . $_fieldLabel, $_prefix . $_tableLabel . '.' . $_fieldLabel . $_suffix);

                    // This one is tricky, we need to work according to prefix
                } else {
                    // Prefix matches with label?
                    if (!empty($_finalFieldPrefix) && mb_strtolower(substr($_fieldLabel, 0, strlen($_finalFieldPrefix))) == $_finalFieldPrefix) {
                        $_resultList[] = array($_tableLabel . '.' . $_fieldLabel, $_prefix . $_tableLabel . '.' . $_fieldLabel . $_suffix);


                        // Prefix matches with field name?
//                    } elseif (!empty($_finalFieldPrefix) && mb_strtolower(substr($_fieldName, 0, strlen($_finalFieldPrefix))) == $_finalFieldPrefix) {
//                        $_resultList[] = array($_tableLabel . '.' . $_fieldLabel, $_prefix . $_tableLabel . '.' . $_fieldLabel . $_suffix);

                    }
                }

                if (count($_resultList) >= self::CAP_FIELDNAMES) {
                    return $_resultList;
                }
            }
        }

        // List of custom fields
        $_groupTypes = array();
        $_customFieldTables = array();
        foreach ($_tableList as $_tableName) {
            if (!isset($this->_schemaContainer[$_tableName])) {
                continue;
            }

            $_groupTypeList = SWIFT_KQLParser::GetCustomFieldGroupTypesByTable($_tableName);
            if (!empty($_groupTypeList)) {
                $_groupTypes = array_merge($_groupTypes, $_groupTypeList);
                $_customFieldTables[] = $_tableName;
            }
        }

        // Tables with custom fields
        if (($this->GetActiveMode() == self::MODE_SELECT) && empty($_restrictedTableName) && empty($_fieldPrefix) && empty($_finalFieldPrefix)) {
            foreach ($_customFieldTables as $_tableName) {
                $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
                if (empty($_tableLabel)) {
                    $_tableLabel = $_tableName;
                }

                $_resultList[] = array($_tableLabel . ', *', "CUSTOMFIELD('" . $_tableLabel . "', *)");

                if (count($_resultList) >= self::CAP_FIELDNAMES) {
                    return $_resultList;
                }
            }
        }

        $_customFieldGroups = array();

        // Custom fields
        foreach ($_customFieldTables as $_tableName) {
            $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
            if (empty($_tableLabel)) {
                $_tableLabel = $_tableName;
            }

            // Restrict table
            if ($_enforceTableRestriction && !empty($_restrictedTableName) && ($_restrictedTableName != $_tableName)) {
                continue;
            }

            $_customFields = array();
            $_customFieldGroups[$_tableName] = array();

            $_customFieldList = $this->KQLParser->GetCustomFields($_tableName);
            foreach ($_customFieldList as $_customFieldID => $_customFieldContainer) {
                if (!in_array($_customFieldContainer['group_title'], $_customFieldGroups[$_tableName])) {
                    $_customFieldGroups[$_tableName][] = $_customFieldContainer['group_title'];
                }

                if (empty($_fieldPrefix) || in_array($_tableName, $_forceTableList) ||
                    (!empty($_finalFieldPrefix) && (mb_strtolower(substr($_customFieldContainer['title'], 0, strlen($_finalFieldPrefix))) == $_finalFieldPrefix))) {
                    if (isset($_customFields[$_customFieldContainer['title']])) {
                        if (!is_array($_customFields[$_customFieldContainer['title']])) {
                            $_customFields[$_customFieldContainer['title']] = array($_customFields[$_customFieldContainer['title']]);
                        }
                        $_customFields[$_customFieldContainer['title']][] = $_customFieldContainer['group_title'];
                    } else {
                        $_customFields[$_customFieldContainer['title']] = $_customFieldContainer['group_title'];
                    }
                }
            }

            ksort($_customFields);

            foreach ($_customFields as $_customFieldTitle => $_customFieldGroupContainer) {
                if (is_array($_customFieldGroupContainer)) {
                    foreach ($_customFieldGroupContainer as $_customFieldGroup) {
                        $_resultList[] = array($_tableLabel . ', ' . $_customFieldTitle, "CUSTOMFIELD('" . $_tableLabel . "', '" . str_replace("'", "\\'", $_customFieldGroup) . "', '" . str_replace("'", "\\'", $_customFieldTitle) . "')");

                        if (count($_resultList) >= self::CAP_FIELDNAMES) {
                            return $_resultList;
                        }
                    }
                } else {
                    $_resultList[] = array($_tableLabel . ', ' . $_customFieldTitle, "CUSTOMFIELD('" . $_tableLabel . "', '" . str_replace("'", "\\'", $_customFieldTitle) . "')");
                }

                if (count($_resultList) >= self::CAP_FIELDNAMES) {
                    return $_resultList;
                }
            }
        }

        // Groups
        if ($this->GetActiveMode() == self::MODE_SELECT) {
            foreach ($_customFieldGroups as $_tableName => $_customFieldGroupContainer) {
                $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
                if (empty($_tableLabel)) {
                    $_tableLabel = $_tableName;
                }

                if (!empty($_customFieldGroupContainer)) {
                    foreach ($_customFieldGroupContainer as $_customFieldGroup) {
                        if (empty($_fieldPrefix) || in_array($_tableName, $_forceTableList) ||
                            (!empty($_finalFieldPrefix) && (mb_strtolower(substr($_customFieldGroup, 0, strlen($_finalFieldPrefix))) == $_finalFieldPrefix))) {
                            $_resultList[] = array($_tableLabel . ', ' . $_customFieldGroup . ', *', "CUSTOMFIELD('" . $_tableLabel . "', '" . str_replace("'", "\\'", $_customFieldGroup) . "', *)");

                            if (count($_resultList) >= self::CAP_FIELDNAMES) {
                                return $_resultList;
                            }
                        }
                    }
                }
            }
        }

        return $_resultList;
    }

    /**
     * Check and confirm if its an operator token
     *
     * @author Varun Shoor
     * @param string $_token
     * @param string $_tokenText
     * @param array $_tokenContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function IsOperatorToken($_token, $_tokenText, $_tokenContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_tokenText = mb_strtolower($_tokenText);

        if ($_token == 'space') {
            return true;

            // Check against basic operators
        } elseif ($_token == '>' || $_token == '=' || $_token == '<' || $_token == '>=' || $_token == '<=' || $_token == '!=') {
            return true;

            // Check against allowed operators
        } elseif ($_token == 'ident' && in_array($_tokenText, self::$_allowedTextOperators)) {
            return true;

            // If nothing matched then we try to match the prefix text against each element, maybe the user is still typing?
        } else {
            foreach (self::$_allowedTextOperators as $_allowedTextOp) {
                if (!empty($_tokenText) && substr($_allowedTextOp, 0, strlen($_tokenText)) == $_tokenText) {
                    return true;
                }
            }

            foreach (self::$_allowedBasicOperators as $_allowedBasicOp) {
                if (!empty($_tokenText) && substr($_allowedBasicOp, 0, strlen($_tokenText)) == $_tokenText) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check and confirm if its a valid operator prefix
     *
     * @author Varun Shoor
     * @param string $_operatorPrefix
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function IsValidOperatorPrefix($_operatorPrefix)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_operatorPrefix = trim(mb_strtolower($_operatorPrefix));

        foreach (self::$_allowedTextOperators as $_allowedTextOp) {
            if (!empty($_operatorPrefix) && substr($_allowedTextOp, 0, strlen($_operatorPrefix)) == $_operatorPrefix) {
                return true;
            }
        }

        foreach (self::$_allowedBasicOperators as $_allowedBasicOp) {
            if (!empty($_operatorPrefix) && substr($_allowedBasicOp, 0, strlen($_operatorPrefix)) == $_operatorPrefix) {
                return true;
            }
        }

        // Just a space right now
        if (empty($_operatorPrefix)) {
            return true;
        }

        return false;
    }

    /**
     * Get Probable Operator Prefix
     *
     * @author Varun Shoor
     * @param string $_operatorPrefix
     * @param array $_activeFieldContainer
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableOperatorList($_operatorPrefix, $_activeFieldContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!_is_array($_activeFieldContainer)) {
            throw new SWIFT_Exception('Invalid Active Field Container');
        }

        $_operatorPrefix = mb_strtolower(trim($_operatorPrefix));

        if ($_activeFieldContainer[0] == 'cf') {
            $_probableOperatorList = $this->GetOperatorListForCustomField($_activeFieldContainer[2]);
        } else {
            $_probableOperatorList = $this->GetOperatorListOnFieldType($_activeFieldContainer[2][SWIFT_KQLSchema::FIELD_TYPE], $_activeFieldContainer[2]);
        }

        $_resultsList = array();

        foreach ($_probableOperatorList as $_operator) {
            $_displayOperator = strtoupper($_operator);

            if (!empty($_operatorPrefix) && substr($_operator, 0, strlen($_operatorPrefix)) == $_operatorPrefix) {
                $_resultsList[] = array($_displayOperator, ' ' . $_displayOperator . ' ');
            } elseif (empty($_operatorPrefix)) {
                $_resultsList[] = array($_displayOperator, ' ' . $_displayOperator . ' ');
            }
        }

        return $_resultsList;
    }

    /**
     * Retrieve the list of operators based on the field type
     *
     * @author Varun Shoor
     * @param mixed $_fieldType
     * @param array $_fieldContainer
     * @return array The Operator List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOperatorListOnFieldType($_fieldType, $_fieldContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_operatorList = array();

        // Boolean
        if ($_fieldType == SWIFT_KQLSchema::FIELDTYPE_BOOL) {
            $_operatorList = array('=', '!=');

            // Custom
        } elseif ($_fieldType == SWIFT_KQLSchema::FIELDTYPE_CUSTOM) {
            $_operatorList = array('=', '!=', 'in', 'not in');

            // Float, Int, Seconds, UnixTime
        } elseif ($_fieldType == SWIFT_KQLSchema::FIELDTYPE_FLOAT || $_fieldType == SWIFT_KQLSchema::FIELDTYPE_INT
            || $_fieldType == SWIFT_KQLSchema::FIELDTYPE_SECONDS || $_fieldType == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME) {
            $_operatorList = array('=', '!=', '>', '<', '>=', '<=');

            // String
        } elseif ($_fieldType == SWIFT_KQLSchema::FIELDTYPE_STRING) {
            $_operatorList = array('=', '!=', 'like', 'not like', 'in', 'not in');

            // Linked, for linked fields we decide on the renderer field and not the joiner
        } elseif ($_fieldType == SWIFT_KQLSchema::FIELDTYPE_LINKED) {
            $_linkedToContainer = $_fieldContainer[SWIFT_KQLSchema::FIELD_LINKEDTO];

            echo $_linkedToContainer[1];

            $_nameContainer = $this->GetTableAndFieldNameOnText($_linkedToContainer[1]);
            $_tableName = $_nameContainer[0];
            $_fieldName = $_nameContainer[1];

            if (empty($_tableName) || empty($_fieldName) || !isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE])) {
                throw new SWIFT_Exception('Invalid Table or Field Name');
            }

            $_linkedFieldType = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE];

            $_operatorList = self::GetOperatorListOnFieldType($_linkedFieldType, $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName]);
        }

        return $_operatorList;
    }

    /**
     * Retrieve the list of operators for custom field
     *
     * @author Andriy Lesyuk
     * @param array $_customFieldContainer
     * @return array The Operator List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOperatorListForCustomField($_customFieldContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_operatorList = array();

        if ($_customFieldContainer['encryptindb'] != '1') {

            if (($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_TEXT) ||
                ($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_TEXTAREA) ||
                ($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_PASSWORD)) {
                $_operatorList = array('=', '!=', 'like', 'not like', 'in', 'not in');

            } elseif (($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                ($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE)) {
                $_operatorList = array('=', '!=', 'in', 'not in');

            } elseif (($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_RADIO) ||
                ($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_SELECT)) {
                $_operatorList = array('=', '!=', 'in', 'not in');

            } elseif ($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                $_operatorList = array('=', '!=', 'in', 'not in');

            } elseif ($_customFieldContainer['type'] == SWIFT_CustomField::TYPE_DATE) {
                $_operatorList = array('=', '!=', '>', '<', '>=', '<=');

            }
        }

        return $_operatorList;
    }

    /**
     * Check to see if its a valid mode
     *
     * @author Varun Shoor
     * @param mixed $_mode
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMode($_mode)
    {
        return ($_mode == self::MODE_FIELDGLUE || $_mode == self::MODE_FIELDNAME || $_mode == self::MODE_FIELDOP
            || $_mode == self::MODE_FIELDVALUE || $_mode == self::MODE_NONE || $_mode == self::MODE_ORDERBY
            || $_mode == self::MODE_GROUPBY || $_mode == self::MODE_SELECT || $_mode == self::MODE_FROM
            || $_mode == self::MODE_CUSTOMFIELD);
    }

    /**
     * Resets the currently active mode
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ResetActiveMode()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_activeMode = self::MODE_NONE;

        $this->_previousMode = self::MODE_NONE;

        return true;
    }

    /**
     * Set the active mode
     *
     * @author Varun Shoor
     * @param mixed $_activeMode
     * @param mixed $_activeFieldText (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetActiveMode($_activeMode, $_activeFieldText = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidMode($_activeMode)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_activeMode == self::MODE_CUSTOMFIELD) {
            $this->_previousMode = $this->_activeMode;
        }

        $this->_activeMode = $_activeMode;

        if (!empty($_activeFieldText)) {
            if (_is_array($_activeFieldText)) { // custom field
                $_customField = $this->KQLParser->GetCustomField($_activeFieldText, $this->_primaryTableName);
                if (is_array($_customField) && isset($_customField['id'])) {
                    $_tableName = 'cf';
                    $_fieldName = $_customField['id'];
                } else {
                    return false;
                }
            } else {
                $_nameContainer = $this->GetTableAndFieldNameOnText($_activeFieldText);
                $_tableName = $_nameContainer[0];
                $_fieldName = $_nameContainer[1];
            }

            try {
                $this->SetActiveField($_tableName, $_fieldName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get Active Mode
     *
     * @author Varun Shoor
     * @return mixed The Active Mode
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetActiveMode()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_activeMode;
    }

    /**
     * Get Previous Mode
     *
     * @author Andriy Lesyuk
     * @return mixed The Previous Mode
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetPreviousMode()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_previousMode;
    }

    /**
     * Restore Previous Mode
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function RestorePreviousMode()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_previousMode != self::MODE_NONE) {
            $this->_activeMode = $this->_previousMode;

            $this->_previousMode = self::MODE_NONE;

            return true;
        }

        return false;
    }

    /**
     * Retrieve the currently set primary table name
     *
     * @author Varun Shoor
     * @return string The Primary Table Name
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPrimaryTableName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_primaryTableName;
    }

    /**
     * Resets the Active Field
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ResetActiveField()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_activeField = false;

        return true;
    }

    /**
     * Sets the Active Field
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_fieldName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetActiveField($_tableName, $_fieldName)
    {
        $_tableName = mb_strtolower(trim($_tableName));
        $_fieldName = mb_strtolower(trim($_fieldName));

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif ($_tableName == 'cf') { // custom field
            $_customFields = $this->KQLParser->GetCustomFields();

            if (!isset($_customFields[$_fieldName])) {
                throw new SWIFT_Exception('Invalid Custom Field');
            }

            $this->_activeField = array($_tableName, $_fieldName, $_customFields[$_fieldName]);

            return false;
        } elseif (!isset($this->_schemaContainer[$_tableName])) {
            throw new SWIFT_Exception('Invalid Table Name');
        } elseif (!isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName])) {
            throw new SWIFT_Exception('Invalid Field Name: ' . $_fieldName);
        }

        $this->_activeField = array($_tableName, $_fieldName, $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName]);

        return true;
    }

    /**
     * Retrieve the Active Field Array
     *
     * @author Varun Shoor
     * @return array|bool array($_tableName, $_fieldName, $_fieldContainer)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetActiveField()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_activeField;
    }

    /**
     * Reset the active operator
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ResetActiveOperator()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_activeOperator = false;

        return true;
    }

    /**
     * Set the Active Operator
     *
     * @author Varun Shoor
     * @param string $_activeOperator
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetActiveOperator($_activeOperator)
    {
        $_activeOperator = trim(mb_strtolower($_activeOperator));

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_activeOperator)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_activeOperator = mb_strtolower(trim($_activeOperator));

        if (in_array($_activeOperator, self::$_allowedBasicOperators) || in_array($_activeOperator, self::$_allowedTextOperators)) {
            $this->_activeOperator = $_activeOperator;

            return true;
        }

        throw new SWIFT_Exception('Invalid Operator');
    }

    /**
     * Return the Active Operator
     *
     * @author Varun Shoor
     * @return string|bool "Active Operator" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetActiveOperator()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_activeOperator;
    }

    /**
     * Reset the Active Value
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ResetActiveValue()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_activeValue = '';
        $this->_activeValueType = false;

        return true;
    }

    /**
     * Check to see if its a valid value type
     *
     * @author Varun Shoor
     * @param mixed $_valueType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidValueType($_valueType)
    {
        return ($_valueType == self::VALUETYPE_INT || $_valueType == self::VALUETYPE_FLOAT || $_valueType == self::VALUETYPE_FUNCTION
            || $_valueType == self::VALUETYPE_GROUP || $_valueType == self::VALUETYPE_STRING || $_valueType == self::VALUETYPE_STRINGNONQUOTED);
    }

    /**
     * Set Active Value Type
     *
     * @author Varun Shoor
     * @param mixed $_valueType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetActiveValueType($_valueType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidValueType($_valueType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_activeValueType = $_valueType;

        return true;
    }

    /**
     * Set Active Value
     *
     * @author Varun Shoor
     * @param mixed $_valueType
     * @param mixed $_activeValue
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetActiveValue($_valueType, $_activeValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidValueType($_valueType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_activeValueType = $_valueType;
        $this->_activeValue = $_activeValue;

        return true;
    }

    /**
     * Retrieve the Active Value
     *
     * @author Varun Shoor
     * @return mixed "The Active Value" on Success, "" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetActiveValue()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_activeValue;
    }

    /**
     * Retrieve the active value type
     *
     * @author Varun Shoor
     * @return mixed "Active Value Type" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetActiveValueType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_activeValueType;
    }

    /**
     * Get the Probable Field Values, filter by Field Value prefix if necessary
     *
     * @author Varun Shoor
     * @param string $_fieldValuePrefix
     * @param string $_caretFieldValuePrefix (OPTIONAL)
     * @param int $_tokenCaretPosition (OPTIONAL)
     * @return array Results List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableFieldValueList($_fieldValuePrefix, $_caretFieldValuePrefix = '', $_tokenCaretPosition = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fieldValuePrefix = trim($_fieldValuePrefix);

        $_resultsList = $_finalResultsList = array();

        $_activeFieldContainer = $this->GetActiveField();
        if (empty($_activeFieldContainer)) {
            throw new SWIFT_Exception('Invalid Field Container');
        }

        $_processingFieldValueList = $_fieldValueList = $_ignoreTokens = array();

        // We need to process the set value if it has quotes or paranthesis
        $_nonQuoteFieldValuePrefix = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), $_fieldValuePrefix);
        $_nonQuoteCaretFieldValuePrefix = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), $_caretFieldValuePrefix);

        $_singleStringValue = '';

        $_nonParanthesisFVPrefix = preg_replace(array('/^(\()/i', '/(\))$/i'), array('', ''), $_fieldValuePrefix);

        echo 'FIELDVALUEPREFIX: ' . $_fieldValuePrefix . ' (' . strlen($_fieldValuePrefix) . ')' . SWIFT_CRLF;
        echo 'TOKENCARETPOS: ' . $_tokenCaretPosition . SWIFT_CRLF;

        // Has an ending comma?
        $_hasEndingUnfinishedComma = false;
        if (substr($_nonParanthesisFVPrefix, -1) == ',' && strlen($_nonParanthesisFVPrefix) == $_tokenCaretPosition) {
            $_hasEndingUnfinishedComma = true;
        }

        // If this has starting paranthesis or starting AND ending paranthesis
        if (preg_match('/^(\()/i', $_fieldValuePrefix)
            || (preg_match('/^\((.*)\)$/', $_fieldValuePrefix))) {
            $_nonParanthesisChunks = preg_replace(array('/^(\()/i', '/(\))$/i'), array('', ''), $_fieldValuePrefix);
            /** @var string|string[] $_baseTokens */
            $_baseTokens = explode(',', $_nonParanthesisChunks);
            if (count($_baseTokens)) {
                $_activeCaretTokenContainer = self::GetActiveCaretTokenInGroup($_fieldValuePrefix, $_tokenCaretPosition);
                $_activeCaretToken = $_activeCaretTokenPrefix = '';

                if (_is_array($_activeCaretTokenContainer)) {
                    $_activeCaretToken = $_activeCaretTokenContainer[0];
                    $_activeCaretTokenPrefix = $_activeCaretTokenContainer[1];
                }

                foreach ($_baseTokens as $_tokenText) {
                    $_baseProcessedToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_tokenText)));
                    if (!empty($_activeCaretToken) && $_baseProcessedToken == $_activeCaretToken && !$_hasEndingUnfinishedComma) {
                        $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_activeCaretTokenPrefix)));
                        $_ignoreTokens[] = $_activeCaretToken;
                    }

                    $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_tokenText));
                }
            } else {
                $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_baseTokens)));
                $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_baseTokens));
            }
        } else {
            $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', '', '', ''), mb_strtolower(trim($_nonQuoteFieldValuePrefix)));

            if (trim($_nonQuoteCaretFieldValuePrefix) != '') {
                $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', '', '', ''), mb_strtolower(trim($_nonQuoteCaretFieldValuePrefix)));
            }

            $_singleStringValue = trim($_nonQuoteFieldValuePrefix);

            $_fieldValueList[] = trim($_nonQuoteFieldValuePrefix);
        }

        if (!count($_processingFieldValueList) || $_hasEndingUnfinishedComma) {
            $_processingFieldValueList[] = '';
        }

        echo '====' . $_fieldValuePrefix;
        print_r($_processingFieldValueList);

        $_fieldContainer = $_activeFieldContainer[2];

        // Custom field
        if ($_activeFieldContainer[0] == 'cf') {
            if ($_fieldContainer['encryptindb'] != '1') {

                // Types which have options
                if (($_fieldContainer['type'] == SWIFT_CustomField::TYPE_CHECKBOX) ||
                    ($_fieldContainer['type'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE) ||
                    ($_fieldContainer['type'] == SWIFT_CustomField::TYPE_RADIO) ||
                    ($_fieldContainer['type'] == SWIFT_CustomField::TYPE_SELECT)) {
                    foreach ($_processingFieldValueList as $_fieldValueToken) {
                        foreach ($_fieldContainer['options'] as $_originalValue => $_displayValue) {
                            if ((substr($_displayValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                                && !isset($_resultsList[$_displayValue])) {
                                $_resultsList[$_displayValue] = $_displayValue;
                                $_ignoreTokens[] = $_fieldValueToken;
                            }
                        }
                    }

                    // Linked select (with nested options)
                } elseif ($_fieldContainer['type'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                    foreach ($_processingFieldValueList as $_fieldValueToken) {
                        foreach ($_fieldContainer['options'] as $_originalValue => $_displayValueContainer) {
                            if ((substr($_displayValueContainer['value'], 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                                && !isset($_resultsList[$_displayValueContainer['value']])) {
                                $_resultsList[$_displayValueContainer['value']] = $_displayValueContainer['value'];
                                $_ignoreTokens[] = $_fieldValueToken;
                            }

                            if (_is_array($_displayValueContainer['suboptions'])) {
                                foreach ($_displayValueContainer['suboptions'] as $_subOriginalValue => $_subDisplayValueContainer) {
                                    if ((substr($_subDisplayValueContainer['value'], 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                                        && !isset($_resultsList[$_subDisplayValueContainer['value']])) {
                                        $_resultsList[$_subDisplayValueContainer['value']] = $_subDisplayValueContainer['value'];
                                        $_ignoreTokens[] = $_fieldValueToken;
                                    }
                                }
                            }
                        }
                    }

                    // Date (NOTE: same as Unix Time field below)
                } elseif ($_fieldContainer['type'] == SWIFT_CustomField::TYPE_DATE) {
                    $_unixTimeValues = self::$_extendedFunctionList;

                    $this->SetActiveValueType(self::VALUETYPE_FUNCTION);

                    foreach ($_processingFieldValueList as $_fieldValueToken) {
                        foreach ($_unixTimeValues as $_displayValue => $_replacementValue) {
                            $_finalDisplayValue = mb_strtolower(trim($_displayValue));
                            $_finalReplacementValue = mb_strtolower(trim($_replacementValue));

                            if ((substr($_finalDisplayValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken
                                    || substr($_finalReplacementValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                                && !isset($_resultsList[$_displayValue])) {
                                $_resultsList[$_displayValue] = $_replacementValue . ' ';
                                $_ignoreTokens[] = $_fieldValueToken;
                            }
                        }
                    }
                }
            }

            // Base it on field types
            // Boolean field
        } elseif ($_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_BOOL) {
            $_booleanValues = array('TRUE' => '1', 'FALSE' => '0');

            foreach ($_processingFieldValueList as $_fieldValueToken) {
                foreach ($_booleanValues as $_displayValue => $_replacementValue) {
                    $_finalDisplayValue = mb_strtolower(trim($_displayValue));
                    $_finalReplacementValue = mb_strtolower(trim($_replacementValue));

                    if ((substr($_finalDisplayValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken
                            || substr($_finalReplacementValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                        && !isset($_resultsList[$_displayValue])) {
                        $_resultsList[$_displayValue] = $_replacementValue . ' ';
                        $_ignoreTokens[] = $_fieldValueToken;
                    }
                }
            }

            // Seconds field
        } elseif ($_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_SECONDS) {
            $_secondValues = array('30 Seconds' => '30', '1 Minute' => '60', '5 Minutes' => '300', '15 Minutes' => '900', '30 Minutes' => '1800', '1 Hour' => '3600', '3 Hours' => '10800', '6 Hours' => '21600', '12 Hours' => '43200', '1 Day' => '86400', '7 Days' => '604800');

            foreach ($_processingFieldValueList as $_fieldValueToken) {
                foreach ($_secondValues as $_displayValue => $_replacementValue) {
                    $_finalDisplayValue = mb_strtolower(trim($_displayValue));
                    $_finalReplacementValue = mb_strtolower(trim($_replacementValue));

                    if ((substr($_finalDisplayValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken
                            || substr($_finalReplacementValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                        && !isset($_resultsList[$_displayValue])) {
                        $_resultsList[$_displayValue] = $_replacementValue;
                        $_ignoreTokens[] = $_fieldValueToken;
                    }
                }
            }


            // Custom field
        } elseif ($_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_CUSTOM) {
            foreach ($_processingFieldValueList as $_fieldValueToken) {
                foreach ($_fieldContainer[SWIFT_KQLSchema::FIELD_CUSTOMVALUES] as $_originalValue => $_displayValue) {
                    $_finalDisplayValue = $this->Language->Get(mb_strtolower(trim($_displayValue)));

                    if ((substr($_finalDisplayValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                        && !isset($_resultsList[$_finalDisplayValue])) {
                        $_resultsList[$_finalDisplayValue] = $_finalDisplayValue;
                        $_ignoreTokens[] = $_fieldValueToken;
                    }
                }
            }

            // Unix Time field (NOTE: same as SWIFT_CustomField::TYPE_DATE above)
        } elseif ($_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE] == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME) {
            $_unixTimeValues = self::$_extendedFunctionList;

            $this->SetActiveValueType(self::VALUETYPE_FUNCTION);

            foreach ($_processingFieldValueList as $_fieldValueToken) {
                foreach ($_unixTimeValues as $_displayValue => $_replacementValue) {
                    $_finalDisplayValue = mb_strtolower(trim($_displayValue));
                    $_finalReplacementValue = mb_strtolower(trim($_replacementValue));

                    if ((substr($_finalDisplayValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken
                            || substr($_finalReplacementValue, 0, strlen($_fieldValueToken)) == $_fieldValueToken)
                        && !isset($_resultsList[$_displayValue])) {
                        $_resultsList[$_displayValue] = $_replacementValue . ' ';
                        $_ignoreTokens[] = $_fieldValueToken;
                    }
                }
            }
        } elseif (isset($_fieldContainer[SWIFT_KQLSchema::FIELD_LINKEDTO]) && _is_array($_fieldContainer[SWIFT_KQLSchema::FIELD_LINKEDTO])) {
            $_linkedToContainer = $_fieldContainer[SWIFT_KQLSchema::FIELD_LINKEDTO];

            $_nameContainer = $this->GetTableAndFieldNameOnText($_linkedToContainer[1]);
            $_tableName = $_nameContainer[0];
            $_fieldName = $_nameContainer[1];

            if (empty($_tableName) || empty($_fieldName) || !isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE])) {
                throw new SWIFT_Exception('Invalid Table or Field Name');
            }

            $_whereExtension = '';
            if (isset($_linkedToContainer[2]) && !empty($_linkedToContainer[2])) {
                $_whereExtension = ' WHERE (' . $_linkedToContainer[2] . ')';
            }

            $_resultsTextContainer = array();
            $this->Database->Query("SELECT " . $_fieldName . " FROM " . TABLE_PREFIX . $_tableName . " AS " . $_tableName . $_whereExtension . ' GROUP BY ' . $_fieldName);
            while ($this->Database->NextRecord()) {
                $_resultsTextContainer[] = $this->Database->Record[$_fieldName];
            }


            foreach ($_processingFieldValueList as $_fieldValueToken) {
                foreach ($_resultsTextContainer as $_resultsText) {
                    $_processingResultsText = mb_strtolower(trim($_resultsText));

                    if (substr($_processingResultsText, 0, strlen($_fieldValueToken)) == $_fieldValueToken && !isset($_resultsList[$_resultsText])) {
                        $_resultsList[$_resultsText] = $_resultsText;
                        $_ignoreTokens[] = $_fieldValueToken;
                    }
                }
            }
        }

        print_r($_resultsList);

        /**
         * ---------------------------------------------
         * Process the results into replaceable text
         * ==================
         * We now have the results and need to process them into a replaceable text,
         * things like paranthesis, quotes and multiple items have to be taken into account
         * ---------------------------------------------
         */

        $_activeOperator = $this->GetActiveOperator();

        // Quote based operators
        if ($_activeOperator == '=' || $_activeOperator == '!=' || $_activeOperator == '>' || $_activeOperator == '<'
            || $_activeOperator == '>=' || $_activeOperator == '<=' || $_activeOperator == 'like' || $_activeOperator == 'not like') {
            foreach ($_resultsList as $_displayResultText => $_dispatchResultText) {
                if ($this->GetActiveValueType() == self::VALUETYPE_FUNCTION) {
                    $_finalResultsList[] = array($_displayResultText, $_dispatchResultText);
                } else {
                    $_finalResultsList[] = array($_displayResultText, "'" . $_dispatchResultText . "' ");
                }
            }

            // Paranthesis based token
        } else {
            $_fieldValueChunks = $_fieldValueChunksComparison = array();
            $_finalParanthesisValue = '';
            foreach ($_fieldValueList as $_pFieldValue) {
                // Ignore the string we are typing right now..
                if (!empty($_singleStringValue) && $_pFieldValue == $_singleStringValue) {
                    continue;
                } elseif (in_array(mb_strtolower($_pFieldValue), $_ignoreTokens)) {
                    continue;
                }

                if (trim($_pFieldValue) != '') {
                    $_nonQuotedChunk = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), $_pFieldValue);
                    $_nonQuotedComparisonChunk = mb_strtolower(trim($_nonQuotedChunk));

                    if (in_array($_nonQuotedComparisonChunk, $_fieldValueChunksComparison)) {
                        continue;
                    }

                    $_fieldValueChunksComparison[] = $_nonQuotedComparisonChunk;
                    $_fieldValueChunks[] = "'" . $_nonQuotedChunk . "'";
                }
            }

            foreach ($_resultsList as $_displayResultText => $_dispatchResultText) {
                $_toCombineFieldValueList = $_fieldValueChunks;

                $_nonQuotedChunk = mb_strtolower(trim($_dispatchResultText));
                if (in_array($_nonQuotedChunk, $_fieldValueChunksComparison)) {
                    continue;
                }

                $_toCombineFieldValueList[] = "'" . $_dispatchResultText . "'";
                $_finalParanthesisValue = '(' . implode(', ', $_toCombineFieldValueList) . ') ';

                $_finalResultsList[] = array($_displayResultText, $_finalParanthesisValue);
            }
        }

        print_r($_resultsList);

        return $_finalResultsList;
    }

    /**
     * Get the Active Caret Token in Group
     *
     * @author Varun Shoor
     * @param string $_fieldValuePrefix
     * @param int $_tokenCaretPosition
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetActiveCaretTokenInGroup($_fieldValuePrefix, $_tokenCaretPosition)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Token starts by default
        $_hasTokenStarted = true;
        $_hasTokenEnded = $_activateToken = false;

        $_activeToken = $_processedData = $_finalToken = $_finalTokenPrefix = '';

        $_nonParanthesisChunks = preg_replace(array('/^(\()/i'), array(''), $_fieldValuePrefix);
        $_tokenCaretPosition -= strlen($_fieldValuePrefix) - strlen($_nonParanthesisChunks);

        $_fieldValuePrefix = $_nonParanthesisChunks;

        echo 'FIELDVALUE: ActiveToken0: ' . $_fieldValuePrefix . ' (' . $_tokenCaretPosition . ')' . SWIFT_CRLF;
        echo 'FIELDVALUE: ActiveToken1: ' . substr($_fieldValuePrefix, 0, $_tokenCaretPosition) . SWIFT_CRLF;

        if (empty($_tokenCaretPosition)) {
            return [];
        }

        for ($index = 0; $index < strlen($_fieldValuePrefix); $index++) {
            $_activeCharacter = substr($_fieldValuePrefix, $index, 1);

            $_activeToken .= $_activeCharacter;

            // Token is ending?
            if (trim($_activeToken) != '' && ($_activeCharacter == ' ' || $_activeCharacter == ',' || $index == strlen($_fieldValuePrefix) - 1)) {
                $_hasTokenEnded = true;
            }

            // Now we need to see if our caret is at token
            if (!$_activateToken && strlen($_processedData) >= $_tokenCaretPosition) {
                $_activateToken = true;
                $_finalTokenPrefix = substr($_activeToken, 0, -1);
            }

            // Cleanup
            if ($_hasTokenEnded) {
                if ($_activateToken) {
                    $_finalToken = trim($_activeToken);
                    break;
                }

                $_activeToken = '';
                $_hasTokenStarted = true;
                $_hasTokenEnded = false;
            }

            $_processedData .= $_activeCharacter;
        }

        $_finalToken = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\()/i', '/(\))$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalToken)));
        $_finalTokenPrefix = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\()/i', '/(\))$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalTokenPrefix)));

        echo 'FIELDVALUE: Final Token: ' . $_finalToken . SWIFT_CRLF;
        echo 'FIELDVALUE: Final Token Prefix: ' . $_finalTokenPrefix . SWIFT_CRLF;

        return array($_finalToken, $_finalTokenPrefix);
    }

    /**
     * Check to see if its a valid glue type
     *
     * @author Varun Shoor
     * @param mixed $_glueType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidGlue($_glueType)
    {
        return ($_glueType == self::GLUE_AND || $_glueType == self::GLUE_OR || $_glueType == self::GLUE_ORDERBY || $_glueType == self::GLUE_GROUPBY);
    }

    /**
     * Get the Probable Glue Values, filter by a prefix if necessary
     *
     * @author Varun Shoor
     * @param string $_gluePrefix
     * @param string $_caretGluePrefix (OPTIONAL)
     * @param int $_tokenCaretPosition (OPTIONAL)
     * @return array Results List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableGlueList($_gluePrefix, $_caretGluePrefix = '', $_tokenCaretPosition = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_resultsList = array();

        // Decide if we can use AND or OR, if we have used ORDER BY or GROUP BY, we cant!
        $_useANDorOR = true;
        if (in_array(self::GLUE_ORDERBY, $this->_usedExtendedGlues) || in_array(self::GLUE_GROUPBY, $this->_usedExtendedGlues)
            || $this->GetActiveField() == false || $this->GetActiveOperator() == false) {
            $_useANDorOR = false;
        }

        $_glueList = array();

        if ($_useANDorOR) {
            $_glueList[] = self::GLUE_AND;
            $_glueList[] = self::GLUE_OR;
        }

        if (!in_array(self::GLUE_ORDERBY, $this->_usedExtendedGlues)) {
            $_glueList[] = self::GLUE_ORDERBY;

            // If we have used the order by, we dont allow using any other glue
        } else {
            $_glueList = array();
        }

        if (!in_array(self::GLUE_GROUPBY, $this->_usedExtendedGlues)) {
            $_glueList[] = self::GLUE_GROUPBY;

            // If we have used group by, we only allow order by glue
        } else {
            $_glueList = array(self::GLUE_ORDERBY);
        }

        echo 'CARET GLUE PREFIX: ' . $_caretGluePrefix . SWIFT_CRLF;

        foreach ($_glueList as $_glueName) {
            $_returnGlueName = mb_strtoupper($_glueName);
            $_trimmedGlue = trim(substr($_glueName, 0, $_tokenCaretPosition));

            echo 'TRIMMED GLUE: ' . $_trimmedGlue . SWIFT_CRLF;

            if (trim($_caretGluePrefix) == '') {
                $_resultsList[] = array($_returnGlueName, $_returnGlueName . ' ');
            } elseif ($_trimmedGlue == mb_strtolower(trim($_caretGluePrefix))) {
                $_resultsList[] = array($_returnGlueName, $_returnGlueName . ' ');
            }
        }

        return $_resultsList;
    }

    /**
     * Add the provided glue type to the list of used glues
     *
     * @author Varun Shoor
     * @param mixed $_glueType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function AddToUsedGlues($_glueType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidGlue($_glueType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } elseif (in_array($_glueType, $this->_usedExtendedGlues)) {
            return true;
        }

        $this->_usedExtendedGlues[] = $_glueType;

        return true;
    }

    /**
     * Get the Active Caret Token for Group By Glue
     *
     * @author Varun Shoor
     * @param string $_fieldValuePrefix
     * @param int $_tokenCaretPosition
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetActiveCaretTokenForGroupBy($_fieldValuePrefix, $_tokenCaretPosition)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_nonParanthesisChunks = preg_replace(array('/^(\()/i'), array(''), $_fieldValuePrefix);
        $_tokenCaretPosition -= strlen($_fieldValuePrefix) - strlen($_nonParanthesisChunks);
        $_fieldValuePrefix = $_nonParanthesisChunks;

        $_finalToken = $_finalTokenPrefix = $_extendedClauseToken = $_extendedClauseTokenPrefix = '';

        $_fieldTokens = explode(',', $_fieldValuePrefix);
        if (!_is_array($_fieldTokens)) {
            $_fieldTokens = array($_fieldValuePrefix);
        }

        $_isExtendedClause = false;

        $_processedData = '';
        foreach ($_fieldTokens as $_index => $_token) {
            $_startOfToken = strlen($_processedData);

            // Take into account the ','
            if ($_index > 0) {
                $_startOfToken++;
                $_processedData .= ',';
            }

            $_processedData .= $_token;

            $_endOfToken = strlen($_processedData);


            // Process out the extended clause
            $_fieldToken = '';
            $_extendedClause = -1;
            if (strstr($_token, ':')) {
                $_tokenChunks = explode(':', $_token);
                $_fieldToken = $_tokenChunks[0];
                $_extendedClause = $_tokenChunks[1];
            } else {
                $_fieldToken = $_token;
            }


            $_endOfFieldToken = $_startOfToken + strlen($_fieldToken);
            $_startOfExtendedToken = $_startOfToken + strlen($_fieldToken);

            echo 'TOKENCARETPOSITION-STARTOFTOKEN-ENDOFTOKEN=' . $_tokenCaretPosition . '-' . $_startOfToken . '-' . $_endOfToken . SWIFT_CRLF;
            echo 'STARTOFEXTENDEDTOKEN: ' . $_startOfExtendedToken . SWIFT_CRLF;

            // Are we at token?
            if ($_tokenCaretPosition >= $_startOfToken && $_tokenCaretPosition <= $_endOfToken) {

                // Are we at extended clause?
                if ($_extendedClause != -1 && $_tokenCaretPosition > $_startOfExtendedToken) {
                    $_extendedClauseLength = 0;
                    if (!empty($_extendedClause)) {
                        $_extendedClauseLength = strlen($_extendedClause) + 1; // 1 = ':'
                    }
                    $_isExtendedClause = true;

                    $_extendedTokenPrefixLength = $_tokenCaretPosition - $_startOfToken - strlen($_fieldToken) - 1;
                    $_extendedClauseToken = $_extendedClause;
                    $_extendedClauseTokenPrefix = substr($_processedData, $_startOfExtendedToken + 1, $_extendedTokenPrefixLength);

                    $_tokenPrefixLength = $_tokenCaretPosition - $_startOfToken - strlen($_extendedClauseTokenPrefix) - 1;
                    $_finalToken = $_fieldToken;
                    $_finalTokenPrefix = substr($_processedData, $_startOfToken, $_tokenPrefixLength);

                    echo 'EXTENDECLAUSETOKEN: ' . $_extendedClauseToken . SWIFT_CRLF;
                    echo 'EXTENEDCLAUSETOKENPREFIX: ' . $_extendedClauseTokenPrefix . SWIFT_CRLF;

                } else {
                    $_tokenPrefixLength = $_tokenCaretPosition - $_startOfToken;
                    $_finalToken = $_fieldToken;
                    $_finalTokenPrefix = substr($_processedData, $_startOfToken, $_tokenPrefixLength);

                    echo 'PROCESSEDDATA: ' . $_processedData . SWIFT_CRLF;
                    echo 'FINALTOKENPREFIX: ' . $_finalTokenPrefix . SWIFT_CRLF;

                    break;
                }
            }

        }

        $_finalToken = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\()/i', '/(\))$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalToken)));
        $_finalTokenPrefix = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\()/i', '/(\))$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalTokenPrefix)));
        echo 'FINALTOKEN-FINALTOKENPREFIX: ' . $_finalToken . '-' . $_finalTokenPrefix . SWIFT_CRLF;

        return array($_finalToken, $_finalTokenPrefix, $_isExtendedClause, $_extendedClauseToken, $_extendedClauseTokenPrefix);
    }

    /**
     * Get the Probable Group By Values, filter by a prefix if necessary
     *
     * @author Varun Shoor
     * @param array $_tableList
     * @param bool $_enforceTableRestriction
     * @param string $_groupByPrefix
     * @param string $_caretGroupByPrefix (OPTIONAL)
     * @param int $_tokenCaretPosition (OPTIONAL)
     * @return array Results List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableGroupByList($_tableList, $_enforceTableRestriction, $_groupByPrefix, $_caretGroupByPrefix = '', $_tokenCaretPosition = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'GROUPBY: Getting Probable Group By List' . SWIFT_CRLF;

        // Has a preceding space?
        if (trim($_groupByPrefix) != '' && substr($_groupByPrefix, 0, 1) == ' ') {
            $_groupByPrefix = substr($_groupByPrefix, 1);
        }

        $_resultsList = $_probableFieldList = array();
        $_processingFieldValueList = $_fieldValueList = $_ignoreTokens = array();

        echo 'GROUPBYPREFIX: -' . $_groupByPrefix . '-' . SWIFT_CRLF;
        $_isExtendedClause = false;
        $_extendedClause = $_extendedClausePrefix = '';

        // If group by prefix is empty, we return the list of all probable fields
        if (trim($_groupByPrefix) == '') {
            return $this->GetProbableFieldList($_tableList, '', $_enforceTableRestriction, false, false);

            // Now we have to parse it all up
        } else {

            $_baseTokens = explode(',', $_groupByPrefix);
            if (_is_array($_baseTokens)) {
                $_activeCaretTokenContainer = self::GetActiveCaretTokenForGroupBy($_groupByPrefix, $_tokenCaretPosition);
                $_activeCaretToken = $_activeCaretTokenPrefix = '';

                if (_is_array($_activeCaretTokenContainer)) {
                    print_r($_activeCaretTokenContainer);

                    $_activeCaretToken = $_activeCaretTokenContainer[0];
                    $_activeCaretTokenPrefix = $_activeCaretTokenContainer[1];
                    $_isExtendedClause = $_activeCaretTokenContainer[2];
                    $_extendedClause = $_activeCaretTokenContainer[3];
                    $_extendedClausePrefix = $_activeCaretTokenContainer[4];
                }

                echo 'GROUPBY: ActiveCaretToken = ' . $_activeCaretToken . SWIFT_CRLF;
                echo 'GROUPBY: ActiveCaretTokenPrefix = ' . $_activeCaretTokenPrefix . SWIFT_CRLF;

                foreach ($_baseTokens as $_tokenText) {
                    if (substr($_tokenText, -1) == ';') {
                        $_tokenText = substr($_tokenText, 0, -1);
                    }

                    // Does this token have an extended clause?
                    $_extendedClauseChunk = '';
                    $_baseProcessedTokenInterim = $_tokenText;
                    if (strstr($_tokenText, ':')) {
                        $_extendedClauseChunk = mb_strtoupper(trim(substr($_tokenText, strrpos($_tokenText, ':') + 1)));

                        $_baseProcessedTokenInterim = trim(substr($_tokenText, 0, strrpos($_tokenText, ':')));

                        echo 'BASEPROCESSEDTOKENINTERIM: ' . $_baseProcessedTokenInterim . SWIFT_CRLF;
                    }

                    $_baseProcessedToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_baseProcessedTokenInterim)));

                    echo 'BASEPROCESSEDTOKEN: ' . $_baseProcessedToken . SWIFT_CRLF;
                    if ($_baseProcessedToken == $_activeCaretToken) {
                        $_probableFieldList = $this->GetProbableFieldList($_tableList, $_activeCaretTokenPrefix, $_enforceTableRestriction, false, false);

                        $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_activeCaretTokenPrefix)));
                        $_ignoreTokens[] = $_activeCaretToken;
                    }

                    $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_tokenText));
                }
            } else {
                $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_groupByPrefix)));
                $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_groupByPrefix));
            }

            print_r($_processingFieldValueList);
            print_r($_ignoreTokens);
        }


        /**
         * ---------------------------------------------
         * Now we need to rebuild it all up
         * ---------------------------------------------
         */

        $_baseTokens = explode(',', $_groupByPrefix);
        print_r($_probableFieldList);

        if (!_is_array($_probableFieldList)) {
            return array();
        }

        foreach ($_probableFieldList as $_fieldContainer) {
            $_displayText = $_fieldContainer[0];
            $_replacementText = $_fieldContainer[1];

            $_extendedClauseField = '';

            $_finalTokens = array();
            if (_is_array($_baseTokens)) {
                foreach ($_baseTokens as $_tokenText) {
                    if (substr($_tokenText, -1) == ';') {
                        $_tokenText = substr($_tokenText, 0, -1);
                    }

                    // Does this token have an extended clause?
                    $_baseProcessedTokenInterim = $_tokenText;
                    $_extendedClauseChunk = '';
                    if (strstr($_tokenText, ':')) {
                        $_extendedClauseChunk = trim(substr($_tokenText, strrpos($_tokenText, ':') + 1));

                        $_baseProcessedTokenInterim = trim(substr($_tokenText, 0, strrpos($_tokenText, ':')));

                        echo 'BASEPROCESSEDTOKENINTERIM: ' . $_baseProcessedTokenInterim . SWIFT_CRLF;
                    }

                    $_baseProcessedToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_baseProcessedTokenInterim)));

                    $_extendedClauseSuffix = '';
                    if (!empty($_extendedClauseChunk)) {
                        $_extendedClauseSuffix = ':' . $_extendedClauseChunk;
                    }

                    if (in_array($_baseProcessedToken, $_ignoreTokens)) {
                        if ($_isExtendedClause) {
                            $_extendedClauseField = trim($_replacementText);
                            $_finalTokens[] = rtrim($_replacementText) . ':%extendedclause%';
                        } else {
                            if ($this->IsValidGroupByExtendedClause(trim($_replacementText), $_extendedClauseChunk)) {
                                $_finalTokens[] = rtrim($_replacementText) . $_extendedClauseSuffix;
                            } else {
                                $_finalTokens[] = rtrim($_replacementText);
                            }
                        }
                    } else {
                        $_finalTokens[] = trim($_tokenText);
                    }
                }
            }

            if ($_isExtendedClause) {
                return $this->GetProbableExtendedClauseList(implode(', ', $_finalTokens), $_extendedClauseField, $_extendedClause, $_extendedClausePrefix);
            }

            $_resultsList[] = array($_displayText, implode(', ', $_finalTokens));
        }

        return $_resultsList;
    }

    /**
     * Get Probable Extended Clause List
     *
     * @author Varun Shoor
     * @param string $_finalTokens
     * @param string $_extendedClauseField
     * @param string $_extendedClause
     * @param string $_extendedClausePrefix
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableExtendedClauseList($_finalTokens, $_extendedClauseField, $_extendedClause, $_extendedClausePrefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableAndFieldNameContainer = $this->GetTableAndFieldNameOnText($_extendedClauseField);
        if (empty($_tableAndFieldNameContainer[0]) || empty($_tableAndFieldNameContainer[1])) {
            return array();
        }

        $_tableName = $_tableAndFieldNameContainer[0];
        $_fieldName = $_tableAndFieldNameContainer[1];
        if (!isset($this->_schemaContainer[$_tableName]) || !isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName])) {
            return array();
        }

        $_fieldContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName];
        $_fieldType = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE];

        // Are we allowed to set extended clauses for this field?
        if (!isset(self::$_extendedClauses[$_fieldType])) {
            return array();
        }

        $_extendedClausesList = self::$_extendedClauses[$_fieldType];

        $_resultsList = array();
        $_extendedClause = trim(mb_strtolower($_extendedClause));
        $_extendedClausePrefix = trim(mb_strtolower($_extendedClausePrefix));

        foreach ($_extendedClausesList as $_extendedClauseBase) {
            $_extendedClauseBaseInterim = mb_strtolower($_extendedClauseBase);

            if (substr($_extendedClauseBaseInterim, 0, strlen($_extendedClausePrefix)) == $_extendedClausePrefix) {
                $_resultsList[] = array($_extendedClauseBase, str_replace('%extendedclause%', $_extendedClauseBase, $_finalTokens));
            }
        }

        return $_resultsList;
    }

    /**
     * Check to see if its a valid Group By Extended Clause
     *
     * @author Varun Shoor
     * @param string $_extendedClauseField
     * @param string $_extendedClausePrefix
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function IsValidGroupByExtendedClause($_extendedClauseField, $_extendedClausePrefix)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_extendedClauseField = trim($_extendedClauseField);
        $_extendedClausePrefix = trim($_extendedClausePrefix);

        $_tableAndFieldNameContainer = $this->GetTableAndFieldNameOnText($_extendedClauseField);
        if (empty($_tableAndFieldNameContainer[0]) || empty($_tableAndFieldNameContainer[1])) {
            return false;
        }

        $_tableName = $_tableAndFieldNameContainer[0];
        $_fieldName = $_tableAndFieldNameContainer[1];
        if (!isset($this->_schemaContainer[$_tableName]) || !isset($this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName])) {
            return false;
        }

        $_fieldContainer = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName];
        $_fieldType = $this->_schemaContainer[$_tableName][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName][SWIFT_KQLSchema::FIELD_TYPE];

        // Are we allowed to set extended clauses for this field?
        if (!isset(self::$_extendedClauses[$_fieldType])) {
            return false;
        }

        $_extendedClausePrefix = mb_strtolower($_extendedClausePrefix);
        $_extendedClausesList = self::$_extendedClauses[$_fieldType];

        foreach ($_extendedClausesList as $_extendedClause) {
            $_extendedClause = mb_strtolower($_extendedClause);

            if (substr($_extendedClause, 0, strlen($_extendedClausePrefix)) == $_extendedClausePrefix) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get the Active Caret Token for Order By Glue
     *
     * @author Varun Shoor
     * @param string $_fieldValuePrefix
     * @param int $_tokenCaretPosition
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetActiveCaretTokenForOrderBy($_fieldValuePrefix, $_tokenCaretPosition)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_nonParanthesisChunks = preg_replace(array('/^(\()/i'), array(''), $_fieldValuePrefix);
        $_tokenCaretPosition -= strlen($_fieldValuePrefix) - strlen($_nonParanthesisChunks);
        $_fieldValuePrefix = $_nonParanthesisChunks;

        $_finalToken = $_finalTokenPrefix = $_sortOrderToken = $_sortOrderTokenPrefix = '';

        $_fieldTokens = explode(',', $_fieldValuePrefix);
        if (!_is_array($_fieldTokens)) {
            $_fieldTokens = array($_fieldValuePrefix);
        }

        $_isAtSortOrder = false;

        $_processedData = '';
        foreach ($_fieldTokens as $_index => $_token) {
            if (substr($_token, -1) == ';') {
                $_token = substr($_token, 0, strlen($_token) - 1);
            }
            $_startOfToken = strlen($_processedData);

            // Take into account the ','
            if ($_index > 0) {
                $_startOfToken++;
                $_processedData .= ',';
            }

            $_processedData .= $_token;

            $_endOfToken = strlen($_processedData);


            // Process out the sort order
            $_fieldToken = '';
            $_sortOrder = -1;
            if (strrpos($_token, ' ')) {
                $_sortOrderInterim = substr($_token, strrpos($_token, ' ') + 1);
                echo 'SORTORDERINTERIM: ' . $_sortOrderInterim . SWIFT_CRLF;
                if (self::IsValidInterimSortOrder($_sortOrderInterim)) {
                    $_sortOrder = $_sortOrderInterim;
                    $_fieldToken = substr($_token, 0, strrpos($_token, ' '));
                } else {
                    $_fieldToken = $_token;
                }
            } else {
                $_fieldToken = $_token;
            }


            $_endOfFieldToken = $_startOfToken + strlen($_fieldToken);
            $_startOfSortToken = $_startOfToken + strlen($_fieldToken);

            echo 'TOKENCARETPOSITION-STARTOFTOKEN-ENDOFTOKEN=' . $_tokenCaretPosition . '-' . $_startOfToken . '-' . $_endOfToken . SWIFT_CRLF;
            echo 'STARTOFSORTTOKEN: ' . $_startOfSortToken . SWIFT_CRLF;

            // Are we at token?
            if ($_tokenCaretPosition >= $_startOfToken && $_tokenCaretPosition <= $_endOfToken) {

                // Are we at sort token?
                if ($_sortOrder != -1 && $_tokenCaretPosition > $_startOfSortToken) {
                    $_sortTokenLength = 0;
                    if (!empty($_sortOrder)) {
                        $_sortTokenLength = strlen($_sortOrder) + 1; // 1 = ' '
                    }
                    $_isAtSortOrder = true;

                    $_sortOrderPrefixLength = $_tokenCaretPosition - $_startOfToken - strlen($_fieldToken) - 1;
                    $_sortOrderToken = $_sortOrder;
                    $_sortOrderTokenPrefix = substr($_processedData, $_startOfSortToken + 1, $_sortOrderPrefixLength);

                    $_tokenPrefixLength = $_tokenCaretPosition - $_startOfToken - strlen($_sortOrderTokenPrefix) - 1;
                    $_finalToken = $_fieldToken;
                    $_finalTokenPrefix = substr($_processedData, $_startOfToken, $_tokenPrefixLength);

                    echo 'SORTORDERTOKEN: ' . $_sortOrderToken . SWIFT_CRLF;
                    echo 'SORTORDERTOKENPREFIX: ' . $_sortOrderTokenPrefix . SWIFT_CRLF;

                } else {
                    $_tokenPrefixLength = $_tokenCaretPosition - $_startOfToken;
                    $_finalToken = $_fieldToken;
                    $_finalTokenPrefix = substr($_processedData, $_startOfToken, $_tokenPrefixLength);

                    echo 'SORTORDER: ' . $_sortOrder . SWIFT_CRLF;
                    echo 'PROCESSEDDATA: ' . $_processedData . SWIFT_CRLF;
                    echo 'FINALTOKENPREFIX: ' . $_finalTokenPrefix . SWIFT_CRLF;

                    break;
                }
            }

        }

        $_finalToken = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\()/i', '/(\))$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalToken)));
        $_finalTokenPrefix = mb_strtolower(preg_replace(array('/^(,)/i', '/(,)$/i', '/^(\()/i', '/(\))$/i', '/^(\'|")/i', '/(\'|")$/i'), array('', '', '', '', '', ''), trim($_finalTokenPrefix)));
        echo 'FINALTOKEN-FINALTOKENPREFIX: ' . $_finalToken . '-' . $_finalTokenPrefix . SWIFT_CRLF;

        return array($_finalToken, $_finalTokenPrefix, $_isAtSortOrder, $_sortOrderToken, $_sortOrderTokenPrefix);
    }

    /**
     * Get the Probable Order By Values, filter by a prefix if necessary
     *
     * @author Varun Shoor
     * @param array $_tableList
     * @param bool $_enforceTableRestriction
     * @param string $_orderByPrefix
     * @param string $_caretOrderByPrefix (OPTIONAL)
     * @param int $_tokenCaretPosition (OPTIONAL)
     * @return array Results List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableOrderByList($_tableList, $_enforceTableRestriction, $_orderByPrefix, $_caretOrderByPrefix = '', $_tokenCaretPosition = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'ORDERBY: Getting Probable Order By List' . SWIFT_CRLF;

        // Has a preceding space?
        if (trim($_orderByPrefix) != '' && substr($_orderByPrefix, 0, 1) == ' ') {
            $_orderByPrefix = substr($_orderByPrefix, 1);
        }

        $_resultsList = $_probableFieldList = array();
        $_processingFieldValueList = $_fieldValueList = $_ignoreTokens = array();

        echo 'ORDERBYPREFIX: -' . $_orderByPrefix . '-' . SWIFT_CRLF;

        // If order by prefix is empty, we return the list of all probable fields
        $_isAtSortOrder = false;
        $_activeCaretToken = $_activeCaretTokenPrefix = $_sortOrderToken = $_sortOrderTokenPrefix = '';
        if (trim($_orderByPrefix) == '') {
            return $this->GetProbableFieldList($_tableList, '', $_enforceTableRestriction, false, false);

            // Now we have to parse it all up
        } else {

            $_baseTokens = explode(',', $_orderByPrefix);
            if (_is_array($_baseTokens)) {
                $_activeCaretTokenContainer = self::GetActiveCaretTokenForOrderBy($_orderByPrefix, $_tokenCaretPosition);

                if (_is_array($_activeCaretTokenContainer)) {
                    $_activeCaretToken = $_activeCaretTokenContainer[0];
                    $_activeCaretTokenPrefix = $_activeCaretTokenContainer[1];
                    $_isAtSortOrder = $_activeCaretTokenContainer[2];
                    $_sortOrderToken = $_activeCaretTokenContainer[3];
                    $_sortOrderTokenPrefix = $_activeCaretTokenContainer[4];
                }

                echo 'ORDERBY: ActiveCaretToken = ' . $_activeCaretToken . SWIFT_CRLF;
                echo 'ORDERBY: ActiveCaretTokenPrefix = ' . $_activeCaretTokenPrefix . SWIFT_CRLF;

                // Support for CUSTOMFIELD(...) ASC/DESC
                if (mb_strtolower(substr(trim($_activeCaretTokenPrefix), 0, strpos($_activeCaretTokenPrefix, '('))) == 'customfield') {
                    if ($_isAtSortOrder) {

                        $_sortOrder = mb_strtoupper(trim(substr($_caretOrderByPrefix, strrpos($_caretOrderByPrefix, ' ') + 1)));

                        $_processedToken = $_caretOrderByPrefix;
                        if (self::IsValidInterimSortOrder($_sortOrder)) {
                            $_processedToken = trim(substr($_caretOrderByPrefix, 0, strrpos($_caretOrderByPrefix, ' ')));
                        }

                        echo 'ISATSORTORDER: ' . $_processedToken . SWIFT_CRLF;
                        return $this->GetProbableSortOrder($_processedToken . ' %sortorder%', $_sortOrderToken, $_sortOrderTokenPrefix);
                    }
                }

                foreach ($_baseTokens as $_tokenText) {
                    if (substr($_tokenText, -1) == ';') {
                        $_tokenText = substr($_tokenText, 0, -1);
                    }

                    $_sortOrder = mb_strtoupper(trim(substr($_tokenText, strrpos($_tokenText, ' ') + 1)));

                    $_baseProcessedTokenInterim = $_tokenText;
                    if (self::IsValidInterimSortOrder($_sortOrder)) {
                        $_baseProcessedTokenInterim = trim(substr($_tokenText, 0, strrpos($_tokenText, ' ')));
                    }
                    echo 'BASEPROCESSEDTOKENINTERIM: ' . $_baseProcessedTokenInterim . SWIFT_CRLF;

                    $_baseProcessedToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_baseProcessedTokenInterim)));

                    echo 'BASEPROCESSEDTOKEN: ' . $_baseProcessedToken . SWIFT_CRLF;
                    if ($_baseProcessedToken == $_activeCaretToken) {
                        $_probableFieldList = $this->GetProbableFieldList($_tableList, $_activeCaretTokenPrefix, $_enforceTableRestriction, false, false);

                        $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_activeCaretTokenPrefix)));
                        $_ignoreTokens[] = $_activeCaretToken;
                    }

                    $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_tokenText));
                }
            } else {
                $_processingFieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_orderByPrefix)));
                $_fieldValueList[] = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), trim($_orderByPrefix));
            }

            print_r($_processingFieldValueList);
            print_r($_ignoreTokens);
        }


        /**
         * ---------------------------------------------
         * Now we need to rebuild it all up
         * ---------------------------------------------
         */

        $_baseTokens = explode(',', $_orderByPrefix);

        echo 'PROBABLEFIELDLIST: ' . SWIFT_CRLF;
        print_r($_probableFieldList);

        foreach ($_probableFieldList as $_fieldContainer) {
            $_displayText = $_fieldContainer[0];
            $_replacementText = $_fieldContainer[1];

            $_finalTokens = array();
            if (_is_array($_baseTokens)) {
                foreach ($_baseTokens as $_tokenText) {
                    if (substr($_tokenText, -1) == ';') {
                        $_tokenText = substr($_tokenText, 0, -1);
                    }

                    $_sortOrder = mb_strtoupper(trim(substr($_tokenText, strrpos($_tokenText, ' ') + 1)));

                    $_baseProcessedTokenInterim = $_tokenText;
                    if (self::IsValidInterimSortOrder($_sortOrder)) {
                        $_baseProcessedTokenInterim = trim(substr($_tokenText, 0, strrpos($_tokenText, ' ')));
                    } else {
                        $_sortOrder = '';
                    }

                    $_baseProcessedToken = preg_replace(array('/^(\'|")/i', '/(\'|")$/i'), array('', ''), mb_strtolower(trim($_baseProcessedTokenInterim)));

                    if (in_array($_baseProcessedToken, $_ignoreTokens)) {
                        if ($_isAtSortOrder) {
                            $_finalTokens[] = trim($_replacementText) . ' %sortorder%';
                        } else {
                            $_finalTokens[] = trim($_replacementText) . IIF(!empty($_sortOrder), ' ' . $_sortOrder);
                        }
                    } else {
                        $_finalTokens[] = trim($_tokenText);
                    }
                }
            }

            if ($_isAtSortOrder) {
                echo 'ISATSORTORDER: ' . implode(', ', $_finalTokens) . SWIFT_CRLF;
                return $this->GetProbableSortOrder(implode(', ', $_finalTokens), $_sortOrderToken, $_sortOrderTokenPrefix);
            }

            $_resultsList[] = array($_displayText, implode(', ', $_finalTokens));
        }

        return $_resultsList;
    }

    /**
     * Get Probable Sort Order
     *
     * @author Varun Shoor
     * @param string $_sortOrderHaystack
     * @param string $_sortOrderToken
     * @param string $_sortOrderTokenPrefix
     * @return array Results List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableSortOrder($_sortOrderHaystack, $_sortOrderToken, $_sortOrderTokenPrefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sortOrderToken = mb_strtoupper($_sortOrderToken);
        $_sortOrderTokenPrefix = mb_strtoupper($_sortOrderTokenPrefix);

        // If we have the full token, we dont return anything
        if ($_sortOrderTokenPrefix == 'ASC' || $_sortOrderTokenPrefix == 'DESC') {
            return array();
        }

        $_resultsList = array();

        $_sortOrderList = array('ASC', 'DESC');
        foreach ($_sortOrderList as $_sortOrder) {
            if (substr($_sortOrder, 0, strlen($_sortOrderTokenPrefix)) == $_sortOrderTokenPrefix) {
                $_resultsList[] = array($_sortOrder, str_replace('%sortorder%', $_sortOrder, $_sortOrderHaystack));
            }
        }

        return $_resultsList;
    }

    /**
     * Get Probable CUSTOMFIELD() Function Arguments
     *
     * @author Andriy Lesyuk
     * @param string $_baseTableName
     * @param array $_tableList
     * @param string $_customFieldPrefix
     * @param int $_tokenCaretPosition
     * @return array Results List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProbableCustomFieldArgumentList($_baseTableName, $_tableList, $_customFieldPrefix, $_tokenCaretPosition)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Prepare arguments array
        $_offset = 0;
        $_prefix = substr($_customFieldPrefix, 0, $_tokenCaretPosition);
        $_argumentTokens = explode(',', $_prefix);
        if (!_is_array($_argumentTokens)) {
            $_argumentTokens = array($_prefix);
        }
        foreach ($_argumentTokens as $_argumentIndex => $_argumentToken) {
            if ($_argumentIndex < (count($_argumentTokens) - 1)) {
                $_offset += strlen($_argumentToken);
            }
            if ($_argumentIndex > 0) {
                $_offset++;
            }

            $_argumentTokens[$_argumentIndex] = preg_replace(array('/^(["\'])/', '/(["\'])$/'), array('', ''), trim($_argumentTokens[$_argumentIndex]));
        }

        // Get search string
        $_searchString = end($_argumentTokens);
        if (!empty($_searchString) && ($_searchString == '*')) {
            $_searchString = false;
        }

        // Check if the first argument looks to be table name
        $_tableSpecified = false;
        if (count($_argumentTokens) > 0) {
            $_tableLabelResult = $this->Database->Escape(Clean($this->GetTableNameOnLabel(mb_strtolower($_argumentTokens[0]))));
            if (!empty($_tableLabelResult) && isset($this->_schemaContainer[$_tableLabelResult])) {
                $_baseTableName = $_tableLabelResult;
                $_tableSpecified = true;
            }
        }

        $_tables = array();
        $_customFields = array();
        $_customFieldGroups = array();

        // Get list of tables
        if ((count($_argumentTokens) == 1) && !$_tableSpecified) {

            foreach ($_tableList as $_tableName) {
                if (!isset($this->_schemaContainer[$_tableName])) {
                    continue;
                }
                if ($_baseTableName == $_tableName) {
                    continue;
                }

                $_groupTypeList = SWIFT_KQLParser::GetCustomFieldGroupTypesByTable($_tableName);
                if (empty($_groupTypeList)) {
                    continue;
                }

                $_tableLabel = SWIFT_KQLSchema::GetLabel($_tableName);
                if (empty($_tableLabel)) {
                    $_tableLabel = $_tableName;
                }

                if (!empty($_searchString) &&
                    (mb_strtolower(substr($_tableLabel, 0, strlen($_searchString))) != mb_strtolower($_searchString))) {
                    continue;
                }

                $_tables[$_tableName] = $_tableLabel;
            }

        }

        $_groupName = false;
        if ((count($_argumentTokens) > 1) && !$_tableSpecified) {
            $_groupName = $_argumentTokens[0];
        } elseif ((count($_argumentTokens) > 2) && $_tableSpecified) {
            $_groupName = $_argumentTokens[1];
        }
        if (!empty($_groupName) && ($_groupName == '*')) {
            $_groupName = false;
        }

        // Skip if custom fields are not supported for this table
        $_groupTypeList = SWIFT_KQLParser::GetCustomFieldGroupTypesByTable($_baseTableName);
        if (!empty($_groupTypeList)) {

            // Get list of custom fields
            $_sqlExpression = "SELECT customfields.title, customfieldgroups.title AS group_title
                FROM " . TABLE_PREFIX . "customfields customfields
                LEFT JOIN " . TABLE_PREFIX . "customfieldgroups customfieldgroups ON (customfieldgroups.customfieldgroupid = customfields.customfieldgroupid)
                WHERE";

            if (!empty($_groupName)) {
                $_sqlExpression .= " LOWER(customfieldgroups.title) = '" . $this->Database->Escape(mb_strtolower($_groupName)) . "'";
            } else {
                $_sqlExpression .= " customfieldgroups.grouptype IN (" . BuildIN($_groupTypeList) . ")";
            }

            if ($this->GetPreviousMode() != self::MODE_SELECT) {
                $_sqlExpression .= " AND customfields.encryptindb = '0'";
            }

            if (!empty($_searchString)) {
                $_sqlExpression .= " AND customfields.title LIKE '" . $this->Database->Escape($_searchString) . "%'";
            }

            $_sqlExpression .= " ORDER BY customfields.title, customfieldgroups.title";

            $this->Database->Query($_sqlExpression);
            while ($this->Database->NextRecord()) {
                $_customFieldID = mb_strtolower($this->Database->Record['title']);
                if (isset($_customFields[$_customFieldID])) {
                    if (!is_array($_customFields[$_customFieldID][1])) {
                        $_customFields[$_customFieldID][1] = array($_customFields[$_customFieldID][1]);
                    }
                    $_customFields[$_customFieldID][1][] = $this->Database->Record['group_title'];
                } else {
                    $_customFields[$_customFieldID] = array($this->Database->Record['title'], $this->Database->Record['group_title']);
                }

                if (empty($_groupName)) {
                    if (!in_array($this->Database->Record['group_title'], $_customFieldGroups)) {
                        $_customFieldGroups[] = $this->Database->Record['group_title'];
                    }
                }
            }

            // Get list of groups
            if (empty($_groupName) && !empty($_searchString)) { // Groups were not fetched correctly
                $_customFieldGroups = array();

                $_sqlExpression = "SELECT customfieldgroups.title
                    FROM " . TABLE_PREFIX . "customfieldgroups customfieldgroups
                    WHERE customfieldgroups.grouptype IN (" . BuildIN($_groupTypeList) . ") AND customfieldgroups.title LIKE '" . $this->Database->Escape($_searchString) . "%'";

                $this->Database->Query($_sqlExpression);
                while ($this->Database->NextRecord()) {
                    $_customFieldGroups[] = $this->Database->Record['title'];
                }
            }

        }

        $_resultsList = array();

        $_customFieldPrefixLength = strlen($_customFieldPrefix);

        // NOTE: A temporary workaround
        $_customFieldTokens = explode(',', $_customFieldPrefix);
        foreach ($_customFieldTokens as $_customFieldToken) {
            $_matchCount = preg_match('/\'/', preg_replace(array('/^(["\'])/', '/(["\'])$/'), array('', ''), trim($_customFieldToken)));
            if ($_matchCount) {
                $_customFieldPrefixLength += $_matchCount;
            }
        }

        // Tables
        foreach ($_tables as $_tableName => $_tableLabel) {
            $_resultsList[] = array($_tableLabel . ', ...', "'" . $_tableLabel . "', ");

            if (count($_resultsList) >= self::CAP_FIELDNAMES) {
                break;
            }
        }

        // Custom fields
        if (count($_resultsList) < self::CAP_FIELDNAMES) {
            foreach ($_customFields as $_customFieldID => $_customFieldGroupContainer) {
                if (is_array($_customFieldGroupContainer[1])) {
                    foreach ($_customFieldGroupContainer[1] as $_customFieldGroup) {
                        $_resultsList[] = array($_customFieldGroupContainer[0] . ' (' . $_customFieldGroup . ')', IIF($_offset > 0, ' ') . "'" . str_replace("'", "\\'", $_customFieldGroup) . "', '" . str_replace("'", "\\'", $_customFieldGroupContainer[0]) . "'");

                        if (count($_resultsList) >= self::CAP_FIELDNAMES) {
                            break;
                        }
                    }
                } else {
                    $_resultsList[] = array($_customFieldGroupContainer[0], IIF($_offset > 0, ' ') . "'" . str_replace("'", "\\'", $_customFieldGroupContainer[0]) . "'");
                }

                if (count($_resultsList) >= self::CAP_FIELDNAMES) {
                    break;
                }
            }
        }

        // Custom field groups
        if (count($_resultsList) < self::CAP_FIELDNAMES) {
            foreach ($_customFieldGroups as $_customFieldGroup) {
                $_resultsList[] = array($_customFieldGroup . ', ...', IIF($_offset > 0, ' ') . "'" . str_replace("'", "\\'", $_customFieldGroup) . "', ");

                if (count($_resultsList) >= self::CAP_FIELDNAMES) {
                    break;
                }
            }
        }

        return array($_offset, $_customFieldPrefixLength, $_resultsList);
    }

    /**
     * Is it a valid interim sort order?
     *
     * @author Varun Shoor
     * @param string $_interimSortOrder
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function IsValidInterimSortOrder($_interimSortOrder)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_interimSortOrder = mb_strtoupper($_interimSortOrder);

        $_sortOrderList = array('ASC', 'DESC');

        foreach ($_sortOrderList as $_sortOrder) {
            if (substr($_sortOrder, 0, strlen($_interimSortOrder)) == $_interimSortOrder) {
                return true;
            }
        }

        return false;
    }

    /**
     * Smart Fields Splitting
     *
     * @author Andriy Lesyuk
     * @param string $_selectPrefix
     * @return array The Splitted Fields
     */
    protected static function ExplodeFields($_selectPrefix)
    {
        $_fieldChunk = '';
        $_fieldChunks = $_fieldTokens = array();

        $_prevChar = false;
        for ($_i = 0; $_i < strlen($_selectPrefix); $_i++) {
            $_char = substr($_selectPrefix, $_i, 1);

            $_nextChar = false;
            for ($_j = $_i + 1; $_j < strlen($_selectPrefix); $_j++) {
                $_nextChar = substr($_selectPrefix, $_j, 1);
                if ($_nextChar == ' ') {
                    $_nextChar = false;
                } else {
                    break;
                }
            }

            $_lastToken = end($_fieldTokens);

            // Open single quote
            if (($_char == '\'') && ($_lastToken != '\'') && ($_lastToken != '"') && (empty($_prevChar) || ($_prevChar == ',') || ($_prevChar == '('))) {
                $_fieldTokens[] = $_char;

                // Open double quote
            } elseif (($_char == '"') && ($_lastToken != '"') && ($_lastToken != '\'') && (empty($_prevChar) || ($_prevChar == ',') || ($_prevChar == '('))) {
                $_fieldTokens[] = $_char;

                // Close single quote
            } elseif (($_char == '\'') && ($_lastToken == '\'') && (empty($_nextChar) || ($_nextChar == ',') || ($_nextChar == ')'))) {
                array_pop($_fieldTokens);

                // Close double quote
            } elseif (($_char == '"') && ($_lastToken == '"') && (empty($_nextChar) || ($_nextChar == ',') || ($_nextChar == ')'))) {
                array_pop($_fieldTokens);

                // Open paranthesis
            } elseif (($_char == '(') && ($_lastToken != '\'') && ($_lastToken != '"')) {
                $_fieldTokens[] = $_char;

                // Close paranthesis
            } elseif (($_char == ')') && ($_lastToken == '(')) {
                array_pop($_fieldTokens);
            }

            if ($_char != ' ') {
                $_prevChar = $_char;
            }

            if (($_char == ',') && empty($_fieldTokens)) {
                $_fieldChunks[] = $_fieldChunk;
                $_fieldChunk = '';
            } else {
                $_fieldChunk .= $_char;
            }
        }

        $_fieldChunks[] = $_fieldChunk;

        return $_fieldChunks;
    }

}

?>
