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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

use Base\Library\KQL\SWIFT_KQLParser;
use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\KQL2\SWIFT_KQL2;

/**
 * The Report Renderer
 *
 * @author Varun Shoor
 */
abstract class SWIFT_ReportRender extends SWIFT_ReportBase
{
    protected $_renderedOutput = '';
    protected $_chartsOutput = array();

    // Variables related to rendering
    protected $_renderFieldTitleExtendedInfoContainer = array();

    // Charts data
    protected $_internalXContainer = array();
    protected $_internalYContainer = array();
    protected $_internalDataContainer = array();

    // Should be set to true to generate CSV (CSV uses charts arrays)
    protected $_internalDisplayValues = false;

    // Special chart notifications
    protected $_chartNotifications = array();

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
        parent::__construct($_SWIFT_KQL2Object, $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);
    }

    /**
     * Retrieve the Rendered Charts
     *
     * @author Andriy Lesyuk
     * @return string The Rendered Output
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetChartsOutput()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chartsOutput = '';

        if (count($this->_chartsOutput) > 0) {
            $_totalPercentage = 0;
            $_widthPercentage = ceil(100 / count($this->_chartsOutput));

            $_chartsOutput .= '<div class="reportschartscontainer">';
            foreach ($this->_chartsOutput as $_chartOutput) {
                $_chartsOutput .= '<div class="reportschartcontainer" style="width: ' . ((100 - $_totalPercentage < $_widthPercentage) ? 100 - $_totalPercentage : $_widthPercentage) . '%;">';
                $_chartsOutput .= $_chartOutput;
                $_chartsOutput .= '</div>';

                $_totalPercentage += $_widthPercentage;
            }
            $_chartsOutput .= '</div>';
        }

        return $_chartsOutput;
    }

    /**
     * Retrieve the rendered output
     *
     * @author Varun Shoor
     * @return string The Rendered Output
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOutput()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_renderedOutput;
    }

    /**
     * Retrieve CSV
     *
     * @author Andriy Lesyuk
     * @param string $_filePath
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchCSV($_filePath = 'php://output')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_output = fopen($_filePath, 'w');

        $_empty = array();

        if (isset($this->_internalYContainer[0])) {
            $count = count($this->_internalYContainer[0]);
            if ((count($this->_internalYContainer) > 0) && ($count > 0) &&
                (count($this->_internalXContainer[0]) < $count + count($this->_internalDataContainer[0]))) {

                for ($i = 0; $i < $count; $i++) {
                    $_empty[$i] = null;
                }
            }
        }

        foreach ($this->_internalXContainer as $_rowContainer) {
            fputcsv($_output, array_merge($_empty, $_rowContainer));
        }

        foreach ($this->_internalDataContainer as $_rowIndex => $_rowContainer) {
            if (count($this->_internalYContainer) > 0) {
                fputcsv($_output, array_merge($this->_internalYContainer[$_rowIndex], $_rowContainer));
            } else {
                fputcsv($_output, $_rowContainer);
            }
        }

        fclose($_output);

        return true;
    }

    /**
     * Retrieve HTML
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchHTML()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * Embed Header Image
         */

        $_headerImage = $this->Template->RetrieveHeaderImagePath(SWIFT_TemplateEngine::HEADERIMAGE_CONTROLPANEL);

        $_mimeType = kc_mime_content_type($_headerImage);
        $_embeddedImage = 'data:' . $_mimeType . ';base64,' . base64_encode(file_get_contents($_headerImage));

        /**
         * Render output
         */

        $_interfaceName = 'staff';

        // Spoof the template engine
        $this->Template->Assign('_defaultTitle', sprintf($this->Language->Get('exportdefaulttitle'), $this->Settings->Get('general_companyname'), SWIFT_PRODUCT, SWIFT_VERSION));
        $this->Template->Assign('_baseName', RemoveTrailingSlash(SWIFT::Get('swiftpath') . $_interfaceName . '/' . SWIFT_BASENAME));

        $this->Template->Assign('_reportContents', $this->GetOutput());
        $this->Template->Assign('_reportTitle', htmlspecialchars($this->Report->GetProperty('title')));
        $this->Template->Assign('_reportDate', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME));
        $this->Template->Assign('_headerImageCP', $_embeddedImage);

        $this->Template->Render('exportreport', SWIFT_TemplateEngine::TYPE_FILE, '__swift/themes/__cp/templates/exportreport.tpl');
    }

    /**
     * Render a Report
     *
     * @author Varun Shoor
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @param bool $_internalDisplayValues Should Display Values be Saved to Charts Array (for CSV Export)
     * @return SWIFT_ReportRender The Report Render Object
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Process(SWIFT_Report $_SWIFT_ReportObject, $_internalDisplayValues = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_KQLParser = new SWIFT_KQLParser();

        $_SWIFT_KQLParserResultObject = $_SWIFT_KQLParser->ParseStatement($_SWIFT_ReportObject->GetProperty('kql'), $_SWIFT_ReportObject->GetProperty('basetablenametext'));

        $_SWIFT_ReportRenderObject = false;

        $_reportType = $_SWIFT_KQLParserResultObject->GetResultType();
        switch ($_reportType) {
            case SWIFT_KQLParserResult::RESULTTYPE_TABULAR:
                $_SWIFT_ReportRenderObject = new SWIFT_ReportRenderTabular($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);

                break;

            case SWIFT_KQLParserResult::RESULTTYPE_GROUPEDTABULAR:
                $_SWIFT_ReportRenderObject = new SWIFT_ReportRenderGroupedTabular($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);

                break;

            case SWIFT_KQLParserResult::RESULTTYPE_SUMMARY:
                $_SWIFT_ReportRenderObject = new SWIFT_ReportRenderSummary($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);

                break;


            case SWIFT_KQLParserResult::RESULTTYPE_MATRIX:
                $_SWIFT_ReportRenderObject = new SWIFT_ReportRenderMatrix($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);

                break;

            default:
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                break;
        }

        $_aliasMap = $_SWIFT_KQLParser->GetAliasMap();
        $_functionMap = $_SWIFT_KQLParser->GetFunctionMap();
        $_originalAliases = $_SWIFT_KQLParser->GetOriginalAliasMap();
        $_hiddenFields = $_SWIFT_KQLParser->GetHiddenFields();
        $_customFields = $_SWIFT_KQLParser->GetCustomFields();

        $_SWIFT_ReportRenderObject->SetAliasMap($_aliasMap);
        $_SWIFT_ReportRenderObject->SetFunctionMap($_functionMap);
        $_SWIFT_ReportRenderObject->SetOriginalAliasMap($_originalAliases);
        $_SWIFT_ReportRenderObject->SetHiddenFields($_hiddenFields);
        $_SWIFT_ReportRenderObject->SetCustomFields($_customFields);

        $_SWIFT_ReportRenderObject->SetDisplayValues($_internalDisplayValues);

        $_SWIFT_ReportRenderObject->SetSessionTimeZone();

        $_SWIFT_ReportRenderObject->Render();

        $_SWIFT_ReportRenderObject->RestoreSessionTimeZone();

        return $_SWIFT_ReportRenderObject;
    }

    /**
     * Specifies if Values Should be Converted Before Putting Them in Charts Arrays
     *
     * @author Andriy Lesyuk
     * @param bool $_internalDisplayValues Should Display Values be Saved to Charts Array (for CSV Export)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetDisplayValues($_internalDisplayValues = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_internalDisplayValues = $_internalDisplayValues;

        return true;
    }

    /**
     * Retrieve Chart Object
     *
     * @author Andriy Lesyuk
     * @return array|bool The Chart Objects Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCharts()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$this->Report->GetProperty('chartsenabled')) {
            return false;
        }

        // Chart arrays contain display values - not for charts
        if ($this->_internalDisplayValues) {
            return false;
        }

        /**
         * ---------------------------------------------
         * Retrieve Column Descriptions
         * ---------------------------------------------
         */

        $_groupByFields = array();
        foreach ($this->_sqlGroupByXFields as $_groupByXField) { // KQLParserResult->GetGroupByXFields
            if (isset($this->_sqlParsedTitles[$_groupByXField[1]]) && ($this->_sqlParsedTitles[$_groupByXField[1]][1] !== false)) {
                $_groupByFields[$_groupByXField[1]] = array($this->_sqlParsedTitles[$_groupByXField[1]][1],     // original type
                                                            SWIFT_ReportChart::AXIS_X,                         // original axis
                                                            $this->_sqlParsedTitles[$_groupByXField[1]][0],     // name
                                                            $_groupByXField[1],                                 // id
                                                            false,                                             // function used
                                                            false);                                             // DB field id
            }
        }
        foreach ($this->_sqlGroupByFields as $_groupByYField) { // KQLParserResult->GetGroupByFields
            if (isset($this->_sqlParsedTitles[$_groupByYField[1]]) && ($this->_sqlParsedTitles[$_groupByYField[1]][1] !== false)) {
                $_groupByFields[$_groupByYField[1]] = array($this->_sqlParsedTitles[$_groupByYField[1]][1],     // original type
                                                            SWIFT_ReportChart::AXIS_Y,                         // original axis
                                                            $this->_sqlParsedTitles[$_groupByYField[1]][0],     // name
                                                            $_groupByYField[1],                                 // id
                                                            false,                                             // function used
                                                            false);                                             // DB field id
            }
        }

        $_dataFields = array();
        foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {
            if (isset($_groupByFields[$_titleName])) {
                continue;
            }
            if (isset($this->_hiddenFields[$_titleName])) {
                continue;
            }

            $_functionName = false;
            $_fieldName = false;

            if (isset($this->_fieldsToFunctionsMap[$_titleName])) {
                $_functionName = $this->_fieldsToFunctionsMap[$_titleName][0];
                $_fieldName = $this->_fieldsToFunctionsMap[$_titleName][1];

                switch ($_functionName)
                {
                    case 'COUNT':
                        $_dataFields[$_titleName] = array(SWIFT_KQLSchema::FIELDTYPE_INT, // original type
                                                          false,                          // original axis
                                                          $_titleName,                      // name
                                                          $_titleName,                      // id
                                                          $_functionName,                  // function used
                                                          $_fieldName);                      // DB field id
                        break;

                    default:
                        break;
                }
            }

            if (!isset($_dataFields[$_titleName])) {
                // <table>.<field>
                if ($this->_sqlParsedTitles[$_titleName][1] !== false) {
                    $_dataFields[$_titleName] = array($this->_sqlParsedTitles[$_titleName][1],    // original type
                                                      false,                                    // original axis
                                                      $this->_sqlParsedTitles[$_titleName][0],    // name
                                                      $_titleName,                                // id
                                                      $_functionName,                            // function used
                                                      $_fieldName);                                // DB field id

                // <expression> AS <alias>
                } elseif (isset($this->_aliasesToFieldsMap[$_titleName])) {

                    // ((SUM(IF(<table>.<field>, 1, 0))/COUNT(*))*100)
                    if (preg_match('/^\( *\( *SUM\( *IF\([^\(\)]*\) *\) *\/ *COUNT\(\*\) *\) *\* *100 *\)$/i', $this->_aliasesToFieldsMap[$_titleName], $_matches)) {
                        $_dataFields[$_titleName] = array(SWIFT_KQLSchema::FIELDTYPE_FLOAT,    // original type
                                                          false,                            // original axis
                                                          $_titleName,                        // name
                                                          $_titleName,                        // id
                                                          'PERCENT',                        // function used
                                                          false);                            // DB field id

                    // <table>.<field> AS <alias>
                    } elseif (strpos($this->_aliasesToFieldsMap[$_titleName], '.')) {
                        $_keyChunks = explode('.', strtolower($this->_aliasesToFieldsMap[$_titleName]));

                        if ((count($_keyChunks) == 2) &&
                            isset($this->_schemaContainer[$_keyChunks[0]]) &&
                            isset($this->_schemaContainer[$_keyChunks[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[1]])) {

                            if (isset($this->_originalAliasesMap[$_titleName])) {
                                $_aliasName = $this->_originalAliasesMap[$_titleName];
                            } else {
                                $_aliasName = $_titleName;
                            }

                            $_fieldContainer = $this->_schemaContainer[$_keyChunks[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_keyChunks[1]];
                            $_dataFields[$_titleName] = array($_fieldContainer[SWIFT_KQLSchema::FIELD_TYPE],// original type
                                                              false,                                        // original axis
                                                              $_aliasName,                                    // name
                                                              $_titleName,                                    // id
                                                              $_functionName,                                // function used
                                                              $_fieldName);                                    // DB field id
                        }
                    }
                }
            }
        }

        // Remove extra rows
        if ($this->_extraCount > 0) {
            $_internalYContainer = array_slice($this->_internalYContainer, 0, $this->_rowCount);
            $_internalDataContainer = array_slice($this->_internalDataContainer, 0, $this->_rowCount);
        } else {
            $_internalYContainer = $this->_internalYContainer;
            $_internalDataContainer = $this->_internalDataContainer;
        }

        // If Y titles are missing move the first column there (for tabular reports)
        if ((count($_internalYContainer) == 0) && (count($_dataFields) > 0)) {
            $_columnNames = array_keys($_dataFields);

            if (($_dataFields[$_columnNames[0]][SWIFT_ReportChart::ORIGINAL_TYPE] == SWIFT_KQLSchema::FIELDTYPE_STRING) ||
                ($_dataFields[$_columnNames[0]][SWIFT_ReportChart::ORIGINAL_TYPE] == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME)) {
                $_groupByFields[$_columnNames[0]] = $_dataFields[$_columnNames[0]];
                $_groupByFields[$_columnNames[0]][SWIFT_ReportChart::ORIGINAL_AXIS] = SWIFT_ReportChart::AXIS_Y;
                unset($_dataFields[$_columnNames[0]]);

                foreach ($this->_internalXContainer as &$_xContainer) {
                    array_shift($_xContainer);
                }
                unset($_xContainer);

                foreach ($_internalDataContainer as &$_dataContainer) {
                    $_titleContainer = array();
                    $_titleContainer[] = array_shift($_dataContainer);
                    $_internalYContainer[] = $_titleContainer;
                }
                unset($_dataContainer);
            }
        }

        if (count($_dataFields) == 0) {
            return false;
        }

        $_chartResults = SWIFT_ReportChart::Process($this->KQL,
                                                    $this->KQLParserResult,
                                                    $_groupByFields,
                                                    $_dataFields,
                                                    $this->_internalXContainer,
                                                    $_internalYContainer,
                                                    $_internalDataContainer);

        $_charts = array();

        if ($_chartResults) {
            foreach ($_chartResults as $_chart) {
                if (is_string($_chart)) {
                    $this->_chartNotifications[] = $_chart;
                } else {
                    $_charts[] = $_chart;
                }
            }
        }

        return $_charts;
    }

    /**
     * Get Chart Notifications
     *
     * @author Andriy Lesyuk
     * @return array The Chart Notifications
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetChartNotifications()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_chartNotifications;
    }

    /**
     * Add A Chart to Output
     *
     * @author Andriy Lesyuk
     * @param SWIFT_ReportChartBase $_chartObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function AddChart(SWIFT_ReportChartBase $_chartObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chartObject->Render();

        $this->_chartsOutput[] = $_chartObject->GetOutput();

        return true;
    }

    /**
     * Clear Chart Data
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ResetCharts()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_internalXContainer = array();
        $this->_internalYContainer = array();
        $this->_internalDataContainer = array();
        $this->_chartsOutput = array();

        return true;
    }

    /**
     * Render the Report
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Render()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Append to Output
     *
     * @author Varun Shoor
     * @param string $_data
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function AppendOutput($_data)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_renderedOutput .= $_data . SWIFT_CRLF;

        return true;
    }

    /**
     * Retrieve the parsed column value
     *
     * @author Varun Shoor
     * @param string $_columnName
     * @param string $_columnValue
     * @param bool $_html
     * @return mixed The Parsed Column Value
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetParsedColumnValue($_columnName, $_columnValue, $_html = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_sqlParsedTitles[$_columnName]) || $this->_sqlParsedTitles[$_columnName][1] === false) {
            return ($_html ? self::ProcessColumnValue($_columnValue) : $_columnValue);

        // If the value isnt false for the function call, then return data as is
        } else if ($this->_sqlParsedTitles[$_columnName][3] !== false) {
            return ($_html ? self::ProcessColumnValue($_columnValue) : $_columnValue);
        }

        $_fieldType = $this->_sqlParsedTitles[$_columnName][1];

        switch ($_fieldType) {
            case SWIFT_KQLSchema::FIELDTYPE_BOOL:
                return ($_columnValue == '1' ? $this->Language->Get('yes') : $this->Language->Get('no'));

                break;

            case SWIFT_KQLSchema::FIELDTYPE_FLOAT:
                return floatval($_columnValue);

                break;

            case SWIFT_KQLSchema::FIELDTYPE_INT:

                /**
                 * BUG FIX - Andriy Lesyuk
                 *
                 * SWIFT-2089 "Rating Scores" report does not return the values in decimal
                 *
                 */
                if (isset($this->_fieldsToFunctionsMap[$_columnName]) && ($this->_fieldsToFunctionsMap[$_columnName][0] == 'AVG')) {
                    return floatval($_columnValue);
                } else {
                    return (int) ($_columnValue);
                }

                break;

            case SWIFT_KQLSchema::FIELDTYPE_SECONDS:
                return ($_html ? self::ProcessColumnValue(SWIFT_Date::ColorTime($_columnValue, false, true)) : (int) ($_columnValue));

                break;

            case SWIFT_KQLSchema::FIELDTYPE_UNIXTIME:
                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-2119 If no SLA plan is created in help desk and default Overdue hours are also disabled, "Resolved date" under Reports is shown as 1 January 1970
                 *
                 */
                if (empty($_columnValue)) {
                    return $this->Language->Get('na');
                }

                $_date = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_columnValue);

                return ($_html ? self::ProcessColumnValue($_date) : $_date);

                break;

            case SWIFT_KQLSchema::FIELDTYPE_STRING:
                return ($_html ? nl2br(self::ProcessColumnValue($_columnValue)) : $_columnValue);

                break;

            case SWIFT_KQLSchema::FIELDTYPE_CUSTOM:
                if (isset($this->_sqlParsedTitles[$_columnName][2][SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_columnValue])) {
                    $_value = $this->Language->Get($this->_sqlParsedTitles[$_columnName][2][SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_columnValue]);

                    return ($_html ? self::ProcessColumnValue($_value) : $_value);
                }

                break;

            default:
                return ($_html ? self::ProcessColumnValue($_columnValue) : $_columnValue);

                break;
        }

        return ($_html ? self::ProcessColumnValue($_columnValue) : $_columnValue);
    }

    /**
     * Render the Column Value Taking into Account Column Type
     *
     * @author Andriy Lesyuk
     * @param string $_columnName
     * @param string $_columnValue
     * @param int|false $_rowIndex
     * @param array $_rowResults
     * @return mixed The Column Value
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderColumnValue($_columnName, $_columnValue, $_rowIndex = false, $_rowResults = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_columnExpression = $this->KQLObject->Compiler->GetExpressionByColumnName($_columnName, $_rowIndex);

        // Return as it is if FORMAT is @
        if (is_array($_columnExpression) && isset($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
            isset($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['FORMAT']) &&
            ($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['FORMAT'] == '@')) {
            return $_columnValue;
        }

        // Convert value to proper type
        $_convertedValue = $this->ConvertColumnValue($_columnName, $_columnValue, $_rowIndex);

        // Use converted value by default
        $_displayValue = $_convertedValue;

        if (strpos($this->_aliasesToFieldsMap[$_columnName], 'customfield') === 0) {
            //if it is custom field, then escape value
            $_displayValue = htmlspecialchars($_convertedValue);
        }

        // Process the value
        if (is_array($_columnExpression) && isset($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
            switch ($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])
            {
                case SWIFT_KQL2::DATA_BOOLEAN:
                    if ($_convertedValue) {
                        $_displayValue = $this->Language->Get('yes');
                    } else {
                        $_displayValue = $this->Language->Get('no');
                    }
                    break;

                case SWIFT_KQL2::DATA_INTEGER:
                    if (isset($_columnExpression[SWIFT_KQL2::EXPRESSION_DATA][0]) && $_columnExpression[SWIFT_KQL2::EXPRESSION_DATA][0] == 'AVG') {
                        $_displayValue = sprintf("%.02f", $_convertedValue);
                    }
                    break;

                case SWIFT_KQL2::DATA_FLOAT:
                    if (is_numeric($_convertedValue)) {
                        $_displayValue = sprintf("%.02f", $_convertedValue);
                    }
                    break;

                case SWIFT_KQL2::DATA_SECONDS:
                    if (is_numeric($_convertedValue)) {
                        $_displayValue = self::ProcessColumnValue(SWIFT_Date::ColorTime($_convertedValue, false, true));
                    }
                    break;

                case SWIFT_KQL2::DATA_TIME:
                    if (is_numeric($_convertedValue)) {
                        $_displayValue = sprintf("%d:%02d:%02d", floor($_convertedValue / 3600), floor(($_convertedValue % 3600) / 60), $_convertedValue % 60);
                    }
                    break;

                case SWIFT_KQL2::DATA_DATE:
                    if (is_int($_convertedValue) && ($_convertedValue > 0)) {
                        $_displayValue = self::ProcessColumnValue(SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, $_convertedValue));
                    } else {
                        $_displayValue = $this->Language->Get('na');
                    }
                    break;

                case SWIFT_KQL2::DATA_UNIXDATE:
                case SWIFT_KQL2::DATA_DATETIME:
                    if (is_numeric($_convertedValue) && ($_convertedValue > 0)) {
                        $_displayValue = self::ProcessColumnValue(SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_convertedValue));
                    } else {
                        $_displayValue = $this->Language->Get('na');
                    }
                    break;

                case SWIFT_KQL2::DATA_STRING:
                    if (!is_null($_convertedValue)) {
                        if ($this->GetFormat() == self::EXPORT_CSV) {
                            $_displayValue = self::ProcessColumnValue($_convertedValue);
                        } else {
                            $_displayValue = nl2br(self::ProcessColumnValue($_convertedValue));
                        }
                    }
                    break;
            }
        }

        // Execute field writers
        if (is_array($_columnExpression) && $_columnExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
            $_fieldName = $_columnExpression[SWIFT_KQL2::EXPRESSION_DATA];

            if (isset($this->_schemaContainer[$_fieldName[0]]) &&
                isset($this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]])) {
                $_fieldProperties = $this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]];

                if (($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_OPTION) &&
                    isset($_fieldProperties[SWIFT_KQLSchema::FIELD_CUSTOMVALUES])) {
                    if (isset($_fieldProperties[SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_convertedValue])) {
                        $_displayValue = $this->Language->Get($_fieldProperties[SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_convertedValue]);
                    } else {
                        $_displayValue = null;
                    }
                }

                if (isset($_fieldProperties[SWIFT_KQLSchema::FIELD_WRITER])) {
                    $_method = $_fieldProperties[SWIFT_KQLSchema::FIELD_WRITER];
                    $_displayValue = $this->$_method($_displayValue, $_columnName, $_rowResults); // TODO: array(class, method)
                }
            }
        }

        return $_displayValue;
    }

    /**
     * Export the Column Value (for CSV) Taking into Account Column Type
     *
     * @author Andriy Lesyuk
     * @param string $_columnName
     * @param string $_columnValue
     * @param int|false $_rowIndex
     * @param array $_rowResults
     * @return mixed The Column Value
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ExportColumnValue($_columnName, $_columnValue, $_rowIndex = false, $_rowResults = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_columnExpression = $this->KQLObject->Compiler->GetExpressionByColumnName($_columnName, $_rowIndex);

        // Return as it is if FORMAT is @
        if (isset($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
            isset($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['FORMAT']) &&
            ($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['FORMAT'] == '@')) {
            return $_columnValue;
        }

        // Convert value to proper type
        $_convertedValue = $this->ConvertColumnValue($_columnName, $_columnValue, $_rowIndex);

        // Use converted value by default
        $_displayValue = $_convertedValue;

        // Process the value
        if (isset($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
            switch ($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])
            {
                case SWIFT_KQL2::DATA_BOOLEAN:
                    if ($_convertedValue) {
                        $_displayValue = $this->Language->Get('yes');
                    } else {
                        $_displayValue = $this->Language->Get('no');
                    }
                    break;

                case SWIFT_KQL2::DATA_FLOAT:
                    if (is_numeric($_convertedValue)) {
                        $_displayValue = sprintf("%.02f", $_convertedValue);
                    }
                    break;

                case SWIFT_KQL2::DATA_TIME:
                    if (is_numeric($_convertedValue)) {
                        $_displayValue = sprintf("%d:%02d:%02d", floor($_convertedValue / 3600), floor(($_convertedValue % 3600) / 60), $_convertedValue % 60);
                    }
                    break;

                case SWIFT_KQL2::DATA_DATE:
                    if (is_int($_convertedValue) && ($_convertedValue > 0)) {
                        $_displayValue = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, $_convertedValue);
                    } else {
                        $_displayValue = $this->Language->Get('na');
                    }
                    break;

                case SWIFT_KQL2::DATA_UNIXDATE:
                case SWIFT_KQL2::DATA_DATETIME:
                    if (is_numeric($_convertedValue) && ($_convertedValue > 0)) {
                        $_displayValue = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_convertedValue);
                    } else {
                        $_displayValue = $this->Language->Get('na');
                    }
                    break;
            }
        }

        // Execute field writers
        if ($_columnExpression[SWIFT_KQL2::EXPRESSION_TYPE] == SWIFT_KQL2::ELEMENT_FIELD) {
            $_fieldName = $_columnExpression[SWIFT_KQL2::EXPRESSION_DATA];

            if (isset($this->_schemaContainer[$_fieldName[0]]) &&
                isset($this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]])) {
                $_fieldProperties = $this->_schemaContainer[$_fieldName[0]][SWIFT_KQLSchema::SCHEMA_FIELDS][$_fieldName[1]];

                if (($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE] == SWIFT_KQL2::DATA_OPTION) &&
                    isset($_fieldProperties[SWIFT_KQLSchema::FIELD_CUSTOMVALUES])) {
                    if (isset($_fieldProperties[SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_convertedValue])) {
                        $_displayValue = $this->Language->Get($_fieldProperties[SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_convertedValue]);
                    } else {
                        $_displayValue = null;
                    }
                }

                if (isset($_fieldProperties[SWIFT_KQLSchema::FIELD_WRITER])) {
                    $_method = $_fieldProperties[SWIFT_KQLSchema::FIELD_WRITER];
                    $_displayValue = $this->$_method($_displayValue, $_columnName, $_rowResults); // TODO: array(class, method)
                }
            }
        }

        return $_displayValue;
    }

    /**
     * Process the column value
     *
     * @author Varun Shoor
     * @param mixed $_columnValue
     * @return mixed
     */
    public static function ProcessColumnValue($_columnValue)
    {
        $_columnValue = preg_replace('#<br\s*/?>#i', "\n", $_columnValue);
        return htmlspecialchars(strip_tags($_columnValue));
    }

    /**
     * Renders the field title column
     *
     * @author Varun Shoor
     * @param string $_fieldReferenceName
     * @param string $_fieldTitle
     * @param array $_fieldContainer The KQL Schema Field Reference Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderTitleColumn($_fieldReferenceName, $_fieldTitle, $_fieldContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_extendedInfo = $_columnInfo = '';

        if (isset($_fieldContainer[SWIFT_KQLSchema::FIELD_WIDTH])) {
            $_columnInfo .= ' width="' . $_fieldContainer[SWIFT_KQLSchema::FIELD_WIDTH] . '"';
        }

        if (isset($_fieldContainer[SWIFT_KQLSchema::FIELD_ALIGN])) {
            $_extendedInfo .= ' align="' . $_fieldContainer[SWIFT_KQLSchema::FIELD_ALIGN] . '"';
        }

        $this->_renderFieldTitleExtendedInfoContainer[$_fieldReferenceName] = $_extendedInfo;

        $this->AppendOutput('<td' . $_columnInfo . $_extendedInfo . ' nowrap="nowrap" class="reporttdtitle">' . htmlspecialchars($_fieldTitle) . '</td>');

        return true;
    }

    /**
     * Get a List of Supported Formats
     *
     * @author Andriy Lesyuk
     * @return array The List of Supported Export Formats
     */
    public function GetExportFormatMap()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_exportFormatMap;
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

        if ($this->_internalDisplayValues) {
            return self::EXPORT_CSV;
        }
        return self::EXPORT_HTML;
    }

}
