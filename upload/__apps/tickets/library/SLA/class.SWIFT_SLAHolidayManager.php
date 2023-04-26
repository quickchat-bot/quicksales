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

namespace Tickets\Library\SLA;

use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Library;
use Tickets\Models\SLA\SWIFT_SLAHoliday;
use SWIFT_XML;

/**
 * The SLA Holiday Manager. Handles Import/Export of SLA Holidays
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class SWIFT_SLAHolidayManager extends SWIFT_Library
{
    // Core Constants
    const HOLIDAYFILE_SUFFIX = '.holidays.xml';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Generates the sla holiday file name using product name and version
     *
     * @author Varun Shoor
     * @param string $_holidayTitle The Holiday Title
     * @return string The Generated File Name
     */
    public static function GenerateFileName($_holidayTitle)
    {
        return strtolower(SWIFT_PRODUCT) . '.' . str_replace('.', '-', SWIFT_VERSION) . '.' . strtolower(Clean($_holidayTitle)) . self::HOLIDAYFILE_SUFFIX;
    }

    /**
     * Export the SLA Holidays
     *
     * @author Varun Shoor
     * @param string $_title The Holiday Title
     * @param string $_author The Author Name
     * @param string $_fileName The Filename for Export
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Export($_title, $_author, $_fileName)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_fileName))
        {
            $_fileName = self::GenerateFileName($_title);
        }

        $this->XML->AddComment(sprintf($this->Language->Get('generationdate'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, DATENOW)));

        $this->XML->AddParentTag('swiftholidays');
            $this->XML->AddTag('title', $_title);
            $this->XML->AddTag('author', $_author);
            $this->XML->AddTag('version', SWIFT_VERSION);

            $this->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slaholidays ORDER BY slaholidayid ASC");
            while ($this->Database->NextRecord())
            {
                $this->XML->AddParentTag('holiday');
                    $this->XML->AddTag("title", $this->Database->Record['title']);
                    $this->XML->AddTag("day", $this->Database->Record['holidayday']);
                    $this->XML->AddTag("month", $this->Database->Record['holidaymonth']);
                    $this->XML->AddTag("flagicon", $this->Database->Record['flagicon']);
                $this->XML->EndParentTag('holiday');
            }

        $this->XML->EndTag('swiftholidays');

        $_xmlData = $this->XML->ReturnXML();

        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            @ini_set('zlib.output_compression', 'Off');
        }

        @header('Content-Type: application/force-download');

        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")){
            @header('Content-Disposition: attachment; filename="' . $_fileName .'"');
        } else{
            @header('Content-Disposition: attachment; filename="' . $_fileName . '"');
        }

        @header("Content-Transfer-Encoding: binary\n");
        @header("Content-Length: ". strlen($_xmlData) ."\n");

        echo $_xmlData;

        return true;
    }

    /**
     * Import the SLA Holidays
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param string $_fileName The Filename to Import from
     * @return mixed "_slaHolidayContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import($_fileName)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!file_exists($_fileName)) {
            throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
        }

        $_xmlHolidaysXMLContainer = $this->XML->XMLToTree(file_get_contents($_fileName));

        if (!is_array($_xmlHolidaysXMLContainer) || !isset($_xmlHolidaysXMLContainer['swiftholidays'][0]['children']))
        {
            return false;
        }

        $_slaHolidayPointer = &$_xmlHolidaysXMLContainer['swiftholidays'][0]['children'];

        if (!isset($_slaHolidayPointer['title'][0]['values'][0]) || !isset($_slaHolidayPointer['author'][0]['values'][0]) || !isset($_slaHolidayPointer['version'][0]['values'][0]))
        {
            return false;
        }

        $_title = $_slaHolidayPointer['title'][0]['values'][0];
        $_author = $_slaHolidayPointer['author'][0]['values'][0];
        $_version = $_slaHolidayPointer['version'][0]['values'][0];

        if (!isset($_slaHolidayPointer['holiday']) || !_is_array($_slaHolidayPointer['holiday']))
        {
            return false;
        }

        $_slaHolidayContainer = array();
        foreach ($_slaHolidayPointer['holiday'] as $_key => $_val)
        {
            if (!isset($_val['children']['title'][0]['values'][0]) || !isset($_val['children']['day'][0]['values'][0]) || !isset($_val['children']['month'][0]['values'][0]))
            {
                continue;
            }

            $_flagIcon = $_val['children']['flagicon'][0]['values'][0] ?? '';
            $_slaHolidayContainer[] = array('title' => $_val['children']['title'][0]['values'][0], 'day' => (int) ($_val['children']['day'][0]['values'][0]), 'month' => (int) ($_val['children']['month'][0]['values'][0]), 'flagicon' => $_flagIcon);
        }

        foreach ($_slaHolidayContainer as $_key => $_val)
        {
            SWIFT_SLAHoliday::Create($_val['title'], 0, $_val['day'], $_val['month'], $_val['flagicon'], array());
        }

        return $_slaHolidayContainer;
    }
}
