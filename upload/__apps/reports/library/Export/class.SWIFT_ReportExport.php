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

use Base\Library\KQL\SWIFT_KQLParser;
use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\KQL2\SWIFT_KQL2;

/**
 * The Report Exporter
 *
 * @author Andriy Lesyuk
 */
abstract class SWIFT_ReportExport extends SWIFT_ReportBase
{
    // Cell types
    const CELL_TITLE = 'title';
    const CELL_GROUP = 'group';
    const CELL_STATEMENT = 'statement';
    const CELL_COMMON = 'common';

    const EXCEL_DEFAULT_BORDER_STYLE = PHPExcel_Style_Border::BORDER_HAIR;
    const EXCEL_DEFAULT_BORDER_COLOR = 'FFA1A1A1';

    const EXCEL_DEFAULT_TITLE_BOLD = true;
//    const EXCEL_DEFAULT_TITLE_GRADIENT_TYPE = PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR;
    const EXCEL_DEFAULT_TITLE_GRADIENT_TYPE = PHPExcel_Style_Fill::FILL_SOLID;
    const EXCEL_DEFAULT_TITLE_START_COLOR = 'FFF7F6F6';
//    const EXCEL_DEFAULT_TITLE_END_COLOR = 'FFE4DCD1';

    const EXCEL_DEFAULT_GROUP_GRADIENT_TYPE = PHPExcel_Style_Fill::FILL_SOLID;
    const EXCEL_DEFAULT_GROUP_START_COLOR = 'FFFDFCFC';

    const EXCEL_DEFAULT_STATEMENT_BOLD = true;
    const EXCEL_DEFAULT_STATEMENT_FONTSZIE = 12;

    const EXCEL_DEFAULT_FONT = 'Verdana';
    const EXCEL_DEFAULT_FONTSZIE = 11;

    const EXCEL_MARGIN_TOP = 0.75;
    const EXCEL_MARGIN_LEFT = 1;
    const EXCEL_MARGIN_RIGHT = 1;
    const EXCEL_MARGIN_BOTTOM = 0.75;

    protected $_exportDocument = false;
    protected $_exportFormat = false;
    protected $_workSheet = false;
    protected $_defaultFont = false;

    protected $_columnIndex = 0;
    protected $_documentWidth = 0;

    protected $_exportFieldTitleExtendedInfoContainer = array();

    protected $_reportExcelAlignmentMap = array(
        'left' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        'right' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        'center' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'justify' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);

    private static $_exportFormatContainer = array(
        self::EXPORT_EXCEL => array('formatexcel2007', 'menu_xls.png', 'zip'), /* Last item - PHP extension required */
        self::EXPORT_EXCEL5 => array('formatexcel5', 'menu_xls.png'),
//        self::EXPORT_PDF => array('formatpdf', 'menu_pdf.png'),
        self::EXPORT_CSV => array('formatcsv', 'menu_csv.png'),
        self::EXPORT_HTML => array('formathtml', 'menu_html.png'));

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

        $this->_exportDocument = new PHPExcel();
        $this->_workSheet = $this->_exportDocument->getActiveSheet();

        $this->_defaultFont = new PHPExcel_Style_Font();

