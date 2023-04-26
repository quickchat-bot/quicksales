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
use Base\Library\KQL2\SWIFT_KQL2;

/**
 * The Matrix Report Exporter
 *
 * @author Andriy Lesyuk
 */
class SWIFT_ReportExportMatrix extends SWIFT_ReportExport
{
    protected $_matrixDataCountContainer = array();

    protected $_currentRow = 0;

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     * @param SWIFT_KQL2 $_SWIFT_KQL2Object
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @param SWIFT_KQLParserResult $_SWIFT_KQLParserResultObject
     */
    public function __construct($_SWIFT_KQL2Object, SWIFT_Report $_SWIFT_ReportObject, SWIFT_KQLParserResult $_SWIFT_KQLParserResultObject)
    {
        parent::__construct($_SWIFT_KQL2Object, $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);
    }

    /**
     * Export the Report
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Generate($_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
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

        $_processedGroupByXMap = array();

        $this->BuildMatrixXGrid($this->_sqlGroupByXFields, $_processedGroupByXMap);

        $this->CleanupMatrixXGrid($this->_sqlGroupByXFields, $_processedGroupByXMap);

        $this->RecountMatrixXGrid($this->_sqlGroupByXFields, $_processedGroupByXMap);

        /**
         * ---------------------------------------------
         * Build the X titles
         * ---------------------------------------------
         */

        $this->ResetColumn();

        // If its only one field, we reset it to 0 because we wont be showing it in a separate column
        $_baseFieldSpan = 0;
        $_colSpanUserField = 1;

        if ($this->_baseUserFieldCount > 1) {
            $_colSpanUserField = $this->_baseUserFieldCount;
            $_baseFieldSpan = 1;
        } else {
            $_colSpanUserField = 0;
        }

        $_columnIndex = $this->NextColumn();

        $this->MergeAndStyleCells($_columnIndex, $this->GetRecordCount() + 1, $_columnIndex + count($this->_sqlGroupByFields) - 1, $this->GetRecordCount() + count($this->_sqlGroupByXFields) + $_baseFieldSpan, self::CELL_TITLE);

        $_loopCount = 0;
        $_leftEdge = count($this->_sqlGroupByFields);

        $this->ExportXTitle($this->_sqlGroupByXFields, $_processedGroupByXMap, $_loopCount, $_colSpanUserField, $_leftEdge);

        // Export the sub titles for user fields if they are more than 1
        if ($this->_baseUserFieldCount > 1) {
            $this->ResetColumn($_leftEdge);

            for ($index = 1; $index <= $_loopCount; $index++) {
                foreach ($this->_baseUserFieldList as $_userFieldTitle) {
                    $_exportUserFieldTitle = $_userFieldTitle;
                    if (isset($this->_sqlParsedTitles[$_userFieldTitle])) {
                        $_exportUserFieldTitle = $this->_sqlParsedTitles[$_userFieldTitle][0];
                    }

                    $_columnIndex = $this->NextColumn();

                    $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                    $this->StyleCell($_sharedStyle);
                    $this->StyleTitleCell($_sharedStyle);

                    $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_exportUserFieldTitle);

                    $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                }
            }

