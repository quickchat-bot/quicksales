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

namespace Base\Models\GeoIP;

use SWIFT;

/**
 * City Blocks/Location Data Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_GeoIPCity extends SWIFT_GeoIP
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieves the location of the GeoIP City Blocks File
     *
     * @author Varun Shoor
     * @return mixed array(filePath, geoIPType) on Success, "false" otherwise
     */
    public static function GetFileCityBlocks()
    {
        if ($_returnData = self::GetDefaultCVSFileLocation(self::FILE_CITYBLOCKS)) {
            return $_returnData;
        } else if ($_returnData = self::GetDefaultFileLocation(self::FILE_CITYBLOCKS)) {
            return $_returnData;
        }

        return false;
    }

    /**
     * Retrieve the Location of GeoIP City Locations File
     *
     * @author Varun Shoor
     * @return mixed array(filePath, geoIPType) on Success, "false" otherwise
     */
    public static function GetFileCityLocation()
    {
        if ($_returnData = self::GetDefaultCVSFileLocation(self::FILE_CITYLOCATION)) {
            return $_returnData;
        } else if ($_returnData = self::GetDefaultFileLocation(self::FILE_CITYLOCATION)) {
            return $_returnData;
        }

        return false;
    }

    /**
     * Empties the City Locations Database
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EmptyDatabaseLocation()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "geoipcities");

        $_SWIFT->Settings->DeleteKey('geoip', 'citylocations');

        return true;
    }

    /**
     * Empties the City Blocks Database
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EmptyDatabaseBlocks()
    {
        $_SWIFT = SWIFT::GetInstance();

        for ($_index = 1; $_index <= 10; $_index++) {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "geoipcityblocks" . $_index);
        }

        $_SWIFT->Settings->DeleteKey('geoip', 'cityblocks');

        return true;
    }

    /**
     * Import the Database (City Blocks)
     *
     * @author Varun Shoor
     * @param int $_passLimit The Pass Limit
     * @param int $_lineLimit The Line Processing Limit
     * @param int $_offset The Offset to Begin From
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ImportCityBlocks($_passLimit, $_lineLimit, $_offset = 0)
    {
        return parent::Import(self::FILE_CITYBLOCKS, $_passLimit, $_lineLimit, $_offset);
    }

    /**
     * Import the Database (City Location)
     *
     * @author Varun Shoor
     * @param int $_passLimit The Pass Limit
     * @param int $_lineLimit The Line Processing Limit
     * @param int $_offset The Offset to Begin From
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ImportCityLocation($_passLimit, $_lineLimit, $_offset = 0)
    {
        return parent::Import(self::FILE_CITYLOCATION, $_passLimit, $_lineLimit, $_offset);
    }
}

?>
