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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use Base\Models\GeoIP\SWIFT_GeoIP;
use Base\Models\GeoIP\SWIFT_GeoIPCity;
use Base\Models\GeoIP\SWIFT_GeoIPInternetServiceProvider;
use Base\Models\GeoIP\SWIFT_GeoIPNetSpeed;
use Base\Models\GeoIP\SWIFT_GeoIPOrganization;
use Base\Models\Staff\SWIFT_StaffActivityLog;

/**
 * The GeoIP Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \SWIFT_Loader $Load
 * @property View_GeoIP $View
 * @author Varun Shoor
 */
class Controller_GeoIP extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 25;

    // GeoIP Constants
    const GEOIP_CITYLOCATIONS = 'citylocations';
    const GEOIP_CITYBLOCKS = 'cityblocks';
    const GEOIP_NETSPEED = 'netspeed';
    const GEOIP_ORGANIZATION = 'organization';
    const GEOIP_ISP = 'isp';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_geoip');
    }

    /**
     * The GeoIP UI Action
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('geoip') . ' > ' . $this->Language->Get('maintenance'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canrebuildgeoip') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Redirector for Index
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Manage()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Index();

        return true;
    }

    /**
     * Rebuild the GeoIP Index
     *
     * @author Varun Shoor
     * @param string $_geoIPType The GeoIP Type
     * @param int $_entriesPerPass Number of Entries to Process in a Single Pass
     * @param int $_passNumber Number of Passes
     * @param int $_offset The Offset
     * @param int $_startTime THe Start Time
     * @param int $_refreshCount The Refresh Count
     * @param int $_uniqueID The Unique ID for this Instance
     * @param bool $_isFirstTime Whether this function is being executed for the first time
     * @return bool "true" on Success, "false" otherwise
     */
    public function Rebuild($_geoIPType, $_entriesPerPass, $_passNumber, $_offset = 0, $_startTime = 0, $_refreshCount = 0, $_uniqueID = 0, $_isFirstTime = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || !$this->_IsValidGeoIPType($_geoIPType) || $_SWIFT->Staff->GetPermission('admin_canrebuildgeoip') == '0') {
            return false;
        }

        $_javascriptFunctionName = '';
        if ($_geoIPType == self::GEOIP_CITYLOCATIONS) {
            $_javascriptFunctionName = 'CityLocations';
        } elseif ($_geoIPType == self::GEOIP_CITYBLOCKS) {
            $_javascriptFunctionName = 'CityBlocks';
        } elseif ($_geoIPType == self::GEOIP_NETSPEED) {
            $_javascriptFunctionName = 'NetSpeed';
        } elseif ($_geoIPType == self::GEOIP_ORGANIZATION) {
            $_javascriptFunctionName = 'Organization';
        } elseif ($_geoIPType == self::GEOIP_ISP) {
            $_javascriptFunctionName = 'ISP';
        }

        if ($_isFirstTime) {
            if ($_geoIPType == self::GEOIP_CITYLOCATIONS) {
                $_javascriptFunctionName = 'CityLocations';
                SWIFT_GeoIPCity::EmptyDatabaseLocation();
            } elseif ($_geoIPType == self::GEOIP_CITYBLOCKS) {
                $_javascriptFunctionName = 'CityBlocks';
                SWIFT_GeoIPCity::EmptyDatabaseBlocks();
            } elseif ($_geoIPType == self::GEOIP_NETSPEED) {
                $_javascriptFunctionName = 'NetSpeed';
                SWIFT_GeoIPNetSpeed::EmptyDatabase();
            } elseif ($_geoIPType == self::GEOIP_ORGANIZATION) {
                $_javascriptFunctionName = 'Organization';
                SWIFT_GeoIPOrganization::EmptyDatabase();
            } elseif ($_geoIPType == self::GEOIP_ISP) {
                $_javascriptFunctionName = 'ISP';
                SWIFT_GeoIPInternetServiceProvider::EmptyDatabase();
            }
        }

        if (empty($_entriesPerPass)) {
            $_entriesPerPass = SWIFT_GeoIP::LINE_LIMIT;
        }

        if (empty($_passNumber)) {
            $_passNumber = SWIFT_GeoIP::PASS_LIMIT;
        }

        if (empty($_offset)) {
            $_offset = 0;
        }

        if (empty($_startTime)) {
            $_startTime = DATENOW;
        }

        if (empty($_refreshCount)) {
            $_refreshCount = 0;
        }
        $_refreshCount++;

        $_fileReturnData = $_result = [];
        if ($_geoIPType == self::GEOIP_CITYLOCATIONS) {
            $_fileReturnData = SWIFT_GeoIPCity::GetFileCityLocation();
            $_result = SWIFT_GeoIPCity::ImportCityLocation($_passNumber, $_entriesPerPass, $_offset);
        } elseif ($_geoIPType == self::GEOIP_CITYBLOCKS) {
            $_fileReturnData = SWIFT_GeoIPCity::GetFileCityBlocks();
            $_result = SWIFT_GeoIPCity::ImportCityBlocks($_passNumber, $_entriesPerPass, $_offset);
        } elseif ($_geoIPType == self::GEOIP_NETSPEED) {
            $_fileReturnData = SWIFT_GeoIPNetSpeed::GetFile();
            $_result = SWIFT_GeoIPNetSpeed::Import($_passNumber, $_entriesPerPass, $_offset);
        } elseif ($_geoIPType == self::GEOIP_ORGANIZATION) {
            $_fileReturnData = SWIFT_GeoIPOrganization::GetFile();
            $_result = SWIFT_GeoIPOrganization::Import($_passNumber, $_entriesPerPass, $_offset);
        } elseif ($_geoIPType == self::GEOIP_ISP) {
            $_fileReturnData = SWIFT_GeoIPInternetServiceProvider::GetFile();
            $_result = SWIFT_GeoIPInternetServiceProvider::Import($_passNumber, $_entriesPerPass, $_offset);
        }

        $_fileSize = filesize($_fileReturnData[0]);

        if ($_result[0] == -1) {
            $_percent = 100;
        } else {
            $_percent = $_result[0] * (100 / $_fileSize);
        }

        $_redirectURL = false;
        if ($_percent < 100 && $_result[0] < $_fileSize && $_result[0] != -1) {
            $_redirectURL = SWIFT::Get('basename') . "/Base/GeoIP/Rebuild/" . $_geoIPType . '/' . $_entriesPerPass . '/' . $_passNumber . '/' . (int)($_result[0]) . '/' . $_startTime . '/' . $_refreshCount . '/' . $_uniqueID . '/0';
        }

        if ($_isFirstTime) {
            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdategeoip'), strtoupper($_geoIPType)), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_GEOIP, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
        }

        // Have we hit 100%?
        if ($_percent == 100) {
            $this->Settings->UpdateKey('geoip', $_geoIPType, (string)DATENOW);
            $this->Settings->UpdateKey('geoip', $_geoIPType . '_filemtime', (string)filemtime($_fileReturnData[0]));
        }

        $this->View->RenderResult($_geoIPType, $_redirectURL, $_javascriptFunctionName, $_percent, $_result, $_refreshCount, $_fileSize, $_offset, $_startTime, $_uniqueID);
    }

    /**
     * The GeoIP Type
     *
     * @author Varun Shoor
     * @param string $_geoIPType The GeoIP Type
     * @return bool "true" on Success, "false" otherwise
     */
    protected function _IsValidGeoIPType($_geoIPType)
    {
        if ($_geoIPType == self::GEOIP_CITYLOCATIONS || $_geoIPType == self::GEOIP_CITYBLOCKS || $_geoIPType == self::GEOIP_NETSPEED || $_geoIPType == self::GEOIP_ORGANIZATION || $_geoIPType == self::GEOIP_ISP) {
            return true;
        } else {
            return false;
        }
    }
}

?>
