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

require_once ('./'. SWIFT_BASEDIRECTORY .'/'. SWIFT_THIRDPARTYDIRECTORY .'/FusionCharts/Includes/FusionCharts.php');

/**
 * The Base Chart
 *
 * @author Andriy Lesyuk
 */
abstract class SWIFT_ReportChartBase extends SWIFT_Library
{
    const CHART_WIDTH = '100%';
    const CHART_HEIGHT = 300;
    const CHART_DEBUG = false;

    const COLOR_HUE_DIVISION = 38;
    const COLOR_HUE_OFFSET = 5;

    const COLOR_SATURATION_DIVISION = 10;
    const COLOR_SATURATION_OFFSET = 0;
    const COLOR_SATURATION_MIN = 3;
    const COLOR_SATURATION_MAX = 9;


    const COLOR_LIGHTNESS_DIVISION = 7;
    const COLOR_LIGHTNESS_OFFSET = 19;
    const COLOR_LIGHTNESS_MIN = 2;
    const COLOR_LIGHTNESS_MAX = 4;

    protected $_renderedOutput = '';

    protected $_caption = false;
    protected $_subCaption = false;

    protected $_xProperties = array();
    protected $_yProperties = array();

    protected $_xNames = array();
    protected $_yNames = array();
    protected $_values = array();

    protected $_attributes = array();

    protected $_scrollChart = false;

    protected $_colorPalette = array();

    protected $_defaultChartAttributes = array(
        'bgColor'                  => 'F7F6F6',    // Background of the chart (not canvas)
        'showBorder'              => '0',        // Don't show border of the chart (border you see is the container's)
        'canvasBgColor'              => 'FFFFFF',    // Background color of canvas (graph)
        'canvasBorderThickness'      => '1',        // Thickness of the canvas (2px by default)
        'divLineColor'              => 'A1A1A1',    // Color of horizontal lines on canvas
        'showAlternateHGridColor' => '0',        // Disable alternate color of horizontal stripes
        'legendBgColor'              => 'FDFCFC',    // Background color of the legend
        'legendBorderColor'          => 'A1A1A1',    // Border color of the legend
        'baseFont'                  => 'Verdana',    // Font name
        'baseFontColor'              => '222222',    // Font color
        'plotGradientColor'          => '',        // Disable gradient on columns etc
        'palette'                  => '2',        // Use the second theme
        'use3DLighting'              => '0',        // Disable inner shadow for Pie
        'adjustDiv'                  => '0',        // Disables adjusting divisional lines
        'skipOverlapLabels'          => '0'        // Does not allow skipping labels
    );

    static protected $_chartId = 1;

    static protected $_defaultPalette = array(
        array(32,  255, 128),
        array(52,  255, 91),
        array(21,  255, 128),
        array(151, 255, 134),
        array(251, 197, 132),
        array(152, 239, 94),
        array(249, 255, 130),
        array(131, 255, 128),
        array(95,  197, 132),
        array(146, 187, 127),
        array(119, 255, 128),
        array(234, 187, 127),
        array(137, 187, 90),
        array(135, 187, 127),
        array(5,   116, 127),
        array(110, 143, 132),
        array(249, 116, 127),
        array(123, 116, 127),
        array(25,  115, 91),
        array(224, 88,  132)
    );

    static protected $_defaultPaletteRGB = array();
    static protected $_defaultPaletteKHSL = array();

    static protected $_colorPaletteHues = array();
    static protected $_colorPaletteSaturations = array();
    static protected $_colorPaletteLightnesses = array();

