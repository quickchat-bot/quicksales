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

use Base\Library\KQL\SWIFT_KQL;
use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\KQL2\SWIFT_KQL2;

/**
 * The Matrix Report Renderer
 *
 * @author Varun Shoor
 */
class SWIFT_ReportRenderMatrix extends SWIFT_ReportRender
{
    protected $_groupByXCountMap = array();
    protected $_baseTitleContainer = array();
    protected $_parentTitleIgnoreList = array();
    protected $_dataContainer = array();
    protected $_baseUserFieldList = array();

    protected $_replacementYKey = false;
    protected $_groupByYCountMap = array();
    protected $_resultsContainerY = array();

    protected $_baseUserFieldCount = 0;

    protected $_matrixChartIndexes = array();
    protected $_matrixChartXContainer = array();
    protected $_matrixChartDataContainer = array();

    protected $_tableTag = 'tbody';
    protected $_currentRow = 0;

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

        // Build a list of all fields that are grouped
        foreach ($this->_sqlGroupByFields as $_groupByField) {
            $this->_groupFieldList[$_groupByField[1]] = $_groupByField[1];
        }

        foreach ($this->_sqlGroupByXFields as $_groupByXField) {
            $this->_groupFieldList[$_groupByXField[1]] = $_groupByXField[1];
        }

        /**
         * ---------------------------------------------
         * Load up the data
         * ---------------------------------------------
         */
        $this->LoadMatrixData();

        /**
         * ---------------------------------------------
         * Build the Y Map
         * ---------------------------------------------
         */
        $_processedGroupByYMap = $this->BuildMatrixYMap();

        /**
         * ---------------------------------------------
         * Build the count map for X grid
         * ---------------------------------------------
         */
        $_reversedXGroupByFields = array_reverse($this->_sqlGroupByXFields, false);

        $_groupByXCountMap = array();

        $_totalCount = 0;

        foreach ($_reversedXGroupByFields as $_index => $_groupByXField) {
            $_fieldNameReference = $_groupByXField[1];

            $_groupByXCountMap[$_fieldNameReference] = $_totalCount;

            if (!isset($this->_sqlDistinctValueContainer[$_fieldNameReference])) {
                continue;
            }

            $_fieldCount = count($this->_sqlDistinctValueContainer[$_fieldNameReference]);
            if ($_fieldCount == 1) {
                continue;
            }

            $_totalCount += $_fieldCount;

        }

        $this->_groupByXCountMap = $_groupByXCountMap;




        $_processedGroupByXMap = array();

        $this->BuildMatrixXGrid($this->_sqlGroupByXFields, $_processedGroupByXMap);

        $this->CleanupMatrixXGrid($this->_sqlGroupByXFields, $_processedGroupByXMap);

        $this->RecountMatrixXGrid($this->_sqlGroupByXFields, $_processedGroupByXMap);

        /**
         * ---------------------------------------------
         * Build the X titles
         * ---------------------------------------------
         */

        $this->AppendOutput('<div class="reportstablecontainer"><table cellpadding="0" cellspacing="1" border="0" width="100%" class="reportstable">');


        $this->AppendOutput('<thead>');

        $_parentGroupByXField = $this->_sqlGroupByXFields[0];


        // Render the left edge
        $_baseFieldCount = count($this->_baseUserFieldList);

        // If its only one field, we reset it to 0 because we wont be showing it in a separate column
        $_baseFieldSpan = 0;
        $_colSpanUserField = 1;
        if ($_baseFieldCount > 1) {
            $_colSpanUserField = $_baseFieldCount;

            $_baseFieldSpan = 1;
        } else {
            $_colSpanUserField = 0;
        }
        $_leftEdge = '<td colspan="' . count($this->_sqlGroupByFields) . '" rowspan="' . (count($this->_sqlGroupByXFields)+$_baseFieldSpan) . '" class="reporttdtitle">&nbsp;</td>';

        $_loopCount = 0;

        $this->RenderXTitle($this->_sqlGroupByXFields, $_processedGroupByXMap, $_loopCount, $_colSpanUserField, $_leftEdge);

