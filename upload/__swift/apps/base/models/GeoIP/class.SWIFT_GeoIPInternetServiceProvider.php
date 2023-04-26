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

namespace Base\Models\GeoIP;

use SWIFT;

/**
 * ISP Data Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_GeoIPInternetServiceProvider extends SWIFT_GeoIP {
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieve the Location of GeoIP ISP File
     *
     * @author Varun Shoor
     * @return mixed array(filePath, geoIPType) on Success, "false" otherwise
     */
    public static function GetFile() {
        if ($_returnData = self::GetDefaultCVSFileLocation(self::FILE_ISP)) {
            return $_returnData;
        } else if ($_returnData = self::GetDefaultFileLocation(self::FILE_ISP)) {
            return $_returnData;
        }

        return false;
    }

    /**
     * Empties the GeoIP ISP Database
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EmptyDatabase() {
        $_SWIFT = SWIFT::GetInstance();

        for ($_index = 1; $_index <= 10; $_index++) {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "geoipisp" . $_index);
        }

        $_SWIFT->Settings->DeleteKey('geoip', 'isp');

        return true;
    }

    /**
     * Import the Database
     *
     * @author Varun Shoor
     * @param int $_passLimit The Pass Limit
     * @param int $_lineLimit The Line Processing Limit
     * @param int $_offset The Offset to Begin From
     * @return bool "true" on Success, "false" otherwise
     */
    public static function Import($_passLimit, $_lineLimit, $_offset = 0, $_ = null) {
        return parent::Import(self::FILE_ISP, $_passLimit, $_lineLimit, $_offset);
    }
}
?>
