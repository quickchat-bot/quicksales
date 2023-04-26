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

use Base\Library\KQL\SWIFT_KQL;
use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL\SWIFT_KQLSchema;

/**
 * The Report Chart Factory
 *
 * @author Andriy Lesyuk
 */
abstract class SWIFT_ReportChart extends SWIFT_Library
{
    const CHART_COUNT = 3;

    const AXIS_X = 'X';
    const AXIS_Y = 'Y';

    /**
     * Describe properties of field containers passed to Process() in $_groupByFields and $_dataFields arrays
     */
    const ORIGINAL_TYPE  = 0; // type of field
    const ORIGINAL_AXIS  = 1; // axis (X, Y or false)
    const ORIGINAL_NAME  = 2; // field name
    const ORIGINAL_ID    = 3; // field id
    const ORIGINAL_FUNC  = 4; // function name used in SQL
    const ORIGINAL_FIELD = 5; // field database id

    /**
     * Describe properties of axis containers (which store fields) used in chart classes
     */
    const AXIS_AXIS   = 0; // axis (X, Y or false)
    const AXIS_TYPE   = 1; // field type
    const AXIS_NAME   = 2; // field name
    const AXIS_ID     = 3; // field id
    const AXIS_FIELDS = 4; // fields array (see ORIGINAL_*)
    const AXIS_INDEX  = 4; // or index of the field to use on chart
    const AXIS_TITLE  = 5; // whether X rows contain titles/column names

