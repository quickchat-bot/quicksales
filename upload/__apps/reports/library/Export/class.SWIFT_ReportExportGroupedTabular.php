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
 * The Grouped Tabular Report Exporter
 *
 * @author Andriy Lesyuk
 */
class SWIFT_ReportExportGroupedTabular extends SWIFT_ReportExport
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

        $_multiGroupFieldNameReferenceList = array();

        foreach ($this->_sqlGroupByMultiFields as $_multiGroupField) {
            $_multiGroupFieldNameReferenceList[$_multiGroupField[1]] = true;
        }

        foreach ($this->KQLParserResult->GetSQL() as $_statementTitle => $_sqlStatement) {

            // Execute the SQL Statement
            if (!$this->ExecuteSQL($_sqlStatement)) {
                continue;
            }

            // Process Array Keys into Titles
            if (!$this->ProcessSQLResultTitle()) {
                continue;
            }

            // Process Field Values
            if (!$this->ProcessFieldValues()) {
                continue;
            }

            /**
             * ---------------------------------------------
             * Export statement title
             * ---------------------------------------------
             */

            $this->ResetColumn();

            $_columnIndex = $this->NextColumn();

            $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, str_replace('_', ' » ', $_statementTitle));

            $this->MergeAndStyleCells($_columnIndex, $this->GetRecordCount() + 1, $_columnIndex + count($_multiGroupFieldNameReferenceList) - 1, $this->GetRecordCount() + 1, self::CELL_STATEMENT);

            $this->IncrementRecordCount();

            /**
             * ---------------------------------------------
             * Export the titles
             * ---------------------------------------------
             */

            $this->ResetColumn();

            foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {
                if (isset($_multiGroupFieldNameReferenceList[$_titleName])) {
                    continue;
                }
                if (isset($this->_hiddenFields[$_titleName])) {
                    continue;
                }

                $this->GenerateTitleCell($this->NextColumn(), $_titleName, $_titleContainer[0], $_titleContainer[2]);
            }

            $this->IncrementRecordCount();

            /**
             * ---------------------------------------------
             * Export body
             * ---------------------------------------------
             */

            $_rowCount = 0;

            foreach ($this->_sqlResult as $_resultContainer) {
                $this->ResetColumn();

                foreach ($_resultContainer as $_columnName => $_columnValue) {
                    if (isset($_multiGroupFieldNameReferenceList[$_columnName])) {
                        continue;
                    }
                    if (isset($this->_hiddenFields[$_columnName])) {
                        continue;
                    }

                    $_columnIndex = $this->NextColumn();

                    $_sharedStyle = $this->GetCellStyle($_columnIndex, $this->GetRecordCount() + 1);

                    $this->SetColumnValue($_columnName, $_columnValue, $_columnIndex, $_sharedStyle, $_rowCount - $this->_rowCount, $_resultContainer);

                    $this->StyleCell($_sharedStyle);

                    if ($_rowCount >= $this->_rowCount) {
                        $this->StyleTitleCell($_sharedStyle);
                    }

                    if (isset($this->_exportFieldTitleExtendedInfoContainer[$_columnName])) {
                        $_sharedStyle->getAlignment()->setHorizontal($this->_exportFieldTitleExtendedInfoContainer[$_columnName]);
                    }

                    $this->SetCellStyle($_columnIndex, $this->GetRecordCount() + 1, $_sharedStyle);
                }

                $_rowCount++;

                $this->IncrementRecordCount();
            }

            $this->IncrementRecordCount();

        }

        return true;
    }

}
