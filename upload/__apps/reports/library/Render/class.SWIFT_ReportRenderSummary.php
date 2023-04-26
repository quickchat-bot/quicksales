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
 * The Summary Report Renderer
 *
 * @author Varun Shoor
 */
class SWIFT_ReportRenderSummary extends SWIFT_ReportRender
{

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

        $this->AppendOutput('<div class="reportstablecontainer"><table cellpadding="0" cellspacing="1" border="0" width="100%" class="reportstable">');

        /**
         * ---------------------------------------------
         * Render the titles
         * ---------------------------------------------
         */

        $this->AppendOutput('<thead><tr>');

        // First render the primary group by fields
        $_groupByFieldReferenceNameList = array();

        $_internalRowContainer = array();

        foreach ($this->_sqlGroupByFields as $_groupByField) {
            $_fieldName = $_groupByField[0];
            $_fieldReference = $_groupByField[1];

            $_groupByFieldReferenceNameList[] = $_fieldReference;

            // We need to make sure we have a reference for this field
            if (!isset($this->_sqlParsedTitles[$_fieldReference])) {
                throw new SWIFT_Exception('Group by field reference not found in result');
            }

            // Add and render this up before any other result fields
            $_parsedTitleReference = $this->_sqlParsedTitles[$_fieldReference];

            if ($this->_internalDisplayValues) {
                $_internalRowContainer[] = $_parsedTitleReference[0];
            }

            $this->RenderTitleColumn($_fieldReference, $_parsedTitleReference[0], $_parsedTitleReference[2]);
            $this->IncrementRecordCount();
        }

        foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {
            if (in_array($_titleName, $_groupByFieldReferenceNameList)) {
                continue;
            }
            if (isset($this->_hiddenFields[$_titleName])) {
                continue;
            }

            if ($this->_internalDisplayValues) {
                $_internalRowContainer[] = $_titleContainer[0];
            } else {
                $_internalRowContainer[] = $_titleName;
            }

            $this->RenderTitleColumn($_titleName, $_titleContainer[0], $_titleContainer[2]);
            $this->IncrementRecordCount();
        }

        $this->_internalXContainer[] = $_internalRowContainer;

        $this->AppendOutput('</tr></thead>');

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
         * Render the individual rows
         * ---------------------------------------------
         */
        $_rowCount = 0;
        $_renderRowMap = array();
        $_renderRowReference = false;