    /**
     * Constructor
     *
     * @author Andriy Lesyuk
     * @param array $_xProperties
     * @param array $_yProperties
     * @param array $_seriesProperties
     * @param array $_xDataContainer
     * @param array $_yDataContainer
     * @param array $_dataContainer
     * @param array $_chartOptions
     * @param array $_chartAttributes
     * @throws SWIFT_Exception
     */
    public function __construct($_xProperties, $_yProperties, $_seriesProperties,
                                $_xDataContainer, $_yDataContainer, $_dataContainer,
                                $_chartOptions = array(), $_chartAttributes = array())
    {
        parent::__construct();

        // Check if report contains title row (column names)
        if ($_yProperties[SWIFT_ReportChart::AXIS_TITLE]) {
            $_lastXIndex = count($_xDataContainer) - 1;

            if (count($_xDataContainer[$_lastXIndex]) > 1) {
                // Remove title columns that won't be used
                for ($i = count($_xDataContainer[$_lastXIndex]) - 1; $i >= 0; $i--) {
                    if ($_xDataContainer[$_lastXIndex][$i] != $_yProperties[SWIFT_ReportChart::AXIS_ID]) {
                        foreach ($_xDataContainer as &$_rowContainer) {
                            array_splice($_rowContainer, $i, 1);
                        }
                        unset($_rowContainer);
                        foreach ($_dataContainer as &$_rowContainer) {
                            array_splice($_rowContainer, $i, 1);
                        }
                        unset($_rowContainer);
                    }
                }
            }
        }

        // Get max value
        $_maxValue = 0;
        foreach ($_dataContainer as $_rowContainer) {
            $_max = max($_rowContainer);
            if ($_max > $_maxValue) {
                $_maxValue = $_max;
            }
        }

        // Throw exception if zeros
        if ($_maxValue <= 0) {
            throw new SWIFT_Exception('Zeros');
        }

        // Check if table data should be "rotated"
        if ($_xProperties[SWIFT_ReportChart::AXIS_AXIS] != SWIFT_ReportChart::AXIS_X) {
            $_backupXContainer = SWIFT_ReportChartBase::RotateArray($_xDataContainer);
            $_xDataContainer = SWIFT_ReportChartBase::RotateArray($_yDataContainer);
            $_yDataContainer = &$_backupXContainer;
            $_dataContainer = SWIFT_ReportChartBase::RotateArray($_dataContainer);
        }

        // Prepend values of previous rows
        for ($i = $_xProperties[SWIFT_ReportChart::AXIS_INDEX] - 1; $i >= 0; $i--) {
            for ($j = 0; $j < count($_xDataContainer[$_xProperties[SWIFT_ReportChart::AXIS_INDEX]]); $j++) {
                if (!empty($_xDataContainer[$i][$j])) {
                    $_xDataContainer[$_xProperties[SWIFT_ReportChart::AXIS_INDEX]][$j] = $_xDataContainer[$i][$j] . ' - ' . $_xDataContainer[$_xProperties[SWIFT_ReportChart::AXIS_INDEX]][$j];
                }
            }
        }

        // Select title row to show (usually the last one)
        $_xDataContainer = $_xDataContainer[$_xProperties[SWIFT_ReportChart::AXIS_INDEX]];

        if (!empty($_seriesProperties)) {
            // Select series column to show
            for ($i = 0; $i < count($_yDataContainer); $i++) {
                for ($j = $_seriesProperties[SWIFT_ReportChart::AXIS_INDEX] - 1; $j >= 0; $j--) {
                    if (!empty($_yDataContainer[$i][$j])) {
                        $_yDataContainer[$i][$_seriesProperties[SWIFT_ReportChart::AXIS_INDEX]] = $_yDataContainer[$i][$j] . ' - ' . $_yDataContainer[$i][$_seriesProperties[SWIFT_ReportChart::AXIS_INDEX]];
                    }
                }
                $_yDataContainer[$i] = $_yDataContainer[$i][$_seriesProperties[SWIFT_ReportChart::AXIS_INDEX]];
            }
        } else {
            // Remove nesting level
            for ($i = 0; $i < count($_yDataContainer); $i++) {
                if (is_array($_yDataContainer[$i]) && (count($_yDataContainer[$i]) == 1)) {
                    $_yDataContainer[$i] = reset($_yDataContainer[$i]);
                }
            }
        }

        $this->_xProperties = &$_xProperties;
        $this->_yProperties = &$_yProperties;

        $this->_xNames = &$_xDataContainer;
        $this->_yNames = &$_yDataContainer;
        $this->_values = &$_dataContainer;

        $this->_attributes = array_merge($this->_defaultChartAttributes, $_chartAttributes);

        // Calculate chart height (Fusion charts does this incorrectly)
        $this->_attributes['yAxisMaxValue'] = $_maxValue + ($_maxValue / 10);
        $this->_attributes['numDivLines'] = 4;

        if (isset($_chartOptions['caption'])) {
            $this->_caption = $_chartOptions['caption'];
        }

        if (isset($_chartOptions['subCaption'])) {
            $this->_subCaption = $_chartOptions['subCaption'];
        }

        self::InitializeColors();

        $_colorCount = count($this->GetColorIdentifiers());

        $this->_colorPalette = array_slice(self::$_defaultPaletteRGB, 0, $_colorCount);
    }

