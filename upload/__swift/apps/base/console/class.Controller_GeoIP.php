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

namespace Base\Console;

use Base\Models\GeoIP\SWIFT_GeoIP;
use Base\Models\GeoIP\SWIFT_GeoIPCity;
use Base\Models\GeoIP\SWIFT_GeoIPInternetServiceProvider;
use Base\Models\GeoIP\SWIFT_GeoIPNetSpeed;
use Base\Models\GeoIP\SWIFT_GeoIPOrganization;
use Controller_console;
use SWIFT_Console;
use SWIFT_Exception;

/**
 * The GeoIP Controller
 *
 * @author Varun Shoor
 */
class Controller_GeoIP extends Controller_console
{
    /**
     * Rebuild the GeoIP Index
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Rebuild()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->Console->Message('Starting GeoIP Rebuild..', SWIFT_Console::CONSOLE_INFO);

        /**
         * ---------------------------------------------
         * ISP
         * ---------------------------------------------
         */
        $this->Console->Message('Processing ISP Database', SWIFT_Console::CONSOLE_INFO);

        SWIFT_GeoIPInternetServiceProvider::EmptyDatabase();

        $_offset = 0;
        $_passLimit = 2;
        $_lineLimit = 1000;

        while ($_offset != -1) {
            $_result = SWIFT_GeoIP::Import(SWIFT_GeoIP::FILE_ISP, $_passLimit, $_lineLimit, $_offset);

            $_offset = $_result[0];

            $this->Console->Message('ISP - Processed Lines: ' . $_result[1], SWIFT_Console::CONSOLE_INFO);
        }
        $this->Settings->UpdateKey('geoip', SWIFT_GeoIP::FILE_ISP, DATENOW);


        /**
         * ---------------------------------------------
         * NETSPEED
         * ---------------------------------------------
         */
        $this->Console->Message('Processing Netspeed Database', SWIFT_Console::CONSOLE_INFO);

        SWIFT_GeoIPNetSpeed::EmptyDatabase();

        $_offset = 0;
        $_passLimit = 2;
        $_lineLimit = 1000;

        while ($_offset != -1) {
            $_result = SWIFT_GeoIP::Import(SWIFT_GeoIP::FILE_NETSPEED, $_passLimit, $_lineLimit, $_offset);

            $_offset = $_result[0];

            $this->Console->Message('Netspeed - Processed Lines: ' . $_result[1], SWIFT_Console::CONSOLE_INFO);
        }
        $this->Settings->UpdateKey('geoip', SWIFT_GeoIP::FILE_NETSPEED, DATENOW);


        /**
         * ---------------------------------------------
         * ORGANIZATION
         * ---------------------------------------------
         */
        $this->Console->Message('Processing Organization Database', SWIFT_Console::CONSOLE_INFO);

        SWIFT_GeoIPOrganization::EmptyDatabase();

        $_offset = 0;
        $_passLimit = 2;
        $_lineLimit = 1000;

        while ($_offset != -1) {
            $_result = SWIFT_GeoIP::Import(SWIFT_GeoIP::FILE_ORGANIZATION, $_passLimit, $_lineLimit, $_offset);

            $_offset = $_result[0];

            $this->Console->Message('Organization - Processed Lines: ' . $_result[1], SWIFT_Console::CONSOLE_INFO);
        }
        $this->Settings->UpdateKey('geoip', SWIFT_GeoIP::FILE_ORGANIZATION, DATENOW);


        /**
         * ---------------------------------------------
         * CITYLOCATION
         * ---------------------------------------------
         */
        $this->Console->Message('Processing CityLocation Database', SWIFT_Console::CONSOLE_INFO);

        SWIFT_GeoIPCity::EmptyDatabaseLocation();

        $_offset = 0;
        $_passLimit = 2;
        $_lineLimit = 1000;

        while ($_offset != -1) {
            $_result = SWIFT_GeoIP::Import(SWIFT_GeoIP::FILE_CITYLOCATION, $_passLimit, $_lineLimit, $_offset);

            $_offset = $_result[0];

            $this->Console->Message('CityLocation - Processed Lines: ' . $_result[1], SWIFT_Console::CONSOLE_INFO);
        }
        $this->Settings->UpdateKey('geoip', SWIFT_GeoIP::FILE_CITYLOCATION, DATENOW);


        /**
         * ---------------------------------------------
         * CITYBLOCKS
         * ---------------------------------------------
         */
        $this->Console->Message('Processing CityBlocks Database', SWIFT_Console::CONSOLE_INFO);

        SWIFT_GeoIPCity::EmptyDatabaseBlocks();

        $_offset = 0;
        $_passLimit = 2;
        $_lineLimit = 1000;

        while ($_offset != -1) {
            $_result = SWIFT_GeoIP::Import(SWIFT_GeoIP::FILE_CITYBLOCKS, $_passLimit, $_lineLimit, $_offset);

            $_offset = $_result[0];

            $this->Console->Message('CityBlocks - Processed Lines: ' . $_result[1], SWIFT_Console::CONSOLE_INFO);
        }
        $this->Settings->UpdateKey('geoip', SWIFT_GeoIP::FILE_CITYBLOCKS, DATENOW);

        $this->Console->Message('GeoIP Index Completed!', SWIFT_Console::CONSOLE_INFO);

        return true;
    }
}

?>
