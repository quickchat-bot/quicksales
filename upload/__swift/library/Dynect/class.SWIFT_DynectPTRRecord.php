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
 * The PTR record management class
 *
 * @author Ravinder Singh
 */
class SWIFT_DynectPTRRecord extends SWIFT_DynectRecord
{
    const BASE_URL = 'PTRRecord';

    /**
     * Constructor
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_PTRDomainName
     * @param int              $_ttlSeconds (OPTIONAL) The TTL value. Only use valid TTL Values!
     *
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_PTRDomainName, $_ttlSeconds = self::DEFAULT_TTL)
    {
        if (empty($_PTRDomainName)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_rData = array('ptrdname' => $_PTRDomainName);

        parent::__construct($_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds);
    }

    /**
     * Set the PTR Domain Name
     *
     * @author Ravinder Singh
     *
     * @param string $_PTRDomainName The PTR Domain
     *
     * @return SWIFT_DynectPTRRecord
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetPTRDomainName($_PTRDomainName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_PTRDomainName)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $this->SetRData(array('ptrdname' => $_PTRDomainName));

        return $this;
    }

    /**
     * Retrieve the currently set PTR Domain
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPTRDomainName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rData = $this->GetRData();

        return $_rData['ptrdname'];
    }

    /**
     * Create a PTR record
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_PTRDomainName
     * @param int              $_ttlSeconds (OPTIONAL) The TTL Seconds. Only use valid TTL values!
     *
     * @return SWIFT_DynectPTRRecord
     */
    public static function Create(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_PTRDomainName, $_ttlSeconds = self::DEFAULT_TTL)
    {
        $_rData = array('ptrdname' => $_PTRDomainName);

        $_JSONObject = parent::CreateBase(self::BASE_URL, $_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds);

        $_zoneName      = isset($_JSONObject->data->zone) ? (string) $_JSONObject->data->zone : '';
        $_FQDN          = isset($_JSONObject->data->fqdn) ? (string) $_JSONObject->data->fqdn : '';
        $_PTRDomainName = isset($_JSONObject->data->rdata->ptrdname) ? (string) $_JSONObject->data->rdata->ptrdname : '';
        $_ttlSeconds    = isset($_JSONObject->data->ttl) ? (string) $_JSONObject->data->ttl : '';

        $_SWIFT_DynectPTRRecordObject = new SWIFT_DynectPTRRecord($_DynectZoneObject, $_FQDN, $_PTRDomainName, $_ttlSeconds);

        $_DynectZoneObject->Publish();

        return $_SWIFT_DynectPTRRecordObject;
    }

    /**
     * Display PTR Record Properties
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
        $_returnData .= 'PTR Domain: ' . $_rData['ptrdname'] . SWIFT_CRLF;
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
     * @return SWIFT_DynectPTRRecord
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_DynectZoneObject, $_FQDN, $_recordID, $_ = null)
    {
        $_JSONObject = parent::Retrieve(self::BASE_URL, $_DynectZoneObject, $_FQDN, $_recordID);

        $_zoneName      = isset($_JSONObject->data->zone) ? (string) $_JSONObject->data->zone : '';
        $_FQDN          = isset($_JSONObject->data->fqdn) ? (string) $_JSONObject->data->fqdn : '';
        $_PTRDomainName = isset($_JSONObject->data->rdata->ptrdname) ? (string) $_JSONObject->data->rdata->ptrdname : '';
        $_ttlSeconds    = isset($_JSONObject->data->ttl) ? (string) $_JSONObject->data->ttl : '';

        $_SWIFT_DynectPTRRecordObject = new SWIFT_DynectPTRRecord($_DynectZoneObject, $_FQDN, $_PTRDomainName, $_ttlSeconds);

        return $_SWIFT_DynectPTRRecordObject;
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
     * @return SWIFT_DynectRecord
     * @throws SWIFT_Exception If Class is not Loaded or if Invalid Data is Provided
     */
    public function Delete($_recordID = '', $_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return parent::Delete(self::BASE_URL, $_recordID);
    }

    /**
     * Update The record. Use SetPTRDname/SetTTLSeconds function prior to calling
     *
     * @author Ravinder Singh
     *
     * @param string $_recordID (OPTIONAL)
     *
     * @return SWIFT_DynectRecord
     * @throws SWIFT_Exception If Class is not Loaded or if Invalid Data is Provided
     */
    public function Update($_recordID = '', $_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return parent::Update(self::BASE_URL, $_recordID);
    }
}
