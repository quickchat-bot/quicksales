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
 * The Dynect Zone Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_DynectZone extends SWIFT_DynectBase
{
    protected $_zoneName = false;

    protected $_zoneSerial;
    protected $_zoneSerialStyle;
    protected $_zoneType;

    const BASE_URL = 'Zone';

    const ZONESERIAL_INCREMENT = 'increment';
    const ZONESERIAL_EPOCH     = 'epoch';
    const ZONESERIAL_DAY       = 'day';
    const ZONESERIAL_MINUTE    = 'minute';

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param string $_zoneName
     * @param int    $_zoneSerial
     * @param string $_zoneSerialStyle The Zone Serial Style (increment, epoch, day, minute)
     * @param string $_zoneType        The Zone Type (Primary/Secondary)
     *
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_zoneName, $_zoneSerial, $_zoneSerialStyle, $_zoneType)
    {
        parent::__construct();

        $this->SetName($_zoneName);
        $this->SetSerial($_zoneSerial);
        $this->SetSerialStyle($_zoneSerialStyle);
        $this->SetType($_zoneType);
    }

    /**
     * Check and see if its a valid zone serial style
     *
     * @author Varun Shoor
     *
     * @param string $_zoneSerialStyle
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidZoneSerialStyle($_zoneSerialStyle)
    {
        return ($_zoneSerialStyle == self::ZONESERIAL_INCREMENT || $_zoneSerialStyle == self::ZONESERIAL_EPOCH || $_zoneSerialStyle == self::ZONESERIAL_DAY || $_zoneSerialStyle == self::ZONESERIAL_MINUTE);
    }

    /**
     * Set the Zone Serial
     *
     * @author Varun Shoor
     *
     * @param mixed $_zoneSerial
     *
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetSerial($_zoneSerial)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_zoneSerial = (int) ($_zoneSerial);

        $this->_zoneSerial = $_zoneSerial;

        return $this;
    }

    /**
     * Retrieve the currently set Zone Serial
     *
     * @author Varun Shoor
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSerial()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_zoneSerial;
    }

    /**
     * Set the Zone Serial Style
     *
     * @author Varun Shoor
     *
     * @param string $_zoneSerialStyle The Zone Serial Style (increment, epoch, day, minute)
     *
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetSerialStyle($_zoneSerialStyle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidZoneSerialStyle($_zoneSerialStyle)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_zoneSerialStyle = $_zoneSerialStyle;

        return $this;
    }

    /**
     * Retrieve the Zone Serial Style
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSerialStyle()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_zoneSerialStyle;
    }

    /**
     * Set the Zone Type
     *
     * @author Varun Shoor
     *
     * @param string $_zoneType The Zone Type (Primary/Secondary)
     *
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetType($_zoneType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_zoneType) || ($_zoneType != 'Primary' && $_zoneType != 'Secondary')) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_zoneType = $_zoneType;

        return $this;
    }

    /**
     * Retrieve the Zone Type
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_zoneType;
    }

    /**
     * Set the Zone Name
     *
     * @author Varun Shoor
     *
     * @param string $_zoneName The Zone Name
     *
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetName($_zoneName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_zoneName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_zoneName = $_zoneName;

        return $this;
    }

    /**
     * Retrieve the currently set zone name
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_zoneName;
    }

    /**
     * Create a new Zone
     *
     * @author Varun Shoor
     *
     * @param string $_zoneName
     * @param string $_adminContactEmail The Admin Contact Email
     * @param int    $_ttlSeconds        (OPTIONAL) The TTL Seconds. Only use valid TTL values!
     *
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Create($_zoneName, $_adminContactEmail, $_ttlSeconds = 600)
    {
        if (empty($_zoneName) || empty($_adminContactEmail) || empty($_ttlSeconds) || !IsEmailValid($_adminContactEmail)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject = self::GetBase()->Post(self::BASE_URL . '/' . $_zoneName, array('rname' => $_adminContactEmail, 'ttl' => $_ttlSeconds, 'zone' => $_zoneName));
        if (!self::GetBase()->CheckResponse($_ResponseObject, 'SWIFT_DynectZone::Create', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();
        if (!isset($_JSONObject->data->zone)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_zoneName = (string) $_JSONObject->data->zone;
        if (empty($_zoneName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_zoneSerial      = isset($_JSONObject->data->serial) ? (string) $_JSONObject->data->serial : '';
        $_zoneSerialStyle = isset($_JSONObject->data->serial_style) ? (string) $_JSONObject->data->serial_style : '';
        $_zoneType        = isset($_JSONObject->data->zone_type) ? (string) $_JSONObject->data->zone_type : '';

        $_SWIFT_DynectZoneObject = new SWIFT_DynectZone($_zoneName, $_zoneSerial, $_zoneSerialStyle, $_zoneType);

        return $_SWIFT_DynectZoneObject;
    }

    /**
     * Display Zone Properties
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function __toString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_returnData = 'Name: ' . $this->GetName() . SWIFT_CRLF;
        $_returnData .= 'Serial: ' . $this->GetSerial() . SWIFT_CRLF;
        $_returnData .= 'Serial Style: ' . $this->GetSerialStyle() . SWIFT_CRLF;
        $_returnData .= 'Type: ' . $this->GetType() . SWIFT_CRLF;

        return $_returnData;
    }

    /**
     * Retrieve the Zone Object
     *
     * @author Varun Shoor
     *
     * @param string $_zoneName
     *
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_zoneName)
    {
        if (empty($_zoneName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject = self::GetBase()->Get(self::BASE_URL . '/' . $_zoneName);
        if (!self::GetBase()->CheckResponse($_ResponseObject, 'SWIFT_DynectZone::Retrieve', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();
        if (!isset($_JSONObject->data->zone)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_zoneName = (string) $_JSONObject->data->zone;
        if (empty($_zoneName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_zoneSerial      = isset($_JSONObject->data->serial) ? (string) $_JSONObject->data->serial : '';
        $_zoneSerialStyle = isset($_JSONObject->data->serial_style) ? (string) $_JSONObject->data->serial_style : '';
        $_zoneType        = isset($_JSONObject->data->zone_type) ? (string) $_JSONObject->data->zone_type : '';

        $_SWIFT_DynectZoneObject = new SWIFT_DynectZone($_zoneName, $_zoneSerial, $_zoneSerialStyle, $_zoneType);

        return $_SWIFT_DynectZoneObject;
    }

    /**
     * Retrieve all Zones
     *
     * @author Varun Shoor
     * @return array The Zone Name List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAll()
    {
        $_ResponseObject = self::GetBase()->Get(self::BASE_URL);
        if (!self::GetBase()->CheckResponse($_ResponseObject, 'SWIFT_DynectZone::RetrieveAll', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();

        $_zoneList = array();
        if (isset($_JSONObject->data)) {
            foreach ($_JSONObject->data as $_zonePath) {
                $_zoneList[] = StripTrailingSlash(mb_substr($_zonePath, mb_strpos($_zonePath, 'Zone/') + strlen('Zone/')));
            }
        }

        return $_zoneList;
    }

    /**
     * Freeze a Zone
     *
     * @author Varun Shoor
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Freeze()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_argumentContainer = array('freeze' => '1');

        $_ResponseObject = $this->Put(self::BASE_URL . '/' . $this->GetName(), $_argumentContainer);
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectZone::Freeze', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this;
    }

    /**
     * UnFreeze a Zone
     *
     * @author Varun Shoor
     * @return SWIFT_DynectZone
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Thaw()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_argumentContainer = array('thaw' => '1');

        $_ResponseObject = $this->Put(self::BASE_URL . '/' . $this->GetName(), $_argumentContainer);
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectZone::Thaw', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this;
    }

    /**
     * Publish a Zone
     *
     * @author Varun Shoor
     * @return SWIFT_DynectZone|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Publish()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_argumentContainer = array('publish' => '1');

        $_ResponseObject = $this->Put(self::BASE_URL . '/' . $this->GetName(), $_argumentContainer);
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectZone::Publish', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();
        if (!isset($_JSONObject->data->zone)) {
            return false;
        }

        $_zoneName = (string) $_JSONObject->data->zone;
        if (empty($_zoneName)) {
            return false;
        }

        $_zoneSerial      = isset($_JSONObject->data->serial) ? (string) $_JSONObject->data->serial : '';
        $_zoneSerialStyle = isset($_JSONObject->data->serial_style) ? (string) $_JSONObject->data->serial_style : '';
        $_zoneType        = isset($_JSONObject->data->zone_type) ? (string) $_JSONObject->data->zone_type : '';

        return $this;
    }

    /**
     * Delete the Zone
     *
     * @author Varun Shoor
     * @return SWIFT_DynectZone|SWIFT_RESTResponse
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ = null, $__ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ResponseObject = parent::Delete(self::BASE_URL . '/' . $this->GetName());
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectZone::Delete', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject->GetBodyJSONObject();

        return $this;
    }
}
