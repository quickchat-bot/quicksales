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

use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL2\SWIFT_KQL2;

/**
 * The Summary Report Exporter
 *
 * @author Andriy Lesyuk
 */
class SWIFT_ReportExportSummary extends SWIFT_ReportExport
{

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
     * Generate the Report Content
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

        // Execute the SQL Statement
        if (!$this->ExecuteSQL()) {
            return false;
        }

        // Process Array Keys into Titles
        if (!$this->ProcessSQLResultTitle()) {
            return false;
        }

        // Process Custom Field Values
        if (!$this->ProcessFieldValues()) {
            return false;
        }

        // Used at the beginning of each row
        $_groupByFieldReferenceNameList = array();

        /**
         * ---------------------------------------------
         * Export the titles
         * ---------------------------------------------
         */

        $this->ResetColumn();

        // Grouped by fields first
        foreach ($this->_sqlGroupByFields as $_groupByField) {
            $_fieldName = $_groupByField[0];
            $_fieldReference = $_groupByField[1];

            // Save for using later
            $_groupByFieldReferenceNameList[] = $_fieldReference;

            // We need to make sure we have a reference for this field
            if (!isset($this->_sqlParsedTitles[$_fieldReference])) {
                throw new SWIFT_Exception('Group by field reference not found in result');
            }

            $_parsedTitleReference = $this->_sqlParsedTitles[$_fieldReference];
            $this->GenerateTitleCell($this->NextColumn(), $_fieldReference, $_parsedTitleReference[0], $_parsedTitleReference[2]);
        }

        foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {

            // Skip grouped by fields
            if (in_array($_titleName, $_groupByFieldReferenceNameList)) {
                continue;
            }

            // Skip hidden fields
            if (isset($this->_hiddenFields[$_titleName])) {
                continue;
            }

            $this->GenerateTitleCell($this->NextColumn(), $_titleName, $_titleContainer[0], $_titleContainer[2]);
        }

        $this->_workSheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($this->GetRecordCount() + 1, $this->GetRecordCount() + 1);

        $this->IncrementRecordCount();

        /**
         * ---------------------------------------------
         * Build a count map
         * ---------------------------------------------
         */

        $_summaryCountMap = $this->BuildSummaryCountMap();

        /**
         * ---------------------------------------------
         * Sort the SQL result
         * ---------------------------------------------
         */

        $_finalSQLResultContainer = $this->SortSummarySQLResult();

        /**
         * ---------------------------------------------
         * Export the individual rows
         * ---------------------------------------------
         */

        $_rowCount = 0;
        $_exportRowMap = array();

        foreach ($_finalSQLResultContainer as $_index => $_resultContainer) {
            $_exportGroupByField = false;
            $_exportCountMap = false;

            $this->ResetColumn();

            if ($_rowCount < $this->_rowCount) {

                // Group by fields first
                foreach ($this->_sqlGroupByFields as $_groupByField) {
                    $_fieldName = $_groupByField[0];
                    $_fieldReferenceName = $_groupByField[1];
                    $_fieldValue = $_resultContainer[$_fieldReferenceName];

                    if ($_exportGroupByField === false) {
                        $_exportGroupByField = $_fieldReferenceName . ':' . $_fieldValue;
                    } else {
                        $_exportGroupByField .= '_' . $_fieldReferenceName . ':' . $_fieldValue;
                    }

                    if ($_exportCountMap === false) {
                        $_exportCountMap = '[' . $_fieldReferenceName . '][' . $_fieldValue . ']';
                    } else {
                        $_exportCountMap .= '[children][' . $_fieldReferenceName . '][' . $_fieldValue . ']';
                    }

                    $_columnIndex = $this->NextColumn();

                    $_exportGroupByFieldHash = md5($_exportGroupByField);

                    if (isset($_exportRowMap[$_exportGroupByFieldHash])) {
                        continue;
                    }

                    $_rowSpan = (int) (VariableArray($_summaryCountMap, $_exportCountMap . '[count]'));
                    if ($_rowSpan > 1) {

                        $this->MergeAndStyleCells($_columnIndex, $this->GetRecordCount() + 1, $_columnIndex, $this->GetRecordCount() + $_rowSpan, self::CELL_GROUP);

                        $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);
                    } else {
                        $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                        $this->StyleCell($_sharedStyle);
                        $this->StyleGroupCell($_sharedStyle);
                    }

                    if (isset($this->_exportFieldTitleExtendedInfoContainer[$_fieldReferenceName])) {
                        $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                        $_sharedStyle->getAlignment()->setHorizontal($this->_exportFieldTitleExtendedInfoContainer[$_fieldReferenceName]);
                    }

                    $this->SetColumnValue($_fieldReferenceName, $_fieldValue, $_columnIndex, $_sharedStyle, $_rowCount - $this->_rowCount, $_resultContainer);

                    $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);

                    $_exportRowMap[$_exportGroupByFieldHash] = true;
                }

            } else {
                $_columnIndex = $this->NextColumn();
                $_colSpan = count($this->_sqlGroupByFields);

                if ($_colSpan > 1) {
                    $this->MergeAndStyleCells($_columnIndex, $this->GetRecordCount() + 1, $_columnIndex + $_colSpan - 1, $this->GetRecordCount() + 1, self::CELL_GROUP);
                }

                $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                $this->StyleCell($_sharedStyle);
                $this->StyleTitleCell($_sharedStyle);

                $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);

                $_totalIndex = $_rowCount - $this->_rowCount;
                $_totalExpressions = $this->KQLObject->GetClause('TOTALIZE BY');

                if ($_totalExpressions && is_array($_totalExpressions) && isset($_totalExpressions[$_totalIndex]) &&
                    _is_array($_totalExpressions[$_totalIndex]) && is_string($_totalExpressions[$_totalIndex][0])) {
                    $_totalTitle = $_totalExpressions[$_totalIndex][0];
                } else {
                    $_totalTitle = $this->Language->Get('totaldefaulttitle');
                }

                $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_totalTitle);

                for ($i = $_columnIndex; $i < ($_columnIndex + $_colSpan); $i++) {
                    $_sharedStyle = $this->GetCellStyle($i, $this->GetRecordCount() + 1);

                    $_sharedStyle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $this->SetCellStyle($i, $this->GetRecordCount() + 1, $_sharedStyle);
                }
            }

            foreach ($_resultContainer as $_fieldReferenceName => $_fieldValue) {

                // If we have grouped on this field, we dont export it
                if (in_array($_fieldReferenceName, $_groupByFieldReferenceNameList)) {
                    continue;
                }

                // Skip hidden fields
                if (isset($this->_hiddenFields[$_fieldReferenceName])) {
                    continue;
                }

                $_columnIndex = $this->NextColumn();

                $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                if (isset($this->_exportFieldTitleExtendedInfoContainer[$_fieldReferenceName])) {
                    $_sharedStyle->getAlignment()->setHorizontal($this->_exportFieldTitleExtendedInfoContainer[$_fieldReferenceName]);
                }

                $this->SetColumnValue($_fieldReferenceName, $_fieldValue, $_columnIndex, $_sharedStyle, $_rowCount - $this->_rowCount, $_resultContainer);

                $this->StyleCell($_sharedStyle);

                if ($_rowCount >= $this->_rowCount) {
                    $this->StyleTitleCell($_sharedStyle);
                }

                $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
            }

            $_rowCount++;

            $this->IncrementRecordCount();
        }

        return true;
    }

}

?>