        // Render the sub titles for user fields if they are more than 1
        if ($_baseFieldCount > 1) {
            $this->AppendOutput('<tr>');

            $_internalContainer = array();

            for ($index = 1; $index <= $_loopCount; $index++) {
                foreach ($this->_baseUserFieldList as $_userFieldTitle) {
                    $_renderUserFieldTitle = $_userFieldTitle;
                    if (isset($this->_sqlParsedTitles[$_userFieldTitle])) {
                        $_renderUserFieldTitle = $this->_sqlParsedTitles[$_userFieldTitle][0];
                    }
                    if ($this->_internalDisplayValues) {
                        $_internalContainer[] = $_renderUserFieldTitle;
                    } else {
                        $_internalContainer[] = $_userFieldTitle;
                    }
                    $this->AppendOutput('<td nowrap="nowrap" class="reporttdtitle">' . htmlspecialchars($_renderUserFieldTitle) . '</td>');
                }
            }

            $this->_internalXContainer[] = $_internalContainer;

            $this->IncrementRecordCount();
            $this->AppendOutput('</tr>');
        }

        $this->AppendOutput('</thead>');


        /**
         * ---------------------------------------------
         * Start process of rendering
         * ---------------------------------------------
         */


        $this->RebuildMatrixCountForYGrid($_processedGroupByYMap, $this->_sqlGroupByFields);

        $this->CleanupMatrixYGrid($_processedGroupByYMap, $this->_sqlGroupByFields);

        $this->RecountMatrixYGrid($_processedGroupByYMap, $this->_sqlGroupByFields);


        // Finally render the body!
        $this->AppendOutput('<' . $this->_tableTag . '>');

        $this->RenderBody($this->_sqlGroupByFields, $_processedGroupByYMap);

        $this->AppendOutput('</' . $this->_tableTag . '>');


        $this->AppendOutput('</table></div>');

        /**
         * ---------------------------------------------
         * Get Chart Object
         * ---------------------------------------------
         */

        $_chartObjects = $this->GetCharts();

        if ($_chartObjects) {
            foreach ($_chartObjects as $_chartObject) {
                $this->AddChart($_chartObject);
            }
        }

