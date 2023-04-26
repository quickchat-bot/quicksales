<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Dynect Record Base Class
 *
 * @author Ravinder Singh
 */
abstract class SWIFT_DynectRecord extends SWIFT_DynectBase
{
    /**
     * @var SWIFT_DynectZone
     */
    protected $DynectZone;

    protected $_rData;
    protected $_ttlSeconds;
    protected $_FQDN;

    const DEFAULT_TTL = 600;

    const TTL_ONEMINUTE      = 60;
    const TTL_FIVEMINUTES    = 300;
    const TTL_TENMINUTES     = 600;
    const TTL_FIFTEENMINUTES = 900;
    const TTL_HALFHOUR       = 1800;
    const TTL_ONEHOUR        = 3600;
    const TTL_TWOHOURS       = 7200;
    const TTL_FOURHOURS      = 14400;
    const TTL_SIXHOURS       = 21600;
    const TTL_TWELVEHOURS    = 43200;
    const TTL_ONEDAY         = 86400;
    const TTL_TWODAYS        = 172800;
    const TTL_FIVEDAYS       = 432000;
    const TTL_ONEWEEK        = 604800;


    /**
     * Constructor
     *
     * Valid TTL Values:
     * 30 => 30 sec
     * 60 => 1 min
     * 150 => 2 1/2 min
     * 300 => 5 min
     * 450 => 7 1/2 min
     * 600 => 10 min
     * 900 => 15 min
     * 1800 => 30 min
     * 3600 => 1 hour
     * 7200 => 2 hours
     * 14400 => 4 hours
     * 21600 => 6 hours
     * 43200 => 12 hours
     * 86400 => 1 day
     * 172800 => 2 days
     * 432000 => 5 days
     * 604800 => 1 week
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN       The Fully Qualified Domain Name for the record
     * @param array            $_rData      The Record Data
     * @param int              $_ttlSeconds (OPTIONAL) The TTL value. Only use valid TTL values!
     *
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds = self::DEFAULT_TTL)
    {
        parent::__construct();

        if (!$_DynectZoneObject instanceof SWIFT_DynectZone || !$_DynectZoneObject->GetIsClassLoaded() || !count($_rData) || empty($_FQDN)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->SetDynectZone($_DynectZoneObject);
        $this->SetRData($_rData);
        $this->SetTTLSeconds($_ttlSeconds);
        $this->SetFQDN($_FQDN);
    }

    /**
     * Check and see if the TTL second values are correct
     *
     * @author Varun Shoor
     *
     * @param int $_ttlSeconds
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidTTLSeconds($_ttlSeconds)
    {
        return ($_ttlSeconds == 30 || $_ttlSeconds == self::TTL_ONEMINUTE || $_ttlSeconds == 150 || $_ttlSeconds == self::TTL_FIVEMINUTES || $_ttlSeconds == 450 || $_ttlSeconds == self::TTL_TENMINUTES
            || $_ttlSeconds == self::TTL_FIFTEENMINUTES || $_ttlSeconds == self::TTL_HALFHOUR || $_ttlSeconds == self::TTL_ONEHOUR || $_ttlSeconds == self::TTL_TWOHOURS
            || $_ttlSeconds == self::TTL_FOURHOURS || $_ttlSeconds == self:: TTL_SIXHOURS || $_ttlSeconds == self::TTL_TWELVEHOURS || $_ttlSeconds == self::TTL_ONEDAY
            || $_ttlSeconds == self::TTL_TWODAYS || $_ttlSeconds == self::TTL_FIVEDAYS || $_ttlSeconds == self::TTL_ONEWEEK);
    }

    /**
     * Set the Rdata
     *
     * @author Ravinder Singh
     *
     * @param array $_rData The array containing Record Data
     *
     * @return SWIFT_DynectRecord
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetRData($_rData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_rData = $_rData;

        return $this;
    }

    /**
     * Retrieve the currently set Record Data
     *
     * @author Ravinder Singh
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_rData;
    }

    /**
     * Set the TTL value in seconds
     *
     * Valid TTL Values:
     * 30 => 30 sec
     * 60 => 1 min
     * 150 => 2 1/2 min
     * 300 => 5 min
     * 450 => 7 1/2 min
     * 600 => 10 min
     * 900 => 15 min
     * 1800 => 30 min
     * 3600 => 1 hour
     * 7200 => 2 hours
     * 14400 => 4 hours
     * 21600 => 6 hours
     * 43200 => 12 hours
     * 86400 => 1 day
     * 172800 => 2 days
     * 432000 => 5 days
     * 604800 => 1 week
     *
     * @author Ravinder Singh
     *
     * @param int $_ttlSeconds Value of TTL in seconds. Only use valid TTL values!
     *
     * @return SWIFT_DynectRecord
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetTTLSeconds($_ttlSeconds)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidTTLSeconds($_ttlSeconds)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $this->_ttlSeconds = $_ttlSeconds;

        return $this;
    }

    /**
     * Retrieve the currently set TTL value
     *
     * @author Ravinder Singh
     * @return int _ttlSeconds
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTTLSeconds()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_ttlSeconds;
    }

    /**
     * Set the zone object
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     *
     * @return SWIFT_DynectRecord
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetDynectZone(SWIFT_DynectZone $_DynectZoneObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_DynectZoneObject instanceof SWIFT_DynectZone || !$_DynectZoneObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->DynectZone = $_DynectZoneObject;

        return $this;
    }

    /**
     * Retrieve the currently set zone object
     *
     * @author Ravinder Singh
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDynectZone()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->DynectZone;
    }

    /**
     * Set the FQDN
     *
     * @author Ravinder Singh
     *
     * @param string $_FQDN The Fully Qualified Domain Name
     *
     * @return SWIFT_DynectRecord
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetFQDN($_FQDN)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_FQDN = $_FQDN;

        return $this;
    }

    /**
     * Retrieve the currently set FQDN
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFQDN()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_FQDN;
    }

    /**
     * Create an DNS Record
     *
     * @author Ravinder Singh
     *
     * @param string           $_baseURL
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_rData
     * @param int              $_ttlSeconds
     *
     * @return object $_JSONObject the JSON Object containing the response
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CreateBase($_baseURL, SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds = self::DEFAULT_TTL)
    {
        if (empty($_baseURL) || !$_DynectZoneObject instanceof SWIFT_DynectZone || !$_DynectZoneObject->GetIsClassLoaded() || empty($_FQDN) || !_is_array($_rData) || !self::IsValidTTLSeconds($_ttlSeconds)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject = self::GetBase()->Post($_baseURL . '/' . $_DynectZoneObject->GetName() . '/' . $_FQDN . '/', array('rdata' => $_rData, 'ttl' => $_ttlSeconds));

        if (!self::GetBase()->CheckResponse($_ResponseObject, 'SWIFT_DynectRecord::Create', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $_JSONObject = $_ResponseObject->GetBodyJSONObject();

        if (!isset($_JSONObject->data->fqdn)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_JSONObject;
    }

    /**
     * Retrieve the Record Information
     *
     * @author Ravinder Singh
     *
     * @param string           $_baseURL
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_recordID
     *
     * @return object $_JSONObject The JSON object containing the response
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_baseURL, $_DynectZoneObject, $_FQDN, $_recordID)
    {
        if (empty($_baseURL) || !$_DynectZoneObject instanceof SWIFT_DynectZone || !$_DynectZoneObject->GetIsClassLoaded() || empty($_FQDN) || trim($_recordID) == '') {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject = self::GetBase()->Get($_baseURL . '/' . $_DynectZoneObject->GetName() . '/' . $_FQDN . '/' . $_recordID);
        if (!self::GetBase()->CheckResponse($_ResponseObject, 'SWIFT_DynectRecord::Retrieve', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();
        if (!isset($_JSONObject->data->zone)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_JSONObject;
    }

    /**
     * Retrieve records for Zone + FQDN
     *
     * @author Ravinder Singh
     *
     * @param string           $_baseURL
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     *
     * @return object $_JSONObject The JSON object containing the response
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAll($_baseURL, $_DynectZoneObject, $_FQDN)
    {
        if (empty($_baseURL) || !$_DynectZoneObject instanceof SWIFT_DynectZone || !$_DynectZoneObject->GetIsClassLoaded() || empty($_FQDN)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject = self::GetBase()->Get($_baseURL . '/' . $_DynectZoneObject->GetName() . '/' . $_FQDN);
        if (!self::GetBase()->CheckResponse($_ResponseObject, 'SWIFT_DynectRecord::RetrieveAll', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();
        if (!is_array($_JSONObject->data)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $_JSONObject;
    }

    /**
     * Delete the DNS record
     *
     * @author Ravinder Singh
     *
     * @param string $_baseURL
     * @param string $_recordID
     *
     * @return SWIFT_DynectRecord|SWIFT_RESTResponse
     * @throws SWIFT_Exception If Class Not Loaded or Invalid Data Provided
     */
    public function Delete($_baseURL, $_recordID = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ResponseObject = parent::Delete($_baseURL . '/' . $this->DynectZone->GetName() . '/' . $this->GetFQDN() . '/' . $_recordID);
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectRecord::Delete', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->DynectZone->Publish();

        return $this;
    }

    /**
     * Update The DNS record
     *
     * @author Ravinder Singh
     *
     * @param string $_baseURL
     * @param string $_recordID (OPTIONAL)
     *
     * @return SWIFT_DynectRecord
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function Update($_baseURL, $_recordID = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ResponseObject = $this->Put($_baseURL . '/' . $this->DynectZone->GetName() . '/' . $this->GetFQDN() . '/' . $_recordID, array('rdata' => $this->GetRData()));
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectRecord::Update', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();

        if (!isset($_JSONObject->data->zone)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->DynectZone->Publish();

        return $this;
    }
}
