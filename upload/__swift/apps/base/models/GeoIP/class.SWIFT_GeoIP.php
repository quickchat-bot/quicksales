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
use SWIFT_Exception;
use Base\Models\GeoIP\SWIFT_GeoIP_Exception;
use Base\Models\GeoIP\SWIFT_GeoIPCity;
use Base\Models\GeoIP\SWIFT_GeoIPInternetServiceProvider;
use Base\Models\GeoIP\SWIFT_GeoIPNetSpeed;
use Base\Models\GeoIP\SWIFT_GeoIPOrganization;
use SWIFT_Model;
use SWIFT_XML;

/**
 * GeoIP Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_GeoIP extends SWIFT_Model
{
    // Number of lines to process in a single pass
    const LINE_LIMIT = 250;

    // Number of passes to do in a current page
    const PASS_LIMIT = 2;

    // License Types
    const GEOIP_PREMIUM = 1;
    const GEOIP_LITE = 2;
    const GEOIP_NONE = 0;

    // GeoIP Data Types
    const GEOIP_CITY = 1;
    const GEOIP_NETSPEED = 2;
    const GEOIP_ISP = 3;
    const GEOIP_ORGANIZATION = 4;

    // GeoIP Attributes
    const GEOIP_COUNTRY = 5;
    const GEOIP_REGION = 6;
    const GEOIP_POSTALCODE = 7;
    const GEOIP_LATITUDE = 8;
    const GEOIP_LONGITUDE = 9;
    const GEOIP_METROCODE = 10;
    const GEOIP_AREACODE = 11;
    const GEOIP_COUNTRYDESC = 12;
    const GEOIP_TIMEZONE = 13;

    // GeoIP Directory & File Attributes
    const DIRECTORY_GEOIP = 'geoip';
    const DIRECTORY_PREMIUM = 'premium';
    const DIRECTORY_LITE = 'lite';

    const FILE_EXTENSION = 'csv';
    const FILE_ISP = 'isp';
    const FILE_ORGANIZATION = 'organization';
    const FILE_NETSPEED = 'netspeed';
    const FILE_CITYBLOCKS = 'cityblocks';
    const FILE_CITYLOCATION = 'citylocation';


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
     * Checks to see if its a valid GeoIP File Type
     *
     * @author Varun Shoor
     * @param string $_fileType GeoIP File Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidGeoIPFileType($_fileType)
    {
        if ($_fileType == self::FILE_ISP || $_fileType == self::FILE_ISP || $_fileType == self::FILE_ORGANIZATION || $_fileType == self::FILE_NETSPEED || $_fileType == self::FILE_CITYBLOCKS || $_fileType == self::FILE_CITYLOCATION) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the Default CVS Location for a GeoIP File Type
     *
     * @author Varun Shoor
     * @param string $_fileType GeoIP File Type
     * @return mixed array(filePath, geoIPType) on Success, "false" otherwise
     * @throws SWIFT_GeoIP_Exception If Invalid Data is Provided
     */
    public static function GetDefaultCVSFileLocation($_fileType)
    {
        if (!self::IsValidGeoIPFileType($_fileType)) {
            throw new SWIFT_GeoIP_Exception(SWIFT_INVALIDDATA);;
        }

        // First give the preference for the premium CVS file location
        if (file_exists("./" . SWIFT_BASEDIRECTORY . '/' . self::DIRECTORY_GEOIP . "/" . self::DIRECTORY_PREMIUM . "/" . $_fileType . "." . self::FILE_EXTENSION)) {
            return array("./" . SWIFT_BASEDIRECTORY . '/' . self::DIRECTORY_GEOIP . "/" . self::DIRECTORY_PREMIUM . "/" . $_fileType . "." . self::FILE_EXTENSION, self::GEOIP_PREMIUM);

            // Next up is Lite CVS location
        } else if (file_exists("./" . SWIFT_BASEDIRECTORY . '/' . self::DIRECTORY_GEOIP . "/" . self::DIRECTORY_LITE . "/" . $_fileType . "." . self::FILE_EXTENSION)) {
            return array("./" . SWIFT_BASEDIRECTORY . '/' . self::DIRECTORY_GEOIP . "/" . self::DIRECTORY_LITE . "/" . $_fileType . "." . self::FILE_EXTENSION, self::GEOIP_LITE);
        }

        return false;
    }

    /**
     * Retrieves the Default Package GeoIP File Location
     *
     * @author Varun Shoor
     * @param string $_fileType GeoIP File Type
     * @return mixed array(filePath, geoIPType) on Success, "false" otherwise
     * @throws SWIFT_GeoIP_Exception If Invalid Data is Provided
     */
    public static function GetDefaultFileLocation($_fileType)
    {
        if (!self::IsValidGeoIPFileType($_fileType)) {
            throw new SWIFT_GeoIP_Exception(SWIFT_INVALIDDATA);
        }

        // We now look for hash'ed files in ./geoip
        $_fileList = self::GetDataDirectoryList();
        foreach ($_fileList as $key => $val) {
            // If the last characters are like isp.csv then this is the one..
            $_fileLastToken = $_fileType . "." . self::FILE_EXTENSION;
            if (strtolower(substr($val, strlen($val) - strlen($_fileLastToken), strlen($val))) == $_fileLastToken) {
                return array('./' . SWIFT_BASEDIRECTORY . '/' . self::DIRECTORY_GEOIP . '/' . $val, IIF(stristr($val, self::DIRECTORY_PREMIUM), self::GEOIP_PREMIUM, self::GEOIP_LITE));
            }
        }
    }

    /**
     * Parses the CSV File with the given prcessing options
     *
     * @author Varun Shoor
     * @param string $_fileName The file name to process
     * @param int $_length The Maximum Length to Process
     * @param string $_delimiter The Separated Value Delimiter
     * @param string $_enclosure The Separated Value Enclosure Character
     * @param int $_offset The offset to begin from
     * @param int $_lineLimit The maximum number of lines to process
     * @return bool|array
     */
    protected static function ParseCSVFile($_fileName, $_length = 2000, $_delimiter = ',', $_enclosure = '"', $_offset = 0, $_lineLimit = 1000)
    {
        $_lineCount = 0;
        $_currentOffset = -1;

        $_csvHandle = fopen($_fileName, "r");
        if (!$_csvHandle) {
            return false;
        }

        fseek($_csvHandle, $_offset, SEEK_SET);
        $_fileSize = filesize($_fileName);

        $_storage = array();

        while (($_data = fgetcsv($_csvHandle, $_length, $_delimiter, $_enclosure)) !== FALSE) {
            $_lineCount++;
            $_currentOffset = ftell($_csvHandle);

            $_storage[] = $_data;

            if ($_lineCount >= $_lineLimit) {
                break;
            }
        }

        fclose($_csvHandle);

        if ($_currentOffset == $_fileSize) {
            $_currentOffset = -1;
        }

        return array($_currentOffset, $_storage);
    }

    /**
     * Retrieves the CSV files in ./geoip directory
     *
     * @author Varun Shoor
     * @return array Files Available in GeoIP Directory
     */
    protected static function GetDataDirectoryList()
    {
        $_fileList = array();

        if ($_directoryHandle = opendir('./' . SWIFT_BASEDIRECTORY . '/' . self::DIRECTORY_GEOIP)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                $_pathInfoContainer = pathinfo('./' . SWIFT_BASEDIRECTORY . '/' . self::DIRECTORY_GEOIP . '/' . $_fileName);
                if ($_fileName != "." && $_fileName != ".." && isset($_pathInfoContainer['extension']) && strtolower($_pathInfoContainer['extension']) == self::FILE_EXTENSION) {
                    $_fileList[] = $_fileName;
                }
            }
            closedir($_directoryHandle);
        }

        return $_fileList;
    }

    /**
     * Import the Database
     *
     * @author Varun Shoor
     * @param string $_fileType GeoIP File Type
     * @param int $_passLimit The Pass Limit
     * @param int $_lineLimit The Line Processing Limit
     * @param int $_offset The Offset to Begin From
     * @return bool|array
     * @throws SWIFT_GeoIP_Exception If Invalid Data is Provided
     */
    public static function Import($_fileType, $_passLimit, $_lineLimit, $_offset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidGeoIPFileType($_fileType)) {
            throw new SWIFT_GeoIP_Exception(SWIFT_INVALIDDATA);
        }

        if ($_offset == -1) {
            return false;
        }

        if (empty($_passLimit)) {
            $_passLimit = self::PASS_LIMIT;
        }

        if (empty($_lineLimit)) {
            $_lineLimit = self::LINE_LIMIT;
        }

        $_fieldCount = 0;
        $_fileReturn = [];
        $_sql = '';

        switch ($_fileType) {
            case self::FILE_ISP:
                $_fileReturn = SWIFT_GeoIPInternetServiceProvider::GetFile();
                $_sql = "INSERT IGNORE INTO " . TABLE_PREFIX . "geoipisp%d (ipfrom, ipto, isp) VALUES (" . $_SWIFT->Database->Param(0) . "," . $_SWIFT->Database->Param(1) . "," . $_SWIFT->Database->Param(2) . ")";
                $_fieldCount = 3;

                break;

            case self::FILE_ORGANIZATION:
                $_fileReturn = SWIFT_GeoIPOrganization::GetFile();
                $_sql = "INSERT IGNORE INTO " . TABLE_PREFIX . "geoiporganization%d (ipfrom, ipto, organization) VALUES (" . $_SWIFT->Database->Param(0) . "," . $_SWIFT->Database->Param(1) . "," . $_SWIFT->Database->Param(2) . ")";
                $_fieldCount = 3;

                break;

            case self::FILE_NETSPEED:
                $_fileReturn = SWIFT_GeoIPNetSpeed::GetFile();
                $_sql = "INSERT IGNORE INTO " . TABLE_PREFIX . "geoipnetspeed%d (ipfrom, ipto, netspeed) VALUES (" . $_SWIFT->Database->Param(0) . "," . $_SWIFT->Database->Param(1) . "," . $_SWIFT->Database->Param(2) . ")";
                $_fieldCount = 3;

                break;

            case self::FILE_CITYBLOCKS:
                $_fileReturn = SWIFT_GeoIPCity::GetFileCityBlocks();
                $_sql = "INSERT IGNORE INTO " . TABLE_PREFIX . "geoipcityblocks%d (ipfrom, ipto, blockid) VALUES (" . $_SWIFT->Database->Param('0') . "," . $_SWIFT->Database->Param('1') . "," . $_SWIFT->Database->Param('2') . ")";
                $_fieldCount = 3;

                break;

            case self::FILE_CITYLOCATION:
                $_fileReturn = SWIFT_GeoIPCity::GetFileCityLocation();
                $_sql = "INSERT IGNORE INTO " . TABLE_PREFIX . "geoipcities (blockid, country, region, city, postalcode, latitude, longitude, metrocode, areacode) VALUES (" . $_SWIFT->Database->Param('0') . "," . $_SWIFT->Database->Param(1) . "," . $_SWIFT->Database->Param(2) . "," . $_SWIFT->Database->Param(3) . "," . $_SWIFT->Database->Param(4) . "," . $_SWIFT->Database->Param(5) . "," . $_SWIFT->Database->Param(6) . "," . $_SWIFT->Database->Param(7) . "," . $_SWIFT->Database->Param(8) . ")";
                $_fieldCount = 9;

                break;

            default:
                break;
        }

        $_fileName = $_fileReturn[0];
        if (!$_fileName) {
            return false;
        }

        $_linesProcessed = 0;

        for ($ii = 0; $ii < $_passLimit; $ii++) {
            $_csvData = self::ParseCSVFile($_fileName, 1000, ',', '"', $_offset, $_lineLimit);

            if (isset($_csvData[1][0][0]) && (stristr($_csvData[1][0][0], 'Copyright (c)') || stristr($_csvData[1][0][0], 'postalCode'))) {
                $_csvData[1] = array_slice($_csvData[1], 1);
            }

            /*
             * BUG FIX - Parminder Singh
             *
             * SWIFT-1925 Trying to update GeoIP index, get this error
             *
             */
            if (isset($_csvData[1][0][0]) && (stristr($_csvData[1][0][0], 'locId') || stristr($_csvData[1][0][0], 'startIpNum'))) {
                $_csvData[1] = array_slice($_csvData[1], 1);
            }

            $_totalLines = count($_csvData[1]);

            $_linesProcessed += $_totalLines;
            if ($_totalLines) {
                $_finalSQL = $_sql;

                // No Sharding in City Locations
                if ($_fileType == self::FILE_CITYLOCATION) {
                    $_SWIFT->Database->StartTrans();
                    $_SWIFT->Database->Execute($_finalSQL, $_csvData[1]);
                    $_SWIFT->Database->CompleteTrans();
                } else {
                    $_finalCSVContainer = array();
                    foreach ($_csvData[1] as $_index => $_value) {
                        if (count($_value) != $_fieldCount) {
                            continue;
                        }

                        $_tableID = self::GetTableID($_value[1]);
                        if (!isset($_finalCSVContainer[$_tableID])) {
                            $_finalCSVContainer[$_tableID] = array();
                        }

                        $_finalCSVContainer[$_tableID][$_index] = $_value;
                    }

                    foreach ($_finalCSVContainer as $_tableID => $_valueContainer) {
                        $_finalSQL = sprintf($_sql, $_tableID);

                        $_SWIFT->Database->StartTrans();
                        $_SWIFT->Database->Execute($_finalSQL, $_valueContainer);
                        $_SWIFT->Database->CompleteTrans();
                    }
                }
            }

            $_offset = $_csvData[0];

            // File end reached?
            if ($_offset == -1) {
                break;
            }
        }

        return array($_offset, $_linesProcessed);
    }

    /**
     * Processes the GeoIP Notifications for the Dashboard
     *
     * @author Varun Shoor
     * @return mixed "_geoIPContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetDashboardContainer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_unbuiltDatabaseList = $_outOfDateList = $_geoIPContainer = array();
        $_outOfDateIndex = $_unbuiltDatabaseListIndex = 1;

        $_fileCityLocations = SWIFT_GeoIPCity::GetFileCityLocation();
        if ($_fileCityLocations) {
            if (!$_SWIFT->Settings->GetKey('geoip', 'citylocations')) {
                $_unbuiltDatabaseList[] = $_unbuiltDatabaseListIndex . '. ' . $_SWIFT->Language->Get('tabcitylocations');

                $_unbuiltDatabaseListIndex++;
            } else if ($_SWIFT->Settings->GetKey('geoip', 'citylocations_filemtime') != filemtime($_fileCityLocations[0])) {
                $_outOfDateList[] = $_outOfDateIndex . '. ' . $_SWIFT->Language->Get('tabcitylocations');

                $_outOfDateIndex++;
            }
        }

        $_fileCityBlocks = SWIFT_GeoIPCity::GetFileCityBlocks();
        if ($_fileCityBlocks) {
            if (!$_SWIFT->Settings->GetKey('geoip', 'cityblocks')) {
                $_unbuiltDatabaseList[] = $_unbuiltDatabaseListIndex . '. ' . $_SWIFT->Language->Get('tabcityblocks');

                $_unbuiltDatabaseListIndex++;
            } else if ($_SWIFT->Settings->GetKey('geoip', 'cityblocks_filemtime') != filemtime($_fileCityBlocks[0])) {
                $_outOfDateList[] = $_outOfDateIndex . '. ' . $_SWIFT->Language->Get('tabcityblocks');

                $_outOfDateIndex++;
            }
        }

        $_fileNetspeed = SWIFT_GeoIPNetSpeed::GetFile();
        if ($_fileNetspeed) {
            if (!$_SWIFT->Settings->GetKey('geoip', 'netspeed')) {
                $_unbuiltDatabaseList[] = $_unbuiltDatabaseListIndex . '. ' . $_SWIFT->Language->Get('tabnetspeed');

                $_unbuiltDatabaseListIndex++;
            } else if ($_SWIFT->Settings->GetKey('geoip', 'netspeed_filemtime') != filemtime($_fileNetspeed[0])) {
                $_outOfDateList[] = $_outOfDateIndex . '. ' . $_SWIFT->Language->Get('tabnetspeed');

                $_outOfDateIndex++;
            }
        }

        $_fileISP = SWIFT_GeoIPInternetServiceProvider::GetFile();
        if ($_fileISP) {
            if (!$_SWIFT->Settings->GetKey('geoip', 'isp')) {
                $_unbuiltDatabaseList[] = $_unbuiltDatabaseListIndex . '. ' . $_SWIFT->Language->Get('tabisp');

                $_unbuiltDatabaseListIndex++;
            } else if ($_SWIFT->Settings->GetKey('geoip', 'isp_filemtime') != filemtime($_fileISP[0])) {
                $_outOfDateList[] = $_outOfDateIndex . '. ' . $_SWIFT->Language->Get('tabisp');

                $_outOfDateIndex++;
            }
        }

        $_fileOrganization = SWIFT_GeoIPOrganization::GetFile();
        if ($_fileOrganization) {
            if (!$_SWIFT->Settings->GetKey('geoip', 'organization')) {
                $_unbuiltDatabaseList[] = $_unbuiltDatabaseListIndex . '. ' . $_SWIFT->Language->Get('taborganization');

                $_unbuiltDatabaseListIndex++;
            } else if ($_SWIFT->Settings->GetKey('geoip', 'organization_filemtime') != filemtime($_fileOrganization[0])) {
                $_outOfDateList[] = $_outOfDateIndex . '. ' . $_SWIFT->Language->Get('taborganization');

                $_outOfDateIndex++;
            }
        }

        if (count($_outOfDateList)) {
            $_geoIPContainer[] = array('title' => $_SWIFT->Language->Get('titlenotuptodategeoip'), 'contents' => $_SWIFT->Language->Get('msgnotuptodategeoip') . '<BR />' . implode('<BR />', $_outOfDateList));
        }

        if (count($_unbuiltDatabaseList)) {
            $_geoIPContainer[] = array('title' => $_SWIFT->Language->Get('titlenotbuiltgeoip'), 'contents' => $_SWIFT->Language->Get('msgnotbuiltgeoip') . '<BR />' . implode('<BR />', $_unbuiltDatabaseList));
        }

        return $_geoIPContainer;
    }

    /**
     * Converts a dotted IP to Long
     *
     * @author Varun Shoor
     * @param string $_dottedIP The IP (0.0.0.0)
     * @return string
     */
    public static function IPToLong($_dottedIP)
    {
        return sprintf("%u", ip2long($_dottedIP));
    }

    /**
     * Converts Long to IP
     *
     * @author Varun Shoor
     * @param int $_longIP The IP as a Number
     * @return string
     */
    public static function LongToIP($_longIP)
    {
        return long2ip($_longIP);
    }

    /**
     * Retrieve the GeoIP Details for a given ip based on certain flags
     *
     * @author Varun Shoor
     * @param string $_ipAddress The IP Address to Get Data Of
     * @param array $_flags The GeoIP Flags
     * @return array
     */
    public static function GetIPDetails($_ipAddress, $_flags)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (defined('SWIFT_GEOIP_SERVER')) {
            return self::GetIPDetailsFromServer(SWIFT_GEOIP_SERVER, $_ipAddress, $_flags);
        }

        $_ipLong = $_SWIFT->Database->Escape(self::IPToLong($_ipAddress));

        $_tableID = self::GetTableID($_ipLong);

        $_returnResult = array();

        if (in_array(self::GEOIP_ISP, $_flags) && $_SWIFT->Settings->GetKey('geoip', 'isp')) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipisp" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['isp']) && $_result['ipfrom'] <= $_ipLong) {
                $_returnResult[self::GEOIP_ISP] = $_result['isp'];
            }
        }

        if (in_array(self::GEOIP_ORGANIZATION, $_flags) && $_SWIFT->Settings->GetKey('geoip', 'organization')) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoiporganization" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['organization']) && $_result['ipfrom'] <= $_ipLong) {
                $_returnResult[self::GEOIP_ORGANIZATION] = $_result['organization'];
            }
        }

        if (in_array(self::GEOIP_NETSPEED, $_flags) && $_SWIFT->Settings->GetKey('geoip', 'netspeed')) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipnetspeed" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['netspeed']) && $_result['ipfrom'] <= $_ipLong) {
                $_returnResult[self::GEOIP_NETSPEED] = $_result['netspeed'];
            }
        }

        if (in_array(self::GEOIP_CITY, $_flags) && $_SWIFT->Settings->GetKey('geoip', 'citylocations') && $_SWIFT->Settings->GetKey('geoip', 'cityblocks')) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipcityblocks" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['blockid']) && $_result['ipfrom'] <= $_ipLong) {
                $_cityResult = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipcities WHERE blockid = '" . (int)($_result['blockid']) . "'");
                if (!empty($_cityResult['blockid'])) {
                    $_returnResult[self::GEOIP_CITY] = array('blockid' => $_cityResult['blockid'], 'country' => $_cityResult['country'],
                        'region' => $_cityResult['region'], 'city' => $_cityResult['city'], 'postalcode' => $_cityResult['postalcode'],
                        'latitude' => $_cityResult['latitude'], 'longitude' => $_cityResult['longitude'], 'metrocode' => $_cityResult['metrocode'],
                        'areacode' => $_cityResult['areacode']);
                }
            }
        }

        return $_returnResult;
    }

    /**
     * Retrieve the GeoIP Details for a given ip based on certain flags from a remote server
     *
     * @author Varun Shoor
     * @param string $_serverPath
     * @param string $_ipAddress The IP Address to Get Data Of
     * @param array $_flags The GeoIP Flags
     * @return array
     */
    public static function GetIPDetailsFromServer($_serverPath, $_ipAddress, $_flags)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalDispatchList = array();
        $_finalDispatchList[] = 'ipaddress=' . urlencode($_ipAddress);

        foreach ($_flags as $_flag) {
            $_finalDispatchList[] = 'flag[]=' . urlencode($_flag);
        }

        $_finalDispatchQuery = implode('&', $_finalDispatchList);

        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_URL, $_serverPath);
        curl_setopt($_curlHandle, CURLOPT_TIMEOUT, 2);
        curl_setopt($_curlHandle, CURLOPT_USERAGENT, 'SWIFT_GeoIP');
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($_curlHandle, CURLOPT_POST, true);
        curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, $_finalDispatchQuery);
        curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, true);

        $_returnData = curl_exec($_curlHandle);
        if (curl_errno($_curlHandle)) {
            return array();
        } else {
            curl_close($_curlHandle);
        }

        $_XMLObject = @simplexml_load_string($_returnData);
        if (!isset($_XMLObject->GeoIPResult)) {
            return array();
        }

        $_returnResult = array();

        if (in_array(self::GEOIP_ISP, $_flags) && isset($_XMLObject->GeoIPResult->ISP)) {
            $_returnResult[self::GEOIP_ISP] = (string)$_XMLObject->GeoIPResult->ISP;
        }

        if (in_array(self::GEOIP_ORGANIZATION, $_flags) && isset($_XMLObject->GeoIPResult->Organization)) {
            $_returnResult[self::GEOIP_ORGANIZATION] = (string)$_XMLObject->GeoIPResult->Organization;
        }

        if (in_array(self::GEOIP_NETSPEED, $_flags) && isset($_XMLObject->GeoIPResult->NetSpeed)) {
            $_returnResult[self::GEOIP_NETSPEED] = (string)$_XMLObject->GeoIPResult->NetSpeed;
        }

        if (in_array(self::GEOIP_CITY, $_flags) && isset($_XMLObject->GeoIPResult->City)) {
            $_cityResult = array();
            $_cityResult['blockid'] = (string)$_XMLObject->GeoIPResult->City->BlockID;
            $_cityResult['country'] = (string)$_XMLObject->GeoIPResult->City->Country;
            $_cityResult['region'] = (string)$_XMLObject->GeoIPResult->City->Region;
            $_cityResult['city'] = (string)$_XMLObject->GeoIPResult->City->Name;
            $_cityResult['postalcode'] = (string)$_XMLObject->GeoIPResult->City->PostalCode;
            $_cityResult['latitude'] = (string)$_XMLObject->GeoIPResult->City->Latitude;
            $_cityResult['longitude'] = (string)$_XMLObject->GeoIPResult->City->Longitude;
            $_cityResult['metrocode'] = (string)$_XMLObject->GeoIPResult->City->MetroCode;
            $_cityResult['areacode'] = (string)$_XMLObject->GeoIPResult->City->AreaCode;
            $_returnResult[self::GEOIP_CITY] = $_cityResult;
        }

        return $_returnResult;
    }


    /**
     * Retrieve the GeoIP Details for a given ip based on certain flags and dispatch as XML
     *
     * @author Varun Shoor
     * @param string $_ipAddress The IP Address to Get Data Of
     * @param array $_flags The GeoIP Flags
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DispatchIPDetailsAsXML($_ipAddress, $_flags)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_XMLObject = new SWIFT_XML();

        $_ipLong = $_SWIFT->Database->Escape(self::IPToLong($_ipAddress));

        $_tableID = self::GetTableID($_ipLong);

        $_SWIFT_XMLObject->AddParentTag('GeoIP');
        $_SWIFT_XMLObject->AddParentTag('GeoIPResult');

        if (in_array(self::GEOIP_ISP, $_flags)) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipisp" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['isp']) && $_result['ipfrom'] <= $_ipLong) {
                $_SWIFT_XMLObject->AddTag('ISP', $_result['isp']);
            }
        }

        if (in_array(self::GEOIP_ORGANIZATION, $_flags)) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoiporganization" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['organization']) && $_result['ipfrom'] <= $_ipLong) {
                $_SWIFT_XMLObject->AddTag('Organization', $_result['organization']);
            }
        }

        if (in_array(self::GEOIP_NETSPEED, $_flags)) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipnetspeed" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['netspeed']) && $_result['ipfrom'] <= $_ipLong) {
                $_SWIFT_XMLObject->AddTag('NetSpeed', $_result['netspeed']);
            }
        }

        if (in_array(self::GEOIP_CITY, $_flags)) {
            $_result = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipcityblocks" . $_tableID . " WHERE ipto >= '" . $_ipLong . "' ORDER BY ipto ASC");
            if (!empty($_result['blockid']) && $_result['ipfrom'] <= $_ipLong) {
                $_cityResult = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "geoipcities WHERE blockid = '" . (int)($_result['blockid']) . "'");
                if (!empty($_cityResult['blockid'])) {
                    $_SWIFT_XMLObject->AddParentTag('City');

                    $_SWIFT_XMLObject->AddTag('BlockID', $_cityResult['blockid']);
                    $_SWIFT_XMLObject->AddTag('Country', $_cityResult['country']);
                    $_SWIFT_XMLObject->AddTag('Region', $_cityResult['region']);
                    $_SWIFT_XMLObject->AddTag('Name', $_cityResult['city']);
                    $_SWIFT_XMLObject->AddTag('PostalCode', $_cityResult['postalcode']);
                    $_SWIFT_XMLObject->AddTag('Latitude', $_cityResult['latitude']);
                    $_SWIFT_XMLObject->AddTag('Longitude', $_cityResult['longitude']);
                    $_SWIFT_XMLObject->AddTag('MetroCode', $_cityResult['metrocode']);
                    $_SWIFT_XMLObject->AddTag('AreaCode', $_cityResult['areacode']);

                    $_SWIFT_XMLObject->EndParentTag('City');
                }
            }
        }

        $_SWIFT_XMLObject->EndParentTag('GeoIPResult');
        $_SWIFT_XMLObject->EndParentTag('GeoIP');

        $_SWIFT_XMLObject->EchoXML();

        return true;
    }

    /**
     * Retrieve the Table ID
     *
     * @author Varun Shoor
     * @param int $_longIP The Long IP From
     * @return int The Table ID
     * @throws SWIFT_Exception If Invalid Data is Provdied
     */
    public static function GetTableID($_longIP)
    {
        if ($_longIP < 1070000000) {
            return 1;
        } else if ($_longIP >= 1070000000 && $_longIP < 1105000000) {
            return 2;
        } else if ($_longIP >= 1105000000 && $_longIP < 1150000000) {
            return 3;
        } else if ($_longIP >= 1150000000 && $_longIP < 1240000000) {
            return 4;
        } else if ($_longIP >= 1240000000 && $_longIP < 1370000000) {
            return 5;
        } else if ($_longIP >= 1370000000 && $_longIP < 1500000000) {
            return 6;
        } else if ($_longIP >= 1500000000 && $_longIP < 3280000000) {
            return 7;
        } else if ($_longIP >= 3280000000 && $_longIP < 3540000000) {
            return 8;
        } else if ($_longIP >= 3540000000 && $_longIP < 3640000000) {
            return 9;
        } else if ($_longIP >= 3400000000) {
            return 10;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }
}

?>
