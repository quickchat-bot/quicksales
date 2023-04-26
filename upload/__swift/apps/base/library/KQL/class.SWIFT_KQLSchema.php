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

use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use SWIFT_Library;

/**
 * The Base KQL Schema Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_KQLSchema extends SWIFT_Library
{
    static protected $_localeContainer = array();
    static protected $_invertedLocaleContainer = array();

    // Core Constants
    const SCHEMA_TABLELABEL = 1;
    const SCHEMA_RELATEDTABLES = 2;
    const SCHEMA_FIELDS = 3;
    const SCHEMA_ISVISIBLE = 4;
    const SCHEMA_PRIMARYKEY = 5;
    const SCHEMA_AUTOJOIN = 6;
    const SCHEMA_TABLEALIAS = 7;
    const SCHEMA_POSTCOMPILER = 8;

    const FIELD_TYPE = 1;
    const FIELD_RELATEDTO = 2;
    const FIELD_LINKEDTO = 3; // array(linkedtofield, labelfield, customwhereclause)
    const FIELD_WIDTH = 4;
    const FIELD_CUSTOMVALUES = 5;
    const FIELD_ALIGN = 6;
    const FIELD_AUXILIARY = 7;
    const FIELD_PROCESSOR = 8;
    const FIELD_WRITER = 9;

    const FIELDTYPE_INT = 1;
    const FIELDTYPE_FLOAT = 2;
    const FIELDTYPE_STRING = 3;
    const FIELDTYPE_BOOL = 4;
    const FIELDTYPE_UNIXTIME = 5;
    const FIELDTYPE_SECONDS = 6;
    const FIELDTYPE_LINKED = 7;
    const FIELDTYPE_CUSTOM = 8;

    // Clause constants
    const CLAUSE_MULTIPLE = 0;
    const CLAUSE_PARSER = 1;
    const CLAUSE_PRECOMPILER = 2;
    const CLAUSE_COMPILER = 3;
//    const CLAUSE_PROCESSOR = 4;
//    const CLAUSE_BEFORERENDERER = 5;
//    const CLAUSE_AFTERRENDERER = 6;

    // Operator constants
    const OPERATOR_NEGATIVE = 0;
    const OPERATOR_LEFTTYPE = 1;
    const OPERATOR_RIGHTTYPE = 2;
    const OPERATOR_RETURNTYPE = 3;
    const OPERATOR_EXPANDER = 4;
    const OPERATOR_COMPILER = 5;

    // Function constants
    const FUNCTION_ARGUMENTS = 0;
    const FUNCTION_OPTIONALARGUMENTS = 1;
//    const FUNCTION_AUTOCOMPLETE = 2;
    const FUNCTION_RETURNTYPE = 3;
    const FUNCTION_PARSER = 4;
    const FUNCTION_POSTPARSER = 5;
    const FUNCTION_EXPANDER = 6;
    const FUNCTION_PRECOMPILER = 7;
    const FUNCTION_COMPILER = 8;

    // Selector constants
    const SELECTOR_FUNCTION = 0; // aliased function name
    const SELECTOR_ARGUMENT = 1;
    const SELECTOR_RETURNTYPE = 2;
    const SELECTOR_COMPILER = 3;

    // Pre-modifier constants
    const PREMODIFIER_PARSER = 0;
    const PREMODIFIER_COMPILER = 1;

    // Post-modifier constants
    const POSTMODIFIER_CLAUSE = 0;
    const POSTMODIFIER_PARSER = 1;
    const POSTMODIFIER_COMPILER = 2;
//    const POSTMODIFIER_WRITER = 3;

    // Identifier constants
    const IDENTIFIER_TYPE = 0;
    const IDENTIFIER_VALUE = 1;
    const IDENTIFIER_PARSER = 2;

//    const VARIABLE_EXPANDER = 0;
//    const VARIABLE_PRECOMPILER = 1;
    const VARIABLE_COMPILER = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->LoadKQLLabels('kql');
    }

    /**
     * Retrieve the Schema
     *
     * @author Varun Shoor
     * @return array The Schema Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSchema()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_schemaContainer = array();

        return $_schemaContainer;
    }

    /**
     * Retrieve custom clauses
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

        $_clausesContainer = array();

        return $_clausesContainer;
    }

    /**
     * Retrieve custom operators
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

        $_operatorsContainer = array();

        return $_operatorsContainer;
    }

    /**
     * Retrieve custom functions
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

        $_functionsContainer = array();

        return $_functionsContainer;
    }

    /**
     * Retrieve custom selectors
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

        $_selectorsContainer = array();

        return $_selectorsContainer;
    }

    /**
     * Retrieve custom pre-modifiers
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

        $_preModifiersContainer = array();

        return $_preModifiersContainer;
    }

    /**
     * Retrieve custom post-modifiers
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

        $_postModifiersContainer = array();

        return $_postModifiersContainer;
    }

    /**
     * Retrieve custom identifiers
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

        $_identifiersContainer = array();

        return $_identifiersContainer;
    }

    /**
     * Retrieve custom variables
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

        $_variablesContainer = array();

        return $_variablesContainer;
    }

    /**
     * Get schema objects for all apps
     *
     * @author Andriy Lesyuk
     * @return array The Objects Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetSchemaObjects()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_installedAppList = SWIFT_App::GetInstalledApps();

        $_schemaObjects = array();

        foreach ($_installedAppList as $_appName) {
            $_SWIFT_KQLSchemaObject = SWIFT_App::RetrieveKQLSchemaObject($_appName);
            if (!$_SWIFT_KQLSchemaObject instanceof SWIFT_KQLSchema || !$_SWIFT_KQLSchemaObject->GetIsClassLoaded()) {
                continue;
            }

            $_schemaObjects[] = $_SWIFT_KQLSchemaObject;
        }

        return $_schemaObjects;
    }

    /**
     * Get the combined Schema from all apps
     *
     * @author Varun Shoor
     * @return array The Schema Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetCombinedSchema()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_installedAppList = SWIFT_App::GetInstalledApps();

        $_schemaContainer = array();

        foreach ($_installedAppList as $_appName) {
            $_SWIFT_KQLSchemaObject = false;
            try {
                $_SWIFT_KQLSchemaObject = SWIFT_App::RetrieveKQLSchemaObject($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_KQLSchemaObject instanceof SWIFT_KQLSchema || !$_SWIFT_KQLSchemaObject->GetIsClassLoaded()) {
                continue;
            }

            $_schemaContainer = array_merge($_schemaContainer, $_SWIFT_KQLSchemaObject->GetSchema());
        }

        return $_schemaContainer;
    }

    /**
     * Load the KQL Labels
     *
     * @author Varun Shoor
     * @param string $_localeName
     * @param string $_appName (OPTIONAL)
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function LoadKQLLabels($_localeName, $_appName = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_localeName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4287 Schedule reports are not working.
         *
         * Comments: Setting default locale engine to files.
         */
        $_localeContainer = false;
        if (!empty($_appName)) {
            $_localeContainer = $this->Language->LoadApp($_localeName, $_appName, SWIFT_LanguageEngine::TYPE_FILE);
        } else {
            $_localeContainer = $this->Language->Load($_localeName, SWIFT_LanguageEngine::TYPE_FILE);
        }

        if (!$_localeContainer) {
            return false;
        }

        if (_is_array($_localeContainer)) {
            self::$_localeContainer = array_merge(self::$_localeContainer, $_localeContainer);

        }

        self::$_invertedLocaleContainer = array_flip(self::$_localeContainer);

        return true;
    }

    /**
     * Get the Label Value
     *
     * @author Varun Shoor
     * @param string $_labelKey
     * @return string
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetLabel($_labelKey)
    {
        if (isset(self::$_localeContainer[$_labelKey])) {
            return self::$_localeContainer[$_labelKey];
        }

        return '';
    }

    /**
     * Retrieve the actual field name on label
     *
     * @author Varun Shoor
     * @param string $_tableName
     * @param string $_labelName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetActualFieldNameOnLabel($_tableName, $_labelName)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_tableName) || empty($_labelName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_invertedLocaleContainer[$_labelName])) {

        }

        return true;
    }

    /**
     * Check if Type is Acceptable (Uses Argument Type Definition)
     *
     * @author Andriy Lesyuk
     * @param int $_dataType
     * @param int|array $_allowedType
     * @return bool "true" if equal, "false" otherwise
     */
    public static function ArgumentTypeEqual($_dataType, $_allowedType)
    {
        if (is_array($_allowedType)) {
            return in_array($_dataType, $_allowedType);
        } elseif ($_allowedType !== false) {
            return ($_dataType == $_allowedType);
        }

        return false;
    }

}

?>