        return true;
    }

    /**
     * Render the X titles
     *
     * @author Varun Shoor
     * @param array $_groupByXFields
     * @param array $_groupMap
     * @param int $_loopCount
     * @param int $_colSpanUserField
     * @param string $_leftEdge (OPTIONAL)
     * @param bool $_gridFieldRenderTable (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderXTitle($_groupByXFields, &$_groupMap, &$_loopCount, $_colSpanUserField, $_leftEdge = '', &$_gridFieldRenderTable = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!count($_groupByXFields)) {
            return false;
        }

        $_baseCall = false;
        if ($_gridFieldRenderTable === false) {
            $_baseCall = true;
            $_gridFieldRenderTable = array();
        }

        $_fieldName = $_groupByXFields[0][0];
        $_fieldNameReference = $_groupByXFields[0][1];

        $_extendedInfo = $_baseInfo = '';

        if (isset($this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN])) {
            $_extendedInfo .= ' align="' . $this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN] . '"';
        } else {
            $_extendedInfo .= ' align="center"';
        }

        if (isset($this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_WIDTH])) {
            $_baseInfo .= ' width="' . $this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_WIDTH] . '"';
        }

        $_finalInfo = $_extendedInfo . $_baseInfo;

        $_returnHTML = $_leftEdge;

        $_internalContainer = array();

        if (!isset($_gridFieldRenderTable[$_fieldNameReference])) {
            $_gridFieldRenderTable[$_fieldNameReference] = '';
        }
        if (!isset($this->_matrixChartXContainer[$_fieldNameReference])) {
            $this->_matrixChartXContainer[$_fieldNameReference] = array();
        }

        if (isset($_groupMap[$_fieldNameReference])) {
            $_fieldChunks = explode('_', $_fieldNameReference);

            foreach ($_groupMap[$_fieldNameReference] as $_fieldValue => $_fieldValueContainer) {
                $this->IncrementRecordCount();

                if ((count($_fieldChunks) >= 3) && ($_fieldChunks[1] == 'cf') && isset($this->_customFields[$_fieldChunks[2]])) {
                    $serialized = $encrypted = false;
                    if (isset($_fieldValueContainer['values']['results'][0])) {
                        $serialized = $_fieldValueContainer['values']['results'][0][$_fieldNameReference . '_isserialized'] ?? false;
                        $encrypted = $_fieldValueContainer['values']['results'][0][$_fieldNameReference . '_isencrypted'] ?? false;
                    }
                    $_renderFieldValue = SWIFT_KQL::GetParsedCustomFieldValue($_fieldValue, $this->_customFields[$_fieldChunks[2]], $encrypted, $serialized);
                } else {
                    $_renderFieldValue = $this->KQL->GetParsedDistinctValue($_fieldNameReference, $_fieldValue);
                }

                // Save value into chart data
                $_internalContainer[] = $_renderFieldValue;

                $_baseTDInfo = '';
                if (isset($_fieldValueContainer['childcount']) && ($_fieldValueContainer['childcount'] > 0 || !empty($_colSpanUserField))) {

                    /*
                     * BUG FIX - Andriy Lesyuk
                     *
                     * SWIFT-2034 Table heading breakage (invalid colspan)
                     *
                     * Comments: Changed algo for colspan calculation
                     */

                    $_colSpan = 0;
                    if ($_colSpanUserField > 0) {
                        if ($_fieldValueContainer['childcount'] > 0) {
                            $_colSpan = $_fieldValueContainer['childcount'] * $_colSpanUserField;
                        } else {
                            $_colSpan = $_colSpanUserField;
                        }
                    } else {
                        $_colSpan = $_fieldValueContainer['childcount'];
                    }

                    if ($_colSpan > 1) {
                        // Save value for each column
                        for ($i = 1; $i < $_colSpan; $i++) {
                            $_internalContainer[] = $_renderFieldValue;
                        }
                    }

                    $_baseTDInfo = ' colspan="' . $_colSpan . '"';
                }

                // Last field?
                if (count($_groupByXFields) == 1) {
                    $_loopCount++;
                }

                $_tdFinalInfo = $_finalInfo . $_baseTDInfo;
                $_returnHTML .= '<td' . $_tdFinalInfo . ' class="reporttdtitle">' . htmlspecialchars($_renderFieldValue) . '</td>';

                $_temporaryResultMap = array();
                if (isset($_groupMap[$_fieldNameReference][$_fieldValue]['values'])) {

                    $_replacementYKey = $this->_replacementYKey;

                    // Loop through the results
                    foreach ($_groupMap[$_fieldNameReference][$_fieldValue]['values']['results'] as $_resultContainer) {
                        $_processedReplacementKey = preg_replace_callback('/%(.*)%/SU', function($_matches) use ($_resultContainer) { return $_resultContainer[$_matches[1]]; }, $_replacementYKey);

                        // More than one field then we loop
                        if ($this->_baseUserFieldCount >= 1) {
                            $this->_groupByYCountMap[$_processedReplacementKey]++;

                            $_rowIndex = false;
                            if (preg_match('/%grandtotalrowgroupbyexpression\[([0-9]+)\]%/', $_processedReplacementKey, $_matches)) {
                                $_rowIndex = (int) ($_matches[1]);
                            }

                            foreach ($this->_baseUserFieldList as $_baseUserFieldNameReference) {
                                if (!isset($_temporaryResultMap[$_processedReplacementKey])) {
                                    $_temporaryResultMap[$_processedReplacementKey] = '';
                                }
                                $_temporaryResultMap[$_processedReplacementKey] .= '<td>' . $this->RenderColumnValue($_baseUserFieldNameReference, $_resultContainer[$_baseUserFieldNameReference], $_rowIndex, $_resultContainer) . '</td>';

                                // Add data to charts
                                if (!isset($this->_matrixChartDataContainer[$_processedReplacementKey])) {
                                    $this->_matrixChartDataContainer[$_processedReplacementKey] = array();
                                }
                                if ($this->_internalDisplayValues) {
                                    $this->_matrixChartDataContainer[$_processedReplacementKey][] = $this->ExportColumnValue($_baseUserFieldNameReference, $_resultContainer[$_baseUserFieldNameReference], $_rowIndex, $_resultContainer);
                                } else {
                                    $this->_matrixChartDataContainer[$_processedReplacementKey][] = $_resultContainer[$_baseUserFieldNameReference];
                                }
                            }
                        } else {
                            $_temporaryResultMap[$_processedReplacementKey] = '<td>&nbsp;</td>';

                            // Add data to charts
                            $this->_matrixChartDataContainer[$_processedReplacementKey] = false;
                        }
                    }

                    // Now we need to check the final list
                    foreach ($this->_resultsContainerY as $_renderKey => $_renderValue) {
                        if ($this->_resultsContainerY[$_renderKey] === false) {
                            $this->_resultsContainerY[$_renderKey] = '';
                        }

                        if (isset($_temporaryResultMap[$_renderKey])) {
                            $this->_resultsContainerY[$_renderKey] .= $_temporaryResultMap[$_renderKey];

                        // We didnt see a value for this, so we need to go through base field and add it up
                        } else {
                            foreach ($this->_baseUserFieldList as $_baseUserFieldNameReference) {
                                $this->_resultsContainerY[$_renderKey] .= '<td>&nbsp;</td>';

                                // Add data to charts
                                if (!isset($this->_matrixChartDataContainer[$_renderKey])) {
                                    $this->_matrixChartDataContainer[$_renderKey] = array();
                                }
                                $this->_matrixChartDataContainer[$_renderKey][] = false;
                            }
                        }
                    }
                }
            }
        }

        $this->_matrixChartXContainer[$_fieldNameReference] = array_merge($this->_matrixChartXContainer[$_fieldNameReference], $_internalContainer);

        $_gridFieldRenderTable[$_fieldNameReference] .= $_returnHTML;

        if (isset($_groupMap[$_fieldNameReference]) && count($_groupMap[$_fieldNameReference])) {
            foreach ($_groupMap[$_fieldNameReference] as $_fieldValue => $_fieldValueContainer) {
                if (isset($_fieldValueContainer['children'])) {

                    $_slicedGroupByXFields = array_slice($_groupByXFields, 1);

                    $this->RenderXTitle($_slicedGroupByXFields, $_groupMap[$_fieldNameReference][$_fieldValue]['children'], $_loopCount, $_colSpanUserField, '', $_gridFieldRenderTable);
                }
            }
        }

        if ($_baseCall) {
            foreach ($this->_matrixChartDataContainer as $_matrixKey => $_matrixValues) {
                foreach ($_matrixValues as $_matrixValue) {
                    if ($_matrixValue !== false) {
                        $this->_rowCount++;
                        break;
                    }
                }
            }
            $this->_rowCount -= $this->_extraCount;

            foreach ($_gridFieldRenderTable as $_renderFieldNameReference => $_renderedHTML) {
                $this->AppendOutput('<tr>');
                    $this->AppendOutput($_renderedHTML);
                $this->AppendOutput('</tr>');

                $this->_internalXContainer[] = $this->_matrixChartXContainer[$_renderFieldNameReference];
            }
        }

        return true;
    }

    /**
     * Render the content body
     *
     * @author Varun Shoor
     * @param array $_groupByYFields
     * @param array $_groupYMap
     * @param array|bool $_prefixContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderBody($_groupByYFields, &$_groupYMap, &$_prefixContainer = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!count($_groupByYFields)) {
            return false;
        }

        if ($_prefixContainer === false) {
            $_prefixContainer = array();
        }

        $_fieldName = $_groupByYFields[0][0];
        $_fieldNameReference = $_groupByYFields[0][1];

        $_extendedInfo = $_baseInfo = '';

        if (isset($this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN])) {
            $_extendedInfo .= ' align="' . $this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN] . '"';
        } else {
            $_extendedInfo .= ' align="center"';
        }

        if (isset($this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_WIDTH])) {
            $_baseInfo .= ' width="' . $this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_WIDTH] . '"';
        }

        $_finalInfo = $_extendedInfo . $_baseInfo;

        if (isset($_groupYMap[$_fieldNameReference])) {
            $_fieldChunks = explode('_', $_fieldNameReference);

            foreach ($_groupYMap[$_fieldNameReference] as $_fieldValue => $_fieldValueContainer) {
                $_internalBaseIndex = count($this->_internalDataContainer);

                if ((count($_fieldChunks) >= 3) && ($_fieldChunks[1] == 'cf') && isset($this->_customFields[$_fieldChunks[2]])) {
                    $_fieldValue = SWIFT_KQL::GetParsedCustomFieldValue($_fieldValue, $this->_customFields[$_fieldChunks[2]], false, false);
                } else {
                    $_fieldValue = $this->KQL->GetParsedDistinctValue($_fieldNameReference, $_fieldValue);
                }

                $_fieldTitle = $_fieldValue;
                if ($this->_currentRow >= $this->_rowCount) {
                    $_totalIndex = $this->_currentRow - $this->_rowCount;
                    $_totalExpressions = $this->KQLObject->GetClause('TOTALIZE BY');
                    if ($_totalExpressions && is_array($_totalExpressions) && isset($_totalExpressions[$_totalIndex]) &&
                        _is_array($_totalExpressions[$_totalIndex]) && is_string($_totalExpressions[$_totalIndex][0])) {
                        $_fieldTitle = $_totalExpressions[$_totalIndex][0];
                    } else {
                        $_fieldTitle = $this->Language->Get('totaldefaulttitle');
                    }
                }

                // Save field for charts
                if (count($this->_matrixChartIndexes) > 0) {
                    $this->_internalYContainer[$this->_matrixChartIndexes[count($this->_matrixChartIndexes)-1]++][] = $_fieldTitle;
                } else {
                    $this->_internalYContainer[] = array($_fieldTitle);
                }

                $_baseTDInfo = '';
                if (isset($_fieldValueContainer['childcount']) && $_fieldValueContainer['childcount'] > 0) {
                    $_baseTDInfo = ' rowspan="' . ($_fieldValueContainer['childcount']) . '"';

                    // Copy fields for charts
                    for ($i = 1; $i < $_fieldValueContainer['childcount']; $i++) {
                        if (count($this->_matrixChartIndexes) > 0) {
                            $this->_internalYContainer[$this->_matrixChartIndexes[count($this->_matrixChartIndexes)-1]++][] = $_fieldTitle;
                        } else {
                            $this->_internalYContainer[] = array($_fieldTitle);
                        }
                    }
                }

                $_tdFinalInfo = $_finalInfo . $_baseTDInfo;
                $_prefixContainer[] = '<td' . $_tdFinalInfo . ' class="reporttdytitle">' . htmlspecialchars($_fieldTitle) . '</td>';

                if (isset($_fieldValueContainer['children'])) {

                    $_slicedGroupByYFields = array_slice($_groupByYFields, 1);

                    $this->_matrixChartIndexes[] = $_internalBaseIndex; // Nesting index

                    $this->RenderBody($_slicedGroupByYFields, $_groupYMap[$_fieldNameReference][$_fieldValue]['children'], $_prefixContainer);

                    array_pop($this->_matrixChartIndexes);
                }

                if (isset($this->_resultsContainerY[$_fieldValueContainer['key']])) {
                    if ($this->_currentRow == $this->_rowCount) {
                        $this->AppendOutput('</' . $this->_tableTag . '>');
                        $this->_tableTag = 'tfoot';
                        $this->AppendOutput('<' . $this->_tableTag . '>');
                    }

                    if ($this->_currentRow >= $this->_rowCount) {
                        $_extraInfo = '';
                        $_colSpan = count($_prefixContainer);
                        if ($_colSpan > 1) {
                            $_extraInfo = ' colspan="' . $_colSpan . '"';
                        }
                        $_prefixContainer = array('<td align="center"' . $_extraInfo . ' class="reporttdytitle">' . htmlspecialchars($_fieldTitle) . '</td>');
                    }

                    $this->AppendOutput('<tr>');
                        $this->AppendOutput(implode('', $_prefixContainer) . $this->_resultsContainerY[$_fieldValueContainer['key']]);
                    $this->AppendOutput('</tr>');
                    $this->IncrementRecordCount();

                    $this->_currentRow++;

                    // Convert data for charts
                    $this->_internalDataContainer[] = $this->_matrixChartDataContainer[$_fieldValueContainer['key']];

                    $_prefixContainer = array();
                }

            }
        }


        return true;
    }
}
?>