    /**
     * Render Chart XML
     *
     * @author Andriy Lesyuk
     * @return string The Chart XML
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetChartXML()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chartXml = "<chart";
        if ($this->_caption) {
            $_chartXml .= " caption='" . htmlspecialchars($this->_caption, ENT_QUOTES) . "'";
        }
        if ($this->_subCaption) {
            $_chartXml .= " subCaption='" . htmlspecialchars($this->_subCaption, ENT_QUOTES) . "'";
        }
        $_chartXml .= " xAxisName='" . htmlspecialchars($this->_xProperties[SWIFT_ReportChart::AXIS_NAME], ENT_QUOTES) . "'";
        $_chartXml .= " yAxisName='" . htmlspecialchars($this->_yProperties[SWIFT_ReportChart::AXIS_NAME], ENT_QUOTES) . "'";
        foreach ($this->_attributes as $_attributeName => $_attributeValue) {
            $_chartXml .= " " . $_attributeName . "=" . "'" . $_attributeValue . "'";
        }
        if (count($this->_colorPalette) > 0) {
            $_chartXml .= " paletteColors='" . implode(',', $this->_colorPalette) . "'";
        }
        $_chartXml .= ">" . SWIFT_CRLF;

        if ($this->_scrollChart || (count($this->_yNames) > 1)) {
            $_chartXml .= "<categories>" . SWIFT_CRLF;
            foreach ($this->_xNames as $_name) {
                $_chartXml .= "<category label='" . htmlspecialchars($_name, ENT_QUOTES) . "' />" . SWIFT_CRLF;
            }
            $_chartXml .= "</categories>" . SWIFT_CRLF;
        }

        for ($i = 0; $i < count($this->_values); $i++) {
            if ($this->_scrollChart || (count($this->_yNames) > 1)) {
                $_chartXml .= "<dataset";
                if ((count($this->_yNames) > 1) && (!is_array($this->_yNames[$i]))) {
                    $_chartXml .= " seriesName='" . htmlspecialchars($this->_yNames[$i], ENT_QUOTES) . "'";
                }
                $_chartXml .= ">" . SWIFT_CRLF;
            }
            for ($j = 0; $j < count($this->_values[$i]); $j++) {
                $_chartXml .= "<set";
                if (count($this->_yNames) == 1) {
                    $_chartXml .= " label='" . htmlspecialchars($this->_xNames[$j], ENT_QUOTES) . "'";
                }
                $_chartXml .= " value='" . ((empty($this->_values[$i][$j])) ? 0 : $this->_values[$i][$j]) . "'";
                $_chartXml .= " />" . SWIFT_CRLF;
            }
            if ($this->_scrollChart || (count($this->_yNames) > 1)) {
                $_chartXml .= "</dataset>" . SWIFT_CRLF;
            }
        }

        $_chartXml .= "</chart>";

        return $_chartXml;
    }

    /**
     * Render the Chart
     *
     * @author Andriy Lesyuk
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
     * @author Andriy Lesyuk
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
     * Retrieve the Rendered Output
     *
     * @author Andriy Lesyuk
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
     * Generate Unique ID
     *
     * @author Andriy Lesyuk
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetUniqueID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 'chart' . self::$_chartId++;
    }

    /**
     * Get Colors Identifiers
     *
     * @author Andriy Lesyuk
     * @return array The Colors Identifiers
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetColorIdentifiers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return array();
    }

    /**
     * Set Color Palette
     *
     * @author Andriy Lesyuk
     * @param array $_colorPalette The Color Palette
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetColorPalette($_colorPalette)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (count($_colorPalette) > 0) {
            $this->_colorPalette = array();
            foreach ($this->GetColorIdentifiers() as $_yName) {
                if (isset($_colorPalette[$_yName])) {
                    $this->_colorPalette[] = $_colorPalette[$_yName];
                }
            }
        }

        return true;
    }

    /**
     * Rotate the Array
     *
     * @author Andriy Lesyuk
     * @param array $_array The Input 2D Array
     * @return array The Rotated Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public static function RotateArray($_array) {
        $_newArray = array();

        for ($i = 0; $i < count($_array); $i++) {
            for ($j = 0; $j < count($_array[$i]); $j++) {
                if (!isset($_newArray[$j])) {
                    $_newArray[$j] = array();
                }
                $_newArray[$j][$i] = $_array[$i][$j];
            }
        }

        return $_newArray;
    }

    /**
     * Initialize Static Variables
     *
     * @author Andriy Lesyuk
     * @return bool "true"
     */
    public static function InitializeColors()
    {
        if (count(self::$_defaultPalette) > 0) {
            if (count(self::$_defaultPaletteRGB) == 0) {
                foreach (self::$_defaultPalette as $_HSL) {
                    $_RGB = self::GetRGBFromHSL($_HSL);
                    self::$_defaultPaletteRGB[] = sprintf("%02X%02X%02X", $_RGB[0], $_RGB[1], $_RGB[2]);
                }
            }

            if (count(self::$_defaultPaletteKHSL) == 0) {
                foreach (self::$_defaultPalette as $_HSL) {
                    $_h = floor($_HSL[0] / (255 / self::COLOR_HUE_DIVISION));
                    $_s = floor($_HSL[1] / (255 / self::COLOR_SATURATION_DIVISION));
                    $_l = floor($_HSL[2] / (255 / self::COLOR_LIGHTNESS_DIVISION));
                    self::$_defaultPaletteKHSL[sprintf("%02X%02X%02X", $_h, $_s, $_l)] = 1;
                }
            }
        }

        if (count(self::$_colorPaletteHues) == 0) {
            for ($h = 0; $h < self::COLOR_HUE_DIVISION; $h++) {
                self::$_colorPaletteHues[] = round($h * (255 / self::COLOR_HUE_DIVISION)) + self::COLOR_HUE_OFFSET;
            }
        }

        if (count(self::$_colorPaletteSaturations) == 0) {
            for ($s = self::COLOR_SATURATION_MAX; $s >= self::COLOR_SATURATION_MIN; $s--) {
                self::$_colorPaletteSaturations[] = round($s * (255 / self::COLOR_SATURATION_DIVISION)) + self::COLOR_SATURATION_OFFSET;
            }
        }

        if (count(self::$_colorPaletteLightnesses) == 0) {
            $_colorLightnesses = array();
            for ($l = self::COLOR_LIGHTNESS_MIN; $l <= self::COLOR_LIGHTNESS_MAX; $l++) {
                $_colorLightnesses[] = round($l * (255 / self::COLOR_LIGHTNESS_DIVISION) + self::COLOR_LIGHTNESS_OFFSET);
            }

            $_middle = floor(count($_colorLightnesses) / 2);
            self::$_colorPaletteLightnesses[] = $_colorLightnesses[$_middle];
            for ($i = 1; isset($_colorLightnesses[$_middle - $i]) || isset($_colorLightnesses[$_middle + $i]); $i++) {
                if (isset($_colorLightnesses[$_middle - $i])) {
                    self::$_colorPaletteLightnesses[] = $_colorLightnesses[$_middle - $i];
                }
                if (isset($_colorLightnesses[$_middle + $i])) {
                    self::$_colorPaletteLightnesses[] = $_colorLightnesses[$_middle + $i];
                }
            }
        }

        return true;
    }

