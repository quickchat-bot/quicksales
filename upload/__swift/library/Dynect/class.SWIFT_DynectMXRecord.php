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
 * @copyright      Copyright (c) 2001-2013, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The NS record management class
 *
 * @author Ravinder Singh
 */
class SWIFT_DynectMXRecord extends SWIFT_DynectRecord
{
    const BASE_URL = 'MXRecord';

    /**
     * Constructor
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_exchange
     * @param string           $_preference Preference value of MX
     * @param int              $_ttlSeconds (OPTIONAL) The TTL value. Only use the following values!
     *
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_exchange, $_preference, $_ttlSeconds = self::DEFAULT_TTL)
    {
        if (empty($_exchange)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_rData = array('exchange' => $_exchange, 'preference' => $_preference);

        parent::__construct($_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds);
    }

    /**
     * Set the Mail Exchange
     *
     * @author Ravinder Singh
     *
     * @param string $_exchange
     *
     * @return SWIFT_DynectMXRecord
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetExchange($_exchange)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_exchange)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $this->SetRData(array('exchange' => $_exchange, 'preference' => $this->GetPreference()));

        return $this;
    }

    /**
     * Retrieve the currently set Mail Exchange
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetExchange()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rData = $this->GetRData();

        return $_rData['exchange'];
    }

    /**
     * Set the Mail Exchange Preference
     *
     * @author Ravinder Singh
     *
     * @param string $_preference
     *
     * @return SWIFT_DynectMXRecord
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetPreference($_preference)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->SetRData(array('exchange' => $this->GetExchange(), 'preference' => $_preference));

        return $this;
    }

    /**
     * Retrieve the currently set Mail Exchange Preference
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPreference()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rData = $this->GetRData();

        return $_rData['preference'];
    }

    /**
     * Create an MX record
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_exchange
     * @param string           $_preference The MX Preference
     * @param int              $_ttlSeconds (OPTIONAL) The TTL Seconds. Only use valid TTL Values!
     *
     * @return SWIFT_DynectMXRecord
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Create(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_exchange, $_preference, $_ttlSeconds = self::DEFAULT_TTL)
    {
        $_rData = array('exchange' => $_exchange, 'preference' => $_preference);

        $_JSONObject = parent::CreateBase(self::BASE_URL, $_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds);

        print_r($_JSONObject);

        $_zoneName                   = isset($_JSONObject->data->zone) ? (string) $_JSONObject->data->zone : '';
        $_FQDN                       = isset($_JSONObject->data->fqdn) ? (string) $_JSONObject->data->fqdn : '';
        $_exchange                   = isset($_JSONObject->data->rdata->exchange) ? (string) $_JSONObject->data->rdata->exchange : '';
        $_preference                 = isset($_JSONObject->data->rdata->preference) ? (string) $_JSONObject->data->rdata->preference : '';
        $_ttlSeconds                 = isset($_JSONObject->data->ttl) ? (string) $_JSONObject->data->ttl : '';
        $_SWIFT_DynectMXRecordObject = new SWIFT_DynectMXRecord($_DynectZoneObject, $_FQDN, $_exchange, $_preference, $_ttlSeconds);

        $_DynectZoneObject->Publish();

        return $_SWIFT_DynectMXRecordObject;
    }

    /**
     * Display MX Record Properties
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function __toString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_returnData = 'Zone Name: ' . $this->GetDynectZone()->GetName() . SWIFT_CRLF;
        $_returnData .= 'FQDN: ' . $this->GetFQDN() . SWIFT_CRLF;
        $_rData = $this->GetRData();
        $_returnData .= 'Mail Exchange: ' . $_rData['exchange'] . SWIFT_CRLF;
        $_returnData .= 'Mail Exchange Preference: ' . $_rData['preference'] . SWIFT_CRLF;
        $_returnData .= 'TTL: ' . $this->GetTTLSeconds() . SWIFT_CRLF;

        return $_returnData;
    }

    /**
     * Retrieve the Record Object
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_recordID
     *
     * @return SWIFT_DynectMXRecord
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_DynectZoneObject, $_FQDN, $_recordID, $_ = null)
    {
        $_JSONObject = parent::Retrieve(self::BASE_URL, $_DynectZoneObject, $_FQDN, $_recordID);

        $_zoneName     = isset($_JSONObject->data->zone) ? (string) $_JSONObject->data->zone : '';
        $_FQDN         = isset($_JSONObject->data->fqdn) ? (string) $_JSONObject->data->fqdn : '';
        $_exchange     = isset($_JSONObject->data->rdata->exchange) ? (string) $_JSONObject->data->rdata->exchange : '';
        $_preference   = isset($_JSONObject->data->rdata->preference) ? (string) $_JSONObject->data->rdata->preference : '';
        $_ttlSeconds   = isset($_JSONObject->data->ttl) ? (string) $_JSONObject->data->ttl : '';

        $_SWIFT_DynectMXRecordObject = new SWIFT_DynectMXRecord($_DynectZoneObject, $_FQDN, $_exchange, $_preference, $_ttlSeconds);

        return $_SWIFT_DynectMXRecordObject;
    }

    /**
     * Retrieve the Records
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     *
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAll($_DynectZoneObject, $_FQDN, $_ = null)
    {
        $_JSONObject = parent::RetrieveAll(self::BASE_URL, $_DynectZoneObject, $_FQDN);

        $_recordsURIList = array();

        if(isset($_JSONObject->data)) {
            foreach ($_JSONObject->data as $_val) {
                $_recordsURIList[] = (string)$_val;
            }
        }

        return $_recordsURIList;
    }

    /**
     * Delete the record
     *
     * @author Ravinder Singh
     *
     * @param string $_recordID (OPTIONAL)
     *
     * @return SWIFT_DynectMXRecord
     * @throws SWIFT_Exception If Class is not Loaded or if Invalid Data is Provided
     */
    public function Delete($_recordID = '', $_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::Delete(self::BASE_URL, $_recordID);

        return $this;
    }

    /**
     * Update The record. Use SetExchange/SetPreference/SetTTLSeconds function prior to calling
     *
     * @author Ravinder Singh
     *
     * @param string $_recordID (OPTIONAL)
     *
     * @return SWIFT_DynectMXRecord
     * @throws SWIFT_Exception If Class is not Loaded or if Invalid Data is Provided
     */
    public function Update($_recordID = '', $_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::Update(self::BASE_URL, $_recordID);

        return $this;
    }
}