    /**
     * Create Report Chart Objects
     *
     * @author Andriy Lesyuk
     * @param SWIFT_KQL $_SWIFT_KQL
     * @param SWIFT_KQLParserResult $_SWIFT_KQLParserResultObject
     * @param array $_groupByFields List of Group-By Fields
     * @param array $_dataFields List of Data Fields
     * @param array $_xDataContainer X Axis Data (Table Titles)
     * @param array $_yDataContainer Y Axis Data (First Title Columns)
     * @param array $_dataContainer Table Data
     * @return array|bool SWIFT_ReportChartBase Objects
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Process(SWIFT_KQL $_SWIFT_KQL, SWIFT_KQLParserResult $_SWIFT_KQLParserResultObject, &$_groupByFields, &$_dataFields, &$_xDataContainer, &$_yDataContainer, &$_dataContainer)
    {
        $_chartsArray = array();
        $_chartNotifications = array();

        $_valueFields = array();

        foreach ($_dataFields as $_fieldName => $_fieldContainer) {
            // Skip IDs
            if (($_fieldContainer[self::ORIGINAL_TYPE] == SWIFT_KQLSchema::FIELDTYPE_INT) &&
                (preg_match('/id$/', $_fieldContainer[self::ORIGINAL_ID]))) {
                continue;
            }

            if (($_fieldContainer[self::ORIGINAL_TYPE] == SWIFT_KQLSchema::FIELDTYPE_INT) ||
                ($_fieldContainer[self::ORIGINAL_TYPE] == SWIFT_KQLSchema::FIELDTYPE_FLOAT) ||
                ($_fieldContainer[self::ORIGINAL_TYPE] == SWIFT_KQLSchema::FIELDTYPE_SECONDS)) {
                $_valueFields[] = $_fieldName;
            }
        }

        // No data to display
        if (!count($_valueFields)) {
            return false;
        }

        // Get columns and rows count
        $_rowsCount = count($_dataContainer);
        $_colsCount = (count($_dataContainer) > 0) ? count($_dataContainer[0]) : 0;

        // Skip single value ([ [ X ] ])
        if (($_rowsCount < 2) && ($_colsCount < 2)) {
            return false;
        }

        // Get initial types
        $_groupXType = self::GetDataType(self::AXIS_X, $_groupByFields);
        $_groupYType = self::GetDataType(self::AXIS_Y, $_groupByFields);

        $_axisXContainer = false;
        $_axisYContainer = false;
        $_multiSeriesContainer = false;

        // Select data for X axis (plus series)
        if ($_groupXType == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME) {
            $_axisXContainer = self::GetAxisContainer(self::AXIS_X, $_groupByFields, $_groupXType);
            $_multiSeriesContainer = self::GetAxisContainer(self::AXIS_Y, $_groupByFields);
        } elseif ($_groupYType == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME) {
            $_axisXContainer = self::GetAxisContainer(self::AXIS_Y, $_groupByFields, $_groupYType);
            $_multiSeriesContainer = self::GetAxisContainer(self::AXIS_X, $_groupByFields);
        } elseif ($_groupXType == SWIFT_KQLSchema::FIELDTYPE_STRING) {
            if (($_groupYType == SWIFT_KQLSchema::FIELDTYPE_STRING) && ($_rowsCount > $_colsCount)) {
                $_axisXContainer = self::GetAxisContainer(self::AXIS_Y, $_groupByFields, $_groupYType);
                $_multiSeriesContainer = self::GetAxisContainer(self::AXIS_X, $_groupByFields);
            } else {
                $_axisXContainer = self::GetAxisContainer(self::AXIS_X, $_groupByFields, $_groupXType);
                $_multiSeriesContainer = self::GetAxisContainer(self::AXIS_Y, $_groupByFields);
            }
        } elseif ($_groupYType == SWIFT_KQLSchema::FIELDTYPE_STRING) {
            $_axisXContainer = self::GetAxisContainer(self::AXIS_Y, $_groupByFields, $_groupYType);
            $_multiSeriesContainer = self::GetAxisContainer(self::AXIS_X, $_groupByFields);
        }

        // Could not find data for X axis
        if ($_axisXContainer === false) {
            return false;
        }

        // Get last fields for X and series
        $_lastAxisXField = end($_axisXContainer[self::AXIS_FIELDS]);
        $_lastSeriesField = end($_multiSeriesContainer[self::AXIS_FIELDS]);

        if (count($_valueFields) == 1) {

            // Split last X row if possible
            $_distinctValues = $_SWIFT_KQLParserResultObject->GetDistinctValues();
            if (!empty($_distinctValues)) {

                if ($_axisXContainer[self::AXIS_AXIS] == SWIFT_ReportChart::AXIS_X) {
                    $_originalAxisXContainer = &$_axisXContainer;
                    $_originalLastAxisXField = &$_lastAxisXField;
                } else {
                    $_originalAxisXContainer = &$_multiSeriesContainer;
                    $_originalLastAxisXField = &$_lastSeriesField;
                }

                if ($_originalLastAxisXField &&
                    (count($_originalAxisXContainer[self::AXIS_FIELDS]) > 1) &&
                    ($_originalAxisXContainer[self::AXIS_TYPE] != SWIFT_KQLSchema::FIELDTYPE_UNIXTIME) &&
                    isset($_distinctValues[$_originalLastAxisXField[self::AXIS_ID]]) &&
                    (count($_distinctValues[$_originalLastAxisXField[self::AXIS_ID]]) > 1) &&
                    (count($_distinctValues[$_originalLastAxisXField[self::AXIS_ID]]) < 4)) {

                    $_dataField = $_originalLastAxisXField;
                    array_pop($_originalAxisXContainer[self::AXIS_FIELDS]);
                    $_originalLastAxisXField = end($_originalAxisXContainer[self::AXIS_FIELDS]);

                    $_dataValue = $_valueFields[0];
                    $_valueFields = $_distinctValues[$_dataField[self::ORIGINAL_ID]];
                    for ($i = 0; $i < count($_valueFields); $i++) {
                        $_valueFields[$i] = $_SWIFT_KQL->GetParsedDistinctValue($_dataField[self::ORIGINAL_ID], $_valueFields[$i]);
                        $_dataFields[$_valueFields[$i]] = $_dataFields[$_dataValue];
                        $_dataFields[$_valueFields[$i]][self::ORIGINAL_NAME] = $_valueFields[$i];
                    }
                    unset($_dataFields[$_dataValue]);
                }
            }

        }

        // Set X container
        $_xContainer = array(
            $_axisXContainer[self::AXIS_AXIS],
            $_axisXContainer[self::AXIS_TYPE],
            $_lastAxisXField[self::ORIGINAL_NAME],
            $_lastAxisXField[self::ORIGINAL_ID],
            count($_axisXContainer[self::AXIS_FIELDS]) - 1
        );

        /*
         * NOTE:
         * In a case only single serie is available we can group values and use them as series.
         * For example: stacked columns can be used if AVG/MIN/MAX are used for the same field.
         *
        $_valueGroups = array();
        if (count($_multiSeriesContainer[self::AXIS_FIELDS]) == 0) {
            foreach ($_valueFields as $_fieldName) {
                if ($_dataFields[$_fieldName][self::ORIGINAL_FIELD] === false) {
                    $_valueGroups[] = $_fieldName;
                } else {
                    if (isset($_valueGroups[$_dataFields[$_fieldName][self::ORIGINAL_FIELD]])) {
                        if (!is_array($_valueGroups[$_dataFields[$_fieldName][self::ORIGINAL_FIELD]])) {
                            $_valueGroups[$_dataFields[$_fieldName][self::ORIGINAL_FIELD]] = array($_valueGroups[$_dataFields[$_fieldName][self::ORIGINAL_FIELD]]);
                        }
                        $_valueGroups[$_dataFields[$_fieldName][self::ORIGINAL_FIELD]][] = $_fieldName;
                    } else {
                        $_valueGroups[$_dataFields[$_fieldName][self::ORIGINAL_FIELD]] = $_fieldName;
                    }
                }
            }
        } else {
            $_valueGroups = $_valueFields;
        }
        */