        $_tableTag = 'tbody';
        $this->AppendOutput('<' . $_tableTag . '>');
        foreach ($_finalSQLResultContainer as $_index => $_resultContainer) {
            $_renderGroupByField = $_renderCountMap = false;

            $_internalColContainer = array();
            $_internalDataContainer = array();

            // Close <tbody> and start <tfoot>
            if ($_rowCount == $this->_rowCount) {
                $this->AppendOutput('</' . $_tableTag . '>');
                $_tableTag = 'tfoot';
                $this->AppendOutput('<' . $_tableTag . '>');
            }

            $this->AppendOutput('<tr>');

            if ($_rowCount < $this->_rowCount) {

                foreach ($this->_sqlGroupByFields as $_groupByField) {
                    $_fieldName = $_groupByField[0];
                    $_fieldReferenceName = $_groupByField[1];
                    $_fieldValue = $_resultContainer[$_fieldReferenceName];

                    if ($_renderGroupByField === false) {
                        $_renderGroupByField = $_fieldReferenceName . ':' . $_fieldValue;
                    } else {
                        $_renderGroupByField .= '_' . $_fieldReferenceName . ':' . $_fieldValue;
                    }

                    if ($_renderCountMap === false) {
                        $_fieldReferenceNameNew = str_replace(['[', ']'], ['{{', '}}'], $_fieldReferenceName);
                        $_fieldValueNew = str_replace(['[', ']'], ['{{', '}}'], $_fieldValue);
                        $_renderCountMap = '[' . $_fieldReferenceNameNew . '][' . $_fieldValueNew . ']';
                    } else {
                        $_fieldReferenceNameNew = str_replace(['[', ']'], ['{{', '}}'], $_fieldReferenceName);
                        $_fieldValueNew = str_replace(['[', ']'], ['{{', '}}'], $_fieldValue);
                        $_renderCountMap .= '[children][' . $_fieldReferenceNameNew . '][' . $_fieldValueNew . ']';
                    }

                    $_renderGroupByFieldHash = md5($_renderGroupByField);

                    if ($this->_internalDisplayValues) {
                        $_internalColContainer[] = $this->ExportColumnValue($_fieldReferenceName, $_fieldValue, $_rowCount - $this->_rowCount, $_resultContainer);
                    } else {
                        $_internalColContainer[] = $_fieldValue;
                    }

                    if (isset($_renderRowMap[$_renderGroupByFieldHash])) {
                        continue;
                    }

                    $_rowSpan = VariableArray($_summaryCountMap, $_renderCountMap . '[count]');

                    $_extendedInfo = '';
                    if (isset($this->_renderFieldTitleExtendedInfoContainer[$_fieldReferenceName])) {
                        $_extendedInfo = $this->_renderFieldTitleExtendedInfoContainer[$_fieldReferenceName];
                    }

                    $this->AppendOutput('<td class="reporttdytitle" rowspan="' . (int) ($_rowSpan) . '"' . $_extendedInfo . '>' . $this->RenderColumnValue($_fieldReferenceName, $_fieldValue, $_rowCount - $this->_rowCount, $_resultContainer) . '</td>');

                    $_renderRowMap[$_renderGroupByFieldHash] = true;
                }

            } else {
                $_totalIndex = $_rowCount - $this->_rowCount;
                $_totalExpressions = $this->KQLObject->GetClause('TOTALIZE BY');
                if ($_totalExpressions && is_array($_totalExpressions) && isset($_totalExpressions[$_totalIndex]) &&
                    _is_array($_totalExpressions[$_totalIndex]) && is_string($_totalExpressions[$_totalIndex][0])) {
                    $_totalTitle = $_totalExpressions[$_totalIndex][0];
                } else {
                    $_totalTitle = $this->Language->Get('totaldefaulttitle');
                }

                foreach ($this->_sqlGroupByFields as $_groupByField) {
                    $_internalColContainer[] = $_totalTitle;
                }

                $this->AppendOutput('<td class="reporttdytitle" colspan="' . count($this->_sqlGroupByFields) . '" align="center">' . htmlspecialchars($_totalTitle) . '</td>');
            }

            foreach ($_resultContainer as $_fieldReferenceName => $_fieldValue) {
                // If we have grouped on this field, we dont render it
                if (in_array($_fieldReferenceName, $_groupByFieldReferenceNameList)) {
                    continue;
                }
                if (isset($this->_hiddenFields[$_fieldReferenceName])) {
                    continue;
                }

                $_extendedInfo = '';
                if (isset($this->_renderFieldTitleExtendedInfoContainer[$_fieldReferenceName])) {
                    $_extendedInfo = $this->_renderFieldTitleExtendedInfoContainer[$_fieldReferenceName];
                }

                if ($this->_internalDisplayValues) {
                    $_internalDataContainer[] = $this->ExportColumnValue($_fieldReferenceName, $_fieldValue, $_rowCount - $this->_rowCount, $_resultContainer);
                } else {
                    $_internalDataContainer[] = $_fieldValue;
                }

                $this->AppendOutput('<td' . $_extendedInfo . '>' . $this->RenderColumnValue($_fieldReferenceName, $_fieldValue, $_rowCount - $this->_rowCount, $_resultContainer) . '</td>');
            }

            $_rowCount++;

            $this->_internalYContainer[] = $_internalColContainer;
            $this->_internalDataContainer[] = $_internalDataContainer;

            $this->IncrementRecordCount();
            $this->AppendOutput('</tr>');
        }
        $this->AppendOutput('</' . $_tableTag . '>');


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
}
