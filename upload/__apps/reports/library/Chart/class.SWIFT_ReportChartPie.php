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

/**
 * The Pie Chart
 *
 * @author Andriy Lesyuk
 */
class SWIFT_ReportChartPie extends SWIFT_ReportChartBase
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

        if (is_array($this->_xNames[0])) {
            return array_unique($this->_xNames[0]);
        } else {
            return array_unique($this->_xNames);
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

            return false;
        }

        $_chartXml = $this->GetChartXML();

        $_chartSWF = SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THIRDPARTYDIRECTORY . '/FusionCharts/Charts/Pie2D.swf';

        $_chartOutput = renderChart($_chartSWF, "", $_chartXml, $this->GetUniqueID(), self::CHART_WIDTH, self::CHART_HEIGHT, self::CHART_DEBUG, true);

        $this->AppendOutput($_chartOutput);

        return true;
    }

}

?>