        // Set series container
        $_seriesContainer = array();
        if ($_lastSeriesField) {
            $_seriesContainer = array(
                $_multiSeriesContainer[self::AXIS_AXIS],
                $_multiSeriesContainer[self::AXIS_TYPE],
                $_lastSeriesField[self::ORIGINAL_NAME],
                $_lastSeriesField[self::ORIGINAL_ID],
                count($_multiSeriesContainer[self::AXIS_FIELDS]) - 1
            );
        }

        // Calculate series and values count
        $_seriesCount = 1;
        $_valuesCount = 0;
        if ($_xContainer[SWIFT_ReportChart::AXIS_AXIS] != SWIFT_ReportChart::AXIS_X) {
            $_seriesCount = floor(count($_dataContainer[0]) / count($_dataFields));
            $_valuesCount = count($_dataContainer);
        } else {
            $_seriesCount = floor(count($_dataContainer) / count($_dataFields));
            if (count($_groupByFields) > 0) {
                $_valuesCount = count($_dataContainer[0]);
            } else {
                $_valuesCount = 1; // tabular reports
            }
        }

        // Check values count
        if ($_valuesCount < 2) {
            return false;
        }

        // Process data fields
        foreach ($_valueFields as $_fieldName) {
            if (count($_chartsArray) >= self::CHART_COUNT) {
                break;
            }

            // Set Y container
            $_yContainer = array(
                false,
                $_dataFields[$_fieldName][self::ORIGINAL_TYPE],
                $_dataFields[$_fieldName][self::ORIGINAL_NAME],
                $_fieldName,
                count($_xDataContainer) - 1,
                count($_dataFields) > 1
            );

            // Chart attributes (<chart ...>)
            $_chartAttributes = array();
            if ($_dataFields[$_fieldName][self::ORIGINAL_TYPE] == SWIFT_KQLSchema::FIELDTYPE_SECONDS) {
                $_chartAttributes['defaultNumberScale'] ='s';
                $_chartAttributes['numberScaleValue'] = '60,60,24';
                $_chartAttributes['numberScaleUnit'] = 'm,h,d';
            }
            if ($_dataFields[$_fieldName][self::ORIGINAL_FUNC] == 'PERCENT') {
                $_chartAttributes['numberSuffix'] ='%';
            }

            // Chart options
            $_chartOptions = array();

            // Line/Area chart
            if ($_axisXContainer[self::AXIS_TYPE] == SWIFT_KQLSchema::FIELDTYPE_UNIXTIME) {

                // Area chart
                if (($_seriesCount < 4) &&
                    (($_dataFields[$_fieldName][self::ORIGINAL_FUNC] == 'COUNT') ||
                    ($_dataFields[$_fieldName][self::ORIGINAL_FUNC] == 'SUM'))) {

                    try {
                        $_chartsArray[] = new SWIFT_ReportChartArea($_xContainer, $_yContainer, $_seriesContainer,
                                                                    $_xDataContainer, $_yDataContainer, $_dataContainer,
                                                                    $_chartOptions, $_chartAttributes);
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    }

                // Line chart
                } else {

                    try {
                        $_chartsArray[] = new SWIFT_ReportChartLine($_xContainer, $_yContainer, $_seriesContainer,
                                                                    $_xDataContainer, $_yDataContainer, $_dataContainer,
                                                                    $_chartOptions, $_chartAttributes);
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    }

                }

            // Column/Pie chart
            } elseif ($_axisXContainer[self::AXIS_TYPE] == SWIFT_KQLSchema::FIELDTYPE_STRING) {

                if (($_seriesCount > 1) &&
                    (count($_dataFields) == 1) &&
                    ($_multiSeriesContainer[self::AXIS_TYPE] == SWIFT_KQLSchema::FIELDTYPE_STRING) &&
                    (($_dataFields[$_fieldName][self::ORIGINAL_FUNC] == 'COUNT') ||
                    ($_dataFields[$_fieldName][self::ORIGINAL_FUNC] == 'SUM'))) {

                    $_pieDataContainer = array(array($_yContainer[self::ORIGINAL_NAME]));

                    if ($_xContainer[self::AXIS_AXIS] == self::AXIS_X) {
                        $_backupContainer = $_xContainer;
                        $_xContainer = $_seriesContainer;
                        $_seriesContainer = $_backupContainer;
                    }

                    // Generate Y Pie
                    $_yPieContainer = array();
                    for ($i = 0; $i < count($_dataContainer); $i++) {
                        $_yPieContainer[$i] = array();
                        $_yPieContainer[$i][] = array_sum($_dataContainer[$i]);
                    }
                    try {
                        $_chartsArray[] = new SWIFT_ReportChartPie($_xContainer, $_yContainer, array(),
                                                                   $_pieDataContainer, $_yDataContainer, $_yPieContainer,
                                                                   $_chartOptions, $_chartAttributes);
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    }

                    // Generate X Pie
                    $_xPieContainer = array();
                    $_xPieContainer[0] = array();
                    for ($i = 0; $i < count($_dataContainer[0]); $i++) {
                        $_sum = 0;
                        for ($j = 0; $j < count($_dataContainer); $j++) {
                            $_sum += $_dataContainer[$j][$i];
                        }
                        $_xPieContainer[0][$i] = $_sum;
                    }
                    try {
                        $_chartsArray[] = new SWIFT_ReportChartPie($_seriesContainer, $_yContainer, array(),
                                                                   $_xDataContainer, $_pieDataContainer, $_xPieContainer,
                                                                   $_chartOptions, $_chartAttributes);
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    }

                } else {

                    // Pie chart
                    if (($_seriesCount == 1) && ($_valuesCount < 50) &&
                        (($_dataFields[$_fieldName][self::ORIGINAL_FUNC] == 'COUNT') ||
                        ($_dataFields[$_fieldName][self::ORIGINAL_FUNC] == 'SUM'))) {

                        try {
                            $_chartsArray[] = new SWIFT_ReportChartPie($_xContainer, $_yContainer, $_seriesContainer,
                                                                       $_xDataContainer, $_yDataContainer, $_dataContainer,
                                                                       $_chartOptions, $_chartAttributes);
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                        }

                    } elseif ($_valuesCount <= 3000) {

                        try {
                            $_chartsArray[] = new SWIFT_ReportChartColumn($_xContainer, $_yContainer, $_seriesContainer,
                                                                          $_xDataContainer, $_yDataContainer, $_dataContainer,
                                                                          $_chartOptions, $_chartAttributes);
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                        }

                    } else {
                        $_SWIFT = SWIFT::GetInstance();

                        $_chartNotifications[] = $_SWIFT->Language->Get('notifycharttoomuchdata');
                    }

                }
            }

        }

