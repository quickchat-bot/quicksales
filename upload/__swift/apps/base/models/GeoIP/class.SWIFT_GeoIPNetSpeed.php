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
 * Net Speed Data Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_GeoIPNetSpeed extends SWIFT_GeoIP {
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
     * Retrieve the Net Speed File Location
     *
     * @author Varun Shoor
     * @return mixed array(filePath, geoIPType) on Success, "false" otherwise
     */
    public static function GetFile() {
        if ($_returnData = self::GetDefaultCVSFileLocation(self::FILE_NETSPEED)) {
            return $_returnData;
        } else if ($_returnData = self::GetDefaultFileLocation(self::FILE_NETSPEED)) {
            return $_returnData;
        }

        return false;
    }

    /**
     * Empties the GeoIP NetSpeed Database
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EmptyDatabase() {
        $_SWIFT = SWIFT::GetInstance();

        for ($_index = 1; $_index <= 10; $_index++) {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "geoipnetspeed" . $_index);
        }

        $_SWIFT->Settings->DeleteKey('geoip', 'netspeed');

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
        return parent::Import(self::FILE_NETSPEED, $_passLimit, $_lineLimit, $_offset);
    }
}
?>
