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

use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL2\SWIFT_KQL2;

/**
 * The Tabular Report Renderer
 *
 * @author Varun Shoor
 */
class SWIFT_ReportRenderTabular extends SWIFT_ReportRender
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

        $_internalTitleContainer = array();
        $this->AppendOutput('<thead><tr>');
        foreach ($this->_sqlParsedTitles as $_titleName => $_titleContainer) {
            if (isset($this->_hiddenFields[$_titleName])) {
                continue;
            }

            $this->RenderTitleColumn($_titleName, $_titleContainer[0], $_titleContainer[2]);

            if ($this->_internalDisplayValues) {
                $_internalTitleContainer[] = $_titleContainer[0];
            } else {
                $_internalTitleContainer[] = $_titleName;
            }
            $this->IncrementRecordCount();
        }
        $this->_internalXContainer[] = $_internalTitleContainer;
        $this->AppendOutput('</tr></thead>');

        // Render the body
        $_rowCount = 0;
        $_tableTag = 'tbody';
        $this->AppendOutput('<' . $_tableTag . '>');
        foreach ($this->_sqlResult as $_resultContainer) {
            $_internalRowContainer = array();

            // Close <tbody> and start <tfoot>
            if ($_rowCount == $this->_rowCount) {
                $this->AppendOutput('</' . $_tableTag . '>');
                $_tableTag = 'tfoot';
                $this->AppendOutput('<' . $_tableTag . '>');
            }

            $this->AppendOutput('<tr>');
            foreach ($_resultContainer as $_columnName => $_columnValue) {
                if (isset($this->_hiddenFields[$_columnName])) {
                    continue;
                }

                $_extendedInfo = '';
                if (isset($this->_renderFieldTitleExtendedInfoContainer[$_columnName])) {
                    $_extendedInfo = $this->_renderFieldTitleExtendedInfoContainer[$_columnName];
                }

                /**
                 * Bug Fix - Mansi Wason <mansi.wason@kayako.com>
                 *
                 * SWIFT-4118 'Time to Resolve' is shown in seconds when report is exported
                 *
                 * Comments: Fixed for exporting in case of CSV.
                 */
                if ($this->_internalDisplayValues) {
                    $_internalRowContainer[] = $this->RenderColumnValue($_columnName, $_columnValue, $_rowCount - $this->_rowCount, $_resultContainer);
                } else {
                    $_internalRowContainer[] = $_columnValue;
                }

                $this->AppendOutput('<td' . $_extendedInfo . '>' . $this->RenderColumnValue($_columnName, $_columnValue, $_rowCount - $this->_rowCount, $_resultContainer) . '</td>');
            }
            $this->_internalDataContainer[] = $_internalRowContainer;
            $this->IncrementRecordCount();
            $this->AppendOutput('</tr>');
            $_rowCount++;
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
?>