            $this->IncrementRecordCount();
        }

        /**
         * ---------------------------------------------
         * Start Process of Export
         * ---------------------------------------------
         */

        $this->RebuildMatrixCountForYGrid($_processedGroupByYMap, $this->_sqlGroupByFields);

        $this->CleanupMatrixYGrid($_processedGroupByYMap, $this->_sqlGroupByFields);

        $this->RecountMatrixYGrid($_processedGroupByYMap, $this->_sqlGroupByFields);

        /**
         * ---------------------------------------------
         * Export the Body
         * ---------------------------------------------
         */

        $this->ExportBody($this->_sqlGroupByFields, $_processedGroupByYMap);

        return true;
    }

    /**
     * Export the X Titles
     *
     * @author Andriy Lesyuk
     * @param array $_groupByXFields
     * @param array $_groupMap
     * @param int $_loopCount
     * @param int $_colSpanUserField
     * @param int $_leftEdge (OPTIONAL)
     * @param bool $_gridFieldExportTable (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ExportXTitle($_groupByXFields, &$_groupMap, &$_loopCount, $_colSpanUserField, $_leftEdge = 0, &$_gridFieldExportTable = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!count($_groupByXFields)) {
            return false;
        }

        $_baseCall = false;
        if ($_gridFieldExportTable === false) {
            $_baseCall = true;
            $_gridFieldExportTable = array();
        }

        $_fieldName = $_groupByXFields[0][0];
        $_fieldNameReference = $_groupByXFields[0][1];

        /**
         * Save Extended Info
         */

        $_extendedInfo = array();

        if (isset($this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN]) &&
            isset($this->_reportExcelAlignmentMap[$this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN]])) {
            $_extendedInfo['align'] = $this->_reportExcelAlignmentMap[$this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN]];
        } else {
            $_extendedInfo['align'] = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        }

        // Initialize container
        if (!isset($_gridFieldExportTable[$_fieldNameReference])) {
            $_gridFieldExportTable[$_fieldNameReference] = array();
        }

        if (isset($_groupMap[$_fieldNameReference])) {
            $_fieldChunks = explode('_', $_fieldNameReference);

            foreach ($_groupMap[$_fieldNameReference] as $_fieldValue => $_fieldValueContainer) {

                $_exportInfo = array();

                // Save colspan value
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

                    $_exportInfo['colspan'] = $_colSpan;
                }

                // Last field?
                if (count($_groupByXFields) == 1) {
                    $_loopCount++;
                }

                if ((count($_fieldChunks) >= 3) && ($_fieldChunks[1] == 'cf') && isset($this->_customFields[$_fieldChunks[2]])) {
                    $_exportInfo['value'] = SWIFT_KQL::GetParsedCustomFieldValue($_fieldValue, $this->_customFields[$_fieldChunks[2]], false, false);
                } else {
                    $_exportInfo['value'] = $this->KQL->GetParsedDistinctValue($_fieldNameReference, $_fieldValue);
                }
                $_exportInfo['extended'] = &$_extendedInfo;

                $_gridFieldExportTable[$_fieldNameReference][] = $_exportInfo;

                if (isset($_groupMap[$_fieldNameReference][$_fieldValue]['values'])) {
                    $_temporaryResultMap = array();

                    $_replacementYKey = $this->_replacementYKey;

                    // Loop through the results
                    foreach ($_groupMap[$_fieldNameReference][$_fieldValue]['values']['results'] as $_resultContainer) {
                        $_processedReplacementKey = preg_replace_callback('/%(.*)%/SU', function($_matches) use ($_resultContainer) { return $_resultContainer[$_matches[1]]; }, $_replacementYKey);

                        // More than one field then we loop
                        if ($this->_baseUserFieldCount >= 1) {
                            $this->_groupByYCountMap[$_processedReplacementKey]++;

                            foreach ($this->_baseUserFieldList as $_baseUserFieldNameReference) {
                                if (!isset($_temporaryResultMap[$_processedReplacementKey])) {
                                    $_temporaryResultMap[$_processedReplacementKey] = array();
                                }
                                $_temporaryResultMap[$_processedReplacementKey][] = array($_baseUserFieldNameReference, $_resultContainer[$_baseUserFieldNameReference], $_resultContainer);

                                if (!isset($this->_matrixDataCountContainer[$_processedReplacementKey])) {
                                    $this->_matrixDataCountContainer[$_processedReplacementKey] = 1;
                                } else {
                                    $this->_matrixDataCountContainer[$_processedReplacementKey]++;
                                }
                            }
                        } else {
                            $_temporaryResultMap[$_processedReplacementKey] = array();
                            $_temporaryResultMap[$_processedReplacementKey][] = array();
                        }
                    }

                    // Now we need to check the final list
                    foreach ($this->_resultsContainerY as $_exportKey => $_exportValue) {
                        if ($this->_resultsContainerY[$_exportKey] === false) {
                            $this->_resultsContainerY[$_exportKey] = array();
                        }

                        if (isset($_temporaryResultMap[$_exportKey])) {
                            $this->_resultsContainerY[$_exportKey] = array_merge($this->_resultsContainerY[$_exportKey], $_temporaryResultMap[$_exportKey]);

                        // We didnt see a value for this, so we need to go through base field and add it up
                        } else {
                            foreach ($this->_baseUserFieldList as $_baseUserFieldNameReference) {
                                $this->_resultsContainerY[$_exportKey][] = array();
                            }
                        }
                    }
                }
            }
        }

        if (isset($_groupMap[$_fieldNameReference]) && count($_groupMap[$_fieldNameReference])) {
            foreach ($_groupMap[$_fieldNameReference] as $_fieldValue => $_fieldValueContainer) {
                if (isset($_fieldValueContainer['children'])) {
                    $_slicedGroupByXFields = array_slice($_groupByXFields, 1);

                    $this->ExportXTitle($_slicedGroupByXFields, $_groupMap[$_fieldNameReference][$_fieldValue]['children'], $_loopCount, $_colSpanUserField, $_leftEdge, $_gridFieldExportTable);
                }
            }
        }

        if ($_baseCall) {
            $this->_rowCount = count($this->_matrixDataCountContainer) - $this->_extraCount;

            foreach ($_gridFieldExportTable as $_exportFieldNameReference => $_exportInfoArray) {
                $this->ResetColumn($_leftEdge);

                foreach ($_exportInfoArray as $_exportInfo) {
                    $_columnIndex = $this->NextColumn();

                    if (isset($_exportInfo['colspan'])) {

                        $this->MergeAndStyleCells($_columnIndex, $this->GetRecordCount() + 1, $_columnIndex + $_exportInfo['colspan'] - 1, $this->GetRecordCount() + 1, self::CELL_TITLE);

                    } else {
                        $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                        $this->StyleCell($_sharedStyle);
                        $this->StyleTitleCell($_sharedStyle);

                        $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                    }

                    $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_exportInfo['value']);

                    if (isset($_exportInfo['extended'])) {
                        if (isset($_exportInfo['extended']['align'])) {
                            if (isset($_exportInfo['colspan'])) {
                                for ($i = $_columnIndex; $i < ($_columnIndex + $_exportInfo['colspan']); $i++) {
                                    $_sharedStyle = $this->GetCellStyle($i, $this->GetRecordCount() + 1);

                                    $_sharedStyle->getAlignment()->setHorizontal($_exportInfo['extended']['align']);

                                    $this->SetCellStyle($i, $this->GetRecordCount() + 1, $_sharedStyle);
                                }
                            } else {
                                $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                                $_sharedStyle->getAlignment()->setHorizontal($_exportInfo['extended']['align']);

                                $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                            }
                        }
                    }

                }

                if (count($_exportInfoArray) > 0) {
                    $this->IncrementRecordCount();
                }
            }
        }

        return true;
    }

    /**
     * Export the Content Body
     *
     * @author Andriy Lesyuk
     * @param array $_groupByYFields
     * @param array $_groupYMap
     * @param array|bool $_prefixContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ExportBody($_groupByYFields, &$_groupYMap, &$_prefixContainer = false)
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

        /**
         * Save Extended Info
         */

        $_extendedInfo = array();

        if (isset($this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN]) &&
            isset($this->_reportExcelAlignmentMap[$this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN]])) {
            $_extendedInfo['align'] = $this->_reportExcelAlignmentMap[$this->_baseTitleContainer[$_fieldNameReference][2][SWIFT_KQLSchema::FIELD_ALIGN]];
        } else {
            $_extendedInfo['align'] = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        }

        if (isset($_groupYMap[$_fieldNameReference])) {
            $_fieldChunks = explode('_', $_fieldNameReference);

            foreach ($_groupYMap[$_fieldNameReference] as $_fieldValue => $_fieldValueContainer) {

                $_exportInfo = array();

                if (isset($_fieldValueContainer['childcount']) && $_fieldValueContainer['childcount'] > 0) {
                    $_exportInfo['rowspan'] = $_fieldValueContainer['childcount'];
                }

                if ((count($_fieldChunks) >= 3) && ($_fieldChunks[1] == 'cf') && isset($this->_customFields[$_fieldChunks[2]])) {
                    $_exportInfo['value'] = SWIFT_KQL::GetParsedCustomFieldValue($_fieldValue, $this->_customFields[$_fieldChunks[2]], false, false);
                } else {
                    $_exportInfo['value'] = $this->KQL->GetParsedDistinctValue($_fieldNameReference, $_fieldValue);
                }
                $_exportInfo['extended'] = &$_extendedInfo;

                $_prefixContainer[] = $_exportInfo;

                if (isset($_fieldValueContainer['children'])) {
                    $_slicedGroupByYFields = array_slice($_groupByYFields, 1);

                    $this->ExportBody($_slicedGroupByYFields, $_groupYMap[$_fieldNameReference][$_fieldValue]['children'], $_prefixContainer);
                }

                if (isset($this->_resultsContainerY[$_fieldValueContainer['key']])) {
                    $this->ResetColumn(count($this->_sqlGroupByFields) - count($_prefixContainer));

                    if ($this->_currentRow >= $this->_rowCount) {
                        $_columnIndex = $this->NextColumn();
                        $_colSpan = count($_prefixContainer);

                        if ($_colSpan > 1) {

                            $this->MergeAndStyleCells($_columnIndex, $this->GetRecordCount() + 1, $_columnIndex + $_colSpan - 1, $this->GetRecordCount() + 1, self::CELL_GROUP);

                        } else {
                            $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                            $this->StyleCell($_sharedStyle);
                            $this->StyleTitleCell($_sharedStyle);

                            $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                        }

                        $_totalIndex = $this->_currentRow - $this->_rowCount;
                        $_totalExpressions = $this->KQLObject->GetClause('TOTALIZE BY');

                        if ($_totalExpressions && is_array($_totalExpressions) && isset($_totalExpressions[$_totalIndex]) &&
                            _is_array($_totalExpressions[$_totalIndex]) && is_string($_totalExpressions[$_totalIndex][0])) {
                            $_fieldTitle = $_totalExpressions[$_totalIndex][0];
                        } else {
                            $_fieldTitle = $this->Language->Get('totaldefaulttitle');
                        }

                        $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_fieldTitle);

                        for ($i = $_columnIndex; $i < ($_columnIndex + $_colSpan); $i++) {
                            $_sharedStyle = $this->GetCellStyle($i, $this->GetRecordCount() + 1);

                            $_sharedStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            $this->SetCellStyle($i, $this->GetRecordCount() + 1, $_sharedStyle);
                        }

                    } else {
                        foreach ($_prefixContainer as $_exportInfo) {
                            $_columnIndex = $this->NextColumn();

                            if (isset($_exportInfo['rowspan'])) {

                                $this->MergeAndStyleCells($_columnIndex, $this->GetRecordCount() + 1, $_columnIndex, $this->GetRecordCount() + $_exportInfo['rowspan'], self::CELL_GROUP);

                            } else {
                                $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                                $this->StyleCell($_sharedStyle);
                                $this->StyleGroupCell($_sharedStyle);

                                $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                            }

                            $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_exportInfo['value']);

                            if (isset($_exportInfo['extended'])) {
                                if (isset($_exportInfo['extended']['align'])) {
                                    $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                                    $_sharedStyle->getAlignment()->setHorizontal($_exportInfo['extended']['align']);

                                    $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                                }
                            }
                        }
                    }

                    foreach ($this->_resultsContainerY[$_fieldValueContainer['key']] as $_resultContainer) {
                        $_columnIndex = $this->NextColumn();

                        $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                        if (count($_resultContainer) > 0) {
                            $this->SetColumnValue($_resultContainer[0], $_resultContainer[1], $_columnIndex, $_sharedStyle, $this->_currentRow - $this->_rowCount, $_resultContainer[2]);
                        }

                        $this->StyleCell($_sharedStyle);

                        if ($this->_currentRow >= $this->_rowCount) {
                            $this->StyleTitleCell($_sharedStyle);
                        }

                        $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                    }

                    $_prefixContainer = array();

                    $this->IncrementRecordCount();

                    $this->_currentRow++;
                }
            }
        }

        return true;
    }

}

?>