    /**
     * Gets RGB from Hue
     *
     * @author Andriy Lesyuk
     * @param array $_v Temporary Values
     * @param int $_hue Hue Value
     * @return int RGB Value
     */
    public static function GetRGBFromHue($_v, $_hue)
    {
        if ($_hue < 0) {
            $_hue++;
        }
        if ($_hue > 1) {
            $_hue--;
        }

        if ((6 * $_hue) < 1) {
            return $_v[0] + ($_v[1] - $_v[0]) * 6 * $_hue;
        } elseif ((2 * $_hue) < 1 ) {
            return $_v[1];
        } elseif ((3 * $_hue) < 2) {
            return (int) ($_v[0] + ($_v[1] - $_v[0]) * ((2 / 3) - $_hue) * 6);
        } else {
            return $_v[0];
        }
    }

    /**
     * Converts HSL Color to RGB
     *
     * @author Andriy Lesyuk
     * @param array $_HSL HSL Color
     * @return array RGB Color
     */
    public static function GetRGBFromHSL($_HSL)
    {
        $_RGB = array();

        $_h = $_HSL[0] / 255;
        $_s = $_HSL[1] / 255;
        $_l = $_HSL[2] / 255;

        if ($_s == 0) {
            $_RGB[0] = ($_l * 255);
            $_RGB[1] = ($_l * 255);
            $_RGB[2] = ($_l * 255);
        } else {
            $_v = array();
            if ($_l < 0.5) {
                $_v[1] = $_l * (1 + $_s);
            } else {
                $_v[1] = ($_l + $_s) - ($_s * $_l);
            }
            $_v[0] = 2 * $_l - $_v[1];

            $_RGB[0] = (255 * self::GetRGBFromHue($_v, $_h + (1 / 3)));
            $_RGB[1] = (255 * self::GetRGBFromHue($_v, $_h));
            $_RGB[2] = (255 * self::GetRGBFromHue($_v, $_h - (1 / 3)));
        }

        return $_RGB;
    }