        $this->_defaultFont->setName(self::EXCEL_DEFAULT_FONT);
        $this->_defaultFont->setSize(self::EXCEL_DEFAULT_FONTSZIE);
    }

    /**
     * Export a Report
     *
     * @author Andriy Lesyuk
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @param string $_exportFormat
     * @return SWIFT_ReportExport The Report Export Object
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Process(SWIFT_Report $_SWIFT_ReportObject, $_exportFormat = self::EXPORT_EXCEL)
    {
        if (!$_SWIFT_ReportObject instanceof SWIFT_Report || !$_SWIFT_ReportObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_KQLParser = new SWIFT_KQLParser();

        ob_start();
        $_SWIFT_KQLParserResultObject = $_SWIFT_KQLParser->ParseStatement($_SWIFT_ReportObject->GetProperty('kql'), $_SWIFT_ReportObject->GetProperty('basetablenametext'));
        ob_end_clean();

        $_SWIFT_ReportExportObject = false;

        $_reportType = $_SWIFT_KQLParserResultObject->GetResultType();
        switch ($_reportType) {
            case SWIFT_KQLParserResult::RESULTTYPE_TABULAR:
                $_SWIFT_ReportExportObject = new SWIFT_ReportExportTabular($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);
                break;

            case SWIFT_KQLParserResult::RESULTTYPE_GROUPEDTABULAR:
                $_SWIFT_ReportExportObject = new SWIFT_ReportExportGroupedTabular($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);
                break;

            case SWIFT_KQLParserResult::RESULTTYPE_SUMMARY:
                $_SWIFT_ReportExportObject = new SWIFT_ReportExportSummary($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);
                break;


            case SWIFT_KQLParserResult::RESULTTYPE_MATRIX:
                $_SWIFT_ReportExportObject = new SWIFT_ReportExportMatrix($_SWIFT_KQLParser->GetKQL(), $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);
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

        $_SWIFT_ReportExportObject->SetAliasMap($_aliasMap);
        $_SWIFT_ReportExportObject->SetFunctionMap($_functionMap);
        $_SWIFT_ReportExportObject->SetOriginalAliasMap($_originalAliases);
        $_SWIFT_ReportExportObject->SetHiddenFields($_hiddenFields);
        $_SWIFT_ReportExportObject->SetCustomFields($_customFields);

        $_SWIFT_ReportExportObject->SetSessionTimeZone();

        $_SWIFT_ReportExportObject->Prepare($_exportFormat);

        $_SWIFT_ReportExportObject->RestoreSessionTimeZone();

        return $_SWIFT_ReportExportObject;
    }

    /**
     * Prepare the Report Document
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat Export Format
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Prepare($_exportFormat = self::EXPORT_EXCEL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->InitializeDocument($_exportFormat);

        $this->Generate($_exportFormat);

        $this->FinalizeDocument($_exportFormat);

        return true;
    }

    /**
     * Dispatch the Report to the Browser
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat Export Format
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchFile($_exportFormat = self::EXPORT_EXCEL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            @ini_set('zlib.output_compression', 'Off');
        }

        $_fileName = $this->GetFilename();

        if (isset($this->_exportFormatMap[$_exportFormat])) {
            header('Content-Type: ' . $this->_exportFormatMap[$_exportFormat][1]);
            header('Content-Disposition: attachment; filename="' . $_fileName . '.' . $this->_exportFormatMap[$_exportFormat][2] . '"');
            header("Content-Transfer-Encoding: binary");
            header('Cache-Control: max-age=0');

            $_documentWriter = PHPExcel_IOFactory::createWriter($this->_exportDocument, $this->_exportFormatMap[$_exportFormat][0]);
            $_documentWriter->save('php://output');
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return true;
    }

    /**
     * Gets the Report File Content
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat Export Format
     * @return array The Report File Properties
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFile($_exportFormat = self::EXPORT_EXCEL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fileContainer = array();

        if (isset($this->_exportFormatMap[$_exportFormat])) {
            $_fileName = $this->GetFilename();

            $_fileContainer['mime-type'] = $this->_exportFormatMap[$_exportFormat][1];
            $_fileContainer['filename'] = $_fileName . '.' . $this->_exportFormatMap[$_exportFormat][2];

            $_tempFileName = SWIFT_FileManager::DEFAULT_TEMP_PREFIX . BuildHash();
            $_tempFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_tempFileName;

            $_documentWriter = PHPExcel_IOFactory::createWriter($this->_exportDocument, $this->_exportFormatMap[$_exportFormat][0]);
            $_documentWriter->save($_tempFilePath);

            $_fileContainer['content'] = file_get_contents($_tempFilePath);

            @unlink($_tempFilePath);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_fileContainer;
    }

    /**
     * Sets Spreadsheet's Metadata, Styles Etc.
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat Export Format
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function InitializeDocument($_exportFormat = self::EXPORT_EXCEL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_exportFormat = $_exportFormat;

        $this->_exportDocument->getProperties()->setCreator('QuickSupport ' . SWIFT_PRODUCT . ' v' . SWIFT_VERSION);
        $this->_exportDocument->getProperties()->setLastModifiedBy('QuickSupport ' . SWIFT_PRODUCT . ' v' . SWIFT_VERSION);
        $this->_exportDocument->getProperties()->setTitle($this->Report->GetProperty('title'));
        $this->_exportDocument->getProperties()->setSubject($this->Report->GetProperty('title'));

        $_reportCategoryCache = $this->Cache->Get('reportcategorycache');
        $this->_exportDocument->getProperties()->setCategory($_reportCategoryCache[$this->Report->GetProperty('reportcategoryid')]['title']);

        $this->_exportDocument->getDefaultStyle()->getFont()->setName(self::EXCEL_DEFAULT_FONT);
        $this->_exportDocument->getDefaultStyle()->getFont()->setSize(self::EXCEL_DEFAULT_FONTSZIE);

        return true;
    }

    /**
     * Sets Other Useful Properties
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat Export Format
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function FinalizeDocument($_exportFormat = self::EXPORT_EXCEL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (count($this->_sqlGroupByFields) > 0) {
            $this->_workSheet->getPageSetup()->setColumnsToRepeatAtLeftByStartAndEnd(PHPExcel_Cell::stringFromColumnIndex(0), PHPExcel_Cell::stringFromColumnIndex(count($this->_sqlGroupByFields) - 1));
        }

        $this->_workSheet->getHeaderFooter()->setOddHeader('&C' . $this->Report->GetProperty('title'));
        $this->_workSheet->getHeaderFooter()->setEvenHeader('&C' . $this->Report->GetProperty('title'));

        $_currentDate = strftime('%d %B %Y %H:%M');

        $this->_workSheet->getHeaderFooter()->setOddFooter('&L' . $_currentDate . '&R&P of &N');
        $this->_workSheet->getHeaderFooter()->setEvenFooter('&L' . $_currentDate . '&R&P of &N');

        for ($_columnIndex = 0; $_columnIndex <= PHPExcel_Cell::columnIndexFromString($this->_workSheet->getHighestColumn()) - 1; $_columnIndex++) {
            $this->_workSheet->getColumnDimensionByColumn($_columnIndex)->setAutoSize(true);
        }

        if (!$this->IsWidthCalculated()) {
            $this->CalculateWidth();
        }

        if ($this->_documentWidth > PHPExcel_Shared_Font::centimeterSizeToPixels(21 - self::EXCEL_MARGIN_LEFT - self::EXCEL_MARGIN_RIGHT)) {
            $this->_workSheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        }

        $this->_workSheet->getPageMargins()->setTop(self::EXCEL_MARGIN_TOP);
        $this->_workSheet->getPageMargins()->setLeft(self::EXCEL_MARGIN_LEFT);
        $this->_workSheet->getPageMargins()->setRight(self::EXCEL_MARGIN_RIGHT);
        $this->_workSheet->getPageMargins()->setBottom(self::EXCEL_MARGIN_BOTTOM);

        return true;
    }

    /**
     * Checks if Width Has Been Calculated
     *
     * @author Andriy Lesyuk
     * @return bool "true" if Width Has Been Calculated, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function IsWidthCalculated()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return ($this->_documentWidth > 0);
    }

    /**
     * Calculate Document Width
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CalculateWidth()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_documentWidth = 0;

        $this->_workSheet->calculateColumnWidths();

        foreach ($this->_workSheet->getColumnDimensions() as $_columnDimension) {
            $this->_documentWidth += PHPExcel_Shared_Drawing::cellDimensionToPixels($_columnDimension->getWidth(), $this->_defaultFont);
        }

        return true;
    }

    /**
     * Generate the Report Content
     *
     * @author Andriy Lesyuk
     * @param string $_exportFormat Export Format
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Generate($_exportFormat = self::EXPORT_EXCEL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Get Cell Style Object
     *
     * @author Andriy Lesyuk
     * @param int $_columnIndex
     * @param int $_rowIndex
     * @return CellStyleMock The Cell Style Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetCellStyle($_columnIndex, $_rowIndex)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_cellStyle = $this->_workSheet->getStyleByColumnAndRow($_columnIndex, $_rowIndex);

        return clone $_cellStyle;
    }

    /**
     * Sets Cell Style
     *
     * @author Andriy Lesyuk
     * @param int $_columnIndex
     * @param int $_rowIndex
     * @param CellStyleMock $_sharedStyle The Cell Style Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetCellStyle($_columnIndex, $_rowIndex, $_sharedStyle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_workSheet->setSharedStyle($_sharedStyle, PHPExcel_Cell::stringFromColumnIndex($_columnIndex) . $_rowIndex);

        return true;
    }

    /**
     * Default Style for Cell
     *
     * @author Andriy Lesyuk
     * @param CellStyleMock $_sharedStyle The Cell Style
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected  function StyleCell($_sharedStyle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sharedStyle->getBorders()->getTop()->setBorderStyle(self::EXCEL_DEFAULT_BORDER_STYLE);
        $_sharedStyle->getBorders()->getRight()->setBorderStyle(self::EXCEL_DEFAULT_BORDER_STYLE);
        $_sharedStyle->getBorders()->getBottom()->setBorderStyle(self::EXCEL_DEFAULT_BORDER_STYLE);
        $_sharedStyle->getBorders()->getLeft()->setBorderStyle(self::EXCEL_DEFAULT_BORDER_STYLE);

        $_borderColor = new PHPExcel_Style_Color(self::EXCEL_DEFAULT_BORDER_COLOR);

        $_sharedStyle->getBorders()->getTop()->setColor($_borderColor);
        $_sharedStyle->getBorders()->getRight()->setColor($_borderColor);
        $_sharedStyle->getBorders()->getBottom()->setColor($_borderColor);
        $_sharedStyle->getBorders()->getLeft()->setColor($_borderColor);

        return true;
    }

    /**
     * Style the Title (Heading) Cell
     *
     * @author Andriy Lesyuk
     * @param CellStyleMock $_sharedStyle
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function StyleTitleCell($_sharedStyle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sharedStyle->getFont()->setBold(self::EXCEL_DEFAULT_TITLE_BOLD);

        if ($this->_exportFormat != self::EXPORT_EXCEL5) {
            $_sharedStyle->getFill()->setFillType(self::EXCEL_DEFAULT_TITLE_GRADIENT_TYPE);
        } else {
            $_sharedStyle->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        }

        $_sharedStyle->getFill()->setStartColor(new PHPExcel_Style_Color(self::EXCEL_DEFAULT_TITLE_START_COLOR));
//        $_sharedStyle->getFill()->setEndColor(new PHPExcel_Style_Color(self::EXCEL_DEFAULT_TITLE_END_COLOR));

        return true;
    }

    /**
     * Style the Group (Side) Cell
     *
     * @author Andriy Lesyuk
     * @param CellStyleMock $_sharedStyle
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function StyleGroupCell($_sharedStyle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sharedStyle->getFill()->setFillType(self::EXCEL_DEFAULT_GROUP_GRADIENT_TYPE);
        $_sharedStyle->getFill()->setStartColor(new PHPExcel_Style_Color(self::EXCEL_DEFAULT_GROUP_START_COLOR));

        return true;
    }

    /**
     * Style the Statement Cell (Grouped Tabular Report)
     *
     * @author Andriy Lesyuk
     * @param CellStyleMock $_sharedStyle
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function StyleStatementCell($_sharedStyle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_sharedStyle->getFont()->setBold(self::EXCEL_DEFAULT_STATEMENT_BOLD);
        $_sharedStyle->getFont()->setSize(self::EXCEL_DEFAULT_STATEMENT_FONTSZIE);

        return true;
    }

    /**
     * Generate the Field Title Cell
     *
     * @author Andriy Lesyuk
     * @param int $_columnIndex
     * @param string $_fieldReferenceName
     * @param string $_fieldTitle
     * @param array $_fieldContainer The KQL Schema Field Reference Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GenerateTitleCell($_columnIndex, $_fieldReferenceName, $_fieldTitle, $_fieldContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rowIndex = $this->GetRecordCount() + 1;

        $_sharedStyle = $this->GetCellStyle($_columnIndex, $_rowIndex);

        if (isset($_fieldContainer[SWIFT_KQLSchema::FIELD_ALIGN])) {
            if (isset($this->_reportExcelAlignmentMap[$_fieldContainer[SWIFT_KQLSchema::FIELD_ALIGN]])) {
                $this->_exportFieldTitleExtendedInfoContainer[$_fieldReferenceName] = $this->_reportExcelAlignmentMap[$_fieldContainer[SWIFT_KQLSchema::FIELD_ALIGN]];

                $_sharedStyle->getAlignment()->setHorizontal($this->_reportExcelAlignmentMap[$_fieldContainer[SWIFT_KQLSchema::FIELD_ALIGN]]);
            }
        }

        $this->StyleCell($_sharedStyle);
        $this->StyleTitleCell($_sharedStyle);

        $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $_rowIndex, $_fieldTitle);

        $this->SetCellStyle($_columnIndex, $_rowIndex, $_sharedStyle);

        return true;
    }

    /**
     * Set Cell Value with Correct Datatype
     *
     * @author Andriy Lesyuk
     * @param int $_columnIndex
     * @param string $_columnName
     * @param string $_columnValue
     * @param CellStyleMock $_sharedStyle
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetCellValue($_columnIndex, $_columnName, $_columnValue, $_sharedStyle) # FIXME: obsolete
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_sqlParsedTitles[$_columnName]) || $this->_sqlParsedTitles[$_columnName][1] === false) {
            $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_columnValue);

            return true;

        // If the value isn't false for the function call, then return data as is
        } else if ($this->_sqlParsedTitles[$_columnName][3] !== false) {
            $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_columnValue);

            return true;
        }

        $_fieldType = $this->_sqlParsedTitles[$_columnName][1];

        switch ($_fieldType) {
            case SWIFT_KQLSchema::FIELDTYPE_INT:
                $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, (int) ($_columnValue), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $_sharedStyle->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

                break;

            case SWIFT_KQLSchema::FIELDTYPE_FLOAT:
                $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, floatval($_columnValue), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $_sharedStyle->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

                break;

            case SWIFT_KQLSchema::FIELDTYPE_STRING:
                $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_columnValue, PHPExcel_Cell_DataType::TYPE_STRING);

                break;

            case SWIFT_KQLSchema::FIELDTYPE_BOOL:
                $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, (int) ($_columnValue), PHPExcel_Cell_DataType::TYPE_BOOL);

                break;

            case SWIFT_KQLSchema::FIELDTYPE_UNIXTIME:
                $_value = (int) ($_columnValue);
                if ($_value > 0) {
                    $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, PHPExcel_Shared_Date::PHPToExcel($_value), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                } else {
                    $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, null, PHPExcel_Cell_DataType::TYPE_NULL);
                }
                $_sharedStyle->getNumberFormat()->setFormatCode(self::GetExportDateFormat());

                break;

            case SWIFT_KQLSchema::FIELDTYPE_SECONDS:
                $_value = (int) ($_columnValue);
                $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_value / 86400, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $_sharedStyle->getNumberFormat()->setFormatCode(SWIFT_ReportExport::GetExportElapsedFormat($_value));

                break;

            case SWIFT_KQLSchema::FIELDTYPE_CUSTOM:
                if (isset($this->_sqlParsedTitles[$_columnName][2][SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_columnValue])) {
                    $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $this->Language->Get($this->_sqlParsedTitles[$_columnName][2][SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_columnValue]), PHPExcel_Cell_DataType::TYPE_STRING);
                } else {
                    $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_columnValue, PHPExcel_Cell_DataType::TYPE_NULL);
                }

                break;

            default:
                $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_columnValue);

                break;
        }

        return true;
    }

    /**
     * Set the Cell Value Taking into Account Expression Type
     *
     * @author Andriy Lesyuk
     * @param string $_columnName
     * @param string $_columnValue
     * @param int $_columnIndex
     * @param CellStyleMock $_sharedStyle
     * @param int|bool $_rowIndex
     * @param array $_rowResults
     * @return bool Always True
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetColumnValue($_columnName, $_columnValue, $_columnIndex, $_sharedStyle, $_rowIndex = false, $_rowResults = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_columnExpression = $this->KQLObject->Compiler->GetExpressionByColumnName($_columnName, $_rowIndex);

        // Return as it is if FORMAT is @
        if (isset($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]) &&
            isset($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['FORMAT']) &&
            ($_columnExpression[SWIFT_KQL2::EXPRESSION_EXTRA]['FORMAT'] == '@')) {
            $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_columnValue);

            return true;
        }

        // Convert value to proper type
        $_convertedValue = $this->ConvertColumnValue($_columnName, $_columnValue, $_rowIndex);

        // Indicates if the value has been set
        $_valueWritten = false;

        // Process the value
        if (is_null($_convertedValue)) {
            $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, null, PHPExcel_Cell_DataType::TYPE_NULL);

            $_valueWritten = true;

        } elseif (isset($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])) {
            switch ($_columnExpression[SWIFT_KQL2::EXPRESSION_RETURNTYPE])
            {
                case SWIFT_KQL2::DATA_BOOLEAN:
                    if (is_bool($_convertedValue)) {
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_convertedValue, PHPExcel_Cell_DataType::TYPE_BOOL);

                        $_valueWritten = true;
                    }
                    break;

                case SWIFT_KQL2::DATA_INTEGER:
                    /**
                     * Bug Fix - Ravi Sharma <ravi.sharma@opencart.com.vn>
                     *
                     * SWIFT-4553 Ticket ID is not visible when reports are exported in .xls and .xlsx format.
                     *
                     * Comments: Was skipping the float values in phpexcel.
                     */
                    if (is_int($_convertedValue) || is_float($_convertedValue)) {
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_convertedValue, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                        $_sharedStyle->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

                        $_valueWritten = true;
                    }
                    break;

                case SWIFT_KQL2::DATA_FLOAT:
                    if (is_float($_convertedValue)) {
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_convertedValue, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                        $_sharedStyle->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

                        $_valueWritten = true;
                    }
                    break;

                case SWIFT_KQL2::DATA_SECONDS:
                    if (is_numeric($_convertedValue)) {
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_convertedValue / 86400, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                        $_sharedStyle->getNumberFormat()->setFormatCode(SWIFT_ReportExport::GetExportElapsedFormat($_convertedValue));

                        $_valueWritten = true;
                    }
                    break;

                case SWIFT_KQL2::DATA_TIME:
                    if (is_int($_convertedValue)) {
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_convertedValue / 86400, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                        $_sharedStyle->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4);

                        $_valueWritten = true;
                    }
                    break;

                case SWIFT_KQL2::DATA_DATE:
                    if (is_int($_convertedValue)) {
                        if ($_convertedValue > 0) {
                            $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, PHPExcel_Shared_Date::PHPToExcel($_convertedValue), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        } else {
                            $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, null, PHPExcel_Cell_DataType::TYPE_NULL);
                        }

                        $_sharedStyle->getNumberFormat()->setFormatCode(self::GetExportDateFormat());

                        $_valueWritten = true;
                    }
                    break;

                case SWIFT_KQL2::DATA_UNIXDATE:
                case SWIFT_KQL2::DATA_DATETIME:
                    if (is_numeric($_convertedValue)) {
                        if ($_convertedValue > 0) {
                            $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, PHPExcel_Shared_Date::PHPToExcel($_convertedValue, true, SWIFT::Get('timezone')), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        } else {
                            $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, null, PHPExcel_Cell_DataType::TYPE_NULL);
                        }

                        $_sharedStyle->getNumberFormat()->setFormatCode(self::GetExportDateFormat(true));

                        $_valueWritten = true;
                    }
                    break;

                case SWIFT_KQL2::DATA_STRING:
                    if (is_string($_convertedValue)) {
                        $_convertedValue = preg_replace('#<br\s*/?>#i', "\n", $_convertedValue);
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, strip_tags($_convertedValue), PHPExcel_Cell_DataType::TYPE_STRING);

                        $_sharedStyle->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        $_valueWritten = true;
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
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $this->Language->Get($_fieldProperties[SWIFT_KQLSchema::FIELD_CUSTOMVALUES][$_convertedValue]), PHPExcel_Cell_DataType::TYPE_STRING);
                    } else {
                        $this->_workSheet->setCellValueExplicitByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, null, PHPExcel_Cell_DataType::TYPE_NULL);
                    }

                    $_sharedStyle->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $_valueWritten = true;
                }

                if (isset($_fieldProperties[SWIFT_KQLSchema::FIELD_WRITER])) {
                    $_exportData = array(
                        'columnIndex' => $_columnIndex,
                        'sharedStyle' => $_sharedStyle
                    );

                    $_method = $_fieldProperties[SWIFT_KQLSchema::FIELD_WRITER];
                    $_writerResult = $this->$_method($_convertedValue, $_columnName, $_rowResults, $_exportData); // TODO: array(class, method)

                    if ($_writerResult) {
                        $_valueWritten = true;
                    }
                }
            }
        }

        if ($_valueWritten == false) {
            $this->_workSheet->setCellValueByColumnAndRow($_columnIndex, $this->GetRecordCount() + 1, $_convertedValue);
        }

        return true;
    }

    /**
     * Merge Cells
     *
     * @author Andriy Lesyuk
     * @param int $_startColumn
     * @param int $_startRow
     * @param int $_endColumn
     * @param int $_endRow
     * @param string $_cellType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function MergeAndStyleCells($_startColumn, $_startRow, $_endColumn, $_endRow, $_cellType = self::CELL_COMMON)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_workSheet->mergeCellsByColumnAndRow($_startColumn, $_startRow, $_endColumn, $_endRow);

        for ($x = $_startRow; $x <= $_endRow; $x++) {
            for ($y = $_startColumn; $y <= $_endColumn; $y++) {
                $_sharedStyle = $this->GetCellStyle($y, $x);

                switch ($_cellType)
                {
                    case self::CELL_TITLE:
                        $this->StyleCell($_sharedStyle);
                        $this->StyleTitleCell($_sharedStyle);
                        break;

                    case self::CELL_GROUP:
                        $this->StyleCell($_sharedStyle);
                        $this->StyleGroupCell($_sharedStyle);
                        break;

                    case self::CELL_STATEMENT:
                        $this->StyleStatementCell($_sharedStyle);
                        break;

                    default:
                        $this->StyleCell($_sharedStyle);
                        break;
                }

                $this->SetCellStyle($y, $x, $_sharedStyle);
            }
        }

        if (($_endColumn - $_startColumn) > 0) {
            $this->MergeColumns(($_endColumn - $_startColumn) + 1);
        }

        if (($_endRow - $_startRow) > 0) {
            $_sharedStyle = $this->GetCellStyle($_startColumn, $_startRow);

            $_sharedStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $this->SetCellStyle($_startColumn, $_startRow, $_sharedStyle);
        }

        return true;
    }

    /**
     * Reset Column Index
     *
     * @author Andriy Lesyuk
     * @param int $_columnIndex
     * @return int Column Index (Always Zero)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ResetColumn($_columnIndex = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_columnIndex = $_columnIndex;

        return $this->_columnIndex;
    }

    /**
     * Increments Column Index
     *
     * @author Andriy Lesyuk
     * @return int Column Index
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function NextColumn()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_columnIndex++;
    }

    /**
     * Return Number of Columns
     *
     * @author Andriy Lesyuk
     * @return int Column Number
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetColumns()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_columnIndex;
    }

    /**
     * Merge Columns
     *
     * @author Andriy Lesyuk
     * @return int Column Index
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function MergeColumns($_count = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_count > 1)
        {
            $this->_columnIndex += ($_count - 1);
        }

        return $this->_columnIndex;
    }

    /**
     * Return Excel Date Format
     * NOTE: Copy of SWIFT_Date::GetCalendarDateFormat
     *
     * @author Andriy Lesyuk
     * @param bool $_withTime
     * @return string The Date Format
     */
    public static function GetExportDateFormat($_withTime = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_timeFormat = '';
        if ($_withTime) {
            $_timeFormat = ' h:mm:ss';
        }

        if ($_SWIFT->Settings->Get('dt_caltype') == 'us') {
            return 'm/d/yyyy' . $_timeFormat;
        } else {
            return 'd/m/yyyy' . $_timeFormat;
        }
    }

    /**
     * Return Excel Elapsed Time Format
     *
     * @author Andriy Lesyuk
     * @param int $_columnValue
     * @return string The Elapsed Time Format
     * @throws SWIFT_Exception
     */
    public static function GetExportElapsedFormat($_columnValue)
    {
        $_customFormat = '';

        $_hour = floor($_columnValue / 3600);
        $_minute = floor(($_columnValue % 3600) / 60);

        /*
         * if ($_hour > 0) {
         *     return $this->Language->Get('excelvardate2');
         * } else if ($_minute > 0) {
         *     return $this->Language->Get('excelvardate3');
         * } else {
         *     return $this->Language->Get('excelvardate4');
         * }
         */

        $_SWIFT = SWIFT::GetInstance();

        $_customFormat .= '[>0.0416550925925926]' . $_SWIFT->Language->Get('excelvardate2') . ';';
        $_customFormat .= '[>0.00068287037037037]' . $_SWIFT->Language->Get('excelvardate3') . ';';
        $_customFormat .= $_SWIFT->Language->Get('excelvardate4');

        return $_customFormat;

    }

    /**
     * Get a List of Supported Formats
     *
     * @author Andriy Lesyuk
     * @return array The List of Supported Export Formats
     */
    public static function GetExportFormatContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_exportFormatContainer = array();

        foreach (self::$_exportFormatContainer as $_key => $_val) {

            /**
             * IMPROVEMENT - Ankit Saini <ankit.saini@opencart.com.vn>
             *
             * SWIFT-5220 Performance adjustments for scalable desks
             */
            if($_SWIFT->Settings->Get('cpu_hidereportexportxlsoption') && ($_key == self::EXPORT_EXCEL || $_key == self::EXPORT_EXCEL5)){
                continue;
            }

            if (isset($_val[2]) && !extension_loaded($_val[2])) {
                continue;
            }

            $_exportFormatContainer[$_key] = $_val;
        }

        return $_exportFormatContainer;
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

        return $this->_exportFormat;
    }

}

class CellStyleMock {
    public function getBorders() {
        return $this;
    }
    public function getFont() {
        return $this;
    }
    public function getFill() {
        return $this;
    }
    public function getAlignment() {
        return $this;
    }
    public function getNumberFormat() {
        return $this;
    }
    public function getRight() {
        return $this;
    }
    public function getLeft() {
        return $this;
    }
    public function getTop() {
        return $this;
    }
    public function getBottom() {
        return $this;
    }
    public function setBorderStyle() {
        return $this;
    }
    public function setColor() {
        return $this;
    }
    public function getCellByColumnAndRow($_col = 0, $_row = 0) {
        return $this;
    }
    public function getHyperlink() {
        return $this;
    }
    public function setUrl() {
        return $this;
    }
}
