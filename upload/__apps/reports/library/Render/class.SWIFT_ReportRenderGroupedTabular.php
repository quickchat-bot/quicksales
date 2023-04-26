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
 * The Grouped Tabular Report Renderer
 *
 * @author Varun Shoor
 */
class SWIFT_ReportRenderGroupedTabular extends SWIFT_ReportRender
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

        $_multiGroupFieldNameReferenceList = array();
        foreach ($this->_sqlGroupByMultiFields as $_multiGroupField) {
            $_multiGroupFieldNameReferenceList[$_multiGroupField[1]] = true;
        }

        $_dataContainer = array();

        foreach ($this->KQLParserResult->GetSQL() as $_statementTitle => $_sqlStatement) {
            // Execute the SQL Statement
            if (!$this->ExecuteSQL($_sqlStatement)) {
                continue;
            }

            // Process Array Keys into Titles
            if (!$this->ProcessSQLResultTitle()) {
                continue;
            }

            // Process Custom Field Values
            if (!$this->ProcessFieldValues()) {
                continue;
            }

            $this->AppendOutput('<h3 class="reportsheading">' . str_replace('_', ' &raquo; ', htmlspecialchars($_statementTitle)) . '</h3>');

            $_dataContainer[] = array(str_replace('_', ' » ', $_statementTitle));

            /**
             * ---------------------------------------------
             * Render the charts
             * ---------------------------------------------
             */

            $_internalTitleContainer = array();
            foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {
                if (isset($_multiGroupFieldNameReferenceList[$_titleName])) {
                    continue;
                }
                if (isset($this->_hiddenFields[$_titleName])) {
                    continue;
                }
                if ($this->_internalDisplayValues) {
                    $_internalTitleContainer[] = $_titleContainer[0];
                } else {
                    $_internalTitleContainer[] = $_titleName;
                }
            }
            $this->_internalXContainer[] = $_internalTitleContainer;

            $_rowCount = 0;
            foreach ($this->_sqlResult as $_resultContainer) {
                $_internalRowContainer = array();
                foreach ($_resultContainer as $_columnName => $_columnValue) {
                    if (isset($_multiGroupFieldNameReferenceList[$_columnName])) {
                        continue;
                    }
                    if (isset($this->_hiddenFields[$_columnName])) {
                        continue;
                    }
                    if ($this->_internalDisplayValues) {
                        $_internalRowContainer[] =  $this->ExportColumnValue($_columnName, $_columnValue, $_rowCount - $this->_rowCount, $_resultContainer);
                    } else {
                        $_internalRowContainer[] =  $_columnValue;
                    }
                }
                $this->_internalDataContainer[] = $_internalRowContainer;
                $_rowCount++;
            }

            $_chartObjects = $this->GetCharts();

            if ($_chartObjects) {
                foreach ($_chartObjects as $_chartObject) {
                    $this->AddChart($_chartObject);
                }
            }

            $_chartsOutput = $this->GetChartsOutput();
            $this->AppendOutput($_chartsOutput);

            foreach ($this->_internalXContainer as $_rowContainer) {
                array_push($_dataContainer, $_rowContainer);
            }
            foreach ($this->_internalDataContainer as $_rowContainer) {
                array_push($_dataContainer, $_rowContainer);
            }

            $this->ResetCharts();

            /**
             * ---------------------------------------------
             * Render the titles
             * ---------------------------------------------
             */

            $this->AppendOutput('<div class="reportstablecontainer"><table cellpadding="0" cellspacing="1" border="0" width="100%" class="reportstable">');

            $this->AppendOutput('<thead><tr>');
            foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {
                if (isset($_multiGroupFieldNameReferenceList[$_titleName])) {
                    continue;
                }
                if (isset($this->_hiddenFields[$_titleName])) {
                    continue;
                }

                $this->RenderTitleColumn($_titleName, $_titleContainer[0], $_titleContainer[2]);
                $this->IncrementRecordCount();
            }
            $this->AppendOutput('</tr></thead>');

            // Render the body
            $_rowCount = 0;
            $_tableTag = 'tbody';
            $this->AppendOutput('<' . $_tableTag . '>');
            foreach ($this->_sqlResult as $_resultContainer) {
                if ($_rowCount == $this->_rowCount) {
                    $this->AppendOutput('</' . $_tableTag . '>');
                    $_tableTag = 'tfoot';
                    $this->AppendOutput('<' . $_tableTag . '>');
                }

                $this->AppendOutput('<tr>');
                foreach ($_resultContainer as $_columnName => $_columnValue) {
                    if (isset($_multiGroupFieldNameReferenceList[$_columnName])) {
                        continue;
                    }
                    if (isset($this->_hiddenFields[$_columnName])) {
                        continue;
                    }

                    $_extendedInfo = '';
                    if (isset($this->_renderFieldTitleExtendedInfoContainer[$_columnName])) {
                        $_extendedInfo = $this->_renderFieldTitleExtendedInfoContainer[$_columnName];
                    }

                    $this->AppendOutput('<td' . $_extendedInfo . '>' . $this->RenderColumnValue($_columnName, $_columnValue, $_rowCount - $this->_rowCount, $_resultContainer) . '</td>');
                }
                $this->IncrementRecordCount();
                $this->AppendOutput('</tr>');
                $_rowCount++;
            }

            $this->AppendOutput('</' . $_tableTag . '>');

            $this->AppendOutput('</table></div><br /><hr class="reportsline" /><br />');
        }

        $this->_internalDataContainer = $_dataContainer;

        return true;
    }
}
?>