    /**
     * Generate Colors Palette
     *
     * @author Andriy Lesyuk
     * @param int $_count Colors Count
     * @return array Color Palette
     */
    public static function GeneratePalette($_count = 3)
    {
        self::InitializeColors();

        // copy default palette
        $_colorPalette = array_slice(self::$_defaultPaletteRGB, 0, $_count);

        // return if no more colors are needed
        $_originalCount = $_count;
        $_count -= count($_colorPalette);
        if ($_count <= 0) {
            return $_colorPalette;
        }

        $_colors = array();

        $_colorsMap = self::$_defaultPaletteKHSL;

        $_hues = self::$_colorPaletteHues;
        $_saturations = self::$_colorPaletteSaturations;
        $_lightnesses = self::$_colorPaletteLightnesses;

        shuffle($_hues);

        for ($l = 0; ($l < count($_lightnesses)) && (count($_colors) < $_count); $l++) {
            for ($s = 0; ($s < count($_saturations)) && (count($_colors) < $_count); $s++) {
                for ($h = 0; ($h < count($_hues)) && (count($_colors) < $_count); $h++) {
                    $_h = floor($_hues[$h] / (255 / self::COLOR_HUE_DIVISION));
                    $_s = floor($_saturations[$s] / (255 / self::COLOR_SATURATION_DIVISION));
                    $_l = floor($_lightnesses[$l] / (255 / self::COLOR_LIGHTNESS_DIVISION));
                    $_khsl = sprintf("%02X%02X%02X", $_h, $_s, $_l);

                    if (!isset($_colorsMap[$_khsl])) {
                        $_color = array();

                        $_color[] = $_hues[$h];
                        $_color[] = $_saturations[$s];
                        $_color[] = $_lightnesses[$l];

                        $_colors[] = $_color;
                        $_colorsMap[$_khsl] = 1;
                    }
                }
            }
        }

        shuffle($_colors);

        // Convert HSL to RGB
        for ($i = 0; $i < count($_colors); $i++) {
            $_RGB = self::GetRGBFromHSL($_colors[$i]);
            $_colorPalette[] = sprintf("%02X%02X%02X", $_RGB[0], $_RGB[1], $_RGB[2]);

            if (count($_colorPalette) == $_originalCount) {
                break;
            }
        }

        return $_colorPalette;
    }

}