        // Get colors count
        $_colorIdentifiers = array();
        foreach ($_chartsArray as $_chart) {
            foreach ($_chart->GetColorIdentifiers() as $_colorIdentifier) {
                $_colorIdentifiers[$_colorIdentifier] = false;
            }
        }
        $_colorsCount = count($_colorIdentifiers);

        // Generate color palette
        if ($_colorsCount > 0) {
            $_colorPalette = SWIFT_ReportChartBase::GeneratePalette($_colorsCount);
            foreach ($_colorIdentifiers as $_identifier => $_color) {
                $_colorIdentifiers[$_identifier] = array_shift($_colorPalette);
            }

            foreach ($_chartsArray as $_chart) {
                $_chart->SetColorPalette($_colorIdentifiers);
            }
        }

        return array_merge($_chartsArray, $_chartNotifications);
    }

    /**
     * Get Axis Type (if single)
     *
     * @author Andriy Lesyuk
     * @param string $_axisType (self::AXIS_X or self::AXIS_Y)
     * @param array $_groupByFields
     * @return int The Axis Data Type, "false" if not single
     */
    public static function GetDataType($_axisType, $_groupByFields)
    {
        $_axisDataType = false;

        foreach ($_groupByFields as $_fieldName => $_fieldContainer) {
            if ($_fieldContainer[self::ORIGINAL_AXIS] == $_axisType) {
                $_axisDataType = $_fieldContainer[self::ORIGINAL_TYPE];
            }
        }

        return $_axisDataType;
    }

    public static function GetAxisContainer($_axisType, $_groupByFields, $_axisDataType = 0)
    {
        $_axisContainer = array();

        if ($_axisDataType === 0) {
            $_axisDataType = self::GetDataType($_axisType, $_groupByFields);
        }

        $_axisContainer[self::AXIS_AXIS] = $_axisType;
        $_axisContainer[self::AXIS_TYPE] = $_axisDataType;
        $_axisContainer[self::AXIS_NAME] = false; // name
        $_axisContainer[self::AXIS_ID] = false; // id
        $_axisContainer[self::AXIS_FIELDS] = array();

        foreach ($_groupByFields as $_fieldName => $_fieldContainer) {
            if ($_fieldContainer[self::ORIGINAL_AXIS] == $_axisType) {
                $_axisContainer[self::AXIS_FIELDS][] = $_fieldContainer;
            }
        }

        return $_axisContainer;
    }

}

?>
