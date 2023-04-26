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

/**
 * The Line Chart
 *
 * @author Andriy Lesyuk
 */
class SWIFT_ReportChartLine extends SWIFT_ReportChartBase
{

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
        parent::__construct($_xProperties, $_yProperties, $_seriesProperties,
                            $_xDataContainer, $_yDataContainer, $_dataContainer,
                            $_chartOptions, $_chartAttributes);

        $this->_attributes['showValues'] = '0';

        if (count($this->_values[0]) > 100) {
            $this->_scrollChart = true;
        }
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

        if (is_array($this->_yNames[0])) {
            return array_unique($this->_yNames[0]);
        } else {
            return array_unique($this->_yNames);
        }
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

        $_chartXml = $this->GetChartXML();

        $_chartSWF = SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THIRDPARTYDIRECTORY . '/FusionCharts/Charts/';
        if ($this->_scrollChart) {
            $_chartSWF .= 'ScrollLine2D.swf';
        } elseif (count($this->_yNames) > 1) {
            $_chartSWF .= 'MSLine.swf';
        } else {
            $_chartSWF .= 'Line.swf';
        }

        $_chartHeight = self::CHART_HEIGHT;
        if (count($this->_yNames) > 25) {
            $_chartHeight += 150;
        }

        $_chartOutput = renderChart($_chartSWF, "", $_chartXml, $this->GetUniqueID(), self::CHART_WIDTH, $_chartHeight, self::CHART_DEBUG, true);

        $this->AppendOutput($_chartOutput);

        return true;
    }

}
