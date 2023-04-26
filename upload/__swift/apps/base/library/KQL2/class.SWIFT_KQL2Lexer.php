<?php
/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-QuickSupport Singapore Pte. Ltd.h Ltd.
 * @license    http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 * @filesource
 * ###################################
 * =======================================
 */

namespace Base\Library\KQL2;

use Base\Library\KQL2\SWIFT_KQL2;
use SWIFT_Library;

\SWIFT_Loader::LoadLibrary('KQL2:KQL2', 'base');

/**
 * The KQL Lexical Analyzer
 *
 * @author Andriy Lesyuk
 */
class SWIFT_KQL2Lexer extends SWIFT_Library
{
    // Query properties
    protected $_kql = '';
    protected $_length = 0;

    // Run time variables
    protected $_offset = 0;
    protected $_type = 0x00;
    protected $_token = '';
    protected $_subString = '';
    protected $_start = 0;
    protected $_end = 0;

    // States
    protected $_quote = false;
    protected $_error = 0;

    static protected $_operatorChars = array('!', '%', '&', '*', '/', '<', '=', '>', '^', '|', '~'); // Add operator characters here

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     * @param string $_kql
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_kql = '')
    {
        parent::__construct();

        $this->_kql = $_kql;
        $this->_length = strlen($_kql);
    }

    /**
     * Returns Next Token
     *
     * @author Andriy Lesyuk
     * @return string|null The Token
     */
    public function NextToken()
    {
        $this->_subString = '';
        $this->_token = '';
        $this->_type = 0x00;

        $this->_start = $this->_offset;
        $this->_end = $this->_offset;

        if ($this->IsErroneous() || $this->EndOfQuery()) {
            return null;
        }

        while (!$this->EndOfQuery()) {
            $_char = $this->Get();

            if ($this->_quote != false) {
                if ($_char == $this->_quote) {
                    $this->_type = SWIFT_KQL2::TYPE_OTHER;
                    $this->_subString .= $_char;
                    $this->_quote = false;
                    break;
                } elseif ($_char == '\\') {
                    $this->_subString .= $_char;
                    $_char = $this->Get();
                    // For backwards compatibility with SQL_Parser_Lexer
                    if (($_char != "'") && ($_char != '\\') && ($_char != '"') && ($_char != '`')) {
                        $this->_token .= '\\';
                    }
                    $this->_subString .= $_char;
                    $this->_token .= $_char;
                } else {
                    $this->_subString .= $_char;
                    $this->_token .= $_char;
                }

            } else {
                $_code = ord($_char);

                if (($_char == "'") || ($_char == '"') || ($_char == '`')) {
                    if (self::TypeIsUnknown($this->_type)) {
                        $this->_subString .= $_char;
                        $this->_quote = $_char;
                    } else {
                        $this->PushBack();
                        break;
                    }
                } elseif (($_char == ',') || ($_char == '(') || ($_char == ')') || ($_char == ':') || ($_char == '$')) {
                    if (self::TypeIsUnknown($this->_type)) {
                        $this->_type = SWIFT_KQL2::TYPE_SPEC;
                        $this->_subString .= $_char;
                        $this->_token = $_char;
                    } else {
                        $this->PushBack();
                    }
                    break;
                } elseif (($_char == ' ') || ($_char == "\t") || ($_char == "\n") || ($_char == "\r")) {
                    if (self::TypeIsSpace($this->_type)) {
                        $this->_type |= SWIFT_KQL2::TYPE_SPACE;
                        $this->_subString .= $_char;
                        $this->_token = ' '; // Always set single space
                    } else {
                        $this->PushBack();
                        break;
                    }
                } elseif (($_char == '+') || ($_char == '-')) { // Operators or beginning of real number
                    if (self::TypeIsUnknown($this->_type)) {
                        $this->_type |= SWIFT_KQL2::TYPE_SIGN;
                        $this->_subString .= $_char;
                        $this->_token .= $_char;
                    } else {
                        $this->PushBack();
                        break;
                    }
                } elseif ($_char == '.') { // Can be a part of real number or field name
                    if (self::TypeIsUnknown($this->_type) || self::TypeIsRealNumber($this->_type) ||
                        (!self::TypeIsUnknown($this->_type) && self::TypeIsFieldName($this->_type))) {
                        $this->_type |= SWIFT_KQL2::TYPE_DOT;
                        $this->_subString .= $_char;
                        $this->_token .= $_char;
                    } else {
                        $this->PushBack();
                        break;
                    }
                } elseif (($_char == '*') && $this->GetPrevious() && ($this->GetPrevious() == '.')) { // Field name can end with it (e.g. .*)
                    if (self::TypeIsFieldName($this->_type)) {
                        $this->_type |= SWIFT_KQL2::TYPE_ASTERISK;
                        $this->_subString .= $_char;
                        $this->_token .= $_char;
                    } else {
                        $this->PushBack();
                    }
                    break;
                } elseif (in_array($_char, self::$_operatorChars)) { // Operators
                    if (self::TypeIsUnknown($this->_type)) {
                        $this->_type = SWIFT_KQL2::TYPE_SPEC;
                        $this->_subString .= $_char;
                        $this->_token = $_char;
                    } else {
                        $this->PushBack();
                    }
                    break;
                } elseif ((($_code >= 65) && ($_code <= 90)) ||  // A-Z
                    (($_code >= 97) && ($_code <= 122)) || // a-z
                    ($_char == '_')) {                    // _
                    if (self::TypeIsFieldName($this->_type)) {
                        $this->_type |= SWIFT_KQL2::TYPE_ALPHA;
                        $this->_subString .= $_char;
                        $this->_token .= $_char;
                    } else {
                        $this->PushBack();
                        break;
                    }
                } elseif (($_code >= 48) && ($_code <= 57)) { // 0-9
                    if (self::TypeIsRealNumber($this->_type)) {
                        $this->_type |= SWIFT_KQL2::TYPE_NUMBER;
                        $this->_subString .= $_char;
                        $this->_token .= $_char;
                    } elseif (self::TypeIsFieldName($this->_type)) {
                        $this->_type |= SWIFT_KQL2::TYPE_NUMBER;
                        $this->_subString .= $_char;
                        $this->_token .= $_char;
                    } else {
                        $this->PushBack();
                        break;
                    }
                } elseif ($_char == ';') { // End of query
                    $this->_length = $this->_offset;
                    break;
                } else {
                    if (self::TypeIsUnknown($this->_type)) {
                        $this->_type = SWIFT_KQL2::TYPE_ERROR;
                        $this->_error = $this->_offset;
                        $this->_subString .= $_char;
                        $this->_token = $_char;
                    } else {
                        $this->PushBack();
                    }
                    break;
                }
            }
        }

        $this->_end = $this->_offset;

        return $this->_token;
    }

    /**
     * Returns Next Character
     *
     * @author Andriy Lesyuk
     * @return string|null The Next Character
     */
    protected function Get()
    {
        if ($this->_offset < $this->_length) {
            return $this->_kql[$this->_offset++];
        } else {
            return null;
        }
    }

    /**
     * Returns the Previous Character
     *
     * @author Andriy Lesyuk
     * @return string|null The Previous Character
     */
    protected function GetPrevious()
    {
        if ($this->_offset > 1) {
            return $this->_kql[$this->_offset - 2];
        } else {
            return null;
        }
    }

    /**
     * Returns Character to "Stream"
     *
     * @author Andriy Lesyuk
     * @return string|null The Character Returned
     */
    protected function PushBack()
    {
        if ($this->_offset > 0) {
            return $this->_kql[--$this->_offset];
        } else {
            return null;
        }
    }

    /**
     * Indicates If This Is the End of Query
     *
     * @author Andriy Lesyuk
     * @return bool True if End of Query, False Otherwise
     */
    protected function EndOfQuery()
    {
        if ($this->_offset < $this->_length) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get Token Type
     *
     * @author Andriy Lesyuk
     * @return int The Token Type
     */
    public function GetTokenType()
    {
        return $this->_type;
    }

    /**
     * Get Token (Again)
     *
     * @author Andriy Lesyuk
     * @return int The Token
     */
    public function GetToken()
    {
        return $this->_token;
    }

    /**
     * Get Original String
     *
     * @author Andriy Lesyuk
     * @return string The Original String
     */
    public function GetTokenString()
    {
        return $this->_subString;
    }

    /**
     * Get Start Offset
     *
     * @author Andriy Lesyuk
     * @return int The Start Offset
     */
    public function GetTokenStart()
    {
        return $this->_start;
    }

    /**
     * Get End Offset
     *
     * @author Andriy Lesyuk
     * @return int The End Offset
     */
    public function GetTokenEnd()
    {
        return $this->_end;
    }

    /**
     * Returns the Length Of Query (May differ if ";" was met)
     *
     * @author Andriy Lesyuk
     * @return bool There is Error
     */
    public function Length()
    {
        return $this->_length;
    }

    /**
     * Indicates If There Is an Error in Query
     *
     * @author Andriy Lesyuk
     * @return bool There is Error
     */
    public function IsErroneous()
    {
        return ($this->_error > 0);
    }

    /**
     * Check If Type Is Not Yet Known
     *
     * @author Andriy Lesyuk
     * @return bool The Type is Not Yet Known
     */
    public static function TypeIsUnknown($_type)
    {
        return ($_type == 0);
    }

    /**
     * Check If Type Is Space
     *
     * @author Andriy Lesyuk
     * @return bool The Type is Space
     */
    public static function TypeIsSpace($_type)
    {
        return ((($_type & SWIFT_KQL2::TYPE_SPACE) ^ $_type) == 0);
    }

    /**
     * Check if This is a Special Symbol
     *
     * @author Andriy Lesyuk
     * @return bool The Type is Special
     */
    public static function TypeIsSpecial($_type)
    {
        return ($_type == SWIFT_KQL2::TYPE_SPEC) || ($_type == SWIFT_KQL2::TYPE_SIGN);
    }

    /**
     * Check if Type Is for String
     *
     * @author Andriy Lesyuk
     * @return bool The String Can Contain Any Chars
     */
    public static function TypeIsString($_type)
    {
        return ($_type & SWIFT_KQL2::TYPE_OTHER) == SWIFT_KQL2::TYPE_OTHER;
    }

    /**
     * Check if Type Is for Operator
     *
     * @author Andriy Lesyuk
     * @return bool The String is Alpha-Numeric or a Symbol
     */
    public static function TypeIsOperator($_type)
    {
        return ((($_type & SWIFT_KQL2::TYPE_OPERATOR) ^ $_type) == 0) || ($_type == SWIFT_KQL2::TYPE_SPEC) || ($_type == SWIFT_KQL2::TYPE_SIGN);
    }

    /**
     * Check if Type is For Clause
     *
     * @author Andriy Lesyuk
     * @return bool The String is Alpha-Numeric
     */
    public static function TypeIsStringOperator($_type)
    {
        return ((($_type & SWIFT_KQL2::TYPE_OPERATOR) ^ $_type) == 0);
    }

    /**
     * Check If Type Is Fine for Field Name
     *
     * @author Andriy Lesyuk
     * @return bool The Type is for Field Name
     */
    public static function TypeIsFieldName($_type)
    {
        return ((($_type & SWIFT_KQL2::TYPE_FIELDNAME) ^ $_type) == 0) && ($_type != SWIFT_KQL2::TYPE_DOT);
    }

    /**
     * Check If Type Is Fine for Real Number (or Just Number)
     *
     * @author Andriy Lesyuk
     * @return bool The Type is for Number
     */
    public static function TypeIsRealNumber($_type)
    {
        return ((($_type & SWIFT_KQL2::TYPE_SIGNREALNUM) ^ $_type) == 0);
    }

    /**
     * Check if Type is for Error
     *
     * @author Andriy Lesyuk
     * @return bool Erroneous Char Found
     */
    public static function TypeIsError($_type)
    {
        return $_type == SWIFT_KQL2::TYPE_ERROR;
    }

}

?>
